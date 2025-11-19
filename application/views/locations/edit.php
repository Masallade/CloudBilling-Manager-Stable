<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5><?php echo $this->lang->line('Business Location') ?></h5>
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


                <form method="post" id="data_form" class="form-horizontal">


                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"
                            for="name"><?php echo $this->lang->line('Name') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="Name"
                                class="form-control margin-bottom  required" name="name"
                                value="<?php echo $cname ?>">
                        </div>
                    </div>
                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"
                            for="address"><?php echo $this->lang->line('Address') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="Address"
                                class="form-control margin-bottom  required" name="address"
                                value="<?php echo $address ?>">
                        </div>
                    </div>

                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"
                            for="city"><?php echo $this->lang->line('City') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="City"
                                class="form-control margin-bottom  required" name="city" value="<?php echo $city ?>">
                        </div>
                    </div>


                    <div class="form-group row">

                        <label class="col-sm-2 control-label"
                            for=region"><?php echo $this->lang->line('Region') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="Region"
                                class="form-control margin-bottom" name="region" value="<?php echo $region ?>">
                        </div>
                    </div>


                    <div class="form-group row">

                        <label class="col-sm-2 control-label"
                            for="country"><?php echo $this->lang->line('Country') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="Country"
                                class="form-control margin-bottom" name="country" value="<?php echo $country ?>">
                        </div>
                    </div>

                    <div class="form-group row">

                        <label class="col-sm-2 control-label"
                            for="postbox"><?php echo $this->lang->line('Postbox') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="postbox"
                                class="form-control margin-bottom" name="postbox" value="<?php echo $postbox ?>">
                        </div>
                    </div>

                    <div class="form-group row">

                        <label class="col-sm-2 control-label"
                            for="phone"><?php echo $this->lang->line('Phone') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="Phone"
                                class="form-control margin-bottom" name="phone" value="<?php echo $phone ?>">
                        </div>
                    </div>

                    <div class="form-group row">

                        <label class="col-sm-2 control-label"
                            for="email"><?php echo $this->lang->line('Email') ?></label>

                        <div class="col-sm-8">
                            <input type="text" placeholder="Email"
                                class="form-control margin-bottom" name="email" value="<?php echo $email ?>">
                        </div>
                    </div>

                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"></label>

                        <div class="col-sm-4">
                            <input type="submit" id="submit-data" class="btn btn-success margin-bottom"
                                value="<?php echo $this->lang->line('Edit') ?>" data-loading-text="Adding...">
                            <input type="hidden" value="locations/edit" id="action-url">
                            <input type="hidden" value="<?php echo $id ?>" name="id">
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        document.title = '<?php echo $this->lang->line('Edit Business Location') ?>';
    </script>