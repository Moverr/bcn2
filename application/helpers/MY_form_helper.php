<?php
#*********************************************************************************
# Extends the CI_form helper class
#*********************************************************************************

	#sort the problem of dates
 function display_date($db_date)
 {
		$db_date = trim($db_date);	

		// convert_string_to_date($db_date);

		  $display = '';
		  switch ($db_date) {

	
	  case '0000-00-00':
            break;
        case '0000-00-00 00:00:00':
            break;
        case '00:00:00':
            break;
        case '1970-01-01':
            break;
        case ' 1970-01-01 ':
            break;
        case '1970-01-01 00:00:00':
            break;
        case '1970-01-01 00-00-00':
            break;


       case '0000/00/00':
            break;
        case '0000/00/00 00:00:00':
            break;
        case '00:00:00':
            break;
        case '1970/01/01':
            break;
        case ' 1970/01/01 ':
            break;
        case '1970/01/01 00:00:00':
            break;
        case '1970/01/01 00-00-00':
            break;



			default:
			  $display = date("d/m/Y", strtotime($db_date));
			  break;
		  }
		  return $display;
		} 
		
		
function display_time($format,$db_date)
{
$db_date = trim($db_date);	
 // convert_string_to_date($db_date);
  
  $display = '';
  switch ($db_date) {
    case '00':
    case '00:00':
    case '00:00:00':
    case '':
      break;
    default:
      $display = date($format, strtotime($db_date));
      break;
  }
  return $display;
} 


	
#Allows you to use Javascript functions when validating a form's inputs
function get_validation_javascript($validation_package_array)
{
	$count = 0;#
	$js_function_string = "return ";
	
	
	
	#Go through each array item picking the validation requirements and writing the appropriate
	#Javascript function
	foreach($validation_package_array AS $rule => $requirement)
	{	
		# For each field requirements, generate the appropriate javascript
		if($count > 0)
		{
			$js_function_string .= " && ";
		}
		
		# Checks whether required field is filled in
		if($requirement[2] == "required")
		{
			$js_function_string .= " checkEmpty('".$requirement[0]."', '".$requirement[1]."')";
		}
		# Checks whether a field is selected on the condition that another field is a given value
		elseif($requirement[2] == "conditionalrequire")
		{
			$js_function_string .= " checkConditionalEmpty('".$requirement[0]."', '".$requirement[1]."',  '".$requirement[3]."',  '".$requirement[4]."')";
		}
		# Checks whether a field is selected on the condition that another field is equal in value
		elseif($requirement[2] == "conditionalequate")
		{
			$js_function_string .= " checkConditionalEqual('".$requirement[0]."', '".$requirement[1]."',  '".$requirement[3]."')";
		}
		# Checks whether the file submitted in a field is the valid type expected
		elseif($requirement[2] == "filetypecheck")
		{
			$errormsg = str_replace('_VALIDEXTENSIONS_', implode(' or ', $requirement[3]), $requirement[1]);
			$valid_extenstions = format_extensions_to_str($requirement[3]);
			$js_function_string .= " isValidFileType('".$requirement[0]."', '".$valid_extenstions."', '".$errormsg."')";
		}
		# Checks whether the user wants to run a selected script on their database
		elseif($requirement[2] == "runscriptconfirmation")
		{
			$js_function_string .= " uploadBackupScriptConfirmation()";
		}
		# Checks whether the entered email is of a valid format
		elseif($requirement[2] == "validemail")
		{
			$js_function_string .= " isValidEmail('".$requirement[0]."', '".$requirement[1]."')";
		}
		# Checks whether the entered email is of a valid format
		elseif($requirement[2] == "validemail_many")
		{
			$js_function_string .= " isValidEmailMany('".$requirement[0]."', '".$requirement[1]."')";
		}
		# Checks whether the specified field is checked
		elseif($requirement[2] == "checkbox_required")
		{
			$js_function_string .= " isChecked('".$requirement[0]."', '".$requirement[1]."')";
		}
		
		$count++;
	}
	
	return $js_function_string.";";
}

# Function to return the valid extensions in a format that can be used by the file checking javascript
function format_extensions_to_str($extension_array)
{
	$ext_array = array();
	
	foreach($extension_array AS $ext)
	{
		# remove the beginning dot (.)
		array_push($ext_array, substr($ext, 1));
		# Add the option of upper case of the extension
		array_push($ext_array, strtoupper(substr($ext, 1)));
	}
	
	return implode(',', $ext_array);
}


#Function to transform a date from the page to MYSQL format
function change_date_from_page_to_MySQL_format($pagedate, $dateonly='no') 
{	
	if (trim($pagedate)== "") 
	{
		$mysqldate = "NULL";
	} 
	else 
	{
		if($dateonly == 'yes')
		{
			$mysqldate = date("Y-m-d", strtotime($pagedate));
		}
		else
		{
			$mysqldate = date("Y-m-d H:i:s", strtotime($pagedate));
		}
	}		
	return $mysqldate;	
}


#Function to change a data from the MYSQL format to the page date format
function change_MySQL_date_to_page_format($mysqldate, $dateonly='yes') {		
	if($mysqldate == NULL) {
		$pagedate = "";
	} else {
		if($dateonly == 'yes')
		{
			$pagedate = date("m/d/Y", strtotime($mysqldate));
		}
		else
		{
			$pagedate = date("m/d/Y H:i:s", strtotime($mysqldate));
		}
	}
	return $pagedate;	
}
	
# Returns the number of items to be put in each column depending on the number of items to be shown
function get_no_for_each_column($number_of_items)
{
	$first_column_rows = 0;
	$second_column_rows = 0;
	$half_the_items = floor($number_of_items/2); 
		
	# Add 1 to the first column if the number of triggers is odd
	if(($number_of_items%2)==1){
		$first_column_rows = $half_the_items + 1;
	} else {
		$first_column_rows = $half_the_items;
	}
	$second_column_rows = $half_the_items;
	
	return array('first_column'=>$first_column_rows, 'second_column'=>$second_column_rows);
}

#function to check borrower status

function check_borrower_status($obj, $borrower){
	$result = $obj->db->query($obj->Query_reader->get_query_by_code('check_borrower', array('borrower' => $borrower)));
	if($result->num_rows() > 0)
		return $result->num_rows();
	else
		return 0;
}


# Get the difference between two dates in days or years
function get_date_difference($startdate, $enddate, $type, $separator)
{
	$diff = 0;
	
	$date_parts1 = explode($separator, $startdate);
	$date_parts2 = explode($separator, $enddate);
	$start_date = gregoriantojd($date_parts1[0], $date_parts1[1], $date_parts1[2]);
	$end_date = gregoriantojd($date_parts2[0], $date_parts2[1], $date_parts2[2]);
	
	if($type == 'day')
	{
		$diff = $end_date - $start_date;
	}
	else if($type == 'year')
	{
		$diff_string = get_date_difference($startdate,$enddate, $separator);
		
		$diff = trim(substr($diff_string, 0, strpos($diff_string, ' and ')));
		#Show days if the difference is less than 1 month
		if($diff == '')
		{
			$diff = substr($diff_string, strpos($diff_string, ' and '));
		}
	}
	else if($type == 'normyear')
	{
		$diff = round(($end_date - $start_date)/365, 0);
	}
	
	return $diff;
}



#Gets the age difference
function get_age_difference($start_date,$end_date, $separator){
    
	list($start_month,$start_date,$start_year) = explode($separator, $start_date);
    list($current_month,$current_date,$current_year) = explode($separator, $end_date);
     $result = '';
 
    /** days of each month **/
 
    for($x=1 ; $x<=12 ; $x++){
 
        $dim[$x] = date('t',mktime(0,0,0,$x,1,date('Y')));
 
    }
 
    /** calculate differences **/
 
    $m = $current_month - $start_month;
    $d = $current_date - $start_date;
    $y = $current_year - $start_year;

    /** if the start day is ahead of the end day **/
 
    if($d < 0) {
 
        $today_day = $current_date + $dim[$current_month];
        $today_month = $current_month - 1;
        $d = $today_day - $start_date;
        $m = $today_month - $start_month;
        if(($today_month - $start_month) < 0) {
 
            $today_month += 12;
            $today_year = $current_year - 1;
            $m = $today_month - $start_month;
            $y = $today_year - $start_year;
 
        }
 
    }
 
    /** if start month is ahead of the end month **/
 
        if($m < 0) {
 
        $today_month = $current_month + 12;
        $today_year = $current_year - 1;
        $m = $today_month - $start_month;
        $y = $today_year - $start_year;
 
        }
 
    /** Calculate dates **/
 
    if($y < 0) {
 
        die("Start date entered is a future date.");
 
    } else {
 
        switch($y) {
 
            case 0 : $result .= ''; break;
            case 1 : $result .= $y.($m == 0 && $d == 0 ? ' Year' : ' Year'); break;
            default : $result .= $y.($m == 0 && $d == 0 ? ' Years' : ' Years');
 
        }
 
        switch($m) {
 
            case 0: $result .= ''; break;
            case 1: $result .= ($y == 0 && $d == 0 ? $m.' Month' : ($y == 0 && $d != 0 ? $m.' Month' : ($y != 0 && $d == 0 ? ' and '.$m.' Month' : ', '.$m.' Month'))); break;
            default: $result .= ($y == 0 && $d == 0 ? $m.' Months' : ($y == 0 && $d != 0 ? $m.' Months' : ($y != 0 && $d == 0 ? ' and '.$m.' Months' : ', '.$m.' Months'))); break;
 
        }
 
        switch($d) {
 
            case 0: $result .= ($m == 0 && $y == 0 ? 'Today' : ''); break;
            case 1: $result .= ($m == 0 && $y == 0 ? $d.' Day' : ($y != 0 || $m != 0 ? ' and '.$d.' Day' : '')); break;
            default: $result .= ($m == 0 && $y == 0 ? $d.' Days' : ($y != 0 || $m != 0 ? ' and '.$d.' Days' : ''));
 
        }
 
    }

    return $result;
 
}





