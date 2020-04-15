(function($){
    $(document).ready(function(){

        //if ($(window).width() >= 768){
            $('.variations_form').on('reset_data show_variation', function() {
                $('.variations select').each(function () {
                    let $this = $(this);
                        if ($this.find('option').size() === 1) {
                            $this
                                .prop('disabled', true)
                                .css('background', '#eee')
                                .find('option')
                                .prop('selected', true);
                            setTimeout(function(){
                                $('#tm_attribute_id_' + $this.attr('id'))
                                    .prop('disabled', true)
                                    .css('background', '#eee')
                                    .val($this.val());
                            },100);

                        } else if ($this.find('option').size() === 2) {
                            if ($this.find('option:first-child').text() === 'Choose an option') {
                                    $this
                                        .prop('disabled', true)
                                        .css('background', '#eee')
                                        .find('option:last-child')
                                        .prop('selected', true);
                                    setTimeout(function(){
                                        $('#tm_attribute_id_' + $this.attr('id'))
                                            .prop('disabled', true)
                                            .css('background', '#eee')
                                            .val($this.val());
                                    },100);

                            } else {
                                $this
                                    .prop('disabled', false)
                                    .css('background', '#fff');
                                setTimeout(function(){
                                    $('#tm_attribute_id_' + $this.attr('id'))
                                        .prop('disabled', false)
                                        .css('background', '#fff');
                                },100);
                            }
                        } else {
                            $this
                                .prop('disabled', false)
                                .css('background', '#fff');
                            setTimeout(function(){
                                $('#tm_attribute_id_' + $this.attr('id'))
                                    .prop('disabled', false)
                                    .css('background', '#fff');
                            },100);
                        }
                });
                setTimeout(function(){
                    $('.reset_variations').css( 'visibility', 'visible' ).show();
                   }, 100);
            });
        /*} else {
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
        }*/
        $('.reset_variations').on('click', function(){
            $('.variations select').each(function(){
                $(this)
                    .prop( "disabled", false )
                    .css('background','#fff');
                $('#tm_attribute_id_' + $(this).attr('id'))
                    .prop( "disabled", false )
                    .css('background','#fff');

                setTimeout(function(){
                    $('.variations select').change();
                   }, 100);
                
            });
        });


        if ($('table.variations').length) {
            update_variation_list();

        }

        $( ".variations_form" ).on( "woocommerce_variation_select_change", function () {

            update_variation_list();
        } );

        $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
            update_variation_list();
        } );

        function update_variation_list() {

            function format(state) {
                if (!state.id) return state.text;
                if (!state.element.dataset.imagep) return state.text;
                return state.text + "<div><img class='product_select_image' src='" + state.element.dataset.imagep + "'/></div>";

            }

            $('.variations select').each(function(){
                $('#' + this.id).select2({
                    minimumResultsForSearch: -1,
                    templateResult: format,
                    templateSelection: format,
                    escapeMarkup: function(m) { return m; }
                });


            });

            $('.variations select').on('change', function(){
                let img = $(this).find('option:selected').attr('data-imagep');
                if (img) {
                    setTimeout(function() {
                        $('.woocommerce-product-gallery__wrapper img').attr('src', img);
                    }, 300);
                }

            });

            $('.tm-extra-product-options-fields select').each(function(){
                $('#' + this.id).select2({
                    minimumResultsForSearch: -1,
                    templateResult: format,
                    templateSelection: format,
                    escapeMarkup: function(m) { return m; }
                });

            });

            //$('.tm-epo-field').off('tm_trigger_product_image');
            $(".variations .select2-container").css({'width': ''});
            $('<br>').insertBefore(".select2-results__options .product_select_image");
        }


    });
})(jQuery);


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

