<!-- dual list Start -->
<div class="dual-list-box-area mg-b-15" id = "min-height">
    	<div class="sparkline8-hd">

	<div class="sparkline10-list">

		<div class="hpanel shadow-inner hbggreen bg-1 responsive-mg-b-30">
			<div class="panel-body">
				<div class="text-center content-bg-pro">
					<h3>Your Provisional Selections</h3>
				</div>
			</div>
		</div>
	<br/>

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
		<div class="row">
			<div class="col-md-2">
				<label>Program Type</label>
				<select name="program_type" id="program_type" onchange="loadCampus ();" class="form-control">
					<?php
					foreach ($program_types as $program_type)
					{
					    $selected = "";
					    if($PROGRAM_TYPE_ID == $program_type['PROGRAM_TYPE_ID']){
					    $selected = "selected";    
					    }
						?>
						<option value=<?=$program_type['PROGRAM_TYPE_ID']?> <?=$selected?>><?=$program_type['PROGRAM_TITLE']?></option>";
						<?php
					}
					unset($program_types);
					unset($program_type);
					?>
				</select>
			</div>
			<div class="col-md-2">
				<label>Session</label>
				<select name="session" id="session" onchange="loadCampus();" class="form-control">
					<?php
					foreach ($academic_sessions as $academic_session)
					{
					    if($academic_session['YEAR'] <date('Y')) continue;
						?>
						<option value=<?=$academic_session['SESSION_ID']?>><?=$academic_session['YEAR'].' '.$academic_session['BATCH_REMARKS']?></option>";
						<?php
					}
					unset($academic_session);
					unset($academic_sessions);
					?>
				</select>
			</div>
			<div class="col-md-5">
			   	</div>
			<span id="msg"></span>
		</div>
		<div id="display"></div>
	                                
							<!--		<div class="main-sparkline8-hd">-->
							<!--	<h1 class="text-center">How to Download Admission Fee and Retaining Challan For Bachelor Degree Program Admission</h1>-->
							<!--</div>-->
						</div>
						
		    <!--<iframe width="560" height="315" style="text-align:center" src="https://www.youtube.com/embed/Idmw8EOjLf8" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->
		  
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){
loadSelection();
})
$("#program_type,#session").change(function (){
	loadSelection();
});
	function loadSelection (){

		let program_type = $("#program_type").val();
		let session = $("#session").val();
		if ((program_type === "" || program_type === 0 || program_type == null || isNaN(program_type) || session === "") || session === 0 || session == null || isNaN(session))
		{
			$("#msg").text('Please select required fields...')
			return
		}
		$.ajax({
			url:'<?=base_url()?>CandidateSelection/get_candidate_selection_record',
			method: 'POST',
			data: {program_type:program_type,session:session},
			success: function(response){
				$('#display').html(response);
			},
		});
	}
</script>
