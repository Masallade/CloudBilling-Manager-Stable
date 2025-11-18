<div class="content-body">

    <div class="card">

        <div class="card-content">

            <div id="notify" class="alert alert-success" style="display:none;">

                <a href="#" class="close" data-dismiss="alert">&times;</a>



                <div class="message"></div>

            </div>

            <div id="thermal_a" class="alert alert-success" style="display:none;">

                <a href="#" class="close" data-dismiss="alert">&times;</a>



                <div class="message"></div>

            </div>

            <div id="invoice-template" class="card-body">

                <div class="row wrapper white-bg page-heading">



                    <?php

                    $validtoken = hash_hmac('ripemd160', 's' . $invoice['iid'], $this->config->item('encryption_key'));





                    // $link = base_url('billing/invoice?id=' . $invoice['iid'] . '&token=' . $validtoken);
                    $link = base_url('billing/invoice' .  (($company['invoice_format'] == 1) ? '' : $company['invoice_format']) . '?id=' . $invoice['iid'] . '&token=' . $validtoken);


                    // $link = base_url('billing/stockreturn?id=' . $invoice['iid'] . '&token=' . $validtoken);

                    ?>

                </div>



                <!-- Invoice Company Details -->

                <div id="invoice-company-details" class="row mt-2">

                    <div class="col-md-6 col-sm-12 text-xs-center text-md-left">
                        <p></p>



                    </div>

                    <div class="col-md-6 col-sm-12 text-xs-center text-md-right">



                        <ul class="px-0 list-unstyled">

                            <li><?php echo $this->lang->line('Gross Amount') ?></li>

                            <li class="lead text-bold-800"><?= amountExchange($invoice['total'], 0, $this->aauth->get_user()->loc) ?></li>

                        </ul>

                    </div>

                </div>

                <!--/ Invoice Company Details -->



                <!-- Invoice Customer Details -->

                <div id="invoice-customer-details" class="row pt-2">

                    <div class="col-sm-12 text-xs-center text-md-left">

                        <p class="text-muted"><?php echo $this->lang->line('Bill To') ?></p>

                    </div>

                    <div class="col-md-6 col-sm-12 text-xs-center text-md-left">

                        <ul class="px-0 list-unstyled">



                            <li class="text-bold-800"><a href="<?php echo base_url('customers/view?id=' . $invoice['csd']); ?>"><strong class="invoice_a"><?php echo $invoice['name'] .'  -  '. $invoice['company'] . '</strong></a>
                                </li>
                                <li>' . $invoice['address'] . '</li><li>' . $invoice['city'] . ',' . $invoice['country'] . '</li><li>' . $this->lang->line('Phone') . ': ' . $invoice['phone'] . '</li><li>' . $this->lang->line('Email') . ': ' . $invoice['email']; ?>
                            </li>

                            <!-- <li class="text-bold-800"><a

                                        href="<?php echo $curl ?>"><strong

                                            class="invoice_a"><?php echo $invoice['name'] . '</strong></a></li><li>' . $invoice['address'] . '</li><li>' . $invoice['city'] . ',' . $invoice['country'] . '</li><li>' . $this->lang->line('Phone') . ': ' . $invoice['phone'] . '</li><li>' . $this->lang->line('Email') . ': ' . $invoice['email']; ?>

                            </li> -->

                        </ul>



                    </div>

                    <div class="offset-md-3 col-md-3 col-sm-12 text-xs-center text-md-left">

                        <?php echo '<p><span class="text-muted">' . $this->lang->line('Order Date') . ' :</span> ' . dateformat($invoice['invoicedate']) . '</p> <p><span class="text-muted">' . $this->lang->line('Due Date') . ' :</span> ' . dateformat($invoice['invoiceduedate']) . '</p>  <p><span class="text-muted"></p>';

                        ?>

                    </div>

                </div>

                <!--/ Invoice Customer Details -->



                <!-- Invoice Items Details -->

                <div id="invoice-items-details" class="pt-2">

                    <div class="row">

                        <div class="table-responsive col-sm-12">

                            <table class="table table-striped">

                                <thead>

                                    <?php if ($invoice['taxstatus'] == 'cgst') { ?>



                                        <tr>

                                            <th>#</th>

                                            <th><?php echo $this->lang->line('Description') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('HSN') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('Rate') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('Qty') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('Discount') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('CGST') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('SGST') ?></th>

                                            <th class="text-xs-left"><?php echo $this->lang->line('Amount') ?></th>

                                        </tr>

                                </thead>

                                <tbody>

                                    <?php $c = 1;

                                        $sub_t = 0;



                                        foreach ($products as $row) {

                                            $sub_t += $row['price'] * $row['qty'];

                                            $gst = $row['totaltax'] / 2;

                                            $rate = $row['tax'] / 2;

                                            echo '<tr>

                        <th scope="row">' . $c . '</th>

                            <td>' . $row['product'] . '</td> 

                            <td>' . $row['code'] . '</td>                          

                            <td>' . amountExchange($row['price'], 0, $this->aauth->get_user()->loc) . '</td>

                             <td>' . amountFormat_general($row['qty']) . $row['unit'] . '</td>

                              <td>' . amountExchange($row['totaldiscount'], 0, $this->aauth->get_user()->loc) . ' (' . amountFormat_s($row['discount']) . $this->lang->line($invoice['format_discount']) . ')</td>

                            <td>' . amountExchange($gst, 0, $this->aauth->get_user()->loc) . ' (' . amountFormat_s($rate) . '%)</td>

                             <td>' . amountExchange($gst, 0, $this->aauth->get_user()->loc) . ' (' . amountFormat_s($rate) . '%)</td>                           

                            <td>' . amountExchange($row['subtotal'], 0, $this->aauth->get_user()->loc) . '</td>

                        </tr>';



                                            echo '<tr><td colspan=5>' . $row['product_des'] . '</td></tr>';

                                            $c++;
                                        } ?>



                                </tbody>

                            <?php



                                    } elseif ($invoice['taxstatus'] == 'igst') {

                            ?>

                                <tr>

                                    <th>#</th>

                                    <th><?php echo $this->lang->line('Description') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('HSN') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Rate') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Qty') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Discount') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('IGST') ?></th>



                                    <th class="text-xs-left"><?php echo $this->lang->line('Amount') ?></th>

                                </tr>

                                </thead>

                                <tbody>

                                    <?php $c = 1;

                                        $sub_t = 0;



                                        foreach ($products as $row) {

                                            $sub_t += $row['price'] * $row['qty'];



                                            echo '<tr>

                        <th scope="row">' . $c . '</th>

                            <td>' . $row['product'] . '</td> 

                            <td>' . $row['code'] . '</td>                          

                            <td>' . amountExchange($row['price'], 0, $this->aauth->get_user()->loc) . '</td>

                             <td>' . amountFormat_general($row['qty']) . $row['unit'] . '</td>

                              <td>' . amountExchange($row['totaldiscount'], 0, $this->aauth->get_user()->loc) . ' (' . amountFormat_s($row['discount']) . $this->lang->line($invoice['format_discount']) . ')</td>

                            <td>' . amountExchange($row['totaltax'], 0, $this->aauth->get_user()->loc) . ' (' . amountFormat_s($row['tax']) . '%)</td>

                                            

                            <td>' . amountExchange($row['subtotal'], 0, $this->aauth->get_user()->loc) . '</td>

                        </tr>';



                                            echo '<tr><td colspan=5>' . $row['product_des'] . '</td></tr>';

                                            $c++;
                                        } ?>



                                </tbody>

                            <?php

                                    } else {

                            ?>

                                <tr>

                                    <th>#</th>

                                    <th><?php echo $this->lang->line('Product Code') ?></th>

                                    <th><?php echo $this->lang->line('Description') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Unit_Rate') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Qty') ?></th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Tax') ?> (<?php echo $this->lang->line('Unit_Tax') ?>)</th>

                                    <th class="text-xs-left"><?php echo $this->lang->line('Amount') ?></th>

                                </tr>

                                </thead>

                                <tbody>

                                    <?php $c = 1;

                                        $sub_t = 0;



                                        foreach ($products as $row) {

                                            $sub_t += $row['price'] * $row['qty'];

                                            echo '<tr>

                                                        <th scope="row">' . $c . '</th>

                                                        <td>' . $row['product'] . '</td>   

                                                        <td>' . $row['product_des'] . '</td>                           

                                                        <td>' . amountExchange($row['price'], 0, $this->aauth->get_user()->loc) . '</td>

                                                        <td>' . amountFormat_general($row['qty']) . $row['unit'] . '</td>

                                                        <td>' . amountExchange($row['totaltax'], 0, $this->aauth->get_user()->loc) . ' (' . amountFormat_s($row['tax']) . '%)</td>

                                                        <td>' . amountExchange($row['subtotal'], 0, $this->aauth->get_user()->loc) . '</td>

                                                    </tr>';

                                            // echo '<tr><td colspan=5>' . $row['product_des'] . '</td></tr>';

                                            $c++;
                                        } ?>



                                </tbody>

                            <?php } ?>

                            </table>

                        </div>

                    </div>

                    <p></p>

                    <div class="row">

                        <div class="col-md-7 col-sm-12 text-xs-center text-md-left">





                            <div class="row">

                                <div class="col-md-8">
                                    <p

                                        class="lead"><?php echo $this->lang->line('Payment Status') ?>:

                                        <u><strong

                                                id="pstatus"><?php echo $this->lang->line(ucwords($invoice['status'])) ?></strong></u>

                                    </p>

                                    <p class="lead"><?php echo $this->lang->line('Payment Method') ?>: <u><strong

                                                id="pmethod"><?php echo $this->lang->line($invoice['pmethod']) ?></strong></u>

                                    </p>



                                    <!-- <p class="lead mt-1"><br><?php echo $this->lang->line('Note') ?>:</p>

                                    <code>

                                        <?php echo $invoice['notes'] ?>

                                    </code> -->

                                </div>

                            </div>

                        </div>

                        <div class="col-md-5 col-sm-12">

                            <p class="lead"><?php echo $this->lang->line('Total Due') ?></p>

                            <div class="table-responsive">

                                <table class="table">

                                    <tbody>

                                        <tr>

                                            <td><?php echo $this->lang->line('Sub Total') ?></td>

                                            <td class="text-xs-right"> <?php echo amountExchange($sub_t, 0, $this->aauth->get_user()->loc) ?></td>

                                        </tr>

                                        <tr>

                                            <td><?php echo $this->lang->line('Tax') ?></td>

                                            <td class="text-xs-right"><?php echo amountExchange($invoice['tax'], 0, $this->aauth->get_user()->loc) ?></td>

                                        </tr>

                                        <tr>



                                        <tr>

                                            <td class="text-bold-800"><?php echo $this->lang->line('Total') ?></td>

                                            <td class="text-bold-800 text-xs-right"> <?php echo amountExchange($invoice['total'], 0, $this->aauth->get_user()->loc) ?></td>

                                        </tr>

                                        <tr>

                                            <td><?php echo $this->lang->line('Payment Made') ?></td>

                                            <td class="pink text-xs-right">

                                                (-) <?php echo ' <span id="paymade">' . amountExchange($invoice['pamnt'], 0, $this->aauth->get_user()->loc) ?></span></td>

                                        </tr>

                                        <tr class="bg-grey bg-lighten-4">

                                            <td class="text-bold-800"><?php echo $this->lang->line('Balance Due') ?></td>

                                            <td class="text-bold-800 text-xs-right"> <?php $myp = '';

                                                                                        $rming = $invoice['total'] - $invoice['pamnt'];

                                                                                        if ($rming < 0) {

                                                                                            $rming = 0;
                                                                                        }

                                                                                        echo ' <span id="paydue">' . amountExchange($rming, 0, $this->aauth->get_user()->loc) . '</span></strong>'; ?></td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>



                        </div>

                    </div>

                </div>



                <hr>

                <pre><?php echo $this->lang->line('Public Access URL') ?>: <?php

                                                                            echo $link ?></pre>

                <!-- <div class="row">

                    <table class="table table-striped">

                        <thead>

                        <tr>

                            <th><?php echo $this->lang->line('Files') ?></th>





                        </tr>

                        </thead>

                        <tbody id="activity">

                        <?php foreach ($attach as $row) {



                            echo '<tr><td><a data-url="' . base_url() . 'purchase/file_handling?op=delete&name=' . $row['col1'] . '&invoice=' . $invoice['iid'] . '" class="aj_delete"><i class="btn-danger btn-lg fa fa-trash"></i></a> <a class="n_item" href="' . base_url() . 'userfiles/attach/' . $row['col1'] . '"> ' . $row['col1'] . ' </a></td></tr>';
                        } ?>



                        </tbody>

                    </table>

                </div> -->

                <!-- <div class="card">

                    //The fileinput-button span is used to style the file input field as button 

                    <span class="btn btn-success fileinput-button">

        <i class="glyphicon glyphicon-plus"></i>

        <span>Select files...</span>

                        // The file input field used as target for the file upload widget

        <input id="fileupload" type="file" name="files[]" multiple>

    </span>

                    <br>

                    <pre>Allowed: gif, jpeg, png, docx, docs, txt, pdf, xls </pre>

                    <br>

                    //The global progress bar 

                    <div id="progress" class="progress">

                        <div class="progress-bar progress-bar-success"></div>

                    </div>

                    // The container for the uploaded files 

                    <table id="files" class="files"></table>

                    <br>

                </div> -->

            </div>

            </section>

        </div>

    </div>

