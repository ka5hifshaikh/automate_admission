<?php


class StudentReports_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
//		$CI =& get_instance();
		$this->load->model('log_model');
		
	}

    function getStudentByProgram($campus_id,$program_type_id,$session_id,$shif_id,$prog_list_id_str,$part_id,$roll_no=null) {
        $cond = "";
        if($roll_no){
            $cond = "AND CONCAT(sess.SESSION_CODE,'/',spm.PROG_CODE,'/',sl.ROLL_NO_CODE) in ($roll_no) ";
        }
        //print_r($cond);
        //exit();
        $query ="SELECT cam.NAME AS CAMPUS_NAME, app.APPLICATION_ID,ur.USER_ID, ur.FIRST_NAME, ur.FNAME, ur.LAST_NAME, 
        pl.PROGRAM_TITLE, pl.PROGRAM_TITLE_CARD, cat.CATEGORY_NAME, cat.CATEGORY_NAME_CARD, ur.BLOOD_GROUP, ur.HOME_ADDRESS, ur.CNIC_NO, ur.FAMILY_CONTACT_NO, 
        CONCAT(sess.SESSION_CODE,'/',spm.PROG_CODE,'/',sl.ROLL_NO_CODE) AS ROLL_NO, ur.PROFILE_IMAGE, class.PART_NAME, spm.DEPT_NAME,
        sess.YEAR AS BATCH_YEAR, class.PART_NO, sh.SHIFT_NAME, pl.PROG_LIST_ID, class.NAME_PHARM, sh.SHIFT_ID, ass.PROGRAM_TYPE_ID, cam.CAMPUS_ID
        FROM selection_list sl 
        JOIN applications app ON (sl.APPLICATION_ID = app.APPLICATION_ID)
        JOIN users_reg ur ON (ur.USER_ID = app.USER_ID)
        JOIN admission_session ass ON (ass.ADMISSION_SESSION_ID = app.ADMISSION_SESSION_ID)
        JOIN campus cam ON (cam.CAMPUS_ID = ass.CAMPUS_ID)
        JOIN sessions sess ON (sess.SESSION_ID = ass.SESSION_ID)
        JOIN program_list pl ON (sl.PROG_LIST_ID = pl.PROG_LIST_ID)
        JOIN category cat ON (sl.CATEGORY_ID = cat.CATEGORY_ID)
        JOIN shift_program_mapping spm ON (cam.CAMPUS_ID = spm.CAMPUS_ID AND ass.PROGRAM_TYPE_ID = spm.PROGRAM_TYPE_ID AND sl.SHIFT_ID = spm.SHIFT_ID AND pl.PROG_LIST_ID = spm.PROG_LIST_ID )
       
        JOIN shift sh ON (sl.SHIFT_ID = sh.SHIFT_ID)
        JOIN (
          SELECT ca.APPLICATION_ID, fl.SELECTION_LIST_ID, p.PART_ID, p.NAME AS PART_NAME, p.PART_NO, p.NAME_PHARM
          FROM candidate_account ca 
          JOIN fee_ledger fl ON (ca.ACCOUNT_ID = fl.ACCOUNT_ID)
          JOIN fee_program_list fpl ON (fl.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID)
          JOIN part p ON (fpl.PART_ID = p.PART_ID)
          WHERE ca.ACTIVE=1 AND fl.IS_YES = 'Y' AND fl.CHALLAN_TYPE_ID IN(1,4) AND p.PART_ID = '$part_id'
          GROUP BY fl.SELECTION_LIST_ID) AS class ON (sl.APPLICATION_ID = class.APPLICATION_ID AND sl.SELECTION_LIST_ID = class.SELECTION_LIST_ID)
        WHERE 
              sess.SESSION_ID = '$session_id'
          AND cam.CAMPUS_ID = '$campus_id'
          AND ass.PROGRAM_TYPE_ID = '$program_type_id'
          AND sl.SHIFT_ID = '$shif_id'
          AND pl.PROG_LIST_ID in ($prog_list_id_str)
          AND class.PART_ID = '$part_id'
          AND sl.IS_PROVISIONAL = 'N' 
          AND sl.ACTIVE = 1 
          AND sl.ROLL_NO_CODE>0
          $cond
        GROUP BY sl.SELECTION_LIST_ID
        ORDER BY spm.PROG_CODE,sl.ROLL_NO_CODE";
        
        $this->legacy_db = $this->load->database('admission_db',true);
	        //echo $query;
	       // exit();
		$q = $this->legacy_db->query($query);
	
		$result = $q->result_array();
		return $result;
    }
    
    function getStudentInfo($searchBy,$searchValue){
        if($searchBy==1)$searchValue;
        else exit('Invalid input');
        $stuInfo = "SELECT s.SESSION_ID,ur.USER_ID,sl.APPLICATION_ID,camp.NAME as CAMPUS_NAME,sl.SELECTION_LIST_ID, pl.PROGRAM_TYPE_ID, ur.CNIC_NO, ur.FIRST_NAME, ur.FNAME, ur.LAST_NAME,ur.GENDER, pl.PROGRAM_TITLE, cat.CATEGORY_NAME, CONCAT(s.SESSION_CODE,'/',spm.PROG_CODE,'/',sl.ROLL_NO_CODE) AS ROLL_NO, sl.SHIFT_ID
            FROM selection_list sl
            JOIN admission_session ads ON ads.ADMISSION_SESSION_ID = sl.ADMISSION_SESSION_ID
            JOIN sessions s ON s.SESSION_ID = ads.SESSION_ID
            JOIN admit_card ac ON ac.APPLICATION_ID = sl.APPLICATION_ID
            JOIN applications app ON app.APPLICATION_ID = sl.APPLICATION_ID
            JOIN users_reg ur ON ur.USER_ID = app.USER_ID
            JOIN program_list pl ON pl.PROG_LIST_ID = sl.PROG_LIST_ID
            JOIN category cat ON cat.CATEGORY_ID = sl.CATEGORY_ID
            JOIN shift_program_mapping spm ON spm.CAMPUS_ID = ads.CAMPUS_ID AND spm.SHIFT_ID = sl.SHIFT_ID AND spm.PROGRAM_TYPE_ID = ads.PROGRAM_TYPE_ID AND spm.PROG_LIST_ID = sl.PROG_LIST_ID
            JOIN campus camp ON camp.CAMPUS_ID = ads.CAMPUS_ID
            WHERE sl.IS_ENROLLED LIKE 'Y' AND sl.APPLICATION_ID = $searchValue";
        $this->legacy_db = $this->load->database('admission_db',true);
		$studentInfo = $this->legacy_db->query($stuInfo);
		$result = $studentInfo->row_array();
		return $result;
    }
    
    function getStudentInfoByPart($searchBy,$searchValue){
        if($searchBy==1)$searchValue;
        else exit('Invalid input');
        $stuInfo = "SELECT s.YEAR, sl.APPLICATION_ID, sl.SELECTION_LIST_ID, pl.PROGRAM_TYPE_ID, c.NAME AS CAMPUS_NAME, prt.NAME AS PART_NAME, sem.NAME AS SEMESTER_NAME, ur.CNIC_NO, ur.FIRST_NAME, ur.FNAME, ur.LAST_NAME, pl.PROGRAM_TITLE, cat.CATEGORY_NAME, CONCAT(s.SESSION_CODE,'/',spm.PROG_CODE,'/',sl.ROLL_NO_CODE) AS ROLL_NO
        FROM selection_list sl
        JOIN admission_session ads ON ads.ADMISSION_SESSION_ID = sl.ADMISSION_SESSION_ID
        JOIN sessions s ON s.SESSION_ID = ads.SESSION_ID
        JOIN campus c ON c.CAMPUS_ID = ads.CAMPUS_ID
        JOIN admit_card ac ON ac.APPLICATION_ID = sl.APPLICATION_ID
        JOIN applications app ON app.APPLICATION_ID = sl.APPLICATION_ID
        JOIN users_reg ur ON ur.USER_ID = app.USER_ID
        JOIN program_list pl ON pl.PROG_LIST_ID = sl.PROG_LIST_ID
        JOIN category cat ON cat.CATEGORY_ID = sl.CATEGORY_ID
        JOIN shift_program_mapping spm ON spm.CAMPUS_ID = ads.CAMPUS_ID AND spm.SHIFT_ID = sl.SHIFT_ID AND spm.PROGRAM_TYPE_ID = ads.PROGRAM_TYPE_ID AND spm.PROG_LIST_ID = sl.PROG_LIST_ID
        JOIN fee_program_list fpl ON fpl.CAMPUS_ID = ads.CAMPUS_ID AND fpl.PROG_LIST_ID = pl.PROG_LIST_ID
        JOIN part prt ON prt.PART_ID = fpl.PART_ID
        JOIN semester sem ON sem.SEMESTER_ID = fpl.SEMESTER_ID
        JOIN candidate_account ca ON app.APPLICATION_ID = ca.APPLICATION_ID
        JOIN fee_ledger fl ON fl.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID AND ca.ACCOUNT_ID = fl.ACCOUNT_ID
        JOIN challan_type ct ON ct.CHALLAN_TYPE_ID = fl.CHALLAN_TYPE_ID
        WHERE sl.IS_ENROLLED LIKE 'Y' AND ct.CHALLAN_TITLE LIKE 'ADMISSION' AND sl.APPLICATION_ID =  $searchValue
        ORDER BY prt.PART_NO DESC";
        $this->legacy_db = $this->load->database('admission_db',true);
		$studentInfo = $this->legacy_db->query($stuInfo);
		$result = $studentInfo->row_array();
		return $result;
    }
    
    function getStudentPaidChallan($searchBy,$searchValue){
        $searchValue = $this->security->xss_clean($searchValue);
        $stuAccount = "SELECT 
        p.NAME AS PART_NAME, 
        fl.CHALLAN_NO, 
        fl.CHALLAN_AMOUNT,
        fl.PAYABLE_AMOUNT,
        fl.PAID_AMOUNT,
        fc.LATE_FEE,
        DATE_FORMAT(fl.DATE,'%d-%m-%Y') AS CHALLAN_DATE, 
        fl.DETAILS, 
        fl.REMARKS,
        fl.CHALLAN_TYPE_ID
        FROM candidate_account ca
        JOIN fee_ledger fl ON fl.ACCOUNT_ID = ca.ACCOUNT_ID
        JOIN fee_challan fc ON fc.CHALLAN_NO = fl.CHALLAN_NO 
        JOIN fee_program_list fpl ON fpl.FEE_PROG_LIST_ID = fl.FEE_PROG_LIST_ID
        JOIN part p ON p.PART_ID = fpl.PART_ID
        JOIN semester s ON s.SEMESTER_ID = fpl.SEMESTER_ID
        WHERE fl.CHALLAN_TYPE_ID = 1 AND fl.PAID_AMOUNT > 0 AND ca.APPLICATION_ID = $searchValue
        ORDER BY DATE(fl.DATE)";
        $this->legacy_db = $this->load->database('admission_db',true);
		$studentAccount = $this->legacy_db->query($stuAccount);
		$result = $studentAccount->result_array();
		return $result;
    }
    
    function getStudentRefundChallan($searchBy,$searchValue){
        $searchValue = $this->security->xss_clean($searchValue);
        $stuAccount = "SELECT 
        p.NAME AS PART_NAME, 
        fl.CHALLAN_NO, 
        fl.CHALLAN_AMOUNT,
        fl.PAYABLE_AMOUNT,
        fl.PAID_AMOUNT,
        fc.LATE_FEE,
        DATE_FORMAT(fl.DATE,'%d-%m-%Y') AS CHALLAN_DATE, 
        fl.DETAILS, 
        fl.REMARKS,
        fl.CHALLAN_TYPE_ID
        FROM candidate_account ca
        JOIN fee_ledger fl ON fl.ACCOUNT_ID = ca.ACCOUNT_ID
        JOIN fee_challan fc ON fc.CHALLAN_NO = fl.CHALLAN_NO 
        JOIN fee_program_list fpl ON fpl.FEE_PROG_LIST_ID = fl.FEE_PROG_LIST_ID
        JOIN part p ON p.PART_ID = fpl.PART_ID
        JOIN semester s ON s.SEMESTER_ID = fpl.SEMESTER_ID
        WHERE fl.CHALLAN_TYPE_ID = 3 AND fl.PAID_AMOUNT < 0 AND ca.APPLICATION_ID = $searchValue";
        $this->legacy_db = $this->load->database('admission_db',true);
		$studentAccount = $this->legacy_db->query($stuAccount);
		$result = $studentAccount->result_array();
		return $result;
    }
    
    public function getSelectionOnRollNo($roll_no){
        $roll_no = $this->security->xss_clean($roll_no);
        $this->legacy_db = $this->load->database('admission_db',true);
        return $this->legacy_db->select('*')->from('selection_list')->where('ROLL_NO',$roll_no)->get()->row_array();
    }
    


}