<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/27/2015
 * Time: 10:12 AM
 */

function get_procurement_type_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');

    return $ci->procurement_type_m->get_procurement_type_info($id,$param);
}


function get_active_procurement_types()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    $where=

        array
        (
            'isactive' =>'y'
        );

    return $ci->procurement_type_m->get_where($where);
}

