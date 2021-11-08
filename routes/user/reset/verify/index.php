<?php 

include_once '../../../../vendor/autoload.php';

include_once '../../../../config/db.php';


 require_once '../../../../models/user.php';
 require_once '../../../../models/email.php';
// generate json web token
include_once '../../../../config/core.php';
header('Access-Control-Allow-Origin: '.$aud);
header('Access-Control-Allow-Methods: GET');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



use \Firebase\JWT\JWT;


$database = new Db();
$db = $database->getConnection();

$user = new User($db);
// get posted data
$data = json_decode(base64_decode(file_get_contents('php://input')),true);
if(!isset($data['hash'])){
    echo sendJson(array('code'=>0,'message'=>"Code is equired", 'payload'=>null));
    return;
}


$resp = $user->verifyHash($data);


if($resp['code']==0){
  
    echo sendJson(array('code'=>0,'message'=>$resp['message'], 'payload'=>null));
    return;
}
$email = new email();


echo sendJson(array('code'=>1,'message'=>$resp['message'], 'payload'=>null));
return;


?>