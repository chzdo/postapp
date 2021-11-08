<?php 

include_once '../../../../../vendor/autoload.php';
include_once '../../../../../config/core.php';
include_once '../../../../../config/db.php';
include_once '../../../../../models/email.php';
header('Access-Control-Allow-Origin: '.$aud);
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('X-Actual-Content-Length' , '1000');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');


// generate json web token



use \Firebase\JWT\JWT;
$headers =  apache_request_headers();
 
$token =isset($headers['Authorization']) ? $headers['Authorization'] : '';

if(!$token){
   
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
   return;
 }

 try {
    $decoded = JWT::decode($token, $key, array('HS256'));

   if(!($decoded->data->role == 'Admin' || $decoded->data->role == 'PGSchool' )){
       echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
     return;
    }
}catch(Exception $e){
    echo sendJson(array('code'=>2,'message'=>RESPONSE['auth'], 'payload'=>null));
    return;
}
// get posted data
$_GET = json_decode(base64_decode($_GET['0']),true);
if(!isset($_GET['session'])){
     
    echo sendJson(array('code'=>0,'message'=>RESPONSE['invalid'], 'payload'=>null));
    return;
}
$session = htmlspecialchars(strip_tags($_GET['session']));




try{

 $response = $client->request('GET', 'application/all/complete/', [
       'query' => ['session' => htmlspecialchars(strip_tags($_GET['session']))]
    ]);
    $data = $response->getBody()->getContents();
   $users = json_decode($data,true);
  if($users['code']==1){
      $recievers = array();
   foreach($users['payload'] as $dept){
    foreach($dept as $opt){
       foreach($opt as $key=>$values){
           foreach($values as $id=>$details){
          
          $query =   http_build_query(
                    array(
                       'name' => $details['surname'] .' ' . $details['firstname'],
                       'appd_id' => $id,
                    )
                    );
                    $email = $details['user_name'];
                    $html = file_get_contents(URL.'api/emailview/invite.php?'.$query, false);
                       
                   $recievers[] = array('email'=>$email, 'html'=>$html);
                 
                   
                }
       }
    }
   }

   $email = new email();
  
     $email->addReciever($recievers);
     $email->setHeading('INVITATION FOR ENTRANCE TEST','noreply@pgschool.fulafia.edu.ng','FULAFIA PG SCHOOL');
     $result =  $email->send();
     echo sendJson(array('code'=>1, 'message'=>'email sent', 'payload'=>$result));
}else{
    echo sendJson(array('code'=>0, 'message'=>$users['message'], 'payload'=>null));
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
