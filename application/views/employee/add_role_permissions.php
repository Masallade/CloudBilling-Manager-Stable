<!-- employee/role_add.php -->

<style>
    /* Main content styling */

    .role-section {

        display: flex;

        justify-content: space-between;

        align-items: center;

        margin-bottom: 20px;

    }



    .role-section input[type="text"] {

        width: 48%;

        padding: 8px;

        font-size: 16px;

        border: 1px solid #ddd;

        border-radius: 4px;

    }



    .module-section {

        display: flex;

        flex-wrap: wrap;

        gap: 20px;

    }



    .module {

        flex: 1 1 calc(33.33% - 20px);

        background-color: #f9f9f9;

        padding: 15px;

        border-radius: 4px;

        border: 1px solid #ddd;

    }



    .module h3 {

        font-size: 16px;

        margin-bottom: 10px;

        background-color: #e9ecef;

        padding: 8px;

        border-radius: 4px;

        text-transform: uppercase;

    }

    



    .checkbox-group {

        display: flex;

        flex-wrap: wrap;

        gap: 10px;

    }



    .checkbox-group label {

        display: flex;

        align-items: center;

        font-size: 14px;

        width: 48%;

        color: #555;

    }

    .checkbox-group.stock-top-row label {
        width: 32%;
    }



    .checkbox-group input[type="checkbox"] {

        margin-right: 5px;

    }



    .submenu {

        margin-left: 20px;

        margin-top: 10px;

    }
</style>



