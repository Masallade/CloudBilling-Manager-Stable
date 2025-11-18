

<div class="content-body">
    <div class="card">
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <form method="post" id="data_form">
                    <div class="row">
                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-3"></div>
                        <div class="col-sm-2"></div>
                        <div class="col-sm-3">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12">
                                        <h3 class="title">
                                            Return Product To
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="frmSearch col-sm-12"><label for="cst"
                                                                            class="caption"><?php echo $this->lang->line('Search Customer') ?> </label>
                                        <input type="text" class="form-control" name="cst" id="customer-box1"
                                               placeholder="Enter Customer Name or Mobile Number to search"
                                               autocomplete="off"/>

                                        <div id="customer-box-result"></div>
                                    </div>

                                </div>
                                <div id="customer">
                                    <div class="clientinfo">
                                        <?php echo $this->lang->line('Customer Details') ?>
                                        <hr>
                                        <input type="hidden" name="customer_id" id="customer_id" value="0">
                                        <div id="customer_name"></div>
                                    </div>
                                    <div class="clientinfo">

                                        <div id="customer_address1"></div>
                                    </div>

                                    <div class="clientinfo">

                                        <div type="text" id="customer_phone"></div>
                                    </div>
                                    <!-- <hr><?php echo $this->lang->line('Warehouse') ?> <select id="warehouses"
                                                                                             class="selectpicker form-control">
                                        <option value="0"><?php echo $this->lang->line('All') ?></option><?php foreach ($warehouse as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                        } ?>

                                    </select>-->
                                </div>


                            </div>
                        </div>
                        <div class="col-sm-6 cmp-pnl">
                            <div class="inner-cmp-pnl">


                                <div class="form-group row">

                                    <div class="col-sm-12"><h4
                                                class="title"><?php echo $this->lang->line('Customer') ?> <?php echo $this->lang->line('Stock Return') ?> </h4>
                                    </div>

                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6"><label for="invocieno"
                                                                 class="caption"><?php echo $this->lang->line('Order') ?> </label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="text" class="form-control" placeholder="Invoice #"
                                                   name="invocieno"
                                                   value="<?php echo $lastinvoice + 1 ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6"><label for="invocieno"
                                                                 class="caption"><?php echo $this->lang->line('Reference') ?> </label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="text" class="form-control" placeholder="Reference #"
                                                   name="refer">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">

                                    <div class="col-sm-6"><label for="invociedate"
                                                                 class="caption"><?php echo $this->lang->line('Order Date') ?> </label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="date" class="form-control required"
                                                   placeholder="Billing Date" name="invoicedate"
                                                   autocomplete="false">
                                        </div>
                                    </div>
                                    <div class="col-sm-6"><label for="invocieduedate"
                                                                 class="caption"><?php echo $this->lang->line('Order Due Date') ?> </label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar-o"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="date" class="form-control required" id="tsn_due"
                                                   name="invocieduedate"
                                                   placeholder="Due Date" autocomplete="false">
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label for="taxformat"
                                               class="caption"><?php echo $this->lang->line('Tax') ?> </label>
                                        <select class="form-control round"
                                                onchange="changeTaxFormat(this.value)"
                                                id="taxformat">
                                            <?php echo $taxlist; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">

                                        <div class="form-group">
                                            <label for="discountFormat"
                                                   class="caption"><?php echo $this->lang->line('Discount') ?></label>
                                            <select class="form-control" onchange="changeDiscountFormat(this.value)"
                                                    id="discountFormat">
                                                <?php echo $this->common->disclist() ?>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="toAddInfo"
                                               class="caption">Invoice Return Note</label>
                                        <textarea class="form-control" name="notes" rows="2"></textarea></div>
                                </div>

                            </div>
                        </div>

                    </div>


                    <div id="saman-row">
                        <table class="table-responsive tfr my_stripe">
                            <thead>

                            <tr class="item_header bg-gradient-directional-pink white">
                                <th width="10%" class="text-center"><?php echo $this->lang->line('Item Name') ?></th>
                                <th width="20%" class="text-center">Description</th>
                                <th width="8%" class="text-center"><?php echo $this->lang->line('Quantity') ?></th>
                                <th width="10%" class="text-center"><?php echo $this->lang->line('Rate') ?></th>
                                <th width="10%" class="text-center"><?php echo $this->lang->line('Tax') ?>(%)</th>
                                <th width="10%" class="text-center"><?php echo $this->lang->line('Tax') ?></th>
                                <!-- <th width="7%" class="text-center"><?php echo $this->lang->line('Discount') ?></th> -->
                                <th width="10%" class="text-center">
                                    <?php echo $this->lang->line('Amount') ?>
                                    (<?php echo $this->config->item('currency'); ?>)
                                </th>
                                <th width="5%" class="text-center"><?php echo $this->lang->line('Action') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr style=" background-color: #FFFFFF;">
                                <td><input type="text" class="form-control" data-index="0" name="product_name[]"
                                           placeholder="<?php echo $this->lang->line('Enter Product name') ?>"
                                           id='productname_1-0' required>
                                </td>
                                <td ><input id="dpid-0" class="form-control" name="product_description[]"  data-index="0"
                                                          placeholder="<?php echo $this->lang->line('Enter Product description'); ?>"
                                                          autocomplete="off"></td>
                                <td><input type="text" class="form-control req amnt" name="product_qty[]"  data-index="0" id="amount-0"
                                           autocomplete="off" value="1"></td>
                                <td><input type="text" class="form-control req prc" name="product_price[]"  data-index="0" id="price-0"
                                           autocomplete="off"></td>
                                <td><input type="text" class="form-control vat " name="product_tax[]"  data-index="0" id="vat-0"
                                           autocomplete="off"></td>
                                <td class="text-center vat-inc-amount" id="texttaxa-0">0</td>
                           
                                <td class="text-center"><span class="currenty"><?php echo $this->config->item('currency'); ?></span>
                                    <strong><span class='ttlText' id="result-0">0</span></strong></td>


                                <input type="hidden" name="taxa[]"  data-index="0" id="taxa-0" value="0">
                                <input type="hidden" name="disca[]"  data-index="0" id="disca-0" value="0">
                                <input type="hidden" class="ttInput" name="product_subtotal[]"  data-index="0" id="total-0" value="0">
                                <input type="hidden" class="pdIn" name="pid[]"  data-index="0" id="pid-0" value="0">
                                <input type="hidden" name="unit[]"  data-index="0" id="unit-0" value="">
                                <input type="hidden" name="hsn[]"  data-index="0" id="hsn-0" value="">
                                <input type="hidden" name="inv_id[]"  data-index="0" id="inv_id-0" value="">
                            </tr>
           

                            <tr class="last-item-row" style="background-color:#ffffff">
                                <td class="add-row">
                                    <button type="button" class="btn btn-success" id="addproduct1">
                                        <i class="fa fa-plus-square"></i> <?php echo $this->lang->line('Add Row') ?>
                                    </button>
                                </td>
                                <td colspan="7"></td>
                            </tr>

                            <tr class="sub_c" style="display: table-row;">
                                <td colspan="4" align="right"><input type="hidden" value="0" id="subttlform"
                                                                     name="subtotal"><strong><?php echo $this->lang->line('Total Tax') ?></strong>
                                </td>
                                <td align="left" colspan="4"><span
                                            class="currenty lightMode">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->config->item('currency'); ?></span>
                                    <span id="taxr" class="lightMode">0</span></td>
                            </tr>
                        <!--    <tr class="sub_c" style="display: table-row;">
                                <td colspan="6" align="right">
                                    <strong><?php echo $this->lang->line('Total Discount') ?></strong></td>
                                <td align="left" colspan="2"><span
                                            class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>
                                    <span id="discs" class="lightMode">0</span></td>
                            </tr>

                            <tr class="sub_c" style="display: table-row;">
                                <td colspan="6" align="right">
                                    <strong><?php echo $this->lang->line('Shipping') ?></strong></td>
                                <td align="left" colspan="2"><input type="text" class="form-control shipVal"
                                                                    onkeypress="return isNumber(event)"
                                                                    placeholder="Value"
                                                                    name="shipping" autocomplete="off"
                                                                    onkeyup="billUpyog();">
                                    ( <?php echo $this->lang->line('Tax') ?> <?= $this->config->item('currency'); ?>
                                    <span id="ship_final">0</span> )
                                </td>
                            </tr>-->

                            <tr class="sub_c" style="display: table-row;">
                             <!--   <td colspan="2"><?php if ($exchange['active'] == 1){
                                    echo $this->lang->line('Payment Currency client') . ' <small>' . $this->lang->line('based on live market') ?></small>
                                    <select name="mcurrency"
                                            class="selectpicker form-control">
                                        <option value="0">Default</option>
                                        <?php foreach ($currency as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['symbol'] . ' (' . $row['code'] . ')</option>';
                                        } ?>

                                    </select><?php } ?></td>-->
                                <td colspan="4" align="right"><strong><?php echo $this->lang->line('Grand Total') ?>
                                        (<span
                                                class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)&nbsp;&nbsp;&nbsp;&nbsp;</strong>
                                </td>
                                <td align="left" colspan="4"><input type="text" name="total" class="form-control"
                                                                    id="invoiceyoghtml" readonly="">

                                </td>
                            </tr>
                            <tr class="sub_c" style="display: table-row;">
                                <td colspan="2"><?php echo $this->lang->line('Payment Terms') ?> <select name="pterms"
                                                                                                         class="selectpicker form-control"><?php foreach ($terms as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                        } ?>

                                    </select></td>
                                <td colspan="2">
                                    <div>
                                        <label><?php echo $this->lang->line('Update Stock') ?></label>
                                        <fieldset class="right-radio">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="update_stock"
                                                       id="customRadioRight1" value="yes" checked="">
                                                <label class="custom-control-label"
                                                       for="customRadioRight1"><?php echo $this->lang->line('Yes') ?></label>
                                            </div>
                                        </fieldset>
                                        <fieldset class="right-radio">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="update_stock"
                                                       id="customRadioRight2" value="no">
                                                <label class="custom-control-label"
                                                       for="customRadioRight2"><?php echo $this->lang->line('No') ?></label>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                                <td align="right" colspan="4"><input type="submit" class="btn btn-success sub-btn"
                                                                     value="<?php echo $this->lang->line('Generate Order') ?>"
                                                                     id="submit-data" data-loading-text="Creating...">

                                </td>
                            </tr>


                            </tbody>
                        </table>
                    </div>

                    <input type="hidden" value="stockreturn/action" id="action-url">
                    <input type="hidden" value="1" name="person_type">
                    <input type="hidden" value="puchase_search" id="billtype">
                    <input type="hidden" value="0" name="counter" id="ganak">
                    <input type="hidden" value="<?php echo $this->config->item('currency'); ?>" name="currency">
                    <input type="hidden" value="<?= $taxdetails['handle']; ?>" name="taxformat" id="tax_format">

                    <input type="hidden" value="<?= $taxdetails['format']; ?>" name="tax_handle" id="tax_status">
                    <input type="hidden" value="yes" name="applyDiscount" id="discount_handle">

                    <input type="hidden" value="<?= $this->common->disc_status()['disc_format']; ?>"
                           name="discountFormat" id="discount_format">
                    <input type="hidden" value="<?= amountFormat_general($this->common->disc_status()['ship_rate']); ?>"
                           name="shipRate"
                           id="ship_rate">
                    <input type="hidden" value="<?= $this->common->disc_status()['ship_tax']; ?>" name="ship_taxtype"
                           id="ship_taxtype">
                    <input type="hidden" value="0" name="ship_tax" id="ship_tax">


                </form>
            </div>

        </div>
    </div>
