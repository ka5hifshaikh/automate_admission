<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PDF extends FPDF
{
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

$pdf = new PDF('L','mm','A4');

$pdf->AddPage();

$x=7;


myFunction("BANK'S COPY",$x,$pdf,$row,$roll_no);
line($x,$pdf);
$x=75;
myFunction("ACCOUNT'S COPY",$x,$pdf,$row,$roll_no);
line($x,$pdf);
$x=145;
myFunction("ADMISSION'S COPY",$x,$pdf,$row,$roll_no);
line($x,$pdf);
$x=215;
myFunction("STUDENT'S COPY",$x,$pdf,$row,$roll_no);
//$pdf->AddPage();

//Bill_1_Guideline($pdf);

function line($x,$pdf){
    $pdf->Line($x+70,5,$x+70,213);
}

$pdf->Output("1.pdf",'I');

function myFunction($copy, $x,$pdf,$record,$roll_no){
    
    $stdName = $record['CANDIDATE_NAME'];
    $rollNo = $roll_no;
    $fName = $record['CANDIDATE_FNAME'];
    $surName = $record['CANDIDATE_SURNAME'];
    $application_id = $record['APPLICATION_ID'];
    $cnic_no = $record['CNIC_NO'];
    $campus_name = $record['CAMPUS_NAME'];

    $degree_program = $record['DEGREE_PROGRAM'];

    $total_amount = $record['TOTAL_AMOUNT'];

    $in_words =  convert_number_to_words($total_amount);
    $in_words = ucwords(strtoupper($in_words)).' ONLY';

    $category_name = $record['CATEGORY_NAME'];
    $valid_upto = $record['VALID_UPTO'];

    $account_no = $record['ACCOUNT_NO'];
    $candidate_id = $record['CANDIDATE_ID'];
    $challan_no = $record['CHALLAN_NO'];
      $challan_no  =  ADMP_CODE.str_pad($challan_no, 5, "0", STR_PAD_LEFT);
    
    $YEAR = $record['YEAR'];
    $current_date = date("d-m-Y");

//    if (date("Y-m-d") >date_format($valid_upto,'Y-m-d'))
//    {
//        exit("Sorry your challan is expired..");
//    }

    $pdf->SetFont('Arial','',12);
    $pdf->setTextColor(247,7,7);
    //$pdf->text(85,195,"Please DO NOT pay this challan at Easypaisa/ UBL Omni/ TCS.");
    $pdf->setTextColor(0,0,0);
    
    
    $pdf->Image('images/umpk/logo.png',5+$x,3,15);
    //$pdf->Image('assets/img/1bill.jpg',20+$x,4,15);
    $pdf->Image('images/umpk/sb_logo.png',25+$x,8,18);
    
    $height=22;
    
    
     $pdf->SetFont('Arial','B',8);
     $pdf->text(15+$x,$height,$copy);
    
    $pdf->SetFont('Arial','B',10);
    $pdf->text($x+12,$height+5,UNIVERSITY_NAME);
    $pdf->SetFont('Arial','B',8);
   // $pdf->text($x+4,$height+9,"Institutional Fee Collection: YTS-31");

    $height=$height+10;
    $pdf->SetFont('Times','B',11);
    
    
    $pdf->SetXY($x + 3, $height);
    
    $pdf->MultiCell(65,6,'SINDH BANK ACCOUNT NO',1,"C",false);

    $height=$height+6;
    $pdf->SetXY($x + 3, $height);
    $pdf->Cell(65,6,"PK21SIND0004041068006001",1,"","C",false);
     
    $height=$height+6;
    $pdf->SetXY($x + 3, $height);
    $pdf->Cell(32,6,"Challan No.",1,"","C",false);
    
    $pdf->Cell(33,6,$challan_no,1,"","C",false);

/*
    $pdf->SetFont('Arial','B',8);

//    $pdf->text($x+5,33,"UNIVERSITY OF SINDH BRANCH, JAMSHORO");
    $height=$height+6;
    $pdf->SetFont('Arial','',8);
    $pdf->text($x+7,$height,"Please receive and credit to University of Sindh");
    $height = $height+5;
    $pdf->SetFont('Arial','B',8);
    $pdf->text($x+5,$height,"ADMISSION MISCELLANEOUS ACCOUNT NO.");
    $pdf->SetFont('Arial','B',11);
    $height = $height+5;
    $pdf->text($x+15,$height,"CMD. $account_no");

  $height = $height+2;
  $pdf->SetTextColor(255,0,0);
    $pdf->SetFont('Arial','B',11);
     $pdf->SetXY($x + 7, $height);
    //$pdf->text($x+13,$height,"CHALLAN NO: ");
    $pdf->Cell(30,7,"CHALLAN NO",1,"","C",false);
    $height = $height+ 6;
  $pdf->SetTextColor(255,0,0);
    $pdf->SetFont('Arial','B',11);
  //  $pdf->text($x+13,$height,$challan_no);
   //$pdf->SetXY($x + 13, $height);
    $pdf->Cell(30,7,$challan_no,1,"","C",false);
    $pdf->SetFont('Arial','B',9);
    $height=$height+7;
    */
    
    $pdf->SetFont('Arial','B',9);
    $height =$height+ 10;
    $pdf->SetXY($x + 5, $height);
    
    $pdf->SetTextColor(255,0,0);
    $pdf->text($x+7,$height,"This challan is valid upto: $valid_upto");
    $height = $height+2;
    $pdf->SetXY($x + 5, $height);
    $pdf->SetFont('Arial','B',11);
    $pdf->SetTextColor(255,255,255);
    $pdf->MultiCell(60, 5, $category_name, 1, 'C', true);
    $pdf->SetTextColor(0,0,0);

//    $pdf->text($x+19,49,$category_name);

    $pdf->setTextColor(60,60,60);
    // $pdf->setTextColor(0,0,0);

//    $pdf->SetFont('Arial','B',10);
//    $pdf->text($x+10,85,"CANDIDATE INFORMATION");

//    $pdf->SetFont('Arial','',8);
//    $pdf->text($x+5,54,"ROLL NO:");
//    $pdf->SetFont('Arial','B',9);
//    $pdf->text($x+22,54,strtoupper($rollNo));
//
//    $pdf->SetFont('Arial','',8);
//    $pdf->text($x+5,58,"SEAT NO:");
//    $pdf->SetFont('Arial','B',9);
//    $pdf->text($x+22,58,$seat_no);
    $height =$height+ 10;
    $pdf->SetFont('Arial','',8);
    $pdf->text($x+5,$height,"CANDIDATE NAME:");
    $pdf->SetFont('Arial','B',9);
    $height =$height+5;
    $pdf->text($x+5,$height,strtoupper($stdName));

    $pdf->SetFont('Arial','',8);
    $height =$height+6;
    $pdf->text($x+5,$height,"FATHER'S NAME:");
    $pdf->SetFont('Arial','B',9);
    $height =$height+6;
    $pdf->text($x+5,$height,strtoupper($fName));
    $height =$height+6;
    $pdf->SetFont('Arial','',8);
    $pdf->text($x+5,$height,"SURNAME:");
    $pdf->SetFont('Arial','B',9);
    $height =$height+6;
    $pdf->text($x+5,$height,strtoupper($surName));
    
    $height =$height+6;
    $pdf->SetFont('Arial','',8);
    $pdf->text($x+5,$height,"CNIC NO:");
    $pdf->SetFont('Arial','B',9);
    $height =$height+6;
    $pdf->text($x+5,$height,$cnic_no);
    
    // $height =$height+5;
    // $pdf->SetFont('Arial','',8);
    // $pdf->text($x+5,$height,"APPLIED CAMPUS:");
    // $height =$height+4;
    // $pdf->SetFont('Arial','B',9);
    // $pdf->text($x+5,$height,strtoupper($campus_name));

    //$height =$height+6;
    //$pdf->SetFont('Arial','',8);
   // $pdf->text($x+5,$height,"APPLIED CAMPUS:");
   // $pdf->SetFont('Arial','B',8);
    //$height = $height+1;
   // $pdf->SetXY($x + 4, $height);
   // $pdf->MultiCell(65,4,"$campus_name",0,"L",false);

    $height =$height+10;
    $pdf->SetFont('Arial','',8);
    $pdf->text($x+5,$height,"APPLIED FOR:");
    $height =$height+5;
    $pdf->SetFont('Arial','B',9);
    
    $d_p =strtoupper($degree_program);
    if($d_p != 'CERTIFICATE PROGRAM'){$d_p .= ' DEGREE PROGRAM';}
    $pdf->text($x+5,$height,$d_p);


//    $pdf->SetFont('Arial','',8);
//    $pdf->text($x+5,86,"CLASS:");
//    $pdf->SetFont('Arial','B',9);
//    $pdf->text($x+18,86,strtoupper($class));
//
//    $pdf->SetFont('Arial','',8);
//    $pdf->text($x+5,90,"PROGRAM:");
//    $pdf->SetFont('Arial','B',8);
//    $pdf->SetXY($x + 4, 91);
//    $pdf->MultiCell(65,4,"$Program",0,"L",false);
//


    $pdf->setTextColor(0,0,0);

    $pdf->ln(0);
    $height=$height+4;
    $pdf->SetXY($x + 3, $height);
    $pdf->SetFont('Times','B',10);
    $pdf->Cell(40,6,"Purpose of Payment",1,"","C",false);
    $pdf->Cell(25,6,"Amount (Rs.)",1,"","C",false);
//    $pdf->ln();
//    $pdf->SetXY($x + 3, 121);
//    $pdf->SetFont('Times','B',10);
//    $pdf->Cell(40,6,"DUES",1,"","R",false);
//    $pdf->Cell(25,6,"Rs. ".number_format($due,2),1,"","R",false);
    $height = $height+6;
    $pdf->SetXY($x + 3,$height );
    $pdf->SetFont('Times','B',10);
    $x1 = $pdf->getX();
    $y = $pdf->getY();
    $pdf->MultiCell(40,5,"Admission Registration and Processing Fee",1,"J");
    $pdf->SetXY($x1+40, $y);
    $pdf->Cell(25,10,"Rs. ".number_format($total_amount,2),1,"","R",false);
    $height = $height+15;
    $pdf->SetXY($x + 3, $height);
    $pdf->SetFont('Times','B',9);

//    $pdf->TableCell(65,4,"Amount (in words): $in_words",0,'L',0);

    $pdf->MultiCell(65,4,"Amount (in words): $in_words",0,"L",false);

    $pdf->SetXY($x + 4, 157);
    $pdf->SetFont('ARIAL','',8);

    $pdf->MultiCell(64,4,"                      IMPORTANT NOTE
         This paid amount (Rs: ".number_format($total_amount,2)."/=) is non-transferable and non-refundable. In case any applicant submitted / provided wrong information in admission form (detected at any stage), his/her admission shall be cancelled. ".UNIVERSITY_NAME." reserves the right to rectify any error / omission detected at any stage.",1,"L",false);

    $data = $candidate_id. "~"  . $application_id. "~". $challan_no . "~".$cnic_no."~" . $total_amount . "~" . $valid_upto . "~" . $account_no . "~" . $current_date;
    //$result=str_pad($data, 10, "0", STR_PAD_LEFT);


    $s="                                                                                ".$data;

    $result=substr($s, strlen($s) - 80, strlen($s));


    $pdf->setTextColor(0,0,0);

    $pdf->SetFont('Arial','',4);
    $pdf->text($x+5,199,$data);
    $pdf->SetFont('Times','',7);
    $pdf->text($x+5,203,"Powered by: Information Technology Services Centre (ITSC)");

    QRcode::png("$result","../eportal_resource/qr_images/".$challan_no.".png", 'QR_ECLEVEL_L', 3, 2);
    $path="../eportal_resource/qr_images/".$challan_no.".png";
    $pdf->Image($path,51+$x,6,18);
}

function Bill_1_Guideline($pdf){

$pdf->SetFont('Arial','B',14);

$pdf->MultiCell(200,6,'This page is only for the information of payment method. DO NOT PRINT',0,"C",false);
$pdf->ln(7);
$pdf->MultiCell(250,6,'Pay your challan at Habib Bank Limited or any Branch of following Bank through 1-Bill ID',0,"C",false);

$pdf->SetFont('Arial','',11);

$x=15;
$y=40;
$pdf->text($x,$y,"1.  ALLIED BANK LIMITED (ABL)");
$pdf->text($x,$y=$y+6,"2.  ASKARI BANK");
$pdf->text($x,$y=$y+6,"3.  DUBAI ISLAMIC BANK");
$pdf->text($x,$y=$y+6,"4.  MEEZAN BANK");
$pdf->text($x,$y=$y+6,"5.  SINDH BANK");
$pdf->text($x,$y=$y+6,"6.  SONERI BANK");
$pdf->text($x,$y=$y+6,"7.  UNITED BANK LIMITED (UBL)");
$pdf->text($x,$y=$y+6,"8.  FAISAL BANK LIMITED");
$pdf->text($x,$y=$y+6,"9.  MCB ISLAMIC BANK");
$pdf->text($x,$y=$y+6,"10. NATIONAL BANK OF PAKISTAN");
$pdf->text($x,$y=$y+6,"11. JS BANK");
$pdf->text($x,$y=$y+6,"12. BANK ALFALAH LIMITED");
$pdf->text($x,$y=$y+6,"13. MUSLIM COMMERCIAL BANK (MCB)");
$pdf->text($x,$y=$y+6,"14. HABIB METRO BANK");
$pdf->text($x,$y=$y+6,"15. BANK ISLAMI PAKISTAN LIMITED");

$pdf->SetFont('Arial','B',14);

$pdf->text($x,$y=$y+15,"You can also pay your challan from the following Bank/ Mobile Wallet App");

$pdf->SetFont('Arial','',11);
$pdf->text($x,$y=$y+6,"");
$pdf->text($x,$y=$y+6,"1.  JazzCash Mobile App (Through 1-Bill Voucher/ Invoice)");
$pdf->text($x,$y=$y+6,"2.  HBL Konnect Mobile App (Through Lifestyle -> Education -> UNIVERSITY OF SINDH)");
$pdf->text($x,$y=$y+6,"3.  HBL Mobile App (Through More -> Bill Payments -> Education -> UNIVERSITY OF SINDH)");
$pdf->setTextColor(10,163,240);
$pdf->text($x,$y=$y+10,"Visit this link to verify your online payment: https://itsc.usindh.edu.pk/eportal/public/verify_challan.php");
$pdf->setTextColor(0,0,0);
$pdf->SetFont('Arial','B',11); 
$pdf->text($x,$y=$y+10,"Important Notices:");

$pdf->setTextColor(247,7,7);
$pdf->text($x,$y=$y+10,"1.  Please DO NOT pay challan at Easypaisa/ UBL Omni/ TCS.");
$pdf->text($x,$y=$y+6,"2.  Do not pay the challan amount through Fund transfer/ Send Money.");

}
?>
