<?php
/**
 * This file contains common functions used in the system.
 *
 * @author Al Zziwa <azziwa@gmail.com>
 * @version 1.1.0
 * @copyright TMIS
 * @created 01/08/2015
 */






#Function to filter forwarded data to get only the passed variables
#In addition, it picks out all non-zero data from a URl array to be passed to a form
function filter_forwarded_data($obj, $urlDataArray=array(), $reroutedUrlDataArray=array(), $noOfPartsToIgnore=RETRIEVE_URL_DATA_IGNORE)
{
	# Get the passed details into the url data array if any
	$urlData = $obj->uri->uri_to_assoc($noOfPartsToIgnore, $urlDataArray);
	
	$dataArray = array();
	
	
	foreach($urlData AS $key=>$value)
	{
		if($value !== FALSE && trim($value) != '' && !array_key_exists($value, $urlData))
		{
			if($value == '_'){
				$dataArray[$key] = '';
			} else {
				$dataArray[$key] = $value;
			}
		}
	}
	
	#handle re-routed URL data
	if(!empty($reroutedUrlDataArray))
	{
		$urlInfo = $obj->uri->ruri_to_assoc(3);
		foreach($reroutedUrlDataArray AS $urlKey)
		{
			if(!empty($urlInfo[$urlKey]))
			{
				$dataArray[$urlKey] = $urlInfo[$urlKey];
			}
		}
	}
	
	return restore_bad_chars_in_array($dataArray);
}







# Goes through a row returned from a form escaping quotes and neutralising HTML insertions
function clean_form_data($formData)
{
	$cleanData = array();
	
	foreach($formData AS $key=>$value)
	{
		if(is_array($value))
		{
			foreach($value AS $subKey=>$subValue)
			{
				if(is_array($subValue))
				{
					foreach($subValue AS $subSubKey=>$subSubValue)
					{
						if(is_array($subSubValue))
						{
							foreach($subSubValue AS $subSubSubKey=>$subSubSubValue)
							{
								$cleanData[$key][$subKey][$subSubKey][$subSubSubKey] = htmlentities(trim($subSubSubValue), ENT_QUOTES);
							}
						}
						else
						{
							$cleanData[$key][$subKey][$subSubKey] = htmlentities(trim($subSubValue), ENT_QUOTES);
						}
					}
				}
				else
				{
					$cleanData[$key][$subKey] = htmlentities(trim($subValue), ENT_QUOTES);
				}
			}
		}
		else
		{
			$cleanData[$key] = htmlentities(trim($value), ENT_QUOTES); 
		}
	}
	
	return $cleanData;
}





#Checks if a password is valid
function is_valid_password($password, $validationSettings=array())
{
	$isValid = true;
	$minLength = !empty($validationSettings['minLength'])? $validationSettings['minLength']: 8;
	$maxLength = !empty($validationSettings['maxLength'])? $validationSettings['maxLength']: 60;
	$needsChar = !empty($validationSettings['needsChar'])? $validationSettings['needsChar']: false;
	$needsNumber = !empty($validationSettings['needsNumber'])? $validationSettings['needsNumber']: false;
	
	if(empty($password))
	{
		$isValid = false;
	}
	else if(strlen($password) < $minLength)
	{
		$isValid = false;
	}
	else if(strlen($password) > $maxLength)
	{
		$isValid = false;
	}
	#TODO: Fix preg_match regexpression
	else if($needsChar && !preg_match('/[[:punct:]]/', $password))
	{
		$isValid = false;
	}
	#TODO: Fix preg_match regexpression
	else if($needsNumber && !preg_match('/^[0-9]+$/', $password))
	{
		$isValid = false;
	}
	
	return $isValid;
}







# Returns the passed message with the appropriate formating based on whether it is an error or not
function format_notice($obj, $msg)
{
	$style = "border-radius: 5px;
	-moz-border-radius: 5px;";
	
	if(is_array($msg))
	{
		$result = $obj->_query_reader->run('save_error_msg', array('msgcode'=>$msg['code'], 'details'=>$msg['details'], 'username'=>$obj->session->userdata('username'), 'ipaddress'=>$obj->input->ip_address() ));
	
		$msg = $msg['details'];
	}
    
	# Error message. look for "WARNING:" in the message
	if(strcasecmp(substr($msg, 0, 8), 'WARNING:') == 0)
	{
		$msgString = "<table width='100%' border='0' cellspacing='0' cellpadding='5' style=\"".$style."border:0px;\">".
						"<tr><td width='1%' class='error' style='border:0px;padding:5px;min-width:0px;' nowrap>".str_replace("WARNING:", "<img src='".base_url()."assets/images/warning.png' border='0'/></td><td  class='error'  style='font-size:13px; color:#000;border:0px;' width='99%' valign='middle'>", $msg)."</td></tr>".
					  "</table>";
	}
	# Error message. look for "ERROR:" in the message
	else if(strcasecmp(substr($msg, 0, 6), 'ERROR:') == 0)
	{
		$msgString = "<table width='100%' border='0' cellspacing='0' cellpadding='5' style=\"".$style."border:0px;\">".
						"<tr><td class='error' style='border:0px;padding:5px;min-width:0px;' width='1%' nowrap>".str_replace("ERROR:", "<img src='".base_url()."assets/images/error.png'  border='0'/></td><td  width='99%' class='error'  style='font-size:13px;border:0px;' valign='middle'>", $msg)."</td></tr>".
					  "</table>";
		
		$userId = $obj->native_session->get('__user_id')? $obj->native_session->get('__user_id'): 'UNKNOWN';
		$email = $obj->native_session->get('__email_address')? $obj->native_session->get('__email_address'): 'UNKNOWN';
		$obj->_logger->add_event(array('log_code'=>'system_error', 'result'=>'fail', 'details'=>"userid=".$userId."|email=".$email."|msg=".$msg));
	}
	
	#Normal Message
	else
	{
		$msgString = "<table width='100%' border='0' cellspacing='0' cellpadding='5' style=\"".$style."border:0px;\">".
						"<tr><td class='message' style='border:0px;' nowrap>".$msg."</td></tr>".
					  "</table>";
	}
	
	return $msgString;
}





#Function to fomart a notice string to the appropriate color
function format_status($status)
{
	$statusString = str_replace('_', ' ', $status);
	
	if(strtolower($status) == 'pending' || strtolower($status) == 'suspended' || strtolower($status) == 'inactive' || strtolower($status) == 'unopened')
	{
		$statusString = "<span class='orange'>".$status."</span>";
	}
	elseif(strtolower($status) == 'joined' || strtolower($status) == 'active' || strtolower($status) == 'already_member' || strtolower($status) == 'member')
	{
		$statusString = "<span class='green'>".$status."</span>";
	}
	elseif(strtolower($status) == 'bounced' || strtolower($status) == 'blocked' || strtolower($status) == 'deleted' || strtolower($status) == 'not_eligible')
	{
		$statusString = "<span class='red'>".$status."</span>";
	}
	elseif(strtolower($status) == 'read' || strtolower($status) == 'clicked')
	{
		$statusString = "<span class='blue'>".$status."</span>";
	}
	
	return $statusString;
}





# Function that encrypts the entered values
function encrypt_value($value)
{
	$num = strlen($value);
	$numIndex = $num-1;
	$newValue="";
		
	#Reverse the order of characters
	for($x=0;$x<strlen($value);$x++){
		$newValue .= substr($value,$numIndex,1);
		$numIndex--;
	}
		
	#Encode the reversed value
	$newValue = base64_encode($newValue);
	return $newValue;
}
	
	
#Function that decrypts the entered values
function decrypt_value($dvalue)
{
	#Decode value
	$dvalue = base64_decode($dvalue);
		
	$dnum = strlen($dvalue);
	$dnumIndex = $dnum-1;
	$newDvalue = "";
		
	#Reverse the order of characters
	for($x=0;$x<strlen($dvalue);$x++){
		$newDvalue .= substr($dvalue,$dnumIndex,1);
		$dnumIndex--;
	}
	return $newDvalue;
}



# Function to replace placeholders for bad characters in a text passed in URL with their actual characters
function restore_bad_chars($goodString)
{
	$badString = '';
	$badChars = array("'", "\"", "\\", "(", ")", "/", "<", ">", "!", "#", "@", "%", "&", "?", "$", ",", " ", ":", ";", "=", "*");
	$replaceChars = array("_QUOTE_", "_DOUBLEQUOTE_", "_BACKSLASH_", "_OPENPARENTHESIS_", "_CLOSEPARENTHESIS_", "_FORWARDSLASH_", "_OPENCODE_", "_CLOSECODE_", "_EXCLAMATION_", "_HASH_", "_EACH_", "_PERCENT_", "_AND_", "_QUESTION_", "_DOLLAR_", "_COMMA_", "_SPACE_", "_FULLCOLON_", "_SEMICOLON_", "_EQUAL_","_ASTERISK_");
	
	foreach($replaceChars AS $pos => $charEquivalent)
	{
		$badString = str_replace($charEquivalent, $badChars[$pos], $goodString);
		$goodString = $badString;
	}
	
	return $badString;
}

