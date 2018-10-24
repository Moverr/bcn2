<?php

function get_bid_invitation_info($passed_id, $param)
{
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');


    return $ci->bid_invitation_m->get_bid_invitation_info($passed_id, $param);

}

function get_bids_due_to_expire_next_week()
{
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    $from=mysqldate();
    $to=date('d-M-Y',strtotime(mysqldate())+604800) ;


    return $ci->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to);

}


function get_lots_by_IFB($ifb_id)

{
      $ci =& get_instance();
      $result =   $ci->db->query("SELECT * FROM lots WHERE bid_id  = ".$ifb_id)->result_array();

      return $result;

}


function get_bid_invitation_by_procurement($procurement_id){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');

    return $ci->bid_invitation_m->get_bid_invitation_by_procurement_id($procurement_id);


}


function get_bid_responses_per_procurement($procurement_id){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');

    $bid_invitation_id=$ci->bid_invitation_m->get_bid_invitation_by_procurement_id($procurement_id);

    return get_bid_receipts_by_bid($bid_invitation_id);
}

function get_bid_invitation_info_by_procurement($procurement_id, $param)
{
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');


    return $ci->bid_invitation_m->get_bid_invitation_info($procurement_id, $param);

}

//get best evaluated bidder by procurement
function get_beb_by_procurement_ref_num($procurement_ref_no){
    //print_array($procurement_ref_no);
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_beb_by_procurement_ref_num($procurement_ref_no);
}

//get best evaluated bidder by bid
function get_beb_by_bid($bid_id,$param=''){
    $ci =& get_instance();

    $ci->load->model('bid_invitation_m');
    $result=$ci->bid_invitation_m->get_beb_by_bid($bid_id);
    foreach($result as $row){
        switch($param){
            case 'title':
                $result=$row['providernames'];
                break;
            case 'id';
                $result=$row['id'];
                break;
            case 'nationality':
                $result=$row['nationality'];
                break;
            default:
                $result;
        }
    }
    return $result;
}


function get_bid_responsiveness_by_bid($bid_id){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_bid_responsiveness_by_bid($bid_id);
}

function get_extra_bed_info($bid_id){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_beb_extra_details($bid_id);
}


# get invitation for bids
function get_all_ifbs($from='',$to,$pde='',$financial_year='',$count=''){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_invitation_for_bids($from, $to, $pde,$financial_year,$count);

}

# get active invitation for bids
function get_active_ifbs($from='',$to,$pde='',$financial_year='',$count=''){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_active_invitation_for_bids($from, $to, $pde,$financial_year,$count);

}

# get archived invitation for bids
function get_archived_ifbs($from='',$to,$pde='',$financial_year='',$count=''){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_archived_invitation_for_bids($from, $to, $pde,$financial_year,$count);

}

# get active beb
function get_active_bebs($from='',$to,$pde='',$financial_year='',$count=''){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_active_best_evaluated_bidders($from, $to, $pde,$financial_year,$count);

}


# get archived beb
function get_archived_bebs($from='',$to,$pde='',$financial_year='',$count=''){
    $ci =& get_instance();
    $ci->load->model('bid_invitation_m');
    return $ci->bid_invitation_m->get_archived_best_evaluated_bidders($from, $to, $pde,$financial_year,$count);

}


