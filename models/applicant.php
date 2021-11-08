<?php

use Carbon\Carbon;


class Applicant
{
    public $appd_id;
    public  $id;
    public $status;
    public $all;
    private $table = 'session';
    public $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    function createAccount($input)
    {

        $slice =      $this->sort($input, ['email', 'phone']);


        $response =  $this->db->pull($slice, "Select * from " . TABLES['app_login'] . " where email = ? or phone = ? ");

        if ($response && $this->db->result != NULL) {
            return array("code" => 0, "message" => "Email or Phone Number already exist");
        }

        $hash = password_hash($input['password'], PASSWORD_DEFAULT);
        $input['password'] = $hash;
        $response =   $this->db->insert($input, TABLES['app_login']);

        if ($response) {
            $code = rand(1000, 9999);
            $this->db->insert(array("email" => $input['email'], "code" =>  password_hash($code, PASSWORD_DEFAULT), "type" => APP_CODES_CREATE_ACCOUNT, "exp_date" => Carbon::now()->addHours(2)), TABLES['app_codes']);
            return array("code" => 1, "message" => "User Created", "payload" => $code);
        }
        return array("code" => 0, "message" => "Database Error ");
    }

    function verifyAccount($input)
    {
        $slice =      array_filter(
            $input,
            fn ($key) => in_array($key, ['email']),
            ARRAY_FILTER_USE_KEY
        );

        $response =  $this->db->pullAll($slice, "Select ac.exp_date, ac.code from " . TABLES['app_codes'] . " as ac join " . TABLES['app_login'] . " as al on al.email = ac.email and al.status = 0 where ac.email = ? and   ac.type =  " . APP_CODES_CREATE_ACCOUNT);

        if (!$response && $this->db->result == NULL) {
            return array("code" => 0, "message" => "This email has either been verified before or does not exist");
        }
        $result = $this->db->result;
        $found = false;
        $code = NULL;
        foreach ($result as $res) {
            if (password_verify($input['code'], $res['code'])) {
                $found = true;
                $code = $res;
                break;
            }
        }

        if (!$found) {
            return array("code" => 0, "message" => "Invalid Code");
        }

        if (Carbon::createFromDate($code['exp_date'])->lt(Carbon::now())) {
            return array("code" => 0, "message" => "Code has expired");
        }
        $resp =   $this->db->update(array("status" => 1), array("email" => $slice['email']), [], TABLES['app_login']);

        if ($resp) {
            return array("code" => 1, "message" => "Account Verified", "payload" => null);
        }


        return array("code" => 0, "message" => "Database Error ");
    }
    function changePassword($input)
    {
        $slice =      array_filter(
            $input,
            fn ($key) => in_array($key, ['email']),
            ARRAY_FILTER_USE_KEY
        );

        $response =  $this->db->pullAll($slice, "Select ac.exp_date, ac.code from " . TABLES['app_codes'] . " as ac join " . TABLES['app_login'] . " as al on al.email = ac.email and al.status = 1 where ac.email = ? and   ac.type =  " . APP_CODES_RECOVER_PASSWORD);

        if (!$response && $this->db->result == NULL) {
            return array("code" => 0, "message" => "This email has either not been verified before or does not exist");
        }
        $result = $this->db->result;
        $found = false;
        $code = NULL;
        foreach ($result as $res) {
            if (password_verify($input['code'], $res['code'])) {
                $found = true;
                $code = $res;
                break;
            }
        }

        if (!$found) {
            return array("code" => 0, "message" => "Invalid Code");
        }

        if (Carbon::createFromDate($code['exp_date'])->lt(Carbon::now())) {
            return array("code" => 0, "message" => "Code has expired");
        }
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        $resp =   $this->db->update(array("password" => $password), array("email" => $slice['email'], 'status' => 1), ["and"], TABLES['app_login']);

        if ($resp) {
            return array("code" => 1, "message" => "Password Changed", "payload" => null);
        }


        return array("code" => 0, "message" => "Database Error ");
    }
    function resendOTP($input)
    {

        $code = rand(1000, 9999);
        $resp =  $this->db->insert(array("email" => $input['email'], "code" =>  password_hash($code, PASSWORD_DEFAULT), "type" => $input['type'], "exp_date" => Carbon::now()->addHours(2)), TABLES['app_codes']);

        if ($resp) {
            return array("code" => 1, "message" => "Code sent to your mail ", "payload" => $code);
        }


        return array("code" => 0, "message" => "Database Error ");
    }

    function verifyEmail($input)
    {

        return   $this->db->pull(array("email" => $input), "select * from " . TABLES['app_login'] . "  where email = ? ");
    }