# Function to replace bad characters before they are passed in a URL
function replace_bad_chars($badString)
{
	$badChars = array("'", "\"", "\\", "(", ")", "/", "<", ">", "!", "#", "@", "%", "&", "?", "$", ",", " ", ":", ";", "=");
	$replaceChars = array("_QUOTE_", "_DOUBLEQUOTE_", "_BACKSLASH_", "_OPENPARENTHESIS_", "_CLOSEPARENTHESIS_", "_FORWARDSLASH_", "_OPENCODE_", "_CLOSECODE_", "_EXCLAMATION_", "_HASH_", "_EACH_", "_PERCENT_", "_AND_", "_QUESTION_", "_DOLLAR_", "_COMMA_", "_SPACE_", "_FULLCOLON_", "_SEMICOLON_", "_EQUAL_");
	$goodString = '';
	
	foreach($badChars AS $pos => $char){
		$goodString = str_replace($char, $replaceChars[$pos], $badString);
		$badString = $goodString;
	}
	
	return $goodString;
}


# Restore bar chars in an array
function restore_bad_chars_in_array($goodArray)
{
	$badArray = array();
	
	foreach($goodArray AS $key=>$item)
	{
		$badArray[$key] = restore_bad_chars($item);
	}
	
	return $badArray;
}







# Returns the AJAX constructor to a page where needed
function get_ajax_constructor($needsAjax, $extraIds=array())
{
	$ajaxString = "";
	
	if($needsAjax)
	{
		$ajaxString = "<script language=\"javascript\"  type=\"text/javascript\">".
							"var http = getHTTPObject();";
							
		if(!empty($extraIds))
		{
			foreach($extraIds AS $id)
			{
				$ajaxString .=  "var ".$id." = getHTTPObject();";
			}
		}					
		$ajaxString .=  "</script>";
	}
	return $ajaxString;
}




//Function to return a number with two decimal places and a comma after three places
function add_commas($number, $noDecimalPlaces=2)
{
	if(!isset($number) || $number == "" ||  $number <= 0)
	{
		return number_format('0.00', $noDecimalPlaces, '.', ',');
	} 
	else 
	{
		return number_format(remove_commas($number), $noDecimalPlaces, '.', ',');
	}
}
	
//Function to remove commas before saving to the database
function remove_commas($number)
{
	return clean_str(str_replace(",","",$number));
}

	
//Function to remove quotes before saving to the database
function remove_quotes($string)
{
	return str_replace('"', '', str_replace("'", '', $string));
}
	
//Function to clean user input so that it doesnt break the display functions
//This also helps disable hacker bugs
function clean_str($strInput)
{
	return htmlentities(trim($strInput));
}


	
	


#Function to get current user's IP address
function get_ip_address()
{
	$ip = "";
	if ( isset($_SERVER["REMOTE_ADDR"]) )    
	{
    	$ip = ''.$_SERVER["REMOTE_ADDR"];
	} 
	else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    
	{
    	$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} 
	else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )
	{
    	$ip = $_SERVER["HTTP_CLIENT_IP"];
	}
	
	return (ENVIRONMENT == 'development' || (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== FALSE))? DEFAULT_IP: $ip;
}



function get_current_uri($escapeQuotes=TRUE)
{
	$link =  "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	return $escapeQuotes? htmlspecialchars($link, ENT_QUOTES, 'UTF-8'): $link;
}





#Function to format phone number for display
function format_phone_number($number, $country='USA')
{
	$finalNumber = "";
	if(!empty($number))
	{
		#For 10 digit countries
		if(in_array($country, array('USA')))
		{
			#+1(213)123-4567
			$finalNumber = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $number);
		}
	}
	
	return $finalNumber;
}




# This function converts a binary string to hexadecimal characters.
# @param $bytes  Input string.
# @return String with lowercase hexadecimal characters.
function string_to_hex($bytes) 
{
	$ret = '';
	for($i = 0; $i < strlen($bytes); $i++) {
		$ret .= sprintf('%02x', ord($bytes[$i]));
	}
	return $ret;
}


#Function to generate random bytes
function generate_random_bytes($length) 
{
	# Use mt_rand to generate $length random bytes. 
	$data = '';
	for($i = 0; $i < $length; $i++) 
	{
		$data .= (rand()%9);
	}

	return $data;
}
	
#Function to generate an ID
function generate_id() 
{
	return '_' . string_to_hex(generate_random_bytes(21));
}


	
# Function checks all values to see if they are all true and returns the value TRUE or FALSE
function get_decision($values_array, $defaultTo=TRUE)
{
	$decision = empty($values_array)? $defaultTo: TRUE;
	
	if(empty($values_array))
	{
		foreach($values_array AS $value)
		{
			if(!$value)
			{
				$decision = FALSE;
				break;
			}
		}
	}
	
	return $decision;
}


function user_photo_thumb($photo)
{
	$photo_array = explode('.', $photo);
	if(count($photo_array)>1)
	{
		return $photo_array[0].'_thumb.'.$photo_array[1];
	}
	else
	{
		return 'user.jpg';
	}
}

#Function to display options of a select box
function write_options_list($obj, $table, $selected = "",$cname='name',$id='id'){
	#die("<option>".$table['searchstring']."</option>");
	if($table['searchstring'] == '')
		$query_string = $obj->_query_reader->get_query_by_code('get_options',array('table' => $table['tname'],'searchstring' => ''));
	else
	$query_string = $obj->_query_reader->get_query_by_code('get_options',array('table' => $table['tname'],'searchstring' => $table['searchstring']));
	#if($table['tname'] == "vehiclemodels")
	   #die("<option>".$query_string."</option>");
	$result = $obj->db->query($query_string);
	
	echo "<option value=\"\">-Select an option-</option>";
	foreach($result->result() AS $row){
		?>
        <option value="<?php echo $row->$id ?>"
        <?php
			if($selected == $row->$id) echo "selected";
		?>
        ><?php echo $row->$cname ?></option>
        <?php
	}
}


# Returns the select options based on the passed data, id and value fields, and selected value
function get_select_options($select_data_array, $value_field, $display_field, $selected, $show_instr='Y', $instr_txt='Select')
{	
	$drop_HTML = "";
	#Determine whether to show the instruction option
	if($show_instr == 'Y'){
		$drop_HTML = "<option value='' ";
		# Select by default if there is no selected option
		if($selected == '')
		{
			$drop_HTML .= " selected";
		}
	
		$drop_HTML .= ">- ".$instr_txt." -</option>";
	}
	
	foreach($select_data_array AS $data_row)
	{
		$drop_HTML .= " <option  value='".addslashes($data_row[$value_field])."' ";
		
		# Show as selected if value matches the passed value
		#check if passed value is an array		
        if(is_array($selected)){
        	if(in_array($data_row[$value_field], $selected)) $drop_HTML .= " selected";
                  
		}elseif(!is_array($selected)){
        	if($selected == $data_row[$value_field]) $drop_HTML .= " selected";
        }		
				
		$display_array = array();
		# Display all data given based on whether what is passed is an array
		if(is_array($display_field))
		{
			$drop_HTML .= ">";
			
			foreach($display_field AS $display)
			{
				array_push($display_array, $data_row[$display]);
			}
			
			$drop_HTML .= implode(' - ', $display_array)."</option>";
		}
		else
		{
			$drop_HTML .= ">".$data_row[$display_field]."</option>";
		}
	}
	
	return $drop_HTML;
}



#Function to return user friendly text if a value is empty
function check_empty_value($value, $return_text)
{
 	if(empty($value))
	{
		return $return_text;
	}
	elseif(is_null($value) || $value == '')
    {
    	return $return_text;
    }
    else
    {
    	return $value;
    }
}



#Function to check user access
function check_user_access($obj, $access_code, $action='returnbool', $search_level='page')
{
	if($search_level== 'group')
	{
		$user_details = $obj->_query_reader->get_row_as_array('check_user_access_section', array('groupid'=>$obj->native_session->get('__permission_group'), 'accesscode'=>$access_code));
	}
	else if($search_level == 'page')
	{
		$user_details = $obj->_query_reader->get_row_as_array('check_user_access', array('groupid'=>$obj->native_session->get('__permission_group'), 'accesscode'=>$access_code));
	}
		
	if(!empty($user_details)){
		if($action == 'returnbool'){
			return TRUE;
		}
		else if($action == 'redirect')
		{
			#Do nothing - continue with loading the page
		}
	}
	else
	{
		if($action == 'returnbool'){
			return FALSE;
		}
		else if($action == 'redirect')
		{
			$obj->session->set_userdata('emsg', "WARNING: You do not have access to this page.");
			redirect('admin/load_dashboard/m/emsg');
		}
	}
	
}

#Function to execute a query and return the results as an array
function get_row_as_array($obj, $query) 
{
	$row = array();
	$result = $obj->db->query($query);
	$alldata = $result->result_array();
	if(count($alldata) > 0){
		$row = $alldata[0];
	}
	
	return $row;
}

#fucntion to replace the query value placeholders with the actual values
function replaceValuePlaceHolders($querystr, $value_array)
{
	foreach($value_array AS $key=>$value){
		$querystr = str_replace('_'.strtoupper($key).'_', $value, $querystr);
	}
		
	return $querystr;
}


#Function to hide digits of a string given and show only the part desired
function hide_digits($fullString, $showLast=2, $hideChar='*')
{
	$fullLength = strlen($fullString);
	$hideLength = $fullLength - $showLast;
	$finalString = "";
	for($i=0;$i<$hideLength; $i++)
	{
		$finalString .= $hideChar;
	}
	
	#Add the part not to be hidden
	$finalString .= substr($fullString, -$showLast);
	
	return $finalString;
}




