<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/27/2015
 * Time: 10:12 AM
 */

function get_lot_by_id($id)
{
    $ci=& get_instance();
    $ci->load->model('lot_m');

    return $ci->lot_m->get_lot_info($id);
}


