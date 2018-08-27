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
            });
        });
    });
})(jQuery);