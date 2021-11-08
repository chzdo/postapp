<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../models/student.php';
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
$_GET = json_decode(base64_decode($_GET["0"]),true);
if(!isset($_GET['id'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_GET['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
$id = htmlspecialchars(strip_tags($_GET['id']));
$session = htmlspecialchars(strip_tags($_GET['session']));



try{
   
$database = new Db();
$db = $database->getConnection();
$student = new Student($db);
if(!$student->verify($id)){
    echo sendJson(array('code'=>0, 'message'=>'Student Does not exist', 'payload'=>null));
    return;
}
if ($student->getClearance($id, $session)){
    echo sendJson(array('code'=>1, 'message'=>'found', 'payload'=>$student->clearance));
}else{
    echo sendJson(array('code'=>1, 'message'=>'not found', 'payload'=>[]));
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
