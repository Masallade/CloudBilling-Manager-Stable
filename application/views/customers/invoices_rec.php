<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">New Sale &nbsp; <a
                    href="<?php echo base_url('invoices/create') ?>"
                    class="btn btn-primary btn-sm rounded">
                    <?php echo $this->lang->line('Add new') ?></a></h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>

        <?php //var_dump($list); 
        ?>
        <div class="card-content">

            <?php if ($this->session->flashdata('success_message')) {
                $message = $this->session->flashdata('success_message');
            ?>
                <div class="alert alert-success">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <div class="message"> <?php echo $message; ?> </div>
                </div>

            <?php  } ?>

            <?php if ($this->session->flashdata('err_message')) {
                $message = $this->session->flashdata('err_message');
            ?>

                <div class="alert alert-danger">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>

                    <div class="message"> <?php echo $message; ?> </div>

                </div>

            <?php  } ?>



            <form method="post" id="recipt_form" action="<?php echo base_url(); ?>Transactions/payinvoice_rec">
                <div class="card-body">



                    <div class="form-group row">



                        <div class="col-sm-12">


                            <div class="input-group">
                                <div class="input-group-addon"><span class="icon-bookmark-o"
                                        aria-hidden="true"></span></div>
                                <div class="col-sm-4" style="margin:0 auto;">
                                    <label for="cst" class="caption"><b>Search Client For Receipt</b></label>
                                    <input type="text" class="form-control " name="cst" id="customer-rec-box"
                                        placeholder="Enter Customer Name or Phone Number to search"
                                        autocomplete="off" />
                                    <br>
                                    <div id="customer">

                                        <input type="hidden" name="customer_id" id="customer_id" value="0">
                                        <div id="customer_name"></div>



                                        <div class="clientinfo">

                                            <div id="customer_bal"></div>
                                        </div>

                                        <div id="customer_pass"></div>




                                    </div>
                                </div>



                            </div>

                            <div id="customer-box-result"></div>



                        </div>


                    </div>

                    <!-- div class="row">

                    <div class="col-md-2"><?php echo $this->lang->line('Invoice Date') ?></div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" id="start_date"
                               class="form-control form-control-sm" autocomplete="off"/>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                               autocomplete="off"/>
                    </div>

                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm"/>
                    </div>

                </div -->

                    <table id="invoices" class="table-bordered zero-configuration " style="width:100%;">
                        <thead>
                            <tr>
                                <th>Sr#</th>
                                <th> No. </th>
                                <th><?php echo 'A/C' ?></th>
                                <th>Type</th>
                                <th>Date</th>
                                <th><?php echo 'Due Date'; ?></th>
                                <th><?php echo $this->lang->line('Amount') ?></th>
                                <th> Remaining</th>
                                <th><?php echo 'Receipt(GBP)' ?></th>

                                <th><?php echo 'Pay In Full'; ?></th>
                            </tr>
                        </thead>
                        <?php if (!empty($list)) { ?>
                            <tbody id="InvoiceTbl">
                                <!--	<?php $k = 0;
                                        foreach ($list as $invoices): $remaining = 0;
                                            $no++;    ?>
           <tr><td><?php echo $no; ?></td>

           <td> <?php echo $invoices->tid; ?></td>
		   <td> <?php echo $invoices->name; ?></td>
           <td> <?php echo $invoices->inv_type; ?></td>
		   <td> <?php echo dateformat($invoices->invoicedate); ?> </td>
		   <td><?php echo  dateformat($invoices->invoiceduedate); ?></td>
		   <td><?php echo amountExchange($invoices->total, 0, $this->aauth->get_user()->loc); ?></td>
		   <td><?php $remaining = ($invoices->total - $invoices->pamnt);
                                            echo amountExchange($remaining, 0, $this->aauth->get_user()->loc); ?></td>
		   <td><input type="text" name="receipt_amount[]" onkeypress="return isNumber(event)" onkeyup="rowsTotal(<?php echo $k; ?>), billsUpyog(), updatePID(<?php echo $invoices->id; ?>,<?php echo $k; ?>)" onblur="ValidatePrice(this.value,<?php echo $invoices->id; ?>,<?php echo $k; ?>)" id="receipt_amount<?php echo $k; ?>"> </td>
		   <td><input type="checkbox" class="checkbox" onchange="doalert(this,'<?php echo $k; ?>'),rowsTotal(<?php echo $k; ?>), billsUpyog()" name="receipt" data-id="<?php echo $invoices->tid; ?>" value="<?php echo $remaining; ?>"  >
		   </td> 
		   </tr>
                               
                                <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-<?php echo $k; ?>" value="0">
                                <input type="hidden" class="pdIn" name="pid[]" id="pid-<?php echo $k; ?>" value="<?php echo $invoices->id; ?>"> 
                                <input type="hidden" name="rmprice[]" id="rmprice-<?php echo $k; ?>" value="<?php echo $remaining; ?>">
                                <input type="hidden" name="customers[]"  value="<?php echo $invoices->csd; ?>">
                                <input type="hidden" name="hsn[]" id="hsn-<?php echo $k; ?>" value="<?php echo $invoices->name; ?>">
                                <input type="hidden" name="serial[]" id="serial-<?php echo $k; ?>" value="">
					<?php $k++;
                                        endforeach; ?> -->

                            </tbody>
                        <?php } ?>

                    </table>
                    <div class="form-group row">

                        <div class="col-sm-8 text-right"></div>

                        <div class="col-sm-4 text-right">
                            Analysis Total :

                            <input type="text" name="total" class="col-sm-4" id="invoiceyoghtml" style=" text-align: center;" readonly="">

                        </div>


                    </div>
                    <div class="form-group row">

                        <div class="col-sm-8 text-right"></div>

                        <div class="col-sm-4 text-right">
                            Reference :

                            <input type="text" name="receipt_reference" class="col-sm-4" autocomplete="off"> <br><br>
                            Payment Date:
                            <input type="date" name="date" class="col-sm-4" autocomplete="off" required> <br><br>
                            Payment Method: <select name="method" id="method" required>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash" selected>Cash</option>
                                <option value="Card Payment">Card Payment</option>
                                <option value="Bank Cheque">Bank Cheque</option>
                                <option value="Bank Draft">Bank Draft</option>

                            </select>
                        </div>


                    </div>



                </div>

                <div class="form-group row">

                    <div class="col-sm-8 text-right"></div>

                    <div class="col-sm-4 text-right">

                        <input type="submit" name="btnSubmit" class="btn btn-success btn-lg" value="Make Payment">
                    </div>


                </div>

        </div>
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

        </form>
    </div>
