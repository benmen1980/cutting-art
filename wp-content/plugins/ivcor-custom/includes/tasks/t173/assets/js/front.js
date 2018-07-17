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