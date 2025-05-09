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
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="review-content-section">
                        <div id="dropzone1" class="pro-ad">
                            <div class="card">
                                <div class='card-header'>
                                    <center><h3>Please Give Your LAT Result</h3></center>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $hidden = array("APPLICATION_ID"=>$APPLICATION_ID,"DISCIPLINE_ID"=>$DISCIPLINE_ID);

                                    ?>
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <strong>
                                                  <span class='text-danger '><em>Provide LAT information and upload LAT score result card upto 09-01-2021 @ 06:00PM</em></span>
                                            </strong>
                                            <br>
                                             <strong>
                                                  <a href="https://www.youtube.com/watch?v=t6lbXpI6C5M&ab_channel=TutorialsPoint"><span class='text-danger '><em>Click here to watch tutorial</em></span></a>
                                            </strong>
                                        </div>
                                    </div>
                                    <hr>

                                    <!--                                        <form action="/upload" class="dropzone dropzone-custom needsclick add-professors dz-clickable" id="demo1-upload" novalidate="novalidate">-->

                                    <form action="<?=base_url()."form/upload_lat_handler"?>"method="post" id = "select_choice_form"  onsubmit="return check_final_validation()" enctype="multipart/form-data">

                                        <div class="row">

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
                                                //$up_down_button = "<td><button class='btn btn-warning' type='button' onclick=\"up('$CHOICE_NO')\"><i class='fa fa-sort-up'></i>Up</button> <button class='btn btn-warning' type='button' onclick=\"down('$CHOICE_NO')\"><i class='fa fa-sort-down'></i>Down</button></td>";
                                                $row    ="<tr id='$PROG_LIST_ID'><td>$CHOICE_NO</td><input type='hidden' name='minor_subject_array[]' value='$PROG_LIST_ID'><td>$PROG_NAME</td></tr>";
                                               // echo $row;
                                            }
                                            $RESULT_IMAGE= $TEST_SCORE =$TEST_DATE=$TOKEN_NO="";
                                            if($show_lat_form){
                                                $TOKEN_NO = $lat_info['TOKEN_NO'];
                                                $TEST_SCORE = $lat_info['TEST_SCORE'];
                                                $TEST_DATE = getDateForView($lat_info['TEST_DATE']);
                                                $RESULT_IMAGE = $lat_info['RESULT_IMAGE'];
                                            }
                                            ?>
                                            <div id="llb_infor_form_view">
                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="exampleInput1" class="bmd-label-floating">HEC Roll No
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
                                        <br><br>
                                        <div class="row">
                                            <div class="col-md-6">

                                                  <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="fa fa-save"></i> Save </button>>
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
//    if(!$show_lat_form){
//        echo "$('#llb_infor_form_view').hide();";
//    }
    ?>


    var llb_id =<?=LLB_PROG_LIST_ID?>;
    var max_list = <?=$max?>;
    var min_list = <?=$min?>;
    function validateProgramSelection(){
       let  TOKEN_NO = $('#TOKEN_NO').val();
        let TEST_SCORE = $('#TEST_SCORE').val();
       let TEST_DATE =  $('#TEST_DATE').val();
       if(TEST_SCORE<50){
           alertMsg('ERROR','Test score must be greater then equal to 50');
           return false;
       }
      return true;
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
            $("#table-body-courceDetail").append("<tr id='" + id + "'><td>" + len + "</td><input type='hidden' name='minor_subject_array[]' value='" + id + "'><td>" + txt + "</td><td></td></tr>");
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
    function check_final_validation(){
        if(!validateProgramSelection()){
            return false;
        }
        return true;
    }

    function get_next() {
        is_next  = true;
        $('#select_choice_form').submit();

    }

</script>