<div class="content-body">

    <div id="notify" class="alert alert-success" style="display:none;">

        <a href="#" class="close" data-dismiss="alert">&times;</a>

        <div class="message"></div>

    </div>

    <div class="card">

        <div class="card-header">

            <h5 class="title">Add New Role</h5>

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

            <div class="card-body">

                <form id="roleAddForm">

                    <!-- Hidden field for CSRF token -->

                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                    <div class="form-group">

                        <label for="roleName">Role Name*</label>

                        <input type="text" class="form-control" id="roleName" name="roleName" required>

                    </div>

                    <div class="form-group">

                        <label for="roleDescription">Role Description</label>

                        <textarea class="form-control" id="roleDescription" name="roleDescription"></textarea>

                    </div>

                    <!-- Check All / Uncheck All -->

                    <div class="form-row align-items-center mb-1">

                        <!-- Check All and Uncheck All buttons -->

                        <div class="col-auto">

                            <div class="form-group mb-0">

                                <button type="button" class="btn btn-sm btn-info" id="checkAll">Check All</button>

                                <button type="button" class="btn btn-sm btn-warning" id="uncheckAll">Uncheck All</button>

                            </div>

                        </div>



                        <!-- Save and Cancel buttons -->

                        <div class="col-auto ml-auto">

                            <div class="form-group mb-0">

                                <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('Save'); ?></button>

                                <a href="<?php echo base_url('employee/permissions'); ?>" class="btn btn-secondary"><?php echo $this->lang->line('Cancel'); ?></a>

                            </div>

                        </div>

                    </div>



                    <div class="module-section">

                        <div class="module">

                            <h3>DASHBOARD</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="dashboard"> Access Dashboard</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>SALES</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="salesAccess"> Access Sales</label>

                                <label><input type="checkbox" name="salesNewInvoice"> New Invoice</label>

                                <label><input type="checkbox" name="salesEditInvoice"> Edit Invoice</label>

                                <label><input type="checkbox" name="salesDeleteInvoice"> Delete Invoice</label>

                                <label><input type="checkbox" name="salesDuplicateInvoice"> Duplicate Invoice</label>

                                <label><input type="checkbox" name="salesSelectPostInvoice"> Select Posted Invoice</label>

                                <label><input type="checkbox" name="salesManageInvoices"> Manage Invoices</label>

                                <label><input type="checkbox" name="salesUnpostedInvoices"> Unposted Invoices</label>

                                <label><input type="checkbox" name="salesPostedInvoices"> Posted Invoices</label>

                                <label><input type="checkbox" name="salesPrintInvoices"> Print Invoices</label>

                                <label><input type="checkbox" name="salesShareInvoices"> Share Invoices</label>

                                <label><input type="checkbox" name="salesCustomerInvoices"> Customer Invoices</label>

                                <label><input type="checkbox" name="salesCreditNote"> Credit Note</label>

                                <label><input type="checkbox" name="invoiceSave"> Invoice Save</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>QUOTATION</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="quotationAccess"> Access Quotation</label>

                                <label><input type="checkbox" name="quotationNewQuotation"> New Quotation</label>

                                <label><input type="checkbox" name="quotationEditQuotation"> Edit Quotation</label>

                                <label><input type="checkbox" name="quotationDeleteQuotation"> Delete Quotation</label>

                                <label><input type="checkbox" name="quotationManageQuotations"> Manage Quotations</label>

                                <label><input type="checkbox" name="quotationViewQuotation"> View Quotation</label>

                                <label><input type="checkbox" name="quotationPrintQuotation"> Print Quotation</label>

                                <label><input type="checkbox" name="quotationConvertQuotation"> Convert to Invoice</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>PURCHASE</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="purchaseAccess"> Access Purchase</label>

                                <label><input type="checkbox" name="purchaseCreateOrder"> Create Order</label>

                                <label><input type="checkbox" name="purchaseEditOrder"> Edit Order</label>

                                <label><input type="checkbox" name="purchaseDeleteOrder"> Delete Order</label>

                                <label><input type="checkbox" name="purchaseManageOrders"> Manage Orders</label>

                                <label><input type="checkbox" name="purchaseViewOrder"> View Order</label>

                                <label><input type="checkbox" name="purchaseExportOrder"> Export Order</label>

                                <label><input type="checkbox" name="purchaseVerifyOrder"> Verify Order</label>

                                <label><input type="checkbox" name="purchaseCreditNotePO"> Credit Note</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>STOCK RETURN</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="stockReturnAccess"> Stock Return</label>

                                <label><input type="checkbox" name="stockSuppliersReturns"> Suppliers Returns</label>

                                <label><input type="checkbox" name="stockCustomersReturns"> Customers Returns</label>

                                <label><input type="checkbox" name="stockCustomerViewReturns"> Customer View Returns</label>

                                <label><input type="checkbox" name="stockReturnManageStockReturn"> Manage Stock Return</label>

                                <label><input type="checkbox" name="stockReturnViewStockReturn"> View Stock Return</label>

                                <label><input type="checkbox" name="stockReturnPrintStockReturn"> Print Stock Return</label>

                                <label><input type="checkbox" name="stockReturnDeleteStockReturn"> Delete Stock Return</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>STOCK</h3>

                            <div class="checkbox-group">
                                <label><input type="checkbox" name="stockAccess"> Access Stock</label>

                                <label><input type="checkbox" name="stockTransfer"> Stock Transfer</label>

                                <label><input type="checkbox" name="stockAdjustment"> Stock Adjustment</label>

                                <label><input type="checkbox" name="stockItemsManager"> Items Manager</label>

                                <label><input type="checkbox" name="stockNewProduct"> New Product</label>

                                <label><input type="checkbox" name="stockEditProduct"> Edit Product</label>

                                <label><input type="checkbox" name="stockDeleteProduct"> Delete Product</label>

                                <label><input type="checkbox" name="stockManageProducts"> Manage Products</label>

                                <label><input type="checkbox" name="stockSettingsProduct"> Settings Products</label>

                                <label><input type="checkbox" name="stockProductCategories"> Product Categories</label>

                                <label><input type="checkbox" name="stockNewCategory"> New Category</label>

                                <label><input type="checkbox" name="stockEditCategory"> Edit Category</label>

                                <label><input type="checkbox" name="stockDeleteCategory"> Delete Category</label>

                                <label><input type="checkbox" name="stockViewCategory"> View Category</label>

                                <label><input type="checkbox" name="stockWarehouses"> Warehouses</label>

                                <label><input type="checkbox" name="stockWarehouseCreate"> Create Warehouse</label>

                                <label><input type="checkbox" name="stockWarehouseEdit"> Edit Warehouse</label>

                                <label><input type="checkbox" name="stockWarehouseDelete"> Delete Warehouse</label>

                                <label><input type="checkbox" name="stockWarehouseView"> View Warehouse</label>

                                <label><input type="checkbox" name="stockProductsLabel"> Products Label</label>

                                <label><input type="checkbox" name="stockCustomLabel"> Custom Label</label>

                                <label><input type="checkbox" name="stockStandardLabel"> Standard Label</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>CUSTOMER</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="customerAccess"> Access Customer</label>

                                <label><input type="checkbox" name="customerNewCustomer"> New Customer</label>

                                <label><input type="checkbox" name="customerManageCustomer"> Manage Customer</label>

                                <label><input type="checkbox" name="customerEditCustomer"> Edit Customer</label>

                                <label><input type="checkbox" name="customerDeleteCustomer"> Delete Customer</label>

                                <label><input type="checkbox" name="customerViewCustomer"> View Customer</label>

                                <label><input type="checkbox" name="customerItemPriceList"> Item Price List</label>

                                <label><input type="checkbox" name="customerGroupCustomerPrice"> Group Customer Price</label>

                                <label><input type="checkbox" name="customerReceipt"> Customer Receipt</label>

                                <label><input type="checkbox" name="customerActivities"> Customer Activities</label>

                            </div>

                            <h3>Customer Card Actions</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="customerViewDetails"> View Details</label>

                                <label><input type="checkbox" name="customerViewInvoices"> View Invoices</label>

                                <label><input type="checkbox" name="customerViewActivity"> View Activity</label>

                                <label><input type="checkbox" name="customerViewCreditNotes"> View Credit Notes</label>

                                <label><input type="checkbox" name="customerViewAdvancePayments"> View Advance Payments</label>

                                <label><input type="checkbox" name="customerAccountStatements"> Account Statements</label>

                                <label><input type="checkbox" name="customerPrintVATReport"> Print VAT Report</label>

                                <label><input type="checkbox" name="customerSendMessage"> Send Mail</label>

                                <label><input type="checkbox" name="customerEditProfile"> Edit Profile</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>SUPPLIERS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="suppliersAccess"> Access Suppliers</label>

                                <label><input type="checkbox" name="suppliersNewSupplier"> New Supplier</label>

                                <label><input type="checkbox" name="suppliersEditSupplier"> Edit Supplier</label>

                                <label><input type="checkbox" name="suppliersDeleteSupplier"> Delete Supplier</label>

                                <label><input type="checkbox" name="suppliersManageSuppliers"> Manage Suppliers</label>

                            </div>

                            <h3>Supplier Card Actions</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="suppliersViewDetails"> View Details</label>

                                <label><input type="checkbox" name="suppliersViewPO"> View PO</label>

                                <label><input type="checkbox" name="suppliersViewActivity"> View Activity</label>

                                <label><input type="checkbox" name="suppliersAccountStatements"> Account Statements</label>

                                <label><input type="checkbox" name="suppliersPrintVATReport"> Print VAT Report</label>

                                <label><input type="checkbox" name="suppliersSendMessage"> Send Mail</label>

                                <label><input type="checkbox" name="suppliersBulkPayment"> Bulk Payment</label>

                                <label><input type="checkbox" name="suppliersEditProfile"> Edit Profile</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>SALES REPORTS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="salesReportsAccess"> Sales Reports Access</label>

                                <label><input type="checkbox" name="salesReportsByProductSummary"> Sales By Product Summary</label>

                                <label><input type="checkbox" name="salesReportsByProduct"> Sales By Product</label>

                                <label><input type="checkbox" name="salesReportsDailyBreakdownSheet"> Daily Breakdown Sheet</label>

                                <label><input type="checkbox" name="salesReportsDayBook"> Day Book</label>

                                <label><input type="checkbox" name="salesReportsVatCollection"> Vat Collection Report</label>

                                <label><input type="checkbox" name="salesReportsPaymentRecievedSummary"> Payment Recieved Summary</label>

                                <label><input type="checkbox" name="salesReportsStockReturn"> Stock Return Reports</label>

                                <label><input type="checkbox" name="salesReportsCustomerClosingBalance"> Customer Closing Balance</label>

                                <label><input type="checkbox" name="salesReportsAllCustomersBalance"> All Customers Balance</label>

                                <label><input type="checkbox" name="salesReportsProfitandLoss"> Profit & Loss</label>

                                <label><input type="checkbox" name="salesReportsZone"> Zone Report</label>

                                <label><input type="checkbox" name="salesReportsSales"> Sales Report</label>

                                <label><input type="checkbox" name="salesReportsProduct"> Product Report</label>

                            </div>

                            <h3>Charts</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="chartAccess"> Chart Reports</label>

                                <label><input type="checkbox" name="salesreportsProductCategory"> Product Category Reports Chart</label>

                                <label><input type="checkbox" name=salesreportTrendingProduct"> Trending Product Reports</label>

                                <label><input type="checkbox" name="salesreportsTopCustomers"> Top Customers Reports</label>

                            </div>

                        </div>
                        <div class="module">

                            <h3>PURCHASE REPORTS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="purchaseReportsAccess"> Purchase Reports</label>

                                <label><input type="checkbox" name="purchaseReportsByCategory"> Purchase By Category</label>

                                <label><input type="checkbox" name="purchaseReportsBySupplier"> Purchase By Supplier</label>

                                <label><input type="checkbox" name="purchaseReportsSupplierPayment"> Supplier Payment Report</label>

                                <label><input type="checkbox" name="purchaseReportsVatCollection"> Vat Report</label>

                                <label><input type="checkbox" name="purchaseReportsTransactionsBySupplier"> Transactions By Supplier</label>

                            </div>

                        </div>

                        <div class="module">
                            <h3>Employees</h3>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="usersAccess"> Access Users</label>
                                <label><input type="checkbox" name="usersEmployees"> Employees</label>
                                <label><input type="checkbox" name="usersEmployeesAdd"> Employee Add</label>
                                <label><input type="checkbox" name="usersEmployeesEdit"> Employee Edit</label>
                                <label><input type="checkbox" name="usersEmployeesDelete"> Employee Delete</label>
                                <label><input type="checkbox" name="usersEmployeesList"> Employees List</label>
                                <label><input type="checkbox" name="usersEmployeesManage"> Manage Employees</label>
                                <label><input type="checkbox" name="usersEmployeesView"> View Employee</label>
                            </div>
                        </div>
                        <div class="module">
                            <h3>Role and Permissions</h3>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="usersEmployeesPermissions"> Permissions</label>
                                <label><input type="checkbox" name="usersEmployeesPermissionsAdd"> Role and Permission Add</label>
                                <label><input type="checkbox" name="usersEmployeesPermissionsEdit"> Role and Permission Edit</label>
                                <label><input type="checkbox" name="usersEmployeesPermissionsDelete"> Role and Permission Delete</label>
                            </div>
                        </div>
                        <div class="module">
                            <h3>HR & Payroll</h3>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="usersEmployeesSalaries"> Salaries</label>
                                <label><input type="checkbox" name="usersEmployeesAttendances"> Attendances</label>
                                <label><input type="checkbox" name="usersEmployeesHolidays"> Holidays</label>
                                <label><input type="checkbox" name="usersPayroll"> Payroll</label>
                                <label><input type="checkbox" name="usersDepartment"> Department</label>
                                <label><input type="checkbox" name="usersLocations"> Locations</label>
                            </div>
                        </div>

                        <div class="module">

                            <h3>ACCOUNTS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="accountsAccess"> Access Accounts</label>

                                <label><input type="checkbox" name="accountsManage"> Manage Accounts</label>

                                <label><input type="checkbox" name="accountsList"> Accounts List</label>

                                <label><input type="checkbox" name="accountsBalanceSheet"> Balance Sheet</label>

                                <label><input type="checkbox" name="accountsStatements"> Account Statements</label>

                                <label><input type="checkbox" name="accountsIncome"> Income</label>

                                <label><input type="checkbox" name="accountsExpense"> Expense</label>

                                <label><input type="checkbox" name="accountsVATObligations"> VAT Obligations</label>

                            </div>

                            <h3>Transactions</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="accountsTransactions"> Transactions</label>

                                <label><input type="checkbox" name="accountsViewTransactions"> View Transactions</label>

                                <label><input type="checkbox" name="accountsNewTransaction"> New Transaction</label>

                                <label><input type="checkbox" name="accountsAdvancePayment"> Advance Payment</label>

                                <label><input type="checkbox" name="accountsReversalTransaction"> Reversal Transaction</label>

                                <label><input type="checkbox" name="accountsNewTransfer"> New Transfer</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>DATA EXPORT IMPORT</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="dataExportImportAccess"> Access Data Export Import</label>

                                <label><input type="checkbox" name="dataExportPeople"> Export People Data</label>

                                <label><input type="checkbox" name="dataExportTransactions"> Export Transactions</label>

                                <label><input type="checkbox" name="dataExportProducts"> Export Products</label>

                                <label><input type="checkbox" name="dataExportLogData"> Log Data</label>

                                <label><input type="checkbox" name="dataExportAccountStatements"> Account Statements</label>

                                <label><input type="checkbox" name="dataExportTax"> Tax Export</label>

                                <label><input type="checkbox" name="dataExportDatabaseBackup"> Database Backup</label>

                                <label><input type="checkbox" name="dataImportProducts"> Import Products</label>

                                <label><input type="checkbox" name="dataImportCustomers"> Import Customers</label>

                                <label><input type="checkbox" name="dataExportPeopleProducts"> Products Account Statements</label>

                                <label><input type="checkbox" name="dataBulkDeletion"> Bulk Deletion</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>MISCELLANEOUS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="miscellaneousAccess"> Access Miscellaneous</label>

                                <label><input type="checkbox" name="miscellaneousInvoiceNotes"> Invoice Notes</label>

                                <label><input type="checkbox" name="miscellaneousCalendar"> Calendar</label>

                                <label><input type="checkbox" name="miscellaneousDocuments"> Documents</label>

                            </div>

                        </div>

                        <div class="module">

                            <h3>SETTINGS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="settingsAccess"> Access Settings</label>

                                <label><input type="checkbox" name="settingsLanguage"> Language</label>

                                <label><input type="checkbox" name="settingsGeneral"> General Settings</label>

                                <label><input type="checkbox" name="settingsCompanyProfile"> Company Profile</label>

                                <label><input type="checkbox" name="settingsInvoiceFormat"> Invoice Format</label>

                                <label><input type="checkbox" name="settingsInvoiceFormatUpload"> Upload Invoice Format</label>

                                <label><input type="checkbox" name="settingsInvoiceFormatDelete"> Delete Invoice Format</label>

                                <label><input type="checkbox" name="settingsThirdPartyIntegration"> Third Party Integration</label>

                                <label><input type="checkbox" name="projectsAccess"> Projects Access</label>
                            </div>

                        </div>

                        <div class="module">

                            <h3>POS</h3>

                            <div class="checkbox-group">

                                <label><input type="checkbox" name="posAccess"> Access POS</label>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>



