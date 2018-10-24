<?php
#*********************************************************************************
# Used to define all messages sent to users
#*********************************************************************************

#Define emails 
function get_confirmation_messages($obj, $formdata, $emailtype)
{
	$emailto = '';
	$emailcc = '';
	$emailbcc = '';
	$email_HTML = '';
	$subject = '';
	$fileurl = '';
	$formdata['messageid'] = generate_msg_id();
	$site_url = substr(base_url(), 0, -1);
	
	switch ($emailtype) 
	{
	case 'registration_confirm':
			$emailto = $formdata['emailaddress'];
			$emailbcc = SITE_ADMIN_MAIL;
			
			$subject = "Account details for ".$formdata['firstname']." ". $formdata['lastname'] ." at ".SITE_TITLE;
			
			$email_HTML = 	"Hello ". $formdata['firstname'] .",
							<br>
							A profile has been created for you on the ".SITE_TITLE.".
							<br>
							Login using the following details:
							<br>Email address: ".$formdata['emailaddress']."
							<br>Password: ".$formdata['password']."
							<br><br>
							For your security, you are advised to change your password the first time you login.";
			
			$email_HTML .= 	"<br><br>
							Regards,<br><br>
							Your team at ".SITE_SLOGAN."<br>".
							$site_url;
		break;
		
		
		
		
		case 'password_change_notice':
			$emailto = $formdata['emailaddress'];
			
			$subject = "Your password has been changed at ".SITE_TITLE;
			$call = !empty($formdata['firstname'])? $formdata['firstname']: $formdata['emailaddress'];
			$email_HTML = $call.",
						<br><br>
						Your password has been changed at ".SITE_TITLE.
						"<br><br>If you did not change your password or authorize its change, please contact us immediately at ".SECURITY_EMAIL;
			
			$email_HTML .= 	"<br><br>
						Regards,<br><br>
						Your team at ".SITE_TITLE."<br>
						".$site_url;
		break;
		
		
		
		
		
		case 'send_sys_msg_by_email':
			$emailto = NOREPLY_EMAIL;
			$emailbcc = $formdata['emailaddress'];
			
			$subject = $formdata['subject'];
			
			$email_HTML = "The following message has been sent to you from ".SITE_TITLE.":<br><hr>".nl2br($formdata['details'])
			."<hr><br>To respond to the above message, please login at:<br>"
			.base_url()."admin/login".
			"<br><br>and click on the messages icon to respond.";
			
			$email_HTML .= 	"<br><br>
							Regards,<br><br>
							Your team at ".SITE_TITLE."<br>
							".$site_url;
		break;
		
		
		
		
		
		
		
		
		case 'changed_password_notify':
			$emailto = $formdata['emailaddress'];
			
			$subject = "Your new password for ".SITE_TITLE;
			$call = !empty($formdata['firstname'])? $formdata['firstname']: $formdata['emailaddress'];
			$email_HTML = $call.",
						<br><br>
						Your new password for ".SITE_TITLE." is: ".$formdata['newpass'].
						"<br><br>If you did not request the new password, please contact us immediately at ".SECURITY_EMAIL;
			
			$email_HTML .= 	"<br><br>
						Regards,<br><br>
						Your team at ".SITE_TITLE."<br>
						".$site_url;
		break;
		
		
		
		
		
		
		case 'website_feedback':
			$emailto = $formdata['emailaddress'];
			$emailbcc = SITE_ADMIN_MAIL;
			
			$subject = "Your message to ".SITE_TITLE." has been received.";
			
			$email_HTML = "Hello,
						<br><br>
						Your message to ".SITE_TITLE." has been received. If necessary, you will be notified when our staff answers your message.<br>The details of your message are included below:
						<br><br>";
						
			$email_HTML .= "<table>
						<tr><td nowrap><b>Your email address:</b></td><td>".$formdata['emailaddress']."</td></tr>
						<tr><td nowrap><b>What do you need help with?</b></td><td>".$formdata['helptopic']."</td></tr>
						<tr><td nowrap><b>Subject:</b></td><td>".$formdata['subject']."</td></tr>
						<tr><td nowrap><b>Description:</b></td><td>".$formdata['description']."</td></tr>";
			
			if(!empty($formdata['attachmenturl']))
			{
				$email_HTML .= "<tr><td><b>Attachment:</b></td><td><a href='".base_url()."documents/force_download/f/".encryptValue('attachments')."/u/".encryptValue($formdata['attachmenturl'])."'>".$formdata['attachmenturl']."</a></td></tr>";
			}
						
			$email_HTML .= "</table>";
			
			$email_HTML .= 	"<br><br>
						Regards,<br><br>
						Your team at ".SITE_TITLE."<br>
						".$site_url;
		break;
				
		
		case 'account_reactivated_notice':
			$emailto = $formdata['emailaddress'];
			$emailbcc = SITE_ADMIN_MAIL;
			
			$subject = "Your account has been reactivated at ".SITE_TITLE;
			
			$email_HTML = $formdata['firstname'].",
						<br><br>
						Your account with username <b>".$formdata['username']."</b> has been reactivated at ".SITE_TITLE.".
						<BR><BR>
						If you did not authorize this action, please notify us immediately at ".SECURITY_EMAIL.".";
			
			$email_HTML .= 	"<br><br>
						Regards,<br><br>
						Your team at ".SITE_TITLE."<br>
						".$site_url;
		break;
		
		
		default:
			$emailto = $formdata['emailaddress'];
			if(!empty($formdata['subject'])){
				$subject = $formdata['subject'];
			}
			else
			{
				$subject = SITE_TITLE." Message";
			}
			
			$email_HTML = $formdata['message'];
			
		break;
	}
	
	$email_HTML .= "<br><br>MESSAGE ID: ".$formdata['messageid'];
	
	return array('emailto'=>$emailto, 'emailcc'=>$emailcc, 'emailbcc'=>$emailbcc, 'subject'=>$subject, 'message'=>$email_HTML, 'fileurl'=>$fileurl);
}


