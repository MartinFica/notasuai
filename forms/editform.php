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
 * @author  2019 Martin Fica <mafica@alumnos.uai.cl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// You can access the database via the $DB method calls here.
require(__DIR__.'/../../../config.php');

//Necesario para desplegar el formulario
require_once ($CFG->libdir . '/formslib.php');

class editform extends moodleform {

    //Add elements to form
    public function definition() {
        global $CFG, $DB;

        $edit_form = $this->_form;
        $instance = $this->_customdata;
        $product_id = $instance['product_id'];

        $product = $DB->get_record('product', ['id'=>$product_id]);

        // Name edit
        $edit_form->addElement ("text", "name", get_string('name', 'notasuai'));
        $edit_form->setType ("name", PARAM_TEXT);

        // Description edit
        $edit_form->addElement ('textarea','description', get_string('description', 'notasuai'), 'wrap="virtual" rows="5" cols="50"');
        $edit_form->setType ('description', PARAM_RAW);

        // Price edit
        $edit_form->addElement ("text", "price", get_string('price', 'notasuai'));
        $edit_form->setType ("price", PARAM_INT);

        // Quantity edit
        $edit_form->addElement ("text", "quantity", get_string('quantity', 'notasuai'));
        $edit_form->setType ("quantity", PARAM_INT);

        // Set action to "add"
        $edit_form->addElement ("hidden", "action", "edit");
        $edit_form->setType ("action", PARAM_TEXT);
        $edit_form->addElement('hidden', 'product_id', $product_id);
        $edit_form->setType('product_id', PARAM_INT);

        $this->add_action_buttons(true);

    }

    //Custom validation should be added here
    function validation($data, $files) {

        global $DB;
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

