(function($){

    $('.variations_form').on('reset_data show_variation', function(){
        $('table.variations select').each(function(){
            let dataTmForVariation = $(this).attr('id');
            $(this).find('option').each(function(){
                let value = $(this).attr('value');
                let text = $(this).text();
                $('select[data-tm-for-variation="' + dataTmForVariation + '"]')
                    .find('option[value="' + value + '"]').text(text);
            });
        });
    });

})(jQuery);