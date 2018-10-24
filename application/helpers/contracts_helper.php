<?php

function get_contract_detail_info($contracts_id,$param)
{
    $ci=& get_instance();
    $ci->load->model('contracts_m');

    return $ci->contracts_m->get_contract_info($contracts_id,$param);
}

function get_contract_price_info_by_contract($contracts_id, $param)
{
    $ci =& get_instance();
    $ci->load->model('contract_price_m');

    return $ci->contract_price_m->get_contract_price_info_by_contract($contracts_id, $param);
}

function get_contract_total_payment_info_by_contract($contracts_id, $param)
{
    $ci =& get_instance();
    $ci->load->model('contract_total_payment_m');

    ///print_array($ci->load->model('contract_total_payment_m')->get_all());

    return $ci->contract_total_payment_m->contract_total_payment_info_by_contract($contracts_id, $param);
}

function get_contracts_by_procurement($procurement_id){
    $ci=& get_instance();
    $ci->load->model('contracts_m');

    $where=array(
        'isactive'=>'Y',
        'procurement_ref_id'=>$procurement_id
    );

    return $ci->contracts_m->get_where($where);

}


function get_contracts_by_procurement_method($procurement_method,$from,$to,$pde=''){
    $ci=& get_instance();
    $ci->load->model('contracts_m');

    return $ci->contracts_m->get_contracts_by_contracts_by_procurement_method($procurement_method,$from,$to,$pde);

}

function get_contract_info_by_ref_number($ref_number)
{
    $ci=& get_instance();
    $ci->load->model('contracts_m');

    return $ci->contracts_m->get_contract_by_ref_number($ref_number);
}


function get_call_off_orders_by_contract($contract_id,$status='')
{
    $ci=& get_instance();
    $ci->load->model('contracts_m');

    return $ci->contracts_m->get_call_off_orders_by_contract($contract_id,$status);
}

function get_call_off_orders_by_status($status='awarded',$from,$to)
{
    $ci=& get_instance();
    $ci->load->model('contracts_m');

    return $ci->contracts_m->get_call_off_orders_by_contract($status,$from,$to);
}

function get_contract_amount_by_lot_id($id=''){
    $ci=& get_instance();
    $ci->load->model('contracts_m');
    return $ci->contracts_m->get_contract_amount_by_lot_id($id);
}

function get_awarded_contracts($from, $to, $pde = '',$financial_year='',$completed='',$count=''){
    $ci=& get_instance();
    $ci->load->model('contracts_m');
    return $ci->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed,$count);
}


function get_terminated_contracts($from, $to, $pde = '',$financial_year='',$completed='',$count=''){
    $ci=& get_instance();
    $ci->load->model('contracts_m');
    return $ci->contracts_m->get_contracts_all_terminated($from, $to, $pde,$financial_year,$completed,$count);
}

function get_lots_by_contract($contract_id)
{
    $ci=& get_instance();
    $ci->load->model('contracts_m');
    return $ci->contracts_m->get_lots_by_contract($contract_id);
}

function get_contract_details_by_bid($bidid)
{
    $ci=& get_instance();
    $ci->load->model('contracts_m');
    return $ci->contracts_m->get_contract_details_by_bid($bidid);    
}

function get_sum_of_contract_value($contract_id)
{
     $ci=& get_instance();
     $ci->load->model('contracts_m');
     return $ci->contracts_m->get_sum_of_contract_value($contract_id);        
}