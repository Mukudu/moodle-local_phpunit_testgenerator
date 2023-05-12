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

require_once(__DIR__ . '/../../../classes/plugins.class.php');

/**
 * Events subplugin
 *
 * @package   phputestgenerator_event
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event extends \local_phpunit_testgenerator\phputestgeneratorplugin {

    /** @var $class - the class we handle */
    private $class = '\core\event\base';

    /** @var $constructorlines - lines used to construct the object in each test. */
    protected $constructorlines = array();

    /**
     * Return the name of the subplugin
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::get_name()
     */
    public function get_name() : string {
        return get_string('pluginname', 'phputestgenerator_event');
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

    /**
     * Generates the lines that create the class object for each test.
     *
     * Constructor for events is different.
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::make_constructorlines()
     * @param \stdClass $class
     */
    protected function make_constructorlines(\stdClass $class = null) : void {
        if (!count($this->constructorlines)) {
            $this->make_class_varname($class->name);
            $this->constructorlines[] = "\t\t" . '/* Here ensure to define the event properties that are required */' . "\n";
            $this->constructorlines[] = "\t\t" . '$eventdata = array(' . "\n";
            $this->constructorlines[] = "\t\t\t" . '"other" => array("message" => "This is just a test")' . "\n";
            $this->constructorlines[] = "\t\t" . ');' . "\n";
            $this->constructorlines[] = "\t\t" . '$' . $this->classvarname . ' = ' . $class->fullname . "::create(\$eventdata);\n";
        }
    }

    /**
     * Generate lines post (after) the main test lines.
     *
     * @see \local_phpunit_testgenerator\phputestgeneratorplugin::generate_postfunctions_tests()
     * @param string $pluginpath
     * @param string $relativefile
     * @param \stdClass $class
     * @return array
     */
    public function generate_postfunctions_tests(string $pluginpath, string $relativefile, \stdClass $class) {
        return array($this->get_trigger_testlines($class->name));
    }

    /**
     * Additional test function to test if event can be triggered.
     *
     * @param string $classname
     * @return string
     */
    private function get_trigger_testlines(string $classname) : string {
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