    function isAppUser($input)
    {
        $slice = $this->sort($input, ['session', 'session', 'email']);

        $res =  $this->db->pull([$slice['session'], $slice['session'], $slice['email']], "select aa.*, ap.status as pay_status from " . TABLES['app_login'] . " as al
        
        join " . TABLES['app_application'] . " as aa on aa.email = al.email and aa.session = ?
        left join " . TABLES['app_payment'] . " as ap on ap.email = aa.email and  ap.session = ? and ap.type = " . APP_PAYMENT . "
        
         where al.email = ? ");

        if ($res) {
            $this->app = $this->db->result;
            return true;
        }
        return false;
    }
    function getFee($input)
    {
         extract(TABLES);
         $this->db->pull(array("id" => $input), "select * from $app_pay_type  where id = ? ");
         return $this->db->result;
    }
    function getFaculty()
    {
  extract(TABLES);
      $faculty =  $this->portal->pullAll([], "select id,name from $faculty where status = 1 ");
    
      if($faculty){
             $list = $this->portal->result;
    
             foreach($list as $lists){
               $de = [];
             $dep = $this->portal->pullAll(["faculty"=>$lists['id']], "select id,name from $dept where faculty = ? and status = 1 ");
             if($dep){
                 $dep = $this->portal->result;
               
                 foreach($dep as $depts){
                    $opt_r = [];
                    $p = [];
                    $opt = $this->portal->pullAll(["department"=>$depts['id']], "select id,name from $options where department = ?  ");
                    if($opt){
                             $opt_r = $this->portal->result;
                    }

                    $pro = $this->portal->pullAll(["dept_id"=>$depts['id']], "select $prog.short_name,$prog.id from $dept_prog 
                    join $prog on $prog.id = $dept_prog.prog_id
                    
                    where dept_id = ? ");
                    if($pro){
                        $p = $this->portal->result;
                    }
                    $depts['options'] = @$opt_r;
                    $depts['programmes'] = @$p;
                    $de[] = $depts;
                }
                $lists['department'] = @$de;
             }
             $fac[] = $lists;
             }
             $this->faculty = @$fac;
      }
        return $faculty;
    }
    function getCountry()
    {
  extract(TABLES);
      $country =  $this->portal->pullAll([], "select id,country from $country  ");
    
      if($country){
             $list = $this->portal->result;

             foreach($list as $lists){
                $newSt = [];
              
               if($lists['country'] == "Nigeria"){
             $sta = $this->portal->pullAll([], "select sn,state from $state ");
             if($sta){
                 $st = $this->portal->result;
               
                 foreach($st as $sts){
                    $ll = null;
                    $l = $this->portal->pullAll(["state"=>$sts['state']], "select lga from $lga where state = ?  ");
                    if($l){
                             $ll = $this->portal->result;
                    }
                    $sts['lga'] =  @$ll;
                    $newSt[] = $sts;
                }
            }
               }
            $lists['state'] = @$newSt;
            $newC[] = $lists;
             }
             $this->country = $newC;
            }
        return $country;
    }
    function login($input)
    {

        $slice =      array_filter(
            $input,
            fn ($key) => in_array($key, ['email']),
            ARRAY_FILTER_USE_KEY
        );
        $response =  $this->db->pull($slice, "Select al.*,an.firstname, an.othername, an.surname from " . TABLES['app_login'] . " as al left join " . TABLES['app_names'] . " as an on al.email = an.email where al.email = ? ");

        if (!$response && $this->db->result == NULL) {
            return array("code" => 0, "message" => "user not found", 'payload' => array('email' => false));
        }
        $result = $this->db->result;
        if (!password_verify($input['password'], $result['password'])) {
            return array("code" => 0, "message" => "Invalid Password!", "payload" => array('password' => false));
        }

        if ($result['status'] == 0) {
            return array("code" => 0, "message" => "This account has not been verified", 'payload' => array('status' => false));
        }

        $slice =      array_filter(
            $result,
            fn ($key) => in_array($key, ['email', 'phone', 'status', 'firstname', 'othername', 'surname']),
            ARRAY_FILTER_USE_KEY
        );
        return array("code" => 1, "message" => "User Found", 'payload' => $slice);


        return array("code" => 0, "message" => "Database Error ");
    }



    function check_name($input)
    {

        $slice =      array_filter(
            $input,
            fn ($key) => in_array($key, ['email']),
            ARRAY_FILTER_USE_KEY
        );
        $response =  $this->db->pull($slice, "Select * from " . TABLES['app_names'] . " where email = ? ");

        if (!$response && $this->db->result == NULL) {
            return array("code" => 0, "message" => "name not found", 'payload' => ["noname"=>true]);
        }
        $result = $this->db->result;


        $slice =      array_filter(
            $result,
            fn ($key) => in_array($key, ['firstname', 'othername', 'surname']),
            ARRAY_FILTER_USE_KEY
        );

        return array("code" => 1, "message" => "name Found", 'payload' => $slice);


        return array("code" => 0, "message" => "Database Error " , "payload"=>null);
    }

    function set_name($input)
    {

        $slice =      array_filter(
            $input,
            fn ($key) => in_array($key, ['email']),
            ARRAY_FILTER_USE_KEY
        );
        $response =  $this->db->pull($slice, "Select * from " . TABLES['app_names'] . " where email = ? ");

        if ($response && $this->db->result != NULL) {
            return array("code" => 0, "message" => "Name for this user already exist", 'payload' => $this->db->result);
        }
        $slice =      array_filter($input, fn ($key) => in_array($key, ['email', "firstname", "othername", "surname"]), ARRAY_FILTER_USE_KEY);
        $result = $this->db->insert($slice, TABLES['app_names']);

        if ($result) {
            $slice =      array_filter(
                $input,
                fn ($key) => in_array($key, ['firstname', 'othername', 'surname']),
                ARRAY_FILTER_USE_KEY
            );
            return array("code" => 1, "message" => "name Found", 'payload' => $slice);
        }

        return array("code" => 0, "message" => "Database Error ");
    }


    function sort($array, $key)
    {
        $n = [];
        foreach ($key as $k) {
            $n[$k] = $array[$k];
        }
        return $n;
    }

    function get_application($input)
    {

        $slice =      $this->sort($input, ['email', 'session']);



        $response =  $this->db->pull(
            $slice,
            "Select  ap.* , aa.*, ap.status as pay_status
                                        from " . TABLES['app_application'] . " as aa
                                       
                                       left join " . TABLES['app_payment'] . " 
                                       as ap on aa.email = ap.email and aa.session = ap.session and ap.type = " . APP_PAYMENT . "
                                                                           
                                       
                                       where aa.email = ? and aa.session = ? "
        );

        if (!$response && $this->db->result == NULL) {
            return array("code" => 0, "message" => "No Application Found", 'payload' => array("app_count" => 0, "app_summary" => []));
        }

        return array("code" => 1, "message" => " Application Found", 'payload' => array("app_count" => count($this->db->result), "app_summary" => $this->db->result));
        return array("code" => 0, "message" => "Database Error ");
    }



    function start_application($input)
    {

        $slice =      array_filter(
            $input,
            fn ($key) => in_array($key, ['email', 'session']),
            ARRAY_FILTER_USE_KEY
        );

        $response =  $this->db->pull(
            $slice,
            "Select  *
                                            from " . TABLES['app_application'] . "                                   
                                           
                                           where session = ?  and  email = ? "
        );

        if ($response && $this->db->result != NULL) {
            return array("code" => 0, "message" => "You already have an existing application for this session with " . $this->db->result['appd_id'] . " you can choose to edit it", 'payload' => null);
        }


        $resp =    $this->db->insert($slice, TABLES['app_application']);
        if ($resp) {
            return             array("code" => 1, "message" => "Application  Started", 'payload' => null);
        }
        return array("code" => 0, "message" => "Database Error ");
    }


    function checkPayment($input){
        extract(TABLES);
           $new = $this->sort($input,['email','session','type']);
           if($input['type'] == APP_EDIT_PAYMENT){
            $y =  $this->db->pull($new, "select * from $app_payment where email=? and session = ? and type = ?  order by id DESC ");
           
           }else{
             $y =  $this->db->pull($new, "select * from $app_payment where email=? and session = ? and type = ? ");
           }
             if($y){
             $this->payment = $this->db->result;
             $new_hash_string = APP_MERCHANTID . $this->db->result['rrr'] . APP_APIKEY;
             $new_hash = hash('sha512', $new_hash_string);
             $this->payment['hash'] = $new_hash;
             $this->payment['RRR'] = $this->db->result['rrr'];           
             $this->payment['orderId'] = $this->db->result['order_id'];
             $this->payment['ref'] = "FUL/MIS/SPGS/". $this->payment['orderId'] ;


             if($this->db->result['status']==1){
             /**   $query = "select completename as name, signature from $staff  
    
                where bursar = 1 
                
                "  ;
               $j =  $this->query_staff->pull([],$query);
              
               
                $this->payment['bursar'] = $j == false? '': $this->query_staff->result['name'];
                $this->payment['bursar_sign'] = $j == false? '': SIGNATURE_URL.$this->query_staff->result['signature'];
             **/
             }
           }
                 return $y;
    }

    function savePayment($input)
    {



        $response =  $this->db->pull(
            array("session" => $input['session'], "email" => $input['email'], "type" => $input['type']),
            "Select  *
                                                from " . TABLES['app_payment'] . "                                   
                                               
                                               where session = ?  and  email = ? and type = ? "
        );

        if ($response && $this->db->result != NULL && $input['type'] != APP_EDIT_PAYMENT) {
            return array("code" => 0, "message" => "You already have an existing payment for this session ", 'payload' => $this->db->result);
        }


        $resp =    $this->db->insert($input, TABLES['app_payment']);
        if ($resp) {
            return             array("code" => 1, "message" => "Application Payment Generated", 'payload' => null);
        }
        return array("code" => 0, "message" => "Database Error ");
    }


    function verifyRRR($input)
    {

        $slice = $this->sort($input, ['email', 'rrr', 'type']);

        $response =  $this->db->pull(
            $slice,
            "Select  ap.*, apl.purpose
                                     from " . TABLES['app_payment'] . "  as ap 
                                     join " .  TABLES['app_pay_type'] . " as apl on apl.id = ap.type                              
                                    
                                    where email = ?  and  rrr = ? and type = ? "
        );

        if (!$response && $this->db->result == NULL) {
            return false;
        }
        $this->payments = $this->db->result;
        return true;
    }


    function verifyOrderID($input)
    {

        $slice = $this->sort($input, ['email', 'order_id', 'type']);

        $response =  $this->db->pull(
            $slice,
            "Select  ap.*, apl.purpose
                                     from " . TABLES['app_payment'] . "  as ap 
                                     join " .  TABLES['app_pay_type'] . " as apl on apl.id = ap.type                              
                                    
                                    where email = ?  and  order_id = ? and type = ? "
        );

        if (!$response && $this->db->result == NULL) {
            return false;
        }
        $this->payments = $this->db->result;
        return true;
    }


    function updatePayment()
    {



        $response =  $this->db->update(
            array("status" => 1, "date_paid" => Carbon::now()),
            array("rrr" => $this->payments['rrr']),
            [],
            TABLES['app_payment']
        );
      if($response && $this->payments['type'] == APP_EDIT_PAYMENT){
         // echo $this->payments['appd_id'];
        $response =  $this->db->update(
            array("status" => 1, "date_applied" => null),
            array("appd_id" => $this->payments['appd_id']),
            [],
            TABLES['app_application']
        );
      }

        return $response;
    }




    function getCourse()
    {
        extract(TABLES);
        $r =  $this->db->pull(
            array("appd_id" => $this->app['appd_id']),

            "select $app_course.* from $app_application
    
        join  $app_course on $app_course.appd_id = $app_application.appd_id
        
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->course =  array_merge($this->db->result,$this->getCourseInfo($this->db->result));
            return true;
        }
        $this->course = [];
        return $r;
    }


     function getCourseInfo($input){
         extract(TABLES);
       $input = $this->sort($input,['programme_id','options_id']);

      
         $this->portal->pull($input, "select $dept.name as department , $faculty.name as faculty, 
         $prog.short_name as programme, $options.name as options  from  $options 
          join $dept on $dept.id = $options.department 
          join $faculty on $faculty.id = $dept.faculty
          join $prog on $prog.id = ?
          where $options.id = ?
         
         ");
         return $this->portal->result;
     }

    function getRefereeForm($input)
    {
        extract(TABLES);
        $student =   $this->db->pull(
            array("appd_id" => base64_decode($input['appd_id'])),

            "select $app_names.firstname,$app_names.othername, $app_names.surname  , $app_course.programme_id, $app_application.session, $app_application.email as app_email, $app_course.options_id from $app_application
    
        join  $app_names on $app_names.email = $app_application.email
        join  $app_course on $app_course.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if(!$student){
               return array("code"=>0 , "message"=>"Student not found");
        }
       

        $student_info = array_merge($this->db->result,$this->getCourseInfo($this->db->result));
    

     
        $r =  $this->db->pull(
            array("appd_id" => base64_decode($input['appd_id']), "email"=>base64_decode($input['email'])),

            "select * from $app_referee
    
             where $app_referee.appd_id = ? and $app_referee.email = ? "
        );
     
        if ($r) {
            $referee_info =  array_merge($this->db->result,$student_info);

            if($referee_info['status']==1){
                return array("code"=>0 , "message"=>"You have filled the form already. Thank you" , "payload"=> $referee_info);
            }
            return array("code"=>1 , "message"=>"Referee found" , "payload"=> $referee_info);
            
      
        }
        $this->referee_info = [];
          return array("code"=>0 , "message"=>"Referee Information Not Found" );
    }

    function setRefereeForm($input)
    {
        extract(TABLES);
       $r =  $this->db->pull(array($input['m_id']),"select * from $app_referee_form where m_id = ?");

       if($r){
        return array("code"=>0 , "message"=>"You have Filled for this applicant");
       }
       unset($input['email']);
       $input['appd_id'] = base64_decode($input['appd_id']);
        $student =   $this->db->insert($input,$app_referee_form);
           
        if(!$student){
               return array("code"=>0 , "message"=>"Could not Save");
        }
        $r =   $this->db->update(array("status"=>1),array("id"=>$input['m_id']),[],$app_referee);

      
        if (!$r) {
          
                return array("code"=>0 , "message"=>"Could not Update Status" , "payload"=> $referee_info);
        
        }
     
          return array("code"=>1 , "message"=>"Saved" );
    }

    function getAlevel()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_alevel.* from $app_application
    
        join  $app_alevel on $app_alevel.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->alevel =  $this->db->result;
            return true;
        }
        $this->alevel = [];
        return $r;
    }

    function getProf()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_prof.* from $app_application
    
        join  $app_prof on $app_prof.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->prof =  $this->db->result;
            return true;
        }
        $this->prof = [];
        return $r;
    }


