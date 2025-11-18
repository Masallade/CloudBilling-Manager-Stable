  <style>
    .hidden {
      display: none;
    }
  </style>


<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">New Credit Note</h4>
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
                        <div class="col-sm-4 cmp-pnl" style="margin-top:-20px">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12">
                                        <h3 class="title ml-1">
                                            <?php echo $this->lang->line('Bill To') ?> <!--a href='#'
                                                                                          class="btn btn-primary btn-sm "
                                                                                          data-toggle="modal"
                                                                                          data-target="#addCustomer">
                                                <?php echo $this->lang->line('Add Client') ?>
                                            </a -->
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="frmSearch col-sm-12">
                                       
                                    </div>
                                </div>
                                <div id="customer">
                                    <div class="clientinfo"  style="margin-top:-35px">
                                        <?php //echo $this->lang->line('Client Details'); ?>
                                        <hr>
                                        <input type="hidden" name="customer_id" id="customer_id" value="0">
                                      <input type="text" name="customer_name" class="form-control" id="customer_name"><br>

                                        <textarea name="customer_address1" class="form-control" id="customer_address1" rows="3" cols="21"></textarea> 
                                    </div>

                                    <div class="clientinfo">

                                        <div id="customer_phone"></div>
                                    </div>
                                    <br>
                                  <!--  <input type="checkbox" id="pamt_terms" name="pamt_terms" value="1">
  <label for="pamt_terms"> Include Payment Terms and Conditions</label>-->
                                    <hr>
                                    <div id="customer_pass"></div>
									
									<div style="display:none;" > <?php echo $this->lang->line('Warehouse') ?>  </div>
									
									<select style="display:none;"
                                            id="s_warehouses"
                                            class="form-control round">
                                        <?php echo $this->common->default_warehouse();
                                        echo '<option value="0">' . $this->lang->line('All') ?></option><?php foreach ($warehouse as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                        } ?>

                                    </select>
                                                     <!--     <p style="color: red;font-weight: bold;">Maximum Credit Amount: <input type="text"  id="inv_blnc_can_credit2" value="" disabled/> </p>-->

                                    <input type="hidden" name="customer_postcode" id="customer_postcode" value="" />

                                    <input type="hidden" name="customer_inv_to_credit" id="customer_inv_to_credit" value="" />

                                     

									
                                </div>


                            </div>
                        </div>
						
						<div class="col-sm-3 cmp-pnl">
                            <table class="table-responsive tfr">
                                <tbody>
                                    <tr class="sub_c px-1" style="display: table-row;">
                                        <td class="reverse_align"><strong>Total VAT</strong>
                                        </td>
                                        <td align="center"><span
                                                    class="currenty lightMode"><?= $this->config->item('currency'); ?></span>
                                            <span id="taxr" class="lightMode">0</span></td>
                                    </tr>
                                    <tr class="sub_c px-1" style="display: table-row;">
                                        <td class="reverse_align">
                                            <strong><?php echo $this->lang->line('Total Discount') ?></strong></td>
                                        <td align="center"><span
                                                    class="currenty lightMode"><?php echo $this->config->item('currency');
                                                if (isset($_GET['project'])) {
                                                    echo '<input type="hidden" value="' . intval($_GET['project']) . '" name="prjid">';
                                                } ?></span>
                                            <span id="discs" class="lightMode">0</span></td>
                                    </tr>
                                    <tr>
                                        <td class="reverse_align px-1"><strong><?php echo $this->lang->line('Grand Total') ?>
                                                (<span
                                                        class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)</strong>
                                        </td>
                                        <td align="left"><input type="text" name="total" class="form-control"
                                                                    id="invoiceyoghtml" style=" text-align: center;" readonly="">
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-sm-5 cmp-pnl" style="margin-top:-35px">
                            <div class="inner-cmp-pnl">


                                <div class="form-group row"  style="margin-bottom:0px !important;" >
                                  
                                    <div class="col-sm-6"><label for="cst"
                                                                            class="caption">Search Invoice</label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                                 aria-hidden="true"></span></div>
                                          
												   <input type="text" class="form-control " name="cst" id="customer-box-credit-note"
                                               placeholder="Enter Inv# to search" autofocus
                                               />
											   
                                        </div>
										
                                    </div>
									 <div id="customer-box-result"></div>
									  <div class="col-sm-6"><label for="invocieno"
                                                                 class="caption"><?php echo $this->lang->line('Invoice Number') ?></label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="text" class="form-control " readonly placeholder="Invoice #"
                                                   name="invocieno"
                                                   value="<?php echo $lastinvoice + 1 ?>">
                                        </div>
                                    </div>
									
                                </div>
                                <div class="form-group row" style="margin-bottom:0px !important;">

                                    <div class="col-sm-6"><label for="invociedate"
                                                                 class="caption"><?php echo $this->lang->line('Invoice Date'); ?></label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="date" class="form-control required"
                                                   placeholder="Billing Date" name="invoicedate"
                                                   autocomplete="false">
                                        </div>
                                    </div>
                                  <!---  <div class="col-sm-6"><label for="invocieduedate"
                                                                 class="caption"><?php echo $this->lang->line('Invoice Due Date') ?></label>

                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar-o"
                                                                                 aria-hidden="true"></span></div>
                                            <input type="date" class="form-control required"
                                                   name="invocieduedate"
                                                   placeholder="Due Date" autocomplete="false">
                                        </div>
                                    </div> !-->
                                    <!-- <div class="col-sm-6">
                                         <label for="invoiceType"
                                               class="caption"> Invoice Type</label><br>
                                       <input type="radio" id="invoice_t" name="invoiceType"  value="INVOICE" checked>
                                         <label for="invoice_t"> INVOICE</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="radio" id="invoice_t2" name="invoiceType" value="DAYPASS">
                                                <label for="invoice_t2"> DAYPASS</label>
                            
                                    </div>-->
                                </div>

                                



















								
								 <div class="form-group row" style="margin-bottom:8px !important;" id="buttonContainer">
                                  <!---  <div class="col-sm-6">
                                        <label for="invoiceType"
                                               class="caption"> Invoice Type</label>
                                        <select class="form-control"                                            
                                                id="invoiceType"name="invoiceType">
											<option value="INVOICE" selected> Invoice </option>
											<option value="DAYPASS"> DAY PASS </option>
                                        </select>
                                    </div> !-->
                                    <div class="col-sm-12" style="margin-top:10px;">
									
                                        <input type="submit" 
                                            class="btn btn-success sub-btn" 
                                            value="Save"
                                            id="submit-data-noprint" data-loading-text="Creating...">

									   <!--  <a  class="btn btn-warning" id="customerProducts" href=" " onclick="myFunction()" target="_blank">Customer Products</a>       -->                                                               
                                                                                                                                        
                                       <input type="submit" 
                                            class="btn btn-success sub-btn" 
                                            value="Save & Print"
                                            id="submit-data" data-loading-text="Creating...">

                                                                    
                                       
                                    </div> </div>
									

                                <div class="form-group row"  style="display:none;" >
                                    <div class="col-sm-6">
                                        <label for="taxformat"
                                               class="caption"><?php echo $this->lang->line('Tax') ?></label>
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
                                            <select class="form-control round"
                                                    onchange="changeDiscountFormat(this.value)"
                                                    id="discountFormat">

                                                <?php echo $this->common->disclist() ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row" style="display:none;" >
                                    <div class="col-sm-12">
                                        <label for="toAddInfo"
                                               class="caption"><?php echo $this->lang->line('Invoice Note') ?></label>
                                        <textarea class="form-control round" name="notes" rows="2"></textarea></div>
                                </div>

                            </div>
                        </div>

                    </div>


                    <div id="saman-row">
                        <table class="table-responsive tfr">
  
                            <thead>
                           <tr class="item_header bg-gradient-directional-blue white">
                                <th width="16%" class="text-center"> Product Code</th> 
								<th width="20%" class="text-center"> Product Description</th>
                                <th width="8%" class="text-center"><?php echo $this->lang->line('Quantity') ?></th>
                                <th width="5%" class="text-center">Price <?php //echo $this->lang->line('Rate') ?></th>
                                <th width="10%" class="text-center">Special Notes</th>
                                <th width="5%" class="text-center">VAT</th>
                                <th width="5%" class="text-center">Net VAT <?php //echo $this->lang->line('Tax') ?></th>
                                <th width="5%" class="text-center"><?php echo $this->lang->line('Discount') ?></th>
                                <th width="10%" class="text-center">
                                    Net <?php //echo $this->lang->line('Amount') ?>
                                    (<?= currency($this->aauth->get_user()->loc); ?>)
                                </th>
                                <th width="5%" class="text-center"><?php echo $this->lang->line('Action') ?></th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php for($i =0; $i<60; $i++){ ?>
							<tr>
                                <td><input type="text" class="form-control inputs" name="product_name[]"
                                           placeholder="<?php echo 'Enter Product Code'; ?>"
                                           id='productname-<?= $i; ?>'>
                                </td>
								
								 <td ><input type="text" id="dpid-<?= $i; ?>" class="form-control" name="product_description[]"
                                                          placeholder="<?php echo $this->lang->line('Enter Product description'); ?> (Optional)"
                                                          autocomplete="off" /></td>
														  
                                <td><input type="text" class="form-control req inputs amnt" name="product_qty[]" value="1" id="amount-<?= $i; ?>"
                                           onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog()"
                                           autocomplete="off" value=""><input type="hidden" id="alert-<?= $i; ?>" value=""
                                                                               name="alert[]"></td>
                                <td><input type="text" class="form-control req inputs prc" name="product_price[]" id="price-<?= $i; ?>"
                                           onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>'), billUpyog()"
                                           autocomplete="off"></td>
								<td class="text-center">  <input type="checkbox" id="SI-<?= $i; ?>" value="0" name="SI[<?= $i; ?>]">  </td>

                                <td>
                                <input type="text" class="form-control vat " name="product_tax[]" id="vat-<?= $i; ?>"
                                           onkeypress="return isNumber(event)" onkeyup="rowTotal('<?= $i; ?>', false), billUpyog()"
                                           autocomplete="off">
                                           </td>
                                           

                                <td class="text-center" id="texttaxa-<?= $i; ?>">0</td> 
                                
                                
                                <td><input type="text" class="form-control discount" name="product_discount[]"
                                           onkeypress="return isNumber(event)" id="discount-<?= $i; ?>"
                                           onkeyup="rowTotal('<?= $i; ?>'), billUpyog()" autocomplete="off"></td>
                                <td class="text-center"><span class="currenty"><?= currency($this->aauth->get_user()->loc); ?></span>
                                    <strong><span class='ttlText' id="result-<?= $i; ?>">0</span></strong></td>
                                <td class="text-center"><button type="button" data-rowid="<?= $i;?>" class=" btn-danger removeProd" style="margin-bottom: 2px;" title="Remove"> <i class="fa fa-minus-square"></i> </button> </td>
                                <input type="hidden" name="taxa[]" id="taxa-<?= $i; ?>" value="0">
                                <input type="hidden" name="disca[]" id="disca-<?= $i; ?>" value="0">
                                <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-<?= $i; ?>" value="0">
                                <input type="hidden" class="pdIn" name="pid[]" id="pid-<?= $i; ?>" value="0">
                                <input type="hidden" name="unit[]" id="unit-<?= $i; ?>" value="">
								<input type="hidden" name="vattype[]" id="vattype-<?= $i; ?>" value="">
                                <input type="hidden" name="hsn[]" id="hsn-<?= $i; ?>" value="">
                                <input type="hidden" name="serial[]" id="serial-<?= $i; ?>" value="">
                            </tr>
							<?php } ?>

                            <tr class="last-item-row sub_c">
                                <td class="add-row">
                                    <button type="button" class="btn btn-success" aria-label="Left Align"
                                            id="addproduct">
                                        <i class="fa fa-plus-square"></i> <?php echo $this->lang->line('Add Row') ?>
                                    </button>
                                </td>
                                <td colspan="7"></td>
                            </tr>

                            <tr class="sub_c" style="display: table-row; visibility: hidden;">
                                <td colspan="7" class="reverse_align"><input type="hidden" value="0" id="subttlform"
                                                                     name="subtotal"><strong>Total VAT</strong>
                                </td>
                                <td align="center" colspan="2"><span
                                            class="currenty lightMode"><?= $this->config->item('currency'); ?></span>
                                    <span id="taxr" class="lightMode">0</span></td>
                            </tr>
                            <tr class="sub_c" style="display: table-row; visibility: hidden;" >
                                <td colspan="7" class="reverse_align">
                                    <strong><?php echo $this->lang->line('Total Discount') ?></strong></td>
                                <td align="center" colspan="2"><span
                                            class="currenty lightMode"><?php echo $this->config->item('currency');
                                        if (isset($_GET['project'])) {
                                            echo '<input type="hidden" value="' . intval($_GET['project']) . '" name="prjid">';
                                        } ?></span>
                                    <span id="discs" class="lightMode">0</span></td>
                            </tr>

                            <tr class="sub_c" style="display: none;">
                                <td colspan="7" class="reverse_align">
                                    <strong><?php echo $this->lang->line('Shipping') ?></strong></td>
                                <td align="center" colspan="2"><input type="text" class="form-control shipVal"
                                                                    onkeypress="return isNumber(event)"
                                                                    placeholder="Value"
                                                                    name="shipping" autocomplete="off"
                                                                    onkeyup="billUpyog()">
                                    ( <?php echo $this->lang->line('Tax') ?> <?= $this->config->item('currency'); ?>
                                    <span id="ship_final">0</span> )
                                </td>
                            </tr>
                            <tr class="sub_c" style="display: none;">
                                <td colspan="7" class="reverse_align">
                                    <strong> <?php echo $this->lang->line('Extra') . ' ' . $this->lang->line('Discount') ?></strong>
                                </td>
                                <td align="center" colspan="2"><input type="text"
                                                                    class="form-control form-control-sm discVal"
                                                                    onkeypress="return isNumber(event)"
                                                                    placeholder="Value"
                                                                    name="disc_val" autocomplete="off" value="0"
                                                                    onkeyup="billUpyog()">
                                    <input type="hidden"
                                           name="after_disc" id="after_disc" value="0">
                                    ( <?= $this->config->item('currency'); ?>
                                    <span id="disc_final">0</span> )
                                </td>
                            </tr>


                            <tr class="sub_c" style="display: table-row;">
                                <td colspan="2"><?php if (isset($employee)){
                                       echo $this->lang->line('Employee')
                                ?><br>
                                    <select name="employee"
                                            class=" mt-1 col form-control form-control-sm">

                                        <?php foreach ($employee as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . $row['name'] . ')</option>';
                                        } ?>

                                    </select><?php } ?><br><?php if ($exchange['active'] == 1){
                                    echo $this->lang->line('Payment Currency client') . ' <small>' . $this->lang->line('based on live market') ?></small>
                                    <select name="mcurrency"
                                            class="selectpicker form-control">
                                        <option value="0">Default</option>
                                        <?php foreach ($currency as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['symbol'] . ' (' . $row['code'] . ')</option>';
                                        } ?>

                                    </select><?php } ?></td>
                                <td colspan="5" class="reverse_align" style="visibility: hidden;"><strong><?php echo $this->lang->line('Grand Total') ?>
                                        (<span
                                                class="currenty lightMode"><?php echo $this->config->item('currency'); ?></span>)</strong>
                                </td>
                                <!--<td align="left" colspan="2" style="visibility: hidden;"><input type="text" name="total" class="form-control"
                                                                    id="invoiceyoghtml" style=" text-align: center;" readonly="">-->

                                </td>
                            </tr>
                            <tr class="sub_c" style="display: table-row;">
                                <td colspan="2" style="display: none;"><?php echo $this->lang->line('Payment Terms') ?> <select name="pterms"
                                                                                                         class="selectpicker form-control"><?php foreach ($terms as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                                        } ?>

                                    </select></td>
                           <!--     <td class="reverse_align" colspan="9" ><input type="submit"
                                                                     class="btn btn-success sub-btn btn-lg" style="margin-top:5px;"
                                                                     value="<?php echo $this->lang->line('Generate Invoice') ?> "
                                                                     id="submit-data" data-loading-text="Creating...">

                                </td> -->
                            </tr>


                            </tbody>
                        </table>

                        <?php
                        if(is_array($custom_fields)){
                          echo'<div class="card">';
                                    foreach ($custom_fields as $row) {
                                        if ($row['f_type'] == 'text') { ?>
                                            <div class="row mt-1">

                                                <label class="col-sm-8"
                                                       for="docid"><?= $row['name'] ?></label>

                                                <div class="col-sm-6">
                                                    <input type="text" placeholder="<?= $row['placeholder'] ?>"
                                                           class="form-control margin-bottom b_input <?= $row['other'] ?>"
                                                           name="custom[<?= $row['id'] ?>]">
                                                </div>
                                            </div>


                                        <?php }
                                    }
                                    echo'</div>';
                        }
                                    ?>
                    </div>
                    <input type="hidden" value="new_i" id="inv_page">
                    <input type="hidden" value="stockreturn/action2" id="action-url">
                    <input type="hidden" value="search" id="billtype">
                    <input type="hidden" value="59" name="counter" id="ganak">
                    <input type="hidden" value="<?= currency($this->aauth->get_user()->loc); ?>" name="currency">
                    <input type="hidden" value="<?= $taxdetails['handle']; ?>" name="taxformat" id="tax_format">
                    <input type="hidden" value="<?= $taxdetails['format']; ?>" name="tax_handle" id="tax_status">
                      <input type="hidden" name="inv_blnc_can_credit" id="inv_blnc_can_credit" value="" /> </p>

                    <input type="hidden" value="yes" name="applyDiscount" id="discount_handle">
                    <input type="hidden" value="<?= $this->common->disc_status()['disc_format']; ?>"
                           name="discountFormat" id="discount_format">
                    <input type="hidden" value="<?= amountFormat_general($this->common->disc_status()['ship_rate']); ?>"
                           name="shipRate"
                           id="ship_rate">
                    <input type="hidden" value="<?= $this->common->disc_status()['ship_tax']; ?>" name="ship_taxtype"
                           id="ship_taxtype">
                    <input type="hidden" value="0" name="ship_tax" id="ship_tax">
                    <input type="hidden" value="0" id="custom_discount">

                </form>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="addCustomer" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content ">
            <form method="post" id="product_action" class="form-horizontal">
                <!-- Modal Header -->
                <div class="modal-header bg-gradient-directional-purple white">

                    <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('Add Customer') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only"><?php echo $this->lang->line('Close') ?></span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <p id="statusMsg"></p><input type="hidden" name="mcustomer_id" id="mcustomer_id" value="0">
                    <div class="row">
                        <div class="col-sm-6">
                            <h5><?php echo $this->lang->line('Billing Address') ?></h5>
                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="name"><?php echo $this->lang->line('Name') ?></label>

                                <div class="col-sm-10">
                                    <input type="text" placeholder="Name"
                                           class="form-control margin-bottom" id="mcustomer_name" name="name" required>
                                </div>
                            </div>

                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="phone"><?php echo $this->lang->line('Phone') ?></label>

                                <div class="col-sm-10">
                                    <input type="text" placeholder="Phone"
                                           class="form-control margin-bottom" name="phone" id="mcustomer_phone">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label"
                                       for="email"><?php echo $this->lang->line('Email') ?></label>

                                <div class="col-sm-10">
                                    <input type="email" placeholder="Email"
                                           class="form-control margin-bottom crequired" name="email"
                                           id="mcustomer_email">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="address"><?php echo $this->lang->line('Address') ?></label>

                                <div class="col-sm-10">
                                    <input type="text" placeholder="Address"
                                           class="form-control margin-bottom " name="address" id="mcustomer_address1">
                                </div>
                            </div>
                            <div class="form-group row">


                                <div class="col-sm-6">
                                    <input type="text" placeholder="City"
                                           class="form-control margin-bottom" name="city" id="mcustomer_city">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="Region" id="region"
                                           class="form-control margin-bottom" name="region">
                                </div>

                            </div>

                            <div class="form-group row">


                                <div class="col-sm-6">
                                    <input type="text" placeholder="Country"
                                           class="form-control margin-bottom" name="country" id="mcustomer_country">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="PostBox" id="postbox"
                                           class="form-control margin-bottom" name="postbox">
                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-6">
                                    <input type="text" placeholder="Company"
                                           class="form-control margin-bottom" name="company">
                                </div>

                                <div class="col-sm-6">
                                    <input type="text" placeholder="TAX ID"
                                           class="form-control margin-bottom" name="taxid" id="mcustomer_city">
                                </div>


                            </div>

                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label  col-form-label-sm"
                                       for="customergroup"><?php echo $this->lang->line('Group') ?></label>

                                <div class="col-sm-10">
                                    <select name="customergroup" class="form-control form-control-sm">
                                        <?php
                                        foreach ($customergrouplist as $row) {
                                            $cid = $row['id'];
                                            $title = $row['title'];
                                            echo "<option value='$cid'>$title</option>";
                                        }
                                        ?>
                                    </select>


                                </div>
                            </div>


                        </div>

                        <!-- shipping -->
                        <div class="col-sm-6">
                            <h5><?php echo $this->lang->line('Shipping Address') ?></h5>
                            <div class="form-group row">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="customer1s"
                                           id="copy_address">
                                    <label class="custom-control-label"
                                           for="copy_address"><?php echo $this->lang->line('Same As Billing') ?></label>
                                </div>


                                <div class="col-sm-10">
                                    <?php echo $this->lang->line("leave Shipping Address") ?>
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="name_s"><?php echo $this->lang->line('Name') ?></label>

                                <div class="col-sm-10">
                                    <input type="text" placeholder="Name"
                                           class="form-control margin-bottom" id="mcustomer_name_s" name="name_s"
                                           required>
                                </div>
                            </div>

                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="phone_s"><?php echo $this->lang->line('Phone') ?></label>

                                <div class="col-sm-10">
                                    <input type="text" placeholder="Phone"
                                           class="form-control margin-bottom" name="phone_s" id="mcustomer_phone_s">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="email_s"><?php echo $this->lang->line('Email') ?></label>

                                <div class="col-sm-10">
                                    <input type="email" placeholder="Email"
                                           class="form-control margin-bottom" name="email_s"
                                           id="mcustomer_email_s">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                       for="address_s"><?php echo $this->lang->line('Address') ?></label>

                                <div class="col-sm-10">
                                    <input type="text" placeholder="Address"
                                           class="form-control margin-bottom " name="address_s"
                                           id="mcustomer_address1_s">
                                </div>
                            </div>
                            <div class="form-group row">


                                <div class="col-sm-6">
                                    <input type="text" placeholder="City"
                                           class="form-control margin-bottom" name="city_s" id="mcustomer_city_s">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="Region" id="region_s"
                                           class="form-control margin-bottom" name="region_s">
                                </div>

                            </div>

                            <div class="form-group row">


                                <div class="col-sm-6">
                                    <input type="text" placeholder="Country"
                                           class="form-control margin-bottom" name="country_s" id="mcustomer_country_s">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="PostBox" id="postbox_s"
                                           class="form-control margin-bottom" name="postbox_s">
                                </div>
                            </div>


                        </div>

                    </div>
                                   <?php
                                   if(is_array($custom_fields_c)){
                                    foreach ($custom_fields_c as $row) {
                                        if ($row['f_type'] == 'text') { ?>
                                            <div class="form-group row">

                                                <label class="col-sm-2 col-form-label"
                                                       for="docid"><?= $row['name'] ?></label>

                                                <div class="col-sm-8">
                                                    <input type="text" placeholder="<?= $row['placeholder'] ?>"
                                                           class="form-control margin-bottom b_input"
                                                           name="custom[<?= $row['id'] ?>]">
                                                </div>
                                            </div>


                                        <?php }
                                    }
                                   }
                                    ?>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                    <input type="submit" id="mclient_add" class="btn btn-primary submitBtn" value="ADD"/>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">



