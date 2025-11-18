<link rel="stylesheet" type="text/css"
      href="<?= assets_url() ?>app-assets/<?= LTR ?>/core/menu/menu-types/horizontal-menu.css">
      <?php
 if ($this->aauth->premission(13)) { ?>
          <script src="https://apps.elfsight.com/p/platform.js" defer></script>
 <?php } ?>

     
</head>
<body class="horizontal-layout horizontal-menu 2-columns menu-expanded" data-open="click" data-menu="horizontal-menu"
      data-col="2-columns">
<span id="hdata"
      data-df="<?php echo $this->config->item('dformat2'); ?>"
      data-curr="<?php echo currency($this->aauth->get_user()->loc); ?>"></span>
 

 
<!-- Horizontal navigation-->
<div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-light navbar-without-dd-arrow navbar-shadow menu-border"
     role="navigation" data-menu="menu-wrapper">
    <!-- Horizontal menu content-->
    <div class="navbar-container main-menu-content" data-menu="menu-container">
    
    <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">


<?php
 if ($this->aauth->get_user()->totp_secret != '10') { ?>
 <?php
 //}else{

  //   redirct('/purchase');
 }
 //print_r($this->aauth->get_user());
            if ($this->aauth->get_user()->totp_secret == '10') { ?>


                            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="icon-basket-loaded"></i><span><?php echo 'Stock'; ?></span></a>
                    <ul class="dropdown-menu">
                       
                         <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                         
                       <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>purchase"
                                                    data-toggle="dropdown">Pucrhase Orders</a>
                                </li>
                        
                    
                    </ul>
                </li>
               
                 
            <?php 
            } 
 ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url(); ?>dashboard/"><i
                            class="icon-speedometer"></i><span><?= $this->lang->line('Dashboard') ?></span></a>

            </li>
            <?php 
            if ($this->aauth->premission(1)) { ?>
                               <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i class="ft-list"></i><span><?php echo 'Sale'; ?></span></a>
                    <ul class="dropdown-menu">
                        <?php /**<li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-paper-plane"></i><?php echo $this->lang->line('pos sales') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>pos_invoices/create"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('New Invoice'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>pos_invoices/create?v2=true"
                                                    data-toggle="dropdown"><?= $this->lang->line('New Invoice'); ?>
                                        V2 - Mobile</a>
                                </li>
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>pos_invoices"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Manage Invoices'); ?></a>
                                </li>

                            </ul>
                        </li> */?>
                         <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                         
                       <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>invoices/create"
                                                    data-toggle="dropdown"><i class="ft-list"></i><?php echo $this->lang->line('New Invoice'); ?></a>
                                </li>

                                

                        <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>invoices/unposted_invoices"
                                                    data-toggle="dropdown"><i class="ft-list"></i>Unposted Invoices</a>
                            </li>
                              
                        <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>invoices"
                                                    data-toggle="dropdown"><i class="ft-list"></i>Posted Invoices</a>
                                                    </li>
                         <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>invoices/print_invoices"
                                                    data-toggle="dropdown"><i class="ft-list"></i>Print Invoices</a>
                                                    </li>

                                  <?php          
                                    if ($this->aauth->premission(10)) {
                                ?>
                                                    <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>reports/print_customer_invoices"
                                                    data-toggle="dropdown"><i class="ft-list"></i>Customer Invoices</a>
                                                    </li>
                                <?php } ?>
                                                    
                        

                       <?php /**  <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-radio"></i><?php echo $this->lang->line('Subscriptions') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>subscriptions/create"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('New Subscription'); ?></a>
                                </li>

                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>subscriptions"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Subscriptions'); ?></a>
                            </ul>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>stockreturn/creditnotes"><i
                                        class="icon-screen-tablet"></i><?php echo $this->lang->line('Credit Notes'); ?>
                            </a>
                        </li>*/ ?>
                    </ul>
                </li>
               
            <?php }
            if ($this->aauth->premission(2) && $this->aauth->get_user()->totp_secret != '10') { ?>
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="ft-layers"></i><span><?php echo $this->lang->line('Stock') ?></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-list"></i> <?php echo $this->lang->line('Items Manager') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>products/add"
                                                    data-toggle="dropdown"> <?php echo $this->lang->line('New Product'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>products"
                                                    data-toggle="dropdown"><?= $this->lang->line('Manage Products'); ?></a>
                                </li>


                            </ul>
                        </li>
                        <li data-menu=""><a class="dropdown-item"
                                            href="<?php echo base_url(); ?>productcategory"
                                            data-toggle="dropdown"><i
                                        class="ft-umbrella"></i><?php echo $this->lang->line('Product Categories'); ?>
                            </a>
                        </li>
                        <li data-menu=""><a class="dropdown-item"
                                            href="<?php echo base_url(); ?>productcategory/warehouse"
                                            data-toggle="dropdown"><i
                                        class="ft-sliders"></i><?php echo $this->lang->line('Warehouses'); ?></a>
                        </li>
                        <li data-menu=""><a class="dropdown-item"
                                            href="<?php echo base_url(); ?>products/stock_transfer"
                                            data-toggle="dropdown"><i
                                        class="ft-wind"></i><?php echo $this->lang->line('Stock Transfer'); ?></a>
                        </li>
                        </li>

                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-handbag"></i> <?php echo $this->lang->line('Purchase Order') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>purchase/create"
                                                    data-toggle="dropdown"> <?php echo $this->lang->line('New Order'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>purchase"
                                                    data-toggle="dropdown"><?= $this->lang->line('Manage Orders'); ?></a>
                                </li>
                                <li data-menu="">
                                    <a class="dropdown-item" data-toggle="dropdown" href="<?= base_url(); ?>purchase/verification"></i>Verify Orders</a>
                                </li>


                            </ul>
                        </li>

                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-puzzle"></i> <?php echo $this->lang->line('Stock Return') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>stockreturn"
                                                    data-toggle="dropdown"> <?php echo $this->lang->line('SuppliersRecords'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>stockreturn/customer_invoices"
                                                    data-toggle="dropdown">Customer Invoice</a>
                                </li>

                                 <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>stockreturn/customer"
                                                    data-toggle="dropdown">Customer Return Invoices</a>
                                </li>


                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-target"></i><?php echo $this->lang->line('Suppliers') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?= base_url(); ?>supplier/create"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('New Supplier'); ?></a>
                                </li>

                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>supplier"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Manage Suppliers'); ?></a>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-barcode"></i><?php echo $this->lang->line('ProductsLabel'); ?></a>
                            <ul class="dropdown-menu">


                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>products/custom_label"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('custom_label'); ?></a></li>
                                  <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>products/standard_label"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('standard_label'); ?></a></li>
                            </ul>
                        </li>

                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(3)) {
                ?>
               
                 <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                        class="ft-users"></i><span>Customer <?php //echo $this->lang->line('CRM') ?></span></a>
                    <ul class="dropdown-menu">
                        <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>customers/create"
                                                    data-toggle="dropdown"> <i
                                        class="fa fa-user"></i> New Customer <?php //echo $this->lang->line('New Client') ?></a>
                                </li>
                        
                          
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>customers"
                                                    data-toggle="dropdown">  <i
                                        class="ft-list"></i> Manage Customer <?php //= $this->lang->line('Manage Clients'); ?></a>
                                </li>
                                <?php
                                    if ($this->aauth->premission(5)) {
                                ?>
                                 <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/add"
                                                    data-toggle="dropdown"><i class="icon-wallet"></i><?= $this->lang->line('New Transaction'); ?> </a>
                                </li>
                                <?php } ?>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>customers/list_price"><i
                                        class="icon-grid"></i>Customer Price List</a>
                        </li>
                        <!-- li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="fa fa-ticket"></i><?php echo $this->lang->line('Support Tickets') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>tickets/?filter=unsolved"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('UnSolved') ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>tickets"
                                                    data-toggle="dropdown"><?= $this->lang->line('Manage Tickets'); ?></a>
                                </li>
                            </ul>
                        </li -->
                        <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>invoices/receipt"
                                                    data-toggle="dropdown"><i
                                        class="icon-book-open"></i> Customer Receipt</a>
                        <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>customers/activities"
                                                    data-toggle="dropdown"><i
                                        class="fa fa-money"></i> Customer Activities</a>
                        </li>
                    </ul>
                </li>

            <?php }
            if ($this->aauth->premission(4)) {
                ?>
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="icon-briefcase"></i><span><?= $this->lang->line('Project') ?></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-calendar"></i><?php echo $this->lang->line('Project Management') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>projects/addproject"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('New Project') ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>projects"
                                                    data-toggle="dropdown"><?= $this->lang->line('Manage Projects'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>tools/todo"><i
                                        class="icon-list"></i><?php echo $this->lang->line('To Do List'); ?></a>
                        </li>

                    </ul>
                </li>
            <?php }
            if (!$this->aauth->premission(4) && $this->aauth->premission(7)) {
                ?>
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="icon-briefcase"></i><span><?php echo $this->lang->line('Project') ?></span></a>
                    <ul class="dropdown-menu">
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>manager/projects"><i
                                        class="icon-calendar"></i><?php echo $this->lang->line('Manage Projects'); ?>
                            </a>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>manager/todo"><i
                                        class="icon-list"></i><?php echo $this->lang->line('To Do List'); ?></a>
                        </li>

                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(5)) {
                ?>
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="icon-calculator"></i><span><?= $this->lang->line('Accounts') ?></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-book-open"></i><?php echo $this->lang->line('Accounts') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>accounts"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Manage Accounts') ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>accounts/balancesheet"
                                                    data-toggle="dropdown"><?= $this->lang->line('BalanceSheet'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>reports/accountstatement"
                                                    data-toggle="dropdown"><?= $this->lang->line('Account Statements'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="icon-wallet"></i><?php echo $this->lang->line('Transactions') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>transactions"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('View Transactions') ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/add"
                                                    data-toggle="dropdown"><?= $this->lang->line('New Transaction'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/transfer"
                                                    data-toggle="dropdown"><?= $this->lang->line('New Transfer'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/income"
                                                    data-toggle="dropdown"><?= $this->lang->line('Income'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/expense"
                                                    data-toggle="dropdown"><?= $this->lang->line('Expense'); ?></a>
                                </li>
                                <?php 
                                if ($this->aauth->premission(3)) {
                                    ?>
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>customers"
                                                    data-toggle="dropdown"><?= $this->lang->line('Clients Transactions'); ?></a>
                                </li>
            <?php } ?>
                            </ul>
                        </li>
 <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/income"
                                                    data-toggle="dropdown"><i class="fa fa-money"></i><?= $this->lang->line('Income'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>transactions/expense"
                                                    data-toggle="dropdown"><i class="ft-external-link"></i><?= $this->lang->line('Expense'); ?></a>
                                </li>
                    </ul>
                </li>

               

            <?php }
            if ($this->aauth->premission(10)) {
                ?>
       
       
    <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="icon-briefcase"></i><span><?php echo 'Sales Reports'; ?></span></a>
                    <ul class="dropdown-menu">
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>invoices/extended"><i
                                     data-toggle="dropdown"></i><?php echo 'Sales By Product (Summary)'; ?>
                            </a>
                        </li>
                          <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>stockreturn/stock_report"><i
                                     data-toggle="dropdown"></i><?php echo 'Sales By Product'; ?>
                            </a>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>pos_invoices/extended"><i
                                data-toggle="dropdown"></i><?php echo 'Daily Breakdown Sheet'; ?></a>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>invoices/daybook"><i
                                data-toggle="dropdown"></i>Day Books</a>
                        </li>
                         <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>invoices/payments_report"><i
                                data-toggle="dropdown"></i>Payments Recieved Summary</a>
                        </li>
                          <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>stockreturn/stock_return_report"><i
                                data-toggle="dropdown"></i>Stock Return Report</a>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>customers/customer_report"><i
                                data-toggle="dropdown"></i>Customer Report</a>
                        </li>
                         <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>customers/customer_balance"><i
                                data-toggle="dropdown"></i>All Customer Balance Report</a>
                        </li>
                         <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>reports/proftloss"><i
                                data-toggle="dropdown"></i>Profit&Loss</a>
                        </li>
                         
                         <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>invoices/geofence"><i
                                data-toggle="dropdown"></i><?php echo 'Zone Report'; ?></a>
                        </li>

                         <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>chart/product_cat"
                                                    data-toggle="dropdown"><?= $this->lang->line('Product Categories'); ?> Graphical</a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>chart/trending_products"
                                                    data-toggle="dropdown"><?= $this->lang->line('Trending Products'); ?> Graph</a>
                                </li>
                               
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>chart/topcustomers"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Top_Customers') ?> Graph</a>
                                </li> 

                               <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>reports/sales"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Sales') ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>reports/products"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Products') ?></a>
                                </li>     

                    </ul>
                </li>  
            

            <?php }
            if ($this->aauth->premission(6)) {
                ?>
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="icon-note"></i><span><?php echo $this->lang->line('Miscellaneous') ?></span></a>
                    <ul class="dropdown-menu">
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>tools/notes"><i
                                        class="icon-note"></i>Invoice Notes</a>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>events"><i
                                        class="icon-calendar"></i><?php echo $this->lang->line('Calendar'); ?></a>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>tools/documents"><i
                                        class="icon-doc"></i><?php echo $this->lang->line('Documents'); ?></a>
                        </li>


                    </ul>
                </li>
            <?php }
            if ($this->aauth->premission(9)) {
                ?>
              
                           <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#"
                                                                      data-toggle="dropdown"><i
                                class="ft-file-text"></i><span><?php //echo $this->lang->line('HRM') ?> Users</span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a
                                    class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i
                                        class="ft-users"></i><?php echo $this->lang->line('Employees') ?></a>
                            <ul class="dropdown-menu">
                                <li data-menu=""><a class="dropdown-item" href="<?php echo base_url(); ?>employee"
                                                    data-toggle="dropdown"><?php echo $this->lang->line('Employees') ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>employee/permissions"
                                                    data-toggle="dropdown"><?= $this->lang->line('Permissions'); ?></a>
                                </li>
                               <?php /** <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>employee/salaries"
                                                    data-toggle="dropdown"><?= $this->lang->line('Salaries'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>employee/attendances"
                                                    data-toggle="dropdown"><?= $this->lang->line('Attendance'); ?></a>
                                </li>
                                <li data-menu=""><a class="dropdown-item"
                                                    href="<?php echo base_url(); ?>employee/holidays"
                                                    data-toggle="dropdown"><?= $this->lang->line('Holidays'); ?></a>
                                </li> */?>
                            </ul>
                        </li>
                        <li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>employee/departments"><i
                                        class="icon-folder"></i><?php echo $this->lang->line('Departments'); ?></a>
                        </li>
                        <!--li data-menu="">
                            <a class="dropdown-item" href="<?php echo base_url(); ?>employee/payroll"><i
                                        class="icon-notebook"></i><?php echo $this->lang->line('Payroll'); ?></a>
                        </li-->

                    </ul>
                </li>


            <?php }
            // if ($this->aauth->get_user()->roleid > 4 && $this->aauth->get_user()->id != '29') {
                if($this->aauth->premission(17)) {
                ?>
                <li class="dropdown mega-dropdown nav-item" data-menu="megamenu"><a class="dropdown-toggle nav-link"
                                                                                    href="#" data-toggle="dropdown"><i
                                class="ft-bar-chart-2"></i><span><?php echo $this->lang->line('Export_Import'); ?></span></a>
                    <ul class="mega-dropdown-menu dropdown-menu row">
                        <li class="col-md-4" data-mega-col="col-md-3">
                            <ul class="drilldown-menu">
                                <li class="menu-list">
                                    <ul class="mega-menu-sub">
                                        <li><a class="dropdown-item" href="<?php echo base_url(); ?>export/crm"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Export People Data'); ?>
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item"
                                               href="<?php echo base_url(); ?>export/transactions"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Export Transactions'); ?>
                                            </a></li>
                                        <li><a class="dropdown-item" href="<?php echo base_url(); ?>export/products"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Export Products'); ?>
                                            </a></li>

                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="col-md-4" data-mega-col="col-md-3">
                            <ul class="drilldown-menu">
                                <li class="menu-list">
                                    <ul class="mega-menu-sub">
                                        <li><a class="dropdown-item" href="<?php echo base_url(); ?>export/account"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Account Statements'); ?>
                                            </a></li>
                                        <li><a class="dropdown-item"
                                               href="<?php echo base_url(); ?>export/taxstatement"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Tax_Export'); ?>
                                            </a></li>
                                        <li><a class="dropdown-item" href="<?php echo base_url(); ?>export/dbexport"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Database Backup'); ?>
                                            </a></li>
                                            
                                  <?php          
                                    if ($this->aauth->premission(19)) {
                                ?>
                                                <li><a  class="dropdown-item" href="<?php echo base_url(); ?>invoices/bulk_delete"><i
                                    class="fa fa-caret-right"></i> Bulk Deletion
                                </a></li>
                                <?php } ?>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="col-md-4" data-mega-col="col-md-3">
                            <ul class="drilldown-menu">
                                <li class="menu-list">
                                    <ul class="mega-menu-sub">
                                        <li><a class="dropdown-item" href="<?php echo base_url(); ?>import/products"><i
                                                        class="fa fa-caret-right"></i></i><?php echo $this->lang->line('Import Products'); ?>
                                            </a></li>
                                        <li><a class="dropdown-item" href="<?php echo base_url(); ?>import/customers"><i
                                                        class="fa fa-caret-right"></i><?php echo $this->lang->line('Import Customers'); ?>
                                            </a></li>
                                              <li><a  class="dropdown-item" href="<?php echo base_url(); ?>export/people_products"><i
                                    class="fa fa-caret-right"></i> <?php echo $this->lang->line('ProductsAccount Statements'); ?>
                        </a></li>
                         <li><a  class="dropdown-item" href="<?php echo base_url(); ?>settings/logdata"><i
                                    class="fa fa-caret-right"></i> Log Data
                        </a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </li>
            <?php }
            ?>

             <li class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link"
                                                                   href="#" data-toggle="dropdown"><span
                                    class="avatar avatar-online"><img
                                        src="<?php echo base_url('userfiles/employee/thumbnail/' . $this->aauth->get_user()->picture) ?>"
                                        alt="avatar"><i></i></span><span
                                    class="user-name"><?php echo $this->lang->line('Account') ?></span></a>
                        <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item"
                                                                          href="<?php echo base_url(); ?>user/profile"><i
                                        class="ft-user"></i> <?php echo $this->lang->line('Profile') ?></a>
                            <a href="<?php echo base_url(); ?>user/attendance"
                               class="dropdown-item"><i
                                        class="fa fa-list-ol"></i><?php echo $this->lang->line('Attendance') ?></a>
                            <a href="<?php echo base_url(); ?>user/holidays"
                               class="dropdown-item"><i
                                        class="fa fa-hotel"></i><?php echo $this->lang->line('Holidays') ?></a>

                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('user/logout'); ?>"><i
                                        class="ft-power"></i> <?php echo $this->lang->line('Logout') ?></a>
                        </div>
                    </li>

<!--
<li class="nav-item">            <a class=" nav-link " href="<?php echo base_url('user/logout'); ?>"><i
                                        class="ft-power"></i> <?php echo $this->lang->line('Logout') ?></a> </li> -->

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