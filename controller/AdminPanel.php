<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 9/16/2020
 * Time: 10:28 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';
class AdminPanel extends AdminLogin
{
    private $script_name = "";
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Administration');
        $this->load->model('log_model');
        $this->load->model('Api_qualification_model');
        $this->load->model('Api_location_model');
        $this->load->model('User_model');
        $this->load->model('Application_model');
        $this->load->model('Admission_session_model');
        $this->load->model('TestResult_model');
        $this->load->model('AdmitCard_model');
        $this->load->model('FeeChallan_model');
        $this->load->model('Prerequisite_model');
        $this->load->model('Selection_list_report_model');
        
        
        $this->load->model('Web_model');
        
        
       
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

//		$this->load->library('javascript');
        $self = $_SERVER['PHP_SELF'];
        $self = explode('index.php/',$self);
        $this->script_name = $self[1];
            $ids = array(8);
            if(in_array($role_id,$ids)){
                //prePrint($this->script_name);
                $self = explode('AdminPanel/set_application_id/',$this->script_name);
                if(count($self)==1){
                $paths = array("AdminPanel/search_student_by_cnic","AdminPanel/get_basic_information","AdminPanel/set_application_id");
                    if(!in_array($this->script_name,$paths )){
                        exit("<h1>Action prohibited</h1>");
                    }    
                }
                
            } 
        
