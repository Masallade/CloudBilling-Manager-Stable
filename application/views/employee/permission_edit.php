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

      /* Notification styling - Top of page */
      #notify {
          position: fixed;
          top: 20px;
          left: 50%;
          transform: translateX(-50%);
          z-index: 9999;
          width: 90%;
          max-width: 600px;
          margin: 0;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
          border-radius: 8px;
          animation: slideDown 0.3s ease-out;
          display: none;
          /* Initially hidden */
      }

      @keyframes slideDown {
          from {
              opacity: 0;
              transform: translateX(-50%) translateY(-20px);
          }

          to {
              opacity: 1;
              transform: translateX(-50%) translateY(0);
          }
      }

      #notify .alert {
          margin-bottom: 0;
          border-radius: 8px;
          font-weight: 500;
          padding: 15px 20px;
          border: none;
          font-size: 16px;
      }

      #notify .alert-success {
          background: linear-gradient(135deg, #28a745, #20c997);
          color: white;
          border-left: 4px solid #1e7e34;
      }

      #notify .alert-danger {
          background: linear-gradient(135deg, #dc3545, #fd7e14);
          color: white;
          border-left: 4px solid #bd2130;
      }

      #notify .close {
          color: white;
          opacity: 0.8;
          font-size: 20px;
          font-weight: bold;
      }

      #notify .close:hover {
          opacity: 1;
          color: white;
      }
  </style>



  <div class="content-body">

      <div id="notify" class="alert alert-success" style="display:none;">
          <a href="#" class="close" data-dismiss="alert">&times;</a>
          <div class="message"></div>
      </div>

      <div class="card">

          <div class="card-header">

              <h5 class="title">Edit Role Permissions - <?= htmlspecialchars($role->role_name) ?></h5>

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

                  <form id="permissionsForm">

                      <input type="hidden" name="role_id" value="<?= $role->id ?>">

                      <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">



                      <div class="form-group">

                          <label for="roleName">Role Name*</label>

                          <input type="text" class="form-control" id="roleName" name="roleName" value="<?= htmlspecialchars($role->role_name) ?>" required>

                      </div>

                      <div class="form-group">

                          <label for="roleDescription">Role Description</label>

                          <textarea class="form-control" id="roleDescription" name="roleDescription"><?= htmlspecialchars($role->role_description) ?></textarea>

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

                                  <label><input type="checkbox" name="dashboard" <?= isset($permissions['dashboard']) && $permissions['dashboard'] ? 'checked' : '' ?>> Access Dashboard</label>

                              </div>

                          </div>



                          <div class="module">

                              <h3>SALES</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="salesAccess" <?= isset($permissions['salesAccess']) && $permissions['salesAccess'] ? 'checked' : '' ?>> Access Sales</label>

                                  <label><input type="checkbox" name="salesNewInvoice" <?= isset($permissions['salesNewInvoice']) && $permissions['salesNewInvoice'] ? 'checked' : '' ?>> New Invoice</label>

                                  <label><input type="checkbox" name="salesEditInvoice" <?= isset($permissions['salesEditInvoice']) && $permissions['salesEditInvoice'] ? 'checked' : '' ?>> Edit Invoice</label>

                                  <label><input type="checkbox" name="salesDeleteInvoice" <?= isset($permissions['salesDeleteInvoice']) && $permissions['salesDeleteInvoice'] ? 'checked' : '' ?>> Delete Invoice</label>

                                  <label><input type="checkbox" name="salesDuplicateInvoice" <?= isset($permissions['salesDuplicateInvoice']) && $permissions['salesDuplicateInvoice'] ? 'checked' : '' ?>> Duplicate Invoice</label>

                                  <label><input type="checkbox" name="salesSelectPostInvoice" <?= isset($permissions['salesSelectPostInvoice']) && $permissions['salesSelectPostInvoice'] ? 'checked' : '' ?>> Select Post Invoice</label>

                                  <label><input type="checkbox" name="salesManageInvoices" <?= isset($permissions['salesManageInvoices']) && $permissions['salesManageInvoices'] ? 'checked' : '' ?>> Manage Invoices</label>

                                  <label><input type="checkbox" name="salesUnpostedInvoices" <?= isset($permissions['salesUnpostedInvoices']) && $permissions['salesUnpostedInvoices'] ? 'checked' : '' ?>> Unposted Invoices</label>

                                  <label><input type="checkbox" name="salesPostedInvoices" <?= isset($permissions['salesPostedInvoices']) && $permissions['salesPostedInvoices'] ? 'checked' : '' ?>> Posted Invoices</label>

                                  <label><input type="checkbox" name="salesPrintInvoices" <?= isset($permissions['salesPrintInvoices']) && $permissions['salesPrintInvoices'] ? 'checked' : '' ?>> Print Invoices</label>

                                  <label><input type="checkbox" name="salesShareInvoices" <?= isset($permissions['salesShareInvoices']) && $permissions['salesShareInvoices'] ? 'checked' : '' ?>> Share Invoices</label>

                                  <label><input type="checkbox" name="salesCustomerInvoices" <?= isset($permissions['salesCustomerInvoices']) && $permissions['salesCustomerInvoices'] ? 'checked' : '' ?>> Customer Invoices</label>

                                  <label><input type="checkbox" name="salesCreditNote" <?= isset($permissions['salesCreditNote']) && $permissions['salesCreditNote'] ? 'checked' : '' ?>> Credit Note</label>

                                  <label> <input type="checkbox" name="invoiceSave" <?= isset($permissions['invoiceSave']) && $permissions['invoiceSave'] ? 'checked' : '' ?>> Invoice Save</label>


                              </div>

                          </div>

                          <div class="module">

                              <h3>QUOTATION</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="quotationAccess" <?= isset($permissions['quotationAccess']) && $permissions['quotationAccess'] ? 'checked' : '' ?>> Access Quotation</label>

                                  <label><input type="checkbox" name="quotationNewQuotation" <?= isset($permissions['quotationNewQuotation']) && $permissions['quotationNewQuotation'] ? 'checked' : '' ?>> New Quotation</label>

                                  <label><input type="checkbox" name="quotationEditQuotation" <?= isset($permissions['quotationEditQuotation']) && $permissions['quotationEditQuotation'] ? 'checked' : '' ?>> Edit Quotation</label>

                                  <label><input type="checkbox" name="quotationDeleteQuotation" <?= isset($permissions['quotationDeleteQuotation']) && $permissions['quotationDeleteQuotation'] ? 'checked' : '' ?>> Delete Quotation</label>

                                  <label><input type="checkbox" name="quotationManageQuotations" <?= isset($permissions['quotationManageQuotations']) && $permissions['quotationManageQuotations'] ? 'checked' : '' ?>> Manage Quotations</label>

                                  <label><input type="checkbox" name="quotationViewQuotation" <?= isset($permissions['quotationViewQuotation']) && $permissions['quotationViewQuotation'] ? 'checked' : '' ?>> View Quotation</label>

                                  <label><input type="checkbox" name="quotationPrintQuotation" <?= isset($permissions['quotationPrintQuotation']) && $permissions['quotationPrintQuotation'] ? 'checked' : '' ?>> Print Quotation</label>

                                  <label><input type="checkbox" name="quotationConvertQuotation" <?= isset($permissions['quotationConvertQuotation']) && $permissions['quotationConvertQuotation'] ? 'checked' : '' ?>> Convert to Invoice</label>


                              </div>

                          </div>

                          <div class="module">

                              <h3>PURCHASE</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="purchaseAccess" <?= isset($permissions['purchaseAccess']) && $permissions['purchaseAccess'] ? 'checked' : '' ?>> Access Purchase</label>

                                  <label><input type="checkbox" name="purchaseCreateOrder" <?= isset($permissions['purchaseCreateOrder']) && $permissions['purchaseCreateOrder'] ? 'checked' : '' ?>> Create Order</label>

                                  <label><input type="checkbox" name="purchaseEditOrder" <?= isset($permissions['purchaseEditOrder']) && $permissions['purchaseEditOrder'] ? 'checked' : '' ?>> Edit Order</label>

                                  <label><input type="checkbox" name="purchaseDeleteOrder" <?= isset($permissions['purchaseDeleteOrder']) && $permissions['purchaseDeleteOrder'] ? 'checked' : '' ?>> Delete Order</label>

                                  <label><input type="checkbox" name="purchaseManageOrders" <?= isset($permissions['purchaseManageOrders']) && $permissions['purchaseManageOrders'] ? 'checked' : '' ?>> Manage Orders</label>

                                  <label><input type="checkbox" name="purchaseViewOrder" <?= isset($permissions['purchaseViewOrder']) && $permissions['purchaseViewOrder'] ? 'checked' : '' ?>> View Order</label>

                                  <label><input type="checkbox" name="purchaseExportOrder" <?= isset($permissions['purchaseExportOrder']) && $permissions['purchaseExportOrder'] ? 'checked' : '' ?>> Export Order</label>

                                  <label><input type="checkbox" name="purchaseVerifyOrder" <?= isset($permissions['purchaseVerifyOrder']) && $permissions['purchaseVerifyOrder'] ? 'checked' : '' ?>> Verify Order</label>

                                  <label><input type="checkbox" name="purchaseCreditNotePO" <?= isset($permissions['purchaseCreditNotePO']) && $permissions['purchaseCreditNotePO'] ? 'checked' : '' ?>> Credit Note</label>


                              </div>

                          </div>

                          <div class="module">

                              <h3>STOCK RETURN</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="stockReturnAccess" <?= isset($permissions['stockReturnAccess']) && $permissions['stockReturnAccess'] ? 'checked' : '' ?>> Stock Return</label>

                                  <label><input type="checkbox" name="stockSuppliersReturns" <?= isset($permissions['stockSuppliersReturns']) && $permissions['stockSuppliersReturns'] ? 'checked' : '' ?>> Suppliers Returns</label>

                                  <label><input type="checkbox" name="stockCustomersReturns" <?= isset($permissions['stockCustomersReturns']) && $permissions['stockCustomersReturns'] ? 'checked' : '' ?>> Customers Returns</label>

                                  <label><input type="checkbox" name="stockCustomerViewReturns" <?= isset($permissions['stockCustomerViewReturns']) && $permissions['stockCustomerViewReturns'] ? 'checked' : '' ?>> Customer View Returns</label>

                                  <label><input type="checkbox" name="stockReturnManageStockReturn" <?= isset($permissions['stockReturnManageStockReturn']) && $permissions['stockReturnManageStockReturn'] ? 'checked' : '' ?>> Manage Stock Return</label>

                                  <label><input type="checkbox" name="stockReturnViewStockReturn" <?= isset($permissions['stockReturnViewStockReturn']) && $permissions['stockReturnViewStockReturn'] ? 'checked' : '' ?>> View Stock Return</label>

                                  <label><input type="checkbox" name="stockReturnPrintStockReturn" <?= isset($permissions['stockReturnPrintStockReturn']) && $permissions['stockReturnPrintStockReturn'] ? 'checked' : '' ?>> Print Stock Return</label>

                                  <label><input type="checkbox" name="stockReturnDeleteStockReturn" <?= isset($permissions['stockReturnDeleteStockReturn']) && $permissions['stockReturnDeleteStockReturn'] ? 'checked' : '' ?>> Delete Stock Return</label>

                              </div>

                          </div>

                          <div class="module">

                              <h3>STOCK</h3>

                              <div class="checkbox-group">
                                  <label><input type="checkbox" name="stockAccess" <?= isset($permissions['stockAccess']) && $permissions['stockAccess'] ? 'checked' : '' ?>> Access Stock</label>

                                  <label><input type="checkbox" name="stockTransfer" <?= isset($permissions['stockTransfer']) && $permissions['stockTransfer'] ? 'checked' : '' ?>> Stock Transfer</label>

                                  <label><input type="checkbox" name="stockAdjustment" <?= isset($permissions['stockAdjustment']) && $permissions['stockAdjustment'] ? 'checked' : '' ?>> Stock Adjustment</label>

                                  <label><input type="checkbox" name="stockItemsManager" <?= isset($permissions['stockItemsManager']) && $permissions['stockItemsManager'] ? 'checked' : '' ?>> Items Manager</label>

                                  <label><input type="checkbox" name="stockNewProduct" <?= isset($permissions['stockNewProduct']) && $permissions['stockNewProduct'] ? 'checked' : '' ?>> New Product</label>

                                  <label><input type="checkbox" name="stockEditProduct" <?= isset($permissions['stockEditProduct']) && $permissions['stockEditProduct'] ? 'checked' : '' ?>> Edit Product</label>

                                  <label><input type="checkbox" name="stockDeleteProduct" <?= isset($permissions['stockDeleteProduct']) && $permissions['stockDeleteProduct'] ? 'checked' : '' ?>> Delete Product</label>

                                  <label><input type="checkbox" name="stockManageProducts" <?= isset($permissions['stockManageProducts']) && $permissions['stockManageProducts'] ? 'checked' : '' ?>> Manage Products</label>

                                  <label><input type="checkbox" name="stockSettingsProduct" <?= isset($permissions['stockSettingsProduct']) && $permissions['stockSettingsProduct'] ? 'checked' : '' ?>> Settings Products</label>

                                  <label><input type="checkbox" name="stockProductCategories" <?= isset($permissions['stockProductCategories']) && $permissions['stockProductCategories'] ? 'checked' : '' ?>> Product Categories</label>

                                  <label><input type="checkbox" name="stockNewCategory" <?= isset($permissions['stockNewCategory']) && $permissions['stockNewCategory'] ? 'checked' : '' ?>> New Category</label>

                                  <label><input type="checkbox" name="stockEditCategory" <?= isset($permissions['stockEditCategory']) && $permissions['stockEditCategory'] ? 'checked' : '' ?>> Edit Category</label>

                                  <label><input type="checkbox" name="stockDeleteCategory" <?= isset($permissions['stockDeleteCategory']) && $permissions['stockDeleteCategory'] ? 'checked' : '' ?>> Delete Category</label>

                                  <label><input type="checkbox" name="stockViewCategory" <?= isset($permissions['stockViewCategory']) && $permissions['stockViewCategory'] ? 'checked' : '' ?>> View Category</label>

                                  <label><input type="checkbox" name="stockWarehouses" <?= isset($permissions['stockWarehouses']) && $permissions['stockWarehouses'] ? 'checked' : '' ?>> Warehouses</label>

                                  <label><input type="checkbox" name="stockWarehouseCreate" <?= isset($permissions['stockWarehouseCreate']) && $permissions['stockWarehouseCreate'] ? 'checked' : '' ?>> Create Warehouse</label>

                                  <label><input type="checkbox" name="stockWarehouseEdit" <?= isset($permissions['stockWarehouseEdit']) && $permissions['stockWarehouseEdit'] ? 'checked' : '' ?>> Edit Warehouse</label>

                                  <label><input type="checkbox" name="stockWarehouseDelete" <?= isset($permissions['stockWarehouseDelete']) && $permissions['stockWarehouseDelete'] ? 'checked' : '' ?>> Delete Warehouse</label>

                                  <label><input type="checkbox" name="stockWarehouseView" <?= isset($permissions['stockWarehouseView']) && $permissions['stockWarehouseView'] ? 'checked' : '' ?>> View Warehouse</label>

                                  <label><input type="checkbox" name="stockUnits" <?= isset($permissions['stockUnits']) && $permissions['stockUnits'] ? 'checked' : '' ?>> Measurement Units</label>

                                  <label><input type="checkbox" name="stockProductsLabel" <?= isset($permissions['stockProductsLabel']) && $permissions['stockProductsLabel'] ? 'checked' : '' ?>> Products Label</label>

                                  <label><input type="checkbox" name="stockCustomLabel" <?= isset($permissions['stockCustomLabel']) && $permissions['stockCustomLabel'] ? 'checked' : '' ?>> Custom Label</label>

                                  <label><input type="checkbox" name="stockStandardLabel" <?= isset($permissions['stockStandardLabel']) && $permissions['stockStandardLabel'] ? 'checked' : '' ?>> Standard Label</label>

                              </div>

                          </div>

                          <div class="module">

                              <h3>CUSTOMER</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="customerAccess" <?= isset($permissions['customerAccess']) && $permissions['customerAccess'] ? 'checked' : '' ?>> Access Customer</label>

                                  <label><input type="checkbox" name="customerNewCustomer" <?= isset($permissions['customerNewCustomer']) && $permissions['customerNewCustomer'] ? 'checked' : '' ?>> New Customer</label>

                                  <label><input type="checkbox" name="customerManageCustomer" <?= isset($permissions['customerManageCustomer']) && $permissions['customerManageCustomer'] ? 'checked' : '' ?>> Manage Customer</label>

                                  <label><input type="checkbox" name="customerEditCustomer" <?= isset($permissions['customerEditCustomer']) && $permissions['customerEditCustomer'] ? 'checked' : '' ?>> Edit Customer</label>

                                  <label><input type="checkbox" name="customerDeleteCustomer" <?= isset($permissions['customerDeleteCustomer']) && $permissions['customerDeleteCustomer'] ? 'checked' : '' ?>> Delete Customer</label>

                                  <label><input type="checkbox" name="customerViewCustomer" <?= isset($permissions['customerViewCustomer']) && $permissions['customerViewCustomer'] ? 'checked' : '' ?>> View Customer</label>

                                  <label><input type="checkbox" name="customerItemPriceList" <?= isset($permissions['customerItemPriceList']) && $permissions['customerItemPriceList'] ? 'checked' : '' ?>> Item Price List</label>

                                  <label><input type="checkbox" name="customerGroupCustomerPrice" <?= isset($permissions['customerGroupCustomerPrice']) && $permissions['customerGroupCustomerPrice'] ? 'checked' : '' ?>> Group Customer Price</label>

                                  <label><input type="checkbox" name="customerReceipt" <?= isset($permissions['customerReceipt']) && $permissions['customerReceipt'] ? 'checked' : '' ?>> Customer Receipt</label>

                                  <label><input type="checkbox" name="customerActivities" <?= isset($permissions['customerActivities']) && $permissions['customerActivities'] ? 'checked' : '' ?>> Customer Activities</label>

                              </div>

                              <h3>Customer Card Actions</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="customerViewDetails" <?= isset($permissions['customerViewDetails']) && $permissions['customerViewDetails'] ? 'checked' : '' ?>> View Details</label>

                                  <label><input type="checkbox" name="customerViewInvoices" <?= isset($permissions['customerViewInvoices']) && $permissions['customerViewInvoices'] ? 'checked' : '' ?>> View Invoices</label>

                                  <label><input type="checkbox" name="customerViewActivity" <?= isset($permissions['customerViewActivity']) && $permissions['customerViewActivity'] ? 'checked' : '' ?>> View Activity</label>

                                  <label><input type="checkbox" name="customerViewCreditNotes" <?= isset($permissions['customerViewCreditNotes']) && $permissions['customerViewCreditNotes'] ? 'checked' : '' ?>> View Credit Notes</label>

                                  <label><input type="checkbox" name="customerViewAdvancePayments" <?= isset($permissions['customerViewAdvancePayments']) && $permissions['customerViewAdvancePayments'] ? 'checked' : '' ?>> View Advance Payments</label>

                                  <label><input type="checkbox" name="customerAccountStatements" <?= isset($permissions['customerAccountStatements']) && $permissions['customerAccountStatements'] ? 'checked' : '' ?>> Account Statements</label>

                                  <label><input type="checkbox" name="customerPrintVATReport" <?= isset($permissions['customerPrintVATReport']) && $permissions['customerPrintVATReport'] ? 'checked' : '' ?>> Print VAT Report</label>

                                  <label><input type="checkbox" name="customerSendMessage" <?= isset($permissions['customerSendMessage']) && $permissions['customerSendMessage'] ? 'checked' : '' ?>> Send Mail</label>

                                  <label><input type="checkbox" name="customerEditProfile" <?= isset($permissions['customerEditProfile']) && $permissions['customerEditProfile'] ? 'checked' : '' ?>> Edit Profile</label>

                              </div>

                          </div>



                          <div class="module">

                              <h3>SUPPLIERS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="suppliersAccess" <?= isset($permissions['suppliersAccess']) && $permissions['suppliersAccess'] ? 'checked' : '' ?>> Access Suppliers</label>

                                  <label><input type="checkbox" name="suppliersNewSupplier" <?= isset($permissions['suppliersNewSupplier']) && $permissions['suppliersNewSupplier'] ? 'checked' : '' ?>> New Supplier</label>

                                  <label><input type="checkbox" name="suppliersEditSupplier" <?= isset($permissions['suppliersEditSupplier']) && $permissions['suppliersEditSupplier'] ? 'checked' : '' ?>> Edit Supplier</label>

                                  <label><input type="checkbox" name="suppliersDeleteSupplier" <?= isset($permissions['suppliersDeleteSupplier']) && $permissions['suppliersDeleteSupplier'] ? 'checked' : '' ?>> Delete Supplier</label>

                                  <label><input type="checkbox" name="suppliersManageSuppliers" <?= isset($permissions['suppliersManageSuppliers']) && $permissions['suppliersManageSuppliers'] ? 'checked' : '' ?>> Manage Suppliers</label>

                              </div>

                              <h3>Supplier Card Actions</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="suppliersViewDetails" <?= isset($permissions['suppliersViewDetails']) && $permissions['suppliersViewDetails'] ? 'checked' : '' ?>> View Details</label>

                                  <label><input type="checkbox" name="suppliersViewPO" <?= isset($permissions['suppliersViewPO']) && $permissions['suppliersViewPO'] ? 'checked' : '' ?>> View PO</label>

                                  <label><input type="checkbox" name="suppliersViewActivity" <?= isset($permissions['suppliersViewActivity']) && $permissions['suppliersViewActivity'] ? 'checked' : '' ?>> View Activity</label>

                                  <label><input type="checkbox" name="suppliersAccountStatements" <?= isset($permissions['suppliersAccountStatements']) && $permissions['suppliersAccountStatements'] ? 'checked' : '' ?>> Account Statements</label>

                                  <label><input type="checkbox" name="suppliersPrintVATReport" <?= isset($permissions['suppliersPrintVATReport']) && $permissions['suppliersPrintVATReport'] ? 'checked' : '' ?>> Print VAT Report</label>

                                  <label><input type="checkbox" name="suppliersSendMessage" <?= isset($permissions['suppliersSendMessage']) && $permissions['suppliersSendMessage'] ? 'checked' : '' ?>> Send Mail</label>

                                  <label><input type="checkbox" name="suppliersBulkPayment" <?= isset($permissions['suppliersBulkPayment']) && $permissions['suppliersBulkPayment'] ? 'checked' : '' ?>> Bulk Payment</label>

                                  <label><input type="checkbox" name="suppliersEditProfile" <?= isset($permissions['suppliersEditProfile']) && $permissions['suppliersEditProfile'] ? 'checked' : '' ?>> Edit Profile</label>

                              </div>

                          </div>



                          <div class="module">

                              <h3>SALES REPORTS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="salesReportsAccess" <?= isset($permissions['salesReportsAccess']) && $permissions['salesReportsAccess'] ? 'checked' : '' ?>> Sales Reports Access</label>

                                  <label><input type="checkbox" name="salesReportsByProductSummary" <?= isset($permissions['salesReportsByProductSummary']) && $permissions['salesReportsByProductSummary'] ? 'checked' : '' ?>> Sales By Product Summary</label>

                                  <label><input type="checkbox" name="salesReportsByProduct" <?= isset($permissions['salesReportsByProduct']) && $permissions['salesReportsByProduct'] ? 'checked' : '' ?>> Sales By Product</label>

                                  <label><input type="checkbox" name="salesReportsDailyBreakdownSheet" <?= isset($permissions['salesReportsDailyBreakdownSheet']) && $permissions['salesReportsDailyBreakdownSheet'] ? 'checked' : '' ?>> Daily Breakdown Sheet</label>

                                  <label><input type="checkbox" name="salesReportsDayBook" <?= isset($permissions['salesReportsDayBook']) && $permissions['salesReportsDayBook'] ? 'checked' : '' ?>> Day Book</label>

                                  <label><input type="checkbox" name="salesReportsVatCollection" <?= isset($permissions['salesReportsVatCollection']) && $permissions['salesReportsVatCollection'] ? 'checked' : '' ?>> Vat Collection</label>

                                  <label><input type="checkbox" name="salesReportsPaymentRecievedSummary" <?= isset($permissions['salesReportsPaymentRecievedSummary']) && $permissions['salesReportsPaymentRecievedSummary'] ? 'checked' : '' ?>> Payment Recieved Summary</label>

                                  <label><input type="checkbox" name="salesReportsStockReturn" <?= isset($permissions['salesReportsStockReturn']) && $permissions['salesReportsStockReturn'] ? 'checked' : '' ?>> Stock Return Reports</label>

                                  <label><input type="checkbox" name="salesReportsCustomerClosingBalance" <?= isset($permissions['salesReportsCustomerClosingBalance']) && $permissions['salesReportsCustomerClosingBalance'] ? 'checked' : '' ?>> Customer Closing Balance</label>

                                  <label><input type="checkbox" name="salesReportsAllCustomersBalance" <?= isset($permissions['salesReportsAllCustomersBalance']) && $permissions['salesReportsAllCustomersBalance'] ? 'checked' : '' ?>> All Customers Balance</label>

                                  <label><input type="checkbox" name="salesReportsProfitandLoss" <?= isset($permissions['salesReportsProfitandLoss']) && $permissions['salesReportsProfitandLoss'] ? 'checked' : '' ?>> Profit & Loss</label>

                                  <label><input type="checkbox" name="salesReportsZone" <?= isset($permissions['salesReportsZone']) && $permissions['salesReportsZone'] ? 'checked' : '' ?>> Zone Report</label>

                                  <label><input type="checkbox" name="salesReportsSales" <?= isset($permissions['salesReportsSales']) && $permissions['salesReportsSales'] ? 'checked' : '' ?>> Sales Report</label>

                                  <label><input type="checkbox" name="salesReportsProduct" <?= isset($permissions['salesReportsProduct']) && $permissions['salesReportsProduct'] ? 'checked' : '' ?>> Product Report</label>

                              </div>

                              <h3>Charts</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="chartAccess" <?= isset($permissions['chartAccess']) && $permissions['chartAccess'] ? 'checked' : '' ?>> Chart Reports</label>

                                  <label><input type="checkbox" name="salesreportsProductCategory" <?= isset($permissions['salesreportsProductCategory']) && $permissions['salesreportsProductCategory'] ? 'checked' : '' ?>> Product Category Reports Chart</label>

                                  <label><input type="checkbox" name="salesreportTrendingProduct" <?= isset($permissions['salesreportTrendingProduct']) && $permissions['salesreportTrendingProduct'] ? 'checked' : '' ?>> Trending Product Reports</label>

                                  <label><input type="checkbox" name="salesreportsTopCustomers" <?= isset($permissions['salesreportsTopCustomers']) && $permissions['salesreportsTopCustomers'] ? 'checked' : '' ?>> Top Customers Reports</label>

                              </div>

                          </div>

                          <div class="module">

                              <h3>PURCHASE REPORTS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="purchaseReportsAccess" <?= isset($permissions['purchaseReportsAccess']) && $permissions['purchaseReportsAccess'] ? 'checked' : '' ?>> Purchase Reports</label>

                                  <label><input type="checkbox" name="purchaseReportsByCategory" <?= isset($permissions['purchaseReportsByCategory']) && $permissions['purchaseReportsByCategory'] ? 'checked' : '' ?>> Purchase By Category</label>

                                  <label><input type="checkbox" name="purchaseReportsBySupplier" <?= isset($permissions['purchaseReportsBySupplier']) && $permissions['purchaseReportsBySupplier'] ? 'checked' : '' ?>> Purchase By Supplier</label>

                                  <label><input type="checkbox" name="purchaseReportsSupplierPayment" <?= isset($permissions['purchaseReportsSupplierPayment']) && $permissions['purchaseReportsSupplierPayment'] ? 'checked' : '' ?>> Supplier Payment Report</label>

                                  <label><input type="checkbox" name="purchaseReportsVatCollection" <?= isset($permissions['purchaseReportsVatCollection']) && $permissions['purchaseReportsVatCollection'] ? 'checked' : '' ?>> Vat Report</label>

                                  <label><input type="checkbox" name="purchaseReportsTransactionsBySupplier" <?= isset($permissions['purchaseReportsTransactionsBySupplier']) && $permissions['purchaseReportsTransactionsBySupplier'] ? 'checked' : '' ?>> Transactions By Supplier</label>

                              </div>

                          </div>


                          <div class="module">
                              <h3>Employees</h3>
                              <div class="checkbox-group">
                                  <label><input type="checkbox" name="usersAccess" <?= isset($permissions['usersAccess']) && $permissions['usersAccess'] ? 'checked' : '' ?>> Access Users</label>
                                  <label><input type="checkbox" name="usersEmployees" <?= isset($permissions['usersEmployees']) && $permissions['usersEmployees'] ? 'checked' : '' ?>> Access Employees</label>
                                  <label><input type="checkbox" name="usersEmployeesAdd" <?= isset($permissions['usersEmployeesAdd']) && $permissions['usersEmployeesAdd'] ? 'checked' : '' ?>> Employee Add</label>
                                  <label><input type="checkbox" name="usersEmployeesEdit" <?= isset($permissions['usersEmployeesEdit']) && $permissions['usersEmployeesEdit'] ? 'checked' : '' ?>> Employee Edit</label>
                                  <label><input type="checkbox" name="usersEmployeesDelete" <?= isset($permissions['usersEmployeesDelete']) && $permissions['usersEmployeesDelete'] ? 'checked' : '' ?>> Employee Delete</label>
                                  <label><input type="checkbox" name="usersEmployeesList" <?= isset($permissions['usersEmployeesList']) && $permissions['usersEmployeesList'] ? 'checked' : '' ?>> Employees List</label>
                                  <label><input type="checkbox" name="usersEmployeesManage" <?= isset($permissions['usersEmployeesManage']) && $permissions['usersEmployeesManage'] ? 'checked' : '' ?>> Manage Employees</label>
                                  <label><input type="checkbox" name="usersEmployeesView" <?= isset($permissions['usersEmployeesView']) && $permissions['usersEmployeesView'] ? 'checked' : '' ?>> View Employee</label>
                              </div>
                          </div>
                          <div class="module">
                              <h3>Role and Permissions</h3>
                              <div class="checkbox-group">
                                  <label><input type="checkbox" name="usersEmployeesPermissions" <?= isset($permissions['usersEmployeesPermissions']) && $permissions['usersEmployeesPermissions'] ? 'checked' : '' ?>> Permissions</label>
                                  <label><input type="checkbox" name="usersEmployeesPermissionsAdd" <?= isset($permissions['usersEmployeesPermissionsAdd']) && $permissions['usersEmployeesPermissionsAdd'] ? 'checked' : '' ?>> Role and Permission Add</label>
                                  <label><input type="checkbox" name="usersEmployeesPermissionsEdit" <?= isset($permissions['usersEmployeesPermissionsEdit']) && $permissions['usersEmployeesPermissionsEdit'] ? 'checked' : '' ?>> Role and Permission Edit</label>
                                  <label><input type="checkbox" name="usersEmployeesPermissionsDelete" <?= isset($permissions['usersEmployeesPermissionsDelete']) && $permissions['usersEmployeesPermissionsDelete'] ? 'checked' : '' ?>> Role and Permission Delete</label>
                              </div>
                          </div>
                          <div class="module">
                              <h3>HR & Payroll</h3>
                              <div class="checkbox-group">
                                  <label><input type="checkbox" name="usersEmployeesSalaries" <?= isset($permissions['usersEmployeesSalaries']) && $permissions['usersEmployeesSalaries'] ? 'checked' : '' ?>> Salaries</label>
                                  <label><input type="checkbox" name="usersEmployeesAttendances" <?= isset($permissions['usersEmployeesAttendances']) && $permissions['usersEmployeesAttendances'] ? 'checked' : '' ?>> Attendances</label>
                                  <label><input type="checkbox" name="usersEmployeesHolidays" <?= isset($permissions['usersEmployeesHolidays']) && $permissions['usersEmployeesHolidays'] ? 'checked' : '' ?>> Holidays</label>
                                  <label><input type="checkbox" name="usersPayroll" <?= isset($permissions['usersPayroll']) && $permissions['usersPayroll'] ? 'checked' : '' ?>> Payroll</label>
                                  <label><input type="checkbox" name="usersDepartment" <?= isset($permissions['usersDepartment']) && $permissions['usersDepartment'] ? 'checked' : '' ?>> Department</label>
                                  <label><input type="checkbox" name="usersLocations" <?= isset($permissions['usersLocations']) && $permissions['usersLocations'] ? 'checked' : '' ?>> Locations</label>
                              </div>
                          </div>



                          <div class="module">

                              <h3>ACCOUNTS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="accountsAccess" <?= isset($permissions['accountsAccess']) && $permissions['accountsAccess'] ? 'checked' : '' ?>> Access Accounts</label>

                                  <label><input type="checkbox" name="accountsManage" <?= isset($permissions['accountsManage']) && $permissions['accountsManage'] ? 'checked' : '' ?>> Manage Accounts</label>

                                  <label><input type="checkbox" name="accountsList" <?= isset($permissions['accountsList']) && $permissions['accountsList'] ? 'checked' : '' ?>> Accounts List</label>

                                  <label><input type="checkbox" name="accountsBalanceSheet" <?= isset($permissions['accountsBalanceSheet']) && $permissions['accountsBalanceSheet'] ? 'checked' : '' ?>> Balance Sheet</label>

                                  <label><input type="checkbox" name="accountsStatements" <?= isset($permissions['accountsStatements']) && $permissions['accountsStatements'] ? 'checked' : '' ?>> Account Statements</label>

                                  <label><input type="checkbox" name="accountsIncome" <?= isset($permissions['accountsIncome']) && $permissions['accountsIncome'] ? 'checked' : '' ?>> Income</label>

                                  <label><input type="checkbox" name="accountsExpense" <?= isset($permissions['accountsExpense']) && $permissions['accountsExpense'] ? 'checked' : '' ?>> Expense</label>

                                  <label><input type="checkbox" name="accountsVATObligations" <?= isset($permissions['accountsVATObligations']) && $permissions['accountsVATObligations'] ? 'checked' : '' ?>> VAT Obligations</label>

                              </div>

                              <h3>Transactions</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="accountsTransactions" <?= isset($permissions['accountsTransactions']) && $permissions['accountsTransactions'] ? 'checked' : '' ?>> Transactions</label>

                                  <label><input type="checkbox" name="accountsViewTransactions" <?= isset($permissions['accountsViewTransactions']) && $permissions['accountsViewTransactions'] ? 'checked' : '' ?>> View Transactions</label>

                                  <label><input type="checkbox" name="accountsNewTransaction" <?= isset($permissions['accountsNewTransaction']) && $permissions['accountsNewTransaction'] ? 'checked' : '' ?>> New Transaction</label>

                                  <label><input type="checkbox" name="accountsAdvancePayment" <?= isset($permissions['accountsAdvancePayment']) && $permissions['accountsAdvancePayment'] ? 'checked' : '' ?>> Advance Payment</label>

                                  <label><input type="checkbox" name="accountsReversalTransaction" <?= isset($permissions['accountsReversalTransaction']) && $permissions['accountsReversalTransaction'] ? 'checked' : '' ?>> Reversal Transaction</label>

                                  <label><input type="checkbox" name="accountsNewTransfer" <?= isset($permissions['accountsNewTransfer']) && $permissions['accountsNewTransfer'] ? 'checked' : '' ?>> New Transfer</label>

                              </div>

                          </div>



                          <div class="module">

                              <h3>DATA EXPORT IMPORT</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="dataExportImportAccess" <?= isset($permissions['dataExportImportAccess']) && $permissions['dataExportImportAccess'] ? 'checked' : '' ?>> Access Data Export Import</label>

                                  <label><input type="checkbox" name="dataExportPeople" <?= isset($permissions['dataExportPeople']) && $permissions['dataExportPeople'] ? 'checked' : '' ?>> Export People Data</label>

                                  <label><input type="checkbox" name="dataExportTransactions" <?= isset($permissions['dataExportTransactions']) && $permissions['dataExportTransactions'] ? 'checked' : '' ?>> Export Transactions</label>

                                  <label><input type="checkbox" name="dataExportProducts" <?= isset($permissions['dataExportProducts']) && $permissions['dataExportProducts'] ? 'checked' : '' ?>> Export Products</label>

                                  <label><input type="checkbox" name="dataExportLogData" <?= isset($permissions['dataExportLogData']) && $permissions['dataExportLogData'] ? 'checked' : '' ?>> Log Data</label>

                                  <label><input type="checkbox" name="dataExportAccountStatements" <?= isset($permissions['dataExportAccountStatements']) && $permissions['dataExportAccountStatements'] ? 'checked' : '' ?>> Account Statements</label>

                                  <label><input type="checkbox" name="dataExportTax" <?= isset($permissions['dataExportTax']) && $permissions['dataExportTax'] ? 'checked' : '' ?>> Tax Export</label>

                                  <label><input type="checkbox" name="dataExportDatabaseBackup" <?= isset($permissions['dataExportDatabaseBackup']) && $permissions['dataExportDatabaseBackup'] ? 'checked' : '' ?>> Database Backup</label>

                                  <label><input type="checkbox" name="dataImportProducts" <?= isset($permissions['dataImportProducts']) && $permissions['dataImportProducts'] ? 'checked' : '' ?>> Import Products</label>

                                  <label><input type="checkbox" name="dataImportCustomers" <?= isset($permissions['dataImportCustomers']) && $permissions['dataImportCustomers'] ? 'checked' : '' ?>> Import Customers</label>

                                  <label><input type="checkbox" name="dataExportPeopleProducts" <?= isset($permissions['dataExportPeopleProducts']) && $permissions['dataExportPeopleProducts'] ? 'checked' : '' ?>> Products Account Statements</label>

                                  <label><input type="checkbox" name="dataBulkDeletion" <?= isset($permissions['dataBulkDeletion']) && $permissions['dataBulkDeletion'] ? 'checked' : '' ?>> Bulk Deletion</label>

                              </div>

                          </div>



                          <div class="module">

                              <h3>MISCELLANEOUS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="miscellaneousAccess" <?= isset($permissions['miscellaneousAccess']) && $permissions['miscellaneousAccess'] ? 'checked' : '' ?>> Access Miscellaneous</label>

                                  <label><input type="checkbox" name="miscellaneousInvoiceNotes" <?= isset($permissions['miscellaneousInvoiceNotes']) && $permissions['miscellaneousInvoiceNotes'] ? 'checked' : '' ?>> Invoice Notes</label>

                                  <label><input type="checkbox" name="miscellaneousCalendar" <?= isset($permissions['miscellaneousCalendar']) && $permissions['miscellaneousCalendar'] ? 'checked' : '' ?>> Calendar</label>

                                  <label><input type="checkbox" name="miscellaneousDocuments" <?= isset($permissions['miscellaneousDocuments']) && $permissions['miscellaneousDocuments'] ? 'checked' : '' ?>> Documents</label>

                              </div>

                          </div>



                          <div class="module">

                              <h3>SETTINGS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="settingsAccess" <?= isset($permissions['settingsAccess']) && $permissions['settingsAccess'] ? 'checked' : '' ?>> Access Settings</label>

                                  <label><input type="checkbox" name="settingsLanguage" <?= isset($permissions['settingsLanguage']) && $permissions['settingsLanguage'] ? 'checked' : '' ?>> Language</label>

                                  <label><input type="checkbox" name="settingsGeneral" <?= isset($permissions['settingsGeneral']) && $permissions['settingsGeneral'] ? 'checked' : '' ?>> General Settings</label>

                                  <label><input type="checkbox" name="settingsCompanyProfile" <?= isset($permissions['settingsCompanyProfile']) && $permissions['settingsCompanyProfile'] ? 'checked' : '' ?>> Company Profile</label>

                                  <label><input type="checkbox" name="settingsInvoiceFormat" <?= isset($permissions['settingsInvoiceFormat']) && $permissions['settingsInvoiceFormat'] ? 'checked' : '' ?>> Invoice Format</label>

                                  <label><input type="checkbox" name="settingsInvoiceFormatUpload" <?= isset($permissions['settingsInvoiceFormatUpload']) && $permissions['settingsInvoiceFormatUpload'] ? 'checked' : '' ?>> Upload Invoice Format</label>

                                  <label><input type="checkbox" name="settingsInvoiceFormatDelete" <?= isset($permissions['settingsInvoiceFormatDelete']) && $permissions['settingsInvoiceFormatDelete'] ? 'checked' : '' ?>> Delete Invoice Format</label>

                                  <label><input type="checkbox" name="settingsThirdPartyIntegration" <?= isset($permissions['settingsThirdPartyIntegration']) && $permissions['settingsThirdPartyIntegration'] ? 'checked' : '' ?>> Third Party Integration</label>

                                  <label><input type="checkbox" name="projectsAccess" <?= isset($permissions['projectsAccess']) && $permissions['projectsAccess'] ? 'checked' : '' ?>> Projects Access</label>
                              </div>

                          </div>

                          <div class="module">

                              <h3>POS</h3>

                              <div class="checkbox-group">

                                  <label><input type="checkbox" name="posAccess" <?= isset($permissions['posAccess']) && $permissions['posAccess'] ? 'checked' : '' ?>> Access POS</label>

                              </div>

                          </div>

                      </div>

                  </form>

              </div>

          </div>

      </div>

  </div>



  <script>
      document.title = 'Edit Permission';

      document.getElementById("checkAll").addEventListener("click", function() {

          const checkboxes = document.querySelectorAll("input[type='checkbox']");

          checkboxes.forEach(cb => cb.checked = true);

      });



      document.getElementById("uncheckAll").addEventListener("click", function() {

          const checkboxes = document.querySelectorAll("input[type='checkbox']");

          checkboxes.forEach(cb => cb.checked = false);

      });

      $(document).ready(function() {

          // Handle notification close button
          $(document).on('click', '#notify .close', function(e) {
              e.preventDefault();
              $('#notify').fadeOut(function() {
                  $(this).remove();
              });
          });

          $('#permissionsForm').on('submit', function(e) {

              e.preventDefault();

              // Get form data
              var formData = new FormData(this);

              // Add all checkboxes with 1 or 0
              $(this).find('input[type="checkbox"]').each(function() {
                  formData.set($(this).attr('name'), $(this).is(':checked') ? '1' : '0');
              });

              // Show loading state
              var submitButton = $(this).find('button[type="submit"]');
              var originalText = submitButton.text();
              submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

              // AJAX request
              $.ajax({
                  url: '<?= base_url("employee/update_role_permissions") ?>',
                  type: 'POST',
                  data: formData,
                  processData: false,
                  contentType: false,
                  dataType: 'json',
                  success: function(response) {
                      // Reset button immediately
                      submitButton.prop('disabled', false).html(originalText);

                      console.log('Response received:', response); // Debug log

                      if (response.status === 'success') {
                          // Remove any existing notification
                          $("#notify").remove();

                          // Create and show success message
                          $('.content-body').prepend(`
                              <div id="notify" class="alert alert-success" style="display:none;">
                                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                                  <i class="fa fa-check-circle" style="margin-right: 10px;"></i><strong>SUCCESS!</strong> ${response.message} <small style="opacity: 0.9;">(Page updated successfully)</small>
                              </div>
                          `);

                          $('#notify').fadeIn();

                          // Auto-hide success message after 5 seconds and remove it from the DOM
                          setTimeout(function() {
                              $('#notify').fadeOut(function() {
                                  $(this).remove();
                              });
                          }, 5000);
                      } else {
                          // Remove any existing notification
                          $("#notify").remove();

                          // Create and show error message
                          $('.content-body').prepend(`
                              <div id="notify" class="alert alert-danger" style="display:none;">
                                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                                  <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i><strong>ERROR!</strong> ${response.message || 'An error occurred'}
                              </div>
                          `);

                          $('#notify').fadeIn();

                          // Auto-hide error message after 5 seconds and remove it from the DOM
                          setTimeout(function() {
                              $('#notify').fadeOut(function() {
                                  $(this).remove();
                              });
                          }, 5000);
                      }
                  },
                  error: function(xhr, status, error) {
                      // Reset button immediately
                      submitButton.prop('disabled', false).html(originalText);

                      console.error('AJAX Error:', xhr.responseText);

                      // Remove any existing notification
                      $("#notify").remove();

                      // Create and show error message
                      $('.content-body').prepend(`
                          <div id="notify" class="alert alert-danger" style="display:none;">
                              <a href="#" class="close" data-dismiss="alert">&times;</a>
                              <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i><strong>ERROR!</strong> An error occurred. Please try again.
                          </div>
                      `);

                      $('#notify').fadeIn();

                      // Auto-hide error message after 5 seconds and remove it from the DOM
                      setTimeout(function() {
                          $('#notify').fadeOut(function() {
                              $(this).remove();
                          });
                      }, 5000);
                  },
                  complete: function() {
                      // This is redundant but kept as fallback
                      submitButton.prop('disabled', false).html(originalText);
                  }
              });
          });
      });
  </script>