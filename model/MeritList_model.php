<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 12/13/2020
 * Time: 3:44 PM
 */
class MeritList_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
//		$CI =& get_instance();
        $this->load->model('log_model');
        $this->legacy_db = $this->load->database('admission_db', true);
    }

     function getDisciplineSeatsDistributions($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids=array()){

        $this->legacy_db->select("dsd.`TOTAL_SEATS`,dsd.`TOTAL_SEATS_REMAINING`,pl.`PROGRAM_TITLE`,cat.`CATEGORY_NAME`,cam.`NAME`,dsd.CATEGORY_ID,dsd.PROG_LIST_ID");

        $this->legacy_db->from('discipline_seats_distributions AS dsd');
        $this->legacy_db->join('program_list AS pl', ' dsd.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'dsd.`CATEGORY_ID` = cat.`CATEGORY_ID`');
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

    function getDistinctDisciplineSeatsDistributions($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids=array()){

        $this->legacy_db->distinct('dsd.PROG_LIST_ID');
        $this->legacy_db->select("pl.`PROGRAM_TITLE`,dsd.PROG_LIST_ID");

        $this->legacy_db->from('discipline_seats_distributions AS dsd');
        $this->legacy_db->join('program_list AS pl', ' dsd.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        //$this->legacy_db->join('category AS cat', 'dsd.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('campus AS cam', 'dsd.`CAMPUS_ID` = cam.`CAMPUS_ID`');
        $this->legacy_db->where('dsd.CAMPUS_ID', $campus_id);
        $this->legacy_db->where('dsd.SHIFT_ID', $shift_id);
        $this->legacy_db->where('dsd.SESSION_ID', $session_id);
        $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        if(count($prog_list_ids)){
            $this->legacy_db->where_in('pl.PROG_LIST_ID', $prog_list_ids);
        }
        $this->legacy_db->order_by('pl.program_title');
        //$this->legacy_db->order_by('dsd.CATEGORY_ID');
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }

    function getDistrictQuotaSeats($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids=array())
    {
        $this->legacy_db->select("dqs.DISTRICT_ID,dqs.DIVISION_ID,dqs.PROG_LIST_ID,pl.`PROGRAM_TITLE`,dqs.`RURAL_SEATS`,dqs.`URBAN_SEATS`,dqs.`TOTAL_SEATS`,dqs.`RURAL_SEATS_REMAINING`,dqs.`URBAN_SEATS_REMAINING`,dqs.`TOTAL_SEATS_REMAINING`");

        $this->legacy_db->from('district_quota_seats AS dqs');
        $this->legacy_db->join('program_list AS pl', ' dqs.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        //$this->legacy_db->join('category AS cat', 'dqs.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('campus AS cam', 'dqs.`CAMPUS_ID` = cam.`CAMPUS_ID`');
        $this->legacy_db->where('dqs.CAMPUS_ID', $campus_id);
        $this->legacy_db->where('dqs.SHIFT_ID', $shift_id);
        $this->legacy_db->where('dqs.SESSION_ID', $session_id);
        $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        if(count($prog_list_ids)){
            $this->legacy_db->where_in('pl.PROG_LIST_ID', $prog_list_ids);
        }

        $this->legacy_db->order_by('pl.program_title');
        //$this->legacy_db->order_by('dqs.CATEGORY_ID');
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }

    function getSeatDistribution($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids=array()){
        $discipline_list = $this->getDistinctDisciplineSeatsDistributions($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids);
        $discipline_seat_list = $this->getDisciplineSeatsDistributions($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids);
        $district_quota_seats = $this->getDistrictQuotaSeats($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_ids);


        $discipline_merge_seat_list =array();
        foreach ($discipline_list  as $discipline){
            // prePrint($discipline);
            $seat_list = array();
            $commerce_quota = array("M"=>5,"F"=>5);
            foreach ($discipline_seat_list as $discipline_seats){

                if($discipline['PROG_LIST_ID']==$discipline_seats['PROG_LIST_ID']){
                    $seat_list[]= $discipline_seats;
                }

            }

//            if($discipline['PROG_LIST_ID']==BBA_PROG_LIST_ID || $discipline['PROG_LIST_ID']==MBA_PROG_LIST_ID){
//                $discipline['COMMERCE_QUOTA'] = $commerce_quota;
//            }
//            if($discipline['PROG_LIST_ID']==110){
//                $discipline['COMMERCE_QUOTA'] = 20;
//            }

            $district_seats_list = array();
            foreach ($district_quota_seats as $district_quota_seat){

                if($discipline['PROG_LIST_ID']==$district_quota_seat['PROG_LIST_ID']){
                    $district_seats_list[]= $district_quota_seat;
                }
            }
            if(count($district_seats_list)>0){
                $discipline['DISTRICT_QUOTA'] = $district_seats_list;
                $discipline['IS_QUOTA'] = "Y";
            }
            else{
                $discipline['IS_QUOTA'] = "N";
            }
            $discipline['CATEGORIES'] = $seat_list ;
            $discipline_merge_seat_list[] = $discipline;
        }
        return $discipline_merge_seat_list;
    }

    function addList($form_array){
        $this->load->model('log_model');
        $this->legacy_db = $this->load->database("admission_db",true);

        $this->legacy_db->trans_begin();
        $this->legacy_db->db_debug = true;
        $transaction_flag = false;
        if($this->legacy_db->insert_batch('selection_list', $form_array)){
            $transaction_flag = true;
        }else{
            $transaction_flag = false;
        }
        if ($this->legacy_db->affected_rows() >=1) {
            $transaction_flag = true;
        }else{
            $transaction_flag = false;
        }


        if($transaction_flag ==true){
            $this->legacy_db->trans_commit();

            // $this->log_model->create_log(0,0,$PRE_RECORD,$CURRENT_RECORD,"ADD_APPLICANTS_MINORS",'applicants_minors',11,$user_id);
            //  $this->log_model->itsc_log("ADD_APPLICANTS_MINORS","SUCCESS",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,"",0,'applicants_minors');

            return true;
        }
        else{
            $this->legacy_db->trans_rollback();
            //$this->log_model->create_log(0,0,$PRE_RECORD,$CURRENT_RECORD,"ADD_APPLICANTS_MINORS_FAILED",'applicants_minors',11,$user_id);
            //$this->log_model->itsc_log("ADD_APPLICANTS_MINORS","FAILED",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,"",0,'applicants_minors');

            return false;
        }

    }
    function getSelectedStudent_OLD($admission_session_id,$shift_id,$session_id,$prog_type_id){

        $this->legacy_db->select("sl.*");
        $this->legacy_db->from('selection_list AS sl');
        $this->legacy_db->join('program_list AS pl', ' sl.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'sl.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('admission_session AS ass', 'sl.`ADMISSION_SESSION_ID` = ass.`ADMISSION_SESSION_ID`');
        $this->legacy_db->join('applications AS app', 'sl.`APPLICATION_ID` = app.`APPLICATION_ID`');
        $this->legacy_db->where('ass.ADMISSION_SESSION_ID', $admission_session_id);
        $this->legacy_db->where('sl.SHIFT_ID', $shift_id);
        $this->legacy_db->where('sl.SESSION_ID', $session_id);
        $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        $result = $this->legacy_db->get()->result_array();
       // echo $this->legacy_db->last_query();
        return $result;
    }
    /*
   * YASIR CREATED FOLLOWING METHODS 25-12-2020
   * */

    function getDisciplineSeatsDistributions_detail($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_id=array()){

        $this->legacy_db->select("cat_type.`CATEGORY_NAME` AS `CATEGORY_TYPE_NAME`,`DISCIPLINE_SEAT_ID`,dsd.`TOTAL_SEATS`,dsd.`TOTAL_SEATS_REMAINING`,pl.`PROGRAM_TITLE`,cat.`CATEGORY_NAME`,cam.`NAME`,dsd.CATEGORY_ID,dsd.PROG_LIST_ID");

        $this->legacy_db->from('discipline_seats_distributions AS dsd');
        $this->legacy_db->join('program_list AS pl', ' dsd.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'dsd.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('category_type AS cat_type', 'cat_type.`CATEGORY_TYPE_ID` = cat.`CATEGORY_TYPE_ID`');
        $this->legacy_db->join('campus AS cam', 'dsd.`CAMPUS_ID` = cam.`CAMPUS_ID`');
        if ($campus_id>0) $this->legacy_db->where('dsd.CAMPUS_ID', $campus_id);
        if ($shift_id>0) $this->legacy_db->where('dsd.SHIFT_ID', $shift_id);
        if ($session_id>0) $this->legacy_db->where('dsd.SESSION_ID', $session_id);
        if ($prog_type_id>0)$this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        if (count($prog_list_id)>0)$this->legacy_db->where_in('pl.PROG_LIST_ID', $prog_list_id);
        $this->legacy_db->order_by('pl.program_title');
        $this->legacy_db->order_by('dsd.CATEGORY_ID');
        $result = $this->legacy_db->get()->result_array();
        return $result;
    }

    function getDistrictQuotaSeats_detail($campus_id,$shift_id,$session_id,$prog_type_id,$prog_list_id=array(),$district_id){

        $this->legacy_db->select("dqs.DISTRICT_QUOTE_ID,dqs.DISTRICT_ID,dqs.PROG_LIST_ID,pl.`PROGRAM_TITLE`,dqs.`RURAL_SEATS`,dqs.`URBAN_SEATS`,dqs.`TOTAL_SEATS`,dqs.`RURAL_SEATS_REMAINING`,dqs.`URBAN_SEATS_REMAINING`,dqs.`TOTAL_SEATS_REMAINING`");
        $this->legacy_db->from('district_quota_seats AS dqs');
        $this->legacy_db->join('program_list AS pl', ' dqs.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        //$this->legacy_db->join('category AS cat', 'dqs.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('campus AS cam', 'dqs.`CAMPUS_ID` = cam.`CAMPUS_ID`');
        if ($campus_id>0) $this->legacy_db->where('dqs.CAMPUS_ID', $campus_id);
        if ($shift_id>0) $this->legacy_db->where('dqs.SHIFT_ID', $shift_id);
        if ($session_id>0) $this->legacy_db->where('dqs.SESSION_ID', $session_id);
        if ($prog_type_id>0) $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        if (count($prog_list_id)>0) $this->legacy_db->where_in('pl.PROG_LIST_ID', $prog_list_id);
        if ($district_id>0) $this->legacy_db->where('dqs.DISTRICT_ID', $district_id);
        $this->legacy_db->order_by('pl.program_title');
        //$this->legacy_db->order_by('dqs.CATEGORY_ID');
        $result = $this->legacy_db->get()->result_array();
        return $result;
    }
    
     function min_cpn_report($admission_session_id,$shift_id,$session_id,$prog_type_id){

        $this->legacy_db->select("sl.PROG_LIST_ID,pl.`PROGRAM_TITLE`,sl.CATEGORY_ID ,cat.`CATEGORY_NAME`,  MIN(sl.`CPN`) AS MIN_CPN ");
        $this->legacy_db->from('selection_list AS sl');
        $this->legacy_db->join('program_list AS pl', ' sl.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'sl.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('admission_session AS ass', 'sl.`ADMISSION_SESSION_ID` = ass.`ADMISSION_SESSION_ID`');
        $this->legacy_db->join('applications AS app', 'sl.`APPLICATION_ID` = app.`APPLICATION_ID`');
        $this->legacy_db->where('ass.ADMISSION_SESSION_ID', $admission_session_id);
        $this->legacy_db->where('sl.SHIFT_ID', $shift_id);
        $this->legacy_db->where('sl.SESSION_ID', $session_id);
        $this->legacy_db->where('sl.LIST_NO', 2);
        $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        $this->legacy_db->group_by(array("sl.PROG_LIST_ID", "sl.CATEGORY_ID"));
        $result = $this->legacy_db->get()->result_array();
        // echo $this->legacy_db->last_query();
        return $result;
    }
    function min_cpn_report_district($admission_session_id,$shift_id,$session_id,$prog_type_id){

        $this->legacy_db->select("sl.PROG_LIST_ID,pl.`PROGRAM_TITLE`,sl.CATEGORY_ID ,cat.`CATEGORY_NAME`,DISTRICT_NAME,U_R, MIN(sl.`CPN`) AS MIN_CPN");
        $this->legacy_db->from('selection_list AS sl');
        $this->legacy_db->join('program_list AS pl', ' sl.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'sl.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('admission_session AS ass', 'sl.`ADMISSION_SESSION_ID` = ass.`ADMISSION_SESSION_ID`');
        $this->legacy_db->join('applications AS app', 'sl.`APPLICATION_ID` = app.`APPLICATION_ID`');
        $this->legacy_db->where('ass.ADMISSION_SESSION_ID', $admission_session_id);
        $this->legacy_db->where('sl.SHIFT_ID', $shift_id);
        $this->legacy_db->where('sl.SESSION_ID', $session_id);
        $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
         $this->legacy_db->where('sl.LIST_NO', 2);
        $this->legacy_db->where_in('sl.CATEGORY_ID', array(GENERAL_MERIT_JUR,GENERAL_MERIT_OUT_JUR));
        $this->legacy_db->group_by(array("sl.PROG_LIST_ID", "sl.CATEGORY_ID","sl.DISTRICT_NAME","sl.U_R"));
        $result = $this->legacy_db->get()->result_array();
        // echo $this->legacy_db->last_query();
        return $result;
    }
    
    
    /*
   * KAshif CREATED FOLLOWING METHODS 27-01-2020
   * */
    function getSelectedStudent($admission_session_id,$shift_id,$session_id,$prog_type_id,$test_id){

        $this->legacy_db->select("sl.*,ur.DISTRICT_ID,ur.GENDER,ur.U_R");
        $this->legacy_db->from('selection_list AS sl');
        $this->legacy_db->join('program_list AS pl', ' sl.`PROG_LIST_ID` = pl.`PROG_LIST_ID`');
        $this->legacy_db->join('category AS cat', 'sl.`CATEGORY_ID` = cat.`CATEGORY_ID`');
        $this->legacy_db->join('admission_session AS ass', 'sl.`ADMISSION_SESSION_ID` = ass.`ADMISSION_SESSION_ID`');
        $this->legacy_db->join('applications AS app', 'sl.`APPLICATION_ID` = app.`APPLICATION_ID`');
         $this->legacy_db->join('users_reg AS ur', 'ur.`USER_ID` = app.`USER_ID`');
        $this->legacy_db->join('admission_list AS al', 'sl.`ADMISSION_LIST_ID` = al.`ADMISSION_LIST_ID`');
        $this->legacy_db->where('ass.ADMISSION_SESSION_ID', $admission_session_id);
        $this->legacy_db->where('sl.SHIFT_ID', $shift_id);
        $this->legacy_db->where('ass.SESSION_ID', $session_id);
        $this->legacy_db->where('pl.PROGRAM_TYPE_ID', $prog_type_id);
        $this->legacy_db->where('sl.TEST_ID', $test_id);
        $this->legacy_db->where('sl.ACTIVE > 0 ');
        $this->legacy_db->where("sl.IS_PROVISIONAL = 'N' ");
         $this->legacy_db->where("sl.CATEGORY_ID != ".SPECIAL_SELF_FINANCE_CATEGORY_ID );


        $this->legacy_db->order_by("al.LIST_NO","DESC");
        $result = $this->legacy_db->get()->result_array();
        echo $this->legacy_db->last_query();
        $key_array = array();
        foreach ($result as $row){

            if(!isset($key_array[$row['APPLICATION_ID']])){
                $key_array[$row['APPLICATION_ID']] = array("SELF"=>null,"MERIT"=>null);
            }

            if((!($row['CATEGORY_ID']==SELF_FINANCE || $row['CATEGORY_ID']==OTHER_PROVINCES_SELF_FINANCE))&&$key_array[$row['APPLICATION_ID']]['MERIT']==null){
                $key_array[$row['APPLICATION_ID']]['MERIT'] = $row;
            }else if(($row['CATEGORY_ID']==SELF_FINANCE || $row['CATEGORY_ID']==OTHER_PROVINCES_SELF_FINANCE)&&$key_array[$row['APPLICATION_ID']]['SELF']==null){
                $key_array[$row['APPLICATION_ID']]['SELF'] = $row;
            }
           // array_push($key_array[$row['APPLICATION_ID']],$row);
        }
        return $key_array;
    }

    function getFeeLedger($admission_session_id,$shift_id,$session_id,$prog_type_id,$test_id){


        $this->legacy_db->select("fl.CHALLAN_TYPE_ID,fl.BANK_ACCOUNT_ID,fl.CHALLAN_NO,fl.DETAILS,fl.CHALLAN_AMOUNT,fl.PAYABLE_AMOUNT,fl.PAID_AMOUNT,fl.DATE,fl.IS_MERIT");
        $this->legacy_db->select("sl.APPLICATION_ID,app.USER_ID,sl.SELECTION_LIST_ID,sl.PROG_LIST_ID,sl.CHOICE_NO,sl.CATEGORY_ID");
        $this->legacy_db->from('candidate_account AS ca');
        $this->legacy_db->join('fee_ledger AS fl', 'fl.`ACCOUNT_ID` = ca.`ACCOUNT_ID`');
        $this->legacy_db->join('selection_list AS sl', 'sl.`SELECTION_LIST_ID` = fl.`SELECTION_LIST_ID`');
        $this->legacy_db->join('applications AS app', 'ca.`APPLICATION_ID` = app.`APPLICATION_ID`');
        $this->legacy_db->join('admission_session AS ass', 'app.`ADMISSION_SESSION_ID` = ass.`ADMISSION_SESSION_ID`');
        $this->legacy_db->where('ass.ADMISSION_SESSION_ID', $admission_session_id);
        $this->legacy_db->where('sl.SHIFT_ID', $shift_id);
        $this->legacy_db->where('ass.SESSION_ID', $session_id);
        $this->legacy_db->where('ass.PROGRAM_TYPE_ID', $prog_type_id);
        $this->legacy_db->where('sl.TEST_ID', $test_id);
        $this->legacy_db->where('fl.IS_YES', 'Y');
        $this->legacy_db->where('ca.ACTIVE', 1);
         $this->legacy_db->where("sl.CATEGORY_ID != ".SPECIAL_SELF_FINANCE_CATEGORY_ID );
        $this->legacy_db->where('sl.ACTIVE > 0 ');
        $this->legacy_db->where("sl.IS_PROVISIONAL = 'N' ");

        $result = $this->legacy_db->get()->result_array();

        $key_array = array();
        foreach ($result as $row){

            if(!isset($key_array[$row['APPLICATION_ID']])){
                $key_array[$row['APPLICATION_ID']] = array("SELF_FEE"=>null,"MERIT_FEE"=>null,"RETAIN_FEE"=>null);
            }

            if($row['IS_MERIT']=='Y'&&$row['CHALLAN_TYPE_ID']==1){
                $key_array[$row['APPLICATION_ID']]['MERIT_FEE'] = $row;
            }else if($row['IS_MERIT']=='N'&&$row['CHALLAN_TYPE_ID']==1){
                $key_array[$row['APPLICATION_ID']]['SELF_FEE'] = $row;
            }else if($row['CHALLAN_TYPE_ID']==2){
                $key_array[$row['APPLICATION_ID']]['RETAIN_FEE'] = $row;
            }

        }
        echo $this->legacy_db->last_query();
        return $key_array;
    }
    
    
    /*
   * Yasir CREATED FOLLOWING METHODS 25-02-2021
   * */

    function saveBookletAdmission($array,$op_id){
    	$application_id = $array['APPLICATION_ID'];
    	$user_id 		= $array['USER_ID'];
    	$shift_id 		= $array['SHIFT_ID'];
		$this->legacy_db->select("*");
		$this->legacy_db->from("selection_list");
		$this->legacy_db->where("APPLICATION_ID",$application_id);
		$this->legacy_db->where("SHIFT_ID",$shift_id);
		$previous = $this->legacy_db->get()->result_array();
		if ($this->legacy_db->insert('selection_list',$array)){
			$this->log_model->create_log(0,$this->legacy_db->insert_id(),$previous,$array,'BOOKLET_ADMISSION','SELECTION_LIST',28,$op_id);
			$this->log_model->itsc_log('BOOKLET_ADMISSION','SUCCESS','','',$op_id,$array,$previous,0,'selection_list');
			$flag = true;
		}else{
			$flag = false;
		}
    	return $flag;
	}
    


}
