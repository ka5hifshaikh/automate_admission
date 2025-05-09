<?php


class Statistics_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
//		$CI =& get_instance();
		$this->load->model('log_model');
	}

	function count_submitted_applications (){
		$this->legacy_db = $this->load->database('admission_db',true);
//		print_r($adm_con);
		$this->legacy_db->select('DATE(SUBMISSION_DATE) AS DATE,COUNT(APPLICATION_ID) AS UNITS');
		$this->legacy_db->where ('IS_SUBMITTED','Y');
		$this->legacy_db->group_by('DATE(SUBMISSION_DATE)');
		$this->legacy_db->order_by('DATE(SUBMISSION_DATE)');
		 return $this->legacy_db->get('applications')->result_array();
//		print_r($this->legacy_db->last_query());
//		exit();
	}

	function get_application_statistics_district_wise ($session_id,$program_type_id,$campus_id,$province_id,$division_id,$district_id){
		$this->legacy_db = $this->load->database('admission_db',true);
		$this->legacy_db->select('*');
		$this->legacy_db->from('`application_statistics_district_wise`');
		if($session_id>0) $this->legacy_db->where("SESSION_ID=$session_id");
		if($program_type_id>0) $this->legacy_db->where("PROGRAM_TYPE_ID=$program_type_id");
		if($campus_id>0) $this->legacy_db->where("CAMPUS_ID IN ($campus_id)");
		if($province_id>0) $this->legacy_db->where("PROVINCE_ID IN ($province_id)");
		if($division_id>0) $this->legacy_db->where("DIVISION_ID IN ($division_id)");
		if($district_id>0) $this->legacy_db->where("DISTRICT_ID IN ($district_id)");
		$this->legacy_db->order_by("DISTRICT_NAME");
		return($this->legacy_db->get()->result_array());
	}

	function get_statistics ($session_id,$program_type_id,$campus_id){
		$this->legacy_db = $this->load->database('admission_db',true);
//		print_r($adm_con);
		$this->legacy_db->select('*');
		$this->legacy_db->from('`application_statistics`');

		if($session_id>0) $this->legacy_db->where("SESSION_ID=$session_id");
		if($program_type_id>0) $this->legacy_db->where("PROGRAM_TYPE_ID=$program_type_id");
		if($campus_id>0) $this->legacy_db->where("CAMPUS_ID IN ($campus_id)");
		return($this->legacy_db->get()->result_array());
	}
	function get_enrolment_data($session_id){
	    $sub_query = "SELECT sl.SELECTION_LIST_ID,sl.APPLICATION_ID,app.USER_ID,ass.CAMPUS_ID,ass.ADMISSION_SESSION_ID,sess.SESSION_ID,ca.ACCOUNT_ID,sl.PROG_LIST_ID,sl.SHIFT_ID,sl.CATEGORY_ID,fl.CHALLAN_NO,fl.PAID_AMOUNT,fl.DATE AS PAID_DATE 
	                    FROM selection_list sl
	                  
                        join applications app on (sl.APPLICATION_ID = app.APPLICATION_ID)
                        join admission_session ass on (ass.ADMISSION_SESSION_ID = app.ADMISSION_SESSION_ID)
                        join sessions sess on (sess.SESSION_ID = ass.SESSION_ID)
                        
                        join candidate_account ca on (sl.APPLICATION_ID = ca.APPLICATION_ID)
                        join fee_ledger fl on (ca.ACCOUNT_ID = fl.ACCOUNT_ID AND sl.SELECTION_LIST_ID = fl.SELECTION_LIST_ID AND fl.CHALLAN_TYPE_ID=1)
                        
                        where 
                        sess.SESSION_ID = $session_id 
                        and sl.IS_PROVISIONAL = 'N' 
                        and sl.ACTIVE = 1 and sl.ROLL_NO_CODE>0
                        and ca.ACTIVE=1
                        and fl.IS_YES = 'Y'
                        GROUP by sl.APPLICATION_ID,sl.SELECTION_LIST_ID,app.USER_ID  ";
        
        $query = "SELECT sq.*,ur.CNIC_NO,ur.FIRST_NAME,ur.LAST_NAME,ur.FNAME,camp.NAME AS CAMPUS_NAME,i.INSTITUTE_NAME,pl.PROGRAM_TITLE,pt.PROGRAM_TITLE AS PROGRAM_TYPE_TITLE FROM users_reg AS ur 
                    JOIN ($sub_query) AS sq ON (ur.USER_ID = sq.USER_ID)
                    JOIN program_list AS pl ON (pl.PROG_LIST_ID = sq.PROG_LIST_ID)
                    JOIN campus AS camp ON (camp.CAMPUS_ID = sq.CAMPUS_ID)
                    JOIN program_type AS pt ON (pl.PROGRAM_TYPE_ID = pt.PROGRAM_TYPE_ID)
                    JOIN qualifications AS q ON (q.USER_ID=ur.USER_ID)
                    JOIN institute AS i ON (i.INSTITUTE_ID = q.ORGANIZATION_ID)
                    JOIN discipline AS d ON (d.DISCIPLINE_ID = q.DISCIPLINE_ID)
                    JOIN degree_program AS dp ON (dp.DEGREE_ID = d.DEGREE_ID )
                    WHERE dp.DEGREE_ID IN(2,3,4,5,6) ORDER BY dp.DEGREE_ID DESC;
                    ";
        
        $this->legacy_db = $this->load->database('admission_db',true);
	
		$q = $this->legacy_db->query($query);
	
		$result = $q->result_array();
		$new_array = array();
        foreach($result as $obj){
            $application_id = $obj['APPLICATION_ID'];
            if(isset($new_array[$application_id])){
                array_push($new_array[$application_id]['qualifications'],$obj);
            }else{
                $new_array[$application_id]=$obj;
                $new_array[$application_id]['qualifications'] = array();
                array_push($new_array[$application_id]['qualifications'],$obj);
            }
        }
		return $new_array;
	}
	/*
		function get_application_statistics_district_wise_two ($session_id,$program_type_id,$campus_id,$province_id,$division_id,$district_id)
	{
		$this->legacy_db = $this->load->database('admission_db',true);
		$this->legacy_db->select("ass.`ADMISSION_SESSION_ID`,
  ass.`CAMPUS_ID`,
  ass.`PROGRAM_TYPE_ID`,
  ass.`SESSION_ID`,
  TRIM(BOTH '\"' FROM JSON_EXTRACT (app.FORM_DATA,'$.users_reg.PROVINCE_ID')) AS PROVINCE_ID,
  TRIM(BOTH '\"' FROM JSON_EXTRACT (app.FORM_DATA,'$.users_reg.DIVISION_ID')) AS DIVISION_ID,
  TRIM(    BOTH '\"' FROM JSON_EXTRACT (      app.FORM_DATA,      '$.users_reg.DISTRICT_ID'    )  ) AS DISTRICT_ID,
  c.`NAME`,
  pt.`PROGRAM_TITLE`,
  s.`REMARKS`,
  TRIM(    BOTH '\"' FROM JSON_EXTRACT (      app.FORM_DATA,      '$.users_reg.DISTRICT_NAME'    )  ) AS DISTRICT_NAME,
  COUNT(    CASE      WHEN app.`STATUS_ID` = 1       THEN app.`APPLICATION_ID`     END  ) AS 'DRAFT',
  COUNT(    CASE      WHEN app.`IS_SUBMITTED` = 'Ý'       THEN app.`APPLICATION_ID`     END  ) AS 'SUBMITTED',
  COUNT(    CASE      WHEN app.`STATUS_ID` = 4       THEN app.`APPLICATION_ID`    END  ) 'IN_REVIEW',
  COUNT(    CASE      WHEN app.`STATUS_ID` = 3       THEN app.`APPLICATION_ID`     END  ) 'IN_PROCESS',
  COUNT(    CASE      WHEN app.`STATUS_ID` = 5       THEN app.`APPLICATION_ID`    END  ) 'FORM_VERIFIED',
  COUNT(    CASE      WHEN app.`STATUS_ID` = 6       THEN app.`APPLICATION_ID`     END  ) 'FORM_REJECTED',
  COUNT(    CASE      WHEN app.`STATUS_ID` = 8       THEN app.`APPLICATION_ID`     END  ) 'ENROLLED',
  COUNT(    CASE      WHEN app.`APPLICATION_ID` = ac.`APPLICATION_ID`       THEN ac.`APPLICATION_ID`     END  ) 'TOTAL_ADMIT_CARDS',
  COUNT(    CASE      WHEN ac.`IS_DISPATCHED` = 'N'       THEN ac.`APPLICATION_ID`     END  ) 'NOT_DISPATCHED',
  COUNT(    CASE      WHEN ac.`IS_DISPATCHED` = 'Y'       THEN ac.`APPLICATION_ID`     END  )");
		$this->legacy_db->from('program_type pt,campus c, `sessions` s');
		$this->legacy_db->join('`admission_session` ass','s.SESSION_ID = ass.SESSION_ID','INNER');
		$this->legacy_db->join('`applications` app','ass.ADMISSION_SESSION_ID = app.ADMISSION_SESSION_ID','INNER');
		$this->legacy_db->join('admit_card ac','app.`APPLICATION_ID` = ac.`APPLICATION_ID`','LEFT');
		$this->legacy_db->where("c.`CAMPUS_ID` = ass.`CAMPUS_ID`");
		$this->legacy_db->where("pt.`PROGRAM_TYPE_ID` = ass.`PROGRAM_TYPE_ID`");
		$this->legacy_db->where("app.APPLICATION_ID>0");
	    $this->legacy_db->group_by("ass.`ADMISSION_SESSION_ID`,DISTRICT_ID");     
		$this->legacy_db->order_by("s.`SESSION_ID`,ass.`CAMPUS_ID`,ass.`PROGRAM_TYPE_ID`,PROVINCE_ID,DIVISION_ID,DISTRICT_ID");
		return($this->legacy_db->get()->result_array());
	}*/
	
	function getFeeStatistics(){
	    $this->legacy_db = $this->load->database('admission_db',true);
	    
	    //$result = intval($session_id);
	    $this->legacy_db->select('*');
	    $this->legacy_db->from('sessions se');
	    $this->legacy_db->where('se.SESSION_ID', 1);
	    $result = $this->legacy_db->get()->result_array();
	    
	    
	    //$this->legacy_db->select('fc.CHALLAN_NO');
	    //$this->legacy_db->from('fee_challan fc');
	    //$this->legacy_db->join('selection_list sl','fc.SELECTION_LIST_ID = sl.SELECTION_LIST_ID AND fc.APPLICATION_ID = sl.APPLICATION_ID');
	    //$this->legacy_db->join('admission_session ads','sl.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
	    //$this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
	    //$this->legacy_db->where('se.SESSION_ID', $session_id);
	    //$this->legacy_db->where('ads.PROGRAM_TYPE_ID', $program_type_id);
	    //$this->legacy_db->where('fc.PART_ID', $part_id);
	    //$result = $this->legacy_db->get()->result_array();

		return($result);
	}
	
	function getIssuedFeeStatistics($SESSION_ID,$PROGRAM_TYPE_ID,$CAMPUS_ID,$LIST_NO,$TYPE_CODE){
	    $this->legacy_db = $this->load->database('admission_db',true);
	    
	    $this->legacy_db->select('fct.DESCRIPTION, fct.FEE_TYPE_TITLE AS CATEGORY, COUNT(fc.CHALLAN_NO) AS ISSUED_CHALLAN, SUM(fc.PAYABLE_AMOUNT) AS ISSUED_AMOUNT, fct.TYPE_CODE');
	    $this->legacy_db->from('fee_challan fc');
	    $this->legacy_db->join('selection_list sl','fc.SELECTION_LIST_ID = sl.SELECTION_LIST_ID');
	    $this->legacy_db->join('admission_list al','sl.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID');
	    $this->legacy_db->join('admission_session ads','sl.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
	    $this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
	    $this->legacy_db->join('category ct','ct.CATEGORY_ID = sl.CATEGORY_ID');
	    $this->legacy_db->join('fee_category_type fct','fct.FEE_CATEGORY_TYPE_ID = ct.FEE_CATEGORY_TYPE_ID');
	    $this->legacy_db->where('sl.IS_PROVISIONAL', 'N');
	    $this->legacy_db->where('fc.CHALLAN_TYPE_ID', 1);
	    $this->legacy_db->where('al.LIST_NO', $LIST_NO);
	    $this->legacy_db->where('fc.PAYABLE_AMOUNT >', 0);
	    $this->legacy_db->where('se.SESSION_ID', $SESSION_ID);
	    $this->legacy_db->where('ads.PROGRAM_TYPE_ID', $PROGRAM_TYPE_ID);
	    $this->legacy_db->where('ads.CAMPUS_ID', $CAMPUS_ID);
	    $this->legacy_db->where('fct.TYPE_CODE', $TYPE_CODE);
	    $this->legacy_db->group_by('fct.TYPE_CODE');
	    $result = $this->legacy_db->get()->row_array();

	    return $result;
	}
	
	function getPaidFeeStatistics($SESSION_ID,$PROGRAM_TYPE_ID,$CAMPUS_ID,$LIST_NO,$TYPE_CODE){
	    $this->legacy_db = $this->load->database('admission_db',true);
	    
	    $this->legacy_db->select('fct.DESCRIPTION, fct.FEE_TYPE_TITLE AS CATEGORY, COUNT(cp.CHALLAN_NO) AS PAID_CHALLAN, SUM(cp.PAID_AMOUNT) AS PAID_AMOUNT, SUM(fc.PAYABLE_AMOUNT) AS PAYABLE_AMOUNT, fct.TYPE_CODE');
	    $this->legacy_db->from('challan_paid cp');
	    $this->legacy_db->join('fee_challan fc','cp.CHALLAN_NO = fc.CHALLAN_NO');
	    $this->legacy_db->join('selection_list sl','fc.SELECTION_LIST_ID = sl.SELECTION_LIST_ID');
	    $this->legacy_db->join('admission_list al','sl.ADMISSION_LIST_ID = al.ADMISSION_LIST_ID');
	    $this->legacy_db->join('admission_session ads','sl.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
	    $this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
	    $this->legacy_db->join('category ct','ct.CATEGORY_ID = sl.CATEGORY_ID');
	    $this->legacy_db->join('fee_category_type fct','fct.FEE_CATEGORY_TYPE_ID = ct.FEE_CATEGORY_TYPE_ID');
	    $this->legacy_db->where('al.LIST_NO', $LIST_NO);
	    $this->legacy_db->where('se.SESSION_ID', $SESSION_ID);
	    $this->legacy_db->where('ads.PROGRAM_TYPE_ID', $PROGRAM_TYPE_ID);
	    $this->legacy_db->where('ads.CAMPUS_ID', $CAMPUS_ID);
	    $this->legacy_db->where('fct.TYPE_CODE', $TYPE_CODE);
	    $this->legacy_db->group_by('cp.TYPE_CODE');
	    $result = $this->legacy_db->get()->row_array();
	    return $result;
	}
}
