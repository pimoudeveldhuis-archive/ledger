$(window).scroll(function() {  
    if(block_loader === false && $(window).scrollTop() + $(window).height() >= $(document).height()) {
        if(ajax_call !== null)
            ajax_call.abort();
        
        ajax_call = $.ajax({
            method: 'GET',
            url: '/ajax/transactions_scroll',
            data: { loaded_till: loaded_till, filters: filters },
            dataType: 'json',
            beforeSend: function() {
                block_loader = true;
                $(".loader").show();
            },
            success: function(callback) {
                if(callback.amount > 0)
                    block_loader = false;

                loaded_till = callback.loaded_till;

                for(var i in callback.html) {
                    $("#transactions").append(callback.html[i]);
                }

                $(".loader").hide();
                if($(window).height() === $(document).height()) {
                    $(window).scroll();
                }
            }
        });
    }
});

$(window).scroll();