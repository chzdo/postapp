<?php

include_once '../../../../vendor/autoload.php';

include_once '../../../../config/db.php';
include_once '../../../../models/logs.php';
include_once '../../../../models/application.php';
include_once '../../../../models/faculty.php';
include_once '../../../../models/dept.php';
include_once '../../../../models/programme.php';
include_once '../../../../models/pdfEdit.php';
include_once '../../../../models/phpxcel.php';
include_once '../../../../models/email.php';
include_once '../../../../models/session.php';
// generate json web token
include_once '../../../../config/core.php';
header("Access-Control-Allow-Origin: $aud");
header('Access-Control-Allow-Methods: POST, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

use Dompdf\Frame\Factory;
use \Firebase\JWT\JWT;


$headers =  apache_request_headers();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : '';
if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}
try {
    $decoded = JWT::decode($token, $key, array('HS256'));
    if($decoded->data->fmis != 1 && $decoded->data->spgs != 1 ){
        echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'] . $decoded->data->role, 'payload' => null));
      return;
    }
} catch (Exception $e) {
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
  return;
}

$_GET = json_decode(base64_decode($_GET['0']),true);
// get posted data
if (!(isset($_GET['session']) )) {

    echo  sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

$session = htmlspecialchars(strip_tags($_GET['session']));

$database = new Db();
$db = $database->getConnection();

$app = new Application($db);


try {
    $s = new Session($db);
 
    
    
    
    
    
    if (!$s->check($session)) {
        echo  sendJson(array('code' => 0, 'message' => "Session not found", 'payload' => null));
        return;
    }
    

   
   ob_end_clean();
        $save = $app->getAdmissionList($session);
        if($save){
            echo sendJson(array('code' => 1, 'message' => 'found', 'payload' => $app->all));
            return;

        }
        echo sendJson(array('code' => 0, 'message' => 'not found', 'payload' => null));
return;

} catch (Exception $e) { // Note: safer fully qualified exception 
    //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
}
