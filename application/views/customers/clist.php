<?php
$due = false;
if ($this->input->get('due')) {
    $due = true;
} ?>
</style>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><a href="<?php echo base_url('customers') ?>" class="mr-5">
                    <?php echo $this->lang->line('Clients') ?></a> <a href="<?php echo base_url('customers/create') ?>" class="btn btn-primary btn-sm rounded">
                    <?php echo $this->lang->line('Add new') ?></a>
                <!-- <a href="<?php echo base_url('customers/customersList') ?>" class="btn btn-info btn-sm rounded">
                    Customer Filers</a> -->
            </h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li> <a href="#sendMail" data-toggle="modal" data-remote="false" class="btn btn-info btn-sm rounded" data-lang="<?php echo $this->lang->line('Email Selected') ?>"> <span class="fa fa-envelope"></span>
                            <?php echo $this->lang->line('Email Selected') ?></a></li>
                    <li> <a href="#sendSmsS" data-toggle="modal" data-remote="false" class="btn btn-success btn-sm rounded" data-lang="<?php echo $this->lang->line('SMS Selected') ?>"> <span class="fa fa-mobile"></span>
                            <?php echo $this->lang->line('SMS Selected') ?></a></li>
                    <?php if ($this->aauth->premission(24)) { ?>
                        <li><a id="delete_selected" href="#" class="btn btn-danger btn-sm rounded" data-lang="<?php echo $this->lang->line('Delete Selected') ?>"> <span class="fa fa-trash-o"></span>
                                <?php echo $this->lang->line('Delete Selected') ?></a></li>
                    <?php } ?>

                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
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

                <table id="clientstable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <h3 style="margin-left:850px;"><span>Total Balance <br> <?php echo amountExchange($ttlBalance); ?></span></h3>
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('Select') ?></th>
                            <th><?php echo $this->lang->line('A/C #') ?></th>
                            <?php if ($due) {
                                echo '  <th>' . $this->lang->line('Due') . '</th>';
                            } ?>
                            <th><?php echo $this->lang->line('Customer') ?></th>
                            <th> <?php echo $this->lang->line('Address') ?></th>
                            <th><?php echo $this->lang->line('Balance') ?></th>
                            <th><?php echo $this->lang->line('Phone') ?></th>
                            <th><?php echo $this->lang->line('Last Invoice Date') ?></th>
                            <th><?php echo $this->lang->line('Payment Terms') ?></th>
                            <th><?php echo $this->lang->line('Settings') ?></th>


                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th><?php echo $this->lang->line('Select') ?></th>
                            <th><?php echo $this->lang->line('A/C #') ?></th>
                            <?php if ($due) {
                                echo '  <th>' . $this->lang->line('Due') . '</th>';
                            } ?>
                            <th><?php echo $this->lang->line('Customer') ?></th>
                            <th> <?php echo $this->lang->line('Address') ?></th>
                            <th><?php echo $this->lang->line('Balance') ?></th>
                            <th><?php echo $this->lang->line('Phone') ?></th>
                            <th><?php echo $this->lang->line('Last Invoice Date') ?></th>
                            <th><?php echo $this->lang->line('Payment Terms') ?></th>
                            <th><?php echo $this->lang->line('Settings') ?></th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
</div>

<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">Delete Customer</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('are_you_sure_delete_customer') ?></p>
                <ul>
                    <li>This will Delete Invoices Data</li>
                    <li>This will Delete Transactions Data</li>
                    <li>This will Change Total Balance</li>
                </ul>
            </div>
            <div class="modal-footer">
                <input type="hidden" class="form-control" id="object-id" name="deleteid" value="0">
                <input type="hidden" id="action-url" value="customers/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<div id="sendMail" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Email Selected') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form id="sendmail_form"><input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">



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




                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                <button type="button" class="btn btn-primary" id="sendNowSelected"><?php echo $this->lang->line('Send') ?></button>
            </div>
        </div>
    </div>
