var billtype = $('#billtype').val();
var d_csrf = crsf_token + '=' + crsf_hash;

$('#addproduct1').on('click', function () {
    var cvalue = parseInt($('#ganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#ganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row div').length;
    //product row
    var data = '<tr><td><input type="text" class="form-control inputs ui-autocomplete-input" name="product_name[]" autocomplete="off" placeholder="Enter Product Code" id="productname-' + cvalue + '"  data-index="' + cvalue + '"></td><td><input type="text" class="form-control"  id="dpid-' + cvalue + '" name="product_description[]" placeholder="Enter Product description" autocomplete="off" data-index="' + cvalue + '" /> </td> <td><input type="text" class="form-control inputs req amnt" name="product_qty[]" id="amount-' + cvalue + '" data-index="' + cvalue + '" value="1" autocomplete="off" value=""  inputmode="numeric"><input type="hidden" id="alert-' + cvalue + '" value=""  name="alert[]"> </td> <td><input type="text" class="form-control req prc inputs" name="product_price[]" data-index="' + cvalue + '" id="price-' + cvalue + '" autocomplete="off" inputmode="numeric"></td> <td> <input type="text" class="form-control vat " name="product_tax[]" data-index="' + cvalue + '" id="vat-' + cvalue + '" autocomplete="off" inputmode="numeric"></td> <td id="texttaxa-' + cvalue + '" data-index="' + cvalue + '" class="text-center vat-inc-amount">0</td> <td class="text-center"><span class="currenty">' + currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue + '" >0</span></strong></td> <td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn-danger removeProd" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="taxa[]" id="taxa-' + cvalue + '" value="0"><input type="hidden" name="disca[]" id="disca-' + cvalue + '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="product_subtotal-' + cvalue + '" value="0"> <input type="hidden" class="pdIn" name="pid[]" id="pid-' + cvalue + '" value="0"> <input type="hidden" name="vattype[]" id="vattype-' + cvalue + '" /> <input type="hidden" name="unit[]" id="unit-' + cvalue + '" value=""> <input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value=""> <input type="hidden" name="serial[]" id="serial-' + cvalue + '" value=""> </tr>';
    //ajax request
    // $('#saman-row').append(data);
    console.log(data);
    $('tr.last-item-row').before(data);
    row = cvalue;
    $('#productname-' + cvalue).autocomplete({
        source: function (request, response) {
            let cid = $('#customer_id').val();
            $.ajax({
                url: baseurl + 'search_products/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=' + row + '&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
                success: function (data) {

                    response($.map(data, function (item) {

                        var product_d = item[7];
                        var product_k = item[0];
                        //console.log(product_d);
                        //console.log(item[7]);
                        return {
                            label: product_d + ' - ' + product_k,
                            value: product_d,
                            data: item
                        };
                    }));
                }
            });
        },
        autoFocus: true,
        minLength: 0,
        select: function (event, ui) {

            id_arr = $(this).attr('id');
            id = id_arr.split("-");
            var t_r = ui.item.data[3];
            if ($("#taxformat option:selected").attr('data-trate')) {

                t_r = $("#taxformat option:selected").attr('data-trate');
            }
            var discount = ui.item.data[4];
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);

            $('#amount-' + id[1]).val(1);

            $('#vat_type-' + id[1]).val(ui.item.data[10]);

            $('#price-' + id[1]).val(ui.item.data[1]);
            $('#w_qty-' + id[1]).val(ui.item.data[13]);
            $('#w_unit-' + id[1]).val(ui.item.data[14]);
            $('#cost-' + id[1]).val(ui.item.data[12]);
            $('#pid-' + id[1]).val(ui.item.data[2]);
            //$('#vat-' + id[1]).val(t_r);
            if (ui.item.data[10] == 'T1') {
                var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
            } else {
                var vat_price = 0;
            }
            $('#vat-' + id[1]).val(vat_price);

            $('#discount-' + id[1]).val(discount);
            $('#dpid-' + id[1]).val(ui.item.data[5]);
            $('#unit-' + id[1]).val(ui.item.data[6]);
            $('#hsn-' + id[1]).val(ui.item.data[7]);
            $('#alert-' + id[1]).val(ui.item.data[8]);
            $('#pack_units-' + id[1]).val(ui.item.data[8]);
            $('#serial-' + id[1]).val(ui.item.data[10]);
            $('#vattype-' + id[1]).val(ui.item.data[11]);
            rowTotal(cvalue);
            billUpyog();
            $ind = cvalue;
            $nextrow = $ind + 1;
            $('#productname-' + $nextrow).focus();
            $('.inputs').keydown(function (e) {
                if (e.which === 13) {
                    var index = $('.inputs').index(this) + 1;
                    $('.inputs').eq(index).focus();
                    event.preventDefault();
                    return false;
                }
            });
        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });

});

$('#addproduct').on('click', function () {
    var cvalue = parseInt($('#ganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#ganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row div').length;
    //product row
    var data = '<tr><td><input type="text" class="form-control inputs ui-autocomplete-input" name="product_name[]" autocomplete="off" placeholder="Enter Product Code" id="productname-' + cvalue + '"></td> <td><input type="text" class="form-control"  id="dpid-' + cvalue + '" name="product_description[]" placeholder="Enter Product description" autocomplete="off" /> </td> <td><input type="text" class="form-control inputs req amnt" name="product_qty[]" id="amount-' + cvalue + '" value="1" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off" value=""  inputmode="numeric"><input type="hidden" id="alert-' + cvalue + '" value=""  name="alert[]"> </td> <td><input type="text" class="form-control req prc inputs" name="product_price[]" id="price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off" inputmode="numeric"></td> <td class="text-center">  <input type="checkbox" id="SI-' + cvalue + '" value=0 name="SI[' + cvalue + ']" ></td> <td> <input type="text" class="form-control vat " name="product_tax[]" id="vat-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + ', true), billUpyog()" autocomplete="off" inputmode="numeric"></td> <td id="texttaxa-' + cvalue + '" class="text-center">0</td> <td><input type="text" class="form-control discount " name="product_discount[]" onkeypress="return isNumber(event)" id="discount-' + cvalue + '" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td> <td class="text-center"><span class="currenty">' + currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue + '">0</span></strong></td> <td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn-danger removeProd" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="taxa[]" id="taxa-' + cvalue + '" value="0"><input type="hidden" name="disca[]" id="disca-' + cvalue + '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="product_subtotal-' + cvalue + '" value="0"> <input type="hidden" class="pdIn" name="pid[]" id="pid-' + cvalue + '" value="0"> <input type="hidden" name="vattype[]" id="vattype-' + cvalue + '" /> <input type="hidden" name="unit[]" id="unit-' + cvalue + '" value=""> <input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value=""> <input type="hidden" name="serial[]" id="serial-' + cvalue + '" value=""> </tr>';
    //ajax request
    // $('#saman-row').append(data);
    console.log(data);
    $('tr.last-item-row').before(data);
    row = cvalue;
    $('#productname-' + cvalue).autocomplete({
        source: function (request, response) {
            let cid = $('#customer_id').val();
            $.ajax({
                url: baseurl + 'search_products/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=' + row + '&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
                success: function (data) {

                    response($.map(data, function (item) {

                        var product_d = item[7];
                        var product_k = item[0];
                        //console.log(product_d);
                        //console.log(item[7]);
                        return {
                            label: product_d + ' - ' + product_k,
                            value: product_d,
                            data: item
                        };
                    }));
                }
            });
        },
        autoFocus: true,
        minLength: 0,
        select: function (event, ui) {

            id_arr = $(this).attr('id');
            id = id_arr.split("-");
            var t_r = ui.item.data[3];
            if ($("#taxformat option:selected").attr('data-trate')) {

                t_r = $("#taxformat option:selected").attr('data-trate');
            }
            var discount = ui.item.data[4];
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);

            $('#amount-' + id[1]).val(1);

            $('#vat_type-' + id[1]).val(ui.item.data[11]);

            $('#price-' + id[1]).val(ui.item.data[1]);
            $('#w_qty-' + id[1]).val(ui.item.data[13]);
            $('#w_unit-' + id[1]).val(ui.item.data[14]);
            $('#cost-' + id[1]).val(ui.item.data[12]);
            $('#pid-' + id[1]).val(ui.item.data[2]);
            //$('#vat-' + id[1]).val(t_r);
            if (ui.item.data[11] == 'T1') {
                var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
            } else {
                var vat_price = 0;
            }
            $('#vat-' + id[1]).val(vat_price);

            $('#discount-' + id[1]).val(discount);
            $('#dpid-' + id[1]).val(ui.item.data[5]);
            $('#unit-' + id[1]).val(ui.item.data[6]);
            $('#hsn-' + id[1]).val(ui.item.data[7]);
            $('#alert-' + id[1]).val(ui.item.data[8]);
            $('#pack_units-' + id[1]).val(ui.item.data[8]);
            $('#serial-' + id[1]).val(ui.item.data[10]);
            $('#vattype-' + id[1]).val(ui.item.data[11]);
            rowTotal(cvalue);
            billUpyog();
            $ind = cvalue;
            $nextrow = $ind + 1;
            $('#productname-' + $nextrow).focus();
            $('.inputs').keydown(function (e) {
                if (e.which === 13) {
                    var index = $('.inputs').index(this) + 1;
                    $('.inputs').eq(index).focus();
                    event.preventDefault();
                    return false;
                }
            });
        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });

});
$('#addproduct_purcahse_order').on('click', function () {
    var cvalue = parseInt($('#ganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#ganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row div').length;
    var data = '<tr><td><input type="text" class="form-control inputs ui-autocomplete-input" name="product_name[]" placeholder="Enter Product Code" id="productname-' + cvalue + '" autocomplete="off"></td> <td><input type="text" class="form-control inputs"  id="dpid-' + cvalue + '" name="product_description[]" placeholder="Enter Product description" autocomplete="off" /> </td>           <td><select class="form-control inputs"id="packing_type-' + cvalue + '"name="packing_type[]"><option value="pallet" selected> Pallet </option><option value="box"> Box </option></select></td> <td><input type="text" class="form-control inputs req amnt" name="product_qty[]" id="amount-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off" value=""  inputmode="numeric"><input type="hidden" id="alert-' + cvalue + '" value=""  name="alert[]"> </td>   <td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn-danger removeProd" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="taxa[]" id="taxa-' + cvalue + '" value="0"><input type="hidden" name="disca[]" id="disca-' + cvalue + '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue + '" value="0"> <input type="hidden" class="pdIn" name="pid[]" id="pid-' + cvalue + '" value="0"> <input type="hidden" name="unit[]" id="unit-' + cvalue + '" value=""> <input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value=""> <input type="hidden" name="serial[]" id="serial-' + cvalue + '" value=""> </tr>';
    console.log(data);
    $('tr.last-item-row').before(data);

    row = cvalue;

    $('#productname-' + cvalue).autocomplete({

        source: function (request, response) {
            let cid = $('#customer_id').val();
            $.ajax({
                url: baseurl + 'search_products/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=' + row + '&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
                success: function (data) {
                    response($.map(data, function (item) {
                        var product_d = item[7];
                        var product_k = item[0];

                        return {
                            label: product_d + ' - ' + product_k,
                            value: product_d,
                            data: item
                        };
                    }));
                }
            });
        },
        autoFocus: true,
        minLength: 0,
        select: function (event, ui) {
            id_arr = $(this).attr('id');
            id = id_arr.split("-");
            var t_r = ui.item.data[3];
            if ($("#taxformat option:selected").attr('data-trate')) {

                t_r = $("#taxformat option:selected").attr('data-trate');
            }
            var discount = ui.item.data[4];
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);

            $('#amount-' + id[1]).val(1);
            $('#price-' + id[1]).val(ui.item.data[1]);
            $('#cost-' + id[1]).val(ui.item.data[12]);
            $('#w_qty-' + id[1]).val(ui.item.data[13]);
            $('#w_unit-' + id[1]).val(ui.item.data[14]);
            $('#pid-' + id[1]).val(ui.item.data[2]);
            //$('#vat-' + id[1]).val(t_r);
            if (ui.item.data[11] == 'T1') {
                var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
            } else {
                var vat_price = 0;
            }
            $('#vat-' + id[1]).val(vat_price);

            $('#discount-' + id[1]).val(discount);
            $('#dpid-' + id[1]).val(ui.item.data[5]);
            $('#unit-' + id[1]).val(ui.item.data[6]);
            $('#hsn-' + id[1]).val(ui.item.data[7]);
            $('#alert-' + id[1]).val(ui.item.data[8]);
            $('#pack_units-' + id[1]).val(ui.item.data[8]);
            $('#serial-' + id[1]).val(ui.item.data[10]);

            rowTotal(cvalue);
            billUpyog();
            $ind = cvalue;
            $nextrow = $ind + 1;
            $('#productname-' + $nextrow).focus();
        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });

});
//caculations
var precentCalc = function (total, percentageVal) {
    var pr = (total / 100) * percentageVal;
    return parseFloat(pr);
};
//format
var deciFormat = function (minput) {
    if (!minput) minput = 0;
    return parseFloat(minput).toFixed(2);
};
var formInputGet = function (iname, inumber) {
    var inputId;
    inputId = iname + '-' + inumber;
    var inputValue = $(inputId).val();

    if (inputValue == '') {

        return 0;
    } else {
        return inputValue;
    }
};


//ship calculation
var coupon = function () {
    var cp = 0;
    if ($('#coupon_amount').val()) {
        cp = accounting.unformat($('#coupon_amount').val(), accounting.settings.number.decimal);
    }
    return cp;
};
var shipTot = function () {
    var ship_val = accounting.unformat($('.shipVal').val(), accounting.settings.number.decimal);
    var ship_p = 0;
    if ($("#taxformat option:selected").attr('data-trate')) {
        var ship_rate = $("#taxformat option:selected").attr('data-trate');
    } else {
        var ship_rate = accounting.unformat($('#ship_rate').val(), accounting.settings.number.decimal);
    }
    var tax_status = $("#ship_taxtype").val();
    if (tax_status == 'excl') {
        ship_p = (ship_val * ship_rate) / 100;
        ship_val = ship_val + ship_p;
    } else if (tax_status == 'incl') {
        ship_p = (ship_val * ship_rate) / (100 + ship_rate);
    }
    $('#ship_tax').val(accounting.formatNumber(ship_p));
    $('#ship_final').html(accounting.formatNumber(ship_p));
    return ship_val;
};

var alertNC = function (id) {
    //var id = $id;
    $.ajax({
        url: baseurl + 'Invoices/ProductList/' + id,
        dataType: "html",
        method: 'get',
        data: 'name_startsWith=abc&' + d_csrf,
        success: function (response) {
            var $target = $('html,body');
            $target.animate({ scrollTop: $target.height() }, 1000);
            $('#MyInvoices').html(response); $('#MyInvoices').show();
        }
    });

}


//product total
// var samanYog = function () {
//     var itempriceList = [];
//     var idList = [];
//     var r = 0;
//     $('.ttInput').each(function () {
//         var vv = accounting.unformat($(this).val(), accounting.settings.number.decimal);
//         var vid = $(this).attr('id');
//         vid = vid.split("-");
//         itempriceList.push(vv);
//         idList.push(vid[1]);
//         r++;
//     });
//     var sum = 0;
//     var taxc = 0;
//     var discs = 0;
//     for (var z = 0; z < idList.length; z++) {
//         var x = idList[z];
//         if (itempriceList[z] > 0) {
//             sum += itempriceList[z];
//         }
//         var t1 = accounting.unformat($("#taxa-" + x).val(), accounting.settings.number.decimal);
//         var d1 = accounting.unformat($("#disca-" + x).val(), accounting.settings.number.decimal);
//         if (t1 > 0) {
//             taxc += t1;
//         }
//         if (d1 > 0) {
//             discs += d1;
//         }
//     }

//     $("#discs").html(accounting.formatNumber(discs));
//     $("#taxr").html(accounting.formatNumber(taxc));
//     return accounting.unformat(sum, accounting.settings.number.decimal);
// };
var samanYog = function () {
    var itempriceList = [];
    var idList = [];
    var r = 0;
    $('.ttInput').each(function () {
        var vv = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var vid = $(this).attr('id');
        vid = vid.split("-");
        itempriceList.push(vv);
        idList.push(vid[1]);
        r++;
    });
    var sum = 0;
    var taxc = 0;
    var discs = 0;
    for (var z = 0; z < idList.length; z++) {
        var x = idList[z];
        if (itempriceList[z] > 0) {
            sum += itempriceList[z];
        }
        var t1 = accounting.unformat($("#taxa-" + x).val(), accounting.settings.number.decimal);
        var d1 = accounting.unformat($("#disca-" + x).val(), accounting.settings.number.decimal);
        if (t1 > 0) {
            taxc += t1;
        }
        if (d1 > 0) {
            discs += d1;
        }
    }

    $("#discs").html(accounting.formatNumber(discs));
    $("#taxr").html(accounting.formatNumber(taxc));
    return accounting.unformat(sum, accounting.settings.number.decimal);
};

//actions
var deleteRow = function (num) {
    var totalSelector = $("#subttlform");
    var prodttl = accounting.unformat($("#total-" + num).val(), accounting.settings.number.decimal);
    var subttl = accounting.unformat(totalSelector.val(), accounting.settings.number.decimal);
    var totalSubVal = subttl - prodttl;
    totalSelector.val(totalSubVal);
    $("#subttlid").html(accounting.formatNumber(totalSubVal));
    // Recalculate totals including tax
    samanYog(); // This will update #taxr with current tax total
    var totalTax = accounting.unformat($("#taxr").html(), accounting.settings.number.decimal) || 0;
    var totalBillVal = totalSubVal + totalTax + shipTot() - coupon();
    //final total
    var clean = accounting.formatNumber(totalBillVal);
    $("#mahayog").html(clean);
    $("#invoiceyoghtml").val(clean);
    $("#bigtotal").html(clean);
};

var percentage = function () {
    var priceSum = 0;
    var costSum = 0;

    // Sum all price fields
    $("[id^='price-']").each(function () {
        var priceVal = accounting.unformat($(this).val(), accounting.settings.number.decimal) || 0;
        if (priceVal > 0) {
            priceSum += priceVal;
        }
    });

    // Sum all cost fields
    $("[id^='cost-']").each(function () {
        var costVal = accounting.unformat($(this).val(), accounting.settings.number.decimal) || 0;
        if (costVal > 0) {
            costSum += costVal;
        }
    });
    console.log('costSum', costSum, 'priceSum', priceSum);
    // Calculate profit
    var profit = priceSum - costSum;

    // Calculate profit percentage (avoid dividing by 0)
    var profitPercentage = costSum > 0 ? (profit / costSum) * 100 : 0;
    return accounting.unformat(profitPercentage, accounting.settings.number.decimal);

}

var billUpyog = function () {
    var out = 0;
    var disc_val = accounting.unformat($('.discVal').val(), accounting.settings.number.decimal);
    if (disc_val) {
        $("#subttlform").val(accounting.formatNumber(samanYog()));
        var disc_rate = $('#discountFormat').val();

        switch (disc_rate) {
            case '%':
                out = precentCalc(accounting.unformat($('#subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'b_p':
                out = precentCalc(accounting.unformat($('#subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
            case 'bflat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
        }
        out = parseFloat(out).toFixed(two_fixed);

        $('#disc_final').html(accounting.formatNumber(out));
        $('#after_disc').val(accounting.formatNumber(out));
    } else {
        $('#disc_final').html(0);
        $('#after_disc').val(0);
    }
    var netAmount = samanYog();
    var totalTax = accounting.unformat($("#taxr").html(), accounting.settings.number.decimal) || 0;
    var totalBillVal = accounting.formatNumber(netAmount + totalTax + shipTot() - coupon() - out);
    var totalprofitVal = accounting.formatNumber(percentage());
    console.log('Price_now', totalprofitVal);
    console.log('netAmount:', netAmount, 'totalTax:', totalTax, 'totalBillVal:', totalBillVal);
    $("#mahayog").html(totalBillVal);
    $("#subttlform").val(accounting.formatNumber(netAmount));
    $("#invoiceyoghtml").val(totalBillVal);
    // Correctly update the Estimated Profit in the view
    $("#estimated_profit").html(totalprofitVal);
    $("#bigtotal").html(totalBillVal);
};

var o_rowTotal = function (numb) {
    //most res
    var result;
    var totalValue;
    var amountVal = formInputGet("#amount", numb);
    var priceVal = formInputGet("#price", numb);
    var discountVal = formInputGet("#discount", numb);
    if (discountVal == '') {
        $("#discount-" + numb).val(0);
        discountVal = 0;
    }
    var vatVal = formInputGet("#vat", numb);
    console.log('vatVal', vatVal);
    if (vatVal == '') {
        $("#vat-" + numb).val(0);
        vatVal = 0;
    }
    var taxo = 0;
    var disco = 0;
    var totalPrice = parseFloat(amountVal) * priceVal;
    var tax_status = $("#taxformat option:selected").val();
    var disFormat = $("#discount_format").val();

    //tax after bill
    if (tax_status == 'yes') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Inpercentage = totalPrice * vatVal;
            totalValue = parseFloat(totalPrice) + parseFloat(Inpercentage);
            taxo = deciFormat(Inpercentage);


            if (disFormat == 'flat') {
                disco = deciFormat(discountVal);
                totalValue = parseFloat(totalValue) - parseFloat(discountVal);
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = parseFloat(totalValue) - parseFloat(discount);
                disco = deciFormat(discount);
            }

        } else {
            //before tax
            if (disFormat == 'bflat') {
                disco = deciFormat(discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discountVal);
            } else if (disFormat == 'b_p') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discount);
                disco = deciFormat(discount);
            }

            //tax
            var Inpercentage = precentCalc(totalValue, vatVal);
            totalValue = parseFloat(totalValue) + parseFloat(Inpercentage);
            taxo = deciFormat(Inpercentage);


        }
    } else if (tax_status == 'inclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Inpercentage = (+totalPrice * +vatVal) / (100 + +vatVal);
            totalValue = parseFloat(totalPrice);
            taxo = deciFormat(Inpercentage);


            if (disFormat == 'flat') {
                disco = deciFormat(discountVal);
                totalValue = parseFloat(totalValue) - parseFloat(discountVal);
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = parseFloat(totalValue) - parseFloat(discount);
                disco = deciFormat(discount);
            }

        } else {
            //before tax
            if (disFormat == 'bflat') {
                disco = deciFormat(discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discountVal);
            } else if (disFormat == 'b_p') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discount);
                disco = deciFormat(discount);
            }

            //tax
            var Inpercentage = (+totalPrice * +vatVal) / (100 + +vatVal);
            totalValue = parseFloat(totalValue);
            taxo = deciFormat(Inpercentage);


        }
    } else {
        taxo = 0;
        if (disFormat == '%' || disFormat == 'flat') {
            //tax

            //  totalValue = deciFormat(totalPrice);


            if (disFormat == 'flat') {
                disco = deciFormat(discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discountVal);
            } else if (disFormat == '%') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discount);
                disco = deciFormat(discount);
            }

        } else {
            //before tax
            if (disFormat == 'bflat') {
                disco = deciFormat(discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discountVal);
            } else if (disFormat == 'b_p') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = parseFloat(totalPrice) - parseFloat(discount);
                disco = deciFormat(discount);
            }
        }
    }
    $("#result-" + numb).html(deciFormat(totalValue) - deciFormat(taxo));
    $("#taxa-" + numb).val(taxo);
    $("#texttaxa-" + numb).text(taxo);
    $("#disca-" + numb).val(disco);
    var totalID = "#total-" + numb;
    $(totalID).val(deciFormat(totalValue));
    samanYog();
};

