<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Merge And Print Invoices </h4>
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
                    <div class="col-md-2">Invoice Date Between:</div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" id="start_date"
                            class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                            autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm" />
                    </div>
                </div>


                <br>
                <div class="row">
                    <?php
                    // COMMENTED OUT: Multi-currency support and hardcoded PKR fallback
                    // OLD CODE:
                    // $sample_currency = null;
                    // $this->db->select('multi, loc')->from('geopos_invoices')->limit(1);
                    // $query = $this->db->get();
                    // if ($query->num_rows() > 0) {
                    //     $sample_inv = $query->row();
                    //     $sample_currency = $sample_inv->multi;
                    //     $sample_loc = $sample_inv->loc;
                    // } else {
                    //     $this->db->select('id')->from('geopos_currencies')->where('LOWER(code)', 'pkr')->or_where('LOWER(symbol)', 'pkr')->limit(1);
                    //     $curr_query = $this->db->get();
                    //     if ($curr_query->num_rows() > 0) {
                    //         $sample_currency = $curr_query->row()->id;
                    //         $sample_loc = $this->aauth->get_user()->loc;
                    //     }
                    // }
                    // if ($sample_currency !== null) {
                    //     $formatted = amountExchange(0, $sample_currency, isset($sample_loc) ? $sample_loc : $this->aauth->get_user()->loc);
                    //     preg_match('/^([^\d\s,\.]+)\s*/', $formatted, $matches);
                    //     $currency_symbol = !empty($matches[1]) ? $matches[1] : 'PKR';
                    // } else {
                    //     $currency_symbol = 'PKR'; // Fallback
                    // }
                    
                    // NEW CODE: Always use geopos_system.currency
                    $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
                    $row = $query->row_array();
                    $currency_symbol = strtoupper($row['currency']);
                    ?>
                    <div class="col-md-2">Selected Invoices (<?php echo $currency_symbol; ?>):</div>
                    <div class="col-md-2">
                        <input type="number" step="any" name="total_selected_amount" value="0" readonly id="total_selected_amount" class="form-control form-control-sm" autocomplete="off" />
                    </div>
                    <div class="col-md-2">
                        <input type="button" name="break_down_report" id="break_down_report" value="Print Selected Invoices" class="btn btn-info btn-sm" />
                    </div>

                    <!--div class="col-md-2" >  
                  <input type="button" name="break_dn_report" id="break_dn_report" value="Print Delivery Notes" class="btn btn-info btn-sm"/> 
                  </div-->

                </div>
                <br>

                <table id="invoices" class="table table-striped table-bordered zero-configuration ">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th><?php echo 'A/C'; ?></th>
                            <th><?php echo 'Name'; ?></th>
                            <th><?php echo 'Invoice Type'; ?></th>
                            <th><?php echo 'Invoice Date'; ?></th>
                            <th><?php echo $this->lang->line('Amount') ?> (<?php echo $currency_symbol; ?>)</th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th> ALL <input type="checkbox" id="select_all_page" title="Select current page" /></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th><?php echo 'A/C'; ?></th>
                            <th><?php echo 'Name'; ?></th>
                            <th><?php echo 'Invoice Type'; ?></th>
                            <th><?php echo 'Invoice Date'; ?></th>
                            <th><?php echo $this->lang->line('Amount') ?> (<?php echo $currency_symbol; ?>)</th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th> ALL</th>
                        </tr>
                    </tfoot>
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

<form method="POST" id="form_for_export" action="<?php echo site_url('invoices/print_merged') ?>" target="_blank">
    <input type="hidden" id="hidden_fields" name="hidden_fields"
        value="" />
    <input type="hidden" name="print_type" value="sale" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>

<form method="POST" id="form_for_donote" action="<?php echo site_url('invoices/print_do_merged') ?>" target="_blank">
    <input type="hidden" id="hidden_field" name="hidden_field"
        value="" />
    <input type="hidden" name="print_type" value="sale" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>

<script type="text/javascript">
    document.title = 'Print Invoices';
