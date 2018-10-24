<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Provider_m extends MY_Model
{
 	function __construct()
    {

        parent::__construct();
    }
    public $_tablename='providers';
    public $_primary_key='providerid';


    public function get_provider_info($id='', $param='')
    {

        //if NO ID
        if($id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where($this->_primary_key,$id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {


                        case 'title':
                            $result=$row['providernames'];
                            break;


                        default:
                            $result=$query->result_array();
                    }

                }

                return $result;
            }

        }
    }




}