<style type="text/css">
    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background: #6693bc !important;
        font-weight: bold !important;
        color: #ffffff !important;
    }

    .ui-autocomplete {
        z-index: 9999;
    }

    .content-wrapper {

        z-index: 0;
    }
</style>
<!-- AJAX Content for Invoices/PO -->
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h2 class="card-title">View PO: <?php echo $details['name'] ?></h2>
            </div>
            <div class="col-md-6 text-right">
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        <li><a data-action="close"><i class="ft-x"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card-content">
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                        <!-- <div id="mybutton">

                            <div class="">
                                <a href="#sendMail" data-toggle="modal" data-remote="false" class="btn btn-primary btn-md  " data-type="reminder"><i class="icon-envelope"></i> <?php echo $this->lang->line('Send Message') ?>
                                </a>
                                <a href="<?php echo base_url('supplier/bulkpayment?id=' . $details['id']) ?>" class="btn btn-grey-blue btn-md"><i class="fa fa-money"></i> <?php echo $this->lang->line('Bulk Payment') ?>
                                </a>
                                <a href="<?php echo base_url('supplier/edit?id=' . $details['id']) ?>" class="btn btn-warning btn-md"><i class="icon-pencil"></i> <?php echo $this->lang->line('Edit Profile') ?>
                                </a>
                            </div>
                        </div> -->

                        <hr>
                        <h5><?php echo $this->lang->line('Orders') ?></h5>



                        <hr>

                        <div class="card card-block">

                            <!-- <h4><?php echo $this->lang->line('Supplier Details') ?></h4>

                            <hr> -->

                            <div class="row m-t-lg">

                                <div class="col-md-1">

                                    <strong><?php echo $this->lang->line('Name') ?></strong>

                                </div>

                                <div class="col-md-10">

                                    <?php echo $details['name'] ?>

                                </div>



                            </div>

                            <div class="row m-t-lg">

                                <div class="col-md-1">

                                    <strong><?php echo $this->lang->line('Email') ?></strong>

                                </div>

                                <div class="col-md-10">

                                    <?php echo $details['email'] ?>

                                </div>



                            </div>

                        </div>

                        <hr>

                        <table id="invoices" class="table table-striped table-bordered zero-configuration">

                            <thead>

                                <tr>

                                    <th><?php echo $this->lang->line('No') ?></th>

                                    <th><?php echo $this->lang->line('Order') ?> #</th>

                                    <th>Reference #</th>

                                    <th><?php echo $this->lang->line('Date') ?></th>

                                    <th><?php echo $this->lang->line('Total') ?></th>

                                    <th class="no-sort"><?php echo $this->lang->line('Status') ?></th>

                                    <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>





                                </tr>

                            </thead>

                            <tbody>

                            </tbody>



                            <tfoot>

                                <tr>

                                    <th><?php echo $this->lang->line('No') ?></th>

                                    <th><?php echo $this->lang->line('Order') ?> #</th>

                                    <th>Reference #</th>

                                    <th><?php echo $this->lang->line('Date') ?></th>

                                    <th><?php echo $this->lang->line('Total') ?></th>

                                    <th class="no-sort"><?php echo $this->lang->line('Status') ?></th>

                                    <th class="no-sort"><?php echo $this->lang->line('Settings') ?></th>



                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>




                <div id="delete_model" class="modal fade">

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <div class="modal-header">

                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                                <h4 class="modal-title"><?php echo $this->lang->line('Delete') ?></h4>

                            </div>

                            <div class="modal-body">

                                <p><?php echo $this->lang->line('delete this order') ?></p>

                            </div>

                            <div class="modal-footer">

                                <input type="hidden" id="object-id" value="">

                                <input type="hidden" id="action-url" value="purchase/delete_i">

                                <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>

                                <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Cancel') ?></button>

                            </div>

                        </div>

                    </div>

                </div>

                <script type="text/javascript">
                    $(document).ready(function() {



                        var table = $('#invoices').DataTable({

                            "processing": true,

                            "serverSide": true,

                            responsive: true,

                            <?php datatable_lang(); ?>

                            "order": [],

                            "ajax": {

                                "url": "<?php echo site_url('supplier/inv_list') ?>",

                                "type": "POST",

                                "data": {
                                    'cid': <?php echo $_GET['id'] ?>,
                                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                                }

                            },

                            "columnDefs": [

                                {

                                    "targets": [0],

                                    "orderable": false,

                                },

                            ],
                            dom: 'Blfrtip',

                            buttons: [

                                {

                                    extend: 'excelHtml5',

                                    footer: true,

                                    exportOptions: {

                                        columns: [1, 2, 3, 4, 5]

                                    }

                                }

                            ],



                        });



                    });
                </script>