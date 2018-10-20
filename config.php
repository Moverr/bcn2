<?php
/*
 * This document includes all global settings required for operation of the system
 *
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */

/*
 *---------------------------------------------------------------
 * GLOBAL SETTINGS
 *---------------------------------------------------------------
 */
    define('SECURE_MODE', false);

    define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'); //Set to HTTPS:// if SECURE_MODE = TRUE

    define('RETRIEVE_URL_DATA_IGNORE', 3); //The starting point to obtain the passed url data

    define('SITE_TITLE', 'BCN');

    define('SITE_SLOGAN', '');

    define('SYS_TIMEZONE', 'Africa/Nairobi');

    define('NUM_OF_ROWS_PER_PAGE', '2');

    define('NUM_OF_LISTS_PER_VIEW', '2');

    define('IMAGE_URL', BASE_URL.'assets/images/');

    define('HOME_URL', getcwd().'/');

    define('DEFAULT_CONTROLLER', 'page');

    define('UPLOAD_DIRECTORY', HOME_URL.'assets/uploads/');
    define('DOWNLOAD_DOCUMENT_URL', 'assets/uploads/documents/');

    define('MAX_FILE_SIZE', 40000000);

    define('ALLOWED_EXTENSIONS', '.doc,.docx,.txt,.pdf,.xls,.xlsx,.jpeg,.png,.jpg,.gif');

    define('MAXIMUM_FILE_NAME_LENGTH', 100);

    define('MINIFY', false);

    define('PORT_HTTP', '80');

    define('PORT_HTTP_SSL', '443');

    define('PHP_LOCATION', 'php5');

    define('ENABLE_PROFILER', false); //See perfomance stats based on set benchmarks

    define('DOWNLOAD_LIMIT', 10000); //Max number of rows that can be downloaded

    define('RETIREMENT_AGE', 60); // Mandatory retirement age

/*
 *---------------------------------------------------------------
 * CRON JOB SETTINGS
 *---------------------------------------------------------------
 */

    define('CRON_HOME_URL', '');

    define('CRON_FILE', CRON_HOME_URL.'cron.list');

    define('CRON_FILE_NAME', 'cron.list');

    define('CRON_FILE_LOG', CRON_HOME_URL.'cron.log');

    define('CRON_REFRESH_PERIOD', '5 minutes');

    define('DEFAULT_CRON_HOME_URL', '');

    define('GLOBAL_CRON_FILE', DEFAULT_CRON_HOME_URL.'global.cron.list');

    define('CRON_INSTALLATIONS', serialize(array(''))); //Use in case of multiple system installations on one server

/*
 *---------------------------------------------------------------
 * QUERY CACHE SETTINGS
 *---------------------------------------------------------------
 */

    define('ENABLE_QUERY_CACHE', false);

    define('QUERY_FILE', HOME_URL.'application/helpers/queries_list_helper.php');

/*
 *---------------------------------------------------------------
 * MESSAGE CACHE SETTINGS
 *---------------------------------------------------------------
 */

    define('ENABLE_MESSAGE_CACHE', false);

    define('MESSAGE_FILE', HOME_URL.'application/helpers/message_list_helper.php');

/*
 *---------------------------------------------------------------
 * SMS GLOBAL CREDENTIALS
 *---------------------------------------------------------------
 */

    define('SMS_GLOBAL_USERNAME', 'sms-global-api-user');

    define('SMS_GLOBAL_PASSWORD', 'sms-global-api-pass');

    define('SMS_GLOBAL_VERIFIED_SENDER', 'verified-phone-number-with-country-code');

/*
 *
 *	0 = Disables logging, Error logging TURNED OFF
 *	1 = Error Messages (including PHP errors)
 *	2 = Debug Messages
 *	3 = Informational Messages
 *	4 = All Messages
 *	The log file can be found in: [HOME_URL]application/logs/
 *	Run >tail -n50 log-YYYY-MM-DD.php to view the errors being generated
 */

 define('LOG_ERROR_LEVEL', 0);

/*
 *---------------------------------------------------------------
 * COMMUNICATION SETTINGS
 *---------------------------------------------------------------
 */

    define('NOREPLY_EMAIL', 'noreply@nwtdemos.com');

    define('APPEALS_EMAIL', 'appeals@nwtdemos.com');

    define('FRAUD_EMAIL', 'fraud@nwtdemos.com');

    define('SECURITY_EMAIL', 'security@nwtdemos.com');

    define('HELP_EMAIL', 'support@nwtdemos.com');

    define('SITE_ADMIN_MAIL', 'moverr@gmail.com,buwian12@gmail.com');

    define('SIGNUP_EMAIL', 'register@nwtdemos.com');

    define('SITE_ADMIN_NAME', 'UHRL Admin');

    define('SITE_GENERAL_NAME', 'UHRL');

    define('DEV_TEST_EMAIL', 'moverr@gmail.com,buwian12@gmail.com');
    define('BEYONIC_API_KEY', '25eac071a2a4c31a1b4edcf6d63fa8314b48afec');

/*
 *--------------------------------------------------------------------------
 * URI PROTOCOL
 *--------------------------------------------------------------------------
 *
 * The default setting of "AUTO" works for most servers.
 * If your links do not seem to work, try one of the other delicious flavors:
 *
 * 'AUTO'
 * 'REQUEST_URI'
 * 'PATH_INFO'
 * 'QUERY_STRING'
 * 'ORIG_PATH_INFO'
 *
 */

    define('URI_PROTOCOL', 'AUTO'); // Set "AUTO" For WINDOWS
                                           // Set "REQUEST_URI" For LINUX

/*
 *---------------------------------------------------------------
 * DATABASE SETTINGS
 *---------------------------------------------------------------
 */

    //CLIENT

    define('HOSTNAME', 'localhost');

    define('USERNAME', 'root');

    define('PASSWORD', '');

    define('DATABASE', 'achest');

    define('DBDRIVER', 'mysqli');

    define('DBPORT', '3306');

/*
    #SERVER
    define('HOSTNAME', "localhost");

    define('USERNAME', "nwtdemos_achest");

    define('PASSWORD', "mLAe8Lx)Ww_a");

    define('DATABASE', "nwtdemos_achest");

    define('DBDRIVER', "mysqli");

    define('DBPORT', "3306");
    */

        $currentyear = date('Y');
        $currentmonth = date('m');
        if ($currentmonth > 1) {
            define('CURRENTYEAR', date('Y'));
            $endyear = CURRENTYEAR + 1;
            define('ENDYEAR', $endyear);
        } else {
            define('CURRENTYEAR', $currentyear - 1);
            define('ENDYEAR', $currentyear);
        }

         define('STARTMONTHNAME', 'january');
         define('STARTMONTHNUMBER', '1');

         define('ENDMONTHNAME', 'december');
//		 define('ENDMONTHNAME','12');

         define('STARTYEAR', '2000');

/*
 *---------------------------------------------------------------
 * EMAIL settings
 *---------------------------------------------------------------
 */
    define('SMTP_HOST', 'localhost');

    define('SMTP_PORT', '25');

    define('SMTP_USER', 'root');

    define('SMTP_PASS', 'P@ssword?');

    define('FLAG_TO_REDIRECT', '0'); // 1 => Redirect emails to a specific mail id,
                                    // 0 => No need to redirect emails.
/*
 * If "FLAG_TO_REDIRECT" is set to 1, it will redirect all the mails from this site
 * to the email address  defined in "MAILID_TO_REDIRECT".
 */

    define('MAILID_TO_REDIRECT', DEV_TEST_EMAIL);
