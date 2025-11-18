<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?php echo $this->lang->line('Supplier Details') ?>
                : <?php echo $details['name'] ?></h4>
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
                    <!-- <div class="col-md-4 border-right border-right-grey">


                        <div class="ibox-content mt-2">
                            <img alt="image" id="dpic" class="rounded-circle img-border height-150" src="<?php echo base_url('userfiles/customers/') . $details['picture'] ?>">
                        </div>
                        <hr>


                        <div class="row mt-3">
                            <div class="col-md-12">
                                <a href="<?php echo base_url('supplier/view?id=' . $details['id']) ?>" class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1"><i class="fa fa-user"></i> <?php echo $this->lang->line('View') ?></a>
                                <a href="<?php echo base_url('supplier/invoices?id=' . $details['id']) ?>" class="btn btn-success btn-md mr-1 mb-1 btn-block btn-lighten-1"><i class="fa fa-file-text"></i> <?php echo $this->lang->line('View Purchase Orders') ?>
                                </a>
                                <a href="<?php echo base_url('supplier/transactions?id=' . $details['id']) ?>" class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block  btn-lighten-1"><i class="fa fa-money"></i> <?php echo $this->lang->line('View Transactions') ?>
                                </a>


                            </div>
                        </div>


                    </div> -->
                    <div class="col-md-2 border-right border-right-grey">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <a href="<?php echo base_url('supplier/view?id=' . $details['id']) ?>" class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1"><i class="fa fa-user"></i> View Details</a>
                                <a href="<?php echo base_url('supplier/invoices?id=' . $details['id']) ?>" class="btn btn-dark btn-md mr-1 mb-1 btn-block btn-lighten-1"><i class="fa fa-file-text"></i> View PO</a>
                                <a href="<?php echo base_url('supplier/transactions?id=' . $details['id']) ?>" class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block btn-lighten-1"><i class="fa fa-money"></i> View Activity</a>
                                <a href="<?php echo base_url('supplier/statement?id=' . $details['id']) ?>" class="btn btn-danger btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-briefcase"></i> <?php echo $this->lang->line('Account Statements') ?></a>
                                <a href="<?php echo base_url('supplier/vatreport?id=' . $details['id']) ?>" class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i class="fa fa-print"></i> Print VAT Report</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="mybutton">

                            <div class="">
                                <a href="#sendMail" data-toggle="modal" data-remote="false" class="btn btn-primary btn-md  " data-type="reminder"><i class="icon-envelope"></i> <?php echo $this->lang->line('Send Message') ?>
                                </a>
                                <a href="<?php echo base_url('supplier/bulkpayment?id=' . $details['id']) ?>" class="btn btn-grey-blue btn-md"><i class="fa fa-money"></i> <?php echo $this->lang->line('Bulk Payment') ?>
                                </a>
                                <a href="<?php echo base_url('supplier/edit?id=' . $details['id']) ?>" class="btn btn-warning btn-md"><i class="icon-pencil"></i> <?php echo $this->lang->line('Edit Profile') ?>
                                </a>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-2"><?= $this->lang->line('Bulk Payment'); ?></h5>
                        <hr>

                        <form method="post" id="product_action">

                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <input type="hidden" name="customer" value="<?= $id ?>">


                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pay_cat"><?php echo $this->lang->line('Type') ?></label>

                                <div class="col-sm-4">
                                    <select name="trans_type" id="trans_type" class="form-control">
                                        <option value='due'><?php echo $this->lang->line('Due') ?></option>
                                        <option value='partial'><?php echo $this->lang->line('Partial') ?></option>
                                    </select>


                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="sdate"><?php echo $this->lang->line('From Date') ?></label>

                                <div class="col-sm-4">
                                    <input type="date" class="form-control required" placeholder="Start Date" name="sdate" id="sdate" autocomplete="false">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="edate"><?php echo $this->lang->line('To Date') ?></label>

                                <div class="col-sm-4">
                                    <input type="date" class="form-control required" placeholder="End Date" name="edate" id="t_date" autocomplete="false">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pay_cat"></label>
                                <div class="col-sm-4">
                                    <input type="submit" class="btn btn-primary btn-md" id="calculate_due" value="<?php echo $this->lang->line('Calculate') ?>">
                                </div>
                            </div>
                        </form>
                        <hr>

                        <form method="post" id="product_action_2">

                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <input type="hidden" name="customer" value="<?= $id ?>">
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="sdate"><?php echo $this->lang->line('Amount') ?></label>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control" placeholder="Amount" name="amount" id="amount" autocomplete="false" value="0">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pay_cat"><?php echo $this->lang->line('Type') ?></label>

                                <div class="col-sm-4">
                                    <select name="trans_type_2" id="trans_type_2" class="form-control">
                                        <option value='due'><?php echo $this->lang->line('Due') ?></option>
                                        <option value='partial'><?php echo $this->lang->line('Partial') ?></option>
                                    </select>


                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pmethod"><?php echo $this->lang->line('Payment Method') ?></label>
                                <div class="col-sm-4">
                                    <select name="pmethod" class="form-control mb-1">
                                        <option value="Contra">Contra</option>
                                        <option value="Cash"><?php echo $this->lang->line('Cash') ?></option>
                                        <option value="Card"><?php echo $this->lang->line('Card') ?></option>
                                        <option value="Balance"><?php echo $this->lang->line('Client Balance') ?></option>
                                        <option value="Bank"><?php echo $this->lang->line('Bank') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="account"><?php echo $this->lang->line('Account') ?></label>
                                <div class="col-sm-4">
                                    <select name="account" class="form-control">
                                        <?php foreach ($acclist as $row) {
                                            echo '<option value="' . $row['id'] . '">' . $row['holder'] . ' / ' . $row['acn'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="sdate"><?php echo $this->lang->line('From Date') ?></label>

                                <div class="col-sm-4">
                                    <input type="date" class="form-control required" placeholder="Start Date" name="sdate_2" id="sdate_2" autocomplete="false">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="edate"><?php echo $this->lang->line('To Date') ?></label>

                                <div class="col-sm-4">
                                    <input type="date" class="form-control required" placeholder="End Date" name="edate_2" id="edate_2" autocomplete="false">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-3 control-label" for="sdate"><?php echo $this->lang->line('Note') ?></label>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control" placeholder="Note" name="note" autocomplete="false" value="<?= $this->lang->line('Bulk Payment'); ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="pay_cat"></label>
                                <div class="col-sm-4">
                                    <input type="submit" class="btn btn-success btn-md" id="calculate_pay" value="<?php echo $this->lang->line('Make Payment') ?>">
                                </div>
                            </div>
                        </form>
                        <hr>
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
        // Function to clear and append the temporary success message each time
        function showTemporaryMessage(type, message) {
            // Clear any existing notification and append a new one
            $('#notify').remove(); // Remove previous notifications
            $('.content-body').prepend(`
            <div id="notify" class="alert ${type}" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message">${message}</div>
            </div>
        `);

            // Show the notification
            $("#notify").fadeIn();

            // Scroll to the notification
            $("html, body").animate({
                scrollTop: $('#notify').offset().top
            }, 1000);

            // Hide the notification after a delay
            hideMessageAfterDelay(4000); // Hide after 4 seconds
        }

        $("#calculate_due").click(function(e) {
            e.preventDefault();
            var actionurl = baseurl + 'supplier/bulk_post';
            t_actionCaculate(actionurl);
            $("#sdate_2").val($("#sdate").val());
            $("#edate_2").val($("#t_date").val());
            $("#trans_type_2").val($("#trans_type").val());
        });

        $("#calculate_pay").click(function(e) {
            e.preventDefault();
            var actionurl = baseurl + 'supplier/bulk_post_payment';
            t_actionCaculate(actionurl, '#product_action_2');
        });

        function t_actionCaculate(actionurl, f_name = '#product_action') {
            var errorNum = farmCheck();
            if (errorNum > 0) {
                // Show error message
                showTemporaryMessage("alert-warning", "<strong>Error</strong>: It appears you have forgotten to complete something!");
            } else {
                $(".required").parent().removeClass("has-error");
                $.ajax({
                    url: actionurl,
                    type: 'POST',
                    data: $(f_name).serialize() + '&' + crsf_token + '=' + crsf_hash,
                    dataType: 'json',
                    success: function(data) {
                        // Show success message
                        showTemporaryMessage("alert-success", "<strong>" + data.status + "</strong>: " + data.message);
                        $("#param1").html(data.param1);
                        $("#amount").val(data.due);
                    },
                    error: function(data) {
                        // Show error message
                        showTemporaryMessage("alert-warning", "<strong>" + data.status + "</strong>: " + data.message);
                    }
                });
            }
        }

        // Function to hide the message after a delay
        function hideMessageAfterDelay(delay) {
            setTimeout(function() {
                $("#notify").fadeOut('slow', function() {
                    $(this).remove();
                });
            }, delay);
        }
    </script>