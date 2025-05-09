<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';
class ResultPanel extends AdminLogin
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
//		$this->load->library('javascript');
        $self = $_SERVER['PHP_SELF'];
        $self = explode('index.php/',$self);
        $this->script_name = $self[1];
        $this->verify_login();
    }
    public function createTestType(){

    }
    public function upload_test_marks(){

        $user = $this->session->userdata($this->SessionName);
        $user_role = $this->session->userdata($this->user_role);
        $user_id = $user['USER_ID'];
        $role_id = $user_role['ROLE_ID'];

        $side_bar_data = $this->Configuration_model->side_bar_data($user_id,$role_id);
        //$this->verify_path($this->script_name,$side_bar_data);

        if(isset($_FILES['result'])&&isValidData($_FILES['result']['tmp_name'])&&isset($_POST['TEST_ID'])){
            $TEST_ID = isValidData($_POST['TEST_ID']);
            $all_candidate_card_id = $this->TestResult_model->getTestResultbyTestId($TEST_ID);
            if(count($all_candidate_card_id)<=0){
                $error =array('TYPE'=>'ERROR','MSG'=>"No Candidate found at this Test ");
                $this->session->set_flashdata('ALERT_MSG',$error);

            }

            if($xlsx = SimpleXLSX::parse($_FILES['result']['tmp_name'])){
                $dim = $xlsx->dimension();
                $cols = $dim[0];
                $full_array=array();
                $error="";
                $file_pre_fix = date("Y-m-d-h-i-s-A")."-$user_id".substr($_FILES['result']['name'],strpos($_FILES['result']['name'],'.'));
                foreach ($xlsx->rows() as $k => $r) {
                    if($k==0){
                        continue;
                    }
                    $CARD_ID= isValidData($r[0]);
                    $TEST_SCORE = isValidData($r[1]);

                    $data_row= array("CARD_ID"=>$CARD_ID,"TEST_SCORE"=>$TEST_SCORE,"TEST_ID"=>$TEST_ID);
                    if(is_numeric($TEST_SCORE)&&is_numeric($TEST_ID)&&$CARD_ID){
                        $check = true ;

                        $index = binary_search($all_candidate_card_id,'CARD_ID',$CARD_ID);
                        if($index>-1){
                            $check = false;
                        }

                        if($check){
                            $error.="<div class='text-danger'>this record not found thats why no data has been update [".($k)."] ".json_encode($data_row)."</div>";
                        }else{
                            $full_array[]=$data_row;
                        }

                    }else{
                        $error.="<div class='text-warning'>Something went worng in Row [".($k)."] ".json_encode($data_row)."</div>";
                    }

                }

               // $after_date = date('h:i:s');
                //prePrint($after_date);
                if($error==""&&count($full_array)){
                    $res =$this->TestResult_model->updateTestMarks($full_array,$user_id,$file_pre_fix);
                    //  $res = true;
                    if($res){
                        //echo "SUCCESS";
                        $success =array('TYPE'=>'SUCCESS','MSG'=>'Successfully update test score');
                        $this->session->set_flashdata('ALERT_MSG',$success);
                        //$this->session->set_flashdata('ALERT_MSG', $error);

                    }else{
                        //echo "SOMETHING WENT WORNG CHECK LOG TABLE";
                        $error =array('TYPE'=>'ERROR','MSG'=>"<div class='text-danger'>SOMETHING WENT WORNG CHECK LOG TABLE</div>");
                        $_SESSION['ALERT_MSG']=$error;
                        //$this->session->set_flashdata('ALERT_MSG', $error);

                    }
                }
                else{

                    // echo $error;
                    $error =array('TYPE'=>'ERROR','MSG'=>$error);
                    $this->session->set_flashdata('ALERT_MSG',$error);
                    //$this->session->set_flashdata('ALERT_MSG', $error);

                }
                $config['upload_path']          = './test_result_import_log/';
                $config['allowed_types']        = 'xls|xlsx';
                if($error==""){
                    $config['file_name']			= "TEST_RESULT_SUCCESS_".$file_pre_fix;

                }else{
                    $config['file_name']			= "TEST_RESULT_FAILED_".$file_pre_fix;

                }

                $this->load->library('upload', $config);
                $this->upload->do_upload('result');


            }else{
                $error =array('TYPE'=>'ERROR','MSG'=>"INVALID FILE ...! FILE IS NOT OPEN");
                $this->session->set_flashdata('ALERT_MSG',$error);
                //$this->session->set_flashdata('ALERT_MSG', $error);

            }

            redirect(base_url()."ResultPanel/upload_test_marks");
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
        $this->load->view('admin/read_xls');
        $this->load->view('include/footer_area',$data);
        $this->load->view('include/footer',$data);

    }
    function get_all_card_id(){
         if(isset($_GET['TEST_ID'])&&isValidData($_GET['TEST_ID'])) {
            $TEST_ID = isValidData($_GET['TEST_ID']);
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
                // $detail_cpns = json_decode($result['DETAIL_CPN'],true);
                
                if($k==0){
                       echo "<thead>";
                   
                    
                   
                        echo "<tr>";
                        echo ' <th data-field="state" data-checkbox="true"></th>';
                        echo ' <th data-field="card_id" >CARD ID</th>';
                        echo ' <th data-field="name" >NAME</th>';
                        echo ' <th data-field="surname" >SURNAME</th>';
                        echo ' <th data-field="father_name" >FATHER NAME</th>';
                        echo ' <th data-field="cnic_no" >CNIC NO</th>';
                        echo ' <th data-field="test_score" >TEST SCORE</th>';
                      
                        
                        echo "</tr>
                        </thead>
                        <tbody>";
                        
                       
                }   
                echo "<tr>";
               // $FORM_DATA  =json_decode($result['FORM_DATA'],true);
               // $users_reg = $result['users_reg'];
                echo "<td></td><td>{$result['CARD_ID']}</td>";
                 echo "<td>{$result['FIRST_NAME']}</td>";
                  echo "<td>{$result['LAST_NAME']}</td>";
                   echo "<td>{$result['FNAME']}</td>";
                   echo "<td>{$result['CNIC_NO']}</td>";
                   echo "<td>{$result['TEST_SCORE']}</td>";  
               
                             
                        
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
}
