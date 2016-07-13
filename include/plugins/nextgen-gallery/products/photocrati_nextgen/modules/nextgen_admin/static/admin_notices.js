jQuery(function($){
    $('.ngg_admin_notice .dismiss').click(function(e){
        e.preventDefault();

        var $notice = $(this).parents('.ngg_admin_notice');
        var $notice_name = $notice.attr('data-notification-name');
        if ($notice_name.length > 0) {
            var url = ngg_dismiss_url+'&name='+$notice_name;
            $.post(url, function(response){
                if (typeof(response) != 'object') response = JSON.parse(response);
                if (response.success) {
                    $notice.fadeOut();
                }
                else alert(response.msg);
            });
        }
    });
});