<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 3/4/2015
 * Time: 6:30 AM
 */
//get user type y id
function get_usergroup_info($group_id, $param = '')
{
    $ci =& get_instance();
    $ci->load->model('usergroups_m');

    return $ci->usergroups_m->get_usergroup_info_by_id($group_id, $param);

}

function get_usergroup_by_user($user_id)
{
    $ci =& get_instance();
    $ci->load->model('usergroups_m');
    $ci->load->model('role_m');
    $where = array(
        'userid' => $user_id,
        'isactive' => 'Y'
    );
    $group = array();
    foreach ($ci->role_m->get_where($where) as $roles_row) {
        $group[] = $roles_row['groupid'];
    }
    if (count($group)) {
        foreach (array_slice($group, 0, 1) as $row) {
            return get_usergroup_info($row, 'title');
        }
    } else {
        return 'Unassigned member';
    }


}