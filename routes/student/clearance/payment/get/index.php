<?php 

include_once '../../../../../vendor/autoload.php';
include_once '../../../../../config/core.php';
include_once '../../../../../config/db.php';
include_once '../../../../../models/student.php';
require_once '../../../../../models/session.php';
include_once '../../../../../models/logs.php';
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







try{
   
$database = new Db();
$db = $database->getConnection();
$student = new Student($db);
$clearance = new Clearance($db);
$session = new Session($db);
$type = new Payment($db);
if (!$student->verify($decoded->data->id)){
    echo sendJson(array('code'=>0, 'message'=>'User not found', 'payload'=>null));
return;
}



$verify = $type->getPayments($decoded->data->id,$student->info['appd_id']);


echo sendJson(array('code'=>1, 'message'=>'found', 'payload'=>$verify));
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
