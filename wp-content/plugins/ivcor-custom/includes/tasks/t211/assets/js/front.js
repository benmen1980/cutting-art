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

                    if (code && code.split('-').length === 2) {
                        let codeArr = code.split('-');

                        if (space === 1)
                            value = value.replace(/ /g, '');

                        let check = value.search('[^\\u' + codeArr[0] + '-\\u' + codeArr[1] + ']');
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