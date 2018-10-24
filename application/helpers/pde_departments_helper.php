<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/27/2015
 * Time: 10:12 AM
 */

function get_pde_department_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('pde_department_m');

    return $ci->pde_department_m->get_pde_department_info($id,$param);
}


function get_active_pde_departments()
{
    $ci=& get_instance();
    $ci->load->model('pde_department_m');
    $where=

        array
        (
            'isactive' =>'y'
        );

    return $ci->pde_department_m->get_where($where);
}

