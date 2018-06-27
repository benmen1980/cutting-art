(function($){
    if ($.tc_add_filter) {
        $.tc_add_filter("tc_calculate_product_price", wcac_tc_calculate_product_price, 20, 1);

        function wcac_tc_calculate_product_price(price) {
            if (wcdpm) price = price + price * wcdpm.retailPriceProc / 100;
            return price + price * wcac.retailPriceProc / 100;
        }

    }
})(jQuery);