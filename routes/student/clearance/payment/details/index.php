<?php 

include_once '../../../../../vendor/autoload.php';
include_once '../../../../../config/core.php';
include_once '../../../../../config/db.php';
include_once '../../../../../models/student.php';
include_once '../../../../../models/clearance.php';
include_once '../../../../../models/payment.php';
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

$_GET = json_decode(base64_decode($_GET['0']),true);
// get posted data
if(!isset($_GET['id'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_GET['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_GET['type'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
$id = htmlspecialchars(strip_tags($_GET['id']));
$session = htmlspecialchars(strip_tags($_GET['session']));
$type = htmlspecialchars(strip_tags($_GET['type']))
;


try{
   
$database = new Db();
$db = $database->getConnection();
$clearance = new Clearance($db);
$student = new Student($db);
$pay = new Payment($db);


if(!$student->verify($id)){
    echo sendJson(array('code'=>0, 'message'=>'Student doesnt exist', 'payload'=>null));
    return;
}
$pay->external = $database->getExtConnection();
$pay_info =$clearance->getPayInfo($type);
if($pay->checkPayInfo($id,$student->info['appd_id'],$session,$pay_info['id'])){
    echo sendJson(array('code'=>1, 'message'=>'Found', 'payload'=>$pay->pay_info));
    return;
}

    echo sendJson(array('code'=>0, 'message'=>'not found', 'payload'=>null));
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
