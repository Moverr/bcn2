<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 5/16/14
 * Time: 8:22 AM
 *
 * controls notification related helper calls
 */
//get notification
function get_unseen_msg()
{
    $ci=& get_instance();
    $ci->load->model('notification_m');

    //the query
    return $ci->notification_m->get_unseen_messages();
}

function check_if_notification_duplicate($user_id,$notification)
{
    $ci=& get_instance();
    $ci->load->model('notification_m');

    //the query
    return $ci->notification_m->prevent_duplicate_msg($user_id,$notification);
}

function generate_notification($user_id,$msg)
{
    $ci=& get_instance();
    $ci->load->model('notification_m');

    $msg_data=array
    (
        'user_id'   =>$user_id,
        'content'   =>$msg
    );

    //the query
    return $ci->notification_m->create($msg_data);

}

//generate notification

//echo $message;


function send_notification($recipients_array, $title, $message_type, $message)
{
    $ci =& get_instance();
    $ci->load->model('notification_m');

    $piped_recipients = array_to_pipes($recipients_array);
    $message_data = array(
        'status' => 'unseen',
        'title' => $title,
        'msgtype' => $message_type,
        'body' => $message,
        'triggeredby' => $ci->session->userdata('userid'),
        'receipients' => $piped_recipients,
        'user_id' => '0',
        'dateupdated' => mysqldate(),
        'content' => '',
        'viewedby' => ''
    );
    return $ci->notification_m->create($message_data);
}


function count_new_notifications($userid)
{
 
     $ci=& get_instance();
     $ci->load->model('notification_m', 'notifications');
      $searchstring = array('SEARCHSTRING' => ' where  status not like "U" AND  A.userid = "'.$userid.'" ','SEARCHSTRING2'=>' AND notifications_recepients.recepient_id = '.$userid.'' );         
     return $ci->notifications->number_of_notifications($userid,$searchstring);
}


function count_other_notifications($userid,$level)
{
     $ci=& get_instance();
     $ci->load->model('notification_m', 'notifications');
    $searchstring = array('SEARCHSTRING' => ' where  status  like "'.$level.'"  AND  A.userid = "'.$userid.'"  ','SEARCHSTRING2'=>' AND notifications_recepients.recepient_id = '.$userid.'' );           
     return $ci->notifications->number_of_other_notifications($userid,$searchstring);
}


function count_cat_notifications($userid,$level)
{
     $ci=& get_instance();
     $ci->load->model('notification_m', 'notifications');
     $searchstring = array('SEARCHSTRING' => ' where  status like "'.$level.'" ','SEARCHSTRING2'=>' AND notifications_recepients.recepient_id = '.$userid.'' );      
     return $ci->notifications->number_of_notifications($userid,$searchstring);
}

function notification_list($userid,$limit)
{
     $ci=& get_instance();
     $ci->load->model('notification_m', 'notifications');
      $searchstring = array('SEARCHSTRING' => ' where  status like "R"  AND  A.userid = "'.$userid.'"   ','SEARCHSTRING2'=>' AND notifications_recepients.recepient_id = '.$userid.' LIMIT '.$limit );        
 
     return $ci->notifications->sample_new_notifications($userid,$searchstring); 
}
function update_status($userid,$notification,$status)
{

     $ci=& get_instance();
     $ci->load->model('notification_m', 'notifications');       
     return $ci->notifications->update_status($userid,$notification,$status); 
}

function push_permission($title,$body,$level,$permission,$entity='')
{
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');       
      return $ci->notifications->push_permission($title,$body,$level,$permission,$entity); 
}

function push_permission_all($title,$body,$level,$permission)
{
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');       
      return $ci->notifications->push_permission_all($title,$body,$level,$permission); 
}




function push_permission_ppda($title,$body,$level,$permission)
{
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');       
      return $ci->notifications->push_permission_ppda($title,$body,$level,$permission); 
}

function notifyropp($bidinvitation)
{
  $ci=& get_instance();
  $ci->load->model('notification_m', 'notifications');    
  #echo "Reached";   
  return $ci->notifications->notifyrop($bidinvitation); 
}


function weeklyreport()
{
 
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');   
    
    #exit("moeoe");
         $day = date("w");
        if($day == 2 || $day == 4)
         {      
         $level = 'ppda';       
           $ci->notifications->weeklyreport($level); 
         $level = 'ifb'; 
         $ci->notifications->weeklyreport($level); 
         }
         
        /* if($day == 7)
         {      
           $level = 'ppda';       
           $ci->notifications->weeklyreport($level); 
         $level = 'ifb'; 
         $ci->notifications->weeklyreport($level); 
         // $ci->notifications->weeklyreport($level); 
         } */
}







#pde list :: 
function get_pde_list()
{

     $ci=& get_instance();
    $ci->load->model('pde_m');

    return $ci->pde_m->get_pde_list();
}

#Fetch Pde tyoe List
function get_pdetype_list()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    return $ci->procurement_type_m->get_procurementtype_list();
}

#Fetch Funding Source List
function get_funding_source_list()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    return $ci->procurement_type_m->get_fundingsource_list();
}

#Fetch Procurement Method List
function get_procurement_method_list()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    return $ci->procurement_type_m->get_procurement_method_list();
}


#Fetch Procurement Financial Years List
function fetch_financialyears_list()
{
    $ci=& get_instance();
    $ci->load->model('procurement_type_m');
    return $ci->procurement_type_m->fetch_financialyears_list();
}


function weeklybebreport()
{
 
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');   
    
    #exit("moeoe");
         $day = date("w");
         $level = 'ppda';       
           $ci->notifications->weeklybebreport($level); 
        // $level = 'ifb'; 
        // $ci->notifications->weeklybebreport($level); 

          
      /* 
         if($day == 7)
         {      
           $level = 'ppda';       
           $ci->notifications->weeklyreport($level); 
         $level = 'ifb'; 
         $ci->notifications->weeklyreport($level); 
         // $ci->notifications->weeklyreport($level); 
         } */
}





function monthlyreportonexpiringcontracts()
{
 
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');   
    
    #exit("moeoe");
         $day = date("w");
         $level = 'ppda';       
        $ci->notifications->monthlyreportonexpiringcontracts($level); 
        
}


function awarded_beb_to_suspended_providers()
{
 
      $ci=& get_instance();
      $ci->load->model('notification_m', 'notifications');   
    
    #exit("moeoe");
         $day = date("w");
         $level = 'ppda';       
        $ci->notifications->awarded_beb_to_suspended_providers($level); 
        
}


#FETCH PROCUREMENT PLAN NOTIFICATIONS
function procurement_plan_notifications()
{
  $ci=& get_instance();
  $ci->load->model('notification_m', 'notifications'); 
 
  return $ci->notifications->entities_without_plans(); 
}

