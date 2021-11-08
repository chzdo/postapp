<?php
include_once '../../../../../vendor/autoload.php';

include_once '../../../../../config/db.php';


require_once '../../../../../models/dept.php';
require_once '../../../../../models/courses.php';
require_once '../../../../../models/session.php';
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

$postdata =    json_decode(base64_decode(file_get_contents('php://input')), true);


if (!isset($postdata['dept_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

if (!isset($postdata['semester_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['list'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['session_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['course_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
try {
    $dept = new Dept($db);
    $course = new Courses($db);
    $prog = new Session($db);
    if(!$dept->check($postdata['dept_id'])){
        echo sendJson(array('code' => 0, 'message' => "Department does not exist", 'payload' => null));
        return;
    }
    if(!$prog->check($postdata['session_id'])){
        echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
        return;
    }
    if(!$course->check($postdata['course_id'])){
        echo sendJson(array('code' => 0, 'message' => "Course does not exist", 'payload' => null));
        return;
    }
    if(!($postdata['semester_id'] != 1 || $postdata['semester_id'] != 2)){
        echo sendJson(array('code' => 0, 'message' => "Invalid Semester", 'payload' => null));
        return;
    }
    $session_flag = false;
    
    if(!$prog->isCurrent($postdata['session_id']) || $prog->isClosed($postdata['session_id']) ){
        $session_flag = true;
     }
     if($session_flag){
      
            if (!isset($postdata['token'])){
                echo sendJson(array('code'=>0, 'message'=>'Token Required', 'payload'=>null ));
           return;
            }
        
            $token =$postdata['token'];
        
            $tokenizer = new Tokens($db);
        
            if(!$tokenizer->verify($token)){
                echo sendJson(array('code'=>0, 'message'=>'Token Does Not exist', 'payload'=>null ));
                return;
            }
           
          $info['id'] = $course->name;
          $info['session'] = $postdata['session_id'];
            if(!$tokenizer->isReason($info,'assign')){
                echo sendJson(array('code'=>0, 'message'=>'Reason Match failed', 'payload'=>null ));
                return;
            }
         
     }

$dept->external = $database->getExtConnection();
    $response = $dept->setAssignedCourses($postdata['list'],$postdata['dept_id'],$decoded->data->s_dept_id,$postdata['session_id'],$postdata['course_id'],$postdata['semester_id'],$decoded->data->id);
    if ($response['code'] == 0) {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
        return;
     }
    

     $response = $dept->getAssignedCourses($postdata['dept_id'], $postdata['session_id'],$postdata['course_id'], $postdata['semester_id']);

    if ($response['code'] == 0) {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
        return;
    }
    $chosen = $response['payload'];
    $response = $dept->getLectures($postdata['dept_id']);
    if ($response['code'] == 0) {
        echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
        return;
    }
    $choices = $response['payload'];
    $choices = filter($choices, $chosen);
   
(new Logs($db))->eventLog($decoded->data->id, EVENTS['setassign_d'].'  with  name '.$dept->name .'  for'.$prog->name);
 echo sendJson(array('code' => 1, 'message' => 'found', 'payload' => array('chosen'=>$chosen, 'choices'=>$choices)));
 return;


} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