$(document).on('change', '[name="invoiceType"]', function (e) {
    if ($('[name="invoiceType"]:checked').val() == "INVOICE") {
        $('[name="product_tax[]"]').each(function () {
            if ($(this).val() !== '') {
                $(this).trigger('keyup');
            }
        });
    }
});

var rowTotal = function (numb, isValueManuallyModified = "") {
    //most res
    var result;
    var page = '';
    var totalValue = 0;
    var amountVal = accounting.unformat($("#amount-" + numb).val(), accounting.settings.number.decimal);
    var priceVal = accounting.unformat($("#price-" + numb).val(), accounting.settings.number.decimal);


    isValueManuallyModified = ($('[name="invoiceType"]:checked').val() == 'DAYPASS');

    // if($("#vattype-" + numb).val() == 'T1'){
    //     $("#vat-" + numb).val(accounting.formatNumber(priceVal*0.20));
    //     // $("#vat-" + numb).val(priceVal*0.20);
    // }

    if ($("#vattype-" + numb).val() == 'T1') {
        if (isValueManuallyModified !== true) {
            $("#vat-" + numb).val((Math.floor(priceVal * tax_rate_decimal * 100) / 100).toFixed(2));
            // isValueManuallyModified = true;
        }
    }

    isValueManuallyModified = false;


    var discountVal = accounting.unformat($("#discount-" + numb).val(), accounting.settings.number.decimal);
    var vatVal = accounting.unformat($("#vat-" + numb).val(), accounting.settings.number.decimal);
    //var qqty = $("#qty-" + numb).val();
    var taxo = 0;
    var disco = 0;
    var totalPrice = amountVal.toFixed(two_fixed) * priceVal;
    var tax_status = $("#taxformat option:selected").val();
    console.log(tax_status);
    var disFormat = $("#discount_format").val();
    if ($("#inv_page").val() == 'new_i' && formInputGet("#pid", numb) > 0) {
        var alertVal = accounting.unformat($("#alert-" + numb).val(), accounting.settings.number.decimal);
        if (alertVal <= +amountVal) {
            var aqt = alertVal - amountVal;
            alert('Low Stock! ' + accounting.formatNumber(aqt));
        }
    }
    //tax after bill
    if (tax_status == 'yes') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Inpercentage = amountVal * vatVal;
            totalValue = totalPrice + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalValue - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = totalValue - discount;
                disco = accounting.formatNumber(discount);
            }
        } else {
            //before tax
            if (disFormat == 'bflat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_p') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            //tax
            var Inpercentage = precentCalc(totalValue, vatVal);
            totalValue = totalValue + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
        }
    } else if (tax_status == 'inclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            totalValue = totalPrice;
            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalValue - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = totalValue - discount;
                disco = accounting.formatNumber(discount);
            }
        } else {
            //before tax
            if (disFormat == 'bflat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_p') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }
            //tax
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            totalValue = totalValue;
            taxo = accounting.formatNumber(Inpercentage);
        }
    } else {
        taxo = 0;
        if (disFormat == '%' || disFormat == 'flat') {
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

        } else {
            //before tax
            if (disFormat == 'bflat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_p') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }
        }
    }
    var product_subtotal = totalValue - accounting.unformat(taxo, accounting.settings.number.decimal);
    var product_subtotal_formatted = accounting.formatNumber(product_subtotal);
    $("#result-" + numb).html(product_subtotal_formatted);
    $("#product_subtotal-" + numb).val(product_subtotal);
    $("#taxa-" + numb).val(taxo);
    $("#texttaxa-" + numb).text(taxo);
    $("#disca-" + numb).val(disco);
    $("#total-" + numb).val(accounting.formatNumber(totalValue));
    console.log('product_subtotal', product_subtotal, 'totalValue', totalValue);
    console.log('taxo', taxo);
    samanYog();
};

