<?php
include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../config/query.php';
include_once '../../../models/applicant.php';
include_once '../../../models/session.php';
include_once '../../../models/payment.php';
include_once '../../../models/student.php';
include_once '../../../models/n.php';
// generate json web token


header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);
$postdata =   json_decode(file_get_contents('php://input'), true);

use Firebase\JWT\JWT;

$headers =  apache_request_headers();

$token = isset($headers['Authorization']) ? $headers['Authorization'] : "";



if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


try {
    $decoded = JWT::decode($token, $key, array('HS256'));

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


/*
    if($app->app['status'] != 3 && ! $student->checkAdmissionState($app->app['appd_id']) ){
        echo sendJson(array('code' => 0, 'message' => "You do not qualify to make payments. You have not been admitted !", 'payload' => ["redirect" => true]));
        return;
    }
*/
    $verify = $payment->getPaymentType(true);


         foreach($verify as $v){
             if($v['clear_id'] == 1){
                $pay_type = $v['id'];
                $payment->info = $v;
                break;
             }
         }
     

      $payment->external = $database->getExtConnection();

    $info['name'] = $decoded->data->surname . ', ' . $decoded->data->firstname . ' ' . $decoded->data->othername;
    $info['email'] = $decoded->data->email;
    $info['phone'] = $decoded->data->phone;

   if ($payment->checkAcceptPayInfo($app->app['appd_id'], $postdata['session'], $pay_type)) {

                      if($payment->pay_info['status']==1){
                        echo sendJson(array('code' => 1, 'message' => "Payment Found", 'payload' => $payment->pay_info));
                        return;
                      }

echo 1;
   }else{
        $payment->setPaymentInfo($info);
        //echo sendJson($student->info);
        $rrrQuery = $payment->getRRR();

        if ($rrrQuery['code'] == 0) {
            echo sendJson(array('code' => 0, 'message' => $rrrQuery['message'], 'payload' => null));
            return;
        }
    
        $saveResp = $payment->save($app->app['appd_id'], $pay_type, $postdata['session'], $app->app['appd_id']);

        if ($saveResp['code'] == 0) {
            echo sendJson(array('code' => 0, 'message' => $saveResp['message'], 'payload' => null));
            return;
        }
    }

    $info = $payment->pay_info;
    $info['name'] = $decoded->data->surname . ', ' . $decoded->data->firstname . ' ' . $decoded->data->othername;
    $info['email'] = $decoded->data->email;
    $info['phone'] = $decoded->data->phone;
 
    $info['appd_id'] = $app->app['appd_id'];


    $info['gateway'] = GATEWAYRRRPAYMENTURL;
    $info['merchant'] = MERCHANTID;
    $info['response'] = APP_PATH;
   
 

    echo sendJson(array("code" => 1, "message" => "Payment Generated", "payload" => $info));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
