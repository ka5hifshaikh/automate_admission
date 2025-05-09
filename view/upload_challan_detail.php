<div id = "min-height" class="container-fluid" style="padding:30px">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <?php
            
           
                                    
            $url = base_url().'form/admission_form_challan';
            ?>
            <a target='_blank' href='<?=$url?>' class='btn btn-warning widget-btn-1 btn-lg'>Download Challan</a>
            <li class="list-group-item list-group-item-warning" style="font-weight: bold"> Download your system generated Challan of Admission Processing Fee of Rs. <?=$application['CHALLAN_AMOUNT']?>/- which has to be paid in Sindh Bank Mirpurkhas branch.  </li>
            <div class="row">
                <br>
                 <?php
                $data['application']=$application;
                $data['users_reg']=$user;
                $data['qualifications']=$qualifications;
                $data['category']=$category;
                $data['program_choice']=$program_choice;
                show_progress_status($data);
                ?>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="review-content-section">
                        <div id="dropzone1" class="pro-ad">
                            <div class="card">
                                <div class="card-header">
                                    <h1>Upload Paid Challan For <?=ucwords(strtolower($application['PROGRAM_TITLE']))?> Program in <?=ucwords(strtolower($application['NAME']))?></h1>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $hidden = array("APPLICATION_ID"=>$APPLICATION_ID);

                                    ?>

                            <!--                                        <form action="/upload" class="dropzone dropzone-custom needsclick add-professors dz-clickable" id="demo1-upload" novalidate="novalidate">-->

                            <?php
                            if($application['IS_SUBMITTED']=="N"){
                                echo form_open(base_url('form/challan_upload_handler'), ' enctype="multipart/form-data" class="dropzone dropzone-custom needsclick add-professors dz-clickable" id="challan_form "',$hidden);
                            }

                            ?>

                            <div class="row">

                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="exampleInput1" class="bmd-label-floating">Bank Branch
                                            <span class="text-danger">*</span></label>
                                        <br>
                                              <select   readonly id="BRANCH_ID" class=" form-control "  name="BRANCH_ID">
                                            <!--<option value="0">------------Choose------------</option>-->
                                            <?php

                                            foreach ($bank_branches as $bank_branch) {
                                                $select = "";
                                                
                                                echo "<option value='{$bank_branch['BRANCH_ID']}'  $select>{$bank_branch['BRANCH_CODE']} &nbsp;&nbsp;{$bank_branch['BRANCH_NAME']}</option>";
                                                if(1==$bank_branch['BRANCH_ID']){
                                                    $select = "selected";
                                                    break;
                                                }
                                            }
                                            ?>

                                        </select>

                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="exampleInput1" class="bmd-label-floating">Challan Paid Date
                                            <span class="text-danger">* &nbsp;<small>dd/mm/yyyy</small></span></label>
                                        <div class="form-group data-custon-pick" id="data_2">
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <?php
                                                if($application['CHALLAN_DATE']){
                                                    $date = getDateForView($application['CHALLAN_DATE']);
                                                }else{
                                                    $date = date('d/m/Y');
                                                    $date="";
                                                }

                                                ?>
                                                <input  type="text" id="CHALLAN_PAID_DATE"  name="CHALLAN_PAID_DATE" class="form-control"   value="<?=$date?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="exampleInput1" class="bmd-label-floating">Challan Number
                                            <span class="text-danger">*</span>
                                            <span class="text-danger" id="CHALLAN_NO_VIEW_MSG"></span>
                                        </label>
                                        <input  readonly value ="<?= $challan_no = ADMP_CODE.str_pad($application['FORM_CHALLAN_ID'], 5, "0", STR_PAD_LEFT);?>"type="text" id="CHALLAN_NO" class="form-control allow-number" placeholder="CHALLAN NO" name="CHALLAN_NO" value="<?=($application['PAID']=='N'||$application['PAID']=='Y')?$application['FORM_CHALLAN_ID']:'';?>">


                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="exampleInput1" class="bmd-label-floating">Challan Amount
                                            <span class="text-danger">*</span>
                                            <span class="text-danger" id="CHALLAN_AMOUNT_VIEW_MSG"></span>
                                        </label>
                                        <input readonly type="text"  value =" <?=$application['CHALLAN_AMOUNT']?>" id="CHALLAN_AMOUNT" class="form-control allow-number" placeholder="CHALLAN AMOUNT" name="CHALLAN_AMOUNT" value="<?=$application['PAID_AMOUNT']?>" >


                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                    <div class="form-group res-mg-t-15">
                                        <label for="exampleInput1" class="bmd-label-floating">Paid Challan Image
                                            <span class="text-danger">*</span>
                                        </label><br>
                                        <?php

                                        $image_path_default =base_url()."dash_assets/img/avatar/docavtar.png";
                                        $image_path = "";
                                        if($application['CHALLAN_IMAGE'] != ""){

                                            $image_path_default = base_url().EXTRA_IMAGE_PATH.$application['CHALLAN_IMAGE'];
                                            $image_path = base_url(). EXTRA_IMAGE_PATH.$application['CHALLAN_IMAGE'];

                                        }
                                        ?>

                                        <img src="<?php echo $image_path_default; ?>" alt="CHALLAN IMAGE" id="challan-image-view"  class="img-table-certificate"  width="150px" height="150px" name="challan-image-view" >
                                        <input type="file" name="challan_image" id="challan_image"   onchange="changeImage(this,'challan_image','challan-image-view',500)" accept=".jpg,.png,.jpeg" value="<?php echo $image_path; ?>">
                                        <input type="text" name="challan_image1" id="challan_image1" value="<?php echo $image_path; ?>" hidden>
                                        <span class="text-danger">Make Sure Image must be clear and Image size should be less than 500KB</span>

                                    </div>
                                </div>
                            </div>
                                    <div class="row">
                                        <div class="col-lg-4">
                                        </div>
                                        <?php
                                    if($application['IS_SUBMITTED']=="N") {
                                        ?>
                                        <div class="col-lg-4">
                                            <div class="payment-adress">
                                                <button type="submit"
                                                       value="save"  name="action" class="btn btn-primary btn-lg waves-effect waves-light">Save
                                                </button>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                        ?>
                                        <div class="col-lg-2">
                                            <div class="">
                                                <button type="submit" name="action" class="btn btn-success btn-lg "  value="next"  >Save & Next</button>
                                            </div>
                                        </div>

                                    </div>
                                    <?php
                                    if($application['IS_SUBMITTED']=="N"){
                                    echo "</form>";
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    var FORM_CHALLAN_ID = <?=$application['FORM_CHALLAN_ID']?>;
    var CHALLAN_AMOUNT = <?=$application['CHALLAN_AMOUNT']?>;

    $( '#CHALLAN_NO' ).keyup(function() {
       if(FORM_CHALLAN_ID==$( '#CHALLAN_NO' ).val()){
           $( '#CHALLAN_NO_VIEW_MSG' ).html("");
       }else{
           $( '#CHALLAN_NO_VIEW_MSG' ).html("INVALID CHALLAN NO");
       }
    });
    $( '#CHALLAN_AMOUNT' ).keyup(function() {
        if(CHALLAN_AMOUNT==$( '#CHALLAN_AMOUNT' ).val()){
            $( '#CHALLAN_AMOUNT_VIEW_MSG' ).html("");
        }else{
            $( '#CHALLAN_AMOUNT_VIEW_MSG' ).html("INVALID CHALLAN AMOUNT");
        }
    });
    $( '.img-table-certificate' ).click(function() {
        alertImage('Image',$(this).attr('src'));
    });
    function check_validtion_of_challan(){
      //  window.location.href = "<?=base_url()?>form/check_validation_and_challan";
    }
</script>
<style>
    .select2-container--default .select2-results__option[aria-disabled=true] {
        color: #f90000;
    }
    .btn-success {
        color: #fff;
        background-color: #5cb85c;
        border-color: #4cae4c;
    }
</style>
