(function($){
    $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
        if (variation.price_html)
            $('p.price').html(variation.price_html);
        $('p.price').show();
    } );
})(jQuery);