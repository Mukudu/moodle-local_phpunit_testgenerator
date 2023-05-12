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
 * English Language file.
 *
 * @package   local_phpunit_testgenerator
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'PHPUnit Tests Generator';
$string['plugindescription'] = 'This development plugin generates skeleton PHPUnit test files.';
$string['pluginrestriction'] = 'This plugin has to be run on the commandline as it generates files.';

$string['unrecognisedparms'] = 'WARNING: Unrecognised parameters passed';
$string['nopluginpath'] = 'Plugin path has not been specified';
$string['nopluginsubpath'] = 'No plugin sub-directory has been specified';
$string['plugindirmissing'] = 'Plugin directory not found';
$string['noversionfile'] = 'Version file is missing - is this a plugin?';

$string['helptext'] = '
Generates PHPUnit Test skeleton files.

Usage:
  php {$a} [--plugin-path=path/to/plugin] [--purge] [--help]

--plugin-path
        is required unless --help is specified and must exist.
--purge
        overwrite existing test files - use with caution
-h, --help          Print out this help

Example from Moodle root directory:
\$ php {$a} --plugin-path=local/housekeeping --purge

';
$string['testdirfail'] = 'Failed to create tests directory';
$string['testpathisfile'] = '"{$a}" is not a directory';
$string['mixedpurposefile'] = '"{$a}" is a mixed purpose file - skipping';
$string['interfacefile'] = '"{$a}" is an interface file - skipping';
$string['traitfile'] = '"{$a}" is a trait file - skipping';
$string['nofunctions'] = '"{$a}" has not got any functions - skipping';
$string['testfilenopurge'] = 'Test file exists for "{$a}", skipping';
$string['failedtosave'] = 'Failed to save "{$a}"';
$string['generationcomplete'] = 'Test Skeleton Generation complete.';

$string['testneedscompleting'] = 'This test needs to be completed';
$string['provideassertionmsg'] = 'Provide a better assertion here!';
$string['providevaluemsg'] = 'Provide a value here.';
$string['errorwarningmsg'] = 'This may cause errors as we do not know the arguments we need.';
$string['marktestincomplete'] = 'Mark this test as incomplete.';

