<?php



class Student
{
  public $info;
  public  $otherName;
  public $lastName;
  public $id;
  private $table = 'users';
  public $db;
  private $password;
  public $photo;
  public $role;
  function __construct($db)
  {
    $this->db = $db;
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }


  function newStudent($id){

  if(  (int) $this->info['session_admitted'] >   (int) $id ){
    return -1;
  }else{
    return  (int) $this->info['session_admitted'] ==   (int) $id;
  }

  }
  
  function Auth($new)
  {
  
     $query = "Select 
       *
       from  ".TABLES['users']."

 
        where ".TABLES['users'].".userid = :id
       " ;

      $stmt = $this->db->prepare($query);
       $this->id = htmlspecialchars(strip_tags($this->id));
       $stmt->bindParam(':id',$new);
     
       $stmt->execute();
     $count = $stmt->rowCount();
 
      if ($count > 0){
      
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        // assign values to object properties
     
       
           $query =  "select bio.*, acad.dept_id, acad.faculty_id, acad.prog_id, acad.session_admitted, acad.appd_id, role.id as role, role.role as role_name
             from ".TABLES['users']." as u join ".
             TABLES['students_info']." as bio on bio.student_id = u.userid join "
             .TABLES['students']." as acad on acad.student_id =  u.userid join "
             .TABLES['roles']." as role on role.id =  u.role where u.userid = ? ";
             $stmt = $this->db->prepare($query);
             $stmt->execute([$new]);
          $row = $stmt->fetch(PDO::FETCH_ASSOC);

          $query =  "select *
          from ".TABLES['student_clear']."  where (student_id = ? || student_id = ?) and session = ? and clearance_type = ?  ";
          $s = $this->db->prepare($query);
          $s->execute([$this->id,$new,$row['session_admitted'],SCHOOL_FEE]);
       $count  = $s->rowCount() ;
    
          
           
           $this->data = array(
            "id" => $row['student_id'], // $user->id,
            "firstname" => $row['firstname'],
            "othername" => $row['othername'],
            "lastname" => $row['surname'],
            "dept_id" => $row['dept_id'],//$user->dept_id,
            "faculty_id" => $row['faculty_id'],
            "session_admitted" => $row['session_admitted'],
            "prog_id"=> $row['prog_id'],// $user->lastName,
            "email" => $row['email'],
            "condition" => $count == 0 ? 0 : 1 ,
            "photo" => 'https://spgs.fulafia.edu.ng/applications/registration/'.$row['passport'],
            'role'=> $row['role'],//$user->role,
            'role_name'=>$row['role_name'],
            "name" => $row['firstname'] . " " . $row['surname'],
           
            );
            return true;
          }

    return false;
  }

function activateStudent(){
  
 $app = $this->app->getSummary();
extract($app);
extract($application);
extract($course_info);

$this->user_info = $user_info;
$r =  $this->createUser($appd_id, ACCOUNT['STUDENT']);


if($r['code']==0){
  return $r;
}

$a = $this->saveAcademicInfo(array(
  "appd_id" => $appd_id,
  "student_id" =>  null,
  "session_admitted"=> $session_id,
  "dept_id" => $dept_id,
  "faculty_id"=>$faculty_id,
  "prog_id" => $programme_id,
  "options_id" => $options_id,
  "status"=>1
));

if(!$a){
  $this->portal->delete(['appd_id'=>null],[],$users);
  return ['code'=>0,'message'=>'Could not save academic information'];
}
$user_info['passport'] = $user_info['passport_r'];
unset($user_info['passport_r']);
$bio =   $this->saveBioInfo($user_info);
if(!$bio){
  $this->portal->delete(['student_id'=>null],[],$users);
  $this->portal->delete(['appd_id'=>null],[],$students);
  return ['code'=>0,'message'=>'Could not save Bio infromation'];
}


return ['code'=>1,'message'=>'Student Cleared'];



}
  private function genMatric(){
    $q = "select count(*) as count, d.short_name as dname, f.short_name as fname , p.short_name as pname , se.session as sess from ".TABLES['students']. " as s join
   
    ".TABLES['student_clear']. " as sc on sc.student_id = s.appd_id and clearance_type = ? and session = ?  join 

    ".TABLES['dept']. " as d on d.id = s.dept_id   join 
    ".TABLES['faculty']. " as f on f.id =s.faculty_id   join 
    ".TABLES['prog']. " as p on p.id =s.prog_id   join
    ".TABLES['session']. " as se on se.id =s.session_admitted   
    where s.dept_id = ? and s.faculty_id = ? and session_admitted = ? and prog_id = ?";
    $stmt =  $this->db->prepare($q);




    $stmt->execute([SCHOOL_FEE,$this->info['session_admitted'],$this->info['dept_id'],$this->info['faculty_id'],$this->info['session_admitted'],$this->info['prog_id']]);

    $count = $stmt->rowCount();

   
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
     $ses = explode('/',$row['sess'])[0];
     $zeros = "";
     if((int)$row['count'] < 10){
       $zeros = "000";
     }else if((int)$row['count'] < 100){
      $zeros = "00";
     }else if((int)$row['count'] < 100){
      $zeros = "0";
     }
     $p = strtoupper(str_replace(".","",$row['pname']));
   $reg = $ses.'/'.$row['fname'].'/'.$row['dname'].'/'.$p.'/'.$zeros.$row['count'];
   $this->db->beginTransaction();
     
   $q = "update ".TABLES['students_info']." set student_id = ? where student_id = ? ";
   $stmt =  $this->db->prepare($q);

    if (!$stmt->execute([$reg, $this->info['appd_id']])) {
       $this->db->rollback();
       return false;
     }
   


   $q = "update ".TABLES['students']." set student_id = ? where appd_id = ? ";
   $stmt =  $this->db->prepare($q);

   if (!$stmt->execute([$reg, $this->info['appd_id']])) {
    $this->db->rollback();
    return false;
  }


  $q = "update ".TABLES['users']." set userid = ? where userid = ? ";
  $stmt =  $this->db->prepare($q);

  if (!$stmt->execute([$reg, $this->info['appd_id']])) {
   $this->db->rollback();
   return false;
 }
     $this->db->commit();

     return $reg;
 
  }

