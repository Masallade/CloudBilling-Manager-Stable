<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h5 class="title">
                <?php echo $this->lang->line('View') . ' :' . $department['val1'] ?> <a
                        href="<?php echo base_url('employee/departments') ?>"
                        class="btn btn-primary btn-sm rounded">
                    <?php echo $this->lang->line('Departments') ?>
                </a>
            </h5>
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


                <table id="emptable" class="table table-striped table-bordered zero-configuration" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo $this->lang->line('Name') ?></th>
                        <th><?php echo $this->lang->line('Salary') ?></th>


                        <th><?php echo $this->lang->line('Actions') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>#</th>
                        <th><?php echo $this->lang->line('Name') ?></th>
                        <th><?php echo $this->lang->line('Salary') ?></th>

                        <th><?php echo $this->lang->line('Actions') ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.title = '<?php echo $this->lang->line('View') . ' :' . $department['val1'] ?>';
    $(document).ready(function () {
        $('#emptable').DataTable({
            'processing': true,
            'serverSide': true,
            'stateSave': true,
            responsive: true,
            'lengthMenu': [[25, 50, 75, 100, -1], [25, 50, 75, 100, '<?php echo $this->lang->line('All'); ?>']],
            'pageLength': 25,
            <?php datatable_lang();?>
            'order': [],
            'ajax': {
                'url': "<?php echo site_url('employee/dep_emp_list')?>",
                'type': 'POST',
                'data': {
                    'id': '<?php echo $id; ?>',
                    '<?php echo $this->security->get_csrf_token_name();?>': crsf_hash
                }
            },
            'columnDefs': [
                {
                    'targets': [0],
                    'orderable': false,
                },
            ]
        });
    });

    $('.delemp').click(function (e) {
        e.preventDefault();
        $('#empid').val($(this).attr('data-object-id'));

    });
</script>
