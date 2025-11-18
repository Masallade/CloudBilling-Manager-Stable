<style>
    .multiselect-container {

        width: 210px;
    }
</style>

<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Sales Report By Product </h4>
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

                    <div class="col-md-3">Invoice Date Between:</div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" id="start_date"
                            class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                            autocomplete="off" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" />
                    </div>


                </div>


                <div class="row" style="margin-top:10px;">

                    <div class="col-md-3"> Product Category:</div>

                    <div class="col-md-4">
                        <select multiple id="chkveg" class="form-control">

                            <?php foreach ($categories as $var => $categories) { ?>


                                <option value="<?php echo $categories['id'] ?>"><?php echo $categories['title'] ?></option><?php } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Set Criteria" class="btn btn-info btn-sm" />
                        <!-- <button class="btn btn-success btn-sm select-all">Select All</button> -->

                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-md-2"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-7 text-right">
                        <input type="button" name="stock_adjustment_export_btn " id="stock_adjustment_export_btn" value="Export" class="btn btn-info" />
                        <input type="button" name="break_down_report " id="break_down_report" value="Sale By Product" class="btn btn-info" />
                        <input type="button" name="dbreak_down_report" id="dbreak_down_report" value="Daily Breakdown Sheet" class="btn btn-info" />
                    </div>


                </div>

                <hr>
                <table id="reports" class="table table-striped table-bordered zero-configuration ">
                    <thead>
                        <tr>
                            <th> Inv #</th>
                            <th><?php echo 'A/C'; ?></th>
                            <th><?php echo 'Name'; ?></th>
                            <th><?php echo 'Invoice Date'; ?></th>
                            <th><?php echo $this->lang->line('Amount'); ?> (GBP)</th>
                            <th><input type="checkbox" id="checkAll">&nbsp; ALL </th>
                        </tr>
                    </thead>
                    <tbody id="reports_body">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th> Inv #</th>
                            <th><?php echo 'A/C'; ?></th>
                            <th><?php echo 'Name'; ?></th>
                            <th><?php echo 'Invoice Date'; ?></th>
                            <th><?php echo $this->lang->line('Amount'); ?> (GBP)</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="Myreports" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
    </div>
</div>


<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Delete Invoice') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('delete this invoice') ?> ?</p>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="reports/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                    id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal"
                    class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>
<!--change added by me-->
<form method="POST" id="form_for_export" action="<?php echo site_url('reports/stock_report_export') ?>" target="_blank">


    <input type="hidden" id="hidden_start_date" name="hidden_start_date"
        value="" />
    <input type="hidden" id="hidden_end_date" name="hidden_end_date"
        value="" />
    <input type="hidden" id="hidden_start_category" name="hidden_start_category"
        value="" />
    <input type="hidden" id="hidden_end_category" name="hidden_end_category"
        value="" />
    <input type="hidden" id="categories" name="categories_name"
        value="" />

    <!-- <input type="hidden" id="categories_name" name="categories_name"  
     value=""/> -->

    <input type="hidden" id="hidden_fields" name="hidden_fields" value="" />
    <input type="hidden" id="unposted_fields" name="unposted_fields" value="" />
    <input type="hidden" id="export_format" name="export_format" value="pdf" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>


<form method="POST" id="dform_for_export" action="<?php echo site_url('reports/daily_break_down_sheet_export') ?>" target="_blank">
    <input type="hidden" id="dhidden_fields" name="hidden_fields"
        value="" />



    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
</form>

