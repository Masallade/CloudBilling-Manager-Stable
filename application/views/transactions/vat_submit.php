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


<form method="POST" action="<?php echo base_url('HMRC/submitVAT'); ?>">
    <div class="row">
        <div class="col-md-6">
        <div class="form-group">
        <label for="vatDueSales">VAT Due Sales:</label>
        <input type="vatDueSales" name="vatDueSales" class="form-control" id="vatDueSales">
            </div>
            </div>
         <div class="col-md-6">
    <div class="form-group">
        <label for="vatDueAcquisitions">VAT Due Acquisitions:</label>
        <input type="vatDueAcquisitions" name="vatDueAcquisitions" class="form-control" id="vatDueAcquisitions">
        </div>
    </div>
            <div class="col-md-6">
        <div class="form-group">
        <label for="totalVatDue">Total Vat Due:</label>
        <input type="totalVatDue" name="totalVatDue" class="form-control" id="totalVatDue">
        <small class="form-text" style="color: red;">*Total Vat Due should be equal to vat Due Sales + vat Due Acquisitions</small>

            </div>
            </div>
         <div class="col-md-6">
    <div class="form-group">
        <label for="vatReclaimedCurrPeriod">VAT Reclaimed Curr Period:</label>
        <input type="vatReclaimedCurrPeriod" name="vatReclaimedCurrPeriod" class="form-control" id="vatReclaimedCurrPeriod">
        </div>
    </div>
            <div class="col-md-6">
        <div class="form-group">
        <label for="netVatDue">Net Vat Due:</label>
        <input type="netVatDue" name="netVatDue" class="form-control" id="netVatDue">
        <small class="form-text" style="color: red;">*Net Vat Due should be the difference between the largest and the smallest values among Total Vat Due and VAT Reclaimed Curr Period</small>

            </div>
            </div>
         <div class="col-md-6">
    <div class="form-group">
        <label for="totalValueSalesExVAT">Total Value Sales ExVAT:</label>
        <input type="totalValueSalesExVAT" class="form-control" id="totalValueSalesExVAT">
        </div>
    </div>
            <div class="col-md-6">
        <div class="form-group">
        <label for="totalValuePurchasesExVAT">Total Value Purchases ExVAT:</label>
        <input type="totalValuePurchasesExVAT" name="totalValuePurchasesExVAT" class="form-control" id="totalValuePurchasesExVAT">
            </div>
            </div>
         <div class="col-md-6">
    <div class="form-group">
        <label for="totalValueGoodsSuppliedExVAT">total Value Goods Supplied ExVAT:</label>
        <input type="totalValueGoodsSuppliedExVAT" name="totalValueGoodsSuppliedExVAT" class="form-control" id="totalValueGoodsSuppliedExVAT">
        </div>
    </div>
            <div class="col-md-6">
        <div class="form-group">
        <label for="totalAcquisitionsExVAT">total Acquisitions ExVAT:</label>
        <input type="totalAcquisitionsExVAT" name="totalAcquisitionsExVAT" class="form-control" id="totalAcquisitionsExVAT">
            </div>
            </div>
         <div class="col-md-6">
    <div class="form-group">
        <label for="finalised">finalised:</label>
         <select id="finalised" name="finalised">
                <option value="1">Yes</option>
                <option value="0">No</option>

            </select>
        </div>

    </div>
            <button type="button" id="submitVAT" class="btn btn-primary btn-block">Submit VAT</button>
  </form>
</div>
                 
</div>


<script type="text/javascript">
    $(document).ready(function () {
        const url = new URL(window.location.href);

        const queryParams = new URLSearchParams(url.search);

        const vatId = queryParams.get('vat_id');

              $('#submitVAT').click(function(){
                   var periodKey =$('#periodKey').val();
                   var vatDueSales =$('#vatDueSales').val();
                   var vatDueAcquisitions =$('#vatDueAcquisitions').val();
                   var totalVatDue =$('#totalVatDue').val();
                   var vatReclaimedCurrPeriod =$('#vatReclaimedCurrPeriod').val();
                   var netVatDue =$('#netVatDue').val();
                   var totalValueSalesExVAT =$('#totalValueSalesExVAT').val();
                   var totalValuePurchasesExVAT =$('#totalValuePurchasesExVAT').val();
                   var totalValueGoodsSuppliedExVAT =$('#totalValueGoodsSuppliedExVAT').val();
                   var totalAcquisitionsExVAT =$('#totalAcquisitionsExVAT').val();
                   var finalised =$('#finalised').val();

                $.ajax({
                    url: '<?php echo base_url("HMRC/VATsubmit"); ?>',
                    type: 'POST',
                    data: {
                        '<?=$this->security->get_csrf_token_name()?>': crsf_hash,
                    periodKey : periodKey,
                    vatDueSales : vatDueSales,
                    vatDueAcquisitions : vatDueAcquisitions,
                    totalVatDue : totalVatDue,
                    vatReclaimedCurrPeriod : vatReclaimedCurrPeriod,
                    netVatDue : netVatDue,
                    totalValueSalesExVAT : totalValueSalesExVAT,
                    totalValuePurchasesExVAT : totalValuePurchasesExVAT,
                    totalValueGoodsSuppliedExVAT : totalValueGoodsSuppliedExVAT,
                    totalAcquisitionsExVAT : totalAcquisitionsExVAT,
                    finalised : finalised,
                    vat_id : vatId,
                    },
                    success: function(response){
                     
                       // location.reload();

                    },
                    error: function(){

                        // Handle error
                        console.log('Error occurred');
                    }
                });
            });
    });

    function calculateTotalVatDue() {

        const vatDueSales = parseFloat(document.getElementById('vatDueSales').value) || 0;
        const vatDueAcquisitions = parseFloat(document.getElementById('vatDueAcquisitions').value) || 0;
                const totalVatDue = vatDueSales + vatDueAcquisitions;
                document.getElementById('totalVatDue').value = totalVatDue.toFixed(2); // Round to 2 decimal places
    }

    document.getElementById('vatDueSales').addEventListener('input', calculateTotalVatDue);
    document.getElementById('vatDueAcquisitions').addEventListener('input', calculateTotalVatDue);


       function calculateNetVatDue() {
        const totalVatDue = parseFloat(document.getElementById('totalVatDue').value) || 0;
        const vatReclaimedCurrPeriod = parseFloat(document.getElementById('vatReclaimedCurrPeriod').value) || 0;
        const netVatDue = Math.abs(totalVatDue - vatReclaimedCurrPeriod);
        document.getElementById('netVatDue').value = netVatDue.toFixed(2); // Round to 2 decimal places
    }
    document.getElementById('totalVatDue').addEventListener('input', calculateNetVatDue);
    document.getElementById('vatReclaimedCurrPeriod').addEventListener('input', calculateNetVatDue);
    calculateNetVatDue();
</script>