</div>

<script src="<?php echo assets_url('assets/myjs/jquery.ui.widget.js') ?>"></script>

<script src="<?php echo assets_url('assets/myjs/jquery.fileupload.js') ?>"></script>

<script>
    document.title = '<?php echo $this->lang->line('View Invoice') ?> #<?php echo $invoice['tid'] ?>';
    /*jslint unparam: true */

    /*global window, $ */

    $(function() {

        'use strict';

        // Change this to the location of your server-side upload handler:

        var url = '<?php echo base_url() ?>purchase/file_handling?id=<?php echo $invoice['iid'] ?>';

        $('#fileupload').fileupload({

                url: url,

                dataType: 'json',

                formData: {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                },

                done: function(e, data) {

                    $.each(data.result.files, function(index, file) {

                        $('#files').append('<tr><td><a data-url="<?php echo base_url() ?>purchase/file_handling?op=delete&name=' + file.name + '&invoice=<?php echo $invoice['iid'] ?>" class="aj_delete"><i class="btn-danger btn-sm fa fa-trash"></i> ' + file.name + ' </a></td></tr>');



                    });

                },

                progressall: function(e, data) {

                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    $('#progress .progress-bar').css(

                        'width',

                        progress + '%'

                    );

                }

            }).prop('disabled', !$.support.fileInput)

            .parent().addClass($.support.fileInput ? undefined : 'disabled');

    });



    $(document).on('click', ".aj_delete", function(e) {

        e.preventDefault();



        var aurl = $(this).attr('data-url');

        var obj = $(this);



        jQuery.ajax({



            url: aurl,

            type: 'GET',

            dataType: 'json',

            success: function(data) {

                obj.closest('tr').remove();

                obj.remove();

            }

        });



    });
