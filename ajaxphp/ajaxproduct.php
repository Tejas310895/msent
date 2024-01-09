<?php

include("../includes/db.php");

if (isset($_POST['submit'])) {
    $itemArr = $_POST['item'];
    $qtyArr = $_POST['qty'];
    $sku_id = $_POST['sku_id'];
    $prod_list = explode('_', $_POST['product_name']);
    if (count($prod_list) > 1) {
        $product_name = $prod_list[0];
        $product_id = $prod_list[1];
    } else {
        $product_name = $prod_list[0];
        $product_id = 0;
    }
    $product_type = $_POST['product_type'];
    $hsn_code = $_POST['hsn_code'];
    $gst_rate = $_POST['gst_rate'];



    $carton_qty = $_POST['carton_qty'];
    $carton_box_size = $_POST['carton_size'];

    date_default_timezone_set('Asia/Kolkata');

    $today = date("Y-m-d H:i:s");

    if ($product_id == 0) {
        $get_sku = "select * from products where SKU_id='$sku_id'";

        $run_sku = mysqli_query($con, $get_sku);

        $count_sku = mysqli_num_rows($run_sku);

        if ($count_sku > 0) {
            echo "<script>alert('Same SKU ID, Please Use Different SKU ID')</script>";
            echo "<script>window.open('../index.php?create_product','_self')</script>";
        }
    }

    if (true) {
        if ($product_id == 0) {
            $insert_product = "insert into products (product_name,product_type,SKU_id,hsn_code,gst_rate,product_created_at,product_updated_at) values ('$product_name','$product_type','$sku_id','$hsn_code','$gst_rate','$today','$today')";
            $run_product = mysqli_query($con, $insert_product);
            $product_id = mysqli_insert_id($con);
        } else {
            $insert_product = "update products set product_name='$product_name',product_type='$product_type',hsn_code='$hsn_code',gst_rate='$gst_rate',product_created_at='$today',product_updated_at='$today' where product_id='$product_id'";
            $run_product = mysqli_query($con, $insert_product);
        }


        if (!empty($itemArr)) {
            for ($i = 0; $i < count($itemArr); $i++) {
                if (!empty($itemArr[$i])) {
                    $item = $itemArr[$i];
                    $qty = $qtyArr[$i];

                    $check_exist = "select * from raw_required where item_id=$item and SKU_id=$sku_id";
                    $run_raw = mysqli_query($con, $check_exist);
                    $count_raw = mysqli_num_rows($run_raw);
                    if ($count_raw > 0) {
                        $insert_required = "update raw_required set item_qty=$qty where item_id=$item and SKU_id=$sku_id";
                    } else {
                        $insert_required = "insert into raw_required (SKU_id,item_id,item_qty) values ('$sku_id','$item','$qty')";
                    }
                    $run_required = mysqli_query($con, $insert_required);

                    $stock_reduce = $carton_box_size * ($qty * $carton_qty);

                    $update_stock = "update raw_items set item_stock=item_stock-'$stock_reduce',item_updated_at='$today' where item_id='$item'";
                    $run_update_stock = mysqli_query($con, $update_stock);
                }
            }
        }

        $cart_list = explode('_', $_POST['carton_name']);
        if (count($cart_list) > 1) {
            $carton_name = $cart_list[0];
            $carton_id = $cart_list[1];
        } else {
            $carton_id = 0;
            $carton_name = $cart_list[0];
        }

        if ($carton_id == 0) {

            $insert_carton = "insert into cartons (product_id,
            carton_title,
            carton_qty,
            carton_box_size,
            carton_created_at,
            carton_updated_at)
             values
             ('$product_id',
             '$carton_name',
             '$carton_box_size',
             '$carton_box_size',
             '$today',
             '$today'
             )";
            $run_carton = mysqli_query($con, $insert_carton);
            $carton_id = mysqli_insert_id($con);
        } else {
            $insert_carton = "update cartons set 
            product_id='$product_id',
            carton_title='$carton_name',
            carton_qty='$carton_box_size',
            carton_box_size='$carton_box_size',
            carton_updated_at ='$today'
             where carton_id='$carton_id'";
            $run_carton = mysqli_query($con, $insert_carton);
        }

        $get_print_id = "select * from manufacturing order by print_id desc";
        $run_print_id = mysqli_query($con, $get_print_id);
        $row_print_id = mysqli_fetch_array($run_print_id);

        $print_bef_id = $row_print_id['print_id'];
        $print_id = $print_bef_id + 1;

        $insert_manufacturing = "insert into manufacturing (print_id,
        carton_id,
        carton_qty,
        manufacturing_created_at,
        manufacturing_updated_at)
        values 
        ('$print_id',
        '$carton_id',
        '$carton_qty',
        '$today',
        '$today')
        ";
        $run_manufacturing = mysqli_query($con, $insert_manufacturing);

        $update_carton_stock = "update cartons set carton_stock=carton_stock+'$carton_qty' where carton_id='$carton_id'";
        $run_carton_stock = mysqli_query($con, $update_carton_stock);

        echo "<script>alert('Done')</script>";
        echo "<script>window.open('../print_stock.php?print_id=$print_id','_self')</script>";
    }
}

