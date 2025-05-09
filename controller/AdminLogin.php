<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/10/2020
 * Time: 9:42 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class AdminLogin extends CI_Controller {
    /**
     * Login constructor.
     */
    private $SelfController = 'AdminLogin';
    public function __construct()
    {
        parent::__construct();
		$this->load->model('Configuration_model');
    }

    /**
     * Login constructor.
     */

    function index(){
        
        $this->load->helper("form");
        $this->load->view('includes/header');
        $this->load->view('includes/navbar');

        $this->load->view('admin_login');
		$this->load->view('includes/footer_link');
        $this->load->view('includes/footer');
    }

	function set_admission_role ($user_admission_role) {
		$this->session->set_userdata($this->user_role, $user_admission_role[0]);
	}
 
    protected function verify_login() {
		if((!$this->session->has_userdata($this->SessionName))){
			redirect(base_url().$this->SelfController);
			exit();
		}
	}
	
	
	/*
	 * verify_path method is updated on 15-10-2020 by Yasir Mehboob bcz sub menu was giving access prohibited
	 * */
	 
	protected function verify_path ($path=null,$side_bar_data) {
	   // prePrint($side_bar_data);
	    $side_bar_data = $side_bar_data['privilages'];
	    // prePrint($side_bar_data);
			foreach ($side_bar_data as $p){
				if ($path == null)
				{
					$self = $_SERVER['REQUEST_URI'];
					$path = str_replace('/admission/','',$self);
				}
			//	prePrint($p['LINK']);
			//	prePrint($path);
				if ($p['LINK'] == $path)
				{
					return true;
				}
			}
			exit("<h2>Access Prohibbited</h2>");
	}
	
	public function invg_app_auth(){
	      $this->load->model('User_model');

        if($this->input->server('REQUEST_METHOD') === 'POST'){
        		$postdata = file_get_contents("php://input");
	        	$request= json_decode($postdata,true);
            if(isset($request['auth_key'])&&!empty(trim($request['auth_key']))){
	        	    $auth_key = $this->security->xss_clean($request['auth_key']);
	        	    $auth_key = addslashes(trim($auth_key));
	        	}else{
	        	    log_message('error', "Auth Key index not found.");
	                  $reponse = array("ID"=>"2","DESCRIPTION"=>"Auth Key index not found.");
                			  $this->output
                                ->set_status_header(502)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                        return 2;
                                       // exit();
	        	}
	        if(isset($request['mac_address'])&&!empty(trim($request['mac_address']))){
	        	    $mac_address = $this->security->xss_clean($request['mac_address']);
	        	    $mac_address = addslashes(trim($mac_address));
	        	}else{
	        	    log_message('error', "Mac Address index not found.");
	                  $reponse = array("ID"=>"2","DESCRIPTION"=>"Mac Address index not found.");
                			  $this->output
                                ->set_status_header(502)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                        return 2;
                                       // exit();
	        	}
	        	
          
               $data = $this->User_model->getInvgAppAuthByKey($auth_key); 
               if($data){
                   if($data['MAC_ADDRESS']){
                        if($data['MAC_ADDRESS'] == $mac_address){
                            $reponse = array("ID"=>"1","DESCRIPTION"=>"SUCCESS","DATA"=>$data);
                			  $this->output
                                ->set_status_header(200)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                          return 2;
                        }else{
                            $reponse = array("ID"=>"2","DESCRIPTION"=>"Mac Address invalid.");
                			  $this->output
                                ->set_status_header(502)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                          return 2; 
                        }
                   }else{
                       //updateing mac address
                       $form_array  =array("MAC_ADDRESS"=>$mac_address);
                       $ok = $this->User_model->updateInvgAppAuthByKey($auth_key,$form_array);
                       if($ok){
                           $data['MAC_ADDRESS'] = $mac_address;
                             $reponse = array("ID"=>"1","DESCRIPTION"=>"SUCCESS","DATA"=>$data);
                			  $this->output
                                ->set_status_header(200)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                          return 2;   
                       }else{
                             $reponse = array("ID"=>"2","DESCRIPTION"=>"MAC ADDRESS UPDATING FAIL.");
                			  $this->output
                                ->set_status_header(502)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                          return 2; 
                       }
                  
                   }
                   
               }else{
                     $reponse = array("ID"=>"2","DESCRIPTION"=>"Auth Key invalid.");
                			  $this->output
                                ->set_status_header(502)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                          return 2;
               }
             
               
               
           
        }else{
              $reponse = array("ID"=>"2","DESCRIPTION"=>"Invalid Request Method.");
                			  $this->output
                                ->set_status_header(502)
                                ->set_content_type('application/json', 'utf-8')
                                        ->set_output(json_encode($reponse));
                                        return 2;
        }
	}
	
}//class
