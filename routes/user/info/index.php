<?php 

include_once '../../../vendor/autoload.php';

include_once '../../../config/db.php';


 require_once '../../../models/user.php';
// generate json web token
include_once '../../../config/core.php';
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
$data = (Object) json_decode(base64_decode($_GET["0"]));

$token =isset($data->key) ? $data->key : "";


if(!$token){
  
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
    return;
}
    try{
    $decoded = JWT::decode($token, $key, array('HS256'));

    }catch(Exception $e){
        echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
        return;
    }
  try{
    echo sendJson(array('code'=>1,'message'=>"found", 'payload'=>$decoded->data));
   
    }catch(Exception $e){
      
        echo sendJson(array('code'=>0,'message'=>$e->getMessage(), 'payload'=>null));
    }



?>