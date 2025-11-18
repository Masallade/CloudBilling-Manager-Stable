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

    /* Column width adjustments for transactions table */
    #crtstable th:nth-child(1), #crtstable td:nth-child(1) { width: 8%; } /* No column */
    #crtstable th:nth-child(2), #crtstable td:nth-child(2) { width: 12%; } /* Date column */
    #crtstable th:nth-child(3), #crtstable td:nth-child(3) { width: 10%; } /* Ref column */
    #crtstable th:nth-child(4), #crtstable td:nth-child(4) { width: 30%; } /* Details column - wider */
    #crtstable th:nth-child(5), #crtstable td:nth-child(5) { width: 15%; } /* User column */
    #crtstable th:nth-child(6), #crtstable td:nth-child(6) { width: 8%; } /* Debit column - narrower */
    #crtstable th:nth-child(7), #crtstable td:nth-child(7) { width: 8%; } /* Credit column - narrower */
    #crtstable th:nth-child(8), #crtstable td:nth-child(8) { width: 9%; } /* Balance column - narrower */
    
    /* Right align monetary columns */
    #crtstable td:nth-child(6), #crtstable td:nth-child(7), #crtstable td:nth-child(8) {
        text-align: right;
    }
</style>
<div class="content-body">
    <div class="card">
        <!-- <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <h2 class="card-title">Customer Account
                        : <?php echo $details['name'] ?>
                    </h2>
                </div>


                <div class="col-md-6">
                    <div class="col-md-8">




                        <div class="input-group">

                            <input type="text" class="form-control" name="cst" id="customer-box"
                                placeholder="Enter Customer Code"
                                autocomplete="off" />
                            <span class="input-group-btn">
                                <button class="btn btn-primary" id="search-client-button" type="button">Search</button>
                                <input type="hidden" name="payer_id" id="customer_id" value="0">
                            </span>
                        </div>
                    </div>
                </div>




                <div class="col-md-3">
                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>

                <div class="message"></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!isset($is_ajax) || !$is_ajax): ?>
                        <div class="col-md-2 border-right border-right-grey">




                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <a href="<?php echo base_url('customers/view?id=' . $details['id']) ?>"
                                        class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-user"></i> View Details</a>
                                    <a href="<?php echo base_url('customers/invoices?id=' . $details['id']) ?>"
                                        class="btn btn-success btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-file-text"></i> <?php echo $this->lang->line('View Invoices') ?>
                                    </a>
                                    <a href="<?php echo base_url('customers/transactions?id=' . $details['id']) ?>"
                                        class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block  btn-lighten-1"><i
                                            class="fa fa-money"></i> View Activity
                                    </a>
                                    <a href="<?php echo base_url('customers/transactions_credit_notes?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-md mr-1 mb-1 btn-block  btn-lighten-1" style="background-color: #7c51a1 !important;"><i
                                            class="fa fa-money"></i> View Credit Notes
                                    </a>

                                    <a href="<?php echo base_url('customers/transactions_advance_payments?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-md mr-1 mb-1 btn-block  btn-lighten-1" style="background-color: #FFA87D !important;border-color: #FF976A !important;"><i
                                            class="fa fa-money"></i> View Advance Payments
                                    </a>
                                    <a href="<?php echo base_url('customers/statement?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i
                                            class="fa fa-briefcase"></i> <?php echo $this->lang->line('Account Statements') ?>
                                    </a>
                                    </a>
                                    <!-- <a href="<?php echo base_url('customers/balanceinvoice?id=' . $details['id']) ?>" target="_blank"
                                        class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-print"></i>
                                            Print Balance Invoice
                                        </a> -->
                                    <a href="<?php echo base_url('customers/vatreport?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-print"></i>
                                        Print VAT Report
                                    </a>
                                    <!--     <a href="<?php echo base_url('customers/quotes?id=' . $details['id']) ?>"
                                        class="btn btn-purple btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                                    class="fa fa-quote-left"></i> Add Balance
                                        </a>  -->


                                </div>
                            </div>


                        </div>
                        <div class="col-md-10">
                        <?php else: ?>
                            <div class="col-md-12">
                        <?php endif; ?>

                        <h4><?php echo $this->lang->line('Transactions') ?></h4>
                        <hr>
                        <table id="crtstable" class="table table-striped table-bordered zero-configuration"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><?php echo 'No'; ?></th>
                                    <th><?php echo $this->lang->line('Date') ?></th>


                                    <th><?php echo 'Ref'; ?></th>
                                    <th>Details</th>
                                    <th>User</th>

                                    <th><?php echo $this->lang->line('Debit') ?> (GBP)</th>
                                    <th><?php echo $this->lang->line('Credit') ?> (GBP)</th>
                                    <th>Balance</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th><?php echo 'No'; ?></th>
                                    <th><?php echo $this->lang->line('Date') ?></th>


                                    <th><?php echo 'Ref'; ?></th>
                                    <th>Details</th>
                                    <th>User</th>

                                    <th><?php echo $this->lang->line('Debit') ?> (GBP)</th>
                                    <th><?php echo $this->lang->line('Credit') ?> (GBP)</th>
                                    <th>Balance</th>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                </div>


            </div>
        </div>
    </div>


    <div id="sendMail" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Email</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="sendmail_form"><input type="hidden"
                            name="<?php echo $this->security->get_csrf_token_name(); ?>"
                            value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="row">
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-envelope-o"
                                            aria-hidden="true"></span></div>
                                    <input type="text" class="form-control" placeholder="Email" name="mailtoc"
                                        value="<?php echo $details['email'] ?>">
                                </div>

                            </div>

                        </div>


                        <div class="row">
                            <div class="col mb-1"><label
                                    for="shortnote"><?php echo $this->lang->line('Customer Name') ?></label>
                                <input type="text" class="form-control"
                                    name="customername" value="<?php echo $details['name'] ?>">
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
                            id="cid" name="tid" value="<?php echo $details['id'] ?>">
                        <input type="hidden" id="action-url" value="communication/send_general">


                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                    <button type="button" class="btn btn-primary"
                        id="sendNow"><?php echo $this->lang->line('Send') ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo assets_url('assets/myjs/jquery.ui.widget.js') ?>"></script>
    <!-- The basic File Upload plugin -->
    <script src="<?php echo assets_url('assets/myjs/jquery.fileupload.js') ?>"></script>
    <script>
        /*jslint unparam: true */
        /*global window, $ */
        $(function() {
            'use strict';
            // Change this to the location of your server-side upload handler:
            var url = '<?php echo base_url() ?>customers/displaypic?id=<?php echo $details['id'] ?>';
            $('#fileupload').fileupload({
                    url: url,
                    dataType: 'json',
                    formData: {
                        '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                    },
                    done: function(e, data) {

                        //$('<p/>').text(file.name).appendTo('#files');
                        $("#dpic").load(function() {
                            $(this).hide();
                            $(this).fadeIn('slow');
                        }).attr('src', '<?php echo base_url() ?>userfiles/customers/' + data.result);


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
    </script>
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


        });
    </script>


    <div id="delete_model" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title"><?php echo $this->lang->line('Delete Transaction') ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><?php echo $this->lang->line('delete this transaction') ?></p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="object-id" value="">
                    <input type="hidden" id="action-url" value="transactions/delete_i">
                    <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                    <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("#search-client-button").click(function() {
            var c_id = $("#customer_id").val();
            if (c_id != 0) {
                base_url = '<?= base_url(); ?>' + '/customers/view?id=' + c_id;
                window.location.href = base_url;

            }
        });
        var table;

        $(document).ready(function() {

            //datatables
            table = $('#crtstable').DataTable({
                "bPaginate": true,
                "bFilter": true,
                "bInfo": false,
                "bSortable": true,
                "bRetrieve": true,
                "pageLength": 50,
                aoColumnDefs: [{
                        "aTargets": [0],
                        "bSortable": true
                    },
                    {
                        "aTargets": [1],
                        "bSortable": true
                    },
                    {
                        "aTargets": [2],
                        "bSortable": true
                    },
                    {
                        "aTargets": [3],
                        "bSortable": false
                    }
                ],
                "processing": true, //Feature control the processing indicator.
                "serverSide": false, //Feature control DataTables' server-side processing mode.
                "order": [],
                // "order": [ 1, "desc" ], //Initial no order.
                responsive: true,
                <?php datatable_lang(); ?>

                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo site_url('customers/translist') ?>",
                    "type": "POST",
                    "data": {
                        'cid': <?php echo $_GET['id'] ?>,
                        '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                    }
                },

                //Set column definition initialisation properties.
                "columnDefs": [

                    {
                        "targets": [0], //first column / numbering column
                        "orderable": true, //set not orderable
                    },
                    {
                        "targets": [1], // second column / "date" column
                        "orderable": true, // set orderable
                        "orderData": [1], // specify the index of the column to sort
                        "orderSequence": ["DESC"] // specify the sorting order ("asc" for ascending)
                    },
                ],


            });

        });

        var table = $('#crtstable').DataTable();

        $('.dataTables_filter input[type="search"]').unbind().keyup(function(e) {
            var value = $(this).val().toUpperCase();
            table.search(value).draw();
        });
    </script>