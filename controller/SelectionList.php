<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 12/15/2020
 * Time: 1:57 PM
 */


defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';
class SelectionList extends AdminLogin
{
    private $script_name = "";
    private $pre_requiste_list = null;
    private $pre_req_log = null;
    private $minor_maping_list = null;
    private $check_current_choice_no = null;


    public function __construct(){
        parent::__construct();

        set_time_limit(1500);
        ini_set('memory_limit', '-1');

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
        $this->load->model('ForSpecailSelf_model');
        $this->load->model('Selection_list_report_model');
        $this->load->model("AdmitCard_model");
            $this->load->model("ForEvening_model");
//		$this->load->library('javascript');
        $self = $_SERVER['PHP_SELF'];
        $self = explode('index.php/', $self);
        $this->script_name = $self[1];
        $this->verify_login();
        $this->date = date("d_m_y_h_i_s_A");

        //echo "yes";
    }
   
    public function getAdmissionList(){

        if(isset($_POST['CAMPUS_ID'])&&(isset($_POST['YEAR'])||isset($_POST['SESSION_ID']))&&isset($_POST['PROG_TYPE_ID'])&&isset($_POST['SHIFT_ID'])){

            if(isset($_POST['SESSION_ID'])){
                $session_id =$_POST['SESSION_ID'];
            }else{
                
            $session = $this->Admission_session_model->getSessionByYearData($_POST['YEAR']);
            $session_id =$session['SESSION_ID'] ;
            }

            $campus_id = $_POST['CAMPUS_ID'];
            $SHIFT_ID = $_POST['SHIFT_ID'];
            
            $prog_type_id = $_POST['PROG_TYPE_ID'];
            $admission_session_obj = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$prog_type_id);
            $admission_session_id = $admission_session_obj['ADMISSION_SESSION_ID'];
            $result = $this->Selection_list_report_model->get_admission_list_no($admission_session_id,$SHIFT_ID);
            echo json_encode($result);

        }else{
            echo "[]";
        }


    }
    
    public function generateFirstList(){
        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);


        $data['test_year'] =$this->TestResult_model->getTestTypeYear();
        $data['campus'] =$this->Administration->getCampus();
        $data['side_bar_values'] = $side_bar_data;


        $data['user'] = $user;
        $data['profile_url'] = $user['PROFILE_IMAGE'];
        $this->load->view('include/header',$data);
        $this->load->view('include/preloder');
        $this->load->view('include/side_bar',$data);
        $this->load->view('include/nav',$data);
        $this->load->view('admin/generate_first_merit_list');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);
    }
    
    public function generateList(){
       // prePrint($_POST);
        if(isset($_POST['PROG_LIST_ID'])&&count($_POST['PROG_LIST_ID'])&&isset($_POST['PROG_TYPE_ID'])&&isset($_POST['SHIFT_ID'])&&isset($_POST['YEAR'])&&isset($_POST['TEST_ID'])&&isset($_POST['CAMPUS_ID'])&&isset($_POST['ADMISSION_LIST_ID'])&&isset($_POST['IS_PROVISIONAL'])) {
           // $PROG_LIST_IDS = $_POST['PROG_LIST_ID'];
            if($_POST['SHIFT_ID'] == MORNING_SHIFT_ID){
                if(isset($_POST['IS_SPECAIL_SELF'])&&$_POST['IS_SPECAIL_SELF']=='N'){
                    $this->generateMeritList();
                }else if(isset($_POST['IS_SPECAIL_SELF'])&&$_POST['IS_SPECAIL_SELF']=='Y'){
                    $this->ForSpecailSelfGenerateMeritList();
                }
            }else if($_POST['SHIFT_ID'] == EVENING_SHIFT_ID){
                $this->ForEveningGenerateMeritList();
            }

        }
        else{
            redirect(base_url('SelectionList/generateFirstList'));
        }
    }
    
    public function generateMeritList(){


        if(isset($_POST['PROG_LIST_ID'])&&count($_POST['PROG_LIST_ID'])&&isset($_POST['PROG_TYPE_ID'])&&isset($_POST['SHIFT_ID'])&&isset($_POST['YEAR'])&&isset($_POST['TEST_ID'])&&isset($_POST['CAMPUS_ID'])){
            $this->pre_req_log = fopen("merit_list/error_log_for_prereq".$this->date.".csv", "w") or die("Unable to open file!");

            $session = $this->Admission_session_model->getSessionByYearData($_POST['YEAR']);
            $PROG_LIST_IDS = $_POST['PROG_LIST_ID'];
            $TEST_ID = $_POST['TEST_ID'];
            $campus_id = $_POST['CAMPUS_ID'];
            $shift_id = $_POST['SHIFT_ID'];
            $session_id =$session['SESSION_ID'] ;
            $prog_type_id = $_POST['PROG_TYPE_ID'];
            $ADMISSION_LIST_ID = $_POST['ADMISSION_LIST_ID'];
			$admission_list =  $this->Selection_list_report_model->get_admission_list_no_by_id($ADMISSION_LIST_ID);
            $first_merit_list = $admission_list['LIST_NO'];
            $merit_list_name = merit_list_decode($first_merit_list);
            $is_provisional = $_POST['IS_PROVISIONAL'];
            $admission_session_obj = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$prog_type_id);
            $admission_session_id = $admission_session_obj['ADMISSION_SESSION_ID'];
            $campus_name = $admission_session_obj['NAME'];

            prePrint("Start Time".date("d-m-y h:i:s A"));

            $pre_requisite_list = $this->Prerequisite_model->getPrerequisiteByProgramTypeId($prog_type_id);
            $minor_maping_list =  $this->Administration->getMinorMapping();

            $minor_maping_list_array = array();

            foreach ($minor_maping_list as $minor_maping){
                if(!isset($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']])||!is_array($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']])){
                    $minor_maping_list_array[$minor_maping['DISCIPLINE_ID']] = array();
                }
                array_push($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']],$minor_maping);
            }

            $this->minor_maping_list = $minor_maping_list_array;


            $pre_requisite_list_array = array();

            foreach ($pre_requisite_list as $pre_req){
                if(!isset($pre_requisite_list_array[$pre_req['PROG_LIST_ID']])||!is_array($pre_requisite_list_array[$pre_req['PROG_LIST_ID']])){
                    $pre_requisite_list_array[$pre_req['PROG_LIST_ID']] = array();
                }
                array_push($pre_requisite_list_array[$pre_req['PROG_LIST_ID']],$pre_req);
            }

            $this->pre_requiste_list = $pre_requisite_list_array;



            $campus_jurisdiction_list = $this->Administration->getMappedCampusJurisdiction($campus_id);

            // prePrint($campus_jurisdiction_list);
            //  exit();

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $candidates_fee_ledger = $this->MeritList_model->getFeeLedger($admission_session_id,$shift_id,$session_id,$prog_type_id,$TEST_ID);
            //  prePrint($candidates_fee_ledger);
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $discipline_seat_distribution = $this->MeritList_model->getSeatDistribution($campus_id,$shift_id,$session_id,$prog_type_id,$PROG_LIST_IDS);
//            prePrint($discipline_seat_distribution);
//            exit();
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $all_selected_student = $this->MeritList_model->getSelectedStudent($admission_session_id,$shift_id,$session_id,$prog_type_id,$TEST_ID);
            //prePrint($all_selected_student);
            $prev_selected_list = array();
            
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $this->filter_candidate($prev_selected_list,$all_selected_student,$candidates_fee_ledger,$discipline_seat_distribution);
            
             //prePrint($discipline_seat_distribution);
             //exit();

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));


            $all_candidate_results_sort = $this->TestResult_model->getListOfStudentByTestIdAndCampusIdAndShiftId($TEST_ID,$campus_id,$shift_id);

            prePrint("after sorting Time".date("d-m-y h:i:s A"));
            prePrint("ALL STUDENT after sorting " . count($all_candidate_results_sort));

            $selected_candidate = array();
            $not_selected_candidate = array();
            


            $this->getDepartmentNextMeritList($all_candidate_results_sort,$discipline_seat_distribution,$selected_candidate,$not_selected_candidate,$campus_jurisdiction_list,$campus_id,$prog_type_id,$prev_selected_list);
            prePrint("End Time department merit list".date("d-m-y h:i:s A"));

            $myfile  = fopen("merit_list/SELECTED-CANDIDATE-$merit_list_name-LIST-$campus_name-".date("d_m_y_h_i_s_A").".csv",'w+');
            $txt = "CARD_ID,APPLICATION_ID,CPN,TEST_SCORE,USER_ID,CNIC_NO,FIRST_NAME,LAST_NAME,FNAME,GENDER,U_R,DISTRICT_NAME,CATEGORY_NAME,PROGRAM_TITLE,CHOICE_NO,CAMPUS_NAME,PROG_LIST_ID,STATUS,SSC_OBT,SSC_TOT,SSC_GRP,SSC_P_YEAR,SSC_BORAD,HSC_OBT,HSC_TOT,HSC_GRP,HSC_P_YEAR,HSC_BORAD,GRD_OBT,GRD_TOT,GRd_GRP,GRD_P_YEAR,GRD_BORAD,IS_PROMOTED,MOBILE_NO,EMAIL\n";
            fwrite($myfile, $txt);
            $form_array = array();

            foreach ($selected_candidate as $candidate){



                $candidate_info = $candidate['candidate'];
                //$form_data = json_decode($candidate_info['FORM_DATA'],true);
                $IS_PROMOTED = $candidate_info['IS_PROMOTED'];
                $users_reg = $candidate_info['users_reg'];
                $qualifications = $candidate_info['qualifications'];
                $inter = findObjectinList($qualifications,"DEGREE_ID",3);
                $metric = findObjectinList($qualifications,"DEGREE_ID",2);
                if($qualifications[0]['DEGREE_ID']!=10){
                     $qualification = $qualifications[0];
                 }else{
                    $qualification = $qualifications[1];

                 }
                $FIRST_NAME =  $users_reg['FIRST_NAME'];
                $LAST_NAME =  $users_reg['LAST_NAME'];
                $F_NAME =  $users_reg['FNAME'];
                $CNIC_NO =  $users_reg['CNIC_NO'];
                $MOBILE_NO =  "0".$users_reg['MOBILE_NO'];
                $EMAIL =  $users_reg['EMAIL'];
                $GENDER =  $users_reg['GENDER'];
                $U_R =  $users_reg['U_R'];
                $DISTRICT_NAME =  $users_reg['DISTRICT_NAME'];
                $CATEGORY_NAME = $candidate['CATEGORY']['CATEGORY_NAME'];
                $PROGRAM_TITLE = $candidate['CATEGORY']['PROGRAM_TITLE'];
                $CAMPUS_NAME = $candidate['CATEGORY']['NAME'];
                $PROG_LIST_ID = $candidate['CATEGORY']['PROG_LIST_ID'];
                $CATEGORY_ID = $candidate['CATEGORY']['CATEGORY_ID'];
                $APPLICATION_ID = $candidate['APPLICATION_ID'];
                $USER_ID = $candidate['USER_ID'];
                $CPN = $candidate_info['CPN'];
                $TEST_SCORE = $candidate_info['TEST_SCORE'];
                $DETAIL_CPN = $candidate_info['DETAIL_CPN'];
                $CARD_ID = $candidate_info['CARD_ID'];
                $ADMISSION_SESSION_ID = $candidate_info['ADMISSION_SESSION_ID'];
                $STATUS_ID = $candidate_info['STATUS_ID'];
                $CHOICE_NO = $candidate['CHOICE_NO'];
                $DISTRICT_ID =  $users_reg['DISTRICT_ID'];
                $DIVISION_ID =  $users_reg['DIVISION_ID'];

                $inter_csv = "{$inter['OBTAINED_MARKS']},{$inter['TOTAL_MARKS']},{$inter['DISCIPLINE_NAME']},{$inter['PASSING_YEAR']},\"{$inter['ORGANIZATION']}\"";
                $metric_csv = "{$metric['OBTAINED_MARKS']},{$metric['TOTAL_MARKS']},{$metric['DISCIPLINE_NAME']},{$metric['PASSING_YEAR']},\"{$metric['ORGANIZATION']}\"";
                 $qualification_csv = "{$qualification['OBTAINED_MARKS']},{$qualification['TOTAL_MARKS']},{$qualification['DISCIPLINE_NAME']},{$qualification['PASSING_YEAR']},\"{$qualification['ORGANIZATION']}\"";


                $txt = "$CARD_ID,$APPLICATION_ID,$CPN,$TEST_SCORE,$USER_ID,$CNIC_NO,\"$FIRST_NAME\",\"$LAST_NAME\",\"$F_NAME\",$GENDER,$U_R,\"$DISTRICT_NAME\",\"$CATEGORY_NAME\",\"$PROGRAM_TITLE\",$CHOICE_NO,\"$CAMPUS_NAME\",$PROG_LIST_ID,$STATUS_ID,$metric_csv,$inter_csv,$qualification_csv,$IS_PROMOTED,$MOBILE_NO,\"$EMAIL\"\n";

                $DETAIL_CPN = "";
                $list = array(
                    "APPLICATION_ID"=>$APPLICATION_ID,
                    "TEST_ID"=>$TEST_ID,
                    "SHIFT_ID"=>$shift_id,
                    "CHOICE_NO"=>$CHOICE_NO,
                    "PROG_LIST_ID"=>$PROG_LIST_ID,
                    "CATEGORY_ID"=>$CATEGORY_ID,
                    "CARD_ID"=>$CARD_ID,
                    "ADMISSION_SESSION_ID"=>$ADMISSION_SESSION_ID,
                    "ADMISSION_LIST_ID"=>$ADMISSION_LIST_ID,
                    "CPN"=>$CPN,
                    "ACTIVE"=>1,
                    "IS_PROVISIONAL"=>$is_provisional,
        
                );
                $form_array[] = $list;
                fwrite($myfile, $txt);
            }
            fclose($myfile);

            $myfile  = fopen("merit_list/NOT-SELECTED-CANDIDATE-$merit_list_name-LIST-$campus_name-".date("d_m_y_h_i_s_A").".csv",'w+');
            $txt = "CARD_ID,APPLICATION_ID,CPN,TEST_SCORE,USER_ID,CNIC_NO,FIRST_NAME,LAST_NAME,FNAME,GENDER,U_R,DISTRICT_NAME,STATUS,PROGRAMS_CHOICE,CATEGORY\n";
            fwrite($myfile, $txt);
            foreach($not_selected_candidate as $candidate){
                //$form_data = json_decode($candidate['FORM_DATA'],true);

                $users_reg = $candidate['users_reg'];

                $FIRST_NAME =  $users_reg['FIRST_NAME'];
                $LAST_NAME =  $users_reg['LAST_NAME'];
                $F_NAME =  $users_reg['FNAME'];
                $CNIC_NO =  $users_reg['CNIC_NO'];
                $GENDER =  $users_reg['GENDER'];
                $U_R =  $users_reg['U_R'];
                $DISTRICT_NAME =  $users_reg['DISTRICT_NAME'];
                $STATUS_ID = $candidate['STATUS_ID'];
                $CPN = $candidate['CPN'];
                $TEST_SCORE =  $candidate['TEST_SCORE'];
                $APPLICATION_ID = $candidate['APPLICATION_ID'];
                $USER_ID = $candidate['USER_ID'];
                $CARD_ID = $candidate['CARD_ID'];
                $choices = "";
                foreach($candidate['application_choices'] as $choice){
                    $choices.=$choice['PROGRAM_TITLE'].",";
                }
                $categories="";
                foreach($candidate['application_category'] as $category){
                    $categories.=$category['FORM_CATEGORY_NAME'].",";
                }
                $txt = "$CARD_ID,$APPLICATION_ID,$CPN,$TEST_SCORE,$USER_ID,$CNIC_NO,\"$FIRST_NAME\",\"$LAST_NAME\",\"$F_NAME\",$GENDER,$U_R,\"$DISTRICT_NAME\",$STATUS_ID,\"$choices\",\"$categories\"\n";
                fwrite($myfile, $txt);
                //prePrint($candidate['application_choices']);

                //exit();
            }
            fclose($myfile);
            prePrint("end time putting data into xls file".date("d-m-y h:i:s A"));

            $query_result = $this->MeritList_model->addList($form_array);
            $query_result = true;
            prePrint(count($selected_candidate));
            prePrint("writing merit list in database".date("d-m-y h:i:s A"));
            if($query_result){
                echo "<h1>Successfully Insert record</h1>";
            }else{
                echo "<h1>Something went wrong in insert</h1>";
            }

        }else{
            exit("FORM IDs Not Found");
        }

    }
   
    public function getDepartmentNextMeritList(&$all_candidate_results,&$discipline_seat_distribution,&$selected_candidate,&$not_selected_candidate,$campus_jurisdiction_list,$campus_id=1,$prog_type_id=1,$prev_selected_list = array()){

        $error_log_seat_distribution= fopen("merit_list/error_log_seat_distribution ".date("d_m_y_h_i_s_A").".csv", "w") or die("Unable to open file!");
        foreach ($all_candidate_results as $i => $candidate ){
            $is_prev_self_selected = false;
            $pre_choice_no = 31;

            $CARD_ID = $candidate['CARD_ID'];
            $CPN = $candidate['CPN'];
            $candidate['IS_PROMOTED'] = 'N';
            $APPLICATION_ID = $candidate['APPLICATION_ID'];
           
            $prev_selected_program = null;
            $IS_SELF_DROP = false;
            if(isset($prev_selected_list[$APPLICATION_ID])){
                $prev_selected_program = $prev_selected_list[$APPLICATION_ID];

                if($prev_selected_program['IS_DROP']||$prev_selected_program['IS_RETAIN']){
                    continue;
                }
                else if($prev_selected_program['IS_SELF_DROP']){
                    $IS_SELF_DROP = true;
                    for($j = 0 ; $j<count($candidate['application_category']) ; $j++){
                        $f_cat_id = $candidate['application_category'][$j]['FORM_CATEGORY_ID'];
                        if($f_cat_id==SELF_FINANCE_FORM_CATEGORY_ID){
                            unset($candidate['application_category'][$j]);
                            break;
                        }
                    }
                   // prePrint($candidate);

                }
                else if($prev_selected_program['IS_SELF_RETAIN']){

                    $PROG_LIST_ID = $prev_selected_program['PRE_SELECTED']['PROG_LIST_ID'];

                    $retaind_program = findObjectinList($candidate['application_choices'],'PROG_LIST_ID',$PROG_LIST_ID);
                    $candidate['application_choices'] = array($retaind_program);

                    for($j = 0 ; $j<count($candidate['application_category']) ; $j++){

                        $f_cat_id = $candidate['application_category'][$j]['FORM_CATEGORY_ID'];
                        if($f_cat_id==SELF_FINANCE_FORM_CATEGORY_ID){
                            unset($candidate['application_category'][$j]);
                            break;
                        }
                    }

                }

                if($prev_selected_program['IS_SELF']&&$prev_selected_program['SELF_LAST_CHOICE']>0){

                    $pre_choice_no =$prev_selected_program['SELF_LAST_CHOICE'];
                    $is_prev_self_selected = true;
                    if($prev_selected_program['MERIT_LAST_CHOICE']!=-1){
                        $pre_choice_no_m =$prev_selected_program['MERIT_LAST_CHOICE'];
                        $current_choices = array();
                        foreach ($candidate['application_choices'] as $cur_choice){
                            if($cur_choice['CHOICE_NO']<$pre_choice_no_m){
                                $current_choices[]=$cur_choice;
                            }
                        }
                        $candidate['application_choices'] = $current_choices;
                    }
                }

                if($prev_selected_program['IS_MERIT']){
                    $pre_choice_no =$prev_selected_program['MERIT_LAST_CHOICE'];
                    $current_choices = array();
                    foreach ($candidate['application_choices'] as $cur_choice){
                        if($cur_choice['CHOICE_NO']<$pre_choice_no){
                            $current_choices[]=$cur_choice;
                        }
                    }
                    $candidate['application_choices'] = $current_choices;
                }
                $candidate['IS_PROMOTED'] = 'Y';

            }

            if($prev_selected_program&&!($prev_selected_program['IS_SELF']||$prev_selected_program['IS_MERIT'])){

             $prev_selected_program = null;

            }

            //$form_data = json_decode($candidate['FORM_DATA'],true);
            $users_reg = $candidate['users_reg'];
            $qualifications = $candidate['qualifications'];
            $DISTRICT_ID = $users_reg['DISTRICT_ID'];
            $DIVISION_ID = $users_reg['DIVISION_ID'];
            $PROVINCE_ID = $users_reg['PROVINCE_ID'];
            $FIRST_NAME = $users_reg['FIRST_NAME'];
            $GENDER = $users_reg['GENDER'];
			$RELIGION = $users_reg['RELIGION'];
            $U_R = $users_reg['U_R'];
            $CNIC_NO = $users_reg['CNIC_NO'];


            if(isset($candidate['applicants_minors'])&&isset($candidate['application_choices'])&&isset($candidate['application_category'])) {
                $applicants_minors =$candidate['applicants_minors'];
                $application_choices =$candidate['application_choices'];
                $application_category =$candidate['application_category'];
            }
            else{
                echo $candidate['APPLICATION_ID'].",";
                $application_category = array();
                $applicants_minors = array();
                $application_choices = array();
            }





            $is_seat_allot = false;
            $is_self_selected = false;



            foreach ($application_choices as $choice) {
                $choice_prog_list_id = $choice['PROG_LIST_ID'];
                $CHOICE_NO = $choice['CHOICE_NO'];
                $this->check_current_choice_no =$CHOICE_NO;
                if(!$this->checkPreRequsiteUpgrade($choice_prog_list_id,$candidate,$prog_type_id)){
                    // break;
                    continue;
                }
                
                //find the index of program in discipline_seat_distribution
                $choice_prog_list_index = getIndexOfObjectInList($discipline_seat_distribution,'PROG_LIST_ID',$choice_prog_list_id);
                if($choice_prog_list_index<0){
                    //prePrint($choice);
                    //echo "$CHOICE_NO -> NOT FOUND INDEX<br>";
                    ///$myfile = $this->pre_req_log;
                    $txt = "Seat Distribution not found ,$CNIC_NO,$choice_prog_list_id\n";
                    fwrite($error_log_seat_distribution, $txt);
                    continue;
                    //exit("NOT FOUND INDEX");
                }
                $CATEGORIES =  &$discipline_seat_distribution[$choice_prog_list_index]['CATEGORIES'];


                if ($PROVINCE_ID == SINDH_PROVINCE_ID) {
                    
                    /*
                   **********karachi quota is band from 2022 thats why we comment this code 28-12-2021 **********
                    if ($DISTRICT_ID == KARACHI_DISTRICT_ID && $campus_id != 4) {

                        //prePrint("KARACHI DITRCIT");
                        //prePrint("$CARD_ID --> $FIRST_NAME --> $APPLICATION_ID");

                        $is_seat_allot = false;
                        //find the index of karachi reserved quota in categories
                        $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',KARACHI_RESERVED_QUOTA);
                        if($j>=0){
                            if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);

                                array_push($selected_candidate, $object);
                                $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);

                                $is_seat_allot = true;
                                //break;
                            }
                        }
                        if ($GENDER == 'F' && $is_seat_allot == false ) {
                            $is_seat_allot = false;
                            //find the index of FEMALE_QUOTA_JUR  quota in categories
                            $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', FEMALE_QUOTA_JUR);
                                        if ($j >= 0) {
                                            if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                array_push($selected_candidate, $object);
                                                $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                $is_seat_allot = true;
                                                //break;
                                            }
                                        }


                        }
                        if (count($application_category) > 1 && $is_seat_allot == false) {
                            foreach ($application_category as $category) {

                                if ($category['FORM_CATEGORY_ID'] == SU_AFFILATED_EMP_FORM_CATEGORY_ID) {

                                    $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',SUE_AFFILIATED_QUOTA);
                                    if($j>=0){
                                        if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                            $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                            $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                            array_push($selected_candidate, $object);
                                            $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                            $is_seat_allot = true;
                                            //break;
                                        }
                                    }

                                }
                                else if ($category['FORM_CATEGORY_ID'] == SU_EMP_FORM_CATEGORY_ID) {
                                    $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',SUE_QUOTA);
                                    if($j>=0){
                                        if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                            $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                            $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                            array_push($selected_candidate, $object);
                                            $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                            $is_seat_allot = true;
                                            //break;
                                        }
                                    }

                                }

                                if ($is_seat_allot) {
                                    break;
                                }

                            }
                        }
                        if (count($application_category) > 1 && $is_seat_allot == false && $is_self_selected == false && ($is_prev_self_selected==false ||($is_prev_self_selected==true&&$pre_choice_no>$CHOICE_NO))) {
                            foreach ($application_category as $category) {

                                if ($category['FORM_CATEGORY_ID'] == SELF_FINANCE_FORM_CATEGORY_ID) {

                                    $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SELF_FINANCE);
                                    if ($j >= 0) {
                                        if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                            $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                            $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                            array_push($selected_candidate, $object);
                                            $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected,SELF_FINANCE_FORM_CATEGORY_ID);
                                            $is_self_selected = true;
                                            break;
                                        }
                                    }


                                }
                            }

                            if ($is_seat_allot) {
                                // $merit_count_el++;
                            } else {
                                //$merit_countN_nel++;
                            }
                        }



                    }
                    else {
                    **********karachi quota is band from 2022 thats why we comment this code 28-12-2021 **********
                    */
                        $campus_jurisdiction_obj = findObjectinList($campus_jurisdiction_list,'DISTRICT_ID',$DISTRICT_ID);
                       
                            if ($campus_jurisdiction_obj) {
                            //echo "$i--3,";
                            if ($campus_jurisdiction_obj['IS_JURISDICTION'] == "Y") {
                                //echo "$i--4,";
                                $is_seat_allot = false;
                                //find the index of GENERAL_MERIT_JUR  quota in categories
                                $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',GENERAL_MERIT_JUR);
                               
                                if($j>=0){
                                    if($discipline_seat_distribution[$choice_prog_list_index]['IS_QUOTA'] == 'Y'){
                                        $DISTRICT_QUOTA = &$discipline_seat_distribution[$choice_prog_list_index]['DISTRICT_QUOTA'];
                                        //$district_index = $this->getDistrictIdIndex($DISTRICT_QUOTA,$DISTRICT_ID);
                                        //find the index of DISTRICT Id in DISCIPLINE SEAT DISTRIBUTION if it is  quota
                                        $district_index= getIndexOfObjectInList($DISTRICT_QUOTA,'DISTRICT_ID',$DISTRICT_ID);
                                        if($district_index>=0){
                                            if($DISTRICT_QUOTA[$district_index]['RURAL_SEATS']==0&&$DISTRICT_QUOTA[$district_index]['URBAN_SEATS']==0){

                                                if ($DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING'] > 0) {
                                                    //    echo "$i--3--$j,";
                                                    $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING']--;
                                                    // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                                    //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                                    $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                    array_push($selected_candidate, $object);
                                                    $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                    $is_seat_allot = true;
                                                    //  break;
                                                }

                                            }
                                            else{

                                                if($U_R=='R'){
                                                    if ($DISTRICT_QUOTA[$district_index]['RURAL_SEATS_REMAINING'] > 0 && $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING'] > 0) {
                                                        //    echo "$i--3--$j,";
                                                        $DISTRICT_QUOTA[$district_index]['RURAL_SEATS_REMAINING']--;
                                                        $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING']--;
                                                        // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                                        //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }
                                                }else if($U_R=='U'){
                                                    if ($DISTRICT_QUOTA[$district_index]['URBAN_SEATS_REMAINING'] > 0 && $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING'] > 0) {
                                                        //    echo "$i--3--$j,";
                                                        $DISTRICT_QUOTA[$district_index]['URBAN_SEATS_REMAINING']--;
                                                        $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING']--;
                                                        // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                                        //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }

                                                }else{
                                                    echo "AREA NOT FOUND URBAN/RURAL";
                                                }


                                            }

                                        }
                                        else{
                                            echo "District Not Found";
                                        }
                                    }
                                    else {
                                        if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {
                                            //    echo "$i--3--$j,";
                                            $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                            // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                            //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                            $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                            array_push($selected_candidate, $object);
                                            $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                            $is_seat_allot = true;
                                            //break;
                                        }else{
                                            // prePrint($CATEGORIES);
                                            //prePrint($CATEGORIES[$j]['TOTAL_SEATS_REMAINING']);
                                        }
                                    }
                                }


                                if ($is_seat_allot) {
                                    // $merit_count_el++;
                                }
                                else {
                                    if ($GENDER == 'F') {
                                        $is_seat_allot = false;
                                        //find the index of FEMALE_QUOTA_JUR  quota in categories
                                        $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', FEMALE_QUOTA_JUR);
                                        if ($j >= 0) {
                                            if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                array_push($selected_candidate, $object);
                                                $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                $is_seat_allot = true;
                                                //break;
                                            }
                                        }


                                    }
									if ($is_seat_allot==false &&$RELIGION != 'ISLAM') {
										$is_seat_allot = false;
										//find the index of MINORITY_QUOTA  quota in categories
										$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', MINORITY_QUOTA);
										if ($j >= 0) {
											if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

												$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

												$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
												array_push($selected_candidate, $object);
												$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
												$is_seat_allot = true;
												//break;
											}
										}


									}
                                    if (count($application_category) > 1 && $is_seat_allot == false) {
                                        foreach ($application_category as $category) {

                                            if ($category['FORM_CATEGORY_ID'] == DISABLED_QUOTA_FORM_CATEGORY_ID) {
                                                $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', DISABLE_PERSONS_QUOTA);
                                                if ($j >= 0) {
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }
                                                }


                                            }
                                            else if ($category['FORM_CATEGORY_ID'] == HAFIZ_FORM_CATEGORY_ID) {

                                                $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', HAFIZ_QUOTA);
                                                if ($j >= 0) {
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }
                                                }

                                            }

                                            else if ($category['FORM_CATEGORY_ID'] == SU_EMP_FORM_CATEGORY_ID) {
                                                $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SUE_QUOTA);
                                                if ($j >= 0) {
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }
                                                }

                                            }
											else if ($category['FORM_CATEGORY_ID'] == SPORT_FORM_CATEGORY_ID) {
												$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SPORT_QUOTA);
												if ($j >= 0) {
													if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

														$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

														$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
														array_push($selected_candidate, $object);
														$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
														$is_seat_allot = true;
														//break;
													}
												}

											}


                                            if ($is_seat_allot) {
                                                break;
                                            }

                                        }
                                    }

                                    $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',COMMERCE_QUOTA);
                                    if($j>=0&&isset($discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA'])){
                                        if($is_seat_allot==false){
                                            $qual = findObjectinList($qualifications,'DISCIPLINE_ID',PRE_COMMERCE_DISCIPLINE_ID);
                                            $diploma = findObjectinList($qualifications,'DISCIPLINE_ID',17);
                                            if($qual || $diploma){
                                                 if($choice_prog_list_id==110){
                                                  if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0 &&$discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']>0) {
                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                                        $discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']--;
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                    }  
                                                }
                                                else if($GENDER == 'F' && $qual){
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0 &&$discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['F']>0) {
                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                                        $discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['F']--;
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                    }
                                                }else if($GENDER == 'M' && $qual){
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0 &&$discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['M']>0) {
                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                                        $discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['M']--;
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                    }
                                                }
                                               
                                            }
                                        }
                                    }



                                    if (count($application_category) > 1 && $is_seat_allot == false && $is_self_selected == false && ($is_prev_self_selected==false ||($is_prev_self_selected==true&&$pre_choice_no>$CHOICE_NO))) {
                                        foreach ($application_category as $category) {

                                            if ($category['FORM_CATEGORY_ID'] == SELF_FINANCE_FORM_CATEGORY_ID) {

                                                $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SELF_FINANCE);
                                                if ($j >= 0) {
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected,SELF_FINANCE_FORM_CATEGORY_ID);
                                                        $is_self_selected = true;
                                                        break;
                                                    }
                                                }


                                            }
                                        }

                                        if ($is_seat_allot) {
                                            // $merit_count_el++;
                                        } else {
                                            //$merit_countN_nel++;
                                        }
                                    }

                                }
                            }
                            else {
                                //prePrint("OUTJUR");
                                //echo $CPN;
                                $is_seat_allot = false;
                                //find the index of GENERAL_MERIT_JUR  quota in categories
                                $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',GENERAL_MERIT_OUT_JUR);
                                if($j>=0){
                                    if($discipline_seat_distribution[$choice_prog_list_index]['IS_QUOTA'] == 'Y'){
                                        $DISTRICT_QUOTA = &$discipline_seat_distribution[$choice_prog_list_index]['DISTRICT_QUOTA'];
                                        //$district_index = $this->getDistrictIdIndex($DISTRICT_QUOTA,$DISTRICT_ID);
                                        //find the index of DISTRICT Id in DISCIPLINE SEAT DISTRIBUTION if it is  quota
                                        $district_index= getIndexOfObjectInList($DISTRICT_QUOTA,'DIVISION_ID',$DIVISION_ID);
                                        if($district_index>=0){
                                            if($DISTRICT_QUOTA[$district_index]['RURAL_SEATS']==0&&$DISTRICT_QUOTA[$district_index]['URBAN_SEATS']==0){

                                                if ($DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING'] > 0) {
                                                    //    echo "$i--3--$j,";
                                                    $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING']--;
                                                    // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                                    //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                                    $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                    array_push($selected_candidate, $object);
                                                    $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                    $is_seat_allot = true;
                                                    //  break;
                                                }

                                            }
                                            else{

                                                if($U_R=='R'){
                                                    if ($DISTRICT_QUOTA[$district_index]['RURAL_SEATS_REMAINING'] > 0 && $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING'] > 0) {
                                                        //    echo "$i--3--$j,";
                                                        $DISTRICT_QUOTA[$district_index]['RURAL_SEATS_REMAINING']--;
                                                        $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING']--;
                                                        // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                                        //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }
                                                }else if($U_R=='U'){
                                                    if ($DISTRICT_QUOTA[$district_index]['URBAN_SEATS_REMAINING'] > 0 && $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING'] > 0) {
                                                        //    echo "$i--3--$j,";
                                                        $DISTRICT_QUOTA[$district_index]['URBAN_SEATS_REMAINING']--;
                                                        $DISTRICT_QUOTA[$district_index]['TOTAL_SEATS_REMAINING']--;
                                                        // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                                        //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                        //break;
                                                    }

                                                }else{
                                                    echo "AREA NOT FOUND URBAN/RURAL";
                                                }


                                            }

                                        }
                                        else{
                                            echo "District Not Found";
                                        }
                                    }
                                    else {
                                        if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {
                                            //    echo "$i--3--$j,";
                                            $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                            // prePrint(  $CATEGORIES[$j]['PROGRAM_TITLE']);
                                            //prePrint(  $CATEGORIES[$j]['CATEGORY_NAME']);
                                            $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                            array_push($selected_candidate, $object);
                                            $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                            $is_seat_allot = true;
                                            //break;
                                        }else{
                                            // prePrint($CATEGORIES);
                                            //prePrint($CATEGORIES[$j]['TOTAL_SEATS_REMAINING']);
                                        }
                                    }
                                }

                                if ($is_seat_allot) {
                                    // $merit_count_el_oj++;
                                }
                                else {
                                    //  echo $GENDER;
                                    if ($GENDER == 'F') {
                                        $is_seat_allot = false;
                                        //find the index of FEMALE_QUOTA_JUR  quota in categories
                                        $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',FEMALE_QUOTA_JUR);
                                        if($j>=0){
                                            if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                                array_push($selected_candidate, $object);
                                                $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                $is_seat_allot = true;
                                                //break;
                                            }
                                        }


                                    }
									if ($is_seat_allot==false && $RELIGION != 'ISLAM') {
										$is_seat_allot = false;
										//find the index of MINORITY_QUOTA  quota in categories
										$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', MINORITY_QUOTA);
										if ($j >= 0) {
											if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

												$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

												$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
												array_push($selected_candidate, $object);
												$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
												$is_seat_allot = true;
												//break;
											}
										}


									}
									if (count($application_category) > 1 && $is_seat_allot == false) {
										foreach ($application_category as $category) {

											if ($category['FORM_CATEGORY_ID'] == DISABLED_QUOTA_FORM_CATEGORY_ID) {
												$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', DISABLE_PERSONS_QUOTA);
												if ($j >= 0) {
													if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

														$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

														$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
														array_push($selected_candidate, $object);
														$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
														$is_seat_allot = true;
														//break;
													}
												}


											}
											else if ($category['FORM_CATEGORY_ID'] == HAFIZ_FORM_CATEGORY_ID) {

												$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', HAFIZ_QUOTA);
												if ($j >= 0) {
													if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

														$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

														$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
														array_push($selected_candidate, $object);
														$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
														$is_seat_allot = true;
														//break;
													}
												}

											}

											else if ($category['FORM_CATEGORY_ID'] == SU_EMP_FORM_CATEGORY_ID) {
												$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SUE_QUOTA);
												if ($j >= 0) {
													if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

														$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

														$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
														array_push($selected_candidate, $object);
														$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
														$is_seat_allot = true;
														//break;
													}
												}

											}
											else if ($category['FORM_CATEGORY_ID'] == SPORT_FORM_CATEGORY_ID) {
												$j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SPORT_QUOTA);
												if ($j >= 0) {
													if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

														$CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

														$object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
														array_push($selected_candidate, $object);
														$this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
														$is_seat_allot = true;
														//break;
													}
												}

											}


											if ($is_seat_allot) {
												break;
											}

										}
									}

                                    $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',COMMERCE_QUOTA);
                                    if($j>=0&&isset($discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA'])){
                                        if($is_seat_allot==false){
                                            $qual = findObjectinList($qualifications,'DISCIPLINE_ID',PRE_COMMERCE_DISCIPLINE_ID);
                                            $diploma = findObjectinList($qualifications,'DISCIPLINE_ID',17);
                                            if($qual || $diploma){
                                                 if($choice_prog_list_id==110){
                                                  if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0 &&$discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']>0) {
                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                                        $discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']--;
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                    }
                                                }
                                                else if($GENDER == 'F' && $qual){
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0 &&$discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['F']>0) {
                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                                        $discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['F']--;
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                    }
                                                }else if($GENDER == 'M' && $qual){
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0 &&$discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['M']>0) {
                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;
                                                        $discipline_seat_distribution[$choice_prog_list_index]['COMMERCE_QUOTA']['M']--;
                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                                        $is_seat_allot = true;
                                                    }
                                                }

                                            }
                                        }
                                    }

                                    if (count($application_category) > 1 && $is_seat_allot == false&&$is_self_selected==false&& ($is_prev_self_selected==false ||($is_prev_self_selected==true&&$pre_choice_no>$CHOICE_NO))) {
                                        foreach ($application_category as $category) {

                                            if ($category['FORM_CATEGORY_ID'] == SELF_FINANCE_FORM_CATEGORY_ID) {

                                                $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SELF_FINANCE);
                                                if ($j >= 0) {
                                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                                        array_push($selected_candidate, $object);
                                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected,SELF_FINANCE_FORM_CATEGORY_ID);
                                                        $is_self_selected = true;
                                                        break;
                                                    }
                                                }


                                            }

                                        }


                                    }



                                }

                            }
                        } else {
                             echo $candidate['APPLICATION_ID'].",<br>";
                             prePrint($candidate);
                             //echo $candidate['APPLICATION_ID'].",";
                            exit("<h1>Jurisdiction Not Found</h1>");
                        }


                    }
                /***********karachi quota is band from 2022 thats why we comment this code 28-12-2021 **********
                }
                **********karachi quota is band from 2022 thats why we comment this code 28-12-2021 ***********/
                else {
                    // prePrint("OTHER PROVINCE");
                    //  prePrint("$CARD_ID --> $FIRST_NAME --> $APPLICATION_ID");
                    $is_seat_allot = false;


                    if (count($application_category) > 1 && $is_seat_allot == false) {
                        foreach ($application_category as $category) {

                            if ($category['FORM_CATEGORY_ID'] == SU_AFFILATED_EMP_FORM_CATEGORY_ID) {

                                $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',SUE_AFFILIATED_QUOTA);
                                if($j>=0){
                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                        array_push($selected_candidate, $object);
                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                        $is_seat_allot = true;
                                        //break;
                                    }
                                }

                            }
                            else if ($category['FORM_CATEGORY_ID'] == SU_EMP_FORM_CATEGORY_ID) {
                                $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',SUE_QUOTA);
                                if($j>=0){
                                    if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                        $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                        $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                        array_push($selected_candidate, $object);
                                        $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected);
                                        $is_seat_allot = true;
                                        //break;
                                    }
                                }

                            }

                            if ($is_seat_allot) {
                                break;
                            }

                        }
                    }
                    if (count($application_category) > 1 && $is_seat_allot == false && $is_self_selected == false &&($is_prev_self_selected==false ||($is_prev_self_selected==true&&$pre_choice_no>$CHOICE_NO))) {
                        if($IS_SELF_DROP==false) {

                            $j= getIndexOfObjectInList($CATEGORIES,'CATEGORY_ID',OTHER_PROVINCES_SELF_FINANCE);
                            if($j>=0){
                                if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                    $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                    $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'],  "PROG_LIST_ID" => $choice_prog_list_id,"CHOICE_NO"=>$CHOICE_NO,"CATEGORY" => $CATEGORIES[$j]);
                                    array_push($selected_candidate, $object);
                                    $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected,OTHER_PROVINCES_SELF_FINANCE);
                                    $is_self_selected = true;
                                    //break;
                                }
                            }
                        }




                    }



                }

                if($is_seat_allot){
                    break;
                }
            }
            if($is_seat_allot==false&&$is_self_selected == false){
                array_push($not_selected_candidate, $candidate);
            }





        }

        fclose($error_log_seat_distribution);


    }

    private function checkPreRequsiteUpgrade($choice_prog_list_id,$candidate,$prog_type_id){
        $myfile =  $this->pre_req_log;
        if($prog_type_id==2 && $choice_prog_list_id==178&&$candidate['users_reg']['GENDER']!="F"){
            $txt =  "Not Eligible Male Candidate for PGD ,".$candidate['users_reg']['CNIC_NO']."\n";
                    fwrite($myfile, $txt);

                    return false;
        }
        if($this->pre_requiste_list!=null){
            $prerequiste_list = $this->pre_requiste_list;
            if(isset($prerequiste_list[$choice_prog_list_id])){
                $prerequiste_list_of_program = $prerequiste_list[$choice_prog_list_id];

                if(isset($candidate['applicants_minors'])){
                    $applicants_minors =  $candidate['applicants_minors'];
                }
                else{
                    $txt =  "applicant minor not found,".$candidate['users_reg']['CNIC_NO']."\n";
                    fwrite($myfile, $txt);

                    return false;
                }

                $qualification = null;
                if(isset($candidate['qualifications'])){
                    $qualifications =  $candidate['qualifications'];
                    if($prog_type_id==2){
                        if($qualifications[0]['DEGREE_ID']!=10){
                            $qualification =$qualifications[0];
                        }else{
                            $qualification = $qualifications[1];
                        }
                    }else{
                        $qualification = findObjectinList($qualifications,'DEGREE_ID',3);
                    }
                }else{
                    $txt =  "Applicant qualification not found,".$candidate['users_reg']['CNIC_NO']."\n";
                    fwrite($myfile, $txt);
                    return false;
                }


                if($qualification == null){
                    $txt ="Last Qualification not found,".$candidate['users_reg']['CNIC_NO'].",PROG_LIST_ID=$choice_prog_list_id,\n";
                    fwrite($myfile, $txt);
                    return false;
                }

                if(($prog_type_id==2&&$qualification['DISCIPLINE_ID']==20)){
                    //only for master Bsc

                    if($choice_prog_list_id==79||$choice_prog_list_id==135){
                        $pre_medical  = findObjectinList($qualifications,'DISCIPLINE_ID',11);
                        if(!$pre_medical){
                            $txt = "Intermediate not pre-medial,".$candidate['users_reg']['CNIC_NO'].",PROG LIST ID $choice_prog_list_id,'".json_encode($prerequiste_list_of_program)."'\n";
                            fwrite($myfile, $txt);
                            return false;
                        }
                    }
                    $check_prereq = false;
                    foreach ($applicants_minors as $applicants_minor){
                        $MINOR_MAPPING_ID = $applicants_minor['MINOR_MAPPING_ID'];
                        $pre_req_obj = findObjectinList($prerequiste_list_of_program,'MINOR_MAPPING_ID',$MINOR_MAPPING_ID);
                        if($pre_req_obj&&$pre_req_obj['PROG_LIST_ID']==$choice_prog_list_id){
                            $check_prereq = true;
                            break;
                        }
                    }
                  //  $applicants_minor = findObjectinList($applicants_minors, 'DISCIPLINE_ID', $qualification['DISCIPLINE_ID']);

                    if ($check_prereq) {


                        if($pre_req_obj){

                            $percentage =$qualification['OBTAINED_MARKS']/$qualification['TOTAL_MARKS']*100;
                            if($pre_req_obj['PRE_REQ_PER']<=$percentage){
                                return true;
                            }else{
                                $txt = "low percentage,".$candidate['users_reg']['CNIC_NO'].",PROG LIST ID $choice_prog_list_id,Precentage = $percentage,\"".$pre_req_obj['PROGRAM_TITLE']."\",'".json_encode($prerequiste_list_of_program)."'\n";
                                fwrite($myfile, $txt);
                                return false;
                            }

                        }else{
                            $txt = "Prerequiste Not Found," . $candidate['users_reg']['CNIC_NO'] . ",DISCIPLINE_NAME=" . $qualification['DISCIPLINE_NAME'] . ",PROG_LIST_ID=$choice_prog_list_id,\"".json_encode($applicants_minors)."\",'".json_encode($prerequiste_list_of_program)."'\n";
                            fwrite($myfile, $txt);
                        }

                    }
                    else {
                        $txt = "Applicant Minor not found," . $candidate['users_reg']['CNIC_NO'] . ",DISCIPLINE_NAME=" . $qualification['DISCIPLINE_NAME'] . ",PROG_LIST_ID=$choice_prog_list_id,'".json_encode($prerequiste_list_of_program)."'\n";
                        fwrite($myfile, $txt);
                    }


                }else{

                    //$qualification['DISCIPLINE_ID'];
                    $minor_maping_list = $this->minor_maping_list;
                    // $applicants_minor = $this->Administration->getMinorsByDiscipline_id( $qualification['DISCIPLINE_ID']);
                    $applicants_minor = $minor_maping_list[$qualification['DISCIPLINE_ID']];
                    if(count($applicants_minor)==1){
                        $applicants_minor = $applicants_minor[0];
                        $MINOR_MAPPING_ID = $applicants_minor['MINOR_MAPPING_ID'];
                        $pre_req_obj = findObjectinList($prerequiste_list_of_program,'MINOR_MAPPING_ID',$MINOR_MAPPING_ID);
                        if($pre_req_obj){

                            $percentage =$qualification['OBTAINED_MARKS']/$qualification['TOTAL_MARKS']*100;
                            if($pre_req_obj['PRE_REQ_PER']<=$percentage){
                                return true;
                            }else{
                                $txt = "low percentage,".$candidate['users_reg']['CNIC_NO'].",PROG LIST ID $choice_prog_list_id,Precentage = $percentage,\"".$pre_req_obj['PROGRAM_TITLE']."\",'".json_encode($prerequiste_list_of_program)."'\n";
                                fwrite($myfile, $txt);
                                return false;
                            }

                        }else{
                            $txt = "Prerequiste Not Found," . $candidate['users_reg']['CNIC_NO'] . ",DISCIPLINE_NAME=" . $qualification['DISCIPLINE_NAME'] . ",PROG_LIST_ID=$choice_prog_list_id,".json_encode($prerequiste_list_of_program)."'\n";
                            fwrite($myfile, $txt);
                        }
                    }else{
                        $txt ="minor issue in database,".$candidate['users_reg']['CNIC_NO'].",PROG_LIST_ID=$choice_prog_list_id,\n";
                        fwrite($myfile, $txt);
                    }

                }





            }else{
                $txt ="Prerequiste not found,".$candidate['users_reg']['CNIC_NO'].",PROG_LIST_ID=$choice_prog_list_id,\n";
                fwrite($myfile, $txt);
            }

        }
        else{
            $txt ="Prerequiste loading failed from database,".$candidate['users_reg']['CNIC_NO']."\n";
            fwrite($myfile, $txt);
            exit();
            //return false;
        }
    }
    
    private function remove_seats($prev_selected_program,&$discipline_seat_distribution,$is_prev_self_selected,$current_category = null){

        //$prev_program['CATEGORY_ID'];
        //$prev_program['PROG_LIST_ID'];
       // prePrint($prev_selected_program);
        if($prev_selected_program&&($is_prev_self_selected==true ||( $is_prev_self_selected==false && $current_category==null))) {
            $prev_program = $prev_selected_program['PRE_SELECTED'];
            $prog_list_index = getIndexOfObjectInList($discipline_seat_distribution, 'PROG_LIST_ID', $prev_program['PROG_LIST_ID']);
            if ($prog_list_index >= 0) {
                if ($discipline_seat_distribution[$prog_list_index]['IS_QUOTA'] == 'Y' && ($prev_program['CATEGORY_ID'] == GENERAL_MERIT_JUR || $prev_program['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR)) {

                    // $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'];
                    if($prev_program['CATEGORY_ID'] == GENERAL_MERIT_JUR){
                          $district_id_index = getIndexOfObjectInList($discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'], 'DISTRICT_ID', $prev_program['DISTRICT_ID']);     
                    }else if($prev_program['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR){
                          $district_id_index = getIndexOfObjectInList($discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'], 'DIVISION_ID', $prev_program['DIVISION_ID']);
                    }
                  
                    if ($prev_program['U_R'] == 'R') {
                        $RURAL_SEATS_REMAINING = $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['RURAL_SEATS_REMAINING'];
                        $RURAL_SEATS = $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['RURAL_SEATS'];
                        if ($RURAL_SEATS_REMAINING < $RURAL_SEATS) {
                            $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['RURAL_SEATS_REMAINING']++;
                        }

                        $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['TOTAL_SEATS_REMAINING']++;
                    } else if ($prev_program['U_R'] == 'U') {
                        $URBAN_SEATS_REMAINING = $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['URBAN_SEATS_REMAINING'];
                        $URBAN_SEATS = $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['URBAN_SEATS'];
                        if ($URBAN_SEATS_REMAINING < $URBAN_SEATS) {
                            $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['URBAN_SEATS_REMAINING']++;
                        }

                        $discipline_seat_distribution[$prog_list_index]['DISTRICT_QUOTA'][$district_id_index]['TOTAL_SEATS_REMAINING']++;
                    }

                } else {
                    if($this->check_current_choice_no <=$prev_program['CHOICE_NO']){
                                $category_id_index = getIndexOfObjectInList($discipline_seat_distribution[$prog_list_index]['CATEGORIES'], 'CATEGORY_ID', $prev_program['CATEGORY_ID']);
                            $TOTAL_SEATS = $discipline_seat_distribution[$prog_list_index]['CATEGORIES'][$category_id_index]['TOTAL_SEATS'];
                            $TOTAL_SEATS_REMAINING = $discipline_seat_distribution[$prog_list_index]['CATEGORIES'][$category_id_index]['TOTAL_SEATS_REMAINING'];
                           
                            if ($TOTAL_SEATS_REMAINING < $TOTAL_SEATS) {
                                $discipline_seat_distribution[$prog_list_index]['CATEGORIES'][$category_id_index]['TOTAL_SEATS_REMAINING']++;
                            }
                            if ($prev_program['CATEGORY_ID'] == COMMERCE_QUOTA) {
                                if($prev_program['PROG_LIST_ID']==110){
                                   $discipline_seat_distribution[$prog_list_index]['COMMERCE_QUOTA']++;
                                }else{
                                    if ($prev_program['GENDER'] == 'M') {
                                        $discipline_seat_distribution[$prog_list_index]['COMMERCE_QUOTA']['M']++;
                                    }
                                    if ($prev_program['GENDER'] == 'F') {
                                        $discipline_seat_distribution[$prog_list_index]['COMMERCE_QUOTA']['F']++;
                                    }
                                }
                                
                            } 
                        
                    }
                   

                }
            }

        }
    }
   
    private function filter_candidate(&$result_array,&$all_selected_student,&$candidates_fee_ledger,&$discipline_seat_distribution){
       
        foreach ($all_selected_student as $application_id=>$selected_student){
          
            $reserved_seat = array();
            $is_retain = false;
            $is_self_drop = false;
            $is_drop = false;
            $is_self  = false;
            $is_merit  = false;
            $is_self_retain = false;
            $MERIT_LAST_CHOICE = -1;
            $SELF_LAST_CHOICE = -1;
            if($selected_student['SELF'] && $selected_student['MERIT']){
                $MERIT_LAST_CHOICE = $selected_student['MERIT']['CHOICE_NO'];
                $SELF_LAST_CHOICE = $selected_student['SELF']['CHOICE_NO'];
                if(isset($candidates_fee_ledger[$application_id])){

                    $candidates_ledger = $candidates_fee_ledger[$application_id];

                    if($candidates_ledger['SELF_FEE']){
                        if($selected_student['MERIT']['CHOICE_NO']<=$selected_student['SELF']['CHOICE_NO']){
                            if($selected_student['MERIT']['CHOICE_NO']==1){
                                //REATAIN MERIT
                                $is_retain = true;
                            }
                            $is_merit = true;
                            array_push($reserved_seat , $selected_student['MERIT']);

                        }else{
                            $is_self  = true;

                            array_push($reserved_seat , $selected_student['SELF']);
                        }

                    }
                    else if($candidates_ledger['MERIT_FEE']){
                        $is_self_drop = true;
                        $is_merit = true;
                        if($selected_student['MERIT']['CHOICE_NO']==1){
                            //REATAIN MERIT
                            $is_retain = true;
                        }
                        array_push($reserved_seat , $selected_student['MERIT']);
                        //prePrint("SELF DROP OUT ");
                    }
                    else{
                        $is_drop = true;
                    }

                }
                else{
                    $is_drop = true;
                    //prePrint("DROP OUT ");
                }
            }
            else if($selected_student['SELF']){
                $SELF_LAST_CHOICE = $selected_student['SELF']['CHOICE_NO'];

                if(isset($candidates_fee_ledger[$application_id])){
                    $candidates_ledger = $candidates_fee_ledger[$application_id];
                    if($candidates_ledger['SELF_FEE']){
                        $is_self  = true;

                        array_push($reserved_seat , $selected_student['SELF']);
                    }else{
                        $is_self_drop = true;
                        // prePrint("SELF DROP OUT ");
                    }

                }else{
                    $is_self_drop = true;
                    //prePrint("SELF DROP OUT ");
                }
            }
            else if($selected_student['MERIT']){
                $MERIT_LAST_CHOICE = $selected_student['MERIT']['CHOICE_NO'];
                if(isset($candidates_fee_ledger[$application_id])){
                    $candidates_ledger = $candidates_fee_ledger[$application_id];
                    if($candidates_ledger['MERIT_FEE']){
                        $is_merit = true;
                        if($selected_student['MERIT']['CHOICE_NO']==1){
                            //REATAIN MERIT
                            $is_retain = true;
                        }
                        array_push($reserved_seat , $selected_student['MERIT']);

                    }else{
                        $is_drop = true;
                        // prePrint("DROP OUT ");
                    }
                }else{
                    $is_drop = true;
                    // prePrint("DROP OUT ");
                }
            }
            else{
                prePrint("SOMETHING WENT WRONG SENERIO MISTMATCH");
            }

            if(isset($candidates_fee_ledger[$application_id])&&count($reserved_seat)){
                $candidates_ledger =  $candidates_fee_ledger[$application_id];
                $PROG_LIST_ID = $reserved_seat[0]['PROG_LIST_ID'];

                if($is_merit&&$candidates_ledger['RETAIN_FEE']&&$candidates_ledger['RETAIN_FEE']['PROG_LIST_ID']==$PROG_LIST_ID){
                    $is_retain = true;
                }
                else if($is_self&&$candidates_ledger['RETAIN_FEE']&&$candidates_ledger['RETAIN_FEE']['PROG_LIST_ID']==$PROG_LIST_ID){
                    $is_self_retain = true;
                }

            }

            $result_array[$application_id]['IS_MERIT'] = $is_merit;
            $result_array[$application_id]['IS_SELF'] = $is_self;
            $result_array[$application_id]['IS_RETAIN'] = $is_retain;
            $result_array[$application_id]['IS_SELF_RETAIN'] = $is_self_retain;
            $result_array[$application_id]['IS_DROP'] = $is_drop;
            $result_array[$application_id]['IS_SELF_DROP'] = $is_self_drop;
            $result_array[$application_id]['MERIT_LAST_CHOICE'] = $MERIT_LAST_CHOICE;
            $result_array[$application_id]['SELF_LAST_CHOICE'] = $SELF_LAST_CHOICE;
            $result_array[$application_id]['LEDGER'] = isset($candidates_fee_ledger[$application_id])?$candidates_fee_ledger[$application_id]:null;

//            if($application_id==29136){
//                prePrint( $result_array[$application_id]);
//                exit() ;
//            }
            
                //     prePrint($application_id.",");
                // }
            if($is_merit||$is_self){
                
                $result_array[$application_id]['PRE_SELECTED'] = $reserved_seat[0];

            }
            else{
                //drop student;
                //prePrint($selected_student);
                // if($selected_student['MERIT']&&$selected_student['MERIT']['CATEGORY_ID']==1&&$selected_student['MERIT']['PROG_LIST_ID']==98){
                //     prePrint($application_id.",");
                // }
                $result_array[$application_id]['PRE_SELECTED'] = $selected_student;
                continue;
            }


            $selected_student = $reserved_seat[0];
            // if( $selected_student['PROG_LIST_ID']==5&& $selected_student['CATEGORY_ID']==1&&$selected_student['DISTRICT_ID']==130){
            //     prePrint($selected_student); 
            // }
       
            //  prePrint($selected_student);
            $selected_student['PROG_LIST_ID'];
            $selected_student['CATEGORY_ID'];
            $GENDER = $selected_student['GENDER'];
            $U_R =  $selected_student['U_R'];
            $index = getIndexOfObjectInList($discipline_seat_distribution,"PROG_LIST_ID",$selected_student['PROG_LIST_ID']);
            //prePrint($discipline_seat_distribution[$index]);
            //[IS_QUOTA] => N
            if($index>=0) {
                $discipline_seat_distribution[$index]['CATEGORIES'];

                if ($discipline_seat_distribution[$index]['IS_QUOTA'] == 'Y' && ($selected_student['CATEGORY_ID'] == GENERAL_MERIT_JUR || $selected_student['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR)) {
                   
                    if($selected_student['CATEGORY_ID'] == GENERAL_MERIT_JUR){
                         $district_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['DISTRICT_QUOTA'], 'DISTRICT_ID', $selected_student['DISTRICT_ID']);
                    }else if($selected_student['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR){
                          $district_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['DISTRICT_QUOTA'], 'DIVISION_ID', $selected_student['DIVISION_ID']);
                    }
                    if ($discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['RURAL_SEATS'] == 0 && $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['URBAN_SEATS'] == 0) {
                        $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['TOTAL_SEATS_REMAINING']--;
                    }else {
                        if ($selected_student['U_R'] == 'R') {

                            $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['RURAL_SEATS_REMAINING']--;

                        } else {
                            $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['URBAN_SEATS_REMAINING']--;
                        }
                        $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['TOTAL_SEATS_REMAINING']--;
                    }

                }
                else {
                    $cat_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['CATEGORIES'], "CATEGORY_ID", $selected_student['CATEGORY_ID']);
                    if($cat_index>=0){
                            $discipline_seat_distribution[$index]['CATEGORIES'][$cat_index]['TOTAL_SEATS_REMAINING']--;
                        if ($selected_student['CATEGORY_ID'] == COMMERCE_QUOTA) {
                            if($selected_student['PROG_LIST_ID']==110 ){
                                 $discipline_seat_distribution[$index]['COMMERCE_QUOTA']--;
                            }else{
                                if ($GENDER == 'F') {
                                    $discipline_seat_distribution[$index]['COMMERCE_QUOTA']['F']--;
                                } else if ($GENDER == 'M') {
                                    $discipline_seat_distribution[$index]['COMMERCE_QUOTA']['M']--;
                                } 
                            }
                        }
                    }else{
                        // prePrint($discipline_seat_distribution[$index]['CATEGORIES']);
                        // if($selected_student['CATEGORY_ID']==1){
                        //     prePrint($selected_student);
                        //     exit();
                        // }
                    }
                    
                }
            }

        }
       
    }

    public function generate_last_cpn($campus_id=1,$shift_id=1,$session_id=1,$prog_type_id=1,$admission_session_id=1){


        if($shift_id == 1){
            $shift = "MORNING";
        }else if($shift_id = 2){
            $shift = "EVENING";
        }

        if($prog_type_id==1){
            $file_name = "BACHELOR";
        }else{
            $file_name = "MASTER";
        }
        $file = fopen("merit_list/$file_name-$campus_id-LAST-CPN-{$this->date}.csv","w+");

        $discipline_seat_distribution = $this->MeritList_model->getSeatDistribution($campus_id,$shift_id,$session_id,$prog_type_id);
        $result = $this->MeritList_model->min_cpn_report($admission_session_id,$shift_id,$session_id,$prog_type_id);
        $result_district = $this->MeritList_model->min_cpn_report_district($admission_session_id,$shift_id,$session_id,$prog_type_id);

        $discipline_min_array = array();

        foreach ($result as $value){
            if(!isset($discipline_min_array[$value['PROG_LIST_ID']])){
                $discipline_min_array[$value['PROG_LIST_ID']] = array();
            }
            array_push($discipline_min_array[$value['PROG_LIST_ID']],$value);
        }

        $result_distrcit_array = array();

        foreach ($result_district as $district){
            if(!isset($result_distrcit_array[$district['PROG_LIST_ID']])){
                $result_distrcit_array[$district['PROG_LIST_ID']] = array();
            }
            array_push($result_distrcit_array[$district['PROG_LIST_ID']],$district);
            //$district['PROG_LIST_ID'];
        }

        foreach ($result_distrcit_array as $i=>$district_list){


            $result_distrcit_name_array = array();

            foreach ($district_list as $district){
                if(!isset($result_distrcit_name_array[$district['DISTRICT_NAME']])){
                    $result_distrcit_name_array[$district['DISTRICT_NAME']] = array();
                }
                array_push($result_distrcit_name_array[$district['DISTRICT_NAME']],$district);

            }
            // prePrint($result_distrcit_name_array);

            $result_distrcit_array[$i] = $result_distrcit_name_array;
        }

        //prePrint($result);

        foreach ($discipline_min_array as $discipline){
            $txt = "";
            $txt_cat = "";

            foreach ($discipline as $value) {
                $is_quota = false;
                $district_file_txt = '';
                $txt = "DISCIPLINE," . $value['PROGRAM_TITLE'] . ",$shift\n ";
                //prePrint($txt);
                if ($value['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR ) {
                    $program_list = findObjectinList($discipline_seat_distribution, 'PROG_LIST_ID', $value['PROG_LIST_ID']);
                    if ($program_list && $program_list['IS_QUOTA'] == 'Y') {
                        $is_quota = true;

                        foreach ($result_distrcit_array[$value['PROG_LIST_ID']] as $district_name => $district) {
                            //prePrint($district);
                            $urban_min_cpn = 0;
                            $rural_min_cpn = 0;
                            foreach ($district as $area) {
                                if ($area['U_R'] == 'U') {
                                    $urban_min_cpn = $area['MIN_CPN'];
                                } elseif ($area['U_R'] == 'R') {
                                    $rural_min_cpn = $area['MIN_CPN'];
                                }
                            }
                            $district_file_txt .= "$district_name,URBAN,$urban_min_cpn,RURAL,$rural_min_cpn\n";
                        }
                    }
                }
                $txt_cat.= $value['CATEGORY_NAME'] . "," . $value['MIN_CPN'] . "\n";
                if ($is_quota) {
                    // prePrint($district_file_txt);
                    $txt_cat.=$district_file_txt;
                }

            }

            prePrint($txt);
            printf($txt_cat);
            fwrite($file,$txt);
            fwrite($file,$txt_cat."\n\n");
        }


    }
   
    public function generate_last_cpn_checking($campus_id=1,$shift_id=1,$session_id=1,$prog_type_id=1,$admission_session_id=1){


        if($shift_id == 1){
            $shift = "MORNING";
        }else if($shift_id = 2){
            $shift = "EVENING";
        }

        if($prog_type_id==1){
            $file_name = "BACHELOR";
        }else{
            $file_name = "MASTER";
        }
       // $file = fopen("merit_list/$file_name-$campus_id-LAST-CPN-{$this->date}.csv","w+");

        $discipline_seat_distribution = $this->MeritList_model->getSeatDistribution($campus_id,$shift_id,$session_id,$prog_type_id);
        $result = $this->MeritList_model->min_cpn_report($admission_session_id,$shift_id,$session_id,$prog_type_id);
        $result_district = $this->MeritList_model->min_cpn_report_district($admission_session_id,$shift_id,$session_id,$prog_type_id);

        $discipline_min_array = array();

        foreach ($result as $value){
            if(!isset($discipline_min_array[$value['PROG_LIST_ID']])){
                $discipline_min_array[$value['PROG_LIST_ID']] = array();
            }
            array_push($discipline_min_array[$value['PROG_LIST_ID']],$value);
        }

        $result_distrcit_array = array();

        foreach ($result_district as $district){
            if(!isset($result_distrcit_array[$district['PROG_LIST_ID']])){
                $result_distrcit_array[$district['PROG_LIST_ID']] = array();
            }
            array_push($result_distrcit_array[$district['PROG_LIST_ID']],$district);
            //$district['PROG_LIST_ID'];
        }

        foreach ($result_distrcit_array as $i=>$district_list){


            $result_distrcit_name_array = array();

            foreach ($district_list as $district){
                if(!isset($result_distrcit_name_array[$district['DISTRICT_NAME']])){
                    $result_distrcit_name_array[$district['DISTRICT_NAME']] = array();
                }
                array_push($result_distrcit_name_array[$district['DISTRICT_NAME']],$district);

            }
            // prePrint($result_distrcit_name_array);

            $result_distrcit_array[$i] = $result_distrcit_name_array;
        }
        
          $all_candidate_results_sort = $this->TestResult_model->getListOfStudentByTestIdAndCampusIdAndShiftId($TEST_ID=1,$campus_id=1,$shift_id=1);
        
         $campus_jurisdiction_list = $this->Administration->getMappedCampusJurisdiction($campus_id);
         //prePrint($campus_jurisdiction_list);
           
           $id = array(24747	,
5777	,
27980	,
21097	,
23320	,
34512	,
15213	,
16021	,
14478	,
13506	,
18910	,
25157	,
25686	,
26410	,
26410	,
18913	,
9897	,
11461	,
8591	,
22736	,
23276	,
23276	,
15899	,
20700	,
26012	,
30446	,
26235	,
23184	,
24454	,
24454	,
11866	,
11866	,
21366	,
34545	,
12122	,
4559	,
34787	,
17578	,
29589	,
10127	,
4927	,
1031	,
5236	,
19175	,
10133	,
28293	,
27334	,
19615	,
29399	,
33796	,
33810	,
12191	,
6252	,
25963	,
19986	,
16743	,
31694	,
27574	,
26953	,
11556	,
7344	,
4262	,
28108	,
30350	,
20225	,
20559	,
27806	,
6092	,
26421	,
21547	,
7850	,
29729	,
14403	,
6812	,
23347	,
30355	,
34349	,
30709	,
30242	,
18658	,
19321	,
29333	,
23982	,
17580	,
14114	,
23676	,
23729	,
14035	,
21712	,
4023	,
16796	,
26418	,
3420	,
26421	,
23383	,
6335	,
26740	,
12934	,
7885	,
4777	,
23131	,
33130	,
3720	,
6487	,
23922	,
27190	,
33255	,
10056	,
27075	,
33735	,
33384	
);
$self_Array = $new_array = array();
foreach($id as $app_id){
    if(isset($all_candidate_results_sort[$app_id])){
         $can = $all_candidate_results_sort[$app_id];
    }else{
        prePrint("OHTER CAMPUS");
        continue;
    }
 
           // $form_data = json_decode($can['FORM_DATA'],true);
            $DISTRICT_NAME =$can['users_reg']['DISTRICT_NAME'];
            $DISTRICT_ID =$can['users_reg']['DISTRICT_ID'];
            $CNIC_NO =$can['users_reg']['CNIC_NO'];
             $area  =$can['users_reg']['U_R'];
             $gender  =$can['users_reg']['GENDER'];
            //prePrint($DISTRICT_NAME);
            $choices = $can['application_choices'];
            $application_category = $can['application_category'];
            $CPN = $can['CPN'];
            $jur = findObjectinList($campus_jurisdiction_list,'DISTRICT_ID',$DISTRICT_ID);
            foreach($choices as $ch ){
                $is_choice_found = false;
                   $PROG_LIST_ID = $ch['PROG_LIST_ID'];
                   $CHOICE_NO = $ch['CHOICE_NO'];
                if($PROG_LIST_ID==143){
                    continue;
                }
             
                 $PROGRAM_TITLE = $ch['PROGRAM_TITLE'];
        
                
                $prog = findObjectinList($discipline_seat_distribution,'PROG_LIST_ID',$PROG_LIST_ID);
                 
                 if($prog['IS_QUOTA']=='Y'){
                    
                        $all_distrct = $result_distrcit_array[$PROG_LIST_ID];
                        //prePrint($all_distrct);
                        if(isset($all_distrct[$DISTRICT_NAME])){
                                
                                foreach($all_distrct[$DISTRICT_NAME] as $district){
                                    if($district['U_R']==$area && $district['MIN_CPN']>0&&$district['MIN_CPN']<=$CPN){
                                         prePrint("GM ".$area);
                                         prePrint($PROGRAM_TITLE.",".$CHOICE_NO);
                                        prePrint($CNIC_NO);
                                        $new_array[]=$app_id;
                                        $is_choice_found = true;
                                        break;
                                    }
                                }
                        }else{
                           // PrePrint("NO DISTRICT FOUND<br>");
                        }
                          
                    
                 }else{
                     $discipline = $discipline_min_array[$PROG_LIST_ID];
                    if($jur['IS_JURISDICTION']=='Y'){
                        $jur_dis = findObjectinList($discipline,'CATEGORY_ID',1);
                        if($jur_dis['MIN_CPN']&&$jur_dis['MIN_CPN']<=$CPN){
                            prePrint("GM J");
                                     prePrint($PROGRAM_TITLE.",".$CHOICE_NO);
                                        prePrint($CNIC_NO);
                                        $new_array[]=$app_id;
                                        $is_choice_found = true;
                                        break;
                        }
                        
                    }else{
                        
                        $jur_dis = findObjectinList($discipline,'CATEGORY_ID',3);
                         
                          
                            if($jur_dis['MIN_CPN']&&$jur_dis['MIN_CPN']<=$CPN){
                                prePrint("GM OJ");
                              prePrint($PROGRAM_TITLE.",".$CHOICE_NO);
                                        prePrint($CNIC_NO);
                                        $new_array[]=$app_id;
                                        $is_choice_found = true;
                                        break;
                            }
                           
                        
                    }
                
                    
                 }
                   
                   $discipline = $discipline_min_array[$PROG_LIST_ID];
                    if($jur['IS_JURISDICTION']=='Y'){
                       
                        if($gender=='F'){
                            
                            $jur_dis = findObjectinList($discipline,'CATEGORY_ID',4);
                            if($jur_dis['MIN_CPN']&&$jur_dis['MIN_CPN']<=$CPN){
                                prePrint("FM J");
                             prePrint($PROGRAM_TITLE.",".$CHOICE_NO);
                                        prePrint($CNIC_NO);
                                        $new_array[]=$app_id;
                                        $is_choice_found = true;
                                        break;
                            }
                        }
                    }else{
                        
                       
                            if($gender=='F'){
                            
                            $jur_dis = findObjectinList($discipline,'CATEGORY_ID',5);
                            if($jur_dis['MIN_CPN']&&$jur_dis['MIN_CPN']<=$CPN){
                                 prePrint("FM OJ");
                                         prePrint($PROGRAM_TITLE.",".$CHOICE_NO);
                                        prePrint($CNIC_NO);
                                        $new_array[]=$app_id;
                                        $is_choice_found = true;
                                        break;
                            }
                        }
                        
                    }
                if($is_choice_found ==true){
                    break;
                }
                $is_found = findObjectinList($application_category,'FORM_CATEGORY_ID',SELF_FINANCE_FORM_CATEGORY_ID);
                if($is_found){
                    $self_tick  = findObjectinList($discipline,'CATEGORY_ID',SELF_FINANCE);
                            if($self_tick['MIN_CPN']&&$self_tick['MIN_CPN']<=$CPN){
                                prePrint("SELF FINANCE");
                                  prePrint($PROGRAM_TITLE.",".$CHOICE_NO);
                                        prePrint($CNIC_NO);
                                        $self_Array[]=$app_id;
                                     //   break;
                            }
                }
                
            }   
}
           
            
            
            
            // prePrint();
            //prePrint($can['appliation_categories']);
       
        //prePrint($discipline_seat_distribution);
       // prePrint($discipline_min_array);
        //($result_distrcit_array);
         exit();
        

     


    }

    public function ForSpecailSelfGenerateMeritList(){


        if(isset($_POST['PROG_LIST_ID'])&&count($_POST['PROG_LIST_ID'])&&isset($_POST['PROG_TYPE_ID'])&&isset($_POST['SHIFT_ID'])&&isset($_POST['YEAR'])&&isset($_POST['TEST_ID'])&&isset($_POST['CAMPUS_ID'])){
            $this->pre_req_log = fopen("merit_list/for_specail_self_error_log_for_prereq".$this->date.".csv", "w") or die("Unable to open file!");

            $session = $this->Admission_session_model->getSessionByYearData($_POST['YEAR']);
            $PROG_LIST_IDS = $_POST['PROG_LIST_ID'];
            $TEST_ID = $_POST['TEST_ID'];
            $campus_id = $_POST['CAMPUS_ID'];
            $shift_id = $_POST['SHIFT_ID'];
            $session_id =$session['SESSION_ID'] ;
            $prog_type_id = $_POST['PROG_TYPE_ID'];
            $ADMISSION_LIST_ID = $_POST['ADMISSION_LIST_ID'];
            $admission_list =  $this->Selection_list_report_model->get_admission_list_no_by_id($ADMISSION_LIST_ID);
            $first_merit_list = $admission_list['LIST_NO'];

            $merit_list_name = merit_list_decode($first_merit_list);
            $is_provisional = $_POST['IS_PROVISIONAL'];
            $admission_session_obj = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$prog_type_id);
            $admission_session_id = $admission_session_obj['ADMISSION_SESSION_ID'];
            $campus_name = $admission_session_obj['NAME'];

            prePrint("Start Time".date("d-m-y h:i:s A"));

            $pre_requisite_list = $this->Prerequisite_model->getPrerequisiteByProgramTypeId($prog_type_id);
            $minor_maping_list =  $this->Administration->getMinorMapping();

            $minor_maping_list_array = array();

            foreach ($minor_maping_list as $minor_maping){
                if(!isset($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']])||!is_array($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']])){
                    $minor_maping_list_array[$minor_maping['DISCIPLINE_ID']] = array();
                }
                array_push($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']],$minor_maping);
            }

            $this->minor_maping_list = $minor_maping_list_array;


            $pre_requisite_list_array = array();

            foreach ($pre_requisite_list as $pre_req){
                if(!isset($pre_requisite_list_array[$pre_req['PROG_LIST_ID']])||!is_array($pre_requisite_list_array[$pre_req['PROG_LIST_ID']])){
                    $pre_requisite_list_array[$pre_req['PROG_LIST_ID']] = array();
                }
                array_push($pre_requisite_list_array[$pre_req['PROG_LIST_ID']],$pre_req);
            }

            $this->pre_requiste_list = $pre_requisite_list_array;



            $campus_jurisdiction_list = $this->Administration->getMappedCampusJurisdiction($campus_id);

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $candidates_fee_ledger = $this->ForSpecailSelf_model->ForSpecailSelfGetFeeLedger($admission_session_id,$shift_id,$session_id,$prog_type_id,$TEST_ID);
            //  prePrint($candidates_fee_ledger);
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $discipline_seat_distribution = $this->MeritList_model->getSeatDistribution($campus_id,$shift_id,$session_id,$prog_type_id,$PROG_LIST_IDS);

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $all_selected_student = $this->ForSpecailSelf_model->ForSpecailSelfGetSelectedStudent($admission_session_id,$shift_id,$session_id,$prog_type_id,$TEST_ID);
            //prePrint($all_selected_student);
            $prev_selected_list = array();
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $this->for_specail_self_filter_candidate($prev_selected_list,$all_selected_student,$candidates_fee_ledger,$discipline_seat_distribution);

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));


            $all_candidate_results_sort = $this->ForSpecailSelf_model->ForGetListOfStudentByTestIdAndCampusIdAndShiftId($TEST_ID,$campus_id,$shift_id);
//                prePrint($all_candidate_results_sort);
//                exit();
            prePrint("after sorting Time".date("d-m-y h:i:s A"));
            prePrint("ALL STUDENT after sorting " . count($all_candidate_results_sort));

            $selected_candidate = array();
            $not_selected_candidate = array();



            $this->ForSpecialSelfGetDepartmentNextMeritList($all_candidate_results_sort,$discipline_seat_distribution,$selected_candidate,$not_selected_candidate,$campus_jurisdiction_list,$campus_id,$prog_type_id,$prev_selected_list);
            prePrint("End Time department merit list".date("d-m-y h:i:s A"));

            $myfile  = fopen("merit_list/FOR-SPECAIL-SELF-SELECTED-CANDIDATE-$merit_list_name-LIST-$campus_name-".date("d_m_y_h_i_s_A").".csv",'w+');
            $txt = "CARD_ID,APPLICATION_ID,CPN,USER_ID,CNIC_NO,FIRST_NAME,LAST_NAME,FNAME,GENDER,U_R,DISTRICT_NAME,CATEGORY_NAME,PROGRAM_TITLE,CHOICE_NO,CAMPUS_NAME,PROG_LIST_ID,STATUS,SSC_OBT,SSC_TOT,SSC_GRP,SSC_P_YEAR,SSC_BORAD,HSC_OBT,HSC_TOT,HSC_GRP,HSC_P_YEAR,HSC_BORAD,IS_PROMOTED,MOBILE_NO,EMAIL\n";
            fwrite($myfile, $txt);
            $form_array = array();

            foreach ($selected_candidate as $candidate){



                $candidate_info = $candidate['candidate'];
               // $form_data = json_decode($candidate_info['FORM_DATA'],true);
                $IS_PROMOTED = $candidate_info['IS_PROMOTED'];
                $users_reg = $candidate_info['users_reg'];
                $qualifications = $candidate_info['qualifications'];
                $inter = findObjectinList($qualifications,"DEGREE_ID",3);
                $metric = findObjectinList($qualifications,"DEGREE_ID",2);

                $FIRST_NAME =  $users_reg['FIRST_NAME'];
                $LAST_NAME =  $users_reg['LAST_NAME'];
                $F_NAME =  $users_reg['FNAME'];
                $CNIC_NO =  $users_reg['CNIC_NO'];
                $MOBILE_NO =  "0".$users_reg['MOBILE_NO'];
                $EMAIL =  $users_reg['EMAIL'];
                $GENDER =  $users_reg['GENDER'];
                $U_R =  $users_reg['U_R'];
                $DISTRICT_NAME =  $users_reg['DISTRICT_NAME'];
                $CATEGORY_NAME = $candidate['CATEGORY']['CATEGORY_NAME'];
                $PROGRAM_TITLE = $candidate['CATEGORY']['PROGRAM_TITLE'];
                $CAMPUS_NAME = $candidate['CATEGORY']['NAME'];
                $PROG_LIST_ID = $candidate['CATEGORY']['PROG_LIST_ID'];
                $CATEGORY_ID = $candidate['CATEGORY']['CATEGORY_ID'];
                $APPLICATION_ID = $candidate['APPLICATION_ID'];
                $USER_ID = $candidate['USER_ID'];
                $CPN = $candidate_info['CPN'];
                $DETAIL_CPN = $candidate_info['DETAIL_CPN'];
                $CARD_ID = $candidate_info['CARD_ID'];
                $ADMISSION_SESSION_ID = $candidate_info['ADMISSION_SESSION_ID'];
                $STATUS_ID = $candidate_info['STATUS_ID'];
                $CHOICE_NO = $candidate['CHOICE_NO'];
                $DISTRICT_ID =  $users_reg['DISTRICT_ID'];

                $inter_csv = "{$inter['OBTAINED_MARKS']},{$inter['TOTAL_MARKS']},{$inter['DISCIPLINE_NAME']},{$inter['PASSING_YEAR']},\"{$inter['ORGANIZATION']}\"";
                $metric_csv = "{$metric['OBTAINED_MARKS']},{$metric['TOTAL_MARKS']},{$metric['DISCIPLINE_NAME']},{$metric['PASSING_YEAR']},\"{$metric['ORGANIZATION']}\"";


                $txt = "$CARD_ID,$APPLICATION_ID,$CPN,$USER_ID,$CNIC_NO,\"$FIRST_NAME\",\"$LAST_NAME\",\"$F_NAME\",$GENDER,$U_R,\"$DISTRICT_NAME\",\"$CATEGORY_NAME\",\"$PROGRAM_TITLE\",$CHOICE_NO,\"$CAMPUS_NAME\",$PROG_LIST_ID,$STATUS_ID,$metric_csv,$inter_csv,$IS_PROMOTED,$MOBILE_NO,\"$EMAIL\"\n";

                $DETAIL_CPN = "";
                
                $list = array(
                    "APPLICATION_ID"=>$APPLICATION_ID,
                    "TEST_ID"=>$TEST_ID,
                    "SHIFT_ID"=>$shift_id,
                    "CHOICE_NO"=>$CHOICE_NO,
                    "PROG_LIST_ID"=>$PROG_LIST_ID,
                    "CATEGORY_ID"=>$CATEGORY_ID,
                    "CARD_ID"=>$CARD_ID,
                    "ADMISSION_SESSION_ID"=>$ADMISSION_SESSION_ID,
                    
                    "CPN"=>$CPN,
                    "ACTIVE"=>1,
                    "IS_PROVISIONAL"=>$is_provisional,
                    "ADMISSION_LIST_ID"=>$ADMISSION_LIST_ID
                );
                $form_array[] = $list;
                fwrite($myfile, $txt);
            }
            fclose($myfile);

            $myfile  = fopen("merit_list/FOR-SPECAIL-SELF-NOT-SELECTED-CANDIDATE-$merit_list_name-LIST-$campus_name-".date("d_m_y_h_i_s_A").".csv",'w+');
            $txt = "CARD_ID,APPLICATION_ID,CPN,USER_ID,CNIC_NO,FIRST_NAME,LAST_NAME,FNAME,GENDER,U_R,DISTRICT_NAME,STATUS,PROGRAMS_CHOICE,CATEGORY\n";
            fwrite($myfile, $txt);
            foreach($not_selected_candidate as $candidate){
               // $form_data = json_decode($candidate['FORM_DATA'],true);

                $users_reg = $candidate['users_reg'];

                $FIRST_NAME =  $users_reg['FIRST_NAME'];
                $LAST_NAME =  $users_reg['LAST_NAME'];
                $F_NAME =  $users_reg['FNAME'];
                $CNIC_NO =  $users_reg['CNIC_NO'];
                $GENDER =  $users_reg['GENDER'];
                $U_R =  $users_reg['U_R'];
                $DISTRICT_NAME =  $users_reg['DISTRICT_NAME'];
                $STATUS_ID = $candidate['STATUS_ID'];
                $CPN = $candidate['CPN'];
                $APPLICATION_ID = $candidate['APPLICATION_ID'];
                $USER_ID = $candidate['USER_ID'];
                $CARD_ID = $candidate['CARD_ID'];
                $choices = "";
                foreach($candidate['application_choices'] as $choice){
                    $choices.=$choice['PROGRAM_TITLE'].",";
                }
                $categories="";
                foreach($candidate['application_category'] as $category){
                    $categories.=$category['FORM_CATEGORY_NAME'].",";
                }
                $txt = "$CARD_ID,$APPLICATION_ID,$CPN,$USER_ID,$CNIC_NO,\"$FIRST_NAME\",\"$LAST_NAME\",\"$F_NAME\",$GENDER,$U_R,\"$DISTRICT_NAME\",$STATUS_ID,\"$choices\",\"$categories\"\n";
                fwrite($myfile, $txt);
                //prePrint($candidate['application_choices']);

                //exit();
            }
            fclose($myfile);
            prePrint("end time putting data into xls file".date("d-m-y h:i:s A"));

            $query_result = $this->MeritList_model->addList($form_array);
            $query_result = true;
            prePrint(count($selected_candidate));
            prePrint("writing merit list in database".date("d-m-y h:i:s A"));
            if($query_result){
                echo "<h1>Successfully Insert record</h1>";
            }else{
                echo "<h1>Something went wrong in insert</h1>";
            }

        }else{
            exit("FORM IDs Not Found");
        }

    }
    
    public function ForSpecialSelfGetDepartmentNextMeritList(&$all_candidate_results,&$discipline_seat_distribution,&$selected_candidate,&$not_selected_candidate,$campus_jurisdiction_list,$campus_id=1,$prog_type_id=1,$prev_selected_list = array()){

        $error_log_seat_distribution= fopen("merit_list/specail_self_error_log_seat_distribution ".date("d_m_y_h_i_s_A").".csv", "w") or die("Unable to open file!");
        foreach ($all_candidate_results as $i => $candidate ){
            $is_prev_self_selected = false;
            $pre_choice_no = 31;

            $CARD_ID = $candidate['CARD_ID'];
            $CPN = $candidate['CPN'];
            $candidate['IS_PROMOTED'] = 'N';
            $APPLICATION_ID = $candidate['APPLICATION_ID'];

            $prev_selected_program = null;
            $IS_SELF_DROP = false;
            if(isset($prev_selected_list[$APPLICATION_ID])){
                $prev_selected_program = $prev_selected_list[$APPLICATION_ID];

                if($prev_selected_program['IS_DROP']||$prev_selected_program['IS_RETAIN']){

                    continue;

                }
                else if($prev_selected_program['IS_SELF_DROP']){
                    $IS_SELF_DROP = true;
                    for($j = 0 ; $j<count($candidate['application_category']) ; $j++){
                        $f_cat_id = $candidate['application_category'][$j]['FORM_CATEGORY_ID'];
                        if($f_cat_id==SPECIAL_SELF_FINANCE){
                            unset($candidate['application_category'][$j]);
                            break;
                        }
                    }
                    // prePrint($candidate);

                }
                else if($prev_selected_program['IS_SELF_RETAIN']){

                    $PROG_LIST_ID = $prev_selected_program['PRE_SELECTED']['PROG_LIST_ID'];

                    $retaind_program = findObjectinList($candidate['application_choices'],'PROG_LIST_ID',$PROG_LIST_ID);
                    $candidate['application_choices'] = array($retaind_program);

                    for($j = 0 ; $j<count($candidate['application_category']) ; $j++){

                        $f_cat_id = $candidate['application_category'][$j]['FORM_CATEGORY_ID'];
                        if($f_cat_id==SPECIAL_SELF_FINANCE){
                            unset($candidate['application_category'][$j]);
                            break;
                        }
                    }

                }

                if($prev_selected_program['IS_SELF']&&$prev_selected_program['SELF_LAST_CHOICE']>0){

                    $pre_choice_no =$prev_selected_program['SELF_LAST_CHOICE'];
                    $is_prev_self_selected = true;
                    if($prev_selected_program['MERIT_LAST_CHOICE']!=-1){
                        $pre_choice_no_m =$prev_selected_program['MERIT_LAST_CHOICE'];
                        $current_choices = array();
                        foreach ($candidate['application_choices'] as $cur_choice){
                            if($cur_choice['CHOICE_NO']<$pre_choice_no_m){
                                $current_choices[]=$cur_choice;
                            }
                        }
                        $candidate['application_choices'] = $current_choices;
                    }
                }

                if($prev_selected_program['IS_MERIT']){
                    $pre_choice_no =$prev_selected_program['MERIT_LAST_CHOICE'];
                    $current_choices = array();
                    foreach ($candidate['application_choices'] as $cur_choice){
                        if($cur_choice['CHOICE_NO']<$pre_choice_no){
                            $current_choices[]=$cur_choice;
                        }
                    }
                    $candidate['application_choices'] = $current_choices;
                }
                $candidate['IS_PROMOTED'] = 'Y';

            }

            if($prev_selected_program&&!($prev_selected_program['IS_SELF']||$prev_selected_program['IS_MERIT'])){

                $prev_selected_program = null;

            }

            //$form_data = json_decode($candidate['FORM_DATA'],true);
            $users_reg = $candidate['users_reg'];
            $qualifications = $candidate['qualifications'];
            $DISTRICT_ID = $users_reg['DISTRICT_ID'];
            $PROVINCE_ID = $users_reg['PROVINCE_ID'];
            $FIRST_NAME = $users_reg['FIRST_NAME'];
            $GENDER = $users_reg['GENDER'];
            $U_R = $users_reg['U_R'];
            $CNIC_NO = $users_reg['CNIC_NO'];


            if(isset($candidate['applicants_minors'])&&isset($candidate['application_choices'])&&isset($candidate['application_category'])) {
                $applicants_minors =$candidate['applicants_minors'];
                $application_choices =$candidate['application_choices'];
                $application_category = $candidate['application_category'];
            }
            else{
                echo $candidate['APPLICATION_ID'].",";
                $application_category = array();
                $applicants_minors = array();
                $application_choices = array();
            }





            $is_seat_allot = false;
            $is_self_selected = false;

            if($PROVINCE_ID!=6){
                continue;
            }

            foreach ($application_choices as $choice) {
                $choice_prog_list_id = $choice['PROG_LIST_ID'];
                $CHOICE_NO = $choice['CHOICE_NO'];
                $this->check_current_choice_no =$CHOICE_NO;
                if(!$this->checkPreRequsiteUpgrade($choice_prog_list_id,$candidate,$prog_type_id)){
                    // break;
                    continue;
                }

                //find the index of program in discipline_seat_distribution
                $choice_prog_list_index = getIndexOfObjectInList($discipline_seat_distribution,'PROG_LIST_ID',$choice_prog_list_id);
                if($choice_prog_list_index<0){
                    //prePrint($choice);
                    //echo "$CHOICE_NO -> NOT FOUND INDEX<br>";
                    ///$myfile = $this->pre_req_log;
                    $txt = "Seat Distribution not found ,$CNIC_NO,$choice_prog_list_id\n";
                    fwrite($error_log_seat_distribution, $txt);
                    continue;
                    //exit("NOT FOUND INDEX");
                }

                $CATEGORIES =  &$discipline_seat_distribution[$choice_prog_list_index]['CATEGORIES'];



                    // prePrint("OTHER PROVINCE");
                    //  prePrint("$CARD_ID --> $FIRST_NAME --> $APPLICATION_ID");
                    $is_seat_allot = false;
                if (count($application_category) >= 1 && $is_seat_allot == false && $is_self_selected == false && ($is_prev_self_selected==false ||($is_prev_self_selected==true&&$pre_choice_no>$CHOICE_NO))) {
                    foreach ($application_category as $category) {

                        if ($category['FORM_CATEGORY_ID'] == SPECIAL_SELF_FINANCE) {

                            $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SPECIAL_SELF_FINANCE_CATEGORY_ID);
                            if ($j >= 0) {
                                if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                    $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                    $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                    array_push($selected_candidate, $object);
                                    $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected,SELF_FINANCE_FORM_CATEGORY_ID);
                                    $is_self_selected = true;
                                    $is_seat_allot = true;
                                    break;
                                }
                            }


                        }
                    }

                    if ($is_seat_allot) {
                        // $merit_count_el++;
                    } else {
                        //$merit_countN_nel++;
                    }
                }




                if($is_seat_allot){
                    break;
                }
            }
            if($is_seat_allot==false&&$is_self_selected == false){
                array_push($not_selected_candidate, $candidate);
            }





        }

        fclose($error_log_seat_distribution);


    }
    
    private function for_specail_self_filter_candidate(&$result_array,&$all_selected_student,&$candidates_fee_ledger,&$discipline_seat_distribution){
        foreach ($all_selected_student as $application_id=>$selected_student){

            $reserved_seat = array();
            $is_retain = false;
            $is_self_drop = false;
            $is_drop = false;
            $is_self  = false;
            $is_merit  = false;
            $is_self_retain = false;
            $MERIT_LAST_CHOICE = -1;
            $SELF_LAST_CHOICE = -1;
            if($selected_student['SELF'] && $selected_student['MERIT']){
                $MERIT_LAST_CHOICE = $selected_student['MERIT']['CHOICE_NO'];
                $SELF_LAST_CHOICE = $selected_student['SELF']['CHOICE_NO'];
                if(isset($candidates_fee_ledger[$application_id])){

                    $candidates_ledger = $candidates_fee_ledger[$application_id];

                    if($candidates_ledger['SELF_FEE']){
                        if($selected_student['MERIT']['CHOICE_NO']<=$selected_student['SELF']['CHOICE_NO']){
                            if($selected_student['MERIT']['CHOICE_NO']==1){
                                //REATAIN MERIT
                                $is_retain = true;
                            }
                            $is_merit = true;
                            array_push($reserved_seat , $selected_student['MERIT']);

                        }else{
                            $is_self  = true;

                            array_push($reserved_seat , $selected_student['SELF']);
                        }

                    }
                    else if($candidates_ledger['MERIT_FEE']){
                        $is_self_drop = true;
                        $is_merit = true;
                        if($selected_student['MERIT']['CHOICE_NO']==1){
                            //REATAIN MERIT
                            $is_retain = true;
                        }
                        array_push($reserved_seat , $selected_student['MERIT']);
                        //prePrint("SELF DROP OUT ");
                    }
                    else{
                        $is_drop = true;
                    }

                }
                else{
                    $is_drop = true;
                    //prePrint("DROP OUT ");
                }
            }
            else if($selected_student['SELF']){
                $SELF_LAST_CHOICE = $selected_student['SELF']['CHOICE_NO'];

                if(isset($candidates_fee_ledger[$application_id])){
                    $candidates_ledger = $candidates_fee_ledger[$application_id];
                    if($candidates_ledger['SELF_FEE']){
                        $is_self  = true;

                        array_push($reserved_seat , $selected_student['SELF']);
                    }else{
                        $is_self_drop = true;
                        // prePrint("SELF DROP OUT ");
                    }

                }else{
                    $is_self_drop = true;
                    //prePrint("SELF DROP OUT ");
                }
            }
            else if($selected_student['MERIT']){
                $MERIT_LAST_CHOICE = $selected_student['MERIT']['CHOICE_NO'];
                if(isset($candidates_fee_ledger[$application_id])){
                    $candidates_ledger = $candidates_fee_ledger[$application_id];
                    if($candidates_ledger['MERIT_FEE']){
                        $is_merit = true;
                        if($selected_student['MERIT']['CHOICE_NO']==1){
                            //REATAIN MERIT
                            $is_retain = true;
                        }
                        array_push($reserved_seat , $selected_student['MERIT']);

                    }else{
                        $is_drop = true;
                        // prePrint("DROP OUT ");
                    }
                }else{
                    $is_drop = true;
                    // prePrint("DROP OUT ");
                }
            }
            else{
                prePrint("SOMETHING WENT WRONG SENERIO MISTMATCH");
            }

            if(isset($candidates_fee_ledger[$application_id])&&count($reserved_seat)){
                $candidates_ledger =  $candidates_fee_ledger[$application_id];
                $PROG_LIST_ID = $reserved_seat[0]['PROG_LIST_ID'];

                if($is_merit&&$candidates_ledger['RETAIN_FEE']&&$candidates_ledger['RETAIN_FEE']['PROG_LIST_ID']==$PROG_LIST_ID){
                    $is_retain = true;
                }
                else if($is_self&&$candidates_ledger['RETAIN_FEE']&&$candidates_ledger['RETAIN_FEE']['PROG_LIST_ID']==$PROG_LIST_ID){
                    $is_self_retain = true;
                }

            }

            $result_array[$application_id]['IS_MERIT'] = $is_merit;
            $result_array[$application_id]['IS_SELF'] = $is_self;
            $result_array[$application_id]['IS_RETAIN'] = $is_retain;
            $result_array[$application_id]['IS_SELF_RETAIN'] = $is_self_retain;
            $result_array[$application_id]['IS_DROP'] = $is_drop;
            $result_array[$application_id]['IS_SELF_DROP'] = $is_self_drop;
            $result_array[$application_id]['MERIT_LAST_CHOICE'] = $MERIT_LAST_CHOICE;
            $result_array[$application_id]['SELF_LAST_CHOICE'] = $SELF_LAST_CHOICE;
            $result_array[$application_id]['LEDGER'] = isset($candidates_fee_ledger[$application_id])?$candidates_fee_ledger[$application_id]:null;

//            if($application_id==29136){
//                prePrint( $result_array[$application_id]);
//                exit() ;
//            }
            if($is_merit||$is_self){

                $result_array[$application_id]['PRE_SELECTED'] = $reserved_seat[0];

            }
            else{
                //drop student;
                $result_array[$application_id]['PRE_SELECTED'] = $selected_student;
                continue;
            }


            $selected_student = $reserved_seat[0];

            //  prePrint($selected_student);
            $selected_student['PROG_LIST_ID'];
            $selected_student['CATEGORY_ID'];
            $GENDER = $selected_student['GENDER'];
            $U_R =  $selected_student['U_R'];
            $index = getIndexOfObjectInList($discipline_seat_distribution,"PROG_LIST_ID",$selected_student['PROG_LIST_ID']);
            //prePrint($discipline_seat_distribution[$index]);
            //[IS_QUOTA] => N
            if($index>=0) {
                $discipline_seat_distribution[$index]['CATEGORIES'];

                if ($discipline_seat_distribution[$index]['IS_QUOTA'] == 'Y' && ($selected_student['CATEGORY_ID'] == GENERAL_MERIT_JUR || $selected_student['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR)) {
                    $district_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['DISTRICT_QUOTA'], 'DISTRICT_ID', $selected_student['DISTRICT_ID']);
                    if ($discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['RURAL_SEATS'] == 0 && $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['URBAN_SEATS'] == 0) {
                        $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['TOTAL_SEATS_REMAINING']--;
                    } else {
                        if ($selected_student['U_R'] == 'R') {

                            $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['RURAL_SEATS_REMAINING']--;

                        } else {
                            $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['URBAN_SEATS_REMAINING']--;
                        }
                        $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['TOTAL_SEATS_REMAINING']--;
                    }

                }
                else {
                    $cat_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['CATEGORIES'], "CATEGORY_ID", $selected_student['CATEGORY_ID']);
                    $discipline_seat_distribution[$index]['CATEGORIES'][$cat_index]['TOTAL_SEATS_REMAINING']--;
                    if ($selected_student['CATEGORY_ID'] == COMMERCE_QUOTA) {
                         if($selected_student['PROG_LIST_ID']==110 ){
                                 $discipline_seat_distribution[$index]['COMMERCE_QUOTA']--;
                            }else{
                                if ($GENDER == 'F') {
                                    $discipline_seat_distribution[$index]['COMMERCE_QUOTA']['F']--;
                                } else if ($GENDER == 'M') {
                                    $discipline_seat_distribution[$index]['COMMERCE_QUOTA']['M']--;
                                } 
                            }
                        
                    }
                }
            }

        }
    }
    
    public function ForEveningGenerateMeritList(){


        if(isset($_POST['PROG_LIST_ID'])&&count($_POST['PROG_LIST_ID'])&&isset($_POST['PROG_TYPE_ID'])&&isset($_POST['SHIFT_ID'])&&isset($_POST['YEAR'])&&isset($_POST['TEST_ID'])&&isset($_POST['CAMPUS_ID'])){
            $this->pre_req_log = fopen("merit_list/for_evening_error_log_for_prereq".$this->date.".csv", "w") or die("Unable to open file!");

            $session = $this->Admission_session_model->getSessionByYearData($_POST['YEAR']);
            $PROG_LIST_IDS = $_POST['PROG_LIST_ID'];
            $TEST_ID = $_POST['TEST_ID'];
            $campus_id = $_POST['CAMPUS_ID'];
            $shift_id = $_POST['SHIFT_ID'];
            $session_id =$session['SESSION_ID'] ;
            $prog_type_id = $_POST['PROG_TYPE_ID'];
            $ADMISSION_LIST_ID = $_POST['ADMISSION_LIST_ID'];
            $admission_list =  $this->Selection_list_report_model->get_admission_list_no_by_id($ADMISSION_LIST_ID);
            $first_merit_list = $admission_list['LIST_NO'];

            $merit_list_name = merit_list_decode($first_merit_list);
            $is_provisional = $_POST['IS_PROVISIONAL'];
            $admission_session_obj = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$prog_type_id);
            $admission_session_id = $admission_session_obj['ADMISSION_SESSION_ID'];
            $campus_name = $admission_session_obj['NAME'];

            prePrint("Start Time".date("d-m-y h:i:s A"));

            $pre_requisite_list = $this->Prerequisite_model->getPrerequisiteByProgramTypeId($prog_type_id);
            $minor_maping_list =  $this->Administration->getMinorMapping();

            $minor_maping_list_array = array();

            foreach ($minor_maping_list as $minor_maping){
                if(!isset($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']])||!is_array($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']])){
                    $minor_maping_list_array[$minor_maping['DISCIPLINE_ID']] = array();
                }
                array_push($minor_maping_list_array[$minor_maping['DISCIPLINE_ID']],$minor_maping);
            }

            $this->minor_maping_list = $minor_maping_list_array;


            $pre_requisite_list_array = array();

            foreach ($pre_requisite_list as $pre_req){
                if(!isset($pre_requisite_list_array[$pre_req['PROG_LIST_ID']])||!is_array($pre_requisite_list_array[$pre_req['PROG_LIST_ID']])){
                    $pre_requisite_list_array[$pre_req['PROG_LIST_ID']] = array();
                }
                array_push($pre_requisite_list_array[$pre_req['PROG_LIST_ID']],$pre_req);
            }

            $this->pre_requiste_list = $pre_requisite_list_array;



            $campus_jurisdiction_list = $this->Administration->getMappedCampusJurisdiction($campus_id);

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $candidates_fee_ledger = $this->ForEvening_model->ForEveningGetFeeLedger($admission_session_id,$shift_id,$session_id,$prog_type_id,$TEST_ID);
            //  prePrint($candidates_fee_ledger);
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $discipline_seat_distribution = $this->MeritList_model->getSeatDistribution($campus_id,$shift_id,$session_id,$prog_type_id,$PROG_LIST_IDS);

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
            $all_selected_student = $this->ForEvening_model->ForEveningGetSelectedStudent($admission_session_id,$shift_id,$session_id,$prog_type_id,$TEST_ID);
            //prePrint($all_selected_student);
        //    exit();
            $prev_selected_list = array();
            prePrint("get selected Start Time".date("d-m-y h:i:s A"));
        //     prePrint($candidates_fee_ledger);
           //  prePrint($discipline_seat_distribution);
             
            $this->for_evening_filter_candidate($prev_selected_list,$all_selected_student,$candidates_fee_ledger,$discipline_seat_distribution);

            prePrint("get selected Start Time".date("d-m-y h:i:s A"));


            $all_candidate_results_sort = $this->ForEvening_model->ForGetListOfStudentByTestIdAndCampusIdAndShiftId($TEST_ID,$campus_id,$shift_id);
           // prePrint($discipline_seat_distribution);
         //      prePrint($all_candidate_results_sort);

          //      exit();
            prePrint("after sorting Time".date("d-m-y h:i:s A"));
            prePrint("ALL STUDENT after sorting " . count($all_candidate_results_sort));

            $selected_candidate = array();
           $not_selected_candidate = array();
