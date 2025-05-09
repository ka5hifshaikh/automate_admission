<body>
    <!-- Header Area End -->
    <div style="height:100px"></div>
    <div class="container">
        <div class="card" style="margin-top: 20px;margin-bottom: 50px;">
           <div class="row">
               <!--<div class="col-md">-->
               <!--     <div style="padding-left:20px" class='text-danger'><h3>Please must follow the following password policy</h3></br>At least one digit ...!</br>-->
               <!-- At least one lowercase/ small alphabate ...!</br>-->
               <!-- At least one uppercase/ capital alphabate ...!</br>-->
               <!-- At least one special character like (*!,%#@.) ...!</br>-->
               <!-- At least 8 characters in length, but no more than 50 ...!</div></br>-->
               <!--</div>-->
               <div class="col-md">
                   <?=form_open("forget/set_pwd_handler")?>
                <!--<form class="form" method="post" action="">-->
              <div class="card-header card-header-primary text-center">
              <h3 class="card-title">Reset Password</h3>
              </div>
                <?php
                // print_r($USER_DATA);
                $name ="";
                $email = "";
                $cnic = "";
                if(is_array($USER_DATA) || is_object($USER_DATA))
                {
                    $name  = $USER_DATA['NAME'];
                    $email = $USER_DATA['EMAIL'];
                    $cnic  = $USER_DATA['CNIC_NO'];
                }
                echo form_hidden('user_data',json_encode($USER_DATA))
                ?>
              <div class="card-body">
                  <span class="bmd-form-group">
                 <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="material-icons">face</i>
                    </span>
                  </div>
                  <input type="text" class="form-control" placeholder="Name" name="name" value ="<?=$name?>" readonly>
                </div></span>
                <span class="bmd-form-group">
                 <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="material-icons">mail</i>
                    </span>
                  </div>
                  <input type="email" class="form-control" placeholder="Email" name="email" value ="<?=$email?>" readonly>
                </div></span>
                <span class="bmd-form-group">
                 <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="material-icons">chat</i>
                    </span>
                  </div>
                  <?php
                  $cnic= substr( $cnic,0,3);
                $cnic .= "xxxxxxx".substr( $cnic,-3,5);
                  ?>
                  <input type="text" class="form-control" placeholder="CNIC" name="CNIC" value ="<?php echo $cnic;?>" readonly>
                </div></span>
               <span class="bmd-form-group">
                 <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="material-icons">lock</i>
                    </span>
                  </div>
                  <input type="password" class="form-control" placeholder="Password" name="password" >
                </div></span>
               <span class="bmd-form-group">
                 <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="material-icons">lock</i>
                    </span>
                  </div>
                  <input type="password" class="form-control" placeholder="Re-Type Password" name="re_type_password" >
                </div></span>
              
              </div>
              <div>
              
               
                </div>
        
               <div class="footer text-center">
                   <p class='text-danger' style='font-size:12pt; font-weight:bold'>Your password must be atleast 8 characters in length, but no more than 50 ...!</p>
                <input class="btn btn-primary btn-link btn-wd btn-lg" type="submit" value="Set Password" name="confirm">
            
              </div>
             
            </form>
            </div>
            </div>
   </div>