#Validate an email address. If the email address is not required, then an empty string will be an acceptable
#value for the email address
function is_valid_email($email, $isRequired = true)
{
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   
	   #if email is not required and is an empty string, do not check it. Return True.
	   if(!$isRequired && empty($email)){
		   return true;
	   }
	   if (is_bool($atIndex) && !$atIndex){
		  $isValid = false;
	   } else {
		  $domain = substr($email, $atIndex+1);
		  $local = substr($email, 0, $atIndex);
		  $localLen = strlen($local);
		  $domainLen = strlen($domain);
		  
		if ($localLen < 1 || $localLen > 64) {
			 # local part length exceeded
			 $isValid = false;
		  } else if ($domainLen < 1 || $domainLen > 255) {
			 # domain part length exceeded
			 $isValid = false;
		  }  else if ($local[0] == '.' || $local[$localLen-1] == '.') {
			 # local part starts or ends with '.'
			 $isValid = false;
		  } else if (preg_match('/\\.\\./', $local)) {
			 # local part has two consecutive dots
			 $isValid = false;
		  } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
			 # character not valid in domain part
			 $isValid = false;
		  } else if (preg_match('/\\.\\./', $domain)) {
			 # domain part has two consecutive dots
			 $isValid = false;
		  } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
			 # character not valid in local part unless 
			 # local part is quoted
			 if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
				$isValid = false;
			 }
		  } else if (strpos($domain, '.') === FALSE) {
			 # domain has no period
			 $isValid = false;
		  }
		  
		 /* if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
			 # domain not found in DNS
			 $isValid = false;
		  } */
	 }
	 #return true if all above pass
	 return $isValid;
}
	
	
	
#Validate a delimited list of email addresses
function is_valid_email_list($emaillist, $isRequired = true, $delimiter = ",") 
{
	$list = explode($delimiter, $emaillist); 
	foreach ($list as $email) {
		if (!is_valid_email($email, $isRequired)) {
			return false; 
		} 
	}
	return true; 
}




#Convert from one base to another
function convert_bases($numberInput, $fromBaseInput, $toBaseInput)
{
    if ($fromBaseInput==$toBaseInput) return $numberInput;
    $fromBase = str_split($fromBaseInput,1);
    $toBase = str_split($toBaseInput,1);
    $number = str_split($numberInput,1);
    $fromLen=strlen($fromBaseInput);
    $toLen=strlen($toBaseInput);
    $numberLen=strlen($numberInput);
    $retval='';
    if ($toBaseInput == '0123456789')
    {
        $retval=0;
        for ($i = 1;$i <= $numberLen; $i++)
            $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
        return $retval;
    }
    if ($fromBaseInput != '0123456789')
        $base10=convBase($numberInput, $fromBaseInput, '0123456789');
    else
        $base10 = $numberInput;
		
    if ($base10<strlen($toBaseInput))
        return $toBase[$base10];
    while($base10 != '0')
    {
        $retval = $toBase[bcmod($base10,$toLen)].$retval;
        $base10 = bcdiv($base10,$toLen,0);
    }
    return $retval;
}


#Function to check if string contains special characters
function does_string_contain_special_characters($string, $allowSpaces=FALSE)
{
	if (!$allowSpaces && !preg_match("#^[a-zA-Z0-9]+$#", $string)) {
   		return TRUE;  
	} 
	else if ($allowSpaces && !preg_match("#^[a-zA-Z0-9 ]+$#", $string)) {
   		return TRUE;  
	} 
	else 
	{
   		return FALSE;
	}
}


#Function to clean a string and remove special characters or spaces
function remove_string_special_characters($string, $allowSpaces=FALSE)
{
	if($allowSpaces)
	{
		$string = str_replace(' ', '-', $string);
		return str_replace('-', ' ', preg_replace('/[^A-Za-z0-9\-]/', '', $string));
	}
	else
	{
		return preg_replace('/[^A-Za-z0-9]/', '', $string);
	}
}


# Format the date according to instructions given
function format_date($dateString, $instruction="YYYY-MM-DD H:I:S", $default="&nbsp;")
{
	$date = $dateString;
	# Proceed if the date is not empty
	if(!(empty($dateString) || $dateString == '0000-00-00 00:00:00' || $dateString == '0000-00-00'))
	{





   

    $display = '';
    switch ($dateString) {
        case '0000-00-00':
         $display = '';
            break;
        case '0000-00-00 00:00:00':
         $display = '';
         break;
        case '30-11--0001':
         $display = '';
            break;
        case '00:00:00':
         $display = '';
            break;
        case '1970-01-01':
         $display = '';
            break;
        case ' 1970-01-01 ':
         $display = '';
            break;
        case '1970-01-01 00:00:00':
         $display = '';
            break;
        case '1970-01-01 00-00-00':
         $display = '';
            break;



       case '0000/00/00':
        $display = '';
            break;
        case '0000/00/00 00:00:00':
         $display = '';
            break;
        case '00:00:00':
         $display = '';
            break;
        case '1970/01/01':
         $display = '';
            break;
        case ' 1970/01/01 ':
         $display = '';
            break;
        case '1970/01/01 00:00:00':
         $display = '';
            break;
        case '1970/01/01 00-00-00':
         $display = '';
            break;



        case '':
         $display = '';
            break;
        case ' ':
         $display = '';
            break;
        default:
            $display = trim($dateString);
             
            break;
    }


	 if(empty($display))
	        return ''; 

	 $dateString = $display; 

		switch($instruction)
		{
			case "YYYY-MM-DD":
				$date = date("Y-m-d", strtotime($dateString));
			break;
			
			case "YYYY-MM-DD H:I:S":
				$date = date("Y-m-d H:i:s", strtotime($dateString));
			break;
			
			case "Y-m-d":
				$date = date("Y-m-d", strtotime($dateString));
			break;
			
			case "d-M-Y h:i:s":
			case "d-M-Y h:i:sa T":
			case "d-M-Y h:ia T":
			case "d-M-Y":
				$date = date($instruction, strtotime($dateString));
			break;
		}
	}
	else
	{
		$date = $default; 
	}
	
	return $date;
}







#Function to provide the difference of two dates in a desired format
#$minKey tells the function which minimum key to return in ideal situation, but if this key is empty, it will return the next non-empty key below it
function format_date_interval($startDate, $endDate, $returnArray=TRUE, $ignoreEmpty=TRUE, $minKey='')
{
    $interval = date_diff(date_create($startDate), date_create($endDate));
    $diffString = $interval->format("years:%Y,months:%M,days:%d,hours:%H,minutes:%i,seconds:%s");
    
	#Put the diff in an array
	$diffArray = array();
    array_walk(explode(',',$diffString),
    
	function($val,$key) use(&$diffArray){
        $diffPart=explode(':',$val);
        $diffArray[$diffPart[0]] = $diffPart[1];
    });
	
	#Remove the empty parts of the array
	$finalArray = array();
	foreach($diffArray AS $partKey=>$intervalPart)
	{
		$intervalPart = $intervalPart+0;
		if(!empty($intervalPart))
		{
			$finalArray[$partKey] = $intervalPart;
		}
	}
	
	#Now consider the minKey to be returned
	if(!empty($minKey))
	{
		$finalMinArray = array();
		$lastNonEmptyValue = 0;
		$reachedMinKey = FALSE;
			
		foreach($diffArray AS $key=>$value)
		{
			$value = 0+$value;
			#Only update the last non-empty value if you encounter a non-empty value
			$lastNonEmptyValue = !empty($value)? $value: $lastNonEmptyValue;
			
			if(!empty($value) && $key != $minKey)
			{
				$finalMinArray[$key] = $value;
				#Break if you already passed the minimum key
				if($reachedMinKey) break;
			} 
			else if(trim($key) == $minKey)
			{
				if(!empty($value))
				{
					$finalMinArray[$key] = $value;
				}
				$reachedMinKey = TRUE;
				if(!empty($lastNonEmptyValue))break;
			}
		}
		
		$finalArray = $finalMinArray;
	}
	
	
	#Return the interval in a desired format
	if($returnArray)
	{
		#Ignore empty parts of the interval or not?
		return $ignoreEmpty? $finalArray : $diffArray;
	}
	else
	{
		if($ignoreEmpty)
    	{
			$finalString = "";
			foreach($finalArray AS $partKey=>$intervalPart)
			{
				$intervalPart = 0+$intervalPart;
				$finalString .= $intervalPart." ".($intervalPart == 1? substr($partKey, 0, -1): $partKey).", ";
			}
			return !empty($finalArray)? trim($finalString, ', '): "0 seconds";
		}
		else 
		{
			return $diffString;
		}
	}
    
	
}









#Function to format a number to a desired length and format
function format_number($number, $maxCharLength=100, $decimalPlaces=2, $singleChar=TRUE, $hasCommas=TRUE)
{
	#first strip any formatting;
    $number = (0+str_replace(",","",$number));
    #is this a number?
    if(!is_numeric($number)) return false;
	
	#now format it based on desired length and other instructions
    if($number > 1000000000000 && $maxCharLength < 13) return number_format(($number/1000000000000),$decimalPlaces, '.', ($hasCommas? ',': '')).($singleChar? 'T': ' trillion');
    else if($number > 1000000000 && $maxCharLength < 10) return number_format(($number/1000000000),$decimalPlaces, '.', ($hasCommas? ',': '')).($singleChar? 'B': ' billion');
    else if($number > 1000000 && $maxCharLength < 7) return number_format(($number/1000000),$decimalPlaces, '.', ($hasCommas? ',': '')).($singleChar? 'M': ' million');
    else if($number > 1000 && $maxCharLength < 4) return number_format(($number/1000),$decimalPlaces, '.', ($hasCommas? ',': '')).($singleChar? 'K': ' thousand');
	else return number_format($number,(is_float($number)? $decimalPlaces: 0), '.', ($hasCommas? ',': ''));
}






