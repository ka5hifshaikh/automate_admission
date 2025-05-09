<!-- dual list Start -->
<div id="min-height" class="container-fluid" style="padding: 30px; min-height: 239px;">
<div class="dual-list-box-area mg-b-15">
	<div class="sparkline10-list">
		<div class="container-fluid">
			<div class="sparkline10-hd">
				<div class="main-sparkline10-hd text-center bg-warning">
					<h1>Add Edit Block</h1>
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
			<?=form_open(base_url()."AdmitCard/save_block")?>
			<input type="hidden" name="block_id" id="block_id" />
			<!--		<form id="form" action="save_shift_program_mapping" method="post" class="wizard-big">-->
			<div class="row">
				<div class="col-lg-2 col-md-2">
					<label>Session</label>
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

				<div class="col-lg-4 col-md-4">
					<label>Venue</label>
					<select name="venue_id" id="venue_id" onchange="get_block();" class="form-control">
						<option value=""></option>
					</select>
				</div>

				<div class="col-lg-1 col-md-1">
					<label>Block No</label>
					<input type="text" name="block_no" id="block_no" class="form-control">
				</div>
				<div class="col-lg-5 col-md-5">
					<label>Block Name</label>
					<input type="text" name="block_name" id="block_name" class="form-control">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-lg-3 col-md-3">
					<div class="form-group">
						<label>Seating Capacity</label>
						<input type="text" id="seating_capacity" name="seating_capacity" class="form-control" />
					</div>
				</div>
				<div class="col-lg-2 col-md-2">
					<label>Reserved For</label>
					<select name="for" id="for" class="form-control">
						<option value="M">MALE</option>
						<option value="F">FEMALE</option>
						<option value="G">GENERAL</option>
					</select>
				</div>
				<div class="col-lg-4 col-md-4">
					<div class="form-group">
						<label>Location</label>
						<input type="text" id="block_location" name="block_location" class="form-control" />
					</div>
				</div>
				<div class="col-lg-3 col-md-3">
				<div class="form-group">
						<label>Building Name</label>
						<input type="text" id="buliding_name" name="buliding_name" class="form-control" />
					</div>
				</div>
			
				
			
			</div>
				<br>
				<div class="row">
				 
					
				</div>
				<div class="col-lg-3 col-md-3">
					<div class="form-group">
						<label>Start Seat No</label>
						<input type="text" id="start_seat_no" name="start_seat_no" class="form-control" />
					</div>
				</div>
				<div class="col-lg-3 col-md-3">
					<div class="form-group">
						<label>End Seat No</label>
						<input type="text" id="end_seat_no" name="end_seat_no" class="form-control" />
					</div>
					
				</div>
					<div class="col-lg-3 col-md-3">
					  	<div class="form-group">
						<label>Remarks</label>
						<input type="text" id="remarks" name="remarks" class="form-control" />
					</div> 
					<br/>
				
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
						<th>Block ID</th>
						<th>Venue ID</th>
						<th>Session ID</th>
						<th>Venue No</th>
						<th>Block No</th>
						<th>Venue Name</th>
						<th>Block Name</th>
						<th>Capacity</th>
						<th>For</th>
						<th>Location</th>
						 <th>Building Name</th>
						<th>Start Seat No</th>
						<th>End Seat No</th>
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

	function DeleteBlock (block_id){

		if (block_id === "" || block_id === 0 || block_id == null || isNaN(block_id))
			return;

		if (confirm("Do you want to delete?") === false)
			return;
		// $("#selected_programs").empty();

		$.ajax({
			url:'<?=base_url()?>AdmitCard/DeleteBlock',
			method: 'POST',
			data: {block_id:block_id,csrf_name:csrf_hash},
			dataType: 'json',
			// success: function(response){
			// 	console.log(response);
			// }
			success: function (data, status) {
				// console.log(status);
				alert_msg("<div class='text-danger'>" + data+ "</div>");
				$('#msg').hide();
				get_block();
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

	function EditBlock (VENUE_ID,BLOCK_NO,BLOCK_NAME,BLOCK_LOCATION,BLOCK_REMARKS,SESSION_ID,SEATING_CAPACITY,RESERVED_FOR,BLOCK_ID,BUILDING_NAME,START_NO,END_NO){
		// alert("working");
		$("#session_id").val(decodeURIComponent(SESSION_ID));
		$("#block_no").val(decodeURIComponent(BLOCK_NO));
		$("#block_id").val(decodeURIComponent(BLOCK_ID));
		$("#block_name").val(decodeURIComponent(BLOCK_NAME));
		$("#remarks").val(decodeURIComponent(BLOCK_REMARKS));
		$("#block_location").val(decodeURIComponent(BLOCK_LOCATION));
		$("#for").val(decodeURIComponent(RESERVED_FOR));
		$("#seating_capacity").val(decodeURIComponent(SEATING_CAPACITY));
			$("#building_name").val(decodeURIComponent(BUILDING_NAME));
		$("#start_seat_no").val(decodeURIComponent(START_NO));
		$("#end_seat_no").val(decodeURIComponent(END_NO));
		$('html,body').animate({
				scrollTop: $(".container-fluid").offset().top},
			'slow');
	}

	function get_block (){

		let venue_id = $("#venue_id").val();
		if (venue_id === "" || venue_id === 0 || venue_id == null || isNaN(venue_id))
		{
			return;
		}
		$("#table_data").empty();
		$.ajax({
			url:'<?=base_url()?>AdmitCard/getBlock',
			method: 'POST',
			data: {venue_id:venue_id,csrf_name:csrf_hash},
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
					var BLOCK_ID 	= value['BLOCK_ID'];
					var BLOCK_LOCATION 	= value['BLOCK_LOCATION'];
					var BLOCK_NAME 	= value['BLOCK_NAME'];
					var BLOCK_NO 	= value['BLOCK_NO'];
					var BLOCK_REMARKS 	= value['BLOCK_REMARKS'];
					var RESERVED_FOR 	= value['RESERVED_FOR'];
					var SEATING_CAPACITY= value['SEATING_CAPACITY'];
					var VENUE_LOCATION 	= value['VENUE_LOCATION'];
					var VENUE_REMARKS 	= value['VENUE_REMARKS'];
						var BUILDING_NAME 	= value['BUILDING_NAME'];
					var START_SEAT_NO 	= value['START_SEAT_NO'];
					var END_SEAT_NO 	= value['END_SEAT_NO'];

					if (value['REMARKS'] == null || value['REMARKS'] === "")
						var REMARKS = '';
					else REMARKS = value['REMARKS'];

					if (value['VENUE_NO'] == null || value['VENUE_NO'] === "")
						var VENUE_NO = '';
					else VENUE_NO = value['VENUE_NO'];

					let tr="<tr>";
					tr+= "<td>"+i+"</td>";
					tr+= "<td>"+BLOCK_ID+"</td>";
					tr+= "<td>"+VENUE_ID+"</td>";
					tr+= "<td>"+SESSION_ID+"</td>";
					tr+= "<td>"+VENUE_NO+"</td>";
					tr+= "<td>"+BLOCK_NO+"</td>";
					tr+= "<td>"+VENUE_NAME+"</td>";
					tr+= "<td>"+BLOCK_NAME+"</td>";
					tr+= "<td>"+SEATING_CAPACITY+"</td>";
					tr+= "<td>"+RESERVED_FOR+"</td>";
					tr+= "<td>"+BLOCK_LOCATION+"</td>";
				
						tr+= "<td>"+BUILDING_NAME+"</td>";
					tr+= "<td>"+START_SEAT_NO+"</td>";
					tr+= "<td>"+END_SEAT_NO+"</td>";
						tr+= "<td>"+BLOCK_REMARKS+"</td>";

					tr+= "<td><a href='javascript:void(0)' onclick=DeleteBlock("+BLOCK_ID+");>Delete</a>";
					tr+=" | <a href='javascript:void(0)' onclick=EditBlock('"+encodeURIComponent(VENUE_ID)+"','"+encodeURIComponent(BLOCK_NO)+"','"+encodeURIComponent(BLOCK_NAME)+"','"+encodeURIComponent(BLOCK_LOCATION)+"','"+encodeURIComponent(BLOCK_REMARKS)+"','"+encodeURIComponent(SESSION_ID)+"','"+encodeURIComponent(SEATING_CAPACITY)+"','"+encodeURIComponent(RESERVED_FOR)+"','"+encodeURIComponent(BLOCK_ID)+"','"+encodeURIComponent(BUILDING_NAME)+"','"+encodeURIComponent(START_SEAT_NO)+"','"+encodeURIComponent(END_SEAT_NO)+"');>Edit</a>";

					tr+="</td>";
					tr+="</tr>";
					$("#table_data").append(tr);
				});
			}
		});
	}

	function show_session_venue (){

		let session_id = $("#session_id").val();
		// alert(shift_id);
		if (session_id === "" || session_id === 0 || session_id == null || isNaN(session_id))
		{
			return;
		}
		$("#venue_id").empty();
		$.ajax({
			url:'<?=base_url()?>AdmitCard/getVenue',
			method: 'POST',
			data: {session_id:session_id,csrf_name:csrf_hash},
			dataType: 'json',
			success: function(response){
				let i=0;

				let option="<option value=''></option>";
				$("#venue_id").append(option);

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

					let option="<option value='"+VENUE_ID+"'>"+VENUE_NAME+"</option>";

					$("#venue_id").append(option);
				});
			}
		});
	}
</script>
