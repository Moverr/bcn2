<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 22:26
 */


function get_sub_county_by_id($type_id,$param='')
{
    $ci=& get_instance();
    $ci->load->model('sub_county_m');

    return $ci->sub_county_m->get_sub_county_by_id($type_id,$param);

}

function get_active_sub_counties()
{
    $ci=& get_instance();
    $ci->load->model('sub_county_m');

    return $ci->sub_county_m->get_all();
}

function get_sub_country_by_district($district_id)
{
    $ci=& get_instance();
    $ci->load->model('sub_county_m');

    $where=array
    (
        'district_id'=>$district_id,
        'trash'      =>'n'
    );

    return $ci->sub_county_m->get_where($where);
}

