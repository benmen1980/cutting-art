(function($){
    $(document).on('ready', function(){
        $('.change-payment-method-for-user').on('change', function(){
            let value = $(this).val();
            let userId = $(this).attr('user-id');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: wcdpm.ajaxUrl,
                data: {
                    action: 'change_payment_method_for_user',
                    paymentMethodId: value,
                    userId: userId
                },
                success: function (res) {
                    if (res) alert('Success!')
                }
            });
        });
        $('.change-retail-price-proc-for-user').on('change', function(){
            let value = $(this).val();
            let userId = $(this).attr('user-id');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: wcdpm.ajaxUrl,
                data: {
                    action: 'change_retail_price_proc_for_user',
                    proc: value,
                    userId: userId
                },
                success: function (res) {
                    if (res) alert('Success!')
                }
            });
        });
    });
})(jQuery);