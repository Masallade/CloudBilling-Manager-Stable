<?php
// Generate PDF for quotation using the same template as invoices
$company = $this->settings->company_details(1);
$tid = $this->input->get('id');

// Get quotation details
$quotation = $this->quotations->quotation_details($tid, $this->limited);
$products = $this->quotations->quotation_products($tid);
$employee = $this->quotations->employee($quotation['eid']);

// Get customer details
$customer_id = $quotation['csd'];
$customer = $this->db->get_where('geopos_customers', array('id' => $customer_id))->row_array();

// Clear output buffer
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Get custom data
$sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $customer_id . '"';
$query2 = $this->db->query($sql2);
$response2 = $query2->result_array();
$custom_fields = isset($response2[0]['data']) ? $response2[0]['data'] : '';

// Mark as printed if needed
$d = $this->input->get('d');
if ($d == '1' || $d == 1) {
    $sql = 'Update geopos_quotation set print_status=1 where id= ' . $tid . ' ';
    $this->db->query($sql);
}

ini_set('memory_limit', '64M');

$left_logo = base_url("userfiles/company/" . $company['logo']);

// Prepare customer billing information
$bill_to_lines = '';

// Check for company name in quotation or customer record
$company_name = !empty($quotation['cust_name']) ? $quotation['cust_name'] : 
                (!empty($customer['company']) ? $customer['company'] : 
                (!empty($customer['name']) ? $customer['name'] : ''));

if (!empty($company_name)) {
    $bill_to_lines .= '<strong>' . htmlspecialchars($company_name) . '</strong><br>';
}

// Address from quotation table or customer table
$address_line_1 = !empty($quotation['cust_address']) ? trim($quotation['cust_address']) : 
                  (!empty($customer['address']) ? trim($customer['address']) : '');
$address_line_2 = !empty($quotation['cust_city']) ? trim($quotation['cust_city']) : 
                  (!empty($customer['city']) ? trim($customer['city']) : '');
$postcode_value = !empty($quotation['cust_postcode']) ? trim($quotation['cust_postcode']) : 
                  (!empty($customer['postcode']) ? trim($customer['postcode']) : '');

if ($address_line_1 !== '') {
    $bill_to_lines .= htmlspecialchars($address_line_1);
    if ($address_line_2 !== '') {
        $bill_to_lines .= ', ' . htmlspecialchars($address_line_2);
    }
    if ($postcode_value !== '') {
        $bill_to_lines .= '<br>' . htmlspecialchars($postcode_value);
    }
}

// Sales Tax and NTN
$sales_tax_value = '';
if (!empty($customer)) {
    foreach (['sales_tax', 'sales_tax_no', 'gst', 'vat_no', 'strn', 'stn'] as $key) {
        if (!empty($customer[$key])) {
            $sales_tax_value = (string)$customer[$key];
            break;
        }
    }
}
$ntn_value = '';
if (!empty($customer)) {
    foreach (['ntn', 'ntn_no', 'taxid'] as $key) {
        if (!empty($customer[$key])) {
            $ntn_value = (string)$customer[$key];
            break;
        }
    }
}
if ($sales_tax_value === '') {
    $sales_tax_value = '32-77-8761-291-58';
}
if ($ntn_value === '') {
    $ntn_value = '7222516-7';
}

$bill_to_lines .= '<br><span class="tax-info">Sales Tax # ' . htmlspecialchars($sales_tax_value) . '</span>';
$bill_to_lines .= '<br><span class="tax-info">NTN # ' . htmlspecialchars($ntn_value) . '</span>';

$reference_no = isset($quotation['refer']) ? $quotation['refer'] : '';

// Calculate totals
$subtotal = 0;
$total_tax = 0;

if (is_array($products) && count($products) > 0) {
    foreach ($products as $row) {
        $qty = isset($row['qty']) ? (float)$row['qty'] : 0;
        $price = isset($row['price']) ? (float)$row['price'] : 0;
        
        if ($qty > 0) {
            $line_total = $qty * $price;
            $subtotal += $line_total;
            
            if (isset($row['tax']) && $row['tax'] > 0) {
                $total_tax += ($line_total * $row['tax'] / 100);
            }
        }
    }
}

// Use totals from quotation table if available
if (isset($quotation['subtotal']) && $quotation['subtotal'] > 0) {
    $subtotal = (float)$quotation['subtotal'];
}
if (isset($quotation['tax']) && $quotation['tax'] > 0) {
    $total_tax = (float)$quotation['tax'];
}
if (isset($quotation['total']) && $quotation['total'] > 0) {
    $grand_total = (float)$quotation['total'];
} else {
    $grand_total = $subtotal + $total_tax;
}

// Calculate valid until date
$valid_until = !empty($quotation['until']) ? 
               date('d M Y', strtotime($quotation['until'])) : 
               date('d M Y', strtotime($quotation['quotedate'] . ' +30 days'));

