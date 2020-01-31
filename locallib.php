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
function notasuai_exporttoexcel($header, $filename, $data, $descriptions, $dates, $tabs){
	global $CFG;
	$workbook = new MoodleExcelWorkbook("-");
	$workbook->send($filename);
	foreach ($tabs as $index=>$tab){
		$attxls = $workbook->add_worksheet($tab);
		$i = 1;     //y axis
		$j = 3;     //x axis
		$headerformat = $workbook->add_format();
		$headerformat->set_bold(1);
		$headerformat->set_size(10);
		foreach ($descriptions[$index] as $descr){
			$attxls->write($i, $j, $descr, $headerformat);
			$j++;
		}
		$i = 2;
		$j = 3;
		foreach ($dates[$index] as $date){
			$attxls->write($i, $j, $date, $headerformat);
			$j++;
		}
		$i= 3;
		$j = 0;
		foreach($header[$index] as $cell){
			$attxls->write($i, $j, $cell, $headerformat);
			$j++;
		}
		$i=4;
		$j=0;
		foreach ($data[$index] as $row){
			foreach($row as $cell){
				$attxls->write($i, $j,$cell);
				$i++;
			}
			$j++;
			$i=4;
		}
	}
	$workbook->close();
	exit;
}

