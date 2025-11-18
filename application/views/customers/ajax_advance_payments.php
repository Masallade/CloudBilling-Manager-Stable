<!-- AJAX Content for Customer Advance Payments -->
<div class="card">
    <div class="card-header" style="margin-top: 10px; background-color:rgb(164, 184, 206); color: #fff; text-align: center;">
        <h2 class="card-title" style="color: #fff;">Customer Advance Payments</h2>
    </div>
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
                                <table id="advance_payments_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo $this->lang->line('ID') ?></th>
                                            <th><?php echo $this->lang->line('Date') ?></th>
                                            <th><?php echo $this->lang->line('Amount') ?></th>
                                            <th><?php echo $this->lang->line('Description') ?></th>
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
        if ($.fn.DataTable.isDataTable('#advance_payments_table')) {
            $('#advance_payments_table').DataTable().destroy();
        }

        $('#advance_payments_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo base_url('customers/advancepaymnets_translist') ?>",
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
                }, // Amount
                {
                    "data": 3
                } // Description
            ],
            "order": [
                [0, "desc"]
            ],
            "pageLength": 25,
            "lengthMenu": [
                [25, 50, 75, 100],
                [25, 50, 75, 100]
            ],
            "paging": true,
            "searching": true,
            "info": true,
            "responsive": true,
            "language": {
                "processing": "<?php echo $this->lang->line('Processing') ?>...",
                "lengthMenu": "Show _MENU_ entries",
                "zeroRecords": "No matching records found",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "search": "Search:",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "rowCallback": function(row, data, index) {
                $('td:eq(0)', row).html(index + 1);
                return row;
            }
        });
    });
</script>