var changeTaxFormat = function (getSelectv) {

    if (getSelectv == 'yes') {
        var tformat = $('#taxformat option:selected').data('tformat');
        var trate = $('#taxformat option:selected').data('trate');
        $("#tax_status").val(tformat);
        $("#tax_format").val('%');
    } else if (getSelectv == 'inclusive') {
        var tformat = $('#taxformat option:selected').data('tformat');
        var trate = $('#taxformat option:selected').data('trate');
        $("#tax_status").val(tformat);
        $("#tax_format").val('incl');

    } else {
        $("#tax_status").val('no');
        $("#tax_format").val('off');

    }
    var discount_handle = $("#discountFormat").val();
    var tax_handle = $("#tax_format").val();
    formatRest(tax_handle, discount_handle, trate);
}

var changeDiscountFormat = function (getSelectv) {
    if (getSelectv != '0') {
        $(".disCol").show();
        $("#discount_handle").val('yes');
        $("#discount_format").val(getSelectv);
    } else {
        $("#discount_format").val(getSelectv);
        $(".disCol").hide();
        $("#discount_handle").val('no');
    }
    var tax_status = $("#tax_format").val();
    formatRest(tax_status, getSelectv);
}

function formatRest(taxFormat, disFormat, trate = '') {
    console.log(taxFormat);
    var amntArray = [];
    var idArray = [];
    $('.amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);
        amntArray.push(v);
    });
    var prcArray = [];
    $('.prc').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        prcArray.push(v);
    });
    var vatArray = [];
    $('.vat').each(function () {
        if (trate) {
            var v = accounting.unformat(trate, accounting.settings.number.decimal);
            $(this).val(v);
        } else {
            var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        }
        vatArray.push(v);
    });

    var discountArray = [];
    $('.discount').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        discountArray.push(v);
    });

    var taxr = 0;
    var discsr = 0;
    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        amtVal = amntArray[i];
        prcVal = prcArray[i];
        vatVal = vatArray[i];
        discountVal = discountArray[i];
        var result = amtVal * prcVal;
        if (vatVal == '') {
            vatVal = 0;
        }
        if (discountVal == '') {
            discountVal = 0;
        }
        if (taxFormat == '%') {
            if (disFormat == '%' || disFormat == 'flat') {
                var Inpercentage = precentCalc(result, vatVal);
                var result = result + Inpercentage;
                taxr = taxr + Inpercentage;
                $("#texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#taxa-" + x).val(accounting.formatNumber(Inpercentage));

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'flat') {
                    result = parseFloat(result) - parseFloat(discountVal);
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
            } else {
                if (disFormat == 'b_p') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'bflat') {
                    result = result - discountVal;
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }

                var Inpercentage = precentCalc(result, vatVal);
                result = result + Inpercentage;
                taxr = taxr + Inpercentage;
                $("#texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#taxa-" + x).val(accounting.formatNumber(Inpercentage));

            }
        }


        $("#total-" + x).val(accounting.formatNumber(result));
        $("#result-" + x).html(accounting.formatNumber(result));


    }
    var sum = accounting.formatNumber(samanYog());
    $("#subttlid").html(sum);
    $("#taxr").html(accounting.formatNumber(taxr));
    $("#discs").html(accounting.formatNumber(discsr));
    billUpyog();
}

//remove productrow


$('#saman-row').on('click', '.removeProd', function () {
    console.log('removeProd');
    var pidd = $(this).closest('tr').find('.pdIn').val();
    var pqty = $(this).closest('tr').find('.amnt').val();
    console.log('pidd', pidd, 'pqty', pqty);
    pqty = pidd + '-' + pqty;
    $('<input>').attr({
        type: 'hidden',
        id: 'restock',
        name: 'restock[]',
        value: pqty
    }).appendTo('form');
    $(this).closest('tr').remove();
    $('#d' + $(this).closest('tr').find('.pdIn').attr('id')).closest('tr').remove();
    $('.amnt').each(function (index) {
        rowTotal(index);
        billUpyog();
    });

    return false;
});






$('#productname_1-0').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#product_desc-0').val(ui.item.data[5]);
        $('#amount-0').val(qytt);
        $('#price-0').val(ui.item.data[1]);
        $('#cost-0').val(ui.item.data[12]);
        $('#w_qty-0').val(ui.item.data[13]);
        $('#w_unit-0').val(ui.item.data[14]);
        $('#pid-0').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[10] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-0').val(vat_price);
        $('#discount-0').val(discount);
        $('#dpid-0').val(ui.item.data[5]);
        $('#unit-0').val(ui.item.data[6]);
        $('#hsn-0').val(ui.item.data[7]);

        $('#alert-0').val(ui.item.data[8]);
        $('#serial-0').val(ui.item.data[10]);
        $('#vattype-0').val(ui.item.data[11]);
        rowTotal(0);
        $('#pack_units-0').val(ui.item.data[8]);

        billUpyog();
        $('#amount-0').focus();

    }
});














$('#productname-0').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-0').val(qytt);
        $('#price-0').val(ui.item.data[1]);
        $('#cost-0').val(ui.item.data[12]);
        $('#w_qty-0').val(ui.item.data[13]);
        $('#w_unit-0').val(ui.item.data[14]);
        $('#pid-0').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            console.log('ui.item.data[1]', ui.item.data[1]);
            console.log('tax_rate_decimal', tax_rate_decimal);
            console.log('ui.item.data[1] * tax_rate_decimal', ui.item.data[1] * tax_rate_decimal);
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
            console.log('vat_price', vat_price);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-0').val(vat_price);
        $('#discount-0').val(discount);
        $('#dpid-0').val(ui.item.data[5]);
        $('#unit-0').val(ui.item.data[6]);
        $('#hsn-0').val(ui.item.data[7]);

        $('#alert-0').val(ui.item.data[8]);
        $('#serial-0').val(ui.item.data[10]);
        $('#vattype-0').val(ui.item.data[11]);
        rowTotal(0);
        $('#pack_units-0').val(ui.item.data[8]);

        billUpyog();
        $('#amount-0').focus();

    }
});



