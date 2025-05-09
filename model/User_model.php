<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/10/2020
 * Time: 10:54 PM
 */

class User_model extends CI_model
{
    function __construct(){
        parent::__construct();

        $this->legacy_db = $this->load->database('admission_db',true);
    }

    function getUserFullDetailById($user_id,$application_id){
        $user_reg  = $this->getUserById($user_id);
        if($user_reg){
            $qual = $this->getQulificatinByUserId($user_id,$application_id);
            //$expr = $this->getExperiancesByUserId($user_id);
            $gaurd = $this-> getGuardianByUserId($user_id);
            return array("users_reg"=>$user_reg,"qualifications"=>$qual,"guardian"=>$gaurd);
        }else{
            return false;
        }

    }
    //this is method add on  5-nov-2020
	//this method update on 08-02-2021 by yasir
	function getUserFullDetailWithChoiceById($user_id,$application_id,$SHIFT_ID=1){
        $this->load->model("Application_model");
        $user_reg  = $this->getUserById($user_id);
        if($user_reg){
            $qual = $this->getQulificatinByUserId($user_id,$application_id);
            //$expr = $this->getExperiancesByUserId($user_id);
            $gaurd = $this-> getGuardianByUserId($user_id);
            $applicant_choice = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user_id,$application_id,$SHIFT_ID);
			$applicant_choice_evening = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user_id,$application_id,EVENING_SHIFT_ID);
            $applicant_category = $this->Application_model->getApplicantCategory($application_id,$user_id);
           
            if(isset($qual[0])&&isset($qual[0]['DISCIPLINE_ID'])){
                 $discipline_id = $qual[0]['DISCIPLINE_ID'];
            }else{
                $discipline_id = 0;
            }
            $applicant_minor = $this->Application_model->getApplicantsMinorsByUserIdAndApplicationId($user_id,$application_id);

            return array("users_reg"=>$user_reg,"qualifications"=>$qual,"guardian"=>$gaurd,"application_choices"=>$applicant_choice,"application_category"=>$applicant_category,"applicants_minors"=>$applicant_minor,"application_choices_evening"=>$applicant_choice_evening);
        }else{
            return false;
        }

    }
    function getUserByCnicAndPassword($cnic,$password){
            $this->db->where('CNIC_NO',$cnic);
            $this->db->where('PASSWORD',$password);
            $user = $this->db->get('users_reg')->row_array();
            return $user;

        }
    function getUserByPassportAndPassword($passport,$password){
        $this->db->where('PASSPORT_NO',$passport);
        $this->db->where('PASSWORD',$password);
        $user = $this->db->get('users_reg')->row_array();
        return $user;

    }
    function getUserByCnic($cnic){
        $this->db->where('CNIC_NO',$cnic);
        $user = $this->db->get('users_reg')->row_array();
        return $user;

    }
    function getUserByCnicLegacyDb($cnic){
        $this->legacy_db->where('CNIC_NO',$cnic);
        $user = $this->legacy_db->get('users_reg')->row_array();
        return $user;

    }
    function getUserByUserIdLegacyDb($user_id){
        $this->legacy_db->where('USER_ID',$user_id);
        $user = $this->legacy_db->get('users_reg')->row_array();
        return $user;

    }
     function getUserByPassportNo($passport_no){
        $this->db->where('PASSPORT_NO',$passport_no);
        $user = $this->db->get('users_reg')->row_array();
        return $user;

    }
