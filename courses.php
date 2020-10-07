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
	require_once($CFG->dirroot . '/local/notasuai/locallib.php');

    global $DB, $PAGE, $OUTPUT, $USER;

	$context = context_system::instance();

	require_login();
    if (isguestuser()){
        die();
    }

    $url_view= '/local/notasuai/view.php';
	$url = new moodle_url($url_view);

    // Possible actions -> view, add. Standard is view mode
    $view = optional_param("view", "category", PARAM_TEXT);
    $courses = optional_param("courses", null, PARAM_TEXT);
	$exam_check = optional_param('exam_check', null, PARAM_TEXT);
	$exam_blah = optional_param('exams', null, PARAM_TEXT);
	
	if(!is_null($exam_blah)){
		$exam_aux = json_decode($exam_blah);
		//$aux =(array) $exam_aux;
		if ($exam_check == 'export'){
			//$martin = Array ( [0] => 2 [1] => 4 [2] => 5 );
			export_to_excel($exam_aux, $context);
		}		
	}

	// Array ( [0] => 2 [1] => 4 [2] => 5 )
	
	$PAGE->set_context($context);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_pagelayout("standard");
	
    $PAGE->set_title(get_string('title', 'local_notasuai'));
    $PAGE->set_heading(get_string('heading', 'local_notasuai'));
	
	$arcourses = json_decode($courses);
    $classes = (array) $arcourses;
    $testsform = new tests(null, $classes);

	if ($testsform->is_cancelled()) {
		redirect(new moodle_url("/local/notasuai/index.php"));
	}
	else if($formdata = $testsform->get_data()) {
		
        //form data analysis
		$exams = array();
        foreach ($formdata->emarking_checkbox as $valor) { 

            if($valor != 0 || $valor != ''){
				$exams[] = $valor;
            }
        }
		
		$exam_string = json_encode($exams);		
		redirect(new moodle_url("/local/notasuai/courses.php", array('exam_check' => 'export', 'exams' => $exam_string, 'courses' => $courses)));
	}

    echo $OUTPUT->header();

	$testsform->display();
		
    echo $OUTPUT->footer();
	
	
	?>	
	
	