$('#productname-1').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-1').val(1);
        $('#price-1').val(ui.item.data[1]);
        $('#cost-1').val(ui.item.data[12]);
        $('#w_qty-1').val(ui.item.data[13]);
        $('#w_unit-1').val(ui.item.data[14]);
        $('#pid-1').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-1').val(vat_price);
        $('#discount-1').val(discount);
        $('#dpid-1').val(ui.item.data[5]);
        $('#unit-1').val(ui.item.data[6]);
        $('#hsn-1').val(ui.item.data[7]);
        $('#alert-1').val(ui.item.data[8]);
        $('#serial-1').val(ui.item.data[10]);

        $('#vattype-1').val(ui.item.data[11]);
        $('#pack_units-1').val(ui.item.data[8]);
        rowTotal(1);

        billUpyog();

        $('#productname-2').focus();
    }
});



$('#productname-2').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-2').val(1);
        $('#price-2').val(ui.item.data[1]);
        $('#cost-2').val(ui.item.data[12]);
        $('#w_qty-2').val(ui.item.data[13]);
        $('#w_unit-2').val(ui.item.data[14]);
        $('#pid-2').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-2').val(vat_price);
        $('#discount-2').val(discount);
        $('#dpid-2').val(ui.item.data[5]);
        $('#unit-2').val(ui.item.data[6]);
        $('#hsn-2').val(ui.item.data[7]);

        $('#alert-2').val(ui.item.data[8]);
        $('#serial-2').val(ui.item.data[10]);
        $('#vattype-2').val(ui.item.data[11]);
        rowTotal(2);
        $('#pack_units-2').val(ui.item.data[8]);

        billUpyog();
        $('#productname-3').focus();

    }
});


$('#productname-3').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-3').val(1);
        $('#price-3').val(ui.item.data[1]);
        $('#cost-3').val(ui.item.data[12]);
        $('#w_qty-3').val(ui.item.data[13]);
        $('#w_unit-3').val(ui.item.data[14]);
        $('#pid-3').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-3').val(vat_price);
        $('#discount-3').val(discount);
        $('#dpid-3').val(ui.item.data[5]);
        $('#unit-3').val(ui.item.data[6]);
        $('#hsn-3').val(ui.item.data[7]);
        $('#alert-3').val(ui.item.data[8]);

        $('#serial-3').val(ui.item.data[10]);
        $('#vattype-3').val(ui.item.data[11]);
        $('#pack_units-3').val(ui.item.data[8]);
        rowTotal(3);

        billUpyog();
        $('#productname-4').focus();

    }
});



$('#productname-4').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-4').val(1);
        $('#price-4').val(ui.item.data[1]);
        $('#cost-4').val(ui.item.data[12]);
        $('#pid-4').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-4').val(vat_price);
        $('#discount-4').val(discount);
        $('#dpid-4').val(ui.item.data[5]);
        $('#unit-4').val(ui.item.data[6]);
        $('#hsn-4').val(ui.item.data[7]);

        $('#alert-4').val(ui.item.data[8]);
        $('#serial-4').val(ui.item.data[10]);
        $('#vattype-4').val(ui.item.data[11]);
        rowTotal(4);
        $('#pack_units-4').val(ui.item.data[8]);

        billUpyog();
        $('#productname-5').focus();

    }
});


$('#productname-5').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-5').val(1);
        $('#price-5').val(ui.item.data[1]);
        $('#cost-5').val(ui.item.data[12]);
        $('#w_qty-5').val(ui.item.data[13]);
        $('#w_unit-5').val(ui.item.data[14]);
        $('#pid-5').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-5').val(vat_price);
        $('#discount-5').val(discount);
        $('#dpid-5').val(ui.item.data[5]);
        $('#unit-5').val(ui.item.data[6]);
        $('#hsn-5').val(ui.item.data[7]);

        $('#alert-5').val(ui.item.data[8]);
        $('#serial-5').val(ui.item.data[10]);
        $('#vattype-5').val(ui.item.data[11]);
        rowTotal(5);
        $('#pack_units-5').val(ui.item.data[8]);

        billUpyog();
        $('#productname-6').focus();

    }
});



$('#productname-6').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-6').val(1);
        $('#price-6').val(ui.item.data[1]);
        $('#cost-6').val(ui.item.data[12]);
        $('#w_qty-6').val(ui.item.data[13]);
        $('#w_unit-6').val(ui.item.data[14]);
        $('#pid-6').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-6').val(vat_price);
        $('#discount-6').val(discount);
        $('#dpid-6').val(ui.item.data[5]);
        $('#unit-6').val(ui.item.data[6]);

        $('#hsn-6').val(ui.item.data[7]);
        $('#alert-6').val(ui.item.data[8]);
        $('#serial-6').val(ui.item.data[10]);
        $('#vattype-6').val(ui.item.data[11]);
        rowTotal(6);
        $('#pack_units-6').val(ui.item.data[8]);

        billUpyog();

        $('#productname-7').focus();
    }
});


$('#productname-7').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-7').val(1);
        $('#price-7').val(ui.item.data[1]);
        $('#cost-7').val(ui.item.data[12]);
        $('#w_qty-7').val(ui.item.data[13]);
        $('#w_unit-7').val(ui.item.data[14]);
        $('#pid-7').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-7').val(vat_price);
        $('#discount-7').val(discount);
        $('#dpid-7').val(ui.item.data[5]);
        $('#unit-7').val(ui.item.data[6]);
        $('#hsn-7').val(ui.item.data[7]);

        $('#alert-7').val(ui.item.data[8]);
        $('#serial-7').val(ui.item.data[10]);
        $('#vattype-7').val(ui.item.data[11]);
        rowTotal(7);
        $('#pack_units-7').val(ui.item.data[8]);

        billUpyog();
        $('#productname-8').focus();

    }
});


$('#productname-8').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-8').val(1);
        $('#price-8').val(ui.item.data[1]);
        $('#cost-8').val(ui.item.data[12]);
        $('#w_qty-8').val(ui.item.data[13]);
        $('#w_unit-8').val(ui.item.data[14]);
        $('#pid-8').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-8').val(vat_price);
        $('#discount-8').val(discount);
        $('#dpid-8').val(ui.item.data[5]);
        $('#unit-8').val(ui.item.data[6]);
        $('#hsn-8').val(ui.item.data[7]);

        $('#alert-8').val(ui.item.data[8]);
        $('#serial-8').val(ui.item.data[10]);
        $('#vattype-8').val(ui.item.data[11]);
        rowTotal(8);
        $('#pack_units-8').val(ui.item.data[8]);

        billUpyog();
        $('#productname-9').focus();

    }
});



$('#productname-9').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-9').val(1);
        $('#price-9').val(ui.item.data[1]);
        $('#cost-9').val(ui.item.data[12]);
        $('#w_qty-9').val(ui.item.data[13]);
        $('#w_unit-9').val(ui.item.data[14]);
        $('#pid-9').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-9').val(vat_price);
        $('#discount-9').val(discount);
        $('#dpid-9').val(ui.item.data[5]);
        $('#unit-9').val(ui.item.data[6]);
        $('#hsn-9').val(ui.item.data[7]);
        $('#alert-9').val(ui.item.data[8]);

        $('#serial-9').val(ui.item.data[10]);
        $('#vattype-9').val(ui.item.data[11]);
        $('#pack_units-9').val(ui.item.data[8]);
        rowTotal(9);

        billUpyog();

        $('#productname-10').focus();
    }
});


$('#productname-10').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-10').val(1);
        $('#price-10').val(ui.item.data[1]);
        $('#cost-10').val(ui.item.data[12]);
        $('#w_qty-10').val(ui.item.data[13]);
        $('#w_unit-10').val(ui.item.data[14]);
        $('#pid-10').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-10').val(vat_price);
        $('#discount-10').val(discount);
        $('#dpid-10').val(ui.item.data[5]);
        $('#unit-10').val(ui.item.data[6]);
        $('#hsn-10').val(ui.item.data[7]);
        $('#alert-10').val(ui.item.data[8]);

        $('#serial-10').val(ui.item.data[10]);
        $('#vattype-10').val(ui.item.data[11]);
        $('#pack_units-10').val(ui.item.data[8]);
        rowTotal(10);

        billUpyog();
        $('#productname-11').focus();

    }
});



$('#productname-11').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-11').val(1);
        $('#price-11').val(ui.item.data[1]);
        $('#cost-11').val(ui.item.data[12]);
        $('#w_qty-11').val(ui.item.data[13]);
        $('#w_unit-11').val(ui.item.data[14]);
        $('#pid-11').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-11').val(vat_price);
        $('#discount-11').val(discount);
        $('#dpid-11').val(ui.item.data[5]);
        $('#unit-11').val(ui.item.data[6]);
        $('#hsn-11').val(ui.item.data[7]);
        $('#alert-11').val(ui.item.data[8]);

        $('#serial-11').val(ui.item.data[10]);
        $('#vattype-11').val(ui.item.data[11]);
        $('#pack_units-11').val(ui.item.data[8]);
        rowTotal(11);

        billUpyog();
        $('#productname-12').focus();

    }
});


$('#productname-12').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-12').val(1);
        $('#price-12').val(ui.item.data[1]);
        $('#cost-12').val(ui.item.data[12]);
        $('#w_qty-12').val(ui.item.data[13]);
        $('#w_unit-12').val(ui.item.data[14]);
        $('#pid-12').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-12').val(vat_price);
        $('#discount-12').val(discount);
        $('#dpid-12').val(ui.item.data[5]);
        $('#unit-12').val(ui.item.data[6]);

        $('#hsn-12').val(ui.item.data[7]);
        $('#alert-12').val(ui.item.data[8]);
        $('#serial-12').val(ui.item.data[10]);
        $('#vattype-12').val(ui.item.data[11]);
        rowTotal(12);
        $('#pack_units-12').val(ui.item.data[8]);

        billUpyog();
        $('#productname-13').focus();

    }
});



$('#productname-13').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-13').val(1);
        $('#price-13').val(ui.item.data[1]);
        $('#cost-13').val(ui.item.data[12]);
        $('#w_qty-13').val(ui.item.data[13]);
        $('#w_unit-13').val(ui.item.data[14]);
        $('#pid-13').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-13').val(vat_price);
        $('#discount-13').val(discount);
        $('#dpid-13').val(ui.item.data[5]);
        $('#unit-13').val(ui.item.data[6]);
        $('#hsn-13').val(ui.item.data[7]);

        $('#alert-13').val(ui.item.data[8]);
        $('#serial-13').val(ui.item.data[10]);
        $('#vattype-13').val(ui.item.data[11]);
        rowTotal(13);
        $('#pack_units-13').val(ui.item.data[8]);

        billUpyog();
        $('#productname-14').focus();

    }
});


$('#productname-14').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-14').val(1);
        $('#price-14').val(ui.item.data[1]);
        $('#cost-14').val(ui.item.data[12]);
        $('#w_qty-14').val(ui.item.data[13]);
        $('#w_unit-14').val(ui.item.data[14]);
        $('#pid-14').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-14').val(vat_price);
        $('#discount-14').val(discount);
        $('#dpid-14').val(ui.item.data[5]);
        $('#unit-14').val(ui.item.data[6]);

        $('#hsn-14').val(ui.item.data[7]);
        $('#alert-14').val(ui.item.data[8]);
        $('#serial-14').val(ui.item.data[10]);
        $('#vattype-14').val(ui.item.data[11]);
        rowTotal(14);
        $('#pack_units-14').val(ui.item.data[8]);

        billUpyog();

        $('#productname-15').focus();
    }
});

