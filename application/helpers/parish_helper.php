<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 22:26
 */


function get_parish_by_id($id,$param='')
{
    $ci=& get_instance();
    $ci->load->model('parish_m');

    return $ci->parish_m->get_parish_by_id($id,$param);

}

function get_active_parishes()
{
    $ci=& get_instance();
    $ci->load->model('parish_m');

    return $ci->parish_m->get_all();
}

function get_parishes_by_sub_county($sub_county_id)
{
    $ci=& get_instance();
    $ci->load->model('parish_m');

    $where=array
    (
        'sub_county_id'=>$sub_county_id,
        'trash'      =>'n'
    );

    return $ci->parish_m->get_where($where);
}

