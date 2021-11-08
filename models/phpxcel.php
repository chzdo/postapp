<?php
//include '../../../vendor/autoload.php';

//throw new \Exception('Composer autoloader could not be found. Install dependencies with `composer install` and try again.');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class excel
{

  private     $spreadsheet;

  function __construct()
  {
    $this->spreadsheet =   new Spreadsheet();
  }

  function HtmlToExcel($html)
  {

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();

    $this->spreadsheet = $reader->loadFromString(    str_replace(' & ', ' &amp; ', $html));



    $styleArray = [
      'font' => [
         // 'bold' => true,
      ],
      'alignment' => [
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
      ],
      'borders' => [
          'top' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          ],
      ],
      /**
      'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
          'rotation' => 90,
          'startColor' => [
              'argb' => 'FFFFFFFF',
          ],
          'endColor' => [
              'argb' => 'FFFFFFFF',
          ],
      ],
      **/
  ];
  $this->spreadsheet->getActiveSheet()->setShowGridlines(true);
  $this->spreadsheet->getDefaultStyle()->applyFromArray($styleArray);
  $highest = $this->spreadsheet->getActiveSheet()->getHighestColumn();
  $this->spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
  $this->spreadsheet->getActiveSheet()->getStyle('A1:Z'.$this->spreadsheet->getActiveSheet()->getHighestRow())
  ->getAlignment()->setWrapText(true); 
  $this->spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30);
  $this->spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30);
  $this->spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
  $this->spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
  $this->spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
  $this->spreadsheet->getActiveSheet()->getStyle('A1:Z'.$this->spreadsheet->getActiveSheet()->getHighestRow())
  ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
  $this->spreadsheet->getActiveSheet()->getStyle('A1:Z'.$this->spreadsheet->getActiveSheet()->getHighestRow())
  ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
  foreach(range('A','Z') as $columnID) {
  
  //  $this->spreadsheet->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
}
  //  $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Tcpdf');
  //  $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
 $this->output('123');
  }
  function getApplicationList($list = null, $session = null)
  {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath(IMG_PATH);
    $drawing->setHeight(36);
    $drawing->setCoordinates('A1');

    $this->spreadsheet->getProperties()
      ->setCreator(CREATOR)
      ->setLastModifiedBy(CREATOR)
      ->setTitle(CATEGORY_ADMISSION)
      ->setSubject(CATEGORY_ADMISSION)
      ->setDescription(
        "Admission list for Post graduate Student"
      )
      ->setCustomProperty('ShaCode', md5(CATEGORY_ADMISSION))
      ->setCustomProperty('session', $session)
      ->setKeywords(CATEGORY_ADMISSION)
      ->setCategory(CATEGORY_ADMISSION);

    $this->spreadsheet->setShowSheetTabs(false);

    $this->spreadsheet->getSecurity()
      ->setWorkbookPassword(ADMISSION_PASSWORD)
      ->setLockWindows(true)
      ->setLockStructure(true);

    $sheet = $this->spreadsheet->getActiveSheet();
    $sheet->setTitle(CATEGORY_ADMISSION);
    $sheet->getProtection()->setSheet(true)
      ->setPassword(ADMISSION_PASSWORD)
      ->setSort(true)
      ->setInsertRows(true)
      ->setFormatCells(true);


    // $this->spreadsheet->getDefaultStyle()->getProtection()->setLocked(false);
    $sheet->getDefaultColumnDimension()->setAutoSize(true);

    $sheet->mergeCells('B1:F1');
    $drawing->setWorksheet($sheet);
    $sheet->getStyle('B1:G1')->getFont()->setSize(20)->setBold(true);
    //   $sheet->getStyle('A1')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_INHERIT);
    // $sheet->getStyle("A1:G1")->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);


    //  $spreadsheet->getActiveSheet()->mergeCells('A1:F!');
    $sheet->setCellValue('B1', 'FULAFIA POST-GRADUATE  APPLICATION LIST');
    $sheet->mergeCells('b2:c2');
    $dateTimeNow = time();
    $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateTimeNow);
    $sheet->setCellValue(
      'a2',
      'Date'
    );
    $sheet->setCellValue(
      'b2',
      $excelDateValue
    );
    $sheet->setCellValue(
      'd2',
      'Session'
    );
    $sheet->setCellValue(
      'e2',
      $session
    );
    $sheet->getCell('b2')->getStyle()->getNumberFormat()->setFormatCode(
      \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
    );



    //$sheet->getCell("F6:$hih')
    $sheet->fromArray(
      $list,
      null,
      'A5'
    );

    $validation = $sheet->getCell('G6')
      ->getDataValidation();
    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE);
    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Number is not allowed!');
    $validation->setPromptTitle('Allowed input');
    $validation->setPrompt('Only numbers between 0 and 1 are allowed. 1 means Admitted otherwise 0');
    $validation->setFormula1(0);
    $validation->setFormula2(1);
    $hih = $sheet->getHighestRow();
    $j = 7;
    while ($j <= $hih) {
      $sheet->getCell("G$j")->setDataValidation(clone $validation);
      $j++;
    }

    $sheet->getStyle("G6:G$hih")->getProtection()->setLocked(
      \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
    );


    // $writer = new Xls($this->spreadsheet);
    // $filename = rand(2,4).time().'.xls';
    // $writer->save($filename);
    // return $filename;
    $this->output(rand(2, 4) . time());
  }
  function getCourseList($list = null, $session,$semester,$coursename,$course,$coursetitle,$user,$teachers)
  {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath(IMG_PATH2);
    $drawing->setHeight(36);
    $drawing->setCoordinates('A1');

    $this->spreadsheet->getProperties()
      ->setCreator($user)
      ->setLastModifiedBy($user)
      ->setTitle(CATEGORY_RESULT)
      ->setSubject(CATEGORY_RESULT)
      ->setDescription(
        "Result Computation"
      )
      ->setCustomProperty('ShaCode', md5(CATEGORY_RESULT))
      ->setCustomProperty('session', $session)
      ->setCustomProperty('course_id',$course)
      ->setCustomProperty('semester',$semester);


    $this->spreadsheet->setShowSheetTabs(false);

    $this->spreadsheet->getSecurity()
      ->setWorkbookPassword(RESULT_PASSWORD)
      ->setLockWindows(true)
      ->setLockStructure(true);

    $sheet = $this->spreadsheet->getActiveSheet();
    $sheet->setTitle($coursename);
    $sheet->getProtection()->setSheet(true)
      ->setPassword(RESULT_PASSWORD)
      ->setSort(true)
      ->setInsertRows(true)
      ->setFormatCells(true);


    // $this->spreadsheet->getDefaultStyle()->getProtection()->setLocked(false);
    $sheet->getDefaultColumnDimension()->setAutoSize(true);

    $sheet->mergeCells('B1:F1');
    $drawing->setWorksheet($sheet);
    $sheet->getStyle('B1:G1')->getFont()->setSize(20)->setBold(true);
    //   $sheet->getStyle('A1')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_INHERIT);
    // $sheet->getStyle("A1:G1")->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);


    //  $spreadsheet->getActiveSheet()->mergeCells('A1:F!');
    $sheet->setCellValue('B1', 'FULAFIA POSTGRADUATE RESULT SHEET');
    $sheet->mergeCells('b2:c2');
    $dateTimeNow = time();
    $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateTimeNow);
    $sheet->setCellValue(
      'a2',
      'Date'
    );
    $sheet->setCellValue(
      'b2',
      $excelDateValue
    );
    $sheet->setCellValue(
      'd2',
      'Session'
    );
    $sheet->setCellValue(
      'e2',
      $session
    );
    $sheet->setCellValue(
      'f2',
      'Course Code'
    );
    $sheet->setCellValue(
      'g2',
      $coursename
    );
    $sheet->setCellValue(
      'a3',
      'Course Title'
    );
    $sheet->setCellValue(
      'b3',
      $coursetitle
    );
    $sheet->setCellValue(
      'd3',
      'Course Lecturers'
    );
    $sheet->setCellValue(
      'e3',
      $teachers
    );
    $sheet->getCell('b2')->getStyle()->getNumberFormat()->setFormatCode(
      \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
    );

    $this->spreadsheet->getActiveSheet()->getColumnDimension('H')->setVisible(false);


    //$sheet->getCell("F6:$hih')
    $sheet->fromArray(
      $list,
      null,
      'A5'
    );

    $validation = $sheet->getCell('C6')
      ->getDataValidation();
    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE);
    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Number is not allowed!');
    $validation->setPromptTitle('Allowed input');
    $validation->setPrompt('Only numbers between 0 and 20 are allowed');
    $validation->setFormula1(0);
    $validation->setFormula2(20);
    $hih = $sheet->getHighestRow();
    $validation2 = $sheet->getCell('D6')
    ->getDataValidation();
  $validation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE);
  $validation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
  $validation2->setAllowBlank(true);
  $validation2->setShowInputMessage(true);
  $validation2->setShowErrorMessage(true);
  $validation2->setErrorTitle('Input error');
  $validation2->setError('Number is not allowed!');
  $validation2->setPromptTitle('Allowed input');
  $validation2->setPrompt('Only numbers between 0 and 20 are allowed');
  $validation2->setFormula1(0);
  $validation2->setFormula2(20);
  $validation3 = $sheet->getCell('E6')
  ->getDataValidation();
