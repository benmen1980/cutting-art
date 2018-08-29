(function($){
    $(document).on('ready', function(){
       if (t204.tmMeta) {
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