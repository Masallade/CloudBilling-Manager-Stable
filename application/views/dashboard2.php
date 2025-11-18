<script type="text/javascript">
    var dataVisits = [
        <?php $tt_inc = 0;
        foreach ($incomechart as $row) {
            $tt_inc += $row['total'];
            echo "{ x: '" . $row['date'] . "', y: " . intval(amountExchange_s($row['total'], 0, $this->aauth->get_user()->loc)) . "},";
        }
        ?>
    ];
    var dataVisits2 = [
        <?php $tt_exp = 0;
        foreach ($expensechart as $row) {
            $tt_exp += $row['total'];
            echo "{ x: '" . $row['date'] . "', y: " . intval(amountExchange_s($row['total'], 0, $this->aauth->get_user()->loc)) . "},";
        }
        ?>
    ];
</script>

<div class="row px-1 first-row">
    <div class="elfsight-app-00af607e-5ca1-4309-9d98-553216dce90a"></div>
    <!-- <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
         <a href="<?php echo base_url(); ?>customers">

            <div class="card-content">
                <div class="media align-items-stretch">
                    <div class="p-2 text-center bg-warning bg-darken-2">
                        
                    </div>
                    <div class="p-1 media-body">
                        <h5 class="fw-bold"><?php echo $this->lang->line('TotalBalance'); ?> </h5>
                        <h5 class="text-bold-400 mb-0"> <?= amountExchange($ttlBalance, 0, $this->aauth->get_user()->loc); ?></h5>
                    </div>
                </div>
            </div> </a>
        </div>
    </div> -->
    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>invoices/extended">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-warning bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('TodaySales'); ?></h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-arrow-up"></i><?= amountExchange($todaysales, 0, $this->aauth->get_user()->loc) ?>
                            </h5>
                            <small><?= amountExchange($todaysalesvat, 0, $this->aauth->get_user()->loc) ?> (VAT)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>invoices/extended">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-warning bg-darken-2">

                        </div>
                        <div class="p-1  media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('TomorrowSales'); ?></h5>
                            <h5 class="text-bold-400 mb-0">
                                <i class="ft-arrow-up"></i><?= amountExchange($tomorrowsales, 0, $this->aauth->get_user()->loc) ?>
                            </h5>
                            <small><?= amountExchange($tomorrowsalesvat, 0, $this->aauth->get_user()->loc) ?> (VAT)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>invoices/extended">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-warning bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('ThisMonth'); ?> Sales</h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-arrow-up"></i><?= amountExchange($monthsales, 0, $this->aauth->get_user()->loc) ?>
                            </h5>
                            <small><?= amountExchange($monthsalesvat, 0, $this->aauth->get_user()->loc) ?> (VAT)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>invoices?filter=daily">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-primary bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"> <? echo $this->lang->line('TodayInvoices'); ?> </h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-plus"></i> <?= $todayin ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>invoices?filter=tomorrow">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-primary bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('Tomorrow'); ?> </h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-plus"></i> <?= $tomorrowin ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>invoices?filter=monthly">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-primary bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('ThisMonth'); ?></h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-arrow-up"></i><?= $monthin ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>


