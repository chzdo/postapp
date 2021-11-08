<?php 

include_once '../../../../vendor/autoload.php';
include_once '../../../../config/core.php';
include_once '../../../../config/db.php';
include_once '../../../../config/query.php';
include_once '../../../../models/logs.php';
include_once '../../../../models/n.php';
include_once '../../../../models/applicant.php';
include_once '../../../../models/clearance.php';
include_once '../../../../models/session.php';
include_once '../../../../models/student.php';
include_once '../../../../models/tokenizer.php';
header('Access-Control-Allow-Origin: '.$aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('X-Actual-Content-Length' , '1000');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');


// generate json web token


use \Firebase\JWT\JWT;


$headers =  apache_request_headers();
 
$token =isset($headers['Authorization']) ? $headers['Authorization'] : '';

if(!$token){
   
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
   return;
 }

 try {
    $decoded = JWT::decode($token, $key, array('HS256'));
    if($decoded->data->fmis != 1 && $decoded->data->spgs != 1 ){
        echo sendJson(array('code'=>2,'message'=>'unauthroized2'.$decoded->data->role, 'payload'=>null));
    }
   
}catch(Exception $e){
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
    return;
}


  $_POST = json_decode(base64_decode(file_get_contents('php://input')), true);
// get posted data

if(!isset($_POST['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_POST['id'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_POST['email'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}

$session = $_POST['session'];
$stu = $_POST['id'];

$email  = $_POST['email'];


try{
   
$database = new Db();
$db = $database->getConnection();

$clear = new Clearance($db);
$app = new Applicant(new Query($database->getAppConnection()));
$sess = new Session($db);
$student = new Student($db);
$session_flag =false;
$clear->portal = new Query($db);
$clear->app = new Query($database->getAppConnection());
$student->portal = new Query($db);
$student->app = $app;
$student->app->portal =new Query($db);
if (!$sess->check($session)){
    echo sendJson(array('code'=>0, 'message'=>'Session Not found', 'payload'=>null ));
   return;
}


 
if(!$sess->isCurrent($session) || $sess->isClosed($session) ){
   // $session_flag = true;
 }

 if(!$student->app->isAppUser(array('session'=>$session, 'email'=>$email))){
    echo sendJson(array('code'=>0, 'message'=>'This Student application was not found', 'payload'=>null ));
    return;
 }


$info['student_id'] = $stu;
$info['appd_id'] = $stu;
$info['clear_type'] = 2;
$info['session'] = $session;
$info['cleared_by'] = $decoded->data->id;


if($session_flag){
    if (!isset($_POST['token'])){
        echo sendJson(array('code'=>0, 'message'=>'Token Required', 'payload'=>null ));
   return;
    }

    $token =$_POST['token'];

    $tokenizer = new Tokens($db);

    if(!$tokenizer->verify($token)){
        echo sendJson(array('code'=>0, 'message'=>'Token Does Not exist', 'payload'=>null ));
        return;
    }
   $c['id'] = $info['student_id'];
    if(!$tokenizer->isReason($c,'clear')){
        echo sendJson(array('code'=>0, 'message'=>'Reason Match failed', 'payload'=>null ));
        return;
    }
 }
 if (!$clear->VerifyQualification($info)){

    echo sendJson(array('code'=>0, 'message'=>'This Student is not qualified to be cleared ', 'payload'=>null ));
   return;
}
if (!$clear->VerifyClear($info)){

    echo sendJson(array('code'=>0, 'message'=>'This Student have been cleared ', 'payload'=>null ));
   return;
}
if (!$student->checkAdmissionState($stu)){
    echo sendJson(array('code'=>0, 'message'=>'This Student has not been admitted', 'payload'=>null ));
    return;
}

if(!$clear->clear($info)){
    echo sendJson(array('code'=>0, 'message'=>'This Student was not cleared ', 'payload'=>null ));
    return;
}

$v = $student->activateStudent();
if($v['code']==0){
    echo sendJson($v);
    return;
}
$n = new N($client,$db); 
$info = array("name"=>$student->user_info['surname'].' '.$student->user_info['firstname'],"email"=>$student->user_info['email'], "id"=>$student->user_info['appd_id'], "type"=>EMAIL_CODE_ACCEPT , "cron"=>false);
$n->notify(EMAIL_CODE_ACCEPT,$info);

(new Logs($db))->eventLog($decoded->data->id,EVENTS['pg_clear_student'].' with ID '.$stu);
$resp = $clear->getFinalClearanceInfo(true);

if ($clear->getClearance($session)){
  

    echo  sendJson(array('code'=>1, 'message'=>'Cleared', 'payload'=>['students'=>$clear->students, 'session_flag'=> $session_flag, 'clear_count'=>$clear->clear_type] ));
   
  

}else{
    echo sendJson(array('code'=>0, 'message'=>'cleareance list not found', 'payload'=>null));
}
 
} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
