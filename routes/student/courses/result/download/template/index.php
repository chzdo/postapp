<?php
include_once '../../../../../../vendor/autoload.php';

include_once '../../../../../../config/db.php';

require_once '../../../../../../models/phpxcel.php';
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

$postdata =  json_decode(base64_decode($_GET['0']),true);





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
if (!isset($postdata['dept_id'])) {
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
    if (!$dept->check($postdata['dept_id'])) {
        echo sendJson(array('code' => 0, 'message' => "department does not exist", 'payload' => null));
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
   

    $header = array("S/N","REGISTRATION NUMBER","CA 1","CA 2","EXAM");
    $j=0;
    foreach ($header as $head) {
        $excelArray[0][$j++] = $head;
    }

    $dept->external = $database->getExtConnection();

   $lecturers =  $dept->getAssignedCourses($postdata['dept_id'],$postdata['session_id'],$postdata['course_id'], $postdata['semester_id'])  ; 
        if($lecturers['code']==0){
            echo sendJson(array('code' => 0, 'message' => "course not assigned", 'payload' => null));
            return;
        }
   $l = '';
        foreach($lecturers['payload'] as $key=>$value){
            $l .=  array_key_last($lecturers['payload'])==$key ?  $value['name'] : $value['name'] .",";
        }
   $i = 0;
   $course->external = $database->getExtConnection();
   $app = $course->getResult($postdata['session_id'],$postdata['semester_id']);

                            foreach ($app['payload']['result'] as $id => $details) {
                              ++$i;
                                $excelArray[$i][0] = $i;
                                $excelArray[$i][1] = $details['student_id'];
                                $excelArray[$i][2] =  $details['ca_1'];
                                $excelArray[$i][4] =  $details['ca_2'];
                                $excelArray[$i][5] =  $details['exam'];
                                $excelArray[$i][6] =  '';
                                $excelArray[$i][7] =  '';
                                $excelArray[$i][8] =  $details['id'];
                            }

                          
                            $ss = $postdata['semester_id'] == 1? 'First Semester': 'Second Semester';
                            $phpexcel = new excel();
                         return   $phpexcel->getCourseList(
                                $excelArray,
                                $prog->name,
                                $ss,
                                $course->name,
                                $postdata['course_id'],
                                $course->title,
                                $decoded->data->id,
                               $l

                            );
   


} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
