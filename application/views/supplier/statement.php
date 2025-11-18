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
<!-- AJAX Content for Statement -->
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h2 class="card-title">Account Statements: <?php echo $details['name'] ?></h2>
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
                        <h5 class="mb-2"><?= $this->lang->line('Account Statement'); ?></h5>
                        <hr>
                        <form action="<?php echo base_url() ?>supplier/statement" method="post" target="_blank" role="form">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <input type="hidden" name="supplier" value="<?= $details['id'] ?>">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pay_cat"><?php echo $this->lang->line('Type') ?></label>
                                <div class="col-sm-9">
                                    <select name="trans_type" class="form-control">
                                        <option value='All'><?php echo $this->lang->line('All Transactions') ?></option>
                                        <option value='Expense'><?php echo $this->lang->line('Debit') ?></option>
                                        <option value='Income'><?php echo $this->lang->line('Credit') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="sdate"><?php echo $this->lang->line('From Date') ?></label>

                                <div class="col-sm-4">
                                    <input type="date" class="form-control required" placeholder="Start Date" name="sdate" id="sdate" autocomplete="false" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="edate"><?php echo $this->lang->line('To Date') ?></label>

                                <div class="col-sm-4">
                                    <input type="date" class="form-control required" placeholder="End Date" name="edate" autocomplete="false" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pay_cat"></label>
                                <div class="col-sm-4">
                                    <input type="submit" class="btn btn-primary btn-md mr-1" value="View in PDF">
                                    <input type="submit" class="btn btn-info btn-md" value="View in Excel" name="excel">
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="sendMail" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Email</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="sendmail_form"><input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="row">
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-envelope-o" aria-hidden="true"></span></div>
                                    <input type="text" class="form-control" placeholder="Email" name="mailtoc" value="<?php echo $details['email'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Customer Name') ?></label>
                                <input type="text" class="form-control" name="customername" value="<?php echo $details['name'] ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Subject') ?></label>
                                <input type="text" class="form-control" name="subject" id="subject">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label for="shortnote"><?php echo $this->lang->line('Message') ?></label>
                                <textarea name="text" class="summernote" id="contents" title="Contents"></textarea>
                            </div>
                        </div>

                        <input type="hidden" class="form-control" id="cid" name="tid" value="<?php echo $details['id'] ?>">
                        <input type="hidden" id="action-url" value="communication/send_general">


                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                    <button type="button" class="btn btn-primary" id="sendNow"><?php echo $this->lang->line('Send') ?></button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('#sdate_2').datepicker('setDate', '<?php echo date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))); ?>');
        $("#search-client-button").click(function() {
            var c_id = $("#customer_id").val();
            if (c_id != 0) {
                base_url = '<?= base_url(); ?>' + '/customers/view?id=' + c_id;
                window.location.href = base_url;

            }


        });
    </script>