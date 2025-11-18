<?php
$due = false;
if ($this->input->get('due')) {
    $due = true;
}
?>
<div class="content-body">
    <div class="card">
        <div class="card-header">
            <?php if (!empty($cust_name)): ?>
                <h4 class="card-title" style="text-align:center;">
                    <?php echo $cust_name; ?> Product Pricing List
                </h4>
            <?php else: ?>
                <h4 class="card-title" style="text-align:center;">
                    All Product Pricing List
                </h4>
            <?php endif; ?>

            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>

            <!-- <div class="row" style="margin-top:10px;">
                <div class="col-md-3"> Customers:</div>
                <div class="col-md-4">
                    <select multiple id="chkveg" class="form-control">
                        <?php foreach ($customers as $var => $customers) { ?>
                            <option value="<?php echo $customers['id'] ?>"><?php echo $customers['name'] ?>-<?php echo $customers['company'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="button" name="search" id="search" value="Set Criteria" class="btn btn-info btn-sm" />
                </div>
            </div> -->
        </div>
        <div class="card-content">
            <div id="notify" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="card-body">
                <table id="clientstable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cust Code</th>
                            <th>Customer Name</th>
                            <th>Product Code </th>
                            <th>Product Description </th>
                            <th>Old Price </th>
                            <th>Price </th>
                            <th>Add on </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <!-- <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Product Code </th>
                            <th>Product Description </th>
                            <th>Price </th>
                            <th>Add on </th>
                        </tr>
                    </tfoot> -->
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.title = "Product Pricing List";
    $(document).ready(function() {
        $('#chkveg').select2({
            includeSelectAllOption: true
        });

        var selectAllOption = new Option("Select All", "selectAll", false, false);
        $("#chkveg").prepend(selectAllOption).trigger("change");

        // Handle the click event for the "Select All" option
        $("#chkveg").on("select2:select", function(e) {
            if (e.params.data.id === "selectAll") {
                var options = $("#chkveg").find("option");
                options.prop("selected", false); // Deselect all options
                $("#chkveg").trigger("change");

                var options = $("#chkveg").find("option:not(:contains('Select All'))");
                options.prop("selected", true); // Select all options except "Select All"
                $("#chkveg").trigger("change");
            }
        });

        // Event handler for "Set Criteria" button click
        $('#search').on('click', function() {
            // Get the selected customer IDs (as an array)
            var selectedCustomers = $('#chkveg').val();

            // If "Select All" is selected, treat it as all customers
            if (selectedCustomers.includes('selectAll')) {
                selectedCustomers = $("#chkveg option:not(:contains('Select All'))").map(function() {
                    return $(this).val();
                }).get();
            }

            // If no customers are selected, send an empty array or do not send it
            var cid = selectedCustomers.length > 0 ? selectedCustomers : null;

            // Reload the DataTable with the selected customers
            table.ajax.reload();
        });
        // Get customer ID from PHP (null if not set)
        var cust_id = "<?php echo isset($cust_id) ? $cust_id : ''; ?>";
        // Initialize DataTable
        var table = $('#clientstable').DataTable({
            'processing': true,
            'serverSide': true,
            'stateSave': true,
            responsive: true,
            deferRender: true,
            <?php datatable_lang(); ?> 'order': [],
            'ajax': {
                'url': "<?php echo site_url('customers/loadprice_list') ?>",
                'type': 'POST',
                'data': function(d) {
                    d['<?= $this->security->get_csrf_token_name() ?>'] = crsf_hash;

                    if (cust_id) {
                        d['custid'] = cust_id;
                    }

                    var cid = $('#chkveg').val();
                    if (cid && cid.length > 0) {
                        d['cid'] = cid;
                    }

                    <?php if ($due) echo "d['due'] = true"; ?>
                }
            },
            'columnDefs': [{
                'targets': [0],
                'orderable': false,
            }],
            dom: 'Blfrtip',
            "paging": true, // Enable pagination
            "pageLength": 25, // Default number of rows per page
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            buttons: [{
                    extend: 'excelHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    customize: function(doc) {
                        doc.content[1].table.widths = ['5%', '20%', '20%', '35%', '20%'];
                        doc.pageMargins = [40, 60, 40, 60];
                        doc.content[0].text = 'Product Pricing List';
                    }
                }
            ]
        });
    });
</script>