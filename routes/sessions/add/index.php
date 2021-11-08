<?php
include_once '../../../vendor/autoload.php';

include_once '../../../config/db.php';


require_once '../../../models/session.php';

require_once '../../../models/logs.php';
// generate json web token
include_once '../../../config/core.php';

header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



use \Firebase\JWT\JWT;


$database = new Db();
$db = $database->getConnection();


// get posted data

$headers =  apache_request_headers();

$token = isset($headers['Authorization']) ? $headers['Authorization'] : "";



if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


try {
    $decoded = JWT::decode($token, $key, array('HS256'));

    if ($decoded->data->fmis != 1) {
        echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
        return;
    }
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}

$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);

if (!isset($postdata['name'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}



try {
    $sess = new Session($db);
 
  if (!preg_match('/^\d{4}\/\d{4}$/',$postdata['name'])){
    echo sendJson(array('code' => 0, 'message' => 'invalid session pattern', 'payload' => null));
    return;
  }


    if($sess->check($postdata['name'],1)){
        echo sendJson(array('code' => 0, 'message' => 'Session already exist', 'payload' => null));
        return;
    }
    $response = $sess->add($postdata['name'], $decoded->data->id);
    if ($response['code'] == 1) {
        (new Logs($db))->eventLog($decoded->data->id, EVENTS['added_s'].' '.$postdata['name']);

        echo sendJson(array('code' => 1, 'message' => $response['message'], 'payload' => $response['payload']));
    } else {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
    }
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
