<?php 

include_once '../../../../../vendor/autoload.php';
include_once '../../../../../config/core.php';
include_once '../../../../../config/db.php';
include_once '../../../../../models/student.php';
include_once '../../../../../models/logs.php';
include_once '../../../../../models/clearance.php';
include_once '../../../../../models/payment.php';
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

$_POST = json_decode(base64_encode(file_get_contents("php://input")), true);
// get posted data
if(!isset($_POST['id'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_POST['rrr'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
if(!isset($_POST['orderid'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
$id = htmlspecialchars(strip_tags($_POST['id']));
$rrr = htmlspecialchars(strip_tags($_POST['rrr']));
$order_id = htmlspecialchars(strip_tags($_POST['orderid']))
;


try{
   
$database = new Db();
$db = $database->getConnection();
$student = new Student($db);
$clearance = new Clearance($db);
$type = new Payment($db);
if (!$student->verify($id)){
    echo sendJson(array('code'=>0, 'message'=>'User not found', 'payload'=>null));
return;
}

if (!$type->VerifyRRR($rrr,$id,$student->info['appd_id'],$order_id)){
    echo sendJson(array('code'=>0, 'message'=>'This RRR does not belong to you ', 'payload'=>null));
return;
}




$verify = $type->verifyPayment();

if($verify == null){
    echo sendJson(array('code'=>0, 'message'=>'Remita Verification Error', 'payload'=>null));
    return;
}
if(!($verify['status']=='00' || $verify['status']=='01')){
    (new Logs($db))->eventLog($decoded->data->id,EVENTS['verifyRRR'].' for '.$id );
    echo sendJson(array('code'=>0, 'message'=>$verify['message'], 'payload'=>null));
    return;
}


$update = $type->updatePayment();

if(!$update){
    echo sendJson(array('code'=>0, 'message'=>'Cound Not Update Payment', 'payload'=>null));
    return;
}

$clr['student_id'] = $type->pay_info['student_id'];
$clr['clear_type'] = $type->pay_info['pay_type'];
$clr['session'] = $type->pay_info['session'];
$clearance->VerifyClear($clr)
;

if(!$clearance->VerifyClear($clr)){
    echo sendJson(array('code'=>0, 'message'=>'you have been cleared previously', 'payload'=>$type->pay_info));
    return;
}
$clear = $clearance->clear($clr);

if($clear){
    (new Logs($db))->eventLog($decoded->data->id,EVENTS['clearRRR'].' for '.$id. ' in '.$clr['session'].' session' );
    echo sendJson(array('code'=>1, 'message'=>'Payment verified and cleared', 'payload'=>$type->pay_info));
    return;
}
echo sendJson(array('code'=>0, 'message'=>'Payment Verified but not cleared', 'payload'=>null));
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
