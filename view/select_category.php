<?php
/**
 * Created by PhpStorm.
 * User: JAVED
 * Date: 2020-09-03
 * Time: 2:17 PM
 */


$GENERAL_MERIT_OBJ=null;
$SELF_FINANCE_OBJ =null;
$SU_EMPLOYEE_QUOTA_OBJ=null;
$SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_OBJ=null;
$DISABLED_PERSON_QUOTA_OBJ=null;
$SPORTS_QUOTA_OBJ=null;
$HAFIZ_QUOTA_OBJ = null;
$HAFIZ_QUOTA_CHECK = $SELF_FINANCE_CHECK = $SU_EMPLOYEE_QUOTA_CHECK=$SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_CHECK=$DISABLED_PERSON_QUOTA_CHECK=$SPORTS_QUOTA_CHECK="";

    foreach ($list_of_category as $category){

        if($category['FORM_CATEGORY_ID']==1){
            $GENERAL_MERIT_OBJ = $category;
        } else if($category['FORM_CATEGORY_ID']==7){
            $EVENING_PROGRAM_OBJ = $category;
            $EVENING_PROGRAM_CHECK = "checked";
        } else if($category['FORM_CATEGORY_ID']==2){
            $SELF_FINANCE_OBJ = $category;
            $SELF_FINANCE_CHECK = "checked";
        } else if($category['FORM_CATEGORY_ID']==3){
            $SU_EMPLOYEE_QUOTA_OBJ = $category;
            $SU_EMPLOYEE_QUOTA_CHECK = "checked";
        } else if($category['FORM_CATEGORY_ID']==4){
            $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_OBJ = $category;
            $SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_CHECK = "checked";
        } else if($category['FORM_CATEGORY_ID']==5){
            $DISABLED_PERSON_QUOTA_OBJ = $category;
            $DISABLED_PERSON_QUOTA_CHECK = "checked";
        } else if($category['FORM_CATEGORY_ID']==6){
            $SPORTS_QUOTA_OBJ = $category;
            $SPORTS_QUOTA_CHECK = "checked";
        }
     else if($category['FORM_CATEGORY_ID']==9){
            $HAFIZ_QUOTA_OBJ = $category;
            $HAFIZ_QUOTA_CHECK = "checked";
        }

    }
?>
<style>
    .checkbox {
        height: 26px;
        width: 26px;
    }

    h3 {

        font-size: 11pt;
    }

    span {
        font-size: 10pt;
    }
