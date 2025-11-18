<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.css" />
<style>
    table.table-bordered.dataTable td:nth-child(6) {
        min-width: 80px;
    }

    table.table-bordered.dataTable td:nth-child(4) {
        min-width: 145px;
    }

    table.table-bordered.dataTable td:last-child {
        min-width: 120px;
    }

    .bootstrap-tagsinput .tag {
        background-color: #2dcee3;
        border-radius: 4px;
        padding: 2px 8px;
        margin: 2px;
        display: inline-block;
    }

    .bootstrap-tagsinput {
        width: 100%;
        padding: 6px;
    }
</style>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> <?= $this->settings->auto_post() ? 'Manage Invoices' : 'Posted Invoices' ?> </h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
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
                <div class="row">
                    <div class="col-md-12 mb-2">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">Invoice Date Between:</div>
                    <div class="col-md-2">
                        <input type="hidden" name="expression" id="expression" />
                        <input type="hidden" name="expression2" id="expression2" />
                        <input type="hidden" name="joinQuery" id="joinQuery" />
                        <input type="hidden" name="value" id="value" />
                        <input type="hidden" name="value2" id="value2" />

                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" />
                    </div>
                    <!-- <div class="col-md-3">
                    <input type="checkbox" id="post_invoices" > Show Only Unposted Invoices</div>
                    -->
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm" />

                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-2">Selected Invoices(<?= currency($this->aauth->get_user()->loc); ?>):</div>
                    <div class="col-md-2">
                        <input type="number" step="any" name="total_selected_amount" value="0" readonly id="total_selected_amount" class=" form-control form-control-sm" autocomplete="off" />
                    </div>
                    <div class="col-md-8">
                        <?php if ($this->aauth->permission_new(null, 'salesPrintInvoices')) { ?>
                        <select id="print_type" class="form-control form-control-sm" style="width:auto; display:inline-block;">
                            <option value="sale">Sale</option>
                            <option value="counter">Counter</option>
                            <option value="quote">Quote</option>
                        </select>
                        <input type="button" name="break_down_report" id="break_down_report" value="Print Selected" class="btn btn-success btn-sm" />
                        <?php } ?>

                        <!-- <input type="button" name="break_down_report" id="break_down_report" value="Print Selected" class="btn btn-success btn-sm" /> -->
                        <span class="d-inline-block ml-2"> Advanced Search: </span>
                        <button type="button" class="btn btn-info btn-sm ml-1" data-toggle="modal" data-target="#advanced_search_modal">
                            Advanced Search
                        </button>
                        <button type="button" class="btn btn-info btn-sm clear-filter">
                            Clear Filter
                        </button>
                    </div>
                    <br>
                    <br>

                    <table id="invoices" class="table table-striped table-bordered zero-configuration" style="width: 100%; table-layout: auto;">

                        <thead>
                            <tr>
                                <th style="width: 4%"><?php echo $this->lang->line('No') ?></th>
                                <th style="width: 8%">Inv #</th>
                                <th style="width: 8%"><?php echo 'A/C'; ?></th>
                                <th style="width: 17%"><?php echo 'Name'; ?></th>
                                <th style="width: 8%"><?php echo 'Type'; ?></th>
                                <th style="width: 10%"><?php echo 'Invoice Date'; ?></th>
                                <th style="width: 10%"><?php echo $this->lang->line('Tax') ?></th>
                                <th style="width: 10%">Net <?php echo $this->lang->line('Amount') ?></th>
                                <th style="width: 10%"><?php echo $this->lang->line('Total') ?></th>
                                <th style="width: 6%"><?php echo 'Status'; ?></th>
                                <th style="width: 6%">Print</th>
                                <th style="width: 6%"><input type="checkbox" id="checkAll"> All</th>
                                <th class="no-sort" style="width: 7%"><?php echo $this->lang->line('Settings') ?></th>
                            </tr>
                        </thead>

                        <tbody id="invoices_body">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="MyInvoices" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
        </div>
    </div>

    <div id="advanced_search_modal" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 bg-info text-white">
                    <h4 class="modal-title">Advanced Search</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body filter-container">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-6 py-1">
                                <p class="m-0">Input Invoice Numbers:</p>
                                <input class="w-100" id="invoiceNumbers" type="text" data-role="tagsinput" />
                            </div>
                            <div class="col-6 py-1">
                                <p class="m-0">Driver Name:</p>
                                <input class="w-100 form-control" id="driverName" type="text" style="padding: 8px;" />
                            </div>
                        </div>
                        <p class="mb-1">OR, Apply your filters below</p>
                        <div class="row">
                            <div class="col-2 bg-info text-white py-1">
                                Join
                            </div>
                            <div class="col-4 bg-info text-white py-1">
                                Field
                            </div>
                            <div class="col-3 bg-info text-white py-1">
                                Condition
                            </div>
                            <div class="col-3 bg-info text-white py-1">
                                Value
                            </div>
                            <div class="col-2 py-1">
                                Where
                            </div>
                            <div class="col-4 py-1">
                                Invoice Number
                            </div>
                            <div class="col-3 py-1">
                                <select class="w-100 condition_1" id="condition_1" name="condition_1">
                                    <option value="=">Is Equal To</option>
                                    <option value=">">Is Greater Than</option>
                                    <option value="<">Is Less Than</option>
                                    <option value=">=">Is Greater Than or Equal To</option>
                                    <option value="<=">Is Less Than or Equal To</option>
                                </select>
                            </div>
                            <div class="col-3 py-1">
                                <input type="number" name="value_1" value="0" id="value_1" class=" form-control" autocomplete="off" />
                            </div>
                            <div class="col-2 py-1">
                                <select class="w-100 join" name="">
                                    <option value="or">Or</option>
                                    <option value="and">And</option>
                                    <option value="between">Between</option>
                                </select>
                            </div>
                            <div class="col-4 py-1">
                                Invoice Number
                            </div>
                            <div class="col-3 py-1">
                                <select class="w-100 condition_2" id="condition_2" name="condition_2">
                                    <option value="=">Is Equal To</option>
                                    <option value=">">Is Greater Than</option>
                                    <option value="<">Is Less Than</option>
                                    <option value=">=">Is Greater Than or Equal To</option>
                                    <option value="<=">Is Less Than or Equal To</option>
                                </select>
                            </div>
                            <div class="col-3 py-1">
                                <input type="number" name="value_2" value="0" id="value_2" class=" form-control" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 ">
                    <div class="col-8">
                        <strong>Expression:</strong>
                        <div class="generated-expression"></div>
                    </div>
                    <div class="col-4 text-right">
                        <button type="button" data-dismiss="modal" class="btn btn-info mr-1 apply-filters"
                            id="apply-filters">Apply</button>
                        <button type="button" data-dismiss="modal"
                            class="btn cancel-expression"><?php echo $this->lang->line('Cancel') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareModalLabel">Share Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Share this link:</strong></p>
                    <input type="text" id="copy-link" class="form-control" readonly>
                    <button onclick="copyToClipboard()" class="btn btn-primary mt-2">Copy Link</button>
                    <hr>
                    <p>Share via:</p>
                    <a id="whatsapp-share" href="#" target="_blank" class="btn btn-success">WhatsApp</a>
                    <!-- <a id="email-share" href="#" class="btn btn-info">Email</a> -->
                </div>
            </div>
        </div>
    </div>

    <form method="POST" id="form_for_export" action="<?php echo site_url('invoices/print_merged') ?>" target="_blank">
        <input type="hidden" id="hidden_fields" name="hidden_fields" value="" />
        <input type="hidden" id="print_type_hidden" name="print_type" value="" />
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    </form>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.js"></script>
    <script type="text/javascript">
        <?php if ($this->settings->auto_post()) { ?>
        document.title = 'Manage Invoices';
        <?php } else { ?>
        document.title = 'Posted Invoices';
        <?php } ?>
        $("body").on("dblclick", "tr", function() {
            let id = $(this).find('span[data-id]').data("id");
            let link = $(this).find('span[data-link]').data("link");
            if (link) {
                window.location.href = link;
            } else {
                console.error("Invoice link not found for id:", id);
            }
        });

        $('#break_down_report').click(function() {
            var fields = [];

            $("input:checkbox[name=receipt]:checked").each(function() {
                fields.push($(this).val());
            });
            if (fields == "") {
                return;
            }
            $('#hidden_fields').val(fields);
            //url="<?php echo site_url('invoices/daily_break_down_sheet_export?ids=') ?>"+fields;

            $('#form_for_export').submit();
            // window.open(url, '_blank');

            //   window.location.href = baseurl + "pos_invoices/extended";

        });

        $(document).ready(function() {});

        $(document).ready(function() {
            // Keep end_date always enabled
            var $endDate = $('#end_date');
            var $startDate = $('#start_date');
            
            function enableEndDate() {
                if ($endDate.length) {
                    $endDate.prop('disabled', false).css('opacity', '1');
                    $endDate.removeAttr('disabled');
                    // Ensure it's type="date"
                    $endDate.attr('type', 'date');
                }
            }
            
            // Enable immediately
            enableEndDate();
            
            // Watch for start_date changes and enable end_date
            $startDate.on('change input', function() {
                enableEndDate();
            });
            
            // Also listen to native date input events
            var startDateElement = document.getElementById('start_date');
            if (startDateElement) {
                startDateElement.addEventListener('change', enableEndDate);
                startDateElement.addEventListener('input', enableEndDate);
            }
            
            // Use MutationObserver to watch for disabled attribute changes
            if (typeof MutationObserver !== 'undefined' && $endDate.length) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && 
                            (mutation.attributeName === 'disabled' || mutation.attributeName === 'style')) {
                            enableEndDate();
                        }
                    });
                });
                
                observer.observe($endDate[0], {
                    attributes: true,
                    attributeFilter: ['disabled', 'style']
                });
            }
            
            // Periodic check as fallback
            setInterval(enableEndDate, 200);
            
            // Re-enable after delays to catch any late initialization
            setTimeout(enableEndDate, 500);
            setTimeout(enableEndDate, 1000);
            setTimeout(enableEndDate, 2000);
            
            // Persist selected rows across redraws
            window.selectedInvoiceIds = window.selectedInvoiceIds || new Set();
            window.isBulkSelecting = false;
            // On page load, initialize hidden input value
            $('#print_type_hidden').val($('#print_type').val());

            // When user changes print_type, update hidden input instantly
            $('#print_type').on('change', function() {
                $('#print_type_hidden').val($(this).val());
            });

            $('#break_down_report').click(function() {
                Swal.showLoading();

                var fields = [];
                $("input:checkbox[name=receipt]:checked").each(function() {
                    fields.push($(this).val());
                });

                if (fields.length === 0) {
                    Swal.close();
                    Swal.fire('No invoices selected', 'Please select at least one item.', 'warning');
                    return;
                }

                // Set hidden fields for invoices
                $('#hidden_fields').val(fields.join(','));

                // Debug: check values before submit (optional)
                // console.log("Hidden fields:", $('#hidden_fields').val());
                // console.log("Print type hidden:", $('#print_type_hidden').val());

                // Submit the form with slight delay to ensure inputs are updated
                setTimeout(function() {
                    $('#form_for_export').submit();
                    Swal.close();
                }, 100);
            });
        });


        $(document).ready(function() {

            // Function to find the indexes of checked checkboxes and log them
            function findCheckedIndexes() {
                // Select all checkboxes that are checked within the <tr> elements
                var checkedCheckboxes = $('tr input[type="checkbox"]:checked');

                // Get the index of each checked checkbox's parent <tr> element
                var indexes = $('tr input[type="checkbox"]:checked').map(function() {
                    return $(this).closest('tr').index();
                }).get();

                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                draw_data(start_date, end_date, $("#expression").val(), indexes);

            }

            // Use event delegation to bind the change event listener to checkboxes
            //   $("body").on('change', 'tr input[name="receipt"]', findCheckedIndexes);


            function generateExp() {
                let cond_1 = $("#condition_1").val();
                let cond_2 = $("#condition_2").val();
                let join_val = $("select.join").val();
                let val_1 = $("input#value_1").val();
                let val_2 = $("input#value_2").val();
                join_val = (join_val == "between") ? " and " : join_val;
                let generated = "Where 'Invoice number' " + cond_1 + " " + val_1 + " " + join_val + " 'Invoice number' " + cond_2 + " " + val_2;
                let generated_exp = "'tid' " + cond_1 + " " + val_1 + " " + join_val + " 'tid' " + cond_2 + " " + val_2;

                let invoices = $("#invoiceNumbers").tagsinput('items');
                if (invoices.length > 0) {
                    generated = "Where 'Invoice number' IN (" + $("#invoiceNumbers").val() + ")";
                    // Don't disable date fields - they should always be editable
                    // $("#end_date").attr("disabled", true);
                    // $("#start_date").attr("disabled", true);
                    $("#condition_1").attr("disabled", true);
                    $("#condition_2").attr("disabled", true);
                    $("select.join").attr("disabled", true);
                    $("input#value_1").attr("disabled", true);
                    $("input#value_2").attr("disabled", true);
                } else {
                    $("#condition_1").attr("disabled", false);
                    $("#condition_2").attr("disabled", false);
                    $("select.join").attr("disabled", false);
                    $("input#value_1").attr("disabled", false);
                    $("input#value_2").attr("disabled", false);
                }

                $(".generated-expression").text(generated);
                $("#expression").val("tid " + cond_1);
                $("#expression2").val("tid " + cond_2);
                $("#value").val(val_1);
                $("#value2").val(val_2);
                $("#joinQuery").val(join_val);
                // Don't disable date fields - they should always be editable
                // if (generated != "") {
                //     $("#end_date").attr("disabled", true);
                //     $("#start_date").attr("disabled", true);
                // }
            }
            $(".filter-container input, .filter-container select").change(generateExp);
            $("#invoiceNumbers").change(generateExp);

            $('#break_down_report').click(function() {
                Swal.showLoading();

                var fields = [];
                var invoiceFormat = <?php echo $invoice_format; ?>; // Dynamically set invoice_format from PHP

                $("input:checkbox[name=receipt]:checked").each(function() {
                    fields.push($(this).val());
                });


                fields.forEach((id, index) => {
                    const url = '<?php echo site_url('invoices/printinvoice') ?>' +
                        (invoiceFormat == 1 ? '' : invoiceFormat) +
                        '?id=' + id + '&d=1';

                    $.ajax({
                        url: url,
                        type: 'GET', // You can use 'POST', 'PUT', 'DELETE', etc. depending on your server's API
                        success: function(data) {
                            if (index === fields.length - 1) {
                                Swal.fire(
                                    'Success!',
                                    'Your files have been printed.',
                                    'success'
                                )
                                $('#form_for_export').submit();
                            }
                        },
                        error: function(xhr, status, error) {
                            // This function is called if the AJAX request fails
                            console.error('Error:', error);
                        }
                    });
                });

                if (fields == "") {
                    return;
                }
                $('#hidden_fields').val(fields);
                //url="<?php echo site_url('invoices/daily_break_down_sheet_export?ids=') ?>"+fields;

                // $('#form_for_export').submit();
                // window.open(url, '_blank');

                //   window.location.href = baseurl + "pos_invoices/extended";

            });

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            draw_data(start_date, end_date, $("#expression").val());

            $('#post_invoices_button').click(function() {
                var table = $('#invoices').DataTable();
                table.rows().nodes().each(function(row) {
                    if ($(row).find('td a.is_posted').length) {
                        $(row).show();
                    } else {
                        $(row).remove();
                    }
                    $('.cancel-bulk-delete').css('display', 'inline-block');
                    $('.post_invoices').css('display', 'inline-block');
                    document.getElementById("hidebutton").style.display = "none";
                });
            });

            function slected_indexes() {
                var checkedCheckboxes = $('tr input[type="checkbox"]:checked');

                // Get the index of each checked checkbox's parent <tr> element
                var indexes = checkedCheckboxes.map(function() {
                    return $(this).closest('tr').index();
                }).get();

                return (indexes);
            }

            function draw_data(start_date = '', end_date = '', param_override = '', due_date_set = '', expression = '', rows = []) {
                $('#invoices').DataTable().destroy();
                console.log('After Due Date:', due_date_set); // Check the value of due_date
                // Set param: empty if due_date is not empty, otherwise follow param_override or default
                var param = due_date_set ? 'date_override' : (param_override === 'date_override' ? param_override : '<?= isset($param) ? $param : "date_override" ?>');

                // Set due_date: if due_date_set is 'clear', set to empty string, otherwise use PHP $due_date
                var due_date = (due_date_set === 'clear') ? '' : '<?= isset($due_date) ? $due_date : '' ?>';

                console.log('Using Due Date:', due_date);

                console.log('Param:', param);
                var tables = $('#invoices').DataTable({
                    'processing': true,
                    'serverSide': false,
                    'stateSave': true,
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
                            "bSortable": true
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
                            "bSortable": true
                        },
                        {
                            "aTargets": [8],
                            "bSortable": true
                        },
                        {
                            "aTargets": [9],
                            "bSortable": true
                        }


                    ],


                    responsive: false,
                    'serverSide': true,
                    'processing': true,
                    'pageLength': 25,
                    bInfo: true,

                    <?php datatable_lang(); ?>

                    // responsive: false,

                    'order': [],
                    'ajax': {
                        'url': "<?= site_url('invoices/ajax_list') ?>",
                        'type': 'POST',


                        'data': function(d) {
                            console.log('Data sent to server:', {
                                start_date: start_date,
                                end_date: end_date,
                                param: param,
                                due_date: due_date,
                                expression: $("#expression").val(),
                            });
                            // Merge DataTables paging params (d) with custom filters
                            return $.extend({}, d, {
                                '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                                start_date: start_date,
                                end_date: end_date,
                                param: param,
                                due_date: due_date,
                                expression: $("#expression").val(),
                                expression2: $("#expression2").val(),
                                value: $("#value").val(),
                                value2: $("#value2").val(),
                                joinQuery: $("#joinQuery").val(),
                                invoices: $("#invoiceNumbers").tagsinput('items'),
                                driver: $("#driverName").val()
                            });
                        }
                    },
                    'columnDefs': [{
                        'targets': [0, 14],
                        'orderable': false,
                    }],
                    dom: 'Blfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        footer: true,
                        exportOptions: {
                            columns: [5, 1, 2, 3, 4, 6, 7, 8, 9],
                            // columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                            rows: (rows.length == 0) ? undefined : rows,
                        }
                    }],
                    "search": {
                        "caseInsensitive": false
                    },
                    aLengthMenu: [
                        [-1, 25, 50, 100, 200],
                        ["All", 25, 50, 100, 200]
                    ],
                    "fnCreatedRow": function(nRow, aData, iDataIndex) {
                        $(nRow).attr('id', aData[0]);
                    }


                });

                var table = $('#invoices').DataTable();

                // Reapply persisted selections on each draw
                function reapplySelectionsAndTotals() {
                    var totalAmount = 0;
                    var allVisibleSelected = true;

                    $('#invoices tbody tr').each(function() {
                        var $row = $(this);
                        var $checkbox = $row.find('input[name="receipt"]');
                        var id = $checkbox.val();
                        var rowData = table.row(this).data() || [];
                        var amount = parseFloat((rowData[8] || '').toString().replace(/[^\d.-]/g, '')) || 0;

                        if (id && window.selectedInvoiceIds.has(id)) {
                            $row.addClass('selected');
                            $checkbox.prop('checked', true);
                            totalAmount += amount;
                        } else {
                            $row.removeClass('selected');
                            $checkbox.prop('checked', false);
                            allVisibleSelected = false;
                        }
                    });

                    $('#total_selected_amount').val(totalAmount.toFixed(2));
                    // Sync header checkAll
                    $('#checkAll').prop('checked', allVisibleSelected && $('#invoices tbody tr').length > 0);
                }

                table.on('draw.dt', function() {
                    reapplySelectionsAndTotals();
                });

                // Cancel AJAX draws while bulk selecting
                table.on('preXhr.dt', function(e, settings, data) {
                    if (window.isBulkSelecting) {
                        return false; // cancel the request
                    }
                });

                // Prevent row double-toggle when clicking directly on checkbox,
                // and keep selection/totals/header synced
                $('#invoices tbody').off('click.receiptCb', 'input[name="receipt"]').on('click.receiptCb', 'input[name="receipt"]', function(e) {
                    e.stopPropagation();
                    var $cb = $(this);
                    var $row = $cb.closest('tr');
                    var id = $cb.val();
                    if ($cb.is(':checked')) {
                        $row.addClass('selected');
                        if (id) window.selectedInvoiceIds.add(id);
                    } else {
                        $row.removeClass('selected');
                        if (id) window.selectedInvoiceIds.delete(id);
                    }
                    reapplySelectionsAndTotals();
                });

                $('.dataTables_filter input[type="search"]').unbind().keyup(function(e) {
                    var value = $(this).val().toUpperCase();
                    table.search(value).draw();
                });


                $('#showUnpostedInvoicesBtn').on('click', function() {



                    var start_date = $('#start_date').val();
                    var end_date = $('#end_date').val();

                    $.ajax({
                        url: 'invoices/ajax_list',
                        type: 'GET',
                        data: {

                            start_date: start_date,
                            end_date: end_date
                        },

                        success: function(data) {

                        },
                        error: function() {


                        }
                    });




                });



                $('.cancel-bulk-delete').click(function() {
                    $(this).css('display', 'none');
                    $('.post_invoices_button2').attr("data-mode", "select");
                    $(".post_invoices_button").text("POST Invoices");
                    var start_date = $('#start_date').val();
                    var end_date = $('#end_date').val();
                    draw_data(start_date, end_date, $("#expression").val());
                })

                $('.post_invoices').click(function() {

                    // alert($(this).data('object-id'));
                    var fields = [];
                    var objectId = $(this).data('object-id');

                    fields.push(objectId);

                    $("input:checkbox[name=receipt]:checked").each(function() {
                        fields.push($(this).val());
                    });
                    if (fields.length < 1) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning',
                            text: 'Please select atleast one Invoice to Post',
                        })
                    } else {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, Post it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.showLoading();
                                $.ajax({
                                    type: "get",
                                    url: "<?php echo site_url('invoices/post_invoices') ?>",
                                    data: {

                                        ids: fields
                                    },
                                    success: function(data) {
                                        Swal.fire(
                                            'Posted!',
                                            fields,
                                            'success'
                                        );
                                        var start_date = $('#start_date').val();
                                        var end_date = $('#end_date').val();
                                        draw_data(start_date, end_date, $("#expression").val());
                                        $('.bulk-delete').attr("data-mode", "select");
                                        $(".bulk-delete-text").text("POST Invoices");
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops...',
                                            text: 'Something went wrong!',
                                        })
                                    }
                                });
                            }
                        })
                    }

                });

                $('body').on('click', '.delete-object', function() {

                    var objectId = $(this).data('object-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.showLoading();
                            $.ajax({
                                type: "get",
                                url: "<?php echo site_url('invoices/delete_invoices') ?>",
                                // url: "https://starfoods.cloudbillingmanager.com/invoices/delete_invoices",
                                data: {

                                    ids: objectId
                                },
                                success: function(data) {
                                    Swal.fire(
                                        'Deleted!',
                                        objectId,
                                        'success'
                                    );
                                    $('#invoices').DataTable().destroy();
                                    var start_date = $('#start_date').val();
                                    var end_date = $('#end_date').val();
                                    draw_data(start_date, end_date);

                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!',
                                    })
                                }
                            });
                        }
                    })


                });

                $('body').on('click', '.duplicate_inv', function() {

                    var objectId = $(this).data('object-id');


                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want Duplicate This Invoice!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Duplicate it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.showLoading();
                            $.ajax({
                                type: "get",
                                url: "<?php echo site_url('invoices/duplicate_invoices') ?>",
                                data: {

                                    ids: objectId
                                },
                                success: function(data) {
                                    Swal.fire(
                                        'Duplicated!',
                                        'Invoice has successsfully been duplicated!',
                                        'success'
                                    );
                                    // $('#invoices').DataTable().destroy();
                                    // var start_date = $('#start_date').val();
                                    // var end_date = $('#end_date').val(); 
                                    // draw_data(start_date, end_date);
                                    // $('.bulk-delete').attr("data-mode","select");
                                    <?php if ($this->settings->auto_post()) ?>
                                    location.reload();
                                    <?php ?>

                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!',
                                    })
                                }
                            });
                        }
                    })


                });



                // $('#invoices tbody').off('click', 'tr').on('click', 'tr', function() {

                //     var data = tables.row(this).data();
                //     $(':checkbox', data[12]).trigger('click');
                //     if ($(this).hasClass('selected')) {
                //         var col_amt = parseFloat(data[8].replace(/[^\d.-]/g, ''));
                //         var pre_amt = parseFloat($('#total_selected_amount').val());
                //         var value = pre_amt - col_amt;
                //         $('#total_selected_amount').val(value.toFixed(2));
                //         var col_wt = parseFloat(data[9].replace(/[^\d.-]/g, ''));
                //         var pre_wt = parseFloat($('#total_selected_weight').val());
                //         var value = pre_wt - col_wt;
                //         $('#total_selected_weight').val(value.toFixed(2));
                //         $(this).removeClass('selected');
                //         var $chk = $(this).find('input[type=checkbox]');
                //         $chk.prop('checked', false);
                //     } else {
                //         var $chk = $(this).find('input[type=checkbox]');
                //         var col_amt = parseFloat(data[8].replace(/[^\d.-]/g, ''));
                //         var pre_amt = parseFloat($('#total_selected_amount').val());
                //         var value = pre_amt + col_amt;
                //         $('#total_selected_amount').val(value.toFixed(2));
                //         var col_wt = parseFloat(data[9].replace(/[^\d.-]/g, ''));
                //         var pre_wt = parseFloat($('#total_selected_weight').val());
                //         var value = pre_wt + col_wt;
                //         $('#total_selected_weight').val(value.toFixed(2));
                //         $chk.prop('checked', true);
                //         $(this).addClass('selected');
                //     }

                // });

                $('#invoices tbody').off('click', 'tr').on('click', 'tr', function() {

                    var data = tables.row(this).data();
                    $(':checkbox', data[12]).trigger('click');

                    // Safely parse amounts and default to 0 if invalid
                    var col_amt = parseFloat(data[8]?.replace(/[^\d.-]/g, '')) || 0;
                    var col_wt = parseFloat(data[9]?.replace(/[^\d.-]/g, '')) || 0;

                    var pre_amt = parseFloat($('#total_selected_amount').val()) || 0;
                    var pre_wt = parseFloat($('#total_selected_weight').val()) || 0;

                    if ($(this).hasClass('selected')) {
                        $('#total_selected_amount').val((pre_amt - col_amt).toFixed(2));
                        $('#total_selected_weight').val((pre_wt - col_wt).toFixed(2));
                        $(this).removeClass('selected');
                        var $cb = $(this).find('input[type=checkbox]');
                        $cb.prop('checked', false);
                        if ($cb.attr('name') === 'receipt') {
                            var id = $cb.val();
                            if (id) window.selectedInvoiceIds.delete(id);
                        }
                    } else {
                        $('#total_selected_amount').val((pre_amt + col_amt).toFixed(2));
                        $('#total_selected_weight').val((pre_wt + col_wt).toFixed(2));
                        var $cb = $(this).find('input[type=checkbox]');
                        $cb.prop('checked', true);
                        if ($cb.attr('name') === 'receipt') {
                            var id = $cb.val();
                            if (id) window.selectedInvoiceIds.add(id);
                        }
                        $(this).addClass('selected');
                    }
                    // Update header checkAll
                    var allVisibleSelected = $('#invoices tbody tr').length > 0 && $('#invoices tbody tr').toArray().every(function(tr){ return $(tr).hasClass('selected'); });
                    $('#checkAll').prop('checked', allVisibleSelected);
                });
            }

            function clearFilter() {
                $("#condition_1").attr("disabled", false)
                $("#condition_2").attr("disabled", false)
                $("#start_date").attr("disabled", false)
                // Ensure date fields are always enabled
                $("#start_date").prop("disabled", false).removeAttr("disabled")
                $("#end_date").prop("disabled", false).removeAttr("disabled")
                $("#condition_1").attr("disabled", false)
                $("#condition_2").attr("disabled", false)
                $("select.join").attr("disabled", false)
                $("input#value_1").attr("disabled", false)
                $("input#value_2").attr("disabled", false)
                $("#condition_1").val("=")
                $("#condition_2").val("=")
                $("input#value_1").val(0)
                $("input#value_2").val(0)
                $("#expression").val("");
                $("#expression2").val("");
                $("#value").val(0);
                $("#value2").val(0);
                $("#joinQuery").val("and");
                $("#invoiceNumbers").val("");
                $('#invoiceNumbers').tagsinput('destroy');
                $("#invoiceNumbers").tagsinput({
                    trimValue: true
                });
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var due_date_set = 'clear';
                var param_override = 'date_override';
                // draw_data(start_date, end_date,param_override, due_date_set,"");
                draw_data(start_date, end_date, param_override, due_date_set, $("#expression").val());
                // draw_data(start_date, end_date, "");
            }

            $(".cancel-expression").click(clearFilter);
            $(".clear-filter").click(clearFilter);

            $(".apply-filters").click(function() {
                generateExp();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var expression = $("#expression").val();
                draw_data(start_date, end_date, expression);
            });

            $("select.join").change(function() {
                if ($(this).val() == "between") {
                    $("#condition_1").val(">=")
                    $("#condition_1").attr("disabled", true)
                    $("#condition_2").val("<=")
                    $("#condition_2").attr("disabled", true)
                } else {
                    $("#condition_1").attr("disabled", false)
                    $("#condition_2").attr("disabled", false)
                }
            })



            $("#checkAll").on('change', function() {
                var selectAllChecked = this.checked;
                var table = $('#invoices').DataTable();
                window.isBulkSelecting = true;

                // Do not trigger row click handlers to avoid side effects/redraws
                // Toggle selection state and checkboxes directly
                var totalAmount = 0;

                $('#invoices tbody tr').each(function() {
                    var $row = $(this);
                    var $checkbox = $row.find('input[name="receipt"]');
                    var id = $checkbox.val();
                    var rowData = table.row(this).data() || [];
                    var amount = parseFloat((rowData[8] || '').toString().replace(/[^\d.-]/g, '')) || 0;

                    if (selectAllChecked) {
                        $row.addClass('selected');
                        $checkbox.prop('checked', true);
                        if (id) window.selectedInvoiceIds.add(id);
                        totalAmount += amount;
                    } else {
                        $row.removeClass('selected');
                        $checkbox.prop('checked', false);
                        if (id) window.selectedInvoiceIds.delete(id);
                    }
                });

                // Update totals
                if (selectAllChecked) {
                    $('#total_selected_amount').val(totalAmount.toFixed(2));
                } else {
                    $('#total_selected_amount').val((0).toFixed(2));
                }

                // Re-enable XHR after synchronous bulk update completes
                // Use a microtask to ensure handlers complete first
                setTimeout(function() { window.isBulkSelecting = false; }, 0);
            });
            // $('#search').click(function() {
            //     var start_date = $('#start_date').val();
            //     var end_date = $('#end_date').val();
            //     if (start_date != '' && end_date != '') {
            //         draw_data(start_date, end_date, $("#expression").val());
            //     } else {
            //         alert("Date range is Required");
            //     }
            // });
            $('#search').click(function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var due_date_set = 'clear';
                var param_override = 'date_override';

                console.log('Due Date:', due_date_set); // Check the value of due_date
                if (start_date !== '' && end_date !== '') {
                    draw_data(start_date, end_date, param_override, due_date_set, $("#expression").val());
                } else {
                    alert("Date range is required.");
                }
            });


            $('#invoices').DataTable().search('').draw();



        });

        function copyToClipboard() {
            const link = $('#copy-link').val();
            if (link) {
                navigator.clipboard.writeText(link).then(() => {
                    alert("Link copied to clipboard!");
                }).catch(err => {
                    console.error('Error copying text: ', err);
                });
            } else {
                alert("No link available to copy.");
            }
        }

        $('#shareModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var link = button.data('link');
            var email = button.data('email');
            var name = button.data('name');
            var csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get the CSRF token

            $('#copy-link').val(link);
            $('#whatsapp-share').attr('href', 'https://wa.me/?text=' + encodeURIComponent(link));

            $('#email-share').off('click').on('click', function() {
                $.ajax({
                    url: '<?= base_url('invoices/sendInvoiceEmail') ?>',
                    method: 'POST',
                    data: {
                        email: email,
                        name: name,
                        subject: 'Invoice',
                        message: 'Please find the invoice link below:\n\n' + link,
                        '<?= $this->security->get_csrf_token_name(); ?>': csrfToken // Add CSRF token
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#email-share').prop('disabled', true).text('Sending...');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Email sent successfully!');
                            $('#shareModal').modal('hide');
                        } else {
                            alert('Failed to send email: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('An error occurred while sending the email.');
                    },
                    complete: function() {
                        $('#email-share').prop('disabled', false).text('Send Email');
                    }
                });
            });
        });
    </script>