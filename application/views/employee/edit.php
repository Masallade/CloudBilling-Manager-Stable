<article class="content-body">
    <div class="card card-block">
        <!-- Notification Alert -->
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <form method="post" id="product_action" class="form-horizontal">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
            <div class="card-body">
                <h5><?php echo $this->lang->line('Update Employee'); ?></h5>
                <hr>
                
                <!-- User Information Section -->
                <div class="mb-4">
                    <h5><i class="fa fa-user-circle"></i> <?php echo $this->lang->line('User Information'); ?></h5>
                    <hr>
                    
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label" for="username">
                            <?php echo $this->lang->line('UserName'); ?>
                        </label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control margin-bottom" name="username"
                                value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        <label class="col-sm-2 col-form-label" for="email">
                            <?php echo $this->lang->line('Email'); ?>
                        </label>
                        <div class="col-sm-4">
                            <input type="email" placeholder="<?php echo $this->lang->line('Email'); ?>" 
                                class="form-control margin-bottom" name="email"
                                value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <?php if ($this->aauth->get_user()->roleid >= 0): ?>
                            <label class="col-sm-2 col-form-label" for="roleid">
                                <?php echo $this->lang->line('UserRole'); ?>
                            </label>
                            <div class="col-sm-4">
                                <select name="roleid" class="form-control margin-bottom">
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= htmlspecialchars($role['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= $role['id'] == $user['roleid'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="col-sm-6"></div>
                        <?php endif; ?>
                        <label class="col-sm-2 col-form-label" for="location">
                            <?php echo $this->lang->line('Business Location'); ?>
                        </label>
                        <div class="col-sm-4">
                            <select name="location" class="form-control margin-bottom">
                                <?php
                                $current_loc = isset($user['loc']) ? $user['loc'] : 0;
                                $current_loc_name = '';
                                $loc_found = false;

                                // Get locations
                                $loc = locations();

                                // Find the current location name if it exists in the list
                                foreach ($loc as $row) {
                                    if ($row['id'] == $current_loc) {
                                        $current_loc_name = $row['cname'];
                                        $loc_found = true;
                                        break;
                                    }
                                }

                                // Show "Default" option
                                echo '<option value="0"' . ($current_loc == 0 ? ' selected' : '') . '>' . $this->lang->line('Default') . '</option>';

                                // List all locations with proper selection
                                foreach ($loc as $row) {
                                    $selected = ($row['id'] == $current_loc && $current_loc != 0) ? ' selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($row['cname'], ENT_QUOTES, 'UTF-8') . '</option>';
                                }

                                // If current location exists but we want to preserve its value with "Do not change"
                                // Only show this if the location was not found in the list (edge case)
                                if ($current_loc != 0 && !$loc_found) {
                                    echo '<option value="' . htmlspecialchars($current_loc, ENT_QUOTES, 'UTF-8') . '" selected>' . $this->lang->line('Do not change') . ' (ID: ' . htmlspecialchars($current_loc, ENT_QUOTES, 'UTF-8') . ')</option>';
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
                                            src="<?php echo base_url('userfiles/employee/') . $user['picture'] ?>"
                                            onerror="this.src='<?php echo base_url('assets/images/default-avatar.png') ?>'">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="fileupload" class="form-label">
                                        <i class="fa fa-upload"></i> <?php echo $this->lang->line('Upload Picture'); ?>
                                    </label>
                                    <input id="fileupload" type="file" name="files[]"
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
                                                src="<?php echo base_url('userfiles/employee_sign/') . $user['sign'] ?>"
                                                onerror="this.src='<?php echo base_url('assets/images/default-signature.png') ?>'">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label for="sign_fileupload" class="form-label">
                                            <i class="fa fa-upload"></i> <?php echo $this->lang->line('Change Your Signature'); ?>
                                        </label>
                                        <input id="sign_fileupload" type="file" name="files[]"
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
                                <h5><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('Employee Details'); ?> (<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>)</h5>
                                <hr>
                                <h6><?php echo $this->lang->line('Personal Information'); ?></h6>

                                <!-- Personal Details -->
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="name">
                                        <?php echo $this->lang->line('Name'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Name'); ?>" class="form-control margin-bottom required"
                                            name="name" value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="phone">
                                        <?php echo $this->lang->line('Phone'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Phone'); ?>" class="form-control margin-bottom"
                                            name="phone" value="<?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="phone">
                                        <?php echo $this->lang->line('Phone'); ?> (Alt)
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Phone'); ?> (Alt)" class="form-control margin-bottom"
                                            name="phonealt" value="<?php echo htmlspecialchars($user['phonealt'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="address">
                                        <?php echo $this->lang->line('Address'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Address'); ?>" class="form-control margin-bottom"
                                            name="address" value="<?php echo htmlspecialchars($user['address'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="city">
                                        <?php echo $this->lang->line('City'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('City'); ?>" class="form-control margin-bottom"
                                            name="city" value="<?php echo htmlspecialchars($user['city'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="region">
                                        <?php echo $this->lang->line('Region'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Region'); ?>" class="form-control margin-bottom"
                                            name="region" value="<?php echo htmlspecialchars($user['region'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="country">
                                        <?php echo $this->lang->line('Country'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Country'); ?>" class="form-control margin-bottom"
                                            name="country" value="<?php echo htmlspecialchars($user['country'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" for="postbox">
                                        <?php echo $this->lang->line('Postbox'); ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Postbox'); ?>" class="form-control margin-bottom"
                                            name="postbox" value="<?php echo htmlspecialchars($user['postbox'], ENT_QUOTES, 'UTF-8'); ?>">
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
                                        <input type="number" placeholder="<?php echo $this->lang->line('Commission'); ?> %"
                                            class="form-control margin-bottom" name="commission"
                                            value="<?php echo htmlspecialchars($user['c_rate'], ENT_QUOTES, 'UTF-8'); ?>" min="0" max="100" step="0.01">
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
                                            <?php
                                            $current_dept = isset($user['dept']) ? $user['dept'] : 0;
                                            $current_dept_name = '';
                                            $dept_found = false;

                                            // Find the current department name if it exists in the list
                                            foreach ($dept as $row) {
                                                if ($row['id'] == $current_dept) {
                                                    $current_dept_name = $row['val1'];
                                                    $dept_found = true;
                                                    break;
                                                }
                                            }

                                            // Show "Default" option
                                            echo '<option value="0"' . ($current_dept == 0 ? ' selected' : '') . '>' . $this->lang->line('Default') . ' - ' . $this->lang->line('No') . '</option>';

                                            // List all departments with proper selection
                                            foreach ($dept as $row) {
                                                $selected = ($row['id'] == $current_dept && $current_dept != 0) ? ' selected' : '';
                                                echo '<option value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($row['val1'], ENT_QUOTES, 'UTF-8') . '</option>';
                                            }

                                            // If current department exists but we want to preserve its value with "Do not change"
                                            // Only show this if the department was not found in the list (edge case)
                                            if ($current_dept != 0 && !$dept_found) {
                                                echo '<option value="' . htmlspecialchars($current_dept, ENT_QUOTES, 'UTF-8') . '" selected>' . $this->lang->line('Do not change') . ' (ID: ' . htmlspecialchars($current_dept, ENT_QUOTES, 'UTF-8') . ')</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <label class="col-sm-2 col-form-label" for="salary">
                                        <?php echo $this->lang->line('Salary'); ?>
                                    </label>
                                    <div class="col-sm-4">
                                        <input type="text" placeholder="<?php echo $this->lang->line('Salary'); ?>" onkeypress="return isNumber(event)"
                                            class="form-control margin-bottom" name="salary"
                                            value="<?php echo amountFormat_general($user['salary']); ?>">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <input type="hidden" name="eid" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <input type="submit" id="profile_update" class="btn btn-success margin-bottom"
                                            value="<?php echo $this->lang->line('Update'); ?>"
                                            data-loading-text="Updating...">
                                        <a href="<?php echo site_url('employee'); ?>" class="btn btn-secondary margin-bottom ml-2">
                                            <?php echo $this->lang->line('Cancel'); ?>
                                        </a>
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
    document.title = '<?php echo $this->lang->line('Update Employee') ?>';
    
    $(document).ready(function() {
        var $form = $('#product_action');
        var $submitBtn = $('#profile_update');
        var originalText = $submitBtn.val();
        
        // Form submission handler
        $form.on('submit', function(e) {
            e.preventDefault();

            // Show loading state
            $submitBtn.prop('disabled', true).val('Updating...');

            // Ensure notification is ready
            var $notify = $('#notify');
            if ($notify.length === 0) {
                // Create notification if it doesn't exist
                $('.card.card-block').prepend('<div id="notify" class="alert alert-success" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
                $notify = $('#notify');
            }

            // Adding CSRF token to the request (already in form, but ensure it's included)
            var formData = $(this).serialize();

            $.ajax({
                url: '<?php echo site_url("employee/update"); ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Response received:', response);
                    
                    // Ensure response is parsed correctly
                    var parsedResponse = response;
                    if (typeof response === 'string') {
                        try {
                            parsedResponse = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse response:', e, response);
                            parsedResponse = { status: 'Error', message: 'Invalid response from server.' };
                        }
                    }
                    
                    console.log('Parsed response:', parsedResponse);
                    
                    // Ensure notification element exists and is ready
                    if ($notify.length === 0) {
                        $('.card.card-block').prepend('<div id="notify" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
                        $notify = $('#notify');
                    }
                    
                    if (parsedResponse && parsedResponse.status === 'Success') {
                        // Show success message
                        $notify.removeClass('alert-danger').addClass('alert-success');
                        $notify.find('.message').html('<strong>' + parsedResponse.status + '</strong>: ' + (parsedResponse.message || 'Employee updated successfully!'));
                        $notify.css('display', 'block').hide().fadeIn(300);

                        // Scroll to top of page
                        $('html, body').animate({
                            scrollTop: 0
                        }, 300);

                        // Auto-hide after 5 seconds
                        setTimeout(function() {
                            $notify.fadeOut(300);
                        }, 5000);
                    } else {
                        // Show error message
                        var errorMsg = (parsedResponse && parsedResponse.message) ? parsedResponse.message : 'An error occurred. Please try again.';
                        $notify.removeClass('alert-success').addClass('alert-danger');
                        $notify.find('.message').html('<strong>Error</strong>: ' + errorMsg);
                        $notify.css('display', 'block').hide().fadeIn(300);

                        // Scroll to top of page
                        $('html, body').animate({
                            scrollTop: 0
                        }, 300);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'An unexpected error occurred. Please try again.';
                    
                    try {
                        if (xhr.responseText) {
                            var response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMsg = response.message;
                            } else {
                                errorMsg = xhr.responseText.substring(0, 100);
                            }
                        }
                    } catch (e) {
                        // Use default error message
                        console.error('Error parsing response:', e);
                        if (xhr.responseText) {
                            errorMsg = 'Server error: ' + xhr.status + ' ' + xhr.statusText;
                        }
                    }
                    
                    // Ensure notification element exists
                    if ($notify.length === 0) {
                        $('.card.card-block').prepend('<div id="notify" class="alert alert-danger" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
                        $notify = $('#notify');
                    }
                    
                    $notify.removeClass('alert-success').addClass('alert-danger');
                    $notify.find('.message').html('<strong>Error</strong>: ' + errorMsg);
                    $notify.css('display', 'block').hide().fadeIn(300);

                    // Scroll to top of page
                    $('html, body').animate({
                        scrollTop: 0
                    }, 300);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).val(originalText);
                }
            });
        });
        
        // Auto-hide notifications on close
        $('.close').on('click', function() {
            $('#notify').fadeOut();
        });
    });
</script>



<script src="<?php echo base_url('assets/myjs/jquery.ui.widget.js') ?>"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo base_url('assets/myjs/jquery.fileupload.js') ?>"></script>
<script>
    /*jslint unparam: true */
    /*global window, $ */
    $(function() {
        'use strict';

        // Reusable function to show alert messages
        function showAlert(message, isSuccess) {
            $('#notify .message').html(message);
            if (isSuccess) {
                $('#notify').removeClass('alert-danger').addClass('alert-success');
            } else {
                $('#notify').removeClass('alert-success').addClass('alert-danger');
            }
            $('#notify').fadeIn();

            // Hide alert after 3 seconds
            setTimeout(function() {
                $('#notify').fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }

        // Parse response data from jQuery File Upload
        function parseResponse(data) {
            if (data.result) {
                try {
                    // If result is a string, parse it as JSON
                    if (typeof data.result === 'string') {
                        return JSON.parse(data.result);
                    }
                    // If result is already an object
                    return data.result;
                } catch (e) {
                    return null;
                }
            }
            return null;
        }

        // Profile Picture Upload
        var profilePicUrl = '<?php echo site_url("employee/displaypic"); ?>?id=<?php echo $user['id'] ?>';
        $('#fileupload').fileupload({
                url: profilePicUrl,
                dataType: 'json',
                formData: {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                },
                add: function(e, data) {
                    // Validate file before upload
                    var file = data.files[0];
                    if (file && file.size > 2 * 1024 * 1024) { // 2MB limit
                        showAlert('File size must be less than 2MB.', false);
                        return false;
                    }
                    if (file && !file.type.match('image.*')) {
                        showAlert('Please select a valid image file.', false);
                        return false;
                    }
                    data.submit();
                },
                done: function(e, data) {
                    var response = data.result;
                    if (response && response.status === 'Success') {
                        // Update image with smooth transition
                        var newSrc = '<?php echo base_url('userfiles/employee/'); ?>' + response.filename + '?' + new Date().getTime();
                        $("#dpic").fadeOut(200, function() {
                            $(this).attr('src', newSrc).fadeIn(200);
                        });
                        showAlert(response.message, true);
                    } else {
                        var errorMsg = (response && response.message) ? response.message : 'Failed to upload profile picture.';
                        showAlert(errorMsg, false);
                    }
                },
                fail: function(e, data) {
                    console.error('Upload failed:', data);
                    showAlert('Failed to upload profile picture. Please try again.', false);
                },
                progressall: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css('width', progress + '%');
                }
            }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

        // Signature Upload
        var signUrl = '<?php echo site_url("employee/user_sign"); ?>?id=<?php echo $user['id'] ?>';
        $('#sign_fileupload').fileupload({
                url: signUrl,
                dataType: 'json',
                formData: {
                    '<?= $this->security->get_csrf_token_name() ?>': crsf_hash
                },
                add: function(e, data) {
                    // Validate file before upload
                    var file = data.files[0];
                    if (file && file.size > 2 * 1024 * 1024) { // 2MB limit
                        showAlert('File size must be less than 2MB.', false);
                        return false;
                    }
                    if (file && !file.type.match('image.*')) {
                        showAlert('Please select a valid image file.', false);
                        return false;
                    }
                    data.submit();
                },
                done: function(e, data) {
                    var response = data.result;
                    if (response && response.status === 'Success') {
                        // Update image with smooth transition
                        var newSrc = '<?php echo base_url('userfiles/employee_sign/'); ?>' + response.filename + '?' + new Date().getTime();
                        $("#sign_pic").fadeOut(200, function() {
                            $(this).attr('src', newSrc).fadeIn(200);
                        });
                        showAlert(response.message, true);
                    } else {
                        var errorMsg = (response && response.message) ? response.message : 'Failed to upload signature.';
                        showAlert(errorMsg, false);
                    }
                },
                fail: function(e, data) {
                    console.error('Upload failed:', data);
                    showAlert('Failed to upload signature. Please try again.', false);
                },
                progressall: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css('width', progress + '%');
                }
            }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });
</script>
</script>