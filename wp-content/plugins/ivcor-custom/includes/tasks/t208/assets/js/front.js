(function($){

    $('.input-hide-price-for-user-save').on('click', function(){
       let hidePrices = $(this).prev().prop('checked');
       $.ajax({
           type: 'POST',
           dataType: 'json',
           url: t208.ajaxUrl,
           data: {
               action: 't208_hide_price_for_user_save',
               hidePrice: hidePrices ? 1 : 0
           },
           success: function () {
                alert('Success!');
           }
       });
    });

    $('.table-retail-price-category-for-user-save').on('click', function(){
       let update = {};
       $('.retail_price_addition').each(function(){
          update[$(this).attr('term_id')] = $(this).attr('proc') ? $(this).attr('proc') : 0;
       });
       $.ajax({
           type: 'POST',
           dataType: 'json',
           url: t208.ajaxUrl,
           data: {
               action: 't208_table_retail_price_category_for_user_save',
               update: update
           },
           success: function () {
               alert('Success!');
           }
       });
    });

    $('.table-retail-price-category-any-for-user-save').on('click', function(){
        let retailPrice = {};
        let newPrice = {};
        let termId = $(this).attr('term-id');

        $('.retail_price_addition').each(function(){
            retailPrice[$(this).attr('variation_id')] = $(this).attr('proc') ? $(this).attr('proc') : 0;
        });

        $('.new_retail_price').each(function(){
            newPrice[$(this).attr('variation_id')] = $(this).attr('price') ? $(this).attr('price') : 0;
        });

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: t208.ajaxUrl,
            data: {
                action: 't208_table_retail_price_category_any_for_user_save',
                retailPrice: retailPrice,
                newPrice: newPrice,
                termId: termId
            },
            success: function () {
                alert('Success!');
            }
        });
    });

    $('.update_category').on('change', function(){
        let status = $(this).prop('checked');
        let elem = $(this).closest('tr').find('.retail_price_addition');
        if (status) {
            let proc = elem.attr('proc');
            elem.html('<input type="number" style="width:70px" value="' + proc + '">%');
        }else{
            let proc = elem.find('input[type="number"]').val();
            elem.html(proc + '%');
            elem.attr('proc', proc);
        }
    });

    $('.retail_price_addition').on('change input', 'input[type="number"]', function(){
        $(this).parent().attr('proc', $(this).val());
    });

    $('.update_price_by_percents').on('change', function(){
        let status = $(this).prop('checked');
        let elem = $(this).closest('tr').find('.retail_price_addition');
        if (status) {
            let proc = elem.attr('proc');
            elem.html('<input type="number" style="width:70px" value="' + proc + '">%');
        }else{
            let proc = elem.find('input[type="number"]').val();
            elem.html(proc + '%');
            elem.attr('proc', proc);
        }
    });

    $('.update_price_manually').on('change', function(){
        let status = $(this).prop('checked');
        let elem = $(this).closest('tr').find('.new_retail_price');
        if (status) {
            let price = elem.attr('price');
            elem.html('<input type="number" style="width:70px" value="' + price + '">');
        }else{
            let price = elem.find('input[type="number"]').val();
            elem.html(price);
            elem.attr('price', price);
        }
    });

    $('.new_retail_price').on('change input', 'input[type="number"]', function(){
        $(this).parent().attr('price', $(this).val());
    });

})(jQuery);