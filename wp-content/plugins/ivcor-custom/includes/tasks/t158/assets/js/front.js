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
                k = 1;
                $('ul#shipping_method li').each(function () {
                    if (k === 1) {
                        $(document.body).on('updated_checkout', function () {
                            $('ul#shipping_method li:nth-child(1)').hide();
                        });
                    } else if (k === 2) {
                        $(this).find('input').prop('checked', 1);
                    }

                    k++;
                });
            } else {
                k = 1;
                $('ul#shipping_method li').each(function () {
                    if (k === 1) {
                        $(this).find('input').prop('checked', 1);
                        $(document.body).on('updated_checkout', function () {
                            $('ul#shipping_method li:nth-child(1)').show();
                        });
                    }
                    k++;
                });
            }
        });
    }
})(jQuery);