<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Form extends CI_Controller
{
    private $SelfController = 'form';
    private $profile = 'candidate/profile';
    private $LoginController = 'login';
    private $SessionName = 'USER_LOGIN_FOR_ADMISSION';
    private $user ;
    private $file_size = 500;
   // private $notice = "<div class='text-danger'><h1>Dear Candidate, Thank you.<br>You've completed the initial part of the Online Admission Form. The remaining sections will become accessible once the Pre-Entry Test is conducted. We will notify you via email when this happens. Please remember to regularly monitor your Email Inbox and E-Portal Dashboard for any important updates.</h1></div>";
    private $notice = "<div class='text-danger'><h1>Admission form has been closed.</h1></div>";


	public function __construct()
	{
		parent::__construct();

        if(!$this->session->has_userdata($this->SessionName)){
            redirect(base_url().$this->LoginController);
            exit();
        }else{
            //   echo "<h4 style='color:red;text-align:center'>Under Maintenance Please visit after few hours</h4>";
                    // exit();
            $this->user = $this->session->userdata($this->SessionName);
        }
		$this->load->model('Administration');
		$this->load->model('log_model');
		$this->load->model("Admission_session_model");
		$this->load->model("Application_model");
		$this->load->model("User_model");
		$this->load->model("Prerequisite_model");
		$this->load->model("TestResult_model");
		$this->load->model("AdmitCard_model");
	}
    //added this method on 5-nov-2020
    public function index(){
	    redirect(base_url('form/dashboard'));
	}
	
	public function announcement ()
	{
	   // echo "<center><h1>Under Maintenance<br>
//      Please wait for a while</h1></center>";
	   // exit();
	   if($this->user['DISTRICT_ID']<=0){
            redirect(base_url()."Advertisement/select_district");
            exit();
        }
	   $program_type_id = 0;
	   if(isset($_POST['program_type_id'])){
	       $program_type_id=$_POST['program_type_id'];
	   }
		$admission_announcements = $this->Admission_session_model->get_form_admission_session ();
        $user = $this->user ;
		$data['user'] = $user;
		$data['profile_url'] = '';
            // if(158729==$user['USER_ID']){
//                 prePrint($admission_announcements);
//                exit();
            // }
		$data['admission_announcement'] = $admission_announcements;
		$data['program_type_id'] = $program_type_id;
        $data['user_application_list'] = $this->Application_model->getApplicationByUserId($user['USER_ID']);
         $data['valid_campus'] =  $this->Administration->getJurisdictionByDistrictId ($user['DISTRICT_ID']);
        
		$this->load->view('include/header',$data);

		$this->load->view('display_form_announcement',$data);

		$this->load->view('include/footer');
	}

	public function review_1($next_page)
	{

        $next_page =urldecode($next_page);
        $next_page = base64_decode($next_page);
        
        if($this->session->has_userdata('APPLICATION_ID')){
            
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);

            if ($application) {

                $bank = $this->Admission_session_model->getBankInformationByBranchId($application['BRANCH_ID']);
                //$bank = $this->Admission_session_model;
                $data['user'] = $user_fulldata['users_reg'];
                $data['qualifications'] = $user_fulldata['qualifications'];
                $data['guardian'] = $user_fulldata['guardian'];
                $data['next_page'] = $next_page;
                $data['application'] = $application;
                $data['bank'] = $bank;


                $this->load->view('include/header', $data);
//		$this->load->view('include/preloder');
//		$this->load->view('include/side_bar');
//		$this->load->view('include/nav',$data);
                $this->load->view('form_review', $data);
//		$this->load->view('include/footer_area');
                $this->load->view('include/footer');

            }else{
                echo "Application Id not found";
            }
        }else{
            echo "Application Id not found";
        }
//        if($this->session->has_userdata('APPLICATION_ID')) {
//            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
//
//            $user = $this->user;
//            $user_id = $user['USER_ID'];
//
//            $user_data = $this->User_model->getUserFullDetailById($user_id);
//            //prePrint($user_data);
//            $data['user'] = $user_data['users_reg'];
//            $data['qualifications'] = $user_data['qualifications'];
//            $data['guardian'] = $user_data['guardian'];
//            $data['next_page'] = $next_page;
//
//
//            $this->load->view('include/header', $data);
////		$this->load->view('include/preloder');
////		$this->load->view('include/side_bar');
////		$this->load->view('include/nav',$data);
//            $this->load->view('form_review', $data);
////		$this->load->view('include/footer_area');
//            $this->load->view('include/footer');
//        }else{
//            echo "Application Id not found";
//        }


	}

	public function application_list(){

        $user = $this->user ;
        $data['user'] = $user;
        $data['profile_url'] = '';


        $data['user_application_list'] = $this->Application_model->getApplicationByUserId($user['USER_ID']);
        $this->load->view('include/header',$data);
        //prePrint($data);
        $this->load->view('application_list',$data);

        $this->load->view('include/footer');
    }
   
    public function addApplication (){

        $user = $this->user ;
        $user_id = $user['USER_ID'];
	    $this->form_validation->set_rules('ADMISSION_SESSION_ID','form session','required');
        $this->form_validation->set_rules('CAMPUS_ID','campus','required');

        if ($this->form_validation->run())
        {
            $ADMISSION_SESSION_ID =  $this->input->post('ADMISSION_SESSION_ID');
            $admission = $this->Admission_session_model->getAdmissionSessionById($ADMISSION_SESSION_ID);


            if($admission){
                $end_date = $admission['ADMISSION_END_DATE'];
                $SESSION_ID = $admission['SESSION_ID'];
                $PROGRAM_TYPE_ID = $admission['PROGRAM_TYPE_ID'];
                $CAMPUS_ID = $admission['CAMPUS_ID'];
                $FORM_FEES = $admission['FORM_FEES'];

                    $datetime = date('Y-m-d');
                    if ($end_date >= $datetime) {

                        $result = $this->Application_model->getApplicationByUserIdAndAdmissionSessionId($user_id, $ADMISSION_SESSION_ID);
                        $list  = $this->Application_model->getApplicationByUserId($user_id);
                        // if($PROGRAM_TYPE_ID &&$SESSION_ID){
                            
                        // }
                        $bool  = false;
                        foreach($list as $value){
                            if($value['SESSION_ID']==$SESSION_ID&&$value['PROGRAM_TYPE_ID']==$PROGRAM_TYPE_ID){
                                $bool = true;
                                break;
                            }
                        }
                        if($bool){
                             echo "You have already applied in your desired campus";
                             exit();
                        }
                        //exit();
                        if (!$result) {
                            $user_data = $this->User_model->getUserById($user_id);
                             $user_data = array("users_reg"=>$user_data);
                             $form_fees['USER_DATA'] =$user_data;
                            $user_data = json_encode($user_data);
                            $datetime = date('Y-m-d H:i:s');
                            $form_array = array(
                                "USER_ID" => $user_id,
                                'ADMISSION_SESSION_ID' => $ADMISSION_SESSION_ID,
                                'FORM_DATE' => $datetime,
                                'STATUS_ID' => 1,
                                'IS_SUBMITTED' => 'N',
                                'FORM_DATA' => '',
                                'FORM_STATUS' => FORM_STATUS);
                                $form_fees['DUE_DATE'] = $end_date;
                                $form_fees['AMOUNT'] = $FORM_FEES;
                               // $form_fees['USER_DATA'] = $user_data[''];
                                // prePrint($form_fees['USER_DATA']);
                                // exit();
							//echo "heelo";
                            $is_add_application = $this->Application_model->addApplicationWithTransction($form_array,$form_fees,$PROGRAM_TYPE_ID);
							//exit("Qweqw");

                            if ($is_add_application) {
                                $APPLICATION_ID = $is_add_application;
                                $APPLICATION_ID = urlencode(base64_encode($APPLICATION_ID));
                                //setting session data for application
                                $this->session->set_userdata('APPLICATION_ID', $APPLICATION_ID);

                                $url = base_url() . "form/admission_form_challan";
                                $this->session->set_flashdata('OPEN_TAB', $url);
                                $this->set_application_id($APPLICATION_ID,urlencode(base64_encode('form/upload_application_challan')));
								exit();
                            }
                            else {
                                echo "form does not submit";
                            }

                        } else {
                            echo "Your application is already submitted";
                            //     redirect(base_url().'form/announcement');
                        }

                    } else {
                        echo "Date is Expired...!";
                        //   redirect(base_url().'form/announcement');
                    }


            }else{
                echo "Invalid Admission Session Id";
                //redirect(base_url().'form/announcement');
            }

        }
        else{
            redirect(base_url().'form/announcement');
        }
    }
    
    private function addApplication_closed (){

        $user = $this->user ;
        $user_id = $user['USER_ID'];
	    $this->form_validation->set_rules('ADMISSION_SESSION_ID','form session','required');
        $this->form_validation->set_rules('CAMPUS_ID','campus','required');

        if ($this->form_validation->run())
        {
            $ADMISSION_SESSION_ID =  $this->input->post('ADMISSION_SESSION_ID');
            $admission = $this->Admission_session_model->getAdmissionSessionById($ADMISSION_SESSION_ID);


            if($admission){
                $end_date = $admission['ADMISSION_END_DATE'];
                $SESSION_ID = $admission['SESSION_ID'];
                $PROGRAM_TYPE_ID = $admission['PROGRAM_TYPE_ID'];
                $CAMPUS_ID = $admission['CAMPUS_ID'];


                    $datetime = date('Y-m-d');
                    if ($end_date >= $datetime) {

                        $result = $this->Application_model->getApplicationByUserIdAndAdmissionSessionId($user_id, $ADMISSION_SESSION_ID);
                        $list  = $this->Application_model->getApplicationByUserId($user_id);
                        // if($PROGRAM_TYPE_ID &&$SESSION_ID){
                            
                        // }
                        $bool = false;
                        foreach($list as $value){
                            if($value['SESSION_ID']==$PROGRAM_TYPE_ID&&$value['PROGRAM_TYPE_ID']==$PROGRAM_TYPE_ID){
                                $bool = true;
                                break;
                            }
                        }
                        if($bool){
                             echo "You have already applied for your desired Campus";
                             exit();
                        }
                        //exit();
                        if (!$result) {
                            $user_data = $this->User_model->getUserFullDetailById($user_id);
                            $user_data = json_encode($user_data);
                            $datetime = date('Y-m-d H:i:s');
                            $form_array = array(
                                "USER_ID" => $user_id,
                                'ADMISSION_SESSION_ID' => $ADMISSION_SESSION_ID,
                                'FORM_DATE' => $datetime,
                                'STATUS_ID' => 1,
                                'IS_SUBMITTED' => 'N',
                                'FORM_DATA' => $user_data,
                                'FORM_STATUS' => FORM_STATUS);
                            $is_add_application = $this->Application_model->addApplication($form_array);


                            if ($is_add_application) {
                                $APPLICATION_ID = $is_add_application;
                                $form_array = array(
                                    "USER_ID" => $user_id,
                                    'ADMISSION_SESSION_ID' => $ADMISSION_SESSION_ID,
                                    'APPLICATION_ID' => $APPLICATION_ID,
                                    'FORM_FEE_ID' => $form_fees['FORM_FEE_ID'],
                                    'CHALLAN_AMOUNT' => $form_fees['AMOUNT']);
                                $is_add_challan = $this->Application_model->addChallan($form_array);


                                if ($is_add_challan) {

                                    $APPLICATION_ID = urlencode(base64_encode($APPLICATION_ID));
                                    //setting session data for application
                                    $this->session->set_userdata('APPLICATION_ID', $APPLICATION_ID);

                                    $url = base_url() . "form/admission_form_challan";
                                    $this->session->set_flashdata('OPEN_TAB', $url);
                                    $this->set_application_id($APPLICATION_ID,urlencode(base64_encode('candidate/profile')));


                                } else {
                                    echo "can not generate challan";
                                }


                            } else {
                                echo "Form is not submitted";
                            }

                        } else {
                            echo "Your application is already submitted";
                            //     redirect(base_url().'form/announcement');
                        }

                    } else {
                        echo "Date Expired...!";
                        //   redirect(base_url().'form/announcement');
                    }

            }else{
                echo "Invalid Admission Session Id";
                //redirect(base_url().'form/announcement');
            }

        }
        else{
            redirect(base_url().'form/announcement');
        }
    }

    public function admission_form_challan(){

        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID']=$APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);

            if($application){

                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'],'d-m-Y');

                    if ($application['ADMISSION_END_DATE']<date('Y-m-d'))
                    {
                        exit("Sorry your challan is expired..");
                    }
                        // prePrint($application);
                        // exit();
                       //$application['YEAR'] = 2021;
                    $row = array(
                        'CNIC_NO' => $user['CNIC_NO'],
                        'APPLICATION_ID' => $application['APPLICATION_ID'],
                        'CHALLAN_NO' => $application['FORM_CHALLAN_ID'],
                        "FIRST_NAME" => $user['FIRST_NAME'],
                        "CANDIDATE_SURNAME" => $user['LAST_NAME'],
                        "CANDIDATE_FNAME" => $user['FNAME'],
                        "CANDIDATE_NAME" => $user['FIRST_NAME'],
                        "TOTAL_AMOUNT" => $application['FORM_FEES'],
                        "CATEGORY_NAME" => "ADMISSIONS ".$application['YEAR'],
                        "VALID_UPTO" => $valid_upto,
                        "ACCOUNT_NO" => $application['ACCOUNT_NO'],
                        "ACCOUNT_TITLE" => $application['ACCOUNT_TITLE'],
                        "CANDIDATE_ID" => $user['USER_ID'],
                        "DEGREE_PROGRAM" => $application['PROGRAM_TITLE'],
                        "YEAR"=>$application['YEAR'],
                        'CAMPUS_NAME' => $application['NAME'],
                    );
			if($application['PROGRAM_TYPE_ID'] == 1){
             //$shift_id  = 2; 
             $programTitle="BACHELOR";
            }else if($application['PROGRAM_TYPE_ID'] == 2){
               // $shift_id  = 1;
                $programTitle="MASTER";
            }elseif($application['PROGRAM_TYPE_ID'] == 3){
                $programTitle="CERTIFICATE PROGRAM";
            }
            $challan_no = str_pad($application['FORM_CHALLAN_ID'], 7, '0', STR_PAD_LEFT);
                    $params = array (
	                'CHALLAN_NO'=>ADMP_CODE.$challan_no,
        			'SECTION_ACCOUNT_ID'=>20,
        			'REF_NO'=>$application['FORM_CHALLAN_ID'],
        			'ROLL_NO'=>null,
        			'BATCH_ID'=>null,
        			'DESCRIPTION'=>"ADMISSION PROCESSING FEES",
        			'AMOUNT'=>$application['FORM_FEES'],
        			'CHALLAN_DATE'=>date('Y-m-d'),
        			'CNIC_NO'=>$user['CNIC_NO'],
        			'NAME'=>$user['FIRST_NAME'],
        			'FNAME'=>$user['FNAME'],
        			'SURNAME'=>$user['LAST_NAME'],
        			'MOBILE_NO'=>"0".$user['MOBILE_NO'],
        			'EMAIL'=>$user['EMAIL'],
        			'PROGRAM'=>$programTitle,
        			'PROG_TYPE'=>$programTitle,
        			'DUE_DATE'=>$application['ADMISSION_END_DATE'],
        			'PROG_CODE'=>null,
        			'TYPE_CODE'=>'20-001'
    		);
    	
       
    	//	$transaction_flag = true;
                $response = postCURL(HBL_PAYMENT_URL,$params);
                // if(!($response['response_code']=="200")){
                //     prePrint($response);
                //   exit("API NOT WORKING PLEASE CONTACT IT SERVICES..!");
                // }
                    $data['row'] = $row;
                    $data['roll_no'] = $user['USER_ID'];
                    $this->load->view('admission_form_challan', $data);

            }else{
                echo "This application id is not associated with you.";
            }

        }else{
            echo "Application Id Not Found";
        }


    }

    public function upload_application_challan(){

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

            $data['user'] = $user['users_reg'];
            $data['qualifications'] = $user['qualifications'];
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['users_reg']['USER_ID'], $APPLICATION_ID);
            $bank_branches = $this->Admission_session_model->getAllBankInformation();
            if ($application) {
                if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/dashboard'));
                    exit();
                }

                    $category = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['users_reg']['USER_ID']);

                    $program_choice = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['users_reg']['USER_ID'],$APPLICATION_ID,$MORNING_SHIFT=1);

                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');

                    // if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
                    //     $error = "<div class='text-danger'>Form over due date</div>";
                    //   $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                    //             $this->session->set_flashdata('ALERT_MSG',$alert);
                    //             redirect(base_url()."form/dashboard");
                    //             exit();
                    // }


                    $data['profile_url'] = $this->profile;
                    $data['bank_branches'] = $bank_branches;
                    $data['application'] = $application;
                    $data['category'] = $category;
                    $data['program_choice'] = $program_choice;

                    $data['roll_no'] = $user['users_reg']['USER_ID'];
                    $this->load->view('include/header', $data);
                    $this->load->view('include/preloder');
                    $this->load->view('include/side_bar', $data);
                    $this->load->view('include/nav', $data);
                    $this->load->view('upload_challan_detail', $data);
                    $this->load->view('include/footer_area', $data);
                    $this->load->view('include/footer', $data);




            } else {
                
                echo "This application id is not associated with you.";
            }
        }
        else{
            echo "Application Id Not Found";
        }
    }

    public function challan_upload_handler(){

        $user = $this->user ;
        $USER_ID = $user['USER_ID'];
        $is_upload_any_doc = false;
        $config_a = array();
        $config_a['maintain_ratio'] = true;
        $config_a['width']         = 360;
        $config_a['height']       = 500;
        $config_a['resize']       = false;
        $error = "";
        $challan_image ="";
        $CHALLAN_AMOUNT =$BRANCH_ID = 0;
        $CHALLAN_PAID_DATE='0000-00-00';
        $APPLICATION_ID = 0;
       
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            if($this->session->has_userdata('APPLICATION_ID')) {
                $APPLICATION_ID_SESSION = $this->session->userdata('APPLICATION_ID');
            }else{
                $error.="<div class='text-danger'>Application Id not found in Session</div>";
            }
            if(isset($_POST['APPLICATION_ID'])&&isValidData($_POST['APPLICATION_ID'])){
                $APPLICATION_ID =isValidData($_POST['APPLICATION_ID']);
                if($APPLICATION_ID_SESSION!=$APPLICATION_ID){
                    $error.="<div class='text-danger'>Application Id Missmatch</div>";
                }
            }
            else{
                $error.="<div class='text-danger'>Application Id not found</div>";
            }
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);
            if($application) {
                $FORM_CHALLAN_ID = $application['FORM_CHALLAN_ID'];
                $challan_image = $application['CHALLAN_IMAGE'];
                $CHALLAN_PAID_DATE = $application['CHALLAN_DATE'];
                $CHALLAN_AMOUNT = $application['PAID_AMOUNT'];
                $BRANCH_ID = $application['BRANCH_ID'];
                if($application['IS_SUBMITTED']=="N"){

                }else{
                    $error.="<div class='text-danger'>Your Form has been Submitted thats why you can't change the challan information</div>";
                }
                   // $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');



                    if (($application['ADMISSION_END_DATE'] < date('Y-m-d'))&&false) {

                        $error.="<div class='text-danger'>Sorry your challan is expired..</div>";
                    }else {
                        $folder = EXTRA_IMAGE_CHECK_PATH . "$USER_ID";
                        // echo $folder;
                        // exit();
                        if (!is_dir($folder)) {
                            //exit("hello");
                            mkdir(EXTRA_IMAGE_CHECK_PATH . "/$USER_ID");
                        }
                       // exit('hello');

                        if(isset($_POST['BRANCH_ID'])&&isValidData($_POST['BRANCH_ID'])){
                            $BRANCH_ID =isValidData($_POST['BRANCH_ID']);
                        }else{
                            $error.="<div class='text-danger'>Bank Branch must be select</div>";
                        }
                        if(isset($_POST['CHALLAN_AMOUNT'])&&isValidData($_POST['CHALLAN_AMOUNT'])){
                            $CHALLAN_AMOUNT =isValidData($_POST['CHALLAN_AMOUNT']);
                            if($CHALLAN_AMOUNT != $application['CHALLAN_AMOUNT']){
                                $error.="<div class='text-danger'>Your entered amount does not match actual challan amount </div>";
                            }

                        }else{
                            $error.="<div class='text-danger'>Challan Amount Must be Enter</div>";
                        }
                        if(isset($_POST['CHALLAN_NO'])&&isValidData($_POST['CHALLAN_NO'])){
                            $CHALLAN_NO =isValidData($_POST['CHALLAN_NO']);
                            if($CHALLAN_NO!=$FORM_CHALLAN_ID){
                               // $error.="<div class='text-danger'>Invalid Challan No..!</div>";
                            }
                        }else{
                            $error.="<div class='text-danger'>Challan Number Must be Enter</div>";
                        }

                        if(isset($_POST['CHALLAN_PAID_DATE'])&&isValidTimeDate($_POST['CHALLAN_PAID_DATE'],'d/m/Y')){
                            $CHALLAN_PAID_DATE = getDateForDatabase($_POST['CHALLAN_PAID_DATE']);
                            if($CHALLAN_PAID_DATE>date('Y-m-d')){
                                $error.="<div class='text-danger'>Choose Valid Challan Paid Date</div>";
                            }
                        }else{
                            $error.="<div class='text-danger'>Challan Paid Date Must be Choose</div>";
                        }


                        if (isset($_FILES['challan_image'])) {
                            if (isValidData($_FILES['challan_image']['name'])) {

                                $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                                $image_name = "challan_image_$FORM_CHALLAN_ID"."_"."$USER_ID";
                                //echo "$file_path";
                                $res = $this->upload_image('challan_image', $image_name, $this->file_size, $file_path, $config_a);
                                if ($res['STATUS'] === true) {
                                    $challan_image = "$USER_ID/" . $res['IMAGE_NAME'];
                                    $is_upload_any_doc = true;

                                } else {
                                    $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                                }
                            } else {
                                if ($challan_image == "")
                                    $error .= "<div class='text-danger'>Must Upload Challan Image and image size must be less then {$this->file_size}kb </div>";
                            }
                        }
                        else {

                            if ($challan_image == "")
                                $error .= "<div class='text-danger'>Must Upload Challan Image and image size must be less then {$this->file_size}kb </div>";
                        }

                        if($error==""){
                            $form_data=array("BRANCH_ID"=>$BRANCH_ID,
                                "CHALLAN_DATE"=>$CHALLAN_PAID_DATE,
                                "PAID_AMOUNT"=>$CHALLAN_AMOUNT,
                                "CHALLAN_IMAGE"=>$challan_image,
                                "PAID"=>"N",
                                "USER_ID"=>$USER_ID);
                            $res = $this->Application_model->updateChallanById($FORM_CHALLAN_ID,$form_data);
                            if($res==1){

                                $APPLICATION_ID = base64_encode($APPLICATION_ID);
                                $APPLICATION_ID = urlencode($APPLICATION_ID);
                                $success= "<div class='text-success'>Challan Information Update Successfully</div>";
                                $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                if($_POST['action']=="next"){
                                    redirect(base_url()."candidate/profile");
                                }else{
                                  redirect(base_url()."form/upload_application_challan");  
                                }
                                

                            }else if($res==0){

                                $APPLICATION_ID = base64_encode($APPLICATION_ID);
                                $APPLICATION_ID = urlencode($APPLICATION_ID);
                                if($is_upload_any_doc){
                                    $success= "<div class='text-success'>Challan Information Update Successfully</div>";
                                }else{
                                    $success= "<div class='text-success'>No data has been changed...! </div>";
                                    $success= "<div class='text-success'>Challan Information Update Successfully...!</div>";
                                }

                                $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                if($_POST['action']=="next"){
                                    redirect(base_url()."candidate/profile");
                                }else{
                                  redirect(base_url()."form/upload_application_challan");  
                                }

                            }else{

                                $APPLICATION_ID = base64_encode($APPLICATION_ID);
                                $APPLICATION_ID = urlencode($APPLICATION_ID);
                                $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url()."form/upload_application_challan");

                            }
                        }
                    }


            }else{
                    $error.="<div class='text-danger'>This Application is not associate with you</div>";
            }

        }
        else{
            $error.="<div class='text-danger'>Invalid Request</div>";
        }
        if($error!=""){
            $APPLICATION_ID = base64_encode($APPLICATION_ID);
            $APPLICATION_ID = urlencode($APPLICATION_ID);
            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/upload_application_challan");
        }


    }

    public function set_application_id($APPLICATION_ID,$url){
        
        
        $APPLICATION_ID = base64_decode(urldecode($APPLICATION_ID));
         
        $user_id = $this->user['USER_ID'];
        $user = $this->User_model->getUserById($user_id);
       
        if(( empty($user['PROFILE_IMAGE']) || $user['PROFILE_IMAGE'] == '' || $user['PROFILE_IMAGE'] == null) && $user['REMARKS'] != "NEW_ADMISSION"){
            
           
            $FORM_STATUS = json_decode(FORM_STATUS,true);
            //$FORM_STATUS = json_decode($FORM_STATUS,true);

            if(is_array($FORM_STATUS) && isset($FORM_STATUS['PROFILE_PHOTO'])&& isset($FORM_STATUS['PROFILE_PHOTO']['STATUS'])){
                // echo "YES";
                // exit();
        //           if($user_id == 75123){
        //  prePrint($user);
        //  exit();
        // }
                $FORM_STATUS['PROFILE_PHOTO']['STATUS'] = RE_UPLOAD;
            }
            $FORM_STATUS = json_encode($FORM_STATUS);
            $formArray = array(
                "USER_ID"=>$user_id,
                "FORM_STATUS"=>$FORM_STATUS
            );
                     
            $res =$this->Application_model->updateApplicationById($APPLICATION_ID,$formArray);
          
        }
          $formArray = array('APPLICATION_ID'=>$APPLICATION_ID);
            $res = $this->User_model->updateUserById($user_id,$formArray);
            //   prePrint($res);
            //   prePrint($formArray);
            //   prePrint($user_id);
            //   exit();
	    $this->session->set_userdata('APPLICATION_ID', $APPLICATION_ID);
        $url = base_url() . base64_decode(urldecode($url));
        redirect($url);
        exit();
    }

    public function lock_form(){
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailById($user['USER_ID']);

            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);


           

                //prePrint($application);
               if ($application) {
                
                 if($application['ADMISSION_END_DATE']<date('Y-m-d')){
                    $error = "<div class='text-danger'> You can not submit form due date over</div>";
                       $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url()."form/dashboard");
                                exit();
                }
                
                if($application['IS_SUBMITTED']=='N') {


                    $error = $this->isValidProfileInformation($user_fulldata, $application);


                    //prePrint($error);
                    if ($error == "") {
                        if ($application['PAID'] == 'N' && isValidData($application['CHALLAN_IMAGE'])) {

                            $this->Application_model->lock_form($APPLICATION_ID, $user_fulldata);
                            $success = "Your form has been successfully submited...!";
                            $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                             $dashboard = base64_encode("dashboard");
                                $dashboard = urlencode($dashboard);
                            redirect(base_url()."form/review/$dashboard");

                        } else {
                            $error .= "<div class='text-danger'>Challan image not found</div>";
                        }


                    } else {
                        $alert = array('MSG' => $error, 'TYPE' => 'ERROR');
                        $this->session->set_flashdata('ALERT_MSG', $alert);
                        redirect(base_url() . "form/upload_application_challan");
                        //prePrint($error);
                    }
                }else{
                    $success =  "Your form is already submitted...!";
                    $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                     $dashboard = base64_encode("dashboard");
                                $dashboard = urlencode($dashboard);
                    redirect(base_url()."form/review/$dashboard");

                }


            }
            else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }


           
        }
        else {
            redirect(base_url() . "login");
        }
    }

    public function check_validation_and_challan(){
        
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailById($user['USER_ID']);

            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);

            if ($application) {

                //prePrint($application);

                $error = $this->isValidProfileInformation($user_fulldata,$application);


                //prePrint($error);
                if($error==""){
                    if($application['PAID']=='N'&&isValidData($application['CHALLAN_IMAGE'])){
                        $next_page = "lock_form";
                        $next_page = base64_encode($next_page);
                        $next_page =urlencode($next_page);

                        redirect(base_url() . "form/review/$next_page");
                    }else{
                        $error.="<div class='text-danger'>Please select Bank Branch and must save it</div>";
                        $error.="<div class='text-danger'>Please upload image of Paid Challan and then press SAVE button to save.</div>";
                        $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                        redirect(base_url()."form/upload_application_challan");
                    }


                }else{
                    $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/upload_application_challan");
                    //prePrint($error);
                }


            }else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }
        }else {
            redirect(base_url() . "login");
        }
    }

    public function check_validation(){
        $this->block_for_test();
            if($this->session->has_userdata('APPLICATION_ID')){
                $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
                $user = $this->session->userdata($this->SessionName);
                //prePrint($user);
                $user_fulldata = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                $data['user'] = $user_fulldata;
                $data['APPLICATION_ID'] = $APPLICATION_ID;
                $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);

                if ($application) {

                    //prePrint($application);

                    $error = $this->isValidProfileInformation($user_fulldata,$application);


//                   prePrint($error);
//                   exit();
                   if($error==""){
                       $next_page = "select_subject";

                       $next_page = base64_encode($next_page);
                       $next_page =urlencode($next_page);
                       redirect(base_url() . "form/review/$next_page");
                       //redirect(base_url() . "form/");

                   }else{
                       $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                       $this->session->set_flashdata('ALERT_MSG',$alert);
                       redirect(base_url()."candidate/profile");
                      // prePrint($error);
                   }


                }
            }else {
                redirect(base_url() . "login");
            }
    }

    public function dashboard(){
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);
            // prePrint($user_fulldata);
            // exit();
            $data['profile_url'] = $this->profile;
            $data['user'] = $user_fulldata['users_reg'];
            $data['qualifications'] = $user_fulldata['qualifications'];
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            //$application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            $data['user_application_list'] = $this->Application_model->getApplicationByUserId($user['USER_ID']);

            if (count($data['user_application_list'])>0) {
               
//                $error = $this->isValidProfileInformation($user_fulldata,$application);
//               if($error==""){
//                   $data['basic_profile'] = 100;
//               }else{
//                   substr_count($error, '<div>', 3);
//               }
                    //prePrint($application);
                $data['IS_SUPER_PASSWORD_LOGIN'] = $this->user['IS_SUPER_PASSWORD_LOGIN'];
                   // $data['this'] = $this;
                   // if(isset($_GET['des']))
                    //prePrint("YES");
                $this->load->view('include/header',$data);
                $this->load->view('include/preloder');
                $this->load->view('include/side_bar',$data);
                $this->load->view('include/nav',$data);
                $this->load->view('dashboard',$data);
                $this->load->view('include/footer_area',$data);
                $this->load->view('include/footer',$data);

            }else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }
        }
        else {
            redirect(base_url() . "login");
        }
    }
    
       //UPDATED FUNCTION ON 18-OCT-2020
    public function upload_minor_subjects(){
        $this->block_for_test();
        $error = "";
        $success = true;
        $success_msg = "";
        $user = $this->session->userdata($this->SessionName);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            if ($this->session->has_userdata('APPLICATION_ID')) {
                $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            } else {
                $error .= "<div class='text-danger'>Application Id not found in Session</div>";
            }
            if(isset($_POST['DISCIPLINE_ID'])&&isValidData($_POST['DISCIPLINE_ID'])){
                $DISCIPLINE_ID =isValidData($_POST['DISCIPLINE_ID']);
            }else{
                $error.="<div class='text-danger'>Discipline Id Not found</div>";
            }
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
             if($application['STATUS_ID']<2){
                //redirect(base_url('Candidate'));
            }
            if(isset($_POST['minor_subject_array'])&&is_array($_POST['minor_subject_array'])&&count($_POST['minor_subject_array'])>0&&$error==""){

                $minor_subject_array = $_POST['minor_subject_array'];
                // $delete_result = $this->Application_model->deleteApplicantsMinorsByUserIdAndDisciplineId($user['USER_ID'],$DISCIPLINE_ID);
                // if($delete_result>0) {
                    $applicants_minnor_list = array();
                    foreach ($minor_subject_array as $MINOR_MAPPING_ID) {

                        //$is_exist = $this->Application_model->getApplicantsMinorsByUserIdAndMinorMappingId($user['USER_ID'],$MINOR_MAPPING_ID);

                        $applicants_minnor = array(
                            "APPLICATION_ID" => $APPLICATION_ID,
                            "DISCIPLINE_ID" => $DISCIPLINE_ID,
                            "MINOR_MAPPING_ID" => $MINOR_MAPPING_ID,
                            "USER_ID" => $user['USER_ID'],
                            "ACTIVE" => 1
                        );
                        $applicants_minnor_list[] = $applicants_minnor;


                    }
                  
                    if(count($applicants_minnor_list)>0){
                        $is_add = $this->Application_model->addApplicantsMinorsBatch($applicants_minnor_list);
                        if ($is_add) {
                            $success_msg .= "<div class='text-success'>Successfully added </div>";
                            //success add
                        } else {
                            $success = false;
                            $error .= "<div class='text-danger'>Something went wrong in minor id </div>";
                            // something went wrong
                        }
                    }else{
                        $success = false;
                        $error .= "<div class='text-danger'>Something went wrong in minor id </div>";

                    }

                // }
                // else{
                //     $error .= "<div class='text-danger'>Something went wrong delete previous minor</div>";
                // }


            }
            else{
                $error .= "<div class='text-danger'>Must select at least one subject </div>";
            }

        }
        else{
            $error .= "<div class='text-danger'>Invalid request upload_minor_subjects</div>";

        }
        if($error){
            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/select_subject");
        }
        else{
          //  $success_msg .= "<div class='text-success'>your subject add successfully</div>";
            $alert = array('MSG'=>$success_msg,'TYPE'=>'SUCCESS');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            if(isset($_POST['IS_NEXT'])&&$_POST['IS_NEXT']==1){

                redirect(base_url()."form/select_category");
            }else{
                redirect(base_url()."form/select_subject");
            }

        }


    }

    //UPDATED FUNCTION ON 18-OCT-2020 VIEW FILE select_minor_subject.php
    public function select_subject(){
        $this->block_for_test();
        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
             if($application['STATUS_ID']>FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/dashboard'));
            }
            if ($application) {
            
            //form close from bachelor
                $this->close_registration_for_bachelor($application);
                
                
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6)),
                    'OTC'=>array('PROGRAM_TYPE_ID'=>3,'DEGREE_ID'=>3)
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['OTC']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['OTC']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);


