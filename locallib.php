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
 * @package    local
 * @subpackage notasuai
 * @copyright  2019  Martin Fica (mafica@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to export the Grades's Summary to excel
 *
 * @param array $header
 *            Array containing the header of each row
 * @param varchar $filename
 *            Full name of the excel
 * @param array $data
 *            Array containing the data of each row
 * @param array $description
 *            Array containing the selected descriptions of attendances ???
 * @param array $dates
 *            Array containing the dates of each session
 * @param array $tabs
 *            Array containing the tabs of the excel  DELETE, DONT NEED IT
 */

/**
 * Exports all grades and scores in an exam in Excel format
 *
 * @param unknown $emarking
 * @param unknown $context
 */

function  export_to_excel($emarking)
    {

        global $DB, $CFG;
        // Validate that we have a rubric associated
        //list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) =
            //emarking_validate_rubric($context, false, false);
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
		foreach ($emarking as $id){
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
        // HERE 'emarkingid' => $emarking->id

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
        $excelfilename = clean_filename("ReporteUAI" . "-grades.xls");
        // Save the data to Excel
        emarking_save_data_to_excel($headers, $tabledata, $excelfilename, 5);
    }

function export_excel($emarking, $context = null)
{

    global $DB, $CFG;
    // Validate that we have a rubric associated
    //list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context, false, false);

    // Calculate levels indexes in forced formative feedback (no grades)
    $levelsindex = array();
    /*foreach($definition->rubric_criteria as $crit) {
        $total = count($crit['levels']);
        $current = 0;
        foreach($crit['levels'] as $lvl) {
            $current++;
            $levelsindex[$lvl['id']] = $total - $current + 1;
        }
    }*/

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

    $n = 0;
    foreach($emarking as $id){
        if($id > 0){
            if ($n==0){
                $testquery = $testquery . ' WHERE e.id = ' . $id;
                $n += 1;
            }
            else{
                $testquery = $testquery . ' AND e.id = ' . $id;
                
            }
        }
    }
    $testquery = $testquery . " ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";

    // Get data and generate a list of questions.
    $rows = $DB->get_records_sql($testquery);
    // HERE 'emarkingid' => $emarking->id

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
    $excelfilename = clean_filename("ReporteUAI" . "-grades.xls");
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