<div class="row mb-1">
    <div class="col-12">
        <div class="card-group">
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="primary" style="font-size: 1.14rem;"><?php echo $this->lang->line('TotalBalance'); ?></h3><?= '<span class=" font-medium-1 display-block"></span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($ttlBalance, 0, $this->aauth->get_user()->loc); ?></span>
                            </div>
                            <div class="media-right d-none media-middle">
                                <i class="fa fa-money primary font-large-2 float-right"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="primary" style="font-size: 1.14rem;"><? echo $this->lang->line('StockValues'); ?></h3><?= '<span class=" font-medium-1 display-block"></span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($warehouse_stock[0]['product_price'], 0, $this->aauth->get_user()->loc); ?></span>
                            </div>
                            <div class="media-right d-none media-middle">
                                <i class="fa fa-money primary font-large-2 float-right"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h4 class="red" style="font-size: 1.14rem;"><? echo $this->lang->line('StockSellingAmount'); ?></h4><?= '<span class=" font-medium-1 display-block"></span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($warehouse_stock[0]['fproduct_price'], 0, $this->aauth->get_user()->loc); ?></span>


                            </div>
                            <div class="media-right d-none media-middle">
                                <i class="fa fa-money red font-large-2 float-right"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">

                                <h3 class="blue" style="font-size: 1.14rem;"><? echo $this->lang->line('EstimatedProfit'); ?></h3><?= '<span class=" font-medium-1 display-block"></span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($warehouse_stock[0]['fproduct_price'] - $warehouse_stock[0]['product_price'], 0, $this->aauth->get_user()->loc); ?></span>

                            </div>
                            <div class="media-right d-none media-middle">
                                <i class="ft-external-link blue font-large-2 float-right"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="purple" style="font-size: 1.14rem;">.<? echo $this->lang->line('StockItemsQty'); ?></h3><?= '<span class=" font-medium-1 display-block"></span>'; ?>
                                <span class="font-medium-1"><? echo $warehouse_stock[0]['totalrec']; ?></span>
                            </div>
                            <div class="media-right d-none media-middle">
                                <i class="ft-inbox purple font-large-2 float-right"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row match-height">
    <div class="col-xl-8 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?php echo $this->lang->line('in_last _30') ?></h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div id="products-sales" class="height-300"></div>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body text-left w-100">
                                            <h3 class="primary"><?= amountExchange($todaysales, 0, $this->aauth->get_user()->loc) ?></h3>
                                            <span><?php echo $this->lang->line('today_income') ?></span>
                                        </div>

                                    </div>
                                    <div class="progress progress-sm mt-1 mb-0">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body text-left w-100">
                                            <h3 class="danger"><?= amountExchange($todayinexp['debit'], 0, $this->aauth->get_user()->loc) ?></h3>
                                            <span><?php echo $this->lang->line('today_expenses') ?></span>
                                        </div>

                                    </div>
                                    <div class="progress progress-sm mt-1 mb-0">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 40%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body text-left w-100">
                                            <h3 class="success"><?= amountExchange($todayprofit, 0, $this->aauth->get_user()->loc) ?></h3>
                                            <span><?php echo $this->lang->line('today_profit') ?></span>
                                        </div>

                                    </div>
                                    <div class="progress progress-sm mt-1 mb-0">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body text-left w-100">
                                            <h3 class="warning"> <?= amountExchange($todayinexp['credit'], 0, $this->aauth->get_user()->loc) ?></h3>
                                            <!--h3 class="warning"><?= amountExchange($tt_inc - $tt_exp, 0, $this->aauth->get_user()->loc) ?></h3 -->
                                            <span>Today Revenue</span>
                                        </div>

                                    </div>
                                    <div class="progress progress-sm mt-1 mb-0">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 35%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>






    <div class="col-xl-4 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?php echo $this->lang->line('RecentCustomers'); ?></h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-content px-1">
                <div id="recent-buyers" class="media-list height-450  mt-1 position-relative">
                    <?php
                    if (isset($recent_buy[0]['csd'])) {

                        foreach ($recent_buy as $item) {



                            echo '       <a href="' . base_url('customers/view?id=' . $item['csd']) . '" class="media border-0">
                        <div class="media-left pr-1">
                           
                            <i></i>
                            </span>
                        </div>
                        <div class="media-body w-100">
                                    
                            <h6 class="list-group-item-heading my-0"><span style="min-height: 18px; display: inline-block">' . $item['name'] . '</span> <span class="font-medium-4 float-right pt-1">' . amountExchange($item['total'], 0, $this->aauth->get_user()->loc) . '</span></h6>
                            <div class="text-right" style="margin-top: 24px;">' . amountExchange($item['tax'], 0, $this->aauth->get_user()->loc) . ' (VAT)</div>
                            <p class="list-group-item-text mb-0"><span class="badge  st-' . $item['status'] . '">' . $this->lang->line(ucwords($item['status'])) . '</span></p>
                        </div>
                    </a>';
                        }
                    } elseif ($recent_buy == 'sql') {
                        echo ' <div class="media-body w-100">  <h5 class="list-group-item-heading bg-danger white">Critical SQL Strict Mode Error: </h5>Please Disable Strict SQL Mode for in database  settings.</div>';
                    }

                    ?>


                </div>
                <br>
            </div>
        </div>
    </div>
