<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 5/2/14
 * Time: 4:57 PM
 *
 * i cant begin to stress how important these functions are
 */

//generate seo friendly funtions
function seo_url($string)
{
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return strtolower($string);
}

//limit strings to only the first 100

function limit_words($string,$word_count)
{
    if (strlen($string) > $word_count) {

        // truncate string
        $stringCut = substr($string, 0, $word_count);

        // make sure it ends in a word so assassinate doesn't become ass...
        $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'';
    }
    return $string;
}


//remove dashes between words
function remove_dashes($string)
{
    return str_replace('-', ' ', $string);

}

//validate emails
function validate_mail($email)
{

    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

//get all files in a directory randomly
function get_all_directory_files($directory_path)
{

    $scanned_directory = array_diff(scandir($directory_path), array('..', '.'));



    return custom_shuffle($scanned_directory);
}

//to shuffle the elements
function custom_shuffle($my_array = array()) {
    $copy = array();
    while (count($my_array)) {
        // takes a rand array elements by its key
        $element = array_rand($my_array);
        // assign the array and its value to an another array
        $copy[$element] = $my_array[$element];
        //delete the element from source array
        unset($my_array[$element]);
    }
    return $copy;
}



//to clear forrm fields
function clear_form_fields()
{
    $str='';
    $str.='<script>
							  $(".form-horizontal")[0].reset();
							  </script>';
    return $str;
}





//check if ur on localhost
function check_localhost()
{
    if ( $_SERVER["SERVER_ADDR"] == '127.0.0.1' )
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}



//remove underscors from a string
function remove_underscore($string)
{
    return str_replace('_', ' ', $string);
}



function last_segment()
{
    $ci=& get_instance();
    $last = $ci->uri->total_segments();
    return $ci->uri->segment($last);
}

function strtotitle($title)
// Converts $title to Title Case, and returns the result.
{
// Our array of 'small words' which shouldn't be capitalised if
// they aren't the first word. Add your own words to taste.
    $smallwordsarray = array(
        'of','a','the','and','an','or','nor','but','is','if','then','else','when',
        'at','from','by','on','off','for','in','out','over','to','into','with'
    );

// Split the string into separate words
    $words = explode(' ', $title);

    foreach ($words as $key => $word)
    {
// If this word is the first, or it's not one of our small words, capitalise it
// with ucwords().
        if ($key == 0 or !in_array($word, $smallwordsarray))
            $words[$key] = ucwords($word);
    }

// Join the words back into a string
    $newtitle = implode(' ', $words);

    return $newtitle;
}

function jquery_redirect($url)
{
    ?>
    <script>

        var delay = 2000; //Your delay in milliseconds
        var timer = document.getElementById("timer");
        timer.innerHTML = "This page will redirect shortly seconds.";

        setTimeout(function () {
            window.location.replace("<?=$url?>");
        }, delay);
    </script>
<?php
}


//print an array
function print_array($array) {
    print '<pre>';
    print_r($array);
    print '</pre>';
}


function jquery_clear_fields()
{
    ?>
    <script>
        $(".form-horizontal")[0].reset();
        $(".form")[0].reset();
    </script>
<?php
}

function jquery_alert($alert_message)
{
    ?>
    <script>
        // similar behavior as an HTTP redirect
        alert('<?=$alert_message?>')
    </script>
<?php
}


function check_live_server()
{
    $whitelist = array(
        '127.0.0.1',
        '::1'
    );

    if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist))
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}



function video_image($url){
    $image_url = parse_url($url);
    if($image_url['host'] == 'www.youtube.com' ||
        $image_url['host'] == 'youtube.com'){
        $array = explode("&", $image_url['query']);
        return "http://img.youtube.com/vi/".substr($array[0], 2)."/0.jpg";
    }else if($image_url['host'] == 'www.youtu.be' ||
        $image_url['host'] == 'youtu.be'){
        $array = explode("/", $image_url['path']);
        return "http://img.youtube.com/vi/".$array[1]."/0.jpg";
    }else if($image_url['host'] == 'www.vimeo.com' ||
        $image_url['host'] == 'vimeo.com'){
        $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".
            substr($image_url['path'], 1).".php"));
        return $hash[0]["thumbnail_medium"];
    }
}

