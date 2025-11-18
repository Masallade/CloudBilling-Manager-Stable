<!doctype html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Print Statement</title>

    <style>
        body {
            color: #2B2000;
        }

        table {
            width: 100%;
            line-height: 16pt;
            text-align: right;
            border-collapse: collapse;
        }

        .mfill {
            background-color: #eee;
        }

        .descr {
            font-size: 10pt;
            color: #515151;
        }

        .invoice-box {
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 4mm;
            border: 0;

            font-size: 16pt;
            line-height: 24pt;

            color: #000;
        }

        .invoice-box table {
            width: 100%;
            line-height: 17pt;
            text-align: left;
        }

        .plist {
            border: 1px solid #ddd;
            margin-top: 10pt;
            margin-bottom: 10pt;
        }

        .plist tr td {
            line-height: 12pt;
            padding: 8pt 6pt;
            border: 1px solid #ddd;
            text-align: left;
        }

        .plist tr:first-child td {
            background-color: #515151;
            color: #FFF;
            font-weight: bold;
            text-align: center;
        }

        .subtotal tr td {
            line-height: 10pt;
            padding: 6pt 4pt;
        }

        .sign {
            text-align: right;
            font-size: 10pt;
            margin-right: 110pt;
        }

        .sign1 {
            text-align: right;
            font-size: 10pt;
            margin-right: 90pt;
        }

        .sign2 {
            text-align: right;
            font-size: 10pt;
            margin-right: 115pt;
        }

        .sign3 {
            text-align: right;
            font-size: 10pt;
            margin-right: 115pt;
        }

        .terms {
            font-size: 9pt;
            line-height: 16pt;
        }

        .invoice-box table td {
            padding: 10pt 4pt 5pt 4pt;
            vertical-align: top;

        }

        .invoice-box table tr td:nth-child(2) {
            text-align: left;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20pt;

        }

        .invoice-box table tr.top table td.title {
            font-size: 45pt;
            line-height: 45pt;
            color: #555;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 20pt;
        }

        .invoice-box table tr.heading td {
            background: #515151;
            color: #FFF;
            padding: 6pt;

        }

        .invoice-box table tr.details td {
            padding-bottom: 20pt;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #ddd;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .plist tr.item td {
            border-bottom: 1px solid #ddd;
        }

        .plist tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(4) {
            border-top: 2px solid #fff;
            font-weight: bold;
        }

        .myco {
            width: 500pt;
        }

        .myco2 {
            width: 290pt;
        }

        .myw {
            width: 180pt;
            font-size: 14pt;
            line-height: 30pt;
        }


    </style>
</head>

