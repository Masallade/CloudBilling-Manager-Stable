<!-- AJAX Content for View Details -->
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

                    <div class="">
                        <!-- Container for balance and payment details -->
                        <div class="d-flex justify-content-between align-items-center bg-blue bg-lighten-4 p-1 mt-2">
                            <h5 class="m-0">
                                <strong class="badge bg-dark">
                                    <?php
                                    // Display balance with localization and formatting
                                    echo $this->lang->line('Paid') . ': ' . amountExchange($pamnt, 0, $this->aauth->get_user()->loc);
                                    ?>
                                </strong>
                                <strong class="badge bg-blue">
                                    <?php
                                    // Display balance with localization and formatting
                                    echo $this->lang->line('Due') . ': ' . amountExchange($expense, 0, $this->aauth->get_user()->loc);
                                    ?>
                                </strong>
                            </h5>
                        </div>

                        <h4></h4>
                        <hr>
                        <!-- <?php if ($details['company']) { ?>
                            <div class="row m-t-lg">
                                <div class="col-md-2">
                                    <strong><?php echo $this->lang->line('Name') ?></strong>
                                </div>
                                <div class="col-md-10">
                                    <?php echo $details['name'] ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row m-t-lg">
                                <div class="col-md-2">
                                    <strong><?php echo $this->lang->line('Company') ?></strong>
                                </div>
                                <div class="col-md-10">
                                    <?php echo $details['company'] ?>
                                </div>
                            </div>
                            <hr>
                        <?php } ?> -->

                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('Address') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['address'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('City') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['city'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('Region') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['region'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('Country') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['country'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('PostBox') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['postbox'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong>Email</strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['email'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('Phone') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['phone'] ?>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
