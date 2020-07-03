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
		$contextsystem = context_system::instance();
		
		$mform->addElement('header', 'nameforyourheaderelement', get_string('category', 'local_notasuai'));
		
        //if(is_siteadmin()){
          // get category
          $category_query = "SELECT id, name FROM {course_categories}";
        //}
        /*elseif (has_capability('local/notasuai:generatereport', $contextsystem)) {
          //Query to get the categorys of the secretary
        $category_query = "SELECT cc.*
                        FROM {course_categories} cc
                        INNER JOIN {role_assignments} ra ON (ra.userid = ?)
                        INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname = ?)
                        INNER JOIN {context} co ON (co.id = ra.contextid  AND  co.instanceid = cc.id  )";

        $queryparams = array($USER->id, "manager-report");*/
        // Get Records
        $category_sql = $DB->get_records_sql($category_query, array());
		
		$class_query = "SELECT id, fullname FROM {course} WHERE category = ?";
			
		$emarking_query ="SELECT * FROM {emarking} WHERE course = ?";
		

        foreach ($category_sql as $categories){
			$class_sql = $DB->get_records_sql($class_query, array($categories->id));
			if (count($class_sql)>0){
				$n = 0;
				foreach($class_sql as $course){
					$emarking_sql = $DB->get_records_sql($emarking_query, array($course->id));
					if (count($emarking_sql)>0){
						$n += 1;
					}
				}
				if($n > 0){
					$name = $categories->name;
					$cat[$categories->id] = $name;
				}
			}
        }

        // Category Input
        $mform->addElement("select", "category_id",get_string('categ', 'local_notasuai'), $cat);

        // Output button
        $mform->addElement('submit','category_submit',get_string('button1', 'local_notasuai'));
    }
	
	//}
}

class course extends moodleform{

