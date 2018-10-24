<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 22:26
 */

//get user type y id
function get_usertype_by_type_id($type_id,$param='')
{
    $ci=& get_instance();
    $ci->load->model('usertype_m');

    return $ci->usertype_m->get_usertypes_by_id($type_id,$param);

}

function get_active_usertypes()
{
    $ci=& get_instance();
    $ci->load->model('usertype_m');

    return $ci->usertype_m->get_all();
}


//get count by id
function get_usertypes_count_by_type($type)
{
    $ci=& get_instance();
    $ci->load->model('usertype_m');

    return $ci->usertype_m->all_count_by_type($type);
}

function get_usertype($id='',$param='')
{
    $ci=& get_instance();
    //load model
    $ci->load->model('usertype_m');

    return $ci->usertype_m->get_usertypes_by_id($id,$param);

}