<!-- AJAX Content for Customer Credit Notes -->
<div class="card">
    <div class="card-header" style="margin-top: 10px; background-color:rgb(164, 184, 206); color: #fff; text-align: center;">
        <h2 class="card-title" style="color: #fff;">Customer Credit Notes</h2>
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
                                <table id="credit_notes_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
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
        if ($.fn.DataTable.isDataTable('#credit_notes_table')) {
            $('#credit_notes_table').DataTable().destroy();
        }

        $('#credit_notes_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo base_url('customers/creditnotes_translist') ?>",
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
                "processing": "<?php echo $this->lang->line('Processing') ?>..."
            },
            "rowCallback": function(row, data, index) {
                $('td:eq(0)', row).html(index + 1);
                return row;
            }
        });
    });
</script>