  public function createMatricNumber($client,$email){
    $res = $this->genMatric();
    if($res == false){
   return array("code"=> 0, "message"=>"Verification Successful but Matric Number generation failed! contact Admin");

    }

    
 $info = array(
   "id"=>htmlspecialchars(strip_tags($res)),
   "name" => $this->info['firstname'] . ' ' .$this->info['surname'],
   "cron" => false,
   "email" => $this->info['email'],
   "type" => EMAIL_CODE_MATRIC
 );

 $email->notify(EMAIL_CODE_MATRIC,$info);
  

  
     return array("code"=>1 ,"message"=>"profile updated", "payload"=>$res);
  
  }
  public function verify($id,$i = 0)
  {
    $this->id = htmlspecialchars(strip_tags($id));
$extra = '';
    if($i==1){
    $extra = ' and  std.status = 1';
    }
    $q = "SELECT std_info.*, std.*, sess.session , d.name as department , op.name as option , f.name as faculty ,p.programme, p.short_name from " .

      TABLES['students'] . " as std join 
      " . TABLES['students_info'] . " as std_info on std.student_id = std_info.student_id or std.appd_id = std_info.student_id

      join ". TABLES['dept'] ." as d on d.id = std.dept_id 
      join ". TABLES['faculty']." as  f on std.faculty_id = f.id
        join ". TABLES['options']." as  op on std.options_id = op.id
      join ". TABLES['prog']." as  p on std.prog_id = p.id
      join ". TABLES['session'] ." as sess on sess.id = std.session_admitted
              WHERE std.student_id = ? or std.appd_id = ? ".$extra;

    $stmt =  $this->db->prepare($q);




    $stmt->execute([$this->id,$this->id]);
    $count = $stmt->rowCount();

    if ($count <= 0) {
      return false;
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->info = $row;


    return true;
  }
  public function getClearance($id, $session)
  {
    $this->id = htmlspecialchars(strip_tags($id));
    $session = htmlspecialchars(strip_tags($session));
    $q = "SELECT * from " .

      TABLES['student_clear'] . " 

              WHERE (student_id = ? || student_id = ?) and session = ? order by clearance_type ASC";

    $stmt =  $this->db->prepare($q);




    $stmt->execute([$this->id,$this->info['appd_id'], $session]);
    $count = $stmt->rowCount();

    if ($count <= 0) {
      return false;
    }
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->clearance = $row;


    return true;
  }


  function  getRegisteredCourses($semester, $session)
  {


    $register = array();
    $register2 = array();
    $carry = array();
  $session = htmlspecialchars(strip_tags($session));

   $semester = htmlspecialchars(strip_tags($semester));

    $query = "select  load_id from " . TABLES['result_course'] . " as rc right join 
   
   " . TABLES['register_course'] . " as reg on rc.reg_id = reg.id where reg.semester = ?  and reg.student_id = ?
  and (ca_1 + ca_2 + exam ) < 50 
    group by course_id";

    $stmt =    $this->db->prepare($query);

    $carryOver = array();
    $c = array();
    $cj = array();
    if ($stmt->execute([$semester, $this->info['student_id']])) {
      $stmt->rowCount();

      if ($stmt->rowCount() > 0) {
        while ($carryOver = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $c[] = $carryOver['load_id'];
        }
       
        $in = str_repeat('?,', count($c) - 1) . '?';
        $query = "select  load_id from " . TABLES['result_course'] . " as rc right join 
   
        " . TABLES['register_course'] . " as reg on rc.reg_id = reg.id where reg.semester = ?  and reg.student_id = ?
       and  (ca_1 + ca_2 + exam ) > 50 and load_id in ($in)
         group by course_id";
        $stmt =    $this->db->prepare($query);

        if ($stmt->execute(array_merge([$semester, $this->info['student_id']], $c))) {

          if ($stmt->rowCount() > 0) {
            while ($carryOver = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $cj[] = $carryOver['load_id'];
            }
          }
        }

        $carry = array_diff($c, $cj);

      }

    
     $query = "select  cl.id as id, cl.course_class, cl.course_load, c.course_title, c.course_code, cl.id , c.id as course_id, rc.status
  
  
  from " . TABLES['register_course'] . " as rc 
  
 join 
   
        " . TABLES['courses_load'] . " as cl on rc.load_id = cl.id 
        
join   " . TABLES['courses'] . " as c on c.id = rc.course_id 

where rc.semester = ?  and rc.session = ? and rc.student_id = ? 
   ";
  
      $stmt =    $this->db->prepare($query);
      if ($stmt->execute([$semester, $session, $this->info['student_id']])) {
    
        if ($stmt->rowCount() > 0) {
          $register = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($carry) > 0) {
     
    
        
          $in = str_repeat("?,", count($carry) - 1) . "?";
          $query = "select true as must, cl.id as id , cl.course_class, cl.course_load, c.course_title, c.course_code,  c.id as course_id , 0 as status
  
  

         from " . TABLES['courses_load'] . " as cl 
         
       join   " . TABLES['courses'] . " as c on cl.course_id = c.id 
 
        where  cl.id in ($in)
    ";


          $stmt =    $this->db->prepare($query);

          if ($stmt->execute(array_values($carry))) {
            if ($stmt->rowCount() > 0) {
              $register2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
              $new = [];
             // $this->setRegisteredCourses($register2, $semester, $session);
            }
          }
        }
      }
      $final = array_replace_recursive(array_column($register2, null, 'id'), array_column($register, null, 'id'));
   
      return array('code' => 1, 'message' => 'found', 'payload' => $final);
    }
  }




  function  getRC()
  {


    $register = array();
    $register2 = array();
    $carry = array();
  

    $query = "select  load_id from " . TABLES['result_course'] . " as rc right join 
   
   " . TABLES['register_course'] . " as reg on rc.reg_id = reg.id where  reg.student_id = ?
  and (ca_1 + ca_2 + exam ) < 50 
    group by course_id";

    $stmt =    $this->db->prepare($query);

    $carryOver = array();
    $c = array();
    $cj = array();
    if ($stmt->execute([$this->info['student_id']])) {


      if ($stmt->rowCount() > 0) {
        while ($carryOver = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $c[] = $carryOver['load_id'];
        }
        $in = str_repeat('?,', count($c) - 1) . '?';
        $query = "select  load_id from " . TABLES['result_course'] . " as rc right join 
   
        " . TABLES['register_course'] . " as reg on rc.reg_id = reg.id where  reg.student_id = ?
       and  (ca_1 + ca_2 + exam ) > 50 and load_id in ($in)
         group by course_id";
        $stmt =    $this->db->prepare($query);

        if ($stmt->execute(array_merge([$this->info['student_id']], $c))) {

          if ($stmt->rowCount() > 0) {
            while ($carryOver = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $cj[] = $carryOver['load_id'];
            }
          }
        }

        $carry = array_diff($c, $cj);
      }

      $query = "select s.session, rc.semester, cl.id as id, cl.course_class, cl.course_load, c.course_title, c.course_code, cl.id , c.id as course_id, rc.status
  
  
  from " . TABLES['register_course'] . " as rc 
  
 join 
   
        " . TABLES['courses_load'] . " as cl on rc.load_id = cl.id 
        
join   " . TABLES['courses'] . " as c on c.id = rc.course_id 
join   " . TABLES['session'] . " as s on s.id = rc.session 
where  rc.student_id = ? 
   ";
      $stmt =    $this->db->prepare($query);
      if ($stmt->execute([ $this->info['student_id']])) {

        if ($stmt->rowCount() > 0) {
          $register = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($carry) > 0) {
          $in = str_repeat("?,", count($carry) - 1) . "?";
          $query = "select  cl.id as id , cl.course_class, cl.course_load, c.course_title, c.course_code,  c.id as course_id , 0 as status
  
  

         from " . TABLES['courses_load'] . " as cl 
         
       join   " . TABLES['courses'] . " as c on cl.course_id = c.id 
 
        where  cl.id in ($in)
    ";


          $stmt =    $this->db->prepare($query);

          if ($stmt->execute(array_values($carry))) {
            if ($stmt->rowCount() > 0) {
              $register2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
          }
        }
      }
      $final = array_replace_recursive(array_column($register2, null, 'id'), array_column($register, null, 'id'));

      return array('code' => 1, 'message' => 'found', 'payload' => $final);
    }
  }







  function setRegisteredCourses($list, $semester, $session)
  {

    $session = htmlspecialchars(strip_tags($session));

     $semester = htmlspecialchars(strip_tags($semester));

    $query = "delete from " . TABLES['register_course'] . " where  semester = ?  and  session = ? and  student_id = ? ";

    $stmt =    $this->db->prepare($query);
    if ($stmt->execute([$semester,  $session, $this->info['student_id']])) {

      $query = "insert into  " . TABLES['register_course'] . " set  semester = ?, load_id = ? , course_id = ?,  dept_id = ?,  session = ?, student_id = ?";
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $stmt =    $this->db->prepare($query);
      if (!$stmt) {
        echo "\nPDO::errorInfo():\n";
        print_r($this->db->errorInfo());
      }

      $course = array();
      $this->db->beginTransaction();
      foreach ($list as $app) {

        // $app = htmlspecialchars(strip_tags($app));




        if (!$stmt->execute([$semester, $app['id'], $app['course_id'], $this->info['dept_id'],  $session, $this->info['student_id']])) {
          $this->db->rollback();
          return array('code' => 0, 'message' => "DB ERror");
        }
      }

      $this->db->commit();
      return array('code' => 1, 'message' => "Success");
    }
  }

  function setApproveCourses($semester, $session)
  {


     $session = htmlspecialchars(strip_tags($session));


    $semester = htmlspecialchars(strip_tags($semester));


    $query = "update " . TABLES['register_course'] . " set status = 1 where  semester = ?  and  session = ? and  student_id = ? ";


    $stmt =    $this->db->prepare($query);


    if ($stmt->execute([$semester,  $session, $this->info['student_id']])) {

      return true;
    }
    return false;
  }

  function checkRegID($id, $load)
  {
    $id =  htmlspecialchars(strip_tags($id));
    $load =  htmlspecialchars(strip_tags($load));
    $query = "select * from " . TABLES['register_course'] . " where id = ? and student_id = ? and course_id = ?";


    $stmt = $this->db->prepare($query);

    if ($stmt->execute([$id, $this->info['student_id'], $load])) {
      if ($stmt->rowCount() > 0) {
        return true;
      }
    }
    return false;
  }


  function setResult($score, $reg , $log, $course, $session)
  {
 $reg;
    $query = "select * from " . TABLES['result_course'] . " where reg_id = ?";

$changes = '';
    $stmt = $this->db->prepare($query);

    if ($stmt->execute([$reg])) {
   $stmt->rowCount() ;
      if ($stmt->rowCount() > 0) {
       "in";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row['ca_1'] != $score['ca1']){
          $changes .= " Changed ca1 from ". $row['ca_1']. " to ".$score['ca1'];
        }
        if($row['ca_2'] != $score['ca2']){
          $changes .= " Changed ca2 from ". $row['ca_2']. " to ".$score['ca2'];
        }
        if($row['exam'] != $score['exam']){
          $changes .= " Changed exam from ". $row['exam']. " to ".$score['exam'];
        }
        $type = "update ";
        $extra = "where id =  ".$row['id'];
      } else {
        $extra = '';
        $type = "insert into  ";
      }
    } else {
      return false;
    }



  $query = $type . TABLES['result_course'] . " set reg_id = ? , ca_1 = ? , ca_2 = ? , exam = ?  ". $extra;

 

    $stmt = $this->db->prepare($query);

    if ($stmt->execute([$reg, $score['ca1'], $score['ca2'], $score['exam']])) {
      if($changes != ''){
        $changes .= ' for '.$this->info['student_id'];

       $log->resultLog($changes,$course,$session);
      }
      return true;
    }
    return false;
  }

  function createUser($id,$type){
    $valid = true;
    $stmt = $this->db->prepare("select * from ".TABLES['students'].' where appd_id = ?');
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
     $valid = false;
   }
    $stmt = $this->db->prepare("select * from ".TABLES['students_info'].' where student_id = ?');
    $stmt->execute([$id]);
     if($stmt->rowCount() > 0){
       $valid = false;
     }
   
    $stmt = $this->db->prepare("select * from ".TABLES['users'].' where userid = ?');
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
     $valid = false;
   }
   if(!$valid){
     return array("code"=>0 , "message"=>"You already have accepted the admission");
   }
    $q = "insert into ".TABLES['users']." (userid,role,password) values (?,?,?)";

