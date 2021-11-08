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
    $db_portal = $database->getConnection();
    //  $db_staff = $database->getExtConnection();
    $query = new Query($db);
    // $query_staff = new Query($db_staff);
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



    $info['name'] = $decoded->data->surname . ', ' . $decoded->data->firstname . ' ' . $decoded->data->othername;
    $info['email'] = $decoded->data->email;
    $info['phone'] = $decoded->data->phone;

    $fee = $app->getFee(APP_PAYMENT);
    $payment->info = $fee;
    $postdata['type'] = APP_PAYMENT;

    $resp = $app->checkPayment($postdata);
    if ($resp && @$app->payment['status'] == 1) {

        $app->payment['name'] = $info['name'];
        $app->payment['phone'] = $info['phone'];
        $app->payment['purpose'] = $fee['purpose'];
        $app->payment['session'] = $session->name;
        echo sendJson(array("code" => 1, "message" => "You have already made payments", "payload" => $app->payment));
        return;
    } else {
        if (!$session->checkCurrentAdmission($postdata['session'])) {
            echo sendJson(array('code' => 0, 'message' => "This Session is not accepting payments", 'payload' => null));
            return;
        }
    
        if ($session->info['app_state'] == 0) {
            echo sendJson(array('code' => 0, 'message' => "registration for this session has ended", 'payload' => null));
            return;
        }
        if ($resp && @$app->payment['status'] == 0) {
            $payment->pay_info = $app->payment;
        } else {
            $payment->setPaymentInfo($info);
            $r = $payment->getRRR();
            if ($r['code'] == 0) {
                echo sendJson(array("code" => 0, "message" => "Remita Error: Could not generate RRR", "payload" => null));
                return;
            }

            $pay = array(
                "type" => APP_PAYMENT, "amount" => $payment->info['amount'],
                "order_id" => $payment->pay_info['orderId'], "rrr" => $payment->pay_info['RRR'],
                "session" => $postdata['session'], "email" => $email, "appd_id" => null
            );
            $resp = $app->savePayment($pay);
            if ($resp['code'] == 0) {
                echo sendJson($resp);
                return;
            }
            $r = $app->start_application($postdata);
            if ($r['code'] == 0) {
                echo sendJson($r);
                return;
            }
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
    $info['response'] = APP_URL . "/" . $postdata['session'] . '/confirm/' . $postdata['type'];

    echo sendJson(array("code" => 1, "message" => "payment generated", "payload" => $info));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
