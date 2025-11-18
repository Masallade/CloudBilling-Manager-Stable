<div class="content-body">
    <div class="card">
        <div class="card-header pb-0">
            <h5><?php echo $this->lang->line('Add New Product') ?></h5>
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

        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <div class="card-body">
            <form method="post" id="data_form">
                <input type="hidden" name="act" value="add_product">

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
                                <input type="hidden" name="image" id="image" value="<?php echo $product->image ?? 'default.png'; ?>">
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="border rounded p-3 shadow-sm bg-white" style="max-width: 200px; margin: 0 auto;">
                                    <img id="previewImage" src="<?php echo base_url('userfiles/product/' . ($product->image ?? 'default.png')); ?>" class="img-fluid rounded" style="max-height: 150px; width: auto;" alt="Preview">
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
                        <label for="product_name"><?php echo $this->lang->line('Product Name') ?>*</label>
                        <div class="form-group">
                            <input type="text" placeholder="Product Name" class="form-control required" name="product_name" id="product_name" required>
                            <small class="text-danger error-message" id="product_name_error" style="display:none;">Product Name is required</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="product_code"><?php echo $this->lang->line('Product Code') ?>*</label>
                        <div class="form-group">
                            <input type="text" placeholder="Product Code" class="form-control required" name="product_code" id="product_code" required>
                            <small class="text-danger error-message" id="product_code_error" style="display:none;">Product Code is required</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="product_cat"><?php echo $this->lang->line('Product Category') ?>*</label>
                        <div class="form-group">
                            <select name="product_cat" id="product_cat" class="form-control required" required>
                                <option value="">Select Category</option>
                                <?php
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
                        <label for="product_warehouse"><?php echo $this->lang->line('Warehouse') ?>*</label>
                        <div class="form-group">
                            <select name="product_warehouse" id="product_warehouse" class="form-control required" required>
                                <option value="">Select Warehouse</option>
                                <?php
                                foreach ($warehouse as $row) {
                                    $cid = $row['id'];
                                    $title = $row['title'];
                                    echo "<option value='$cid'>$title</option>";
                                }
                                ?>
                            </select>
                            <small class="text-danger" id="warehouse_error" style="display:none;">Please select a warehouse first before creating a product.</small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Pricing Section -->
                <h6 class="mb-3"><i class="fa fa-dollar-sign"></i> Pricing</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fproduct_price">Sales Price*</label>
                            <div class="input-group">
                                <span class="input-group-addon"><?php echo $this->config->item('currency') ?></span>
                                <input type="text" name="fproduct_price" class="form-control required" placeholder="0.00" onkeypress="return isNumber(event)" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="product_price">Purchase Price*</label>
                            <div class="input-group">
                                <span class="input-group-addon"><?php echo $this->config->item('currency') ?></span>
                                <input type="text" name="product_price" id="product_price" class="form-control required" placeholder="0.00" onkeypress="return isNumber(event)" required>
                            </div>
                            <small class="text-danger error-message" id="product_price_error" style="display:none;">Purchase Price is required</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="p_to_b">Pallet To Boxs Qty</label>
                            <input type="number" name="p_to_b" class="form-control" placeholder="10.00" onkeypress="return isNumber(event)">
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
                            <input type="text" placeholder="Stock Units" class="form-control required" name="product_qty" id="product_qty" onkeypress="return isNumber(event)" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="product_qty_alert"><?php echo $this->lang->line('Alert Quantity') ?></label>
                            <input type="text" placeholder="Alert Quantity" class="form-control" name="product_qty_alert" id="product_qty_alert" onkeypress="return isNumber(event)">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="unit"><?php echo $this->lang->line('Measurement Unit') ?>*</label>
                        <div class="form-group">
                            <select name="unit" class="form-control required" id="unit" required>
                                <option value=''>None</option>
                                <?php
                                foreach ($units as $row) {
                                    $cid = $row['code'];
                                    $title = $row['name'];
                                    echo "<option value='$cid'>$title - $cid</option>";
                                }
                                ?>
                            </select>
                            <small class="text-danger error-message" id="unit_error" style="display:none;">Measurement Unit is required</small>
                        </div>
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
                                <option value="T1">Yes</option>
                                <option value="T0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="wdate"><?php echo $this->lang->line('Valid') . ' (' . $this->lang->line('To Date') ?>)</label>
                            <input type="date" class="form-control" placeholder="Expiry Date" name="wdate" id="wdate"  autocomplete="false" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>">
                            <small class="text-muted">Do not change if not applicable</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="product_desc"><?php echo $this->lang->line('Description') ?>*</label>
                        <div class="form-group">
                            <textarea placeholder="Description" class="form-control required" name="product_desc" id="product_desc" rows="3" required></textarea>
                            <small class="text-danger error-message" id="product_desc_error" style="display:none;">Description is required</small>
                        </div>
                    </div>
                </div>

                <?php
                foreach ($custom_fields as $row) {
                    if ($row['f_type'] == 'text') { ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="custom_<?= $row['id'] ?>"><?= $row['name'] ?></label>
                                    <input type="text" placeholder="<?= $row['placeholder'] ?>" class="form-control b_input <?= $row['other'] ?>" name="custom[<?= $row['id'] ?>]" id="custom_<?= $row['id'] ?>">
                                </div>
                            </div>
                        </div>
                <?php }
                }
                ?>

                <!-- Hidden Fields -->
                <div class="form-group row d-none">
                    <label class="col-sm-2 control-label" for="product_price"><?php echo $this->lang->line('Default TAX Rate') ?></label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <input type="text" name="product_tax" class="form-control" placeholder="<?php echo $this->lang->line('Default TAX Rate') ?>" onkeypress="return isNumber(event)"><span class="input-group-addon">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group row d-none">
                    <label class="col-sm-2 control-label" for="product_price"><?php echo $this->lang->line('Default Discount Rate') ?></label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <input type="text" name="product_disc" class="form-control" placeholder="<?php echo $this->lang->line('Default Discount Rate') ?>" onkeypress="return isNumber(event)"><span class="input-group-addon">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group row d-none">
                    <div class="col-sm-4">
                        <input type="text" placeholder="BarCode Numeric Digit 123112345671" class="form-control margin-bottom" name="barcode" onkeypress="return isNumber(event)">
                        <small>Leave blank if you want auto generated in EAN13.</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Submit Button -->
                <div class="form-group row">
                    <div class="col-md-12">
                        <button type="submit" id="submit-data" class="btn btn-lg btn-blue" data-loading-text="Adding...">
                            <i class="fa fa-save"></i> <?php echo $this->lang->line('Add product') ?>
                        </button>
                        <button type="button" class="btn btn-pink add_serial btn-sm">
                            <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_serial') ?>
                        </button>
                        <input type="hidden" value="products/addproduct" id="action-url">
                        <input type="hidden" value="index_prod" id="index_prod">
                    </div>
                </div>
                <div id="added_product"></div>
            </form>
        </div>
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
    document.title = 'Add Product';
    
    $(document).ready(function() {
        $('input[name="fproduct_price"], input[name="product_price"]').on('input', function() {
            var salesPrice = parseFloat($('input[name="fproduct_price"]').val()) || 0;
            var purchasePrice = parseFloat($('input[name="product_price"]').val()) || 0;

            if (salesPrice < 0) {
                $('input[name="fproduct_price"]').val(0)
            }
            if (purchasePrice < 0) {
                $('input[name="product_price"]').val(0)
            }
            if (purchasePrice > salesPrice) {
                $('input[name="product_price"]').val(salesPrice.toFixed(2));
                $('#price-error').text('');
            }
        });
    });

    $(function() {
        'use strict';
        const url = '<?php echo base_url() ?>products/file_handling';
        const maxSize = 10 * 1024 * 1024; // 10 MB

        $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                formData: {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                },
                add: function(e, data) {
                    const file = data.files[0];
                    let hasError = false;
                    let errorMessage = '';

                    // ✅ Check file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        hasError = true;
                        errorMessage = 'Only JPG, JPEG, and PNG files are allowed.';
                    }

                    // ✅ Check file size
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
                        $('#previewImage').attr('src', '<?php echo base_url('userfiles/product/default.png'); ?>');
                        
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

                    // ✅ Show file name
                    $('.custom-file-label').text(file.name);

                    // ✅ Preview image immediately
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImage').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);

                    // ✅ Automatically submit file
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
                    
                    let img = 'default.png';
                    
                    // Handle response - check if files array exists
                    if (data.result && data.result.files && data.result.files.length > 0) {
                        // Get the first file from the response
                        const file = data.result.files[0];
                        if (file && file.name) {
                            img = file.name;
                            $('#previewImage').attr('src', '<?php echo base_url('userfiles/product/'); ?>' + file.name);
                            $('#image').val(file.name);
                            console.log('Image uploaded successfully:', file.name);
                        }
                    } else if (data.result && data.result.files) {
                        // Handle case where files might be an object instead of array
                        $.each(data.result.files, function(index, file) {
                            if (file && file.name) {
                                $('#previewImage').attr('src', '<?php echo base_url('userfiles/product/'); ?>' + file.name);
                                img = file.name;
                                $('#image').val(file.name);
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
                    
                    let errorMsg = 'File upload failed. Please try again.';
                    
                    // Check for error in response
                    if (data.result) {
                        // Check if files array has error
                        if (data.result.files && data.result.files.length > 0) {
                            const file = data.result.files[0];
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
                                const file = data.jqXHR.responseJSON.files[0];
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
                            if (!errorMsg.includes('Network error')) {
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
                    const progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css('width', progress + '%');
                }
            }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

        // Also validate on native file input change (before fileupload plugin processes it)
        $('#fileupload').on('change', function(e) {
            const file = this.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 10 * 1024 * 1024; // 10 MB
                
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
        $('.form-control, .form-select').removeClass('error');
        $('.error-message').remove();
        
        // List of required fields with their labels and types
        var requiredFields = [
            { id: 'product_name', label: 'Product Name', type: 'text' },
            { id: 'product_code', label: 'Product Code', type: 'text' },
            { id: 'product_cat', label: 'Product Category', type: 'select' },
            { id: 'product_warehouse', label: 'Warehouse', type: 'select' },
            { id: 'fproduct_price', label: 'Sales Price', type: 'number' },
            { id: 'product_price', label: 'Purchase Price', type: 'number' },
            { id: 'product_qty', label: 'Stock Units', type: 'number' },
            { id: 'unit', label: 'Measurement Unit', type: 'select' },
            { id: 'product_desc', label: 'Description', type: 'text' }
        ];
        
        // Validate each required field
        requiredFields.forEach(function(field) {
            var $field = $('#' + field.id);
            var value = $field.val();
            var isEmpty = false;
            
            // Check if field is empty based on type
            if (field.type === 'number') {
                // For numeric fields, check if empty, null, or NaN (but allow 0)
                if (!value || value.trim() === '' || value === null || isNaN(parseFloat(value))) {
                    isEmpty = true;
                } else {
                    // Additional validation: ensure it's a valid positive number (0 is allowed)
                    var numValue = parseFloat(value);
                    if (isNaN(numValue) || numValue < 0) {
                        isEmpty = true;
                    }
                }
            } else if (field.type === 'select') {
                // For select fields, check if empty, null, or default option
                // Special case for unit field which has "None" as empty value
                if (field.id === 'unit') {
                    if (!value || value.trim() === '' || value === null) {
                        isEmpty = true;
                    }
                } else {
                    // For other select fields, check if empty or default option
                    if (!value || value.trim() === '' || value === null || value === '0') {
                        isEmpty = true;
                    }
                }
            } else {
                // For text fields (including textarea), check if empty or just whitespace
                if (!value || value.trim() === '' || value === null) {
                    isEmpty = true;
                }
            }
            
            if (isEmpty) {
                isValid = false;
                
                // Add error styling
                $field.addClass('error');
                
                // Add error message below the field
                var errorMsg = '<small class="text-danger error-message d-block mt-1">' + field.label + ' is required</small>';
                if ($field.next('.error-message').length === 0) {
                    $field.after(errorMsg);
                }
                
                // Also show specific error message if exists
                var $specificError = $('#' + field.id + '_error');
                if ($specificError.length) {
                    $specificError.show();
                }
                
                // Store first error field for focus
                if (!firstErrorField) {
                    firstErrorField = $field;
                }
            } else {
                // Remove error styling if field is valid
                $field.removeClass('error');
                $field.next('.error-message').remove();
                // Hide specific error messages
                var $specificError = $('#' + field.id + '_error');
                if ($specificError.length) {
                    $specificError.hide();
                }
            }
        });
        
        // Special validation for Product Name
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
        
        // Special validation for Product Code
        var productCode = $('#product_code').val();
        if (!productCode || productCode.trim() === '' || productCode === null) {
            isValid = false;
            $('#product_code').addClass('error');
            $('#product_code_error').show();
            if (!firstErrorField) {
                firstErrorField = $('#product_code');
            }
        } else {
            $('#product_code').removeClass('error');
            $('#product_code_error').hide();
        }
        
        // Special validation for Product Category
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
        
        // Special validation for warehouse
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
    
    // Warehouse validation before form submission
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
        
        // Log image value for debugging
        var imageValue = $('#image').val();
        console.log('Form submission - Image value:', imageValue);
        if (!imageValue || imageValue === 'default.png') {
            console.warn('Warning: Image may not have been uploaded. Current value:', imageValue);
        }
        
        // If validation passes, hide any error messages
        $('.error-message').remove();
        $('#warehouse_error').hide();
    });

    // Remove error styling when Product Name is changed
    $('#product_name').on('input', function() {
        var value = $(this).val();
        if (value && value.trim() !== '') {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
            $('#product_name_error').hide();
        }
    });
    
    // Remove error styling when Product Code is changed
    $('#product_code').on('input', function() {
        var value = $(this).val();
        if (value && value.trim() !== '') {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
            $('#product_code_error').hide();
        }
    });
    
    // Remove error styling when Product Category is changed
    $('#product_cat').on('change', function() {
        var value = $(this).val();
        if (value && value.trim() !== '' && value !== '0') {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
            $('#product_cat_error').hide();
        }
    });
    
    // Remove error styling when warehouse is changed
    $('#product_warehouse').on('change', function() {
        if ($(this).val()) {
            $('#warehouse_error').hide();
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
        }
    });
    
    // Remove error styling when other required fields are changed
    $('#fproduct_price, #product_price, #product_qty, #unit, #product_desc').on('input change', function() {
        var $field = $(this);
        var value = $field.val();
        var isValid = false;
        
        // Check field type and validate accordingly
        if ($field.is('select')) {
            // For select fields, check if a valid option is selected
            if (value && value.trim() !== '' && value !== '0') {
                isValid = true;
            }
        } else if ($field.is('textarea') || $field.attr('type') === 'text') {
            // For text/textarea fields, check if not empty
            if (value && value.trim() !== '') {
                isValid = true;
            }
        } else {
            // For number fields, check if valid number
            if (value && value.trim() !== '' && !isNaN(parseFloat(value))) {
                isValid = true;
            }
        }
        
        if (isValid) {
            $field.removeClass('error');
            $field.next('.error-message').remove();
            $field.siblings('.error-message').remove();
            // Also hide specific error messages
            $('#' + $field.attr('id') + '_error').hide();
        }
    });
</script>