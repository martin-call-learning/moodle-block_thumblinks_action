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
 * File containing a class allowing set editing mode on either on moodle 3 & moodle 4.
 *
 * @package     block_thumblinks_action
 * @copyright   2022 - CALL Learning
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Martin CORNU-MANSUY <martin@call-learning>
 */

/**
 *  Behat customisations for the bloc
 *
 * @package     block_thumblinks_action
 * @copyright   2022 - CALL Learning
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Martin CORNU-MANSUY <martin@call-learning>
 */
class behat_block_thumblinks_action extends behat_base {
    /**
     * Return the list of partial named selectors for this plugin.
     *
     * @return behat_component_named_selector[]
     */
    public static function get_partial_named_selectors(): array {
        return [
            new behat_component_named_selector(
                'Thumbnail link', [
                    <<<XPATH
    .//a[contains(@class,'thumbnail') and contains(normalize-space(.),%locator%)]
XPATH
                ]
            ),
        ];
    }

    /**
     * Return a list of the exact named selectors for the component.
     *
     * @return behat_component_named_selector[]
     */
    public static function get_exact_named_selectors(): array {
        return [
            new behat_component_named_selector('Link with URL', [
                "//div[contains(@class,'block-thumblinks-action')]//a[contains(@href, %locator%)]",
            ]),
            new behat_component_named_selector('Link with Background', [
                "//div[contains(@class,'block-thumblinks-action')]//a[contains(@style, %locator%)]",
            ]),
        ];
    }

}
