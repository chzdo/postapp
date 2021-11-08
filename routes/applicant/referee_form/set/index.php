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

if (!isset($postdata['relationship_duration'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['intellect_capacity'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['persistent_capacity'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['imagine_ability'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['scholar_product'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['previous_work'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['previous_work'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['oral'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['overall'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['personality'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['oral'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['email'])) {
    echo sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
if (!isset($postdata['accept_student'])) {
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
    $payment = new Payment($db_portal);
  
  


$app->portal = new Query($db_portal);


$r = $app->getRefereeForm($postdata);

if($r['code']==0){
    echo sendJson($r);
    return;
}

 if(!$session->check($r['payload']['session'])){
    echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
    return;
 }
 


 
$postdata['m_id'] = $r['payload']['id'];
 $resp = $app->setRefereeForm($postdata);
 $n = new N($client,$db);
   $info = array(
       "name"=> $r['payload']['surname'].', '.$r['payload']['firstname'].' '.$r['payload']['othername'],
       "r_name" => $r['payload']['name'],
       "email"=> $r['payload']['app_email'],
       "cron"=> false,
       "type"=>EMAIL_CODE_APP_REFEREE_SUBMIT_STUDENT
   );

   $n->notify(EMAIL_CODE_APP_REFEREE_SUBMIT_STUDENT,$info);


   $n = new N($client,$db);
   $info = array(
       "name"=>  $r['payload']['name'],
       "appd_id" => $r['payload']['appd_id'],
       "email"=> $r['payload']['email'],
       "cron"=> false,
       "type"=>EMAIL_CODE_APP_REFEREE_SUBMIT_REF
   );
   
   $n->notify(EMAIL_CODE_APP_REFEREE_SUBMIT_REF,$info);
    echo sendJson($resp);

return;



} catch (Exception $e) {

    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
    return;
}
