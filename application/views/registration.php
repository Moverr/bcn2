<?php
    include 'includes/header.php';
    ?>
 
 <script  src="scripts/registration.js"></script>
<div class="container-fluid">
  <div class="row" id="content" >
  <div class="container-fluid registration_form"  >
                    <div class="row">
                        <div class="col-md-12">
                        <h1>CREATE AN ACCOUNT  </h1>
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
                                    <input type="email" class="form-control" id="userName" />
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


                                <button type="button" onClick="javascript:handleRegistration()" class="btn btn-primary">
                                    Submit 
                                </button> &nbsp;&nbsp;&nbsp;
                                <a href="#"> Already have an Account</a>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                    </div>
    </div>

	 <div class="container-fluid login_form "  >
                    <div class="row">
                        <div class="col-md-12">
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

                               
                                

                                <button type="button" onClick="javascript:handleRegistration()" class="btn btn-primary">
                                    Submit 
                                </button> &nbsp;&nbsp;&nbsp;
                                <a href="#"> Already have an Account</a>
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