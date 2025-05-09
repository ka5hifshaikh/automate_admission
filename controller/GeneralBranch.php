<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';
class GeneralBranch extends AdminLogin
{
	public function __construct()
	{
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
		$self = $_SERVER['PHP_SELF'];
		$self = explode('index.php/', $self);
		$this->script_name = $self[1];
		$this->verify_login();
	}

	public function certificates()
	{

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id, $role_id);
		$this->verify_path($this->script_name, $side_bar_data);

		$sessions = $this->Admission_session_model->getSessionData();

		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['sessions'] = $sessions;
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;

		$this->load->view('include/header', $data);
		$this->load->view('include/side_bar');
		$this->load->view('include/nav', $data);
		$this->load->view('general_branch/certificate_downloading_panel', $data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}

	public function get_profile(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$search_value = isValidData($request->search_value);
		$error = "";
		if (empty($search_value))
			$error .= "Roll No. is Required";

		if (empty($error)) {

			$application = $this->EnrolmentCard_model->get_application($search_value);
		
			if (empty($application)) {
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode('ROLL NO. NOT FOUND'));
			}else{
				$application_id = $application['APPLICATION_ID'];
				$qualification = $this->EnrolmentCard_model->get_qualification($application_id);
				$enrollments_log = $this->EnrolmentCard_model->get_certificate_log($application_id, 'ENROLLMENT');
				$eligibility_log = $this->EnrolmentCard_model->get_certificate_log($application_id, 'ELIGIBILITY');
				$challan_logs = $this->EnrolmentCard_model->get_challan_log ($application_id);

				$enrollments_log_temp = array();
				foreach ($enrollments_log as $enrollment){
					$param = array('ROLL_NO'=>$enrollment['ROLL_NO'],'BY'=>'ADMIN');
					$param = json_encode($param);
					$enrollment['ENROLMENT_CARD_ID']=str_pad($enrollment['ENROLMENT_CARD_ID'], 8, '0', STR_PAD_LEFT);
					$enrollment['URL']="<a href=".base_url()."enrollment_card_pdf/".base64url_encode($param)." target='_blank'>Download/Print</a>";
					$enrollment['STATUS_DECODE']=certificate_card_status($enrollment['ACTIVE']);
					$enrollment['ISSUE_DATE']=getDateCustomeView($enrollment['ISSUE_DATE'],'d-m-Y');
					if ($enrollment['IS_REISSUED'] == "N") $IS_REISSUED_DECODE='No';
					elseif ($enrollment['IS_REISSUED'] == "Y") $IS_REISSUED_DECODE='Yes';
					else $IS_REISSUED_DECODE=$enrollment['IS_REISSUED'];
					$enrollment['IS_REISSUED_DECODE']=$IS_REISSUED_DECODE;
					$enrollments_log_temp[]=$enrollment;
				}
				$enrollments_log=$enrollments_log_temp;
				unset($enrollments_log_temp);

				/*
				 * below code is for eligibility certificate
				 * */

				$eligibility_log_temp = array();
				foreach ($eligibility_log as $eligibility){
					$param = array('ROLL_NO'=>$eligibility['ROLL_NO'],'BY'=>'ADMIN');
					$param = json_encode($param);
					$eligibility['ELIGIBILITY_CERTIFICATE_ID']=str_pad($eligibility['ELIGIBILITY_CERTIFICATE_ID'], 8, '0', STR_PAD_LEFT);
					$eligibility['URL']="<a href=".base_url()."eligibility_certificate_pdf/".base64url_encode($param)." target='_blank'>Download/Print</a>";
					$eligibility['STATUS_DECODE']=certificate_card_status($eligibility['ACTIVE']);
					$eligibility['ISSUE_DATE']=getDateCustomeView($eligibility['ISSUE_DATE'],'d-m-Y');
					if ($eligibility['IS_REISSUED'] == "N") $IS_REISSUED_DECODE='No';
					elseif ($eligibility['IS_REISSUED'] == "Y") $IS_REISSUED_DECODE='Yes';
					else $IS_REISSUED_DECODE=$eligibility['IS_REISSUED'];
					$eligibility['IS_REISSUED_DECODE']=$IS_REISSUED_DECODE;
					$eligibility_log_temp[]=$eligibility;
				}
				$eligibility_log=$eligibility_log_temp;
				unset($eligibility_log_temp);
			}
			$challan_logs_temp = array();

			foreach ($challan_logs as $challan_log){
				$challan_log['CHALLAN_DATE'] = getDateCustomeView($challan_log['CHALLAN_DATE'],'d-m-Y');
				$challan_log['DUE_DATE'] = getDateCustomeView($challan_log['DUE_DATE'],'d-m-Y');
				$challan_log['DECODED'] = base64url_encode(json_encode($challan_log));
				$challan_logs_temp[]=$challan_log;
			}

			$challan_logs=$challan_logs_temp;

			$array['PROFILE'] = $application;
			$array['QUALIFICATION'] = $qualification;
			$array['ENROLLMENT_LOG'] = $enrollments_log;
			$array['ELIGIBILITY_LOG'] = $eligibility_log;
			$array['CHALLAN_LOG'] = $challan_logs;

			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($array));

		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output($error);
		}
	}// function

	public function get_certificate(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$profile 			= ($request->profile);
		$certificate_type 	= isValidData($request->certificate_type);
		$error = "";

		if (empty($profile->ROLL_NO))
			$error .= "Roll No. is Required.";
		if (empty($profile->SELECTION_LIST_ID))
			$error .= "Selection List ID is Required.";
		if (empty($profile->APPLICATION_ID))
			$error .= "Application ID is Required.";
		if (empty($certificate_type))
			$error .= "Select Certificate.";

		if (empty($error)) {

			$selection_list_id = $profile->SELECTION_LIST_ID;
			$application_id = $profile->APPLICATION_ID;
			$cert = null;
			if ($certificate_type == "ENROLLMENT_CARD"){
				$cert = $this->EnrolmentCard_model->get_enrolment_card($application_id,$selection_list_id);
			}elseif ($certificate_type == "ELIGIBILITY_CERTIFICATE"){
				$cert = $this->EnrolmentCard_model->get_eligibility_certificate($application_id,$selection_list_id);
			}

			if (!empty($cert)){
				$cert['ISSUE_DATE']=getDateCustomeView($cert['ISSUE_DATE'],'d-m-Y');
				http_response_code(200);
				$this->output->set_content_type('application/json')->set_output(json_encode($cert));
			}else{
				http_response_code(206);
				$this->output->set_content_type('application/json')->set_output(json_encode('Certificate not found.'));
			}
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output($error);
		}
	}// function

	public function save_certificate(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$profile 			= ($request->profile);
		$certificate_type=null;
		if (isset($request->certificate_type)) $certificate_type 	= isValidData($request->certificate_type);
		$challan_selection=null;
		if (isset($request->challan_selection)) $challan_selection 	= isValidData($request->challan_selection);
		$challan_no=null;
		if (isset($request->challan_no)) $challan_no = isValidData($request->challan_no);
		$remarks=null;
		if (isset($request->remarks)) $remarks = isValidData($request->remarks);
		$challan_date=null;
		if (isset($request->challan_date)) $challan_date = isValidData($request->challan_date);
		$status=null;
		if (isset($request->status)) $status = isValidData($request->status);

		$error = "";

		if (empty($profile->ROLL_NO))
			$error .= " Roll No. is Required.";
		if (empty($profile->SELECTION_LIST_ID))
			$error .= " Selection List ID is Required.";
		if (empty($profile->APPLICATION_ID))
			$error .= " Application ID is Required.";
		if (empty($certificate_type))
			$error .= " Select Certificate.";
		if (empty($challan_selection))
			$error .= " Certificate/ Card Challan";
		if (!(is_numeric($status) && $status>=0))
			$error .= " Status is required";
		if ($challan_selection == "withChallan" && 	empty($challan_no) && $status !=2)
			$error .= " Challan No. is required";

		if (empty($error)) {

			$selection_list_id = $profile->SELECTION_LIST_ID;
			$roll_no = $profile->ROLL_NO;
			$application_id = $profile->APPLICATION_ID;
			$user = $this->session->userdata($this->SessionName);
			$user_role = $this->session->userdata($this->user_role);
			$issuer_id = $user['USER_ID'];
			$role_id = $user_role['ROLE_ID'];

			$cert = null;
			$is_reissued='N';
			if ($challan_selection == "withChallan"){
				$is_reissued='Y';
			}

			$array['APPLICATION_ID']=$application_id;
			$array['SELECTION_LIST_ID']=$selection_list_id;
			$array['REMARKS']=$remarks;
			$array['CHALLAN_NO']=$challan_no;
			$array['CHALLAN_DATE']=$challan_date;
			$array['IS_REISSUED']=$is_reissued;
			$array['ISSUER_ID']=$issuer_id;
			$array['ISSUED_BY']='ADMIN';
			$array['ACTIVE']=$status;
			$array['ISSUE_DATE']=date('Y-m-d h:i:s');
			if ($is_reissued == "Y")
				$array['REISSUE_DATE']=date('Y-m-d h:i:s');
			else
				$array['REISSUE_DATE']=null;

			if ($certificate_type == "ENROLLMENT_CARD"){
				$cert = $this->EnrolmentCard_model->get_enrolment_card($application_id,$selection_list_id);
				if (empty($cert)){
					$this->legacy_db = $this->load->database("admission_db",true);
					$this->legacy_db->db_debug = false;
					if ($this->legacy_db->insert('enrolment_card', $array)){
						$this->log_model->create_log(0,$this->legacy_db->insert_id(),null,$array,$roll_no,'enrolment_card',11,0);
						http_response_code(200);
						$this->output->set_content_type('application/json')->set_output(json_encode('Successfully Generated Enrollment Card.'));
					}else{
						http_response_code(206);
						$this->output->set_content_type('application/json')->set_output(json_encode('Enrollment Card Generation Failed.'));
					}
				}else{
					unset($array['APPLICATION_ID']);
					unset($array['SELECTION_LIST_ID']);
					unset($array['ISSUE_DATE']);
					$where = "ENROLMENT_CARD_ID =".$cert['ENROLMENT_CARD_ID'];
					if ($this->Administration->update($where,$array,$cert,'enrolment_card')){
//						$this->log_model->create_log($cert['ENROLMENT_CARD_ID'],$cert['ENROLMENT_CARD_ID'],$cert,$array,$roll_no,'enrolment_card',12,0);
						http_response_code(200);
						$this->output->set_content_type('application/json')->set_output(json_encode('Successfully updated Enrollment Card.'));
					}else{
						http_response_code(206);
						$this->output->set_content_type('application/json')->set_output(json_encode('Enrollment Card update Failed.'));
					}
				}
			}elseif ($certificate_type == "ELIGIBILITY_CERTIFICATE"){
				$cert = $this->EnrolmentCard_model->get_eligibility_certificate($application_id,$selection_list_id);
				if (empty($cert)){
					$this->legacy_db = $this->load->database("admission_db",true);
					$this->legacy_db->db_debug = false;
					if ($this->legacy_db->insert('eligibility_certificate', $array)){
						$this->log_model->create_log(0,$this->legacy_db->insert_id(),null,$array,$roll_no,'eligibility_certificate',11,0);
						http_response_code(200);
						$this->output->set_content_type('application/json')->set_output(json_encode('Successfully Generated Eligibility Card.'));
					}else{
						http_response_code(206);
						$this->output->set_content_type('application/json')->set_output(json_encode('Eligibility Certificate Generation Failed.'));
					}
				}else{
					unset($array['APPLICATION_ID']);
					unset($array['SELECTION_LIST_ID']);
					unset($array['ISSUE_DATE']);
					$where = "ELIGIBILITY_CERTIFICATE_ID  =".$cert['ELIGIBILITY_CERTIFICATE_ID'];
					if ($this->Administration->update($where,$array,$cert,'eligibility_certificate')){
//						$this->log_model->create_log(0,$cert['ELIGIBILITY_CERTIFICATE_ID'],$cert,$array,$roll_no,'eligibility_certificate',12,0);
						http_response_code(200);
						$this->output->set_content_type('application/json')->set_output(json_encode('Successfully updated Eligibility Certificate.'));
					}else{
						http_response_code(206);
						$this->output->set_content_type('application/json')->set_output(json_encode('Eligibility Certificate update Failed.'));
					}
				}
			}
		}else{
			http_response_code(206);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
	}// function

	public function save_single_challan_handler(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata,true);
		$fee_info = $request['challanInfo'];

		$error = "";

		if (empty($fee_info['CHALLAN_TYPE_ID'])) $error.="Challan Type is Required";
		elseif (empty($fee_info['CHALLAN_AMOUNT'])) $error.="Challan Amount is Required";
		elseif (empty($fee_info['PROFILE'])) $error.="Profile is Required";

		$profile = $fee_info['PROFILE'];

		if (empty($error)){
			$user = $this->session->userdata($this->SessionName);
			$user_role = $this->session->userdata($this->user_role);
			$issuer_id = $user['USER_ID'];
			$role_id = $user_role['ROLE_ID'];
			$section_account_id=53;

			if ($fee_info['CHALLAN_TYPE_ID'] == "53-001") $description="ENROLLMENT FEE";
			elseif ($fee_info['CHALLAN_TYPE_ID'] == "53-002") $description="ELIGIBILITY FEE";
			else $description="";

			$_param = array (
				'SECTION_ACCOUNT_ID'=>$section_account_id,
				'ROLL_NO'=>$profile['ROLL_NO'],
				'BATCH_ID'=>0,
				'DESCRIPTION'=>$description,
				'CNIC_NO'=>$profile['CNIC_NO'],
				'NAME'=>$profile['FIRST_NAME'],
				'FNAME'=>$profile['FNAME'],
				'SURNAME'=>$profile['LAST_NAME'],
				'MOBILE_NO'=>$profile['MOBILE_NO'],
				'EMAIL'=>$profile['EMAIL'],
				'PROGRAM'=>$profile['PROGRAM_TITLE'],
				'DEPT_NAME'=>$profile['DEPT_NAME'],
				'CAMPUS_NAME'=>$profile['CAMPUS_NAME'],
				'PROG_TYPE'=>$profile['PROGRAM_TYPE_TITLE'],
				'STUDENT_TYPE'=>$profile['SHIFT_NAME'],
				'PROG_CODE'=>0,
				'TYPE_CODE'=>isValidData($fee_info['CHALLAN_TYPE_ID']),
			);

			$this->legacy_db = $this->load->database('admission_db',true);
			if (isset($fee_info['CHALLAN_NO']) && $fee_info['CHALLAN_NO']>0 ){

			}else{
				$challan_date = date('Y-m-d');
				$due_date = date('Y-m-d',strtotime('+7 days'));

				$challan = array (
					'SECTION_ACCOUNT_ID'=>$section_account_id,
					'TYPE_CODE'=>isValidData($fee_info['CHALLAN_TYPE_ID']),
					'APPLICATION_ID'=>isValidData($fee_info['PROFILE']['APPLICATION_ID']),
					'SELECTION_LIST_ID'=>isValidData($fee_info['PROFILE']['SELECTION_LIST_ID']),
					'CHALLAN_AMOUNT'=>isValidData($fee_info['CHALLAN_AMOUNT']),
					'CHALLAN_DATE'=>$challan_date,
					'DUE_DATE'=>$due_date,
					'ISSUER_ID'=>isValidData($issuer_id)
				);
				if ($this->legacy_db->insert('general_branch_challan',$challan)){
					$last_id = $this->legacy_db->insert_id();
					$last_id_with_leading_zeros = str_pad($last_id, 7, '0', STR_PAD_LEFT);
					$challan_no = $section_account_id.''.$last_id_with_leading_zeros;

					$_param['AMOUNT']=isValidData($fee_info['CHALLAN_AMOUNT']);
					$_param['CHALLAN_DATE']=$challan_date;
					$_param['DUE_DATE']=$due_date;
					$_param['CHALLAN_NO']=$challan_no;
					$_param['REF_NO']=$last_id;

					$rest_response = postCURL(ONLINE_PAYMENT_TRANSFER_URL, $_param);
					http_response_code(200);
					echo ("Successfully uploaded...");
				}else{
					http_response_code(206);
					echo ("Transaction failed...");
				}
			}


		}else{
			http_response_code(206);
			echo $error;
			die();
		}
	}//method

	public function challan_print($challan){
		if (empty($challan)){
			exit('invalid url');
		}
		$challan = json_decode(base64url_decode($challan),true);
		$arr['challan']=$challan;
		$this->load->view('general_branch/challan_pdf',$arr);
	}

}//class