function age_from_dob($dob){
    $dob = strtotime($dob);
    $y = date('Y', $dob);
    if (($m = (date('m') - date('m', $dob))) < 0) {
        $y++;
    } elseif ($m == 0 && date('d') - date('d', $dob) < 0) {
        $y++;
    }
    return date('Y') - $y;
}




function make_unique_id()
{
    return uniqid();
}



function unzip_file($location,$newLocation)
{
    if(exec("unzip $location",$arr)){
        mkdir($newLocation);
        for($i = 1;$i< count($arr);$i++){
            $file = trim(preg_replace("~inflating: ~","",$arr[$i]));
            copy($location.'/'.$file,$newLocation.'/'.$file);
            unlink($location.'/'.$file);
        }
        return TRUE;
    }else{
        return FALSE;
    }
}

function csv_to_array($myString)
{
    return explode(',', $myString);
}

function jquery_redirect_with_delay($url, $seconds)
{
    ?>
    <script>
        var delay = '<?=($seconds*1000)?>'; //Your delay in milliseconds

        setTimeout(function(){ window.location = '<?=$url?>'; }, delay);
    </script>
<?php
}



function youtube_validator($url)
{
    $rx = '~
    ^(?:https?://)?              # Optional protocol
     (?:www\.)?                  # Optional subdomain
     (?:youtube\.com|youtu\.be)  # Mandatory domain name
     /watch\?v=([^&]+)           # URI with video id as capture group 1
     ~x';

    $has_match = preg_match($rx, $url, $matches);
}




//check if an image exists
function check_image_existance($path,$image_name)
{
    //buld the url
    $image_url=$path.$image_name;
    if (file_exists($image_url) !== false) {
        return true;
    }
}

//delete a file
function delete_image($path,$image_name)
{
    //images to delete
    $items=array(get_thumbnail($image_name),$image_name);

    //delete only if exists
    foreach($items as $item)
    {
        if(check_image_existance($path,$item))
        {
            unlink($path.$item);
        }
    }

}

function serialize_array(&$array, $root = '$root', $depth = 0)
{
    $items = array();

    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            serialize_array($value, $root . '[\'' . $key . '\']', $depth + 1);
        } else {
            $items[$key] = $value;
        }
    }

    if (count($items) > 0) {
        echo $root . ' = array(';

        $prefix = '';
        foreach ($items as $key => &$value) {
            echo $prefix . '\'' . $key . '\' => \'' . addslashes($value) . '\'';
            $prefix = ', ';
        }

        echo ');' . "\n";
    }
}




if (!function_exists('pipes_to_array')) {
    function pipes_to_array($piped_string)
    {
        //do only if a string is passed
        if ($piped_string) {
            $tags = explode('|', $piped_string);
            $array_values = array();

            foreach ($tags as $tag) {
                //save only tags with values
                if ($tags <> '' && !in_array($tag, $array_values)) {
                    $array_values[] = $tag;
                }
            }

            return $array_values;
        } else {
            return NULL;
        }

    }
}

/**
 * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
 * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
 */
function array_to_csv(array &$fields, $delimiter = ',', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false)
{
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        if ($field === null && $nullToMysqlNull) {
            $output[] = 'NULL';
            continue;
        }

        // Enclose fields containing $delimiter, $enclosure or whitespace
        if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
            $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
        } else {
            $output[] = $field;
        }
    }

    return implode($delimiter, $output);
}




function delete_all_files_in_directory($path)
{
    $files = glob($path); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file))
            unlink($file); // delete file
    }
}

