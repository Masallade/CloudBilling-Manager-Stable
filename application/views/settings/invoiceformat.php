<style>
    .content-body .row .format-card {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        height: 520px;
        border: 2px solid #e7e7e7;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }

    .content-body .row .format-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-5px);
        border-color: #007bff;
    }

    .content-body .row .format-card img {
        transition: all 1.2s ease;
        width: 100%;
        height: 420px;
        object-fit: cover;
        object-position: top;
        display: block;
    }

    .content-body .row .format-card:hover img {
        transform: translateY(calc(-100% + 420px));
    }

    .invoice-select {
        width: 100%;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px 20px;
        text-align: center;
        z-index: 9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
    }

    .invoice-select h4 {
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin: 0;
        color: #495057;
    }

    .format-card.selected {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25) !important;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        transform: scale(1.02);
    }
    
    .format-card.selected .invoice-select {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        color: white;
    }
    
    .format-card.selected .invoice-select h4 {
        color: white;
    }
    
    .format-card.selected .invoice-select i {
        color: white;
    }

    .upload-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 5px;
        margin-bottom: 5px;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .upload-section:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }

    .upload-area {
        text-align: center;
        padding: 5px;
    }

    .upload-icon {
        font-size: 18px;
        color: #6c757d;
        margin-bottom: 5px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .upload-icon i {
        font-size: 18px;
        color: #6c757d;
    }
    
    .icon-fallback {
        font-size: 18px;
        color: #6c757d;
        display: none;
    }
    
    /* Simple fallback - show emoji if FontAwesome fails */
    .upload-icon i.fa-upload:empty {
        display: none;
    }
    
    .upload-icon i.fa-upload:empty + .icon-fallback {
        display: inline-block;
    }

    .file-input-wrapper {
        position: relative;
        display: inline-block;
        margin: 10px 0;
    }

    .file-input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-input-label {
        display: inline-block;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-input-label:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }

    .instructions-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 5px;
        margin-bottom: 5px;
        border-radius: 0 8px 8px 0;
    }

    .instructions-box h5 {
        color: #1976d2;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .instructions-box ul {
        margin: 0;
        padding-left: 20px;
    }

    .instructions-box li {
        margin-bottom: 4px;
        color: #424242;
    }

    .preview-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .format-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 1;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .format-card:hover .format-actions {
        opacity: 1;
        transform: scale(1.05);
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    /* Enhanced delete button styling */
    .format-actions .btn-danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        transition: all 0.3s ease;
    }
    
    .format-actions .btn-danger:hover {
        background: linear-gradient(135deg, #c82333, #bd2130);
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
    }
    
    .format-actions .btn-danger i {
        font-size: 14px;
        color: white;
    }
    
    /* Format count badge styling */
    #formatCount {
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 20px;
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    #formatCount:hover {
        background: linear-gradient(135deg, #138496, #117a8b);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    /* Empty state styling */
    .empty-state {
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }
    
    .empty-state:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }
    
    .empty-state i {
        opacity: 0.6;
        transition: all 0.3s ease;
    }
    
    .empty-state:hover i {
        opacity: 1;
        transform: scale(1.1);
    }
    
    /* Responsive grid layout for invoice formats */
    .format-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: flex-start;
    }
    
    .format-grid .format-card {
        flex: 0 0 calc(25% - 15px);
        min-width: 280px;
        max-width: 350px;
    }
    
    @media (max-width: 1200px) {
        .format-grid .format-card {
            flex: 0 0 calc(33.333% - 14px);
        }
    }
    
    @media (max-width: 768px) {
        .format-grid .format-card {
            flex: 0 0 calc(50% - 10px);
        }
    }
    
    @media (max-width: 576px) {
        .format-grid .format-card {
            flex: 0 0 100%;
        }
    }
    
    /* Ensure consistent card heights */
    .format-card {
        display: flex;
        flex-direction: column;
    }
    
    .format-card img {
        flex-grow: 1;
        object-fit: cover;
    }
