<article class="content">
    <div>

        <div class="row">
            <div class="col-md-8">
                <div class="card card-block">
                    <!-- Notification Alert -->
                    <div id="notify" class="alert alert-success" style="display:none;">
                        <a href="#" class="close" data-dismiss="alert">&times;</a>
                        <div class="message"></div>
                    </div>
                    <div id="errors" class="well"></div>
                    <form method="post" id="product_action" class="form-horizontal">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
                        <div class="card-body">

                            <h5><?php echo $this->lang->line('Update Your Password') ?> (<?php echo $user['username'] ?>
                                )</h5>
                            <hr>


                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                    for="address"><?php echo $this->lang->line('New Password') ?></label>

                                <div class="col-sm-10">
                                    <input type="password" placeholder="New Password"
                                        class="form-control margin-bottom  required" name="newpassword"
                                        id="user-pass">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"
                                    for="address"><?php echo $this->lang->line('Re New Password') ?></label>

                                <div class="col-sm-10">
                                    <input type="password" placeholder="Re New Password"
                                        class="form-control margin-bottom  required" name="renewpassword"
                                        id="user-pass2">
                                </div>
                            </div>


                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label"></label>

                                <div class="col-sm-4">
                                    <input type="hidden"
                                        name="eid"
                                        value="<?php echo $user['id'] ?>">
                                    <input type="submit" id="password_update" class="btn btn-success margin-bottom"
                                        value="<?php echo $this->lang->line('Update Password') ?>"
                                        data-loading-text="Updating...">
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</article>
<script src="<?php echo assets_url(); ?>assets/myjs/jquery.password-validation.js" type="text/javascript"></script>
<script>
    document.title = "<?php echo $this->lang->line('Update Your Password') ?> (<?php echo $user['username'] ?>)";
    $(document).ready(function() {
        $("#user-pass").passwordValidation({
            "confirmField": "#user-pass2"
        }, function(element, valid, match, failedCases) {

            $("#errors").html("<div class='alert alert-warning mb-2' role='alert'><strong>Password Rules</strong><br>" + failedCases.join("<br>") + "</div>");

            if (valid) $(element).css("border", "2px solid green");
            if (!valid) {
                $(element).css("border", "2px solid red");
                $('#password_update').attr('disabled', 'disabled');
            }
            if (valid && match) {
                $("#user-pass2").css("border", "2px solid green");
                $('#errors').html('');
                $('#password_update').removeAttr('disabled');
            }
            if (!valid || !match) {
                $("#user-pass2").css("border", "2px solid red");
                $('#password_update').attr('disabled', 'disabled');
            }
        });
    });
</script>
<script type="text/javascript">
      $("#product_action").submit(function(e) {
        e.preventDefault(); // Prevent form submission

        // Remove existing notification if any
        $("#notify").remove();

        // Append temporary success message at the top
        $('.content-body').prepend(`
        <div id="notify" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
    `);

        var actionurl = '<?php echo site_url("employee/updatepassword"); ?>'; // Define the action URL

        // Adding CSRF token to the request
        var csrf_token = $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val();

        $.ajax({
            url: actionurl,
            type: 'POST',
            data: $(this).serialize() + '&' + '<?= $this->security->get_csrf_token_name(); ?>=' + csrf_token,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'Success') {
                    // Show success message
                    $('#notify .message').html(response.message);
                    $('#notify').removeClass('alert-danger').addClass('alert-success').fadeIn();

                    // Hide success message after 3 seconds and remove it from the DOM
                    setTimeout(function() {
                        $('#notify').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Show error message
                    $('#notify .message').html(response.message);
                    $('#notify').removeClass('alert-success').addClass('alert-danger').fadeIn();
                }
            },
            error: function() {
                // Show error message if there's an issue with the AJAX request
                $('#notify .message').html('An error occurred. Please try again.');
                $('#notify').removeClass('alert-success').addClass('alert-danger').fadeIn();
            }
        });
    });
</script>