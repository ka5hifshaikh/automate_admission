<?php


class Web_model extends CI_Model
{

	function getUserByCnic($cnic){
		$this->db->where('CNIC_NO',$cnic);
		$user = $this->db->get('users_reg')->row_array();
		return $user;
	}

	function getApplicationByUserId($cnic_no=0,$user_id=0,$application_id=0,$session_id=0){
		if ($cnic_no>0)
		{
			$user = $this->getUserByCnic($cnic_no);
			$user_id=$user['USER_ID'];
		}

		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('*,a.REMARKS');
		$this->legacy_db->from('applications a');
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = a.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`form_challan` fc","a.`APPLICATION_ID` = fc.`APPLICATION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`","LEFT");
		$this->legacy_db->where("a.USER_ID",$user_id);
		if($application_id>0)$this->legacy_db->where("a.APPLICATION_ID",$application_id);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		return $this->legacy_db->get()->result_array();

	}
	
	/*
	NEW METHODS YASIR 13-01-2021
	*/
	
	function get_candidate_objection_list($campus_id=0,$cnic_no=0,$user_id=0,$application_id=0,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0,$test_id=0,$is_provisional = 'Y',$list_no=0){
		if ($cnic_no>0)
		{
			$user = $this->getUserByCnic($cnic_no);
			$user_id=$user['USER_ID'];
			if (empty($user)) return null;
		}
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('slc.PROG_LIST_ID,a.APPLICATION_ID,a.USER_ID,ass.ADMISSION_SESSION_ID,pt.PROGRAM_TITLE,c.NAME,slc.CPN AS TEST_CPN,tr.CARD_ID,slc.FIRST_NAME,slc.LAST_NAME,slc.FNAME,slc.LIST_NO, slc.DISTRICT_NAME,slc.CATEGORY_NAME,slc.GENDER,slc.U_R,slc.CPN AS CPN_MERIT_LIST,slc.PROGRAM_TITLE,slc.SHIFT_ID,slc.CHOICE_NO,s.SHIFT_NAME,pt.PROGRAM_TITLE AS PROGRAM_TITLE_CATE,tr.DETAIL_CPN,app_status.STATUS_NAME,a.MESSAGE');
		$this->legacy_db->from('applications a');
		$this->legacy_db->join("`application_status` app_status","app_status.`STATUS_ID` = a.`STATUS_ID`");
// 		$this->legacy_db->join("`applied_shift` apps","apps.`APPLICATION_ID` = a.`APPLICATION_ID`");
	
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		
		$this->legacy_db->join("`selection_list_candidate` slc","slc.`APPLICATION_ID` = a.`APPLICATION_ID` AND tr.CARD_ID=slc.CARD_ID  AND tr.`TEST_ID`=slc.`TEST_ID`");
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = slc.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`shift` s","slc.`SHIFT_ID` = s.`SHIFT_ID`");
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`","LEFT");
		if ($user_id>0)$this->legacy_db->where("a.USER_ID",$user_id);
		
		if($is_provisional == 'Y')$this->legacy_db->where("slc.IS_PROVISIONAL='Y'");
		if($is_provisional == 'N')$this->legacy_db->where("slc.IS_PROVISIONAL='N'");
		
		if($application_id>0)$this->legacy_db->where("a.APPLICATION_ID",$application_id);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		if($test_id>0)$this->legacy_db->where("tr.TEST_ID",$test_id);
		if($campus_id>0)$this->legacy_db->where("c.CAMPUS_ID",$campus_id);
		if($list_no>0)$this->legacy_db->where("slc.LIST_NO",$list_no);
		
		$this->legacy_db->order_by("slc.CPN","DESC");
        
        if($cnic_no == "4170106388014"){
            
        }
        $data =  $this->legacy_db->get()->result_array();
            // prePrint($this->legacy_db->last_query());
            // exit();
		return $data;
	}
	function get_candidate_profile_display($campus_id=0,$cnic_no=0,$user_id=0,$application_id=0,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0,$test_id=0,$is_provisional = 'Y'){
	   	if ($cnic_no>0)
		{
			$user = $this->getUserByCnic($cnic_no);
			$user_id=$user['USER_ID'];
			if (empty($user)) return null;
		}
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('a.APPLICATION_ID,a.USER_ID,c.NAME,tr.CPN ,tr.CARD_ID,tr.DETAIL_CPN,app_status.STATUS_NAME,a.MESSAGE,a.FORM_DATA,tt.REMARKS as TEST_TYPE,tr.ACTIVE');
		$this->legacy_db->from('applications a');
		$this->legacy_db->join("`application_status` app_status","app_status.`STATUS_ID` = a.`STATUS_ID`");
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = a.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`test_type` tt","tr.`TEST_ID` = tt.`TEST_ID`");
		
		
		if ($user_id>0)$this->legacy_db->where("a.USER_ID",$user_id);
		
		if($application_id>0)$this->legacy_db->where("a.APPLICATION_ID",$application_id);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		if($test_id>0)$this->legacy_db->where("tr.TEST_ID",$test_id);
		if($campus_id>0)$this->legacy_db->where("c.CAMPUS_ID",$campus_id);
		
		//$this->legacy_db->order_by("tr.CPN","DESC");
		
		//$this->legacy_db->get()->result_array();
		//prePrint($this->legacy_db->last_query());
		//exit();
		return $this->legacy_db->get()->result_array(); 
	}
	public function getQualification(){
	    //$query = "select ur.CNIC_NO,ap.APPLICATION_ID,ap.USER_ID,qual.QUALIFICATION_ID from applications ap join users_reg ur on (ap.USER_ID = ur.USER_ID ) join qualifications qual on (qual.USER_ID = ur.USER_ID) where qual.ACTIVE = 1 and ap.IS_DELETED = 'N' and ap.STATUS_ID in (10,5) and qual.APPLICATION_ID is NULL and ap.ADMISSION_SESSION_ID >=1 AND ap.ADMISSION_SESSION_ID<=28  limit 30";
	    $this->legacy_db = $this->load->database("admission_db",true);
		
		$this->legacy_db->select('ur.CNIC_NO,ap.APPLICATION_ID,ap.USER_ID,qual.QUALIFICATION_ID');
		$this->legacy_db->from('applications ap');
		$this->legacy_db->join("`users_reg` ur","ap.USER_ID = ur.USER_ID");
		$this->legacy_db->join("qualifications qual","qual.USER_ID = ur.USER_ID");
	    $this->legacy_db->where("qual.ACTIVE = 1 and ap.IS_DELETED = 'N' and ap.STATUS_ID in (10,5) and qual.APPLICATION_ID is NULL and ap.ADMISSION_SESSION_ID >=1 AND ap.ADMISSION_SESSION_ID<=28");
		$this->legacy_db->limit(500);
		$data = $this->legacy_db->get()->result_array();
	//	prePrint($data);
		foreach($data as $obj){
		    $APPLICATION_ID = $obj['APPLICATION_ID'];
		    $USER_ID = $obj['USER_ID'];
		    $QUALIFICATION_ID = $obj['QUALIFICATION_ID'];
		    $CNIC_NO = $obj['CNIC_NO'];
		    $this->legacy_db->trans_begin();
		    $this->db->trans_begin();
            $q  = "UPDATE qualifications SET APPLICATION_ID = $APPLICATION_ID WHERE USER_ID = $USER_ID AND QUALIFICATION_ID = $QUALIFICATION_ID";
            $this->legacy_db->query($q);
            $this->db->query($q);
            //prePrint($CNIC_NO);
            //prePrint($q);
            
            if ($this->db->trans_status() === FALSE||$this->legacy_db->trans_status() === FALSE)
            {
                $this->db->trans_rollback();
                $this->legacy_db->trans_rollback();
            }
            else
            {
                $this->db->trans_commit();
                 $this->legacy_db->trans_commit();
            }
		}
		return $data ; 
	}
	
}