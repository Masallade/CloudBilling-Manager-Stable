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
<h5><?php echo isset($product['product_name']) ? htmlspecialchars($product['product_name']) : ''; ?> <?php echo isset($product['title']) ? '(' . htmlspecialchars($product['title']) . ')' : ''; ?></h5>

<table class="table table-striped table-bordered">
    <tr>
        <td><?php echo isset($product['product_name']) ? htmlspecialchars($product['product_name']) : ''; ?></td>
        <td><?php echo safe_lang('Code', 'Code') . ' : ' . (isset($product['product_code']) ? htmlspecialchars($product['product_code']) : ''); ?></td>
        <td>
            <?php echo safe_lang('Stock', 'Stock') . ' : ' . (isset($product['qty']) ? amountFormat_general($product['qty']) : '0.00'); ?>
            <br><br>
            <?php if ($this->aauth->premission(25)): ?>
                <a href="<?php echo base_url('products/edit?id=' . $product['pid']); ?>" class="btn btn-primary btn-sm">
                    <span class="icon-pencil"></span> <?php echo safe_lang('Edit', 'Edit'); ?>
                </a>
            <?php endif; ?>
            <div class="btn-group">
                <button type="button" class="btn btn-blue dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-print"></i> <?php echo safe_lang('Print', 'Print'); ?>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?php echo base_url('products/barcode?id=' . $product['pid']); ?>" target="_blank">
                        <?php echo safe_lang('BarCode', 'BarCode'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo base_url('products/posbarcode?id=' . $product['pid']); ?>" target="_blank">
                        <?php echo safe_lang('BarCode', 'BarCode') . ' - Compact'; ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo base_url('products/label?id=' . $product['pid']); ?>" target="_blank">
                        <?php echo safe_lang('Product', 'Product') . ' ' . safe_lang('Label', 'Label'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo base_url('products/poslabel?id=' . $product['pid']); ?>" target="_blank">
                        <?php echo safe_lang('Label', 'Label') . ' - Compact'; ?>
                    </a>
                </div>
            </div>
            <a class="btn btn-pink btn-sm" href="<?php echo base_url('products/report_product?id=' . $product['pid']); ?>" target="_blank">
                <span class="icon-pie-chart2"></span> <?php echo safe_lang('Sales', 'Sales'); ?>
            </a>
        </td>
    </tr>
</table>



<?php if (!empty($product_variations)): ?>
    <h6><?php echo safe_lang('Products', 'Products') . ' ' . safe_lang('Variations', 'Variations'); ?></h6>
    <table class="table table-striped table-bordered">
        <?php foreach ($product_variations as $product_variation): ?>
            <tr>
                <td>
                    <a href="<?php echo base_url('products/edit?id=' . $product_variation['pid']); ?>" class="btn btn-primary btn-sm">
                        <span class="icon-pencil"></span> <?php echo safe_lang('Edit', 'Edit'); ?>
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-blue dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-print"></i> <?php echo safe_lang('Print', 'Print'); ?>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?php echo base_url('products/barcode?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('BarCode', 'BarCode'); ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('products/posbarcode?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('BarCode', 'BarCode') . ' - Compact'; ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('products/label?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('Product', 'Product') . ' ' . safe_lang('Label', 'Label'); ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('products/poslabel?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('Label', 'Label') . ' - Compact'; ?>
                            </a>
                        </div>
                    </div>
                    <a class="btn btn-pink btn-sm" href="<?php echo base_url('products/report_product?id=' . $product_variation['pid']); ?>" target="_blank">
                        <span class="icon-pie-chart2"></span> <?php echo safe_lang('Sales', 'Sales'); ?>
                    </a>
                    <?php echo isset($product_variation['product_name']) ? htmlspecialchars($product_variation['product_name']) : ''; ?>
                </td>
                <td><?php echo safe_lang('Code', 'Code') . ' : ' . (isset($product_variation['product_code']) ? htmlspecialchars($product_variation['product_code']) : ''); ?></td>
                <td><?php echo safe_lang('Stock', 'Stock') . ' : ' . (isset($product_variation['qty']) ? amountFormat_general($product_variation['qty']) : '0.00'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>



<?php if (!empty($product_warehouse)): ?>
    <h6><?php echo safe_lang('Warehouse', 'Warehouse'); ?></h6>
    <table class="table table-striped table-bordered">
        <?php foreach ($product_warehouse as $product_variation): ?>
            <tr>
                <td>
                    <a href="<?php echo base_url('products/edit?id=' . $product_variation['pid']); ?>" class="btn btn-primary btn-sm">
                        <span class="icon-pencil"></span> <?php echo safe_lang('Edit', 'Edit'); ?>
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-blue dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-print"></i> <?php echo safe_lang('Print', 'Print'); ?>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?php echo base_url('products/barcode?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('BarCode', 'BarCode'); ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('products/posbarcode?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('BarCode', 'BarCode') . ' - Compact'; ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('products/label?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('Product', 'Product') . ' ' . safe_lang('Label', 'Label'); ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('products/poslabel?id=' . $product_variation['pid']); ?>" target="_blank">
                                <?php echo safe_lang('Label', 'Label') . ' - Compact'; ?>
                            </a>
                        </div>
                    </div>
                    <a class="btn btn-pink btn-sm" href="<?php echo base_url('products/report_product?id=' . $product_variation['pid']); ?>" target="_blank">
                        <span class="icon-pie-chart2"></span> <?php echo safe_lang('Sales', 'Sales'); ?>
                    </a>
                    <?php echo isset($product_variation['product_name']) ? htmlspecialchars($product_variation['product_name']) : ''; ?>
                </td>
                <td><?php echo safe_lang('Code', 'Code') . ' : ' . (isset($product_variation['product_code']) ? htmlspecialchars($product_variation['product_code']) : ''); ?></td>
                <td><?php echo isset($product_variation['title']) ? htmlspecialchars($product_variation['title']) : ''; ?></td>
                <td><?php echo safe_lang('Stock', 'Stock') . ' : ' . (isset($product_variation['qty']) ? amountFormat_general($product_variation['qty']) : '0.00'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<hr>



