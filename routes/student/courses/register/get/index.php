<?php
include_once '../../../../../vendor/autoload.php';

include_once '../../../../../config/db.php';


require_once '../../../../../models/dept.php';
require_once '../../../../../models/courses.php';
require_once '../../../../../models/session.php';
require_once '../../../../../models/student.php';
// generate json web token
include_once '../../../../../config/core.php';

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



if (!isset($postdata['student_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['semester_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['session_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

try {
    $dept = new Dept($db);
    $course = new Courses($db);
    $prog = new Session($db);
    $stu = new Student($db);
 
    if (!$stu->verify($postdata['student_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Student does not exist", 'payload' => null));
        return;
    }
    if (!$prog->check($postdata['session_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
        return;
    }
    if (!($postdata['semester_id'] != 1 || $postdata['semester_id'] != 2)) {
        echo sendJson(array('code' => 0, 'message' => "Invalid Semester", 'payload' => null));
        return;
    }
    $response = $stu->getRegisteredCourses($postdata['semester_id'],$postdata['session_id']);

    if ($response['code'] == 0) {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
        return;
    }
    $chosen = array_values($response['payload']);

    $response = $dept->getCourse($stu->info['dept_id'], $postdata['semester_id'], $stu->info['prog_id']);
    if ($response['code'] == 0) {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
        return;
    }
    $dept->external = $database->getExtConnection();
    $av =  $dept->getPGCordinator($stu->info['dept_id']);
 
    $choices = $response['payload'];
    $choices = filter($choices, $chosen);
   echo sendJson(array('code' => 1, 'message' => 'found', 'payload' => array('chosen' => $chosen, 'choices' => $choices ,'course_adviser'=>array('name'=>$av['completename'],"signature"=>SIGNATURE_URL.$av['signature']))));
    return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