/*	function getUserById($user_id){
	    $this->db->select("*,ur.REMARKS");
        $this->db->from("users_reg ur");
        $this->db->join('districts AS d', 'ur.DISTRICT_ID = d.DISTRICT_ID');
        $this->db->where('USER_ID',$user_id);

        $user = $this->db->get()->row_array();
        return $user;

    }
    
*/
//this is method updated 5-nov-2020
    function getUserById($user_id){
	    $this->db->select("*,ur.REMARKS");
        $this->db->from("users_reg ur");
        $this->db->join('districts AS d', 'ur.DISTRICT_ID = d.DISTRICT_ID');
        $this->db->join('provinces AS p', 'p.PROVINCE_ID = d.PROVINCE_ID');
        $this->db->join('countries AS c', 'c.COUNTRY_ID = p.COUNTRY_ID');
        $this->db->where('USER_ID',$user_id);

        $user = $this->db->get()->row_array();
        return $user;

    }
    function getUserByIdForAdmin($user_id){
	    $this->db->select("*,ur.REMARKS");
        $this->db->from("users_reg ur");
        // $this->db->join('districts AS d', 'ur.DISTRICT_ID = d.DISTRICT_ID');
        // $this->db->join('provinces AS p', 'p.PROVINCE_ID = d.PROVINCE_ID');
        // $this->db->join('countries AS c', 'c.COUNTRY_ID = p.COUNTRY_ID');
        $this->db->where('USER_ID',$user_id);

        $user = $this->db->get()->row_array();
        return $user;

    }
    function getUserByIdWithProfilePhoto($user_id){
	    $this->db->select("*,ur.REMARKS");
        $this->db->from("users_reg ur");
        $this->db->join('districts AS d', 'ur.DISTRICT_ID = d.DISTRICT_ID');
        $this->db->join('provinces AS p', 'p.PROVINCE_ID = d.PROVINCE_ID');
        $this->db->join('countries AS c', 'c.COUNTRY_ID = p.COUNTRY_ID');
        $this->db->join('profile_photo AS pp', 'pp.USER_ID = ur.USER_ID');
         
         
        $this->db->where('ur.USER_ID',$user_id);

        $user = $this->db->get()->row_array();
        //echo $this->db->last_query();
        //exit();
        return $user;

    }
	// JOIN QUERY TO GET USER ROLE FROM ROLE AND ROLE_RELATION TABLE
	// SELECT r.`ROLE_NAME`,r.`ACTIVE`, rr.`USER_ID`, r.`KEYWORD` from role r, role_relation rr where rr.USER_ID=93774 AND r.ROLE_ID=rr.ROLE_ID
	/*function getUserRoleByUserId($user_id){
	    //$this->db = $this->load->database('default',true);
		$this->db->select('r.`ROLE_NAME`,r.`ACTIVE`, rr.`USER_ID`, r.`KEYWORD`');
		$this->db->from('role_relation rr');
		$this->db->join('role AS r', 'rr.ROLE_ID = r.ROLE_ID');
		$this->db->where('rr.USER_ID',$user_id);
		$this->db->where('r.KEYWORD','UG_A');
        $this->db->where('r.ACTIVE','1');
		$user = $this->db->get()->row_array();
		
         //echo "Test ".$this->db->last_query();
        return $user;

    }*/
    function getUserRoleByUserId($user_id,$role_id=0){
        $this->db->select('r.ROLE_ID, rr.R_R_ID, r.`ROLE_NAME`,r.`ACTIVE`, rr.`USER_ID`, r.`KEYWORD`,rr.ACTIVE as IS_ACTIVE');
        $this->db->from('role_relation rr');
        $this->db->join('role AS r', 'rr.ROLE_ID = r.ROLE_ID');
        $this->db->where('rr.USER_ID',$user_id);
        if($role_id>0){
            $this->db->where('r.ROLE_ID',$role_id);
        }
        $this->db->where('r.ACTIVE','1');
        //   $this->db->where('rr.ACTIVE','1');
        $user = $this->db->get()->result_array();
        return $user;

        //echo $this->db->last_query();
//        return $user;

    }

	function getUserAdmissionRoleByUserId($user_id){
		$this->legacy_db = $this->load->database('admission_db',true);
		$this->legacy_db->select('ur.*,r.ROLE_ID, rr.R_R_ID, r.`ROLE_NAME`,r.`ACTIVE`, rr.`USER_ID`, r.`KEYWORD`');
		$this->legacy_db->from('role_relation rr');
		$this->legacy_db->join('role AS r', 'rr.ROLE_ID = r.ROLE_ID');
		$this->legacy_db->join('users_reg AS ur', 'ur.USER_ID = rr.USER_ID');
		$this->legacy_db->where('rr.USER_ID',$user_id);
		$this->legacy_db->where('rr.ACTIVE','1');
		$user = $this->legacy_db->get()->result_array();
//		$this->legacy_db->last_query();
//		exit();
		return $user;
	}

    function getQulificatinByUserId($user_id,$application_id){
        $this->db->select('q.*,d.DEGREE_ID,p.DEGREE_TITLE,d.DISCIPLINE_NAME,i.INSTITUTE_NAME INSTITUTE,o.INSTITUTE_NAME ORGANIZATION');
        $this->db->from('qualifications q');
        $this->db->join('institute AS i', 'q.INSTITUTE_ID = i.INSTITUTE_ID','LEFT');
        $this->db->join('institute AS o', 'q.ORGANIZATION_ID = o.INSTITUTE_ID');
        $this->db->join('discipline AS d', 'q.DISCIPLINE_ID = d.DISCIPLINE_ID');
        $this->db->join('degree_program AS p', 'd.DEGREE_ID = p.DEGREE_ID');
        $this->db->where('q.USER_ID',$user_id);
        $this->db->where('q.ACTIVE',1);
         $this->db->where('q.APPLICATION_ID',$application_id);
        $this->db->order_by('p.DEGREE_ID', 'DESC');
//        $this->db->select('*');
//        $this->db->from('qualifications q');
//        $this->db->join('institute AS i', 'q.INSTITUTE_ID = i.INSTITUTE_ID');
//        $this->db->join('institute AS o', 'q.ORGANIZATION_ID = o.INSTITUTE_ID');
//        $this->db->join('discipline AS d', 'q.DISCIPLINE_ID = d.DISCIPLINE_ID');
//        $this->db->join('degree_program AS p', 'd.DEGREE_ID = p.DEGREE_ID');
//        $this->db->where('q.USER_ID',$user_id);
//        $this->db->where('q.ACTIVE',1);
//        $this->db->order_by('p.DEGREE_ID', 'DESC');
        $qulification_list = $this->db->get()->result_array();
        return $qulification_list;

    }
    
    function getQulificatinByUserID_DEGREE_ID($user_id,$degree_id,$application_id){
        $this->db->select('q.*,d.DEGREE_ID,p.DEGREE_TITLE,d.DISCIPLINE_NAME,i.INSTITUTE_NAME INSTITUTE,o.INSTITUTE_NAME ORGANIZATION');
        $this->db->from('qualifications q');
        $this->db->join('institute AS i', 'q.INSTITUTE_ID = i.INSTITUTE_ID','LEFT');
        $this->db->join('institute AS o', 'q.ORGANIZATION_ID = o.INSTITUTE_ID');
        $this->db->join('discipline AS d', 'q.DISCIPLINE_ID = d.DISCIPLINE_ID');
        $this->db->join('degree_program AS p', 'd.DEGREE_ID = p.DEGREE_ID');
        $this->db->where('q.USER_ID',$user_id);
        $this->db->where('d.DEGREE_ID',$degree_id);
        $this->db->where('q.APPLICATION_ID',$application_id);
        $this->db->where('q.ACTIVE',1);
        $this->db->order_by('p.DEGREE_ID', 'DESC');
//        $this->db->select('*');
//        $this->db->from('qualifications q');
//        $this->db->join('institute AS i', 'q.INSTITUTE_ID = i.INSTITUTE_ID');
//        $this->db->join('institute AS o', 'q.ORGANIZATION_ID = o.INSTITUTE_ID');
//        $this->db->join('discipline AS d', 'q.DISCIPLINE_ID = d.DISCIPLINE_ID');
//        $this->db->join('degree_program AS p', 'd.DEGREE_ID = p.DEGREE_ID');
//        $this->db->where('q.USER_ID',$user_id);
//        $this->db->where('q.ACTIVE',1);
//        $this->db->order_by('p.DEGREE_ID', 'DESC');
        $qulification_list = $this->db->get()->result_array();
        return $qulification_list;

    }
    
	
    function getUserByPassport($passport){
        $this->db->where('PASSPORT_NO',$passport);
        $user = $this->db->get('users_reg')->row_array();
        return $user;

    }

	function changePasswordByCNIC($cnic,$password){
		$formArray = array('PASSWORD'=>$password);
		$this->db->trans_begin();
		$this->legacy_db->trans_begin();
//		$this->db->where('PASSWORD',$curr_password);
		$this->db->where('CNIC_NO',$cnic);
		$this->db->update('users_reg',$formArray);
		
		$this->legacy_db->where('CNIC_NO',$cnic);
		$this->legacy_db->update('users_reg',$formArray);
		

		if($this->db->affected_rows() ==1){
			$this->db->trans_commit();
			$this->legacy_db->trans_commit();
			return true;
		}else{
			$this->db->trans_rollback();
				$this->legacy_db->trans_rollback();
			return false;
		}
	}

    function resetPassword($user_id,$password){
        //load loging model
        $this->load->model('log_model');
        $this->db->where('USER_ID',$user_id);
        $PRE_RECORD =  $this->db->get('users_reg')->row_array();


        $formArray = array('PASSWORD'=>$password,'PASSWORD_TOKEN'=>'');
        $this->db->trans_begin();
        $this->legacy_db->trans_begin();
        // $this->db->where('PASSWORD',$curr_password);
        $this->db->where('USER_ID',$user_id);
        $this->db->update('users_reg',$formArray);
          $this->legacy_db->where('USER_ID',$user_id);
        $this->legacy_db->update('users_reg',$formArray);

        //this code is use for loging
        $QUERY = $this->db->last_query();

        if($this->db->affected_rows() ==1){
            $this->db->trans_commit();
             $this->legacy_db->trans_commit();
            //this code is use for loging
            $this->db->where('USER_ID',$user_id);
            $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
            $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"CHANGE_PASSWORD_SUCCESS",'users_reg',24,$user_id);
            $this->log_model->itsc_log("CHANGE_PASSWORD","SUCCESS",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

            return true;
        }else{
            $this->db->trans_rollback();
             $this->legacy_db->trans_rollback();
            //this code is use for loging
            $this->db->where('USER_ID',$user_id);
            $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
            $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"CHANGE_PASSWORD_FAILED",'users_reg',24,$user_id);
            $this->log_model->itsc_log("CHANGE_PASSWORD","FAILED",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

            return false;
        }

    }
    
    function changePassword($user_id,$curr_password,$password){
        //load loging model
        $this->load->model('log_model');
        $this->db->where('USER_ID',$user_id);
        $PRE_RECORD =  $this->db->get('users_reg')->row_array();


        $formArray = array('PASSWORD'=>$password);
        $this->db->trans_begin();
        $this->legacy_db->trans_begin();
        $this->db->where('PASSWORD',$curr_password);
        $this->db->where('USER_ID',$user_id);
        $this->db->update('users_reg',$formArray);
        $this->legacy_db->where('PASSWORD',$curr_password);
        $this->legacy_db->where('USER_ID',$user_id);
        $this->legacy_db->update('users_reg',$formArray);

        //this code is use for loging
        $QUERY = $this->db->last_query();

        if($this->db->affected_rows() ==1){
            $this->db->trans_commit();
            $this->legacy_db->trans_commit();
            //this code is use for loging
            $this->db->where('USER_ID',$user_id);
            $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
            $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"CHANGE_PASSWORD_SUCCESS",'users_reg',24,$user_id);
            $this->log_model->itsc_log("CHANGE_PASSWORD","SUCCESS",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

            return true;
        }else{
            $this->db->trans_rollback();
             $this->legacy_db->trans_rollback();
            //this code is use for loging
            $this->db->where('USER_ID',$user_id);
            $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
            $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"CHANGE_PASSWORD_FAILED",'users_reg',24,$user_id);
            $this->log_model->itsc_log("CHANGE_PASSWORD","FAILED",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

            return false;
        }

    }

    function updateUserById($user_id,$formArray,$admin_id=0){
         if($admin_id == 0){
            $user_type='CANDIDATE';
            $edititor_id=$user_id;
        }else{
            $user_type='ADMIN';
            $edititor_id=$admin_id;
        }
        //load loging model
        $this->load->model('log_model');
        $this->db->where('USER_ID',$user_id);
        $PRE_RECORD =  $this->db->get('users_reg')->row_array();

        $this->db->trans_begin();
          $this->db->where('USER_ID',$user_id);
        $this->db->update('users_reg',$formArray);

         //this code is use for loging
        $QUERY = $this->db->last_query();

            if($this->db->affected_rows() >=0){
                $this->db->trans_commit();
				$this->db->where('USER_ID',$user_id);
                $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
                $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$edititor_id);
                     return 1;
            }elseif($this->db->affected_rows() ==0){
                $this->db->trans_commit();
                 $this->db->where('USER_ID',$user_id);
                $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
                $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$edititor_id);
                  return 0;
            }else{
                $this->db->trans_rollback();
                 $this->db->where('USER_ID',$user_id);
                $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
                 $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$edititor_id);
                return -1;
            }

    }
   
   function updateUserByIdLagecyDb($user_id,$formArray,$admin_id=0){
         if($admin_id == 0){
            $user_type='CANDIDATE';
            $edititor_id=$user_id;
        }else{
            $user_type='ADMIN';
            $edititor_id=$admin_id;
        }
        //load loging model
        $this->load->model('log_model');
        //$this->db->where('USER_ID',$user_id);
        //$PRE_RECORD =  $this->db->get('users_reg')->row_array();

        $this->legacy_db->trans_begin();
      
        $this->legacy_db->where('USER_ID',$user_id);
        $this->legacy_db->update('users_reg',$formArray);

         //this code is use for loging
        //$QUERY = $this->db->last_query();

            if($this->legacy_db->affected_rows() ==1){
                //$this->db->trans_commit();
                $this->legacy_db->trans_commit();
                //this code is use for loging
            //    $this->db->where('USER_ID',$user_id);
             //   $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
               // $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$edititor_id);
            //    $this->log_model->itsc_log("UPDATE_USER_INFORMATION","SUCCESS",$QUERY,$user_type,$edititor_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

                // $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$user_id);
                // $this->log_model->itsc_log("UPDATE_USER_INFORMATION","SUCCESS",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

                return 1;
            }elseif($this->legacy_db->affected_rows() ==0){
              //  $this->db->trans_commit();
                $this->legacy_db->trans_commit();
                //this code is use for loging
                //$this->db->where('USER_ID',$user_id);
               // $CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
             //   $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$edititor_id);
              //  $this->log_model->itsc_log("UPDATE_USER_INFORMATION","SUCCESS",$QUERY,$user_type,$edititor_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

                // $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$user_id);
                // $this->log_model->itsc_log("UPDATE_USER_INFORMATION","SUCCESS",$QUERY,'CANDIDATE',$user_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

                return 0;
            }else{
            //    $this->db->trans_rollback();
                $this->legacy_db->trans_rollback();
                //this code is use for loging
                //$this->db->where('USER_ID',$user_id);
                //$CURRENT_RECORD =  $this->db->get('users_reg')->row_array();
                // $this->log_model->create_log($user_id,$user_id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_USER_INFORMATION",'users_reg',12,$edititor_id);
                //$this->log_model->itsc_log("UPDATE_USER_INFORMATION","FAILED",$QUERY,$user_type,$edititor_id,$CURRENT_RECORD,$PRE_RECORD,$user_id,'users_reg');

            
                return -1;
            }

    }
    function getExperiancesByUserId($user_id){

        $this->db->where('USER_ID',$user_id);
        $this->db->where('ACTIVE',1);
        $this->db->from('experiances');
        $experiances_list = $this->db->get()->result_array();
        return $experiances_list;
    }

    function addExperiances($form_array){
        //load loging model
        $this->load->model('log_model');

        $this->db->trans_begin();
        $this->db->insert('experiances', $form_array);

        //this code is use for loging
        $QUERY = $this->db->last_query();
        $id = $this->db->insert_id();


        if($this->db->affected_rows() != 1){
            $this->db->trans_rollback();

            //this code is use for loging
            $this->log_model->create_log(0,$id,"","","ADD_EXPERIANCE",'experiances',11,$form_array['USER_ID']);
            $this->log_model->itsc_log("ADD_EXPERIANCE","FAILED",$QUERY,'CANDIDATE',$form_array['USER_ID'],"","",$id,'experiances');



            return false;
        }else {
            $this->db->trans_commit();

            //this code is use for loging

            $this->db->where('EXPERIANCE_ID',$id);
            $CURRENT_RECORD =  $this->db->get('experiances')->row_array();
            $this->log_model->create_log(0,$id,"","","ADD_EXPERIANCE",'experiances',11,$form_array['USER_ID']);
            $this->log_model->itsc_log("ADD_EXPERIANCE","SUCCESS",$QUERY,'CANDIDATE',$form_array['USER_ID'],$CURRENT_RECORD,"",$id,'experiances');

            return true;
        }
    }

    function deleteExperiance($USER_ID,$experiance_id){
        //load loging model
        $this->load->model('log_model');
        $this->db->where('EXPERIANCE_ID',$experiance_id);
        $PRE_RECORD =  $this->db->get('experiances')->row_array();


        $this->db->trans_begin();

        $formArray = array('ACTIVE'=>0);

        $this->db->where('EXPERIANCE_ID',$experiance_id);
        $this->db->where('USER_ID',$USER_ID);
        $this->db->where('ACTIVE',1);
        $this->db->update('experiances',$formArray);
        //this code is use for loging
        $QUERY = $this->db->last_query();


        if($this->db->affected_rows() != 1){
            $this->db->trans_rollback();

            //this code is use for loging
            $this->db->where('EXPERIANCE_ID',$experiance_id);
            $CURRENT_RECORD =  $this->db->get('experiances')->row_array();
            $this->log_model->create_log($experiance_id,$experiance_id,$PRE_RECORD,$CURRENT_RECORD,"DELETE_EXPERIANCE",'experiances',13,$CURRENT_RECORD['USER_ID']);
            $this->log_model->itsc_log("DELETE_EXPERIANCE","FAILED",$QUERY,'CANDIDATE',$USER_ID,$CURRENT_RECORD,$PRE_RECORD,$experiance_id,'experiances');


            return false;
        }else {
            $this->db->trans_commit();

            //this code is use for loging
            $this->db->where('EXPERIANCE_ID',$experiance_id);
            $CURRENT_RECORD =  $this->db->get('experiances')->row_array();
            $this->log_model->create_log($experiance_id,$experiance_id,$PRE_RECORD,$CURRENT_RECORD,"DELETE_EXPERIANCE",'experiances',13,$CURRENT_RECORD['USER_ID']);
            $this->log_model->itsc_log("DELETE_EXPERIANCE","SUCCESS",$QUERY,'CANDIDATE',$USER_ID,$CURRENT_RECORD,$PRE_RECORD,$experiance_id,'experiances');

            return true;
        }
    }

    function addUser($form_array,$image=null){

        //load loging model
        $this->load->model('log_model');

        $this->db->trans_begin();
        $this->db->db_debug = false;
        if($this->db->insert('users_reg', $form_array)){

            //this code is use for loging
            $QUERY = $this->db->last_query();
            $id = $this->db->insert_id();
            $form_array['USER_ID']=$id;
               $res = $this->upload_image('profile_image', "profile_image_" . $id, $id);
                    if ($res['STATUS'] === true) {
                        $PROFILE_IMAGE = $res['IMAGE_NAME'];

                    } else {
                        $error = "<div class='text-danger'>Error {$res['MESSAGE']}</div>";
                        exit($error);
                         return false;
                    }
             $form_array['PROFILE_IMAGE'] = $PROFILE_IMAGE;
                $this->updateUserById($id,$form_array);
			$this->db->trans_commit();
			$CURRENT_RECORD = $this->db->get('users_reg')->row_array();
			$this->log_model->create_log(0, $id, "", $CURRENT_RECORD, "ADD_USER", 'users_reg', 11, $id);

			return true;

        }
        else{
            //this code is use for loging
            $this->log_model->create_log(0,0,"",$form_array,"ADD_USER_FAILED",'users_reg',11,0);

            return false;
        }

    }

    function addFamilyInfo($form_array,$admin_id=0){
        if($admin_id == 0){
            $user_type='CANDIDATE';
            $edititor_id=$form_array['USER_ID'];
            $CODE = 11;
        }else{
            $user_type='ADMIN';
            $edititor_id=$admin_id;
            $CODE = 31;
        }
        //load loging model
        $this->load->model('log_model');

        $this->db->trans_begin();
        $this->db->db_debug = false;
        if($this->db->insert('family_info', $form_array)){

            //this code is use for loging
            $QUERY = $this->db->last_query();
            $id = $this->db->insert_id();

            if ($this->db->affected_rows() != 1) {
                $this->db->trans_rollback();

                //this code is use for loging
                $this->log_model->create_log(0,$id,"","","ADD_FAMILY_INFO_FAILED",'family_info',$CODE,$edititor_id);

                return -1;

            } else {
                $this->db->trans_commit();

                //this code is use for loging
                $this->db->where('USER_ID',$id);
                $CURRENT_RECORD =  $this->db->get('family_info')->row_array();
                $this->log_model->create_log(0,$id,"",$CURRENT_RECORD,"ADD_FAMILY_INFO",'family_info',$CODE,$edititor_id);

                return 1;
            }

        }
        else{
            //this code is use for loging
            $this->log_model->create_log(0,0,"",$form_array,"ADD_FAMILY_INFO_FAILED",'family_info',$CODE,$form_array['USER_ID']);

            return -1;
        }

    }
    function updateFamilyInfoById($id,$formArray,$admin_id=0){
        if($admin_id == 0){
            $user_type='CANDIDATE';
            $edititor_id=$formArray['USER_ID'];
            $CODE = 12;
        }else{
            $user_type='ADMIN';
            $edititor_id=$admin_id;
            $CODE = 32;
        }
        //load loging model
        $this->load->model('log_model');
        $this->db->where('FAMILY_INFO_ID',$id);
        $PRE_RECORD =  $this->db->get('family_info')->row_array();

        $this->db->trans_begin();
        $this->db->where('FAMILY_INFO_ID',$id);
        $this->db->update('family_info',$formArray);

        //this code is use for loging
        $QUERY = $this->db->last_query();

        if($this->db->affected_rows() ==1){
            $this->db->trans_commit();
            //this code is use for loging
            $this->db->where('FAMILY_INFO_ID',$id);
            $CURRENT_RECORD =  $this->db->get('family_info')->row_array();
            $this->log_model->create_log($id,$id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_FAMILY_INFO",'family_info',$CODE,$edititor_id);

            return 1;
        }elseif($this->db->affected_rows() ==0){
            $this->db->trans_commit();

            //this code is use for loging
            $this->db->where('FAMILY_INFO_ID',$id);
            $CURRENT_RECORD =  $this->db->get('family_info')->row_array();
            $this->log_model->create_log($id,$id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_FAMILY_INFO",'family_info',$CODE,$edititor_id);

            return 0;
        }else{
            $this->db->trans_rollback();

            //this code is use for loging
            $this->db->where('FAMILY_INFO_ID',$id);
            $CURRENT_RECORD =  $this->db->get('family_info')->row_array();
            $this->log_model->create_log($id,$id,$PRE_RECORD,$CURRENT_RECORD,"UPDATE_FAMILY_INFO",'family_info',$CODE,$edititor_id);

            return -1;
        }

    }
    function saveGuardianByUserId($user_id,$formArray,$admin_id= 0){

        $family_info = $this->getGuardianByUserId($user_id);
        if($family_info){
            return $this->updateFamilyInfoById($family_info['FAMILY_INFO_ID'],$formArray,$admin_id);
        }else{
            return $this->addFamilyInfo($formArray,$admin_id);
        }

    }
    function getGuardianByUserId($user_id){
        $this->db->where('USER_ID',$user_id);
        $this->db->where('IS_CANDIDATE_GUARDIAN','Y');

       return $this->db->get('family_info')->row_array();
    }
    function getUserByEmailAddress($email){
        $this->db->where('EMAIL',$email);
        $user = $this->db->get('users_reg')->result_array();
        return $user;
    }
    function getInvgAppAuthByKey($key){
         $this->legacy_db->where('AUTH_KEY',$key);
           $this->legacy_db->where('ACTIVE','1');
        $data = $this->legacy_db->get('invg_app_auth')->row_array();
        return $data;
    }
    function updateInvgAppAuthByKey($key,$formArray){
          
            $this->legacy_db->where('AUTH_KEY',$key);
            $this->legacy_db->update('invg_app_auth',$formArray);
          if($this->legacy_db->affected_rows() ==1){
                $this->legacy_db->trans_commit();
                return  true;
          }else{
                $this->legacy_db->roll_back();
                return  false;
          }
    
    }
     private function upload_image($index_name,$image_name,$user_id,$max_size = 100,$path = '../eportal_resource/images/applicants_profile_image/',$con_array=array())
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


                
//            $this->load->library('ftp');
//            $this->CI_ftp($path,$image_data['file_name'],$user_id);
            
           // exit("YES");
            return array("STATUS"=>true,"IMAGE_NAME"=>$image_data['file_name']);

        }
    }
    private function CI_ftp($path,$name,$user_id){
      
      $date_time =date('Y F d l h:i A');
      $msg = array(
          "USER_ID"=>$user_id,
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
 
    function getItscUser($name, $fName, $surname, $email, $mobNo) {
        $this->db->group_start();
    
        if (!empty($name)) {
            $this->db->like('FIRST_NAME', $name);
        }
        if (!empty($fName)) {
            $this->db->like('FNAME', $fName);
        }
        if (!empty($surname)) {
            $this->db->like('LAST_NAME', $surname);
        }
        if (!empty($email)) {
            $this->db->like('EMAIL', $email);
        }
        if (!empty($mobNo)) {
            $this->db->like('MOBILE_NO', $mobNo);
        }
    
        $this->db->group_end();
    
        $users = $this->db->get('users_reg')->result_array();
        return $users;
    }

 
 
}