</style>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Invoice Format </h4>
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
                <!-- Upload Button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-file-text-o"></i> Select Invoice Format 
                        <span class="badge badge-info ml-2" id="formatCount">
                            <?php
                            // Count invoice formats
                            $format_dir = FCPATH . 'assets/format/';
                            $format_count = 0;
                            if (is_dir($format_dir)) {
                                $files = scandir($format_dir);
                                foreach ($files as $file) {
                                    if (preg_match('/^invoice_\d+\.(jpg|jpeg|png|gif)$/i', $file)) {
                                        $format_count++;
                                    }
                                }
                            }
                            echo $format_count;
                            ?>
                        </span>
                    </h5>
                    <div>
                        <?php if ($this->aauth->permission_new(null, 'settingsInvoiceFormatUpload')) { ?>  
                            <button type="button" class="btn btn-success btn-lg mr-2" data-toggle="modal" data-target="#uploadModal">
                                <i class="fa fa-plus"></i> Upload New Format
                            </button>
                        <?php } ?>
                        <button class="btn btn-primary btn-lg" type="submit" form="formatForm">
                            <i class="fa fa-save"></i> Save Selected Format
                        </button>
                    </div>
                </div>

                <!-- Format Selection -->
                <?= form_open('settings/save_invoice_format', array('id' => 'formatForm')); ?>
                    
                    <div class="row">
                        <?php
                        // Get all invoice format images from the directory
                        $format_dir = FCPATH . 'assets/format/';
                        $format_images = array();
                        
                        if (is_dir($format_dir)) {
                            $files = scandir($format_dir);
                            foreach ($files as $file) {
                                if (preg_match('/^invoice_\d+\.(jpg|jpeg|png|gif)$/i', $file)) {
                                    $format_images[] = $file;
                                }
                            }
                            sort($format_images);
                        }
                        
                        // Show only actual images that exist
                        if (!empty($format_images)) {
                            foreach ($format_images as $index => $image_file) {
                                $format_number = preg_replace('/[^0-9]/', '', $image_file);
                                $image_path = base_url("assets/format/" . $image_file);
                                $is_selected = ($company['invoice_format'] == $format_number);
                            ?>
                                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-4">
                                    <div class="card format-card <?= $is_selected ? 'selected' : '' ?>" data-format="<?= $format_number ?>">
                                        <?php if($this->aauth->permission_new(null, 'settingsInvoiceFormatDelete')) { ?>
                                        <div class="format-actions">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteFormat('<?= $image_file ?>')" title="Delete Invoice Format <?= $format_number ?>" data-toggle="tooltip" data-placement="top">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        <?php } ?>
                                        <div class="invoice-select">
                                            <label class="w-100 text-left" for="format-<?= $format_number ?>">
                                                <h4 class="mb-0">
                                                    <i class="fa fa-file-text-o"></i> Invoice Format <?= $format_number ?>
                                                    <?php if ($is_selected) { ?>
                                                        <span class="badge badge-success ml-2">
                                                            <i class="fa fa-check"></i> Selected
                                                        </span>
                                                    <?php } ?>
                                                </h4>
                                            </label>
                                            <input type="radio" name="format" id="format-<?= $format_number ?>" value="<?= $format_number ?>" <?= $is_selected ? 'checked' : '' ?> />
                                        </div>
                                        <img src="<?= $image_path ?>" class="w-100" alt="Invoice Format <?= $format_number ?>" />
                                    </div>
                                </div>
                            <?php }
                        } else {
                            // Show message when no formats exist
                            ?>
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fa fa-file-image-o" style="font-size: 64px; color: #6c757d; margin-bottom: 20px;"></i>
                                        <h4 class="text-muted mb-3">No Invoice Formats Found</h4>
                                        <p class="text-muted mb-4">Upload your first invoice format to get started!</p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="fa fa-upload"></i> Upload New Invoice Format
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Instructions Box -->
                    <div class="instructions-box">
                        <h5><i class="fa fa-info-circle"></i> Invoice Format Instructions</h5>
                        <ul>
                            <li><strong>Image Naming Convention:</strong> Upload images with names like "invoice_1.jpg", "invoice_2.jpg", etc.</li>
                            <li><strong>Recommended Size:</strong> 800x600 pixels or similar aspect ratio for best display</li>
                            <li><strong>File Format:</strong> JPG, PNG, or GIF formats are supported</li>
                            <li><strong>File Size:</strong> Maximum 2MB per image</li>
                            <li><strong>Storage Location:</strong> Images are automatically saved to assets/format/ directory</li>
                        </ul>
                    </div>

                    <div class="upload-section">
                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fa fa-upload"></i>
                                <span class="icon-fallback">📁</span>
                            </div>
                            <h5>Upload Custom Invoice Format</h5>
                            <p class="text-muted">Choose an image file to upload as a new invoice format</p>
                            
                            <?= form_open_multipart('settings/upload_invoice_format', array('id' => 'uploadForm')); ?>
                                <div class="file-input-wrapper">
                                    <input type="file" name="invoice_image" id="invoice_image" class="file-input" accept="image/*" required>
                                    <label for="invoice_image" class="file-input-label">
                                        <i class="fa fa-upload"></i> Choose Image File
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="format_name" id="format_name" class="form-control" placeholder="Enter format name (e.g., invoice_10)" required style="max-width: 300px; margin: 0 auto;">
                                </div>
                                
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <h6>Preview:</h6>
                                    <img id="previewImg" class="preview-image" alt="Preview">
                                </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" form="uploadForm" class="btn btn-success">
                        <i class="fa fa-save"></i> Upload Format
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        document.title = 'Invoice Format';
        
        // Initialize tooltips
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
        
        // Image preview functionality
        const invoiceImageInput = document.getElementById('invoice_image');
        if (invoiceImageInput) {
            invoiceImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewImg = document.getElementById('previewImg');
                        const imagePreview = document.getElementById('imagePreview');
                        if (previewImg && imagePreview) {
                            previewImg.src = e.target.result;
                            imagePreview.style.display = 'block';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Format selection visual feedback
        document.querySelectorAll('.format-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on delete button
                if (e.target.closest('.format-actions')) {
                    return;
                }
                
                // Remove selected class and badge from all cards
                document.querySelectorAll('.format-card').forEach(c => {
                    c.classList.remove('selected');
                    const badge = c.querySelector('.badge-success');
                    if (badge) {
                        badge.remove();
                    }
                });
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Add selected badge
                const h4 = this.querySelector('h4');
                if (h4 && !h4.querySelector('.badge-success')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge badge-success ml-2';
                    badge.innerHTML = '<i class="fa fa-check"></i> Selected';
                    h4.appendChild(badge);
                }
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
        
        // Upload form submission with AJAX
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fileInput = document.getElementById('invoice_image');
            const formatName = document.getElementById('format_name').value;
            
            // Validate file
            if (!fileInput.files[0]) {
                Swal.fire('Error', 'Please select an image file', 'error');
                return;
            }
            
            // Validate format name
            if (!formatName || !formatName.match(/^invoice_\d+$/)) {
                Swal.fire('Error', 'Format name must be like "invoice_10", "invoice_11", etc.', 'error');
                return;
            }
            
            // Show loading
            Swal.fire({
                title: 'Uploading...',
                text: 'Please wait while we upload your invoice format',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // AJAX upload
            fetch('<?= base_url("settings/upload_invoice_format") ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Invalid JSON response:', text);
                        throw new Error('Invalid response from server');
                    }
                });
            })
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success', 'Invoice format uploaded successfully!', 'success')
                        .then(() => {
                            $('#uploadModal').modal('hide');
                            onUploadSuccess();
                        });
                } else {
                    Swal.fire('Error', data.message || 'Upload failed', 'error');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Upload error:', error);
                Swal.fire('Error', 'Upload failed: ' + error.message, 'error');
            });
            });
        }
        
        // Format form submission with AJAX
        const formatForm = document.getElementById('formatForm');
        if (formatForm) {
            formatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait while we save your selection',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // CSRF token disabled for debugging
            // formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
            
            fetch('<?= base_url("settings/save_invoice_format") ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    console.error('Response not ok:', response.status, response.statusText);
                    throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
                }
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Invalid JSON response:', text);
                        throw new Error('Invalid response from server: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success', 'Invoice format saved successfully!', 'success');
                } else {
                    Swal.fire('Error', data.message || 'Save failed', 'error');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Save error:', error);
                Swal.fire('Error', 'Save failed: ' + error.message, 'error');
            });
            });
        }
        
        // Delete format function
        function deleteFormat(filename) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete the invoice format: ' + filename,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the invoice format',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Use GET method to avoid CSRF issues
                    const deleteUrl = '<?= base_url("settings/remove_format") ?>?filename=' + encodeURIComponent(filename);
                    console.log('Delete URL:', deleteUrl);
                    console.log('Filename:', filename);
                    
                    fetch(deleteUrl, {
                        method: 'GET'
                    })
                    .then(response => {
                        console.log('Delete response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
                        }
                        return response.text().then(text => {
                            console.log('Delete response text:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Invalid JSON response:', text);
                                throw new Error('Invalid response from server: ' + text.substring(0, 100));
                            }
                        });
                    })
                    .then(data => {
                        Swal.close();
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || 'Invoice format has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                // Reload the page to show updated format list
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Delete operation failed. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Delete error:', error);
                        Swal.fire('Error', 'Delete failed: ' + error.message, 'error');
                    });
                }
            });
        }
        
        // Auto-generate format name based on file selection (only if format_name element exists)
        if (invoiceImageInput) {
            invoiceImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const formatNameInput = document.getElementById('format_name');
                if (file && formatNameInput && !formatNameInput.value) {
                    // Count existing formats and suggest next number
                    const existingFormats = document.querySelectorAll('.format-card').length;
                    const nextNumber = existingFormats + 1;
                    formatNameInput.value = 'invoice_' + nextNumber;
                }
            });
        }
        
        // Function to update format count
        function updateFormatCount() {
            const formatCards = document.querySelectorAll('.format-card');
            const countElement = document.getElementById('formatCount');
            if (countElement) {
                countElement.textContent = formatCards.length;
            }
        }
        
        // Update count after successful upload
        function onUploadSuccess() {
            setTimeout(() => {
                location.reload(); // Reload to show new format
            }, 1500);
        }
        
        // Update count after successful deletion
        function onDeleteSuccess() {
            setTimeout(() => {
                location.reload(); // Reload to remove deleted format
            }, 1500);
        }
        
        // Add refresh button functionality
        function refreshFormats() {
            Swal.fire({
                title: 'Refreshing...',
                text: 'Please wait while we refresh the invoice formats',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                Swal.close();
                location.reload();
            }, 1000);
        }
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+R or F5 to refresh
            if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
                e.preventDefault();
                refreshFormats();
            }
        });
    </script>