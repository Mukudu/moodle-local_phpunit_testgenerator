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

// namespace local_phpunit_testgenerator;

defined('MOODLE_INTERNAL') || die();

/**
 * Base subplugin
 *
 * @package   phputestgenerator_event
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phputestgeneratorbase extends \local_phpunit_testgenerator\phputestgeneratorplugin {

    public function is_handler($filepath, $fullclassname) {
        return false;
    }

    public function get_name() {
        return 'phputestgeneratorbase';
    }


}
