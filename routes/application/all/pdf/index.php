<?php 

include_once '../../../../vendor/autoload.php';

include_once '../../../../config/db.php';
include_once '../../../../models/faculty.php';
include_once '../../../../models/logs.php';
include_once '../../../../models/dept.php';
include_once '../../../../models/programme.php';
include_once '../../../../models/session.php';
// generate json web token
include_once '../../../../config/core.php';
header("Access-Control-Allow-Origin: $aud");
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');



use \Firebase\JWT\JWT;
/**
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
     
    sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
}
  $session = htmlspecialchars(strip_tags($_GET['session']));

**/


try{
  $database = new Db();
$db = $database->getConnection();
  $session = new Session($db);

  if (!$session->check(3)) {
      echo sendJson(array('code' => 0, 'message' => "Session does not exist", 'payload' => null));
      return;
  }

     $response = $client->request('GET', URL.'api/routes/application/all/pdf/pdf.php', [
        'query' => ['session' => htmlspecialchars(strip_tags($_GET['session']))]
    ]);
    $page = $response->getBody()->getContents();

   
$data = [
    'landscape' => true,
    'format'=> 'A3',
  'html' => $page,
  'apiKey' => '219352871426a7e52f64403ae82fa317ce7904c08d5f7af9ae9f163e7eb31660',
];


$dataString = $data;
var_dump($dataString);
$ch = curl_init('https://api.html2pdf.app/v1/generate');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
]);

$response = curl_exec($ch);
$err = curl_error($ch);
var_dump($response); return;
curl_close($ch);
$database = new Db();
$db = $database->getConnection();
if ($err) {
  header('Content-Type: application/json');
  echo sendJson(array('code'=>0, 'message'=>$err, 'payload'=>null));
  return;
} else {
  header('Content-Type: application/pdf');
  header('Content-Disposition: inline; filename="your-file-name.pdf"');
  header('Content-Transfer-Encoding: binary');
  header('Accept-Ranges: bytes');
 // (new Logs($db))->eventLog($decoded->data->id,EVENTS['dwlAPPPdf'].' for '.$session->name);
  echo $response;
}
 
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

