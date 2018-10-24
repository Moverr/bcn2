<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 22:26
 */

//get user type y id
function get_district_by_id($type_id,$param='')
{
    $ci=& get_instance();
    $ci->load->model('district_m');

    return $ci->district_m->get_district_by_id($type_id,$param);

}

function get_active_districts()
{
    $ci=& get_instance();
    $ci->load->model('district_m');

    return $ci->district_m->get_all();
}

