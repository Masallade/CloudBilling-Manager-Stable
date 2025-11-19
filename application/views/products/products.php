<div class="content-body">
    <div id="notify" class="alert" style="display: none;"></div>
    <div class="row stats-cards">
        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body" id="in_stock">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="success"><span id="dash_0"></span></h3>
                                <span><?php echo $this->lang->line('In Stock') ?></span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-rocket success font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body" id="out_stock">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="danger"><span id="dash_1"></span></h3>
                                <span><?php echo $this->lang->line('Stock out') ?></span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-eyeglasses danger font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body" id="total_stock">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="purple"><span id="dash_2"></span></h3>
                                <span><?php echo $this->lang->line('Total') ?></span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-pie-chart purple font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body" id="worth_sales_stock">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="blue"><?php echo $salessum . ' / ' . $worthsum; ?></h3>
                                <span>Worth (Sales/Stock)</span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-graph blue font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <style>
        .stats-cards .card { height: 110px; }
        .stats-cards .card .card-content, .stats-cards .card .card-body { height: 100%; }
        .stats-cards .card .card-body { display: flex; align-items: center; }
        .stats-cards .card .media { align-items: center; width: 100%; }
        .stats-cards .card .media-body h3 { margin: 0; }
        
        /* Settings column button styling */
        #productstable td:last-child {
            white-space: nowrap;
            min-width: 200px;
        }
        
        #productstable .btn-group {
            display: inline-flex;
            vertical-align: middle;
        }
        
        #productstable .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            margin: 1px;
        }
        
        #productstable .btn i {
            margin-right: 0;
        }
        
        #productstable .dropdown-menu {
            min-width: 180px;
        }
        
        #productstable .dropdown-item {
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
        }
        
        #productstable .dropdown-item i {
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }
    </style>
    <div class="card">
        <div class="card-header">
            <h5><?php echo $this->lang->line('Products') ?>
                <?php if ($this->aauth->permission_new(null, 'stockNewProduct')) { ?> 
                    <a href="<?php echo base_url('products/add') ?>" class="btn btn-primary btn-sm rounded">
                    <?php echo $this->lang->line('Add new') ?>
                </a>
                <?php } ?>
                <!-- <a href="<?php echo base_url('products') ?>?group=yes" class="btn btn-purple btn-sm rounded"><i class="ft-grid"></i></a> 
                 <a href="<?php echo base_url('products') ?>" class="btn btn-purple btn-sm rounded"><i class="ft-list"></i></a>-->
            </h5>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>

                <div class="message"></div>
            </div>

            <div class="card-body">

                <div class="row">
                    <br>
                    <div class="col-md-4">
                        <label>Total Selected Worth (Sales/Stock)</label>
                        <input type="text" id="total_selected_worth" class="form-control" readonly value="0.00 / 0.00">
                    </div>
                    <!-- <div class="col-md-4">
                        <lable>Total Selected Purchase Price</lable>
                        <input type="number" step="any" value="0" id="total_selected_amount" class="form-control" readonly>
                    </div> -->
                    <div class="col-md-4">
                        <?php if ($this->aauth->premission(25)) { ?>
                            <button class="btn btn-primary" id="edit" style="margin-top:20px;">Edit Selected Products</button>
                        <?php } ?>
                    </div>
                    <br>
                </div>
                <br>
                <br>
                <table id="productstable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo $this->lang->line('Name') ?></th>
                            <th><?php echo $this->lang->line('Qty') ?></th>
                            <th><?php echo $this->lang->line('Code') ?></th>
                            <!--th>VAT Code</th-->
                            <th><?php echo $this->lang->line('Category') ?></th>
                            <!--th><?php echo $this->lang->line('Warehouse') ?></th-->
                            <th>Purchase Price (<?php echo isset($currency) ? htmlspecialchars($currency) : 'GBP'; ?>)</th>
                            <th>Sale Price (<?php echo isset($currency) ? htmlspecialchars($currency) : 'GBP'; ?>)</th>
                            <!-- <th>Weight</th> -->
                            <th><input type="checkbox" class="checkAll">&nbsp; ALL</th>
                            <th><?php echo $this->lang->line('Settings') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

                    <tfoot>
                        <!--tr>
                        <th>#</th>
                        <th><?php echo $this->lang->line('Name') ?></th>
                        <th><?php echo $this->lang->line('Qty') ?></th>
                        <th><?php echo $this->lang->line('Code') ?></th>
                        
                        <th><?php echo $this->lang->line('Category') ?></th>
                      
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <th><input type="checkbox" class="checkAll"> ALL</th>
                        <th><?php echo $this->lang->line('Settings') ?></th>
                    </tr -->
                    </tfoot>
                </table>

            </div>
            <input type="hidden" id="dashurl" value="products/prd_stats">
        </div>
        <script type="text/javascript">
            var table;
            const urlParams = new URLSearchParams(window.location.search);
            const stock_out_filter = urlParams.get('stock_out_filter');
            $(document).ready(function() {

                var url, filter;

                // Set browser tab title for Products page
                document.title = 'Products';

                if (stock_out_filter) {
                    url = "<?php echo site_url('products/product_list_filtered') ?>";
                    filter = 2;
                    $("#in_stock").click(function() {
                        filter = 1;
                        url = "<?php echo site_url('products/product_list_filtered') ?>";
                        if ($.fn.DataTable.isDataTable('#productstable')) {
                            $('#productstable').DataTable().destroy();
                        }
                        draw_data(url, filter);
                    });
                    $("#out_stock").click(function() {
                        filter = 2;
                        url = "<?php echo site_url('products/product_list_filtered') ?>";
                        if ($.fn.DataTable.isDataTable('#productstable')) {
                            $('#productstable').DataTable().destroy();
                        }
                        draw_data(url, filter);
                    });
                    $("#total_stock").click(function() {
                        url = "<?php echo site_url('products/product_list') ?>";
                        filter = 0;
                        if ($.fn.DataTable.isDataTable('#productstable')) {
                            $('#productstable').DataTable().destroy();
                        }
                        draw_data(url, filter);
                    });
                } else {
                    url = "<?php echo site_url('products/product_list') ?>";
                    filter = 0;
                    $("#in_stock").click(function() {
                        filter = 1;
                        url = "<?php echo site_url('products/product_list_filtered') ?>";
                        if ($.fn.DataTable.isDataTable('#productstable')) {
                            $('#productstable').DataTable().destroy();
                        }
                        draw_data(url, filter);
                    });
                    $("#out_stock").click(function() {
                        filter = 2;
                        url = "<?php echo site_url('products/product_list_filtered') ?>";
                        if ($.fn.DataTable.isDataTable('#productstable')) {
                            $('#productstable').DataTable().destroy();
                        }
                        draw_data(url, filter);
                    });
                    $("#total_stock").click(function() {
                        url = "<?php echo site_url('products/product_list') ?>";
                        filter = 0;
                        if ($.fn.DataTable.isDataTable('#productstable')) {
                            $('#productstable').DataTable().destroy();
                        }
                        draw_data(url, filter);
                    });
                }


                draw_data(url, filter);

                    function draw_data(url = url, filter = filter) {
                    $('#total_selected_amount').val(0);
                    $('#total_selected_worth').val(0);
                    table = $('#productstable').DataTable({
                        "processing": true, //Feature control the processing indicator.
                        "serverSide": true, //Feature control DataTables' server-side processing mode.
                            "order": [], //Initial no order.
                            "deferRender": true,
                            "searchDelay": 500,
                        responsive: true,
                        <?php datatable_lang(); ?>

                        // Load data for the table's content from an Ajax source
                        "ajax": {
                            "url": url,
                            "type": "POST",
                            'data': {
                                '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                                'group': '<?= $this->input->get('group') ?>',
                                'filter': filter
                            }
                        },

                        //Set column definition initialisation properties.
                        "columnDefs": [{
                                "targets": [0], //first column / numbering column
                                "orderable": false, //set not orderable
                            },
                            {
                                "targets": [7,8],
                                "orderable": false,
                            },
                        ],
                        dom: 'Blfrtip',
                        pageLength: 25,
                        lengthMenu: [
                            [25, 50, 75, 100, -1],
                            [25, 50, 75, 100, "All"]
                        ],
                        buttons: [{
                            extend: 'excelHtml5',
                            footer: true,
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            }
                        }],

                    });

                }
                miniDash();

                $(document).on('click', ".view-object2", function(e) {
                    e.preventDefault();
                    $('#view-object-id').val($(this).attr('data-object-id'));
                    $('#view_model').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    var actionurl = $('#view-action-url').val();
                    actionurl + '/view_history';
                    $.ajax({
                        url: "<?php echo site_url('products/view_history') ?>",
                        // url: "<?php echo base_url(); ?>/Products/view_history",
                        data: 'id=' + $('#view-object-id').val() + '&' + crsf_token + '=' + crsf_hash,
                        type: 'POST',
                        // dataType: 'html',
                        success: function(data) {
                            //const json = JSON.parse(data);

                            $('#view_object').html(data);

                        }

                    });

                });





                $(document).on('click', ".view-object", function(e) {
                    e.preventDefault();
                    $('#view-object-id').val($(this).attr('data-object-id'));

                    $('#view_model').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    var actionurl = $('#view-action-url').val();
                    $.ajax({
                        url: baseurl + actionurl,
                        data: 'id=' + $('#view-object-id').val() + '&' + crsf_token + '=' + crsf_hash,
                        type: 'POST',
                        dataType: 'html',
                        success: function(data) {
                            $('#view_object').html(data);

                        }

                    });

                });
                // Remove extra initial draw to avoid unexpected redraws


                var prod_ids = new Array();
                var selectedIds = new Set();
                var value = 0;
                var sale_value = 0;

                function updateTotalSelectedAmount() {
                    const sales = isNaN(sale_value) ? 0 : sale_value;
                    const purchase = isNaN(value) ? 0 : value;
                    $('#total_selected_worth').val(sales.toFixed(2) + ' / ' + purchase.toFixed(2));
                }
                // Checkbox click event (persist selection)
                $(document).on("click", ".checkbox", function(e) {
                    e.stopPropagation();
                    var data = table.row(this.closest('tr')).data();
                    var purchase_price = parseFloat(data[5]?.replace(/[^\d.-]/g, '')) || 0;
                    var sale_price = parseFloat(data[6]?.replace(/[^\d.-]/g, '')) || 0;
                    var qty = parseFloat(data[2]?.replace(/[^\d.-]/g, '')) || 0;
                    // var pre_amt = parseFloat($('#total_selected_amount').val()) || 0;
                    var pre_amt = parseFloat($('#total_selected_worth').val().split(' / ')[1]) || 0; // Purchase price (right side of the /)
                    var pre_sale_amt = parseFloat($('#total_selected_worth').val().split(' / ')[0]) || 0; // Sales price (left side of the /)


                    if ($(this).prop("checked") == true) {
                        $('#view-object-id').val($(this).attr('data-object-id'));
                        var ids = $('#view-object-id').val();
                        prod_ids.push(ids);
                        selectedIds.add(String(ids));

                        // document.getElementById("edit").onclick = function() {
                        //     location.href = baseurl + "products/new_edit" + '?p_id=' + prod_ids;
                        //     return false;
                        // };

                        value = pre_amt + (purchase_price * qty);
                        sale_value = pre_sale_amt + (sale_price * qty);

                        $('#total_selected_amount').val(value.toFixed(2));
                    } else {
                        var ids = $(this).attr('data-object-id');
                        selectedIds.delete(String(ids));
                        value = pre_amt - (purchase_price * qty);
                        sale_value = pre_sale_amt - (sale_price * qty);

                        if (value < 0) value = 0;
                        if (sale_value < 0) sale_value = 0;

                        $('#total_selected_amount').val(value.toFixed(2));
                    }

                    updateTotalSelectedAmount();
                });

                // Select all on current page
                $(document).on('click', 'th .checkAll', function(e){
                    e.stopPropagation();
                    var checked = $(this).is(':checked');
                    $(table.rows({page:'current'}).nodes()).each(function(){
                        var $row = $(this);
                        var $chk = $row.find('input.checkbox');
                        if ($chk.length){
                            var id = $chk.attr('data-object-id');
                            $chk.prop('checked', checked);
                            if (checked){
                                selectedIds.add(String(id));
                            } else {
                                selectedIds.delete(String(id));
                            }
                        }
                    });
                    recalcTotalsFromPage();
                });

                function recalcTotalsFromPage(){
                    var purchaseSum = 0;
                    var saleSum = 0;
                    $(table.rows({page:'current'}).nodes()).each(function(){
                        var $row = $(this);
                        var $chk = $row.find('input.checkbox');
                        if ($chk.prop('checked')){
                            var data = table.row(this).data();
                            var purchase_price = parseFloat(data[5]?.replace(/[^\d.-]/g, '')) || 0;
                            var sale_price = parseFloat(data[6]?.replace(/[^\d.-]/g, '')) || 0;
                            var qty = parseFloat(data[2]?.replace(/[^\d.-]/g, '')) || 0;
                            purchaseSum += purchase_price * qty;
                            saleSum += sale_price * qty;
                        }
                    });
                    value = purchaseSum;
                    sale_value = saleSum;
                    updateTotalSelectedAmount();
                }

                // Reapply selections after any draw
                $('#productstable').on('draw.dt', function(){
                    var allVisibleChecked = true;
                    $(table.rows({page:'current'}).nodes()).each(function(){
                        var $row = $(this);
                        var $chk = $row.find('input.checkbox');
                        var id = $chk.attr('data-object-id');
                        var isSelected = selectedIds.has(String(id));
                        $chk.prop('checked', isSelected);
                        if (!isSelected){ allVisibleChecked = false; }
                    });
                    $('th .checkAll').prop('checked', allVisibleChecked);
                    recalcTotalsFromPage();
                });

                // Edit selected products: collect checked IDs and redirect
                $(document).on('click', '#edit', function(e) {
                    e.preventDefault();
                    var selectedIds = [];
                    $('#productstable input[type="checkbox"]:checked').not('.checkAll').each(function() {
                        var $cb = $(this);
                        var id = $cb.attr('data-object-id')
                            || $cb.attr('object-id')
                            || $cb.val()
                            || $cb.data('id');

                        if (!id) {
                            var $row = $cb.closest('tr');
                            var $idCarrier = $row.find('[object-id], [data-object-id], .edit_price_grid');
                            id = $idCarrier.attr('object-id') || $idCarrier.attr('data-object-id') || null;
                        }

                        if (id) selectedIds.push(id);
                    });
                    if (selectedIds.length === 0) {
                        alert('Please select at least one product.');
                        return;
                    }
                    window.location.href = baseurl + 'products/new_edit' + '?p_id=' + selectedIds.join(',');
                });

            });
        </script>

        <div id="delete_model" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title"><?php echo $this->lang->line('Delete') ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo $this->lang->line('delete this product') ?></p>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="object-id" value="">
                        <input type="hidden" id="action-url" value="products/delete_i">
                        <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                        <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Cancel') ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div id="view_model" class="modal  fade">
            <div class="modal-dialog modal-lg">
                <div class="modal-content ">
                    <div class="modal-header">
                        <h4 class="modal-title"><?php 
                        $title = $this->lang->line('Product Edit History');

                        echo $title; 
                        ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" id="view_object">
                        <p></p>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="view-object-id" value="">
                        <input type="hidden" id="view-action-url" value="products/view_over">

                        <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Close') ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="edit_price_model" role="dialog">
            <div class="modal-dialog">
                <form action="#">
                    <!--    <form method="post" action="<?php echo base_url() ?>products/updateqty">  -->
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Edit Product Stock</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                <div class="col-md-6" style="display: none;">
                                    <lable>Purchase Price</lable>
                                    <input type="number" class="form-control" name="edit_purchase_price" id="edit_purchase_price" step="any">
                                </div>
                                <div class="col-md-6" style="display: none;">
                                    <lable>Sale Price</lable>
                                    <input type="number" class="form-control" name="edit_sale_price" id="edit_sale_price" step="any">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <lable>Current Stock Quantity</lable>
                                    <input type="number" class="form-control" name="edit_product_qty" id="edit_product_qty" step="any" readonly>
                                    <input type="hidden" class="form-control" name="edit_product_id" id="edit_product_id">
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <lable>New Stock Quantity</lable>
                                    <input type="number" class="form-control" name="edit_product_qty_new" id="edit_product_qty_new" step="any">
                                    <input type="hidden" class="form-control" name="edit_product_name" id="edit_product_name">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="button" class="btn btn-success" id="edit_qty" data-dismiss="modal" value="Save">
                        </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('edit_product_qty_new').addEventListener('input', function() {
            const currentQty = parseFloat(document.getElementById('edit_product_qty').value) || 0;
            const newQty = parseFloat(this.value) || 0;
            total = newQty + currentQty;
            if (0 > total && currentQty > 0) {
                alert('New stock quantity cannot be greater than the current stock quantity in negtive.');
                this.value = 0; // Reset to 0
            }
        });

        $(document).on("click", "#edit_qty", function() {
            var $button = $(this);
            $button.prop('disabled', true).text('Updating...');

            var data = {
                edit_purchase_price: $('#edit_purchase_price').val(),
                edit_product_qty_new: $('#edit_product_qty_new').val(),
                edit_sale_price: $('#edit_sale_price').val(),
                edit_product_qty: $('#edit_product_qty').val(),
                edit_product_name: $('#edit_product_name').val(),
                edit_product_id: $('#edit_product_id').val(),
                '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
            };

            $.ajax({
                url: "<?php echo site_url('products/updateqty') ?>",
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        $("#total_stock").trigger('click');
                        this.edit_product_qty_new = 0; // Reset to 0
                        // window.location.href = data.redirect_url; // Redirect to /products
                    } else {
                        $('#notify').removeClass().addClass('alert alert-danger')
                            .text('Failed to update product quantity.')
                            .fadeIn().delay(3000).fadeOut();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message :
                        'Failed to update product quantity.';

                    $('#notify').removeClass().addClass('alert alert-danger')
                        .text(errorMessage)
                        .fadeIn().delay(3000).fadeOut();
                },
                complete: function() {
                    $button.prop('disabled', false).text('Update Quantity');

                    // Show success message after AJAX completes
                    $('#notify').removeClass().addClass('alert alert-success')
                        .text('Product quantity updated successfully!')
                        .fadeIn().delay(3000).fadeOut();
                }
            });
        });

        var prod_ids = new Array();
        var value = 0; // For purchase price total
        var sale_value = 0; // For sales price total

        function updateTotalSelectedAmount() {
            const sales = isNaN(sale_value) ? 0 : sale_value;
            const purchase = isNaN(value) ? 0 : value;
            $('#total_selected_worth').val(sales.toFixed(2) + ' / ' + purchase.toFixed(2));
        }
        $(document).on("click", ".checkAll", function() {
            // Reset totals
            value = 0;
            sale_value = 0;
            prod_ids = []; // Clear previous selections
            $('#total_selected_amount').val(0);
            $('#total_selected_worth').val('0.00 / 0.00');

            if ($(this).prop("checked") == true) {
                $(".checkbox").each(function() {
                    $(this).prop("checked", true); // Check all individual checkboxes

                    var data = table.row(this.closest('tr')).data();
                    var purchase_price = parseFloat(data[5]?.replace(/[^\d.-]/g, '')) || 0;
                    var sale_price = parseFloat(data[6]?.replace(/[^\d.-]/g, '')) || 0;
                    var qty = parseFloat(data[2]?.replace(/[^\d.-]/g, '')) || 0;

                    // Add product ID to array
                    var ids = $(this).attr('data-object-id');
                    prod_ids.push(ids);

                    // Calculate totals
                    value += purchase_price * qty;
                    sale_value += sale_price * qty;
                });
            } else {
                $(".checkbox").each(function() {
                    $(this).prop("checked", false); // Uncheck all individual checkboxes
                });
                value = 0;
                sale_value = 0;
            }

            // Update display
            $('#total_selected_amount').val(value.toFixed(2));
            updateTotalSelectedAmount();
        });

        $(document).on('click', '.edit_price_grid', function() {
            var product_id = $(this).attr('object-id');

            $('#edit_product_id').val(product_id);
            $.ajax({
                url: baseurl + "products/editqty/" + product_id,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#edit_purchase_price').val(data.price);
                    $('#edit_sale_price').val(data.saleprice);
                    $('#edit_product_qty').val(data.qty);
                    $('#edit_product_name').val(data.name);
                    $('#edit_price_model').modal('show');
                }
            });
        });
    </script>