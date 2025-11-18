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
    <div id="sendMail" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Email</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="sendmail_form"><input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="row">
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-envelope-o" aria-hidden="true"></span></div>
                                    <input type="text" class="form-control" placeholder="Email" name="mailtoc" value="<?php echo $details['email'] ?>">
                                </div>

                            </div>

                        </div>


                        <div class="row">
                            <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Customer Name') ?></label>
                                <input type="text" class="form-control" name="customername" value="<?php echo $details['name'] ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Subject') ?></label>
                                <input type="text" class="form-control" name="subject" id="subject">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Message') ?></label>
                                <textarea name="text" class="summernote" id="contents" title="Contents"></textarea>
                            </div>
                        </div>

                        <input type="hidden" class="form-control" id="cid" name="tid" value="<?php echo $details['id'] ?>">
                        <input type="hidden" id="action-url" value="communication/send_general">


                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendNow">Send</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <h2 class="card-title">Supplier Account
                        : <?php echo $details['name'] ?>
                    </h2>
                </div>
                <div class="col-md-6">
                    <!-- <div class="col-md-8">
                        <div class="input-group">

                            <input type="text" class="form-control" name="cst" id="customer-box" placeholder="Enter Customer Code" autocomplete="off" />
                            <span class="input-group-btn">
                                <button class="btn btn-primary" id="search-client-button" type="button">Search</button>
                                <input type="hidden" name="payer_id" id="customer_id" value="0">
                            </span>
                        </div>
                    </div> -->
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
                                <?php if ($this->aauth->permission_new(null, 'suppliersViewDetails')) { ?>
                                <button type="button" data-action="view-details" data-supplier-id="<?= $details['id'] ?>" class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1 supplier-nav-btn active"><i class="fa fa-user"></i> View Details</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'suppliersViewPO')) { ?>
                                <button type="button" data-action="view-po" data-supplier-id="<?= $details['id'] ?>" class="btn btn-dark btn-md mr-1 mb-1 btn-block btn-lighten-1 supplier-nav-btn"><i class="fa fa-file-text"></i> View PO</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'suppliersViewActivity')) { ?>
                                <button type="button" data-action="view-activity" data-supplier-id="<?= $details['id'] ?>" class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block btn-lighten-1 supplier-nav-btn"><i class="fa fa-money"></i> View Activity</button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'suppliersAccountStatements')) { ?>
                                <button type="button" data-action="account-statements" data-supplier-id="<?= $details['id'] ?>" class="btn btn-danger btn-block btn-md mr-1 mb-1 btn-lighten-1 supplier-nav-btn"><i class="fa fa-briefcase"></i> <?php echo $this->lang->line('Account Statements') ?></button>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'suppliersPrintVATReport')) { ?>
                                <a href="<?php echo base_url('supplier/vatreport?id=' . $details['id']) ?>" class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-print"></i> Print VAT Report</a>
                                <?php } ?>
                            </div>
                        </div>


                    </div>
                    <div class="col-md-10">
                        <div id="mybutton">

                            <div class="">
                                <?php if ($this->aauth->permission_new(null, 'suppliersSendMessage')) { ?>
                                <a href="#sendMail" data-toggle="modal" data-remote="false" class="btn btn-primary btn-md  " data-type="reminder"><i class="icon-envelope"></i> <?php echo $this->lang->line('Send Message') ?>
                                </a>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'suppliersBulkPayment')) { ?>
                                <a href="<?php echo base_url('supplier/bulkpayment?id=' . $details['id']) ?>" class="btn btn-grey-blue btn-md"><i class="fa fa-money"></i> <?php echo $this->lang->line('Bulk Payment') ?>
                                </a>
                                <?php } ?>
                                
                                <?php if ($this->aauth->permission_new(null, 'suppliersEditProfile')) { ?>
                                <a href="<?php echo base_url('supplier/edit?id=' . $details['id']) ?>" class="btn btn-warning btn-md"><i class="icon-pencil"></i> <?php echo $this->lang->line('Edit Profile') ?>
                                </a>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- AJAX Content Area -->
                        <div id="supplier-content-area">
                            <!-- Content will be loaded here via AJAX -->
                        </div>

                    </div>
                </div>


            </div>


            <!-- <div class="col-md-12"><br>
                                <h5><?php echo $this->lang->line('Change Customer Picture') ?></h5><input
                                        id="fileupload"
                                        type="file"
                                        name="files[]"></div> -->


        </div>
    </div>
</div>


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
    document.title = 'Supplier Details';
    $(function() {
        $('.summernote').summernote({
            height: 200,
            placeholder: 'Type your message here...',
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
            ],
            callbacks: {
                onInit: function() {
                    console.log('Summernote initialized');
                }
            }
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

    // Handle Send Message functionality
    $("#sendNow").click(function() {
        // Get form data
        var mailtoc = $('input[name="mailtoc"]').val();
        var customername = $('input[name="customername"]').val();
        var subject = $('input[name="subject"]').val();
        var message = $('#contents').summernote('code');
        
        // Basic validation
        if (!mailtoc || !subject || !message || message.trim() === '') {
            showNotify('Please fill in all required fields.', 'error');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: '<?= base_url("communication/send_general") ?>',
            type: 'POST',
            data: {
                mailtoc: mailtoc,
                customername: customername,
                subject: subject,
                text: message,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                // Reset button
                $("#sendNow").prop('disabled', false).html('Send');
                
                console.log('Email response:', response);
                
                if (response && response.status === 'Success') {
                    // Show success message using flash notification
                    showNotify(response.message || 'Email sent successfully!', 'success');
                    // Close modal
                    $('#sendMail').modal('hide');
                    // Reset form
                    $("#sendmail_form")[0].reset();
                    $('#contents').summernote('reset');
                } else {
                    // Show error message using flash notification
                    var errorMsg = response && response.message ? response.message : 'Unknown error occurred';
                    showNotify(errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Reset button
                $("#sendNow").prop('disabled', false).html('Send');
                
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                var errorMsg = 'Network error occurred. ';
                if (xhr.status === 500) {
                    errorMsg += 'Server error. Please check your SMTP configuration.';
                } else if (xhr.status === 404) {
                    errorMsg += 'Service not found.';
                } else {
                    errorMsg += 'Please try again.';
                }
                
                showNotify(errorMsg, 'error');
            }
        });
    });

    // Function to show flash notifications
    function showNotify(message, type) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        var notifyHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="fa ' + icon + '"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        
        $('#notify').html(notifyHtml).show();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('#notify').fadeOut();
        }, 5000);
    }

    // AJAX Navigation for Supplier Card
    $(document).ready(function() {
        // Load initial content (View Details) - only if user has permission
        <?php if ($this->aauth->permission_new(null, 'suppliersViewDetails')) { ?>
        loadSupplierContent('view-details', <?= $details['id'] ?>);
        <?php } else { ?>
        $('#supplier-content-area').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> You do not have permission to view supplier details.</div>');
        <?php } ?>

        // Handle navigation button clicks
        $('.supplier-nav-btn').on('click', function() {
            var action = $(this).data('action');
            var supplierId = $(this).data('supplier-id');

            // Update active button
            $('.supplier-nav-btn').removeClass('active');
            $(this).addClass('active');

            // Load content
            loadSupplierContent(action, supplierId);
        });
    });

    function loadSupplierContent(action, supplierId) {
        // Check permissions before loading content
        var hasPermission = false;
        switch (action) {
            case 'view-details':
                hasPermission = <?= $this->aauth->permission_new(null, 'suppliersViewDetails') ? 'true' : 'false' ?>;
                break;
            case 'view-po':
                hasPermission = <?= $this->aauth->permission_new(null, 'suppliersViewPO') ? 'true' : 'false' ?>;
                break;
            case 'view-activity':
                hasPermission = <?= $this->aauth->permission_new(null, 'suppliersViewActivity') ? 'true' : 'false' ?>;
                break;
            case 'account-statements':
                hasPermission = <?= $this->aauth->permission_new(null, 'suppliersAccountStatements') ? 'true' : 'false' ?>;
                break;
        }

        if (!hasPermission) {
            $('#supplier-content-area').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> You do not have permission to access this content.</div>');
            return;
        }

        // Show loading indicator
        $('#supplier-content-area').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

        var url = '';
        switch (action) {
            case 'view-details':
                url = '<?= base_url("supplier/ajax_view_details") ?>';
                break;
            case 'view-po':
                url = '<?= base_url("supplier/ajax_invoices") ?>';
                break;
            case 'view-activity':
                url = '<?= base_url("supplier/ajax_transactions") ?>';
                break;
            case 'account-statements':
                url = '<?= base_url("supplier/ajax_statement") ?>';
                break;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                id: supplierId,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            success: function(response) {
                $('#supplier-content-area').html(response);
            },
            error: function(xhr, status, error) {
                $('#supplier-content-area').html('<div class="alert alert-danger">Error loading content. Please try again.</div>');
                console.error('AJAX Error:', error);
            }
        });
    }
</script>