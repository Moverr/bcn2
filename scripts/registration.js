 
 var getBaseURL = function()
 {
    var pageURL = document.location.href;
    var urlArray = pageURL.split("/");  
    var BaseURL = urlArray[0]+"//"+urlArray[2]+"/";
    //Dev environments have the installation sitting in a separate folder
    if(urlArray[2] == 'localhost'  || urlArray[2] == 'localhost:4443')
    {
        BaseURL = BaseURL+'bcn/';
    }
 
 
    return BaseURL;
 }

 
   
   
   var  handleRegistration = function (){
         let emailAddress = $("#emailAddress").val().trim();
         let username = $("#userName").val().trim();
         let password = $("#password").val().trim();
         let repeatepassword = $("#repeatpassword").val().trim();
        
         if(emailAddress.length ==0 || username.length ==0  || password.length ==0  || password.length ==0 ){
            $('.alert').removeClass("hide_form_div");
            $('.alert').html("Fill Blanks");
         }
         $('.alert').addClass("hide_form_div");

        let url = getBaseURL()+"page/set_accounttype_session";   

        let formdata = {};
                //update session for this field : just in calse ::

        formdata['type'] = fieldChangeId;
        formdata['val'] = fieldValue;

        let  response =  submitForm(url,"POST",formdata);

        console.log(formdata);
 
        
         
    }   
 
    var submitForm = function(url,method,formdata){
        var response = {};
        $.ajax({
            type: method,
            url:  url,
            data:formdata,
            success: function(data, textStatus, jqXHR){
                response['type'] = "SUCCESS";
                response['data'] = data;
                response['textStatus'] = textStatus;
                response['jqXHR'] = jqXHR;
                return response;
                              
            },
            error:function(data , textStatus, jqXHR)
            { 
                response['type'] = "FAILURE";
                response['data'] = data;
                response['textStatus'] = textStatus;
                response['jqXHR'] = jqXHR;
                return response;
            }
        });
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