jQuery( document ).ready(function($) {

    /*Featured API Start*/
    $(document).on( 'change', '.bic-features input[type=checkbox]', function() {
        $('#features-fields').hide();

        var values = [];
        
        $(".bic-features input[type=checkbox]:checked").each(function(){
            values.push($(this).val());
        });

        if( $.inArray( "other", values ) > -1 ){
            $('#message-field-group').show()
        } else{
            $('#message-field-group').hide()
        }


        if($(".bic-features input[type=checkbox]:checked").length > 0){
            $('#features-fields').show();
        }
    });

    $("#gpbic_feature_alert").removeClass('notice notice-error');
    $.ajax({
        type: 'POST',
        url: gpfil.ajaxurl,
        data: {
            'current_page' : gpfil.current_page,
            'api_url' : gpfil.api_url,
            'action' : 'fil_get_all_features'
        },
        success: function ( response ) {

            if(response.success){
                if( response.data.features.length > 0 ){
                    $("#feature-title").html(response.data.name);
                    $("#feature-description").html(response.data.description);
                    $("#get_notified").html(response.data.submit_text);

                    $.each(response.data.features, function(k, v) {
                        var feature_ul = $(".bic-features");
                        var feature_li = document.createElement( "li" );
                        var feature_label = document.createElement( "label" );
                            feature_label.setAttribute("class", "bic-alias");
                            feature_label.setAttribute("for", "f"+k);
                        var feature = document.createElement('input');
                            feature.type = 'checkbox';
                            feature.id = "f"+k;
                            feature.name = 'features[]';
                            feature.value = v.tag;
                        var faeture_desc = document.createElement('p');
                            faeture_desc.innerHTML = v.name;

                        feature_label.appendChild(feature);
                        feature_label.appendChild(faeture_desc);
                        feature_li.append(feature_label);

                        feature_ul.append( feature_li );
                    });
                } else{
                    $('.features-container').hide();
                }

            }else{
                $("#gpbic_feature_alert")
                .addClass('notice notice-error')
                .html('<p>'+response.data+'</p>');
            }
                $("#features_loading").hide();
        },
        error: function (data) {
            console.log('An error occurred.');
            console.log(data);
        },
    });

    /*Features API Finish*/

    $('#bic_featuers').on( "submit", function(e){
        e.preventDefault(); // avoid to execute the actual submit of the form.
        
        var form = $(this);
        var alert = $('#gpbic_feature_alert');
            alert.html('');
            alert.removeClass( 'notice notice-success notice-error' );
        var btn = form.find('button[type="submit"]');

        
        btn.attr('disabled', true);
        form.find('.submit .spinner').css("float", 'left');
        form.find('.submit .spinner').css("visibility", 'visible');

        $.ajax({
            type: form.attr('method'),
            url: gpfil.ajaxurl,
            data: $(this).serialize(),
            success: function ( response ) {
                
                
                if( response.success ){
                    $('.features-container').html( '<h3>'+response.data+'</h3>' );
                } else{
                    alert.addClass( 'notice notice-error' );
                    alert.html( '<p>'+response.data+'</p>' );
                    print_errors(response.data);
                }

                form.find('.submit .spinner').css("float", 'right');
                form.find('.submit .spinner').css("visibility", 'hidden');
                btn.attr('disabled', false);
                
            },
            error: function (data) {
                console.log('An error occurred.');
                console.log(data);
            },
        });

    });
});