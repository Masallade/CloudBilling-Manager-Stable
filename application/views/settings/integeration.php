<style>
    .connection-block>label>img {
        height: 142px;
        object-fit: contain;
    }

    .connection-block {
        padding-left: 0;
    }

    .connection-block:not(.disabled)>label:hover {
        transform: scale(1.03);
        box-shadow: 0px 6px 24px rgba(0, 0, 0, 0.15);
    }

    .connection-block>label {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 0 16px rgba(0, 0, 0, 0.15);
        padding: 18px 42px;
        cursor: pointer;
        width: 100%;
        transition: all 500ms;
    }

    .connection-block.disabled>label::after {
        content: '';
        background-color: #0000001f;
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        border-radius: inherit;
        cursor: default;
    }
    
</style>
<div class="content-body">

    <div class="card">

        <div class="card-header">

            <h5><?= $this->lang->line('ChoosePlatformtoIntegerate') ?></h5>

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

                <form method="get" id="product_action" class="form-horizontal" action="<?= base_url('/HMRC/auth') ?>" target>



                    <div class="form-group row">



                        <label class="col-sm-12 col-form-label" for="currency">
                            <strong> <?= $this->lang->line('SelectPlatform') ?> </strong>

                        </label>



                        <div class="col-sm-4">
                            <div class="form-check connection-block">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type" value="HMRC" required <?= ($user->is_HMRC_connected != "0") ? "checked" : "" ?>>
                                    <span> HMRC (UK) <?= ($user->is_HMRC_connected != "0") ? '(' . $this->lang->line('Connected') . ')' : "" ?> </span>
                                    <img class="w-100" src="<?= assets_url() ?>/assets/images/HMRC.png" />
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check connection-block disabled">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type" value="ZATCA" disabled>
                                    <span> ZATCA (KSA) [<?= $this->lang->line('ComingSoon') ?>] </span>
                                    <img class="w-100" src="<?= assets_url() ?>/assets/images/zatca.png" />
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check connection-block disabled">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type" value="FBR" disabled>
                                    <span> FBR (PAK) [<?= $this->lang->line('ComingSoon') ?>] </span>
                                    <img class="w-100" src="<?= assets_url() ?>/assets/images/FBR.png" />
                                </label>
                            </div>

                        </div>

                    </div>





                    <div class="form-group row">

                        <div class="col-sm-4">

                            <input type="submit" id="integeration_update" class="btn btn-success margin-bottom" value="<?php echo $this->lang->line('Connect') ?>">

                        </div>

                    </div>



            </div>

            </form>

        </div>

    </div>

</div>

<script type="text/javascript">



</script>