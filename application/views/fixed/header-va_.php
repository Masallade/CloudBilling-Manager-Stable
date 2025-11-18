<link rel="stylesheet" type="text/css"
      href="<?= assets_url() ?>app-assets/<?= LTR ?>/core/menu/menu-types/vertical-menu-modern.css">
</head>
<body class="vertical-layout vertical-menu-modern 2-columns   menu-expanded fixed-navbar" data-open="click"
      data-menu="vertical-menu-modern" data-col="2-columns">
<span id="hdata"
      data-df="<?php echo $this->config->item('dformat2'); ?>"
      data-curr="<?php echo currency($this->aauth->get_user()->loc); ?>"></span>
 
<!-- ////////////////////////////////////////////////////////////////////////////-->
<!-- Horizontal navigation-->
<div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
    <!-- Horizontal menu content-->
    <div class="main-menu-content">

        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <?php if ($this->aauth->get_user()->roleid == 5) { ?>
                <li class="p-1"><select class="form-control"
                                        onchange="javascript:location.href = baseurl+'settings/switch_location?id='+this.value;"> <?php
                        $loc = location($this->aauth->get_user()->loc);
                        echo ' <option value="' . $loc['id'] . '"> *' . $loc['cname'] . '*</option>';

                        $loc = locations();
                        foreach ($loc as $row) {
                            echo ' <option value="' . $row['id'] . '"> ' . $row['cname'] . '</option>';
                        }
                        echo ' <option value="0">Master/Default</option>';
                        ?></select>
                </li> <?php } ?>
                <li class="nav-item "><a href="<?= base_url(); ?>dashboard/"><i
                                class="icon-speedometer"></i><span><?= $this->lang->line('Dashboard') ?></span></a>

                </li>
            <?php
            if ($this->aauth->premission(1)) { ?>
                <li class="nav-item has-sub <?php if ($this->li_a == "sales") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-basket-loaded"></i><span><?php echo $this->lang->line('sales') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item"><a
                                    href="#"><i
                                        class="icon-paper-plane"></i><?php echo $this->lang->line('pos sales') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a href="<?= base_url(); ?>pos_invoices/create"
                                    ><?php echo $this->lang->line('New Invoice'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>pos_invoices/create?v2=true"><?= $this->lang->line('New Invoice'); ?>
                                        V2 - Mobile</a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>pos_invoices"><?php echo $this->lang->line('Manage Invoices'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item"><a href="#"><i
                                        class="icon-basket"></i><?php echo $this->lang->line('sales') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a href="<?= base_url(); ?>invoices/create"
                                                         data-toggle="dropdown"><?php echo $this->lang->line('New Invoice'); ?></a>
                                </li>

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>invoices"><?php echo $this->lang->line('Manage Invoices'); ?></a>
                            </ul>
                        </li>
                        <li class="menu-item"><a href="#"><i
                                        class="icon-call-out"></i><?php echo $this->lang->line('Quotes') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?= base_url(); ?>quote/create"><?php echo $this->lang->line('New Quote'); ?></a>
                                </li>

                                <li class="menu-item"><a class="dropdown-item" href="<?php echo base_url(); ?>quote"
                                                         data-toggle="dropdown"><?php echo $this->lang->line('Manage Quotes'); ?></a>
                            </ul>
                        </li>

                        <li class="menu-item"><a href="#"><i
                                        class="ft-radio"></i><?php echo $this->lang->line('Subscriptions') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?= base_url(); ?>subscriptions/create"><?php echo $this->lang->line('New Subscription'); ?></a>
                                </li>

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>subscriptions"><?php echo $this->lang->line('Subscriptions'); ?></a>
                            </ul>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>stockreturn/creditnotes"><i
                                        class="icon-screen-tablet"></i><?php echo $this->lang->line('Credit Notes'); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(2)) { ?>
                <li class="nav-item has-sub <?php if ($this->li_a == "stock") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="ft-layers"></i><span><?php echo $this->lang->line('Stock') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item"><a
                                    href="#"><i
                                        class="ft-list"></i> <?php echo $this->lang->line('Items Manager') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?= base_url(); ?>products/add"> <?php echo $this->lang->line('New Product'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>products"><?= $this->lang->line('Manage Products'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item"><a href="<?php echo base_url(); ?>productcategory"><i
                                        class="ft-umbrella"></i><?php echo $this->lang->line('Product Categories'); ?>
                            </a>
                        </li>
                        <li class="menu-item"><a href="<?php echo base_url(); ?>productcategory/warehouse"><i
                                        class="ft-sliders"></i><?php echo $this->lang->line('Warehouses'); ?></a>
                        </li>
                        <li class="menu-item"><a class="dropdown-item"
                                                 href="<?php echo base_url(); ?>products/stock_transfer"><i
                                        class="ft-wind"></i><?php echo $this->lang->line('Stock Transfer'); ?></a>
                        </li>
                        </li>

                        <li class="menu-item"><a href="#"><i
                                        class="icon-handbag"></i> <?php echo $this->lang->line('Purchase Order') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a class="dropdown-item" href="<?= base_url(); ?>purchase/create"
                                                         data-toggle="dropdown"> <?php echo $this->lang->line('New Order'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>purchase"><?= $this->lang->line('Manage Orders'); ?></a>
                                </li>


                            </ul>
                        </li>

                        <li class="menu-item"><a href="#"><i
                                        class="icon-puzzle"></i> <?php echo $this->lang->line('Stock Return') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?= base_url(); ?>stockreturn"> <?php echo $this->lang->line('SuppliersRecords'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>stockreturn/customer"><?php echo $this->lang->line('CustomersRecords'); ?></a>
                                </li>


                            </ul>
                        </li>
                        <li class="menu-item"><a href="#"><i
                                        class="ft-target"></i><?php echo $this->lang->line('Suppliers') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?= base_url(); ?>supplier/create"><?php echo $this->lang->line('New Supplier'); ?></a>
                                </li>

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>supplier"><?php echo $this->lang->line('Manage Suppliers'); ?></a>
                            </ul>
                        </li>
                           <li class="menu-item" ><a
                                  href="#"><i
                                        class="fa fa-barcode"></i><?php echo $this->lang->line('ProductsLabel'); ?></a>
                            <ul class="menu-content">


                                <li  class="menu-item"><a href="<?php echo base_url(); ?>products/custom_label"
                                                   ><?php echo $this->lang->line('custom_label'); ?></a></li>
                                  <li  class="menu-item"><a href="<?php echo base_url(); ?>products/standard_label"
                                                ><?php echo $this->lang->line('standard_label'); ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(3)) {
                ?>
                <li class="nav-item has-sub <?php if ($this->li_a == "crm") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-diamond"></i> <span><?php echo $this->lang->line('CRM') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item"><a href="#"><i
                                        class="ft-users"></i> <?php echo $this->lang->line('Clients') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>customers/create"><?php echo $this->lang->line('New Client') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>customers"><?= $this->lang->line('Manage Clients'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>clientgroup"><i
                                        class="icon-grid"></i> <?php echo $this->lang->line('Client Groups'); ?></a>
                        </li>
                        <li class="menu-item"><a href="#"><i
                                        class="fa fa-ticket"></i> <?php echo $this->lang->line('Support Tickets') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>tickets/?filter=unsolved"><?php echo $this->lang->line('UnSolved') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>tickets"><?= $this->lang->line('Manage Tickets'); ?></a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(4)) {
                ?>
                <li class="menu-item  has-sub <?php if ($this->li_a == "project") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-briefcase"></i><span><?= $this->lang->line('Project') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item"><a href="#"><i
                                        class="icon-calendar"></i> <?php echo $this->lang->line('Project Management') ?>
                            </a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>projects/addproject"><?php echo $this->lang->line('New Project') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>projects"><?= $this->lang->line('Manage Projects'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>tools/todo"><i
                                        class="icon-list"></i> <?php echo $this->lang->line('To Do List'); ?></a>
                        </li>

                    </ul>
                </li>
            <?php }
            if (!$this->aauth->premission(4) && $this->aauth->premission(7)) {
                ?>
                <li class="menu-item has-sub <?php if ($this->li_a == "manager") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-briefcase"></i> <span><?php echo $this->lang->line('Project') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>manager/projects"><i
                                        class="icon-calendar"></i> <?php echo $this->lang->line('Manage Projects'); ?>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>manager/todo"><i
                                        class="icon-list"></i> <?php echo $this->lang->line('To Do List'); ?></a>
                        </li>

                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(5)) {
                ?>
                <li class="menu-item  has-sub <?php if ($this->li_a == "accounts") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-calculator"></i><span><?= $this->lang->line('Accounts') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item"><a href="#" data-toggle="dropdown"><i
                                        class="icon-book-open"></i> <?php echo $this->lang->line('Accounts') ?></a>
                            <ul class="menu-content">
                                <li data-menu=""><a href="<?php echo base_url(); ?>accounts"
                                    ><?php echo $this->lang->line('Manage Accounts') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>accounts/balancesheet"><?= $this->lang->line('BalanceSheet'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/accountstatement"><?= $this->lang->line('Account Statements'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item"><a href="#"><i
                                        class="icon-wallet"></i> <?php echo $this->lang->line('Transactions') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>transactions"><?php echo $this->lang->line('View Transactions') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>transactions/add"><?= $this->lang->line('New Transaction'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>transactions/transfer"><?= $this->lang->line('New Transfer'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>transactions/income"><?= $this->lang->line('Income'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>transactions/expense"><?= $this->lang->line('Expense'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>customers"><?= $this->lang->line('Clients Transactions'); ?></a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </li>

                <li class="menu-item  has-sub <?php if ($this->li_a == "promo") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-energy"></i> <span><?php echo $this->lang->line('Promo Codes') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item"><a href="#"><i
                                        class="icon-trophy"></i> <?php echo $this->lang->line('Coupons') ?></a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>promo/create"><?php echo $this->lang->line('New Promo') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>promo"><?= $this->lang->line('Manage Promo'); ?></a>
                                </li>
                            </ul>
                        </li>


                    </ul>
                </li>

            <?php }
            if ($this->aauth->premission(10)) {
                ?>
                <li class="menu-item  has-sub <?php if ($this->li_a == "data") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-pie-chart"></i>
                        <span><?php echo $this->lang->line('Data & Reports') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>register"><i
                                        class="icon-eyeglasses"></i> <?php echo $this->lang->line('Business Registers'); ?>
                            </a>
                        </li>

                        <li class="menu-item"><a href="#"><i
                                        class="icon-doc"></i> <?php echo $this->lang->line('Statements') ?></a>
                            <ul class="menu-content">

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/accountstatement"><?= $this->lang->line('Account Statements'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/customerstatement"><?php echo $this->lang->line('Customer_Account_Statements') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/supplierstatement"><?php echo $this->lang->line('Supplier_Account_Statements') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/taxstatement"><?php echo $this->lang->line('TAX_Statements'); ?></a>
                                </li>
                                   <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>pos_invoices/extended"
                                                          data-toggle="dropdown"><?php echo $this->lang->line('ProductSales'); ?></a></li>
                            </ul>
                        </li>

                        <li class="menu-item"><a href="#"><i
                                        class="icon-bar-chart"></i> <?php echo $this->lang->line('Graphical Reports') ?>
                            </a>
                            <ul class="menu-content">

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/product_cat"><?= $this->lang->line('Product Categories'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/trending_products"><?= $this->lang->line('Trending Products'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/profit"><?= $this->lang->line('Profit'); ?></a>
                                </li>

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/topcustomers"><?php echo $this->lang->line('Top_Customers') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/incvsexp"><?php echo $this->lang->line('income_vs_expenses') ?></a>
                                </li>

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/income"><?= $this->lang->line('Income'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>chart/expenses"><?= $this->lang->line('Expenses'); ?></a>


                            </ul>
                        </li>
                        <li class="menu-item"><a href="#"><i
                                        class="icon-bulb"></i> <?php echo $this->lang->line('Summary_Report') ?>
                            </a>
                            <ul class="menu-content">
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/statistics"><?php echo $this->lang->line('Statistics') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/profitstatement"><?= $this->lang->line('Profit'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/incomestatement"><?php echo $this->lang->line('Calculate Income'); ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/expensestatement"><?php echo $this->lang->line('Calculate Expenses') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/sales"><?php echo $this->lang->line('Sales') ?></a>
                                </li>
                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/products"><?php echo $this->lang->line('Products') ?></a>
                                </li>

                                <li class="menu-item"><a
                                            href="<?php echo base_url(); ?>reports/commission"><?= $this->lang->line('Employee'); ?> <?= $this->lang->line('Commission'); ?></a>
                                </li>

                            </ul>
                        </li>

                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(6)) {
                ?>
                <li class="menu-item  has-sub <?php if ($this->li_a == "misc") {
                    echo ' open';
                } ?>"><a href="#"><i
                                class="icon-note"></i><span><?php echo $this->lang->line('Miscellaneous') ?></span></a>
                    <ul class="menu-content">
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>tools/notes"><i
                                        class="icon-note"></i> <?php echo $this->lang->line('Notes'); ?></a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>events"><i
                                        class="icon-calendar"></i> <?php echo $this->lang->line('Calendar'); ?></a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>tools/documents"><i
                                        class="icon-doc"></i> <?php echo $this->lang->line('Documents'); ?></a>
                        </li>


                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(9)) {
                ?>
                <li class="menu-item  has-sub <?php if ($this->li_a == "emp") {
                    echo ' open';
                } ?>""><a href="#"><i
                            class="ft-file-text"></i><span><?php echo $this->lang->line('HRM') ?></span></a>
                <ul class="menu-content">
                    <li class="menu-item"><a href="#"><i
                                    class="ft-users"></i> <?php echo $this->lang->line('Employees') ?></a>
                        <ul class="menu-content">
                            <li class="menu-item"><a
                                        href="<?php echo base_url(); ?>employee"><?php echo $this->lang->line('Employees') ?></a>
                            </li>
                            <li class="menu-item"><a
                                        href="<?php echo base_url(); ?>employee/permissions"><?= $this->lang->line('Permissions'); ?></a>
                            </li>
                            <li class="menu-item"><a
                                        href="<?php echo base_url(); ?>employee/salaries"><?= $this->lang->line('Salaries'); ?></a>
                            </li>
                            <li class="menu-item"><a
                                        href="<?php echo base_url(); ?>employee/attendances"><?= $this->lang->line('Attendance'); ?></a>
                            </li>
                            <li class="menu-item"><a
                                        href="<?php echo base_url(); ?>employee/holidays"><?= $this->lang->line('Holidays'); ?></a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="<?php echo base_url(); ?>employee/departments"><i
                                    class="icon-folder"></i> <?php echo $this->lang->line('Departments'); ?></a>
                    </li>
                    <li class="menu-item">
                        <a href="<?php echo base_url(); ?>employee/payroll"><i
                                    class="icon-notebook"></i> <?php echo $this->lang->line('Payroll'); ?></a>
                    </li>

                </ul>
                </li>
            <?php }
            if ($this->aauth->get_user()->roleid > 4) {
                ?>
                <li class="menu-item   has-sub <?php if ($this->li_a == "export") {
                    echo ' open';
                } ?>""><a href="#"><i
                            class="ft-bar-chart-2"></i>
                    <span><?php echo $this->lang->line('Export_Import'); ?></span></a>
                <ul class="menu-content">
                    <li class="menu-item"><a href="<?php echo base_url(); ?>export/crm"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Export People Data'); ?>
                        </a>
                    </li>
                    <li class="menu-item"><a href="<?php echo base_url(); ?>export/transactions"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Export Transactions'); ?>
                        </a></li>
                    <li class="menu-item"><a href="<?php echo base_url(); ?>export/products"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Export Products'); ?>
                        </a></li>
                    <li><a href="<?php echo base_url(); ?>export/account"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Account Statements'); ?>
                        </a></li>
                    <li><a href="<?php echo base_url(); ?>export/people_products"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('ProductsAccount Statements'); ?>
                        </a></li>
                    <li><a
                                href="<?php echo base_url(); ?>export/taxstatement"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Tax_Export'); ?>
                        </a></li>
                    <li><a href="<?php echo base_url(); ?>export/dbexport"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Database Backup'); ?>
                        </a></li>
                    <li><a href="<?php echo base_url(); ?>import/products"><i
                                    class="fa fa-caret-right"></i></i> <?php echo $this->lang->line('Import Products'); ?>
                        </a></li>
                    <li><a href="<?php echo base_url(); ?>import/customers"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('Import Customers'); ?>
                        </a></li>
                    <li class="mt-1"></li>


                </ul>


                </li>
            <?php }
            ?>

        </ul>
    </div>
    <!-- /horizontal menu content-->
</div>
<!-- Horizontal navigation-->
<div id="c_body"></div>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">