#limit string length
function limit_string_length($string, $maxLength, $ignoreSpaces=TRUE, $endString='..')
{
    if (strlen(html_entity_decode($string, ENT_QUOTES)) <= $maxLength) return $string;
	
	if(!$ignoreSpaces)
	{
    	$newString = substr($string, 0, $maxLength);
		$newString = (substr($newString, -1, 1) != ' ')?substr($newString, 0, strrpos($newString, " ")) : $string;
	}
	else
	{
		$newString = substr(html_entity_decode($string, ENT_QUOTES), 0, $maxLength);
		if(strpos($newString, '&') !== FALSE)
		{
			$newString = substr($newString, 0, strrpos($newString, " "));
		}
	}
	
    return $newString.$endString;
}



#Function to compute distance between two latitudes and longitudes
function compute_distance_between_latitude_and_longitude($latitude1, $longitude1, $latitude2, $longitude2, $unit='miles')
{
	$theta = $longitude1 - $longitude2;
  	$distance = sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)) +  cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta));
	$distance = acos($distance);
	$distance = rad2deg($distance);
	$miles = $distance * 60 * 1.1515;
	$unit = strtoupper($unit);
	 
	if ($unit == "kilometers") 
	{
	   return ($miles * 1.609344);
	} 
	else if ($unit == "nautical_miles") 
	{
	   return ($miles * 0.8684);
    } 
	else if ($unit == "miles")
	{
	   return $miles;
	}
}



#Function to compute age from birthday
function compute_age_from_birthday($birthday, $returnType='years')
{
	$age = 0;
	
	if(!empty($birthday) && $birthday != '0000-00-00')
	{
		$interval = format_date_interval($birthday, date('Y-m-d'), TRUE, FALSE);
		if($returnType == 'years')
		{
			$age = $interval['years'];
		}
		else
		{
			$age = $interval;
		}
	}
	
	return $age;
}



#Function to check whether a variable is not empty
function is_not_empty($variable)
{
	return !empty($variable);
}


# get a list of sort columns and their data to pass to array_multisort
function pick_sort_list_data($data, $dataKeys)
{
	$sortList = array();
	foreach($data AS $key=>$row)
	{
   	 	#Pick the columns to sort by
		foreach($dataKeys AS $dataKey)
		{
			$sortList[$dataKey][$key] = !empty($row[$dataKey])?$row[$dataKey]:'';
		}
	}
	
	return $sortList;
}



#Format website for display
function format_website_for_display($rawWebsite)
{
	$website = strtolower($rawWebsite);
	if(strpos($website, 'http://') !== false)
	{
		$website = substr($rawWebsite, 7);
	}
	else if(strpos($website, 'https://') !== false)
	{
		$website = substr($rawWebsite, 8);
	}
	#Do not show derivative URLs for source
	else if(strpos($website, '?') !== false || strlen($website) > 45)
	{
		$website = (strpos($website, '?') !== false && strlen($website) < 45)? $website: "";
	}
	else 
	{
		$website = $rawWebsite;
	}
	#Remove trailing slash if it is there
	$website = (substr($website, -1) == '/')? substr($website, 0, -1): $website;
	return strlen($website)> 45? "<a href='".$rawWebsite."' target='_blank'>".substr($website, 0,44).'..</a>': $website;
}



#Remove an array item from the given items and return the final array
function remove_item($item, $fullArray)
{
	#First remove the item from the array list
	unset($fullArray[array_search($item, $fullArray)]);
	
	return $fullArray;
}




#Return a string between the given strings
function get_string_between($string, $start, $end)
{
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
	
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
	
    return substr($string,$ini,$len);
}



#Function to get a slow loading page link
function get_slow_link_url($url, $title, $loadingMessage='')
{
	return base_url().'page/load_slow_page/p/'.encrypt_value($url).'/t/'.encrypt_value($title).(!empty($loadingMessage)? '/m/'.encrypt_value($loadingMessage): '');
}



#Function to get the longitude and latitude of a location given its address
function get_longitude_latitude_from_address($address)
{
	$location = array('longitude'=>'', 'latitude'=>'');
	#Remove any trailing spaces
	$address = trim($address);
	
	if(!empty($address))
	{
		$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.str_replace(' ','+',$address).'&sensor=false');
        $output= json_decode($geocode);
        $location['latitude'] = !empty($output->results[0]->geometry->location->lat)? $output->results[0]->geometry->location->lat: '';
        $location['longitude'] = !empty($output->results[0]->geometry->location->lng)? $output->results[0]->geometry->location->lng: '';
	}
	
	return $location;
}



#Function to get unique array values from a multidimensional array
function multi_array_unique($array)
{
	return array_map("unserialize", array_unique(array_map("serialize", $array)));
}




#CHecks whether an array key that begins or ends in a value is in the passed array
function array_key_contains($keyPart, $array)
{
	 
	 

	$keys = array_keys($array);

 

	$theKey = '';
	$exists = FALSE;
	
	foreach($keys AS $key)
	{

		if(strpos($key, $keyPart) !== FALSE)
		{
			$exists = TRUE;
			$theKey = $key;
			break;
		}
	}
	
	return array('boolean'=>$exists, 'key'=>$theKey);
}





# Generates an 8-character temporary password for the user - this is a one time case and system does not keep un-encrypted copy
function generate_temp_password()
{
	$numbers = '0123456789';
	$letters = 'abcdefghijklmnopqrstuvwxyz';
	$characters = '_!-*.';
	$time = strtotime('now');
	
	$password = array();
	$password[0] = $letters[rand(0, strlen($letters)-1)];
	$password[1] = $letters[rand(0, strlen($letters)-1)];
	$password[2] = $numbers[rand(0, strlen($numbers)-1)];
	$password[3] = $characters[rand(0, strlen($characters)-1)];
	$password[4] = $time[rand(0, strlen($time)-1)];
	$password[5] = strtoupper($letters[rand(0, strlen($letters)-1)]);
	$password[6] = $letters[rand(0, strlen($letters)-1)];
	$password[7] = $time[rand(0, strlen($time)-1)];
	
	return implode('',$password);
}



# Generate a verification code for a new person
function generate_person_code($id)
{
	return strrev(strtoupper(generate_random_bytes(2).dechex($id)));
}




# Get the row in a multi dimensional array that has a specified key set as the given value
function get_row_from_list($list, $key, $value, $return='value')
{
	$selected = array();
	foreach($list AS $i=>$row)
	{
		if(array_key_exists($key, $row) && $row[$key] == $value){
			$selected = ($return == 'key')? $i: $row; 
			break;
		}
	}
	
	return $selected;
}


# Get the first page to hit when logged in
function get_user_dashboard($obj, $userId)
{
	# 1. Has the system set a default page to redirect to?
	if($obj->native_session->get('redirect_url'))
	{
		$page = $obj->native_session->get('redirect_url');
		$obj->native_session->delete('redirect_url');
	} 
	else
	{
		# 2. Get the user group
		if($obj->native_session->get('__permission_group'))
		{
			$groupId = $obj->native_session->get('__permission_group');
		}
		else
		{
			$user = $obj->_query_reader->get_row_as_array('get_user_by_id', array('user_id'=>$userId));
			$groupId = $user['permission_group_id'];
		}
	
		# 3. Get the group default page
		if($obj->native_session->get('__group_default_page'))
		{
			$page = $obj->native_session->get('__group_default_page');
		}
		else if($obj->native_session->get('__permissions'))
		{
			#Go to the group default page if allowed
			$default = $obj->_query_reader->get_row_as_array('get_group_default_permission', array('group_id'=>$groupId));
			if(!empty($default['code']) && in_array($default['code'], $obj->native_session->get('__permissions')))
			{
				$page = $default['page'];
			}
			# 4. If the user is not allowed to view default page, go to the first allowed permission
			else 
			{
				$permissions = $obj->native_session->get('__permissions');
				$permission = $obj->_query_reader->get_row_as_array('get_permission_by_code', array('code'=>$permissions[0]));
				$page = !empty($permission['url'])? $permission['url']: "";
			}
			
			# Set this so that you do not have to fetch the default page from the DB again - for this user's session
			if(!empty($page)) $obj->native_session->set('__group_default_page', $page);
		}
		
		# 5. If none, logout the user and notify
		if(empty($page))
		{
			$page = 'account/logout';
			$obj->native_session->set('msg', 'ERROR: Your account does not have any access permissions.');
		}
	}
	
	return $page;
}



# Get the message stored in the session to be shown at the given area
function get_session_msg($obj)
{
	$msg = $obj->native_session->get('msg')? $obj->native_session->get('msg'): "";
	$obj->native_session->delete('msg');
	
	return $msg;
}



