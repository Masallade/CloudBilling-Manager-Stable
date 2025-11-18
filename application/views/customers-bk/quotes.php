<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?php echo $this->lang->line('Customer Details') ?>
                : <?php echo $details['name'] ?></h4>
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
                    <div class="col-md-2 border-right border-right-grey">


                     


                        <div class="row mt-3">
                            <div class="col-md-12">
                                <a href="<?php echo base_url('customers/view?id=' . $details['id']) ?>"
                                   class="btn btn-blue btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-user"></i> View Details</a>
                                <a href="<?php echo base_url('customers/invoices?id=' . $details['id']) ?>"
                                   class="btn btn-success btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-file-text"></i> <?php echo $this->lang->line('View Invoices') ?>
                                </a>
                                <a href="<?php echo base_url('customers/transactions?id=' . $details['id']) ?>"
                                   class="btn btn-blue-grey btn-md mr-1 mb-1 btn-block  btn-lighten-1"><i
                                            class="fa fa-money"></i> View Activity
                                </a>
                                <a href="<?php echo base_url('customers/statement?id=' . $details['id']) ?>"
                                   class="btn btn-primary btn-block btn-md mr-1 mb-1 btn-lighten-1"><i
                                            class="fa fa-briefcase"></i> <?php echo $this->lang->line('Account Statements') ?>
                                </a>
                           <!--     <a href="<?php echo base_url('customers/quotes?id=' . $details['id']) ?>"
                                   class="btn btn-purple btn-md mr-1 mb-1 btn-block btn-lighten-1"><i
                                            class="fa fa-quote-left"></i> Add Balance
                                </a>  -->

                            </div>
                        </div>


                    </div>
                    <div class="col-md-10">
                       
                        <h4><?php echo 'Add New Customer Balance';?></h4>
                        <hr>
						
						
					<form name="addBalance" id="addBalance" method="GET" action="add_balance2">
						
						<div class="form-group row">
<input type="hidden" name="cstid" value="<?php echo $this->input->get('id'); ?>" />
                                    <div class="col-sm-6" ><label                                        class="caption"><?php echo 'Balance Amount'; ?> </label>

                                       <div class="input-group">
									<input type="number" name="balance" step="any" autocomplete="off" class="form-control" required id="balance" /> </div> </div> </div>
									
									<div class="form-group row">
									<div class="col-sm-6" > 
										<label  class="caption"><?php echo 'Description'; ?> </label>
										<div class="input-group">
											<input type="text" class="form-control" name="description" id="description" /> </div>
									</div> 
									</div>
									
									<div class="form-group row">
									<div class="col-sm-6"><label  class="caption"><?php echo $this->lang->line('Invoice Date') ?> </label> 
									<div class="input-group">
                   
                        <input type="text" name="end_date" id="end_date" class="form-control"
                               data-toggle="datepicker" required autocomplete="off"/>
                    </div></div> </div>
									
									<div class="form-group row">
									<div class="col-sm-12">
                                           <input type="submit" style=" padding:5px; margin-top:20px; margin-left:20px;"
                                           class="btn btn-success sub-btn" 
                                           value="Add Balance" name="btnBalance"
                                           id="btnBalance">
                                       </div>
									</div>
					</form>


                    </div>
                </div>


            </div>
        </div>
    </div>


    <div id="sendMail" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Email</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="sendmail_form"><input type="hidden"
                                                    name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                                    value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="row">
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-envelope-o"
                                                                         aria-hidden="true"></span></div>
                                    <input type="text" class="form-control" placeholder="Email" name="mailtoc"
                                           value="<?php echo $details['email'] ?>">
                                </div>

                            </div>

                        </div>


                        <div class="row">
                            <div class="col mb-1"><label
                                        for="shortnote"><?php echo $this->lang->line('Customer Name') ?></label>
                                <input type="text" class="form-control"
                                       name="customername" value="<?php echo $details['name'] ?>"></div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label
                                        for="shortnote"><?php echo $this->lang->line('Subject') ?></label>
                                <input type="text" class="form-control"
                                       name="subject" id="subject">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-1"><label
                                        for="shortnote"><?php echo $this->lang->line('Message') ?></label>
                                <textarea name="text" class="summernote" id="contents" title="Contents"></textarea>
                            </div>
                        </div>

                        <input type="hidden" class="form-control"
                               id="cid" name="tid" value="<?php echo $details['id'] ?>">
                        <input type="hidden" id="action-url" value="communication/send_general">


                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo $this->lang->line('Close') ?></button>
                    <button type="button" class="btn btn-primary"
                            id="sendNow"><?php echo $this->lang->line('Send') ?></button>
                </div>
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
                <div class="modal-body">
                    <p><?php echo $this->lang->line('delete this quote') ?></p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="object-id" value="">
                    <input type="hidden" id="action-url" value="quote/delete_i">
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
            $('#invoices').DataTable({
                'processing': true,
                'serverSide': true,
                'stateSave': true,
                responsive: true,
                <?php datatable_lang();?>
                'order': [],
                'ajax': {
                    'url': "<?php echo site_url('customers/qto_list')?>",
                    'type': 'POST',
                    'data': {'cid':<?php echo $_GET['id'] ?>, '<?=$this->security->get_csrf_token_name()?>': crsf_hash}
                },
                'columnDefs': [
                    {
                        'targets': [0],
                        'orderable': false,
                    },
                ],
            });
        });
    </script>