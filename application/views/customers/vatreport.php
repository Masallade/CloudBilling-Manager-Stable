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
<div class="content-body">
    <div class="card">
        <!-- <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <h2 class="card-title">Customer Account
                        : <?php echo $details['name'] ?>
                    </h2>
                </div>


                <div class="col-md-6">
                    <div class="col-md-8">




                        <div class="input-group">

                            <input type="text" class="form-control" name="cst" id="customer-box"
                                placeholder="Enter Customer Code"
                                autocomplete="off" />
                            <span class="input-group-btn">
                                <button class="btn btn-primary" id="search-client-button" type="button">Search</button>
                                <input type="hidden" name="payer_id" id="customer_id" value="0">
                            </span>
                        </div>
                    </div>
                </div>




                <div class="col-md-3">
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
        </div> -->
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!isset($is_ajax) || !$is_ajax): ?>
                        <div class="col-md-2 border-right border-right-grey">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <a href="<?php echo base_url('customers/view?id=' . $details['id']) ?>"
                                        class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-user"></i> View Details</a>
                                    <a href="<?php echo base_url('customers/invoices?id=' . $details['id']) ?>"
                                        class="btn btn-success btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-file-text"></i> <?php echo $this->lang->line('View Invoices') ?>
                                    </a>
                                    <a href="<?php echo base_url('customers/transactions?id=' . $details['id']) ?>"
                                        class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block  btn-lighten-1"><i
                                            class="fa fa-money"></i> View Activity
                                    </a>
                                    <a href="<?php echo base_url('customers/transactions_credit_notes?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-md mr-1 mb-1 btn-block  btn-lighten-1" style="background-color: #7c51a1 !important;"><i
                                            class="fa fa-money"></i> View Credit Notes
                                    </a>

                                    <a href="<?php echo base_url('customers/transactions_advance_payments?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-md mr-1 mb-1 btn-block  btn-lighten-1" style="background-color: #FFA87D !important;border-color: #FF976A !important;"><i
                                            class="fa fa-money"></i> View Advance Payments
                                    </a>
                                    <a href="<?php echo base_url('customers/statement?id=' . $details['id']) ?>"
                                        class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i
                                            class="fa fa-briefcase"></i> <?php echo $this->lang->line('Account Statements') ?>
                                    </a>
                                    <a href="<?php echo base_url('customers/balanceinvoice?id=' . $details['id']) ?>" target="_blank"
                                        class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-print"></i>
                                        Print Balance Invoice
                                    </a>
                                    <a href="<?php echo base_url('customers/vatreport') ?>"
                                        class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-print"></i>
                                        Print VAT Report
                                    </a>


                                    <!--     <a href="<?php echo base_url('customers/quotes?id=' . $details['id']) ?>"
                                   class="btn btn-purple btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-quote-left"></i> Add Balance
                                </a>  -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10">
                        <?php else: ?>
                            <div class="col-md-12">
                            <?php endif; ?>
                            <h5 class="mb-2">VAT Report</h5>
                            <hr>
                            <form action="<?php echo base_url() ?>customers/vatinvoice" method="post" target="_blank" role="form">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                    value="<?php echo $this->security->get_csrf_hash(); ?>">
                                <input type="hidden" name="customer" value="<?= $id ?>">
                                <!--div class="form-group row">
                                <label class="col-sm-3 col-form-label"
                                       for="pay_cat"><?php echo $this->lang->line('Type') ?></label>
                                <div class="col-sm-9">
                                    <select name="trans_type" class="form-control">
                                        <option value='All'><?php echo $this->lang->line('All Transactions') ?></option>
                                        <option value='Expense'><?php echo $this->lang->line('Debit') ?></option>
                                        <option value='Income'><?php echo $this->lang->line('Credit') ?></option>
                                    </select>
                                </div>
                            </div -->
                                <div class="form-group row">

                                    <label class="col-sm-3 control-label"
                                        for="sdate"><?php echo $this->lang->line('From Date') ?></label>

                                    <div class="col-sm-4">
                                        <input type="date" class="form-control required"
                                            placeholder="Start Date" name="sdate" id="sdate"
                                            autocomplete="false">
                                    </div>
                                </div>
                                <div class="form-group row">

                                    <label class="col-sm-3 control-label"
                                        for="edate"><?php echo $this->lang->line('To Date') ?></label>

                                    <div class="col-sm-4">
                                        <input type="date" class="form-control required"
                                            placeholder="End Date" name="edate"
                                            autocomplete="false">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label" for="pay_cat"></label>
                                    <div class="col-sm-4">
                                        <input type="submit" class="btn btn-primary btn-md"
                                            value="<?php echo $this->lang->line('View') ?>">
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
                        <form id="sendmail_form"><input type="hidden"
                                name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <div class="row">
                                <div class="col">
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-envelope-o"
                                                aria-hidden="true"></span></div>
                                        <input type="text" class="form-control" placeholder="Email" name="mailtoc"
                                            value="<?php echo $details['email'] ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-1"><label
                                        for="shortnote"><?php echo $this->lang->line('Customer Name') ?></label>
                                    <input type="text" class="form-control"
                                        name="customername" value="<?php echo $details['name'] ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-1"><label
                                        for="shortnote"><?php echo $this->lang->line('Subject') ?></label>
                                    <input type="text" class="form-control"
                                        name="subject" id="subject">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-1"><label
                                        for="shortnote"><?php echo $this->lang->line('Message') ?></label>
                                    <textarea name="text" class="summernote" id="contents" title="Contents"></textarea>
                                </div>
                            </div>

                            <input type="hidden" class="form-control"
                                id="cid" name="tid" value="<?php echo $details['id'] ?>">
                            <input type="hidden" id="action-url" value="communication/send_general">


                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                        <button type="button" class="btn btn-primary"
                            id="sendNow"><?php echo $this->lang->line('Send') ?></button>
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