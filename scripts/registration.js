 
    function handleRegistration(){
       
    }
 
    var handleRegistrationUI = function(VIEW){
        switch (VIEW) {
            case 'REGISTER':
                    $('.registration_panel').removeClass("hide_form_div");
                    $('.registration_panel').addClass("show_form_div");

                    $('.login_panel').removeClass("show_form_div");
                    $('.login_panel').addClass("hide_form_div");

                break;
        case 'LOGIN':
                    $('.login_panel').removeClass("hide_form_div");
                    $('.login_panel').addClass("show_form_div");

                    $('.registration_panel').removeClass("show_form_div");
                    $('.registration_panel').addClass("hide_form_div");

                   
                    
        break;
            default:
                break;
        }
    }