<div class="content-body">
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="success" id="dash_0">10</h3>
                                <span>11 KG Cylinders Orders</span>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-fire success font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="danger" id="dash_1">10</h3>
                                <span>22 KG Cylinders Orders</span>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-fire danger font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="blue" id="dash_2">5</h3>
                                <span>44 KG Cylinders Orders</span>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-fire blue font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="purple" id="dash_6"><?php echo $totalt ?></h3>
                                <span>Total Orders</span>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-list-alt purple font-large-2 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
           <h3 class="title">
                Orders List 
            </h3> 
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>

        <hr>
        <div class="card-body">


            <div class="card card-block">
                <div id="notify" class="alert alert-success" style="display:none;">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>

                    <div class="message"></div>
                </div>


                <table id="promotable" class="table table-striped table-bordered zero-configuration">
                    <thead>
                    <tr>
                        <th>#</th>
						<th>Item Name</th>
						<th>Customer Name</th>
						<th>Delivery Address</th>
						<th>Quantity</th>
                        <th>Order Amount</th>
                        <th>Warehouse Name</th>
						<th>Order Date/Time</th>
                        <th><?php echo $this->lang->line('Action') ?></th>


                    </tr>
                    </thead>
                    <tbody>

                    </tbody>

                </table>
            </div>

            <input type="hidden" id="dashurl" value="promo/promo_stats">
        </div>

    </div>
</div>
<div id="delete_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title"><?php echo $this->lang->line('Delete') ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="object-id" value="">
                <input type="hidden" id="action-url" value="orders/delete_i">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="delete-confirm"><?php echo $this->lang->line('Delete') ?></button>
                <button type="button" data-dismiss="modal"
                        class="btn"><?php echo $this->lang->line('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    $(document).ready(function () {

        $('#promotable').DataTable({

            "processing": true,
            "serverSide": true,
            "stateSave": true,
            responsive: true,
            <?php datatable_lang();?>
            "ajax": {
                "url": "<?php echo site_url('orders/load_list')?>",
                "type": "POST",
                'data': {'<?=$this->security->get_csrf_token_name()?>': crsf_hash}
            },
            "columnDefs": [
                {
                    "targets": [0],
                    "orderable": true,
                },
            ], dom: 'Blfrtip',
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

        $(document).on('click', ".set-task", function (e) {
            e.preventDefault();
            $('#taskid').val($(this).attr('data-id'));

            $('#pop_model').modal({backdrop: 'static', keyboard: false});

        });


        $(document).on('click', ".view_task", function (e) {
            e.preventDefault();

            var actionurl = 'tools/view_task';
            var id = $(this).attr('data-id');
            $('#task_model').modal({backdrop: 'static', keyboard: false});


            $.ajax({

                url: baseurl + actionurl,
                type: 'POST',
                data: {'tid': id},
                dataType: 'json',
                success: function (data) {

                    $('#description').html(data.description);
                    $('#task_title').html(data.name);
                    $('#employee').html(data.employee);
                    $('#assign').html(data.assign);
                    $('#priority').html(data.priority);
                }

            });

        });
        miniDash();


    });

</script>