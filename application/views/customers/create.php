<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?php echo $this->lang->line('Add New Customer') ?></h4>

            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <form method="post" id="data_form" class="form-horizontal">
                <div class="card">

                    <div class="card-content">
                        <div class="card-body">

                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active show" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab" aria-selected="true"><?php echo $this->lang->line('Billing Address') ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab" aria-selected="false"><?php echo $this->lang->line('Shipping Address') ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab4" role="tab" aria-selected="false">Payment Terms</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab" aria-selected="false"><?php echo $this->lang->line('Other') . ' ' . $this->lang->line('Settings') ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5" role="tab" aria-selected="false">Bank Details</a>
                                </li>

                            </ul>
                            <div class="tab-content px-1 pt-1">
                                <div class="tab-pane active show" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                                    <div class="form-group row mt-1">

                                        <label class="col-sm-2 col-form-label" for="name"> Customer A/C# </label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Customer A/C#" class="form-control margin-bottom b_input required" name="name" id="mcustomer_name">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="name">Name <?php //echo $this->lang->line('Company') 
                                                                                                ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Name" class="form-control margin-bottom b_input" name="company">
                                        </div>
                                    </div>

                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="phone"><?php echo $this->lang->line('Phone') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="phone" class="form-control margin-bottom required b_input" name="phone" id="mcustomer_phone">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="email">Email</label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="email" class="form-control margin-bottom b_input" name="email" id="mcustomer_email">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="address">Street <?php echo $this->lang->line('Address') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Street Address" class="form-control margin-bottom b_input" name="address" id="mcustomer_address1">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="city"><?php echo $this->lang->line('City') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="city" class="form-control margin-bottom b_input" name="city" id="mcustomer_city">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="region"><?php echo $this->lang->line('Region') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Region" class="form-control margin-bottom b_input" name="region" id="region">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="country"><?php echo $this->lang->line('Country') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Country" class="form-control margin-bottom b_input" name="country" id="mcustomer_country">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="postbox">Post Code</label>

                                        <div class="col-sm-6">
                                            <input type="text" placeholder="Post Code" class="form-control margin-bottom b_input" name="postbox" id="postbox">
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                                    <div class="form-group row">

                                        <div class="input-group mt-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="customer1" id="copy_address">
                                                <label class="custom-control-label" for="copy_address"><?php echo $this->lang->line('Same As Billing') ?></label>
                                            </div>

                                        </div>

                                        <div class="col-sm-10 text-info">
                                            <?php echo $this->lang->line("leave Shipping Address") ?>
                                        </div>
                                    </div>

                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="name_s"><?php echo $this->lang->line('Name') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Name" class="form-control margin-bottom b_input" name="name_s" id="mcustomer_name_s">
                                        </div>
                                    </div>


                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="phone_s"><?php echo $this->lang->line('Phone') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="phone" class="form-control margin-bottom b_input" name="phone_s" id="mcustomer_phone_s">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="email_s">Email</label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="email" class="form-control margin-bottom b_input" name="email_s" id="mcustomer_email_s">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="address"><?php echo $this->lang->line('Address') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="address_s" class="form-control margin-bottom b_input" name="address_s" id="mcustomer_address1_s">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="city_s"><?php echo $this->lang->line('City') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="city" class="form-control margin-bottom b_input" name="city_s" id="mcustomer_city_s">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="region_s"><?php echo $this->lang->line('Region') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Region" class="form-control margin-bottom b_input" name="region_s" id="region_s">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="country_s"><?php echo $this->lang->line('Country') ?></label>

                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Country" class="form-control margin-bottom b_input" name="country_s" id="mcustomer_country_s">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="postbox"><?php echo $this->lang->line('PostBox') ?></label>

                                        <div class="col-sm-6">
                                            <input type="text" placeholder="PostBox" class="form-control margin-bottom b_input" name="postbox_s" id="postbox_s">
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                                    <div class="form-group row"><label class="col-sm-2 col-form-label" for="Discount"><?php echo $this->lang->line('Discount') ?> </label>
                                        <div class="col-sm-6">
                                            <input type="text" placeholder="Custom Discount" class="form-control margin-bottom b_input" name="discount">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="taxid"><?php echo $this->lang->line('TAX') ?> ID</label>

                                        <div class="col-sm-6">
                                            <input type="text" placeholder="TAX ID" class="form-control margin-bottom b_input" name="taxid">
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="docid"><?php echo $this->lang->line('Document') ?> ID</label>

                                        <div class="col-sm-6">
                                            <input type="text" placeholder="Document ID" class="form-control margin-bottom b_input" name="docid">
                                        </div>
                                    </div>
                                    <div class="form-group row"><label class="col-sm-2 col-form-label" for="c_field">Payment Terms </label>
                                        <div class="col-sm-6">
                                            <input type="text" placeholder="Payment Terms" class="form-control margin-bottom b_input" name="c_field">
                                        </div>
                                    </div>



                                    <div class="form-group row d-none">

                                        <label class="col-sm-2 col-form-label" for="customergroup"><?php echo $this->lang->line('Customer group') ?></label>

                                        <div class="col-sm-6">
                                            <select name="customergroup" class="form-control b_input">
                                                <?php

                                                foreach ($customergrouplist as $row) {
                                                    $cid = $row['id'];
                                                    $title = $row['title'];
                                                    echo "<option value='$cid'>$title</option>";
                                                }
                                                ?>
                                            </select>


                                        </div>
                                    </div>

                                    <div class="form-group row d-none">

                                        <label class="col-sm-2 col-form-label" for="currency">Language</label>

                                        <div class="col-sm-6">
                                            <select name="language" class="form-control b_input">

                                                <?php

                                                echo $langs;
                                                ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="currency"><?php echo $this->lang->line('customer_login') ?></label>

                                        <div class="col-sm-6">
                                            <select name="c_login" class="form-control b_input">

                                                <option value="1"><?php echo $this->lang->line('Yes') ?></option>
                                                <option value="0"><?php echo $this->lang->line('No') ?></option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label class="col-sm-2 col-form-label" for="password_c"><?php echo $this->lang->line('New Password') ?></label>

                                        <div class="col-sm-6">
                                            <input type="text" placeholder="Leave blank for auto generation" class="form-control margin-bottom b_input" name="password_c" id="password_c">
                                        </div>
                                    </div>


                                </div>
                                <div class="tab-pane show" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                                    <?php foreach ($custom_fields as $row) {
                                        if ($row['f_type'] == 'text') { ?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="docid"><?= $row['name'] ?>(For Driver)</label>
                                                <div class="col-sm-8">
                                                    <div>
                                                        <input type="text" placeholder="<?= $row['placeholder'] ?>" class="form-control margin-bottom b_input <?= $row['other'] ?>" name="custom[<?= $row['id'] ?>]">

                                                    </div>
                                                </div>
                                            </div>
                                    <?php }
                                    } ?>
                                    <?php foreach ($custom_fields as $row) {
                                        if ($row['f_type'] == 'text') { ?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="docid"><?= $row['name'] ?></label>
                                                <div class="col-sm-8">
                                                    <div>
                                                        <input type="radio" id="custom_balance_<?= $row['id'] ?>" name="limit[<?= $row['id'] ?>]" value="balance" onclick="toggleInputField('balance', <?= $row['id'] ?>)" />
                                                        <label for="custom_balance_<?= $row['id'] ?>">Total Balance Limit</label>
                                                    </div>
                                                    <div>
                                                        <input type="radio" id="custom_invoices_<?= $row['id'] ?>" name="limit[<?= $row['id'] ?>]" value="invoices" onclick="toggleInputField('invoices', <?= $row['id'] ?>)" />
                                                        <label for="custom_invoices_<?= $row['id'] ?>">No. of Invoices Limit</label>
                                                    </div>
                                                    <div id="balance_input_<?= $row['id'] ?>" class="input-field" style="display: none;">
                                                        <label for="balance_limit_<?= $row['id'] ?>">Balance Limit: GBP</label>
                                                        <input placeholder="Total Balance Limit" type="number" id="balance_limit_<?= $row['id'] ?>" name="balance_limit[<?= $row['id'] ?>]" />
                                                    </div>
                                                    <div id="invoices_input_<?= $row['id'] ?>" class="input-field" style="display: none;">
                                                        <label for="invoices_limit_<?= $row['id'] ?>">Invoices Limit:</label>
                                                        <input placeholder="No. of Invoices" type="number" id="invoices_limit_<?= $row['id'] ?>" name="invoices_limit[<?= $row['id'] ?>]" />
                                                    </div>
                                                </div>
                                            </div>
                                    <?php }
                                    } ?>
                                </div>
                                <div class="tab-pane show" id="tab5" role="tabpanel" aria-labelledby="base-tab5">
                                    <div id="bankAccounts">
                                        <div class="form-group row bank-account" data-id="1">
                                            <label class="col-sm-2 col-form-label">Bank Account ID</label>
                                            <div class="col-sm-8">
                                                <input type="text" placeholder="Enter the Bank Account ID" class="form-control margin-bottom b_input" name="bank_id_1">
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="button" class="btn btn-danger remove-account">Remove</button>
                                            </div>
                                        </div>
                                        <div class="form-group row bank-account" data-id="1">
                                            <label class="col-sm-2 col-form-label">Bank Account Number</label>
                                            <div class="col-sm-8">
                                                <input type="text" placeholder="Enter the Bank Account Number" class="form-control margin-bottom b_input" name="bank_number_1">
                                            </div>
                                        </div>
                                        <div class="form-group row bank-account" data-id="1">
                                            <label class="col-sm-2 col-form-label">Bank Account Ref</label>
                                            <div class="col-sm-8">
                                                <input type="text" placeholder="Enter the Bank Account Ref" class="form-control margin-bottom b_input" name="bank_ref_1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-10">
                                            <button type="button" id="addAccount" class="btn btn-secondary mt-2">Add Another Bank Account</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--  <div class="tab-pane show" id="tab4" role="tabpanel" aria-labelledby="base-tab4">

                                 <?php
                                    foreach ($custom_fields as $row) {
                                        if ($row['f_type'] == 'text') { ?>
                                            <div class="form-group row">

                                                <label class="col-sm-2 col-form-label"
                                                       for="docid"><?= $row['name'] ?></label>

                                                <div class="col-sm-8">
                                               <div>
                                            <input type="radio" id="custom[<?= $row['id'] ?>]" name="custom[<?= $row['id'] ?>]" value="balance" checked />
                                            <label for="balance"><?php echo "Total Balance Limit"; ?></label>
                                          </div>
                                          <div>
                                            <input type="radio" id="custom[<?= $row['id'] ?>]" name="custom[<?= $row['id'] ?>]" value="<?php echo "invoices"; ?>" />
                                            <label for="invoices"><?php echo "No. of Invoices Limit"; ?></label>
                                          </div>-->


                        <!-- <input type="text" placeholder="<?= $row['placeholder'] ?>"
                                                           class="form-control margin-bottom b_input <?= $row['other'] ?>"
                                                           name="custom[<?= $row['id'] ?>]"> -->

                        <!-- </div>
                                            </div>


                                        <?php }
                                    }
                                        ?>

                                </div> -->
                        <div id="mybutton">
                            <input type="submit" id="submit-data" class="btn btn-lg btn btn-primary margin-bottom round float-xs-right mr-2" value="<?php echo $this->lang->line('Add customer') ?>" data-loading-text="Adding...">
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-success">
            <?php echo $this->session->flashdata('message'); ?>
        </div>
    <?php endif; ?>


    <input type="hidden" value="customers/addcustomer" id="action-url">
    </form>
