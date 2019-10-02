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


class course extends moodleform {
	
	function definition(){
		
		global $DB, $CFG;
		$mform = $this->_form;

		// Campus Input
		$mform->addElement("select", "type", "Seleccione curso", array('--Select Campus--', 'Santiago', 'Viña', 'Ambos'));

		// Class input
        $this->add_checkbox_controller(1, "Todos", array('style' => 'font-weight: bold;'), 1);
        $mform->addElement ('advcheckbox', "algebra", "Algebra", null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'algebrai', 'Algebra I', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'algebraii', 'Algebra II', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'algebral', 'Algebra Lineal', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'calculodif', 'Calculo Diferencial', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'calculoi', 'Calculo I', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'calculoii', 'Calculo II', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'calculoiii', 'Calculo III', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'calculoint', 'Calculo Integral', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'calculomul', 'Calculo Multivariado', null, array('group' => 1));
        $mform->addElement ('advcheckbox', 'edo', 'Ecuaciones Diferenciales', null, array('group' => 1));

		//$mform->setType ("class", PARAM_);

		// Set action to "add"
		$mform->addElement ("hidden", "action", "add");
		$mform->setType ("action", PARAM_TEXT);

		$this->add_action_buttons(true);
	}
	
/*	function validation ($data, $files){
		
		global $DB;
		$errors = array();
	
		$course_id = $data["course_id"];
		$name = $data["name"];
	
		if (isset($data["course_id"]) && !empty($data["course_id"]) && $data["course_id"] != "" && $data["course_id"] != null ){
		}else{
			$errors["course_id"] = "Campo requerido";
		}
	
		if (isset($data["name"]) && !empty($data["name"]) && $data["name"] != "" && $data["name"] != null ){
		}else{
			$errors["name"] = "Campo requerido";
		}
	
		return $errors;
	}*/
}

class grades extends moodleform {
	
	function definition (){
		
		global $DB, $CFG;
		$mform = $this->_form;

		$instance = $this->_customdata;
		$idattendance = $instance["idapunte"];

		// Query for retrieving courses records
		$sql = "SELECT id, fullname
				FROM {course}
				WHERE id>1";

		// Retrieves courses records
		$courses = $DB->get_records_sql($sql, array(1));
		
		// Retrieves the previous information registered
		$attendancedata = $DB->get_record("relacion", array("id" => $idattendance));

		// Select user input
		$status = array();
		foreach($courses as $course){
			$id = $course->id;
			$name = $course->fullname;
			$status[$course->id] = $name;
		}
		$mform->addElement("select", "course_id", "Seleccione curso", $status);

		// Name input
		$mform->addElement ("text", "name", "Ingrese nombre del apunte");
		$mform->setType ("name", PARAM_TEXT);

		// Set action to "edit"
		$mform->addElement("hidden", "action", "edit");
		$mform->setType("action", PARAM_TEXT);
		$mform->addElement("hidden", "idapunte", $idattendance);
		$mform->setType("idapunte", PARAM_INT);

		$this->add_action_buttons(true);
	}

    /*function validation ($data, $files){
		
		global $DB;
		$errors = array();
	
		$course_id = $data["course_id"];
		$name = $data["name"];
	
		if (isset($data["course_id"]) && !empty($data["course_id"]) && $data["course_id"] != "" && $data["course_id"] != null ){
		}else{
			$errors["course_id"] = "Campo requerido";
		}
	
		if (isset($data["name"]) && !empty($data["name"]) && $data["name"] != "" && $data["name"] != null ){
		}else{
			$errors["name"] = "Campo requerido";
		}
	
		return $errors;
	}*/
}