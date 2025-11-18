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
                    <h2 class="card-title">
                        Customer Activities
                    </h2>
                </div>

                <div class="col-md-9">
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
                <div class="row justify-content-center">
                    <div class="col-md-2">
                        <span>From:</span>
                        <input type="date" class="form-control required" placeholder="Start Date" autocomplete="false" name="sdate" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <span>To:</span>
                        <input type="date" class="form-control required" placeholder="End Date" autocomplete="false" name="edate" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <span>Debit:</span>
                        <input type="number" step="any" name="debit_amount" value="0" readonly="" id="debit_amount" class=" form-control" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <span>Credit:</span>
                        <input type="number" step="any" name="credit_amount" value="0" readonly="" id="credit_amount" class=" form-control" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <span>Balance:</span>
                        <input type="number" step="any" name="balance_amount" value="0" readonly="" id="balance_amount" class=" form-control" autocomplete="off">
                    </div>
                    <div class="col-md-2 offset-md-4 mt-2">
                        <span>Total Selected Debit:</span>
                        <input type="number" step="any" name="total_debit_amount" value="0" readonly="" id="total_debit_amount" class=" form-control" autocomplete="off">
                    </div>
                    <div class="col-md-2 mt-2">
                        <span>Total Selected Credit:</span>
                        <input type="number" step="any" name="total_credit_amount" value="0" readonly="" id="total_credit_amount" class=" form-control" autocomplete="off">
                    </div>
                    <hr class="mt-3">
                    <div class="col-md-12">
                        <h4>
                            <?php echo $this->lang->line('Transactions'); ?>
                        </h4>
                        <hr>
                        <table id="crtstable" class="table table-striped table-bordered zero-configuration"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>
                                        <?php echo 'No'; ?>
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Date'); ?>
                                    </th>
                                    <th>
                                        <?php echo 'Ref'; ?>
                                    </th>
                                    <th>Customer</th>
                                    <th>Details</th>
                                    <th>
                                        <?php echo $this->lang->line('Debit'); ?> (GBP)
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Credit'); ?> (GBP)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>
                                        <?php echo 'No'; ?>
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Date'); ?>
                                    </th>
                                    <th>
                                        <?php echo 'Ref'; ?>
                                    </th>
                                    <th>Customer</th>
                                    <th>Details</th>
                                    <th>
                                        <?php echo $this->lang->line('Debit'); ?> (GBP)
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Credit'); ?> (GBP)
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.title = "Customer Activities";
    $(document).ready(function() {
        function calculateSum() {
            let start = $('input[name="sdate"]').val();
            let end = $('input[name="edate"]').val();
            $.ajax({
                url: "<?php echo site_url('customers/activitylist'); ?>",
                type: "POST",
                data: {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                    'start': start,
                    'end': end,
                },
                success: function(data, text) {
                    var json = $.parseJSON(data)
                    console.log(json)
                    $("input#debit_amount").val(json.debit);
                    $("input#credit_amount").val(json.credit);
                    $("input#balance_amount").val(json.balance);
                },
                error: function(request, status, error) {

                }
            });
        }

        var tables = $('#crtstable').DataTable({});
        //datatables
        function draw_table() {
            $('#crtstable').DataTable().destroy();
            let start = $('input[name="sdate"]').val();
            let end = $('input[name="edate"]').val();
            tables = $('#crtstable').DataTable({
                "bPaginate": true,
                "bFilter": true,
                "bInfo": false,
                "bSortable": false,
                "bRetrieve": true,
                "pageLength": 50,
                aoColumnDefs: [{
                        "aTargets": [0],
                        "bSortable": false
                    },
                    {
                        "aTargets": [1],
                        "bSortable": false
                    },
                    {
                        "aTargets": [2],
                        "bSortable": false
                    },
                    {
                        "aTargets": [3],
                        "bSortable": false
                    }
                ],
                "processing": true, //Feature control the processing indicator.
                "serverSide": false, //Feature control DataTables' server-side processing mode.
                "order": [],
                responsive: true,
                <?php datatable_lang(); ?>
                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo site_url('customers/activitylist'); ?>",
                    "type": "POST",
                    "data": {
                        '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                        'start': start,
                        'end': end,
                    }
                },
                //Set column definition initialisation properties.
                "columnDefs": [{
                        "targets": [0], //first column / numbering column
                        "orderable": true, //set not orderable
                    },
                    {
                        "targets": [1], // second column / "date" column
                        "orderable": false, // set orderable
                        "orderData": [1], // specify the index of the column to sort
                        "orderSequence": ["DESC"] // specify the sorting order ("asc" for ascending)
                    },
                ],
            });

            calculateSum();
        }
        draw_table();
        $("input[name='sdate']").change(draw_table)
        $("input[name='edate']").change(draw_table)

        $('#crtstable tbody').off('click', 'tr').on('click', 'tr', function() {

            var data = tables.row(this).data();
            if ($(this).hasClass('selected')) {
                var col_amt = parseFloat(data[6].replace(/[^\d.-]/g, ''));
                var pre_amt = parseFloat($('#total_credit_amount').val());
                var value = pre_amt - col_amt;
                $('#total_credit_amount').val(value.toFixed(2));

                col_amt = parseFloat(data[5].replace(/[^\d.-]/g, ''));
                pre_amt = parseFloat($('#total_debit_amount').val());
                value = pre_amt - col_amt;
                $('#total_debit_amount').val(value.toFixed(2));

                $(this).removeClass('selected');
            } else {
                var col_amt = parseFloat(data[6].replace(/[^\d.-]/g, ''));
                var pre_amt = parseFloat($('#total_credit_amount').val());
                var value = pre_amt + col_amt;
                $('#total_credit_amount').val(value.toFixed(2));

                col_amt = parseFloat(data[5].replace(/[^\d.-]/g, ''));
                pre_amt = parseFloat($('#total_debit_amount').val());
                value = pre_amt + col_amt;
                $('#total_debit_amount').val(value.toFixed(2));

                $(this).addClass('selected');
            }
        });
    });
</script>