    function getPub()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_pub.* from $app_application
    
        join  $app_pub on $app_pub.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->pub =  $this->db->result;
            return true;
        }
        $this->pub = [];
        return $r;
    }

    function getThesis()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_thesis.* from $app_application
    
        join  $app_thesis on $app_thesis.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->thesis =  $this->db->result;
            return true;
        }
        $this->thesis = [];
        return $r;
    }

    function getReferee()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_referee.* from $app_application
    
        join  $app_referee on $app_referee.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->referee =  $this->db->result;
            return true;
        }
        $this->referee = [];
        return $r;
    }


    function getOther()
    {
        extract(TABLES);
        $r =  $this->db->pull(
            array("appd_id" => $this->app['appd_id']),

            "select $app_other.* from $app_application
    
        join  $app_other on $app_other.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->other =  $this->db->result;
            return true;
        }
        $this->other = [];
        return $r;
    }

    function getEmp()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_emp.* from $app_application
    
        join  $app_emp on $app_emp.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->emp =  $this->db->result;
            return true;
        }
        $this->emp = [];
        return $r;
    }

    function getDegree()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_degree.* from $app_application
    
        join  $app_degree on $app_degree.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->degree =  $this->db->result;
            return true;
        }
        $this->degree = [];
        return $r;
    }
    function getOlevel()
    {
        extract(TABLES);
        $r =  $this->db->pullAll(
            array("appd_id" => $this->app['appd_id']),

            "select $app_olevel.* from $app_application
    
        join  $app_olevel on $app_olevel.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );
        $olevel = $this->db->result;
        $finalresult = [];
        if($r){
        foreach ($olevel as $o) {
            $y =  $this->db->pullAll(
                array("appd_id" => $this->app['appd_id'], "exam_number" => $o['exam_number']),

                "select $app_olevel_result.* from $app_olevel
    
        join  $app_olevel_result on $app_olevel_result.exam_number = $app_olevel.exam_number
        where $app_olevel.appd_id = ? and  $app_olevel.exam_number = ? "
            );
            $finalresult[$o['exam_number']] = $o;
            $finalresult[$o['exam_number']]['result'] = $this->db->result;
        }
    }
        $this->olevel =  array_values($finalresult);

        return $r;
    }

    function getBio()
    {
        extract(TABLES);

        $r =  $this->db->pull(
            array("appd_id" => $this->app['appd_id']),
            "select $app_login.email, $app_login.phone, $app_bio.*, $app_names.* from $app_application
         
        join  $app_names on $app_names.email = $app_application.email 
        join  $app_login on $app_login.email = $app_application.email 
        join  $app_bio on $app_bio.appd_id = $app_application.appd_id
        where $app_application.appd_id = ? and $app_application.status > 0 "
        );

        if ($r) {
            $this->db->result['passport_r'] =  $this->db->result['passport'];
            $this->db->result['passport'] = PASSPORT_URL_STUDENT. $this->db->result['passport'];
            $this->bio =  $this->db->result;
            return true;
        }
        $this->bio = [];
        return $r;
    }

    function uploadPassport($file)
    {

        if (!is_array($file)  && $this->getBio()) {
            $this->location = $file;
            return array("code" => 1);
        }
        extract($file);
        extract($this->app);
        $file_t = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($type, $file_t)) {
            return array("code" => 0, "message" => "invalid file type $type. must be any of " . implode(",", $file_t));
        }
        if (($size / 1024) > 50) {
            return array("code" => 0, "message" => "Size too big! Image should not be more than 50kb. Current image size is " . ((int)($size / 1024)) . "kb");
        }

        $name = $serial . '.' . explode(".", $name)[count(explode(".", $name)) - 1];
        $passport = "../../passport";
      

        $location = "$passport/$name";

        if (file_exists($location)) {
            unlink($location);
        }
        if (move_uploaded_file($tmp_name, $location)) {
            $this->location = 'passport/'.$name;
            return array("code" => 1, "message" => "File Uploaded");
        }
        return array("code" => 0, "message" => "Could not upload file");
    }
    function genID($id)
    {
        extract($id);
        $c =  count_chars($this->app['serial']);
        $zeros = "";
        if ($c == 1) {
            $zeros = "000";
        } else if ($c == 2) {
            $zeros = "00";
        } else if ($c == 3) {
            $zeros = "0";
        }
        return $reg = "FUL/$session/$programme/$zeros" . $this->app['serial'];
    }
    function setCourse($input, $Idparams)
    {
        extract($this->app);

        if ($pay_status == 0) {
            return array("code" => 0, "message" => "you have not paid for this application", "payload" => ["redirect" => true]);
        }
        if (!isset($appd_id)) {
            return array("code" => 0, "message" => "you have not paid for this application", "payload" => ["redirect" => true]);
        }



        $id = $this->genID($Idparams);
        $input['appd_id'] = $id;

        unset($input['session']);
        unset($input['email']);
        if ($this->getCourse()) {
            $y =    $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_application']);

            if ($y) {

                $this->db->update($input, array("appd_id" => $appd_id), [], TABLES['app_course']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_bio']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_alevel']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_olevel']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_degree']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_emp']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_other']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_prof']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_pub']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_thesis']);
                $this->db->update(array("appd_id" => $id), array("appd_id" => $appd_id), [], TABLES['app_referee']);
                return true;
            }
            return $y;
        }
        $y =    $this->db->update(array("status" => 1, "appd_id" => $id), array("serial" => $serial), [], TABLES['app_application']);

        if ($y) {

            return  $this->db->insert($input, TABLES['app_course']);
        }
        return $y;
    }

    function verifyAppID($input)
    {

        return $this->db->pull(array("appd_id" => $input), "select * from " . TABLES['app_application'] . " where appd_id = ? ");
    }

    function getSideSummary()
    {
        
        extract(TABLES);

        $list = [
            $app_course, $app_bio,
            $app_alevel, $app_olevel, $app_degree, $app_emp, $app_prof, $app_pub, $app_thesis, $app_referee, $app_other
        ];
        $result = [];
        foreach ($list as $table) {
            $this->db->pull(array($this->app['appd_id']), "select count($table.appd_id) as $table" . "_count from $app_application 
                            left join $table on $table.appd_id = $app_application.appd_id where $app_application.appd_id = ? and $app_application.status > 0");
            $result[key($this->db->result)] = $this->db->result[key($this->db->result)];
        }




        return $result;
    }

    function getSummary($k = 0)
    {
        extract(TABLES);
        $list = [
            $app_application, $app_payment,  $app_course, $app_bio,
            $app_alevel, $app_olevel, $app_degree, $app_emp, $app_prof, $app_pub, $app_thesis, $app_referee, $app_other
        ];
        $result = [];
        foreach ($list as $table) {
            if($table == $app_bio){
            $this->db->pull(
                array("appd_id" => $this->app['appd_id']),
                "select $app_login.email, $app_login.phone, $app_bio.*, $app_names.* from $app_application
             
            join  $app_names on $app_names.email = $app_application.email 
            join  $app_login on $app_login.email = $app_application.email 
            join  $app_bio on $app_bio.appd_id = $app_application.appd_id
            where $app_application.appd_id = ? and $app_application.status > 0 "
            );
           if($this->db->result != null){
            $this->db->result['passport_r'] = $this->db->result['passport'];
            $this->db->result['passport'] = PASSPORT_URL_STUDENT. $this->db->result['passport'];
           }
        }else if ($table == $app_payment){
                $this->db->pullAll(array($this->app['email'], $this->app['session']), "select $table.* , $app_pay_type.purpose  from $app_application as a
                join $table on $table.email = a.email 
                join $app_pay_type on $table.type = $app_pay_type.id
                where a.email = ? and a.session = ?");

            }
            else if ($table == $app_application){
                $this->db->pull(array($this->app['appd_id']), "select $table.*  from $app_application as a
                join $table on $table.appd_id = a.appd_id where a.appd_id = ? and a.status > 0");
                $this->portal->pull(['id'=>$this->db->result['session']],"select session from $session where id = ?");
                $this->db->result['session_id'] =     $this->db->result['session'] ;
                $this->db->result['session'] = $this->portal->result['session'];
            }

            else if ($table == $app_referee){
              
                $this->db->pullAll(array($this->app['appd_id']), "select $app_referee.*, $app_referee_form.*  from $app_application as a
                join $table on $table.appd_id = a.appd_id 
               left join $app_referee_form on $app_referee_form.m_id = $app_referee.id
                where a.appd_id = ? and a.status > 0");
               
               
            }
            else if ($table == $app_course){
                $this->db->pull(array($this->app['appd_id']), "select $table.*  from $app_application as a
                join $table on $table.appd_id = a.appd_id where a.appd_id = ? and a.status > 0");
                $this->portal->pull(['id'=>$this->db->result['dept_id']],"select name from $dept where id = ?");
                $this->db->result['department'] = $this->portal->result['name'];
                $this->portal->pull(['id'=>$this->db->result['faculty_id']],"select name from $faculty where id = ?");
                $this->db->result['faculty'] = $this->portal->result['name'];
                $this->portal->pull(['id'=>$this->db->result['programme_id']],"select programme from $prog where id = ?");
                $this->db->result['programme'] = $this->portal->result['programme'];
                $this->portal->pull(['id'=>$this->db->result['options_id']],"select name from $options where id = ?");
                $this->db->result['options'] = $this->portal->result['name'];
            }
            
            else{
            $this->db->pullAll(array($this->app['appd_id']), "select $table.*  from $app_application as a
                            join $table on $table.appd_id = a.appd_id where a.appd_id = ? and a.status > 0");

            }

           


             if($table == $app_olevel){
                 $o_result = $this->db->result;
                 $r = [];
                 if($o_result != NULL){
                 foreach($o_result as $res){
                                      $this->db->pullAll(array($res['exam_number']), "select $app_olevel_result.*  from $app_olevel as a
                 join $app_olevel_result on $app_olevel_result.exam_number = a.exam_number where a.exam_number = ? ");
                 $res['result'] = $this->db->result;
                 $r []= $res;
                 }
                 $result[$table] = $r;
                 continue;
                }
             }
            $result[$table] = $this->db->result;
        }

        return $result;
    }

    function getSummaryAll($session)
    {
        $s = $session;
        extract(TABLES);
       
        $result = [];
        $this->db->pullAll(["session"=> $s ], "select $app_application.appd_id, $app_application.status , $app_course.*, $app_names.*,   if($app_application.status = 0, 'not started',
         if($app_application.status = 1, 'not submitted', 'submitted')) as status_text  from $app_application
          join $app_names on $app_names.email = $app_application.email
           join $app_payment on $app_payment.email = $app_application.email and type = ".APP_PAYMENT."
        
           left join $app_course on $app_application.appd_id = $app_course.appd_id 
           where $app_application.session = ? order by $app_application.status DESC ");

        $applicants = $this->db->result;
      //var_dump($applicants); return;
        foreach ($applicants as $app_r) {



      
              if ($app_r['status'] > 0){
              
            $a =  $this->db->pull(['appd_id'=>$app_r['appd_id'], 'session'=>$s],"select if(status = 0, 'not paid','paid')
            as exam_status from $app_payment where appd_id = ? and session = ? and type = ".APP_PAYMENT_EXAM);
           
            $app_r['exam_status'] = $a ? @$this->db->result['exam_status'] : 'not paid';
          
                $this->portal->pull(['id'=>$app_r['dept_id']],"select name from $dept where id = ?");
                $app_r['department'] = @$this->portal->result['name'];
                $this->portal->pull(['id'=>$app_r['faculty_id']],"select name from $faculty where id = ?");
                $app_r['faculty'] = @$this->portal->result['name'];
                $this->portal->pull(['id'=>$app_r['programme_id']],"select programme from $prog where id = ?");
                $app_r['programme'] = @$this->portal->result['programme'];
                $this->portal->pull(['id'=>$app_r['options_id']],"select name from $options where id = ?");
                $app_r['options'] = @$this->portal->result['name'];
         

              }else{
                $app_r['exam_status'] = 'not paid';
              }


            $result[] = $app_r;
        
           
        }
    
    
      
    
 
        return $result;
    }

    function getRefereeSummary($session)
    {
        $s = $session;
        extract(TABLES);
      
        $result = [];
        try{
        $this->db->pullAll(["session"=> $s ], "select $app_application.*, $app_names.*, $app_login.phone, $app_course.*, $app_payment.rrr, $app_bio.address  from $app_application
          join $app_names on $app_names.email = $app_application.email
           join $app_payment on $app_payment.email = $app_application.email and type = ".APP_PAYMENT." 
           join $app_bio on $app_application.appd_id = $app_bio.appd_id 
        
           join $app_login on $app_login.email = $app_application.email 
            join $app_course on $app_application.appd_id = $app_course.appd_id 
           where $app_application.session = ? and $app_application.status > 1  order by $app_application.serial  ASC ");
if($this->db->result != null){
    $this->portal->pull(['id'=>$s],"select * from $session where id = ?");
           
    $ss = @$this->portal->result['session'];
           foreach ($this->db->result as $app_r) {
            $app_r['session'] = $ss;
           
         
            $a =  $this->db->pullAll(['appd_id'=>$app_r['appd_id']],"select * from $app_referee as ar join $app_referee_form  as arf on  ar.id = arf.m_id where ar.appd_id = ?  and ar.status = 1");
            $app_r['referee'] = @$this->db->result;
           
        
   
            $this->portal->pull(['id'=>$app_r['faculty_id']],"select name from $faculty where id = ?");
            $app_r['faculty']  = @$this->portal->result['name'];
             $this->portal->pull(['id'=>$app_r['dept_id']],"select name from $dept where id = ?");
             $app_r['department'] = @$this->portal->result['name'];
         
             $this->portal->pull(['id'=>$app_r['programme_id']],"select programme from $prog where id = ?");
             $app_r['programme'] = @$this->portal->result['programme'];
             $this->portal->pull(['id'=>$app_r['options_id']],"select name from $options where id = ?");
             $app_r['options'] = @$this->portal->result['name'];
             $result['session'] = $app_r['session'];
             $result['value'][$app_r['faculty']][$app_r['department']][ $app_r['programme']][] = $app_r;

           }
        }
       return $applicants = $result;
      
    }catch(Exception $e){
        echo $e;
    }
      
      
      
    
    }








    function getSummarySubmitted($session)
    {
        $s = $session;
        extract(TABLES);
      
        $result = [];
        try{
        $this->db->pullAll(["session"=> $s ], "select $app_application.*, $app_names.*, $app_login.phone, $app_course.*, $app_payment.rrr, $app_bio.address  from $app_application
          join $app_names on $app_names.email = $app_application.email
           join $app_payment on $app_payment.email = $app_application.email and type = ".APP_PAYMENT." 
           join $app_bio on $app_application.appd_id = $app_bio.appd_id 
        
           join $app_login on $app_login.email = $app_application.email 
            join $app_course on $app_application.appd_id = $app_course.appd_id 
           where $app_application.session = ? and $app_application.status > 1  order by $app_application.serial  ASC ");
if($this->db->result != null){
    $this->portal->pull(['id'=>$s],"select * from $session where id = ?");
           
    $ss = @$this->portal->result['session'];
           foreach ($this->db->result as $app_r) {
            $app_r['session'] = $ss;
            $a =  $this->db->pull(['appd_id'=>$app_r['appd_id'], 'session'=>$s],"select if(status = 0, 'not paid','paid')
            as exam_status from $app_payment where appd_id = ? and session = ? and type = ".APP_PAYMENT_EXAM);
           
            $app_r['exam_status'] = $a ? @$this->db->result['exam_status'] : 'not paid';
            $a =  $this->db->pullAll(['appd_id'=>$app_r['appd_id']],"select * from $app_degree where appd_id = ? ");
            $app_r['degree'] = @$this->db->result;

            $a =  $this->db->pullAll(['appd_id'=>$app_r['appd_id']],"select * from $app_referee as ar join $app_referee_form  as arf on  ar.id = arf.m_id where ar.appd_id = ?  and ar.status = 1");
            $app_r['referee'] = @$this->db->result;
           
            $a =  $this->db->pullAll(['appd_id'=>$app_r['appd_id']],"select * from $app_olevel as ao join $app_olevel_result  as aor on  aor.exam_number = ao.exam_number where ao.appd_id = ? ");
            $app_r['olevel'] = @$this->db->result;
            if($a){
                $temp = [];
              foreach($app_r['olevel'] as $key => $value) {
             
                  @$temp[$value['exam_type'].'-'.$value['exam_number']] .= $value['subject'].'-'.$value['grade'].",";
              }
              $app_r['olevel'] = $temp;
            }else{
                $app_r['olevel'] = [];
            }
   
            $this->portal->pull(['id'=>$app_r['faculty_id']],"select name from $faculty where id = ?");
            $app_r['faculty']  = @$this->portal->result['name'];
             $this->portal->pull(['id'=>$app_r['dept_id']],"select name from $dept where id = ?");
             $app_r['department'] = @$this->portal->result['name'];
         
             $this->portal->pull(['id'=>$app_r['programme_id']],"select programme from $prog where id = ?");
             $app_r['programme'] = @$this->portal->result['programme'];
             $this->portal->pull(['id'=>$app_r['options_id']],"select name from $options where id = ?");
             $app_r['options'] = @$this->portal->result['name'];
             $result['session'] = $app_r['session'];
             $result['value'][$app_r['faculty']][$app_r['department']][ $app_r['programme']][] = $app_r;

           }
        }
       return $applicants = $result;
      
    }catch(Exception $e){
        echo $e;
    }
      
      
      
    
    }
















    function setBio($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);
        $input['passport'] = $this->location;
        $input['appd_id'] = $appd_id;

        if ($this->getBio()) {
            return $this->db->update($input, array("appd_id" => $appd_id), [], $app_bio);
        }
        return $this->db->insert($input, $app_bio);
    }


    function setAlevel($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->insert($input, $app_alevel);
    }
    function setProf($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->insert($input, $app_prof);
    }
    function setPub($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->insert($input, $app_pub);
    }

    function setThesis($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->insert($input, $app_thesis);
    }
    function setReferee($input)
    {
        extract(TABLES);
        extract($this->app);
       // unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;
                $c = $this->db->pull(array("email"=>$input['email'],"appd_id"=>$appd_id),"select * from $app_referee where email = ? and appd_id = ?");
       if($c){
           return array("code"=> 0 , "message"=>"You have added this referee already");
       }
                $c = $this->db->insert($input, $app_referee);
                if($c){
                    return array("code"=> 1 , "message"=>"Successful");
                }
                return array("code"=> 0 , "message"=>"Could not save information");
    }
    function setOther($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;
         if($this->getOther()){
           return  $this->db->update($input,array("appd_id"=>$appd_id),[], $app_other);
         }
        return $this->db->insert($input, $app_other);
    }
    function setEmp($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->insert($input, $app_emp);
    }
    function setDegree($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->insert($input, $app_degree);
    }
    function setOlevel($input,$result)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

            $olevel =     $this->db->pull([$input['exam_number'],$appd_id],"select * from $app_olevel where exam_number = ? and appd_id = ?");
       if($olevel){
           return array("code"=>0, "message"=>"This exam number is already existng with this id.");
       }

      $resp = $this->db->insert($input,$app_olevel);

      if(!$resp){
        return array("code"=>0, "message"=>"Could not save information.");
      }

       $olevel_result =     $this->db->pull([$input['exam_number']],"select * from $app_olevel_result where exam_number = ? ");
       
       if($olevel_result){
           return array("code"=>1, "message"=>"Result already existing");
       }
           foreach($result as $key=>$res){
             
               $this->db->insert(array(
                   "exam_number"=>$input['exam_number'],
                   "subject"=>$res['subject'],
                   "grade"=>$res['grade']
               ), $app_olevel_result);
           }
           return array("code"=>1, "message"=>"Result Saved");
    }
    function removeAlevel($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, ["and"], $app_alevel);
    }
    function removeProf($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, ["and"], $app_prof);
    }
    function removePub($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, ["and"], $app_pub);
    }
    function removeThesis($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, ["and"], $app_thesis);
    }
        function removeReferee($input)
        {
            extract(TABLES);
            extract($this->app);
            unset($input['email']);
            unset($input['session']);
    
            $input['appd_id'] = $appd_id;
    
            return $this->db->delete($input, ["and"], $app_referee);
        }    
        
        function removeOther()
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, [], $app_other);
    }
    function removeEmp($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, ["and"], $app_emp);
    }
    function removeDegree($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

        return $this->db->delete($input, ["and"], $app_degree);
    }
    function removeOlevel($input)
    {
        extract(TABLES);
        extract($this->app);
        unset($input['email']);
        unset($input['session']);

        $input['appd_id'] = $appd_id;

       $res =  $this->db->delete($input, ["and"], $app_olevel);
       if($res){
        return   $this->db->delete(array("exam_number"=>$input['exam_number']), [], $app_olevel_result); 
       }
    }
    function Submit()
    {
        extract(TABLES);
        extract($this->app);
        $side = $this->getSideSummary();
  
        extract($side);
           if($status > 1){
            return array("code"=>0, "message"=>"You have Submitted this application already");
           }
        if($course_info_count == 0){
            return array("code"=>0, "message"=>"You have not completed your Course information");
        }
        if($user_info_count == 0){
            return array("code"=>0, "message"=>"You have not completed your Bio information");
        }
        if($olevel_info_count == 0){
            return array("code"=>0, "message"=>"You have not completed your Olevel information");
        }
        if($degree_info_count == 0){
            return array("code"=>0, "message"=>"You have not completed your Degree information");
        }
        if($thesis_info_count == 0){
            return array("code"=>0, "message"=>"You have not completed your Thesis information");
        }
     
        if($referee_info_count < 3){
            return array("code"=>0, "message"=>"You have not completed your Referee information");
        }
        if($other_info_count == 0){
            return array("code"=>0, "message"=>"You have not completed your Other information");
        }
       

       

       $res =  $this->db->update(array("status"=>2, "date_applied"=>Carbon::now()),["appd_id"=>$appd_id], [], $app_application);
       if($res){
          return array("code"=>1, "message"=>"Submitted");
       }
       return array("code"=>0, "message"=>"COuld not submit application");
    }























    function checkExist($id, $session)
    {

        $query = http_build_query(array(
            'appd_id' => $id,
            'session' => $session
        ));

        $response = file_get_contents(API_APPLICANT . $query, false);
        $user = json_decode($response, true);
        if ($user->code == 1) {
            return $user;
        }
        return false;
    }
    function setAdmission($post, $session)
    {
        $error = [];
        $APIsql = '';
        $APIsqlrole  = array();
        foreach ($post as $key => $pos) {

            if (!($pos['status'] == 1 || $pos['status']  == 0)) {
                $error[] = array('id' => $pos['appd_id'], 'reason' => 'invalid admission status');
                unset($post[$key]);
                continue;
            }
            // $user = ($this->checkExist($pos['appd_id'],$session));
            //  if($user === false){
            //   $error[] = array('id'=>$post['appd_id'], 'reason'=>'invalid admission status');
            //   unset($post[$key]);
            //  continue;
            //}
            if ($this->checkAdmission($pos['appd_id'], $session)) {
                $error[] = array('id' => $pos['appd_id'], 'reason' => 'Admission set already');
                unset($post[$key]);
                continue;
            }


            $APIsql .=  "when appd_id = '" . $pos['appd_id'] . "'  then '" . ($pos['status'] == 1 ? 3 : 4) . "'  ";

            $APIsqlrole[] = "'" . $pos['appd_id'] . "'";


            // ;

        }
        //var_dump($user);

        $q = implode(',', $APIsqlrole);

        return  array('code' => 1, 'message' => 'success', 'payload' => array('error' => $error, 'Api' => array('setQuery' => $APIsql, 'whereQuery' => $q), 'filteredApp' => $post));
    }

    function saveAdmissionList($list, $session, $faculty, $dept)
    {
        $sql = "INSERT INTO " . TABLES['admission'] . "(appd_id,department,faculty,session,name,adm_status) values (?,?,?,?,?,?)";
        //  $qpart = array_fill(0,count($list),"(?,?,?,?,?,?)");
        //   $sql .= implode(',', $qpart);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $stmt =  $this->db->prepare($sql);
        if (!$stmt) {
            echo "\nPDO::errorInfo():\n";
            print_r($this->db->errorInfo());
        }

        $i = 1;
        $this->db->beginTransaction();
        foreach ($list as $app) {

            $d = $this->getID($app['dept'], $dept);
            $f = $this->getID($app['faculty'], $faculty);

            if (!$stmt->execute([$app['appd_id'], $d, $f, $session, $app['name'], $app['status']])) {
                $this->db->rollback();
                return false;
            }
        }
        try {
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
        }
    }

    function setBulkAdmission($app)
    {

        $list[][] = array();
        $arrayhead = $app[5];
        $i = 0;
        foreach ($arrayhead as $head) {
            if ($i > 6) break;
            if ($head != HEAD[$i]) {
                return (object)array('code' => 0, 'message' => 'invalid file');
            }
            $i++;
        }
        $file = array_splice($app, 5);

        $i = 0;
        foreach ($file as $student) {

            $list[$i]['appd_id'] = $student['B'];
            $list[$i]['name'] = $student['C'];
            $list[$i]['email'] = $student['D'];
            $list[$i]['faculty'] = $student['E'];
            $list[$i]['dept'] = $student['F'];
            $list[$i]['status'] = $student['G'];
            $i++;
        }

        return (object)array('code' => 1, 'message' => 'Success', 'payload' => $list);
    }



    function allActive()
    {




        $query = "Select 
       * 
       from  " . TABLES['session'] . " 
        where status = 1";

        $stmt = $this->db->prepare($query);


        $stmt->execute();
        $count = $stmt->rowCount();
        $count;
        if ($count > 0) {

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // assign values to object properties
            $this->all = $row;
            return true;
        }
        return false;
    }

    public function checkAdmission($id, $session)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['admission'] . "

              WHERE appd_id = :id and session = :session ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $this->id);
        $stmt->bindParam('session', $session);

        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }

        return true;
    }

    function getAdmissionList($id)
    {
        $session = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['admission'] . "

            WHERE session = :id ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $session);
        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->all[] = $row;
        }

        return true;
    }
    function getID($id, $obj)
    {

        $v = array_filter($obj, function ($var) use ($id) {
            return ($var['name'] == $id);
        });


        foreach ($v as $key) {

            if (isset($key['id'])) {
                return $key['id'];
                break;
            }
        }

        return null;
    }
}
