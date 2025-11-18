<!-- AJAX Content for Customer VAT Report -->

<style type="text/css">
    .ui-autocomplete {
        z-index: 10050 !important;
    }

    .content-wrapper {
        z-index: 0;
    }

    .form-control[readonly] {
        cursor: pointer !important;
    }

    /* Fix calendar z-index to appear above cards */
    .ui-datepicker {
        z-index: 10000 !important;
    }

    .ui-front {
        z-index: 10000 !important;
    }

    .ui-widget {
        z-index: 10000 !important;
    }

    .ui-widget-overlay {
        z-index: 9999 !important;
    }
</style>

<div class="card">

    <div class="card-header" style="margin-top: 10px; background-color:rgb(164, 184, 206); color: #fff; text-align: center;">

        <h2 class="card-title" style="color: #fff;">Customer VAT Report</h2>

    </div>

    <div class="card-content">

        <div id="notify" class="alert alert-success" style="display:none;">

            <a href="#" class="close" data-dismiss="alert">&times;</a>

            <div class="message"></div>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-12">

                    <form action="<?php echo base_url() ?>customers/vatinvoice" method="post" target="_blank" role="form">

                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                        <input type="hidden" name="customer" value="<?php echo $this->input->post('id') ?>" id="customer_id_hidden">



                        <div class="row">

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="sdate"><?php echo $this->lang->line('Start Date') ?></label>

                                    <input type="text" class="form-control" name="sdate" id="sdate" autocomplete="off" readonly required style="background-color:#fff;">

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="edate"><?php echo $this->lang->line('End Date') ?></label>

                                    <input type="text" class="form-control" name="edate" id="edate" autocomplete="off" readonly required style="background-color:#fff;">

                                </div>

                            </div>

                        </div>



                        <div class="row">

                            <div class="col-md-12">

                                <div class="form-group">

                                    <button type="submit" class="btn btn-primary">

                                        <i class="fa fa-file-pdf-o"></i> <?php echo $this->lang->line('Generate VAT Report') ?>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Add accessible title to icon-only "View" buttons if present in this subview
        $('button, a').each(function() {
            var $el = $(this);
            var hasText = $.trim($el.text()).length > 0;
            var hasIcon = $el.find('i.fa, i.fas, i.far, i.fal, i.fab').length > 0;
            if (!hasText && hasIcon && !$el.attr('title')) {
                $el.attr('title', 'View');
                $el.attr('aria-label', 'View');
            }
        });
    });

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
            alert('Please select both Start Date and End Date');
            return false;
        }

        // Validate date range
        var startDate = new Date(sdate.split('-').reverse().join('-'));
        var endDate = new Date(edate.split('-').reverse().join('-'));

        if (startDate > endDate) {
            e.preventDefault();
            alert('Start Date cannot be greater than End Date');
            return false;
        }
    });
</script>