<body dir="<?= LTR ?>">
<?php
// Helper function to safely get language lines with fallback
function safe_lang($key, $fallback = '') {
    $ci =& get_instance();
    $text = $ci->lang->line($key);
    if ($text === FALSE || empty($text)) {
        return !empty($fallback) ? $fallback : $key;
    }
    return $text;
}
?>
<div class="invoice-box">
    <table>
        <tr>
            <td class="myco">
                <img src="<?php echo base_url('userfiles/company/' . $company['logo']) ?>"
                     style="max-width:260px;">
            </td>
            <td>

            </td>
            <td class="myw">
                <?php echo safe_lang('Product Category', 'Product Category') . ' ' . safe_lang('Statement', 'Statement'); ?>
            </td>
        </tr>
    </table>
    <br>
    <table>
        <thead>
        <tr class="heading">
            <td> <?php echo safe_lang('Our Info', 'Our Info') ?>:</td>

            <td><?php switch ($r_type) {
                    case 1 :
                        echo safe_lang('Sales', 'Sales');
                        break;
                    case 2 :
                        echo safe_lang('Purchase Order', 'Purchase Order');
                        break;
                    case 3 :
                        echo safe_lang('Stock', 'Stock');
                        break;

                }

                echo ' ' . safe_lang('Details', 'Details') ?>:
            </td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><h3><?php echo isset($company['cname']) ? htmlspecialchars($company['cname']) : ''; ?></h3>
           

                <?php 
                $address = isset($company['address']) ? $company['address'] : '';
                $city = isset($company['city']) ? $company['city'] : '';
                $region = isset($company['region']) ? $company['region'] : '';
                $postbox = isset($company['postbox']) ? $company['postbox'] : '';
                $country = isset($company['country']) ? $company['country'] : '';
                $phone = isset($company['phone']) ? $company['phone'] : '';
                $mobile = isset($company['mobile']) ? $company['mobile'] : '';
                $email = isset($company['email']) ? $company['email'] : '';
                
                // Build address line
                $addressParts = array_filter([$address, $city, $region]);
                $addressLine = !empty($addressParts) ? implode(' ', $addressParts) : '';
                
                // Build location line
                $locationParts = array_filter([$postbox, $country]);
                $locationLine = !empty($locationParts) ? implode(', ', $locationParts) : '';
                
                // Build contact line
                $contactParts = [];
                if ($phone) $contactParts[] = 'Phone: ' . $phone;
                if ($mobile) $contactParts[] = 'Mobile: ' . $mobile;
                if ($email) $contactParts[] = 'Email: ' . $email;
                $contactLine = !empty($contactParts) ? implode(', ', $contactParts) : '';
                
                // Combine all lines
                $lines = array_filter([$addressLine, $locationLine, $contactLine]);
                echo !empty($lines) ? implode('<br>', $lines) : '';
                ?>
            </td>

            <td>
                <?php echo safe_lang('Product Category', 'Product Category') . ' : ' . (isset($product['title']) ? htmlspecialchars($product['title']) : '');
                ?>
            </td>
        </tr>
        </tbody>
    </table>
    <hr>
    <table class="plist" cellpadding="0" cellspacing="0">
        <?php if ($r_type < 3) { ?>
            <thead>
            <tr>
                <td><?php echo safe_lang('Date', 'Date') ?></td>
                <td><?php echo safe_lang('Qty', 'Qty') ?></td>
                <td><?php echo safe_lang('Price', 'Price') ?></td>
                <td><?php echo safe_lang('Invoice', 'Invoice') ?>#</td>
                <td><?php echo safe_lang('Total', 'Total') ?></td>
            </tr>
            </thead>
            <tbody>
            <?php
            $fill = false;
            $price = 0;
            $balance = 0; // Reset balance for each report
            if (!empty($report)) {
                foreach ($report as $row) {
                    $balance += $row['qty'];
                    if ($fill == true) {
                        $flag = ' mfill';
                    } else {
                        $flag = '';
                    }

                    $price += $row['qty'] * $row['price'];
                    
                    // Format date with fallback
                    $invoicedate = '';
                    if (isset($row['invoicedate']) && !empty($row['invoicedate'])) {
                        $date_val = trim($row['invoicedate']);
                        if ($date_val && $date_val != '0000-00-00' && $date_val != '0000-00-00 00:00:00') {
                            try {
                                $formatted = dateformat($date_val);
                                $invoicedate = !empty($formatted) ? $formatted : $date_val;
                            } catch (Exception $e) {
                                $timestamp = strtotime($date_val);
                                if ($timestamp !== false && $timestamp > 0) {
                                    $date_format = $this->config->item('dformat');
                                    $invoicedate = $date_format ? date($date_format, $timestamp) : date('d-m-Y', $timestamp);
                                } else {
                                    $invoicedate = $date_val;
                                }
                            }
                        }
                    }
                    
                    $qty = isset($row['qty']) ? amountFormat_general($row['qty']) : '0.00';
                    $price_val = isset($row['price']) ? amountExchange($row['price'], 0, $this->aauth->get_user()->loc) : '0.00';
                    $tid = isset($row['tid']) ? $row['tid'] : '';

                    echo '<tr class="item' . $flag . '">';
                    echo '<td>' . htmlspecialchars($invoicedate) . '</td>';
                    echo '<td style="text-align: right;">' . htmlspecialchars($qty) . '</td>';
                    echo '<td style="text-align: right;">' . htmlspecialchars($price_val) . '</td>';
                    echo '<td style="text-align: center;">' . htmlspecialchars($tid) . '</td>';
                    echo '<td style="text-align: right;">' . htmlspecialchars(amountFormat_general($balance)) . '</td>';
                    echo '</tr>';
                    $fill = !$fill;
                }
            } else {
                echo '<tr><td colspan="5" style="text-align: center; padding: 20pt;">' . safe_lang('No data available', 'No data available') . '</td></tr>';
            }
            ?>
            </tbody>
        <?php } else { ?>
            <thead>
            <tr>
                <td><?php echo safe_lang('Date', 'Date') ?></td>
                <td><?php echo safe_lang('Qty', 'Qty') ?></td>
                <td><?php echo safe_lang('Note', 'Note') ?>#</td>
                <td><?php echo safe_lang('Total', 'Total') ?></td>
            </tr>
            </thead>
            <tbody>
            <?php
            $fill = false;
            $price = 0;
            $balance = 0; // Reset balance for each report
            $in = 1;
            if (!empty($report)) {
                foreach ($report as $row) {
                    $balance += $row['qty'];
                    if ($fill == true) {
                        $flag = ' mfill';
                    } else {
                        $flag = '';
                    }
                    $price += $row['qty'] * $row['price'];
                    
                    $product_name = isset($row['product_name']) ? $row['product_name'] : '';
                    
                    // Format date with fallback
                    $invoicedate = '';
                    if (isset($row['invoicedate']) && !empty($row['invoicedate'])) {
                        $date_val = trim($row['invoicedate']);
                        if ($date_val && $date_val != '0000-00-00' && $date_val != '0000-00-00 00:00:00') {
                            try {
                                $formatted = dateformat($date_val);
                                $invoicedate = !empty($formatted) ? $formatted : $date_val;
                            } catch (Exception $e) {
                                $timestamp = strtotime($date_val);
                                if ($timestamp !== false && $timestamp > 0) {
                                    $date_format = $this->config->item('dformat');
                                    $invoicedate = $date_format ? date($date_format, $timestamp) : date('d-m-Y', $timestamp);
                                } else {
                                    $invoicedate = $date_val;
                                }
                            }
                        }
                    }
                    
                    $qty = isset($row['qty']) ? amountFormat_general($row['qty']) : '0.00';
                    $note = isset($row['note']) ? $row['note'] : '';

                    echo '<tr class="item' . $flag . '">';
                    echo '<td colspan="4" style="font-weight: bold; padding-left: 10pt;">' . $in . '. ' . htmlspecialchars($product_name) . '</td>';
                    echo '</tr>';
                    echo '<tr class="item' . $flag . '">';
                    echo '<td>' . htmlspecialchars($invoicedate) . '</td>';
                    echo '<td style="text-align: right;">' . htmlspecialchars($qty) . '</td>';
                    echo '<td>' . htmlspecialchars($note) . '</td>';
                    echo '<td style="text-align: right;">' . htmlspecialchars(amountFormat_general($balance)) . '</td>';
                    echo '</tr>';
                    $fill = !$fill;
                    $in++;
                }
            } else {
                echo '<tr><td colspan="4" style="text-align: center; padding: 20pt;">' . safe_lang('No data available', 'No data available') . '</td></tr>';
            }
            ?>
            </tbody>
        <?php } ?>
    </table>
    <table class="subtotal">
        <thead>
        <tbody>
        <tr>
            <td class="myco2" rowspan="3"><br><br><br>

            </td>
            <td><strong><?php echo safe_lang('Summary', 'Summary') ?>:</strong></td>
            <td></td>


        </tr>
        <tr>


            <td><?php echo safe_lang('Total', 'Total') . ' ' . safe_lang('Products', 'Products') ?>:</td>

            <td><?php echo amountFormat_general($balance); ?></td>
        </tr>
        <tr>


            <td><?php echo safe_lang('Total', 'Total') ?>:</td>

            <td><?php echo amountExchange($price, 0, $this->aauth->get_user()->loc); ?></td>
        </tr>

        </tbody>
    </table>
    <br>
    <div class="sign">Authorized person</div>
    <div class="sign1"></div>
    <div class="sign2"></div>
    <div class="sign3"></div>
    <br>
    <div class="terms">
        <hr>

    </div>
</div>
</body>
</html>
