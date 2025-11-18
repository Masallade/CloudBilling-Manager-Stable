<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5>Change Language</h5>
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
                <form method="post" id="product_action" class="form-horizontal">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
                    <input type="hidden" name="id" value="<?php echo $company['id'] ?>">


                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"
                               for="currency">Language</label>

                        <div class="col-sm-6">
                            <select name="language" class="form-control">

                                <?php

                                echo $langs;
                                ?>

                            </select>
                        </div>
                    </div>


                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"></label>

                        <div class="col-sm-4">
                            <input type="submit" id="billing_update" class="btn btn-success margin-bottom"
                                   value="<?php echo $this->lang->line('Update') ?>" data-loading-text="Updating...">
                        </div>
                    </div>

            </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Ensure baseurl is defined
    if (typeof baseurl === 'undefined') {
        var baseurl = '<?php echo base_url() ?>';
    }
    if (typeof crsf_token === 'undefined') {
        var crsf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    }
    if (typeof crsf_hash === 'undefined') {
        var crsf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    }
    
    document.title = 'Change Language';
    
    $("#billing_update").click(function (e) {
        e.preventDefault();
        
        var actionurl = baseurl + 'settings/language';
        
        $.ajax({
            url: actionurl,
            type: 'POST',
            data: $("#product_action").serialize(),
            dataType: 'json',
            success: function(response) {
                if (!response || !response.status) {
                    console.error('Invalid response:', response);
                    $("#notify .message").html("<strong>Error</strong>: Invalid response from server");
                    $("#notify").removeClass("alert-success").addClass("alert-warning").fadeIn();
                    return;
                }
                
                var message = response.message || 'Operation completed';
                var status = response.status;
                
                if (status === 'Success') {
                    $("#notify .message").html("<strong>" + status + "</strong>: " + message);
                    $("#notify").removeClass("alert-warning").addClass("alert-success").fadeIn();
                    
                    // Safe scroll to notification
                    var notifyEl = $('#notify');
                    if (notifyEl.length && notifyEl.offset()) {
                        $("html, body").animate({ scrollTop: notifyEl.offset().top }, 200);
                    }
                    
                    // Reload page after 1 second to reflect language change
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $("#notify .message").html("<strong>" + status + "</strong>: " + message);
                    $("#notify").removeClass("alert-success").addClass("alert-warning").fadeIn();
                    
                    // Safe scroll to notification
                    var notifyEl = $('#notify');
                    if (notifyEl.length && notifyEl.offset()) {
                        $("html, body").animate({ scrollTop: notifyEl.offset().top }, 1000);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr);
                var errorMsg = 'An error occurred. Please try again.';
                
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // Try to parse response text
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                $("#notify .message").html("<strong>Error</strong>: " + errorMsg);
                $("#notify").removeClass("alert-success").addClass("alert-warning").fadeIn();
                
                // Safe scroll to notification
                var notifyEl = $('#notify');
                if (notifyEl.length && notifyEl.offset()) {
                    $("html, body").animate({ scrollTop: notifyEl.offset().top }, 1000);
                }
            }
        });
    });
</script>

