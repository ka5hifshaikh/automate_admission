<?php

$max = 0;
$min = 0;
if($PROGRAM_TYPE_ID==1){
    $max = CHOICE_QUANTITY_FOR_BACHELOR_MAX;
    $min = CHOICE_QUANTITY_FOR_BACHELOR_MIN;
    $min_msg = 5;
}else if($PROGRAM_TYPE_ID==2){
    $max = CHOICE_QUANTITY_FOR_MASTER_MAX;
    $min = CHOICE_QUANTITY_FOR_MASTER_MIN;
    $min_msg = $min;
}
?>
<div id = "min-height" class="container-fluid" style="padding:30px">
    <div class="row">
         <div class="alert alert-warning" role="alert">
                                <strong><p style="text-align: center; font-size: 20pt"><a class='text-danger'>Last date to apply for admission under Special Self Finance Category is Tuesday 30-01-2024.</strong> </a> </p>    
                                <strong><p style="text-align: center; font-size: 16pt"><a href='<?=base_url()?>assets/FEES STRUCTURE SP.SELF FINANCE 2024.pdf' target='_blank' class='text-danger'>Click here for Fees Structure of Bachelor Degree Programs - Special Self Finance Category - 2024.</strong> </a> </p>
                            </div>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="review-content-section">
                        <div id="dropzone1" class="pro-ad">
                            <div class="card">
                                <div class='card-header'>
                                    <center><h3>Please give your choices in preference order for Specail Self- Finance Category - 2024</h3></center>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $hidden = array("APPLICATION_ID"=>$APPLICATION_ID,"DISCIPLINE_ID"=>$DISCIPLINE_ID);

                                    ?>
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <strong><center><h4>
                                                It is advised to please select minimum <?=$min_msg;?> and maximum <?=10?> choices of Subjects / Disciplines / Programs in order of preference according to your last qualification.<br>
                                                Please check prospestus for pre-requiste for each degree program.<br>
                                                <span class='text-danger '><em>Red highlighted degree programs / disciplines / subjects indicates NO eligiblity according to last qualification OR not announced under Specail Self-Finance Category</em></span>
                                            </h4></center></strong>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-5 col-md-5 ">
                                            <div class="form-group">
                                                <label for="exampleInput1" class="bmd-label-floating"> Choose Program
                                                    <span class="text-danger">*</span></label>
                                                <select  class="js-example-basic-single form-control mb-3" name="PROGRAM_LIST_ID" id="PROGRAM_LIST_ID">
                                                    <option value="0">--Choose--</option>
                                                    <?php
                                                    foreach ($PROGRAM_LIST as $PROGRAM) {

                                                        foreach ($VALID_PROGRAM_LIST as $k =>$VALID_PROGRAM) {

                                                            if($PROGRAM['PROG_LIST_ID']==$VALID_PROGRAM['PROG_LIST_ID']){
                                                                $is_add = true;

                                                                foreach ($CHOOSEN_PROGRAM_LIST as $CHOOSEN_PROGRAM ){
                                                                    if($CHOOSEN_PROGRAM['PROG_LIST_ID']==$PROGRAM['PROG_LIST_ID']){
                                                                        $is_add = false;
                                                                        break;
                                                                    }
                                                                }

                                                                if($PROGRAM['PRE_REQ_PER']>$precentage){
                                                                    $is_add = false;
                                                                }

                                                                if($is_add){
                                                                    echo "<option value='{$PROGRAM['PROG_LIST_ID']}'  >{$PROGRAM['PROGRAM_TITLE']} </option>";
                                                                }

                                                                break;
                                                            }
                                                        }
                                                    }
                                                    foreach ($PROGRAM_LIST as $PROGRAM) {
                                                        $bool = true;

                                                        foreach ($VALID_PROGRAM_LIST as $k =>$VALID_PROGRAM) {
                                                            if($PROGRAM['PROG_LIST_ID']==$VALID_PROGRAM['PROG_LIST_ID']){
                                                                $bool = false;
                                                                break;
                                                            }
                                                        }

                                                        if($PROGRAM['PRE_REQ_PER']>$precentage){
                                                            $bool = true;
                                                        }

                                                        if($bool){
                                                            echo "<option value='{$PROGRAM['PROG_LIST_ID']}' disabled >{$PROGRAM['PROGRAM_TITLE']} </option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                            <button  class="btn btn-success " id="add_program">Add Choice</button>

                                        </div>
                                    </div>
                                    <!--                                        <form action="/upload" class="dropzone dropzone-custom needsclick add-professors dz-clickable" id="demo1-upload" novalidate="novalidate">-->

                                    <form action="<?=base_url()."form/upload_special_self_choices"?>"method="post" id = "select_choice_form"  enctype="multipart/form-data">

                                        <div class="row">
                                            <div class="col-md-9">
                                                <table class='table' >
                                                    <thead>
                                                    <th>Choice No</th>
                                                    <th>Program</th>
                                                    </thead>
                                                    <tbody id="table-body-courceDetail" >
                                                    <?php
                                                    $show_lat_form = false;

                                                    for($i= 0 ;$i<count($CHOOSEN_PROGRAM_LIST);$i++){
                                                        for($j=0;$j<count($CHOOSEN_PROGRAM_LIST)-1-$i;$j++){
                                                            if($CHOOSEN_PROGRAM_LIST[$j]['CHOICE_NO']>$CHOOSEN_PROGRAM_LIST[$j+1]['CHOICE_NO']){
                                                                $temp = $CHOOSEN_PROGRAM_LIST[$j+1];
                                                                $CHOOSEN_PROGRAM_LIST[$j+1] = $CHOOSEN_PROGRAM_LIST[$j];
                                                                $CHOOSEN_PROGRAM_LIST[$j] = $temp;
                                                            }
                                                        }
                                                    }

                                                    foreach ($CHOOSEN_PROGRAM_LIST as $CHOOSEN_PROGRAM ){

                                                        $PROG_LIST_ID =  $CHOOSEN_PROGRAM['PROG_LIST_ID'];
                                                        $CHOICE_NO = $CHOOSEN_PROGRAM['CHOICE_NO'];
                                                        foreach ($PROGRAM_LIST as $PROGRAM) {
                                                            if($PROGRAM['PROG_LIST_ID']==$PROG_LIST_ID){
                                                                $PROG_NAME = $PROGRAM['PROGRAM_TITLE'];
                                                            }
                                                        }
                                                        if(LLB_PROG_LIST_ID==$PROG_LIST_ID){
                                                            $show_lat_form = true;
                                                            $min= 1;
                                                        }
                                                        $up_down_button = "<td><button class='btn btn-warning' type='button' onclick=\"up('$CHOICE_NO')\"><i class='fa fa-sort-up'></i>Up</button> <button class='btn btn-warning' type='button' onclick=\"down('$CHOICE_NO')\"><i class='fa fa-sort-down'></i>Down</button></td>";
                                                        $row    ="<tr id='$PROG_LIST_ID'><td>$CHOICE_NO</td><input type='hidden' name='minor_subject_array[]' value='$PROG_LIST_ID'><td>$PROG_NAME</td>$up_down_button<td><input type='button' onclick=\"deleteDataInTable('$PROG_LIST_ID','$PROG_NAME')\" value='Delete' class='btn btn-sm btn-danger' ></td></tr>";
                                                        echo $row;
                                                    }
                                                    ?>
                                                    </tbody>



                                                </table>
                                            </div>
                                            <?php
                                            $RESULT_IMAGE= $TEST_SCORE =$TEST_DATE=$TOKEN_NO="";
                                            if($show_lat_form){
                                                $TOKEN_NO = $lat_info['TOKEN_NO'];
                                                $TEST_SCORE = $lat_info['TEST_SCORE'];
                                                $TEST_DATE = getDateForView($lat_info['TEST_DATE']);
                                                $RESULT_IMAGE = $lat_info['RESULT_IMAGE'];
                                            }
                                            ?>
                                            <div id="llb_infor_form_view">
                                                <div class="col-md-3">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1" class="bmd-label-floating">HEC Ticket No
                                                                    <span class="text-danger">*</span></label>
                                                                <input value="<?=$TOKEN_NO?>" type="text" id="TOKEN_NO" class="form-control " placeholder="Ticket No" name="TOKEN_NO" >


                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1" class="bmd-label-floating">HEC LAT Test Score
                                                                    <span class="text-danger">*</span></label>
                                                                <input value="<?=$TEST_SCORE?>" type="text" id="TEST_SCORE" class="form-control allow-number" placeholder="TEST SCORE" name="TEST_SCORE" >


                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1" class="bmd-label-floating">HEC LAT Test Date
                                                                    <span class="text-danger">* &nbsp;</span></label>
                                                                <div class="form-group data-custon-pick" id="data_2">
                                                                    <div class="input-group date">
                                                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                                        <input  value="<?=$TEST_DATE?>" type="text" id="TEST_DATE"  name="TEST_DATE" class="form-control" value="" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div style="margin-top:35px">

                                                                <label for="exampleInput1" class="bmd-label-floating">HEC LAT Result Card
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <br>
                                                                <?php
                                                                $image_path = "";

                                                                $image_path_default =base_url()."dash_assets/img/avatar/docavtar.png";
                                                                if($RESULT_IMAGE){
                                                                    $image_path_default=base_url().EXTRA_IMAGE_PATH.$RESULT_IMAGE;
                                                                    $image_path=$RESULT_IMAGE;
                                                                }
                                                                ?>
                                                                <img src="<?php echo $image_path_default; ?>" alt="Result Card" class="img-table-certificate" id="result-card-image-view" onclick="setImage()" width="150px" height="150px" name="result-card-image-view" >
                                                                <input type="file" name="result_card_image" id="result_card_image"                       onchange="changeImage(this,'result_card_image','result-card-image-view',500)" accept=".jpg,.png,.jpeg">
                                                                <input type="text" name="result_card_image1" id="result_card_image1" value="<?php echo $image_path; ?>" hidden>
                                                                <span class="text-danger">Make Sure Image must be clear and Image size should be less than 500KB</span>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">

                                                  <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="fa fa-save"></i> Save </button>>
                                                  </span>
                                            </div>
                                            <div class="col-md-6">

                                                  <span class="input-group-btn">
                                                    <button type="button" class="btn btn-success waves-effect waves-light" onclick="get_next()" ><i class="fa fa-next"></i> Save & Next </button>>
                                                  </span>
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
    <?php
    if(!$show_lat_form){
        echo "$('#llb_infor_form_view').hide();";
    }
    ?>


    var llb_id =<?=LLB_PROG_LIST_ID?>;
    var max_list = <?=$max?>;
    var min_list = <?=$min?>;
    function validateProgramSelection(){

        var len = ($("#table-body-courceDetail tr").length);
        if(len<=max_list&&min_list<=len) {
            return true;
        }else{
            alertMsg("Warning","You Must select minimum "+min_list+" Program...!")
            return false;
        }
    }
    $("#add_program").click(function(){

        var id = $("#PROGRAM_LIST_ID").val();
        if(id==null ||id<=0){
            return;
        }
        //alert(id);
        var txt = $("#PROGRAM_LIST_ID option:selected").text();
        addDataInTable(id,txt);
    });



    function addDataInTable(id,txt){
        // minor_option_id_
        if(llb_id==id){
            min_list = 1;
            $('#llb_infor_form_view').show();
        }

        var len = ($("#table-body-courceDetail tr").length)+1;
        if(len<=max_list) {
            let up_down_button = "<td><button class='btn btn-warning' type='button' onclick=up("+ len +")><i class='fa fa-sort-up'></i>Up</button> <button class='btn btn-warning' type='button' onclick=down("+ len +")><i class='fa fa-sort-down'></i>Down</button></td>";
            $("#table-body-courceDetail").append("<tr id='" + id + "'><td>" + len + "</td><input type='hidden' name='minor_subject_array[]' value='" + id + "'><td>" + txt + "</td>"+up_down_button+"<td><input type='button' onclick=\"deleteDataInTable('" + id + "','" + txt + "');\" value='Delete' class='btn btn-sm btn-danger' ></td></tr>");
            $("#PROGRAM_LIST_ID option[value='" + id + "']").remove();
            $("#PROGRAM_LIST_ID").siblings("[value='" + id + "']").remove();
        }else{
            alertMsg("Warning","You can select maximum "+max_list+" subject...!")
        }

    }

    function deleteDataInTable(elementNo,txt){
        //alert("aaa");
        //$(elementNo).parent().parent().remove();
        if(elementNo==llb_id){
            min_list = <?=$min?>;
            $('#llb_infor_form_view').hide();
        }
        $("#table-body-courceDetail tr[id='"+elementNo+"']").remove();
        $("#PROGRAM_LIST_ID").append("<option value=\""+elementNo+"\" >"+txt+"</option>");
        $("#table-body-courceDetail tr").each(function(index,elem){
            var no = (index +1);
            var d = $(elem).children().get(0);
            $(d).html(no);

        });
        //   sort();
    }
    function up(thisObj){
        thisObj--;
        if(thisObj==0){
            return;
        }
        var list  = $("#table-body-courceDetail").children();
        $("#table-body-courceDetail").html('');

        var pre = thisObj-1;
        for(let i=0;i<list.length;i++){


            let number = i+1;

            let up_button_element ="<button type='button'  class='btn btn-warning' onclick=up("+ number +")><i class='fa fa-sort-up'></i>Up</button>";
            let down_button_element ="<button type='button' class='btn btn-warning' onclick=down("+ number +")><i class='fa fa-sort-down'></i>Down</button>";

            let button_element;
            if(number===1){
                button_element = up_button_element+" "+down_button_element;
            }else if(number===list.length){
                button_element = up_button_element+" "+down_button_element;
            }else{
                button_element = up_button_element+" "+down_button_element;
            }
            if(pre===i){
                list[thisObj].cells[0].innerText=number;
                list[thisObj].cells[2].innerHTML = button_element;
                $("#table-body-courceDetail").append(list[thisObj]);

            }else if(thisObj===i){
                list[pre].cells[0].innerText=number;
                list[pre].cells[2].innerHTML = button_element;
                $("#table-body-courceDetail").append(list[pre]);
            }else{
                list[i].cells[0].innerText=number;
                list[i].cells[2].innerHTML = button_element;
                $("#table-body-courceDetail").append(list[i]);
            }

        }

    }
    function down(thisObj){

        thisObj--;
        var list  = $("#table-body-courceDetail").children();


        var pre = thisObj+1;
        console.log(pre);
        console.log(list.length);
        if(pre==list.length){
            return;
        }
        $("#table-body-courceDetail").html('');
        for(let i=0;i<list.length;i++){

            let number = i+1;

            let up_button_element ="<button type='button' class='btn btn-warning' onclick=up("+ number +")><i class='fa fa-sort-up'></i>Up</button>";
            let down_button_element ="<button type='button' class='btn btn-warning' onclick=down("+ number +")><i class='fa fa-sort-down'></i>Down</button>";
            let button_element;
            if(number===1){
                button_element = up_button_element+" "+down_button_element;
            }else if(number===list.length){
                button_element = up_button_element+" "+down_button_element;
            }else{
                button_element = up_button_element+" "+down_button_element;
            }

            if(pre===i){
                list[thisObj].cells[0].innerText=number;
                list[thisObj].cells[2].innerHTML = button_element;
                $("#table-body-courceDetail").append(list[thisObj]);

            }else if(thisObj===i){
                list[pre].cells[0].innerText=number;
                list[pre].cells[2].innerHTML = button_element;
                $("#table-body-courceDetail").append(list[pre]);
            }else{
                list[i].cells[0].innerText=number;
                list[i].cells[2].innerHTML = button_element;
                $("#table-body-courceDetail").append(list[i]);
            }

        }

    }
    var is_next = false;

    $('#select_choice_form').submit(function (event) {
        event.preventDefault();
        if(!validateProgramSelection()){
            return;

        }
        var form = $('#select_choice_form')[0];
        var data = new FormData(form);
        $('.preloader').fadeIn(700);
        jQuery.ajax({
            url: "<?=base_url()?>form/upload_special_self_choices",
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
                        $("#pre_text").html("<br><br><h3>Uploading "+percentComplete+"%</h3>");
                        console.log(percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function (data, status) {
                $('.preloader').fadeOut(700);
                // $('input[name="csrf_form_token"]').val(data.csrfHash);
                $('#alert_msg_for_ajax_call').html("");
                alertMsg("Success",data.MESSAGE);
                //console.log(is_next);
                if(is_next==true){
                    window.location.href = '<?=base_url('form/check_final_validation/final_lock')?>';
                }
                is_next = false;

            },
            beforeSend:function (data, status) {


                $('#alert_msg_for_ajax_call').html("LOADING...!");
            },
            error:function (data, status) {
                is_next = false;
                var value = data.responseJSON;
                alertMsg("Error",value.MESSAGE);
                // $('input[name="csrf_form_token"]').val(value.csrfHash);
                $('#alert_msg_for_ajax_call').html(value.MESSAGE);
                $('.preloader').fadeOut(700);

            },
        });
    });
    function get_next() {
        is_next  = true;
        $('#select_choice_form').submit();

    }

</script>