$('#productname-15').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-15').val(1);
        $('#price-15').val(ui.item.data[1]);
        $('#cost-15').val(ui.item.data[12]);
        $('#w_qty-15').val(ui.item.data[13]);
        $('#w_unit-15').val(ui.item.data[14]);
        $('#pid-15').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-15').val(vat_price);
        $('#discount-15').val(discount);
        $('#dpid-15').val(ui.item.data[5]);
        $('#unit-15').val(ui.item.data[6]);

        $('#hsn-15').val(ui.item.data[7]);
        $('#alert-15').val(ui.item.data[8]);
        $('#serial-15').val(ui.item.data[10]);
        $('#vattype-15').val(ui.item.data[11]);
        rowTotal(15);
        $('#pack_units-15').val(ui.item.data[8]);

        billUpyog();

        $('#productname-16').focus();
    }
});


$('#productname-16').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-16').val(1);
        $('#price-16').val(ui.item.data[1]);
        $('#cost-16').val(ui.item.data[12]);
        $('#w_qty-16').val(ui.item.data[13]);
        $('#w_unit-16').val(ui.item.data[14]);
        $('#pid-16').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-16').val(vat_price);
        $('#discount-16').val(discount);
        $('#dpid-16').val(ui.item.data[5]);
        $('#unit-16').val(ui.item.data[6]);

        $('#hsn-16').val(ui.item.data[7]);
        $('#alert-16').val(ui.item.data[8]);
        $('#serial-16').val(ui.item.data[10]);
        $('#vattype-16').val(ui.item.data[11]);
        rowTotal(16);
        $('#pack_units-16').val(ui.item.data[8]);

        billUpyog();

        $('#productname-17').focus();
    }
});



$('#productname-17').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-17').val(1);
        $('#price-17').val(ui.item.data[1]);
        $('#cost-17').val(ui.item.data[12]);
        $('#w_qty-17').val(ui.item.data[13]);
        $('#w_unit-17').val(ui.item.data[14]);
        $('#pid-17').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-17').val(vat_price);
        $('#discount-17').val(discount);
        $('#dpid-17').val(ui.item.data[5]);
        $('#unit-17').val(ui.item.data[6]);
        $('#hsn-17').val(ui.item.data[7]);

        $('#alert-17').val(ui.item.data[8]);
        $('#serial-17').val(ui.item.data[10]);
        $('#vattype-17').val(ui.item.data[11]);
        rowTotal(17);
        $('#pack_units-17').val(ui.item.data[8]);

        billUpyog();
        $('#productname-18').focus();

    }
});


$('#productname-19').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-19').val(1);
        $('#price-19').val(ui.item.data[1]);
        $('#cost-19').val(ui.item.data[12]);
        $('#w_qty-19').val(ui.item.data[13]);
        $('#w_unit-19').val(ui.item.data[14]);
        $('#pid-19').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-19').val(vat_price);
        $('#discount-19').val(discount);
        $('#dpid-19').val(ui.item.data[5]);
        $('#unit-19').val(ui.item.data[6]);
        $('#hsn-19').val(ui.item.data[7]);

        $('#alert-19').val(ui.item.data[8]);
        $('#serial-19').val(ui.item.data[10]);
        $('#vattype-19').val(ui.item.data[11]);
        rowTotal(19);
        $('#pack_units-19').val(ui.item.data[8]);

        billUpyog();
        $('#productname-20').focus();

    }
});




$('#productname-18').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-18').val(1);
        $('#price-18').val(ui.item.data[1]);
        $('#cost-18').val(ui.item.data[12]);
        $('#w_qty-18').val(ui.item.data[13]);
        $('#w_unit-18').val(ui.item.data[14]);
        $('#pid-18').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-18').val(vat_price);
        $('#discount-18').val(discount);
        $('#dpid-18').val(ui.item.data[5]);

        $('#unit-18').val(ui.item.data[6]);
        $('#hsn-18').val(ui.item.data[7]);
        $('#alert-18').val(ui.item.data[8]);
        $('#serial-18').val(ui.item.data[10]);
        $('#vattype-18').val(ui.item.data[11]);
        rowTotal(18);
        $('#pack_units-18').val(ui.item.data[8]);

        billUpyog();

        $('#productname-19').focus();
    }
});


$('#productname-20').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-20').val(1);
        $('#price-20').val(ui.item.data[1]);
        $('#cost-20').val(ui.item.data[12]);
        $('#w_qty-20').val(ui.item.data[13]);
        $('#w_unit-20').val(ui.item.data[14]);
        $('#pid-20').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-20').val(vat_price);
        $('#discount-20').val(discount);
        $('#dpid-20').val(ui.item.data[5]);
        $('#unit-20').val(ui.item.data[6]);
        $('#hsn-20').val(ui.item.data[7]);

        $('#alert-20').val(ui.item.data[8]);
        $('#serial-20').val(ui.item.data[10]);
        $('#vattype-20').val(ui.item.data[11]);
        rowTotal(20);
        $('#pack_units-20').val(ui.item.data[8]);

        billUpyog();
        $('#productname-21').focus();

    }
});



$('#productname-21').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-21').val(1);
        $('#price-21').val(ui.item.data[1]);
        $('#cost-21').val(ui.item.data[12]);
        $('#w_qty-21').val(ui.item.data[13]);
        $('#w_unit-21').val(ui.item.data[14]);
        $('#pid-21').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 12/100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-21').val(vat_price);
        $('#discount-21').val(discount);
        $('#dpid-21').val(ui.item.data[5]);
        $('#unit-21').val(ui.item.data[6]);
        $('#hsn-21').val(ui.item.data[7]);

        $('#alert-21').val(ui.item.data[8]);
        $('#serial-21').val(ui.item.data[10]);
        $('#vattype-21').val(ui.item.data[11]);
        rowTotal(21);
        $('#pack_units-21').val(ui.item.data[8]);

        billUpyog();
        $('#productname-22').focus();

    }
});


$('#productname-22').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-22').val(1);
        $('#price-22').val(ui.item.data[1]);
        $('#cost-22').val(ui.item.data[12]);
        $('#w_qty-22').val(ui.item.data[13]);
        $('#w_unit-22').val(ui.item.data[14]);
        $('#pid-22').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 22 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-22').val(vat_price);
        $('#discount-22').val(discount);
        $('#dpid-22').val(ui.item.data[5]);
        $('#unit-22').val(ui.item.data[6]);
        $('#hsn-22').val(ui.item.data[7]);

        $('#alert-22').val(ui.item.data[8]);
        $('#serial-22').val(ui.item.data[10]);
        $('#vattype-22').val(ui.item.data[11]);
        rowTotal(22);
        $('#pack_units-22').val(ui.item.data[8]);

        billUpyog();
        $('#productname-23').focus();

    }
});


$('#productname-23').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-23').val(1);
        $('#price-23').val(ui.item.data[1]);
        $('#cost-23').val(ui.item.data[12]);
        $('#w_qty-23').val(ui.item.data[13]);
        $('#w_unit-23').val(ui.item.data[14]);
        $('#pid-23').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 23 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-23').val(vat_price);
        $('#discount-23').val(discount);
        $('#dpid-23').val(ui.item.data[5]);
        $('#unit-23').val(ui.item.data[6]);
        $('#hsn-23').val(ui.item.data[7]);

        $('#alert-23').val(ui.item.data[8]);
        $('#serial-23').val(ui.item.data[10]);
        $('#vattype-23').val(ui.item.data[11]);
        rowTotal(23);
        $('#pack_units-23').val(ui.item.data[8]);

        billUpyog();
        $('#productname-24').focus();

    }
});


$('#productname-24').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-24').val(1);
        $('#price-24').val(ui.item.data[1]);
        $('#cost-24').val(ui.item.data[12]);
        $('#w_qty-24').val(ui.item.data[13]);
        $('#w_unit-24').val(ui.item.data[14]);
        $('#pid-24').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 24 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-24').val(vat_price);
        $('#discount-24').val(discount);
        $('#dpid-24').val(ui.item.data[5]);
        $('#unit-24').val(ui.item.data[6]);
        $('#hsn-24').val(ui.item.data[7]);

        $('#alert-24').val(ui.item.data[8]);
        $('#serial-24').val(ui.item.data[10]);
        $('#vattype-24').val(ui.item.data[11]);
        rowTotal(24);
        $('#pack_units-24').val(ui.item.data[8]);

        billUpyog();
        $('#productname-25').focus();

    }
});


$('#productname-25').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-25').val(1);
        $('#price-25').val(ui.item.data[1]);
        $('#cost-25').val(ui.item.data[12]);
        $('#w_qty-25').val(ui.item.data[13]);
        $('#w_unit-25').val(ui.item.data[14]);
        $('#pid-25').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 25 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-25').val(vat_price);
        $('#discount-25').val(discount);
        $('#dpid-25').val(ui.item.data[5]);
        $('#unit-25').val(ui.item.data[6]);
        $('#hsn-25').val(ui.item.data[7]);
        $('#alert-25').val(ui.item.data[8]);
        $('#serial-25').val(ui.item.data[10]);
        $('#vattype-25').val(ui.item.data[11]);
        rowTotal(25);
        $('#pack_units-25').val(ui.item.data[8]);

        billUpyog();
        $('#productname-26').focus();

    }
});

$('#productname-26').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-26').val(1);
        $('#price-26').val(ui.item.data[1]);
        $('#cost-26').val(ui.item.data[12]);
        $('#w_qty-26').val(ui.item.data[13]);
        $('#w_unit-26').val(ui.item.data[14]);
        $('#pid-26').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 26 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-26').val(vat_price);
        $('#discount-26').val(discount);
        $('#dpid-26').val(ui.item.data[5]);
        $('#unit-26').val(ui.item.data[6]);
        $('#hsn-26').val(ui.item.data[7]);
        $('#alert-26').val(ui.item.data[8]);
        $('#serial-26').val(ui.item.data[10]);
        $('#vattype-26').val(ui.item.data[11]);
        rowTotal(26);
        $('#pack_units-26').val(ui.item.data[8]);

        billUpyog();
        $('#productname-27').focus();

    }
});


$('#productname-27').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-27').val(1);
        $('#price-27').val(ui.item.data[1]);
        $('#cost-27').val(ui.item.data[12]);
        $('#w_qty-27').val(ui.item.data[13]);
        $('#w_unit-27').val(ui.item.data[14]);
        $('#pid-27').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 27 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-27').val(vat_price);
        $('#discount-27').val(discount);
        $('#dpid-27').val(ui.item.data[5]);
        $('#unit-27').val(ui.item.data[6]);
        $('#hsn-27').val(ui.item.data[7]);
        $('#alert-27').val(ui.item.data[8]);
        $('#serial-27').val(ui.item.data[10]);
        $('#vattype-27').val(ui.item.data[11]);
        rowTotal(27);
        $('#pack_units-27').val(ui.item.data[8]);

        billUpyog();
        $('#productname-28').focus();

    }
});


$('#productname-28').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-28').val(1);
        $('#price-28').val(ui.item.data[1]);
        $('#cost-28').val(ui.item.data[12]);
        $('#w_qty-28').val(ui.item.data[13]);
        $('#w_unit-28').val(ui.item.data[14]);
        $('#pid-28').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 28 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-28').val(vat_price);
        $('#discount-28').val(discount);
        $('#dpid-28').val(ui.item.data[5]);
        $('#unit-28').val(ui.item.data[6]);
        $('#hsn-28').val(ui.item.data[7]);
        $('#alert-28').val(ui.item.data[8]);
        $('#serial-28').val(ui.item.data[10]);
        $('#vattype-28').val(ui.item.data[11]);
        rowTotal(28);
        $('#pack_units-28').val(ui.item.data[8]);

        billUpyog();
        $('#productname-29').focus();

    }
});