# Check user access to a given feature
# Valid return options [msg, boolean]
function check_access($obj, $accessCode, $return='msg', $setMenuItem=true)
{
	# 1. Are the user's permissions set and they have the requested permission?
	# then, return appropriate response

	#print_r($accessCode);

	#print_r($obj->native_session->get('__permissions'));

	if($obj->native_session->get('__permissions') && in_array($accessCode, $obj->native_session->get('__permissions')))
	{
		if($setMenuItem) $obj->native_session->set('__selected_permission', $accessCode);
		if($return == 'boolean') return true;
	}
	else
	{
		if($return == 'boolean')
		{
			return false;
		}
		else
		{
			$obj->native_session->set('msg', "ERROR: You do not have access to this feature.");
			redirect(base_url().($obj->native_session->get('__user_id')? get_user_dashboard($obj, $obj->native_session->get('__user_id')): 'account/logout')); 
		}
	}
}



# Choose the right permission access code to return to the function - up to 2 levels
function get_access_code($data, $instructions)
{
	$code = '';
	
	# $key = 'action', $value = full array for action permissions
	foreach($instructions AS $key=>$value)
	{
		# 'action' is passed in the data
		if(array_key_exists($key, $data))
		{
			# Check if this is an array - which requires further processing
			if(!empty($value[$data[$key]]) && is_array($value[$data[$key]]))
			{
				# Loop through 'level' array
				foreach($value[$data[$key]] AS $key2=>$value2)
				{
					# Level exists
					if(array_key_exists($key2, $data))
					{
						$code = $value2[$data[$key2]];
						break 2;
					}
				}
			}
			#Code is available at the first level
			else if(!empty($value[$data[$key]]))
			{
				$code = $value[$data[$key]];
				break;
			}
		}
	}
	
	#Handle a unique case where a default access code for the function is provided
	if(empty($code) && array_key_exists('', $instructions))
	{
		$code = $instructions[''];
	}
	
	
 
	return $code;
}



#Check if a model is loaded 
function is_model_loaded($obj, $modelName) 
{
	return in_array($modelName, $obj->ci_models, TRUE);
}



#Process the other field for data
function process_other_field($data)
{
	if(!empty($data['other']))
	{
		$level1Parts = explode('|', $data['other']);
		foreach($level1Parts AS $part)
		{
			if(!empty($part))
			{
				$level2Parts = explode('=', $part);
				if(count($level2Parts) > 1) $data[$level2Parts[0]] = restore_bad_chars($level2Parts[1]);
			}
		}
	}
	
	return $data;
}




# Force file download
function force_download($folder, $file)
{

	$url = trim(UPLOAD_DIRECTORY.$folder.'/'.$file);
	
	 

 
	if(file_exists($url)  )
	{

		 
 
		if(strtolower(strrchr($file,".")) == '.pdf')
		{
			header('Content-disposition: attachment; filename="'.$file.'"');
			header('Content-type: application/pdf');
			readfile(UPLOAD_DIRECTORY.$folder."/".$file);
		}
		else if(strtolower(strrchr($file,".")) == '.xls')
		{
			
			
			// We'll be outputting an excel file
			header('Content-type: application/vnd.ms-excel');

			// It will be called file.xls
			header('Content-Disposition: attachment; ');
			 
			readfile(UPLOAD_DIRECTORY.$folder."/".$file);
		}
		else if(strtolower(strrchr($file,".")) == '.zip')
		{
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename="'.strtotime('now').str_replace('.','',get_ip_address()).'.zip"');
			header('Content-Transfer-Encoding: binary');
			header('Vary: Accept-Encoding');
			header('Content-Encoding: gzip');
			header('Keep-Alive: timeout=5, max=100');
			header('Connection: Keep-Alive');
			header('Transfer-Encoding: chunked');
			header('Content-Type: application/octet-stream');
			apache_setenv('no-gzip', '1');

		}
		else
		{
			 
			redirect(base_url()."assets/uploads/".$folder."/".$file);
		}
	}
}