</div>
<div id="sendSmsS" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('SMS Selected') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form id="sendsms_form"><input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">



                    <div class="row">
                        <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Message') ?></label>
                            <textarea name="message" class="form-control" rows="3" cols="60"></textarea>
                        </div>
                    </div>


                    <input type="hidden" id="action-url" value="communication/send_general">


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                <button type="button" class="btn btn-primary" id="sendSmsSelected"><?php echo $this->lang->line('Send') ?></button>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    document.title = 'Customers';
</script>
<script type="text/javascript">
    $(document).ready(function() {
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



        $('#clientstable').DataTable({
            'processing': true,
            'serverSide': true,
            'stateSave': false,
            'bSortable': true,
            'bRetrieve': true,

            "bPaginate": true,
            "bFilter": true,
            "bInfo": true,


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
                },
                {
                    "aTargets": [4],
                    "bSortable": true
                },
                {
                    "aTargets": [5],
                    "bSortable": true
                },
                {
                    "aTargets": [6],
                    "bSortable": true
                },
                {
                    "aTargets": [7],
                    "bSortable": false
                },
                {
                    "aTargets": [8],
                    "bSortable": false
                },

            ],


            responsive: true,
            'pageLength': 25,
            bInfo: true,
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                infoFiltered: "(filtered from _MAX_ total records)"
            },
            <?php datatable_lang(); ?> 'order': [],
            'ajax': {
                'url': "<?php echo site_url('customers/load_list') ?>",
                'type': 'POST',
                'data': function(d){ d['<?= $this->security->get_csrf_token_name() ?>']= crsf_hash; <?php if ($due) echo "d['due']=true;" ?> }
            },
            'columnDefs': [{
                'targets': [0],
                'orderable': true,
            }, ],
            dom: 'Blfrtip',
            buttons: [{

                    extend: 'pdfHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7]
                    }
                }
            ],
            aLengthMenu: [
                [ 25, 50, 100, 200, -1],
                [25, 50, 100, 200, "All"]
            ],
            "createdRow": function(row, data, dataIndex) {
                // Assuming column 9 index is 9
                if (data[9] === false) {
                    $(row).css({
                        'background-color': 'rgb(212, 103, 114)',
                    });
                }
            }
        });

        var table = $('#clientstable').DataTable();

        $('.dataTables_filter input[type="search"]').unbind().keyup(function(e) {
            var value = $(this).val().toUpperCase();
            table.search(value).draw();
        });
        $(document).on('click', "#delete_selected", function(e) {
            e.preventDefault();
            
            // Check if any customers are selected
            var selected = $("input[name='cust[]']:checked");
            if (selected.length === 0) {
                alert('Please select at least one customer to delete.');
                return;
            }
            
            // Confirm deletion
            if (!confirm('Are you sure you want to delete ' + selected.length + ' selected customer(s)?')) {
                return;
            }
            
            if ($("#notify").length == 0) {
                $("#c_body").html('<div id="notify" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
            }
            
            jQuery.ajax({
                url: "<?php echo site_url('customers/delete_i') ?>",
                type: 'POST',
                data: selected.serialize() + '&<?= $this->security->get_csrf_token_name() ?>=' + crsf_hash + '<?php if ($due) echo "&due=true" ?>',
                dataType: 'json',
                success: function(data) {
                    if (data && data.status && data.message) {
                        // Remove deleted rows from table
                        selected.closest('tr').remove();
                        
                        // Show notification
                        $("#notify .message").html("<strong>" + data.status + "</strong>: " + data.message);
                        if (data.status === 'Success') {
                            $("#notify").removeClass("alert-danger").addClass("alert-success");
                        } else {
                            $("#notify").removeClass("alert-success").addClass("alert-danger");
                        }
                        $("#notify").fadeIn();
                        
                        // Reload DataTable to refresh data
                        $('#clientstable').DataTable().ajax.reload(null, false);
                        
                        // Scroll to notification
                        $("html, body").animate({
                            scrollTop: $('#notify').offset().top
                        }, 1000);
                    } else {
                        // Invalid response format
                        $("#notify .message").html("<strong>Error</strong>: Invalid response from server.");
                        $("#notify").removeClass("alert-success").addClass("alert-danger").fadeIn();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX errors
                    var errorMsg = 'An error occurred while deleting customers.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch(e) {
                            errorMsg = 'Server error: ' + xhr.status;
                        }
                    }
                    
                    $("#notify .message").html("<strong>Error</strong>: " + errorMsg);
                    $("#notify").removeClass("alert-success").addClass("alert-danger").fadeIn();
                    $("html, body").animate({
                        scrollTop: $('#notify').offset().top
                    }, 1000);
                }
            });
        });


        //uni sender
        $('#sendMail').on('click', '#sendNowSelected', function(e) {
            e.preventDefault();
            $("#sendMail").modal('hide');
            if ($("#notify").length == 0) {
                $("#c_body").html('<div id="notify" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
            }
            jQuery.ajax({
                url: "<?php echo site_url('customers/sendSelected') ?>",
                type: 'POST',
                data: $("input[name='cust[]']:checked").serialize() + '&' + $("#sendmail_form").serialize(),
                dataType: 'json',
                success: function(data) {
                    $("#notify .message").html("<strong>" + data.status + "</strong>: " + data.message);
                    $("#notify").removeClass("alert-danger").addClass("alert-success").fadeIn();
                    $("html, body").animate({
                        scrollTop: $('#notify').offset().top
                    }, 1000);
                }
            });
        });

        $('#sendSmsS').on('click', '#sendSmsSelected', function(e) {
            e.preventDefault();
            $("#sendSmsS").modal('hide');
            if ($("#notify").length == 0) {
                $("#c_body").html('<div id="notify" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
            }
            jQuery.ajax({
                url: "<?php echo site_url('customers/sendSmsSelected') ?>",
                type: 'POST',
                data: $("input[name='cust[]']:checked").serialize() + '&' + $("#sendsms_form").serialize(),
                dataType: 'json',
                success: function(data) {
                    $("#notify .message").html("<strong>" + data.status + "</strong>: " + data.message);
                    $("#notify").removeClass("alert-danger").addClass("alert-success").fadeIn();
                    $("html, body").animate({
                        scrollTop: $('#notify').offset().top
                    }, 1000);
                }
            });
        });


        $('#clientstable').DataTable().search('').draw();
    });
</script>