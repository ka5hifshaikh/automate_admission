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


myFunction("BANK'S COPY",$x,$pdf,$challan);
line($x,$pdf);
$x=75;
myFunction("ACCOUNT'S COPY",$x,$pdf,$challan);
line($x,$pdf);
$x=145;
myFunction("ADMISSION'S COPY",$x,$pdf,$challan);
line($x,$pdf);
$x=215;
myFunction("STUDENT'S COPY",$x,$pdf,$challan);


 //$pdf->AddPage('P','A4');
  //$pdf->Image('assets/challan_instruction.jpeg',0,0,$pdf->GetPageWidth(),$pdf->GetPageHeight());


function line($x,$pdf){
	$pdf->Line($x+70,5,$x+70,213);
}

$id=$challan['FEE_CHALLAN']['CHALLAN_NO'];
$pdf->Output("1.pdf",'I');

function myFunction($copy, $x,$pdf,$challan)
{
	$record = $challan['PROFILE'];
	$challan_info = $challan['FEE_CHALLAN'];

	$stdName = $record['FIRST_NAME'];
	$list_no = $record['LIST_NO'];
	$fName = $record['FNAME'];
	$surName = $record['LAST_NAME'];
	$application_id = $record['APPLICATION_ID'];
	$cnic_no = $record['CNIC_NO'];
	$campus_name = $record['NAME'];

	$degree_program = $record['PROGRAM_TITLE'];

//	$total_amount = $challan_info['PAYABLE_AMOUNT'];
//	$category_name = $challan_info['ACCOUNT_TITLE'];
	$total_amount = "200";
	$category_name = "RETAINING CHALLAN";

	$in_words =  convert_number_to_words($total_amount);

	$in_words = ucwords(strtoupper($in_words)).' ONLY';

//	$label = $challan_info['REMARKS'];
	$label = "RETAINING FEE";
	$valid_upto = $challan_info['VALID_UPTO'];

	$account_no = $challan_info['ACCOUNT_NO'];
	$challan_no = $challan_info['CHALLAN_NO'];
	$challan_no = str_pad($challan_no, 5, '0', STR_PAD_LEFT);

	$current_date = date("d-m-Y");

	if (date("Y-m-d") >$valid_upto)
	{
		exit("Sorry your challan is expired..");
	}
	$valid_upto = date_create($valid_upto);
	$valid_upto = date_format($valid_upto,'d-m-Y');
	
	$pdf->Image('./assets/img/University_of_Sindh_logo.png',5+$x,4,18);
	$pdf->Image('./assets/img/ubl logoo.png',25+$x,4,18);
	
	
	$height=25;
    
    $pdf->ln(0); 
    $pdf->SetFont('Times','B',13);
    
    $height=$height+4;
    $pdf->SetXY($x + 3, $height);
    
    $pdf->MultiCell(65,6,'1-Bill ID',1,"C",false);

    $height=$height+6;
    $pdf->SetXY($x + 3, $height);
    $pdf->Cell(65,6,"1001145094".$challan_no,1,"","C",false);
    
    $pdf->SetFont('Times','',9);
     
    $height=$height+6;
    $pdf->SetXY($x + 3, $height);
    $pdf->Cell(32,6,"Challan No.",1,"","C",false);
    
    // $height=$height+6;
    // $pdf->SetXY($x + 3, $height);
    $pdf->Cell(33,6,$challan_no,1,"","C",false);
	
	/*
	$pdf->SetFont('Arial','B',20);

//    $pdf->text(5+$x,15,"HBL");
  $pdf->Image('assets/img/University_of_Sindh_logo.png',5+$x,4,18);
    $pdf->Image('assets/img/hbl_logo.jpg',25+$x,10,18);

	$pdf->SetFont('Arial','B',7);
	$height=25;
	$pdf->text(20+$x,$height,$copy);
	$pdf->Ln();

	$pdf->SetFont('Arial','B',8);

//    $pdf->text($x+5,33,"UNIVERSITY OF SINDH BRANCH, JAMSHORO");
	$height=$height+6;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+7,$height,"Please receive and credit to University of Sindh");
	$height = $height+5;
	$pdf->SetFont('Arial','B',8);
	$pdf->text($x+17,$height,"ADMISSION ACCOUNT NO.");
	$pdf->SetFont('Arial','B',11);
	$height = $height+5;
	$pdf->text($x+15,$height,"CMD. $account_no");
	$height = $height+5;
	$pdf->SetFont('Arial','B',9);
	$pdf->text($x+4,$height,"CHALLAN NO: ".$challan_no);
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+43,$height,"DATE: $current_date");
	$pdf->SetFont('Arial','B',9);
	$height=$height+7;
	$pdf->SetTextColor(255,0,0);
	$pdf->text($x+7,$height,"This challan is valid upto: $valid_upto");
    */
	
	$height = $height+5;
	$pdf->SetFont('Arial','B',8);
    $pdf->text($x+43,$height-19,"DATE: $current_date");
    $height = $height+5;
	$pdf->SetFont('Arial','B',9);
	$pdf->text($x+45,$height-45,$challan_no);
	$pdf->SetFont('Arial','B',9);
	$height=$height+3;
	$pdf->text($x+7,$height,"This challan is valid upto: $valid_upto");

	$pdf->SetFont('Arial','B',11);
	$height =$height+ 3;
	$pdf->SetXY($x + 5, $height);
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
	$height =$height+4;
	$pdf->text($x+5,$height,strtoupper($stdName));

	$pdf->SetFont('Arial','',8);
	$height =$height+5;
	$pdf->text($x+5,$height,"FATHER'S NAME:");
	$pdf->SetFont('Arial','B',9);
	$height =$height+4;
	$pdf->text($x+5,$height,strtoupper($fName));
	$height =$height+5;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+5,$height,"SURNAME:");
	$pdf->SetFont('Arial','B',9);
	$height =$height+4;
	$pdf->text($x+5,$height,strtoupper($surName));

	$height =$height+5;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+5,$height,"CNIC NO:");
	$pdf->SetFont('Arial','B',9);
	$height =$height+0;
	$pdf->text($x+20,$height,$cnic_no);

	$height =$height+5;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+5,$height,"APP NO:");
	$pdf->SetFont('Arial','B',9);
	$height =$height+0;
	$pdf->text($x+20,$height,$application_id);

	$height =$height+0;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+35,$height,"SELECTION LIST #:");
	$pdf->SetFont('Arial','B',9);
	$height =$height+0;
	$pdf->text($x+62,$height,$list_no);

	$height =$height+5;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+5,$height,"CAMPUS:");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY($x + 4, 104);
	$pdf->MultiCell(65,4,"$campus_name",0,"L",false);

	$height =$height+11;
	$pdf->SetFont('Arial','',8);
	$pdf->text($x+5,$height,"RETAINED SELECTION:");

	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY($x + 4, 115);
	$pdf->MultiCell(65,4,strtoupper($degree_program),0,"L",false);
	/*
	$height =$height+4;
	$pdf->SetFont('Arial','B',9);
	$pdf->text($x+5,$height,strtoupper($degree_program).' DEGREE PROGRAM');
*/

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
	$height=$height+10;
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
	$pdf->MultiCell(40,7,$label,1,"J");
	$pdf->SetXY($x1+40, $y);
	$pdf->Cell(25,7,"Rs. ".number_format($total_amount,2),1,"","R",false);
	$height = $height+10;
	$pdf->SetXY($x + 3, $height);
	$pdf->SetFont('Times','B',9);

