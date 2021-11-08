<?php 

include_once '../../../vendor/autoload.php';
include_once '../../../models/applicant.php';
include_once '../../../config/db.php';
include_once '../../../config/query.php';
include_once '../../../models/faculty.php';
include_once '../../../models/logs.php';
include_once '../../../models/dept.php';
include_once '../../../models/programme.php';
include_once '../../../models/session.php';
include_once '../../../models/options.php';
// generate json web token
include_once '../../../config/core.php';
header("Access-Control-Allow-Origin: $aud");
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');



use \Firebase\JWT\JWT;


$headers =  apache_request_headers();
 
$token =isset($headers['Authorization']) ? $headers['Authorization'] : '';

if(!$token){
   
  echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
return;
}

try {
  $decoded = JWT::decode($token, $key, array('HS256'));

  if($decoded->data->fmis != 1 && $decoded->data->spgs != 1 ){
      echo sendJson(array('code'=>0,'message'=>RESPONSE['auth'], 'payload'=>null));
   return;
    }
}catch(Exception $e){
  echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
  return;
}
$_GET = json_decode(base64_decode($_GET['0']),true);

if(!isset($_GET['session'])){
     
 echo   sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}

  $session = htmlspecialchars(strip_tags($_GET['session']));
 


try{
  $database = new Db();
  $db = $database->getConnection();
  $app = new Applicant(new Query($database->getAppConnection()));
  $s = new Session($db);
  $app->portal = new Query($db);
  if(!$s->check($session)){
    echo sendJson(array("code"=>0,"message"=>"Session does not exist"));
    return;
  }



 $a = $app->getRefereeSummary($session);
//var_dump($a);
 // var_dump($val);
    echo sendJson(array('code'=>1, 'message'=>"found", 'payload'=>$a));
} catch (Exception $e) { // Note: safer fully qualified exception 
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code'=>0, 'message'=>$e->getMessage(), 'payload'=>null));
}
//var_dump($payload);



 








//============================================================+
// END OF FILE
//============================================================+
    ?>

