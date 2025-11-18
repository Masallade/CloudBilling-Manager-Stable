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

class Dashboard_model extends CI_Model
{

	public function kpis_summary($loc)
	{
		// Build a single fast aggregate over invoices
		$conditions = ["total > 0", "notes != 'Advance Payment'"];
		$params = [];
		// Exclude DAYPASS if user lacks permission 19 (mirrors other methods)
		if (!$this->aauth->premission(19)) {
			$conditions[] = "inv_type != 'DAYPASS'";
		}
		// Location scoping
		if (!empty($loc)) {
			$conditions[] = "loc = ?";
			$params[] = (int)$loc;
		} elseif (!BDATA) {
			$conditions[] = "loc = 0";
		}
		$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
		$sql = "
			SELECT
			  SUM(CASE WHEN status IN ('due','partial') THEN total - pamnt ELSE 0 END)                                 AS total_due,
			  SUM(total)                                                                                               AS total_sales,
			  SUM(CASE WHEN DATE(invoicedate) = CURDATE() THEN total ELSE 0 END)                                        AS today_sales,
			  SUM(CASE WHEN MONTH(invoicedate)=MONTH(CURDATE()) AND YEAR(invoicedate)=YEAR(CURDATE()) THEN total END)   AS month_sales,
			  SUM(CASE WHEN YEAR(invoicedate)=YEAR(CURDATE()) THEN total END)                                           AS year_sales,
			  SUM(tax)                                                                                                  AS total_sales_vat,
			  SUM(CASE WHEN DATE(invoicedate) = CURDATE() THEN tax ELSE 0 END)                                          AS today_sales_vat,
			  SUM(CASE WHEN MONTH(invoicedate)=MONTH(CURDATE()) AND YEAR(invoicedate)=YEAR(CURDATE()) THEN tax END)     AS month_sales_vat,
			  SUM(CASE WHEN YEAR(invoicedate)=YEAR(CURDATE()) THEN tax END)                                             AS year_sales_vat
			FROM geopos_invoices
			{$where}
		";
		$row = $this->db->query($sql, $params)->row_array();
		if (!is_array($row)) {
			$row = [];
		}
		return [
			'total_due'       => (float)($row['total_due'] ?? 0),
			'total_sales'     => (float)($row['total_sales'] ?? 0),
			'today_sales'     => (float)($row['today_sales'] ?? 0),
			'month_sales'     => (float)($row['month_sales'] ?? 0),
			'year_sales'      => (float)($row['year_sales'] ?? 0),
			'total_sales_vat' => (float)($row['total_sales_vat'] ?? 0),
			'today_sales_vat' => (float)($row['today_sales_vat'] ?? 0),
			'month_sales_vat' => (float)($row['month_sales_vat'] ?? 0),
			'year_sales_vat'  => (float)($row['year_sales_vat'] ?? 0),
		];
	}

