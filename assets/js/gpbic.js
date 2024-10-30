jQuery( document ).ready(function($) {

   $('#gpbic_form').on( "submit", function(e){
        e.preventDefault(); // avoid to execute the actual submit of the form.
        
        var form = $(this);
        var alert = $('#gpbic_form_response');
        var table = $('#gpbic_form_result');
        var btn = form.find('button[type="submit"]');
        
        
        alert.html('');
        alert.removeClass( 'notice notice-success notice-error' );
        alert.hide();
        table.hide();
        btn.attr('disabled', true);
        form.find('.submit .spinner').css("float", 'left');
        form.find('.submit .spinner').css("visibility", 'visible');

        $.ajax({
            type: form.attr('method'),
            url: gpbic.ajaxurl,
            data: $(this).serialize(),
            success: function ( response ) {
                
                alert.show();
                table.show();
                if( response.success ){
                    alert.addClass( 'notice notice-success' );
                    if( $('#form_action').val() == 'gpbic_run_api_manual' ){
                        print_results(response.data.data);
                        alert.html( '<p>'+response.data.data.summary.description+'</p>' );
                        btn.attr('disabled', true);

                        show_countdown_timer(response.data.next_run);

                    } else{
                        alert.html( '<p>'+response.data+'</p>' );
                        btn.attr('disabled', false);
                    }
                    
                } else{
                    alert.addClass( 'notice notice-error' );
                    alert.html( '<p>'+response.data+'</p>' );
                    if( $('#form_action').val() == 'gpbic_run_api_manual' ){
                        print_errors(response.data);
                    }
                    btn.attr('disabled', false);
                }

                // btn.attr('disabled', false);
                form.find('.submit .spinner').css("float", 'right');
                form.find('.submit .spinner').css("visibility", 'hidden');
            },
            error: function (data) {
                console.log('An error occurred.');
                console.log(data);
            },
        });

    });
    show_countdown_timer();
    function show_countdown_timer(next_run_param = ''){
        
        var next_run = $( '#next_run' ).val();

        if( next_run === 0 && next_run_param == "" ){
            var nextRunDate = new Date().toLocaleString("en-US", { timeZone: 'UTC' });
        } else{
            if( next_run_param == "" ){
                var nextRunDate = new Date( next_run ).toLocaleString("en-US");
            } else{
                var nextRunDate = new Date( next_run_param ).toLocaleString("en-US");
            }
        }

        /* Countdown Timer */
        var nextRunDateUTC = new Date(nextRunDate).getTime();

        // Update the count down every 1 second
        var x = setInterval( function() {

            // Get today's date and time
            var currentTimeUTCString = new Date().toLocaleString("en-US", { timeZone: 'UTC' });
            var now = new Date(currentTimeUTCString).getTime();
            
            // Find the distance between now and the count down date
            var distance = nextRunDateUTC - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="demo"
            $("#api_countdown").html("in " + days + " days, " + hours + " hours, " + minutes + " minutes, " + seconds + " seconds");

            // If the count down is finished, write some text
            if (distance < 0) {
                clearInterval(x);
                $("#api_countdown").html("now");
            }
        }, 1000);
    }

    $('#bic-filter-logs').on( "change", function(e){
        
        var alert = $('#gpbic_form_response');
        alert.html('');
        alert.removeClass( 'notice notice-success notice-error' );

        $.ajax({
            type: 'POST',
            url: gpbic.ajaxurl,
            data: {
                'option' : $(this).val(),
                'action' : 'bic_filter_log'
            },
            success: function ( response ) {
                if( response.success ){
                    alert.addClass( 'notice notice-success' );
                    print_results(response.data);
                    alert.html( '<p>'+response.data.summary.description+'</p>' );
                } else{
                    alert.addClass( 'notice notice-error' );
                    alert.html( '<p>'+response.data+'</p>' );
                    print_errors(response.data);
                }
            },
            error: function (data) {
                console.log('An error occurred.');
                console.log(data);
            },
        });

    })

    $('#bic_status_bar').on( "change", function(e) {
        
        var status_bar = $(this).is(':checked');

        var alert = $('#gpbic_form_response');
        alert.html('');
        alert.removeClass( 'notice notice-success notice-error' );

        $.ajax({
            type: 'POST',
            url: gpbic.ajaxurl,
            data: {
                'option' : status_bar,
                'action' : 'bic_admin_status_bar'
            },
            success: function ( response ) {

                alert.addClass( 'notice notice-success' );
                if( status_bar ){
                    alert.html( '<p>Admin bar status enabled.</p>' );
                } else{
                    alert.html( '<p>Admin bar status disabled.</p>' );
                }
                
                setTimeout(function () {
                    location.reload(true);
                  }, 1000);
            },
            error: function (data) {
                console.log('An error occurred.');
                console.log(data);
            },
        });
    })
    
    var lastLog = $('#bic-filter-logs option:last').val();
    $('#bic-filter-logs').val(lastLog).change();

    function print_errors(error){
        var table = document.getElementById('gpbic_form_result');
        var tbody = table.getElementsByTagName('tbody')[0];
        tbody.innerHTML = '';
        var tr  = document.createElement('tr'); 
        var td1  = document.createElement('td');
        td1.colSpan = 3;
        td1.innerHTML = error;
        tbody.appendChild(tr);
        tr.appendChild(td1);
    }
    function print_results(results) {

        // create results list
        var table = document.getElementById('gpbic_form_result');
        var tbody = table.getElementsByTagName('tbody')[0];
        tbody.innerHTML = '';
        $.each(results.servers, function( index, result ) {
            
            var tr  = document.createElement('tr'); 

            tbody.appendChild(tr);

            var td1  = document.createElement('td'); 
            var td2  = document.createElement('td'); 
            var td3  = document.createElement('td'); 
            
            td1.innerHTML = result.location;
            td2.innerHTML = result.resultvalue;
            td3.innerHTML = result.resultstatus == 'ok' ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-dismiss"></span>';
            
            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);
        });
    }
});