</div>
<div class="row match-height">
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?php echo $this->lang->line('recent_invoices') ?></h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <p><span class="float-right"> <a href="<?php echo base_url() ?>invoices/create" class="btn btn-primary btn-sm rounded"><?php echo $this->lang->line('Add Sale') ?></a>
                            <a href="<?php echo base_url() ?>invoices" class="btn btn-success btn-sm rounded"><?php echo $this->lang->line('Manage Invoices') ?></a>

                    </p>
                </div>
            </div>
            <div class="card-content">

                <div class="table-responsive">
                    <table id="recent-orders" class="table table-hover mb-1">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('Invoices') ?>#</th>
                                <th><?php echo $this->lang->line('Customer') ?></th>
                                <th><?php echo $this->lang->line('Status') ?></th>
                                <th><?php echo $this->lang->line('Due') ?></th>
                                <th><?php echo $this->lang->line('Amount') ?></th>
                                <th><?php echo $this->lang->line('VAT') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            foreach ($recent as $item) {
                                $page = 'subscriptions';
                                $t = 'Sub ';
                                if ($item['i_class'] == 0) {
                                    $page = 'invoices';
                                    $t = '';
                                } elseif ($item['i_class'] == 1) {
                                    $page = 'pos_invoices';
                                    $t = 'POS ';
                                }
                                echo '    <tr>
                                <td class="text-truncate"><a href="' . base_url() . $page . '/view?id=' . $item['id'] . '">' . $t . '#' . $item['tid'] . '</a></td>
                             
                                <td class="text-truncate"> ' . $item['name'] . '</td>
                                <td class="text-truncate"><span class="badge  st-' . $item['status'] . ' st-' . $item['status'] . '">' . $this->lang->line(ucwords($item['status'])) . '</span></td><td class="text-truncate">' . dateformat($item['invoicedate']) . '</td>
                                <td class="text-truncate">' . amountExchange($item['total'], 0, $this->aauth->get_user()->loc) . '</td>
                                <td class="text-truncate">' . amountExchange($item['tax'], 0, $this->aauth->get_user()->loc) . '</td>
                            </tr>';
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row px-1 first-row">
    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>purchase/extended">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-warning bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('TodayPurchase'); ?></h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-arrow-up"></i><?= amountExchange($todaypurchase, 0, $this->aauth->get_user()->loc) ?>
                            </h5>
                            <small><?= amountExchange($todaypurchasevat, 0, $this->aauth->get_user()->loc) ?> (VAT)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>purchase/extended">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-warning bg-darken-2">

                        </div>
                        <div class="p-1  media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('TomorrowPurchase'); ?></h5>
                            <h5 class="text-bold-400 mb-0">
                                <i class="ft-arrow-up"></i><?= amountExchange($todaypurchase, 0, $this->aauth->get_user()->loc) ?>
                            </h5>
                            <small><?= amountExchange($todaypurchasevat, 0, $this->aauth->get_user()->loc) ?> (VAT)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>purchase/extended">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-warning bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('ThisMonthPurchase'); ?></h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-arrow-up"></i><?= amountExchange($monthpurchase, 0, $this->aauth->get_user()->loc) ?>
                            </h5>
                            <small><?= amountExchange($monthpurchasevat, 0, $this->aauth->get_user()->loc) ?> (VAT)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>purchase?filter=daily">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-primary bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"> <? echo $this->lang->line('TodayPurchases'); ?> </h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-plus"></i> <?= $todayinpurchase ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>purchase?filter=tomorrow">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-primary bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('Tomorrow'); ?> </h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-plus"></i> <?= $tomorrowinpurchase ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="col-xl-2 col-lg-6 col-12 p-0">
        <div class="card dashboard-cards">
            <a href="<?php echo base_url(); ?>purchase?filter=monthly">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-primary bg-darken-2">

                        </div>
                        <div class="p-1 media-body">
                            <h5 class="fw-bold"><? echo $this->lang->line('ThisMonth'); ?></h5>
                            <h5 class="text-bold-400 mb-0"><i class="ft-arrow-up"></i><?= $monthinpurchase ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row match-height">
    <div class="col-xl-8 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Recent Purchases</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <p><span class="float-right"> <a href="<?php echo base_url() ?>purchase/create" class="btn btn-primary btn-sm rounded"><?php echo $this->lang->line('Add') ?> Purchase</a>
                            <a href="<?php echo base_url() ?>purchase" class="btn btn-success btn-sm rounded"><?php echo $this->lang->line('Manage ') ?>Purchase</a>

                    </p>
                </div>
            </div>
            <div class="card-content">

                <div class="table-responsive">
                    <table id="recent-orders" class="table table-hover mb-1">
                        <thead>
                            <tr>
                                <th>Purchases#</th>
                                <th>Suppliers</th>
                                <th><?php echo $this->lang->line('Status') ?></th>
                                <th><?php echo $this->lang->line('Due') ?></th>
                                <th><?php echo $this->lang->line('Amount') ?></th>
                                <th><?php echo $this->lang->line('VAT') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            foreach ($recentpurchase as $item) {
                                $page = 'purchase';
                                $t = 'Purchase ';
                                echo '    <tr>
                                <td class="text-truncate"><a href="' . base_url() . $page . '/view?id=' . $item['id'] . '">' . $t . '#' . $item['tid'] . '</a></td>
                             
                                <td class="text-truncate"> ' . $item['name'] . '</td>
                                <td class="text-truncate"><span class="badge  st-' . $item['status'] . ' st-' . $item['status'] . '">' . $this->lang->line(ucwords($item['status'])) . '</span></td><td class="text-truncate">' . dateformat($item['invoicedate']) . '</td>
                                <td class="text-truncate">' . amountExchange($item['total'], 0, $this->aauth->get_user()->loc) . '</td>
                                <td class="text-truncate">' . amountExchange($item['tax'], 0, $this->aauth->get_user()->loc) . '</td>
                            </tr>';
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Recent Suppliers</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-content px-1">
                <div id="recent-buyers" class="media-list height-450  mt-1 position-relative" style="overflow: auto;" >
                    <?php
                    if (isset($recent_supplier[0]['csd'])) {

                        foreach ($recent_supplier as $item) {



                            echo '       <a href="' . base_url('supplier/view?id=' . $item['csd']) . '" class="media border-0">
                        <div class="media-left pr-1">
                           
                            <i></i>
                            </span>
                        </div>
                        <div class="media-body w-100">
                                    
                            <h6 class="list-group-item-heading my-0"><span style="min-height: 18px; display: inline-block">' . $item['name'] . '</span> <span class="font-medium-4 float-right pt-1">' . amountExchange($item['total'], 0, $this->aauth->get_user()->loc) . '</span></h6>
                            <div class="text-right" style="margin-top: 24px;">' . amountExchange($item['tax'], 0, $this->aauth->get_user()->loc) . ' (VAT)</div>
                            <p class="list-group-item-text mb-0"><span class="badge  st-' . $item['status'] . '">' . $this->lang->line(ucwords($item['status'])) . '</span></p>
                        </div>
                    </a>';
                        }
                    } elseif ($recent_supplier == 'sql') {
                        echo ' <div class="media-body w-100">  <h5 class="list-group-item-heading bg-danger white">Critical SQL Strict Mode Error: </h5>Please Disable Strict SQL Mode for in database  settings.</div>';
                    }

                    ?>


                </div>
                <br>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12">
        <div class="card-group">
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="primary"><?php $ipt = sprintf("%0.0f", ($tt_inc * 100) / $goals['income']); ?><?php echo ' ' . $ipt . '%' ?></h3><?= '<span class=" font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('income') . '</span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($tt_inc, 0, $this->aauth->get_user()->loc) . '/' . amountExchange($goals['income'], 0, $this->aauth->get_user()->loc) ?></span>
                            </div>
                            <div class="media-right media-middle">
                                <i class="fa fa-money primary font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $ipt ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="red"><?php $ipt = sprintf("%0.0f", ($tt_exp * 100) / $goals['expense']); ?><?php echo ' ' . $ipt . '%' ?></h3><?= '<span class="font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('expenses') . '</span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($tt_exp, 0, $this->aauth->get_user()->loc) . '/' . amountExchange($goals['expense'], 0, $this->aauth->get_user()->loc) ?></span>
                            </div>
                            <div class="media-right media-middle">
                                <i class="ft-external-link red font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $ipt ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="blue"><?php $ipt = sprintf("%0.0f", ($monthsales * 100) / $goals['sales']); ?><?php echo ' ' . $ipt . '%' ?></h3><?= '<span class="font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('sales') . '</span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($monthsales, 0, $this->aauth->get_user()->loc) . '/' . amountExchange($goals['sales'], 0, $this->aauth->get_user()->loc) ?></span>
                            </div>
                            <div class="media-right media-middle">
                                <i class="ft-flag blue font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0">
                            <div class="progress-bar bg-blue" role="progressbar" style="width: <?= $ipt ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card dashboard-cards">
                <div class="card-content">

                    <div class="card-body">
                        <div class="media">
                            <div class="media-body text-left w-100">
                                <h3 class="purple"><?php $ipt = sprintf("%0.0f", (($tt_inc - $tt_exp) * 100) / $goals['sales']); ?><?php echo ' ' . $ipt . '%' ?></h3><?= '<span class="font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('net_income') . '</span>'; ?>
                                <span class="font-medium-1"><?= amountExchange($tt_inc - $tt_exp, 0, $this->aauth->get_user()->loc) . '/' . amountExchange($goals['netincome'], 0, $this->aauth->get_user()->loc) ?></span>
                            </div>
                            <div class="media-right media-middle">
                                <i class="ft-inbox purple font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0">
                            <div class="progress-bar bg-purple" role="progressbar" style="width: <?= $ipt ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--<div class="row match-height">
    <div class="col-xl-7 col-lg-12">
        <div class="card" id="transactions">

            <div class="card-body">
                <h4><?php echo $this->lang->line('cashflow') ?></h4>
                <p><?php echo $this->lang->line('graphical_presentation') ?></p>
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1"
                           href="#sales"
                           aria-expanded="true"><?php echo $this->lang->line('income') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2"
                           href="#transactions1"
                           aria-expanded="false"><?php echo $this->lang->line('expenses') ?></a>
                    </li>


                </ul>
                <div class="tab-content pt-1">
                    <div role="tabpanel" class="tab-pane active" id="sales" aria-expanded="true"
                         data-toggle="tab">
                        <div id="dashboard-income-chart"></div>

                    </div>
                    <div class="tab-pane" id="transactions1" data-toggle="tab" aria-expanded="false">
                        <div id="dashboard-expense-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?php echo $this->lang->line('task_manager') . ' ' ?> <a
                            href="<?php echo base_url() ?>manager/todo"><i
                                class="icon-arrow-right deep-orange"></i></a></h4>
            </div>

            <div class="card-content">
                <div id="daily-activity">
                    <table class="table table-striped table-bordered base-style table-responsive" >
                        <thead>
                        <tr>
                            <th>

                            </th>

                            <th><?php echo $this->lang->line('Tasks') ?></th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $t = 0;
                        foreach ($tasks as $row) {
                            $name = '<a class="check text-default" data-id="' . $row['id'] . '" data-stat="Due"> <i class="fa fa-check"></i> </a><a href="#" data-id="' . $row['id'] . '" class="view_task"></a>';
                            if ($row['status'] == 'Done') {
                                $name = '<a class="check text-success" data-id="' . $row['id'] . '" data-stat="Done"> <i class="fa fa-check"></i> </a><a href="#" data-id="' . $row['id'] . '" class="view_task"></a>';
                            }

                            echo ' <tr>
                                <td class="text-truncate">
                                   ' . $name . '
                                </td>
                            
                                <td class="text-truncate">' . $row['name'] . '</td>
                                <td class="text-truncate"><span id="st' . $t . '" class="badge badge-default task_' . $row['status'] . '">' . $row['status'] . '</span></td>
                            </tr>';


                            $t++;
                        }
                        ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> -->
<div class="row match-height">
    <div class="col-xl-8 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?php echo $this->lang->line('recent') ?> <a href="<?php echo base_url() ?>transactions" class="btn btn-primary btn-sm rounded"><?php echo $this->lang->line('Transactions') ?></a>
                </h4>
                <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                        <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover mb-1">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('Date') ?>#</th>
                                <th><?php echo $this->lang->line('Account') ?></th>
                                <th><?php echo $this->lang->line('Debit') ?></th>
                                <th><?php echo $this->lang->line('Credit') ?></th>

                                <th><?php echo $this->lang->line('Method') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            foreach ($recent_payments as $item) {

                                echo '<tr>
                                <td class="text-truncate"><a href="' . base_url() . 'transactions/view?id=' . $item['id'] . '">' . dateformat($item['date']) . '</a></td>
                                <td class="text-truncate"> ' . $item['account'] . '</td>
                                <td class="text-truncate">' . amountExchange($item['debit'], 0, $this->aauth->get_user()->loc) . '</td>
                                <td class="text-truncate">' . amountExchange($item['credit'], 0, $this->aauth->get_user()->loc) . '</td>                    
                                <td class="text-truncate">' . $this->lang->line($item['method']) . '</td>
                            </tr>';
                            } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-12">
        <div class="card">
            <div class="card-header ">
                <h4 class="card-title"><?php echo $this->lang->line('Stock Alert') ?></h4>

            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">

                    <?php

                    foreach ($stock as $item) {
                        echo '<li class="list-group-item"><span class="badge badge-danger float-xs-right">' . +$item['qty'] . ' ' . $item['unit'] . '</span> <a href="' . base_url() . 'products/edit?id=' . $item['pid'] . '">' . $item['product_name'] . '  </a><small class="purple"> <i class="ft-map-pin"></i> ' . $item['title'] . '</small>
                                </li>';
                    } ?>

                </ul>

            </div>
        </div>
    </div>
</div>
<div class="row match-height">
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Recent Activities (Last 7 days)</h4>

            </div>
            <div class="card-body">
            <div class="table-responsive">
            <table id="crtstable" class="table table-striped table-bordered zero-configuration"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>
                                        <?php echo 'No'; ?>
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Date'); ?>
                                    </th>
                                    <th>
                                        <?php echo 'Ref'; ?>
                                    </th>
                                    <th>Customer</th>
                                    <th>Details</th>
                                    <th>
                                        <?php echo $this->lang->line('Debit'); ?> (GBP)
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Credit'); ?> (GBP)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>
                                        <?php echo 'No'; ?>
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Date'); ?>
                                    </th>
                                    <th>
                                        <?php echo 'Ref'; ?>
                                    </th>
                                    <th>Customer</th>
                                    <th>Details</th>
                                    <th>
                                        <?php echo $this->lang->line('Debit'); ?> (GBP)
                                    </th>
                                    <th>
                                        <?php echo $this->lang->line('Credit'); ?> (GBP)
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(window).on("load", function() {
        $('#recent-buyers').perfectScrollbar({
            wheelPropagation: true
        });
        /********************************************
         *               PRODUCTS SALES              *
         ********************************************/
        var sales_data = [
            <?php foreach ($countmonthlychart as $row) {
                echo "{ y: '" . $row['date'] . "', sales: " . intval(amountExchange_s($row['total'], 0, $this->aauth->get_user()->loc)) . ", invoices: " . intval($row['ttlid']) . "},";
            } ?>
        ];
        var months = ["<?= lang('Jan') ?>", "<?= lang('Feb') ?>", "<?= lang('Mar') ?>", "<?= lang('Apr') ?>", "<?= lang('May') ?>", "<?= lang('Jun') ?>", "<?= lang('Jul') ?>", "<?= lang('Aug') ?>", "<?= lang('Sep') ?>", "<?= lang('Oct') ?>", "<?= lang('Nov') ?>", "<?= lang('Dec') ?>"];
        Morris.Area({
            element: 'products-sales',
            data: sales_data,
            xkey: 'y',
            ykeys: ['sales', 'invoices'],
            labels: ['sales', 'invoices'],
            behaveLikeLine: true,
            xLabelFormat: function(x) { // <--- x.getMonth() returns valid index
                var day = x.getDate();
                var month = months[x.getMonth()];
                return day + ' ' + month;
            },
            resize: true,
            pointSize: 0,
            pointStrokeColors: ['#00B5B8', '#FA8E57', '#F25E75'],
            smooth: true,
            gridLineColor: '#E4E7ED',
            numLines: 6,
            gridtextSize: 14,
            lineWidth: 0,
            fillOpacity: 0.9,
            hideHover: 'auto',
            lineColors: ['#00B5B8', '#F25E75']
        });


    });
</script>
<script type="text/javascript">
    function drawIncomeChart(dataVisits) {
        $('#dashboard-income-chart').empty();
        Morris.Area({
            element: 'dashboard-income-chart',
            data: dataVisits,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 40',
            labels: ['<?php echo $this->lang->line('Amount') ?>'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) {
                    return y;
                } else {
                    return '';
                }
            },
            resize: true,
            lineColors: [
                '#00A5A8',
            ],
            pointFillColors: [
                '#00A5A8',
            ],
            fillOpacity: 0.4,
        });
    }

    function drawExpenseChart(dataVisits2) {

        $('#dashboard-expense-chart').empty();
        Morris.Area({
            element: 'dashboard-expense-chart',
            data: dataVisits2,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 0',
            labels: ['<?php echo $this->lang->line('Amount') ?>'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) {
                    return y;
                } else {
                    return '';
                }
            },
            resize: true,
            lineColors: [
                '#ff6e40',
            ],
            pointFillColors: [
                '#34cea7',
            ]
        });
    }
    drawIncomeChart(dataVisits);
    drawExpenseChart(dataVisits2);
    $('#dashboard-sales-breakdown-chart').empty();
    Morris.Donut({
        element: 'dashboard-sales-breakdown-chart',
        data: [{
                label: "<?php echo $this->lang->line('Income') ?>",
                value: <?= intval(amountExchange_s($tt_inc, 0, $this->aauth->get_user()->loc)); ?>
            },
            {
                label: "<?php echo $this->lang->line('Expenses') ?>",
                value: <?= intval(amountExchange_s($tt_exp, 0, $this->aauth->get_user()->loc)); ?>
            }
        ],
        resize: true,
        colors: ['#34cea7', '#ff6e40'],
        gridTextSize: 6,
        gridTextWeight: 400
    });
    $('a[data-toggle=tab').on('shown.bs.tab', function(e) {
        window.dispatchEvent(new Event('resize'));
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
    $('#crtstable').DataTable({
                "bPaginate": true,
                "bFilter": true,
                "bInfo": false,
                "bSortable": false,
                "bRetrieve": true,
                "pageLength": 50,
                aoColumnDefs: [
                    { "aTargets": [0], "bSortable": false },
                    { "aTargets": [1], "bSortable": false },
                    { "aTargets": [2], "bSortable": false },
                    { "aTargets": [3], "bSortable": false }
                ],
                "processing": true, //Feature control the processing indicator.
                "serverSide": false, //Feature control DataTables' server-side processing mode.
                "order": [],
                responsive: true,
                <?php datatable_lang();?>
                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo site_url('customers/activitylist'); ?>",
                    "type": "POST",
                    "data": {
                        '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                        'start': formatDate(new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)), // current date in "DD-MM-YYYY" format
                        'end': formatDate(new Date()),   // current date in "DD-MM-YYYY" format
                    }
                },
                //Set column definition initialisation properties.
                "columnDefs": [
                    {
                        "targets": [0], //first column / numbering column
                        "orderable": true, //set not orderable
                    },
                    {
                        "targets": [1], // second column / "date" column
                        "orderable": false, // set orderable
                        "orderData": [1], // specify the index of the column to sort
                        "orderSequence": ["DESC"] // specify the sorting order ("asc" for ascending)
                    },
                ],
            });
            });
            function formatDate(date) {
                        const day = ('0' + date.getDate()).slice(-2);
                        const month = ('0' + (date.getMonth() + 1)).slice(-2);
                        const year = date.getFullYear();
                        
                        return `${day}-${month}-${year}`;
                    }
</script>