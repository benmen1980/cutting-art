(function($){
    $('#ship-to-different-address-checkbox').on('change', function(){
        if ($(this).prop('checked')) {
            let k = 1;
            $('ul#shipping_method li').each(function(){
                if (k === 1) {
                    $( document.body ).on('updated_checkout', function(){
                        $('ul#shipping_method li:first-child').hide();
                    });
                } else if (k === 2) {
                    $(this).find('input').prop('checked',1);
                }

                k++;
            });
        }else{
            let k = 1;
            $('ul#shipping_method li').each(function(){
                if (k === 1) {
                    $(this).find('input').prop('checked',1);
                    $( document.body ).on('updated_checkout', function(){
                        $('ul#shipping_method li:first-child').show();
                    });
                }
                k++;
            });
        }
    });
})(jQuery);