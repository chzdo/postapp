<?php


error_reporting(E_ALL ^ E_DEPRECATED);

use Carbon\Carbon;
// show error reporting

 
// set your default time-zone
date_default_timezone_set('Africa/Lagos');
 
// variables used for jwt
$key = "FULAFIA_PG_PORTAL";
$app_key = "FULAFIA_PG_ADMISSION_PORTAL";
$result_key = "RESULT_FULAFIA_PG_PORTAL";
$iss = "https://spgs.fulafia.edu.ng/api/";
$app_iss = "https://spgs.fulafia.edu.ng/api/";
$aud = "*";
$app_aud = "*";
$iat = Carbon::now()->timestamp;
$app_iat = Carbon::now()->timestamp;
$nbf = Carbon::now()->timestamp;
$app_nbf = Carbon::now()->timestamp;
$exp = Carbon::now()->addHours(2)->timestamp;
$app_exp = Carbon::now()->addHours(6)->timestamp;
$res_exp = Carbon::now()->addHours(1)->timestamp;

use GuzzleHttp\Client;

 $client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://spgs.fulafia.edu.ng/applications/api/',
    // You can set any number of default request options.
    'timeout'  => 10000,
]);

const classification= array(array("id"=>1 ,"classification"=>"Core"),array("id"=>2 ,"classification"=>"Elective"));
define("ACCOUNT",array("STUDENT"=>3,"ADMIN"=>1, "PGADMIN"=>2, "LECTURER"=>4));


define("DATABASE",
  array(
    "portal"=> "journals_pgportal",
    "staff"=>"",
    "app" =>"journals_spgs"
  )
  );



define('TABLES' , array(
  'roles'=> 'roles',
  'users' => 'users',
  'staff' => 'staffs',
  'staff_dept' => 'coursestudy',
  'staff_fac' => 'facultys',
  'staff_pos' => 'positions',
  'session' => 'session',
  'session_admission' => 'admission_session',
  'session_current' => 'current_session',
  'faculty'=> 'faculty',
  'dept'=> 'department',
  'dept_prog'=> 'dept_prog',
  'options'=> 'options',
  'prog'=> 'programme',
  'admission'=>'admission_list',
  'students'=> 'student',
  'students_info'=> 'user_info',
  'student_clear'=> 'student_clearance',
  'clear_type'=>'clearance_type',
  'pay_type'=>'payment_type',
  'pay'=>'payments',
  'country'=>'country',
  'state'=>'state',
  'lga'=>'lga',
  'token'=>'tokens',
  'event_logs'=>'event_logs',
  'sign_logs'=>'sign_logs',
  'result_logs'=>'result_logs',
  'courses'=>'courses',
  'courses_load'=>'course_loads',
  'dept_courses'=>'course_dept',
  'dept_load'=>'dept_load',
  'course_assign'=>'assign_course',
  'lecturers'=>'lecturers_id',
  'register_course'=>'register_course',
  'result_course'=>'result_course',
  'approve_result'=>'approve_result',
  'approve_result_log'=>'approve_result_logs',
  'assignment'=>'assignment',
  'assignment_submit'=>'assignment_submit',
  'material'=>'materials',
  'hash'=>'hash',

  'app_login'=> 'users',
  'app_codes'=> 'codes',
  'app_names'=> 'user_name',
  'app_application'=> 'application',
  'app_payment'=> 'payments',
  'app_pay_type'=> 'payment_type',
  'app_course'=> 'course_info',
  'app_alevel'=> 'alevel_info',
  'app_olevel'=> 'olevel_info',
  'app_olevel_result'=> 'olevel_result',
  'app_degree'=> 'degree_info',
  'app_emp'=> 'emp_info',
  'app_other'=> 'other_info',
  'app_prof'=> 'prof_info',
  'app_pub'=> 'pub_info',
  'app_referee'=> 'referee_info',
  'app_referee_form'=> 'referee_form',
  'app_thesis'=> 'thesis_info',
  'app_bio'=> 'user_info',
) );