# Send download headers
function send_download_headers($filename) 
{
    # disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    # force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    # disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}



# Convert an array to csv
function array2csv(array &$array)
{
   if(count($array) == 0) 
   {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   
   foreach($array AS $row) 
   {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}




# Minify a list of files
function minify_js($page, $files) 
{
	$string = "";
	# Minify and show the minified version
	if(MINIFY)
	{
		$fileLocation = HOME_URL.'assets/js/';
		# If the file exists, just return the file, else create the minified version
		if(!file_exists($fileLocation.'__'.$page.'.min.js'))
		{
			require_once(HOME_URL.'external_libraries/jsmin/JSMin.php');
			foreach($files AS $file)
			{
				$min = JSMin::minify(file_get_contents($fileLocation.$file));
  				file_put_contents($fileLocation.'__'.$page.'.min.js', $min, FILE_APPEND);
			}
		}
		$string = "<script type='text/javascript' src='".base_url()."assets/js/__".$page.".min.js'></script>"; 
	}
	# List the files out one by one
	else
	{
		foreach($files AS $file) $string .= "<script type='text/javascript' src='".base_url()."assets/js/".$file."'></script>";
	}
	
	return $string;
}


function format_column_numbers($data_array, $array_cols)
{
	$new_data_array = array();
	foreach($data_array AS $rowkey=>$row)
	{
		foreach($row AS $key=>$value)
		{	
			#Format the field if it is in the given columns
			if(in_array($key, $array_cols))
			{
				$new_data_array[$rowkey][$key] = addCommas(str_replace('$','',str_replace(',','',$value)));
			} 
			else 
			{
				$new_data_array[$rowkey][$key] = $value;
			}
		}
	}
	
	return $new_data_array;
}


 	//Function to return a number with two decimal places and a comma after three places
	function addCommas($number, $no_decimal_places=2){
		if(!isset($number) || $number == "" ||  $number <= 0){
			return number_format('0.00', $no_decimal_places, '.', ',');
		} else {
			return number_format(removeCommas($number), $no_decimal_places, '.', ',');
		}
	}
	



	//Function to remove commas before saving to the database
	function removeCommas($number){
		return cleanStr(str_replace(",","",$number));
	}
	
	//Function to clean user input so that it doesnt break the display functions
	//This also helps disable hacker bugs
	function cleanStr($strinput){
		return htmlentities(trim($strinput));
	}
	
	




#Function to get current browser information
function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
	$bname = 'Internet Explorer';
    $ub = "MSIE";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
   
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }
   
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
   
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
   
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
   
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 




#Returns the user's properly formatted Member Type
function format_usertype($usertype)
{
    switch ($usertype) {
        case "IP":
            return "Internal Personnel";
            break;
        
        case "QIB":
            return "Qualified Institutional Buyer";
            break;
        
        case "QC":
            return "Qualified Client";
            break;
        
        case "AC":
            return "Accredited Investor";
            break;

        default:
            return "Undefined Type";
            break;
    }
}



#Trim string to accesptable number of strings
function trimStr($str, $length)
{
   if(strlen($str) < $length)
   {
       return $str;
   }
   else 
   {
      return substr($str, 0, $length).".."; 
   }
}




# Function that encrypts the entered values
function encryptValue($val){
	$num = strlen($val);
	$numIndex = $num-1;
	$val1="";
		
	#Reverse the order of characters
	for($x=0;$x<strlen($val);$x++){
		$val1.= substr($val,$numIndex,1);
		$numIndex--;
	}
		
	#Encode the reversed value
	$val1 = base64_encode($val1);
	return $val1;
}


#Function that decrypts the entered values
function decryptValue($dval){
	#Decode value
	$dval = base64_decode($dval);
		
	$dnum = strlen($dval);
	$dnumIndex1 = $dnum-1;
	$dval1 = "";
		
	#Reverse the order of characters
	for($x=0;$x<strlen($dval);$x++){
		$dval1.= substr($dval,$dnumIndex1,1);
		$dnumIndex1--;
	}
	return $dval1;
}






# Returns the color you can assign to a row based on the passed counter
function get_row_color($counter, $no_of_steps, $row_borders='', $dark_color='#F0F0E1', $light_color='#FFFFFF', $color_only='N')
{
	if(($counter%$no_of_steps)==0) {
		if($row_borders == 'row_borders')
		{
			if($color_only == 'Y'){
				$rowclass = $light_color;
			} else {
				$rowclass = "background-color: ".$light_color."; border-bottom: 1px solid #AAAAAA;";
			}
		}
		else
		{
			if($color_only == 'Y'){
				$rowclass = $light_color;
			} else {
				$rowclass = "background-color: ".$light_color.";";
			}
		}
	} else {
		if($row_borders == 'row_borders')
		{
			if($color_only == 'Y'){
				$rowclass = $dark_color;
			} else {
				$rowclass = "background-color: ".$dark_color."; border-bottom: 1px solid #AAAAAA;";
			}
		} 
		else
		{
			if($color_only == 'Y'){
				$rowclass = $dark_color;
			} else {
				$rowclass = "background-color: ".$dark_color.";";
			}
		}
	}
	
	return $rowclass;
}




# Returns the whole string or part of a string depending on its size
function format_to_length($long_string, $length, $is_code='N')
{
	$final_string = $long_string;
	
	if(strlen($long_string) > $length)
	{
		$temp_string = substr($long_string, 0, $length);
		if($is_code == 'Y')
		{
			$opens = substr_count($temp_string, '<');
			$closes = substr_count($temp_string, '>');
			
			#Check if the opens and closes are the same
			#Look for the next close if they are different
			if($opens != $closes)
			{
				$close_parts = explode(">", $temp_string);
				$ok_string = implode(">", array_slice($close_parts, 0, (count($close_parts) -1)));
				$start_string_break = strlen($ok_string);
				$end_string_break = strpos($long_string, ">", $start_string_break);
				
				$final_string = substr($long_string, 0, $end_string_break).">... ";
			}
			#Just continue to show the appropriate string
			else
			{
				$final_string = $temp_string."... ";
			}
		}
		else
		{
			$final_string = $temp_string."... ";
		}
	}
	
	return $final_string;
}





# Removes the DEFINER=`username`@`host` clause from the CREATE VIEW Statement. 
# This causes problems when restoring MySQL views from an application since the user@host
# may not exist on the new system
# 
# Parameter passed is the string $filename The name of the file from which the definer information is to be removed
# Returns true if sucessful or a string with the error message that occured

function remove_MySQL_view_definer_information($filename) {
	# regular expression to remove the definer tag of the query
	# Remove DEFINER=`username`@`host`
	# explaination of REGEX /DEFINER=`([^`]+)`@`([^`]+)`/i
	# /DEFINER= - match starts with the literals DEFINER=
	# `([^`]+)` - match any characters between the ` - matches the `username`
	# @ - match the literal @
	# `([^`]+)` - match any characters between the ` - matches `%` and `hostname`
	# ` - ends with 
	$regex_pattern = "/DEFINER=`([^`]+)`@`([^`]+)`/i";
	$new_string = "";
	$file = fopen($filename, "r") or exit("Unable to open file!");
	$temp_file_name = "tmp_".time().".txt";
	
	$temp_file_handle = fopen($temp_file_name, "a+") or exit("Unable to open temporary file!");
	# Read the file one line at a time until the end is reached
	while(!feof($file)) {
		$new_string = preg_replace($regex_pattern, "", fgets($file));
		
		# Write the string without the definer to the temporary file.
		if (fwrite($temp_file_handle, $new_string) === FALSE) {
			return "Cannot write to temporary file ($temp_file_resource)";
		}
	}
	fclose($temp_file_handle);
	fclose($file);	
		
	// delete the backup file with definer
	#unlink($filename);
	// rename the temp file to the actual backup file
	if (rename($temp_file_name, $filename) === FALSE) {
		return "Cannot write to script file ($filename)";
	}
	return true;
}





# reverse strrchr() - PHP v4.0b3 and above
function reverse_strrchr($haystack, $needle)
{
    $pos = strrpos($haystack, $needle);
    if($pos === false) 
	{
        return $haystack;
    }
    return substr($haystack, 0, $pos + 1);
}





#Converts the XML object into an array
function convert_object_to_array($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();
   
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
   
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = convert_object_to_array($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}





function get_current_page_url() 
{
 	$pageURL = 'http';
 	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") 
	{
		$pageURL .= "s";
	}
 	$pageURL .= "://";
	
 	if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") 
	{
 	 $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 	} 
	else 
	{
 	 $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
 	return $pageURL;
}

function set_session_data($obj,$data = array())
{
	if(!empty($data))
	{
		foreach ($data as $key => $value) {
			# code...
			$obj->native_session->set($key,$value);
		}
	}
}


#Function to set the user's cookie settings
function set_system_cookie($cookie_name, $cookie_value, $expiry_date='')
{
	if($expiry_date != '')
	{
		$expiry_date = mktime (0, 0, 0, date('n',strtotime($expiry_date)), date('j',strtotime($expiry_date)), date('Y',strtotime($expiry_date)));
	}
	else
	{
		$expiry_date = mktime (0, 0, 0, 12, 31, date('Y',strtotime('next year')));
	}
	
	#Update the user cookie if its different from what was stored
	if(isset($_COOKIE[$cookie_name]) && isset($cookie_value)){
		#Expire and recreate the cookie
		setcookie($cookie_name,'',time()-3600);
		setcookie($cookie_name, $cookie_value, $expiry_date, '/');
	}
	else if(isset($cookie_value))
	{
		setcookie($cookie_name, $cookie_value, $expiry_date, '/');
	}
}



#Function to check if at least one item in the small array exists in a large array
function sys_in_array($small_array, $large_array)
{
	$inarray = FALSE;
	
	foreach($small_array AS $item)
	{
		if(in_array($item, $large_array))
		{
			$inarray = TRUE;
			break;
		}
	}
	
	return $inarray;
}


#function to return part of an array when being used in a list
function get_list_array_part($all_items, $start_index, $items_per_page)
{
	$logical_end = $start_index + $items_per_page;
	
	if(count($all_items) < $logical_end){
		$items_per_page = count($all_items) - $start_index;
	}
	
	return array_slice($all_items, $start_index, $items_per_page);
}




#function to sort a multi-dimensional array
//$order has to be either asc or desc
 function sortmulti ($array, $index, $order, $natsort=FALSE, $case_sensitive=FALSE) {
        if(is_array($array) && count($array)>0) {
            foreach(array_keys($array) as $key)
            $temp[$key]=$array[$key][$index];
            if(!$natsort) {
                if ($order=='asc')
                    asort($temp);
                else   
                    arsort($temp);
            }
            else
            {
                if ($case_sensitive===true)
                    natsort($temp);
                else
                    natcasesort($temp);
            if($order!='asc')
                $temp=array_reverse($temp,TRUE);
            }
            foreach(array_keys($temp) as $key)
                if (is_numeric($key))
                    $sorted[]=$array[$key];
                else   
                    $sorted[$key]=$array[$key];
            return $sorted;
        }
    return $sorted;
}






	#function to get month year pairs between two dates
	function get_month_year_keys($startdate, $enddate)
	{
		$month_yr_keys = array();
		$start_yr = date('Y', strtotime($startdate));
		$start_month = date('n', strtotime($startdate));
		$end_yr = date('Y', strtotime($enddate));
		$end_month = date('n', strtotime($enddate));
		
		#Get the years in the period
		for($i=$start_yr; $i<($end_yr+1); $i++)
		{
			if($i == $end_yr){
				$k_end = ($end_month+1);
			} else {
				$k_end = 13;
			}
			if($i == $start_yr){
				$k_start = $start_month;
			} else {
				$k_start = 1;
			}
			
			#Now get the months in the period
			for($k=$k_start; $k<$k_end; $k++){
				array_push($month_yr_keys, date('M-Y', strtotime($i.'-'.$k.'-1')));
			}
		}
		
		return $month_yr_keys;
	}
	
	
	



	
	#Function to fill empty spots in an array given all possible keys
	function fill_empty_array_spots($data, $allkeys, $fill_value=0)
	{
		$new_data = array();
		foreach($allkeys AS $key)
		{
			if(empty($data[$key])){
				$new_data[$key] = $fill_value;
			}
			else
			{
				$new_data[$key] = $data[$key];
			}
		}
		
		return $new_data;
	}
	



	#Function to get the enum field values given the table name and field name
	function get_enum_values($obj, $table, $column)
	{
		$enum_values = array();
		
		$result = $obj->db->query($obj->_query_reader->get_query_by_code('get_enum_values', array('tablename'=>$table, 'columnname'=>$column)));
		
		// If the query's successful
		if($result) 
		{ 
			$enum = $result->result_array();
			preg_match_all("/'([\w ]*)'/", $enum[0]['Type'], $values);
			$enum_values = $values[1];
		}
		
		return $enum_values;
	}
	




	#Search for given char or string of chars position and return it in an array
	function get_string_pos($needle, $haystack)
	{
   	 	if(strlen($needle) < strlen($haystack))
     	{
    		$seeks = array();
    		while($seek = strrpos($haystack, $needle))
    		{
        		array_push($seeks, $seek);
        		$haystack = substr($haystack, 0, $seek);
   			}
    		return $seeks;
		}
		else 
		{
			return array();
		}
	}



	#remove empty values
	function remove_empty_values($val)
	{
		if(!empty($val))
		{
			return $val;
		}
	}



	//Function to check whether a url is valid
	function is_valid_url($url)
	{
		if (!($url = @parse_url($url)))
		{
			return false;
		}
	 
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
		$url['path'] = (!empty($url['path'])) ? $url['path'] : '/';
		$url['path'] .= (isset($url['query'])) ? "?$url[query]" : '';
	 
		if (isset($url['host']) AND $url['host'] != @gethostbyname($url['host']))
		{
			if (PHP_VERSION >= 5)
			{
				$headers = @implode('', @get_headers("$url[scheme]://$url[host]:$url[port]$url[path]"));
			}
			else
			{
				if (!($fp = @fsockopen($url['host'], $url['port'], $errno, $errstr, 10)))
				{
					return false;
				}
				fputs($fp, "HEAD $url[path] HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
				$headers = fread($fp, 4096);
				fclose($fp);
			}
			return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
		}
		return false;
	}
	
	




#Function to find the difference between 2 strings	
function diff($old, $new)
{
	$maxlen = 0;
	foreach($old as $oindex => $ovalue)
	{
		$nkeys = array_keys($new, $ovalue);
		foreach($nkeys as $nindex){
			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
			if($matrix[$oindex][$nindex] > $maxlen)
			{
				$maxlen = $matrix[$oindex][$nindex];
				$omax = $oindex + 1 - $maxlen;
				$nmax = $nindex + 1 - $maxlen;
			}
		}	
	}
	
	if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
	return array_merge(
		diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
		array_slice($new, $nmax, $maxlen), diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}




#Function to highlight the difference between two strings
function html_diff($old, $new)
{
	$diff = diff(explode(' ', $old), explode(' ', $new));
	$ret = '';
	foreach($diff as $k)
	{
		if(is_array($k))
			$ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
			(!empty($k['i'])?"<span style='background:#B8EFDD;'>".implode(' ',$k['i'])."</span> ":'');
		else $ret .= $k . ' ';
	}
	return $ret;
}





#Function to show appropriate date on mail
function show_mail_date($date_string)
{
	#Show time if the message was sent the same date
	if(date('d-M-Y', strtotime($date_string)) == date('d-M-Y'))
	{
		return date('h:i A', strtotime($date_string));
	}
	#Show month and day if sent same year
	else if(date('Y', strtotime($date_string)) == date('Y'))
	{
		return date("M d", strtotime($date_string));
	}
	#Include year if the message was sent in a different year
	else
	{
		return date("M d, Y", strtotime($date_string));
	}
}



#Function to control access to a function based on the passed variables
function access_control($obj, $usertypes=array())
{
	#Check if the user has an active [remember me] cookie
	#If so, log them in remotely.
	$cookie_name = get_user_cookie_name($obj);
	if(!$obj->session->userdata('userid') && isset($_COOKIE[$cookie_name]))
	{
		#get the stored cookie value with the login details
		$login_details = explode("||", decryptValue($_COOKIE[$cookie_name]));
		$chk_user = $obj->Users->validate_login_user(array('username'=>$login_details[0], 'password'=>$login_details[1]));
		if(count($chk_user) > 0)
		{
			$obj->Users->populate_user_details($chk_user);
		}
		#TODO: THIS LINE IS FOR TESTING. REMOVE ON ACTIVE VERSION
		$obj->session->set_userdata('refreshed_session', "YES");
	}
		
	#By default, this function checks that the user is logged in
	if($obj->session->userdata('userid'))
	{
		if($obj->session->userdata('isadmin') == 'Y')
		{
			$usertype = 'admin';
		}
		else
		{
			$usertype = $obj->session->userdata('usertype');
		}
		
		#If logged in, check if the user is allowed to access the given page
		if(!empty($usertypes) && !in_array($usertype, $usertypes))
		{
			$qmsg = 'WARNING: You do not have the priviledges to access this function.';
		}
	}
	else
	{
		$qmsg = 'WARNING: You are not logged in. Please login to continue.';
	}
		
	#Redirect if the user has no access to the given page
	if(!empty($qmsg))
	{
		$obj->session->set_userdata('qmsg', $qmsg);
		redirect(base_url()."admin/logout/m/qmsg");
	}
}




#Function to return the maximum date in the given format
function get_max_date($row_array, $date_fields=array(), $default_value='N/A', $desired_format='d-M-Y h:i A')
{
	$date_array = array();
	
	foreach($date_fields AS $field)
	{
		#Add the date to those to be compared if it is not empty or NULL
		if(!empty($row_array[$field]))
		{
			array_push($date_array, date($desired_format, strtotime($row_array[$field])));
		}
	}
	
	if(!empty($date_array))
	{
		return max($date_array);
	}
	else
	{
		return $default_value;
	}
}



#Function to effectively neutralize quotes by converting them to their equivalent in HTML
function neutralize_quotes($old_string)
{
	$new_string = str_replace("'", "&rsquo;", $old_string);
	$new_string = str_replace('"', "&rdquo;", $new_string);
	
	return $new_string;
}

#Function to put paragraph breaks bettern than nl2br()
function nl2br2($string) {
   $string = str_replace(array("\r\n", "\r", "\n"), array("<BR>", "<BR>", "<BR>"), $string);
   return $string;
}


#Function to remove generic words from an array of words and return the rest
function eliminate_generic_words($obj, $allwords)
{
	$words_result = $obj->_query_reader->get_row_as_array('get_wordlist_by_type', array('wordtype'=>'generic'));
	$generic_words = array_merge(explode(',', $words_result['wordlist']), array(''));
	#Get only the non-generic words
	$unique_words = array_diff($allwords, $generic_words);
	
	return $unique_words;
}



#Function to add synonym words to list of words
function add_synonym_words($obj, $allwords)
{
	$word_cond = "";
	foreach($allwords AS $word)
	{
		if($word_cond != '')
		{
			$word_cond .= " OR ";
		}
		$word_cond .= " word = '".htmlentities(strtolower($word), ENT_QUOTES)."' ";
	}
	
	$parent_word_cond = "";
	foreach($allwords AS $word)
	{
		if($parent_word_cond != '')
		{
			$parent_word_cond .= " OR ";
		}
		$theword = htmlentities(strtolower($word), ENT_QUOTES);
		$parent_word_cond .= " (synonyms LIKE '".$theword."' OR synonyms LIKE '".$theword.",%' OR synonyms LIKE '%,".$theword.",%' OR synonyms LIKE '%,".$theword."') ";
	}
	
	$words_result = $obj->_query_reader->get_row_as_array('get_synonym_words', array('wordtype'=>"'specific'", 'wordcond'=>$word_cond));
	$parent_words_result = $obj->_query_reader->get_row_as_array('get_synonym_parents', array('wordtype'=>"'specific'", 'wordcond'=>$parent_word_cond));
	
	$words = array_unique(array_merge($allwords, explode(',', $words_result['wordlist'])));
	$words = array_unique(array_merge($words, explode(',', $parent_words_result['wordlist'])));

	#remove empty spaces and return the final word array
	return array_diff($words, array(''));
}



#Function to get the difference between two dates
#NOTE: That this provides an approximation - especially for the months and years as it 
#does not account for differences in month length or leap years.
function get_date_diff($start_date, $end_date, $diff_type)
{
	$actual_diff = 0;
	$diff = strtotime($end_date) - strtotime($start_date);
	
	if($diff_type == 'days')
	{
		$actual_diff = floor($diff / (60*60*24));
	}
	else if($diff_type == 'months')
	{
		$actual_diff = floor($diff / (30*60*60*24));
	}
	else if($diff_type == 'years')
	{
		$actual_diff = floor($diff / (365*30*60*60*24));
	}
	
	return $actual_diff;
}



	
#function to get the HTTP response given a url	
function http_response($url)
{
        #Add the http:// if not included
	    if(strtolower(substr($url, 0, 7)) != 'http://' && strtolower(substr($url, 0, 8)) != 'https://')
		{
			$url = 'http://'.$url;
		}
		
		// first do some quick sanity checks:
        if(!$url || !is_string($url))
		{
            return false;
        }
        // quick check url is roughly a valid http request: ( http://blah/... ) 
        if( ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url) )
		{
            return false;
        }
        // the next bit could be slow:
//         if(get_http_response_code_using_curl($url) != 200){
		if(get_http_response_code_using_getheaders($url) != 200){  // use this one if you cant use curl or curl is slow
            return false;
        }
        // all good!
        return true;
}


#function to get the HTTP response given a url using PHP's CURL function
function get_http_response_code_using_curl($url, $followredirects = true)
{
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if(! $url || ! is_string($url)){
            return false;
        }
        $ch = @curl_init($url);
        if($ch === false){
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER         ,true);    // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY         ,true);    // dont need body
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);    // catch output (do NOT print!)
        if($followredirects){
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS      ,10);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        }else{
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,false);
        }
//      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_USERAGENT      ,"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        @curl_exec($ch);
        if(@curl_errno($ch)){   // should be 0
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        @curl_close($ch);
        return $code;
}
	
	
#function to get the HTTP response given a url using PHP's get headers function	
function get_http_response_code_using_getheaders($url, $followredirects = true)
{
        // returns string responsecode, or false if no responsecode found in headers (or url does not exist)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if(! $url || ! is_string($url)){
            return false;
        }
        $headers = @get_headers($url);
        if($headers && is_array($headers)){
            if($followredirects){
                // we want the the last errorcode, reverse array so we start at the end:
                $headers = array_reverse($headers);
            }
            foreach($headers as $hline){
                // search for things like "HTTP/1.1 200 OK" , "HTTP/1.0 200 OK" , "HTTP/1.1 301 PERMANENTLY MOVED" , "HTTP/1.1 400 Not Found" , etc.
                // note that the exact syntax/version/output differs, so there is some string magic involved here
                if(preg_match('/^HTTP\/\S+\s+([1-9][0-9][0-9])\s+.*/', $hline, $matches) ){// "HTTP/*** ### ***"
                    $code = $matches[1];
                    return $code;
                }
            }
            // no HTTP/xxx found in headers:
            return false;
        }
        // no headers :
        return false;
}




function config_left_menu_item ($selected_menu, $this_menu, $link = '', $classes = '')
{
	$style_values = array();
	if(!empty($selected_menu) && $selected_menu == $this_menu) 
	{
		$style_values['open_link'] = $style_values['close_link'] = '';
		//$style_values['selected'] = 'id="li_'.$this_menu.'" class="selected '.$classes.'"'; 
		//hide current menu
		$style_values['selected'] = 'id="li_'.$this_menu.'" style="display:none" class="'.$classes.'"';
	}
	else
	{
		$style_values['open_link'] = '<a id="'.$this_menu.'"  href="javascript:void(0);">';
		$style_values['close_link'] = '</a>';
		$style_values['selected'] = 'id="li_'.$this_menu.'" class="'.$classes.'"';
	}
		
		return $style_values;
}

function GetTimeStamp($MySqlDate)
{
        /*
                Take a date in yyyy-mm-dd format and return it to the user
                in a PHP timestamp
                Robin 06/10/1999
        */
		#Remove time
		$datetime = explode(" ", $MySqlDate);
		
        $date_array = explode("-",$datetime[0]); // split the array
        
        $var_year = $date_array[0];
        $var_month = $date_array[1];
        $var_day = $date_array[2];

        #$var_s = $date_array[3];
        #$var_m = $date_array[4];
        #$var_h = $date_array[5];
		#print_r($date_array); exit();

        $var_timestamp = mktime(0,0,0,$var_month,$var_day,$var_year);
        return($var_timestamp); // return it to the user
}

#Function to deactivate an item in the database
function delete_row ($obj, $item, $key)
{
	$query = $obj->_query_reader->get_query_by_code('delete_item', array('item' => $item, 'id' => $key));
	$result = $obj->db->query($query);
	return $result;
}


#Function to activate an item in the database
function activate_row ($obj, $item, $key)
{
	$query = $obj->_query_reader->get_query_by_code('activate_item', array('item' => $item, 'id' => $key));
	$result = $obj->db->query($query);
	return $result;
}

#Gets a user's first,last and middle names
function get_school_user_fullname($obj, $userid)
{
	return $obj->_query_reader->get_row_as_array('get_school_user_fullname', array('id' => $userid));	
}


#Function to get available books
function num_of_available_copies($obj, $bookid)
{
	$transactions_arr = $obj->_query_reader->get_row_as_array('get_num_of_book_transactions', array('bookid' => $bookid));
}

//get the term title and name
function get_term_name_year($obj, $termid)
{
	$termdetails = $obj->_query_reader->get_row_as_array('get_term_name_year', array('id' => $termid));
	
	if(empty($termdetails))
	{
		$termdetails['term'] = "";
		$termdetails['year'] = "N/A";	
	}
	
	return $termdetails;
}

//get db object details
function get_db_object_details ($obj, $table, $objectid)
{
	return $obj->_query_reader->get_row_as_array('get_object_details', array('table' => $table, 'objectid' =>$objectid));
}

//get the class title
function get_class_title($obj, $classid)
{
	$classdetails = $obj->_query_reader->get_row_as_array('get_class_title', array('id' => $classid));
	
	if(empty($classdetails))
	{
		$classdetails['class'] = "";	
	}
	
	return $classdetails['class'];
}

//get the fee title and notes
function get_fee_lines($obj, $feeid)
{
	$feedetails = $obj->_query_reader->get_row_as_array('get_fee_lines', array('isactive' => 'Y', 'limittext' => '' , 'searchstring' => ' AND id ='.$feeid));	
	return $feedetails;
}

//get the staff user group details title and notes
function get_user_group_details($obj, $usergroupid)
{
	$groupdetails = $obj->_query_reader->get_row_as_array('search_staff_groups', array('isactive' => 'Y', 'limittext' => '' , 'searchstring' => ' AND id ='.$usergroupid));	
	return $groupdetails;
}

#function to get number of staff in a group
function get_group_members($obj, $group)
{
	$query = $obj->_query_reader->get_query_by_code('search_school_users', array('limittext' => '', 'searchstring' => ' AND usergroup ='.$group));
	
	return $obj->db->query($query);
	
}

#function to get a student's current class info
function current_class($obj, $studentid)
{
		$query = $obj->_query_reader->get_query_by_code('search_register', array('limittext' => '', 'searchstring' => ' student ='.$studentid));
		$result = $obj->db->query($query);
		$termid_str = '';
		foreach($result->result_array() AS $val)
		{
			if($termid_str != '')
			{
				$termid_str .= ','.$val['term'];
			}
			else
			{
				$termid_str .= $val['term'];
			}
		}
		
		#Get the latest term registered for by the student
		if(!empty($termid_str))
		$termdetails = $obj->_query_reader->get_row_as_array('search_terms_list', array('limittext' => '', 'searchstring' => ' AND id IN ('.$termid_str.')'));
		
		#Now get the correct details from the register
		if(!empty($termdetails))
		$get_class_info = $obj->_query_reader->get_row_as_array('search_register', array('limittext' => '', 'searchstring' => ' student = "'.$studentid.'" AND term = "'.$termdetails['id'].'"'));
		
		#Get the class title
		$current_class['class'] = (!empty($get_class_info))? get_class_title($obj, $get_class_info['class']) : '';
		$current_class['classid'] = (!empty($get_class_info))? $get_class_info['class'] : '';
		$current_class['term'] = (!empty($termdetails))? $termdetails['term'] : '';
		$current_class['year'] = (!empty($termdetails))? $termdetails['year'] : '';
		
		return $current_class;
}
	

#Function to remove empty indices from an array
function remove_empty_indices($array_obj)
{
	if(is_array($array_obj))
	{
		foreach($array_obj as $key => $value)
		{
			if(is_array($value))
			{
				$array_obj[$key] = remove_empty_indices($value);
			}
			else
			{
				if($value == '') unset($array_obj[$key]);
			}	
		}
	}
	
	return $array_obj;
}



#Function get a user's role(s)
function get_user_roles_text($obj, $user_id, $usergroups = array())
{
	$user_roles = $obj->db->get_where('roles', array('isactive'=>'Y', 'userid'=>$user_id))->result_array();
	$user_roles_arr_text = array();
	
	if(empty($usergroups)) $usergroups = $obj->db->get_where('usergroups', array('isactive'=>'Y'))->result_array();
	
	if(!empty($user_roles) && !empty($usergroups))
	{
		foreach($user_roles as $user_role)
		{
			foreach($usergroups as $usergroup)
			{
				if($user_role['groupid'] == $usergroup['id'])
				{
					array_push($user_roles_arr_text, $usergroup['groupname']);
					break;
				}
			}
		}
	}
	
	return $user_roles_arr_text;
}



function pad_string ($str, $str_size, $pad_str = '0')
{
	while ((strlen($str) % $str_size) !== 0)
	{
		$str = $pad_str . $str;
	}
	
	return $str;
}


function parenthesize (array $values, $wrapper = ")")
{
	$finalStr = "";
            
	foreach($values as $val)
	{
		if($finalStr == "")
        {
        	$finalStr = $val;
        }
        else
        {
        	$finalStr .= ", ".$val;
        }                
    }
    if($wrapper == "NONE")
	{
		return $finalStr;
	}
	else
	{
		return $wrapper.$finalStr.$wrapper;
	}               
}


function find_parent($array, $needle, $parent = null) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $pass = $parent;
            if (is_string($key)) {
                $pass = $key;
            }
            $found = find_parent($value, $needle, $pass);
            if ($found !== false) {
                return $found;
            }
        } else if ($key === 'id' && $value === $needle) {
            return $parent;
        }
    }

    return false;
}

