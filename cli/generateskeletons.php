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

/**
 * CLI script.
 *
 * @package   local_phpunit_testgenerator
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/local_phpunit_testgenerator_file.php');
require_once(__DIR__ . '/../classes/phputestgeneratorbase.php');
require_once(__DIR__ .'/../locallib.php');
global $CFG;
require_once($CFG->libdir . '/clilib.php');

$testsdir = 'tests/';       // Trailing slash required.

// TODO Turn this into settings.  Maybe???
$excludefiles = array();
$excludedirs = array('/db');
// Add the tests folder to the directory exclusions.
$excludedirs[] = '/' . trim($testsdir, '/');

// Test Moodle logo.
cli_logo();

list($options, $unrecognized) = cli_get_params(
    [
        'help'                 => false,
        'plugin-path'          => false,
        'purge'                => false
    ],
    [
        'h' => 'help',
    ]
);

if ($unrecognized) {
    echo get_string('unrecognisedparms', 'local_phpunit_testgenerator') . "\n";
    // exit(0);
}

// Check the plugin directory.
$fullpluginpath = '';
if (empty($options['plugin-path'])) {
    echo "\n". get_string('nopluginpath', 'local_phpunit_testgenerator') . "\n";
} else {
    // Check for a sub directory e.g. local/myplugin.
    $pos = strpos($options['plugin-path'], '/');
    if ($pos === false) {       // Should not happen.
        echo "\n" . get_string('nopluginsubpath', 'local_phpunit_testgenerator') . "\n";
        $options['plugin-path'] = false;
    } else {
        // Clear leading or trailing slashes.
        $options['plugin-path'] = trim($options['plugin-path'], '/');
        $fullpluginpath = $CFG->dirroot . "/" . $options['plugin-path'] . "/";
        if (!file_exists($fullpluginpath)) {
            echo "\n". get_string('plugindirmissing', 'local_phpunit_testgenerator') . "\n";
            $options['plugin-path'] = false;
        } else {
            $versionfile = $fullpluginpath . "version.php";
            if (file_exists($versionfile)) {
                require_once($versionfile);
            } else {
                echo "\n" . get_string('noversionfile', 'local_phpunit_testgenerator') . "\n";
                $options['plugin-path'] = false;
            }
        }
    }
}

if (!empty($options['help']) || empty($options['plugin-path'])) {
    $thisfile = $argv[0];
    $help = get_string('helptext', 'local_phpunit_testgenerator', $thisfile);
    echo $help;
    exit(0);
}

// Ensure we have a tests directory to write the test files to.
$testpath = $fullpluginpath . $testsdir;
if (!file_exists($testpath)) {
    if (!mkdir($testpath)) {
        die(get_string('testdirfail', 'local_phpunit_testgenerator'));
    }
} else if (!is_dir($testpath)) {
    die(get_string('testpathisfile', 'local_phpunit_testgenerator'));
}

// If we got this far - we will be needing our sub-plugins.
$extensions = load_subplugins();

// Now let's get all possible testable php files.
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullpluginpath));
$filedets = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
foreach ($filedets as $files) {
    foreach ($files as $file) {

        // Relative path name.
        $relativefile = str_replace($fullpluginpath, '/', $file);

        // Is it in excluded directory?
        $relativedir = dirname($relativefile);
        if (!in_array($relativedir, $excludedirs)) {
            while (dirname($relativedir) != '/') {
                $relativedir = dirname($relativedir);
            }
        } else {
            continue;
        }

        // Is it in the excluded files list?
        if (in_array($relativefile, $excludefiles)) {
            continue;
        }

        // Update test path.
        $filetestpath = $testpath;
        if (dirname($relativefile) != '/') {
            $filetestpath = $testpath . str_replace('/', '_', trim(dirname($relativefile), '/')) . '_';
        }

        // This is why we require local/moodlecheck - parse our file.
        $parsefile = new local_phpunit_testgenerator_file($file);

        $functions = $parsefile->get_functions();
        // If there are no functions - then we have nothing to do - most UI facing scripts will have no functions.
        if (empty($functions)) {
            echo get_string('nofunctions', 'local_phpunit_testgenerator', $relativefile) . "\n";
            continue;
        }

        // Check if this is a Moodle UI facing script.
        if ($requires = $parsefile->get_requires()) {
            if (is_ui_facing($requires, str_replace($CFG->dirroot, '', $file))) {
                continue;
            }
        }

        $artifacts = $parsefile->get_artifacts();
        $interfaces = $artifacts[T_INTERFACE];
        $traits = $artifacts[T_TRAIT];
        $classes = $artifacts[T_CLASS];

        // Check for mixed purposes files - we cannot deal with them yet.
        if (! ((empty($classes) == empty($interfaces)) ? empty($classes) : empty($traits)) ) {
            echo get_string('mixedpurposefile', 'local_phpunit_testgenerator', $relativefile) . "\n";
            continue;
        }

        // Check if we are dealing with interface/s - No tests.
        if (count($interfaces)) {
            echo get_string('interfacefile', 'local_phpunit_testgenerator', $relativefile) . "\n";
            continue;
        }

        // TODO maybe we can do something with this.
        // Using getMockForTrait() method?
        if (count($traits)) {
            echo get_string('traitfile', 'local_phpunit_testgenerator', $relativefile) . "\n";
            continue;
        }

        // Connect methods with classes - special for non class functions.
        $noclass = new \stdClass();
        $noclass->name = 'nonclass';
        $classes[] = $noclass;
        $classmethods = array();
        foreach ($functions as $function) {
            if ($function->class) {
                $classmethods[$function->class->name][] = $function;
            } else {
                $classmethods[$noclass->name][] = $function;
            }
        }

        if (count($classes)) {
            foreach ($classes as &$class) {     // Each valid class gets its own test file.
                $filelines = array();

                // Has the class got any functions?
                if (empty($classmethods[$class->name])) {
                    continue;
                }

                $handler = new phputestgeneratorbase();        // Default handler.
                if (empty($class->type)) {  // Not a real class.
                    // Default test file name.
                    $testfilename = $filetestpath . basename($file, '.php') . '_test.php';
                } else {

                    // Namespaced class name.
                    if ($class->namespace = $parsefile->get_namespace()) {
                        $class->fullname = '\\' . $class->namespace->name . '\\' . $class->name;
                    } else {
                        $class->fullname = '\\' . $class->name;
                    }

                    // Find a sub-plugin handler if there is one.
                    foreach ($extensions as $extension) {
                        if ($extension->is_handler($file, $class->fullname)) {
                            $handler = $extension;
                            break;
                        }
                    }

                    // Check if we are dealing with a moodle_form - no tests for that.
                    if (is_moodleform($file, $class->fullname)) {
                        continue;
                    }

                    // Class test file name.
                    $testfilename = $filetestpath . $class->name . '_test.php';
                }

                // Check for existing test file.
                if (!$options['purge'] && file_exists($testfilename)) {
                    echo get_string('testfilenopurge', 'local_phpunit_testgenerator', $relativefile) . "\n";
                    continue;
                }

                // Store the relevant methods with the class.
                $class->functions = $classmethods[$class->name];

                $filelines[] = $handler->generate($options['plugin-path'], $relativefile, $class, $testfilename);

                if (file_put_contents($testfilename, $filelines) === false) {
                    echo get_string('failedtosave', 'local_phpunit_testgenerator', $relativefile) . "\n";
                }

            }
        }
    }
}

echo "\n" . get_string('generationcomplete', 'local_phpunit_testgenerator') . "\n";
