<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../models/clearance.php';
include_once '../../../models/student.php';
include_once '../../../models/session.php';
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
// get posted data
$data = json_decode(base64_decode($_GET["0"]),true);
// get posted data

if(!isset($data['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}


$sess = htmlspecialchars(strip_tags($data['session']));
if($decoded->data->role == 4){
    if(!isset($data['id'])){
     
        echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
        return;
    }
    $student_id = $data['id'];
}else{
$student_id = $decoded->data->id;
}
try{
   
$database = new Db();
$db = $database->getConnection();
$clear = new Clearance($db);
$student = new Student($db);
$session = new Session($db);
if (!$student->verify($student_id)){
    echo sendJson(array('code'=>0, 'message'=>'User not found', 'payload'=>null));
return;
}
if (!$session->check($sess)) {
    echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
    return;
}
$student_type = $student->newStudent($sess);
if((int)$student_type == -1){
    echo sendJson(array('code'=>0, 'message'=>'There is no payment option for this session', 'payload'=>null));
    return;
}



if ($clear->getActive($student_type)){
    echo sendJson(array('code'=>1, 'message'=>'found', 'payload'=>$clear->all));
}else{
    echo sendJson(array('code'=>0, 'message'=>'not found', 'payload'=>null));
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