# Goes through a row returned from a form escaping quotes and neutralising HTML insertions
function clean_form_data($formdata)
{
	$clean_data = array();
	
	foreach($formdata AS $key=>$value)
	{
		if(is_array($value))
		{
			foreach($value AS $sub_key=>$sub_value)
			{
				if(is_array($sub_value))
				{
					foreach($sub_value AS $sub_sub_key=>$sub_sub_value)
					{
						if(is_array($sub_sub_value))
						{
							foreach($sub_sub_value AS $sub_sub_sub_key=>$sub_sub_sub_value)
							{
								$clean_data[$key][$sub_key][$sub_sub_key][$sub_sub_sub_key] = htmlentities($sub_sub_sub_value, ENT_QUOTES);
							}
						}
						else
						{
							$clean_data[$key][$sub_key][$sub_sub_key] = htmlentities($sub_sub_value, ENT_QUOTES);
						}
					}
				}
				else
				{
					$clean_data[$key][$sub_key] = htmlentities($sub_value, ENT_QUOTES);
				}
			}
		}
		else
		{
			$clean_data[$key] = htmlentities($value, ENT_QUOTES);
		}
	}
	
	return $clean_data;
}


# Goes through the submitted post data and converts to a string ready for storage into the database
# up to 3 levels
function convert_post_data_to_string($formdata, $exclude_array=array())
{
	$form_string = '';
	if(!isset($formdata))
	{
		$formdata = array();
	}
	
	foreach($formdata AS $key=>$value)
	{
		#Handle values which are arrays
		if(is_array($value) && !in_array($key, $exclude_array))
		{
			$form_string .= '<>'.$key.'=';
			$count=0;
			foreach($value AS $sub_key => $sub_value)
			{
				#Handle arrays
				if(is_array($sub_value) && (!in_array($sub_key, $exclude_array) || $sub_key == 0))
				{
					$sub_value_str = '';
					foreach($sub_value AS $sub_sub_key => $sub_sub_value)
					{
						if(!in_array($sub_sub_key, $exclude_array) || $sub_sub_key == 0)
						{
							$sub_value_str .= $sub_sub_key.'_'.$sub_sub_value.',';
						}
					}
					$sub_value = trim($sub_value_str, ',');
				}
				else if(!in_array($sub_key, $exclude_array) || $sub_key == 0)
				{
					#Handle empty strings
					if(trim($sub_value) == '')
					{
						$sub_value = '_';
					}
				}
				
				
				if(!in_array($sub_key, $exclude_array) || $sub_key == 0){
					if($count != 0)
					{
						$form_string .= '**'.$sub_key.'|'.$sub_value;
					}
					else
					{
						$form_string .= $sub_key.'|'.$sub_value;
					}
					
					$count++;
				}
			
			}
		}
		else if(!is_array($value) && (!in_array($key, $exclude_array) || $key == 0))
		{
			#Handle empty strings
			if(trim($value) == '')
			{
				$value = '_';
			}
			
			if(!in_array($key, $exclude_array) || $key == 0)
			{
				$form_string .= '<>'.$key.'='.$value;
			}
		}
	}
	
	return trim($form_string, '<>');
}



# Recovers a string from the post data saved in the database and regenerates the $_POST array
function convert_string_to_post_data($post_string, $exclude_array=array(), $replace_array=array())
{
	$post_array = array();
	
	$post_array_level1 = explode('<>', $post_string);
	
	foreach($post_array_level1 AS $key_value)
	{
		$key_value_array = explode('=', $key_value);
		
		#Was an array
		if(isset($key_value_array[1]) && strpos($key_value_array[1], '|') !== FALSE)
		{
			$post_array[$key_value_array[0]] = array();
			$post_array_level2 = explode('**', $key_value_array[1]);
			
			foreach($post_array_level2 AS $sub_key_value)
			{
				$sub_key_value_array = explode('|', $sub_key_value);
				#Was an array
				if(strpos($sub_key_value_array[1], '_') !== FALSE && $sub_key_value_array[1] != '_' && $sub_key_value_array[0] != 'drillevent')
				{
					$post_array[$key_value_array[0]][$sub_key_value_array[0]] = array();
					$post_array_level3 = explode(',', $sub_key_value_array[1]);
					
					foreach($post_array_level3 AS $sub_sub_key_value)
					{
						$sub_sub_key_value_array = explode('_', $sub_sub_key_value);
						#If key is in the excluded array do not include it in the conversion results
						if(!in_array($key_value_array[0], $exclude_array) && !in_array($sub_key_value_array[0], $exclude_array) && !in_array($sub_sub_key_value_array[0], $exclude_array))
						{
							
							#If key requires replacing the value with that given, change it here
							if(array_key_exists($sub_sub_key_value_array[0], $replace_array))
							{
								$post_array[$key_value_array[0]][$sub_key_value_array[0]][$sub_sub_key_value_array[0]] = $replace_array[$sub_sub_key_value_array[0]];
							}
							else
							{
								$post_array[$key_value_array[0]][$sub_key_value_array[0]][$sub_sub_key_value_array[0]] = $sub_sub_key_value_array[1];
							}
						}
					}
				}
				else
				{
					#If was an empty string
					if($sub_key_value_array[1] == '_')
					{
						$sub_key_value_array[1] = '';
					}
				
					#If key is in the excluded array do not include it in the conversion results
					if(!in_array($key_value_array[0], $exclude_array) && !in_array($sub_key_value_array[0], $exclude_array))
					{
						#If key requires replacing the value with that given, change it here
						if(array_key_exists($sub_key_value_array[0], $replace_array))
						{
							$post_array[$key_value_array[0]][$sub_key_value_array[0]] = $replace_array[$sub_key_value_array[0]];
						}
						else
						{
							$post_array[$key_value_array[0]][$sub_key_value_array[0]] = $sub_key_value_array[1];
						}
					}
				}
			}
		}
		else
		{
			#If was an empty string
			if(isset($key_value_array[1]) && $key_value_array[1] == '_')
			{
				$key_value_array[1] = '';
			}
			
			#If key is in the excluded array do not include it in the conversion results
			if(!in_array($key_value_array[0], $exclude_array))
			{
				#If key requires replacing the value with that given, change it here
				if(isset($key_value_array[1]) && array_key_exists($key_value_array[1], $replace_array))
				{
					$post_array[$key_value_array[0]] = $replace_array[$key_value_array[1]];
				}
				else if(isset($key_value_array[0]) && isset($key_value_array[1]))
				{
					$post_array[$key_value_array[0]] = $key_value_array[1];
				}
			}
		}
		
	}
	
	return $post_array;
}


#Function converts basic data for array
function convert_basic_array($string, $delimitor, $separator)
{
	$the_array = array();
	$row = explode($delimitor, $string);
	foreach($row AS $item)
	{
		$the_value = htmlentities(trim(strstr($item, $separator), $separator));
		
		$check_array = explode('**',$the_value);
		if(in_array('drillevent', $check_array))
		{
			$check_array[array_search('drillevent',$check_array)] = 'drillevent=';
			$the_value = implode('**', $check_array);
		}
		
		if($the_value == '_')
		{
			$the_value = '';
		}
		
		$the_array[strstr($item, $separator, TRUE)] = $the_value;
	}
	
	return $the_array;
}




