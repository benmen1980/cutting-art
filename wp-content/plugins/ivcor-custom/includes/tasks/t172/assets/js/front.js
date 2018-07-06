(function($){
    if (
        $('.woocommerce-order-details').length &&
        $('.woocommerce-column--billing-address').length &&
        $('.woocommerce-column--shipping-address').length
    ) {
        let tabs = {
            order_details: $('.woocommerce-order-details').html(),
            billing_address: $('.woocommerce-column--billing-address').html(),
            shipping_address: $('.woocommerce-column--shipping-address').html()
        };

        let html = '<div class="t172_tabs"><ul>';

        $.each(tabs, function(tabKey, tabHtml){
            html += '<li tab_id="t172_' + tabKey + '" class="t172_tab">' + tabKey.replace('_',' ').replace(/(^|\s)\S/g, l => l.toUpperCase()) + '</li>';
        });

        html += '</ul></div>';

        html += '<div class="t172_tabs_content">';

        $.each(tabs, function(tabKey, tabHtml){
            html += '<div id="t172_' + tabKey + '" class="t172_tab_content">' + tabHtml + '</div>';
        });

        html += '</div>';

        $('.woocommerce-order').append(html);

        $('.t172_tabs li').on('click', function(){
            $('.t172_tabs li').removeClass('active');
            $('.t172_tab_content').hide();
            $(this).addClass('active');
            $('#' + $(this).attr('tab_id')).show();
        });

        $('.t172_tabs li:first-child').trigger('click');

        $('.t172_tabs_content').css('min-height',$('#t172_order_details').height())
    }
})(jQuery);