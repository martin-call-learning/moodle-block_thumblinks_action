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
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_thumblinks_action\output;
defined('MOODLE_INTERNAL') || die();

global $CFG;

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for thumblink_action block.
 *
 * @package    block_thumblinks_action
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
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
     * thumblinks_action constructor.
     *
     * @param array $thumbtitles
     * @param array $thumbimages
     * @param array $thumburls
     * @param \moodle_url $cta
     * @param string $ctatitle
     * @param int $blockcontextid
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function __construct($thumbtitles, $thumbimages, $thumburls, $cta, $ctatitle, $blockcontextid) {
        $thumbtitlecount = empty($thumbtitles) ? 0 : count($thumbtitles);
        $thumbimgcount = empty($thumbimages) ? 0 : count($thumbimages);
        $numthumbnails = max($thumbtitlecount, $thumbimgcount);
        $fs = get_file_storage();

        for ($itemi = 0; $itemi < $numthumbnails; $itemi++) {
            $thumbnail = new \stdClass();
            $thumbnail->title = !empty($thumbtitles[$itemi]) ? $thumbtitles[$itemi] : '';
            $thumbnail->url = !empty($thumburls[$itemi]) ? $thumburls[$itemi] : null;
            $allfiles = $fs->get_area_files($blockcontextid, 'block_thumblinks_action', 'images', $itemi);
            foreach ($allfiles as $file) {
                if ($file->is_valid_image()) {
                    $thumbnail->image = \moodle_url::make_pluginfile_url(
                        $blockcontextid,
                        'block_thumblinks_action',
                        'images',
                        $itemi,
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out();
                }
            }
            $this->thumbnails[] = $thumbnail;
        }
        $this->cta = new \moodle_url($cta);
        $this->ctatitle = $ctatitle;
    }

    /**
     * Export for mustache template
     *
     * @param renderer_base $output
     * @return array|\stdClass
     */
    public function export_for_template(renderer_base $output) {
        $exportedvalue = [
            'thumbnails' => $this->thumbnails,
            'ctatitle' => $this->ctatitle,
            'cta' => ($this->cta) ? $this->cta->out() : ''
        ];
        return $exportedvalue;
    }
}