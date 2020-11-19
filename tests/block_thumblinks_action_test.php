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
 * Base class for unit tests for block_thumblinks_action.
 *
 * @package   block_thumblinks_action
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_thumblinks_action\output\thumblinks_action;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for block_thumblinks_action
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_thumblinks_action_test extends advanced_testcase {

    /**
     * Current block
     *
     * @var block_base|false|null
     */
    protected $block = null;
    /**
     * Current user
     *
     * @var stdClass|null
     */
    protected $user = null;

    /**
     * Basic setup for these tests.
     */
    public function setUp() {
        global $CFG;
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
        // Create a Sponsor block.
        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('frontpage');
        $blockname = 'thumblinks_action';
        $page->blocks->load_blocks();
        $page->blocks->add_block_at_end_of_default_region($blockname);
        // Here we need to work around the block API. In order to get 'get_blocks_for_region' to work,
        // we would need to reload the blocks (as it has been added to the DB but is not
        // taken into account in the block manager).
        // The only way to do it is to recreate a page so it will reload all the block.
        // It is a main flaw in the  API (not being able to use load_blocks twice).
        // Alternatively if birecordsbyregion was nullable,
        // should for example have a load_block + create_all_block_instances and
        // should be able to access to the block.
        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('frontpage');
        $page->blocks->load_blocks();
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);
        $block = block_instance($blockname, $block->instance);
        $this->block = $block;
        $this->upload_files_in_block(array('img1.png', 'img2.png'));
        $this->block = $block;
    }

    /**
     * Test that output is as expected. This also test file loading into the plugin.
     */
    public function test_simple_content() {
        // We need to reload the block so config is there.
        $block = block_instance_by_id($this->block->instance->id);
        $content = $block->get_content();
        $this->assertNotNull($content->text);

        $expected = '<div  class="block-thumblinks-action block-cards">
    <div class="container">
        <div class="row">
                <a class="thumbnail col mx-2 my-1 d-flex flex-column justify-content-center p-0" '
            . '  style="background-image: url(https://www.example.com/moodle/pluginfile.php/188001/'
            . 'block_thumblinks_action/images/0/img1.png);"  href="http://moodle.com/0">
                    <div class="title w-100 p-1 p-l-2 mt-5">Title 0</div>
                </a>
                <a class="thumbnail col mx-2 my-1 d-flex flex-column justify-content-center p-0"'
            . '   style="background-image: url(https://www.example.com/moodle/pluginfile.php/188001/'
            . 'block_thumblinks_action/images/1/img2.png);"  href="http://moodle.com/1">
                    <div class="title w-100 p-1 p-l-2 mt-5">Title 1</div>
                </a>
        </div>
    </div>
    <div class="container text-center mt-5">
        <a class="btn btn-primary" href="https://www.moodle.org">
            Moodle forever
        </a>
    </div>
</div>';
        $text = preg_replace('/id="block-thumblinks-action([^"]+)"/i', '', $content->text);
        $this->assertEquals($expected, $text);
    }

    /**
     * Test that output is as expected. This also test file loading into the plugin.
     */
    public function test_output_renderer_change_files() {
        // We need to reload the block so config is there.
        $this->upload_files_in_block(array('img4.png', 'img5.png'));
        $block = block_instance_by_id($this->block->instance->id);
        $renderer = $this->block->page->get_renderer('core');
        $renderable = new thumblinks_action(
            $block->config->thumbtitle,
            $block->config->thumburl,
            $block->config->thumbimage,
            'https://www.moodle.org',
            'Moodle forever',
            $block->context->id
        );
        $exported = $renderable->export_for_template($renderer);
        $this->assertStringEndsWith('img4.png', $exported['thumbnails'][0]->image);
        $this->assertStringEndsWith('img5.png', $exported['thumbnails'][1]->image);
    }

    /**
     * Upload a file/image in the block
     *
     * @param array $imagesnames
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    protected function upload_files_in_block($imagesnames) {
        global $CFG;
        $block = block_instance_by_id($this->block->instance->id);
        $usercontext = context_user::instance($this->user->id);
        $files = [];
        $configdata = (object) [
            'title' => 'block title',
            'cta' => 'https://www.moodle.org',
            'ctatitle' => 'Moodle forever'
        ];
        foreach ($imagesnames as $index => $filename) {
            $draftitemid = file_get_unused_draft_itemid();
            $filerecord = array(
                'contextid' => $usercontext->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $draftitemid,
                'filepath' => '/',
                'filename' => $filename,
            );
            // Create an area to upload the file.
            $fs = get_file_storage();
            // Create a file from the string that we made earlier.
            $files[] = $fs->create_file_from_pathname($filerecord,
                $CFG->dirroot . '/blocks/thumblinks_action/tests/fixtures/bookmark-new.png');
            $configdata->thumbtitle[] = 'Title ' . $index;
            $configdata->thumburl[] = 'http://moodle.com/' . $index;
        }
        $configdata->thumbimage = [$files[0]->get_itemid(), $files[1]->get_itemid()];
        $block->instance_config_save((object) $configdata);
    }
}
