<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../models/clearance.php';
include_once '../../../models/session.php';
include_once '../../../models/student.php';
include_once '../../../models/tokenizer.php';
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


     $_POST = json_decode(base64_decode(file_get_contents('php://input')), true);
// get posted data
if(!isset($_POST['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_POST['reason'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if (!isset($_POST['token'])){
    echo sendJson(array('code'=>0, 'message'=>'Token Required', 'payload'=>null ));
return;
}
$session = $_POST['session'];
$reason = $_POST['reason'];
$token = $_POST['token'];

try{
   
$database = new Db();
$db = $database->getConnection();
$clear = new Clearance($db);
$sess = new Session($db);
    $tokenizer = new Tokens($db);

   

if (!$sess->check($session)){
    echo sendJson(array('code'=>0, 'message'=>'Session Not found', 'payload'=>null ));
   return;
}

if(!$tokenizer->verify($token)){
    echo sendJson(array('code'=>0, 'message'=>'Token Does Not exist', 'payload'=>null ));
    return;
}

$info['reason'] = $reason;
$info['session'] = $session;
if(!$tokenizer->verifyReason($info)){
    echo sendJson(array('code'=>0, 'message'=>'Reason Match failed', 'payload'=>null ));
    return;
}

echo sendJson(array('code'=>1, 'message'=>'Authorized', 'payload'=>null ));




 

    

   




 
} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