// prePrint($degree_list['MASTER']['DEGREE_ID']);
                        
//                         prePrint($form_data['qualifications']);
//                         exit();
                    foreach ($form_data['qualifications'] as $k=>$qualification){
                                $check = false;
                               
                                
                                foreach($degree_list['MASTER']['DEGREE_ID'] as $degree_id){
                                    if($degree_id==$qualification['DEGREE_ID']){
                                       $check = true;
                                       break;
                                    }
                                }
                        if($check){
                                $bool  = true;
                                if($k==0){
                                    $valid_qualification = $qualification;
                                }
                            
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }

                            //break;
                        }
                        //  prePrint("K".$k);
                        //         prePrint($qualification);
                        //          prePrint("CEHL".$check);
                        //         prePrint($valid_qualification);
                    }
                }

// exit();

//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }



                    $data['profile_url'] =  $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){

                        $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);
                        
                        if($result!=null && count($result)==1){
                            //prePrint($result);
                            $result =$result[0];
                           
                            $is_exist = $this->Application_model->getApplicantsMinorsByApplicationIdAndMinorMappingId($APPLICATION_ID,$result['MINOR_MAPPING_ID']);
                            if(count($is_exist)==0) {
                                $applicants_minnor = array(
                                    "APPLICATION_ID" => $APPLICATION_ID,
                                    "DISCIPLINE_ID" => $result['DISCIPLINE_ID'],
                                    "MINOR_MAPPING_ID" => $result['MINOR_MAPPING_ID'],
                                    "USER_ID" => $user['USER_ID'],
                                    "ACTIVE" => 1
                                );
                                $is_add = $this->Application_model->addApplicantsMinors($applicants_minnor);
                                if ($is_add) {
                                  //  echo "Minor Automatic Added";
                                    $error = "<div class='text-danger'>Minor Automatic Added</div>";
                                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                                    //$this->session->set_flashdata('ALERT_MSG',$alert);
                                    redirect(base_url('form/select_category'));

                                } else{
                                  //  echo "ByDefault Minor Not added";
                                    $error = "<div class='text-danger'> ByDefault Minor Not added</div>";
                                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                                    $this->session->set_flashdata('ALERT_MSG',$alert);
                                    redirect(base_url('form/select_category'));

                                }

                            }else{

//                                $error = "<div class='text-danger'> Already selected minors</div>";
//                                $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
//                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url('form/select_category'));

                            }

                        }
                        else if($result!=null && count($result)>1){
                            $data['minors'] = $result;
                            $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];
                            /***********DANGER**************
                            WE NEED TO UPDATE APPLICATION ID INSTEAD OF USER ID IN 
                             $data['applicantsMinors'] = $this->Application_model->getApplicantsMinorsByUserIdAndDisciplineID($user['USER_ID'],$valid_qualification['DISCIPLINE_ID']);
                            ***********DANGER**************/
                            /***********UPDATED**************/
                            $data['applicantsMinors'] = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                            $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                            // $data['roll_no'] = $user['USER_ID'];
                            $this->load->view('include/header', $data);
                            $this->load->view('include/preloder');
                            $this->load->view('include/side_bar', $data);
                            $this->load->view('include/nav', $data);
                            $this->load->view('select_minor_subject', $data);
                            $this->load->view('include/footer_area', $data);
                            $this->load->view('include/footer', $data);
                        }else{
                            echo "minors not found";
                        }
                    }
                    else{

                        $error = "<div class='text-danger'>Please must add appropriate degree</div>";
                        $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                        redirect(base_url('candidate/add_inter_qualification'));
                    }
                    // prePrint($application);





            } else {
                echo "this application id is not associate with you";
                redirect(base_url('form/dashboard'));
            }
        }else{
            echo "Application Id Not Found";
            redirect(base_url('form/dashboard'));
        }
    }

    //UPDATED FUNCTION ON 18-OCT-2020 VIEW FILE select_program.php
    public function select_program(){
    $this->block_for_test();
        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            
            // prePrint($application);
            // if($application['PROGRAM_TYPE_ID'] == 1){
                 //redirect(base_url('form/add_evening_category'));
                 //exit();
            // }
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
             if($application['STATUS_ID']>FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/dashboard'));
            }
            if ($application) {
                // if($application['PROGRAM_TYPE_ID']==2){
                // redirect(base_url('form/add_evening_category'));    
                // }
                //form close from bachelor
                $this->close_registration_for_bachelor($application);
                
                
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                   $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6)),
                    'OTC'=>array('PROGRAM_TYPE_ID'=>3,'DEGREE_ID'=>3)
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['OTC']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['OTC']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);



                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }

                            //break;
                        }
                    }
                }



