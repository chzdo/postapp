<?php 
include_once '../../../vendor/autoload.php';

include_once '../../../config/db.php';


 require_once '../../../models/faculty.php';
// generate json web token
include_once '../../../config/core.php';

header('Access-Control-Allow-Origin: '.$aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



use \Firebase\JWT\JWT;


$database = new Db();
$db = $database->getConnection();


// get posted data

  $headers =  apache_request_headers();
 
$token =isset($headers['Authorization']) ? $headers['Authorization'] : "";



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
    $faculty= new Faculty($db);
   
    if($faculty->all()){
        echo sendJson(array('code'=>1,'message'=>"found", 'payload'=>$faculty->all));
    }else{
        echo sendJson(array('code'=>0,'message'=>"No Session Found", 'payload'=>null));
    }
    }catch(Exception $e){
      
        echo sendJson(array('code'=>0,'message'=>$e->getMessage(), 'payload'=>null));
        return;
    }

 

?>