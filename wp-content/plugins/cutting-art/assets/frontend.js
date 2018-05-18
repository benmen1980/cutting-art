jQuery(document).ready(function ($) {

    $('#conversion-table').dialog({
        autoOpen: false,
        modal: true,
        draggable: false,

        width: 600
    });

    $('#show-conversion-table').click(function(e) {
        e.preventDefault();
        $('#conversion-table').dialog('open');
    });

});

 