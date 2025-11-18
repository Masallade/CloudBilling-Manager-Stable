<!-- AJAX Content for Customer Invoices -->
<div class="card">
    <div class="card-header" style="margin-top: 10px; background-color:rgb(164, 184, 206); color: #fff; text-align: center;">
        <h2 class="card-title" style="color: #fff;">Customer Invoices</h2>
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
                                <table id="invoices_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo $this->lang->line('Invoice') ?>#</th>
                                            <th><?php echo $this->lang->line('Date') ?></th>
                                            <th><?php echo $this->lang->line('Amount') ?></th>
                                            <th><?php echo $this->lang->line('Status') ?></th>
                                            <th><?php echo $this->lang->line('Actions') ?></th>
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
        if ($.fn.DataTable.isDataTable('#invoices_table')) {
            $('#invoices_table').DataTable().destroy();
        }

        $('#invoices_table').DataTable({
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
                    "bSortable": true,
                    "searchable": true
                },
                {
                    "aTargets": [2],
                    "bSortable": true,
                    "searchable": true
                },
                {
                    "aTargets": [3],
                    "bSortable": true,
                    "searchable": true
                },
                {
                    "aTargets": [4],
                    "bSortable": true,
                    "searchable": true
                },
                {
                    "aTargets": [5],
                    "bSortable": false,
                    "searchable": false
                }
            ],
            "processing": true,
            "serverSide": true,
            "order": [
                [1, "desc"]
            ],
            "ajax": {
                "url": "<?php echo base_url('customers/inv_list') ?>",
                "type": "POST",
                "data": {
                    cid: <?php echo $details['id'] ?>,
                    tyd: '',
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                "error": function(xhr, error, thrown) {
                    console.log('DataTables AJAX error:', error, thrown);
                    console.log('Response:', xhr.responseText);
                    
                    // Try to parse error response
                    var errorMessage = 'Error loading data. Please try again.';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            errorMessage = response.error;
                        }
                    } catch (e) {
                        // If not JSON, use the response text if available
                        if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }
                    }
                    
                    alert(errorMessage);
                }
            },
            "columns": [{
                    "data": null,
                    "defaultContent": ""
                }, // Index column (will be populated by rowCallback)
                {
                    "data": 0
                }, // Invoice #
                {
                    "data": 1
                }, // Date
                {
                    "data": 2
                }, // Amount
                {
                    "data": 3
                }, // Status
                {
                    "data": 4
                } // Actions
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
            "stateSave": false,
            "deferRender": true,
            "language": {
                "processing": "<?php echo $this->lang->line('Processing') ?>...",
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "rowCallback": function(row, data, index) {
                var api = $('#invoices_table').DataTable();
                var pageInfo = api.page.info();
                $('td:eq(0)', row).html(pageInfo.start + index + 1);
                return row;
            }
        });
    });
</script>