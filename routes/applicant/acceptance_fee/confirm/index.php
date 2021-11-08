<?php
include_once '../../../../vendor/autoload.php';
include_once '../../../../config/core.php';
include_once '../../../../config/db.php';
include_once '../../../../config/query.php';
include_once '../../../../models/applicant.php';
include_once '../../../../models/session.php';
include_once '../../../../models/payment.php';
include_once '../../../../models/clearance.php';
include_once '../../../../models/student.php';
include_once '../../../../models/n.php';
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

    if ($decoded->data->status != 1) {
        echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
        return;
    }
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


$email = $decoded->data->email;
if (!isset($postdata['session'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['order_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}



try {

    $database = new Db();
    $db = $database->getAppConnection();
    $db_staff = $database->getExtConnection();
    $db_portal = $database->getConnection();
    $query = new Query($db);
    $query_staff = new Query($db_staff);
    $app = new Applicant($query);
    $session = new Session($db_portal);
    $payment = new Payment($db_portal);
    $clearance= new Clearance($db_portal);
    $student = new Student($db_portal);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($email == false) {
        echo sendJson(array('code' => 0, 'message' => "Invalid email address", 'payload' => null));
        return;
    }

    if (!$app->verifyEmail($email)) {
        echo sendJson(array('code' => 0, 'message' => "Email does not exist", 'payload' => null));
        return;
    }
    if (!$session->check($postdata['session'])) {
        echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
        return;
    };
    if (!$session->checkCurrentAdmission($postdata['session'])) {
        echo sendJson(array('code' => 0, 'message' => "This Session is not accepting payments for acceptance Fee", 'payload' => null));
        return;
    }

    
  
   
    
    $app->query_staff = $query_staff;
    $postdata['email'] = $email;
    if (!$app->isAppUser($postdata)) {
        echo sendJson(array('code' => 0, 'message' => "No App found for this session", 'payload' => ["redirect" => true]));
        return;
    }



    if($app->app['status'] != 3 && ! $student->checkAdmissionState($app->app['appd_id']) ){
        echo sendJson(array('code' => 0, 'message' => "You do not qualify to make payments. You have not been admitted !", 'payload' => ["redirect" => true]));
        return;
    }


if (!$payment->VerifyOrderID(null,$app->app['appd_id'],$postdata['order_id'])){
    echo sendJson(array('code'=>0, 'message'=>'This RRR does not belong to you ', 'payload'=>null));
return;
}

$verify = $payment->verifyOrderPayment();

if($verify == null){
    echo sendJson(array('code'=>0, 'message'=>'Remita Verification Error', 'payload'=>null));
    return;
}
if(!($verify['status']=='00' || $verify['status']=='01')){
  
    echo sendJson(array('code'=>0, 'message'=>$verify['message'], 'payload'=>null));
    return;
}

$update = $payment->updatePayment();

if(!$update){
    echo sendJson(array('code'=>0, 'message'=>'Cound Not Update Payment', 'payload'=>null));
    return;
}

$clr['student_id'] = $payment->pay_info['student_id'];
$clr['appd_id'] = $app->app['appd_id'];
$clr['clear_type'] = $payment->pay_info['clear_type'];
$clr['session'] = $payment->pay_info['session'];
$clr['cleared_by'] = $payment->pay_info['student_id'];


if(!$clearance->VerifyClear($clr)){
    echo sendJson(array('code'=>0, 'message'=>'you have been cleared previously', 'payload'=>$payment->pay_info));
    return;
}
$clear = $clearance->clear($clr);

$n = new N($client,$db);
   
    $info = $payment->pay_info;
    $info['name'] = $decoded->data->surname . ', ' . $decoded->data->firstname . ' ' . $decoded->data->othername;
    $info['email'] = $decoded->data->email;
    $info['phone'] = $decoded->data->phone;

  
    $inf = array(
        "name"=> $info['name'],
        "id" => $info['student_id'] ,
        "email"=> $info['email'],
        "rrr" =>$payment->pay_info['rrr'],
        "session" => $session->name,
        "purpose" =>$payment->pay_info['purpose'],
        "amount" =>$payment->pay_info['amount'],
        "cron"=> false,
        "type"=>EMAIL_CODE_PAY
    );
    $n->notify(EMAIL_CODE_PAY,$inf);
 

    echo sendJson(array("code" => 1, "message" => "Payment Verified", "payload" => $info));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
