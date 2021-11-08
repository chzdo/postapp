<?php

include_once '../../../../vendor/autoload.php';
include_once '../../../../models/logs.php';
include_once '../../../../config/db.php';
include_once '../../../../models/faculty.php';
include_once '../../../../models/programme.php';
include_once '../../../../models/phpxcel.php';
include_once '../../../../models/session.php';
include_once '../../../../models/dept.php';
// generate json web token
include_once '../../../../config/core.php';
header("Access-Control-Allow-Origin: $aud");
header('Access-Control-Allow-Methods: GET, OPTIONS');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

//libxml_use_internal_errors(true);

use \Firebase\JWT\JWT;

//Authen
$headers =  apache_request_headers();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : '';



if (!$token) {

    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
   return;
}

try {
    $decoded = JWT::decode($token, $key, array('HS256'));
    if($decoded->data->fmis != 1 && $decoded->data->spgs != 1 ){
        echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
        return;
    }
}catch(Exception $e){
    echo sendJson(array('code' => 2, 'message' => RESPONSE['auth'], 'payload' => null));
    return;
}


// get posted data
$_GET = json_decode(base64_decode($_GET['0']),true);

if(!isset($_GET['session'])){
    echo  sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
$database = new Db();
$db = $database->getConnection();

$session = htmlspecialchars(strip_tags($_GET['session']));

$s = new Session($db);

if(!$s->checkCurrentAdmission($session)){
    echo  sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}




try {
   
   



    $response = $client->request('GET', API_COMPLETE, [
        'query' => ['session' => htmlspecialchars(strip_tags($session))]
    ]);

    $data= $response->getBody()->getContents();
    $excelArray[][] = array();
    $header = HEAD;
    $j = 0;
    foreach ($header as $head) {
        $excelArray[0][$j++] = $head;
    }

    $users = json_decode($data, true);
    if ($users['code'] == 1) {
      
        $f = new Faculty($db);
        $d = new Dept($db);
        $p = new Programme($db);
        $f->allActive();
        $d->allActive();
        $p->allActive();
$i= 0;
        $recievers = array();
        foreach ($users['payload'] as $f_id =>$faculty) {
            $facname = search($f_id, $f->all);
 
             
            if ($facname != null) {
       
                foreach ($faculty as $d_id => $dept) {
                    $deptname = search($d_id, $d->all);
                
                    if ($deptname != null) {
                     
                        foreach ($dept as $option => $app) {
                            $optname = search($option, $p->all);
                         
                            foreach ($app as $id => $details) {
                              ++$i;
                                $excelArray[$i][0] = $i;
                                $excelArray[$i][1] = $id;
                                $excelArray[$i][2] =  $details['surname'] . ' ' . $details['firstname'] . ' ' . $details['othername'];
                                $excelArray[$i][3] =  $details['user_name'];
                                $excelArray[$i][4] =  $facname;
                                $excelArray[$i][5] =  $deptname;
                                $excelArray[$i][6] = "0";
                     
                            }
                        }
                    }
                }
            }
        }

  $phpexcel = new excel();
 (new Logs($db))->eventLog($decoded->data->id,EVENTS['dwlADM'].' for '.$s->name);
  return $phpexcel->getApplicationList($excelArray,$s->name);
     
    } else {
        echo sendJson(array('code' => 0, 'message' => $users['message'], 'payload' => null));
    return;
    }
} catch (Exception $e) { // Note: safer fully qualified exception 
    //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
}


function search($id, $array)
{
  
    foreach ($array as $key => $val) {
      
        if ($val['id'] == $id) {
            return isset($val['name'])? $val['name']: $val['programme'];
        }
    }
    return null;
}
