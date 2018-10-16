(function($){

    $('#t217_price_dropdown').on('change', function(){

        switch($(this).val()) {
            case 'hide':
                t216_update_price_display('hide');
                break;
            case 'regular':
                t216_update_price_display('regular');
                break;
            case 'retail':
                t216_update_price_display('retail');
                break;
            default:
                t216_update_price_display();
                break;
        }
    });

    function t216_update_price_display(param){
        param = param || '';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: t216.ajaxUrl,
            data: {
                action: 't216_update_price_display',
                param: param
            },
            success: function (res) {
                if (res) {
                    location.reload();
                }
            }
        });
    }

})(jQuery);