function load_excel_sheet($file_url){

	$ci=& get_instance();
	$result = TRUE;
	$grade_list = array();
	$values = array();
	$counter = 0;

	$ci->load->library('PHPExcel');	
	$objPHPExcel = PHPExcel_IOFactory::load($file_url);

	return  $objPHPExcel;

}

#read excel data
function read_excel_data($file_url)
{ 
	$ci=& get_instance();

	$result = TRUE;
	$grade_list = array();
	$values = array();
	$counter = 0;
	
	$ci->load->library('PHPExcel');
	
	$objPHPExcel = PHPExcel_IOFactory::load($file_url);
	
	//get only the Cell Collection
	$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
	$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	$worksheet = $objPHPExcel->setActiveSheetIndex(0);
	
	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) 
	{
    	$arrayData[$worksheet->getTitle()] = $worksheet->toArray(NULL, true, true, true);
	}
	
	return $arrayData;
}

#generate pdf report
function report_to_pdf($obj, $html, $report_title = 'PPDA_report')
{	
	// Load library
	$obj->load->library('dompdf_gen');
	#$html = '<div><b>Hello everybody</b></div>';
	// Convert to PDF
	$obj->dompdf->load_html($html);
	$obj->dompdf->render();
	$obj->dompdf->stream($report_title . ".pdf", array("Attachment" => false));								
}





