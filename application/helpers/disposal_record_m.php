<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Disposal_record_m extends MY_Model
{
 	function __construct()
    {

        parent::__construct();
    }
    public $_tablename='disposal_record';
    public $_primary_key='id';


    public function get_disposal_record_info($id='', $param='')
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
                        case 'plan_id':
                            $result=$row['disposal_plan'];
                            break;
                        case 'plan':
                            $result=get_disposal_plan_info_by_id($row['disposal_plan'],'title');
                            break;
                        case 'serial_no':
                            $result = $row['disposal_serial_no'];
                            break;


                        case 'pde_id':
                            $result=$row['pde_id'];
                            break;

                        case 'pde':
                            $result=get_pde_info_by_id($row['pde_id'],'title');
                            break;

                        case 'title':
                            $result=$row['subject_of_disposal'];
                            break;


                        case 'isactive':
                            $result=$row['isactive'];
                            break;

                        case 'location':
                            $result=$row['asset_location'];
                            break;

                        case 'amount':
                            $result=$row['amount'];
                            break;

                        case 'currency':
                            $result=$row['currence'];
                            break;

                        case 'approval_date':
                            $result=$row['dateofaoapproval'];
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