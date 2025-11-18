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
                <form method="post" id="data_form">


                    <div class="row">


                        <div class="col-sm-3 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <h3 class="title">
                                            <?php echo $this->lang->line('Bill From') ?>
                                    </div>
                                </div>
                                <div id="customer">
                                    <div class="clientinfo">

                                        <?php echo '  <input type="hidden" name="customer_id" id="customer_id" value="' . $invoice['csd'] . '">
                                        <div id="customer_name"><strong>' . $invoice['name'] . '</strong></div>
                                        </div>
                                        <div class="clientinfo">

                                        <div id="customer_address1"><strong>' . $invoice['address'] . '<br>' . $invoice['city'] . ',' . $invoice['country'] . '</strong></div>
                                        </div>

                                        <div class="clientinfo">

                                        <div type="text" id="customer_phone">Phone: <strong>' . $invoice['phone'] . '</strong><br>Email: <strong>' . $invoice['email'] . '</strong></div>
                                        </div>'; ?>
                                        <!--  <hr><?php echo $this->lang->line('Warehouse') ?> <select id="s_warehouses"
                                                                                                 class="selectpicker form-control">
                                            <?php echo $this->common->default_warehouse();
                                            echo '<option value="0">' . $this->lang->line('All') ?></option>
                                            <?php foreach ($warehouse as $row) {
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
                                            <td class=""><strong>Total <?php echo getTaxName(); ?></strong> (<span class="currenty lightMode"><?= $this->config->item('currency'); ?></span>)
                                            </td>
                                            <td align="right">
                                                <span class="currenty lightMode"><?= $this->config->item('currency'); ?></span> <span id="overall-total-vat">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class=""><strong><?php echo $this->lang->line('Sub Total') ?></strong>
                                                (<span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)
                                            </td>
                                            <td align="right">
                                                <span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span> <span id="overall-total">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="" style="min-width: 140px"><strong><?php echo $this->lang->line('Grand Total') ?></strong>
                                                (<span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)
                                            </td>
                                            <td align="right">
                                                <span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span> <span id="overall-grand-total">0</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-6 cmp-pnl">
                                <div class="inner-cmp-pnl">

                                    <div class="form-group row">
                                        <div class="frmSearch col-sm-6"><label for="cst" class="caption"><?php echo $this->lang->line('Search Supplier') ?> </label>
                                            <input type="text" autofocus class="form-control inputs" name="cst" id="supplier-name" placeholder="Enter Supplier Name or Mobile Number to search" autocomplete="off" />

                                            <div id="supplier-box-result"></div>
                                        </div>

                                        <div class="col-sm-6"><label for="invocieno" class="caption"> <?php echo $this->lang->line('Purchase Order') ?>
                                                #</label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <input type="text" class="form-control inputs" placeholder="Purchase Order #" name="invocieno" value="<?php echo $invoice['tid']; ?>" readonly><input type="hidden" name="iid" value="<?php echo $invoice['iid']; ?>">
                                            </div>
                                        </div>
                                        <!--  <div class="col-sm-6"><label for="invocieno"
                                                                     class="caption"> <?php echo $this->lang->line('Reference') ?></label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                                     aria-hidden="true"></span></div>
                                                <input type="text" class="form-control" placeholder="Reference #"
                                                       name="refer"
                                                       value="<?php echo $invoice['refer'] ?>">
                                            </div>
                                        </div> -->
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6" style="margin-top: -20px"><label for="invociedate" class="caption">Order Date</label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                                <input type="date" class="form-control inputs required" placeholder="Billing Date" name="invoicedate" autocomplete="false" value="<?php echo date('Y-m-d', strtotime($invoice['invoicedate'])) ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-6" style="margin-top: -20px"><label for="invocieduedate" class="caption">Expected Delivery Date</label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar-o" aria-hidden="true"></span></div>
                                                <input type="date" class="form-control required inputs" name="invocieduedate" placeholder="Due Date" autocomplete="false" value="<?php echo date('Y-m-d', strtotime($invoice['invoiceduedate'])) ?>">
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <label class="caption">Payment Status </label>
                                            <select class="form-control" name="pstatus">
                                                <option value="due" <?php if ($invoice['status'] == "due") {
                                                                        echo "selected";
                                                                    } ?>>Payment Due</option>
                                                <option value="paid" <?php if ($invoice['status'] == "paid") {
                                                                            echo "selected";
                                                                        } ?>> Paid</option>
                                                <?php if ($invoice['status'] == "Verified") {
                                                    echo "<option selected > Verified</option>";
                                                } ?>

                                            </select>
                                        </div>
                                        <div class="col-sm-3"><label for="invocieno" class="caption">Invoice_Ref # </label>

                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <input type="number" class="form-control" placeholder="Invoice Ref #" name="invoice_ref" value="<?php echo $invoice['invoice_ref'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="submit" style="float:right; margin-top:20px;" class="btn btn-success sub-btn" value="Update" id="submit-data" data-loading-text="Creating...">
                                        </div>






                                    </div>

                                    <div class="form-group row" style="display:none">
                                        <div class="col-sm-6">
                                            <label for="taxformat" class="caption"><?php echo $this->lang->line('Tax') ?></label>
                                            <select class="form-control round" onchange="changeTaxFormat(this.value)" id="taxformat">

                                                <?php echo $taxlist; ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">

                                            <div class="form-group">
                                                <label for="discountFormat" class="caption"><?php echo $this->lang->line('Discount') ?></label>
                                                <select class="form-control" onchange="changeDiscountFormat(this.value)" id="discountFormat">
                                                    <?php echo '<option value="' . $invoice['format_discount'] . '">' . $this->lang->line('Do not change') . '</option>'; ?>
                                                    <?php echo $this->common->disclist() ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row" style="display:none">
                                        <div class="col-sm-12">
                                            <label for="toAddInfo" class="caption"><?php echo $this->lang->line('Order Note') ?></label>
                                            <textarea class="form-control" name="notes" rows="2"><?php echo $invoice['notes'] ?></textarea>
                                        </div>
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

                                        <th width="10%" class="text-center">VAT</th>
                                        <th width="10%" class="text-center">Packing</th>


                                        <th width="8%" class="text-center">Units per Pack</th>

                                        <!--th width="10%" class="text-center"><?php echo $this->lang->line('Discount') ?></th-->

                                        <th width="5%" class="text-center"><?php echo $this->lang->line('Action') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 0;
                                    foreach ($products as $row) {

                                        if ($row['packing'] == 'pallet') {
                                            $sel = 'selected';
                                        } else {
                                            $sel = '';
                                        }
                                        if ($row['packing'] == 'box') {
                                            $selb = 'selected';
                                        } else {
                                            $selb = '';
                                        }

                                        echo '<tr >


                            <td><input type="text" id="productname-' .  $i . '" class="form-control inputs ui-autocomplete-input" name="product_name[]" placeholder="Enter Product Name or Code"  value="' . $row['product'] . '"autocomplete="off" onchange="updateOverallTotals()" data-id="'.$i.'">
                            </td>

                            <td ><input type="text"  id="dpid-' . $i . '" class="form-control" value="' . $row['product_des'] . '" name="product_description[]"
                            placeholder="Enter Product description"
                            autocomplete="off" /></td>


                            <td><input type="text" class="form-control inputs req amnt" name="product_qty[]" id="amount-' . $i . '" 
                            onkeypress="return isNumber(event)" onkeyup="rowTotal(' . $i . '), billUpyog(), updateOverallTotals()" data-id="'.$i.'"
                            autocomplete="off" value="' . ceil((int)$row['qty'] / (int)$row['unit']) . '" ><input type="hidden" name="old_product_qty[]" value="' . amountFormat_general($row['qty'] / $row['unit']) . '" ></td>
                            
                            <td>
                                <input type="text" class="form-control req inputs prc" name="product_price[]" id="price-' . $i . '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' . $i . '), billUpyog(), updateOverallTotals()" data-id="'.$i.'" autocomplete="off" value="' . amountFormat_general($row['purchase']) . '">
                            </td>

                            <td><input type="text" class="form-control vat" name="product_tax[]" id="vat-' . $i . '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' . $i . '), billUpyog(), updateOverallTotals()" data-id="'.$i.'" autocomplete="off" readonly value="' . amountFormat_general($row['tax']) . '"></td>

                            <td><select class="form-control" id="packing_type-' . $i . '" name="packing_type[]">
                            <option value="pallet" ' . $sel . '> Pallet </option>
                            <option value="box"  ' . $selb . ' > Box </option>
                            </select></td>

                            <td><input type="text" class="form-control" name="product_units[]" onkeyup="updateOverallTotals()" data-id="'.$i.'" value="' . amountFormat_general($row['unit']) . '" id="pack_units-' . $i . '" autocomplete="off" value=""><input type="hidden" id="alert-' . $i . '" value="" name="alert[]"></td>



                        
                            <td class="text-center">
                                <!-- All rows: Both plus and minus buttons -->
                                <button type="button" data-rowid="' . $i . '" class="btn btn-success btn-sm addproduct_purchase" style="padding: 3px 8px; margin-right: 3px;" title="Add New Row"><i class="fa fa-plus" style="font-size: 12px;"></i></button>
                                <button type="button" data-rowid="' . $i . '" class="btn btn-danger btn-sm removeProd" style="padding: 3px 8px;" title="Remove"><i class="fa fa-minus" style="font-size: 12px;"></i></button>
                            </td>
                            <input type="hidden" name="taxa[]" id="taxa-' . $i . '" value="' . edit_amountExchange_s($row['totaltax'], $invoice['multi'], $this->aauth->get_user()->loc) . '">
                            <input type="hidden" name="disca[]" id="disca-' . $i . '" value="' . edit_amountExchange_s($row['totaldiscount'], $invoice['multi'], $this->aauth->get_user()->loc) . '">
                            <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' . $i . '" value="' . edit_amountExchange_s($row['subtotal'], $invoice['multi'], $this->aauth->get_user()->loc) . '">
                            <input type="hidden" class="pdIn" name="pid[]" id="pid-' . $i . '" value="' . $row['pid'] . '">
                            <input type="hidden" name="unit[]" id="unit-' . $i . '" value="' . $row['unit'] . '">   <input type="hidden" name="hsn[]" id="unit-' . $i . '" value="' . $row['code'] . '">
                            </tr>
                            ';
                                        $i++;
                                    }

                                    if ($i < 20) {
                                        for ($k = $i; $k < 20; $k++) {
                                            echo '<tr>
                <td><input type="text" id="productname-' .  $k . '" class="form-control inputs ui-autocomplete-input" name="product_name[]" placeholder="Enter Product Name or Code"  autocomplete="off">
                            </td>

                            <td ><input type="text"  id="dpid-' . $k . '" class="form-control" name="product_description[]"
                            placeholder="Enter Product description"
                            autocomplete="off" /></td>



                            <td><input type="text" class="form-control inputs req amnt" name="product_qty[]" id="amount-' . $k . '"
                            onkeypress="return isNumber(event)" onkeyup="rowTotal(' . $k . '), billUpyog(), updateOverallTotals()" data-id="'.$k.'"
                            autocomplete="off" ><input type="hidden" name="old_product_qty[]" ></td>
                        
                            <td>
                                <input type="text" class="form-control req inputs prc" name="product_price[]" id="price-' . $k . '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' . $k . '), billUpyog(), updateOverallTotals()" data-id="'.$k.'" autocomplete="off" value="' . amountFormat_general($row['product_price']) . '">
                            </td>

                            <td><input type="text" class="form-control vat" name="product_tax[]" id="vat-' . $k . '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' . $k . '), billUpyog(), updateOverallTotals()" data-id="'.$k.'" autocomplete="off" readonly value="' . amountFormat_general($row['product_tax']) . '"></td>

                            <td><select class="form-control" id="packing_type-' . $k . '" name="packing_type[]">
                            <option value="pallet" selected> Pallet </option>
                            <option value="box"> Box </option>
                            </select></td>

                            <td><input type="text" class="form-control" name="product_units[]" onkeypress="return isNumber(event)" onkeyup="updateOverallTotals()" data-id="'.$k.'" id="pack_units-' . $k . '" autocomplete="off" value=""><input type="hidden" id="alert-' . $k . '" value="" name="alert[]"></td>


                            <td class="text-center">
                                <!-- All rows: Both plus and minus buttons -->
                                <button type="button" data-rowid="' . $k . '" class="btn btn-success btn-sm addproduct_purchase" style="padding: 3px 8px; margin-right: 3px;" title="Add New Row"><i class="fa fa-plus" style="font-size: 12px;"></i></button>
                                <button type="button" data-rowid="' . $k . '" class="btn btn-danger btn-sm removeProd" style="padding: 3px 8px;" title="Remove"><i class="fa fa-minus" style="font-size: 12px;"></i></button>
                            </td>
                            <input type="hidden" name="taxa[]" id="taxa-' . $k . '" >
                            <input type="hidden" name="disca[]" id="disca-' . $k . '" >
                            <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' . $k . '" >
                            <input type="hidden" class="pdIn" name="pid[]" id="pid-' . $k . '" >
                            <input type="hidden" name="unit[]" id="unit-' . $k . '">   <input type="hidden" name="hsn[]" id="unit-' . $k . '" >
                            </tr>
                            ';
                                        }
                                    }

                                    ?>
                                    <!--tr class="last-item-row sub_c">
                            <td class="add-row">
                                <button type="button" class="btn btn-success" id="addproduct_purcahse_order">
                                    <i class="fa fa-plus-square"></i> <?php echo $this->lang->line('Add Row') ?>
                                </button>
                            </td>
                            <td colspan="7"></td>
                        </tr -->

                                    <tr class="sub_c" style="display: none;">
                                        <td colspan="6" align="right">
                                            <strong><?php echo $this->lang->line('Total Tax') ?></strong>
                                        </td>
                                        <td align="left" colspan="2"><span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>
                                            <span id="taxr" class="lightMode"><?= edit_amountExchange_s($invoice['tax'], $invoice['multi'], $this->aauth->get_user()->loc) ?></span>
                                        </td>
                                    </tr>
                                    <tr class="sub_c" style="display: none;">
                                        <td colspan="6" align="right">
                                            <strong><?php echo $this->lang->line('Total Discount') ?></strong>
                                        </td>
                                        <td align="left" colspan="2"><span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>
                                            <span id="discs" class="lightMode"><?php echo edit_amountExchange_s($invoice['discount'], $invoice['multi'], $this->aauth->get_user()->loc) ?></span>
                                        </td>
                                    </tr>

                                    <tr class="sub_c" style="display: none;">
                                        <td colspan="6" align="right"><input type="hidden" value="<?php echo edit_amountExchange_s($invoice['subtotal'], $invoice['multi'], $this->aauth->get_user()->loc) ?>" id="subttlform" name="subtotal"><strong><?php echo $this->lang->line('Shipping') ?></strong>
                                        </td>
                                        <td align="left" colspan="2"><input type="text" class="form-control shipVal" onkeypress="return isNumber(event)" placeholder="Value" name="shipping" autocomplete="off" onkeyup="billUpyog()" value="<?php if ($invoice['ship_tax_type'] == 'excl') {
                                                                                                                                                                                                                                                    $invoice['shipping'] = $invoice['shipping'] - $invoice['ship_tax'];
                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                echo amountExchange_s($invoice['shipping'], $invoice['multi'], $this->aauth->get_user()->loc); ?>">( <?= $this->lang->line('Tax') ?> <?= $this->config->item('currency'); ?>
                                            <span id="ship_final"><?= edit_amountExchange_s($invoice['ship_tax'], $invoice['multi'], $this->aauth->get_user()->loc) ?> </span>
                                            )
                                        </td>
                                    </tr>

                                    <tr class="sub_c" style="display: none;">
                                        <td colspan="2"><?php if ($exchange['active'] == 1) {
                                                            echo $this->lang->line('Payment Currency client') . ' <small>' . $this->lang->line('based on live market') ?></small>
                                                <select name="mcurrency" class="selectpicker form-control">

                                                    <?php
                                                            echo '<option value="' . $invoice['multi'] . '">Do not change</option><option value="0">None</option>';
                                                            foreach ($currency as $row) {

                                                                echo '<option value="' . $row['id'] . '">' . $row['symbol'] . ' (' . $row['code'] . ')</option>';
                                                            } ?>

                                                </select><?php } ?>
                                        </td>
                                        <td colspan="4" align="right"><strong><?php echo $this->lang->line('Grand Total') ?>
                                                (<span class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)</strong>
                                        </td>
                                        <td align="left" colspan="2"><input type="text" name="total" class="form-control" id="invoiceyoghtml" value="<?= edit_amountExchange_s($invoice['total'], $invoice['multi'], $this->aauth->get_user()->loc); ?>" readonly="">

                                        </td>
                                    </tr>
                                    <tr class="sub_c" style="display: none;">
                                        <td colspan="2"><?php echo $this->lang->line('Payment Terms') ?> <select name="pterms" class="selectpicker form-control"><?php echo '<option value="' . $invoice['termid'] . '">*' . $invoice['termtit'] . '</option>';
                                                                                                                                                                    foreach ($terms as $row) {
                                                                                                                                                                        echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                                                                                                                                                    } ?>


                                            </select></td>
                                        <td colspan="2">
                                            <div>
                                                <label><?php echo $this->lang->line('Update Stock') ?></label>

                                                <fieldset class="right-radio">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" name="update_stock" id="customRadioRight1" value="yes">
                                                        <label class="custom-control-label" for="customRadioRight1"><?php echo $this->lang->line('Yes') ?></label>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="right-radio">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" name="update_stock" id="customRadioRight2" value="no" checked="">
                                                        <label class="custom-control-label" for="customRadioRight2"><?php echo $this->lang->line('No') ?></label>
                                                    </div>
                                                </fieldset>

                                            </div>
                                        </td>
                                        <!--   <td align="right" colspan="4"><input type="submit" class="btn btn-success sub-btn"
                           value="<?php echo $this->lang->line('Update Order') ?>"
                           id="submit-data"
                           data-loading-text="Updating...">
                       </td> -->
                                    </tr>


                                </tbody>
                            </table>
                        </div>
                        <?php $gank;
                        if ($i < 19) {
                            $gank = 19;
                        } else {
                            $gank = $i;
                        } ?>
                        <input type="hidden" value="purchase/editaction2" id="action-url">
                        <input type="hidden" value="puchase_search" id="billtype">
                        <input type="hidden" value="<?php echo $gank; ?>" name="counter" id="ganak">
                        <input type="hidden" value="<?php echo $this->config->item('currency'); ?>" name="currency">

                        <input type="hidden" value="<?= $this->common->taxhandle_edit($invoice['taxstatus']) ?>" name="taxformat" id="tax_format">
                        <input type="hidden" value="<?= $invoice['format_discount']; ?>" name="discountFormat" id="discount_format">
                        <input type="hidden" value="<?= $invoice['taxstatus']; ?>" name="tax_handle" id="tax_status">
                        <input type="hidden" value="yes" name="applyDiscount" id="discount_handle">

                        <input type="hidden" value="<?php
                                                    $tt = 0;
                                                    if ($invoice['ship_tax_type'] == 'incl') $tt = @number_format(($invoice['shipping'] - $invoice['ship_tax']) / $invoice['shipping'], 2, '.', '');
                                                    echo amountFormat_general(number_format((($invoice['ship_tax'] / $invoice['shipping']) * 100) + $tt, 3, '.', '')); ?>" name="shipRate" id="ship_rate">
                        <input type="hidden" value="<?= $invoice['ship_tax_type']; ?>" name="ship_taxtype" id="ship_taxtype">
                        <input type="hidden" value="<?= amountFormat_general($invoice['ship_tax']); ?>" name="ship_tax" id="ship_tax">


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
                <div class="modal-header">

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


                        <div class="col-sm-6">
                            <input type="text" placeholder="City" class="form-control margin-bottom" name="city" id="mcustomer_city">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" placeholder="Region" class="form-control margin-bottom" name="region">
                        </div>

                    </div>

                    <div class="form-group row">


                        <div class="col-sm-6">
                            <input type="text" placeholder="Country" class="form-control margin-bottom" name="country" id="mcustomer_country">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" placeholder="PostBox" class="form-control margin-bottom" name="postbox">
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
<script type="text/javascript">
    document.title = '<?php echo $this->lang->line('Edit Purchase Order') . ' - ' . $invoice['tid']; ?>';
    $('.editdate').datepicker({
        autoHide: true,
        format: '<?php echo $this->config->item('dformat2'); ?>'
    });
