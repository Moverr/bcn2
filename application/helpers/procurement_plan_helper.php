<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/30/2015
 * Time: 12:35 PM
 */

function get_active_procurement_plans($pde_id,$order='DESC')
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_m');
    $where=array
    (
        'pde_id'   =>$pde_id,
        'isactive'=>'y'
    );
    return $ci->procurement_plan_m->get_where_order_by($where,  $key='id', $order);
}

function get_procurement_plan_info($plan_id,$param)
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_m');

    return $ci->procurement_plan_m->get_procurement_plan_info($plan_id,$param);
}
function count_procurement_plans_by_pde($pde_id,$financial_year=''){

    $ci=& get_instance();
    $ci->load->model('procurement_plan_m');
    if($financial_year==''){
        $financial_year=date('Y').'-'.(date('Y')+1);
    }
    $where=array
    (
        'pde_id'   =>$pde_id,
        'isactive'=>'y',
        'financial_year'=>$financial_year
    );
    $ci->procurement_plan_m->get_count_by_criteria($where);
    //print_array($ci->db->last_query());
    return $ci->procurement_plan_m->get_count_by_criteria($where);
}

function get_compliant_pdes($where='')
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_m');

    return $ci->procurement_plan_m->compliant_pdes($where);
}

function get_late_procurements($financial_year){
    $ci=& get_instance();


    return $ci->load->procurement_plan_m->custom_query('SELECT
  pdes.pdeid,
  pdes.pdename,
  pdes.abbreviation,
  PP.financial_year,
  BI.procurement_ref_no,
  PPE.subject_of_procurement,
  BI.invitation_to_bid_date,
  PPE.bid_issue_date,
  DATEDIFF(BI.invitation_to_bid_date, PPE.bid_issue_date) AS days_delayed
FROM pdes
  INNER JOIN procurement_plans PP
    ON pdes.pdeid = PP.pde_id
  INNER JOIN procurement_plan_entries PPE
    ON PPE.procurement_plan_id = PP.id
  LEFT JOIN bidinvitations BI
    ON PPE.id= BI.procurement_id
    WHERE
    PP.financial_year ="'.$financial_year.'" AND PP.isactive = "Y"
    ');
}

function count_procurement_plans($financial_year=''){

    $ci=& get_instance();
    $ci->load->model('procurement_plan_m');
    if($financial_year==''){
        $financial_year=date('Y').'-'.(date('Y')+1);
    }
    $where=array
    (

        'isactive'=>'y',
        'financial_year'=>$financial_year
    );
    $ci->procurement_plan_m->get_count_by_criteria($where);

    return $ci->procurement_plan_m->get_count_by_criteria($where);


}


# get financial years by entity
function get_financial_years($pde=''){
    $ci=& get_instance();
    $ci->load->model('procurement_plan_m');
    return $ci->procurement_plan_m-> get_financial_years_by_pde($pde);

}

