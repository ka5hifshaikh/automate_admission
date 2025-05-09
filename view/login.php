<style>
.blink_me {
  animation: blinker 3s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}
.notice-msg{
    font-size: 13pt;margin-top:15px;font-family: Times, serif; 
}
.card
{

	padding-right: 20px;
	padding-left: 20px;
	padding-bottom: 20px;
	text-align: left;
	border: 0;
	margin-bottom: 30px;
	margin-top: 30px;
	border-radius: 6px;
	color: rgba(0, 0, 0, 0.87);
	background: #fff;
	width: 100%;
	box-shadow: 0 10px 20px 0 rgba(0, 0, 0, 0), 0 3px 1px -2px rgba(84, 84, 84, 0.15), 0 1px 5px 0 rgba(0, 0, 0, 0.12);
}
.card-header{
	margin-top: -15px;
	padding: 10px;
}
</style>





<marquee><h2 style='margin-left: 50px;font-weight: bold;' class="text-danger">
Last date for Online Submission of Admission Form is 21-11-2024.
<!--New Registration for Admissions 2025 is CLOSED.-->
</marquee>


        </div>
    </div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4" style="padding-left: 40px;">
            <div class="card" style="margin-top: 20px;margin-bottom: 50px;min-height: 400px;">
           <div class="card-header card-header-primary bg-theme-color-2 text-center">
                    <h3 class="card-title text-white  ">Login</h3>
                </div>
                <div class="card-body">
                    <div class="login">
                        <?=form_open('login/loginHandler')?>
                            <div class="col-12">

                                <input hidden style="width:1.3em;height:1.3em;" type="radio" class=" mb-3" id="is_cnic" name="check_cnic" value="cnic" checked>
                                &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;
							</div>
                            <div id="cnic_view" style="width:100%">

                                <div class="col-12">
                                    <label for="" style="font-size:17px">CNIC No.<span class="text-danger">* (without dashes)</span></label>
                                    <input  type="text" class="form-control mb-3" id="cnic" name="cnic" placeholder="CNIC or Form-B(xxxxxxxxxxxxx) ">
                                </div>

                            </div>
                            <div id="passport_view" style="width:100%">



                                <div class="col-12">
                                    <label for="" style="font-size:17px">Passport No<span class="text-danger">* </span></label>
                                    <input  type="text" class="form-control mb-3" id="passport" name="passport" placeholder="Passport No">
                                </div>

                            </div>
                            <div class="col-12">
                                <label for="" style="font-size:17px">Password<span class="text-danger">* </span></label>
                                <input  type="password" class="form-control mb-3" id="password" name="password" placeholder="Password">
                            </div>

                            <div class="col-12">
								<br>
                                <button type="submit" id="register" name='login' class="btn btn-primary btn-md"><span class='fa fa-unlock'></span>&nbsp;&nbsp;login</button>
                            
                            <a  class="text-right text-success" style="font-size:13pt; font-weight:bold"  href="<?=base_url()?>forget">Forgot Password?</a>
                            </div>
                        <hr/>
                            <div class="col-12 top-margin" style="font-size:17px;  " >
                                <b>New Student? Click below for Registration </b><br /><br />
                                <a  class="text-right bg-theme-color-2" style="font-size:11pt; text-decoration: none; margin: 0px; padding: 14px; color: white;"  href="register"> New Registration</a>
          <!--                  <a id="" href="register"  class="btn btn-success text-center">-->
          <!--  <i class="fa fa-signup"></i> New Registration-->
          <!--</a>-->
          <p><br /> </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card" style="margin-top: 20px;margin-bottom: 50px;min-height: 420px;">
                <div class="card-header card-header-primary   bg-theme-color-2 text-center">
                    <h3 class="card-title text-white">DIRECTORATE OF ADMISSIONS</h3>
                    <h4 class="card-title text-white">Important Instructions</h4>
                </div>
                <div class="card-body" style='margin-top:0px'>
                    <div class="row">
                        <div class="col-md-12">
                             <h4 class=' notice-msg' >-&nbsp;<a href="<?=base_url().''?>" target='_blank' ><b>Click here to download  Advertisement 2025</b></a>.</h4>

                        <h4 class=' notice-msg' >-&nbsp;First create your account on <?=UNIVERSITY_NAME?> Admission Portal by clicking on <a href=<?=base_url().'register'?>><b>New Registeration</b></a>.</h4>
					<h4 class=' notice-msg' >-&nbsp;It is mandatory to use your own CNIC or B-Form Number for new registration. </h4>
                            <h4 class=' notice-msg' >-&nbsp;If you are currently enrolled or Ex-Student of <?=UNIVERSITY_NAME?> and want to apply for the Admissions in 2025, you can login with your previous LMS/ Eportal account password. You don't need to select New Registration.</h4>

                            <h4 class=' notice-msg' >-&nbsp;Please use your own Mobile Number and Email Address in registration process because <?=UNIVERSITY_NAME?> may correspond/contact with you during admission process on your given mobile number or email address.</h4>


                            <h4 class=' notice-msg' >-&nbsp;Please Login with your CNIC Number and password, and complete your Online Admission Form by filling all required information and uploading all required documents.</h4>

                            <!-- <p class='' style="font-size:15px; font-family:'Times New Roman', Times, serif">-&nbsp; <a href='#'>Click here to watch video tutorial how to fill admission form</a>, if you want to read guidlines in (English/ Sindh/ Urdu) <a href='#'>please click here.</a> </h2> -->

                            <h4 class=' notice-msg' >-&nbsp;If you have any query please email <b>Directorate of Admissions Help Desk</b> at <a href="mailto: <?=EMAIL?>" style="text-decoration: none; margin: 0px; padding: 05px; background-color:blue; color: white;"> <b><?=EMAIL?></b></a>, you will get reply within 24 to 48 hrs (working days)
                             or Contact on given numbers : <?=PHONE_NO?> (during office Hours 09:00am to 04:00pm) </h4>

                            <h4 class=' notice-msg' >-&nbsp;It is recommended to use Google Chrome / Mozilla FireFox / Microsoft Internet Explorer Browser for form filling process on your Desktop / Laptop Pc. Please avoid form filling process through your Smart Phone.
                            </h4>
                            <!--<h4 style="color:red;text-align: justify;padding:20px;font-size: 14pt;">-->


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<br/>
<br/>
  <!--<div class="main main-raised">-->
  <!--  <div class="container">-->
  <!--    <div class="section text-center">-->
  <!--      <img src="<?=base_url()."assets/img/Advertisement 2022.jpg"?>" alt="adv"/>-->
  <!--      <br/>-->
  <!--      <img src="<?=base_url()."assets/img/kashif.jpg"?>" alt="adv"/>-->
       
  <!--    </div>-->
       
      
  <!--  </div>-->
  <!--</div>-->
  

</div>
<script>

$(document).ready(function(){
    alert_msg("<h3 class='text-danger'>Dear students, you can log in to the admission portal and download your slip by tomorrow evening, 22 November 2024.<br><br><br>Pre-entry Test is scheduled on Sunday, 24th November 2024.<h3>","News Alert!");
//alert_msg('<a href="https://admission.usindh.edu.pk/admission/selection_list"><img width="950px" src="assets/img/list.jpeg"></a>','Provisinol List');

//alert_msg('<center><iframe width="560" height="315" src="https://www.youtube.com/embed/r-9eV2F5QOs" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></center>','<h3 class="text-success" style="text-align:centre">How to apply in Evening Bachelor/Master Degree Programs?</h3>');
    
});
</script>
