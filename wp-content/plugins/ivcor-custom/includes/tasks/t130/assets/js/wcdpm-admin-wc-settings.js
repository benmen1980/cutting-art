(function($){
    $(document).on('ready', function(){
        $('.no-default span').on('click', function(){
            let gatewayId = $(this).attr('gateway-id');
            let $this = $(this);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: wcdpm.ajaxUrl,
                data: {
                    action: 'set_default_payment_method',
                    gatewayId: gatewayId
                },
                success: function (res) {
                    if (res) {
                        $('.wc_gateways .default').removeClass('yes-default').addClass('no-default');
                        $this.parent().addClass('yes-default').removeClass('no-default');
                    }
                }
            });
        });
    });
})(jQuery);