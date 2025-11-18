<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5 class="title">
                <?php echo $this->lang->line('SalaryHistory') . ' : ' . $employee['name'] ?> 
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

                        <th><?php echo $this->lang->line('Date') ?></th>
                        <th><?php echo $this->lang->line('Current') ?></th>

                        <th><?php echo $this->lang->line('Previous') ?></th>

                        <th><?php echo $this->lang->line('Change') ?></th>


                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>#</th>

                        <th><?php echo $this->lang->line('Date') ?></th>
                        <th><?php echo $this->lang->line('Current') ?></th>

                        <th><?php echo $this->lang->line('Previous') ?></th>

                        <th><?php echo $this->lang->line('Change') ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        document.title = "<?php echo $employee['name'] . ' ' . $this->lang->line('SalaryHistory'); ?>";
        
        $(document).ready(function () {
            var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
            var eid = <?php echo $eid; ?>;
            
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
                    url: '<?php echo site_url("employee/history_list"); ?>',
                    type: 'POST',
                    data: function(d) {
                        d[csrfTokenName] = csrfTokenHash;
                        d.eid = eid; // Send employee ID
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
                    { data: 1, name: 'date', orderable: true, searchable: true },
                    { data: 2, name: 'current', orderable: true, searchable: false },
                    { data: 3, name: 'previous', orderable: true, searchable: false },
                    { data: 4, name: 'change', orderable: true, searchable: false }
                ],
                order: [[1, 'desc']], // Default sort by date descending (newest first)
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

        $('.delemp').click(function (e) {
            e.preventDefault();
            $('#empid').val($(this).attr('data-object-id'));
        });
    </script>


    <div id="delete_model" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Deactive Employee</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deactivate this account ? <br><strong> It will disable this account
                            access
                            to
                            user.</strong></p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="object-id" value="">
                    <input type="hidden" id="action-url" value="employee/disable_user">
                    <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm">Confirm
                    </button>
                    <button type="button" data-dismiss="modal" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="pop_model" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('Delete'); ?></h4>
                </div>

                <div class="modal-body">
                    <form id="form_model">


                        <div class="modal-body">
                            <p>Are you sure you want to delete this employee? <br><strong> It may interrupt old
                                    invoices,
                                    disable account is a better option.</strong></p>
                        </div>
                        <div class="modal-footer">


                            <input type="hidden" class="form-control required"
                                   name="empid" id="empid" value="">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>
                            <input type="hidden" id="action-url" value="employee/delete_user">
                            <button type="button" class="btn btn-primary"
                                    id="submit_model"><?php echo $this->lang->line('Delete'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>