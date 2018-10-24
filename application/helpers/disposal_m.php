<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Disposal_m extends MY_Model
{
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
    	$this->load->model('sys_file', 'sysfile', TRUE);
        parent::__construct();
    }
    public $_tablename='disposal_plans';
    public $_primary_key='id';
    #new pde 
    function insert_disposal($post){
    #	print_r($post); exit();		
	       
        $currency = mysql_real_escape_string($post['currency']);       
        $disposal_serial_number =  mysql_real_escape_string($post['disposal_serial_number']);        
        $subject_of_disposal =  mysql_real_escape_string($post['subject_of_disposal']);        
        $asset_location =  mysql_real_escape_string($post['asset_location']);		
		$amount =  mysql_real_escape_string($post['amount']);		
		$disposal_plan = mysql_real_escape_string($post['disposal_plan']);	
		
		$strategic_asset  = mysql_real_escape_string($post['strategic_asset']);
		$strategic_asseta = 'N';

		if($strategic_asset == 'Yes'){
			$date_of_approval = 	mysql_real_escape_string($post['date_of_approval']);	
			$strategic_asseta = 'Y';
	     }
	    else
	    $date_of_approval = '';

		$method_of_disposal = mysql_real_escape_string($post['method_of_disposal']);
		$date_of_aoapproval = mysql_real_escape_string($post['date_of_aoapproval']); 
	  


	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');


     $data = array( 'pde' => $pde ,
		   'disposalplan' => $disposal_plan, 
           'disposalserialno' => $disposal_serial_number,
           'subjectofdisposal' =>$subject_of_disposal,           
           'assetlocation' => $asset_location, 
           'amount' => $amount,
           'currence' => $currency, 
           'strategicasset' => $strategic_asset,
           'dateofapproval' => $date_of_approval,
           'methodofdisposal' =>  $method_of_disposal,
           'isactive' => 'Y',   
           'author' => $userid,   
           'dateadded' => date("Y-m-d H:i:s"),
           'dateofaoapproval' => $date_of_aoapproval  
		  
       );
 
	 $query = $this->Query_reader->get_query_by_code('add_disposal_record',  $data);
	 #print_r($query); exit();
	 $result = $this->db->query($query); 
	 // if uploaded detailed plan insert into the db::
	 $dispsalplan =  $this->db->insert_id();
	 $this->session->set_userdata('usave', 'You have successfully Saved a disposal record  '.$disposal_serial_number.' ' ); 

	 return 1;

    }

    #update disposal record ::
    function update_disposal($post){

    	$currency = mysql_real_escape_string($post['currency']);       
        $disposal_serial_number =  mysql_real_escape_string($post['disposal_serial_number']);        
        $subject_of_disposal =  mysql_real_escape_string($post['subject_of_disposal']);        
        $asset_location =  mysql_real_escape_string($post['asset_location']);		
		$amount =  mysql_real_escape_string($post['amount']);		
		$disposal_plan = mysql_real_escape_string($post['disposal_plan']);	
		
		$strategic_asset  = mysql_real_escape_string($post['strategic_asset']);
		$strategic_asseta = 'N';
		
		if($strategic_asset == 'Yes'){
			$date_of_approval = 	mysql_real_escape_string($post['date_of_approval']);	
			$strategic_asseta = 'Y';
	     }
	    else
	    $date_of_approval = '';


		 

		$method_of_disposal = mysql_real_escape_string($post['method_of_disposal']);
		$date_of_aoapproval = mysql_real_escape_string($post['date_of_aoapproval']); 
	  


	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');
    $disposalrecodid = $post['disposalrecordid'];

     $data = array( 'pde' => $pde ,
		   'disposalplan' => $disposal_plan, 
           'disposalserialno' => $disposal_serial_number,
           'subjectofdisposal' =>$subject_of_disposal,           
           'assetlocation' => $asset_location, 
           'amount' => $amount,
           'currence' => $currency, 
           'strategicasset' => $strategic_asseta,
           'dateofapproval' => $date_of_approval,
           'methodofdisposal' =>  $method_of_disposal,
           'isactive' => 'Y',   
           'author' => $userid,   
           'dateadded' => date("Y-m-d H:i:s"),
           'dateofaoapproval' => $date_of_aoapproval ,
           'id'=> $disposalrecodid
		  
       );
 
	 $query = $this->Query_reader->get_query_by_code('update_disposal',  $data);
	 #print_r($query); exit();
	 $result = $this->db->query($query); 
	 // if uploaded detailed plan insert into the db::
	 $dispsalplan =  $this->db->insert_id();
	 $this->session->set_userdata('usave', 'You have successfully updated a disposal record  '.$disposal_serial_number.' ' ); 

	 return 1;
    }

    
	#check if the disposal record already exists
	function check_disposal_record($post)
	{
		$disposal_ref_no  = $post['itemcheck'];
		 $query = mysql_query("select * from disposal_record where disposal_ref_no = '".$disposal_ref_no."'");
		 $rows = mysql_num_rows($query);
		 return $rows;
		 
	}
	#fetch disposal records
	function fetch_disposal_records($data,$searchstring)
	{
	 $result = paginate_list($this, $data, 'fetch_disposal_records', array('searchstring'=>$searchstring),10);     
	 
    return $result;				
	}
	function search_disposal_records($data,$search){ 
	$searchstring = "1 and 1 and disposal_record.disposal_ref_no like '%".$search."%' or disposal_record.subject_of_disposal  like '%".$search."%'  or  disposal_record.asset_location like '%".$search."%'  or   disposal_record.reserve_price='".$search."'  or disposal_record.currence  like '%".$search."%'  or  disposal_record.dateadded  like '%".$search."%'  or  pdes.abbreviation    like '%".$search."%'  or pdes.pdename    like '%".$search."%'  order by   disposal_record.dateadded DESC";
	#$searchstring = "1 and 1 order by   disposal_record.dateadded DESC";
	 $result = paginate_list($this, $data, 'fetch_disposal_records', array('searchstring'=>$searchstring),10);   
	 return $result;
	}
	function insert_bid_invitation($post){
		
	 	#print_r($post); exit();
		$disposal_record = mysql_real_escape_string($post['disposal_serial_no']);
        $disposal_reference_no =   mysql_real_escape_string($post['disposal_reference_no']);
		#$post['bid_document_issue_date'];
	
        $bid_document_issue_date =   date('Y-m-d',strtotime($post['bid_document_issue_date']));
        $deadline_for_submition =   date('Y-m-d',strtotime($post['deadline_for_submition']));  
          
		$cc_date_of_approval =  mysql_real_escape_string($post['cc_date_of_approval']);
		#$bid_date_array = explode('-',$bid_duration);
		
		$bid_opening_date  =  $bid_document_issue_date;
		$date_of_approval_form28 = date('Y-m-d',strtotime($post['date_of_approval_form28']));
		$date_of_initiation_form28 = date('Y-m-d',strtotime($post['date_of_initiation_form28']));

 
		#print_r($bid_opening_date); exit();
		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');
		$difference = strtotime($deadline_for_submition) - strtotime($bid_document_issue_date);		
		$days_between =  floor($difference/(60*60*24)); 	
      //print_r($days_between); exit();  
	      if($days_between <= 0)
	      {
	      	print_r("Invalid Date Ranges between Bid Document Issue Date and Deadline of Submition");
	      	exit();
	      }
			# documentissuedate' => $days_between,     
     		$data = array( 'disposalserialno' => $disposal_record,
           'disposalrefno' =>   $disposal_reference_no,           
           'documentissuedate' =>$bid_document_issue_date,               
           'bidopeningdate' => $bid_opening_date,
		   'bidduration' => $days_between,		   
           'ccapprovaldate' => $cc_date_of_approval,
		   'isactive' => 'Y' ,
		   'author' => $userid ,
		   'dateadded' => date("Y-m-d H:i:s"),       
		   'dateofapprovalform28'=> $date_of_approval_form28,
		   'dateofinitiationform28' =>$date_of_initiation_form28      
            );

      if(!empty($post['update']))
        {
  $data['id'] = $post['update'];
  $query = $this->Query_reader->get_query_by_code('update_disposal_bids',  $data); 
  $result = $this->db->query($query);  
  $this->session->set_userdata('usave', 'You have successfully updated   Bid Invitation '); 	
        }
        else{ 
  $query = $this->Query_reader->get_query_by_code('add_disposal_bids',  $data); 
  $result = $this->db->query($query);  
  $this->session->set_userdata('usave', 'You have successfully Saved   Bid Invitation '); 
			}
 

	return 1;

	}
	
	
	function fetch_active_disposal_records($data,$searchstring){
	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');	
	#$query = $this->Query_reader->get_query_by_code('active_disposal_records',array('searchstring'=>'and disposal_record.isactive="Y" and disposal_plans.isactive="Y" and  users.pde = '.$pde.'','limittext'=>'limit 10',)); 
	#print_r($query); exit();
    $result = paginate_list($this, $data, 'active_disposal_records', array('searchstring'=>'and disposal_record.isactive="Y" and disposal_plans.isactive="Y" and  users.pde = '.$pde.''),1000);   
	 
    return $result;	
	}
	#fetch disposal records
	function fetch_disposal_bid_invitations($data,$searchstring)
	{
	#	$query = $this->Query_reader->get_query_by_code('view_disposal_bid_invitations',array('searchstring'=>$searchstring,'limittext'=>'limit 10','ORDERBY'=>'')); 
		#print_r($query); exit();
	 $result = paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring,'ORDERBY'=>''),10);     
	 
    return $result;				
	}
    
	#SEARCH BID INVITATIONS
    function search_disposal_bid_invitations($data,$search){ 
	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');	
	$searchstring = "1 and 1 and disposal_record.disposal_ref_no like '%".$search."%' or disposal_record.subject_of_disposal  like '%".$search."%'  or   disposal_bid_invitation.bid_document_issue_date like '%".$search."%'  or    disposal_bid_invitation.bid_opening_date like'%".$search."%'  or 
	disposal_bid_invitation.subject_of_disposal like'%".$search."%'  or     
		disposal_bid_invitation.description like'%".$search."%'  or 
		   disposal_bid_invitation.bid_duration ='".$search."'  or  disposal_bid_invitation.dateadded  like '%".$search."%'  and users.pde ='".$pde."' and users.userid='".$userid."'   order by   disposal_bid_invitation.dateadded DESC";
	#$searchstring = "1 and 1 order by   disposal_record.dateadded DESC";
	 $result = paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring),10);   
	 return $result;
	}

	#SAVE BID RESPONSE
	function savebidresponse($post){
	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');
    $dispossalrefno = mysql_real_escape_string($post['dispossalrefno']);
    $serviceprovider = mysql_real_escape_string($post['serviceprovider']);
    $nationality = mysql_real_escape_string($post['nationality']);
    $readoutprice = mysql_real_escape_string($post['readoutprice']);
    $datesubmitted =  date('Y-m-d',mysql_real_escape_string(strtotime($post['datesubmitted'])));
    $receivedby = mysql_real_escape_string($post['receivedby']);
	//provider id
	$providerid = 0;
	 
	// save information 
		// check if name exists in the  providers table

		$query = mysql_query("SELECT * FROM providers where providernames  like '".$serviceprovider."'  limit 1") or die("".mysql_error());
		 $providenms  ='';
		if(mysql_num_rows($query) > 0)
		{
			while ($res = mysql_fetch_array($query)) {
				# code...
				$providerid = $res['providerid'];
				$providenms = $res['providernames'];
			}
		}
		else{
			$query = mysql_query("INSERT INTO providers(providernames) values ('".$serviceprovider."') ") or die("".mysql_error());
			$providerid = mysql_insert_id();
		}
		//get bid information ::
		//	$bidid  = 0;
		$query = mysql_query("SELECT a.* FROM disposal_bid_invitation   as a  inner join disposal_record as b on a.disposal_ref_no = b.id where b.disposal_ref_no like '".$dispossalrefno."' limit 1") ;
		# print_r($query);
		while ($res = mysql_fetch_array($query)) {
			# code...
			$bidid = $res['id'];
		}
  # exit( '3:'.$dispossalrefno);
		//check to see if the provider already submitted
		$query = mysql_query("SELECT * FROM bid_response WHERE  provider_id = ".$providerid." and  bid_invitation = ".$bidid."") or die("".mysql_error());
		 
		if(mysql_num_rows($query) > 0){
			return "3:".$providenms." Have a bid proposal existing on  Disposal Ref No - ".$dispossalrefno ;
		}
			else{
		//save the bid
		$query =mysql_query("INSERT INTO bid_response(bid_invitation,provider_id,nationality,readoutprice,receivedby,author,datesubmitted) values('".$bidid."','".$providerid."','".$nationality."','".$readoutprice."','".$receivedby."','".$userid."','".$datesubmitted."') ") or die("".mysql_error());
		// echo $query;
		// exit();
		if($query)
		{
			return 1;
		}else
		{
			return 0 ;
		}




	//bid response information //
	return $post;
		}


	}
	#UPDATE BID RESPONSE
	function update_bid_response(){}
	function save_disposal_plan($post){


	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');	
	

	$data = array( 'pde' => $pde,
           'financialyear' =>   $post['start_year'].'-'.$post['end_year'],           
           'title' => $post['title'], 
           'author' => $userid,          
           'details' => $post['description']		        
            );


	$query = $this->Query_reader->get_query_by_code('add_disposal_plan',  $data); 
    $result = $this->db->query($query);    
	$this->session->set_userdata('usave', 'You have successfully created the '.$post['title'].' ' .  $post['start_year'].'-'.$post['end_year'] . ' Disposal Plan'); 
	$insertid = $this->db->insert_id();
	return $insertid;

	}

	#update disposal plan
	function update_disposal_plan($post,$id){


	$pde =  $this->session->userdata('pdeid');
	$userid =  $this->session->userdata('userid');	
	

	$data = array( 'pde' => $pde,
           'financialyear' =>   $post['start_year'].'-'.$post['end_year'],           
           'title' => $post['title'], 
           'author' => $userid,          
           'details' => $post['description'],
           'searchstring'=> 'AND disposal_plans.id = '.$id	        
            );
	

	$query = $this->Query_reader->get_query_by_code('update_disposal_plan',  $data); 
    $result = $this->db->query($query);    
	$this->session->set_userdata('usave', 'You have successfully updated  the '.$post['title'].' ' .  $post['start_year'].'-'.$post['end_year'] . ' Disposal Plan'); 
	 
	return 1;

	}



	function fetch_disposal_plans($data,$searchstring)
	{
	   	# $data = array('searchstring' => $searchstring,'limittext'=>' limit 10');
		# $query = $this->Query_reader->get_query_by_code('view_disposal_plans',  $data); 
		# print_r($query);
		# exit();
		 $result = paginate_list($this, $data, 'view_disposal_plans', array('searchstring'=>$searchstring),10);      
	     # print_r($result); exit();
         return $result;	
	}
	function fetchdisposal_plans($data,$searchstring)
	{
		
		 $datar = array('searchstring' => $searchstring,'limittext'=>' ');
		  $query = $this->Query_reader->get_query_by_code('view_disposal_plans',  $datar); 
		   #print_r($query);
		   #exit();
		   $result = $this->db->query($query)->result_array(); 
		   return $result;
		  
	}
	 #fetch Dipsosal Response
	function fetch_disposal_reference($data,$searchstring)
	{
	 $result = paginate_list($this, $data, 'fetch_bid_responses', array('searchstring'=>$searchstring),10);    
	 
    return $result;				
	}
	function fetch_disposal_methods($data,$searchstring,$limittext)
	{
		$result = paginate_list($this, $data, 'fetch_disposal_methods', array('searchstring'=>$searchstring),$limittext);  
	   #$query = $this->Query_reader->get_query_by_code('fetch_disposal_methods',  array('searchstring'=>$searchstring,'limittext'=>'')); 
       #print_r($query); exit();
        return $result;	
		
	}
	function fetch_disposal_details($data,$searchstring,$limittext){
		$result = paginate_list($this, $data, 'active_disposal_reference_numbers', array('searchstring'=>$searchstring),$limittext);  
	   # $query = $this->Query_reader->get_query_by_code('active_disposal_reference_numbers',  array('searchstring'=>$searchstring,'limittext'=>'')); 
      # print_r($query); exit();
       return $result;	
	}

	# FETCH Best Evaluated Bidder  LIST
    function fetch_beb_list($idx = 0,$data=array()){

		switch ($idx) {
			case 0:
			$userid = $this->session->userdata['userid'];
			$pde = mysql_query("select * from  users where userid =".$userid);
			$q = mysql_fetch_array($pde);

			$result = paginate_list($this, $data, 'view_disposal_bebs',  array('SEARCHSTRING' => '  1 and 1 and 	users.userid = '.$userid.' ' ),200);

		#$query = $this->Query_reader->get_query_by_code('view_disposal_bebs', array('SEARCHSTRING' => '  1 and 1 and 	users.userid = '.$userid.' ORDER BY bestevaluatedbidder.dateadded DESC ' ,'limittext' => ' '));
	    #print_r($query); exit();
		#$result = $this->db->query($query)->result_array();
		return $result;


   	  return $result;

		break;

			default:
				# code...
				break;
		}
	}
	function checkfinancialyears($post)
	{
		  $yeart = $string = preg_replace('/\s+/', '', mysql_real_escape_string($post['financialyear']));
		  $decission = $this->uri->segment(3);
		  $userid = $this->session->userdata['userid'];
			$pde = mysql_query("select * from  users where userid =".$userid);
			$q = mysql_fetch_array($pde);			 
			$pdeid = $q['pde'];

		#  print_r($decission); exit();
		  $query = '';
		  switch ($decission) {
				case 'update':
					 $id = $this->uri->segment(4);
				     $datar = array('year' => $yeart.'-','SEARCHSTTRING'=>' AND disposal_plans.isactive ="Y" AND disposal_plans.id != '.$id,'PDE'=>$pdeid);
					 $query = $this->Query_reader->get_query_by_code('checkfinancial_year',  $datar); 
					  $result = $this->db->query($query)->result_array(); 

		  if(count($result) > 0 )	
		  {	
		  return 1; 	
		  }
		  else{
		  return 0;
		  }	 
				break;
				
				default:
				     $datar = array('year' => $yeart.'-','SEARCHSTTRING'=>' AND disposal_plans.isactive ="Y" ','PDE'=>$pdeid);
					 $query = $this->Query_reader->get_query_by_code('checkfinancial_year',  $datar); 
			  # print_r($query); exit();
			   $result = $this->db->query($query)->result_array(); 

		  if(count($result) > 0 )	
		  {	
		  return 1; 	
		  }
		  else{
		  return 0;
		  }	 
					break;
		  }

		
		 
	}
	
	function getserialnumber($pde)
	{
		$pdeinfo = $this->db->query("SELECT * FROM pdes where pdeid = ".$pde." limit 1 ")->result_array();
		$code = '';
		$code = $pdeinfo[0]['code'];
		$serial = $code.'/disposal/';
		return $serial;
		 
	}

	 # 3 in one del archive and restore
     function remove_restore_disposalplan($type,$pdetypeid){

        switch ($type) {
           
            case 'delete':
             $query = $this->Query_reader->get_query_by_code('archive_diposal_plans', array('ID'=>$pdetypeid,'ISACTIVE'=>'N'));
             $result = $this->db->query($query);
             if($result)             
                return 1;
            break;          
            default:
                # code...
                break;
        }

    }

     # 3 in one del archive and restore
     function remove_restore_disposalplan_record($type,$pdetypeid){

        switch ($type) {
           
            case 'delete':
             $query = $this->Query_reader->get_query_by_code('archive_diposal_plans_records', array('ID'=>$pdetypeid,'ISACTIVE'=>'N'));
             $result = $this->db->query($query);
             if($result)             
                return 1;
            break;          
            default:
                # code...
                break;
        }

    }



    public function get_disposal_plan_info($id='', $param='')
    {

        //if NO ID
        if($id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where('id',$id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {
                        case 'pde_id':
                            $result=$row['pde_id'];
                            break;
                        case 'pde':
                            $result=get_pde_info_by_id($row['pde'],'title');
                            break;
                        case 'financial_year':
                            $result = $row['financial_year'];
                            break;


                        case 'description':
                            $result=$row['description'];
                            break;


                        case 'isactive':
                            $result=$row['isactive'];
                            break;

                        case 'title':
                            $result=$row['title'];
                            break;


                        default:
                            $result=$query->result_array();
                    }

                }

                return $result;
            }

        }
    }








}