<?php

if (!isset($_SESSION['admin_user'])) {

    echo "<script>window.open('login.php','_self')</script>";
} else {
?>
    <?php
    $get_product = "select * from products";
    $run_product = mysqli_query($con, $get_product);
    $row_product = mysqli_fetch_all($run_product, MYSQLI_ASSOC);
    $row_product = array_reduce($row_product, function ($carry, $val) {
        $carry[] = $val['product_name'] . '_' . $val['product_id'];
        return $carry;
    });

    $get_items = "select item_id,item_name from raw_items";
    $run_items = mysqli_query($con, $get_items);
    $raw_stock = mysqli_fetch_all($run_items, MYSQLI_ASSOC);
    ?>
    <div class="col-12" id="raw_alerts">

    </div>
    <div class="row p-3 mb-3" style="background-color:#191c24;border-radius:5px;">
        <div class="col-6">
            <h4 class="py-2">UPDATE MANUFACTURING</h4>
        </div>
        <div class="col-6">
            <a class="btn btn-success float-right" href="index.php?view_manufacturing">Go Back</a>
        </div>
    </div>

    <form id="insert_product" method="post" action="ajaxphp/ajaxproduct.php">
        <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <label>PRODUCT NAME</label>
                    <div class="ui-widget">
                        <input id="tags" type="text" class="form-control" name="product_name" aria-describedby="" placeholder="Enter Product Name" onkeydown="clear_all()" required>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label>PRODUCT SKU ID</label>
                    <input type="text" class="form-control" name="sku_id" aria-describedby="" placeholder="Enter SKU ID" required readonly>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <label>PRODUCT TYPE</label>
                    <input type="text" class="form-control" name="product_type" aria-describedby="" placeholder="Enter Product Type" required>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <label>HSN CODE</label>
                    <input type="text" class="form-control" name="hsn_code" aria-describedby="" placeholder="Enter HSN CODE" required>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <label>GST RATE</label>
                    <input type="text" class="form-control" name="gst_rate" aria-describedby="" placeholder="Enter GST rate in %" required>
                </div>
            </div>
        </div>
        <div class="form-group fieldGroup">
            <div class="input-group">
                <select class="form-control mx-5" id="exampleFormControlSelect1" name="item[]" id="item" required>
                    <?php

                    echo "<option disabled selected value>Choose the raw Item</option>";
                    $get_items = "select * from raw_items";
                    $run_items = mysqli_query($con, $get_items);
                    while ($row_items = mysqli_fetch_array($run_items)) {

                        $item_id = $row_items['item_id'];
                        $item_name = $row_items['item_name'];

                        echo "<option value='$item_id'>$item_name</option>";
                    }

                    ?>
                </select>
                <input type="text" name="qty[]" id="qty" class="form-control" placeholder="Enter Qty required per item" required onchange="refresh_cart_qty()" />
                <div class="input-group-addon mx-3 mt-1">
                    <a href="javascript:void(0)" class="btn btn-success addMore"><span class="glyphicon glyphicon glyphicon-plus" aria-hidden="true"></span> Add</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label>Carton</label>
                    <div class="ui-widget">
                        <input id="myInput" class="form-control" type="text" name="carton_name" placeholder="Select Carton" onkeydown="clear_caton()" required>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <label>Size</label>
                    <div class="ui-widget">
                        <input class="form-control" type="text" name="carton_size" placeholder="Select Carton size" required>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label>Quantity Manufactured</label>
                    <input type="text" class="form-control" name="carton_qty" onchange="stock_validation($(this))" placeholder="Enter Quantity" required>
                </div>
            </div>
        </div>

        <button type="submit" name="submit" id="add_product" class="btn btn-lg btn-primary mx-5 mt-5 float-right">Submit</button>
    </form>
    <!-- copy of input fields group -->
    <div class="form-group fieldGroupCopy" style="display: none;">
        <div class="input-group">
            <select class="form-control mx-5" id="exampleFormControlSelect1" name="item[]" id="item" required>
                <?php

                echo "<option disabled selected value>Choose the raw Item</option>";
                $get_items = "select * from raw_items";
                $run_items = mysqli_query($con, $get_items);
                while ($row_items = mysqli_fetch_array($run_items)) {

                    $item_id = $row_items['item_id'];
                    $item_name = $row_items['item_name'];

                    echo "<option value='$item_id'>$item_name</option>";
                }

                ?>
            </select>
            <input type="text" name="qty[]" id="qty" class="form-control" placeholder="Enter Qty required per item" required onchange="refresh_cart_qty()" />
            <div class="input-group-addon mx-4 mt-1">
                <a href="javascript:void(0)" class="btn btn-danger remove"><span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>X</a>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="js/product.js"></script>
    <script src="js/script.js"></script>
    <script>
        $(function() {
            var availableTags = <?php echo json_encode($row_product); ?>;
            $("#tags").autocomplete({
                source: availableTags,
                select: function(event, ui) {
                    get_prod_values(ui.item.value);
                }
            });
        });

        function get_prod_values(name) {
            $.ajax({
                type: "post",
                url: "ajaxphp/ajaxproduct.php",
                data: {
                    "get_ready_product": name
                },
                success: function(response) {
                    prod_data = JSON.parse(response)['prod_data'];
                    carton_data = JSON.parse(response)['carton_data'];
                    req_raw = JSON.parse(response)['req_raw'];
                    raw_stock = <?php echo json_encode($raw_stock); ?>;
                    $('input[name="sku_id"]').val(prod_data['SKU_id']);
                    $('input[name="product_type"]').val(prod_data['product_type']);
                    $('input[name="hsn_code"]').val(prod_data['hsn_code']);
                    $('input[name="gst_rate"]').val(prod_data['gst_rate']);
                    $('input[name="sku_id"]').attr('readonly', true);
                    $.each(req_raw, function(propName, propVal) {
                        if (propName == 0) {
                            $('.fieldGroup').find('select[name="item[]"]').val(propVal['item_id']);
                            $('.fieldGroup').find('input[name="qty[]"]').val(propVal['item_qty']);
                        } else {
                            var copy_html =
                                '<div class="input-group">' +
                                '<select class="form-control mx-5" id="exampleFormControlSelect1" name="item[]" id="item" value="' + propVal['item_id'] + '" required>';
                            copy_html += "<option disabled selected value>Choose the raw Item</option>";
                            $.each(raw_stock, function(propName, allselectval) {
                                if (allselectval['item_id'] == propVal['item_id']) {
                                    copy_html += '<option value="' + allselectval['item_id'] + '" selected>' + allselectval['item_name'] + '</option>';
                                } else {
                                    copy_html += '<option value="' + allselectval['item_id'] + '">' + allselectval['item_name'] + '</option>';
                                }
                            });
                            copy_html += '</select>' +
                                '<input type="text" name="qty[]" id="qty" class="form-control" value="' + propVal['item_qty'] + '" required onchange="refresh_cart_qty()"/>' +
                                '<div class="input-group-addon mx-4 mt-1">' +
                                '<a href="javascript:void(0)" class="btn btn-danger remove"><span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>X</a>' +
                                '</div>' +
                                '</div>';
                            var fieldHTML = '<div class="form-group fieldGroup">' + copy_html + '</div>';
                            $('body').find('.fieldGroup:last').after(fieldHTML);
                        }
                    });
                    $("#myInput").autocomplete({
                        source: carton_data,
                        select: function(event, ui) {
                            get_cart_det(ui.item.value);
                        }
                    });
                }
            });
        }

        function get_cart_det(name) {
            $.ajax({
                type: "post",
                url: "ajaxphp/ajaxproduct.php",
                data: {
                    'cart_check_name': name
                },
                success: function(response) {
                    $('input[name="carton_size"]').val(response);
                    $('input[name="carton_size"]').attr('readonly', true);
                }
            });
        }

        function clear_all() {
            $('input[name="sku_id"]').val('');
            $('input[name="product_type"]').val('');
            $('input[name="hsn_code"]').val('');
            $('input[name="gst_rate"]').val('');
            $('input[name="sku_id"]').attr('readonly', false);
            $('.fieldGroup').not(':first').remove();
            $('.fieldGroup').find('select[name="item[]"]').val('');
            $('.fieldGroup').find('input[name="qty[]"]').val('');
            $("#myInput").val('');
            $("#myInput").autocomplete("destroy");
            $('input[name="carton_qty"]').val('');
            $('input[name="carton_size"]').val('');
            $('input[name="carton_size"]').attr('readonly', false);
        }

        function clear_caton() {
            $('input[name="carton_size"]').val('');
            $('input[name="carton_size"]').attr('readonly', false);
        }

        function stock_validation(element) {
            $('.fieldGroup').each(function() {
                var item_id = $(this).find('select[name="item[]"]');
                var item_qty = $(this).find('input[name="qty[]"]');
                var carton_size = $('input[name="carton_size"]').val();
                var carton_name = $('#myInput').val();
                if (item_id.val() == '' || item_qty.val() == '' || carton_name == '') {
                    $(this).find('select[name="item[]"]').css('border-color', 'red');
                    $(this).find('input[name="qty[]"]').css('border-color', 'red');
                    element.val('');
                    if (carton_name == '') {
                        $('#myInput').css('border-color', 'red');
                    }
                } else {
                    $.ajax({
                        type: "post",
                        url: "ajaxphp/ajaxproduct.php",
                        data: {
                            'req_raw_item': item_id.val(),
                            'req_raw_qty': item_qty.val(),
                            'req_cart_name': carton_name,
                            'carton_size': carton_size,
                            'req_manu_qty': element.val()
                        },
                        success: function(response) {
                            console.log(response);
                            if (response == 1) {
                                item_id.css('border-color', '');
                                item_qty.css('border-color', '');
                            } else {
                                item_id.css('border-color', 'red');
                                item_qty.css('border-color', 'red');
                                element.val('');
                                alert('Insufficient Stock');
                            }
                        }
                    });
                }
            });
        }

        function refresh_cart_qty() {
            $('input[name="carton_qty"]').val('');
        }
    </script>
<?php } ?>