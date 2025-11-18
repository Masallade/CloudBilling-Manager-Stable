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
            <h4 class="card-title"> Unposted Invoices </h4>
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
                        <input type="date" name="start_date" id="start_date"
                            class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                        <input type="hidden" name="expression" id="expression" />
                        <input type="hidden" name="expression2" id="expression2" />
                        <input type="hidden" name="joinQuery" id="joinQuery" />
                        <input type="hidden" name="value" id="value" />
                        <input type="hidden" name="value2" id="value2" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                            autocomplete="off" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm" />
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-2">Selected Invoices (<?= currency($this->aauth->get_user()->loc); ?>)</div>
                    <div class="col-md-2">
                        <input type="number" step="any" name="total_selected_amount" value="0" readonly id="total_selected_amount" class=" form-control form-control-sm" autocomplete="off" />
                    </div>
                    <!--<div class="col-md-4" >                        
                        <button class="btn btn-success btn-sm post_invoices" id="post_invoices">Post Selected</button>
                    </div>-->
                    <div class="col-md-8">
                        <!-- <input type="button" name="break_down_report" id="break_down_report" value="Print Selected" class="btn btn-success btn-sm"/>-->
                        <?php if ($this->aauth->permission_new(null, 'salesPrintInvoices')) { ?>
                        <select id="print_type" class="form-control form-control-sm" style="width:auto; display:inline-block;">
                            <option value="sale">Sale</option>
                            <option value="counter">Counter</option>
                            <option value="quote">Quote</option>
                            <option value="loadingslip">Loading Slip</option>
                        </select>
                        <input type="button" name="break_down_report" id="break_down_report" value="Print Selected" class="btn btn-success btn-sm" />
                        <?php } ?>
                        <?php if ($this->aauth->permission_new(null, 'salesSelectPostInvoice')) { ?>
                        <button class="btn btn-success btn-sm post_invoices" id="post_invoices">Post Selected</button>
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
                    <div class="col-md-2">

                        <!--  <button class="btn btn-success btn-sm post_invoices" id="post_invoices">Post Invoices</button>-->
                    </div>
                </div>
                <br>

                <table id="invoices" class="table table-striped table-bordered zero-configuration" style="width: 100%; table-layout: auto;">

                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th> Inv #</th>
                            <th><?php echo $this->lang->line('A/C'); ?></th>
                            <th><?php echo $this->lang->line('Name'); ?></th>
                            <th><?php echo $this->lang->line('Type'); ?></th>
                            <th><?php echo $this->lang->line('Date'); ?></th>
                            <th><?php echo $this->lang->line('Tax') ?></th>
                            <th><?php echo $this->lang->line('Net Amount') ?></th>
                            <th><?php echo $this->lang->line('Payment') ?></th>
                            <th><?php echo $this->lang->line('PrintStatus') ?></th>
                            <th><input type="checkbox" id="checkAll"> <?php echo $this->lang->line('SelectAll') ?></th>
                            <th class="no-sort" style="width:100px;"><?php echo $this->lang->line('Settings') ?></th>
                        </tr>
                    </thead>
                    <tbody id="invoices_body">
                    </tbody>
                    <!--       <tfoot>
                      <tr>
                       <th><?php echo $this->lang->line('No') ?></th>
                        <th> Inv #</th>
                        <th><?php echo 'A/C'; ?></th>  
                        <th><?php echo 'Name'; ?></th>
                        <th><?php echo 'Invoice Type'; ?></th>
                        <th><?php echo 'Invoice Date'; ?></th> 
                        <th><?php echo $this->lang->line('Amount') ?></th>
                        <th><?php echo $this->lang->line('Status') ?></th>
                        <th><input type="checkbox" id="checkAll"> Select All</th>
                        <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
                    </tr>
                    </tfoot> -->
                </table>
            </div>
        </div>

        <div id="MyInvoices" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
    </div>
</div>

<script type="text/javascript">
    document.title = 'Unposted Invoices';
</script>
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
<!-- <form method="POST" id="form_for_export" action="<?php echo site_url('invoices/print_merged_unposted') ?>" target="_blank">
    <input type="hidden" id="hidden_fields" name="hidden_fields"
        value="" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form> -->
<form method="POST" id="form_for_export" action="<?php echo site_url('invoices/print_merged_unposted') ?>" target="_blank">
    <input type="hidden" id="hidden_fields" name="hidden_fields" value="" />
    <input type="hidden" id="print_type_hidden" name="print_type" value="" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.js"></script>
<script type="text/javascript">
    $("body").on("dblclick", "tr", function() {
        let id = $(this).attr("id");
        window.location.href = $('span[data-id="' + id + '"]').data("link");
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

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        draw_data(start_date, end_date, $("#expression").val());

        function draw_data(start_date = '', end_date = '', expression = '') {
            $('#invoices').DataTable().destroy();
            var tables = $('#invoices').DataTable({

                'processing': true,
                'serverSide': true,
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
                    }


                ],


                responsive: true,
                'pageLength': 25,
                bInfo: true,

                <?php datatable_lang(); ?>

                'order': [],
                'ajax': {
                    'url': "<?php echo site_url('invoices/ajax_list_bfr_post') ?>",
                    'type': 'POST',
                    'data': function (d) {
                        d['<?= $this->security->get_csrf_token_name() ?>'] = crsf_hash;
                        d.start_date = start_date;
                        d.end_date = end_date;
                        d.expression = expression;
                        d.expression2 = $("#expression2").val();
                        d.value = $("#value").val();
                        d.value2 = $("#value2").val();
                        d.joinQuery = $("#joinQuery").val();
                        d.invoices = $("#invoiceNumbers").tagsinput('items');
                        d.driver = $("#driverName").val();
                        // Note: d already contains DataTables paging params: d.start, d.length, d.draw
                    }
                },
                'columnDefs': [{
                    'targets': [0, 9],
                    'orderable': false,
                }],
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7]
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

            $('.dataTables_filter input[type="search"]').unbind().keyup(function(e) {
                var value = $(this).val().toUpperCase();
                table.search(value).draw();
            });



            $('.cancel-bulk-delete').click(function() {
                $(this).css('display', 'none');
                $('.bulk-delete').attr("data-mode", "select");
                $(".bulk-delete-text").text("Bulk Delete DAYPASS");
                $('#invoices').DataTable().destroy();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                draw_data(start_date, end_date, $("#expression").val());
            })

            $('#invoices tbody').off('click', 'tr').on('click', 'tr', function() {

                var data = tables.row(this).data();
                $(':checkbox', data[10]).trigger('click');
                if ($(this).hasClass('selected')) {
                    var col_amt = parseFloat(data[6].replace(/[^\d.-]/g, ''));
                    var pre_amt = parseFloat($('#total_selected_amount').val());
                    var value = pre_amt - col_amt;
                    $('#total_selected_amount').val(value.toFixed(2));
                    $(this).removeClass('selected');
                    var $chk = $(this).find('input[type=checkbox]');
                    $chk.prop('checked', false);
                } else {
                    var $chk = $(this).find('input[type=checkbox]');
                    var col_amt = parseFloat(data[6].replace(/[^\d.-]/g, ''));
                    var pre_amt = parseFloat($('#total_selected_amount').val());
                    var value = pre_amt + col_amt;
                    $('#total_selected_amount').val(value.toFixed(2));
                    $chk.prop('checked', true);
                    $(this).addClass('selected');
                }

            });
        }

        function clearFilter() {
            $("#condition_1").attr("disabled", false)
            $("#condition_2").attr("disabled", false)
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
            draw_data(start_date, end_date, "");
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

        $("#checkAll").click(function() {
            $('input:checkbox').not(this).prop('checked', this.checked);
            $("#invoices_body tr").each(function() {
                if ($('#checkAll').is(':checked')) {
                    if ($(this).hasClass('selected')) {
                        // $(this).click();
                    } else {
                        $(this).click();
                    }
                } else {
                    if ($(this).hasClass('selected')) {
                        $(this).click();
                    } else {

                    }

                }
            });
        });





        $('body').on('click', '.delete-invoice-bfr-post', function() {


            // var fields=[];
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
                        url: "<?php echo site_url('invoices/delete_post_invoices') ?>",
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
                            draw_data(start_date, end_date, $("#expression").val());

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


        $('body').on('click', '.post_invoices', function() {
            var fields = [];
            var objectId = $(this).data('object-id');

            $("input:checkbox[name=receipt]:checked").each(function() {
                fields.push($(this).val());
            });

            if (objectId) {
                fields.push(objectId);
            }

            const uniqueArray = [...new Set(fields)];

            if (uniqueArray.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select at least one Invoice to Post',
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to post these invoices?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Post them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        type: "get",
                        url: "<?php echo site_url('invoices/post_invoices') ?>",
                        data: {
                            ids: uniqueArray
                        },
                        dataType: "json", // tell jQuery to expect JSON
                        success: function(data) {
                            if (data.success) {
                                Swal.fire('Posted!', 'Invoices have been posted.', 'success');

                                const newIds = data.new_ids || [];
                                console.log("New posted invoice IDs:", newIds);

                                newIds.forEach(function(newId) {
                                    runAllPrintUrls(newId);
                                });

                                // Refresh DataTable
                                $('#invoices').DataTable().destroy();
                                var start_date = $('#start_date').val();
                                var end_date = $('#end_date').val();
                                draw_data(start_date, end_date, $("#expression").val());

                                $('.bulk-delete').attr("data-mode", "select");
                                $(".bulk-delete-text").text("POST Invoices");
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to post invoices. Please try again.',
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    });
                }
            });
        });

        function runAllPrintUrls(newId) {
            if (!newId) return;
            console.log('posted invoice ID:', newId);
            const urls = [
                "invoices/printinvoice",
                "invoices/counter_invoice",
                "invoices/quotattion_invoice"
            ];

            urls.forEach(function(endpoint) {
                $.get(baseurl + endpoint + "?id=" + newId + "&d=1");
                // window.open(baseurl + endpoint + "?id=" + newId + "&d=1", '_blank');

            });
        }



        $('#break_down_report').click(function() {
            Swal.showLoading();

            var fields = [];

            $("input:checkbox[name=receipt]:checked").each(function() {
                fields.push($(this).val());
            });

            fields.forEach((id, index) => {
                $.ajax({
                    url: '<?php echo site_url('invoices/printinvoice_bfr_posting') ?>?id=' + id + '&d=1',
                    type: 'GET',
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
                        console.error('Error:', error);
                    }
                });
            });

            if (fields == "") {
                return;
            }
            $('#hidden_fields').val(fields);



        });



        $('#search').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            if (start_date != '' && end_date != '') {
                $('#invoices').DataTable().destroy();
                draw_data(start_date, end_date, $("#expression").val());
            } else {
                alert("Date range is Required");
            }
        });
        $('#invoices').DataTable().search('').draw();
    });
</script>