<?php
include_once '../../../../../../vendor/autoload.php';

include_once '../../../../../../config/db.php';


require_once '../../../../../../models/dept.php';
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

$postdata =    json_decode(base64_decode(file_get_contents('php://input')), true);



if (!isset($postdata['student_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['reg_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['score'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['score']['ca1'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['score']['ca2'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['score']['exam'])) {
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
if (!isset($postdata['semester_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

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
    if (!$course->check($postdata['course_id'])) {
        echo sendJson(array('code' => 0, 'message' => "Inavlid Course", 'payload' => null));
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
    $session_flag = false;
    
    if(!$prog->isCurrent($postdata['session_id']) || !$prog->isAdminActive($postdata['session_id'] ) ){
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
           
          $info['id'] = $postdata['student_id'];
          $info['session'] = $postdata['session_id'];
            if(!$tokenizer->isReason($info,'result_upload')){
                echo sendJson(array('code'=>0, 'message'=>'Reason Match failed', 'payload'=>null ));
                return;
            }
         
     }
   
     if ($postdata['score']['ca1'] > 20 ||$postdata['score']['ca2'] > 20 ||((float)$postdata['score']['ca2'] + (float)$postdata['score']['ca1'] + (float)$postdata['score']['exam'] )> 100 ) {
        echo sendJson(array('code' => 0, 'message' => "Invalid Score", 'payload' => null));
        return;
    }
    if (!$stu->checkRegID($postdata['reg_id'],$postdata['course_id'])){
        echo sendJson(array('code' => 0, 'message' => "This Course has not been registered", 'payload' => null));
        return;
    }


     $response = $stu->setResult($postdata['score'],$postdata['reg_id'],new Logs($db,$decoded->data->id),$postdata['course_id'],$postdata['session_id']);
     if (!$response) {
        echo sendJson(array('code' => 0, 'message' => "Could not save! Something went wrong", 'payload' => null));
        return;
     }
    
     $course->external = $database->getExtConnection();
     $response = $course->getResult($postdata['session_id'],$postdata['semester_id']);

     if ($response['code'] == 0) {
         echo sendJson(array('code' => 0, 'message' => $response['message'], 'payload' => null));
         return;
     }
  
     $ss = $postdata['semester_id'] == 1? 'First Semester': 'Second Semester';
(new Logs($db))->eventLog($decoded->data->id, EVENTS['upload_result'].$course->name .'  '.$prog->name.' '.$ss.' for student'.$stu->info['student_id']);
echo sendJson(array('code' => 1, 'message' => 'found', 'payload' => $response['payload']));
 return;


} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
