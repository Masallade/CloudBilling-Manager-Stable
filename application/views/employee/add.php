<article class="content-body">
    <div class="card card-block">
        <!-- Notification Alert -->
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <form method="post" id="data_form" class="form-horizontal" enctype="multipart/form-data">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
            <div class="card-body">
                <h5><?php echo $this->lang->line('Add Employee'); ?></h5>
                <hr>
                
                <!-- User Information Section -->
                <div class="mb-4">
                    <h5><i class="fa fa-user-circle"></i> <?php echo $this->lang->line('User Information'); ?></h5>
                    <hr>
                    
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label" for="username">
                            <?php echo $this->lang->line('UserName'); ?>
                            <small class="text-muted d-block">(Use Only a-z0-9)</small>
                        </label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control margin-bottom required" name="username"
                                placeholder="<?php echo $this->lang->line('UserName'); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="email">
                            <?php echo $this->lang->line('Email'); ?>
                        </label>
                        <div class="col-sm-4">
                            <input type="email" placeholder="<?php echo $this->lang->line('Email'); ?>" 
                                class="form-control margin-bottom required" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label" for="password">
                            <?php echo $this->lang->line('Password'); ?>
                            <small class="text-muted d-block">(min 6 | max 20 | a-zA-Z 0-9 @ $)</small>
                        </label>
                        <div class="col-sm-4">
                            <input type="password" placeholder="<?php echo $this->lang->line('Password'); ?>" 
                                class="form-control margin-bottom required" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <?php if (!empty($roles)): ?>
                            <label class="col-sm-2 col-form-label" for="roleid">
                                <?php echo $this->lang->line('UserRole'); ?>
                            </label>
                            <div class="col-sm-4">
                                <select name="roleid" class="form-control margin-bottom" required>
                                    <option value=""><?php echo $this->lang->line('Select'); ?> <?php echo $this->lang->line('Role'); ?></option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= htmlspecialchars($role['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="roleid" value="3">
                            <div class="col-sm-6">
                                <div class="alert alert-info mb-0">
                                    <strong><?php echo $this->lang->line('Note'); ?>:</strong> No roles found. Default role will be assigned.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label" for="location">
                            <?php echo $this->lang->line('Business Location'); ?>
                        </label>
                        <div class="col-sm-4">
                            <select name="location" class="form-control margin-bottom">
                                <option value="0"><?php echo $this->lang->line('Default'); ?></option>
                                <?php
                                $loc = locations();
                                if (!empty($loc)) {
                                    foreach ($loc as $row) {
                                        echo '<option value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['cname'], ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-sm-6"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <!-- Profile Picture Section -->
                        <div class="mb-3">
                            <div class="grid_3 grid_4">
                                <h5><i class="fa fa-user-circle"></i> <?php echo $this->lang->line('Profile Picture'); ?></h5>
                                <hr>
                                <div class="text-center">
                                    <div class="image-container" style="max-height: 200px; overflow: hidden; border-radius: 8px; border: 2px solid #e9ecef;">
                                        <img alt="<?php echo $this->lang->line('Profile Picture'); ?>" id="dpic"
                                            class="img-fluid"
                                            src="<?php echo base_url('assets/images/default-avatar.png'); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="fileupload" class="form-label">
                                        <i class="fa fa-upload"></i> <?php echo $this->lang->line('Upload Picture'); ?>
                                    </label>
                                    <input id="fileupload" type="file" name="picture"
                                        class="form-control"
                                        accept="image/*"
                                        style="font-size: 14px;">
                                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
                                </div>
                            </div>

                            <!-- Signature Section -->
                            <div class="mt-4">
                                <div class="grid_3 grid_4">
                                    <h5><i class="fa fa-signature"></i> <?php echo $this->lang->line('Signature'); ?></h5>
                                    <hr>
                                    <div class="text-center">
                                        <div class="image-container" style="max-height: 150px; overflow: hidden; border-radius: 8px; border: 2px solid #e9ecef;">
                                            <img alt="<?php echo $this->lang->line('Signature'); ?>" id="sign_pic"
                                                class="img-fluid"
                                                style="width: 100%; height: 150px; object-fit: contain; background-color: #f8f9fa;"
                                                src="<?php echo base_url('assets/images/default-signature.png'); ?>">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label for="sign_fileupload" class="form-label">
                                            <i class="fa fa-upload"></i> <?php echo $this->lang->line('Upload Signature'); ?>
                                        </label>
                                        <input id="sign_fileupload" type="file" name="signature"
                                            class="form-control"
                                            accept="image/*"
                                            style="font-size: 14px;">
                                        <small class="form-text text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div>
                            <div class="grid_3 grid_4">
                                <h5><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('Employee Details'); ?></h5>
                                <hr>
                                <h6><?php echo $this->lang->line('Personal Information'); ?></h6>

                                <!-- Personal Details -->
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="name">
                                        <?php echo $this->lang->line('Name'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Name'); ?>" class="form-control margin-bottom required"
                                            name="name" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="phone">
                                        <?php echo $this->lang->line('Phone'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Phone'); ?>" class="form-control margin-bottom"
                                            name="phone">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="address">
                                        <?php echo $this->lang->line('Address'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Address'); ?>" class="form-control margin-bottom"
                                            name="address">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="city">
                                        <?php echo $this->lang->line('City'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('City'); ?>" class="form-control margin-bottom"
                                            name="city">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="region">
                                        <?php echo $this->lang->line('Region'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Region'); ?>" class="form-control margin-bottom"
                                            name="region">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="country">
                                        <?php echo $this->lang->line('Country'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Country'); ?>" class="form-control margin-bottom"
                                            name="country">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="postbox">
                                        <?php echo $this->lang->line('Postbox'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Postbox'); ?>" class="form-control margin-bottom"
                                            name="postbox">
                                    </div>
                                </div>

                                <hr>
                                <h6><?php echo $this->lang->line('Employment Details'); ?></h6>

                                <!-- Employment Information -->
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="commission">
                                        <?php echo $this->lang->line('Commission'); ?> %
                                    </label>
                                    <div class="col-sm-2">
                                        <input type="number" placeholder="<?php echo $this->lang->line('Commission'); ?> %" value="0"
                                            class="form-control margin-bottom" name="commission" min="0" max="100" step="0.01">
                                    </div>
                                    <div class="col-sm-8">
                                        <small class="text-muted d-block">
                                            It will be based on each invoice amount - inclusive of all taxes, shipping, discounts
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="department">
                                        <?php echo $this->lang->line('Department'); ?>
                                    </label>
                                    <div class="col-sm-4">
                                        <select name="department" class="form-control margin-bottom">
                                            <option value="0"><?php echo $this->lang->line('Default'); ?> - <?php echo $this->lang->line('No'); ?></option>
                                            <?php
                                            if (!empty($dept)) {
                                                foreach ($dept as $row) {
                                                    echo '<option value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['val1'], ENT_QUOTES, 'UTF-8') . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <label class="col-sm-2 col-form-label" for="salary">
                                        <?php echo $this->lang->line('Salary'); ?>
                                    </label>
                                    <div class="col-sm-4">
                                        <input type="number" placeholder="<?php echo $this->lang->line('Salary'); ?>" class="form-control margin-bottom"
                                            name="salary" value="0" min="0" step="0.01">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <input type="submit" id="submit-employee" class="btn btn-success margin-bottom"
                                            value="<?php echo $this->lang->line('Add'); ?>"
                                            data-loading-text="Adding...">
                                        <button type="reset" class="btn btn-secondary margin-bottom ml-2">
                                            <?php echo $this->lang->line('Cancel'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</article>

<script type="text/javascript">
    document.title = '<?php echo $this->lang->line('Add Employee'); ?>';

    // Helper function to show alert messages
    function showAlert(message, isSuccess) {
        var $notify = $('#notify');
        $notify.find('.message').html(message);

        if (isSuccess) {
            $notify.removeClass('alert-danger').addClass('alert-success');
        } else {
            $notify.removeClass('alert-success').addClass('alert-danger');
        }

        $notify.fadeIn();

        // Hide alert after 3 seconds
        setTimeout(function() {
            $notify.fadeOut();
        }, 3000);
    }

    // Form submission handler
    $(document).ready(function() {
        var $form = $('#data_form');
        var $submitBtn = $('#submit-employee');
        var originalText = $submitBtn.val();

        // Profile Picture Preview
        $('#fileupload').on('change', function(e) {
            var file = e.target.files[0];
            var $input = $(this);

            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('Profile picture size must be less than 2MB.', false);
                    $input.val('');
                    $('#dpic').attr('src', '<?php echo base_url('assets/images/default-avatar.png'); ?>');
                    return;
                }

                // Validate file type
                if (!file.type.match('image.*')) {
                    showAlert('Please select a valid image file for profile picture.', false);
                    $input.val('');
                    $('#dpic').attr('src', '<?php echo base_url('assets/images/default-avatar.png'); ?>');
                    return;
                }

                // Preview image
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#dpic').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
                $input.removeClass('is-invalid');
            }
        });

        // Signature Preview
        $('#sign_fileupload').on('change', function(e) {
            var file = e.target.files[0];
            var $input = $(this);

            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('Signature size must be less than 2MB.', false);
                    $input.val('');
                    $('#sign_pic').attr('src', '<?php echo base_url('assets/images/default-signature.png'); ?>');
                    return;
                }

                // Validate file type
                if (!file.type.match('image.*')) {
                    showAlert('Please select a valid image file for signature.', false);
                    $input.val('');
                    $('#sign_pic').attr('src', '<?php echo base_url('assets/images/default-signature.png'); ?>');
                    return;
                }

                // Preview image
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#sign_pic').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
                $input.removeClass('is-invalid');
            }
        });

        // Form submission
        $form.on('submit', function(e) {
            e.preventDefault();

            // Basic validation
            var isValid = true;
            $('.required').each(function() {
                if ($(this).val().trim() === '') {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                showAlert('Please fill all required fields.', false);
                return;
            }

            // Show loading state
            $submitBtn.prop('disabled', true).val('Creating...');

            // Create FormData to include file uploads
            var formData = new FormData(this);

            $.ajax({
                url: '<?php echo site_url("employee/submit_user"); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    // Ensure response is parsed correctly
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse response:', e);
                        }
                    }

                    if (response && response.status === 'Success') {
                        // Show success notification
                        $('#notify').removeClass('alert-danger').addClass('alert-success');
                        $('#notify .message').html('<strong>' + response.status + '</strong>: ' + (response.message || 'Employee created successfully!'));
                        $('#notify').fadeIn();

                        // Get redirect URL
                        const redirectUrl = response.redirect_url || '<?php echo site_url("employee"); ?>';

                        // Scroll to top of page (safer than scrolling to notification)
                        $('html, body').animate({
                            scrollTop: 0
                        }, 300);

                        // Redirect after 1.5 seconds
                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 1500);

                    } else {
                        // Show error notification
                        var errorMsg = (response && response.message) ? response.message : 'An error occurred. Please try again.';
                        $('#notify').removeClass('alert-success').addClass('alert-danger');
                        $('#notify .message').html('<strong>Error</strong>: ' + errorMsg);
                        $('#notify').fadeIn();

                        // Scroll to top of page
                        $('html, body').animate({
                            scrollTop: 0
                        }, 300);

                        // Re-enable submit button
                        $submitBtn.prop('disabled', false).val(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'An unexpected error occurred. Please try again.';

                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        // Use default error message
                        if (xhr.responseText) {
                            errorMsg = xhr.responseText.substring(0, 100);
                        }
                    }

                    // Show error notification
                    $('#notify').removeClass('alert-success').addClass('alert-danger');
                    $('#notify .message').html('<strong>Error</strong>: ' + errorMsg);
                    $('#notify').fadeIn();

                    // Scroll to top of page
                    $('html, body').animate({
                        scrollTop: 0
                    }, 300);

                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).val(originalText);
                }
            });
        });

        // Auto-hide notifications
        $('.close').on('click', function() {
            $('#notify').fadeOut();
        });

        // Form validation on blur
        $('.required').on('blur', function() {
            var $input = $(this);
            if ($input.val().trim() === '') {
                $input.addClass('is-invalid');
            } else {
                $input.removeClass('is-invalid').addClass('is-valid');
            }
        });

        // Real-time username availability check
        $('input[name="username"]').on('blur', function() {
            var username = $(this).val().trim();
            if (username.length >= 3) {
                checkUsernameAvailability(username);
            }
        });

        // Real-time email availability check
        $('input[name="email"]').on('blur', function() {
            var email = $(this).val().trim();
            if (email && isValidEmail(email)) {
                checkEmailAvailability(email);
            }
        });
    });

    // Helper functions
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function checkUsernameAvailability(username) {
        var $input = $('input[name="username"]');

        $.ajax({
            url: '<?php echo site_url("employee/check_username"); ?>',
            type: 'POST',
            data: {
                username: username,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                $input.next('.invalid-feedback').remove();
                if (response.exists) {
                    $input.addClass('is-invalid').removeClass('is-valid');
                    $input.after('<div class="invalid-feedback">Username already exists. Please choose another.</div>');
                } else {
                    $input.addClass('is-valid').removeClass('is-invalid');
                }
            },
            error: function() {
                // Silently fail - don't block user if check fails
            }
        });
    }

    function checkEmailAvailability(email) {
        var $input = $('input[name="email"]');

        $.ajax({
            url: '<?php echo site_url("employee/check_email"); ?>',
            type: 'POST',
            data: {
                email: email,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                $input.next('.invalid-feedback').remove();
                if (response.exists) {
                    $input.addClass('is-invalid').removeClass('is-valid');
                    $input.after('<div class="invalid-feedback">Email already exists. Please use another email.</div>');
                } else {
                    $input.addClass('is-valid').removeClass('is-invalid');
                }
            },
            error: function() {
                // Silently fail - don't block user if check fails
            }
        });
    }
</script>