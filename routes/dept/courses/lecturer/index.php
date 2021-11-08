<?php
include_once '../../../../vendor/autoload.php';

include_once '../../../../config/db.php';
require_once '../../../../models/session.php';
include_once '../../../../models/programme.php';

require_once '../../../../models/dept.php';
require_once '../../../../models/logs.php';
// generate json web token
include_once '../../../../config/core.php';


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
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}

$postdata =   json_decode(base64_decode($_GET["0"]),true);


if (!isset($postdata['id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['semester'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['session'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

try {
    $sess = new Session($db);
    $dept = new Dept($db);
    $course = new Courses($db);
    $prog = new Programme($db);
    if (!$dept->check($decoded->data->dept_id)) {
        echo sendJson(array('code' => 0, 'message' => "Department is not offered in PG", 'payload' => null));
        return;
    }
    if (!$sess->check($postdata['session'])) {
        echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
        return;
    }
    if (!($postdata['semester'] != 1 || $postdata['semester'] != 2)) {
        echo sendJson(array('code' => 0, 'message' => "Invalid Semester", 'payload' => null));
        return;
    }
    $response = $course->getCourses($postdata['id'], $postdata['semester'], $postdata['session']);

    if ($response===false) {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
        return;
    }

   
    echo sendJson(array('code' => 1, 'message' => 'found', 'payload' => $response));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
