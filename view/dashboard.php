<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/11/2020
 * Time: 4:22 PM
 */
 //if(isset($_GET['des']))
//prePrint($user_application_list);
?>
<style>
.panel-body a{
    color:#FFf;
    margin-top: 0px;
}

</style>

<div id = "min-height" class="container-fluid" style="padding:30px">
    <div class="row">

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                         <!-- <div class="alert alert-danger" role="alert">
                          <strong>You will have to add your qualifications later on. Keep visiting your email account and E-portal account dashboard for further process regarding Admissions 2025.</strong>
                          </div>-->
                            <!-- <div class="alert alert-success" role="alert">-->
                            <!--    <strong>Objection / Query Form</strong> <a href='<?=base_url()?>ObjectionQuery/ticket' target='new'>Click here to submit online objection</a>-->
                            <!--</div>-->
                            
                            <!--<div class="alert alert-warning" role="alert">
                                <strong><p style="text-align: center; font-size: 16pt"><a href='<?=base_url()?>CandidateSelection' target='new'>Click here to download your Admission Fees Challan, if you are selected in Provisional Merit List.</strong> </a> </p>
                            </div>-->
                         <!--<div class="alert alert-success" role="alert">-->
                         <!--       <strong><a href='<?=base_url()?>assets/hostel_form.pdf' target='new'>Click here to Download Hostel Challan and Form</strong> </a>-->
<!--                        </div>-->
                        <div class="alert alert-danger" role="alert">
                                <strong>
                                        <ul>
                                            <li style='padding-top:10px'>1) Candidates applying for admissions to the <b>Bachelor Degree Program</b>, please complete/ process the First Three steps of the Online Admission form. Soon after the conduct of Pre-Entry Test and announcement of pending results of intermediate exams by all the concerned Boards of Sindh Province, options for uploading <b>Domicile & PRC (FOrm-C)</b> and adding <b>Qualifications</b>, selecting <b>Category</b>(ies), <b>Choice</b>(s) and uploading of Domicile & PRC (FOrm-C) will be opened. <b>Keep visiting your email account and E-portal account dashboard for further process regarding Admissions 2025.</b></li>
                                            <li style='padding-top:10px'>2) The verification of paid challans, validation of photographs and documents are under process, please keep patience.