//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


                    $data['profile_url'] = $this->profile;

                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);



                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor)
                        {
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }
        
                        if(count($minorMappingIds)==0){
                           echo "Please Must select Minor Subject";
                            $error = "<div class='text-danger'> Please Must select Minor Subject</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_subject'));
                            exit();
                        }
                        $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
                        if(count($list_of_categoy)==0){
                            echo "Please must Save Category";
                            $error = "<div class='text-danger'> Please must Save your category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_category'));
                            exit();
                        }
                        $is_valid = 0;
                          foreach($list_of_categoy as $cat_obj){
						        if($cat_obj['FORM_CATEGORY_ID']==7){
						          $is_valid = 1;  
						        }
						        
						    }
                       $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);

                        $prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (1,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);
                        
            //             if(158729==$user['USER_ID']){
//                 prePrint($valid_program_list);
//                 prePrint($prog_list_by_shift);
//                 exit();
//            // }
                        $valid_exact_program = array();
                        foreach($prog_list_by_shift as $prog_list){
                            foreach ($valid_program_list as $valid_program){
                                if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
                                    $valid_exact_program[]=$valid_program;
                                }
                            }
                        }
                        
                        $CHOOSEN_PROGRAM_LIST = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['USER_ID'],$APPLICATION_ID,$MORNING_SHIFT=1);
                        $lat_info = $this->Application_model->getLatInfoByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);
                        
                        $program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID'],$MORNING_SHIFT=1);
                
                
                        //prePrint($prog_list_by_shift);
                        //prePrint($program_list);
                
                        //exit();
                
                        $program_list       = $prog_list_by_shift; 
                        
                         
                         $CHOOSEN_PROGRAM_LIST_NEW = array();
                        foreach($CHOOSEN_PROGRAM_LIST as $cho){
                            if($cho['IS_SPECIAL_CHOICE']=='N'){
                                array_push($CHOOSEN_PROGRAM_LIST_NEW,$cho);
                            }
                        }
                        $data['VALID_PROGRAM_LIST'] =$valid_exact_program;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST_NEW;
                        $data['lat_info'] =$lat_info;
                      //  $data['user'] =$form_data;
                        $data['form_data'] =$form_data;
                        $data['application'] =$application;
                        $data['category'] =$list_of_categoy;
                        $data['is_evening_category']=$is_valid;
                        
                        $precentage = ($valid_qualification['OBTAINED_MARKS']*100/$valid_qualification['TOTAL_MARKS']);
                        $data['precentage'] =round($precentage,2);

                         
                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('select_program', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                        redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);




            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    //UPDATED FUNCTION ON 30-OCT-2020
    public function upload_program_handler(){
         $this->block_for_test();
        $error="";
        $config_a = array();
        $config_a['maintain_ratio'] = true;
        $config_a['width']         = 360;
        $config_a['height']       = 500;
        $config_a['resize']       = false;
        $reponse['RESPONSE'] = "ERROR";


        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            if ($application) {
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

               
                   $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6)),
                    'OTC'=>array('PROGRAM_TYPE_ID'=>3,'DEGREE_ID'=>3)
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else  if($application['PROGRAM_TYPE_ID']==$degree_list['OTC']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['OTC']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);



                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }

                            //break;
                        }
                    }
                }




//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


                    $data['profile_url'] = $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);



                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor)
                        {
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }

                        if(count($minorMappingIds)==0){
                            $error .= "<div class='text-danger'>Minor/Elective subject(s) not found</div>";
                        }

                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);
                        
                         $prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (1,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);

                        $valid_exact_program = array();
                        foreach($prog_list_by_shift as $prog_list){
                            foreach ($valid_program_list as $valid_program){
                                if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
                                    $valid_exact_program[]=$valid_program;
                                }
                            }
                        }
                        $valid_program_list = $valid_exact_program;
                        
                        //$program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        //prePrint($valid_program_list);
                        $min_choice = 0;
                        $max_choice = 0;
                        $choice_list= array();
                        $llb_validation = false;
                        if(isset($_POST['minor_subject_array'])){
                            $choice_list = $_POST['minor_subject_array'];

                        }else{
                            if($application['PROGRAM_TYPE_ID'] == 1){
                                $error .= "<div class='text-danger'>Program not Found</div>";
                            }
                        }

                        if($application['PROGRAM_TYPE_ID'] == 1){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            if(in_array(LLB_PROG_LIST_ID,$choice_list)||in_array(286,$choice_list)){
                                $min_choice = 1;
                                $llb_validation = true;
                            }


                        }else if($application['PROGRAM_TYPE_ID'] == 2){
                            $max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
                        }
                        else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                        }
                        if($llb_validation){


                            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
                            } else {
                                $error .= "<div class='text-danger'>You must enter Ticket Number / Seat Number</div>";
                            }

                            if(isset($_POST['TEST_DATE'])&&isValidTimeDate($_POST['TEST_DATE'],'d/m/Y')){
                                $TEST_DATE = getDateForDatabase($_POST['TEST_DATE']);
                                if($TEST_DATE>date('Y-m-d')){
                                    $error.="<div class='text-danger'>Choose Valid Test Date</div>";
                                }
                            }else{
                                $error.="<div class='text-danger'>Test Must be Choose</div>";
                            }

                            if (isset($_POST['TEST_SCORE']) && isValidData($_POST['TEST_SCORE'])) {
                                $TEST_SCORE = strtoupper(isValidData($_POST['TEST_SCORE']));
                                if($TEST_SCORE<0||$TEST_SCORE>100){
                                    $error .= "<div class='text-danger'>Invalid Test Score</div>";
                                }
                            } else {
                                $error .= "<div class='text-danger'>You must enter Test Score</div>";
                            }

                            $result_card_image= "";

                            if (isset($_POST['result_card_image1']) && isValidData($_POST['result_card_image1'])) {
                                $result_card_image = strtoupper(isValidData($_POST['result_card_image1']));
                            }

                            $user_id = $user['USER_ID'];
                            if (isset($_FILES['result_card_image'])) {
                                if (isValidData($_FILES['result_card_image']['name'])) {

                                    $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                                    $image_name = "lat_result_card_image_$user_id";
                                    $res = $this->upload_image('result_card_image', $image_name, $this->file_size, $file_path, $config_a);
                                    if ($res['STATUS'] === true) {
                                        $result_card_image = "$user_id/" . $res['IMAGE_NAME'];
                                        $is_upload_any_doc = true;

                                    } else {
                                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                                    }
                                } else {
                                    if ($result_card_image == "")
                                        $error .= "<div class='text-danger'>You have to upload the image of your Result Card and image size should not be more than 500kb </div>";
                                }
                            }
                            else {

                                if ($result_card_image == "")
                                    $error .= "<div class='text-danger'>You have to upload the image of your Result Card and image size should not be more than 500kb </div>";
                            }
                        }

                        if($error==""){
                            if($llb_validation){
                                $lat_info = array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,"TOKEN_NO"=>$TOKEN_NO,"TEST_DATE"=>$TEST_DATE,"TEST_SCORE"=>$TEST_SCORE,"RESULT_IMAGE"=>$result_card_image);
                            }else{
                                $lat_info = null;
                            }

                            if(count($choice_list)>=0&&count($choice_list)<=$max_choice&&$min_choice<=count($choice_list)){

                                $check_valid = true;
                                foreach($choice_list as $choice){
                                    $check_id_valid = false;
                                    foreach ($valid_program_list as $valid_program){

                                        if($choice==$valid_program['PROG_LIST_ID']){
                                            $check_id_valid = true;
                                            break;
                                        }
                                    }
                                    if($check_id_valid ==false){
                                        $check_valid = false;
                                        break;
                                    }
                                }

                                if($check_valid==true){

                                    $list_of_choice = array();

                                    foreach($choice_list as $CHOICE_NO => $PROG_LIST_ID){
                                        $CHOICE_NO++;
                                        $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>$PROG_LIST_ID,'CHOICE_NO'=>$CHOICE_NO,'SHIFT_ID'=>1);
                                    }
                                    if(count($list_of_choice)==0){
                                         $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'SHIFT_ID'=>1);
                                    }
                                    //prePrint($list_of_choice);
                                    if(count($list_of_choice)>=0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info)){
                                            
                                    }
                                    else{
                                        $error .= "<div class='text-danger'>Your choices are not added or updated. Kindly contact technical team.</div>";
                                    }

                                }
                                else{
                                    $error .= "<div class='text-danger'>Your choices are invalid. If you are facing any technical issue OR need any assistance, please contact technical team.</div>";
                                }


                            }else{
                                 if($application['PROGRAM_TYPE_ID'] == 1){
                                    $error .= "<div class='text-danger'>You must select minimum $min_choice and maximum $max_choice choices</div>";
                                     
                                 }else{
                                       $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
                                        $is_valid = 0;
                                          foreach($list_of_categoy as $cat_obj){
                						        if($cat_obj['FORM_CATEGORY_ID']==7){
                						          $is_valid = 1;  
                						        }
                						        
                						    }
                						 if($is_valid==0){
                						     $error .= "<div class='text-danger'>You must select minimum 1 and maximum $max_choice choices</div>";
                						 }
                                 }
                            }
                        }


                    if($error==""){
                        $reponse['RESPONSE']="SUCCESS";
                        $reponse['MESSAGE']="Information Successfully Updated";
                    }else{
                        $reponse['RESPONSE']="ERROR";
                        $reponse['MESSAGE']=$error;
                    }



                    }else{
                          redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree - Please add/select appropriate degree";
                        $error .= "<div class='text-danger'>Invalid Degree - Please add/select appropriate degree</div>";
                        exit();
                    }
                    // prePrint($application);






            }
            else {
                echo "This Application Id is not associated with you";
                $error .= "<div class='text-danger'>This application id is not associated with you</div>";
                exit();
            }
        }
        else{
            echo "Application Id Not Found";
            $error .= "<div class='text-danger'>Application Id Not Found</div>";
            exit();
        }

        if($error!=""){
            $reponse['RESPONSE']="ERROR";
            $reponse['MESSAGE']=$error;

        }

        if ($reponse['RESPONSE'] == "ERROR") {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        } else {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }

    //UPDATED FUNCTION ON 18-OCT-2020 VIEW FILE select_category.php
    public function select_category(){
        $this->block_for_test();
        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
             // prePrint($application);
                // if($application['PROGRAM_TYPE_ID'] == 1){
                //     redirect(base_url('form/add_evening_category'));
                //     exit();
                // }
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
             if($application['STATUS_ID']>FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/dashboard'));
            }
            if ($application) {
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                  $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6)),
                    'OTC'=>array('PROGRAM_TYPE_ID'=>3,'DEGREE_ID'=>3)
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['OTC']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['OTC']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);



                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }

                            //break;
                        }
                    }
                }