    function definition()
    {
        global $DB, $CFG;
        $mform = $this->_form;
        $category = $this->_customdata;

        $mform->addElement ("hidden", "category_id", $category);
        $mform->setType ("category_id", PARAM_INT);
		$contextsystem = context_system::instance();

        //if(is_siteadmin()){
          // get courses
          $class_query = "SELECT id, fullname FROM {course} WHERE category = ?";
        //}
        /*elseif (has_capability('local/notasuai:generatereport', $contextsystem)) {
          //Query to get the categorys of the secretary
			$class_query = "SELECT c.*
                FROM {course} cc
                INNER JOIN {role_assignments} ra ON (ra.userid = ?)
                INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname = ?)
                INNER JOIN {context} co ON (co.id = ra.contextid  AND  co.instanceid = cc.id  )";*/

        // Get Records
        //create de list of courses with checkboxs
        $mform->addElement('header', 'nameforyourheaderelement', get_string('course', 'local_notasuai'));
        $class_sql = $DB->get_records_sql($class_query, array($category));
        $this->add_checkbox_controller(1);

        $th_title = get_string("course", "local_notasuai");
        $mform->addElement('html', '<table class="table table-striped table-condensed table-hover">');
        $mform->addElement('html', '<thead>');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', '<th>#');
        $mform->addElement('html', '</th>');
        $mform->addElement('html', '<th>'.$th_title);
        $mform->addElement('html', '</th>');
        $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</thead>');
        $mform->addElement('html', '<tbody>');

		$emarking_query ="SELECT * FROM {emarking} WHERE course = ?";
        $counter = 1;
        foreach ($class_sql as $class) {
            $name = $class->fullname;
            $course[$class->id] = $name;
            $id = $class->id;
			
			$emarking_sql = $DB->get_records_sql($emarking_query, array($id));
						
			if (count($emarking_sql)>0){
			    $mform->addElement('html', '<tr>');
				$mform->addElement('html', '<td>'.$counter.'</td>');
				$mform->addElement('html', '<td>');

				$mform->addElement('advcheckbox', $id, $name, null, array('group' => 1), $id);

				$mform->addElement('html', '</td>');
				$mform->addElement('html', '</tr>');
				$counter++;
			}
        }


        $mform->addElement('html', '</tbody>');
        $mform->addElement('html', '</table>');

        $mform->addElement ("hidden", "action", "redirect");
        $mform->setType ("action", PARAM_TEXT);

        // Output button
        $mform->addElement('submit','class_submit',get_string('button2', 'local_notasuai'));
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

        $mform->addElement('header', 'nameforyourheaderelement', get_string('tests', 'local_notasuai'));
        $th_title = get_string("course", "local_notasuai");
        $mform->addElement('html', '<table class="table table-striped table-condensed table-hover">');
        $mform->addElement('html', '<thead>');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', '<th>#');
        $mform->addElement('html', '</th>');
        $mform->addElement('html', '<th>'.$th_title);
        $mform->addElement('html', '</th>');

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


		$n_tests = 0;
		foreach ($classesarray as $class){
			$ct = (count($class)-2)/2;
			if ($n_tests <= $ct){
				$n_tests = $ct;
			}

		}

        /*NUM TEST HEAD TABLE*/

		/*for($i = 1; $i <= $n_tests; $i++){

            $mform->addElement('html', '<th>Emarking '.$i);
            //$mform->add_checkbox_controller($test->id, "Yo ".$test->id, array('style' => 'font-weight: bold;'));
            $mform->addElement('html', '</th>');
        }

        $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</thead>');*/


		$checkbox_controller = 1;
		while ($checkbox_controller <= $n_tests){
            $mform->addElement('html', '<th>');
			$this->add_checkbox_controller($checkbox_controller, "Emarking ".$checkbox_controller, array('style' => 'font-weight: bold;'));
            $mform->addElement('html', '</th>');
			$checkbox_controller += 1;
		}

        $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</thead>');

        /*TABLE HEAD END*/
        /*BODY*/
        $mform->addElement('html', '<tbody>');

		$submited_query = "SELECT status
                FROM {emarking_submission}
                WHERE emarking = ?";
				
        $n_courses = 1;
        foreach ($classesarray as $class){

            $mform->addElement('html', '<tr>');
            $mform->addElement('html', '<td>'.$n_courses.'</td>');


            $slice = array_slice($class,2);
            if (count($slice) > 0){
                $name = $class[0];
                $id = $class[1];
                $status = $class ? 1 : 0;
                $testsarray = array();
                $m=1;
                $o=1;
				
                $mform->addElement('html', '<td>'.$name.'</td>');

                for ($n = 0; $n < count($slice); $n += 2){
                    
					$submited = 0;
					$submited_sql = $DB->get_records_sql($submited_query, array($slice[$n]));
					foreach($submited_sql as $status1){
						foreach($status1 as $status2){
							if ($status2 >= 20){
								$submited++;
							}
						}
					}
					
					$mform->addElement('html', '<td>');
					if ($submited>0){
						$mform->addElement('advcheckbox', $m . " " .$slice[$n+1], $slice[$n+1], null, array('group' => $m),$slice[$n]);
					}
					$mform->addElement('html', '</td>');


                    //$testsarray[] =& $mform->createElement('advcheckbox', $m . " " .$slice[$n+1], $slice[$n+1], null, array('group' => $m),$slice[$n]);
                    $mform->setDefault($name, $status);
                    $m++;
                    if ($m > $o){
                        $o=$m;
                    }
                }
                //$mform->addGroup($testsarray, "hi", $name, array(''), true);
            }




            $mform->addElement('html', '</tr>');
            $n_courses++;
        }

        $mform->addElement('html', '</tbody>');
        $mform->addElement('html', '</table>');

        // Output button
        $this->add_action_buttons(false,get_string('download', 'local_notasuai'));
    }

}

