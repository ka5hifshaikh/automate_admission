<?php
if( ! defined('BASEPATH')) exit('No direct script access allowed');
class MyyPDF extends FPDF{
    
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

	function Header(){
		$this->SetFont('Arial','B',15);
	}
	
	function Footer(){
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		// $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}

$pdf = new MyyPDF('P','mm','A4');

$pdf->AddPage();

    $program_title  = $CLASS_ENROLMENT['PROGRAM_TITLE'];
    $dept_email     = $CLASS_ENROLMENT['DEPT_EMAIL'];
    $dept_name      = $CLASS_ENROLMENT['DEPT_NAME'];
    $std_data       = $CLASS_ENROLMENT['STD_DATA'];
    $campus_name    = $CAMPUS_NAME;
    $year           = $YEAR;
    $part_name      = $PART_NAME;
    $shift_name     = $SHIFT_NAME;

    $pdf->SetFont("Arial",'B',10);
    $row_height = 7;
    $pdf->Cell(15,$row_height,"S#",1,'L');
    $pdf->Cell(30,$row_height,"Roll#",1,'L');
    $pdf->Cell(60,$row_height,"Student Name",1,'L');
    $pdf->Cell(80,$row_height,"Father Name",1,'L');
    // $pdf->Cell(60,$row_height,"Surname",1,'L');
    
    $pdf->ln(7);
    $no=0;
    $pdf->SetFont("Arial",'',10);
    
    foreach ($std_data as $std){
        $no++;
        $name   = $std['FIRST_NAME'];
        $lname  = $std['LAST_NAME'];
        $fname  = $std['FNAME'];
        $roll_no= $std['ROLL_NO'];
        
        $pdf->Cell(15,$row_height,$no,1,'L');
        $pdf->Cell(30,$row_height,$roll_no,1,'L');
        $pdf->Cell(60,$row_height,$name,1,'L');
        $pdf->Cell(80,$row_height,$fname.', '.$lname,1,'L');
        // $pdf->Cell(60,$row_height,$lname,1,'L');
        $pdf->ln(7);
    }
            

$file_name = $campus_name."_".$program_title."_".$part_name."_".$shift_name."_BATCH_".$year.'.pdf';
$file_name_saving_path="../HoDClassEnrolmentPdfReports/".$file_name;
$attachments = array ($file_name_saving_path);

$pdf->Output($file_name_saving_path,'F');
$email_body="<p>*This is system generated email, please don’t reply to this email</p>
			 <p>University of Sindh reserves the right to correct any error/omission detected later on, and also reserves the right to cancel any provisional admission at any time without issuing notice.</p>
<br><br>
                      
                      Best Regards, <br>";


$EMAIL = "dir.adms@umpk.edu.pk";
// send_smtp_email_with_attachment("Provisional Admission List of $program_title $part_name $shift_name of Batch $year",$email_body,$EMAIL,$this,$attachments);
?>
