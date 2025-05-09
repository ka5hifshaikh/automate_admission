<section>
	<!-- Static Table Start -->
	
	<div class="static-table-area">
		<div class="container-fluid">
		      		 
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="sparkline8-list">
						<div class="sparkline8-hd">
							<div class="main-sparkline8-hd">
							    <h2 class="text-center" style="font-weight: bold">ONLINE ADMISSION FORM FOR THE ACADEMIC YEAR 2025</h2>
							    
								<h1 class="text-center" style="font-weight: bold"><?=UNIVERSITY_NAME?> has announced Admissions in the following Campuses</h1>

							    <ul class="list-group dual-list-box-inner" style="margin-top: 2%; margin-bottom: 2%">
								<li class="list-group-item text-center" style="font-weight: bold; font-size:14pt">Please Read Important Instructions</li>
								<li class="list-group-item  font-weight-bold" style="font-weight: bold">1. Dear Candidate,&nbsp;please carefully select your desired <span style="color: black"> CAMPUS & DEGREE PROGRAM </span> from the following list in which you want to take the admission and you are allowed to choose only <b>ONE</b> campus.</li>
								<li class="list-group-item  font-weight-bold" style="font-weight: bold">2. Must verify your form at the final stage before submitting it. After submission, you will not be allowed to edit your online application form.</li>
									<!--<li class="list-group-item list-group-item-info font-weight-bold" style="font-weight: bold">3. Applicants applying for admission to B.Ed (Secondary) 1.5 Year or B.Ed (Secondary) 2.5 Year are advised to select <strong><span style="color: black">BS (3RD YEAR) / MASTER DEGREE PROGRAMS</span></strong> from below option.</li>-->
							
            				</ul>

							</div>
						</div>
						
                        <div>
                           
                            	<form action="<?=base_url()?>form/announcement" method='post' >
                            	    <label style='font-size:14pt'>Apply for</label>
							            <select style="font-size: large;font-weight: 900;"  name="program_type_id" id="program_type" onchange="this.form.submit()" class="form-control">
							                <option value="0">Choose Degree Program</option>
							                <option value="1" <?=($program_type_id==1)?"selected":""?>>BACHELOR DEGREE PROGRAMS</option>
							                <option value="3" <?=($program_type_id==3)?"selected":""?>>BS (3RD YEAR)</option>
							                <option value="2" <?=($program_type_id==2)?"selected":""?>>MASTER DEGREE PROGRAMS</option>
							                <option value="4" <?=($program_type_id==4)?"selected":""?>>WEEKEND DEGREE PROGRAM</option>
										</select>
							        </form>
						
                        </div>
                        <br/>
						<div class="sparkline8-graph">
							<div class="static-table-list table-responsive">
								<table class="table table-hover">
									<thead>
									<tr style="font-size: 11pt; font-family: 'Times New Roman'" class="text-center">
										<th>#</th>
										<th><i class="educate-icon educate-library"></i> Campus</th>
										<!--<th><i class="fa fa-location-arrow"></i> Campus City</th>-->
										<th>Degree Program</th>
										<th>Admission Session</th>
<!--										<th>Batch</th>-->
										<!--<th>Form Start Date</th>-->
										<th>Form Last Date</th>
										<th>Apply Now</th>
									</tr>
									</thead>
									<?php
									//prePrint($program_type_id);
