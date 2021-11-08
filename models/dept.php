<?php

use Carbon\Carbon;
require 'courses.php';
class Dept
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
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  function allActive()
  {




    $query = "Select 
       d.* , f.name as f_name
       from  " . TABLES['dept'] . " as d join ". TABLES['faculty'] ." as f on d.faculty = f.id
        where d.status = 1";

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

  public function check($id,$i=null)
  {
    $this->id = htmlspecialchars(strip_tags($id));

    if($i==1){
      $q = "SELECT d.*, f.name as f_name from " .

      TABLES['dept'] .  " as d join ". TABLES['faculty'] ." as f on d.faculty = f.id

              WHERE d.id = :id ";
    }else{
    $q = "SELECT d.*, f.name as f_name from " .

      TABLES['dept'] .  " as d join ". TABLES['faculty'] ." as f on d.faculty = f.id

              WHERE d.id = :id and d.status = 1";
    }
    $stmt =  $this->db->prepare($q);

    $stmt->bindParam('id', $this->id);


    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count <= 0) {
      return false;
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->name = $row['name'];
    $this->faculty = $row['f_name'];
    return true;
  }

  public function checkwithFaculty($id,$i=null,$f)
  {
    $this->id = htmlspecialchars(strip_tags($id));

    if($i==1){
      $q = "SELECT d.*, f.name as f_name from " .

      TABLES['dept'] .  " as d join ". TABLES['faculty'] ." as f on d.faculty = $f

              WHERE d.id = :id ";
    }else{
    $q = "SELECT d.*, f.name as f_name from " .

      TABLES['dept'] .  " as d join ". TABLES['faculty'] ." as f on d.faculty = $f

              WHERE d.id = :id and d.status = 1";
    }
    $stmt =  $this->db->prepare($q);

    $stmt->bindParam('id', $this->id);


    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count <= 0) {
      return false;
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->name = $row['name'];
    $this->faculty = $row['f_name'];
    return true;
  }


  function add($name, $faculty, $creator)
  {
    try {
      $f_name = htmlspecialchars(strip_tags($name));
      $f_ = htmlspecialchars(strip_tags($faculty));
      $f_creator = htmlspecialchars(strip_tags($creator));
      $stmt = $this->db->prepare('select * from ' . TABLES['dept'] . ' where name = ? ');
      if (!$stmt->execute([$f_name])) {
        return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
      }
      if ($stmt->rowCount() > 0) {
        return array('code' => 0, 'message' => 'Department Already Exist', 'payload' => null);
      }
      $stmt = $this->db->prepare('insert into   ' . TABLES['dept'] . ' set  name = ? , faculty = ? , created_by = ? ');

      if ($stmt->execute([$f_name, $f_, $f_creator])) {

        $this->all();
        return array('code' => 1, 'message' => 'Department created', 'payload' => $this->all);
      }
      return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
    } catch (Exception $e) {

      return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
    }
  }


  function setStatus($id, $status, $user)
  {
    try {
      $id = htmlspecialchars(strip_tags($id));
      $status = htmlspecialchars(strip_tags($status));
      $user = htmlspecialchars(strip_tags($user));
      $stmt = $this->db->prepare('update  ' . TABLES['dept'] . ' set  status = ? , updated_on = ? , updated_by = ? where id = ?');

      if ($stmt->execute([$status,  Carbon::now(), $user, $id])) {
        $this->all();
        return array('code' => 1, 'message' => 'Department Status Changed', 'payload' => $this->all);
      }
      return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
    } catch (Exception $e) {

      return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
    }
  }


  function Update($id, $name, $faculty, $updater)
  {
    try {
      $id = htmlspecialchars(strip_tags($id));
      $f_name = htmlspecialchars(strip_tags($name));
      $f_ = htmlspecialchars(strip_tags($faculty));
      $f_updater = htmlspecialchars(strip_tags($updater));
      $stmt = $this->db->prepare('select * from ' . TABLES['dept'] . ' where name = ?  and not id = ?');
      if (!$stmt->execute([$f_name, $id])) {
        return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
      }
      if ($stmt->rowCount() > 0) {
        return array('code' => 0, 'message' => 'Department Already Exist', 'payload' => null);
      }
      $stmt = $this->db->prepare('update   ' . TABLES['dept'] . ' set  name = ? , faculty = ? , updated_by = ?, updated_on = ? where id = ?');
      $u_on = Carbon::now();
      if ($stmt->execute([$f_name, $f_, $f_updater, $u_on, $id])) {
        $this->all();
        return array('code' => 1, 'message' => 'Department Updated', 'payload' => $this->all);
      }
      return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
    } catch (Exception $e) {

      return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
    }
  }

  function remove($id)
  {
    try {
      $id = htmlspecialchars(strip_tags($id));

      $stmt = $this->db->prepare('delete  from ' . TABLES['dept'] . ' where  id = ?');
      if (!$stmt->execute([$id])) {
        return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
      }


      $this->all();
      return array('code' => 1, 'message' => 'Department Removed', 'payload' => $this->all);
    } catch (Exception $e) {

      return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
    }
  }







  function all()
  {
    $query = "Select 
          d.*, f.name as f_name
          from  " . TABLES['dept'] . " as d join " . TABLES['faculty'] . " as f on d.faculty = f.id
            ";

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
  function setMaxLoad($dept_id, $load, $semester, $prog)
  {
    $dept = htmlspecialchars(strip_tags($dept_id));
    $load = htmlspecialchars(strip_tags($load));
    $semester = htmlspecialchars(strip_tags($semester));
    $prog = htmlspecialchars(strip_tags($prog));
    $query = "select * from " . TABLES['dept_load'] . " where dept_id = ? and semester = ? and prog_id = ?";
//$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt =    $this->db->prepare($query);
    if ($stmt->execute([$dept, $semester, $prog])) {
      if ($stmt->rowCount() > 0) {
        $query = "Update " . TABLES['dept_load'] . " set unit_load = ? where dept_id = ? and semester = ? and prog_id = ? ";
      } else {
        $query = "insert into " . TABLES['dept_load'] . " set unit_load = ? , dept_id = ? , semester = ?, prog_id = ? ";
      }
      $stmt =    $this->db->prepare($query);
      if ($stmt->execute([$load,$dept, $semester,$prog])) {

        return array('code' => 1, 'message' => 'Max Credit Load set', 'payload' => $load);
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }

  function getMaxLoad($dept_id, $semester, $prog)
  {
    $dept = htmlspecialchars(strip_tags($dept_id));
    $semester = htmlspecialchars(strip_tags($semester));

    $query = "select * from " . TABLES['dept_load'] . " where dept_id = ? and semester = ? and prog_id = ?";

    $stmt =    $this->db->prepare($query);
    if ($stmt->execute([$dept, $semester,$prog])) {
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return array('code' => 1, 'message' => 'found', 'payload' => $row);
      } else {
        return array('code' => 0, 'message' => 'not found', 'payload' => null);
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }
  function getCourse($dept_id, $semester, $prog)
  {
    $dept = htmlspecialchars(strip_tags($dept_id));
    $semester = htmlspecialchars(strip_tags($semester));

   $query = "select dc.course_load_id as id, dc.dept_id, dc.semester,dc.prog_id, cl.course_load, cl.course_class, c.id as course_id, c.course_title, c.course_code from " . TABLES['dept_courses'] . " as dc  join ".
     TABLES['courses_load'] ." as cl on dc.course_load_id = cl.id join ". TABLES['courses']." as c on cl.course_id = c.id   where dc.dept_id = ? and dc.semester = ? and prog_id = ?";

    $stmt =    $this->db->prepare($query);
    
    if ($stmt->execute([$dept, $semester, $prog])) {
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array('code' => 1, 'message' => 'found', 'payload' => $row);
      } else {
        return array('code' => 1, 'message' => 'not found', 'payload' => array());
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }




  function getCourseAll($dept_id, $semester)
  {
    $dept = htmlspecialchars(strip_tags($dept_id));
    $semester = htmlspecialchars(strip_tags($semester));

    $query = "select  c.*  from  " . TABLES['dept_courses'] . " as dc  join ".
     TABLES['courses_load'] ." as cl on dc.course_load_id = cl.id join ". TABLES['courses']." as c on cl.course_id = c.id   where dc.dept_id = ? and dc.semester = ? ";

    $stmt =    $this->db->prepare($query);
    
    if ($stmt->execute([$dept, $semester])) {
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array('code' => 1, 'message' => 'found', 'payload' => $row);
      } else {
        return array('code' => 1, 'message' => 'not found', 'payload' => array());
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }
  function setCourse($dept_id, $list, $semester,$prog, $courses)
  {

    $dept = htmlspecialchars(strip_tags($dept_id));
   
    $semester = htmlspecialchars(strip_tags($semester));

    $query = "delete from " . TABLES['dept_courses'] . " where dept_id = ? and semester = ? and prog_id = ?";

    $stmt =    $this->db->prepare($query);
    if ($stmt->execute([$dept, $semester, $prog])) {
   
      $query = "insert into  " . TABLES['dept_courses'] . " set dept_id = ? , semester = ?, course_load_id =? , prog_id = ?";
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $stmt =    $this->db->prepare($query);
    if(!$stmt){
      echo "\nPDO::errorInfo():\n";
      print_r($this->db->errorInfo()); 
    }
     $course = array();
      $this->db->beginTransaction();
      foreach ($list as $app) {
        $app = htmlspecialchars(strip_tags($app));
        if(!$courses->checkWithLoad($app)){
          return array('code'=> 0, 'message'=> "course with id $app not found");

        }

        $course_id = $courses->all['course_id'];
        if(array_search($course_id,$course) !== false){
          $this->db->rollback();
          return array('code'=> 0, 'message'=> "duplicate registration for ".$courses->all['course_code']);
        }
       $course [] = $course_id;
        if (!$stmt->execute([$dept,$semester,$app, $prog])) {
          $this->db->rollback();
          return array('code'=> 0, 'message'=> "DB ERror");
        }
      }
     
        $this->db->commit();
        return array('code'=>1 , 'message'=> "Success");
    
    }
  }

  function getLectures($dept_id)
  {
    $this->external->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dept = htmlspecialchars(strip_tags($dept_id));


    $query = "select staffidno, fname,mname,lname, completename , cstudy, acronym  from " . TABLES['staff'] . " as s 
    join " . TABLES['staff_dept'] . " as sd on sd.cid = s.cid where
    
    acc_status = 1 order by s.cid ";

    $stmt =    $this->external->prepare($query);
    
    if ($stmt->execute([$dept])) {
      $staff = array();
      if ($stmt->rowCount() > 0) {
       while( $row = $stmt->fetch(PDO::FETCH_ASSOC)){
         $row['id'] = $row['staffidno'];
         $staff []  = $row;
       }
        return array('code' => 1, 'message' => 'found', 'payload' => $staff);
      } else {
        return array('code' => 1, 'message' => 'not found', 'payload' => array());
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }
  function checkLecturer($id, $dept_id)
  {
    $this->external->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    $dept = htmlspecialchars(strip_tags($dept_id));
    $id = htmlspecialchars(strip_tags($id));

    $query = "select * from " . TABLES['staff'] . "   where cid = ? and staffidno = ? and acc_status = 1";

    $stmt =    $this->external->prepare($query);
    
    if ($stmt->execute([$dept,$id])) {
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
       return true;
      } 
    }
    return false;
  }


  function getAssignedCourses($dept_id,$session,$course_id, $semester)
  {
   $dept = htmlspecialchars(strip_tags($dept_id));

    $session = htmlspecialchars(strip_tags($session));
    $course_id = htmlspecialchars(strip_tags($course_id));
    $semester = htmlspecialchars(strip_tags($semester));
  
    $query = "select lecturer_id from " . TABLES['course_assign'] . "  as ca where ca.dept_id = ? and
     ca.session_id = ? and  ca.semester = ? and ca.course_id = ?"  ;

    $stmt =    $this->db->prepare($query);
    $list = array();
    if ($stmt->execute([$dept,$session,$semester,$course_id])) {
      if ($stmt->rowCount() > 0) {
       while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
         $id [] = $r['lecturer_id'];
       }
     
       $id =  array_values($id);
          $in = "(";
       foreach($id as $key=>$value){
         if(array_key_last($id)==$key){
          $in .= "'$value'" .")";
         }else{
           $in .= "'$value'" ."," ;
         }
       
       }
       $query =   "select staffidno, staffidno as id,  fname,mname,lname, completename , cstudy, acronym  from " . TABLES['staff'] . " as s 
       join " . TABLES['staff_dept'] . " as sd on sd.cid = s.cid where staffidno  in $in;";
     // = "select staffidno as id, staffidno, completename from " . TABLES['staff'] . "   where staffidno in $in "  ;
       $stmt =    $this->external->prepare($query);
      
       if ($stmt->execute()) {
      $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
       }
        return array('code' => 1, 'message' => 'found', 'payload' => $row);
      } else {
        return array('code' => 1, 'message' => 'not found', 'payload' => array());
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }

  
  function setAssignedCourses($list,$dept,$s_dept_id,$session,$course_id, $semester,$id)
  {
    $dept = htmlspecialchars(strip_tags($dept));
    $sdept = htmlspecialchars(strip_tags($s_dept_id));
    $session = htmlspecialchars(strip_tags($session));
    $course_id = htmlspecialchars(strip_tags($course_id));
    $semester = htmlspecialchars(strip_tags($semester));
  
    $query = "delete from " . TABLES['course_assign'] . " where   semester = ? and course_id = ? and session_id = ?";

    $stmt =    $this->db->prepare($query);
    if ($stmt->execute([ $semester, $course_id, $session])) {
   
      $query = "insert into  " . TABLES['course_assign'] . " set  semester = ?, course_id =? , session_id = ?, lecturer_id = ?, dept_id = ?,  assigned_by = ?";
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $stmt =    $this->db->prepare($query);
    if(!$stmt){
      echo "\nPDO::errorInfo():\n";

      print_r($this->db->errorInfo()); 
    }
     $course = array();
      $this->db->beginTransaction();
      foreach ($list as $app) {
        $app = htmlspecialchars(strip_tags($app));
        if(!$this->checkLecturer($app,$s_dept_id)){
          return array('code'=> 0, 'message'=> "Lecturer with id $app not found");

        }

      
    
        if (!$stmt->execute([$semester,$course_id,$session,$app,$dept, $id ])) {
          $this->db->rollback();
          return array('code'=> 0, 'message'=> "DB ERror");
        }
      }
     
        $this->db->commit();
        return array('code'=>1 , 'message'=> "Success");
    
    }
  }



  function getAssignedCoursesSummary($dept_id,$session,$semester)
  {
    $dept = htmlspecialchars(strip_tags($dept_id));
    $session = htmlspecialchars(strip_tags($session));
  
    $semester = htmlspecialchars(strip_tags($semester));
  
    $query = "select  c.* , ca.lecturer_id from " . TABLES['course_assign'] . "  as ca  join ".TABLES['courses']." as c on c.id = ca.course_id  where 
     ca.session_id = ? and  ca.semester = ? and ca.dept_id = ?"  ;

    $stmt =    $this->db->prepare($query);
    
    if ($stmt->execute([$session,$semester,$dept])) {
      if ($stmt->rowCount() > 0) {
        $course = $stmt->fetchAll(PDO::FETCH_ASSOC);
      

       foreach($course as $key=>$value){

        $li [] = $value['lecturer_id'];
       }

       $id =  array_values($li);

       $in = "(";


    foreach($id as $key=>$value){

      if(array_key_last($id) == $key){

          $in .= "'$value'" .")";

      }else{

         $in .= "'$value'" ."," ;
      }      

    }

    $query = "select staffidno as id, staffidno, completename from " . TABLES['staff'] . "   where staffidno in $in "  ;
    $stmt =    $this->external->prepare($query);
   
    if ($stmt->execute()) {

         $l = $stmt->fetchAll(PDO::FETCH_ASSOC);

         foreach($course as $key=>$value){
            
               foreach($l as $k=>$v){
                 if($v['id'] == $value['lecturer_id']){
                   $value['name'] = $v['completename'];
                   break;
                 }
               }


          $list[$value['course_code']][] = $value;
         }   



    }

       return array('code' => 1, 'message' => 'found', 'payload' => $list);
      } else {
        return array('code' => 1, 'message' => 'not found', 'payload' => array());
      }
    }
    return array('code' => 0, 'message' => 'Error', 'payload' => null);
  }

 public function getPGCordinator($dept){
     
  $query = "select  short_name  from ".TABLES['dept']." where id = ?"  ;

 $stmt =    $this->db->prepare($query);
 
 if ($stmt->execute([$dept])) {

   $s =  $stmt->fetch(PDO::FETCH_ASSOC);
 }
  $this->external->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $query =  "select s.completename ,s.signature 
        from " . TABLES['staff'] . " as s
        
         join " . TABLES['staff_dept'] . " as sd on sd.cid = s.cid 
        
     
        where sd.acronym = ? and s.spgs = 2";

            $stmt = $this->external->prepare($query);
         
            $stmt->execute([$s['short_name']]);
         
            if ($stmt->rowCount() <= 0) {
                return false;
            }
          return   $row = $stmt->fetch(PDO::FETCH_ASSOC);


   


  }
}
