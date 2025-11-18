<!-- AJAX Content for Invoices/PO -->
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
                    <h5><?php echo $this->lang->line('Orders') ?></h5>
                    <hr>

                    <div class="card card-block">
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
                        <tbody></tbody>
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
                    'cid': <?php echo $details['id'] ?>,
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