# Makes FALSE values empty strings in the URL data obtained
function clean_url_data($urldata)
{
	$new_urldata = array();
	foreach($urldata AS $key=>$value)
	{
		if($value === FALSE || trim($value) == '')
		{
			$new_urldata[$key] = '';
		}
		else
		{
			$new_urldata[$key] = $value;
		}
	}
	
	return $new_urldata;
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


 
# Picks out all non-zero data from a URl array to be passed to a form
function assign_to_data($urldata, $passed_defaults = array())
{
	$data_array = array();
	$default_field_values = array('Enter Time', 'Enter First Name', 'Enter Last Name');
	if(!empty($passed_defaults)){
		$default_field_values = array_unique(array_merge($default_field_values, $passed_defaults));
	}
	
	foreach($urldata AS $key=>$value)
	{
		if(in_array($value, $default_field_values)){
			$value = '_';
		}
		
		if($value !== FALSE && trim($value) != '' && !array_key_exists($value, $urldata))
		{
			if($value == '_'){
				$data_array[$key] = '';
			} else {
				$data_array[$key] = $value;
			}
		}
	}
	
	return $data_array;
}

# Returns a number with two decimal places and a comma after three places
function add_commas($number, $number_of_decimals)
{
	# Default to zero if the number is not set
	if(!isset($number) || $number == "" ||  $number <= 0)
	{
		$number = "0";
	} 
	
	return number_format($number, $number_of_decimals, '.', ',');
}




# Function to return the year drop down list given the passed variables
function get_year_combo($selectedyear, $range, $order, $direction, $focal_year = '')
{
	$option_string = '';
	# Choose direction of display
	if($direction == 'FUTURE')
	{
		$start_year = ($focal_year == '')? date('Y') : $focal_year; 
		$end_year = $start_year + $range;
	}
	else
	{
		$end_year = ($focal_year == '')? date('Y') : $focal_year; 
		$start_year = $end_year - $range;
	}
	
	if(trim($selectedyear) == '')
	{
		$option_string .= "<option value='' selected>- Year -</option>";
	}
	
	#Organize years based on preferred order
	if($order == 'DESC')
	{
		for($i = $end_year; $i>($start_year-1);$i--)
		{
			$option_string .= "<option value='".$i."'";
			if($selectedyear == $i)
			{
				$option_string .= " selected";
			}
			
			$option_string .= ">".$i."</option>";
		}
	}
	else
	{
		for($i = $start_year; $i<($end_year+1);$i++)
		{
			$option_string .= "<option value='".$i."'";
			if($selectedyear == $i)
			{
				$option_string .= " selected";
			}
			
			$option_string .= ">".$i."</option>";
		}
	}
	
	
	return $option_string;
}


# Function to get all months and organize them according to preferrance
function get_month_combo($selectedmonth, $order, $required)
{
	$allmonths = array(1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June", 7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December");
	$option_string = '';
	
	if($required == 'combo')
	{
		if(trim($selectedmonth) == '')
		{
			$option_string .= "<option value='' selected>- Month -</option>";
		}
	
		if($order == 'DESC')
		{
			for($i = 12; $i>0;$i--)
			{
				$option_string .= "<option value='".$i."'";
				if($selectedmonth == $i)
				{
					$option_string .= " selected";
				}
			
				$option_string .= ">".$allmonths[$i]."</option>";
			}
		}
		else
		{
			for($i = 1; $i<13;$i++)
			{
				$option_string .= "<option value='".$i."'";
				if($selectedmonth == $i)
				{
					$option_string .= " selected";
				}
			
				$option_string .= ">".$allmonths[$i]."</option>";
			}
		}
	}
	else if($required == 'monthname')
	{
		if(array_key_exists($selectedmonth, $allmonths))
		{
			$option_string = $allmonths[$selectedmonth];
		}
	}
	
	return $option_string;
}


#function to get the days in a given month
function get_day_combo($selectedday, $month, $year, $required)
{
	$option_string = '';
	#get last day of the month
	if(trim($month) != '' && trim($year) != '')
	{
		$lastday = date('d', strtotime('last day of '.$month.', '.$year));
	}
	else
	{
		$lastday = 31;
	}
	
	# Returning data for a drop down
	if($required == 'combo')
	{
		if(trim($selectedday) == '')
		{
			$option_string .= "<option value='' selected>- Day -</option>";
		}
	
		for($i=1; $i<($lastday+1); $i++)
		{
			$option_string .= "<option value='".$i."'";
			if($selectedday == $i)
			{
				$option_string .= " selected";
			}
			
			$option_string .= ">".$i."</option>";
		}
	} 
	else if($required == 'lastday')
	{
		$option_string = $lastday;
	}
	
	return $option_string;
}





#function to get the hours in a day
function get_hours_combo($selectedhour='', $required='24hour')
{
	$option_string = '';
	
	if($required == '24hour')
	{
		for($i=0; $i<24; $i++)
		{
			$option_string .= "<option value='".$i."'";
			if($selectedhour == $i)
			{
				$option_string .= " selected";
			}
			
			$option_string .= ">".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>";
		}
	}
	
	return $option_string;
}





#function to get the minutes for the hour
function get_mins_combo($selectedmins='', $step = 1)
{
	$option_string = '';
	
	for($i=0; $i<60; ($i = $i+$step))
	{
		$option_string .= "<option value='".$i."'";
		if($selectedmins == $i)
		{
			$option_string .= " selected";
		}
			
		$option_string .= ">".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>";
	}
	return $option_string;
}


#Function to pick all continents
function get_all_continents($obj = '')
{
	$continent_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_continents_list', array()));
	
	return $continent_result->result_array();
}



#Function to pick all countries
function get_all_countries($obj, $continent='')
{
	$country_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_countries_list', array('continent'=>$continent)));
	
	return $country_result->result_array();
}




# Returns an array with all the states
function get_all_states()
{
		$states = array(
			"Alabama" => "Alabama",
			"Alaska" => "Alaska",
			"Arizona" => "Arizona",
			"Arkansas" => "Arkansas",
			"California" => "California",
			"Colorado" => "Colorado",
			"Connecticut" => "Connecticut",
			"Delaware" => "Delaware",
			"District of Columbia" => "District of Columbia",
			"Florida" => "Florida",
			"Georgia" => "Georgia",
			"Hawaii" => "Hawaii",
			"Idaho" => "Idaho",
			"Illinois" => "Illinois",
			"Indiana" => "Indiana",
			"Iowa" => "Iowa",
			"Kansas" => "Kansas",
			"Kentucky" => "Kentucky",
			"Louisiana" => "Louisiana",
			"Maine" => "Maine",
			"Maryland" => "Maryland",
			"Massachusetts" => "Massachusetts",
			"Michigan" => "Michigan",
			"Minnesota" => "Minnesota",
			"Mississippi" => "Mississippi",
			"Missouri" => "Missouri",
			"Montana" => "Montana",
			"Nebraska" => "Nebraska",
			"Nevada" => "Nevada",
			"New Hampshire" => "New Hampshire",
			"New Jersey" => "New Jersey",
			"New Mexico" => "New Mexico",
			"New York" => "New York",
			"North Carolina" => "North Carolina",
			"North Dakota" => "North Dakota",
			"Ohio" => "Ohio",
			"Oklahoma" => "Oklahoma",
			"Oregon" => "Oregon",
			"Pennsylvania" => "Pennsylvania",
			"Rhode island" => "Rhode island",
			"South Carolina" => "South Carolina",
			"South Dakota" => "South Dakota",
			"Tennessee" => "Tennessee",
			"Texas" => "Texas",
			"Utah" => "Utah",
			"Vermont" => "Vermont",
			"Virgin Islands" => "Virgin Islands",
			"Virginia" => "Virginia",
			"Washington" => "Washington",
			"West Virginia" => "West Virginia",
			"Wisconsin" => "Wisconsin",
			"Wyoming" => "Wyoming",
			"Alberta" => "Alberta",
			"Nova Scotia" => "Nova Scotia",
			"British Columbia" => "British Columbia",
			"Ontario" => "Ontario",
			"Manitoba" => "Manitoba",
			"Prince Edward Island" => "Prince Edward Island",
			"New Brunswick" => "New Brunswick",
			"Quebec" => "Quebec",
			"Newfoundland" => "Newfoundland",
			"Saskatchewan" => "Saskatchewan",
			"Northwest Territories" => "Northwest Territories",
			"Yukon Territory" => "Yukon Territory",
			"Nunavut" => "Nunavut",
			"American Samoa" => "American Samoa",
			"Guam" => "Guam",
			"Marshall Islands" => "Marshall Islands",
			"Micronesia (Federated States of)" => "Micronesia (Federated States of)",
			"Palau" => "Palau",
			"Puerto Rico" => "Puerto Rico",
			"U.S. Minor Outlying Islands" => "U.S. Minor Outlying Islands",
			"Northern Mariana Islands" => "Northern Mariana Islands",
			"Armed Forces Africa" => "Armed Forces Africa",
			"Armed Forces Americas AA (except Canada)" => "Armed Forces Americas AA (except Canada)",
			"Armed Forces Canada" => "Armed Forces Canada",
			"Armed Forces Europe AE" => "Armed Forces Europe AE",
			"Armed Forces Middle East AE" => "Armed Forces Middle East AE",
			"Armed Forces Pacific AP" => "Armed Forces Pacific AP",
			"Foreign" => "Foreign",
			"Others Not Listed above" => "Others Not Listed above"
		);
	return $states;
}






# Returns all languages
function get_all_languages($obj = '')
{
	$lang_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_languages_list', array()));
	
	return $lang_result->result_array();
}


#Function to pick all specialities
function get_all_specialities($obj, $idlist=array())
{
	if(!empty($idlist))
	{
		$condition = " categorystamp IN ('".implode("','", $idlist)."') ";
	}
	else
	{
		$condition = " 1=1 ";
	}
	$speciality_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_speciality_list', array('condition'=>$condition)));
	return $speciality_result->result_array();
}




# Returns an array with all the basic colors
function get_all_colors()
{
	$colors = array(
		"000000" => "Black",
		"0000FF" => "Blue",
		"804000" => "Brown",
		"736F6E" => "Gray",
		"00FF00" => "Green",
		"FF8040" => "Orange",
		"FF00FF" => "Pink",
		"8E35EF" => "Purple",
		"FF0000" => "Red",
		"FFFF00" => "Yellow",
		"FFFFFF" => "White",
	);
	return $colors;
}





#Function to get the difference between two multi dimensional arrays [up to 4 levels]
# It is assumed all items of an array are one data type
function multi_array_diff($array1, $array2)
{
	$diff = array();
	
	#Do a foreach only if there are more than one levels of array below the current value (first value in array)
	if(contains_an_array($array1))#is_array(current($array1))
	{
		
		foreach($array1 AS $key_1=>$array1_1)
		{
			if(contains_an_array($array1_1))#is_array(current($array1_1))
			{
				#3 DIMENSIONAL ARRAYS 
				foreach($array1_1 AS $key_1_2=>$array1_1_2)
				{
					#echo "<br>KEY: ".$key_1_2." --- "; print_r($array1_1_2);
					if(array_key_exists($key_1, $array2) && array_key_exists($key_1_2, $array2[$key_1])){
						
						if(is_array($array1_1_2) && is_array($array2[$key_1][$key_1_2])){
							if(count(array_diff_assoc($array1_1_2, $array2[$key_1][$key_1_2])) != 0){
								$diff[$key_1][$key_1_2] = array_diff_assoc($array1_1_2, $array2[$key_1][$key_1_2]);
							}
						}
						else if(strcasecmp($array1_1_2, $array2[$key_1][$key_1_2]) != 0)
						{
							$diff[$key_1][$key_1_2] = $array1_1_2;
						}
					}
					else
					{
						$diff[$key_1][$key_1_2] = $array1_1_2;
					}
				}
				
			}
			#2 DIMENSIONAL ARRAYS
			else
			{
				#echo "<br>KEY: ".$key_1." --- "; print_r($array1_1);
				if(array_key_exists($key_1, $array2)){
					
					if(is_array($array1_1) && is_array($array2[$key_1])){
						if(count(array_diff($array1_1, $array2[$key_1])) != 0){
							$diff[$key_1] = array_diff($array1_1, $array2[$key_1]);
						}
					}
					else if(strcasecmp($array1_1, $array2[$key_1]) != 0)
					{
						$diff[$key_1] = $array1_1;
					}
				}
				else
				{
					$diff[$key_1] = $array1_1;
				}
			}
		}
	}
	#1 DIMENSIONAL ARRAYS
	else
	{
		$diff = array_diff($array1, $array2);
	}
	#echo "<br><br><br><br>";
	
	return $diff;
}





#Check if the given array contains an element which is an array
function contains_an_array($the_array, $desired_result='result')
{
	$returned_array = array();
	$result = FALSE;
	
	if(is_array($the_array))
	{
		foreach($the_array AS $key=>$element)
		{
			if(is_array($element))
			{
				$returned_array[$key] = $element;
				$result = TRUE;
			}
			else
			{
				$returned_array[$key] = array($element);
			}
		}
	}
	
	#Return the original if there is no array in the array
	if(!$result)
	{
		$returned_array = $the_array;
	}
	
	#Return appropriate result
	if($desired_result == 'result')
	{
		return $result;
	}
	else if($desired_result == 'array')
	{
		return $returned_array;
	}
}



function my_strstr($haystack, $needle, $before_needle=FALSE) 
{
 	//Find position of $needle or abort
 	if(($pos=strpos($haystack,$needle))===FALSE) return $haystack;

 	if($before_needle) return substr($haystack,0,$pos+strlen($needle));
	 else return substr($haystack,$pos);
}




#Function to remove empty items from the given array
function remove_empty_items($old_array, $mantain_keys=FALSE)
{
	$new_array = array();
	foreach($old_array AS $key=>$value)
	{
		if(trim($value) != ''){
			if($mantain_keys)
			{
				$new_array[$key] = $value;
			}
			else
			{
				array_push($new_array, $value);
			}
		}
	}
	return $new_array;
}



#Function to generate an automatic email string for reference of the receiver
function generate_email_tracking_string($obj)
{
	if($obj->session->userdata('userid'))
	{
		$tracking_id = $obj->session->userdata('userid')."-".strtotime('now');
	}
	else
	{
		$tracking_id = "SF-".strtotime('now');
	}
	
	return "Email Tracking ID: ".$tracking_id;
}

//function to set up the pagination urls depending on whether to reload a page or use ajax calls
function config_paginate_link($refresh_container, $link)
{
	return ($refresh_container == '')? 'href="'.$link.'"' : 'onclick="updateFieldLayer(\''.$link.'\', \'\', \'\', \''.$refresh_container.'\', \'\');" href="javascript:void(0)"';
}


function pagination($item_count, $limit, $cur_page, $link, $refresh_container = '')
{
       $page_count = ceil($item_count/$limit);
       $current_range = array(($cur_page-2 < 1 ? 1 : $cur_page-2), ($cur_page+2 > $page_count ? $page_count : $cur_page+2));
	   
       // First and Last pages
       $first_page = $cur_page > 3 ? '<li><a '.config_paginate_link($refresh_container, sprintf($link, '1')).'>1</a></li>'.($cur_page < 5 ? ', ' : ' ... ') : null;
       $last_page = $cur_page < $page_count-2 ? ($cur_page > $page_count-4 ? ', ' : ' ... ').'<li><a '.config_paginate_link($refresh_container, sprintf($link, $page_count)).'>'.$page_count.'</a></li>' : null;

       // Previous and next page
       $previous_page = $cur_page > 1 ? '<li><a class="prevnext" style="border-left:0px;"  href="'.sprintf($link, ($cur_page-1)).'"> «</a></li>' : null;# &nbsp;&nbsp; 
	   
       $next_page = $cur_page < $page_count ? '<li><a class="prevnext" '.config_paginate_link($refresh_container, sprintf($link, ($cur_page+1))).' ">» </a></li>' : null;# &nbsp;&nbsp; 
	   
       // Display pages that are in range
       for ($x=$current_range[0];$x <= $current_range[1]; ++$x)
               $pages[] = ($x == $cur_page ? '<li class="active"><a href="javascript:void(0)">'.$x.'</a></li>' : '<li><a '.config_paginate_link($refresh_container, sprintf($link, $x)).' >'.$x.'</a></li>');

       if ($page_count > 1)
               return "<ul>".$previous_page.$first_page.implode('', $pages).$last_page.$next_page."</ul>";
}



#A wrapping span to show a required field
function get_required_field_wrap($required_fields, $field_id, $field_side='start', $txtmsg='', $showdiv='N')
{
	
	if($showdiv == 'Y')
	{
		if($field_side == 'start'){
			return "<div class='required_fields' id='".$field_id."__fwrap' style='padding:5px;'>";
		}
		else if($field_side == 'end')
		{
			return "</div>";
		}
	}
	else
	{
		$start = "<table border='0' cellspacing='0' cellpadding='5'>";
		if($txtmsg != ''){
			$start .= "<tr><td  style='font-weight: bold; color: #FF0000; background-color:#FFFF99;'>".$txtmsg."</td></tr>";
		}
	
		$start .= "<tr><td style='font-weight: bold; color:#000000; background-color:#FFFF99;' nowrap>";
		$end = "</td></tr></table>";
	
		if(in_array($field_id, $required_fields)){
			if($field_side == 'start'){
				return $start;
			}
			else if($field_side == 'end')
			{
				return $end;
			}
			else
			{
				return '';
			}
		}
		else
		{
			return '';
		}
	}
}



#function to get part of a string
function reduce_string_by_chars($string, $max_chars)
{
	if(strlen($string) > $max_chars){
		$string = substr($string, 0, $max_chars)."..";
	}
	
	return $string;
}


#function to format field based on instructions of a string
function format_fields_instr($field_array, $row_data=array())
{
	$bool = FALSE;
	#assuming instructions are in the first array item
	switch($field_array[0]) 
	{
 		case 'UCFIRST': 
			$string = ucfirst($row_data[$field_array[1]]); 
			$bool = TRUE;
		break;
		
		case 'MONEY': 
			$string = '$'.addCommas($row_data[$field_array[1]]); 
			$bool = TRUE;
		break;
		
		case 'ADDCOMMAS': 
			$string = addCommas($row_data[$field_array[1]]); 
			$bool = TRUE;
		break;
		
		default:
			$string = '';
		break;
	}
	
	
	return array('bool'=>$bool, 'string'=>$string);
}

#Function to convert array values into one line string separating each value by the specified string
function stringify_array ($array_obj, $value_separator)
{
	if(is_array($array_obj))
	{
		foreach($array_obj as $val)
		{
			if(is_array($val))
			{
				$value_separator .= stringify_array($val, $value_separator);
			}
			else
			{
				$value_separator .= $val. "|";
			}
        	
		}
		return $value_separator;
	}
	else
	{
		return $value_separator = "ERROR : $array_obj is not an array";
	}
}

#function to validate the reference number()
function validate_ref_no($formdata,$required_fields = array(),$correctformat=array())
{
	print_r($correctformat); exit();
  	
	/*$empty_fields = array();
	$bool_result = TRUE;
	foreach($required_fields AS $required)
	{
		//compare fordata;
		//if($formdata)

		#array_push($empty_fields, $field_str[0]);
	} */
}

#Function to validate the passed form based on the entered data
function validate_form($formtype, $formdata, $required_fields=array())
{

	$empty_fields = array();
	$bool_result = TRUE;
			
	foreach($required_fields AS $required)
	{
		if(strpos($required, '*') !== FALSE){
			#This is a checkbox group
			if(strpos($required, 'CHECKBOXES') !== FALSE)
			{
				$field_str = explode('*', $required);
				if(empty($formdata[$field_str[0]])){
					array_push($empty_fields, $field_str[0]);
				}
			}
			#This is a combobox
			if(strpos($required, 'COMBOBOX') !== FALSE)
			{
				$field_str = explode('*', $required);
				if(count($formdata[$field_str[0]]) == 1){
					if(empty($formdata[$field_str[0]][0])){
						array_push($empty_fields, $field_str[0]);
					}
				}elseif(empty($formdata[$field_str[0]])){
					array_push($empty_fields, $field_str[0]);
				}
			}
			#This is a required row
			else if(strpos($required, 'ROW') !== FALSE)
			{
				$row_str = explode('*', $required);
				$field_array = explode('<>', $row_str[1]);
				$decision = FALSE;
						
				$rowcounter = 0;
				#Take one row field and use that to check the rest
				foreach($formdata[$field_array[0]] AS $col_field)
				{
					$row_decision = TRUE;
					foreach($field_array AS $row_field){
						if(empty($formdata[$row_field][$rowcounter])){
							$row_decision = FALSE;
						}
					}
							
					if($row_decision && !empty($col_field)){
						$decision = TRUE;
						break;
					}
							
					$rowcounter++;
				}
						
				if(!$decision){
					array_push($empty_fields, $field_array);
				}
			}
			#This is a required radio with other options
			else if(strpos($required, 'RADIO') !== FALSE)
			{
				$row_str = explode('*', $required);
						
				#The radio is not checked
				if(empty($formdata[$row_str[0]])){
					array_push($empty_fields, $row_str[0]);
				}
				#if the radio is checked, check the other required fields
				else
				{
					if($formdata[$row_str[0]] == 'Y'){
						$field_array = explode('<>', $row_str[1]);
						#Remove first RADIO field item which is not needed
						array_shift($field_array);
							
						foreach($field_array AS $radio_field)
						{
							if(empty($formdata[$radio_field])){
								array_push($empty_fields, $radio_field);
							}
						}
					}
				}
			}
			#This is ensuring that the fields specified are the same
			else if(strpos($required, 'SAME') !== FALSE)
			{
				$row_str = explode('*', $required);
				$field_array = explode('<>', $row_str[1]);
				
				if($formdata[$row_str[0]] != $formdata[$field_array[1]]){
					array_push($empty_fields, $row_str[0]);
				}
			}
			#This is ensuring that the email is the correct format
			else if(strpos($required, 'EMAILFORMAT') !== FALSE)
			{
				$row_str = explode('*', $required);
				
				if(!is_valid_email($formdata[$row_str[0]]))
				{
					array_push($empty_fields, $row_str[0]);
				}
			}
			
			#This is ensuring that the number is the correct format
			else if(strpos($required, 'NUMBER') !== FALSE)
			{
				$row_str = explode('*', $required);
				
				if(!is_numeric($formdata[$row_str[0]]))
				{
					array_push($empty_fields, $row_str[0]);
				}
			}
			
			#This is ensuring that the first field is less than the next field
			else if(strpos($required, 'LESSTHAN') !== FALSE)
			{
				$row_str = explode('*', $required);
				$field_array = explode('<>', $row_str[1]);
				
				if(!($formdata[$row_str[0]] < $formdata[$field_array[1]] || $formdata[$row_str[0]] == '' || $formdata[$field_array[1]] == '')){
					array_push($empty_fields, $row_str[0]);
				}
			}
			#This field should validate   code of the financial year :: 
		}
				
		#Is a plain text field or other value field
		else
		{
			if(!(!empty($formdata[$required]) || $formdata[$required] == '0')){
				array_push($empty_fields, $required);
			}
		}
	}
		
	
	
	if(empty($empty_fields)){
		$bool_result = TRUE;
	}else{
		$bool_result = FALSE;
	}
	
	return array('bool'=>$bool_result, 'requiredfields'=>$empty_fields);
		
}

#Function to validate borrower period
function validate_borrower_period($date1, $date2){
	$date1 = format_date2($date1);
	$date2 = format_date2($date2);
	if(strtotime($date2) <= strtotime($date1))
		return "Issuing date cannot be less or equal to return date";
	elseif(get_date_difference2($date1, $date2) > 3)
		return "Borrowing period must not exceed 3 days";
	else
		return "";
}

function get_date_difference2($date1, $date2){	
     $datediff = strtotime($date2) - strtotime($date1);
     return floor($datediff/(60*60*24));
}

function get_date_value($date, $index){
	$datetime = explode(" ", $date);
	$date_array = explode("-", $datetime[0]);
	return $date_array[$index];
}

#Function to get the display string from the passed array
function get_display_value($key, $array)
{
	if(!empty($array[$key]))
	{
		return $array[$key];
	}
	else
	{
		return "";
	}
}



#FUnction to get any tab information passed
function get_tab_data_if_any($data)
{
	if(!empty($data['b'])){
		$data['activetab'] = decryptValue($data['b']);
	}
	if(!empty($data['s'])){
		$data['currentlink'] = decryptValue($data['s']);
	}
	
	return $data;
}



#function to format dates provided from a form field list into a format fit for entering into the database
function format_dates_for_db($formdata, $field_list)
{
	$new_formdata = $formdata;
	
	foreach($field_list AS $key)
	{
		$field_key = explode('*', $key);
		if(array_key_exists($field_key[0], $formdata)){
			if(!empty($field_key[1]) && $field_key[1] == 'EXT'){
				$new_formdata[$field_key[0]] = date('Y-m-d H:i:s', strtotime($formdata[$field_key[0]]));
			}
			else
			{
				$new_formdata[$field_key[0]] = date('Y-m-d', strtotime($formdata[$field_key[0]]));
			}
		}
	}
	
	return $new_formdata;
}

#Function to get stocked Items

function get_stocked($obj, $id){
	$result_array = $obj->Query_reader->get_row_as_array('get_stocked',array('id' => $id));
	return $result_array['total'];
}

#Function to get stocked rentals

function get_stocked_rentals($obj, $id){
	$result_array = $obj->db->query($obj->Query_reader->get_query_by_code('get_stocked_rentals',array('id' => $id)));
	
	return $result_array->num_rows();
}

#Function to get borrowed rentals

function get_borrowed_rentals($obj, $id){
	$result_array = $obj->Query_reader->get_row_as_array('get_borrowed_rentals',array('id' => $id));
	return $result_array['total'];
}

#Function to get returned rentals

function get_stock_items($obj, $id, $val){
	$result_array = $obj->db->query($obj->Query_reader->get_query_by_code('get_library_stock_items',array('id' => $id, 'isavailable' => $val)));
	return $result_array->num_rows();
}

function get_all_stock_items($obj, $id){
	$result_array = $obj->db->query($obj->Query_reader->get_query_by_code('get_all_library_stock_items',array('id' => $id)));
	return $result_array->num_rows();
}

#Function to get sold Items

function get_sold($obj, $id){
	$result_array = $obj->Query_reader->get_row_as_array('get_sold',array('id' => $id));
	return $result_array['total'];
}

#Function to show the appropriate download logo
function get_doc_logo($file_url)
{
	$ext = strtolower(strrchr($file_url,"."));
	if($ext == '.pdf'){
		return "pdf_logo.png";
	}
	else if($ext == '.xls' || $ext == '.xlsx')
	{
		return "excel_logo.png";
	}
	else if($ext == '.doc' || $ext == '.docx')
	{
		return "doc_logo.png";
	}
	else
	{
		return "file_logo.png";
	}
}




#Function to fill in a blank array with data available from a post
function fill_available_data($blank_data, $post_array)
{
	$full_array = $blank_data;
	foreach($blank_data AS $key=>$value)
	{
		if(array_key_exists($key, $post_array)){
			$full_array[$key] = $post_array[$key];
		}
	}
	
	return $full_array;
}



#Function to insert the affiliate code in an article/email message
function insert_affiliate_code($code, $article_str)
{
	return str_replace("=CODE=", "/r/".$code, $article_str);
}




#Function to paginate a list given its query and other data
function paginate_list($obj, $data, $query_code, $variable_array=array(), $rows_per_page=NUM_OF_ROWS_PER_PAGE)
{
	#determine the page to show
	if(!empty($data['p'])){
		$data['current_list_page'] = $data['p'];
	} else {
		#If it is an array of results
		if(is_array($query_code))
		{
			$obj->session->set_userdata('search_total_results', count($query_code));
		}
		#If it is a real query
		else
		{
			if(empty($variable_array['limittext']))
			{
				$variable_array['limittext'] = '';
			}
			
			#echo $obj->Query_reader->get_query_by_code($query_code, $variable_array );
			#exit($query_code);
			 $list_result = $obj->db->query($obj->Query_reader->get_query_by_code($query_code, $variable_array ));
			$obj->session->set_userdata('search_total_results', $list_result->num_rows());
			
		   # $list_result = 100;
			#$obj->session->set_userdata('search_total_results', 100);
			
		}
		
		$data['current_list_page'] = 1;
	}
	
	$data['rows_per_page'] = $rows_per_page;
	$start = ($data['current_list_page']-1)*$rows_per_page;
	
	#If it is an array of results
	if(is_array($query_code))
	{
		$data['page_list'] = array_slice($query_code, $start, $rows_per_page);
	}
	else
	{
		$limittxt = " LIMIT ".$start." , ".$rows_per_page;
		$list_result = $obj->db->query($obj->Query_reader->get_query_by_code($query_code, array_merge($variable_array, array('limittext'=>$limittxt)) ));
		$data['page_list'] = $list_result->result_array();
	}
	
	return $data;
}




#Get settings for an image that has been reduced to certain constraints
function minimize_image($image_url, $min_height='', $min_width='')
{
	$img_settings = getimagesize($image_url);
	if($min_height != '' && $img_settings[1] > $min_height){
		$min_height = $min_height;
		$min_width = $img_settings[0]*($min_height/$img_settings[1]);
	}
	else if($min_width != '' && $img_settings[0] > $min_width){
		$min_height = $img_settings[1]*($min_width/$img_settings[0]);
		$min_width = $min_width; 
	}
	else
	{
		$min_height = $img_settings[1];
		$min_width = $img_settings[0];
	}
	
	return array('width'=>$min_width, 'height'=>$min_height, 'actualwidth'=>$img_settings[0], 'actualheight'=>$img_settings[1]);
}


#Function to minimize code to reduce instances of stealing it
function minimize_code($obj, $codetype='javascript')
{
	if($codetype == 'javascript')
	{
		$obj->load->library("jsmin");
	
		$combined_file_name = HOME_URL."js/combined.js"; 
		if (!MINIFY_JS) {
			// remove the combined JS file so that it can be regenerated 
			unlink($combined_file_name); 
		} 
		if (!file_exists($combined_file_name)) {
			$files = glob(HOME_URL."js/*.js");
			$js = "";
			
			foreach($files AS $file) 
			{
				if(strpos($file, 'easyui') == FALSE && strpos($file, '.min.') == FALSE && strpos($file, '.datepick.') == FALSE){
					if (MINIFY_JS) {
						$js .= JSMin::minify(file_get_contents($file));
					} else {
						$js .= file_get_contents($file);
					}
				}
			}
			file_put_contents($combined_file_name, $js);
		}  
		return "<script type='text/javascript' src='".base_url()."js/combined.js'></script>";
	
	} 
	else if($codetype == 'stylesheets')
	{
		
		$obj->load->library("xmlrpc");
		
		$obj->load->library("cssmin");
		
		$combined_file_name = HOME_URL."css/combined.css"; 
		if (!MINIFY_JS) {
			// remove the combined JS file so that it can be regenerated 
			unlink($combined_file_name); 
		}
		
		if (!file_exists($combined_file_name)) 
		{
			$files = glob(HOME_URL."css/*.css");
			$css = "";
		
					
			foreach($files as $file) 
			{
				if(strpos($file, 'easyui') == FALSE && strpos($file, '.min.') == FALSE && strpos($file, '.datepick.') == FALSE)
				{
					if (MINIFY_JS)
					{
						$css .= CssMin::minify(file_get_contents($file));
					} else {
						$css .= file_get_contents($file);
					}
				}
			}
			
			file_put_contents($combined_file_name, $css);
		} 
		
		return "<link href='".base_url()."css/combined.css' rel='stylesheet' type='text/css' />";
	}	
}



#Function to construct a custom dropdown
function custom_dropdown($list_name, $list_items, $below_line_items=array(), $selected='', $height='', $width='', $show_separator='Y')
{
	$drop_HTML = "";
	
	#Show the list items
	if(!empty($list_items) || !empty($below_line_items))
	{
		if(empty($selected))
		{
			$selected_string = " [Select] ";
			$selected_value = "";
		}
		else if(strpos($selected, '<>') !== FALSE)
		{
			$sel_value_array = explode('<>', $selected);
			$selected_string = $sel_value_array[1];
			$selected_value = $sel_value_array[0];
		}
		else
		{
			$selected_string = $selected;
			$selected_value = $selected;
		}
		
		if(empty($width) && !empty($selected_value))
		{
			$width = strlen($selected_value)*20;
		}
		else if(empty($width) && !empty($selected_string))
		{
			$width = strlen($selected_string)*20;
		}
		else if(empty($width))
		{
			$width = (!empty($list_items))? (strlen($list_name[0])*10) : (strlen($below_line_items[0])*10);
		}
		
		$drop_HTML .= "<style type='text/css'>
		#".$list_name." {
			width:".$width."px !important;
			height:30px !important;
		}
		</style>
		<div class='dropdown' style='display:inline-block;'>
			<select id='".$list_name."' class='dropdown'>";
	}
	
	#Show the default selected value if there is no selected value
	if((!empty($list_items) || !empty($below_line_items)) && empty($selected_value))
	{
		$drop_HTML .= "<option value='' selected='selected'>".$selected_string."</option>";
	}
	
	if(!empty($list_items))
	{
		foreach($list_items AS $item)
		{
			if(strpos($item, '<>') !== FALSE)
			{
				$item_array = explode('<>', $item);
				$drop_HTML .= "<option value='".$item_array[0]."'";
				
				if(!empty($selected_string) && !empty($selected_value) && $item_array[0] == $selected_value)
				{
					$drop_HTML .= " selected='selected'";
				}
				
				#If the link is javascript, then use onclick
				if(strpos($item_array[1], 'javascript:') !== FALSE)
				{
					$drop_HTML .= " onclick=\"".substr($item_array[1], 11)."\">".$item_array[0]."</option>";
				}
				else
				{
					$drop_HTML .= ">".$item_array[1]."</option>";
				}
			}
			else
			{
				$drop_HTML .= "<option value='".$item."'";
				
				if(!empty($selected_string) && !empty($selected_value) && $item == $selected_value)
				{
					$drop_HTML .= " selected='selected'";
				}
				
				$drop_HTML .= ">".$item."</option>";
			}
		}	
	}

		
	if(!empty($below_line_items))
	{
		if($show_separator == 'Y'){
			$drop_HTML .= "<option disabled='disabled'>----------------</option>";
		}
		
		foreach($below_line_items AS $bitem)
		{
			if(strpos($bitem, '<>') !== FALSE)
			{
				$bitem_array = explode('<>', $bitem);
				$drop_HTML .= "<option value='".$bitem_array[0]."'";
				
				if(!empty($selected_string) && !empty($selected_value) && $bitem_array[0] == $selected_value)
				{
					$drop_HTML .= " selected='selected'";
				}
				
				$drop_HTML .= ">".$bitem_array[1]."</option>";
			}
			else
			{
				$drop_HTML .= "<option value='".$bitem."'";
				
				if(!empty($selected_string) && !empty($selected_value) && $bitem == $selected_value)
				{
					$drop_HTML .= " selected='selected'";
				}
				
				$drop_HTML .= ">".$bitem."</option>";
			}
		}
	}	
				
   					
   	if(!empty($list_items) || !empty($below_line_items))
	{				
		$drop_HTML .= "</select></div>";
	}
	
	return $drop_HTML;
}


