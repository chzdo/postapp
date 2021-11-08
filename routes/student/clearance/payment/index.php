<?php

include_once '../../../../vendor/autoload.php';
include_once '../../../../config/core.php';
include_once '../../../../config/db.php';
include_once '../../../../models/student.php';
include_once '../../../../models/logs.php';
include_once '../../../../models/session.php';
include_once '../../../../models/payment.php';
include_once '../../../../models/clearance.php';
header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('X-Actual-Content-Length', '1000');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');


// generate json web token



use \Firebase\JWT\JWT;

$headers =  apache_request_headers();

$token = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}

try {
    $decoded = JWT::decode($token, $key, array('HS256'));
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => $e->getMessage() . ' ' . RESPONSE['auth'], 'payload' => null));
    return;
}

$_GET = json_decode(base64_decode($_GET['0']),true);
// get posted data
if (!isset($_GET['id'])) {

    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($_GET['type'])) {

    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($_GET['session'])) {

    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
$id = htmlspecialchars(strip_tags($_GET['id']));
$sess_id = htmlspecialchars(strip_tags($_GET['session']));
$type_id = htmlspecialchars(strip_tags($_GET['type']));


try {

    $database = new Db();
    $db = $database->getConnection();
    $student = new Student($db);
    $session = new Session($db);
    $type = new Payment($db);
    $clear = new Clearance($db);
    if (!$student->verify($id)) {
        echo sendJson(array('code' => 0, 'message' => 'User not found', 'payload' => null));
        return;
    }

    if (!$session->check($sess_id)) {
        echo sendJson(array('code' => 0, 'message' => 'Session Not found', 'payload' => null));
        return;
    }

    if ($session->isClosed($sess_id) || !$session->isCurrent($sess_id)) {
        echo sendJson(array('code' => 0, 'message' => 'Session is not accepting payments', 'payload' => null));
        return;
    }


    $student_type = $student->newStudent($sess_id);

    if ((int)$student_type == -1) {
        echo sendJson(array('code' => 0, 'message' => 'There is no payment option for this session', 'payload' => null));
        return;
    }
    $verify = $type->getPaymentType($student_type);

    $valid = false;
    $pay_type = '';
    foreach ($verify as $key => $value) {
        if ($value['clear_id'] == $type_id) {
            $valid = true;
            $pay_type = $value['id'];
        }
    }
    
    if (!$valid) {
        echo sendJson(array('code' => 0, 'message' => 'This payment option is not available for you', 'payload' => null));
        return;
    }


    if (!$type->check($pay_type)) {
        echo sendJson(array('code' => 0, 'message' => 'Payment Type not found', 'payload' => null));
        return;
    }


    if ($student_type) {
        $info['student_id'] = $student->info['student_id'];
        $info['appd_id'] = $student->info['appd_id'];
        $info['clear_type'] = 1;
        $info['session'] = $sess_id;
        if ($type_id != 1) {

            if ($clear->VerifyClear($info)) {
                echo sendJson(array('code' => 0, 'message' => 'You have not been cleared for this payment', 'payload' => null));
                return;
            }
            $info['clear_type'] = (int) $type_id - 1;
            if ($clear->VerifyClear($info)) {
                echo sendJson(array('code' => 0, 'message' => 'You have not been cleared for this payment', 'payload' => null));
                return;
            }
        } else {
            if (!$session->checkCurrentAdmission($sess_id)) {
                echo sendJson(array('code' => 0, 'message' => 'This Session is not currently accepting students', 'payload' => null));
                return;
            }
        }
    } else {
        $info['student_id'] = $id;
        $info['clear_type'] = SCHOOL_FEE;
        $info['session'] = $sess_id;
        if (!$session->isCurrent($sess_id)) {
            echo sendJson(array('code' => 0, 'message' => 'This Session is not accepting payments', 'payload' => null));
            return;
        }

    }
    $info['student_id'] = $id;
    $type->external = $database->getExtConnection();
    $info['name'] = $student->info['firstname'] . ' ' . $student->info['othername'] . ' ' . $student->info['surname'];
    $info['email'] = $student->info['email'];;
    $info['phone'] = $student->info['phone'];

    if (!$type->checkPayInfo($student->info['student_id'],$student->info['appd_id'], $sess_id, $pay_type)) {
        $type->setPaymentInfo($info);
        //echo sendJson($student->info);
        $rrrQuery = $type->getRRR();

        if ($rrrQuery['code'] == 0) {
            echo sendJson(array('code' => 0, 'message' => $rrrQuery['message'], 'payload' => null));
            return;
        }
    
        $saveResp = $type->save($info['student_id'], $pay_type, $sess_id, $decoded->data->id);

        if ($saveResp['code'] == 0) {
            echo sendJson(array('code' => 0, 'message' => $saveResp['message'], 'payload' => null));
            return;
        }
    }

    $info['student_id'] = $info['student_id'];
    $info['RRR'] = $type->pay_info['RRR'];
    $info['hash'] = $type->pay_info['hash'];
    $info['orderid'] = $type->pay_info['orderId'];
    $info['amount'] = $type->info['amount'];
    $info['purpose'] = $type->info['name'];
    $info['session'] = $session->name;
    $info['gateway'] = GATEWAYRRRPAYMENTURL;
    $info['merchant'] = MERCHANTID;
    $info['response'] = PATH;

    (new Logs($db))->eventLog($decoded->data->id, EVENTS['genRRR'] . ' for ' . $info['student_id'] . ' in ' . $info['session'] . ' session');
    echo sendJson(array('code' => 1, 'message' => 'Success', 'payload' => $info));
    return;
} catch (Exception $e) { // Note: safer fully qualified exception 
    //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
}
//var_dump($payload);


 








//============================================================+
// END OF FILE
//============================================================+
