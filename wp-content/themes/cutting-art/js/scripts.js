(function($){
	$(document).ready(function(){
        //$('.variations select option:first-child').val('empty');
            $('.variations select').each(function(){
                if ($(this).find('option').size() == 2 && $(this).find('option:first-child').html() == 'Choose an option') {
                    console.log('!!!');
                    //console.log($(this).find('option').size());
                    $(this).find('option:nth-child(2)').attr('selected', true);
                    $(this).prop( "disabled", true );
                } else if ($(this).find('option').size() == 1) {
                    $(this).prop( "disabled", true );
                } else {
                    $(this).prop( "disabled", false ); 
                }
            });
            if ($(window).width() >= 768){
                $('.variations select').on('click', function(){
                    //$(this).addClass('thisSelect');
                    //var thisSelect = $('.variations select.thisSelect');
                    $('.variations select').each(function(){
                        if ($(this).find('option').size() == 2 && $(this).find('option:first-child').html() == 'Choose an option') {
                            console.log('!!!');
                            //console.log($(this).find('option').size());
                            $(this).find('option:nth-child(2)').attr('selected', true);
                            $(this).prop( "disabled", true );
                        } else if ($(this).find('option').size() == 1) {
                            $(this).prop( "disabled", true );
                        } else {
                            $(this).prop( "disabled", false ); 
                        }
                    });
                });
            } else {
                $('.variations select').on('change tap touchstart focus', function(){
                    //$(this).addClass('thisSelect');
                    //var thisSelect = $('.variations select.thisSelect');
                    $('.variations select').each(function(){
                        if ($(this).find('option').size() == 2 && $(this).find('option:first-child').html() == 'Choose an option') {
                            //console.log('!!!');
                            //console.log($(this).find('option').size());
                            $(this).find('option:nth-child(2)').attr('selected', true);
                            $(this).prop( "disabled", true );
                        } else if ($(this).find('option').size() == 1) {
                            $(this).prop( "disabled", true );
                        } else {
                            $(this).prop( "disabled", false ); 
                        }
                    });
                });
            }
            $('.reset_variations').on('click', function(){
                $('.variations select').each(function(){
                    $(this).prop( "disabled", false );
                });
            });
	});
})(jQuery);