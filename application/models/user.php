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

        $personId = $this->query_reader->add_data('add_user', $added_data);
        if ($personId) {
            $data['boolean'] = true;
            $data['msg'] = 'SUCCESS:  Record Saved Succesfully ';
        }

        return $data;
    }
}
