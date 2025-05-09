<?php
/**
 * Created by PhpStorm.
 * User: Yasir Mehboob
 * Date: 16/01/2021
 * Time: 05:00 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
class CandidateSelection extends CI_Controller
{
	private $SelfController = 'CandidateSelection';
	private $profile = 'candidate/profile';
	private $LoginController = 'login';
	private $SessionName = 'USER_LOGIN_FOR_ADMISSION';
	private $user;
	private $APPLICATION_ID = 0;

	public function __construct()
	{
		parent::__construct();

		if ($this->session->has_userdata($this->SessionName) && $this->session->has_userdata('APPLICATION_ID')) {
			$this->APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
			$this->user = $this->session->userdata($this->SessionName);
		} else {
			redirect(base_url() . $this->LoginController);
			exit();
		}
		$this->load->model('Administration');
		$this->load->model('log_model');
		$this->load->model("Admission_session_model");
		$this->load->model("Application_model");
		$this->load->model("User_model");
		$this->load->model("Prerequisite_model");
		$this->load->model('User_model');
		$this->load->model('Api_location_model');
		$this->load->model('Configuration_model');
		$this->load->model('Api_qualification_model');
		$this->load->model('Selection_list_report_model');
		$this->load->model("TestResult_model");
		$this->load->model("FeeChallan_model");
	}

	public function index(){

		$user = $this->session->userdata($this->SessionName);
		$academic_session = $this->Admission_session_model->getSessionData();
		$program_types 	= $this->Administration->programTypes ();
		 $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
		   $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
           
//		if($user){
			$data['user'] = $user;
			$data['profile_url'] = $this->profile;
			$data['academic_sessions'] = $academic_session;
			$data['program_types'] = $program_types;
			$data['PROGRAM_TYPE_ID'] = $application['PROGRAM_TYPE_ID'];

			$this->load->view('include/header',$data);
			$this->load->view('include/preloder');
			$this->load->view('include/side_bar',$data);
			$this->load->view('include/nav',$data);
			$this->load->view('candidate_selection_dashboard',$data);
			$this->load->view('include/footer_area',$data);
			$this->load->view('include/footer',$data);
//		}
	}

	public function get_candidate_selection_record(){

		$this->form_validation->set_rules('program_type','program type is required','required|trim|integer');
		$this->form_validation->set_rules('session','session type is required','required|trim|integer');
		if($this->form_validation->run())
		{
			$program_type_id 	= isValidData( $this->input->post('program_type'));
			$session_id 		= isValidData( $this->input->post('session'));
			$records = $this->Selection_list_report_model->get_candidate_selection_list(0,$this->APPLICATION_ID,$session_id,$program_type_id,0,0);

			$new_array = array();
			$paid_fee_record = $this->FeeChallan_model->get_candidate_paid_history ($this->APPLICATION_ID,0);
// 			prePrint($records);
// 			prePrint($paid_fee_record);
// 			exit();
			foreach ($records as $record) {

				$APPLICATION_ID = $record['APPLICATION_ID'];
				$SELECTION_LIST_ID = $record['SELECTION_LIST_ID'];
				$challan_no=0;
				$challan_type_id=1;
				$fee_record = $this->FeeChallan_model->get_candidate_admission_challan ($APPLICATION_ID,$SELECTION_LIST_ID,$challan_no,$challan_type_id);
			 //   prePrint($fee_record);
			 //   exit();
			
		    	$array = array();
				$array['PROFILE']=$record;
				$array['FEE_CHALLAN']=$fee_record;
				
				array_push($new_array,$array);
			}
			
            $data['PAID_FEE_CHALLAN'] = $paid_fee_record;
			$data['selection_record'] = $new_array;
			$this->load->view('candidate_selection_display',$data);
		}else
		{
			echo "<span class='text-danger text-center'> Please select required fields</span>";
		}
	}

	public function FeeChallanPrint($challan){

		$this->load->view('letter');

	}
	public function RetainChallanPrint($challan){
		if (empty($challan)) exit("Require valid parameter....");

		$challan = urldecode(base64_decode(base64url_decode($challan)));
		$challan = json_decode($challan,true);
		if (empty($challan)) exit('Invalid input');
        // prePrint($challan);
        // exit();
        $challan['FEE_CHALLAN']['CHALLAN_NO'][1]='2';
		$data['challan']=$challan;
		$this->load->view('admission_retain_challan_pdf',$data);

	}

}
