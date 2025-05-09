<!-- dual list Start -->
<div class="dual-list-box-area mg-b-15">
	<div class="sparkline10-list">
		<div class="container-fluid">
			<div class="sparkline10-hd">
				<div class="main-sparkline10-hd text-center bg-warning">
					<h1>Add / Update / Delete  Prerequisite</h1>
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
			<?=form_open(base_url()."prerequisite/save_prerequisite")?>
			<input type="hidden" name="prerequisite_id" id="prerequisite_id" />
			<!--		<form id="form" action="save_shift_program_mapping" method="post" class="wizard-big">-->
			<div class="row">
				<div class="col-lg-4 col-md-4">
					<label>Degree Program</label>
					<?php
					//					print_r($degree_programs);
					?>
					<select name="degree_id" id="degree_id" onchange="get_discipline();" class="form-control">
						<option value=""></option>
						<?php
						foreach ($degree_programs as $degree_programs_key=>$degree_programs_value)
						{
							?>
							<option value=<?=$degree_programs_value['DEGREE_ID']?>><?=$degree_programs_value['DEGREE_TITLE']?></option>";
							<?php
						}
						unset($degree_programs);
						unset($degree_programs_key);
						unset($degree_programs_value);
						?>
					</select>
				</div>

				<div class="col-lg-4 col-md-4">
					<label>Discipline</label>
					<select name="discipline_id" id="discipline_id" onchange="loadMappedMinors()" required class="form-control">
						<option value=""></option>
					</select>
				</div>
				<div class="col-lg-4 col-md-4">

					<div class="form-group">
						<label>Subject</label>
						<select id="subject_id"  name="subject_id" onchange="LoadPrerequisite ();" class="form-control">
							<option></option>
						</select>
					</div>
				</div>
			</div>
<!--			<br>-->
			<div class="row">

				<div class="col-lg-2 col-md-2">
					<div class="form-group">
						<label>Program Type</label>
						<select id="program_type" name="program_type" class="form-control" onchange="getProgramByProgramType()">
