<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 24/05/14
 * Time: 14:01
 */


function get_user_info_by_id($id, $param)
{
    $ci =& get_instance();
    $ci->load->model('users_m');

    return $ci->users_m->get_user_info($id, $param);
}



function get_active_users()
{
    $ci =& get_instance();
    $ci->load->model('users_m');
    $where = array
    (
        'isactive' => 'Y'
    );

    return $ci->users_m->get_where($where);
}




function login_status_checker()
{
    $ci =& get_instance();
    //if user is not logged in redirected to home page
    if (!$ci->session->userdata('userid')) {
        redirect(base_url());
    }
}

function check_my_access($permission_code = '')
{
    //if am an admin allow all
    $ci =& get_instance();
    $ci->load->model('role_m');
    //if its admin logged in
    if ($ci->session->userdata('isadmin') == 'Y') {
        return TRUE;
    } else {
        //echo $permission_code;
        //if no permission id is passed
        if (!$permission_code) {
            return FALSE;
        } else {
            //echo $permission_code;
            //echo 'foo';

            //get all current user's groups
            $my_groups = $ci->role_m->get_my_roles();
            //print_array($my_groups);
            if ($my_groups) {
                //todo based on assumption that a user can not be in 2 groups with conflicting permissions
                foreach ($my_groups as $group) {
                    //echo $group;
                    //get_permissions basket
                    $permission_basket = get_permissions_by_group($group);
                }

                //if there are no permissions
                if (!$permission_basket) {
                    //echo 'foo';
                    return FALSE;
                } else {
                    //if the permission_code is in basket then return true
                    if (in_array($permission_code, $permission_basket)) {
                        //print_array($permission_basket);
                        return $permission_basket;
                    } else {
                        //echo 'foo';
                        return FALSE;

                    }

                }

            } else {
                //if user has no group
                return FALSE;

            }

        }

    }
}

function get_permissions_by_group($group_id = '')
{
    //echo $group_id;
    $ci =& get_instance();
    $ci->load->model('groupaccess_m');
    $ci->load->model('permission_m');

    if (!$group_id) {
        return FALSE;
    } else {
        $where = array
        (
            'groupid' => $group_id,

        );
        //print_array($where);
        $result_set = $ci->groupaccess_m->get_where($where);
        //echo $ci->db->last_query();
        //if group has no permissions
        //print_array($result_set);
        if (!$result_set) {
            return FALSE;
        } else {
            //print_array($result_set);
            $permissions_basket = array();
            //return the permission ids
            foreach ($result_set as $row) {
                //resolve permission codes
                $permissions_basket[] = $ci->permission_m->get_permission_info_by_id($id = $row['permissionid'], $param = 'code');

            }

            //print_array($permissions_basket);

            return $permissions_basket;
        }

    }
}

function load_restriction_page()
{
    /*
     * NOTE SELF; THIS FUNCTION IS ONLY USEFUL INSIDE A CONTROLLER
     *
     */

    $ci =& get_instance();
    //show permission page
    //load plan registration form
    $data['page_title'] = 'Access Restricted ';
    $data['current_menu'] = 'view_procurement_plans';
    $data['view_to_load'] = 'error_pages/500_v';
    $data['view_data']['form_title'] = $data['page_title'];

    //massage to display
    $data['message'] = 'You do not have permission to view this page';

    $ci->load->view('dashboard_v', $data);
}

function get_all_session_data()
{
    $ci =& get_instance();
    return print_array($ci->session->all_userdata());

}

function get_users_by_group($group_id, $pde_id)
{
    if ($pde_id == '') {
        return NULL;
    } else {
        //echo $pde_id;
        $ci =& get_instance();
        $ci->load->model('role_m');
        $where =

            array
            (
                'groupid' => $group_id,
                'isactive' => 'Y'
            );
        $users = array();
        $users_unfiltered = array();
        foreach ($ci->role_m->get_where($where) as $roles_row) {
            //prevent dupes
            if (!in_array($roles_row['userid'], $users)) {
                $users_unfiltered[] = $roles_row['userid'];
            }

            foreach ($users_unfiltered as $user) {
                //if users pdes match
                if (get_user_info_by_id($user, 'pde_id') == $pde_id) {
                    //insert user id not already inserted
                    if (!in_array($user, $users)) {
                        $users[] = $user;
                    }
                }
            }

        }
        //this is an array of active users by group id in a given pde
        return $users;
    }


}

function get_users_by_pde($pde_id)
{
    $ci =& get_instance();
    $ci->load->model('users_m');
    $where = array
    (
        'isactive' => 'Y',
        'pde' => $pde_id
    );

    return $ci->users_m->get_where($where);
}

function get_users_by_usertype($group_id='')
{
    //echo $pde_id;
    $ci =& get_instance();
    $ci->load->model('role_m');
    if($group_id){
        $where =

            array
            (
                'groupid' => $group_id,
                'isactive' => 'Y'
            );
    }else{
        $where =

            array
            (
                
                'isactive' => 'Y'
            );
    }

    $users = array();
    $users_unfiltered = array();
    foreach ($ci->role_m->get_where($where) as $roles_row) {
        //prevent dupes
        if (!in_array($roles_row['userid'], $users)) {
            $users_unfiltered[] = $roles_row['userid'];
        }

        foreach ($users_unfiltered as $user) {
            //insert user id not already inserted
            if (!in_array($user, $users)) {
                $users[] = $user;
            }
        }

    }
    //this is an array of active users by group id in a given pde
    return $users;


}


function log_action($action='', $message='', $context='')
{
    $ci =& get_instance();
    $ci->load->model('accesstrail_m');

    return $ci->accesstrail_m->log_action($action,$message,$context);
}
