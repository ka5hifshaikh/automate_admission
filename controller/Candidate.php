<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/11/2020
 * Time: 4:07 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Candidate extends CI_Controller
{
    private $SelfController = 'candidate';
    private $profile = 'candidate/profile';
    private $LoginController = 'login';
    private $SessionName = 'USER_LOGIN_FOR_ADMISSION';
    private $user ;
    private $APPLICATION_ID =0;
    private $file_size = 500;
    //private $notice = "<div class='text-danger'><h1>Dear Candidate, <br>You will have to add your qualifications after Pre-Entry Test. You will be notified through Email. Keep visiting your email account and E-portal account dashboard for further process regarding Admissions 2025.</h1></div>";
     private $notice = "<div class='text-danger'><h1>Admission form has been closed.</h1></div>";

    public function __construct()
    {
        parent::__construct();

        if($this->session->has_userdata($this->SessionName)&&$this->session->has_userdata('APPLICATION_ID')){
                // redirect(base_url().$this->LoginController);
                    // $this->load->view("underMaintenance.html");
                    // echo "<h4 style='color:red'>Under Maintenance Please visit after few hours</h4>";
                    // exit();
                $this->APPLICATION_ID = $this->session->userdata('APPLICATION_ID');
                $this->user = $this->session->userdata($this->SessionName);
    
        }else{
            redirect(base_url().$this->LoginController);
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



    }

    function index(){

        $user = $this->session->userdata($this->SessionName);
        if($user){
            $data['user'] = $user;
            $data['profile_url'] = $this->profile;

            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('home',$data);
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }
    }

    function profile(){

        $user = $this->session->userdata($this->SessionName);
        
        $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $this->APPLICATION_ID);
        $countries =$this->Api_location_model->getAllCountry();

        $prefixs = $this->Configuration_model->getPrefix();
        $prefixs = json_decode($prefixs['VALUE'],true);
        $blood_groups=array("A+","A-","B+","B-","O+","O-","AB+","AB-");
        $GENDER=array('M'=>"MALE","F"=>"FEMALE");
        $area=array('R'=>"RURAL","U"=>"URBAN");
        $RELIGIONS=array("ISLAM","HINDUISM","CHRISTIAN","OTHER");
        $REL_GUARD=array('FATHER'=>"FATHER","MOTHER"=>"MOTHER","BROTHER"=>"BROTHER","SISTER"=>"SISTER","HUSBAND"=>"HUSBAND","UNCLE"=>"UNCLE","AUNTY"=>"AUNTY","GRAND FATHER"=>"GRAND FATHER","GRAND MOTHER"=>"GRAND MOTHER","OTHER"=>"OTHER");
        $OCC_GUARD=array('BUSINESS MAN'=>"BUSINESS MAN","ENGINEER"=>"ENGINEER","DOCTOR"=>"DOCTOR","FARMER"=>"FARMER","GOVERMENT EMPLOYEE"=>"GOVERMENT EMPLOYEE","PRIVATE COMPANY EMPLOYEE"=>"PRIVATE COMPANY EMPLOYEE","LANDLOARD"=>"LANDLOARD","RETIRED"=>"RETIRED","OTHER"=>"OTHER");
        if($application){
            if($application['IS_SUBMITTED']=='Y'){
                $next_page ="dashboard"; 
                  $next_page = base64_encode($next_page);
                 $next_page =urlencode($next_page);
       
            redirect(base_url()."form/review/$next_page");
            exit();    
            }else{
                // if($application['ADMISSION_END_DATE']<date('Y-m-d')){
                //     $error = "<div class='text-danger'>Form over due date</div>";
                //       $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                //                 $this->session->set_flashdata('ALERT_MSG',$alert);
                //                 redirect(base_url()."form/dashboard");
                //                 exit();
                // }
               
            }
        }else{
            redirect(base_url().$this->LoginController);
            exit();
        }
        $user = $this->User_model->getUserFullDetailById($user['USER_ID'],$this->APPLICATION_ID);
        if($user){
// prePrint($user['USER_ID']);
// exit();
        //    $user = $this->User_model->getUserById($user['USER_ID']);
           // prePrint($user);
            $category = $this->Application_model->getApplicantCategory($application['APPLICATION_ID'], $user['users_reg']['USER_ID']);
           
            $program_choice = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user['users_reg']['USER_ID'],$application['APPLICATION_ID'],$MORNING_SHIFT=1);
//  prePrint($category);
// prePrint($program_choice);
// prePrint($user);
// prePrint($application);

//             exit();
            $family_info  =  $this->User_model->getGuardianByUserId($user['users_reg']['USER_ID']);
            $data['user'] = $user['users_reg'];
            $data['qualifications'] = $user['qualifications'];
             $data['category'] = $category;
             $data['program_choice'] = $program_choice;
                    
            $data['profile_url'] = $this->profile;
            $data['countries'] = $countries;
            $data['prefixs'] = $prefixs;
            $data['blood_groups'] = $blood_groups;
            $data['family_info'] = $family_info;
            $data['REL_GUARD'] = $REL_GUARD;
            $data['OCC_GUARD'] = $OCC_GUARD;
            $data['GENDER'] = $GENDER;
            $data['area'] = $area;
            $data['RELIGIONS'] = $RELIGIONS;
            $data['application'] = $application;
           // prePrint($application);
            //exit();


            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('profile',$data);
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }else{
            redirect(base_url().$this->LoginController);
            exit();
        }

    }


 
    function updateProfile(){
        $reponse = getcsrf($this);
        $user = $this->User_model->getUserById($this->user['USER_ID']);
        $USER_ID = $user['USER_ID'] ;
        $U_R= 0;
        $PROFILE_IMAGE = "";
        if($user['PROFILE_IMAGE']){
            $PROFILE_IMAGE = PROFILE_IMAGE_CHECK_PATH.$user['PROFILE_IMAGE'] ;
        }

        if(!(!empty($PROFILE_IMAGE)&&file_exists($PROFILE_IMAGE)&&(filesize($PROFILE_IMAGE)>0))){

            // $PROFILE_IMAGE="";
            // $user['PROFILE_IMAGE']="";
        }
        $IS_CNIC_PASS = $user['IS_CNIC_PASS'] ;
        $PASSPORT_EXPIRY=$user['PASSPORT_EXPIRY'];
        $CNIC_EXPIRY=$user['CNIC_EXPIRY'];
        $error ="";
        $GNAME =$GAURD_MOBILE_NO =$GAURD_HOME_ADDRESS = $GAURD_MOBILE_CODE =$REL_GUARD=$OCC_GUARD="";
        $RELIGION=$GENDER=$BLOOD_GROUP = $ZIP_CODE=$PERMANENT_ADDRESS=$HOME_ADDRESS=$PLACE_OF_BIRTH =$MOBILE_CODE = $FNAME =$PLACE_OF_BIRTH= $LAST_NAME= $MOBILE_NO = $PREFIX_ID = $FIRST_NAME = "";
        $PHONE = $CITY_ID =$DISTRICT_ID=$PROVINCE_ID=$COUNTRY_ID = 0;
        $PASSPORT_EXPIRY = $CNIC_EXPIRY =$DATE_OF_BIRTH='1900-01-01';
        if(isset($_POST['FIRST_NAME'])&&isValidData($_POST['FIRST_NAME'])){
            $FIRST_NAME = strtoupper(isValidData($_POST['FIRST_NAME']));
        }else{
            $error.="<div class='text-danger'>Name Must be Enter</div>";
        }
        if(isset($_POST['PREFIX_ID'])&&isValidData($_POST['PREFIX_ID'])){
            $PREFIX_ID = isValidData($_POST['PREFIX_ID']);
        }else{
            //$error.="<div class='text-danger'>PREFIX Must be Select</div>";
        }

//        if(isset($_POST['EMAIL'])&&isValidData($_POST['EMAIL'])){
//            $EMAIL = strtolower(isValidData($_POST['EMAIL']));
//            $this->form_validation->set_rules('EMAIL','Email','required|valid_email');
//            if($this->form_validation->run()==false){
//                $error.="<div class='text-danger'>Please Provide Valid Email</div>";
//            }
//        }else{
//            $error.="<div class='text-danger'>Email Must be Enter</div>";
//        }
        if(isset($_POST['MOBILE_NO'])&&isValidData($_POST['MOBILE_NO'])){
            $MOBILE_NO = isValidData($_POST['MOBILE_NO']);
            if(strlen($MOBILE_NO)>=12 ||strlen($MOBILE_NO)<=9){
                $error.="<div class='text-danger'>Invalid Mobile</div>";
            }
            else{
                $firstCharacter = $MOBILE_NO[0];
    
                if($firstCharacter ==0){
                    $MOBILE_NO=  substr($MOBILE_NO ,1);
                }
            }
        }else{
            $error.="<div class='text-danger'>Mobile Must be Enter</div>";
        }


        if(isset($_POST['LAST_NAME'])&&isValidData($_POST['LAST_NAME'])){
            $LAST_NAME = strtoupper(isValidData($_POST['LAST_NAME']));
        }else{
            // if($user['PROVINCE_ID']==6){
            //       $error.="<div class='text-danger'>Last Name / Surname Must be Enter</div>";
            // }
          
        }
        if(isset($_POST['FNAME'])&&isValidData($_POST['FNAME'])){
            $FNAME = strtoupper(isValidData($_POST['FNAME']));
        }else{
            $error.="<div class='text-danger'>Father Name Must be Enter</div>";
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
                $error.="<div class='text-danger'>Religion Must be Select</div>";
            }
        if(isset($_POST['HOME_ADDRESS'])&&isValidData($_POST['HOME_ADDRESS'])){
            $HOME_ADDRESS = strtoupper(isValidData($_POST['HOME_ADDRESS']));
        }else{
            $error.="<div class='text-danger'>Home Address Must be Enter</div>";
        }
        if(isset($_POST['PERMANENT_ADDRESS'])&&isValidData($_POST['PERMANENT_ADDRESS'])){
            $PERMANENT_ADDRESS = strtoupper(isValidData($_POST['PERMANENT_ADDRESS']));
        }else{
            $error.="<div class='text-danger'>Permanent Address Must be Enter</div>";
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
            $CITY_ID = 0;
            //$error.="<div class='text-danger'>City Must be Select</div>";
        }

        if(isset($_POST['DATE_OF_BIRTH'])&&isValidTimeDate($_POST['DATE_OF_BIRTH'],'d/m/Y')){
            $DATE_OF_BIRTH = getDateForDatabase($_POST['DATE_OF_BIRTH']);
            if($DATE_OF_BIRTH>date('Y-m-d')){
                $error.="<div class='text-danger'>Choose Valid Date Of Bith</div>";
            }
        }else{
            $error.="<div class='text-danger'>Date Of Birth Must be Choose</div>";
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
            $error.="<div class='text-danger'>Blood Group Must be Select</div>";
        }
        if(isset($_POST['U_R'])&&isValidData($_POST['U_R'])){
            $U_R = isValidData($_POST['U_R']);
        }else{
            $error.="<div class='text-danger'>Urban / Rural Must be Select</div>";
        }

        if(isset($_POST['CNIC_EXPIRY'])&&isValidTimeDate($_POST['CNIC_EXPIRY'],'d/m/Y')){
            $CNIC_EXPIRY = getDateForDatabase($_POST['CNIC_EXPIRY']);
        }
        if(isset($_POST['PASSPORT_EXPIRY'])&&isValidTimeDate($_POST['PASSPORT_EXPIRY'],'d/m/Y')){
            $PASSPORT_EXPIRY = getDateForDatabase($_POST['PASSPORT_EXPIRY']);
        }

        if(isset($_POST['GENDER'])&&isValidData($_POST['GENDER'])){
            $GENDER = isValidData($_POST['GENDER']);
        }else{
            $error.="<div class='text-danger'>Gender Must be select</div>";
        }

        //Guardian information
        if(isset($_POST['GNAME'])&&isValidData($_POST['GNAME'])){
            $GNAME = strtoupper(isValidData($_POST['GNAME']));
        }else{
            $error.="<div class='text-danger'>Guardian Name Must be Enter</div>";
        }
        if(isset($_POST['REL_GUARD'])&&isValidData($_POST['REL_GUARD'])){
            $REL_GUARD = isValidData($_POST['REL_GUARD']);
        }else{
            $error.="<div class='text-danger'>Relationship of Guardian  Must be select</div>";
        }
        if(isset($_POST['OCC_GUARD'])&&isValidData($_POST['OCC_GUARD'])){
            $OCC_GUARD = isValidData($_POST['OCC_GUARD']);
        }else{
            $error.="<div class='text-danger'>Occupation of Guardian Must be select</div>";
        }
        if(isset($_POST['GAURD_MOBILE_CODE'])&&isValidData($_POST['GAURD_MOBILE_CODE'])){
            $GAURD_MOBILE_CODE = isValidData($_POST['GAURD_MOBILE_CODE']);
        }else{
            $error.="<div class='text-danger'>Guardian Mobile Code Must be select</div>";
        }
        if(isset($_POST['GAURD_MOBILE_NO'])&&isValidData($_POST['GAURD_MOBILE_NO'])){
            $GAURD_MOBILE_NO = isValidData($_POST['GAURD_MOBILE_NO']);
            if(strlen($GAURD_MOBILE_NO)>=12 ||strlen($GAURD_MOBILE_NO)<=9){
                $error.="<div class='text-danger'>Invalid  Gaurdian Mobile</div>";
            }
            $firstCharacter = $GAURD_MOBILE_NO[0];

            if($firstCharacter ==0){
                $GAURD_MOBILE_NO=  substr($GAURD_MOBILE_NO ,1);
            }
        }else{
            $error.="<div class='text-danger'>Guardian's Mobile No Must be Enter</div>";
        }
        if(isset($_POST['GAURD_HOME_ADDRESS'])&&isValidData($_POST['GAURD_HOME_ADDRESS'])){
            $GAURD_HOME_ADDRESS = isValidData($_POST['GAURD_HOME_ADDRESS']);
        }else{
            $error.="<div class='text-danger'>Guardian Address Must be Enter</div>";
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

        if($user['STATUS']=='N'&&$user['REMARKS']=="NEW_ADMISSION"&&!$user['PROFILE_IMAGE']) {
            if (isset($_FILES['profile_image'])) {
                // prePrint($_FILES['profile_image'][]);
                if (isValidData($_FILES['profile_image']['name'])) {

                    $res = $this->upload_image('profile_image', "profile_image_" . $this->user['USER_ID']);
                    if ($res['STATUS'] === true) {
                        $PROFILE_IMAGE = $res['IMAGE_NAME'];

                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                    if ($user['PROFILE_IMAGE']) {
                        $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
                    } else {
                        $error .= "<div class='text-danger'>Must Upload Profile Picture</div>";
                    }
                }
            } else {
                if ($user['PROFILE_IMAGE']) {
                    $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
                } else {
                    $error .= "<div class='text-danger'>Must Upload Profile Picture</div>";
                }
            }
        }else{
            $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
        }
        /*
    if($user['REMARKS']=="NEW_ADMISSION"){
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
//            "COUNTRY_ID"=>$COUNTRY_ID,
//            "PROVINCE_ID"=>$PROVINCE_ID,
//            "DISTRICT_ID"=>$DISTRICT_ID,
            "CITY_ID"=>$CITY_ID,
            "DATE_OF_BIRTH"=>$DATE_OF_BIRTH,
            "ZIP_CODE"=>$ZIP_CODE,
            "BLOOD_GROUP"=>$BLOOD_GROUP,
            "CNIC_EXPIRY"=>$CNIC_EXPIRY,
            "PASSPORT_EXPIRY"=>$PASSPORT_EXPIRY,
            "GENDER"=>$GENDER,
            "PROFILE_IMAGE"=>$PROFILE_IMAGE,
            "U_R"=>$U_R
        );
    }else{
          $form_array = array(
           "PREFIX_ID"=>$PREFIX_ID,
            "MOBILE_NO"=>$MOBILE_NO,
            "MOBILE_CODE"=>$MOBILE_CODE,
            "PLACE_OF_BIRTH"=>$PLACE_OF_BIRTH,
            "HOME_ADDRESS"=>$HOME_ADDRESS,
            "PERMANENT_ADDRESS"=>$PERMANENT_ADDRESS,
            "PHONE"=>$PHONE,
            "RELIGION"=>$RELIGION,
//            "COUNTRY_ID"=>$COUNTRY_ID,
//            "PROVINCE_ID"=>$PROVINCE_ID,
//            "DISTRICT_ID"=>$DISTRICT_ID,
            "CITY_ID"=>$CITY_ID,
            "DATE_OF_BIRTH"=>$DATE_OF_BIRTH,
            "ZIP_CODE"=>$ZIP_CODE,
            "BLOOD_GROUP"=>$BLOOD_GROUP,
            "CNIC_EXPIRY"=>$CNIC_EXPIRY,
            "PASSPORT_EXPIRY"=>$PASSPORT_EXPIRY,
            "GENDER"=>$GENDER,
            "U_R"=>$U_R
        );
    }
    */
     $form_array = array(
           "PREFIX_ID"=>$PREFIX_ID,
            "MOBILE_NO"=>$MOBILE_NO,
            "MOBILE_CODE"=>$MOBILE_CODE,
            "PLACE_OF_BIRTH"=>$PLACE_OF_BIRTH,
            "HOME_ADDRESS"=>$HOME_ADDRESS,
            "PERMANENT_ADDRESS"=>$PERMANENT_ADDRESS,
            "PHONE"=>$PHONE,
            "RELIGION"=>$RELIGION,
//            "COUNTRY_ID"=>$COUNTRY_ID,
//            "PROVINCE_ID"=>$PROVINCE_ID,
//            "DISTRICT_ID"=>$DISTRICT_ID,
            "CITY_ID"=>$CITY_ID,
            "DATE_OF_BIRTH"=>$DATE_OF_BIRTH,
            "ZIP_CODE"=>$ZIP_CODE,
            "BLOOD_GROUP"=>$BLOOD_GROUP,
            "CNIC_EXPIRY"=>$CNIC_EXPIRY,
            "PASSPORT_EXPIRY"=>$PASSPORT_EXPIRY,
            "GENDER"=>$GENDER,
            "PROFILE_IMAGE"=>$PROFILE_IMAGE,
            "U_R"=>$U_R
        );
      
        if($user['STATUS']=='C' ){
            $error.="<div class='text-danger'>Your application is locked thats why you can't change any given information.</div>";
        }
        if($error==""){
            if($user['STATUS']=='G' ){
                $res = 0;
                $res_guard = $this->User_model->saveGuardianByUserId($USER_ID,$gardian_array);
            }else{
                $res = $this->User_model->updateUserById($USER_ID,$form_array);
                $res_guard = $this->User_model->saveGuardianByUserId($USER_ID,$gardian_array);
            }

            if($res===-1||$res_guard==-1){
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
            }
            if($res===0&&$res_guard==0){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>No data has been updated..!<div>";
            }else{
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Saved ..!<div>";
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

    function uploadDocuments(){ 
        $APPLICATION_ID=$this->APPLICATION_ID;
        if ($this->input->server('REQUEST_METHOD') == 'POST'){
            $reponse = getcsrf($this);
            $USER_ID = $this->user['USER_ID'];
            $user = $this->User_model->getUserById($USER_ID);
            $application = $this->Application_model->getApplicationByUserAndApplicationId($USER_ID,$APPLICATION_ID);
            $condition_for_bachelor = $application['PROGRAM_TYPE_ID']==1 && $application['BACHELOR_FORM_COMPLETE']==0;
          
            $folder = EXTRA_IMAGE_CHECK_PATH."$USER_ID";
            if(!is_dir($folder)){
                mkdir(EXTRA_IMAGE_CHECK_PATH."/$USER_ID");
            }

            $CNIC_FRONT_IMAGE = "";

            if($user['CNIC_FRONT_IMAGE']){
                $CNIC_FRONT_IMAGE = EXTRA_IMAGE_CHECK_PATH.$user['CNIC_FRONT_IMAGE'] ;
            }

            if(empty($CNIC_FRONT_IMAGE)){

                $CNIC_FRONT_IMAGE="";
                $user['CNIC_FRONT_IMAGE']="";
            }else{
                $CNIC_FRONT_IMAGE = $user['CNIC_FRONT_IMAGE'] ;
            }

            $CNIC_BACK_IMAGE = "";

            if($user['CNIC_BACK_IMAGE']){
                $CNIC_BACK_IMAGE = EXTRA_IMAGE_CHECK_PATH.$user['CNIC_BACK_IMAGE'] ;
            }

            if(empty($CNIC_BACK_IMAGE)){

                $CNIC_BACK_IMAGE="";
                $user['CNIC_BACK_IMAGE']="";
            }else{
                $CNIC_BACK_IMAGE = $user['CNIC_BACK_IMAGE'] ;
            }

            $PASSPORT_FRONT_IMAGE = "";

            if($user['PASSPORT_FRONT_IMAGE']){
                $PASSPORT_FRONT_IMAGE = EXTRA_IMAGE_CHECK_PATH.$user['PASSPORT_FRONT_IMAGE'] ;
            }

            if(empty($PASSPORT_FRONT_IMAGE)){

                $PASSPORT_FRONT_IMAGE="";
                $user['PASSPORT_FRONT_IMAGE']="";
            }else{
                $PASSPORT_FRONT_IMAGE = $user['PASSPORT_FRONT_IMAGE'] ;
            }

            $PASSPORT_BACK_IMAGE = "";

            if($user['PASSPORT_BACK_IMAGE']){
                $PASSPORT_BACK_IMAGE = EXTRA_IMAGE_CHECK_PATH.$user['PASSPORT_BACK_IMAGE'] ;
            }

            if(empty($PASSPORT_BACK_IMAGE)){

                $PASSPORT_BACK_IMAGE="";
                $user['PASSPORT_BACK_IMAGE']="";
            }else{
                $PASSPORT_BACK_IMAGE = $user['PASSPORT_BACK_IMAGE'] ;
            }

            $DOMICILE_IMAGE = "";

            if($user['DOMICILE_IMAGE']){
                $DOMICILE_IMAGE = EXTRA_IMAGE_CHECK_PATH.$user['DOMICILE_IMAGE'] ;
            }

            if(empty($DOMICILE_IMAGE)){

                $DOMICILE_IMAGE="";
                $user['DOMICILE_IMAGE']="";
            }else{
                $DOMICILE_IMAGE = $user['DOMICILE_IMAGE'] ;
            }

            $DOMICILE_FORM_C_IMAGE = "";

            if($user['DOMICILE_FORM_C_IMAGE']){
                $DOMICILE_FORM_C_IMAGE = EXTRA_IMAGE_CHECK_PATH.$user['DOMICILE_FORM_C_IMAGE'] ;
            }

            if(empty($DOMICILE_FORM_C_IMAGE)){

                $DOMICILE_FORM_C_IMAGE="";
                $user['DOMICILE_FORM_C_IMAGE']="";
            }else{
                $DOMICILE_FORM_C_IMAGE =$user['DOMICILE_FORM_C_IMAGE'] ;
            }

            //start method work
            $is_upload_any_doc = false;
            $config_a = array();
            $config_a['maintain_ratio'] = true;
            $config_a['width']         = 360;
            $config_a['height']       = 500;
            $config_a['resize']       = false;

            $error="";
            if($user['STATUS']!='C') {
                if ($user['IS_CNIC_PASS'] === 'P') {

                    //checking passport validation
                    if (isset($_FILES['passport_front_image'])) {
                        if (isValidData($_FILES['passport_front_image']['name'])) {

                            $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                            $image_name = "passport_front_image_$USER_ID"."_".$APPLICATION_ID;
                            $res = $this->upload_image('passport_front_image', $image_name, $this->file_size, $file_path, $config_a);
                            if ($res['STATUS'] === true) {
                                $PASSPORT_FRONT_IMAGE = "$USER_ID/" . $res['IMAGE_NAME'];
                                $is_upload_any_doc = true;
                            } else {
                                $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                            }
                        } else {
                            if ($PASSPORT_FRONT_IMAGE == "")
                                $error .= "<div class='text-danger'>Must Passport Front Image and image size must be less then 500kb </div>";
                        }
                    } else {
                        if ($PASSPORT_FRONT_IMAGE == "")
                            $error .= "<div class='text-danger'>Must Passport Front Image and image size must be less then 500kb </div>";
                    }

                    if (isset($_FILES['passport_back_image'])) {
                        if (isValidData($_FILES['passport_back_image']['name'])) {

                            $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                            $image_name = "passport_back_image_$USER_ID"."_".$APPLICATION_ID;
                            $res = $this->upload_image('passport_back_image', $image_name, $this->file_size, $file_path, $config_a);
                            if ($res['STATUS'] === true) {
                                $PASSPORT_BACK_IMAGE = "$USER_ID/" . $res['IMAGE_NAME'];
                                $is_upload_any_doc = true;
                            } else {
                                $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                            }
                        } else {
                            if ($PASSPORT_BACK_IMAGE == "")
                                $error .= "<div class='text-danger'>Must Passport Back Image and image size must be less then 500kb </div>";
                        }

                    } else {
                        if ($PASSPORT_BACK_IMAGE == "")
                            $error .= "<div class='text-danger'>Must Passport Back Image and image size must be less then 500kb Id Not found something went worng </div>";
                    }


                } else {

                    //checking cnic validation
                    if (isset($_FILES['cnic_front_image'])) {
                        if (isValidData($_FILES['cnic_front_image']['name'])) {

                            $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                            $image_name = "cnic_front_image_$USER_ID"."_".$APPLICATION_ID;
                            $res = $this->upload_image('cnic_front_image', $image_name, $this->file_size, $file_path, $config_a);
                            if ($res['STATUS'] === true) {
                                $CNIC_FRONT_IMAGE = "$USER_ID/" . $res['IMAGE_NAME'];
                                $is_upload_any_doc = true;

                            } else {
                                $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                            }
                        } else {
                            if ($CNIC_FRONT_IMAGE == "")
                                $error .= "<div class='text-danger'>Must Cnic Front Image and image size must be less then 500kb </div>";
                        }
                    } else {

                        if ($CNIC_FRONT_IMAGE == "")
                            $error .= "<div class='text-danger'>Must Cnic Front Image and image size must be less then 500kb </div>";
                    }

                    if (isset($_FILES['cnic_back_image'])) {
                        if (isValidData($_FILES['cnic_back_image']['name'])) {

                            $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                            $image_name = "cnic_back_image_$USER_ID"."_".$APPLICATION_ID;
                            $res = $this->upload_image('cnic_back_image', $image_name, $this->file_size, $file_path, $config_a);
                            if ($res['STATUS'] === true) {
                                $CNIC_BACK_IMAGE = "$USER_ID/" . $res['IMAGE_NAME'];
                                $is_upload_any_doc = true;
                            } else {
                                $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                            }
                        } else {
                            if ($CNIC_BACK_IMAGE == "")
                                $error .= "<div class='text-danger'>Must Cnic Back Image and image size must be less then 500kb </div>";
                        }

                    } else {
                        if ($CNIC_BACK_IMAGE == "")
                            $error .= "<div class='text-danger'>Must Cnic Back Image and image size must be less then 500kb Id Not found something went worng </div>";
                    }

                }


                //checking domicile validation
                if (isset($_FILES['domicile_image'])) {
                    if (isValidData($_FILES['domicile_image']['name'])) {

                        $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                        $image_name = "domicile_image_$USER_ID"."_".$APPLICATION_ID;
                        $res = $this->upload_image('domicile_image', $image_name, $this->file_size, $file_path, $config_a);
                        if ($res['STATUS'] === true) {
                            $DOMICILE_IMAGE = "$USER_ID/" . $res['IMAGE_NAME'];
                            $is_upload_any_doc = true;

                        } else {
                            $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        }
                    } else {
                        if ($DOMICILE_IMAGE == "" && !$condition_for_bachelor)
                            $error .= "<div class='text-danger'>Must Upload Domicile Image and image size must be less then 500kb  </div>";
                    }
                } else {

                    if ($DOMICILE_IMAGE == "" && !$condition_for_bachelor)
                        $error .= "<div class='text-danger'>Must Upload Domicile Image and image size must be less then 500kb </div>";
                }

                if (isset($_FILES['domicile_formc_image'])) {
                    if (isValidData($_FILES['domicile_formc_image']['name'])) {

                        $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                        $image_name = "domicile_formc_image_$USER_ID"."_".$APPLICATION_ID;
                        $res = $this->upload_image('domicile_formc_image', $image_name, $this->file_size, $file_path, $config_a);
                        if ($res['STATUS'] === true) {
                            $DOMICILE_FORM_C_IMAGE = "$USER_ID/" . $res['IMAGE_NAME'];
                            $is_upload_any_doc = true;
                        } else {
                            $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        }
                    } else {
                        if ($DOMICILE_FORM_C_IMAGE == "" && !$condition_for_bachelor)
                            $error .= "<div class='text-danger'>Must Upload Domicile  Form-C Image and image size must be less then 500kb </div>";
                    }

                } else {
                    if ($DOMICILE_FORM_C_IMAGE == "" && !$condition_for_bachelor)
                        $error .= "<div class='text-danger'>Must Upload Domicile Form-C Image and image size must be less then 500kb Id Not found something went worng </div>";
                }
            }
            else{
                $error .= "<div class='text-danger'>You are not authorized to update documents</div>";
            }

            if($error==""){
                $form_array = array(
                    "DOMICILE_FORM_C_IMAGE"=>$DOMICILE_FORM_C_IMAGE,
                    "DOMICILE_IMAGE"=>$DOMICILE_IMAGE,
                    "CNIC_FRONT_IMAGE"=>$CNIC_FRONT_IMAGE,
                    "CNIC_BACK_IMAGE"=>$CNIC_BACK_IMAGE,
                    "PASSPORT_FRONT_IMAGE"=>$PASSPORT_FRONT_IMAGE,
                    "PASSPORT_BACK_IMAGE"=>$PASSPORT_BACK_IMAGE,

                );
                $res = $this->User_model->updateUserById($USER_ID,$form_array);
                if($res===1){
                    $reponse['RESPONSE'] = "SUCCESS";
                    $reponse['MESSAGE'] = "<div class='text-success'>Document Upload Successfully...!<div>";
                }else if($res===0){
                    $reponse['RESPONSE'] = "SUCCESS";
                    if($is_upload_any_doc == true){
                        $reponse['MESSAGE'] = "<div class='text-success'>Document Upload Successfully...!<div>";
                    }else{
                        $reponse['MESSAGE'] = "<div class='text-success'>No changes has been made..!<div>";
                    }

                }else{
                    $reponse['RESPONSE'] = "ERROR";
                    $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
                }

            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = $error;

            }




        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "Invalid Request Method";
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

    function apiGetExperianceList(){
        $user = $this->session->userdata($this->SessionName);
        $experiancesList = $this->User_model->getExperiancesByUserId($user['USER_ID']);
        $output= "<div style='overflow-x:auto'>
            <table class='table table-bordered' >
                <tr>
                   <th>EMPLOYMENT TYPE</th>
                   <th>ORGANIZATION NAME</th>
                   <th>ADDRESS</th>
                   <th>CONTACT NO</th>
                   <th>JOB DESCRIPTION</th>
                   <th>JOB CONTINUE</th>
                    <th>SALARY</th>
                   <th>START DATE</th>
                   <th>END DATE</th>
                   <th colspan='2'>ACTION</th>
                </tr>";
        foreach($experiancesList as $degree) {
            if($degree['IS_JOB_CONTINUE']=='N'){
                $degree['IS_JOB_CONTINUE'] = "NO";
            }else{
                $degree['IS_JOB_CONTINUE'] = "YES";
            }
            $output.= "<tr>
                        <td>{$degree['EMP_TYPE']}</td>
                        <td>{$degree['ORGANIZATION_NAME']}</td>
                        <td>{$degree['ADDRESS']}</td>
                        <td>{$degree['CONTACT_NO']}</td>
                        <td>{$degree['JOB_DESCRIPTION']}</td>
                        <td>{$degree['IS_JOB_CONTINUE']}</td>
                        <td>{$degree['SALARY']}</td>
                       <td>".getDateCustomeView($degree['START_DATE'],'d,M,Y')."</td>
                        <td>".getDateCustomeView($degree['END_DATE'],'d,M,Y')."</td>
                        <td><button onclick=\"deleteExperiance('{$degree['EXPERIANCE_ID']}')\" class='btn btn-danger'><i class='fa fa-trash'></i> Delete</button></td>
                    </tr>";


        }


        $output .="</table>  </div>";
        echo $output;
    }

    function apiGetAddExperianceForm(){
        $user = $this->session->userdata($this->SessionName);

        $this->load->view('profile_section/add_experiance_form');
    }

    function addExperiance(){

        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];
        if ($this->input->server('REQUEST_METHOD') == 'POST'){



            $error="";
            $DESCRIPTION =$ADDRESS = $ORGANIZATION_NAME = $EMP_TYPE=$CONTACT_NO="";
            $SALARY=0;
            $START_DATE = $END_DATE = '1900-01-01';
            $IS_JOB_CONTINUE = 'N';
            if(isset($_POST['is_job_continue'])){
                $IS_JOB_CONTINUE = 'Y';
            }
            if(isset($_POST['EMP_TYPE'])&&isValidData($_POST['EMP_TYPE'])){
                $EMP_TYPE =isValidData($_POST['EMP_TYPE']);
            }else{
                $error .= "<div class='text-danger'>Employeement type must be Choice<div>";
            }
            if(isset($_POST['ORGANIZATION_NAME'])&&isValidData($_POST['ORGANIZATION_NAME'])){
                $ORGANIZATION_NAME =isValidData($_POST['ORGANIZATION_NAME']);
            }else{
                $error .= "<div class='text-danger'>Organization name must be provided<div>";
            }
            if(isset($_POST['ADDRESS'])&&isValidData($_POST['ADDRESS'])){
                $ADDRESS =isValidData($_POST['ADDRESS']);
            }else{
                $error .= "<div class='text-danger'>Address must be provided<div>";
            }
            if(isset($_POST['DESCRIPTION'])&&isValidData($_POST['DESCRIPTION'])){
                $DESCRIPTION =isValidData($_POST['DESCRIPTION']);
            }else{
                $error .= "<div class='text-danger'>Job Description must be provided<div>";
            }
            if(isset($_POST['CONTACT_NO'])&&isValidData($_POST['CONTACT_NO'])){
                $CONTACT_NO =isValidData($_POST['CONTACT_NO']);
            }
            if(isset($_POST['SALARY'])&&isValidData($_POST['SALARY'])){
                $SALARY =isValidData($_POST['SALARY']);
            }

            if (isset($_POST['JOB_START_DATE']) && isValidTimeDate($_POST['JOB_START_DATE'], 'd/m/Y')) {
                $START_DATE = getDateForDatabase($_POST['JOB_START_DATE']);
            }
            else {
                $error .= "<div class='text-danger'>Job Start Date must be Filled</div>";
            }

            if (isset($_POST['JOB_END_DATE']) && isValidTimeDate($_POST['JOB_END_DATE'], 'd/m/Y')) {
                $END_DATE = getDateForDatabase($_POST['JOB_END_DATE']);
            }
            else {
                if($IS_JOB_CONTINUE=='N'){
                    $error .= "<div class='text-danger'>Job End Date must be Filled</div>";
                }
            }
            if ($END_DATE <= $START_DATE) {
                if($IS_JOB_CONTINUE=='N') {
                    $error .= "<div class='text-danger'>Invalid Date Start Date must be less then End Date</div>";
                }
            }


            if($error=="") {
                $form_array = array('USER_ID' => $USER_ID,
                    'EMP_TYPE' => $EMP_TYPE,
                    'ORGANIZATION_NAME' => $ORGANIZATION_NAME,
                    'ADDRESS' => $ADDRESS,
                    'CONTACT_NO' => $CONTACT_NO,
                    'START_DATE' => $START_DATE,
                    'END_DATE' => $END_DATE,
                    'JOB_DESCRIPTION' => $DESCRIPTION,
                    'IS_JOB_CONTINUE' => $IS_JOB_CONTINUE,
                    'SALARY' => $SALARY,
                    'ACTIVE' => 1);

                $res = $this->User_model->addExperiances($form_array);



                if($res===true){
                    $reponse['RESPONSE'] = "SUCCESS";
                    $reponse['MESSAGE'] = "<div class='text-success'>Successfully Save ..!<div>";
                }else{
                    $reponse['RESPONSE'] = "ERROR";
                    $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
                }

            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = $error;
            }




        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "Invalid Request Method";
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

    function apiDeleteExperiance(){
        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];



        $experiance_id = $this->input->post('experiance_id');

        if(is_numeric($experiance_id)) {

            $res = $this->User_model->deleteExperiance($USER_ID,$experiance_id);

            if($res ===true){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Deleted Experiance...!</div>";
            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-success'>Somethis went wrong may be your not authorize to delete your Experiance...!</div>";
            }

        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "<div class='text-success'>Invalid Experiance ID...!</div>";
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

    function apiGetQualificationList(){
        $user = $this->session->userdata($this->SessionName);
        $qulificationList = $this->Api_qualification_model->getQualificatinByUserId($user['USER_ID'],$this->APPLICATION_ID);
        $output= "<div style='overflow-x:auto'>
            <table class='table table-bordered' >
                <tr>
                    <th>Qualification / Degree / Certificate</th>
                    <th>Discipline / Subject / Group</th>
                    <th>Organization / University / Board</th>
                    
                    <th>Roll No</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    <th>Marksheet</th>
                    <th>Pass certificate</th>
                    <th>Exam Year</th>
                    
                    <th colspan='2'>ACTION</th>
                </tr>";

        foreach($qulificationList as $degree) {
            $edit_button="";
            $delete_button="";
            if($degree['STATUS']==0){
                $edit_button = "<button class='btn btn-info' onclick=\"editQualification('{$degree['QUALIFICATION_ID']}') \"><i class='fa fa-pencil-square-o'></i> Edit</button>";
                $delete_button ="<button onclick=\"deleteQualification('{$degree['QUALIFICATION_ID']}')\" class='btn btn-danger'><i class='fa fa-trash'></i> Delete</button>";
            }
            if($degree['DEGREE_ID']==10){
                continue;
            }
            $output.= "<tr>
                        <td>{$degree['DEGREE_TITLE']}</td>
                        <td>{$degree['DISCIPLINE_NAME']}</td>
                        <td>{$degree['ORGANIZATION']}</td>
                        
                        <td>{$degree['ROLL_NO']}</td>
                        <td>{$degree['TOTAL_MARKS']}</td>
                        <td>{$degree['OBTAINED_MARKS']}</td>
                        <td><img class='img-table-certificate' src='".base_url().EXTRA_IMAGE_PATH.$degree['MARKSHEET_IMAGE']."' alt='MARKSHEET_IMAGE'></td>
                        <td><img class='img-table-certificate' src='".base_url().EXTRA_IMAGE_PATH.$degree['PASSCERTIFICATE_IMAGE']."' alt='PASSCERTIFICATE_IMAGE'></td>
                        <td>{$degree['PASSING_YEAR']}</td>
                        <td>$edit_button</td>
                        <td>$delete_button</td>
                    </tr>";


        }


        $output .="</table> 
                    
                    </div>
                    <style>
                    .img-table-certificate{
                    border: #40402e;
                    border-style: double;
                    border-radius: 10%;
                    }
                    </style>
                    
                    ";
        echo $output;
    }

    function apiGetAddQualificationForm(){
        $user = $this->session->userdata($this->SessionName);

        $degree = $this->Api_qualification_model->getAllDegreeProgram();
        $organization = $this->Api_qualification_model->getAllOrganization();
        $program_type_id = 0;
        if(isset($_GET['program_type_id'])){
            $program_type_id = $_GET['program_type_id'];
        }
        $data['degree_program'] = $degree;
        $data['organizations'] = $organization;
        $data['program_type_id'] = $program_type_id;
        $this->load->view('profile_section/qualification_form',$data);
    }

    function apiGetEditQualificationForm(){
        $qul_id = $this->input->get('qualification_id');
        if($qul_id){
            $user = $this->session->userdata($this->SessionName);

            $data['qualification'] =$qualification= $this->Api_qualification_model->getQualificationByUserIdAndQulificationId($user['USER_ID'],$qul_id,$this->APPLICATION_ID);
            if(isset($data['qualification'])&&$data['qualification']) {
                $program_type_id = 0;
                if(isset($_GET['program_type_id'])){
                    $program_type_id = $_GET['program_type_id'];
                }
                $degree = $this->Api_qualification_model->getAllDegreeProgram();

                $organization = $this->Api_qualification_model->getAllOrganization();
                $institute = $this->Api_qualification_model->getInstituteByOrgId($qualification['ORGANIZATION_ID']);
                $discipline = $this->Api_qualification_model->getDisciplineByDegreeId($qualification['DEGREE_ID']);

                $data['degree_program'] = $degree;
                $data['organizations'] = $organization;
                $data['institutes'] = $institute;
                $data['disciplines'] = $discipline;
                $data['program_type_id'] = $program_type_id;
                $this->load->view('profile_section/edit_qualification_form', $data);
            }else{
                echo "<div class='text-danger'>Something went wrong</div>";
            }
        }else{
            echo "<div class='text-danger'>Something went wrong</div>";
        }

    }

    function addQualification(){
        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];
        $folder = EXTRA_IMAGE_CHECK_PATH."$USER_ID";
        $APPLICATION_ID = $this->APPLICATION_ID;
        //echo $folder;
        if(!is_dir($folder)){
            // $folder;
            mkdir(EXTRA_IMAGE_CHECK_PATH."/$USER_ID");
        }
        $ROLL_NO = $grade = "";
        $IS_DECLARE = 'N';
        $PASSING_YEAR =$RESULT_DATE = $START_DATE = $END_DATE = '1900-01-01';
        $cgpa = $out_of = $DEGREE_ID = $DISCIPLINE_ID = $INSTITUTE_ID = $ORGANIZATION_ID = $TOTAL_MARKS = $OBTAINED_MARKS = 0;
        $error = "";

        if (isset($_POST['DEGREE_ID']) && isValidData($_POST['DEGREE_ID'])) {
            $DEGREE_ID = (int)isValidData($_POST['DEGREE_ID']);
        } else {
            $error .= "<div class='text-danger'>Qualification / Degree / Certificate must be select</div>";
        }

        if (isset($_POST['check_grade'])) {
            if ($_POST['check_grade'] == 'grade') {
                $GRADING_AS = "G";
                if (isset($_POST['grade']) && isValidData($_POST['grade'])) {
                    $grade = isValidData($_POST['grade']);
                } else {
                  //  $error .= "<div class='text-danger'>Grade must be select</div>";
                }
            } else if ($_POST['check_grade'] == 'cgpa') {
                $GRADING_AS = "C";
                if (isset($_POST['cgpa']) && isValidData($_POST['cgpa'])) {
                    $cgpa = isValidData($_POST['cgpa']);
                } else {
                    //$error .= "<div class='text-danger'>CGPA Must Enter</div>";
                }
                if (isset($_POST['out_of']) && isValidData($_POST['out_of'])) {
                    $out_of = isValidData($_POST['out_of']);
                } else {
                    //$error .= "<div class='text-danger'>Out Of must select</div>";
                }

                if (!is_numeric($cgpa)) {
                   // $error .= "<div class='text-danger'> Must Enter Valid CGPA / Percentage</div>";
                } else {
                    $cgpa = number_format((float)$cgpa, 2, '.', '');
                    if ($cgpa > $out_of) {
                     //   $error .= "<div class='text-danger'> Must Enter Valid CGPA / Percentage</div>";
                    }
                }


            }
        }
        if (!isset($_POST['result_not_declare'])) {
            $IS_DECLARE = 'Y';
            if (isset($_POST['RESULT_DATE']) && isValidTimeDate($_POST['RESULT_DATE'], 'd/m/Y')) {
                $RESULT_DATE = getDateForDatabase($_POST['RESULT_DATE']);
            } else {
               // $error .= "<div class='text-danger'>Result Declare Date must be Filled</div>";
            }

        }


        if (isset($_POST['INSTITUTE_ID']) && isValidData($_POST['INSTITUTE_ID'])) {
            $INSTITUTE_ID = isValidData($_POST['INSTITUTE_ID']);
            if ($INSTITUTE_ID <= 0) {
                //$error .= "<div class='text-danger'>Institute / Department / School / College must be select</div>";
            }
        } else {
           // $error .= "<div class='text-danger'>Institute / Department / School / College must be select</div>";
        }
        if (isset($_POST['ORGANIZATION_ID']) && isValidData($_POST['ORGANIZATION_ID'])) {
            $ORGANIZATION_ID = isValidData($_POST['ORGANIZATION_ID']);
        } else {
            $error .= "<div class='text-danger'>Organization / University / Board must be select</div>";
        }
        if (isset($_POST['DISCIPLINE_ID']) && isValidData($_POST['DISCIPLINE_ID'])) {
            $DISCIPLINE_ID = isValidData($_POST['DISCIPLINE_ID']);
            if ($DISCIPLINE_ID <= 0) {
                $error .= "<div class='text-danger'>Discipline / Subject / Group must be select</div>";
            }
        } else {
            $error .= "<div class='text-danger'>Discipline / Subject / Group must be select</div>";
        }
        if (isset($_POST['ROLL_NO']) && isValidData($_POST['ROLL_NO'])) {
            $ROLL_NO = isValidData($_POST['ROLL_NO']);
        } else {
            $error .= "<div class='text-danger'>Roll Number must be Filled</div>";
        }
        if (isset($_POST['TOTAL_MARKS']) && isValidData($_POST['TOTAL_MARKS'])) {
            $TOTAL_MARKS = isValidData($_POST['TOTAL_MARKS']);
            if($TOTAL_MARKS<100){
                $error .= "<div class='text-danger'>Total Marks must be Filled</div>";
            }
        } else {
            if ($DEGREE_ID != 8)
                $error .= "<div class='text-danger'>Total Marks must be Filled</div>";
        }
        if (isset($_POST['OBTAINED_MARKS']) && isValidData($_POST['OBTAINED_MARKS'])) {
            $OBTAINED_MARKS = isValidData($_POST['OBTAINED_MARKS']);
        } else {
            if ($DEGREE_ID != 8)
                $error .= "<div class='text-danger'>Obtained Marks must be Filled</div>";
        }

        if (isset($_POST['PASSING_YEAR']) && isValidData($_POST['PASSING_YEAR'])) {
            $PASSING_YEAR = isValidData($_POST['PASSING_YEAR']);
        } else {

                $error .= "<div class='text-danger'>Passing Year Must be Select</div>";
        }


        if (isset($_POST['START_DATE']) && isValidTimeDate($_POST['START_DATE'], 'd/m/Y')) {
            $START_DATE = getDateForDatabase($_POST['START_DATE']);
        } else {
           // $error .= "<div class='text-danger'>Start Date must be Filled</div>";
        }
        if (isset($_POST['END_DATE']) && isValidTimeDate($_POST['END_DATE'], 'd/m/Y')) {
            $END_DATE = getDateForDatabase($_POST['END_DATE']);
        } else {
            //$error .= "<div class='text-danger'>End Date must be Filled</div>";
        }
        if ($END_DATE <= $START_DATE) {
           // $error .= "<div class='text-danger'>Invalid Date Start Date must be less then End Date</div>";
        }



        if ($DEGREE_ID != 8 && $TOTAL_MARKS <= 0) {
            $error .= "<div class='text-danger'>Invalid Total Marks </div>";
        }
        if ($DEGREE_ID != 8 && $OBTAINED_MARKS <= 0) {
            $error .= "<div class='text-danger'>Invalid Obtained Marks </div>";
        }
        if ($DEGREE_ID == 8 && $TOTAL_MARKS < 0) {
            $error .= "<div class='text-danger'>Invalid Total Marks </div>";
        }
        if ($DEGREE_ID == 8 && $OBTAINED_MARKS < 0) {
            $error .= "<div class='text-danger'>Invalid Obtained Marks </div>";
        }


        if ($TOTAL_MARKS < $OBTAINED_MARKS) {
            $error .= "<div class='text-danger'>Obtained Marks must be less then or Equal to Total Marks </div>";
        }


        $degree_name =  $this->Api_qualification_model->getDegreeProgramById($DEGREE_ID);
        $degree_name = str_replace(' ','_',$degree_name['DEGREE_TITLE']);
        $degree_name = str_replace('/','_',$degree_name);
        $config_a = array();
        $config_a['maintain_ratio'] = true;
        $config_a['width']         = 360;
        $config_a['height']       = 500;

        $config_a['resize']       = false;

        if(isset($_FILES['marksheet_image'])){
            if (isValidData($_FILES['marksheet_image']['name'])) {

                $file_path = EXTRA_IMAGE_CHECK_PATH."$USER_ID/";
                $image_name = $degree_name."_marksheet_image_$USER_ID"."_".$APPLICATION_ID;
                $res =  $this->upload_image('marksheet_image',$image_name,$this->file_size,$file_path,$config_a);
                if($res['STATUS']===true){
                    $marksheet_image = "$USER_ID/".$res['IMAGE_NAME'];

                }else{
                    $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                }
            }else{
                if($DEGREE_ID!=8)
                    $error.="<div class='text-danger'>Must Upload Marksheet and image size must be less then 500kb </div>";
            }
        }
        else{
            if($DEGREE_ID!=8)
                $error.="<div class='text-danger'>Must Upload Marksheet and image size must be less then 500kb Id Not found something went worng </div>";
        }
$passcertificate_image = "";
        if(isset($_FILES['passcertificate_image'])){
            if (isValidData($_FILES['passcertificate_image']['name'])) {

                $file_path = EXTRA_IMAGE_CHECK_PATH."$USER_ID/";
                $image_name = $degree_name."_passcertificate_image_$USER_ID"."_".$APPLICATION_ID;
                $res =  $this->upload_image('passcertificate_image',$image_name,$this->file_size,$file_path,$config_a);
                if($res['STATUS']===true){
                    $passcertificate_image = "$USER_ID/".$res['IMAGE_NAME'];
                }else{
                    $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                }
            }else{
                 if(!($DEGREE_ID==8 || $DEGREE_ID==10))
                    $error.="<div class='text-danger'>Must Upload Pass Certificate and image size must be less then 500kb </div>";
            }

        }
        else{
            if(!($DEGREE_ID==8 || $DEGREE_ID==10))
            $error.="<div class='text-danger'>Must Upload Pass Certificate and image size must be less then 500kb Id Not found something went worng </div>";
        }


        $if_exist = $this->Api_qualification_model->getQualificatinByUserIdAndDegreeId($USER_ID,$DEGREE_ID,$this->APPLICATION_ID);

        if(count($if_exist)) {
            $error.="<div class='text-danger'>Same Qualification is Already Exist Please Update OR Delete...!</div>";
        }

        if($error==""){



            $form_array = array(
                "USER_ID"           =>  $USER_ID,
                "DISCIPLINE_ID"     =>$DISCIPLINE_ID,
                "ORGANIZATION_ID"   =>  $ORGANIZATION_ID,
                "INSTITUTE_ID"      =>  $INSTITUTE_ID,
                "START_DATE"        =>  $START_DATE,
                "END_DATE"          =>  $END_DATE,
                "RESULT_DATE"       =>  $RESULT_DATE,
                "TOTAL_MARKS"       =>  $TOTAL_MARKS,
                "OBTAINED_MARKS"    =>  $OBTAINED_MARKS,
                "CGPA"              =>  $cgpa,
                "GRADING_AS"        =>  $GRADING_AS,
                "GRADE"             =>  $grade,
                "ACTIVE"            =>  1,
                "IS_RESULT_DECLARE" =>  $IS_DECLARE,
                "ROLL_NO"           =>  $ROLL_NO,
                "OUT_OF"            =>  $out_of,
                "MARKSHEET_IMAGE"   =>  $marksheet_image,
                "PASSCERTIFICATE_IMAGE"=>  $passcertificate_image,
                "PASSING_YEAR"=>  $PASSING_YEAR,
            );
            $res = $this->Api_qualification_model->addQualification($form_array,$this->APPLICATION_ID);



            if($res===true){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Save ..!<div>";
            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
            }

        }
        else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = $error;
        }

        if($reponse['RESPONSE'] == "ERROR"){
            if(!isset($reponse['MESSAGE'])){
                $reponse['MESSAGE'] = "<div class='text-danger'>Message Not Defined..!</div>";
            }
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
        else{
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }

    function updateQualification(){
        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];
        $error = "";
        $QUAL_ID = 0;
        $APPLICATION_ID = $this->APPLICATION_ID;
        if(isset($_POST['QUAL_ID'])&& isValidData($_POST['QUAL_ID'])&&is_numeric($_POST['QUAL_ID'])){
            $QUAL_ID = (int)isValidData($_POST['QUAL_ID']);
        }else{
            $error .= "<div class='text-danger'>Invalid Qualification Id</div>";
        }

        $qualification = $this->Api_qualification_model->getQualificationByUserIdAndQulificationId($USER_ID,$QUAL_ID,$this->APPLICATION_ID);

        if(!$qualification){
            $error .= "<div class='text-danger'>Qualification Not Found</div>";
        }
        else {

            $folder = EXTRA_IMAGE_CHECK_PATH . "$USER_ID";

            if (!is_dir($folder)) {

                mkdir(EXTRA_IMAGE_CHECK_PATH . "/$USER_ID");
            }

            $ROLL_NO = $grade = "";
            $IS_DECLARE = 'N';
            $PASSING_YEAR = $RESULT_DATE = $START_DATE = $END_DATE = '1900-01-01';
            $cgpa = $out_of = $DEGREE_ID = $DISCIPLINE_ID = $INSTITUTE_ID = $ORGANIZATION_ID = $TOTAL_MARKS = $OBTAINED_MARKS = 0;


            if (isset($_POST['DEGREE_ID']) && isValidData($_POST['DEGREE_ID'])) {
                $DEGREE_ID = (int)isValidData($_POST['DEGREE_ID']);
            } else {
                $error .= "<div class='text-danger'>Qualification / Degree / Certificate must be select</div>";
            }

            if (isset($_POST['check_grade'])) {
                if ($_POST['check_grade'] == 'grade') {
                    $GRADING_AS = "G";
                    if (isset($_POST['grade']) && isValidData($_POST['grade'])) {
                        $grade = isValidData($_POST['grade']);
                    } else {
                    //    $error .= "<div class='text-danger'>Grade must be select</div>";
                    }
                } else if ($_POST['check_grade'] == 'cgpa') {
                    $GRADING_AS = "C";
                    if (isset($_POST['cgpa']) && isValidData($_POST['cgpa'])) {
                        $cgpa = isValidData($_POST['cgpa']);
                    } else {
                       // $error .= "<div class='text-danger'>CGPA Must Enter</div>";
                    }
                    if (isset($_POST['out_of']) && isValidData($_POST['out_of'])) {
                        $out_of = isValidData($_POST['out_of']);
                    } else {
                      //  $error .= "<div class='text-danger'>Out Of must select</div>";
                    }

                    if (!is_numeric($cgpa)) {
                      //  $error .= "<div class='text-danger'> Must Enter Valid CGPA / Percentage</div>";
                    } else {
                        $cgpa = number_format((float)$cgpa, 2, '.', '');
                        if ($cgpa > $out_of) {
                          //  $error .= "<div class='text-danger'> Must Enter Valid CGPA / Percentage</div>";
                        }
                    }


                }
            }
            if (!isset($_POST['result_not_declare'])) {
                $IS_DECLARE = 'Y';
                if (isset($_POST['RESULT_DATE']) && isValidTimeDate($_POST['RESULT_DATE'], 'd/m/Y')) {
                    $RESULT_DATE = getDateForDatabase($_POST['RESULT_DATE']);
                } else {
                   // $error .= "<div class='text-danger'>Result Declare Date must be Filled</div>";
                }

            }


            if (isset($_POST['INSTITUTE_ID']) && isValidData($_POST['INSTITUTE_ID'])) {
                $INSTITUTE_ID = isValidData($_POST['INSTITUTE_ID']);
                if ($INSTITUTE_ID <= 0) {
                 //   $error .= "<div class='text-danger'>Institute / Department / School / College must be select</div>";
                }
            } else {
               // $error .= "<div class='text-danger'>Institute / Department / School / College must be select</div>";
            }
            if (isset($_POST['ORGANIZATION_ID']) && isValidData($_POST['ORGANIZATION_ID'])) {
                $ORGANIZATION_ID = isValidData($_POST['ORGANIZATION_ID']);
            } else {
                $error .= "<div class='text-danger'>Organization / University / Board must be select</div>";
            }
            if (isset($_POST['DISCIPLINE_ID']) && isValidData($_POST['DISCIPLINE_ID'])) {
                $DISCIPLINE_ID = isValidData($_POST['DISCIPLINE_ID']);
                if ($DISCIPLINE_ID <= 0) {
                    $error .= "<div class='text-danger'>Discipline / Subject / Group must be select</div>";
                }
            } else {
                $error .= "<div class='text-danger'>Discipline / Subject / Group must be select</div>";
            }
            if (isset($_POST['ROLL_NO']) && isValidData($_POST['ROLL_NO'])) {
                $ROLL_NO = isValidData($_POST['ROLL_NO']);
            } else {
                $error .= "<div class='text-danger'>Roll Number must be Filled</div>";
            }
            if (isset($_POST['TOTAL_MARKS']) && isValidData($_POST['TOTAL_MARKS'])) {
                $TOTAL_MARKS = isValidData($_POST['TOTAL_MARKS']);
                if($TOTAL_MARKS<100){
                    $error .= "<div class='text-danger'>Total Marks must be Filled</div>";
                }
            } else {
                if ($DEGREE_ID != 8)
                    $error .= "<div class='text-danger'>Total Marks must be Filled</div>";
            }
            if (isset($_POST['OBTAINED_MARKS']) && isValidData($_POST['OBTAINED_MARKS'])) {
                $OBTAINED_MARKS = isValidData($_POST['OBTAINED_MARKS']);
            } else {
                if ($DEGREE_ID != 8)
                    $error .= "<div class='text-danger'>Obtained Marks must be Filled</div>";
            }
            if (isset($_POST['PASSING_YEAR']) && isValidData($_POST['PASSING_YEAR'])) {
                $PASSING_YEAR = isValidData($_POST['PASSING_YEAR']);
            } else {

                $error .= "<div class='text-danger'>Passing Year Must be Select</div>";
            }

            if (isset($_POST['START_DATE']) && isValidTimeDate($_POST['START_DATE'], 'd/m/Y')) {
                $START_DATE = getDateForDatabase($_POST['START_DATE']);
            } else {
                //$error .= "<div class='text-danger'>Start Date must be Filled</div>";
            }
            if (isset($_POST['END_DATE']) && isValidTimeDate($_POST['END_DATE'], 'd/m/Y')) {
                $END_DATE = getDateForDatabase($_POST['END_DATE']);
            } else {
              //  $error .= "<div class='text-danger'>End Date must be Filled</div>";
            }
            if ($END_DATE <= $START_DATE) {
                //$error .= "<div class='text-danger'>Invalid Date </div>";
            }


            if ($DEGREE_ID != 8 && $TOTAL_MARKS <= 0) {
                $error .= "<div class='text-danger'>Invalid Total Marks </div>";
            }
            if ($DEGREE_ID != 8 && $OBTAINED_MARKS <= 0) {
                $error .= "<div class='text-danger'>Invalid Obtained Marks </div>";
            }
            if ($DEGREE_ID == 8 && $TOTAL_MARKS < 0) {
                $error .= "<div class='text-danger'>Invalid Total Marks </div>";
            }
            if ($DEGREE_ID == 8 && $OBTAINED_MARKS < 0) {
                $error .= "<div class='text-danger'>Invalid Obtained Marks </div>";
            }


            if ($TOTAL_MARKS < $OBTAINED_MARKS) {
                $error .= "<div class='text-danger'>Obtained Marks must be less then or Equal to Total Marks </div>";
            }


            $degree_name = $this->Api_qualification_model->getDegreeProgramById($DEGREE_ID);
            $degree_name = str_replace(' ', '_', $degree_name['DEGREE_TITLE']);
            $degree_name = str_replace('/', '_', $degree_name);
            $config_a = array();
            $config_a['maintain_ratio'] = true;
            $config_a['width'] = 360;
            $config_a['height'] = 500;

            $config_a['resize'] = false;

            $marksheet_image = $qualification['MARKSHEET_IMAGE'];

            if ($marksheet_image)
                $marksheet_image = EXTRA_IMAGE_CHECK_PATH . $marksheet_image;
            $marks_check = true;
            if (!(!empty($marksheet_image) && file_exists($marksheet_image) && (filesize($marksheet_image) > 0))) {

                $marksheet_image = "";
                $qualification['MARKSHEET_IMAGE'] = "";
                $marks_check = false;
            }else{
                $marksheet_image = $qualification['MARKSHEET_IMAGE'];
            }

            $passcertificate_image = $qualification['PASSCERTIFICATE_IMAGE'];

            if ($passcertificate_image)
                $passcertificate_image = EXTRA_IMAGE_CHECK_PATH . $passcertificate_image;

            $pass_check = true;
            if (!(!empty($passcertificate_image) && file_exists($passcertificate_image) && (filesize($passcertificate_image) > 0))) {

                $passcertificate_image = "";
                $qualification['PASSCERTIFICATE_IMAGE'] = "";
                $pass_check = false;
            }else{
                $passcertificate_image = $qualification['PASSCERTIFICATE_IMAGE'];
            }

            if (isset($_FILES['marksheet_image'])) {
                if (isValidData($_FILES['marksheet_image']['name'])) {

                    $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                    $image_name = $degree_name . "_marksheet_image_$USER_ID"."_".$APPLICATION_ID;
                    $res = $this->upload_image('marksheet_image', $image_name, $this->file_size, $file_path, $config_a);
                    if ($res['STATUS'] === true) {
                        $marksheet_image = "$USER_ID/" . $res['IMAGE_NAME'];

                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                    if ($DEGREE_ID != 8 && !$marks_check) {
                        $error .= "<div class='text-danger'>Must Upload Marksheet and image size must be less then 500kb </div>";
                    }

                }
            } else {
                if ($DEGREE_ID != 8 && !$marks_check)
                    $error .= "<div class='text-danger'>Must Upload Marksheet and image size must be less then 500kb Id Not found something went worng </div>";
            }

            if (isset($_FILES['passcertificate_image'])) {
                if (isValidData($_FILES['passcertificate_image']['name'])) {

                    $file_path = EXTRA_IMAGE_CHECK_PATH . "$USER_ID/";
                    $image_name = $degree_name . "_passcertificate_image_$USER_ID"."_".$APPLICATION_ID;
                    $res = $this->upload_image('passcertificate_image', $image_name, $this->file_size, $file_path, $config_a);
                    if ($res['STATUS'] === true) {
                        $passcertificate_image = "$USER_ID/" . $res['IMAGE_NAME'];
                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                    if (!($DEGREE_ID==8 || $DEGREE_ID==10) && !$pass_check)
                        $error .= "<div class='text-danger'>Must Upload Pass Certificate and image size must be less then 500kb </div>";
                }

            } else {
                
                if (!($DEGREE_ID==8 || $DEGREE_ID==10) && !$pass_check)
                    $error .= "<div class='text-danger'>Must Upload Pass Certificate and image size must be less then 500kb Id Not found something went worng </div>";
            }

            // $if_exist = $this->Api_qualification_model->getQualificatinByUserIdAndDegreeId($USER_ID, $DEGREE_ID);
            // if (!(count($if_exist) == 1 && $DEGREE_ID == $qualification['DEGREE_ID'])) {
            //     $error .= "<div class='text-danger'>Same Qualification is Already Exist OR You Can't change Degree at this stage. Please Update OR Delete...!</div>";
            // }
        }
        if($error==""){



            $form_array = array(
                "USER_ID"           =>  $USER_ID,
                "DISCIPLINE_ID"     =>  $DISCIPLINE_ID,
                "ORGANIZATION_ID"   =>  $ORGANIZATION_ID,
                "INSTITUTE_ID"      =>  $INSTITUTE_ID,
                "START_DATE"        =>  $START_DATE,
                "END_DATE"          =>  $END_DATE,
                "RESULT_DATE"       =>  $RESULT_DATE,
                "TOTAL_MARKS"       =>  $TOTAL_MARKS,
                "OBTAINED_MARKS"    =>  $OBTAINED_MARKS,
                "CGPA"              =>  $cgpa,
                "GRADING_AS"        =>  $GRADING_AS,
                "GRADE"             =>  $grade,
                "ACTIVE"            =>  1,
                "IS_RESULT_DECLARE" =>  $IS_DECLARE,
                "ROLL_NO"           =>  $ROLL_NO,
                "OUT_OF"            =>  $out_of,
                "MARKSHEET_IMAGE"   =>  $marksheet_image,
                "PASSCERTIFICATE_IMAGE"=>  $passcertificate_image,
                "PASSING_YEAR"=>  $PASSING_YEAR
            );

            $res = $this->Api_qualification_model->updateQualification($QUAL_ID,$form_array,$this->APPLICATION_ID);


            if($res===1){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Save ..!<div>";
            }else if($res===0){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>No data has been changed..!<div>";
            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-danger'>Something went worng..!</div>";
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
        }
        else{
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($reponse));
        }
    }

    function apiDeleteQualification(){
        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];



        $qul_id = $this->input->post('qualification_id');

        if(is_numeric($qul_id)) {
            $res = $this->Api_qualification_model->deleteQualification($USER_ID,$qul_id,$this->APPLICATION_ID);
            if($res ===true){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Successfully Deleted Qualification...!</div>";
            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-success'>Somethis went wrong may be your not authorize to delete your qualification...!</div>";
            }
        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "<div class='text-success'>Invalid Qualification ID...!</div>";
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

    function apiGetInstituteByOrgId(){
        $user = $this->session->userdata($this->SessionName);


        $ORG_ID = $this->input->get('ORG_ID');


        if($ORG_ID){
            $institutes = $this->Api_qualification_model->getInstituteByOrgId($ORG_ID);
            echo "<option value='0'>--Choose--</option>";
            echo "<option value='-1'>--Add School / College--</option>";
            foreach ($institutes as $institute) {

                echo "<option value='{$institute['INSTITUTE_ID']}'  >{$institute['INSTITUTE_NAME']}</option>";
            }
            
        }else{
            echo "<option value='0'>--Choose--</option>";
        }


    }

    function apiGetOrganization(){
        $user = $this->session->userdata($this->SessionName);

            $organization = $this->Api_qualification_model->getAllOrganization();
            echo "<option value='0'>--Choose--</option>";
            foreach ($organization as $institute) {

                echo "<option value='{$institute['INSTITUTE_ID']}'  >{$institute['INSTITUTE_NAME']}</option>";
            }
            echo "<option value='-1'>--Other--</option>";


    }

    function apiGetDisciplineById(){
        $user = $this->session->userdata($this->SessionName);


        $DEGREE_ID = $this->input->get('DEGREE_ID');


        if($DEGREE_ID){
            $disciplines = $this->Api_qualification_model->getDisciplineByDegreeId($DEGREE_ID);
            echo "<option value='0'>--Choose--</option>";


            foreach ($disciplines as $discipline) {

                echo "<option value='{$discipline['DISCIPLINE_ID']}'  >{$discipline['DISCIPLINE_NAME']}</option>";
            }

        }else{
            echo "<option value='0'>--Choose--</option>";
        }


    }

    function addOrganizationForQualification(){

        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];
        $org_name = strtoupper(isValidData($this->input->post('org_name')));
        if(!empty($org_name)){
            $DATE =date('Y-m-d H:i:s');
            $type_id = 1;
            $PARENT_ID = 0;
            $active = 0;
            $IS_INST = 'Y';
            $remarks = 'ADDED_BY_NORMAL_USER';
            $form_array = array('INSTITUTE_NAME'=>$org_name ,
            'INSTITUTE_TYPE_ID'=>$type_id,
            'PARENT_ID'=>$PARENT_ID,
            'REMARKS'=>$remarks ,
            'USER_ID'=> $USER_ID,
            'DATE_TIME'=> $DATE,
            'ACTIVE'=> $active,
            'IS_INST'=> $IS_INST);
            $res = $this->Api_qualification_model->addInstitute($form_array);
            if($res===true){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Organization Add Success Fully...!</div>";
            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-success'>Something Went Worng...!</div>";
            }

        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "<div class='text-danger'>Organization Name Must be Provide...!</div>";
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

    function addInstituteForQualification(){
        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];

        $institute = strtoupper(isValidData($this->input->post('institute')));
        $PARENT_ID = isValidData($this->input->post('org_id'));


        if($institute&&$PARENT_ID){

            $DATE =date('Y-m-d H:i:s');
            $type_id = 2;
            $active = 0;
            $IS_INST = 'N';
            $remarks = 'ADDED_BY_NORMAL_USER';

            $form_array = array('INSTITUTE_NAME'=>$institute ,
                'INSTITUTE_TYPE_ID'=>$type_id,
                'PARENT_ID'=>$PARENT_ID,
                'REMARKS'=>$remarks ,
                'USER_ID'=> $USER_ID,
                'DATE_TIME'=> $DATE,
                'ACTIVE'=> $active,
                'IS_INST'=> $IS_INST);

            $res = $this->Api_qualification_model->addInstitute($form_array);

            if($res===true){
                $reponse['RESPONSE'] = "SUCCESS";
                $reponse['MESSAGE'] = "<div class='text-success'>Institute / Department / School / College ...!</div>";
            }else{
                $reponse['RESPONSE'] = "ERROR";
                $reponse['MESSAGE'] = "<div class='text-success'>Something Went Worng...!</div>";
            }

        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "<div class='text-danger'>Organization must be select and Institute Must be Provided...!</div>";
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
comment this method on 5-nov-2020
    public function select_category(){

        if($this->session->has_userdata('APPLICATION_ID')) {
            $APPLICATION_ID = $this->session->userdata('APPLICATION_ID');

            $user = $this->session->userdata($this->SessionName);
            $user = $this->User_model->getUserById($user['USER_ID']);

            $data['user'] = $user;
            $data['APPLICATION_ID'] = $APPLICATION_ID;
            $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $APPLICATION_ID);

            if ($application) {

                $form_data = $this->User_model->getUserFullDetailById($user['USER_ID']);

                $degree_list = array(
                    'BACHELOR'=>array('PROGRAM_TYPE_ID'=>1,'DEGREE_ID'=>3),
                    'MASTER'=>array('PROGRAM_TYPE_ID'=>2,'DEGREE_ID'=>4)
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
                    //echo "master";
                    //4
                    // prePrint($form_data['qualifications']);
                    foreach ($form_data['qualifications'] as $qualification){
                        if($qualification['DEGREE_ID'] ==$degree_list['MASTER']['DEGREE_ID']){
                            $bool  = true;
                            $valid_qualification = $qualification;
                            break;
                        }
                    }
                }
                //$from_data = json_encode($from_data);


                $form_fees = $this->Admission_session_model->getFormFeesBySessionAndCampusId($application['SESSION_ID'], $application['CAMPUS_ID']);

                if ($form_fees) {
                    $valid_upto = getDateCustomeView($application['ADMISSION_END_DATE'], 'd-m-Y');

                    if ($application['ADMISSION_END_DATE'] < date('Y-m-d')) {
                        exit("Sorry your challan is expired..");
                    }


                    $data['profile_url'] = base_url() . $this->profile;
//                    $data['is_valid_qualification'] = $bool;
//                    $data['form_data'] = $form_data;
                    //$data['application'] = $application;
                    if($bool&&$valid_qualification!=null){

                        //  $result = $this->Application_model->getMinorMappingByDisciplineId($valid_qualification['DISCIPLINE_ID']);



                        $data['DISCIPLINE_ID'] = $valid_qualification['DISCIPLINE_ID'];
                        

                        $formcategory = $this->Administration->getCategory();
						$data['formcategory'] = $formcategory;
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
    */
    public function upload_profile_image(){
        $user = $this->session->userdata($this->SessionName);

        $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $this->APPLICATION_ID);
        if($application){
            $form_staus = json_decode($application['FORM_STATUS'],true);
        }else{
            $error = "<div class='text-danger'> You are not authorized to change profile picture</div>";
            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/dashboard");
            exit();
        }

        if(isset($form_staus['PROFILE_PHOTO'])&&isset($form_staus['PROFILE_PHOTO']['STATUS'])&&$form_staus['PROFILE_PHOTO']['STATUS']==RE_UPLOAD){

            $data['user'] = $user;
            $data['profile_url'] = $this->profile;
            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('profile_section/upload_profile_picture',$data);
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }else{
            $error = "<div class='text-danger'> You are not authorized to change profile picture</div>";
            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/dashboard");
            exit();
        }

    }
    
    public function upload_profile_image_handler(){
        $user = $this->session->userdata($this->SessionName);

        $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $this->APPLICATION_ID);
        if($application){
            $form_staus = json_decode($application['FORM_STATUS'],true);
        }else{
            $error = "<div class='text-danger'> You are not authorized to change profile picture</div>";
            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/dashboard");
            exit();
        }

        if(isset($form_staus['PROFILE_PHOTO'])&&isset($form_staus['PROFILE_PHOTO']['STATUS'])&&$form_staus['PROFILE_PHOTO']['STATUS']==RE_UPLOAD){

            $error = "";
            $PROFILE_IMAGE="";
            if (isset($_FILES['profile_image'])) {
                // prePrint($_FILES['profile_image'][]);
                if (isValidData($_FILES['profile_image']['name'])) {

                    $res = $this->upload_image('profile_image', "profile_image_" . $this->user['USER_ID']);
                    if ($res['STATUS'] === true) {
                        $PROFILE_IMAGE = $res['IMAGE_NAME'];

                    } else {
                        $error .= "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                    }
                } else {
                    if ($user['PROFILE_IMAGE']) {
                        $PROFILE_IMAGE = $user['PROFILE_IMAGE'];
                    } else {
                        $error .= "<div class='text-danger'>Must Upload Profile Picture</div>";
                    }
                }
            } else {
                if ($user['PROFILE_IMAGE']) {
                    $PROFILE_IMAGE = $user['PROFILE_IMAGE'];

                } else {
                    $error .= "<div class='text-danger'>Must Upload Profile Picture</div>";
                }
            }

            if($error==""){

                $form_array = array("PROFILE_IMAGE"=>$PROFILE_IMAGE);
                $res = $this->User_model->updateUserById($user['USER_ID'],$form_array);

                //$user_data = $this->User_model->getUserFullDetailById($user['USER_ID']);
                  $user_data = $this->User_model->getUserFullDetailWithChoiceById($user['USER_ID'],$this->APPLICATION_ID,$SHIFT_ID=1);
                $user_data = json_encode($user_data);

                $form_staus['PROFILE_PHOTO']['STATUS'] = "PENNDING VERIFICATION";
                $form_staus=json_encode($form_staus);

                $form_array= array("USER_ID"=>$user['USER_ID'],"FORM_STATUS"=>$form_staus,'IS_PROFILE_PHOTO_VERIFIED'=>0,"FORM_DATA"=>"");

                $res1 =$this->Application_model->updateApplicationById($this->APPLICATION_ID,$form_array);



                if($res===-1||$res1==-1){
                    $error = "<div class='text-danger'>Something went worng..!</div>";
                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."Candidate/upload_profile_image");
                    exit();
                }
                if($res===0&&$res1==0){
                    $error = "<div class='text-success'>No data has been changed..!<div>";

                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/dashboard");
                    exit();
                }else{
                    $error = "<div class='text-success'>No data has been changed..!<div>";

                    $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                    $this->session->set_flashdata('ALERT_MSG',$alert);
                    redirect(base_url()."form/dashboard");
                    exit();
                }

            }else{

                $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                $this->session->set_flashdata('ALERT_MSG',$alert);
                redirect(base_url()."Candidate/upload_profile_image");
                exit();
            }

        }else{
            $error = "<div class='text-danger'> You are not authorized to change profile picture</div>";
            $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
            $this->session->set_flashdata('ALERT_MSG',$alert);
            redirect(base_url()."form/dashboard");
            exit();
        }

    }
    
    //added this method on 5-nov-2020
    //must update views/profile_section/edit_qualification_from.php
    //must update views/profile_section/qualification_from.php
    //must update views/profile_section/qualification_information.php
    public function add_inter_qualification(){

        $user = $this->session->userdata($this->SessionName);

        $application = $this->Application_model->getApplicationByUserAndApplicationId($user['USER_ID'], $this->APPLICATION_ID);
        //   prePrint($application);
        // exit();
//        $countries =$this->Api_location_model->getAllCountry();
//
//        $prefixs = $this->Configuration_model->getPrefix();
//        $prefixs = json_decode($prefixs['VALUE'],true);
//        $blood_groups=array("A+","A-","B+","B-","O+","O-","AB+","AB-");
//        $GENDER=array('M'=>"MALE","F"=>"FEMALE");
//        $area=array('R'=>"RURAL","U"=>"URBAN");
//        $RELIGIONS=array("ISLAM","HINDUISM","CHRISTIAN","OTHER");
//        $REL_GUARD=array('FATHER'=>"FATHER","MOTHER"=>"MOTHER","BROTHER"=>"BROTHER","SISTER"=>"SISTER","UNCLE"=>"UNCLE","AUNTY"=>"AUNTY","GRAND FATHER"=>"GRAND FATHER","GRAND MOTHER"=>"GRAND MOTHER","OTHER"=>"OTHER");
//        $OCC_GUARD=array('BUSSINESS MAN'=>"BUSSINESS MAN","ENGINEER"=>"ENGINEER","DOCTOR"=>"DOCTOR","FARMER"=>"FARMER","GOVERMENT EMPLOYEE"=>"GOVERMENT EMPLOYEE","PRIVATE COMPANY EMPLOYEE"=>"PRIVATE COMPANY EMPLOYEE","LANDLOARD"=>"LANDLOARD","RETIRED"=>"RETIRED","OTHER"=>"OTHER");
        if($application){

        if($application['PROGRAM_TYPE_ID']>=2){
             
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

        }else{
            redirect(base_url().$this->LoginController);
            exit();
        }
        
        $user = $this->User_model->getUserById($user['USER_ID']);
        if($user){
// prePrint($user['USER_ID']);
// exit();
            //    $user = $this->User_model->getUserById($user['USER_ID']);
            // prePrint($user);
         //   $family_info  =  $this->User_model->getGuardianByUserId($user['USER_ID']);
            $data['user'] = $user;
            $data['profile_url'] = $this->profile;
//            $data['countries'] = $countries;
//            $data['prefixs'] = $prefixs;
//            $data['blood_groups'] = $blood_groups;
//            $data['family_info'] = $family_info;
//            $data['REL_GUARD'] = $REL_GUARD;
//            $data['OCC_GUARD'] = $OCC_GUARD;
//            $data['GENDER'] = $GENDER;
//            $data['area'] = $area;
//            $data['RELIGIONS'] = $RELIGIONS;
            $data['application'] = $application;



            $this->load->view('include/header',$data);
            $this->load->view('include/preloder');
            $this->load->view('include/side_bar',$data);
            $this->load->view('include/nav',$data);
            $this->load->view('add_inter_qualification',$data);
            $this->load->view('include/footer_area',$data);
            $this->load->view('include/footer',$data);
        }else{
            redirect(base_url().$this->LoginController);
            exit();
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


                
            $this->load->library('ftp');
            
            // $config['hostname'] = 'ftp://itsc.usindh.edu.pk';
            //  $config['username'] = 'itsc';
            // $config['password'] = 'imcs2468**&&';
            // $config['debug']        = true;
            
            // $connect = $this->ftp->connect($config);
            // // if($connect){
            // //   exit("YES"); 
            // // }else{
            // //     exit("FALSE");
            // // }
            
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
            //$this->CI_ftp($path,$image_data['file_name']);
            
           // exit("YES");
            return array("STATUS"=>true,"IMAGE_NAME"=>$image_data['file_name']);

        }
    }

    private function templateMethod(){

        $reponse = getcsrf($this);
        $USER_ID = $this->user['USER_ID'];
        if ($this->input->server('REQUEST_METHOD') == 'POST'){

            $reponse['RESPONSE'] = "SUCCESS";
            $reponse['MESSAGE'] = "OK";
        }else{
            $reponse['RESPONSE'] = "ERROR";
            $reponse['MESSAGE'] = "Invalid Request Method";
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
  
     private function close_registration_for_bachelor($application){
                     if ($application['ADMISSION_END_DATE'] < date('Y-m-d')&&$this->user['IS_SUPER_PASSWORD_LOGIN'] == 'N') {
                    //     $error = "<div class='text-danger'>Online Admission Form Process is CLOSED for {$application['PROGRAM_TITLE']} Degree Programs {$application['YEAR']}</div>";
                    //   $alert = array('MSG'=>$error,'TYPE'=>'ALERT');
                    //             $this->session->set_flashdata('ALERT_MSG',$alert);
                    //             redirect(base_url()."form/dashboard");
                    //             exit();
                    }
    }

}
