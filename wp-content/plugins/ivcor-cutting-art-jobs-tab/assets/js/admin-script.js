(function($){

    if ($('#p18a_tabs_menu').length && caJobs.tab) {
        let li = '';

        li += '<li style="margin-right: 4px">';
        li += '<a href="' + caJobs.tab + '">Jobs</a>';
        li += '</li>';

        $('#p18a_tabs_menu').find('ul').append(li);
    }

    if ($('input[name="job_status_default"]').length && caJobs.ajaxUrl) {
        $('input[name="job_status_default"]').on('change', function () {
            let term_id = $(this).attr('term_id');
            console.log(term_id);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: caJobs.ajaxUrl,
                data: {
                    action: 'update_job_status_default',
                    term_id: term_id
                },
                success: function (res) {
                    console.log(res);
                    alert('Success!');
                }
            });
        });
    }

    if (caJobs.isJobs) {
        $('.job_status-checklist input[type="checkbox"]').on('click', function(){
           if ($(this).prop('checked')) {
               $('.job_status-checklist input[type="checkbox"]').prop('checked', false);
               $(this).prop('checked', true)
           }
        });
    }

})(jQuery);