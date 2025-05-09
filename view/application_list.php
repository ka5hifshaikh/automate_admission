<section>
	<!-- Static Table Start -->
	<div class="static-table-area">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="sparkline8-list">
						

						<div class="sparkline8-graph">
							<div class="static-table-list table-responsive">
								<table class="table table-hover">
									<thead>
									<tr style="font-size: 11pt; font-family: 'Times New Roman'" class="text-center">
										<th>#</th>
										<th><i class="educate-icon educate-library"></i> Campus</th>
										<th><i class="fa fa-location-arrow"></i> Campus City</th>
										<th>Degree Program</th>
										<th>Admission Session</th>
<!--										<th>Batch</th>-->
										<th>Form Start Date</th>
										<th>Form Last Date</th>
										<th colspan="4">Action</th>
									</tr>
									</thead>
									<?php
									if(is_array($user_application_list) || is_object($user_application_list))
									{
										$sno=0;
										foreach ($user_application_list as $admission_announcement_key=>$admission_announcement_value)
										{

										    $is_already_applied = false;

                                            //this method is define in functions_helper in this mehtod we provide Array and key of arary and finding value method return obj if exists else return false;
                                            //$res = findObjectinList($user_application_list,'ADMISSION_SESSION_ID',$admission_announcement_value['ADMISSION_SESSION_ID']);

                                            // if res contain not false value or any o object it mean user already applied
                                            $APPLICATION_ID=0;
//                                            if($res){
//
//                                                $APPLICATION_ID=$res['APPLICATION_ID'];
//                                                $is_already_applied = true;
//                                            }
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
											$BATCH_REMARKS = $admission_announcement_value['BATCH_REMARKS'];
                                            $APPLICATION_ID=$admission_announcement_value['APPLICATION_ID'];
											if ($BATCH_REMARKS == 'S') $BATCH_REMARKS = "Spring";
											elseif ($BATCH_REMARKS == 'F') $BATCH_REMARKS = "Fall";

											$start_date = date_create($ADMISSION_START_DATE);
											$start_date = date_format($start_date,'D, d-m-Y');
											$end_date = date_create($ADMISSION_END_DATE);
											$end_date = date_format($end_date,'D, d-m-Y');

											$link = "";
                                            $url = "candidate/profile";
                                            $APPLICATION_ID = urlencode(base64_encode($APPLICATION_ID));
                                            $nextpage = urlencode(base64_encode("upload_challan"));

                                            $upload_challan = "form/upload_application_challan/$APPLICATION_ID";
                                            $review_url = "form/review/$APPLICATION_ID/$nextpage";
                                            $submit_url = "form/submit/$APPLICATION_ID";
                                            $challan_url = "form/admission_form_challan";
                                            $application_url = "form/set_application_id/$APPLICATION_ID/";
                                            $review_link = "<a href='".base_url().$application_url.base64_encode(urlencode($review_url))."' class='btn btn-success widget-btn-1 btn-sm'>Review Form</a>";

                                            if ($ADMISSION_START_DATE>date('Y-m-d')){
                                                $upload_challan_link = $challan_link =$submit_link= "";
                                            }

                                            else if ($ADMISSION_END_DATE<date('Y-m-d'))
                                            {
                                                $upload_challan_link =$challan_link =$submit_link= "";
                                            }

                                            else {

                                                $submit_link = "<a href='".base_url().$application_url.base64_encode(urlencode($submit_url))."' class='btn btn-danger widget-btn-1 btn-sm'>Submit Form</a>";
                                                $challan_link = "<a target='_blank' href='".base_url().$application_url.urlencode(base64_encode($challan_url))."' class='btn btn-info widget-btn-1 btn-sm'>Download Challan</a>";
                                                $upload_challan_link = "<a href='".base_url().$application_url.base64_encode(urlencode($upload_challan))."' class='btn btn-warning widget-btn-1 btn-sm'>Upload Challan</a>";
                                            }

                                            $hidden = array('ADMISSION_SESSION_ID' => $ADMISSION_SESSION_ID, 'CAMPUS_ID' => $CAMPUS_ID);
											?>


									<tbody>
									<tr style="font-size: 11pt;color: black">
										<td><?=$sno?></td>
										<td><?=ucwords(strtolower($NAME))?></td>
										<td><?=ucwords(strtolower($LOCATION))?></td>
										<td><?=ucwords(strtolower($PROGRAM_TITLE))?></td>
										<td><?=ucwords(strtolower($BATCH_REMARKS))?> <?=$YEAR?></td>
										<td><?=$start_date?></td>
										<td><?=$end_date?></td>


                                        <td><?=$challan_link?></td>
									</tr>
									</tbody>

								<?php
										}//foreach
									}//if
									?>
								</table>
							</div>
						</div>
					</div>
					</div>
				</div>
				<br>
					<a href="<?=base_url().'form/dashboard'?>" class="btn btn-primary span-left"> <span><i class="fa fa-backward"></i></span> Back To Dashboard </a>
			</div>
		</div>

</section>

