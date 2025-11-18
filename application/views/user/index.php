<link rel="stylesheet" href="<?= assets_url('assets/custom/owlcarousel/assets/owl.carousel.min.css') ?>">
<link rel="stylesheet" href="<?= assets_url('assets/custom/owlcarousel/assets/owl.theme.default.min.css') ?>">
<link rel="icon" type="image/x-icon" href="<?= assets_url('app-assets/images/ico/favicon.ico') ?>">

<style>
    .content-wrapper {
        margin: 26px;
        border-radius: 32px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    }

    .blank-page .content-wrapper .flexbox-container {
        height: calc(100vh - 52px);
        align-items: initial;
    }

    .form-simple input {
        border-radius: 18px;
        padding: 24px 48px;
    }

    .btn-outline-primary {
        border-color: #400791;
        background-color: #400791;
        color: #fff;
        border-radius: 18px;
        padding: 16px;
    }

    .btn-outline-primary:hover {
        background: #5300c9;
    }

    .logo-container>img {
        padding: 8px;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    div#c_body {
        position: absolute;
        z-index: 99999;
        max-width: 420px;
        right: 16px;
        top: 16px;
    }

    .features-slide .owl-item {
        background: #fff;
        padding: 16px;
        border-radius: 18px;
    }

    .features-slide .owl-stage {
        display: flex;
        align-items: center;
    }
</style>

