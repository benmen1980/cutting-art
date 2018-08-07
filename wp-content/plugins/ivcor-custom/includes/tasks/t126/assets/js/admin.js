(function($){
    $(document).on('ready', function(){

        if ( $('#addtag').length ) {
            /*let htmlField = '';
            htmlField += '<div class="form-field term-external-name-wrap">';
                htmlField += '<label for="tag-external-name">External Name</label>';
                htmlField += '<input name="external-name" id="tag-external-name" type="text" value="" size="40">';
                htmlField += '<p>External Name to override Name field of Product Attribute</p>';
            htmlField += '</div>';
            $(htmlField).insertBefore('#addtag .submit');*/
        }

        if ( $('#edittag').length ) {

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: t126.ajaxUrl,
                data: {
                    action: 't126_get_external_name_by_term_id',
                    tag_ID: $('#edittag').find('input[name="tag_ID"]').val()
                },
                success: function (externalName) {
                    let htmlField = '';
                    htmlField += '<tr class="form-field term-external-name-wrap">';
                    htmlField += '<th scope="row"><label for="external-name">External Name</label></th>';
                    htmlField += '<td><input name="external-name" id="external-name" type="text" value="' + externalName + '" size="40">';
                    htmlField += '<p class="description">External Name to override Name field of Product Attribute</p></td>';
                    htmlField += '</tr>';
                    $('#edittag .form-table').append(htmlField);
                }
            });
        }

    });
})(jQuery);