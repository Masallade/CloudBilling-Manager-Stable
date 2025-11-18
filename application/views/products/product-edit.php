<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5><?php echo $this->lang->line('Edit Product') ?></h5>
            <hr>
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
                    <input type="hidden" name="pid" value="<?php echo $product['pid'] ?>">

                    <!-- Image Upload Section - Top -->
                    <div class="card mb-4" style="background: #f8f9fa;">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fa fa-image"></i> Product Image</h6>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label" for="fileupload">Upload Image <small class="text-muted">(jpg, jpeg, png only, max 10 MB)</small></label>
                                    <div class="input-group custom-file">
                                        <input id="fileupload" type="file" name="files[]" class="custom-file-input" accept=".jpg,.jpeg,.png">
                                        <label class="custom-file-label" for="fileupload">Choose Product Image</label>
                                    </div>
                                    <div id="progress" class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <input type="hidden" name="image" id="image" value="<?= $product_image ?>">
                                </div>
                                <div class="col-md-6 text-center">
                                    <div class="border rounded p-3 shadow-sm bg-white" style="max-width: 200px; margin: 0 auto;">
                                        <img src="<?= $image_url ?>" id="previewImage" class="img-fluid rounded" style="max-height: 150px; width: auto;" alt="Preview">
                                    </div>
                                    <small class="text-danger d-block mt-2" id="fileupload_error" style="display:none;"></small>
                                    <small class="d-block mt-2 text-muted">Preview</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Product Information Section -->
                    <h6 class="mb-3"><i class="fa fa-info-circle"></i> Product Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name"><?php echo $this->lang->line('Product Name') ?>*</label>
                                <input type="text" placeholder="Product Name" class="form-control required" name="product_name" id="product_name" value="<?= htmlentities($product['product_name']); ?>" required>
                                <small class="text-danger error-message" id="product_name_error" style="display:none;">Product Name is required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_code"><?php echo $this->lang->line('Product Code') ?></label>
                                <input type="text" placeholder="Product Code" class="form-control" name="product_code" id="product_code" value="<?php echo $product['product_code'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_cat"><?php echo $this->lang->line('Product Category') ?>*</label>
                                <select name="product_cat" class="form-control required" id="product_cat" required>
                                    <?php
                                    echo '<option value="' . $cat_ware['cid'] . '">' . $cat_ware['catt'] . ' (S)</option>';
                                    foreach ($cat as $row) {
                                        $cid = $row['id'];
                                        $title = $row['title'];
                                        echo "<option value='$cid'>$title</option>";
                                    }
                                    ?>
                                </select>
                                <small class="text-danger error-message" id="product_cat_error" style="display:none;">Product Category is required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_warehouse"><?php echo $this->lang->line('Warehouse') ?>*</label>
                                <select name="product_warehouse" id="product_warehouse" class="form-control required" required>
                                    <?php
                                    $current_warehouse_id = isset($cat_ware['wid']) ? $cat_ware['wid'] : 0;
                                    foreach ($warehouse as $row) {
                                        $cid = $row['id'];
                                        $title = $row['title'];
                                        $selected = ($current_warehouse_id == $cid) ? 'selected' : '';
                                        echo "<option value='$cid' $selected>$title</option>";
                                    }
                                    if ($current_warehouse_id && !in_array($current_warehouse_id, array_column($warehouse, 'id'))) {
                                        echo '<option value="' . $current_warehouse_id . '" selected>' . (isset($cat_ware['watt']) ? $cat_ware['watt'] : 'Current Warehouse') . ' (Current)</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-danger" id="warehouse_error" style="display:none;">Please select a warehouse.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Sub Category -->
                    <div class="row" style="display:none;">
                        <label class="col-sm-2 col-form-label" for="sub_cat"><?php echo $this->lang->line('Sub') ?><?php echo $this->lang->line('Category') ?></label>
                        <div class="col-sm-6">
                            <select id="sub_cat" name="sub_cat" class="form-control select-box">
                                <?= '<option value="' . $cat_sub['id'] . '" selected>' . $cat_sub['title'] . ' (S)</option>';
                                foreach ($cat_sub_list as $row) {
                                    $cid = $row['id'];
                                    $title = $row['title'];
                                    echo "<option value='$cid'>$title</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Pricing Section -->
                    <h6 class="mb-3"><i class="fa fa-dollar-sign"></i> Pricing</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_price">Purchase Price *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><?php echo $this->config->item('currency'); ?></span>
                                    <input type="text" name="product_price" id="product_price" class="form-control required" placeholder="0.00" onkeypress="return isNumber(event)" value="<?php echo rtrim(rtrim(number_format($product['product_price'], 6, '.', ''), '0'), '.') ?>" required>
                                </div>
                                <small class="text-danger error-message" id="product_price_error" style="display:none;">Purchase Price is required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fproduct_price">Sales Price</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><?php echo $this->config->item('currency') ?></span>
                                    <input type="text" name="fproduct_price" class="form-control" placeholder="0.00" onkeypress="return isNumber(event)" value="<?php echo rtrim(rtrim(number_format($product['fproduct_price'], 6, '.', ''), '0'), '.') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="display:none;">
                                <label for="p_to_b">Pallet To Boxs Qty</label>
                                <input type="number" name="p_to_b" class="form-control" placeholder="10.00" onkeypress="return isNumber(event)" value="<?php echo $product['p_to_b'] ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Inventory Section -->
                    <h6 class="mb-3"><i class="fa fa-cubes"></i> Inventory</h6>
                    <div class="row">
                    <div class="col-md-6">
                            <label for="product_qty"><?php echo $this->lang->line('Stock Units') ?>*</label>
                            <div class="form-group">
                                <input type="text" placeholder="Stock Units" class="form-control required" name="product_qty" id="product_qty" onkeypress="return isNumber(event)" value="<?php echo amountFormat_general($product['qty']) ?>" required>
                                <small class="text-danger error-message" id="product_qty_error" style="display:none;">Stock Units is required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_qty_alert"><?php echo $this->lang->line('Alert Quantity') ?></label>
                                <input type="text" placeholder="Low Stock Alert Quantity" class="form-control" name="product_qty_alert" id="product_qty_alert" value="<?php echo $product['alert'] ?>" onkeypress="return isNumber(event)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="unit"><?php echo $this->lang->line('Measurement Unit') ?>*</label>
                            <div class="form-group">
                                <select name="unit" class="form-control required" id="unit" required>
                                    <option value=''>None</option>
                                    <?php
                                    $current_unit = isset($product['unit']) ? $product['unit'] : '';
                                    foreach ($units as $row) {
                                        $cid = $row['code'];
                                        $title = $row['name'];
                                        $selected = ($current_unit == $cid) ? 'selected' : '';
                                        echo "<option value='$cid' $selected>$title</option>";
                                    }
                                    ?>
                                </select>
                                <small class="text-danger error-message" id="unit_error" style="display:none;">Measurement Unit is required</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row d-none">
                        <label class="col-sm-2 col-form-label"><?php echo $this->lang->line('BarCode') ?></label>
                        <div class="col-sm-2">
                            <select class="form-control" name="code_type">
                                <?php echo $product['barcode'] ?>
                                <option value="  <?php echo $product['code_type'] ?>"> <?php echo $product['code_type'] ?> *</option>
                                <option value="EAN13">EAN13 - Default</option>
                                <option value="UPCA">UPC</option>
                                <option value="EAN8">EAN8</option>
                                <option value="ISSN">ISSN</option>
                                <option value="ISBN">ISBN</option>
                                <option value="C128A">C128A</option>
                                <option value="C39">C39</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" placeholder="BarCode Numeric Digit 123112345671" class="form-control margin-bottom" name="barcode" value="<?php echo $product['barcode'] ?>" onkeypress="return isNumber(event)">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Additional Information Section -->
                    <h6 class="mb-3"><i class="fa fa-edit"></i> Additional Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="taxable">Taxable</label>
                                <select class="form-control" name="taxable" id="taxable">
                                    <option value="">Select</option>
                                    <option value="T1" <?php if(isset($product['code_type']) && $product['code_type'] == 'T1') echo 'selected'; ?>>Yes</option>
                                    <option value="T0" <?php if(isset($product['code_type']) && $product['code_type'] == 'T0') echo 'selected'; ?>>No</option>
                                </select>
                                <small class="text-danger error-message" id="taxable_error">Taxable is required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" >
                                <label for="wdate"><?php echo $this->lang->line('Valid') . ' (' . $this->lang->line('To Date') ?>)</label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    placeholder="Expiry Date" 
                                    name="wdate" 
                                    id="wdate" 
                                    autocomplete="false"
                                    value="<?php 
                                    $expiry_date = '';
                                    if (isset($product['expiry']) && !empty($product['expiry']) && $product['expiry'] != '0000-00-00' && $product['expiry'] != '0000-00-00 00:00:00') {
                                        // Convert expiry date to Y-m-d format for date input
                                        $expiry_date = date('Y-m-d', strtotime($product['expiry']));
                                    }
                                    echo htmlspecialchars($expiry_date); 
                                    ?>">
                                <small class="text-muted">Do not change if not applicable</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="product_desc"><?php echo $this->lang->line('Description') ?></label>
                                <textarea placeholder="Description" class="form-control" name="product_desc" id="product_desc" rows="3"><?php echo $product['product_des'] ?></textarea>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($custom_fields as $row) {
                        if ($row['f_type'] == 'text') { ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="custom_<?= $row['id'] ?>"><?= $row['name'] ?></label>
                                        <input type="text" placeholder="<?= $row['placeholder'] ?>" class="form-control b_input <?= $row['other'] ?>" name="custom[<?= $row['id'] ?>]" id="custom_<?= $row['id'] ?>" value="<?= $row['data'] ?>">
                                    </div>
                                </div>
                            </div>
                    <?php }
                    }
                    ?>

                    <!-- Hidden Tax and Discount Fields -->
                    <div class="form-group row d-none">
                        <label class="col-sm-2 col-form-label"><?php echo $this->lang->line('Default TAX Rate') ?></label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" name="product_tax" class="form-control" placeholder="0.00" onkeypress="return isNumber(event)" value="<?php echo amountFormat_general($product['taxrate']) ?>"><span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row d-none">
                        <label class="col-sm-2 col-form-label"><?php echo $this->lang->line('Default Discount Rate') ?></label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" name="product_disc" class="form-control" placeholder="0.00" onkeypress="return isNumber(event)" value="<?php echo amountFormat_general($product['disrate']) ?>"><span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Button -->
                    <div class="form-group row">
                        <div class="col-md-12">
                            <button type="submit" id="submit-data" class="btn btn-success" data-loading-text="Updating...">
                                <i class="fa fa-save"></i> <?php echo $this->lang->line('Update') ?>
                            </button>
                            <button type="button" class="btn btn-pink add_serial btn-sm">
                                <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_serial') ?>
                            </button>
                            <input type="hidden" value="products/editproduct" id="action-url">
                        </div>
                    </div>
                    <div id="added_product"></div>

                    <?php
                    if (is_array(@$serial_list[0])) {
                        foreach ($serial_list as $item) { ?>
                            <div class="form-group serial">
                                <label for="field_s" class="col-lg-2 control-label"><?php echo $this->lang->line('serial') ?></label>
                                <div class="col-lg-10">
                                    <input class="form-control box-size" placeholder="<?php echo $this->lang->line('serial') ?>" type="text" value="<?= $item['serial'] ?>" <?= ($item['status'] ? 'readonly=""' : 'name="product_serial_e[' . $item['id'] . ']"'); ?>>
                                </div>
                            </div>
                        <?php
                        }
                    }
                    ?>
                </form>
            </div>
        </div>

        <script src="<?php echo assets_url('assets/myjs/jquery.ui.widget.js'); ?>"></script>
        <script src="<?php echo assets_url('assets/myjs/jquery.fileupload.js') ?>"></script>
        <style>
            .error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }

            .custom-file.error .custom-file-input {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            select.error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            textarea.error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            .radio-inline.error {
                border: 1px solid #dc3545;
                border-radius: 4px;
                padding: 5px 10px;
            }

            #fileupload_error {
                margin-top: 5px;
                font-size: 0.875rem;
            }
            .card-body h6 {
                color: #495057;
                font-weight: 600;
                border-bottom: 2px solid #e9ecef;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .form-group label {
                font-weight: 500;
                color: #495057;
                margin-bottom: 5px;
            }
        </style>
        <script>
            document.title = 'Edit Product';

            $(function() {
                'use strict';
                var url = '<?php echo base_url() ?>products/file_handling';

                $('#fileupload').fileupload({
                        url: url,
                        dataType: 'json',
                        formData: {
                            '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                        },
                        add: function(e, data) {
                            var file = data.files[0];
                            var hasError = false;
                            var errorMessage = '';

                            // ✅ Check file type
                            var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                            if (!allowedTypes.includes(file.type)) {
                                hasError = true;
                                errorMessage = 'Only JPG, JPEG, and PNG files are allowed.';
                            }

                            // ✅ Check file size (10 MB = 10485760 bytes)
                            var maxSize = 10 * 1024 * 1024; // 10 MB
                            if (file.size > maxSize) {
                                hasError = true;
                                errorMessage = 'File size must be 10 MB or less.';
                            }

                            if (hasError) {
                                // Show error message
                                $('#fileupload_error').text(errorMessage).css('display', 'block');
                                $('#fileupload').closest('.custom-file').addClass('error');
                                $('#fileupload').addClass('error');

                                // Set progress bar to red/orange for error
                                $('#progress .progress-bar')
                                    .removeClass('bg-success')
                                    .addClass('bg-danger')
                                    .css('width', '100%');

                                // Reset file input
                                $('#fileupload').val('');
                                $('.custom-file-label').text('Choose Product Image');
                                $('#previewImage').attr('src', '<?= $image_url ?>');

                                // Prevent file upload
                                return false;
                            }

                            // Hide error message if file is valid
                            $('#fileupload_error').hide();
                            $('#fileupload').closest('.custom-file').removeClass('error');
                            $('#fileupload').removeClass('error');

                            // Reset progress bar to green and 0% for valid file
                            $('#progress .progress-bar')
                                .removeClass('bg-danger bg-warning')
                                .addClass('bg-success')
                                .css('width', '0%');

                            // ✅ Show preview immediately
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $('#previewImage').attr('src', e.target.result);
                            };
                            reader.readAsDataURL(file);

                            // ✅ Update file name in label
                            $('.custom-file-label').text(file.name);

                            // ✅ Submit the file
                            data.submit();
                        },
                        done: function(e, data) {
                            // Reset progress bar to 100% on success
                            $('#progress .progress-bar')
                                .removeClass('bg-danger bg-warning')
                                .addClass('bg-success')
                                .css('width', '100%');
                            
                            // Hide any error messages
                            $('#fileupload_error').hide();
                            $('#fileupload').closest('.custom-file').removeClass('error');
                            $('#fileupload').removeClass('error');
                            
                            // Handle response - check if files array exists
                            if (data.result && data.result.files && data.result.files.length > 0) {
                                var file = data.result.files[0];
                                if (file && file.name) {
                                    $('#image').val(file.name);
                                    $('#previewImage').attr('src', '<?php echo base_url('userfiles/product/'); ?>' + file.name);
                                    
                                    // Update files list if element exists
                                    if ($('#files').length) {
                                        $('#files').html(`
                                            <tr>
                                                <td>
                                                    <a data-url="<?php echo base_url() ?>products/file_handling?op=delete&name=${file.name}" class="aj_delete">
                                                        <i class="btn-danger btn-sm icon-trash-a"></i> ${file.name}
                                                    </a>
                                                    <br>
                                                    <img style="max-height:200px;" src="<?php echo base_url('userfiles/product/'); ?>${file.name}">
                                                </td>
                                            </tr>
                                        `);
                                    }
                                    console.log('Image uploaded successfully:', file.name);
                                }
                            } else if (data.result && data.result.files) {
                                // Handle case where files might be an object instead of array
                                $.each(data.result.files, function(index, file) {
                                    if (file && file.name) {
                                        $('#image').val(file.name);
                                        $('#previewImage').attr('src', '<?php echo base_url('userfiles/product/'); ?>' + file.name);
                                        console.log('Image uploaded successfully:', file.name);
                                    }
                                });
                            } else {
                                console.error('Unexpected response structure:', data.result);
                            }
                        },
                        fail: function(e, data) {
                            // Handle upload failure
                            $('#progress .progress-bar')
                                .removeClass('bg-success bg-warning')
                                .addClass('bg-danger')
                                .css('width', '100%');
                            
                            var errorMsg = 'File upload failed. Please try again.';
                            
                            // Check for error in response
                            if (data.result) {
                                // Check if files array has error
                                if (data.result.files && data.result.files.length > 0) {
                                    var file = data.result.files[0];
                                    if (file.error) {
                                        errorMsg = file.error;
                                    }
                                } else if (data.result.error) {
                                    errorMsg = data.result.error;
                                }
                            }
                            
                            // Check jqXHR response
                            if (data.jqXHR) {
                                if (data.jqXHR.responseJSON) {
                                    if (data.jqXHR.responseJSON.files && data.jqXHR.responseJSON.files.length > 0) {
                                        var file = data.jqXHR.responseJSON.files[0];
                                        if (file.error) {
                                            errorMsg = file.error;
                                        }
                                    } else if (data.jqXHR.responseJSON.error) {
                                        errorMsg = data.jqXHR.responseJSON.error;
                                    }
                                } else if (data.jqXHR.status === 0) {
                                    errorMsg = 'Network error. Please check your connection.';
                                } else if (data.jqXHR.status === 413) {
                                    errorMsg = 'File too large. Maximum size is 10 MB.';
                                } else if (data.jqXHR.status >= 500) {
                                    errorMsg = 'Server error. Please try again later.';
                                } else if (data.jqXHR.status === 403) {
                                    errorMsg = 'Permission denied. Please check file permissions.';
                                }
                            }
                            
                            // Check for text response
                            if (data.textStatus) {
                                if (data.textStatus === 'error') {
                                    if (errorMsg.indexOf('Network error') === -1) {
                                        errorMsg = 'Upload error: ' + (data.jqXHR ? data.jqXHR.statusText : 'Unknown error');
                                    }
                                }
                            }
                            
                            $('#fileupload_error').text(errorMsg).css('display', 'block');
                            $('#fileupload').closest('.custom-file').addClass('error');
                            $('#fileupload').addClass('error');
                            console.error('File upload failed:', {
                                result: data.result,
                                jqXHR: data.jqXHR ? {
                                    status: data.jqXHR.status,
                                    statusText: data.jqXHR.statusText,
                                    responseText: data.jqXHR.responseText,
                                    responseJSON: data.jqXHR.responseJSON
                                } : null,
                                textStatus: data.textStatus,
                                errorThrown: data.errorThrown
                            });
                        },
                        progressall: function(e, data) {
                            var progress = parseInt(data.loaded / data.total * 100, 10);
                            $('#progress .progress-bar').css('width', progress + '%');
                        }
                    }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');

                // Also validate on native file input change (before fileupload plugin processes it)
                $('#fileupload').on('change', function(e) {
                    var file = this.files[0];
                    if (file) {
                        var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        var maxSize = 10 * 1024 * 1024; // 10 MB

                        if (!allowedTypes.includes(file.type)) {
                            $('#fileupload_error').text('Only JPG, JPEG, and PNG files are allowed.').css('display', 'block');
                            $(this).closest('.custom-file').addClass('error');
                            $(this).addClass('error');
                            $(this).val('');
                            $('.custom-file-label').text('Choose Product Image');

                            // Set progress bar to red for error
                            $('#progress .progress-bar')
                                .removeClass('bg-success bg-warning')
                                .addClass('bg-danger')
                                .css('width', '100%');

                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }

                        if (file.size > maxSize) {
                            $('#fileupload_error').text('File size must be 10 MB or less.').css('display', 'block');
                            $(this).closest('.custom-file').addClass('error');
                            $(this).addClass('error');
                            $(this).val('');
                            $('.custom-file-label').text('Choose Product Image');

                            // Set progress bar to red for error
                            $('#progress .progress-bar')
                                .removeClass('bg-success bg-warning')
                                .addClass('bg-danger')
                                .css('width', '100%');

                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }

                        // File is valid, hide error and reset progress bar
                        $('#fileupload_error').hide();
                        $(this).closest('.custom-file').removeClass('error');
                        $(this).removeClass('error');

                        // Reset progress bar to green and 0%
                        $('#progress .progress-bar')
                            .removeClass('bg-danger bg-warning')
                            .addClass('bg-success')
                            .css('width', '0%');
                    }
                });
            });

            $(document).on('click', ".aj_delete", function(e) {
                e.preventDefault();
                var aurl = $(this).attr('data-url');
                var obj = $(this);
                jQuery.ajax({
                    url: aurl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        obj.closest('tr').remove();
                        obj.remove();
                    }
                });
            });

            $("#sub_cat").select2();
            $("#product_cat").on('change', function() {
                $("#sub_cat").val('').trigger('change');
                var tips = $('#product_cat').val();
                $("#sub_cat").select2({
                    ajax: {
                        url: baseurl + 'products/sub_cat?id=' + tips,
                        dataType: 'json',
                        type: 'POST',
                        quietMillis: 50,
                        data: function(product) {
                            return {
                                product: product,
                                '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.title,
                                        id: item.id
                                    }
                                })
                            };
                        },
                    }
                });
            });
            $(document).on('click', ".tr_clone_add", function(e) {
                e.preventDefault();
                var n_row = $('#v_var').find('tbody').find("tr:last").clone();
                $('#v_var').find('tbody').find("tr:last").after(n_row);
            });
            $(document).on('click', ".tr_clone_add_w", function(e) {
                e.preventDefault();
                var n_row = $('#w_var').find('tbody').find("tr:last").clone();
                $('#w_var').find('tbody').find("tr:last").after(n_row);
            });
            $(document).on('click', ".tr_delete", function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });
            $(document).on('click', ".v_delete_serial", function(e) {
                e.preventDefault();
                $(this).closest('div .serial').remove();
            });

            $(document).on('click', ".add_serial", function(e) {
                e.preventDefault();
                $('#added_product').append('<div class="form-group serial"><label for="field_s" class="col-lg-2 control-label"><?= $this->lang->line('serial') ?></label><div class="col-lg-10"><input class="form-control box-size" placeholder="<?= $this->lang->line('serial') ?>" name="product_serial[]" type="text"  value=""></div><button class="btn-sm btn-purple v_delete_serial m-1 align-content-end"><i class="fa fa-trash"></i> </button></div>');
            });

            // Comprehensive validation for all required fields (marked with *)
            function validateRequiredFields() {
                var isValid = true;
                var firstErrorField = null;
                
                // Remove previous error styling
                $('.form-control, select').removeClass('error');
                $('.error-message').hide();
                
                // Validate Product Name
                var productName = $('#product_name').val();
                if (!productName || productName.trim() === '' || productName === null) {
                    isValid = false;
                    $('#product_name').addClass('error');
                    $('#product_name_error').show();
                    if (!firstErrorField) {
                        firstErrorField = $('#product_name');
                    }
                } else {
                    $('#product_name').removeClass('error');
                    $('#product_name_error').hide();
                }
                
                // Validate Product Category
                var category = $('#product_cat').val();
                if (!category || category.trim() === '' || category === null || category === '0') {
                    isValid = false;
                    $('#product_cat').addClass('error');
                    $('#product_cat_error').show();
                    if (!firstErrorField) {
                        firstErrorField = $('#product_cat');
                    }
                } else {
                    $('#product_cat').removeClass('error');
                    $('#product_cat_error').hide();
                }
                
                // Validate Warehouse
                var warehouse = $('#product_warehouse').val();
                if (!warehouse || warehouse == '' || warehouse == null) {
                    isValid = false;
                    $('#warehouse_error').show();
                    $('#product_warehouse').addClass('error');
                    if (!firstErrorField) {
                        firstErrorField = $('#product_warehouse');
                    }
                } else {
                    $('#warehouse_error').hide();
                    $('#product_warehouse').removeClass('error');
                }
                
                // Validate VAT Status (radio buttons)
                var taxable = $('input[name="taxable"]:checked').val();
                if (!taxable || taxable === null || taxable === undefined) {
                    isValid = false;
                    $('input[name="taxable"]').closest('.form-group').addClass('error');
                    $('input[name="taxable"]').closest('label').addClass('error');
                    $('#taxable_error').show();
                    if (!firstErrorField) {
                        firstErrorField = $('input[name="taxable"]').first();
                    }
                } else {
                    $('input[name="taxable"]').closest('.form-group').removeClass('error');
                    $('input[name="taxable"]').closest('label').removeClass('error');
                    $('#taxable_error').hide();
                }
                
                // Validate Purchase Price
                var purchasePrice = $('#product_price').val();
                if (!purchasePrice || purchasePrice.trim() === '' || purchasePrice === null || isNaN(parseFloat(purchasePrice))) {
                    isValid = false;
                    $('#product_price').addClass('error');
                    $('#product_price_error').show();
                    if (!firstErrorField) {
                        firstErrorField = $('#product_price');
                    }
                } else {
                    var numValue = parseFloat(purchasePrice);
                    if (isNaN(numValue) || numValue < 0) {
                        isValid = false;
                        $('#product_price').addClass('error');
                        $('#product_price_error').show();
                        if (!firstErrorField) {
                            firstErrorField = $('#product_price');
                        }
                    } else {
                        $('#product_price').removeClass('error');
                        $('#product_price_error').hide();
                    }
                }
                
                // Validate Stock Units
                var stockUnits = $('#product_qty').val();
                if (!stockUnits || stockUnits.trim() === '' || stockUnits === null || isNaN(parseFloat(stockUnits))) {
                    isValid = false;
                    $('#product_qty').addClass('error');
                    $('#product_qty_error').show();
                    if (!firstErrorField) {
                        firstErrorField = $('#product_qty');
                    }
                } else {
                    var numValue = parseFloat(stockUnits);
                    if (isNaN(numValue) || numValue < 0) {
                        isValid = false;
                        $('#product_qty').addClass('error');
                        $('#product_qty_error').show();
                        if (!firstErrorField) {
                            firstErrorField = $('#product_qty');
                        }
                    } else {
                        $('#product_qty').removeClass('error');
                        $('#product_qty_error').hide();
                    }
                }
                
                // Validate Measurement Unit
                var measurementUnit = $('#unit').val();
                if (!measurementUnit || measurementUnit.trim() === '' || measurementUnit === null) {
                    isValid = false;
                    $('#unit').addClass('error');
                    $('#unit_error').show();
                    if (!firstErrorField) {
                        firstErrorField = $('#unit');
                    }
                } else {
                    $('#unit').removeClass('error');
                    $('#unit_error').hide();
                }
                
                // Focus on first error field
                if (firstErrorField) {
                    firstErrorField.focus();
                    // Scroll to first error field
                    $('html, body').animate({
                        scrollTop: firstErrorField.offset().top - 100
                    }, 500);
                }
                
                return isValid;
            }
            
            // Form submission validation
            $(document).on('submit', '#data_form', function(e) {
                // Validate all required fields
                if (!validateRequiredFields()) {
                    e.preventDefault();
                    // Show notification
                    $('#notify').removeClass('alert-success').addClass('alert-danger');
                    $('#notify .message').html('<strong>Error:</strong> Please fill in all required fields (marked with *)');
                    $('#notify').fadeIn();
                    setTimeout(function() {
                        $('#notify').fadeOut();
                    }, 5000);
                    return false;
                }
                
                // If validation passes, hide any error messages
                $('.error-message').hide();
                $('#warehouse_error').hide();
            });

            // Remove error styling when Product Name is changed
            $('#product_name').on('input', function() {
                var value = $(this).val();
                if (value && value.trim() !== '') {
                    $(this).removeClass('error');
                    $('#product_name_error').hide();
                }
            });
            
            // Remove error styling when Product Category is changed
            $('#product_cat').on('change', function() {
                var value = $(this).val();
                if (value && value.trim() !== '' && value !== '0') {
                    $(this).removeClass('error');
                    $('#product_cat_error').hide();
                }
            });
            
            // Remove error styling when Warehouse is selected
            $('#product_warehouse').on('change', function() {
                if ($(this).val()) {
                    $('#warehouse_error').hide();
                    $(this).removeClass('error');
                }
            });
            
            // Remove error styling when VAT Status is selected
            $('input[name="taxable"]').on('change', function() {
                $('input[name="taxable"]').closest('.form-group').removeClass('error');
                $('input[name="taxable"]').closest('label').removeClass('error');
                $('#taxable_error').hide();
            });
            
            // Remove error styling when Purchase Price is changed
            $('#product_price').on('input', function() {
                var value = $(this).val();
                if (value && value.trim() !== '' && !isNaN(parseFloat(value)) && parseFloat(value) >= 0) {
                    $(this).removeClass('error');
                    $('#product_price_error').hide();
                }
            });
            
            // Remove error styling when Stock Units is changed
            $('#product_qty').on('input', function() {
                var value = $(this).val();
                if (value && value.trim() !== '' && !isNaN(parseFloat(value)) && parseFloat(value) >= 0) {
                    $(this).removeClass('error');
                    $('#product_qty_error').hide();
                }
            });
            
            // Remove error styling when Measurement Unit is changed
            $('#unit').on('change', function() {
                var value = $(this).val();
                if (value && value.trim() !== '' && value !== null) {
                    $(this).removeClass('error');
                    $('#unit_error').hide();
                }
            });

            // Price validation to prevent purchase price from being higher than sales price
            $(document).ready(function() {
                $('input[name="fproduct_price"], input[name="product_price"]').on('input', function() {
                    var salesPrice = parseFloat($('input[name="fproduct_price"]').val()) || 0;
                    var purchasePrice = parseFloat($('input[name="product_price"]').val()) || 0;

                    if (salesPrice < 0) {
                        $('input[name="fproduct_price"]').val(0);
                    }
                    if (purchasePrice < 0) {
                        $('input[name="product_price"]').val(0);
                    }
                    // Compare the values
                    if (purchasePrice > salesPrice && salesPrice > 0) {
                        // Set purchase price to sales price
                        $('input[name="product_price"]').val(salesPrice.toFixed(2));
                    }
                });
            });
        </script>
    </div>
</div>