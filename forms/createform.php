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
 * @package    local
 * @subpackage notasuai
 * @author  2019 Martin Fica Cabrera <mafica@alumnos.uai.cl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// You can access the database via the $DB method calls here.
require(__DIR__.'/../../../config.php');

//Necesario para desplegar el formulario
require_once ($CFG->libdir . '/formslib.php');

class addproduct extends moodleform {

    //Add elements to form
    function definition() {
        global $DB, $CFG;

        $mform = $this->_form;

        // Name input
        $mform->addElement ("text", "name", get_string('name', 'notasuai'));
        $mform->setType ("name", PARAM_TEXT);

        //Description input
        $mform->addElement ('textarea','description', get_string('description', 'notasuai'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType ('description', PARAM_RAW);

        // Price input
        $mform->addElement ("text", "price", get_string('price', 'notasuai'));
        $mform->setType ("price", PARAM_INT);

        // Quantity input
        $mform->addElement ("text", "quantity", get_string('quantity', 'notasuai'));
        $mform->setType ("quantity", PARAM_INT);

        // Set action to "add"
        $mform->addElement ("hidden", "action", "add");
        $mform->setType ("action", PARAM_TEXT);
        $this->add_action_buttons(true);

    }

    //Custom validation should be added here
    function validation($data, $files) {

        $errors = array();

        $name = $data["name"];
        $description = $data["description"];
        $price = $data["price"];
        $quantity = $data ["quantity"];

        if (isset($name) && !empty($name) && $name != "" && $name != null ){
        }else{
            $errors["name"] = "Campo requerido";
        }

        if (isset($description) && !empty($description) && $description != "" && $description != null ){
        }else{
            $errors["description"] = "Campo requerido";
        }

        if (isset($price) && !empty($price) && $price != "" && $price != null ){
        }else{
            $errors["price"] = "Campo requerido";
        }

        if (isset($quantity) && !empty($quantity) && $quantity != "" && $quantity != null ){
        }else{
            $errors["quantity"] = "Campo requerido";
        }

        return $errors;
    }
}

