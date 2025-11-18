<!-- AJAX Content for Customer Details -->
<div class="card">
    <div class="card-header" style="margin-bottom: 10px; background-color:rgb(164, 184, 206); color: #fff; text-align: center;">
        <h2 class="card-title" style="color: #fff;">Customer Details</h2>
    </div>
    <hr>
    <div class="card-content">
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <?php if ($details['company']) { ?>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?php echo $this->lang->line('Company') ?></strong>
                            </div>
                            <div class="col-md-10">
                                <?php echo $details['company'] ?>
                            </div>
                        </div>
                        <hr>
                    <?php } ?>

                    <div class="row m-t-lg">
                        <div class="col-md-2">
                            <strong><?php echo $this->lang->line('Phone') ?></strong>
                        </div>
                        <div class="col-md-10">
                            <?php echo $details['phone'] ?>
                        </div>
                    </div>
                    <hr>

                    <div class="row m-t-lg">
                        <div class="col-md-2">
                            <strong> Phone 2</strong>
                        </div>
                        <div class="col-md-10">
                            <?php echo $details['name_s'] ?>
                        </div>
                    </div>

                    <hr>
                    <div class="row m-t-lg">
                        <div class="col-md-2">
                            <strong>Contact Person</strong>
                        </div>
                        <div class="col-md-10">
                            <?php echo $details['phone_s'] ?>
                        </div>
                    </div>

                    <hr>
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
                            <strong><?php echo $this->lang->line('Register') ?><?php echo $this->lang->line('Date') ?></strong>
                        </div>
                        <div class="col-md-6">
                            <?php if ($details['reg_date']) echo dateformat($details['reg_date']) ?>
                        </div>
                    </div>
                    <?php foreach ($custom_fields as $field) { ?>
                        <hr>
                        <div class="row m-t-lg">
                            <div class="col-md-2">
                                <strong><?= $field["name"] ?></strong>
                            </div>
                            <div class="col-md-6">
                                <?= $field["data"] ?>
                            </div>
                        </div>
                    <?php } ?>
                    <hr>
                    <div id="accordionWrapa1" role="tablist" aria-multiselectable="true">
                        <div id="heading3" class="card-header">
                            <a data-toggle="collapse" data-parent="#accordionWrapa1" href="#accordion3"
                                aria-expanded="true" aria-controls="accordion3"
                                class="card-title lead">
                                <?php echo $this->lang->line('Shipping Address') ?>
                            </a>
                        </div>
                        <div id="accordion3" role="tabpanel" aria-labelledby="heading3"
                            class="card-collapse collapse show" aria-expanded="false">
                            <div class="card-body">
                                <div class="card-block">
                                    <div class="row m-t-lg">
                                        <div class="col-md-2">
                                            <strong><?php echo $this->lang->line('Address') ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $details['address_s'] ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row m-t-lg">
                                        <div class="col-md-2">
                                            <strong><?php echo $this->lang->line('City') ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $details['city_s'] ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row m-t-lg">
                                        <div class="col-md-2">
                                            <strong><?php echo $this->lang->line('Region') ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $details['region_s'] ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row m-t-lg">
                                        <div class="col-md-2">
                                            <strong><?php echo $this->lang->line('Country') ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $details['country_s'] ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row m-t-lg">
                                        <div class="col-md-2">
                                            <strong><?php echo $this->lang->line('PostBox') ?></strong>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $details['postbox_s'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>