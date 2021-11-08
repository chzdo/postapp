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

if (!isset($postdata['code'])) {
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


//$phone = filter_input((int)$postdata['phone'],FILTER_SANITIZE_NUMBER_INT);

if(!preg_match("/^[0-9]{4}$/",$postdata['code'])){
    echo sendJson(array('code' => 0, 'message' => "Invalid Phone Number", 'payload' => null));
    return;
}


 $resp = $app->verifyAccount($postdata);
 if($resp['code'] == 0){
    echo sendJson(array('code' => 0, 'message' => $resp['message'], 'payload' => null));
    return;
 }


  echo sendJson(array('code' => 1, 'message' => $resp['message'], 'payload' => null));
  return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
