<?php
include_once '../../../../vendor/autoload.php';
include_once '../../../../config/core.php';
include_once '../../../../config/db.php';
include_once '../../../../config/query.php';
include_once '../../../../models/applicant.php';
include_once '../../../../models/session.php';
include_once '../../../../models/payment.php';
include_once '../../../../models/n.php';
// generate json web token


header('Access-Control-Allow-Origin: ' . $aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$postdata =   json_decode(base64_decode(file_get_contents('php://input')), true);







if (!isset($postdata['appd_id'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['email'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

try {
   
    $database = new Db();
    $db = $database->getAppConnection();
    $db_portal = $database->getConnection();
    $query = new Query($db);
    $app = new Applicant($query);
    $session = new Session($db_portal);
 

$app->portal = new Query($db_portal);

 
    
$t = $app->getRefereeForm($postdata);

 if($t['code']==0){
    echo sendJson($t);
    return;
 }
 
  echo sendJson($t);
  return;
} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
