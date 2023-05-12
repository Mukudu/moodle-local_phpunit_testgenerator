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

namespace phputestgenerator_task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../classes/plugins.class.php');

/**
 * Tasks subplugin
 *
 * @package   phputestgenerator_task
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class task extends \local_phpunit_testgenerator\phputestgeneratorplugin {

    /** @var $class - the class we handle */
    private $class = '\core\task\task_base';

    /**
     * Return the name of the subplugin
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::get_name()
     */
    public function get_name() : string {
        return get_string('pluginname', 'phputestgenerator_task');
    }

    /**
     * Returns true if the sub plugin is the class' handler.
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::is_handler()
     * @param string $filepath
     * @param string $fullclassname
     * @return bool
     */
    public function is_handler(string $filepath, string $fullclassname) : bool {
        return $this->is_instance_of($this->class, $filepath, $fullclassname);
    }

}
