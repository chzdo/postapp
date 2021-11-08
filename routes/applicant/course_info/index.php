<?php
include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../config/query.php';
include_once '../../../models/applicant.php';
include_once '../../../models/session.php';
include_once '../../../models/payment.php';
include_once '../../../models/n.php';
// generate json web token


header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);
$postdata =   json_decode(file_get_contents('php://input'), true);

use Firebase\JWT\JWT;
$headers =  apache_request_headers();

$token = isset($headers['Authorization']) ? $headers['Authorization'] : "";



if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


try {
    $decoded = JWT::decode($token, $key, array('HS256'));
    
    if ($decoded->data->status != 1 ) {
        echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
        return;
    }
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


$email = $decoded->data->email;
if (!isset($postdata['rrr'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['type'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}


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
    $payment = new Payment($db_portal);
    $email = filter_var($email,FILTER_VALIDATE_EMAIL);

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
 
    
;
 if(! $session->checkCurrentAdmission($postdata['session'])){
    echo sendJson(array('code' => 0, 'message' => "This Session is not accepting payments", 'payload' => null));
    return;
 }

 if($session->info['app_state'] == 0){
    echo sendJson(array('code' => 0, 'message' => "registration for this session has ended", 'payload' => null));
    return;
 }
 
$postdata['email'] = $email;
if (!$app->VerifyRRR($postdata)){
    echo sendJson(array('code'=>0, 'message'=>'This RRR does not belong to you ', 'payload'=>null));
return;
}


$payment->pay_info['rrr'] = $postdata['rrr'];
 $verify = $payment->verifyPayment();
if($verify == null){
    echo sendJson(array('code'=>0, 'message'=>'Remita Verification Error', 'payload'=>null));
    return;
}
if(($verify['status']=='00' || $verify['status']=='01')){
      echo sendJson(array('code'=>0, 'message'=>$verify['message'], 'payload'=>null));
    return;
};

$r = $app->updatePayment();

if(!$r){
    echo sendJson(array("code"=>0, "message"=>"Could not update payment"));
    return;
}
$app->payments['ref'] = "FUL/MIS/SPGS/".$app->payments['order_id'];

  echo sendJson(array("code"=>1 , "message"=>"Payment Verified","payload"=>$app->payments));
  return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
