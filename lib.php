<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module emarking
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the emarking specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 * 
 * @package local_paperattendance
 * @copyright 2016 Hans Jeria (hansjeria@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function local_notasuai_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
	global $DB, $CFG, $USER;
	
	require_login();
	$filename = array_pop($args);
	$itemid = array_pop($args);

	$fs = get_file_storage();
	if (! $file = $fs->get_file($context->id, 'local_notasuai', $filearea, $itemid, '/', $filename)) {
		echo $context->id . ".." . $filearea . ".." . $itemid . ".." . $filename;
		echo "File really not found";
		send_file_not_found();
	}
	send_file($file, $filename);
}