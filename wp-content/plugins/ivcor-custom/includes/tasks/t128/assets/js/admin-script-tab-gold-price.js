(function($){
    $('#gold_based_price').on('change', update_proc);
    $('#current_gold_price').on('change', update_proc);
    function update_proc(){
        let basedPrice = $('#gold_based_price').val();
        let goldPrice  = $('#current_gold_price').val();
        let diff = parseInt(basedPrice) - parseInt(goldPrice);
        $('#extra_proc').val( Math.abs(diff / basedPrice * 100));
    }
})(jQuery);