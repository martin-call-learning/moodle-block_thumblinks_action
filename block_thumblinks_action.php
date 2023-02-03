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
 * Thumblinks Action block implementation.
 *
 * @package    block_thumblinks_action
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_thumblinks_action\output\thumblinks_action;

/**
 * Class block_thumblinks_action
 *
 * @package    block_thumblinks_action
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_thumblinks_action extends block_base {
    // TODO links seems to be disfunctionnal, they escape real links.

    /**
     * Init function
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_thumblinks_action');
    }

    /**
     * Update the block title from config values
     */
    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    /**
     * Content for the block
     *
     * @return stdClass|string|null
     * @throws coding_exception
     */
    public function get_content() {
        if (!$this->config) {
            $this->content = (object) [
                'text' => get_string("contentnoconfig", "block_thumblinks_action")
            ];
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $this->content->text = '';
        if ($this->config) {
            $this->title = $this->config->title; // Set the title according to the values in the form.

            $renderer = $this->page->get_renderer('core');
            $titles = $this->config->thumbtitle ?? [];
            $urls = $this->config->thumburl ?? [];
            $images = $this->config->thumbimage ?? [];

            $this->content->text = $renderer->render(
                new thumblinks_action(
                    $titles,
                    $images,
                    $urls,
                    $this->config->cta,
                    $this->config->ctatitle,
                    $this->context->id,
                    $this->instance->region ?? ''
                )
            );
        }

        return $this->content;
    }

    /**
     * All applicable formats
     *
     * @return array
     */
    public function applicable_formats(): array {
        return array('all' => true);
    }

    /**
     * Multiple blocks ?
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return true;
    }

    /**
     * Has configuration ?
     *
     * @return bool
     */
    public function has_config(): bool {
        return false;
    }

    /**
     * Serialize and store config data
     *
     * @param stdClass $data
     * @param false $nolongerused
     * @throws coding_exception
     */
    public function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);
        // Save the images.
        if ($config->thumbimage) {
            foreach ($config->thumbimage as $index => $image) {
                file_save_draft_area_files(
                    $image,
                    $this->context->id,
                    'block_thumblinks_action',
                    'images',
                    $index,
                    array('subdirs' => true)
                );
            }
            // Here we make sure we copy the image id into the
            // block parameter. This is then used in save_data
            // to set up the block to the right image.
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                $this->context->id,
                'block_thumblinks_action',
                'images'
            );
            foreach ($files as $file) {
                if (in_array($file->get_filename(), array('.', '..'))) {
                    continue;
                }
                $config->thumbimage[$file->get_itemid()] = $file->get_id();
            }
        }
        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete the block and images.
     *
     * @return bool
     */
    public function instance_delete(): bool {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_thumblinks_action');
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     *
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid): bool {
        global $DB;

        $fromcontext = context_block::instance($fromid);
        $blockinstance = $DB->get_record('block_instances', array('id' => $fromcontext->instanceid));
        $block = block_instance($blockinstance->blockname, $blockinstance);
        $thumbtitlecount = empty($block->config->thumbtitle) ? 0 : count($block->config->thumbtitle);
        $thumbimgcount = empty($block->config->thumbimage) ? 0 : count($block->config->thumbimage);
        $numthumbnails = max($thumbtitlecount, $thumbimgcount);

        $fs = get_file_storage();

        // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
        if (!$fs->is_area_empty($fromcontext->id, 'block_thumblinks_action', 'images', 0, false)) {
            for ($itemid = 0; $itemid < $numthumbnails; $itemid++) {
                $draftitemid = 0;
                file_prepare_draft_area(
                    $draftitemid,
                    $fromcontext->id,
                    'block_thumblinks_action',
                    'images',
                    $itemid,
                    array('subdirs' => true)
                );
                file_save_draft_area_files(
                    $draftitemid,
                    $this->context->id,
                    'block_thumblinks_action',
                    'images',
                    $itemid,
                    array('subdirs' => true)
                );
            }
        }
        return true;
    }
}