<script>
    document.title = 'Add New Role';

    document.getElementById("checkAll").addEventListener("click", function() {

        const checkboxes = document.querySelectorAll("input[type='checkbox']");

        checkboxes.forEach(cb => cb.checked = true);

    });



    document.getElementById("uncheckAll").addEventListener("click", function() {

        const checkboxes = document.querySelectorAll("input[type='checkbox']");

        checkboxes.forEach(cb => cb.checked = false);

    });

    $(document).ready(function() {

        // Handle form submission

        $('#roleAddForm').on('submit', function(e) {

            e.preventDefault();



            // Create FormData object

            var formData = new FormData(this);



            // Add all checkboxes with 1 or 0

            $(this).find('input[type="checkbox"]').each(function() {

                formData.set($(this).attr('name'), $(this).is(':checked') ? '1' : '0');

            });



            // Convert FormData to URL-encoded string for debugging

            var serializedData = new URLSearchParams(formData).toString();

            console.log(serializedData); // For debugging



            // Show loading state

            var submitButton = $(this).find('button[type="submit"]');

            var originalText = submitButton.text();

            submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');



            // AJAX request

            $.ajax({

                url: '<?php echo base_url("employee/add_role"); ?>',

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                dataType: 'json',

                success: function(response) {

                    if (response.status === 'success') {

                        window.location.href = response.redirect;

                    } else {

                        $('#messageBox').html(response.message); // or handle error

                    }

                },

                error: function(xhr, status, error) {

                    $('#notify').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');

                    console.error(xhr.responseText);

                },

                complete: function() {

                    submitButton.prop('disabled', false).html(originalHtml);

                }

            });

        });

    });
</script>