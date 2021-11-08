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

use Firebase\JWT\JWT;



$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);

if (!isset($postdata['email'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['password'])) {
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

     $resp = $app->login($postdata);


     if($resp['code']==0){
        echo sendJson($resp);
        return; 
     }


$token = array(
    "iss" => $app_iss,
    "aud" => $app_aud,
    "iat" => $app_iat,
    "nbf" => $app_nbf,
    "exp" => $app_exp,
    "data" => $resp['payload']
         
 );


 $jwt = JWT::encode($token, $app_key);
 echo sendJson(['code'=> 1 ,'message'=>$resp['message'], 'payload'=>$jwt]);

} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