<script type="text/javascript">
    $(document).ready(function() {


        $('#chkveg').select2({
            includeSelectAllOption: true
        });

        var selectAllOption = new Option("Select All", "selectAll");
        $("#chkveg").prepend(selectAllOption).trigger("change");

        // Handle the click event for the "Select All" option
        $("#chkveg").on("select2:select", function(e) {
            if (e.params.data.id === "selectAll") {
                var options = $("#chkveg").find("option");
                options.prop("selected", false);
                $("#chkveg").trigger("change");

                var options = $("#chkveg").find("option:not(:contains('Select All'))");
                options.prop("selected", true);
                $("#chkveg").trigger("change");
            }
        });

        //  $('#categories_name').val($('#chkveg').name());

        $(document).ready(function() {
            function prepareExport(exportType) {
                var fields = [];
                var start_cat = $('#start_cat').val();
                var end_cat = $('#end_cat').val();
                $('#hidden_start_category').val(start_cat);
                $('#hidden_end_category').val(end_cat);

                var start_date = $('#hidden_start_date').val();
                var end_date = $('#hidden_end_date').val();
                if (start_date === "") {
                    start_date = $('#start_date').val();
                    end_date = $('#end_date').val();
                }

                $("input:checkbox[name=receipt]:checked").each(function() {
                    fields.push($(this).val());
                });
                $('#hidden_fields').val(fields);

                if (fields.length === 0) {
                    alert("Please select at least one product.");
                    return;
                }

                $('#export_format').val(exportType);
                $('#form_for_export').submit();
            }

            // Export PDF
            $('#break_down_report').click(function() {
                prepareExport('pdf');
            });

            // Export Excel
            $('#stock_adjustment_export_btn').click(function() {
                prepareExport('excel');
            });
        });

        // $('#break_down_report').click(function() {
        //     //change names here
        //     var fields = [];
        //     var upfields = [];




        //     // var start_cat = document.getElementById("start_cat").value;
        //     // var end_cat = document.getElementById("end_cat").value;
        //     //  $('#hidden_start_category').val(start_cat);
        //     // $('#hidden_end_category').val(end_cat);



        //     var start_date = document.getElementById("hidden_start_date").value;
        //     var end_date = document.getElementById("hidden_end_date").value;

        //     if (start_date == "") {
        //         start_date = $('#start_date').val();
        //         end_date = $('#end_date').val();
        //     }

        //     $("input:checkbox[name=receipt]:checked").each(function() {
        //         fields.push($(this).val());
        //     });
        //     $("input:checkbox[name=unpostedreceipt]:checked").each(function() {
        //         upfields.push($(this).val());
        //     });
        //     $('#hidden_fields').val(fields);
        //     $('#unposted_fields').val(upfields);
        //     if (fields == "" && upfields == "") {
        //         return;
        //     }

        //     $('#form_for_export').submit();

        // });

        $('#dbreak_down_report').click(function() {
            var fields = [];

            $("input:checkbox[name=receipt]:checked").each(function() {
                fields.push($(this).val());
            });
            if (fields == "") {
                return;
            }
            $('#dhidden_fields').val(fields);
            //url="<?php echo site_url('reports/daily_break_down_sheet_export?ids=') ?>"+fields;

            $('#dform_for_export').submit();
            // window.open(url, '_blank');

            //   window.location.href = baseurl + "pos_reports/extended";

        });

        $('#dbreak_down_report').click(function() {
            var fields = [];

            $("input:checkbox[name=receipt]:checked").each(function() {
                fields.push($(this).val());
            });
            if (fields == "") {
                return;
            }
            $('#dhidden_fields').val(fields);
            //url="<?php echo site_url('reports/daily_break_down_sheet_export?ids=') ?>"+fields;

            $('#dform_for_export').submit();
            // window.open(url, '_blank');

            //   window.location.href = baseurl + "pos_reports/extended";

        });



        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        filter_check = $(location).attr("href").split('/').pop();
        if (filter_check == "monthly") {
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            end_date = day + '-' + (month < 10 ? '0' : '') + month + '-' + d.getFullYear();
            start_date = '1' + '-' + (month < 10 ? '0' : '') + month + '-' + d.getFullYear();
            $('#start_date').val(start_date);
            $('#end_date').val(end_date);
        }







        draw_data(start_date, end_date);

        function draw_data(start_date = '', end_date = '', start_cat = '', end_cat = '') {





            var tables = $('#reports').DataTable({
                'processing': true,
                'serverSide': true,
                'stateSave': true,
                "paging": false,
                <?php datatable_lang(); ?>
                // responsive: true,
                'order': [],
                'ajax': {
                    'url': "<?php echo site_url('reports/ajax_sale_list') ?>",
                    'type': 'POST',
                    'data': {
                        '<?= $this->security->get_csrf_token_name() ?>': crsf_hash,
                        start_date: start_date,
                        end_date: end_date,
                        start_cat: start_cat,
                        end_cat: end_cat
                    }
                },
                'columnDefs': [{
                    'targets': [5],
                    'orderable': false,
                }, ],
                // dom: 'Blfrtip',
                // buttons: [
                // {
                //     extend: 'excelHtml5',
                //     footer: false,
                //     exportOptions: {
                //         columns: [0, 1, 2, 3, 4, 5]
                //     }
                // },
                // 'pdf',
                // ],

            });


            $('#reports tbody').off('click', 'tr').on('click', 'tr', function() {
                var data = tables.row(this).data();
                // $(':checkbox', data[0]).trigger('click');
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    var $chk = $(this).find('input[type=checkbox]');
                    $chk.prop('checked', false);
                } else {
                    var $chk = $(this).find('input[type=checkbox]');
                    $chk.prop('checked', true);
                    $(this).addClass('selected');
                }

            });

        }

        $('#search').click(function() {


            $('#categories').val($('#chkveg').val());

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var cats = $('#categories').val();
            // var start_cat = $('#start_cat').val();
            var end_cat = $('#categories').val();
            if (start_date != '' && end_date != '' && cats != '') {
                //change added by me
                $('#hidden_start_date').val(start_date);
                $('#hidden_end_date').val(end_date);
                $('#hidden_start_category').val(cats);
                // $('#hidden_end_category').val(end_cat);

                $('#reports').DataTable().destroy();
                draw_data(start_date, end_date, end_cat);
            } else {
                if (cats == '') {
                    alert("Product Categories Required");
                } else {
                    alert("Date range is Required");
                }
            }
        });
    });
    $("#checkAll").click(function() {
        $('input:checkbox').not(this).prop('checked', this.checked);
        $("#reports_body tr").each(function() {
            if ($('#checkAll').is(':checked')) {
                if ($(this).hasClass('selected')) {
                    // $(this).click();
                } else {
                    $(this).click();
                }
            } else {
                if ($(this).hasClass('selected')) {
                    $(this).click();
                } else {

                }

            }
        });


    });
</script>