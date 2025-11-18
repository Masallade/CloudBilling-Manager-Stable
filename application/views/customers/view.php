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
<div class="content-body">
    <div class="card">
        <div class="card-header">
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
        </div>

        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>

                <div class="message"></div>
            </div>
            <div class="card-body">


                <div class="row">
                    <div class="col-md-2 border-right border-right-grey">


                        <div class="row mt-3">
                            <div class="col-md-12">
                                <?php if ($this->aauth->permission_new(null, 'customerViewDetails')) { ?>
                                <button type="button" data-action="view-details" data-customer-id="<?= $details['id'] ?>" class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1 customer-nav-btn active"><i class="fa fa-user"></i> View Details</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'customerViewInvoices')) { ?>
                                <button type="button" data-action="view-invoices" data-customer-id="<?= $details['id'] ?>" class="btn btn-success btn-md mr-1 mb-1 btn-block btn-lighten-1 customer-nav-btn"><i class="fa fa-file-text"></i> <?php echo $this->lang->line('View Invoices') ?></button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'customerViewActivity')) { ?>
                                <button type="button" data-action="view-activity" data-customer-id="<?= $details['id'] ?>" class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block btn-lighten-1 customer-nav-btn"><i class="fa fa-eye"></i> View Activity</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'customerViewCreditNotes')) { ?>
                                <button type="button" data-action="view-credit-notes" data-customer-id="<?= $details['id'] ?>" class="btn btn-primary btn-md mr-1 mb-1 btn-block btn-lighten-1 customer-nav-btn" style="background-color: #7c51a1 !important;border-color: #7c51a1 !important;"><i class="fa fa-credit-card"></i> View Credit Notes</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'customerViewAdvancePayments')) { ?>
                                <button type="button" data-action="view-advance-payments" data-customer-id="<?= $details['id'] ?>" class="btn btn-primary btn-md mr-1 mb-1 btn-block btn-lighten-1 customer-nav-btn" style="background-color: #FFA87D !important;border-color: #FF976A !important;"><i class="fa fa-dollar"></i> View Advance Payments</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'customerAccountStatements')) { ?>
                                <button type="button" data-action="account-statements" data-customer-id="<?= $details['id'] ?>" class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1 customer-nav-btn"><i class="fa fa-briefcase"></i> <?php echo $this->lang->line('Account Statements') ?></button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'customerPrintVATReport')) { ?>
                                <button type="button" data-action="print-vat-report" data-customer-id="<?= $details['id'] ?>" class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1 customer-nav-btn"><i class="fa fa-print"></i> Print VAT Report</button>
                                <?php } ?>
                            </div>
                        </div>


                    </div>
                    <div class="col-md-10">
                        <div id="mybutton">
                            <div class="">
                                <?php if ($this->aauth->permission_new(null, 'customerSendMessage')) {
                                ?>
                                    <a href="#sendMail" data-toggle="modal" data-remote="false"
                                        class="btn btn-primary btn-md " data-type="reminder"><i
                                            class="fa fa-envelope"></i> <?php echo $this->lang->line('Send Message') ?>
                                    </a>
                                <?php } ?>
                                <?php if ($this->aauth->permission_new(null, 'customerEditProfile')) { ?>
                                    <a href="<?php echo base_url('customers/edit?id=' . $details['id']) ?>"
                                        class="btn btn-info btn-md"><i
                                            class="fa fa-pencil"></i> <?php echo $this->lang->line('Edit Profile') ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>

                        <h5 class="bg-blue bg-lighten-4  p-1 mt-2"><strong><?php echo  $this->lang->line('Balance') . ': ' . amountExchange($details['balance'], 0, $this->aauth->get_user()->loc) ?></strong></h5>

                        <hr>
                        <div id="customer-content-area">
                            <!-- Content will be loaded here via AJAX -->
                        </div>
                    </div>
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
<!-- jQuery UI for datepicker functionality -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    document.title = 'Customer Details';
    /*jslint unparam: true */
    /*global window, $ */
    $(function() {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var url = '<?php echo base_url() ?>customers/displaypic?id=<?php echo $details['id'] ?>&<?= $this->security->get_csrf_token_name() ?>=' + crsf_hash;
        $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                formData: {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                },
                done: function(e, data) {
                    //$('<p/>').text(file.name).appendTo('#files');
                    $("#dpic").attr('src', '<?php echo base_url() ?>userfiles/customers/' + data.result + '?8978');
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
    $("#search-client-button").click(function() {
        var c_id = $("#customer_id").val();
        if (c_id != 0) {
            base_url = '<?= base_url(); ?>' + '/customers/view?id=' + c_id;
            window.location.href = base_url;

        }
    });

    $(".save-terms").click(function() {

    })

    // AJAX Navigation for Customer Card
    $(document).ready(function() {
        // Load initial content (View Details)
        loadCustomerContent('view-details', <?= $details['id'] ?>);

        // Handle navigation button clicks
        $('.customer-nav-btn').on('click', function() {
            var action = $(this).data('action');
            var customerId = $(this).data('customer-id');

            // Update active button
            $('.customer-nav-btn').removeClass('active');
            $(this).addClass('active');

            // Load content
            loadCustomerContent(action, customerId);
        });
    });

    function loadCustomerContent(action, customerId) {
        // Show loading indicator
        $('#customer-content-area').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

        var url = '';
        switch (action) {
            case 'view-details':
                url = '<?= base_url("customers/ajax_view_details") ?>';
                break;
            case 'view-invoices':
                url = '<?= base_url("customers/ajax_invoices") ?>';
                break;
            case 'view-activity':
                url = '<?= base_url("customers/ajax_transactions") ?>';
                break;
            case 'view-credit-notes':
                url = '<?= base_url("customers/ajax_credit_notes") ?>';
                break;
            case 'view-advance-payments':
                url = '<?= base_url("customers/ajax_advance_payments") ?>';
                break;
            case 'account-statements':
                url = '<?= base_url("customers/ajax_statement") ?>';
                break;
            case 'print-vat-report':
                url = '<?= base_url("customers/ajax_vatreport") ?>';
                break;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                id: customerId,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            success: function(response) {
                $('#customer-content-area').html(response);
                
                // Reinitialize datepickers after AJAX content loads
                initializeDatepickers();
            },
            error: function(xhr, status, error) {
                $('#customer-content-area').html('<div class="alert alert-danger">Error loading content. Please try again.</div>');
                console.error('AJAX Error:', error);
            }
        });
    }

    // Function to initialize datepickers
    function initializeDatepickers() {
        // Check if jQuery UI is loaded
        if (typeof $.fn.datepicker !== 'undefined') {
            // Initialize datepickers for any date input fields
            $('#sdate, #edate').each(function() {
                if (!$(this).hasClass('hasDatepicker')) {
                    $(this).datepicker({
                        dateFormat: 'dd-mm-yy',
                        changeMonth: true,
                        changeYear: true,
                        yearRange: 'c-5:c+5',
                        showButtonPanel: true,
                        closeText: 'Close',
                        currentText: 'Today',
                        monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
                                   'July', 'August', 'September', 'October', 'November', 'December'],
                        monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                        dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                        dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']
                    });
                }
            });
            
            // Set default dates for statement and VAT report
            if ($('#sdate').length > 0 && $('#edate').length > 0) {
                // For account statements - set last 30 days
                if ($('#customer-content-area').find('form[action*="statement"]').length > 0) {
                    var thirtyDaysAgo = new Date();
                    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                    $('#sdate').datepicker('setDate', thirtyDaysAgo);
                    $('#edate').datepicker('setDate', new Date());
                }
                // For VAT reports - set current month
                else if ($('#customer-content-area').find('form[action*="vatinvoice"]').length > 0) {
                    var today = new Date();
                    var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    $('#sdate').datepicker('setDate', firstDay);
                    $('#edate').datepicker('setDate', today);
                }
            }
        } else {
            // If jQuery UI is not loaded, try again after a short delay
            setTimeout(initializeDatepickers, 100);
        }
    }
</script>