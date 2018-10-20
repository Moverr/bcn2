<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Model
{
    public function add_data($queryCode, $queryData = array())
    {
        $this->db->query($this->get_query_by_code($queryCode, $queryData));

        return $this->db->insert_id();
    }
}
