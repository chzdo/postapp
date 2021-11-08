<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../config/query.php';
include_once '../../../models/clearance.php';
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
$_GET = json_decode(base64_decode($_GET['0']),true);
// get posted data
if(!isset($_GET['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
$session = $_GET['session'];



try{
   
$database = new Db();
$db = $database->getConnection();
$app = $database->getAppConnection();
$clear = new Clearance($db);
$sess = new Session($db);

$clear->portal = new Query($db);
$clear->app = new Query($app);
$session_flag = false;

if (!$sess->check($session)){
    echo sendJson(array('code'=>0, 'message'=>'Session Not found', 'payload'=>null ));
   return;
}

if(!$sess->isCurrent($session) || $sess->isClosed($session) ){
   $session_flag = true;
}


$resp = $clear->getFinalClearanceInfo(true);

if ($clear->getClearance($session)){

    echo sendJson(array('code'=>1, 'message'=>'found', 'payload'=>['students'=>$clear->students, 'session_flag'=> $session_flag, 'clear_count'=>$clear->clear_type] ));
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
