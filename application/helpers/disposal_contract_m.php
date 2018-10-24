<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Disposal_contract_m extends MY_Model
{
 	function __construct()
    {

        parent::__construct();
    }
    public $_tablename='disposal_contract';
    public $_primary_key='id';


    public function get_disposal_contract_info($id='', $param='')
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
                        case 'disposalrecord':
                            $result=get_disposal_record_info_by_id($row['disposalrecord'],'title');
                            break;
                        case 'disposalrecord_id':
                            $result = $row['disposalrecord'];
                            break;
                        case 'beneficiary_id':
                            $result = $row['beneficiary'];
                            break;
                        case 'beneficiary':
                            $result = get_provider_info_by_id($row['beneficiary'],'title');
                            break;
                        case 'contractamount':
                            $result=$row['contractamount'];
                            break;

                        case 'isactive':
                            $result=$row['isactive'];
                            break;

                        case 'currency_id':
                            $result=$row['currency'];
                            break;


                        default:
                            $result=$query->result_array();
                    }

                }

                return $result;
            }

        }
    }

    public function get_disposal_contract_info_by_disposal_record($id='', $param='')
    {

        //if NO ID
        if($id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where('disposalrecord',$id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {
                        case 'id':
                            $result=$row['id'];
                            break;

                        case 'beneficiary_id':
                            $result = $row['beneficiary'];
                            break;
                        case 'beneficiary':
                            $result = get_provider_info_by_id($row['beneficiary'],'title');
                            break;
                        case 'contractamount':
                            $result=$row['contractamount'];
                            break;

                        case 'amount':
                            $result=$row['contractamount'];
                            break;

                        case 'isactive':
                            $result=$row['isactive'];
                            break;

                        case 'currency_id':
                            $result=$row['currency'];
                            break;
                        case 'currency':
                            $result=$row['currency'];
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