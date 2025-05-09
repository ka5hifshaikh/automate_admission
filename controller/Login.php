<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/10/2020
 * Time: 9:42 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller {
    /**
     * Login constructor.
     */
    private $HomeController = 'advertisement/ug_advertisement';
    private $SelfController = 'login';


    public function __construct()
    {
        parent::__construct();

        if($this->session->has_userdata($this->SessionName)){
            redirect(base_url().$this->HomeController);
            exit();
        }
        $this->load->model("Application_model");
    }

   
    
    function index(){
    
        $this->load->helper("form");
        $this->load->view('includes/header');
        //$this->load->view('include/preloder');
        $this->load->view('includes/navbar');
        $this->load->view('login');
        $this->load->view('includes/footer_link');
		$this->load->view('includes/footer');
        
    }



}
