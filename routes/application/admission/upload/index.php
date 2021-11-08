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


// get posted data
if (!(isset($_POST['session']) && isset($_FILES['file']))) {

    echo  sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}

$file = $_FILES['file'];

if ($file['type']  !==  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
    echo  sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}
$session = htmlspecialchars(strip_tags($_POST['session']));

$database = new Db();
$db = $database->getConnection();
$f = (new Faculty($db));
$f->allActive();
$d = (new Dept($db));
$d->allActive();
$p = (new Programme($db));
$p->allActive();
$s = new Session($db);
$app = new Application($db);





if (!$s->checkCurrentAdmission($session)) {
    echo  sendJson(array('code' => 0, 'message' => RESPONSE['invalid'], 'payload' => null));
    return;
}






try {


   
    $phpexcel = new excel();

    $temp = 'temp' . rand(2000, 3000); //$decoded->data->id;
    $fileExp = explode('.', $file['name']);

    $filename = $temp . '.' . $fileExp[1];
    if (move_uploaded_file($file['tmp_name'], $filename)) {
        $a =  $phpexcel->readAdmissionList($filename, $s->name);
        if ($a->code == 0) {
            unlink($filename);
            echo sendJson(array('code' => $a->code, 'message' => $a->message, 'payload' => null));
            return;
        }
        unlink($filename);
        $Recievers =  $app->setBulkAdmission($a->payload);
        if ($Recievers->code == 0) {
            echo sendJson(array('code' => $Recievers->code, 'message' => $Recievers->message, 'payload' => null));
            return;
        }

        $response = $app->setAdmission($Recievers->payload, $session);

        if (!count($response['payload']['filteredApp']) > 0) {
            echo sendJson(array('code' => 0, 'message' => 'No Accepted Applicant', 'payload' => array('error' => $response['payload']['error'], 'email' => [])));
            return;
        }
        $resp = $client->request('POST', API_UPDATE, [
            'form_params' => [
                'setQuery' => $response['payload']['Api']['setQuery'],
                'whereQuery' => $response['payload']['Api']['whereQuery'],
                'session' => $session

            ]
        ]);
        $data = $resp->getBody()->getContents();
        $dataJson = json_decode($data);
        if ($dataJson->code == 0) {
            echo sendJson(array('code' => 0, 'message' => $dataJson->message, 'payload' => null));
            return;
        }
        $save = $app->saveAdmissionList($response['payload']['filteredApp'], $session, $f->all, $d->all);


        if (!$save) {
            echo sendJson(array('code' => 0, 'message' => 'Not Saved', 'payload' => array('error' => $response['payload']['error'], 'email' => [])));
            return;
        }
        $r;

        $emailReciever = $response['payload']['filteredApp'];
    /**    foreach ($emailReciever as $email) {


            if (!isset($email['email'])) continue;
            $q =   http_build_query([
                'name' => $email['name'],
                'faculty' => $email['faculty'],
                'dept' => $email['dept'],
                'session' => $s->name,
                'prog' => getParam(explode('/', $email['appd_id'])[1], $p->all, 'short_name', 'programme')
            ]);
            $page = 'reject';
            $attach = null;
            if ($email['status'] == 1) {
                $page =  'accept';

                $pdf = new PDFEdit();
                $file = $pdf->getAdmissionLetter($email);
                $attach = array('file' => $file, 'filename' => 'Admission Letter');
            }
            $html =  file_get_contents(URL . "api/emailview/$page.php?" . $q, false);
            $r[] = array(
                'email' => $email['email'],
                'html' => $html,
                'attach' => $attach
            );
        }
        $email = new email();
        $email->setHeading('Application Status', 'noreply@pgschool.fulafia.edu.ng', 'Admission Office');
        $email->addReciever($r);
        $resp = $email->send();
        if (!(count($resp['sent']) > 0)) {
            echo sendJson(array('code' => 0, 'message' => 'Saved but could not send Mail', 'payload' => array('error' => $response['payload']['error'], 'email' => $resp)));
            return;
        }
        **/
        (new Logs($db))->eventLog($decoded->data->id,EVENTS['uplADM'].' for '.$s->name);
        echo sendJson(array('code' => 1, 'message' => 'File Uploaded', 'payload' => array('error' => $response['payload']['error'], 'email' => $resp)));
    }
} catch (Exception $e) { // Note: safer fully qualified exception 
    //       name used for catch
    // Process the exception, log, print etc.
    echo sendJson(array('code' => 0, 'message' => $e->getMessage(), 'payload' => null));
}
