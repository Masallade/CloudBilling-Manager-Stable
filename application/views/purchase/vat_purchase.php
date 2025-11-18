<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">VAT Report By Purchase </h4>
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
            <form method="POST" id="stock_return_report" action="<?php echo site_url('purchase/vat_purchase_report') ?>" target="_blank">
        <div class="row">
            <div class="col-md-2">PO Date Between:</div>
            <div class="col-md-2">
                <input type="date" name="start_date" id="start_prod_date" class="form-control form-control-sm" autocomplete="off">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" id="end_prod_date" class="form-control form-control-sm" autocomplete="off">
            </div>
            <div class="col-md-2">
                <input type="button" name="print_report" id="print_report" value="Print Report" class="btn btn-info btn-sm">
            </div>
        </div>

        <!-- Hidden input fields for form data -->
        <input type="hidden" name="customer_id" id="customer_id" value="0">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

        <!-- Add other hidden input fields as needed -->

        <!-- Your other content goes here -->

    </form>
</div>


            </div>
        </div>


        <div id="MyInvoices" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
    </div>
 </div>

<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Delete Invoice') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('delete this invoice') ?> ?</p>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>
<!--change added by me-->
<form method="POST" id="stock_return_report" action="<?php echo site_url('purchase/purchase_by_supplier_report') ?>" target="_blank">

    <input type="hidden" id="prod_id" name="prod_id" value="" />
    <input type="hidden" id="start_date" name="start_date" value="" />
    <input type="hidden" id="end_date" name="end_date" value="" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>


<style type="text/css">
    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background: #6693bc !important;
        font-weight: bold !important;
        color: #ffffff !important;
    }

    .ui-autocomplete {
        z-index: 9999;
    }

    .content-wrapper {

        z-index: 0;
    }
</style>




<script type="text/javascript">
    $(document).ready(function() {

        $("#stock-return-box").autocomplete({

            source: function(request, response) {

                let cid = $('#stock-return-box').val();

                $.ajax({


                    type: "GET",
                    dataType: "json",
                    url: baseurl + 'search_products/search_stock',
                    data: 'keyword=' + cid + '&' + crsf_token + '=' + crsf_hash,

                    success: function(data) {
                        response($.map(data, function(item) {
                            var product_d = item[1];
                            var product_k = item[4];
                            return {
                                label: product_d + '-' + product_k,
                                value: product_d,
                                data: item
                            };
                        }));
                    }
                });
            },
            select: function(event, ui) {
                var currentDate = Date.now();
                var $cust_id = ui.item.data[1];

                var add1 = '';
                var add2 = '';
                var add3 = '';

                $('#pid').val(ui.item.data[0]);
                $('#pcode').val(ui.item.data[2]);
                $('#pdesc').val(ui.item.data[3]);
                $('#pcat').val(ui.item.data[4]);
                var cname = ui.item.data[1];
                $('#pcode').focus();

            }

        });



        $('#print_report').click(function() {

            start_date = $('#start_prod_date').val();
            end_date = $('#end_prod_date').val();
            prod_id = $('#pid').val();
            $('#start_date').val(start_date);
            $('#end_date').val(end_date);
            $('#prod_id').val(prod_id);

            $('#stock_return_report').submit();

        });

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        filter_check = $(location).attr("href").split('/').pop();
        if (filter_check == "monthly") {
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            end_date = day + '-' + (month < 10 ? '0' : '') + month + '-' + d.getFullYear();
            start_date = '1' + '-' + (month < 10 ? '0' : '') + month + '-' + d.getFullYear();
            $('#start_date').val(start_date);
            $('#end_date').val(end_date);
            draw_data(start_date, end_date);
        }










        $('#search').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var start_cat = $('#start_cat').val();
            var end_cat = $('#end_cat').val();
            if (start_date != '' && end_date != '') {
                //change added by me
                $('#hidden_start_date').val(start_date);
                $('#hidden_end_date').val(end_date);
                $('#hidden_start_category').val(start_cat);
                $('#hidden_end_category').val(end_cat);

                $('#invoices').DataTable().destroy();
                draw_data(start_date, end_date, start_cat, end_cat);
            } else {
                alert("Date range is Required");
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Add an event listener to the product price input fields
        $('input').keydown(updateOverallTotals);
        $('input').focus(updateOverallTotals);
        $('.ui-menu-item-wrapper').click(function() {
            setTimeout(() => {
                updateOverallTotals();
            }, 300);
        });

        $('input[name^="product_qty"]').on('input', function() {
            if (!isNaN($(this).val()) && $(this).val() != '')
                $(this).val(parseInt($(this).val()))
            else
                $(this).val('')

        });
        $('input[name^="product_units"]').on('input', function() {
            if (!isNaN($(this).val()) && $(this).val() != '')
                $(this).val(parseInt($(this).val()))
            else
                $(this).val('')
        });
    });
    $(document).ready(function() {
        // Add an event listener to the product price input fields
        $('input[name^="product_price"]').on('input', function() {
            // Get the row index from the input field's ID
            var rowIndex = this.id.split('-')[1];

            // Get the product price value
            var productPrice = parseFloat($(this).val());

            // Get the value of the corresponding taxa hidden field
            var taxaValue = $('#vat-' + rowIndex).val();

            // Check if the taxa value is 'T1' before calculating VAT
            if (taxaValue != 0) {
                // Calculate VAT using dynamic tax rate (truncate, not round)
                var vat = Math.floor(productPrice * tax_rate_decimal * 100) / 100;

                // Update the corresponding VAT input field
                $('#vat-' + rowIndex).val(vat.toFixed(2));
            } else {
                // If taxa is not 'T1', reset the VAT field
                $('#vat-' + rowIndex).val(0);
            }
        });
    });
    $(document).ready(function() {

        $('.inputs').keydown(function(e) {
            if (e.which === 13) {
                var index = $('.inputs').index(this) + 1;
                $('.inputs').eq(index).focus();
                $('html, body').animate({
                    scrollTop: $(window).scrollTop() + 10
                });
                event.preventDefault();
                return false;
            }
        });

    });

    focusMethod = function getFocus() {
        document.getElementById("supplier-name").focus();
    }
</script>