<body class="horizontal-layout horizontal-menu 1-column  bg-full-screen-image menu-expanded blank-page blank-page"
    data-open="hover" data-menu="horizontal-menu" data-col="1-column">
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="app-content content">
        <div class="content-wrapper bg-white overflow-hidden">
            <div class="content-header row">
            </div>
            <div>
                <section class="flexbox-container">
                    <div class="w-100 row mx-auto justify-content-between">
                        <div class="col-md-6 row justify-content-center align-items-center mb-5">
                            <div class="mb-2 col-12 row justify-content-center align-items-center">
                                <!--<div class="text-center col-12">
                                <div class="logo-container mb-3">
                                    <img src="<?php echo base_url('app-assets/images/ico/app-icon.png') ?>" alt="logo" style="max-height: 10rem;  max-width: 10rem;">
                                </div>
                            </div> -->
                                <div class="col-md-8">
                                    <div class="text-center mb-2">
                                        <h3>
                                            <strong>
                                                <?= $this->lang->line('Login to Dashboard') ?>
                                            </strong>
                                        </h3>
                                        <span><?= $this->lang->line('Effortlessly manage cloud expenses, boost sales, and streamline invoices with our all-in-one cloud billing solution') ?>.</span>
                                    </div>
                                    <?php
                                    $attributes = array('class' => 'form-horizontal form-simple', 'id' => 'login_form');
                                    echo form_open('user/checklogin', $attributes);
                                    ?>
                                    <fieldset class="form-group position-relative has-icon-left">
                                        <input style="height:40px;" type="text" class="form-control" id="user-name" name="username"
                                            placeholder="<?php echo $this->lang->line('Your Email') ?>" required>
                                        <div class="form-control-position">
                                            <i class="ft-user"></i>
                                        </div>
                                    </fieldset>
                                    <fieldset class="form-group position-relative has-icon-left">
                                        <input style="height:40px;" type="password" class="form-control" id="user-password" name="password"
                                            placeholder="<?php echo $this->lang->line('Your Password') ?>" required>
                                        <div class="form-control-position">
                                            <i class="fa fa-key"></i>
                                        </div>
                                    </fieldset>
                                    <?php if ($response) {
                                        echo '<div id="notify" class="alert alert-danger" >
                                            <a href="#" class="close" data-dismiss="alert">&times;</a> <div class="message">' . $response . '</div>
                                        </div>';
                                    } ?>

                                    <?php if ($this->aauth->get_login_attempts() > 1 && $captcha_on) {
                                        echo '<script src="https://www.google.com/recaptcha/api.js"></script>
                                    <fieldset class="form-group position-relative has-icon-left">
                                        <div class="g-recaptcha" data-sitekey="' . $captcha . '"></div>
                                    </fieldset>';
                                    } ?>

                                    <button type="submit" class="btn btn-outline-primary btn-block"><i
                                            class="ft-unlock"></i> <?php echo $this->lang->line('login') ?></button>
                                    </form>
                                </div>
                                
                                
                                <div class="text-center mt-4" style="direction: rtl;">
                                <span>Forget your password? <a href="<?php echo base_url('user/forgot') ?>" style="text-decoration: underline;">Reset it here</a></span><br>
                                    <span><?= $this->lang->line('© 2023 Cloud Billing Manager') ?> - <a href="https://avantcoretech.com/" target="_blank">AVANTCORE Technologies</a>. <?= $this->lang->line('All rights reserved') ?>.</span>
                                </div>
                                
                                
                            </div>
                            
                            <div class="text-center mt-12">
                                <h3>For Demo Contact:</h3>
                                <span><i class="fa fa-envelope" style="font-size:24px"></i> <a href="mailto:Info@avantcoretech.com" target="_blank">: Info@avantcoretech.com </a> - <i class="fa fa-mobile-phone" style="font-size:22px"></i>
                                    : <a href="tel:+447429682461" target="_blank">+44 7429 682461</a></span>
                            </div>

                        </div>

                        <div class="col-md-6 row justify-content-center mx-0 align-items-center" style="background: #400791;">
                            <div class="col-md-6">
                                <lottie-player src="https://assets6.lottiefiles.com/packages/lf20_kuhijlvx.json" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></lottie-player>
                            </div>
                            <div class="col-md-9 owl-carousel owl-theme position-relative features-slide" style="max-width: auto; margin-bottom: 72px ">
                                <div class="item">
                                    <h5>
                                        <strong>
                                            <?= $this->lang->line('Simplify Billing and Invoicing with Cloud Billing Manager') ?>
                                        </strong>
                                    </h5>
                                    <p class="mb-0">
                                        <?= $this->lang->line('Simplify_Billing_deais') ?>.
                                    </p>
                                </div>
                                <div class="item">
                                    <h5>
                                        <strong>
                                            <?= $this->lang->line('Streamline Inventory Management and Stock Returns') ?>
                                        </strong>
                                    </h5>
                                    <p class="mb-0">
                                        <?= $this->lang->line('Streamline_Inventory_details') ?>.
                                    </p>
                                </div>
                                <div class="item">
                                    <h5>
                                        <strong>
                                            <?= $this->lang->line('Efficient Customer and Employee Management') ?>
                                        </strong>
                                    </h5>
                                    <p class="mb-0">
                                        <?= $this->lang->line('Efficient_Customer_details') ?>.
                                    </p>
                                </div>
                                <div class="item">
                                    <h5>
                                        <strong>
                                            <?= $this->lang->line('Secure Data Handling and Permission Management') ?>
                                        </strong>
                                    </h5>
                                    <p class="mb-0">
                                        <?= $this->lang->line('Secure_Data_details') ?>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="<?= assets_url(); ?>app-assets/vendors/js/vendors.min.js"></script>
    <script type="text/javascript" src="<?= assets_url(); ?>app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script type="text/javascript" src="<?= assets_url(); ?>app-assets/vendors/js/charts/jquery.sparkline.min.js"></script>
    <script src="<?= assets_url(); ?>app-assets/vendors/js/forms/validation/jqBootstrapValidation.js"></script>
    <script src="<?= assets_url(); ?>app-assets/vendors/js/forms/icheck/icheck.min.js"></script>
    <script src="<?= assets_url(); ?>app-assets/js/core/app-menu.js"></script>
    <script src="<?= assets_url(); ?>app-assets/js/core/app.js"></script>
    <script type="text/javascript" src="<?= assets_url(); ?>app-assets/js/scripts/ui/breadcrumbs-with-stats.js"></script>
    <script src="<?= assets_url(); ?>app-assets/js/scripts/forms/form-login-register.js"></script>
    <script src="<?= assets_url('assets/custom/owlcarousel/owl.carousel.min.js') ?>" type="text/javascript"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <script>
        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            autoplay: true,
            nav: false,
            items: 1
        })
    </script>