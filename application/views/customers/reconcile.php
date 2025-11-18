<?php
$due = false;
if ($this->input->get('due')) {
    $due = true;
} ?>

<style>
    #clientstable tr>td:nth-child(8),
    #clientstable tr>th:nth-child(8) {
        display: none;
    }
</style>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Bank Reconcile

        </div>
    </div>
    <div class="card-content">
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>

            <div class="message"></div>
        </div>
        <div class="card-body">

            <table id="clientstable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <div class="row align-items-center">
                    <!-- Form is always visible -->
                    <div class="col-md-6">
                        <form action="your_action_url" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="csv_file">Bank CSV File:</label>
                                <input type="file" name="csv_file" accept=".csv" id="csv_file" required />
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <h3>
                            <span>Total Balance <br> <?php echo amountExchange($ttlBalance); ?></span>
                        </h3>
                    </div>
                    <div class="col-md-2">
                    <button type="submit" class="btn btn-success">Reconcile</button>
                    </div>
                </div>

                <thead>
                    <tr>
                        <!-- <th>Select</th> -->
                        <th> A/C #<?php  //echo $this->lang->line('Name') 
                                    ?></th>
                        <?php if ($due) {
                            echo '  <th>' . $this->lang->line('Due') . '</th>';
                        } ?>
                        <th>Customer <?php //echo $this->lang->line('Address') 
                                        ?></th>
                        <th> <?php echo $this->lang->line('Address') ?></th>
                        <th><?php echo 'Balance(GBP)'; //$this->lang->line('Email') 
                            ?></th>
                        <!-- <th><?php echo $this->lang->line('Phone') ?></th> -->
                        <th>Last Invoice Date</th>
                        <th>Payment Terms</th>
                        <!-- <th><?php echo $this->lang->line('Settings') ?></th> -->


                    </tr>
                </thead>
                <tbody>
                </tbody>

                <tfoot>
                    <tr>
                        <!-- <th>Select</th> -->
                        <th>A/C #<?php  //echo $this->lang->line('Name') 
                                    ?></th>
                        <?php if ($due) {
                            echo '  <th>' . $this->lang->line('Due') . '</th>';
                        } ?>
                        <th>Customer<?php //echo $this->lang->line('Address') 
                                    ?></th>
                        <th> <?php echo $this->lang->line('Address') ?></th>
                        <th>Balance(GBP)</th>
                        <!-- <th><?php echo $this->lang->line('Phone') ?></th> -->
                        <th>Last Invoice Date</th>
                        <th>Payment Terms</th>
                        <!-- <th><?php echo $this->lang->line('Settings') ?></th> -->


                    </tr>
                </tfoot>
            </table>

        </div>
    </div>
</div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#toggleFormBtn').click(function() {
            $('#').toggle(); // Toggle visibility of the form
        });
        $('.summernote').summernote({
            height: 100,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['fullscreen', ['fullscreen']],
                ['codeview', ['codeview']]
            ]
        });



        $('#clientstable').DataTable({
            'processing': true,
            'serverSide': false,
            'stateSave': true,
            'bSortable': true,
            'bRetrieve': true,

            //   "bPaginate": true,
            "bFilter": true,
            "bInfo": false,


            aoColumnDefs: [{
                    "aTargets": [0],
                    "bSortable": true
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
                    "bSortable": false
                },
                {
                    "aTargets": [4],
                    "bSortable": true
                },
                {
                    "aTargets": [5],
                    "bSortable": true
                },
                // { "aTargets": [ 7 ], "bSortable": false },
                // { "aTargets": [ 8 ], "bSortable": false },

            ],


            responsive: true,
            'pageLength': -1,
            bInfo: false,
            <?php datatable_lang(); ?> 'order': [],
            'ajax': {
                'url': "<?php echo site_url('customers/activitylist_') ?>",
                'type': 'POST',
                'data': {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash <?php if ($due) echo ",'due':true" ?>
                }
            },
            'columnDefs': [{
                'targets': [0],
                'orderable': true,
            }, ],
            dom: 'Blfrtip',
            buttons: [
                // Remove or comment out these lines to hide the export buttons
                /*
                {
                    extend: 'pdfHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [1,2,3,4,5,6,7]
                    }
                }
                */
            ],
            // aLengthMenu: [],
            "createdRow": function(row, data, dataIndex) {
                // Assuming column 9 index is 9
                if (data[9] === false) {
                    $(row).css({
                        'background-color': 'rgb(149 149 149)',
                    });
                }
            }
        });

        var table = $('#clientstable').DataTable();

        $('.dataTables_filter input[type="search"]').unbind().keyup(function(e) {
            var value = $(this).val().toUpperCase();
            table.search(value).draw();
        });






        $('#clientstable').DataTable().search('').draw();
    });
</script>