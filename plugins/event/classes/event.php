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

namespace phputestgenerator_event;

defined('MOODLE_INTERNAL') || die();

/**
 * Events subplugin
 *
 * @package   phputestgenerator_event
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event extends \local_phpunit_testgenerator\phputestgeneratorplugin {

    private $class = '\core\event\base';

    private $amhandler = false;

    public function get_name() {
        return get_string('pluginname', 'phputestgenerator_event');
    }

    public function is_handler($filepath, $fullclassname) {
        return $this->amhandler = $this->is_instance_of($this->class, $filepath, $fullclassname);
    }

    protected function get_constructorlines($function) {
        $classlines = array();

        if ($this->amhandler) {
            // Do I want to generate any output?
            // Constructor for events is different.
            $classlines[] = "\t\t" . '/* Here ensure to define the event properties that are required */';
            $classlines[] = "\t\t" . '$eventdata = array(';
            $classlines[] = "\t\t\t" . '"other" => array("message" => "This is just a test")';
            $classlines[] = "\t\t" . ');';
            $classlines[] = "\t\t" . '$' . "$classvarname = $fullclassname::create(\$eventdata);";
        }

        return $classlines;
    }

    public function get_trigger_testlines(){
        return
        '
/**
 * Test the triggering of the event.
 */
public function test_trigger() {
    $this->resetAfterTest(false);

    // Mark this test as incomplete.
    $this->markTestIncomplete("This test needs to be completed");

    $sink = $this->redirectEvents();

    /* Here ensure to define the event properties that are required */
    $eventdata = array(
        "other" => array("message" => "This is just a test")
    );

    $event = ' . $classname . '::create($eventdata);
    $event->trigger();

    $events = $sink->get_events();
    $this->assertGreaterThan(0, count($events));

    foreach ($events as $event) {
        if ($event instanceof ' . $classname . ') {
            break;  // The variable $event will be the correct event.
        }
    }
    // This will fail if the event is not found.
    $this->assertInstanceOf(' . "'" . $classname . "'" . ', $event);
}

';

    }

}
