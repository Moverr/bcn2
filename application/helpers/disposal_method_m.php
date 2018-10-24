<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Disposal_method_m extends MY_Model
{
 	function __construct()
    {

        parent::__construct();
    }
    public $_tablename='disposal_method';
    public $_primary_key='id';


    public function get_disposal_method_info($id='', $param='')
    {

        //if NO ID
        if($id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where('id',$id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {
                        case 'method':
                            $result=$row['method'];
                            break;
                        case 'title':
                            $result=$row['method'];
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