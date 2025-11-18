<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Selected Products</h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize" onclick="focusMethod()"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('products/product_edit_log') ?>">

                    <button type="submit" class="btn btn-success sub-btn" style="float: right; margin-bottom:30px;">Update</button>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                    <div id="saman-row">
                        <table class="table-responsive tfr">

                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">

                                    <th width="26%" class="text-center"> Product Name</th>
                                    <th width="15%" class="text-center"> Purchase Price</th>

                                    <th width="5%" class="text-center">Sale Price <?php //echo $this->lang->line('Rate') 
                                                                                    ?></th>
                                    <!--  <th width="10%" class="text-center">Quantity</th>-->

                                    <th width="5%" class="text-center"><?php echo $this->lang->line('Action') ?></th>
                                </tr>

                            </thead>
                            <tbody>


                                <?php foreach ($product as $key) { ?>
                                    <tr>

                                        <input type="hidden" name="product_id[]" value="<?php echo $key['pid'] ?>">
                                        <input type="hidden" name="product_name[]" value="<?php echo $key['product_name'] ?>">

                                        <td><input type="text" class="form-control inputs" name="product_des[]"
                                                placeholder="<?php echo 'Enter Product Code'; ?>"
                                                id='product_des' value="<?php echo $key['product_des'] ?>">
                                        </td>
                                        <td><input type="text" class="form-control inputs" name="product_price[]"
                                                placeholder="<?php echo 'Enter Product Price'; ?>"
                                                id='productprice' value="<?php echo $key['product_price'] ?>">
                                        </td>
                                        <td><input type="text" class="form-control inputs" name="fproduct_price[]"
                                                placeholder="<?php echo 'Enter fProduct price'; ?>"
                                                id='fproduct_price' value="<?php echo $key['fproduct_price'] ?>">
                                        </td>
                                        <input type="hidden" class="form-control inputs" name="product_qty[]"
                                            placeholder="<?php echo 'Enter Product Quantity'; ?>"
                                            id='productqty' value="<?php echo $key['qty'] ?>">



                                        <td class="text-center"><button type="button" data-rowid="<?= $i; ?>" class=" btn-danger removeProd" style="margin-bottom: 2px;" title="Remove"> <i class="fa fa-minus-square"></i> </button> </td>
                                    </tr>
                                <?php } ?>



                            </tbody>
                        </table>


                    </div>


                </form>
            </div>

        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        document.title = 'Edit Selected Products';


        $('.inputs').keydown(function(e) {
            if (e.which === 13) {
                var index = $('.inputs').index(this) + 1;
                $('.inputs').eq(index).focus().select();
                $('html, body').animate({
                    scrollTop: $(window).scrollTop() + 10
                });
                event.preventDefault();
                return false;
            }
        });
    });

    focusMethod = function getFocus() {
        document.getElementById("customer-box").focus();
    }
</script>
<style type="text/css">
    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background: #6693bc !important;
        font-weight: bold !important;
        color: #ffffff !important;
    }

    .ui-autocomplete {
        z-index: 9999;
    }

    .content-wrapper {

        z-index: 0;
    }
</style>