(function($){

    let currentShipingMethod = 1;
    let k = 1;

    $('#shipping_method .shipping_method').each(function(){
        if ($(this).prop('checked')) currentShipingMethod = k;
        k++;
    });

    if (currentShipingMethod === 1) {
        $('#ship-to-different-address-checkbox').on('change', function () {
            if ($(this).prop('checked')) {
                /**
                 * t213
                 */
                $(document.body).on('updated_checkout', function () {
                    k = 1;

                    $('ul#shipping_method li').each(function () {
                        if (k === 1){
                            $(this).hide().find('input').hide();
                        }else if (k === 2){
                            $(this).show().find('input').show().prop('checked', true);
                        }else if (k === currentShipingMethod){
                            $(this).show().find('input').show().prop('checked', true);
                        }else{
                            $(this).show().find('input').show();
                        }
                        k++;
                    });
                });

                $(document).on('change', '.shipping_method', function(){
                    k = 1;
                    $('#shipping_method .shipping_method').each(function(){
                        if ($(this).prop('checked')) currentShipingMethod = k;
                        k++;
                    });
                });

                /**
                 * end t213
                 */
            } else {
                k = 1;
                $('ul#shipping_method li').each(function () {
                    if (k === 1) {
                        $(this).find('input').prop('checked', 1);
                        $(document.body).on('updated_checkout', function () {
                            $('ul#shipping_method li').hide().find('input');//.hide();
                            $('ul#shipping_method li:nth-child(1)').show().find('input').prop('checked', true);
                        });
                    }
                    k++;
                });
            }
        });
    }
})(jQuery);

(function($){
    if ($('#shipping_method').length) {
        $( document.body ).on('updated_checkout', function(){
            $('#shipping_method').find('li').hide().each(function(){
                if ($(this).find('.shipping_method').prop('checked')) {
                    $(this).show();
                }
            });
        });
    }
})(jQuery);


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

(function($){
    $(document).on('ready', function(){

        $('td.column-priority_customer').each(function(){
            let customerNumber = $(this).text();
            $(this).html(
                '<input class="column_priority_customer_input" style="width: 70px" type="number" value="' + customerNumber + '">' +
            ' <a href="#" class="column_priority_customer_input_save">Save</a>');
        });

        $(document).on('click', '.column_priority_customer_input_save', function(){
            let val = $(this).prev().val();
            let userId = parseInt($(this).closest('tr').attr('id').replace('user-',''));

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: t173.ajaxUrl,
                data: {
                    action: 't173_update_user_meta',
                    priority_customer_number: val,
                    user_id: userId
                },
                success: function () {
                    alert('Success!');
                }
            });
        });

        $('.wrap > form').on('keyup keypress', function(e) {
            let keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

    });
})(jQuery);

(function($){
    $(document).on('ready', function(){
       if (window.t204 && t204.tmMeta) {
           let liID;
           $('.tm-epo-field-label').each(function(){
               let $this = $(this);
               $.each(t204.tmMeta, function(key, meta){
                    if ( $this.text() === meta || $this.text() === '*' + meta ) {
                        liID = $this.closest('li').attr('id');
                    }
               });
           });
           if (liID) {
               $('#' + liID).prependTo('#tm-extra-product-options-fields');
           }
       }
    });
})(jQuery);


