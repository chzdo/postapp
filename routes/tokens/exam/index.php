<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../models/clearance.php';
include_once '../../../models/email.php';
include_once '../../../models/session.php';
include_once '../../../models/student.php';
include_once '../../../models/tokenizer.php';
header('Access-Control-Allow-Origin: '.$aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('X-Actual-Content-Length' , '1000');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');


// generate json web token


use \Firebase\JWT\JWT;


$headers =  apache_request_headers();

$token =isset($headers['Authorization']) ? $headers['Authorization'] : '';

if(!$token){
   
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
   return;
 }

 try {
    $decoded = JWT::decode($token, $key, array('HS256'));

   
}catch(Exception $e){
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
    return;
}


     $postdata =json_decode(base64_decode($_GET['0']),true);;
// get posted data
if(!isset($postdata['email'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}

if(!isset($postdata['name'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}

try{
   
$database = new Db();
$db = $database->getConnection();
$email = new email($db);


$code = rand(1000,9999);

$response = $client->request('GET', URL.'/api/emailview/result_auth.php', [
    'query' => ['name' => htmlspecialchars(strip_tags($postdata['name'])), "code"=>$code]
]);

 $data= $response->getBody()->getContents();
$email->addReciever(array(array('email'=>$postdata['email'],"html"=>$data)));
$email->setHeading('Result Authorization Code','admin@pgschool.com','PG SCHOOL');
$email->send();
$token = array(
    "iss" => $iss,
    "aud" => $aud,
    "iat" => $iat,
    "nbf" => $nbf,
    "exp" => $res_exp,
    "data" => array(
        "emal" => $postdata['email'],
        "name" => $postdata['name'],
        "code" => $code,
        "id"=>$decoded->data->id       
        
       
    )
 );
 $jwt = JWT::encode($token, $result_key);



echo sendJson(array('code'=>1, 'message'=>'A code has been sent to your mail.', 'payload'=>array("code"=>base64_encode($code),"token"=>base64_encode($jwt)) ));




 

    

   




 
} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
