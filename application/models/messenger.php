<?php


class Messenger extends CI_Model
{
    //Constructor to set some default values at class load
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        //Use the message cache if its enabled
        $this->load->helper('message_list');
    }

    // Notify a user by sending a message to their email, sms and in the system
    // $required - makes sure that the sending formats required were successful, although the other formats are still attempted
    public function send($userId, $message, $required = array('system'))
    {
        //print_r($userId);

        //	echo "<br/>:::::<br/>";

        // Sending email
        if (is_array($userId)) {
            //340,380,459,469,474
            $resultArray = array();
            foreach ($userId as $id) {
                array_push($resultArray, $this->send_email_message($id, $message));
            }
            $results['email'] = get_decision($resultArray);
        } else {
            $results['email'] = $this->send_email_message($userId, $message);
        }

        // Sending SMS
        /* if(is_array($userId))
        {
            $resultArray = array();
            foreach($userId AS $id) array_push($resultArray, $this->send_sms_message($id, $message));
            $results['sms'] = get_decision($resultArray);
        }
        else $results['sms'] = $this->send_sms_message($userId, $message);  */

        // Sending System
        if (is_array($userId)) {
            $resultArray = array();
            foreach ($userId as $id) {
                array_push($resultArray, $this->send_system_message($id, $message));
            }
            $results['system'] = get_decision($resultArray);
        } else {
            $results['system'] = $this->send_system_message($userId, $message);
        }

        //If the sending format required passed then return the result as successful even if the others may have failed
        $considered = array();
        foreach ($results as $key => $value) {
            if (in_array($key, $required)) {
                array_push($considered, $value);
            }
        }

        return get_decision($considered);
    }

    // Send email message
    public function send_email_message($userId, $messageDetails)
    {
        $isSent = false;

        // 1. If email address is not provided, then fetch it using the user id
        if (!empty($userId)) {
            $user = $this->_query_reader->get_row_as_array('get_user_profile', array('user_id' => $userId));

            if (!empty($user)) {
                $emailaddress = $user['email_address'];
                $messageDetails['first_name'] = !empty($messageDetails['firstname']) ? $messageDetails['firstname'] : $user['first_name'];
                $messageDetails['login_link'] = trim((!empty($messageDetails['loginlink']) ? $messageDetails['loginlink'] : base_url()), '/');
            }
        }

        $messageDetails['email_from'] = !empty($messageDetails['emailfrom']) ? $messageDetails['emailfrom'] : NOREPLY_EMAIL;
        $messageDetails['from_name'] = !empty($messageDetails['fromname']) ? $messageDetails['fromname'] : SITE_GENERAL_NAME;

        $emailTo = !empty($messageDetails['emailaddress']) ? $messageDetails['emailaddress'] :
        (!empty($messageDetails['email_address']) ? $messageDetails['email_address'] :
         (!empty($emailaddress) ? $emailaddress : ''));

        if (!empty($emailTo)) {
            // 2. Fetch the message template and populate the necessary details
            if (!empty($messageDetails['code'])) {
                //print_r($messageDetails['code']);
                $template = $this->get_template_by_code($messageDetails['code']);

                $emailMessage = $this->populate_template($template, $messageDetails);
                $messageDetails['subject'] = $emailMessage['subject'];
                $messageDetails['details'] = $emailMessage['details'];
            }

            // 3. Send message
            if (!empty($messageDetails['details'])) {
                $this->email->to($emailTo);
                $this->email->from($messageDetails['email_from'], $messageDetails['from_name']);
                $this->email->reply_to($messageDetails['email_from'], $messageDetails['from_name']);
                if (!empty($messageDetails['cc'])) {
                    $this->email->cc($messageDetails['cc']);
                }
                // Copy admin if he is not the sender
                if ((!empty($template['copy_admin']) && $template['copy_admin'] == 'Y') && $messageDetails['email_from'] != SITE_ADMIN_MAIL) {
                    $this->email->bcc(SITE_ADMIN_MAIL);
                }

                $this->email->subject($messageDetails['subject']);
                $this->email->message($messageDetails['details']);

                if (isset($messageDetails['fileurl']) && trim($messageDetails['fileurl']) != '') {
                    $this->email->attach($messageDetails['fileurl']);
                }

                // Use this line to test sending of email without actually sending it
                // echo $this->email->print_debugger();

                $isSent = $this->email->send();

                $this->email->clear(true);
                $messageDetails = clean_form_data($messageDetails);

                //Record messsage exchange if sent
                if ($isSent && !empty($userId)) {
                    $result = $this->_query_reader->run('record_message_exchange', array('code' => (!empty($messageDetails['code']) ? $messageDetails['code'] : 'user_defined_message'), 'send_format' => 'log_email', 'attachment' => (!empty($messageDetails['fileurl']) ? $messageDetails['fileurl'] : ''), 'details' => clean_str($messageDetails['details']), 'subject' => $messageDetails['subject'], 'recipient_id' => $userId, 'sender' => $messageDetails['email_from']));
                }
            }
        }

        return $isSent;
    }

    // Send an SMS to the specified user
    public function send_sms_message($userId, $messageDetails)
    {
        $isSent = false;
        if (!empty($userId)) {
            $user = $this->_query_reader->get_row_as_array('get_user_profile', array('user_id' => $userId));
            if (!empty($user['telephone'])) {
                $messageDetails['email_from'] = !empty($messageDetails['emailfrom']) ? $messageDetails['emailfrom'] : NOREPLY_EMAIL;
                $messageDetails['from_name'] = !empty($messageDetails['fromname']) ? $messageDetails['fromname'] : SITE_GENERAL_NAME;

                //Populate the template if given the code
                if (!empty($messageDetails['code'])) {
                    $template = $this->get_template_by_code($messageDetails['code']);
                    $smsMessage = $this->populate_template($template, $messageDetails);
                    $messageDetails['subject'] = $smsMessage['subject'];
                    $messageDetails['sms'] = $smsMessage['sms'];
                }

                //Attempt sending by SMS and then by API
                $this->load->model('_carrier');
                $carrierEmailDomain = $this->_carrier->get_email_domain($user['telephone']);
                if (!empty($carrierEmailDomain)) {
                    $this->email->to($user['telephone'].'@'.$carrierEmailDomain);
                    $this->email->from($messageDetails['email_from'], $messageDetails['from_name']);
                    $this->email->reply_to($messageDetails['email_from'], $messageDetails['from_name']);
                    if (!empty($template['copy_admin']) && $template['copy_admin'] == 'Y') {
                        $this->email->bcc(SITE_ADMIN_MAIL);
                    }

                    $this->email->subject($messageDetails['subject']);
                    $this->email->message($messageDetails['sms']);

                    $isSent = $this->email->send();
                    $this->email->clear(true);

                    //Record messsage exchange if sent
                    if ($isSent && !empty($userId)) {
                        $result = $this->_query_reader->run('record_message_exchange', array('code' => (!empty($messageDetails['code']) ? $messageDetails['code'] : 'user_defined_message'), 'send_format' => 'log_sms', 'details' => $messageDetails['sms'], 'subject' => $messageDetails['subject'], 'recipient_id' => $userId, 'sender' => $messageDetails['email_from']));
                    }
                }
            }

            //Else use the SMS-Global gateway to send the SMS
            if (!$isSent && !empty($user['telephone']) && !empty($messageDetails['sms'])) {
                $this->load->library('Sms_global', array('user' => SMS_GLOBAL_USERNAME, 'pass' => SMS_GLOBAL_PASSWORD, 'from' => SMS_GLOBAL_VERIFIED_SENDER));

                $this->sms_global->to(preg_replace('/^0/', '256', $user['telephone']));
                $this->sms_global->from(SMS_GLOBAL_VERIFIED_SENDER);
                $this->sms_global->message($messageDetails['sms']);
                $this->sms_global->send();
                // only use this to output the message details on screen for debugging
                //$this->sms_global->print_debugger();

                $isSent = !empty($this->sms_global->get_sms_id()) ? true : false;
            }
        }

        return $isSent;
    }

    // Send a system message to the specified user
    public function send_system_message($userId, $messageDetails)
    {
        if (!empty($userId)) {
            $user = $this->_query_reader->get_row_as_array('get_user_profile', array('user_id' => $userId));

            $emailaddress = !empty($messageDetails['email_address']) ? $messageDetails['email_address'] : '';

            $messageDetails['first_name'] = !empty($messageDetails['first_name']) ? $messageDetails['first_name'] : '';

            $messageDetails['login_link'] = !empty($messageDetails['loginlink']) ? $messageDetails['loginlink'] : trim(base_url(), '/');
        }
        $messageDetails['email_from'] = !empty($messageDetails['emailfrom']) ? $messageDetails['emailfrom'] : $this->native_session->get('__email_address');
        $messageDetails['from_name'] = !empty($messageDetails['fromname']) ? $messageDetails['fromname'] : $this->native_session->get('__last_name').' '.$this->native_session->get('__first_name');

        // 1. Fetch the message template and populate the necessary details
        if (!empty($messageDetails['code'])) {
            $template = $this->get_template_by_code($messageDetails['code']);
            $systemMessage = $this->populate_template($template, $messageDetails);
            $messageDetails['subject'] = $systemMessage['subject'];
            $messageDetails['details'] = $systemMessage['details'];

            $messageDetails = clean_form_data($messageDetails);

            if ($template['copy_admin'] == 'Y') {
                $this->_query_reader->run('record_message_exchange', array('code' => (!empty($messageDetails['code']) ? $messageDetails['code'] : 'user_defined_message'), 'send_format' => 'system', 'details' => $messageDetails['details'], 'attachment' => (!empty($messageDetails['fileurl']) ? substr(strrchr($messageDetails['fileurl'], '/'), 1) : ''), 'subject' => $messageDetails['subject'], 'recipient_id' => implode("','", $this->get_admin_users()), 'sender' => $messageDetails['email_from']));
            }
        }

        // 2. Record the message exchange to be accessed by the recipient in their inbox
        return $this->_query_reader->run('record_message_exchange', array('code' => (!empty($messageDetails['code']) ? $messageDetails['code'] : 'user_defined_message'), 'send_format' => 'system', 'details' => $messageDetails['details'], 'attachment' => (!empty($messageDetails['fileurl']) ? substr(strrchr($messageDetails['fileurl'], '/'), 1) : ''), 'subject' => $messageDetails['subject'], 'recipient_id' => $userId, 'sender' => $messageDetails['email_from']));
    }

    // Get a template of the message given its code
    public function get_template_by_code($code)
    {
        $cachedMessage = ENABLE_MESSAGE_CACHE ? get_sys_message($code) : '';

        return (!empty($cachedMessage) && ENABLE_MESSAGE_CACHE) ? $cachedMessage : $this->_query_reader->get_row_as_array('get_message_template', array('message_type' => $code));
    }

    // Populate the template to generate the actual message
    public function populate_template($template, $values = array(), $type = 'email')
    {
        //print_r($type);

        // Order keys by length - longest first
        array_multisort(array_map('strlen', array_keys($values)), SORT_DESC, $values);

        // SMS message
        if ($type == 'sms' && !empty($template['sms'])) {
            foreach ($values as $key => $value) {
                $template['subject'] = str_replace('_'.strtoupper($key).'_', html_entity_decode($value, ENT_QUOTES), $template['subject']);
                $template['sms'] = str_replace('_'.strtoupper($key).'_', html_entity_decode($value, ENT_QUOTES), $template['sms']);
            }
        }

        // Email or system message
        elseif (in_array($type, array('email', 'system')) && !empty($template['subject']) && !empty($template['details'])) {
            // Go through all passed values and replace where they appear in the template text
            foreach ($values as $key => $value) {
                $template['subject'] = str_replace('_'.strtoupper($key).'_', html_entity_decode($value, ENT_QUOTES), $template['subject']);
                $template['details'] = str_replace('_'.strtoupper($key).'_', html_entity_decode($value, ENT_QUOTES), $template['details']);
            }
        }

        return $template;
    }

    // Populate the session if the user is responding
    public function populate_session($messageId)
    {
        $message = $this->_query_reader->get_row_as_array('get_message_by_id', array('message_id' => $messageId));

        if (!empty($message)) {
            $msgSeparator = '<br><br><br><br>-------------------------------<br>Received '.date('d-M-Y h:ia T', strtotime($message['date_sent'])).'<br><br>';
            $this->native_session->set('recipientname__users', $message['sender_name']);
            $this->native_session->set('recipientid', $message['sender_id']);
            $this->native_session->set('subject', (strtolower(substr($message['subject'], 0, 3)) != 're:' ? 'Re: ' : '').$message['subject']);
            $this->native_session->set('message', ($message['send_format'] != 'sms' ? $msgSeparator : '').$message['details']);
        }
    }

    // Clear a message session profile
    public function clear_session()
    {
        $fields = array('recipientname__users' => '', 'recipientid' => '', 'subject' => '', 'message' => '');
        $this->native_session->delete_all($fields);
    }

    // Get list of messages for the given user a list parameters
    public function get_list($instructions)
    {
        $searchString = " AND send_format IN ('system','sms','email') ";
        // If a search phrase is sent in the instructions
        if (!empty($instructions['searchstring'])) {
            $searchString .= ' AND '.$instructions['searchstring'];
        }
        // $variable = [condition]? [value if condition is true]: [value if condition is false];
        // $count = 5
        $count = !empty($instructions['pagecount']) ? $instructions['pagecount'] : NUM_OF_ROWS_PER_PAGE;
        $start = !empty($instructions['page']) ? ($instructions['page'] - 1) * $count : 0;

        if (!empty($instructions['action']) && $instructions['action'] == 'sent') {
            return $this->_query_reader->get_list('get_sent_message_list', array('sender_email' => $this->native_session->get('__email_address'), 'search_query' => $searchString, 'limit_text' => $start.','.($count + 1), 'order_by' => ' ORDER BY M.date_added DESC '));
        } elseif (!empty($instructions['action']) && $instructions['action'] == 'archive') {
            return $this->_query_reader->get_list('get_message_list', array('recipient_id' => $this->native_session->get('__user_id'), 'search_query' => $searchString, 'limit_text' => $start.','.($count + 1), 'order_by' => " HAVING status='archived' ORDER BY M.date_added DESC"));
        } else {
            return $this->_query_reader->get_list('get_message_list', array('recipient_id' => $this->native_session->get('__user_id'), 'search_query' => $searchString, 'limit_text' => $start.','.($count + 1), 'order_by' => " HAVING status <> 'archived' ORDER BY M.date_added DESC"));
        }
    }

    // Send a message submitted from a form on the UI
    public function send_from_form($messageType, $details)
    {
        $boolean = false;
        $msg = '';

        if (!empty($details['userid'])) {
            // Send to more than one user
            if (strtolower(trim($details['userid'])) == 'all' || $details['userid'] == '_all_admins_') {
                $users = ($details['userid'] == '_all_admins_') ? $this->get_admin_users('list') : $this->_query_reader->get_list('get_active_users');
                $unsuccessful = array();

                foreach ($users as $row) {
                    if ($row['id'] != $this->native_session->get('__user_id')) {
                        if ($messageType == 'email') {
                            $result = $this->send_email_message($row['id'], array('subject' => $details['subject'], 'details' => $details['message']));
                        } elseif ($messageType == 'sms') {
                            $result = $this->send_sms_message($row['id'], array('subject' => 'NONE: From '.($this->native_session->get('__last_name').' '.$this->native_session->get('__first_name')).' at '.date('d-M-Y h:i:sA T'), 'sms' => $details['message']));
                        } else {
                            $result = $this->send_system_message($row['id'], array('subject' => $details['subject'], 'details' => $details['message']));
                        }
                        if (!$result) {
                            array_push($unsuccessful, $row['id']);
                        }
                    }
                }

                $msg = 'The message has been sent to all. ';
                if (!empty($unsuccessful)) {
                    //Get the unsuccessful users' names
                    $userNames = $this->_query_reader->get_single_column_as_array('get_user_names_by_list', 'name', array('user_ids' => "'".implode("','", $unsuccessful)."'", 'limit_full_text' => ' LIMIT 10'));

                    $msg .= 'However, an error occurred for the following: '.implode(', ', $userNames).(count($userNames) == 10 ? ' ..and more.' : '');
                    $boolean = false;
                } else {
                    $boolean = true;
                }
            } else {
                if ($messageType == 'email') {
                    $boolean = $this->send_email_message($details['userid'], array('subject' => $details['subject'], 'details' => $details['message']));
                } elseif ($messageType == 'sms') {
                    $boolean = $this->send_sms_message($details['userid'], array('subject' => 'NONE', 'sms' => $details['message']));
                } else {
                    $boolean = $this->send_system_message($details['userid'], array('subject' => $details['subject'], 'details' => $details['message']));
                }
                if (!$boolean) {
                    $msg = 'ERROR: We could not send the message.';
                }
            }
        }

        return array('boolean' => $boolean, 'msg' => $msg);
    }

    // Verify a message? - just keeping the function consistency
    // This function archives and restores messages from the inbox
    public function verify($instructions)
    {
        $result = array('boolean' => false, 'msg' => 'ERROR: The message instructions could not be resolved.');

        if (!empty($instructions['action'])) {
            switch ($instructions['action']) {
                case 'archive':
                    $result['boolean'] = $this->change_status($instructions['id'], 'archived');
                break;

                case 'restore':
                    $result['boolean'] = $this->change_status($instructions['id'], 'read');
                break;
            }

            if (!empty($result['boolean'])) {
                $result['msg'] = $result['boolean'] ? 'The message status has been changed' : 'ERROR: The message status could not be changed.';
            }
        }

        return $result;
    }

    // Change status of message
    public function change_status($exchangeId, $status)
    {
        return $this->_query_reader->run('add_message_status', array('message_exchange_id' => $exchangeId, 'user_id' => $this->native_session->get('__user_id'), 'status' => $status));
    }

    // Get the details about a message given its ID
    public function message_details($messageId)
    {
        return $this->_query_reader->get_row_as_array('get_message_by_id', array('message_id' => $messageId));
    }

    // Returns admin user ids
    public function get_admin_users($return = 'array')
    {
        if ($return == 'list') {
            return $this->_query_reader->get_list('get_users_in_group', array('group' => 'admin', 'condition' => ''));
        } else {
            return $this->_query_reader->get_single_column_as_array('get_users_in_group', 'user_id', array('group' => 'admin', 'condition' => ''));
        }
    }

    // Returns users in the specified permission groups
    public function get_users_in_role($groups, $thisUserId = '')
    {
        $userIds = array();

        // 1. Go through the user groups picking the users in the groups by ids
        foreach ($groups as $group) {
            // 2. If the group is CAO, then pick only CAO users in the user's county/district if the user has no county
            if ($group == 'cao') {
                $userIds = array_merge($userIds, $this->_query_reader->get_single_column_as_array('get_caos_for_user', 'user_id', array('teacher_id' => $thisUserId)));
            }
            // 3. If the group is Manager, the pick only the Manager users at the user's school
            elseif ($group == 'manager') {
                $userIds = array_merge($userIds, $this->_query_reader->get_single_column_as_array('get_managers_for_user', 'user_id', array('teacher_id' => $thisUserId)));
            } else {
                $userIds = array_merge($userIds, $this->_query_reader->get_single_column_as_array('get_users_in_group', 'user_id', array('group' => $group, 'condition' => '')));
            }
        }

        return $userIds;
    }

    // Get part of an address for a user
    public function get_address_part($userId, $addressPart, $addressType = 'contact')
    {
        $address = $this->_query_reader->get_row_as_array('get_user_address', array('user_id' => $userId, 'address_type' => $addressType));

        return !empty($address[$addressPart]) ? $address[$addressPart] : '';
    }

    //Load queries into the message file
    public function load_messages_into_cache()
    {
        $messages = $this->db->query('SELECT * FROM message')->result_array();

        //Now load the queries into the file
        file_put_contents(MESSAGE_FILE, '<?php '.PHP_EOL.'global $sysMessage;'.PHP_EOL);
        foreach ($messages as $message) {
            $messageString = "\$sysMessage['".$message['message_type']."'] = array('subject'=>\"".str_replace('"', '\"', $message['subject'])."\", 'details'=>\"".str_replace('"', '\"', $message['details'])."\", 'sms'=>\"".str_replace('"', '\"', $message['sms'])."\", 'copy_admin'=>'".$message['copy_admin']."');".PHP_EOL;
            file_put_contents(MESSAGE_FILE, $messageString, FILE_APPEND);
        }

        file_put_contents(MESSAGE_FILE, PHP_EOL.PHP_EOL.' function get_sys_message($code) { '.PHP_EOL.'global $sysMessage; '.PHP_EOL."return !empty(\$sysMessage[\$code])? \$sysMessage[\$code]: '';".PHP_EOL.' }'.PHP_EOL, FILE_APPEND);

        echo 'MESSAGE CACHE FILE HAS BEEN UPDATED ['.date('F d, Y H:i:sA T').']';
    }
}
