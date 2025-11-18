<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Manage Invoices </h4>
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
                <div class="row align-items-end">
                    <div class="col-md-1"></div>
                    <div class="col-md-2">
                        Invoice Date Between:
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        &nbsp;
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2 mt-1">
                        Invoice Type:
                        <div>
                            <div class="form-check-inline">
                                <label class="form-check pl-0 mb-0">
                                    <input type="checkbox" class="form-check-input" value="DAYPASS" name="invoiceType" checked>DAYPASS
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check pl-0 mb-0">
                                    <input type="checkbox" class="form-check-input" value="INVOICE" name="invoiceType">INVOICE
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mt-1 pr-0">
                        Invoice Status:
                        <div>
                            <div class="form-check-inline">
                                <label class="form-check mb-0 pl-0">
                                    <input type="checkbox" class="form-check-input" value="due" name="invoiceStatus" checked>Due
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check mb-0 pl-0">
                                    <input type="checkbox" class="form-check-input" value="partial" name="invoiceStatus">Partial
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check mb-0 pl-0">
                                    <input type="checkbox" class="form-check-input" value="paid" name="invoiceStatus">Paid
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm" />
                        <button class="btn btn-danger bulk-delete btn-sm ml-1">
                            <span class="fa fa-trash"></span>
                            <span class="bulk-delete-text"> Bulk Delete</span>
                        </button>
                        <button class="btn btn-secondary cancel-bulk-delete btn-sm ml-1" style="display: none">Cancel</button>

                    </div>

                </div>


                <br>
                <!--
      <div class="row">
                    <div class="col-md-2">Selected Invoices</div>
                    <div class="col-md-2">
                        <input type="number" step="any" name="total_selected_amount" value="0" readonly id="total_selected_amount" class=" form-control form-control-sm"   autocomplete="off"/>
                    </div>
                    <div class="col-md-4" ><input type="button" name="break_down_report" id="break_down_report" value="Print Selected" class="btn btn-success btn-sm"/>
                        
                    </div> 
        </div>
        -->
                <br>

                <table id="invoices" class="table table-striped table-bordered zero-configuration ">

                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th> Inv #</th>
                            <th><?php echo 'A/C'; ?></th>
                            <th><?php echo 'Name'; ?></th>
                            <th><?php echo 'Type'; ?></th>
                            <th><?php echo 'Date'; ?></th>
                            <th><?php echo $this->lang->line('Amount') ?></th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th>Print Status</th>
                            <th><input type="checkbox" id="checkAll"> Select All</th>
                            <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
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

<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Delete Invoice') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('delete this invoice') ?> ?</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

<form method="POST" id="form_for_export" action="<?php echo site_url('invoices/print_merged') ?>" target="_blank">
    <input type="hidden" id="hidden_fields" name="hidden_fields" value="" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
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
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        draw_data(start_date, end_date);

        function draw_data(start_date = '', end_date = '') {

            var types = '';
            var statuses = '';
            $('input[name="invoiceType"]:checked').each(function(ind) {
                types += ((ind == 0 ? "" : ",") + this.value);
            });

            $('input[name="invoiceStatus"]:checked').each(function(ind) {
                statuses += ((ind == 0 ? "" : ",") + this.value);
            });

            var tables = $('#invoices').DataTable({
                'processing': true,
                'serverSide': true,
                'stateSave': true,
                'pageLength': -1,
                <?php datatable_lang(); ?>

                responsive: false,

                'order': [],
                'ajax': {
                    'url': "<?php echo site_url('invoices/ajax_bulk_invoices') ?>",
                    'type': 'POST',
                    'data': {
                        '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                        start_date: start_date,
                        end_date: end_date,
                        type: types,
                        status: statuses
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
                        columns: [1, 2, 3, 4, 5]
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
            $('.cancel-bulk-delete').click(function() {
                $(this).css('display', 'none');
                $('.bulk-delete').attr("data-mode", "select");
                $(".bulk-delete-text").text("Bulk Delete");
                $('#invoices').DataTable().destroy();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                draw_data(start_date, end_date);
            })
            $('.bulk-delete').click(function() {
                var fields = [];
                $("input:checkbox[name=receipt]:checked").each(function() {
                    fields.push($(this).val());
                });
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo site_url('invoices/bulk_delete_daypass') ?>",
                            data: {
                                '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                                ids: fields
                            },
                            success: function(data) {
                                Swal.fire(
                                    'Deleted!',
                                    fields,
                                    'success'
                                );
                                $('#invoices').DataTable().destroy();
                                var start_date = $('#start_date').val();
                                var end_date = $('#end_date').val();
                                draw_data(start_date, end_date);
                                $('.bulk-delete').attr("data-mode", "select");
                                $(".bulk-delete-text").text("Bulk Delete");
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
            $('#invoices tbody').off('click', 'tr').on('click', 'tr', function() {

                var data = tables.row(this).data();
                $(':checkbox', data[8]).trigger('click');
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
        $("#checkAll").click(function() {
            $('#invoices_body input:checkbox').not(this).prop('checked', this.checked);
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
        $('#search').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            if (start_date != '' && end_date != '') {
                $('#invoices').DataTable().destroy();
                draw_data(start_date, end_date);
            } else {
                alert("Date range is Required");
            }
        });
        $('#invoices').DataTable().search('').draw();
    });
</script>