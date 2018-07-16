(function($){
    if ($('#shipping_method').length) {
        $( document.body ).on('updated_checkout', function(){
            $('#shipping_method').find('li').hide().each(function(){
                if ($(this).find('.shipping_method').prop('checked')) {
                    $(this).show();
                }
            });
        });
    }
})(jQuery);