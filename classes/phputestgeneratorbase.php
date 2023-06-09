<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/plugins.class.php');

/**
 * Base sub-plugin
 *
 * @package   phputestgenerator_event
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phputestgeneratorbase extends \local_phpunit_testgenerator\phputestgeneratorplugin {

    /**
     * Reports if it is the handler of the class.
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::is_handler()
     * @return bool.
     */
    public function is_handler(string $filepath, string $fullclassname) : bool {
        return false;
    }

    /**
     * Return the sub plugin's name.
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::get_name()
     * @return string.
     */
    public function get_name() : string {
        return 'phputestgeneratorbase';
    }

}