    $stmt = $this->db->prepare($q);
$pass = password_hash($id,PASSWORD_DEFAULT );
    if($stmt->execute([$id,$type,$pass])){
     return array("code"=>1 , "message"=>"Successful");
    }
    return array("code"=>0 , "message"=>"Internal Error");
  }
  function rollback($id,$type){
    if ($type ==1 ){
    $stmt = $this->db->prepare("delete  from ".TABLES['users'].' where userid = ?');
    $stmt->execute([$id]);
    }else {
      $stmt = $this->db->prepare("delete  from ".TABLES['users'].',  where userid = ?');
      $stmt->execute([$id]);
      $stmt = $this->db->prepare("delete  from ".TABLES['students'].',  where student_id = ?');
      $stmt->execute([$id]);
    }
 
  
  }
  function saveAcademicInfo($info){

    $q = "insert into ".TABLES['students']." set 
    
  
    appd_id = ? ,
    session_admitted = ? ,
    dept_id = ? ,
    faculty_id = ?,
    prog_id = ? ,
    options_id = ?
   ";

    $stmt = $this->db->prepare($q);

    if($stmt->execute([$info['appd_id'],$info['session_admitted'],$info['dept_id'],
    $info['faculty_id'],
    $info['prog_id'], $info['options_id']])){
     return true;
    }
    return false;
  }
  function saveBioInfo($info){

    $q = "insert into ".TABLES['students_info']." set 
    
    student_id = ? ,
    sex = ? ,
    phone = ? ,
    email = ? ,
    marital_status = ?,
    dob = ? ,
    state = ? ,
    lga = ? ,
    address = ?,
    postal_address = ? ,
    country = ? ,
    passport = ?,
    firstname = ?,
    othername = ?,
    surname = ?

   ";

    $stmt = $this->db->prepare($q);

    if($stmt->execute([$info['appd_id'],$info['sex'],
    $info['phone'],
    $info['email'],
    $info['marital_status'],
    $info['dob'],
    $info['state'],
    $info['lga'],
    $info['address'],
    $info['postal_address'],
    $info['country'],
    $info['passport'],
    $info['firstname'],
    $info['othername'],
    $info['surname'],
  
    
    ])){
     return true;
    }
    return false;
  }
