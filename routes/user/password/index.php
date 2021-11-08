<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../models/student.php';
include_once '../../../models/user.php';
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

$_GET = json_decode(base64_decode(file_get_contents('php://input')), true);

// get posted data
if(!isset($_GET['oldpassword'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_GET['newpassword'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
$old = htmlspecialchars(strip_tags($_GET['oldpassword']));

$new =  htmlspecialchars(strip_tags($_GET['newpassword']));

if(strlen($new) < 8){
    echo sendJson(array('code'=>0,'message'=>"Password Length too short", 'payload'=>null));
    return;
}

try{
   
$database = new Db();
$db = $database->getConnection();
$student = new Student($db);
$user= new User($db);
if (!$student->verify($decoded->data->id)){
    echo sendJson(array('code'=>0, 'message'=>'Student does not exist', 'payload'=>null));
return;
}
$user->id = $decoded->data->id;
$res = $user->changePassword($old,$new);
if($res['code'] == 0){
    echo sendJson(array('code'=>0, 'message'=>$res['message'], 'payload'=>null));
    return;
}
echo sendJson(array('code'=>1, 'message'=>'Password Changed', 'payload'=>null));
return;
} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
