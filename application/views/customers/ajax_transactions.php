<!-- AJAX Content for Customer Transactions -->
<div class="card">
    <div class="card-header" style="background-color:rgb(164, 184, 206); color: #fff; text-align: center;">
        <h2 class="card-title" style="color: #fff;">Customer Activities</h2>
    </div>
    <hr>
    <div class="card-content">
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="transactions_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo $this->lang->line('ID') ?></th>
                                            <th><?php echo $this->lang->line('Date') ?></th>
                                            <th><?php echo $this->lang->line('Reference') ?></th>
                                            <th><?php echo $this->lang->line('Description') ?></th>
                                            <th><?php echo $this->lang->line('User') ?></th>
                                            <th><?php echo $this->lang->line('Credit') ?></th>
                                            <th><?php echo $this->lang->line('Debit') ?></th>
                                            <th><?php echo $this->lang->line('Balance') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#transactions_table')) {
            $('#transactions_table').DataTable().destroy();
        }

        $('#transactions_table').DataTable({
            "bPaginate": true,
            "bFilter": true,
            "bInfo": true,
            "bSortable": true,
            "bRetrieve": true,
            "pageLength": 25,
            aoColumnDefs: [{
                    "aTargets": [0],
                    "bSortable": false,
                    "searchable": false
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
                }
            ],
            "processing": true, //Feature control the processing indicator.
            "serverSide": true, //Feature control DataTables' server-side processing mode.
            "order": [],
            "ajax": {
                "url": "<?php echo base_url('customers/translist') ?>",
                "type": "POST",
                "data": {
                    cid: <?php echo $details['id'] ?>,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                }
            },
            "columns": [{
                    "data": null,
                    "defaultContent": ""
                }, // Index column (will be populated by rowCallback)
                {
                    "data": 0
                }, // ID
                {
                    "data": 1
                }, // Date
                {
                    "data": 2
                }, // Reference
                {
                    "data": 3
                }, // Description
                {
                    "data": 4
                }, // User
                {
                    "data": 5
                }, // Credit
                {
                    "data": 6
                }, // Debit
                {
                    "data": 7
                } // Balance
            ],
            "order": [
                [0, "desc"]
            ],
            "pageLength": 25,
            "lengthMenu": [
                [25, 50, 75, 100, -1],
                [25, 50, 75, 100, "All"]
            ],
            "paging": true,
            "searching": true,
            "info": true,
            "responsive": true,
            "language": {
                "processing": "<?php echo $this->lang->line('Processing') ?>..."
            },
            "rowCallback": function(row, data, index) {
                $('td:eq(0)', row).html(index + 1);
                return row;
            }
        });
    });
</script>