</script>


<script type="text/javascript">
    function updateOverallTotals() {
        var totalVAT = 0;
        var grandTotal = 0;

        // Loop through each row and accumulate totals - use accounting.unformat for proper parsing
        $('[id^="price-"]').each(function() {
            var rowIndex = $(this).attr('id').split('-')[1];
            var quantity = accounting.unformat($('#amount-' + rowIndex).val(), accounting.settings.number.decimal) || 0;
            var price = accounting.unformat($(this).val(), accounting.settings.number.decimal) || 0;
            var unitsPerPallet = accounting.unformat($('#pack_units-' + rowIndex).val(), accounting.settings.number.decimal) || 1;

            grandTotal += (quantity * unitsPerPallet) * price;
        });

        $('[id^="vat-"]').each(function() {
            var rowIndex = $(this).attr('id').split('-')[1];
            var quantity = accounting.unformat($('#amount-' + rowIndex).val(), accounting.settings.number.decimal) || 0;
            var vat = accounting.unformat($(this).val(), accounting.settings.number.decimal) || 0;
            var unitsPerPallet = accounting.unformat($('#pack_units-' + rowIndex).val(), accounting.settings.number.decimal) || 1;

            totalVAT += ((quantity * unitsPerPallet) * vat);
        });

        // Update the overall totals in your HTML - immediate update
        $('#overall-total-vat').text(accounting.formatNumber(totalVAT));
        $('#overall-total').text(accounting.formatNumber(grandTotal));
        $('#overall-grand-total').text(accounting.formatNumber(parseFloat(grandTotal) + parseFloat(totalVAT)));
    }
    $(document).ready(function() {
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
        
        // Update on autocomplete selection - immediate
        $('.ui-menu-item-wrapper').on('click', function() {
            setTimeout(function() {
                updateOverallTotals();
            }, 300);
        });

        // Format quantity inputs
        $(document).on('input', 'input[name^="product_qty"]', function() {
            if (!isNaN($(this).val()) && $(this).val() != '')
                $(this).val(parseInt($(this).val()))
            else
                $(this).val('')
            updateOverallTotals();
        });
        
        // Format units per pack inputs
        $(document).on('input', 'input[name^="product_units"]', function() {
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

            // Get the product price value using accounting.unformat for proper parsing (like rowTotal in custom____script.js)
            var productPrice = accounting.unformat($(this).val(), accounting.settings.number.decimal) || 0;

            // Get the value of the corresponding taxa hidden field
            var taxaValue = $('#vat-' + rowIndex).val();

            // Check if the taxa value is 'T1' before calculating VAT
            if (taxaValue != 0) {
                // Calculate VAT (20% of the product price) - using unformatted value for precision
                var vat = productPrice * 0.2;

                // Update the corresponding VAT input field with 3 decimal places
                $('#vat-' + rowIndex).val(accounting.formatNumber(vat));
            } else {
                // If taxa is not 'T1', reset the VAT field
                $('#vat-' + rowIndex).val(0);
            }
            // Update totals immediately after VAT change
            updateOverallTotals();
        });
    });


    $(document).ready(function() {
        $('.inputs').keydown(function(e) {
            if (e.which === 13) {
                var index = $('.inputs').index(this) + 1;
                $('.inputs').eq(index).focus().select();;
                $('html, body').animate({
                    scrollTop: $(window).scrollTop() + 10
                });
                event.preventDefault();
                return false;
            }
        });

    });


    //$(document).ready(function() {


    // $('.inputs').keydown(function (e) {
    //      if (e.which === 13) {
    //          var index = $('.inputs').index(this) + 1;
    //          $('.inputs').eq(index).focus().select();
    //         $('html, body').animate({
    //     scrollTop: $(window).scrollTop() + 10
    // });
    //       event.preventDefault();
    //       return false;
    //      }
    //  });
    // });










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
                '<input type="text" class="form-control req inputs amnt" name="product_qty[]" id="amount-' + cvalue + '" data-id="' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog(), updateOverallTotals()" autocomplete="off" value="">' +
                '<input type="hidden" name="old_product_qty[]">' +
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
                    
                    $('#amount-' + id[1]).val(1).trigger('input');
                    $('#price-' + id[1]).val(ui.item.data[1]).trigger('input');
                    $('#pid-' + id[1]).val(ui.item.data[2]);
                    
                    if(ui.item.data[11] == 'T1' && typeof tax_rate_decimal !== 'undefined'){
                        var vat_price = ui.item.data[1] * tax_rate_decimal;
                    } else {
                        var vat_price = 0;
                    }
                    $('#vat-' + id[1]).val(vat_price.toFixed(2)).trigger('input');
                    
                    $('#dpid-' + id[1]).val(ui.item.data[5]);
                    $('#amount-' + id[1]).val(ui.item.data[6] || 1).trigger('input');
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
        $('#amount-' + cvalue).on('input', function() {
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