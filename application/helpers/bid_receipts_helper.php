<?php

//function get_bid_receipts_by_bid($bid_id){
//    $ci=& get_instance();
//    $ci->load->model('receipts_m');
//
//    $where=array(
//        'isactive'=>'Y',
//        'bid_id'=>$bid_id
//    );
//
//    return $ci->receipts_m->get_where($where);
//
//}

function get_bid_receipts_by_bid($bid_id){
    $ci=& get_instance();
    $ci->load->model('receipts_m');


    return $ci->receipts_m-> get_receipts_by_bid($bid_id);

}

function get_bid_receipts_by_procurement_method($procurement_method_id,$from='',$to=''){
    $ci=& get_instance();
    $ci->load->model('receipts_m');
    return $ci->receipts_m->get_receipts_by_procurement_method($procurement_method_id,$from,$to);

}

function get_bid_receipts_by_contract($contract_id){
    $ci=& get_instance();
    $ci->load->model('receipts_m');
    return $ci->receipts_m->get_bid_receipts_by_contract($contract_id);

}