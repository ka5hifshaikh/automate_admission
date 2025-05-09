<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 11/17/2020
 * Time: 12:31 PM
 */
class TestResult_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
//		$CI =& get_instance();
        $this->load->model('log_model');
        $this->legacy_db = $this->load->database('admission_db',true);
    }
    function getTestResultbyTestId($test_id){

        $this->legacy_db->select("tr.*");
        $this->legacy_db->from('test_result tr');
        $this->legacy_db->join('test_type AS td', 'td.TEST_ID = tr.TEST_ID');
        $this->legacy_db->where('tr.TEST_ID', $test_id);
        $this->legacy_db->order_by('tr.CARD_ID');
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }

    function getTestResultbyTestIdOnlyNullCPN($test_id){

        $this->legacy_db->select("tr.*,td.*,app.FORM_DATA,ass.PROGRAM_TYPE_ID");
        $this->legacy_db->from('test_result tr');
        $this->legacy_db->join('test_type AS td', 'td.TEST_ID = tr.TEST_ID');
        $this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
        $this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');
        $this->legacy_db->where('tr.TEST_ID', $test_id);
        $this->legacy_db->where('app.STATUS_ID >= 3');
        $this->legacy_db->where('tr.CPN IS NULL');


        $result = $this->legacy_db->get()->result_array();
        return $result;
    }

    function getTestResultAndCPNbyTestId($test_id){
        
        $this->legacy_db->select("ur.FIRST_NAME,ur.LAST_NAME,ur.CNIC_NO,ur.FNAME,tr.*,
        td.*,
        qual.*,di.*,
        ass.PROGRAM_TYPE_ID");
        $this->legacy_db->from('test_result tr');
        $this->legacy_db->join('test_type AS td', 'td.TEST_ID = tr.TEST_ID');
        $this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
        $this->legacy_db->join('users_reg AS ur', 'app.USER_ID = ur.USER_ID');
        $this->legacy_db->join('qualifications AS qual', 'qual.USER_ID = app.USER_ID AND qual.APPLICATION_ID = app.APPLICATION_ID');
        $this->legacy_db->join('discipline AS di', 'di.DISCIPLINE_ID = qual.DISCIPLINE_ID');
        $this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');
        
        $this->legacy_db->where('tr.TEST_ID', $test_id);
         $this->legacy_db->where('qual.ACTIVE', 1);
          $this->legacy_db->where('tr.TEST_SCORE >= td.PASSING_SCORE');
          $this->legacy_db->where('di.DEGREE_ID <> 10');
        $this->legacy_db->where('app.STATUS_ID IN (4,5) ');
        $this->legacy_db->order_by('app.APPLICATION_ID,di.DEGREE_ID DESC');
        //$this->legacy_db->where('app.USER_ID =263990 ');
       // $this->legacy_db->limit(10);
            
        
       // $this->legacy_db->order_by("CPN", "desc");
       
        $result = $this->legacy_db->get()->result_array();
       // echo $this->legacy_db->last_query();
        $new_array = array();
        foreach($result as $obj){
            $application_id = $obj['APPLICATION_ID'];
            if(isset($new_array[$application_id])){
                array_push($new_array[$application_id]['qualifications'],$obj);
            }else{
                $new_array[$application_id]=$obj;
                $new_array[$application_id]['qualifications'] = array();
                array_push($new_array[$application_id]['qualifications'],$obj);
            }
        }
       
        return $new_array;
    }
   
    function getTestType(){

        $this->legacy_db->select("*");
        $this->legacy_db->from('test_type ');
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }

    function getTestTypeByYear($year){

        $this->legacy_db->select("*");
        $this->legacy_db->from('test_type ');
        $this->legacy_db->where('YEAR',$year);
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }

    function getTestTypeYear(){

        $this->legacy_db->select("DISTINCT(YEAR)");
        $this->legacy_db->from('test_type ');
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }
   
   
    function updateTestMarks($full_array,$USER_ID=0,$file_post_fix=null){
        $check = true;
        $this->load->model('log_model');
        $this->legacy_db->trans_begin();
        $count=1;
        foreach($full_array as $array){
//            $this->legacy_db->set('TEST_SCORE', "AES_ENCRYPT('{$array['TEST_SCORE']}', UNHEX(SHA2('".SHA2_PRIVATE_KEY."',".SHA2_NUMBER.")))", FALSE);
            
            
            $this->legacy_db->set('TEST_SCORE',$array['TEST_SCORE']);
            //$data=array("TEST_SCORE"=>"");
            $this->legacy_db->where('CARD_ID',$array['CARD_ID']);
            $this->legacy_db->where('TEST_ID',$array['TEST_ID']);
            $this->legacy_db->update('test_result');

            if ($this->legacy_db->affected_rows()>1)
            {
                $check = false;
                break;
            }
            $count++;

        }
        if($check==false){
            $QUERY ="TEST_RESULT_FAILED_$file_post_fix Something went wrong in row $count";
            $this->legacy_db->trans_rollback();
            $this->log_model->create_log(0,0,$QUERY,$QUERY,"UPDATE_MARK_BY_XLSX_FAILED",'test_result',11,$USER_ID);
            return false;
        }else{
            $QUERY="TEST_RESULT_FAILED_$file_post_fix";
            $this->legacy_db->trans_commit();

            $this->log_model->create_log(0,0,$QUERY,$QUERY,"UPDATE_MARK_BY_XLSX_SUCCESS",'test_result',11,$USER_ID);
            return true;
        }



    }

    function updateCPN($full_array,$USER_ID=0){
        $check = true;
        $this->load->model('log_model');
        $this->legacy_db->trans_begin();
        $count=1;
        foreach($full_array as $array){
//            $this->legacy_db->set('CPN', "AES_ENCRYPT('{$array['CPN']}', UNHEX(SHA2('".SHA2_PRIVATE_KEY."',".SHA2_NUMBER.")))", FALSE);
            $this->legacy_db->set('CPN',$array['CPN'], FALSE);
            $this->legacy_db->set('DETAIL_CPN', $array['DETAIL_CPN']);
            //$data=array("TEST_SCORE"=>"");
            $this->legacy_db->where('CARD_ID',$array['CARD_ID']);
            $this->legacy_db->where('TEST_ID',$array['TEST_ID']);
            $this->legacy_db->update('test_result');

            if ($this->legacy_db->affected_rows()>1)
            {
                $check = false;
                break;
            }
            $count++;

        }
        if($check==false){
            $QUERY ="Something went wrong in row $count";
            $this->legacy_db->trans_rollback();
            $this->log_model->create_log(0,0,$QUERY,$QUERY,"GENERATE_CPN_FAILED",'test_result',11,$USER_ID);
            return false;
        }else{
            $QUERY="";
            $this->legacy_db->trans_commit();
            //$this->log_model->log("GENERATE_CPN","SUCCESS",$QUERY,'ADMIN',$USER_ID,"","",0,'test_result');
            $this->log_model->create_log(0,0,"","","GENERATE_CPN_SUCCESS",'test_result',11,$USER_ID);
            return true;
        }
    }

    function truncate_cpn($cpn,$decimal=2){

        $this->legacy_db->select("truncate($cpn,$decimal) AS PER");
        //$this->legacy_db->from('test_type ');
        $result = $this->legacy_db->get()->row_array();
        //prePrint($result);
        //exit();
        return $result['PER'];
    }
    
    
    //add method after 15-dec-2020 edited 23-jan-2022
    function getTestResultAndCPNbyTestIdAndCampusId($test_id,$campus_id){
        $this->legacy_db->select("ur.*,dist.PROVINCE_ID,dist.DIVISION_ID,dist.DISTRICT_NAME,qual.*,di.*,app.ADMISSION_SESSION_ID,ass.PROGRAM_TYPE_ID,tr.CARD_ID,app.APPLICATION_ID,app.USER_ID,tr.DETAIL_CPN,TEST_SCORE,CPN,app.STATUS_ID");
        $this->legacy_db->from('test_result tr');
        $this->legacy_db->join('test_type AS td', 'td.TEST_ID = tr.TEST_ID');
        $this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
        $this->legacy_db->join('users_reg AS ur', 'app.USER_ID = ur.USER_ID');
        $this->legacy_db->join('districts AS dist', 'dist.DISTRICT_ID = ur.DISTRICT_ID');
        $this->legacy_db->join('qualifications AS qual', 'qual.USER_ID = app.USER_ID AND qual.APPLICATION_ID = app.APPLICATION_ID');
        $this->legacy_db->join('discipline AS di', 'di.DISCIPLINE_ID = qual.DISCIPLINE_ID');
        $this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

        $this->legacy_db->where('tr.TEST_ID', $test_id);
        $this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
        $this->legacy_db->where('qual.ACTIVE', 1);
        $this->legacy_db->where('tr.CPN > 0 ');
           $this->legacy_db->where('tr.TEST_SCORE >= td.PASSING_SCORE');
        $this->legacy_db->where('di.DEGREE_ID <> 10');

        $this->legacy_db->where_in('app.STATUS_ID', array(4,5));
        //$this->legacy_db->limit(50);
        $this->legacy_db->order_by("`tr`.`CPN` DESC, `di`.`DEGREE_ID` DESC ");

        $result = $this->legacy_db->get()->result_array();
         echo "-------------getTestResultAndCPNbyTestId----------------";
        echo $this->legacy_db->last_query();
        echo "-----------------------------";
        $new_array = array();
        foreach($result as $obj){
            $application_id = $obj['APPLICATION_ID'];
            if(isset($new_array[$application_id])){
                array_push($new_array[$application_id]['qualifications'],$obj);
            }else{
                $new_array[$application_id]=$obj;
                $new_array[$application_id]['users_reg']=$obj;
                $new_array[$application_id]['qualifications'] = array();
                array_push($new_array[$application_id]['qualifications'],$obj);
            }
        }

//        prePrint($this->legacy_db->last_query());
//        exit();
        return $new_array;
    }

    function getListOfStudentByTestIdAndCampusIdAndShiftId_yasir($test_id,$campus_id,$shift_id){

		prePrint("start getting student data".date("d-m-y h:i:s A"));
//		exit();
       $result = $this->getTestResultAndCPNbyTestIdAndCampusId($test_id,$campus_id);

        $all_applicants = array();
        foreach ($result as $candidate){
            $application_id = $candidate['APPLICATION_ID'];
            $applicants_choices = array();
            $applicants_category = array();
            $applicants_minor = array();

			$applicants_choices = $this->get_applicant_choices($application_id,$shift_id);
			$applicants_category= $this->get_applicant_category ($application_id);
			$applicants_minor   = $this->get_applicant_minor ($application_id);

			$candidate['applicants_minors']=$applicants_minor;
			$candidate['application_choices']=$applicants_choices;
			$candidate['application_category']=$applicants_category;
			$all_applicants[]=$candidate;
            //prePrint($candidate);
           // exit();
        }
//        prePrint($all_applicants);
//		prePrint("total data ".count($all_applicants));
		prePrint("End Time getting all Applicants".date("d-m-y h:i:s A"));
//		exit();

        return $all_applicants;

    }

	function getListOfStudentByTestIdAndCampusIdAndShiftIdUpdate($test_id,$campus_id,$shift_id){


		//$result = $this->getTestResultAndCPNbyTestIdAndCampusId($test_id,$campus_id);

		$this->legacy_db->select("ac.*,pl.* " );
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('application_choices AS ac', 'ac.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('program_list AS pl', 'pl.PROG_LIST_ID = ac.PROG_LIST_ID');

		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('tr.TEST_ID', $test_id);
		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('ac.SHIFT_ID', $shift_id);
		$this->legacy_db->where("(`ac`.`IS_RECOMMENDED` IS NULL OR `ac`.`IS_RECOMMENDED` LIKE 'Y')");
		//$this->legacy_db->limit(100);
		// $this->legacy_db->order_by("CPN", "desc");

		$application_choices = $this->legacy_db->get()->result_array();
		prePrint($this->legacy_db->last_query());
//		exit();
//prePrint($result);
		$this->legacy_db->select("app_cat.*, f_cat.*" );
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('application_category AS app_cat', 'app_cat.APPLICATION_ID = app.APPLICATION_ID');
		$this->legacy_db->join('form_category AS f_cat', 'app_cat.FORM_CATEGORY_ID = f_cat.FORM_CATEGORY_ID');

		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('tr.TEST_ID', $test_id);

		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('app_cat.IS_ENABLE','Y');
		//$this->legacy_db->limit(100);
		// $this->legacy_db->order_by("CPN", "desc");

		$application_category = $this->legacy_db->get()->result_array();
		prePrint($this->legacy_db->last_query());

//        exit();
		$this->legacy_db->select("app_min.*");
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('applicants_minors AS app_min', 'app_min.APPLICATION_ID = app.APPLICATION_ID');

		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('tr.TEST_ID', $test_id);
		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		// $this->legacy_db->limit(100);
		// $this->legacy_db->order_by("CPN", "desc");

		$application_minors = $this->legacy_db->get()->result_array();
		prePrint($this->legacy_db->last_query());
//		exit();
		//$this->legacy_db->limit(1000);
		// $this->legacy_db->order_by("CPN", "desc");
		$all_applicants = array();
//		foreach ($application_choices as $choice){
//			$application_id = $choice['APPLICATION_ID'];
//
//			$index = getIndexOfObjectInList($result, "APPLICATION_ID", $application_id);
//			if($index>=0){
//				array_push($result[$index]['applicants_minors'],$choice);
//			}
//
//		}
		prePrint("End Time geting student data".date("d-m-y h:i:s A"));
		exit();
		foreach ($result as $candidate){
			$application_id = $candidate['APPLICATION_ID'];
			$applicants_choices = array();
			$applicants_category = array();
			$applicants_minor = array();

//			do {
//				$choice_index = getIndexOfObjectInList($application_choices, "APPLICATION_ID", $application_id);
//				if($choice_index>=0){
//					$applicants_choices[] =  $application_choices[$choice_index];
//					unset($application_choices[$choice_index]);
//				}
//			}while($choice_index>=0);


			do {
				$category_index = getIndexOfObjectInList($application_category, "APPLICATION_ID", $application_id);
				if($category_index>=0){
					$applicants_category[] =  $application_category[$category_index];
					unset($application_category[$category_index]);
				}
			}while($category_index>=0);

			do {
				$minor_index = getIndexOfObjectInList($application_minors, "APPLICATION_ID", $application_id);
				if($minor_index>=0){
					$applicants_minor[] =  $application_minors[$minor_index];
					unset($application_minors[$minor_index]);
				}
			}while($minor_index>=0);

			//$applicants_choices = quicksort($applicants_choices,"CHOICE_NO");
			//$candidate['application_choices']=$applicants_choices;
			$candidate['application_category']=$applicants_category;
			$candidate['applicants_minors']=$applicants_minor;
			$all_applicants[]=$candidate;

			//prePrint($candidate);
			// exit();
		}

		return $all_applicants;

	}
	

	function get_applicant_choices($application_id,$shift_id)
	{
		$this->legacy_db->select("ac.*,pl.*");
//		$this->legacy_db->from('test_result tr');
//		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->from('application_choices AS ac');
		$this->legacy_db->join('program_list AS pl', 'pl.PROG_LIST_ID = ac.PROG_LIST_ID');
//		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

//		$this->legacy_db->where('tr.TEST_ID', $test_id);
//		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('ac.SHIFT_ID', $shift_id);
		$this->legacy_db->where('ac.APPLICATION_ID', $application_id);
		$this->legacy_db->where("(`ac`.`IS_RECOMMENDED` IS NULL OR `ac`.`IS_RECOMMENDED` LIKE 'Y')");
		//$this->legacy_db->limit(100);
		 $this->legacy_db->order_by("CHOICE_NO", "ASC");

		$application_choices = $this->legacy_db->get()->result_array();
//		prePrint($this->legacy_db->last_query());
//		exit();
		return $application_choices;
	}

	function get_applicant_category ($application_id)
	{
		$this->legacy_db->select("app_cat.*, f_cat.*" );
//		$this->legacy_db->from('test_result tr');
//		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->from('application_category AS app_cat');
		$this->legacy_db->join('form_category AS f_cat', 'app_cat.FORM_CATEGORY_ID = f_cat.FORM_CATEGORY_ID');

//		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

//		$this->legacy_db->where('tr.TEST_ID', $test_id);

//		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('app_cat.IS_ENABLE','Y');
		$this->legacy_db->where('app_cat.APPLICATION_ID',$application_id);
		//$this->legacy_db->limit(100);
		// $this->legacy_db->order_by("CPN", "desc");

		$application_category = $this->legacy_db->get()->result_array();
//		prePrint($this->legacy_db->last_query());
		return $application_category;
	}

	function get_applicant_minor ($application_id){

		$this->legacy_db->select("app_min.*");
//		$this->legacy_db->from('test_result tr');
//		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->from('applicants_minors AS app_min');

//		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('app_min.APPLICATION_ID', $application_id);
//		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		// $this->legacy_db->limit(100);
		// $this->legacy_db->order_by("CPN", "desc");

		$application_minors = $this->legacy_db->get()->result_array();
//		prePrint($this->legacy_db->last_query());
		return $application_minors;
	}
	
	//add method 11-jan-2021
	function getListOfStudentByTestIdAndCampusIdAndShiftId($test_id,$campus_id,$shift_id){


		$result = $this->getTestResultAndCPNbyTestIdAndCampusId($test_id,$campus_id);
        echo "Getting student Data successfully<br>";
		$this->legacy_db->select("ac.*,pl.* " );
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('application_choices AS ac', 'ac.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('program_list AS pl', 'pl.PROG_LIST_ID = ac.PROG_LIST_ID');

		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('tr.TEST_ID', $test_id);
		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('ac.SHIFT_ID', $shift_id);
		$this->legacy_db->where("(`ac`.`IS_RECOMMENDED` IS NULL OR `ac`.`IS_RECOMMENDED` LIKE 'Y')");
		  $this->legacy_db->where('ac.IS_SPECIAL_CHOICE', 'N');


		$application_choices = $this->legacy_db->get()->result_array();
        echo "Getting student choice successfully<br>";
		$application_choices_array = array();
		foreach ($application_choices as $application_choice){
            $application_id = $application_choice['APPLICATION_ID'];

             if(!isset($application_choices_array[$application_id])){
                 $application_choices_array[$application_id] = array();
             }
            array_push($application_choices_array[$application_id],$application_choice);
        }

		//prePrint($this->legacy_db->last_query());

		$this->legacy_db->select("app_cat.*, f_cat.*" );
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('application_category AS app_cat', 'app_cat.APPLICATION_ID = app.APPLICATION_ID');
		$this->legacy_db->join('form_category AS f_cat', 'app_cat.FORM_CATEGORY_ID = f_cat.FORM_CATEGORY_ID');

		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('tr.TEST_ID', $test_id);

		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		$this->legacy_db->where('app_cat.IS_ENABLE','Y');

		$application_category = $this->legacy_db->get()->result_array();
        echo "Getting student Category successfully<br>";
        $application_category_array = array();
        foreach ($application_category as $application_cat){
            $application_id = $application_cat['APPLICATION_ID'];

            if(!isset($application_category_array[$application_id])){
                $application_category_array[$application_id] = array();
            }
            array_push($application_category_array[$application_id],$application_cat);
        }

		//prePrint($this->legacy_db->last_query());

//        exit();
		$this->legacy_db->select("app_min.*");
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('applications AS app', 'app.APPLICATION_ID = tr.APPLICATION_ID');
		$this->legacy_db->join('applicants_minors AS app_min', 'app_min.APPLICATION_ID = app.APPLICATION_ID');

		$this->legacy_db->join('admission_session AS ass', 'app.ADMISSION_SESSION_ID = ass.ADMISSION_SESSION_ID');

		$this->legacy_db->where('tr.TEST_ID', $test_id);
		$this->legacy_db->where('ass.CAMPUS_ID', $campus_id);
		// $this->legacy_db->limit(100);
		// $this->legacy_db->order_by("CPN", "desc");

		$application_minors = $this->legacy_db->get()->result_array();

        $application_minor_array = array();
        foreach ($application_minors as $application_minor){
            $application_id = $application_minor['APPLICATION_ID'];

            if(!isset($application_minor_array[$application_id])){
                $application_minor_array[$application_id] = array();
            }
            array_push($application_minor_array[$application_id],$application_minor);
        }
		//prePrint($this->legacy_db->last_query());

		$all_applicants = array();

        foreach ($result as $candidate) {
            $application_id = $candidate['APPLICATION_ID'];


            $applicants_category = $applicants_choices = $applicants_minor =array();
            if(isset($application_category_array[$application_id])){
                $applicants_category=$application_category_array[$application_id];
            }
            if(isset($application_choices_array[$application_id])){
                $applicants_choices=$application_choices_array[$application_id];
            }
            if(isset($application_minor_array[$application_id])){
                $applicants_minor=$application_minor_array[$application_id];
            }

            $applicants_choices= quicksort($applicants_choices,'CHOICE_NO','ASC');
            $candidate['applicants_minors']=$applicants_minor;
            $candidate['application_choices']=$applicants_choices;
            $candidate['application_category']=$applicants_category;
            $all_applicants[$application_id]=$candidate;

        }

		prePrint("End Time geting student data".date("d-m-y h:i:s A"));
		//exit();


		return $all_applicants;

	}
	
	function getTestResultbyYearAndApplicationId($year,$app_id){

        $this->legacy_db->select("tr.*");
        $this->legacy_db->from('test_result tr');
        $this->legacy_db->join('test_type AS td', 'td.TEST_ID = tr.TEST_ID');
        $this->legacy_db->where('td.YEAR', $year);
         $this->legacy_db->where('tr.APPLICATION_ID', $app_id);
         $this->legacy_db->where('tr.ACTIVE', 1);
        //$this->legacy_db->order_by('tr.CARD_ID');
        $result = $this->legacy_db->get()->result_array();

        return $result;
    }
    
    /*
     * YASIR ADDED NEW METHOD ON 03-03-2021
     * */

	function getTestResultWithCpn($test_id,$app_id){

		$this->legacy_db->select("tr.*");
		$this->legacy_db->from('test_result tr');
		$this->legacy_db->join('test_type AS td', 'td.TEST_ID = tr.TEST_ID');
		$this->legacy_db->where('td.TEST_ID', $test_id);
		$this->legacy_db->where('tr.APPLICATION_ID', $app_id);
		//$this->legacy_db->order_by('tr.CARD_ID');
		$result = $this->legacy_db->get()->row_array();

		return $result;
	}
	function getLatInformationByAdmissionSessionId($admission_session_id,$limit=null,$offset=null,$IS_RECOMMENDED=null){
	    
	    
		$this->legacy_db->select("ac.*,ali.*,app.APPLICATION_ID,ur.FIRST_NAME,ur.LAST_NAME,ur.FNAME");
		$this->legacy_db->from('applications app');
		$this->legacy_db->join('users_reg AS ur', 'ur.USER_ID = app.USER_ID');
		$this->legacy_db->join('application_choices AS ac', 'ac.APPLICATION_ID = app.APPLICATION_ID');
		$this->legacy_db->join('applicants_lat_info AS ali', 'app.APPLICATION_ID = ali.APPLICATION_ID');
		$this->legacy_db->where('app.ADMISSION_SESSION_ID', $admission_session_id);
		$this->legacy_db->where('ali.ACTIVE', 1);
		$this->legacy_db->where('ac.PROG_LIST_ID', 143);
		if($limit!=null&&$offset!=null){
            $this->legacy_db->limit($limit, $offset);
        }elseif($limit!=null){
            $this->legacy_db->limit($limit);
        }
        
        if($IS_RECOMMENDED!=null){
            if($IS_RECOMMENDED == "Y"){
                $this->legacy_db->where("(ac.IS_RECOMMENDED = '$IS_RECOMMENDED' OR ac.IS_RECOMMENDED  IS NULL)");
            }else{
                $this->legacy_db->where('ac.IS_RECOMMENDED', $IS_RECOMMENDED);
            }
            	
        }
		//$this->legacy_db->order_by('tr.CARD_ID');
		$result = $this->legacy_db->get()->result_array();
        
        //echo $this->legacy_db->last_query();
       // exit();
		return $result;
	}
}
