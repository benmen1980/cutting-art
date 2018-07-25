(function($){

    if ($(document).find('.order_item').length) {
        $(document).find('.order_item').each(function(){
            let quantity = parseInt($(this).find('.product-quantity').text().replace('× ', ''));
            let symbol = $(this).find('.woocommerce-Price-currencySymbol').text();
            let subtotal = parseFloat($(this).find('.woocommerce-Price-amount').text().replace(symbol, '').replace(/,/g, ''));
            let subtotalNew = (subtotal/quantity).toFixed(2);
            $('.product-quantity').html('');
            $('span.woocommerce-Price-amount').html('<span class="woocommerce-Price-currencySymbol">' + symbol + '</span>' + subtotalNew);
        });
    }

}(jQuery));