/*#Function to construct a custom dropdown
function custom_dropdown($list_name, $list_items, $below_line_items=array(), $selected='', $height='', $width='', $show_separator='Y')
{
	$drop_HTML = "";
	
	if(!empty($list_items)){
		if(empty($selected)){
			$selected_string = "[Select]";
		} else {
			if($selected[0] == '*'){
				$selected_string = substr($selected, 1);
				$selected_style='';
			} else {
				$selected_string = $selected;
				$selected_style=" style='color:#000000; font-size:13px; font-weight:normal;'";
			}
			
		}
		
		if($width != ''){
			$width_style = "width:".$width."px;";
		} else {
			$width_style = "";
		}
		
		$drop_HTML .= "<div id='navwrapper' style='display: inline-block;' onclick=\"document.getElementById('".$list_name."_listdiv').style.display=''\">
			<ul id='nav' class='floatright'>

				<li><a class='dmenu' href='javascript:void(0)' ".$selected_style." id='".$list_name."_selected'>".$selected_string."</a>
				<ul style='".$width_style."padding-bottom:0px;'>";
	
	#For controlling appearance
	$drop_HTML .= "<div id='".$list_name."_listdiv' class='droplistlayer'>";
	
	#Drop down items above the list separator
	if($height !=''){			
		$drop_HTML .= "<div style='height:".$height."px; 
						overflow: -moz-scrollbars-vertical;
						overflow-x: hidden;
						overflow-y: scroll;'>";
	}
	
	foreach($list_items AS $item){
		$item_array = explode('<>', $item);
		$drop_HTML .= format_list_item($item_array, $width_style);
	}
	
	if($height !=''){
		$drop_HTML .= "</div>";
	}
	
	
	#Drop down items below the separator
	if(!empty($below_line_items)){
		if($show_separator == 'Y'){
			$drop_HTML .= "<li class='menuseprator' style='".$width_style."'></li>";
		}
		#Put the items below the separator line
		foreach($below_line_items AS $item){
			$item_array = explode('<>', $item);
			$drop_HTML .= format_list_item($item_array, $width_style);
		}
	}			
	
	$drop_HTML .= "</div>";
				
	$drop_HTML .= "</ul>
			</li>
			</ul>
		</div>";
		
		if(strpos($selected, "[") !== FALSE){
			$selected = '';
		}
		
		#The hidden dropdown value storer
		$drop_HTML .= "<input name='".$list_name."' id='".$list_name."' type='hidden' value='".$selected."' />";
	}
	
	return $drop_HTML;
}*/