(function($){

    $('.input-hide-price-for-user-save').on('click', function(){
       let hidePrices = $(this).prev().prop('checked');
       $.ajax({
           type: 'POST',
           dataType: 'json',
           url: t208.ajaxUrl,
           data: {
               action: 't208_hide_price_for_user_save',
               hidePrice: hidePrices ? 1 : 0
           },
           success: function (res) {
               if (res) alert('Success!')
           }
       });
    });

    $('.table-retail-price-category-for-user-save').on('click', function(){
       let update = {};
       $('.retail_price_addition').each(function(){
          update[$(this).attr('term_id')] = $(this).attr('proc') ? $(this).attr('proc') : 0;
       });
       $.ajax({
           type: 'POST',
           dataType: 'json',
           url: t208.ajaxUrl,
           data: {
               action: 't208_table_retail_price_category_for_user_save',
               update: update
           },
           success: function () {
               alert('Success!');
           }
       });
    });

    $('.table-retail-price-category-any-for-user-save').on('click', function(){
        let retailPrice = {};
        let newPrice = {};
        let termId = $(this).attr('term-id');

        $('.retail_price_addition').each(function(){
            if ($(this).attr('proc') && $(this).attr('proc') !== '0')
                retailPrice[$(this).attr('variation_id')] = $(this).attr('proc');
        });

        $('.new_retail_price').each(function(){
            if ($(this).attr('price') && $(this).attr('price') !== '0')
                newPrice[$(this).attr('variation_id')] = $(this).attr('price');
        });

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: t208.ajaxUrl,
            data: {
                action: 't208_table_retail_price_category_any_for_user_save',
                termId: termId,
                retailPrice: retailPrice,
                newPrice: newPrice,
            },
            success: function () {
                alert('Success!');
            }
        });
    });

    $('.update_category').on('change', function(){
        let status = $(this).prop('checked');
        let elem = $(this).closest('tr').find('.retail_price_addition');
        if (status) {
            let proc = elem.attr('proc');
            elem.html('<input type="number" style="width:70px" value="' + proc + '">%');
        }else{
            let proc = elem.find('input[type="number"]').val();
            elem.html(proc + '%');
            elem.attr('proc', proc);
        }
    });

    $('.retail_price_addition').on('change input', 'input[type="number"]', function(){
        $(this).parent().attr('proc', $(this).val());
    });

    $('.update_price_by_percents').on('change', function(){
        let status = $(this).prop('checked');
        let elem = $(this).closest('tr').find('.retail_price_addition');
        if (status) {
            let proc = elem.attr('proc');
            elem.html('<input type="number" style="width:70px" value="' + proc + '">%');
        }else{
            let proc = elem.find('input[type="number"]').val();
            elem.html(proc + '%');
            elem.attr('proc', proc);
        }
    });

    $('.update_price_manually').on('change', function(){
        let status = $(this).prop('checked');
        let elem = $(this).closest('tr').find('.new_retail_price');
        if (status) {
            let price = elem.attr('price');
            elem.html('<input type="number" style="width:70px" value="' + price + '">');
        }else{
            let price = elem.find('input[type="number"]').val();
            elem.html(price);
            elem.attr('price', price);
        }
    });

    $('.new_retail_price').on('change input', 'input[type="number"]', function(){
        $(this).parent().attr('price', $(this).val());
    });

})(jQuery);


(function($){

    $('.variations_form').on('submit', function(){

        let r = true;

        $('.cpf-type-textfield:not(.tc-hidden)').each(function(){
            let $this = $(this).find('.tm-extra-product-options-textfield');
            if (r) {
                let dataTmValidationJson = $this.attr('data-tm-validation');
                let dataTmValidation = JSON.parse(dataTmValidationJson);

                if (dataTmValidation && dataTmValidation.language && t211.languages[dataTmValidation.language]) {
                    let input = $this.find('input');
                    let value = input.val();
                    let code = t211.languages[dataTmValidation.language].replace(/\s+/g,'');
                    let space = t211.languageSpaceSymbol[dataTmValidation.language + '_space_symbol'];

                    if (code && code.split(',').length > 1) {
                        let codeArr = code.split(',');

                        if (space === 1)
                            value = value.replace(/ /g, '');

                        //fix special chars
                        var specialArr = [];
                        jQuery(codeArr).each(function( g, h ) {
                            specialArr.push("\\"+h.split(" ").join(' +'));
                        });
                        delete specialArr[0];
                        delete specialArr[1];
                        var newspecialArr = specialArr.join("");

                        let check = value.search('[^\\u' + codeArr[0] + '-\\u' + codeArr[1] + newspecialArr +']+');

                        if (check === -1){
                            input.removeClass('tm-error');
                            input.closest('li').find('#tmcp_textfield_3-error').remove();
                            r = true;
                        }else{
                            input.addClass('tm-error');
                            input.closest('li').find('#tmcp_textfield_3-error').remove();
                            input.closest('li').append('<label id="tmcp_textfield_3-error" class="tm-error" for="tmcp_textfield_2">Wrong language: ' + t211.languagesName[dataTmValidation.language].english_name + '</label>');
                            r = false;
                        }
                    }

                }
            }
        });

        return r;
    });

})(jQuery);