//             prePrint($discipline_seat_distribution);
  //           prePrint($prev_selected_list);
            // prePrint($all_candidate_results_sort);
            
           // exit();
             

            $this->ForEveningGetDepartmentNextMeritList($all_candidate_results_sort,$discipline_seat_distribution,$selected_candidate,$not_selected_candidate,$campus_jurisdiction_list,$campus_id,$prog_type_id,$prev_selected_list);
            prePrint("End Time department merit list".date("d-m-y h:i:s A"));
    //        prePrint($selected_candidate);
           
      //       exit();
             
            $myfile  = fopen("merit_list/FOR-EVENING-SELECTED-CANDIDATE-$merit_list_name-LIST-$campus_name-".date("d_m_y_h_i_s_A").".csv",'w+');
            $txt = "CARD_ID,APPLICATION_ID,CPN,USER_ID,CNIC_NO,FIRST_NAME,LAST_NAME,FNAME,GENDER,U_R,DISTRICT_NAME,CATEGORY_NAME,PROGRAM_TITLE,CHOICE_NO,CAMPUS_NAME,PROG_LIST_ID,STATUS,SSC_OBT,SSC_TOT,SSC_GRP,SSC_P_YEAR,SSC_BORAD,HSC_OBT,HSC_TOT,HSC_GRP,HSC_P_YEAR,HSC_BORAD,IS_PROMOTED,MOBILE_NO,EMAIL\n";
            fwrite($myfile, $txt);
            $form_array = array();

            foreach ($selected_candidate as $candidate){



                $candidate_info = $candidate['candidate'];
                ///$form_data = json_decode($candidate_info['FORM_DATA'],true);
                $IS_PROMOTED = $candidate_info['IS_PROMOTED'];
                $users_reg = $candidate_info['users_reg'];
                $qualifications = $candidate_info['qualifications'];
                $inter = findObjectinList($qualifications,"DEGREE_ID",3);
                $metric = findObjectinList($qualifications,"DEGREE_ID",2);

                $FIRST_NAME =  $users_reg['FIRST_NAME'];
                $LAST_NAME =  $users_reg['LAST_NAME'];
                $F_NAME =  $users_reg['FNAME'];
                $CNIC_NO =  $users_reg['CNIC_NO'];
                $MOBILE_NO =  "0".$users_reg['MOBILE_NO'];
                $EMAIL =  $users_reg['EMAIL'];
                $GENDER =  $users_reg['GENDER'];
                $U_R =  $users_reg['U_R'];
                $DISTRICT_NAME =  $users_reg['DISTRICT_NAME'];
                $CATEGORY_NAME = $candidate['CATEGORY']['CATEGORY_NAME'];
                $PROGRAM_TITLE = $candidate['CATEGORY']['PROGRAM_TITLE'];
                $CAMPUS_NAME = $candidate['CATEGORY']['NAME'];
                $PROG_LIST_ID = $candidate['CATEGORY']['PROG_LIST_ID'];
                $CATEGORY_ID = $candidate['CATEGORY']['CATEGORY_ID'];
                $APPLICATION_ID = $candidate['APPLICATION_ID'];
                $USER_ID = $candidate['USER_ID'];
                $CPN = $candidate_info['CPN'];
                $DETAIL_CPN = $candidate_info['DETAIL_CPN'];
                $CARD_ID = $candidate_info['CARD_ID'];
                $ADMISSION_SESSION_ID = $candidate_info['ADMISSION_SESSION_ID'];
                $STATUS_ID = $candidate_info['STATUS_ID'];
                $CHOICE_NO = $candidate['CHOICE_NO'];
                $DISTRICT_ID =  $users_reg['DISTRICT_ID'];

                $inter_csv = "{$inter['OBTAINED_MARKS']},{$inter['TOTAL_MARKS']},{$inter['DISCIPLINE_NAME']},{$inter['PASSING_YEAR']},\"{$inter['ORGANIZATION']}\"";
                $metric_csv = "{$metric['OBTAINED_MARKS']},{$metric['TOTAL_MARKS']},{$metric['DISCIPLINE_NAME']},{$metric['PASSING_YEAR']},\"{$metric['ORGANIZATION']}\"";


                $txt = "$CARD_ID,$APPLICATION_ID,$CPN,$USER_ID,$CNIC_NO,\"$FIRST_NAME\",\"$LAST_NAME\",\"$F_NAME\",$GENDER,$U_R,\"$DISTRICT_NAME\",\"$CATEGORY_NAME\",\"$PROGRAM_TITLE\",$CHOICE_NO,\"$CAMPUS_NAME\",$PROG_LIST_ID,$STATUS_ID,$metric_csv,$inter_csv,$IS_PROMOTED,$MOBILE_NO,\"$EMAIL\"\n";

                $DETAIL_CPN = "";
                $list = array(
                    "APPLICATION_ID"=>$APPLICATION_ID,
                    "TEST_ID"=>$TEST_ID,
                    "SHIFT_ID"=>$shift_id,
                    
                    "CHOICE_NO"=>$CHOICE_NO,
                    
                    "PROG_LIST_ID"=>$PROG_LIST_ID,
                    "CATEGORY_ID"=>$CATEGORY_ID,
                    "CARD_ID"=>$CARD_ID,
                    "ADMISSION_SESSION_ID"=>$ADMISSION_SESSION_ID,
                    "CPN"=>$CPN,
                    "ACTIVE"=>1,
                    "IS_PROVISIONAL"=>$is_provisional,
                    "ADMISSION_LIST_ID"=>$ADMISSION_LIST_ID
                );
                $form_array[] = $list;
                fwrite($myfile, $txt);
            }
            fclose($myfile);

            $myfile  = fopen("merit_list/FOR-EVENING-NOT-SELECTED-CANDIDATE-$merit_list_name-LIST-$campus_name-".date("d_m_y_h_i_s_A").".csv",'w+');
            $txt = "CARD_ID,APPLICATION_ID,CPN,USER_ID,CNIC_NO,FIRST_NAME,LAST_NAME,FNAME,GENDER,U_R,DISTRICT_NAME,STATUS,PROGRAMS_CHOICE,CATEGORY\n";
            fwrite($myfile, $txt);
            foreach($not_selected_candidate as $candidate){
               // $form_data = json_decode($candidate['FORM_DATA'],true);

                $users_reg = $candidate['users_reg'];

                $FIRST_NAME =  $users_reg['FIRST_NAME'];
                $LAST_NAME =  $users_reg['LAST_NAME'];
                $F_NAME =  $users_reg['FNAME'];
                $CNIC_NO =  $users_reg['CNIC_NO'];
                $GENDER =  $users_reg['GENDER'];
                $U_R =  $users_reg['U_R'];
                $DISTRICT_NAME =  $users_reg['DISTRICT_NAME'];
                $STATUS_ID = $candidate['STATUS_ID'];
                $CPN = $candidate['CPN'];
                $APPLICATION_ID = $candidate['APPLICATION_ID'];
                $USER_ID = $candidate['USER_ID'];
                $CARD_ID = $candidate['CARD_ID'];
                $choices = "";
                foreach($candidate['application_choices'] as $choice){
                    $choices.=$choice['PROGRAM_TITLE'].",";
                }
                $categories="";
                foreach($candidate['application_category'] as $category){
                    $categories.=$category['FORM_CATEGORY_NAME'].",";
                }
                $txt = "$CARD_ID,$APPLICATION_ID,$CPN,$USER_ID,$CNIC_NO,\"$FIRST_NAME\",\"$LAST_NAME\",\"$F_NAME\",$GENDER,$U_R,\"$DISTRICT_NAME\",$STATUS_ID,\"$choices\",\"$categories\"\n";
                fwrite($myfile, $txt);
                //prePrint($candidate['application_choices']);

                //exit();
            }
            fclose($myfile);
            prePrint("end time putting data into xls file".date("d-m-y h:i:s A"));

            $query_result = $this->MeritList_model->addList($form_array);
            $query_result = true;
            prePrint(count($selected_candidate));
            prePrint("writing merit list in database".date("d-m-y h:i:s A"));
            if($query_result){
                echo "<h1>Successfully Insert record</h1>";
            }else{
                echo "<h1>Something went wrong in insert</h1>";
            }

        }else{
            exit("FORM IDs Not Found");
        }

    }
    
    public function ForEveningGetDepartmentNextMeritList(&$all_candidate_results,&$discipline_seat_distribution,&$selected_candidate,&$not_selected_candidate,$campus_jurisdiction_list,$campus_id=1,$prog_type_id=1,$prev_selected_list = array()){

        $error_log_seat_distribution= fopen("merit_list/evening_error_log_seat_distribution ".date("d_m_y_h_i_s_A").".csv", "w") or die("Unable to open file!");
        foreach ($all_candidate_results as $i => $candidate ){
            $is_prev_self_selected = false;
            $pre_choice_no = 31;

            $CARD_ID = $candidate['CARD_ID'];
            $CPN = $candidate['CPN'];
            $candidate['IS_PROMOTED'] = 'N';
            $APPLICATION_ID = $candidate['APPLICATION_ID'];

            $prev_selected_program = null;
            $IS_SELF_DROP = false;
            
            if(isset($prev_selected_list[$APPLICATION_ID])){
                $prev_selected_program = $prev_selected_list[$APPLICATION_ID];

                if($prev_selected_program['IS_DROP']||$prev_selected_program['IS_RETAIN']){

                    continue;

                }
                else if($prev_selected_program['IS_SELF_DROP']){
                    $IS_SELF_DROP = true;
                      continue;
                    for($j = 0 ; $j<count($candidate['application_category']) ; $j++){
                        $f_cat_id = $candidate['application_category'][$j]['FORM_CATEGORY_ID'];
                        if($f_cat_id==SELF_FINANCE_EVENING){
                            unset($candidate['application_category'][$j]);
                            break;
                        }
                    }
                    // prePrint($candidate);

                }
                else if($prev_selected_program['IS_SELF_RETAIN']){
                      continue;
                    $PROG_LIST_ID = $prev_selected_program['PRE_SELECTED']['PROG_LIST_ID'];

                    $retaind_program = findObjectinList($candidate['application_choices'],'PROG_LIST_ID',$PROG_LIST_ID);
                    $candidate['application_choices'] = array($retaind_program);

                    for($j = 0 ; $j<count($candidate['application_category']) ; $j++){

                        $f_cat_id = $candidate['application_category'][$j]['FORM_CATEGORY_ID'];
                        if($f_cat_id==SELF_FINANCE_EVENING){
                            unset($candidate['application_category'][$j]);
                            break;
                        }
                    }

                }

                if($prev_selected_program['IS_SELF']&&$prev_selected_program['SELF_LAST_CHOICE']>0){

                    $pre_choice_no =$prev_selected_program['SELF_LAST_CHOICE'];
                    $is_prev_self_selected = true;
                    if($prev_selected_program['MERIT_LAST_CHOICE']!=-1){
                        $pre_choice_no_m =$prev_selected_program['MERIT_LAST_CHOICE'];
                        $current_choices = array();
                        foreach ($candidate['application_choices'] as $cur_choice){
                            if($cur_choice['CHOICE_NO']<$pre_choice_no_m){
                                $current_choices[]=$cur_choice;
                            }
                        }
                        $candidate['application_choices'] = $current_choices;
                    }
                }

                if($prev_selected_program['IS_MERIT']){
                    $pre_choice_no =$prev_selected_program['MERIT_LAST_CHOICE'];
                    $current_choices = array();
                    foreach ($candidate['application_choices'] as $cur_choice){
                        if($cur_choice['CHOICE_NO']<$pre_choice_no){
                            $current_choices[]=$cur_choice;
                        }
                    }
                    $candidate['application_choices'] = $current_choices;
                }
                $candidate['IS_PROMOTED'] = 'Y';

            }

            if($prev_selected_program&&!($prev_selected_program['IS_SELF']||$prev_selected_program['IS_MERIT'])){

                $prev_selected_program = null;

            }
            
          //  $form_data = json_decode($candidate['FORM_DATA'],true);
            $users_reg = $candidate['users_reg'];
            $qualifications = $candidate['qualifications'];
            $DISTRICT_ID = $users_reg['DISTRICT_ID'];
            $PROVINCE_ID = $users_reg['PROVINCE_ID'];
            $FIRST_NAME = $users_reg['FIRST_NAME'];
            $GENDER = $users_reg['GENDER'];
            $U_R = $users_reg['U_R'];
            $CNIC_NO = $users_reg['CNIC_NO'];


            if(isset($candidate['applicants_minors'])&&isset($candidate['application_choices'])&&isset($candidate['application_category'])) {
                $applicants_minors =$candidate['applicants_minors'];
                $application_choices =$candidate['application_choices'];
                $application_category =$candidate['application_category'];
            }
            else{
                echo $candidate['APPLICATION_ID'].",";
                $application_category = array();
                $applicants_minors = array();
                $application_choices = array();
            }





            $is_seat_allot = false;
            $is_self_selected = false;

//            if($PROVINCE_ID!=6){
//                continue;
//            }

            foreach ($application_choices as $choice) {
                $choice_prog_list_id = $choice['PROG_LIST_ID'];
                $CHOICE_NO = $choice['CHOICE_NO'];
                $this->check_current_choice_no =$CHOICE_NO;
                if(!$this->checkPreRequsiteUpgrade($choice_prog_list_id,$candidate,$prog_type_id)){
                    echo "PRE-REQUISTE   ";
                    prePrint($candidate);
                    // break;
                    continue;
                }

                //find the index of program in discipline_seat_distribution
                $choice_prog_list_index = getIndexOfObjectInList($discipline_seat_distribution,'PROG_LIST_ID',$choice_prog_list_id);
                if($choice_prog_list_index<0){
                    //prePrint($choice);
                    //echo "$CHOICE_NO -> NOT FOUND INDEX<br>";
                    ///$myfile = $this->pre_req_log;
                    $txt = "Seat Distribution not found ,$CNIC_NO,$choice_prog_list_id\n";
                    fwrite($error_log_seat_distribution, $txt);
                    continue;
                    //exit("NOT FOUND INDEX");
                }

                $CATEGORIES =  &$discipline_seat_distribution[$choice_prog_list_index]['CATEGORIES'];



                // prePrint("OTHER PROVINCE");
                //  prePrint("$CARD_ID --> $FIRST_NAME --> $APPLICATION_ID");
                $is_seat_allot = false;
                if (count($application_category) >= 1 && $is_seat_allot == false && $is_self_selected == false && ($is_prev_self_selected==false ||($is_prev_self_selected==true&&$pre_choice_no>$CHOICE_NO))) {
                    foreach ($application_category as $category) {

                        if ($category['FORM_CATEGORY_ID'] == SELF_FINANCE_EVENING) {

                            $j = getIndexOfObjectInList($CATEGORIES, 'CATEGORY_ID', SELF_FINANCE_EVENING_CATEGORY_ID);
                            if ($j >= 0) {
                                if ($CATEGORIES[$j]['TOTAL_SEATS_REMAINING'] > 0) {

                                    $CATEGORIES[$j]['TOTAL_SEATS_REMAINING']--;

                                    $object = array("candidate" => $candidate,"APPLICATION_ID"=>$candidate['APPLICATION_ID'],"USER_ID"=>$candidate['USER_ID'], "PROG_LIST_ID" => $choice_prog_list_id, "CHOICE_NO" => $CHOICE_NO, "CATEGORY" => $CATEGORIES[$j]);
                                    array_push($selected_candidate, $object);
                                    $this->remove_seats($prev_selected_program,$discipline_seat_distribution,$is_prev_self_selected,SELF_FINANCE_EVENING);
                                    $is_self_selected = true;
                                    $is_seat_allot = true;
                                    break;
                                }
                            }


                        }
                    }

                    if ($is_seat_allot) {
                        // $merit_count_el++;
                    } else {
                        //$merit_countN_nel++;
                    }
                }




                if($is_seat_allot){
                    break;
                }
            }
            if($is_seat_allot==false&&$is_self_selected == false){
                array_push($not_selected_candidate, $candidate);
            }





        }

        fclose($error_log_seat_distribution);


    }
    
    private function for_evening_filter_candidate(&$result_array,&$all_selected_student,&$candidates_fee_ledger,&$discipline_seat_distribution){
        foreach ($all_selected_student as $application_id=>$selected_student){

            $reserved_seat = array();
            $is_retain = false;
            $is_self_drop = false;
            $is_drop = false;
            $is_self  = false;
            $is_merit  = false;
            $is_self_retain = false;
            $MERIT_LAST_CHOICE = -1;
            $SELF_LAST_CHOICE = -1;
            if($selected_student['SELF'] && $selected_student['MERIT']){
                $MERIT_LAST_CHOICE = $selected_student['MERIT']['CHOICE_NO'];
                $SELF_LAST_CHOICE = $selected_student['SELF']['CHOICE_NO'];
                if(isset($candidates_fee_ledger[$application_id])){

                    $candidates_ledger = $candidates_fee_ledger[$application_id];

                    if($candidates_ledger['SELF_FEE']){
                        if($selected_student['MERIT']['CHOICE_NO']<=$selected_student['SELF']['CHOICE_NO']){
                            if($selected_student['MERIT']['CHOICE_NO']==1){
                                //REATAIN MERIT
                                $is_retain = true;
                            }
                            $is_merit = true;
                            array_push($reserved_seat , $selected_student['MERIT']);

                        }else{
                            $is_self  = true;

                            array_push($reserved_seat , $selected_student['SELF']);
                        }

                    }
                    else if($candidates_ledger['MERIT_FEE']){
                        $is_self_drop = true;
                        $is_merit = true;
                        if($selected_student['MERIT']['CHOICE_NO']==1){
                            //REATAIN MERIT
                            $is_retain = true;
                        }
                        array_push($reserved_seat , $selected_student['MERIT']);
                        //prePrint("SELF DROP OUT ");
                    }
                    else{
                        $is_drop = true;
                    }

                }
                else{
                    $is_drop = true;
                    //prePrint("DROP OUT ");
                }
            }
            else if($selected_student['SELF']){
                $SELF_LAST_CHOICE = $selected_student['SELF']['CHOICE_NO'];

                if(isset($candidates_fee_ledger[$application_id])){
                    $candidates_ledger = $candidates_fee_ledger[$application_id];
                    if($candidates_ledger['SELF_FEE']){
                        $is_self  = true;

                        array_push($reserved_seat , $selected_student['SELF']);
                    }else{
                        $is_self_drop = true;
                        // prePrint("SELF DROP OUT ");
                    }

                }else{
                    $is_self_drop = true;
                    //prePrint("SELF DROP OUT ");
                }
            }
            else if($selected_student['MERIT']){
                $MERIT_LAST_CHOICE = $selected_student['MERIT']['CHOICE_NO'];
                if(isset($candidates_fee_ledger[$application_id])){
                    $candidates_ledger = $candidates_fee_ledger[$application_id];
                    if($candidates_ledger['MERIT_FEE']){
                        $is_merit = true;
                        if($selected_student['MERIT']['CHOICE_NO']==1){
                            //REATAIN MERIT
                            $is_retain = true;
                        }
                        array_push($reserved_seat , $selected_student['MERIT']);

                    }else{
                        $is_drop = true;
                        // prePrint("DROP OUT ");
                    }
                }else{
                    $is_drop = true;
                    // prePrint("DROP OUT ");
                }
            }
            else{
                prePrint("SOMETHING WENT WRONG SENERIO MISTMATCH");
            }

            if(isset($candidates_fee_ledger[$application_id])&&count($reserved_seat)){
                $candidates_ledger =  $candidates_fee_ledger[$application_id];
                $PROG_LIST_ID = $reserved_seat[0]['PROG_LIST_ID'];

                if($is_merit&&$candidates_ledger['RETAIN_FEE']&&$candidates_ledger['RETAIN_FEE']['PROG_LIST_ID']==$PROG_LIST_ID){
                    $is_retain = true;
                }
                else if($is_self&&$candidates_ledger['RETAIN_FEE']&&$candidates_ledger['RETAIN_FEE']['PROG_LIST_ID']==$PROG_LIST_ID){
                    $is_self_retain = true;
                }

            }

            $result_array[$application_id]['IS_MERIT'] = $is_merit;
            $result_array[$application_id]['IS_SELF'] = $is_self;
            $result_array[$application_id]['IS_RETAIN'] = $is_retain;
            $result_array[$application_id]['IS_SELF_RETAIN'] = $is_self_retain;
            $result_array[$application_id]['IS_DROP'] = $is_drop;
            $result_array[$application_id]['IS_SELF_DROP'] = $is_self_drop;
            $result_array[$application_id]['MERIT_LAST_CHOICE'] = $MERIT_LAST_CHOICE;
            $result_array[$application_id]['SELF_LAST_CHOICE'] = $SELF_LAST_CHOICE;
            $result_array[$application_id]['LEDGER'] = isset($candidates_fee_ledger[$application_id])?$candidates_fee_ledger[$application_id]:null;

//            if($application_id==29136){
//                prePrint( $result_array[$application_id]);
//                exit() ;
//            }
            if($is_merit||$is_self){

                $result_array[$application_id]['PRE_SELECTED'] = $reserved_seat[0];

            }
            else{
                //drop student;
                $result_array[$application_id]['PRE_SELECTED'] = $selected_student;
                continue;
            }


            $selected_student = $reserved_seat[0];

            //  prePrint($selected_student);
            $selected_student['PROG_LIST_ID'];
            $selected_student['CATEGORY_ID'];
            $GENDER = $selected_student['GENDER'];
            $U_R =  $selected_student['U_R'];
            $index = getIndexOfObjectInList($discipline_seat_distribution,"PROG_LIST_ID",$selected_student['PROG_LIST_ID']);
            //prePrint($discipline_seat_distribution[$index]);
            //[IS_QUOTA] => N
            if($index>=0) {
                $discipline_seat_distribution[$index]['CATEGORIES'];

                if ($discipline_seat_distribution[$index]['IS_QUOTA'] == 'Y' && ($selected_student['CATEGORY_ID'] == GENERAL_MERIT_JUR || $selected_student['CATEGORY_ID'] == GENERAL_MERIT_OUT_JUR)) {
                    $district_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['DISTRICT_QUOTA'], 'DISTRICT_ID', $selected_student['DISTRICT_ID']);
                    if ($discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['RURAL_SEATS'] == 0 && $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['URBAN_SEATS'] == 0) {
                        $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['TOTAL_SEATS_REMAINING']--;
                    } else {
                        if ($selected_student['U_R'] == 'R') {

                            $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['RURAL_SEATS_REMAINING']--;

                        } else {
                            $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['URBAN_SEATS_REMAINING']--;
                        }
                        $discipline_seat_distribution[$index]['DISTRICT_QUOTA'][$district_index]['TOTAL_SEATS_REMAINING']--;
                    }

                }
                else {
                    $cat_index = getIndexOfObjectInList($discipline_seat_distribution[$index]['CATEGORIES'], "CATEGORY_ID", $selected_student['CATEGORY_ID']);
                    $discipline_seat_distribution[$index]['CATEGORIES'][$cat_index]['TOTAL_SEATS_REMAINING']--;
                    if ($selected_student['CATEGORY_ID'] == COMMERCE_QUOTA) {
                         if($selected_student['PROG_LIST_ID']==110 ){
                                 $discipline_seat_distribution[$index]['COMMERCE_QUOTA']--;
                            }else{
                                if ($GENDER == 'F') {
                                    $discipline_seat_distribution[$index]['COMMERCE_QUOTA']['F']--;
                                } else if ($GENDER == 'M') {
                                    $discipline_seat_distribution[$index]['COMMERCE_QUOTA']['M']--;
                                } 
                            }
                        
                    }
                }
            }

        }
    }

    /**
    * YASIR CREATED NEW METHODS 22-02-2021
    * */

	public function BookletAdmission(){

		$user = $this->session->userdata($this->SessionName);
		$user_role = $this->session->userdata($this->user_role);
		$user_id = $user['USER_ID'];
		$role_id = $user_role['ROLE_ID'];

		$side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
		$this->verify_path($this->script_name,$side_bar_data);

// 		$data['district_list'] = $district_list;
		$data['user'] = $user;
		$data['profile_url'] = '';
		$data['side_bar_values'] = $side_bar_data;
		$data['script_name'] = $this->script_name;

		$this->load->view('include/header',$data);
		$this->load->view('include/preloder');
		$this->load->view('include/side_bar',$data);
		$this->load->view('include/nav',$data);
		$this->load->view('admin/admission_booklet',$data);
//		$this->load->view('include/footer_area');
		$this->load->view('include/footer');

	}
	
	public function getApplicantDataForBookletAdmission(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id 	= isValidData($request->program_type_id);
		$shift_id 			= isValidData($request->shift_id);
		$campus_id 			= isValidData($request->campus_id);
		$session_id 		= isValidData($request->session_id);
		$application_id		= isValidData($request->application_id);
		$test_id			= isValidData($request->test_id);

		$error = "";
		if (empty($program_type_id))
			$error.="Program Type is Required";
		elseif (empty($shift_id))
			$error.="Shift is Required";
		elseif (empty($campus_id))
			$error.="Campus is Required";
		elseif (empty($session_id))
			$error.="Session is Required";
		elseif (empty($application_id))
			$error.="Application is Required";
		elseif (empty($test_id))
			$error.="Test Type is Required";


		if (empty($error)){
			$application_data = $this->Application_model->getApplicationByApplicationID($application_id);
			if (empty($application_data)){
				http_response_code(204);
				$this->output->set_content_type('application/json')->set_output(json_encode('Application not found...'));
			}else{
			$user_id = $application_data['USER_ID'];
			$record = $this->User_model->getUserFullDetailWithChoiceById($user_id,$application_id,$shift_id);
			$selection_list = $this->Selection_list_report_model->get_candidate_selection_list_from_selection_list_table($user_id,$application_id,$session_id,$program_type_id,$shift_id,0);
			$test_result = $this->TestResult_model->getTestResultWithCpn($test_id,$application_id);
			$application_minor = $record['applicants_minors'];
			$pre_req = array();
			foreach ($application_minor as $minor){
				$minor_mapping_id = $minor['MINOR_MAPPING_ID'];
				array_push($pre_req,$this->Prerequisite_model->getPrerequisite_minor_mapping_id ($minor_mapping_id));
			}
			$new_array_choice = array();
			foreach ($record['application_choices'] as $choices){
					$is_special = $choices['IS_SPECIAL_CHOICE'];
					if ($is_special == "N")
						$is_special = "";
					elseif ($is_special == "Y")
						$is_special = "SPE SELF";
				$choices['IS_SPECIAL_CHOICE'] = $is_special;
					array_push($new_array_choice,$choices);
				}

				$record['pre_req'] = $pre_req;
				$record['application_choices']=$new_array_choice;
				$record['selection_list']=$selection_list;
				$record['cpn']=$test_result['CPN'];
//			prePrint($record);
//			exit();
			}
		}else{
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode($error));
		}
		if (empty($record)){
			http_response_code(204);
			$this->output->set_content_type('application/json')->set_output(json_encode("Failed..."));
		}else{
			http_response_code(200);
			$this->output->set_content_type('application/json')->set_output(json_encode($record));
		}
	}//method
	
	public function printVacantSeatReport(){
//		$this->load->view('include/header');
		$this->load->view('admin/vacant_seats_report_print.html');
	}
	
	public function SaveApplicantDataForBookletAdmission(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$application_id 	= isValidData($request->application_id);
		$shift_id 			= isValidData($request->shift_id);
		$campus_id 			= isValidData($request->campus_id);
		$session_id 		= isValidData($request->session_id);
		$program_type_id 	= isValidData($request->program_type_id);
		$program_id 		= isValidData($request->program_id);
		$VacantSeats 		= isValidData($request->VacantSeats);
		$admission_list_id 	= isValidData($request->AdmissionListModel);
		$category_id 		= isValidData($request->Category);
		$CnicNo 			= isValidData($request->CnicNo);
		$RollNo 			= isValidData($request->RollNo);
		$selected_choice 	= isValidData($request->FormChoices);
		$Remarks		 	= isValidData($request->Remarks);
		$test_id		 	= isValidData($request->test_id);

		$error = "";
		if (empty($program_type_id))
			$error.="Program Type is Required";
		elseif (empty($shift_id))
			$error.="Shift is Required";
		elseif (empty($campus_id))
			$error.="Campus is Required";
		elseif (empty($session_id))
			$error.="Session is Required";
		elseif (empty($application_id))
			$error.="Application is Required";
		elseif (empty($program_id))
			$error.="Subject is Required";
		elseif (empty($VacantSeats))
			$error.="Vacant Seat is Required";
		elseif (empty($admission_list_id))
			$error.="Admission List is Required";
		elseif (empty($category_id))
			$error.="Category is Required";
		elseif (empty($CnicNo))
			$error.="Cnic No is Required";
		elseif (empty($selected_choice))
			$error.="Selected Choice is Required";
		elseif (empty($test_id))
			$error.="Test Type is Required";

		if (empty($error)){
			$application_data = $this->Application_model->getApplicationByApplicationID($application_id);
			$admission_session_data = $this->Admission_session_model->getAdmissionSessionID($session_id,$campus_id,$program_type_id);
			$admit_card = $this->AdmitCard_model->getAdmitCardOnAppID ($application_id);
			$test_result = $this->TestResult_model->getTestResultWithCpn($test_id,$application_id);
			$selected_category_detail = $this->Administration->MappedCategory (0,$category_id);
			$selected_category_detail = $selected_category_detail[0];

			if (empty($application_data) || empty($admission_session_data) || empty($admit_card) || empty($test_result) || empty($selected_category_detail)){
				http_response_code(204);
				$this->output->set_content_type('application/json')->set_output(json_encode('Application data not found...'));
			}else{
				$form_data = json_decode($application_data['FORM_DATA'],true);
				$users_reg = $form_data['users_reg'];
				// $applicant_choices = $form_data['application_choices'];
				$applicant_choices = $this->Application_model->getChoiceByUserAndApplicationAndShiftId(0,$application_id,$shift_id);
			
				$admission_session_id=$admission_session_data['ADMISSION_SESSION_ID'];
				$campus_name=$admission_session_data['NAME'];
				$choice_array_index = getIndexOfObjectInList($applicant_choices,'CHOICE_ID',$selected_choice);
				$new_selection_choice = $applicant_choices[$choice_array_index];
			
				$All_admission_lists = $this->Selection_list_report_model->get_admission_list_no ($admission_session_id,$shift_id);
				$get_anc_list_index = getIndexOfObjectInList($All_admission_lists,'ADMISSION_LIST_ID',$admission_list_id);
				$All_admission_lists = $All_admission_lists[$get_anc_list_index];

				if ($new_selection_choice['PROG_LIST_ID'] != $program_id){
					http_response_code(201);
					exit('Selected Choice does not match with subjected program...');
				}elseif(empty($VacantSeats) || $VacantSeats <1){
					http_response_code(201);
					exit('Sorry vacant seat not found...');
				}elseif(empty($All_admission_lists)){
					http_response_code(201);
					exit('Announced admission list not found...');
				}else{
					$subject_prog_list_id = $new_selection_choice['PROG_LIST_ID'];
					$program_title = $new_selection_choice['PROGRAM_TITLE'];
					$choice_no = $new_selection_choice['CHOICE_NO'];
					$list_no = $All_admission_lists['LIST_NO'];
				}

				$selection_array = array(
					'SHIFT_ID'=>$shift_id,
					//'SESSION_ID'=>$session_id,
					'ADMISSION_SESSION_ID'=>$admission_session_id,
					//'USER_ID'=>$users_reg['USER_ID'],
					'APPLICATION_ID'=>$application_id,
					//'LIST_NO'=>$list_no,
					'PROG_LIST_ID'=>$subject_prog_list_id,
					'CATEGORY_ID'=>$category_id,
					//'DISTRICT_ID'=>$users_reg['DISTRICT_ID'],
					'CHOICE_NO'=>$choice_no,
					'ACTIVE'=>1,
					'TEST_ID'=>$test_id,
					'CARD_ID'=>$admit_card['CARD_ID'],
					//'CNIC_NO'=>$users_reg['CNIC_NO'],
					//'FIRST_NAME'=>$users_reg['FIRST_NAME'],
					//'LAST_NAME'=>$users_reg['LAST_NAME'],
				    //'FNAME'=>$users_reg['FNAME'],
					//'GENDER'=>$users_reg['GENDER'],
					//'U_R'=>$users_reg['U_R'],
					//'DISTRICT_NAME'=>$users_reg['DISTRICT_NAME'],
					//'CATEGORY_NAME'=>$selected_category_detail['CATEGORY_NAME'],
					//'PROGRAM_TITLE'=>$program_title,
					//'CAMPUS_NAME'=>$campus_name,
					'CPN'=>$test_result['CPN'],
					'IS_PROVISIONAL'=>'N',
					'ADMISSION_LIST_ID'=>$admission_list_id
				);
				$op = $this->session->userdata($this->SessionName);
				$op_id= $op['USER_ID'];

				$return = $this->MeritList_model->saveBookletAdmission($selection_array,$op_id);
				if ($return) $return="Successfully Saved...";
				else $return="Failed";
			}
		}else{
			http_response_code(201);
			echo $error;
			exit();
		}
		if (empty($return)){
			http_response_code(201);
			echo 'Failed...';
			exit();
		}else{
			http_response_code(200);
			echo  $return;
			exit();
		}
	}//method
		function add_data_in_candidate_selection_batch($data){
        $this->legacy_db = $this->load->database("admission_db",true);
        return $this->legacy_db->insert_batch('selection_list_candidate', $data); 
        
    }
}

