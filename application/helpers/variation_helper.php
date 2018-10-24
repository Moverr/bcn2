<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 22:26
 */

function get_variation($contract_id)
{
    $ci=& get_instance();
    $ci->load->model('variation_m');

    return $ci->variation_m->get_variation($contract_id);

}