//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


                    $data['profile_url'] =$this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);



                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor)
                        {
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }

                        if(count($minorMappingIds)==0){
                            echo "Please Must select Minor Subject";
                            $error = "<div class='text-danger'> Please Must select Minor Subject</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_subject'));
                        }

                        $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
                         $CHOOSEN_PROGRAM_LIST = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['USER_ID'],$APPLICATION_ID,$MORNING_SHIFT=1);
                     

                        $data['list_of_category'] =$list_of_categoy;
                         $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST;
                        //$data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['application'] =$application;
                        $data['form_data'] =$form_data;
                        //     prePrint($valid_program_list);
                        // prePrint($list_of_categoy);
				      //exit();
                        // $data['roll_no'] = $user['USER_ID'];
                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('select_category', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                          redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree - Please select/add appropriate degree";
                    }
                    // prePrint($application);





            } else {
                echo "This application id is not associated with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    //UPDATED FUNCTION ON 22-OCT-2020 MODAL FILE Application_model.php
    public function select_category_handler(){
         $this->block_for_test();
        if($this->session->has_userdata('APPLICATION_ID')) {

            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user_id = $user['USER_ID'];
            $user = $this->User_model->getUserById($user['USER_ID']);
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }

            $is_upload_any_doc = false;
            $config_a = array();
            $config_a['maintain_ratio'] = true;
            $config_a['width']         = 360;
            $config_a['height']       = 500;
            $config_a['resize']       = false;

            $error = "";

            $GENERAL_MERIT_ID=1;
            $SELF_FINANCE_ID=2;
            $SU_EMPLOYEE_QUOTA_ID=3;
            $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ID=4;
            $DISABLED_PERSON_QUOTA_ID=5;
            $HQ_QUOTA_ID=9;
            $SPORTS_QUOTA_ID=6;
            $EVENING_PROGRAM_ID=7;

            $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);

            $data = array();

            $GENERAL_MERIT=array('USER_ID'=>$user_id,'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$GENERAL_MERIT_ID,'CATEGORY_INFO'=>'');

            $data[] = $GENERAL_MERIT;


            if (isset($_POST['SELF_FINANCE'])) {
                $SELF_FINANCE=array('USER_ID'=>$user_id,'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$SELF_FINANCE_ID,'CATEGORY_INFO'=>'');
                $data[] = $SELF_FINANCE;
            }
            if (isset($_POST['EVENING_PROGRAM'])) {
                $EVENING_PROGRAM=array('USER_ID'=>$user_id,'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$EVENING_PROGRAM_ID,'CATEGORY_INFO'=>'');
                $data[] = $EVENING_PROGRAM;
            }else{
                
									    $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'SHIFT_ID'=>EVENING_SHIFT_ID);
									
								$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice);

            }


            if (isset($_POST['SU_EMPLOYEE_QUOTA'])) {

                $SU_EMPLOYEE_QUOTA_ROW = null;

                foreach ($list_of_categoy as $categoy_row){

                    if($categoy_row['FORM_CATEGORY_ID']==$SU_EMPLOYEE_QUOTA_ID){

                        $SU_EMPLOYEE_QUOTA_ROW = $categoy_row;
                        break;
                    }
                }

                if($SU_EMPLOYEE_QUOTA_ROW){
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO= json_decode($SU_EMPLOYEE_QUOTA_ROW['CATEGORY_INFO'],true);
                    }

                $SU_EMPLOYEE_QUOTA=array('USER_ID'=>$user_id,'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$SU_EMPLOYEE_QUOTA_ID,'CATEGORY_INFO'=>'');

                if (isset($_POST['EMPLOYEE_NAME']) && isValidData($_POST['EMPLOYEE_NAME'])) {
                    $EMPLOYEE_NAME = strtoupper(isValidData($_POST['EMPLOYEE_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Complete Employee Name is required</div>";
                }
                if (isset($_POST['DESIGNATION']) && isValidData($_POST['DESIGNATION'])) {
                    $DESIGNATION = strtoupper(isValidData($_POST['DESIGNATION']));
                } else {
                    $error .= "<div class='text-danger'>Designation of Employee is required</div>";
                }
                if (isset($_POST['DEPARTMENT_NAME']) && isValidData($_POST['DEPARTMENT_NAME'])) {
                    $DEPARTMENT_NAME = strtoupper(isValidData($_POST['DEPARTMENT_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Department Name is requiredr</div>";
                }
                if (isset($_POST['IS_REGULAR']) && isValidData($_POST['IS_REGULAR'])) {
                    $IS_REGULAR = strtoupper(isValidData($_POST['IS_REGULAR']));
                } else {
                    $error .= "<div class='text-danger'>Job Nature Must Select</div>";
                }
                if (isset($_POST['RELATIONSHIP']) && isValidData($_POST['RELATIONSHIP'])) {
                    $RELATIONSHIP = strtoupper(isValidData($_POST['RELATIONSHIP']));
                } else {
                    $error .= "<div class='text-danger'>Must select Relationship</div>";
                }

                $service_certificate_of_employee_image= "";
                if(isset($SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO)){
                    $service_certificate_of_employee_image = $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                }

                if (isset($_FILES['service_certificate_of_employee_image'])) {
                    if (isValidData($_FILES['service_certificate_of_employee_image']['name'])) {

                        $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                        $image_name = "service_certificate_of_employee_image_$user_id";
                        $res = $this->upload_image('service_certificate_of_employee_image', $image_name, $this->file_size, $file_path, $config_a);
                        if ($res['STATUS'] === true) {
                            $service_certificate_of_employee_image = "$user_id/" . $res['IMAGE_NAME'];
                            $is_upload_any_doc = true;

                        } else {
                            $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        }
                    } else {
                        if ($service_certificate_of_employee_image == "")
                            $error .= "<div class='text-danger'>Upload image of Employee's Service Certificate and image size must be less then 500kb </div>";
                    }
                }
                else {

                    if ($service_certificate_of_employee_image == "")
                        $error .= "<div class='text-danger'>Upload image of Employee's Service Certificate and image size must be less then 500kb </div>";
                }

                if($error==""){
                    //
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO=array();
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['EMPLOYEE_NAME']=$EMPLOYEE_NAME;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['DESIGNATION']=$DESIGNATION;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['DEPARTMENT_NAME']=$DEPARTMENT_NAME;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['JOB_NATURE']=$IS_REGULAR;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['RELATIONSHIP']=$RELATIONSHIP;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['CERTIFICATE_IMAGE']=$service_certificate_of_employee_image;


                    $SU_EMPLOYEE_QUOTA['CATEGORY_INFO'] = json_encode($SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO);
                    $data[] = $SU_EMPLOYEE_QUOTA;
                }
            }
            if (isset($_POST['SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA'])) {


                $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ROW = null;

                foreach ($list_of_categoy as $categoy_row){

                    if($categoy_row['FORM_CATEGORY_ID']==$SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ID){

                        $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ROW = $categoy_row;
                        break;
                    }
                }

                if($SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ROW){
                    $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_INFO= json_decode($SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ROW['CATEGORY_INFO'],true);
                }

                $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_ID,'CATEGORY_INFO'=>'');

                if (isset($_POST['AFFILIATED_EMPLOYEE_NAME']) && isValidData($_POST['AFFILIATED_EMPLOYEE_NAME'])) {
                    $EMPLOYEE_NAME = strtoupper(isValidData($_POST['AFFILIATED_EMPLOYEE_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Complete Employee Name is required</div>";
                }
                if (isset($_POST['AFFILIATED_DESIGNATION']) && isValidData($_POST['AFFILIATED_DESIGNATION'])) {
                    $DESIGNATION = strtoupper(isValidData($_POST['AFFILIATED_DESIGNATION']));
                } else {
                    $error .= "<div class='text-danger'>Designation of Employee is required</div>";
                }
                if (isset($_POST['AFFILIATED_DEPARTMENT_NAME']) && isValidData($_POST['AFFILIATED_DEPARTMENT_NAME'])) {
                    $DEPARTMENT_NAME = strtoupper(isValidData($_POST['AFFILIATED_DEPARTMENT_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Department Name is required</div>";
                }
                if (isset($_POST['AFFILIATED_IS_REGULAR']) && isValidData($_POST['AFFILIATED_IS_REGULAR'])) {
                    $IS_REGULAR = strtoupper(isValidData($_POST['AFFILIATED_IS_REGULAR']));
                } else {
                    $error .= "<div class='text-danger'>Nature of Job is required</div>";
                }
                if (isset($_POST['AFFILIATED_RELATIONSHIP']) && isValidData($_POST['AFFILIATED_RELATIONSHIP'])) {
                    $RELATIONSHIP = strtoupper(isValidData($_POST['AFFILIATED_RELATIONSHIP']));
                } else {
                    $error .= "<div class='text-danger'>Must select Relationship</div>";
                }

                $affiliated_service_certificate_of_employee_image= "";
                if(isset($SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_INFO)){
                    $affiliated_service_certificate_of_employee_image = $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_INFO['CERTIFICATE_IMAGE'];
                }

                if (isset($_FILES['affiliated_service_certificate_of_employee_image'])) {
                    if (isValidData($_FILES['affiliated_service_certificate_of_employee_image']['name'])) {

                        $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                        $image_name = "affiliated_service_certificate_of_employee_image_$user_id";
                        $res = $this->upload_image('affiliated_service_certificate_of_employee_image', $image_name, $this->file_size, $file_path, $config_a);
                        if ($res['STATUS'] === true) {
                            $affiliated_service_certificate_of_employee_image = "$user_id/" . $res['IMAGE_NAME'];
                            $is_upload_any_doc = true;

                        } else {
                            $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        }
                    } else {
                        if ($affiliated_service_certificate_of_employee_image == "")
                            $error .= "<div class='text-danger'>Upload image of Employee's Service Certificate and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($affiliated_service_certificate_of_employee_image == "")
                        $error .= "<div class='text-danger'>Upload image of Employee's Service Certificate and image size must be less then 500kb </div>";
                }

                if($error==""){
                    //
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO=array();
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['EMPLOYEE_NAME']=$EMPLOYEE_NAME;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['DESIGNATION']=$DESIGNATION;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['DEPARTMENT_NAME']=$DEPARTMENT_NAME;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['JOB_NATURE']=$IS_REGULAR;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['RELATIONSHIP']=$RELATIONSHIP;
                    $SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO['CERTIFICATE_IMAGE']=$affiliated_service_certificate_of_employee_image;


                    $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA['CATEGORY_INFO'] = json_encode($SU_EMPLOYEE_QUOTA_ROW_CATEGORY_INFO);
                    $data[] = $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA;
                }

            }
            if (isset($_POST['DISABLED_PERSON_QUOTA'])) {

                $DISABLED_PERSON_QUOTA_ROW = null;

                foreach ($list_of_categoy as $categoy_row){

                    if($categoy_row['FORM_CATEGORY_ID']==$DISABLED_PERSON_QUOTA_ID){

                        $DISABLED_PERSON_QUOTA_ROW = $categoy_row;
                        break;
                    }
                }

                if($DISABLED_PERSON_QUOTA_ROW){
                    $DISABLED_PERSON_QUOTA_INFO= json_decode($DISABLED_PERSON_QUOTA_ROW['CATEGORY_INFO'],true);
                }

                $DISABLED_PERSON_QUOTA=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$DISABLED_PERSON_QUOTA_ID,'CATEGORY_INFO'=>'');
                //    [TYPE_OF_DISABILTY] => 0
                //    [medical_certificate_image1] =>
                if (isset($_POST['TYPE_OF_DISABILTY']) && isValidData($_POST['TYPE_OF_DISABILTY'])) {
                    $TYPE_OF_DISABILTY = strtoupper(isValidData($_POST['TYPE_OF_DISABILTY']));
                } else {
                    $error .= "<div class='text-danger'>Please select type of Disability</div>";
                }

                $medical_certificate_image= "";
                if(isset($DISABLED_PERSON_QUOTA_INFO)){
                    $medical_certificate_image = $DISABLED_PERSON_QUOTA_INFO['CERTIFICATE_IMAGE'];
                }

                if (isset($_FILES['medical_certificate_image'])) {
                    if (isValidData($_FILES['medical_certificate_image']['name'])) {

                        $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                        $image_name = "medical_certificate_image_$user_id";
                        $res = $this->upload_image('medical_certificate_image', $image_name, $this->file_size, $file_path, $config_a);
                        if ($res['STATUS'] === true) {
                            $medical_certificate_image = "$user_id/" . $res['IMAGE_NAME'];
                            $is_upload_any_doc = true;

                        } else {
                            $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        }
                    } else {
                        if ($medical_certificate_image == "")
                            $error .= "<div class='text-danger'>Please upload image of Medical Certificate and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($medical_certificate_image == "")
                        $error .= "<div class='text-danger'>Please upload image of Medical Certificate and image size must be less then 500kb </div>";
                }

                if($error==""){
                    //
                    $DISABLED_PERSON_QUOTA_ROW_CATEGORY_INFO=array();
                    $DISABLED_PERSON_QUOTA_ROW_CATEGORY_INFO['TYPE_OF_DISABILITY']=$TYPE_OF_DISABILTY;

                    $DISABLED_PERSON_QUOTA_ROW_CATEGORY_INFO['CERTIFICATE_IMAGE']=$medical_certificate_image;


                    $DISABLED_PERSON_QUOTA['CATEGORY_INFO'] = json_encode($DISABLED_PERSON_QUOTA_ROW_CATEGORY_INFO);
                    $data[] = $DISABLED_PERSON_QUOTA;
                }
            }
            if (isset($_POST['HAFIZ_QUOTA'])) {

                $HQ_QUOTA_ROW = null;

                foreach ($list_of_categoy as $categoy_row){

                    if($categoy_row['FORM_CATEGORY_ID']==$HQ_QUOTA_ID){

                        $HQ_QUOTA_ROW = $categoy_row;
                        break;
                    }
                }

                if($HQ_QUOTA_ROW){
                    $HQ_QUOTA_INFO= json_decode($HQ_QUOTA_ROW['CATEGORY_INFO'],true);
                }

                $HQ_QUOTA=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$HQ_QUOTA_ID,'CATEGORY_INFO'=>'');
                //    [TYPE_OF_DISABILTY] => 0
                //    [medical_certificate_image1] =>
                // if (isset($_POST['TYPE_OF_DISABILTY']) && isValidData($_POST['TYPE_OF_DISABILTY'])) {
                //     $TYPE_OF_DISABILTY = strtoupper(isValidData($_POST['TYPE_OF_DISABILTY']));
                // } else {
                //     $error .= "<div class='text-danger'>Please select type of Disability</div>";
                // }

                $hq_certificate_image= "";
                if(isset($HQ_QUOTA_INFO)){
                    $hq_certificate_image = $HQ_QUOTA_INFO['CERTIFICATE_IMAGE'];
                }

                if (isset($_FILES['hq_certificate_image'])) {
                    if (isValidData($_FILES['hq_certificate_image']['name'])) {

                        $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                        $image_name = "hq_certificate_image_$user_id";
                        $res = $this->upload_image('hq_certificate_image', $image_name, $this->file_size, $file_path, $config_a);
                        if ($res['STATUS'] === true) {
                            $hq_certificate_image = "$user_id/" . $res['IMAGE_NAME'];
                            $is_upload_any_doc = true;

                        } else {
                            $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        }
                    } else {
                        if ($hq_certificate_image == "")
                            $error .= "<div class='text-danger'>Please upload image of Hafiz-e-Quran Certificate and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($hq_certificate_image == "")
                        $error .= "<div class='text-danger'>Please upload image of  Hafiz-e-Quran Certificate and image size must be less then 500kb </div>";
                }

                if($error==""){
                    //
                    $HQ_QUOTA_ROW_CATEGORY_INFO=array();
                   // $DISABLED_PERSON_QUOTA_ROW_CATEGORY_INFO['TYPE_OF_DISABILITY']=$TYPE_OF_DISABILTY;

                    $HQ_QUOTA_ROW_CATEGORY_INFO['CERTIFICATE_IMAGE']=$hq_certificate_image;


                    $HQ_QUOTA['CATEGORY_INFO'] = json_encode($HQ_QUOTA_ROW_CATEGORY_INFO);
                    $data[] = $HQ_QUOTA;
                }
            }
            if (isset($_POST['SPORTS_QUOTA'])) {
                $SPORTS_QUOTA=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$SPORTS_QUOTA_ID,'CATEGORY_INFO'=>'');
                $data[] = $SPORTS_QUOTA;
            }
            if($error==""){

                if($this->Application_model->deleteAndInsertApplicantCategory($data))
                {
                    //prePrint($data);
                    $reponse['RESPONSE']="SUCCESS";
                    $reponse['MESSAGE']="Successfully update information";
                }else{
                    $reponse['RESPONSE']="ERROR";
                    $reponse['MESSAGE']="Something went wrong";
                }

            }else{
                //prePrint($error);
                $reponse['RESPONSE']="ERROR";
                $reponse['MESSAGE']=$error;
            }

            if ($reponse['RESPONSE'] == "ERROR") {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode($reponse));
            } else {
                $this->output
                    ->set_status_header(200)
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode($reponse));
            }


        }
    }

    //UPDATED FUNCTION ON 22-OCT-2020 MODAL,View,Controller  FILE User_model.php,form_reivew.php,Form/reivew
    public function check_final_validation($next_page){
         $this->block_for_test();
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$APPLICATION_ID);
//            prePrint($user_fulldata);
//            exit();
            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            if ($application) {

                //prePrint($application);

                $error = $this->isValidProfileInformation($user_fulldata,$application);

//
//                prePrint($error);
//                exit();
                if($error==""){
//                    prePrint($application);
//                exit();
                    if(($application['PAID']=='N'||$application['PAID']=='Y')&&isValidData($application['CHALLAN_IMAGE'])){

                        $min_choice = 0;
                        $max_choice = 0;
                        if($application['PROGRAM_TYPE_ID'] == 1){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            foreach ($user_fulldata['application_choices'] as $choice_list){
                                if($choice_list['PROG_LIST_ID']==LLB_PROG_LIST_ID){
                                    $min_choice = 1;
                                    break;
                                }
                            }


                        }else if($application['PROGRAM_TYPE_ID'] == 2){
                            $max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
                        } else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                        }


                        if($min_choice>count($user_fulldata['application_choices'])||$max_choice<count($user_fulldata['application_choices'])){
                            $error.="<div class='text-danger'>Please select minimum $min_choice and maximum $max_choice choices</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/select_program");
                        }
                        if(count($user_fulldata['application_category'])<=0){

                            $error.="<div class='text-danger'>Please must Save your Category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/select_category");
                        }
                        //$next_page = "final_lock";
                        $next_page1 = base64_encode($next_page);
                        $next_page1 =urlencode($next_page1);
                        if($next_page=="final_lock"){
                            redirect(base_url() . "form/review/$next_page1");
                        }else{
                            $error.="<div class='text-danger'>Something went wrong, please contact technical team</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);

                            redirect(base_url()."form/dashboard");
                        }

                    }else{
                        $error.="<div class='text-danger'>Please select Bank Branch and save it</div>";
                        $error.="<div class='text-danger'>Please upload the image of Paid Challan and then press SAVE button.</div>";
                        $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                        redirect(base_url()."form/upload_application_challan");
                    }


                }else{
                    $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/upload_application_challan");
                    //prePrint($error);
                }


            }else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }
        }else {
            redirect(base_url() . "login");
        }
    }
     //UPDATED FUNCTION ON 22-OCT-2020 MODAL,View,Controller  FILE User_model.php,form_reivew.php,Form/reivew
    public function check_final_validation_evening($next_page){
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$APPLICATION_ID);
//            prePrint($user_fulldata);
//            exit();
            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            if ($application) {

                //prePrint($application);

                $error = $this->isValidProfileInformation($user_fulldata,$application);

//
//                prePrint($error);
//                exit();
                if($error==""){
//                    prePrint($application);
//                exit();
                    if(($application['PAID']=='N'||$application['PAID']=='Y')&&isValidData($application['CHALLAN_IMAGE'])){

                        $min_choice = 0;
                        $max_choice = 0;
                        if($application['PROGRAM_TYPE_ID'] == 1){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            foreach ($user_fulldata['application_choices'] as $choice_list){
                                if($choice_list['PROG_LIST_ID']==LLB_PROG_LIST_ID){
                                    $min_choice = 1;
                                    break;
                                }
                            }


                        }else if($application['PROGRAM_TYPE_ID'] == 2){
                            $max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
                        } else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                        }


                        if($min_choice>count($user_fulldata['application_choices_evening'])||$max_choice<count($user_fulldata['application_choices_evening'])){
                            $error.="<div class='text-danger'>Please select minimum $min_choice and maximum $max_choice choices</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/select_program");
                        }
                        if(count($user_fulldata['application_category'])<=0){

                            $error.="<div class='text-danger'>Save Category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/select_category");
                        }
                        //$next_page = "final_lock";
                        $next_page1 = base64_encode($next_page);
                        $next_page1 =urlencode($next_page1);
                        if($next_page=="final_lock_evening"){
                            redirect(base_url() . "form/review/$next_page1");
                        }else{
                            $error.="<div class='text-danger'>Something went wrong, please contact technical team</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);

                            redirect(base_url()."form/dashboard");
                        }

                    }else{
                        $error.="<div class='text-danger'>Please select Bank Branch and then save it</div>";
                        $error.="<div class='text-danger'>Please upload image of Paid Challan and then press SAVE button.</div>";
                        $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                        redirect(base_url()."form/upload_application_challan");
                    }


                }else{
                    $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/upload_application_challan");
                    //prePrint($error);
                }


            }else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }
        }else {
            redirect(base_url() . "login");
        }
    }

    //UPDATED FUNCTION ON 04-NOV-2020
    public function final_lock(){
         $this->block_for_test();
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$APPLICATION_ID);
//            prePrint($user_fulldata);
//            exit();
            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            if ($application) {

                //prePrint($application);

                $error = $this->isValidProfileInformation($user_fulldata,$application);

//
//                prePrint($error);
//                exit();
                if($error==""){
//                    prePrint($application);
//                exit();
                    if(($application['PAID']=='N'||$application['PAID']=='Y')&&isValidData($application['CHALLAN_IMAGE'])){

                        $min_choice = 0;
                        $max_choice = 0;
                        
                        if($application['PROGRAM_TYPE_ID'] == 1){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                            foreach ($user_fulldata['application_choices'] as $choice_list){
                                if($choice_list['PROG_LIST_ID']==LLB_PROG_LIST_ID){
                                    $min_choice = 1;
                                    break;
                                }
                            }


                        }else if($application['PROGRAM_TYPE_ID'] == 2){
                            $max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
                            
                        }
                        else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                        }
                        
                        if($min_choice<1){
                                $min_choice =1 ;
                            }
                            
                            //application_choices_evening
                            $merge_choice = array_merge($user_fulldata['application_choices'],$user_fulldata['application_choices_evening']);
                        if($min_choice>count($merge_choice)||$max_choice<count($merge_choice)){
                            $error.="<div class='text-danger'>Please select inimum $min_choice and maximum $max_choice choices</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/select_program");
                            exit();
                        }
                        if(count($user_fulldata['application_category'])<=0){

                            $error.="<div class='text-danger'>Save Category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/select_category");
                            exit();
                        }
                        $next_page = "final_lock";
                        $next_page1 = base64_encode($next_page);
                        $next_page1 =urlencode($next_page1);
                        $STATUS_ID = 0;
                         if($application['STATUS_ID']>FINAL_SUBMIT_STATUS_ID){
                          $STATUS_ID = $application['STATUS_ID'];   
                         }
                        $this->Application_model->final_lock_form($APPLICATION_ID,$user_fulldata,$STATUS_ID);
                        redirect(base_url()."form/application_form");

                    }else{
                        $error.="<div class='text-danger'>Please select Bank Branch and then press SAVE button.</div>";
                        $error.="<div class='text-danger'>Please upload image of Paid Challan and then press SAVE button.</div>";
                        $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                        redirect(base_url()."form/upload_application_challan");
                    }


                }else{
                    $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/upload_application_challan");
                    //prePrint($error);
                }


            }else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }
        }else {
            redirect(base_url() . "login");
        }

	}
	   //UPDATED FUNCTION ON 28-02-2021
    public function final_lock_evening(){
        
        if($this->session->has_userdata('APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$APPLICATION_ID);
//            prePrint($user_fulldata);
//            exit();
            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            if ($application) {

                //prePrint($application);

                $error = $this->isValidProfileInformation($user_fulldata,$application);

//
//                prePrint($error);
//                exit();
                if($error==""){
//                    prePrint($application);
//                exit();
                    if(($application['PAID']=='N'||$application['PAID']=='Y')&&isValidData($application['CHALLAN_IMAGE'])){

                        $min_choice = 0;
                        $max_choice = 0;
                        if($application['PROGRAM_TYPE_ID'] == 1){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            foreach ($user_fulldata['application_choices'] as $choice_list){
                                if($choice_list['PROG_LIST_ID']==LLB_PROG_LIST_ID){
                                    $min_choice = 1;
                                    break;
                                }
                            }


                        }else if($application['PROGRAM_TYPE_ID'] == 2){
                            $max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
                        } else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                        }


                        if($min_choice>count($user_fulldata['application_choices_evening'])||$max_choice<count($user_fulldata['application_choices_evening'])){
                            $error.="<div class='text-danger'>Please select minimum $min_choice and maximum $max_choice choices</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/evening_choices");
                            exit();
                        }
                        if(count($user_fulldata['application_category'])<=0){

                            $error.="<div class='text-danger'>Save Category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url()."form/add_evening_category");
                            exit();
                        }
                        $next_page = "final_lock";
                        $next_page1 = base64_encode($next_page);
                        $next_page1 =urlencode($next_page1);
                       $STATUS_ID = 0;
                         if($application['STATUS_ID']>FINAL_SUBMIT_STATUS_ID){
                          $STATUS_ID = $application['STATUS_ID'];   
                         }
                        $this->Application_model->final_lock_form($APPLICATION_ID,$user_fulldata,$STATUS_ID);
                        redirect(base_url()."form/application_form");

                    }else{
                        $error.="<div class='text-danger'>Please select Bank Branch and then press SAVE button.</div>";
                        $error.="<div class='text-danger'>Please upload image of Paid Challan and then press SAVE button.</div>";
                        $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                        redirect(base_url()."form/upload_application_challan");
                    }


                }else{
                    $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/upload_application_challan");
                    //prePrint($error);
                }


            }else{
                $alert = array('MSG'=>"<div class='text-danger'>Application Not found </div>",'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/announcement");
            }
        }else {
            redirect(base_url() . "login");
        }

	}

	//UPDATED FUNCTION ON 04-NOV-2020
	public function application_form(){
        if($this->session->has_userdata('APPLICATION_ID')) {
            
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
        
            $user = $this->session->userdata($this->SessionName);
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
                
            if($application['STATUS_ID']<FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/dashboard'));
                exit();
            }
            if(!file_exists(PROFILE_IMAGE_CHECK_PATH.$user['PROFILE_IMAGE'])){

             /*  do {
                   $resutl = $this->CI_ftp_Download(PROFILE_IMAGE_CHECK_PATH, $user['PROFILE_IMAGE']);

                  /// prePrint("RES".$resutl);
               }while(!$resutl);*/
				exit("file not found");
                //exit();
            }
            
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'], $APPLICATION_ID);
            
            $data['profile_url'] = $this->profile;
            $data['user_fulldata'] = $user_fulldata;
            $data['application'] = $application;
            $data['bank_info'] = $this->Admission_session_model->getBankInformationByBranchId($application['BRANCH_ID']);
            //prePrint($data);
            //exit();
            $this->load->view('application_form',$data);

        }else{
            redirect(base_url() . "login");
        }
    }
   public function review($next_page)
	{

        $next_page =urldecode($next_page);
        $next_page = base64_decode($next_page);
        if($this->session->has_userdata('APPLICATION_ID')){
           
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
            $user = $this->session->userdata($this->SessionName);
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$APPLICATION_ID);
            //$this->User_model->getUserFullDetailWithChoiceById($user_id,$APPLICATION_ID);
            // prePrint($user_fulldata);
            // exit();
             $data['profile_url'] = $this->profile;
            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']==2&&$next_page=='dashboard'){
                redirect(base_url('Candidate/add_inter_qualification'));
            }
             if($application['STATUS_ID']>=FINAL_SUBMIT_STATUS_ID&&$next_page=='dashboard'){
                redirect(base_url('form/dashboard'));
            }
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            
            if ($application) {

                $bank = $this->Admission_session_model->getBankInformationByBranchId($application['BRANCH_ID']);
                //$bank = $this->Admission_session_model;
                $data['user'] = $user_fulldata['users_reg'];
                $data['qualifications'] = $user_fulldata['qualifications'];
                $data['guardian'] = $user_fulldata['guardian'];
                $data['application_choices'] = $user_fulldata['application_choices'];
                $data['application_choices_evening'] = $user_fulldata['application_choices_evening'];
                $data['application_category'] = $user_fulldata['application_category'];
                $data['next_page'] = $next_page;
                $data['application'] = $application;
                $data['bank'] = $bank;


                $this->load->view('include/header', $data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar');
		$this->load->view('include/nav',$data);
                $this->load->view('form_review_1', $data);
		$this->load->view('include/footer_area');
                $this->load->view('include/footer');

            }else{
                echo "Application Id not found";
            }
        }else{
            echo "Application Id not found";
        }
