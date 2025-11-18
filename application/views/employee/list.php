<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5 class="title">
                <?php echo $this->lang->line('Employee') ?> 
                <a href="<?php echo site_url('employee/add') ?>" class="btn btn-primary btn-sm rounded">
                    <?php echo $this->lang->line('Add new') ?>
                </a>
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
            <!-- Notification Alert -->
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><?php echo $this->lang->line('Success'); ?>:</strong> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><?php echo $this->lang->line('ERROR'); ?>:</strong> <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card-body">
                <!-- Employee DataTable -->
                <table id="emptable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo $this->lang->line('Username') ?></th>
                            <th><?php echo $this->lang->line('Email') ?></th>
                            <th><?php echo $this->lang->line('Role') ?></th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th><?php echo $this->lang->line('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th><?php echo $this->lang->line('Username') ?></th>
                            <th><?php echo $this->lang->line('Email') ?></th>
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

<!-- Employee Action Modal (Enable/Disable) -->
<div id="employee_action_modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title" id="modal-title">Employee Action Confirmation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                <p id="modal-message">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="confirm-action">
                    <i class="fa fa-spinner fa-spin" style="display:none;"></i>
                    <span class="btn-text">Confirm</span>
                    </button>
                <button type="button" data-dismiss="modal" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    </div>

<!-- Delete Employee Modal -->
    <div id="pop_model" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo $this->lang->line('Delete'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form_model">
                        <div class="modal-body">
                        <p>Are you sure you want to delete this employee? <br><strong>It may interrupt old invoices, disable account is a better option.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" class="form-control required" name="empid" id="empid" value="">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close'); ?></button>
                        <button type="button" class="btn btn-primary" id="submit_model"><?php echo $this->lang->line('Delete'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
                        </div>

<script type="text/javascript">
document.title = "<?php echo $this->lang->line('Employees') ?>";

// Employee Management Module
const EmployeeManager = {
    table: null,
    csrfToken: '<?php echo $this->security->get_csrf_token_name(); ?>',
    csrfHash: '<?php echo $this->security->get_csrf_hash(); ?>',
    baseUrl: '<?php echo site_url(""); ?>',
    
    // Initialize DataTable
    initDataTable() {
        this.table = $('#emptable').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            <?php datatable_lang(); ?> 
            dom: 'Blfrtip',
            ajax: {
                url: '<?php echo site_url('employee/employee_list')?>',
                type: 'POST',
                data: (d) => {
                    d[this.csrfToken] = this.csrfHash;
                }
            },
            buttons: [{
                extend: 'excelHtml5',
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }],
            columnDefs: [
                { orderable: false, targets: [5] } // Actions column
            ],
            order: [[0, 'asc']]
        });
    },
    
    // Show notification
    showNotification(message, type = 'success') {
        const $notify = $('#notify');
        $notify.removeClass('alert-success alert-danger')
               .addClass(`alert-${type}`)
               .find('.message').html(message)
               .end().show();
        
        // Auto-hide after 5 seconds
        setTimeout(() => $notify.fadeOut(), 5000);
    },
    
    // Refresh table
    refreshTable() {
        setTimeout(() => {
            this.table.ajax.reload(null, false);
        }, 300);
    },
    
    // Handle employee actions
    handleAction(action, employeeId) {
        const config = {
            enable: {
                title: 'Enable Employee',
                message: 'Are you sure you want to <strong>enable</strong> this employee account?<br><strong>This will restore access to the user.</strong>',
                url: 'employee/enable_user',
                buttonText: 'Enable'
            },
            disable: {
                title: 'Disable Employee',
                message: 'Are you sure you want to <strong>disable</strong> this employee account?<br><strong>This will revoke access from the user.</strong>',
                url: 'employee/disable_user',
                buttonText: 'Disable'
            },
            delete: {
                title: 'Delete Employee',
                message: 'Are you sure you want to <strong>delete</strong> this employee?<br><strong>It may interrupt old invoices. Disabling account is a better option.</strong>',
                url: 'employee/delete_user',
                buttonText: 'Delete'
            }
        };
        
        const actionConfig = config[action];
        if (!actionConfig) return;
        
        $('#modal-title').text(actionConfig.title);
        $('#modal-message').html(actionConfig.message);
        $('#action-url').val(actionConfig.url);
        $('#object-id').val(employeeId);
        $('.btn-text').text(actionConfig.buttonText);
        
        $('#employee_action_modal').modal('show');
    },
    
    // Execute action via AJAX
    executeAction() {
        const $btn = $('#confirm-action');
        const $spinner = $btn.find('.fa-spinner');
        const $text = $btn.find('.btn-text');
        const actionUrl = $('#action-url').val();
        const employeeId = $('#object-id').val();
        
        // Show loading state
        $btn.prop('disabled', true);
        $spinner.show();
        $text.text('Processing...');
        
        $.ajax({
            url: this.baseUrl + actionUrl,
            type: 'POST',
            data: {
                deleteid: employeeId,
                [this.csrfToken]: this.csrfHash
            },
            dataType: 'json',
            success: (response) => {
                if (response.status === 'Success') {
                    this.showNotification(response.message, 'success');
                    this.refreshTable();
                    $('#employee_action_modal').modal('hide');
                } else {
                    // Show error message with better styling
                    this.showNotification(response.message || 'An error occurred', 'danger');
                    $('#employee_action_modal').modal('hide');
                }
            },
            error: () => {
                this.showNotification('An error occurred. Please try again.', 'danger');
            },
            complete: () => {
                $btn.prop('disabled', false);
                $spinner.hide();
                $text.text('Confirm');
            }
        });
    }
};

// Initialize when document is ready
$(document).ready(function() {
    EmployeeManager.initDataTable();
    
    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        $('.alert-success, .alert-danger').fadeOut('slow');
    }, 5000);
    
    // Event delegation for dynamic content
    $(document).on('click', '.manage-object', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const employeeId = $btn.data('object-id');
        const action = $btn.hasClass('btn-info') ? 'enable' : 'disable';
        EmployeeManager.handleAction(action, employeeId);
    });
    
    $(document).on('click', '.delemp', function(e) {
        e.preventDefault();
        $('#empid').val($(this).data('object-id'));
        $('#pop_model').modal('show');
    });
    
    // Confirm action handler for enable/disable
    $('#confirm-action').click(function() {
        EmployeeManager.executeAction();
    });
    
    // Delete action handler
    $('#submit_model').click(function() {
        const employeeId = $('#empid').val();
        const $btn = $(this);
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '<?php echo site_url("employee/delete_user"); ?>',
            type: 'POST',
            data: {
                empid: employeeId,
                [EmployeeManager.csrfToken]: EmployeeManager.csrfHash
            },
            dataType: 'json',
            success: (response) => {
                if (response.status === 'Success') {
                    EmployeeManager.showNotification(response.message, 'success');
                    EmployeeManager.refreshTable();
                    $('#pop_model').modal('hide');
                } else {
                    // Show error message with better styling
                    EmployeeManager.showNotification(response.message || 'An error occurred', 'danger');
                    $('#pop_model').modal('hide');
                }
            },
            error: () => {
                EmployeeManager.showNotification('An error occurred. Please try again.', 'danger');
            },
            complete: () => {
                $btn.prop('disabled', false).html('<?php echo $this->lang->line('Delete'); ?>');
            }
        });
    });
    
    // Auto-hide notifications on close
    $(document).on('click', '.close', function() {
        $(this).closest('.alert').fadeOut();
    });
});
</script>