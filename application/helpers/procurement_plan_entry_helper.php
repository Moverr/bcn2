<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 1/30/2015
 * Time: 12:35 PM
 */
function get_active_procurement_plan_entries_by($plan_id)
{
    $ci =& get_instance();
	$procurement_plan_entries = $ci->db->query($ci->Query_reader->get_query_by_code('procurement_entries', array('limittext'=>'', 'orderby'=>'PPE.dateadded ASC', 'searchstring'=>' AND PP.id = "'. $plan_id.'"')))->result_array();
	
	return $procurement_plan_entries;
}

function procurement_plan_ref_number_hint($pde, $procurement_type, $financial_year, $procurement_plan,$offline_val)
{
    $ci =& get_instance();
    $ci->load->model('procurement_plan_entry_m');

    //number sections
    $pde_abbrv = seo_url(get_pde_info_by_id($pde, 'abbrv'));
	
	$procurement_type = is_numeric($procurement_type)?get_procurement_type_info_by_id($procurement_type,'title'):$procurement_type;
	
	switch($procurement_type)
	{
		case 'Works':
			$procurement_type = 'WRKS';
			break;
			
		case 'Supplies':
			$procurement_type = 'SUPPLS';
			break;
			
		case 'Services':
			$procurement_type = 'SRVCS';
			break;
			
		case 'Non consultancy services':
			$procurement_type = 'NCSRVCS';
			break;
	
	}
    

    $where = array
    (
        'procurement_plan_id' => $procurement_plan,
    );

    $last_instance = $ci->procurement_plan_entry_m->last_entered_procurement_ref_number($where);

    //echo $last_instance;
    $str = '';

    //if there is an instance
    if ($last_instance) {
        $pieces = explode('/', $last_instance);

        $str .= '000';


        $str .= $pieces[3] + 1;


        $instance = $str;
    } else {
        $instance = '0001';
    }


    return $pde_abbrv . '/' . $procurement_type . '/' . $financial_year . '/' . $instance. '/' . $offline_val;

}

function get_procurement_plan_entry_info($entry_id, $param)
{
    $ci =& get_instance();
    $ci->load->model('procurement_plan_entry_m');

    return $ci->procurement_plan_entry_m->get_plan_entry_info($entry_id, $param);
}


function format_excel_date($excel_date)
{
	$date_parts = explode('/', $excel_date);
	
	if(count($date_parts)>2)
	{
		$excel_date = '20' . $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
	}
	
	return $excel_date;
}

function days_time_left($expiry)
{

    $date=$expiry;//Converted to a PHP date (a second count)

//Calculate difference
    $diff=$date-time();//time returns current time in seconds
    $days=floor($diff/(60*60*24));//seconds/minute*minutes/hour*hours/day)
    $hours=round(($diff-$days*60*60*24)/(60*60));

//Report
    return "$days days $hours hours remain";
}

function get_procurement_plan_entry_info_reference_number($referenece_number, $param)
{
    $ci =& get_instance();
    $ci->load->model('procurement_plan_entry_m');

    return $ci->procurement_plan_entry_m->get_plan_entry_info_by_ref_num($referenece_number, $param);
}

function count_entries_by_procurement_plan($plan_id){
    $ci =& get_instance();
    $ci->load->model('procurement_plan_entry_m');
    $where=array(
        'procurement_plan_id'=>$plan_id,
        'isactive'=>'y'
    );

    return $ci->procurement_plan_entry_m->get_count_by_criteria($where);

}

function get_procurement_plan_entries_by_plan($plan_id){
    $ci =& get_instance();
    $ci->load->model('procurement_plan_entry_m');
    $where=array(
        'procurement_plan_id'=>$plan_id,
        'isactive'=>'y'
    );
    return $ci->procurement_plan_entry_m->get_where($where);


}

function get_active_procurement_plan_entries_by_pde($pde){
    $ci =& get_instance();
    $ci->load->model('procurement_plan_entry_m');

    return $ci->procurement_plan_entry_m->get_procurement_entries_by_pde($pde);


}