//									exit;
                                    // prePrint($admission_announcement);
                                    // exit();
                                    $p_id = $program_type_id;
                                    if($program_type_id>2){
                                        $program_type_id  =2;
                                    }
									if(is_array($admission_announcement) || is_object($admission_announcement))
									{
										$sno=0;
										foreach ($admission_announcement as $admission_announcement_key=>$admission_announcement_value)
										{

                                            if($program_type_id!=$admission_announcement_value['PROGRAM_TYPE_ID']){
                                                continue;        
                                            }       
										    $is_already_applied = false;
                                        //     prePrint($valid_campus);
                                        //     prePrint($admission_announcement_value['CAMPUS_ID']);
                                        //   exit();
                                            $is_valid_campus = findObjectinList($valid_campus,'CAMPUS_ID',$admission_announcement_value['CAMPUS_ID']);
                                            if(!$is_valid_campus){
                                                continue;
                                            }
                                            //this method is define in functions_helper in this mehtod we provide Array and key of arary and finding value method return obj if exists else return false;
                                            
                                            
                                           $invisible = false;
                                           
                                            foreach($user_application_list as $user_application){
                                             
                                                
                                                if($user_application['SESSION_ID']==$admission_announcement_value['SESSION_ID'] &&$user_application['PROGRAM_TYPE_ID'] ==$admission_announcement_value['PROGRAM_TYPE_ID']){
                                                        $invisible = true;
                                                        break;
                                                }
                                            }
                                           // var_dump($invisible);
                                            $res = findObjectinList($user_application_list,'ADMISSION_SESSION_ID',$admission_announcement_value['ADMISSION_SESSION_ID']);
                            
                                            // if res contain not false value or any o object it mean user already applied
                                            $APPLICATION_ID=0;
                                            if($res){

                                                $APPLICATION_ID=$res['APPLICATION_ID'];
                                                $is_already_applied = true;
                                            }
//                                            foreach($user_application_list as $user_app){
//                                                if($SESSION_ID==$user_app['SESSION_ID']){
//
//                                                }
//                                            }
                                            $sno++;
											$NAME = $admission_announcement_value['NAME'];
											$YEAR = $admission_announcement_value['YEAR'];
											$ADMISSION_SESSION_ID = $admission_announcement_value['ADMISSION_SESSION_ID'];
											$CAMPUS_ID = $admission_announcement_value['CAMPUS_ID'];
											$SESSION_ID = $admission_announcement_value['SESSION_ID'];
											$PROGRAM_TYPE_ID = $admission_announcement_value['PROGRAM_TYPE_ID'];
											$ADMISSION_START_DATE = $admission_announcement_value['ADMISSION_START_DATE'];
											$ADMISSION_END_DATE = $admission_announcement_value['ADMISSION_END_DATE'];
											$LOCATION = $admission_announcement_value['LOCATION'];
											$PROGRAM_TITLE = $admission_announcement_value['PROGRAM_TITLE'];
											if($p_id==3){
											    $PROGRAM_TITLE = "BS (3RD YEAR)";
											}else if($p_id==4){
											    $PROGRAM_TITLE = "WEEKEND DEGREE PROGRAM";
											}
											$BATCH_REMARKS = $admission_announcement_value['BATCH_REMARKS'];
											if ($BATCH_REMARKS == 'S') $BATCH_REMARKS = "Spring";
											elseif ($BATCH_REMARKS == 'F') $BATCH_REMARKS = "Fall";

											$start_date = date_create($ADMISSION_START_DATE);
											$start_date = date_format($start_date,'D, d-m-Y');
											$end_date = date_create($ADMISSION_END_DATE);
											$end_date = date_format($end_date,'D, d-m-Y');

											$link = "";
                                            $url = "form/upload_application_challan";
                                            $APPLICATION_ID = urlencode(base64_encode($APPLICATION_ID));
                                            $challan_url = "form/admission_form_challan";
                                          //  set_application_id($APPLICATION_ID,$url);
                                                $application_url = "form/set_application_id/$APPLICATION_ID/";
										    if ($ADMISSION_START_DATE>date('Y-m-d')){
                                                $link = "will be open soon";
                                                $challan_link = "";
                                            }
											elseif ($ADMISSION_END_DATE<date('Y-m-d'))
                                            {
                                                $link = 'Form over due date';
                                                $challan_link = "";
                                                if($is_already_applied){
                                                    $dash_url = "form/dashboard";
                                                     $challan_link = "<a href='".base_url().$application_url.urlencode(base64_encode($dash_url))."' class='btn btn-info widget-btn-1 btn-sm'>Go To Dashboard</a>";
                                                }else{
                                                   // continue;
                                                }
                                            }
											else {
                                                
											    if($is_already_applied){
                                                    $url = "form/upload_application_challan";
                                                    $challan_url = "form/admission_form_challan";
                                                    $link="<a href='".base_url().$application_url.urlencode(base64_encode($url))."' class='btn btn-warning widget-btn-1 btn-sm'>Already Applied click here to next</a>";
                                                    $challan_link = "<a href='".base_url().$application_url.urlencode(base64_encode($challan_url))."' class='btn btn-info widget-btn-1 btn-sm'>Download Challan</a>";
                                                }else{
                                                    
                                                    if(!$invisible){
                                                        $link="<button type='submit' class='btn btn-success widget-btn-1 btn-sm'>Apply Now</button>";
                                                       
                                                        $url = "form/addApplication";  
                                                        
                                                        
                                                        $challan_link="";   
                                                    }else{
                                                       $challan_link="";     
                                                    }
                                                        
                                                       
                                                    
                                                    
                                                }

                                            }
											$hidden = array('ADMISSION_SESSION_ID' => $ADMISSION_SESSION_ID, 'CAMPUS_ID' => $CAMPUS_ID);
											?>

											<?=($is_already_applied||$invisible)?'':form_open(base_url().$url,'',$hidden)?>
									<tbody>
									<tr style="font-size: 11pt;color: black">
										<td><?=$sno?></td>
										<td><?=ucwords(strtolower($NAME))?></td>
										<!--<td><?=ucwords(strtolower($LOCATION))?></td>-->
										<td><?=ucwords(strtolower($PROGRAM_TITLE))?></td>
										<td><?=ucwords(strtolower($BATCH_REMARKS))?></td>
										<!--<td><?=$start_date?></td>-->
										<td><?=$end_date?></td>

										<td><?=$link?></td>
                                        <td><?=$challan_link?></td>
									</tr>
									</tbody>
											<?=$is_already_applied?'':form_close()?>
								<?php
										}//foreach
									}//if
									?>
								</table>
							</div>
						</div>
						<br>
						<br>
							<div class="row">
							    <div class="col-lg-4 col-md-4">
							        </div>
							<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 center">
							    </div>
							    </div>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 center">
								<div class="login-social-inner">
								
									<a style="float: right;" href="<?=base_url().'logout'?>" class="button btn-social basic-ele-mg-b-10 twitter span-left"> <span><i class="fa fa-power-off"></i></span> Logout </a>
								</div>
							</div>
						</div>
					</div>
					</div>
				</div>
			</div>
		</div>

</section>

