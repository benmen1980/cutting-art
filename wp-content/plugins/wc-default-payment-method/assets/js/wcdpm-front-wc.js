(function($){
    $.tc_add_filter("tc_calculate_product_price", wcdpm_tc_calculate_product_price, 10, 1);
    function wcdpm_tc_calculate_product_price(price) {
        return price + price * wcdpm.retailPriceProc / 100;
    }
})(jQuery);