#Function to format a list item for a custom drop down
function format_list_item($item_array=array(), $width_style='')
{
	$list_string = '';
	
	if(count($item_array) > 0){
		if(count($item_array) == 1){
			$list_string .= "<li style='".$width_style."'><a href=\"javascript:void(0)\" style='white-space: nowrap;'>".$item_array[0]."</a></li>";
		}
		if(count($item_array) == 2){
			$list_string .= "<li style='".$width_style."'><a href=\"".$item_array[1]."\" style='white-space: nowrap;'>".$item_array[0]."</a></li>";
		}
		if(count($item_array) == 3){
			if(empty($item_array[1])){
				$item_array[1] = "javascript:void(0)";
			}
			$list_string .= "<li style='".$width_style."'><a href=\"".$item_array[1]."\" style='".$item_array[2]."'>".$item_array[0]."</a></li>";
		}
	}
	
	return $list_string;
}



#Function to generate a stamp for the searching user session
function get_search_stamp($obj)
{
	return strtotime('now')."_".$obj->input->ip_address();
}


#Function to generate the tip
function generate_tooltip_frame($portion='start')
{
	if($portion == 'start')
	{
		$tiphtml = "<table border='0' cellspacing='0' cellpadding='0'>
		<tr><td colspan='3' style='padding-left:12px;padding-bottom:0px;'><img src='".IMAGE_URL."layer_tip.png'></td></tr>
		
		<tr><td valign='bottom' style='padding-top:0px;'><img src='".IMAGE_URL."layer_top_left_corner.png'></td>
		<td style='background-image: url(".IMAGE_URL."top_border.png);background-repeat: repeat-x;background-position: center bottom;padding-top:0px;'><img src='".IMAGE_URL."spacer.gif' height='12' width='1'></td>
		<td valign='bottom' style='padding-top:0px;'><img src='".IMAGE_URL."layer_top_right_corner.png'></td></tr>
		
		<tr>
		<td style='background-image: url(".IMAGE_URL."right_border.png);background-repeat: repeat-y;background-position: right center;'></td>
		
		
		<td>";
	}
	
	else if($portion == 'end')
	{
		$tiphtml = "</td>
		<td style='background-image: url(".IMAGE_URL."left_border.png);background-repeat: repeat-y;background-position: left center;'></td>
		</tr>
		
		<tr><td valign='top'><img src='".IMAGE_URL."layer_bottom_left_corner.png'></td>
		<td style='background-image: url(".IMAGE_URL."bottom_border.png);background-repeat: repeat-x;background-position: center top;'>&nbsp;</td>
		<td valign='top'><img src='".IMAGE_URL."layer_bottom_right_corner.png'></td></tr>
		</table>";
	}
	
	return $tiphtml;
}



