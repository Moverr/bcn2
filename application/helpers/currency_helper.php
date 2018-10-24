<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/27/2015
 * Time: 10:12 AM
 */

function get_currency_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('currency_m');

    return $ci->currency_m->get_currency_info($id,$param);
}


function get_active_currencies()
{
    $ci=& get_instance();
    $ci->load->model('currency_m');
    $where=

        array
        (
            'isactive' =>'y'
        );

    return $ci->currency_m->get_where($where);
}