define('APP_CODES_CREATE_ACCOUNT',1);

define('APP_CODES_RECOVER_PASSWORD',2);

define('APP_PAYMENT',1);
define('APP_PAYMENT_EXAM',2);
define('APP_EDIT_PAYMENT',3);
define('EXAM_PAYMENT_ACTIVE',1);



define('EMAIL',array(
  'HOST'=>'smtp.mailspons.com',
  'USERNAME' => 'cefc0a15de9544debb78',
  'PASSWORD' => '3fce2a93b4a440b899c41b9228dde891',
  'PORT' => '587',
  'STMP' => 'STARTTLS',
   
));

define('APP_STATE',array('submitted','completed',
  'incomplete',
  'accepted',
  'rejected',
   
));

define('PASSPORT_URL_STUDENT','http://localhost/portal/portal/api/routes/applicant/');

define('SIGNATURE_URL','https://fmis.fulafia.edu.ng/signatures/');
define('PASSPORT_URL_STAFF','https://fulafia.edu.ng/staffprofilepic/');
define('SCHOOL_FEE', 3);
define('ACCEPTANCE_FEE', 1);
define('GST_FEE', 4);
define('CLEARANCE', 2
);
$approve_id = array("default"=> 0 , "lecturer"=> 1, "HOD"=> 2, "DEAN"=> 3 , "PG"=> 4 , "SENATE"=> 5);

define('EVENTS',array(
  'dwlADM'=>' downloaded admission list',
  'uplADM'=>' Uploaded admission list',
  'updADMState'=>' Updated Admission State ',

  'dwlAPPPdf'=>'Downloaded Application Summary ',
  'pg_clear_student'=>'Cleared Student',
  'genRRR'=>'Generated RRR ',
  'verifyRRR'=>'Verification of RRR ',
  'clearRRR'=>'Payment Clearance',
  'added_f'=>'Added Faculty',
  'status_f'=>'Change Status of Faculty',
  'update_f'=>'Updated Faculty',
  'remove_f'=>'Removed Faculty',
   'added_d'=> 'Added Department',
   'update_d'=>'Updated Department',
   'status_d'=>'Change Status of Department',
   'remove_d'=>'Removed Department',
   'added_o'=>'Added Options',
   'update_o'=>'Updated Options',
   'remove_o'=>'Removed Options',
   'added_s'=> 'Added Session',
   'update_s'=>'Updated Session',
   'status_s'=>'Change Status of Session',
   'remove_s'=>'Removed Session',
   'setcurrent_s'=>'Set Current Session',
   'setadmission_s'=>'Set Admission Session',
   'added_c'=> 'Added Course',
   'status_c'=> 'Changed Status of  Course',
   'added_cl'=> 'Added a Load to course',
   'status_cl'=> 'Changed Status of  Course with load',
   'close_s'=> 'Closed Session',
   'setcourses_d'=>"Set Courses for Department",
   'setmax_c'=>"Set Maximuim course load ",
   'setassign_d'=>"Set Course Allocation for",
   'register_courses'=>"Registered course for ",
   'approve_courses'=>"Approve courses for ",
   'upload_result'=>"upload result for  ",
));




define('RESPONSE',array(
  'invalid'=>'Invalid Request Parameters',
  'auth' => 'no authentication ',
  'PASSWORD' => '3fce2a93b4a440b899c41b9228dde891',
  'PORT' => '2525',
  'STMP' => 'tls',
   
));
define('CREATOR', 'FULAFIA MIS');
//ADMISSION KEY WORDS
define('HEAD',array('S/N','REGISTRATION NUMBER','NAME','EMAIL','FACULTY','DEPARTMENT','STATUS'));
define('COMPANY', 'FEDERAL UNIVERSITY OF LAFIA');
define('CATEGORY_ADMISSION', 'ADMISSION LIST');
define('CATEGORY_RESULT', 'COURSE LIST');
define('ADMISSION_PASSWORD', 'SPGS##?e');
define('RESULT_PASSWORD', 'SPGS##?e');
$url = 'https://spgs.fulafia.edu.ng/';
$url_local = "http://localhost/portal/portal/";
$url2 = 'https://spgs.fulafia.edu.ng/spgs/new';
$url_local2 = "http://localhost:3000/spgs/new";
$app_url = 'https://spgs.fulafia.edu.ng/applications';
$app_url_local2 = "http://localhost:3000/application";
define('URL', $url_local);
define('APP_URL', $app_url_local2);
define('URL2', $url_local2);
define('URL_FOR_REFEREE_CREATE', APP_URL."/referee_form");

