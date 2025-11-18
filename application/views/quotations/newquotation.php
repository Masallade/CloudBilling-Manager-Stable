<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="<?= base_url('assets/css/quotations-shared.css') ?>" rel="stylesheet" />
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">New Quotation</h4>
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
            <div id="notify" class="alert" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <form id="quotation_form" method="post" action="<?php echo site_url('quotations/addquotation') ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    
                    <!-- Main Layout: Bill To (Left) and Quotation Details (Right) -->
                    <div class="row">
                        <!-- Bill To Section (Left) -->
                        <div class="col-md-4">
                            <div class="bill-to-section">
                                <h5>Bill To</h5>
                                <div class="bill-to-container">
                                    <div class="form-group">
                                        <input type="text" class="form-control bill-to-field" name="bill_to_name" id="bill_to_name" placeholder="Customer Name" readonly>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control bill-to-field" name="bill_to_address" id="bill_to_address" rows="5" placeholder="Customer Address" readonly></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="terms">Terms & Conditions</label>
                                    <select class="form-control" name="term" id="term">
                                        <option value="">Select Terms</option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term['id'] ?>"><?= $term['title'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                            </div>
                        </div>

                        <!-- Quotation Details Section (Right) -->
                        <div class="col-md-8">
                            <div class="quotation-details-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer">Search Client *</label>
                                            <div class="customer-search-container">
                                                <input type="text" class="form-control" name="customer_search" id="customer_search" placeholder="Type to search customer or click to see all..." required>
                                                <input type="hidden" name="customer" id="customer" value="">
                                                <div id="customer_suggestions" class="customer-suggestions" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quotation_number">Quotation Number</label>
                                            <input type="text" class="form-control" name="quotation_number" id="quotation_number" value="<?= $lastquotation + 1 ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoicedate">Quotation Date *</label>
                                <input type="date" class="form-control" name="invoicedate" id="invoicedate" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoiceduedate">Valid Until *</label>
                                <input type="date" class="form-control" name="invoiceduedate" id="invoiceduedate" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_name">Driver Name</label>
                                            <input type="text" class="form-control" name="driver_name" id="driver_name" placeholder="Enter Driver Name">
                            </div>
                        </div>
                    </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Products</h5>
                            <div class="product-table-container">
                                <div class="product-table-header">
                                    <div class="product-header-cell">Product Code</div>
                                    <div class="product-header-cell">Product Description</div>
                                    <div class="product-header-cell">Quantity</div>
                                    <div class="product-header-cell">Price</div>
                                    <div class="product-header-cell">Tax</div>
                                    <div class="product-header-cell">Net Tax</div>
                                    <div class="product-header-cell">Discount</div>
                                    <div class="product-header-cell">Net Amount</div>
                                    <div class="product-header-cell">Action</div>
                                </div>
                                <div class="product-table-body" id="product_tbody">
                                    <div class="product-row">
                                        <div class="product-cell">
                                            <input type="hidden" name="item_id[]" value="">
                                            <div class="product-search-container">
                                                <input type="text" class="form-control product-search" name="product_search[]" placeholder="Search product or click to see all..." autocomplete="off">
                                                <input type="hidden" name="product_id[]" class="product-id">
                                                <div class="product-suggestions" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="product-cell">
                                            <input type="text" class="form-control" name="product_des[]" placeholder="Enter Product description (Optional)">
                                        </div>
                                        <div class="product-cell">
                                            <input type="number" step="0.01" class="form-control qty" name="product_qty[]" value="1">
                                        </div>
                                        <div class="product-cell">
                                            <input type="number" step="0.01" class="form-control price" name="product_price[]" placeholder="0.00">
                                        </div>
                                        <div class="product-cell">
                                            <input type="number" step="0.01" class="form-control tax" name="product_tax[]" value="0" readonly>
                                        </div>
                                        <div class="product-cell">
                                            <input type="number" step="0.01" class="form-control net-tax" name="product_net_tax[]" readonly>
                                        </div>
                                        <div class="product-cell">
                                            <input type="number" step="0.01" class="form-control discount" name="product_discount[]" value="0">
                                        </div>
                                        <div class="product-cell">
                                            <input type="number" step="0.01" class="form-control subtotal" name="product_subtotal[]" readonly>
                                        </div>
                                        <div class="product-cell">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-sm add-product-row" title="Add Product">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove Product">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <button type="button" class="btn btn-success">Add Product</button> -->
                        </div>
                    </div>

                    <!-- Totals Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipping">Shipping</label>
                                <input type="number" step="0.01" class="form-control" name="shipping" id="shipping" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ship_tax">Shipping Tax</label>
                                <input type="number" step="0.01" class="form-control" name="ship_tax" id="ship_tax" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subtotal">Subtotal</label>
                                <input type="number" step="0.01" class="form-control" name="subtotal" id="subtotal" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax">Total Tax</label>
                                <input type="number" step="0.01" class="form-control" name="tax" id="tax" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total">Total Amount</label>
                                <input type="number" step="0.01" class="form-control" name="total" id="total" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Save Quotation</button>
                            <a href="<?= site_url('quotations') ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    document.title = 'New Quotation';
        let customerSearchTimeout, productSearchTimeout, allCustomersLoaded = false, allProductsLoaded = false;
        
        // Performance optimization: Debounce search with longer delay
        const SEARCH_DELAY = 300; // Increased from 100ms to 300ms
        const CACHE_DURATION = 300000; // 5 minutes cache
        let searchCache = {};
        
        // Get decimal precision from server (you can adjust this based on your location settings)
        const DECIMAL_PRECISION = 3; // This should match your location setting
    
    // Common functions
    function showAlert(message, type) {
        $('#notify .message').text(message);
        $('#notify').removeClass('alert-danger alert-success').addClass('alert-' + type).fadeIn();
    }
    
    function createSuggestionItem(type, data) {
        const firstLetter = data.name.charAt(0).toUpperCase();
        const displayName = data.company || data.name;
        const price = data.price || data.product_price || '';
        const code = data.code || '';
        
        // For products, show CODE - NAME format
        if (type === 'product') {
            const productDisplayName = code ? `${code} - ${data.name}` : data.name;
            return `<div class="${type}-suggestion-item" data-id="${data.id}" data-name="${data.name}" data-company="${data.company || ''}" data-price="${price}" data-tax="${data.tax || data.taxrate || 0}" data-description="${data.description || data.product_des || ''}" data-code-type="${data.code_type || 'T0'}" data-code="${code}">
                <div class="${type}-avatar">${firstLetter}</div>
                <div class="${type}-info">
                    <div class="${type}-name">${productDisplayName}</div>
                    ${price ? `<div class="${type}-price">Price: ${price}</div>` : ''}
                </div>
            </div>`;
        }
        
        // For customers, keep original format
        return `<div class="${type}-suggestion-item" data-id="${data.id}" data-name="${data.name}" data-company="${data.company || ''}" data-price="${price}" data-tax="${data.tax || data.taxrate || 0}" data-description="${data.description || data.product_des || ''}" data-code-type="${data.code_type || 'T0'}">
            <div class="${type}-avatar">${firstLetter}</div>
            <div class="${type}-info">
                <div class="${type}-name">${displayName}</div>
                ${data.email ? `<div class="${type}-email">${data.email}</div>` : ''}
                ${code ? `<div class="${type}-code">Code: ${code}</div>` : ''}
                ${price ? `<div class="${type}-price">Price: ${price}</div>` : ''}
            </div>
        </div>`;
    }
    
    // Simple format amount function using JavaScript
    function formatAmount(amount, callback) {
        if (isNaN(amount) || amount === null || amount === undefined) {
            callback('0.' + '0'.repeat(DECIMAL_PRECISION));
            return;
        }
        
        // Format with dynamic decimal precision
        const formatted = parseFloat(amount).toFixed(DECIMAL_PRECISION);
        callback(formatted);
    }
    
    // Customer search
    $('#customer_search').on('click', function() {
        if (!allCustomersLoaded) loadAllCustomers();
    });
    
    function loadAllCustomers() {
        if (allCustomersLoaded) { $('#customer_suggestions').show(); return; }
        
        // Check cache first
        const cacheKey = 'all_customers_' + <?= $this->aauth->get_user()->loc ?>;
        if (searchCache[cacheKey] && (Date.now() - searchCache[cacheKey].timestamp) < CACHE_DURATION) {
            let html = '';
            searchCache[cacheKey].data.forEach(customer => html += createSuggestionItem('customer', customer));
            $('#customer_suggestions').html(html).show();
            allCustomersLoaded = true;
            return;
        }
        
        $.ajax({
            url: '<?= site_url("quotations/get_all_customers") ?>',
            type: 'GET', dataType: 'json', cache: true,
            success: function(response) {
                if (response.status === 'success' && response.customers.length > 0) {
                    // Cache the results
                    searchCache[cacheKey] = {
                        data: response.customers,
                        timestamp: Date.now()
                    };
                    
                    let html = '';
                    response.customers.forEach(customer => html += createSuggestionItem('customer', customer));
                    $('#customer_suggestions').html(html).show();
                    allCustomersLoaded = true;
                }
            }
        });
    }
    
    $('#customer_search').on('input', function() {
        const query = $(this).val();
        clearTimeout(customerSearchTimeout);
        
        if (query.length < 1) {
            if (allCustomersLoaded) $('#customer_suggestions').show();
            else loadAllCustomers();
            return;
        }
        
        // Check cache first
        const cacheKey = 'search_customers_' + query + '_' + <?= $this->aauth->get_user()->loc ?>;
        if (searchCache[cacheKey] && (Date.now() - searchCache[cacheKey].timestamp) < CACHE_DURATION) {
            let html = '';
            searchCache[cacheKey].data.forEach(customer => html += createSuggestionItem('customer', customer));
            $('#customer_suggestions').html(html).show();
            return;
        }
        
        customerSearchTimeout = setTimeout(() => {
            $.ajax({
                url: '<?= site_url("quotations/search_customers") ?>',
                type: 'GET', data: { query }, dataType: 'json', cache: false,
                success: function(response) {
                    if (response.status === 'success' && response.customers.length > 0) {
                        // Cache the results
                        searchCache[cacheKey] = {
                            data: response.customers,
                            timestamp: Date.now()
                        };
                        
                        let html = '';
                        response.customers.forEach(customer => html += createSuggestionItem('customer', customer));
                        $('#customer_suggestions').html(html).show();
                    } else $('#customer_suggestions').hide();
                }
            });
        }, SEARCH_DELAY); // Use optimized delay
    });
    
    // Product search
    function loadAllProducts($input) {
        const $suggestions = $input.siblings('.product-suggestions');
        
        // Check if this specific dropdown already has products loaded
        if ($suggestions.data('products-loaded')) { 
            positionProductDropdown($input);
            $suggestions.show();
            return; 
        }
        
        // Check cache first
        const cacheKey = 'all_products_' + <?= $this->aauth->get_user()->loc ?>;
        if (searchCache[cacheKey] && (Date.now() - searchCache[cacheKey].timestamp) < CACHE_DURATION) {
            let html = '';
            searchCache[cacheKey].data.forEach(product => html += createSuggestionItem('product', product));
            $suggestions.html(html);
            $suggestions.data('products-loaded', true); // Mark this dropdown as loaded
            positionProductDropdown($input);
            $suggestions.show();
            return;
        }
        
        $.ajax({
            url: '<?= site_url("quotations/get_all_products") ?>',
            type: 'GET', dataType: 'json', cache: true,
            success: function(response) {
                if (response.status === 'success' && response.products.length > 0) {
                    // Cache the results
                    searchCache[cacheKey] = {
                        data: response.products,
                        timestamp: Date.now()
                    };
                    
                    let html = '';
                    response.products.forEach(product => html += createSuggestionItem('product', product));
                    $suggestions.html(html);
                    $suggestions.data('products-loaded', true); // Mark this dropdown as loaded
                    positionProductDropdown($input);
                    $suggestions.show();
                }
            }
        });
    }
    
    $(document).on('click', '.product-search', function() {
        loadAllProducts($(this));
    });
    
    // Function to position product dropdown correctly - simple like customer search
    function positionProductDropdown($input) {
        const $dropdown = $input.siblings('.product-suggestions');
        
        // Simple positioning - just show the dropdown, let CSS handle the rest
        $dropdown.css({
            'display': 'block',
            'position': 'absolute',
            'top': '100%',
            'left': '0',
            'width': '100%',
            'z-index': '9999'
        });
    }
    
    $(document).on('input', '.product-search', function() {
        const query = $(this).val();
        const suggestions = $(this).siblings('.product-suggestions');
        clearTimeout(productSearchTimeout);
        
        if (query.length < 1) {
            loadAllProducts($(this));
            return;
        }
        
        // Check cache first
        const cacheKey = 'search_products_' + query + '_' + <?= $this->aauth->get_user()->loc ?>;
        if (searchCache[cacheKey] && (Date.now() - searchCache[cacheKey].timestamp) < CACHE_DURATION) {
            let html = '';
            searchCache[cacheKey].data.forEach(product => html += createSuggestionItem('product', product));
            suggestions.html(html).show();
            return;
        }
        
        productSearchTimeout = setTimeout(() => {
            $.ajax({
                url: '<?= site_url("quotations/search_products") ?>',
                type: 'GET', data: { query }, dataType: 'json', cache: false,
                success: function(response) {
                    if (response.status === 'success' && response.products.length > 0) {
                        // Cache the results
                        searchCache[cacheKey] = {
                            data: response.products,
                            timestamp: Date.now()
                        };
                        
                        let html = '';
                        response.products.forEach(product => html += createSuggestionItem('product', product));
                        suggestions.html(html);
                        positionProductDropdown($(this));
                        suggestions.show();
                    } else suggestions.hide();
                }.bind(this)
            });
        }, SEARCH_DELAY); // Use optimized delay
    });
    
    // Selection handlers
    $(document).on('click', '.customer-suggestion-item', function() {
        const data = $(this).data();
        $('#customer').val(data.id);
        $('#customer_search').val(data.name + ' (' + data.company + ')');
        $('#customer_suggestions').hide();
        fetchCustomerDetails(data.id);
    });
    
    $(document).on('click', '.product-suggestion-item', function() {
        const data = $(this).data();
        const row = $(this).closest('.product-row');
        row.find('.product-id').val(data.id);
        // Display only product code in the product code field
        row.find('.product-search').val(data.code || '');
        row.find('.product-suggestions').hide();
        row.find('.price').val(data.price);
        row.find('input[name="product_des[]"]').val(data.description);
        
        // Store product tax information for later use
        const isTaxable = data.codeType === 'T1';
        row.find('.product-id').data('is-taxable', isTaxable);
        
        // Calculate tax based on code type and price
        if (isTaxable) {
            const taxAmount = data.price * 0.20; // 20% tax for taxable products
            formatAmount(taxAmount, function(formatted) {
                row.find('.tax').val(formatted);
                calculateRowTotal(row);
            });
        } else {
            formatAmount(0, function(formatted) {
                row.find('.tax').val(formatted);
                calculateRowTotal(row);
            });
        }
    });
    
    function fetchCustomerDetails(customerId) {
        $.ajax({
            url: '<?= site_url("quotations/get_customer_details") ?>',
            type: 'GET', data: { customer_id: customerId }, dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const customer = response.customer;
                    $('#bill_to_name').val(customer.name || '');
                    let address = '';
                    if (customer.address) address += customer.address;
                    if (customer.city) address += (address ? '\n' : '') + customer.city;
                    if (customer.region) address += (address ? '\n' : '') + customer.region;
                    if (customer.country) address += (address ? '\n' : '') + customer.country;
                    $('#bill_to_address').val(address);
                }
            }
        });
    }
    
    // Calculations
    function calculateRowTotal(row) {
        const qty = parseFloat(row.find('.qty').val()) || 0;
        const price = parseFloat(row.find('.price').val()) || 0;
        const taxAmount = parseFloat(row.find('.tax').val()) || 0;
        const discount = parseFloat(row.find('.discount').val()) || 0;
        
        // Calculate all amount types
        const subtotal = qty * price;
        const netTax = qty * taxAmount; // Net tax = qty * tax
        const netAmount = subtotal - discount;
        
        // Update all fields with proper formatting
        formatAmount(netTax, function(formatted) {
            row.find('.net-tax').val(formatted);
            // After formatting net tax, format subtotal
            formatAmount(netAmount, function(formatted) {
                row.find('.subtotal').val(formatted);
                // After both are formatted, calculate totals
                calculateTotals();
            });
        });
    }

    function calculateTotals() {
        let subtotal = 0, totalTax = 0, netAmount = 0;
        $('#product_tbody .product-row').each(function() {
            const rowQty = parseFloat($(this).find('.qty').val()) || 0;
            const rowPrice = parseFloat($(this).find('.price').val()) || 0;
            const rowTaxAmount = parseFloat($(this).find('.tax').val()) || 0;
            const rowDiscount = parseFloat($(this).find('.discount').val()) || 0;
            const rowSubtotal = rowQty * rowPrice;
            const rowNetTax = rowQty * rowTaxAmount; // Net tax = qty * tax
            const rowNetAmount = rowSubtotal - rowDiscount;
            subtotal += rowSubtotal;
            totalTax += rowNetTax; // Sum of all net taxes
            netAmount += rowNetAmount;
        });
        const shipping = parseFloat($('#shipping').val()) || 0;
        const shipTax = parseFloat($('#ship_tax').val()) || 0;
        
        // Update all total fields with proper sequential formatting
        formatAmount(netAmount, function(formatted) {
            $('#subtotal').val(formatted);
            // After subtotal is formatted, format tax
            formatAmount(totalTax, function(formatted) {
                $('#tax').val(formatted);
                // After tax is formatted, format total
                formatAmount(netAmount + shipping + shipTax, function(formatted) {
                    $('#total').val(formatted);
                });
            });
        });
    }

    // Event listeners
    $(document).on('click', '#notify .close', () => $('#notify').hide());
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#customer_search, #customer_suggestions, .product-search, .product-suggestions').length) {
            $('#customer_suggestions, .product-suggestions').hide();
        }
    });
    
    $('#add_product').click(() => {
        const row = `<div class="product-row">
            <div class="product-cell">
                <input type="hidden" name="item_id[]" value="">
                <div class="product-search-container">
                    <input type="text" class="form-control product-search" name="product_search[]" placeholder="Search product or click to see all..." autocomplete="off">
                    <input type="hidden" name="product_id[]" class="product-id">
                    <div class="product-suggestions" style="display: none;"></div>
                </div>
            </div>
            <div class="product-cell">
                <input type="text" class="form-control" name="product_des[]" placeholder="Enter Product description (Optional)">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control qty" name="product_qty[]" value="1">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control price" name="product_price[]" placeholder="0.00">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control tax" name="product_tax[]" value="0" readonly>
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control net-tax" name="product_net_tax[]" readonly>
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control discount" name="product_discount[]" value="0">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control subtotal" name="product_subtotal[]" readonly>
            </div>
            <div class="product-cell">
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm add-product-row" title="Add Product">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove Product">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>`;
        $('#product_tbody').append(row);
    });
    
    $(document).on('click', '.remove-row', function() {
        $(this).closest('.product-row').remove();
        calculateTotals();
        updateMinusButtonVisibility(); // Update minus button visibility after removal
    });
    
    $(document).on('click', '.add-product-row', function() {
        const newRow = `<div class="product-row">
            <div class="product-cell">
                <input type="hidden" name="item_id[]" value="">
                <div class="product-search-container">
                    <input type="text" class="form-control product-search" name="product_search[]" placeholder="Search product or click to see all..." autocomplete="off">
                    <input type="hidden" name="product_id[]" class="product-id">
                    <div class="product-suggestions" style="display: none;"></div>
                </div>
            </div>
            <div class="product-cell">
                <input type="text" class="form-control" name="product_des[]" placeholder="Enter Product description (Optional)">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control qty" name="product_qty[]" value="1">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control price" name="product_price[]" placeholder="0.00">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control tax" name="product_tax[]" value="0" readonly>
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control net-tax" name="product_net_tax[]" readonly>
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control discount" name="product_discount[]" value="0">
            </div>
            <div class="product-cell">
                <input type="number" step="0.01" class="form-control subtotal" name="product_subtotal[]" readonly>
            </div>
            <div class="product-cell">
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm add-product-row" title="Add Product">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove Product">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>`;
        $(this).closest('.product-row').after(newRow);
        updateMinusButtonVisibility(); // Update minus button visibility after adding
    });
    
    // Function to update minus button visibility based on number of rows
    function updateMinusButtonVisibility() {
        const rowCount = $('#product_tbody .product-row').length;
        $('.remove-row').each(function() {
            if (rowCount <= 1) {
                $(this).hide(); // Hide minus button when only one row
            } else {
                $(this).show(); // Show minus button when multiple rows
            }
        });
    }
    
    // Initialize minus button visibility on page load
    updateMinusButtonVisibility();
    
    // Initialize calculations on page load
    calculateTotals();
    
    $(document).on('input', '.qty, .price, .tax, .discount', function() {
        const $this = $(this);
        const value = parseFloat($this.val());
        
        // Prevent negative values for qty and price
        if (($this.hasClass('qty') || $this.hasClass('price')) && value < 0) {
            $this.val(0);
        }
        
        // Auto-calculate tax when price changes
        if ($this.hasClass('price')) {
            const row = $this.closest('.product-row');
            const productId = row.find('.product-id').val();
            
            // Get product tax information from stored data
            if (productId) {
                const isTaxable = row.find('.product-id').data('is-taxable');
                
                if (isTaxable) {
                    // Calculate 20% tax for taxable products
                    const newTax = value * 0.20;
                    formatAmount(newTax, function(formatted) {
                        row.find('.tax').val(formatted);
                        calculateRowTotal(row);
                    });
                } else {
                    // No tax for non-taxable products
                    formatAmount(0, function(formatted) {
                        row.find('.tax').val(formatted);
                        calculateRowTotal(row);
                    });
                }
            }
        }
        
        // Only calculate if it's not a price change (price changes are handled above)
        if (!$this.hasClass('price')) {
            calculateRowTotal($(this).closest('.product-row'));
        }
    });
    
    $(document).on('input', '#shipping, #ship_tax', calculateTotals);
    
    // Date picker
    $('#invoicedate, #invoiceduedate').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

    // Form submission
    $('#quotation_form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(response) {
                if (response.status === 'Success') {
                    showAlert(response.message, 'success');
                    setTimeout(() => window.location.href = '<?= site_url('quotations') ?>', 2000);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('An error occurred while saving the quotation.', 'danger');
            }
        });
    });
});
</script>
<!-- Select2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>