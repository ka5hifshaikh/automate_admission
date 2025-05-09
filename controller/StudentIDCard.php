<?php
/**
 * Created by PhpStorm.
 * User: Kashif Shaikh
 * Date: 9/16/2020
 * Time: 10:28 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'controllers/AdminLogin.php';
class StudentIDCard extends AdminLogin {
    private $script_name = "";
    public function __construct() {
        parent::__construct();
        $this->load->model('Administration');
        $this->load->model('log_model');
        $this->load->model('Api_qualification_model');
        $this->load->model('Api_location_model');
        $this->load->model('User_model');
        $this->load->model('Application_model');
        $this->load->model('Admission_session_model');
        $this->load->model('TestResult_model');
        $this->load->model('AdmitCard_model');
        $this->load->model('FeeChallan_model');
        $this->load->model('Prerequisite_model');
        $this->load->model('StudentReports_model');
        $this->load->library('Tcpdf_master');
        $this->legacy_db = $this->load->database('admission_db',true);
		$this->db = $this->load->database('admission_v2',true);
        $self = $_SERVER['PHP_SELF'];
        $self = explode('index.php/',$self);
        $this->script_name = $self[1];
        $this->verify_login();
    }
    
     private function CI_ftp_Download($path,$name){
        $user = $this->user ;
        $date_time =date('Y F d l h:i A');
        $msg = array(
            "USER_ID"=>$user['USER_ID'],
            "FILE_NAME"=>$name,
            "DATE_TIME"=>$date_time,
            "MSG"=>""
        );

        $this->load->library('ftp');
        $config['hostname'] = FTP_URL;
        $config['username'] = FTP_USER;
        $config['password'] = FTP_PASSWORD;
        $config['debug']        = false;
        $connect = false;
        for($i=1;$i<=3;$i++){
            $connect = $this->ftp->connect($config);
            if($connect){
                break;
            }
        }
        if(!$connect){
            $msg['MSG'] = 'CONNECTION FAILED';
            $msg = json_encode($msg);
            writeQuery($msg);
            $this->ftp->close();
            return false;
        }

        $ftp_path = str_replace("..","/public_html",$path);
        $ftp_dir_path = rtrim($ftp_path,"/");

        // $ftp_path = '/public_html/eportal_resource/foo/';
        // $ftp_dir_path = '/public_html/eportal_resource/foo';



        // $already_exist = $this->ftp->list_files($ftp_path);

        // if($already_exist){

        // }else{
        //     $dir  = $this->ftp->mkdir($ftp_dir_path, 0755);
        // }
//        prePrint($ftp_path.$name);
//        prePrint($path.$name);
//        exit();

        $up = $this->ftp->download($ftp_path.$name,$path.$name, 'binary');
        if(!$up){
            $msg['MSG'] = 'Downloading FAILED';
            $msg = json_encode($msg);
            $this->ftp->close();
            writeQuery($msg);

            return false;
        }

        $this->ftp->close();
        return true;

    }
    
    public function testpdf(){
         if(isset($_GET['s_id'])&&isset($_GET['pt_id'])
	     &&isset($_GET['sh_id'])&&isset($_GET['c_id'])
	     &&isset($_GET['p_id'])&&isset($_GET['pl_id'])){
            
            $session_id = isValidData($_GET['s_id']);
            $shift_id = isValidData($_GET['sh_id']);
            $prog_type_id = isValidData($_GET['pt_id']);
            $campus_id = isValidData($_GET['c_id']);
            $part_id = isValidData($_GET['p_id']);
            $prog_list_id = json_decode(urldecode($_GET['pl_id']));
            $prog_list_id_str = join($prog_list_id,',');
            prePrint($prog_list_id_str);

	     }else{
	         exit("<h1>Please Must Select All parameters</h1>");
	     }
        $response = $this->StudentReports_model->getStudentByProgram($campus_id,$prog_type_id,$session_id,$shift_id,$prog_list_id_str,$part_id);
        foreach ($response as $key=>$value) {				
			$image_path  =$value['PROFILE_IMAGE'];
 			//echo "<img src='$image_path'>";
 			
 			//$image_headers = get_headers($image_path);
 			//if($image_headers[0] == 'HTTP/1.1 200 OK') {
 				$data[] = $value;
 			//}			
		}

	    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor(PDF_AUTHOR);
			$pdf->SetTitle('ID Card Report '.$data[0]['PROGRAM_TITLE'].' '.$data[0]['PART_NAME']);
			$pdf->SetSubject('');
			$pdf->SetKeywords('');
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetAutoPageBreak(FALSE);
			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				require_once(dirname(__FILE__).'/lang/eng.php');
				$pdf->setLanguageArray($l);
			}
			$imageF = K_PATH_IMAGES.'id_card_front.jpg';
			$imageB = K_PATH_IMAGES.'id_card_back.jpg';
			$sign = K_PATH_IMAGES.'DA_sign.png';
			$logoT = K_PATH_IMAGES.'logo_t.jpg';
			$pdf->AddPage();
			$x = 18.65;
			$y = 9;
			$border = 1;
			$count = count($data);
			 
			for ($i = 0; $i < $count; $i++) {
			    
			    $image_path = K_PATH_PROFLE_IMAGES.$data[$i]['PROFILE_IMAGE'];;
			    
			   	$pdf->Image($imageF, $x, $y, 85.6, 0, '','',true);
				$pdf->Image($imageB, $x+86.2, $y, 85.6, 0, '','',true);
				$pdf->Image($image_path, $x+3, $y+16.5, 20, 26.1, '','',true);
				$pdf->Image($sign, $x+61, $y+32.5, 22, 8, '','',true);
				$pdf->Image($logoT, $x+86.2, $y+16.7, 85.6, 25.6, $type = 'JPG', $link = '', $align = 'C', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, 0, $fitbox = 'CM', $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
			
			    $y = $y + 55.8;
				if(($i+1)%5==0){
					$pdf->AddPage();
					$x = 18.65;
					$y = 9;
				}				
			}
			
			$pdf->lastPage();
			ob_end_clean();
			$pdf->Output('Test.pdf', 'I');
			exit;
	}
    
    public function idcardreport(){
		$candidate_id = $this->input->post('candidate_id');
		if ($candidate_id != "") {
			$response = $this->studentmodel->getIDCardCandidateData($candidate_id);
			foreach ($response as $key=>$value) {				
				$image_path = $this->studentmodel->getImagePath($value['candidate_id']);
				$image_headers = @get_headers($image_path);	
				if($image_headers[0] == 'HTTP/1.1 200 OK') {
					$data[] = $value;
				}			
			}			
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor(PDF_AUTHOR);
			$pdf->SetTitle('ID Card Report '.$candidate_id);
			$pdf->SetSubject('');
			$pdf->SetKeywords('');
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetAutoPageBreak(FALSE);
			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				require_once(dirname(__FILE__).'/lang/eng.php');
				$pdf->setLanguageArray($l);
			}		
			$imageF = K_PATH_IMAGES.'id_card_front.jpg';
			$imageB = K_PATH_IMAGES.'id_card_back.jpg';
			$sign = K_PATH_IMAGES.'DA_sign.png';
			$logoT = K_PATH_IMAGES.'logo_t.jpg';
			$pdf->AddPage();
			$border = 0;
			$x = 18.65;
			$y = 9;
			$count = count($data);
			$numPages = round(count($data)/6);
			for ($i = 0; $i < $count; $i++) {
				$category = '';
				$program_name = $data[$i]['program_name'];
				$campus_name = $data[$i]['campus_name'];
				$department_name = $data[$i]['department_name'];
				$academic_year = $data[$i]['batch_year']+$data[$i]['part_no']-1;
				if($data[$i]['shift_name'] == 'EVENING') {
					$category = 'EVENING';
				} elseif(
				    $data[$i]['shift_name'] == 'MORNING' and 
				    ($data[$i]['category_name'] == 'SELF FINANCE' or $data[$i]['category_name'] == 'OTHER PROVINCES SELF FINANCE')) {
					$category = 'SELF FINANCE';
				} elseif(
				    $data[$i]['shift_name'] == 'MORNING' and 
				    $data[$i]['category_name'] == 'FOREIGN PKTAP') {
					$category = 'FOREIGN PKTAP';
				} elseif(
				    $data[$i]['shift_name'] == 'MORNING' and 
				    $data[$i]['category_name'] == 'FOREIGN SELF FINANCE') {
					$category = 'FOREIGN SELF';
				} elseif(
				    $data[$i]['shift_name'] == 'MORNING' and 
				    ($data[$i]['category_name'] != 'SELF FINANCE' or $data[$i]['category_name'] != 'OTHER PROVINCES SELF FINANCE' or $data[$i]['category_name'] != 'FOREIGN SELF FINANCE')) {
					$category = 'MERIT';
				}
				
				if($data[$i]['program_name'] == 'BS (PHYSICAL EDUCATION, HEALTH AND SPORTS SCIENCES)') {
					$program_name = 'BS (PHYSICAL EDU., HEALTH & SPORTS SCI.)';
				} elseif(
				    $data[$i]['program_name'] == 'B.B.A (HONS) (BUSINESS ADMINISTRATION)') {
					$program_name = 'B.B.A (HONS)';
				} elseif(
				    $data[$i]['program_name'] == 'B.Ed (B.ED (SECONDARY) 1.5-YEAR)') {
					$program_name = 'B.ED (SECONDARY) 1.5-YEAR';
				} elseif(
				    $data[$i]['program_name'] == 'B.Ed (B.ED (SECONDARY) 2.5-YEAR)') {
					$program_name = 'B.ED (SECONDARY) 2.5-YEAR';
				} elseif(
				    $data[$i]['program_name'] == 'B.Ed (B.ED (ELEMENTARY))') {
					$program_name = 'B.ED (ELEMENTARY)';
				} elseif(
				    $data[$i]['program_name'] == 'M.B.A (BUSINESS ADMINISTRATION)') {
					$program_name = 'M.B.A (4-Year Degree Program)';
				} elseif(
				    $data[$i]['program_name'] == 'M.B.A (HONS) (BUSINESS ADMINISTRATION)') {
					$program_name = 'M.B.A (HONS)';
				} elseif(
				    $data[$i]['program_name'] == 'LL.B (LAW)') {
					$campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
				} else {
					$program_name = $data[$i]['program_name'];
				}
				if($data[$i]['shift_name'] == 'EVENING') {
					if($program_name == 'B.B.A (HONS) (BUSINESS ADMINISTRATION)') {
						$program_name = 'B.B.A (HONS) - EVENING';
					} elseif($program_name == 'B.B.A (HONS) (BUSINESS ADMINISTRATION (OLD CAMPUS))') {
						$program_name = 'B.B.A (HONS) - OLD CAMPUS EVENING';
						$campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
						$department_name = 'ELSA KAZI CAMPUS, HYDERABAD';
					} elseif($program_name == 'M.B.A (EVENING) (BUSINESS ADMINISTRATION)') {
						$program_name = 'M.B.A (EVENING)';
					} elseif($program_name == 'BS (ENGLISH LANGUAGE & LITERATURE)') {
						$program_name = 'BS (ENGLISH LANG. & LITER.) - EVENING';
					} elseif($program_name == 'M.A (ENGLISH LANGUAGE & LITERATURE)') {
						$program_name = 'M.A (ENGLISH LANG. & LITER.) EVENING';
					} elseif($program_name == 'BS (MEDIA AND COMMUNICATION STUDIES)') {
						$program_name = 'BS (MEDIA AND COMM. STUDIES) - EVENING';
					
					} elseif($program_name == 'BS (MEDICAL LABORATORY TECHNOLOGY)') {
						$program_name = 'BS (MEDICAL LAB. TECHNOLOGY) - EVENING';
						
					} elseif($program_name == 'BS (ENGLISH LANGUAGE AND LITERATURE (OLD CAMPUS))') {
						$program_name = 'BS (ENGLISH LANG. & LITER.) OLD CAMPUS';
						$campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
						$department_name = 'ELSA KAZI CAMPUS, HYDERABAD';					
					} else {
						$program_name = $program_name.' - EVENING';
						$campus_name = $data[$i]['campus_name'];
						$department_name = $data[$i]['department_name'];
					}
				} else {
					$program_name = $program_name;
				}
				if($data[$i]['campus_name'] !== 'ALLAMA I.I. KAZI CAMPUS, JAMSHORO') {
					$department_name = $data[$i]['campus_name'];
				} else {
					$department_name = $data[$i]['department_name'];
				}
				$image_path = $this->studentmodel->getImagePath($data[$i]['candidate_id']);
				$pdf->Image($imageF, $x, $y, 85.6, 0, '','',true);
				$pdf->Image($imageB, $x+86.2, $y, 85.6, 0, '','',true);
				$pdf->Image($image_path, $x+3, $y+16.5, 20, 26.1, '','',true);
				$pdf->Image($sign, $x+61, $y+30, 22, 12, '','',true);
				$pdf->Image($logoT, $x+86.2, $y+16.7, 85.6, 25.6, $type = 'JPG', $link = '', $align = 'C', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, 0, $fitbox = 'CM', $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
				$style = array('border' => false,'vpadding' => 0,'hpadding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => array(255,255,255));
				$pdf->write2DBarcode($data[$i]['candidate_id'], 'QRCODE,H',$x+155, $y+1, 14, 14, $style, 'N', true);
				$pdf->setPageMark();
				$pdf->SetTextColor(0, 0, 0, 0);
				$pdf->SetFont('calibrib', '', 8.5);
				$pdf->MultiCell(68, 0, $campus_name, $border, 'L', 0, 1, $x+16, $y+11.1, true, 0, false, true, 0);			
				if(strlen($program_name)>30){	$pdf->SetFont('clrndnk', '', 9); } else { $pdf->SetFont('clrndnk', '', 10);	}
				$pdf->MultiCell(84.5, 0, $program_name, $border, 'C', 0, 1, $x+0.5, $y+44.8, true, 0, false, true, 0);
				$pdf->SetFont('clrndnk', '', 10);
				$pdf->MultiCell(83.5, 0, $data[$i]['part_name'].' - ACADEMIC YEAR '.$academic_year, $border, 'C', 0, 1, $x+1, $y+49.3, true, 0, false, true, 0);
				$pdf->SetFont('clrndnk', '', 9);
				$pdf->MultiCell(67, 13, $department_name, $border, 'C', 0, 1, $x+87.5, $y+1.5, true, 0, false, true, 13, 'M');
				$pdf->SetFont('calibrib', 'B', 4);
				$pdf->MultiCell(10.5, 0, 'Design by AYP', $border, 'R', 0, 1, $x+157, $y+51.2, true, 0, false, true, 0);
				$pdf->SetTextColor(100, 87, 0, 0);
				$pdf->SetFont('tangent', '', 7);
				$pdf->setFontSpacing(0.254);
				$pdf->MultiCell(10, 0, 'ID # ', $border, 'L', 0, 1, $x+68.2, $y+16.7, true, 0, false, true, 0);
				$pdf->MultiCell(20, 0, 'Name :', $border, 'L', 0, 1, $x+26.5, $y+17, true, 0, false, true, 0);
				$pdf->MultiCell(35, 0, 'Roll No :', $border, 'L', 0, 1, $x+26.5, $y+28.2, true, 0, false, true, 0);
				$pdf->MultiCell(25, 0, 'Valid Upto :', $border, 'L', 0, 1, $x+26.5, $y+36.5, true, 0, false, true, 0);
				if(!empty($data[$i]['fathers_name'])){ $pdf->MultiCell(35, 0, 'Father\'s Name :', $border, 'L', 0, 1, $x+90, $y+17, true, 0, false, true, 0); }
				if(!empty($data[$i]['surname'])){ $pdf->MultiCell(35, 0, 'Surname :', $border, 'L', 0, 1, $x+90, $y+23.4, true, 0, false, true, 0); }
				if(!empty($data[$i]['blood_group'])){ $pdf->MultiCell(35, 0, 'Blood Group :', $border, 'L', 0, 1, $x+90, $y+30.6, true, 0, false, true, 0); }
				if(!empty($data[$i]['family_mobile'])){ $pdf->MultiCell(30, 0, 'Emergency Contact :', $border, 'L', 0, 1, $x+140, $y+27.8, true, 0, false, true, 0); }
				$pdf->MultiCell(35, 0, 'Address :', $border, 'L', 0, 1, $x+90, $y+34, true, 0, false, true, 0);
				$pdf->MultiCell(15.3, 0, 'Category :', $border, 'L', 0, 1, $x+137, $y+16.7, true, 0, false, true, 0);
				$pdf->SetTextColor(255, 255, 255, 255);
				$pdf->SetFont('calibrib', 'B', 7.7);
				$pdf->setFontSpacing(0);
				$pdf->MultiCell(10, 0, $data[$i]['seat_no'], $border, 'L', 0, 1, $x+74, $y+16.6, true, 0, false, true, 0);
				$pdf->MultiCell(21, 0, $category, $border, 'L', 0, 1, $x+151.6, $y+16.6, true, 0, false, true, 0);
				$pdf->SetFont('sanskrit', 'B', 10);
				$pdf->MultiCell(58, 8, $data[$i]['candidate_name'], $border, 'L', 0, 1, $x+26.5, $y+19.3, true, 0, false, true, 0);
				$pdf->MultiCell(79, 0, $data[$i]['fathers_name'], $border, 'L', 0, 1, $x+90, $y+19.3, true, 0, false, true, 0);
				$pdf->MultiCell(79, 0, $data[$i]['surname'], $border, 'L', 0, 1, $x+90, $y+25.7, true, 0, false, true, 0);
				$pdf->SetFont('times', 'B', 9);
				$pdf->MultiCell(35, 0, $data[$i]['rollno'], $border, 'L', 0, 1, $x+26.5, $y+30.5, true, 0, false, true, 0);
				$pdf->SetFont('times', 'BI', 9);
				$pdf->MultiCell(15, 0, $data[$i]['blood_group'], $border, 'L', 0, 1, $x+110, $y+30.1, true, 0, false, true, 0);
				$pdf->MultiCell(30, 0, $data[$i]['family_mobile'], $border, 'L', 0, 1, $x+140, $y+30.1, true, 0, false, true, 0);
				$pdf->SetFont('times', 'i', 7);
				$pdf->MultiCell(79, 6.3, $data[$i]['present_postel_address'], $border, 'L', 0, 1, $x+90, $y+36.3, true, 0, false, true, 6.5);
				$pdf->SetFont('calibrib', 'B', 6.5);
				$pdf->MultiCell(25, 0, 'DIRECTOR ADMISSIONS', $border, 'L', 0, 1, $x+59.8, $y+39.7, true, 0, false, true, 0);
				$pdf->SetTextColor(0, 100, 100, 0);
				$pdf->SetFont('times', 'B', 8);
				$pdf->MultiCell(25, 0, 'DECEMBER '.$academic_year, $border, 'L', 0, 1, $x+26.5, $y+38.8, true, 0, false, true, 0);
				$pdf->SetFont('arialb', '', 11);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],0,1), $border, 'C', 0, 1, $x+91.55, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],1,1), $border, 'C', 0, 1, $x+96.58, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],2,1), $border, 'C', 0, 1, $x+101.61, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],3,1), $border, 'C', 0, 1, $x+106.64, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],4,1), $border, 'C', 0, 1, $x+111.67, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],5,1), $border, 'C', 0, 1, $x+116.7, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],6,1), $border, 'C', 0, 1, $x+121.73, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],7,1), $border, 'C', 0, 1, $x+126.76, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],8,1), $border, 'C', 0, 1, $x+131.79, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],9,1), $border, 'C', 0, 1, $x+136.82, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],10,1), $border, 'C', 0, 1, $x+141.85, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],11,1), $border, 'C', 0, 1, $x+146.88, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],12,1), $border, 'C', 0, 1, $x+151.91, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],13,1), $border, 'C', 0, 1, $x+156.94, $y+46, true, 0, false, true, 0);
				$pdf->MultiCell(4.5, 0, substr($data[$i]['cnic_no'],14,1), $border, 'C', 0, 1, $x+161.97, $y+46, true, 0, false, true, 0);
				$y = $y + 55.8;
				if(($i+1)%5==0){
				    if($i != $count-1){
					    $pdf->AddPage();
					    $x = 18.65;
					    $y = 9;
				    }
				}				
			}
			$pdf->lastPage();
			ob_end_clean();
			$pdf->Output('ID_Card_Report_'.$department_name.'.pdf', 'I');
			exit;
		}
	}
	
	public function idcardpaper(){
    	if(isset($_GET['s_id'])&&isset($_GET['pt_id']) && isset($_GET['sh_id']) && isset($_GET['c_id']) && isset($_GET['p_id']) && isset($_GET['pl_id'])){
            $session_id = isValidData($_GET['s_id']);
            $shift_id = isValidData($_GET['sh_id']);
            $prog_type_id = isValidData($_GET['pt_id']);
            $campus_id = isValidData($_GET['c_id']);
            $part_id = isValidData($_GET['p_id']);
            
            $prog_list_id = json_decode(urldecode($_GET['pl_id']));
            if(!is_numeric($prog_list_id)){
                $prog_list_id_str = join($prog_list_id,',');    
            }else{
                $prog_list_id_str=$_GET['pl_id'];
            }
    	}else{
    	    exit("<h1>Please Must Select All parameters</h1>");
    	}
    	$roll_no = null;
    	if(isset($_GET['roll_no'])){
    	     $roll_no = $_GET['roll_no'];
    	}
    	$response = $this->StudentReports_model->getStudentByProgram($campus_id,$prog_type_id,$session_id,$shift_id,$prog_list_id_str,$part_id,$roll_no);
        foreach ($response as $key=>$value) {				
        	$image_path  = $value['PROFILE_IMAGE'];
        	$data[] = $value;
        }		
    	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
    	$pdf->SetCreator(PDF_CREATOR);
    	$pdf->SetAuthor(PDF_AUTHOR);
    	$pdf->SetTitle('ID Card Report '.$data[0]['PROGRAM_TITLE'].' '.$data[0]['PART_NAME']);
    	$pdf->SetSubject('');
    	$pdf->SetKeywords('');
    	$pdf->setPrintHeader(false);
    	$pdf->setPrintFooter(false);
    	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    	$pdf->SetAutoPageBreak(FALSE);
    	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    		require_once(dirname(__FILE__).'/lang/eng.php');
    		$pdf->setLanguageArray($l);
    	}		
    	$imageF = K_PATH_IMAGES.'id_card_front.jpg';
    	$imageB = K_PATH_IMAGES.'id_card_back.jpg';
    	$sign = K_PATH_IMAGES.'DA_sign.png';
    	$logoT = K_PATH_IMAGES.'logo_t.jpg';
    			
    	$pdf->AddPage();
    	$border = 0;
    	$x = 18.65;
    	$y = 9;
    	$count = count($data);
    	$numPages = round(count($data)/6);
    	$prev_program = "";
    	$k = 0;
    	
    	for ($i = 0; $i < $count; $k++,$i++) {
    	    $campus_id = $data[$i]['CAMPUS_ID'];
    		$category = $data[$i]['CATEGORY_NAME_CARD'];
    		$program_name = $data[$i]['PROGRAM_TITLE_CARD'];
    		$campus_name = $data[$i]['CAMPUS_NAME'];
    		$department_name = $data[$i]['DEPT_NAME'];
    		$academic_year = $data[$i]['BATCH_YEAR']+$data[$i]['PART_NO']-1;
    		$prog_list_id = $data[$i]['PROG_LIST_ID'];
    		if($data[$i]['PROGRAM_TYPE_ID'] == 2 && $data[$i]['BATCH_YEAR'] >= 2024) $academic_year = $data[$i]['BATCH_YEAR']+$data[$i]['PART_NO']-3;
    		if($prog_list_id == 6 || $prog_list_id == 50 || $prog_list_id == 156) $academic_year = $data[$i]['BATCH_YEAR']+$data[$i]['PART_NO']-1;
    		$shif_name = $data[$i]['SHIFT_NAME'];
    		$shif_id = $data[$i]['SHIFT_ID'];
    		$category_name = $data[$i]['CATEGORY_NAME'];
    		$part_name = $data[$i]['PART_NAME'];
    		if($prog_list_id == 80 || $prog_list_id == 326) $part_name = $data[$i]['NAME_PHARM'];
    		$academic_year_part = $part_name.' - ACADEMIC YEAR '.$academic_year;
    		$valid_upto = 'DECEMBER '.$academic_year;
            if($prev_program!=""&&$prev_program!=$data[$i]['PROGRAM_TITLE']){
                $k=0;
                if($y != 9)
                $pdf->AddPage();
    			$x = 18.65;
    			$y = 9;
            }
            $prev_program = $data[$i]['PROGRAM_TITLE'];
            
            if($campus_name !== '".UNIVERSITY_NAME."') {
    			$department_name = $data[$i]['DEPT_NAME'];
    			$campus_name = $data[$i]['CAMPUS_NAME'];
    		} else {
    			$department_name = $data[$i]['DEPT_NAME'];
    			$campus_name = 'ALLAMA I.I. KAZI CAMPUS';
    		}
            
    		if($prog_list_id == 180 || $prog_list_id == 270 || $prog_list_id == 181 || $prog_list_id == 281 || $prog_list_id == 109 || $prog_list_id == 271) {
    		    if($campus_id == 1){
    		        $campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
    		    }
    			$academic_year_part = 'ACADEMIC YEAR '.$data[$i]['BATCH_YEAR'];
    		}
    		if($prog_list_id == 180 || $prog_list_id == 270) $valid_upto = 'MAY '.($academic_year+3);
    		if($prog_list_id == 181 || $prog_list_id == 281) $valid_upto = 'MAY '.($academic_year+4);
    		
    		
    		if($prog_list_id == 143 || $prog_list_id == 150 || $prog_list_id == 156 || $prog_list_id == 263) {
    		    $campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
    		}
    		
    		if($shift_id == 2) {
    		    if($prog_list_id == 99) $program_name = 'BS (MEDIA & COMM. STUDIES)';
    		    if($prog_list_id == 166) $program_name = 'BS (MEDICAL LAB. TECHNOLOGY)';
    		    if($prog_list_id == 259 || $prog_list_id == 263) $program_name = 'BS (ENGLISH LANG. & LIT.)';
    		    if($prog_list_id == 268) $program_name = 'BS (COMPUTER SCIENCE) P.E.';
    		    if($prog_list_id == 269) $program_name = 'BS (COMPUTER SCIENCE) P.M.';
    		    $program_name = $program_name.' - EVENING';
    		}
    		    
            $image_path = K_PATH_PROFLE_IMAGES.$data[$i]['PROFILE_IMAGE'];
            $pdf->Image($imageF, $x, $y, 85.6, 0, '','',true);
    		$pdf->Image($imageB, $x+86.2, $y, 85.6, 0, '','',true);
    		$pdf->Image($image_path, $x+3, $y+16.5, 20, 26.1, '','',true);
    		$pdf->Image($sign, $x+61, $y+32.5, 22, 8, '','',true);
    		$pdf->Image($logoT, $x+86.2, $y+16.7, 85.6, 25.6, $type = 'JPG', $link = '', $align = 'C', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, 0, $fitbox = 'CM', $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
    		$style = array('border' => false,'vpadding' => 0,'hpadding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => array(255,255,255));
    		$qr_data = json_encode(array("USER_ID"=>$data[$i]['USER_ID'],"APPLICATION_ID"=>$data[$i]['APPLICATION_ID']));
            $pdf->write2DBarcode($qr_data, 'QRCODE,H',$x+155, $y+1, 14, 14, $style, 'N', true);
    		$pdf->setPageMark();
    		$pdf->SetTextColor(0, 0, 0, 0);
    		$pdf->SetFont('calibrib', '', 8.5);
    		$pdf->MultiCell(68, 0, $campus_name, $border, 'L', 0, 1, $x+16, $y+11.1, true, 0, false, true, 0);			
    		if(strlen($program_name)>30){	$pdf->SetFont('clrndnk', '', 9); } else { $pdf->SetFont('clrndnk', '', 10);	}
    		$pdf->MultiCell(84.5, 0, $program_name, $border, 'C', 0, 1, $x+0.5, $y+44.8, true, 0, false, true, 0);
    		$pdf->SetFont('clrndnk', '', 10);
    		$pdf->MultiCell(83.5, 0, $academic_year_part, $border, 'C', 0, 1, $x+1, $y+49.3, true, 0, false, true, 0);
    		$pdf->SetFont('clrndnk', '', 9);
    		$pdf->MultiCell(67, 13, $department_name, $border, 'C', 0, 1, $x+87.5, $y+1.5, true, 0, false, true, 13, 'M');
    		$pdf->SetFont('calibrib', 'B', 4);
    		$pdf->MultiCell(10.5, 0, 'Design by AYP', $border, 'R', 0, 1, $x+157, $y+51.2, true, 0, false, true, 0);
    		$pdf->SetTextColor(100, 87, 0, 0);
    		$pdf->SetFont('tangent', '', 7);
    		$pdf->setFontSpacing(0.254);
			$pdf->MultiCell(10, 0, 'ID # ', $border, 'L', 0, 1, $x+68.2, $y+16.7, true, 0, false, true, 0);
			$pdf->MultiCell(20, 0, 'Name :', $border, 'L', 0, 1, $x+26.5, $y+17, true, 0, false, true, 0);
			$pdf->MultiCell(35, 0, 'Roll No :', $border, 'L', 0, 1, $x+26.5, $y+28.2, true, 0, false, true, 0);
			$pdf->MultiCell(25, 0, 'Valid Upto :', $border, 'L', 0, 1, $x+26.5, $y+36.5, true, 0, false, true, 0);
			if(!empty($data[$i]['FNAME'])){ $pdf->MultiCell(35, 0, 'Father\'s Name :', $border, 'L', 0, 1, $x+90, $y+17, true, 0, false, true, 0); }
			if(!empty($data[$i]['LAST_NAME'])){ $pdf->MultiCell(35, 0, 'Surname :', $border, 'L', 0, 1, $x+90, $y+23.4, true, 0, false, true, 0); }
			if(!empty($data[$i]['BLOOD_GROUP'])){ $pdf->MultiCell(35, 0, 'Blood Group :', $border, 'L', 0, 1, $x+90, $y+30.6, true, 0, false, true, 0); }
			if(!empty($data[$i]['FAMILY_CONTACT_NO'])){ $pdf->MultiCell(30, 0, 'Emergency Contact :', $border, 'L', 0, 1, $x+140, $y+27.8, true, 0, false, true, 0); }
			$pdf->MultiCell(35, 0, 'Address :', $border, 'L', 0, 1, $x+90, $y+34, true, 0, false, true, 0);
			$pdf->MultiCell(15.3, 0, 'Category :', $border, 'L', 0, 1, $x+137, $y+16.7, true, 0, false, true, 0);
			$pdf->SetTextColor(255, 255, 255, 255);
			$pdf->SetFont('calibrib', 'B', 7.7);
			$pdf->setFontSpacing(0);
			$pdf->MultiCell(12, 0, $data[$i]['APPLICATION_ID'], $border, 'L', 0, 1, $x+74, $y+16.6, true, 0, false, true, 0);
			$pdf->MultiCell(21, 0, $category, $border, 'L', 0, 1, $x+151.6, $y+16.6, true, 0, false, true, 0);
			$pdf->SetFont('sanskrit', 'B', 10);
			$pdf->MultiCell(58, 8, $data[$i]['FIRST_NAME'], $border, 'L', 0, 1, $x+26.5, $y+19.3, true, 0, false, true, 0);
			$pdf->MultiCell(79, 0, $data[$i]['FNAME'], $border, 'L', 0, 1, $x+90, $y+19.3, true, 0, false, true, 0);
			$pdf->MultiCell(79, 0, $data[$i]['LAST_NAME'], $border, 'L', 0, 1, $x+90, $y+25.7, true, 0, false, true, 0);
			$pdf->SetFont('times', 'B', 9);
			$pdf->MultiCell(35, 0, $data[$i]['ROLL_NO'], $border, 'L', 0, 1, $x+26.5, $y+30.5, true, 0, false, true, 0);
			$pdf->SetFont('times', 'BI', 9);
			$pdf->MultiCell(15, 0, $data[$i]['BLOOD_GROUP'], $border, 'L', 0, 1, $x+110, $y+30.1, true, 0, false, true, 0);
			$pdf->MultiCell(30, 0, $data[$i]['FAMILY_CONTACT_NO'], $border, 'L', 0, 1, $x+140, $y+30.1, true, 0, false, true, 0);
			$pdf->SetFont('times', 'i', 7);
			$pdf->MultiCell(79, 6.3, $data[$i]['HOME_ADDRESS'], $border, 'L', 0, 1, $x+90, $y+36.3, true, 0, false, true, 6.5);
			$pdf->SetFont('calibrib', 'B', 6.5);
			$pdf->MultiCell(25, 0, 'DIRECTOR ADMISSIONS', $border, 'L', 0, 1, $x+59.8, $y+39.7, true, 0, false, true, 0);
			$pdf->SetTextColor(0, 100, 100, 0);
			$pdf->SetFont('times', 'B', 8);
			$pdf->MultiCell(25, 0, $valid_upto, $border, 'L', 0, 1, $x+26.5, $y+38.8, true, 0, false, true, 0);
			$pdf->SetFont('arialb', '', 11);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],0,1), $border, 'C', 0, 1, $x+91.55, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],1,1), $border, 'C', 0, 1, $x+96.58, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],2,1), $border, 'C', 0, 1, $x+101.61, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],3,1), $border, 'C', 0, 1, $x+106.64, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],4,1), $border, 'C', 0, 1, $x+111.67, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, "-", $border, 'C', 0, 1, $x+116.7, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],5,1), $border, 'C', 0, 1, $x+121.73, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],6,1), $border, 'C', 0, 1, $x+126.76, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],7,1), $border, 'C', 0, 1, $x+131.79, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],8,1), $border, 'C', 0, 1, $x+136.82, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],9,1), $border, 'C', 0, 1, $x+141.85, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],10,1), $border, 'C', 0, 1, $x+146.88, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],11,1), $border, 'C', 0, 1, $x+151.91, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, "-", $border, 'C', 0, 1, $x+156.94, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($data[$i]['CNIC_NO'],12,1), $border, 'C', 0, 1, $x+161.97, $y+46, true, 0, false, true, 0);
			$y = $y + 55.8;
			if(($k+1)%5==0){
			    if($i != $count-1){
				    $pdf->AddPage();
				    $x = 18.65;
				    $y = 9;
				    $k=-1;
				}
			}							
		}
    	$pdf->lastPage();
    	ob_end_clean();
    	$pdf->Output('ID_Card_Report_'.$department_name.'.pdf', 'I');
    	exit;
	}
	
	public function dateWiseCard($DateFrom, $DateTo){
	    $this->legacy_db->select('c.NAME AS CAMPUS_NAME, app.APPLICATION_ID,ur.USER_ID, ur.FIRST_NAME, ur.FNAME, ur.LAST_NAME, 
        pl.PROGRAM_TITLE, ct.CATEGORY_NAME, ur.BLOOD_GROUP, ur.HOME_ADDRESS, ur.CNIC_NO, ur.FAMILY_CONTACT_NO, sl.ROLL_NO, ur.PROFILE_IMAGE, p.NAME AS PART_NAME, p.NAME_PHARM, dt.DEPT_NAME,
        se.YEAR AS BATCH_YEAR, p.PART_NO, sh.SHIFT_NAME, pl.PROG_LIST_ID, ct.CATEGORY_NAME_CARD, pl.PROGRAM_TITLE_CARD, c.CAMPUS_ID, sh.SHIFT_ID');
	    $this->legacy_db->from('fee_ledger fl');
	    $this->legacy_db->join('selection_list sl','fl.SELECTION_LIST_ID = sl.SELECTION_LIST_ID');
	    $this->legacy_db->join('fee_program_list fpl','fl.FEE_PROG_LIST_ID = fpl.FEE_PROG_LIST_ID');
	    $this->legacy_db->join('applications app','sl.APPLICATION_ID = app.APPLICATION_ID');
	    $this->legacy_db->join('users_reg ur','ur.USER_ID = app.USER_ID');
	    $this->legacy_db->join('admission_session ads','ads.ADMISSION_SESSION_ID = app.ADMISSION_SESSION_ID');
	    $this->legacy_db->join('sessions se','se.SESSION_ID = ads.SESSION_ID');
	    $this->legacy_db->join('campus c','c.CAMPUS_ID = ads.CAMPUS_ID');
	    $this->legacy_db->join('shift sh','sl.SHIFT_ID = sh.SHIFT_ID');
	    $this->legacy_db->join('program_list pl','sl.PROG_LIST_ID = pl.PROG_LIST_ID');
	    $this->legacy_db->join('category ct','sl.CATEGORY_ID = ct.CATEGORY_ID');
	    $this->legacy_db->join('part p','fpl.PART_ID = p.PART_ID');
	    $this->legacy_db->join('shift_program_mapping spm','c.CAMPUS_ID = spm.CAMPUS_ID AND sh.SHIFT_ID = spm.SHIFT_ID AND pl.PROG_LIST_ID = spm.PROG_LIST_ID');
	    $this->legacy_db->join('departments dt','spm.DEPT_ID = dt.DEPT_ID');
	    $this->legacy_db->where('fl.DATE >=',$DateFrom);
	    $this->legacy_db->where('fl.DATE <=',$DateTo);
	    $this->legacy_db->where('p.PART_NO >',1);
	    $this->legacy_db->where('fl.CHALLAN_NO >',212428000);
	    //$this->legacy_db->where('se.YEAR >',2020);
	    //$this->legacy_db->where('sh.SHIFT_ID',1);
	    $this->legacy_db->order_by('c.CAMPUS_ID, dt.DEPT_NAME, sh.SHIFT_ID, spm.PROG_CODE, p.PART_NO, sl.ROLL_NO_CODE');
	    $response = $this->legacy_db->get()->result_array();
	   // prePrint($response);
	   // exit;
    	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
    	$pdf->SetCreator(PDF_CREATOR);
    	$pdf->SetAuthor(PDF_AUTHOR);
    	$pdf->SetTitle('ID Card Report');
    	$pdf->SetSubject('');
    	$pdf->SetKeywords('');
    	$pdf->setPrintHeader(false);
    	$pdf->setPrintFooter(false);
    	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    	$pdf->SetAutoPageBreak(FALSE);
    	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    		require_once(dirname(__FILE__).'/lang/eng.php');
    		$pdf->setLanguageArray($l);
    	}		
    	$imageF = K_PATH_IMAGES.'id_card_front.jpg';
    	$imageB = K_PATH_IMAGES.'id_card_back.jpg';
    	$sign = K_PATH_IMAGES.'DA_sign.png';
    	$logoT = K_PATH_IMAGES.'logo_t.jpg';
    			
    	$pdf->AddPage();
    	$border = 0;
    	$x = 18.65;
    	$y = 9;
    	
	    foreach ($response as $key=>$value) {
    		$academic_year = $value['BATCH_YEAR']+$value['PART_NO']-1;
    		if($academic_year != 2024) continue;
	        $campus_id = $value['CAMPUS_ID'];
        	$category = $value['CATEGORY_NAME_CARD'];
    		$program_name = $value['PROGRAM_TITLE_CARD'];
    		$campus_name = $value['CAMPUS_NAME'];
    		$department_name = $value['DEPT_NAME'];
    		$prog_list_id = $value['PROG_LIST_ID'];
    		$part_name = $value['PART_NAME'];
    		if($prog_list_id == 80 || $prog_list_id == 326) $part_name = $value['NAME_PHARM'];
    		$academic_year_part = $part_name.' - ACADEMIC YEAR '.$academic_year;
    		if($value['PROGRAM_TYPE_ID'] == 2 && $value['BATCH_YEAR'] >= 2024) $academic_year = $value['BATCH_YEAR']+$value['PART_NO']-3;
    		if($prog_list_id == 6 || $prog_list_id == 50 || $prog_list_id == 156) $academic_year = $value['BATCH_YEAR']+$value['PART_NO']-1;
    		$shif_name = $value['SHIFT_NAME'];
    		$shift_id = $value['SHIFT_ID'];
    		$category_name = $value['CATEGORY_NAME'];
    		

        	$image_path  = K_PATH_PROFLE_IMAGES.$value['PROFILE_IMAGE'];
    		$valid_upto = 'DECEMBER '.$academic_year;
    		
    		if($campus_name !== UNIVERSITY_NAME) {
    			$department_name = $value['DEPT_NAME'];
    			$campus_name = $value['CAMPUS_NAME'];
    		} else {
    			$department_name = $value['DEPT_NAME'];
    			$campus_name = UNIVERSITY_NAME;
    		}
            
    		if($prog_list_id == 180 || $prog_list_id == 270 || $prog_list_id == 181 || $prog_list_id == 281 || $prog_list_id == 109 || $prog_list_id == 271) {
    		    if($campus_id == 1){
    		        $campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
    		    }
    			$academic_year_part = 'ACADEMIC YEAR '.$value['BATCH_YEAR'];
    		}
    		if($prog_list_id == 180 || $prog_list_id == 270) $valid_upto = 'MAY '.($academic_year+3);
    		if($prog_list_id == 181 || $prog_list_id == 281) $valid_upto = 'MAY '.($academic_year+4);
    		
    		
    		if($prog_list_id == 143 || $prog_list_id == 150 || $prog_list_id == 156 || $prog_list_id == 263) {
    		    $campus_name = 'ELSA KAZI CAMPUS, HYDERABAD';
    		}
    		
    		if($shift_id == 2) {
    		    if($prog_list_id == 99) $program_name = 'BS (MEDIA & COMM. STUDIES)';
    		    if($prog_list_id == 166) $program_name = 'BS (MEDICAL LAB. TECHNOLOGY)';
    		    if($prog_list_id == 259 || $prog_list_id == 263) $program_name = 'BS (ENGLISH LANG. & LIT.)';
    		    if($prog_list_id == 268) $program_name = 'BS (COMPUTER SCIENCE) P.E.';
    		    if($prog_list_id == 269) $program_name = 'BS (COMPUTER SCIENCE) P.M.';
    		    $program_name = $program_name.' - EVENING';
    		}
    		
    		$pdf->Image($imageF, $x, $y, 85.6, 0, '','',true);
    		$pdf->Image($imageB, $x+86.2, $y, 85.6, 0, '','',true);
    		$pdf->Image($image_path, $x+3, $y+16.5, 20, 26.1, '','',true);
    		$pdf->Image($sign, $x+61, $y+32.5, 22, 8, '','',true);
    		$pdf->Image($logoT, $x+86.2, $y+16.7, 85.6, 25.6, $type = 'JPG', $link = '', $align = 'C', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, 0, $fitbox = 'CM', $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
    		$style = array('border' => false,'vpadding' => 0,'hpadding' => 0,'fgcolor' => array(0,0,0),'bgcolor' => array(255,255,255));
    		$qr_data = json_encode(array("USER_ID"=>$value['USER_ID'],"APPLICATION_ID"=>$value['APPLICATION_ID']));
            $pdf->write2DBarcode($qr_data, 'QRCODE,H',$x+155, $y+1, 14, 14, $style, 'N', true);
    		$pdf->setPageMark();
    		$pdf->SetTextColor(0, 0, 0, 0);
    		$pdf->SetFont('calibrib', '', 8.5);
    		$pdf->MultiCell(68, 0, $campus_name, $border, 'L', 0, 1, $x+16, $y+11.1, true, 0, false, true, 0);			
    		if(strlen($program_name)>30){ $pdf->SetFont('clrndnk', '', 9); } else { $pdf->SetFont('clrndnk', '', 10);	}
    		$pdf->MultiCell(84.5, 0, $program_name, $border, 'C', 0, 1, $x+0.5, $y+44.8, true, 0, false, true, 0);
    		$pdf->SetFont('clrndnk', '', 10);
    		$pdf->MultiCell(83.5, 0, $academic_year_part, $border, 'C', 0, 1, $x+1, $y+49.3, true, 0, false, true, 0);
    		$pdf->SetFont('clrndnk', '', 9);
    		$pdf->MultiCell(67, 13, $department_name, $border, 'C', 0, 1, $x+87.5, $y+1.5, true, 0, false, true, 13, 'M');
    		$pdf->SetFont('calibrib', 'B', 4);
    		$pdf->MultiCell(10.5, 0, 'Design by AYP', $border, 'R', 0, 1, $x+157, $y+51.2, true, 0, false, true, 0);
    		$pdf->SetTextColor(100, 87, 0, 0);
    		$pdf->SetFont('tangent', '', 7);
    		$pdf->setFontSpacing(0.254);
			$pdf->MultiCell(10, 0, 'ID # ', $border, 'L', 0, 1, $x+68.2, $y+16.7, true, 0, false, true, 0);
			$pdf->MultiCell(20, 0, 'Name :', $border, 'L', 0, 1, $x+26.5, $y+17, true, 0, false, true, 0);
			$pdf->MultiCell(35, 0, 'Roll No :', $border, 'L', 0, 1, $x+26.5, $y+28.2, true, 0, false, true, 0);
			$pdf->MultiCell(25, 0, 'Valid Upto :', $border, 'L', 0, 1, $x+26.5, $y+36.5, true, 0, false, true, 0);
			if(!empty($value['FNAME'])){ $pdf->MultiCell(35, 0, 'Father\'s Name :', $border, 'L', 0, 1, $x+90, $y+17, true, 0, false, true, 0); }
			if(!empty($value['LAST_NAME'])){ $pdf->MultiCell(35, 0, 'Surname :', $border, 'L', 0, 1, $x+90, $y+23.4, true, 0, false, true, 0); }
			if(!empty($value['BLOOD_GROUP'])){ $pdf->MultiCell(35, 0, 'Blood Group :', $border, 'L', 0, 1, $x+90, $y+30.6, true, 0, false, true, 0); }
			if(!empty($value['FAMILY_CONTACT_NO'])){ $pdf->MultiCell(30, 0, 'Emergency Contact :', $border, 'L', 0, 1, $x+140, $y+27.8, true, 0, false, true, 0); }
			$pdf->MultiCell(35, 0, 'Address :', $border, 'L', 0, 1, $x+90, $y+34, true, 0, false, true, 0);
			$pdf->MultiCell(15.3, 0, 'Category :', $border, 'L', 0, 1, $x+137, $y+16.7, true, 0, false, true, 0);
			$pdf->SetTextColor(255, 255, 255, 255);
			$pdf->SetFont('calibrib', 'B', 7.7);
			$pdf->setFontSpacing(0);
			$pdf->MultiCell(12, 0, $value['APPLICATION_ID'], $border, 'L', 0, 1, $x+74, $y+16.6, true, 0, false, true, 0);
			$pdf->MultiCell(21, 0, $category, $border, 'L', 0, 1, $x+151.6, $y+16.6, true, 0, false, true, 0);
			$pdf->SetFont('sanskrit', 'B', 10);
			$pdf->MultiCell(58, 8, $value['FIRST_NAME'], $border, 'L', 0, 1, $x+26.5, $y+19.3, true, 0, false, true, 0);
			$pdf->MultiCell(79, 0, $value['FNAME'], $border, 'L', 0, 1, $x+90, $y+19.3, true, 0, false, true, 0);
			$pdf->MultiCell(79, 0, $value['LAST_NAME'], $border, 'L', 0, 1, $x+90, $y+25.7, true, 0, false, true, 0);
			$pdf->SetFont('times', 'B', 9);
			$pdf->MultiCell(35, 0, $value['ROLL_NO'], $border, 'L', 0, 1, $x+26.5, $y+30.5, true, 0, false, true, 0);
			$pdf->SetFont('times', 'BI', 9);
			$pdf->MultiCell(15, 0, $value['BLOOD_GROUP'], $border, 'L', 0, 1, $x+110, $y+30.1, true, 0, false, true, 0);
			$pdf->MultiCell(30, 0, $value['FAMILY_CONTACT_NO'], $border, 'L', 0, 1, $x+140, $y+30.1, true, 0, false, true, 0);
			$pdf->SetFont('times', 'i', 7);
			$pdf->MultiCell(79, 6.3, $value['HOME_ADDRESS'], $border, 'L', 0, 1, $x+90, $y+36.3, true, 0, false, true, 6.5);
			$pdf->SetFont('calibrib', 'B', 6.5);
			$pdf->MultiCell(25, 0, 'DIRECTOR ADMISSIONS', $border, 'L', 0, 1, $x+59.8, $y+39.7, true, 0, false, true, 0);
			$pdf->SetTextColor(0, 100, 100, 0);
			$pdf->SetFont('times', 'B', 8);
			$pdf->MultiCell(25, 0, $valid_upto, $border, 'L', 0, 1, $x+26.5, $y+38.8, true, 0, false, true, 0);
			$pdf->SetFont('arialb', '', 11);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],0,1), $border, 'C', 0, 1, $x+91.55, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],1,1), $border, 'C', 0, 1, $x+96.58, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],2,1), $border, 'C', 0, 1, $x+101.61, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],3,1), $border, 'C', 0, 1, $x+106.64, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],4,1), $border, 'C', 0, 1, $x+111.67, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, "-", $border, 'C', 0, 1, $x+116.7, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],5,1), $border, 'C', 0, 1, $x+121.73, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],6,1), $border, 'C', 0, 1, $x+126.76, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],7,1), $border, 'C', 0, 1, $x+131.79, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],8,1), $border, 'C', 0, 1, $x+136.82, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],9,1), $border, 'C', 0, 1, $x+141.85, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],10,1), $border, 'C', 0, 1, $x+146.88, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],11,1), $border, 'C', 0, 1, $x+151.91, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, "-", $border, 'C', 0, 1, $x+156.94, $y+46, true, 0, false, true, 0);
			$pdf->MultiCell(4.5, 0, substr($value['CNIC_NO'],12,1), $border, 'C', 0, 1, $x+161.97, $y+46, true, 0, false, true, 0);
    		
    		$y = $y + 55.8;
			if(($key+1)%5==0){
			    if($i != $count-1){
				    $pdf->AddPage();
				    $x = 18.65;
				    $y = 9;
				    $key=-1;
				}
			}					
    		
        }
            //if($prev_program != "" && $prev_program != $data[$i]['PROGRAM_TITLE']){
    //             $k=0;
    //             if($y != 9)
    //             $pdf->AddPage();
    // 			$x = 18.65;
    // 			$y = 9;
            //}

    	$pdf->lastPage();
    	ob_end_clean();
    	$pdf->Output('ID_Card_Report.pdf', 'I');
    	exit;
	}
	
	public function paidChallanReport($search_by=0,$search_value=0){
	 
	    if( $search_by<0 || $search_value<0) {
	        exit("Invalid input");
	    }
	       
	    $search_by = isValidData($search_by);
	    $search_value = isValidData($search_value);
	    $studentInfo = $this->StudentReports_model->getStudentInfo($search_by,$search_value);
	    $studentAccount = $this->StudentReports_model->getStudentPaidChallan($search_by,$search_value);
	    $refundstudentAccount = $this->StudentReports_model->getStudentRefundChallan($search_by,$search_value);
	   
	    $program_type_id = $studentInfo['PROGRAM_TYPE_ID'];
	    $selection_list_id = $studentInfo['SELECTION_LIST_ID'];
	    $user_id = $studentInfo['USER_ID'];
	    $application_id = $studentInfo['APPLICATION_ID'];
	    $session_id = $studentInfo['SESSION_ID'];
        $shift_id = $studentInfo['SHIFT_ID'];
	    $stufeestructure = $this->FeeChallan_model->getFeeStructure($program_type_id,$selection_list_id);
	    $enrollmentFee = $this->FeeChallan_model->getEnrollmentFee($user_id,$application_id,$session_id,$program_type_id);
	    //echo "<pre>";
	    //print_r($stufeestructure);
	    //print_r($studentAccount);
	    //exit;
	   
	    $qr_code = $studentInfo['APPLICATION_ID'];
	   // $qr_code = json_encode($qr_code);
	   
	    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle('Paid_Challan_Report_'.$studentInfo['APPLICATION_ID'].'.pdf');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(FALSE);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}		
        $logo = K_PATH_IMAGES.'avatar-1.jpg';
		$phone = K_PATH_IMAGES.'phone.jpg';
		$email = K_PATH_IMAGES.'email.jpg';
		$url = K_PATH_IMAGES.'url.jpg';
		$style = array('border' => false,'padding' => 'auto','fgcolor' => array(0,0,0),'bgcolor' => false,'position' => 'R','module_width' => 1,'module_height' => 1);
		$tDate=date("F j, Y");
		$pdf->AddPage();
		//****************** HEADER START ***********************//
	    $pdf->Image($logo, 23, 10, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	    $pdf->SetXY(50, 13);
		$pdf->SetFont('Times', 'B', 24);
		$pdf->Cell(0, 0, 'UNIVERSITY OF SINDH', 0, 1, 'L', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(50, 23);
		$pdf->SetFont('Times', '', 16);
		$pdf->Cell(0, 0, 'Jamshoro, Sindh, Pakistan.', 0, 0, 'L', 0, '', 0, false, 'T', 'T');
		$pdf->Ln(2);		
		$pdf->SetFont('helvetica', '', 8);
		$pdf->SetXY(50, 24);
        $pdf->Cell(121, 0, '022-9213166', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 29);
		$pdf->Cell(121, 0, 'dir.adms@usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 34);
		$pdf->Cell(121, 0, 'www.usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');		
        $pdf->Image($phone, 173, 20, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($email, 173, 25, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($url, 173, 30, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);
	    $pdf->write2DBarcode($qr_code, 'QRCODE,L', 0, 17, 20, 20, $style, 'B');
	    $pdf->SetXY(15, 33);
		$pdf->SetFont('Times', '', 10);
		$pdf->Cell(35, 0, 'Directorate of', 0, 1, 'C', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(15, 37);
		$pdf->SetFont('Times', 'B', 12);
		$pdf->Cell(35, 0, 'Admissions', 0, 0, 'C', 0, '', 0, false, 'T', 'T');
		$pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 10, 'Dated : '.$tDate, 0, 1, 'R', 0, '', 0, false, 'T', 'T');
		$pdf->Cell(0, 5, '', 'T', 1, 'C', 0, '', 0, false, 'B', 'B');
		//****************** HEADER END ***********************//
		
		$boarder = 1;
		$x = 20;
		$y = 15;
		//Cell(w, h = 0, txt = '', border = 0, ln = 0, align = '', fill = 0, link = nil, stretch = 0, ignore_min_height = false, calign = 'T', valign = 'M')
		$pdf->SetXY(15, 55);
		$pdf->SetFont('Times', 'B', 16);
		//$pdf->Cell(180, 0, 'ADMISSION FEES PAID CHALLAN DETAILS', 0, 0, 'C', 0, '', 0, false, 'T', 'T');
		$pdf->MultiCell(180, 0, 'ADMISSION FEES PAID CHALLAN DETAILS', 0, 'C', 0, 1, $x, $y+30, true, 0, false, true);
		$pdf->SetFont('Times', '', 10);
		$pdf->MultiCell(40, 7, 'Application ID :', 0, 'R', 0, 1, $x, $y+40, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(30, 7, 'CNIC No. :', 0, 'R', 0, 1, $x+80, $y+40, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Student\'s Name :', 0, 'R', 0, 1, $x, $y+48, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Father\'s Name :', 0, 'R', 0, 1, $x, $y+56, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Surname : ', 0, 'R', 0, 1, $x, $y+64, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Degree Program :', 0, 'R', 0, 1, $x, $y+72, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Roll No. :', 0, 'R', 0, 1, $x, $y+80, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Category :', 0, 'R', 0, 1, $x, $y+88, true, 0, false, true, 7, 'M');
		
		$pdf->SetFont('Times', 'B', 12);
		$pdf->MultiCell(125, 7, $studentInfo['FIRST_NAME'], $boarder, 'L', 0, 1, $x+42, $y+48, true, 0, false, true, 7, 'M');
		$pdf->SetFont('Times', '', 12);
		$pdf->MultiCell(35, 7, $studentInfo['APPLICATION_ID'], $boarder, 'L', 0, 1, $x+42, $y+40, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(55, 7, $studentInfo['CNIC_NO'], $boarder, 'L', 0, 1, $x+112, $y+40, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['FNAME'], $boarder, 'L', 0, 1, $x+42, $y+56, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['LAST_NAME'], $boarder, 'L', 0, 1, $x+42, $y+64, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['PROGRAM_TITLE'], $boarder, 'L', 0, 1, $x+42, $y+72, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['ROLL_NO'], $boarder, 'L', 0, 1, $x+42, $y+80, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['CATEGORY_NAME'], $boarder, 'L', 0, 1, $x+42, $y+88, true, 0, false, true, 7, 'M');
		
		//****************** FEE STRUCTURE START ***********************//
		$x = 15;
		$pdf->SetFont('Times', 'BU', 13);
		$pdf->MultiCell(125, 7, 'Fees Structure', 0, 'L', 0, 1, $x, $y+95, true, 0, false, true, 7, 'M');
		
		$pdf->SetFillColor(0, 0, 0, 30);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0, 100);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('Times', '', 10);
        $header_fs = array('Class','Semester','Fee Amount','Enrolment / Eligibility Fee','Late Fee','Total Fee');
		$header_width_fs = array(45,25,25,40,25,25);
        $num_headers_fs = count($header_fs);
        for($i = 0; $i < $num_headers_fs; ++$i) {
        $pdf->MultiCell($header_width_fs[$i], 7, $header_fs[$i], 1, 'L', 1, 1, $x, $y+102, true, 0, false, true, 7, 'M');
        $x = $x+$header_width_fs[$i];
        }
        $sum_fees_amount = 0;
        foreach($stufeestructure as $fee){
            $x = 15;
            if($session_id == 1 && $program_type_id == 2 && $shift_id == 2) {
                $enr_fee = 0;
            } else {
                if($fee['PART_NO']==1 && ($fee['SEMESTER_ID']==1 || $fee['SEMESTER_ID']==11)) {
                    $enr_fee = $enrollmentFee[0]['AMOUNT'];
                }else{
                    $enr_fee = 0;
                };
            }
            
            $late_fee = $fee['LATE_FEE'] ? $fee['LATE_FEE'] : "";
            $fees = $fee['FEE_AMOUNT']+$enr_fee+$late_fee;
            $sum_fees_amount+=$fees;
            $pdf->MultiCell($header_width_fs[0], 7, $fee['PART_NAME'], $boarder, 'L', 0, 1, $x, $y+109, true, 0, false, true, 7, 'M');
            $pdf->MultiCell($header_width_fs[1], 7, $fee['SEMESTER_NAME'], $boarder, 'L', 0, 1, $x+$header_width_fs[0], $y+109, true, 0, false, true, 7, 'M');
            $pdf->MultiCell($header_width_fs[2], 7, $fee['FEE_AMOUNT'], $boarder, 'R', 0, 1, $x+$header_width_fs[0]+$header_width_fs[1], $y+109, true, 0, false, true, 7, 'M');
            $pdf->MultiCell($header_width_fs[3], 7, $enr_fee, $boarder, 'R', 0, 1, $x+$header_width_fs[0]+$header_width_fs[1]+$header_width_fs[2], $y+109, true, 0, false, true, 7, 'M');
            $pdf->MultiCell($header_width_fs[4], 7, $late_fee, $boarder, 'R', 0, 1, $x+$header_width_fs[0]+$header_width_fs[1]+$header_width_fs[2]+$header_width_fs[3], $y+109, true, 0, false, true, 7, 'M');
            $pdf->MultiCell($header_width_fs[4], 7, $fee['FEE_AMOUNT']+$enr_fee+$late_fee, $boarder, 'R', 0, 1, $x+$header_width_fs[0]+$header_width_fs[1]+$header_width_fs[2]+$header_width_fs[3]+$header_width_fs[4], $y+109, true, 0, false, true, 7, 'M');
            $y = $y + 7;
        }
        
        $pdf->SetFont('Times', 'B', 13);
		$pdf->MultiCell(array_sum($header_width_fs), 7, 'Total Admission Fees Amount : Rs. '.number_format($sum_fees_amount,2), 0, 'R', 0, 1, $x, $y+109, true, 0, false, true, 7, 'M');
		//****************** FEE STRUCTURE END ***********************//
		
		//****************** PAID FEES CHALLAN START ***********************//
		$x = 15;
		$pdf->SetFont('Times', 'BU', 13);
		$pdf->MultiCell(125, 7, 'Paid Admission Fees Record', 0, 'L', 0, 1, $x, $y+116, true, 0, false, true, 7, 'M');
		
		$pdf->SetFillColor(0, 0, 0, 30);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0, 100);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('Times', '', 10);
        $header = array('Class','Challan No.','Paid Amount','Challan Date','Remarks');
		$header_width = array(45,25,25,25,65);
        $num_headers = count($header);
        
        for($i = 0; $i < $num_headers; ++$i) {
        $pdf->MultiCell($header_width[$i], 7, $header[$i], 1, 'L', 1, 1, $x, $y+123, true, 0, false, true, 7, 'M');
        $x = $x+$header_width[$i];
        }
        
        $sum_paid_amount = 0;
        $dues = 0;
        foreach($studentAccount as $challan){
            
        $x = 15;
        
        $paid = $challan['PAID_AMOUNT'];
        $sum_paid_amount+=$paid;
        $pdf->MultiCell($header_width[0], 7, $challan['PART_NAME'], $boarder, 'L', 0, 1, $x, $y+130, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[1], 7, $challan['CHALLAN_NO'], $boarder, 'L', 0, 1, $x+$header_width[0], $y+130, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[2], 7, $challan['PAID_AMOUNT'], $boarder, 'L', 0, 1, $x+$header_width[0]+$header_width[1], $y+130, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[3], 7, $challan['CHALLAN_DATE'], $boarder, 'L', 0, 1, $x+$header_width[0]+$header_width[1]+$header_width[2], $y+130, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[4], 7, $challan['DETAILS'], $boarder, 'L', 0, 1, $x+$header_width[0]+$header_width[1]+$header_width[2]+$header_width[3], $y+130, true, 0, false, true, 7, 'M');
        
        $y = $y + 7;
        
        }
       
// 		$pdf->SetFont('Times', 'B', 13);
// 		$pdf->MultiCell(array_sum($header_width), 7, 'Total Admission Fees Paid Amount : Rs. '.number_format($sum_paid_amount,2), 0, 'R', 0, 1, $x, $y, true, 0, false, true, 7, 'M');
		
		
		//****************** PAID FEES CHALLAN END ***********************//
			//****************** PAID FEES CHALLAN START ***********************//
		$sum_refund_amount = 0;
		if(count($refundstudentAccount)){
			     $y = $y+130;
		$x = 15;
		$pdf->SetFont('Times', 'BU', 13);
		$pdf->MultiCell(125, 7, 'Refund Fees Record', 0, 'L', 0, 1, $x, $y+7, true, 0, false, true, 7, 'M');
		
		$pdf->SetFillColor(0, 0, 0, 30);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0, 100);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('Times', '', 10);
        $header = array('Class','Challan No.','Refund Amount','Challan Date','Remarks');
		$header_width = array(45,25,25,25,65);
        $num_headers = count($header);
        
        for($i = 0; $i < $num_headers; ++$i) {
        $pdf->MultiCell($header_width[$i], 7, $header[$i], 1, 'L', 1, 1, $x, $y+14, true, 0, false, true, 7, 'M');
        $x = $x+$header_width[$i];
        }
        
       
        $dues = 0;
        foreach($refundstudentAccount as $challan){
            
        $x = 15;
        
        $paid = $challan['PAID_AMOUNT'];
        $sum_refund_amount+=$paid;
        $pdf->MultiCell($header_width[0], 7, $challan['PART_NAME'], $boarder, 'L', 0, 1, $x, $y+21, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[1], 7, $challan['CHALLAN_NO'], $boarder, 'L', 0, 1, $x+$header_width[0], $y+21, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[2], 7, -$challan['PAID_AMOUNT'], $boarder, 'L', 0, 1, $x+$header_width[0]+$header_width[1], $y+21, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[3], 7, $challan['CHALLAN_DATE'], $boarder, 'L', 0, 1, $x+$header_width[0]+$header_width[1]+$header_width[2], $y+21, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[4], 7, $challan['DETAILS'], $boarder, 'L', 0, 1, $x+$header_width[0]+$header_width[1]+$header_width[2]+$header_width[3], $y+21, true, 0, false, true, 7, 'M');
        
        $y = $y + 7;
        
        }
        $pdf->SetFont('Times', 'B', 13);
		$pdf->MultiCell(array_sum($header_width), 7, 'Total Admission Fees Paid Amount : Rs. '.number_format($sum_paid_amount+$sum_refund_amount,2), 0, 'R', 0, 1, $x, $y+28, true, 0, false, true, 7, 'M');
	
		}else{
		    $pdf->SetFont('Times', 'B', 13);
		$pdf->MultiCell(array_sum($header_width), 7, 'Total Admission Fees Paid Amount : Rs. '.number_format($sum_paid_amount+$sum_refund_amount,2), 0, 'R', 0, 1, $x, $y+135, true, 0, false, true, 7, 'M');
	  
		}
		
		//****************** PAID FEES CHALLAN END ***********************//
		//$pdf->MultiCell(45, 8, 'Total Payable Amount', $boarder, 'L', 0, 1, $x, $y+150, true, 0, false, true, 7, 'M');
		//$pdf->MultiCell(45, 8, 'Total Paid Amount', $boarder, 'L', 0, 1, $x, $y+158, true, 0, false, true, 7, 'M');
		//$pdf->MultiCell(45, 8, 'Dues', $boarder, 'L', 0, 1, $x, $y+166, true, 0, false, true, 7, 'M');
        //$x+=45;
        $pdf->SetFont('Times', 'B', 12);
		//$pdf->MultiCell(45, 8,number_format($sum_payable_amount) , $boarder, 'R', 0, 1, $x, $y+150, true, 0, false, true, 7, 'M');
		//$pdf->MultiCell(45, 8, number_format($sum_paid_amount), $boarder, 'R', 0, 1, $x, $y+158, true, 0, false, true, 7, 'M');
		//$pdf->MultiCell(45, 8,number_format($dues), $boarder, 'R', 0, 1, $x, $y+166, true, 0, false, true, 7, 'M');
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		//****************** FOOTER START ***********************//
		$pdf->SetY(-15);
        $pdf->SetFont('helvetica', 'I', 8);
		$pdf->Cell(0, 5, '', 'B', 1, 'C', 0, '', 0, false, 'B', 'B');
        $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        //****************** FOOTER END ***********************//
	    $pdf->lastPage();
		ob_end_clean();
		$pdf->Output('Paid_Challan_Report_'.$studentInfo['APPLICATION_ID'].'.pdf', 'I');
		exit;
	}
	
	public function admissionLetterReport($search_by=0,$search_value=0){
	 
	    if( $search_by<0 || $search_value<0) {
	        exit("Invalid input");
	    }
	    
	    $search_by = isValidData($search_by);
	    $search_value = isValidData($search_value);
	    $studentInfo = $this->StudentReports_model->getStudentInfo($search_by,$search_value);
	    $studentAccount = $this->StudentReports_model->getStudentPaidChallan($search_by,$search_value);
	    
	    $rec = array();
	    foreach($studentAccount as $value){
	        $rec[$value['PART_NAME']] = $value;
	    }
	    
	    $studentAccount = $rec;
	    
	    $program_type_id = $studentInfo['PROGRAM_TYPE_ID'];
	    $selection_list_id = $studentInfo['SELECTION_LIST_ID'];
	    $stufeestructure = $this->FeeChallan_model->getFeeStructure($program_type_id,$selection_list_id);
	    
	    $qr_code = $studentInfo['APPLICATION_ID'];
	   
	    $rollNo=$studentInfo['ROLL_NO'];
	    $rec=explode("/",$rollNo);
	    
	    $roll = $rec[0];
	    $ACEDIMIC_YEAR=str_replace("K","0",$roll);
	    
	    
	   
	   
	    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle('Paid_Challan_Report_'.$studentInfo['APPLICATION_ID'].'.pdf');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(FALSE);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}		
        $logo = K_PATH_IMAGES.'avatar-1.jpg';
		$phone = K_PATH_IMAGES.'phone.jpg';
		$email = K_PATH_IMAGES.'email.jpg';
		$url = K_PATH_IMAGES.'url.jpg';
		$style = array('border' => false,'padding' => 'auto','fgcolor' => array(0,0,0),'bgcolor' => false,'position' => 'R','module_width' => 1,'module_height' => 1);
		$tDate=date("F j, Y");
		$pdf->AddPage();
		//****************** HEADER START ***********************//
	    $pdf->Image($logo, 23, 10, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	    $pdf->SetXY(50, 13);
		$pdf->SetFont('Times', 'B', 24);
		$pdf->Cell(0, 0, 'UNIVERSITY OF SINDH', 0, 1, 'L', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(50, 23);
		$pdf->SetFont('Times', '', 16);
		$pdf->Cell(0, 0, 'Jamshoro, Sindh, Pakistan.', 0, 0, 'L', 0, '', 0, false, 'T', 'T');
		$pdf->Ln(2);		
		$pdf->SetFont('helvetica', '', 8);
		$pdf->SetXY(50, 24);
        $pdf->Cell(121, 0, '022-9213166', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 29);
		$pdf->Cell(121, 0, 'dir.adms@usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 34);
		$pdf->Cell(121, 0, 'www.usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');		
        $pdf->Image($phone, 173, 20, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($email, 173, 25, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($url, 173, 30, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);
	    $pdf->write2DBarcode($qr_code, 'QRCODE,L', 0, 17, 20, 20, $style, 'B');
	    $pdf->SetXY(15, 33);
		$pdf->SetFont('Times', '', 10);
		$pdf->Cell(35, 0, 'Directorate of', 0, 1, 'C', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(15, 37);
		$pdf->SetFont('Times', 'B', 12);
		$pdf->Cell(35, 0, 'Admissions', 0, 0, 'C', 0, '', 0, false, 'T', 'T');
		$pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 10, 'Dated : '.$tDate, 0, 1, 'R', 0, '', 0, false, 'T', 'T');
		$pdf->Cell(0, 5, '', 'T', 1, 'C', 0, '', 0, false, 'B', 'B');
		//****************** HEADER END ***********************//
		
		$boarder = 1;
		$x = 20;
		$y = 20;
		//Cell(w, h = 0, txt = '', border = 0, ln = 0, align = '', fill = 0, link = nil, stretch = 0, ignore_min_height = false, calign = 'T', valign = 'M')
		$pdf->SetXY(15, 55);
		$pdf->SetFont('Times', 'B', 16);
		//$pdf->Cell(180, 0, 'ADMISSION FEES PAID CHALLAN DETAILS', 0, 0, 'C', 0, '', 0, false, 'T', 'T');
		$pdf->MultiCell(180, 0, 'ADMISSION LIST', 0, 'C', 0, 1, $x, $y+30, true, 0, false, true);
		$pdf->SetFont('Times', '', 10);
		$pdf->MultiCell(40, 7, 'Application ID :', 0, 'R', 0, 1, $x, $y+45, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(30, 7, 'CNIC No. :', 0, 'R', 0, 1, $x+80, $y+45, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Student\'s Name :', 0, 'R', 0, 1, $x, $y+53, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Father\'s Name :', 0, 'R', 0, 1, $x, $y+59, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Surname : ', 0, 'R', 0, 1, $x, $y+69, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Degree Program :', 0, 'R', 0, 1, $x, $y+77, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Roll No. :', 0, 'R', 0, 1, $x, $y+85, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(40, 7, 'Category :', 0, 'R', 0, 1, $x, $y+93, true, 0, false, true, 7, 'M');
		
		$pdf->SetFont('Times', 'B', 12);
		$pdf->MultiCell(125, 7, $studentInfo['FIRST_NAME'], $boarder, 'L', 0, 1, $x+42, $y+53, true, 0, false, true, 7, 'M');
		$pdf->SetFont('Times', '', 12);
		$pdf->MultiCell(35, 7, $studentInfo['APPLICATION_ID'], $boarder, 'L', 0, 1, $x+42, $y+45, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(55, 7, $studentInfo['CNIC_NO'], $boarder, 'L', 0, 1, $x+112, $y+45, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['FNAME'], $boarder, 'L', 0, 1, $x+42, $y+61, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['LAST_NAME'], $boarder, 'L', 0, 1, $x+42, $y+69, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['PROGRAM_TITLE'], $boarder, 'L', 0, 1, $x+42, $y+77, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['ROLL_NO'], $boarder, 'L', 0, 1, $x+42, $y+85, true, 0, false, true, 7, 'M');
		$pdf->MultiCell(125, 7, $studentInfo['CATEGORY_NAME'], $boarder, 'L', 0, 1, $x+42, $y+93, true, 0, false, true, 7, 'M');
		
		
		
		//****************** PAID FEES CHALLAN START ***********************//
		$x = 62;
		
		$pdf->SetFillColor(0, 0, 0, 30);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0, 100);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('Times', '', 12);
        $header = array('CLASS','ACADEMIC YEAR');
		$header_width = array(63,63);
        $num_headers = count($header);
        
        for($i = 0; $i < $num_headers; ++$i) {
        $pdf->MultiCell($header_width[$i], 7, $header[$i], 1, 'L', 1, 1, $x, $y+123, true, 0, false, true, 7, 'M');
        $x = $x+$header_width[$i];
        }
        
        $sum_paid_amount = 0;
        $dues = 0;
        foreach($studentAccount as $challan){
            
        $x = 62;
        
        $paid = $challan['PAID_AMOUNT'];
        $sum_paid_amount+=$paid;
        $pdf->MultiCell($header_width[0], 7, $challan['PART_NAME'], $boarder, 'L', 0, 1, $x, $y+130, true, 0, false, true, 7, 'M');
        $pdf->MultiCell($header_width[1], 7, "REGULAR - ".$ACEDIMIC_YEAR++, $boarder, 'L', 0, 1, $x+$header_width[0], $y+130, true, 0, false, true, 7, 'M');
        
        $y = $y + 7;
        
        }
        
        $x=30;
        $pdf->SetFont('Times', 'B', 13);
		$pdf->MultiCell(125, 7, 'DEPUTY DIRECTOR ADMISSIONS', 0, 'L', 0, 1, $x, $y+200, true, 0, false, true, 7, 'M');
        
		//$pdf->SetFont('Times', 'B', 13);
		//$pdf->MultiCell(array_sum($header_width), 7, 'Total Admission Fees Paid Amount : Rs. '.number_format($sum_paid_amount,2), 0, 'R', 0, 1, $x, $y+130, true, 0, false, true, 7, 'M');
	
        $pdf->SetFont('Times', 'B', 12);
		
		//****************** FOOTER START ***********************//
		$pdf->SetY(-15);
        $pdf->SetFont('helvetica', 'I', 8);
		$pdf->Cell(0, 5, '', 'B', 1, 'C', 0, '', 0, false, 'B', 'B');
        $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        //****************** FOOTER END ***********************//
	    $pdf->lastPage();
		ob_end_clean();
		$pdf->Output('Admission_List_Report_'.$studentInfo['APPLICATION_ID'].'.pdf', 'I');
		exit;
	}
	
	public function correctionLetterAndList($search_by=0,$search_value=0){
	 
	    if( $search_by<0 || $search_value<0) {
	        exit("Invalid input");
	    }
	    
	    
	    $search_by = isValidData($search_by);
	    $search_value = isValidData($search_value);
	    $studentInfo = $this->StudentReports_model->getStudentInfo($search_by,$search_value);
	    $studentAccount = $this->StudentReports_model->getStudentPaidChallan($search_by,$search_value);
	    
	    $rec = array();
	    foreach($studentAccount as $value){
	        $rec[$value['PART_NAME']] = $value;
	    }
	    
	    $studentAccount = $rec;
	    
	    $program_type_id = $studentInfo['PROGRAM_TYPE_ID'];
	    $selection_list_id = $studentInfo['SELECTION_LIST_ID'];
	    $stufeestructure = $this->FeeChallan_model->getFeeStructure($program_type_id,$selection_list_id);
	    
	    $qr_code = $studentInfo['APPLICATION_ID'];
	   
	    $rollNo=$studentInfo['ROLL_NO'];
	    $rec=explode("/",$rollNo);
	    
	    $roll = $rec[0];
	    $ACEDIMIC_YEAR=str_replace("K","0",$roll);
	    
	    
	   
	   
	    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle('Paid_Challan_Report_'.$studentInfo['APPLICATION_ID'].'.pdf');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(FALSE);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}		
        $logo = K_PATH_IMAGES.'avatar-1.jpg';
		$phone = K_PATH_IMAGES.'phone.jpg';
		$email = K_PATH_IMAGES.'email.jpg';
		$url = K_PATH_IMAGES.'url.jpg';
		$style = array('border' => false,'padding' => 'auto','fgcolor' => array(0,0,0),'bgcolor' => false,'position' => 'R','module_width' => 1,'module_height' => 1);
		$tDate=date("F j, Y");
		$pdf->AddPage();
		//****************** HEADER START ***********************//
	    $pdf->Image($logo, 23, 10, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	    $pdf->SetXY(50, 13);
		$pdf->SetFont('Times', 'B', 24);
		$pdf->Cell(0, 0, 'UNIVERSITY OF SINDH', 0, 1, 'L', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(50, 23);
		$pdf->SetFont('Times', '', 16);
		$pdf->Cell(0, 0, 'Jamshoro, Sindh, Pakistan.', 0, 0, 'L', 0, '', 0, false, 'T', 'T');
		$pdf->Ln(2);		
		$pdf->SetFont('helvetica', '', 8);
		$pdf->SetXY(50, 24);
        $pdf->Cell(121, 0, '022-9213166', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 29);
		$pdf->Cell(121, 0, 'dir.adms@usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 34);
		$pdf->Cell(121, 0, 'www.usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');		
        $pdf->Image($phone, 173, 20, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($email, 173, 25, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($url, 173, 30, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);
	    $pdf->write2DBarcode($qr_code, 'QRCODE,L', 0, 17, 20, 20, $style, 'B');
	    $pdf->SetXY(15, 33);
		$pdf->SetFont('Times', '', 10);
		$pdf->Cell(35, 0, 'Directorate of', 0, 1, 'C', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(15, 37);
		$pdf->SetFont('Times', 'B', 12);
		$pdf->Cell(35, 0, 'Admissions', 0, 0, 'C', 0, '', 0, false, 'T', 'T');
		$pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 10, 'Dated : '.$tDate, 0, 1, 'R', 0, '', 0, false, 'T', 'T');
		$pdf->Cell(0, 5, '', 'T', 1, 'C', 0, '', 0, false, 'B', 'B');
		//****************** HEADER END ***********************//
		
		$boarder = 1;
		$x = 20;
		$y = 20;
	
		$pdf->SetXY(15, 55);
		$pdf->SetFont('Times', '', 12);
		$pdf->MultiCell(180, 0, 'No. DA/ ', 0, 'C', 0, 1, $x+60, $y+30, true, 0, false, true);
		$pdf->SetFont('Times', '', 10);
		
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(120, 0, 'The Controller of Examinations (Semester)', 0, 'C', 0, 1, 20, $y+40, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(100, 0, UNIVERSITY_NAME, 0, 'C', 0, 1, 13, $y+48, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(30, 0, 'Subject:', 0, 'C', 0, 1, 22, $y+70, true, 0, false, true);
		
		$pdf->SetFont('Times', 'BU', 16);
		$pdf->MultiCell(60, 0, 'CORRECTION LIST', 0, 'C', 0, 1, 50, $y+70, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(30, 0, 'Dear Sir,', 0, 'C', 0, 1, 23, $y+90, true, 0, false, true);

		$pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(180, 0, 'I am directed to enclose a list on subject cited above of the following student for your kind information and necessary action as per rules.', 0, 'L', 0, 1, 27, $y+105, true, 0, false, true);
		
		if($studentInfo['GENDER']=="M"){
		    $tag="S/O";
		}else{
		    $tag="D/O";
		}
		
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(180, 0,$studentInfo['FIRST_NAME'], 0, 'L', 0, 1, 27, $y+130, true, 0, false, true);

		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(180, 0,$tag." ".$studentInfo['FNAME'].", ".$studentInfo['LAST_NAME'], 0, 'L', 0, 1, 27, $y+138, true, 0, false, true);
		
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(180, 0,'Assistant Director Admissions', 0, 'L', 0, 1, 27, $y+190, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(180, 0,UNIVERSITY_NAME, 0, 'L', 0, 1, 27, $y+197, true, 0, false, true);
		
	
        $pdf->SetFont('Times', 'B', 12);
		
		//****************** FOOTER START ***********************//
		$pdf->SetY(-15);
        $pdf->SetFont('helvetica', 'I', 8);
		$pdf->Cell(0, 5, '', 'B', 1, 'C', 0, '', 0, false, 'B', 'B');
        $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        //****************** FOOTER END ***********************//
	    
	    
	    $pdf->AddPage();
		//****************** HEADER START ***********************//
	    $pdf->Image($logo, 23, 10, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	    $pdf->SetXY(50, 13);
		$pdf->SetFont('Times', 'B', 24);
		$pdf->Cell(0, 0, 'UNIVERSITY OF SINDH', 0, 1, 'L', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(50, 23);
		$pdf->SetFont('Times', '', 16);
		$pdf->Cell(0, 0, 'Jamshoro, Sindh, Pakistan.', 0, 0, 'L', 0, '', 0, false, 'T', 'T');
		$pdf->Ln(2);		
		$pdf->SetFont('helvetica', '', 8);
		$pdf->SetXY(50, 24);
        $pdf->Cell(121, 0, '022-9213166', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 29);
		$pdf->Cell(121, 0, 'dir.adms@usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');
		$pdf->SetXY(50, 34);
		$pdf->Cell(121, 0, 'www.usindh.edu.pk', 0, 0, 'R', 0, '', 0, false, 'B', 'C');		
        $pdf->Image($phone, 173, 20, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($email, 173, 25, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);		
        $pdf->Image($url, 173, 30, 4, '', 'JPG', '', 'R', false, 300, '', false, false, 0, false, false, false);
	    $pdf->write2DBarcode($qr_code, 'QRCODE,L', 0, 17, 20, 20, $style, 'B');
	    $pdf->SetXY(15, 33);
		$pdf->SetFont('Times', '', 10);
		$pdf->Cell(35, 0, 'Directorate of', 0, 1, 'C', 0, '', 0, false, 'T', 'B');
		$pdf->SetXY(15, 37);
		$pdf->SetFont('Times', 'B', 12);
		$pdf->Cell(35, 0, 'Admissions', 0, 0, 'C', 0, '', 0, false, 'T', 'T');
		$pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 10, 'Dated : '.$tDate, 0, 1, 'R', 0, '', 0, false, 'T', 'T');
		$pdf->Cell(0, 5, '', 'T', 1, 'C', 0, '', 0, false, 'B', 'B');
		//****************** HEADER END ***********************//
		
		
	    $boarder = 1;
		$x = 20;
		$y = 20;
	
		$pdf->SetXY(15, 55);
		$pdf->SetFont('Times', '', 12);
		$pdf->MultiCell(180, 0, 'No. DA/ ', 0, 'C', 0, 1, $x+60, $y+30, true, 0, false, true);
	    
	    
	    $pdf->SetFont('Times', 'BU', 16);
		$pdf->MultiCell(60, 0, 'CORRECTION LIST', 0, 'C', 0, 1, $x+60, $y+70, true, 0, false, true);
	    
	    $pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(30, 0, 'NAME: ', 0, 'C', 0, 1, 23, $y+100, true, 0, false, true);
		
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(130, 0, $studentInfo['FIRST_NAME'], 0, 'L', 0, 1, 80, $y+100, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
	    $pdf->MultiCell(60, 0, "FATHER'S NAME: ", 0, 'C', 0, 1, 22, $y+110, true, 0, false, true);
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(130, 0, $studentInfo['FNAME'], 0, 'L', 0, 1, 80, $y+110, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
	    $pdf->MultiCell(34, 0, "SURNAME: ", 0, 'C', 0, 1, 27, $y+120, true, 0, false, true);
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(130, 0, $studentInfo['LAST_NAME'], 0, 'L', 0, 1, 80, $y+120, true, 0, false, true);
		
	    $pdf->SetFont('Times', '', 16);
	    $pdf->MultiCell(30, 0, "ROLL NO: ", 0, 'C', 0, 1, 27, $y+130, true, 0, false, true);
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(130, 0, $studentInfo['ROLL_NO'], 0, 'L', 0, 1, 80, $y+130, true, 0, false, true);
	    
	    $pdf->SetFont('Times', '', 16);
	    $pdf->MultiCell(30, 0, "CLASS: ", 0, 'C', 0, 1, 24, $y+140, true, 0, false, true);
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(130, 0, $studentInfo['PROGRAM_TITLE'], 0, 'L', 0, 1, 80, $y+140, true, 0, false, true);
		
		$pdf->SetFont('Times', '', 16);
		$pdf->MultiCell(30, 0, "CAMPUS: ", 0, 'C', 0, 1, 27, $y+160, true, 0, false, true);
		$pdf->SetFont('Times', 'B', 16);
		$pdf->MultiCell(130, 0, $studentInfo['CAMPUS_NAME'], 0, 'L', 0, 1, 80, $y+160, true, 0, false, true);
		
	
		$pdf->MultiCell(180, 0,'Computer Programmer', 0, 'L', 0, 1, 27, $y+220, true, 0, false, true);
		
		
		
	    //****************** FOOTER START ***********************//
		$pdf->SetY(-15);
        $pdf->SetFont('helvetica', 'I', 8);
		$pdf->Cell(0, 5, '', 'B', 1, 'C', 0, '', 0, false, 'B', 'B');
        $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        //****************** FOOTER END ***********************//
	    
	    
	    $pdf->lastPage();
		ob_end_clean();
		$pdf->Output('correctionLetterAndList_'.$studentInfo['APPLICATION_ID'].'.pdf', 'I');
		exit;
	}	
	
	public function idCardChallan($search_by=0,$search_value=0){
	    if( $search_by<0 || $search_value<0) {
	        exit("Invalid input");
	    }
	    $search_by = isValidData($search_by);
	    $search_value = isValidData($search_value);
	    
	    //check if challan exsist
	    $challan_exsist = $this->legacy_db->get_where('fee_challan',array('CHALLAN_TYPE_ID' => 6, 'APPLICATION_ID' => $search_value))->result();
	    if(empty($challan_exsist)){
	        $this->legacy_db->select('app.APPLICATION_ID, 6 AS CHALLAN_TYPE_ID, fl.BANK_ACCOUNT_ID, sl.SELECTION_LIST_ID, 400 AS CHALLAN_AMOUNT, 400 AS INSTALLMENT_AMOUNT, 0 AS DUES, 0 AS LATE_FEE, 400 AS PAYABLE_AMOUNT, "2024-05-15" AS VALID_UPTO, date("Y/m/d") AS DATETIME, "ID CARD FEE" AS REMARKS, 1 AS ADMIN_USER_ID, 1 AS PART_ID, 1 AS SEMESTER_ID, fl.FEE_PROG_LIST_ID, 1 AS ACTIVE');
	        $this->legacy_db->from('applications app');
	        $this->legacy_db->join('selection_list sl','app.APPLICATION_ID = sl.APPLICATION_ID');
	        $this->legacy_db->join('candidate_account ca','app.APPLICATION_ID = ca.APPLICATION_ID');
	        $this->legacy_db->join('fee_ledger fl','ca.ACCOUNT_ID = fl.ACCOUNT_ID AND sl.SELECTION_LIST_ID = fl.SELECTION_LIST_ID');
	        $this->legacy_db->where('app.APPLICATION_ID',$search_value);
	        $this->legacy_db->group_by('sl.SELECTION_LIST_ID');
	        $query = $this->legacy_db->get()->row();
	        
// 	        $new_challan = array(
// 				'CHALLAN_NO'=>isValidData($challan['CHALLAN_NO']),
// 				'APPLICATION_ID'=>isValidData($challan['APPLICATION_ID']),
// 				'CHALLAN_TYPE_ID'=>isValidData($challan['CHALLAN_TYPE_ID']),
// 				'BANK_ACCOUNT_ID'=>isValidData($challan['BANK_ACCOUNT_ID']),
// 				'SELECTION_LIST_ID'=>isValidData($challan['SELECTION_LIST_ID']),
// 				'CHALLAN_AMOUNT'=>isValidData($challan['CHALLAN_AMOUNT']),
// 				'INSTALLMENT_AMOUNT'=>isValidData($challan['INSTALLMENT_AMOUNT']),
// 				'DUES'=>isValidData($challan['DUES']),
// 				'LATE_FEE'=>isValidData($challan['LATE_FEE']),
// 				'PAYABLE_AMOUNT'=>isValidData($challan['PAYABLE_AMOUNT']),
// 				'VALID_UPTO'=>isValidData($challan['VALID_UPTO']),
// 				'DATETIME'=>isValidData($challan['DATETIME']),
// 				'REMARKS'=>isValidData($challan['REMARKS']),
// 				'ADMIN_USER_ID'=>isValidData($challan['ADMIN_USER_ID']),
// 				'PART_ID'=>isValidData($challan['PART_ID']),
// 				'SEMESTER_ID'=>isValidData($challan['SEMESTER_ID']),
// 				'FEE_PROG_LIST_ID'=>isValidData($challan['FEE_PROG_LIST_ID']),
// 				'ACTIVE'=>isValidData($challan['ACTIVE']),
// 			);
	    }
//	    prePrint($query);
//	    exit;
	        $studentInfo = $this->StudentReports_model->getStudentInfoByPart($search_by,$search_value);
	   /* echo "<pre>";
	    print_r($studentInfo);
	    exit;
    [APPLICATION_ID] => 6265
    [SELECTION_LIST_ID] => 41321
    $studentInfo['SEAT_NO']
    [PROGRAM_TYPE_ID] => 1
    [PART_NAME] => SECOND YEAR
    [SEMESTER_NAME] => ANNUAL
    [CNIC_NO] => 4320335251053
    [FIRST_NAME] => AMEER HAMZA
    [FNAME] => SYED DEEDAR HUSSAIN
    [LAST_NAME] => RASHIDI
    [PROGRAM_TITLE] => B.B.A (HONS)
    [CATEGORY_NAME] => SELF FINANCE
    $studentInfo[ROLL_NO] => 2K21/LBBA/11
) */
	    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle('IDCard_Challan_Report_.pdf');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(FALSE);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		$pdf->setJPEGQuality(150);
		
		$uslogo = K_PATH_IMAGES.'avatar-1.jpg';
		$hbllogo = K_PATH_IMAGES.'hbl-logo.jpg';
		$pdf->AddPage();
		
		
		//MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		//     Cell($w, $h, $txt, $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
		$x = 7;
		$y = 25;
		$w = 65;
		$h = 0;
		$y_title_increment = 6;
		$y_field_increment = 3.5;
		$font_size_title = 8;
		$font_size_field = 10;
		$title8 = array('arial', '', 8);
		$title9 = array('arial', '', 9);
		$style = array('border' => false,'padding' => 'auto','fgcolor' => array(0,0,0),'bgcolor' => false,'position' => 'S','module_width' => 1,'module_height' => 1);
		$copy = array('BANK\'S COPY', 'ACCOUNT\'S COPY', 'ADMISSION\'S COPY', 'STUDENT\'S COPY');
		$challan_no = substr($studentInfo['YEAR'], -2).$studentInfo['APPLICATION_ID'];
		$program_name_len = strlen($studentInfo['PROGRAM_TITLE']);
		$date = strtotime("+7 day");
		$pdf->MultiCell(1, 185, '', 'R', 'C', 0, 0, 74.5, 10, true, 0, false, true, 0);
		$pdf->MultiCell(1, 185, '', 'R', 'C', 0, 0, 146.5, 10, true, 0, false, true, 0);
		$pdf->MultiCell(1, 185, '', 'R', 'C', 0, 0, 218.5, 10, true, 0, false, true, 0);
// Image method signature:
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
		for ($i = 0; $i < 4; ++$i) {
			$pdf->Image($uslogo, $x+3, 10, 15, 17, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
			$pdf->Image($hbllogo, $x+22, 10, 23, 15, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
			$pdf->write2DBarcode($studentInfo['APPLICATION_ID'], 'QRCODE,H', $x+45, 10, 20, 20, $style, 'B');
			
			$pdf->SetFont('arial', 'B', 7);
			$pdf->MultiCell($w, $h, $copy[0+$i], 0, 'C', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + 4;
			$pdf->SetFont('arial', '', 8);
			$pdf->MultiCell($w, $h, 'Please receive and creadit to Uinversity of Sindh', 0, 'C', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + 5;
			$pdf->SetFont('arial', '', 9);
			$pdf->MultiCell($w, $h, 'ADMISSION ACCOUNT NUMBER', 0, 'C', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + 4;
			$pdf->SetFont('arial', 'B', 11);
			$pdf->MultiCell($w, $h, 'CMD. 00427992039203', 0, 'C', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + 6;
			$pdf->SetFont('arial', 'B', 8);
			$pdf->MultiCell($w-26, $h, 'CHALLAN NO: '.$challan_no, 0, 'L', 0, 0, $x, $y, true, 0, false, true, 0);
			//$pdf->SetFont('arial', '', 8);
			$pdf->MultiCell($w-35, $h, 'DATE: '.date("d-m-Y"), 0, 'R', 0, 1, $x+35, $y, true, 0, false, true, 0);
			$y = $y + 5;
			$pdf->SetTextColor(255, 0, 0);
			$pdf->SetFont('arial', 'B', 10);
			$pdf->MultiCell($w, $h, 'This challan is valid upto: '.date("d-m-Y",$date), 0, 'C', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + 4.5;
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFont('arial', 'B', 11);
			$pdf->MultiCell($w, $h, 'ID CARD FEE CHALLAN', 0, 'C', 1, 1, $x, $y, true, 0, false, true, 0);
			$pdf->SetTextColor(0, 0, 0);
			$y = $y + 7;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w-35, $h, 'ROLL NO :', 0, 'L', 0, 0, $x, $y, true, 0, false, true, 0);
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w-30, $h, $studentInfo['ROLL_NO'], 0, 'L', 0, 1, $x+30, $y, true, 0, false, true, 0);
			$y = $y + 5;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w-35, $h, 'APPLICATION ID :', 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w-30, $h, $studentInfo['APPLICATION_ID'], 0, 'L', 0, 1, $x+30, $y, true, 0, false, true, 0);
			$y = $y + $y_title_increment;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w, $h, 'STUDENT\'S NAME :', 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_field_increment;
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w, $h, $studentInfo['FIRST_NAME'], 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_title_increment;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w, $h, 'FATHER\'S NAME :', 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_field_increment;
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w, $h, $studentInfo['FNAME'], 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_title_increment;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w, $h, 'SURNAME :', 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_field_increment;
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w, $h, $studentInfo['LAST_NAME'], 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_title_increment;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w-50, $h, 'CLASS :', 0, 'L', 0, 0, $x, $y, true, 0, false, true, 0);
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w-15, $h, $studentInfo['PART_NAME'], 0, 'L', 0, 1, $x+15, $y, true, 0, false, true, 0);
			$y = $y + $y_title_increment;
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w, $h, 'PROGRAM :', 0, 'L', 0, 1, $x, $y, true, 0, false, false, 0);
			$y = $y + $y_field_increment;
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w, $h, $studentInfo['PROGRAM_TITLE'], 0, 'L', 0, 2, $x, $y, true, 0, false, true);			
			if ($program_name_len > 30) { $y = $y + 10;	} else { $y = $y + $y_title_increment; }
			$pdf->SetFont('arial', '', $font_size_title);
			$pdf->MultiCell($w, $h, 'CAMPUS :', 0, 'L', 0, 2, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_field_increment;
			$pdf->SetFont('arialb', 'B', $font_size_field);
			$pdf->MultiCell($w, $h, $studentInfo['CAMPUS_NAME'], 0, 'L', 0, 1, $x, $y, true, 0, false, true, 0);
			$y = $y + 10;
			$pdf->SetFont('times', 'B', 11);
			$pdf->MultiCell($w-25, $h+12, 'ID CARD FEE', 1, 'R', 0, 1, $x, $y, true, 0, false, true, 12, 'M', true);
			$pdf->MultiCell($w-40, $h+12, 'Rs. 400.00', 1, 'R', 0, 1, $x+40, $y, true, 0, false, true, 12, 'M', true);
			//$y = $y + $y_field_increment + 5;
			//$pdf->MultiCell($w-25, $h, 'DUES', 1, 'R', 0, 1, $x, $y, true, 0, false, true, 0, 'M', true);
			//$pdf->MultiCell($w-40, $h, 'Rs. ', 1, 'R', 0, 1, $x+40, $y, true, 0, false, true, 0, 'M', true);
			//$y = $y + $y_field_increment + 0.65;
			//$pdf->MultiCell($w-25, $h, 'TOTAL FEE', 1, 'R', 0, 1, $x, $y, true, 0, false, true, 0, 'M', true);
			//$pdf->MultiCell($w-40, $h, 'Rs. ', 1, 'R', 0, 1, $x+40, $y, true, 0, false, true, 0, 'M', true);
			$y = $y + $y_title_increment + 7;
			$pdf->MultiCell($w, $h+11, 'Amount (In words):', 0, 'L', 0, 2, $x, $y, true, 0, false, true, 11);
			$y = $y + $y_title_increment;
			$pdf->MultiCell($w, $h+11, 'FOUR HUNDRED ONLY', 0, 'L', 0, 2, $x, $y, true, 0, false, true, 11);
			$y = $y + $y_title_increment + 5;
			$pdf->SetFont('arialb', 'B', 9);
			$pdf->SetTextColor(127);
			$pdf->MultiCell($w, $h, 'For Admission Office use only', 1, 'C', 0, 2, $x, $y, true, 0, false, true, 0);
			$y = $y + $y_title_increment - 2;
			$pdf->MultiCell($w, $h+24, '', 1, 'C', 0, 2, $x, $y, true, 0, false, true, 24);
			$y = $y + $y_title_increment + 18;
			$pdf->SetFont('arialb', '', 7);
			$pdf->MultiCell($w, $h, '(Signature & Stamp of Issuing Officer)', 1, 'C', 0, 2, $x, $y, true, 0, false, true, 0);
			$pdf->SetTextColor(0, 0, 0, 100);
			$x = $x + 72;
			$y = 25;
		}
		
		$pdf->lastPage();
		ob_end_clean();
		$pdf->Output('IDCard_Challan_Report_.pdf', 'I');
		exit;
	}
	
	public function get_fees_statistics_data(){
        $postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$program_type_id= isValidData($request->program_type_id);
		$session_id 	= isValidData($request->session_id);
		$part_id 	= isValidData($request->part_id);
		
		$upto_date = $this->legacy_db->from('fee_ledger')->order_by('DATE','DESC')->get()->result_array();
		$upto_date = date_create($upto_date[0]['DATE']);
		$upto_date = date_format($upto_date, "d-m-Y");

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);
		$pdf->SetTitle('Paid Challan Report '.$upto_date);
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(FALSE);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}		
		$logo = K_PATH_IMAGES.'avatar-1.jpg';
		$phone = K_PATH_IMAGES.'phone.jpg';
		$email = K_PATH_IMAGES.'email.jpg';
		$url = K_PATH_IMAGES.'url.jpg';
		$style = array('border' => false,'padding' => 'auto','fgcolor' => array(0,0,0),'bgcolor' => false,'position' => 'R','module_width' => 1,'module_height' => 1);
		$tDate=date("F j, Y");
		$pdf->AddPage('L');
		
		$pdf->SetFont('Times', 'B', 14);
		$pdf->MultiCell(300, 0, 'STATEMENT OF PAID ADMISSION FEES CHALLAN BATCH WISE BACHELOR AND MASTER DEGREE PROGRAMS', 0, 'C', 0, 1, 0, 15, true, 0, false, true);
		$pdf->MultiCell(300, 0, 'FOR ACADEMIC YEAR 2023 UPTO '.$upto_date, 0, 'C', 0, 1, 0, 23, true, 0, false, true);
		$boarder = 1;
		$w = 86;
		$h = 8;
		$x = 30;
		$y = 40;
		$pdf->SetFont('Times', 'B', 12);
		$pdf->MultiCell($w, $h, 'MERIT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$pdf->MultiCell($w, $h, 'SELF / SPECIAL SELF', $boarder, 'C', 0, 1, $x = $x + $w, $y, true, 0, false, true, $h, 'M');
		$pdf->MultiCell($w, $h, 'EVENING', $boarder, 'C', 0, 1, $x = $x + $w, $y, true, 0, false, true, $h, 'M');
		$w = 17;
		$x = 30;
		$y = $y + $h;
		$pdf->SetFont('arial', 'B', 8);
		$pdf->MultiCell($w, $h, 'ISSUED CHALLAN', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'CHALLAN AMOUNT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, 'PAID CHALLAN', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'PAID AMOUNT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, 'ISSUED CHALLAN', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'CHALLAN AMOUNT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, 'PAID CHALLAN', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'PAID AMOUNT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
        $x = $x + $w;
		$w = $w - 9;
        $pdf->MultiCell($w, $h, 'ISSUED CHALLAN', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'CHALLAN AMOUNT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, 'PAID CHALLAN', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'PAID AMOUNT', $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$w = 25;
		$h = 16;
		$x = 5;
		$y = 56;
		$challans = array();
		$MERIT_TOTAL_CHALLAN = 0;
		$SELF_TOTAL_CHALLAN = 0;
		$EVENING_TOTAL_CHALLAN = 0;
		$MERIT_PAID_CHALLAN = 0;
		$SELF_PAID_CHALLAN = 0;
		$EVENING_PAID_CHALLAN = 0;
		$MERIT_TOTAL_AMOUNT = 0;
		$SELF_TOTAL_AMOUNT = 0;
		$EVENING_TOTAL_AMOUNT = 0;
		$MERIT_PAID_AMOUNT = 0;
		$SELF_PAID_AMOUNT = 0;
		$EVENING_PAID_AMOUNT = 0;
		$batches = $this->legacy_db->select('*')->from('sessions')->where_in('YEAR',[2021,2022])->order_by('YEAR','DESC')->get()->result_array();
		foreach ($batches as $key => $batch) {
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->MultiCell($w, $h, $batch['SESSION_CODE'], $boarder, 'R', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
			
			$this->legacy_db->select('se.YEAR, fct.FEE_TYPE_TITLE AS CATEGORY, COUNT(fc.CHALLAN_NO) AS TOTAL_CHALLAN, SUM(fc.PAYABLE_AMOUNT) AS TOTAL_AMOUNT');
			$this->legacy_db->from('fee_challan fc');
			$this->legacy_db->join('selection_list sl','fc.SELECTION_LIST_ID = sl.SELECTION_LIST_ID');
			$this->legacy_db->join('admission_session ads','sl.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
			$this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
			$this->legacy_db->join('category cat','cat.CATEGORY_ID = sl.CATEGORY_ID');
			$this->legacy_db->join('fee_category_type fct','fct.FEE_CATEGORY_TYPE_ID = cat.FEE_CATEGORY_TYPE_ID');
			$this->legacy_db->join('bank_account ba','fc.BANK_ACCOUNT_ID = ba.BANK_ACCOUNT_ID');
			$this->legacy_db->where(array('fc.CHALLAN_TYPE_ID' => 1, 'fc.ACTIVE' => 1, 'fc.PAYABLE_AMOUNT >' => 0, 'se.YEAR' => $batch['YEAR'], 'fc.CHALLAN_NO >=' => 212330000, 'fc.CHALLAN_NO <=' => 212379999));
			$this->legacy_db->group_by('ba.CMD_NO');
			$this->legacy_db->order_by('fct.FEE_CATEGORY_TYPE_ID');
			$issued_challan_2k23 = $this->legacy_db->get();
			$issued_challan_2k23 = $issued_challan_2k23->result_array();
			$MERIT_TOTAL_CHALLAN += $issued_challan_2k23[0]['TOTAL_CHALLAN'];
			$SELF_TOTAL_CHALLAN += $issued_challan_2k23[1]['TOTAL_CHALLAN'];
			$EVENING_TOTAL_CHALLAN += $issued_challan_2k23[2]['TOTAL_CHALLAN'];
			$MERIT_TOTAL_AMOUNT += $issued_challan_2k23[0]['TOTAL_AMOUNT'];
			$SELF_TOTAL_AMOUNT += $issued_challan_2k23[1]['TOTAL_AMOUNT'];
			$EVENING_TOTAL_AMOUNT += $issued_challan_2k23[2]['TOTAL_AMOUNT'];
			
			$w = 17;
			$x = 30;
			$pdf->SetFont('arial', 'B', 8);
			foreach ($issued_challan_2k23 as $key => $issued_challan) {
				$pdf->MultiCell($w, $h, $issued_challan['TOTAL_CHALLAN'], $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w + 9;
				$pdf->MultiCell($w, $h, 'Rs. '.round($issued_challan['TOTAL_AMOUNT']), $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w - 9;
				
				$x = $x + $w;
				$w = $w + 9;
				
				$x = $x + $w;
				$w = $w - 9;
			}

			$this->legacy_db->select('fct.FEE_TYPE_TITLE AS CATEGORY, COUNT(fl.CHALLAN_NO) AS PAID_CHALLAN, SUM(fl.PAID_AMOUNT) AS PAID_AMOUNT');
			$this->legacy_db->from('fee_ledger fl');
			$this->legacy_db->join('fee_challan fc','fl.CHALLAN_NO = fc.CHALLAN_NO');
			$this->legacy_db->join('selection_list sl','fc.SELECTION_LIST_ID = sl.SELECTION_LIST_ID');
			$this->legacy_db->join('admission_session ads','sl.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
			$this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
			$this->legacy_db->join('category cat','cat.CATEGORY_ID = sl.CATEGORY_ID');
			$this->legacy_db->join('fee_category_type fct','fct.FEE_CATEGORY_TYPE_ID = cat.FEE_CATEGORY_TYPE_ID');
			$this->legacy_db->join('bank_account ba','fc.BANK_ACCOUNT_ID = ba.BANK_ACCOUNT_ID');
			$this->legacy_db->where(array('fc.CHALLAN_TYPE_ID' => 1, 'fc.ACTIVE' => 1, 'fc.PAYABLE_AMOUNT >' => 0, 'se.YEAR' => $batch['YEAR'], 'fc.CHALLAN_NO >=' => 212330000, 'fc.CHALLAN_NO <=' => 212379999));
			$this->legacy_db->group_by('ba.CMD_NO');
			$this->legacy_db->order_by('fct.FEE_CATEGORY_TYPE_ID');
			$paid_challan_2k23 = $this->legacy_db->get();
			$paid_challan_2k23 = $paid_challan_2k23->result_array();
			$MERIT_PAID_CHALLAN += $paid_challan_2k23[0]['PAID_CHALLAN'];
			$SELF_PAID_CHALLAN += $paid_challan_2k23[1]['PAID_CHALLAN'];
			$EVENING_PAID_CHALLAN += $paid_challan_2k23[2]['PAID_CHALLAN'];
			$MERIT_PAID_AMOUNT += $paid_challan_2k23[0]['PAID_AMOUNT'];
			$SELF_PAID_AMOUNT += $paid_challan_2k23[1]['PAID_AMOUNT'];
			$EVENING_PAID_AMOUNT += $paid_challan_2k23[2]['PAID_AMOUNT'];
			$w = 17;
			$x = 73;
			foreach ($paid_challan_2k23 as $key => $paid_challan) {
				$pdf->MultiCell($w, $h, $paid_challan['PAID_CHALLAN'], $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w + 9;
				$pdf->MultiCell($w, $h, 'Rs. '.round($paid_challan['PAID_AMOUNT']), $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w - 9;

				$x = $x + $w;
				$w = $w + 9;

				$x = $x + $w;
				$w = $w - 9;

			}
			$w = 25;
			$x = 5;
			$y = $y + $h;
		}
		

        $old_batches = $this->db->select('*')->from('admission_year')->where_in('year',[2019,2020])->order_by('year','DESC')->get()->result_array();
 		
		foreach ($old_batches as $key => $batch) {
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->MultiCell($w, $h, $batch['remarks'], $boarder, 'R', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
			
			$this->db->select('ay.year,	ugcc.ACCOUNT_NO, ugcc.CATEGORY_NAME, COUNT(ugcc.CHALLAN_NO) AS TOTAL_CHALLAN, SUM(ugcc.TOTAL_AMOUNT) AS TOTAL_AMOUNT');
			$this->db->from('ug_candidate_challan ugcc');
			$this->db->join('admission_list_details ald','ugcc.CANDIDATE_ID = ald.candidate_id');
			$this->db->join('candidate ca','ald.candidate_id = ca.candidate_id');
			$this->db->join('admission_year ay','ca.admission_year_id = ay.admission_year_id');
			$this->db->where(array('ugcc.ACTIVE' => 1, 'ugcc.TOTAL_AMOUNT >' => 0, 'ay.year' => $batch['year'], 'ugcc.CHALLAN_NO >=' => 212330000, 'ugcc.CHALLAN_NO <=' => 212379999));
			$this->db->group_by('ugcc.ACCOUNT_NO');
			
			$issued_challan_2k20 = $this->db->get();
			$issued_challan_2k20 = $issued_challan_2k20->result_array();
			$MERIT_TOTAL_CHALLAN += $issued_challan_2k20[0]['TOTAL_CHALLAN'];
			$SELF_TOTAL_CHALLAN += $issued_challan_2k20[1]['TOTAL_CHALLAN'];
			$EVENING_TOTAL_CHALLAN += $issued_challan_2k20[2]['TOTAL_CHALLAN'];
			$MERIT_TOTAL_AMOUNT += $issued_challan_2k20[0]['TOTAL_AMOUNT'];
			$SELF_TOTAL_AMOUNT += $issued_challan_2k20[1]['TOTAL_AMOUNT'];
			$EVENING_TOTAL_AMOUNT += $issued_challan_2k20[2]['TOTAL_AMOUNT'];
			$w = 17;
			$x = 30;
			$pdf->SetFont('arial', 'B', 8);
			foreach ($issued_challan_2k20 as $key => $issued_challan) {
				$pdf->MultiCell($w, $h, $issued_challan['TOTAL_CHALLAN'], $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w + 9;
				$pdf->MultiCell($w, $h, 'Rs. '.$issued_challan['TOTAL_AMOUNT'], $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w - 9;

				$x = $x + $w;
				$w = $w + 9;

				$x = $x + $w;
				$w = $w - 9;
				
			}

			$this->db->select('ay.year, ugcc.ACCOUNT_NO, COUNT(ugcc.CHALLAN_NO) AS PAID_CHALLAN, SUM(pr.amount) AS PAID_AMOUNT, SUM(ugcc.TOTAL_AMOUNT) AS PAID_AMOUNT_FEE');
			$this->db->from('part_registry pr');
			$this->db->join('ug_candidate_challan ugcc','pr.challan_no = ugcc.CHALLAN_NO');
			$this->db->join('admission_list_details ald','ugcc.CANDIDATE_ID = ald.candidate_id');
			$this->db->join('candidate ca','ald.candidate_id = ca.candidate_id');
			$this->db->join('admission_year ay','ca.admission_year_id = ay.admission_year_id');
			$this->db->where(array('pr.type' => 0, 'ugcc.ACTIVE' => 1, 'ugcc.TOTAL_AMOUNT >' => 0, 'ay.year' => $batch['year'], 'ugcc.CHALLAN_NO >=' => 212330000, 'ugcc.CHALLAN_NO <=' => 212379999));
			$this->db->group_by('ugcc.ACCOUNT_NO');
			
			$paid_challan_2k20 = $this->db->get();
			$paid_challan_2k20 = $paid_challan_2k20->result_array();
			$MERIT_PAID_CHALLAN += $paid_challan_2k20[0]['PAID_CHALLAN'];
			$SELF_PAID_CHALLAN += $paid_challan_2k20[1]['PAID_CHALLAN'];
			$EVENING_PAID_CHALLAN += $paid_challan_2k20[2]['PAID_CHALLAN'];
			$MERIT_PAID_AMOUNT += $paid_challan_2k20[0]['PAID_AMOUNT'];
			$SELF_PAID_AMOUNT += $paid_challan_2k20[1]['PAID_AMOUNT'];
			$EVENING_PAID_AMOUNT += $paid_challan_2k20[2]['PAID_AMOUNT'];
			$w = 17;
			$x = 73;
			foreach ($paid_challan_2k20 as $key => $paid_challan) {
				$pdf->MultiCell($w, $h, $paid_challan['PAID_CHALLAN'], $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w + 9;
				$pdf->MultiCell($w, $h, 'Rs. '.$paid_challan['PAID_AMOUNT'], $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
				$x = $x + $w;
				$w = $w - 9;

				$x = $x + $w;
				$w = $w + 9;

				$x = $x + $w;
				$w = $w - 9;
			}
			$w = 25;
			$x = 5;
			$y = $y + $h;
		}
		

		$pdf->SetFont('arial', 'B', 9);
		$w = 25;
		$h = 10;
		$x = 5;
		$y = 120;
		$pdf->MultiCell($w, $h, 'TOTAL', $boarder, 'R', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 8;
		$pdf->MultiCell($w, $h, $MERIT_TOTAL_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'Rs. '.$MERIT_TOTAL_AMOUNT, $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, $MERIT_PAID_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'Rs. '.$MERIT_PAID_AMOUNT, $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, $SELF_TOTAL_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'Rs. '.$SELF_TOTAL_AMOUNT, $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, $SELF_PAID_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'Rs. '.$SELF_PAID_AMOUNT, $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, $EVENING_TOTAL_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'Rs. '.$EVENING_TOTAL_AMOUNT, $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 9;
		$pdf->MultiCell($w, $h, $EVENING_PAID_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w + 9;
		$pdf->MultiCell($w, $h, 'Rs. '.$EVENING_PAID_AMOUNT, $boarder, 'C', 0, 1, $x, $y, true, 0, false, true, $h, 'M');
		$x = 47;
		$y = 140;
		$w = $w + 10;
		$pdf->MultiCell($w, $h, 'TOTAL CHALLAN', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, 'TOTAL AMOUNT', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, 'PAID CHALLAN', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, 'PAID AMOUNT', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, 'REMAINING CHALLAN', $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');

		$w = 42;
		$x = 5;
		$y = 150;
		$pdf->MultiCell($w, $h, 'GRAND TOTAL', $boarder, 'R', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$w = $w - 6;
		$pdf->MultiCell($w, $h, $MERIT_TOTAL_CHALLAN+$SELF_TOTAL_CHALLAN+$EVENING_TOTAL_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, 'Rs. '.($MERIT_TOTAL_AMOUNT+$SELF_TOTAL_AMOUNT+$EVENING_TOTAL_AMOUNT), $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, $MERIT_PAID_CHALLAN+$SELF_PAID_CHALLAN+$EVENING_PAID_CHALLAN, $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, 'Rs. '.($MERIT_PAID_AMOUNT+$SELF_PAID_AMOUNT+$EVENING_PAID_AMOUNT), $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$x = $x + $w;
		$pdf->MultiCell($w, $h, ($MERIT_TOTAL_CHALLAN+$SELF_TOTAL_CHALLAN+$EVENING_TOTAL_CHALLAN)-($MERIT_PAID_CHALLAN+$SELF_PAID_CHALLAN+$EVENING_PAID_CHALLAN), $boarder, 'C', 0, 0, $x, $y, true, 0, false, true, $h, 'M');
		$pdf->lastPage();
		ob_end_clean();
		$pdf->Output('Paid_Challan_Report_'.$upto_date.'.pdf', 'I');
		exit;
    }	
    
    public function hostel_idcardpaper($hostel){
    	
    	// Query
    	$this->legacy_db->select('
            app.APPLICATION_ID,
            ur.USER_ID,
            sl.ROLL_NO,
            pl.PROGRAM_TITLE,
            dpt.DEPT_NAME,
            ur.FIRST_NAME,
            ur.FNAME,
            ur.LAST_NAME,
            ur.CNIC_NO,
            ur.PERMANENT_ADDRESS,
            h.HOSTEL_NAME,
            ha.ROOM_NO,
            ur.PROFILE_IMAGE
    	');
    	$this->legacy_db->from('hostel_allotment ha');
    	$this->legacy_db->join('applications app','ha.APPLICATION_ID = app.APPLICATION_ID');
    	$this->legacy_db->join('users_reg ur','app.USER_ID = ur.USER_ID');
    	$this->legacy_db->join('selection_list sl','app.APPLICATION_ID = sl.APPLICATION_ID');
    	$this->legacy_db->join('admission_session ads','sl.ADMISSION_SESSION_ID = ads.ADMISSION_SESSION_ID');
    	$this->legacy_db->join('sessions se','ads.SESSION_ID = se.SESSION_ID');
    	$this->legacy_db->join('program_list pl','sl.PROG_LIST_ID = pl.PROG_LIST_ID');
    	$this->legacy_db->join('shift_program_mapping spm','ads.CAMPUS_ID = spm.CAMPUS_ID AND sl.SHIFT_ID = spm.SHIFT_ID AND pl.PROG_LIST_ID = spm.PROG_LIST_ID');
    	$this->legacy_db->join('departments dpt','spm.DEPT_ID = dpt.DEPT_ID');
    	$this->legacy_db->join('hostels h','ha.HOSTEL_ID = h.HOSTEL_ID');
    	$this->legacy_db->where('sl.IS_ENROLLED','Y');
    	$this->legacy_db->where('ha.PRINT','Y');
    	$this->legacy_db->where('h.HOSTEL_ID',$hostel);
    	$this->legacy_db->order_by('ha.ROOM_NO_CODE');
    	$hostelers = $this->legacy_db->get()->result_array();
    	$HOSTEL_NAME = $hostelers[0]['HOSTEL_NAME'];
    	
    	
    	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);		
    	$pdf->SetCreator(PDF_CREATOR);
    	$pdf->SetAuthor(PDF_AUTHOR);
    	$pdf->SetTitle($HOSTEL_NAME);
    	$pdf->SetSubject('');
    	$pdf->SetKeywords('');
    	$pdf->setPrintHeader(false);
    	$pdf->setPrintFooter(false);
    	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    	$pdf->SetAutoPageBreak(FALSE);
    	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    		require_once(dirname(__FILE__).'/lang/eng.php');
    		$pdf->setLanguageArray($l);
    	}		
    	$imageF = K_PATH_IMAGES.'hostel_id_card_front.png';
    	$imageB = K_PATH_IMAGES.'hostel_id_card_back.png';
    	$sign = K_PATH_IMAGES.'boys_provost_sign.png';
    	$logoT = K_PATH_IMAGES.'logo_t.jpg';
    	$pdf->AddPage();
    	$border = 0;
    	$x = 14.4;
    	$y = 3;
    	$w = 9;
    	$h = 5.8;
    	$count = count($hostelers);
    	$numPages = round($count/6);
    	$prev_program = "";
    	$k = 0;
    	foreach ($hostelers as $key=>$hosteler) {
    	    $k = $k + 1;
    	    $PROFILE_IMAGE = K_PATH_PROFLE_IMAGES.$hosteler['PROFILE_IMAGE'];
            $pdf->Image($imageF, $x, $y, 90, 58, '','',true);
    		$pdf->Image($imageB, $x+90.6, $y, 90, 58, '','',true);
    	    $pdf->SetAlpha(0.5);
    		$pdf->Image($logoT, $x+90.6, $y+17, 85.6, 25.6, $type = 'JPG', $link = '', $align = 'C', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, 0, $fitbox = 'CM', $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
            $pdf->SetAlpha(1);
    		$pdf->Image($PROFILE_IMAGE, $x+4, $y+16.2, 24, 29.5, '','','CM',false,300,'',false,false,0,false,false,false);
    		$pdf->SetTextColor(100, 87, 0, 0);
    		$pdf->SetFont('tangent', '', 8.5);
			$pdf->MultiCell(8, 0, 'ID # ', $border, 'L', 0, 1, $x+65, $y+17, true, 0, false, true, 0);
			$pdf->MultiCell(30, 0, 'Student\'s Name :', $border, 'L', 0, 1, $x+30, $y+18, true, 0, false, true, 0);
			$pdf->MultiCell(18, 0, 'Room No :', $border, 'L', 0, 1, $x+30, $y+31.3, true, 0, false, true, 0);
			$pdf->MultiCell(30, 0, 'Valid Upto :', $border, 'L', 0, 1, $x+30, $y+37.3, true, 0, false, true, 0);
			$pdf->MultiCell(30, 0, 'Father\'s Name :', $border, 'L', 0, 1, $x+98, $y+18, true, 0, false, true, 0);
			$pdf->MultiCell(15, 0, 'Roll No :', $border, 'L', 0, 1, $x+150, $y+26.5, true, 0, false, true, 0);
			if(!empty($hosteler['LAST_NAME'])) $pdf->MultiCell(30, 0, 'Surname :', $border, 'L', 0, 1, $x+98, $y+26.5, true, 0, false, true, 0);
			$pdf->MultiCell(30, 0, 'Address :', $border, 'L', 0, 1, $x+98, $y+35, true, 0, false, true, 0);

			$pdf->SetTextColor(255, 255, 255, 255);
			$pdf->SetFont('sanskrit', 'B', 10);
			$pdf->MultiCell(14, 0, $hosteler['APPLICATION_ID'], $border, 'L', 0, 1, $x+73, $y+16.7, true, 0, false, true, 0);
			$pdf->MultiCell(59, 0, $hosteler['FIRST_NAME'], $border, 'L', 0, 1, $x+30, $y+21.5, true, 0, false, true, 0);
			$pdf->MultiCell(80, 0, $hosteler['FNAME'], $border, 'L', 0, 1, $x+98, $y+21.5, true, 0, false, true, 0);
			$pdf->SetFont('times', 'B', 10);
			$pdf->MultiCell(30, 0, $hosteler['ROLL_NO'], $border, 'L', 0, 1, $x+150, $y+30, true, 0, false, true, 0);
			$pdf->SetFont('sanskrit', 'B', 10);
			if(!empty($hosteler['LAST_NAME'])) $pdf->MultiCell(52, 0, $hosteler['LAST_NAME'], $border, 'L', 0, 1, $x+98, $y+30, true, 0, false, true, 0);
			$pdf->SetFont('times', 'BI', 8);
			$pdf->MultiCell(80, 8, $hosteler['PERMANENT_ADDRESS'], $border, 'L', 0, 1, $x+98, $y+38.5, true, 0, false, true, 8, 'T');
			$pdf->SetFont('times', 'B', 11);
			$pdf->MultiCell(12, 0, $hosteler['ROOM_NO'], $border, 'L', 0, 1, $x+48, $y+30.8, true, 0, false, true, 0);
			$pdf->SetFont('times', 'B', 10);
			$pdf->SetTextColor(0, 100, 100, 0);
			$pdf->SetFont('sanskrit', 'B', 9);
			$pdf->MultiCell(30, 0, 'DECEMBER, 2024', $border, 'L', 0, 1, $x+30, $y+40.8, true, 0, false, true, 0);
			$pdf->SetTextColor(255, 255, 255, 255);
			$pdf->SetFont('calibrib', 'B', 7);
			$pdf->Image($sign, $x+60, $y+30.8, 30, 12, '','','CM',false,300,'',false,false,1,false,false,false);
			$pdf->MultiCell(30, 0, 'PROVOST BOYS HOSTELS', $border, 'C', 0, 1, $x+60, $y+41.7, true, 0, false, true, 0);

    		$pdf->SetTextColor(0, 0, 0, 0);
    		if(strlen($hosteler['DEPT_NAME']) > 52) { $pdf->SetFont('calibrib', 'B', 6.5); } else { $pdf->SetFont('calibrib', 'B', 7.5); }
    		$pdf->MultiCell(74.5, 0, $hosteler['DEPT_NAME'], $border, 'L', 0, 1, $x+15, $y+11, true, 0, false, true, 0);
    		$pdf->SetFont('clrndnk', 'B', 9);
    		$pdf->MultiCell(86, 10, $hosteler['HOSTEL_NAME'], $border, 'C', 0, 1, $x+2, $y+47.5, true, 0, false, true, 10, 'M');
    		$pdf->MultiCell(70, 13.5, $hosteler['PROGRAM_TITLE'], $border, 'C', 0, 1, $x+92, $y+1.2, true, 0, false, true, 13.5, 'M');
    		$style = array('border' => true,'vpadding' => 3,'hpadding' => 5,'fgcolor' => array(0,0,0),'bgcolor' => array(255,255,255));
    		$qr_data = json_encode(array("A" => $hosteler['APPLICATION_ID'], "B" => $hosteler['HOSTEL_ID'], "C" => $hosteler['ROOM_NO']));
            $pdf->write2DBarcode($qr_data, 'QRCODE,H',$x+164, $y+1, 13.8, 13.6, $style, 'B', true);
    		
    		$pdf->SetTextColor(0, 100, 100, 0);
    		$pdf->SetFont('arialb', '', 12);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],0,1), $border, 'C', 0, 1, $x+94.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],1,1), $border, 'C', 0, 1, $x+100.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],2,1), $border, 'C', 0, 1, $x+105.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],3,1), $border, 'C', 0, 1, $x+111.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],4,1), $border, 'C', 0, 1, $x+116.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, "-", $border, 'C', 0, 1, $x+122.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],5,1), $border, 'C', 0, 1, $x+127.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],6,1), $border, 'C', 0, 1, $x+133.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],7,1), $border, 'C', 0, 1, $x+138.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],8,1), $border, 'C', 0, 1, $x+144.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],9,1), $border, 'C', 0, 1, $x+149.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],10,1), $border, 'C', 0, 1, $x+155.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],11,1), $border, 'C', 0, 1, $x+160.7, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, "-", $border, 'C', 0, 1, $x+166.2, $y+48.7, true, 0, false, true, 0);
			$pdf->MultiCell(5, 0, substr($hosteler['CNIC_NO'],12,1), $border, 'C', 0, 1, $x+171.7, $y+48.7, true, 0, false, true, 0);
    		$pdf->SetTextColor(255, 255, 255, 255);
    		
    	    $y = $y + 58.3;
    		if((++$key)%5 == 0){
			    //if($key != count($hostelers)-1){
				    $pdf->AddPage();
    	            $x = 14.4;
				    $y = 3;
				    $k=-1;
				//}
			}							
    	}
    	
    	$pdf->lastPage();
    	ob_end_clean();
    // 	prePrint($HOSTEL_NAME);
    // 	exit;
    	$pdf->Output('Hostel.pdf', 'I');
        exit();
	}
}
