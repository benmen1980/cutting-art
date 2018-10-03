(function($){

    let currentShipingMethod = 1;
    let k = 1;

    $('#shipping_method .shipping_method').each(function(){
        if ($(this).prop('checked')) currentShipingMethod = k;
        k++;
    });

    if (currentShipingMethod === 1) {
        $('#ship-to-different-address-checkbox').on('change', function () {
            if ($(this).prop('checked')) {
                /**
                 * t213
                 */
                $(document.body).on('updated_checkout', function () {
                    k = 1;

                    $('ul#shipping_method li').each(function () {
                        if (k === 1){
                            $(this).hide().find('input').hide();
                        }else if (k === 2){
                            $(this).show().find('input').show().prop('checked', true);
                        }else if (k === currentShipingMethod){
                            $(this).show().find('input').show().prop('checked', true);
                        }else{
                            $(this).show().find('input').show();
                        }
                        k++;
                    });
                });

                $(document).on('change', '.shipping_method', function(){
                    k = 1;
                    $('#shipping_method .shipping_method').each(function(){
                        if ($(this).prop('checked')) currentShipingMethod = k;
                        k++;
                    });
                });

                /**
                 * end t213
                 */
            } else {
                k = 1;
                $('ul#shipping_method li').each(function () {
                    if (k === 1) {
                        $(this).find('input').prop('checked', 1);
                        $(document.body).on('updated_checkout', function () {
                            $('ul#shipping_method li').hide().find('input');//.hide();
                            $('ul#shipping_method li:nth-child(1)').show().find('input').prop('checked', true);
                        });
                    }
                    k++;
                });
            }
        });
    }
})(jQuery);