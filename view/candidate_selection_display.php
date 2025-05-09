<style>
	td{
		font-size: 10pt;
	}
	tr{
		font-size: 10pt;
	}
	th{
		font-size: 10pt;
	}
</style>

<div class="table-responsive wd-tb-cr">
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Campus</th>
			<th>Degree Program</th>
			<th>Shift</th>
			<th>Category</th>
			<th>List #</th>
			<th>Choice</th>
			<th>CPN</th>
			<th>Valid Upto</th>
			<th>Admission Challan</th>
			<th>Retaining Challan</th>
		</tr>
		</thead>
		<tbody>
		<?php
		  
		
		foreach ($selection_record as $record=>$key){
		   
			$profile = $key['PROFILE'];
			$fee_challan = $key['FEE_CHALLAN'];
			$PROGRAM_TITLE = $profile['PROGRAM_TITLE'];
			$CAMPUS_NAME = $profile['NAME'];
			$CAMPUS_ID = $profile['CAMPUS_ID'];
			$CHOICE_NO = $profile['CHOICE_NO'];
			$SHIFT_NAME = $profile['SHIFT_NAME'];
			$PROGRAM_TITLE_CATE = $profile['PROGRAM_TITLE_CATE'];
			$CPN_MERIT_LIST = $profile['CPN_MERIT_LIST'];
			$CATEGORY_NAME = $profile['CATEGORY_NAME'];
			$LIST_NO = $profile['LIST_NO'];
			$APPLICATION_STATUS_ID = $profile['APPLICATION_STATUS_ID'];
			$PROG_LIST_ID = $profile['PROG_LIST_ID'];

			$key = json_encode($key);
			$send_challan_info = base64url_encode(base64_encode(urlencode($key)));

			$challan_no = $fee_challan['CHALLAN_NO'];
			$valid_upto = $fee_challan['VALID_UPTO'];
			
			$challan_expired = "";
			if(date("Y-m-d")>$valid_upto){
			    $challan_expired = "<p class='text-danger'><b>You have not paid this challan upto its valid date. You can not download or pay this challan now.</b></p>";
			}
			
			$paid_amount = $fee_challan['PAYABLE_AMOUNT'];
			$valid_upto = date_create($valid_upto);
			$valid_upto = date_format($valid_upto,'d-m-Y');
            
            
            $paid_status = -1;
            $is_retain_paid_status = -1;
            // if($profile['APPLICATION_ID'] == 17933){
                
                // prePrint($PAID_FEE_CHALLAN);
                // prePrint(merge_list_with_key($PAID_FEE_CHALLAN,'CHALLAN_NO'));
                $retain_challan_no = $challan_no;
                $retain_challan_no[1] = 2;
                //prePrint($retain_challan_no);
                $paid_status = getIndexOfObjectInList_with_multi_check($PAID_FEE_CHALLAN,'CHALLAN_NO',$challan_no,'PAYABLE_AMOUNT',$paid_amount);
                $is_retain_paid_status = getIndexOfObjectInList_with_multi_check($PAID_FEE_CHALLAN,'CHALLAN_NO',$retain_challan_no,'PAYABLE_AMOUNT',200);
            // }
            
			$challan_found = "";
			$retain_challan = "";
			
			if (empty($fee_challan)){
			    	$challan_found = "<p class='text-danger'>Admission Fees challan not found..</p>";
				    $retain_challan = "";
			}else{
				$challan_found = "<span class='btn btn-sm'><a href='".base_url()."CandidateSelection/FeeChallanPrint/$send_challan_info'  target='_blank' title='Click here to download admission fee challan'>Admission Challan</a></span>";
				$retain_challan = "<span class='btn btn-sm'><a href='".base_url()."CandidateSelection/RetainChallanPrint/$send_challan_info' target='_blank' title='Click here to download retain fee challan'>Retain Challan</a></span>";
			}
			if($CHOICE_NO == 1){
			    $retain_challan ='<span class="income-percentange bg-success" style="padding: 5px;font-weight:bold;">NO NEED TO PAY RETAINING CHALLAN</span>';
			}
		    
		  //  if(($PROG_LIST_ID ==159 || $PROG_LIST_ID ==158 || $PROG_LIST_ID ==140 || $PROG_LIST_ID ==141 || $PROG_LIST_ID ==257 || $PROG_LIST_ID ==258 || $PROG_LIST_ID ==259 || $PROG_LIST_ID ==260 || $PROG_LIST_ID ==97)&&$CAMPUS_ID==1){
			 //   $retain_challan = "<p class='text-danger'>Please visit office of the director of concerned department for to collect RETAINING challan</p>";
		  //  }
		    
		    if(is_array($fee_challan)){
		       // prePrint($PAID_FEE_CHALLAN);
		       if($paid_amount <1){
		           $challan_found = '<span class="income-percentange bg-success" style="padding: 5px;font-weight:bold;">FEES ADJUSTED.</span>';
		       }elseif($paid_status>=0){
		            $challan_found = '<span class="income-percentange bg-success" style="padding: 5px;font-weight:bold;">FEES PAID</span>';
		          //  $retain_challan = "-";
		       }elseif($challan_expired!=''){
		           $challan_found = $challan_expired;
		           $retain_challan = $challan_expired;
		       }
		       if($is_retain_paid_status>=0){
		            $retain_challan = '<span class="income-percentange bg-success" style="padding: 5px;font-weight:bold;">FEES PAID</span>';
		       }
		    }
			
			if($APPLICATION_STATUS_ID <>5){
			    	$challan_found = "<p class='text-danger'>Please visit Directorate of Admissions, ".UNIVERSITY_NAME." with all your original documents.</p>";
			        $retain_challan = "";
			}
			
		
			?>
			
			<tr>
				<td><?=$CAMPUS_NAME?></td>
				<td>
					<span class="text-success font-bold"><?=$PROGRAM_TITLE?></span>
				</td>
				<td><?=$SHIFT_NAME?></td>
				<td><?=$CATEGORY_NAME?></td>
				<td><span class="text-primary font-bold"><?=merit_list_decode($LIST_NO)?></span></td>
				<td><?=$CHOICE_NO?></td>
				<td style="color: white"><span class="income-percentange bg-green" style="padding: 5px"><?=$this->TestResult_model->truncate_cpn($CPN_MERIT_LIST,2)?></span></td>
				<td><p><?=$valid_upto?></p></td>
				<td><?=$challan_found?></td>
				<td><?=$retain_challan?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<br><br>
	<h1 class="text-center text-primary">
	                                    Retaining Challan
	                                    </h1>
	                        	<div class="main-sparkline8-hd">
								<h1 class="text-center">After payment of Admission fees Challan, if you desired to be in same discipline/subject where you have been selected means you don't want to promote in next lists, you may do so by downloading computerized Discipline Retaining challan of Rs. 200/- and pay upto its valid date. After payment of Discipline/ Subject Retaining challan of Rs. 200/-, you will not be promoted in upcoming selection lists.
								</h1>
								</div>
									<div class="main-sparkline8-hd">
								<h1 class="text-center text-danger">دخلا فيس جو چالان ادا ڪرڻ کان پوءِ، جيڪڏھن توھان چاھيو ٿا ته ساڳي ڊسيپلين/سليڪشن ۾ برقرار رھو، جنھن ۾ توھان کي چونڊيو ويو آھي ۽ توھان اڳين ڪنھن لسٽ ۾ منتخب ٿيڻ نٿا چاھيو ته توھان 200 روپين جو ڪمپيوٽرائيزڊ ريٽيننگ چالان ڊاؤنلوڊ ڪري مقرره  تاريخ اندر جمع ڪرايو.  Retaining چالان ادا ڪرڻ کان پوء، توهان جي چونڊ ان ئي ڊپارٽمينٽ ۾ رهندي جنهن ۾ توهان کي چونڊيو ويو آهي ۽ توهان جو نالو مستقبل جي lists ۾ شامل نه ڪيو ويندو.
								
								</h1>
								</div>
	
</div>