#Function to generate a tool tip
function generate_tooltip($caller_id, $layer_html, $tip_title)
{
	$tiphtml = "<div id='".$caller_id."_box' style='display:none;position:absolute;'>".generate_tooltip_frame()
	."<table style='background-color:#FFFFFF;'> 
	
		<tr>
		<td style='font-size: 18px;'><b>".$tip_title."</b></td>
		<td align='right' valign='top'><a href=\"javascript:hideTipDetails('".$caller_id."');\"><img src='".IMAGE_URL."delete_icon.png' border='0'></a></td>
		</tr>
		
		<tr>
		<td colspan='2'>".$layer_html."</td>
		</tr>
		</table>".generate_tooltip_frame('end')."</div>";
		
	return $tiphtml;
}






#function to generate a wiki
function generate_wiki_field($fieldname, $dimensions = array('cols'=>80, 'rows'=>20), $tools=array('bold', 'italic', 'underline', 'header', 'link','redirect','anchor','tip','picture','reference','character','bulletlist','numberedlist','indent','select'), $sectionname="description", $currentvalue="") {
	$wiki = "<script src='".base_url()."js/jquery.min.js' type='text/javascript'></script>
	<script src='".base_url()."js/mdbrain.js' type='text/javascript'></script>";
	$wiki .= "<table border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td style='padding-bottom:0px;'><table border='0' cellspacing='0' cellpadding='4' width='100%'>
      <tr>";
	  foreach($tools as $tool) {
		  switch($tool) {
			  case 'bold':
			  	$wiki .= "<td class='tool_bg_leftend' width='1%' title='Make selected text bold.' onclick=\"applyWikiRule('bold','".$fieldname."');\"><img src='".IMAGE_URL."bold_icon.png'/></td>
";
			  break;
			  case 'italic':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' title='Make selected text italic.' onclick=\"applyWikiRule('italic','".$fieldname."', '');\"><img src='".IMAGE_URL."itallic_icon.png'/></td>
";
			  break;
			  case 'underline':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' title='Underline selected text.' onclick=\"applyWikiRule('underline','".$fieldname."','');\"><img src='".IMAGE_URL."underline_icon.png'/></td>
";
			  break;
			  case 'header':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_header' name='wiki_tool_header' title='Apply a heading to the selected text.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addheader', '".$fieldname."', this);\"><img src='".IMAGE_URL."heading_icon.png'/></td>
";
			  break;
			  case 'link':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_link' name='wiki_tool_link' title='Add a link to the selected text.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addlink', '".$fieldname."', this);\"><img src='".IMAGE_URL."link_icon.png' border='0'/></td>
";
			  break;
			  case 'redirect':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_redirect' name='wiki_tool_redirect' title='Add a redirection link.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addredirect', '".$fieldname."', this);\"><img src='".IMAGE_URL."redirect_icon.png'/></td>
";
			  break;
			  case 'anchor':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_anchor' name='wiki_tool_anchor' title='Add a link anchor.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addanchor', '".$fieldname."', this);\"><img src='".IMAGE_URL."anchor_icon.png'/></td>
";
			  break;
			  case 'tip':
			  	$wiki .= " <td class='tool_bg_middle' width='1%' id='wiki_tool_tip' name='wiki_tool_tip' title='Add a tip to the selected text.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addtip', '".$fieldname."', this);\"><img src='".IMAGE_URL."tip_icon.png'/></td>
";
			  break;
			  case 'picture':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_image' name='wiki_tool_image' title='Add a picture.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addimage', '".$fieldname."', this);\"><img src='".IMAGE_URL."picture_icon.png'/></td>
";
			  break;
			  case 'reference':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_reference' name='wiki_tool_reference' title='Add a reference.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addreference', '".$fieldname."', this);\"><img src='".IMAGE_URL."reference_icon.png'/></td>
";
			  break;
			  case 'character':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_character' name='wiki_tool_character' title='Add a special character.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addcharacter', '".$fieldname."', this);\"><img src='".IMAGE_URL."special_char_icon.png'/></td>
";
			  break;
			  case 'bulletlist':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_bulletlist' name='wiki_tool_bulletlist' title='Add a bulleted list.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addbulletlist', '".$fieldname."', this);\"><img src='".IMAGE_URL."bullet_list_icon.png'/></td>
";
			  break;
			  case 'numberedlist':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_numberlist' name='wiki_tool_numberlist' title='Add a numbered list.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addnumberlist', '".$fieldname."', this);\"><img src='".IMAGE_URL."number_list_icon.png'/></td>
";
			  break;
			  case 'indent':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_indentation' name='wiki_tool_indentation' title='Add indentation.' onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addindentation', '".$fieldname."', this);\"><img src='".IMAGE_URL."indent_icon.png'/></td>
";
			  break;
			  case 'select':
			  	$wiki .= "<td class='tool_bg_middle' width='1%' id='wiki_tool_select' name='wiki_tool_select' title='Add Dropdown field.'onclick=\"showToolDetails('tooldetails_".$sectionname."', 'addselect', '".$fieldname."', this);\"><img src='".IMAGE_URL."select_icon.png'/></td>";
			  break;
		  }		  
	  }
	  $wiki .= "<td class='tool_bg_rightend' width='99%' title='Get help on updating this field.'><a href='javascript:openWindow(\"".base_url()."wiki/help/i/".encryptValue('wiki_use')."\")' class='bluelinks'>Help</a></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td style='padding-top:0px;padding-bottom:0px;'><div  id='tooldetails_".$sectionname."' class='tooldetails' style='display:none;'></div><input type='hidden' id='active_wiki_tool' name='active_wiki_tool' value=''></td>
  </tr>
  <tr>
    <td valign='top' align='left' style='padding-left:0px;'>
	<textarea name='".$fieldname."' id='".$fieldname."' class='textfield' cols='".$dimensions['cols']."' rows='".$dimensions['rows']."'>".$currentvalue."</textarea>
	</td>
  </tr>
