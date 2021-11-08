<?php
include_once '../../../../../vendor/autoload.php';

include_once '../../../../../config/db.php';

require_once '../../../../../models/phpxcel.php';
require_once '../../../../../models/dept.php';
require_once '../../../../../models/assignment.php';
require_once '../../../../../models/courses.php';
require_once '../../../../../models/session.php';
require_once '../../../../../models/student.php';
require_once '../../../../../models/logs.php';
require_once '../../../../../models/tokenizer.php';
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

$postdata =    $_POST;



if (!isset($postdata['session_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['course_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['question'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}


if (!isset($postdata['file_type'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['dead_line'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['staff_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
try {
  
    $ass = new Assignment($db);
    $course = new Courses($db);
    $prog = new Session($db);
    $stu = new Student($db);
    if (!$course->check($postdata['course_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Course does not exist", 'payload' => null));
        return;
    }
    if (!$prog->check($postdata['session_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
        return;
    }
  
if(isset($_FILES['file'])){
    $ass->setFile($_FILES['file']);
}
 if($ass->create($postdata)){
    echo sendJson(array('code' => 1, 'message' => "assignment created", 'payload' => null));
    return;
 }
                    
 echo sendJson(array('code' => 0, 'message' => 'Somethin went wrong', 'payload' => null));
 return;


} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
