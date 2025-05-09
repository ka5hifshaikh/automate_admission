<?php
/**
 * Created by PhpStorm.
 * User: Yasir Mehboob
 * Date: 01/12/2021
 * Time: 10:48 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';

class Selection_list_report extends AdminLogin{
	public function __construct(){
		parent::__construct();

		$this->load->model('Administration');
		$this->load->model('log_model');
		$this->load->model('Api_qualification_model');
		$this->load->model('Api_location_model');
		$this->load->model('User_model');
		$this->load->model('Application_model');
		$this->load->model('Admission_session_model');
		$this->load->model('TestResult_model');
		$this->load->model('MeritList_model');
		$this->load->model('Prerequisite_model');
		$this->load->model('Selection_list_report_model');

		$self = $_SERVER['PHP_SELF'];
		$self = explode('index.php/', $self);
		$this->script_name = $self[1];
		$this->verify_login();
	}
    public function getMissngData(){
        set_time_limit(1500);
        ini_set('memory_limit', '-1');
        $data = $this->Selection_list_report_model->getMissngData();
        $NOT_FOUND = $data['NOT_FOUND'];
        $CHANGE_ROLL_NO = $data['CHANGE_ROLL_NO'];
        $MULTIPLE_RECORD = $data['MULTIPLE_RECORD'];
        prePrint(count($data['NOT_FOUND']));
    }
    public function selection_report(){
        
        $user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

		$academic_session = $this->Admission_session_model->getSessionData();
		$program_types 	= $this->Administration->programTypes();
		$shift = $this->Administration->shifts();
// 		$application_status_list = $this->FormVerificationModel->get_application_status_list ();
        // $district_list = $this->Api_location_model->getDistrictByProvinceId(6);
        
// 		$data['district_list'] = $district_list;
		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;
		$data['academic_sessions'] = $academic_session;
		$data['program_types'] = $program_types;
// 		$data['application_status_list'] = $application_status_list;
        $data['shifts'] = $shift;

		$this->load->view('include/header',$data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar',$data);
		$this->load->view('include/nav',$data);
		$this->load->view('admin/selection_list_report_window',$data);
//		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
		
    }
    
    public function selection_report_for_verification(){
        
        $user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

		$academic_session = $this->Admission_session_model->getSessionData();
		$program_types 	= $this->Administration->programTypes();
		$shift = $this->Administration->shifts();
// 		$application_status_list = $this->FormVerificationModel->get_application_status_list ();
        // $district_list = $this->Api_location_model->getDistrictByProvinceId(6);
        
// 		$data['district_list'] = $district_list;
		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;
		$data['academic_sessions'] = $academic_session;
		$data['program_types'] = $program_types;
// 		$data['application_status_list'] = $application_status_list;
        $data['shifts'] = $shift;

		$this->load->view('include/header',$data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar',$data);
		$this->load->view('include/nav',$data);
		$this->load->view('admin/selection_list_report_verification',$data);
//		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
		
    }
	
	public function display_selection_list_pdf($campus_id=0,$admission_session_id=0,$message_no=0,$first_date='',$second_date=''){

    $this->form_validation->set_rules('program_type','Program Type is required','required|trim|integer');
    $this->form_validation->set_rules('session','Session is required','required|trim|integer');
    $this->form_validation->set_rules('campus','Campus is required','required|trim|integer');
    $this->form_validation->set_rules('shift_id','Shift is required','required|trim|integer');
    $this->form_validation->set_rules('list_no','List No is required','required|trim|integer');
    $this->form_validation->set_rules('message','Message is required','required|trim');
    $this->form_validation->set_rules('test_id','Test is required','required|trim|integer');
     $this->form_validation->set_rules('is_provisional','Is Provisional is required','required|trim');
    
		if($this->form_validation->run()){
		    
			$prog_type_id = isValidData($this->input->post('program_type'));
			$is_provisional=isValidData($this->input->post('is_provisional'));
			$shift_id 	  = isValidData($this->input->post('shift_id'));
			$admission_session_id= isValidData($this->input->post('campus'));
			$list_no 	  = isValidData($this->input->post('list_no'));
			$session 	  = isValidData($this->input->post('session'));
			$message 	  = isValidData($this->input->post('message'));
			$test_id 	  = isValidData($this->input->post('test_id'));
			$PROG_LIST_ID =$this->input->post('PROG_LIST_ID');
		  
			$message_no = 3;
		}else{
		    exit("Input parameters are not complete...");
		}
		$pdf = new FPDF('L','mm','A3');
//		$pdf->SetM

		$admission_session_ids = array ($admission_session_id);

        $admission_session 	= $this->Selection_list_report_model->getDetailOnAdmissionSessionById($admission_session_id);
        // prePrint($admission_session);
        $campus_id = $admission_session['CAMPUS_ID'];
        $program_list_ids=$this->Administration->getMappedPrograms ($shift_id,$prog_type_id,$campus_id);
        // prePrint($program_list_ids);
        // exit;
		foreach ($admission_session_ids as $admission_session_id):

		$academic_year 		= $admission_session['YEAR'];
		$batch_remarks 		= $admission_session['BATCH_REMARKS'];
		$program_title_cat 	= $admission_session['PROGRAM_TITLE'];
// 		if ($admission_session['NAME'] == 'UNIVERSITY OF SINDH, JAMSHORO') {
// 		$campus_name 		= 'DIRECTORATE OF ADMISSIONS, '.$admission_session['NAME'];
// 		} else {
// 		$campus_name 		= $admission_session['NAME'];
// 		}
    	$campus_name 		= $admission_session['NAME'];

			$sno=0;
			$program_name = "";
			
				// foreach ($program_list_ids as $program_list_id):
				foreach ($PROG_LIST_ID as $PROG_LIST_ID_NEW):
				// $program_list_id = $program_list_id['PROG_ID'];
				$program_list_id = $PROG_LIST_ID_NEW;
				
		 	$program_list_obj = $this->Administration->getProgram_prog_list_id($program_list_id);
			$program_title = $program_list_obj['PROGRAM_TITLE'];
                if($prog_type_id==1){
                    
                }else if($prog_type_id==2){
                    
                }
				$pdf->AddPage();
				$prev_page = $pdf->PageNo();
				$pdf->SetFont('Times','B',16);
				$page_size = $pdf->GetPageWidth();
				$pdf->Cell($page_size, 7, merit_list_decode($list_no)." MERIT LIST OF CANDIDATES PROVISIONALLY SELECTED FOR ADMISSION TO", 0, 0,'C');
				$pdf->ln();
				$pdf->Cell($page_size, 7,$program_title." FIRST YEAR -".shift_decode ($shift_id), 0, 0,'C');
				$pdf->ln();
				$pdf->Cell($page_size, 7, "FOR ACADEMIC YEAR - $academic_year @ $campus_name", 0, 0,'C');
				$pdf->ln();
				$pdf->SetFont('Times','B',9);
				$pdf->Cell(15, 7, '', 0, 'C');

				$pdf->Cell(15, 7, '', 0, 'C');
				$pdf->Cell(20, 7, '', 0, 'C');
				$pdf->Cell(75, 7, '', 0, 'C');
				$pdf->Cell(75, 7, "", 0, 'C');
				$pdf->Cell(55, 7, "", 0, 'C');
//				$pdf->Cell(12, 7, "", 0, 'C');
//				$pdf->Cell(30, 7, "", 0, 'C');
//				$pdf->Cell(15, 7, "", 0, 'C');
				$pdf->Cell(20, 7, "S.S.C", 0,0, 'C');
				$pdf->Cell(25, 7, "H.S.C", 0, 0,'C');
				if($prog_type_id==2){
                $pdf->Cell(25, 7, "GRD", 0, 0,'C');    
                }
				
				$pdf->Cell(11, 7, "DED", 0, 'C');
				$pdf->Cell(11, 7, "AFT", 0, 'C');
				$pdf->Cell(11, 7, "TST", 0, 'C');
				$pdf->Cell(11, 7, "S.S.C", 0, 'C');
				$pdf->Cell(11, 7, "H.S.C", 0, 'C');
				
				if($prog_type_id==2){
                $pdf->Cell(11, 7, "GRD", 0, 'C'); 
                }
				$pdf->Cell(11, 7, "TEST", 0, 'C');


				$pdf->ln();

				$pdf->Cell(10, 7, 'S.NO', 0,0, 'C');
				$pdf->Cell(15, 7, 'APP ID', 0,0, 'C');
				$pdf->Cell(75, 7, 'NAME', 0, 0, 'C');
				$pdf->Cell(80, 7, "FATHER'S NAME", 0,0, 'C');
				$pdf->Cell(40, 7, "DISTRICT", 0,0, 'C');
//				$pdf->Cell(10, 7, "U/R", 0, 'C');
				$pdf->Cell(27, 7, "GROUP", 0,0, 'C');
				$pdf->Cell(10, 7, "CH#", 0, 0,'C');
				$pdf->Cell(10, 7, "Marks", 0, 0,'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
				$pdf->Cell(11, 7, "Marks", 0, 'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
					if($prog_type_id==2){
				    $pdf->Cell(11, 7, "Marks", 0, 'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
				
				}
				$pdf->Cell(11, 7, "MKS", 0, 'C');
				$pdf->Cell(12, 7, "DED", 0, 'C');
				$pdf->Cell(12, 7, "SCR", 0, 'C');
			
				if($prog_type_id==2){
				$pdf->Cell(11, 7, "20%", 0, 'C');
				$pdf->Cell(11, 7, "30%", 0, 'C');
                $pdf->Cell(11, 7, "50%", 0, 0,'C');    
                }else{
                $pdf->Cell(11, 7, "40%", 0, 'C');
				$pdf->Cell(11, 7, "60%", 0, 'C');
                }
				$pdf->Cell(10, 7, "0%", 0, 'C');
				$pdf->Cell(10, 7, "CPN", 0, 'C');
				$pdf->ln();

				$pdf->SetFont('Times','',9);
				$records = $this->Selection_list_report_model->selected_list_for_pdf($admission_session_id,0,0,$shift_id,$program_list_id,$list_no,0,$is_provisional);
			 //   prePrint($records);
						$cate = "";
						foreach ($records as $record):
						//$new_page = $pdf->PageNo();

							if ($pdf->GetY() >=240):
								$page_size = $pdf->GetPageWidth();
								$pdf->SetFont('Times','B',8);
								$pdf->ln(3);
//								$pdf->Cell($page_size-30, 7,"COMPUTER PROGRAMMER", 0, 0,'C');
//								$pdf->Cell(30, 7,"ASSISTANT DIRECTOR ADMISSIONS", 0, 1,'C');
								// $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO 14-01-2021. FIRST PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON 15-01-2021. THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND ON ANY STAGE.";
							
								    	if($message_no==2){
        								//$message = "LAST DATE FOR SUBMISSION OF ALL REQUIRED DOCUMENTS @ DIRECTORATE OF ADMISSIONS, UNIVERSITY OF SINDH JAMSHORO & PAYMENT OF ADMISSION FEE CHALLAN IS ".urldecode($first_date).".";    
        								$message = "LAST DATE FOR SUBMISSION OF ALL REQUIRED DOCUMENTS @ $campus_name & PAYMENT OF ADMISSION FEE CHALLAN IS ".urldecode($first_date).".";    
								    	
								    	}else if($message_no==1){
        								   $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO ".urldecode($first_date).". PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON ".urldecode($second_date).".";
        								}elseif($message_no==3){
        								    $message = $message;
        								}
							
							
								
								
								$pdf->MultiCell($page_size-15, 5,$message,0,'C');
								$pdf->MultiCell($page_size-15, 5,'THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND AT ANY STAGE.',0,'C');
								
								
								$pdf->SetFont('Times','',8);
						
								$pdf->Cell($page_size, 5,"NOTE: ".strtoupper(UNIVERSITY_NAME)." RESERVES THE RIGHT TO RECITFY ERROR OMISSION DETECTED LATER ON AND ALSO RESERVES THE RIGHT TO CANCEL ANY PROVISIONAL ADMISSION AT ANY TIME WITHOUT ISSUING NOTICE CONCERNED TABLE CLERK IS BOUND TO VERIFY ELIGIBILITY ETC.",0,1,'L');

		
								$pdf->Cell($page_size-150, 5,"THIS IS A COMPUTER-GENERATED REPORT AND IT DOES NOT REQUIRE ANY SIGNATURE. THIS REPORT SHALL NOT BE INVALIDATED SOLELY ON THE GROUND THAT IT IS NOT SIGNED.", 0, 0,'l');
								
								$pdf->Cell(30, 5,"Page No: ".$pdf->PageNo(), 0, 0,'l');
								
								$pdf->Cell(100, 5,"Powered by: Information Technology Services Centre (ITSC)", 0, 0,'l');
								
								// $pdf->ln();
								
								
						
								$pdf->AddPage();
								$prev_page = $pdf->PageNo();
								$pdf->SetFont('Times','B',16);

								$pdf->Cell($page_size, 7, merit_list_decode($list_no)." MERIT LIST OF CANDIDATES PROVISIONALLY SELECTED FOR ADMISSION TO", 0, 0,'C');
                				$pdf->ln();
                				$pdf->Cell($page_size, 7,$program_title." FIRST YEAR -".shift_decode ($shift_id), 0, 0,'C');
                				$pdf->ln();
                				$pdf->Cell($page_size, 7, "FOR ACADEMIC YEAR - $academic_year @ $campus_name", 0, 0,'C');
								$pdf->ln();

								$pdf->SetFont('Times','B',9);
								$pdf->Cell(15, 7, '', 0, 'C');

				$pdf->Cell(15, 7, '', 0, 'C');
				$pdf->Cell(20, 7, '', 0, 'C');
				$pdf->Cell(75, 7, '', 0, 'C');
				$pdf->Cell(75, 7, "", 0, 'C');
				$pdf->Cell(55, 7, "", 0, 'C');
//				$pdf->Cell(12, 7, "", 0, 'C');
//				$pdf->Cell(30, 7, "", 0, 'C');
//				$pdf->Cell(15, 7, "", 0, 'C');
				$pdf->Cell(20, 7, "S.S.C", 0,0, 'C');
				$pdf->Cell(25, 7, "H.S.C", 0, 0,'C');
				if($prog_type_id==2){
                $pdf->Cell(25, 7, "GRD", 0, 0,'C');    
                }
				
				$pdf->Cell(11, 7, "DED", 0, 'C');
				$pdf->Cell(11, 7, "AFT", 0, 'C');
				$pdf->Cell(11, 7, "TST", 0, 'C');
				$pdf->Cell(11, 7, "S.S.C", 0, 'C');
				$pdf->Cell(11, 7, "H.S.C", 0, 'C');
				
				if($prog_type_id==2){
                $pdf->Cell(11, 7, "GRD", 0, 'C'); 
                }
				$pdf->Cell(11, 7, "TEST", 0, 'C');


				$pdf->ln();

				$pdf->Cell(10, 7, 'S.NO', 0,0, 'C');
				$pdf->Cell(15, 7, 'APP ID', 0,0, 'C');
				$pdf->Cell(75, 7, 'NAME', 0, 0, 'C');
				$pdf->Cell(80, 7, "FATHER'S NAME", 0,0, 'C');
				$pdf->Cell(40, 7, "DISTRICT", 0,0, 'C');
//				$pdf->Cell(10, 7, "U/R", 0, 'C');
				$pdf->Cell(27, 7, "GROUP", 0,0, 'C');
				$pdf->Cell(10, 7, "CH#", 0, 0,'C');
				$pdf->Cell(10, 7, "Marks", 0, 0,'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
				$pdf->Cell(11, 7, "Marks", 0, 'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
					if($prog_type_id==2){
				    $pdf->Cell(11, 7, "Marks", 0, 'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
				
				}
				$pdf->Cell(11, 7, "MKS", 0, 'C');
				$pdf->Cell(12, 7, "DED", 0, 'C');
				$pdf->Cell(12, 7, "SCR", 0, 'C');
			
				if($prog_type_id==2){
				$pdf->Cell(11, 7, "20%", 0, 'C');
				$pdf->Cell(11, 7, "30%", 0, 'C');
                $pdf->Cell(11, 7, "50%", 0, 0,'C');    
                }else{
                $pdf->Cell(11, 7, "40%", 0, 'C');
				$pdf->Cell(11, 7, "60%", 0, 'C');
                }
				$pdf->Cell(10, 7, "0%", 0, 'C');
				$pdf->Cell(10, 7, "CPN", 0, 'C');
								$pdf->ln();

							endif;
							$sno++;
							$application_id = $record['APPLICATION_ID'];
							$candidate_name = $record['FIRST_NAME'];
							$surname = $record['LAST_NAME'];
							$fname = $record['FNAME'];
							$category_name = $record['CATEGORY_NAME'];
							$district_name = $record['DISTRICT_NAME'];
							$area = $record['U_R'];
							$choice_no = $record['CHOICE_NO'];
							$cpn_merit_list = $record['CPN_MERIT_LIST'];
							$test_cpn = 0;
							$test_score = ($record['TEST_SCORE']?$record['TEST_SCORE']:'0');
							$detail_cpn = json_decode($record['DETAIL_CPN'],true);

							$ssc = findObjectinList($detail_cpn,"DEGREE_ID",SSC_DEGREE_ID);
							$ssc_total 		= $ssc['TOTAL_MARKS'];
							$ssc_obtained 	= $ssc['OBTAINED_MARKS'];
							$ssc_passing_year = $ssc['PASSING_YEAR'];
							$ssc_after_deduct = $ssc['AFTER_DEDUCT_MARKS'];
							$ssc_percentage 	= $ssc['PERCENTAGE'];
							$ssc_cpn_percentage = $ssc['CPN_PERCENTAGE'];
							
							
							$test_score_detail  = findObjectinList($detail_cpn,"DEGREE_TITLE","TEST_SCORE");

							$hsc = findObjectinList($detail_cpn,"DEGREE_ID",HSC_DEGREE_ID);
				 		
							$hsc_total 		= $hsc['TOTAL_MARKS'];
							$hsc_obtained 	= $hsc['OBTAINED_MARKS'];
							$hsc_passing_year = $hsc['PASSING_YEAR'];
							$hsc_after_deduct = $hsc['AFTER_DEDUCT_MARKS'];
							$hsc_percentage 	= $hsc['PERCENTAGE'];
							$hsc_cpn_percentage = $hsc['CPN_PERCENTAGE'];
							$hsc_group 			= $hsc['DISCIPLINE_NAME'];
							if(isset($hsc['DEDUCT_MARKS'])){
							    $hsc_deduct_marks 	= ($hsc['DEDUCT_MARKS'])?$hsc['DEDUCT_MARKS']:'0';
							}else $hsc_deduct_marks= '--';
							
						    if($prog_type_id==2){
						       $grd = findObjectinList($detail_cpn,"DEGREE_TITLE",'GRADUATION');
						       	$grd_total 		= $grd['TOTAL_MARKS'];
    							$grd_obtained 	= $grd['OBTAINED_MARKS'];
    							$grd_passing_year = $grd['PASSING_YEAR'];
    							$grd_after_deduct = $grd['AFTER_DEDUCT_MARKS'];
    							$grd_percentage 	= $grd['PERCENTAGE'];
    							$grd_cpn_percentage = $grd['CPN_PERCENTAGE'];
    							$grd_group 			= $grd['DISCIPLINE_NAME'];
    							if(isset($grd['DEDUCT_MARKS'])){
							    $grd_deduct_marks 	= ($grd['DEDUCT_MARKS'])?$grd['DEDUCT_MARKS']:'0';
							}else $hsc_deduct_marks= '--';
    							$grd_cpn_percentage = $this->TestResult_model->truncate_cpn($grd_cpn_percentage,2);
				 		
						    }	

							$hsc_cpn_percentage = $this->TestResult_model->truncate_cpn($hsc_cpn_percentage,2);
							$ssc_cpn_percentage = $this->TestResult_model->truncate_cpn($ssc_cpn_percentage,2);
							
							if($test_score_detail){
							    
							    $test_cpn = $test_score_detail['CPN_PERCENTAGE'];
							    
							$test_cpn = $this->TestResult_model->truncate_cpn($test_cpn,2);	
							    
							}
				// 			prePrint($test_cpn);
				// 			exit();
						
//		prePrint($hsc);
							if ($hsc_group == "PRE-MEDICAL") $hsc_group="PRE-MEDL";
							elseif ($hsc_group == "PRE-ENGINEERING") $hsc_group="PRE-ENGG";
							elseif ($hsc_group == "PRE-COMMERCE") $hsc_group="PRE-COMM";
							elseif ($hsc_group == "PRE-COMMERCE") $hsc_group="PRE-COMM";
								elseif ($hsc_group == "SCIENCE GENERAL") $hsc_group="SCI-GEN";

							if($cate!=$record['CATEGORY_NAME']){
								$pdf->SetFont('Times','B',12);
								$cate = $record['CATEGORY_NAME'];
								$pdf->Cell(190, 7, $cate,0,1, 'L');
								$sno = 1;
								$pdf->SetFont('Times','',9);
							}
							$pdf->SetFont('Times','',9);
							$pdf->Cell(10,7,$sno,1,0,'C');
							$pdf->Cell(15,7,$application_id,1);
							$pdf->Cell(75,7,$candidate_name,1);
							$pdf->Cell(75,7,$fname.', '.$surname,1);
							$pdf->Cell(48,7,$district_name.'  ('.$area.')',1);
//							$pdf->Cell(8,7,$area,1);
							$pdf->Cell(25,7,$hsc_group,1);
							$pdf->Cell(8, 7, $choice_no, 1,0, 'C');
							$pdf->Cell(11, 7, $ssc_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $ssc_passing_year, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_passing_year, 1, 0,'C');
							if($prog_type_id==2){
							 $pdf->Cell(11, 7, $grd_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $grd_passing_year, 1, 0,'C');
							$pdf->Cell(11, 7, $grd_deduct_marks, 1,0, 'C');
							    $pdf->Cell(11, 7, $grd_after_deduct, 1, 0,'C');
							}else{
							    $pdf->Cell(11, 7, $hsc_deduct_marks, 1,0, 'C');
							    $pdf->Cell(11, 7, $hsc_after_deduct, 1, 0,'C');
							}
						
							$pdf->Cell(11, 7, $test_score, 1,0, 'C');
							$pdf->Cell(11, 7, number_format($ssc_cpn_percentage,2), 1,0, 'C');
							$pdf->Cell(11, 7, number_format($hsc_cpn_percentage,2), 1,0, 'C');
								if($prog_type_id==2){
								    $pdf->Cell(11, 7, number_format($grd_cpn_percentage,2), 1,0, 'C');
								}
							$pdf->Cell(11, 7, number_format($test_cpn,2), 1, 0,'C');
							$pdf->SetFont('Times','B',9);
							$pdf->Cell(11, 7, number_format($cpn_merit_list,2), 1, 0,'C');
							$pdf->ln();

							$pdf->SetFont('Times','',9);
					endforeach;
					            $pdf->SetY(245);
					
							    $pdf->SetFont('Times','B',8);
								$pdf->ln(3);
//								$pdf->Cell($page_size-30, 7,"COMPUTER PROGRAMMER", 0, 0,'C');
//								$pdf->Cell(30, 7,"ASSISTANT DIRECTOR ADMISSIONS", 0, 1,'C');
								// $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO 14-01-2021. FIRST PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON 15-01-2021. THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND ON ANY STAGE.";
								
								if($message_no==2){
        								//$message = "LAST DATE FOR SUBMISSION OF ALL REQUIRED DOCUMENTS @ DIRECTORATE OF ADMISSIONS, UNIVERSITY OF SINDH JAMSHORO & PAYMENT OF ADMISSION FEE CHALLAN IS ".urldecode($first_date).".";
        								$message = "LAST DATE FOR SUBMISSION OF ALL REQUIRED DOCUMENTS @ $campus_name & PAYMENT OF ADMISSION FEE CHALLAN IS ".urldecode($first_date).".";    
        								}else if($message_no==1){
        								   $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO ".urldecode($first_date).". PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON ".urldecode($second_date).".";
        								}elseif($message_no==3){
        								    $message = $message;
        								}
							
								$pdf->MultiCell($page_size-15, 5,$message,0,'C');
								$pdf->MultiCell($page_size-15, 5,'THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND AT ANY STAGE.',0,'C');
								
								$pdf->SetFont('Times','',8);
						
								$pdf->Cell($page_size, 5,"NOTE: UNIVERSITY OF SINDH RESERVES THE RIGHT TO RECITFY ERROR OMISSION DETECTED LATER ON AND ALSO RESERVES THE RIGHT TO CANCEL ANY PROVISIONAL ADMISSION AT ANY TIME WITHOUT ISSUING NOTICE CONCERNED TABLE CLERK IS BOUND TO VERIFY ELIGIBILITY ETC.",0,1,'L');

		
								$pdf->Cell($page_size-150, 5,"THIS IS A COMPUTER-GENERATED REPORT AND IT DOES NOT REQUIRE ANY SIGNATURE. THIS REPORT SHALL NOT BE INVALIDATED SOLELY ON THE GROUND THAT IT IS NOT SIGNED.", 0, 0,'l');
								$pdf->Cell(30, 5,"Page No: ".$pdf->PageNo(), 0, 0,'l');
								$pdf->Cell(100, 5,"Powered by: Information Technology Services Centre (ITSC)", 0, 0,'l');
								
								// $pdf->ln();
			endforeach;
		endforeach;

		$pdf->Output("1.pdf",'I');

	}//method
	
	//ADDED BY VIKESH KUMAR FOR LLB(LAW)
	public function display_selection_list_law_pdf($campus_id=1,$admission_session_id=1,$message_no=0,$first_date='',$second_date='') {

		$pdf = new FPDF('L','mm','A3');

		$admission_session_ids = array ($admission_session_id);
		$shift_id = 1;
		$list_no = 1;
		$prog_type_id = 1; //bachelor
// 		$program_list_ids = array (9,258,259);
        // $campus_id = 1;
        $program_list_ids=$this->Administration->getMappedProgramsLaw ($shift_id,$prog_type_id,$campus_id);
// 		$program_list_ids = $this->Administration->getProgramByTypeID($prog_type_id);
		foreach ($admission_session_ids as $admission_session_id):

		$admission_session 	= $this->Selection_list_report_model->getDetailOnAdmissionSessionById($admission_session_id);

		$academic_year 		= $admission_session['YEAR'];
		$batch_remarks 		= $admission_session['BATCH_REMARKS'];
		$program_title_cat 	= $admission_session['PROGRAM_TITLE'];
		$campus_name 		= $admission_session['NAME'];

			$sno=0;
			$program_name = "";
				foreach ($program_list_ids as $program_list_id):
				$program_list_id = $program_list_id['PROG_ID'];
		 	$program_list_obj = $this->Administration->getProgram_prog_list_id($program_list_id);
			$program_title = $program_list_obj['PROGRAM_TITLE'];

				$pdf->AddPage();
				$prev_page = $pdf->PageNo();
				$pdf->SetFont('Times','B',16);
				$page_size = $pdf->GetPageWidth();
				$pdf->Cell($page_size, 7, merit_list_decode($list_no)." MERIT LIST OF CANDIDATES PROVISIONALLY SELECTED FOR ADMISSION TO", 0, 0,'C');
				$pdf->ln();
				$pdf->Cell($page_size, 7,$program_title." FIRST YEAR -".shift_decode ($shift_id), 0, 0,'C');
				$pdf->ln();
				$pdf->Cell($page_size, 7, "FOR ACADEMIC YEAR - $academic_year @ $campus_name", 0, 0,'C');
				$pdf->ln();
				$pdf->SetFont('Times','B',9);
				$pdf->Cell(15, 7, '', 0, 'C');

				$pdf->Cell(15, 7, '', 0, 'C');
				$pdf->Cell(20, 7, '', 0, 'C');
				$pdf->Cell(75, 7, '', 0, 'C');
				$pdf->Cell(75, 7, "", 0, 'C');
				$pdf->Cell(55, 7, "", 0, 'C');
//				$pdf->Cell(12, 7, "", 0, 'C');
//				$pdf->Cell(30, 7, "", 0, 'C');
//				$pdf->Cell(15, 7, "", 0, 'C');
				$pdf->Cell(20, 7, "S.S.C", 0,0, 'C');
				$pdf->Cell(25, 7, "H.S.C", 0, 0,'C');
				$pdf->Cell(11, 7, "DED", 0, 'C');
				$pdf->Cell(11, 7, "AFT", 0, 'C');
				$pdf->Cell(11, 7, "TST", 0, 'C');
				$pdf->Cell(11, 7, "S.S.C", 0, 'C');
				$pdf->Cell(11, 7, "H.S.C", 0, 'C');
				$pdf->Cell(11, 7, "TEST", 0, 'C');

				$pdf->ln();

				$pdf->Cell(10, 7, 'S.NO', 0,0, 'C');
				$pdf->Cell(15, 7, 'APP ID', 0,0, 'C');
				$pdf->Cell(75, 7, 'NAME', 0, 0, 'C');
				$pdf->Cell(80, 7, "FATHER'S NAME", 0,0, 'C');
				$pdf->Cell(40, 7, "DISTRICT", 0,0, 'C');
//				$pdf->Cell(10, 7, "U/R", 0, 'C');
				$pdf->Cell(27, 7, "GROUP", 0,0, 'C');
				$pdf->Cell(10, 7, "CH#", 0, 0,'C');
				$pdf->Cell(10, 7, "Marks", 0, 0,'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
				$pdf->Cell(11, 7, "Marks", 0, 'C');
				$pdf->Cell(11, 7, "Year", 0, 'C');
				$pdf->Cell(11, 7, "MKS", 0, 'C');
				$pdf->Cell(12, 7, "DED", 0, 'C');
				$pdf->Cell(12, 7, "SCR", 0, 'C');
				$pdf->Cell(11, 7, "10%", 0, 'C');
				$pdf->Cell(11, 7, "30%", 0, 'C');
				$pdf->Cell(10, 7, "60%", 0, 'C');
				$pdf->Cell(10, 7, "CPN", 0, 'C');
				$pdf->ln();

				$pdf->SetFont('Times','',9);
				$records = $this->Selection_list_report_model->selected_list_law($admission_session_id,0,0,$shift_id,$program_list_id,$list_no,0);
						$cate = "";
						foreach ($records as $record):
						//$new_page = $pdf->PageNo();

							if ($pdf->GetY() >=240):
								$page_size = $pdf->GetPageWidth();
								$pdf->SetFont('Times','B',8);
								$pdf->ln(3);
//								$pdf->Cell($page_size-30, 7,"COMPUTER PROGRAMMER", 0, 0,'C');
//								$pdf->Cell(30, 7,"ASSISTANT DIRECTOR ADMISSIONS", 0, 1,'C');
								// $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO 14-01-2021. FIRST PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON 15-01-2021. THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND ON ANY STAGE.";
							
								    	if($message_no==2){
        								$message = "LAST DATE FOR SUBMISSION OF ALL REQUIRED DOCUMENTS @ DIRECTORATE OF ADMISSIONS, ".UNIVERSITY_NAME." & PAYMENT OF ADMISSION FEE CHALLAN IS ".urldecode($first_date).".";
								    	
								    	}else if($message_no==1){
        								   $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO ".urldecode($first_date).". PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON ".urldecode($second_date).".";
        								}else{
        								    $message = $message_no;
        								}
							
							
								
								
								$pdf->MultiCell($page_size-15, 5,$message,0,'C');
								$pdf->MultiCell($page_size-15, 5,'THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND AT ANY STAGE.',0,'C');
								
								
								$pdf->SetFont('Times','',8);
						
								$pdf->Cell($page_size, 5,"NOTE: UNIVERSITY OF SINDH RESERVES THE RIGHT TO RECITFY ERROR OMISSION DETECTED LATER ON AND ALSO RESERVES THE RIGHT TO CANCEL ANY PROVISIONAL ADMISSION AT ANY TIME WITHOUT ISSUING NOTICE CONCERNED TABLE CLERK IS BOUND TO VERIFY ELIGIBILITY ETC.",0,1,'L');

		
								$pdf->Cell($page_size-150, 5,"THIS IS A COMPUTER-GENERATED REPORT AND IT DOES NOT REQUIRE ANY SIGNATURE. THIS REPORT SHALL NOT BE INVALIDATED SOLELY ON THE GROUND THAT IT IS NOT SIGNED.", 0, 0,'l');
								
								$pdf->Cell(30, 5,"Page No: ".$pdf->PageNo(), 0, 0,'l');
								
								$pdf->Cell(100, 5,"Powered by: Information Technology Services Centre (ITSC)", 0, 0,'l');
								
								// $pdf->ln();
								
								
						
								$pdf->AddPage();
								$prev_page = $pdf->PageNo();
								$pdf->SetFont('Times','B',16);

								$pdf->Cell($page_size, 7,"FIRST MERIT LIST OF CANDIDATES PROVISIONALLY SELECTED FOR ADMISSION TO", 0, 0,'C');
								$pdf->ln();
								$pdf->Cell($page_size, 7, $program_title." FIRST YEAR - MORNING ", 0, 0,'C');
								$pdf->ln();
								//$pdf->Cell($page_size, 7, "FOR ACADEMIC YEAR - 2021 @ ALLAMA I.I. KAZI CAMPUS, JAMSHORO", 0, 0,'C');
								
								$pdf->Cell($page_size, 7, "FOR ACADEMIC YEAR - $academic_year @ $campus_name", 0, 0,'C');
								
								$pdf->ln();

								$pdf->SetFont('Times','B',9);
								$pdf->Cell(15, 7, '', 0, 'C');

								$pdf->Cell(15, 7, '', 0, 'C');
								$pdf->Cell(20, 7, '', 0, 'C');
								$pdf->Cell(75, 7, '', 0, 'C');
								$pdf->Cell(75, 7, "", 0, 'C');
								$pdf->Cell(55, 7, "", 0, 'C');
//				$pdf->Cell(12, 7, "", 0, 'C');
//				$pdf->Cell(30, 7, "", 0, 'C');
//				$pdf->Cell(15, 7, "", 0, 'C');
								$pdf->Cell(20, 7, "S.S.C", 0,0, 'C');
								$pdf->Cell(25, 7, "H.S.C", 0, 0,'C');
								$pdf->Cell(11, 7, "DED", 0, 'C');
								$pdf->Cell(11, 7, "AFT", 0, 'C');
								$pdf->Cell(11, 7, "TST", 0, 'C');
								$pdf->Cell(11, 7, "S.S.C", 0, 'C');
								$pdf->Cell(11, 7, "H.S.C", 0, 'C');
								$pdf->Cell(11, 7, "TEST", 0, 'C');


								$pdf->ln();

								$pdf->Cell(10, 7, 'S.NO', 0,0, 'C');
								$pdf->Cell(15, 7, 'APP ID', 0,0, 'C');
								$pdf->Cell(75, 7, 'NAME', 0, 0, 'C');
								$pdf->Cell(80, 7, "FATHER'S NAME", 0,0, 'C');
								$pdf->Cell(40, 7, "DISTRICT", 0,0, 'C');
//				$pdf->Cell(10, 7, "U/R", 0, 'C');
								$pdf->Cell(27, 7, "GROUP", 0,0, 'C');
								$pdf->Cell(10, 7, "CH#", 0, 0,'C');
								$pdf->Cell(10, 7, "Marks", 0, 0,'C');
								$pdf->Cell(11, 7, "Year", 0, 'C');
								$pdf->Cell(11, 7, "Marks", 0, 'C');
								$pdf->Cell(11, 7, "Year", 0, 'C');
								$pdf->Cell(11, 7, "MKS", 0, 'C');
								$pdf->Cell(12, 7, "DED", 0, 'C');
								$pdf->Cell(12, 7, "SCR", 0, 'C');
								$pdf->Cell(11, 7, "10%", 0, 'C');
								$pdf->Cell(11, 7, "30%", 0, 'C');
								$pdf->Cell(10, 7, "60%", 0, 'C');
								$pdf->Cell(10, 7, "CPN", 0, 'C');
								$pdf->ln();

							endif;
							$sno++;
							$application_id = $record['APPLICATION_ID'];
							$candidate_name = $record['FIRST_NAME'];
							$surname = $record['LAST_NAME'];
							$fname = $record['FNAME'];
							$category_name = $record['CATEGORY_NAME'];
							$district_name = $record['DISTRICT_NAME'];
							$area = $record['U_R'];
							$choice_no = $record['CHOICE_NO'];
							$cpn_merit_list = $record['CPN_MERIT_LIST'];
                            
                            
							$test_score = $record['TEST_SCORE']+0;
							$detail_cpn = json_decode($record['DETAIL_CPN'],true);
                            
							$ssc = findObjectinList($detail_cpn,"DEGREE_ID",SSC_DEGREE_ID);
							$ssc_total 		= $ssc['TOTAL_MARKS'];
							$ssc_obtained 	= $ssc['OBTAINED_MARKS'];
							$ssc_obtained 	= $ssc['OBTAINED_MARKS'];
							$ssc_passing_year = $ssc['PASSING_YEAR'];
							$ssc_passing_year = $ssc['PASSING_YEAR'];
							$ssc_after_deduct = $ssc['AFTER_DEDUCT_MARKS'];
							$ssc_percentage 	= $ssc['PERCENTAGE'];
							$ssc_cpn_percentage = $ssc['CPN_PERCENTAGE'];

							$hsc = findObjectinList($detail_cpn,"DEGREE_ID",HSC_DEGREE_ID);
							$hsc_total 		= $hsc['TOTAL_MARKS'];
							$hsc_obtained 	= $hsc['OBTAINED_MARKS'];
							$hsc_passing_year = $hsc['PASSING_YEAR'];
							$hsc_after_deduct = $hsc['AFTER_DEDUCT_MARKS'];
							$hsc_percentage 	= $hsc['PERCENTAGE'];
							$hsc_cpn_percentage = $hsc['CPN_PERCENTAGE'];
							$hsc_group 			= $hsc['DISCIPLINE_NAME'];
							$hsc_deduct_marks 	= ($hsc['DEDUCT_MARKS'])?$hsc['DEDUCT_MARKS']:'0';

                            $law = findObjectinList($detail_cpn,"DEGREE_ID","");
                            $law_cpn_percentage = $law['CPN_PERCENTAGE'];

							$hsc_cpn_percentage = $this->TestResult_model->truncate_cpn($hsc_cpn_percentage,2);
							$ssc_cpn_percentage = $this->TestResult_model->truncate_cpn($ssc_cpn_percentage,2);
							$law_cpn_percentage = $this->TestResult_model->truncate_cpn($law_cpn_percentage,2);
//		prePrint($hsc);
							if ($hsc_group == "PRE-MEDICAL") $hsc_group="PRE-MEDL";
							elseif ($hsc_group == "PRE-ENGINEERING") $hsc_group="PRE-ENGG";
							elseif ($hsc_group == "PRE-COMMERCE") $hsc_group="PRE-COMM";
							elseif ($hsc_group == "PRE-COMMERCE") $hsc_group="PRE-COMM";

							if($cate!=$record['CATEGORY_NAME']){
								$pdf->SetFont('Times','B',12);
								$cate = $record['CATEGORY_NAME'];
								$pdf->Cell(190, 7, $cate,0,1, 'L');
								$sno = 1;
								$pdf->SetFont('Times','',9);
							}
							$pdf->SetFont('Times','',9);
							$pdf->Cell(10,7,$sno,1,0,'C');
							$pdf->Cell(15,7,$application_id,1);
							$pdf->Cell(75,7,$candidate_name,1);
							$pdf->Cell(75,7,$fname.', '.$surname,1);
							$pdf->Cell(48,7,$district_name.'  ('.$area.')',1);
//							$pdf->Cell(8,7,$area,1);
							$pdf->Cell(25,7,$hsc_group,1);
							$pdf->Cell(8, 7, $choice_no, 1,0, 'C');
							$pdf->Cell(11, 7, $ssc_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $ssc_passing_year, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_passing_year, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_deduct_marks, 1,0, 'C');
							$pdf->Cell(11, 7, $hsc_after_deduct, 1, 0,'C');
							$pdf->Cell(11, 7, $test_score, 1,0, 'C');
							$pdf->Cell(11, 7, number_format($ssc_cpn_percentage,2), 1,0, 'C');
							$pdf->Cell(11, 7, number_format($hsc_cpn_percentage,2), 1,0, 'C');
							$pdf->Cell(11, 7, number_format($law_cpn_percentage,2), 1, 0,'C');
							$pdf->SetFont('Times','B',9);
							$pdf->Cell(11, 7, number_format($cpn_merit_list,2), 1, 0,'C');
							$pdf->ln();

							$pdf->SetFont('Times','',9);
					endforeach;
					            $pdf->SetY(245);
					
							    $pdf->SetFont('Times','B',8);
								$pdf->ln(3);
//								$pdf->Cell($page_size-30, 7,"COMPUTER PROGRAMMER", 0, 0,'C');
//								$pdf->Cell(30, 7,"ASSISTANT DIRECTOR ADMISSIONS", 0, 1,'C');
								// $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO 14-01-2021. FIRST PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON 15-01-2021. THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND ON ANY STAGE.";
								
								if($message_no==2){
        								$message = "LAST DATE FOR SUBMISSION OF ALL REQUIRED DOCUMENTS @ DIRECTORATE OF ADMISSIONS, ".UNIVERSITY_NAME." & PAYMENT OF ADMISSION FEE CHALLAN IS ".urldecode($first_date).".";
        								}else if($message_no==1){
        								   $message = "LAST DATE FOR SUBMISSION OF OBJECTION (IF ANY) THROUGH ONLINE EPORTAL ACCOUNT UPTO ".urldecode($first_date).". PROVISIONAL MERIT LIST (AFTER CLEARING OBJECTIONS) WILL BE DISPLAYED ON ".urldecode($second_date).".";
        								}else{
        								    $message = $message_no;
        								}
							
								$pdf->MultiCell($page_size-15, 5,$message,0,'C');
								$pdf->MultiCell($page_size-15, 5,'THE PROVISIONAL ADMISSION IS ALLOWED ON BASIS OF DATA SUBMITTED BY APPLICANT. PROVISIONAL SELECTION SHALL BE CANCELLED IF ANY ERROR FOUND AT ANY STAGE.',0,'C');
								
								$pdf->SetFont('Times','',8);
						
								$pdf->Cell($page_size, 5,"NOTE: UNIVERSITY OF SINDH RESERVES THE RIGHT TO RECITFY ERROR OMISSION DETECTED LATER ON AND ALSO RESERVES THE RIGHT TO CANCEL ANY PROVISIONAL ADMISSION AT ANY TIME WITHOUT ISSUING NOTICE CONCERNED TABLE CLERK IS BOUND TO VERIFY ELIGIBILITY ETC.",0,1,'L');

		
								$pdf->Cell($page_size-150, 5,"THIS IS A COMPUTER-GENERATED REPORT AND IT DOES NOT REQUIRE ANY SIGNATURE. THIS REPORT SHALL NOT BE INVALIDATED SOLELY ON THE GROUND THAT IT IS NOT SIGNED.", 0, 0,'l');
								$pdf->Cell(30, 5,"Page No: ".$pdf->PageNo(), 0, 0,'l');
								$pdf->Cell(100, 5,"Powered by: Information Technology Services Centre (ITSC)", 0, 0,'l');
								
								// $pdf->ln();
			endforeach;
		endforeach;

		$pdf->Output("1.pdf",'I');

	}//method
	

    public function display_select_list_for_verification_pdf($campus_id=1,$admission_session_id=1,$message_no=0,$first_date='',$second_date=''){
            // prePrint($message_no);
             //exit();
               $this->form_validation->set_rules('program_type','Program Type is required','required|trim|integer');
    $this->form_validation->set_rules('session','Session is required','required|trim|integer');
    $this->form_validation->set_rules('campus','Campus is required','required|trim|integer');
    $this->form_validation->set_rules('shift_id','Shift is required','required|trim|integer');
    $this->form_validation->set_rules('list_no','List No is required','required|trim|integer');
    //$this->form_validation->set_rules('message','Message is required','required|trim');
    $this->form_validation->set_rules('test_id','Test is required','required|trim|integer');
     $this->form_validation->set_rules('is_provisional','Is Provisional is required','required|trim');
    
		if($this->form_validation->run()){
		    
			$prog_type_id = isValidData($this->input->post('program_type'));
			$is_provisional=isValidData($this->input->post('is_provisional'));
			$shift_id 	  = isValidData($this->input->post('shift_id'));
			$admission_session_id= isValidData($this->input->post('campus'));
			$list_no 	  = isValidData($this->input->post('list_no'));
			$session 	  = isValidData($this->input->post('session'));
			//$message 	  = isValidData($this->input->post('message'));
			$test_id 	  = isValidData($this->input->post('test_id'));
			$PROG_LIST_ID =$this->input->post('PROG_LIST_ID');
		  //  prePrint($PROG_LIST_ID);
			$message_no = 3;
		}else{
		    exit("Input parameters are not complete...");
		}
		$pdf = new FPDF('L','mm','A3');
		$pdf->SetAutoPageBreak(false);
		$pdf->AddPage();
//		$pdf->SetM

		$admission_session_ids = array ($admission_session_id);
	
		$prog_type_id = 1; //bachelor
		
		$data = $this->Selection_list_report_model->selected_list_for_verification($admission_session_id,$shift_id,$list_no,$is_provisional);
	                        $pdf->SetXY(10, 25);
	                        $pdf->SetFont('Times','B',10);
						//	$pdf->Cell(10,6,$sno,1,0,'C');
							$pdf->Cell(20,6,'APP_ID',1,0,'C');
							$pdf->Cell(11,6,"GEN",1,0,'C');
							$pdf->Cell(85,6,"NAME",1,0,'C');
							$pdf->Cell(90,6,'FATHER NAME , SURNAME',1,0,'C');
							$pdf->Cell(45,6,'DISTRICT',1,0,'C');
							$pdf->Cell(10,6,'AREA',1,0,'C');
							$pdf->Cell(20,6,"CPN  ",1,0,'C');
						    $pdf->Cell(110,6,"SELECTION PROGARM",1,0,'C');
						    	$pdf->Cell(10,6,"CH#",1,1,'C'); 
        //echo count($data);
        foreach($data as $record){
            //$sno++;
							$application_id = $record['APPLICATION_ID'];
							$candidate_name = $record['FIRST_NAME'];
							$surname = $record['LAST_NAME'];
							$fname = $record['FNAME'];
							$category_name = $record['CATEGORY_NAME'];
							$PROGRAM_TITLE = $record['PROGRAM_TITLE'];
							$district_name = $record['DISTRICT_NAME'];
							$area = $record['U_R'];
							$choice_no = $record['CHOICE_NO'];
							$cpn_merit_list = $record['CPN'];
							$gender= $record['GENDER'];
							$test_cpn = 0;
							$test_score = ($record['TEST_SCORE']?$record['TEST_SCORE']:'0');
							$detail_cpn = json_decode($record['DETAIL_CPN'],true);

							$ssc = findObjectinList($detail_cpn,"DEGREE_ID",SSC_DEGREE_ID);
							$ssc_total 		= $ssc['TOTAL_MARKS'];
							$ssc_obtained 	= $ssc['OBTAINED_MARKS'];
						
							$ssc_passing_year = $ssc['PASSING_YEAR'];
							$ssc_after_deduct = $ssc['AFTER_DEDUCT_MARKS'];
							$ssc_percentage 	= $ssc['PERCENTAGE'];
							$ssc_cpn_percentage = $ssc['CPN_PERCENTAGE'];
                            $ssc_group 			= $ssc['DISCIPLINE_NAME'];
							$hsc = findObjectinList($detail_cpn,"DEGREE_ID",HSC_DEGREE_ID);
							$hsc_total 		= $hsc['TOTAL_MARKS'];
							$hsc_obtained 	= $hsc['OBTAINED_MARKS'];
							$hsc_passing_year = $hsc['PASSING_YEAR'];
							$hsc_after_deduct = $hsc['AFTER_DEDUCT_MARKS'];
							$hsc_percentage 	= $hsc['PERCENTAGE'];
							$hsc_cpn_percentage = $hsc['CPN_PERCENTAGE'];
							$hsc_group 			= $hsc['DISCIPLINE_NAME'];
							$hsc_deduct_marks 	= isset($hsc['DEDUCT_MARKS'])?$hsc['DEDUCT_MARKS']:'0';
                            $hsc_deduct_marks = 0;
							$hsc_cpn_percentage = $this->TestResult_model->truncate_cpn($hsc_cpn_percentage,2);
							$ssc_cpn_percentage = $this->TestResult_model->truncate_cpn($ssc_cpn_percentage,2);
							$test_cpn = $this->TestResult_model->truncate_cpn($test_cpn,2);
							$graduation = findObjectinList($detail_cpn,"DEGREE_ID",6);
							if(!$graduation){
							    $graduation = findObjectinList($detail_cpn,"DEGREE_ID",5);
							}
							if(!$graduation){
							    $graduation = findObjectinList($detail_cpn,"DEGREE_ID",4); 
							}
							if($graduation){
							$graduation_total = $graduation['TOTAL_MARKS'];
							$graduation_obtained = $graduation['OBTAINED_MARKS'];
							$graduation_passing_year =$graduation['PASSING_YEAR'];
							$graduation_after_deduct = $graduation['AFTER_DEDUCT_MARKS'];
							$graduation_per = $graduation['PERCENTAGE'];
							$graduation_cpn_per = $graduation['CPN_PERCENTAGE'];
							$graduation_dis = $graduation['DISCIPLINE_NAME']; 
							$graduation_deduct 	= $graduation['DEDUCT_MARKS'];
							}
							
				//  			prePrint($graduation);
				// 		exit();
//		prePrint($hsc);
							if ($hsc_group == "PRE-MEDICAL") $hsc_group="PRE-MEDL";
							elseif ($hsc_group == "PRE-ENGINEERING") $hsc_group="PRE-ENGG";
							elseif ($hsc_group == "PRE-COMMERCE") $hsc_group="PRE-COMM";
							elseif ($hsc_group == "ASSOCIATE DIPLOMA IN ENGINEERING") $hsc_group="AD-ENGG";
							elseif ($hsc_group == "ASSOCIATE DIPLOMA IN COMMERCE") $hsc_group="AD-COMM";

							if( $pdf->GetY()>=260){
							    $pdf->ln();
							    	$pdf->Cell(30, 5,"Page No: ".$pdf->PageNo(), 0, 0,'l');
								$pdf->Cell(100, 5,"Powered by: Information Technology Services Centre (ITSC)", 0, 0,'l');
							    $pdf->AddPage();
							    $pdf->SetXY(10, 25);
							    $pdf->SetFont('Times','B',10);
						//	$pdf->Cell(10,7,$sno,1,0,'C');
							$pdf->Cell(20,7,'APP_ID',1,0,'C');
							$pdf->Cell(11,7,"GEN",1,0,'C');
							$pdf->Cell(85,7,"NAME",1,0,'C');
							$pdf->Cell(90,7,'FATHER NAME , SURNAME',1,0,'C');
							$pdf->Cell(45,7,'DISTRICT',1,0,'C');
							$pdf->Cell(10,7,'AREA',1,0,'C');
							$pdf->Cell(20,7,"CPN  ",1,0,'C');
						    $pdf->Cell(110,7,"SELECTION PROGARM",1,'C');
						    	$pdf->Cell(10,7,"CH#",1,1,'C'); 
							}
							$pdf->SetFont('Times','B',10);
						//	$pdf->Cell(10,7,$sno,1,0,'C');
							$pdf->Cell(20,7,$application_id,1);
							$pdf->Cell(11,7,$gender,1,0,'C');
							$pdf->Cell(85,7,$candidate_name,1);
							$pdf->Cell(90,7,$fname.', '.$surname,1);
							$pdf->Cell(45,7,$district_name,1);
							$pdf->Cell(10,7,$area,1,0,'C');
							$pdf->Cell(20,7,number_format($cpn_merit_list,2),1,0,'C');
						    $pdf->Cell(110,7,$PROGRAM_TITLE,1);
						    	$pdf->Cell(10,7,$choice_no,1,0,'C');
							
							$pdf->ln();
							$pdf->SetFont('Times','',9);
							
							if($graduation){
							$pdf->Cell(20,7,'',0);
							$pdf->Cell(11, 7, 'SSC', 1, 0,'L');
							$pdf->Cell(11, 7, $ssc_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $ssc_total, 1, 0,'C');
							$pdf->Cell(11, 7, $ssc_passing_year, 1, 0,'C');
							$pdf->Cell(15,7,$ssc_group,1);
							
							$pdf->Cell(11, 7, 'HSC', 1, 0,'L');
							$pdf->Cell(11, 7, $hsc_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_total, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_passing_year, 1, 0,'C');
							$pdf->Cell(11, 7, $hsc_deduct_marks, 1,0, 'C');
							$pdf->Cell(11, 7, $hsc_after_deduct, 1, 0,'C');
							$pdf->Cell(20, 7, $hsc_group, 1, 0,'C');
							
							$pdf->Cell(11, 7, 'GRD', 1, 0,'L');
							$pdf->Cell(11, 7, $graduation_obtained, 1, 0,'C');
							$pdf->Cell(11, 7, $graduation_total, 1, 0,'C');
							$pdf->Cell(11, 7, $graduation_passing_year, 1, 0,'C');
							$pdf->Cell(11, 7, $graduation_deduct, 1,0, 'C');
							$pdf->Cell(11, 7, $graduation_after_deduct, 1, 0,'C');
							$pdf->Cell(50, 7, $graduation_dis, 1, 0,'L');
							
							} else {
							$pdf->Cell(31,7,'',0);
							$pdf->Cell(15, 7, 'SSC', 1, 0,'L');
							$pdf->Cell(15, 7, $ssc_obtained, 1, 0,'C');
							$pdf->Cell(15, 7, $ssc_total, 1, 0,'C');
							$pdf->Cell(15, 7, $ssc_passing_year, 1, 0,'C');
							$pdf->Cell(25,7,$ssc_group,1);
							
							$pdf->Cell(15, 7, 'HSC', 1, 0,'L');
							$pdf->Cell(15, 7, $hsc_obtained, 1, 0,'C');
							$pdf->Cell(15, 7, $hsc_total, 1, 0,'C');
							$pdf->Cell(15, 7, $hsc_passing_year, 1, 0,'C');
							$pdf->Cell(15, 7, $hsc_deduct_marks, 1,0, 'C');
							$pdf->Cell(15, 7, $hsc_after_deduct, 1, 0,'C');
							$pdf->Cell(45, 7, $hsc_group, 1, 0,'C');
							$pdf->Cell(30, 7, '', 1, 0,'C');
							}
							$pdf->Cell(110, 7, $category_name, 1, 0,'C');
				// 			$pdf->Cell(11, 7, $test_score, 1,0, 'C');
				// 			$pdf->Cell(11, 7, number_format($ssc_cpn_percentage,2), 1,0, 'C');
				// 			$pdf->Cell(11, 7, number_format($hsc_cpn_percentage,2), 1,0, 'C');
				// 			$pdf->Cell(11, 7, number_format($test_cpn,2), 1, 0,'C');
							$pdf->SetFont('Times','B',9);
						//	$pdf->Cell(11, 7, number_format($cpn_merit_list,2), 1, 0,'C');
							$pdf->ln();
							 $pdf->ln(2);

        }
        $pdf->ln();
        	$pdf->Cell(30, 5,"Page No: ".$pdf->PageNo(), 0, 0,'l');
								$pdf->Cell(100, 5,"Powered by: Information Technology Services Centre (ITSC)", 0, 0,'l');
        //prePrint($data);
        //exit();
		$pdf->Output("1.pdf",'I');

	}//method

    public function getAdmissionList(){
        
        $this->form_validation->set_rules('admission_session_id','Admission Session ID is required','required|trim|integer');
        $this->form_validation->set_rules('shift_id','Shift ID is required','required|trim|integer');
        

		if($this->form_validation->run())
		{
			$admission_session_id 	= isValidData($this->input->post('admission_session_id'));
			$shift_id 	            = isValidData($this->input->post('shift_id'));
			
		
			$record =$this->Selection_list_report_model->get_admission_list_no($admission_session_id,$shift_id);
			
			if (is_array($record) || is_object($record))
			{
				$record = json_encode($record);
				http_response_code(200);
				exit($record);
			}else
			{
				$reponse = "<div class='text-danger'>Sorry record not found.</div>";
				http_response_code(405);
				exit(json_encode($reponse));
			}
		}else
		{
			$reponse = "<div class='text-danger'>Sorry you have provided invalid parameters</div>";
			http_response_code(405);
			exit(json_encode($reponse));
		}
        
    }
    
    /*
     * YASIR CREATED NEW METHODS 20-02-2021
     * 
     */
	public function vacantSeatsReport(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
//		$this->verify_path($this->script_name,$side_bar_data);

		$academic_session = $this->Admission_session_model->getSessionData();
		$program_types 	= $this->Administration->programTypes ();
		$shift = $this->Administration->shifts ();
// 		$application_status_list = $this->FormVerificationModel->get_application_status_list ();
		// $district_list = $this->Api_location_model->getDistrictByProvinceId(6);

// 		$data['district_list'] = $district_list;
		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;
		$data['academic_sessions'] = $academic_session;
		$data['program_types'] = $program_types;
// 		$data['application_status_list'] = $application_status_list;
		$data['shifts'] = $shift;

		$this->load->view('include/header',$data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar',$data);
		$this->load->view('include/nav',$data);
		$this->load->view('admin/vacant_seats_report_window',$data);
//		$this->load->view('include/footer_area');
		$this->load->view('include/footer');

	}

	public function vacantSeatsData(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id = isValidData($request->program_type_id);
		$shift_id 	= isValidData($request->shift_id);
		$campus_id 	= isValidData($request->campus_id);
		$session_id 	= isValidData($request->session_id);

		$prog_list_ids = array();

		$error = "";
		if (empty($program_type_id))
			$error.="Program Type is Required";
		elseif (empty($shift_id))
			$error.="Shift is Required";
		elseif (empty($campus_id))
			$error.="Campus is Required";
		if (empty($error)){

		$admission_session_data = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
//		prePrint($admission_session_data);
//		exit();
		$admission_session_id=$admission_session_data['ADMISSION_SESSION_ID'];
		$filled_seats = $this->Selection_list_report_model->getFilledSeats($admission_session_id,$shift_id);
		$total_seats = $this->Selection_list_report_model->getDisciplineSeatsDistributionsWithCategory($campus_id,$shift_id,$session_id,$program_type_id,$prog_list_ids);
//		prePrint($filled_seats[1][4]);
		$new_array = array();
		foreach ($total_seats as $total_seat){
			$CAMPUS_ID 			= $total_seat['CAMPUS_ID'];
			$CATEGORY_TYPE_ID 	= $total_seat['CATEGORY_TYPE_ID'];
			$CATEGORY_ID 		= $total_seat['CATEGORY_ID'];
			$PROG_LIST_ID 		= $total_seat['PROG_LIST_ID'];
			$CATEGORY_TYPE_NAME = $total_seat['CATEGORY_TYPE_NAME'];
			$PROGRAM_TITLE 		= $total_seat['PROGRAM_TITLE'];
			$CATEGORY_NAME 		= $total_seat['CATEGORY_NAME'];
			$CAMPUS_NAME 		= $total_seat['NAME'];
			$TOTAL_SEATS 		= $total_seat['TOTAL_SEATS'];
			if (empty($TOTAL_SEATS)) $TOTAL_SEATS=0;

			$prog_filled_seat=0;
			if(isset($filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS'])){
				$prog_filled_seat = ($filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS'])?$filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS']:0;
			}
			if (empty($prog_filled_seat))$prog_filled_seat=0;

			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CAMPUS_NAME']=$CAMPUS_NAME;
			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['PROGRAM_TITLE']=$PROGRAM_TITLE;

			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORY_TYPE_NAME']= $CATEGORY_TYPE_NAME;
			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORIES'][]= array (
							'CATEGORY_ID'=>$CATEGORY_ID,
							'CATEGORY_NAME'=>$CATEGORY_NAME,
							'TOTAL_SEATS'=>$TOTAL_SEATS,
							'FILLED_SEATS'=>$prog_filled_seat,
							'VACANT_SEATS'=>$TOTAL_SEATS-$prog_filled_seat,
				);
		}//end foreach of iteration of all categories
//		prePrint($new_array);
	}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if (empty($new_array)){
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Record Not Found...'));
		}else{
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($new_array));
		}

	}//method
	
	public function printVacantSeatReport(){
//		$this->load->view('include/header');
		$this->load->view('admin/vacant_seats_report_print.html');
	}
	
	/*
     * Kashif CREATED NEW METHODS 07-03-2021
     * */
	public function vacantSeatsReportWithCurrentSelection(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
//		$this->verify_path($this->script_name,$side_bar_data);

		$academic_session = $this->Admission_session_model->getSessionData();
		$program_types 	= $this->Administration->programTypes();
		$shift = $this->Administration->shifts ();
// 		$application_status_list = $this->FormVerificationModel->get_application_status_list ();
		// $district_list = $this->Api_location_model->getDistrictByProvinceId(6);

// 		$data['district_list'] = $district_list;
		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;
		$data['academic_sessions'] = $academic_session;
		$data['program_types'] = $program_types;
// 		$data['application_status_list'] = $application_status_list;
		$data['shifts'] = $shift;

		$this->load->view('include/header',$data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar',$data);
		$this->load->view('include/nav',$data);
		$this->load->view('admin/vacant_seats_report_window_with_current_selection',$data);
//		$this->load->view('include/footer_area');
		$this->load->view('include/footer');

	}
	
	public function vacantSeatsDataWithCurrentSelection(){
       
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id = isValidData($request->program_type_id);
		$shift_id 	= isValidData($request->shift_id);
		$campus_id 	= isValidData($request->campus_id);
		$session_id 	= isValidData($request->session_id);
		$admission_list_id 	= isValidData($request->admission_list_id);

		$prog_list_ids = array();

		$error = "";
		if (empty($program_type_id))
			$error.="Program Type is Required";
		elseif (empty($shift_id))
			$error.="Shift is Required";
		elseif (empty($campus_id))
			$error.="Campus is Required";
		elseif (empty($admission_list_id))
			$error.="List No is Required";
		if (empty($error)){

		$admission_session_data = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
//		prePrint($admission_session_data);
//		exit();
		$admission_session_id=$admission_session_data['ADMISSION_SESSION_ID'];
		$filled_seats = $this->Selection_list_report_model->getFilledSeats($admission_session_id,$shift_id);
		$total_seats = $this->Selection_list_report_model->getDisciplineSeatsDistributionsWithCategory($campus_id,$shift_id,$session_id,$program_type_id,$prog_list_ids);
//		prePrint($filled_seats[1][4]);
		$new_array = array();
		foreach ($total_seats as $total_seat){
			$CAMPUS_ID 			= $total_seat['CAMPUS_ID'];
			$CATEGORY_TYPE_ID 	= $total_seat['CATEGORY_TYPE_ID'];
			$CATEGORY_ID 		= $total_seat['CATEGORY_ID'];
			$PROG_LIST_ID 		= $total_seat['PROG_LIST_ID'];
			$CATEGORY_TYPE_NAME = $total_seat['CATEGORY_TYPE_NAME'];
			$PROGRAM_TITLE 		= $total_seat['PROGRAM_TITLE'];
			$CATEGORY_NAME 		= $total_seat['CATEGORY_NAME'];
			$CAMPUS_NAME 		= $total_seat['NAME'];
			$TOTAL_SEATS 		= $total_seat['TOTAL_SEATS'];
			if (empty($TOTAL_SEATS)) $TOTAL_SEATS=0;

			$prog_filled_seat=0;
			if(isset($filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS'])){
				$prog_filled_seat = ($filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS'])?$filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS']:0;
			}
			if (empty($prog_filled_seat))$prog_filled_seat=0;

			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CAMPUS_NAME']=$CAMPUS_NAME;
			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['PROGRAM_TITLE']=$PROGRAM_TITLE;

			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORY_TYPE_NAME']= $CATEGORY_TYPE_NAME;
			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORIES'][]= array (
							'CATEGORY_ID'=>$CATEGORY_ID,
							'CATEGORY_NAME'=>$CATEGORY_NAME,
							'TOTAL_SEATS'=>$TOTAL_SEATS,
							'FILLED_SEATS'=>$prog_filled_seat,
							'VACANT_SEATS'=>$TOTAL_SEATS-$prog_filled_seat,
				);
		}//end foreach of iteration of all categories
//		prePrint($new_array);
	}
	else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
// 		foreach($new_array as $v){
// 		    prePrint($v);
// 		exit();
// 		}
		 $result = $this->Selection_list_report_model->getSelectionListCountByAdmissionListId($admission_list_id);
		 $promoted_seat = $this->Selection_list_report_model->getSelectionListPreviousPromotedCountByAdmissionListId($admission_list_id);
		 foreach($result as $value){
		     $prog_list_id = $value['PROG_LIST_ID'];
		     $CATEGORY_TYPE_ID = $value['CATEGORY_TYPE_ID'];
		      $data = &$new_array[$campus_id][$prog_list_id]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORIES'];
		      for($i=0;$i<count($data);$i++){
		          if($value['CATEGORY_ID']==$data[$i]['CATEGORY_ID']){
		              $data[$i]['CURRENT_FILLED_SEAT'] = $value['FILLED_SEAT'];
		               $data[$i]['TOTAL_VACANT_SEAT_WITH_SELECTION'] = $data[$i]['VACANT_SEATS']-$value['FILLED_SEAT'] ;
		                $data[$i]['TOTAL_FILLED_SEAT_WITH_SELECTION'] = $data[$i]['FILLED_SEATS']+$value['FILLED_SEAT'] ;
		             $data[$i]['PROMOTED_SEAT'] = isset($promoted_seat[$prog_list_id][$value['CATEGORY_ID']])?$promoted_seat[$prog_list_id][$value['CATEGORY_ID']]:0;
		          }
		      }
		 }
       
		
		if (empty($new_array)){
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Record Not Found...'));
		}else{
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($new_array));
		}

	}//method
	//kashif created on demand for sir ayaz keerio
	public function vacantSeatsReportAllProg(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
//		$this->verify_path($this->script_name,$side_bar_data);

		$academic_session = $this->Admission_session_model->getSessionData();
		$program_types 	= $this->Administration->programTypes ();
		$shift = $this->Administration->shifts ();
// 		$application_status_list = $this->FormVerificationModel->get_application_status_list ();
		// $district_list = $this->Api_location_model->getDistrictByProvinceId(6);

// 		$data['district_list'] = $district_list;
		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;
		$data['academic_sessions'] = $academic_session;
		$data['program_types'] = $program_types;
// 		$data['application_status_list'] = $application_status_list;
		$data['shifts'] = $shift;

		$this->load->view('include/header',$data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar',$data);
		$this->load->view('include/nav',$data);
		$this->load->view('admin/vacant_seats_report_window_all_prog',$data);
//		$this->load->view('include/footer_area');
		$this->load->view('include/footer');

	}
	
	public function vacantSeatsDataAllProg(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id = isValidData($request->program_type_id);
		$shift_id 	= isValidData($request->shift_id);
		$campus_id 	= isValidData($request->campus_id);
		$session_id 	= isValidData($request->session_id);

		$prog_list_ids = array();

		$error = "";
		if (empty($program_type_id))
			$error.="Program Type is Required";
		elseif (empty($shift_id))
			$error.="Shift is Required";
		elseif (empty($campus_id))
			$error.="Campus is Required";
		if (empty($error)){

		$admission_session_data = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
//		prePrint($admission_session_data);
//		exit();
		$admission_session_id=$admission_session_data['ADMISSION_SESSION_ID'];
		$filled_seats = $this->Selection_list_report_model->getFilledSeats($admission_session_id,$shift_id);
		$total_seats = $this->Selection_list_report_model->getDisciplineSeatsDistributionsWithCategory($campus_id,$shift_id,$session_id,$program_type_id,$prog_list_ids);
//		prePrint($filled_seats[1][4]);
		$new_array = array();
		foreach ($total_seats as $total_seat){
			$CAMPUS_ID 			= $total_seat['CAMPUS_ID'];
			$CATEGORY_TYPE_ID 	= $total_seat['CATEGORY_TYPE_ID'];
			$CATEGORY_ID 		= $total_seat['CATEGORY_ID'];
			$PROG_LIST_ID 		= $total_seat['PROG_LIST_ID'];
			$CATEGORY_TYPE_NAME = $total_seat['CATEGORY_TYPE_NAME'];
			$PROGRAM_TITLE 		= $total_seat['PROGRAM_TITLE'];
			$CATEGORY_NAME 		= $total_seat['CATEGORY_NAME'];
			$CAMPUS_NAME 		= $total_seat['NAME'];
			$TOTAL_SEATS 		= $total_seat['TOTAL_SEATS'];
			if (empty($TOTAL_SEATS)) $TOTAL_SEATS=0;

			$prog_filled_seat=0;
			if(isset($filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS'])){
				$prog_filled_seat = ($filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS'])?$filled_seats[$admission_session_id][$PROG_LIST_ID][$CATEGORY_ID]['FILLED_SEATS']:0;
			}
			if (empty($prog_filled_seat))$prog_filled_seat=0;

			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CAMPUS_NAME']=$CAMPUS_NAME;
			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['PROGRAM_TITLE']=$PROGRAM_TITLE;
				$new_array[$CAMPUS_ID][$PROG_LIST_ID]['TOTAL_SEATS']+=$TOTAL_SEATS;
				$new_array[$CAMPUS_ID][$PROG_LIST_ID]['FILLED_SEATS']+=$prog_filled_seat;
					$new_array[$CAMPUS_ID][$PROG_LIST_ID]['VACANT_SEATS']+=($TOTAL_SEATS-$prog_filled_seat);

			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORY_TYPE_NAME']= $CATEGORY_TYPE_NAME;
			$new_array[$CAMPUS_ID][$PROG_LIST_ID]['CATEGORY_TYPE'][$CATEGORY_TYPE_ID]['CATEGORIES'][]= array (
							'CATEGORY_ID'=>$CATEGORY_ID,
							'CATEGORY_NAME'=>$CATEGORY_NAME,
							'TOTAL_SEATS'=>$TOTAL_SEATS,
							'FILLED_SEATS'=>$prog_filled_seat,
							'VACANT_SEATS'=>$TOTAL_SEATS-$prog_filled_seat,
				);
		}//end foreach of iteration of all categories
//		prePrint($new_array);
	}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if (empty($new_array)){
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode('Record Not Found...'));
		}else{
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($new_array));
		}

	}//method
	
	public function printVacantAllProgSeatReport(){
//		$this->load->view('include/header');
		$this->load->view('admin/vacant_seats_all_prog_report_print.html');
	}
	
	public function SelectedCandidates(){

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
		$this->load->view('admin/programSelectedCandidatesWindow',$data);
		$this->load->view('include/footer_area');
		$this->load->view('include/footer');
	}
	
	public function selectedReportHandler(){
	    
	    $postdata   = file_get_contents("php://input");
		$request    = json_decode($postdata);

		$program_type_id    = isValidData($request->program_type_id);
		$shift_id 	        = isValidData($request->shift_id);
		$campus_id 	        = isValidData($request->campus_id);
		$session_id 	    = isValidData($request->session_id);
		$prog_list_id       = $request->program_id;
		$prog_list_id       = implode(",",$prog_list_id);
        
        $error = "";
		if (empty($program_type_id))
			$error.="Program Type is Required";
		elseif (empty($shift_id))
			$error.="Shift is Required";
		elseif (empty($campus_id))
			$error.="Campus is Required";
		elseif (empty($session_id))
			$error.="Session is Required";
		elseif (empty($prog_list_id))
			$error.="Program is Required";
		
		if (empty($error)){
		
        $admission_session_data = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
		$admission_session_id=$admission_session_data['ADMISSION_SESSION_ID'];
		//$rows = $this->Selection_list_report_model->getProgramSelectedCandidates($admission_session_id,$prog_list_id,$shift_id);
	   	$candidates = $this->RollNo_model->get_candidates($admission_session_id, $shift_id, $prog_list_id);
				 //  prePrint($candidates);
             //exit;
// 		header('Content-Type: text/csv; charset=utf-8');
// 		header('Content-Disposition: attachment; filename=REPORT.csv');

// create a file pointer connected to the output stream
// 		$output = fopen('php://output', 'w');

				// $columns = array("APPLICATION_ID","CARD_NO","FIRST_NAME","FNAME","LAST_NAME","CNIC NO","GENDER","AREA","DISTRICT NAME","CAMPUS_NAME","CATEGORY_NAME","PROGRAM","MATRICULATION PASSING YEAR","MATRICULATION TOTAL","MATRICULATION OBT","INTERMEDIATE PASSING YEAR","INTERMEDIATE TOTAL","INTERMEDIATE OBT","TEST SCORE","CPN");
// 	fputcsv($output,$columns);
		$new_array = array();
// 		array_push($new_array,$columns);
		
		foreach ($candidates as $row){
			$APPLICATION_ID 	= $row['APPLICATION_ID'];
			$CARD_ID 			= $row['CARD_ID'];
			$CNIC_NO 			= $row['CNIC_NO'];
			$FIRST_NAME = $row['FIRST_NAME'];
			$LAST_NAME 	= $row['LAST_NAME'];
			$FNAME	 	= $row['FNAME'];
			$GENDER 	= $row['GENDER'];
			$U_R 		= $row['U_R'];
			$DISTRICT_NAME 	= $row['DISTRICT_NAME'];
			$CAMPUS_NAME 	= $row['CAMPUS_NAME'];
			$CATEGORY_NAME 	= $row['CATEGORY_NAME'];
			$PROGRAM_TITLE 	= $row['PROGRAM_TITLE'];
			$CPN 			= $row['CPN'];
			$TEST_SCORE 	= $row['TEST_SCORE'];
			$detail_cpn 	= $row['DETAIL_CPN'];
			$detail_cpn 	= json_decode($detail_cpn,true);

			$result_metric 	= findObjectinList($detail_cpn,'DEGREE_ID',2);
			$metric_year 	= $result_metric['PASSING_YEAR'];
			$metric_obt 	= $result_metric['OBTAINED_MARKS'];
			$metric_total 	= $result_metric['TOTAL_MARKS'];

			$result_inter 	= findObjectinList($detail_cpn,'DEGREE_ID',3);
			$inter_year 	= $result_inter['PASSING_YEAR'];
			$inter_obt 		= $result_inter['OBTAINED_MARKS'];
			$inter_total 	= $result_inter['TOTAL_MARKS'];
            
            $data = array ("APPLICATION_ID"=>$APPLICATION_ID,
                            "CARD_ID"=>$CARD_ID,
                            "FIRST_NAME"=>$FIRST_NAME,
                            "FNAME"=>$FNAME,
                            "LAST_NAME"=>$LAST_NAME,
                            "CNIC_NO"=>$CNIC_NO,
                            "GENDER"=>$GENDER,
                            "U_R"=>$U_R,
                            "DISTRICT_NAME"=>$DISTRICT_NAME,
                            "CAMPUS_NAME"=>$CAMPUS_NAME,
                            "CATEGORY_NAME"=>$CATEGORY_NAME,
                            "PROGRAM_TITLE"=>$PROGRAM_TITLE,
                            "METRIC_YEAR"=>$metric_year,
                            "METRIC_TOTAL"=>$metric_total,
                            "METRIC_OBT"=>$metric_obt,
                            "INTER_YEAR"=>$inter_year,
                            "INTER_TOTAL"=>$inter_total,
                            "INTER_OBT"=>$inter_obt,
                            "TEST_SCORE"=>$TEST_SCORE,
                            "CPN"=>$CPN);
            array_push($new_array,$data);
// 			$csv_row = array("$APPLICATION_ID","$CARD_ID","$FIRST_NAME","$FNAME","$LAST_NAME","$CNIC_NO","$GENDER","$U_R","$DISTRICT_NAME","$CAMPUS_NAME","$CATEGORY_NAME","$PROGRAM_TITLE","$metric_year","$metric_total","$metric_obt","$inter_year","$inter_total","$inter_obt","$TEST_SCORE","$CPN");
// 			fputcsv($output,$csv_row);

		}
		    echo json_encode($new_array);
		    exit;
	    }else{
	        echo $error;
		    exit;
	    }
	}//method
	
	public function total_selection_discipline_wise_handler($campus_id=1,$admission_session_id=1,$message_no=0,$first_date='',$second_date=''){
        
        
		$this->form_validation->set_rules('program_type','Program Type is required','required|trim|integer');
		$this->form_validation->set_rules('session','Session is required','required|trim|integer');
		$this->form_validation->set_rules('campus','Campus is required','required|trim|integer');
		$this->form_validation->set_rules('shift_id','Shift is required','required|trim|integer');
		$this->form_validation->set_rules('list_no','List No is required','required|trim|integer');
		$this->form_validation->set_rules('test_id','Test is required','required|trim|integer');
		$this->form_validation->set_rules('is_provisional','Is Provisional is required','required|trim');
  
		if($this->form_validation->run()){
			$prog_type_id = isValidData($this->input->post('program_type'));
			$is_provisional=isValidData($this->input->post('is_provisional'));
			$shift_id 	  = isValidData($this->input->post('shift_id'));
			$admission_session_id= isValidData($this->input->post('campus'));//campus use as aadmission session _id
			$list_no 	  = isValidData($this->input->post('list_no'));
			$session 	  = isValidData($this->input->post('session'));
			$test_id 	  = isValidData($this->input->post('test_id'));
			$PROG_LIST_ID =$this->input->post('PROG_LIST_ID');
		}else{
		    prePrint($_POST);
			exit("Input parameters are not complete....");
		}

		$prog_type_id = 1; //bachelor

		$data = $this->Selection_list_report_model->total_selection_discipline_wise($admission_session_id,$shift_id,$list_no,$is_provisional);

		$report = array('PROGRAM TITLE','COMMERCE QUOTA','DISABLE PERSONS QUOTA','SUE AFFILIATED COLLEGE SD QUOTA','QUOTA / GENERAL MERIT (OUT OF JURISDICTION)','QUOTA / GENERAL MERIT (JURISDICTION)','FEMALE QUOTA (JURISDICTION)','FEMALE QUOTA (OUT OF JURISDICTION)','KARACHI RESERVED QUOTA','OTHER PROVINCES SELF FINANCE','SELF FINANCE','SUE SON DAUGHTER QUOTA','TOTAL','M','F');
		$report_2= array();
		foreach($data as $record){

			$application_id = $record['APPLICATION_ID'];
			$candidate_name = $record['FIRST_NAME'];
			$surname = $record['LAST_NAME'];
			$fname = $record['FNAME'];
			$category_name = $record['CATEGORY_NAME'];
			$PROGRAM_TITLE = $record['PROGRAM_TITLE'];
			$district_name = $record['DISTRICT_NAME'];
			$area = $record['U_R'];
			$choice_no = $record['CHOICE_NO'];
			$cpn_merit_list = $record['CPN'];
			$gender= $record['GENDER'];
			$active= $record['ACTIVE'];
			$prog_list_id= $record['PROG_LIST_ID'];
			$category_id= $record['CATEGORY_ID'];

			if ($active==0) continue;

			$report_2[$PROGRAM_TITLE]['PROGRAM_TITLE']=$PROGRAM_TITLE;
//			$report_2[$PROGRAM_TITLE]['CATEGORY_NAME']=$category_name;

			if ($report_2[$PROGRAM_TITLE]['PROGRAM_TITLE']==$PROGRAM_TITLE){
				if($category_name == "COMMERCE QUOTA") @$report_2[$PROGRAM_TITLE]['COMMERCE_QUOTA']++;
				elseif($category_name == "DISABLE PERSONS QUOTA") @$report_2[$PROGRAM_TITLE]['DISABLE_PERSONS_QUOTA']++;
				elseif($category_name == "QUOTA / GENERAL MERIT (OUT OF JURISDICTION)") @$report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_OUT_OF_JURISDICTION']++;
				elseif($category_name == "SUE AFFILIATED COLLEGE SD QUOTA") @$report_2[$PROGRAM_TITLE]['SUE_AFFILIATED_COLLEGE_SD_QUOTA']++;
				elseif($category_name == "QUOTA / GENERAL MERIT (JURISDICTION)") @$report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_JURISDICTION']++;
				elseif($category_name == "FEMALE QUOTA (JURISDICTION)") @$report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_JURISDICTION']++;
				elseif($category_name == "FEMALE QUOTA (OUT OF JURISDICTION)") @$report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_OUT_OF_JURISDICTION']++;
				elseif($category_name == "KARACHI RESERVED QUOTA") @$report_2[$PROGRAM_TITLE]['KARACHI_RESERVED_QUOTA']++;
				elseif($category_name == "OTHER PROVINCES SELF FINANCE") @$report_2[$PROGRAM_TITLE]['OTHER_PROVINCES_SELF_FINANCE']++;
				elseif($category_name == "SELF FINANCE") @$report_2[$PROGRAM_TITLE]['SELF_FINANCE']++;
				elseif($category_name == "SUE SON DAUGHTER QUOTA") @$report_2[$PROGRAM_TITLE]['SUE_SON_DAUGHTER_QUOTA']++;

				if(!isset($report_2[$PROGRAM_TITLE]['COMMERCE_QUOTA'])) $report_2[$PROGRAM_TITLE]['COMMERCE_QUOTA']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['DISABLE_PERSONS_QUOTA'])) $report_2[$PROGRAM_TITLE]['DISABLE_PERSONS_QUOTA']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_OUT_OF_JURISDICTION'])) $report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_OUT_OF_JURISDICTION']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['SUE_AFFILIATED_COLLEGE_SD_QUOTA'])) $report_2[$PROGRAM_TITLE]['SUE_AFFILIATED_COLLEGE_SD_QUOTA']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_JURISDICTION'])) $report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_JURISDICTION']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_JURISDICTION'])) $report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_JURISDICTION']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_OUT_OF_JURISDICTION'])) $report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_OUT_OF_JURISDICTION']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['KARACHI_RESERVED_QUOTA'])) $report_2[$PROGRAM_TITLE]['KARACHI_RESERVED_QUOTA']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['OTHER_PROVINCES_SELF_FINANCE'])) $report_2[$PROGRAM_TITLE]['OTHER_PROVINCES_SELF_FINANCE']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['SELF_FINANCE'])) $report_2[$PROGRAM_TITLE]['SELF_FINANCE']=0;
				if(!isset($report_2[$PROGRAM_TITLE]['SUE_SON_DAUGHTER_QUOTA'])) $report_2[$PROGRAM_TITLE]['SUE_SON_DAUGHTER_QUOTA']=0;

				$report_2[$PROGRAM_TITLE]['TOTAL']= $report_2[$PROGRAM_TITLE]['COMMERCE_QUOTA']+$report_2[$PROGRAM_TITLE]['DISABLE_PERSONS_QUOTA']+$report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_OUT_OF_JURISDICTION']+$report_2[$PROGRAM_TITLE]['SUE_AFFILIATED_COLLEGE_SD_QUOTA']+$report_2[$PROGRAM_TITLE]['QUOTA_GENERAL_MERIT_JURISDICTION']+$report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_JURISDICTION']+$report_2[$PROGRAM_TITLE]['FEMALE_QUOTA_OUT_OF_JURISDICTION']+$report_2[$PROGRAM_TITLE]['KARACHI_RESERVED_QUOTA']+$report_2[$PROGRAM_TITLE]['OTHER_PROVINCES_SELF_FINANCE']+$report_2[$PROGRAM_TITLE]['SELF_FINANCE']+$report_2[$PROGRAM_TITLE]['SUE_SON_DAUGHTER_QUOTA'];
				if($gender == "M") @$report_2[$PROGRAM_TITLE]['MALE']++;
				if($gender == "F") @$report_2[$PROGRAM_TITLE]['FEMALE']++;
				if(!isset($report_2[$PROGRAM_TITLE]['MALE'])) $report_2[$PROGRAM_TITLE]['MALE']='';
				if(!isset($report_2[$PROGRAM_TITLE]['FEMALE'])) $report_2[$PROGRAM_TITLE]['FEMALE']='';
			}
		}//foreach
			$out['HEADING']=$report;
			$out['DATA']=$report_2;

			echo json_encode($out);
			exit();
	}//method
	
	public function getSelectionListByListId(){

        if(isset($_POST['IS_PROVISIONAL'])&&isset($_POST['ADMISSION_LIST_ID'])){

            $ADMISSION_LIST_ID = $_POST['ADMISSION_LIST_ID'];
            $IS_PROVISIONAL = $_POST['IS_PROVISIONAL'];
            
            $result = $this->Selection_list_report_model->getSelectionListByListId($ADMISSION_LIST_ID,$IS_PROVISIONAL);
            echo json_encode($result);

        }else{
            echo "[]";
        }


    }
   
}
