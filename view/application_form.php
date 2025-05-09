<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PDF extends FPDF {
    public $array_x=array();
    public $array_y=array();
    public $array_w=array();
    public $array_h=array();
    public $count=0;

    function customCell($w,$h,$txt,$bdr,$alin,$ln=0,$fill=false){
        //$this->SetFont('Time','','',0)
        $prey=$this->GetY();
        $prex=$this->GetX();
        $this->MultiCell($w,$h,$txt,0,$alin,$fill);
        $currnty=$this->GetY();
        if($ln== 0){
            $this->SetXY($prex+$w,$prey);
        }
        if($bdr==1){
            $this->Rect($prex,$prey,$w,$currnty-$prey);
        }


    }

    function TableCell($w,$h,$txt,$bdr,$alin,$ln=0,$fill=false){
        //$this->SetFont('Time','','',0)
        $prey=$this->GetY();
        $prex=$this->GetX();
        $this->MultiCell($w,$h,$txt,0,$alin,$fill);
        $currnty=$this->GetY();
        $this->array_x[$this->count]= $prex;
        $this->array_y[$this->count]= $prey;
        $this->array_w[$this->count]= $w;
        $this->array_h[$this->count]= $currnty;
        $this->count++;
        if($ln== 0){

            $this->SetXY($prex+$w,$prey);
        }else{
            if(count($this->array_h)>0)
                $max_h = max($this->array_h);
            for($i=0;$i<$this->count ;$i++){
                $p_x =  $this->array_x[$i];

                $p_y = $this->array_y[$i];
                $p_w = $this->array_w[$i];
                $this->Rect($p_x,$p_y,$p_w,$max_h-$prey);
            }
            $this->SetY($max_h);
            $this->count=0;

        }
        if($bdr==1){
            //$this->Rect($prex,$prey,$w,$currnty-$prey);
        }
    }

    function Header()
    {
        $this->SetFont('Arial','B',15);

    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('P','mm','A4');

$pdf->AddPage();
$first_column_width = 60;
$sec_column_width = 130;
$row_height = 5;
$app_camp="ALLAMA I.I. KAZI CAMPUS JAMSHORO";
$app_cat="";
$sp_ch="";
$is_evening_choice = $is_specila_self = false;
foreach ($user_fulldata['application_category'] as $applicant_cat){
    $app_cat.= $sp_ch.$applicant_cat['FORM_CATEGORY_NAME'];
    if($applicant_cat['FORM_CATEGORY_NAME']=="SELF FINANCE (EVENING)"){
        $is_evening_choice = true;
    }
    $sp_ch = ", ";
}

// prePrint($application);
// prePrint($user_fulldata);
// exit();

$qualifications = $user_fulldata['qualifications'];
$applicants_minors = $user_fulldata['applicants_minors'];
$user_id=$user_fulldata['users_reg']['USER_ID'];
$name = strtoupper($user_fulldata['users_reg']['FIRST_NAME']);
$fname = strtoupper($user_fulldata['users_reg']['FNAME']);
$last_name =strtoupper( $user_fulldata['users_reg']['LAST_NAME']);
$GENDER = strtoupper($user_fulldata['users_reg']['GENDER'])=='M'?"MALE":"FEMALE";
$MOBILE_NO = ($user_fulldata['users_reg']['MOBILE_CODE']=="0092"?"0":$user_fulldata['users_reg']['MOBILE_CODE'])."".$user_fulldata['users_reg']['MOBILE_NO'];
$CNIC_NO =strtoupper( $user_fulldata['users_reg']['CNIC_NO']);
$DATE_OF_BIRTH =getDateCustomeView($user_fulldata['users_reg']['DATE_OF_BIRTH'],'d-M-Y');
$BLOOD_GROUP =strtoupper( $user_fulldata['users_reg']['BLOOD_GROUP']);
$DISTRICT_NAME =strtoupper( $user_fulldata['users_reg']['DISTRICT_NAME']);
$AREA = strtoupper($user_fulldata['users_reg']['U_R'])=='R'?"RURAL":"URBAN";
$PHONE = $user_fulldata['users_reg']['PHONE']==0?"":$user_fulldata['users_reg']['PHONE'];
$EMAIL =strtolower( $user_fulldata['users_reg']['EMAIL']);
$HOME_ADDRESS =strtoupper( $user_fulldata['users_reg']['HOME_ADDRESS']);
$PERMANENT_ADDRESS =strtoupper( $user_fulldata['users_reg']['PERMANENT_ADDRESS']);
$PROVINCE_NAME =strtoupper( $user_fulldata['users_reg']['PROVINCE_NAME']);
$COUNTRY_NAME =strtoupper( $user_fulldata['users_reg']['COUNTRY_NAME']);

$BLOOD_GROUP =strtoupper( $user_fulldata['users_reg']['BLOOD_GROUP']);
$RELIGION =strtoupper( $user_fulldata['users_reg']['RELIGION']);


$GURDIAN_FIRST_NAME =strtoupper( $user_fulldata['guardian']['FIRST_NAME']);
$G_MOBILE =  ($user_fulldata['guardian']['MOBILE_CODE']=="0092"?"0":$user_fulldata['guardian']['MOBILE_CODE'])."".$user_fulldata['guardian']['MOBILE_NO'];
$challan_no = str_pad($application['FORM_CHALLAN_ID'], 5, '0', STR_PAD_LEFT);

$pdf->SetFillColor(0,0,0);

$current_date = date("d-m-Y");
$CHALLAN_DATE = getDateCustomeView($application['CHALLAN_DATE'],'d-m-Y');

$data = $user_id. "~"  . $application['APPLICATION_ID']. "~". $challan_no . "~".$CNIC_NO."~" . $CHALLAN_DATE . "~" . $current_date;
//$result=str_pad($data, 10, "0", STR_PAD_LEFT);

$profile_image = PROFILE_IMAGE_CHECK_PATH.$user_fulldata['users_reg']['PROFILE_IMAGE'];
// prePrint($profile_image);
// exit();
if(!is_dir('../eportal_resource/qr_images')){
    mkdir('../eportal_resource/qr_images');
}
QRcode::png("$data","../eportal_resource/qr_images/".$application['APPLICATION_ID'].".png", 'QR_ECLEVEL_L', 3, 2);
$path="../eportal_resource/qr_images/".$application['APPLICATION_ID'].".png";
$pdf->Image($path,175,5,16,16);
$pdf->Image('images/umpk/logo.png',10,4,26,26);
$pdf->Image($profile_image,175,26,23,28);
$pdf->SetFont("Arial",'B',20);
$pdf->Cell(0,$row_height,UNIVERSITY_NAME,0,1,'C',false);
$pdf->Ln(1);
$pdf->SetFont("Times",'',12);
$pdf->Cell(0,$row_height,"ONLINE ADMISSION FORM - {$application['YEAR']}",0,1,'C',false);
$pdf->SetFont("Times",'',13);
$pdf->Ln(2);
$pdf->Cell(0,$row_height,"({$application['PROGRAM_TITLE']} DEGREE PROGRAM)",0,1,'C',false);



$pdf->Ln(4);
$pdf->Cell(190,$row_height,"Candidate's Application No. {$application['APPLICATION_ID']}",0,1);


$pdf->SetFont("Times",'B',12);
$pdf->TableCell($first_column_width,$row_height,"Applied Campus ",1,'L',0);
$pdf->TableCell($sec_column_width-25,$row_height,$application['NAME'],1,'L',1);


$pdf->TableCell($first_column_width,$row_height,"Applied Category(ies)",1,'L',0);
$pdf->SetFont("Times",'B',11);
$pdf->TableCell($sec_column_width-25,$row_height,$app_cat,1,'L',1);

$pdf->Ln(5);
$pdf->SetFont("Times",'B',11);
$pdf->customCell($first_column_width,$row_height,"Personal Information",0,'L',1);

$pdf->SetFont("Times",'',10);

$pdf->TableCell($first_column_width,$row_height,"Name",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$name,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Father's Name",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$fname,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Surname",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$last_name,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Gender",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$GENDER,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"CNIC No.",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$CNIC_NO,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Date of Birth ",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$DATE_OF_BIRTH,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Blood Group",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$BLOOD_GROUP,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Religion",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$RELIGION,1,'L',1);

// $pdf->TableCell($first_column_width,$row_height,"Nationality",1,'L',0);
// $pdf->TableCell($sec_column_width,$row_height,$COUNTRY_NAME,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Province",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$PROVINCE_NAME.", ".$COUNTRY_NAME,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"District of Domicile",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$DISTRICT_NAME." (".$AREA.")",1,'L',1);

// $pdf->TableCell($first_column_width,$row_height,"Telephone No. (Landline)",1,'L',0);
// $pdf->TableCell($sec_column_width,$row_height,$PHONE,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Mobile No",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$MOBILE_NO,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Email Address ",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$EMAIL,1,'L',1);

$pdf->TableCell($first_column_width,$row_height-1,"Home Address",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height-1,$HOME_ADDRESS,1,'L',1);

$pdf->TableCell($first_column_width,$row_height-1,"Permanent Address",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height-1,$PERMANENT_ADDRESS,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Gurdian's Name",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$GURDIAN_FIRST_NAME,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Gurdian's Mobile No",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$G_MOBILE,1,'L',1);


$pdf->Ln(5);
$pdf->SetFont("Times",'B',11);
$pdf->customCell($first_column_width,$row_height,"Academic Record",0,'L',1);

$pdf->SetFont("Times",'B',10);

$pdf->TableCell($first_column_width,$row_height-1,"Examination Passed ",1,'C',0);
$pdf->TableCell(30,$row_height-1,"Group",1,'C',0);
$pdf->TableCell(18,$row_height-1,"Marks Obtained",1,'C',0);
$pdf->TableCell(15,$row_height-1,"Total Marks",1,'C',0);
$pdf->TableCell(15,$row_height-1,"Year",1,'C',0);
$pdf->TableCell(17,$row_height-1,"Seat No.",1,'C',0);
$pdf->TableCell(35,$row_height-1,"Name of Board/University",1,'C',1);

$pdf->SetFont("Times",'',10);
$check = 0;
$count_qual =  count($qualifications)-1;
if($application['PROGRAM_TYPE_ID']==2){
   if($qualifications[0]['DEGREE_ID']==10){
       $last = $qualifications[1]['DEGREE_ID'];
   }else{
       $last = $qualifications[0]['DEGREE_ID'];
   }
    $list_degree = array(2,3,$last);
}
if($application['PROGRAM_TYPE_ID']==1 || $application['PROGRAM_TYPE_ID']==3){
    $list_degree = array(2,3);
}
// if($application['APPLICATION_ID']==2637){
//  prePrint($list_degree);   
//  exit();
// }
for($i=$count_qual ; $i>=0;$i--){

    $qualification = $qualifications[$i];
    // if($qualification['DEGREE_ID']==10){
    //     $check = 1;
    //     continue;
       
        
    // }
    // if($application['PROGRAM_TYPE_ID']==2){
    //     if($i>$check&&$qualification['DEGREE_ID']>3){
    //         continue;
    //     }
    // }
    if(in_array($qualification['DEGREE_ID'], $list_degree)){
        
    
    $pdf->TableCell($first_column_width,$row_height-1,$qualification['DEGREE_TITLE'],1,'L',0);
    $pdf->TableCell(30,$row_height-1,$qualification['DISCIPLINE_NAME'],1,'L',0);
    $pdf->TableCell(18,$row_height-1,$qualification['OBTAINED_MARKS'],1,'L',0);
    $pdf->TableCell(15,$row_height-1,$qualification['TOTAL_MARKS'],1,'L',0);
    $pdf->TableCell(15,$row_height-1,$qualification['PASSING_YEAR'],1,'L',0);
    $pdf->TableCell(17,$row_height-1,$qualification['ROLL_NO'],1,'L',0);
    $pdf->TableCell(35,$row_height-1,$qualification['ORGANIZATION'],1,'L',1);
    }
}
if(count($applicants_minors)>1){
    $subject = "";
    $subject_ch ="";
    foreach($applicants_minors as $applicants_minor){
        $subject.=$subject_ch.$applicants_minor['SUBJECT_TITLE'];
        $subject_ch = ", ";
    }
    $pdf->Ln(1);
    $pdf->SetFont("Times",'',10);
    $pdf->TableCell($first_column_width,$row_height,"Elective / Minor Subjects",1,'L',0);
    $pdf->TableCell($sec_column_width,$row_height,$subject,1,'L',1);
}


$pdf->Ln(3);
$pdf->SetFont("Times",'B',11);
$pdf->customCell($first_column_width,$row_height,"Bank Challan Information",0,'L',1);
$pdf->SetFont("Times",'',10);

$pdf->TableCell($first_column_width,$row_height,"Bank Challan No.",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,str_pad($application['FORM_CHALLAN_ID'],5,"0",STR_PAD_LEFT),1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Date of Payment",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$CHALLAN_DATE,1,'L',1);

$pdf->TableCell($first_column_width,$row_height,"Paid Amount",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$application['CHALLAN_AMOUNT'],1,'L',1);



$pdf->TableCell($first_column_width,$row_height,"HBL Branch",1,'L',0);
$pdf->TableCell($sec_column_width,$row_height,$bank_info['BRANCH_NAME'],1,'L',1);

$pdf->setY(239);
$pdf->SetFont("Times",'',10);
$pdf->Cell(0,$row_height,"Note: If any applicant submitted forged / fake documents (detected at any stage) his / her admission shall be cancelled on his/her own ",0,1);
$pdf->Cell(0,$row_height,"risk and cost and paid fees will not be refunded.",0,1);
$pdf->Ln(1);
$pdf->SetFont("Times",'B',10);
$pdf->Cell(0,$row_height,"UNDERTAKING",0,1,'C');
$pdf->SetFont("Times",'',10);
$pdf->Cell(0,$row_height,"I do hereby state that all information and data given by me as above is true and correct and shall always be binding to me and ",0,1);
$pdf->Cell(0,$row_height,"undertake to abide all provisions of act, statutes, rules and regulations of the University.",0,1);

$pdf->SetFont('Times','',10);
$pdf->text(160,280,"Signature of Candidate");

$pdf->SetFont('Times','',10);
$pdf->text(10,285,"Powered by: Information Technology Services Centre (ITSC)");
$pdf->SetFont('Times','',7);
$pdf->text(110,285,$data);


if(count($user_fulldata['application_choices'])){
    
//add new page
$pdf->AddPage();

$pdf->Image($path,175,5,16,16);
$pdf->Image('images/umpk/logo.png',10,4,26,26);

$pdf->Image($profile_image,175,26,23,28);
$pdf->SetFont("Arial",'B',20);
$pdf->Cell(0,$row_height,UNIVERSITY_NAME,0,1,'C',false);
$pdf->Ln(1);
$pdf->SetFont("Times",'',12);
$pdf->Cell(0,$row_height,"ONLINE ADMISSION FORM - {$application['YEAR']}",0,1,'C',false);
$pdf->SetFont("Times",'',13);
$pdf->Ln(2);
$pdf->Cell(0,$row_height,"({$application['PROGRAM_TITLE']} DEGREE PROGRAM)",0,1,'C',false);
$pdf->Ln(4);
$pdf->Cell(190,$row_height,"Candidate's Application No. {$application['APPLICATION_ID']}",0,1);
    $pdf->Ln(5);
    $pdf->SetFont("Times",'B',11);
    $pdf->customCell($first_column_width,$row_height,"Applied Choice(s)",0,'L',1);
    
    $pdf->SetFont("Times",'B',10);
    $pdf->customCell($sec_column_width+8,$row_height+1,"SUBJECTS / DISCIPLINES (MORNING)",1,'C',1);
    $pdf->SetFont("Times",'',10);
    foreach ($user_fulldata['application_choices'] as $applicant_cho){
         if($applicant_cho['IS_SPECIAL_CHOICE']=='Y'){
                                                $is_specila_self = true;
                                                continue;
                                            }
        $pdf->customCell(8,$row_height+1,$applicant_cho['CHOICE_NO'],1,'L',0);
        $pdf->customCell($sec_column_width,$row_height+1,$applicant_cho['PROGRAM_TITLE'],1,'L',1);
    }
    $pdf->setY(239);
    $pdf->SetFont("Times",'',10);
    $pdf->Cell(0,$row_height,"Note: If any applicant submitted forged / fake documents (detected at any stage) his / her admission shall be cancelled on his/her own ",0,1);
    $pdf->Cell(0,$row_height,"risk and cost and paid fees will not be refunded.",0,1);
    $pdf->Ln(1);
    $pdf->SetFont("Times",'B',10);
    $pdf->Cell(0,$row_height,"UNDERTAKING",0,1,'C');
    $pdf->SetFont("Times",'',10);
    $pdf->Cell(0,$row_height,"I do hereby state that all information and data given by me as above is true and correct and shall always be binding to me and ",0,1);
    $pdf->Cell(0,$row_height,"undertake to abide all provisions of act, statutes, rules and regulations of the University.",0,1);
    
    $pdf->SetFont('Times','',10);
    $pdf->text(160,280,"Signature of Candidate");
    $pdf->SetFont('Times','',10);
    $pdf->text(10,285,"Powered by: Information Technology Services Centre (ITSC)");
    $pdf->SetFont('Times','',7);
    $pdf->text(110,285,$data);
}

if($is_specila_self==true){
    	$pdf->AddPage();

	$pdf->Image($path,175,5,16,16);
	$pdf->Image('images/umpk/logo.png',10,4,26,26);

	$pdf->Image($profile_image,175,26,23,28);
	$pdf->SetFont("Arial",'B',20);
	$pdf->Cell(0,$row_height,UNIVERSITY_NAME,0,1,'C',false);
	$pdf->Ln(1);
	$pdf->SetFont("Times",'',12);
	$pdf->Cell(0,$row_height,"ONLINE ADMISSION FORM - {$application['YEAR']}",0,1,'C',false);
	$pdf->SetFont("Times",'',13);
	$pdf->Ln(2);
	$pdf->Cell(0,$row_height,"({$application['PROGRAM_TITLE']} DEGREE PROGRAM - (MORNING) SPECIAL SELF FINANCE)",0,1,'C',false);
	$pdf->Ln(4);
	$pdf->Cell(190,$row_height,"Candidate's Application No. {$application['APPLICATION_ID']}",0,1);

	$pdf->Ln(5);
	$pdf->SetFont("Times",'B',11);
	$pdf->customCell($first_column_width,$row_height,"Applied Choice(s)",0,'L',1);

	$pdf->SetFont("Times",'B',10);
	$pdf->customCell($sec_column_width+8,$row_height+1,"SUBJECTS / DISCIPLINES  (MORNING) SPECIAL SELF FINANCE",1,'C',1);
	$pdf->SetFont("Times",'',10);

foreach ($user_fulldata['application_choices'] as $applicant_cho){
     if($applicant_cho['IS_SPECIAL_CHOICE']=='N'){
                                            $is_specila_self = true;
                                            continue;
                                        }
    $pdf->customCell(8,$row_height+1,$applicant_cho['CHOICE_NO'],1,'L',0);
    $pdf->customCell($sec_column_width,$row_height+1,$applicant_cho['PROGRAM_TITLE'],1,'L',1);
}




}
$evening_choices = $user_fulldata['application_choices_evening'];
if($is_evening_choice&&count($evening_choices)>0){

	$pdf->AddPage();

	$pdf->Image($path,175,5,16,16);
	$pdf->Image('images/umpk/logo.png',10,4,26,26);

	$pdf->Image($profile_image,175,26,23,28);
	$pdf->SetFont("Arial",'B',20);
	$pdf->Cell(0,$row_height,UNIVERSITY_NAME,0,1,'C',false);
	$pdf->Ln(1);
	$pdf->SetFont("Times",'',12);
	$pdf->Cell(0,$row_height,"ONLINE ADMISSION FORM - {$application['YEAR']}",0,1,'C',false);
	$pdf->SetFont("Times",'',13);
	$pdf->Ln(2);
	$pdf->Cell(0,$row_height,"({$application['PROGRAM_TITLE']} DEGREE PROGRAM - EVENING)",0,1,'C',false);
	$pdf->Ln(4);
	$pdf->Cell(190,$row_height,"Candidate's Application No. {$application['APPLICATION_ID']}",0,1);

	$pdf->Ln(5);
	$pdf->SetFont("Times",'B',11);
	$pdf->customCell($first_column_width,$row_height,"Applied Choice(s)",0,'L',1);

	$pdf->SetFont("Times",'B',10);
	$pdf->customCell($sec_column_width+8,$row_height+1,"SUBJECTS / DISCIPLINES (EVENING)",1,'C',1);
	$pdf->SetFont("Times",'',10);

	foreach ($evening_choices as $applicant_cho){
		$pdf->customCell(8,$row_height+1,$applicant_cho['CHOICE_NO'],1,'L',0);
		$pdf->customCell($sec_column_width,$row_height+1,$applicant_cho['PROGRAM_TITLE'],1,'L',1);

	}
	$pdf->setY(239);
$pdf->SetFont("Times",'',10);
$pdf->Cell(0,$row_height,"Note: If any applicant submitted forged / fake documents (detected at any stage) his / her admission shall be cancelled on his/her own ",0,1);
$pdf->Cell(0,$row_height,"risk and cost and paid fees will not be refunded.",0,1);
$pdf->Ln(1);
$pdf->SetFont("Times",'B',10);
$pdf->Cell(0,$row_height,"UNDERTAKING",0,1,'C');
$pdf->SetFont("Times",'',10);
$pdf->Cell(0,$row_height,"I do hereby state that all information and data given by me as above is true and correct and shall always be binding to me and ",0,1);
$pdf->Cell(0,$row_height,"undertake to abide all provisions of act, statutes, rules and regulations of the University.",0,1);

$pdf->SetFont('Times','',10);
$pdf->text(160,280,"Signature of Candidate");
$pdf->SetFont('Times','',10);
$pdf->text(10,285,"Powered by: Information Technology Services Centre (ITSC)");
$pdf->SetFont('Times','',7);
$pdf->text(110,285,$data);



}//checking wether evening choice array is empty or not

//prePrint($evening_choices);


$pdf->AddPage();
if($application['PROGRAM_TITLE'] == 'BACHELOR') {
    $req_doc = "BACHELOR"; 
} else {
    $req_doc = "MASTER";
}
$pdf->Image("assets/img/{$req_doc}.jpg",10,5,$pdf->GetPageWidth()-20,$pdf->GetPageHeight()-20);
$pdf->SetFont('Times','',10);
$pdf->text(10,285,"Powered by: Information Technology Services Centre (ITSC)");
$pdf->SetFont('Times','',7);
$pdf->text(110,285,$data);



$pdf->Output("application_form_{$application['APPLICATION_ID']}.pdf",'I');
//exit();
?>