// Start HTML structure with CSS
$html = '<html><head><style>
    * { font-family: "Open Sans", sans-serif; }
    body { font-size: 8pt; color: #333333; }
    @page { margin: 0.7in 0.4in 0.7in 0.55in; }
    table { width: 100%; border-collapse: collapse; }
    footer.doc-footer { position: fixed; bottom: -0.7in; left: 0; right: 0; height: 0.4in; text-align: right; padding: 0 20px; color: #aaaaaa; font-size: 6pt; background: #ffffff; }
    footer.doc-footer .page-count:after { content: "Page " counter(page) " of " counter(pages); }
    .page-header { margin-bottom: 10px; }
    .doc-meta { margin-top: 10px; margin-bottom: 20px; }
    .doc-meta .title-row { width:100%; margin: 6px 0 2px; }
    .doc-meta .title-cell { width:20%; text-align:center; font-weight:200; }
    .customer-details { font-size: 9pt; color: #333333; line-height:1.6; }
    .tax-info { font-size: 9pt; color: #333333; }
    .title { font-size: 11pt; letter-spacing: 1px; color: #333333; }
    .items { border: 1px solid #d8d8d7; margin-top: 0; }
    .items th, .items td { border: 1px solid #d8d8d7; padding: 6px; font-size: 8pt; color: #333333; }
    .items thead th { background: #F5F6F7; color: #333333; font-size: 8pt; font-weight: 600; }
    .items tbody td { background: #ffffff; font-size: 8pt; color: #333333; }
    .meta-table { width: auto; margin-left: auto; border-collapse: collapse; table-layout: auto; }
    .meta-table td { padding: 2px 6px; font-size: 9pt; color: #333333; }
    .meta-table td.value { text-align: left; white-space: nowrap; }
    .org-name { font-weight: 700; font-size: 9pt; color: #333333; }
    .org-details { font-size: 8pt; color: #333333; }
    .customer-label { font-weight: 700; font-size: 9pt; color: #333333; margin-bottom: 6px; }
    .totals-box { 
        padding: 10px; 
        margin-top: 20px; 
        width: 300px; 
        float: right; 
        font-size: 8pt;
        color: #333333;
    }
    .total-row { 
        display: table; 
        width: 100%; 
        margin-bottom: 5px; 
    }
    .total-label { 
        display: table-cell; 
        font-weight: bold; 
        width: 60%; 
    }
    .total-value { 
        display: table-cell; 
        text-align: right; 
        font-weight: bold; 
    }
    .grand-total { 
        border-top: 1px solid #333; 
        padding-top: 5px; 
        margin-top: 5px; 
    }
    .page-break { page-break-after: always; }
</style></head><body>';

// Store header HTML in a variable for reuse
$header_html = '<div class="page-header">
    <table style="width:100%; margin-bottom:5px;"><tr>
        <td style="width:45%; vertical-align:top;">
            <img src="' . $left_logo . '" height="80" style="vertical-align:top;">
        </td>
        <td style="width:55%; text-align:right; vertical-align:top;">
            <div class="org-name">' . htmlspecialchars($company['cname']) . '</div>
            <div class="org-details">
            ' . htmlspecialchars($company['address']) . '<br>
            ' . htmlspecialchars($company['city']) . ', ' . htmlspecialchars($company['country']) . '<br>
            Mob: ' . htmlspecialchars($company['mobile']) . ' | Tel: ' . htmlspecialchars($company['phone']) . '<br>
            Email: ' . htmlspecialchars($company['email']) . '<br>
            Web: ' . htmlspecialchars($company['website'] ?? 'www.aaico.pk') . '<br>
            <strong>NTN: ' . htmlspecialchars($ntn_value) . '</strong>
            </div>
        </td>
    </tr></table>
    <div class="doc-meta">
        <table class="title-row">
            <tr>
                <td style="width:40%;"><hr style="margin:0; border:none; border-top:2px solid #dcdcdc;"></td>
                <td class="title-cell"><span class="title">QUOTATION</span></td>
                <td style="width:40%;"><hr style="margin:0; border:none; border-top:2px solid #dcdcdc;"></td>
            </tr>
        </table>
        <table style="margin-top:10px; width:100%; table-layout:fixed;">
            <tr>
                <td style="width:58%; vertical-align:top; padding-right:12px;">
                    <div style="padding:8px;">
                        <div class="customer-label">Principal</div>
                        <div class="customer-details">' . $bill_to_lines . '</div>
            </div>
                </td>
                <td style="width:42%; vertical-align:top;">
                    <table class="meta-table">
                        <tr>
                            <td style="font-weight:600;">Quote No:</td>
                            <td class="value">' . htmlspecialchars($quotation['tid']) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Quote Date</td>
                            <td class="value" style="white-space:nowrap;">' . date('d M Y', strtotime($quotation['quotedate'])) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Valid Until</td>
                            <td class="value" style="white-space:nowrap;">' . $valid_until . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">P.O. #</td>
                            <td class="value" style="white-space:nowrap;">' . htmlspecialchars($reference_no) . '</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </div>
</div>';

// Add header for first page
$html .= $header_html;

// Start items table
$html .= '<table class="items">
            <thead>
                <tr>
                <th style="width:6%; text-align:center;">#</th>
                <th style="text-align:left;">Item & Description</th>
                <th style="width:8%; text-align:center;">Qty</th>
                <th style="width:12%; text-align:right;">Rate</th>
                <th style="width:13%; text-align:right;">Amount</th>
                </tr>
            </thead>
        <tbody>';

$rownum = 0;
$items_on_page = 0;

// Loop through products
foreach ($products as $row) {
    if ($row['qty'] > 0) {
        $rownum++;
        $items_on_page++;

        $item_name = !empty($row['product_des']) ? $row['product_des'] : ($row['product'] ?? 'Product');
        $unit = isset($row['unit']) ? ' ' . $row['unit'] : '';
        $rate = amountExchange($row['price'], 0, $this->aauth->get_user()->loc);
        $amount = amountExchange($row['qty'] * $row['price'], 0, $this->aauth->get_user()->loc);

        $html .= '<tr>'
            . '<td style="text-align:center;">' . $rownum . '</td>'
            . '<td style="text-align:left;">' . htmlspecialchars($item_name) . '</td>'
            . '<td style="text-align:center;">' . $row['qty'] . $unit . '</td>'
            . '<td style="text-align:right;">' . $rate . '</td>'
            . '<td style="text-align:right;">' . $amount . '</td>'
            . '</tr>';

        // Force a page break after 15 rows
        if ($items_on_page == 15) {
            $html .= '</tbody></table>';
            $html .= '<div class="page-break"></div>';

            // Add header for next page
            $html .= $header_html;

            // Restart items table
            $html .= '<table class="items">
                <thead>
                    <tr>
                        <th style="width:6%; text-align:center;">#</th>
                        <th style="text-align:left;">Item & Description</th>
                        <th style="width:8%; text-align:center;">Qty</th>
                        <th style="width:12%; text-align:right;">Rate</th>
                        <th style="width:13%; text-align:right;">Amount</th>
                </tr>
                </thead>
                <tbody>';

            $items_on_page = 0;
        }
    }
}

if ($rownum === 0) {
    $html .= '<tr><td colspan="5" style="text-align:center; color:#777;">No items found</td></tr>';
}

$html .= '</tbody></table>';

// Add terms if available
if (!empty($custom_fields)) {
    $html .= '<div style="margin-top:10px; font-size:8pt; color:#333333;">
        <strong>Terms:</strong> ' . htmlspecialchars($custom_fields) . '
    </div>';
}

if (!empty($quotation['pamt_terms'])) {
    $html .= '<div style="margin-top:10px; font-size:8pt; color:#333333;">
        <strong>Terms:</strong> ' . htmlspecialchars($quotation['pamt_terms']) . '
    </div>';
}

$html .= '<div style="margin-top:10px; font-size:8pt; color:#333333; clear:both;">Thanks for your business.</div>';

// Totals section
$html .= '<div class="totals-box">
            <div class="total-row">
        <div class="total-label">Sub Total</div>
        <div class="total-value">' . amountExchange($subtotal, 0, $this->aauth->get_user()->loc) . '</div>
            </div>
            <div class="total-row">
        <div class="total-label">Total Taxable Amount</div>
        <div class="total-value">' . amountExchange($subtotal, 0, $this->aauth->get_user()->loc) . '</div>
    </div>';

if ($total_tax > 0) {
    $html .= '<div class="total-row">
        <div class="total-label">GST (18%)</div>
        <div class="total-value">' . amountExchange($total_tax, 0, $this->aauth->get_user()->loc) . '</div>
    </div>';
}

$html .= '<div class="total-row grand-total">
        <div class="total-label">Total</div>
        <div class="total-value">PKR ' . amountExchange($grand_total, 0, $this->aauth->get_user()->loc) . '</div>
    </div>
    <div class="total-row">
        <div class="total-label">Payment Made</div>
        <div class="total-value">(-) ' . amountExchange($grand_total, 0, $this->aauth->get_user()->loc) . '</div>
        </div>
    <div class="total-row grand-total">
        <div class="total-label">Balance Due</div>
        <div class="total-value">PKR 0.00</div>
    </div>
</div>';

// Footer with page numbers
$html .= '<footer class="doc-footer"><span class="page-count"></span></footer>';

$html .= '</body></html>';

// Generate PDF
$file_name = $tid;

$this->dpdf->loadHtml($html);
$this->dpdf->render();
$output = $this->dpdf->output();
$file_location = 'userfiles/invoices/quote_' . $file_name . '.pdf';
file_put_contents($file_location, $output);

// Show in browser for preview
$this->dpdf->stream("quotation_" . $file_name . ".pdf", array("Attachment" => 0));
?>
