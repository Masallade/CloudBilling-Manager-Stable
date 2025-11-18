<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Daily BreakDown Sheet </h4>
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
                        <input type="text" name="start_date" id="start_date"
                               class=" form-control form-control-sm"  data-toggle="datepicker" autocomplete="off"/>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm"
                               data-toggle="datepicker" autocomplete="off"/>
                               
                    </div>
                     <div class="col-md-2">
                <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm"/>
            </div> 
                </div>
<br>
         <div class="row">
                    <div class="col-md-2">Select Driver</div>
                    <div class="col-md-2">
                       <select name="interest" class="form-control" id='driver_select' form="form_for_export" required>
                       <option value="">Select Option</option>
                            <?php foreach( $drivers as $var => $driver ){ ?>
                            <option value="<?php echo $driver['name'] ?>"><?php echo $driver['name'] ?></option><?php } ?>
                        </select>
                    </div>
                   <div class="col-md-2" > <input type="text" name="vehicle_no" id="vehicle_no" placeholder="Enter Vehicle No" class="form-control form-control-sm" required/> 
                        </div>
                    <div class="col-md-2" ><input type="button" name="break_down_report" id="break_down_report" value="Print Report" class="btn btn-info btn-sm"/> </div> 
                </div>
<br>
<br>
                <table id="invoices" class="table table-striped table-bordered zero-configuration ">
                    <thead>
                    <tr>
                        <th> Inv #</th>
                        <th><?php echo 'A/C'; ?></th>  
                        <th><?php echo 'Name'; ?></th>
                        <th><?php echo 'Invoice Date'; ?></th> <th><?php echo 'Invoice Due Date'; ?></th>
                        <th><?php echo $this->lang->line('Amount') ?></th>
                        <th><input type="checkbox" id="checkAll"> Select All</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>

                    <tfoot>
                    <tr>
                        
                        <th> Inv #</th>
                        <th><?php echo 'A/C'; ?></th>  <th><?php echo 'Name'; ?></th>
                       <th><?php echo 'Invoice Date'; ?></th> 
                       <th><?php echo 'Invoice Due Date'; ?></th>
                        <th><?php echo $this->lang->line('Amount') ?></th>
                        <th>Select All</th>
                        
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div id="MyInvoices" style="margin:0 auto; min-height:200px; width:98%; display:none;"></div>
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
                <input type="hidden" id="action-url" value="invoices/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>

  <form method="POST" id="form_for_export" action="<?php echo site_url('invoices/daily_break_down_sheet_export')?>" target="_blank">  
    <input type="hidden" id="hidden_fields" name="hidden_fields"  
    value=""/>
    <input type='hidden' name='driver_name' id='driver_name' value=''>
    <input type='hidden' name='vehicle_number' id='vehicle_number' value=''>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
</form>
<script type="text/javascript">

$(function() {
    $('#driver_select').change(function() {
        var x = $(this).val();
        $('#driver_name').val(x);
    });
})

      $('#break_down_report').click(function () {
            var fields=[];

        $("input:checkbox[name=receipt]:checked").each(function(){
              fields.push($(this).val());
        });
        if(fields=="")
        {
            return;
        }
         $('#hidden_fields').val(fields);
        //url="<?php echo site_url('invoices/daily_break_down_sheet_export?ids=')?>"+fields;
        $('#vehicle_number').val($('#vehicle_no').val());
        $('#form_for_export').submit();
    // window.open(url, '_blank');

    //   window.location.href = baseurl + "pos_invoices/extended";
        
        });
    $(document).ready(function () {
           var start_date = $('#start_date').val();
            var end_date = $('#end_date').val(); 
            draw_data(start_date, end_date);
     function draw_data(start_date = '', end_date = '') {
        var tables =$('#invoices').DataTable({
                'processing': true,
                'serverSide': true,
                'stateSave': true,
                 'paging':   false,
                <?php datatable_lang();?>
                // responsive: true,
                'order': [],
                'ajax': {
                    'url': "<?php echo site_url('invoices/bk_ajax_sale_list')?>",
                    'type': 'POST',
                    'data': {
                        '<?=$this->security->get_csrf_token_name()?>': crsf_hash,
                        start_date: start_date,
                        end_date: end_date, 
                    }
                },
                'columnDefs': [
                    {
                        'targets': [0, 6],
                        'orderable': false,
                    },
                ],
                // dom: 'Blfrtip',
                // buttons: [
                //     {
                //         extend: 'excelHtml5',
                //         footer: false,
                //         exportOptions: {
                //             columns: [0, 1, 2, 3, 4, 5]
                //         }
                //     },
                //     'pdf',
                // ],
                
            });
$('#invoices tbody').off('click', 'tr').on( 'click', 'tr', function () {
            var data = tables.row( this ).data();
                $(':checkbox', data[6]).trigger('click');
            if ( $(this).hasClass('selected') ) { 
                 $(this).removeClass('selected'); 
                  var $chk = $(this).find('input[type=checkbox]');
                  $chk.prop('checked',false);
            } 
            else {                 
              var $chk = $(this).find('input[type=checkbox]');
                  $chk.prop('checked',true);
                $(this).addClass('selected');                
            }  

        });
        }
        $('#search').click(function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val(); 
            if (start_date != '' && end_date != '') {
                $('#invoices').DataTable().destroy();
                draw_data(start_date, end_date);
            } else {
                alert("Date range is Required");
            }
        });
        
    });
    $("#checkAll").click(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
        $("#invoices tr").each(function() {
        if($('#checkAll').is(':checked')){
            if ( $(this).hasClass('selected') ) { 
            // $(this).click();
            }else{
                $(this).click();
                } 
            }else{
            if ( $(this).hasClass('selected') ) { 
                $(this).click();
            }else{
            
                } 
            }
        });
    });
</script>