function checkAdmissionState($id){
 
  $query = "select * from " . TABLES['admission'] . " where appd_id = ? and adm_status  = 1 ";


  $stmt = $this->db->prepare($query);

  if ($stmt->execute([$id])) {
    if ($stmt->rowCount() > 0) {
      $this->adm_info = $stmt->fetch(PDO::FETCH_ASSOC);
      return true;
    }
  }
  return false;
}
function setBulkResult($a,$courseid,$session,$logs){
  $error = array();
$array = (array)$a->payload;
  $file = array_splice($array,5);
 $new = array();

  foreach($file as $key=>$value){
    if ($value['C'] > 20 ||$value['D'] > 20 ||((float)$value['C'] + (float)$value['D'] + (float)$value['E'] )> 100 ) {
   
    return array('code' => 0, 'message' => "Invalid Score for ".$value['B'], 'payload' => null);
   
  }


 $this->info['student_id'] = $value['B'];
  if (!$this->checkRegID($value['H'],$courseid)){
   return array('code' => 0, 'message' => "Invalid Course registration for  ".$value['B'], 'payload' => null);
  
}
$score = array('ca1'=>$value['C'],"ca2"=>$value['D'],"exam"=>$value['E']);
$response = $this->setResult($score,$value['H'],$logs,$courseid,$session);
if(!$response){
  $error[] =$value['B'];
}

  }
  return array('code' => 1, 'message' => "Uploading Complete", 'payload' => $error);


}




