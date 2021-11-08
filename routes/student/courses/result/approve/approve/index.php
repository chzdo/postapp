<?php
include_once '../../../../../../vendor/autoload.php';

include_once '../../../../../../config/db.php';

require_once '../../../../../../models/phpxcel.php';
require_once '../../../../../../models/dept.php';
require_once '../../../../../../models/faculty.php';
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
$approve_id = array("default"=> 0 , "lecturer"=> 1, "HOD"=> 2, "DEAN"=> 3 , "PG"=> 4 , "SENATE"=> 5);

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

$postdata =   json_decode(base64_decode($_GET["0"]),true);;
if (!isset($postdata['Auth_id'])) {
    echo sendJson(array('code' => 0, 'message' => "No Authorization Code", 'payload' => null));
    return;
}

try {
    $result = JWT::decode(base64_decode($postdata['Auth_id']), $result_key, array('HS256'));

} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => "Invalid Authorization Code", 'payload' => null));
    return;
}

if($result->data->id != $decoded->data->id ){
    echo sendJson(array('code' => 0, 'message' => "Invalid Authorization Code", 'payload' => null));
    return;
}

if (!isset($postdata['session_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['semester_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['course_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}


if (!isset($postdata['approve_code'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['approve_comment'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
try {
    $dept = new Dept($db);
    $fac = new Faculty($db);
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
    if (!$dept->check($decoded->data->dept_id)) {
        echo sendJson(array('code' => 0, 'message' => "Department does not exist", 'payload' => null));
        return;
    }

    if (!$fac->check($decoded->data->faculty_id)) {
        echo sendJson(array('code' => 0, 'message' => "Faculty does not exist", 'payload' => null));
        return;
    }


    if (!($postdata['semester_id'] != 1 || $postdata['semester_id'] != 2)) {
        echo sendJson(array('code' => 0, 'message' => "Invalid Semester", 'payload' => null));
        return;
    }

    if(!$prog->isClosed($postdata['session_id']) ){
        echo sendJson(array('code' => 0, 'message' => "Session has not closed for registration. Result uploading only starts after registration ends", 'payload' => null));
        return;
     }
    if(array_search($postdata['approve_code'],$approve_id) == false){
        echo sendJson(array('code' => 0, 'message' => "Unauthorized", 'payload' => null));
        return;
    }

    if( $postdata['approve_code'] < 4){
   if (!$course->checkCourseDept($decoded->data->dept_id,$decoded->data->faculty_id)){
    echo sendJson(array('code' => 0, 'message' => "Invalid Department or Faculty", 'payload' => null));
    return;
   }
    }

    if ($postdata['approve_code'] == 1){
        if (!$course->isLecturer($postdata['semester_id'],$postdata['session_id'],$decoded->data->id)){
            echo sendJson(array('code' => 0, 'message' => "Invalid Lecturer", 'payload' => null));
            return;
           }
    }

   $app = $course->approveResult($postdata['session_id'],$postdata['semester_id'],$decoded->data->id,$postdata['approve_code'],$postdata['approve_comment']);

 if($app['code']==0){
    echo sendJson(array('code' => 0, 'message' => $app['message'], 'payload' => null));
    return;
 }
                    
 echo sendJson(array('code' => 1, 'message' => 'found', 'payload' => $app['payload']));
 return;


} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
