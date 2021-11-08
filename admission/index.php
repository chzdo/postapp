<?php error_reporting(0); ?>
<html>
        <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        </head>

        <body>

                <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">
                    
        <img src="../../public/logo.png" style="width:70px ; height: 70px" class="rounded" alt="...">
        <h5>
            FEDERAL UNIVERSITY OF LAFIA
        </h5>
        <h6>
            SCHOOL OF POSTGRADUATE STUDIES
        </h6>



<?php 

include_once '../vendor/autoload.php';
include_once '../config/core.php';
include_once '../config/db.php';
include_once '../models/clearance.php';
include_once '../models/student.php';
include_once '../models/session.php';
include_once '../models/n.php';

// get posted data
$data = $_GET;
// get posted data

if(!isset($data['a'])){
     
    echo     '           <div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">Oooops!</h4>
    <p>Oops! Invalid Access</p>
    <hr>
    <p class="mb-0">This Link is either broken or incomplete</p>
  </div>';
    return;
}
if(!isset($data['load'])){
    echo     '           <div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">Oooops!</h4>
    <p>Oops! Invalid Access</p>
    <hr>
    <p class="mb-0">This Link is either broken or incomplete</p>
  </div>';
    return;
}

$id = base64_decode($data['a']);
$load = base64_decode($data['load']);
$database = new Db();
$db = $database->getConnection();


$student = new Student($db);
$session = new Session($db);
if (!$student->checkAdmissionState($id)){
    echo    '           <div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">Oooops!</h4>
    <p>Oops! Invalid Access</p>
    <hr>
    <p class="mb-0">You have not been offered admission</p>
  </div>';
return;
}

$s = json_decode($load, true);


$response = $student->createUser($s['appd_id'], ACCOUNT['STUDENT']);
    if($response['code']== 0){
        echo    '           <div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">Oooops!</h4>
    <p>Oops! Invalid Access</p>
    <hr>
    <p class="mb-0">'. $response['message'] .'</p>
  </div>';return;
    }else{
 $acad =   $student->saveAcademicInfo(array(
        "appd_id" => $s['appd_id'],
        "student_id" =>  $s['appd_id'],
        "session_admitted"=> $student->adm_info['session'],
        "dept_id" => $s['dept_id'],
        "faculty_id"=>$s['faculty_id'],
        "prog_id" => $s['programme_id'],
        "options_id" => $s['specialization']
    ));

    if(!$acad){
        $student->rollback($id,1);
        echo    '           <div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">Oooops!</h4>
    <p>Oops! Error</p>
    <hr>
    <p class="mb-0">Somethong went wrong</p>
  </div>';return;
    }

    $bio =   $student->saveBioInfo($s);
    if(!$bio){
        $student->rollback($id,2);
        echo    '           <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Oooops!</h4>
        <p>Oops! Error</p>
        <hr>
        <p class="mb-0">Somethong went wrong</p>
      </div>';return;
    }
    echo    '           <div class="alert alert-success" role="alert">
    <h4 class="alert-heading">Congratulations!</h4>
    <p>Yeah! </p>
    <hr>
    <p class="mb-0">Congratulations you have initiated the process of accepting your admission! To pay for your ACCEPTANCE FEE,  <a class="btn btn-success" href="'.URL2.'"> Click here to proceed </a>. Remember to USE your REGISTRATION NUMBER as your username and password.</p>
  </div>'; 

  $n = new N($client,$db);
 
  $info = array("name"=>$s['surname'],"email"=>$s['email'], "id"=>$s['appd_id'], "type"=>EMAIL_CODE_ACCEPT , "cron"=>false);
  $n->notify(EMAIL_CODE_ACCEPT,$info);

}







 

//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
