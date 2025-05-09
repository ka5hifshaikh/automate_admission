<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 7/11/2020
 * Time: 4:22 PM
 */
$readonly ="";
?>


<script >
    <?php
    $res = getcsrf($this);
    ?>
    var csrfName="<?=$res['csrfName']?>";
    var csrfHash="<?=$res['csrfHash']?>";
    var is_next = false;
    function callAjax(url,set_id,msg_id="alert_msg_for_ajax_call"){
        jQuery.ajax({
            url:url ,
            async:false,
            success: function (data, status) {
                $('#'+msg_id).html("");
                $('#'+set_id).html(data);
            },
            beforeSend:function (data, status) {
                $('#'+msg_id).html("LOADING...!");
            },
            error:function (data, status) {
                alertMsg("Error",data.responseText);
                $('#'+msg_id).html("Something went worng..!");
            },
        });
    }

</script>
<div id = "min-height" class="container-fluid" style="padding:30px">
     <br>
                 <?php
                $data['application']=$application;
                $data['users_reg']=$user;
                $data['qualifications']=$qualifications;
                $data['category']=$category;
                $data['program_choice']=$program_choice;
                show_progress_status($data);
                ?>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="product-payment-inner-st">
                <ul id="myTabedu1" class="tab-review-design">
                    <li class="active"><a  id="basic_information_tab" href="#basic_information">Basic Information</a></li>
                      <li class=""><a id="documents_tab" href="#documents"> Additional Documents</a></li>
                    <!--<li class=""><a id="education_tab" href="#education"> Education Information</a></li>-->
<!--					<li class=""><a id="experiances_tab" href="#experiances"> Experience</a></li>-->
                  
                </ul>

                <div id="myTabContent" class="tab-content custom-product-edit">
                    <div class="product-tab-list tab-pane fade active in" id="basic_information">
                    <?php   require_once "profile_section/basic_information.php";?>
                    </div>
                    <div class="product-tab-list tab-pane fade" id="documents">
                        <?php   require_once "profile_section/document_form.php";?>
                    </div>
                    
                    <!--<div class="product-tab-list tab-pane fade" id="education">-->
                       <?php   //require_once "profile_section/qualification_information.php";?>
                    <!--</div>-->
                    
<!--                    <div class="product-tab-list tab-pane fade" id="experiances">-->
<!--                        --><?php //  require_once "profile_section/experiances.php";?>
<!--                    </div>-->
                </div>
                <div id="alert_msg_for_ajax_call"></div>
                </div>
            </div>
        </div>
</div>

<script>
<?php
        if($user['STATUS']=='C'){
            ?>
$('input').attr({"disabled":true});
$('select').attr({"disabled":true});
$('textarea').attr({"disabled":true});
        <?php
        }
?>
	$(document).ready(function () {

		function profile_guideline() {

			var msg="<ol style='font-size: 11pt' class='list-group'>";
			msg+="<li class='list-group-item list-group-item-warning'>Fill your profile carefully once submitted can't be editable.</li>";
			msg+="<li class='list-group-item'><span class='text-danger'>*</span> Marked fields are required, do not leave them blank.</li>";
			msg+="<li class='list-group-item'>Your profile image must be passport size 120 x 159 pixels dimensions approximately and it must be in white background and must not be greater than 100 KB.</li>";
			msg+="<li class='list-group-item'>Document must not be greater than 500 KB.</li>";
// 			msg+="<li class='list-group-item'><a href=''> <i class='fa fa-file-video-o'></i> Please watch this tutorial before filling / updating profile.</a></li>";
// 			msg+="<li class='list-group-item'><a href=''> <i class='fa fa-file-video-o'></i> For more tutorials click here.</a></li>";
			msg+="<li class='list-group-item'><i class='fa fa-connectdevelop'></i> If you have any query please email Directorate of Admissions Help Desk at <b><em>dir.adms@umpk.edu.pk</em></b>, you will get reply within 24 to 48 hrs (working days) or Contact on given numbers : <b>0229213166 - 0229213199 (during office Hours 9:00am to 5:00pm)</b></li>";
			msg+="</ol>";

			alertMsg('Form Filling Guidelines',msg);
		}
		<?php
        if(isset($_SESSION['ALERT_MSG'])){
            
        }else{
            echo "profile_guideline();";
        }
		?>

	});
    function callAjax(url,set_id,msg_id="alert_msg_for_ajax_call"){
        jQuery.ajax({
            url:url ,
            async:false,
            success: function (data, status) {
                $('#'+msg_id).html("");
                $('#'+set_id).html(data);
            },
            beforeSend:function (data, status) {
                $('#'+msg_id).html("LOADING...!");
            },
            error:function (data, status) {
                alertMsg("Error",data.responseText);
                $('#'+msg_id).html("Something went worng..!");
            },
        });
    }
    $('#add_qulification').click(function (event) {
        event.preventDefault();
        let program_type_id = <?=$application['PROGRAM_TYPE_ID']?$application['PROGRAM_TYPE_ID']:0 ?>;

        callAjax("<?=base_url()?>Candidate/apiGetAddQualificationForm?program_type_id="+program_type_id,"qulification_form_view");
        $('.js-example-basic-single').select2();
        $('.select2').attr('style','width:100%');
        $('.disab').hide();


    });

    $( '.img-table-certificate' ).click(function() {
        alertImage('Image',$(this).attr('src'));
    });

    function getQualification(){
        callAjax("<?=base_url()?>Candidate/apiGetQualificationList","qulification_table_view");
    }
    function editQualification(id){
        let program_type_id = <?=$application['PROGRAM_TYPE_ID']?$application['PROGRAM_TYPE_ID']:0 ?>;
        callAjax("<?=base_url()?>Candidate/apiGetEditQualificationForm?qualification_id="+id+"&program_type_id="+program_type_id,'qulification_form_view');
        $('.js-example-basic-single').select2();
        $('.select2').attr('style','width:100%');
        $('.disab').hide();
    }
    let name;
    // var
    function deleteQulification(id){
        if(confirm("Are You Sure?\nDo You want to delete your qualification..!")){
            $('.preloader').fadeIn(700);
            var data = new FormData();
            data.append("action", 'delete_qualification');
            data.append("QUAL_ID", id);
            <?php
                $res = getcsrf($this);
            ;
            $res['csrfHash'];
            ?>
            data.append("<?=$res['csrfName']?>", <?=$res['csrfName']?>);
            jQuery.ajax({
                url: "<?=base_url()?>Candidate/apiDeleteQualification",
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                data: data,
                success: function (data, status) {

                    $('.preloader').fadeOut(700);
                    $('#qul_form_msg').html("");
                    alertMsg("Success",data.MESSAGE);
                    getQualification();

                },
                beforeSend:function (data, status) {


                    $('#qul_form_msg').html("Loading...!");



                },
                error:function (data, status) {
                    var value = data.responseJSON;

                    alertMsg("Error",value.MESSAGE);
                    $('#qul_form_msg').html(value.MESSAGE);
                    $('.preloader').fadeOut(700);
                    getQualification();


                },
            });
        }
    }
		
	getQualification();

    function next_tab(id) {
        if(id!="education_tab"){
            <?php
            if($user['STATUS']!='C'){
                ?>
            is_next = true;
            $('#base_profile_form').submit();
            <?php
            }else{
                ?>
            $('#'+id).click();
            <?php
            }
            ?>


        }else{
            $('#'+id).click();
        }

    }
    function check_validtion_of_data(){
        window.location.href = "<?=base_url()?>form/check_validation";
    }
</script>