</div>
</div>
</div>
<script type="text/javascript">
    document.title = 'Add Customer';
</script>
<script>
    let accountId = 2;

    document.getElementById('addAccount').addEventListener('click', function() {
        const bankAccounts = document.getElementById('bankAccounts');

        const newAccount = `
            <div class="form-group row bank-account" data-id="${accountId}">
                <label class="col-sm-2 col-form-label">Bank Account ID</label>
                <div class="col-sm-8">
                    <input type="text" placeholder="Enter the Bank Account ID" class="form-control margin-bottom b_input" name="bank_id_${accountId}">
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger remove-account">Remove</button>
                </div>
            </div>
            <div class="form-group row bank-account" data-id="${accountId}">
                <label class="col-sm-2 col-form-label">Bank Account Number</label>
                <div class="col-sm-8">
                    <input type="text" placeholder="Enter the Bank Account Number" class="form-control margin-bottom b_input" name="bank_number_${accountId}">
                </div>
            </div>
            <div class="form-group row bank-account" data-id="${accountId}">
                <label class="col-sm-2 col-form-label">Bank Account Ref</label>
                <div class="col-sm-8">
                    <input type="text" placeholder="Enter the Bank Account Ref" class="form-control margin-bottom b_input" name="bank_ref_${accountId}">
                </div>
            </div>
        `;

        bankAccounts.insertAdjacentHTML('beforeend', newAccount);
        accountId++;
    });

    document.getElementById('bankAccounts').addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-account')) {
            const accountDiv = event.target.closest('.bank-account');
            const accountId = accountDiv.getAttribute('data-id');
            const accountFields = document.querySelectorAll(`.bank-account[data-id="${accountId}"]`);
            accountFields.forEach(field => field.remove());
        }
    });
</script>
<script>
    function toggleInputField(type, id) {
        var balanceInput = document.getElementById('balance_input_' + id);
        var invoicesInput = document.getElementById('invoices_input_' + id);

        if (type === 'balance') {
            balanceInput.style.display = 'block';
            invoicesInput.style.display = 'none';
        } else if (type === 'invoices') {
            balanceInput.style.display = 'none';
            invoicesInput.style.display = 'block';
        }
    }
</script>