<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';

class FeesReports extends AdminLogin
{

	public function __construct(){
		parent::__construct();

		$this->load->model('Administration');
		$this->load->model('log_model');
		$this->load->model("Configuration_model");
		$this->load->model('Api_location_model');
		$this->load->model("Admission_session_model");
		$this->load->model("AdmitCard_model");
		$this->load->model('User_model');
		$this->load->model('Application_model');
		$this->load->model('FormVerificationModel');
		$this->load->model('FeeChallan_model');
		$this->load->model('Selection_list_report_model');
		$this->load->model('TestResult_model');
		$this->load->model('Statistics_model');
//		$this->load->library('javascript');
		$self = $_SERVER['PHP_SELF'];
		$self = explode('index.php/',$self);
//		prePrint($self);
		$this->script_name = $self[1];
		$this->verify_login();
	}
	
    public function candidate_ledger_for_user(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

		$sessions = $this->Admission_session_model->getSessionData();

		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['sessions'] = $sessions;
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;

		$this->load->view('include/header',$data);
//		$this->load->view('include/preloder');
		$this->load->view('include/side_bar');
		$this->load->view('include/nav',$data);
		$this->load->view('admin/candidate_ledgerReportForUser',$data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}
	
	public function candidate_ledger(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

		$sessions = $this->Admission_session_model->getSessionData();

		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['sessions'] = $sessions;
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;

		$this->load->view('include/header',$data);
//		$this->load->view('include/preloder');
		$this->load->view('include/side_bar');
		$this->load->view('include/nav',$data);
		$this->load->view('admin/candidate_ledgerReport',$data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}

	public function get_candidate_ledger(){
	    
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

			$search_by 		= isValidData($request->search_by);
			$search_value 	= isValidData($request->search_value);
			//$show_retain    = isValidData($request->show_retain);
			$error = "";
			//prePrint($request);
			//exit;
			if (empty($search_by))
				$error.="Search By is Required";
			elseif (empty($search_value))
				$error.="Search Value is Required";

			/*
			 * Search BY 1 for APPLICATION ID AND 2 BY CANDIDATE ACCOUNT ID
			 * */

			if (empty($error)){
				$records = $this->FeeChallan_model->get_candidate_ledger ($search_by,$search_value);

				 if ($records == 0 && $search_by == 1){
				 	$records = array();
					$apps = $this->Application_model->getApplicationByApplicationID($search_value);
				 //	prePrint($apps);
				// 	exit;
					$records['PROFILE'] = array('APPLICATION_ID'=>$apps['APPLICATION_ID'],'ACCOUNT_ID'=>$records['PROFILE']['ACCOUNT_ID'],'FIRST_NAME'=>$apps['FIRST_NAME'],'LAST_NAME'=>$apps['LAST_NAME'],'FNAME'=>$apps['FNAME'],'MOBILE_NO'=>$apps['MOBILE_NO'],'EMAIL'=>$apps['EMAIL'],'CNIC_NO'=>$apps['CNIC_NO']);
				}

				if(is_array($records)){
				    
					$application_id = $records['PROFILE']['APPLICATION_ID'];

					$user_id=0;
					$session_id=0;
					$program_type_id=0;
					$shift_id=0;
					$program_list_id=0;
		
					$selection_list = $this->Selection_list_report_model->get_candidate_selection_list_from_selection_list_table($user_id,$application_id,$session_id,$program_type_id,$shift_id,$program_list_id);

					if(count($selection_list)>0) {
						
						$new_selection_array = array();
						$new_challan_info = array();

						foreach ($selection_list as $list) {

							$list['CPN_MERIT_LIST'] = $this->TestResult_model->truncate_cpn($list['CPN_MERIT_LIST'], 2);
							$list['TEST_CPN'] = $this->TestResult_model->truncate_cpn($list['TEST_CPN'], 2);
						    $paid_fee = $this->FeeChallan_model->getPaidProgramFeeCandidate (0,$application_id,$list['SELECTION_LIST_ID']);
							$roll_no=	$this->Application_model->getCandidateRollNo($list['SELECTION_LIST_ID']);
//							prePrint($roll_no);
//							$roll_no = null;
							if($roll_no){
							$list['ROLL_NO']  = $roll_no['ROLL_NO'];    
							}else{
							 $list['ROLL_NO'] = null;   
							}
							
							if($paid_fee>0) $list['PAID_FEE']=true;
							else $list['PAID_FEE']=false;
							
					    	$fee_challan_list = $this->FeeChallan_model->get_candidate_admission_challan_list($application_id,$list['SELECTION_LIST_ID'], 0, 0);
		                    
						    foreach($fee_challan_list as $fee_challan){
						    	
							$fee_challan['DUE_DATE'] = getDateCustomeView($fee_challan['VALID_UPTO'], 'd-m-Y');
							$new_array = array();
							$new_array['PROFILE'] = $list;
							$new_array['FEE_CHALLAN'] = $fee_challan;
							$new_array_encoded = json_encode($new_array);
						
							    if($roll_no){
							        $enrollemt = $this->Application_model->getEnrollmentByRollNo($roll_no['ROLL_NO']);
							        if($enrollemt){
							         $send_challan_info = "https://itsc.usindh.edu.pk/student/public/challan2.php?id=".base64url_encode(base64_encode(urlencode($fee_challan['CHALLAN_NO'])))."&request=itsc&rollno={$list['ROLL_NO']}&batchID={$enrollemt['BATCH_ID']}&USER_ID=".$list['USER_ID'];         
							        }else{
							          	$send_challan_info = base_url()."PdfReport/FeeChallanPrint/".base64url_encode($new_array_encoded);   
							        }
							       
							    }else{
							        	$send_challan_info = base_url()."PdfReport/FeeChallanPrint/".base64url_encode($new_array_encoded); 
							    }
							   
							
							
							$new_array['URL_INFO'] = $send_challan_info;

							array_push($new_challan_info, $new_array);
						}
						
						    array_push($new_selection_array, $list);
						}//foreach
                        for($i=0 ; $i<count($new_challan_info);$i++){
                            for($j=0 ; $j<count($new_challan_info)-1;$j++){
                                if($new_challan_info[$j]['FEE_CHALLAN']['VALID_UPTO']>$new_challan_info[$j+1]['FEE_CHALLAN']['VALID_UPTO']){
                                 $temp = $new_challan_info[$j];
                                 $new_challan_info[$j]=$new_challan_info[$j+1];
                                 $new_challan_info[$j+1] = $temp  ;
                                }
                            }    
                        }
						$records['CHALLAN'] = $new_challan_info;
						$records['SELECTION_LIST'] = $new_selection_array;
						
					
					}else{
						$records=0;
					}
				}else{
					$records=0;
				}
			}else{
				http_response_code(204);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
			}
			if (is_array($records)){
				http_response_code(200);
				$this->output->set_content_type('application/json')->set_output(json_encode($records));
			}else{
				http_response_code(204);
				$this->output->set_content_type('application/json')->set_output(json_encode('Record not found...'));
			}
//		}
	}

	public function change_is_yes(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

//		$this->form_validation->set_rules('search_value','Search Value is required','required|trim');
//		$this->form_validation->set_rules('search_by','Search By is required','required|trim');
//		if ($this->form_validation->run()){

		$FEE_LEDGER_ID 		= isValidData($request->FEE_LEDGER_ID);
		$IS_YES 	= isValidData($request->IS_YES);
		$error = "";
		if (empty($FEE_LEDGER_ID))
			$error.="fee ledger id is Required";
		elseif (empty($IS_YES))
			$error.="could not find your given parameter";

		if (empty($error)){
			$where = "FEE_LEDGER_ID = $FEE_LEDGER_ID";
			$record = array ('IS_YES'=>$IS_YES);
			$table = "fee_ledger";
			$return = $this->FeeChallan_model->update($where,$record,null,$table);
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully updated"));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
//		}
	}

	public function change_is_merit(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

//		$this->form_validation->set_rules('search_value','Search Value is required','required|trim');
//		$this->form_validation->set_rules('search_by','Search By is required','required|trim');
//		if ($this->form_validation->run()){

		$FEE_LEDGER_ID 		= isValidData($request->FEE_LEDGER_ID);
		$IS_MERIT 	= isValidData($request->IS_MERIT);
		$error = "";
		if (empty($FEE_LEDGER_ID))
			$error.="fee ledger id is Required";
		elseif (empty($IS_MERIT))
			$error.="could not find your given parameter";

		if (empty($error)){
			$where = "FEE_LEDGER_ID = $FEE_LEDGER_ID";
			$record = array ('IS_MERIT'=>$IS_MERIT);
			$table = "fee_ledger";
			$return = $this->FeeChallan_model->update($where,$record,null,$table);
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully updated"));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
//		}
	}
	
	public function change_selection_status(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$SELECTION_LIST_ID	= isValidData($request->SELECTION_LIST_ID);
		$is_active 			= isValidData($request->is_active);
		if(isset($request->remarks)){
		$remarks 			= isValidData($request->remarks);
		}else{
		$remarks='';
		}
		$error = "";
		if (empty($SELECTION_LIST_ID))
			$error.="selection list id is Required";
		elseif ($is_active<0 || $is_active>1)
			$error.="could not find your given parameter";

		if (empty($error)){
			$where = "SELECTION_LIST_ID = $SELECTION_LIST_ID";
			$record = array ('ACTIVE'=>$is_active,'REMARKS'=>$remarks);
			$table = "selection_list";
			$return = $this->FeeChallan_model->update($where,$record,null,$table);
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully updated"));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
//		}
	}
	
	public function transfer_fee(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$SELECTION_LIST_ID	= isValidData($request->SELECTION_LIST_ID);
		$APPLICATION_ID		= isValidData($request->APPLICATION_ID);

		$error = "";
		if (empty($SELECTION_LIST_ID))
			$error.="selection list id is Required";
		elseif (empty($APPLICATION_ID))
			$error.="application id is Required";

		if (empty($error)){
			$remarks = "SELECTION CHANGED ".date('d-m-Y');
			$challan_info = $this->FeeChallan_model->get_candidate_admission_challan ($APPLICATION_ID,$SELECTION_LIST_ID,0,1);
			$FEE_PROG_LIST_ID = $challan_info['FEE_PROG_LIST_ID'];
			if(empty($FEE_PROG_LIST_ID) || $FEE_PROG_LIST_ID == 0) exit("failed");
			$candidate_account = $this->FeeChallan_model->get_candidate_account ($APPLICATION_ID);
			$ACCOUNT_ID = $candidate_account['ACCOUNT_ID'];
			$where = "ACCOUNT_ID = $ACCOUNT_ID AND IS_YES='Y' AND CHALLAN_TYPE_ID IN (1,2)";
			$record = array ('FEE_PROG_LIST_ID'=>$FEE_PROG_LIST_ID,'REMARKS'=>$remarks,'SELECTION_LIST_ID'=>$SELECTION_LIST_ID);
			$table = "fee_ledger";
			$return = $this->FeeChallan_model->update($where,$record,null,$table);
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully updated"));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
	}

	public function disable_account(){
	    
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$ACCOUNT_ID = isValidData($request->ACCOUNT_ID);
		$IS_ACTIVE 	= isValidData($request->IS_ACTIVE);
		$error = "";
		if (empty($ACCOUNT_ID))
			$error.="account id is Required";
		elseif ($IS_ACTIVE == "")
			$error.="could not find your given parameter";

		if (empty($error)){
			$where = "ACCOUNT_ID = $ACCOUNT_ID";
			$record = array ('ACTIVE'=>$IS_ACTIVE);
			$table = "candidate_account";
			$return = $this->FeeChallan_model->update($where,$record,null,$table);
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully updated"));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
//		}
	}
	
	public function generate_challan() {
	    $user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

		$sessions = $this->Admission_session_model->getSessionData();

		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['sessions'] = $sessions;
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;
		$data['campus'] = $this->Administration->getCampus();
        $data['program_type'] = $this->Administration->programTypes();
        $data['shift'] = $this->Administration->shifts();
        
		$this->load->view('include/header',$data);
		$this->load->view('include/side_bar');
		$this->load->view('include/nav',$data);
		$this->load->view('admin/generate_candidate_challan',$data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}
	
    public function getChallanData() {
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
        $generateby = isValidData($request->generateby);
        $this->load->model('ChallanSetup_model');
        $error = "";
		set_time_limit(-1);
        if($generateby == "generatebyprogram") {
			$campus_id = isValidData($request->campus_id);
			$program_type_id = isValidData($request->program_type_id);
			$shift_id = isValidData($request->shift_id);
            $fee_demerit_id = isValidData($request->fee_demerit_id);
			$prog_id = isValidData($request->prog_id);
			$part_id = isValidData($request->part_id);
			$semester_id = isValidData($request->semester_id);
			$session_id = isValidData($request->session_id);
			$challan_type_id = isValidData($request->challan_type_id);
			$checkboxCalculatePaidFee 	= isValidData($request->checkboxCalculatePaidFee);
			$checkboxDueFee 			= isValidData($request->checkboxDueFee);
            // $checkboxEnrolmentFee 		= isValidData($request->checkboxEnrolmentFee);
			$valid_upto='';
			if(isset($request->valid_upto))$valid_upto = isValidData($request->valid_upto);

			//if (isset($request->starting_challan_no)) $starting_challan_no = isValidData($request->starting_challan_no);
			//else $starting_challan_no=0;

			$otherAmount = null;
			if (isset($request->otherAmount))$otherAmount = isValidData($request->otherAmount);
			if ($otherAmount==0 || $otherAmount=='' || $otherAmount==null) $otherAmount=null;

			//if ($starting_challan_no == 0){
			//	$starting_challan_no = $this->FeeChallan_model->get_last_challan_no();
			//	if ($starting_challan_no == 0 || $starting_challan_no == null || $starting_challan_no == '') $starting_challan_no=0;
			//	else $starting_challan_no++;
			//}

			$error="";
			if (empty($campus_id))$error.="Campus is required";
			if (empty($program_type_id))$error.="Program Type is required";
			if (empty($shift_id))$error.="Shift is required";
            if (empty($fee_demerit_id))$error.="Fee Demerit is required";
			if (empty($prog_id))$error.="Program is required";
            if (empty($part_id))$error.="Part is required";
            if (empty($semester_id))$error.="Semester is required";
			if (empty($valid_upto))$error.="Valid upto is required";
			if (empty($session_id))$error.="Session is required";
			if (empty($challan_type_id))$error.="Challan type is required";
			//$fee_demerit_id=0;
            // $part_id=0;
            // $semester_id=0;

			if (empty($error)){
                $fpl_id = $this->FeeChallan_model->getFeeProgramList($campus_id,$program_type_id,$shift_id,$prog_id,$fee_demerit_id,$part_id,$semester_id);
                
                $fee_prog_list_id = $fpl_id->FEE_PROG_LIST_ID; 
                
                //$semester_arr = array(1,2,3,4,5,6,7,8,9,10);
                //$semester = array_filter($semester_arr, function($value) use ($semester_id) {
                //    return ($value < $semester_id);
                //});
                
				$records = $this->FeeChallan_model->getChallanByProgram($session_id,$fee_prog_list_id);
                prePrint($records);
				exit();
				if (!empty($records)){
					$challan_records = array();
					foreach ($records as $record){
						$prog_list_id = $record['PROG_LIST_ID'];
						$fee_category_type_id = $record['FEE_CATEGORY_TYPE_ID'];
						$session_id = $record['SESSION_ID'];
						$seat_list_shift_id = $record['SHIFT_ID'];
						$PROGRAM_TYPE_ID = $record['PROGRAM_TYPE_ID'];
						//$OLD_CHALLAN_AMOUNT = $record['OLD_CHALLAN_AMOUNT'];
						$ROLL_NO = $record['ROLL_NO'];
						$part_no = $record['PART_NO'];
						$selection_list_id = $record['SELECTION_LIST_ID'];
						$fee = $this->FeeChallan_model->get_sum_fee_structure_amount($campus_id,$program_type_id,$shift_id,$prog_list_id,$part_id,$semester_id,$session_id,$fee_demerit_id,$fee_category_type_id);
						$total_fee = $this->FeeChallan_model->get_total_fee_structure_amount($campus_id,$program_type_id,$shift_id,$prog_list_id,$session_id,$fee_category_type_id);
						$due_fee = $this->FeeChallan_model->get_due_fee($campus_id,$program_type_id,$shift_id,$prog_list_id,$fee_category_type_id,$session_id,$part_id,$semester);
						$paid_amount = $this->FeeChallan_model->get_candidate_paid_amount($record['APPLICATION_ID'], 0, 1, 1, 'Y');
						if($part_no > 1){
						    $part = $part_no - 1;
						    $check_active = $this->FeeChallan_model->get_pre_challan_paid($part,$selection_list_id);
						    if(!empty($check_active)) $active = 1; else $active = 0;
						}
						if($part_no == 1) $active = 1;
						$paid_fee = $paid_amount['PAID_AMOUNT'];
						if($checkboxCalculatePaidFee) {
							if (empty($paid_fee)) $paid_fee = 0;
							elseif ($paid_fee['PAID_AMOUNT'] == 0 || $paid_fee['PAID_AMOUNT'] == null || $paid_fee['PAID_AMOUNT'] == '') $paid_fee = 0;
							else $paid_fee = $paid_fee['PAID_AMOUNT'];
						}
						$status = "";
						$FEE_DEMERIT_ID_DB=0;
						if (empty($fee['FEE_PROG_LIST_ID']) || !isset($fee)){
							$status = "Fee structure is missing";
						}else{
							$status="OK";
							$FEE_DEMERIT_ID_DB=$fee['FEE_DEMERIT_ID'];
						}
						if ($otherAmount>0){
							$challan_amount=$otherAmount;
						}else{
							$challan_amount = $fee['AMOUNT'];
						}
						if ($checkboxDueFee){
							$payable_amount = ($due_fee['TOTAL_AMOUNT'] - $paid_fee) + $fee['AMOUNT'];
							$dues = $due_fee['TOTAL_AMOUNT'] - $paid_fee;
						}else{
							$payable_amount = $fee['AMOUNT'];
							$dues = null;
						}
						if ($checkboxCalculatePaidFee){
							$payable_amount = $paid_fee['PAID_AMOUNT'];
						}
						//prePrint($due_fee['TOTAL_AMOUNT']);
						//prePrint($paid_amount['PAID_AMOUNT']);
                        //prePrint($dues);
                        //exit();
						$remarks='';
						if ($seat_list_shift_id == 1 && $payable_amount<=$paid_fee) $remarks = "NOT PAYABLE";
						elseif (($seat_list_shift_id == 2 && $payable_amount<=$paid_fee)) $remarks = "NOT PAYABLE";
						elseif ($paid_fee>0) $remarks='DIFFERENCE FEE';
						elseif ($FEE_DEMERIT_ID_DB == 1) $remarks="FIRST AND SECOND SEMESTER FEE";
						elseif ($FEE_DEMERIT_ID_DB == 2) $remarks="FIRST SEMESTER FEE";

						//if ($status=="OK") $challan_no = $starting_challan_no; else $challan_no = '';
						
						$arr = array();
						
						$arr['STATUS'] = $status;
						$arr['CHALLAN_NO'] = $record['CHALLAN_NO'];
						$arr['APPLICATION_ID'] = $record['APPLICATION_ID'];
						$arr['ROLL_NO'] = $ROLL_NO;
						$arr['CHALLAN_TYPE_ID'] = $challan_type_id;
						$arr['BANK_ACCOUNT_ID'] = 8;
						//$arr['BANK_ACCOUNT_ID'] = $record['BANK_ACCOUNT_ID'];
						$arr['SELECTION_LIST_ID'] = $record['SELECTION_LIST_ID'];
						$arr['FIRST_NAME'] = $record['FIRST_NAME'];
						$arr['FNAME'] = $record['FNAME'];
						$arr['LAST_NAME'] = $record['LAST_NAME'];
						$arr['PROGRAM_TITLE'] = $record['PROGRAM_TITLE'];
						$arr['CATEGORY_NAME'] = $record['CATEGORY_NAME'];
						$arr['FEE_PROG_LIST_ID'] = $fee['FEE_PROG_LIST_ID'];
						$arr['FEE_DEMERIT_ID'] = $fee['FEE_DEMERIT_ID'];
						$arr['PART_ID'] = $fee['PART_ID'];
						$arr['SEMESTER_ID'] = $fee['SEMESTER_ID'];
						$arr['OLD_CHALLAN_AMOUNT'] = $total_fee['TOTAL_AMOUNT'];
						$arr['CHALLAN_AMOUNT'] = $challan_amount;
						$arr['INSTALLMENT_AMOUNT'] = $challan_amount;
						$arr['DUES'] = $dues;
						$arr['LATE_FEE'] = 0;
						$arr['PAID_AMOUNT'] = $paid_fee;
						$arr['PAYABLE_AMOUNT'] = $payable_amount;
						$arr['VALID_UPTO'] = $valid_upto;
						$arr['DATETIME'] = date('Y-m-d H:i:s');
						$arr['ADMIN_USER_ID'] = $_SESSION['ADMIN_LOGIN_FOR_ADMISSION']['USER_ID'];
						$arr['REMARKS'] = $fee['SEMESTER_NAME'];
						$arr['ACTIVE'] = 1;

						$challan_records[] = $arr;
						unset($arr);
						//if ($status == "OK") $starting_challan_no++;
					} //foreach
//					prePrint($challan_records);
//					exit();
					http_response_code(200);
					echo json_encode($challan_records);
					exit();
				}//if
			}else{
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
			}
		    
		}
        else if($generateby == "generatebyselectionlist") {

		    //$campus_id 			= isValidData($request->campus_id);
            //$program_type_id 	= isValidData($request->program_type_id);
            //$shift_id 			= isValidData($request->shift_id);
//            $fee_demerit_id = isValidData($request->fee_demerit_id);
            $admission_list_id 	= isValidData($request->admission_list_id);
//            $part_id = isValidData($request->part_id);
//            $semester_id = isValidData($request->semester_id);
			$challan_type_id 	= isValidData($request->challan_type_id);
			$checkboxCalculatePaidFee 	= isValidData($request->checkboxCalculatePaidFee);
			$checkboxDueFee 			= isValidData($request->checkboxDueFee);
			$checkboxEnrolmentFee 		= isValidData($request->checkboxEnrolmentFee);

			$valid_upto='';
            if(isset($request->valid_upto))$valid_upto = isValidData($request->valid_upto);

            //if (isset($request->starting_challan_no)) $starting_challan_no = isValidData($request->starting_challan_no);
			//else $starting_challan_no=0;

			$otherAmount = null;
			if (isset($request->otherAmount))$otherAmount = isValidData($request->otherAmount);
			if ($otherAmount==0 || $otherAmount=='' || $otherAmount==null) $otherAmount=null;

            //if ($starting_challan_no == 0){
			//	$starting_challan_no = $this->FeeChallan_model->get_last_challan_no();
			//	if ($starting_challan_no == 0 || $starting_challan_no == null || $starting_challan_no == '') $starting_challan_no=0;
			//	else $starting_challan_no++;
			//}

            $error="";
            //if (empty($campus_id))$error.="Campus is required";
            //if (empty($program_type_id))$error.="Program Type is required";
            //if (empty($shift_id))$error.="Shift is required";
//            if (empty($fee_demerit_id))$error.="Fee Demerit is required";
            if (empty($admission_list_id))$error.="Admission List is required";
//            if (empty($part_id))$error.="Part is required";
//            if (empty($semester_id))$error.="Semester is required";
            if (empty($valid_upto))$error.="Valid upto is required";
            if (empty($challan_type_id))$error.="Challan Type is required";

            $fee_demerit_id=0;
			$part_id=0;
			$semester_id=0;

			if (empty($error)){
				//$records = $this->FeeChallan_model->getStudentListForChallan($campus_id,$program_type_id,$shift_id,$fee_demerit_id,$admission_list_id,0,0,'SelectionList');
                $records = $this->ChallanSetup_model->getDataByAdmissionList($admission_list_id);
                prePrint($records);
                exit();
				if (!empty($records)){
					$challan_records = array();
					foreach ($records as $record){
						$prog_list_id = $record['PROG_LIST_ID'];
						$fee_category_type_id = $record['FEE_CATEGORY_TYPE_ID'];
						$session_id = $record['SESSION_ID'];
						$enrolment_fee = $record['ENROLMENT_FEE'];
						$seat_list_shift_id = $record['SHIFT_ID'];
						$PROGRAM_TYPE_ID = $record['PROGRAM_TYPE_ID'];

						if ($enrolment_fee ==0 || $enrolment_fee == null || $enrolment_fee == '') $enrolment_fee=0;

						$paid_fee=0;
						if($checkboxCalculatePaidFee) {
							$paid_fee = $this->FeeChallan_model->get_candidate_paid_amount($record['APPLICATION_ID'], 0, 1, 1, 'Y');
							if (empty($paid_fee)) $paid_fee = 0;
							elseif ($paid_fee['PAID_AMOUNT'] == 0 || $paid_fee['PAID_AMOUNT'] == null || $paid_fee['PAID_AMOUNT'] == '') $paid_fee = 0;
							else $paid_fee = $paid_fee['PAID_AMOUNT'];
						}

						if($PROGRAM_TYPE_ID == 1){
							$part_id=array (1);
							$semester_id= array (1,11);
						}else{
							$part_id=array (6,8);
							$semester_id= array (1,11);
						}
						$fee = $this->FeeChallan_model->get_sum_fee_structure_amount($campus_id,$program_type_id,$shift_id,$prog_list_id,$part_id,$semester_id,$session_id,0,$fee_category_type_id);

						$status = "";
						$FEE_DEMERIT_ID_DB=0;
						if (empty($fee['FEE_PROG_LIST_ID']) || !isset($fee)){
							$status = "Fee structure is missing";
						}else{
							$status="OK";
							$FEE_DEMERIT_ID_DB=$fee['FEE_DEMERIT_ID'];
						}
						if ($otherAmount>0){
							$challan_amount=$otherAmount;
						}else{
							$challan_amount = $fee['AMOUNT'];
						}
						if ($checkboxEnrolmentFee){
							$challan_amount=$challan_amount+$enrolment_fee;
						}
						if ($checkboxCalculatePaidFee){
							$payable_amount = $challan_amount-$paid_fee;
						}else{
							$payable_amount = $challan_amount;
						}

						$remarks='';
						if ($seat_list_shift_id == 1 && $challan_amount<=$paid_fee) $remarks = "NOT PAYABLE";
						elseif (($seat_list_shift_id == 2 && $challan_amount<=$paid_fee)) $remarks = "NOT PAYABLE";
						elseif ($paid_fee>0) $remarks='DIFFERENCE FEE';
						elseif ($FEE_DEMERIT_ID_DB == 1) $remarks="FIRST AND SECOND SEMESTER FEE";
						elseif ($FEE_DEMERIT_ID_DB == 2) $remarks="FIRST SEMESTER FEE";

						if ($status=="OK") $challan_no=$starting_challan_no;
						else $challan_no='';
						$arr = array();
						$arr['STATUS']=$status;
						$arr['CHALLAN_NO']=$challan_no;
						$arr['ROLL_NO']=null;
						$arr['APPLICATION_ID']=$record['APPLICATION_ID'];
						$arr['CHALLAN_TYPE_ID']=$challan_type_id;
						$arr['BANK_ACCOUNT_ID']=$record['BANK_ACCOUNT_ID'];
						$arr['SELECTION_LIST_ID']=$record['SELECTION_LIST_ID'];
						$arr['FIRST_NAME']=$record['FIRST_NAME'];
						$arr['FNAME']=$record['FNAME'];
						$arr['LAST_NAME']=$record['LAST_NAME'];
						$arr['PROGRAM_TITLE']=$record['PROGRAM_TITLE'];
						$arr['CATEGORY_NAME']=$record['CATEGORY_NAME'];
						$arr['FEE_PROG_LIST_ID']=$fee['FEE_PROG_LIST_ID'];
						$arr['FEE_DEMERIT_ID']=$fee['FEE_DEMERIT_ID'];
						$arr['PART_ID']=$fee['PART_ID'];
						$arr['SEMESTER_ID']=$fee['SEMESTER_ID'];
						$arr['OLD_CHALLAN_AMOUNT']=null;
						$arr['CHALLAN_AMOUNT']=$challan_amount;
						$arr['INSTALLMENT_AMOUNT']=$challan_amount;
						$arr['DUES']=0;
						$arr['LATE_FEE']=0;
						$arr['PAID_AMOUNT']=$paid_fee;
						$arr['PAYABLE_AMOUNT']=$payable_amount;
						$arr['VALID_UPTO']=$valid_upto;
						$arr['DATETIME']=date('Y-m-d H:i:s');
						$arr['ADMIN_USER_ID']=$_SESSION['ADMIN_LOGIN_FOR_ADMISSION']['USER_ID'];
						$arr['REMARKS']=$remarks;

						$challan_records[]=$arr;
						unset($arr);
						if ($status == "OK") $starting_challan_no++;
					} //foreach
//					prePrint($challan_records);
//					exit();
					http_response_code(200);
					echo json_encode($challan_records);
					exit();
				}//if
            }else{
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
			}
        } else {
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
	}//method
	
	public function generate_retain_challan(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$account_id	        = isValidData($request->account_id);
		$object_data		= ($request->obj_data);
	    $challan_data = null;
        /*
		$challan_data = $this->FeeChallan_model->get_cmd_record_challan_no($object_data->CHALLAN_NO);
		$challan_data = $challan_data[0];
        */
        $found = $this->FeeChallan_model->getCandidateRetainPaid($account_id,$object_data->CHALLAN_NO,RETAIN_AMOUNT);
        if(is_array($found)){
            	http_response_code(204);
            	$this->output->set_content_type('application/json')->set_output(json_encode("Retain challan already created"));
			 //   echo 'Retain challan already created';
			    exit;
        }
       
		$error = "";
		if (empty($account_id))
			$error.="Account id is Required";
		elseif(empty($object_data))
		    $error.="Invalid input";
// 		elseif(empty($challan_data))
// 		    $error.="Challan data not found";
    
		if (empty($error)){
			$remarks = "FORCED RETAINED ".date('d-m-Y');
	
            		$candidate_ledger = array(
						'ACCOUNT_ID'=>$account_id,
						'CHALLAN_TYPE_ID'=>2,
						'BANK_ACCOUNT_ID'=>$object_data->BANK_ACCOUNT_ID,
						'CHALLAN_NO'=>$object_data->CHALLAN_NO,
						'DETAILS'=>'RETAINING FEE',
						'CHALLAN_AMOUNT'=>RETAIN_AMOUNT,
						'PAYABLE_AMOUNT'=>RETAIN_AMOUNT,
						'PAID_AMOUNT'=>0,
						'DATE'=>$object_data->LEDGER_COLLECTION_DATE,
						'REMARKS'=>$remarks,
						'FEE_PROG_LIST_ID'=>$object_data->FEE_PROG_LIST_ID,
						'IS_MERIT'=>$object_data->IS_MERIT,
						'SELECTION_LIST_ID'=>$object_data->FEE_LEDGER_SELECTION_LIST_ID,
						'IS_YES'=>'Y'
					);
					
				$return = $this->FeeChallan_model->insert($candidate_ledger,'fee_ledger');
				
				// prePrint($candidate_ledger);
				// exit;
		}else{
			http_response_code(204);
			echo json_encode($error);
// 			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully Inserted"));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
	}
	
	public function programFeeReport(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;

		$this->load->view('include/header',$data);
//		$this->load->view('include/preloder');
		$this->load->view('include/side_bar');
		$this->load->view('include/nav',$data);
		$this->load->view('admin/programFeeReportWindow',$data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}

	public function getFeeCategory(){

		$postdata = file_get_contents("php://input");
		$request= json_decode($postdata);
		$fee_category = null;
		if(isset($request->flag)){
			$fee_category = $this->FeeChallan_model->get_fee_category();

		}
		if (empty($fee_category)){
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Record not found...'));
		}else{
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($fee_category));
		}
	}//method

	public function getProgramFeesReport(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id= isValidData($request->program_type_id);
		$shift_id 		= isValidData($request->shift_id);
		$campus_id 		= isValidData($request->campus_id);
		$session_id 	= isValidData($request->session_id);
		$program_id 	= ($request->program_id);
		$part_id 		= isValidData($request->part_id);
		$semester_id 	= isValidData($request->semester_id);
//		$program_id = implode($program_id,',');

		$error = "";
		if (empty($program_type_id)) $error.="Program Type is Required";
		elseif (empty($shift_id)) $error.="Shift is Required";
		elseif (empty($campus_id)) $error.="Campus is Required";
		elseif (empty($session_id)) $error.="Session is Required";
		elseif (empty($program_id)) $error.="Program is Required";
		elseif (empty($part_id)) $error.="Part is Required";

		$new_array = array();
		if (empty($error)){
			$admission_session = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
			$admission_session_id = $admission_session['ADMISSION_SESSION_ID'];
			$category_id = 0;
			$record = $this->FeeChallan_model->get_program_fee_paid_details($admission_session_id,$shift_id,$program_id,$category_id,$part_id,$semester_id);
			$campus_data = $this->Administration->getCampus ();
			$campus_data = findObjectinList($campus_data,'CAMPUS_ID',$campus_id);

			$new_array['SESSION_CODE'] = $admission_session['SESSION_CODE'];
			$new_array['DEGREE_TITLE'] = $admission_session['PROGRAM_TITLE'];
			$new_array['SHIFT'] = shift_decode($shift_id);
			$new_array['PART'] = part_decode ($part_id);
			$new_array['CAMPUS_NAME'] = $campus_data['NAME'];
			$new_array['RECORD'] = $record;
//			prePrint($campus_data);
		 echo json_encode($new_array);
		}else{
			echo $error;
			die();
		}
	}

	public function printProgramFeeReport(){
		$this->load->view('admin/printProgramFeeReport.html');
	}

	public function edit_challan_online (){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
//		$this->verify_path($this->script_name,$side_bar_data);

		$sessions = $this->Admission_session_model->getSessionData();

		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['sessions'] = $sessions;
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;

		$this->load->view('include/header',$data);
//		$this->load->view('include/preloder');
		$this->load->view('include/side_bar');
		$this->load->view('include/nav',$data);
		$this->load->view('admin/edit_challan_online_panel',$data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}

	/*
	 * getting student challans from admission_online database for the student's challan edit.
	 * */

	public function get_student_challan_online(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$search_value 	= isValidData($request->search_value);
		$error = "";
		if (empty($search_value))
			$error.="Search Value is Required";
		if (empty($error)){
			$this->legacy_db=$this->load->database("admission_v2",true);
			$rows = $this->legacy_db->select(" * ")
				->from('ug_candidate_challan')
				->where('BATCH',$search_value)
				->get()->result_array();
//			prePrint($this->legacy_db->last_query());
            $new_rows=[];
            foreach($rows as $row){
                $row['CODE_CHALLAN_NO']=base64url_encode(base64_encode(urlencode($row['CHALLAN_NO'])));
                $new_rows[]=$row;
            }
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if (is_array($rows)){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($new_rows));
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Record not found...'));
		}
//		}
	}

	public function updateOnlineChallan(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		$challan 	= $request['challan'];
		$old_challan 	= $request['old_challan'];
	
		$new_challan_with_challan_no = $challan;

		$error = "";
		if (empty($challan) || empty($old_challan))
			$error.="Empty Challan list";

		if (empty($error)){
			$challan_no = $challan['CHALLAN_NO'];

			unset($challan['CHALLAN_NO']);
			unset($challan['BATCH_ID']);
			unset($challan['USER_ID']);
			unset($challan['CODE_CHALLAN_NO']);
			
            $challan['TOTAL_AMOUNT'] = $challan['DUES']+$challan['FEE_AMOUNT'];
			$this->legacy_db=$this->load->database("admission_v2",true);
			$this->legacy_db->where('CHALLAN_NO',$challan_no);
			$this->legacy_db->update('ug_candidate_challan',$challan);

			if ($this->legacy_db->affected_rows()>0){
				$student = $this->RollNo_model->get_student_examination($new_challan_with_challan_no['BATCH'],0);
				$challan_user = $this->User_model->getUserByUserIdLegacyDb($student['USER_ID']);
				$type_code = "21-000";
				if($challan['CATEGORY_NAME']=="MERIT CATEGORY"){
				    $type_code = '21-001';
				}else if($challan['CATEGORY_NAME']=="SELF FINANCE CATEGORY"){
				    $type_code = '21-002';
				}
				else if($challan['CATEGORY_NAME']=="SPECIAL SELF FINANCE"){
				    $type_code = '21-003';
				}else if($challan['CATEGORY_NAME']=="EVENING PROGRAMME"){
				    $type_code = '21-004';
				}
				 $new_challan_with_challan_no['TOTAL_AMOUNT'] = $new_challan_with_challan_no['DUES']+$new_challan_with_challan_no['FEE_AMOUNT']+$new_challan_with_challan_no['LATE_FEE'];
				$_param = array (
					'CHALLAN_NO'=>$new_challan_with_challan_no['CHALLAN_NO'],
					'SECTION_ACCOUNT_ID'=>ADMISSION_FEE_SECTION_ACCOUNT_ID,
					'REF_NO'=>$new_challan_with_challan_no['CHALLAN_NO'],
					'ROLL_NO'=>$new_challan_with_challan_no['BATCH'],
					'BATCH_ID'=>$student['BATCH_ID'],
					'DESCRIPTION'=>$new_challan_with_challan_no['FEE_LABLE'],
					'AMOUNT'=>$new_challan_with_challan_no['TOTAL_AMOUNT'],
					'CHALLAN_DATE'=>date('Y-m-d'),
					'CNIC_NO'=>$challan_user['CNIC_NO'],
					'NAME'=>$new_challan_with_challan_no['CANDIDATE_NAME'],
					'FNAME'=>$new_challan_with_challan_no['CANDIDATE_FNAME'],
					'SURNAME'=>$new_challan_with_challan_no['CANDIDATE_SURNAME'],
					'MOBILE_NO'=>$challan_user['MOBILE_NO'],
					'EMAIL'=>$challan_user['EMAIL'],
					'PROGRAM'=>$new_challan_with_challan_no['PROGRAM']." (".$new_challan_with_challan_no['CLASS'].")",
					'DUE_DATE'=>$new_challan_with_challan_no['VALID_UPTO'],
					'PROG_CODE'=>0,
					"TYPE_CODE"=>$type_code,
					"CAMPUS_NAME"=>$new_challan_with_challan_no['CAMPUS_NAME']
				);

				$rest_response = postCURL(ONLINE_PAYMENT_TRANSFER_URL, $_param);
				$this->log_model->create_log($challan_no,$challan_no,$old_challan,$new_challan_with_challan_no,"UG_CHALLAN_UPDATE",'ug_candidate_challan',12,0);
				http_response_code(200);
				$this->output->set_content_type('application/json')->set_output(json_encode('Successfully updated.'));
			}else{
				http_response_code(204);
				$this->output->set_content_type('application/json')->set_output(json_encode('Sorry process failed..'));
			}
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
	}
	
	public function getFeeStructure(){
	    $postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id= isValidData($request->program_type_id);
		$shift_id 		= isValidData($request->shift_id);
		$campus_id 		= isValidData($request->campus_id);
		$session_id 	= isValidData($request->session_id);
		$program_id 	= ($request->program_id);
		$part_id 		= isValidData($request->part_id);
		$semester_id 	= isValidData($request->semester_id);
//		$program_id     = implode($program_id,',');

		$error = "";
		if (empty($program_type_id)) $error.="Program Type is Required";
		elseif (empty($shift_id)) $error.="Shift is Required";
		elseif (empty($campus_id)) $error.="Campus is Required";
		elseif (empty($session_id)) $error.="Session is Required";
		elseif (empty($program_id)) $error.="Program is Required";
		elseif (empty($part_id)) $error.="Part is Required";

		$new_array = array();
		if (empty($error)){
			$admission_session = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
			$admission_session_id = $admission_session['ADMISSION_SESSION_ID'];
			$category_id = 0;
			$record = $this->FeeChallan_model->get_fee_structure($session_id,$campus_id,$program_id,$part_id,$semester_id);
			$campus_data = $this->Administration->getCampus ();
			$campus_data = findObjectinList($campus_data,'CAMPUS_ID',$campus_id);

			$new_array['SESSION_CODE'] = $admission_session['SESSION_CODE'];
			$new_array['DEGREE_TITLE'] = $admission_session['PROGRAM_TITLE'];
			$new_array['SHIFT'] = shift_decode($shift_id);
			$new_array['PART'] = part_decode ($part_id);
			$new_array['CAMPUS_NAME'] = $campus_data['NAME'];
			$new_array['RECORD'] = $record;
		 echo json_encode($new_array);
		}else{
			echo $error;
			die();
		}
	}
	
	public function updateChallan(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
	
		$data = $request['challan'];
		$profile = $request['challan']['PROFILE'];
        
		$error = "";
		if (empty($data) || empty($profile)){
			http_response_code(206);
			exit("Invalid request");
		}
		
		$challan = $data['FEE_CHALLAN'];
		$REMARKS        = isValidData($challan['REMARKS']);
		$CHALLAN_AMOUNT = isValidData($challan['CHALLAN_AMOUNT']);
		$DUES           = isValidData($challan['DUES']);
		$LATE_FEE       = isValidData($challan['LATE_FEE']);
		$PAYABLE_AMOUNT = isValidData($challan['PAYABLE_AMOUNT']);
		$VALID_UPTO 	= isValidData($challan['VALID_UPTO']);
		$CHALLAN_NO 	= isValidData($challan['CHALLAN_NO']);
		$CHALLAN_DATE   = isValidData($challan['CHALLAN_DATE']);
		$ACTIVE   = isValidData($challan['ACTIVE']);
		$section_account_id = substr($CHALLAN_NO,0,2);
        
		if (empty($CHALLAN_AMOUNT) || $CHALLAN_AMOUNT<0)
			$error.="Challan Amount is required";
		elseif (empty($VALID_UPTO))
			$error.="Due Date is required";
		elseif (empty($CHALLAN_NO) || $CHALLAN_NO<0)
			$error.="Challan No is required";

		if (empty($error)){
			$where = "CHALLAN_NO = $CHALLAN_NO";
			$record = array ('REMARKS'=>$REMARKS,'CHALLAN_AMOUNT'=>$CHALLAN_AMOUNT,'DUES'=>$DUES,'LATE_FEE'=>$LATE_FEE,'PAYABLE_AMOUNT'=>$PAYABLE_AMOUNT,'VALID_UPTO'=>$VALID_UPTO,"ACTIVE"=>$ACTIVE);
			$table = "fee_challan";
			$return = $this->FeeChallan_model->update($where,$record,null,$table);
			if($return){
			    $_param = array (
					'CHALLAN_NO'=>$challan['CHALLAN_NO'],
					'SECTION_ACCOUNT_ID'=>$section_account_id,
					'REF_NO'=>$challan['CHALLAN_NO'],
					'ROLL_NO'=>$profile['ROLL_NO'],
					'BATCH_ID'=>0,
    				'DESCRIPTION'=>$challan['REMARKS'],
					'AMOUNT'=>$PAYABLE_AMOUNT+$LATE_FEE,
					'CHALLAN_DATE'=>$CHALLAN_DATE,
					'CNIC_NO'=>$data['PROFILE']['CNIC_NO'],
					'NAME'=>$profile['FIRST_NAME'],
					'FNAME'=>$profile['FNAME'],
					'SURNAME'=>$profile['LAST_NAME'],
					'MOBILE_NO'=>$profile['MOBILE_NO'],
					'EMAIL'=>$profile['EMAIL'],
					'PROGRAM'=>$data['PROFILE']['PROGRAM_TITLE'],
					'DUE_DATE'=>$VALID_UPTO,
					'PROG_CODE'=>0,
					'TYPE_CODE'=>$profile['TYPE_CODE'],
					'PROG_TYPE'=>$profile['PROGRAM_TITLE_CATE'],
					'STUDENT_TYPE'=>$profile['SHIFT_NAME']
				);
				$rest_response = postCURL(ONLINE_PAYMENT_TRANSFER_URL, $_param);
			}
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully updated"));
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
	}//method

	public function mark_paid(){
		$return=false;
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		$data = $request['challan'];

		$error = "";

		if (empty($data)){
			http_response_code(206);
			exit("Invalid request");
		}

		$challan = $data['FEE_CHALLAN'];

		$profile = $data['PROFILE'];

		$PAID_DATE = null;
		$PAID_AMOUNT = 0;
		$CHALLAN_NO = 0;

		if(isset($challan['PAID_DATE'])) $PAID_DATE = $challan['PAID_DATE'];
		if(isset($challan['PAID_AMOUNT'])) $PAID_AMOUNT = $challan['PAID_AMOUNT'];
		if(isset($challan['CHALLAN_NO'])) $CHALLAN_NO = $challan['CHALLAN_NO'];
		if(isset($challan['DETAILS'])) $DETAILS = $challan['DETAILS'];

		if (empty($CHALLAN_NO) || $CHALLAN_NO<0)
			$error.="Challan No is required";
		elseif (empty($PAID_AMOUNT))
			$error.="Paid Amount is required";
		elseif (empty($PAID_DATE))
			$error.="Paid Date is required";
		elseif (empty($DETAILS))
			$error.="Details are required";

		if (empty($error)){
			$account_id=0;
			if (isset($profile['APPLICATION_ID']) && $profile['APPLICATION_ID']>0){
				$application_id = isValidData($profile['APPLICATION_ID']);
				$candidate_account = $this->FeeChallan_model->get_candidate_account ($application_id);
				if ($candidate_account) {
					$account_id = $candidate_account['ACCOUNT_ID'];
				}else {
					$creat_account_array = array('USER_ID'=>isValidData($profile['USER_ID']),'APPLICATION_ID'=>$application_id,'FIRST_NAME'=>isValidData($profile['FIRST_NAME']),'FNAME'=>isValidData($profile['FNAME']),'LAST_NAME'=>isValidData($profile['LAST_NAME']),'DATE'=>date('Y-m-d h:i:s'),'ACTIVE'=>1,'REMARKS'=>'LEDGER-GENERATED');
					
					$account_id = $this->FeeChallan_model->create_candidate_account($creat_account_array);
				}
				if($account_id>0){
                    //$db_challan = $this->FeeChallan_model->get_candidate_challan($CHALLAN_NO);
					$db_challan = $this->FeeChallan_model->get_candidate_challan_ledger_entry ($CHALLAN_NO);
					$db_challan=$db_challan[0];
					if (empty($db_challan)){
						http_response_code(206);
						exit('Challan not found in database');
					}

					$CHALLAN_TYPE_ID = $db_challan['CHALLAN_TYPE_ID'];
					$BANK_ACCOUNT_ID = $db_challan['BANK_ACCOUNT_ID'];
					$CHALLAN_AMOUNT = $db_challan['CHALLAN_AMOUNT'];
					$PAYABLE_AMOUNT = $db_challan['PAYABLE_AMOUNT'];
				// 	$DETAILS = $db_challan['DETAILS'];
					$CHALLAN_REMARKS = $db_challan['REMARKS'];
					$FEE_PROG_LIST_ID = $db_challan['FEE_PROG_LIST_ID'];
					$IS_YES = 'Y';
					$SELECTION_LIST_ID = $db_challan['SELECTION_LIST_ID'];
					$CATEGORY_ID = $db_challan['CATEGORY_ID'];
					$CATEGORY_LABEL = null;
					if ($CATEGORY_ID == SELF_FINANCE || $CATEGORY_ID == OTHER_PROVINCES_SELF_FINANCE || $CATEGORY_ID ==SPECIAL_SELF_FINANCE_CATEGORY_ID || $CATEGORY_ID ==SELF_FINANCE_EVENING_CATEGORY_ID) {
						$CATEGORY_LABEL = "N";
					}else{
						$CATEGORY_LABEL = "Y";
					}
					$candidate_ledger_insert = array(
						'ACCOUNT_ID'=>$account_id,
						'CHALLAN_TYPE_ID'=>$CHALLAN_TYPE_ID,
						'BANK_ACCOUNT_ID'=>$BANK_ACCOUNT_ID,
						'CHALLAN_NO'=>$CHALLAN_NO,
						'DETAILS'=>$DETAILS,
						'CHALLAN_AMOUNT'=>$CHALLAN_AMOUNT,
						'PAYABLE_AMOUNT'=>$PAYABLE_AMOUNT,
						'PAID_AMOUNT'=>$PAID_AMOUNT,
						'DATE'=>$PAID_DATE,
						'REMARKS'=>$CHALLAN_REMARKS,
						'FEE_PROG_LIST_ID'=>$FEE_PROG_LIST_ID,
						'IS_MERIT'=>$CATEGORY_LABEL,
						'SELECTION_LIST_ID'=>$SELECTION_LIST_ID,
						'IS_YES'=>$IS_YES
					);
					$candidate_ledger_update = array(
						'CHALLAN_AMOUNT'=>$CHALLAN_AMOUNT,
						'PAYABLE_AMOUNT'=>$PAYABLE_AMOUNT,
						'PAID_AMOUNT'=>$PAID_AMOUNT,
						'DATE'=>$PAID_DATE,
						'DETAILS'=>$DETAILS,
						'REMARKS'=>$CHALLAN_REMARKS,
					);
					$already_paid = $this->FeeChallan_model->get_paid_challan (0,$CHALLAN_NO);
					if ($already_paid){
						$where = "FEE_LEDGER_ID=".$already_paid['FEE_LEDGER_ID'];
						$return = $this->FeeChallan_model->update($where,$candidate_ledger_update,$already_paid,'fee_ledger');
					}else{
						$this->legacy_db = $this->load->database('admission_db',true);
						if ($this->legacy_db->insert('fee_ledger',$candidate_ledger_insert)){
							$return = true;
						}else $return = false;
					}
				}// if is candidate account
			}
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		
		if ($return){
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("$CHALLAN_NO successfully marked paid."));
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode('Failed...'));
		}
	}//method
	
	public function delete_ledger_record_handler(){
	    
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		$fee_ledger_ids = $request['fee_ledger_ids'];

		$error = "";

		if (empty($fee_ledger_ids)){
			http_response_code(206);
			exit("Please select ledger records");
		}
		if (empty($error)){
			$this->legacy_db = $this->load->database('admission_db',true);
			$this->legacy_db->trans_begin();
			$flag = false;
		foreach ($fee_ledger_ids as $fee_ledger_id){
			$this->legacy_db->where('FEE_LEDGER_ID',$fee_ledger_id);
			if ($this->legacy_db->delete('fee_ledger')){
				$flag=true;
			}else{
				$flag=false;
				break;
			}
		}
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if ($flag){
			$this->legacy_db->trans_commit();
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode("Successfully Deleted."));
		}else{
			$this->legacy_db->trans_rollback();
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode('Transaction Failed...'));
		}//else
	}//method
	
	public function generate_candidate_challan() {
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		$error = null;
		set_time_limit(-1);
			if (empty($request)) $error.="Please Load challan first.";
			if (empty($error)){
				$challan_list_generated = array();
				$challan_list_not_generated = array();
				$flag=false;
				$this->legacy_db = $this->load->database('admission_db',true);
				$this->legacy_db->trans_begin();
				foreach ($request as $key=>$challan){
					$status = isValidData($challan['STATUS']);
					if ($status == "OK"){
							$new_challan = array(
								'CHALLAN_NO'=>isValidData($challan['CHALLAN_NO']),
								'APPLICATION_ID'=>isValidData($challan['APPLICATION_ID']),
								'CHALLAN_TYPE_ID'=>isValidData($challan['CHALLAN_TYPE_ID']),
								'BANK_ACCOUNT_ID'=>isValidData($challan['BANK_ACCOUNT_ID']),
								'SELECTION_LIST_ID'=>isValidData($challan['SELECTION_LIST_ID']),
								'CHALLAN_AMOUNT'=>isValidData($challan['CHALLAN_AMOUNT']),
								'INSTALLMENT_AMOUNT'=>isValidData($challan['INSTALLMENT_AMOUNT']),
								'DUES'=>isValidData($challan['DUES']),
								'LATE_FEE'=>isValidData($challan['LATE_FEE']),
								'PAYABLE_AMOUNT'=>isValidData($challan['PAYABLE_AMOUNT']),
								'VALID_UPTO'=>isValidData($challan['VALID_UPTO']),
								'DATETIME'=>isValidData($challan['DATETIME']),
								'REMARKS'=>isValidData($challan['REMARKS']),
								'ADMIN_USER_ID'=>isValidData($challan['ADMIN_USER_ID']),
								'PART_ID'=>isValidData($challan['PART_ID']),
								'SEMESTER_ID'=>isValidData($challan['SEMESTER_ID']),
								'FEE_PROG_LIST_ID'=>isValidData($challan['FEE_PROG_LIST_ID']),
								'ACTIVE'=>isValidData($challan['ACTIVE']),
							);

						if ($this->legacy_db->insert('fee_challan',$new_challan)){
							$flag=true;
							$challan_list_generated[]=$challan;
							unset($request[$key]);
						}else{
							$flag=false;
							$challan_list_not_generated[]=$challan;
							//($request[$key]);
							//break;
						}
					}else{
						$challan_list_not_generated[]=$challan;
						//unset($request[$key]);
					}
				}//foreach
				$out['CHALLAN_NOT_GENERATED']=$challan_list_not_generated;
				$out['CHALLAN_GENERATED']=$challan_list_generated;
				if ($flag){
					$this->legacy_db->trans_commit();
					$out['MESSAGE']="Challan successfully generated.";
					http_response_code(200);
					$this->output->set_content_type('application/json')->set_output(json_encode($out));
				}else{
					$this->legacy_db->roll_back();
					$out['MESSAGE']="Something went wrong while generating challan.";
					http_response_code(200);
					$this->output->set_content_type('application/json')->set_output(json_encode($out));
				}
			}else{
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
			}//else
	}//method
	
	public function get_paid_online_challan_stat(){
        $stat = $this->FeeChallan_model->get_paid_online_challan_stat();
            
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $data['stats'] = $stat;
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/get_paid_online_challan_stat');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }
    
    public function get_fees_statistics(){
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;

        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $data['stats'] = $stat;
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/get_fees_statistics_view');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
    
    public function generate_challan_no(){
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;

        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $data['stats'] = $stat;
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/generate_candidate_challan_no');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
    
    public function generate_bank_challan_no(){
        $postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		
		$generateby = $request[0]['GENERATE_BY'];
		$error = null;
		set_time_limit(-1);
		if (empty($request)) $error.="Please Load challan first.";
		if (empty($error)){
		    $challan_no_generated = array();
			$challan_no_not_generated = array();
			$flag=false;
			$this->legacy_db = $this->load->database('admission_db',true);
			$this->legacy_db->trans_begin();
			if($generateby == "generatebyprogram") {
			    foreach ($request as $key=>$challan){
			        $status = isValidData($challan['STATUS']);
                    if ($status == "OK"){
                        $new_challan_no = array(
                            'CHALLAN_NO'=>isValidData($challan['CHALLAN_NO']),
                            'CANDIDATE_ID'=>isValidData($challan['APPLICATION_ID']),
                            'CANDIDATE_NAME'=>isValidData($challan['CANDIDATE_NAME']),
                            'CANDIDATE_FNAME'=>isValidData($challan['CANDIDATE_FNAME']),
                            'BATCH_ID'=>isValidData($challan['BATCH_ID']),
                            'PROGRAM_CLASS'=>isValidData($challan['PROGRAM_CLASS']),
                            'CAMPUS_NAME'=>isValidData($challan['CAMPUS_NAME']),
                            'SHIFT'=>isValidData($challan['SHIFT']),
                            'CATEGORY'=>isValidData($challan['CATEGORY']),
                            'AY'=>isValidData($challan['AY']),
                            'SESSION_ID'=>isValidData($challan['SESSION_ID']),
                            'SELECTION_LIST_ID'=>isValidData($challan['SELECTION_LIST_ID']),
                            'FEE_PROG_LIST_ID'=>isValidData($challan['FEE_PROG_LIST_ID'])
                        );
                        if ($this->legacy_db->insert('fee_challan_bank',$new_challan_no)){
                            $flag=true;
                            $challan_no_generated[]=$challan;
                            unset($request[$key]);
                        }else{
                            $flag=false;
                            $challan_no_not_generated[]=$challan;
                        }
                    } else {
                        $challan_no_not_generated[]=$challan;
                    }
			    }
			}
			$out['CHALLAN_NOT_GENERATED']=$challan_no_not_generated;
			$out['CHALLAN_GENERATED']=$challan_no_generated;
			if ($flag){
				$this->legacy_db->trans_commit();
				$out['MESSAGE']="Challan Numbers successfully generated.";
				http_response_code(200);
				$this->output->set_content_type('application/json')->set_output(json_encode($out));
			}else{
				$this->legacy_db->roll_back();
				$out['MESSAGE']="Something went wrong while generating challan numbers.";
				http_response_code(200);
				$this->output->set_content_type('application/json')->set_output(json_encode($out));
			}
		} else {
		    http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
    }
    
    public function generate_bank_challan(){
        $postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		
		$generateby = $request[0]['GENERATE_BY'];
		
		$error = null;
		set_time_limit(-1);
		if (empty($request)) $error.="Please Load challan first.";
		if (empty($error)){
			$challan_list_generated = array();
			$challan_list_not_generated = array();
			$flag=false;
			$this->legacy_db = $this->load->database('admission_db',true);
			$this->legacy_db->trans_begin();
			if($generateby == "generatebyselectionlist") {
                foreach ($request as $key=>$challan){
                    prePrint($challan);
                    $status = isValidData($challan['STATUS']);
                    if ($status == "OK"){
                        $new_challan = array(
                            'CHALLAN_NO'=>isValidData($challan['CHALLAN_NO']),
                            'CANDIDATE_ID'=>isValidData($challan['APPLICATION_ID']),
                            'CANDIDATE_NAME'=>isValidData($challan['CANDIDATE_NAME']),
                            'CANDIDATE_FNAME'=>isValidData($challan['CANDIDATE_FNAME']),
                            'BATCH_ID'=>isValidData($challan['BATCH_ID']),
                            'PROGRAM_CLASS'=>isValidData($challan['PROGRAM_CLASS']),
                            'CAMPUS_NAME'=>isValidData($challan['CAMPUS_NAME']),
                            'SHIFT'=>isValidData($challan['SHIFT']),
                            'CATEGORY'=>isValidData($challan['CATEGORY']),
                            'AY'=>isValidData($challan['AY']),
                            'SESSION_ID'=>isValidData($challan['SESSION_ID']),
                            'SELECTION_LIST_ID'=>isValidData($challan['SELECTION_LIST_ID']),
                            'FEE_PROG_LIST_ID'=>isValidData($challan['FEE_PROG_LIST_ID']),
                        );
                        if ($this->legacy_db->insert('fee_challan_bank',$new_challan)){
                            $flag=true;
                            $challan_list_generated[]=$challan;
                            unset($request[$key]);
                        }else{
                            $flag=false;
                            $challan_list_not_generated[]=$challan;
                        }
                    }else{
                        $challan_list_not_generated[]=$challan;
                    }
    			}//foreach
			} elseif($generateby == "generatebyprogram") {
			    foreach ($request as $key=>$challan){
                    //prePrint($challan);
                    $status = isValidData($challan['STATUS']);
                    if ($status == "OK"){
                        $new_challan = array(
                            'CHALLAN_NO'=>isValidData($challan['CHALLAN_NO']),
                            'APPLICATION_ID'=>isValidData($challan['APPLICATION_ID']),
                            'CHALLAN_TYPE_ID'=>isValidData($challan['CHALLAN_TYPE_ID']),
                            'BANK_ACCOUNT_ID'=>isValidData($challan['BANK_ACCOUNT_ID']),
                            'SELECTION_LIST_ID'=>isValidData($challan['SELECTION_LIST_ID']),
                            'CHALLAN_AMOUNT'=>isValidData($challan['CHALLAN_AMOUNT']),
                            'INSTALLMENT_AMOUNT'=>isValidData($challan['INSTALLMENT_AMOUNT']),
                            'DUES'=>isValidData($challan['DUES']),
                            'LATE_FEE'=>isValidData($challan['LATE_FEE']),
                            'PAYABLE_AMOUNT'=>isValidData($challan['PAYABLE_AMOUNT']),
                            'VALID_UPTO'=>isValidData($challan['VALID_UPTO']),
                            'DATETIME'=>isValidData($challan['DATETIME']),
                            'REMARKS'=>isValidData($challan['REMARKS']),
                            'ADMIN_USER_ID'=>isValidData($challan['ADMIN_USER_ID']),
                            'PART_ID'=>isValidData($challan['PART_ID']),
                            'SEMESTER_ID'=>isValidData($challan['SEMESTER_ID']),
                            'FEE_PROG_LIST_ID'=>isValidData($challan['FEE_PROG_LIST_ID']),
                            'ACTIVE'=>isValidData($challan['ACTIVE']),
                        );
                        //prePrint($new_challan);  
                        //exit();
                        if ($this->legacy_db->insert('fee_challan',$new_challan)){
                            $flag=true;
                            $challan_list_generated[]=$challan;
                            unset($request[$key]);
                        }else{
                            $flag=false;
                            $challan_list_not_generated[]=$challan;
                        }
                    }else{
                        $challan_list_not_generated[]=$challan;
                    }
    			}//foreach
			}
				$out['CHALLAN_NOT_GENERATED']=$challan_list_not_generated;
				$out['CHALLAN_GENERATED']=$challan_list_generated;
				if ($flag){
					$this->legacy_db->trans_commit();
					$out['MESSAGE']="Challan successfully generated.";
					http_response_code(200);
					$this->output->set_content_type('application/json')->set_output(json_encode($out));
				}else{
					$this->legacy_db->roll_back();
					$out['MESSAGE']="Something went wrong while generating challan.";
					http_response_code(200);
					$this->output->set_content_type('application/json')->set_output(json_encode($out));
				}
			}else{
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
			}//else
    }
    
    public function getChallanDataForNumber(){
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];
        
        $postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
        $this->legacy_db = $this->load->database('admission_db',true);
		$generateby = isValidData($request->generateby);
		$error = "";
		
		set_time_limit(-1);
        if($generateby == "generatebyselectionlist") {
            $admission_list_id 	= isValidData($request->admission_list_id);
            $error="";
            if (empty($admission_list_id))$error.="Admission List is required";
            
			if (empty($error)){
				$records = $this->FeeChallan_model->getDataByAdmissionList($admission_list_id);
                $challan_no = $this->FeeChallan_model->getLastChallanNo($generateby);
                $challan_no = $challan_no+1;
                $status = 'OK';
                
				if (!empty($records)){
					$challan_records = array();
					foreach ($records as $record){
					    $selection_list_id = $record['SELECTION_LIST_ID'];
					    $fee_prog_list_id = $record['FEE_PROG_LIST_ID'];
					    $check_challan = $this->FeeChallan_model->checkFeeChallanBank($selection_list_id,$fee_prog_list_id);
					    //prePrint($check_challan);
				        //exit();
					    $arr = array();
					    if (empty($check_challan)){
					    
					    $arr['STATUS'] = $status;
					    $arr['GENERATE_BY'] = $generateby;
					    $arr['CHALLAN_NO'] = $challan_no++;
					    $arr['APPLICATION_ID'] = $record['CANDIDATE_ID'];
					    $arr['CANDIDATE_NAME'] = $record['CANDIDATE_NAME'];
					    $arr['CANDIDATE_FNAME'] = $record['CANDIDATE_FNAME'];
					    $arr['BATCH_ID'] = $record['BATCH_ID'];
					    $arr['PROGRAM_CLASS'] = $record['PROGRAM_CLASS'];
					    $arr['CAMPUS_NAME'] = $record['CAMPUS_NAME'];
					    $arr['SHIFT'] = $record['SHIFT'];
					    $arr['CATEGORY'] = $record['CATEGORY'];
					    $arr['AY'] = $record['AY'];
					    $arr['SESSION_ID'] = $record['SESSION_ID'];
					    $arr['SELECTION_LIST_ID'] = $record['SELECTION_LIST_ID'];
					    $arr['FEE_PROG_LIST_ID'] = $record['FEE_PROG_LIST_ID'];
					    $challan_records[]=$arr;
					    }
					    
					}
					
					http_response_code(200);
					echo json_encode($challan_records);
					exit();
				}//if
            } else {
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
			}
        } elseif($generateby == "generatebyprogram") {
            $campus_id = isValidData($request->campus_id);
            $program_type_id = isValidData($request->program_type_id);
            $shift_id = isValidData($request->shift_id);
            $part_id = isValidData($request->part_id);
            $program_id = isValidData($request->program_id);
            $semester_id = isValidData($request->semester_id);
            $session_id = isValidData($request->session_id);
            $fee_demerit_id = isValidData($request->fee_demerit_id);
            $challan_type_id = isValidData($request->challan_type_id);
            $validUpto = isValidData($request->validUpto);
            $bankAccount = isValidData($request->bankAccount);
            $error="";
            if (empty($program_type_id)) $error.="Program Type is Required";
		    elseif (empty($shift_id)) $error.="Shift is Required";
		    elseif (empty($campus_id)) $error.="Campus is Required";
		    elseif (empty($session_id)) $error.="Session is Required";
		    elseif (empty($program_id)) $error.="Program is Required";
		    elseif (empty($part_id)) $error.="Part is Required";
		    elseif (empty($semester_id)) $error.="Semester is Required";
		    elseif (empty($fee_demerit_id)) $error.="Demerit is Required";
		    elseif (empty($challan_type_id)) $error.="Challan Type is Required";
		    elseif (empty($validUpto)) $error.="Valid upto is required";
            if (empty($error)){
                $records = $this->FeeChallan_model->getDataByProgram($session_id,$campus_id,$program_type_id,$shift_id,$program_id);
                $status = 'OK';
                if (!empty($records)){
                    $challan_records = array();
                    foreach ($records as $key=>$record){
                        $campus_id = $record['CAMPUS_ID'];
                        $program_type_id = $record['PROGRAM_TYPE_ID'];
                        $prog_id = $record['PROG_LIST_ID'];
                        $shift_id = $record['SHIFT_ID'];
                        $prog_list_id = $record['PROG_LIST_ID'];
                        $session_id = $record['SESSION_ID'];
                        $selection_list_id = $record['SELECTION_LIST_ID'];
                        $fee_category_type_id = $record['FEE_CATEGORY_TYPE_ID'];
                        $user_id = $record['USER_ID'];
                        $application_id = $record['APPLICATION_ID'];
                        
                        $fpl_id = $this->FeeChallan_model->getFeeProgramList($campus_id,$program_type_id,$shift_id,$prog_list_id,$fee_demerit_id,$part_id,$semester_id);
                
                        $fee_prog_list_id = $fpl_id->FEE_PROG_LIST_ID; 
                        
                        $fee = $this->FeeChallan_model->get_sum_fee_structure_amount($campus_id,$prog_type_id,$shift_id,$prog_list_id,$part_id,$semester_id,$session_id,0,$fee_category_type_id);
                        
                        $challan_no_fcb = $this->legacy_db->get_where('fee_challan_bank', array('SELECTION_LIST_ID' => $selection_list_id, 'FEE_PROG_LIST_ID' => $fee_prog_list_id));
                        $message = "";
                        if($challan_no_fcb->num_rows() > 0){
                            $challan_no = $challan_no_fcb->row()->CHALLAN_NO;
                            $message = "Exsist";
                        } else {
                            $last_challan_no = $this->FeeChallan_model->getLastChallanNo($generateby);
                            $challan_no = $key+1+$last_challan_no;
                            $message = "Not Exsist";
                        }
                        //$where_category = array('ac.IS_ENABLE' => 'Y', 'ac.FORM_CATEGORY_ID' => 3, 'ac.USER_ID' => $user_id, 'ac.APPLICATION_ID' => $application_id);
                        //$this->legacy_db->select_max('ac.FORM_CATEGORY_ID')
                        //                ->from('application_category ac')
                        //                ->where($where_category);
                                        
                        //$emp_category = $this->legacy_db->get()->row()->FORM_CATEGORY_ID;
                        
                        //prePrint($where_fee_structure);
					    //exit(); 
                        
                        $where_fee_structure = array('fs.SESSION_ID' => $session_id, 'fpl.FEE_PROG_LIST_ID' => $fee_prog_list_id, 'fct.FEE_CATEGORY_TYPE_ID' => $fee_category_type_id);
                        
                        $this->legacy_db->select('fpl.FEE_PROG_LIST_ID,
                                                  fpl.CAMPUS_ID,
                                                  fpl.PROGRAM_TYPE_ID,
                                                  fpl.SHIFT_ID,
                                                  fpl.PROG_LIST_ID,
                                                  fs.FEE_CATEGORY_TYPE_ID,
                                                  fs.SESSION_ID,
                                                  ba.BANK_ACCOUNT_ID,
                                                  ba.ACCOUNT_TITLE,
                                                  p.PART_ID,
                                                  p.PART_NO,
                                                  s.NAME AS SEMESTER_NAME,
                                                  s.SEMESTER_ID,
                                                  fd.FEE_DEMERIT_ID,
                                                  fd.NAME AS FEE_TYPE,
                                                  SUM(fs.AMOUNT) AS CHALLAN_AMOUNT')
                                        ->from('fee_structure fs')
                                        ->join('fee_program_list fpl','fs.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID')
                                        ->join('fee_category_type fct','fct.FEE_CATEGORY_TYPE_ID = fs.FEE_CATEGORY_TYPE_ID')
                                        ->join('bank_account ba','ba.BANK_ACCOUNT_ID = fct.BANK_ACCOUNT_ID')
                                        ->join('fee_demerit fd','fpl.FEE_DEMERIT_ID = fd.FEE_DEMERIT_ID')
                                        ->join('part p','fpl.PART_ID = p.PART_ID')
                                        ->join('semester s','fpl.SEMESTER_ID = s.SEMESTER_ID')
                                        ->where($where_fee_structure)
                                        ->group_by(array('fs.SESSION_ID','fs.FEE_CATEGORY_TYPE_ID','fpl.PROG_LIST_ID',' fpl.CAMPUS_ID','fpl.SHIFT_ID'));
                        $fee_structure = $this->legacy_db->get()->row();
                        
                        if($bankAccount == 1){
                            if(intval($fee_structure->BANK_ACCOUNT_ID) == 2){
                                $bank_account = 5;
                            } elseif(intval($fee_structure->BANK_ACCOUNT_ID) == 3){
                                $bank_account = 7;
                            } elseif(intval($fee_structure->BANK_ACCOUNT_ID) == 4){
                                $bank_account = 9;
                            } elseif(intval($fee_structure->BANK_ACCOUNT_ID) == 6){
                                $bank_account = 8;
                            }
                            
                        } else {
                            $bank_account = intval($fee_structure->BANK_ACCOUNT_ID);
                        }
                        
                        if($program_type_id === '1'){
                            $where_degree_id = array(3);
                        } else {
                            $where_degree_id = array(4,5,6);
                        }
                        $where_degree = array('q.ACTIVE' => 1, 'q.USER_ID' => $user_id, 'q.APPLICATION_ID' => $application_id);
                        $this->legacy_db->select_max('dis.DEGREE_ID')
                                        ->from('qualifications q')
                                        ->join('discipline dis','q.DISCIPLINE_ID = dis.DISCIPLINE_ID')
                                        ->where($where_degree)
                                        ->where_in('dis.DEGREE_ID',$where_degree_id);
                                        
                        $degree_id = $this->legacy_db->get()->row()->DEGREE_ID;
                        
                        $where_enrolment_fee = array('q.ACTIVE' => 1, 'q.USER_ID' => $user_id, 'q.APPLICATION_ID' => $application_id, 'fe.SESSION_ID' => $session_id, 'dis.DEGREE_ID' => $degree_id);
                        $this->legacy_db->select('q.USER_ID, q.APPLICATION_ID, fe.SESSION_ID, fe.AMOUNT, dis.DEGREE_ID')
                                        ->from('qualifications q')
                                        ->join('discipline dis','q.DISCIPLINE_ID = dis.DISCIPLINE_ID')
                                        ->join('fee_enrolment fe','q.ORGANIZATION_ID = fe.INSTITUTE_ID')
                                        ->where($where_enrolment_fee);
                        $enrolment_fee = $this->legacy_db->get()->row()->AMOUNT;
                        
                        
                        $where_paid_fee = array('ca.ACTIVE' => 1, 'fl.CHALLAN_TYPE_ID' => $challan_type_id, 'fl.IS_YES' => 'Y', 'ca.APPLICATION_ID' => $application_id, 'fl.SELECTION_LIST_ID' => $selection_list_id);
                        $this->legacy_db->select('ca.APPLICATION_ID, fl.SELECTION_LIST_ID, SUM(fl.PAID_AMOUNT) AS PAID_AMOUNT, SUM(fc.LATE_FEE) AS LATE_FEE')
                                        ->from('candidate_account ca')
                                        ->join('fee_ledger fl','ca.ACCOUNT_ID = fl.ACCOUNT_ID')
                                        ->join('fee_program_list fpl','fl.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID')
                                        ->join('part p','fpl.PART_ID = p.PART_ID')
                                        ->join('fee_challan fc','fl.CHALLAN_NO = fc.CHALLAN_NO')
                                        ->where($where_paid_fee)
                                        ->group_by('fl.ACCOUNT_ID');
                        $paid_fee_record = $this->legacy_db->get()->row();
                        $paid_fee = $paid_fee_record->PAID_AMOUNT;
                        $late_fee = $paid_fee_record->LATE_FEE;
                        
                        $where_refund_amount = array('ca.ACTIVE' => 1, 'fl.IS_YES' => 'Y', 'ca.APPLICATION_ID' => $application_id, 'fl.SELECTION_LIST_ID' => $selection_list_id);
                        $this->legacy_db->select('ca.APPLICATION_ID, fl.SELECTION_LIST_ID, SUM(fl.PAID_AMOUNT) AS REFUND_AMOUNT')
                                        ->from('candidate_account ca')
                                        ->join('fee_ledger fl','ca.ACCOUNT_ID = fl.ACCOUNT_ID AND fl.CHALLAN_TYPE_ID = 3')
                                        ->where($where_refund_amount)
                                        ->group_by('fl.ACCOUNT_ID');
                        $refund_amount = $this->legacy_db->get()->row();
                        
                        if($refund_amount){
                            $refund_amount = $refund_amount->REFUND_AMOUNT;
                        }else{
                            $refund_amount = 0;
                        };
                        if($shift_id == 2){
                            if($semester_id == 2) $sem = array(1);
                            if($semester_id == 3) $sem = array(1,2);
                            if($semester_id == 4) $sem = array(1,2,3);
                            if($semester_id == 5) $sem = array(1,2,3,4);
                            if($semester_id == 6) $sem = array(1,2,3,4,5);
                            if($semester_id == 7) $sem = array(1,2,3,4,5,6);
                            if($semester_id == 8) $sem = array(1,2,3,4,5,6,7);
                            if($semester_id == 9) $sem = array(1,2,3,4,5,6,7,8);
                            if($semester_id == 10) $sem = array(1,2,3,4,5,6,7,8,9);
                        }
                        //Education 1.5 and 2.5
                        if($prog_id == 180 || $prog_id == 181 || $prog_id == 270 || $prog_id == 281){
                            if($fee_structure->SEMESTER_ID == '2'){
                                $previous_semester = array(1);
                            } elseif($fee_structure->SEMESTER_ID == '3'){
                                $previous_semester = array(1,2);
                            } elseif($fee_structure->SEMESTER_ID == '4'){
                                $previous_semester = array(1,2,3);
                            } elseif($fee_structure->SEMESTER_ID == '5'){
                                $previous_semester = array(1,2,3,4);
                            }
                            $where_previous_fee = array('fs.SESSION_ID' => $session_id, 'fs.FEE_CATEGORY_TYPE_ID' => $fee_category_type_id, 'fpl.PROG_LIST_ID' => $prog_id, 'fpl.CAMPUS_ID' => $campus_id, 'fpl.SHIFT_ID' => $shift_id, 'fpl.PROGRAM_TYPE_ID' => $program_type_id);
                            $this->legacy_db->select('fs.SESSION_ID, fs.FEE_CATEGORY_TYPE_ID, fpl.PROG_LIST_ID, fpl.CAMPUS_ID, fpl.SHIFT_ID, SUM(fs.AMOUNT) AS FEES_AMOUNT')
                                        ->from('fee_structure fs')
                                        ->join('fee_program_list fpl','fs.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID')
                                        ->join('part p','fpl.PART_ID = p.PART_ID')
                                        ->join('semester s','fpl.SEMESTER_ID = s.SEMESTER_ID')
                                        ->where($where_previous_fee)
                                        ->where_in('s.SEMESTER_ID',$previous_semester)
                                        ->group_by(array('fs.SESSION_ID','fs.FEE_CATEGORY_TYPE_ID','fpl.PROG_LIST_ID',' fpl.CAMPUS_ID','fpl.SHIFT_ID'));
                            $previous_fee = $this->legacy_db->get()->row()->FEES_AMOUNT;
                            $active = 1;
                        } else {
					        if($fee_structure->PART_NO == '2'){
					            $where_previous_part = array(1);
					            $previous_part = 1;
					        }elseif ($fee_structure->PART_NO == '3'){
					            $where_previous_part = array(1,2);
					            $previous_part = 2;
					        }elseif ($fee_structure->PART_NO == '4'){
					            $where_previous_part = array(1,2,3);
					            $previous_part = 3;
					        }elseif ($fee_structure->PART_NO == '5'){
					            $where_previous_part = array(1,2,3,4);
					            $previous_part = 4;
					        }
                            $where_previous_fee = array('fs.SESSION_ID' => $session_id, 'fs.FEE_CATEGORY_TYPE_ID' => $fee_category_type_id, 'fpl.PROG_LIST_ID' => $prog_id, 'fpl.CAMPUS_ID' => $campus_id, 'fpl.SHIFT_ID' => $shift_id, 'fpl.PROGRAM_TYPE_ID' => $program_type_id);
                            $this->legacy_db->select('fs.SESSION_ID, fs.FEE_CATEGORY_TYPE_ID, fpl.PROG_LIST_ID, fpl.CAMPUS_ID, fpl.SHIFT_ID, SUM(fs.AMOUNT) AS FEES_AMOUNT')
                                        ->from('fee_structure fs')
                                        ->join('fee_program_list fpl','fs.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID')
                                        ->join('part p','fpl.PART_ID = p.PART_ID')
                                        ->where($where_previous_fee)
                                        ->where_in('fpl.SEMESTER_ID',$sem)
                                        ->group_by(array('fs.SESSION_ID','fs.FEE_CATEGORY_TYPE_ID','fpl.PROG_LIST_ID',' fpl.CAMPUS_ID','fpl.SHIFT_ID'));
                            $previous_fee = $this->legacy_db->get()->row()->FEES_AMOUNT;
                        
                            $where_check_part = array('ca.ACTIVE' => 1, 'fl.IS_YES' => 'Y', 'ca.APPLICATION_ID' => $application_id, 'fl.SELECTION_LIST_ID' => $selection_list_id, 'p.PART_NO' => $previous_part);
                            $this->legacy_db->select('ca.APPLICATION_ID, fl.SELECTION_LIST_ID, p.PART_NO')
                                        ->from('candidate_account ca')
                                        ->join('fee_ledger fl','ca.ACCOUNT_ID = fl.ACCOUNT_ID')
                                        ->join('fee_program_list fpl','fl.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID')
                                        ->join('part p','fpl.PART_ID = p.PART_ID')
                                        ->where($where_check_part)
                                        ->group_by('p.PART_NO');
                            $check_part = $this->legacy_db->get()->row();
                        
                            if($check_part){
                                $active = 1;
                            }else{
                                $active = 0;
                            }
                        }
                        $remarks = '';
                        $dues = (intval($previous_fee) + intval($enrolment_fee)) - (intval($paid_fee) + intval($refund_amount) - intval($late_fee));
                        $payable_amount = intval($fee_structure->CHALLAN_AMOUNT) + $dues;
                        
                        if($payable_amount <= 0){
                            $remarks = $fee_structure->SEMESTER_NAME.' FEE ADJUSTED';
                        }
                        if($payable_amount > 0){
                            $remarks = $fee_structure->SEMESTER_NAME.' FEE';
                        }
                        $arr = array();
                        $arr['STATUS'] = $status;
                        $arr['GENERATE_BY'] = $generateby;
                        $arr['MSG'] = $message;
                        $arr['CHALLAN_NO'] = $challan_no++;
                        $arr['APPLICATION_ID'] = $record['APPLICATION_ID'];
                        $arr['CANDIDATE_NAME'] = $record['FIRST_NAME'];
                        $arr['CANDIDATE_FNAME'] = $record['FNAME'];
                        $arr['BATCH_ID'] = $record['ROLL_NO'];
                        $arr['PROGRAM_CLASS'] = $record['PROGRAM_TITLE'];
                        $arr['CAMPUS_NAME'] = $record['CAMPUS_NAME'];
                        $arr['SHIFT'] = $record['SHIFT_NAME'];
                        $arr['CATEGORY'] = $record['CATEGORY_NAME'];
                        $arr['CHALLAN_CATEGORY'] = $fee_structure->ACCOUNT_TITLE;
                        $arr['AY'] = $record['YEAR'];
                        $arr['SESSION_ID'] = $record['SESSION_ID'];
                        $arr['SELECTION_LIST_ID'] = $record['SELECTION_LIST_ID'];
                        $arr['FEE_PROG_LIST_ID'] = $fee_prog_list_id; 
                        $arr['CHALLAN_TYPE_ID'] = $challan_type_id;
                        $arr['BANK_ACCOUNT_ID'] = $bank_account; 
                        $arr['CHALLAN_AMOUNT'] = $fee_structure->CHALLAN_AMOUNT;
                        $arr['INSTALLMENT_AMOUNT'] = $fee_structure->CHALLAN_AMOUNT;
                        $arr['DUES'] = $dues;
                        $arr['LATE_FEE'] = 0;
                        $arr['PAYABLE_AMOUNT'] = $payable_amount;
                        $arr['VALID_UPTO'] = $validUpto;
                        $arr['DATETIME'] = date("Y-m-d");
                        $arr['REMARKS'] = $remarks;
                        $arr['ADMIN_USER_ID'] = $admin_user_id;
                        $arr['PART_ID'] = $fee_structure->PART_ID;
                        $arr['SEMESTER_ID'] = $fee_structure->SEMESTER_ID;
                        $arr['ACTIVE'] = $active;
                        $arr['ENROLMENT_FEE'] = $enrolment_fee;
                        $arr['PREVIOUS_FEE'] = $previous_fee;
                        $challan_records[]=$arr;
                    }
					http_response_code(200);
					echo json_encode($challan_records);
					exit();
				}//if
            }
        } elseif($generateby == "generatebyapplication"){
            $application_id = isValidData($request->application_id);
            $fee_demerit_id = isValidData($request->fee_demerit_id);
            $part_id = isValidData($reques->part_id);
            $semester_id = isValidData($request->semester_id);
            $error="";
            if (empty($application_id)) $error.="Application ID is Required";
            //elseif (empty($fee_demerit_id)) $error.="Demerit is Required";
		    //elseif (empty($part_id)) $error.="Part is Required";
		    //elseif (empty($semester_id)) $error.="Semester is Required";
		    $record = $this->FeeChallan_model->getDataByApplication($application_id);
            $status = 'OK';
		    
        } else {
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
    }
    
    public function feesUnpaidPDF($CAMPUS_ID=0,$DEPT_ID=0,$SendEmail=null){
		$this->load->library('header_footer');
		if( $CAMPUS_ID<0 || $DEPT_ID<0) {
			exit("Invalid input");
		}
		$CAMPUS_ID = isValidData($CAMPUS_ID);
		$DEPT_ID = isValidData($DEPT_ID);
		$programs = $this->FeeChallan_model->getProgramsByInstDept($CAMPUS_ID,$DEPT_ID);
		$title = $programs[0]['DEPT_NAME'];
		$file_name = 'AdmissionFeeNotPaid_'.str_replace(' ','_',$programs[0]['DEPT_NAME']).'.pdf';
		$dept_students = array();
		$pdf = new Header_footer(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle($title);
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(TRUE, 15);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		foreach ($programs as $key => $program) {
		    
			$PROG_CODE = $program['PROG_CODE'];
			$SHIFT_ID = $program['SHIFT_ID'];
			$pdf->QRCodeValue = $program['DEPT_NAME'];
			$pdf->HeaderTitle = $program['DEPT_NAME'];
			$pdf->HeaderCampus = $program['NAME'];
			$batches = array(2023,2022,2021,2020);
			foreach ($batches as $key => $batch) {
				$YEAR = $batch;
				$PART_NO = 2025-$YEAR;
				if($program['PROGRAM_TYPE_ID'] == 2 && $YEAR < 2023) continue;
				if($program['PROGRAM_TYPE_ID'] == 2 && $program['PROG_CODE'] != 'MBA'){
					if($YEAR == 2024) $PART_NO = 1; else $PART_NO = 2;
				}
				$students = $this->FeeChallan_model->getStudentsByInstDept($CAMPUS_ID,$SHIFT_ID,$DEPT_ID,$PROG_CODE,$YEAR);
				if($students){
					$x = 10;
					$y = 42;
					$w = 8;
					$h = 8;
					$border = 1;
					$fill = 0;
					$pageBreak = 27;
					$s_no = 0;
					$count = 0;
					foreach ($students as $key => $student) {
						$APPLICATION_ID = $student['APPLICATION_ID'];
						$FIRST_NAME = $student['FIRST_NAME'];
						$FNAME = $student['FNAME'];
						$LAST_NAME = $student['LAST_NAME'];
						$ROLL_NO = $student['ROLL_NO'];
						$SESSION_ID = $student['SESSION_ID'];
						$PROGRAM_TYPE_ID = $student['PROGRAM_TYPE_ID'];
						$fpl = $this->FeeChallan_model->getFeeProgramListID($PROG_CODE,$SESSION_ID,$PART_NO);
						$FEE_PROG_LIST_ID = $fpl['FEE_PROG_LIST_ID'];
						$FEE_DEMERIT_ID = $fpl['FEE_DEMERIT_ID'];
						$check_ledger = $this->FeeChallan_model->getLedger($APPLICATION_ID,$FEE_PROG_LIST_ID,$FEE_DEMERIT_ID,$YEAR,$PROGRAM_TYPE_ID);
						if($check_ledger != null && $check_ledger['CHALLAN_NO'] == null){
							$PROGRAM_TITLE = $program['PROGRAM_TITLE'].' - '.$program['SHIFT_NAME'].' - BATCH '.$students[0]['SESSION_CODE'];
							++$count;
							if($count == 1){
								$pdf->AddPage();
								$pdf->SetXY(15, 55);
								$pdf->SetFont('times', 'B', 11);
								$pdf->SetTextColor(255, 0, 0);
								$pdf->MultiCell(190, 0, 'LIST OF STUDENTS WHO HAVE NOT PAID ADMISSION FEES FOR THE ACADEMIC YEAR 2024', 0, 'C', 0, 1, $x, $y, true, 0, false, true);
								$pdf->SetTextColor(0, 75, 0);
								$pdf->MultiCell(190, 0, $PROGRAM_TITLE, 0, 'C', 0, 1, $x, $y = $y+5, true, 0, false, true);
								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetFillColor(191);
								$pdf->SetDrawColor(191);
								$pdf->SetLineWidth(0.3);
								$pdf->SetFont('calibrib', 'B', 10);
								$pdf->Cell(8, 5, '#', 1, 0, 'C', 1);
								$pdf->Cell(55, 5, 'STUDENT NAME', 1, 0, 'L', 1);
								$pdf->Cell(55, 5, 'FATHER NAME', 1, 0, 'L', 1);
								$pdf->Cell(36, 5, 'SURNAME', 1, 0, 'L', 1);
								$pdf->Cell(35, 5, 'ROLL NO', 1, 1, 'C', 1);
								$pdf->SetFillColor(224, 235, 255);
								$pdf->SetTextColor(0);
							}
							$s_no = ++$s_no;
							$pdf->SetFont('times', '', 10);
							$y = $y + $h;
							$pdf->MultiCell($w, $h, $s_no, $border, 'C', $fill, 0, $x, $y, true, 0, false, true, 8, 'M');
							$w = $w + 47;
							$pdf->MultiCell($w, $h, $FIRST_NAME, $border, 'L', $fill, 0, $x = $x + 8, $y, true, 0, false, true, 8, 'M');
							$pdf->MultiCell($w, $h, $FNAME, $border, 'L', $fill, 0, $x = $x + 55, $y, true, 0, false, true, 8, 'M');
							$w = $w - 20;
							$pdf->MultiCell($w, $h, $LAST_NAME, $border, 'L', $fill, 0, $x = $x + 55, $y, true, 0, false, true, 8, 'M');
							$pdf->MultiCell($w, $h, $ROLL_NO, $border, 'L', $fill, 1, $x = $x + 35, $y, true, 0, false, true, 8, 'M');
							if($count > $pageBreak){
								$pdf->AddPage();
								$pageBreak = $pageBreak + 29;
								$y = 35;
							}
						}
						$w = 8;
						$x = 10;
					}
					$y = 15;
				}	
			}
		}
		
		$pdf->lastPage();
		ob_end_clean();
		
    	if($SendEmail == "Toemail"){	
    	    $temp_file_path = sys_get_temp_dir() . '/' . $file_name;
            $pdf->Output($temp_file_path, 'F');
            $attachments = array ($temp_file_path);
            $email_body="
            <p>This report is generated on ".date('d-m-Y h:s:i A')."</p>
            <p>*This is system generated email, please don’t reply to this email</p>
    			 <p>University of Sindh reserves the right to correct any error/omission detected later on.</p>
                <br><br>
                          
                          Best Regards, <br>";
    
        $email_body = "
        <p>Dear sir/madam,</p>
        <p>
        This email serves as a formal notice regarding the enforcement of the fee submission policy for higher class students. Effective from <b>Saturday, 7th December 2024</b>, students who have not paid their fees by the <b>Friday, 6th December 2024</b> deadline must not be allowed to appear in their remaining examinations.
        </p>
        <p>
        Attached to this email are lists of students who have yet to clear their dues. Kindly ensure the following actions are taken:
        </p>
        <ol>
        <li>Circulate the Lists: Share the attached lists with student groups and post them on your departmental notice boards immediately to notify the concerned students.
        </li>
        <li>
        Updated Lists: You will receive updated lists on <b>Friday night, 6th December 2024</b>, with the names of students still ineligible to appear in the exams. Please ensure these are also circulated and enforced.
        </li>
        </ol>
        
        <p>Your cooperation in strictly implementing this policy is crucial to maintain administrative order.</p>
        
        <p>Thank you.</p>

        <p>Best regards,</p>
        <p>ITSC</p>
        <p>University of Sindh, Jamshoro</p>
        ";
        $EMAIL = $program['EMAIL'];
          
        if($EMAIL == null || $EMAIL == ""){
            return 'Mail not found';
        } 
        // $subject = "Next Higher Class Fee Defaulters of $title";
        $subject = "Enforcement of Fee Submission Policy for Higher Classes";
        $cc_to = array ('yasir.mehboob@usindh.edu.pk','admission@usindh.edu.pk','director.itsc@usindh.edu.pk','itsc@usindh.edu.pk','controller@usindh.edu.pk');
        
        $mailSend = $this->send_smtp_email_fees_list($subject,$email_body,$EMAIL,$attachments,$cc_to);
        if($mailSend){
            echo "Email sent to ".$EMAIL;
        }
        unlink($temp_file_path);
        }// end for checking wheater send to email or view
        else{
        	$pdf->Output($file_name, 'I');
            exit;    
        }

    }//function

    private function send_smtp_email_fees_list($email_subject,$email_body,$email,$attachments,$cc_to){

    	$mail_obj = $this->phpmailer_lib->mail_admission();
    	
    	try {
    		$mail_obj->addAddress($email);     //Add a recipient
    		$mail_obj->addReplyTo("admission@usindh.edu.pk");
    		$mail_obj->addCC("admission@usindh.edu.pk");
    		$mail_obj->addCC("director.itsc@usindh.edu.pk");
    		$mail_obj->addCC("itsc@usindh.edu.pk");
    		$mail_obj->addCC("controller@usindh.edu.pk");
    		$mail_obj->addCC("yasir.mehboob@usindh.edu.pk");
    		$mail_obj->addCC("ayaz.keerio@usindh.edu.pk");
    		$mail_obj->Subject = $email_subject;
    		$mail_obj->Body    = $email_body;
    		foreach ($attachments as $attachment){
    			$mail_obj->AddAttachment($attachment);
    		}
    		$mail_obj->send();
    		return true;
    	} catch (Exception $e) {
    		return false;
    	}

    }
	
	public function sendEmailToHod(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$CAMPUS_ID 		= isValidData($request->$CAMPUS_ID);
		$DEPT_ID 		= isValidData($request->$DEPT_ID);
		
		$sentMails = array ();
		
		$msent = $this->feesUnpaidPDFEmail($CAMPUS_ID,$DEPT_ID);
		
        $sno=0;
        $sentMails = array ();
        foreach($data as $progEnrolment){
        $sno++;
        $filename = $sno.".pdf";
        
            $program_title = $progEnrolment['PROGRAM_TITLE'];
            $dept_email = $progEnrolment['DEPT_EMAIL'];
            $dept_name = $progEnrolment['DEPT_NAME'];
            $std_data = $progEnrolment['STD_DATA'];
            $p_data['CLASS_ENROLMENT'] = $progEnrolment;
            $p_data['CAMPUS_NAME'] = $campus_name;
            $p_data['YEAR'] = $year;
            $p_data['PART_NAME'] = $part_name;
            $p_data['SHIFT_NAME'] = $shift_name;
        
    
        $msent = $this->generate_pdf($p_data,$sno);
        $sentMails[]=$msent;
            
        }
        
        echo json_encode($sentMails);

	}//method
	
	public function feesUnpaidPDFEmail($CAMPUS_ID,$DEPT_ID){
	    $this->load->library('header_footer');
		if( $CAMPUS_ID<0 || $DEPT_ID<0) {
			exit("Invalid input");
		}
		$CAMPUS_ID = isValidData($CAMPUS_ID);
		$DEPT_ID = isValidData($DEPT_ID);
		$programs = $this->FeeChallan_model->getProgramsByInstDept($CAMPUS_ID,$DEPT_ID);
		$title = $programs[0]['DEPT_NAME'];
		$file_name = 'AdmissionFeeNotPaid_'.str_replace(' ','_',$programs[0]['DEPT_NAME']).'.pdf';
		$dept_students = array();
		$pdf = new Header_footer(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle($title);
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(TRUE, 15);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		foreach ($programs as $key => $program) {
		    $dept_email = $program['EMAIL'];
			$PROG_CODE = $program['PROG_CODE'];
			$SHIFT_ID = $program['SHIFT_ID'];
			$pdf->QRCodeValue = $program['DEPT_NAME'];
			$pdf->HeaderTitle = $program['DEPT_NAME'];
			$pdf->HeaderCampus = $program['NAME'];
			$batches = array(2023,2022,2021,2020);
			foreach ($batches as $key => $batch) {
				$YEAR = $batch;
				$PART_NO = 2025-$YEAR;
				if($program['PROGRAM_TYPE_ID'] == 2 && $program['PROG_CODE'] != 'MBA'){
					if($YEAR == 2024) $PART_NO = 1; else $PART_NO = 2;
				}
				$students = $this->FeeChallan_model->getStudentsByInstDept($CAMPUS_ID,$SHIFT_ID,$DEPT_ID,$PROG_CODE,$YEAR);
				if($students){
					$x = 10;
					$y = 42;
					$w = 8;
					$h = 8;
					$border = 1;
					$fill = 0;
					$pageBreak = 27;
					$s_no = 0;
					$count = 0;
					foreach ($students as $key => $student) {
						$APPLICATION_ID = $student['APPLICATION_ID'];
						$FIRST_NAME = $student['FIRST_NAME'];
						$FNAME = $student['FNAME'];
						$LAST_NAME = $student['LAST_NAME'];
						$ROLL_NO = $student['ROLL_NO'];
						$SESSION_ID = $student['SESSION_ID'];
						$PROGRAM_TYPE_ID = $student['PROGRAM_TYPE_ID'];
						$fpl = $this->FeeChallan_model->getFeeProgramListID($PROG_CODE,$SESSION_ID,$PART_NO);
						$FEE_PROG_LIST_ID = $fpl['FEE_PROG_LIST_ID'];
						$FEE_DEMERIT_ID = $fpl['FEE_DEMERIT_ID'];
						$check_ledger = $this->FeeChallan_model->getLedger($APPLICATION_ID,$FEE_PROG_LIST_ID,$FEE_DEMERIT_ID,$YEAR,$PROGRAM_TYPE_ID);
						if($check_ledger != null && $check_ledger['CHALLAN_NO'] == null){
							$PROGRAM_TITLE = $program['PROGRAM_TITLE'].' - '.$program['SHIFT_NAME'].' - BATCH '.$students[0]['SESSION_CODE'];
							++$count;
							if($count == 1){
								$pdf->AddPage();
								$pdf->SetXY(15, 55);
								$pdf->SetFont('times', 'B', 11);
								$pdf->SetTextColor(255, 0, 0);
								$pdf->MultiCell(190, 0, 'LIST OF STUDENTS WHO HAVE NOT PAID ADMISSION FEES FOR THE ACADEMIC YEAR 2024', 0, 'C', 0, 1, $x, $y, true, 0, false, true);
								$pdf->SetTextColor(0, 75, 0);
								$pdf->MultiCell(190, 0, $PROGRAM_TITLE, 0, 'C', 0, 1, $x, $y = $y+5, true, 0, false, true);
								$pdf->SetTextColor(0, 0, 0);
								$pdf->SetFillColor(191);
								$pdf->SetDrawColor(191);
								$pdf->SetLineWidth(0.3);
								$pdf->SetFont('calibrib', 'B', 10);
								$pdf->Cell(8, 5, '#', 1, 0, 'C', 1);
								$pdf->Cell(55, 5, 'STUDENT NAME', 1, 0, 'L', 1);
								$pdf->Cell(55, 5, 'FATHER NAME', 1, 0, 'L', 1);
								$pdf->Cell(36, 5, 'SURNAME', 1, 0, 'L', 1);
								$pdf->Cell(35, 5, 'ROLL NO', 1, 1, 'C', 1);
								$pdf->SetFillColor(224, 235, 255);
								$pdf->SetTextColor(0);
							}
							$s_no = ++$s_no;
							$pdf->SetFont('times', '', 10);
							$y = $y + $h;
							$pdf->MultiCell($w, $h, $s_no, $border, 'C', $fill, 0, $x, $y, true, 0, false, true, 8, 'M');
							$w = $w + 47;
							$pdf->MultiCell($w, $h, $FIRST_NAME, $border, 'L', $fill, 0, $x = $x + 8, $y, true, 0, false, true, 8, 'M');
							$pdf->MultiCell($w, $h, $FNAME, $border, 'L', $fill, 0, $x = $x + 55, $y, true, 0, false, true, 8, 'M');
							$w = $w - 20;
							$pdf->MultiCell($w, $h, $LAST_NAME, $border, 'L', $fill, 0, $x = $x + 55, $y, true, 0, false, true, 8, 'M');
							$pdf->MultiCell($w, $h, $ROLL_NO, $border, 'L', $fill, 1, $x = $x + 35, $y, true, 0, false, true, 8, 'M');
							if($count > $pageBreak){
								$pdf->AddPage();
								$pageBreak = $pageBreak + 29;
								$y = 35;
							}
						}
						$w = 8;
						$x = 10;
					}
					$y = 15;
				}	
			}
		}
		$pdf->lastPage();
		ob_end_clean();
		$pdf->Output($file_name, 'I');
		
		
		
	}
	
	public function sfao(){
	    $this->legacy_db = $this->load->database('admission_db',true);
	    $this->legacy_db->select('
	                            s.*, 
	                            ur.FIRST_NAME AS ADMIN_FIRST_NAME,
	                            ur.FNAME AS ADMIN_FNAME,
	                            ur.LAST_NAME AS ADMIN_LAST_NAME,
	                            sl.ROLL_NO AS ADMIN_ROLL_NO,
	                            pl.PROGRAM_TITLE AS ADMIN_PROGRAM_TITLE,
	                            dst.DISTRICT_NAME  AS ADMIN_DISTRICT_NAME,
	                            ur.CNIC_NO AS ADMIN_CNIC_NO,
	                            se.YEAR,
	                            se.SESSION_ID,
	                            ca.ACCOUNT_ID,
	                            ads.CAMPUS_ID,
	                            ads.PROGRAM_TYPE_ID,
	                            sl.SHIFT_ID,
	                            pl.PROG_LIST_ID,
	                            ct.FEE_CATEGORY_TYPE_ID');
	    $this->legacy_db->from('sfao s');
	    $this->legacy_db->join('applications app','s.APPLICATION_ID = app.APPLICATION_ID');
	    $this->legacy_db->join('users_reg ur','app.USER_ID = ur.USER_ID');
	    $this->legacy_db->join('districts dst','ur.DISTRICT_ID = dst.DISTRICT_ID');
	    $this->legacy_db->join('candidate_account ca','app.APPLICATION_ID = ca.APPLICATION_ID');
	    $this->legacy_db->join('selection_list sl','app.APPLICATION_ID = sl.APPLICATION_ID AND sl.IS_ENROLLED = "Y"');
	    $this->legacy_db->join('category ct','sl.CATEGORY_ID = ct.CATEGORY_ID');
	    $this->legacy_db->join('program_list pl','sl.PROG_LIST_ID = pl.PROG_LIST_ID');
	    $this->legacy_db->join('admission_session ads','app.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
	    $this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
	    $this->legacy_db->where('s.SCHOLARSHIP',1);
	    $students = $this->legacy_db->get()->result_array();
	    
	    $filename = "sfao.csv";
	    $delimiter = ",";
	    header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
	    $fp = fopen('php://output', 'w');
	    fputcsv($fp, array_keys($students[0]), $delimiter);
	    $record = array();
	    foreach($students as $student){
	        $this->legacy_db->select('p.NAME AS PART_NAME, s.NAME AS SEMESTER_NAME, fl.CHALLAN_NO, fl.PAID_AMOUNT, fl.DATE');
	        $this->legacy_db->from('fee_ledger fl');
	        $this->legacy_db->join('fee_program_list fpl','fl.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID');
	        $this->legacy_db->join('part p','fpl.PART_ID = p.PART_ID');
	        $this->legacy_db->join('semester s','fpl.SEMESTER_ID = s.SEMESTER_ID');
	        $this->legacy_db->where('fl.ACCOUNT_ID',$student['ACCOUNT_ID']);
	        $this->legacy_db->where('fl.CHALLAN_TYPE_ID',1);
	        $this->legacy_db->order_by('p.PART_NO, s.SEMESTER_ID','ASC');
	        $ledger = $this->legacy_db->get()->result_array();
	        
	        $refund = $this->legacy_db->get_where('fee_ledger', array('ACCOUNT_ID' => $student['ACCOUNT_ID'], 'CHALLAN_TYPE_ID' => 3))->row_array();
	        
	        //total fee amount query
	        $this->legacy_db->select('SUM(fs.AMOUNT) AS AMOUNT');
	        $this->legacy_db->from('fee_structure fs');
	        $this->legacy_db->join('fee_program_list fpl','fs.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID');
	        $this->legacy_db->join('fee_category_type fct','fs.FEE_CATEGORY_TYPE_ID = fct.FEE_CATEGORY_TYPE_ID');
	        $this->legacy_db->where('fs.SESSION_ID',$student['SESSION_ID']);
	        $this->legacy_db->where('fpl.CAMPUS_ID',$student['CAMPUS_ID']);
	        $this->legacy_db->where('fpl.PROGRAM_TYPE_ID',$student['PROGRAM_TYPE_ID']);
	        $this->legacy_db->where('fpl.SHIFT_ID',$student['SHIFT_ID']);
	        $this->legacy_db->where('fpl.PROG_LIST_ID',$student['PROG_LIST_ID']);
	        $this->legacy_db->where('fct.FEE_CATEGORY_TYPE_ID',$student['FEE_CATEGORY_TYPE_ID']);
	        $fees = $this->legacy_db->get()->row_array();
	        
	        if($student['PROGRAM_TYPE_ID'] == 1) $DEGREE_ID = array(3); else $DEGREE_ID = array(4,5,6);
	        //enrolment fee query
	        $this->legacy_db->select('q.USER_ID, q.APPLICATION_ID, fe.SESSION_ID, fe.AMOUNT, dis.DEGREE_ID');
	        $this->legacy_db->from('qualifications q');
	        $this->legacy_db->join('discipline dis','q.DISCIPLINE_ID = dis.DISCIPLINE_ID');
	        $this->legacy_db->join('fee_enrolment fe','q.ORGANIZATION_ID = fe.INSTITUTE_ID');
	        $this->legacy_db->where_in('dis.DEGREE_ID',$DEGREE_ID);
	        $this->legacy_db->where('q.ACTIVE',1);
	        $this->legacy_db->where('q.USER_ID',$student['USER_ID']);
	        $this->legacy_db->where('q.APPLICATION_ID',$student['APPLICATION_ID']);
	        $this->legacy_db->where('fe.SESSION_ID',$student['SESSION_ID']);
	        $enrolment_fee = $this->legacy_db->get()->row_array();
	        
	        $total_fee = intval($fees['AMOUNT']) + intval($enrolment_fee['AMOUNT']);
	        
	        $challans = [];
	        $PAID_AMOUNT = 0;
	        $REFUND_AMOUNT = 0;
	        foreach ($ledger as $k=>$challan) {
	            $PAID_AMOUNT += intval($challan['PAID_AMOUNT']);
	            $REFUND_AMOUNT += intval($refund['PAID_AMOUNT']);
	            
	            $challans[] = $challan['PART_NAME'];
	            $challans[] = $challan['SEMESTER_NAME'];
	            $challans[] = $challan['CHALLAN_NO'];
	            $challans[] = $challan['PAID_AMOUNT'];
	            $challans[] = $challan['DATE'];
	        }
	        $abc = array('PAID' => $PAID_AMOUNT, 'REFUND' => $REFUND_AMOUNT, 'TOTAL' => $PAID_AMOUNT+$REFUND_AMOUNT, 'FEE' => $fees['AMOUNT'], 'ENR_FEE' => $enrolment_fee['AMOUNT'], 'TOTAL_FEE' => $total_fee);
	        $data = array_merge($student,$abc,$challans);
	        $record[] = $enrolment_fee;
	        
	        fputcsv($fp, $data, $delimiter);
	    }
	   // prePrint($record);
	    fclose($fp);
	    exit;
	}
	
}
