<?php
    include 'includes/header.php';
    ?>
 
 
<div class="container-fluid">
  <div class="row" id="content" >
  <div class="container-fluid"  >
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


                                <button type="submit" onClick={this.submitHandler} class="btn btn-primary">
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
 
    <script type="text/babel" src="scripts/registration.js"></script>
    <script type="text/babel">
      // To get started with this tutorial running your own code, simply remove
      // the script tag loading scripts/example.js and start writing code here.
    </script>
<!-- <script src="./scripts/registration.js"></script> -->
</body>
</html>