</script>



<!-- Modal HTML -->

<div id="part_payment" class="modal fade">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5><?php echo $this->lang->line('Payment') ?></h5>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>



            </div>



            <div class="modal-body">

                <form class="payment">

                    <div class="row">

                        <div class="col mb-1">

                            <div class="input-group">

                                <div class="input-group-addon"><?php echo $this->config->item('currency') ?></div>

                                <input type="text" class="form-control" placeholder="Total Amount" name="amount"

                                    id="rmpay"

                                    value="<?= amountExchange_s($rming, 0, $this->aauth->get_user()->loc) ?>">

                            </div>



                        </div>

                        <div class="col">

                            <div class="input-group">

                                <div class="input-group-addon"><span class="icon-calendar4"

                                        aria-hidden="true"></span></div>

                                <input type="text" class="form-control required" id="tsn_date"

                                    placeholder="Billing Date" name="paydate"

                                    value="<?php echo dateformat($this->config->item('date')); ?>">

                            </div>

                        </div>

                    </div>



                    <div class="row">

                        <div class="col mb-1"><label

                                for="pmethod"><?php echo $this->lang->line('Payment Method') ?></label>

                            <select name="pmethod" class="form-control mb-1">

                                <option value="Cash"><?php echo $this->lang->line('Cash') ?></option>

                                <option value="Card"><?php echo $this->lang->line('Card') ?></option>

                                <option value="Bank">Bank</option>

                            </select><label for="account"><?php echo $this->lang->line('Account') ?></label>



                            <select name="account" class="form-control">

                                <?php foreach ($acclist as $row) {

                                    echo '<option value="' . $row['id'] . '">' . $row['holder'] . ' / ' . $row['acn'] . '</option>';
                                }

                                ?>

                            </select>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col mb-1"><label

                                for="shortnote"><?php echo $this->lang->line('Note') ?></label>

                            <input type="text" class="form-control"

                                name="shortnote" placeholder="Short note"

                                value="Payment refund for purchase #<?php echo $invoice['tid'] ?>">
                        </div>

                    </div>

                    <div class="modal-footer">

                        <input type="hidden" class="form-control required"

                            name="tid" id="invoiceid" value="<?php echo $invoice['iid'] ?>">

                        <button type="button" class="btn btn-default"

                            data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>

                        <input type="hidden" name="cid" value="<?php echo $invoice['cid'] ?>"><input type="hidden"

                            name="cname"

                            value="<?php echo $invoice['name'] ?>">

                        <button type="button" class="btn btn-primary"

                            id="stockreturnpayment"><?php echo $this->lang->line('Receive Payment') ?></button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>





