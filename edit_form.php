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
        $mform->setDefault('config_title', get_string('pluginname', 'block_thumblinks_action'));
        $mform->setType('config_title', PARAM_TEXT);

        // The CTA Link.
        $mform->addElement('text', 'config_cta', get_string('config:cta', 'block_thumblinks_action'));
        $mform->setDefault('config_cta', '');
        $mform->setType('config_cta', PARAM_URL);
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

        $repeatarray[] = $mform->createElement(
            'text',
            'config_thumbtitle',
            get_string('config:thumbtitle', 'block_thumblinks_action')
        );
        $repeatedoptions['config_thumbtitle']['type'] = PARAM_TEXT;

        $repeatarray[] = $mform->createElement(
            'filemanager',
            'config_thumbimage',
            get_string('config:thumbimage', 'block_thumblinks_action'),
            null,
            $this->get_file_manager_options()
        );
        $repeatedoptions['config_thumbimage']['type'] = PARAM_RAW;

        // The CTA Link.
        $repeatarray[] = $mform->createElement(
            'text',
            'config_thumburl',
            get_string('config:thumburl', 'block_thumblinks_action')
        );
        $repeatedoptions['config_thumburl']['type'] = PARAM_URL;

        $numthumbnails = $this->get_current_repeats();

        $this->repeat_elements(
            $repeatarray,
            $numthumbnails,
            $repeatedoptions,
            'thumb_repeats',
            'thumb_add_fields',
            1,
            get_string('addmorethumbnails', 'block_thumblinks_action'),
            true,
            'thumb_remove_fields',
            get_string('removelastthumb', 'block_thumblinks_action')
        );
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
            for ($index = 0; $index < $numthumbnails; $index++) {
                $fieldname = 'config_thumbimage';
                $filefields->{$fieldname}[$index] = array();
                // Here we could try to use the file_get_submitted_draft_itemid, but it expects to have an itemid defined
                // Which is not what we have right now, we just have a flat list.
                $param = optional_param_array($fieldname, 0, PARAM_INT);
                $draftitemid = null;
                if (!empty($param[$index])) {
                    $draftitemid = $param[$index];
                }
                file_prepare_draft_area(
                    $draftitemid,
                    $this->block->context->id,
                    'block_thumblinks_action',
                    'images',
                    $index,
                    $this->get_file_manager_options()
                );

                $filefields->{$fieldname}[$index] = $draftitemid;
            }
            moodleform::set_data($filefields);
        }
    }

    /**
     * Get number of repeats.
     *
     * @return int the number of repeats.
     */
    protected function get_current_repeats(): int {
        $thumbtitlecount = empty($this->block->config->thumbtitle) ? 0 : count($this->block->config->thumbtitle);
        $thumbimgcount = empty($this->block->config->thumbimage) ? 0 : count($this->block->config->thumbimage);
        return max(1, $thumbtitlecount, $thumbimgcount);
    }

    /**
     * Get usual options for filemanager
     *
     * @return array
     */
    protected function get_file_manager_options(): array {
        return array('subdirs' => 0,
            'maxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,
            'maxfiles' => 1,
            'context' => $this->block->context);
    }

    /**
     * Method to add a repeating group of elements to a form.
     *
     * We can also remove the last element of the list.
     *
     * @param array $elementobjs Array of elements or groups of elements that are to be repeated
     * @param int $repeats no of times to repeat elements initially
     * @param array $options a nested array. The first array key is the element name.
     *    the second array key is the type of option to set, and depend on that option,
     *    the value takes different forms.
     *         'default'    - default value to set. Can include '{no}' which is replaced by the repeat number.
     *         'type'       - PARAM_* type.
     *         'helpbutton' - array containing the helpbutton params.
     *         'disabledif' - array containing the disabledIf() arguments after the element name.
     *         'rule'       - array containing the addRule arguments after the element name.
     *         'expanded'   - whether this section of the form should be expanded by default. (Name be a header element.)
     *         'advanced'   - whether this element is hidden by 'Show more ...'.
     * @param string $repeathiddenname name for hidden element storing no of repeats in this form
     * @param string $addfieldsname name for button to add more fields
     * @param int $addfieldsno how many fields to add at a time
     * @param string $addstring name of button, {no} is replaced by no of blanks that will be added.
     * @param bool $addbuttoninside if true, don't call closeHeaderBefore($addfieldsname). Default false.
     * @param string $deletefieldsname name of the button that will trigger the deletion of the repeat element
     * @param string $deletestring name for button to remove the last field
     * @return int no of repeats of element in this page
     * @throws coding_exception
     */
    public function repeat_elements(
        $elementobjs,
        $repeats,
        $options,
        $repeathiddenname,
        $addfieldsname,
        $addfieldsno = 5,
        $addstring = null,
        $addbuttoninside = false,
        $deletefieldsname = null,
        $deletestring = null
    ): int {
        $repeats = $this->optional_param($repeathiddenname, $repeats, PARAM_INT);
        if ($deletefieldsname) {
            $removefields = $this->optional_param($deletefieldsname, '', PARAM_TEXT);
            if (!empty($removefields)) {
                $repeats -= 1; // Remove last course.
            }
            if ($deletestring === null) {
                $deletestring = get_string('delete', 'moodle');
            }
        }
        if ($addstring === null) {
            $addstring = get_string('addfields', 'form', $addfieldsno);
        } else {
            $addstring = str_ireplace('{no}', $addfieldsno, $addstring);
        }

        $addfields = $this->optional_param($addfieldsname, '', PARAM_TEXT);
        if (!empty($addfields)) {
            $repeats += $addfieldsno;
        }
        $mform =& $this->_form;
        $mform->registerNoSubmitButton($addfieldsname);
        $mform->addElement('hidden', $repeathiddenname, $repeats);
        $mform->setType($repeathiddenname, PARAM_INT);
        // Value not to be overridden by submitted value.
        $mform->setConstants(array($repeathiddenname => $repeats));
        $namecloned = array();
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($elementobjs as $elementobj) {
                $elementclone = fullclone($elementobj);
                $this->repeat_elements_fix_clone($i, $elementclone, $namecloned);

                if ($elementclone instanceof HTML_QuickForm_group && !$elementclone->_appendName) {
                    foreach ($elementclone->getElements() as $el) {
                        $this->repeat_elements_fix_clone($i, $el, $namecloned);
                    }
                    $elementclone->setLabel(str_replace('{no}', $i + 1, $elementclone->getLabel()));
                }

                $mform->addElement($elementclone);
            }
        }
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($options as $elementname => $elementoptions) {
                $pos = strpos($elementname, '[');
                if ($pos !== false) {
                    $realelementname = substr($elementname, 0, $pos) . "[$i]";
                    $realelementname .= substr($elementname, $pos);
                } else {
                    $realelementname = $elementname . "[$i]";
                }
                foreach ($elementoptions as $option => $params) {
                    switch ($option) {
                        case 'default':
                            $mform->setDefault($realelementname, str_replace('{no}', $i + 1, $params));
                            break;
                        case 'helpbutton':
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addHelpButton'), $params);
                            break;
                        case 'disabledif':
                            foreach ($namecloned as $num => $name) {
                                if ($params[0] == $name) {
                                    $params[0] = $params[0] . "[$i]";
                                    break;
                                }
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'disabledIf'), $params);
                            break;
                        case 'hideif':
                            foreach ($namecloned as $num => $name) {
                                if ($params[0] == $name) {
                                    $params[0] = $params[0] . "[$i]";
                                    break;
                                }
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'hideIf'), $params);
                            break;
                        case 'rule':
                            if (is_string($params)) {
                                $params = array(null, $params, null, 'client');
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addRule'), $params);
                            break;

                        case 'type':
                            $mform->setType($realelementname, $params);
                            break;

                        case 'expanded':
                            $mform->setExpanded($realelementname, $params);
                            break;

                        case 'advanced':
                            $mform->setAdvanced($realelementname, $params);
                            break;
                    }
                }
            }
        }
        $mform->addElement('submit', $addfieldsname, $addstring);
        if ($deletefieldsname) {
            $mform->addElement('submit', $deletefieldsname, $deletestring);
            $mform->registerNoSubmitButton($deletefieldsname);
        }

        if (!$addbuttoninside) {
            $mform->closeHeaderBefore($addfieldsname);
        }

        return $repeats;
    }
}
