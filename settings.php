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
 * Settings menu.
 *
 * @package   local_phpunit_testgenerator
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $ADMIN;  // For the IDE.
if ($hassiteconfig) {

    // Want my link to appear after the PHPUnit test link.
    $devtree = $ADMIN->locate('development');

    $insertbefore = '';
    $getnext = false;
    foreach ($devtree->children as $child) {
        if ($getnext) {
            $insertbefore = $child->name;
            break;
        }
        if ($child->name == 'toolphpunit') {
            $getnext = true;
        }
    }
    $mynode = new \admin_externalpage('local_phpunit_testgenerator',
            get_string('pluginname', 'local_phpunit_testgenerator'),
            new moodle_url('/local/phpunit_testgenerator/index.php'));

    $ADMIN->add('development', $mynode, $insertbefore);
}
