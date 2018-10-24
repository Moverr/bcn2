<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/27/2015
 * Time: 10:12 AM
 */

function get_procurement_method_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('procurement_method_m');

    return $ci->procurement_method_m->get_procurement_method_info($id,$param);
}


function get_active_procurement_methods()
{
    $ci=& get_instance();
    $ci->load->model('procurement_method_m');
    $where=

        array
        (
            'isactive' =>'Y'
        );

    return $ci->procurement_method_m->get_where($where);
}

