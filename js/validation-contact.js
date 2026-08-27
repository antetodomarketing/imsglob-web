$(document).ready(function(){
        $('#send_message').click(function(e){

            //Stop form submission & check the validation
            e.preventDefault();

            var $btn = $(this);
            var originalVal = $btn.attr('value') || 'Enviar';

            // Variable declaration
            var error = false;
            var name = $('#name').val();
            var email = $('#email').val();
            var phone = $('#phone').val();
            var message = $('#message').val();

            $('#name,#email,#phone,#message').click(function(){
                $(this).removeClass("error_input");
            });

            // Form field validation
            if(name.length == 0){
                error = true;
                $('#name').addClass("error_input");
            }else{
                $('#name').removeClass("error_input");
            }
            if(email.length == 0 || email.indexOf('@') == -1){
                error = true;
                $('#email').addClass("error_input");
            }else{
                $('#email').removeClass("error_input");
            }
            if(phone.length == 0){
                error = true;
                $('#phone').addClass("error_input");
            }else{
                $('#phone').removeClass("error_input");
            }
            if(message.length == 0){
                error = true;
                $('#message').addClass("error_input");
            }else{
                $('#message').removeClass("error_input");
            }

            // If there is no validation error, process the mail function
            if(error == false){
                // Disable submit button while sending
                $btn.attr({'disabled' : 'true', 'value' : 'Sending...' });
                $('#error_message').hide();

                $.post("contact.php", $("#contact_form").serialize(), function(result){
                    if($.trim(result) == 'sent'){
                        // Hide the form and show the success message
                        $('#contact_form').fadeOut(300);
                        $('#success_message').fadeIn(500);
                    }else{
                        // Show error and re-enable the button
                        $('#error_message').fadeIn(500);
                        $btn.removeAttr('disabled').attr('value', originalVal);
                    }
                }).fail(function(){
                    // Network/server error: never leave the button stuck on "Sending..."
                    $('#error_message').fadeIn(500);
                    $btn.removeAttr('disabled').attr('value', originalVal);
                });
            }
        });
    });
