<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/22/2015
 * Time: 4:59 PM
 */
function get_disposal_record_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('disposal_record_m');

    return $ci->disposal_record_m->get_disposal_record_info($id, $param);
}

function get_disposal_record_info_by_disposal_plan($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('disposal_record_m');

    return $ci->disposal_record_m->get_disposal_record_info_by_disposal_plan($id, $param);
}