#remote search for providers validity from ROP 
function searchprovidervalidity($providernames)
{	
	  $ci=& get_instance();
      $ci->load->model('remoteapi_m', 'remote');       
      $data = $ci->remote->checkifsuspended($providernames); 
      return  $data;
}


#Function to return part of an array from start to finish given
function get_array_part($array, $start, $end)
{
	$arraypart = array();
	$count = 0;
	
	foreach($array AS $row){
		if($count >= $start && $count < $end){
			array_push($arraypart, $row);
		}
		
		$count++;
	}
	
	return $arraypart;
}



# Function to redirect a user from an iframe
function redirectFromIframe($url)
{
	echo "<script type='text/javascript'>window.top.location.href = '".$url."';</script>";exit;
}


# Format age for display indicator
function format_age($userAge, $return='style', $retirementAge = RETIREMENT_AGE)
{
	$format = "";
	
	if(($retirementAge - $userAge) < 4 && $userAge <= RETIREMENT_AGE)
	{
		$format = $return=='timeleft'? "<br>[".($retirementAge - $userAge)." yrs to AMAR]": "font-weight:bold;color: #FFD418;";
	}
	else if(($retirementAge - $userAge) < 0)
	{
		$format = $return=='timeleft'? "<br>[".($userAge - $retirementAge)." yrs past AMAR]": "font-weight:bold;color: #FF0000;";
	}
	
	return $format;
}



?>