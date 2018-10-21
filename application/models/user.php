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
        if (!empty($results)) {
            throw new Exception('An Email Address '.$details['emailAddress'].' exists in the database ');
        }
        // get_list

        $personId = $this->query_reader->add_data('add_user', $added_data);
        if ($personId) {
            $data['boolean'] = true;
            $data['msg'] = 'SUCCESS:  Record Saved Succesfully ';
        }

        return $data;
    }
}
