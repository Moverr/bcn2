<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Model
{
    public function add($person_id, $details = array())
    {
        $added_data = array(
            'username' => $details['username'],
            'emailaddress' => htmlentities($details['emailaddress'], ENT_QUOTES),
            'password' => htmlentities($details['password'], ENT_QUOTES),
            'status' => 'ACTIVE',
        );

        if (!empty($details['id'])) {
            $added_data['id'] = $details['id'];

            $personId = $this->_query_reader->run('update_post', $added_data);
            $data['boolean'] = true;
            $data['msg'] = 'SUCCESS:  Record Updated  Succesfully ';
        } else {
            $personId = $this->_query_reader->add_data('add_post', $added_data);
            if ($personId) {
                $data['boolean'] = true;
                $data['msg'] = 'SUCCESS:  Record Saved Succesfully ';
            }
        }

        return $data;
    }
}
