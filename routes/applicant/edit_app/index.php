<?php
include_once '../../../vendor/autoload.php';
include_once '../../../config/core.php';
include_once '../../../config/db.php';
include_once '../../../config/query.php';
include_once '../../../models/applicant.php';
include_once '../../../models/session.php';
include_once '../../../models/payment.php';
include_once '../../../models/n.php';
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





try {

    $database = new Db();
    $db = $database->getAppConnection();
  //  $db_staff = $database->getExtConnection();
    $db_portal = $database->getConnection();
    $query = new Query($db);
 //   $query_staff = new Query($db_staff);
    $app = new Applicant($query);
    $session = new Session($db_portal);
    $payment = new Payment($db_portal);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $app->portal = new Query($db_portal);
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


    //$app->query_staff = $query_staff;
    $postdata['email'] = $email;
    if (!$app->isAppUser($postdata)) {
        echo sendJson(array('code' => 0, 'message' => "No App found for this session", 'payload' => ["redirect" => true]));
        return;
    }

    if($app->app['status'] != 2 ){
        echo sendJson(array('code' => 0, 'message' => "You do not qualify to make payments. Please submit your application first!", 'payload' => ["redirect" => true]));
        return;
    }

    $info['name'] = $decoded->data->surname . ', ' . $decoded->data->firstname . ' ' . $decoded->data->othername;
    $info['email'] = $decoded->data->email;
    $info['phone'] = $decoded->data->phone;
    $info['appd_id'] = $app->app['appd_id'];

    $postdata['type'] = APP_EDIT_PAYMENT;
    $fee = $app->getFee(APP_EDIT_PAYMENT);
    
   
    $payment->info = $fee;
   $l = $app->checkPayment($postdata);
 
        if ($l && @$app->payment['status'] == 0) {
         
            $payment->pay_info = $app->payment;
            $info['status'] = $app->payment['status'];
         
        
        
     
    } else {
     
        $payment->setPaymentInfo($info);
        $r = $payment->getRRR();
        if ($r['code'] == 0) {
            echo sendJson(array("code" => 0, "message" => "Remita Error: Could not generate RRR", "payload" => null));
            return;
        }

        $pay = array(
            "type" => APP_EDIT_PAYMENT, "amount" => $payment->info['amount'],
            "order_id" => $payment->pay_info['orderId'], "rrr" => $payment->pay_info['RRR'],
            "session" => $postdata['session'], "email" => $email, "appd_id" => $app->app['appd_id']
        );
        $resp = $app->savePayment($pay);
        if ($resp['code'] == 0) {
            echo sendJson($resp);
            return;
        }
    }


    $info['RRR'] = $payment->pay_info['RRR'];
    $info['hash'] = $payment->pay_info['hash'];
    $info['orderid'] = $payment->pay_info['orderId'];
    $info['amount'] = $payment->info['amount'];
    $info['purpose'] = $payment->info['purpose'];
    $info['session'] = $session->name;
    $info['gateway'] = APP_GATEWAYRRRPAYMENTURL;
    $info['merchant'] = APP_MERCHANTID;
   $info['response'] = APP_URL."/".$postdata['session'].'/confirm/'.$postdata['type'];

    echo sendJson(array("code" => 1, "message" => "Payment Generated", "payload" => $info));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
