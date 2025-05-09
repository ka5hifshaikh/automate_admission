<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/10/2020
 * Time: 9:42 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Logout extends CI_Controller {
    /**
     * Login constructor.
     */


    function index(){

      $user = $this->session->userdata('USER_LOGIN_FOR_ADMISSION');
      $this->load->model('log_model');
        $this->log_model->create_log($user['USER_ID'],$user['USER_ID'],$user,$user,"LOGOUT",'users_reg',21,$user['USER_ID']);
        $this->log_model->itsc_log("LOGIN","SUCCESS","LOGOUT","CANDIDATE",$user['USER_ID'],$user,$user,$user['USER_ID'],'users_reg');

        $this->session->sess_destroy();
      redirect(base_url()."login");
    }


}