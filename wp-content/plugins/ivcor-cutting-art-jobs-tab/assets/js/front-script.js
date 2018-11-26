(function($){

    if ($(document).find('.order_item').length) {
        $(document).find('.order_item').each(function(){
            let quantity = parseInt($(this).find('.product-quantity').text().replace('× ', ''));
            let symbol = $(this).find('.woocommerce-Price-currencySymbol').text();
            let subtotal = parseFloat($(this).find('.woocommerce-Price-amount').text().replace(symbol, '').replace(/,/g, ''));
            let subtotalNew = (subtotal/quantity).toFixed(2);
            $('.product-quantity').html('');
            $('span.woocommerce-Price-amount').html('<span class="woocommerce-Price-currencySymbol">' + symbol + '</span>' + subtotalNew);
        });
    }

    if ($(document).find('#job_choose_job').length) {
        $('#job_choose_status').select2({
            placeholder: "Choose Status",
            width: '100%',
            allowClear: true
        });

        $('#job_choose_status').on('select2:select', function () {
            $('#job_choose_job').next().show();
        });

        $('#job_choose_status').on('select2:unselect', function () {
            $('#job_choose_job').next().hide();
            $('#job_scan_number').hide();
        });

        $('#job_choose_job').select2({
            placeholder: "Choose Job",
            width: '100%'
        }).next().hide();

        $('#job_choose_job').on('select2:select', function () {
            $('#job_scan_number').show();
        });

        $('#job_choose_job').on('select2:unselect', function () {
            $('#job_scan_number').hide();
        });

        $('#job_scan_number').on('click', function(){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: job.ajaxUrl,
                data: {
                    action: 'update_job_status',
                    jobs: $('#job_choose_job').val(),
                    status: $('#job_choose_status').val()
                },
                success: function (res) {
                    if (res) {
                        $('.col-full > .woocommerce').html('<div class="woocommerce-message" role="alert">Update of the status succeeded.</div>');
                        $('#job_choose_job').val(null).trigger('select2:unselect');
                        $('#job_choose_status').val(null).trigger('change').trigger('select2:unselect');
                        let tr = '<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">';
                        tr += '<td>' + res.number + '</td>';
                        tr += '<td>' + res.status + '</td>';
                        tr += '<td>' + res.user + '</td>';
                        tr += '<td>' + res.time + '</td>';

                        tr += '</tr>';
                        $('#job-logs tbody').prepend(tr);
                    }else{
                        $('.col-full > .woocommerce').html('<div class="woocommerce-error" role="alert">Job Number does not exist.</div>');
                    }
                    setTimeout(function(){
                        $('.col-full > .woocommerce').html('');
                    }, 5000);
                }
            });
        });
    }

}(jQuery));