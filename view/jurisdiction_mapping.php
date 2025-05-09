<!-- dual list Start -->
<div class="dual-list-box-area mg-b-15">
	<div class="sparkline10-list">
	<div class="container-fluid">
		<div class="sparkline10-hd">
			<div class="main-sparkline10-hd text-center bg-warning">
				<h1>Jurisdiction Mapping</h1>
			</div>
		</div>
		<?php
				if($this->session->flashdata('message'))
					{
						echo '
                    <div class="alert alert-warning">
                        '.$this->session->flashdata("message").'
                    </div>
                    ';
					}
					?>
		<?=form_open('mapping/save_jurisdiction')?>
<!--		<form id="form" action="save_shift_program_mapping" method="post" class="wizard-big">-->
		<div class="row">
			<div class="col-md-5">
				<label>Campus</label>
				<select name="campus" id="campus" onchange="loadMappedCampus()" class="form-control">
					<option value=""></option>
					<?php
					foreach ($campus as $campus_value)
					{
						?>
						<option value=<?=$campus_value['CAMPUS_ID']?>><?=$campus_value['NAME']?></option>";
						<?php
					}
					unset($campus);
					unset($campus_value);
				?>
				</select>
			</div>
			<div class="col-md-3">
				<label>Province</label>
				<select name="province" id="province" onchange="loadDisticts()" class="form-control">
					<option value=""></option>
					<?php
				
					foreach ($province as $province_value)
					{
						?>
						<option value=<?=$province_value['PROVINCE_ID']?>><?=$province_value['PROVINCE_NAME']?></option>";
						<?php
					}
					unset($province);
					unset($province_value);
					?>
				</select>
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">

					<div class="sparkline10-hd">
						<div class="main-sparkline10-hd">
						<label>Districts</label>
						<span class='font-bold text-right'>Is Juristiction <input type='checkbox' value='Y' name='is_jurisdiction' id='is_jurisdiction'></span>
						
						</div>
					</div>
					<div class="sparkline10-graph">
						<div class="basic-login-form-ad">
							<!--<div class="row">-->

								<!--<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">-->
									<div class="dual-list-box-inner">

											<select class="form-control" name="Districts[]" id="Districts" style="height: 200px" multiple="multiple">
											</select>
									</div>
								<!--</div>-->

							<!--</div>-->
						</div>
					</div>
				</div>
			</div>
			<button type="submit" name="save" class="btn btn-primary">Save</button>
		</form>
			<br/>
		<div class="row">
			<div class="col-md-12 col-sm-12 col-lg-12 col-xs-12"
				 <div class="table-responsive">
					 <table class="table">
						 <thead>
						 <th>S.NO</th>
						 <th>J. ID</th>
						 <th>C. ID</th>
						 <th>D. ID</th>
						 <th>CAMPUS NAME</th>
						 <th>DISTRICT NAME</th>
						 <th>IS JURISDICTION</th>
						 <TH>IS CAMPUS</TH>
						 <TH>IS MAIN</TH>
						 <TH>REMARKS</TH>
						 <th>ACTION</th>
						 </thead>
						 <tbody id="table_data">
						 </tbody>
					 </table>
				 </div>
		</div>
		</div>
	</div>
</div>

<?php $CI =& get_instance(); ?>
<script>
	var csrf_name = '<?php echo $CI->security->get_csrf_token_name(); ?>';
	var csrf_hash = '<?php echo $CI->security->get_csrf_hash(); ?>';
</script>

<script type="text/javascript">

	function DeleteJurisdiction (jurisdiction_id){

		if (jurisdiction_id === "" || jurisdiction_id === 0 || jurisdiction_id == null || isNaN(jurisdiction_id))
			return;

		if (confirm("Do you want to delete?") === false)
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>mapping/DeleteJurisdiction',
			method: 'POST',
			data: {jurisdiction_id:jurisdiction_id,csrf_name:csrf_hash},
			dataType: 'json',
			// success: function(response){
			// 	console.log(response);
			// }
			success: function (data, status) {
				// console.log(status);
				loadMappedCampus ();
				alert_msg("<div class='text-danger'>" + data+ "</div>");
				$('#msg').hide();
				
			},
			beforeSend:function (data, status) {
			    loadMappedCampus ();
				alert_msg("<div class='text-warning text-center'>Processing.... Please wait</div>");
			},
			error:function (data, status) {
			    loadMappedCampus ();
				alert_msg("<div class='text-danger'>" + data.responseText + "</div>");
				// $('#msg').html("<div class='text-danger'>" + data.responseText + "</div>");
				$('#msg').hide();
			},
		});
	}

	function loadDisticts (){

		let province = $("#province").val();
		// alert(shift_id);
		$("#Districts").html('')
		if (province === "" || province === 0 || province == null || isNaN(province))
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>mapping/getdistricts',
			method: 'POST',
			data: {province:province,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				$.each(response, function (index,value) {
					// i++;
					// if (value['REMARKS'] == null)
					// 	var remarks = '';
					let option="";
					option+= "<option value='"+value['DISTRICT_ID']+"'>"+value['DISTRICT_NAME']+"</option>";
					$("#Districts").append(option);
				});
			}
		});
	}
	function loadMappedCampus (){

		let campus = $("#campus").val();
// 		let program_type = $("#program_type").val();
		// alert(shift_id);
			if (campus === "" || campus === 0 || campus == null || isNaN(campus))
			return;
				// campus = 0; 

		$("#table_data").empty();
		$.ajax({
			url:'<?=base_url()?>mapping/getMappedCampusJurisdiction',
			method: 'POST',
			data: {campus:campus,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				$.each(response, function (index,value) {
					i++;
				if (value['REMARKS_JURISDICTION'] == null)
					var remarks = '';
				else remarks = value['REMARKS_JURISDICTION'];
                
                let DISTRICT_ID = value['DISTRICT_ID'];
                let DISTRICT_NAME = district_array[DISTRICT_ID];
                let jurisdiction_id = value['JURISDICTION_ID'];


					let tr="<tr>";
					tr+= "<td>"+i+"</td>";
					tr+= "<td>"+value['JURISDICTION_ID']+"</td>";
					tr+= "<td>"+value['CAMPUS_ID']+"</td>";
					tr+= "<td>"+value['DISTRICT_ID']+"</td>";
					tr+= "<td>"+value['CAMPUS_NAME']+"</td>";
					tr+= "<td>"+DISTRICT_NAME+"</td>";
					tr+= "<td>"+value['IS_JURISDICTION']+"</td>";
					tr+= "<td>"+value['IS_CAMPUS']+"</td>";
					tr+= "<td>"+value['IS_MAIN']+"</td>";
					tr+= "<td>"+remarks+"</td>";
					// tr+= "<td>-</td>";
					tr+= "<td><a href='javascript:void(0)' onclick=DeleteJurisdiction("+jurisdiction_id+");>Delete</a></td>";
					tr+="</tr>";
					$("#table_data").append(tr);
				});
			}
		});
	}
	
	var district_array = [];
		function loadAllDistricts (){
		$.ajax({
			url:'<?=base_url()?>mapping/getAllDistricts',
			method: 'POST',
			data: {csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				$.each(response, function (index,value) {
				    
				    district_array[value['DISTRICT_ID']] = value['DISTRICT_NAME'];
				});
			}
		});
	}

	$(document).ready(function () {
	    loadAllDistricts ();
// 		$("#shift").change(function () {
// 			ignoreMappedPrograms ();
// 		});
// 		loadMappedPrograms ();
	});
// 	console.log(district_array);
</script>