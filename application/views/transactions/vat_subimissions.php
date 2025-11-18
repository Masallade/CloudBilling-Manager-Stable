<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5>VAT Obligations</h5>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>

                <div class="message"></div>
            </div>

                                  <div class="form-inline">
                                        <div class="form-group">
                                            <label for="email">Enter VAT Registration : </label>
                                            <input type="text" name="vat_registration" class="form-control" id="vat_registration">
                                        </div>
                                        &nbsp;&nbsp;
                                        <button id="getObligationsBtn" class="btn btn-default">GET Obligations</button>
                                       </div>
            <hr>
            <table id="trans_table" class="table table-striped table-bordered zero-configuration" cellspacing="0"
                   width="100%">
                <thead>
                <tr>

                    <th>Period key</th>
                    <th><?php echo $this->lang->line('Start') ?></th>
                    <th><?php echo $this->lang->line('End') ?></th>
                    <th><?php echo $this->lang->line('Due') ?></th>
                    <th><?php echo "Status";//$this->lang->line('Payer') ?></th>
                    <th><?php echo $this->lang->line('Received') ?></th>
                    <th><?php echo $this->lang->line('Action') ?></th>


                </tr>
                </thead>
                <tbody>
                </tbody>

                <tfoot>
                <tr>
                    <th>Period key</th>
                    <th><?php echo $this->lang->line('Start') ?></th>
                    <th><?php echo $this->lang->line('End') ?></th>
                    <th><?php echo $this->lang->line('Due') ?></th>
                    <th><?php echo "Status";//$this->lang->line('Payer') ?></th>
                    <th><?php echo $this->lang->line('Received') ?></th>
                    <th><?php echo $this->lang->line('Action') ?></th>


                </tr>
                </tfoot>
            </table>

                                <div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <p>Loading...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        ...
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary">Save changes</button>
                                    </div>
                                    </div>
                                </div>
                                </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#trans_table').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            responsive: true,
            <?php datatable_lang();?>
            "ajax": {
                "url": "<?php echo site_url('transactions/vat_translist')?>",
                "type": "POST",
                'data': {'<?=$this->security->get_csrf_token_name()?>': crsf_hash}
            },
            "columnDefs": [
                {
                    "targets": [0],
                    "orderable": true,
                },
            ],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }
            ],
        });
    });
  

              $('#getObligationsBtn').click(function(){
       
                  var data_arn =$('#vat_registration').val();
                    // alert(data_arn);
                $('#loadingModal').modal('show');
                $.ajax({
                    url: '<?php echo base_url("HMRC/retrieveVATPayments"); ?>',
                    type: 'POST',
                    data: {
                        '<?=$this->security->get_csrf_token_name()?>': crsf_hash,
                         vat_registration : data_arn

                    },
                    success: function(response){
                    //     // Hide buffering modal
                     $('#loadingModal').modal('hide');
 
                     window.location.href = '<?php echo base_url("transactions/obligations"); ?>';

                    },
                    error: function(){
                        // Hide buffering modal
                        $('#loadingModal').modal('hide');

                        // Handle error
                        console.log('Error occurred');
                    }
                });
            });
</script>

<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Delete') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('delete this transaction') ?></p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="transactions/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>