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
		
		print_r($contextsystem);
		
		$mform->addElement('header', 'nameforyourheaderelement', get_string('category', 'local_notasuai'));
		
        if(is_siteadmin()){
          // get category
          $category_query = "SELECT id, name FROM {course_categories}";
		  $category_sql = $DB->get_records_sql($category_query, array());
        }
        elseif (has_capability('local/notasuai:generatereport', $contextsystem)) {
          //Query to get the categorys of the secretary
			$category_query = "SELECT cc.*
                FROM {course_categories} cc
                INNER JOIN {role_assignments} ra ON (ra.userid = ?)
                INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname = ?)
                INNER JOIN {context} co ON (co.id = ra.contextid  AND  co.instanceid = cc.id  )";

			$queryparams = array($USER->id, "manager-report");
			// Get Records
			$category_sql = $DB->get_records_sql($category_query, $queryparams);
			
			print_r($category_sql);
		}
		
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
}

class course extends moodleform{

    function definition()
    {
        global $DB, $CFG, $OUTPUT;
        $mform = $this->_form;
        $category = $this->_customdata;

        $mform->addElement ("hidden", "category_id", $category);
        $mform->setType ("category_id", PARAM_INT);
		$contextsystem = context_system::instance();

        if(is_siteadmin()){
          // get courses
          $class_query = "SELECT id, fullname FROM {course} WHERE category = ?";
		  $class_sql = $DB->get_records_sql($class_query, array($category));
        }
        elseif (has_capability('local/notasuai:generatereport', $contextsystem)) {
          //Query to get the categorys of the secretary
			$class_query = "SELECT c.*
                FROM {course} cc
                INNER JOIN {role_assignments} ra ON (ra.userid = ?)
                INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname = ?)
                INNER JOIN {context} co ON (co.id = ra.contextid  AND  co.instanceid = cc.id  )";
			
			$queryparams = array($USER->id, "manager-report");
			$class_sql = $DB->get_records_sql($class_query, $queryparams);
		}

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

	function validation($data,$files) {
		
		global $DB, $CFG, $OUTPUT;
        $errors = array();
		
		$confirmed = 0;
		$N = count($data);
		$n = 0;

		foreach($data as $dt){
			if (($n > 1) && ($n < $N-4)){
				if ($dt > 0){
					$confirmed++;
				}
			}
			$n++;
		}

        if ($confirmed != 0){
		}else{
            $errors["class_submit"] = get_string('error1', 'local_notasuai');
        }

		return $errors;
		
    }
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

        if(is_siteadmin()){
			$class_sql = "SELECT id, fullname, shortname 
                FROM {course}
                WHERE id = ?";
		    $test_sql = "SELECT id, name
                FROM {emarking}
                WHERE course = ?";
        }
		
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
		$checkboxcount = 0; 
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
					$name_checkbox ="emarking_checkbox[".$checkboxcount."]"; 
					if ($submited>0){
						$mform->addElement('advcheckbox', $name_checkbox, $slice[$n+1], null, array('group' => $m),$slice[$n]);
					}
					else{
						$mform->addElement('advcheckbox', $name_checkbox, $slice[$n+1], null, array('group' => $m,'disabled'),$slice[$n]);
					}
					
					$checkboxcount++;
					$mform->addElement('html', '</td>');

                    $m++;
                    if ($m > $o){
                        $o=$m;
                    }
                }
            }

            $mform->addElement('html', '</tr>');
            $n_courses++;
        }

        $mform->addElement('html', '</tbody>');
        $mform->addElement('html', '</table>');

        // Output button
        $this->add_action_buttons(true,get_string('download', 'local_notasuai'));
    }
	
	function validation($data,$files) {

        $errors = array();
		
		$confirmed = 0;
						
		foreach($data['emarking_checkbox'] as $dt){
			if ($dt > 0){
				$confirmed++;
			}
		}
		
        if ($confirmed != 0){
		}else{
            $errors['buttonar'] = get_string('error2', 'local_notasuai');
        }

        return $errors;
    }

}