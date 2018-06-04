(function($){

    let fieldsForm = {
        'cplp-firstname':   'First Name',
        'cplp-lastname':    'Last Name',
        'cplp-companyname': 'Company Name',
        'cplp-street':      'Street',
        'cplp-city':        'City',
        'cplp-zipcode':     'Zip Code',
        'cplp-email':       'Email',
        'cplp-subject':     'Subject',
        'cplp-message':     'Message'
    };

    $(document).on('ready', function(){

        $('#loginform').after("<p id='cplp-open-popup'><a>Contact Us/Sign Up</a></p>");

        $(document).mouseup(function(e) {
            let container = $('#cplp-popup');
            if (!container.is(e.target) && container.has(e.target).length === 0){
                if (container.length) {
                    container.remove();
                    $('#login').css('opacity', '1');
                }
            }
        });

        $('#cplp-open-popup').on('click', 'a', function(e){

            e.stopPropagation();

            let popup = '';
            let topPopup = window.innerHeight/2 - 422;

            popup += '<div id="cplp-popup" style="top: ' + (topPopup > 0 ? topPopup : 0) + 'px;">';
                popup += '<h2>Contact Us/Sign Up</h2><br>';
                $.each(fieldsForm, function(key, val){
                    popup += '<p>';
                        popup += '<label for="' + key + '">' + val + '<br>';
                            if (key === 'cplp-message') {
                                popup += '<textarea name="' + key + '" id="' + key + '" class="textarea"></textarea>';
                            } else {
                                popup += '<input type="text" name="' + key + '" id="' + key + '" class="input">';
                            }
                        popup += '</label>';
                    popup += '</p>';
                });
            popup += '<br><input type="button" name="cplp-submit" id="cplp-submit" class="button button-primary button-large" value="Send">';
            popup += '<input type="button" name="cplp-close" id="cplp-close" class="button button-default button-large" value="Close">';
            popup += '</div>';

            $('#cplp-popup').remove();
            $('#login').css('opacity','0');
            $('body').append(popup);

        });

        $(document).on('click', '#cplp-submit', function(){

            let options = {};

            if (!checkRequired('#cplp-firstname')) return false;
            if (!checkRequired('#cplp-lastname')) return false;
            if (!checkRequired('#cplp-companyname')) return false;
            if (!checkRequired('#cplp-street')) return false;
            if (!checkRequired('#cplp-city')) return false;
            if (!checkRequired('#cplp-zipcode')) return false;
            if (!checkRequired('#cplp-email')) return false;
            if (!checkRequired('#cplp-subject')) return false;
            if (!checkRequired('#cplp-message')) return false;

            $.each(fieldsForm, function(key){
                options[key] = $('#' + key).val()
            });

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: cplp.ajaxUrl,
                data: {
                    action: 'cplp_send_form_login_page',
                    options: options
                },
                success: function (res) {
                    if (res) {
                        $('#cplp-popup').remove();
                        $('#login').css('opacity', '1');
                        alert('Success!');
                    } else {
                        $('#cplp-popup').remove();
                        $('#login').css('opacity', '1');
                        alert('Error!');
                    }
                }
            });
        });

        $(document).on('click', '#cplp-close', function(){
            $('#cplp-popup').remove();
            $('#login').css('opacity', '1');
        });

    });

    function checkRequired( element ) {
        if (element === '#cplp-email'){
            if (!validateEmail($(element).val())){
                $(element).addClass('cplp-required');
                return false;
            }else{
                $(element).removeClass('cplp-required');
                return true;
            }
        }else{
            if (!$(element).val()){
                $(element).addClass('cplp-required');
                return false;
            }else{
                $(element).removeClass('cplp-required');
                return true;
            }
        }

    }

    function validateEmail(email) {
        let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

})(jQuery);