</div>

<script>
function changeFunction() {
    let ind = $(this).data("index");
    let qty = parseFloat($('[name="product_qty[]"][data-index="'+ind+'"]').val()).toFixed(2);
    let vat = parseFloat($('[name="product_tax[]"][data-index="'+ind+'"]').val()).toFixed(2);
    let price = parseFloat($('[name="product_price[]"][data-index="'+ind+'"]').val()).toFixed(2);
    let total_vat = qty*vat;

    let amount_vat = (qty*price);

    $("#texttaxa-"+ind).text(total_vat);

    $("#result-"+ind).text(parseFloat(total_vat + amount_vat).toFixed(2));
    var sum = 0;
    $(".vat-inc-amount").each(function(){
        sum += +parseFloat($(this).text()).toFixed(2);
    });
    $("#taxr").text(sum);

    var sum = 0;
    $(".ttlText").each(function(){
        sum += +parseFloat($(this).text()).toFixed(2);
    });
    $("#invoiceyoghtml").val(sum);

    
    $(".vat").each(function(){
        let val = parseFloat($(this).val()).toFixed(2);
        $(this).val(val);
    });
    
}
$(document).ready(function() {
    $('body').on("keyup change", '[name="product_name[]"]', changeFunction)
    $('body').on("click", '.ui-menu-item', changeFunction)
    $('body').on("keyup change", '.inputs', changeFunction)
    $('body').on("keyup change", ".req.amnt", changeFunction)
    $('body').on("keyup change", ".vat", changeFunction)
})
</script>


<style type="text/css">
.ui-menu-item .ui-menu-item-wrapper.ui-state-active {
    background: #6693bc !important;
    font-weight: bold !important;
    color: #ffffff !important;
} 

.ui-autocomplete{
    z-index:9999;
}

.content-wrapper {

z-index: 0;
}


</style>