</style>
<div id="min-height" class="container-fluid" style="padding:30px">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <br>
                <?php

                    $data['application']=$application;
                    $data['users_reg']=$form_data['users_reg'];
                    $data['qualifications']=$form_data['qualifications'];
                    $data['category']=$list_of_category;
                    $data['program_choice']=$CHOOSEN_PROGRAM_LIST;
                    show_progress_status($data);
                ?>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="review-content-section">
                        <div id="dropzone1" class="pro-ad">
                            <div class="card">
                                <div class='card-header'>
                                    <h4>Please select your option from the following categories and tick the appropriate
                                        box. (you can select more than one category if applicable)</h3>
                                </div>
                                <div class="card-body">
                                    <form action="<?=base_url()."form/select_category_handler"?>" method="post"
                                        id="select_category_form" enctype="multipart/form-data">
                                        <table class="table">
                                            <tr>
                                                <th style="width: 100px;"><input checked disabled type="checkbox"
                                                        class="checkbox" name="GENERAL_MERIT" id="GENERAL_MERIT"></th>
                                                <th><label>
                                                        <h3>GENERAL MERIT</h3>
                                                    </label></th>
                                            </tr>
                                            <tr>
                                                <th style="width: 100px;"><input <?=$SELF_FINANCE_CHECK?>
                                                        type="checkbox" class="checkbox" name="SELF_FINANCE"
                                                        id="SELF_FINANCE"></th>
                                                <th><label>
                                                        <h3>SELF FINANCE (MORNING)</h3>
                                                    </label></th>
                                            </tr>
                                            <tr>
                                                <th style="width: 100px;"><input <?=$EVENING_PROGRAM_CHECK?>
                                                        type="checkbox" class="checkbox" name="EVENING_PROGRAM"
                                                        id="EVENING_PROGRAM"></th>
                                                <th><label>
                                                        <h3>EVENING PROGRAM</h3>
                                                    </label></th>
                                            </tr>
                                            <tr>
                                                <th style="width: 100px;"><input <?=$SU_EMPLOYEE_QUOTA_CHECK?>
                                                        type="checkbox" class="checkbox" name="SU_EMPLOYEE_QUOTA"
                                                        id="SU_EMPLOYEE_QUOTA"></th>
                                                <th><label>
                                                        <h3>UMPK EMPLOYEE QUOTA</h3>
                                                        <span class="text-danger">(SEATS FOR REAL SONS / DAUGHTERS /
                                                            BROTHERS / SISTERS OF THE EMPLOYEES OF <?=strtoupper(UNIVERSITY_NAME)?>)</span>
                                                    </label>
                                                </th>
                                            </tr>
                                            <tr id="SU_EMPLOYEE_QUOTA_FORM">
                                                <?php
                                                if($SU_EMPLOYEE_QUOTA_OBJ){
                                                    $SU_EMPLOYEE_QUOTA_CATEGORY_INFO = json_decode($SU_EMPLOYEE_QUOTA_OBJ['CATEGORY_INFO'],true);
                                                }
                                                ?>
                                                <th colspan="2">
                                                    <div class="row">
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Employee Name
                                                                    <span class="text-danger">*</span></label>
                                                                <input
                                                                    value="<?=isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['EMPLOYEE_NAME'])?$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['EMPLOYEE_NAME']:''?>"
                                                                    type="text" id="EMPLOYEE_NAME"
                                                                    class="form-control allow-string"
                                                                    placeholder="Employee Name" name="EMPLOYEE_NAME">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Designation
                                                                    <span class="text-danger">*</span></label>
                                                                <input
                                                                    value="<?=isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['DESIGNATION'])?$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['DESIGNATION']:''?>"
                                                                    type="text" id="DESIGNATION"
                                                                    class="form-control allow-string"
                                                                    placeholder="Designation" name="DESIGNATION">


                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Department Name
                                                                    <span class="text-danger">*</span></label>
                                                                <input
                                                                    value="<?=isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['DEPARTMENT_NAME'])?$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['DEPARTMENT_NAME']:''?>"
                                                                    type="text" id="DEPARTMENT_NAME"
                                                                    class="form-control allow-string"
                                                                    placeholder="Department Name"
                                                                    name="DEPARTMENT_NAME">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <?php
                                                            $permanent = $temp = "";
                                                            if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['JOB_NATURE'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['JOB_NATURE']=="PERMANENT"){
                                                                $permanent ="selected";
                                                            }
                                                            else if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['JOB_NATURE'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['JOB_NATURE']=="TEMPORARY"){
                                                                $temp ="selected";
                                                            }
                                                        ?>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Regular / Temporary
                                                                    <span class="text-danger">*</span></label>
                                                                <select type="text" id="IS_REGULAR"
                                                                    class="form-control " name="IS_REGULAR">
                                                                    <option value="0">Choose</option>
                                                                    <option <?=$permanent?>>Permanent</option>
                                                                    <option <?=$temp?>>Temporary</option>
                                                                </select>

                                                            </div>
                                                        </div>
                                                        <?php
                                                            $wife =$Mother = $Father = "";
                                                            $husband  =$Sister = $Brother = "";
                                                            if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP']=="FATHER"){
                                                                $Father ="selected";
                                                            }
                                                            else if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP']=="MOTHER"){
                                                                $Mother ="selected";
                                                            }
                                                            else if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP']=="SISTER"){
                                                                $Sister ="selected";
                                                            }
                                                            else if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP']=="BROTHER"){
                                                                $Brother ="selected";
                                                            }
                                                            else if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP']=="HUSBAND"){
                                                                $husband ="selected";
                                                            }
                                                            else if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP'])&&$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['RELATIONSHIP']=="WIFE"){
                                                                $wife ="selected";
                                                            }
                                                        ?>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Relationship With
                                                                    Candidate
                                                                    <span class="text-danger">*</span></label>
                                                                <select type="text" id="RELATIONSHIP"
                                                                    class="form-control allow-string"
                                                                    placeholder="RELATIONSHIP" name="RELATIONSHIP">
                                                                    <option value="0">Choose</option>
                                                                    <option <?=$Father?>>Father</option>
                                                                    <option <?=$Mother?>>Mother</option>
                                                                    <option <?=$Sister?>>Sister</option>
                                                                    <option <?=$Brother?>>Brother</option>
                                                                     <option <?=$wife?>>Wife</option>
                                                                     <option <?=$husband?>>Husband</option>

                                                                </select>


                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div style="margin-top:35px">

                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Upload Service
                                                                    Certificate of Employee
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <?php
                                                                $image_path = "";
    
                                                                $image_path_default =base_url()."dash_assets/img/avatar/docavtar.png";
                                                                if(isset($SU_EMPLOYEE_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'])){
                                                                    $image_path_default=base_url().EXTRA_IMAGE_PATH.$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                                                                    $image_path=$SU_EMPLOYEE_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                                                                }
                                                                ?>
                                                                <img src="<?php echo $image_path_default; ?>"
                                                                    alt="Service Certificate Of Employee"
                                                                    class="img-table-certificate"
                                                                    id="service-certificate-of-employee-image-view"
                                                                    onclick="setImage()" width="150px" height="150px"
                                                                    name="service-certificate-of-employee-image-view">
                                                                <input type="file"
                                                                    name="service_certificate_of_employee_image"
                                                                    id="service_certificate_of_employee_image"
                                                                    onchange="changeImage(this,'service_certificate_of_employee_image','service-certificate-of-employee-image-view',500)"
                                                                    accept=".jpg,.png,.jpeg">
                                                                <input type="text"
                                                                    name="service_certificate_of_employee_image1"
                                                                    id="service_certificate_of_employee_image1"
                                                                    value="<?php echo $image_path; ?>" hidden>
                                                                <span class="text-danger">Make Sure Image must be clear
                                                                    and Image size should be less than 500KB</span>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                             
                                            <tr>
                                                <th style="width: 100px;">
                                                    <input <?=$DISABLED_PERSON_QUOTA_CHECK?>
                                                        type="checkbox" class="checkbox" name="DISABLED_PERSON_QUOTA"
                                                        id="DISABLED_PERSON_QUOTA">
                                                </th>
                                                <th>
                                                    <label>
                                                        <h3>DISABLED PERSON QUOTA</h3>
                                                    </label>
                                                </th>
                                            </tr>
                                            <tr id="DISABLED_PERSON_QUOTA_FORM">
                                                <th colspan="2">
                                                    <?php
                                                        if($DISABLED_PERSON_QUOTA_OBJ){
                                                            $DISABLED_PERSON_QUOTA_CATEGORY_INFO = json_decode($DISABLED_PERSON_QUOTA_OBJ['CATEGORY_INFO'],true);
                                                        }
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Type of Disability
                                                                    <span class="text-danger">*</span></label>
                                                                <?php
                                                                    $permanent = $temp = "";
                                                                    if(isset($DISABLED_PERSON_QUOTA_CATEGORY_INFO['TYPE_OF_DISABILITY'])&&$DISABLED_PERSON_QUOTA_CATEGORY_INFO['TYPE_OF_DISABILITY']=="PERMANENT"){
                                                                        $permanent ="selected";
                                                                    }
                                                                    else if(isset($DISABLED_PERSON_QUOTA_CATEGORY_INFO['TYPE_OF_DISABILITY'])&&$DISABLED_PERSON_QUOTA_CATEGORY_INFO['TYPE_OF_DISABILITY']=="ACCIDENTAL"){
                                                                        $temp ="selected";
                                                                    }
                                                                ?>
                                                                <select type="text" id="TYPE_OF_DISABILTY"
                                                                    class="form-control allow-string" placeholder=""
                                                                    name="TYPE_OF_DISABILTY">
                                                                    <option value="0">Choose</option>
                                                                    <option <?=$permanent?>>Permanent</option>
                                                                    <option <?=$temp?>>Accidental</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div style="margin-top:35px">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Upload Medical
                                                                    Certificate
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <br>
                                                                <?php
                                                                    $image_path = "";
                                                                    $image_path_default =base_url()."dash_assets/img/avatar/docavtar.png";
                                                                    if(isset($DISABLED_PERSON_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'])){
                                                                        $image_path_default=base_url().EXTRA_IMAGE_PATH.$DISABLED_PERSON_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                                                                        $image_path=$DISABLED_PERSON_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                                                                    }
                                                                ?>
                                                                <img src="<?php echo $image_path_default; ?>"
                                                                    alt="Medical Certificate"
                                                                    class="img-table-certificate"
                                                                    id="medical-certificate-image-view"
                                                                    onclick="setImage()" width="150px" height="150px"
                                                                    name="medical-certificate-image-view">
                                                                <input type="file" name="medical_certificate_image"
                                                                    id="medical_certificate_image"
                                                                    onchange="changeImage(this,'affiliated_service_certificate_of_employee_image','medical-certificate-image-view',500)"
                                                                    accept=".jpg,.png,.jpeg">
                                                                <input type="text" name="medical_certificate_image1"
                                                                    id="medical_certificate_image1"
                                                                    value="<?php echo $image_path; ?>" hidden>
                                                                <span class="text-danger">Make Sure Image must be clear
                                                                    and Image size should be less than 500KB</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                              <tr>
                                                <th style="width: 100px;">
                                                    <input <?=$HAFIZ_QUOTA_CHECK?>
                                                        type="checkbox" class="checkbox" name="HAFIZ_QUOTA"
                                                        id="HAFIZ_QUOTA">
                                                </th>
                                                <th>
                                                    <label>
                                                        <h3>HAFIZ E QURAN QUOTA</h3>
                                                    </label>
                                                </th>
                                            </tr>
                                            <tr id="HAFIZ_QUOTA_FORM">
                                                <th colspan="2">
                                                    <?php
                                                        if($HAFIZ_QUOTA_OBJ){
                                                            $HAFIZ_QUOTA_CATEGORY_INFO = json_decode($HAFIZ_QUOTA_OBJ['CATEGORY_INFO'],true);
                                                        }
                                                    ?>
                                                    <div class="row">
                                                          <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div style="margin-top:35px">
                                                                <label for="exampleInput1"
                                                                    class="bmd-label-floating">Upload Hafiz e Quran Certificate
                                                                    
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <br>
                                                                <?php
                                                                    $image_path = "";
                                                                    $image_path_default =base_url()."dash_assets/img/avatar/docavtar.png";
                                                                    if(isset($HAFIZ_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'])){
                                                                        $image_path_default=base_url().EXTRA_IMAGE_PATH.$HAFIZ_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                                                                        $image_path=$HAFIZ_QUOTA_CATEGORY_INFO['CERTIFICATE_IMAGE'];
                                                                    }
                                                                ?>
                                                                <img src="<?php echo $image_path_default; ?>"
                                                                    alt="Hafiz e Quran Certificate"
                                                                    class="img-table-certificate"
                                                                    id="hafiz-certificate-image-view"
                                                                    onclick="setImage()" width="150px" height="150px"
                                                                    name="hafiz-certificate-image-view">
                                                                <input type="file" name="hq_certificate_image"
                                                                    id="hq_certificate_image"
                                                                    onchange="changeImage(this,'hq_certificate_image','hq-certificate-image-view',500)"
                                                                    accept=".jpg,.png,.jpeg">
                                                                <input type="text" name="hq_certificate_image1"
                                                                    id="hq_certificate_image1"
                                                                    value="<?php echo $image_path; ?>" hidden>
                                                                <span class="text-danger">Make Sure Image must be clear
                                                                    and Image size should be less than 500KB</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="width: 100px;"><input <?=$SPORTS_QUOTA_CHECK?>
                                                        type="checkbox" class="checkbox" name="SPORTS_QUOTA"
                                                        id="SPORTS_QUOTA"></th>
                                                <th>
                                                    <label>
                                                        <h3>SPORTS QUOTA</h3>
                                                    </label>
                                                </th>
                                            </tr>
                                        </table>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <br>
                                                <button type="button" class="btn btn-warning btn-lg"
                                                    onclick="window.location.href = '<?=base_url('candidate/profile')?>'">Back</button>
                                            </div>
                                            <div class="col-md-3">
                                                <br>
                                                <button type="submit" class="btn btn-success btn-lg">Save</button>
                                            </div>
                                            <div class="col-md-3">
                                                <br>
                                                <button type="button" class="btn btn-primary btn-lg"
                                                    onclick="get_next()">Save & Next</button>
                                            </div>
                                        </div>
                                    </form>
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
    //Sindh Employee Quota Start
    <?php
        if($SU_EMPLOYEE_QUOTA_CHECK==""){
            echo "$('#SU_EMPLOYEE_QUOTA_FORM').hide();";
        }
        if($SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_CHECK==""){
            echo "$('#SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_FORM').hide();";
        }
        if($DISABLED_PERSON_QUOTA_CHECK==""){
            echo "$('#DISABLED_PERSON_QUOTA_FORM').hide();";
        }
         if($HAFIZ_QUOTA_CHECK==""){
            echo "$('#HAFIZ_QUOTA_FORM').hide();";
        }
    ?>


    $("#SU_EMPLOYEE_QUOTA").change(function() {
        if ($("#SU_EMPLOYEE_QUOTA").is(':checked')) {
            $('#SU_EMPLOYEE_QUOTA_FORM').show();

        } else {
            $('#SU_EMPLOYEE_QUOTA_FORM').hide();
        }
    });
    //Sindh Employee Quota End

    //Sindh Affiliated College Employee Quota Start


    $("#SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA").change(function() {
        if ($("#SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA").is(':checked')) {
            $('#SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_FORM').show();

        } else {
            $('#SU_AFFILIATED_COLLEGE_EMPLOYEE_QUOTA_FORM').hide();
        }
    });
    //Sindh Affiliated College Employee Quota End

    //Diabled Person Quota Start


    $("#DISABLED_PERSON_QUOTA").change(function() {
        if ($("#DISABLED_PERSON_QUOTA").is(':checked')) {
            $('#DISABLED_PERSON_QUOTA_FORM').show();

        } else {
            $('#DISABLED_PERSON_QUOTA_FORM').hide();
        }
    });
    $("#HAFIZ_QUOTA").change(function() {
        if ($("#HAFIZ_QUOTA").is(':checked')) {
            $('#HAFIZ_QUOTA_FORM').show();

        } else {
            $('#HAFIZ_QUOTA_FORM').hide();
        }
    });
    //Diabled Person Quota  End
    var is_next = false;
    $('#select_category_form').submit(function(event) {
        event.preventDefault();
        // if(!category_validate()){
        //     return;
        //
        // }
        var form = $('#select_category_form')[0];
        var data = new FormData(form);
        $('.preloader').fadeIn(700);
        jQuery.ajax({
            url: "<?=base_url()?>form/select_category_handler",
            type: "POST",
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (evt.loaded / evt.total) * 100;
                        percentComplete = Math.round(percentComplete);
                        $("#pre_text").html("<br><br><h3>Uploading " + percentComplete +
                            "%</h3>");
                        console.log(percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function(data, status) {
                $('.preloader').fadeOut(700);
                // $('input[name="csrf_form_token"]').val(data.csrfHash);
                $('#alert_msg_for_ajax_call').html("");
                alertMsg("Success", data.MESSAGE);
                if (is_next == true) {
                    window.location.href = '<?=base_url('form/select_program')?>';
                }
                is_next = false;
                //console.log(is_next);


            },
            beforeSend: function(data, status) {


                $('#alert_msg_for_ajax_call').html("LOADING...!");
            },
            error: function(data, status) {
                var value = data.responseJSON;
                alertMsg("Error", value.MESSAGE);
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                $('#alert_msg_for_ajax_call').html(value.MESSAGE);
                $('.preloader').fadeOut(700);
                is_next = false;
            },
        });
    });

    function get_next() {
        is_next = true;
        $('#select_category_form').submit();

    };
</script>