//        if($this->session->has_userdata('APPLICATION_ID')) {
//            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
//
//            $user = $this->user;
//            $user_id = $user['USER_ID'];
//
//            $user_data = $this->User_model->getUserFullDetailById($user_id);
//            //prePrint($user_data);
//            $data['user'] = $user_data['users_reg'];
//            $data['qualifications'] = $user_data['qualifications'];
//            $data['guardian'] = $user_data['guardian'];
//            $data['next_page'] = $next_page;
//
//
//            $this->load->view('include/header', $data);
////		$this->load->view('include/preloder');
////		$this->load->view('include/side_bar');
////		$this->load->view('include/nav',$data);
//            $this->load->view('form_review', $data);
////		$this->load->view('include/footer_area');
//            $this->load->view('include/footer');
//        }else{
//            echo "Application Id not found";
//        }


	}
	
	   //ADDED FUNCTION ON 08-JAN-2020
	public function add_lat_info_locked___locked(){

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']<3||$application['STATUS_ID']>5){
                redirect(base_url('form/dashboard'));
            }
//            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
//                redirect(base_url('form/application_form'));
//                exit();
//            }
//            if($application['STATUS_ID']<2){
//                redirect(base_url('form/dashboard'));
//            }
            if ($application) {
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }


                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);



                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }

                            //break;
                        }
                    }
                }



//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


                    $data['profile_url'] = $this->profile;

                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);



                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor)
                        {
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }

                        if(count($minorMappingIds)==0){
                            echo "Please Must select Minor Subject";
                            $error = "<div class='text-danger'> Please Must select Minor Subject</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_subject'));
                        }
                        $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
                        if(count($list_of_categoy)==0){
                            echo "Please must Save Category";
                            $error = "<div class='text-danger'> Please must Save category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_category'));
                        }
                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);

                        $prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (1,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);

                        $valid_exact_program = array();
                        foreach($prog_list_by_shift as $prog_list){
                            foreach ($valid_program_list as $valid_program){
                                if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
                                    $valid_exact_program[]=$valid_program;
                                }
                            }
                        }

                        $CHOOSEN_PROGRAM_LIST = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['USER_ID'],$APPLICATION_ID,$MORNING_SHIFT=1);
                        $lat_info = $this->Application_model->getLatInfoByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);
                        $program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        $data['VALID_PROGRAM_LIST'] =$valid_exact_program;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['CHOOSEN_PROGRAM_LIST'] = $CHOOSEN_PROGRAM_LIST;
                        $data['lat_info'] =$lat_info;

                        $precentage = ($valid_qualification['OBTAINED_MARKS']*100/$valid_qualification['TOTAL_MARKS']);
                        $data['precentage'] =round($precentage,2);


                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('select_lat_information', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                          redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);





            } else {
                echo "This application id is not associated with you";
            }
        }
        else{
            echo "Application Id Not Found";
        }
    }

    public function upload_lat_handler_locked___locked(){
	    //prePrint($_POST);
        $config_a = array();
        $config_a['maintain_ratio'] = true;
        $config_a['width']         = 360;
        $config_a['height']       = 500;
        $config_a['resize']       = false;

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);
            $CHOOSEN_PROGRAM_LIST = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['USER_ID'],$APPLICATION_ID,$MORNING_SHIFT=1);
            $lat_info = $this->Application_model->getLatInfoByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);
            $is_llb_exist = false;
            foreach ($CHOOSEN_PROGRAM_LIST as $CHOOSEN_PROGRAM){
                if($CHOOSEN_PROGRAM['PROG_LIST_ID']==LLB_PROG_LIST_ID){
                    $is_llb_exist = true;
                }
            }

            $error = "";
            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
            } else {
                $error .= "<div class='text-danger'>Please enter Ticket Number / Seat Number</div>";
            }

            if (isset($_POST['TEST_DATE']) && isValidTimeDate($_POST['TEST_DATE'], 'd/m/Y')) {
                $TEST_DATE = getDateForDatabase($_POST['TEST_DATE']);
                if ($TEST_DATE > date('Y-m-d')) {
                    $error .= "<div class='text-danger'>Choose valid Test Date</div>";
                }
            } else {
                $error .= "<div class='text-danger'>Must choose/select Test</div>";
            }

            if (isset($_POST['TEST_SCORE']) && isValidData($_POST['TEST_SCORE'])) {
                $TEST_SCORE = strtoupper(isValidData($_POST['TEST_SCORE']));
                if ($TEST_SCORE < 50|| $TEST_SCORE > 100) {
                    $error .= "<div class='text-danger'>Invalid test Score</div>";
                }
            } else {
                $error .= "<div class='text-danger'>Please enter valid Test Score</div>";
            }

            $result_card_image = "";

            if (isset($_POST['result_card_image1']) && isValidData($_POST['result_card_image1'])) {
                $result_card_image = strtoupper(isValidData($_POST['result_card_image1']));
            }

            $user_id = $user['USER_ID'];
            if (isset($_FILES['result_card_image'])) {
                if (isValidData($_FILES['result_card_image']['name'])) {

                    $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                    $image_name = "lat_result_card_image_$user_id";
                    $res = $this->upload_image('result_card_image', $image_name, $this->file_size, $file_path, $config_a);
                    if ($res['STATUS'] === true) {
                        $result_card_image = "$user_id/" . $res['IMAGE_NAME'];
                        $is_upload_any_doc = true;

                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                    if ($result_card_image == "")
                        $error .= "<div class='text-danger'>Please upload the image of your Result Card and image size must be less than 500kb </div>";
                        else{
                            $result_card_image = $lat_info['RESULT_IMAGE'];
                        }
                }
            }
            else {

                if ($result_card_image == "")
                    $error .= "<div class='text-danger'>Please upload the image of your Result Card and image size must be less than 500kb </div>";
                    else{
                      $result_card_image  = $lat_info['RESULT_IMAGE'];
                    }
            }
            if($error==""){
                $lat_info_array = array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,"TOKEN_NO"=>$TOKEN_NO,"TEST_DATE"=>$TEST_DATE,"TEST_SCORE"=>$TEST_SCORE,"RESULT_IMAGE"=>$result_card_image);
                if($is_llb_exist){
                    // prePrint($lat_info_array);
                    // exit();
                    $res = $this->Application_model->update_lat_info($lat_info['LAT_INFO_ID'],$lat_info_array);
                }
                else{
                    // $this->Application_model->insert_lat_info($lat_info);
                    $choice_no  = count($CHOOSEN_PROGRAM_LIST)+1;
                    $form_array = array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>LLB_PROG_LIST_ID,'CHOICE_NO'=>$choice_no,'SHIFT_ID'=>1);
                    $res  = $this->Application_model->insert_choice($form_array,$lat_info_array);
                }
                if($res){
                    $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$APPLICATION_ID,$SHIFT_ID=1);

                    $user_fulldata = json_encode($user_fulldata);

                    $application_array = array("FORM_DATA"=>$user_fulldata,"USER_ID"=>$user['USER_ID']);

                    $res_app = $this->Application_model->updateApplicationById($APPLICATION_ID,$application_array);

                    $error .= "<div class='text-success'>Successfully Update</div>";
                    $alert = array('MSG' => $error, 'TYPE' => 'SUCCESS');
                    $this->session->set_flashdata('ALERT_MSG', $alert);
                    // redirect(base_url()."form/upload_application_challan/$APPLICATION_ID");
                    redirect(base_url('form/add_lat_info'));
                }else {
                    $error .= "<div class='text-success'>No data has been changed!</div>";
                    $alert = array('MSG' => $error, 'TYPE' => 'SUCCESS');
                    $this->session->set_flashdata('ALERT_MSG', $alert);
                    // redirect(base_url()."form/upload_application_challan/$APPLICATION_ID");
                    redirect(base_url('form/add_lat_info'));
                }
            }else{
                $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
               // redirect(base_url()."form/upload_application_challan/$APPLICATION_ID");
                redirect(base_url('form/add_lat_info'));
            }




        }else{
            redirect(base_url('form/dashboard'));
        }
    }
    	
	public function add_special_self_category(){

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);
            $CANDIDATE_USER_ID = $user['USER_ID'];

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);

        
                                            
