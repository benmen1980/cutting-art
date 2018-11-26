(function($){
    $(document).on('ready', function(){

        $('td.column-name').on('click', function(){
           $(this).parent().find('td.column-sku').trigger('click');
        });

        $('td.column-sku').on('click', function(){
            let sku = $(this).text();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: t131.ajaxUrl,
                data: {
                    action: 't131_get_admin_url_product',
                    sku: sku
                },
                success: function (res) {
                    console.log(res);
                    location.href = res.url;
                }
            });
        });
    });
})(jQuery);