if (isset($_POST['carton_entry'])) {
    $carton_title = $_POST['carton_title'];
    $product_id = $_POST['product_id'];
    $carton_qty = $_POST['carton_qty'];
    $carton_lable = $_POST['carton_lable'];
    $carton_sub_lable = $_POST['carton_sub_lable'];
    $carton_box_size = $_POST['carton_box_size'];

    date_default_timezone_set('Asia/Kolkata');

    $today = date("Y-m-d H:i:s");

    $insert_carton = "insert into cartons (product_id,
                                           carton_title,
                                           carton_qty,
                                           carton_lable,
                                           carton_sub_lable,
                                           carton_box_size,
                                           carton_created_at,
                                           carton_updated_at)
                                            values
                                            ('$product_id',
                                            '$carton_title',
                                            '$carton_qty',
                                            '$carton_lable',
                                            '$carton_sub_lable',
                                            '$carton_box_size',
                                            '$today',
                                            '$today'
                                            )";
    $run_carton = mysqli_query($con, $insert_carton);

    if ($run_carton) {
        echo "<div class='alert alert-success' role='alert'>
        <strong>Done!</strong> Your Carton is added successfully.
      </div>";
    } else {
        echo "
        <div class='alert alert-Danger' role='alert'>
        <strong>Error!</strong> Failed to add the Carton try again.
        </div>
        ";
    }
}

if (isset($_POST['make_product'])) {
    $carton_id = $_POST['carton_id'];
    $carton_qty = $_POST['carton_qty'];

    date_default_timezone_set('Asia/Kolkata');

    $today = date("Y-m-d H:i:s");

    $get_proid = "select * from cartons where carton_id='$carton_id'";
    $run_proid = mysqli_query($con, $get_proid);
    $row_proid = mysqli_fetch_array($run_proid);
    $pro_id = $row_proid['product_id'];
    $pro_count = $row_proid['carton_qty'];

    $get_sku = "select * from products where product_id='$pro_id'";
    $run_sku = mysqli_query($con, $get_sku);
    $row_sku = mysqli_fetch_array($run_sku);
    $sku_id = $row_sku['SKU_id'];

    $get_required = "select * from raw_required where SKU_id='$sku_id'";
    $run_required = mysqli_query($con, $get_required);
    $stock = 0;
    $check = 0;
    while ($row_required = mysqli_fetch_array($run_required)) {
        $item_id = $row_required['item_id'];
        $item_qty = $row_required['item_qty'];

        $get_item = "select * from raw_items where item_id='$item_id'";
        $run_item = mysqli_query($con, $get_item);
        $count_item = mysqli_num_rows($run_item);
        $row_item = mysqli_fetch_array($run_item);
        $item_stock = $row_item['item_stock'];

        $stock_check = $item_qty * ($pro_count * $carton_qty);

        if ($item_stock < $stock_check) {
            $check = 0;
        } else {
            $check = ++$check;
        }
        $stock = ++$stock;
    }

    if ($stock == $check) {

        $get_print_id = "select * from manufacturing order by print_id desc";
        $run_print_id = mysqli_query($con, $get_print_id);
        $row_print_id = mysqli_fetch_array($run_print_id);

        $print_bef_id = $row_print_id['print_id'];
        $print_id = $print_bef_id + 1;

        $get_productid = "select * from cartons where carton_id='$carton_id'";
        $run_productid = mysqli_query($con, $get_productid);
        $row_productid = mysqli_fetch_array($run_productid);
        $product_id = $row_productid['product_id'];
        $product_count = $row_productid['carton_qty'];

        $get_skuid = "select * from products where product_id='$product_id'";
        $run_skuid = mysqli_query($con, $get_skuid);
        $row_skuid = mysqli_fetch_array($run_skuid);
        $sku_id2 = $row_skuid['SKU_id'];

        $get_raw_required = "SELECT DISTINCT(item_id) from raw_required where SKU_id='$sku_id2'";
        $run_raw_required = mysqli_query($con, $get_raw_required);
        while ($row_raw_required = mysqli_fetch_array($run_raw_required)) {
            $requireditem_id = $row_raw_required['item_id'];

            $get_itemqty = "select * from raw_required where item_id='$requireditem_id' and SKU_id='$sku_id2'";
            $run_itemqty = mysqli_query($con, $get_itemqty);
            $row_itemqty = mysqli_fetch_array($run_itemqty);
            $requireditem_qty = $row_itemqty['item_qty'];

            $stock_reduce = $requireditem_qty * ($pro_count * $carton_qty);

            $update_stock = "update raw_items set item_stock=item_stock-'$stock_reduce',item_updated_at='$today' where item_id='$requireditem_id'";
            $run_update_stock = mysqli_query($con, $update_stock);
        }

        if ($update_stock) {
            $insert_manufacturing = "insert into manufacturing (print_id,
                                                                carton_id,
                                                                carton_qty,
                                                                manufacturing_created_at,
                                                                manufacturing_updated_at)
                                                                values 
                                                                ('$print_id',
                                                                '$carton_id',
                                                                '$carton_qty',
                                                                '$today',
                                                                '$today')
                                                                ";
            $run_manufacturing = mysqli_query($con, $insert_manufacturing);

            $update_carton_stock = "update cartons set carton_stock=carton_stock+'$carton_qty' where carton_id='$carton_id'";
            $run_carton_stock = mysqli_query($con, $update_carton_stock);

            echo "<script>alert('Products Manufacturing Successfull')</script>";
            echo "<script>window.open('../print_stock.php?print_id=$print_id','_self')</script>";
        } else {
            echo "<script>alert('Products Manufacturing Failed')</script>";
            echo "<script>window.open('../index.php?product_manufacturing','_self')</script>";
        }
    } else {
        echo "<script>alert('Stock Unavailable')</script>";
        echo "<script>window.open('../index.php?product_manufacturing','_self')</script>";
    }
}

