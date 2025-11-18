<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.css" />
<link href="<?= base_url('assets/css/quotations-shared.css') ?>" rel="stylesheet" />
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Manage Quotations</h4>
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
                        <?php if ($this->aauth->permission_new(null, 'quotationNewQuotation')): ?>
                        <a href="<?= site_url('quotations/newquotation') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> New Quotation
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">Quotation Date Between:</div>
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
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm" />
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-2">Selected Quotations:</div>
                    <div class="col-md-2">
                        <input type="number" step="any" name="total_selected_amount" value="0" readonly id="total_selected_amount" class=" form-control form-control-sm" autocomplete="off" />
                    </div>
                    <div class="col-md-8">
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

                    <table id="quotations" class="table table-striped table-bordered zero-configuration" style="width: 100%; table-layout: auto;">

                        <thead>
                            <tr>
                                <th style="width: 4%"><?php echo $this->lang->line('No') ?></th>
                                <th style="width: 8%">Quote #</th>
                                <th style="width: 8%"><?php echo 'A/C'; ?></th>
                                <th style="width: 17%"><?php echo 'Name'; ?></th>
                                <th style="width: 8%"><?php echo 'Type'; ?></th>
                                <th style="width: 10%"><?php echo 'Quote Date'; ?></th>
                                <th style="width: 10%"><?php echo $this->lang->line('VAT') ?></th>
                                <th style="width: 10%">Net <?php echo $this->lang->line('Amount') ?></th>
                                <th style="width: 10%"><?php echo $this->lang->line('Total') ?></th>
                                <th style="width: 6%"><?php echo 'Status'; ?></th>
                                <th style="width: 6%">Print</th>
                                <th style="width: 6%"><input type="checkbox" id="checkAll"> All</th>
                                <th class="no-sort" style="width: 7%"><?php echo $this->lang->line('Settings') ?></th>
                            </tr>
                        </thead>

                        <tbody id="quotations_body">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="MyQuotations" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
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
                                <p class="m-0">Input Quotation Numbers:</p>
                                <input class="w-100" id="quotationNumbers" type="text" data-role="tagsinput" />
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
                                Quotation Number
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
                                Quotation Number
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
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.js"></script>
<script type="text/javascript">
    document.title = 'Manage Quotations';
    
    $("body").on("dblclick", "tr", function() {
        let id = $(this).find('span[data-id]').data("id");
        let link = $(this).find('span[data-link]').data("link");
        if (link) {
            window.location.href = link;
        } else {
            console.error("Quotation link not found for id:", id);
        }
    });

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
        window.selectedQuotationIds = window.selectedQuotationIds || new Set();
        window.isBulkSelecting = false;

        // Simple format amount function using JavaScript
        const DECIMAL_PRECISION = 3; // This should match your location setting
        
        function formatAmount(amount, callback) {
            if (isNaN(amount) || amount === null || amount === undefined) {
                callback('0.' + '0'.repeat(DECIMAL_PRECISION));
                return;
            }
            
            // Format with dynamic decimal precision
            const formatted = parseFloat(amount).toFixed(DECIMAL_PRECISION);
            callback(formatted);
        }

        function generateExp() {
            let cond_1 = $("#condition_1").val();
            let cond_2 = $("#condition_2").val();
            let join_val = $("select.join").val();
            let val_1 = $("input#value_1").val();
            let val_2 = $("input#value_2").val();
            join_val = (join_val == "between") ? " and " : join_val;
            let generated = "Where 'Quotation number' " + cond_1 + " " + val_1 + " " + join_val + " 'Quotation number' " + cond_2 + " " + val_2;
            let generated_exp = "'tid' " + cond_1 + " " + val_1 + " " + join_val + " 'tid' " + cond_2 + " " + val_2;

            let quotations = $("#quotationNumbers").tagsinput('items');
            if (quotations.length > 0) {
                generated = "Where 'Quotation number' IN (" + $("#quotationNumbers").val() + ")";
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
        $("#quotationNumbers").change(generateExp);

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        draw_data(start_date, end_date, $("#expression").val());

        function draw_data(start_date = '', end_date = '', param_override = '', due_date_set = '', expression = '', rows = []) {
            $('#quotations').DataTable().destroy();
            
            var param = due_date_set ? 'date_override' : (param_override === 'date_override' ? param_override : '<?= isset($param) ? $param : "date_override" ?>');
            var due_date = (due_date_set === 'clear') ? '' : '<?= isset($due_date) ? $due_date : '' ?>';

            var tables = $('#quotations').DataTable({
                'processing': true,
                'serverSide': false,
                'stateSave': true,
                'bSortable': true,
                'bRetrieve': true,
                "bPaginate": true,
                "bFilter": true,
                "bInfo": true,

                aoColumnDefs: [
                    {"aTargets": [0], "bSortable": true},
                    {"aTargets": [1], "bSortable": true},
                    {"aTargets": [2], "bSortable": true},
                    {"aTargets": [3], "bSortable": true},
                    {"aTargets": [4], "bSortable": true},
                    {"aTargets": [5], "bSortable": true},
                    {"aTargets": [6], "bSortable": true},
                    {"aTargets": [7], "bSortable": true},
                    {"aTargets": [8], "bSortable": true},
                    {"aTargets": [9], "bSortable": true}
                ],

                responsive: false,
                'serverSide': true,
                'processing': true,
                'pageLength': 25,
                bInfo: true,

                <?php datatable_lang(); ?>

                'order': [],
                'ajax': {
                    'url': "<?= site_url('quotations/ajax_list') ?>",
                    'type': 'POST',
                    'data': function(d) {
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
                            quotations: $("#quotationNumbers").tagsinput('items'),
                            driver: $("#driverName").val()
                        });
                    }
                },
                'columnDefs': [{
                    'targets': [0, 12],
                    'orderable': false,
                }],
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [5, 1, 2, 3, 4, 6, 7, 8, 9],
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

            var table = $('#quotations').DataTable();

            // Reapply persisted selections on each draw
            function reapplySelectionsAndTotals() {
                var totalAmount = 0;
                var allVisibleSelected = true;

                $('#quotations tbody tr').each(function() {
                    var $row = $(this);
                    var $checkbox = $row.find('input[name="receipt"]');
                    var id = $checkbox.val();
                    var rowData = table.row(this).data() || [];
                    var amount = parseFloat((rowData[8] || '').toString().replace(/[^\d.-]/g, '')) || 0;

                    if (id && window.selectedQuotationIds.has(id)) {
                        $row.addClass('selected');
                        $checkbox.prop('checked', true);
                        totalAmount += amount;
                    } else {
                        $row.removeClass('selected');
                        $checkbox.prop('checked', false);
                        allVisibleSelected = false;
                    }
                });

                formatAmount(totalAmount, function(formatted) {
                    $('#total_selected_amount').val(formatted);
                });
                $('#checkAll').prop('checked', allVisibleSelected && $('#quotations tbody tr').length > 0);
            }

            table.on('draw.dt', function() {
                reapplySelectionsAndTotals();
            });

            // Cancel AJAX draws while bulk selecting
            table.on('preXhr.dt', function(e, settings, data) {
                if (window.isBulkSelecting) {
                    return false;
                }
            });

            // Prevent row double-toggle when clicking directly on checkbox
            $('#quotations tbody').off('click.receiptCb', 'input[name="receipt"]').on('click.receiptCb', 'input[name="receipt"]', function(e) {
                e.stopPropagation();
                var $cb = $(this);
                var $row = $cb.closest('tr');
                var id = $cb.val();
                if ($cb.is(':checked')) {
                    $row.addClass('selected');
                    if (id) window.selectedQuotationIds.add(id);
                } else {
                    $row.removeClass('selected');
                    if (id) window.selectedQuotationIds.delete(id);
                }
                reapplySelectionsAndTotals();
            });

            $('.dataTables_filter input[type="search"]').unbind().keyup(function(e) {
                var value = $(this).val().toUpperCase();
                table.search(value).draw();
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
                            url: "<?php echo site_url('quotations/delete') ?>",
                            data: {
                                id: objectId
                            },
                            success: function(data) {
                                Swal.fire(
                                    'Deleted!',
                                    'Quotation has been deleted.',
                                    'success'
                                );
                                $('#quotations').DataTable().destroy();
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

            $('#quotations tbody').off('click', 'tr').on('click', 'tr', function() {
                var data = tables.row(this).data();
                $(':checkbox', data[12]).trigger('click');

                var col_amt = parseFloat(data[8]?.replace(/[^\d.-]/g, '')) || 0;
                var pre_amt = parseFloat($('#total_selected_amount').val()) || 0;

                if ($(this).hasClass('selected')) {
                    var newAmount = pre_amt - col_amt;
                    formatAmount(newAmount, function(formatted) {
                        $('#total_selected_amount').val(formatted);
                    });
                    $(this).removeClass('selected');
                    var $cb = $(this).find('input[type=checkbox]');
                    $cb.prop('checked', false);
                    if ($cb.attr('name') === 'receipt') {
                        var id = $cb.val();
                        if (id) window.selectedQuotationIds.delete(id);
                    }
                } else {
                    var newAmount = pre_amt + col_amt;
                    formatAmount(newAmount, function(formatted) {
                        $('#total_selected_amount').val(formatted);
                    });
                    var $cb = $(this).find('input[type=checkbox]');
                    $cb.prop('checked', true);
                    if ($cb.attr('name') === 'receipt') {
                        var id = $cb.val();
                        if (id) window.selectedQuotationIds.add(id);
                    }
                    $(this).addClass('selected');
                }
                var allVisibleSelected = $('#quotations tbody tr').length > 0 && $('#quotations tbody tr').toArray().every(function(tr){ return $(tr).hasClass('selected'); });
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
            $("#condition_1").val("=")
            $("#condition_2").val("=")
            $("input#value_1").val(0)
            $("input#value_2").val(0)
            $("#expression").val("");
            $("#expression2").val("");
            $("#value").val(0);
            $("#value2").val(0);
            $("#joinQuery").val("and");
            $("#quotationNumbers").val("");
            $('#quotationNumbers').tagsinput('destroy');
            $("#quotationNumbers").tagsinput({
                trimValue: true
            });
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var due_date_set = 'clear';
            var param_override = 'date_override';
            draw_data(start_date, end_date, param_override, due_date_set, $("#expression").val());
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
            var table = $('#quotations').DataTable();
            window.isBulkSelecting = true;

            var totalAmount = 0;

            $('#quotations tbody tr').each(function() {
                var $row = $(this);
                var $checkbox = $row.find('input[name="receipt"]');
                var id = $checkbox.val();
                var rowData = table.row(this).data() || [];
                var amount = parseFloat((rowData[8] || '').toString().replace(/[^\d.-]/g, '')) || 0;

                if (selectAllChecked) {
                    $row.addClass('selected');
                    $checkbox.prop('checked', true);
                    if (id) window.selectedQuotationIds.add(id);
                    totalAmount += amount;
                } else {
                    $row.removeClass('selected');
                    $checkbox.prop('checked', false);
                    if (id) window.selectedQuotationIds.delete(id);
                }
            });

            if (selectAllChecked) {
                formatAmount(totalAmount, function(formatted) {
                    $('#total_selected_amount').val(formatted);
                });
            } else {
                formatAmount(0, function(formatted) {
                    $('#total_selected_amount').val(formatted);
                });
            }

            setTimeout(function() { window.isBulkSelecting = false; }, 0);
        });

        $('#search').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var due_date_set = 'clear';
            var param_override = 'date_override';

            if (start_date !== '' && end_date !== '') {
                draw_data(start_date, end_date, param_override, due_date_set, $("#expression").val());
            } else {
                alert("Date range is required.");
            }
        });

        $('#quotations').DataTable().search('').draw();
    });
</script>
