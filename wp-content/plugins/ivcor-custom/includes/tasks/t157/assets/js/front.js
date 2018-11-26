(function($){
    $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
        t157_update_price(variation.price_html);
    });
    $( ".single_variation_wrap" ).on( "hide_variation", function ( event, variation ) {
        t157_update_price(0);
    } );
    setInterval(function(){
        $(document).find('.tc-price').attr('style','display: none !important');
        if ($('dd.tm-final-totals').length && $('p.price').html() !== $('dd.tm-final-totals').html()) {
            t157_update_price($('dd.tm-final-totals').html());
        }
    },100);

    function t157_update_price(price){
        if (price){
            $('p.price').html(price).show();
        } else {
            $('p.price').html('').hide();
        }
    }
})(jQuery);