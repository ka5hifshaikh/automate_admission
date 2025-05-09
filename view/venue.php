<!-- dual list Start -->
<div id="min-height" class="container-fluid" style="padding: 30px; min-height: 239px;">
<div class="dual-list-box-area mg-b-15">
	<div class="sparkline10-list">
		<div class="container-fluid">
			<div class="sparkline10-hd">
				<div class="main-sparkline10-hd text-center bg-warning">
					<h1>Add Edit Venue</h1>
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
			<?=form_open(base_url()."AdmitCard/save_venue")?>
			<input type="hidden" name="venue_id" id="venue_id" />
			<!--		<form id="form" action="save_shift_program_mapping" method="post" class="wizard-big">-->
			<div class="row">
				<div class="col-lg-2 col-md-2">
					<label>Session</label>
					<?php
					//					print_r($degree_programs);
					?>
					<select name="session_id" id="session_id" onchange="show_session_venue();" class="form-control">
						<option value=""></option>
						<?php
						foreach ($sessions as $session_value)
						{
							?>
							<option value=<?=$session_value['SESSION_ID']?>><?=$session_value['YEAR'].'  ('.$session_value['BATCH_REMARKS'].')'?></option>";
							<?php
						}
						?>
					</select>
				</div>

				<div class="col-lg-2 col-md-2">
					<label>Venue No</label>
				<input type="text" name="venue_no" id="venue_no" class="form-control">
				</div>
				<div class="col-lg-8 col-md-8">
					<label>Venue Name</label>
					<input type="text" name="venue_name" id="venue_name" class="form-control">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-lg-6 col-md-6">
					<div class="form-group">
						<label>Location</label>
						<input type="text" id="venue_location" name="venue_location" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-4">
					<div class="form-group">
						<label>Remarks</label>
						<input type="text" id="remarks" name="remarks" class="form-control" />
					</div>
				</div><br/>
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
						<th>Venue ID</th>
						<th>Session ID</th>
						<th>Venue No</th>
						<th>Venue Name</th>
						<th>Location</th>
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
</div>

<?php $CI =& get_instance(); ?>
<script>
	var csrf_name = '<?php echo $CI->security->get_csrf_token_name(); ?>';
	var csrf_hash = '<?php echo $CI->security->get_csrf_hash(); ?>';
</script>

<script type="text/javascript">

	function DeleteVenue (venue_id){

		if (venue_id === "" || venue_id === 0 || venue_id == null || isNaN(venue_id))
			return;

		if (confirm("Do you want to delete?") === false)
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>AdmitCard/DeleteVenue',
			method: 'POST',
			data: {venue_id:venue_id,csrf_name:csrf_hash},
			dataType: 'json',
			// success: function(response){
			// 	console.log(response);
			// }
			success: function (data, status) {
				// console.log(status);
				alert_msg("<div class='text-danger'>" + data+ "</div>");
				$('#msg').hide();
				show_session_venue();
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

	function EditVenue (VENUE_ID,VENUE_NO,VENUE_NAME,LOCATION,REMARKS,SESSION_ID){
		// alert("working");
		$("#session_id").val(decodeURIComponent(SESSION_ID));
		$("#venue_no").val(decodeURIComponent(VENUE_NO));
		$("#venue_id").val(decodeURIComponent(VENUE_ID));
		$("#venue_name").val(decodeURIComponent(VENUE_NAME));
		$("#remarks").val(decodeURIComponent(REMARKS));
		$("#venue_location").val(decodeURIComponent(LOCATION));
	
		$('html,body').animate({
				scrollTop: $(".container-fluid").offset().top},
			'slow');
	}

	function show_session_venue (){

		let session_id = $("#session_id").val();
		// alert(shift_id);
		if (session_id === "" || session_id === 0 || session_id == null || isNaN(session_id))
		{
			return;
		}
		$("#table_data").empty();
		$.ajax({
			url:'<?=base_url()?>AdmitCard/getVenue',
			method: 'POST',
			data: {session_id:session_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				console.log(response);
				let i=0;
				$.each(response, function (index,value) {
					i++;
					var VENUE_ID 	= value['VENUE_ID'];
					var VENUE_NAME 	= value['VENUE_NAME'];
					var VENUE_NO 	= value['VENUE_NO'];
					var SESSION_ID 	= value['SESSION_ID'];
					var LOCATION 	= value['LOCATION'];
					var REMARKS 	= value['REMARKS'];
				

					if (value['REMARKS'] == null || value['REMARKS'] === "")
						var REMARKS = '';
					else REMARKS = value['REMARKS'];

					if (value['VENUE_NO'] == null || value['VENUE_NO'] === "")
						var VENUE_NO = '';
					else VENUE_NO = value['VENUE_NO'];

					let tr="<tr>";
					tr+= "<td>"+i+"</td>";
					tr+= "<td>"+VENUE_ID+"</td>";
					tr+= "<td>"+SESSION_ID+"</td>";
					tr+= "<td>"+VENUE_NO+"</td>";
					tr+= "<td>"+VENUE_NAME+"</td>";
					tr+= "<td>"+LOCATION+"</td>";
					tr+= "<td>"+REMARKS+"</td>";
				

					tr+= "<td><a href='javascript:void(0)' onclick=DeleteVenue("+VENUE_ID+");>Delete</a>";
					tr+=" | <a href='javascript:void(0)' onclick=EditVenue('"+encodeURIComponent(VENUE_ID)+"','"+encodeURIComponent(VENUE_NO)+"','"+encodeURIComponent(VENUE_NAME)+"','"+encodeURIComponent(LOCATION)+"','"+encodeURIComponent(REMARKS)+"','"+encodeURIComponent(SESSION_ID)+"'");>Edit</a>";

					tr+="</td>";
					tr+="</tr>";
					$("#table_data").append(tr);
				});
			}
		});
	}
</script>
