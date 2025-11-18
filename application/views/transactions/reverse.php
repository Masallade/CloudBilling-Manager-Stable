<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4>Add Reverse Transaction</h4>
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
                                <label class="custom-control-label" for="customRadio1">Reference Number &nbsp;</label>
                            </div>
                            <div class="custom-radio" style="display:none;">
                                <input type="radio" class="custom-control-input" name="ty_p" id="customRadio2" value="1">
                                <label class="custom-control-label" for="customRadio2"><?php echo $this->lang->line('Supplier') ?></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="trans_ref" id="inv-id-search-box" placeholder="Enter transaction reference" autocomplete="off" />
                        </div>
                        <div id="trans-box-result" class="sbox-result"></div>

                        <!--div id="trans-box-result" class="sbox-result" -->

                    </div>


                    <hr>
                    <div id="customerpanel" class="form-group row bg-blue bg-lighten-4 pb-1">

                        <div class="col-sm-4">
                            <label for="toBizName" class="caption col-form-label"><?php echo $this->lang->line('C/o') ?>
                                <span style="color: red;">*</span>
                            </label>
                            <input type="hidden" name="payer_id" id="payer_id" value="0">
                            <input type="hidden" name="eid" id="eid" value="0">
                            <input type="hidden" name="tid" id="tid" value="0">
                            <input type="hidden" name="created_by" id="created_by" value="0">
                            <input type="hidden" name="supplier" id="supplier" value="0">
                            <input type="hidden" name="is_advance_payment" id="is_advance_payment" value="0">
                            <input type="text" class="form-control required" name="payer" id="customer_name">
                        </div>


                        <div class="col-sm-4" style="display:none"><label class=" col-form-label" for="pay_cat"><?php echo $this->lang->line('To') . ' ' . $this->lang->line('Account') ?></label>
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


                        <div class="col-sm-4"><label class="col-form-label" for="amount"><?php echo $this->lang->line('Amount') ?></label>
                            <!-- <input type="text" placeholder="Amount" class="form-control margin-bottom  required" name="amount" value="0" onkeypress="return isNumber(event)"> -->
                            <input type="text" class="form-control required" name="amount" id="amount">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-sm-4"><label class="col-form-label" for="date"><?php echo $this->lang->line('Date') ?></label>
                            <input type="date" class="form-control required" name="date" autocomplete="false">
                        </div>


                        <!-- <div class="col-sm-4"><label class="col-form-label" for="product_price"><?php echo $this->lang->line('Type') ?></label>

                            <select name="pay_type" class="form-control">
                                <option value="Expense">Credit</option> -->
                                <!-- <option value="Income"
                                         selected><?php echo $this->lang->line('Income') . ' / ' . $this->lang->line('Credit') ?></option> -->


                            <!-- </select>


                        </div> -->


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
                            <input type="submit" id="submit-data" class="btn btn-success btn-lg margin-bottom" value="<?php echo $this->lang->line('Reverse Transaction') ?>" data-loading-text="Adding...">
                            <input type="hidden" value="transactions/revers_trans" id="action-url">
                        </div>
                    </div>


                </form>
                <div id="flash-message" style="display:none;" class="alert">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <div class="message"></div>
                </div>
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
        </script>
        <script>
            $(document).ready(function() {
                $('#inv-id-search-box').autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: '<?php echo base_url('transactions/search_transaction'); ?>',
                            method: 'GET',
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                response(JSON.parse(data));
                            }
                        });
                    },
                    minLength: 1,
                    select: function(event, ui) {
                        $('#inv-id-search-box').val(ui.item.value); // Set the input with the transaction reference
                        $('#customer_name').val(ui.item.payer); // Set the input with the payer name
                        $('#payer_id').val(ui.item.payer_id); // Set the hidden input with the payer_id
                        $('#eid').val(ui.item.eid); // Set the hidden input with the eid
                        $('#tid').val(ui.item.tid); // Set the hidden input with the tid
                        $('#loc').val(ui.item.loc); // Set the hidden input with the loc
                        $('#is_advance_payment').val(ui.item.is_advance_payment);
                        $('#supplier').val(ui.item.supplier);
                        $('#created_by').val(ui.item.created_by);
                        $('#amount').val(ui.item.amount); // Set the input with the amount
                        return false; // Prevent the default action
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "</div>")
                        .appendTo(ul);
                };
            });
            $(document).ready(function() {
                $('#data_form').submit(function(e) {
                    e.preventDefault(); // Prevent default form submission

                    var form = $(this);
                    var url = $('#action-url').val();

                    $.ajax({
                        type: "POST",
                        url: baseurl + url,
                        data: form.serialize(), // Serialize form data
                        dataType: "json",
                        success: function(response) {
                            var flashMessage = $('#flash-message');
                            var messageDiv = flashMessage.find('.message');

                            if (response.status === 'Success') {
                                flashMessage.removeClass('alert-danger').addClass('alert-success');
                            } else {
                                flashMessage.removeClass('alert-success').addClass('alert-danger');
                            }

                            messageDiv.text(response.message);
                            flashMessage.show();
                        },
                        error: function(xhr, status, error) {
                            var flashMessage = $('#flash-message');
                            var messageDiv = flashMessage.find('.message');

                            flashMessage.removeClass('alert-success').addClass('alert-danger');
                            messageDiv.text("An error occurred: " + error);
                            flashMessage.show();
                        }
                    });
                });
            });
        </script>
        <style>
            .alert {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }
        </style>