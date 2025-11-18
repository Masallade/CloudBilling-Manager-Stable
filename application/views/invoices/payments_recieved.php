<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Payments Received</h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
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
                <div class="row">
                    <div class="col-md-2">Invoice Date Between:</div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2 text-left">
                        <input type="button" name="break_down_report" id="break_down_report" value="Print Report" class="btn btn-info btn-sm" />
                        <input type="button" name="export_report" id="export_report" value="Export Report" class="btn btn-success btn-sm" />
                    </div>
                </div>
                <hr>
            </div>
        </div>
        <div id="MyInvoices" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
    </div>
</div>

<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo $this->lang->line('Delete Invoice'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('Delete this invoice?'); ?></p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete-confirm"><?php echo $this->lang->line('Delete'); ?></button>
                <button type="button" data-dismiss="modal" class="btn"><?php echo $this->lang->line('Cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<form method="POST" id="form_for_export" action="<?php echo site_url('invoices/payments_recieved_report'); ?>" target="_blank">
    <input type="hidden" id="hidden_start_date" name="hidden_start_date" value="" />
    <input type="hidden" id="hidden_end_date" name="hidden_end_date" value="" />
    <input type="hidden" id="hidden_start_category" name="hidden_start_category" value="" />
    <input type="hidden" id="hidden_end_category" name="hidden_end_category" value="" />
    <input type="hidden" id="hidden_fields" name="hidden_fields" value="" />
    <input type="hidden" id="hidden_daypass_check" name="hidden_daypass_check" value="" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $('#break_down_report').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            $('#hidden_start_date').val(start_date);
            $('#hidden_end_date').val(end_date);
            
            if ($('#daypass_check').is(':checked')) {
                $('#hidden_daypass_check').val(1);
            } else {
                $('#hidden_daypass_check').val(0);
            }
            
            $('#form_for_export').submit();
        });

        $('#export_report').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            $('#hidden_start_date').val(start_date);
            $('#hidden_end_date').val(end_date);
            $('#form_for_export').append('<input type="hidden" name="excel" value="1" />');
            $('#form_for_export').submit();
        });

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var filter_check = $(location).attr("href").split('/').pop();

        if (filter_check === "monthly") {
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            end_date = day + '-' + (month < 10 ? '0' : '') + month + '-' + d.getFullYear();
            start_date = '1' + '-' + (month < 10 ? '0' : '') + month + '-' + d.getFullYear();
            $('#start_date').val(start_date);
            $('#end_date').val(end_date);
            draw_data(start_date, end_date);
        }

        $('#search').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var start_cat = $('#start_cat').val();
            var end_cat = $('#end_cat').val();

            if (start_date !== '' && end_date !== '') {
                $('#hidden_start_date').val(start_date);
                $('#hidden_end_date').val(end_date);
                $('#hidden_start_category').val(start_cat);
                $('#hidden_end_category').val(end_cat);

                $('#invoices').DataTable().destroy();
                draw_data(start_date, end_date, start_cat, end_cat);
            } else {
                alert("Date range is required.");
            }
        });
    });
</script>
