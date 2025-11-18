<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5><?php echo $this->lang->line('Edit Note') ?></h5>
            <hr>
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


                <form method="post" id="data_form" class="form-horizontal">


                    <input type="hidden" name="id" value="<?php echo $note['id'] ?>">
                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"
                               for="name"><?php echo $this->lang->line('Title') ?></label>

                        <div class="col-sm-10">
                            <input type="text" placeholder="Task Title"
                                   class="form-control margin-bottom  required" name="title"
                                   value="<?php echo $note['title'] ?>">
                        </div>
                    </div>


                    <div class="form-group row">

                        <label class="col-sm-2 control-label"
                               for="edate"><?php echo $this->lang->line('Description') ?></label>

                        <div class="col-sm-10">
                        <textarea class="summernote"
                                  placeholder=" Note"
                                  autocomplete="false" rows="10"
                                  max-length="180"
                                  name="content"><?php echo $note['content'] ?></textarea>
                        <p>Character Count: <span id="wordCount">0</span></p>
                        </div>
                    </div>
                    <div class="form-group row">

                        <label class="col-sm-2 col-form-label"></label>

                        <div class="col-sm-4">
                            <input type="submit" id="submit-data" class="btn btn-success margin-bottom"
                                   value="<?php echo $this->lang->line('Update') ?>" data-loading-text="Adding...">
                            <input type="hidden" value="tools/editnote" id="action-url">
                        </div>
                    </div>


                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $('.summernote').summernote({
                height: 250,
                callbacks: {
                    onKeydown: function(e) {
                        var tempElement = $('<div/>');
                        tempElement.html($(".summernote").summernote('code'));

                        // Retrieve the text value from the temporary element
                        var content = tempElement.text();
                        var wordCount = content.length;
                        var wordLimit = 184; // Set your desired word limit here

                        $('#wordCount').text(wordCount);
                        if (wordCount > wordLimit) {
                            e.preventDefault()
                            var exceededWords = wordCount - wordLimit;
                            var trimmedContent = content.substring(0, wordLimit);
                            var lastWords = content.substring(0, -exceededWords);
                            $(this).summernote('code', $(".summernote").summernote('code').replace(lastWords, ""));
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Character limit exceeded! Your content has been trimmed. Exceeded words: ' + exceededWords,
                                footer: '<p>Character limit is 185 letters.</p>'
                            })
                            return false;
                        }

                    },
                },
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

            
            var tempElement = $('<div/>');
            tempElement.html($(".summernote").summernote('code'));

            // Retrieve the text value from the temporary element
            var content = tempElement.text();
            var wordCount = content.length;
            $('#wordCount').text(wordCount);
        });
    </script>