/*
class excel_export extends moodleform{
	

    function definition()
    {

        global $DB, $CFG;
        // Validate that we have a rubric associated
        list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) =
            emarking_validate_rubric($context, false, false);
        // Calculate levels indexes in forced formative feedback (no grades)
        $levelsindex = array();
        foreach($definition->rubric_criteria as $crit) {
            $total = count($crit['levels']);
            $current = 0;
            foreach($crit['levels'] as $lvl) {
                $current++;
                $levelsindex[$lvl['id']] = $total - $current + 1;
            }
        }
	
		$emarking = $this->_customdata;

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
                     LEFT JOIN mdl_gradingform_rubric_criteria cr ON (cr.id = l.criterionid)";
                     
                     //ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";
                    //WHERE e.id = ?
					
		$n = 0;
		foreach ($idemarking as $id){
			if ($id > 0){
				if ($n == 0){
					$testquery = $testquery . " WHERE e.id = " . $id;
					$n += 1;
				}
				else{
					$testquery = $testquery . " AND e.id = " . $id;
				}
			}
		}
		$testquery = $testquery . " ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";

        // Get data and generate a list of questions.
        $rows = $DB->get_recordset_sql($testquery);
			
        // Make a list of all criteria
        $questions = array();
        foreach ($rows as $row) {
            if (array_search($row->description, $questions) === false && $row->description) {
                $questions [] = $row->description;
            }
        }
        // Starting the loop
        $current = 0;
        $laststudent = 0;
        // Basic headers that go everytime
        $headers = array(
            '00course' => get_string('course'),
            '01exam' => get_string('exam', 'mod_emarking'),
            '02idnumber' => get_string('idnumber'),
            '03lastname' => get_string('lastname'),
            '04firstname' => get_string('firstname'));
        $tabledata = array();
        $data = null;
        // Get dataset again
        $rows = $DB->get_recordset_sql($testquery);
        // Now iterate through students
        $studentname = '';
        $lastrow = null;
        foreach ($rows as $row) {
            // The index allows to sort final grade at the end (99grade)
            $index = 10 + array_search($row->description, $questions);
            $keyquestion = $index . "" . $row->description;
            // If the index is not there yet we create it
            if (! isset($headers [$keyquestion]) && $row->description) {
                $headers [$keyquestion] = $row->description;
            }
            // If we changed student
            if ($laststudent != $row->id) {
                if ($laststudent > 0) {
                    $tabledata [$studentname] = $data;
                    $current ++;
                }
                $data = array(
                    '00course' => $row->course,
                    '01exam' => $row->exam,
                    '02idnumber' => $row->idnumber,
                    '03lastname' => $row->lastname,
                    '04firstname' => $row->firstname);
                // If it's not formative feedback, add the grade as a final column
                if(!isset($CFG->emarking_formativefeedbackonly) || !$CFG->emarking_formativefeedbackonly) {
                    $data['99grade'] = $row->grade;
                }
                $laststudent = intval($row->id);
                $studentname = $row->lastname . ',' . $row->firstname;
            }
            // Store the score (including bonus) or level index in criterion
            if ($row->description) {
                if(isset($CFG->emarking_formativefeedbackonly) && $CFG->emarking_formativefeedbackonly) {
                    $data [$keyquestion] = $levelsindex[$row->levelid];
                } else {
                    $data [$keyquestion] = $row->totalscore;
                }
            }
            $lastrow = $row;
        }
        // Add the last row
        $studentname = $lastrow->lastname . ',' . $lastrow->firstname;
        $tabledata [$studentname] = $data;
        // Add the grade if it's summative feedback
        if(!isset($CFG->emarking_formativefeedbackonly) || !$CFG->emarking_formativefeedbackonly) {
            $headers ['99grade'] = get_string('grade');
        }
        ksort($tabledata);
        // Now pivot the table to form the Excel report
        $current = 0;
        $newtabledata = array();
        foreach ($tabledata as $data) {
            foreach ($questions as $q) {
                $index = 10 + array_search($q, $questions);
                if (! isset($data [$index . "" . $q])) {
                    $data [$index . "" . $q] = '0.000';
                }
            }
            ksort($data);
            $current ++;
            $newtabledata [] = $data;
        }
        $tabledata = $newtabledata;
        // The file name of the report
        $excelfilename = clean_filename($emarking->name . "-grades.xls");
        // Save the data to Excel
        emarking_save_data_to_excel($headers, $tabledata, $excelfilename, 5);
    }

    function emarking_save_data_to_excel($headers, $tabledata, $excelfilename, $colnumber = 5) {
        // Creating a workbook.
        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers.
        $workbook->send($excelfilename);
        // Adding the worksheet.
        $myxls = $workbook->add_worksheet(get_string('emarking', 'mod_emarking'));
        // Writing the headers in the first row.
        $row = 0;
        $col = 0;
        foreach (array_values($headers) as $d) {
            $myxls->write_string($row, $col, $d);
            $col ++;
        }
        // Writing the data.
        $row = 1;
        foreach ($tabledata as $data) {
            $col = 0;
            foreach (array_values($data) as $d) {
                if ($row > 0 && $col >= $colnumber) {
                    $myxls->write_number($row, $col, $d);
                } else {
                    $myxls->write_string($row, $col, $d);
                }
                $col ++;
            }
            $row ++;
        }
        $workbook->close();
    }
}
*/