define('EMAIL_CODE_ACCEPT',0);
define('EMAIL_CODE_LOGIN',1);
define('EMAIL_CODE_RESET',2);
define('EMAIL_CODE_PAY',3);
define('EMAIL_CODE_MATRIC',4);
define('EMAIL_CODE_APP_CREATE',5);
define('EMAIL_CODE_APP_PASSWORD',6);
define('EMAIL_CODE_APP_CREATE_COURSE',7);

define('EMAIL_CODE_APP_REFEREE_CREATE',8);

define('EMAIL_CODE_APP_SUBMIT',9);
define('EMAIL_CODE_APP_REFEREE_SUBMIT_STUDENT',10);
define('EMAIL_CODE_APP_REFEREE_SUBMIT_REF',11);

define('PG','POSTGRADAUTE SCHOOL');
define('NOREPLY','no-reply@spgs.fulafia.edu.ng');
 define('REDIRECT', '/spgs/new/dashboard/clearance/');
define('SCHOOLNAME', 'FEDERAL UNIVERSITY OF LAFIA');
define('SCHOOL', 'POST GRADUATE SCHOOL');
define('API_ALL', 'https://spgs.fulafia.edu.ng/applications/api/application/all/');
define('API_COMPLETE', 'https://spgs.fulafia.edu.ng/applications/api/application/all/complete/');
define('API_APPLICANT', 'https://spgs.fulafia.edu.ng/applications/api/application/applicant/');
define('API_UPDATE', 'https://spgs.fulafia.edu.ng/applications/api/application/all/update/');
define('IMG_PATH',"logo.png");
define('IMG_PATH2',"../../../../../../../public/logo.png");

define('ADMISSION_LETTER_PATH','../../../../admission_letter/');

define("MERCHANTID", "3048228467");
define("SERVICETYPEID", "5122701491");
define("APIKEY","437186");
define("GATEWAYURL", "https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit");
define("GATEWAYRRRPAYMENTURL", "https://login.remita.net/remita/ecomm/finalize.reg");
define("CHECKSTATUSURL", "https://login.remita.net/remita/ecomm");


define("APP_MERCHANTID", "3048228467");
define("APP_SERVICETYPEID", "5122701491");
define("APP_APIKEY","437186");
define("APP_GATEWAYURL", "https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit");
define("APP_GATEWAYRRRPAYMENTURL", "https://login.remita.net/remita/ecomm/finalize.reg");
define("APP_CHECKSTATUSURL", "https://login.remita.net/remita/ecomm");




define("PATH", 'http://spgs.fulafia.edu.ng/spgs/new/dashboard/payments/confirm');
define("APP_PATH", 'http://localhost:3000/session/payments/confirm');









function getParam($id, $obj, $filterKey, $returnKey)
    {

          $v = array_filter($obj, function ($var) use ($id, $filterKey) {
            return ($var[$filterKey] == $id);
        });
    
     
        foreach ($v as $key) {
    
            if (isset($key[$returnKey])) {
               return $key[$returnKey];
                break;
            }
        }
    
        return null;
    }

    function filter($choices,$chosen){
      foreach($choices as $key=>$value){
        foreach($chosen as $k=>$v){
            if($value['id']==$v['id']){
                unset($choices[$key]);
            }
        }
    }
    return array_values($choices);
    }

    
    set_exception_handler(function(){
     // http_response_code(500);
     // echo 'Internal Server Error';
    });

   function sendJson($value){
    //return(json_encode($value));
     return base64_encode(json_encode($value));
   }
?>