//    $pdf->TableCell(65,4,"Amount (in words): $in_words",0,'L',0);

	$pdf->MultiCell(65,4,"Amount (in words): $in_words",0,"L",false);

	$pdf->SetXY($x + 4, 152);
	$pdf->SetFont('ARIAL','',8);

	$pdf->MultiCell(64,3,"                      IMPORTANT NOTE
         The provisional selection/admission is allowed on the basis of data provided / submitted by the candidate him/herself. The University of Sindh reserves the right to rectify any error / omission detected at any stage. In case any applicant submitted forged / fake documents or provided wrong information in online admission form (detected at any stage), their admission shall be cancelled and prosecuted under Criminal Laws.",1,"L",false);

	$data = $application_id. "~". $challan_no . "~".$cnic_no."~" . $total_amount . "~" . $valid_upto . "~" . $account_no . "~" . $current_date;
	//$result=str_pad($data, 10, "0", STR_PAD_LEFT);


	$s="                                                                                ".$data;

	$result=substr($s, strlen($s) - 80, strlen($s));

//    $pdf->text($x+5,190,"MANAGER");
//    $pdf->text($x+52,190,"CASHIER");

	$pdf->setTextColor(0,0,0);

	$pdf->SetFont('Arial','',4);
	$pdf->text($x+5,199,$data);
	$pdf->SetFont('Times','',7);
	$pdf->text($x+5,203,"Powered by: Information Technology Services Centre (ITSC)");

	QRcode::png("$result","../eportal_resource/qr_images/".$challan_no.".png", 'QR_ECLEVEL_L', 3, 2);
	$path="../eportal_resource/qr_images/".$challan_no.".png";
	$pdf->Image($path,44+$x,6,18);
}
?><?php
