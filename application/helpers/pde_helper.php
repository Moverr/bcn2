<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/22/2015
 * Time: 4:59 PM
 */
function get_pde_info_by_id($id,$param)
{
    $ci=& get_instance();
    $ci->load->model('pde_m');

    return $ci->pde_m->get_pde_info($id, $param);
}

function get_pde_id_by_abbreviation($abbrv)
{
    $ci=& get_instance();
    $ci->load->model('pde_m');

    $where=array
    (
        'abbreviation'  =>$abbrv
    );

    if($ci->pde_m->get_where($where))
    {
        foreach ($ci->pde_m->get_where($where) as $info)
        {
            return $info['pdeid'];
        }
    }
    else
    {
        return NULL;
    }




}

function get_active_pdes($category='')
{
    $ci=& get_instance();
    $ci->load->model('pde_m');

    return $ci->pde_m->get_active_pdes($category);
}





#Fetch Disposal Methods
function fetch_disposal_methods()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    return $ci->procurement_type_m->fetch_disposal_methods();
}



#Fetch Disposal Methods
function fetch_disposal_years()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    return $ci->procurement_type_m->fetch_disposal_years();
}