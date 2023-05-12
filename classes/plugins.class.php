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

namespace local_phpunit_testgenerator;

defined('MOODLE_INTERNAL') || die();

/**
 * Subplugin base class
 *
 * @package   local_phpunit_testgenerator
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class phputestgeneratorplugin {

    protected $constructorlines = array();

    protected $classvarname = '';

    protected $pendinglines = true; // Default.

    protected $dbreset = 'true';

    abstract function get_name();

    abstract function is_handler($filepath, $fullclassname);

    protected function set_dbreset(bool $reset = true) {
        if ($reset) {
            $this->dbreset = 'true';
        } else {
            $this->dbreset = 'false';
        }
    }

    protected function make_class_varname($classname) {
        if (!$this->classvarname) {
            $this->classvarname = strtolower(preg_replace('/[\W_]/', '', $classname));
        }
    }

    protected function make_constructorlines($class = null) {
        if ($class && !empty($class->type)) {
            if (!count($this->constructorlines)) {
                $this->make_class_varname($class->name); // Create a class variable name.
                // Let's create the class constructor.
                foreach ($class->functions as $function) {
                    // We need to the constructor for our tests.
                    if ($function->name == '__construct') {
                        if (!$function->accessmodifiers || in_array(T_PUBLIC, $function->accessmodifiers)) {    // Only public ones.
                            $argsnippet = '';
                            if (count($function->arguments)) {
                                foreach ($function->arguments as $arg) {
                                    if ($argmt = $arg[1]) {
                                        $this->constructorlines[] = "\t\t$argmt = null;\t// " .
                                                    get_string('providevaluemsg', 'local_phpunit_testgenerator');
                                        if ($argsnippet) {
                                            $argsnippet .= ', ';
                                        }
                                        $argsnippet .= $argmt;
                                    }
                                }
                            }
                            $this->constructorlines[] = "\n\t\t" . '$' . $this->classvarname . " = new " .
                                    $class->name . "($argsnippet);\n\n";
                        }
                        break;
                    }
                }
                if (!$this->constructorlines) {     // No constructor - default constructor.
                    // This line will not be used by static functions.
                    $this->constructorlines[] = "\t\t" . '$' . $this->classvarname . " = new " . $class->name . "();" .
                            " // ". get_string('errorwarningmsg', 'local_phpunit_testgenerator') . "\n";
                }
            }
        }
    }


    public function generate_prefunction_tests($pluginpath, $relativefile, $class) {
    }

    public function generate_postfunctions_tests($pluginpath, $relativefile, $class) {
    }

    public function generate($pluginpath, $relativefile, $class) {
        $filelines = array();

        // Get the top lines of the test file.
        $filelines[] =  $this->get_filetop($pluginpath, $relativefile, $class);

        // Constructor stuff -if this is a class.
        if (!empty($class->type)) {
            $this->make_constructorlines($class);
        }

        // Test functions we want before the the class methods/functions.
        if ($prefunctions = $this->generate_prefunction_tests($pluginpath, $relativefile, $class)) {
            $filelines = array_merge($filelines, $prefunctions);
        }

        foreach ($class->functions as $function) {

            if (!empty($class->type)) {
                // Ignore private and protected functions.
                if (!$function->accessmodifiers || !(in_array(T_PUBLIC, $function->accessmodifiers))) {
                    continue;
                }
                // Ignore all magic functions.
                if (preg_match('/^__/', $function->name)) {
                    continue;
                }
            }

            if ($functionlines = $this->generate_functiontestlines($function)) {
                $filelines = array_merge($filelines, $functionlines);
            }

        }

        // Test functions we want after the class methods/functions.
        if ($postfunctions = $this->generate_postfunctions_tests($pluginpath, $relativefile, $class)) {
            $filelines = array_merge($filelines, $postfunctions);
        }

        // Last lines in the test file.
        $filelines[] = $this->get_fileend($class);

        // Replace tabs with spaces to meet Moodle Development Guidelines.
        $filelines = preg_replace("|\t|", '    ', $filelines);

        return implode('', $filelines);
    }

    public function generate_pendinglines($onoroff = true) {
        $this->pendinglines = $onoroff;
    }

    public function generate_functiontestlines($function) {
        $methodlines = array();

        // Name for the variable - use the function name , it will be unique.
        $variablename = '$' . strtolower(preg_replace('/[\W_]/', '', $function->name));

        // PHP Doc for test function.
        $methodlines[] = "\t/**\n\t * Testing {$function->name}()\n \t*/\n";

        /* Function lines. */
        $testmethodname = 'test_' . $function->name;
        $methodlines[] = "\tpublic function $testmethodname() {\n";

        // TODO can we determine whether to reset the DB or not?
        $methodlines[] = "\n\t\t\$this->resetAfterTest(" . $this->dbreset . ");\n";  // Default reset after test.

        // We add in some pending lines to avoid tests being run before the functionality is checked. Can be overridden.
        if ($this->pendinglines) {
            $methodlines[] =  $this->get_pending_lines();
        }

        $argsnippet = '';
        $arglines = array();        // So we can add these lines in the logical place.
        foreach ($function->arguments as $arg) {
            if ($argmt = $arg[1]) {
                $arglines[] = "\t\t$argmt = null;\t// ". get_string('providevaluemsg', 'local_phpunit_testgenerator') . "\n";
                if ($argsnippet) {
                    $argsnippet .= ', ';
                }
                $argsnippet .= $argmt;
            }
        }

        if (!empty($function->class)) {
            if (in_array(T_STATIC, $function->accessmodifiers)) {
                if (empty($function->class->namespace)) {
                    $staticfuncall = '\\' . $function->fullname;
                } else {
                    $staticfuncall = '\\' . $function->class->namespace->name . '\\' . $function->fullname;
                }
                $methodlines = array_merge($methodlines, $arglines);
                $methodlines[] = "\t\t$variablename = $staticfuncall($argsnippet);\n";
            } else {
                // We need a class constructor.
                $methodlines = array_merge($methodlines, $this->constructorlines);
                $methodlines = array_merge($methodlines, $arglines);
                $methodlines[] = "\t\t" . $variablename . ' = $' . $this->classvarname . '->' . $function->name .
                        "($argsnippet);\n";
            }
        } else {
            $methodlines = array_merge($methodlines, $arglines);
            $methodlines[] = "\t\t$variablename = " . $function->name . "($argsnippet);\n";
        }

        // Add in a final assertion.
        $methodlines[] = "\t\t" . '$this->assertNotEmpty(' . $variablename . ", '" .
                get_string('provideassertionmsg', 'local_phpunit_testgenerator') . "');\n";
        $methodlines[] = "\t}\n\n";

        $methodlines = preg_replace("|\t|", '    ', $methodlines);

        return $methodlines;
    }

    protected function get_pending_lines() {
        $testneedscompleting = get_string('testneedscompleting', 'local_phpunit_testgenerator');
        $marktestincomplete = get_string('marktestincomplete', 'local_phpunit_testgenerator');
        return "
        // $marktestincomplete
        \$this->markTestIncomplete('$testneedscompleting');

";
    }

    /**
     * Tests if the class extends or implements another class.
     *
     * Borrowed code from the PHP docs - IsExtendsOrImplements() function.
     *
     * @param unknown $search
     * @param unknown $className
     * @return boolean
     */
    protected function is_instance_of($instancename, $filepath, $fullclassname) {
        global $CFG;    // Needed by the included files.

        // Load the class file if required.
        if (!class_exists($fullclassname)) {
            require_once($filepath);
        }

        $class = new \ReflectionClass($fullclassname);
        if(false === $class) {
            return false;
        }

        do {
            $name = $class->getName();
            if( $instancename == $name || (trim($instancename, '\\')) == $name) {
                return true;
            }
            $class = $class->getParentClass();
        } while( false !== $class );

        return false;
    }

    protected function get_fileend() {
        return "}\n\n";
    }

    protected function get_filetop($fullpluginpath, $relativefilepath, $class) {

        $thisyear = date('Y');

        $relativefilepath = ltrim($relativefilepath, '/');

        // Namespace for plugin.
        $testnamespace = substr_replace($fullpluginpath, '_', strpos($fullpluginpath, '/'), 1);

        if (empty($class->type)) {
            // We use the filename for the classname.
            $testclassname = basename($relativefilepath, '.php');
        } else {
            $testclassname = $class->name;
        }
        $testclassname .= '_test';

        return
        "<?php
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

namespace $testnamespace;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../$relativefilepath');

/**
 * Test script for $relativefilepath.
 *
 * @package     $testnamespace
 * @copyright   $thisyear
 * @author
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class $testclassname extends \advanced_testcase {

";
    }

}
