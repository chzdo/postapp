<?php
include_once '../../../../vendor/autoload.php';
include_once '../../../../config/core.php';
include_once '../../../../config/db.php';
include_once '../../../../config/query.php';
include_once '../../../../models/applicant.php';
include_once '../../../../models/session.php';
include_once '../../../../models/programme.php';
include_once '../../../../models/faculty.php';
include_once '../../../../models/dept.php';
include_once '../../../../models/options.php';
include_once '../../../../models/payment.php';
include_once '../../../../models/n.php';
// generate json web token


header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);

use Firebase\JWT\JWT;
use Mpdf\Tag\Option;

$headers =  apache_request_headers();

$token = isset($headers['Authorization']) ? $headers['Authorization'] : "";



if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


try {
    $decoded = JWT::decode($token, $app_key, array('HS256'));
    
    if ($decoded->data->status != 1 ) {
        echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
        return;
    }
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


$email = $decoded->data->email;

if (!isset($postdata['session'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}



 
  
  

try {
   
    $database = new Db();
    $db = $database->getAppConnection();
    $db_portal = $database->getConnection();
    $query = new Query($db);
    $app = new Applicant($query);
    $session = new Session($db_portal);
    $faculty = new Faculty($db_portal);
    $dept = new Dept($db_portal);
    $prog = new Programme($db_portal);
    $opt = new Options($db_portal);
    $email = filter_var($email,FILTER_VALIDATE_EMAIL);
    $app->portal = new Query($db_portal);
if($email == false){
    echo sendJson(array('code' => 0, 'message' => "Invalid email address", 'payload' => null));
    return;
}

 if(!$app->verifyEmail($email)){
    echo sendJson(array('code' => 0, 'message' => "Email does not exist", 'payload' => null));
    return;
 }
 if(!$session->check($postdata['session'])){
    echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
    return;
 }

 $postdata['email'] = $email;
 if(!$app->isAppUser($postdata)){
    echo sendJson(array('code' => 0, 'message' => "No App found for this session", 'payload' => ["redirect"=>true]));
    return;
 }
 
 if(!$app->app['status'] == 1){
    echo sendJson(array('code' => 0, 'message' => "You cannot perform this operation", 'payload' => ["redirect"=>true]));
    return;
 }
 
 $b = $app->removeOther();
   if(!$b){
    echo sendJson(array('code' => 0, 'message' => "Could not save your information", 'payload' => ["redirect"=>true]));
    return;
   }
   $app->getOther();
   $a = $app->getSideSummary();

 
   $b = $app->getSummary();
  echo sendJson(array("code"=>1 , "message"=>"Other up","payload"=>['referee'=>$app->referee, 'summary'=>array("side"=>$a,"main"=>$b)]));
   return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
