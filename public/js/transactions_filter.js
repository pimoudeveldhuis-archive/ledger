$('#ajax_filter select').change(function() {
    filters = {};

    $("#ajax_filter select").each(function() {
        filters[$(this).attr('id').substring(6)] = $(this).find(":selected").val();
    });

    if(ajax_call !== null)
        ajax_call.abort();
        
    ajax_call = $.ajax({
        method: 'GET',
        url: '/ajax/transactions_filter',
        data: { filters: filters },
        dataType: 'json',
        beforeSend: function() {
            block_loader = true;
            $(".loader").show();
            $("#transactions").html("");
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
});
