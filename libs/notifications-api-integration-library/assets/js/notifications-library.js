jQuery( document ).ready(function($) {

   /*Dismiss notification*/
    $("#nil-notification-container").on('click', '.notice-dismiss', function(){
        $.ajax({
            type: 'POST',
            url: wpApiSettings.root + 'blocked-in-china/v1/administration/dismiss-notification',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', gpnil.nonce);
            },
            error: function (data) {
                console.log('An error occurred.');
                console.log(data);
            },
        });
    })
});