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

    protected $constructorlines = array();

    public function get_name() {
        return get_string('pluginname', 'phputestgenerator_event');
    }

    public function is_handler($filepath, $fullclassname) {
        return $this->amhandler = $this->is_instance_of($this->class, $filepath, $fullclassname);
    }

    // Constructor for events is different.
    protected function make_constructorlines($class = null) {
         if (!count($this->constructorlines)) {
            $this->make_class_varname($class->name);
            $this->constructorlines[] = "\t\t" . '/* Here ensure to define the event properties that are required */' . "\n";
            $this->constructorlines[] = "\t\t" . '$eventdata = array(' . "\n";
            $this->constructorlines[] = "\t\t\t" . '"other" => array("message" => "This is just a test")' . "\n";
            $this->constructorlines[] = "\t\t" . ');' . "\n";
            $this->constructorlines[] = "\t\t" . '$' . $this->classvarname . ' = ' . $class->fullname . "::create(\$eventdata);\n";
        }
    }

    public function generate_postfunctions_tests($pluginpath, $relativefile, $class) {
        return array($this->get_trigger_testlines($class->name));
    }

    private function get_trigger_testlines($classname){
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
