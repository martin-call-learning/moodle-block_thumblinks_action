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
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_thumblinks_action_edit_form
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_thumblinks_action_edit_form extends block_edit_form {

    /**
     * Form definition
     *
     * @param object $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Title of the block.
        $mform->addElement('text', 'config_title', get_string('config:title', 'block_thumblinks_action'));
        $mform->setDefault('config_title', 'default value');
        $mform->setType('config_title', PARAM_TEXT);

        // The CTA Link.
        $mform->addElement('url', 'config_cta', get_string('config:cta', 'block_thumblinks_action'));
        $mform->setDefault('config_cta', '');
        $mform->setType('config_cta', PARAM_URL);
        // The CTA title.
        $mform->addElement('text', 'config_ctatitle', get_string('config:ctatitle', 'block_thumblinks_action'));
        $mform->setDefault('config_ctatitle', '');
        $mform->setType('config_ctatitle', PARAM_TEXT);

        $this->add_thubmnail_elements($mform);

    }

    protected function add_thubmnail_elements($mform) {
        global $DB;
        $repeatarray = array();
        $repeatedoptions = array();

        $repeatarray[] = $mform->createElement('text', 'config_thumbtitle',
            get_string('config:thumbtitle', 'block_thumblinks_action'));
        $repeatedoptions['config_thumbtitle']['type'] = PARAM_RAW;

        $repeatarray[] = $mform->createElement('filemanager', 'config_thumbimage',
            get_string('config:thumbimage', 'block_thumblinks_action'));
        $repeatedoptions['config_thumbimage']['type'] = PARAM_RAW;

        // The CTA Link.
        $repeatarray[] = $mform->createElement('url', 'config_thumburl',
            get_string('config:thumburl', 'block_thumblinks_action'));
        $repeatedoptions['config_thumburl']['type'] = PARAM_URL;

        $thumbtitlecount = empty($this->block->config->thumbtitle)? 0: count($this->block->config->thumbtitle);
        $thumbimgcount = empty($this->block->config->thumbimage)? 0: count($this->block->config->thumbimage);
        $numthumbnails = max(1, $thumbtitlecount, $thumbimgcount);

        $this->repeat_elements($repeatarray, $numthumbnails,
            $repeatedoptions, 'thumb_repeats', 'thumb_add_fields', 1,
            get_string('addmorethumbnails', 'block_thumblinks_action'), true);

    }
}
