(function($){
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
    $('.input-retail-price-proc-for-user').on('input', function(){
        let max = parseInt($(this).attr('max'));
        let min = parseInt($(this).attr('min'));
        let val = parseInt($(this).val());

        if (val > max) {$(this).val(max); alert('Max 500%');}
        if (val < min) {$(this).val(min); alert('Min 0%');}
    });
    $('.input-retail-price-proc-for-user-save').on('click', function () {
        let value = $(this).prev().val();
        let userId = $(this).prev().attr('user-id');
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
})(jQuery);