//			if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
//				redirect(base_url('form/application_form'));
//				exit();
//			}

//			if($application['STATUS_ID']<2){
//				redirect(base_url('form/dashboard'));
//			}
            if ($application) {
                $this->check_special_self_validation($application);
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }
                }else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }
                            //break;
                        }
                    }
                }

                    $data['profile_url'] = $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){
                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];
                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();
                        foreach ($applicantsMinors as $applicantsMinor){
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }
                        if(count($minorMappingIds)==0){
                            echo "Please Must select Minor Subject";
                            $error = "<div class='text-danger'> Please Must select Minor Subject</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_subject'));
                        }

                        $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);
                        $program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        // prePrint($list_of_categoy);
                        // exit();
                        $data['VALID_PROGRAM_LIST'] =$valid_program_list;
                        $data['list_of_category'] =$list_of_categoy;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];

                        $candidate_evening_category = findObjectinList($list_of_categoy,'FORM_CATEGORY_ID',SPECIAL_SELF_FINANCE);
                        if (is_array($candidate_evening_category)){
                            $evening_choice_bool = true;
                        }else{
                            $category_array = array (
                                'USER_ID'=>$CANDIDATE_USER_ID,
                                'APPLICATION_ID'=>$APPLICATION_ID,
                                'FORM_CATEGORY_ID'=>SPECIAL_SELF_FINANCE,
                                'IS_ENABLE'=>'Y',
                            );
                            $success_category = $this->Administration->insert($category_array,'application_category');
                            if ($success_category){
                                $evening_choice_bool = true;
                            }else{
                                exit("Error: while adding your Special Self Finance Category");
                            }
                        }
                        if ($evening_choice_bool){
                            redirect(base_url($this->SelfController.'/special_self_choices'));
                        }
                    }else{
                          redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree - Please must select/add appropriate Degree";
                    }


            } else {
                echo "This application id is not associated with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

   public  function special_self_choices(){

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            /*
                if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                    redirect(base_url('form/application_form'));
                    exit();
                }
            */
            if ($application) {

                //form close from bachelor
                //$this->close_registration_for_bachelor($application);

                $this->check_special_self_validation($application);
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }
                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);



                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }

                            //break;
                        }
                    }
                }


//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }

                    $data['profile_url'] = $this->profile;

                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);

                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor)
                        {
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }

                        if(count($minorMappingIds)==0){
                            echo "Please select Minor Subjects";
                            $error = "<div class='text-danger'> Please Must select Minor Subject</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_subject'));
                        }
                        $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
                        if(count($list_of_categoy)==0){
                            echo "Please must Save Category";
                            $error = "<div class='text-danger'> Please must Save category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/select_category'));
                        }
                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);

                        $prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (MORNING_SHIFT_ID,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);

                        $valid_exact_program = array();
                        
                        //Vikesh Add or delete bachelor degree programs for Special Self Finance Category  
                        
                        foreach($prog_list_by_shift as $prog_list){
                            foreach ($valid_program_list as $valid_program){
                                $show_program = array(5,234,78,160,14,9,110,268,269,154,280,260,259,278,153,279,138,102,81,99,166,18,28,184,19,20,108,106,273,101,4,22,80,143,286);

                                if(!in_array($valid_program['PROG_LIST_ID'], $show_program)){
                                   continue; 
                                }
                                if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
                                    $valid_exact_program[]=$valid_program;
                                }
                            }
                        }

                        $CHOOSEN_PROGRAM_LIST = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['USER_ID'],$APPLICATION_ID,MORNING_SHIFT_ID);
                        $lat_info = $this->Application_model->getLatInfoByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);
                        $program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        $CHOOSEN_PROGRAM_LIST_NEW = array();
                        foreach($CHOOSEN_PROGRAM_LIST as $cho){
                            if($cho['IS_SPECIAL_CHOICE']=='Y'){
                                array_push($CHOOSEN_PROGRAM_LIST_NEW,$cho);
                            }
                        }
                        $data['VALID_PROGRAM_LIST'] =$valid_exact_program;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST_NEW;
                        $data['lat_info'] =$lat_info;

                        $precentage = ($valid_qualification['OBTAINED_MARKS']*100/$valid_qualification['TOTAL_MARKS']);
                        $data['precentage'] =round($precentage,2);


                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('special_choice_list_candidate', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                        echo "Invalid Degree Please must add appropriate degree";
                    }


            } else {
                echo "This Application Id is not associated with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    public function upload_special_self_choices(){

//		prePrint($_POST);
//		exit();
        $error="";
        $config_a = array();
        $config_a['maintain_ratio'] = true;
        $config_a['width']         = 360;
        $config_a['height']       = 500;
        $config_a['resize']       = false;
        $reponse['RESPONSE'] = "ERROR";


        if($this->session->has_userdata('APPLICATION_ID')) {

            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($this->user['IS_SUPER_PASSWORD_LOGIN']=='N'&&IS_SPECIAL_SELF_OPEN == 0){
                redirect(base_url('form/application_form'));
                exit();
            }
            if ($application) {
                $this->check_special_self_validation($application);
                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

                $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
                );

                //$form_data = json_decode($application['FORM_DATA'],true);
                $bool = false;
                $valid_qualification = null;
                if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
                    // echo "bach";
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }
                }
                else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
                    foreach ($form_data['qualifications'] as $k=>$qualification){
                        if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
                            $bool  = true;
                            if($k==0){
                                $valid_qualification = $qualification;
                            }
                            if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
                                $valid_qualification = $qualification;
                            }
                        }
                    }
                }


