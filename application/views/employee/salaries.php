<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5 class="title">
                <?php echo $this->lang->line('Employees') ?> <?php echo $this->lang->line('Salaries'); ?>
            </h5>
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

                <table id="emptable" class="table table-striped table-bordered zero-configuration" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo $this->lang->line('Name') ?></th>
                        <th><?php echo $this->lang->line('Salary') ?></th>
                        <th><?php echo $this->lang->line('Role') ?></th>
                        <th><?php echo $this->lang->line('Status') ?></th>
                        <th><?php echo $this->lang->line('Actions') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>#</th>
                        <th><?php echo $this->lang->line('Name') ?></th>
                        <th><?php echo $this->lang->line('Salary') ?></th>
                        <th><?php echo $this->lang->line('Role') ?></th>
                        <th><?php echo $this->lang->line('Status') ?></th>
                        <th><?php echo $this->lang->line('Actions') ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.title = "<?php echo $this->lang->line('Employee') . ' ' . $this->lang->line('Salaries'); ?>";
    
    $(document).ready(function () {
        var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        // Initialize DataTables with AJAX - Server-side processing
        $('#emptable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[25, 50, 75, 100, -1], [25, 50, 75, 100, "<?php echo $this->lang->line('All'); ?>"]],
            pagingType: "full_numbers",
            <?php datatable_lang(); ?>
            ajax: {
                url: '<?php echo site_url("employee/salaries_list"); ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfTokenName] = csrfTokenHash;
                    // Handle "All" option - send -1 to server
                    // Server will treat -1 as show all records
                },
                dataSrc: function(json) {
                    // Return the data array from response
                    return json.data;
                }
            },
            columns: [
                { data: 0, name: '#', orderable: true, searchable: false },
                { data: 1, name: 'name', orderable: true, searchable: true },
                { data: 2, name: 'salary', orderable: true, searchable: false },
                { data: 3, name: 'role', orderable: true, searchable: true },
                { data: 4, name: 'status', orderable: true, searchable: true },
                { data: 5, name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            dom: 'Blfrtip',
            info: true,
            paging: true,
            buttons: [{
                extend: 'excelHtml5',
                text: "<?php echo $this->lang->line('Export to Excel'); ?>",
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }],
            drawCallback: function(settings) {
                // Handle "All" option (-1)
                var api = this.api();
                var pageLen = api.page.len();
                if (pageLen == -1) {
                    $('.dataTables_length select').val(-1);
                }
            }
        });
    });
</script>