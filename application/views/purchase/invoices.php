<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?php echo $this->lang->line('Purchase Order') ?>  <?php if ($this->aauth->permission_new(null, 'purchaseCreateOrder')) { ?><a href="<?php echo base_url('purchase/create') ?>" class="btn btn-primary btn-sm rounded">
                    <?php echo $this->lang->line('Add new') ?></a><?php } ?></h4>
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

                    <div class="col-md-2"><?php echo $this->lang->line('Date') ?></div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" id="start_date" class="date30 form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>

                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                    </div>

                </div>
                <hr>

                <table id="po" class="table table-striped table-bordered zero-configuration">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th>Order #</th>
                            <th>Reference #</th>
                            <th><?php echo $this->lang->line('Supplier') ?></th>
                            <th>Order Date</th>
                            <!--  <th><?php echo $this->lang->line('Amount') ?></th> -->
                            <th>Verification</th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th>Order #</th>
                            <th>Reference #</th>
                            <th><?php echo $this->lang->line('Supplier') ?></th>
                            <th>Order Date</th>
                            <!--  <th><?php echo $this->lang->line('Amount') ?></th> -->
                            <th>Verification</th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>


    </div>
    <div id="delete_model" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title"><?php echo $this->lang->line('Delete Order') ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><?php echo $this->lang->line('delete this order') ?></p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="object-id" value="">
                    <input type="hidden" id="action-url" value="purchase/delete_i">
                    <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm">Delete
                    </button>
                    <button type="button" data-dismiss="modal" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        document.title = 'Purchase Order';
        $(document).ready(function() {
            // Keep end_date always enabled
            var $endDate = $('#end_date');
            var $startDate = $('#start_date');
            
            function enableEndDate() {
                if ($endDate.length) {
                    $endDate.prop('disabled', false).css('opacity', '1');
                    $endDate.removeAttr('disabled');
                }
            }
            
            // Enable immediately
            enableEndDate();
            
            // Watch for start_date changes and enable end_date
            $startDate.on('change input', function() {
                enableEndDate();
            });
            
            // Also listen to native date input events
            var startDateElement = document.getElementById('start_date');
            if (startDateElement) {
                startDateElement.addEventListener('change', enableEndDate);
                startDateElement.addEventListener('input', enableEndDate);
            }
            
            // Use MutationObserver to watch for disabled attribute changes
            if (typeof MutationObserver !== 'undefined' && $endDate.length) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && 
                            (mutation.attributeName === 'disabled' || mutation.attributeName === 'style')) {
                            enableEndDate();
                        }
                    });
                });
                
                observer.observe($endDate[0], {
                    attributes: true,
                    attributeFilter: ['disabled', 'style']
                });
            }
            
            // Periodic check as fallback
            setInterval(enableEndDate, 200);
            
            // Re-enable after delays to catch flatpickr initialization
            setTimeout(enableEndDate, 500);
            setTimeout(enableEndDate, 1000);
            setTimeout(enableEndDate, 2000);
            
            // Get initial date values from input fields
            var initial_start_date = $('#start_date').val() || '';
            var initial_end_date = $('#end_date').val() || '';
            draw_data(initial_start_date, initial_end_date);

            function draw_data(start_date = '', end_date = '') {
                $('#po').DataTable({
                    'processing': true,
                    'serverSide': true,
                    'stateSave': true,
                    responsive: true,
                    <?php datatable_lang(); ?> 'order': [],
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [
                        [25, 50, 75, 100, -1],
                        [25, 50, 75, 100, 'All']
                    ],
                    'ajax': {
                        'url': "<?php echo site_url('purchase/ajax_list') ?>?param=<?= $param ?>",
                        'type': 'POST',
                        'data': {
                            '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                            start_date: start_date,
                            end_date: end_date
                        }
                    },
                    'columnDefs': [{
                        'targets': [0],
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
                });
            };

            $('#search').click(function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                if (start_date != '' && end_date != '') {
                    $('#po').DataTable().destroy();
                    draw_data(start_date, end_date);
                } else {
                    alert("Date range is Required");
                }
            });
        });
    </script>