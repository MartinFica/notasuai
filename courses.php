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
        $test = $aux["hi"];

        // setting up excel
        $filename = "Grades".date('dmYHi');
        $tabs = array("Emarking");
        $title = "Hi, im dustin";
        $header = array("Curso", "Prueba", "Apellido(s)","Nombre","Pregunta 1","Pregunta 2","Pregunta 3","Pregunta 4");

        $data = array("hi","no","maybe");
        $descriptions = array("yes","bye","hello");
        $dates = array("nah","bruh","you");

        $testquery = "SELECT cc.fullname AS course,
                     e.name AS exam,
                     u.id,
                     u.idnumber,
                     u.lastname,
                     u.firstname,
                     cr.id criterionid,
                     cr.description,
                     l.id levelid,
                     IFNULL(l.score, 0) AS score,
                     IFNULL(c.bonus, 0) AS bonus,
                     IFNULL(l.score,0) + IFNULL(c.bonus,0) AS totalscore,
                     d.grade
                     FROM mdl_emarking e
                     INNER JOIN mdl_emarking_submission s ON (e.id = s.emarking)
                     INNER JOIN mdl_emarking_draft d ON (d.submissionid = s.id AND d.qualitycontrol=0)
                     INNER JOIN mdl_course cc ON (cc.id = e.course)
                     INNER JOIN mdl_user u ON (s.student = u.id)
                     INNER JOIN mdl_emarking_page p ON (p.submission = s.id)
                     LEFT JOIN mdl_emarking_comment c ON (c.page = p.id AND d.id = c.draft AND c.levelid > 0)
                     LEFT JOIN mdl_gradingform_rubric_levels l ON (c.levelid = l.id)
                     LEFT JOIN mdl_gradingform_rubric_criteria cr ON (cr.id = l.criterionid)
                     WHERE e.id = ?
                     ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";
        foreach($test as $id){
            $sql = $DB->get_records_sql($testquery, array($id));
        }

        notasuai_exporttoexcel($title, $header, $filename, $data, $descriptions, $dates, $tabs);
    }
    echo $OUTPUT->header();

    $testsform->display();

    echo $OUTPUT->footer();