$(document).ready(function() {


$('.inputs').keydown(function (e) {
     if (e.which === 13) {
         var index = $('.inputs').index(this) + 1;
         $('.inputs').eq(index).focus().select();
        $('html, body').animate({
    scrollTop: $(window).scrollTop() + 10
});
      event.preventDefault();
      return false;
     }
 });

$('#inv_blnc_can_credit2').val($('#inv_blnc_can_credit').val());

//    toggleButtonsVisibility();

//       // Monitor changes in the 'total' input
//       $('#invoiceyoghtml').on('input', function () {
//           alert('hello');
//         toggleButtonsVisibility();
//       });

//           function toggleButtonsVisibility() {
//         var totalValue = parseFloat($('#invoiceyoghtml').val()) || 0;
//         var invBlncValue = parseFloat($('#inv_blnc_can_credit').val()) || 0;

//         if (totalValue > invBlncValue) {
//           $('#buttonContainer').addClass('hidden');
//         } else {
//           $('#buttonContainer').removeClass('hidden');
//         }
//       }
});

 


function myFunction() {
 
let newUrl = baseurl +"customers/list_price/"+$('#customer_id').val();

  $('#customerProducts').attr("href", newUrl);
  }
focusMethod = function getFocus() {
  document.getElementById("customer-box").focus();
}


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