//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


                    $data['profile_url'] = $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);

                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor){
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }

                        if(count($minorMappingIds)==0){
                            $error .= "<div class='text-danger'>Please enter complete Employee Name</div>";
                        }

                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);

                        $prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (MORNING_SHIFT_ID,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);

                        $valid_exact_program = array();
                        foreach($prog_list_by_shift as $prog_list){
                            foreach ($valid_program_list as $valid_program){
                                if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
                                    $valid_exact_program[]=$valid_program;
                                }
                            }
                        }
                        $valid_program_list = $valid_exact_program;

                        //$program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        //prePrint($valid_program_list);
                        $min_choice = 0;
                        $max_choice = 0;
                        $choice_list= array();
                        $llb_validation = false;
                        if(isset($_POST['minor_subject_array'])){
                            $choice_list = $_POST['minor_subject_array'];

                        }else{
                            $error .= "<div class='text-danger'>Please enter complete Employee Name</div>";
                        }

                        if($application['PROGRAM_TYPE_ID'] == 1){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            if(in_array(LLB_PROG_LIST_ID,$choice_list)){
                                $min_choice = 1;
                                $llb_validation = true;
                            }
                        }else if($application['PROGRAM_TYPE_ID'] == 2){
                            $max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
                        } else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                        }
                        if($llb_validation){
                            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
                            } else {
                                $error .= "<div class='text-danger'>You must enter Ticket Number / Seat Number</div>";
                            }

                            if(isset($_POST['TEST_DATE'])&&isValidTimeDate($_POST['TEST_DATE'],'d/m/Y')){
                                $TEST_DATE = getDateForDatabase($_POST['TEST_DATE']);
                                if($TEST_DATE>date('Y-m-d')){
                                    $error.="<div class='text-danger'>Choose Valid Test Date</div>";
                                }
                            }else{
                                $error.="<div class='text-danger'>Test Must be Choose</div>";
                            }

                            if (isset($_POST['TEST_SCORE']) && isValidData($_POST['TEST_SCORE'])) {
                                $TEST_SCORE = strtoupper(isValidData($_POST['TEST_SCORE']));
                                if($TEST_SCORE<0||$TEST_SCORE>100){
                                    $error .= "<div class='text-danger'>Invalid Test Score</div>";
                                }
                            } else {
                                $error .= "<div class='text-danger'>Test Score Must be Enter</div>";
                            }

                            $result_card_image= "";

                            if (isset($_POST['result_card_image1']) && isValidData($_POST['result_card_image1'])) {
                                $result_card_image = strtoupper(isValidData($_POST['result_card_image1']));
                            }

                            $user_id = $user['USER_ID'];
                            if (isset($_FILES['result_card_image'])) {
                                if (isValidData($_FILES['result_card_image']['name'])) {

                                    $file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
                                    $image_name = "lat_result_card_image_$user_id";
                                    $res = $this->upload_image('result_card_image', $image_name, $this->file_size, $file_path, $config_a);
                                    if ($res['STATUS'] === true) {
                                        $result_card_image = "$user_id/" . $res['IMAGE_NAME'];
                                        $is_upload_any_doc = true;

                                    } else {
                                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                                    }
                                } else {
                                    if ($result_card_image == "")
                                        $error .= "<div class='text-danger'>You must Upload Result Card Image. Image size must be less than 500kb</div>";
                                }
                            }
                            else {

                                if ($result_card_image == "")
                                    $error .= "<div class='text-danger'>You must Upload Result Card Image. Image size must be less than 500kb</div>";
                            }
                        }

                        if($error==""){
                            if($llb_validation){
                                $lat_info = array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,"TOKEN_NO"=>$TOKEN_NO,"TEST_DATE"=>$TEST_DATE,"TEST_SCORE"=>$TEST_SCORE,"RESULT_IMAGE"=>$result_card_image);
                            }else{
                                $lat_info = null;
                            }

                            if(count($choice_list)>0&&count($choice_list)<=$max_choice&&$min_choice<=count($choice_list)){
                                $check_valid = true;
                                foreach($choice_list as $choice){
                                    $check_id_valid = false;
                                    foreach ($valid_program_list as $valid_program){

                                        if($choice==$valid_program['PROG_LIST_ID']){
                                            $check_id_valid = true;
                                            break;
                                        }
                                    }
                                    if($check_id_valid ==false){
                                        $check_valid = false;
                                        break;
                                    }
                                }

                                if($check_valid==true){

                                    $list_of_choice = array();

                                    foreach($choice_list as $CHOICE_NO => $PROG_LIST_ID){
                                        $CHOICE_NO++;
                                        $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>$PROG_LIST_ID,'CHOICE_NO'=>$CHOICE_NO,'SHIFT_ID'=>MORNING_SHIFT_ID,'IS_SPECIAL_CHOICE'=>'Y');
                                    }

                                    //prePrint($list_of_choice);
                                    if(count($list_of_choice)>0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info)){

                                    }
                                    else{
                                        $error .= "<div class='text-danger'>Your choices not added or updated. Kindly contact technical team.</div>";
                                    }
                                }
                                else{
                                    $error .= "<div class='text-danger'>Your choices are invalid. Kindly contact technical team.</div>";
                                }
                            }else{
                                $error .= "<div class='text-danger'>You must choice minimum $min_choice and maximum $max_choice</div>";
                            }
                        }


                        if($error==""){
                            $reponse['RESPONSE']="SUCCESS";
                            $reponse['MESSAGE']="Information Updated Successfully";
                        }else{
                            $reponse['RESPONSE']="ERROR";
                            $reponse['MESSAGE']=$error;
                        }
                    }else{
                        echo "Invalid Degree Please must add appropriate degree";
                        $error .= "<div class='text-danger'>Invalid Degree Please must add appropriate degree</div>";
                        exit();
                    }
                    // prePrint($application);

            }else {
                echo "this application id is not associate with you";
                $error .= "<div class='text-danger'>This Application Id is not associated with you.</div>";
                exit();
            }
        }else{
            echo "Application Id Not Found";
            $error .= "<div class='text-danger'>Application Id Not Found</div>";
            //exit();
        }

        if($error!=""){
            $reponse['RESPONSE']="ERROR";
            $reponse['MESSAGE']=$error;

        }

        if ($reponse['RESPONSE'] == "ERROR") {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        } else {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }
    
    private function check_special_self_validation($application){
         if($this->user['IS_SUPER_PASSWORD_LOGIN']=='N'&&IS_SPECIAL_SELF_OPEN==0){
             	$alert = array('MSG'=>"<div class='text-danger'>Currently, Special Self Finance category is CLOSED.</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url()."form/dashboard");
                                exit();
         }
         if(!(($application['STATUS_ID']==5||$application['STATUS_ID']==4)&&$application['CAMPUS_ID']==1 && $application['PROGRAM_TYPE_ID']==1)){
                   	$alert = array('MSG'=>"<div class='text-danger'>You are not Eligible For Special Self Finance <br> Applications having status Verified / In-Review are eligile for applying admissions on Special Self Finance<br> </div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url()."form/dashboard");
                                exit();
             
         }
    }
    
    private function getValidationArray($application){
        $must_provide = "Must Be Provided";
        $must_select = "Must Be Provided";
        $must_upload = "Must Upload";

        $qualification = array();
        $master_id_or = null;
        if($application['PROGRAM_TYPE_ID']==1||$application['PROGRAM_TYPE_ID']==3){
        $qualification =  $bachelor = array(2,3);
        $qualification_error_msg =  $bachelor_error_msg = array("Matriculation information is missing. Please provide your Matriculation information","Intermediate information is missing. Please provide your Intermediate information");

        }else if($application['PROGRAM_TYPE_ID']==2){
            $qualification =  $master_id = array(2,3);
             $master_id_or = array(4,5,6);

            $qualification_error_msg =  $master_error_msg = array("Matriculation information is missing. Please provide your Matriculation information","Intermediate information is missing. Please provide your Intermediate information");
        }

        $validation_array=array(
                "users_reg" =>array(
                    "FIRST_NAME"=>array("regex"=>"[A-Za-z]{2}","error_msg"=>"Full Name $must_provide as per Matriculation"),
                    //"LAST_NAME"=>array("regex"=>"^[A-Za-z.]+","error_msg"=>"Surname $must_provide"),
                    "FNAME"=>array("regex"=>"[A-Za-z]{2}","error_msg"=>"Father $must_provide"),
                    "GENDER"=>array("regex"=>"[A-Za-z]{1}","error_msg"=>"Gender $must_select"),
                    "MOBILE_NO"=>array("regex"=>"[0-9]{10}","error_msg"=>"Mobile Number $must_provide"),
                    "HOME_ADDRESS"=>array("regex"=>"[A-Za-z0-9\-\\,.]+","error_msg"=>"Home Address $must_provide"),
                    "PERMANENT_ADDRESS"=>array("regex"=>"[A-Za-z0-9\-\\,.]+","error_msg"=>"Parmanent Address $must_provide"),
                    "DATE_OF_BIRTH"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"Date of Birth $must_provide"),
                    "BLOOD_GROUP"=>array("regex"=>"^(A|B|AB|O)[+-]$","error_msg"=>"Blood Group $must_select"),
                    "MOBILE_CODE"=>array("regex"=>"[0-9]{4}","error_msg"=>"Mobile $must_select"),
                    "COUNTRY_ID"=>array("regex"=>"[0-9]","error_msg"=>"Country $must_select"),
                    "PROVINCE_ID"=>array("regex"=>"[0-9]","error_msg"=>"Province $must_select"),
                    "DISTRICT_ID"=>array("regex"=>"[0-9]","error_msg"=>"District $must_select"),
                    "PROFILE_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"Profile Image $must_upload"),
                    "DOMICILE_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"Domicile Image $must_upload"),
                    "DOMICILE_FORM_C_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"Domicile Form C Image $must_upload"),
                    "EMAIL"=>array("regex"=>"^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$","error_msg"=>"Email $must_provide"),
                    "RELIGION"=>array("regex"=>"[A-Za-z]{2}","error_msg"=>"Religion $must_provide"),
                    "U_R"=>array("regex"=>"^\w{1}$","error_msg"=>"Area $must_select"),

                ),
                "CNIC"=>array(
                    "CNIC_NO"=>array("regex"=>"[0-9]{13}","error_msg"=>"CNIC No $must_provide"),
                    "CNIC_FRONT_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"CNIC Front / B-Form Image $must_upload"),
                    "CNIC_BACK_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"CNIC Back / B-Form  Image $must_upload"),

                ),
                "PASSPORT"=>array(
                    "PASSPORT_NO"=>array("regex"=>"[0-9]{13}","error_msg"=>"Passport No $must_provide"),
                    "PASSPORT_FRONT_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"Passport Front Image $must_upload"),
                    "PASSPORT_BACK_IMAGE"=>array("regex"=>"[a-zA-Z]|\d|[^a-zA-Z\d]","error_msg"=>"Passport Back / B-Form Image $must_upload"),
                ),
                "guardian"=>array(
                    "FIRST_NAME"=>array("regex"=>"[A-Za-z]{2}","error_msg"=>"Guardian Name $must_provide"),
                    "RELATIONSHIP"=>array("regex"=>"[A-Za-z]{2}","error_msg"=>"Relationship Name $must_select"),
                    "MOBILE_CODE"=>array("regex"=>"[0-9]{4}","error_msg"=>"Guardian Mobile Code $must_select"),
                    "MOBILE_NO"=>array("regex"=>"[0-9]{10}","error_msg"=>"Guardian Mobile Number $must_provide"),
                    "HOME_ADDRESS"=>array("regex"=>"[A-Za-z0-9\-\\,.]+","error_msg"=>"Home Address $must_provide"),
                ),
                "qualifications"=>array(
                    "DEGREE_ID" =>$qualification,
                    "DEGREE_ID_MSG" =>$qualification_error_msg
                ),
                "or_qualifications"=>array(
                    "OR_DEGREE_ID"=>$master_id_or,
                    "OR_DEGREE_ID_MSG"=>"Bachelor 14 Year / BA / BSC / BCOM information. Please must add"
                )
            );
            return $validation_array;
    }

    private function isValidProfileInformation($user_fulldata,$application){
        //calling private method get validationArray
        $validation_array = $this->getValidationArray($application);

        $user_reg_validation = $validation_array['users_reg'];
        $guardian_validation = $validation_array['guardian'];
        $qualifications_validation = $validation_array['qualifications'];
        $qualification_error_msg = $validation_array['qualifications']['DEGREE_ID_MSG'];
        $or_qualifications_validation = $validation_array['or_qualifications']['OR_DEGREE_ID'];
        $or_qualification_error_msg = $validation_array['or_qualifications']['OR_DEGREE_ID_MSG'];

        $users_reg = $user_fulldata['users_reg'];
        $guardian = $user_fulldata['guardian'];
        $qualifications = $user_fulldata['qualifications'];



        if($users_reg['IS_CNIC_PASS']=='P'){
            $user_reg_validation = array_merge($user_reg_validation,$validation_array['PASSPORT']);
        }else{
            $user_reg_validation = array_merge($user_reg_validation,$validation_array['CNIC']);
        }

        $error = "";
        foreach($user_reg_validation as $column=>$value){


            if(preg_match("/".$value['regex']."/", $users_reg[$column])){

            }else{
                $error.="<div class='text-danger'>{$value['error_msg']}</div>";

            }
        }

        foreach($guardian_validation as $column=>$value){


            if(preg_match("/".$value['regex']."/", $guardian[$column])){

            }else{
                $error.="<div class='text-danger'>{$value['error_msg']}</div>";
            }
        }
        
        foreach($qualifications as $qual){

            foreach($qualifications_validation['DEGREE_ID'] as $k=>$val){
                if($qual['DEGREE_ID']==$val){
                    unset($qualifications_validation['DEGREE_ID'][$k]);
                    unset($qualification_error_msg[$k]);

                    break;
                }
            }
        }
        foreach ($qualification_error_msg as $error_msg){
            $error.="<div class='text-danger'>{$error_msg}</div>";
        }


            if(is_array($or_qualifications_validation)){
                $bool = true;
                foreach($qualifications as $qual){

                    foreach($or_qualifications_validation as $val){
                        if($qual['DEGREE_ID']==$val){
                            $bool = false;
                            break;
                        }
                    }
                }

                if($bool){
                    $error.="<div class='text-danger'>{$or_qualification_error_msg}</div>";
                }

            }
        return $error;
        //prePrint($qualification_error_msg);

    }
    
    private function upload_image($index_name,$image_name,$max_size = 50,$path = '../eportal_resource/images/applicants_profile_image/',$con_array=array())
    {

        $config['upload_path']          = $path;
        $config['allowed_types']        = 'gif|jpg|png|jpeg';
        $config['max_size']             = $max_size;
        $config['max_width']            = 0;
        $config['max_height']           = 0;
        $config['file_name']			= $image_name;
        $config['overwrite']			= true;

        if(isset($this->upload)){
            $this->upload =  null;
        }
        $this->load->library('upload', $config);
          //  echo "asda";
        if ( ! $this->upload->do_upload($index_name))
        {
            //echo "if";
            return array("STATUS"=>false,"MESSAGE"=>$this->upload->display_errors());
        }
        else
        {
            $image_data = $this->upload->data();

            $image_path = $image_data['full_path'];
           // echo $image_path;
            $config['image_library'] = 'gd2';
            $config['source_image'] = $image_path;
            $config['create_thumb'] = FALSE;
            if(!count($con_array)){
                $config['maintain_ratio'] = TRUE;
                $config['width']         = 180;
                $config['height']       = 260;
            }else{
                if(isset($con_array['maintain_ratio'])){
                    $config['maintain_ratio']=$con_array['maintain_ratio'];
                }

                if(isset($con_array['width'])){
                    $config['width']=$con_array['width'];
                }

                if(isset($con_array['height'])){
                    $config['height']=$con_array['height'];
                }
            }
            if(isset($this->image_lib)){
                $this->image_lib =  null;
            }
            if(isset($con_array['resize'])){
                if($con_array['resize']===true){
                    $this->load->library('image_lib',$config);

                    $this->image_lib->resize();
                }
            }else{
                $this->load->library('image_lib',$config);

                $this->image_lib->resize();

            }
            
           
            
            // $config['hostname'] = 'ftp://itsc.usindh.edu.pk';
 
            //  $config['username'] = 'itsc';
            // $config['password'] = 'imcs2468**&&';
            // $config['debug']        = false;
            
            // $connect = $this->ftp->connect($config);
 
            
            // //creating ftp path
            // $ftp_path = str_replace("..","/public_html",$path);
            //  $ftp_dir_path = rtrim($ftp_path,"/");
           
            // // $ftp_path = '/public_html/eportal_resource/foo/';
            // // $ftp_dir_path = '/public_html/eportal_resource/foo';
            
            
            // $already_exist = $this->ftp->list_files($ftp_path);
            // if($already_exist){
                
            // }else{
            // $dir  = $this->ftp->mkdir($ftp_dir_path, 0755);    
            // }

            // $up = $this->ftp->upload($path.$image_data['file_name'],$ftp_path.$image_data['file_name'], 'binary', 0775);
            
            
            // $this->ftp->close();
           // $this->CI_ftp($path,$image_data['file_name']);
 
            return array("STATUS"=>true,"IMAGE_NAME"=>$image_data['file_name']);

        }
    }
    
    private function close_registration_for_bachelor($application){
                    //  if ($application['ADMISSION_END_DATE'] < date('Y-m-d')&&$application['PROGRAM_TYPE_ID']==1&&$this->user['IS_SUPER_PASSWORD_LOGIN'] == 'N') {
                    //     $error = "<div class='text-danger'>Online Admission Form Process is CLOSED for {$application['PROGRAM_TITLE']} Degree Programs {$application['YEAR']}</div>";
                    //   $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                    //             $this->session->set_flashdata('ALERT_MSG',$alert);
                    //             redirect(base_url()."form/dashboard");
                    //             exit();
                    // }
    }
    
    private function CI_ftp($path,$name){
      $user = $this->user ;
      $date_time =date('Y F d l h:i A');
      $msg = array(
          "USER_ID"=>$user['USER_ID'],
          "FILE_NAME"=>$name,
          "DATE_TIME"=>$date_time,
          "MSG"=>""
          );
      
     $this->load->library('ftp');
             $config['hostname'] = FTP_URL;
             $config['username'] = FTP_USER;
            $config['password'] = FTP_PASSWORD;
            $config['debug']        = true;
            $connect = false;
            for($i=1;$i<=3;$i++){
                $connect = $this->ftp->connect($config);    
                if($connect){
                    break;
                }
            }
            if(!$connect){
                $msg['MSG'] = 'CONNECTION FAILED';
                $msg = json_encode($msg);
                writeQuery($msg);
                $this->ftp->close();
                return false;
            }
              
             $ftp_path = str_replace("..","/public_html",$path);
             $ftp_dir_path = rtrim($ftp_path,"/");
           
            // $ftp_path = '/public_html/eportal_resource/foo/';
            // $ftp_dir_path = '/public_html/eportal_resource/foo';
            
            
            
            $already_exist = $this->ftp->list_files($ftp_path);
            
            if($already_exist){
                
            }else{
            $dir  = $this->ftp->mkdir($ftp_dir_path, 0755);    
            }

            $up = $this->ftp->upload($path.$name,$ftp_path.$name, 'binary', 0775);
            if(!$up){
                $msg['MSG'] = 'UPLOADING FAILED';
                $msg = json_encode($msg);
                $this->ftp->close();
                writeQuery($msg);
               
             return false;
            }
            
            $this->ftp->close();
            return true;
     
 }
   
    private function CI_ftp_Download($path,$name){
        $user = $this->user ;
        $date_time =date('Y F d l h:i A');
        $msg = array(
            "USER_ID"=>$user['USER_ID'],
            "FILE_NAME"=>$name,
            "DATE_TIME"=>$date_time,
            "MSG"=>""
        );

        $this->load->library('ftp');
        $config['hostname'] = FTP_URL;
        $config['username'] = FTP_USER;
        $config['password'] = FTP_PASSWORD;
        $config['debug']        = false;
        $connect = false;
        for($i=1;$i<=3;$i++){
            $connect = $this->ftp->connect($config);
            if($connect){
                break;
            }
        }
        if(!$connect){
            $msg['MSG'] = 'CONNECTION FAILED';
            $msg = json_encode($msg);
            writeQuery($msg);
            $this->ftp->close();
            return false;
        }

        $ftp_path = str_replace("..","/public_html",$path);
        $ftp_dir_path = rtrim($ftp_path,"/");

        // $ftp_path = '/public_html/eportal_resource/foo/';
        // $ftp_dir_path = '/public_html/eportal_resource/foo';



        // $already_exist = $this->ftp->list_files($ftp_path);

        // if($already_exist){

        // }else{
        //     $dir  = $this->ftp->mkdir($ftp_dir_path, 0755);
        // }
//        prePrint($ftp_path.$name);
//        prePrint($path.$name);
//        exit();

        $up = $this->ftp->download($ftp_path.$name,$path.$name, 'binary');
        if(!$up){
            $msg['MSG'] = 'Downloading FAILED';
            $msg = json_encode($msg);
            $this->ftp->close();
            writeQuery($msg);

            return false;
        }

        $this->ftp->close();
        return true;

    }
   
     /*
     * Yasir Added following code 01-02-2020
     * */

	public function add_evening_category(){
        $this->block_for_test();
		if($this->session->has_userdata('APPLICATION_ID')) {
			$APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

			$user = $this->session->userdata($this->SessionName);
			$user = $this->User_model->getUserById($user['USER_ID']);
			$CANDIDATE_USER_ID = $user['USER_ID'];

			$data['user'] = $user;
			$data['APPLICATION_ID'] = $APPLICATION_ID;
			$application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            $this->close_registration_for_bachelor($application);
			if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
				redirect(base_url('form/dashboard'));
				exit();
			}

//			if($application['STATUS_ID']<2){
		//		redirect(base_url('form/dashboard'));
		//		exit();
//			}
			if ($application) {
			 //       if($application['PROGRAM_TYPE_ID']==2){
			 //            $error = "<div class='text-danger'>Evening Master Admission Closed</div>";
				// 			$alert = array('MSG'=>$error,'TYPE'=>'ALERT');
				// 			$this->session->set_flashdata('ALERT_MSG',$alert);
    //                             	redirect(base_url('form/dashboard'));
    //                                 exit();
			 //       }
				$form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

				$degree_list = array(
					'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
					'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
				);

				//$form_data = json_decode($application['FORM_DATA'],true);
				$bool = false;
				$valid_qualification = null;
				if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
					// echo "bach";
					foreach ($form_data['qualifications'] as $qualification){
						if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
							$bool  = true;
							$valid_qualification = $qualification;
							break;
						}
					}
				}else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
				foreach ($form_data['qualifications'] as $k=>$qualification){
						if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
							$bool  = true;
							if($k==0){
								$valid_qualification = $qualification;
							}
							if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
								$valid_qualification = $qualification;
							}
							//break;
						}
					}
				}

					$data['profile_url'] = $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
					//$data['application'] = $application;
					if($bool&&$valid_qualification!=null){
						$data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];
						$applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
						$minorMappingIds  = array();
						foreach ($applicantsMinors as $applicantsMinor){
							$minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
						}
						if(count($minorMappingIds)==0){
							echo "Please Must select Minor Subject";
							$error = "<div class='text-danger'> Please Must select Minor Subject</div>";
							$alert = array('MSG'=>$error,'TYPE'=>'ALERT');
							$this->session->set_flashdata('ALERT_MSG',$alert);
							redirect(base_url('form/select_subject'));
						}

						$list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
						$valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);
						$program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);

						$data['VALID_PROGRAM_LIST'] =$valid_program_list;
						$data['list_of_category'] =$list_of_categoy;
						$data['PROGRAM_LIST'] =$program_list;
						$data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
						 

						$candidate_evening_category = findObjectinList($list_of_categoy,'FORM_CATEGORY_ID',SELF_FINANCE_EVENING);
						if (is_array($candidate_evening_category)){
							$evening_choice_bool = true;
						}else{
							$category_array = array (
								'USER_ID'=>$CANDIDATE_USER_ID,
								'APPLICATION_ID'=>$APPLICATION_ID,
								'FORM_CATEGORY_ID'=>SELF_FINANCE_EVENING,
								'IS_ENABLE'=>'Y',
							);
							$success_category = $this->Administration->insert($category_array,'application_category');
							if ($success_category){
								$evening_choice_bool = true;
							}else{
								exit("Error: while adding your evening category");
							}
						}
						if ($evening_choice_bool){
							redirect(base_url($this->SelfController.'/evening_choices'));
						}
					}else{
						echo "Invalid Degree - Please select/add appropriate Degree";
					}
					// prePrint($application);

			} else {
				echo "This Application Id is not associated with you.";
			}
		}else{
			echo "Application Id Not Found";
		}
	}

	function evening_choices(){
       $this->block_for_test();
		if($this->session->has_userdata('APPLICATION_ID')) {
			$APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

			$user = $this->session->userdata($this->SessionName);
			$user = $this->User_model->getUserById($user['USER_ID']);

			$data['user'] = $user;
			$data['APPLICATION_ID'] = $APPLICATION_ID;
			$application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
	if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
				redirect(base_url('form/dashboard'));
				exit();
			}
// 			redirect(base_url('form/dashboard'));
// 			exit();
			if ($application) {
			    $show_program = $hide_program = array();
                            if($application['PROGRAM_TYPE_ID']==2){
    //                         $error = "<div class='text-danger'>Evening Master Admission Closed</div>";
				// 			$alert = array('MSG'=>$error,'TYPE'=>'ALERT');
				// 			$this->session->set_flashdata('ALERT_MSG',$alert);
    //                             	redirect(base_url('form/dashboard'));
    //                                 exit();
    
    //vikesh open evening programs master
    
							        //$show_program = array("1"=>array(50,156,270,271,281),
							        //"3"=>array(270),
                                    //"7"=>array(270));
							       // $show_program = $show_program[$application['CAMPUS_ID']];
							    }
							  if($application['PROGRAM_TYPE_ID']==1){
							      //$hide_program = array(264,110,8,260,263,265,81,14,101,99,19,106,22,9,234);  //vikesh block evening programs bachelor
							  }  
				//form close from bachelor
				$this->close_registration_for_bachelor($application);


				$form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

				$degree_list = array(
					'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
					'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
				);

				//$form_data = json_decode($application['FORM_DATA'],true);
				$bool = false;
				$valid_qualification = null;
				if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
					// echo "bach";
					foreach ($form_data['qualifications'] as $qualification){
						if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
							$bool  = true;
							$valid_qualification = $qualification;
							break;
						}
					}
				}
				else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
					//echo "master";
					//4
					// prePrint($form_data['qualifications']);



					foreach ($form_data['qualifications'] as $k=>$qualification){
						if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
							$bool  = true;
							if($k==0){
								$valid_qualification = $qualification;
							}
							if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
								$valid_qualification = $qualification;
							}

							//break;
						}
					}
				}


