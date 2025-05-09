<!-- dual list Start -->
<div class="dual-list-box-area mg-b-15">
	<div class="sparkline10-list">
		<div class="container-fluid">
			<div class="sparkline10-hd">
				<div class="main-sparkline10-hd text-center bg-warning">
					<h1>Add Edit Minors</h1>
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
			<?=form_open(base_url()."mapping/save_minor")?>
			<input type="hidden" name="minor_mapping_id" id="minor_mapping_id" />
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
					<?php
					//					print_r($degree_programs);
					?>
					<select name="discipline_id" id="discipline_id" onchange="loadMappedMinors()" required class="form-control">
						<option value=""></option>
					</select>
				</div>

				<div class="col-lg-4 col-md-4">

					<div class="form-group">
						<label>Subject Title</label>
						<input type="text" name="minor_name" id="minor_name" class="form-control" required>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="btn-group-sm">
					<button type="submit" name="save" class="btn btn-primary">Save</button>
				</div>
			</div>

			</form>
			<br/>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-lg-12 col-xs-12"
				<div class="table-responsive">
					<table class="table">
						<thead>
						<th>S.No</th>
						<th>Minor Mapping ID</th>
						<th>Discipline ID</th>
						<th>Subject Title</th>
						<th>Discipline Name</th>
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

	function DeleteMinorSubject (MINOR_MAPPING_ID){

		if (MINOR_MAPPING_ID === "" || MINOR_MAPPING_ID === 0 || MINOR_MAPPING_ID == null || isNaN(MINOR_MAPPING_ID))
			return;

		if (confirm("Do you want to delete?") === false)
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>mapping/DeleteMinorSubject',
			method: 'POST',
			data: {minor_mapping_id:MINOR_MAPPING_ID,csrf_name:csrf_hash},
			dataType: 'json',
			// success: function(response){
			// 	console.log(response);
			// }
			success: function (data, status) {
				// console.log(status);
				alert_msg("<div class='text-danger'>" + data+ "</div>");
				$('#msg').hide();
				loadMappedMinors();
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

	function EditMinorSubject (MINOR_MAPPING_ID,DEGREE_ID,DISCIPLINE_ID,SUBJECT_TITLE){
		// alert("working");
		$("#minor_mapping_id").val(decodeURIComponent(MINOR_MAPPING_ID));
		$("#degree_id").val(decodeURIComponent(DEGREE_ID));
		$("#discipline_id").val(decodeURIComponent(DISCIPLINE_ID));
		$("#minor_name").val(decodeURIComponent(SUBJECT_TITLE));
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
		$("#table_data").empty();
		$.ajax({
			url:'<?=base_url()?>mapping/getMappedMinors',
			method: 'POST',
			data: {discipline_id:discipline_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				console.log(response);
				let i=0;
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

					let tr="<tr>";
					tr+= "<td>"+i+"</td>";
					tr+= "<td>"+MINOR_MAPPING_ID+"</td>";
					tr+= "<td>"+DISCIPLINE_ID+"</td>";
					tr+= "<td>"+SUBJECT_TITLE+"</td>";
					tr+= "<td>"+DISCIPLINE_NAME+"</td>";
					tr+= "<td>"+MINOR_REMARKS+"</td>";

					tr+= "<td><a href='javascript:void(0)' onclick=DeleteMinorSubject("+MINOR_MAPPING_ID+");>Delete</a>";
					tr+=" | <a href='javascript:void(0)' onclick=EditMinorSubject('"+encodeURIComponent(MINOR_MAPPING_ID)+"','"+encodeURIComponent(DEGREE_ID)+"','"+encodeURIComponent(DISCIPLINE_ID)+"','"+encodeURIComponent(SUBJECT_TITLE)+"');>Edit</a>";

					tr+="</td>";
					tr+="</tr>";
					$("#table_data").append(tr);
				});
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

	$(document).ready(function () {
		$("#category_type_id").change(function () {
			loadMappedCategory ();
		});
		loadMappedMinors ();
	});
</script>