</div>
</div>


<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Delete Invoice') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('delete this invoice') ?> ?</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                    id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal"
                    class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .dataTables_info {
        display: none;
    }
</style>
<script type="text/javascript">
    document.title = "Customer Receipt";
    var billsUpyog = function() {
        var out = 0;

        var totalBillVal = accounting.formatNumber(samansYog());
        $("#mahayog").html(totalBillVal);
        $("#subttlform").val(accounting.formatNumber(samansYog()));
        $("#invoiceyoghtml").val(totalBillVal);
        $("#bigtotal").html(totalBillVal);
    };

    var updatePID = function(id, numb) {
        $('#pid-' + numb).val(id);

    }

    var ValidatePrice = function(vale, id, numb) {
        var rmprice = $('#rmprice-' + numb).val();
        //console.log(); console.log();
        if (parseInt(vale) > parseInt(rmprice)) {
            $("#receipt_amount" + numb).val('');
            $("#receipt_amount" + numb).focus();
            alert("Receipt Price should be less or equal to Remaining ");
            return false;
        }

        //console.log(val);
    }

    var rowsTotal = function(numb) {
        //most res
        var result;
        var page = '';
        var totalValue = 0;
        var amountVal = accounting.unformat($("#receipt_amount" + numb).val(), accounting.settings.number.decimal);
        var totalValue = amountVal;
        $("#receipt_amount" + numb).html(accounting.formatNumber(totalValue));

        $("#total-" + numb).val(accounting.formatNumber(totalValue));
        samansYog();
    };


    var samansYog = function() {
        var itempriceList = [];
        var idList = [];
        var r = 0;
        $('.ttInput').each(function() {
            var vv = accounting.unformat($(this).val(), accounting.settings.number.decimal);
            var vid = $(this).attr('id');
            vid = vid.split("-");
            itempriceList.push(vv);
            idList.push(vid[1]);
            r++;
        });


        var sum = 0;

        for (var z = 0; z < idList.length; z++) {
            var x = idList[z];
            if (itempriceList[z] > 0) {
                sum += itempriceList[z];
            }

        }


        return accounting.unformat(sum, accounting.settings.number.decimal);
    };



    var id = '';

    function doalert(checkboxElem, cid) {

        //var preval = checkboxElem.value; console.log(preval);
        if (checkboxElem.checked) {

            $('#receipt_amount' + cid).val(checkboxElem.value);
            $('#receipt_amount' + cid).focus();
            //alert (); 
        } else {
            $('#receipt_amount' + cid).val('');
        }
    }
    $(document).ready(function() {





        /**$("#customer-rec-box").keyup(function () {
        $.ajax({
            type: "GET",
            url: baseurl + 'search_products/crsearch',
            data: 'keyword=' + $(this).val() + '&' + crsf_token + '=' + crsf_hash,
            beforeSend: function () {
                $("#customer-box").css("background", "#FFF url(" + baseurl + "assets/custom/load-ring.gif) no-repeat 165px");
            },
            success: function (data) {
                $("#customer-box-result").show();
                $("#customer-box-result").html(data);
                $("#customer-box").css("background", "none");

            }
        });
    });	*/




        $("#customer-rec-box").autocomplete({

            source: function(request, response) {

                let cid = $('#customer-rec-box').val();

                $.ajax({


                    type: "GET",
                    dataType: "json",
                    url: baseurl + 'search_products/crsearch',
                    data: 'keyword=' + cid + '&' + crsf_token + '=' + crsf_hash,

                    success: function(data) {
                        response($.map(data, function(item) {
                            var product_d = item[1];
                            var product_k = item[6];
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

                var add1 = '';
                var add2 = '';
                var add3 = '';

                $.ajax({
                    type: "POST",
                    url: baseurl + 'invoices/ajax_list_rec',
                    data: 'id=' + ui.item.data[0] + '&' + crsf_token + '=' + crsf_hash,

                    success: function(response) {
                        //$("#invoices").show();
                        $("#InvoiceTbl").html(response);
                        //$('#invoices').show();
                        //$('#invoices').append(response);               
                    }
                });
                $('#customer_id').val(ui.item.data[0]);
                $('#custom_discount').val(0);
                $('#customer_name').html('<strong><a href="' + baseurl + 'customers/view?id=' + ui.item.data[0] + '"> ' + ui.item.data[1] + '</a></strong> <br><strong>' + ui.item.data[6] + '</strong>');
                $('#customer_name').val(ui.item.data[1]);
                if (ui.item.data[2] != null) {
                    add1 = ui.item.data[2];
                }
                if (ui.item.data[8] != null) {
                    add2 = ui.item.data[8];
                }
                if (ui.item.data[9] != null) {
                    add3 = ui.item.data[9];
                }
                $('#customer_address1').html('<strong>' + add1 + ' ' + add2 + ' ' + add3 + '</strong>');
                if (ui.item.data[4] != null) {
                    $('#customer_phone').html('Phone: <strong>' + ui.item.data[4] + '</strong>');
                }
                $('#customer_bal').html('Balance (GBP) :  <strong>' + ui.item.data[7] + '</strong>');

                $("#customer").show();
                $('#invoiceyoghtml').val('');



















            }

        });





    });

    function selectGCustomer(cid, cname, cadd1, cadd2, ph, email, balance, discount = 0) {
        $('#customer_id').val(cid);
        //var cid =  $('#customer_id').val(cid);
        $('#custom_discount').val(discount);
        $('#customer_name').html('<strong><a href="' + baseurl + '/customers/view?id=' + cid + '"> ' + cname + '</a></strong> <br><strong>' + email + '</strong>');
        $('#customer_name').val(cname);
        $('#customer_address1').html('<strong>' + cadd1 + '<br>' + cadd2 + '</strong>');
        $('#customer_phone').html('Phone: <strong>' + ph + '</strong>');

        $('#customer_bal').html('Balance (GBP) :  <strong>' + balance + '</strong>');
        $("#customer-box").val();
        $("#customer-box-result").hide();
        $(".sbox-result").hide();
        $("#customer").show();

        $.ajax({
            type: "POST",
            url: baseurl + 'invoices/ajax_list_rec',
            data: 'id=' + cid + '&' + crsf_token + '=' + crsf_hash,

            success: function(response) {
                //$("#invoices").show();
                $("#InvoiceTbl").html(response);
                //$('#invoices').show();
                //$('#invoices').append(response);               
            }
        });

        $('[data-toggle="datepicker"]').datepicker({
            autoHide: true,
            format: 'dd-mm-yyyy'
        });
        $('[data-toggle="datepicker"]').datepicker('setDate', '01-03-2022');
        $('#sdate').datepicker({
            autoHide: true,
            format: 'dd-mm-yyyy'
        });
        $('#sdate').datepicker('setDate', '30-01-2022');
        $('.date30').datepicker({
            autoHide: true,
            format: 'dd-mm-yyyy'
        });
        $('.date30').datepicker('setDate', '30-01-2022');


    }


    document.addEventListener("DOMContentLoaded", function() {
        // Select your form
        var form = document.getElementById("recipt_form");

        form.addEventListener("submit", function(e) {
            e.preventDefault(); // Prevent the form from submitting immediately

            // Use SweetAlert to show the confirmation dialog
            Swal.fire({
                title: 'You wont be able to revert this!',
                text: "To reverse this payment add Credit/Advance Payment agaisnt the customer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form programmatically
                    form.submit();
                }
                // If cancelled, do nothing, form will not submit
            });
        });
    });
</script>
<style type="text/css">
    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background: #6693bc !important;
        font-weight: bold !important;
        color: #ffffff !important;
    }
</style>