(function($){

    $('.t215_edit_best_seller').on('click', function(){
       let value = $(this).prev().val();
       let postId = $(this).prev().attr('post_id');
       $.ajax({
           type: 'POST',
           dataType: 'json',
           url: t215.ajaxUrl,
           data: {
               action: 't215_edit_best_seller',
               value: value ? value : 0,
               postId: postId
           },
           success: function () {
                alert('Success!');
           }
       });
    });

})(jQuery);

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

(function($){

    $('#t217_price_dropdown').on('change', function(){

        switch($(this).val()) {
            case 'hide':
                t216_update_price_display('hide');
                break;
            case 'regular':
                t216_update_price_display('regular');
                break;
            case 'retail':
                t216_update_price_display('retail');
                break;
            default:
                t216_update_price_display();
                break;
        }
    });

    function t216_update_price_display(param){
        param = param || '';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: t216.ajaxUrl,
            data: {
                action: 't216_update_price_display',
                param: param
            },
            success: function (res) {
                if (res) {
                    location.reload();
                }
            }
        });
    }

})(jQuery);


jQuery(document).ready(function($) {
    $(".selectit input[type='checkbox']").change(function (ev) {
        $(this).closest('li').find('input').prop('checked', this.checked);
    })
    });

    (function($){
        $(document).ready(function(){
    
            if ($.tc_add_filter) {
    
                $.tc_add_filter("tc_adjust_total", tc_adjust_total, 20, 2);
                function tc_adjust_total(total, $totals_holder) {
                    update_tm_select();
                    return total * get_proc();
                    //return total ;
                }
    
                $.tc_add_filter("tc_calculate_product_price", tc_calculate_product_price, 20, 1);
                function tc_calculate_product_price(price) {
                    // return price * get_proc();
                    let variationId = $('form.variations_form').find('input[name="variation_id"]').val();
                    let netPriceArray = $('#tm-epo-totals').attr("data-variations");
                    netPrice = JSON.parse(netPriceArray)[variationId];
                    console.log('this is net price : ' + netPrice);
                    console.log('this is  price : ' + price);
                    console.log('the proc is: ' + get_proc());
                    return price  ;
                }
            }
    
            $('.variations_form').on('reset_data show_variation', function() {
                update_tm_select();
            });
    
            function update_tm_select(){
                let proc = get_proc();
                $('.tm-extra-product-options').find('select option').each(function(){
                    let price = $(this).data('price');
                    let text = $(this).data('text');
                    //$(this).text(text + ' (+' + t233.symbol + price * proc + ')')
                    $(this).text(text + ' (+' + t233.symbol + price  + ')')
                });
            }
    
            function get_proc() {
                let variationId = $('form.variations_form').find('input[name="variation_id"]').val();
                return t233.variationsPrices[variationId].retailPrice / t233.variationsPrices[variationId].price;
            }
        });
    })(jQuery);

    (function($){
        $(document).ready(function(){
            $('.tm-extra-product-options-container').each(function() {
                $(this).find('input[type=text]').attr('value','');
            });
        });
    })(jQuery);

    jQuery(document).ready(function($) {
        //$('.woocommerce-table--order-details').hide();
            var tracking_items = $('.wc-item-meta-label:contains("Tracking number item:")').closest('li');
        
            if (tracking_items.length != 0){
            $( "<th class=\"woocommerce-table__tracking_number\">Tracking number</th>" ).insertAfter( $('.woocommerce-table--order-details').find('.product-name').closest('th') );
        
            var items = [];
        
                tracking_items.each(function () {
                var text = $(this).find("p").text();
                $("<td class=\"woocommerce-table__tracking_number\">" + text + "</td>" ).insertAfter( $(this).closest('tr').find('.product-name') );
                });
        
            tracking_items.hide();
            
            }
        });
        jQuery(document).ready(function($) {
            /* Create issue into CU1-T4
            if (document.referrer !=window.location.href) {
                Cookies.set("previousUrl", document.referrer, {path: "/"});
            }
            */
        
            $('body').on('click','.continue_shopping_button', function () {
                var prev = Cookies.get("previousUrl");
                window.location.href=prev;
            })
        });
                
