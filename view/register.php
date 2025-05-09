



<div class="container">


    <div class="card" style="margin-top: 20px;margin-bottom: 50px;">
		<div class="card-header card-header-primary bg-theme-color-2 text-center">
			<h2 class="card-title text-white  ">Registration Form</h2>
		</div>
            
            
        
        <div class="card-body">
            <div class="login">
              
             <form  id="registration" action="" autocomplete="off" class="row" method="post" >
   <div id="cnic_view" style="width:100%">
                        <div class="col-md-12">
                                <span class="text-danger font-weight-bold" style="margin:5px;font-size: 13pt;">
                                    <ul>
                                        <li>Use your own CNIC or B-Form Number to Register.</li>
                                        <li>CNIC/B-Form number can not be changed after Registration. Please enter your own CNIC or B-Form Number carefully.</li>
                                        <li>If your CNIC/B-Form No. is already registered, there is no need to register again. Simply log in to your account to access the Online Admission Form.</li>
                                    </ul>
                                </span>
                        </div>


                        <div class="col-md-12">
                            <label for="" style="font-size:17px">CNIC No. / B-Form No.<span class="text-danger">* (without dashes)</span><span id="cnic_verification_msg"class="text-warni"></span></label>
                            <input onkeyup="check_cnic_already_exist()" onfocusout="checkAlertValidation('CNIC')" type="text" class="form-control mb-3 allow-number" data-toggle="tooltip" title="Please type your CNIC/B-FORM No without '-' dashes" id="cnic" name="cnic" placeholder="CNIC Number or B-Form Number (1234567891234)">
                        </div>
                        <div class="col-md-12">
                            <label for="" style="font-size:17px">Re-Type CNIC No. / B-Form No.<span class="text-danger">* (without dashes)</span></label>
                            <input onfocusout="checkAlertValidation('RE-CNIC')" type="text" class="form-control mb-3 allow-number" id="retype_cnic" name="retype_cnic" data-toggle="tooltip" title="Please Re-type your CNIC/B-FORM No without '-' dashes" placeholder="Re-Type CNIC Number or B-Form Number (1234567891234)">
                        </div>
                    </div>
                    <div id="passport_view" style="width:100%">
                        <div class="col-md-12">
                                <span class="text-danger font-weight-bold" style="margin:5px;font-size: 13pt;">
                
                                    <ul>
                                        <li>Use your own Passport Number to Register.</li>
                                        <li>Passport Number can not be changed after Registration.</li>
                                    </ul>
                                </span>
                        </div>


                        <div class="col-md-12">
                            <label for="" style="font-size:17px">Passport No<span class="text-danger">* </span></label>
                            <input onfocusout="checkAlertValidation('PASSPORT')" type="text" class="form-control mb-3" id="passport" name="passport" placeholder="Passport No">
                        </div>
                        <div class="col-md-12">
                            <label for="" style="font-size:17px">Re-Type Passport No<span class="text-danger">* </span></label>
                            <input onfocusout="checkAlertValidation('RE-PASSPORT')" type="text" class="form-control mb-3" id="retype_passport" name="retype_passport" placeholder="Passport No">
                        </div>
                    </div>
                     <div class="col-md-12">
                          <label for="" style="font-size:17px">Email Address <span class="text-danger">*</span></label>
                        <!--<div class="row">-->
                        <!--    <div class="col-md-9">-->
                                 <input type="email" class="form-control mb-3" id="email" name="email" data-toggle="tooltip" title="Your email address <?=UNIVERSITY_NAME?> will correspond/contact you during admission process" placeholder="Email Address">
                 
                            <!--</div>-->
                            <!--<div class="col-md-3">-->
                                <!--<button id="send_email_code_btn" class="btn btn-info"> Send Email Verification Code</button>-->
                            <!--    <h6 class="text-danger" id="email_counter"></h6>-->
                            <!--</div> -->
                        <!--</div>  -->
                    </div>
                    <!--<div class="col-md-12">-->
                    <!--      <label for="" style="font-size:17px">Email Verifcation Code <span class="text-danger">*</span></label>-->
                    <!--    <div class="row">-->
                    <!--        <div class="col-md-9">-->
                    <!--             <input  onfocusout="check_email_verification_code()" onkeyup="check_email_verification_code()" type="text" class="form-control mb-3" id="email_verification_code" name="email_verification_code" data-toggle="tooltip" title="Enter email verification code which you recived on your mail" placeholder="Email Verification Code">-->
                 
                    <!--        </div>-->
                    <!--        <div class="col-md-3">-->
                              
                    <!--          <h6 id="email_verification_msg" ></h6>-->
                                
                    <!--        </div>-->
                    <!--    </div>  -->
                    <!--</div>-->
                    <div class="col-md-12">
                        <label for="" style="font-size:17px">Mobile Number<span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-2">
                                <select  class="js-example-basic-single form-control mb-3" name="PHONE_CODE" id="PHONE_CODE">
                                    <?php

                                    foreach ($countries as $country) {
                                        $select = "";
                                        if($country['COUNTRY_NAME']=='PAKISTAN'){
                                            $select = "selected";
                                        }
                                        echo "<option value='{$country['PHONE_CODE']}' $select >{$country['COUNTRY_NAME']} &nbsp;&nbsp; {$country['PHONE_CODE']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-10">
                                <input autocomplete="off" type="text" class="form-control mb-3 allow-mobile-number " data-toggle="tooltip" title="You will get SMS notifications on the provided number." id="mobile" name="mobile" placeholder="3332691464">
                            </div>
                            <!--<div class="col-md-3">-->
                            <!--    <button id="send_mobile_code_btn" class="btn btn-warning"> Send Mobile Verfication Code</button>-->
                            <!--    <h6  class="text-danger" id="mobile_counter"></h6>-->
                            <!--</div>-->
                        </div>
                       
                    </div>
                    <!-- <div class="col-md-12">-->
                    <!--      <label for="" style="font-size:17px">Mobile No Verifcation Code <span class="text-danger">*</span></label>-->
                    <!--    <div class="row">-->
                    <!--        <div class="col-md-9">-->
                    <!--             <input type="text" onfocusout="check_mobile_verification_code()" onkeyup="check_mobile_verification_code()" class="form-control mb-3" id="mobile_verification_code" name="mobile_verification_code" data-toggle="tooltip" title="Enter Mobile verification code which you recived on your Mobile No" placeholder="Mobile No Verification Code">-->
                 
                    <!--        </div>-->
                    <!--        <div class="col-md-3">-->
                    <!--             <h6 id="mobile_verification_msg"></h6>-->
                    <!--        </div>-->
                    <!--    </div>  -->
                    <!--</div>-->
                    
                    <div class="col-md-12">
                        <span class="text-danger font-weight:bold" style="font-size: 120%;"><b>Provide following information as per <u>Matriculation Record / Certificate</u></b>.</span><br>
                        <label for="" style="font-size:17px">Full Name <span class="text-danger">* </span></label>
                        <input type="text" class="form-control mb-3 allow-string" id="full_name" name="full_name" data-toggle="tooltip" title="Your Name spelling as per Matriculation Record" placeholder="Full name (Spelling as per Matric Marks Certificate)">
                    </div>
                <div class="col-md-12">

                    <label for="" style="font-size:17px">Father's Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control mb-3 allow-string" id="f_name" name="f_name" data-toggle="tooltip" title="Your Father's Name spelling as per Matriculation Record" placeholder="Father's Name (Spelling as per Matric Marks Certificate)">
                </div>
                    <div class="col-md-12">

                        <label for="" style="font-size:17px">Surname<span class="text-danger"></span></label>
                        <input type="text" class="form-control mb-3 allow-string" id="surname" name="surname" data-toggle="tooltip" title="Your Surname spelling as per Matriculation Record" placeholder="Surname (Spelling as per Matric Marks Certificate)">
                    </div>
                    <div class="col-md-12">

                        <label for="" style="font-size:17px">Gender<span class="text-danger">*</span></label>
                          <select name="GENDER" id="GENDER" class="form-control">
                                <option value='0'>--choose--</option> ;
                                <?php
                                $selected = "";
                                $blood_groups=array('M'=>"MALE","F"=>"FEMALE");
                                foreach($blood_groups as $k=>$boolg){
                                   
                                      
                                        echo "<option value='$k' >$boolg</option>" ;
                                }
                                ?>

                            </select>
                    </div>
                   
                    <div class="col-md-12">
                        <label for="" style="font-size:17px">Country <span class="text-danger">*</span></label>
                        <select name="COUNTRY_ID" id="COUNTRY_ID" data-toggle="tooltip" title="Please select correct option later on you can't change this!" class="js-example-basic-single form-control mb-3">

                            <?php

                            foreach ($countries as $country) {
                                $select = "";
                                if($country['COUNTRY_NAME']=='PAKISTAN'){
                                    $select = "selected";
                                }
                                echo "<option value='{$country['COUNTRY_ID']}' $select >{$country['COUNTRY_NAME']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <div class="col-md-12">
                    <label for="" style="font-size:17px">Domicile Province / State <span class="text-danger">*</span></label>
                    <select  id="PROVINCE_ID" class="js-example-basic-single form-control"  ONCHANGE="getDistrict(this.value)" data-toggle="tooltip" title="Please select correct option later on you can't change this!" name="PROVINCE_ID">
                        <option value="0">--Choose--</option>


                    </select>
                </div>
                <div class="col-md-12">
                    <label for="" style="font-size:17px">Domicile District<span class="text-danger">*</span></label>
                    <select  id="DISTRICT_ID" class="js-example-basic-single form-control" data-toggle="tooltip" title="Please select correct option later on you can't change this!" name="DISTRICT_ID">
                        <option value="0">--Choose--</option>



                    </select>
                </div>

                    <div class="col-md-12">
                        <!--                        <label for="" style="font-size:17px">CNIC<span class="text-danger"></span></label>-->
                        <input style="width:1.3em;height:1.3em;" hidden type="radio" class=" mb-3" id="is_cnic" name="check_cnic" value="cnic" checked>
                        &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;
                        <!--                        <label for="" style="font-size:17px">Passport <span class="text-danger">(For Foregin)</span></label>-->
                        <input style="width:1.3em;height:1.3em;" hidden  type="radio" class=" mb-3" id="is_passport" name="check_cnic" value="passport">
                    </div>
                 
                    <div class="col-md-12">
                        <label for="" style="font-size:17px">Password <span class="text-danger">* </span></label>
                        <input onfocusout="checkAlertValidation('PASSWORD')" type="password" class="form-control mb-3" id="password" name="password" data-toggle="tooltip" title="Please remember your password. You will get login with this password" placeholder="Password">
                    </div>
                    <div class="col-md-12">
                        <label for="" style="font-size:17px">Re-Type Password <span class="text-danger">* </span></label>
                        <input onfocusout="checkAlertValidation('RE-PASSWORD')" type="password" class="form-control mb-3" id="retype_password" data-toggle="tooltip" title="Retype password which you have typed above." name="retype_password" placeholder="Re-Type Password">
                    </div>
				 	<div class="col-md-12">

						<div class="row">
							<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
								<br>
								<br>
								<div class="form-group res-mg-t-15">
									<img src="<?php echo base_url()."dash_assets/img/cp1.jpg"; ?>"   width="150px" height="150px">

									<img src="<?php echo base_url()."dash_assets/img/cp2.jpg"; ?>"   width="150px" height="150px">


									<img src="<?php echo base_url()."dash_assets/img/cp3.jpg"; ?>"   width="150px" height="150px">

									<img src="<?php echo base_url()."dash_assets/img/correct-photo.jpg"; ?>"   width="150px" height="150px">
								</div>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
							<br>
								<div class="form-group res-mg-t-15">
									<label for="exampleInput1" class="bmd-label-floating">Profile Image
										<span class="text-danger">*</span>
									</label><br>
									<?php

									$image_path_default =base_url()."dash_assets/img/avatar/default-avatar.png";
									$image_path = "";

									?>
									<img src="<?php echo $image_path_default; ?>" alt="Profile" class="" id="profile-image-view"  width="150px" height="150px" name="profile-image-view" >
									<input  type="file" name="profile_image" id="profile_image"
											onchange="changeImage(this,'profile_image','profile-image-view',100)"
											accept=".jpg,.png,.jpeg" style='opacity: 1;position:relative;z-index: 2;'>
									<input type="text" name="profile_image1" id="profile_image1"
										   value="<?php echo $image_path; ?>" hidden>
									<span class="text-danger">Image must be passport size with white background and image size should be less than 100KB</span>

								</div>
							</div>

						</div>

					</div>
				 	<div class="col-md-12">
                        <button type="submit" id="register" class="btn btn-primary btn-lg">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



</div>
<!-- footer -->

<!-----Scripting for Registration form------>
<script>
function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}
function check_cnic_already_exist(){
     let cnic = $("#cnic").val().trim();
      $('#cnic_verification_msg').html("");
     if(checkCnicValidation()===true){
          var data = new FormData();
     data.append(csrfName, csrfHash);
     data.append('cnic', cnic);
      
     jQuery.ajax({
            url: "<?=base_url();?>Register/check_cnic_already_exist",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (data, status) {

          $('#cnic_verification_msg').html(data.MESSAGE);
               
              

              

            },
            beforeSend:function (data, status) {
                

            },
            error:function (data, status) {

                var value = data.responseJSON;

               $('#cnic_verification_msg').html(value.MESSAGE);
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                // csrfHash = value.csrfHash;
                //$('#alert_msg_for_ajax_call').html(value.MESSAGE);


              



            },
        });
     }
}
function check_email_verification_code(){
     let email = $("#email").val().trim();
     let email_verification_code = $("#email_verification_code").val().trim();
    
        if(!validateEmail(email)){
                let big_error= "<div class='text-danger'>Please provide valid email</div>";
                 alert_msg(big_error);
                return;
        }
        if(!email_verification_code){
            let big_error= "<div class='text-danger'>Please provide  email verification code</div>";
                 alert_msg(big_error);
        }
     var data = new FormData();
     data.append(csrfName, csrfHash);
     data.append('email', email);
      data.append('email_verification_code', email_verification_code);
     jQuery.ajax({
            url: "<?=base_url();?>Register/verify_email_code",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (data, status) {

          $('#email_verification_msg').html(data.MESSAGE);
               
              

              

            },
            beforeSend:function (data, status) {
                

            },
            error:function (data, status) {

                var value = data.responseJSON;

               $('#email_verification_msg').html(value.MESSAGE);
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                // csrfHash = value.csrfHash;
                //$('#alert_msg_for_ajax_call').html(value.MESSAGE);


              



            },
        });
}

$("#send_email_code_btn").click(function(event) {
    event.preventDefault();
      let email = $("#email").val().trim();
    if(!validateEmail(email)){
            let big_error= "<div class='text-danger'>Please provide valid email</div>";
             alert_msg(big_error);
            return;
        }
        
  email_counter();
    var data = new FormData();
     data.append(csrfName, csrfHash);
     data.append('email', email);
     jQuery.ajax({
            url: "<?=base_url();?>Register/send_email_code",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (data, status) {

                $('.preloader').fadeOut(700);
               
                alert_msg(data.MESSAGE,"Success");

              

            },
            beforeSend:function (data, status) {
                $('.preloader').fadeIn(700);
                $("#email_verification_code").val("");
            },
            error:function (data, status) {

                var value = data.responseJSON;

                alert_msg(value.MESSAGE,"Error");
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                // csrfHash = value.csrfHash;
                //$('#alert_msg_for_ajax_call').html(value.MESSAGE);


              reset_email_counter();
                $('.preloader').fadeOut(700);



            },
        });
  
});

function check_mobile_verification_code(){
    
     let mobile = $("#mobile").val().trim();
     let mobile_verification_code = $("#mobile_verification_code").val().trim();
    let big_error ="";
        if(!mobile || mobile.length!=10){
            big_error+= "<div class='text-danger'>Please provide your active mobile number  must be 10 digit don't start with zero.</div>";;
        }
        if(!(/^\d+$/.test(mobile))){
            big_error += "<div class='text-danger'>All Character must be digit in Mobile No</div>";
        }
    if(big_error!=""){
            
             alert_msg(big_error);
            return;
        }
        if(!mobile_verification_code){
            let big_error= "<div class='text-danger'>Please provide  mobile verification code</div>";
                 alert_msg(big_error);
        }
     var data = new FormData();
     data.append(csrfName, csrfHash);
     data.append('mobile', mobile);
      data.append('mobile_verification_code', mobile_verification_code);
     jQuery.ajax({
            url: "<?=base_url();?>Register/verify_mobile_code",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (data, status) {

          $('#mobile_verification_msg').html(data.MESSAGE);
               
              

              

            },
            beforeSend:function (data, status) {
                

            },
            error:function (data, status) {

                var value = data.responseJSON;

               $('#mobile_verification_msg').html(value.MESSAGE);
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                // csrfHash = value.csrfHash;
                //$('#alert_msg_for_ajax_call').html(value.MESSAGE);


              



            },
        });
}
$("#send_mobile_code_btn").click(function(event) {
    event.preventDefault();
      let mobile = $("#mobile").val().trim();
      let cnic = $("#cnic").val().trim();
      let error=checkCnicValidation();
      let big_error= "";
      if(error==!true){
            big_error+=error;
        }
        if(!mobile || mobile.length!=10){
            big_error+= "<div class='text-danger'>Please provide your active mobile number  must be 10 digit don't start with zero.</div>";
        }
        if(!(/^\d+$/.test(mobile))){
            big_error += "<div class='text-danger'>All Character must be digit in Mobile No</div>";
        }
    if(big_error!=""){
            
             alert_msg(big_error);
            return;
        }
  mobile_counter();
    var data = new FormData();
     data.append(csrfName, csrfHash);
     data.append('mobile', mobile);
     data.append('cnic', cnic);
     jQuery.ajax({
            url: "<?=base_url();?>Register/send_mobile_code",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (data, status) {

                $('.preloader').fadeOut(700);
               
                alert_msg(data.MESSAGE,"Success");

              

            },
            beforeSend:function (data, status) {
                $('.preloader').fadeIn(700);

            },
            error:function (data, status) {

                var value = data.responseJSON;

                alert_msg(value.MESSAGE,"Error");
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                // csrfHash = value.csrfHash;
                //$('#alert_msg_for_ajax_call').html(value.MESSAGE);


              reset_mobile_counter();
                $('.preloader').fadeOut(700);



            },
        });
  
});

 var email_counter_interval;
 var emailCountDownDate;
function email_counter(){
     emailCountDownDate= new Date().getTime()+1000*60*2;
    
     email_counter_interval = setInterval(function() {
    $('#send_email_code_btn').hide();
      // Get today's date and time
      
      let now = new Date().getTime();
        
      // Find the distance between now and the count down date
      let distance = emailCountDownDate - now;
      // Time calculations for days, hours, minutes and seconds
      let days = Math.floor(distance / (1000 * 60 * 60 * 24));
      let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
      // Output the result in an element with id="demo"
      document.getElementById("email_counter").innerHTML = minutes + ":" + seconds + " ";
        
      // If the count down is over, write some text 
      if (distance < 0) {
      reset_email_counter();
      }
    }, 1000);
}

 var mobileCountDownDate;
 var mobile_counter_interval ;
function mobile_counter(){
    mobileCountDownDate = new Date().getTime()+1000*60*3;
    
      mobile_counter_interval = setInterval(function() {
    $('#send_mobile_code_btn').hide();
      // Get today's date and time
      
      let now = new Date().getTime();
        
      // Find the distance between now and the count down date
      let distance = mobileCountDownDate - now;
      // Time calculations for days, hours, minutes and seconds
      let days = Math.floor(distance / (1000 * 60 * 60 * 24));
      let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
      // Output the result in an element with id="demo"
      document.getElementById("mobile_counter").innerHTML = minutes + ": " + seconds + " ";
        
      // If the count down is over, write some text 
      if (distance < 0) {
      reset_mobile_counter();
      }
    }, 1000);
}
function reset_email_counter(){
      clearInterval(email_counter_interval);
        document.getElementById("email_counter").innerHTML = "";
        $('#send_email_code_btn').show();
    	emailCountDownDate = new Date().getTime()+1000*60*2;
}
function reset_mobile_counter(){
        clearInterval(mobile_counter_interval);
        document.getElementById("mobile_counter").innerHTML = "";
        $('#send_mobile_code_btn').show();
    	mobileCountDownDate = new Date().getTime()+1000*60*3;
}
// Set the date we're counti down to

    
// Update the count down every 1 second


    <?php
    $res = getcsrf($this);
    ?>
    var csrfName="<?=$res['csrfName']?>";
    var csrfHash="<?=$res['csrfHash']?>";

    $("#register").click(function (event) {

        //stop submit the form, we will post it manually.
        event.preventDefault();

        let big_error = "";
        let error="";
        let name = $("#full_name").val();
        let f_name = $("#f_name").val();
        let surname = $("#surname").val();
        let email = $("#email").val();
        let mobile = $("#mobile").val();
        let code = $("#PHONE_CODE").val();
        let DISTRICT_ID = $("#DISTRICT_ID").val();
        let PROVINCE_ID = $("#PROVINCE_ID").val();
        let profile_image = $('#profile_image').val();
        let GENDER = $('#GENDER').val();
        if(!name){
            big_error+= "<div class='text-danger'>Please provide your full name as per matriculation certificate.</div>";;
        }
        if(!profile_image){
            big_error+= "<div class='text-danger'>Please must upload valid profile image once you upload you cannot change it.</div>";;
        }
        if(!f_name){
            big_error+= "<div class='text-danger'>Please provide your Father.</div>";;
        }
        if(!surname){
           // big_error+= "<div class='text-danger'>Please provide your Surname / Cast / Family Name.</div>";;
        }
        if(!GENDER){
            big_error+= "<div class='text-danger'>Please provide your Gender.</div>";;
        }
        if(!validateEmail(email)){
            big_error+= "<div class='text-danger'>Please provide email</div>";
        }
        if(mobile.charAt(0) === '0'){
          mobile = mobile.substring(1);
        }
        if(!mobile || mobile.length!=10){
            big_error+= "<div class='text-danger'>Please provide your active mobile number  must be 10 digits don't start with zero.</div>";;
        }
        if(!(/^\d+$/.test(mobile))){
            big_error += "<div class='text-danger'>All Character must be digit in Mobile No</div>";
        }
        if(!(PROVINCE_ID>0)){
            big_error+= "<div class='text-danger'>Domilice province must be select</div>";
        }
        if(!(DISTRICT_ID>0)){
            big_error+= "<div class='text-danger'>Domilice district must be select</div>";
        }
        if(!profile_image){
            big_error+= "<div class='text-danger'>Please must upload valid profile image once you upload you cannot change it.</div>";;
        }
        if($("#is_cnic").is(':checked')) {
            error = checkCnicValidation();
            if (error !== true) {
                big_error += error;
            }

            error = checkCnicReValidation();
            if (error !== true) {
                big_error += error;
            }
        }
        else {
            error = checkPassportValidation();
            if (error !== true) {
                big_error += error;
            }

            error = checkPassportReValidation();
            if (error !== true) {
                big_error += error;
            }
        }

        error = checkPasswordValidation()
        if(error!==true){
            big_error+=error;
        }
        error = checkPasswordReValidation();
        if(error!==true){
            big_error+=error;
        }


        if(big_error!==""){
            alert_msg(big_error);
            return;
        }
        var form = $('#registration')[0];

        // Create an FormData object
        var data = new FormData(form);

        // If you want to add an extra field for the FormData



        $('.preloader').fadeIn(700);
        // disabled the submit button
        $("#register").prop("disabled", true);
        data.append(csrfName, csrfHash);

        //data.set('mobile',code+mobile);
        data.append("action", "add_new_user");
        jQuery.ajax({
            url: "<?=base_url();?>Register/user_register_handler",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (data, status) {

                $('.preloader').fadeOut(700);
                $('input[name="csrf_form_token"]').val(data.csrfHash);
                csrfHash = data.csrfHash;
                alert_msg(data.MESSAGE,"Success");

                $("#register").prop("disabled", false);
                setNull();

            },
            beforeSend:function (data, status) {
                $('.preloader').fadeIn(700);

            },
            error:function (data, status) {

                var value = data.responseJSON;

                alert_msg(value.MESSAGE,"Error");
                $('input[name="csrf_form_token"]').val(value.csrfHash);
                csrfHash = value.csrfHash;
                //$('#alert_msg_for_ajax_call').html(value.MESSAGE);


                $("#register").prop("disabled", false);
                $('.preloader').fadeOut(700);



            },
        });




    });



    $("#passport_view").hide();

    $("#is_passport").change(function(){
        if($("#is_passport").is(':checked')){
            //console.log("passport");
            $("#passport_view").show();
            $("#cnic_view").hide();
        }else{
            $("#cnic_view").show();
            $("#passport_view").hide();
            //console.log("cnic");
        }
    });

    $("#is_cnic").change(function(){
        if($("#is_cnic").is(':checked')){
            $("#passport_view").hide();
            $("#cnic_view").show();
            //console.log("is_cnic");
        }else{

            $("#passport_view").show();
            $("#cnic_view").hide();
            //    console.log("pass");
        }
    });
    $("#COUNTRY_ID").change(function(){
        var value = $("#COUNTRY_ID option:selected");

        // console.log($("#COUNTRY_ID").text());
        getProvinces($("#COUNTRY_ID").val());
        getDistrict(0);
        if(value.text()==='PAKISTAN'){
            // $("#is_cnic").checked();
            $("#is_cnic").prop("checked", true);
            $("#is_passport").prop("checked", false);
            $("#passport_view").hide();
            $("#cnic_view").show();
            //console.log("is_cnic");
        }else{
            $("#is_cnic").prop("checked", false);
            $("#is_passport").prop("checked", true);
            $("#passport_view").show();
            $("#cnic_view").hide();
            //    console.log("pass");
        }
    });

    function checkAlertValidation(val){
        if(val==="CNIC"){
            let error = checkCnicValidation();
            if(error!==true){
                alert_msg(error);
            }
        }else if(val==="RE-CNIC"){
            let error = checkCnicReValidation();
            if(error!==true){
                alert_msg(error);
            }
        }else if(val==="PASSPORT"){
            let error = checkPassportValidation();
            if(error!==true){
                alert_msg(error);
            }
        }else if(val==="RE-PASSPORT"){
            let error = checkPassportReValidation();
            if(error!==true){
                alert_msg(error);
            }
        }else if(val==="PASSWORD"){
            let error = checkPasswordValidation()
            if(error!==true){
                alert_msg(error);
            }
        }else if(val==="RE-PASSWORD"){
            let error = checkPasswordReValidation();
            if(error!==true){
                alert_msg(error);
            }
        }


    }
    function checkCnicValidation(){
        let cnic = $('#cnic').val();
         //$('#cnic_verification_msg').html("");
        //console.log(cnic);
        let error = "";
        if(!cnic){
            error += "<div class='text-danger'>Must provide your CNIC Number</div>";
        }
        if(cnic.length!==13){
            error += "<div class='text-danger'>Please enter your 13 digits valid CNIC Number</div>";
        }
        if(!(/^\d+$/.test(cnic))){
            error += "<div class='text-danger'>All Characters must be in digits</div>";
        }
        let invalid_list = [
            "0000000000000",
            "1111111111111",
            "2222222222222",
            "3333333333333",
            "4444444444444",
            "5555555555555",
            "6666666666666",
            "7777777777777",
            "8888888888888",
            "9999999999999",
            "1234567891234"];
        let bool = false;
        for(let i = 0 ; i<invalid_list.length;i++){
            if(invalid_list[i]==cnic){
                bool = true;
                break;
            }
        }
        if(bool){
            error += "<div class='text-danger'>Invalid Cnic No Please Provide Valid CNIC NO</div>";
        }
        if(error!==""){
            // alert_msg(error);
            return error;
        }else{
            return true;
        }
    }
    function checkCnicReValidation(){

        let retype_cnic = $('#retype_cnic').val();
        //console.log(cnic);
        let cnic_val =  checkCnicValidation();
        if(cnic_val===true){
            let error = "";
            if(!retype_cnic){
                error += "<div class='text-danger'>Cnic must fill</div>";
            }
            if(retype_cnic.length!==13){
                error += "<div class='text-danger'>Please enter your 13 digits valid CNIC No</div>";
            }
            if(!(/^\d+$/.test(retype_cnic))){
                error += "<div class='text-danger'>All Character must be digit</div>";
            }
            let cnic = $('#cnic').val();
            if(retype_cnic!==cnic){
                error += "<div class='text-danger'>Your CNIC No and retype CNIC No doesn't match</div>";
            }

            if(error!==""){
                //alert_msg(error);
                return error;
            }else{
                return true;
            }
        }else{
            return cnic_val;
        }
    }
    function checkPassportValidation(){
        let passport = $('#passport').val();
        //console.log(cnic);
        let error = "";
        if(!passport){
            error += "<div class='text-danger'>Passport must fill</div>";
        }

        if(passport.length<3||passport.length>20){
            error += "<div class='text-danger'>Passport length should be minimum 3 characters to a maximum of 20 characters</div>";
        }
        if(error!==""){
            //alert_msg(error);
            return error;
        }else{
            return true;
        }
    }
    function checkPassportReValidation(){
        let retype_passport = $('#retype_passport').val();
        let passport = $('#passport').val();
        //console.log(cnic);
        let error = "";
        if(!retype_passport){
            error += "<div class='text-danger'>Passport must fill</div>";
        }

        if(retype_passport.length<3||retype_passport.length>20){
            error += "<div class='text-danger'>Passport length should be minimum 3 characters to a maximum of 20 characters</div>";
        }
        if(retype_passport!==passport){
            error += "<div class='text-danger'>Passport missmatch</div>";
        }
        if(error!==""){
            // alert_msg(error);
            return error;
        }else{
            return true;
        }
    }

    function checkPasswordValidation(){
        let password = $('#password').val();
        //console.log(cnic);
        let error = "";
        if(!password){
            error += "<div class='text-danger'>Password must fill</div>";
        }

        if(password.length<8){
            error += "<div class='text-danger'>Password length should be minimum 8 characters</div>";
        }
        if(error!==""){
            // alert_msg(error);
            return error;
        }else{
            return true;
        }
    }
    function checkPasswordReValidation(){
        let password = $('#password').val();
        let repassword = $('#retype_password').val();
        let error = "";
        if(password!==repassword){
            error += "<div class='text-danger'>Password missmatch</div>";
        }
        if(error!==""){
            // alert_msg(error);
            return error;
        }else{
            return true;
        }
    }
    function setNull() {
        $('#full_name').val(null);
        $('#email').val(null);
        $('#passport').val(null);
        $('#retype_passport').val(null);
        $('#password').val(null);
        $('#retype_password').val(null);
        $('#cnic').val(null);
        $('#retype_cnic').val(null);
        $('#mobile').val(null);
        $('#surname').val(null);
        $('#f_name').val(null);
        $('#mobile_verification_code').val(null);
         $('#email_verification_code').val(null);
         $('#mobile_verification_msg').html("");
         $('#email_verification_msg').html("");
        getProvinces($("#COUNTRY_ID").val());
        getDistrict(0);
    }

    function getProvinces(country_id){
        if(country_id>0){
            $("#PROVINCE_ID").html("<option value='0'>--Choose--</option>");
            jQuery.ajax({
                url: "<?=base_url()?>api/getProvinceByCountryId?country_id="+country_id,
                async:true,
                success: function (data, status) {
                    $('#alert_msg_for_ajax_call').html("");

                    data.forEach(function(item, index) {
                        $("#PROVINCE_ID").append(new Option(item.PROVINCE_NAME, item.PROVINCE_ID));
                    });



                },
                beforeSend:function (data, status) {


                    $('#alert_msg_for_ajax_call').html("LOADING...!");
                },
                error:function (data, status) {
                    alertMsg("Error",data.responseText);
                    $('#alert_msg_for_ajax_call').html("Something went worng..!");
                },
            });
        }else{
            $("#PROVINCE_ID").html("<option value='0'>--Choose--</option>");
            console.log("error");
        }
    }
    function getDistrict(province_id){
        if(province_id>0){
            $("#DISTRICT_ID").html("<option value='0'>--Choose--</option>");
            jQuery.ajax({
                url: "<?=base_url()?>api/getDistrictByProvinceId?province_id="+province_id,
                async:true,
                success: function (data, status) {
                    $('#alert_msg_for_ajax_call').html("");

                    data.forEach(function(item, index) {
                        $("#DISTRICT_ID").append(new Option(item.DISTRICT_NAME, item.DISTRICT_ID));
                    });



                },
                beforeSend:function (data, status) {


                    $('#alert_msg_for_ajax_call').html("LOADING...!");
                },
                error:function (data, status) {
                    alertMsg("Error",data.responseText);
                    $('#alert_msg_for_ajax_call').html("Something went worng..!");
                },
            });
        }else{
            $("#DISTRICT_ID").html("<option value='0'>--Choose--</option>");
            console.log("error");
        }
    }
    getProvinces($("#COUNTRY_ID").val());
</script>
