<style type="text/css">
    .ui-autocomplete {
        z-index: 9999 !important;
    }

    .content-wrapper {
        z-index: 0;
    }

    .form-control[readonly] {
        cursor: pointer !important;
    }

    /* Fix calendar z-index to appear above cards */
    .ui-datepicker,
    .ui-front,
    .ui-widget {
        z-index: 10000 !important;
    }

    .ui-widget-overlay {
        z-index: 9999 !important;
    }

    /* Statement form styling */
    .statement-form {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .btn-group {
        margin-top: 20px;
    }

    .btn {
        margin-right: 10px;
    }
</style>

<div class="content-body">
    <div class="card">
        <div class="card-header" style="margin-top: 10px; background-color:rgb(164, 184, 206); color: #fff; text-align: center;">

            <h2 class="card-title" style="color: #fff;">Customer Account Statement</h2>

        </div>
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body statement-form">
                <form action="<?php echo base_url() ?>customers/statement" method="post" target="_blank" role="form">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="customer" value="<?= $this->input->post('id') ?>" id="customer_id_hidden">

                    <!-- Transaction Type -->
                    <div class="form-group">
                        <label for="trans_type"><?php echo $this->lang->line('Transaction Type') ?></label>
                        <select name="trans_type" class="form-control" id="trans_type">
                            <option value='All'><?php echo $this->lang->line('All Transactions') ?></option>
                            <option value='Expense'><?php echo $this->lang->line('Debit') ?></option>
                            <option value='Income'><?php echo $this->lang->line('Credit') ?></option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sdate"><?php echo $this->lang->line('From Date') ?></label>
                                <input type="date" class="form-control" name="sdate" id="sdate" autocomplete="off" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edate"><?php echo $this->lang->line('To Date') ?></label>
                                <input type="date" class="form-control" name="edate" id="edate" autocomplete="off" required value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-file-pdf-o"></i> <?php echo $this->lang->line('View in PDF') ?>
                        </button>
                        <button type="submit" name="excel" class="btn btn-success btn-lg">
                            <i class="fa fa-file-excel-o"></i> <?php echo $this->lang->line('View in Excel') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        // Form validation
        $(document).on('submit', 'form', function(e) {
            var sdate = $('#sdate').val();
            var edate = $('#edate').val();
            var customer = $('#customer_id_hidden').val();

            if (!customer) {
                e.preventDefault();
                alert('Customer ID is missing. Please refresh the page and try again.');
                return false;
            }

            if (!sdate || !edate) {
                e.preventDefault();
                alert('Please select both From Date and To Date');
                return false;
            }

            // Validate date range
            var startDate = new Date(sdate.split('-').reverse().join('-'));
            var endDate = new Date(edate.split('-').reverse().join('-'));

            if (startDate > endDate) {
                e.preventDefault();
                alert('From Date cannot be greater than To Date');
                return false;
            }
        });
    </script>