<!--							<option></option>-->
							<?php
							foreach ($program_types as $program_type)
							{
								?>
								<option value=<?=$program_type['PROGRAM_TYPE_ID']?>><?=$program_type['PROGRAM_TITLE']?></option>";
								<?php
							}
							unset($program_type);
							unset($program_types);
							?>
						</select>
					</div>
				</div>

				<div class="col-lg-4 col-md-4">
					<div class="form-group">
						<label>Program of Study</label>
						<select id="study_program" name="study_program[]" class="form-control" size="7" multiple="multiple">
						<option></option>
						</select>
					</div>
				</div>
				<div class="col-lg-4 col-md-4">
					<div class="form-group">
						<label>Remarks</label>
						<textarea id="remarks" name="remarks" class="form-control"></textarea>
					</div>
				</div>
				<div class="btn-group-sm">
					<button type="submit" name="save" class="btn btn-primary">Save</button>
				</div>
			</div>

			</form>
			<br/>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<table  class="table">
						<thead>
						<th>S.No</th>
						<th>Minor Mapping ID</th>
						<th>Discipline ID</th>
						<th>Prog List ID</th>
						<th>Prerequisite ID</th>
						<th>Program of Study</th>
						<th>Subject Title</th>
						<th>Remarks</th>
						<th>Action</th>
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

	function DeletePrerequisite (prerequisite_id){

		if (prerequisite_id === "" || prerequisite_id === 0 || prerequisite_id == null || isNaN(prerequisite_id))
			return;

		if (confirm("Do you want to delete?") === false)
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>prerequisite/DeletePrerequisite',
			method: 'POST',
			data: {prerequisite_id:prerequisite_id,csrf_name:csrf_hash},
			dataType: 'json',
			// success: function(response){
			// 	console.log(response);
			// }
			success: function (data, status) {
				// console.log(status);
				alert_msg("<div class='text-danger'>" + data+ "</div>");
				$('#msg').hide();
				LoadPrerequisite();
			},
			beforeSend:function (data, status) {
				alert_msg("<div class='text-warning text-center'>Processing.... Please wait</div>");
			},
			error:function (data, status) {
				alert_msg("<div class='text-danger'>" + data.responseText + "</div>");
				// $('#msg').html("<div class='text-danger'>" + data.responseText + "</div>");
				$('#msg').hide();
			},
		});
	}

	function EditPrerequisite (MINOR_MAPPING_ID,DEGREE_ID,DISCIPLINE_ID,SUBJECT_TITLE,PROG_LIST_ID,REMARKS,PREREQUISITE_ID){
		// alert("working");
		$("#subject_id").val(decodeURIComponent(MINOR_MAPPING_ID));
		$("#degree_id").val(decodeURIComponent(DEGREE_ID));
		$("#discipline_id").val(decodeURIComponent(DISCIPLINE_ID));
		$("#study_program").val(decodeURIComponent(PROG_LIST_ID));
		$("#remarks").val(decodeURIComponent(REMARKS));
		$("#prerequisite_id").val(decodeURIComponent(PREREQUISITE_ID));
		$('html,body').animate({
				scrollTop: $(".container-fluid").offset().top},
			'slow');
	}
	function loadMappedMinors (){

		let discipline_id = $("#discipline_id").val();
		// alert(shift_id);
		if (discipline_id === "" || discipline_id === 0 || discipline_id == null || isNaN(discipline_id))
		{
			return;
		}
		$("#subject_id").empty();
		$.ajax({
			url:'<?=base_url()?>mapping/getMappedMinors',
			method: 'POST',
			data: {discipline_id:discipline_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				// let dropOption= "<option></option>";
				// $("#subject_id").append(dropOption);
				$.each(response, function (index,value) {
					i++;
					var DISCIPLINE_ID 		= value['DISCIPLINE_ID'];
					var DISCIPLINE_NAME 	= value['DISCIPLINE_NAME'];
					var DEGREE_ID 			= value['DEGREE_ID'];
					var MINOR_MAPPING_ID 	= value['MINOR_MAPPING_ID'];
					var SUBJECT_TITLE 		= value['SUBJECT_TITLE'];
					var DISCIPLINE_REMARKS 		= value['DISCIPLINE_REMARKS'];
					var MINOR_REMARKS 		= value['MINOR_REMARKS'];

					if (value['MINOR_REMARKS'] == null || value['MINOR_REMARKS'] === "")
						var MINOR_REMARKS = '';
					else MINOR_REMARKS = value['MINOR_REMARKS'];

					let dropOption= "<option value="+MINOR_MAPPING_ID+">"+SUBJECT_TITLE+"</option>";
					$("#subject_id").append(dropOption);
				});
				LoadPrerequisite ();
			}
		});
	}

	function get_discipline (){

		let degree_id = $("#degree_id").val();
		// alert(shift_id);
		if (degree_id === "" || degree_id === 0 || degree_id == null || isNaN(degree_id))
		{
			return ;
		}
		$("#discipline_id").empty();
		$.ajax({
			url:'<?=base_url()?>mapping/get_discipline',
			method: 'POST',
			data: {degree_id:degree_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				let dropOption= "<option></option>";
				$("#discipline_id").append(dropOption);
				$.each(response, function (index,value) {
					i++;

					var DISCIPLINE_ID 		= value['DISCIPLINE_ID'];
					var DEGREE_ID 			= value['DEGREE_ID'];
					var DISCIPLINE_NAME 	= value['DISCIPLINE_NAME'];
					var CATEGORY_NAME 		= value['CATEGORY_NAME'];
					var P_CODE 				= value['P_CODE'];
					var CATEGORY_CODE 		= value['CATEGORY_CODE'];
					let dropOption= "<option value="+DISCIPLINE_ID+">"+DISCIPLINE_NAME+"</option>";
					$("#discipline_id").append(dropOption);
				});
			}
		});
	}

	function LoadPrerequisite (){

		let subject_id = $("#subject_id").val();
		// alert(shift_id);
		if (subject_id === "" || subject_id === 0 || subject_id == null || isNaN(subject_id))
		{
			return;
		}
		$("#table_data").empty();
		$.ajax({
			url:'<?=base_url()?>prerequisite/getPrerequisite',
			method: 'POST',
			data: {minor_mapping_id:subject_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				$.each(response, function (index,value) {
					i++;
					var DISCIPLINE_ID 		= value['DISCIPLINE_ID'];
					// var DISCIPLINE_NAME 	= value['DISCIPLINE_NAME'];
					var DEGREE_ID 			= value['DEGREE_ID'];
					var MINOR_MAPPING_ID 	= value['MINOR_MAPPING_ID'];
					var SUBJECT_TITLE 		= value['SUBJECT_TITLE'];
					var DISCIPLINE_REMARKS 		= value['DISCIPLINE_REMARKS'];
					var REMARKS 		= value['REMARKS'];
					var PROGRAM_TITLE 		= value['PROGRAM_TITLE'];
					var PREREQUISITE_ID 	= value['PREREQUISITE_ID'];
					var PROG_LIST_ID	 	= value['PROG_LIST_ID'];

					if (value['REMARKS'] == null || value['REMARKS'] === "")
						var REMARKS = '';
					else REMARKS = value['REMARKS'];

					let tr="<tr>";
					tr+= "<td>"+i+"</td>";
					tr+= "<td>"+MINOR_MAPPING_ID+"</td>";
					tr+= "<td>"+DISCIPLINE_ID+"</td>";
					tr+= "<td>"+PROG_LIST_ID+"</td>";
					tr+= "<td>"+PREREQUISITE_ID+"</td>";
					tr+= "<td>"+PROGRAM_TITLE+"</td>";
					tr+= "<td>"+SUBJECT_TITLE+"</td>";
					tr+= "<td>"+REMARKS+"</td>";

					tr+= "<td><a href='javascript:void(0)' onclick=DeletePrerequisite("+PREREQUISITE_ID+");>Delete</a>";
					tr+=" | <a href='javascript:void(0)' onclick=EditPrerequisite('"+encodeURIComponent(MINOR_MAPPING_ID)+"','"+encodeURIComponent(DEGREE_ID)+"','"+encodeURIComponent(DISCIPLINE_ID)+"','"+encodeURIComponent(SUBJECT_TITLE)+"','"+encodeURIComponent(PROG_LIST_ID)+"','"+encodeURIComponent(REMARKS)+"','"+encodeURIComponent(PREREQUISITE_ID)+"');>Edit</a>";

					tr+="</td>";
					tr+="</tr>";
					$("#table_data").append(tr);
				});
			}
		});
	}

	$(document).ready(function () {
		// loadMappedMinors ();
		// LoadPrerequisite ();
		getProgramByProgramType ();
	});

	function getProgramByProgramType (){

		let program_type = $("#program_type").val();
		// alert(shift_id);
		$("#study_program").html('');
		if (program_type === "" || program_type === 0 || program_type == null || isNaN(program_type))
			return;
		$("#study_program").empty();

		$.ajax({
			url:'<?=base_url()?>mapping/getProgramByProgramTypeID',
			method: 'POST',
			data: {program_type:program_type,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				$.each(response, function (index,value) {
					// i++;
					// if (value['REMARKS'] == null)
					// 	var remarks = '';
					let option="";
					option+= "<option value='"+value['PROG_ID']+"'>"+value['PROGRAM_TITLE']+"</option>";
					$("#study_program").append(option);
				});
			}
		});
	}
</script>
