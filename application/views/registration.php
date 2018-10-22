<?php
    include 'includes/header.php';
    ?>
 
 <script  src="scripts/registration.js"></script> 
 <link rel="stylesheet" href="scripts/registration.css" crossorigin="anonymous" />
 <script>
 
$(document).ready(function() {

    $(".loginFormButton").click(function(){
        alert("Test");
    });

    $(".handleRegistrationUI").click(function() {
		//Do stuff when clicked
		handleRegistrationUI('REGISTER')
	});

	 $(".handleLoginUI").click(function() {
		//Do stuff when clicked
		handleRegistrationUI('LOGIN')
    });
    
    $("#submitCreateAccount").click(function() {
		//Do stuff when clicked
		handleRegistration();
    });
    

	
	
});

	 </script>



<div class="container-fluid">
  <div class="row" id="content" >
  <div class="container-fluid registration_form hide_form_div registration_panel "  >
                    <div class="row">
                    
                        <div class="col-md-12">
                         <h1>CREATE AN ACCOUNT  </h1>
                         <div class="alert alert-warning   hide_form_div  " role="alert"> Something went wrong </div>
                      
                            <form role="form">
                                <div class="form-group">
                                    
                                    <label htmlFor="emailAddress">
                                        Email address : 
                                    </label>
                                    <input type="email" class="form-control" id="emailAddress" />
                                </div>

                                 <div class="form-group">
                                    
                                    <label htmlFor="userName">
                                        Username : 
                                    </label>
                                    <input type="text" class="form-control" id="userName" />
                                </div>

                                <div class="form-group">
                                    
                                    <label htmlFor="password">
                                        Password : 
                                    </label>
                                    <input type="password" class="form-control" id="password" />
                                </div>

                                  <div class="form-group">
                                    
                                    <label htmlFor="repeatpassword">
                                        Repeat Password : 
                                    </label>
                                    <input type="password" class="form-control" id="repeatpassword" />
                                </div>


                                <button type="button"  id="submitCreateAccount" class="btn btn-primary">
                                    Submit 
                                </button> &nbsp;&nbsp;&nbsp;
                                <a href="#" class="handleLoginUI"> Already have an Account</a>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                    </div>
    </div>

	 <div class="container-fluid show_form_div login_panel "  >
                    <div class="row">
                        <div class="col-md-8">
                        <h1> LOGIN ACCOUNT  </h1>
                            <form role="form">
                                <div class="form-group">
                                    
                                    <label htmlFor="loginUsername">
                                       Username: 
                                    </label>
                                    <input type="text" class="form-control" id="loginUsername" />
                                </div>

                                 <div class="form-group">
                                    
                                    <label htmlFor="loginPassword">
                                       Password : 
                                    </label>
                                    <input type="loginPassword" class="form-control" id="loginPassword" />
                                </div>

                               
                                

                                <button type="button" id="loginFormButton"   class="loginFormButton btn btn-primary">
                                    Submit 
                                </button> &nbsp;&nbsp;&nbsp;
                                <a href="#" id="handleRegistrationUI" class="handleRegistrationUI"> Register an account !! </a>
                            </form>
						</div>
						
						<div class="col-md-4">
                        <h4> FORGOT PASSWORD  </h4>
                            <form role="form">
                                <div class="form-group">
                                    
                                    <label htmlFor="userEmailAddress">
                                       Emailaddress: 
                                    </label>
                                    <input type="text" class="form-control" id="userEmailAddress" />
                                </div>

                                 
                                <button type="button" onClick="javascript:handleRegistration()" id="resetFormButton" class="  btn btn-primary">
                                    Reset 
                                </button>
                             
                            </form>
						</div>
						
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                    </div>
    </div>


  </div>
</div>



  

 <!-- npx babel --watch src --out-dir . --prod -->


<?php
    // include 'includes/footer.php';
    ?>
 

</body>
</html>