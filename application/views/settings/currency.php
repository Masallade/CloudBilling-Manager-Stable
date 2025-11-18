
<article class="content-body">
    <div class="card card-block">
        <form method="post" id="product_action" class="form-horizontal">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
            <div class="card-body">
                <h5><?php echo $this->lang->line('GeneralSettings'); ?></h5>
                <hr>

                <!-- Sales Invoice Auto Post -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="auto_post"><?php echo $this->lang->line('SalesInvoiceAutoPost'); ?></label>
                    <div class="col-sm-6">
                        <select name="auto_post" class="form-control">
                            <option value="1"><?php echo $this->lang->line('Enable'); ?></option>
                            <option value="0" <?= $currency['auto_post'] == 0 ? 'selected' : '' ?>><?php echo $this->lang->line('Disable'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Show Profit Percentage -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="show_profit_per"><?php echo $this->lang->line('ShowProfitPercentage'); ?></label>
                    <div class="col-sm-6">
                        <select name="show_profit_per" class="form-control">
                            <option value="1" <?= isset($currency['show_profit_per']) && $currency['show_profit_per'] == 1 ? 'selected' : '' ?>><?php echo $this->lang->line('Enable'); ?></option>
                            <option value="0" <?= isset($currency['show_profit_per']) && $currency['show_profit_per'] == 0 ? 'selected' : '' ?>><?php echo $this->lang->line('Disable'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Auto Pricing -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="auto_pricing"><?php echo $this->lang->line('ItemPricingList'); ?></label>
                    <div class="col-sm-6">
                        <select name="auto_pricing" class="form-control">
                            <option value="1" <?= $currency['auto_pricing'] == 1 ? 'selected' : '' ?>><?php echo $this->lang->line('Enable'); ?></option>
                            <option value="0" <?= $currency['auto_pricing'] == 0 ? 'selected' : '' ?>><?php echo $this->lang->line('Disable'); ?></option>
                        </select>
                    </div>
                </div>

                <hr>

                <!-- Tax Settings -->
                <h5><?php echo $this->lang->line('TaxSettings') ?: 'Tax Settings'; ?></h5>
                <hr>

                <!-- Tax Type -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="tax_type">Tax Type</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="tax_type" placeholder="e.g., VAT, GST, Sales Tax" value="<?php echo isset($currency['tax_type']) ? $currency['tax_type'] : 'VAT' ?>">
                        <small class="form-text text-muted">Enter the tax type name (e.g., VAT, GST, Sales Tax)</small>
                    </div>
                </div>

                <!-- Tax Rate -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="tax_rate">Tax Rate (%)</label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control" name="tax_rate" placeholder="e.g., 20 for 20%" step="0.01" min="0" max="100" value="<?php echo isset($currency['tax_rate']) ? $currency['tax_rate'] : '20' ?>">
                        <small class="form-text text-muted">Enter the tax percentage (e.g., 20 for 20%)</small>
                    </div>
                </div>

                <hr>

                <!-- Currency Settings -->
                <h5><?php echo $this->lang->line('CurrencySettings') ?: 'Currency Settings'; ?></h5>
                <hr>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="currency"><?php echo $this->lang->line('CurrencySymbol') ?></label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control margin-bottom" name="currency" value="<?php echo strtoupper($currency['currency']) ?>">
                    </div>
                </div>

                <!-- Decimal Separator -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="currency"><?php echo $this->lang->line('DecimalSeparator') ?></label>
                    <div class="col-sm-6">
                        <select name="deci_sep" class="form-control">
                            <?php echo '<option value="' . $currency['key1'] . '">' . $currency['key1'] . '</option>'; ?>
                            <option value=",">, (Comma)</option>
                            <option value=".">. (Dot)</option>
                            <option value="">None</option>
                        </select>
                    </div>
                </div>

                <!-- Thousand Separator -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="thous_sep"><?php echo $this->lang->line('ThousandSeparator') ?></label>
                    <div class="col-sm-6">
                        <select name="thous_sep" class="form-control">
                            <?php echo '<option value="' . $currency['key2'] . '">' . $currency['key2'] . '</option>'; ?>
                            <option value=",">, (Comma)</option>
                            <option value=".">. (Dot)</option>
                            <option value="">None</option>
                        </select>
                    </div>
                </div>

                <!-- Decimal Places -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="currency"><?php echo $this->lang->line('DecimalPlace') ?></label>
                    <div class="col-sm-6">
                        <select name="decimal" class="form-control">
                            <?php echo '<option value="' . $currency['url'] . '">' . $currency['url'] . '</option>'; ?>
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>

                <!-- Symbol Position -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="spost"><?php echo $this->lang->line('SymbolPosition'); ?></label>
                    <div class="col-sm-6">
                        <select name="spos" class="form-control">
                            <?php
                            if ($currency['method'] == 'l') {
                                $method = '**Left**';
                            } else {
                                $method = '**Right**';
                            }
                            echo '<option value="' . $currency['method'] . '">' . $method . '</option>';
                            ?>
                            <option value="l">Left</option>
                            <option value="r">Right</option>
                        </select>
                    </div>
                </div>

                <hr>

                <!-- Round Off Settings -->
                 <?php 
                /* <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="roundoff"><?php echo $this->lang->line('InvoiceRoundOff'); ?></label>
                    <div class="col-sm-6">
                        <select name="roundoff" class="form-control">
                            <?php
                            $method = $currency['other'] == 'PHP_ROUND_HALF_UP' ? '**ROUND_HALF_UP**' : ($currency['other'] == 'PHP_ROUND_HALF_DOWN' ? '**ROUND_HALF_DOWN**' : '**Off**');
                            echo '<option value="' . $currency['other'] . '">' . $method . '</option>';
                            ?>
                            <option value="">Off</option>
                            <option value="PHP_ROUND_HALF_UP">ROUND_HALF_UP</option>
                            <option value="PHP_ROUND_HALF_DOWN">ROUND_HALF_DOWN</option>
                        </select>
                    </div>
                </div>               
                 */ ?>

                <!-- Round Off Precision -->
                 <?php 
                 /* 
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="r_precision"><?php echo $this->lang->line('InvoiceRoundOffPrecision'); ?></label>
                    <div class="col-sm-6">
                        <select name="r_precision" class="form-control">
                            <?php echo '<option value="' . $currency['active'] . '">' . $currency['active'] . '</option>'; ?>
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>
                */ ?>
            </div>

            <div class="form-group row">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-4">
                    <input type="submit" id="billing_update" class="btn btn-success margin-bottom" value="Update" data-loading-text="Updating...">
                </div>
            </div>
        </form>
    </div>
</article>

<!-- Include Toastr CSS -->
<link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/vendors/css/extensions/toastr.css">

<!-- Include Toastr JS -->
<script src="<?= assets_url() ?>app-assets/vendors/js/extensions/toastr.min.js"></script>

<script type="text/javascript">
    document.title = '<?php echo $this->lang->line('GeneralSettings') ?>';
    
    // Configure Toastr defaults
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    $("#product_action").submit(function(e) {
        e.preventDefault(); // Prevent form submission

        var actionurl = baseurl + 'settings/currency'; // Define the action URL

        // Adding CSRF token to the request
        var csrf_token = $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val();

        $.ajax({
            url: actionurl,
            type: 'POST',
            data: $(this).serialize() + '&' + '<?= $this->security->get_csrf_token_name(); ?>=' + csrf_token,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Show success toast notification
                    toastr.success(response.message, 'Success', {
                        timeOut: 5000,
                        positionClass: 'toast-top-right'
                    });
                } else {
                    // Show error toast notification
                    toastr.error(response.message || 'An error occurred', 'Error', {
                        timeOut: 5000,
                        positionClass: 'toast-top-right'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Show error toast notification if there's an issue with the AJAX request
                toastr.error('An error occurred. Please try again.', 'Error', {
                    timeOut: 5000,
                    positionClass: 'toast-top-right'
                });
            }
        });
    });
</script>