<!-- dual list Start -->
<div class="dual-list-box-area mg-b-15">
	<div class="sparkline10-list">
		<div class="container-fluid">
			<div class="sparkline10-hd">
				<div class="main-sparkline10-hd text-center bg-warning">
					<h1>Selection Category Mapping</h1>
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
			<?=form_open(base_url()."mapping/save_category")?>
			<input type="hidden" name="category_id" id="category_id" />
			<!--		<form id="form" action="save_shift_program_mapping" method="post" class="wizard-big">-->
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
					<label>Category Type</label>
					<select name="category_type_id" id="category_type_id" required class="form-control">
						<option value=""></option>
						<?php
						foreach ($category_type as $category_type_key=>$category_type_value)
						{
							?>
							<option value=<?=$category_type_value['CATEGORY_TYPE_ID']?>><?=$category_type_value['CATEGORY_NAME']?></option>";
							<?php
						}
						unset($category_type);
						unset($category_type_key);
						unset($category_type_value);
						?>
					</select>
				</div>

				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

					<div class="form-group">
						<label>Category Name</label>
						<input type="text" name="category_name" id="category_name" class="form-control" required>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">

					<div class="form-group">
						<label>Code</label>
						<input type="number" id="code" name="code" class="form-control">
					</div>
				</div>

				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
					<div class="form-group">
						<label>P Code</label>
						<input type="text" id="p_code" name="p_code" class="form-control">
					</div>
				</div>

				<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<div class="form-group">
						<label>Remarks</label>
						<input type="text" id="remarks" name="remarks" class="form-control">
					</div>
				</div>
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
						<th>Category ID</th>
						<th>Category Type ID</th>
						<th>Category Type Name</th>
						<th>Category Name</th>
						<th>P Code</th>
						<th>Code</th>
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

	function DeleteCategoryMap (category_id){

		if (category_id === "" || category_id === 0 || category_id == null || isNaN(category_id))
			return;

		if (confirm("Do you want to delete?") === false)
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>mapping/DeleteMappedCategory',
			method: 'POST',
			data: {category_id:category_id,csrf_name:csrf_hash},
			dataType: 'json',
			// success: function(response){
			// 	console.log(response);
			// }
			success: function (data, status) {
				// console.log(status);
				alert_msg("<div class='text-danger'>" + data+ "</div>");
				$('#msg').hide();
				loadMappedCategory();
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

	function EditCategoryMap (CATEGORY_ID,CATEGORY_TYPE_ID,CATEGORY_NAME,CATEGORY_TYPE_NAME,CATEGORY_CODE,P_CODE,remarks){
		// alert("working");
		$("#category_type_id").val(decodeURIComponent(CATEGORY_TYPE_ID));
		$("#category_name").val(decodeURIComponent(CATEGORY_NAME));
		$("#p_code").val(decodeURIComponent(P_CODE));
		$("#code").val(decodeURIComponent(CATEGORY_CODE));
		$("#remarks").val(decodeURIComponent(remarks));
		$("#category_id").val(decodeURIComponent(CATEGORY_ID));

		$('html,body').animate({
				scrollTop: $(".container-fluid").offset().top},
			'slow');
	}

	function loadMappedCategory (){

		let category_type_id = $("#category_type_id").val();
		// alert(shift_id);
		if (category_type_id === "" || category_type_id === 0 || category_type_id == null || isNaN(category_type_id))
			category_type_id = 0;
		$("#table_data").empty();
		$.ajax({
			url:'<?=base_url()?>mapping/getMappedCategory',
			method: 'POST',
			data: {category_type_id:category_type_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				// console.log(response);
				let i=0;
				$.each(response, function (index,value) {
					i++;
					if (value['CATEGORY_REMARKS'] == null || value['CATEGORY_REMARKS'] === "")
						var remarks = '';
					else remarks = value['CATEGORY_REMARKS'];

					var CATEGORY_ID 		= value['CATEGORY_ID'];
					var CATEGORY_TYPE_ID 	= value['CATEGORY_TYPE_ID'];
					var CATEGORY_TYPE_NAME 	= value['CATEGORY_TYPE_NAME'];
					var CATEGORY_NAME 		= value['CATEGORY_NAME'];
					var P_CODE 				= value['P_CODE'];
					var CATEGORY_CODE 		= value['CATEGORY_CODE'];

					// alert(CATEGORY_CODE);
					let tr="<tr>";
					tr+= "<td>"+i+"</td>";
					tr+= "<td>"+value['CATEGORY_ID']+"</td>";
					tr+= "<td>"+value['CATEGORY_TYPE_ID']+"</td>";
					tr+= "<td>"+value['CATEGORY_TYPE_NAME']+"</td>";
					tr+= "<td>"+value['CATEGORY_NAME']+"</td>";
					tr+= "<td>"+value['P_CODE']+"</td>";
					tr+= "<td>"+value['CATEGORY_CODE']+"</td>";
					// tr+= "<td>"+value['CATEGORY_REMARKS']+"</td>";
					tr+= "<td>"+remarks+"</td>";
					// tr+= "<td>-</td>";

					// var data_array  = [
					// 	CATEGORY_TYPE_NAME,
					// 	remarks,
					// 	CATEGORY_NAME
					//
					// ];

					// var data_array = JSON.stringify(value);

					 // CATEGORY_ID 		= "'"+CATEGORY_ID+"'";
					 // CATEGORY_TYPE_ID 	= "'"+CATEGORY_TYPE_ID+"'";
					 //
					 // CATEGORY_NAME 		= '"'+CATEGORY_NAME+'"';
					 // P_CODE 			= "'"+P_CODE+"'";
					 // CATEGORY_CODE 		= "'"+CATEGORY_CODE+"'";


					tr+= "<td><a href='javascript:void(0)' onclick=DeleteCategoryMap("+value['CATEGORY_ID']+");>Delete</a>";
					tr+=" | <a href='javascript:void(0)' onclick=EditCategoryMap('"+encodeURIComponent(CATEGORY_ID)+"','"+encodeURIComponent(CATEGORY_TYPE_ID)+"','"+encodeURIComponent(CATEGORY_NAME)+"','"+encodeURIComponent(CATEGORY_TYPE_NAME)+"','"+encodeURIComponent(CATEGORY_CODE)+"','"+encodeURIComponent(P_CODE)+"','"+encodeURIComponent(remarks)+"');>Edit</a>";

					tr+="</td>";
					tr+="</tr>";
					$("#table_data").append(tr);
				});
			}
		});
	}

	$(document).ready(function () {
		$("#category_type_id").change(function () {
			loadMappedCategory ();
		});
		loadMappedCategory ();
	});
</script>