        $this->verify_login();
    }

    
  
    public function dashboard(){
        $this->search_student_by_cnic();
    }
    public function search_student_by_cnic(){

        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // prePrint($side_bar_data);
        // exit();
        $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;


        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/search_user');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }

    public function get_basic_information(){

        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;


        if(isset($_POST['CNIC_NO']) && isValidData($_POST['CNIC_NO']) && isset($_POST['SEARCH_BY']) && isValidData($_POST['SEARCH_BY'])){
            $search_value = isValidData($_POST['CNIC_NO']);
            $SEARCH_BY = isValidData($_POST['SEARCH_BY']);
            
            if($SEARCH_BY == "APPLICATION_ID")
            {
                $application_data = $this->Application_model->getApplicationByApplicationID($search_value);
                if(!$application_data)
                {
                      $reponse = "<div class='text-danger'>Sorry record not found by given application ID</div>";
                        http_response_code(405);
                        exit($reponse);
                }
                $user = $this->User_model->getUserById($application_data['USER_ID']); 
             z
                
            }elseif($SEARCH_BY == "CNIC"){
                $CNIC_NO = $search_value;
                 $user = $this->User_model->getUserByCnic($CNIC_NO); 
            }elseif($SEARCH_BY == "PASSPORT"){
                $PASSPORT_NO = $search_value;
                 $user = $this->User_model->getUserByPassportNo($PASSPORT_NO);
            }elseif($SEARCH_BY =="USER_ID"){
                 $USER_ID = $search_value;
               $user = $this->User_model->getUserById($USER_ID); 
            }
            
            
           
            if($user){

                $data['user_application_list'] = $this->Application_model->getApplicationByUserId($user['USER_ID']);
                $data['user'] = $user;

                $this->session->set_userdata('STUDENT_USER_ID', $user['USER_ID']);
                $data['ROLE_ID'] = $role_id;
                
                $this->db->from('districts');
                $this->db->order_by("DISTRICT_NAME", "asc");
                $data['districts'] = $this->db->get()->result();
                
                $this->load->view('admin/candidate_basic_information',$data);
            }else{
                $reponse = "<div class='text-danger'>Sorry Record not found</div>";
                http_response_code(405);
                exit($reponse);
            }
        }else{
            $reponse = "<div class='text-danger'>Invalid parameters given</div>";
            http_response_code(405);
            exit($reponse);
        }

    }

    public function forget_user_password(){
        
        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        //exit("yes");
        // $this->verify_path($this->script_name,$side_bar_data);

        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->userdata('STUDENT_USER_ID')) {
            
            
            $CNIC_NO = $MOBILE_NO = $EMAIL=$error = "";

            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
              $user = $this->User_model->getUserByIdForAdmin($USER_ID);
             $token = rand(10000000,99999999);
            $code = cryptPassowrd($token);
            $password= $code;
            $form_array = array("PASSWORD"=>$password);
             $res = $this->User_model->updateUserById($USER_ID, $form_array, $admin['USER_ID']);


                if ($res === -1 ) {
                    $reponse['RESPONSE'] = "ERROR";
                    $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
                     $this->output
                ->set_status_header(400)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
                }
                if ($res === 0 ) {
                   sendPasswordByEmail($user['EMAIL'],$token,$this);
                    $reponse['RESPONSE'] = "SUCCESS";
                    $reponse['MESSAGE'] = "<div class='text-success'>New Password is: <b>$token</b> <div>";
                     $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
                } else {
                    sendPasswordByEmail($user['EMAIL'],$token,$this);
                    $reponse['RESPONSE'] = "SUCCESS";
                    $reponse['MESSAGE'] = "<div class='text-success'>New Password is: <b>$token</b><div>";
                     $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
                }
                 

        }else{
              $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "<div class='text-danger'>User Not Found</div>";
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }
  
    public function basic_info_form_handler(){
        
        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];
        
        // $this->verify_path($this->script_name,$side_bar_data);

        if($this->session->has_userdata('STUDENT_USER_ID') && $this->session->userdata('STUDENT_USER_ID')) {

            $FIRST_NAME = $FNAME = $LAST_NAME = $DISTRICT_ID = $CNIC_NO = $MOBILE_NO = $EMAIL=$error = "";

            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $user = $this->User_model->getUserByIdForAdmin($USER_ID);

            if(!$user){
                $error .= "<div class='text-danger'>User Not Found </div>";
            }
            
            if (isset($_POST['FIRST_NAME']) && isValidData($_POST['FIRST_NAME'])) {
                $FIRST_NAME = strtoupper(isValidData($_POST['FIRST_NAME']));
            } else {
                $error .= "<div class='text-danger'>First Name Must be Enter</div>";
            }
            
            if (isset($_POST['FNAME']) && isValidData($_POST['FNAME'])) {
                $FNAME = strtoupper(isValidData($_POST['FNAME']));
            } else {
                $error .= "<div class='text-danger'>Father Name Must be Enter</div>";
            }

            if (isset($_POST['LAST_NAME']) && isValidData($_POST['LAST_NAME'])) {
                $LAST_NAME = strtoupper(isValidData($_POST['LAST_NAME']));
            } else {
                $error .= "<div class='text-danger'>Last Name Must be Enter</div>";
            }

            if (isset($_POST['DISTRICT_ID']) && isValidData($_POST['DISTRICT_ID'])) {
                $DISTRICT_ID = strtoupper(isValidData($_POST['DISTRICT_ID']));
            } else {
                $error .= "<div class='text-danger'District Must be Select</div>";
            }

            if (isset($_POST['EMAIL']) && isValidData($_POST['EMAIL'])) {
                $EMAIL = strtolower(isValidData($_POST['EMAIL']));
                $this->form_validation->set_rules('EMAIL', 'EMAIL', 'required|valid_email');
                if ($this->form_validation->run() == false) {
                    $error .= "<div class='text-danger'>Please Provide Valid Email</div>";
                }
            } else {
                $error .= "<div class='text-danger'>Email Must be Enter</div>";
            }
            if (isset($_POST['CNIC_NO']) && isValidData($_POST['CNIC_NO'])) {
                $CNIC_NO = isValidData($_POST['CNIC_NO']);

            } else {
                $error .= "<div class='text-danger'>CNIC Must be Enter</div>";
            }
            if (isset($_POST['MOBILE_NO']) && isValidData($_POST['MOBILE_NO'])) {
                $MOBILE_NO = isValidData($_POST['MOBILE_NO']);
                if (strlen($MOBILE_NO) >= 12 || strlen($MOBILE_NO) <= 9) {
                    $error .= "<div class='text-danger'>Invalid Mobile</div>";
                } else {
                    $firstCharacter = $MOBILE_NO[0];

                    if ($firstCharacter == 0) {
                        $MOBILE_NO = substr($MOBILE_NO, 1);
                    }
                }
            } else {
                $error .= "<div class='text-danger'>Mobile Must be Enter</div>";
            }

            $form_array = array(
                "FIRST_NAME" => $FIRST_NAME,
                "FNAME" => $FNAME,
                "LAST_NAME" => $LAST_NAME,
                "MOBILE_NO" => $MOBILE_NO,
                "EMAIL" => $EMAIL,
                "CNIC_NO" => $CNIC_NO,
                "DISTRICT_ID" => $DISTRICT_ID,
            );
            $user_cnic = $this->User_model->getUserByCnic($CNIC_NO);
            if(count($user_cnic)&&$user_cnic['USER_ID']!=$USER_ID){
                $error .= "<div class='text-danger'>This Cnic No is Already Associate Another person</div>";
            }
            if ($error == "") {

                $res = $this->User_model->updateUserById($USER_ID, $form_array, $admin['USER_ID']);


                if ($res === -1 ) {
                    $reponse['RESPONSE'] = "ERROR";
                    $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
                }
                else if ($res === 0 ) {
                    $reponse['RESPONSE'] = "SUCCESS";
                    $reponse['MESSAGE'] = "<div class='text-success'>No data has been changed..!<div>";
                } else {
                    $reponse['RESPONSE'] = "SUCCESS";
                    $reponse['MESSAGE'] = "<div class='text-success'>Successfully Save ..!<div>";
                }

            } else {
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = $error;
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
        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "<div class='text-danger'>User Not Found</div>";
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }//else
    }//method

    public function set_application_id($APPLICATION_ID,$url){
        $APPLICATION_ID = base64_decode(urldecode($APPLICATION_ID));
        $this->session->set_userdata('STUDENT_APPLICATION_ID', $APPLICATION_ID);
        $url = base_url() . base64_decode(urldecode($url));
        redirect($url);
        exit();
    }
    // created by javed
    public function student_update()
    {
        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        $blood_groups=array("A+","A-","B+","B-","O+","O-","AB+","AB-");
        $GENDER=array('M'=>"MALE","F"=>"FEMALE");
        $area=array('R'=>"RURAL","U"=>"URBAN");
        $RELIGIONS=array("ISLAM","HINDUISM","CHRISTIAN","OTHER");
        $REL_GUARD=array('FATHER'=>"FATHER","MOTHER"=>"MOTHER","BROTHER"=>"BROTHER","SISTER"=>"SISTER","UNCLE"=>"UNCLE","AUNTY"=>"AUNTY","GRAND FATHER"=>"GRAND FATHER","GRAND MOTHER"=>"GRAND MOTHER","OTHER"=>"OTHER");
        $OCC_GUARD=array('BUSSINESS MAN'=>"BUSSINESS MAN","ENGINEER"=>"ENGINEER","DOCTOR"=>"DOCTOR","FARMER"=>"FARMER","GOVERMENT EMPLOYEE"=>"GOVERMENT EMPLOYEE","PRIVATE COMPANY EMPLOYEE"=>"PRIVATE COMPANY EMPLOYEE","LANDLOARD"=>"LANDLOARD","RETIRED"=>"RETIRED","OTHER"=>"OTHER");


//        $next_page =urldecode($next_page);
//        $next_page = base64_decode($next_page);


        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')){

            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

//            prePrint($USER_ID);
            $user_fulldata = $this->User_model->getUserFullDetailById($USER_ID,$APPLICATION_ID);
//prePrint($user_fulldata);
            $data['user'] = $user_fulldata;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($USER_ID, $APPLICATION_ID);
            /* array set */
//            $family_info  =  $this->User_model->getGuardianByUserId($USER_ID);
//            print_r($family_info);
            //$application = $this->Application_model->getApplicationByUserAndApplicationId($USER_ID['USER_ID'],$APPLICATION_ID);
            $countries =$this->Api_location_model->getAllCountry();
            
            $bank_branches = $this->Admission_session_model->getAllBankInformation();
            $data['user'] = $USER_ID;
            // $data['profile_url'] = $this->profile;
            $data['countries'] = $countries;
            //$data['prefixs'] = $prefixs;
            $data['blood_groups'] = $blood_groups;
//            $data['family_info'] = $family_info;
            $data['REL_GUARD'] = $REL_GUARD;
            $data['OCC_GUARD'] = $OCC_GUARD;
            $data['GENDER'] = $GENDER;
            $data['area'] = $area;
            $data['RELIGIONS'] = $RELIGIONS;
            $data['application'] = $application;

            $data['bank_branches'] = $bank_branches;
            $data['application'] = $application;

            if ($application) {

                $bank = $this->Admission_session_model->getBankInformationByBranchId($application['BRANCH_ID']);
                //$bank = $this->Admission_session_model;
                $data['user'] = $user_fulldata['users_reg'];
                $data['qualifications'] = $user_fulldata['qualifications'];
                $data['guardian'] = $user_fulldata['guardian'];
                $data['application'] = $application;
                $data['bank'] = $bank;

                $data['profile_url'] = 'candidate/profile';
                     $data['user'] =$admin;

                $this->load->view('include/header', $data);
		        $this->load->view('include/preloder');
		        $this->load->view('include/side_bar');
		        $this->load->view('include/nav',$data);
		        $data['user'] = $user_fulldata['users_reg'];
                $this->load->view('admin/student_update', $data);
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

    public function basic_info_handler(){
        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];


       // $this->verify_path($this->script_name,$side_bar_data);

        $admin_id = $admin['USER_ID'];

        $USER_ID = $this->session->userdata('STUDENT_USER_ID');
        $user = $this->User_model->getUserById($USER_ID);
        $USER_ID = $user['USER_ID'] ;
        $U_R= 0;



        $PROFILE_IMAGE = "";


        $IS_CNIC_PASS = $user['IS_CNIC_PASS'] ;
        $PASSPORT_EXPIRY=$user['PASSPORT_EXPIRY'];
        $CNIC_EXPIRY=$user['CNIC_EXPIRY'];
        $error ="";
        $GNAME =$GAURD_MOBILE_NO =$GAURD_HOME_ADDRESS = $GAURD_MOBILE_CODE =$REL_GUARD=$OCC_GUARD="";
        $RELIGION=$GENDER=$BLOOD_GROUP = $ZIP_CODE=$PERMANENT_ADDRESS=$HOME_ADDRESS=$PLACE_OF_BIRTH =$MOBILE_CODE = $FNAME =$PLACE_OF_BIRTH= $LAST_NAME= $MOBILE_NO = $PREFIX_ID = $FIRST_NAME = "";
        $PHONE = $CITY_ID =$DISTRICT_ID=$PROVINCE_ID=$COUNTRY_ID = 0;
        $PASSPORT_EXPIRY = $CNIC_EXPIRY =$DATE_OF_BIRTH='1900-01-01';
        $CNIC_NO="";
        $EMAIL="";

        if($this->session->has_userdata('STUDENT_APPLICATION_ID')){
            $STUDENT_APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
        }else{
            $error.="<div class='text-danger'>Application Id Not Found</div>";
        }


        if(isset($_POST['FIRST_NAME'])&&isValidData($_POST['FIRST_NAME'])){
            $FIRST_NAME = strtoupper(isValidData($_POST['FIRST_NAME']));
        }else{
            //$error.="<div class='text-danger'>Name Must be Enter</div>";
        }
        if(isset($_POST['PREFIX_ID'])&&isValidData($_POST['PREFIX_ID'])){
            $PREFIX_ID = isValidData($_POST['PREFIX_ID']);
        }else{
            //$error.="<div class='text-danger'>PREFIX Must be Select</div>";
        }

        if(isset($_POST['EMAIL'])&&isValidData($_POST['EMAIL'])){
            $EMAIL = strtolower(isValidData($_POST['EMAIL']));
            $this->form_validation->set_rules('EMAIL','Email','required|valid_email');
            if($this->form_validation->run()==false){
              //  $error.="<div class='text-danger'>Please Provide Valid Email</div>";
            }
        }else{
            //$error.="<div class='text-danger'>Email Must be Enter</div>";
        }
        if(isset($_POST['MOBILE_NO'])&&isValidData($_POST['MOBILE_NO'])){
            $MOBILE_NO = isValidData($_POST['MOBILE_NO']);
            if(strlen($MOBILE_NO)>=12 ||strlen($MOBILE_NO)<=9){
               // $error.="<div class='text-danger'>Invalid Mobile</div>";
            }
            $firstCharacter = $MOBILE_NO[0];

            if($firstCharacter ==0){
                $MOBILE_NO=  substr($MOBILE_NO ,1);
            }
        }else{
            //$error.="<div class='text-danger'>Mobile Must be Enter</div>";
        }


        if(isset($_POST['LAST_NAME'])&&isValidData($_POST['LAST_NAME'])){
            $LAST_NAME = strtoupper(isValidData($_POST['LAST_NAME']));
        }else{
           // $error.="<div class='text-danger'>Last Name / Surname Must be Enter</div>";
        }
        if(isset($_POST['FNAME'])&&isValidData($_POST['FNAME'])){
            $FNAME = strtoupper(isValidData($_POST['FNAME']));
        }else{
            //$error.="<div class='text-danger'>Father Name Must be Enter</div>";
        }

        if(isset($_POST['MOBILE_CODE'])&&isValidData($_POST['MOBILE_CODE'])){
            $MOBILE_CODE = isValidData($_POST['MOBILE_CODE']);
        }
        if(isset($_POST['PLACE_OF_BIRTH'])&&isValidData($_POST['PLACE_OF_BIRTH'])){
            $PLACE_OF_BIRTH = strtoupper(isValidData($_POST['PLACE_OF_BIRTH']));
        }

        if(isset($_POST['RELIGION'])&&isValidData($_POST['RELIGION'])){
            $RELIGION = isValidData($_POST['RELIGION']);
        }else{
            //$error.="<div class='text-danger'>Religion Must be Select</div>";
        }
        if(isset($_POST['HOME_ADDRESS'])&&isValidData($_POST['HOME_ADDRESS'])){
            $HOME_ADDRESS = strtoupper(isValidData($_POST['HOME_ADDRESS']));
        }else{
            //$error.="<div class='text-danger'>Home Address Must be Enter</div>";
        }
        if(isset($_POST['PERMANENT_ADDRESS'])&&isValidData($_POST['PERMANENT_ADDRESS'])){
            $PERMANENT_ADDRESS = strtoupper(isValidData($_POST['PERMANENT_ADDRESS']));
        }else{
           // $error.="<div class='text-danger'>Permanent Address Must be Enter</div>";
        }
        if(isset($_POST['COUNTRY_ID'])&&isValidData($_POST['COUNTRY_ID'])){
            $COUNTRY_ID = isValidData($_POST['COUNTRY_ID']);
        }else{
            //$error.="<div class='text-danger'>Country Must be Select</div>";
        }
        if(isset($_POST['PROVINCE_ID'])&&isValidData($_POST['PROVINCE_ID'])){
            $PROVINCE_ID = isValidData($_POST['PROVINCE_ID']);
        }else{
            //  $error.="<div class='text-danger'>Province Must be Select</div>";
        }
        if(isset($_POST['DISTRICT_ID'])&&isValidData($_POST['DISTRICT_ID'])){
            $DISTRICT_ID = isValidData($_POST['DISTRICT_ID']);
        }else{
            //$error.="<div class='text-danger'>District Must be Select</div>";
        }
        if(isset($_POST['CITY_ID'])&&isValidData($_POST['CITY_ID'])){
            $CITY_ID = isValidData($_POST['CITY_ID']);
        }else{
            //$error.="<div class='text-danger'>City Must be Select</div>";
        }

        if(isset($_POST['DATE_OF_BIRTH'])&&isValidTimeDate($_POST['DATE_OF_BIRTH'],'d/m/Y')){
            $DATE_OF_BIRTH = getDateForDatabase($_POST['DATE_OF_BIRTH']);
            if($DATE_OF_BIRTH>date('Y-m-d')){
              //  $error.="<div class='text-danger'>Choose Valid Date Of Bith</div>";
            }
        }else{
            //$error.="<div class='text-danger'>Date Of Birth Must be Choose</div>";
        }
        if(isset($_POST['ZIP_CODE'])&&isValidData($_POST['ZIP_CODE'])){
            $ZIP_CODE = strtoupper(isValidData($_POST['ZIP_CODE']));
        }
        if(isset($_POST['PHONE'])&&isValidData($_POST['PHONE'])){
            $PHONE = strtoupper(isValidData($_POST['PHONE']));
        }

        if(isset($_POST['BLOOD_GROUP'])&&isValidData($_POST['BLOOD_GROUP'])){
            $BLOOD_GROUP = isValidData($_POST['BLOOD_GROUP']);
        }else{
            //$error.="<div class='text-danger'>Blood Group Must be Select</div>";
        }
        if(isset($_POST['U_R'])&&isValidData($_POST['U_R'])){
            $U_R = isValidData($_POST['U_R']);
        }else{
            //$error.="<div class='text-danger'>Urban / Rural Must be Select</div>";
        }

        if(isset($_POST['CNIC_EXPIRY'])&&isValidTimeDate($_POST['CNIC_EXPIRY'],'d/m/Y')){
            $CNIC_EXPIRY = getDateForDatabase($_POST['CNIC_EXPIRY']);
        }
        if(isset($_POST['CNIC_NO'])&&isValidData($_POST['CNIC_NO'])){
            $CNIC_NO = isValidData($_POST['CNIC_NO']);
        }
        if(isset($_POST['PASSPORT_EXPIRY'])&&isValidTimeDate($_POST['PASSPORT_EXPIRY'],'d/m/Y')){
            $PASSPORT_EXPIRY = getDateForDatabase($_POST['PASSPORT_EXPIRY']);
        }

        if(isset($_POST['GENDER'])&&isValidData($_POST['GENDER'])){
            $GENDER = isValidData($_POST['GENDER']);
        }else{
           // $error.="<div class='text-danger'>Gender Must be select</div>";
        }

        //Guardian information
        if(isset($_POST['GNAME'])&&isValidData($_POST['GNAME'])){
            $GNAME = strtoupper(isValidData($_POST['GNAME']));
        }else{
         //   $error.="<div class='text-danger'>Guardian Name Must be Enter</div>";
        }
        if(isset($_POST['REL_GUARD'])&&isValidData($_POST['REL_GUARD'])){
            $REL_GUARD = isValidData($_POST['REL_GUARD']);
        }else{
           // $error.="<div class='text-danger'>Relationship of Guardian  Must be select</div>";
        }
        if(isset($_POST['OCC_GUARD'])&&isValidData($_POST['OCC_GUARD'])){
            $OCC_GUARD = isValidData($_POST['OCC_GUARD']);
        }else{
            //$error.="<div class='text-danger'>Occupation of Guardian Must be select</div>";
        }
        if(isset($_POST['GAURD_MOBILE_CODE'])&&isValidData($_POST['GAURD_MOBILE_CODE'])){
            $GAURD_MOBILE_CODE = isValidData($_POST['GAURD_MOBILE_CODE']);
        }else{
            //$error.="<div class='text-danger'>Guardian Mobile Code Must be select</div>";
        }
        if(isset($_POST['GAURD_MOBILE_NO'])&&isValidData($_POST['GAURD_MOBILE_NO'])){
            $GAURD_MOBILE_NO = isValidData($_POST['GAURD_MOBILE_NO']);
            if(strlen($GAURD_MOBILE_NO)>=12 ||strlen($GAURD_MOBILE_NO)<=9){
              //  $error.="<div class='text-danger'>Invalid  Gaurdian Mobile</div>";
            }
            $firstCharacter = $GAURD_MOBILE_NO[0];

            if($firstCharacter ==0){
                $GAURD_MOBILE_NO=  substr($GAURD_MOBILE_NO ,1);
            }
        }else{
         //   $error.="<div class='text-danger'>Guardian's Mobile No Must be Enter</div>";
        }

        if(isset($_POST['GAURD_HOME_ADDRESS'])&&isValidData($_POST['GAURD_HOME_ADDRESS'])){
            $GAURD_HOME_ADDRESS = isValidData($_POST['GAURD_HOME_ADDRESS']);
        }else{
            //$error.="<div class='text-danger'>Guardian Address Must be Enter</div>";
        }


        $gardian_array=array(
            "FIRST_NAME"=>$GNAME,
            "RELATIONSHIP"=>$REL_GUARD,
            "OCCUPATION"=>$OCC_GUARD,
            "MOBILE_CODE"=>$GAURD_MOBILE_CODE,
            "MOBILE_NO"=>$GAURD_MOBILE_NO,
            "HOME_ADDRESS"=>$GAURD_HOME_ADDRESS,
            "ACTIVE"=>1,
            "USER_ID"=>$USER_ID,
            "IS_CANDIDATE_GUARDIAN"=>'Y');


        //END GARDIAN INFORMATION

//        if($user['STATUS']=='N'&& $user['REMARKS']=="NEW_ADMISSION") {
            if (isset($_FILES['profile_image'])) {
                // prePrint($_FILES['profile_image'][]);
                if (isValidData($_FILES['profile_image']['name'])) {

                    $res = $this->upload_image('profile_image', "profile_image_" . $USER_ID);
                    if ($res['STATUS'] === true) {
                        $PROFILE_IMAGE = $res['IMAGE_NAME'];
                      //  $resutl = $this->CI_ftp_Download(PROFILE_IMAGE_CHECK_PATH, $PROFILE_IMAGE);

                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                    $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
                   
                }
            } else { 
                
                $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
              
            }
//        }else{
//            $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
//        }

        $form_array = array(
            "FIRST_NAME"=>$FIRST_NAME,
            "PREFIX_ID"=>$PREFIX_ID,
            "MOBILE_NO"=>$MOBILE_NO,
            "LAST_NAME"=>$LAST_NAME,
            "FNAME"=>$FNAME,
            "MOBILE_CODE"=>$MOBILE_CODE,
            "PLACE_OF_BIRTH"=>$PLACE_OF_BIRTH,
            "HOME_ADDRESS"=>$HOME_ADDRESS,
            "PERMANENT_ADDRESS"=>$PERMANENT_ADDRESS,
            "PHONE"=>$PHONE,
            "RELIGION"=>$RELIGION,
            "COUNTRY_ID"=>$COUNTRY_ID,
            "PROVINCE_ID"=>$PROVINCE_ID,
            "DISTRICT_ID"=>$DISTRICT_ID,
            "CITY_ID"=>$CITY_ID,
            "DATE_OF_BIRTH"=>$DATE_OF_BIRTH,
            "ZIP_CODE"=>$ZIP_CODE,
            "BLOOD_GROUP"=>$BLOOD_GROUP,
            "CNIC_EXPIRY"=>$CNIC_EXPIRY,
            "PASSPORT_EXPIRY"=>$PASSPORT_EXPIRY,
            "GENDER"=>$GENDER,
            "U_R"=>$U_R,
            "EMAIL"=>$EMAIL,
             "PROFILE_IMAGE"=>$PROFILE_IMAGE,
            "CNIC_NO"=>$CNIC_NO,
        );


        if($error==""){
              $PRE_RECORD = $this->User_model->getUserFullDetailWithChoiceById($USER_ID,$STUDENT_APPLICATION_ID,$SHIFT_ID=1);
           
            $PRE_RECORD = json_encode($PRE_RECORD);


            $res = $this->User_model->updateUserById($USER_ID,$form_array,$admin_id);

            $res_guard = $this->User_model->saveGuardianByUserId($USER_ID,$gardian_array,$admin_id);

          //  $user_fulldata = $this->User_model->getUserFullDetailById($USER_ID);
           
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($USER_ID,$STUDENT_APPLICATION_ID,$SHIFT_ID=1);
           
            $user_fulldata = json_encode($user_fulldata);

            $application_array = array("FORM_DATA"=>$user_fulldata,"USER_ID"=>$USER_ID);

            $res_app = $this->Application_model->updateApplicationById($STUDENT_APPLICATION_ID,$application_array);
            
            $this->log_model->create_log($STUDENT_APPLICATION_ID,$STUDENT_APPLICATION_ID,$PRE_RECORD,$user_fulldata,"ADMIN_UPDATE_APPLICATION",'applications',13,$admin_id);
            
            if($res===-1||$res_guard==-1){
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
            }
            if($res===0&&$res_guard==0){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>No data has been changed..!<div>";
            }else{
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Save ..!<div>";
            }

        }
        else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = $error;
        }


        if($reponse['RESPONSE'] == "ERROR"){
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }else{
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }
    
    public function student_application_unlock(){

        $user = $this->session->userdata($this->SessionName);
            $user_role = $this->session->userdata($this->user_role);
            $user_id = $user['USER_ID'];
            $role_id = $user_role['ROLE_ID'];

            $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
            $this->verify_path($this->script_name,$side_bar_data);
        if(isset($_POST['USER_PASSWORD'])&&isset($_POST['IS_UNLOCK'])&&isValidData($_POST['USER_PASSWORD'])){
            $user = $this->session->userdata($this->SessionName);
			$user = $this->User_model->getUserById($user['USER_ID']);
			//prePrint($user);
			//exit();
            if($user['PASSWORD']==cryptPassowrd(isValidData($_POST['USER_PASSWORD']))){
                if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')){

                    $STUDENT_USER_ID = $this->session->userdata('STUDENT_USER_ID');
                    $STUDENT_APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

                    $user_fulldata = $this->User_model->getUserFullDetailById($STUDENT_USER_ID,$STUDENT_APPLICATION_ID);

                    if($user_fulldata){
                        $this->Application_model->unlock_form($STUDENT_APPLICATION_ID,$user_fulldata,$user['USER_ID']);
                        send_unlock_mail($user_fulldata['users_reg']['EMAIL']);
                        $success= "<div class='text-success'>This Application has been unlock successfully</div>";
                        $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                    }else{
                        $success= "<div class='text-danger'>User data Not Found</div>";
                        $alert = array('MSG'=>$success,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                    }
                    $this->session->unset_userdata('STUDENT_USER_ID');
                    $this->session->unset_userdata('STUDENT_APPLICATION_ID');
                    redirect(base_url('AdminPanel/search_student_by_cnic'));
                }else{
                    redirect(base_url('AdminPanel/search_student_by_cnic'));
                }
            }
            else{
                $success= "<div class='text-danger'>Password missmatch</div>";
                $alert = array('MSG'=>$success,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/student_application_unlock'));
            }


        }else{
            
            $data['side_bar_values'] = $side_bar_data;


            $data['user'] = $user;
            $data['profile_url'] = $user['PROFILE_IMAGE'];
            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('admin/unlock_application_request');
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }


    }
    
    public function delete_application(){

        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        if(isset($_POST['USER_PASSWORD'])&&isset($_POST['IS_DELETE'])&&isset($_POST['REMARKS'])&&isValidData($_POST['USER_PASSWORD'])&&isValidData($_POST['REMARKS'])){
            $user = $this->session->userdata($this->SessionName);
			$user = $this->User_model->getUserById($user['USER_ID']);
            if($user['PASSWORD']==cryptPassowrd(isValidData($_POST['USER_PASSWORD']))){
                if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')){
                    $remarks = isValidData($_POST['REMARKS']);
                    $STUDENT_USER_ID = $this->session->userdata('STUDENT_USER_ID');
                    $STUDENT_APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');


                  
                      $user_fulldata = $this->User_model->getUserFullDetailById($STUDENT_USER_ID,$STUDENT_APPLICATION_ID);
                        $this->Application_model->unlock_form($STUDENT_APPLICATION_ID,$user_fulldata,$user['USER_ID']);
                          $form_array = array(
                        "IS_DELETED"=>"Y",
                        "REMARKS"=>$remarks,
                    );
                    $this->Application_model->updateApplicationById($STUDENT_APPLICATION_ID ,$form_array);
                      $formArray = array('DESIGNATION_ID'=>null);
                        $res = $this->User_model->updateUserById($STUDENT_USER_ID,$formArray,$user_id);
                    $QUERY = "";
                    $PRE_RECORD ="";
                    $CURRENT_RECORD =  array("APPLICATION_ID"=>$STUDENT_APPLICATION_ID,
                        "REMARKS"=>$remarks,
                        "IS_DELETED"=>"Y",
                        "USER_ID"=>$STUDENT_USER_ID
                    );
                    $this->log_model->create_log(0,$STUDENT_APPLICATION_ID,$PRE_RECORD,$CURRENT_RECORD,"DELETE_APPLICATION",'applications',13,$user_id);




                        $success= "<div class='text-success'>This Application has been delete successfully</div>";
                        $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                        $this->session->set_flashdata('ALERT_MSG',$alert);

                    $this->session->unset_userdata('STUDENT_USER_ID');
                    $this->session->unset_userdata('STUDENT_APPLICATION_ID');
                    redirect(base_url('AdminPanel/search_student_by_cnic'));
                }else{
                    redirect(base_url('AdminPanel/search_student_by_cnic'));
                }
            }
            else{
                $success= "<div class='text-danger'>Password missmatch</div>";
                $alert = array('MSG'=>$success,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/delete_application'));
            }


        }else{

            $data['side_bar_values'] = $side_bar_data;


            $data['user'] = $user;
            $data['profile_url'] = $user['PROFILE_IMAGE'];
            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('admin/delete_application');
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }


    }
    public function change_campus(){
        $user = $this->session->userdata($this->SessionName);
            $user_role = $this->session->userdata($this->user_role);
            $user_id = $user['USER_ID'];
            $role_id = $user_role['ROLE_ID'];
            
            $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
            
             $STUDENT_USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $STUDENT_APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

                 
                  //  exit();
           // $this->verify_path($this->script_name,$side_bar_data);
        if(isset($_POST['program_type'])&&isset($_POST['session'])&&isset($_POST['campus'])&&isValidData($_POST['program_type'])&&isValidData($_POST['campus'])&&isValidData($_POST['session'])){
            $user = $this->session->userdata($this->SessionName);
        
                if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')){

                   
                    $user_fulldata = $this->Application_model->getApplicationByApplicationId($STUDENT_APPLICATION_ID);
                    //prePrint($user_fulldata);
                    if($user_fulldata){
                       // $this->Application_model->unlock_form($STUDENT_APPLICATION_ID,$user_fulldata,$user['USER_ID']);
                        //send_unlock_mail($user_fulldata['users_reg']['EMAIL']);
                        		$program_type 		= htmlspecialchars($this->input->post('program_type'));
                    			$session 			= htmlspecialchars($this->input->post('session'));
                    			$campus 			= htmlspecialchars($this->input->post('campus'));
                        	$admission_session = $this->Admission_session_model->getAdmissionSessionID($session,$campus,$program_type);
                			if (empty($admission_session))
                			{
                				$this->session->set_flashdata('message','Sorry system could not found announced session.');
                				    redirect(base_url('AdminPanel/change_campus'));
                				    exit();
                			}
                // 			prePrint($STUDENT_APPLICATION_ID);
                // 			prePrint($admission_session['ADMISSION_SESSION_ID']);
                // 			exit();
                			$this->Application_model->updateAdmissionSessionByApplicationId($STUDENT_APPLICATION_ID,$admission_session['ADMISSION_SESSION_ID']);
                        $success= "<div class='text-success'>The Campus has been Update Successfully</div>";
                        $alert = array('MSG'=>$success,'TYPE'=>'SUCCESS');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                    }else{
                        $success= "<div class='text-danger'>User data Not Found</div>";
                        $alert = array('MSG'=>$success,'TYPE'=>'ERROR');
                        $this->session->set_flashdata('ALERT_MSG',$alert);
                    }
                    $this->session->unset_userdata('STUDENT_USER_ID');
                    $this->session->unset_userdata('STUDENT_APPLICATION_ID');
                    redirect(base_url('AdminPanel/change_campus'));
                }else{
                    	$this->session->set_flashdata('message','Sorry system could not found application id.');
                    redirect(base_url('AdminPanel/change_campus'));
                }
            
           


        }else{
            
            $data['side_bar_values'] = $side_bar_data;
            $academic_session = $this->Admission_session_model->getSessionData();
	    	$program_types 	= $this->Administration->programTypes ();
            $data['academic_sessions'] = $academic_session;
		    $data['program_types'] = $program_types;
            $data['user'] = $user;
            $data['profile_url'] = $user['PROFILE_IMAGE'];
            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('admin/change_campus');
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }

    }
    public function generate_cpn(){

         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $error_msg = "";
        $query_string = "";
        if(isset($_POST['TEST_ID'])) {
            $TEST_ID = isValidData($_POST['TEST_ID']);
            $all_candidate_card_id = $this->TestResult_model->getTestResultAndCPNbyTestId($TEST_ID);
            //prePrint($all_candidate_card_id['40210']);
            //exit();
            //prePrint($all_candidate_card_id);
           
            $list_of_data = array();
            $cpn_configs = null;
            $i = 0;
            foreach ($all_candidate_card_id as $can) {
                
                if ($i==0) {
                    $cpn_configs = json_decode($can['CPN_CONFIG'], true);
                }
                $i++;
                
               // $form_data = json_decode($can['FORM_DATA'], true);
                $qualifications = $can['qualifications'];
                $YEAR = $can['YEAR'];
                $APPLICATION_ID = $can['APPLICATION_ID'];
                $CARD_ID = $can['CARD_ID'];
                $TEST_SCORE = (int)$can['TEST_SCORE'];
                $PASSING_SCORE = (int)$can['PASSING_SCORE'];
                $TOTAL_SCORE = (int)$can['TOTAL_SCORE'];
                //prePrint($qualifications);

                if ($can['PROGRAM_TYPE_ID'] == 2) {
                    //for master student we minus 2 year of current session
                    $DEDUCT__CURRENT_YEAR = $YEAR - 1;
                    if ($qualifications[0]['DEGREE_ID'] == 10) {
                        $last = $qualifications[1]['DEGREE_ID'];
                    } else {
                        $last = $qualifications[0]['DEGREE_ID'];
                    }
                    if ($last > 3 && $last <= 6) {
                        $list_degree = array(2, 3, "LAST" => $last);
                    } else {

                        $error_msg .= "<div>INVALID DEGREE ID FOUND AT  application id  application id =>" . $APPLICATION_ID . " AND CARD_ID => $CARD_ID </div>".json_encode($qualifications);
                        //$error =array("INVALID DEGREE ID FOUND AT  application id =>" . $APPLICATION_ID . " AND CARD_ID => $CARD_ID ");
                        //$this->session->set_flashdata('ALERT_MSG',$error);
                      //  redirect(base_url('AdminPanel/generate_cpn'));

                       // exit();
                    }

                }
                if ($can['PROGRAM_TYPE_ID'] == 1) {
                    //for master student we minus 1 year of current session

                    $DEDUCT__CURRENT_YEAR = $YEAR - 1;
                    //echo $DEDUCT_YEAR;
                    $list_degree = array(2, "LAST" => 3);
                }
                $cpn_detail = array();
                $DURATION = 0;
                // prePrint($list_degree);
                foreach ($list_degree as $key => $degree_id) {
                    $is_qualification_find = false;
                    $is_config_find = false;

                    foreach ($qualifications as $qualification) {
                        if ($qualification['DEGREE_ID'] == $degree_id) {
                            $is_qualification_find = $qualification;
                            break;
                        }
                    }
                    foreach ($cpn_configs as $cpn_config) {
                        if ($cpn_config['DEGREE_ID'] == $degree_id) {
                            $is_config_find = $cpn_config;
                            break;
                        }
                    }
                    if ($is_qualification_find && $is_config_find) {

                        $CPN_WEIGHTAGE_IN_PERCENTAGE = (int)$is_config_find['CPN_WEIGHTAGE_IN_PERCENTAGE'];
                        $DURATION += (int)$is_config_find['DURATION'];
                        $AFTER_DEDUCT_MARKS = $OBTAINED_MARKS = (int)$is_qualification_find['OBTAINED_MARKS'];
                        $TOTAL_MARKS = (int)$is_qualification_find['TOTAL_MARKS'];
                        $PASSING_YEAR = (int)$is_qualification_find['PASSING_YEAR'];

                        $is_config_find['IS_DEDUCTABLE'] = 'N';
                        if($degree_id==2){
                            $DEDUCT__PASSING_YEAR = $PASSING_YEAR;
                        }

                        if ($key === "LAST") {


                            $DEDUCT_YEAR = $DEDUCT__CURRENT_YEAR - ($DEDUCT__PASSING_YEAR+$DURATION);


                            if ($DEDUCT_YEAR < 0) {
                               // exit("PASSING YEAR INVALID at application id =>" . $APPLICATION_ID . " AND CARD_ID => $CARD_ID AND PASSING YEAR " . $is_qualification_find['PASSING_YEAR']);
                                $query_string.="$APPLICATION_ID,";
                                $error_msg .= "<div>PASSING YEAR INVALID at  application id =>" . $APPLICATION_ID . " AND CARD_ID => $CARD_ID </div>";
                                //$error =array('TYPE'=>'ERROR','MSG'=>$error_msg);
                              // prePrint($error);
                                // $this->session->set_flashdata('ALERT_MSG',$error);
                                //redirect(base_url('AdminPanel/generate_cpn'));

                                //exit();
                            }
                            if ($can['PROGRAM_TYPE_ID'] == 2 && $DEDUCT_YEAR>0) {
                                $DEDUCT_YEAR--;
                            }

                            $DEDUCT_MARKS = $DEDUCT_YEAR * PER_YEAR_DEDUCTION_MARKS;
                            if ($DEDUCT_MARKS > MAX_DEDUCATION_MARKS) {
                                $DEDUCT_MARKS = MAX_DEDUCATION_MARKS;
                            }
                            $AFTER_DEDUCT_MARKS = $OBTAINED_MARKS - $DEDUCT_MARKS;
                            $is_config_find['IS_DEDUCTABLE'] = 'Y';
                            $is_config_find['DEDUCT_MARKS'] = $DEDUCT_MARKS;
                        }

                        //$weightage = round($CPN_WEIGHTAGE_IN_PERCENTAGE / 100, 2);
                          $weightage = $CPN_WEIGHTAGE_IN_PERCENTAGE / 100;
                        //$percentage = round($AFTER_DEDUCT_MARKS * 100 / $TOTAL_MARKS, 2);
                        $percentage = $AFTER_DEDUCT_MARKS * 100 / $TOTAL_MARKS;
                        //$cumlative = round($weightage * $percentage, 2);
                        $cumlative = $weightage * $percentage;

                        $is_config_find['PASSING_YEAR'] = $is_qualification_find['PASSING_YEAR'];
                        $is_config_find['DISCIPLINE_NAME'] = $is_qualification_find['DISCIPLINE_NAME'];
                        $is_config_find['OBTAINED_MARKS'] = $OBTAINED_MARKS;
                        $is_config_find['TOTAL_MARKS'] = $TOTAL_MARKS;
                        $is_config_find['AFTER_DEDUCT_MARKS'] = $AFTER_DEDUCT_MARKS;
                        $is_config_find['PERCENTAGE'] = $percentage;
                        $is_config_find['CPN_PERCENTAGE'] = $cumlative;

                        $cpn_detail[] = $is_config_find;
                    } else {
                        if(!$is_qualification_find){
                         $error_msg .= "<div>QUALIFICAITON  AT application id =>" . $APPLICATION_ID . " AND CARD_ID => $CARD_ID </div>";    
                        }else if(!$is_config_find){
                             $error_msg .= "<div> CONFIG NOT FOUND AT application id =>" . $APPLICATION_ID . " AND CARD_ID => $CARD_ID </div>";    
                        }
                       


                    }
                }
                $is_test = false;
                foreach ($cpn_configs as $cpn_config) {

                    if ($cpn_config['DEGREE_TITLE'] == 'TEST_SCORE' && $cpn_config['CPN_WEIGHTAGE_IN_PERCENTAGE'] > 0) {

                        $CPN_WEIGHTAGE_IN_PERCENTAGE = $cpn_config['CPN_WEIGHTAGE_IN_PERCENTAGE'];
                        //$weightage = round($CPN_WEIGHTAGE_IN_PERCENTAGE / 100, 2);
                        $weightage = $CPN_WEIGHTAGE_IN_PERCENTAGE / 100;
                        //$weigtage = $this->TestResult_model->truncate_cpn($CPN_WEIGHTAGE_IN_PERCENTAGE / 100, 2);
                        //prePrint($weightage);
                        $percentage = $TEST_SCORE * 100 / $TOTAL_SCORE;
                        //$percentage = $this->TestResult_model->truncate_cpn($TEST_SCORE * 100 / $TOTAL_SCORE);
                        //prePrint($percentage);
                        $cumlative = $weightage * $percentage;
                        //prePrint($cumlative);
                        $cpn_config['PERCENTAGE'] = $percentage;
                        $cpn_config['CPN_PERCENTAGE'] = $cumlative;
                        $cpn_detail[] = $cpn_config;
                        $is_test = true;
                       // prePrint($cpn_config);
                        //exit();
                    }
                }

                //prePrint($cpn_detail);
                $json_cpn_detail = json_encode($cpn_detail);
                $CPN = 0;
                foreach ($cpn_detail as $cpn) {
                    $CPN += $cpn['CPN_PERCENTAGE'];
                }
                //$CPN = round($CPN, 2);
                if($is_test && $TEST_SCORE<$PASSING_SCORE){
                    $CPN = 0;
                   // $json_cpn_detail = NULL;

                }
                $list_of_data[] = array("CPN" => $CPN, "DETAIL_CPN" => $json_cpn_detail, "CARD_ID" => $CARD_ID, "TEST_ID" => $TEST_ID);
                //prePrint($CPN);


            }
        //     prePrint($list_of_data);
        //   exit();
            if($error_msg==""){
                if($this->TestResult_model->updateCPN($list_of_data, $user_id)){
                    $error =array('TYPE'=>'SUCCESS','MSG'=>"Successfully generate CPN");
                    $this->session->set_flashdata('ALERT_MSG',$error);
                }else{
                    $error =array('TYPE'=>'ERROR','MSG'=>"Something wentworng...! in database");
                    $this->session->set_flashdata('ALERT_MSG',$error);
                }
            }else{

                    //exit($query_string);
                $error =array('TYPE'=>'ERROR','MSG'=>$error_msg.$query_string);
                $this->session->set_flashdata('ALERT_MSG',$error);
                redirect(base_url('AdminPanel/generate_cpn'));

                // prePrint($is_qualification_find);
                //prePrint($is_config_find);
                exit();
            }

            //prePrint($list_of_data);
            redirect(base_url('AdminPanel/generate_cpn'));
            exit();
        }
        $data['test_year'] =$this->TestResult_model->getTestTypeYear();
        $data['side_bar_values'] = $side_bar_data;


        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/generate_cpn');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }

    public function getTestType(){
        echo "<option value='0'>--Choose--</option>";
        $year = 0;
        if(isset($_GET['SESSION_ID'])&&isValidData($_GET['SESSION_ID'])){
            $session_id = isValidData($_GET['SESSION_ID']);
            $session = $this->Admission_session_model->getSessionByID($session_id);
            $year = $session['YEAR'];
        }else if(isset($_GET['YEAR'])&&isValidData($_GET['YEAR'])){
            $year = isValidData($_GET['YEAR']);
        }
        
            $test_types =$this->TestResult_model->getTestTypeByYear($year);
            foreach ($test_types as $test_type){
                echo "<option value='{$test_type['TEST_ID']}'>{$test_type['TEST_NAME']}</option>";
            
            }

    }
    
    public function getTestResultByTestId(){
      //  exit();
       ini_set('memory_limit', '-1');
        if(isset($_GET['TEST_ID'])&&isValidData($_GET['TEST_ID'])) {
            $TEST_ID = isValidData($_GET['TEST_ID']);
            //exit($TEST_ID);
            $all_candidate_results = $this->TestResult_model->getTestResultAndCPNbyTestId($TEST_ID);
           
           
            ?>
             <div class="data-table-area mg-b-15">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="sparkline13-list">
                                <div class="sparkline13-hd">
                                    <div class="main-sparkline13-hd">
                                        <h1>All People</h1>
                                      
                                    </div>

                                </div>


                                <div class="sparkline13-graph">
                                    <div class="datatable-dashv1-list custom-datatable-overright">

                                        <div id="toolbar">
                                            <select class="form-control dt-tb">
                                                <option value="">Export Basic</option>
                                                <option value="all">Export All</option>
                                                <option value="selected">Export Selected</option>
                                            </select>
                                        </div>
                                        <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true"
                                               data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                           
                          <?php
           
            foreach($all_candidate_results as $k=>$result){
                $detail_cpns = json_decode($result['DETAIL_CPN'],true);
                
                if($k==0){
                    if(is_array($detail_cpns)){
                        echo "<thead>
                                            <tr >";
                                            echo "<th>Id</th>";
                            foreach($detail_cpns as $detail_cpn){
                                if($detail_cpn['DEGREE_ID']){
                                  
                                    if($detail_cpn['IS_DEDUCTABLE']==="Y"){
                                        $col_span = 7;
                                    }else{
                                        $col_span = 5;
                                    }
                                     
                                    echo "<th colspan='$col_span'>{$detail_cpn['DEGREE_TITLE']}</td>"; 
                                   
                                }else{
                                      echo "<th colspan='2'>{$detail_cpn['DEGREE_TITLE']}</th>"; 
                                     
                                     
                                }
                              
                            }
                               echo "<th>Final</th>"; 
                        echo "</tr>";
                        }
                       
                    
                    if(is_array($detail_cpns)){
                        echo "<tr>";
                          echo "<th>Application Id</th>";
                            foreach($detail_cpns as $detail_cpn){
                                if($detail_cpn['DEGREE_ID']){
                                    echo "<th>PASSING YEAR</th>";
                                    echo "<th>TOTAL MARKS</th>"; 
                                    echo "<th>OBTAINED MARKS</th>"; 
                                    
                                    if($detail_cpn['IS_DEDUCTABLE']==="Y"){
                                    
                                    echo "<th>DEDUCT MARKS</th>"; 
                                    echo "<th>MARKS AFTER DEDUCT</th>"; 
                                    
                                        
                                    }
                                    echo "<th>PERCENTAGE</td>"; 
                                    echo "<th>PERCENTAGE OUT / {$detail_cpn['CPN_WEIGHTAGE_IN_PERCENTAGE']}</th>"; 
                                }else{
                                    echo "<th>TEST SCORE</th>"; 
                                    
                                      echo "<th>TEST SCORE OUT / {$detail_cpn['CPN_WEIGHTAGE_IN_PERCENTAGE']}</th>";
                                     
                                }
                            }
                        echo "<th>CPN</th>"; 
                        echo "</tr></thead><tbody>";
                        }
                       
                }   
                echo "<tr>";
                echo "<td>{$result['APPLICATION_ID']}</td>";
                
                
                if(is_array($detail_cpns)){
                    foreach($detail_cpns as $detail_cpn){
                        if($detail_cpn['DEGREE_ID']){
                            echo "<td>{$detail_cpn['PASSING_YEAR']}</td>";
                             echo "<td>{$detail_cpn['TOTAL_MARKS']}</td>"; 
                            echo "<td>{$detail_cpn['OBTAINED_MARKS']}</td>"; 
                           
                            if($detail_cpn['IS_DEDUCTABLE']==="Y"){
                            
                            echo "<td>{$detail_cpn['DEDUCT_MARKS']}</td>"; 
                            echo "<td>{$detail_cpn['AFTER_DEDUCT_MARKS']}</td>"; 
                            
                                
                            }
                            echo "<td>{$detail_cpn['PERCENTAGE']}</td>"; 
                            echo "<td>{$detail_cpn['CPN_PERCENTAGE']}</td>"; 
                        }else{
                            echo "<td>{$result['TEST_SCORE']}</td>";
                              echo "<td>{$detail_cpn['CPN_PERCENTAGE']}</td>"; 
                            
                        }
                    }
                     
                }
               
                echo "<td>{$result['CPN']}</td>";                 
                        
                echo " </tr>";
                
                 //prePrint($result['TEST_SCORE']);
                 //prePrint($result['CPN']);
                 //prePrint(json_decode($result['DETAIL_CPN'],true));
                }
        ?>
        </tbody>
        </table>
             </div> <!-- /.table-stats -->
                                </div>
                            </div>



                        </div>
                    </div>
                </div>
            </div>

    <script src="<?=base_url()?>dash_assets/js/data-table/bootstrap-table.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/tableExport.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/data-table-active.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/bootstrap-table-editable.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/bootstrap-editable.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/bootstrap-table-resizable.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/colResizable-1.5.source.js"></script>
    <script src="<?=base_url()?>dash_assets/js/data-table/bootstrap-table-export.js"></script>
        <?php
    }
}
    
    public function print_invalid_intermediate(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
        for($i=8;$i<=14;$i++){
             $result = $this->Application_model->getApplicationByAdmissionSessionIdAdmin($i);
         echo "<table border='1px'>";
         echo "<tr><td>No</td><td>CNIC</td><td>APPLICATION ID</td><td>total marks</td><td>obtained</td><td>board</td></tr>";
        foreach($result as $k =>$applications){
            
            if($applications['STATUS_ID']==3||$applications['STATUS_ID']==4||$applications['STATUS_ID']==5){
                $form_data = json_decode($applications['FORM_DATA'],true);
                $qualifications = $form_data['qualifications'];
                $qual = findObjectinList($qualifications,"DEGREE_ID",3);
               
                if($qual['TOTAL_MARKS']!=1100){
                    // echo "<tr><td>";
                    //  prePrint($qualifications);
                    //   echo "</td></tr>";
                    echo "<tr>";
                    echo "<td>$k</td>";
                    // echo "<td>".$form_data['users_reg']['USER_ID']."</td>";
                    echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                     echo "<td>".$applications['APPLICATION_ID']."</td>";
                     echo "<td>".$qual['TOTAL_MARKS']."</td>";
                     echo "<td>".$qual['OBTAINED_MARKS']."</td>";
                     echo "<td>".$qual['ORGANIZATION']."</td>";
                    echo "</tr>";
                   
                }
                
            }
        }
           echo "</table>";
        }
       
    }   
   
   public function print_invalid_matriculation(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
        for($i=1;$i<=7;$i++){
             $result = $this->Application_model->getApplicationByAdmissionSessionIdAdmin($i);
         echo "<table border='1px'>";
         echo "<tr><td>No</td><td>CNIC</td><td>APPLICATION ID</td><td>total marks</td><td>obtained</td><td>board</td></tr>";
        foreach($result as $k =>$applications){
            
            if($applications['STATUS_ID']==3||$applications['STATUS_ID']==4||$applications['STATUS_ID']==5){
                $form_data = json_decode($applications['FORM_DATA'],true);
                $qualifications = $form_data['qualifications'];
                $qual = findObjectinList($qualifications,"DEGREE_ID",2);
               
                if($qual['TOTAL_MARKS']!=850){
                    // echo "<tr><td>";
                    //  prePrint($qualifications);
                    //   echo "</td></tr>";
                    echo "<tr>";
                    echo "<td>$k</td>";
                    // echo "<td>".$form_data['users_reg']['USER_ID']."</td>";
                    echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                     echo "<td>".$applications['APPLICATION_ID']."</td>";
                     echo "<td>".$qual['TOTAL_MARKS']."</td>";
                     echo "<td>".$qual['OBTAINED_MARKS']."</td>";
                     echo "<td>".$qual['ORGANIZATION']."</td>";
                    echo "</tr>";
                   
                }
                
            }
        }
           echo "</table>";
        }
       
    }
   
    public function rejected_student(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
        for($i=1;$i<=7;$i++){
             $result = $this->Application_model->getApplicationByAdmissionSessionIdAdmin($i);
         echo "<table border='1px'>";
         echo "<tr><td>No</td><td>CNIC</td><td>APPLICATION ID</td><td>total marks</td><td>obtained</td><td>board</td></tr>";
        foreach($result as $k =>$applications){
            
            if($applications['STATUS_ID']==2){
                $form_data = json_decode($applications['FORM_DATA'],true);
                $qualifications = $form_data['qualifications'];
                $qual = findObjectinList($qualifications,"DEGREE_ID",10);
               if($qual==null){
                    // echo "<tr>";
                    // echo "<td>$k</td>";
                    // // echo "<td>".$form_data['users_reg']['USER_ID']."</td>";
                    // echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                    //  echo "<td>".$applications['APPLICATION_ID']."</td>";
                    //  echo "<td></td>";
                    //  echo "<td></td>";
                    //  echo "<td></td>";
                    // echo "</tr>";
               }else{
                   $red = "";
                   if($qual['TOTAL_MARKS']==1100){
                      // $red = "style='background-color: red;'";
                        echo "<tr $red>";
                    echo "<td>$k</td>";
                    // echo "<td>".$form_data['users_reg']['USER_ID']."</td>";
                    echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                     echo "<td>".$applications['APPLICATION_ID']."</td>";
                     echo "<td>".$qual['TOTAL_MARKS']."</td>";
                     echo "<td>".$qual['OBTAINED_MARKS']."</td>";
                     echo "<td>".$qual['ORGANIZATION']."</td>";
                    echo "</tr>";
                   }
                    // echo "<tr $red>";
                    // echo "<td>$k</td>";
                    // // echo "<td>".$form_data['users_reg']['USER_ID']."</td>";
                    // echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                    //  echo "<td>".$applications['APPLICATION_ID']."</td>";
                    //  echo "<td>".$qual['TOTAL_MARKS']."</td>";
                    //  echo "<td>".$qual['OBTAINED_MARKS']."</td>";
                    //  echo "<td>".$qual['ORGANIZATION']."</td>";
                    // echo "</tr>";
               }
                
                
            }
        }
           echo "</table>";
        }
       
    }   
  
    public function print_csv_data(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
//           header("Content-type: text/csv");
// header("Content-Disposition: attachment; filename=file.csv");
// header("Pragma: no-cache");
// header("Expires: 0");
        for($i=8;$i<=14;$i++){
             //$result = $this->Application_model->getApplicationByAdmissionSessionIdAdminForBackup($i);
             $result = $this->Application_model->getApplicationByAdmissionSessionIdAdmin($i);
             
         echo "<table border='1px'>";
         echo "<tr>
        
         <td>APPLICATION_ID</td>
         <td>STATUS_ID</td>
         <td>NAME</td>
         <td>FATHER_NAME</td>
         <td>SURNAME</td>
         <td>CNIC_NO</td>
         <td>EMAIL</td>
         <td>DATE_OF_BIRTH</td>
         <td>MOBILE_NO</td>
          <td>GENDER</td>
           <td>HOME_ADDRESS</td>
            <td>PERMANENT_ADDRESS</td>
            <td>BLOOD_GROUP</td>
            <td>RELIGION</td>
            <td>DISTRICT_NAME</td>
            <td>U_R</td>
              <td>SSC ROLL_NO</td>
             <td>SSC OBTAINED_MARKS</td>
             <td>SSC TOTAL_MARKS</td>
              <td>SSC GROUP</td>
             <td>SSC PASSING_YEAR</td>
             <td>SSC BOARD</td>
             <td>HSC ROLL_NO</td>
             <td>HSC OBTAINED_MARKS</td>
             <td>HSC TOTAL_MARKS</td>
              <td>HSC GROUP</td>
             <td>HSC PASSING_YEAR</td>
             <td>HSC BOARD</td>
             <td>GRAD ROLL_NO</td>
             <td>GRAD OBTAINED_MARKS</td>
             <td>GRAD TOTAL_MARKS</td>
              <td>GRAD GROUP</td>
             <td>GRAD PASSING_YEAR</td>
             <td>GRAD BOARD</td>
             <td>CPN</td>
             
             
         
         </tr>";
    
        foreach($result as $k =>$applications){
            
            if($applications['STATUS_ID']==3||$applications['STATUS_ID']==4||$applications['STATUS_ID']==5){
                $form_data = json_decode($applications['FORM_DATA'],true);
                $qualifications = $form_data['qualifications'];
                $inter = findObjectinList($qualifications,"DEGREE_ID",3);
                 $metric = findObjectinList($qualifications,"DEGREE_ID",2);
                 if($qualifications[0]['DEGREE_ID']!=10){
                     $qualification = $qualifications[0];
                 }else{
                    $qualification = $qualifications[1];

                 }
            if($qualification['PASSING_YEAR']<2020){
                continue;
            }
                    echo "<tr>";
                    
                    echo "<td>".$applications['APPLICATION_ID']."</td>";
                    echo "<td>".$applications['STATUS_ID']."</td>";
                    echo "<td>".$form_data['users_reg']['FIRST_NAME']."</td>";
                    echo "<td>".$form_data['users_reg']['FNAME']."</td>";
                    echo "<td>".$form_data['users_reg']['LAST_NAME']."</td>";
                    echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                          echo "<td>".$form_data['users_reg']['EMAIL']."</td>";
                            echo "<td>".$form_data['users_reg']['DATE_OF_BIRTH']."</td>";
                             echo "<td>".$form_data['users_reg']['MOBILE_NO']."</td>";
                              echo "<td>".$form_data['users_reg']['GENDER']."</td>";
                               echo "<td>".$form_data['users_reg']['HOME_ADDRESS']."</td>";
                                echo "<td>".$form_data['users_reg']['PERMANENT_ADDRESS']."</td>";
                                 echo "<td>".$form_data['users_reg']['BLOOD_GROUP']."</td>";
                                  echo "<td>".$form_data['users_reg']['RELIGION']."</td>";
                                   echo "<td>".$form_data['users_reg']['DISTRICT_NAME']."</td>";
                                    echo "<td>".$form_data['users_reg']['U_R']."</td>";
                                    echo "<td>".$metric['ROLL_NO']."</td>";
                                     echo "<td>".$metric['OBTAINED_MARKS']."</td>";
                                      echo "<td>".$metric['TOTAL_MARKS']."</td>";
                                       echo "<td>".$metric['DISCIPLINE_NAME']."</td>";
                                       echo "<td>".$metric['PASSING_YEAR']."</td>";
                                       
                                        echo "<td>".$metric['ORGANIZATION']."</td>";
                                         echo "<td>".$inter['ROLL_NO']."</td>";
                                     echo "<td>".$inter['OBTAINED_MARKS']."</td>";
                                      echo "<td>".$inter['TOTAL_MARKS']."</td>";
                                       echo "<td>".$inter['DISCIPLINE_NAME']."</td>";
                                       echo "<td>".$inter['PASSING_YEAR']."</td>";
                                       
                                        echo "<td>".$inter['ORGANIZATION']."</td>";
                                         echo "<td>".$qualification['ROLL_NO']."</td>";
                                     echo "<td>".$qualification['OBTAINED_MARKS']."</td>";
                                      echo "<td>".$qualification['TOTAL_MARKS']."</td>";
                                       echo "<td>".$qualification['DISCIPLINE_NAME']."</td>";
                                       echo "<td>".$qualification['PASSING_YEAR']."</td>";
                                       echo "<td><img width='100' height='100' src='".base_url().EXTRA_IMAGE_PATH.$qualification['MARKSHEET_IMAGE']."'/></td>";
                                      echo "<td><img width='100' height='100' src='".base_url().EXTRA_IMAGE_PATH.$qualification['PASSCERTIFICATE_IMAGE']."'/></td>";
                                       
                                        echo "<td>".$qualification['ORGANIZATION']."</td>";
                                          echo "<td>".$applications['CPN']."</td>";
                     
                    echo "</tr>";
                   
                
                
            }
        }
           echo "</table>";
        }
       
    }  
      
    public function print_law_data(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
//           header("Content-type: text/csv");
// header("Content-Disposition: attachment; filename=file.csv");
// header("Pragma: no-cache");
// header("Expires: 0");
        for($i=8;$i<=8;$i++){
             $result = $this->Application_model->getApplicationByAdmissionSessionIdAdmin($i);
         echo "<table border='1px'>";
         echo "<tr>
        
         <td>APPLICATION_ID</td>
         <td>STATUS_ID</td>
         <td>NAME</td>
         <td>FATHER_NAME</td>
         <td>SURNAME</td>
         <td>CNIC_NO</td>
         <td>EMAIL</td>
         <td>DATE_OF_BIRTH</td>
         <td>MOBILE_NO</td>
          <td>GENDER</td>
           <td>HOME_ADDRESS</td>
            <td>PERMANENT_ADDRESS</td>
            <td>BLOOD_GROUP</td>
            <td>RELIGION</td>
            <td>DISTRICT_NAME</td>
            <td>U_R</td>
              <td>DISCIPLINE</td>
            
             
             
         
         </tr>";
    
        foreach($result as $k =>$applications){
            
            if($applications['STATUS_ID']==2||$applications['STATUS_ID']==3||$applications['STATUS_ID']==4||$applications['STATUS_ID']==5){
                $form_data = json_decode($applications['FORM_DATA'],true);
                $qualifications = $form_data['qualifications'];
                $law_master = findObjectinList($qualifications,"DISCIPLINE_ID",167);
                 $law = findObjectinList($qualifications,"DISCIPLINE_ID",74);
                if($law){
                   echo "<tr>";
                    
                    echo "<td>".$applications['APPLICATION_ID']."</td>";
                    echo "<td>".$applications['STATUS_ID']."</td>";
                    echo "<td>".$form_data['users_reg']['FIRST_NAME']."</td>";
                    echo "<td>".$form_data['users_reg']['FNAME']."</td>";
                    echo "<td>".$form_data['users_reg']['LAST_NAME']."</td>";
                    echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                          echo "<td>".$form_data['users_reg']['EMAIL']."</td>";
                            echo "<td>".$form_data['users_reg']['DATE_OF_BIRTH']."</td>";
                             echo "<td>".$form_data['users_reg']['MOBILE_NO']."</td>";
                              echo "<td>".$form_data['users_reg']['GENDER']."</td>";
                               echo "<td>".$form_data['users_reg']['HOME_ADDRESS']."</td>";
                                echo "<td>".$form_data['users_reg']['PERMANENT_ADDRESS']."</td>";
                                 echo "<td>".$form_data['users_reg']['BLOOD_GROUP']."</td>";
                                  echo "<td>".$form_data['users_reg']['RELIGION']."</td>";
                                   echo "<td>".$form_data['users_reg']['DISTRICT_NAME']."</td>";
                                    echo "<td>".$form_data['users_reg']['U_R']."</td>";
                                  
                                       echo "<td>".$law['DISCIPLINE_NAME']."</td>";
                                      
                                         
                     
                    echo "</tr>";
                     
                }
                else if($law_master){
                   echo "<tr>";
                    
                    echo "<td>".$applications['APPLICATION_ID']."</td>";
                    echo "<td>".$applications['STATUS_ID']."</td>";
                    echo "<td>".$form_data['users_reg']['FIRST_NAME']."</td>";
                    echo "<td>".$form_data['users_reg']['FNAME']."</td>";
                    echo "<td>".$form_data['users_reg']['LAST_NAME']."</td>";
                    echo "<td>".$form_data['users_reg']['CNIC_NO']."</td>";
                          echo "<td>".$form_data['users_reg']['EMAIL']."</td>";
                            echo "<td>".$form_data['users_reg']['DATE_OF_BIRTH']."</td>";
                             echo "<td>".$form_data['users_reg']['MOBILE_NO']."</td>";
                              echo "<td>".$form_data['users_reg']['GENDER']."</td>";
                               echo "<td>".$form_data['users_reg']['HOME_ADDRESS']."</td>";
                                echo "<td>".$form_data['users_reg']['PERMANENT_ADDRESS']."</td>";
                                 echo "<td>".$form_data['users_reg']['BLOOD_GROUP']."</td>";
                                  echo "<td>".$form_data['users_reg']['RELIGION']."</td>";
                                   echo "<td>".$form_data['users_reg']['DISTRICT_NAME']."</td>";
                                    echo "<td>".$form_data['users_reg']['U_R']."</td>";
                                  
                                       echo "<td>".$law_master['DISCIPLINE_NAME']."</td>";
                                      
                                         
                     
                    echo "</tr>";
                     
                }
                
                
                
            }
        }
           echo "</table>";
        }
       
    }  
    
    public function admission_form_challan($valid_upto='2021-02-26'){

        if($this->session->has_userdata('STUDENT_APPLICATION_ID')){
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

            $user_id = $this->session->userdata('STUDENT_USER_ID');
            $user = $this->User_model->getUserById($user_id);

            $data['user'] = $user;
            $data['APPLICATION_ID']=$APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'],$APPLICATION_ID);

            if($application){
                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'],$application['CAMPUS_ID']);
                 if($form_fees){
                     $valid_upto = getDateCustomeView($valid_upto,'d-m-Y');

                //     if ($application['ADMISSION_END_DATE']<date('Y-m-d'))
                //     {
                //         exit("Sorry your challan is expired..");
                //     }
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
                        "TOTAL_AMOUNT" => $form_fees['AMOUNT'],
                        "CATEGORY_NAME" => "ADMISSIONS ".$application['YEAR'],
                        "VALID_UPTO" => $valid_upto,
                        "ACCOUNT_NO" => $form_fees['ACCOUNT_NO'],
                        "ACCOUNT_TITLE" => $form_fees['ACCOUNT_TITLE'],
                        "CANDIDATE_ID" => $user['USER_ID'],
                        "DEGREE_PROGRAM" => $application['PROGRAM_TITLE'],
                        "YEAR"=>$application['YEAR'],
                        'CAMPUS_NAME' => $application['NAME'],
                    );
                    $data['row'] = $row;
                    $data['roll_no'] = $user['USER_ID'];
                    $this->load->view('admission_form_challan', $data);

                }else{
                    echo "fees not found";
                }

            }else{
                echo "this application id is not associate with you";
            }

        }else{
            echo "Application Id Not Found";
        }


    }

    private function upload_image($index_name,$image_name,$max_size = 100,$path = '../eportal_resource/images/applicants_profile_image/',$con_array=array())
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

        if ( ! $this->upload->do_upload($index_name))
        {
            return array("STATUS"=>false,"MESSAGE"=> $config['upload_path'].$this->upload->display_errors());
        }
        else
        {
            $image_data = $this->upload->data();

            $image_path = $image_data['full_path'];

            $config['image_library'] = 'gd2';
            $config['source_image'] = $image_path;
            $config['create_thumb'] = FALSE;
            if(!count($con_array)){
                $config['maintain_ratio'] = TRUE;
                $config['width']         = 180;
                $config['height']       = 260;
            }
            else{
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



          //  $this->load->library('ftp');



            //$this->CI_ftp($path,$image_data['file_name']);

            $image_data['file_name'] = strtolower($image_data['file_name']);
            return array("STATUS"=>true,"IMAGE_NAME"=>$image_data['file_name']);

        }
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



        $already_exist = $this->ftp->list_files($ftp_path);

        if($already_exist){

        }else{
            $dir  = $this->ftp->mkdir($ftp_dir_path, 0755);
        }

        $up = $this->ftp->upload($path.$name,$ftp_path.$name, 'binary', 0775);
        if(!$up){
            $msg['MSG'] = 'UPLOADING FAILED';
            $msg = json_encode($msg);
            writeQuery($msg);
            $this->ftp->close();
            return false;
        }

        $this->ftp->close();
        return true;

    }
    
    public function read_file ()
	{
//			ini_set("memory_limit","-1");
//			$num = 12365;
//			echo $this->getNum("$num");
//			echo $this->getNum("ABC123");
//			echo $this->getNum(" ABC 123");

//			$file = fopen("result.txt","r");
			$file = file_get_contents("result.txt");
//			prePrint($file);
			$file = explode("|",$file);
			$new_array = array();

			$records = $this->Api_qualification_model->getQualificatinBySeatNoAndYear(0,2020,3,96);
//			prePrint($records);
			foreach ($file as $k=>$text)
			{
				//if ($k==100) break;
				if (empty($text)) continue;
				$new_array[]=$text;
				//prePrint($k." ".$text);
			}
			$mrks = array();
		
			foreach ($new_array as $i=>$text)
			{
				if ($text == "PASS")
				{
					$seat_no = $new_array[$i-1];
					$seat_no = $this->getNum("$seat_no");;
					$marks = $new_array[$i+1];
					$marks = $this->getNum("$marks");
					$mrks[$seat_no]=$marks;

//					echo "Seat NO $seat_no MARks: $marks <br/>";

				}
			}
				$i=0;
			foreach ($records as $record)
			{
				$roll_no = $record['ROLL_NO'];
				$obtain_marks = $record['OBTAINED_MARKS'];
				$USER_ID = $record['USER_ID'];
				$cnic_no = $record['CNIC_NO'];
				if (isset($mrks[$roll_no])){
					$board_marks  = $mrks[$roll_no];

					if ($board_marks!=$obtain_marks) {
					    $i++;
						echo "<H3  style='color: red'>SNO: $i   CNIC: $cnic_no USER ID: $USER_ID - MIS MATCH</H3>";
						prePrint("OBTAINED MAARKS".$obtain_marks);
					prePrint("Board marks".$board_marks);
					}else{
					//	echo "<h3>USER ID: $USER_ID - MATCH FOUND</h3>";
					}
					
				}
			}

		}

	function getNum($string)
	{
		$string_len = strlen($string);
		if ($string_len>0)
		{
			for ($i=$string_len-1; $i>=0; $i--){
				if (!($string[$i]>='0' && $string[$i]<='9')){
					break;
				}
			}
			if ($i>=0){
				return substr($string,$i+1);
			}else
			{
				return  $string;
			}
		}else return 0;
	}
	
	    //UPDATED FUNCTION ON 25-march-2021
    public function upload_minor_subjects(){
        $error = "";
        $success = true;
        $success_msg = "";
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')){

            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
             $user = $this->User_model->getUserById($USER_ID);
        }else{
             $error .= "<div class='text-danger'>Invalid request upload_minor_subjects</div>";
        }
        
      


        if ($this->input->server('REQUEST_METHOD') == 'POST' &&$error=="") {

            if ($this->session->has_userdata('STUDENT_APPLICATION_ID')) {
                $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
            } else {
                $error .= "<div class='text-danger'>Application Id not found in Session</div>";
            }
            if(isset($_POST['DISCIPLINE_ID'])&&isValidData($_POST['DISCIPLINE_ID'])){
                $DISCIPLINE_ID =isValidData($_POST['DISCIPLINE_ID']);
            }else{
                $error.="<div class='text-danger'>Discipline Id Not found</div>";
            }
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            // if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
            //     redirect(base_url('form/application_form'));
            //     exit();
            // }
            //  if($application['STATUS_ID']<2){
            //     redirect(base_url('Candidate'));
            // }
            if(isset($_POST['minor_subject_array'])&&is_array($_POST['minor_subject_array'])&&count($_POST['minor_subject_array'])>0&&$error==""){

                $minor_subject_array = $_POST['minor_subject_array'];
                $delete_result = $this->Application_model->deleteApplicantsMinorsByUserIdAndDisciplineId($user['USER_ID'],$DISCIPLINE_ID);
                if($delete_result>0) {
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

                }
                else{
                    $error .= "<div class='text-danger'>Something went wrong delete previous minor</div>";
                }


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
            redirect(base_url()."AdminPanel/select_subject");
        }
        else{
          //  $success_msg .= "<div class='text-success'>your subject add successfully</div>";
            $alert = array('MSG'=>$success_msg,'TYPE'=>'SUCCESS');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            if(isset($_POST['IS_NEXT'])&&$_POST['IS_NEXT']==1){

                redirect(base_url()."AdminPanel/select_category");
            }else{
                redirect(base_url()."AdminPanel/select_subject");
            }

        }


    }

    //UPDATED FUNCTION ON 25-march-2021 VIEW FILE select_minor_subject.php
    public function select_subject(){
        $admin = $this->session->userdata($this->SessionName);
//prePrint($this->user_role);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];
        //prePrint($role_id);

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // prePrint($side_bar_data);
        //   $role_data = $this->Configuration_model->get_privilages($user_id,$role_id);
        //   prePrint($role_data);
       // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
         if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')){

            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
     
            // $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            // $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($USER_ID);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            
            if ($application) {
            
            //form close from bachelor
             //   $this->close_registration_for_bachelor($application);
                
                
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
                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }



                    $data['profile_url'] =  "AdminPanel";
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){

                        $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);
                        
                        if($result!=null && count($result)==1){
                            //prePrint($result);
                            $result =$result[0];
                            /***********DANGER**************
                            WE NEED TO UPDATE APPLICATION ID INSTEAD OF USER ID IN 
                             $is_exist = $this->Application_model->getApplicantsMinorsByUserIdAndMinorMappingId($user['USER_ID'],$result['MINOR_MAPPING_ID']);
                            ***********DANGER**************/
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
                                    echo "Minor Automatic Added";
                                    $error = "<div class='text-danger'>Minor Automatic Added</div>";
                                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                                    //$this->session->set_flashdata('ALERT_MSG',$alert);
                                    redirect(base_url('AdminPanel/select_category'));

                                } else{
                                    echo "ByDefault Minor Not added";
                                    $error = "<div class='text-danger'> ByDefault Minor Not added</div>";
                                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                                    $this->session->set_flashdata('ALERT_MSG',$alert);
                                    redirect(base_url('AdminPanel/select_category'));

                                }

                            }else{

//                                $error = "<div class='text-danger'> Already selected minors</div>";
//                                $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
//                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url('AdminPanel/select_category'));

                            }

                        }
                        else if($result!=null && count($result)>1){
                            $data['minors'] = $result;
                            $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];
                            /***********DANGER**************
                            WE NEED TO UPDATE APPLICATION ID INSTEAD OF USER ID IN 
                             $data['applicantsMinors'] = $this->Application_model->getApplicantsMinorsByUserIdAndDisciplineID($user['USER_ID'],$valid_qualification['DISCIPLINE_ID']);
                            ***********DANGER**************/
                             $data['applicantsMinors'] = $this->Application_model->getApplicantsMinorsByApplicationIdAndDisciplineID($APPLICATION_ID,$valid_qualification['DISCIPLINE_ID']);
                            $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                            // $data['roll_no'] = $user['USER_ID'];
                            $this->load->view('include/header', $data);
                            $this->load->view('include/preloder');
                            $data['user'] =$admin;
                            $this->load->view('include/side_bar', $data);
                            $this->load->view('include/nav', $data);
                              $data['user'] = $user;
                            $this->load->view('admin/select_minor_subject', $data);
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
                        redirect(base_url('AdminPanel/student_update'));
                    }
                    // prePrint($application);



                } else {
                    echo "fees not found";
                    redirect(base_url('AdminPanel/search_student_by_cnic'));
                }

            } else {
                echo "this application id is not associate with you";
                redirect(base_url('AdminPanel/search_student_by_cnic'));
            }
        }else{
            echo "Application Id Not Found";
            redirect(base_url('AdminPanel/search_student_by_cnic'));
        }
    }

    //UPDATED FUNCTION ON 25-march-2021 VIEW FILE select_program.php
    public function select_program_soon(){

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
            // if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
            //     redirect(base_url('form/application_form'));
            //     exit();
            // }
            // if($application['STATUS_ID']<2){
            //     redirect(base_url('form/dashboard'));
            // }
            if ($application) {
                
                //form close from bachelor
               // $this->close_registration_for_bachelor($application);
                
                
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


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
//                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');
//
//                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
//                        exit("Sorry your challan is expired..");
//                    }


                    $data['profile_url'] = $this->profile;

                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);



                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByUserIdAndDisciplineID($user['USER_ID'],$valid_qualification['DISCIPLINE_ID']);
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
                            echo "Please Must Save Category";
                            $error = "<div class='text-danger'> Please Must Save category</div>";
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
                        $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST;
                        $data['lat_info'] =$lat_info;
                        
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
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);



                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    //UPDATED FUNCTION ON 25-march-2021
    public function upload_program_handler_soon(){
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


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByUserIdAndDisciplineID($user['USER_ID'],$valid_qualification['DISCIPLINE_ID']);
                        $minorMappingIds  = array();

                        foreach ($applicantsMinors as $applicantsMinor)
                        {
                            $minorMappingIds[]=$applicantsMinor['MINOR_MAPPING_ID'];
                        }

                        if(count($minorMappingIds)==0){
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                        }
                        if($llb_validation){


                            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
                            } else {
                                $error .= "<div class='text-danger'>Ticket Number / Seat Number Must be Enter</div>";
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
                                    $error .= "<div class='text-danger'>Invalid test Score</div>";
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
                                        $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
                                }
                            }
                            else {

                                if ($result_card_image == "")
                                    $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
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
                                        $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>$PROG_LIST_ID,'CHOICE_NO'=>$CHOICE_NO,'SHIFT_ID'=>1);
                                    }

                                    //prePrint($list_of_choice);
                                    if(count($list_of_choice)>0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info)){

                                    }
                                    else{
                                        $error .= "<div class='text-danger'>Your choices not added or updated this may not happen. Kindly contact technical team..</div>";
                                    }

                                }
                                else{
                                    $error .= "<div class='text-danger'>Your choices are invalid this may not happen. If you have any technical issue please contact technical team..!</div>";
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



                }
                else {
                    $error .= "<div class='text-danger'>fees not found</div>";
                    echo "fees not found";
                    exit();
                }

            }
            else {
                echo "this application id is not associate with you";
                $error .= "<div class='text-danger'>this application id is not associate with you</div>";
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

    //UPDATED FUNCTION ON 25-march-2021 VIEW FILE select_category.php
    public function select_category_soon(){

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
             // prePrint($application);
                if($application['PROGRAM_TYPE_ID'] == 1){
                    redirect(base_url('form/add_evening_category'));
                    exit();
                }
            if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/application_form'));
                exit();
            }
            if($application['STATUS_ID']<2){
                redirect(base_url('form/dashboard'));
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


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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

                        $applicantsMinors = $this->Application_model->getApplicantsMinorsByUserIdAndDisciplineID($user['USER_ID'],$valid_qualification['DISCIPLINE_ID']);
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
                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);
                        $program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        $data['VALID_PROGRAM_LIST'] =$valid_program_list;
                        $data['list_of_category'] =$list_of_categoy;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        //     prePrint($valid_program_list);
                        // prePrint($program_list);
//                            exit();
                        // $data['roll_no'] = $user['USER_ID'];
                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('select_category', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);



                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    //UPDATED FUNCTION ON 25-march-2021 MODAL FILE Application_model.php
    public function select_category_handler_soon(){

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
            $SPORTS_QUOTA_ID=6;

            $list_of_categoy = $this->Application_model->getApplicantCategory($APPLICATION_ID, $user['USER_ID']);

            $data = array();

            $GENERAL_MERIT=array('USER_ID'=>$user_id,'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$GENERAL_MERIT_ID,'CATEGORY_INFO'=>'');

            $data[] = $GENERAL_MERIT;


            if (isset($_POST['SELF_FINANCE'])) {
                $SELF_FINANCE=array('USER_ID'=>$user_id,'APPLICATION_ID'=>$APPLICATION_ID,'FORM_CATEGORY_ID'=>$SELF_FINANCE_ID,'CATEGORY_INFO'=>'');
                $data[] = $SELF_FINANCE;
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
                    $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
                }
                if (isset($_POST['DESIGNATION']) && isValidData($_POST['DESIGNATION'])) {
                    $DESIGNATION = strtoupper(isValidData($_POST['DESIGNATION']));
                } else {
                    $error .= "<div class='text-danger'>Designation of Employee Must be Enter</div>";
                }
                if (isset($_POST['DEPARTMENT_NAME']) && isValidData($_POST['DEPARTMENT_NAME'])) {
                    $DEPARTMENT_NAME = strtoupper(isValidData($_POST['DEPARTMENT_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Department Name Must be Enter</div>";
                }
                if (isset($_POST['IS_REGULAR']) && isValidData($_POST['IS_REGULAR'])) {
                    $IS_REGULAR = strtoupper(isValidData($_POST['IS_REGULAR']));
                } else {
                    $error .= "<div class='text-danger'>Job Nature Must Select</div>";
                }
                if (isset($_POST['RELATIONSHIP']) && isValidData($_POST['RELATIONSHIP'])) {
                    $RELATIONSHIP = strtoupper(isValidData($_POST['RELATIONSHIP']));
                } else {
                    $error .= "<div class='text-danger'>Relationship Must Select</div>";
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
                            $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
                    }
                }
                else {

                    if ($service_certificate_of_employee_image == "")
                        $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
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
                    $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
                }
                if (isset($_POST['AFFILIATED_DESIGNATION']) && isValidData($_POST['AFFILIATED_DESIGNATION'])) {
                    $DESIGNATION = strtoupper(isValidData($_POST['AFFILIATED_DESIGNATION']));
                } else {
                    $error .= "<div class='text-danger'>Designation of Employee Must be Enter</div>";
                }
                if (isset($_POST['AFFILIATED_DEPARTMENT_NAME']) && isValidData($_POST['AFFILIATED_DEPARTMENT_NAME'])) {
                    $DEPARTMENT_NAME = strtoupper(isValidData($_POST['AFFILIATED_DEPARTMENT_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Department Name Must be Enter</div>";
                }
                if (isset($_POST['AFFILIATED_IS_REGULAR']) && isValidData($_POST['AFFILIATED_IS_REGULAR'])) {
                    $IS_REGULAR = strtoupper(isValidData($_POST['AFFILIATED_IS_REGULAR']));
                } else {
                    $error .= "<div class='text-danger'>Job Nature Must Select</div>";
                }
                if (isset($_POST['AFFILIATED_RELATIONSHIP']) && isValidData($_POST['AFFILIATED_RELATIONSHIP'])) {
                    $RELATIONSHIP = strtoupper(isValidData($_POST['AFFILIATED_RELATIONSHIP']));
                } else {
                    $error .= "<div class='text-danger'>Relationship Must Select</div>";
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
                            $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($affiliated_service_certificate_of_employee_image == "")
                        $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
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
                    $error .= "<div class='text-danger'>Type Of Disability Must Select</div>";
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
                            $error .= "<div class='text-danger'>Must Upload Medical Certificate Image and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($medical_certificate_image == "")
                        $error .= "<div class='text-danger'>Must Upload Medical Certificate Image and image size must be less then 500kb </div>";
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
    
    public function print_student_card_2(){

		$admission_session_id=1;
		$shift_id=1;
		$program_ids=259;
		$part_id=1;

		$students = $this->RollNo_model->get_candidate_roll_no_report($admission_session_id,$shift_id,$program_ids,$part_id);
        // prePrint($students);
		$i = 0;
		$height = 1;
//		$cell_width = 45;

		$bullet = "".chr(149);
		$font_size = 10;
		$font_size_2 = 8;
		$row_size = 4;

		$pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
		$pdf->SetFont('Times', '', 6);
		$check_min_one = true;
		//prePrint($students);
		//exit();
		foreach($students as $student){
			$check_min_one= false;
			if($i%5 == 0){
				$pdf ->AddPage();
				$height = 1;
			}//if
			$i++;
			$USER_ID 		= $student['USER_ID'];
			$std_profile = $this->User_model->getUserByIdWithProfilePhoto($USER_ID);
			$std_guardian = $this->User_model->getGuardianByUserId($USER_ID);
            
			$PROFILE_IMAGE = $std_profile['PROFILE_IMAGE'];
			$CAMPUS_NAME 	= $student['CAMPUS_NAME'];
			$APPLICATION_ID = $student['APPLICATION_ID'];
			$CARD_ID 		= $student['CARD_ID'];
			$ACCOUNT_ID 	= $student['ACCOUNT_ID'];
			$PROGRAM_TITLE 	= $student['PROGRAM_TITLE'];
			$FIRST_NAME 	= $std_profile['FIRST_NAME'];
			$FNAME 			= $std_profile['FNAME'];
			$LAST_NAME 		= $std_profile['LAST_NAME'];
			$ROLL_NO 		= $student['ROLL_NO'];
			$CATEGORY_NAME 	= $student['CATEGORY_NAME'];
			$YEAR 			= $student['YEAR'];
			$PART_NAME 		= $student['PART_NAME'];
			$SHIFT 			= shift_decode($student['SHIFT_ID']);
			$CNIC			= $std_profile['CNIC_NO'];
			$blood_group 	= $std_profile['BLOOD_GROUP'];
			$emergency_contact = $std_guardian['MOBILE_NO'];
			$home_address = $std_profile['PERMANENT_ADDRESS'];
//$home_address = 'this is javed bharnani address behar colony is the enlginsh pair hath KOTRI JAMSHORO SINDH';
			$len =strlen($home_address);
			if($len>35){
				$address1	=  substr($home_address,0,35);
				$pos 		= strrpos($address1," ");
				$address1	=  substr($home_address,0,$pos);
				$address2	=  substr($home_address,$pos+1);
			}else{
				$address1=  $home_address;
				$address2=  "";
			}

			$department = "INSTITUTE OF MATHEMATICS AND COMPUTER SCIENCE";

			$len = strlen($department);
			$dept2 = "";
			$dept  = "";
			if($len>30){
				$dept =  substr($department,0,30);
				$pos = strrpos($dept," ");
				$dept=  substr($department,0,$pos);
				$dept2=  substr($department,$pos+1);
			}else{
				$dept=  $department;
				$dept2=  "";
			}

			$signature = base_url()."assets/img/director_signature.jpg";

			if(file_exists($signature)){
				$signature = base_url()."assets/img/director_signature.jpg";
			}

			$image_path = base_url()."assets/img/uos_logo.png";
//			echo $image_path;
			$url  = base_url()."assets/img/uos_logo.png";
//			$path = "https://itsc.usindh.edu.pk/eportal_resource/images/applicants_profile_image/profile_image_62043.jpg";

			/*
			if(!file_exists(PROFILE_IMAGE_CHECK_PATH.$PROFILE_IMAGE)){
				do {
					$resutl = $this->CI_ftp_Download(PROFILE_IMAGE_CHECK_PATH, $PROFILE_IMAGE);
					/// prePrint("RES".$resutl);
				}while(!$resutl);
				//exit();
			}
			*/

//			$profile_image = PROFILE_IMAGE_CHECK_PATH.$PROFILE_IMAGE;
//echo $profile_image;
//continue
			/*
			if(file_exists($path)&& $path != '../../'){

				$finfo = finfo_open(FILEINFO_MIME_TYPE);

				$type = finfo_file($finfo, $path);

				if(($type=="image/jpeg" || $type=="image/jpg" || $type=="image/png")){
					$image_path =  '../../'.$candidate["PROFILE_IMAGE"];
					if(($type=="image/jpeg" || $type=="image/jpg") &&strpos($image_path,".png")){
						$image_path ='../../../images/applicants/dummy/qr.png';
						// $image_path = str_replace(".png",".jpg",$image_path);
						//         echo($type);
						//   echo ($image_path);
						//   echo "</br>";
					}

				}else{
					//  print_r("ERROR");
					//  echo "</br>";
				}
			}

			if(file_exists('../../../images/applicants/'.$cand_id.'/qr.png'))
			{
				$url = '../../../images/applicants/'.$cand_id.'/qr.png';
			}
			*/

			$cur_year =  date('Y');
			$year = $YEAR;
			$year = $cur_year -$year;
			$valid_upto = "DECEMBER $cur_year";
			$y_a = array("","1st","2nd","3rd","4th","5th");
			$year = $y_a[$year];

			$logo_image1=base_url().'assets/img/front.png';
			$logo_image2=base_url().'assets/img/back.png';
		//	$pdf->Image(base_url().'assets/img/back.png',$img_back_x,$img_back_y,'8.636','5.588');
			$pdf ->Image($logo_image1, 18,$height, 86.36,55.88);
			$pdf ->Image($logo_image2, 105, $height, 86.36,55.88);

	//		$pdf ->Image('UOS_logo.png', 10, $height+5, 12, 12);
			$pdf ->SetFont('Arial', 'B', $font_size);
// $pdf ->Cell(15, $row_size,'',0,0);
			$pdf ->Cell(190, $row_size-2.5,'',0,1);
			$pdf ->Cell(18, $row_size,'',0,0);

			$pdf ->SetTextColor(255,255,255);
			$pdf->SetXY(55,$height+3);
			$pdf ->Cell(50, $row_size,'', 0,0);
			$pdf ->SetFont('Times', 'B', $font_size-1);
			$pdf ->Cell(73, $row_size,$dept, 0,0,'C'); //dept name on card backside header
			$pdf->SetXY(92.5,$height+8);
			$pdf ->Cell(73, $row_size,$dept2, 0,1,'C'); //dept name on card backside header


			$pdf->SetXY(20,$height+9);

			$pdf ->SetFont('Arial', 'B', $font_size_2);
			$pdf ->Cell(14, $row_size,'',0,0);
			$pdf ->Cell(52, $row_size+5,$CAMPUS_NAME, 0,1,'L');
			$subtitute = 0.2;
			$pdf ->SetFont('Times', '', $font_size-1);
			$pdf ->SetTextColor(77,166,255);
			$pdf->Cell(27);
//			$pdf->SetY($height+30);
//			$pdf->ln(10);
			$pdf ->Cell(40, $row_size-3,"Name:", 0,0,'L');
			$pdf ->SetFont('Times', '', $font_size-1);
			$pdf ->Cell(7, $row_size-3,'ID #',0,0, 'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf ->SetFont('Times', 'B', $font_size-1);
			$pdf ->Cell(10, $row_size-3,$APPLICATION_ID,0,0, 'L');
			$pdf ->SetFont('Times', '', $font_size-2);
			$pdf ->SetTextColor(77,166,255);
			$pdf ->Cell(13, $row_size-3,'', 0,0);
			$pdf ->Cell(30, $row_size-3,"Father ' s Name:", 0,0,'L');
			$pdf ->Cell(11, $row_size-3,"Category:", 0,0,'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf ->Cell(13, $row_size-3,category_decode($CATEGORY_NAME), 0,0,'L');

			$pdf->ln(1);

			$pdf ->SetFont('Times', 'B', 10);
			$pdf ->Cell(27, $row_size+1,'',0,0);
			$pdf ->Cell(50, $row_size+1,ucwords(strtoupper($FIRST_NAME)), 0,0,'L');
			$pdf ->Cell(17, $row_size+1,'',0,0);

			$pdf ->Cell(3, $row_size+1,'', 0,0);
			$pdf ->Cell(90, $row_size+1,ucwords(strtoupper($FNAME)), 0,1,'L');
			$pdf ->Cell(27, $row_size+1,'',0,0);

			$pdf->ln(-1.5);
			$pdf ->SetTextColor(77,166,255);
			$pdf ->SetFont('Times', '', 9);
			$pdf->Cell(97);
			$pdf ->Cell(90, $row_size,"Surname:", 0,1,'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf ->SetFont('Times', '', $font_size-1);
			$pdf ->SetTextColor(77,166,255);
			$pdf ->Cell(27, $row_size+1,'',0,0);
			$pdf ->Cell(70, $row_size,'Roll No:', 0,0);
			$pdf ->SetTextColor(0,0,0);
			$pdf ->SetFont('Times', 'B', $font_size-1);
			$pdf ->Cell(90, $row_size,ucwords(strtoupper($LAST_NAME)), 0,1,'L');
			//$pdf->ln(1);

			$pdf ->SetTextColor(0,0,0);
			$pdf ->SetFont('Times', 'B', 10);
			$pdf ->Cell(27, $row_size,'',0,0);
			$pdf ->Cell(50, $row_size,($ROLL_NO), 0,0,'L');
			$pdf ->Cell(17, $row_size,'',0,0);
			$pdf ->Cell(3, $row_size,'', 0,0);
			$pdf ->SetFont('Times', '', 9);
			$pdf ->SetTextColor(77,166,255);
			$pdf ->Cell(20, $row_size,"Blood Group:", 0,0,'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf ->Cell(10, $row_size,$blood_group, 0,0,'L');
			$pdf ->SetTextColor(77,166,255);

			$pdf ->Cell(30, $row_size,"Contact Number:", 0,0,'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf ->Cell(100, $row_size,$emergency_contact, 0,0,'L');

			//$pdf->ln(3);

			$pdf ->SetTextColor(0,0,0);
			$pdf ->SetFont('Times', 'B', 10);
			$pdf ->Cell(27, $row_size,'',0,0);
			$pdf ->Cell(50, $row_size,($ROLL_NO), 0,0,'L');

			$pdf->ln(4);
			$pdf ->SetFont('Times', '', 9);
			$pdf ->SetTextColor(77,166,255);
			$pdf ->Cell(27, $row_size,'',0,0);
			$pdf ->Cell(70, $row_size,"Valid upto", 0,0,'L');
			$pdf ->SetFont('Times', '', 9);
			$pdf ->SetTextColor(77,166,255);
			$pdf ->Cell(13, $row_size,"Address:", 1,0,'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf ->Cell(65, $row_size,$address1, 0,0,'L');
			$pdf ->SetTextColor(0,0,0);
			$pdf->ln(4);
			$pdf ->SetFont('Times', '', 9);
			$pdf ->SetTextColor(255,0,0);
			$pdf ->Cell(27, $row_size,'',0,0);
			$pdf ->Cell(38, $row_size,$valid_upto, 0,0,'L');
			$pdf ->SetFont('Times', '',7);
			$pdf ->SetTextColor(0,0,0);
			$pdf ->Cell(32, $row_size,"DIRECTOR ADMISSIONS", 0,0,'L');
			$pdf ->SetFont('Times', '', 9);
			$pdf ->SetTextColor(0,0,0);
			$pdf ->Cell(90, $row_size,$address2, 0,0,'L');

			$pdf ->SetTextColor(0,0,0);
            try{
            $image_file_name = "../eportal_resource/temp_images/$PROFILE_IMAGE";
            file_put_contents($image_file_name, base64_decode($std_profile['PROFILE_PICTURE']));
			$pdf ->Image($image_file_name, 18, $height+17, 18, 25);
			unlink($image_file_name);
            }catch(Exception $ex){
                $pdf ->Image($image_path, 18, $height+17, 18, 25);
            }
//			$pdf ->Image("profile_image_62043.jpg", 18, $height+17, 18, 25,'JPG','https://itsc.usindh.edu.pk/eportal_resource/images/applicants_profile_image/');


			$pdf ->SetFont('Times', '', ($font_size-1));
			$pdf ->Cell(190, 0.5,'',0,1);
			$pdf ->Cell(25, $row_size,'',0,0);
//
//			$pdf ->SetTextColor(77,166,255);
//			$pdf ->SetFont('Times', '', $font_size-1);
//			$pdf ->Cell(17, $row_size-$subtitute,'',0,0);
//			$pdf ->Cell(3, $row_size-$subtitute,'', 0,0);
//
//			$pdf ->SetFont('Times', '', $font_size);
//			$pdf ->Cell(91, $row_size-$subtitute,"", 0,0,'C');
//			$pdf ->Cell(6, $row_size-$subtitute,"", 0,0);
//			$pdf ->SetFont('Times', '', 9);
//			$pdf ->SetTextColor(77,166,255);
//
//			$pdf ->SetFont('Times', '', $font_size-1);
//			$pdf->SetXY(13,$height+33);
//			$pdf ->Cell(24, $row_size-$subtitute,'',0,0);
//			$pdf ->Cell(17, $row_size-$subtitute,'',0,0);
//			$pdf ->Cell(12,$row_size-$subtitute,"", 0,0);
			$pdf ->Image($signature, 80, $height+23, 15, 15);
//
//			$pdf ->SetFont('Times', 'B', $font_size_2);
//			$pdf ->Cell(27, $row_size-$subtitute,'',0,0);
//			$pdf ->SetTextColor(255,0,0);
//			$pdf ->Cell(50, $row_size+5,$valid_upto, 0,0,'L');
//			$pdf ->SetTextColor(0,0,0);
//			$pdf ->Cell(17, $row_size-$subtitute,'',0,0);
//			$pdf ->Cell(12, $row_size-$subtitute,"", 0,0);
//			$pdf ->SetFont('Times', '', $font_size-1);
////			$pdf ->SetTextColor(77,166,255);
//			$pdf ->SetTextColor(0,0,0);
//			$pdf ->SetFont('Times', '', ($font_size-4));
//			$pdf ->Cell(55, $row_size-$subtitute,'DIRECTOR ADMISSIONS',0,0, 'C');
//			$pdf ->Cell(3, $row_size-$subtitute,"", 0,0);
//
//			$pdf ->SetTextColor(0,0,0);
//			$pdf ->Cell(30, $row_size-$subtitute,"Address", 0,1,'L');
//			$pdf ->Cell(24, $row_size-$subtitute,'',0,0);
//			$pdf ->Cell(23, $row_size-$subtitute,'', 0,0,'C');
//
//			$pdf ->SetFont('Arial', 'B', $font_size-2);
//			$pdf ->Cell(90, $row_size-$subtitute,ucwords($address1), 0,1,'L');
//			$pdf ->Cell(24, $row_size-$subtitute,'',0,0);
//
//			$pdf ->SetFont('Times', 'B', $font_size-2);
//			$pdf ->Cell(90, $row_size-$subtitute,ucwords($address2), 0,1,'L');
//			$pdf ->Cell(190, 1,'', 0,1,0);

//			$pdf ->SetFont('Times', '', $font_size);
//			$pdf ->Cell(10, $row_size-$subtitute-2,'',0,0);
//			$pdf ->Cell(60, $row_size-$subtitute-2,"", 0,0,'C');
//			$pdf ->Cell(20, $row_size-$subtitute-2,'',0,0);
//			$pdf ->Cell(10, $row_size-$subtitute-2,"", 0,0);
//			$pdf ->Cell(90, $row_size-$subtitute-2,"", 0,1,'C');

			$pdf ->SetFont('Times', 'B', $font_size-2);
//			$pdf ->SetTextColor(255,255,255);
			$pdf ->SetTextColor(0,0,0);
//$pdf ->Cell(10, $row_size,'',1,0);
			$pdf->SetXY(18,$height+45);
			$pdf ->Cell(90, 5,"$PROGRAM_TITLE -  $SHIFT", 0,1,'C');
			$pdf ->Cell(90, 5,"$PART_NAME - ACADEMIC YEAR $cur_year", 0,0,'C');
            	
//$pdf ->Cell(20, $row_size,'',1,0);
//			$pdf ->Cell(10, $row_size,"", 0,0);
//			$pdf ->Cell(8, $row_size,'',0,0);
			$pdf ->SetFont('Times', 'B', $font_size);
			$pdf ->SetTextColor(0,0,0);
			//$cnic_nos = str_split($CNIC);
			//unset($cc_nic);
			$pdf->SetXY(110,$height+46.1);
            //$CNIC ="4120446313587";
			for($k = 0; $k<strlen($CNIC); $k++) {
				$cc_nic=$CNIC[$k];
				if ($k == 5){
					//$cc_nic="-";
						$pdf->Cell(5.1, 5.2, "-", 0, 0, 'C');
				}elseif ($k == 12){
					//$cc_nic.="-";
						$pdf->Cell(5.1, 5.2, "-", 0, 0, 'C');
				}
				$pdf->Cell(5.1, 5.2, $cc_nic, 0, 0, 'C');
				/*
				$pdf->SetXY($x,$y);
				$pdf->Cell($cell_w+5, 5, "" . $cnic_nos[$k], 0, 0, 'C');//already 1
				if ($k == 4){
					$pdf->SetXY($x+5,$y);
					$pdf->Cell(5, 5, "-", 0, 0, 'C');
				}elseif ($k == 11){
					$pdf->SetXY($x+7,$y);
					$pdf->Cell(5, 5, "-", 0, 0, 'C');
				}
				$x = $x+5;
			*/
			}
			/*
			$x=105;
			$y = $height+59;
			$pdf->SetXY($x,$y);
			$pdf->Cell(100, 5,$cc_nic, 0, 0, 'C');
			*/
			/*
			$pdf ->Cell(5, 5,'', 0,1,'C');
			$pdf ->Cell(190, $row_size+5,'', 0,1,'C');
			*/
			$data = $APPLICATION_ID. "~". $USER_ID . "~".$CNIC."~" . $ROLL_NO . "~" . $CARD_ID . "~" . $ACCOUNT_ID;
			$data = EncryptThis($data);
			//$result=str_pad($data, 10, "0", STR_PAD_LEFT);
//			$s=$data;

//			$result=substr($s, strlen($s) - 80, strlen($s));
//			prePrint($result);
//			exit;
			QRcode::png("$data","../eportal_resource/qr_images/".$APPLICATION_ID.".png", 'QR_ECLEVEL_L', 3, 2);
			$path_qr="../eportal_resource/qr_images/".$APPLICATION_ID.".png";

			$pdf->SetXY(40,$height+1);
			$pdf->Image($path_qr,175,$height+1, 14, 14);

			$height = $height+56.5;
			$row_size=4;
		}
		/*if($check_min_one){
			$pdf ->AddPage();
			$pdf ->SetFont('Arial', 'B', $font_size);
			$pdf ->Cell(190, 10,'NO student found',0,1);
		}*/
// exit();
		$pdf ->Output();
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
	
	public function get_all_candidate_challan_verification(){

        $session_id = 10;
        $program_type_id = 1;
        
        
       $BRANCH_CODE = $ADMMISSION_SESSION_ID = $BRANCH_ID =$offset = $IS_PROFILE_PHOTO_VERIFIED = $DISTRICT_ID= $challan_upload = $limit=$is_verified=$gender=$date_time=null;
        if(isset($_GET['IS_VERIFIED'])){
            $is_verified = $_GET['IS_VERIFIED'];
        }
        if(isset($_GET['DATE_TIME'])){
            $date_time = $_GET['DATE_TIME'];
        }
        if(isset($_GET['GENDER'])){
            $gender = $_GET['GENDER'];
        }
         if(isset($_GET['LIMIT'])){
            $limit = $_GET['LIMIT'];
        }
         if(isset($_GET['CHALLAN_UPLOAD'])){
            $challan_upload = $_GET['CHALLAN_UPLOAD'];
        }
        if(isset($_GET['PROGRAM_TYPE_ID'])){
            $program_type_id = $_GET['PROGRAM_TYPE_ID'];
        }
        if(isset($_GET['DISTRICT_ID'])){
            $DISTRICT_ID = $_GET['DISTRICT_ID'];
        }
        if(isset($_GET['BRANCH_CODE'])){
            $BRANCH_CODE = $_GET['BRANCH_CODE'];
        }
        if(isset($_GET['ADMMISSION_SESSION_ID'])){
            $ADMMISSION_SESSION_ID = $_GET['ADMMISSION_SESSION_ID'];
        }
        if(isset($_GET['PROVINCE_ID'])){
            $PROVINCE_ID = $_GET['PROVINCE_ID'];
        }
        if(isset($_GET['OFFSET'])){
            $offset = ($_GET['OFFSET']-1)*$limit;
            
            // prePrint($offset);
            // exit();
        }
         if(isset($_GET['IS_PROFILE_PHOTO_VERIFIED'])){
            $IS_PROFILE_PHOTO_VERIFIED = $_GET['IS_PROFILE_PHOTO_VERIFIED'];
        }
    //   $photodata = $this->AdmitCard_model->getDataForPhoto($date_time,$program_type_id,$gender,$is_verified,$limit,$challan_upload,$DISTRICT_ID,$IS_PROFILE_PHOTO_VERIFIED,$PROVINCE_ID);
        $list  =  $this->FeeChallan_model->get_form_challan_for_verification($is_verified,$limit,$offset,$BRANCH_CODE,$DISTRICT_ID,$ADMMISSION_SESSION_ID);
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $data['admission_session_data'] = $this->Admission_session_model->getAdmissionSession();
        //$this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        $data['list'] = $list;
        
        $data['limit'] = $limit;
        $data['offset'] = $_GET['OFFSET'];
        $data['admission_id'] = $ADMMISSION_SESSION_ID;
        $data['is_verified'] = $is_verified;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/get_all_candidate_challan_verification',$data);
     

        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
    public function lat_recommendation(){
        
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];
         
        if(isset($_GET['APPLICATION_ID'])&&isset($_GET['STATUS'])){
              $APPLICATION_ID = $_GET['APPLICATION_ID'];
              $STATUS = $_GET['STATUS'];
              echo "$APPLICATION_ID $STATUS";
              if($STATUS==1){
                  $STATUS = "Y";
              }else{
                  $STATUS = "N";
              }
              $this->Application_model->updateChoiceByApplicationAndProgListID($APPLICATION_ID,143,array("IS_RECOMMENDED"=>$STATUS),$user_id);
          }
    }
    public function get_all_candidate_lat_information(){

        $session_id = 2;
        $program_type_id = 2;
        if($date_time == 00){
            $date_time=null;
        }
        
       $IS_RECOMMENDED = $ADMISSION_SESSION_ID =$offset = $IS_PROFILE_PHOTO_VERIFIED = $DISTRICT_ID= $challan_upload = $limit=$is_verified=$gender=$date_time=null;
        if(isset($_GET['ADMISSION_SESSION_ID'])){
            $ADMISSION_SESSION_ID = $_GET['ADMISSION_SESSION_ID'];
        }
        if(isset($_GET['DATE_TIME'])){
            $date_time = $_GET['DATE_TIME'];
        }
        if(isset($_GET['GENDER'])){
            $gender = $_GET['GENDER'];
        }
         if(isset($_GET['LIMIT'])){
            $limit = $_GET['LIMIT'];
        }
         if(isset($_GET['CHALLAN_UPLOAD'])){
            $challan_upload = $_GET['CHALLAN_UPLOAD'];
        }
        if(isset($_GET['PROGRAM_TYPE_ID'])){
            $program_type_id = $_GET['PROGRAM_TYPE_ID'];
        }
        if(isset($_GET['DISTRICT_ID'])){
            $DISTRICT_ID = $_GET['DISTRICT_ID'];
        }
        if(isset($_GET['BRANCH_CODE'])){
            $BRANCH_CODE = $_GET['BRANCH_CODE'];
        }
        if(isset($_GET['IS_RECOMMENDED'])){
            $IS_RECOMMENDED = $_GET['IS_RECOMMENDED'];
        }
        if(isset($_GET['OFFSET'])){
            $offset = $_GET['OFFSET'];
            // prePrint($offset);
            // exit();
        }
         if(isset($_GET['IS_PROFILE_PHOTO_VERIFIED'])){
            $IS_PROFILE_PHOTO_VERIFIED = $_GET['IS_PROFILE_PHOTO_VERIFIED'];
        }
    //   $photodata = $this->AdmitCard_model->getDataForPhoto($date_time,$program_type_id,$gender,$is_verified,$limit,$challan_upload,$DISTRICT_ID,$IS_PROFILE_PHOTO_VERIFIED,$PROVINCE_ID);
           $list  =  $this->TestResult_model->getLatInformationByAdmissionSessionId($ADMISSION_SESSION_ID,$limit,$offset,$IS_RECOMMENDED);
         $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
       
        //$this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        $data['list'] = $list;
      
        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/get_all_candidate_lat_information',$data);
     

        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }

	public function get_all_candidate_image(){
        
        $session_id = 10;
        $program_type_id = 1;
        if($date_time == 00){
            $date_time=null;
        }
    $offset = 1;    
      $admission_session_id =  $IS_PROFILE_PHOTO_VERIFIED = $DISTRICT_ID= $challan_upload = $limit=$is_verified=$gender=$date_time=null;
        if(isset($_GET['IS_VERIFIED'])&&isValidData($_GET['IS_VERIFIED'])){
            $is_verified = $_GET['IS_VERIFIED'];
        }
        if(isset($_GET['DATE_TIME'])){
            $date_time = $_GET['DATE_TIME'];
        }
        if(isset($_GET['GENDER'])&&isValidData($_GET['GENDER'])){
            $gender = $_GET['GENDER'];
        }
         if(isset($_GET['LIMIT'])){
            $limit = $_GET['LIMIT'];
        }
         if(isset($_GET['CHALLAN_UPLOAD'])&&isValidData($_GET['CHALLAN_UPLOAD'])){
            $challan_upload = $_GET['CHALLAN_UPLOAD'];
        }
        if(isset($_GET['ADMISSION_SESSION_ID'])){
            $admission_session_id = $_GET['ADMISSION_SESSION_ID'];
        }
        if(isset($_GET['DISTRICT_ID'])){
            $DISTRICT_ID = $_GET['DISTRICT_ID'];
        }
        if(isset($_GET['PROVINCE_ID'])){
            $PROVINCE_ID = $_GET['PROVINCE_ID'];
        }
        if(isset($_GET['IS_PROFILE_PHOTO_VERIFIED'])&&isValidData($_GET['IS_PROFILE_PHOTO_VERIFIED'])){
            $IS_PROFILE_PHOTO_VERIFIED = $_GET['IS_PROFILE_PHOTO_VERIFIED'];
        }
        if(isset($_GET['OFFSET'])){
           $offset =($_GET['OFFSET']-1)*$limit;
        }else{
            $_GET['OFFSET']=1;
        }
       $photodata = $this->AdmitCard_model->getDataForPhoto($date_time,$admission_session_id,$gender,$is_verified,$limit,$challan_upload,$DISTRICT_ID,$IS_PROFILE_PHOTO_VERIFIED,$PROVINCE_ID,$offset);
        // prePrint($photodata);
        // exit();
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
           $data['admission_session_data'] = $this->Admission_session_model->getAdmissionSession();
        //$this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        $data['photodata'] = $photodata;
        
        $data['limit'] = $limit;
        $data['offset'] = $_GET['OFFSET'];
        $data['admission_id'] = $admission_session_id;
        $data['is_verified'] = $is_verified;
        $data['IS_PROFILE_PHOTO_VERIFIED'] = $IS_PROFILE_PHOTO_VERIFIED;
        $data['CHALLAN_UPLOAD'] = $challan_upload;
        $data['GENDER'] = $gender;
        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/photo_verification',$data);
     

        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
   
    public function change_photo_handler(){
        
        
          
          $PROFILE_IMAGE="";
            if(isset($_POST['user_id'])&&is_numeric($_POST['user_id'])&&$_POST['user_id']>0){
                $user_id = $_POST['user_id'];
            }else{
                $error .= "<div class='text-danger'>User id Invalid</div>";
            }
            if (isset($_FILES['file'])) {
                // prePrint($_FILES['profile_image'][]);
                if (isValidData($_FILES['file']['name'])) {

                    $res = $this->upload_image('file', "profile_image_" . $user_id);
                    if ($res['STATUS'] === true) {
                        $PROFILE_IMAGE = $res['IMAGE_NAME'];

                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                        $error .= "<div class='text-danger'>Must Upload Profile Picture</div>";
                    
                }
            } else {
                
                    $error .= "<div class='text-danger'>Must Upload Profile Picture</div>";
                }
                if($error==""){
                    $form_array = array("PROFILE_IMAGE"=>$PROFILE_IMAGE);
                    $res = $this->User_model->updateUserById($user_id,$form_array);
                    if($res===1||$res===0){
                        $reponse['MESSAGE'] = "successfully update";
               $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));  
                    }else{
                         $reponse['MESSAGE'] = "Something went wrong";
                       $this->output
                        ->set_status_header(500)
                        ->set_content_type('application/json', 'utf-8')
                        ->set_output(json_encode($reponse)); 
                    }
                }else{
                     $reponse['MESSAGE'] = $error;
               $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
                }

    }
    
    public function photoHandler(){
        $user = $this->session->userdata($this->SessionName);
        
        $user_id = $user['USER_ID'];
        $application_ids = array_keys($_POST);
        
        if(count($application_ids)>0){
        $form_array = array("IS_PROFILE_PHOTO_VERIFIED"=>1);
        $this->Application_model->updateApplicationsByIds($application_ids,$form_array,$user_id);
        }
        //prePrint($application_ids);
        
    }  
    
    
    public function challan_image_verification(){
        $user = $this->session->userdata($this->SessionName);
            $admin_id = $user['USER_ID'];
            
        if(isset($_GET['APPLICATION_ID'])&&isset($_GET['STATUS'])&&isset($_GET['FORM_CHALLAN_ID'])&&isValidData($_GET['APPLICATION_ID'])&&isValidData($_GET['FORM_CHALLAN_ID'])){
            $status = isValidData($_GET['STATUS']);
            $APPLICATION_ID = isValidData($_GET['APPLICATION_ID']);
            $FORM_CHALLAN_ID = isValidData($_GET['FORM_CHALLAN_ID']);
            $application = $this->Application_model->getApplicationByApplicationId( $APPLICATION_ID);
            $email = $application['EMAIL'];
			
				$form_status = json_decode($application['FORM_STATUS'],true);

				//$form_status['CHALLAN']['STATUS'] = $form_fee_status;
			//	$form_status['PROFILE_PHOTO']['STATUS'] = $profile_photo_verified_status;
				//$form_status['ADDITIONAL_DOCUMENT']['STATUS'] = $additional_documents_verified_status;

            if($status>=0 && $status <= 2){
                if($status == 1){
                    $stat = 'Y';
                    $form_status['CHALLAN']['STATUS'] = 'VERIFIED';
                }else if($status == 2){
                    $stat = 'R';
                    $form_status['CHALLAN']['STATUS'] = 'REJECTED';
                     $email_subject ='CHALLAN NOT VERIFIED';
    $email_body = "<img src='https://usindh.edu.pk/wp-content/uploads/2018/10/2logo-usindh.png'> <br> <p style='font-size:14pt'>السلام عليكم</p><br>
    Dear Candidate,<br><br>Your uploaded challan image is not verified, your Admit card will not be issued. If you have paid the challan within due dates, please visit Directorate of Admissions, ".UNIVERSITY_NAME." along with original paid copy of your challan upto Wednesday 27.09.2024 within office hours (09:00am to 03:00pm) OR you can send it through email at dir.adms@umpk.edu.pk
					
                     
                      <br>
                       <br>
                        <br>
                      
                      Best Regards, <br>
                      -------------------------------------<br>
                      DIRECTOR ADMISSIONS<br>
                      ".UNIVERSITY_NAME.", Pakistan.<br>
                      Email: dir.adms@umpk.edu.pk<br>";
                            $res  = send_smtp_email($email_subject,$email_body,$email,$this);
                            
                }else{
                    $stat = 'N';
                    $form_status['CHALLAN']['STATUS'] = 'PENNDING VERIFICATION';
                }
                //prePrint($form_status);
                   
               $form_array = array('FORM_STATUS'=>json_encode($form_status));
               $this->Application_model->updateApplicationById($APPLICATION_ID,$form_array,$admin_id);
                $c_date = date('Y-m-d');
                 $record = array("IS_VERIFIED"=>$stat,"PAID"=>$stat,"REMARKS"=>"MANUALLLY BY USER_ID = ".$admin_id,"VERIFIER_ID"=>$admin_id,"VERIFICATION_DATE"=>$c_date);
               $this->FeeChallan_model->update("FORM_CHALLAN_ID = $FORM_CHALLAN_ID",$record,"null","form_challan");
               $reponse['MESSAGE'] = "Successfully";
               $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
            }else{
               $reponse['MESSAGE'] = "Invalid Status $status";
               $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
            }
            
        }else{
                $reponse['MESSAGE'] = "Invalid Application Id / Status Id ";
                $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
             
        }
    }
    
    public function update_photo_by_application_id(){
            $user = $this->session->userdata($this->SessionName);
            $admin_id = $user['USER_ID'];
        if(isset($_GET['APPLICATION_ID'])&&isset($_GET['STATUS'])&&isValidData($_GET['APPLICATION_ID'])){
            $status = isValidData($_GET['STATUS']);
            $APPLICATION_ID = isValidData($_GET['APPLICATION_ID']);
            $application = $this->Application_model->getApplicationByApplicationId( $APPLICATION_ID);
			//	prePrint($application);
			$email = $application['EMAIL'];
				$form_status = json_decode($application['FORM_STATUS'],true);

				//$form_status['CHALLAN']['STATUS'] = $form_fee_status;
			//	$form_status['PROFILE_PHOTO']['STATUS'] = $profile_photo_verified_status;
				//$form_status['ADDITIONAL_DOCUMENT']['STATUS'] = $additional_documents_verified_status;

            if($status>=0 && $status <= 2){
                if($status == 1){
                    $form_status['PROFILE_PHOTO']['STATUS'] = 'VERIFIED';
                }else if($status == 2){
                    $email_subject ='YOUR PROFILE PHOTO / IMAGE IS REJECTED';
    $email_body = "<img src='https://usindh.edu.pk/wp-content/uploads/2018/10/2logo-usindh.png'> <br> <p style='font-size:14pt'>السلام عليكم</p><br>
    Dear Candidate,<br><br>Your profile photo has been <b>REJECTED</b> due to inappropriate photo, kindly <b>re-upload</b> your profile photo otherwise your Pre-Entry Test Admit Card/Slip will not be generated.
                    <br>Please follow these steps to re-upload your passport size profile picture:
                    <br>1. Login to your Admission E-portal account,
                    <br>2. Navigate to the Dashboard and look for the option to Re-upload Profile Picture and upload a new appropriate image.
        
                     
                      <br>
                       <br>
                        <br>
                      
                      Best Regards, <br>
                      -------------------------------------<br>
                      DIRECTOR ADMISSIONS<br>
                      ".UNIVERSITY_NAME.", Pakistan.<br>
                      Email: dir.adms@umpk.edu.pk<br>";
                            $res  = send_smtp_email($email_subject,$email_body,$email,$this);
                    $form_status['PROFILE_PHOTO']['STATUS'] = 'RE_UPLOAD';
                }else{
                    $form_status['PROFILE_PHOTO']['STATUS'] = 'PENNDING VERIFICATION';
                }
                //prePrint($form_status);
                
               $form_array = array('FORM_STATUS'=>json_encode($form_status),"IS_PROFILE_PHOTO_VERIFIED"=>$status);
               $this->Application_model->updateApplicationById($APPLICATION_ID,$form_array,$admin_id);
               $reponse['MESSAGE'] = "Successfully";
               $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
            }else{
               $reponse['MESSAGE'] = "Invalid Status $status";
               $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
            }
            
        }else{
                $reponse['MESSAGE'] = "Invalid Application Id / Status Id ";
                $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse)); 
             
        }
     }
    
    public function update_gender_by_user_id(){
         $user = $this->session->userdata($this->SessionName);
        $admin_id = $user['USER_ID'];
        if(isset($_GET['USER_ID'])&&isset($_GET['GENDER'])&&isValidData($_GET['USER_ID'])&&isValidData($_GET['GENDER'])){
            $USER_ID = isValidData($_GET['USER_ID']);
            $GENDER = isValidData($_GET['GENDER']);
            
            if($GENDER=='M'||$GENDER=='F'){
                $array=array("GENDER"=>$GENDER);
                $res = $this->User_model->updateUserById($USER_ID,$array,$admin_id);
                if($res==1 ||$res==0){
                    $reponse['MESSAGE'] = 'SUCCESSFULLY SAVED';
                
                $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
                }else{
                    $reponse['MESSAGE'] = 'GENDER NOT SAVED SOMETHING WENT WRONG';
                
                $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
                }
            }else{
                $reponse['MESSAGE'] = 'INVALID GENDER';
                
                $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
            }
            
        }else{
              $reponse['MESSAGE'] = 'INVALID GENDER / USER_ID';
                
                $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }
    
    
    public function get_all_unmatch(){
        //echo "yes";
        $list  =  $this->FeeChallan_model->get_form_challan_for_verification();
        //$data = $this->FeeChallan_model->get_cmd_unmatch();
        prePrint(count($list));
        $count = 0;
        $list_of_challan = array();
        echo "<table border=1>";
        //foreach($data as $d){
                $k = 0;
              foreach($list as $obj){
                  
                  //  if($d['CREDIT_DATE']==$obj['CHALLAN_DATE'] && $d['CANDIDATE_NAME']==$obj['FIRST_NAME']&&  rtrim($d['BRANCH_CODE'],'0')==rtrim($obj['BRANCH_CODE'],'0')){
                        
                    $k++;
                    if($count == 0){
                          echo "<tr>
                        
                          <td>NO</td>
                          
                           <td>BRANCH ID</td>
                                <td>BRANCE CODE</td>
                                <td>BRANCE NAME</td>
                          <td>APPLICATION_ID</td>
                            <td>FIRST_NAME</td>
                           
                             <td>FNAME</td>
                           
                           
                             
                              <td>FORM_CHALLAN_ID</td>
                               <td>CHALLAN DATE</td>
                               
                            
                             
                            </tr>";
                    }
                    $image_path = base_url().EXTRA_IMAGE_PATH.$obj['CHALLAN_IMAGE'];
                    // <td><img width='100' height='200' src='$image_path'/></td>
                    echo "<tr>
                   
                    <td>$k</td>
         
                      <td>{$obj['BRANCH_ID']}</td>
                                 <td>{$obj['BRANCH_CODE']}</td>
                                  <td>{$obj['BRANCH_NAME']}</td>
                                  <td>{$obj['APPLICATION_ID']}</td>
                                 
                            
                            
                            
                             <td>{$obj['FIRST_NAME']}</td>
                             <td>{$obj['FNAME']}</td>
                             
                             
                              <td>{$obj['FORM_CHALLAN_ID']}</td>
                             
                                <td>{$obj['CHALLAN_DATE']}</td>
                             <td><img width='100' height='200' src='$image_path'/></td>
                             
                            
                            
                            </tr>";
                           // array_push($list_of_challan,$obj);
                    //prePrint($list[$challan_no]);
            
                $count++;
                 //   }
              //  }
                
    
        }
      
        
        echo "</table>";
        //prePrint(count($data));
       // prePrint(count($list_of_challan));
      //  prePrint($list_of_challan);
         // $user = $this->session->userdata($this->SessionName);
        //$user_role = $this->session->userdata($this->user_role);
        //$user_id = $user['USER_ID'];
        //  echo $this->FeeChallan_model->update_form_challan($list_of_challan,$user_id);
        
    }
        //UPDATED FUNCTION ON 18-OCT-2020 VIEW FILE select_program.php
    public function select_program(){
     $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];
          if(!($role_id==1||$role_id==4)){
            redirect(base_url()."AdminPanel/student_update");
        }
        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');


           
            $user = $this->User_model->getUserById($USER_ID);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            
            // prePrint($application);
            // if($application['PROGRAM_TYPE_ID'] == 1){
            //     redirect(base_url('form/add_evening_category'));
            //     exit();
            // }
           
            if ($application) {
                
                //form close from bachelor
                //$this->close_registration_for_bachelor($application);
                
                
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


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                            echo "Please Must Save Category";
                            $error = "<div class='text-danger'> Please Must Save category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('AdminPanel/select_category'));
                            exit();
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
                        $CHOOSEN_PROGRAM_LIST_NEW = array();
                        foreach($CHOOSEN_PROGRAM_LIST as $cho){
                            if($cho['IS_SPECIAL_CHOICE']!='Y'){
                                array_push($CHOOSEN_PROGRAM_LIST_NEW,$cho);
                            }
                        }
                         $program_list       = $prog_list_by_shift;
                        $data['VALID_PROGRAM_LIST'] =$valid_exact_program;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST_NEW;
                        $data['lat_info'] =$lat_info;
                      //  $data['user'] =$form_data;
                      $data['form_data'] =$form_data;
                        $data['application'] =$application;
                        $data['category'] =$list_of_categoy;
                        
                        $precentage = ($valid_qualification['OBTAINED_MARKS']*100/$valid_qualification['TOTAL_MARKS']);
                        $data['precentage'] =round($precentage,2);

                        //   prePrint($program_list);
                        // exit();
                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('admin/select_program', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                        redirect(base_url()."AdminPanel/student_update");
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);



                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    //UPDATED FUNCTION ON 30-OCT-2020
    public function upload_program_handler(){
      //  exit();
        $error="";
        $config_a = array();
        $config_a['maintain_ratio'] = true;
        $config_a['width']         = 360;
        $config_a['height']       = 500;
        $config_a['resize']       = false;
        $reponse['RESPONSE'] = "ERROR";


        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
            
            $user = $this->User_model->getUserById($USER_ID);
                $user_id=$USER_ID;
            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
           
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


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                        }
                        if($llb_validation){


                            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
                            } else {
                                $error .= "<div class='text-danger'>Ticket Number / Seat Number Must be Enter</div>";
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
                                    $error .= "<div class='text-danger'>Invalid test Score</div>";
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
                                        $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
                                }
                            }
                            else {

                                if ($result_card_image == "")
                                    $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
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
                                        $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>$PROG_LIST_ID,'CHOICE_NO'=>$CHOICE_NO,'SHIFT_ID'=>1);
                                    }

                                    //prePrint($list_of_choice);
                                    if(count($list_of_choice)>0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info,$admin_id)){
                                         foreach($list_of_choice as $choice){
                                             $where = array('CATEGORY_ID != '=>24,'APPLICATION_ID'=>$choice['APPLICATION_ID'],'PROG_LIST_ID'=>$choice['PROG_LIST_ID'],'SHIFT_ID'=>$choice['SHIFT_ID']);
                                             $data = array('CHOICE_NO'=>$choice['CHOICE_NO']);
                                             $this->Application_model->updateApplicationSelectionList($data,$where);
                                         }
                                    }
                                    else{
                                        $error .= "<div class='text-danger'>Your choices not added or updated this may not happen. Kindly contact technical team..</div>";
                                    }

                                }
                                else{
                                    $error .= "<div class='text-danger'>Your choices are invalid this may not happen. If you have any technical issue please contact technical team..!</div>";
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
                          redirect(base_url()."AdminPanel/student_update");
                        echo "Invalid Degree Please must add appropriate degree";
                        $error .= "<div class='text-danger'>Invalid Degree Please must add appropriate degree</div>";
                        exit();
                    }
                    // prePrint($application);



                }
                else {
                    $error .= "<div class='text-danger'>fees not found</div>";
                    echo "fees not found";
                    exit();
                }

            }
            else {
                echo "this application id is not associate with you";
                $error .= "<div class='text-danger'>this application id is not associate with you</div>";
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
  
      	
	public function add_special_self_category(){

          $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $CANDIDATE_USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
            
            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($CANDIDATE_USER_ID);
            $CANDIDATE_USER_ID = $user['USER_ID'];

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($CANDIDATE_USER_ID, $APPLICATION_ID);

        
                                            
//			if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
//				redirect(base_url('form/application_form'));
//				exit();
//			}

//			if($application['STATUS_ID']<2){
//				redirect(base_url('form/dashboard'));
//			}
            if ($application) {
                $this->check_special_self_validation($application);
                $form_data = $this->User_model->getUserFullDetailById($CANDIDATE_USER_ID,$APPLICATION_ID);

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
                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);
                if ($form_fees) {

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
                                exit("Error: while adding your special category");
                            }
                        }
                        if ($evening_choice_bool){
                            redirect(base_url('AdminPanel/special_self_choices'));
                        }
                    }else{
                        //  redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);
                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    public  function special_self_choices(){
        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

           // $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($USER_ID);

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

                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                            $error = "<div class='text-danger'> Please Must Save category</div>";
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
                        $this->load->view('admin/special_choice_list_candidate', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
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

        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
            //$user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($USER_ID);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            // if($this->user['IS_SUPER_PASSWORD_LOGIN']=='N'&&IS_SPECIAL_SELF_OPEN == 0){
            //     redirect(base_url('form/application_form'));
            //     exit();
            // }
            if ($application) {
               // $this->check_special_self_validation($application);
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

                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                        }
                        if($llb_validation){
                            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
                            } else {
                                $error .= "<div class='text-danger'>Ticket Number / Seat Number Must be Enter</div>";
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
                                    $error .= "<div class='text-danger'>Invalid test Score</div>";
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
                                        $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
                                }
                            }
                            else {

                                if ($result_card_image == "")
                                    $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
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
                                    if(count($list_of_choice)>0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info,$admin_id)){
                                         foreach($list_of_choice as $choice){
                                             $where = array('CATEGORY_ID '=>24,'APPLICATION_ID'=>$choice['APPLICATION_ID'],'PROG_LIST_ID'=>$choice['PROG_LIST_ID'],'SHIFT_ID'=>$choice['SHIFT_ID']);
                                             $data = array('CHOICE_NO'=>$choice['CHOICE_NO']);
                                             $this->Application_model->updateApplicationSelectionList($data,$where);
                                         }

                                    }
                                    else{
                                        $error .= "<div class='text-danger'>Your choices not added or updated this may not happen. Kindly contact technical team..</div>";
                                    }
                                }
                                else{
                                    $error .= "<div class='text-danger'>Your choices are invalid this may not happen. If you have any technical issue please contact technical team..!</div>";
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
                    $error .= "<div class='text-danger'>fees not found</div>";
                    echo "fees not found";
                    exit();
                }
            }else {
                echo "this application id is not associate with you";
                $error .= "<div class='text-danger'>this application id is not associate with you</div>";
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
             	$alert = array('MSG'=>"<div class='text-danger'>Currently Closed Special Self Finance </div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url()."form/dashboard");
                                exit();
         }
         if(!(($application['STATUS_ID']==5||$application['STATUS_ID']==4)&&$application['CAMPUS_ID']==1 && $application['PROGRAM_TYPE_ID']==1)){
                   	$alert = array('MSG'=>"<div class='text-danger'>You are not Eligible For Special Self Finance <br> Only Verified and In Review form are eligile for Special Self Finance <br> </div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                                redirect(base_url()."form/dashboard");
                                exit();
             
         }
    }
  
    //UPDATED FUNCTION ON 18-OCT-2020 VIEW FILE select_category.php
    public function select_category(){
        
          $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];
          if(!($role_id==1||$role_id==4)){
            redirect(base_url()."AdminPanel/student_update");
        }
        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

          
            $user = $this->User_model->getUserById($USER_ID);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
             
          
           
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

                            //break;
                        }
                    }
                }


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                     
                       // $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);
                        //$program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);
                        //$data['VALID_PROGRAM_LIST'] =$valid_program_list;
                        $data['list_of_category'] =$list_of_categoy;
                         $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST;
                        //$data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['application'] =$application;
                        $data['form_data'] =$form_data;
                        //     prePrint($valid_program_list);
                        // prePrint($program_list);
//                            exit();
                        // $data['roll_no'] = $user['USER_ID'];
                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('admin/select_category', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                          redirect(base_url()."AdminPanel/student_update");
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);



                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    //UPDATED FUNCTION ON 22-OCT-2020 MODAL FILE Application_model.php
    public function select_category_handler(){
       // exit();
        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

          $user_id=$USER_ID;
            $user = $this->User_model->getUserById($USER_ID);

          
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
           
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
            $SPORTS_QUOTA_ID=6;
               $EVENING_PROGRAM_ID = 7;
               $HQ_QUOTA_ID=9;

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
                    $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
                }
                if (isset($_POST['DESIGNATION']) && isValidData($_POST['DESIGNATION'])) {
                    $DESIGNATION = strtoupper(isValidData($_POST['DESIGNATION']));
                } else {
                    $error .= "<div class='text-danger'>Designation of Employee Must be Enter</div>";
                }
                if (isset($_POST['DEPARTMENT_NAME']) && isValidData($_POST['DEPARTMENT_NAME'])) {
                    $DEPARTMENT_NAME = strtoupper(isValidData($_POST['DEPARTMENT_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Department Name Must be Enter</div>";
                }
                if (isset($_POST['IS_REGULAR']) && isValidData($_POST['IS_REGULAR'])) {
                    $IS_REGULAR = strtoupper(isValidData($_POST['IS_REGULAR']));
                } else {
                    $error .= "<div class='text-danger'>Job Nature Must Select</div>";
                }
                if (isset($_POST['RELATIONSHIP']) && isValidData($_POST['RELATIONSHIP'])) {
                    $RELATIONSHIP = strtoupper(isValidData($_POST['RELATIONSHIP']));
                } else {
                    $error .= "<div class='text-danger'>Relationship Must Select</div>";
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
                            $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
                    }
                }
                else {

                    if ($service_certificate_of_employee_image == "")
                        $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
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
                    $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
                }
                if (isset($_POST['AFFILIATED_DESIGNATION']) && isValidData($_POST['AFFILIATED_DESIGNATION'])) {
                    $DESIGNATION = strtoupper(isValidData($_POST['AFFILIATED_DESIGNATION']));
                } else {
                    $error .= "<div class='text-danger'>Designation of Employee Must be Enter</div>";
                }
                if (isset($_POST['AFFILIATED_DEPARTMENT_NAME']) && isValidData($_POST['AFFILIATED_DEPARTMENT_NAME'])) {
                    $DEPARTMENT_NAME = strtoupper(isValidData($_POST['AFFILIATED_DEPARTMENT_NAME']));
                } else {
                    $error .= "<div class='text-danger'>Department Name Must be Enter</div>";
                }
                if (isset($_POST['AFFILIATED_IS_REGULAR']) && isValidData($_POST['AFFILIATED_IS_REGULAR'])) {
                    $IS_REGULAR = strtoupper(isValidData($_POST['AFFILIATED_IS_REGULAR']));
                } else {
                    $error .= "<div class='text-danger'>Job Nature Must Select</div>";
                }
                if (isset($_POST['AFFILIATED_RELATIONSHIP']) && isValidData($_POST['AFFILIATED_RELATIONSHIP'])) {
                    $RELATIONSHIP = strtoupper(isValidData($_POST['AFFILIATED_RELATIONSHIP']));
                } else {
                    $error .= "<div class='text-danger'>Relationship Must Select</div>";
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
                            $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($affiliated_service_certificate_of_employee_image == "")
                        $error .= "<div class='text-danger'>Must Upload Service Certificate Of Employee Image and image size must be less then 500kb </div>";
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
                    $error .= "<div class='text-danger'>Type Of Disability Must Select</div>";
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
                            $error .= "<div class='text-danger'>Must Upload Medical Certificate Image and image size must be less then 500kb </div>";
                    }
                } else {

                    if ($medical_certificate_image == "")
                        $error .= "<div class='text-danger'>Must Upload Medical Certificate Image and image size must be less then 500kb </div>";
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

                if($this->Application_model->deleteAndInsertApplicantCategory($data,$admin_id))
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

    public function add_evening_category(){

        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

           // $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($USER_ID);
            $CANDIDATE_USER_ID = $user['USER_ID'];

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
          //  $this->close_registration_for_bachelor($application);
//			if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
//				redirect(base_url('form/application_form'));
//				exit();
//			}

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
                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);
                if ($form_fees) {

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
                            redirect(base_url('AdminPanel/select_subject'));
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
                            redirect(base_url('AdminPanel/evening_choices'));
                        }
                    }else{
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                    // prePrint($application);
                } else {
                    echo "fees not found";
                }

            } else {
                echo "this application id is not associate with you";
            }
        }else{
            echo "Application Id Not Found";
        }
    }

    public function evening_choices(){

        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

            $user = $this->User_model->getUserById($USER_ID);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            /*
                if($application['STATUS_ID']==FINAL_SUBMIT_STATUS_ID){
                    redirect(base_url('form/application_form'));
                    exit();
                }
            */
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
                    $show_program = array("1"=>array(50,156,270,271,281),
                        //"3"=>array(270),
                        "7"=>array(270));
                    //    "5"=>array(50));
                    $show_program = $show_program[$application['CAMPUS_ID']];
                }
                if($application['PROGRAM_TYPE_ID']==1){
                    $hide_program = array(264,110,8,260,263,265,81,14,101,99,19,106,22,9,234); //vikesh block evening programs bachelor
                }
                //form close from bachelor
               // $this->close_registration_for_bachelor($application);


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

                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                        $is_valid = false;
                        if(count($list_of_categoy)==0){
                            echo "Please Must Save Category";
                            $error = "<div class='text-danger'> Please Must Save category</div>";
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
                            echo "Please Must Save Category";
                            $error = "<div class='text-danger'> Please Must Save category</div>";
                            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                            $this->session->set_flashdata('ALERT_MSG',$alert);
                            redirect(base_url('form/add_evening_category'));
                            exit();
                        }
                        $valid_program_list = $this->Prerequisite_model->getPrerequisiteByMinorMappingIdList($minorMappingIds);

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
                        $program_list       = $this->Administration->getProgramByTypeID($application['PROGRAM_TYPE_ID']);

                        $data['VALID_PROGRAM_LIST'] =$valid_exact_program;
                        $data['PROGRAM_LIST'] =$program_list;
                        $data['PROGRAM_TYPE_ID'] =$application['PROGRAM_TYPE_ID'];
                        $data['CHOOSEN_PROGRAM_LIST'] =$CHOOSEN_PROGRAM_LIST;
                        $data['lat_info'] =$lat_info;

                        $precentage = ($valid_qualification['OBTAINED_MARKS']*100/$valid_qualification['TOTAL_MARKS']);
                        $data['precentage'] =round($precentage,2);


                        $this->load->view('include/header', $data);
                        $this->load->view('include/preloder');
                        $this->load->view('include/side_bar', $data);
                        $this->load->view('include/nav', $data);
                        $this->load->view('admin/evening_choice_list_candidate', $data);
                        $this->load->view('include/footer_area', $data);
                        $this->load->view('include/footer', $data);

                    }else{
                        redirect(base_url()."candidate/add_inter_qualification");
                        echo "Invalid Degree Please must add appropriate degree";
                    }
                } else {
                    echo "fees not found";
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


        $admin = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $admin_id = $admin['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($admin_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
            $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

            $user = $this->User_model->getUserById($USER_ID);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;

            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if(OPEN_EVENING_PORTAL == 0&&$role_id!=1){
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

                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                            $error .= "<div class='text-danger'>Complete Employee Name Must be Enter</div>";
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
                        }
                        if($llb_validation){
                            if (isset($_POST['TOKEN_NO']) && isValidData($_POST['TOKEN_NO'])) {
                                $TOKEN_NO = strtoupper(isValidData($_POST['TOKEN_NO']));
                            } else {
                                $error .= "<div class='text-danger'>Ticket Number / Seat Number Must be Enter</div>";
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
                                    $error .= "<div class='text-danger'>Invalid test Score</div>";
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
                                        $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
                                }
                            }
                            else {

                                if ($result_card_image == "")
                                    $error .= "<div class='text-danger'>Must Upload Result Card Image and image size must be less than 500kb </div>";
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
                                        $list_of_choice[]=array('USER_ID'=>$user['USER_ID'],'APPLICATION_ID'=>$APPLICATION_ID,'PROG_LIST_ID'=>$PROG_LIST_ID,'CHOICE_NO'=>$CHOICE_NO,'SHIFT_ID'=>EVENING_SHIFT_ID);
                                    }

                                    //prePrint($list_of_choice);
                                    if(count($list_of_choice)>0&&$this->Application_model->deleteAndInsertApplicantChoice($list_of_choice,$lat_info)){
                                         foreach($list_of_choice as $choice){
                                             $where = array('APPLICATION_ID'=>$choice['APPLICATION_ID'],'PROG_LIST_ID'=>$choice['PROG_LIST_ID'],'SHIFT_ID'=>$choice['SHIFT_ID']);
                                             $data = array('CHOICE_NO'=>$choice['CHOICE_NO']);
                                             $this->Application_model->updateApplicationSelectionList($data,$where);
                                         }
                                    }
                                    else{
                                        $error .= "<div class='text-danger'>Your choices not added or updated this may not happen. Kindly contact technical team..</div>";
                                    }
                                }
                                else{
                                    $error .= "<div class='text-danger'>Your choices are invalid this may not happen. If you have any technical issue please contact technical team..!</div>";
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
                    $error .= "<div class='text-danger'>fees not found</div>";
                    echo "fees not found";
                    exit();
                }
            }else {
                echo "this application id is not associate with you";
                $error .= "<div class='text-danger'>this application id is not associate with you</div>";
                exit();
            }
        }else{
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

    public function application_form(){
        
            if($this->session->has_userdata('STUDENT_USER_ID')&&$this->session->has_userdata('STUDENT_APPLICATION_ID')) {
             $USER_ID = $this->session->userdata('STUDENT_USER_ID');
            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');

          
            $user = $this->User_model->getUserById($USER_ID);

            $APPLICATION_ID = $this->session->userdata('STUDENT_APPLICATION_ID');
            
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);
            if($application['STATUS_ID']<FINAL_SUBMIT_STATUS_ID){
                redirect(base_url('form/dashboard'));
                exit();
            }
            if(!file_exists(PROFILE_IMAGE_CHECK_PATH.$user['PROFILE_IMAGE'])){

//               do {
//                   $resutl = $this->CI_ftp_Download(PROFILE_IMAGE_CHECK_PATH, $user['PROFILE_IMAGE']);
//
//                  /// prePrint("RES".$resutl);
//               }while(!$resutl);
				exit("file not found");
                //exit();
            }
            //prePrint($user);
            $user_fulldata = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'], $APPLICATION_ID);
            $data['profile_url'] = $this->profile;
            $data['user_fulldata'] = $user_fulldata;
            $data['application'] = $application;
            $data['bank_info'] = $this->Admission_session_model->getBankInformationByBranchId($application['BRANCH_ID']);
            $this->load->view('application_form',$data);

        }else{
            redirect(base_url() . "login");
        }
    }
    
    public function dump_old_data(){
        prePrint("Start Time".date("d-m-y h:i:s A"));
        set_time_limit(3000);
        ini_set('memory_limit', '-1');
        $this->Administration->get_all_old_data();
        prePrint("END Time".date("d-m-y h:i:s A"));
    }
    
    public function dump_old_challan_data(){
            prePrint("Start Time".date("d-m-y h:i:s A"));
        set_time_limit(3000);
        ini_set('memory_limit', '-1');
      
        $this->Administration->get_all_old_challan_data();
         prePrint("END Time".date("d-m-y h:i:s A"));
    }
    
    public function dump_old_challan_missing_data(){
            prePrint("Start Time".date("d-m-y h:i:s A"));
        set_time_limit(3000);
        ini_set('memory_limit', '-1');
      
        $this->Administration->get_all_old_challan_mission_data();
         prePrint("END Time".date("d-m-y h:i:s A"));
    }
  
    public function add_data_in_test_result(){
        
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        $error_msg = "";
        $query_string = "";
           $data['test_year'] =$this->TestResult_model->getTestTypeYear();
        $data['campus'] =$this->Administration->getCampus();
        $data['side_bar_values'] = $side_bar_data;


        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/add_data_in_test_result');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
     public function add_data_in_test_result_handler(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
          $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        //prePrint($_POST);
        $error  = "";
                if (isset($_POST['PROG_TYPE_ID']) && isValidData($_POST['PROG_TYPE_ID'])) {
                    $PROG_TYPE_ID = isValidData($_POST['PROG_TYPE_ID']);
                } else {
                    $error .= "<div class='text-danger'>Must Select Program</div>";
                }
                 if (isset($_POST['YEAR']) && isValidData($_POST['YEAR'])) {
                    $YEAR = isValidData($_POST['YEAR']);
                } else {
                    $error .= "<div class='text-danger'>Must Select Year</div>";
                }
                 if (isset($_POST['TEST_ID']) && isValidData($_POST['TEST_ID'])) {
                    $TEST_ID = isValidData($_POST['TEST_ID']);
                } else {
                    $error .= "<div class='text-danger'>Must Select Test Id</div>";
                }
                 if (isset($_POST['CAMPUS_ID']) &&count($_POST['CAMPUS_ID'])) {
                    $CAMPUS_IDS = $_POST['CAMPUS_ID'];
                } else {
                    $error .= "<div class='text-danger'>Must Select Campus</div>";
                }
                 if (isset($_POST['IS_LLB']) && isValidData($_POST['IS_LLB'])) {
                    $IS_LLB = isValidData($_POST['IS_LLB']);
                } else {
                    $error .= "<div class='text-danger'>Must Select Is LAW </div>";
                }
                //prePrint($CAMPUS_ID);
                //exit();
                $ADMISSION_SESSION_IDS = "";
            if($error==""){
                $session = $this->Admission_session_model->getSessionByYearData($_POST['YEAR']);
                 $session_id =$session['SESSION_ID'];
                 
                 foreach($CAMPUS_IDS as $CAMPUS_ID){
                      $admission_session_obj = $this->Admission_session_model->getAdmissionSessionID($session_id,$CAMPUS_ID,$PROG_TYPE_ID);
                      if($admission_session_obj){
                          $ADMISSION_SESSION_IDS .= $admission_session_obj['ADMISSION_SESSION_ID'].",";
                      }
                
                 }
                 $ADMISSION_SESSION_IDS = rtrim($ADMISSION_SESSION_IDS,",");
            }
            
            if($ADMISSION_SESSION_IDS==""){
                $error .= "<div class='text-danger'>Must selet at least one campus </div>";
            }
            if($error==""){
                 
                 
                 
                 
                
                
                $res = $this->AdmitCard_model->add_data_in_test_result($TEST_ID,$ADMISSION_SESSION_IDS,$IS_LLB);
                if($res){
                    	$alert = array('MSG'=>"<div class='text-success'>Sucessfully add</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/add_data_in_test_result'));
                exit(); 
                }else{
                   	$alert = array('MSG'=>"<div class='text-danger'>something went wrong</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/add_data_in_test_result'));
                exit(); 
                }
                
                
                
            }else{
                 	$alert = array('MSG'=>"<div class='text-danger'>$error</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/add_data_in_test_result'));
                exit();
                
            }
        
   
    }
    public function add_data_in_candidate_selection_list(){
        
            
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        
        //$this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        
       
        $data['campus'] = $this->Administration->getCampus();
        $data['program_types'] = $this->Administration->programTypes();
        $data['session']= $this->Admission_session_model->getSessionData();
         
           // prePrint($session_obj);
            //exit();
        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/add_data_in_candidate_selection_list',$data);
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
    
    public function add_data_in_candidate_selection_list_handler(){
         set_time_limit(60*10);
          ini_set('memory_limit', '-1');
          $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

      
        //prePrint($_POST);
        $error  = "";
                if (isset($_POST['ADMISSION_LIST_ID']) && isValidData($_POST['ADMISSION_LIST_ID'])) {
                    $ADMISSION_LIST_ID = isValidData($_POST['ADMISSION_LIST_ID']);
                } else {
                    $error .= "<div class='text-danger'>Must Select Admission List</div>";
                }
                 if (isset($_POST['IS_PROVISIONAL']) && isValidData($_POST['IS_PROVISIONAL'])) {
                    $IS_PROVISIONAL = isValidData($_POST['IS_PROVISIONAL']);
                } else {
                    $error .= "<div class='text-danger'>Must Select =Is Provisional</div>";
                }
            
            if($error==""){
                $result = $this->Selection_list_report_model->getSelectionListByListId($ADMISSION_LIST_ID,$IS_PROVISIONAL);
                
                $res= $this->Selection_list_report_model->add_data_in_candidate_selection_batch($result);
                
                
                if($res){
                    	$alert = array('MSG'=>"<div class='text-success'>Sucessfully add</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/add_data_in_candidate_selection_list'));
                exit(); 
                }else{
                   	$alert = array('MSG'=>"<div class='text-danger'>something went wrong</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/add_data_in_candidate_selection_list'));
                exit(); 
                }
                
                
                
            }else{
                 	$alert = array('MSG'=>"<div class='text-danger'>$error</div>",'TYPE'=>'ALERT');
                                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/add_data_in_candidate_selection_list'));
                exit();
                
            }
        
   
    }
    
    public function view_all_session(){
        
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $data['session_data'] = $this->Admission_session_model->getSessionData();
        //$this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        
        if(isset($_GET['id'])&&isValidData($_GET['id'])){
            $id = isValidData($_GET['id']);
            $session_obj = $this->Admission_session_model->getSessionByID($id);
            $data['session_obj']=$session_obj;
        }

        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/view_all_session',$data);
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }
    
    public function view_all_session_handler(){
        if(isset($_POST['add'])||isset($_POST['update'])){
            $error = "";
            $session_id = 0;
            if (isset($_POST['session_id']) && isValidData($_POST['session_id'])) {
                    $session_id = isValidData($_POST['session_id']);
                } else {
                    if(isset($_POST['update'])){
                    $error .= "<div class='text-danger'>Session ID not found </div>";
                    }
                }
             if (isset($_POST['sessoin_code']) && isValidData($_POST['sessoin_code'])) {
                    $sessoin_code = isValidData($_POST['sessoin_code']);
                } else {
                    $error .= "<div class='text-danger'>Session Code Must be Enter </div>";
                }
             if (isset($_POST['year']) && isValidData($_POST['year'])) {
                    $year = isValidData($_POST['year']);
                } else {
                    $error .= "<div class='text-danger'>Year Must be Enter </div>";
                }
             if (isset($_POST['batch_remarks']) && isValidData($_POST['batch_remarks'])) {
                    $batch_remarks = isValidData($_POST['batch_remarks']);
                } else {
                    $error .= "<div class='text-danger'>Batch Remarks Must be Enter </div>";
                }
             if (isset($_POST['date']) && isValidData($_POST['date'])) {
                    $date = isValidData($_POST['date']);
                } else {
                    $error .= "<div class='text-danger'>Date Must be Enter </div>";
                }
            if (isset($_POST['remarks']) && isValidData($_POST['remarks'])) {
                    $remarks = isValidData($_POST['remarks']);
                } else {
                    $error .= "<div class='text-danger'>Remarks Must be Enter </div>";
                }
                 if (isset($_POST['BACHELOR_FORM_COMPLETE']) && is_numeric($_POST['BACHELOR_FORM_COMPLETE'])) {
                    $BACHELOR_FORM_COMPLETE = isValidData($_POST['BACHELOR_FORM_COMPLETE']);
                } else {
                    $error .= "<div class='text-danger'>Remarks Must be Enter </div>";
                }
                
            if($error==""){
                $form_array = array("BACHELOR_FORM_COMPLETE"=>$BACHELOR_FORM_COMPLETE,"YEAR"=>$year,"REMARKS"=>$remarks,"DATE"=>$date,"BATCH_REMARKS"=>$batch_remarks,"SESSION_CODE"=>$sessoin_code);
                if($session_id){
                    $result = $this->Admission_session_model->updateSession($session_id,$form_array);
                }else{
                    $result = $this->Admission_session_model->addSession($form_array);
                }
                
                if($result){
                    if($session_id){
                        $success= "<div class='text-success'>Successfully Update Session</div>";
                    }
                    else{
                        $success= "<div class='text-success'>Successfully Add Session</div>";
                    }
                    $alert = array('MSG'=>$success,'TYPE'=>'Success');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url('AdminPanel/view_all_session')); 
                }else{
                    $error= "<div class='text-danger'>Something went wrong contact Technical Team</div>";
                     $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/view_all_session')); 
                }
            }else{
                //  $error= "<div class='text-danger'>Invalid Parameter</div>";
                $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/view_all_session')); 
            }
        }else{
             $error= "<div class='text-danger'>Invalid Parameter</div>";
                $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/view_all_session')); 
        }
    }
   
    public function view_all_admission_session(){
        
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $data['admission_session_data'] = $this->Admission_session_model->getAdmissionSession();
        //$this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;
        
        if(isset($_GET['id'])&&isValidData($_GET['id'])){
            $id = isValidData($_GET['id']);
            $session_obj = $this->Admission_session_model->getAdmissionSessionById($id);
            $data['admission_session_obj']=$session_obj;
        }
        $data['campus'] = $this->Administration->getCampus();
        $data['program_types'] = $this->Administration->programTypes();
        $data['session']= $this->Admission_session_model->getSessionData();
         
           // prePrint($session_obj);
            //exit();
        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/view_all_admission_session',$data);
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }
    
      public function view_all_admission_session_handler(){
        //   prePrint($_POST);
        //   exit();
   
 

        if(isset($_POST['add'])||isset($_POST['update'])){
            $error = "";
            $admisssion_session_id = 0;
            if (isset($_POST['admisssion_session_id']) && isValidData($_POST['admisssion_session_id'])) {
                    $admisssion_session_id = isValidData($_POST['admisssion_session_id']);
                } else {
                    if(isset($_POST['update'])){
                    $error .= "<div class='text-danger'>Session ID not found </div>";
                    }
                }
             if (isset($_POST['session']) && isValidData($_POST['session'])) {
                    $session = isValidData($_POST['session']);
                } else {
                    $error .= "<div class='text-danger'>Session  Must be Select </div>";
                }
             if (isset($_POST['Program_Type']) && isValidData($_POST['Program_Type'])) {
                    $Program_Type = isValidData($_POST['Program_Type']);
                } else {
                    $error .= "<div class='text-danger'>Degree Program Must be Select </div>";
                }
             if (isset($_POST['campus']) && isValidData($_POST['campus'])) {
                    $campus = isValidData($_POST['campus']);
                } else {
                    $error .= "<div class='text-danger'>Campus Must be Select </div>";
                }
             if (isset($_POST['admission_start_date']) && isValidData($_POST['admission_start_date'])) {
                    $admission_start_date = isValidData($_POST['admission_start_date']);
                } else {
                    $error .= "<div class='text-danger'>Admission Start Date Must be Enter </div>";
                }
            if (isset($_POST['admission_end_date']) && isValidData($_POST['admission_end_date'])) {
                    $admission_end_date = isValidData($_POST['admission_end_date']);
                } else {
                    $error .= "<div class='text-danger'>Admission End Date Must be Enter </div>";
                }
             if (isset($_POST['passing_score']) && isValidData($_POST['passing_score'])) {
                    $passing_score = isValidData($_POST['passing_score']);
                } else {
                    $error .= "<div class='text-danger'>Passing Score Must be Enter </div>";
                }
             if (isset($_POST['passing_out']) && isValidData($_POST['passing_out'])) {
                    $passing_out = isValidData($_POST['passing_out']);
                } else {
                    $error .= "<div class='text-danger'>Total Score Must be Enter </div>";
                }
            if (isset($_POST['remarks']) && isValidData($_POST['remarks'])) {
                    $remarks = isValidData($_POST['remarks']);
                } else {
                    $error .= "<div class='text-danger'>Remarks Must be Enter </div>";
                }
             if (isset($_POST['is_display']) &&is_numeric($_POST['is_display'])) {
                    $is_display = isValidData($_POST['is_display']);
                } else {
                    $error .= "<div class='text-danger'>Is Display Must Be Select </div>";
                }
                 if (isset($_POST['form_fee']) && isValidData($_POST['form_fee'])) {
                    $form_fee = isValidData($_POST['form_fee']);
                } else {
                    $error .= "<div class='text-danger'>Form Fee Must Be Enter</div>";
                }
                
                    
            if($error==""){
                $form_array = array("DISPLAY"=>$is_display,"REMARKS"=>$remarks,"PASSING_OUT"=>$passing_out,"PASSING_SCORE"=>$passing_score,
                "ADMISSION_START_DATE"=>$admission_start_date,"ADMISSION_END_DATE"=>$admission_end_date,"CAMPUS_ID"=>$campus,"PROGRAM_TYPE_ID"=>$Program_Type,"SESSION_ID"=>$session);
                $form_fee_array = array("AMOUNT"=>$form_fee,"REMARKS"=>$remarks,"CAMPUS_ID"=>$campus,"SESSION_ID"=>$session,"BANK_ACCOUNT_ID"=>1,"DESCRIPTION"=>"FORM_FEE");
                if($admisssion_session_id){
                    
                    $result = $this->Admission_session_model->updateAdmissionSession($admisssion_session_id,$form_array,$form_fee_array);
                }else{
                    $result = $this->Admission_session_model->addAdmissionSession($form_array,$form_fee_array);
                }
                
                if($result){
                    if($admisssion_session_id){
                        $success= "<div class='text-success'>Successfully Update Adminssion Session</div>";
                    }
                    else{
                        $success= "<div class='text-success'>Successfully Add Admission Session</div>";
                    }
                    $alert = array('MSG'=>$success,'TYPE'=>'Success');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url('AdminPanel/view_all_admission_session')); 
                }else{
                    $error= "<div class='text-danger'>Something went wrong contact Technical Team</div>";
                     $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/view_all_admission_session')); 
                }
            }else{
                //  $error= "<div class='text-danger'>Invalid Parameter</div>";
                $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/view_all_admission_session')); 
            }
        }else{
             $error= "<div class='text-danger'>Invalid Parameter</div>";
                $alert = array('MSG'=>$error,'TYPE'=>'ERROR');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url('AdminPanel/view_all_admission_session')); 
        }
    }
    
    public function dump_old_qualification_data(){
        prePrint("Start Time".date("d-m-y h:i:s A"));
        set_time_limit(3000);
        ini_set('memory_limit', '-1');
      
        $data = $this->Web_model->getQualification();
       // prePrint($data);
         prePrint("END Time".date("d-m-y h:i:s A"));
    }
    public function download_candidate_slip(){
         $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $data['admission_session_data'] = $this->Admission_session_model->getAdmissionSession();
        // prePrint($side_bar_data);
        // exit();
        // $this->verify_path($this->script_name,$side_bar_data);
       
       
       
       $offset= $limit = $END_SEAT_NO =$START_SEAT_NO= $date_time =null;
        if(isset($_GET['SESSION_ID'])){
            
            $session_id  = isValidData($_GET['SESSION_ID']);
        }
         if(isset($_GET['PROG_TYPE_ID'])){
            
            $program_type_id  = isValidData($_GET['PROG_TYPE_ID']);
        }
        if(isset($_GET['START_SEAT_NO'])){
            
            $START_SEAT_NO  = isValidData($_GET['START_SEAT_NO']);
        }
        if(isset($_GET['END_SEAT_NO'])){
            $END_SEAT_NO  = isValidData($_GET['END_SEAT_NO']);
        }
        $list_of_candidate = $this->AdmitCard_model->getAdmitCardForApp($session_id,$program_type_id,$date_time,$limit,$offset,$START_SEAT_NO,$END_SEAT_NO);
        $data['list_of_candidate']  = $list_of_candidate;
         
        //prePrint($list_of_candidate);
        //exit();
         $this->load->view('web/admin_candidate_slip',$data);
        
    }
    
    public function search_student(){

        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;


        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/search_student');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }
    
    public function get_basic_information_ITSC(){

        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        // $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;


        $name = isset($_POST['NAME']) ? isValidData($_POST['NAME']) : '';
        $fName = isset($_POST['FNAME']) ? isValidData($_POST['FNAME']) : '';
        $surname = isset($_POST['SURNAME']) ? isValidData($_POST['SURNAME']) : '';
        $email = isset($_POST['EMAIL']) ? isValidData($_POST['EMAIL']) : '';
        $mobNo = isset($_POST['MOB_NO']) ? isValidData($_POST['MOB_NO']) : '';

        
        $user = $this->User_model->getItscUser($name,$fName,$surname,$email,$mobNo);

            if($user){
                $data['user'] = $user;

                // $this->session->set_userdata('STUDENT_USER_ID', $user['USER_ID']);
                // $data['ROLE_ID'] = $role_id;
                $this->load->view('admin/itsc_candidate',$data);
            }else{
                $reponse = "<div class='text-danger'>Sorry Record not found</div>";
                http_response_code(405);
                exit($reponse);
            }
    }    
    
    function change_role(){
      
         $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        $this->verify_path($this->script_name,$side_bar_data);
        $data['side_bar_values'] = $side_bar_data;


        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];

        if(isset($_POST['submit'])&&isset($_POST['ROLE'])&&is_numeric($_POST['ROLE'])){
            $bool=false;
            //

            $user_role_data = $this->User_model->getUserRoleByUserId($user_id,$_POST['ROLE']);
            //  $is_valid = getDataStaticQuery("*","role_relation","USER_ID={$user['USER_ID']} AND ROLE_ID={$_POST['ROLE']} AND ACTIVE = 1");
          
            if(count($user_role_data)>0){
                $this->user = $this->session->userdata($this->SessionName);
              //  prePrint($this->user);
                //prePrint($user_role_data[0]); 
                $user_data = array_merge($this->user,$user_role_data[0]);
                $session_data=$this->getSessionData($user_data);
                //prePrint($session_data);
                
                $this->session->set_userdata($this->SessionName, $session_data);
               // prePrint($user_role_data);
                //$this->user_role='ADMIN_ROLE';
                $this->set_admission_role($user_role_data);
                //exit();
                //$this->user_role = $this->session->userdata($this->user_role);
                if($user_role_data[0]['ROLE_ID'] == 6 || $user_role_data[0]['ROLE_ID'] == 4)
                            {
                                redirect(base_url()."FormVerification");
                            }elseif($user_role_data[0]['ROLE_ID'] == 7){
                                 redirect(base_url().$this->generalBranch_mainpage); 
                            }else{
                                redirect(base_url().$this->HomeController);    
                            }
                // prePrint($user_role_data);
                //prePrint(  $this->user_role);
                redirect(base_url().'AdminPanel/search_student_by_cnic');
                exit();
            }else{
                $error.="Warning you are tampering role it is illegale..!";
            }

        }
       
       
        $data['role'] = $user_role['ROLE_ID'];

        $data['role_list']=$this->User_model->getUserRoleByUserId($user_id);
        if(count( $data['role_list'])<=1){
            redirect(base_url().'AdminPanel/search_student_by_cnic');
            exit();
        }
        
         $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/change_role',$data);
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }

      private function getSessionData($user) {
        $session_data =array('USER_ID'=>$user['USER_ID'],'ROLE_NAME'=>$user['ROLE_NAME'],'KEYWORD'=>$user['KEYWORD'],'ACTIVE'=>$user['ACTIVE'],'FIRST_NAME'=>$user['FIRST_NAME'],'LAST_NAME'=>$user['LAST_NAME'],'EMAIL'=>$user['EMAIL'],'CNIC_NO'=>$user['CNIC_NO'],'PROFILE_IMAGE'=>$user['PROFILE_IMAGE'],'PASSPORT_NO'=>$user['PASSPORT_NO'],'PROFILE'=>$user['PROFILE_IMAGE']);
        return $session_data;
    }
    
	function set_admission_role ($user_admission_role) {
		$this->session->set_userdata($this->user_role, $user_admission_role[0]);
	}
   

    
   
}
