<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 2/2/2015
 * Time: 8:46 AM
 */
function get_procurement_plan_phase_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_phase_m');

    return $ci->procurement_plan_phase_m->get_procurement_plan_phase_info($id,$param);
}


function get_active_procurement_plan_phases()
{
    $ci=& get_instance();
    $ci->load->model('procurement_plan_phase_m');
    $where=

        array
        (
            'isactive' =>'y'
        );

    return $ci->procurement_plan_phase_m->get_where($where);
}