<!-- cancel -->

<div id="cancel_bill" class="modal fade">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">



                <h4 class="modal-title"><?php echo $this->lang->line('Cancel Purchase Order') ?></h4>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

            </div>

            <div class="modal-body">

                <form class="cancelbill">

                    <div class="row">

                        <div class="col">

                            <?php echo $this->lang->line('this action! Are you sure') ?>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <input type="hidden" class="form-control"

                            name="tid" value="<?php echo $invoice['iid'] ?>">

                        <button type="button" class="btn btn-default"

                            data-dismiss="modal"> <?php echo $this->lang->line('Close') ?></button>

                        <button type="button" class="btn btn-primary"

                            id="send"> <?php echo $this->lang->line('Cancel') ?></button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</div>



<!-- Modal HTML -->

<div id="sendEmail" class="modal fade">

    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <div class="modal-header">



                <h4 class="modal-title">Email</h4>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

            </div>

            <div id="request">

                <div id="ballsWaveG">

                    <div id="ballsWaveG_1" class="ballsWaveG"></div>

                    <div id="ballsWaveG_2" class="ballsWaveG"></div>

                    <div id="ballsWaveG_3" class="ballsWaveG"></div>

                    <div id="ballsWaveG_4" class="ballsWaveG"></div>

                    <div id="ballsWaveG_5" class="ballsWaveG"></div>

                    <div id="ballsWaveG_6" class="ballsWaveG"></div>

                    <div id="ballsWaveG_7" class="ballsWaveG"></div>

                    <div id="ballsWaveG_8" class="ballsWaveG"></div>

                </div>

            </div>

            <div class="modal-body" id="emailbody" style="display: none;">

                <form id="sendbill">

                    <div class="row">

                        <div class="col">

                            <div class="input-group">

                                <div class="input-group-addon"><span class="icon-envelope-o"

                                        aria-hidden="true"></span></div>

                                <input type="text" class="form-control" placeholder="Email" name="mailtoc"

                                    value="<?php echo $invoice['email'] ?>">

                            </div>



                        </div>



                    </div>





                    <div class="row">

                        <div class="col mb-1"><label

                                for="shortnote"><?php echo $this->lang->line('Customer Name') ?></label>

                            <input type="text" class="form-control"

                                name="customername" value="<?php echo $invoice['name'] ?>">
                        </div>

                    </div>

                    <div class="row">

                        <div class="col mb-1"><label

                                for="shortnote"><?php echo $this->lang->line('Subject') ?></label>

                            <input type="text" class="form-control"

                                name="subject" id="subject">

                        </div>

                    </div>

                    <div class="row">

                        <div class="col mb-1"><label

                                for="shortnote"><?php echo $this->lang->line('Message') ?></label>

                            <textarea name="text" class="summernote" id="contents" title="Contents"></textarea>
                        </div>

                    </div>



                    <input type="hidden" class="form-control"

                        id="invoiceid" name="tid" value="<?php echo $invoice['iid'] ?>">

                    <input type="hidden" class="form-control"

                        id="emailtype" value="none">





                </form>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default"

                    data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>

                <button type="button" class="btn btn-primary"

                    id="sendM"><?php echo $this->lang->line('Send') ?></button>

            </div>

        </div>

    </div>

