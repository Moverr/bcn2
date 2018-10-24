
<?php 
ob_start();

#*********************************************************************************
# All users have to first hit this class before proceeding to whatever section 
# they are going to.
# 
# It contains the login and other access control functions.
#*********************************************************************************

class Admin extends CI_Controller {
	
	# Constructor
	function __construct() 
	{	
		//**********  Back button will not work, after logout  **********//
			header("cache-Control: no-store, no-cache, must-revalidate");
			header("cache-Control: post-check=0, pre-check=0", false);
			// HTTP/1.0
			header("Pragma: no-cache");
			// Date in the past
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			// always modified
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
		//**********  Back button will not work, after logout  **********//
		
		parent::__construct();	
		// $this->load->library('form_validation'); 
		// $this->load->model('users_m','user1');
		// $this->load->model('sys_email','sysemail');
		// $this->session->set_userdata('page_title','Login');
		// date_default_timezone_set(SYS_TIMEZONE);
		// $data = array();


		#MOVER LOADED MODELS
        // $this->load->model('pde_m');
        // $this->load->model('Pdetypes_m');
        // $this->load->model('Usergroups_m');	
		//  $this->load->model('Remoteapi_m');  	
		 
    }
    
    
	function home()
	{	
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'x'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['m'])){
			$addn = "/m/".$data['m'];
		} else {
			$addn = "";
		}
		
		#Unset navigation session settings
		$this->session->unset_userdata(array('from_search_results'=>''));
		
		
		#checks if the user's session expired
		if($this->session->userdata('userid') || ($this->input->cookie('loggedin') && $this->input->cookie('loggedin') == 'true' && empty($data['x'])))
    	{
        	if($this->session->userdata('fwdurl')){exit($this->session->userdata('fwdurl'));
				redirect($this->session->userdata('fwdurl'));
			}
			else
			{
				redirect($this->user1->get_dashboard().$addn);
			}
   		}
		else 
		{
        	setcookie("loggedin","false", time()+$this->config->item('sess_time_to_update'));
			#Consider passing on some messages even if the user is automatically logged out.
			if(!empty($data['m']) && in_array($data['m'], array('nmsg')))
			{
				$url = base_url().'admin/logout'.$addn;
			}
			else
			{
				$this->session->set_userdata('exp', 'Your session has expired.');
				$url = base_url().'admin/logout/m/exp';
			}
			
			redirect($url);
		}	
	}
	
