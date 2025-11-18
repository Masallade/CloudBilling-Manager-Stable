<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Customer Account
                : <?php echo $details['name'] ?></h2>
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
                        <div id="mybutton">

                            <div class="">
                               

                                <a href="#sendMail" data-toggle="modal" data-remote="false"
                                   class="btn btn-primary btn-md " data-type="reminder"><i
                                            class="fa fa-envelope"></i> <?php echo $this->lang->line('Send Message') ?>
                                </a>


                                <a href="<?php echo base_url('customers/edit?id=' . $details['id']) ?>"
                                   class="btn btn-info btn-md"><i
                                            class="fa fa-pencil"></i> <?php echo $this->lang->line('Edit Profile') ?>
                                </a>


                             
                            </div>

                        </div>
                        <div class="">

                          <h5 class="bg-blue bg-lighten-4  p-1 mt-2"><strong><?php echo  $this->lang->line('Balance') . ': ' . amountExchange($details['balance'], 0, $this->aauth->get_user()->loc) ?></strong></h5>

                            <h4></h4>


                            <hr>
                            <?php if ($details['company']) { ?>
                                <div class="row m-t-lg">
                                    <div class="col-md-2">
                                        <strong><?php echo $this->lang->line('Company') ?></strong>
                                    </div>
                                    <div class="col-md-10">
                                        <?php echo $details['company'] ?>
                                    </div>

                                </div>
                                <hr>
                            <?php } ?>

       
                            <div class="row m-t-lg">
                                <div class="col-md-2">
                                    <strong><?php echo $this->lang->line('Phone') ?></strong>
                                </div>
                                <div class="col-md-10">
                                    <?php echo $details['phone'] ?>
                                </div>

                            </div>
                            <hr>
                                             <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Address') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['address'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('City') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['city'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Region') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['region'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Country') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['country'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('PostBox') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['postbox'] ?>
                                                </div>

                                            </div>
                                             <hr>
                                            <div class="row m-t-lg">
                                            <div class="col-md-2">
                                                <strong>Email</strong>
                                            </div>
                                            <div class="col-md-10">
                                                 <?php echo $details['email'] ?>
                                            </div>
                                            </div>
                                            <hr>
                                             <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Register') ?><?php echo $this->lang->line('Date') ?></strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if ($details['reg_date']) echo dateformat($details['reg_date']) ?>
                                                </div>
                                            </div>
                                <hr>
                            <div id="accordionWrapa1" role="tablist" aria-multiselectable="true">
                               
                               
                                <div id="heading3" class="card-header">
                                    <a data-toggle="collapse" data-parent="#accordionWrapa1" href="#accordion3"
                                       aria-expanded="true" aria-controls="accordion3"
                                       class="card-title lead">
                                      <?php echo $this->lang->line('Shipping Address') ?>
                                    </a>
                                </div>
                                <div id="accordion3" role="tabpanel" aria-labelledby="heading3"
                                     class="card-collapse collapse show" aria-expanded="false">
                                    <div class="card-body">
                                        <div class="card-block">
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Address') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['address_s'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('City') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['city_s'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Region') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['region_s'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('Country') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['country_s'] ?>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="row m-t-lg">
                                                <div class="col-md-2">
                                                    <strong><?php echo $this->lang->line('PostBox') ?></strong>
                                                </div>
                                                <div class="col-md-10">
                                                    <?php echo $details['postbox_s'] ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                               

                              

                                <hr>
                              

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                            </div>

 
                            <div class="col-md-12"><br>
                                <h5><?php echo $this->lang->line('Change Customer Picture') ?></h5><input
                                        id="fileupload"
                                        type="file"
                                        name="files[]"></div>


                        </div>
                    </div>
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
                            <textarea name="text" class="summernote" id="contents" title="Contents"></textarea></div>
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

<script src="<?php echo assets_url('assets/myjs/jquery.ui.widget.js') ?>"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo assets_url('assets/myjs/jquery.fileupload.js') ?>"></script>
<script>
    /*jslint unparam: true */
    /*global window, $ */
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var url = '<?php echo base_url() ?>customers/displaypic?id=<?php echo $details['id'] ?>&<?=$this->security->get_csrf_token_name()?>=' + crsf_hash;
        $('#fileupload').fileupload({
            url: url,
            dataType: 'json',
            formData: {'<?=$this->security->get_csrf_token_name()?>': crsf_hash},
            done: function (e, data) {
                //$('<p/>').text(file.name).appendTo('#files');
                $("#dpic").attr('src', '<?php echo base_url() ?>userfiles/customers/' + data.result + '?8978');
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                    'width',
                    progress + '%'
                );
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });
</script>
<script type="text/javascript">
    $(function () {
        $('.summernote').summernote({
            height: 100,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['fullscreen', ['fullscreen']],
                ['codeview', ['codeview']]
            ]
        });
    });
</script>