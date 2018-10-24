<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/22/2015
 * Time: 4:59 PM
 */
function get_disposal_plan_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('disposal_m');

    return $ci->disposal_m->get_disposal_plan_info($id, $param);
}



