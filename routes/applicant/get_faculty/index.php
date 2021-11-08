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
    $db_staff = $database->getExtConnection();
    $db_portal = $database->getConnection();
    $query = new Query($db);
    $query_staff = new Query($db_staff);
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
    if (!$session->checkCurrentAdmission($postdata['session'])) {
        echo sendJson(array('code' => 0, 'message' => "This Session is not accepting payments", 'payload' => ["redirect" => true]));
        return;
    }

    if ($session->info['app_state'] == 0) {
        echo sendJson(array('code' => 0, 'message' => "registration for this session has ended", 'payload' => ["redirect" => true]));
        return;
    }

    $app->query_staff = $query_staff;
    $postdata['email'] = $email;
    if (!$app->isAppUser($postdata)) {
        echo sendJson(array('code' => 0, 'message' => "No App found for this session", 'payload' => ["redirect" => true]));
        return;
    }

  
  $app->getFaculty();
    echo sendJson(array("code" => 1, "message" => "Payment Generated", "payload" => $app->faculty));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}