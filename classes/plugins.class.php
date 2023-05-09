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
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class phputestgeneratorplugin {

    protected $constructorlines = array();

    protected $classvarname = '';

    abstract function get_name();

    abstract function is_handler($filepath, $fullclassname);

    protected function make_class_varname($classname) {
        if (!$this->classvarname) {
            $this->classvarname = strtolower(preg_replace('/[\W_]/', '', $classname));
        }
    }

    protected function make_constructorlines($function) {
        $argsnippet = '';
        if (count($function->arguments)) {
            foreach ($function->arguments as $arg) {
                if ($argmt = $arg[1]) {
                    $this->constructorlines[] = "\t\t$argmt = null;\t// Provide a value here.";
                    if ($argsnippet) {
                        $argsnippet .= ', ';
                    }
                    $argsnippet .= $argmt;
                }
            }
        }
        $this->make_class_varname($function->class->name); // Create a class variable name.
        $this->constructorlines[] = "\n\t\t" . '$' . $this->classvarname . " = new " .
                $function->class->name . "($argsnippet);\n\n";
    }

    protected function get_classcontructorlines($function) {
        if (empty($this->constructorlines)) {
            // TODO we could use Reflector class maybe to find arguments for constructor?
            $classname = $function->class->name;
            $this->make_class_varname($classname);
            return array("\t\t" . '$' . $this->classvarname . " = new $classname();" .
                " // This may cause errors as we do not know the arguments we need.\n");
        } else {    // We had a class constructor method.
            return $this->constructorlines ;
        }
    }

    public function generate_functiontestlines($function) {
        $methodlines = array();

        $isclassmethod = !empty($function->class);
        $isstatic = false;

        if ($isclassmethod) {

            // Ignore private and protected functions - including private constructors
            if (!$function->accessmodifiers || !(in_array(T_PUBLIC, $function->accessmodifiers))) {
                return $methodlines;
            }

            // We need to the constructor for our tests - if it is not private.
            if ($function->name == '__construct') {
                 $this->make_constructorlines($function);
                // No output yet.
                return $methodlines;
            } else {
                // Ignore all other magic functions.
                if (preg_match('/^__/', $function->name)) {
                    return $methodlines;
                }
            }
            $isstatic = in_array(T_STATIC, $function->accessmodifiers);
        }

        // Name for the variable.
        $variablename = '$' . strtolower(preg_replace('/[\W_]/', '', $function->name));

        // PHP Doc for test function.
        $methodlines[] = "\t/**\n\t * Testing {$function->name}()\n \t*/\n";

        /* Function lines. */
        $testmethodname = 'test_' . $function->name;
        $methodlines[] = "\tpublic function $testmethodname() {\n";

        // We add in some pending lines to avoid tests being run before the functionality is checked. Can be overridden
        $methodlines[] =  $this->get_pending_lines();

        $argsnippet = '';
        foreach ($function->arguments as $arg) {
            if ($argmt = $arg[1]) {
                $methodlines[] = "\t\t$argmt = null;\t// Provide a value here.\n";
                if ($argsnippet) {
                    $argsnippet .= ', ';
                }
                $argsnippet .= $argmt;
            }
        }

        if ($isclassmethod) {
            if ($isstatic) {
                if (empty($function->class->namespace)) {
                    $staticfuncall = '\\' . $function->fullname;
                } else {
                    $staticfuncall = '\\' . $function->class->namespace->name . '\\' . $function->fullname;
                }
                $methodlines[] = "\t\t$variablename = $staticfuncall($argsnippet);\n";
            } else {
                // We need a class constructor.
                $methodlines = array_merge($methodlines, $this->get_classcontructorlines($function));
                $methodlines[] = "\t\t" . $variablename . ' = $' . $this->classvarname . '->' . $function->name . "($argsnippet);\n";
            }
        } else {
            $methodlines[] = "\t\t$variablename = " . $function->name . "($argsnippet);\n";
        }

        // Add in a final assertion.
        $methodlines[] = "\t\t" . '$this->assertNotEmpty(' . $variablename . ", 'Provide a better assertion here!');\n";
        $methodlines[] = "\t}\n\n";

        $methodlines = preg_replace("|\t|", '    ', $methodlines);

        return $methodlines;
    }

    protected function get_pending_lines() {
        return "
        \$this->resetAfterTest(false);
        // Mark this test as incomplete.
        \$this->markTestIncomplete('This test needs to be completed');

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

}
