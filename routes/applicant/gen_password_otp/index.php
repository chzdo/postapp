<?php
include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../config/query.php';
include_once '../../../models/applicant.php';
include_once '../../../models/n.php';
// generate json web token


header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);

if (!isset($postdata['email'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}




try {
   
    $database = new Db();
    $db = $database->getAppConnection();
    $query = new Query($db);
    $app = new Applicant($query);
    $email = filter_var($postdata['email'],FILTER_VALIDATE_EMAIL);

if($email == false){
    echo sendJson(array('code' => 0, 'message' => "Invalid email address", 'payload' => null));
    return;
}

 if(!$app->verifyEmail($postdata['email'])){
    echo sendJson(array('code' => 0, 'message' => "Email does not exist", 'payload' => null));
    return;
 }

 $postdata['type']=APP_CODES_RECOVER_PASSWORD;
 $resp = $app->resendOTP($postdata);
 if($resp['code'] == 0){
    echo sendJson(array('code' => 0, 'message' => $resp['message'], 'payload' => null));
    return;
 }

 $notify = new N($client,$db); 
  $info = array("code"=> $resp['payload'], "email"=>$postdata['email'], "type"=>EMAIL_CODE_APP_PASSWORD , "cron"=>false);
  $notify->notify(EMAIL_CODE_APP_PASSWORD,$info);
  echo sendJson(array('code' => 1, 'message' => $resp['message'], 'payload' => null));
  return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
