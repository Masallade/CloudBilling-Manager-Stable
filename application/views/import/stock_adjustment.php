<div class="content-body">
    <div class="card">
        <?php if ($this->session->flashdata('success')) { ?>
            <div class="alert alert-success">
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php } ?>

        <?php if ($this->session->flashdata('error')) { ?>
            <div class="alert alert-danger">
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php } ?>

        <div class="card-header">
            <h5><?php echo $this->lang->line('Stock Adjustment') ?></h5>
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
                <?php echo form_open_multipart('import/stock_adjustment_upload'); ?>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                    value="<?php echo $this->security->get_csrf_hash(); ?>">

                <hr>
                <p>Your products data file should be as per this template
                    <a href="<?php echo base_url('userfiles/product/Stock_Adjustment.csv') ?>">
                        <strong>Download Template</strong>
                    </a>.
                </p>
                <p><strong>Column Order in xlsx File Must be like this:</strong></p>
                <pre>
                    1. (string) Stock Code A  
                    2. (integer) Quantity
                </pre>

                <hr>

                <div class="form-group row">
                    <div class="col-sm-6">
                        <label class="col-form-label" for="name">File</label>
                        <input type="file" class="form-control" name="userfile" size="15" />
                        <small class="form-text text-muted">(CSV format only)</small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-2 offset-sm-3">
                        <input type="submit" class="btn btn-success btn-block"
                            value="<?php echo $this->lang->line('Stock Adjustment') ?>" data-loading-text="Adding...">
                    </div>
                </div>

                </form>
            </div>
        </div>
    </div>
</div>