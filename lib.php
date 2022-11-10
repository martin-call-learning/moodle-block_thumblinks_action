<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit Form
 *
 * @package    block_thumblinks_action
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_thumblinks_action_edit_form
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\session\manager;

/**
 * Get plugin file for this block (identical to HTML block)
 *
 * @param stdClass $course Course object
 * @param stdClass $birecordorcm Block instance record
 * @param stdClass $context Context object
 * @param string $filearea File area
 * @param array $args Extra arguments
 * @param bool $forcedownload Whether force download
 * @param array $options Additional options affecting the file serving
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @category  files
 */
function block_thumblinks_action_pluginfile(
    $course,
    $birecordorcm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = array()
) {
    global $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !== 'images') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $itemid = array_shift($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    if ((!$file = $fs->get_file($context->id, 'block_thumblinks_action', $filearea, $itemid, $filepath, $filename)) ||
        $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecordorcm->parentcontextid, IGNORE_MISSING)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            $forcedownload = true;
        }
    } else {
        $forcedownload = true;
    }
    manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 *
 * @param  string $search
 * @param  string $replace
 * @return void
 */
function block_thumblinks_action_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'mcms'));
    foreach ($instances as $instance) {
        $config = unserialize(base64_decode($instance->configdata));
        if (isset($config->text) && is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->update_record('block_instances', (object) [
                'id' => $instance->id,
                'configdata' => base64_encode(serialize($config)),
                'timemodified' => time()]);
        }
    }
    $instances->close();
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param string $filearea The filearea.
 * @param array $args The path (the part after the filearea and before the filename).
 * @return array The itemid and the filepath inside the $args path, for the defined filearea.
 */
function block_thumblinks_action_get_path_from_pluginfile(string $filearea, array $args): array {
    // This block never has an itemid (the number represents the revision, but it's not stored in database).
    array_shift($args);

    $itemid = 0;
    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $itemid = array_shift($args);
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => $itemid,
        'filepath' => $filepath,
    ];
}