</table>";
	return $wiki;
}



#Function to get the user's custom location
function get_custom_location($obj, $returntype = "string", $rpart='')
{
	if($returntype == "string")
	{
		if(!$obj->session->userdata('location_string') || ($obj->session->userdata('location_string') && $obj->session->userdata('resetlocation') && $obj->session->userdata('resetlocation') == 'Y'))
		{
			$location = $obj->Users->get_user_location();
			if(!empty($location['city']))
			{
				$slocation = ucwords(strtolower($location['city'].', '.$location['country']));
				
				$obj->session->set_userdata('location_string', $slocation);
			}
			else
			{
				$slocation = "Unknown Location";
			}
			$obj->session->unset_userdata(array('resetlocation'=>''));
			
			return $slocation;
		}
		else
		{
			return $obj->session->userdata('location_string');
		}
		
	}
	else
	{
		if(!$obj->session->userdata('location_array') || ($obj->session->userdata('location_array') && $obj->session->userdata('resetlocation') && $obj->session->userdata('resetlocation') == 'Y'))
		{
			$location = $obj->Users->get_user_location();
			if(!empty($location) && !empty($location['zipcode']) && trim($location['zipcode']) != '-')
			{
				$obj->session->set_userdata('location_array', $location);
			}
			else
			{
				$location = array('country'=>'United States', 'zipcode'=>'10001', 'city'=>'Manhattan', 'region'=>'New York');
			}
			$obj->session->unset_userdata(array('resetlocation'=>''));
			
			return $location;
		}
		else
		{
			return $obj->session->userdata('location_array');
		}
	}
}






#Checks if a disease is editable
function is_disease_editable($obj, $diseasestamp)
{
	$disease = $obj->Query_reader->get_row_as_array('get_diagnosis_by_stamp', array('stamp'=>$diseasestamp));
	if((!empty($disease['isfrozen']) && $disease['isfrozen'] == 'N') || ($obj->session->userdata('isadmin') && $obj->session->userdata('isadmin') == 'Y'))
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}



#Function to return the revision stamp
function get_revision_stamp($obj)
{
	if($obj->session->userdata('editsessionid')){
		$revisionstamp = $obj->session->userdata('editsessionid');
	}
	else
	{
		$revisionstamp = strtotime('now');
		$obj->session->set_userdata('editsessionid', $revisionstamp);
	}
	return $revisionstamp;
}



#Function to return a user identifier in current session
function get_user_identification($obj, $return_type)
{
	$ipaddress = $obj->input->ip_address();
	$value = ($obj->session->userdata($return_type))?$obj->session->userdata($return_type):$ipaddress;
	
	return $value;
}


#Function to pick all page sections
function get_all_page_sections($obj = '')
{
	$section_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_all_page_sections'));
	
	return $section_result->result_array();
}


#Function to get a user's cookie name
function get_user_cookie_name($obj = '')
{
	return replace_bad_chars(encryptValue("user_".$obj->input->ip_address()));
}



