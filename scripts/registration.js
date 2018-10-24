 
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

 
   
    var handleLogin = function(){
        let username = $("#loginUsername").val();
        let passwword = $("#loginPassword").val();
 
        if(username.trim().length == 0  && passwword.trim().length == 0  ){
            $('.alert').removeClass('hide_form_div');
            $('.alert').html("Username and Password  are mandatory ");

            return ; 
        }        

        let url = getBaseURL()+"user/login";   

        var formdata = {};
        formdata['username'] =  username;
        formdata['passwword'] =  passwword;
        
        // $('.alert').removeClass('hide_form_div');
        $('.alert').removeClass('hide_form_div').html("Processing ... ");
        $.ajax({
            type: "POST",
            url:  url,
            data:formdata,
            success: function(data, textStatus, jqXHR){            
                var jsonData = JSON.parse(data);   
                $('.alert').removeClass('hide_form_div').html("Logged in Successfully ");
                // let redirection_url = getBaseURL()+"dashboard/home";   
                // window.location.replace(redirection_url);
                console.log(jsonData);
                alert("pass");
                              
            },
            error:function(data , textStatus, jqXHR)
            { 
              
                  $('.alert').removeClass('hide_form_div').html("Could not log you  in ");
            }
        });


       
        // let  response =  submitForm(url,"POST",formdata);

        // $('.alert').html(response);
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

        let url = getBaseURL()+"registration/save";   

        let formdata = {};
                //update session for this field : just in calse ::

        formdata['emailAddress'] = emailAddress;
        formdata['username'] = username;
        formdata['password'] = password;

        
        let  response =  submitForm(url,"POST",formdata);

        console.log(response);
 
        
         
    }   

     
 
    var submitForm = function(url,method,formdata){
        var response = {};
        $.ajax({
            type: method,
            url:  url,
            data:formdata,
            success: function(data, textStatus, jqXHR){               
                var response =  JSON.parse(JSON.stringify(data));
                $('.alert').removeClass('hide_form_div');
                $('.alert').html(response)
                return response;
                              
            },
            error:function(data , textStatus, jqXHR)
            { 
                $('.alert').html(data) 
                 
            }
        });
    }
    var reset_alert = function(){
        $(".alert").addClass("hide_form_div");
    }
    var handleRegistrationUI = function(VIEW){
        reset_alert();
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