<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"> Merge And Print Customer Invoices </h4>
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
                    <div class="col-md-2">Search Customer:</div>
                    <div class="col-md-2">








                        <div class="input-group">
                            <div class="input-group-addon"><span class="icon-bookmark-o"
                             aria-hidden="true"></span></div>

                             <input type="text" class="form-control " name="cst" id="customer-box"
                             placeholder="Enter Customer Name or Phone Number to search" autofocus
                             />

                         </div>


                         <input type="hidden" name="customer_id" id="customer_id" value="0">




                     </div>
                 </div>

                 <br>
                 <div class="row">
                    <div class="col-md-2">Invoice Date Between:</div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" id="start_date"
                        class="form-control form-control-sm" autocomplete="off" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                        autocomplete="off" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>
                    <div class="col-md-2">
                        <input type="button" name="search" id="search" value="Criteria" class="btn btn-info btn-sm"/>
                        <input type="submit" name="break_down_report" value="Print"  class="btn btn-primary btn-sm" id="break_down_report" />
                    </div> 
                    
                    
                </div>



                <br>

                <table id="invoices" class="table table-striped table-bordered zero-configuration ">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('No') ?></th>
                            <th><?php echo 'A/C'; ?></th>  
                            <th><?php echo 'Name'; ?></th>
                            <th><?php echo 'Invoice Type'; ?></th>
                            <th><?php echo 'Invoice Date'; ?></th> 
                            <th><?php echo 'Date'; ?></th> 
                            <?php
                            $sample_currency = null;
                            $this->db->select('multi, loc')->from('geopos_invoices')->limit(1);
                            $query = $this->db->get();
                            if ($query->num_rows() > 0) {
                                $sample_inv = $query->row();
                                $sample_currency = $sample_inv->multi;
                                $sample_loc = $sample_inv->loc;
                            } else {
                                $this->db->select('id')->from('geopos_currencies')->where('LOWER(code)', 'pkr')->or_where('LOWER(symbol)', 'pkr')->limit(1);
                                $curr_query = $this->db->get();
                                if ($curr_query->num_rows() > 0) {
                                    $sample_currency = $curr_query->row()->id;
                                    $sample_loc = $this->aauth->get_user()->loc;
                                }
                            }
                            if ($sample_currency !== null) {
                                $formatted = amountExchange(0, $sample_currency, isset($sample_loc) ? $sample_loc : $this->aauth->get_user()->loc);
                                preg_match('/^([^\d\s,\.]+)\s*/', $formatted, $matches);
                                $currency_symbol = !empty($matches[1]) ? $matches[1] : 'PKR';
                            } else {
                                $currency_symbol = 'PKR'; // Fallback
                            }
                            ?>
                            <th><?php echo $this->lang->line('Amount') ?> (<?php echo $currency_symbol; ?>)</th>
                            <th><?php echo $this->lang->line('Status') ?></th>
                            <th><input type="checkbox" id="checkAll">&nbsp; ALL </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

   
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

    <form method="POST" id="form_for_export" action="<?php echo site_url('invoices/print_customer_invoices_merged')?>" target="_blank">  
        <input type="hidden" id="hidden_fields" name="hidden_fields"  
        value=""/>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
    </form>
    <script type="text/javascript">
        document.title = 'Customer Invoices';
        $("body").on("dblclick", "tr", function() {
            let id = $(this).attr("id");
            window.location.href = $('[data-id="'+id+'"]').data("link");
        });

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
        //url="<?php echo site_url('reports/daily_break_down_sheet_export?ids=')?>"+fields;
        $('#form_for_export').submit();
        // window.open(url, '_blank');
        //   window.location.href = baseurl + "pos_invoices/extended";
    });
    // $("#customer_id").change(function() {
    //     alert();
    //     $("input#search").click();
    // });
        // $("#customer-box").keyup(function() {
        //     $("input#search").click();
        // });
      $(document).ready(function () {
           

           var targetNode = document.getElementById('customer_id');
  
            // Create an observer instance
            var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'value') {
                var newValue = $(targetNode).val();
                $("input#search").click();

                }
            });
            });
        
            // Configuration of the observer:
            var config = { attributes: true };
        
            // Start observing the target node for attribute changes
            observer.observe(targetNode, config);
           



         var start_date = $('#start_date').val();
         var end_date = $('#end_date').val(); 
         var customer_id = $('#customer_id').val(); 
         draw_data(start_date, end_date, customer_id);
         function draw_data(start_date = '', end_date = '',customer_id='') {
            var tables =      $('#invoices').DataTable({
                'processing': true,
                'serverSide': true,
                'stateSave': true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                <?php datatable_lang();?>
                responsive: false,
                'order': [],
                'ajax': {
                    'url': "<?php echo site_url('invoices/print_list')?>",
                    'type': 'POST',
                    'data': {
                        '<?=$this->security->get_csrf_token_name()?>': crsf_hash,
                        start_date: start_date,
                        end_date: end_date,
                        customer_id: customer_id
                    }
                },
                'columnDefs': [
                {
                    'targets': [7, 8],
                    'orderable': false,
                },
                ],
                dom: 'Blfrtip',
                buttons: [
                {
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
                ],
                "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                   $(nRow).attr('id', aData[0]);
               }
           });

        }

        $('#search').click(function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val(); 
            var customer_id = $('#customer_id').val(); 
            if (start_date != '' && end_date != '') {
                $('#invoices').DataTable().destroy();

                draw_data(start_date, end_date,customer_id);
            } else {
                alert("Date range is Required");
            }
        });
        $('#invoices').DataTable().search('').draw();
    });





      $("#checkAll").click(function (e) {
        $('input:checkbox').not(this).prop('checked', this.checked);
        $("#invoices_body tr").each(function() {
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




