<div style="height:100px"></div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4" style="padding-left: 40px;">
            <div class="card" style="margin-top: 20px;margin-bottom: 50px;min-height: 450px;">
                <div class="card-header">
                    <h1 >LOGIN</h1>
                </div>
                <div class="card-body">
                    <div class="login">
<!--                        <form  id="registration" action="--><?//=base_url()?><!--login/loginHandler" class="row" method="post" >-->
                        <?=form_open('AdminLogin/adminLoginHandler')?>
<!--                            <div class="col-12">-->
<!--                                <label for="" style="font-size:17px">CNIC<span class="text-danger"></span></label>-->
<!--                                <input style="width:1.3em;height:1.3em;" type="radio" class=" mb-3" id="is_cnic" name="check_cnic" value="cnic" checked>-->
<!--                                &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;-->
<!--                                <label for="" style="font-size:17px">Passport <span class="text-danger"></span></label>-->
<!--                                <input style="width:1.3em;height:1.3em;" type="radio" class=" mb-3" id="is_passport" name="check_cnic" value="passport">-->
<!--                            </div>-->
                            <div id="cnic_view" style="width:100%">



                                <div class="col-12">
                                    <label for="" style="font-size:17px">CNIC<span class="text-danger">* (without dashes) </span></label>
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
                                <button type="submit" id="register" name='login' class="btn btn-primary btn-lg">login</button>
                            </div>

                            <div class="col-12 top-margin" >
								<br>
                                <a  class="btn btn-primary " href="forget">Forgot Password</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card" style="margin-top: 20px;margin-bottom: 50px;min-height: 400px;">
                <div class="card-header card-header-primary text-center">
                    <h3 class="card-title ">Important Notes / Instructions</h3>
                </div>
                <div class="card-body" style='margin-top:0px'>
                    <div class="row">
                        <div class="col-md-12">
                            <!--<h4 style="font-size: 14pt;margin-top:15px;font-family:'Times New Roman', Times, serif ">-->

                            <!--<h4 style="color:red;text-align: justify;padding:20px;font-size: 14pt;">-->

                            <!-- Carousel Card -->
                             <!-- End Carousel Card -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
