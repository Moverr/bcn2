var CommentBox = React.createClass({
    submitHandler:() => {
        alert("Testing");
    },
    render: function() {
      return (
                <div className="container-fluid"  >
                    <div className="row">
                        <div className="col-md-12">
                        <h1>CREATE AN ACCOUNT  </h1>
                            <form role="form">
                                <div className="form-group">
                                    
                                    <label htmlFor="emailAddress">
                                        Email address : 
                                    </label>
                                    <input type="email" className="form-control" id="emailAddress" />
                                </div>

                                 <div className="form-group">
                                    
                                    <label htmlFor="userName">
                                        Username : 
                                    </label>
                                    <input type="email" className="form-control" id="userName" />
                                </div>

                                <div className="form-group">
                                    
                                    <label htmlFor="password">
                                        Password : 
                                    </label>
                                    <input type="password" className="form-control" id="password" />
                                </div>

                                  <div className="form-group">
                                    
                                    <label htmlFor="repeatpassword">
                                        Repeat Password : 
                                    </label>
                                    <input type="password" className="form-control" id="repeatpassword" />
                                </div>


                                <button type="submit" onClick={this.submitHandler} className="btn btn-primary">
                                    Submit 
                                </button> &nbsp;&nbsp;&nbsp;
                                <a href="#"> Already have an Account</a>
                            </form>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                        </div>
                    </div>
        </div>

      );
    }
  });



 
  ReactDOM.render(
    <CommentBox />,
    document.getElementById('content')
  );