$('#productname-29').autocomplete({
    source: function (request, response) {
        let cid = $('#customer_id').val();
        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + '-' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-29').val(1);
        $('#price-29').val(ui.item.data[1]);
        $('#cost-29').val(ui.item.data[12]);
        $('#w_qty-29').val(ui.item.data[13]);
        $('#w_unit-29').val(ui.item.data[14]);
        $('#pid-29').val(ui.item.data[2]);
        if (ui.item.data[11] == 'T1') {
            var vat_price = deciFormat(ui.item.data[1] * 29 / 100);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-29').val(vat_price);
        $('#discount-29').val(discount);
        $('#dpid-29').val(ui.item.data[5]);
        $('#unit-29').val(ui.item.data[6]);
        $('#hsn-29').val(ui.item.data[7]);
        $('#alert-29').val(ui.item.data[8]);
        $('#serial-29').val(ui.item.data[10]);
        $('#vattype-29').val(ui.item.data[11]);
        rowTotal(29);
        $('#pack_units-29').val(ui.item.data[8]);

        billUpyog();
        $('#productname-30').focus();

    }
});

$('#productname-30').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-30').val(qytt);
        $('#price-30').val(ui.item.data[1]);
        $('#cost-30').val(ui.item.data[12]);
        $('#w_qty-30').val(ui.item.data[13]);
        $('#w_unit-30').val(ui.item.data[14]);
        $('#pid-30').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-30').val(vat_price);
        $('#discount-30').val(discount);
        $('#dpid-30').val(ui.item.data[5]);
        $('#unit-30').val(ui.item.data[6]);
        $('#hsn-30').val(ui.item.data[7]);

        $('#alert-30').val(ui.item.data[8]);
        $('#serial-30').val(ui.item.data[10]);
        $('#vattype-30').val(ui.item.data[11]);
        rowTotal(30);
        $('#pack_units-30').val(ui.item.data[8]);

        billUpyog();
        $('#amount-31').focus();

    }
});

$('#productname-31').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-31').val(qytt);
        $('#price-31').val(ui.item.data[1]);
        $('#cost-31').val(ui.item.data[12]);
        $('#w_qty-31').val(ui.item.data[13]);
        $('#w_unit-31').val(ui.item.data[14]);
        $('#pid-31').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-31').val(vat_price);
        $('#discount-31').val(discount);
        $('#dpid-31').val(ui.item.data[5]);
        $('#unit-31').val(ui.item.data[6]);
        $('#hsn-31').val(ui.item.data[7]);

        $('#alert-31').val(ui.item.data[8]);
        $('#serial-31').val(ui.item.data[10]);
        $('#vattype-31').val(ui.item.data[11]);
        rowTotal(31);
        $('#pack_units-31').val(ui.item.data[8]);

        billUpyog();
        $('#amount-32').focus();

    }
});

$('#productname-32').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-32').val(qytt);
        $('#price-32').val(ui.item.data[1]);
        $('#cost-32').val(ui.item.data[12]);
        $('#w_qty-32').val(ui.item.data[13]);
        $('#w_unit-32').val(ui.item.data[14]);
        $('#pid-32').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-32').val(vat_price);
        $('#discount-32').val(discount);
        $('#dpid-32').val(ui.item.data[5]);
        $('#unit-32').val(ui.item.data[6]);
        $('#hsn-32').val(ui.item.data[7]);

        $('#alert-32').val(ui.item.data[8]);
        $('#serial-32').val(ui.item.data[10]);
        $('#vattype-32').val(ui.item.data[11]);
        rowTotal(32);
        $('#pack_units-32').val(ui.item.data[8]);

        billUpyog();
        $('#amount-33').focus();

    }
});
$('#productname-33').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-33').val(qytt);
        $('#price-33').val(ui.item.data[1]);
        $('#cost-33').val(ui.item.data[12]);
        $('#w_qty-33').val(ui.item.data[13]);
        $('#w_unit-33').val(ui.item.data[14]);
        $('#pid-33').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-33').val(vat_price);
        $('#discount-33').val(discount);
        $('#dpid-33').val(ui.item.data[5]);
        $('#unit-33').val(ui.item.data[6]);
        $('#hsn-33').val(ui.item.data[7]);

        $('#alert-33').val(ui.item.data[8]);
        $('#serial-33').val(ui.item.data[10]);
        $('#vattype-33').val(ui.item.data[11]);
        rowTotal(33);
        $('#pack_units-33').val(ui.item.data[8]);

        billUpyog();
        $('#amount-34').focus();

    }
});
$('#productname-34').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-34').val(qytt);
        $('#price-34').val(ui.item.data[1]);
        $('#cost-34').val(ui.item.data[12]);
        $('#w_qty-34').val(ui.item.data[13]);
        $('#w_unit-34').val(ui.item.data[14]);
        $('#pid-34').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-34').val(vat_price);
        $('#discount-34').val(discount);
        $('#dpid-34').val(ui.item.data[5]);
        $('#unit-34').val(ui.item.data[6]);
        $('#hsn-34').val(ui.item.data[7]);

        $('#alert-34').val(ui.item.data[8]);
        $('#serial-34').val(ui.item.data[10]);
        $('#vattype-34').val(ui.item.data[11]);
        rowTotal(34);
        $('#pack_units-34').val(ui.item.data[8]);

        billUpyog();
        $('#amount-35').focus();

    }
});
$('#productname-35').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-35').val(qytt);
        $('#price-35').val(ui.item.data[1]);
        $('#cost-35').val(ui.item.data[12]);
        $('#w_qty-35').val(ui.item.data[13]);
        $('#w_unit-35').val(ui.item.data[14]);
        $('#pid-35').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-35').val(vat_price);
        $('#discount-35').val(discount);
        $('#dpid-35').val(ui.item.data[5]);
        $('#unit-35').val(ui.item.data[6]);
        $('#hsn-35').val(ui.item.data[7]);

        $('#alert-35').val(ui.item.data[8]);
        $('#serial-35').val(ui.item.data[10]);
        $('#vattype-35').val(ui.item.data[11]);
        rowTotal(35);
        $('#pack_units-35').val(ui.item.data[8]);

        billUpyog();
        $('#amount-36').focus();

    }
});
$('#productname-36').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-36').val(qytt);
        $('#price-36').val(ui.item.data[1]);
        $('#cost-36').val(ui.item.data[12]);
        $('#w_qty-36').val(ui.item.data[13]);
        $('#w_unit-36').val(ui.item.data[14]);
        $('#pid-36').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-36').val(vat_price);
        $('#discount-36').val(discount);
        $('#dpid-36').val(ui.item.data[5]);
        $('#unit-36').val(ui.item.data[6]);
        $('#hsn-36').val(ui.item.data[7]);

        $('#alert-36').val(ui.item.data[8]);
        $('#serial-36').val(ui.item.data[10]);
        $('#vattype-36').val(ui.item.data[11]);
        rowTotal(36);
        $('#pack_units-36').val(ui.item.data[8]);

        billUpyog();
        $('#amount-37').focus();

    }
});
$('#productname-37').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-37').val(qytt);
        $('#price-37').val(ui.item.data[1]);
        $('#cost-37').val(ui.item.data[12]);
        $('#w_qty-37').val(ui.item.data[13]);
        $('#w_unit-37').val(ui.item.data[14]);
        $('#pid-37').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-37').val(vat_price);
        $('#discount-37').val(discount);
        $('#dpid-37').val(ui.item.data[5]);
        $('#unit-37').val(ui.item.data[6]);
        $('#hsn-37').val(ui.item.data[7]);

        $('#alert-37').val(ui.item.data[8]);
        $('#serial-37').val(ui.item.data[10]);
        $('#vattype-37').val(ui.item.data[11]);
        rowTotal(37);
        $('#pack_units-37').val(ui.item.data[8]);

        billUpyog();
        $('#amount-38').focus();

    }
});
$('#productname-38').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-38').val(qytt);
        $('#price-38').val(ui.item.data[1]);
        $('#cost-38').val(ui.item.data[12]);
        $('#w_qty-38').val(ui.item.data[13]);
        $('#w_unit-38').val(ui.item.data[14]);
        $('#pid-38').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-38').val(vat_price);
        $('#discount-38').val(discount);
        $('#dpid-38').val(ui.item.data[5]);
        $('#unit-38').val(ui.item.data[6]);
        $('#hsn-38').val(ui.item.data[7]);

        $('#alert-38').val(ui.item.data[8]);
        $('#serial-38').val(ui.item.data[10]);
        $('#vattype-38').val(ui.item.data[11]);
        rowTotal(38);
        $('#pack_units-38').val(ui.item.data[8]);

        billUpyog();
        $('#amount-39').focus();

    }
});
$('#productname-39').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-39').val(qytt);
        $('#price-39').val(ui.item.data[1]);
        $('#cost-39').val(ui.item.data[12]);
        $('#w_qty-39').val(ui.item.data[13]);
        $('#w_unit-39').val(ui.item.data[14]);
        $('#pid-39').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-39').val(vat_price);
        $('#discount-39').val(discount);
        $('#dpid-39').val(ui.item.data[5]);
        $('#unit-39').val(ui.item.data[6]);
        $('#hsn-39').val(ui.item.data[7]);

        $('#alert-39').val(ui.item.data[8]);
        $('#serial-39').val(ui.item.data[10]);
        $('#vattype-39').val(ui.item.data[11]);
        rowTotal(39);
        $('#pack_units-39').val(ui.item.data[8]);

        billUpyog();
        $('#amount-40').focus();

    }
});
$('#productname-40').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-40').val(qytt);
        $('#price-40').val(ui.item.data[1]);
        $('#cost-40').val(ui.item.data[12]);
        $('#w_qty-40').val(ui.item.data[13]);
        $('#w_unit-40').val(ui.item.data[14]);
        $('#pid-40').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-40').val(vat_price);
        $('#discount-40').val(discount);
        $('#dpid-40').val(ui.item.data[5]);
        $('#unit-40').val(ui.item.data[6]);
        $('#hsn-40').val(ui.item.data[7]);

        $('#alert-40').val(ui.item.data[8]);
        $('#serial-40').val(ui.item.data[10]);
        $('#vattype-40').val(ui.item.data[11]);
        rowTotal(40);
        $('#pack_units-40').val(ui.item.data[8]);

        billUpyog();
        $('#amount-41').focus();

    }
});
$('#productname-41').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-41').val(qytt);
        $('#price-41').val(ui.item.data[1]);
        $('#cost-41').val(ui.item.data[12]);
        $('#w_qty-41').val(ui.item.data[13]);
        $('#w_unit-41').val(ui.item.data[14]);
        $('#pid-41').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-41').val(vat_price);
        $('#discount-41').val(discount);
        $('#dpid-41').val(ui.item.data[5]);
        $('#unit-41').val(ui.item.data[6]);
        $('#hsn-41').val(ui.item.data[7]);

        $('#alert-41').val(ui.item.data[8]);
        $('#serial-41').val(ui.item.data[10]);
        $('#vattype-41').val(ui.item.data[11]);
        rowTotal(41);
        $('#pack_units-41').val(ui.item.data[8]);

        billUpyog();
        $('#amount-42').focus();

    }
});
$('#productname-42').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-42').val(qytt);
        $('#price-42').val(ui.item.data[1]);
        $('#cost-42').val(ui.item.data[12]);
        $('#w_qty-42').val(ui.item.data[13]);
        $('#w_unit-42').val(ui.item.data[14]);
        $('#pid-42').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-42').val(vat_price);
        $('#discount-42').val(discount);
        $('#dpid-42').val(ui.item.data[5]);
        $('#unit-42').val(ui.item.data[6]);
        $('#hsn-42').val(ui.item.data[7]);

        $('#alert-42').val(ui.item.data[8]);
        $('#serial-42').val(ui.item.data[10]);
        $('#vattype-42').val(ui.item.data[11]);
        rowTotal(42);
        $('#pack_units-42').val(ui.item.data[8]);

        billUpyog();
        $('#amount-43').focus();

    }
});
$('#productname-43').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-43').val(qytt);
        $('#price-43').val(ui.item.data[1]);
        $('#cost-43').val(ui.item.data[12]);
        $('#w_qty-43').val(ui.item.data[13]);
        $('#w_unit-43').val(ui.item.data[14]);
        $('#pid-43').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-43').val(vat_price);
        $('#discount-43').val(discount);
        $('#dpid-43').val(ui.item.data[5]);
        $('#unit-43').val(ui.item.data[6]);
        $('#hsn-43').val(ui.item.data[7]);

        $('#alert-43').val(ui.item.data[8]);
        $('#serial-43').val(ui.item.data[10]);
        $('#vattype-43').val(ui.item.data[11]);
        rowTotal(43);
        $('#pack_units-43').val(ui.item.data[8]);

        billUpyog();
        $('#amount-44').focus();

    }
});
$('#productname-44').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-44').val(qytt);
        $('#price-44').val(ui.item.data[1]);
        $('#cost-44').val(ui.item.data[12]);
        $('#w_qty-44').val(ui.item.data[13]);
        $('#w_unit-44').val(ui.item.data[14]);
        $('#pid-44').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-44').val(vat_price);
        $('#discount-44').val(discount);
        $('#dpid-44').val(ui.item.data[5]);
        $('#unit-44').val(ui.item.data[6]);
        $('#hsn-44').val(ui.item.data[7]);

        $('#alert-44').val(ui.item.data[8]);
        $('#serial-44').val(ui.item.data[10]);
        $('#vattype-44').val(ui.item.data[11]);
        rowTotal(44);
        $('#pack_units-44').val(ui.item.data[8]);

        billUpyog();
        $('#amount-45').focus();

    }
});
$('#productname-45').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-45').val(qytt);
        $('#price-45').val(ui.item.data[1]);
        $('#cost-45').val(ui.item.data[12]);
        $('#w_qty-45').val(ui.item.data[13]);
        $('#w_unit-45').val(ui.item.data[14]);
        $('#pid-45').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-45').val(vat_price);
        $('#discount-45').val(discount);
        $('#dpid-45').val(ui.item.data[5]);
        $('#unit-45').val(ui.item.data[6]);
        $('#hsn-45').val(ui.item.data[7]);

        $('#alert-45').val(ui.item.data[8]);
        $('#serial-45').val(ui.item.data[10]);
        $('#vattype-45').val(ui.item.data[11]);
        rowTotal(45);
        $('#pack_units-45').val(ui.item.data[8]);

        billUpyog();
        $('#amount-46').focus();

    }
});
$('#productname-46').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-46').val(qytt);
        $('#price-46').val(ui.item.data[1]);
        $('#cost-46').val(ui.item.data[12]);
        $('#w_qty-46').val(ui.item.data[13]);
        $('#w_unit-46').val(ui.item.data[14]);
        $('#pid-46').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-46').val(vat_price);
        $('#discount-46').val(discount);
        $('#dpid-46').val(ui.item.data[5]);
        $('#unit-46').val(ui.item.data[6]);
        $('#hsn-46').val(ui.item.data[7]);

        $('#alert-46').val(ui.item.data[8]);
        $('#serial-46').val(ui.item.data[10]);
        $('#vattype-46').val(ui.item.data[11]);
        rowTotal(46);
        $('#pack_units-46').val(ui.item.data[8]);

        billUpyog();
        $('#amount-47').focus();

    }
});
$('#productname-47').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-47').val(qytt);
        $('#price-47').val(ui.item.data[1]);
        $('#cost-47').val(ui.item.data[12]);
        $('#w_qty-47').val(ui.item.data[13]);
        $('#w_unit-47').val(ui.item.data[14]);
        $('#pid-47').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-47').val(vat_price);
        $('#discount-47').val(discount);
        $('#dpid-47').val(ui.item.data[5]);
        $('#unit-47').val(ui.item.data[6]);
        $('#hsn-47').val(ui.item.data[7]);

        $('#alert-47').val(ui.item.data[8]);
        $('#serial-47').val(ui.item.data[10]);
        $('#vattype-47').val(ui.item.data[11]);
        rowTotal(47);
        $('#pack_units-47').val(ui.item.data[8]);

        billUpyog();
        $('#amount-48').focus();

    }
});
$('#productname-48').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-48').val(qytt);
        $('#price-48').val(ui.item.data[1]);
        $('#cost-48').val(ui.item.data[12]);
        $('#w_qty-48').val(ui.item.data[13]);
        $('#w_unit-48').val(ui.item.data[14]);
        $('#pid-48').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-48').val(vat_price);
        $('#discount-48').val(discount);
        $('#dpid-48').val(ui.item.data[5]);
        $('#unit-48').val(ui.item.data[6]);
        $('#hsn-48').val(ui.item.data[7]);

        $('#alert-48').val(ui.item.data[8]);
        $('#serial-48').val(ui.item.data[10]);
        $('#vattype-48').val(ui.item.data[11]);
        rowTotal(48);
        $('#pack_units-48').val(ui.item.data[8]);

        billUpyog();
        $('#amount-49').focus();

    }
});
$('#productname-49').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-49').val(qytt);
        $('#price-49').val(ui.item.data[1]);
        $('#cost-49').val(ui.item.data[12]);
        $('#w_qty-49').val(ui.item.data[13]);
        $('#w_unit-49').val(ui.item.data[14]);
        $('#pid-49').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-49').val(vat_price);
        $('#discount-49').val(discount);
        $('#dpid-49').val(ui.item.data[5]);
        $('#unit-49').val(ui.item.data[6]);
        $('#hsn-49').val(ui.item.data[7]);

        $('#alert-49').val(ui.item.data[8]);
        $('#serial-49').val(ui.item.data[10]);
        $('#vattype-49').val(ui.item.data[11]);
        rowTotal(49);
        $('#pack_units-49').val(ui.item.data[8]);

        billUpyog();
        $('#amount-50').focus();

    }
});

