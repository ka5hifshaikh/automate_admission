<?php
/**
 * Created by PhpStorm.
 * User: Yasir Mehboob
 * Date: 12/01/2021
 * Time: 11:00 AM
 */

class Selection_list_report_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('log_model');
	}

	function selected_list($admission_session_id,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0,$list_no=0,$category_id=0,$is_provisional = 'N'){

		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('slc.SELECTION_LIST_ID AS SELECTION_LIST_ID, a.APPLICATION_ID,ur.USER_ID,ass.ADMISSION_SESSION_ID,pt.PROGRAM_TITLE,c.NAME,tr.CPN AS TEST_CPN,tr.CARD_ID,ur.FIRST_NAME,ur.LAST_NAME,ur.FNAME,al.LIST_NO,d.DISTRICT_NAME,cat.CATEGORY_NAME,ur.GENDER,ur.U_R,slc.CPN AS CPN_MERIT_LIST,pl.PROGRAM_TITLE,slc.SHIFT_ID,slc.CHOICE_NO,s.SHIFT_NAME,pt.PROGRAM_TITLE AS PROGRAM_TITLE_CATE,tr.DETAIL_CPN,tr.TEST_SCORE AS TEST_SCORE,slc.ACTIVE');
		$this->legacy_db->from('applications a');
		
		$this->legacy_db->join("`applied_shift` apps","apps.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`shift` s","apps.`SHIFT_ID` = s.`SHIFT_ID`");
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = a.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`selection_list` slc","slc.`APPLICATION_ID` = a.`APPLICATION_ID` AND tr.CARD_ID=slc.CARD_ID AND apps.SHIFT_ID=slc.SHIFT_ID AND tr.`TEST_ID`=slc.`TEST_ID`");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
			$this->legacy_db->join("`users_reg` ur","ur.`USER_ID` = a.`USER_ID`");
		$this->legacy_db->join("`districts` d","ur.`DISTRICT_ID` = d.`DISTRICT_ID`");
	
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`");
			$this->legacy_db->join("`program_list` pl","pl.`PROG_LIST_ID` = slc.`PROG_LIST_ID`");
		$this->legacy_db->join("`category` cat","cat.`CATEGORY_ID` = slc.`CATEGORY_ID`");
		
		$this->legacy_db->where("slc.IS_PROVISIONAL",$is_provisional);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		if($admission_session_id>0)$this->legacy_db->where("ass.ADMISSION_SESSION_ID",$admission_session_id);
		if($category_id>0)$this->legacy_db->where("slc.CATEGORY_ID",$category_id);
		if($list_no>0)$this->legacy_db->where("al.LIST_NO",$list_no);
		
		$this->legacy_db->order_by('CATEGORY_ID', 'ASC');
		$this->legacy_db->order_by("tr.CPN","DESC");
		
		 
		 $data = $this->legacy_db->get()->result_array();
	
		 return $data;
	
	   // echo $this->legacy_db->last_query();
	   // return;
	}
	
	
	//added by kashif 17-03-2021
	function selected_list_for_pdf($admission_session_id,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0,$list_no=0,$category_id=0,$is_provisional = 'N'){

		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('slc.SELECTION_LIST_ID AS SELECTION_LIST_ID, a.APPLICATION_ID,a.USER_ID,ass.ADMISSION_SESSION_ID,c.NAME,tr.CPN AS TEST_CPN,tr.CARD_ID,ur.FIRST_NAME,ur.LAST_NAME,ur.FNAME,al.LIST_NO,d.DISTRICT_NAME,cat.CATEGORY_NAME,ur.GENDER,ur.U_R,slc.CPN AS CPN_MERIT_LIST,pl.PROGRAM_TITLE,slc.SHIFT_ID,slc.CHOICE_NO,s.SHIFT_NAME,pt.PROGRAM_TITLE AS PROGRAM_TITLE_CATE,tr.DETAIL_CPN,tr.TEST_SCORE AS TEST_SCORE,slc.ACTIVE');
		$this->legacy_db->from('applications a');
		$this->legacy_db->join("`users_reg` ur","ur.`USER_ID` = a.`USER_ID`");
		$this->legacy_db->join("`districts` d","ur.`DISTRICT_ID` = d.`DISTRICT_ID`");
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = a.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`selection_list` slc","slc.`APPLICATION_ID` = a.`APPLICATION_ID` AND tr.CARD_ID=slc.CARD_ID AND  tr.`TEST_ID`=slc.`TEST_ID`");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
	    $this->legacy_db->join("`shift` s","slc.`SHIFT_ID` = s.`SHIFT_ID`");
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`");
		$this->legacy_db->join("`program_list` pl","pl.`PROG_LIST_ID` = slc.`PROG_LIST_ID`");
		$this->legacy_db->join("`category` cat","cat.`CATEGORY_ID` = slc.`CATEGORY_ID`");
		$this->legacy_db->where("slc.IS_PROVISIONAL",$is_provisional);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		if($admission_session_id>0)$this->legacy_db->where("ass.ADMISSION_SESSION_ID",$admission_session_id);
		if($category_id>0)$this->legacy_db->where("slc.CATEGORY_ID",$category_id);
		if($list_no>0)$this->legacy_db->where("al.LIST_NO",$list_no);
		
		$this->legacy_db->order_by('cat.CATEGORY_ID', 'ASC');
		$this->legacy_db->order_by("tr.CPN","DESC");
		
		 
		 $data = $this->legacy_db->get()->result_array();
	
		 return $data;
	
	   // echo $this->legacy_db->last_query();
	   // return;
	}
	
	// ADDED BY VIKESH KUMAR FOR LL.B (LAW) 
	function selected_list_law($admission_session_id,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0,$list_no=0,$category_id=0)
	{
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('a.APPLICATION_ID,ur.USER_ID,ass.ADMISSION_SESSION_ID,pt.PROGRAM_TITLE,c.NAME,tr.CPN AS TEST_CPN,tr.CARD_ID,ur.FIRST_NAME,ur.LAST_NAME,ur.FNAME,al.LIST_NO,d.DISTRICT_NAME,cat.CATEGORY_NAME,ur.GENDER,ur.U_R,slc.CPN AS CPN_MERIT_LIST,pl.PROGRAM_TITLE,slc.SHIFT_ID,slc.CHOICE_NO,s.SHIFT_NAME,pt.PROGRAM_TITLE AS PROGRAM_TITLE_CATE,tr.DETAIL_CPN,tr.TEST_SCORE AS TEST_SCORE ');
		$this->legacy_db->from('applications a');
			
		$this->legacy_db->join("`users_reg` ur","a.`USER_ID` = ur.`USER_ID`");
		$this->legacy_db->join("`districts` d","ur.`DISTRICT_ID` = d.`DISTRICT_ID`");
	
		$this->legacy_db->join("`applied_shift` apps","apps.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`shift` s","apps.`SHIFT_ID` = s.`SHIFT_ID`");
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = a.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`selection_list` slc","slc.`APPLICATION_ID` = a.`APPLICATION_ID` AND tr.CARD_ID=slc.CARD_ID AND apps.SHIFT_ID=slc.SHIFT_ID AND tr.`TEST_ID`=slc.`TEST_ID`");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`");
		$this->legacy_db->join("`program_list` pl","pl.`PROG_LIST_ID` = slc.`PROG_LIST_ID`");
		$this->legacy_db->join("`category` cat","cat.`CATEGORY_ID` = slc.`CATEGORY_ID`");
		$this->legacy_db->where("slc.IS_PROVISIONAL",'N');
		$this->legacy_db->where("tr.TEST_ID",3);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		if($admission_session_id>0)$this->legacy_db->where("ass.ADMISSION_SESSION_ID",$admission_session_id);
		if($category_id>0)$this->legacy_db->where("slc.CATEGORY_ID",$category_id);
		if($list_no>0)$this->legacy_db->where("al.LIST_NO",$list_no);
		
		$this->legacy_db->order_by('cat.CATEGORY_ID', 'ASC');
		$this->legacy_db->order_by("tr.CPN","DESC");
		return $this->legacy_db->get()->result_array();
	}
	
	
	function selected_list_for_verification($admission_session_id,$shift_id=0,$list_no=0,$is_provisional='N')
	{

		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('*,al.LIST_NO');
		$this->legacy_db->from('selection_list slc');
		$this->legacy_db->join("`test_result` tr","tr.`TEST_ID` = slc.`TEST_ID`  AND slc.APPLICATION_ID = tr.APPLICATION_ID");
		$this->legacy_db->join("`applications` app","slc.`APPLICATION_ID` = app.`APPLICATION_ID`");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
		$this->legacy_db->join("`users_reg` ur","app.`USER_ID` = ur.`USER_ID`");
		$this->legacy_db->join("`districts` d","ur.`DISTRICT_ID` = d.`DISTRICT_ID`");
		$this->legacy_db->join("`program_list` pl","pl.`PROG_LIST_ID` = slc.`PROG_LIST_ID`");
		$this->legacy_db->join("`category` cat","cat.`CATEGORY_ID` = slc.`CATEGORY_ID`");
	
		$this->legacy_db->where("slc.IS_PROVISIONAL",$is_provisional);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($admission_session_id>0)$this->legacy_db->where("slc.ADMISSION_SESSION_ID",$admission_session_id);
		if($list_no>0)$this->legacy_db->where("al.LIST_NO",$list_no);
	    $this->legacy_db->order_by('ur.GENDER', 'ASC');
		$this->legacy_db->order_by('slc.APPLICATION_ID', 'ASC');
	
		$data = $this->legacy_db->get()->result_array();
	
	  //  echo $this->legacy_db->last_query();
	   // exit();
	    return $data;
	}
	
	function getDetailOnAdmissionSessionById($admission_session_id){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('*');
		$this->legacy_db->from('admission_session ass');
		$this->legacy_db->join('sessions ses',"ses.SESSION_ID=ass.SESSION_ID");
		$this->legacy_db->join('program_type pt',"pt.PROGRAM_TYPE_ID=ass.PROGRAM_TYPE_ID");
		$this->legacy_db->join('campus c',"c.CAMPUS_ID=ass.CAMPUS_ID");
		$this->legacy_db->where('ass.ADMISSION_SESSION_ID',$admission_session_id);
		return $this->legacy_db->get()->row_array();
	}
	
	/*
	 * Yasir Mehboob Coded 16-01-2021
	 * */
	 
	function get_candidate_selection_list($user_id=0,$application_id=0,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('slc.SELECTION_LIST_ID,a.APPLICATION_ID,a.USER_ID,ass.ADMISSION_SESSION_ID,pt.PROGRAM_TITLE,c.CAMPUS_ID,c.NAME,tr.CPN AS TEST_CPN,tr.CARD_ID,slc.FIRST_NAME,slc.LAST_NAME,slc.FNAME,al.LIST_NO,slc.DISTRICT_NAME,slc.CATEGORY_NAME,slc.GENDER,slc.U_R,slc.CPN AS CPN_MERIT_LIST,slc.PROGRAM_TITLE,slc.SHIFT_ID,slc.CHOICE_NO,s.SHIFT_NAME,pt.PROGRAM_TITLE AS PROGRAM_TITLE_CATE,tr.DETAIL_CPN,app_status.STATUS_NAME,a.MESSAGE,slc.CNIC_NO,a.STATUS_ID AS APPLICATION_STATUS_ID,slc.PROG_LIST_ID AS PROG_LIST_ID,slc.ACTIVE');
		$this->legacy_db->from('applications a');
		$this->legacy_db->join("`application_status` app_status","app_status.`STATUS_ID` = a.`STATUS_ID`");
// 		$this->legacy_db->join("`applied_shift` apps","apps.`APPLICATION_ID` = a.`APPLICATION_ID`");
		
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = a.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`selection_list_candidate` slc","slc.`APPLICATION_ID` = a.`APPLICATION_ID` AND tr.CARD_ID=slc.CARD_ID AND slc.TEST_ID=tr.TEST_ID");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
		$this->legacy_db->join("`shift` s","slc.`SHIFT_ID` = s.`SHIFT_ID`");
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`","LEFT");
		if ($user_id>0)$this->legacy_db->where("a.USER_ID",$user_id);
		$this->legacy_db->where("slc.IS_PROVISIONAL='N'");
		if($application_id>0)$this->legacy_db->where("a.APPLICATION_ID",$application_id);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		$this->legacy_db->order_by("tr.CPN","DESC");
//		$this->legacy_db->get()->result_array();
//		prePrint($this->legacy_db->last_query());
		return $this->legacy_db->get()->result_array();
	}

	function getUserByCnic($cnic){
		$this->db->where('CNIC_NO',$cnic);
		$user = $this->db->get('users_reg')->row_array();
		return $user;
	}
	
	function get_admission_list_no ($admission_session_id,$shift_id){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('*');
		$this->legacy_db->from('admission_list');
		$this->legacy_db->where('ADMISSION_SESSION_ID',$admission_session_id);
		$this->legacy_db->where('SHIFT_ID',$shift_id);
	//	$this->legacy_db->where('IS_DISPLAY','1');
		
// 		$this->legacy_db->get()->row_array();
// 		exit($this->legacy_db->last_query());
		return $this->legacy_db->get()->result_array();
	}
	 function get_admission_list_no_by_id($admission_list_id){
        $this->legacy_db = $this->load->database("admission_db",true);
        $this->legacy_db->select('*');
        $this->legacy_db->from('admission_list');
        $this->legacy_db->where('ADMISSION_LIST_ID',$admission_list_id);

        return $this->legacy_db->get()->row_array();
    }
	
	function get_candidate_selection_list_from_selection_list_table($user_id=0,$application_id=0,$session_id=0,$program_type_id=0,$shift_id=0,$program_list_id=0){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('ss.SESSION_ID,slc.SELECTION_LIST_ID,a.APPLICATION_ID,a.USER_ID,slc.ADMISSION_SESSION_ID,pt.PROGRAM_TITLE,c.CAMPUS_ID,c.NAME,tr.CPN AS TEST_CPN,tr.CARD_ID,ur.FIRST_NAME,ur.LAST_NAME,ur.FNAME,al.LIST_NO,d.DISTRICT_NAME,cat.CATEGORY_NAME,ur.GENDER,ur.U_R,slc.CPN AS CPN_MERIT_LIST,pl.PROGRAM_TITLE,slc.SHIFT_ID,slc.CHOICE_NO,s.SHIFT_NAME,pt.PROGRAM_TITLE AS PROGRAM_TITLE_CATE,tr.DETAIL_CPN,app_status.STATUS_NAME,a.MESSAGE,ur.CNIC_NO,a.STATUS_ID AS APPLICATION_STATUS_ID,slc.PROG_LIST_ID AS PROG_LIST_ID,slc.ACTIVE,slc.REMARKS AS REMARKS,pt.PROGRAM_TYPE_ID,pt.PROGRAM_TITLE AS PROGRAM_TYPE_TITLE,ur.EMAIL,ur.MOBILE_NO,fct.TYPE_CODE');
		$this->legacy_db->from('applications a');
		$this->legacy_db->join("`users_reg` ur","a.`USER_ID` = ur.`USER_ID`");
		$this->legacy_db->join("`districts` d","ur.`DISTRICT_ID` = d.`DISTRICT_ID`");
		$this->legacy_db->join("`application_status` app_status","app_status.`STATUS_ID` = a.`STATUS_ID`");
		$this->legacy_db->join("`test_result` tr","tr.`APPLICATION_ID` = a.`APPLICATION_ID`");
		$this->legacy_db->join("`selection_list` slc","slc.`APPLICATION_ID` = a.`APPLICATION_ID` AND slc.`TEST_ID` = tr.`TEST_ID`");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
		$this->legacy_db->join("`program_list` pl","slc.`PROG_LIST_ID` = pl.`PROG_LIST_ID`");
		$this->legacy_db->join("`category` cat","slc.`CATEGORY_ID` = cat.`CATEGORY_ID`");
		$this->legacy_db->join("`fee_category_type` fct","fct.`FEE_CATEGORY_TYPE_ID` = cat.`FEE_CATEGORY_TYPE_ID`");
		$this->legacy_db->join("`admission_session` ass","ass.`ADMISSION_SESSION_ID` = slc.`ADMISSION_SESSION_ID`");
		$this->legacy_db->join("`sessions` ss","ass.`SESSION_ID` = ss.`SESSION_ID`");
		$this->legacy_db->join("`program_type` pt","ass.`PROGRAM_TYPE_ID` = pt.`PROGRAM_TYPE_ID`");
		$this->legacy_db->join("`campus` c","ass.`CAMPUS_ID` = c.`CAMPUS_ID`");
		
		
		$this->legacy_db->join("`shift` s","slc.`SHIFT_ID` = s.`SHIFT_ID`");
		$this->legacy_db->join("`application_status` aps","aps.`STATUS_ID` = a.`STATUS_ID`","LEFT");
		if ($user_id>0)$this->legacy_db->where("a.USER_ID",$user_id);
		$this->legacy_db->where("slc.IS_PROVISIONAL='N'");
		if($application_id>0)$this->legacy_db->where("a.APPLICATION_ID",$application_id);
		if($session_id>0)$this->legacy_db->where("ss.SESSION_ID",$session_id);
		if($program_type_id>0)$this->legacy_db->where("pt.PROGRAM_TYPE_ID",$program_type_id);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($program_list_id>0)$this->legacy_db->where("slc.PROG_LIST_ID",$program_list_id);
		$this->legacy_db->order_by("tr.CPN","DESC");
//		$this->legacy_db->get()->result_array();
//		prePrint($this->legacy_db->last_query());
		return $this->legacy_db->get()->result_array();
	}
	
    /*
	 * yasir created this methods 20-02-2021
	 * */

	function getDisciplineSeatsDistributionsWithCategory($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids=array()){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select("cam.CAMPUS_ID AS CAMPUS_ID,cat_type.CATEGORY_NAME AS CATEGORY_TYPE_NAME,cat_type.CATEGORY_TYPE_ID,dsd.`TOTAL_SEATS`,pl.`PROGRAM_TITLE`,cat.`CATEGORY_NAME`,cam.`NAME`,dsd.CATEGORY_ID,dsd.PROG_LIST_ID");

		$this->legacy_db->from('discipline_seats_distributions AS dsd');
		$this->legacy_db->join('program_list AS pl', ' dsd.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
		$this->legacy_db->join('category AS cat', 'dsd.`CATEGORY_ID` = cat.`CATEGORY_ID`');
		$this->legacy_db->join('category_type AS cat_type', 'cat_type.`CATEGORY_TYPE_ID` = cat.`CATEGORY_TYPE_ID`');
		$this->legacy_db->join('campus AS cam', 'dsd.`CAMPUS_ID` = cam.`CAMPUS_ID`');
		$this->legacy_db->where('dsd.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('dsd.SHIFT_ID', $shift_id);
		$this->legacy_db->where('dsd.SESSION_ID', $session_id);
		$this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
		if(count($prog_list_ids)){
			$this->legacy_db->where_in('pl.PROG_LIST_ID', $prog_list_ids);
		}

		$this->legacy_db->order_by('pl.program_title');
		$this->legacy_db->order_by('dsd.CATEGORY_ID');
		$result = $this->legacy_db->get()->result_array();

		return $result;
	}

	function getFilledSeats($admission_session_id,$shift_id){
		$this->legacy_db = $this->load->database("admission_db",true);
		$result = $this->legacy_db->query("SELECT count(*) AS FILLED_SEATS,ADMISSION_SESSION_ID,PROG_LIST_ID,SHIFT_ID,CATEGORY_ID from selection_list sll where sll.SELECTION_LIST_ID IN (SELECT sl.SELECTION_LIST_ID
FROM
selection_list sl
JOIN fee_ledger fl
ON (
sl.`SELECTION_LIST_ID` = fl.`SELECTION_LIST_ID`
)
WHERE fl.`IS_YES` = 'Y'
AND sl.`IS_PROVISIONAL` = 'N'
AND sl.`ACTIVE` = 1  and fl.CHALLAN_TYPE_ID=1
group by sl.SELECTION_LIST_ID)
AND ADMISSION_SESSION_ID=$admission_session_id AND SHIFT_ID=$shift_id
GROUP BY  sll.SHIFT_ID,sll.PROG_LIST_ID, sll.CATEGORY_ID");

//	$result = $this->legacy_db->get()->result_array();
	$result = $result->result_array();
	if (count($result)>0){
		$new_array = array();
		foreach ($result as $rows){
			$FILLED_SEATS = $rows['FILLED_SEATS'];
			$ADMISSION_SESSION_ID = $rows['ADMISSION_SESSION_ID'];
			$PROG_LIST_ID = $rows['PROG_LIST_ID'];
			$CATEGORY_ID = $rows['CATEGORY_ID'];
			$new_array[$ADMISSION_SESSION_ID][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS']=$FILLED_SEATS;
		}//FOREACH
		return $new_array;
	}else{
		return false;
	}//ELSE

	}//METHOD


	/*
	 * yasir created following methods on 22-02-2021
	 * */

	function getCategory($category_type_id=0){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select("*");
		$this->legacy_db->from('category AS cat');
		if ($category_type_id>0)$this->legacy_db->where('cat.CATEGORY_TYPE_ID', $category_type_id);
		$result = $this->legacy_db->get()->result_array();
		return $result;
	}
	/*
	 * Kashif created following methods on 07-07-2021
	 * */

	function getSelectionListCountByAdmissionListId($admission_list_id){
	   // SELECT prog_list_id,category_id,COUNT(category_id) FROM selection_list WHERE admission_list_id=1  GROUP BY prog_list_id,category_id
	   $this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select("PROG_LIST_ID,sl.CATEGORY_ID,cat.CATEGORY_TYPE_ID,COUNT(*) AS FILLED_SEAT");
		$this->legacy_db->from('selection_list AS sl');
		$this->legacy_db->join('category cat','(sl.`CATEGORY_ID` = cat.`CATEGORY_ID`)');
		if ($admission_list_id>0)$this->legacy_db->where('sl.ADMISSION_LIST_ID', $admission_list_id);
		$this->legacy_db->where('sl.ACTIVE = 1');
			$this->legacy_db->where("sl.IS_PROVISIONAL = 'N'");
		$this->legacy_db->group_by('sl.PROG_LIST_ID,sl.CATEGORY_ID,cat.CATEGORY_TYPE_ID'); 
		$result = $this->legacy_db->get()->result_array();
		return $result;
	
	}
	function getSelectionListPreviousPromotedCountByAdmissionListId($admission_list_id){
	      $this->legacy_db = $this->load->database("admission_db",true);
	      $this->legacy_db->select("*");
	      $this->legacy_db->from("selection_list sl");
	      $this->legacy_db->where("sl.IS_PROVISIONAL ='N' ");
	      $this->legacy_db->where("sl.ACTIVE = 1");
	      $this->legacy_db->where("sl.ADMISSION_LIST_ID",$admission_list_id);
	      $result = $this->legacy_db->get()->result_array();
	     $application_ids = array();
	     $new_array = array();
	     foreach($result as $sl){
	         array_push($application_ids,$sl['APPLICATION_ID']);
	         if(!isset($new_array[$sl['APPLICATION_ID']])){
	             $new_array[$sl['APPLICATION_ID']] = array();
	         }
	         array_push( $new_array[$sl['APPLICATION_ID']],$sl);
	     }
	     if(count($application_ids)==0)
	     $application_ids[]=0;
	      $this->legacy_db->select("*");
	      $this->legacy_db->from("candidate_account ca");
	       $this->legacy_db->join("fee_ledger fl","(ca.ACCOUNT_ID = fl.ACCOUNT_ID)");
	       $this->legacy_db->join("selection_list sl","(fl.SELECTION_LIST_ID = sl.SELECTION_LIST_ID)");
	        $this->legacy_db->join("category cat ","(sl.CATEGORY_ID = cat.CATEGORY_ID)");
	      $this->legacy_db->where("sl.IS_PROVISIONAL = 'N'");
	      $this->legacy_db->where("sl.ACTIVE = 1");
	      $this->legacy_db->where("fl.IS_YES ='Y'");
	      $this->legacy_db->where("ca.ACTIVE=1");
	       $this->legacy_db->where("sl.ADMISSION_LIST_ID < $admission_list_id ");
	      $this->legacy_db->where_in("sl.APPLICATION_ID",$application_ids);
	 
            $result = $this->legacy_db->get()->result_array();
             $second_new_array = array();
            foreach($result as $row){
                $second_new_array[$row['APPLICATION_ID']] =$row;
            }
            //   prePrint($second_new_array);
            $final_array= array();
            foreach($new_array as $application_id => $value){
                //prePrint($application_id);
                if(isset($second_new_array[$application_id])){
                    $CATEGORY_ID = $second_new_array[$application_id]['CATEGORY_ID'];
                    $PROG_LIST_ID = $second_new_array[$application_id]['PROG_LIST_ID'];
                    if(!isset($final_array[$PROG_LIST_ID][$CATEGORY_ID])){
                        $final_array[$PROG_LIST_ID][$CATEGORY_ID] = 0;
                    }
                    $final_array[$PROG_LIST_ID][$CATEGORY_ID]++;
                    
                }
            }
            return $final_array;
	}
	
	function getProgramSelectedCandidates($admission_session_id,$prog_list_id,$shift_id){
		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select("*");
		$this->legacy_db->from("selection_list sl");
		$this->legacy_db->join("fee_ledger_summary fls","sl.SELECTION_LIST_ID=fls.SELECTION_LIST_ID");
	   $this->legacy_db->join('program_list AS pl', ' sl.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'sl.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('admission_session AS ass', 'sl.`ADMISSION_SESSION_ID` = ass.`ADMISSION_SESSION_ID`');
        $this->legacy_db->join('applications AS app', 'sl.`APPLICATION_ID` = app.`APPLICATION_ID`');
        $this->legacy_db->join('users_reg AS ur', 'ur.`USER_ID` = app.`USER_ID`');
     	$this->legacy_db->join("`test_result` tr","sl.`CARD_ID`=tr.`CARD_ID` AND sl.`APPLICATION_ID`=tr.`APPLICATION_ID`");
		$this->legacy_db->where("sl.ADMISSION_SESSION_ID",$admission_session_id);
		if($prog_list_id !=null)$this->legacy_db->where_in("sl.PROG_LIST_ID",$prog_list_id);
		$this->legacy_db->where("sl.SHIFT_ID",$shift_id);
		$result = $this->legacy_db->get()->result_array();
		return $result;
	}//end
	
	
	function total_selection_discipline_wise($admission_session_id,$shift_id=0,$list_no=0,$is_provisional='N'){

		$this->legacy_db = $this->load->database("admission_db",true);
		$this->legacy_db->select('slc.*,tr.*');
		$this->legacy_db->from('selection_list slc');
		$this->legacy_db->join("`test_result` tr","tr.`TEST_ID` = slc.`TEST_ID`  AND slc.APPLICATION_ID = tr.APPLICATION_ID");
		$this->legacy_db->join("`applications` app","slc.`APPLICATION_ID` = app.`APPLICATION_ID`");
		$this->legacy_db->join("`admission_list` al","slc.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID");
	$this->legacy_db->join("`users_reg` ur","ur.`USER_ID` = app.`USER_ID`");
		$this->legacy_db->join("`districts` d","ur.`DISTRICT_ID` = d.`DISTRICT_ID`");
	
		$this->legacy_db->where("slc.IS_PROVISIONAL",$is_provisional);
		if($shift_id>0)$this->legacy_db->where("slc.SHIFT_ID",$shift_id);
		if($admission_session_id>0)$this->legacy_db->where("slc.ADMISSION_SESSION_ID",$admission_session_id);
		if($list_no>0)$this->legacy_db->where("al.LIST_NO",$list_no);
		$this->legacy_db->order_by('ur.GENDER', 'ASC');
		$this->legacy_db->order_by('slc.APPLICATION_ID', 'ASC');

		return $this->legacy_db->get()->result_array();
	}
	
	function getMissngData(){
	    
	    
	   $this->legacy_db = $this->load->database("admission_online",true);
		$this->legacy_db->select('*');
		$this->legacy_db->from('examination_data ed');
		$this->legacy_db->where("ed.SESSION_ID",2);
		$results = $this->legacy_db->get()->result_array();
		$not_found = $change_roll_no =$multiple_roll_no= array();
		foreach($results as $data){
		    $application_id = $data['APPLICATION_ID'];
		    $this->legacy_db->select('*');
		    $this->legacy_db->from('enrolment en');
		    $this->legacy_db->where("en.TAG_NO",$application_id);
		    $current_results = $this->legacy_db->get()->result_array();
		    if(count($current_results)==0){
		        $not_found[]=$data;
		    }else if(count($current_results)==1){
		        if($current_results[0]['ROLL_NO']!=$data['ROLL_NO']){
		            $change_roll_no[]=$data;    
		        }
		        
		    }else{
		        $multiple_roll_no = $data;
		        //multiple record
		    }
		}
        $final_result = array("NOT_FOUND"=>$not_found,"CHANGE_ROLL_NO"=>$change_roll_no,"MULTIPLE_RECORD"=>$multiple_roll_no);
        return $final_result;
	}
	
	function getSelectionListByListId($ADMISSION_LIST_ID,$IS_PROVISIONAL){
	    	$this->legacy_db = $this->load->database("admission_db",true);
	 $sql = "SELECT
   sl. `SELECTION_LIST_ID`,
   sl.`SHIFT_ID`,
    `SESSION_ID`,
   sl. `ADMISSION_SESSION_ID`,
    ur.`USER_ID`,
    sl.`APPLICATION_ID`,
    `LIST_NO`,
    pl.`PROG_LIST_ID`,
    sl.`CATEGORY_ID`,
    ur.`DISTRICT_ID`,
    `CHOICE_NO`,
    sl.`ACTIVE`,
    tr.`TEST_ID`,
    tr.`CARD_ID`,
    ur.`CNIC_NO`,
    `FIRST_NAME`,
    `LAST_NAME`,
    `FNAME`,
    `GENDER`,
    `U_R`,
    dis.`DISTRICT_NAME`,
    `CATEGORY_NAME`,
    `PROGRAM_TITLE`,
    c.NAME as `CAMPUS_NAME`,
    tr.`DETAIL_CPN`,
    tr.`CPN`,
    sl.`REMARKS`,
    `IS_PROVISIONAL`,
    sl.`ADMISSION_LIST_ID`,
    `ROLL_NO_CODE`
FROM
    selection_list sl
JOIN applications app ON
    app.APPLICATION_ID = sl.APPLICATION_ID
JOIN users_reg ur ON
    ur.USER_ID = app.USER_ID
JOIN districts dis ON
    dis.DISTRICT_ID = ur.DISTRICT_ID
JOIN admission_session ass ON
    ass.ADMISSION_SESSION_ID = app.ADMISSION_SESSION_ID
JOIN campus c ON
    c.CAMPUS_ID = ass.CAMPUS_ID
JOIN admission_list al ON
    al.ADMISSION_LIST_ID =sl.ADMISSION_LIST_ID
JOIN program_list as pl on
    pl.PROG_LIST_ID = sl.PROG_LIST_ID
JOIN category cat ON
    cat.CATEGORY_ID = sl.CATEGORY_ID
    JOIN test_result tr ON
    tr.TEST_ID = sl.TEST_ID and sl.APPLICATION_ID = tr.APPLICATION_ID
    where sl.ADMISSION_LIST_ID = $ADMISSION_LIST_ID and sl.IS_PROVISIONAL = '$IS_PROVISIONAL'"; 
   // echo $sql;
    $query = $this->legacy_db->query($sql);
   // prePrint($query);
    //exit();
    $data = $query->result_array();
    return($data);
  
	}
	function add_data_in_candidate_selection_batch($data){
        $this->legacy_db = $this->load->database("admission_db",true);
        return $this->legacy_db->insert_batch('selection_list_candidate', $data); 
        
    }

}
