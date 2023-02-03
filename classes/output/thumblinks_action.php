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
 * Thumblinks Action block renderable.
 *
 * @package    block_thumblinks_action
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_thumblinks_action\output;

use coding_exception;
use moodle_exception;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class containing data for thumblink_action block.
 *
 * @package    block_thumblinks_action
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class thumblinks_action implements renderable, templatable {

    /**
     * @var array thumbnails
     */
    public $thumbnails = [];

    /**
     * @var moodle_url $cta
     */

    /**
     * Call to action
     *
     * @var moodle_url|null
     */
    public $cta = null;

    /**
     * Title for CTA
     *
     * @var string $ctatitle
     */
    public $ctatitle = '';

    /**
     * The place where the block takes place.
     *
     * @var string $region
     */
    public $region = '';

    /**
     * thumblinks_action constructor.
     *
     * @param array $thumbtitles the list of the thumbnails titles
     * @param array $thumbimages the list of the thumbnails images
     * @param array $thumburls the list of the thumnails urls
     * @param moodle_url $cta The url where the call to action button will lead to
     * @param string $ctatitle The Title of the call to action button at the bottom of the block
     * @param int $blockcontextid the id of the block context
     * @param string $region the region of the block, usually given by block_thumblinks_action::instance->region
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function __construct($thumbtitles, $thumbimages, $thumburls, $cta, $ctatitle, $blockcontextid, $region) {
        $thumbtitlecount = empty($thumbtitles) ? 0 : count($thumbtitles);
        $thumbimgcount = empty($thumbimages) ? 0 : count($thumbimages);
        $numthumbnails = max($thumbtitlecount, $thumbimgcount);
        $this->region = $region;
        $fs = get_file_storage();

        for ($itemi = 0; $itemi < $numthumbnails; $itemi++) {
            $thumbnail = new stdClass();
            $thumbnail->title = $thumbtitles[$itemi] ?? '';
            $thumbnail->url = $thumburls[$itemi] ?? null;
            $allfiles = $fs->get_area_files($blockcontextid, 'block_thumblinks_action', 'images', $itemi);
            foreach ($allfiles as $file) {
                if ($file->is_valid_image()) {
                    $thumbnail->image = moodle_url::make_pluginfile_url(
                        $blockcontextid,
                        'block_thumblinks_action',
                        'images',
                        $itemi,
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out(false);
                }
            }
            $this->thumbnails[] = $thumbnail;
        }
        $this->cta = new moodle_url($cta);
        $this->ctatitle = $ctatitle;
    }

    /**
     * Export for mustache template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $exportedvalue = [
            'thumbnails' => $this->thumbnails,
            'ctatitle' => $this->ctatitle,
            'cta' => ($this->cta) ? $this->cta->out(false) : '',
            'isonside-pre' => $this->region == 'side-pre'
        ];
        return $exportedvalue;
    }
}
