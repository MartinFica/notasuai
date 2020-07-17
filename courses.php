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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package    local
 * @subpackage notasuai
 * @copyright  2019  Martin Fica (mafica@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require(__DIR__.'/../../config.php');
    require_once ($CFG->dirroot."/local/notasuai/forms.php");

    global $DB, $PAGE, $OUTPUT, $USER;

    $url_view= '/local/notasuai/view.php';

    $context = context_system::instance();
    $url = new moodle_url($url_view);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_pagelayout("standard");

    // Possible actions -> view, add. Standard is view mode
    $view = optional_param("view", "category", PARAM_TEXT);
    $courses = optional_param("courses", null, PARAM_TEXT);

    require_login();
    if (isguestuser()){
        die();
    }

    $PAGE->set_title(get_string('title', 'local_notasuai'));
    $PAGE->set_heading(get_string('heading', 'local_notasuai'));

    $arcourses = json_decode($courses);
    $classes = (array) $arcourses;
    $testsform = new tests(null, $classes);
    if ($tests = $testsform->get_data()){
        $aux = (array)$tests;

		print_r($tests);
		echo "<br>";
        //gets Emarking's Id's
        //$idemarking = $aux["hi"];

		export_to_excel($idemarking, $context);
		
    }

    echo $OUTPUT->header();

    $testsform->display();
	
	print_r($aux);

    echo $OUTPUT->footer();
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	