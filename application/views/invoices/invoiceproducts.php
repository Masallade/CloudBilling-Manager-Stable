<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Sales Report Product Wise </h4>
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
                        class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                        autocomplete="off" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>


                </div>


                <div class="row" style="margin-top:10px;">

                  <div class="col-md-3"> Product Category Number Between:</div>
                  <div class="col-md-2">
                    <input type="number" maxlength="2" name="start_cat" id="start_cat" class="form-control form-control-sm" />
                </div>
                <div class="col-md-2">
                    <input type="number" name="end_cat" maxlength="2" id="end_cat" class="form-control form-control-sm" />
                </div>
            </div>
            <div class="row" style="margin-top:10px;">
               <div class="col-md-2"></div>
               <div class="col-md-3"></div>
               <div class="col-md-2 text-right">
                <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm"/>   
            </div>
             <div class="col-md-5" style="text-align:right"><input type="button" name="break_down_report" id="break_down_report" value="Print Report" class="btn btn-info btn-sm"/> </div>

        </div>

        <hr>
        <table id="invoices" class="table table-striped table-bordered zero-configuration ">
            <thead>
                <tr>
                    <th> Inv #</th>
                    <th><?php echo 'A/C'; ?></th>  
                    <th><?php echo 'Name'; ?></th>
                    <th><?php echo 'Invoice Date'; ?></th>
                    <th><?php echo $this->lang->line('Amount'); ?></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                     <th> Inv #</th>
                    <th><?php echo 'A/C'; ?></th>  
                    <th><?php echo 'Name'; ?></th>
                    <th><?php echo 'Invoice Date'; ?></th> 
                    <th><?php echo $this->lang->line('Amount'); ?></th>
                    <th>Action</th>
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
    <!--change added by me-->
<form method="POST" id="form_for_export" action="<?php echo site_url('invoices/stock_report_products')?>" target="_blank">
    

    <input type="hidden" id="hidden_start_date" name="hidden_start_date" 
    value=""/>
    <input type="hidden" id="hidden_end_date" name="hidden_end_date" 
    value=""/>
    <input type="hidden" id="hidden_start_category" name="hidden_start_category" 
    value=""/>
    <input type="hidden" id="hidden_end_category" name="hidden_end_category" 
    value=""/>
    <input type="hidden" id="hidden_fields" name="hidden_fields"  
    value=""/>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
</form>
    <script type="text/javascript">
       $(document).ready(function () {

       $('#break_down_report').click(function () {
//change names here
var fields=[];

var start_cat = document.getElementById("hidden_start_category").value;
var end_cat = document.getElementById("hidden_end_category").value;
var start_date = document.getElementById("hidden_start_date").value;
var end_date = document.getElementById("hidden_end_date").value;

if(start_date=="")
{
    start_date = $('#start_date').val();
    end_date = $('#end_date').val(); 
}

$("input:checkbox[name=receipt]:checked").each(function(){
  fields.push($(this).val());
});
 $('#hidden_fields').val(fields);
if(fields==""){
    return;
}

  $('#form_for_export').submit();

});

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val(); 
      filter_check=$(location).attr("href").split('/').pop();
        if(filter_check=="monthly"){
            var d = new Date();
            var month = d.getMonth()+1;
            var day = d.getDate();
             end_date = day + '-' +(month<10 ? '0' : '') + month  + '-' + d.getFullYear();
             start_date= '1' + '-' +(month<10 ? '0' : '') + month  + '-' + d.getFullYear();
             $('#start_date').val(start_date);
              $('#end_date').val(end_date); 
        }
  






    draw_data(start_date,end_date);

    function draw_data(start_date = '', end_date = '', start_cat='', end_cat='') {





        var tables =      $('#invoices').DataTable({
            'processing': true,
            'serverSide': true,
            'stateSave': true,
            "paging": false,
            <?php datatable_lang();?>
            responsive: true,
            'order': [],
            'ajax': {
                'url': "<?php echo site_url('invoices/ajax_sale_list_products')?>",
                'type': 'POST',
                'data': {
                    '<?=$this->security->get_csrf_token_name()?>': crsf_hash,
                    start_date: start_date,
                    end_date: end_date, 
                    start_cat: start_cat,
                    end_cat: end_cat
                }
            },
            'columnDefs': [
            {
                'targets': [0],
                'orderable': false,
            },
            ],
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
// $('#invoices tbody').off('click', 'tr').on( 'click', 'tr', function () {
//             var data = tables.row( this ).data();
//                 $(':checkbox', data[6]).trigger('click');
//             if ( $(this).hasClass('selected') ) { 
//                  $(this).removeClass('selected'); 
//                   var $chk = $(this).find('input[type=checkbox]');
//                   $chk.prop('checked',false);
//             } 
//             else {                 
//               var $chk = $(this).find('input[type=checkbox]');
//                   $chk.prop('checked',true);
//                 $(this).addClass('selected');                
//             }  

//         });
    
$('#invoices tbody').off('click', 'tr').on( 'click', 'tr', function () {
            var data = tables.row( this ).data();
                // $(':checkbox', data[0]).trigger('click');
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
    var start_cat = $('#start_cat').val();
    var end_cat = $('#end_cat').val();
    if (start_date != '' && end_date != '') {
                //change added by me
                $('#hidden_start_date').val(start_date);
                $('#hidden_end_date').val(end_date); 
                $('#hidden_start_category').val(start_cat);
                $('#hidden_end_category').val(end_cat);

                $('#invoices').DataTable().destroy();
                draw_data(start_date, end_date, start_cat, end_cat);
            } else {
                alert("Date range is Required");
            }
        });
});
</script>