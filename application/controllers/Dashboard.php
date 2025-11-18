<?php
/**
 * Geo POS -  Accounting,  Invoicing  and CRM Application
 * Copyright (c) Rajesh Dukiya. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
            exit;
        }
        if (!$this->aauth->permission_new(null, 'dashboard')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->model('dashboard_model');
        $this->load->model('User_model');
        $this->load->model('tools_model');


    }


    public function index()
    {
        $today = date("Y-m-d");
        $month = date("m");
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $tomorrow = date("Y-m-d",strtotime("+1 day"));
        $year = date("Y");
        if ($this->aauth->permission_new(null, 'dashboard')) {
            // Minimal data for fast page load - everything else loads via AJAX
            $data = [
                'totalUser' => 0,
                'totllsales' => 0,
                'yearsales' => 0,
                'todayin' => 0,
                'tomorrowin' => 0,
                'tomorrowsales' => 0,
                'tomorrowsalesvat' => 0,
                'incomechart' => [],
                'expensechart' => [],
                'countmonthlychart' => [],
                'monthin' => 0,
                'ttlBalance' => 0,
                'customerBalances' => [],
                'due_invoices' => [],
                'todaysales' => 0,
                'todaycost' => 0,
                'todayprofit' => 0,
                'yearpurchase' => 0,
                'yearpurchasevat' => 0,
                'totalPurchase' => 0,
                'todaypurchase' => 0,
                'totalPurchaseVAT' => 0,
                'tomorrowpurchase' => 0,
                'todaypurchasevat' => 0,
                'monthpurchase' => 0,
                'monthlyPurchaseCount' => 0,
                'monthpurchasevat' => 0,
                'todayinpurchase' => 0,
                'todayPurchaseCount' => 0,
                'totalPurchaseCount' => 0,
                'tomorrowinpurchase' => 0,
                'monthinpurchase' => 0,
                'todayPurchaseVerified' => 0,
                'todayPurchaseUnverified' => 0,
                'todayPurchaseVerifiedcount' => 0,
                'todayPurchaseUnverifiedcount' => 0,
                'todayPurchaseDue' => 0,
                'yesterday_received' => [],
                'month_received' => [],
                'year_received' => 0,
                'today_received' => 0,
                'yearsalesvat' => 0,
                'totllsalesvat' => 0,
                'todaysalesinc' => 0,
                'todaysalesincvat' => 0,
                'todaysalesvat' => 0,
                'monthsales' => 0,
                'monthsalesvat' => 0,
                'monthsalesincvat' => 0,
                'lastmonthsalesincvat' => 0,
                'todayinexp' => ['debit' => 0, 'credit' => 0],
                'allProductsInHand' => 0,
                'allItems' => 0,
                'productWillBeReceived' => 0,
                'stockedOutItems' => 0,
                'productCat' => 0,
                'totalCust' => 0,
                'totalSupp' => 0,
                'todayincome' => 0,
                'recent_payments' => [],
                'tasks' => [],
                'recent' => [],
                'recent_buy' => [],
                'goals' => ['income'=>1,'expense'=>1,'sales'=>1,'netincome'=>1],
                'stock' => [],
                'warehouse_stock' => [],
                'tt_inc' => 0,
                'tt_exp' => 0,
            ];
            
            // Ensure dashboard tables have data
            $data['recent'] = $this->dashboard_model->recentInvoices();
            $data['recent_payments'] = $this->dashboard_model->recent_payments();
            $data['stock'] = $this->dashboard_model->stock();
            // Populate Top Customers panel (server-rendered)
            try {
                $data['ttlBalance'] = $this->dashboard_model->CustomerBalance();
                $data['customerBalances'] = $this->dashboard_model->CustomerBalancedash();
            } catch (Throwable $e) {
                $data['ttlBalance'] = 0;
                $data['customerBalances'] = [];
            }

            // Due invoices calendar data for current month
            $monthStart = date('Y-m-01');
            $monthEnd   = date('Y-m-t');
            try {
                if (method_exists($this->User_model, 'get_due_invoices')) {
                    $data['due_invoices'] = $this->User_model->get_due_invoices($monthStart, $monthEnd);
                } else {
                    $data['due_invoices'] = $this->dashboard_model->dueInvoicesCalendar($monthStart, $monthEnd);
                }
            } catch (Throwable $e) {
                $data['due_invoices'] = [];
            }
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Dashboard';
            $this->load->view('fixed/header', $head);
            $this->load->view('dashboard', $data);
            $this->load->view('fixed/footer');
        } else if ($this->aauth->permission_new(null, 'projectsAccess')) {
            $this->load->model('projects_model', 'projects');
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Project List';
            $data['totalt'] = $this->projects->project_count_all();

            $this->load->view('fixed/header', $head);
            $this->load->view('projects/index', $data);
            $this->load->view('fixed/footer');
        }else if ($this->aauth->permission_new(null, 'purchaseVerifyOrder')) {


            
               $head['title'] = "Manage Purchase Orders";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/verification');
        $this->load->view('fixed/footer');




        } else if ($this->aauth->permission_new(null, 'stockAccess')) {
            $head['title'] = "Products";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('products/products');
            $this->load->view('fixed/footer');
        } else {
            $head['title'] = "Manage Invoices";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('invoices/invoices');
            $this->load->view('fixed/footer');
        }
    }

    // --- Lightweight widget endpoints (lazy-loaded on dashboard) ---
    private function cache_get($key, $ttlSeconds, $producer)
    {
        // simple file cache in application/cache
        $cacheDir = APPPATH . 'cache' . DIRECTORY_SEPARATOR;
        $file = $cacheDir . 'dash_' . md5($key) . '.json';
        if (is_file($file) && (time() - filemtime($file) < $ttlSeconds)) {
            $json = file_get_contents($file);
            $data = @json_decode($json, true);
            if (is_array($data)) {
                // Ensure currency values in cached data are uppercase
                $this->ensure_uppercase_currency_in_data($data);
                return $data;
            }
        }
        $data = call_user_func($producer);
        // Ensure currency values are uppercase before caching
        $this->ensure_uppercase_currency_in_data($data);
        @file_put_contents($file, json_encode($data));
        return $data;
    }
    
    private function ensure_uppercase_currency_in_data(&$data)
    {
        // Get currency once from database
        static $currency_info = null;
        if ($currency_info === null) {
            $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
            $row = $query->row_array();
            if (isset($row['currency'])) {
                $currency_info = [
                    'lower' => strtolower($row['currency']),
                    'upper' => strtoupper($row['currency'])
                ];
            } else {
                $currency_info = ['lower' => '', 'upper' => ''];
            }
        }
        
        // Recursively ensure all currency strings in data are uppercase
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $this->ensure_uppercase_currency_in_data($value);
                } elseif (is_string($value) && !empty($currency_info['lower']) && $currency_info['lower'] !== $currency_info['upper']) {
                    // If string contains lowercase currency, replace with uppercase
                    if (stripos($value, $currency_info['lower']) !== false) {
                        $value = str_ireplace($currency_info['lower'], $currency_info['upper'], $value);
                    }
                }
            }
        }
    }

    public function widget_kpis()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $loc = (int)($this->aauth->get_user()->loc ?? 0);
            $user_loc = $this->aauth->get_user()->loc;
            $cacheKey = 'kpis_summary_' . $loc . '_' . date('Ymd');
            $data = $this->cache_get($cacheKey, 60, function () use ($loc, $user_loc) {
                $s = $this->dashboard_model->kpis_summary($loc);
                return [
                    'totals' => [
                        'totalSales'     => amountExchange($s['total_sales'], 0, $user_loc),
                        'totalSalesVat'  => amountExchange($s['total_sales_vat'], 0, $user_loc),
                        'todaySales'     => amountExchange($s['today_sales'], 0, $user_loc),
                        'todaySalesVat'  => amountExchange($s['today_sales_vat'], 0, $user_loc),
                        'monthSales'     => amountExchange($s['month_sales'], 0, $user_loc),
                        'monthSalesVat'  => amountExchange($s['month_sales_vat'], 0, $user_loc),
                        'yearSales'      => amountExchange($s['year_sales'], 0, $user_loc),
                        'yearSalesVat'   => amountExchange($s['year_sales_vat'], 0, $user_loc),
                        'totalDue'       => amountExchange($s['total_due'], 0, $user_loc),
                    ]
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_income_chart()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $since = date('Y-m-d', strtotime('-30 days'));
            $data = $this->cache_get('sales_invoices_chart_' . $today, 60, function () use ($since, $today) {
                // Build conditions similar to other invoice metrics
                $where = ["DATE(invoicedate) BETWEEN ? AND ?", "total > 0", "notes != 'Advance Payment'"];
                $params = [$since, $today];
                if (!$this->aauth->premission(19)) {
                    $where[] = "inv_type != 'DAYPASS'";
                }
                if ($this->aauth->get_user()->loc) {
                    $where[] = 'loc = ?';
                    $params[] = $this->aauth->get_user()->loc;
                } elseif (defined('BDATA') && !BDATA) {
                    $where[] = 'loc = 0';
                }
                $whereSql = 'WHERE ' . implode(' AND ', $where);

                $sql = "SELECT DATE(invoicedate) AS d, COALESCE(SUM(total) - SUM(tax),0) AS sales, COUNT(id) AS invoices
                        FROM geopos_invoices
                        {$whereSql}
                        GROUP BY DATE(invoicedate)
                        ORDER BY d ASC";
                $rows = $this->db->query($sql, $params)->result_array();
                $income = [];
                $expense = [];
                foreach ($rows as $r) {
                    $income[] = ['date' => $r['d'], 'total' => (float)$r['sales']]; // Sales amount
                    $expense[] = ['date' => $r['d'], 'total' => (int)$r['invoices']]; // Invoices count
                }
                return [ 'income' => $income, 'expense' => $expense ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data, JSON_NUMERIC_CHECK));
            return;
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $errorMsg = ENVIRONMENT === 'development' 
                ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
                : 'err';
            $this->output->set_output(json_encode(['error' => $errorMsg]));
            return;
        }
    }

    public function widget_recent_invoices()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $user_loc = $this->aauth->get_user()->loc;
            $data = $this->cache_get('recent_invoices', 60, function () use ($user_loc) {
                $raw = $this->dashboard_model->recentInvoices();
                // Format amounts using amountExchange
                foreach ($raw as &$invoice) {
                    $invoice['formatted_total'] = amountExchange($invoice['total'] ?? 0, 0, $user_loc);
                    $invoice['formatted_tax'] = amountExchange($invoice['tax'] ?? 0, 0, $user_loc);
                }
                return [ 'recent' => $raw ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_purchase_overview()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $month = date('m');
            $year  = date('Y');
            $user_loc = $this->aauth->get_user()->loc;
            $data = $this->cache_get('purchase_overview_' . $today, 60, function () use ($user_loc) {
                $raw = $this->dashboard_model->getPurchaseOverviewOptimized();
                return [
                    'total' => amountExchange($raw['total'], 0, $user_loc),
                    'totalVat' => amountExchange($raw['totalVat'], 0, $user_loc),
                    'today' => amountExchange($raw['today'], 0, $user_loc),
                    'todayVat' => amountExchange($raw['todayVat'], 0, $user_loc),
                    'month' => amountExchange($raw['month'], 0, $user_loc),
                    'monthVat' => amountExchange($raw['monthVat'], 0, $user_loc),
                    'year' => amountExchange($raw['year'], 0, $user_loc),
                    'yearVat' => amountExchange($raw['yearVat'], 0, $user_loc),
                    'countTotal' => $raw['countTotal'],
                    'countToday' => $raw['countToday'],
                    'countMonth' => $raw['countMonth'],
                    'countYear' => $raw['countYear'],
                    'verCount' => $raw['verCount'],
                    'unverCount' => $raw['unverCount']
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_received_summary()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $user_loc = $this->aauth->get_user()->loc;
            $data = $this->cache_get('received_summary_' . $today, 60, function () use ($user_loc) {
                $raw = $this->dashboard_model->getReceivedSummaryOptimized();
                // Format all amounts using amountExchange
                return [
                    'yesterday' => $raw['yesterday'],
                    'yesterday_total' => amountExchange($raw['yesterday_total'], 0, $user_loc),
                    'yesterday_cash' => amountExchange($raw['yesterday_cash'], 0, $user_loc),
                    'yesterday_card' => amountExchange($raw['yesterday_card'], 0, $user_loc),
                    'yesterday_bank' => amountExchange($raw['yesterday_bank'], 0, $user_loc),
                    'today' => $raw['today'],
                    'today_total' => amountExchange($raw['today_total'], 0, $user_loc),
                    'today_cash' => amountExchange($raw['today_cash'], 0, $user_loc),
                    'today_card' => amountExchange($raw['today_card'], 0, $user_loc),
                    'today_bank' => amountExchange($raw['today_bank'], 0, $user_loc),
                    'month' => $raw['month'],
                    'month_total' => amountExchange($raw['month_total'], 0, $user_loc),
                    'month_cash' => amountExchange($raw['month_cash'], 0, $user_loc),
                    'month_card' => amountExchange($raw['month_card'], 0, $user_loc),
                    'month_bank' => amountExchange($raw['month_bank'], 0, $user_loc),
                    'year' => $raw['year'],
                    'year_total' => amountExchange($raw['year_total'], 0, $user_loc),
                    'year_cash' => amountExchange($raw['year_cash'], 0, $user_loc),
                    'year_card' => amountExchange($raw['year_card'], 0, $user_loc),
                    'year_bank' => amountExchange($raw['year_bank'], 0, $user_loc)
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_inventory_summary()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            // Bypass cache to reflect live values immediately
            $data = $this->dashboard_model->getInventorySummaryOptimized();
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_business_partners()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $user_loc = $this->aauth->get_user()->loc;
            $data = $this->cache_get('partners', 60, function () use ($user_loc) {
                $raw = $this->dashboard_model->getBusinessPartnersOptimized();
                return [
                    'totalCust' => $raw['totalCust'],
                    'totalSupp' => $raw['totalSupp'],
                    'totalUser' => $raw['totalUser'],
                    'ttlBalance' => amountExchange($raw['ttlBalance'], 0, $user_loc)
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_recent_payments()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $data = $this->cache_get('recent_payments_list', 30, function () {
                return [ 'rows' => $this->dashboard_model->recent_payments() ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data, JSON_NUMERIC_CHECK));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_today_cards()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $user_loc = $this->aauth->get_user()->loc;
            $data = $this->cache_get('today_cards_' . $today, 15, function () use ($today, $user_loc) {
                // Get income from transactions
                $sqlIncome   = "SELECT COALESCE(SUM(credit),0) AS v FROM geopos_transactions WHERE type='Income' AND DATE(date)=?";
                $income  = (float)($this->db->query($sqlIncome, [$today])->row()->v ?? 0);
                
                // Also get today's sales from invoices (net of tax)
                $where = ["DATE(invoicedate) = ?", "total > 0", "notes != 'Advance Payment'"];
                $params = [$today];
                if (!$this->aauth->premission(19)) {
                    $where[] = "inv_type != 'DAYPASS'";
                }
                if ($this->aauth->get_user()->loc) {
                    $where[] = 'loc = ?';
                    $params[] = $this->aauth->get_user()->loc;
                } elseif (defined('BDATA') && !BDATA) {
                    $where[] = 'loc = 0';
                }
                $whereSql = 'WHERE ' . implode(' AND ', $where);
                $sqlSales = "SELECT COALESCE(SUM(total) - SUM(tax), 0) AS v FROM geopos_invoices {$whereSql}";
                $todaySales = (float)($this->db->query($sqlSales, $params)->row()->v ?? 0);
                
                // Combine transaction income with invoice sales
                $totalIncome = $income + $todaySales;
                
                // Get expenses from transactions
                $sqlExpense  = "SELECT COALESCE(SUM(debit),0)  AS v FROM geopos_transactions WHERE type='Expense' AND DATE(date)=?";
                $expense = (float)($this->db->query($sqlExpense, [$today])->row()->v ?? 0);
                
                $revenue = $totalIncome; // Revenue includes both transactions and sales
                $profit  = $totalIncome - $expense;
                
                return [
                    'todayIncome'   => amountExchange($totalIncome, 0, $user_loc),
                    'todayExpenses' => amountExchange($expense, 0, $user_loc),
                    'todayProfit'   => amountExchange($profit, 0, $user_loc),
                    'todayRevenue'  => amountExchange($revenue, 0, $user_loc),
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function widget_recent_buyers()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $user_loc = $this->aauth->get_user()->loc;
            $data = $this->cache_get('recent_buyers_list', 60, function () use ($user_loc) {
                $raw = $this->dashboard_model->recentBuyersLatest();
                // Format amounts using amountExchange
                foreach ($raw as &$buyer) {
                    $buyer['formatted_total'] = amountExchange($buyer['total'] ?? 0, 0, $user_loc);
                    $buyer['formatted_tax'] = amountExchange($buyer['tax'] ?? 0, 0, $user_loc);
                }
                return [ 'rows' => $raw ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    // Individual AJAX endpoints for each dashboard element
    public function ajax_sales_kpis()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $month = date('m');
            $year = date('Y');
            $data = $this->cache_get('sales_kpis_' . $today, 60, function () use ($today, $month, $year) {
                return [
                    'totalSales' => (float)$this->dashboard_model->TotallSales($today, $month, $year),
                    'totalSalesVat' => (float)$this->dashboard_model->TotallSalesVat($today, $month, $year),
                    'todaySales' => (float)$this->dashboard_model->todaySales($today),
                    'todaySalesVat' => (float)$this->dashboard_model->todaySalesVAT($today),
                    'monthSales' => (float)$this->dashboard_model->monthlySales($month, $year),
                    'monthSalesVat' => (float)$this->dashboard_model->monthlySalesVAT($month, $year),
                    'yearSales' => (float)$this->dashboard_model->thisyearSales($year),
                    'yearSalesVat' => (float)$this->dashboard_model->thisyearSalesVat($year),
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function ajax_invoice_kpis()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $month = date('m');
            $year = date('Y');
            $loc = (int)($this->aauth->get_user()->loc ?? 0);

            $data = $this->cache_get('invoice_kpis_' . $today . '_' . $loc, 60, function () use ($today, $month, $year, $loc) {
                // Build WHERE pieces similar to kpis_summary
                $conditions = ["total > 0", "notes != 'Advance Payment'"];
                $params = [];
                if (!$this->aauth->premission(19)) {
                    $conditions[] = "inv_type != 'DAYPASS'";
                }
                if (!empty($loc)) {
                    $conditions[] = 'loc = ?';
                    $params[] = $loc;
                } elseif (!BDATA) {
                    $conditions[] = 'loc = 0';
                }
                $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

                $sql = "
                    SELECT
                      SUM(CASE WHEN DATE(invoicedate)=CURDATE() THEN 1 ELSE 0 END) AS today_cnt,
                      SUM(CASE WHEN DATE(invoicedate)=DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS tomorrow_cnt,
                      SUM(CASE WHEN MONTH(invoicedate)=MONTH(CURDATE()) AND YEAR(invoicedate)=YEAR(CURDATE()) THEN 1 ELSE 0 END) AS month_cnt,
                      SUM(CASE WHEN MONTH(invoicedate)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(invoicedate)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 ELSE 0 END) AS last_month_cnt
                    FROM geopos_invoices
                    {$where}
                ";
                $row = $this->db->query($sql, $params)->row_array();
                return [
                    'todayInvoices'    => (int)($row['today_cnt'] ?? 0),
                    'tomorrowInvoices' => (int)($row['tomorrow_cnt'] ?? 0),
                    'monthInvoices'    => (int)($row['month_cnt'] ?? 0),
                    'lastMonthInvoices'=> (int)($row['last_month_cnt'] ?? 0),
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function ajax_purchase_kpis()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $month = date('m');
            $year = date('Y');
            $data = $this->cache_get('purchase_kpis_' . $today, 60, function () use ($today, $month, $year) {
                return [
                    'totalPurchase' => (float)$this->dashboard_model->totalPurchase($today),
                    'totalPurchaseVat' => (float)$this->dashboard_model->totalPurchaseVAT($today),
                    'todayPurchase' => (float)$this->dashboard_model->todayPurchase($today),
                    'todayPurchaseVat' => (float)$this->dashboard_model->todayPurchaseVAT($today),
                    'monthPurchase' => (float)$this->dashboard_model->monthlyPurchase($month, $year),
                    'monthPurchaseVat' => (float)$this->dashboard_model->monthlyPurchaseVAT($month, $year),
                    'yearPurchase' => (float)$this->dashboard_model->thisyearPurchase($year),
                    'yearPurchaseVat' => (float)$this->dashboard_model->thisyearPurchaseVat($year),
                    'totalCount' => (int)$this->dashboard_model->totalPurchaseCount(),
                    'todayCount' => (int)$this->dashboard_model->todayPurchaseCount($today),
                    'monthCount' => (int)$this->dashboard_model->monthlyPurchaseCount($month, $year),
                    'verifiedCount' => (int)$this->dashboard_model->todayPurchaseVerifiedcount(),
                    'unverifiedCount' => (int)$this->dashboard_model->todayPurchaseUnverifiedcount(),
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function ajax_inventory_kpis()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $data = $this->cache_get('inventory_kpis', 60, function () {
                return [
                    'allProductsInHand' => (int)$this->dashboard_model->allProductsInHand(),
                    'productWillBeReceived' => (int)$this->dashboard_model->productWillBeReceived(),
                    'stockedOutItems' => (int)$this->dashboard_model->stockedOutItems(),
                    'productCat' => (int)$this->dashboard_model->productCat(),
                    'allItems' => (int)$this->dashboard_model->allItems(),
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }

    public function ajax_goals_data()
    {
        @ini_set('display_errors', 0);
        $this->output->set_content_type('application/json');
        try {
            $today = date('Y-m-d');
            $monthStart = date('Y-m-01');
            $data = $this->cache_get('goals_data_' . $monthStart, 60, function () use ($monthStart, $today) {
                // Monthly Income and Expense from transactions
                $sqlInc = "SELECT COALESCE(SUM(credit),0) AS v FROM geopos_transactions WHERE type='Income' AND DATE(date) BETWEEN ? AND ?";
                $sqlExp = "SELECT COALESCE(SUM(debit),0)  AS v FROM geopos_transactions WHERE type='Expense' AND DATE(date) BETWEEN ? AND ?";
                $inc = (float)($this->db->query($sqlInc, [$monthStart, $today])->row()->v ?? 0);
                $exp = (float)($this->db->query($sqlExp, [$monthStart, $today])->row()->v ?? 0);
                // Monthly sales amount from invoices (net of tax)
                $sqlSales = "SELECT COALESCE(SUM(total) - SUM(tax),0) AS v FROM geopos_invoices WHERE DATE(invoicedate) BETWEEN ? AND ? AND total>0";
                $sales = (float)($this->db->query($sqlSales, [$monthStart, $today])->row()->v ?? 0);
                // Load goal targets from tools_model if available; otherwise fallback
                try {
                    $targets = $this->tools_model->goals(1);
                    if (!is_array($targets)) { $targets = []; }
                } catch (Throwable $e) { $targets = []; }
                $goals = [
                    'income'    => (float)($targets['income'] ?? 999999000),
                    'expense'   => (float)($targets['expense'] ?? 999999000),
                    'sales'     => (float)($targets['sales'] ?? 999999000),
                    'netincome' => (float)($targets['netincome'] ?? 999999000),
                ];
                return [
                    'tt_inc' => $inc,
                    'tt_exp' => $exp,
                    'tt_sales' => $sales,
                    'tt_net' => ($inc - $exp),
                    'goals' => $goals,
                ];
            });
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode($data));
        } catch (Throwable $e) {
            if (ob_get_length()) { @ob_end_clean(); }
            $this->output->set_output(json_encode(['error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'err']));
        }
    }
    
    public function format()
    {
        $today = date("Y-m-d");
        $month = date("m");
        $tomorrow = date("Y-m-d",strtotime("+1 day"));
        $year = date("Y");
        if ($this->aauth->get_user()->roleid > 3) {
            $data['todayin'] = $this->dashboard_model->todayInvoice($today);
            $data['tomorrowin'] = $this->dashboard_model->todayInvoice($tomorrow);
            $data['tomorrowsales'] = $this->dashboard_model->todaySales($tomorrow);
            $data['tomorrowsalesvat'] = $this->dashboard_model->todaySalesVAT($tomorrow);
            $data['todayprofit'] = $this->dashboard_model->todayProfit($today);
            $data['incomechart'] = $this->dashboard_model->incomeChart($today, $month, $year);
            $data['expensechart'] = $this->dashboard_model->expenseChart($today, $month, $year);
            $data['countmonthlychart'] = $this->dashboard_model->countmonthlyChart();
            $data['monthin'] = $this->dashboard_model->monthlyInvoice($month, $year);
            //Customer Balance All Time
            $data['ttlBalance'] = $this->dashboard_model->CustomerBalance();
            //var_dump($data['ttlBalance']); exit;
            $data['todaysales'] = $this->dashboard_model->todaySales($today);

            $data['todaypurchase'] = $this->dashboard_model->todayPurchase($today);
            $data['todaypurchase'] = $this->dashboard_model->todayPurchase($tomorrow);
            $data['todaypurchasevat'] = $this->dashboard_model->todayPurchaseVAT($today);
            $data['monthpurchase'] = $this->dashboard_model->monthlyPurchase($month, $year);
            $data['monthpurchasevat'] = $this->dashboard_model->monthlyPurchaseVAT($month, $year);
            $data['todayinpurchase'] = $this->dashboard_model->todayPurchaseInvoice($today);
            $data['tomorrowinpurchase'] = $this->dashboard_model->todayPurchaseInvoice($tomorrow);
            $data['monthinpurchase'] = $this->dashboard_model->monthlyPurchaseInvoice($month, $year);

            $data['todaysalesincvat'] = $this->dashboard_model->todaySalesIncVAT($today);
            $data['todaysalesvat'] = $this->dashboard_model->todaySalesVAT($today);
            $data['monthsales'] = $this->dashboard_model->monthlySales($month, $year);
            $data['monthsalesvat'] = $this->dashboard_model->monthlySalesVAT($month, $year);
            $data['todayinexp'] = $this->dashboard_model->todayInexp($today);
            $data['todayincome'] = $this->dashboard_model->todayincome($today);
            $data['recent_payments'] = $this->dashboard_model->recent_payments();
            $data['tasks'] = $this->dashboard_model->tasks($this->aauth->get_user()->id);
            $data['recent'] = $this->dashboard_model->recentInvoices();
            $data['recentpurchase'] = $this->dashboard_model->recentPurchase();
            $data['recent_buy'] = $this->dashboard_model->recentBuyers();
            $data['recent_supplier'] = $this->dashboard_model->recentSupplier();
            $data['goals'] = $this->tools_model->goals(1);
            $data['stock'] = $this->dashboard_model->stock();
            $data['warehouse_stock'] = $this->dashboard_model->warehouse_stock();
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Dashboard';
            $this->load->view('fixed/header', $head);
            $this->load->view('dashboard2', $data);
            $this->load->view('fixed/footer');
        } else if ($this->aauth->premission(4)) {
            $this->load->model('projects_model', 'projects');
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Project List';
            $data['totalt'] = $this->projects->project_count_all();

            $this->load->view('fixed/header', $head);
            $this->load->view('projects/index', $data);
            $this->load->view('fixed/footer');
        }else if ($this->aauth->premission(13)) {


            
               $head['title'] = "Manage Purchase Orders";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/verification');
        $this->load->view('fixed/footer');




        } else if ($this->aauth->get_user()->roleid == 1) {
            $head['title'] = "Products";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('products/products');
            $this->load->view('fixed/footer');
        } else {
            $head['title'] = "Manage Invoices";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('invoices/invoices');
            $this->load->view('fixed/footer');
        }
    }

    public function clock_in()
    {
        $id = $this->aauth->get_user()->id;
        if ($this->aauth->auto_attend()) {
            $this->dashboard_model->clockin($id);
        }

        redirect('dashboard');
    }

    public function clock_out()
    {
        $id = $this->aauth->get_user()->id;

        if ($this->aauth->auto_attend()) {
            $this->dashboard_model->clockout($id);
        }


        redirect('dashboard');
    }
}