<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5><?php echo $this->lang->line('Edit Customer Details') ?></h5>

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
                <div class="row">

                    <div class="col-md-6">
                        <h5><?php echo $this->lang->line('Billing Address') ?></h5>
                        <input type="hidden" name="id" value="<?php echo $customer['id'] ?>">


                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="product_name"><?php echo $this->lang->line('Name') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="Name" class="form-control margin-bottom required" name="name" value="<?php echo $customer['name'] ?>" id="mcustomer_name">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="product_name"><?php echo $this->lang->line('Company') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="Company" class="form-control margin-bottom" name="company" value="<?php echo $customer['company'] ?>">
                            </div>
                        </div>

                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="phone"><?php echo $this->lang->line('Phone') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="phone" class="form-control margin-bottom  required" name="phone" value="<?php echo $customer['phone'] ?>" id="mcustomer_phone">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="email">Email</label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="email" class="form-control margin-bottom" name="email" value="<?php echo $customer['email'] ?>" id="mcustomer_email">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="product_name"><?php echo $this->lang->line('Address') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="address" class="form-control margin-bottom" name="address" value="<?php echo $customer['address'] ?>" id="mcustomer_address1">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="city"><?php echo $this->lang->line('City') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="city" class="form-control margin-bottom" name="city" value="<?php echo $customer['city'] ?>" id="mcustomer_city">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="region"><?php echo $this->lang->line('Region') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="region" class="form-control margin-bottom" name="region" value="<?php echo $customer['region'] ?>" id="region">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="country"><?php echo $this->lang->line('Country') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="Country" class="form-control margin-bottom" name="country" value="<?php echo $customer['country'] ?>" id="mcustomer_country">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="postbox"><?php echo $this->lang->line('PostBox') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="region" class="form-control margin-bottom" name="postbox" value="<?php echo $customer['postbox'] ?>" id="postbox">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="postbox"><?php echo $this->lang->line('Tax') ?> ID</label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="TAX ID" class="form-control margin-bottom" name="taxid" value="<?php echo $customer['taxid'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="postbox"><?php echo $this->lang->line('Document') ?> ID</label>

                            <div class="col-sm-6">
                                <input type="text" placeholder="Document ID" class="form-control margin-bottom b_input" name="docid" value="<?php echo $customer['docid'] ?>">
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label" for="postbox"><?php echo $this->lang->line('Extra') ?> </label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Custom Field" class="form-control margin-bottom b_input" name="c_field" value="<?php echo $customer['custom1'] ?>">
                            </div>
                        </div>
                        <div class="form-group row d-none">

                            <label class="col-sm-2 col-form-label" for="customergroup"><?php echo $this->lang->line('Customer group') ?></label>

                            <div class="col-sm-6">
                                <select name="customergroup" class="form-control">
                                    <?php
                                    echo '<option value="' . $customergroup['id'] . '">' . $customergroup['title'] . ' (S)</option>';
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

                            <label class="col-sm-2 col-form-label" for="customergroup">Language</label>
                            <div class="col-sm-6">
                                <select name="language" class="form-control b_input">
                                    <?php
                                    echo '<option value="' . $customer['lang'] . '">-' . ucfirst($customer['lang']) . '-</option>';
                                    echo $langs;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row"><label class="col-sm-2 col-form-label" for="Discount"><?php echo $this->lang->line('Discount') ?> </label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Custom Discount" class="form-control margin-bottom b_input" name="discount" value="<?php echo $customer['discount_c'] ?>">
                            </div>
                        </div>

                        <?php foreach ($custom_fields as $row) {
                            if ($row['f_type'] == 'text') { ?>
                                <div class="form-group row">

                                    <label class="col-sm-2 col-form-label" for="docid"><?= $row['name'] ?>(For Driver)</label>

                                    <div class="col-sm-8">
                                        <input type="text" placeholder="<?= $row['placeholder'] ?>" class="form-control margin-bottom b_input" name="custom[<?= $row['id'] ?>]" value="<?= $row['data'] ?>">
                                    </div>
                                </div>
                        <?php }
                        } ?>
                        <?php
                        if (isset($limit_fields['limit'])) {
                            // Attempt to decode the JSON data
                            $limit_data = json_decode($limit_fields['limit'], true);
                        } elseif (empty($limit_data)) {
                            // If $limit_data is empty or undefined, manually set it to {2: ':'}
                            $limit_data = array('2' => ':');
                        }
                        foreach ($limit_data as $key => $value) {
                            // Split the key to get the limit type and field value
                            $limit_parts = explode(':', $value);
                            $limit_type = $limit_parts[0];
                            $limit_value = $limit_parts[1];

                            // Display the limit type and input fields based on the type
                            if ($limit_type == 'balance') {
                                $balance_checked = 'checked';
                                $invoices_checked = '';
                                $balance_input_style = '';
                                $invoices_input_style = 'display: none;';
                            } elseif ($limit_type == 'invoices') {
                                $balance_checked = '';
                                $invoices_checked = 'checked';
                                $balance_input_style = 'display: none;';
                                $invoices_input_style = '';
                            } else {
                                // Handle the case where limit_type is empty or undefined
                                $balance_checked = '';
                                $invoices_checked = '';
                                $balance_input_style = 'display: none;';
                                $invoices_input_style = 'display: none;';
                            }
                        ?>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label" for="docid">Payment Terms</label>
                                <div class="col-sm-8">
                                    <div>
                                        <input type="radio" id="custom_balance_<?= $key ?>" name="limit[<?= $key ?>]" value="balance" <?= $balance_checked ?> onclick="toggleInputField('balance', <?= $key ?>)" />
                                        <label for="custom_balance_<?= $key ?>">Total Balance Limit</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="custom_invoices_<?= $key ?>" name="limit[<?= $key ?>]" value="invoices" <?= $invoices_checked ?> onclick="toggleInputField('invoices', <?= $key ?>)" />
                                        <label for="custom_invoices_<?= $key ?>">No. of Invoices Limit</label>
                                    </div>
                                    <div id="balance_input_<?= $key ?>" class="input-field" style="<?= $balance_input_style ?>">
                                        <label for="balance_limit_<?= $key ?>">Balance Limit: GBP</label>
                                        <input placeholder="Total Balance Limit" type="number" id="balance_limit_<?= $key ?>" name="balance_limit[<?= $key ?>]" value="<?= $limit_value ?>" />
                                    </div>
                                    <div id="invoices_input_<?= $key ?>" class="input-field" style="<?= $invoices_input_style ?>">
                                        <label for="invoices_limit_<?= $key ?>">Invoices Limit:</label>
                                        <input placeholder="No. of Invoices" type="number" id="invoices_limit_<?= $key ?>" name="invoices_limit[<?= $key ?>]" value="<?= $limit_value ?>" />
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-md-6">
                        <h5><?php echo $this->lang->line('Shipping Address') ?></h5>
                        <div class="form-group row">

                            <div class="input-group mt-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="customer1" id="copy_address">
                                    <label class="custom-control-label" for="copy_address"><?php echo $this->lang->line('Same As Billing') ?></label>
                                </div>

                            </div>

                            <div class="col-sm-10">
                                <?php echo $this->lang->line("leave Shipping Address") ?>
                            </div>
                        </div>

                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="product_name"><?php echo $this->lang->line('Name') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="Name" class="form-control margin-bottom" name="name_s" value="<?php echo $customer['name_s'] ?>" id="mcustomer_name_s">
                            </div>
                        </div>


                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="phone"><?php echo $this->lang->line('Phone') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="phone" class="form-control margin-bottom" name="phone_s" value="<?php echo $customer['phone_s'] ?>" id="mcustomer_phone_s">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="email">Email</label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="email" class="form-control margin-bottom" name="email_s" value="<?php echo $customer['email_s'] ?>" id="mcustomer_email_s">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="product_name"><?php echo $this->lang->line('Address') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="address" class="form-control margin-bottom" name="address_s" value="<?php echo $customer['address_s'] ?>" id="mcustomer_address1_s">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="city"><?php echo $this->lang->line('City') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="city" class="form-control margin-bottom" name="city_s" value="<?php echo $customer['city_s'] ?>" id="mcustomer_city_s">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="region"><?php echo $this->lang->line('Region') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="region" class="form-control margin-bottom" name="region_s" value="<?php echo $customer['region_s'] ?>" id="region_s">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="country"><?php echo $this->lang->line('Country') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="Country" class="form-control margin-bottom" name="country_s" value="<?php echo $customer['country_s'] ?>" id="mcustomer_country_s">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label" for="postbox"><?php echo $this->lang->line('PostBox') ?></label>

                            <div class="col-sm-10">
                                <input type="text" placeholder="region" class="form-control margin-bottom" name="postbox_s" value="<?php echo $customer['postbox_s'] ?>" id="postbox_s">
                            </div>
                        </div>

                        <h5>Bank Details</h5>
                        <?php
                        $bank_accounts = json_decode($customer['bank_accounts'], true); // Decode bank account JSON
                        ?>

                        <div id="bankAccounts">
                            <?php foreach ($bank_accounts['ids'] as $index => $bank_id) : ?>
                                <div class="form-group row bank-account" data-id="<?= $index + 1 ?>">
                                    <label class="col-sm-2 col-form-label">Bank Account ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" placeholder="Enter the Bank Account ID" class="form-control margin-bottom b_input" name="bank_id_<?= $index + 1 ?>" value="<?= htmlspecialchars($bank_id) ?>">
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger remove-account">Remove</button>
                                    </div>
                                </div>
                                <div class="form-group row bank-account" data-id="<?= $index + 1 ?>">
                                    <label class="col-sm-2 col-form-label">Bank Account Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" placeholder="Enter the Bank Account Number" class="form-control margin-bottom b_input" name="bank_number_<?= $index + 1 ?>" value="<?= htmlspecialchars($bank_accounts['numbers'][$index]) ?>">
                                    </div>
                                </div>
                                <div class="form-group row bank-account" data-id="<?= $index + 1 ?>">
                                    <label class="col-sm-2 col-form-label">Bank Account Ref</label>
                                    <div class="col-sm-8">
                                        <input type="text" placeholder="Enter the Bank Account Ref" class="form-control margin-bottom b_input" name="bank_ref_<?= $index + 1 ?>" value="<?= htmlspecialchars($bank_accounts['refs'][$index]) ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-10">
                                <button type="button" id="addAccount" class="btn btn-secondary mt-2">Add Another Bank Account</button>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="form-group row">

                    <label class="col-sm-2 col-form-label"></label>

                    <div class="col-sm-4">
                        <input type="submit" id="submit-data" class="btn btn-success margin-bottom" value="Update customer" data-loading-text="Updating...">
                        <input type="hidden" value="customers/editcustomer" id="action-url">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.title = 'Edit Customer';
</script>
<script>
    let accountId = <?= count($bank_accounts['ids']) + 1 ?>;

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