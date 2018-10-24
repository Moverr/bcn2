<?php

function get_project_by_type_id($project_id,$param='')
{
    $ci=& get_instance();
    $ci->load->model('project_m');

    return $ci->project_m->get_project_by_id($project_id,$param);

}

function get_active_projects()
{
    $ci=& get_instance();
    $ci->load->model('project_m');

    return $ci->project_m->get_all();
}
