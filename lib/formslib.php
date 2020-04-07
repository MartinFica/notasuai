<?php

    // function to add a product to database
    function insertRecord($name, $description, $price, $quantity){
        global $DB, $USER;
        $record = new stdClass();
        $record->name = $name;
        $record->description = $description;
        $record->price = $price;
        $record->quantity = $quantity;
        $record->date = date('Y-m-d H:i');
        $record->user_id = $USER->id;
        // Insert register
        $DB->insert_record('product', $record);
    }

    // function to change a product in the data base
    function updateRecord($product_id, $name, $description, $price, $quantity){
        global $DB;

        $record = new stdClass();
        $record->id = $product_id;
        $record->name = $name;
        $record->description = $description;
        $record->price = $price;
        $record->quantity = $quantity;
        $DB->update_record('product', $record);
    }

    // function to delete a product from database
    function deleteRegister($product_id){
        global $DB, $action;
        //Borrar venta
        if ($DB->delete_records("product", array("id" => $product_id))){
            $action = 'view';
        }
        else {
            return false;
        }
        return true;
    }

    // get's all the products that exist in the table products
    function getAllProducts(){
        global $DB;
        $sql = 'SELECT p.id, p.name, p.description, p.price, p.quantity, p.date, p.user_id, u.username
                    FROM {product} p
                    INNER JOIN {user} u
                    ON u.id = p.user_id
                    ORDER BY p.date DESC';

        $sales = $DB->get_records_sql($sql, null);

        return $sales;
    }

    // finds a specific product base on the product_id
    function findProduct($product_id){
        global $DB;

        /*$sql = 'SELECT p.id, p.name, p.description, p.price, p.quantity, p.date, p.user_id, u.username, u.email
                    FROM {product} p
                    INNER JOIN {user} u 
                    ON (u.id = p.user_id)
                    WHERE p.id = ?
                    ';

        $product = $DB->get_records_sql($sql,array($product_id));]*/

        $product = $DB->get_record('product',['id' => $product_id]);

        return $product;
    }

    // gets all the things a user has on sale
    function getAllmisventas($OUTPUT){
        $products = getAllProducts();
        $products_table = new html_table();

        if(sizeof($products) > 0){

            $products_table->head = [
                'Nombre',
                'Precio',
                'Fecha Publicación',
            ];

            foreach($products as $product){
                /**
                 *Botón eliminar
                 * */
                $delete_url = new moodle_url('/local/web_market/misventas.php', [
                    'action' => 'delete',
                    'product_id' =>  $product->id,

                ]);
                $delete_ic = new pix_icon('t/delete', 'Eliminar');
                $delete_action = $OUTPUT->action_icon(
                    $delete_url,
                    $delete_ic,
                    new confirm_action('¿Ya no vende este articulo?')
                );

                /**
                 *Botón editar
                 * */
                $editar_url = new moodle_url('/local/web_market/cambiarventa.php', [
                    'action' => 'edit',
                    'product_id' =>  $product->id

                ]);
                $editar_ic = new pix_icon('i/edit', 'Editar');
                $editar_action = $OUTPUT->action_icon(
                    $editar_url,
                    $editar_ic
                );

                $products_table->data[] = array(
                    $product->name,
                    $product->price,
                    date('d-m-Y',strtotime($product->date)),
                    $editar_action.' '.$delete_action
                );
            }
        }

        $url_button = new moodle_url("/local/web_market/vender.php", array("action" => "add"));

        $top_row = [];
        $top_row[] = new tabobject(
            'products',
            new moodle_url('/local/web_market/index.php'),
            ' En Venta'
        );
        $top_row[] = new tabobject(
            'misventas',
            new moodle_url('/local/web_market/misventas.php'),
            'Mis Ventas'
        );

        $sale = getSale();
        foreach ($sale as $data){
            $id = $data->id;
        }
        $top_row[] = new tabobject(
            'carro',
            new moodle_url('/local/web_market/comprar.php', [
                'previous' => 'other',
                'sale_id' => $id
            ]),
            'Mi Carro'
        );


        // Displays all the records, tabs, and options
        echo $OUTPUT->tabtree($top_row, 'misventas');
        if (sizeof(getAllProducts()) == 0){
            echo html_writer::nonempty_tag('h4', 'No estas vendiendo nada.', array('align' => 'left'));
        }
        else{
            echo html_writer::table($products_table);
        }

        echo html_writer::nonempty_tag("div", $OUTPUT->single_button($url_button, "Poner a la Venta"), array("align" => "left"));

}

    // lets the user see more details for a specific product
    function comprarProducto($product_id, $url){
        global $DB;
        if($url == 1){
            $url= '/local/web_market/index.php';
        }
        else if($url == 2){
            $url= '/local/web_market/misventas.php';
        }

        $product = findProduct($product_id);

        $udata = $DB->get_record('user',['id' => $product->user_id]);

        $add_toChart = new moodle_url('/local/web_market/confirmar.php', [
            'action' => 'view',
            'product_id' =>  $product_id,
        ]);

        echo
            '<table style="width:50%">
              <tr>
                <td><strong>Name</strong></td>
                <td>'.$product->name.'</td>
              </tr>
              <tr>
                <td><strong>Description</strong></td>
                <td>'.$product->description.'</td>
            </tr>
              <tr>
                <td><strong>Date put on sale</strong></td>
                <td>'.$product->date.'</td>
              </tr>
              <tr>
                <td><strong>Price ($)</strong></td>
                <td>'.$product->price.'</td>
              </tr>
             <tr>
                <td><strong>Owner</strong></td>
                <td>'.$udata->username.'</td>
              </tr>
             <tr>
                <td rowspan="2"><strong>Email</strong></td>
                <td>'.$udata->email.'</td>
              </tr>
            </table> 
            <br>
    
            <a href='.new moodle_url($url).' class="btn btn-primary">ATRÁS</a>
            <a href='.new moodle_url($add_toChart).' class="btn btn-primary">COMPRAR</a>';
    }

    // creates a New Sale entry when it doesnt exist one
    function newSale(){
        global $DB, $USER;
        $record = new stdClass();
        $record->user_id = $USER->id;
        $record->sale_status = '1';
        $DB->insert_record('sale',$record);

        $sale = getSale();
        return $sale;
    }

    // function that gets the current sale of a user (a user cannot have more than 1 active sale (code = 1) )
    function getSale(){
        global $DB,$USER;

        $user_id = $USER->id;

        $sql = 'SELECT s.id, s.user_id, s.sale_status
                    FROM {sale} s
                    WHERE s.sale_status = 1 and s.user_id = ?
        ';
        $sale = $DB->get_records_sql($sql, array($user_id));
        if(sizeof($sale) == 0){
            $sale = newSale();
        }
        return $sale;
    }

    // gets all the items the user has on his current cart
    function getDetails($sale_id){
        global $DB;

        $sql = 'SELECT d.id, d.sale_id, d.product_id ,d.quantity, p.name, p.price, u.username, u.email 
                    FROM {details} d
                    INNER JOIN {product} p
                    ON d.product_id = p.id
                    INNER JOIN {user} u
                    ON p.user_id = u.id
                    WHERE d.sale_id = ?
        ';

        $details = $DB->get_records_sql($sql, array($sale_id));
        return $details;
    }

    // add items to cart
    function addtoCart($product_id,$sale_id){
        global $DB;
        $details = getDetails($sale_id);

        if (!in_array($product_id,$details) and !is_null($product_id)) {
            $record = new stdClass();
            $record->sale_id = $sale_id;
            $record->product_id = $product_id;
            $record->quantity = 1;
            $record->date = date('Y-m-d H:i');

            $DB->insert_record('details', $record);
        }
    }

    // display all the items a user is currently buying
    function getAllmiscompras($sale_id){
        global $DB;

        $sql = 'SELECT d.id, d.sale_id, d.product_id, d.quantity
                    FROM {details} d
                    WHERE d.sale_id = ?
                    ';

        $details = $DB->get_records_sql($sql, array($sale_id));
        return $details;
    }


    function updateProducts(){
        global $DB;

        $sale= getSale();
        foreach ($sale as $data){
            $id = $data->id;
            $user_id = $data->user_id;
        }

        $record = new stdClass();
        $record->id = $id;
        $record->user_id = $user_id;
        $record->sale_status = '0';
        $DB->update_record('sale', $record);

        $details = getDetails($id);

        foreach($details as $detail){
            $id = $detail->id;
            $sale_id = $detail->sale_id;
            $product_id = $detail->product_id;
            $quantity = $detail->quantity;

            $update = new stdClass();
            $update -> id = $id;
            $update -> sale_id = $sale_id;
            $update -> product_id = $product_id;
            $update -> quantity = $quantity;
            $update -> datesold = date('Y-m-d H:i');
            $DB->update_record('details', $update);
        }
    }

    // function to delete a product from database
    function deletefromCart($id){
        global $DB;
        //Borrar item del carro
        $DB->delete_records("details", array("id" => $id));
        }