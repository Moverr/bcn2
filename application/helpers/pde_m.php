<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Pde_m extends MY_Model
{
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }
    public $_tablename='pdes';
    public $_primary_key='pdeid';
    #new pde 
    function insert_pde($pdename,$pdeabbreviation,$pdecategory,$pdetype,$pdecode,$pderollcat,$pdeaddress,$pdetel,$pdefax,$pdeemail,$pdewebsite, $roles,$step){
    	//pdename<>*pdeabbreviation<>*pdecategory<>*pdetype<>*pdecode<>pderollcat<>pdeaddress<>pdetel<>pdefax<>pdeemail<>pdewebsite<>pdeao<>pdeaophone<>pdecc<>pdeccphone<>pdeccemail<>pdeheadpduphone<>pdeheadpduemail<>pdeheadpdu

    if($step == 1){
            $data = array(   
           'pdename' => $pdename ,
           'abbreviation' => $pdeabbreviation,
           'status' =>'in',
           //'create_date' => NOW(),// current date time stamp
           'createby' => 1, // get the currently logged in session :: user id 
           'category' => $pdecategory,
           'type' => $pdetype,
           'code' => $pdecode,
           'pderollcat' => $pderollcat,
           'address' => $pdeaddress,
           'tel' => $pdetel,
           'fax' => $pdefax,
           'email' => $pdeemail,
           'website' => $pdewebsite,
         
        	);

   $query = $this->Query_reader->get_query_by_code('insertnewpde',  $data);
   $result = $this->db->query($query);
  
   $insert_id = mysql_insert_id();
   $this->session->set_userdata('insertid',$insert_id);

   
   

  return 1;
          }
      
      else  if($step == 2){

            $insertid =   $this->session->userdata('insertid');
            //$this->session->userdata('insertid');
            $roles = $roles;           
            foreach ($roles as $key => $value) {
              # code...  
             $data  = array('role' => $key, 'pdeid' => $insertid,'userid' => $value,'issactive'=>'Y' ,'');
             $query = $this->Query_reader->get_query_by_code('assignrole',  $data);    
             //print_r($query); exit();          
             $result = $this->db->query($query);
             $query = mysql_query("UPDATE users set pde = ".$insertid." where userid =".$value) or die('Insert Failed Uers');
            
            }
            
                        
              # UNSET THE SESSION ID AND CONTINUE
             // $this->session->unset_userdata('insertid');
              return 1;
          }
          else{
            return 0;
          }

    }
    #safe delete or archive /restore pde 
    /*
    ARCHIVE AND DELETE PDE
    */
    function remove_restore_pde($type,$pdeid){

    	switch ($type) {
    		case 'restore':
    			# code...
    		 $query = $this->Query_reader->get_query_by_code('archive_restorepdes', array('ID'=>$pdeid,'STATUS' => 'in' ));
			$result = $this->db->query($query);
			if($result)			 
			 	return 1;

    			break;
    	    case 'archive':    	    
    	    $query = $this->Query_reader->get_query_by_code('archive_restorepdes', array('ID'=>$pdeid,'STATUS' => 'out' ));
			   // print_r($query);
          $result = $this->db->query($query);
			 if($result)			 
			 	return 1;
			else
			 return 0;			
    	    break;  
    	    case 'delete':
    	     $query = $this->Query_reader->get_query_by_code('deletepde', array('ID'=>$pdeid));
			 $result = $this->db->query($query);
			 if($result)			 
			 	return 1;
    	    break;  		
    		default:
    			# code...
    			break;
    	}

    }

  function parmanentdel_pde(){

    }
   
    #edit PDE
    function updatepde($pdename,$pdeabbreviation,$pdecategory,$pdetype,$pdecode,$pderollcat,$pdeaddress,$pdetel,$pdefax,$pdeemail,$pdewebsite,$pdeid,$step,$roles){
    	
if($step == 1){
            $data = array(   
           'pdename' => $pdename ,
           'abbreviation' => $pdeabbreviation,
           'status' =>'in',
           //'create_date' => NOW(),// current date time stamp
           'createby' => 1, // get the currently logged in session :: user id 
           'category' => $pdecategory,
           'type' => $pdetype,
           'code' => $pdecode,
           'pderollcat' => $pderollcat,
           'address' => $pdeaddress,
           'tel' => $pdetel,
           'fax' => $pdefax,
           'email' => $pdeemail,
           'website' => $pdewebsite ,
            'id' => $pdeid        
          );
            //$query = mysql_query("UPDATE pdes SET pdename, abbreviation, status, type")
            $query = $this->Query_reader->get_query_by_code('updatepde',  $data);  

           $result = $this->db->query($query);
            //updatepdestep1
}
 else  if($step == 2){

            $sq = mysql_query("DELETE FROM roles where roles.pdeid = ".$pdeid);

             $roles = $roles;
              foreach ($roles as $key => $value) {
              # code...           
              $zat = explode("_", $key) ;
              $data  = array('role' => $zat[1], 'pdeid' => $pdeid,'userid' => $value,'issactive'=>'Y' ,'');
              $query = $this->Query_reader->get_query_by_code('assignrole',  $data);    
             //print_r($query); exit();          
             $result = $this->db->query($query);
             $query = mysql_query("UPDATE users set pde = ".$pdeid." where userid =".$value) or die('Insert Failed Uers');
   
            }
            return 1;

           // $query = $this->Query_reader->get_query_by_code('updatepdestep2',  $data);  
            
    }



	return 1;

    }


    #handle server side validation for Pdes :: 
    function validate_pdes($formdata,$id =0,$datatype=''){
       
       $pdeid =  $id;
       $str = 0;


	       	switch ($datatype) {
	       		case 'insert': 

	       			# code...
		       		if(!empty($formdata['pdename']))
			          {
			                   # searchpde   
			               $searchstring = "pdename like '".mysql_real_escape_string($formdata['pdename'])."' "  ; 
			               $result  = $this-> db_check_pdes($searchstring);
			               if(count($result) > 0){
			               $str = 'PDE Name Already  Exists in the Database ';
			               
			               }
			          }
                   return($str);  
			         
	       			break;
	       		case 'update':
	       			# code...

			       		if(!empty($formdata['pdename']))
					       {					       
					                # searchpde   
					               $searchstring = "pdename like '".mysql_real_escape_string($formdata['pdename'])."' and pdeid != ".$pdeid ;
					               $result  = $this-> db_check_pdes($searchstring);
					               if(count($result) > 0){
					               	 $str = 'PDE Name Exists in the Database  for a Different PDE Authority';
			               	 		return($str);	
					               } 
					                
					        }
	       			break;
	       		
	       		default:
	       			# code...
	       			break;
	       	}
       		return $str;
    }
    //check pde based on a given where clause  and return outcome:
    //majorly used for validation :: SS
    function db_check_pdes($searchstring = '')
    {

   	$query = $this->Query_reader->get_query_by_code('searchpde', array('SEARCHSTRING' => $searchstring ) );    	       	 
		$result = $this->db->query($query)->result_array();
		return $result;    	
    }
  //Fetch  on or Many depending :: 
    function fetch_pdes($status='in',$data){
    	$result = paginate_list($this, $data, 'fetchpdes', array('STATUS'=>$status, 'orderby'=>' ORDER BY create_date DESC' ,'searchstring'=>''),10);         	
    return $result;
    }

    #search pdes
    function search_pdes($s,$data,$search)
    {
     
            $search_string = 'and ( pdes.pdename   like "%'. $search .
              '%" OR pdes.abbreviation like "%'. $search .'%" OR  pdes.code like "%'.
              $search .'%")'; 
              $result = paginate_list($this, $data, 'fetchpdes', array('STATUS'=>'', 'orderby'=>' ORDER BY create_date ' ,'searchstring'=> $search_string),200);           

    return $result;
    }


    #fetchj detailed info
    function fetchdetail($idx = 0){
          $query = $this->Query_reader->get_query_by_code('pdedetails', array('id' => $idx) ); 
       
      $result = $this->db->query($query)->result_array();
      return $result;
    }
    #Fetch Pde Details
    function fetchpdedetails($idx = 0,$data = 0)
    {    	
    	#Get the paginated list of users
      $query = $this->Query_reader->get_query_by_code('pdedetails', array('id' => $idx) ); 
       
      $result = $this->db->query($query)->result_array();
      return $result;
    }
    //fetch roles associated to a given pde:

    # user assigned roles
    function fetchroles($pdeid = 0){

      $query = $this->Query_reader->get_query_by_code('assignedroles', array('searchstring' => '1 and 1') );  
     #print_r($query); exit();
      $result = $this->db->query($query)->result_array();
      
      return $result;
     
    }


    function fetchallroles()
    {
       $query = $this->Query_reader->get_query_by_code('allroles' );  
      $result = $this->db->query($query)->result_array();
      
      return $result;
    }

    function get_pde_info($id,$param)
    {
        $query_data=array
        (
            'pdeid'=>$id
        );
        //run query
        $query = $this->db->select()->from('pdes')->where($query_data)->get();

        $info_array=$query->result_array();

        //if there is a result
        if(count($info_array))
        {

            foreach($info_array as $row)
            {
                switch ($param)
                {
                    case 'pdename':
                        $result=$row['pdename'];
                        break;
                    case 'title':
                        $result=$row['pdename'];
                        break;
                    case 'pde name':
                        $result=$row['pdename'];
                        break;
                    case 'abbreviation':
                        $result=$row['abbreviation'];
                        break;

                    case 'abbrv':
                        $result=$row['abbreviation'];
                        break;
                    case 'status':
                        $result=$row['status'];
                        break;
                    case 'category':
                        $result=$row['category'];
                        break;
                    case 'type':
                        $result=$row['type'];
                        break;
                    case 'code':
                        $result=$row['code'] ;
                        break;
                    case 'pde_roll_cat':
                        $result=$row['pde_roll_cat'];
                        break;
                    case 'address':
                        $result=$row['address'];
                        break;
                    case 'telephone':
                        $result=$row['tel'];
                        break;
                    case 'fax':
                        $result=$row['fax'];
                        break;
                    case 'email':
                        $result=$row['email'];
                        break;
                    case 'website':
                        $result=$row['website'];
                        break;
                    case 'ao':
                        $result=$row['ao'];
                        break;
                    case 'ao_phone':
                        $result=$row['ao_phone'];
                        break;
                    case 'ao_email':
                        $result=$row['ao_email'];
                        break;
                    case 'cc':
                        $result=$row['cc'];
                        break;
                    case 'cc_phone':
                        $result=$row['cc_phone'];
                        break;
                    case 'cc_email':
                        $result=$row['cc_email'];
                        break;
                    case 'head_PDU':
                        $result=$row['head_PDU'];
                        break;
                    case 'head_PDU_phone':
                        $result=$row['head_PDU_phone'];
                        break;
                    case 'head_PDU_email':
                        $result=$row['head_PDU_email'];
                        break;

                    default:
                        //no parameter is passed display all user info
                        $result=$info_array;
                }
            }

            return $result;

        }
        else
        {
            return NULL;
        }

    }
    
     //get by passed parameters
    public function get_where($where)
    {
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key,'DESC')->get();


        return $query->result_array();
    }

    

}