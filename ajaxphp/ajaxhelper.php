<?php

include("../includes/db.php");

if (isset($_POST['item_id'])) {

    $item_id = $_POST['item_id'];

    $get_item_id = "select * from raw_items where item_id='$item_id'";

    $run_item_id = mysqli_query($con, $get_item_id);

    $row_item_id = mysqli_fetch_array($run_item_id);

    $item_unit = $row_item_id['item_unit'];

    echo "Quantity in " . $item_unit;
}

if (isset($_POST['vendor_id'])) {

    $vendor_id = $_POST['vendor_id'];

    echo "<option disabled selected value>Choose the raw Item</option>";
    $get_items = "select * from raw_items where vendor_id='$vendor_id'";
    $run_items = mysqli_query($con, $get_items);
    while ($row_items = mysqli_fetch_array($run_items)) {

        $item_id = $row_items['item_id'];
        $item_name = $row_items['item_name'];

        echo "<option value='$item_id'>$item_name</option>";
    }
}

if (isset($_GET['work_order_delete'])) {
    $work_order_id = $_GET['work_order_delete'];

    $delete_work_order = "DELETE from work_order_task where work_order_entry_id='$work_order_id'";
    $run_delete_work_order = mysqli_query($con, $delete_work_order);

    if ($run_delete_work_order) {
        echo "<script>alert('Work order is deleted')</script>";
        echo "<script>window.open('../index.php?work_orders','_self')</script>";
    } else {
        echo "<script>alert('failed to delete work order')</script>";
        echo "<script>window.open('../index.php?work_orders','_self')</script>";
    }
}

if (isset($_GET['delete_purchase'])) {
    $delete_purchase = $_GET['delete_purchase'];

    $get_data_entry = "SELECT * from raw_entry where entry_id='$delete_purchase'";
    $run_entries = mysqli_query($con, $get_data_entry);
    $row_entries = mysqli_fetch_array($run_entries, MYSQLI_ASSOC);

    $item_id = $row_entries['item_id'];
    $item_qty = $row_entries['item_qty'];

    $update_item = "UPDATE raw_items set item_stock=item_stock-$item_qty where item_id='$item_id'";
    $run_update_item = mysqli_query($con, $update_item);

    if ($run_update_item) {
        $purchase_delete = "DELETE from raw_entry where entry_id='$delete_purchase'";
        $run_purchase_delete = mysqli_query($con, $purchase_delete);
    }

    if ($run_purchase_delete) {
        echo "<script>alert('Purchase is deleted')</script>";
        echo "<script>window.open('../index.php?view_rawentry','_self')</script>";
    } else {
        echo "<script>alert('failed to delete Purchase')</script>";
        echo "<script>window.open('../index.php?view_rawentry','_self')</script>";
    }
}

if (isset($_GET['delete_ready'])) {
    $delete_ready = $_GET['delete_ready'];


    $get_data_manu = "SELECT * from manufacturing where manufacturing_id='$delete_ready'";
    $run_manu = mysqli_query($con, $get_data_manu);
    $row_manu = mysqli_fetch_array($run_manu, MYSQLI_ASSOC);

    $carton_id = $row_manu['carton_id'];
    $manu_qty = $row_manu['carton_qty'];

    $get_data_carton = "SELECT * from cartons where carton_id ='$carton_id'";
    $run_carton = mysqli_query($con, $get_data_carton);
    $row_carton = mysqli_fetch_array($run_carton, MYSQLI_ASSOC);

    $product_id = $row_carton['product_id'];
    $carton_qty = $row_carton['carton_qty'];

    $get_data_product = "SELECT * from products where product_id ='$product_id'";
    $run_product = mysqli_query($con, $get_data_product);
    $row_product = mysqli_fetch_array($run_product, MYSQLI_ASSOC);

    $sku_id = $row_product['SKU_id'];

    $get_data_product_required = "SELECT * from raw_required where SKU_id ='$sku_id'";
    $run_product_required = mysqli_query($con, $get_data_product_required);
    $row_product_required = mysqli_fetch_all($run_product_required, MYSQLI_ASSOC);

    foreach ($row_product_required as $raw) {
        $item_id = $raw['item_id'];
        $less_qty = ($carton_qty * $manu_qty) * $raw['item_qty'];
        $update_item = "UPDATE raw_items set item_stock=item_stock+$less_qty where item_id='$item_id'";
        $run_update_item = mysqli_query($con, $update_item);
    }

    $delete_manu = "DELETE from manufacturing where manufacturing_id='$delete_ready'";
    $run_delete_manu = mysqli_query($con, $delete_manu);

    if ($run_delete_manu) {
        echo "<script>alert('Ready stock entry is deleted')</script>";
        echo "<script>window.open('../index.php?view_manufacturing','_self')</script>";
    } else {
        echo "<script>alert('failed to delete Ready stock entry')</script>";
        echo "<script>window.open('../index.php?view_manufacturing','_self')</script>";
    }
}
