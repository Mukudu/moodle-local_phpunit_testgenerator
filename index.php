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
 * Index file.
 *
 * @package   local_phpunit_testgenerator
 * @copyright 2022 - 2023 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $PAGE, $SITE, $OUTPUT;  // For the IDE.

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$pluginname = get_string('pluginname', 'local_phpunit_testgenerator');
$version = get_config('local_phpunit_testgenerator', 'version');
$PAGE->set_heading($SITE->fullname . " : " . $pluginname);
$PAGE->set_title($pluginname);
$PAGE->set_url('/local/phpunit_testgenerator/index.php');

echo $OUTPUT->header();
echo $OUTPUT->heading("$pluginname - Version: $version");

$message = \html_writer::div(html_writer::tag('p', get_string('plugindescription', 'local_phpunit_testgenerator')));
$message .= \html_writer::div(html_writer::tag('p', get_string('pluginrestriction', 'local_phpunit_testgenerator')));

echo $OUTPUT->box($message);

echo $OUTPUT->footer();
