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

$pdf = new PDF('P','mm','A4');

$pdf->AddPage();




$pdf->Output("letter.pdf",'I');


?>
