<?php
include_once '../../../../../../vendor/autoload.php';

include_once '../../../../../../config/db.php';

require_once '../../../../../../models/phpxcel.php';
require_once '../../../../../../models/dept.php';
require_once '../../../../../../models/assignment.php';
require_once '../../../../../../models/courses.php';
require_once '../../../../../../models/session.php';
require_once '../../../../../../models/student.php';
require_once '../../../../../../models/logs.php';
require_once '../../../../../../models/tokenizer.php';
// generate json web token
include_once '../../../../../../config/core.php';

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

$postdata =    json_decode(base64_decode($_GET["0"]),true);



if (!isset($postdata['type'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if($postdata['type']==1){
if (!isset($postdata['ass_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
}else{
    if (!isset($postdata['course_id'])) {
        echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
        return;
    }
    if (!isset($postdata['session'])) {
        echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
        return;
    }
}

try {
    $dept = new Dept($db);
    $ass = new Assignment($db);
    $course = new Courses($db);
    $prog = new Session($db);
    $stu = new Student($db);
    if($postdata['type'] == 1){
    if (!$ass->check($postdata['ass_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Assignment does not exist", 'payload' => null));
        return;
    }
    $app = $ass->getCourseAssignment();
}else{
    if (!$course->check($postdata['course_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Assignment does not exist", 'payload' => null));
        return;
    }
    $app = $ass->getCourseAssignmentList($postdata['course_id'],$postdata['session']);
}
 
 if($app !== false){
    echo sendJson(array('code' => 1, 'message' => "Found", 'payload' => $app));
    return;
 }  
         
 echo sendJson(array('code' => 0, 'message' => 'Somethin went wrong', 'payload' => null));
 return;


} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