Admit card / Admit Slip will be available here on this dashboard in PDF format. </li>
                                            
                                        </ul>
                                </strong>
                            </div>
                        <center>
                             <!--<iframe width="400" height="315" src="https://www.youtube.com/embed/EpCOJSuP_z0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->
                       
                            <!--<iframe width="400" height="315" src="https://www.youtube.com/embed/rfibZD_n62s" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->
                       
                        <!--<iframe width="560" height="315" src="https://www.youtube.com/embed/eN6u9BUNNBU" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->
                        
                        </center>
                        <div class="white-box">
                            <div class="panel-group edu-custon-design" id="accordion">
                                <?php
                                $last_index =count($user_application_list)-1;
                                 $in = "in";
                                for($i=0 ; $i<=$last_index; $i++){
                                   
                                    $user_application = $user_application_list[$i];
                                     if($user_application['DISPLAY'] != 1){
                                       continue;  
                                     } 
                                      $test_result = $this->TestResult_model->getTestResultbyYearAndApplicationId($user_application['YEAR'],$user_application['APPLICATION_ID']);
                                        
                                      $lat_cpn = $test_cpn = null;
                                        if(count($test_result)>0){
                                            $test_cpn = $test_result[0];
                                           
                                        }
                                        if(count($test_result)>1){
                                           $lat_cpn = $test_result[1]; 
                                           
                                        }
                                    $form_status =  json_decode($user_application['FORM_STATUS'],true);
                                    
                                    $admit_card = $this->AdmitCard_model->getAdmitCardOnAppID($user_application['APPLICATION_ID']);
			                        $admit_card_url = null;
                                    if($admit_card){
                                    $url_data = array("USER_ID"=>$user_application['USER_ID'],"APPLICATION_ID"=>$user_application['APPLICATION_ID'],"CARD_ID"=>$admit_card['CARD_ID']);
		                            $url_data = Base64url_encode(base64_encode(urlencode(json_encode($url_data))));
		                            $admit_card_url = base_url()."slip/".$url_data;
                                        
                                    }
                                    
                                    $APPLICATION_ID = urlencode(base64_encode($user_application['APPLICATION_ID']));
                                    $application_url = "form/set_application_id/{$APPLICATION_ID}/";
                                    $challan_url = "form/admission_form_challan";
                                    
                                    $nextpage ="dashboard"; 
                                    $nextpage = base64_encode($nextpage);
                                    $nextpage = urlencode($nextpage);
                                    $review_url = "form/review/$nextpage";
                                     $lat_info_link = "form/add_lat_info";
                                    
                                    $challan_link =$review_link= "";
                                            if ($user_application['ADMISSION_START_DATE']>date('Y-m-d')){
                                                $challan_link =$review_link= "";
                                            }
                                            else if($user_application['ADMISSION_END_DATE']<date('Y-m-d'))
                                            {
                                                $challan_link =$review_link= "";
                                            }
                                            else {
                                               if($user_application['STATUS_ID']==1){
                                                 $challan_link = "<a target='_blank' href='".base_url().$application_url.urlencode(base64_encode($challan_url))."' class='btn btn-success '>Download Challan</a>";  
                                               }else{
                                                   $challan_link = "<span class='text-success'>Challan Uploaded</span>";
                                               }
                                                 
                                                 
                                            }
                                            $print_link = "form/application_form";
                                    $review_link = "<a href='".base_url().$application_url.urlencode(base64_encode($review_url))."' class='btn btn-warning'>Review</a>";
                                    $print_link = "<a href='".base_url().$application_url.urlencode(base64_encode($print_link))."' class='btn btn-success'>Print Form</a>";
                                    $lat_info_link = "<a href='" . base_url() . $application_url . urlencode(base64_encode($lat_info_link)) . "' class='btn btn-warning'>Click here To Add Lat Info</a>";
                                    $special_url = 'form/add_special_self_category';
                                    $special_self =  base_url() . $application_url . urlencode(base64_encode($special_url)) ;
                                    $eveining_url = 'form/add_evening_category';
                                    $eveining_link =  base_url() . $application_url . urlencode(base64_encode($eveining_url)) ;
                                    $complete_form_url   =  base_url() . $application_url . urlencode(base64_encode('candidate/profile')) ;
                                    
                                  
                                    ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading accordion-head">
                                            <h2 class="panel-title">
                                                <a style="font-size:22px" data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$i?>">
                                                   <?=ucwords(strtolower($user_application['PROGRAM_TITLE']))?> Degree Program <?=ucwords(strtolower($user_application['NAME']))?>  For The Academic Year <?=$user_application['YEAR']?> </a>
                                            </h2>
                                        </div>
                                        <!--id="collapse<?=$i?>" class="panel-collapse panel-ic collapse <?=$in?>"-->
                                        <div >
                                            <div class="panel-body admin-panel-content animated bounce">
                                                <?php
                                                 $category = $this->Application_model->getApplicantCategory($user_application['APPLICATION_ID'], $user_application['USER_ID']);
                                               
                                                $program_choice = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user_application['USER_ID'],$user_application['APPLICATION_ID'],$MORNING_SHIFT=1);
                                                  $evening_program_choice = $this->Application_model->getChoiceByUserAndApplicationAndShiftId($user_application['USER_ID'],$user_application['APPLICATION_ID'],EVENING_SHIFT_ID); 
                                              // prePrint($user_application);
                                                $data['application']=$user_application;
                                                $data['users_reg']=$user;
                                                $data['qualifications']=$qualifications;
                                                $data['category']=$category;
                                                $data['program_choice']=array_merge($program_choice,$evening_program_choice);
                                                show_progress_status($data);
                                                ?>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <table class="table table-bordered">
                                                        <tr >
                                                            <th colspan="2"><h4>Online Admission Form Status</h4></th>
                                                        </tr>
                                                         
                                                        <tr >
                                                            <th style="width: 30%;">Form Status</th>
                                                            <?php
                                                            if($user_application['STATUS_NAME']=="DRAFT" && 1==$user_application['IS_PROFILE_PHOTO_VERIFIED'] &&$user_application['IS_VERIFIED']=="Y" &&$user_application['CHALLAN_IMAGE'] ){
                                                                  echo "<th class='text-success ' >Eligible for Pre-Entry Test <sup class='text-danger blink_me' >New</sup></th>"; 
                                                            }else{
                                                               echo "<th class='text-success' >{$user_application['STATUS_NAME']}</th>"; 
                                                            }
                                                            ?>
                                                            
                                                        </tr>
                                                        <tr >
                                                            <th >Form Remarks</th>
                                                            <th ><div class="text-danger"><?=$user_application['MESSAGE']?$user_application['MESSAGE']:""?></div></th>
                                                        </tr>
                                                        <?php
                                                        $is_challan_show = true;
                                                        if(is_array($form_status)){
                                                        $PROFILE_PHOTO = $form_status['PROFILE_PHOTO'];
                                                        $CHALLAN = $form_status['CHALLAN'];
                                                        $ADDITIONAL_DOCUMENT =  $form_status['ADDITIONAL_DOCUMENT'];
                                                        if(2==$user_application['IS_PROFILE_PHOTO_VERIFIED']){
                                                                  $profile_uplaod_url = base_url().$application_url.urlencode(base64_encode("Candidate/upload_profile_image"));
                                                                  $upload_button = "<span class='text-danger'>Your profile photo has been rejected due to inappropriate photo, kindly re-upload your profile photo otherwise your admit card will not be issued.</span>
                                                                  <br><br>
                                                                  <a class='btn btn-danger' href='$profile_uplaod_url'>Upload Profile</a>";
                                                        }else if(1==$user_application['IS_PROFILE_PHOTO_VERIFIED']){
                                                            $upload_button = "<button class='btn btn-success'>Verified</button>";
                                                        }else{
                                                            $upload_button = "<button class='btn btn-warning'>Pennding Verification</button>";
                                                        }
                                                         echo "<tr >
                                                                    <th >Profile Photo</th>
                                                                    <th >$upload_button</th>
                                                            </tr>";
                                                            
                                                        if($user_application['IS_VERIFIED']=="Y" &&$user_application['CHALLAN_IMAGE']){
                                                                     $upload_button = "<button class='btn btn-success'>Verified</button>";
                                                                     $is_challan_show =false;
                                                        }
                                                        else if($user_application['IS_VERIFIED']=="R" ){
                                                                     $upload_button = "<button class='btn btn-danger'>Not Verifed / Rejected</button><span class='text-danger'><br>Your uploaded challan image is not verified that's why your Admit card will not be issued. If you have paid the challan within due dates, please visit Directorate of Admissions, ".UNIVERSITY_NAME." along with original paid copy of your challan upto Wednesday  25.10.2023 within office hours (09:00am to 03:00pm) OR you can send it through email at <span class='text-info' >dir.adms@umpk.edu.pk</span>";
                                                                     $is_challan_show =false;
                                                        }else{
                                                                    $upload_button="<button class='btn btn-warning'>Pennding Verification</button>";
                                                        }    
                                                          echo "<tr >
                                                                    <th >Registration Fee Challan</th>
                                                                    <th >$upload_button</th>
                                                            </tr>";
                                                            $msg = ucwords(strtolower($ADDITIONAL_DOCUMENT['STATUS']));
                                                        //   echo "<tr >
                                                        //             <th >Additional Documents</th>
                                                        //             <th ><button class='btn btn-warning'>{$msg}</div></th>
                                                        //     </tr>";
                                                            
                                                          
                                                        }
                                                      
                                                        
                                                if ($user_application['STATUS_ID'] >= 3 && $user_application['STATUS_ID'] <= 5 && $user_application['PROGRAM_TYPE_ID']==2) {

                                                    ?>
                                                    <!--<tr>-->
                                                    <!--    <th>Add LAT Information</th>-->
                                                    <!--    <th>-->
                                                    <!--        <div class="text-danger"></div>-->
                                                    <!--    </th>-->
                                                    <!--</tr>-->
                                                    <?php
                                                    }
                                                        ?>
                                                        
                                                    </table>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <table class="table table-bordered">
                                                        <tr >
                                                            <th colspan="2"><h4>Admission Form Information</h4></th>

                                                        </tr>
                                                        <?php
                                                        if($user_application['STATUS_ID'] <3 ){
                                                        ?>
                                                        
                                                         <tr >
                                                            <th style="width: 30%;">Application Form</th>
                                                            <th ><a class='btn  btn-success' href='<?=$complete_form_url?>'>Click Here To Complete Your Form</a></th>
                                                        </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                        <tr >
                                                            <th style="width: 30%;">Registration Fee Challan</th>
                                                            <th ><?=$is_challan_show?$challan_link:"";?></th>
                                                        </tr>
                                                        <?php
                                                        if($user_application['STATUS_ID']>=FINAL_SUBMIT_STATUS_ID){
                                                            ?>
                                                            <tr >
                                                            <th style="width: 30%;">Online Admission Form</th>
                                                            <th><?=$print_link?></th>
                                                        </tr>
                                                            <?php
                                                        }
                                                        else{
                                                        ?>
                                                        
                                                         <tr >
                                                            <th style="width: 30%;">Form Review</th>
                                                            <th ><?=$review_link?></th>
                                                        </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                       
                                                        
                                                        <tr >
                                                            <th >Pre Entry Test Admit Card</th>
                                                            <th ><?php
                                                            if($admit_card_url!=null&&$admit_card['IS_DISPATCHED']=='Y'&&($user_application['STATUS_ID'] == 1 ||$user_application['STATUS_ID'] == 3||$user_application['STATUS_ID'] == 4||$user_application['STATUS_ID'] == 5)){
                                                                
                                                                ?>
                                                                <a href='<?=$admit_card_url?>' class='btn btn-primary' target='new'> Click here to print your Admit Card</a>
                                                                <?php
                                                                
                                                            }else{
                                                                if($user_application['PROGRAM_TYPE_ID']==2){
                                                                    echo "There is no Pre-Entry Test for admission to BS (3RD YEAR) / Master Degree programs except LL.M (Evening) Degree Program. Merit Lists will be made on their Academic Marks.";     
                                                                }else{
                                                                    if($user_application['SESSION_ID']==10){
                                                                        if(!$admit_card){
                                                                            echo '<div class="bg-dark" style="background-color: black;color: white;padding: 8px; word-spacing:3px; text-align:justify;border-radius:6px;">All Admit Cards for Pre-Entry Test - 2024 (Phase-II) have issued. If your admit card is not available for download, please email to dir.adms@umpk.edu.pk or visit Admission office before Saturday 28-10-2023. Pre-Entry Test - 2024 (Phase-II) will be conducted on Sunday October 29, 2023.</div>';    
                                                                    
                                                                            // echo '<span class="bg-dark" style="background-color: black;color: white;padding: 2px;">Your Admit Card  for Pre-Entry Test - 2024 (Phase-II) will be issued on Wednesday October 25, 2023. Pre-Entry Test - 2024 (Phase-II) will be conducted on Sunday October 29, 2023.</span>';    
                                                                        }
                                                                        
                                                                    }
                                                                    //echo "Please send your cnic No. and image of paid challan at dir.adms@umpk.edu.pk ";
                                                                }
                                                               
                                                            }
                                                            ?></th>
                                                        </tr>

                                                        <tr >
                                                            <th >Pre Entry Test Score</th>
                                                            <th > <?php
                                                               
                                                                  if (($user_application['STATUS_ID'] >= 1 && $user_application['STATUS_ID'] <= 5)&&$test_cpn!=null&&isset($test_cpn['TEST_SCORE'])&&$test_cpn['TEST_SCORE']){
                                                                      echo $this->TestResult_model->truncate_cpn($test_cpn['TEST_SCORE']);
                                                                  }
                                                                ?></th>
                                                        </tr>
                                                        <?php
                                                        if(($user_application['STATUS_ID'] >= 3 && $user_application['STATUS_ID'] <= 5)&&$lat_cpn!=null){
                                                            ?>
                                                             <tr >
                                                            <th >LAW CPN</th>
                                                            <th >
                                                                <?php
                                                                  if (($user_application['STATUS_ID'] >= 3 && $user_application['STATUS_ID'] <= 5)&&$lat_cpn!=null&&$lat_cpn['CPN']>0){
                                                                      echo $this->TestResult_model->truncate_cpn($lat_cpn['CPN']);
                                                                  }
                                                                ?>
                                                            </th>
                                                        </tr>

                                                            
                                                            <?php
                                                        }
                                                        
                                                        ?>
                                                        <tr >
                                                            <th >PET CPN</th>
                                                            <th >
                                                                <?php
                                                               
                                                                  if (($user_application['STATUS_ID'] >= 3 && $user_application['STATUS_ID'] <= 5)&&$test_cpn!=null&&isset($test_cpn['CPN'])&&$test_cpn['CPN']>0){
                                                                      echo $this->TestResult_model->truncate_cpn($test_cpn['CPN']);
                                                                  }
                                                                ?>
                                                            </th>
                                                        </tr>
                                                         

                                                    </table>
                                                   
                                                </div>
                                                  
                                               
                                            </div>
                                        </div>
                                    </div>
                                        <?php
                                                if((IS_SPECIAL_SELF_OPEN==1&&($user_application['STATUS_ID']==5||$user_application['STATUS_ID']==4)&&$user_application['CAMPUS_ID']==1 && $user_application['PROGRAM_TYPE_ID']==1)||$IS_SUPER_PASSWORD_LOGIN=='Y'){
                                                ?>
                                                 <div class="row"> 
                                                    <div class="col-md-12">
                                                        <div class="alert alert-danger" role="alert">
                                                             <strong><a href='<?=$special_self?>' target='new' style='color:red;' onclick="return confirm('Are you sure\nYou want to apply for Special Self Finance Category?')"><h2><center><u>Click here to apply for admission on Special Self-Finance Category</u></center></h2> </a></strong>
                                                          </div>
                                                    </div>
                                                </div>
                                                <?php
                                                }
                                                ?>
                                                 <?php
                                                if($IS_SUPER_PASSWORD_LOGIN=='Y'||(OPEN_EVENING_PORTAL_SHOW_LINK==1&&($user_application['STATUS_ID']<=5&&$user_application['STATUS_ID']>=1))){
                                                ?>
                                                <div class="row"> 
                                                    <div class="col-md-12">
                                                        <div >
                                                             <strong><a href='<?=$eveining_link?>'  style='color:red;' onclick="return confirm('Are you sure\nYou Want To Apply in Evening Degree Programs?')"><h2><center><u>Click Here To Apply For Evening Degree Programs</u></center></h2> </a></strong>
                                                          </div>
                                                    </div>
                                                </div>
                                                <?php
                                                }
                                                ?>
                                <?php
                                  $in = "";
                                }
                                ?>


                            </div>
                        </div>
                    </div>

    </div>
    
<!--    <div ng-app="CurrentSelection" ng-controller="formCtrl">
	    <div class="single-pro-review-area mt-t-30 mg-b-15">
	        <div class="container-fluid">
		        <div class="profile-info-inner">
			        <div class="alert alert-success" role="alert">
				        <strong><p style="text-align: center; font-size: 14pt">Your Current Enrolled Degree Program</p></strong>
			        </div>
			        <div class="row" ng-repeat="stdData in stdCurrentData">
				        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

						    <div class="profile-img">
							
						    </div>

						    <div class="profile-details-hr" >
							    <div class="row">
								    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
									    <div class="address-hr">
										<p><b>Session</b><br /> ({{stdData.PROGRAM_TYPE_TITLE}}) {{stdData.YEAR}} {{stdData.BATCH_REMARKS}}</p>
									</div>
								</div>
								<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
									<div class="address-hr tb-sm-res-d-n dps-tb-ntn">
										<p><b>Campus</b><br /> {{stdData.NAME}}</p>
									</div>
								</div>
								<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
									<div class="address-hr tb-sm-res-d-n dps-tb-ntn">
										<p><b>Discipline/Program</b><br /> {{stdData.PROGRAM_TITLE}}</p>
									</div>
								</div>
							</div>
						    <div class="row">
								<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
									<div class="address-hr">
										<p><b>Shift</b><br /> {{stdData.SHIFT_NAME}}</p>
									</div>
								</div>
								
								<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
									<div class="address-hr tb-sm-res-d-n dps-tb-ntn">
										<p><b>Roll No</b><br /> {{stdData.ROLL_NO}}</p>
									</div>
								</div>
								
							
								<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
									<div class="address-hr tb-sm-res-d-n dps-tb-ntn">
										<p><b>Category</b><br /> {{stdData.CATEGORY_NAME}}</p>
									</div>
								</div>
							</div>
							<hr>
			            </div>
				    </div>
	            </div>
            </div>
        </div>
    </div>
	
</div>-->

<script>
	var app = angular.module('CurrentSelection', []);
	app.controller('formCtrl', function($scope,$http,$window) {
		$scope.getCurrentSelection = function () {
			$scope.stdCurrentData = null;
			// let data = {search_value:search_value,search_by:search_by};
			$scope.ProgramTypes = null;
			$http.post('<?=base_url()?>Form/getApplicantCurrentSelection').then(function success(response) {
				if (response.status == 204) {
					// $scope.errorMSG= 'Sorry could not find data';
				}
				if (response.status == 200) {
					let array_data = response.data;
					array_data =  array_data.filter((data)=>{
					      // console.log(data);
					       return data.YEAR==2025?data:null;
					       
					   });
					if(array_data.length==1){
					$scope.stdCurrentData = array_data;    
					}
					
				 //	console.log(array_data);
				}
			}, function error(response) {
				// console.log(response);
			});
		}
		$scope.getCurrentSelection();
		<?php
            if($IS_SUPER_PASSWORD_LOGIN=='Y'){
                ?>
                //$scope.getCurrentSelection();
                <?php
            }
        ?>
		//$scope.getCurrentSelection();
	});
</script>
