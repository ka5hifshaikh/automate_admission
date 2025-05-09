<div id = "min-height" class="container-fluid" style="padding:30px">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="review-content-section">
                        <div id="dropzone1" class="pro-ad">
                            <div class="card">
                                 <div class="card-header">
                                     <center><h3>Choose your Graduation Elective / Minor Subjects</h3></center>
                                     </div>
                                <div class="card-body">
                                    <?php
                                    $hidden = array("APPLICATION_ID"=>$APPLICATION_ID,"DISCIPLINE_ID"=>$DISCIPLINE_ID);

                                    ?>
                                    <div class="row">
                                          <div class="col-lg-5 col-md-5 ">
                                            <div class="form-group">
                                                <label for="exampleInput1" class="bmd-label-floating"> Choose Three Elective / Minor Subjects From The Following Menu
                                                    <span class="text-danger">*</span></label>
                                                <select  class="js-example-basic-single form-control mb-3" name="MINOR_MAPPING_ID" id="MINOR_MAPPING_ID">
                                                    <option value="0">--Choose--</option>
                                                    <?php
                                                    foreach ($minors as $minor) {
                                                        $bool = true;
                                                        foreach ($applicantsMinors as $k =>$applicantsMinor) {
                                                            if($applicantsMinor['MINOR_MAPPING_ID']==$minor['MINOR_MAPPING_ID']){
                                                                $bool = false;
                                                                break;
                                                            }
                                                        }
                                                        if($bool){
                                                            echo "<option value='{$minor['MINOR_MAPPING_ID']}'  >{$minor['SUBJECT_TITLE']} </option>";
                                                        }

                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                                <button  class="btn btn-success " id="add_minor">ADD</button>

                                        </div>

                                    </div>

                                    <!--                                        <form action="/upload" class="dropzone dropzone-custom needsclick add-professors dz-clickable" id="demo1-upload" novalidate="novalidate">-->

                                    <form action="<?=base_url('form/upload_minor_subjects')?>" method="post" id="minor_select_form " onsubmit="return validateMinorSelection()">
                                        <input type="hidden" name="APPLICATION_ID" value="<?=$APPLICATION_ID?>">
                                        <input type="hidden" name="DISCIPLINE_ID" value="<?=$DISCIPLINE_ID?>">

<!--                                    <div class="row">-->
<!--                                        <div class="col-lg-4">-->
<!---->
<!--                                        </div>-->
<!--                                        <div class="col-lg-4">-->
<!--                                            <div class="payment-adress">-->
<!--                                                <button type="submit" class="btn btn-primary btn-lg waves-effect waves-light">Save</button>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    <input type="hidden" name="IS_NEXT" value="0" id="IS_NEXT">
                                    <table class='table'>
                                        <thead>
                                        <th>S.No</th>
                                        <th>Subject</th>
                                        </thead>
                                        <tbody id="table-body-courceDetail" >
                                        <?php
                                        foreach ($applicantsMinors as $k =>$applicantsMinor) {
                                            ?>
                                            <tr id="<?=$applicantsMinor['MINOR_MAPPING_ID']?>">
                                                <td><?=$k+1?></td>
                                                <input type="hidden" name="minor_subject_array[]" value="<?=$applicantsMinor['MINOR_MAPPING_ID']?>">
                                                <td><?=$applicantsMinor['SUBJECT_TITLE']?></td>
                                                <td><input type="button" onclick="deleteDataInTable('<?=$applicantsMinor['MINOR_MAPPING_ID']?>','<?=$applicantsMinor['SUBJECT_TITLE']?>');"
                                                           value="Delete" class="btn btn-sm btn-danger"></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>

                                <tr><td >
                                         <span class="input-group-btn">
        <button  class="btn btn-primary waves-effect waves-light" id="SAVE_BTN"><i class="fa fa-save"></i> Save </button>>
                                                                  </span>

                                    </td>
                                    <td >
                                         <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary btn-lg" onclick="set_next()" >Next</button>
                                         </span>

                                    </td></tr>

                                </table>

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
    function set_next(){
        $('#IS_NEXT').val(1);
        $('#SAVE_BTN').click();
    }
    <?php
    if($PROGRAM_TYPE_ID==1){
        $value = MINOR_SELECT_FOR_BACHELOR;
    }else if($PROGRAM_TYPE_ID==2){
        $value = MINOR_SELECT_FOR_MASTER;
    }
    ?>

    var max_list = <?=$value?>;
    function validateMinorSelection(){

        var len = ($("#table-body-courceDetail tr").length);

            if(len==max_list||max_list==-1) {
                return true;
            }else{
                alertMsg("Warning","You Must select  "+max_list+" subject...!")
                return false;
            }


    }

    $("#add_minor").click(function(){

        var id = $("#MINOR_MAPPING_ID").val();
        if(id==null ||id<=0){
            return;
        }
        //alert(id);
        var txt = $("#MINOR_MAPPING_ID option:selected").text();
        addDataInTable(id,txt);
    });

    function addDataInTable(id,txt){
       // minor_option_id_
        var len = ($("#table-body-courceDetail tr").length)+1;
        if(len<=max_list||max_list==-1) {
            $("#table-body-courceDetail").append("<tr id='" + id + "'><td>" + len + "</td><input type='hidden' name='minor_subject_array[]' value='" + id + "'><td>" + txt + "</td><td><input type='button' onclick=\"deleteDataInTable('" + id + "','" + txt + "');\" value='Delete' class='btn btn-sm btn-danger' ></td></tr>");
            $("#MINOR_MAPPING_ID option[value='" + id + "']").remove();
            $("#MINOR_MAPPING_ID").siblings("[value='" + id + "']").remove();
        }else{
            alertMsg("Warning","You can select maximum "+max_list+" subject...!")
        }

    }
    function deleteDataInTable(elementNo,txt){
        //alert("aaa");
        //$(elementNo).parent().parent().remove();

        $("#table-body-courceDetail tr[id='"+elementNo+"']").remove();
        $("#MINOR_MAPPING_ID").append("<option value=\""+elementNo+"\" >"+txt+"</option>");
        $("#table-body-courceDetail tr").each(function(index,elem){
            var no = (index +1);
            var d = $(elem).children().get(0);
            $(d).html(no);

        });
     //   sort();
    }


</script>
