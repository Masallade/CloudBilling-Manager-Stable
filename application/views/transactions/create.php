<style>
    .bg-blue.bg-lighten-4 {
        background-color: #def0ff !important;
    }
</style>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4><?php echo $this->lang->line('Add New Transaction') ?></h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <form method="post" id="data_form">
                    <div class="row mb-1 ml-1">
                        <div class="col-md-2 display-inline">
                            <div class="  custom-radio">
                                <input type="radio" class="custom-control-input" name="ty_p" id="customRadio1" value="0" checked="">
                                <label class="custom-control-label" for="customRadio1"><?php echo $this->lang->line('Customer') ?> &nbsp;</label>
                            </div>
                            <div class="custom-radio">
                                <input type="radio" class="custom-control-input" name="ty_p" id="customRadio2" value="1">
                                <label class="custom-control-label" for="customRadio2"><?php echo $this->lang->line('Supplier') ?></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="cst" id="customer-box" placeholder="Enter Customer Name or Mobile Number to search (Optional)" autocomplete="off" />
                            <input type="text" autofocus="" class="form-control ui-autocomplete-input" name="cst" id="supplier-name" placeholder="Enter Supplier Name or Mobile Number to search" autocomplete="off">
                        </div>
                        <!--div id="trans-box-result" class="sbox-result" -->

                    </div>


                    <hr>
                    <div id="customerpanel" class="form-group row bg-blue bg-lighten-4 pb-1 mb-0">

                        <div class="col-sm-4"><label for="toBizName" class="caption col-form-label"><?php echo $this->lang->line('C/o') ?>
                                <span style="color: red;">*</span></label><input type="hidden" name="payer_id" id="customer_id" value="0">
                            <input type="text" class="form-control required" name="payer_name" id="customer_name">
                        </div>


                        <div class="col-sm-4"><label class=" col-form-label" for="pay_cat"><?php echo $this->lang->line('To') . ' ' . $this->lang->line('Account') ?></label>
                            <select name="pay_acc" class="form-control">
                                <?php
                                foreach ($accounts as $row) {
                                    $cid = $row['id'];
                                    $acn = $row['acn'];
                                    $holder = $row['holder'];
                                    echo "<option value='$cid'>$acn - $holder</option>";
                                }
                                ?>
                            </select>


                        </div>


                        <input type="hidden" name="act" value="add_product">


                        <div class="col-sm-4"><label class="col-form-label" for="date"><?php echo $this->lang->line('Date') ?></label>
                            <input type="date" class="form-control required" name="date" autocomplete="false">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-sm-4"><label class="col-form-label" for="amount"><?php echo $this->lang->line('Total') ?> <?php echo $this->lang->line('Amount') ?> (Inclusive of VAT)</label>
                            <input type="text" placeholder="Amount" class="form-control margin-bottom  required" name="amount" value="0" onkeypress="return isNumber(event)">
                        </div>

                        <div class="col-sm-4"><label class="col-form-label" for="vat"><?php echo $this->lang->line('VAT') ?></label>
                            <input type="text" placeholder="VAT" class="form-control margin-bottom " name="vat" value="0">
                        </div>

                        <div class="col-sm-4"><label class="col-form-label" for="product_price"><?php echo $this->lang->line('Type') ?></label>

                            <select name="pay_type" class="form-control">
                                <option value="Expense"><?php echo $this->lang->line('Expense') . ' / ' . $this->lang->line('Debit') ?></option>
                                <option value="Income"><?php echo $this->lang->line('Income') . ' / ' . $this->lang->line('Credit') ?></option>


                            </select>


                        </div>


                        <div class="col-sm-4" style="display:none"><label class="col-form-label" for="pay_cat"><?php echo $this->lang->line('Category') ?></label>
                            <!--<select name="pay_cat" class="form-control">
                                <?php
                                foreach ($cat as $row) {
                                    $cid = $row['id'];
                                    $title = $row['name'];
                                    echo "<option value='$title'>$title</option>";
                                }
                                ?>
                            </select>-->
                            <input type="hidden" name="pay_cat" value="Sales" autocomplete="false">


                        </div>


                    </div>
                    <div class="form-group row bg-blue bg-lighten-4 pb-1">

                        <div class="col-sm-4"><label class="col-form-label" for="product_price"><?php echo $this->lang->line('Method') ?> </label>

                            <select name="paymethod" class="form-control">
                                <option value="Cash" selected><?php echo $this->lang->line('Cash') ?></option>
                                <option value="Card"><?php echo $this->lang->line('Card') ?></option>
                                <option value="Cheque"><?php echo $this->lang->line('Cheque') ?></option>
                                <option value="Bank"><?php echo $this->lang->line('Bank') ?></option>
                                <option value="Other"><?php echo $this->lang->line('Other') ?></option>
                            </select>


                        </div>


                        <div class="col-sm-8"><label class="col-form-label"><?php echo $this->lang->line('Note') ?></label>
                            <input type="text" placeholder="Note" class="form-control" name="note">
                        </div>
                    </div>
                    <!---- Dual -->
                    <?php if ($dual['key1']) { ?>
                        <hr>
                        <h4 class="purple"><?php echo $this->lang->line('Dual Entry') ?></h4>
                        <div id="customerpanel" class="form-group row bg-purple bg-lighten-4 pb-1">


                            <div class="col-sm-4"><label class=" col-form-label" for="f_pay_cat"><?php echo $this->lang->line('From') . ' ' . $this->lang->line('Account') ?></label>
                                <select name="f_pay_acc" class="form-control">
                                    <?php
                                    foreach ($accounts as $row) {
                                        $cid = $row['id'];
                                        $acn = $row['acn'];
                                        $holder = $row['holder'];
                                        echo "<option value='$cid'>$acn - $holder</option>";
                                    }
                                    ?>
                                </select>


                            </div>


                            <div class="col-sm-4"><label class="col-form-label" for="f_pay_cat"><?php echo $this->lang->line('From') . ' ' . $this->lang->line('Category') ?></label>
                                <select name="f_pay_cat" class="form-control">
                                    <?php
                                    foreach ($cat as $row) {
                                        $cid = $row['id'];
                                        $title = $row['name'];
                                        echo "<option value='$title'>$title</option>";
                                    }
                                    ?>
                                </select>


                            </div>


                            <div class="col-sm-4"><label class="col-form-label" for="f_paymethod"><?php echo $this->lang->line('From') . ' ' . $this->lang->line('Method') ?> </label>

                                <select name="f_paymethod" class="form-control">
                                    <option value="Cash" selected><?php echo $this->lang->line('Cash') ?></option>
                                    <option value="Card"><?php echo $this->lang->line('Card') ?></option>
                                    <option value="Cheque"><?php echo $this->lang->line('Cheque') ?></option>
                                    <option value="Bank"><?php echo $this->lang->line('Bank') ?></option>
                                    <option value="Other"><?php echo $this->lang->line('Other') ?></option>
                                </select>


                            </div>


                        </div>
                        <div class="form-group row  bg-lighten-4 pb-1">

                            <div class="col-sm-8"><label class="col-form-label"><?php echo $this->lang->line('From') . ' ' . $this->lang->line('Note') ?></label>
                                <input type="text" placeholder="Note" class="form-control" name="f_note">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="form-group row">


                        <div class="col-sm-4">
                            <input type="submit" id="submit-data" class="btn btn-success btn-lg margin-bottom" value="<?php echo $this->lang->line('Add transaction') ?>" data-loading-text="Adding...">
                            <input type="hidden" value="transactions/new_trans" id="action-url">
                        </div>
                    </div>


                </form>
            </div>
        </div>
        <script type="text/javascript">
            $("#trans-box").keyup(function() {
                $.ajax({
                    type: "GET",
                    url: baseurl + 'search_products/party_search',
                    data: 'keyword=' + $(this).val() + '&ty=' + $('input[name=ty_p]:checked').val(),
                    beforeSend: function() {
                        $("#trans-box").css("background", "#FFF url(" + baseurl + "assets/custom/load-ring.gif) no-repeat 165px");
                    },
                    success: function(data) {
                        $("#trans-box-result").show();
                        $("#trans-box-result").html(data);
                        $("#trans-box").css("background", "none");

                    }
                });
            });
            $(document).ready(function() {
                // Initial setup based on the default selected radio button
                updateVisibility();

                // Bind change event to the radio buttons
                $('input[name="ty_p"]').change(function() {
                    updateVisibility();
                });

                function updateVisibility() {
                    var selectedValue = $('input[name="ty_p"]:checked').val();

                    if (selectedValue === '0') {
                        // If "Customer" is selected, show the customer input and hide the supplier input
                        $('#customer-box').show();
                        $('#supplier-name').hide();
                    } else if (selectedValue === '1') {
                        // If "Supplier" is selected, show the supplier input and hide the customer input
                        $('#customer-box').hide();
                        $('#supplier-name').show();
                    }
                }
            });
        </script>