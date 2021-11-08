<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDFEdit
 *
 * @author RVMP GLOBAL
 */
use setasign\Fpdi\Fpdi;





class PDFEdit {

    //put your code here

    function __construct() {
        
    }

    function getAdmissionLetter($details) {

        $source = ADMISSION_LETTER_PATH.'letter.pdf';

        $pdf = new FPDI('Portrait', 'mm', array(215.9, 279.4)); // Array sets the X, Y dimensions in mm

        $pdf->AddPage();
      $pagecount = $pdf->setSourceFile($source);
        $tppl = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tppl);
   
        $pdf->useTemplate($tppl, 0, 0, null, null);

        $pdf->SetFont('times', 'B', 15); // Font Name, Font Style (eg. 'B' for Bold), Font Size
        $pdf->SetTextColor(0, 0, 0); // RGB 
        $pdf->SetXY(130, 85); // X start, Y start in mm for reference
        $pdf->Write(0,  $details['appd_id'] );
        $name = str_replace("/","", $details['appd_id']);
        $location = ADMISSION_LETTER_PATH.$name.".pdf";
       
        $pdf->Output($location, "F");
       
        return $location;
    }

}
?>