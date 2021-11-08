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


use Firebase\JWT\JWT;
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
if (!isset($postdata['firstname'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['othername'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['surname'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
$email = $decoded->data->email;

try {
   
    $database = new Db();
    $db = $database->getAppConnection();
    $query = new Query($db);
    $app = new Applicant($query);
    $email = filter_var($email,FILTER_VALIDATE_EMAIL);

if($email == false){
    echo sendJson(array('code' => 0, 'message' => "Invalid email address", 'payload' => null));
    return;
}

 if(!$app->verifyEmail($email)){
    echo sendJson(array('code' => 0, 'message' => "Email does not exist", 'payload' => null));
    return;
 }
 $postdata['email'] = $email;

 $resp = $app->set_name($postdata);
 if($resp['code'] == 0){
    echo sendJson(array('code' => 0, 'message' => $resp['message'], 'payload' => null));
    return;
 }
$decoded->data->firstname = $resp['payload']['firstname'];
$decoded->data->othername = $resp['payload']['othername'];
$decoded->data->surname = $resp['payload']['surname'];
 $token = array(
    "iss" => $app_iss,
    "aud" => $app_aud,
    "iat" => $app_iat,
    "nbf" => $app_nbf,
    "exp" => $app_exp,
    "data" => $decoded->data
         
 );


 $jwt = JWT::encode($token, $app_key);

  echo sendJson(array("code"=>1, "message"=>"name saved", "payload"=>array("jwt"=>$jwt,"name"=>$resp['payload'])));
  return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
