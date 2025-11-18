<!-- AJAX Content for Transactions -->
<div class="card">
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
                            <a href="#sendMail" data-toggle="modal" data-remote="false" class="btn btn-primary btn-md" data-type="reminder"><i class="icon-envelope"></i> <?php echo $this->lang->line('Send Message') ?></a>
                            <a href="<?php echo base_url('supplier/bulkpayment?id=' . $details['id']) ?>" class="btn btn-grey-blue btn-md"><i class="fa fa-money"></i> <?php echo $this->lang->line('Bulk Payment') ?></a>
                            <a href="<?php echo base_url('supplier/edit?id=' . $details['id']) ?>" class="btn btn-warning btn-md"><i class="icon-pencil"></i> <?php echo $this->lang->line('Edit Profile') ?></a>
                        </div>
                    </div> -->

                    <hr>

                    <div class="card card-block">
                        <h4>Activities</h4>
                        <hr>

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
                                <strong>Email</strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['email'] ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <table id="ts_table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('Date') ?></th>
                                <th>Reference #</th>
                                <th><?php echo $this->lang->line('Debit') ?></th>
                                <th><?php echo $this->lang->line('Credit') ?></th>
                                <th><?php echo $this->lang->line('Account') ?></th>
                                <th><?php echo $this->lang->line('Details') ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th><?php echo $this->lang->line('Date') ?></th>
                                <th>Reference #</th>
                                <th><?php echo $this->lang->line('Debit') ?></th>
                                <th><?php echo $this->lang->line('Credit') ?></th>
                                <th><?php echo $this->lang->line('Account') ?></th>
                                <th><?php echo $this->lang->line('Details') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var table;

    $(document).ready(function() {
        //datatables
        table = $('#ts_table').DataTable({
            "processing": true, //Feature control the processing indicator.
            "serverSide": true, //Feature control DataTables' server-side processing mode.
            "order": [], //Initial no order.
            responsive: true,
            <?php datatable_lang(); ?>

            // Load data for the table's content from an Ajax source
            "ajax": {
                "url": "<?php echo site_url('supplier/translist') ?>",
                "type": "POST",
                "data": {
                    'cid': <?php echo $details['id'] ?>,
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                }
            },

            //Set column definition initialisation properties.
            "columnDefs": [
                {
                    "targets": [0], //first column / numbering column
                    "orderable": true, //set not orderable
                },
            ],
        });
    });
</script>