function delete_files_in_directory_v2($target)
{
    if (is_dir($target)) {
        $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

        foreach ($files as $file) {
            delete_files($file);
        }

        rmdir($target);
    } elseif (is_file($target)) {
        unlink($target);
    }
}

function str_rand($length = 8, $output = 'alphanum')
{
    // Possible seeds
    $outputs['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
    $outputs['numeric'] = '0123456789';
    $outputs['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
    $outputs['hexadec'] = '0123456789abcdef';

    // Choose seed
    if (isset($outputs[$output])) {
        $output = $outputs[$output];
    }

    // Seed generator
    list($usec, $sec) = explode(' ', microtime());
    $seed = (float)$sec + ((float)$usec * 100000);
    mt_srand($seed);

    // Generate
    $str = '';
    $output_count = strlen($output);
    for ($i = 0; $length > $i; $i++) {
        $str .= $output{mt_rand(0, $output_count - 1)};
    }

    return $str;
}

function is_image($filepath)
{
    if (@!is_array(getimagesize($filepath))) {
        $image = false;
    } else {
        $image = true;
    }

    return $image;
}


function get_gmail($username, $password)
{
    $url = "https://mail.google.com/mail/feed/atom";

    $c = curl_init();

    $options = array(
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "$username:$password",
        CURLOPT_SSLVERSION => 3,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)",
        CURLOPT_URL => $url
    );

    curl_setopt_array($c, $options);
    $output = curl_exec($c);

    return $output;
}

#Method to go to previous page
function goback()
{
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}


//gets the data from a URL
function get_url($url)
{
    $ch = curl_init();

    if ($ch === false) {
        die('Failed to create curl object');
    }

    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * @brief
 * Get a list of timezones available for use in the application
 */
function get_timezones()
{
    $o = array();
    $t = timezone_identifiers_list();

    foreach ($t as $a) {
        $t = '';

        //Get the time difference
        $zone = new DateTimeZone($a);
        $seconds = $zone->getOffset(new DateTime("now", $zone));
        $hours = sprintf("%+02d", intval($seconds / 3600));
        $minutes = sprintf("%02d", ($seconds % 3600) / 60);

        $t = $a . "  [ $hours:$minutes ]";
        $o[$a] = $t;
    }
    ksort($o);

    return $o;
}


#Function to backup database to a zip file
function backup_database($username, $password, $db, $host, $directory = '')
{
    //delete the old folder if it exists
    if (!$directory) {
        $directory = 'backup';
    }



    if (!file_exists($directory)) {

        mkdir($directory, 0777, true);
    }
    //todo automatically create backup folder if it does not exist
    $suffix = time();
    #Execute the command to create backup sql file
    exec("mysqldump --user={$username} --password={$password} --quick --add-drop-table --add-locks --extended-insert --lock-tables --all{$db} > $directory/backup.sql");
    //exec("mysqldump --user={$username} --password={$password} --host={$host} {$db} > $directory/backup.sql");

    #Now zip that file
    $zip = new ZipArchive();
    $filename = $directory . "/backup-$suffix.zip";
    if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
        exit("cannot open <$filename>n");
    }
    $zip->addFile($directory . "/backup.sql", "backup.sql");
    $zip->close();
    #Now delete the .sql file without any warning
    @unlink($directory . "/backup.sql");
    #Return the path to the zip backup file
    return $directory . "/backup-$suffix.zip";
}

function set_timeout_limit($time_in_seconds = 600)
{
    set_time_limit($time_in_seconds);// 600 seconds = 10 minutes
}

function create_folder($folder_name)
{

    if (!file_exists($folder_name)) {
        $result = mkdir($folder_name, 0777);
    }
    if ($result) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function monthDropdown($name = "month", $selected = null)
{
    $dd = '<select name="' . $name . '" id="' . $name . '">';

    /*** the current month ***/
    $selected = is_null($selected) ? date('n', time()) : $selected;

    for ($i = 1; $i <= 12; $i++) {
        $dd .= '<option value="' . $i . '"';
        if ($i == $selected) {
            $dd .= ' selected';
        }
        /*** get the month ***/
        $mon = date("F", mktime(0, 0, 0, $i + 1, 0, 0, 0));
        $dd .= '>' . $mon . '</option>';
    }
    $dd .= '</select>';
    return $dd;
}


function months_array()
{
    $months = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
    return $months;
}


//function to get thumbnail from photo_name
function get_thumbnail($image_name)
{
    if ($image_name) {
        $pieces = explode('.', $image_name);

        return $pieces[0] . '_thumb.' . $pieces[1];
    }

}


/* creates a compressed zip file */
function create_zip($files = array(), $destination = '', $overwrite = false)
{
    //if the zip file already exists and overwrite is false, return false
    if (file_exists($destination) && !$overwrite) {
        return false;
    }
    //vars
    $valid_files = array();
    //if files were passed in...
    if (is_array($files)) {
        //cycle through each file
        foreach ($files as $file) {
            //make sure the file exists
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }
    }
    //if we have good files...
    if (count($valid_files)) {
        //create the archive
        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //add the files
        foreach ($valid_files as $file) {
            $zip->addFile($file, $file);
        }
        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

        //close the zip -- done!
        $zip->close();

        //check to make sure the file exists
        return file_exists($destination);
    } else {
        return false;
    }
}

function get_month($number)
{
    $month = '';
    foreach (months_array() as $key => $value) {

        if ($key == $number) {
            $month = $value;
        }


    }
    return $month;
}

//check if there is internet
function check_internet_connection($sCheckHost = 'www.google.com')
{
    return (bool)@fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
}


//round up
function round_up($number,$precision=0){
    return round($number, $precision, PHP_ROUND_HALF_UP);
}

//round dowm
function round_down($number,$precision=0){
    return round($number, $precision, PHP_ROUND_HALF_DOWN);
}

//round even
function round_even($number,$precision=0){
    return round($number, $precision, PHP_ROUND_HALF_EVEN);
}


function array_to_pipes($array)
{
    //if passed value is an array
    if (is_array($array)) {
        return '|' . implode('|', $array) . '|';
    } else {
        return FALSE;
    }

}


//check domain availability
function domain_availability($domains)
{
    foreach ($domains as $domain) {
        $ns = @dns_get_record($domain, DNS_NS);
        if (count($ns)) {
            $rs[$domain] = 0;
        } else {
            $rs[$domain] = 1;
        }
    }
    return $rs;
}

function generate_password($length = 8, $complex = 3)
{
    $min = "abcdefghijklmnopqrstuvwxyz";
    $num = "0123456789";
    $maj = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $symb = "!@#$%^&*()_-=+;:,.?";
    $chars = $min;
    if ($complex >= 2) {
        $chars .= $num;
    }
    if ($complex >= 3) {
        $chars .= $maj;
    }
    if ($complex >= 4) {
        $chars .= $symb;
    }
    $password = substr(str_shuffle($chars), 0, $length);
    return $password;
}

//empty folder recursively
function emptyDirectory($dirname, $self_delete = false)
{
    if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!$dir_handle)
        return false;
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname . "/" . $file))
                @unlink($dirname . "/" . $file);
            else
                emptyDirectory($dirname . '/' . $file, true);
        }
    }
    closedir($dir_handle);
    if ($self_delete) {
        @rmdir($dirname);
    }
    return true;
}

function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }


    return rmdir($dir);
}


function array_unique_multidimensional($input)
{
    $serialized = array_map('serialize', $input);
    $unique = array_unique($serialized);
    return array_intersect_key($input, $unique);
}

function testRange($int,$min,$max){
    return ($min<$int && $int<$max);
}

function unique_multidim_array($array, $key) {
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}
