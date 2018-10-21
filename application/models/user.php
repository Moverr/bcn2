<?php


class User extends CI_Model
{
    public function add($details = array())
    {
        $added_data = array(
            'username' => $details['username'],
            'email' => htmlentities($details['emailAddress'], ENT_QUOTES),
            'password' => sha1($details['password']),
            'status' => 'ACTIVE',
        );

        // todo: check to see that the user with the same email is not in the system:
        $user_email = $this->query_reader->get_list('get_user_by_email', $added_data);
        if (!empty($user_email)) {
            throw new InvalidArgumentException('An Email Address '.$details['emailAddress'].' exists in the database ');
        }
        // get_list

        $personId = $this->query_reader->add_data('add_user', $added_data);
        if ($personId) {
            $data['boolean'] = true;
            $data['msg'] = 'SUCCESS:  Record Saved Succesfully ';
        }

        return $data;
    }

    public function is_valid_account($accountDetails)
    {
        $boolean = false;
        $userId = '';

        $user = $this->query_reader->get_row_as_array('get_user_by_name_and_pass', array('login_name' => $accountDetails['login_name'], 'login_password' => sha1($accountDetails['login_password'])));
        if (!empty($user)) {
            $boolean = true;
            $userId = $user['id'];

            //Set the user's session variables
            $this->native_session->set('user_id', $user['id']);
            $this->native_session->set('email_address', $user['emailaddress']);
            // if (!empty($user['telephone'])) {
            //     $this->native_session->set('telephone', $user['telephone']);
            // }
            // $this->native_session->set('permission_group', $user['permission_group_id']);
            $this->native_session->set('username', $user['username']);
            // $this->native_session->set('last_name', $user['last_name']);
            // $this->native_session->set('gender', $user['gender']);
            // $this->native_session->set('date_of_birth', $user['date_of_birth']);
        }

        return array('boolean' => $boolean, 'user_id' => $userId);
    }
}