//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }

					$data['profile_url'] = $this->profile;

					if($bool&&$valid_qualification!=null){

						//  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);

						$data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

						$applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
						$minorMappingIds  = array();

						foreach ($applicantsMinors as $applicantsMinor)
						{
							$minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
						}

						if(count($minorMappingIds)==0){
							echo "Please Must select Minor Subject";
							$error = "<div class='text-danger'>Please must select Minor Subjects</div>";
							$alert = array('MSG'=>$error,'TYPE'=>'ALERT');
							$this->session->set_flashdata('ALERT_MSG',$alert);
							redirect(base_url('form/select_subject'));
						}
						$list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);
						$is_valid = false;
						if(count($list_of_categoy)==0){
							echo "Please must Save Category";
							$error = "<div class='text-danger'>Please must save Category</div>";
							$alert = array('MSG'=>$error,'TYPE'=>'ALERT');
							$this->session->set_flashdata('ALERT_MSG',$alert);
							redirect(base_url('form/add_evening_category'));
							exit();
						}else{
						    foreach($list_of_categoy as $cat_obj){
						        if($cat_obj['FORM_CATEGORY_ID']==7){
						          $is_valid = true;  
						        }
						        
						    }
						}
						if($is_valid==false){
						    echo "Please must Save Category";
							$error = "<div class='text-danger'>Please must save Category</div>";
							$alert = array('MSG'=>$error,'TYPE'=>'ALERT');
							$this->session->set_flashdata('ALERT_MSG',$alert);
							redirect(base_url('form/add_evening_category'));
							exit();
						}
						$valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);
                        //prePrint($valid_program_list);
                        //prePrint($minorMappingIds);
                        //exit();
						$prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (EVENING_SHIFT_ID,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);

						$valid_exact_program = array();
						foreach($prog_list_by_shift as $prog_list){
							foreach ($valid_program_list as $valid_program){
                            
                                if(count($show_program)){
    							   if(!in_array($prog_list['PROG_LIST_ID'],$show_program)){
    							     //  prePrint("test". $prog_list['PROG_LIST_ID']);
    							       continue;
    							       
    							   }
                                }
    							if(count($hide_program)){
    							       if(in_array($prog_list['PROG_LIST_ID'],$hide_program)){
    							     //  prePrint("test". $prog_list['PROG_LIST_ID']);
    							       continue;
    							       
    							   }
    							   }
								if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
									$valid_exact_program[]=$valid_program;
								}
							}
						}

						$CHOOSEN_PROGRAM_LIST = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['USER_ID'],$APPLICATION_ID,EVENING_SHIFT_ID);
						$lat_info = $this->Application_model->getLatInfoByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);
						
						//$program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);

                        $program_list = $prog_list_by_shift;

						$data['VALID_PROGRAM_LIST'] =$valid_exact_program;
						$data['PROGRAM_LIST'] =$program_list;
						$data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
						$data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST;
						$data['lat_info'] =$lat_info;
						 $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST;
                        $data['lat_info'] =$lat_info;
                      //  $data['user'] =$form_data;
                      $data['form_data'] =$form_data;
                        $data['application'] =$application;
                        $data['category'] =$list_of_categoy;
						

						$precentage = ($valid_qualification['OBTAINED_MARKS']*100/$valid_qualification['TOTAL_MARKS']);
						$data['precentage'] =round($precentage,2);


						$this->load->view('include/header', $data);
						$this->load->view('include/preloder');
						$this->load->view('include/side_bar', $data);
						$this->load->view('include/nav', $data);
						$this->load->view('evening_choice_list_candidate', $data);
						$this->load->view('include/footer_area', $data);
						$this->load->view('include/footer', $data);

					}else{
					      redirect(base_url()."candidate/add_inter_qualification");
						echo "Invalid Degree Please must add appropriate degree";
					}


			} else {
				echo "this application id is not associate with you";
			}
		}else{
			echo "Application Id Not Found";
		}
	}

	public function upload_evening_choices(){

//		prePrint($_POST);
//		exit();
		$error="";
		$config_a = array();
		$config_a['maintain_ratio'] = true;
		$config_a['width']         = 360;
		$config_a['height']       = 500;
		$config_a['resize']       = false;
		$reponse['RESPONSE'] = "ERROR";


		if($this->session->has_userdata('APPLICATION_ID')) {
      
			$APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

			$user = $this->session->userdata($this->SessionName);
			$user = $this->User_model->getUserById($user['USER_ID']);

			$data['user'] = $user;
			$data['APPLICATION_ID'] = $APPLICATION_ID;

			$application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
			
			if(OPEN_EVENING_PORTAL == 0){
				redirect(base_url('form/dashboard'));
				exit();
			}
			
			if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
				redirect(base_url('form/dashboard'));
				exit();
			}
			if ($application) {
				$form_data = $this->User_model->getUserFullDetailById($user['USER_ID'],$APPLICATION_ID);

				$degree_list = array(
					'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
					'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>array(4,5,6))
				);

				//$form_data = json_decode($application['FORM_DATA'],true);
				$bool = false;
				$valid_qualification = null;
				if($application['PROGRAM_TYPE_ID']==$degree_list['BACHELOR']['PROGRAM_TYPE_ID']){
					// echo "bach";
					foreach ($form_data['qualifications'] as $qualification){
						if($qualification['DEGREE_ID'] ==$degree_list['BACHELOR']['DEGREE_ID']){
							$bool  = true;
							$valid_qualification = $qualification;
							break;
						}
					}
				}
				else if($application['PROGRAM_TYPE_ID']==$degree_list['MASTER']['PROGRAM_TYPE_ID']){
					foreach ($form_data['qualifications'] as $k=>$qualification){
						if(in_array($qualification['DEGREE_ID'] ,$degree_list['MASTER']['DEGREE_ID'])){
							$bool  = true;
							if($k==0){
								$valid_qualification = $qualification;
							}
							if($qualification['DEGREE_ID']>$valid_qualification['DEGREE_ID']){
								$valid_qualification = $qualification;
							}
						}
					}
				}
                      

//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


					$data['profile_url'] = $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
					//$data['application'] = $application;
					if($bool&&$valid_qualification!=null){

						//  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);

						$data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

						$applicantsMinors = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
						$minorMappingIds  = array();

						foreach ($applicantsMinors as $applicantsMinor){
							$minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
						}

						if(count($minorMappingIds)==0){
							$error .= "<div class='text-danger'>Minor Not Found</div>";
						}

						$valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);

						$prog_list_by_shift       = $this->Administration->getProgListByShiftAndProgTypeAndCampusId (EVENING_SHIFT_ID,$application['PROGRAM_TYPE_ID'],$application['CAMPUS_ID']);

						$valid_exact_program = array();
						foreach($prog_list_by_shift as $prog_list){
							foreach ($valid_program_list as $valid_program){
								if($prog_list['PROG_LIST_ID']==$valid_program['PROG_LIST_ID']){
									$valid_exact_program[]=$valid_program;
								}
							}
						}
						$valid_program_list = $valid_exact_program;

						//$program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
						//prePrint($valid_program_list);
						$min_choice = 0;
						$max_choice = 0;
						$choice_list= array();
						$llb_validation = false;
						if(isset($_POST['minor_subject_array'])){
							$choice_list = $_POST['minor_subject_array'];

						}else{
							//$error .= "<div class='text-danger'>You muct select at least one Degree Program</div>";
						}

						if($application['PROGRAM_TYPE_ID'] == 1){
							$max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
							$min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
							if(in_array(LLB_PROG_LIST_ID,$choice_list)){
								$min_choice = 1;
								$llb_validation = true;
							}
						}else if($application['PROGRAM_TYPE_ID'] == 2){
							$max_choice = CHOICE_QUANTITY_FOR_MASTER_MAX;
							$min_choice = CHOICE_QUANTITY_FOR_MASTER_MIN;
						} else if($application['PROGRAM_TYPE_ID'] == 3){
                            $max_choice = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
                            $min_choice = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
                            
                        }
						if($llb_validation){
							if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
								$TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
							} else {
								$error .= "<div class='text-danger'>Must Enter Ticket Number / Seat Number</div>";
							}

							if(isset($_POST['TEST_DATE'])&&isValidTimeDate($_POST['TEST_DATE'],'d/m/Y')){
								$TEST_DATE = getDateForDatabase($_POST['TEST_DATE']);
								if($TEST_DATE>date('Y-m-d')){
									$error.="<div class='text-danger'>Choose Valid Test Date</div>";
								}
							}else{
								$error.="<div class='text-danger'>Test Must be Choose</div>";
							}

							if (isset($_POST['TEST_SCORE']) && isValidData($_POST['TEST_SCORE'])) {
								$TEST_SCORE = strtoupper(isValidData($_POST['TEST_SCORE']));
								if($TEST_SCORE<0||$TEST_SCORE>100){
									$error .= "<div class='text-danger'>Invalid Test Score</div>";
								}
							} else {
								$error .= "<div class='text-danger'>Enter Test Score</div>";
							}

							$result_card_image= "";

							if (isset($_POST['result_card_image1']) && isValidData($_POST['result_card_image1'])) {
								$result_card_image = strtoupper(isValidData($_POST['result_card_image1']));
							}

							$user_id = $user['USER_ID'];
							if (isset($_FILES['result_card_image'])) {
								if (isValidData($_FILES['result_card_image']['name'])) {

									$file_path = EXTRA_IMAGE_CHECK_PATH . "$user_id/";
									$image_name = "lat_result_card_image_$user_id";
									$res = $this->upload_image('result_card_image', $image_name, $this->file_size, $file_path, $config_a);
									if ($res['STATUS'] === true) {
										$result_card_image = "$user_id/" . $res['IMAGE_NAME'];
										$is_upload_any_doc = true;

									} else {
										$error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
									}
								} else {
									if ($result_card_image == "")
										$error .= "<div class='text-danger'>Must upload Result Card Image. Image size must be less than 500kb </div>";
								}
							}
							else {

								if ($result_card_image == "")
									$error .= "<div class='text-danger'>Must upload Result Card Image. Image size must be less than 500kb </div>";
							}
						}

						if($error==""){
							if($llb_validation){
								$lat_info = array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,"TOKEN_NO"=>$TOKEN_NO,"TEST_DATE"=>$TEST_DATE,"TEST_SCORE"=>$TEST_SCORE,"RESULT_IMAGE"=>$result_card_image);
							}else{
								$lat_info = null;
							}
                            
							if(count($choice_list)>=0&&count($choice_list)<=$max_choice&&$min_choice<=count($choice_list)){
								$check_valid = true;
								foreach($choice_list as $choice){
									$check_id_valid = false;
									foreach ($valid_program_list as $valid_program){

										if($choice==$valid_program['PROG_LIST_ID']){
											$check_id_valid = true;
											break;
										}
									}
									if($check_id_valid ==false){
										$check_valid = false;
										break;
									}
								}

								if($check_valid==true){

									$list_of_choice = array();

									foreach($choice_list as $CHOICE_NO => $PROG_LIST_ID){
										$CHOICE_NO++;
										$list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>$PROG_LIST_ID,'CHOICE_NO'=>$CHOICE_NO,'SHIFT_ID'=>EVENING_SHIFT_ID);
									}

									//prePrint($list_of_choice);
									if(count($list_of_choice)==0){
									    $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'SHIFT_ID'=>EVENING_SHIFT_ID);
									}
									if(count($list_of_choice)>=0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info)){

									}
									else{
										$error .= "<div class='text-danger'>Your choices are not added or updated. Kindly contact technical team.</div>";
									}
								}
								else{
									$error .= "<div class='text-danger'>Your choices are invalid. Kindly contact technical team.</div>";
								}
							}else{
								$error .= "<div class='text-danger'>You must choice minimum $min_choice and maximum $max_choice</div>";
							}
						}


						if($error==""){
							$reponse['RESPONSE']="SUCCESS";
							$reponse['MESSAGE']="Successfully update information";
						}else{
							$reponse['RESPONSE']="ERROR";
							$reponse['MESSAGE']=$error;
						}
					}else{
						echo "Invalid Degree Please must add appropriate degree";
						$error .= "<div class='text-danger'>Invalid Degree Please must add appropriate degree</div>";
						exit();
					}
					// prePrint($application);

			}else {
				echo "this application id is not associate with you";
				$error .= "<div class='text-danger'>this application id is not associate with you</div>";
				exit();
			}
		}else{
			//echo "Application Id Not Found";
			$error .= "<div class='text-danger'>Application Id Not Found</div>";
			//exit();
		}

		if($error!=""){
			$reponse['RESPONSE']="ERROR";
			$reponse['MESSAGE']=$error;

		}
       
		if ($reponse['RESPONSE'] == "ERROR") {
			$this->output
				->set_status_header(500)
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($reponse));
		} else {
			$this->output
				->set_status_header(200)
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($reponse));
		}
	}

    /*
    Yasir added following code on 22-03-2021 
    */
    
    public function getApplicantCurrentSelection(){

		$user = $this->session->userdata($this->SessionName);
		$user_id=$user['USER_ID'];
		$application_id=0;
		$selection_list_id=0;
		if ($user_id<0){
			http_response_code(201);
			echo "Sorry Applicant not found";
			exit();
		}
		$data = $this->Application_model->getApplicantCurrentAdmission($user_id,$application_id,$selection_list_id);
		if (count($data) ==0){
			http_response_code(201);
			echo "Sorry data not found";
			exit();
		}else{
			$new_array = array();
			foreach ($data as $std){
				$ap_id = $std['APPLICATION_ID'];
				$SELECTION_LIST_ID = $std['SELECTION_LIST_ID'];
				$roll_no = $this->Application_model->getCandidateRollNo($SELECTION_LIST_ID);
				$SELECTION_LIST_ID = base64url_encode(base64_encode(urlencode($SELECTION_LIST_ID)));
				if ($ap_id>0){
					$form_status = "Submitted";
				}else{
					$form_status = "Not Submitted Yet";
				}
				$std['SELECTION_LIST_ID']=$SELECTION_LIST_ID;
				$std['FORM_STATUS']=$form_status;
				$std['ROLL_NO']=$roll_no['ROLL_NO'];
				array_push($new_array,$std);
			}
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($new_array));
		}
	}

	public function update_contact_information(){
        $user = $this->session->userdata($this->SessionName);
        if($user){
            $user = $this->User_model->getUserById($user['USER_ID']);
            $data['user'] = $user;
            $data['profile_url'] = $this->profile;

            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('profile_section/update_contact_information',$data);
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }
	}
	public function update_contact_info(){
        $error = "";
        $user = $this->session->userdata($this->SessionName);
        if(isset($_POST['MOBILE_NO'])&&isValidData($_POST['MOBILE_NO'])){
            $MOBILE_NO = isValidData($_POST['MOBILE_NO']);
            $firstCharacter = $MOBILE_NO[0];

            if($firstCharacter ==0){
                $MOBILE_NO=  substr($MOBILE_NO ,1);
            }

            if(strlen($MOBILE_NO)!=10){
                $error.="<div class='text-danger'>Invalid Mobile</div>";
            }

        }else{
            $error.="<div class='text-danger'>Mobile Must be Enter</div>";
        }
        if(isset($_POST['WHATSAPP_NO'])&&isValidData($_POST['WHATSAPP_NO'])){
            $WHATSAPP_NO = isValidData($_POST['WHATSAPP_NO']);

            if(strlen($WHATSAPP_NO)!=11){
                $error.="<div class='text-danger'>Invalid WhatsApp No. WhatsApp No. must start with 0 and must have 11 digits for example (03421234567)</div>";
            }

        }else{
            $error.="<div class='text-danger'>Whatsapp No Must be Enter</div>";
        }
        if(isset($_POST['EMAIL'])&&isValidEmail($_POST['EMAIL'])){
            $email = strtolower(isValidData($_POST['EMAIL']));
        }else{
            $error.="<div class='text-danger'>Email  Must be Enter</div>";
        }

        if($error==""){
            $form_array = array(
                "MOBILE_NO"=>$MOBILE_NO,
                "WHATSAPP_NO"=>$WHATSAPP_NO,
                "EMAIL"=>$email
            );

            $res = $this->User_model->updateUserById($user['USER_ID'],$form_array);
            if($res===-1){
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
                $alert = array('MSG'=>$reponse['MESSAGE'],'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/update_contact_information");
            }
            if($res===0){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>No data has been changed..!<div>";
                $alert = array('MSG'=>$reponse['MESSAGE'],'TYPE'=>'SUCCESS');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/update_contact_information");
            }else{
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Save..!<div>";
                $alert = array('MSG'=>$reponse['MESSAGE'],'TYPE'=>'SUCCESS');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/update_contact_information");
            }
        }else{
            $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/update_contact_information");
        }
    }
    private function block_for_test(){
         $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
          $user = $this->session->userdata($this->SessionName);
          $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
         if($application && $application['PROGRAM_TYPE_ID']>=2){
             
         }else{
             if($application['CHALLAN_IMAGE']){
                 if(!$application['BACHELOR_FORM_COMPLETE']){
                     $alert = array('MSG'=>$this->notice,'TYPE'=>'NOTICE');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/dashboard");
                    exit(); 
                 }
                    
             }else{
                 $alert = array('MSG'=>"<div class='text-danger'><h1>Must Upload Challan Detail.</h1></div>",'TYPE'=>'NOTICE');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."form/upload_application_challan");
                exit();
             }
            
         }
          
    }
}