</script>
<script type="text/javascript">
    $("body").on("dblclick", "tr", function() {
        let id = $(this).attr("id");
        window.location.href = $('[data-id="' + id + '"]').data("link");
    });

    $('#break_down_report').click(function() {
        var fields = [];

        $("input:checkbox[name=receipt]:checked").each(function() {
            fields.push($(this).val());
        });
        
        console.log('Selected fields:', fields); // Debug log
        
        if (fields.length == 0) {
            alert("Please select at least one invoice to print.");
            return;
        }
        
        $('#hidden_fields').val(fields.join(','));
        console.log('Hidden fields value:', $('#hidden_fields').val()); // Debug log

        $('#form_for_export').submit();
    });


    $('#break_dn_report').click(function() {
        var fields = [];

        $("input:checkbox[name=receipt]:checked").each(function() {
            fields.push($(this).val());
        });
        if (fields == "") {
            return;
        }
        $('#hidden_field').val(fields);
        //url="<?php echo site_url('invoices/daily_break_down_sheet_export?ids=') ?>"+fields;

        $('#form_for_donote').submit();
        // window.open(url, '_blank');

        //   window.location.href = baseurl + "pos_invoices/extended";

    });


    $(document).ready(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        draw_data(start_date, end_date);

        function draw_data(start_date = '', end_date = '') {


            var tables = $('#invoices').DataTable({
                'processing': true,
                'serverSide': true,
                'stateSave': true,
                'pageLength': 25,
                'lengthMenu': [
                    [-1, 25, 50, 100, 200],
                    ["All", 25, 50, 100, 200]
                ],
                'deferRender': true,
                'searchDelay': 500,
                <?php datatable_lang(); ?>

                responsive: false,

                'order': [
                    [4, 'desc']
                ],
                'ajax': {
                    'url': "<?php echo site_url('invoices/ajax_list_customers_invoices') ?>",
                    'type': 'POST',
                    'data': function(d) {
                        return $.extend({}, d, {
                            '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                            start_date: start_date,
                            end_date: end_date
                        });
                    }
                },
                'columnDefs': [{
                    'targets': [0, 7],
                    'orderable': false,
                }, ],
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5]
                    }
                }],
            "fnCreatedRow": function(nRow, aData, iDataIndex) {
                $(nRow).attr('id', aData[0]);
                
                // Add checkbox to the last column if it doesn't exist
                var $lastCell = $(nRow).find('td:last');
                if ($lastCell.find('input[type="checkbox"]').length === 0) {
                    var checkbox = '<input type="checkbox" class="checkbox" name="receipt" data-id="' + aData[0] + '" value="' + aData[0] + '">';
                    $lastCell.html(checkbox);
                }
            }


            });
            // Persist selection across redraws
            var selectedIds = new Set();

            $('#invoices tbody').off('click', 'tr').on('click', 'tr', function(e) {
                // Don't trigger if clicking on checkbox directly
                if ($(e.target).is('input[type="checkbox"]')) {
                    return;
                }

                var data = tables.row(this).data();
                var $chk = $(this).find('input[type=checkbox]');
                
                if ($(this).hasClass('selected')) {
                    var col_amt = parseFloat(data[5].replace(/[^\d.-]/g, ''));
                    var pre_amt = parseFloat($('#total_selected_amount').val());
                    var value = pre_amt - col_amt;
                    $('#total_selected_amount').val(value.toFixed(2));
                    $(this).removeClass('selected');
                    $chk.prop('checked', false);
                    selectedIds.delete(String(data[0]));
                } else {
                    var col_amt = parseFloat(data[5].replace(/[^\d.-]/g, ''));
                    var pre_amt = parseFloat($('#total_selected_amount').val());
                    var value = pre_amt + col_amt;
                    $('#total_selected_amount').val(value.toFixed(2));
                    $chk.prop('checked', true);
                    $(this).addClass('selected');
                    selectedIds.add(String(data[0]));
                }
            });

            // Handle direct checkbox clicks
            $('#invoices tbody').on('change', 'input[type="checkbox"]', function() {
                var $row = $(this).closest('tr');
                var data = tables.row($row).data();
                var isChecked = $(this).is(':checked');
                
                if (isChecked) {
                    $row.addClass('selected');
                    selectedIds.add(String(data[0]));
                } else {
                    $row.removeClass('selected');
                    selectedIds.delete(String(data[0]));
                }
                
                recalcTotalCurrentPage();
            });

            function recalcTotalCurrentPage() {
                var sum = 0;
                $(tables.rows({
                    page: 'current'
                }).nodes()).each(function() {
                    var rowData = tables.row(this).data();
                    var $chk = $('input[name=receipt]', this);
                    if ($chk.length && $chk.prop('checked')) {
                        var amt = parseFloat((rowData[5] || '').toString().replace(/[^\d.-]/g, '')) || 0;
                        sum += amt;
                    }
                });
                $('#total_selected_amount').val(sum.toFixed(2));
            }

            // Prevent header checkbox click from triggering sort
            $('#select_all_page').on('click', function(e) {
                e.stopPropagation();
            });

            $('#select_all_page').off('change').on('change', function() {
                var checked = $(this).is(':checked');
                $(tables.rows({
                    page: 'current'
                }).nodes()).each(function() {
                    var $row = $(this);
                    var $chk = $row.find('input[name=receipt]');
                    if ($chk.length) {
                        $chk.prop('checked', checked);
                        if (checked) {
                            $row.addClass('selected');
                            var rowData = tables.row(this).data();
                            selectedIds.add(String(rowData[0]));
                        } else {
                            $row.removeClass('selected');
                            var rowData = tables.row(this).data();
                            selectedIds.delete(String(rowData[0]));
                        }
                    }
                });
                recalcTotalCurrentPage();
            });

            tables.on('draw', function() {
                // Re-apply selection state to visible rows
                var allVisibleChecked = true;
                $(tables.rows({
                    page: 'current'
                }).nodes()).each(function() {
                    var $row = $(this);
                    var rowData = tables.row(this).data();
                    var isSelected = selectedIds.has(String(rowData[0]));
                    var $chk = $row.find('input[name=receipt]');
                    if ($chk.length) {
                        $chk.prop('checked', isSelected);
                    }
                    if (isSelected) {
                        $row.addClass('selected');
                    } else {
                        $row.removeClass('selected');
                        allVisibleChecked = false;
                    }
                });
                $('#select_all_page').prop('checked', allVisibleChecked);
                recalcTotalCurrentPage();
            });
        }
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
        // Initial draw already occurs on initialization; avoid double request
    });
</script>