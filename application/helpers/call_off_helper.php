<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 22:26
 */

function get_sum_of_contract_values($contract_id)
{
    $ci=& get_instance();
    $ci->load->model('call_off_m');

    return $ci->call_off_m->get_sum_of_contract_values($contract_id);

}


function get_sum_of_total_payments($contract_id)
{
    $ci=& get_instance();
    $ci->load->model('call_off_m');

     
    return $ci->call_off_m->get_sum_of_totalpayments($contract_id);

}


