<div class="sub-result"></div>
<form id="form-upload-user" method="post" autocomplete="off" enctype="multipart/form-data">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    
    <div class="form-group">
        <label class="control-label">Choose File <small class="text-danger">*</small></label>
        <input type="file" class="form-control form-control-sm" id="file" name="file" accept=".csv, .xls, .xlsx" required>
        <small class="text-danger">Upload excel or csv file only.</small>
    </div>
    
    <div class="form-group text-center">
        <div class="user-loader" style="display: none;">
            <i class="fa fa-spinner fa-spin"></i> <small>Please wait ...</small>
        </div>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-sm waves-effect waves-light" id="btnUpload">Upload</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $("body").on("submit", "#form-upload-user", function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        // Add CSRF token dynamically
        formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

        $.ajax({
            type: 'POST',
            url: "<?= base_url('products/import') ?>",
            data: formData,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#btnUpload").prop('disabled', true);
                $(".user-loader").show();
                $(".sub-result").html(''); // Clear previous messages
            },
            success: function(response) {
                $("#btnUpload").prop('disabled', false);
                $(".user-loader").hide();

                if (response.success_message) {
                    $(".sub-result").html('<p style="color: green;">' + response.success_message + '</p>');
                } else if (response.error_message) {
                    $(".sub-result").html('<p style="color: red;">' + response.error_message + '</p>');
                }
            },
            error: function() {
                $("#btnUpload").prop('disabled', false);
                $(".user-loader").hide();
                $(".sub-result").html('<p style="color: red;">An error occurred. Please try again.</p>');
            }
        });
    });
});
</script>