$validation3->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE);
$validation3->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$validation3->setAllowBlank(true);
$validation3->setShowInputMessage(true);
$validation3->setShowErrorMessage(true);
$validation3->setErrorTitle('Input error');
$validation3->setError('Number is not allowed!');
$validation3->setPromptTitle('Allowed input');
$validation3->setPrompt('Only numbers between 0 and 80 are allowed');
$validation3->setFormula1(0);
$validation3->setFormula2(80);
$hih = $sheet->getHighestRow();
  $hih = $sheet->getHighestRow();
    $j = 7;
    while ($j <= $hih) {
      $sheet->getCell("C$j")->setDataValidation(clone $validation);
      $sheet->getCell("D$j")->setDataValidation(clone $validation2);
      $sheet->getCell("E$j")->setDataValidation(clone $validation3);
      $j++;
    }

    $sheet->getStyle("C6:C$hih")->getProtection()->setLocked(
      \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
    );
    $sheet->getStyle("D6:D$hih")->getProtection()->setLocked(
      \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
    );  $sheet->getStyle("E6:E$hih")->getProtection()->setLocked(
      \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
    );

    // $writer = new Xls($this->spreadsheet);
    // $filename = rand(2,4).time().'.xls';
    // $writer->save($filename);
    // return $filename;
    $this->output(rand(2, 4) . time());
  }
  // Create new Spreadsheet object
  function readAdmissionList($file, $session)
  {
    $spreadsheet = IOFactory::load($file);
    $allowed = $spreadsheet->getActiveSheet()->getProtection()->verify(ADMISSION_PASSWORD);
    if (!$allowed) {
      return (object)array("code" => 0,"message"=>'Inavlid Password', "payload" => null);
    }
    if ($spreadsheet->getActiveSheet()->getHighestRow() < 6) {
      return (object)array("code" => 0,"message"=>'Inavlid File', "payload" => null);
    }
    $shacode = $spreadsheet->getProperties()->getCustomPropertyValue('ShaCode');
    if (!($shacode == md5(CATEGORY_ADMISSION))) {
      return (object)array("code" => 0, "message"=>'Inavlid File token', "payload" => null);
    }

    $sses = $spreadsheet->getProperties()->getCustomPropertyValue('session');
    if (!($sses == $session )) {
      return (object)array("code" => 0, "message"=>'Inavlid File for this session', "payload" => null);
    }
    $sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
    return (object)array("code" => 1, 'message'=>'success', "payload" => $sheetData);
  }
  function readResult($file,$course, $session,$semester)
  {
    $spreadsheet = IOFactory::load($file);
    $allowed = $spreadsheet->getActiveSheet()->getProtection()->verify(RESULT_PASSWORD);
    if (!$allowed) {
      return (object)array("code" => 0,"message"=>'Inavlid Password', "payload" => null);
    }
    if ($spreadsheet->getActiveSheet()->getHighestRow() < 6) {
      return (object)array("code" => 0,"message"=>'Inavlid File', "payload" => null);
    }
    $shacode = $spreadsheet->getProperties()->getCustomPropertyValue('ShaCode');
    if (!($shacode == md5(CATEGORY_RESULT))) {
      return (object)array("code" => 0, "message"=>'Inavlid File token', "payload" => null);
    }

    $sses = $spreadsheet->getProperties()->getCustomPropertyValue('session');
    if (!($sses == $session )) {
      return (object)array("code" => 0, "message"=>'Inavlid File for this session', "payload" => null);
    }
    $sses = $spreadsheet->getProperties()->getCustomPropertyValue('semester');
    if (!($sses == $semester )) {
      return (object)array("code" => 0, "message"=>'Inavlid File ', "payload" => null);
    }
    $sses = $spreadsheet->getProperties()->getCustomPropertyValue('course_id');
    if (!($sses == $course)) {
      return (object)array("code" => 0, "message"=>'Inavlid File for this course', "payload" => null);
    }
    //$spreadsheet->getActiveSheet()->getColumnDimension('H')->setVisible(true);
    $sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
    return (object)array("code" => 1, 'message'=>'success', "payload" => $sheetData);
  }

  // Set document properties
  function output($name)
  {
    ob_end_clean();
    // Redirect output to a client’s web browser (Xls)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
    $writer->save('php://output');
    die;
    //$writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
    //$writer->save('php://output');
    //die;
  }
}

?>