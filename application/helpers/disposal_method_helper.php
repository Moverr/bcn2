<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/22/2015
 * Time: 4:59 PM
 */
function get_disposal_method_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('disposal_method_m');

    return $ci->disposal_method_m->get_disposal_method_info($id, $param);
}

function get_active_disposal_method()
{
    $ci=& get_instance();
    $ci->load->model('disposal_method_m');

    $where=

    array
    (
        'status' =>'Y'
    );

    return $ci->disposal_method_m->get_where($where);
}



