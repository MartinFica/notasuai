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
    require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
    require_once ($CFG->libdir."/formslib.php");
    require_once("$CFG->libdir/excellib.class.php");
    require_once($CFG->dirroot . '/local/notasuai/locallib.php');


class category extends moodleform {

    function definition(){

        global $DB, $CFG;
        $mform = $this->_form;

        // get category
        $query = "SELECT id, name 
                    FROM {course_categories}";

        // Get Records
        $sql = $DB->get_records_sql($query, array(1));

        foreach ($sql as $categories){
            $name = $categories->name;
            $cat[$categories->id] = $name;
        };

        // Category Input
        $mform->addElement("select", "category_id",get_string('categ', 'local_notasuai'), $cat);

        // Output button
        $mform->addElement('submit','category_submit',get_string('button1', 'local_notasuai'));
    }

}

class course extends moodleform
{

    function definition()
    {
        global $DB, $CFG;
        $nform = $this->_form;
        $category = $this->_customdata;

        $nform->addElement ("hidden", "category_id", $category);
        $nform->setType ("category_id", PARAM_INT);

        $class_sql = "SELECT id, fullname 
                    FROM {course}
                    WHERE category = ?";
        // Get Records
        $class_query = $DB->get_records_sql($class_sql, array($category));
        $this->add_checkbox_controller(1, "All", array('style' => 'font-weight: bold;'), 1);

        $emarking_sql ="SELECT *
                    FROM {emarking}
                    WHERE course = ?";

        foreach ($class_query as $class) {
            $name = $class->fullname;
            $course[$class->id] = $name;
            $id = $class->id;
            $emarking_query = $DB->get_records_sql($emarking_sql, array($id));
            if (count($emarking_query)>0){
                $nform->addElement('advcheckbox', $id, $name, null, array('group' => 1), $id);
            }
        }

        $nform->addElement ("hidden", "action", "redirect");
        $nform->setType ("action", PARAM_TEXT);

        // Output button
        $nform->addElement('submit','class_submit',get_string('button2', 'local_notasuai'));
    }

    /*	function validation ($data, $files){

            global $DB;
            $errors = array();

            if (isset($data["course_id"]) && !empty($data["course_id"]) && $data["course_id"] != "" && $data["course_id"] != null ){
            }else{
                $errors["course_id"] =  get_string('error1', 'local_notasuai');
            }
            return $errors;
        }*/
}

class tests extends moodleform {

    function definition(){

        global $DB, $CFG;

        $mform = $this->_form;
        $courses = $this->_customdata;

        $coursesstring = json_encode($courses);
        $mform->addElement ("hidden", "courses", $coursesstring);
        $mform->setType ("courses", PARAM_TEXT);

        $class_sql = "SELECT id, fullname, shortname 
                FROM {course}
                WHERE id = ?";
        $test_sql = "SELECT id, name
                FROM {emarking}
                WHERE course = ?";
        $num = 0;
        $classesarray = array();
        foreach($courses as $id){
            // Get Records
            $class_query = $DB->get_records_sql($class_sql, array($id));
            $test_query = $DB->get_records_sql($test_sql, array($id));
            foreach($class_query as $class){
                $aux = array();
                array_push($aux,$class->fullname,$class->id);
                foreach($test_query as $test){
                    array_push($aux,$test->id, $test->name);
                }
                $classesarray[$num] = $aux;
                $num++;
            }
        }

        $this->add_checkbox_controller('1', "Todas las pruebas 1", array('style' => 'font-weight: bold;'));
        $this->add_checkbox_controller('2', "Todas las pruebas 2", array('style' => 'font-weight: bold;'));
        $this->add_checkbox_controller('3', "Todas las pruebas 3", array('style' => 'font-weight: bold;'));
        $this->add_checkbox_controller('4', "Todas las pruebas 4", array('style' => 'font-weight: bold;'));

        foreach ($classesarray as $class){
            $slice = array_slice($class,2);
            if (count($slice) > 0){
                $name = $class[0]; $id = $class[1];
                $status = $class ? 1 : 0;
                $testsarray = array();
                $m=1;
                $o=1;
                for ($n = 0; $n < count($slice); $n += 2){
                    $testsarray[] =& $mform->createElement('advcheckbox', $m . " " .$slice[$n+1], $slice[$n+1], null, array('group' => $m),$slice[$n]);
                    $mform->setDefault($name, $status);
                    $m++;
                    if ($m > $o){
                        $o=$m;
                    }
                }
                $mform->addGroup($testsarray, "hi", $name, array(''), true);
            }
        }

        // Output button
        $this->add_action_buttons(false,"Descargar Excel");
    }

}

class excel_export extends moodleform
{

    function definition()
    {

        global $DB, $CFG;

        $test = $this->_customdata;

        /*$types = array();
        foreach ($formdata->sesstype as $sesstype=>$type){
            $types[] = $sesstype;
        }
        list($selectedtypes, $paramsesstypes) = $DB->get_in_or_equal($types);*/
        //$parametros = array_merge($paramsesstypes, array($courseid));
        //excel parameters
        $filename = "_attendances_".date('dmYHi');
        $tabs = array("Attendances", "Summary");
        $title = "Hi, im dustin";
        $header = array(array("LastName", "FirstName", "Email"), array("LastName", "FirstName", "Email"));
        $header[1] = array_merge($header[1], array("Total percentage"));
        $data = array(array(), array());
        $descriptions = array(array(), array());
        $dates = array(array(), array());
        //Select all students from the last list
        $enrolincludes = explode("," ,$CFG->paperattendance_enrolmethod);
        //list($enrolmethod, $paramenrol) = $DB->get_in_or_equal($enrolincludes);
        //$parameters = array_merge(array($course->id), $paramenrol);

        notasuai_exporttoexcel($title, $header, $filename, $data, $descriptions, $dates, $tabs);


        // get tests
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
        foreach ($test as $testid){
            $sql = $DB->get_records_sql($testquery, array($testid));
        }







    }
}
