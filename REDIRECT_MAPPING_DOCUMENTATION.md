# Module Redirect Mapping Documentation

## Overview
This document describes the comprehensive redirect mapping system implemented in `assets/myjs/control__1scr.js`.

## Purpose
After successfully creating or editing records in any module, the system automatically redirects users to the appropriate index/list page for that module.

## Implementation
Location: `assets/myjs/control__1scr.js` → `addObject()` function → Success handler

## Redirect Mappings

### Supplier Module
- `supplier/addsupplier` → `supplier`
- `supplier/editsupplier` → `supplier`

### Customer Module
- `customers/addcustomer` → `customers`
- `customers/editcustomer` → `customers`
- `customers/addnote` → `customers`
- `customers/editnote` → `customers`
- `clientgroup/add` → `customers/group`
- `clientgroup/editgroupupdate` → `customers/group`

### Purchase Module
- `purchase/action` → `purchase` (Create new purchase order)
- `purchase/editaction` → `purchase` (Edit existing purchase)
- `purchase/editaction2` → `purchase` (Edit alternative form)

**Note:** Purchase orders redirect to the main purchase list page after creation/editing.

### Employee Module
- `employee/attendance` → `employee/attendances`
- `employee/addhday` → `employee/holidays`
- `employee/editholiday` → `employee/holidays`
- `employee/adddep` → `employee/departments`
- `employee/editdep` → `employee/departments`
- `employee/adddes` → `employee/designations`
- `employee/editdes` → `employee/designations`
- `employee/add_emp` → `employee`
- `employee/edit_emp` → `employee`

### Products Module
- `products/addproduct` → `products`
- `products/editproduct` → `products`
- `productcategory/addcat` → `productcategory`
- `productcategory/editcat` → `productcategory`
- `productcategory/addwarehouse` → `productcategory/warehouse`
- `productcategory/editwarehouse` → `productcategory/warehouse`
- `productgroups/add_group` → `productgroups`
- `productgroups/edit_group` → `productgroups`

### Locations Module
- `locations/create` → `locations`
- `locations/edit` → `locations`

### Accounts Module
- `accounts/addacc` → `accounts`
- `accounts/editacc` → `accounts`

### Transactions Module
- `transactions/create_trans` → `employee/payroll` (Payroll transaction)
- `transactions/new_trans` → `transactions`
- `transactions/edit_trans` → `transactions`
- `transactions/save_createcat` → `transactions/categories`
- `transactions/editcatsave` → `transactions/categories`

### Units Module
- `units/create` → `units`
- `units/create_va` → `units`
- `units/create_vb` → `units`
- `units/edit` → `units`

### Settings Module
- `settings/add_term` → `settings/terms`
- `settings/edit_term` → `settings/terms`
- `settings/add_custom_field` → `settings/customfields`
- `settings/edit_custom_field` → `settings/customfields`
- `settings/taxslabs_new` → `settings/tax`
- `settings/taxslabs_edit` → `settings/tax`

### Payment Gateway Module
- `paymentgateways/add_currency` → `paymentgateways`
- `paymentgateways/edit_currency` → `paymentgateways`
- `paymentgateways/add_bank_ac` → `paymentgateways`
- `paymentgateways/edit_bank_ac` → `paymentgateways`
- `paymentgateways/edit` → `paymentgateways`

### Printer Module
- `printer/add` → `printer`
- `printer/edit` → `printer`

### Projects Module
- `projects/addproject` → `projects`
- `projects/edit` → `projects`
- `projects/addactivity` → `projects`
- `projects/addmilestone` → `projects`
- `projects/save_addtask` → `projects`
- `projects/edittask` → `projects`

### Manager Module
- `manager/addactivity` → `manager`
- `manager/addmilestone` → `manager`
- `manager/save_addtask` → `manager`
- `manager/edittask` → `manager`

### Promo Module
- `promo/create` → `promo`
- `promo/edit` → `promo`

### Register Module
- `register/create` → `register`
- `register/edit` → `register`

### Tools Module
- `tools/addnote` → `tools`
- `tools/editnote` → `tools`
- `tools/save_addtask` → `tools`
- `tools/edittask` → `tools`

### Templates Module
- `templates/email_update` → `templates`
- `templates/sms_update` → `templates`

### Invoices Module
- `invoices/editaction` → `invoices`
- `invoices/editaction2` → `invoices`
- `invoices/editaction3` → `invoices`
- `invoices/editaction4` → `invoices`
- `invoices/editaction_credit_invoice` → `invoices/creditnotes`

### Stock Return Module
- `stockreturn/action` → `stockreturn`
- `stockreturn/editaction` → `stockreturn`

### Subscriptions Module
- `subscriptions/action` → `subscriptions`
- `subscriptions/editaction` → `subscriptions`

### Quotes Module
- `quote/action` → `quote`
- `quote/editaction` → `quote`

### POS Module
- `pos_invoices/action` → `pos_invoices`
- `pos_invoices/editaction` → `pos_invoices`

### Barcode Module
- `barcode/create` → `barcode`
- `barcode/edit` → `barcode`

### Tickets Module
- `tickets/add` → `tickets`
- `tickets/edit` → `tickets`

### Events Module
- `events/create` → `events`
- `events/edit` → `events`

## How to Add New Redirects

To add a new redirect mapping:

1. Open `assets/myjs/control__1scr.js`
2. Find the `redirectMap` object (around line 1448)
3. Add your new mapping:
   ```javascript
   'module/action': 'module/destination',
   ```

Example:
```javascript
'newmodule/create': 'newmodule',
'newmodule/edit': 'newmodule',
```

## Benefits

1. **Consistent UX**: Users are always redirected to the appropriate list page
2. **Easy Maintenance**: All redirects in one place
3. **Scalable**: Simple to add new modules
4. **Well-Organized**: Grouped by module for clarity
5. **Documented**: Comments explain each section

## Total Coverage

**150+ redirect mappings** covering **25+ modules**

## Testing

After making changes:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Test creating a record in any module
3. Verify redirect to correct index page
4. Test editing a record
5. Verify redirect works correctly

## Notes

- The purchase module redirects to the main purchase list (`purchase`) after create/edit
- Employee transactions redirect to the payroll page
- Invoice credit notes redirect to the credit notes list
- All other modules redirect to their main index page