$('#productname-50').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-50').val(qytt);
        $('#price-50').val(ui.item.data[1]);
        $('#cost-50').val(ui.item.data[12]);
        $('#w_qty-50').val(ui.item.data[13]);
        $('#w_unit-50').val(ui.item.data[14]);
        $('#pid-50').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-50').val(vat_price);
        $('#discount-50').val(discount);
        $('#dpid-50').val(ui.item.data[5]);
        $('#unit-50').val(ui.item.data[6]);
        $('#hsn-50').val(ui.item.data[7]);

        $('#alert-50').val(ui.item.data[8]);
        $('#serial-50').val(ui.item.data[10]);
        $('#vattype-50').val(ui.item.data[11]);
        rowTotal(50);
        $('#pack_units-50').val(ui.item.data[8]);

        billUpyog();
        $('#amount-51').focus();

    }
});
$('#productname-51').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-51').val(qytt);
        $('#price-51').val(ui.item.data[1]);
        $('#cost-51').val(ui.item.data[12]);
        $('#w_qty-51').val(ui.item.data[13]);
        $('#w_unit-51').val(ui.item.data[14]);
        $('#pid-51').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-51').val(vat_price);
        $('#discount-51').val(discount);
        $('#dpid-51').val(ui.item.data[5]);
        $('#unit-51').val(ui.item.data[6]);
        $('#hsn-51').val(ui.item.data[7]);

        $('#alert-51').val(ui.item.data[8]);
        $('#serial-51').val(ui.item.data[10]);
        $('#vattype-51').val(ui.item.data[11]);
        rowTotal(51);
        $('#pack_units-51').val(ui.item.data[8]);

        billUpyog();
        $('#amount-52').focus();

    }
});
$('#productname-52').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-52').val(qytt);
        $('#price-52').val(ui.item.data[1]);
        $('#cost-52').val(ui.item.data[12]);
        $('#w_qty-52').val(ui.item.data[13]);
        $('#w_unit-52').val(ui.item.data[14]);
        $('#pid-52').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-52').val(vat_price);
        $('#discount-52').val(discount);
        $('#dpid-52').val(ui.item.data[5]);
        $('#unit-52').val(ui.item.data[6]);
        $('#hsn-52').val(ui.item.data[7]);

        $('#alert-52').val(ui.item.data[8]);
        $('#serial-52').val(ui.item.data[10]);
        $('#vattype-52').val(ui.item.data[11]);
        rowTotal(52);
        $('#pack_units-52').val(ui.item.data[8]);

        billUpyog();
        $('#amount-53').focus();

    }
});
$('#productname-53').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-53').val(qytt);
        $('#price-53').val(ui.item.data[1]);
        $('#cost-53').val(ui.item.data[12]);
        $('#w_qty-53').val(ui.item.data[13]);
        $('#w_unit-53').val(ui.item.data[14]);
        $('#pid-53').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-53').val(vat_price);
        $('#discount-53').val(discount);
        $('#dpid-53').val(ui.item.data[5]);
        $('#unit-53').val(ui.item.data[6]);
        $('#hsn-53').val(ui.item.data[7]);

        $('#alert-53').val(ui.item.data[8]);
        $('#serial-53').val(ui.item.data[10]);
        $('#vattype-53').val(ui.item.data[11]);
        rowTotal(53);
        $('#pack_units-53').val(ui.item.data[8]);

        billUpyog();
        $('#amount-54').focus();

    }
});
$('#productname-54').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-54').val(qytt);
        $('#price-54').val(ui.item.data[1]);
        $('#cost-54').val(ui.item.data[12]);
        $('#w_qty-54').val(ui.item.data[13]);
        $('#w_unit-54').val(ui.item.data[14]);
        $('#pid-54').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-54').val(vat_price);
        $('#discount-54').val(discount);
        $('#dpid-54').val(ui.item.data[5]);
        $('#unit-54').val(ui.item.data[6]);
        $('#hsn-54').val(ui.item.data[7]);

        $('#alert-54').val(ui.item.data[8]);
        $('#serial-54').val(ui.item.data[10]);
        $('#vattype-54').val(ui.item.data[11]);
        rowTotal(54);
        $('#pack_units-54').val(ui.item.data[8]);

        billUpyog();
        $('#amount-55').focus();

    }
});
$('#productname-55').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-55').val(qytt);
        $('#price-55').val(ui.item.data[1]);
        $('#cost-55').val(ui.item.data[12]);
        $('#w_qty-55').val(ui.item.data[13]);
        $('#w_unit-55').val(ui.item.data[14]);
        $('#pid-55').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-55').val(vat_price);
        $('#discount-55').val(discount);
        $('#dpid-55').val(ui.item.data[5]);
        $('#unit-55').val(ui.item.data[6]);
        $('#hsn-55').val(ui.item.data[7]);

        $('#alert-55').val(ui.item.data[8]);
        $('#serial-55').val(ui.item.data[10]);
        $('#vattype-55').val(ui.item.data[11]);
        rowTotal(55);
        $('#pack_units-55').val(ui.item.data[8]);

        billUpyog();
        $('#amount-56').focus();

    }
});
$('#productname-56').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-56').val(qytt);
        $('#price-56').val(ui.item.data[1]);
        $('#cost-56').val(ui.item.data[12]);
        $('#w_qty-56').val(ui.item.data[13]);
        $('#w_unit-56').val(ui.item.data[14]);
        $('#pid-56').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-56').val(vat_price);
        $('#discount-56').val(discount);
        $('#dpid-56').val(ui.item.data[5]);
        $('#unit-56').val(ui.item.data[6]);
        $('#hsn-56').val(ui.item.data[7]);

        $('#alert-56').val(ui.item.data[8]);
        $('#serial-56').val(ui.item.data[10]);
        $('#vattype-56').val(ui.item.data[11]);
        rowTotal(56);
        $('#pack_units-56').val(ui.item.data[8]);

        billUpyog();
        $('#amount-57').focus();

    }
});
$('#productname-57').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-57').val(qytt);
        $('#price-57').val(ui.item.data[1]);
        $('#cost-57').val(ui.item.data[12]);
        $('#w_qty-57').val(ui.item.data[13]);
        $('#w_unit-57').val(ui.item.data[14]);
        $('#pid-57').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-57').val(vat_price);
        $('#discount-57').val(discount);
        $('#dpid-57').val(ui.item.data[5]);
        $('#unit-57').val(ui.item.data[6]);
        $('#hsn-57').val(ui.item.data[7]);

        $('#alert-57').val(ui.item.data[8]);
        $('#serial-57').val(ui.item.data[10]);
        $('#vattype-57').val(ui.item.data[11]);
        rowTotal(57);
        $('#pack_units-57').val(ui.item.data[8]);

        billUpyog();
        $('#amount-58').focus();

    }
});
$('#productname-58').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-58').val(qytt);
        $('#price-58').val(ui.item.data[1]);
        $('#cost-58').val(ui.item.data[12]);
        $('#w_qty-58').val(ui.item.data[13]);
        $('#w_unit-58').val(ui.item.data[14]);
        $('#pid-58').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-58').val(vat_price);
        $('#discount-58').val(discount);
        $('#dpid-58').val(ui.item.data[5]);
        $('#unit-58').val(ui.item.data[6]);
        $('#hsn-58').val(ui.item.data[7]);

        $('#alert-58').val(ui.item.data[8]);
        $('#serial-58').val(ui.item.data[10]);
        $('#vattype-58').val(ui.item.data[11]);
        rowTotal(58);
        $('#pack_units-58').val(ui.item.data[8]);

        billUpyog();
        $('#amount-59').focus();

    }
});
$('#productname-59').autocomplete({

    source: function (request, response) {

        let cid = $('#customer_id').val();


        $.ajax({
            url: baseurl + 'search_products/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'name_startsWith=' + request.term + '&cid=' + cid + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&' + d_csrf,
            success: function (data) {
                response($.map(data, function (item) {
                    var product_d = item[7];
                    var product_k = item[0];
                    return {
                        label: product_d + ' - ' + product_k,
                        value: product_d,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
        var t_r = ui.item.data[3];
        if ($("#taxformat option:selected").attr('data-trate')) {

            t_r = $("#taxformat option:selected").attr('data-trate');
        }
        var discount = ui.item.data[4];
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        var qytt = 1;
        $('#amount-59').val(qytt);
        $('#price-59').val(ui.item.data[1]);
        $('#cost-59').val(ui.item.data[12]);
        $('#w_qty-59').val(ui.item.data[13]);
        $('#w_unit-59').val(ui.item.data[14]);
        $('#pid-59').val(ui.item.data[2]);
        //alert(ui.item.data[11]+"=test");
        if (ui.item.data[11] == 'T1') {
            var vat_price = (Math.floor(ui.item.data[1] * tax_rate_decimal * 100) / 100).toFixed(2);
        } else {
            var vat_price = 0;
        }
        //$('#vat-0').val(vat_price);

        $('#vat-59').val(vat_price);
        $('#discount-59').val(discount);
        $('#dpid-59').val(ui.item.data[5]);
        $('#unit-59').val(ui.item.data[6]);
        $('#hsn-59').val(ui.item.data[7]);

        $('#alert-59').val(ui.item.data[8]);
        $('#serial-59').val(ui.item.data[10]);
        $('#vattype-59').val(ui.item.data[11]);
        rowTotal(59);
        $('#pack_units-59').val(ui.item.data[8]);

        billUpyog();
        $('#amount-60').focus();

    }
});
$(document).on('click', ".select_pos_item", function (e) {
    var pid = $(this).attr('data-pid');
    var stock = accounting.unformat($(this).attr('data-stock'), accounting.settings.number.decimal);
    var flag = true;
    var discount = $(this).attr('data-discount');
    var custom_discount = accounting.unformat($('#custom_discount').val(), accounting.settings.number.decimal);
    if (custom_discount > 0) discount = accounting.formatNumber(custom_discount);

    $('.pdIn').each(function () {
        if (pid == $(this).val()) {

            var pi = $(this).attr('id');
            var arr = pi.split('-');
            pi = arr[1];
            $('#discount-' + pi).val(discount);
            var stotal = accounting.unformat($('#amount-' + pi).val(), accounting.settings.number.decimal) + 1;

            if (stotal <= stock) {
                $('#amount-' + pi).val(accounting.formatNumber(stotal));
                $('#search_bar').val('').focus();
            } else {
                $('#stock_alert').modal('toggle');
            }
            rowTotal(pi);
            billUpyog();
            $('#amount-' + pi).focus();
            flag = false;
        }
    });
    var t_r = $(this).attr('data-tax');
    if ($("#taxformat option:selected").attr('data-trate')) {

        var t_r = $("#taxformat option:selected").attr('data-trate');
    }
    if (flag) {
        var ganak = $('#ganak').val();
        var cvalue = parseInt(ganak);
        var functionNum = "'" + cvalue + "'";
        count = $('#saman-row div').length;
        var data = '<tr id="ppid-' + cvalue + '" class="mb-1"><td colspan="7" ><input type="text" class="form-control text-center p-mobile" name="product_name[]" placeholder="Enter Product name or Code" id="productname-' + cvalue + '" value="' + $(this).attr('data-name') + '-' + $(this).attr('data-pcode') + '"><input type="hidden" id="alert-' + cvalue + '" value="' + $(this).attr('data-stock') + '"  name="alert[]"></td></tr><tr><td><input type="text" inputmode="numeric" class="form-control p-mobile p-width req amnt" name="product_qty[]" id="amount-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off" value="1" ></td> <td><input type="text" class="form-control p-width p-mobile req prc" name="product_price[]"  inputmode="numeric" id="price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"  value="' + $(this).attr('data-price') + '"></td><td> <input type="text" class="form-control p-mobile p-width vat" inputmode="numeric" name="product_tax[]" id="vat-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"  value="' + t_r + '"></td>  <td><input type="text" class="form-control p-width p-mobile discount pos_w" name="product_discount[]" inputmode="numeric" onkeypress="return isNumber(event)" id="discount-' + cvalue + '" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"  value="' + discount + '" inputmode="numeric"></td> <td><span class="currenty">' + currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue + '">0</span></strong></td> <td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn-danger removeItem" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="taxa[]" id="taxa-' + cvalue + '" value="0"><input type="hidden" name="disca[]" id="disca-' + cvalue + '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue + '" value="0"> <input type="hidden" class="pdIn" name="pid[]" id="pid-' + cvalue + '" value="' + $(this).attr('data-pid') + '"> <input type="hidden" name="unit[]" id="unit-' + cvalue + '" value="' + $(this).attr('data-unit') + '"> <input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value="' + $(this).attr('data-pcode') + '"> <input type="hidden" name="serial[]" id="serial-' + cvalue + '" value="' + $(this).attr('data-serial') + '"></tr>';

        //ajax request
        // $('#saman-row').append(data);
        $('#pos_items').append(data);
        rowTotal(cvalue);
        billUpyog();
        $('#ganak').val(cvalue + 1);
        $('#amount-' + cvalue).focus();

    }
});



$('#saman-pos2').on('click', '.removeItem', function () {
    var pidd = $(this).attr('data-rowid');
    var pqty = accounting.unformat($('#amount-' + pidd).val(), accounting.settings.number.decimal);
    var old_amnt = $('#amount_old-' + pidd).val();
    if (old_amnt) {
        pqty = pidd + '-' + pqty;
        $('<input>').attr({
            type: 'hidden',
            name: 'restock[]',
            value: pqty
        }).appendTo('form');
    }
    $('#ppid-' + pidd).remove();
    $('.amnt').each(function (index) {
        rowTotal(index);
    });
    billUpyog();
    return false;
});


$('#saman-row-pos').on('click', '.removeItem', function () {

    var pidd = $(this).closest('tr').find('.pdIn').val();
    var pqty = accounting.unformat($(this).closest('tr').find('.amnt').val(), accounting.settings.number.decimal);
    var old_amnt = accounting.unformat($(this).closest('tr').find('.old_amnt').val(), accounting.settings.number.decimal);
    if (old_amnt) {
        pqty = pidd + '-' + pqty;
        $('<input>').attr({
            type: 'hidden',
            name: 'restock[]',
            value: pqty
        }).appendTo('form');
    }
    $(this).closest('tr').remove();
    $('#d' + $(this).closest('tr').find('.pdIn').attr('id')).closest('tr').remove();
    $('#p' + $(this).closest('tr').find('.pdIn').attr('id')).remove();
    $('.amnt').each(function (index) {
        rowTotal(index);

    });
    billUpyog();

    return false;

});


$(document).on('click', ".quantity-up", function (e) {
    var spinner = $(this);
    var input = spinner.closest('.quantity').find('input[name="product_qty[]"]');
    var oldValue = accounting.unformat(input.val(), accounting.settings.number.decimal);

    var newVal = oldValue + 1;
    spinner.closest('.quantity').find('input[name="product_qty[]"]').val(accounting.formatNumber(newVal));
    spinner.closest('.quantity').find('input[name="product_qty[]"]').trigger("change");
    var id_arr = $(input).attr('id');
    id = id_arr.split("-");
    rowTotal(id[1]);
    billUpyog();
    return false;
});


$(document).on('click', ".quantity-down", function (e) {
    var spinner = $(this);
    var input = spinner.closest('.quantity').find('input[name="product_qty[]"]');
    var oldValue = accounting.unformat(input.val(), accounting.settings.number.decimal);
    var min = 1;
    if (oldValue <= min) {
        var newVal = oldValue;
    } else {
        var newVal = oldValue - 1;
    }
    spinner.closest('.quantity').find('input[name="product_qty[]"]').val(accounting.formatNumber(newVal));
    spinner.closest('.quantity').find('input[name="product_qty[]"]').trigger("change");
    var id_arr = $(input).attr('id');
    id = id_arr.split("-");
    rowTotal(id[1]);
    billUpyog();
    return false;
});