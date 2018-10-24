<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

# Function checks all values to see if they are all true and returns the value TRUE or FALSE
function get_decision($values_array)
{
	$decision = TRUE;
		
	foreach($values_array AS $value)
	{
		if(!$value)
		{
			$decision = FALSE;
			break;
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
		$query_string = $obj->Query_reader->get_query_by_code('get_options',array('table' => $table['tname'],'searchstring' => ''));
	else
	$query_string = $obj->Query_reader->get_query_by_code('get_options',array('table' => $table['tname'],'searchstring' => $table['searchstring']));
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
		$user_details = $obj->Query_reader->get_row_as_array('check_user_access_section', array('groupid'=>$obj->session->userdata('usergroup'), 'accesscode'=>$access_code));
	}
	else if($search_level == 'page')
	{
		$user_details = $obj->Query_reader->get_row_as_array('check_user_access', array('groupid'=>$obj->session->userdata('usergroup'), 'accesscode'=>$access_code));
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



#fucntion to replace the query value placeholders with the actual values
function replaceValuePlaceHolders($querystr, $value_array)
{
	foreach($value_array AS $key=>$value){
		$querystr = str_replace('_'.strtoupper($key).'_', $value, $querystr);
	}
		
	return $querystr;
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


#Format the give array columns into the display formats expected
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



# Returns the AJAX constructor to a page where needed
function get_AJAX_constructor($needsajax)
{
	$ajaxstring = "";
	
	if(isset($needsajax) && $needsajax)
	{
		$ajaxstring = "<script language=\"javascript\"  type=\"text/javascript\">".
							"var http = getHTTPObject();".
					  "</script>";
	}
	return $ajaxstring;
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


# Returns the passed message with the appropriate formating based on whether it is an error or not
function format_notice($msg)
{
	if(is_array($msg))
	{
		$result = $msg['obj']->db->query($msg['obj']->Query_reader->get_query_by_code('save_error_msg', array('msgcode'=>$msg['code'], 'details'=>$msg['details'], 'username'=>$msg['obj']->session->userdata('username'), 'ipaddress'=>$msg['obj']->input->ip_address() )));
	
		$msg = $msg['details'];
	}
    
	# Error message. look for "WARNING:" in the message
	if(strcasecmp(substr($msg, 0, 8), 'WARNING:') == 0)
	{
		$msg_string = "<div class='alert'>".
					  "<button class='close' data-dismiss='alert'>×</button>".
					  str_replace("WARNING:", "", $msg).
					  "</div>";
	}
	# Error message. look for "ERROR:" in the message
	else if(strcasecmp(substr($msg, 0, 6), 'ERROR:') == 0)
	{
		$msg_string = "<div class='alert alert-error'>".
					  "<button class='close' data-dismiss='alert'>×</button>".
					  str_replace("ERROR:", "", $msg).
					  "</div>";
	}
	
	# Information message. look for "INFO:" in the message
	else if(strcasecmp(substr($msg, 0, 5), 'INFO:') == 0)
	{
		$msg_string = "<div class='alert alert-info'>".
					  "<button class='close' data-dismiss='alert'>×</button>".
					  str_replace("INFO:", "", $msg).
					  "</div>";
	}
	
	#Normal Message
	else
	{
		$msg_string = "<div class='alert alert-success'>".
					  "<button class='close' data-dismiss='alert'>×</button>".
					  str_replace("SUCCESS:", "", $msg).
					  "</div>";
	}
	
	return $msg_string;
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








# Function to replace placeholders for bad characters in a text passed in URL with their actual characters
function restore_bad_chars($good_string)
{
	$bad_string = '';
	$bad_chars = array("'", "\"", "\\", "(", ")", "/", "<", ">", "!", "#", "@", "%", "&", "?", "$", ",", " ", ":", ";", "=");
	$replace_chars = array("_QUOTE_", "_DOUBLEQUOTE_", "_BACKSLASH_", "_OPENPARENTHESIS_", "_CLOSEPARENTHESIS_", "_FORWARDSLASH_", "_OPENCODE_", "_CLOSECODE_", "_EXCLAMATION_", "_HASH_", "_EACH_", "_PERCENT_", "_AND_", "_QUESTION_", "_DOLLAR_", "_COMMA_", "_SPACE_", "_FULLCOLON_", "_SEMICOLON_", "_EQUAL_");
	
	foreach($replace_chars AS $pos => $char_equivalent)
	{
		$bad_string = str_replace($char_equivalent, $bad_chars[$pos], $good_string);
		$good_string = $bad_string;
	}
	
	return $bad_string;
}

//Function to replace bad characters before they are passed in a URL
function replace_bad_chars($bad_string){
	$bad_chars = array("'", "\"", "\\", "(", ")", "/", "<", ">", "!", "#", "@", "%", "&", "?", "$", ",", " ", ":", ";", "=");
	$replace_chars = array("_QUOTE_", "_DOUBLEQUOTE_", "_BACKSLASH_", "_OPENPARENTHESIS_", "_CLOSEPARENTHESIS_", "_FORWARDSLASH_", "_OPENCODE_", "_CLOSECODE_", "_EXCLAMATION_", "_HASH_", "_EACH_", "_PERCENT_", "_AND_", "_QUESTION_", "_DOLLAR_", "_COMMA_", "_SPACE_", "_FULLCOLON_", "_SEMICOLON_", "_EQUAL_");
	$good_string = '';
	
	foreach($bad_chars AS $pos => $char){
		$good_string = str_replace($char, $replace_chars[$pos], $bad_string);
		$bad_string = $good_string;
	}
	
	return $good_string;
}


#Restore bar chars in an array
function restore_bad_chars_in_array($good_array)
{
	$bad_array = array();
	
	foreach($good_array AS $key=>$item)
	{
		$bad_array[$key] = restore_bad_chars($item);
	}
	
	return $bad_array;
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




 /**
	* Validate an email address. If the email address is not required, then an empty string will be an acceptable
	* value for the email address
	* 
	* @param String $email The email address to be validated
	* @param boolean $isRequired Whether the email is required or not. Defaults to TRUE
	*
	* @returns true if the email address has the correct email address format and that the domain exists.
	*/
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
	
	
	
	/**
	 * Validate a delimited list of email addresses
	 *
	 * @param String $emaillist A delimited list of email addresses
	 * @param boolean $isRequired Whether the email addresses are required
	 * @param String $delimiter The delimiter for the emails, defaults to a comma
	 * 
	 * @return TRUE if the emails in the list are valid, and FALSE if any of the emails in the list are invalid
	 */
	function is_valid_email_list($emaillist, $isRequired = true, $delimiter = ",") 
	{
		$list = explode($delimiter, $emaillist); 
		foreach ($list as $email) {
			if (!isValidEmail($email, $isRequired)) {
				return false; 
			} 
		}
		return true; 
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
		
		$result = $obj->db->query($obj->Query_reader->get_query_by_code('get_enum_values', array('tablename'=>$table, 'columnname'=>$column)));
		
		// If the query's successful
		if($result) 
		{ 
			$enum = $result->result_array();
			preg_match_all("/'([\w ]*)'/", $enum[0]['Type'], $values);
			$enum_values = $values[1];
		}
		
		return $enum_values;
	}
	
	
	
	


	#Function to get IP address
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
	
		return $ip;
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
	$words_result = $obj->Query_reader->get_row_as_array('get_wordlist_by_type', array('wordtype'=>'generic'));
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
	
	$words_result = $obj->Query_reader->get_row_as_array('get_synonym_words', array('wordtype'=>"'specific'", 'wordcond'=>$word_cond));
	$parent_words_result = $obj->Query_reader->get_row_as_array('get_synonym_parents', array('wordtype'=>"'specific'", 'wordcond'=>$parent_word_cond));
	
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
	$query = $obj->Query_reader->get_query_by_code('delete_item', array('item' => $item, 'id' => $key));
	$result = $obj->db->query($query);
	return $result;
}


#Function to activate an item in the database
function activate_row ($obj, $item, $key)
{
	$query = $obj->Query_reader->get_query_by_code('activate_item', array('item' => $item, 'id' => $key));
	$result = $obj->db->query($query);
	return $result;
}

#Gets a user's first,last and middle names
function get_school_user_fullname($obj, $userid)
{
	return $obj->Query_reader->get_row_as_array('get_school_user_fullname', array('id' => $userid));	
}


#Function to get available books
function num_of_available_copies($obj, $bookid)
{
	$transactions_arr = $obj->Query_reader->get_row_as_array('get_num_of_book_transactions', array('bookid' => $bookid));
}

//get the term title and name
function get_term_name_year($obj, $termid)
{
	$termdetails = $obj->Query_reader->get_row_as_array('get_term_name_year', array('id' => $termid));
	
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
	return $obj->Query_reader->get_row_as_array('get_object_details', array('table' => $table, 'objectid' =>$objectid));
}

//get the class title
function get_class_title($obj, $classid)
{
	$classdetails = $obj->Query_reader->get_row_as_array('get_class_title', array('id' => $classid));
	
	if(empty($classdetails))
	{
		$classdetails['class'] = "";	
	}
	
	return $classdetails['class'];
}

//get the fee title and notes
function get_fee_lines($obj, $feeid)
{
	$feedetails = $obj->Query_reader->get_row_as_array('get_fee_lines', array('isactive' => 'Y', 'limittext' => '' , 'searchstring' => ' AND id ='.$feeid));	
	return $feedetails;
}

//get the staff user group details title and notes
function get_user_group_details($obj, $usergroupid)
{
	$groupdetails = $obj->Query_reader->get_row_as_array('search_staff_groups', array('isactive' => 'Y', 'limittext' => '' , 'searchstring' => ' AND id ='.$usergroupid));	
	return $groupdetails;
}

#function to get number of staff in a group
function get_group_members($obj, $group)
{
	$query = $obj->Query_reader->get_query_by_code('search_school_users', array('limittext' => '', 'searchstring' => ' AND usergroup ='.$group));
	
	return $obj->db->query($query);
	
}

#function to get a student's current class info
function current_class($obj, $studentid)
{
		$query = $obj->Query_reader->get_query_by_code('search_register', array('limittext' => '', 'searchstring' => ' student ='.$studentid));
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
		$termdetails = $obj->Query_reader->get_row_as_array('search_terms_list', array('limittext' => '', 'searchstring' => ' AND id IN ('.$termid_str.')'));
		
		#Now get the correct details from the register
		if(!empty($termdetails))
		$get_class_info = $obj->Query_reader->get_row_as_array('search_register', array('limittext' => '', 'searchstring' => ' student = "'.$studentid.'" AND term = "'.$termdetails['id'].'"'));
		
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


#read excel data
function read_excel_data($file_url)
{ 
	$ci=& get_instance();

	$result = TRUE;
	$grade_list = array();
	$values = array();
	$counter = 0;
	
	#$ci->load->library('PHPExcel');
	
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


?>