if (isset($_POST['get_ready_product'])) {
    list($prod_title, $prod_id) = explode('_', $_POST['get_ready_product']);

    date_default_timezone_set('Asia/Kolkata');

    $today = date("Y-m-d H:i:s");

    $get_sku = "select * from products where product_id='$prod_id'";
    $run_sku = mysqli_query($con, $get_sku);
    $row_sku = mysqli_fetch_all($run_sku, MYSQLI_ASSOC);
    $sku_id = $row_sku[0]['SKU_id'];

    $get_req = "select raw_items.item_id,item_name,item_qty from raw_required inner join raw_items on raw_required.item_id=raw_items.item_id where raw_required.SKU_id='$sku_id'";
    $run_req = mysqli_query($con, $get_req);
    $row_req = mysqli_fetch_all($run_req, MYSQLI_ASSOC);

    $get_cart = "select carton_id,carton_title from cartons where product_id='$prod_id' and carton_stock>0";
    $run_cart = mysqli_query($con, $get_cart);
    $row_cart = mysqli_fetch_all($run_cart, MYSQLI_ASSOC);
    $row_cart = array_reduce($row_cart, function ($carry, $val) {
        $carry[] = $val['carton_title'] . '_' . $val['carton_id'];
        return $carry;
    });

    echo json_encode(['prod_data' => array_shift($row_sku), 'carton_data' => $row_cart, 'req_raw' => $row_req]);
}
if (isset($_POST['req_raw_item'])) {
    $raw_id = $_POST['req_raw_item'];
    $raw_req_qty = $_POST['req_raw_qty'];
    $cart_list = explode('_', $_POST['req_cart_name']);
    $req_manu_qty = $_POST['req_manu_qty'];
    $carton_size = $_POST['carton_size'];

    date_default_timezone_set('Asia/Kolkata');

    $today = date("Y-m-d H:i:s");

    $req_total_qty = $raw_req_qty * ($req_manu_qty * $carton_size);

    $get_raw = "select * from raw_items where item_id='$raw_id'";
    $run_raw = mysqli_query($con, $get_raw);
    $row_raw = mysqli_fetch_all($run_raw, MYSQLI_ASSOC);

    if ($req_total_qty > $row_raw[0]['item_stock']) {
        echo $carton_size;
    } else {
        echo 1;
    }
}
if (isset($_POST['cart_check_name'])) {
    list($cart_title, $cart_id) = explode('_', $_POST['cart_check_name']);

    date_default_timezone_set('Asia/Kolkata');

    $today = date("Y-m-d H:i:s");

    $get_cart = "select carton_qty from cartons where carton_id='$cart_id'";
    $run_cart = mysqli_query($con, $get_cart);
    $row_cart = mysqli_fetch_all($run_cart, MYSQLI_ASSOC);

    echo $row_cart[0]['carton_qty'];
}
