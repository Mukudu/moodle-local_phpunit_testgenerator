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
 * Local Library functions file.
 *
 * @package   local_phpunit_testgenerator
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Check if the named class is a moodle form
 *
 * @param string $filepath - the file path
 * @param string $fullclassname - the full class name
 * @return bool
 */
function is_moodleform($filepath, $fullclassname) {
    global $CFG;    // Needed by the included files.
    // Load the class file if required.
    if (!class_exists($fullclassname)) {
        require_once($filepath);
    }
    $class = new ReflectionClass($fullclassname);
    while ($parent = $class->getParentClass()) {
        if ($parent->getName() == 'moodleform') {
            return true;
        }
        $class = $parent;
    }
    return false;
}

/**
 * Checks if the file is a UI facing script
 *
 * Determines this based on the script requirirng the config.php.
 *
 * @param array $requires - list of file requires
 * @param string $pluginfilepath - the plugin file path
 * @return bool
 */
function is_ui_facing($requires, $pluginfilepath) {

    // Get the file depth to compare relative depth for config.php.
    $filedepth = count(explode('/', ltrim(dirname($pluginfilepath), '/')));
    foreach ($requires as $required) {
        $requiredfile = ltrim(trim($required->name, '"\''), '/');
        if (basename($requiredfile) == 'config.php') {
            // Let's double check by checking file depth - that this is the root config.php.
            if ($filedepth == count(explode('/', dirname($requiredfile)))) {
                return true;
            }
        }
        break;
    }
    return false;
}

/**
 * Loads all the sub plugins.
 *
 * @return array - of the subplugins.
 */
function load_subplugins() {
    // Load each of the sub-plugins.
    $allplugins = array();
    $subplugins = \core_component::get_plugin_list('phputestgenerator');
    foreach ($subplugins as $sptype => $sppath) {
        $classname = "\phputestgenerator_$sptype\\$sptype";
        if (!class_exists($classname)) {
            $classfile = "$sppath/classes/$sptype.php";
            require_once($classfile);
        }
        // This works as we do need to pass parameters to the constructor.
        $allplugins[$sptype] = new $classname;
    }
    return $allplugins;
}