function getResult($session,$semester){
 $query = "Select 
  c.course_code, c.course_title , (res.ca_1 + res.ca_2 + res.exam) as score, if((res.ca_1 + res.ca_2 + res.exam) >= 70,'A',
  if((res.ca_1 + res.ca_2 + res.exam) >= 60, 'B', if((res.ca_1 + res.ca_2 + res.exam) >= 50, 'C',
  if((res.ca_1 + res.ca_2 + res.exam) >= 45, 'D', if((res.ca_1 + res.ca_2 + res.exam) >= 40 , 'E',
  'F'))))) as Grade, sess.session, if(rc.semester = 1, 'FIRST SEMESTER','SECOND SEMESTER') as semester_id, cl.course_load, 
  if((res.ca_1 + res.ca_2 + res.exam) > 70,cl.course_load * 5,
  if((res.ca_1 + res.ca_2 + res.exam) > 60, cl.course_load * 4, if((res.ca_1 + res.ca_2 + res.exam) > 50, cl.course_load * 3,
  if((res.ca_1 + res.ca_2 + res.exam) > 45, cl.course_load * 2, if((res.ca_1 + res.ca_2 + res.exam) >= 40 , cl.course_load * 1,
  cl.course_load * 0))))) as GP

   from  " . TABLES['register_course'] . " as rc   join 
  " . TABLES['result_course'] . " as res on res.reg_id = rc.id  join
  ". TABLES['courses_load'] ." as cl on cl.id = rc.load_id  left  join
  ". TABLES['session'] ." as sess on sess.id = rc.session  join
  ". TABLES['courses'] ." as c on c.id = rc.course_id    join
  ". TABLES['approve_result']." as ar on ar.course_id = rc.course_id and ar.status = 5 and ar.session = ? and ar.semester = ? 
  
  
  where
  rc.session = ? and rc.semester = ? and rc.status = 1 and rc.student_id = ?;

 ";

$stmt = $this->db->prepare($query);


if (!$stmt->execute([$session,$semester,$session,$semester,$this->id])) {
   return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
}

$count = $stmt->rowCount();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($count > 0) {

$load = 0;
$gp = 0;
   foreach($row as $key=>$value){
     $new['student_id'] = $this->id;
     $new['session'] = $value['session'];
     $new['semester'] = $value['semester_id'];
     $remark = ((int)$value['score'] >= 50)? "PASS": "FAIL";
     
     $new['result'][] = array("course_code"=>$value['course_code'],"course_title"=>$value['course_title'],"score"=>$value['score'],"grade"=>$value['Grade'],"course_load"=>$value['course_load'],"grade_point"=>$value['GP'],"remark"=>$remark);
    // $new['course']
    $new['signatory_name'] = "Exam Officer";
    $load += $value['course_load'];
    $gp += $value['GP'];
    $gpa =  number_format((float)($gp/$load),2);
    $new['gpa'] = number_format(round((float)$gpa,2,PHP_ROUND_HALF_UP),2,"."," ");
   }
   $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
   where spgs_dean = 1 
   
   "  ;
   
$stmt =    $this->external->prepare($query);
   $stmt->execute();
   $dean = $stmt->fetch(PDO::FETCH_ASSOC);
  
   $new['signatory_name'] = $dean['name'];
   $new['signatory_sign'] = SIGNATURE_URL.$dean['signature'];
   
 
   return array('code' => 1, 'message' => 'Found', 'payload' => $new);
  
}else{
   return array('code' => 0, 'message' => 'not Found', 'payload' => array());
}

}

