<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/22/2015
 * Time: 4:59 PM
 */
function get_disposal_contract_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('disposal_contract_m');

    return $ci->disposal_contract_m->get_disposal_contract_info($id, $param);
}

function get_disposal_contract_info_by_id_disposal_record($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('disposal_contract_m');

    return $ci->disposal_contract_m->get_disposal_contract_info_by_disposal_record($id, $param);
}

