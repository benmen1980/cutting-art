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
        /*$('.job_status-checklist input[type="checkbox"]').on('click', function(){
           if ($(this).prop('checked')) {
               $('.job_status-checklist input[type="checkbox"]').prop('checked', false);
               $(this).prop('checked', true)
           }
        });*/

        let orders = {};
        $('.control_status input').each(function(){
            orders[$(this).attr('term-id')] = $(this).hasClass('active');
        });
        let html = '';
        if (caJobs.default && caJobs.default.term_id)
            html += '<li id="job_status-' + caJobs.default.term_id + '" class="popular-category"><label class="selectit"><input value="' + caJobs.default.term_id + '" type="checkbox" name="tax_input[job_status][]" id="in-job_status-' + caJobs.default.term_id + '"> ' + caJobs.default.name + '</label></li>';
        $.each(orders, function(termId){
            let termTitle = $('li#job_status-' + termId + ' label').text();
           html += '<li id="job_status-' + termId + '" class="popular-category"><label class="selectit"><input value="' + termId + '" type="checkbox" name="tax_input[job_status][]" id="in-job_status-' + termId + '"> ' + termTitle + '</label></li>';
        });
        $('.cat-checklist').html(html);

        $('.control_status, .job_status-checklist').find('input[type="checkbox"]').on('change', function(){
           if (!$(this).prop('checked')) {
               $(this).prop('checked', true);
           } else {
               if (!confirm('You sure?')){
                   $(this).prop('checked', false);
                   return;
               }

               let termId = $(this).attr('term-id') || $(this).val();
               let jobId = $(this).attr('job-id') || $(this).closest('tr').attr('id').replace('edit-','');
               let $this = $(this);

               $.ajax({
                   type: 'POST',
                   dataType: 'json',
                   url: caJobs.ajaxUrl,
                   data: {
                       action: 'update_job_status_from_control',
                       term_id: termId,
                       job_id: jobId
                   },
                   success: function (res) {
                        if (res) {
                            $('tr#edit-' + jobId + ' ul.job_status-checklist').find('input[type="checkbox"]').removeClass('active');
                            $('tr#edit-' + jobId + ' li#job_status-' + termId).find('input[type="checkbox"]').addClass('active').prop('checked', true);

                            $('tr#post-' + jobId + ' .control_status').find('input[type="checkbox"]').removeClass('active');
                            $('tr#post-' + jobId + ' .control_status').find('input[term-id="' + termId + '"]').addClass('active').prop('checked', true);;

                            let cats = '';
                            $.map($('tr#post-' + jobId + ' .control_status').find('input[type="checkbox"]'), function(val){
                                if ($(val).prop('checked'))
                                    cats += $(val).attr('term-id') + ',';
                            });

                            $('#job_status_' + jobId).text(cats);

                            $('tr#post-' + jobId).find('.column-status').html(res);
                        }
                   }
               });
           }
        });
    }

})(jQuery);