<?php 

include_once '../../../vendor/autoload.php';

include_once '../../../config/db.php';
include_once '../../../models/faculty.php';
include_once '../../../models/logs.php';
include_once '../../../models/dept.php';
include_once '../../../models/programme.php';
include_once '../../../models/session.php';
include_once '../../../models/options.php';
// generate json web token
include_once '../../../config/core.php';
header("Access-Control-Allow-Origin: $aud");
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');



use \Firebase\JWT\JWT;


$headers =  apache_request_headers();
 
$token =isset($headers['Authorization']) ? $headers['Authorization'] : '';
if(!$token){
   
  echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
return;
}

try {
  $decoded = JWT::decode($token, $key, array('HS256'));

  if($decoded->data->spgs != 1  ){
      echo sendJson(array('code'=>0,'message'=>RESPONSE['auth'], 'payload'=>null));
   return;
    }
}catch(Exception $e){
  echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
  return;
}
$_GET = json_decode(base64_decode(file_get_contents('php://input')),true);
if(!isset($_GET['session'])){
     
    sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
}
if(!isset($_GET['state'])){
     
  sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
}
  $session = htmlspecialchars(strip_tags($_GET['session']));




try{
$database = new Db();
$db = $database->getConnection();
$session = new Session($db);
if(!$session->checkCurrentAdmission($_GET['session'])){
  echo sendJson(array("code"=>0, "message"=>"Session does not exist"));
  return;
}
 $resp = $session->updateAdmissionSession($_GET);
 (new Logs($db))->eventLog($decoded->data->id,EVENTS['updADMState'].' for '.$session->name);
    echo sendJson($resp);
} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);



 








//============================================================+
// END OF FILE
//============================================================+
    ?>

