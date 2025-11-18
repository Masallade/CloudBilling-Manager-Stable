<div class="content-body">

    <?php if ($this->session->flashdata('success')): ?>

        <div id="notify" class="alert alert-success">

            <a href="#" class="close" data-dismiss="alert">×</a>

            <div class="message">

                <?= $this->session->flashdata('success'); ?>

            </div>

        </div>

    <?php endif; ?>



    <div class="card">

        <div class="card-header">

            <h5 class="title d-flex justify-content-between align-items-center">

                <span>

                    <?php echo $this->lang->line('RolesPermissions'); ?>

                    <?php if ($this->aauth->permission_new(null, 'usersEmployeesPermissionsAdd')): ?>

                        <a href="<?php echo base_url('employee/add_role_permissions'); ?>" class="btn btn-primary btn-sm rounded">

                            <?php echo $this->lang->line('Add new'); ?>

                        </a>

                    <?php endif; ?>

                </span>

            </h5>

            <div class="heading-elements d-flex justify-content-end">

                <ul class="list-inline mb-0">

                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>

                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>

                    <li><a data-action="close"><i class="ft-x"></i></a></li>

                </ul>

            </div>

        </div>

        <div class="card-content">

            <div id="notify" class="alert alert-success" style="display:none;">

                <a href="#" class="close" data-dismiss="alert">×</a>

                <div class="message"></div>

            </div>

            <div class="card-body">

                <table id="emptable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th><?php echo $this->lang->line('Role') ?></th>

                            <th><?php echo $this->lang->line('Description') ?></th>

                            <th><?php echo $this->lang->line('Actions') ?></th>

                        </tr>

                    </thead>

                    <tbody>

                        <!-- Leave this empty; data will be populated via AJAX -->

                    </tbody>

                    <tfoot>

                        <tr>

                            <th>#</th>

                            <th><?php echo $this->lang->line('Role') ?></th>

                            <th><?php echo $this->lang->line('Description') ?></th>

                            <th><?php echo $this->lang->line('Actions') ?></th>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>



    <script type="text/javascript">

        document.title = "<?= $this->lang->line('RolesPermissions'); ?>";

        $(document).ready(function() {

            var base_url = "<?php echo base_url(); ?>";



            // Add CSRF token to AJAX headers

            var csrf_token = "<?php echo $this->security->get_csrf_hash(); ?>";

            $.ajaxSetup({

                data: {

                    '<?php echo $this->security->get_csrf_token_name(); ?>': csrf_token

                }

            });



            // Initialize DataTable with server-side processing

            var table = $('#emptable').DataTable({

                responsive: true,

                serverSide: true, // Enable server-side processing
                lengthMenu: [
                    [25, 50, 75, 100, -1],
                    [25, 50, 75, 100, '<?php echo $this->lang->line('All'); ?>']
                ],
                pageLength: 25,

                ajax: {

                    url: base_url + 'employee/get_roles', // Endpoint to fetch table data

                    type: 'POST',

                    data: function(d) {

                        d['<?php echo $this->security->get_csrf_token_name(); ?>'] = csrf_token;

                    }

                },

                'order': [],
                'columnDefs': [
                    {
                        'targets': [0],
                        'orderable': false,
                    },
                ],

                <?php datatable_lang(); ?>

                dom: 'Blfrtip',

                buttons: [{

                    extend: 'excelHtml5',

                    footer: true,

                    exportOptions: {

                        columns: [0, 1, 2, 3]

                    }

                }]

            });



            // Handle delete button click event

            $('#emptable').on('click', '.delemp', function(e) {

                e.preventDefault();

                var role_id = $(this).attr('data-object-id'); // Get the role ID

                $('#role_id').val(role_id); // Set the role_id in hidden input field in modal

            });



            // Confirm deletion action

            $('#submit_delete_model').click(function() {

                var role_id = $('#role_id').val(); // Get role ID from hidden input

                var action_url = $('#action-url').val(); // Get action URL



                $.ajax({

                    url: base_url + action_url,

                    type: 'POST',

                    data: {

                        role_id: role_id

                    },

                    success: function(response) {

                        var res = JSON.parse(response);

                        if (res.status === 'success') {

                            showNotification('success', res.message);

                            table.ajax.reload(null, false); // Reload the table after deletion

                            $('#pop_model').modal('hide'); // Close the modal

                        } else {

                            showNotification('error', res.message);

                        }

                    },

                    error: function() {

                        showNotification('error', 'An error occurred while trying to delete the role.');

                    }

                });

            });



            // Function to show notification

            function showNotification(type, message) {

                var notifyDiv = $('#notify');

                notifyDiv.removeClass('alert-success alert-danger')

                    .addClass('alert-' + type)

                    .find('.message').text(message);

                notifyDiv.show();

                setTimeout(function() {

                    notifyDiv.hide();

                }, 5000); // Hide notification after 5 seconds

            }

        });

    </script>



    <div id="pop_model" class="modal fade">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title"><?php echo $this->lang->line('Delete'); ?></h4>

                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                </div>

                <div class="modal-body">

                    <form id="form_model">

                        <div class="modal-body">

                            <p>Are you sure you want to delete this role and permissions? <br><strong>This action cannot be undone.</strong></p>

                        </div>

                        <div class="modal-footer">

                            <input type="hidden" class="form-control required" name="role_id" id="role_id" value="">

                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>

                            <input type="hidden" id="action-url" value="employee/delete_role">

                            <button type="button" class="btn btn-primary" id="submit_delete_model"><?php echo $this->lang->line('Delete'); ?></button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>