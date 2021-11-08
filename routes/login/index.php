<?php 


include_once '../../vendor/autoload.php';

include_once '../../config/db.php';


 require_once '../../models/user.php';
 require_once '../../models/n.php';
// generate json web token
include_once '../../config/core.php';
require_once '../../models/logs.php';
header('Access-Control-Allow-Origin: '.$aud);
header('Access-Control-Allow-Methods: POST');

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use cron\Notify;
use \Firebase\JWT\JWT;


$database = new Db();
$db = $database->getConnection();

$user = new User($db);
// get posted data
$data = json_decode(file_get_contents("php://input"),true);

      $data = json_decode(base64_decode($data['token']));
     
if(!isset( $data->userid)){
    echo sendJson(['code'=> 0 ,'message'=>RESPONSE['invalid']]);
    return;
}
 
if(!isset( $data->password)){
    echo  sendJson(['code'=> 0 ,'message'=>RESPONSE['invalid']]);
    return;
}
$user->id = $data->userid;
$user->external = $database->getExtConnection();


 if($user->Auth($data->password)){
    $token = array(
        "iss" => $iss,
        "aud" => $aud,
        "iat" => $iat,
        "nbf" => $nbf,
        "exp" => $exp,
        "data" => $user->data 
             
     );
  $jwt = JWT::encode($token, $key);
  
  //   $notify = new N($client,$db);
 
     $info = array("name"=>$user->data['name'],"email"=>$user->data['email'], "type"=>EMAIL_CODE_LOGIN , "cron"=>false);

   //  $notify->notify(EMAIL_CODE_LOGIN,$info);

     echo sendJson(['code'=> 1 ,'message'=>$jwt]);
    // (new Logs($db))->signinLogs($user->id);

 }else{
     echo sendJson(['code'=> 0 ,'message'=>'User Not found']);
 }
?>