function getRE(){
  $query = "Select 
   c.course_code, c.course_title , (res.ca_1 + res.ca_2 + res.exam) as score, if((res.ca_1 + res.ca_2 + res.exam) >= 70,'A',
   if((res.ca_1 + res.ca_2 + res.exam) >= 60, 'B', if((res.ca_1 + res.ca_2 + res.exam) >= 50, 'C',
   if((res.ca_1 + res.ca_2 + res.exam) >= 45, 'D', if((res.ca_1 + res.ca_2 + res.exam) >= 40 , 'E',
   'F'))))) as Grade, sess.session, if(rc.semester = 1, 'FIRST SEMESTER','SECOND SEMESTER') as semester_id, cl.course_load, 
   if((res.ca_1 + res.ca_2 + res.exam) > 70,cl.course_load * 5,
   if((res.ca_1 + res.ca_2 + res.exam) > 60, cl.course_load * 4, if((res.ca_1 + res.ca_2 + res.exam) > 50, cl.course_load * 3,
   if((res.ca_1 + res.ca_2 + res.exam) > 45, cl.course_load * 2, if((res.ca_1 + res.ca_2 + res.exam) > 40 , cl.course_load * 1,
   cl.course_load * 0))))) as GP
 
    from  " . TABLES['register_course'] . " as rc   join 
   " . TABLES['result_course'] . " as res on res.reg_id = rc.id  join
   ". TABLES['courses_load'] ." as cl on cl.id = rc.load_id  left  join
   ". TABLES['session'] ." as sess on sess.id = rc.session  join
   ". TABLES['courses'] ." as c on c.id = rc.course_id    join
   ". TABLES['approve_result']." as ar on ar.course_id = rc.course_id and ar.status = 5  
   
   
   where
   rc.status = 1 and rc.student_id = ?;
 
  ";
 
 $stmt = $this->db->prepare($query);
 
 
 if (!$stmt->execute([$this->id])) {
    return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
 }
 
 $count = $stmt->rowCount();
 $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
 if ($count > 0) {
 
 $load = 0;
 $gp = 0;
    foreach($row as $key=>$value){
      $new['student_id'] = $this->id;
      $new[$value['session']]['session'] = $value['session'];
     // $new[$value['session']]['semester'] = $value['semester_id'];
      $remark = ((int)$value['score'] >= 60)? "PASS": "FAIL";
      
      $new[$value['session']]['result'][] = array("semester"=>$value['semester_id'],"course_code"=>$value['course_code'],"course_title"=>$value['course_title'],"score"=>$value['score'],"grade"=>$value['Grade'],"course_load"=>$value['course_load'],"grade_point"=>$value['GP'],"remark"=>$remark);
     // $new['course']
   foreach( $new[$value['session']]['result'] as $key => $value){
     $load += (int)$value['course_load'];
     $gp +=  (float)$value['grade_point'];
   
     $gpa =  number_format((float)($gp/$load),2);
     $new[$value['session']]['gpa'] = number_format(round((float)$gpa,2,PHP_ROUND_HALF_UP),2,"."," ");
   }
    }
  

 }else{
    return array('code' => 0, 'message' => 'not Found', 'payload' => array());
 }
 
 }
 