</div>



<div id="pop_model" class="modal fade">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">



                <h4 class="modal-title"><?php echo $this->lang->line('Change Status') ?></h4>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

            </div>



            <div class="modal-body">

                <form id="form_model">





                    <div class="row">

                        <div class="col-xs-12 mb-1"><label

                                for="pmethod"><?php echo $this->lang->line('Mark As') ?></label>

                            <select name="status" class="form-control mb-1">

                                <option value="pending"><?php echo $this->lang->line('Pending') ?></option>

                                <option value="accepted"><?php echo $this->lang->line('Accepted') ?></option>

                                <option value="rejected"><?php echo $this->lang->line('Rejected') ?></option>

                                <option value="partial"><?php echo $this->lang->line('Partial') ?></option>

                            </select>



                        </div>

                    </div>



                    <div class="modal-footer">

                        <input type="hidden" class="form-control required"

                            name="tid" id="invoiceid" value="<?php echo $invoice['iid'] ?>">

                        <button type="button" class="btn btn-default"

                            data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>

                        <input type="hidden" id="action-url" value="stockreturn/update_status">

                        <button type="button" class="btn btn-primary"

                            id="submit_model"><?php echo $this->lang->line('Change Status') ?></button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>



<script type="text/javascript">
    $(function() {

        $('.summernote').summernote({

            height: 100,

            toolbar: [

                // [groupName, [list of button]]

                ['style', ['bold', 'italic', 'underline', 'clear']],

                ['font', ['strikethrough', 'superscript', 'subscript']],

                ['fontsize', ['fontsize']],

                ['color', ['color']],

                ['para', ['ul', 'ol', 'paragraph']],

                ['height', ['height']],

                ['fullscreen', ['fullscreen']],

                ['codeview', ['codeview']]

            ]

        });



        $('#sendM').on('click', function(e) {

            e.preventDefault();



            sendBill($('.summernote').summernote('code'));



        });

    });



    $(document).on('click', "#stockreturnpayment", function(e) {

        e.preventDefault();



        var pyurl = baseurl + 'stockreturn/pay';



        payInvoice(pyurl);





    });



    $(document).on('click', "#cancel-bill_p", function(e) {

        e.preventDefault();



        $('#cancel_bill').modal({
            backdrop: 'static',
            keyboard: false
        }).one('click', '#send', function() {

            var acturl = 'stockreturn/cancelorder';

            cancelBill(acturl);



        });

    });
</script>
<script>
    $(document).ready(function() {
        $('.share-invoice').on('click', function(event) {
            event.preventDefault();
            let invoiceId = $(this).data('id');
            let invoiceUrl = $(this).data('url');
            let downloadUrl = '<?php echo base_url("invoices/printinvoice"); ?>' + '?id=' + invoiceId + '&d=1';

            // Open the download link in a new tab
            let downloadWindow = window.open(downloadUrl, '_blank');
            downloadWindow.focus();

            // After a short delay, open the WhatsApp share link
            setTimeout(function() {
                let shareText = 'Check out this invoice: ' + invoiceUrl;
                let whatsappUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareText);
                window.open(whatsappUrl, '_blank');
            }, 2000); // Adjust delay as needed
        });
    });
</script>