<div class="content-body">
    <div class="card">

        <div class="card-header">

            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize" onclick="focusMethod()"></i></a></li>
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
                <form method="post" id="data_form" onkeydown="return event.key != 'Enter';">


                    <div class="row">

                        <div class="col-sm-4">

                        </div>

                        <div class="col-sm-3"></div>

                        <div class="col-sm-2"></div>

                        <div class="col-sm-3">

                        </div>

                    </div>

                    <div class="container-fluid">
                        <div class="row">


                            <div class="col-sm-4 cmp-pnl">
                                <div id="customerpanel" class="inner-cmp-pnl">
                                    <div class="form-group row">
                                        <div class="fcol-sm-12">
                                            <h3 class="title">
                                                <?php echo $this->lang->line('Bill From') ?>
                                        </div>
                                    </div>


                                    <div id="customer">
                                        <div class="clientinfo">

                                            <input type="hidden" name="customer_id" id="customer_id" value="0">
                                            <div id="customer_name"></div>
                                        </div>
                                        <div class="clientinfo">

                                            <div id="customer_address1"></div>
                                        </div>

                                        <div class="clientinfo">

                                            <div type="text" id="customer_phone"></div>
                                        </div>
                                        <!--  <hr><?php echo $this->lang->line('Warehouse') ?> <select id="s_warehouses"
                                                                                                class="selectpicker form-control">
                                            <?php echo $this->common->default_warehouse();
                                            echo '<option value="0">' . $this->lang->line('All') ?></option><?php foreach ($warehouse as $row) {
                                                                                                                echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                                                                                            } ?>

                                        </select> -->
                                    </div>


                                </div>
                            </div>

                            <div class="col-sm-3 cmp-pnl">
                                <table class="tfr">
                                    <tbody>
                                        <tr>
                                            <td class=""><strong>Total VAT</strong> (<span class="currenty lightMode"><?= $this->config->item('currency'); ?></span>)
                                            </td>
                                            <td align="right">
                                                <span id="overall-total-vat">0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class=""><strong><?php echo $this->lang->line('Sub Total') ?></strong>
                                                (<span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)
                                            </td>
                                            <td align="right"><span id="overall-total">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class=""><strong><?php echo $this->lang->line('Grand Total') ?></strong>
                                                (<span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)
                                            </td>
                                            <td align="right"><span id="overall-grand-total">0.00</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-sm-5 cmp-pnl">
                                <div class="inner-cmp-pnl">



                                    <div class="form-group row">

                                        <div class="frmSearch col-sm-6"><label for="cst" class="caption"><?php echo $this->lang->line('Search Supplier') ?> </label>
                                            <input type="text" autofocus class="form-control" name="cst" id="supplier-name" placeholder="Enter Supplier Name or Mobile Number to search" autocomplete="off" />

                                            <div id="supplier-box-result"></div>
                                        </div>


                                        <div class="col-sm-6"><label for="invocieno" class="caption">Purchase Order # </label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <input type="text" readonly class="form-control" placeholder="Invoice #" name="invocieno" value="<?php echo $lastinvoice + 1 ?>">
                                            </div>
                                        </div>
                                        <!--  <div class="col-sm-6"><label for="invocieno"
                                                                    class="caption"><?php echo $this->lang->line('Reference') ?> </label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                                    aria-hidden="true"></span></div>
                                                <input type="text" class="form-control" placeholder="Reference #"
                                                    name="refer">
                                            </div>
                                        </div> -->
                                    </div>
                                    <div class="form-group row">

                                        <div class="col-sm-6" style="margin-top: -20px"><label for="invociedate" class="caption"><?php echo $this->lang->line('Order Date') ?> </label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                                <input type="date" class="form-control required" placeholder="Billing Date" name="invoicedate" autocomplete="false" value="<?= date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-6" style="margin-top: -20px"><label for="invocieduedate" class="caption">Expected Delivery Date </label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar-o" aria-hidden="true"></span></div>
                                                <input type="date" class="form-control required" id="tsn_due" name="invocieduedate" placeholder="Due Date" autocomplete="false" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="invocieduedate" class="caption">Payment Status </label>
                                            <select class="form-control" name="pstatus">
                                                <option value="due"> Payment Due</option>
                                                <option value="paid"> Paid</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3"><label for="invocieno" class="caption">Invoice_Ref # </label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <input type="number" class="form-control" placeholder="Invoice Ref #" name="invoice_ref">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <!-- <input type="submit" style="float:right; margin-top:20px;"
                                                class="btn btn-success sub-btn"
                                                value="Send Email"
                                                id="submit-data" data-loading-text="Creating..."> -->
                                            <input type="submit" style="float:right; margin-top:20px;" class="btn btn-success sub-btn" value="Create Purchase" id="submit-data" data-loading-text="Creating...">
                                        </div>
                                        <!-- <div class="col-sm-6">
                                            <input type="submit" style="float:right; margin-top:20px;" class="btn btn-success sub-btn" value="Create Purchase" id="submit-data" data-loading-text="Creating...">
                                        </div> -->

                                    </div>






                                    <!--  <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="toAddInfo"
                                                class="caption"><?php echo $this->lang->line('Order Note') ?> </label>
                                            <textarea class="form-control" name="notes" rows="2"></textarea></div>
                                        </div> -->

                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="saman-row">
                        <table class="table-responsive tfr">

                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="17%" class="text-center"> Product Code</th>
                                    <th width="30%" class="text-center"> Product Description</th>
                                    <th width="10%" class="text-center"><?php echo $this->lang->line('Quantity') ?></th>
                                    <th width="10%" class="text-center"><?php echo $this->lang->line('Price') ?></th>

                                    <th width="10%" class="text-center"><?php echo getTaxName(); ?></th>
                                    <th width="10%" class="text-center">Packing</th>


                                    <th width="8%" class="text-center">Units per Pack</th>

                                    <!--th width="10%" class="text-center"><?php echo $this->lang->line('Discount') ?></th-->

                                    <th width="5%" class="text-center"><?php echo $this->lang->line('Action') ?></th>
                                </tr>

                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < 20; $i++) { ?>
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control inputs" name="product_name[]" placeholder="<?php echo 'Enter Product Name or Code'; ?>" id='productname-<?= $i; ?>' onkeyup="updateOverallTotals()">
                                        </td>
                                        <td><input type="text" id="dpid-<?= $i; ?>" class="form-control product-description" name="product_description[]" placeholder="<?php echo $this->lang->line('Enter Product description'); ?> " autocomplete="off" onkeyup="updateOverallTotals()" /></td>
                                        <td><input type="text" class="form-control req inputs amnt" name="product_qty[]" id="unit-<?= $i; ?>" data-id="<?= $i; ?>" onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()" autocomplete="off" value=""><input type="hidden" id="alert-<?= $i; ?>" value="" name="alert[]"></td>

                                        <td>
                                            <input type="text" class="form-control req inputs prc" name="product_price[]" id="price-<?= $i; ?>" data-id="<?= $i; ?>" onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()" autocomplete="off">
                                        </td>
                                        <td><input type="text" class="form-control vat" name="product_tax[]" id="vat-<?= $i; ?>" data-id="<?= $i; ?>" onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()" autocomplete="off" readonly></td>

                                        <!-- <input type="hidden" class="form-control req inputs prc" name="product_price[]" id="price-<?= $i; ?>" data-id="<?= $i; ?>" onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()" autocomplete="off">
                                        <input type="hidden" class="form-control vat" name="product_tax[]" id="vat-<?= $i; ?>" data-id="<?= $i; ?>" onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()" autocomplete="off" readonly> -->

                                        <td>
                                            <select class="form-control " id="packing_type-<?= $i; ?>" name="packing_type[]">
                                                <option value="pallet" selected> Pallet </option>
                                                <option value="box"> Box </option>
                                            </select>
                                        </td>

                                        <td><input type="text" class="form-control" name="product_units[]" data-id="<?= $i; ?>" id="pack_units-<?= $i; ?>" autocomplete="off" value="" onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()"><input type="hidden" id="alert-<?= $i; ?>" value="" name="alert[]"></td>

                                        <!--<td><input type="text" class="form-control discount" name="product_discount[]"
                                             onkeypress="return isNumber(event)" id="discount-<?= $i; ?>"
                                             onkeyup="rowTotal('<?= $i; ?>'), billUpyog(), updateOverallTotals()" autocomplete="off"></td -->

                                        <td class="text-center">
                                            <!-- All rows: Both plus and minus buttons -->
                                            <button type="button" data-rowid="<?= $i; ?>" class="btn btn-success btn-sm addproduct_purchase" style="padding: 3px 8px; margin-right: 3px;" title="Add New Row">
                                                <i class="fa fa-plus" style="font-size: 12px;"></i>
                                            </button>
                                            <button type="button" data-rowid="<?= $i; ?>" class="btn btn-danger btn-sm removeProd" style="padding: 3px 8px;" title="Remove">
                                                <i class="fa fa-minus" style="font-size: 12px;"></i>
                                            </button>
                                        </td>
                                        <input type="hidden" name="taxa[]" id="taxa-<?= $i; ?>" value="0">
                                        <input type="hidden" name="disca[]" id="disca-<?= $i; ?>" value="0">
                                        <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-<?= $i; ?>" value="0">
                                        <input type="hidden" class="pdIn" name="pid[]" id="pid-<?= $i; ?>" value="0">
                                        <input type="hidden" name="unit[]" id="unit-<?= $i; ?>" value="">
                                        <input type="hidden" name="hsn[]" id="hsn-<?= $i; ?>" value="">
                                        <input type="hidden" name="serial[]" id="serial-<?= $i; ?>" value="">
                                    </tr>
                                <?php } ?>

                                <tr class="sub_c" style="display:none;">
                                    <td colspan="7" class="reverse_align"><input type="hidden" value="0" id="subttlform" name="subtotal"><strong>Total VAT</strong>
                                    </td>
                                    <td align="center" colspan="2"><span class="currenty lightMode"><?= $this->config->item('currency'); ?></span>
                                        <span id="taxr" class="lightMode">0</span>
                                    </td>
                                </tr>
                                <tr class="sub_c" style="display: none;">
                                    <td colspan="7" class="reverse_align">
                                        <strong><?php echo $this->lang->line('Total Discount') ?></strong>
                                    </td>
                                    <td align="center" colspan="2"><span class="currenty lightMode"><?php echo $this->config->item('currency');
                                                                                                    if (isset($_GET['project'])) {
                                                                                                        echo '<input type="hidden" value="' . intval($_GET['project']) . '" name="prjid">';
                                                                                                    } ?></span>
                                        <span id="discs" class="lightMode">0</span>
                                    </td>
                                </tr>

                                <tr class="sub_c" style="display: none;">
                                    <td colspan="7" class="reverse_align">
                                        <strong><?php echo $this->lang->line('Shipping') ?></strong>
                                    </td>
                                    <td align="center" colspan="2"><input type="text" class="form-control shipVal" onkeypress="return isNumber(event)" placeholder="Value" name="shipping" autocomplete="off" onkeyup="billUpyog(), updateOverallTotals()">
                                        ( <?php echo $this->lang->line('Tax') ?> <?= $this->config->item('currency'); ?>
                                        <span id="ship_final">0</span> )
                                    </td>
                                </tr>
                                <tr class="sub_c" style="display: none;">
                                    <td colspan="7" class="reverse_align">
                                        <strong> <?php echo $this->lang->line('Extra') . ' ' . $this->lang->line('Discount') ?></strong>
                                    </td>
                                    <td align="center" colspan="2"><input type="text" class="form-control form-control-sm discVal" onkeypress="return isNumber(event)" placeholder="Value" name="disc_val" autocomplete="off" value="0" onkeyup="billUpyog(), updateOverallTotals()">
                                        <input type="hidden" name="after_disc" id="after_disc" value="0">
                                        ( <?= $this->config->item('currency'); ?>
                                        <span id="disc_final">0</span> )
                                    </td>
                                </tr>


                                <tr class="sub_c" style="display: none;">
                                    <td colspan="2"><?php if (isset($employee)) {
                                                        echo $this->lang->line('Employee')
                                                    ?><br>
                                            <select name="employee" class=" mt-1 col form-control form-control-sm">

                                                <?php foreach ($employee as $row) {
                                                            echo '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . $row['name'] . ')</option>';
                                                        } ?>

                                            </select><?php } ?><br><?php if ($exchange['active'] == 1) {
                                                                        echo $this->lang->line('Payment Currency client') . ' <small>' . $this->lang->line('based on live market') ?></small>
                                            <select name="mcurrency" class="selectpicker form-control">
                                                <option value="0">Default</option>
                                                <?php foreach ($currency as $row) {
                                                                            echo '<option value="' . $row['id'] . '">' . $row['symbol'] . ' (' . $row['code'] . ')</option>';
                                                                        } ?>

                                            </select><?php } ?>
                                    </td>
                                    <td colspan="5" class="reverse_align"><strong><?php echo $this->lang->line('Grand Total') ?>
                                            (<span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)</strong>
                                    </td>
                                    <td align="left" colspan="2"><input type="text" name="total" class="form-control" id="invoiceyoghtml" style=" text-align: center;" readonly="">

                                    </td>
                                </tr>
                                <tr class="sub_c" style="display: none;">
                                    <td colspan="2" style="display: none;"><?php echo $this->lang->line('Payment Terms') ?> <select name="pterms" class="selectpicker form-control"><?php foreach ($terms as $row) {
                                                                                                                                                                                        echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                                                                                                                                                                    } ?>

                                        </select></td>
                                    <td colspan="2">
                                        <div>
                                            <label>Update Stock</label>
                                            <fieldset class="right-radio">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" name="update_stock" id="customRadioRight1" value="yes">
                                                    <label class="custom-control-label" for="customRadioRight1">Yes</label>
                                                </div>
                                            </fieldset>
                                            <fieldset class="right-radio">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" name="update_stock" id="customRadioRight2" value="no" checked="">
                                                    <label class="custom-control-label" for="customRadioRight2">No</label>
                                                </div>
                                            </fieldset>

                                        </div>
                                    </td>
                                    <!--     <td class="reverse_align" colspan="9" ><input type="submit"
                                                                     class="btn btn-success sub-btn btn-lg" style="margin-top:5px;"
                                                                     value="<?php echo $this->lang->line('Generate Invoice') ?> "
                                                                     id="submit-data" data-loading-text="Creating...">

                                                                 </td> -->
                                </tr>


                            </tbody>
                        </table>

                        <?php
                        if (is_array($custom_fields)) {
                            echo '<div class="card">';
                            foreach ($custom_fields as $row) {
                                if ($row['f_type'] == 'text') { ?>
                                    <div class="row mt-1">

                                        <label class="col-sm-8" for="docid"><?= $row['name'] ?></label>

                                        <div class="col-sm-6">
                                            <input type="text" placeholder="<?= $row['placeholder'] ?>" class="form-control margin-bottom b_input <?= $row['other'] ?>" name="custom[<?= $row['id'] ?>]">
                                        </div>
                                    </div>


                        <?php }
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <input type="hidden" value="purchase/action" id="action-url">
                    <input type="hidden" value="purchase_print" id="print-url">
                    <input type="hidden" id="auto_post" value="0">
                    <input type="hidden" id="invoice_format" value="1">
                    <input type="hidden" id="index_prod" value="purchase">
                    <input type="hidden" value="puchase_search" id="billtype">
                    <input type="hidden" value="19" name="counter" id="ganak">
                    <input type="hidden" value="<?php echo $this->config->item('currency'); ?>" name="currency">
                    <input type="hidden" value="<?= $taxdetails['handle']; ?>" name="taxformat" id="tax_format">

                    <input type="hidden" value="<?= $taxdetails['format']; ?>" name="tax_handle" id="tax_status">
                    <input type="hidden" value="yes" name="applyDiscount" id="discount_handle">


                    <input type="hidden" value="<?= $this->common->disc_status()['disc_format']; ?>" name="discountFormat" id="discount_format">
                    <input type="hidden" value="<?= amountFormat_general($this->common->disc_status()['ship_rate']); ?>" name="shipRate" id="ship_rate">
                    <input type="hidden" value="<?= $this->common->disc_status()['ship_tax']; ?>" name="ship_taxtype" id="ship_taxtype">
                    <input type="hidden" value="0" name="ship_tax" id="ship_tax">

                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="addCustomer" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="product_action" class="form-horizontal">
                <!-- Modal Header -->
                <div class="modal-header bg-gradient-directional-success white">

                    <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('Add Supplier') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only"><?php echo $this->lang->line('Close') ?></span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <p id="statusMsg"></p><input type="hidden" name="mcustomer_id" id="mcustomer_id" value="0">


                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label" for="name"><?php echo $this->lang->line('Name') ?></label>

                        <div class="col-sm-10">
                            <input type="text" placeholder="Name" class="form-control margin-bottom" id="mcustomer_name" name="name" required>
                        </div>
                    </div>

                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label" for="phone"><?php echo $this->lang->line('Phone') ?></label>

                        <div class="col-sm-10">
                            <input type="text" placeholder="Phone" class="form-control margin-bottom" name="phone" id="mcustomer_phone">
                        </div>
                    </div>
                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label" for="email">Email</label>

                        <div class="col-sm-10">
                            <input type="email" placeholder="Email" class="form-control margin-bottom crequired" name="email" id="mcustomer_email">
                        </div>
                    </div>
                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label" for="address"><?php echo $this->lang->line('Address') ?></label>

                        <div class="col-sm-10">
                            <input type="text" placeholder="Address" class="form-control margin-bottom " name="address" id="mcustomer_address1">
                        </div>
                    </div>
                    <div class="form-group row">


                        <div class="col-sm-4">
                            <input type="text" placeholder="City" class="form-control margin-bottom" name="city" id="mcustomer_city">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" placeholder="Region" class="form-control margin-bottom" name="region">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" placeholder="Country" class="form-control margin-bottom" name="country" id="mcustomer_country">
                        </div>

                    </div>

                    <div class="form-group row">
                        <div class="col-sm-6">
                            <input type="text" placeholder="PostBox" class="form-control margin-bottom" name="postbox">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" placeholder="TAX ID" class="form-control margin-bottom" name="taxid" id="tax_id">
                        </div>
                    </div>


                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                    <input type="submit" id="msupplier_add" class="btn btn-primary submitBtn" value="<?php echo $this->lang->line('ADD') ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.title = 'Create Purchase';
    function updateOverallTotals() {
        var totalVAT = 0;
        var grandTotal = 0;

        // Loop through each row and accumulate totals
        $('[id^="price-"]').each(function() {
            var rowIndex = $(this).data('id');
            var quantity = parseFloat($('#unit-' + rowIndex).val()) || 0;
            var price = parseFloat($(this).val()) || 0;
            var unitsPerPallet = parseFloat($('#pack_units-' + rowIndex).val()) || 1; // Default to 1 if not provided

            grandTotal += (quantity * unitsPerPallet) * price;
        });

        $('[id^="vat-"]').each(function() {
            var rowIndex = $(this).data('id');
            var quantity = parseFloat($('#unit-' + rowIndex).val()) || 0;
            var vat = parseFloat($(this).val()) || 0;
            var unitsPerPallet = parseFloat($('#pack_units-' + rowIndex).val()) || 1; // Default to 1 if not provided

            totalVAT += ((quantity * unitsPerPallet) * vat);
        });

        // Update the overall totals in your HTML, adjust the selector based on your structure
        $('#overall-total-vat').text(totalVAT.toFixed(2));
        $('#overall-total').text(grandTotal.toFixed(2));
        $('#overall-grand-total').text((parseFloat(grandTotal) + parseFloat(totalVAT)).toFixed(2));
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Prevent flatpickr from initializing on native date inputs
        function preventFlatpickrOnDateInputs() {
            $('input[name="invoicedate"], input[name="invocieduedate"]').each(function() {
                var $this = $(this);
                // Destroy any existing flatpickr instance
                if ($this.data('flatpickr')) {
                    try {
                        $this.data('flatpickr').destroy();
                        $this.removeData('flatpickr');
                    } catch(e) {
                        // Ignore errors
                    }
                }
                // Prevent flatpickr from being initialized
                $this.addClass('no-flatpickr');
            });
        }
        
        // Run immediately
        preventFlatpickrOnDateInputs();
        
        // Run periodically to catch any late initialization
        setInterval(preventFlatpickrOnDateInputs, 500);
        
        // Also run after delays
        setTimeout(preventFlatpickrOnDateInputs, 1000);
        setTimeout(preventFlatpickrOnDateInputs, 2000);
        
        // Update totals on any input change - use event delegation for dynamically added rows
        $(document).on('input change keyup paste blur', 'input[name^="product_price"], input[name^="product_qty"], input[name^="product_tax"], input[name^="product_units"]', function() {
            updateOverallTotals();
        });
        
        // Also update on focus for immediate feedback
        $(document).on('focus', 'input[name^="product_price"], input[name^="product_qty"], input[name^="product_tax"], input[name^="product_units"]', function() {
            updateOverallTotals();
        });
        
        // Update when autocomplete item is selected
        $(document).on('autocompleteselect', 'input[name^="product_name"]', function() {
            setTimeout(function() {
                updateOverallTotals();
            }, 100);
        });
        
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
            updateOverallTotals();
        });
        
        $('input[name^="product_units"]').on('input', function() {
            if (!isNaN($(this).val()) && $(this).val() != '')
                $(this).val(parseInt($(this).val()))
            else
                $(this).val('')
            updateOverallTotals();
        });
        
        // Update totals on page load
        setTimeout(function() {
            updateOverallTotals();
        }, 500);
        
        // Update totals when window regains focus (in case values changed while tab was inactive)
        $(window).on('focus', function() {
            updateOverallTotals();
        });
        
        // Also update on click (as a fallback)
        $(document).on('click', function() {
            updateOverallTotals();
        });
    });
    $(document).ready(function() {
        // Add an event listener to the product price input fields - use event delegation
        $(document).on('input', 'input[name^="product_price"]', function() {
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
            // Update totals after VAT change
            updateOverallTotals();
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

    // Add new row functionality
    $(document).on('click', '.addproduct_purchase', function () {
        var cvalue = parseInt($('#ganak').val()) + 1;
        var nxt = parseInt(cvalue);
        $('#ganak').val(nxt);
        var functionNum = "'" + cvalue + "'";
        
        var newRow = '<tr>' +
            '<td>' +
                '<input type="text" class="form-control inputs" name="product_name[]" placeholder="Enter Product Name or Code" id="productname-' + cvalue + '" onkeyup="updateOverallTotals()">' +
            '</td>' +
            '<td>' +
                '<input type="text" id="dpid-' + cvalue + '" class="form-control product-description" name="product_description[]" placeholder="Enter Product description" autocomplete="off" onkeyup="updateOverallTotals()" />' +
            '</td>' +
            '<td>' +
                '<input type="text" class="form-control req inputs amnt" name="product_qty[]" id="unit-' + cvalue + '" data-id="' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog(), updateOverallTotals()" autocomplete="off" value="">' +
                '<input type="hidden" id="alert-' + cvalue + '" value="" name="alert[]">' +
            '</td>' +
            '<td>' +
                '<input type="text" class="form-control req inputs prc" name="product_price[]" id="price-' + cvalue + '" data-id="' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog(), updateOverallTotals()" autocomplete="off">' +
            '</td>' +
            '<td>' +
                '<input type="text" class="form-control vat" name="product_tax[]" id="vat-' + cvalue + '" data-id="' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog(), updateOverallTotals()" autocomplete="off" readonly>' +
            '</td>' +
            '<td>' +
                '<select class="form-control" id="packing_type-' + cvalue + '" name="packing_type[]">' +
                    '<option value="pallet" selected> Pallet </option>' +
                    '<option value="box"> Box </option>' +
                '</select>' +
            '</td>' +
            '<td>' +
                '<input type="text" class="form-control" name="product_units[]" data-id="' + cvalue + '" id="pack_units-' + cvalue + '" autocomplete="off" value="" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog(), updateOverallTotals()">' +
                '<input type="hidden" id="alert-' + cvalue + '" value="" name="alert[]">' +
            '</td>' +
            '<td class="text-center">' +
                '<button type="button" data-rowid="' + cvalue + '" class="btn btn-success btn-sm addproduct_purchase" style="padding: 3px 8px; margin-right: 3px;" title="Add New Row"> <i class="fa fa-plus" style="font-size: 12px;"></i> </button>' +
                '<button type="button" data-rowid="' + cvalue + '" class="btn btn-danger btn-sm removeProd" style="padding: 3px 8px;" title="Remove"> <i class="fa fa-minus" style="font-size: 12px;"></i> </button>' +
            '</td>' +
            '<input type="hidden" name="taxa[]" id="taxa-' + cvalue + '" value="0">' +
            '<input type="hidden" name="disca[]" id="disca-' + cvalue + '" value="0">' +
            '<input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue + '" value="0">' +
            '<input type="hidden" class="pdIn" name="pid[]" id="pid-' + cvalue + '" value="0">' +
            '<input type="hidden" name="unit[]" id="unit-' + cvalue + '" value="">' +
            '<input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value="">' +
            '<input type="hidden" name="serial[]" id="serial-' + cvalue + '" value="">' +
        '</tr>';
        
        // Insert before the first sub_c row (summary rows)
        $('tr.sub_c').first().before(newRow);
        
        // Ensure all rows have both plus and minus buttons
        $('tr').has('input[name="product_name[]"]').each(function() {
            var $row = $(this);
            var rowId = $row.find('input[name="product_name[]"]').attr('id');
            if (rowId) {
                var rowIndex = rowId.split('-')[1];
                var buttonHtml = '<button type="button" data-rowid="' + rowIndex + '" class="btn btn-success btn-sm addproduct_purchase" style="padding: 3px 8px; margin-right: 3px;" title="Add New Row"><i class="fa fa-plus" style="font-size: 12px;"></i></button>' +
                                 '<button type="button" data-rowid="' + rowIndex + '" class="btn btn-danger btn-sm removeProd" style="padding: 3px 8px;" title="Remove"><i class="fa fa-minus" style="font-size: 12px;"></i></button>';
                $row.find('td.text-center').html(buttonHtml);
            }
        });
        
        // Initialize autocomplete for the new product name field if jQuery UI autocomplete is available
        if (typeof $.fn.autocomplete !== 'undefined' && typeof baseurl !== 'undefined' && typeof billtype !== 'undefined') {
            var row = cvalue;
            $('#productname-' + cvalue).autocomplete({
                source: function (request, response) {
                    let cid = $('#customer_id').val() || $('#supplier_id').val() || 0;
                    $.ajax({
                        url: baseurl + 'search_products/' + billtype,
                        dataType: "json",
                        method: 'post',
                        data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=' + row + '&wid=' + ($("#s_warehouses option:selected").val() || 0) + '&' + (typeof d_csrf !== 'undefined' ? d_csrf : ''),
                        success: function (data) {
                            response($.map(data, function (item) {
                                var product_d = item[7];
                                var product_k = item[0];
                                return {
                                    label: product_d + ' - ' + product_k,
                                    value: product_d,
                                    data: item
                                };
                            }));
                        }
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    id_arr = $(this).attr('id');
                    id = id_arr.split("-");
                    var t_r = ui.item.data[3];
                    if ($("#taxformat option:selected").attr('data-trate')) {
                        t_r = $("#taxformat option:selected").attr('data-trate');
                    }
                    
                    $('#unit-' + id[1]).val(1).trigger('input');
                    $('#price-' + id[1]).val(ui.item.data[1]).trigger('input');
                    $('#pid-' + id[1]).val(ui.item.data[2]);
                    
                    if(ui.item.data[11] == 'T1' && typeof tax_rate_decimal !== 'undefined'){
                        var vat_price = ui.item.data[1] * tax_rate_decimal;
                    } else {
                        var vat_price = 0;
                    }
                    $('#vat-' + id[1]).val(vat_price.toFixed(2)).trigger('input');
                    
                    $('#dpid-' + id[1]).val(ui.item.data[5]);
                    $('#unit-' + id[1]).val(ui.item.data[6] || 1).trigger('input');
                    $('#hsn-' + id[1]).val(ui.item.data[7]);
                    $('#alert-' + id[1]).val(ui.item.data[8]);
                    $('#serial-' + id[1]).val(ui.item.data[10]);
                    // Set Units per Pack (pack_units) - data[8] contains p_to_b (pallet to box conversion, units per pack)
                    var unitsPerPack = ui.item.data[8] || 1;
                    $('#pack_units-' + id[1]).val(unitsPerPack).trigger('input');
                    
                    rowTotal(cvalue);
                    billUpyog();
                    // Update totals immediately after product selection
                    updateOverallTotals();
                },
                create: function (e) {
                    $(this).prev('.ui-helper-hidden-accessible').remove();
                }
            });
        }
        
        // Bind input event handlers for quantity and units
        $('#unit-' + cvalue).on('input', function() {
            if (!isNaN($(this).val()) && $(this).val() != '')
                $(this).val(parseInt($(this).val()))
            else
                $(this).val('')
        });
        
        $('#pack_units-' + cvalue).on('input', function() {
            if (!isNaN($(this).val()) && $(this).val() != '')
                $(this).val(parseInt($(this).val()))
            else
                $(this).val('')
        });
        
        // Bind price input handler for VAT calculation
        $('#price-' + cvalue).on('input', function() {
            var rowIndex = cvalue;
            var productPrice = parseFloat($(this).val());
            var taxaValue = $('#vat-' + rowIndex).val();
            
            if (taxaValue != 0 && typeof tax_rate_decimal !== 'undefined') {
                var vat = Math.floor(productPrice * tax_rate_decimal * 100) / 100;
                $('#vat-' + rowIndex).val(vat.toFixed(2));
            } else {
                $('#vat-' + rowIndex).val(0);
            }
            // Update totals after VAT change
            updateOverallTotals();
        });
        
        // Scroll to the new row
        $('html, body').animate({
            scrollTop: $('#productname-' + cvalue).offset().top - 100
        }, 300);
        
        // Focus on the product name field
        $('#productname-' + cvalue).focus();
    });
    
    // Handle row removal - update button visibility
    $(document).on('click', '.removeProd', function() {
        var $removedRow = $(this).closest('tr');
        var removedRowId = $removedRow.find('input[name="product_name[]"]').attr('id');
        
        // After row is removed (using setTimeout to let the removal happen first)
        setTimeout(function() {
            // Find all product rows (excluding summary rows)
            var $productRows = $('tr').has('input[name="product_name[]"]');
            
            if ($productRows.length > 0) {
                // Get the last product row
                var $lastRow = $productRows.last();
                var lastRowId = $lastRow.find('input[name="product_name[]"]').attr('id');
                
                // Ensure all rows have both plus and minus buttons
                $productRows.each(function() {
                    var $row = $(this);
                    var rowId = $row.find('input[name="product_name[]"]').attr('id');
                    if (rowId) {
                        var rowIndex = rowId.split('-')[1];
                        var buttonHtml = '<button type="button" data-rowid="' + rowIndex + '" class="btn btn-success btn-sm addproduct_purchase" style="padding: 3px 8px; margin-right: 3px;" title="Add New Row"><i class="fa fa-plus" style="font-size: 12px;"></i></button>' +
                                         '<button type="button" data-rowid="' + rowIndex + '" class="btn btn-danger btn-sm removeProd" style="padding: 3px 8px;" title="Remove"><i class="fa fa-minus" style="font-size: 12px;"></i></button>';
                        $row.find('td.text-center').html(buttonHtml);
                    }
                });
                
                // Update totals after row removal
                updateOverallTotals();
            }
        }, 10);
    });
</script>
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