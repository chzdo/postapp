<?php 

include_once '../../../../vendor/autoload.php';
include_once '../../../../config/core.php';
include_once '../../../../config/db.php';
include_once '../../../../models/student.php';
include_once '../../../../models/clearance.php';
include_once '../../../../models/session.php';
include_once '../../../../models/payment.php';
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

  
    
    
}catch(Exception $e){
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
    return;
}

 $_GET = json_decode(base64_decode($_GET["0"]),true);
// get posted data
if(!isset($_GET['id'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_GET['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}

$id = htmlspecialchars(strip_tags($_GET['id']));
$sess = htmlspecialchars(strip_tags($_GET['session']));

;


try{
   
$database = new Db();
$db = $database->getConnection();

$clearance = new Clearance($db);
$student = new Student($db);
$session = new Session($db);
if (!$student->verify($decoded->data->id,1)){
    echo sendJson(array('code'=>0, 'message'=>'User not found or not active', 'payload'=>null));
return;
}
if (!$session->check($sess)) {
    echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
    return;
}
$info['appd_id'] = $student->info['appd_id'];
$info['student_id'] = $student->info['student_id'];;
$student_type = $student->newStudent($sess);

if((int)$student_type == -1){
    echo sendJson(array('code'=>0, 'message'=>'You have not been cleared', 'payload'=>null));
    return;
}
$info['session'] = $sess;
$notcleared = $clearance->checkFinalClearanceInfo($info,$student_type);

    echo sendJson(array('code'=>1, 'message'=>'found', 'payload'=>$notcleared));
return;




} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
