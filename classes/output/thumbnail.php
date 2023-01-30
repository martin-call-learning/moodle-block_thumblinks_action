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
 * Thumbnail inside the thumblinks action block
 *
 * @package    block_thumblinks_action
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_thumblinks_action\output;

use moodle_url;

/**
 * A class to represent a thumbnail in the thumblink_action moodle block.
 */
class thumbnail {
    /** @var string the title of the thumbnail. */
    private $title;

    /** @var moodle_url the link of the thumbnail. */
    private $link;

    /** @var moodle_url the image url of the thumbnail. */
    private $imageurl;

    /**
     * Constructor.
     *
     * @param string $title
     * @param moodle_url $link
     * @param moodle_url $imageurl
     */
    public function __construct($title, $link, $imageurl) {
        $this->link = $link;
        $this->title = $title;
        $this->imageurl = $imageurl;
    }

    /**
     * Gets the title of the thumbnail.
     *
     * @return string
     */
    public function gettitle(): string {
        return $this->title;
    }

    /**
     * Gets the imageurl of the thumbnail.
     *
     * @return string
     */
    public function getimageurl(): string {
        return $this->imageurl;
    }

    /**
     * Gets the link of the thumbnail.
     *
     * @return string
     */
    public function getlink(): string {
        return $this->link;
    }
}