public function getStudent()
{
 
  $q = "SELECT  std.status,  if(std.status = 0, 'Not Active', if(std.status = 2, 'Account Suspended', 'Active')) as statusText  , sess.session as session_admitted,  std.appd_id, std.student_id, concat(std_info.surname, ', ' ,  std_info.firstname, ' ', std_info.othername) as name,  d.name as department , f.name as faculty ,p.programme, p.short_name from " .

    TABLES['students'] . " as std join 
    " . TABLES['students_info'] . " as std_info on std.student_id = std_info.student_id or std.appd_id = std_info.student_id

    join ". TABLES['dept'] ." as d on d.id = std.dept_id 
    join ". TABLES['faculty']." as  f on std.faculty_id = f.id
    join ". TABLES['prog']." as  p on std.prog_id = p.id
    join ". TABLES['session']." as  sess on std.session_admitted = sess.id
           ";

  $stmt =  $this->db->prepare($q);




  $stmt->execute();
  $count = $stmt->rowCount();

  if ($count <= 0) {
  return [];
  }
  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
 return $this->info = $row;



}
public function getStudentInfo($payment)
{
 $payments = $payment->getPayments($this->info['student_id'],$this->info['appd_id']);
 $this->info['passport'] = PASSPORT_URL_STUDENT.$this->info['passport'];
$courses = array_values( $this->getRC()['payload']);
$result= array_values( $this->getRE()['payload']);
$c = [];
foreach($courses as $key=>$value){
  $c[$value['session']]['session'] = $value['session'];
  $c[$value['session']]['semester'][$value['semester']]['semester'] = $value['semester'] == 1 ? 'First Semester' : 'Second Semester';
  $c[$value['session']]['semester'][$value['semester']]['courses'] []= array(
                   "course_code" => $value['course_code'],
                   "course_load" => $value['course_load'],
                   "course_title" => $value['course_title'],
                   "course_class" => $value['course_class'],
  );
}

  return $info =[
  "info"=> $this->info,
  "pay" => $payments,
  "courses_registered" => $c,
  "results" => $result
  ];
  



}
}
