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
     * @param moodleform $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Title of the block.
        $mform->addElement('text', 'config_title', get_string('config:title', 'block_thumblinks_action'));
        $mform->setDefault('config_title', get_string('title', 'block_thumblinks_action'));
        $mform->setType('config_title', PARAM_TEXT);

        // The CTA Link.
        $mform->addElement('text', 'config_cta', get_string('config:cta', 'block_thumblinks_action'));
        $mform->setDefault('config_cta', '');
        $mform->setType('config_cta', PARAM_LOCALURL);
        // The CTA title.
        $mform->addElement('text', 'config_ctatitle', get_string('config:ctatitle', 'block_thumblinks_action'));
        $mform->setDefault('config_ctatitle', '');
        $mform->setType('config_ctatitle', PARAM_TEXT);

        $this->add_thubmnail_elements($mform);

    }

    /**
     * Add thumbnails elements
     *
     * @param moodleform $mform
     * @throws coding_exception
     */
    protected function add_thubmnail_elements($mform) {
        $repeatarray = array();
        $repeatedoptions = array();

        $repeatarray[] = $mform->createElement('text', 'config_thumbtitle',
            get_string('config:thumbtitle', 'block_thumblinks_action'));
        $repeatedoptions['config_thumbtitle']['type'] = PARAM_TEXT;

        $repeatarray[] = $mform->createElement(
            'filemanager',
            'config_thumbimage',
            get_string('config:thumbimage', 'block_thumblinks_action'),
            null,
            array('subdirs' => 0, 'maxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED, 'maxfiles' => 1)
        );
        $repeatedoptions['config_thumbimage']['type'] = PARAM_RAW;

        // The CTA Link.
        $repeatarray[] = $mform->createElement('text', 'config_thumburl',
            get_string('config:thumburl', 'block_thumblinks_action'));
        $repeatedoptions['config_thumburl']['type'] = PARAM_LOCALURL;

        $numthumbnails = $this->get_current_repeats();

        $this->repeat_elements($repeatarray, $numthumbnails,
            $repeatedoptions, 'thumb_repeats', 'thumb_add_fields', 1,
            get_string('addmorethumbnails', 'block_thumblinks_action'), true);

    }

    /**
     * Set for data
     *
     * @param array|stdClass $defaults
     */
    public function set_data($defaults) {
        parent::set_data($defaults);
        // Restore filemanager fields.
        // This is a bit of a hack working around the issues of the block.
        // When using set_data, we set the file data to the real file as it reads it
        // from the block config,
        // not the draft manager file. This can be rectified by a second call to set_data.
        // We try to get the previously submitted file.
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $filefields = new stdClass();
            $numthumbnails = $this->get_current_repeats();
            require_sesskey(); // This is because we don't use file_get_submitted_draft_itemid.
            for ($index = 0; $index < $numthumbnails; $index++) {
                $fieldname = 'config_thumbimage';
                $filefields->{$fieldname}[$index] = array();
                // Here we could try to use the file_get_submitted_draft_itemid, but it expects to have an itemid defined
                // Which is not what we have right now, we just have a flat list.
                $param = optional_param_array($fieldname, 0, PARAM_INT);
                $draftitemid = $param[$index];
                if (!empty($param[$index])) {
                    $draftitemid = $param[$index];
                }
                file_prepare_draft_area($draftitemid,
                    $this->block->context->id,
                    'block_thumblinks_action',
                    'images',
                    $index,
                    array('subdirs' => 0, 'maxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED, 'maxfiles' => 1,
                        'context' => $this->block->context));

                $filefields->{$fieldname}[$index] = $draftitemid;
            }
            moodleform::set_data($filefields);
        }
    }


    /**
     * Get number of repeats
     */
    protected function get_current_repeats() {
        $thumbtitlecount = empty($this->block->config->thumbtitle) ? 0 : count($this->block->config->thumbtitle);
        $thumbimgcount = empty($this->block->config->thumbimage) ? 0 : count($this->block->config->thumbimage);
        return max(1, $thumbtitlecount, $thumbimgcount);
    }
}
