(function($){
    $(document).ready(function(){
        //$('.variations select option:first-child').val('empty');
        $('.variations select').each(function(){
            if ($(this).find('option').size() == 2 && $(this).find('option:first-child').html() == 'Choose an option') {
                $(this).find('option:nth-child(2)').attr('selected', true);
                $(this).prop( "disabled", true );
                $(this).css('background','#eee');
            } else if ($(this).find('option').size() == 1) {
                $(this).prop( "disabled", true );
                $(this).css('background','#eee');
            } else {
                $(this).prop( "disabled", false );
                $(this).css('background','#fff');

            }
        });
        if ($(window).width() >= 768){
            $('.variations select').on('click', function(){
                $('.variations select').each(function(){
                    if ($(this).find('option').size() == 2 && $(this).find('option:first-child').html() == 'Choose an option') {
                        $(this).find('option:nth-child(2)').attr('selected', true);
                        $(this).prop( "disabled", true );
                        $(this).css('background','#eee');
                    } else if ($(this).find('option').size() == 1) {
                        $(this).prop( "disabled", true );
                        $(this).css('background','#eee');
                    } else {
                        $(this).prop( "disabled", false );
                        $(this).css('background','#fff');
                    }
                });
            });
        } else {
            $('.variations select').on('change tap touchstart focus', function(){
                $('.variations select').each(function(){
                    if ($(this).find('option').size() == 2 && $(this).find('option:first-child').html() == 'Choose an option') {
                        $(this).find('option:nth-child(2)').attr('selected', true);
                        $(this).prop( "disabled", true );
                        $(this).css('background','#eee');
                    } else if ($(this).find('option').size() == 1) {
                        $(this).prop( "disabled", true );
                        $(this).css('background','#eee');
                    } else {
                        $(this).prop( "disabled", false );
                        $(this).css('background','#fff');
                    }
                });
            });
        }
        $('.reset_variations').on('click', function(){
            $('.variations select').each(function(){
                $(this).prop( "disabled", false );
                $(this).css('background','#fff');
            });
        });
    });
})(jQuery);