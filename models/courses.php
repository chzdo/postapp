<?php

use Carbon\Carbon;

class Courses
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

    function all()
    {
        $query = "Select 
       c.id as course_id, c.course_code, c.course_title,cl.course_class, c.status as course_status, c.created_by as course_creator,
       c.updated_on as course_update, cl.id as load_id, cl.course_load, cl.created_by as load_creator, cl.updated_on as load_update,
       cl.status as load_status
       from  " . TABLES['courses'] . " as c left join " . TABLES['courses_load'] . " as cl on c.id = cl.course_id  order by cl.course_id ASC
       ";

        $stmt = $this->db->prepare($query);


        $stmt->execute();
        $count = $stmt->rowCount();
        $count;
        if ($count > 0) {

            $id = '';
            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              
                if ($row['course_code'] != $id) {
                    $id = $row['course_code'];
                    ++$i;
                }
                $this->row[$i]['id'] = $row['course_id'];
                $this->row[$i]['course_code'] = $row['course_code'];
                $this->row[$i]['course_title'] = $row['course_title'];
                $this->row[$i]['status'] = $row['course_status'];
                $this->row[$i]['created_by'] = $row['course_creator'];
                $this->row[$i]['updated_on'] = $row['course_update'];
                if ($row['load_id'] != null) {
                    $this->row[$i]['variation'][] = array("id" => $row['load_id'],"course_class" => $row['course_class'], "course_load" => $row['course_load'], "status" =>
                    $row['load_status'], "created_by" => $row['load_creator'], "updated_on" => $row['load_update']);
                } else {
                    $this->row[$i]['variation'] = [];
                }
            }
            $this->all = $this->row;
            // assign values to object properties





            return true;
        }
        return false;
    }

    public function check($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['courses'] . "

              WHERE id = :id ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['course_code'];
        $this->title = $row['course_title'];
        return true;
    }

    public function checkWithLoad($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['courses_load'] . " as cl join ." . TABLES['courses'] . "  as c on cl.course_id = c.id 

              WHERE cl.id = :id  ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['course_code'];
        $this->all = $row;
        return true;
    }
    function add($c_code, $c_title, $dept_id,  $creator)
    {
        try {
            $c_code = htmlspecialchars(strip_tags($c_code));
            $c_title = htmlspecialchars(strip_tags($c_title));
            $f_creator = htmlspecialchars(strip_tags($creator));
            $dept_id = htmlspecialchars(strip_tags($dept_id));
            $stmt = $this->db->prepare('select * from ' . TABLES['courses'] . ' where course_code = ? ');
            if (!$stmt->execute([$c_code])) {
                return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
            }
            if ($stmt->rowCount() > 0) {
                return array('code' => 0, 'message' => 'Course Already Exist', 'payload' => null);
            }
            $stmt = $this->db->prepare('insert into   ' . TABLES['courses'] . ' set  course_code = ? , course_title = ? , created_by = ? , dept_id = ?');

            if ($stmt->execute([$c_code, $c_title, $f_creator, $dept_id])) {

                $this->all();
                return array('code' => 1, 'message' => 'Course created', 'payload' => $this->all);
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
            $stmt = $this->db->prepare('update  ' . TABLES['courses'] . ' set  status = ? , updated_on = ? , updated_by = ? where id = ?');

            if ($stmt->execute([$status,  Carbon::now(), $user, $id])) {
                $this->all();
                return array('code' => 1, 'message' => 'Course Status Changed', 'payload' => $this->all);
            }
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        } catch (Exception $e) {

            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
    }

    function setStatusWithLoad($id, $status, $user)
    {
        try {
            $id = htmlspecialchars(strip_tags($id));
            $status = htmlspecialchars(strip_tags($status));
            $user = htmlspecialchars(strip_tags($user));
            $stmt = $this->db->prepare('update  ' . TABLES['courses_load'] . ' set  status = ? , updated_on = ? , updated_by = ? where id = ?');

            if ($stmt->execute([$status,  Carbon::now(), $user, $id])) {
                $this->all();
                return array('code' => 1, 'message' => 'Course with load Status Changed', 'payload' => $this->all);
            }
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        } catch (Exception $e) {

            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
    }

    function addCourseWithLoad($c_c, $c_id, $c_load, $creator)
    {
        try {
            $c_c = htmlspecialchars(strip_tags($c_c));
            $c_id = htmlspecialchars(strip_tags($c_id));
            $c_load = htmlspecialchars(strip_tags($c_load));
            $creator = htmlspecialchars(strip_tags($creator));
            $stmt = $this->db->prepare('select * from ' . TABLES['courses_load'] . ' where course_id = ?  and course_load = ?');
            if (!$stmt->execute([$c_id, $c_load])) {
                return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
            }
            if ($stmt->rowCount() > 0) {
                return array('code' => 0, 'message' => 'Course with load Already Exist', 'payload' => null);
            }
            $stmt = $this->db->prepare('insert into   ' . TABLES['courses_load'] . ' set  course_id = ? , course_load = ?  , created_by = ? , course_class = ? ');

            if ($stmt->execute([$c_id, $c_load, $creator, $c_c])) {

                $this->all();
                return array('code' => 1, 'message' => 'Course with load created', 'payload' => $this->all);
            }
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        } catch (Exception $e) {

            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
    }



    function allwithLoad()
    {
        $query = "Select 
           cour.course_code , cour.course_title, cour_load.course_load, cour_load.id 
            from  " . TABLES['courses_load'] . " as cour_load  join 
           " . TABLES['courses'] . " as cour on cour.id = cour_load.course_id where
           cour.status = 1;

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


    function allActivewithLoad()
    {
        $query = "Select 
           cour.course_code , cour.course_title, cour_load.course_class, cour_load.course_load, cour_load.id 
            from  " . TABLES['courses_load'] . " as cour_load  join 
           " . TABLES['courses'] . " as cour on cour.id = cour_load.course_id where
           cour.status = 1 and cour_load.status = 1;

          ";

        $stmt = $this->db->prepare($query);


        if (!$stmt->execute()) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }

        $count = $stmt->rowCount();
        if ($count > 0) {
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array('code' => 1, 'message' => 'Found', 'payload' => $row);
        } else {
            return array('code' => 0, 'message' => 'not Found', 'payload' => array());
        }
    }

           function  getCourses($staff,$semester,$session){
                      $staff = htmlspecialchars(strip_tags($staff));
                      $semester = htmlspecialchars(strip_tags($semester));
                      $session = htmlspecialchars(strip_tags($session));

                      $query = "select * from ".TABLES['course_assign']. " as ac join 
                    

                      ".TABLES['courses']." as c on c.id = ac.course_id where ac.lecturer_id = ? and semester = ? 
                      and session_id = ?";

                    $stmt =   $this->db->prepare($query);

                    if ($stmt->execute([$staff,$semester,$session])){

                return    $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    return false;


             }
    function getResult($session, $semester)
    {
        $query = "Select distinct c.dept_id, d.name as dept_name, d.short_name , f.id as faculty_id , f.name as faculty_name,  ca.lecturer_id, c.course_code, c.course_title ,  ar.status as approval_status,
        rc.*, res.ca_1 , res.ca_2 , res.exam ,(res.ca_1 + res.ca_2 + res.exam) as score, if((res.ca_1 + res.ca_2 + res.exam) > 70,'A',
        if((res.ca_1 + res.ca_2 + res.exam) > 60, 'B', if((res.ca_1 + res.ca_2 + res.exam) > 50, 'C',
        if((res.ca_1 + res.ca_2 + res.exam) > 45, 'D', if((res.ca_1 + res.ca_2 + res.exam) > 40 , 'E',
        'F'))))) as Grade, sess.session , ca.lecturer_id
         from  " . TABLES['register_course'] . " as rc left  join 
        " . TABLES['result_course'] . " as res on res.reg_id = rc.id 
        join
        ". TABLES['session'] ." as sess on sess.id = rc.session  join
        ". TABLES['courses'] ." as c on c.id = rc.course_id  left join
        ". TABLES['dept'] ." as d on d.id = c.dept_id  left join
        ". TABLES['faculty'] ." as f on f.id = d.faculty  left join
        ". TABLES['approve_result']." as ar on ar.course_id = rc.course_id and ar.session = ? and ar.semester = ? 
        join
        ". TABLES['course_assign']." as ca on ca.course_id = rc.course_id and ca.session_id = ? and ca.semester = ? 
        
        
        where
      
        rc.course_id = ? and rc.session = ? and rc.semester = ? and rc.status = 1;
      order by student_id DESC
       ";

        $stmt = $this->db->prepare($query);


        if (!$stmt->execute([$session, $semester,$session, $semester, $this->id, $session, $semester])) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }

        $count = $stmt->rowCount();
        if ($count > 0) {
            $my = array();
      while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          $my['course_title']  = $row['course_title'];
          $my['course_code']  = $row['course_code'];
          $my['dept_id']  = $row['dept_id'];
          $my['dept_name']  = $row['dept_name'];
          $my['short_name']  = $row['short_name'];
          $my['faculty_name']  = $row['faculty_name'];
          $my['faculty_id']  = $row['faculty_id'];
          $my['session']  = $row['session'];
          $my['course_id']  = $this->id;
          $my['approval_status'] = $row['approval_status'];   
        
          $my['result'][$row['id']]= $row;

      }
      $query = "select lecturer_id from " . TABLES['course_assign'] . "   
      where  course_id = ? and session_id = ? and semester = ?  "  ;
      $stmt =    $this->db->prepare($query);
      $stmt->execute([$this->id,$session, $semester]);


      $lec = $stmt->fetchAll(PDO::FETCH_ASSOC);

$in = "(";

foreach($lec as $key=>$value){

    if(array_key_last($lec)==$key){
        $in.= "'".$value['lecturer_id']."')";
    }else{
        $in.= "'".$value['lecturer_id']."' ,";
    }
}
     $query = "select completename as name, staffidno, signature from " . TABLES['staff'] . "   where staffidno in  $in"  ;
      $stmt =    $this->external->prepare($query);
      $stmt->execute();
      $lec = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
      foreach($lec as $key=>$value){
        $my['lecturer'][$value['staffidno']] = array("sign"=>SIGNATURE_URL.$value['signature'],"name"=>$value['name']);
      
      }
    
      $my['result'] = array_values($my['result']);

      if((int)$my['approval_status']> 1){

     $query = "select completename as name, signature from " . TABLES['staff'] . "  as s 
      
      join ". TABLES['staff_dept'] ." as sd on sd.acronym = ?  

      where s.cid = sd.cid and spgs = 2 
      
      "  ;
   $stmt =    $this->external->prepare($query);
      $stmt->execute([$my['short_name']]);
      $hod = $stmt->fetch(PDO::FETCH_ASSOC);
      if($hod != false){
      $my['HOD'] = $hod['name'];
      $my['HOD_SIGN'] = SIGNATURE_URL.$hod['signature'];
      }
    }
   
    if($my['approval_status']> 2){
      
        $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
        where spgs = 3 
        
        "  ;
        $stmt =    $this->external->prepare($query);
           $stmt->execute();
           $dean = $stmt->fetch(PDO::FETCH_ASSOC);
           if($dean != false){
           $my['FACULTY'] = $dean['name'];
           $my['FACULTY_SIGN'] = SIGNATURE_URL.$dean['signature'];
           }
         }
          if($my['approval_status']> 2){
        $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
        where spgs_dean = 3 
        
        "  ;
        $stmt =    $this->external->prepare($query);
           $stmt->execute();
           $dean = $stmt->fetch(PDO::FETCH_ASSOC);
           if($dean != false){
           $my['FACULTY'] = $dean['name'];
           $my['FACULTY_SIGN'] = SIGNATURE_URL.$dean['signature'];
           }
         }
      $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
      where spgs_dean = 1 
      
      "  ;
      if($my['approval_status']> 3){
   $stmt =    $this->external->prepare($query);
      $stmt->execute();
      $dean = $stmt->fetch(PDO::FETCH_ASSOC);
      if($dean != false){
      $my['DEAN'] = $dean['name'];
      $my['DEAN_SIGN'] = SIGNATURE_URL.$dean['signature'];
      }
    }
    if($my['approval_status']> 4){
        $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
        where vc = 1 
        
        "  ;
        $stmt =    $this->external->prepare($query);
           $stmt->execute();
           $dean = $stmt->fetch(PDO::FETCH_ASSOC);
           if($dean != false){
           $my['SENATE'] = $dean['name'];
           $my['SENATE_SIGN'] = SIGNATURE_URL.$dean['signature'];
           }
         }
   
            return array('code' => 1, 'message' => 'Found', 'payload' => $my);
        } else {
            return array('code' => 1, 'message' => 'not Found', 'payload' => array());
        }
    }


    function   checkCourseDept($dept, $fac)
    {
        $query = "Select 
 * from " . TABLES['courses'] . " as c  join " . TABLES['dept'] . " as d on d.id = c.dept_id and d.faculty = ? where c.id = ? and c.dept_id = ?
";
        $stmt = $this->db->prepare($query);

        if (!$stmt->execute([$fac, $this->id, $dept])) {
            return false;
        }
        $count = $stmt->rowCount();
        if ($count > 0) {
            return true;
        }
        return false;
    }

      function isLecturer($semester,$session,$id){
        $query = "Select 
      *
      from  " . TABLES['course_assign'] . " where course_id = ? and session_id = ? and semester = ?  and lecturer_id = ?
    ";
    $stmt = $this->db->prepare($query);

    if (!$stmt->execute([$this->id, $session, $semester,$id])) {
        return false;
    }
    $count = $stmt->rowCount();
    if ($count == 0 ) {
        return false;

    }
    return true;
      }

    function approveResult($session, $semester, $staff, $code, $comment)
    {


        $query = "Select 
           id, status 
         from  " . TABLES['approve_result'] . " where course_id = ? and session = ? and semester = ? 
       ";


        $stmt = $this->db->prepare($query);

        if (!$stmt->execute([$this->id, $session, $semester])) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }


        $count = $stmt->rowCount();
        if ($count == 0 &&  $code > 1) {
            return array('code' => 1, 'message' => 'Unauthorized', 'payload' => null);
        }


        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $array = null;


        if ($count != 0) {
            if ((int)$row['status'] != (int)$code - 1  &&  $code != 0) {
                return array('code' => 0, 'message' => 'code x10f: Course cannot be approved by you', 'payload' => null);
            }


            $query = "update  " . TABLES['approve_result'] . "
        set course_id = ? , session = ? , semester = ? , status = ? where id = ? ";

            $array = [$this->id, $session, $semester, $code, $row['id']];
        } else {
            $query = "insert into  " . TABLES['approve_result'] . "
          set course_id = ? , session = ? , semester = ? , status = ? ";

            $array = [$this->id, $session, $semester, $code];
        }

        $stmt = $this->db->prepare($query);
        if (!$stmt->execute($array)) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }


        $query = "Select 
        id, status 
          from  " . TABLES['approve_result'] . " where course_id = ? and session = ? and semester = ? 
        ";

        $stmt = $this->db->prepare($query);

        if (!$stmt->execute([$this->id, $session, $semester])) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "insert into  " . TABLES['approve_result_log'] . "
        set comment = ? , approve_result_id = ? , staff_id = ?
      ";

        $stmt = $this->db->prepare($query);

        if (!$stmt->execute([$comment, $row['id'], $staff])) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }

        return array('code' => 1, 'message' => 'Approved', 'payload' => null);
    }



    function getApproveResult($session, $semester,  $dept,$faculty, $code, $staff=null)
    {
        if($code  == 1){
            $query = " Select 
            c.course_code , c.course_title , c.id , ar.status 
            from  " . TABLES['course_assign'] . " as ac  left join 
       
          " . TABLES['approve_result'] . " as ar on ar.course_id = ac.course_id and ar.session = ? and ar.semester = ?
            
            join " . TABLES['courses'] . " as c on ac.course_id = c.id 
       
         
                 
            where   ac.session_id = ? and ac.semester = ? and ac.lecturer_id = ?
          ";
          $exe = array($session,$semester,$session,$semester,$staff);
        }else
    if($code  == 2|| $code == 0 ){
          $query = " Select 
         c.course_code , c.course_title , ar.status , c.id 
         from  " . TABLES['approve_result'] . " as ar
         
         join " . TABLES['courses'] . " as c on ar.course_id = c.id and c.dept_id = ?
    
      
              
         where   ar.session = ? and ar.semester = ? and (ar.status >= ?)
       ";
       $exe = array($dept,$session,$semester,(int) $code - 1);
    }else if ($code == 3){
        $query = " Select 
        c.course_code , c.course_title , c.id, ar.status
        from  " . TABLES['approve_result'] . " as ar
        
        join " . TABLES['courses'] . " as c on ar.course_id = c.id 
      
        join " . TABLES['dept'] . " as dp on dp.id = c.dept_id and faculty = ?
             
        where   ar.session = ? and ar.semester = ? and (ar.status >= ?)
      ";
      $exe = array($faculty,$session,$semester,(int) $code - 1);
    }else{
        $query = " Select 
        c.course_code , c.course_title , c.id, ar.status
        from  " . TABLES['approve_result'] . " as ar
        
        join " . TABLES['courses'] . " as c on ar.course_id = c.id 
      
            
        where   ar.session = ? and ar.semester = ? and (ar.status >= ?)";
        $exe = array($session,$semester,(int) $code - 1);
 
    }

        $stmt = $this->db->prepare($query);




        if (!$stmt->execute($exe)) {
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        }


        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

 
        $array = null;


        
        return array('code' => 1, 'message' => 'Approved', 'payload' => $row);
    }
}