#Function to generate a system button
function generate_sys_button($btn_params=array())
{
	$btn_str = "";
	
	if(!empty($btn_params['btnaction']) && empty($btn_params['noloading']))
	{
		$btn_str .= "<div class='bluronclick'><div style='display:inline-block;'>";
	}
	else if(empty($btn_params['btnaction']))
	{
		$btn_params['btnaction'] = '';
	}
	
	$btn_str .= "<table border='0' cellspacing='0' cellpadding='0' class='btn' onclick=\"".$btn_params['btnaction']."\">
              <tr>
                <td class='btnleft'><img src='".base_url()."images/spacer.gif' width='5' height='5'/></td>
                <td class='btnmiddle' nowrap='nowrap'>".$btn_params['btntext']."</td>
                <td class='btnright'><img src='".base_url()."images/spacer.gif' width='5' height='5'/></td>
              </tr>
            </table>";
	
	if(!empty($btn_params['btnaction']) && empty($btn_params['noloading']))
	{
		$btn_str .= "</div></div>";
	}
		
	if(!empty($btn_params['btnid']))
	{	
     	$btn_value = (!empty($btn_params['btnvalue']))? $btn_params['btnvalue']: 'Submit';
		$btn_str .= "<div style='display:none;'>
                    <input name='".$btn_params['btnid']."' type='submit' id='".$btn_params['btnid']."' value='".$btn_value."' />
                </div>";
	}
	
	
	return $btn_str;
}



#Function to generate the menu HTML - UPTO 4 LEVELS
function generate_menu_list($obj, $list_name, $menu_value, $default_value = " - Select One - ")
{
	$menu = array();
	$menu_HTML = "<ul id='menu' class='clear'>
		<li><a href='javascript:void(0)'>".$default_value."</a>";
	
	#Get the first level menu item
	$first_level_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_menu_items', array('menufield'=>'firstlevel', 'parentfield'=>'menuname', 'parentvalue'=>$list_name)));
	$first_level = $first_level_result->result_array();
	
	if(!empty($first_level))
	{
		$menu['level1'] = $first_level;
		foreach($first_level AS $menu_item)
		{
			#Get the second level menu item
			$second_level_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_menu_items', array('menufield'=>'secondlevel', 'parentfield'=>'firstlevel', 'parentvalue'=>$menu_item['firstlevel'])));
			$second_level = $second_level_result->result_array();
			
			if(!empty($second_level))
			{
				$menu['level2'][$menu_item['firstlevel']] = $second_level;
				if(!empty($second_level))
				{
					foreach($second_level AS $menu2_item)
					{
						#Get the third level menu item
						$third_level_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_menu_items', array('menufield'=>'thirdlevel', 'parentfield'=>'secondlevel', 'parentvalue'=>$menu2_item['secondlevel'])));
						$third_level = $third_level_result->result_array();
						
						if(!empty($third_level))
						{
							$menu['level3'][$menu2_item['secondlevel']] = $third_level;
							foreach($third_level AS $menu3_item)
							{
								#Get the third level menu item
								$fourth_level_result = $obj->db->query($obj->Query_reader->get_query_by_code('get_menu_items', array('menufield'=>'fourthlevel', 'parentfield'=>'thirdlevel', 'parentvalue'=>$menu3_item['thirdlevel'])));
								$fourth_level = $fourth_level_result->result_array();
								
								if(!empty($fourth_level))
								{
									$menu['level4'][$menu3_item['thirdlevel']] = $fourth_level;
								}
							}
						}
					}
				}
			}
		}
	}
	
	
	#The menu HTML
	if(!empty($menu['level1']))
	{
		$menu_HTML .= "<ul>";
		foreach($menu['level1'] AS $item)
		{
			#echo "<BR><BR>== ";print_r($item);
			if(!empty($menu['level2'][$item['firstlevel']]))
			{
				$menu_HTML .= "<li><a href='javascript:void(0)'>".$item['firstlevel']."</a><ul>";
					
					#Second level items
					foreach($menu['level2'][$item['firstlevel']] AS $item2)
					{
						if(!empty($menu['level3'][$item2['secondlevel']]))
						{
							$menu_HTML .= "<li><a href='javascript:void(0)'>".$item2['secondlevel']."</a><ul>";
							
							#Third level items
							foreach($menu['level3'][$item2['secondlevel']] AS $item3)
							{
								if(!empty($menu['level4'][$item3['thirdlevel']]))
								{
									$menu_HTML .= "<li><a href='javascript:void(0)'>".$item3['thirdlevel']."</a><ul>";
							
									#Fourth level items
									foreach($menu['level4'][$item3['thirdlevel']] AS $item4)
									{
										$menu_HTML .= "<li><a href=\"javascript:selectAndRefresh('".$menu_value."_div', '".$item4['fourthlevel']."', '".$menu_value."')\">".$item4['fourthlevel']."</a></li>";
									}
								}
								else
								{
									$menu_HTML .= "<li><a href=\"javascript:selectAndRefresh('".$menu_value."_div', '".$menu_value."', '".$item3['thirdlevel']."')\">".$item3['thirdlevel']."</a></li>";
								}
							}
							
							$menu_HTML .= "</ul></li>";
						}
						else
						{
							$menu_HTML .= "<li><a href=\"javascript:selectAndRefresh('".$menu_value."_div', '".$menu_value."', '".$item2['secondlevel']."')\">".$item2['secondlevel']."</a></li>";
						}
					}
					
					$menu_HTML .= "</ul></li>";
			}
			else
			{
				$menu_HTML .= "<li><a href=\"javascript:selectAndRefresh('".$menu_value."_div', '".$menu_value."', '".$item['firstlevel']."')\">".$item['firstlevel']."</a></li>";
			}
		}
		
		$menu_HTML .= "</ul>";
	}
	
	$menu_HTML .= "</li></ul>";
	
	return $menu_HTML;
}




#Function to check whether the captcha field is satisfied
function is_valid_captcha($obj, $captcha)
{
	$result = FALSE;
	# First, delete old captchas
	$expiration = time()-7200; # Two hour limit
	$obj->db->query($obj->Query_reader->get_query_by_code('delete_old_captchas', array('oldesttime'=>$expiration)));     
		
	$captcha_data = $obj->Query_reader->get_row_as_array('check_for_captcha', array('theword'=>$captcha, 'thisip'=>$obj->input->ip_address(), 'oldestdate'=>$expiration));
	
	if($captcha_data['count'] > 0)
	{
		$result = TRUE;
	}		
	return $result;
}



#Function to check if a user needs suggestions
#CONDITIONS:
#- Must not have a confirmed disease already
#- Must have some diagnosis already
function needs_suggestions($obj)
{
	$decision = FALSE;
	
	if($obj->session->userdata('trigger_suggestions') && $obj->session->userdata('conc_searchresults'))
	{
		$trigger_suggestions = $obj->session->userdata('trigger_suggestions');
		#Check that there is no confirmed result in the display results before suggesting
		$display_results = $obj->session->userdata('conc_searchresults');
		$no_conf = "Y";
		foreach($display_results AS $row)
		{
			if($row['percentage'] == 'CONF'){
				$no_conf = "N";
				break;
			}
		}
		
		if($no_conf == 'Y' && !empty($trigger_suggestions))
		{
			$decision = TRUE;
		}
	}
	
	return $decision;
}



#Function to notify a user that the feature is not yet complete
function pending_feature_notification($instructions='withjs')
{
	$return_str = "";
	if(!empty($instructions) && $instructions == 'withjs')
	{
		$return_str .= "javascript:";
	}
	
	
	
	$return_str .= "alert('This feature is not yet enabled. \\nPlease check back later.')";
	
	return $return_str;
}




#Function to get the date difference string given the difference in hours
function get_date_diff_str($diff, $diff_type='hrs', $return_form='short')
{
	$diff_str = "";
	if($diff_type == 'hrs')
	{
		$difference_months = $diff/(24*30);
		$months = floor($diff/(24*30));
		$days = floor(($difference_months - $months)*30);
		$hours = floor(((($difference_months - $months)*30) - $days)*24);
		
		$end = "";
		if($months > 0)
		{
			#Just show greater than if you need the short form
			$diff_str .= ($return_form == 'short')? ">": "";
			
			$end = ($months == 1)? "": "s";
			$diff_str .= $months."month".$end." ";
		}
		if($days > 0 && (($return_form == 'short' && empty($months)) || $return_form != 'short'))
		{
			$end = ($days == 1)? "": "s";
			$diff_str .= $days."day".$end." ";
		}
		if($hours > 0 && (($return_form == 'short' && empty($months)) || $return_form != 'short'))
		{
			$end = ($hours == 1)? "": "s";
			$diff_str .= $hours."hr".$end." ";
		}
	}
	
	return $diff_str;
}



#Function to check if a file is an image
function is_file_an_image($filepath)
{
	$a = getimagesize($filepath);
	$image_type = $a[2];
	     
	if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
	{
	    return true;
	}
	return false;
}





#Function to get related permissions
function get_related_permissions($obj, $permissionid, $reason='check')
{
	$permission_details = $obj->Query_reader->get_row_as_array('get_permission_by_id', array('id'=>$permissionid));
	$list_str = '';
	
	if(!empty($permission_details['otherschecked']) && $reason=='check')
	{
		$permission_list = $obj->db->query($obj->Query_reader->get_query_by_code('get_related_permissions', array('relatedlist'=>"'".implode("','", explode(",", $permission_details['otherschecked']))."'" )));
	
		$permission_array = array();
		foreach($permission_list->result_array() AS $permission)
		{
			array_push($permission_array, $permission['pid']);
		}
		$list_str = implode(',', $permission_array);
	}
	
	
	if(!empty($permission_details['code']) && $reason=='uncheck')
	{
		$permission_list = $obj->db->query($obj->Query_reader->get_query_by_code('get_permissions_with_related_code', array('code'=>$permission_details['code'] )));
	
		$permission_array = array();
		foreach($permission_list->result_array() AS $permission)
		{
			array_push($permission_array, $permission['pid']);
		}
		$list_str = implode(',', $permission_array);
	}
	
	return $list_str;
}



?>