    public function yesterday_received($yesterday)
    {
        // Validate and format the date
        $formattedDate = date('Y-m-d', strtotime($yesterday));

        // Build the query
        // $this->db->select('paymt_method, SUM(received_payment) as total');
        // Build the query
        $this->db->select('paymt_method,
            SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total');
        $this->db->from('geopos_transactions');
        $this->db->where('paymt_method !=', '');
        $this->db->where('type', 'Income');
        // Use SQL-side yesterday to avoid timezone drift
        $this->db->where('DATE(date) = CURDATE() - INTERVAL 1 DAY');

        // Optional: Filter by location if needed
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        // Group by payment method
        $this->db->group_by('paymt_method');

        // Execute the query and return the results
        return $this->db->get()->result(); // Returns an array of results
    }

    // OPTIMIZED: Single query for all received payment summaries
    public function getReceivedSummaryOptimized()
    {
        $loc_condition = '';
        $params = [];
        
        if ($this->aauth->get_user()->loc) {
            $loc_condition = ' AND loc = ?';
            $params[] = $this->aauth->get_user()->loc;
        } elseif (!BDATA) {
            $loc_condition = ' AND loc = 0';
        }

        $sql = "
            SELECT 
                'yesterday' as period,
                paymt_method,
                SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total
            FROM geopos_transactions 
            WHERE paymt_method != '' 
                AND type = 'Income' 
                AND DATE(date) = CURDATE() - INTERVAL 1 DAY
                {$loc_condition}
            GROUP BY paymt_method
            
            UNION ALL
            
            SELECT 
                'today' as period,
                paymt_method,
                SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total
            FROM geopos_transactions 
            WHERE paymt_method != '' 
                AND type = 'Income' 
                AND DATE(date) = CURDATE()
                {$loc_condition}
            GROUP BY paymt_method
            
            UNION ALL
            
            SELECT 
                'month' as period,
                paymt_method,
                SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total
            FROM geopos_transactions 
            WHERE paymt_method != '' 
                AND type = 'Income' 
                AND MONTH(date) = MONTH(CURDATE()) 
                AND YEAR(date) = YEAR(CURDATE())
                {$loc_condition}
            GROUP BY paymt_method
            
            UNION ALL
            
            SELECT 
                'year' as period,
                paymt_method,
                SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total
            FROM geopos_transactions 
            WHERE paymt_method != '' 
                AND type = 'Income' 
                AND YEAR(date) = YEAR(CURDATE())
                {$loc_condition}
            GROUP BY paymt_method
        ";
        
        $query = $this->db->query($sql, $params);
        $results = $query->result_array();
        
        // Helper function to normalize payment method names
        $normalizeMethod = function($method) {
            $methodLower = strtolower(trim($method));
            if (strpos($methodLower, 'cash') !== false) {
                return 'Cash';
            } elseif (strpos($methodLower, 'card') !== false) {
                return 'Card Payment';
            } elseif (strpos($methodLower, 'bank') !== false || strpos($methodLower, 'transfer') !== false) {
                return 'Bank Transfer';
            }
            return $method; // Return original if no match
        };
        
        // Group results by period and calculate totals
        $grouped = [
            'yesterday' => [],
            'today' => [],
            'month' => [],
            'year' => []
        ];
        
        $totals = [
            'yesterday' => ['total' => 0, 'cash' => 0, 'card' => 0, 'bank' => 0],
            'today' => ['total' => 0, 'cash' => 0, 'card' => 0, 'bank' => 0],
            'month' => ['total' => 0, 'cash' => 0, 'card' => 0, 'bank' => 0],
            'year' => ['total' => 0, 'cash' => 0, 'card' => 0, 'bank' => 0]
        ];
        
        foreach ($results as $row) {
            $period = $row['period'];
            $method = $normalizeMethod($row['paymt_method']);
            $total = (float)$row['total'];
            
            // Add to array
            $grouped[$period][] = [
                'paymt_method' => $method,
                'total' => $total
            ];
            
            // Calculate totals
            $totals[$period]['total'] += $total;
            if ($method === 'Cash') {
                $totals[$period]['cash'] += $total;
            } elseif ($method === 'Card Payment') {
                $totals[$period]['card'] += $total;
            } elseif ($method === 'Bank Transfer') {
                $totals[$period]['bank'] += $total;
            }
        }
        
        // Return format expected by JavaScript
        return [
            'yesterday' => $grouped['yesterday'],
            'yesterday_total' => $totals['yesterday']['total'],
            'yesterday_cash' => $totals['yesterday']['cash'],
            'yesterday_card' => $totals['yesterday']['card'],
            'yesterday_bank' => $totals['yesterday']['bank'],
            'today' => $grouped['today'],
            'today_total' => $totals['today']['total'],
            'today_cash' => $totals['today']['cash'],
            'today_card' => $totals['today']['card'],
            'today_bank' => $totals['today']['bank'],
            'month' => $grouped['month'],
            'month_total' => $totals['month']['total'],
            'month_cash' => $totals['month']['cash'],
            'month_card' => $totals['month']['card'],
            'month_bank' => $totals['month']['bank'],
            'year' => $grouped['year'],
            'year_total' => $totals['year']['total'],
            'year_cash' => $totals['year']['cash'],
            'year_card' => $totals['year']['card'],
            'year_bank' => $totals['year']['bank']
        ];
    }

    public function month_received($month, $year)
    {
        // Validate and ensure $month and $year are integers
        $month = intval($month);
        $year = intval($year);

        // Build the query
        // $this->db->select('paymt_method, SUM(received_payment) as total');
        // Build the query
        $this->db->select('paymt_method,
            SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total');
        $this->db->from('geopos_transactions');
        $this->db->where('paymt_method !=', ''); // Ensure paymt_method is not empty
        $this->db->where('type', 'Income'); // Filter by type 'Income'
        $this->db->where('MONTH(date)', $month); // Filter by month
        $this->db->where('YEAR(date)', $year); // Filter by year

        // Optional: Filter by location if needed
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        // Group by payment method
        $this->db->group_by('paymt_method');

        // Execute the query and return the results
        return $this->db->get()->result(); // Returns an array of results
    }

    public function year_received($year)
    {
        // Validate and sanitize input
        $year = intval($year);

        // Build the query
        $this->db->select('paymt_method,
            SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total');
        $this->db->from('geopos_transactions');
        $this->db->where('paymt_method !=', ''); // Ensure paymt_method is not empty
        $this->db->where('type', 'Income'); // Filter by type 'Income'
        $this->db->where("YEAR(date)", $year);

        // Optional: Filter by location if needed
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        // Group by payment method
        $this->db->group_by('paymt_method');

        // Execute the query and return the results
        return $this->db->get()->result(); // Returns an array of results
    }

    public function today_received($today)
    {
        // Validate and format the date
        $formattedDate = date('Y-m-d', strtotime($today));

        // Build the query (handle received_payment=0 by taking debit)
        $this->db->select('paymt_method,
            SUM(CASE WHEN (received_payment IS NULL OR received_payment = 0) THEN debit ELSE received_payment END) AS total');
        $this->db->from('geopos_transactions');
        $this->db->where('paymt_method !=', ''); // Ensure paymt_method is not empty
        $this->db->where('type', 'Income'); // Filter by type 'Income'
        $this->db->where('DATE(date)', $formattedDate);

        // Optional: Filter by location if needed
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        // Group by payment method
        $this->db->group_by('paymt_method');

        // Execute the query and return the results
        return $this->db->get()->result(); // Returns an array of results
    }


    public function todayInvoice($today)
    {
        $where = "DATE(invoicedate) ='$today' AND notes != 'Advance Payment' AND total>0";
        $this->db->where($where);
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->from('geopos_invoices');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function todayCost($today)
    {
        $this->db->select_sum('(geopos_invoice_items.purchase * geopos_invoice_items.qty)', 'total_cost');
        $this->db->from('geopos_invoices');
        $this->db->join('geopos_invoice_items', 'geopos_invoices.id = geopos_invoice_items.tid');

        $where = "DATE(geopos_invoices.invoicedate) ='$today' AND geopos_invoices.notes != 'Advance Payment' AND geopos_invoices.total > 0";

        $this->db->where($where);

        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        $query = $this->db->get();

        // Check if there are any results
        if ($query->num_rows() > 0) {
            // Return the total cost (multiplication of purchase and qty)
            return $query->row()->total_cost;
        } else {
            // Return 0 if no results
            return 0;
        }
    }


    public function allProductsInHand()
    {
        // Sum all quantities across locations for dashboard visibility
        $this->db->select_sum('qty');
        $this->db->from('geopos_products');
        $query = $this->db->get();
        $result = $query->row();
        return (int)($result->qty ?? 0);
    }

    // OPTIMIZED: Single query for all inventory summary data
    public function getInventorySummaryOptimized()
    {
        $sql = "
            SELECT 
                (SELECT SUM(qty) FROM geopos_products) as allProductsInHand,
                (SELECT COUNT(*) FROM geopos_products WHERE qty <= alert) as stockedOutItems,
                (SELECT COUNT(*) FROM geopos_product_cat) as productCat,
                (SELECT COUNT(*) FROM geopos_products) as allItems,
                (SELECT COALESCE(SUM(pi.qty), 0)
                 FROM geopos_purchase_items pi
                 JOIN geopos_purchase p ON pi.tid = p.id
                 WHERE (p.verified = 'Unverified' OR p.status = 'Unverified')) as productWillBeReceived
        ";
        
        $query = $this->db->query($sql);
        $result = $query->row_array();
        
        return [
            'allProductsInHand' => (int)($result['allProductsInHand'] ?? 0),
            'productWillBeReceived' => (int)($result['productWillBeReceived'] ?? 0),
            'stockedOutItems' => (int)($result['stockedOutItems'] ?? 0),
            'productCat' => (int)($result['productCat'] ?? 0),
            'allItems' => (int)($result['allItems'] ?? 0)
        ];
    }

    public function productWillBeReceived()
    {
        // Select the sum of quantity from purchase_item where order status is unverified
        $this->db->select_sum('geopos_purchase_items.qty');
        $this->db->from('geopos_purchase_items');
        $this->db->join('geopos_purchase', 'geopos_purchase.id = geopos_purchase_items.tid');
        $this->db->where('geopos_purchase.verified', 'Unverified');


        $query = $this->db->get();
        $result = $query->row();

        // Return the sum of quantity
        return $result->qty;
    }

    public function allItems()
    {
        // Count all product rows across locations for dashboard
        $this->db->select('COUNT(qty) as total_qty');
        $this->db->from('geopos_products');
        $query = $this->db->get();
        $result = $query->row();
        return (int)($result->total_qty ?? 0);
    }

    public function stockedOutItems()
    {
        $this->db->select('COUNT(*) as item_count');
        $this->db->from('geopos_products');
        $this->db->where('alert >= qty');

        $query = $this->db->get();
        $result = $query->row();

        // Return the count of items
        return $result->item_count;
    }
    public function totalCust()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('geopos_customers');

        $query = $this->db->get();
        $result = $query->row();

        // Return the count of categories
        return $result->count;
    }
    public function totalSupp()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('geopos_supplier');

        $query = $this->db->get();
        $result = $query->row();

        // Return the count of categories
        return $result->count;
    }
    public function totalUser()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('geopos_employees');

        $query = $this->db->get();
        $result = $query->row();

        // Return the count of categories
        return $result->count;
    }
    public function productCat()
    {
        $this->db->select('COUNT(*) as category_count');
        $this->db->from('geopos_product_cat');

        $query = $this->db->get();
        $result = $query->row();

        // Return the count of categories
        return $result->category_count;
    }


    public function todayPurchaseInvoice($today)
    {
        $where = "DATE(invoicedate) ='$today' AND total>0";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function todayPurchaseVerified($today)
    {
        $where = "DATE(invoicedate) ='$today' AND total>0 AND verified= 'Verified'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function todayPurchaseVerifiedcount()
    {
        $where = "verified= 'Verified'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function todayPurchaseUnverifiedcount()
    {
        $where = "verified= 'Unverified'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function todayPurchaseUnverified($today)
    {
        $where = "DATE(invoicedate) ='$today' AND total>0 AND verified= 'Unverified'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function todayPurchaseDue($today)
    {
        $where = "DATE(invoicedate) ='$today' AND total>0 AND status= 'due'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }

    public function CustomerBalance()
    {
        //  $this->db->select_sum('balance');
        // $this->db->from('geopos_customers');
        // $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        // return $query->row()->balance;
        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" or status="partial"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance = $response[0]['cust_balance'];
        return $cust_balance;
    }

    // OPTIMIZED: Single query for all business partners data
    public function getBusinessPartnersOptimized()
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM geopos_customers) as totalCust,
                (SELECT COUNT(*) FROM geopos_supplier) as totalSupp,
                (SELECT COUNT(*) FROM geopos_users) as totalUser,
                (SELECT COALESCE(SUM(total) - SUM(pamnt), 0) FROM geopos_invoices WHERE status IN ('due', 'partial')) as ttlBalance,
                (SELECT COALESCE(SUM(total) - SUM(pamnt), 0) FROM geopos_invoices WHERE status IN ('due', 'partial')) as customerBalances
        ";
        
        $query = $this->db->query($sql);
        $result = $query->row_array();
        
        return [
            'totalCust' => (int)($result['totalCust'] ?? 0),
            'totalSupp' => (int)($result['totalSupp'] ?? 0),
            'totalUser' => (int)($result['totalUser'] ?? 0),
            'ttlBalance' => (float)($result['ttlBalance'] ?? 0),
            'customerBalances' => (float)($result['customerBalances'] ?? 0)
        ];
    }
    public function CustomerBalancedash()
    {
        //  $this->db->select_sum('balance');
        // $this->db->from('geopos_customers');
        // $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        // return $query->row()->balance;
        // $sql = 'SELECT name, sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" or status="partial"';
        // $query = $this->db->query($sql);
        // $response = $query->result_array();
        // $cust_balance = $response[0]['cust_balance'];
        // return $cust_balance;


        $sql = '
        SELECT csd, cust_name AS cust_name, SUM(total) - SUM(pamnt) AS cust_balance
        FROM geopos_invoices
        WHERE status = "due" OR status = "partial"
        GROUP BY cust_name
        ORDER BY cust_balance DESC
        LIMIT 6
    ';

        $query = $this->db->query($sql);

        // Fetch the result as an associative array
        return $query->result_array();
    }

    public function todaySales($today)
    {

        $where = "DATE(invoicedate) ='$today' AND notes != 'Advance Payment' AND total>0";
        $this->db->select('SUM(total) - SUM(tax) as total');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function tommorowSales($tomorrow)
    {

        $where = "DATE(invoicedate) ='$tomorrow' AND notes != 'Advance Payment' AND total>0";
        $this->db->where($where);
        $this->db->select('*');
        $this->db->from('geopos_invoices');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }

    public function TotallSales($today, $month, $year)
    {

        // Construct the WHERE clause to filter non-zero total
        $where = 'total > 0';

        // Select the sum of total sales from geopos_invoices table
        $this->db->select('SUM(total) - SUM(tax) as total_invoices');
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query_invoices = $this->db->get();
        $total_invoices = $query_invoices->row()->total_invoices;

        return $total_invoices;

        // Select the sum of total sales from geopos_purchase table
        // $this->db->select('SUM(total) - SUM(tax) as total_purchases');
        // $this->db->from('geopos_purchase');
        // $this->db->where($where);
        // if ($this->aauth->get_user()->loc) {
        //     $this->db->where('loc', $this->aauth->get_user()->loc);
        // } elseif (!BDATA) {
        //     $this->db->where('loc', 0);
        // }
        // $query_purchases = $this->db->get();
        // $total_purchases = $query_purchases->row()->total_purchases;

        // // Calculate the total sales by adding total sales from both invoices and purchases
        // $total_sales = $total_invoices + $total_purchases;

        // return $total_sales;
    }
    public function thisyearSales($year)
    {
        // Construct the WHERE clause to filter data for the specified year with non-zero total
        $where = "YEAR(invoicedate) = $year AND total > 0";

        // Select the sum of total sales from geopos_invoices table for the specified year
        $this->db->select('SUM(total) - SUM(tax) as total_invoices');
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query_invoices = $this->db->get();
        $total_invoices = $query_invoices->row()->total_invoices;

        return $total_invoices;
    }
    public function todayPurchase($today)
    {

        $where = "DATE(invoicedate) ='$today' AND total>0";
        $this->db->select('sum(total) - sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function thisyearPurchase($year)
    {
        // Construct the WHERE clause to filter data for the specified year with non-zero total
        $where = "YEAR(invoicedate) = $year AND total > 0";

        // Select the sum of total sales from geopos_invoices table for the specified year
        $this->db->select('sum(total) - sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function thisyearPurchaseVat($year)
    {
        // Construct the WHERE clause to filter data for the specified year with non-zero total
        $where = "YEAR(invoicedate) = $year AND total > 0";

        // Select the sum of total sales from geopos_invoices table for the specified year
        $this->db->select('sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function totalPurchase($today)
    {

        $where = "total>0";
        $this->db->select('sum(total) - sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    // OPTIMIZED: Single query for all purchase overview data
    public function getPurchaseOverviewOptimized()
    {
        $loc_condition = '';
        $params = [];
        
        if ($this->aauth->get_user()->loc) {
            $loc_condition = ' AND loc = ?';
            $params[] = $this->aauth->get_user()->loc;
        } elseif (!BDATA) {
            $loc_condition = ' AND loc = 0';
        }

        $sql = "
            SELECT 
                SUM(CASE WHEN total > 0 THEN total - tax ELSE 0 END) as total,
                SUM(CASE WHEN total > 0 THEN tax ELSE 0 END) as totalVat,
                SUM(CASE WHEN DATE(invoicedate) = CURDATE() AND total > 0 THEN total - tax ELSE 0 END) as today,
                SUM(CASE WHEN DATE(invoicedate) = CURDATE() AND total > 0 THEN tax ELSE 0 END) as todayVat,
                SUM(CASE WHEN MONTH(invoicedate) = MONTH(CURDATE()) AND YEAR(invoicedate) = YEAR(CURDATE()) AND total > 0 THEN total - tax ELSE 0 END) as month,
                SUM(CASE WHEN MONTH(invoicedate) = MONTH(CURDATE()) AND YEAR(invoicedate) = YEAR(CURDATE()) AND total > 0 THEN tax ELSE 0 END) as monthVat,
                SUM(CASE WHEN YEAR(invoicedate) = YEAR(CURDATE()) AND total > 0 THEN total - tax ELSE 0 END) as year,
                SUM(CASE WHEN YEAR(invoicedate) = YEAR(CURDATE()) AND total > 0 THEN tax ELSE 0 END) as yearVat,
                COUNT(CASE WHEN total > 0 THEN 1 END) as countTotal,
                COUNT(CASE WHEN DATE(invoicedate) = CURDATE() THEN 1 END) as countToday,
                COUNT(CASE WHEN MONTH(invoicedate) = MONTH(CURDATE()) AND YEAR(invoicedate) = YEAR(CURDATE()) THEN 1 END) as countMonth,
                COUNT(CASE WHEN YEAR(invoicedate) = YEAR(CURDATE()) THEN 1 END) as countYear,
                COUNT(CASE WHEN verified = 'Verified' THEN 1 END) as verCount,
                COUNT(CASE WHEN verified = 'Unverified' THEN 1 END) as unverCount
            FROM geopos_purchase 
            WHERE 1=1 {$loc_condition}
        ";
        
        $query = $this->db->query($sql, $params);
        $result = $query->row_array();
        
        return [
            'total' => (float)($result['total'] ?? 0),
            'totalVat' => (float)($result['totalVat'] ?? 0),
            'today' => (float)($result['today'] ?? 0),
            'todayVat' => (float)($result['todayVat'] ?? 0),
            'month' => (float)($result['month'] ?? 0),
            'monthVat' => (float)($result['monthVat'] ?? 0),
            'year' => (float)($result['year'] ?? 0),
            'yearVat' => (float)($result['yearVat'] ?? 0),
            'countTotal' => (int)($result['countTotal'] ?? 0),
            'countToday' => (int)($result['countToday'] ?? 0),
            'countMonth' => (int)($result['countMonth'] ?? 0),
            'countYear' => (int)($result['countYear'] ?? 0),
            'verCount' => (int)($result['verCount'] ?? 0),
            'unverCount' => (int)($result['unverCount'] ?? 0)
        ];
    }
    public function totalPurchaseCount()
    {
        $this->db->select('*');
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function totalPurchaseVAT($today)
    {

        $where = "total>0";
        $this->db->select('sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }


    public function todaySalesVAT($today)
    {

        $where = "DATE(invoicedate) ='$today' AND notes != 'Advance Payment' AND total>0";
        $this->db->select('sum(tax) as total');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    public function todayPurchaseVAT($today)
    {

        $where = "DATE(invoicedate) ='$today' AND total>0";
        $this->db->select('sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function todayPurchaseCount($today)
    {
        $where = "DATE(invoicedate) ='$today'";
        $this->db->where($where);
        $this->db->select('*');
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function thisyearSalesVat($year)
    {
        // Construct the WHERE clause to filter data for the specified year with non-zero total
        $where = "YEAR(invoicedate) = $year AND total > 0";

        // Select the sum of total sales from geopos_invoices table for the specified year
        $this->db->select('SUM(tax) as total_invoices');
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query_invoices = $this->db->get();
        $total_invoices = $query_invoices->row()->total_invoices;

        return $total_invoices;
    }
    public function TotallSalesVat($today, $month, $year)
    {

        // Construct the WHERE clause to filter non-zero total
        $where = 'total > 0';

        // Select the sum of total sales from geopos_invoices table
        $this->db->select('SUM(tax) as total_invoices');
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query_invoices = $this->db->get();
        $total_invoices = $query_invoices->row()->total_invoices;

        return $total_invoices;

        // Select the sum of total sales from geopos_purchase table
        // $this->db->select('SUM(total) - SUM(tax) as total_purchases');
        // $this->db->from('geopos_purchase');
        // $this->db->where($where);
        // if ($this->aauth->get_user()->loc) {
        //     $this->db->where('loc', $this->aauth->get_user()->loc);
        // } elseif (!BDATA) {
        //     $this->db->where('loc', 0);
        // }
        // $query_purchases = $this->db->get();
        // $total_purchases = $query_purchases->row()->total_purchases;

        // // Calculate the total sales by adding total sales from both invoices and purchases
        // $total_sales = $total_invoices + $total_purchases;

        // return $total_sales;
    }

    public function todaySalesInc($today)
    {

        $where = "DATE(invoicedate) ='$today' AND notes != 'Advance Payment' AND total>0";
        $this->db->select('count(*) as total');
        // Additional filtering based on permission
        if (!$this->aauth->premission(19)) {
            // Restrict results to exclude "DAYPASS" invoices
            $this->db->where_in('geopos_invoices.inv_type', ['INVOICE']);
        } else {
            // Include both "INVOICE" and "DAYPASS" types if permission exists
            $this->db->where_in('geopos_invoices.inv_type', ['INVOICE', 'DAYPASS']);
        }
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function todaySalesIncVAT($today)
    {

        $where = "DATE(invoicedate) ='$today' AND notes != 'Advance Payment' AND total>0";
        $this->db->select('sum(tax) as total');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    public function tommorowSalesIncVAT($tomorrow)
    {

        $where = "DATE(invoicedate) ='$tomorrow' AND notes != 'Advance Payment' AND total>0";
        $this->db->select('count(*) as total');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type =', 'INVOICE');
        }
        $this->db->from('geopos_invoices');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    public function todayInexp($today)
    {
        $this->db->select('SUM(geopos_transactions.debit) as debit,SUM(geopos_transactions.credit) as credit, geopos_invoices.tax as vat', FALSE);
        $this->db->from('geopos_transactions');
        $this->db->join('geopos_invoices', 'geopos_transactions.tid=geopos_invoices.id', 'inner');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->where('DATE(geopos_transactions.date) ="' . $today . '"');
        $this->db->where('geopos_transactions.type!="Transfer"');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_transactions.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_transactions.loc', 0);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    public function todayincome($today)
    {
        $where = "DATE(added_on) ='$today'";
        $this->db->select_sum('price');
        $this->db->from('geopos_invoice_items');
        $this->db->where($where);
        $query = $this->db->get();
        return $query->row()->price;
    }




    public function recent_payments()
    {
        $this->db->limit(13);
        $this->db->from('geopos_transactions');
        $this->db->join('geopos_invoices', 'geopos_transactions.tid=geopos_invoices.id', 'inner');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->order_by('geopos_transactions.id', 'DESC');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_transactions.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_transactions.loc', 0);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function stock()
    {
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' AND (geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ')';
        } elseif (!BDATA) {
            $whr = ' AND (geopos_warehouse.loc=0)';
        }

        $query = $this->db->query("SELECT geopos_products.*,geopos_warehouse.title FROM geopos_products LEFT JOIN geopos_warehouse ON geopos_products.warehouse=geopos_warehouse.id  WHERE (geopos_products.qty<=geopos_products.alert) $whr ORDER BY geopos_products.product_name ASC LIMIT 10");
        return $query->result_array();
    }

    public function warehouse_stock()
    {


        $query = $this->db->query("SELECT sum(product_price)*qty as product_price ,sum(fproduct_price)*qty as fproduct_price, sum(qty) as totalrec FROM geopos_products");
        $query_array  = $query->result_array();

        return $query_array;
    }


    public function todayItems($today)
    {
        $where = "DATE(invoicedate) ='$today' AND notes != 'Advance Payment' AND total>0";
        $this->db->select_sum('items');
        $this->db->from('geopos_invoices');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $this->db->where($where);
        $query = $this->db->get();
        return $query->row()->items;
    }

    public function todayProfit($today)
    {
        $where = "DATE(added_on) ='$today'";
        $this->db->select_sum('price');
        $this->db->select_sum('purchase');
        $this->db->from('geopos_invoice_items');
        $this->db->where($where);
        $query = $this->db->get();
        return $query->row()->price - $query->row()->purchase;
    }

    public function incomeChart($today, $month, $year)
    {
        $whr = '';
        $whri = "";
        if ($this->aauth->get_user()->loc) {
            $whr = ' AND (loc=' . $this->aauth->get_user()->loc . ')';
        } elseif (!BDATA) {
            $whr = ' AND (loc=0)';
        }
        if (!$this->aauth->premission(19)) {
            $whri = " where geopos_invoices.inv_type!='DAYPASS'  AND ";
        } else {
            $whri = " where ";
        }
        // print_r($whri);die();
        // $query = $this->db->query("SELECT SUM(credit) AS total,date FROM geopos_transactions WHERE ((DATE(date) BETWEEN DATE('$year-$month-01') AND '$today') AND type='Income')  $whr GROUP BY date ORDER BY date DESC");

        $query = $this->db->query("SELECT SUM(credit) AS total,date FROM geopos_transactions inner join geopos_invoices on geopos_transactions.tid=geopos_invoices.id  $whri ((DATE(date) BETWEEN DATE('$year-$month-01') AND '$today') AND type='Income') $whr  GROUP BY date ORDER BY date DESC");
        return $query->result_array();
    }

    public function expenseChart($today, $month, $year)
    {
        $whr = '';
        $whri = "";
        if ($this->aauth->get_user()->loc) {
            $whr = ' AND (loc=' . $this->aauth->get_user()->loc . ')';
        } elseif (!BDATA) {
            $whr = ' AND (loc=0)';
        }
        if (!$this->aauth->premission(19)) {
            $whri = " where geopos_invoices.inv_type!='DAYPASS'  AND ";
        } else {
            $whri = " where ";
        }
        $query = $this->db->query("SELECT SUM(debit) AS total,date FROM geopos_transactions  inner join geopos_invoices on geopos_transactions.tid=geopos_invoices.id  $whri ((DATE(date) BETWEEN DATE('$year-$month-01') AND '$today') AND type='Expense')  $whr GROUP BY date ORDER BY date DESC");
        return $query->result_array();
    }

    public function countmonthlyChart()
    {
        $today = date('Y-m-d');
        $whr = '';
        $whri = "";
        if ($this->aauth->get_user()->loc) {
            $whr = ' AND (loc=' . $this->aauth->get_user()->loc . ')';
        } elseif (!BDATA) {
            $whr = ' AND (loc=0)';
        }
        if (!$this->aauth->premission(19)) {
            $whri = " where inv_type!='DAYPASS'  AND ";
        } else {
            $whri = " where ";
        }
        $query = $this->db->query("SELECT COUNT(id) AS ttlid,SUM(total) AS total,DATE(invoicedate) as date FROM geopos_invoices  $whri (DATE(invoicedate) BETWEEN '$today' - INTERVAL 30 DAY AND '$today')  $whr GROUP BY DATE(invoicedate) ORDER BY date DESC");
        return $query->result_array();
        // $query = $this->db->query("SELECT COUNT(id) AS ttlid,SUM(total) AS total,DATE(invoicedate) as date FROM geopos_invoices WHERE (DATE(invoicedate) BETWEEN '$today' - INTERVAL 30 DAY AND '$today')  $whr GROUP BY DATE(invoicedate) ORDER BY date DESC");
        // return $query->result_array();
    }


    public function monthlyInvoice($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->where($where);
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->from('geopos_invoices');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }

    public function monthlyPurchaseInvoice($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }
    public function monthlyPurchaseCount($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->where($where);
        $this->db->from('geopos_purchase');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        return $this->db->count_all_results();
    }

    public function monthlySales($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->select('sum(total) - sum(tax) as total');
        $this->db->from('geopos_invoices');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    public function monthlySalesVAT($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->select('sum(tax) as total');
        $this->db->from('geopos_invoices');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function monthlySalesIncVAT($month, $year)
    {

        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $this->db->select('sum(tax) as total');
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days' AND notes != 'Advance Payment' AND total>0";
        $this->db->select('COUNT(*) as total');
        $this->db->from('geopos_invoices');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type =', 'INVOICE');
        }
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }
    public function LastmonthlySalesIncVAT($month, $year)
    {
        // Get the last day of the previous month
        $lastDayOfLastMonth = date('Y-m-d', strtotime('last day of previous month'));

        // Extract the month and year from the last day of the previous month
        $lastMonth = date('m', strtotime($lastDayOfLastMonth));
        $lastYear = date('Y', strtotime($lastDayOfLastMonth));

        // Construct the WHERE clause to filter data for the last month
        $where = "YEAR(invoicedate) = $lastYear AND MONTH(invoicedate) = $lastMonth AND notes != 'Advance Payment' AND total>0";
        $this->db->select('COUNT(*) as total');
        $this->db->from('geopos_invoices');
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }


    public function monthlyPurchase($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->select('sum(total) - sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    public function monthlyPurchaseVAT($month, $year)
    {
        $today = date('Y-m-d');
        $days = date("t", strtotime($today));
        $where = "DATE(invoicedate) BETWEEN '$year-$month-01' AND '$year-$month-$days'";
        $this->db->select('sum(tax) as total');
        $this->db->from('geopos_purchase');
        $this->db->where($where);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }


    public function recentInvoices()
    {
        // Relax filters: ignore location and include all invoice types
        $query = $this->db->query("SELECT i.id,i.tid,i.invoicedate,i.total,i.tax,i.status,i.i_class,c.company as name,c.picture,i.csd
FROM geopos_invoices AS i LEFT JOIN geopos_customers AS c ON i.csd=c.id ORDER BY i.id DESC LIMIT 10");
        // print_r($this->db->last_query());  die();
        return $query->result_array();
    }
    public function recentPurchase()
    {
        $whr = '';

        if ($this->aauth->get_user()->loc) {
            $whr = ' WHERE (i.loc=' . $this->aauth->get_user()->loc . ') ';
        } elseif (!BDATA) {
            $whr = ' WHERE (i.loc=0) ';
        }
        $query = $this->db->query("SELECT i.id,i.tid,i.invoicedate,i.total,i.tax,i.status,c.company as name,c.picture,i.csd
FROM geopos_purchase AS i LEFT JOIN geopos_supplier AS c ON i.csd=c.id $whr ORDER BY i.id DESC LIMIT 10");
        // print_r($this->db->last_query());  die();
        return $query->result_array();
    }

    public function recentBuyers()
    {
        $this->db->trans_start();
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' WHERE (i.loc=' . $this->aauth->get_user()->loc . ') ';
        } elseif (!BDATA) {
            $whr = ' WHERE (i.loc=0) ';
        }
        if (!$this->aauth->premission(19)) {
            if ($whr != '') {
                $whr += ' AND (i.inv_type != "DAYPASS") ';
            } else {
                $whr = ' WHERE (i.inv_type != "DAYPASS") ';
            }
        }

        // $query = $this->db->query("SELECT MAX(i.id) AS iid,i.csd,SUM(i.total) AS total,SUM(i.tax) AS tax, c.cid,MAX(c.picture) as picture ,MAX(c.name) as name,MAX(i.status) as status FROM geopos_invoices AS i LEFT JOIN (SELECT geopos_customers.id AS cid, geopos_customers.picture AS picture, geopos_customers.company AS name FROM geopos_customers) AS c ON c.cid=i.csd $whr GROUP BY i.csd ORDER BY iid DESC LIMIT 10;");
        $query = $this->db->query("SELECT MAX(i.id) AS iid,i.csd,SUM(i.total) AS total,SUM(i.tax) AS tax, c.cid,MAX(c.picture) as picture ,MAX(c.name) as name,MAX(i.status) as status FROM geopos_invoices AS i LEFT JOIN (SELECT geopos_customers.id AS cid, geopos_customers.picture AS picture, geopos_customers.company AS name FROM geopos_customers) AS c ON c.cid=i.csd $whr GROUP BY i.csd ORDER BY total DESC LIMIT 6;");
        $result = $query->result_array();
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return 'sql';
        } else {
            return $result;
        }
    }
    public function recentSupplier()
    {
        $this->db->trans_start();
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' WHERE (i.loc=' . $this->aauth->get_user()->loc . ') ';
        } elseif (!BDATA) {
            $whr = ' WHERE (i.loc=0) ';
        }

        $query = $this->db->query("SELECT MAX(i.id) AS iid,i.csd,SUM(i.total) AS total,SUM(i.tax) AS tax, c.cid,MAX(c.picture) as picture ,MAX(c.name) as name,MAX(i.status) as status FROM geopos_purchase AS i LEFT JOIN (SELECT geopos_supplier.id AS cid, geopos_supplier.picture AS picture, geopos_supplier.company AS name FROM geopos_supplier) AS c ON c.cid=i.csd $whr GROUP BY i.csd ORDER BY iid DESC LIMIT 10;");
        $result = $query->result_array();
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return 'sql';
        } else {
            return $result;
        }
    }

    public function tasks($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_todolist');
        $this->db->where('eid', $id);
        $this->db->limit(10);
        $this->db->order_by('DATE(duedate)', 'ASC');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function recentBuyersLatest()
    {
        // Latest customers by most recent invoice id
        $this->db->trans_start();
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' WHERE (i.loc=' . $this->aauth->get_user()->loc . ') ';
        } elseif (!BDATA) {
            $whr = ' WHERE (i.loc=0) ';
        }
        if (!$this->aauth->premission(19)) {
            if ($whr != '') {
                $whr .= ' AND (i.inv_type != "DAYPASS") ';
            } else {
                $whr = ' WHERE (i.inv_type != "DAYPASS") ';
            }
        }
        $query = $this->db->query("SELECT MAX(i.id) AS iid,i.csd,SUM(i.total) AS total,SUM(i.tax) AS tax, c.id as cid, MAX(c.company) as name, MAX(i.status) as status FROM geopos_invoices AS i LEFT JOIN geopos_customers AS c ON c.id=i.csd $whr GROUP BY i.csd ORDER BY iid DESC LIMIT 6;");
        $result = $query->result_array();
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return 'sql';
        } else {
            return $result;
        }
    }

    // Calendar: due invoices grouped per day with total outstanding amount
    public function dueInvoicesCalendar($startDate, $endDate)
    {
        // Normalize dates
        $start = date('Y-m-d', strtotime($startDate));
        $end   = date('Y-m-d', strtotime($endDate));

        // Build optional location filter
        $locSql = '';
        $params = [$start, $end];
        if ($this->aauth->get_user()->loc) {
            $locSql = ' AND i.loc = ?';
            $params[] = $this->aauth->get_user()->loc;
        } elseif (!BDATA) {
            $locSql = ' AND i.loc = 0';
        }

        // Outstanding = total - pamnt for statuses that are not fully paid
        $sql = "
            SELECT DATE(i.invoicedate) AS date,
                   COUNT(i.id) AS invoice_count,
                   SUM(CASE WHEN (i.total - i.pamnt) > 0 THEN (i.total - i.pamnt) ELSE 0 END) AS total_due
            FROM geopos_invoices i
            WHERE DATE(i.invoicedate) BETWEEN ? AND ?
              AND i.total > 0
              AND i.notes != 'Advance Payment'
              $locSql
            GROUP BY DATE(i.invoicedate)
            ORDER BY DATE(i.invoicedate) ASC
        ";
        $rows = $this->db->query($sql, $params)->result_array();
        return $rows;
    }

    public function clockin($id)
    {
        $this->db->select('clock');
        $this->db->where('id', $id);
        $this->db->from('geopos_employees');
        $query = $this->db->get();
        $emp = $query->row_array();
        if (!$emp['clock']) {
            $data = array(
                'clock' => 1,
                'clockin' => time(),
                'clockout' => 0
            );
            $this->db->set($data);
            $this->db->where('id', $id);
            $this->db->update('geopos_employees');
            $this->aauth->applog("[Employee ClockIn]  ID $id", $this->aauth->get_user()->username);
        }
        return true;
    }

    public function clockout($id)
    {

        $this->db->select('clock,clockin');
        $this->db->where('id', $id);
        $this->db->from('geopos_employees');
        $query = $this->db->get();
        $emp = $query->row_array();

        if ($emp['clock']) {

            $data = array(
                'clock' => 0,
                'clockin' => 0,
                'clockout' => time()
            );

            $total_time = time() - $emp['clockin'];


            $this->db->set($data);
            $this->db->where('id', $id);

            $this->db->update('geopos_employees');
            $this->aauth->applog("[Employee ClockOut]  ID $id", $this->aauth->get_user()->username);

            $today = date('Y-m-d');

            $this->db->select('id,adate');
            $this->db->where('emp', $id);
            $this->db->where('DATE(adate)', date('Y-m-d'));
            $this->db->from('geopos_attendance');
            $query = $this->db->get();
            $edate = $query->row_array();
            if ($edate['adate']) {


                $this->db->set('actual_hours', "actual_hours+$total_time", FALSE);
                $this->db->set('tto', date('H:i:s'));
                $this->db->where('id', $edate['id']);
                $this->db->update('geopos_attendance');
            } else {
                $data = array(
                    'emp' => $id,
                    'adate' => date('Y-m-d'),
                    'tfrom' => gmdate("H:i:s", $emp['clockin']),
                    'tto' => date('H:i:s'),
                    'note' => 'Self Attendance',
                    'actual_hours' => $total_time
                );


                $this->db->insert('geopos_attendance', $data);
            }
        }
        return true;
    }
}
