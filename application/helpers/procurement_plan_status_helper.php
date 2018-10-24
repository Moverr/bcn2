<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/30/2015
 * Time: 12:35 PM
 */

function get_procurement_plan_status($procurement_plan,$param='')
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_status_m');
    $where=array
    (
        'pp_id'   =>$procurement_plan,
    );
    foreach($ci->procurement_plan_status_m->get_where($where) as $status)
    {
        //if param is not empty return id
        if($param<>'')
        {
            return $status['status_id'];
        }
        else
        {
            return get_procurement_plan_phase_info_by_id($status['status_id'],'title');
        }

    }
}

function get_approved_procurement_plans()
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_status_m');
    $where=array
    (
        'status_id'   =>3,
    );

    //get only ids
    $approved=array();
    foreach($ci->procurement_plan_status_m->get_where($where) as $plan)
    {
        $approved[]=$plan['pp_id'];
    }

    return $approved;
}