#Function to update form data from messages set in session
function add_msg_if_any($obj, $data)
{
	if(!empty($data['m']) && $obj->session->userdata($data['m'])){
		$data['msg'] = $obj->session->userdata($data['m']);
		$obj->session->unset_userdata(array($data['m']=>''));
	}
	
	return $data;
}

/*
function count_new_notifications($userid)
{
	 $ci=& get_instance();
	 $ci->load->model('notification_m', 'notifications');
	 return $ci->notifications->number_of_notifications($userid);
}

function notification_list($userid,$limit)
{
	 $ci=& get_instance();
	 $ci->load->model('notification_m', 'notifications');
	 return $ci->notifications->view_notifications($userid,$limit);	
}
 */

#Function to generate a message ID
function generate_msg_id()
{
	return "NY".strtotime('now');
}




#Function to generate a password
function generate_new_password()
{
	return "NY".substr(strtotime('now')."", -4);
}




#Function to generate a standard password
function generate_standard_password()
{
	return generate_random_character().strtoupper(generate_random_letter()).strtolower(generate_random_letter()).substr(strtotime('now')."", -4);
}


#Function to generate a standard serial number
function generate_standard_serial()
{
	return strtoupper(generate_random_letter()).strtoupper(generate_random_letter()).substr(strtotime('now')."", -4);
}


#Function to generate a deal ID
function get_deal_id($idtype='deal')
{
	if($idtype == 'order')
	{
		return "OD".strtotime('now').generate_random_letter();
	}
	else
	{
		return strtotime('now').generate_random_letter();
	}
}



#Function to generate a random letter
function generate_random_letter()
{
	$characters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O", "P","Q","R","S","T","U","V","W","X","Y","Z");
	$x = mt_rand(0, count($characters)-1);
	
	return $characters[$x];
}



#Function to generate a random character
function generate_random_character()
{
	$characters = array("_","@","#", "$", "%", "&", "!");
	$x = mt_rand(0, count($characters)-1);
	
	return $characters[$x];
}


#Function to make an object array (to enable tracking) out of a normal message from a redirection
function handle_redirected_msgs($obj, $data)
{
	if(!empty($data['msg']) && !is_array($data['msg'])){
		if(strcasecmp(substr($data['msg'], 0, 6), 'WARNING:') == 0){
			$error_code = "9902";
		}
		else
		{
			$error_code = "0";
		}
			
		$data['msg'] = array('obj'=>$obj, 'code'=>$error_code, 'details'=>$data['msg']);
	}
		
	return $data;
}


#log Access Trail
function log_access_trail($username='John Doe',$status="Success")
{
    $ci =& get_instance();
    $ci->load->model('users_m');
    return $ci->users_m->log_access_trail($username, $status);
}


?>