<style>
    .group-header {
        background-color: #f4f4f4;
        font-weight: bold;
        font-size: 16px;
        text-align: center;
    }
</style>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title text-center">
                <?php echo $cust_name ?? ''; ?> Product Pricing List
            </h4>
        </div>
        <div class="card-body">
            <!-- Success/Error Notification -->
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-1 col-form-label text-right" for="grouptitle"><?php echo $this->lang->line('GroupTitle'); ?>:*</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control group-title" id="group-title" />
                </div>

                <label class="col-sm-1 col-form-label text-right" for="customers"><?php echo $this->lang->line('Customers'); ?>:*</label>
                <div class="col-sm-3">
                    <select multiple id="chkveg" class="form-control">
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo $customer['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label class="col-sm-1 col-form-label text-right" for="products"><?php echo $this->lang->line('Products'); ?>:*</label>
                <div class="col-sm-3">
                    <select multiple id="chkProducts" class="form-control">
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['pid']; ?>"
                                data-purchase-price="<?php echo $product['product_price']; ?>"
                                data-old-sale-price="<?php echo $product['fproduct_price']; ?>"
                                data-product-code="<?php echo $product['product_code']; ?>">
                                <?php echo $product['product_code'] . ' - ' . $product['product_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="product-prices" class="mt-3"></div>

            <!-- Save Button -->
            <div class="row mt-3">
                <div class="col-md-12 text-center">
                    <button id="save-pricing" class="btn btn-info btn-sm w-25">Save Customer Group Pricing</button>
                </div>
            </div>
        </div>

        <div class="card-content">
            <div class="card-body">
                <table id="clientstable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <!-- <th>Group Title</th> -->
                            <th>Customer Name</th>
                            <th>Product Code</th>
                            <th>Product Description</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Profit Margin</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.title = "Customer Group Pricing List";
    $(document).ready(function() {
        const csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        const csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

        // Initialize Select2
        $('#chkveg, #chkProducts').select2();

        // Move success notification to the top
        $('.content-body').prepend(`
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
        `);

        // DataTable Initialization
        const table = $('#clientstable').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            responsive: true,
            ajax: {
                url: "<?php echo site_url('customers/cgp_list'); ?>",
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                }
            },
            rowGroup: {
                dataSrc: 'group_title', // Group rows by `group_title`
                startRender: function(rows, group) {
                    // Render the group title row as a header
                    return $('<tr/>')
                        .append('<td colspan="7" class="group-title">' + group + '</td>')
                        .attr('class', 'group-header'); // Add a class for styling
                }
            },
            columns: [{
                    data: 'customer_name'
                },
                {
                    data: 'productcode'
                },
                {
                    data: 'description'
                },
                {
                    data: 'costprice'
                },
                {
                    data: 'salesprice'
                },
                {
                    data: 'profitmargin',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return parseFloat(data).toFixed(2) + ' %'; // Format as percentage
                        }
                        return data; // Keep raw value for sorting
                    }
                }
            ],
            columnDefs: [{
                targets: [0],
                orderable: false
            }],
            dom: 'Blfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5]
                    },
                    customize: function(doc) {
                        doc.content[1].table.widths = ['15%', '20%', '20%', '15%', '15%', '15%'];
                        doc.pageMargins = [40, 60, 40, 60];
                        doc.content[0].text = 'Product Pricing List';
                    }
                }
            ]
        });

        // Handle Product Selection Change
        $('#chkProducts').on('change', function() {
            const selectedProducts = $(this).val();
            const priceContainer = $('#product-prices');

            // Clear only the products that are no longer selected
            priceContainer.find('.row').each(function() {
                const productId = $(this).find('.sale-price').data('product-id');
                if (!selectedProducts.includes(productId)) {
                    $(this).remove();
                }
            });

            // Add new products
            selectedProducts.forEach(productId => {
                if (!priceContainer.find(`.sale-price[data-product-id="${productId}"]`).length) {
                    const option = $('#chkProducts option[value="' + productId + '"]');
                    const productName = option.text();
                    const productCode = option.data('product-code');
                    const purchasePrice = option.data('purchase-price');
                    const oldSalePrice = option.data('old-sale-price');

                    // Create a new pricing input field for each selected product
                    const priceRow = `
            <div class="form-group row">
                <label class="col-sm-2 col-form-label text-right" for="grouptitle">${productCode} Purchase Price:*</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control purchase-price" value="${purchasePrice}" readonly />
                </div>
                <label class="col-sm-2 col-form-label text-right" for="grouptitle">${productCode} Old Sale Price:*</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control old-sale-price" value="${oldSalePrice}" readonly />
                </div>
                <label class="col-sm-2 col-form-label text-right" for="grouptitle">Set New Sale Price:*</label>
                <div class="col-sm-2">
                    <input type="number" class="form-control sale-price" placeholder="Enter New Sale Price" data-product-id="${productId}" min="${purchasePrice}" step="any" />
                </div>
            </div>
        `;
                    priceContainer.append(priceRow);
                }
            });
        });

        // Save Pricing Data
        $('#save-pricing').on('click', function() {
            const selectedCustomers = $('#chkveg').val();
            const group_title = $('#group-title').val().trim();
            const pricingData = [];
            let hasError = false;

            // Validate Group Title
            if (!group_title) {
                $('#notify').removeClass('alert-success alert-danger').addClass('alert-warning');
                $('#notify .message').html('Group Title is required');
                $('#notify').slideDown();
                setTimeout(function() {
                    $('#notify').slideUp();
                }, 3000);

                return;
            }

            // Validate Sale Prices against Purchase Prices
            $('#product-prices .sale-price').each(function() {
                const productId = $(this).data('product-id');
                const salePrice = $(this).val().trim();
                const purchasePrice = parseFloat($(this).closest('.row').find('.purchase-price').val());

                if (salePrice === "" || isNaN(salePrice)) {
                    hasError = true;
                    $('#notify').removeClass('alert-success').addClass('alert-warning').text('Please enter a valid sale price.');
                    $('#notify').show();
                    setTimeout(() => $('#notify').fadeOut(), 3000);
                    return false;
                }

                const salePriceFloat = parseFloat(salePrice);
                if (salePriceFloat < purchasePrice || salePriceFloat < 0) {
                    hasError = true;
                    $('#notify').removeClass('alert-success alert-danger').addClass('alert-warning');
                    $('#notify .message').html('Sale price cannot be less than purchase price or negative.');
                    $('#notify').slideDown();
                    setTimeout(function() {
                        $('#notify').slideUp();
                    }, 3000);
                    return false;
                } else {
                    pricingData.push({
                        product_id: productId,
                        purchase_price: purchasePrice,
                        sale_price: salePriceFloat
                    });
                }

            });

            if (hasError) return; // Prevent further execution if there's an error

            if (!selectedCustomers || pricingData.length === 0) {
                $('#notify').removeClass('alert-success alert-danger').addClass('alert-warning');
                $('#notify .message').html('Please select customers, products, and set sale prices.');
                $('#notify').slideDown();
                setTimeout(function() {
                    $('#notify').slideUp();
                }, 3000);

                return;
            }

            $.ajax({
                url: "<?php echo site_url('customers/save_pricing'); ?>",
                type: 'POST',
                data: {
                    customers: selectedCustomers,
                    pricing_data: pricingData,
                    group_title: group_title,
                    [csrfName]: csrfHash
                },
                success: function(response) {
                    $('#notify').removeClass('alert-danger alert-warning').addClass('alert-success'); // Reset classes
                    $('#notify .message').html(''); // Clear previous message

                    if (response.status === 'success') {
                        $('#notify .message').html(response.message);
                        $('#notify').removeClass('alert-danger alert-warning').addClass('alert-success').slideDown();

                        table.ajax.reload(null, false);
                        resetPricingForm();

                        setTimeout(function() {
                            $('#notify').slideUp();
                        }, 3000);
                    } else {
                        $('#notify .message').html(response.message);
                        $('#notify').removeClass('alert-success alert-warning').addClass('alert-danger').slideDown();
                    }
                }

            });
        });

        function resetPricingForm() {
            $('#chkveg').val(null).trigger('change');
            $('#chkProducts').val(null).trigger('change');
            $('#product-prices').empty();
            $('#group-title').val('');
        }
    });
</script>