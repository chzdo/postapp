<?php

use Carbon\Carbon;

class Clearance
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



    public function getActive($type)
    {



        if($type){
            $q = "SELECT *  from " .
    
                TABLES['clear_type'] ." order by id ASC"  ;
           }else{
            $q = "SELECT *  from " .
    
            TABLES['clear_type']." where type = 1  order by id ASC"  ;
           }
      

        $stmt =  $this->db->prepare($q);




        $stmt->execute();
       $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->all = $row;


        return true;
    }

    public function clear($info)
    {
        $id = htmlspecialchars(strip_tags($info['student_id']));
        $a_id = htmlspecialchars(strip_tags($info['appd_id']));
        $clear_type = htmlspecialchars(strip_tags($info['clear_type']));
        $session = htmlspecialchars(strip_tags($info['session']));
        $cleared_by = htmlspecialchars(strip_tags($info['cleared_by']));
        $q = "INSERT into  " .

            TABLES['student_clear'] . "(student_id,clearance_type,session,cleared_by) values (?,?,?,?)";

        $stmt =  $this->db->prepare($q);




        if ($stmt->execute([$id, $clear_type, $session, $cleared_by])) {
            return true;
        }
        return false;
    }

    function   VerifyClear($info)
    {
       $id = htmlspecialchars(strip_tags($info['student_id']));
       $a_id = htmlspecialchars(strip_tags($info['appd_id']));
        $clear_type = htmlspecialchars(strip_tags($info['clear_type']));
        $session = htmlspecialchars(strip_tags($info['session']));
      $q = "SELECT * from " .

            TABLES['student_clear'] . " as s_c join " . TABLES['clear_type'] . " as c_t on s_c.clearance_type = c_t.id 
            where (s_c.student_id = ? || s_c.student_id = ? ) and s_c.clearance_type = ? and s_c.session = ? ";

        $stmt =  $this->db->prepare($q);
        $stmt->execute([$id,$a_id, $clear_type, $session]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return true;
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->clr = $row;
        

        return false;
    }
     function VerifyQualification($info){
       
        $id = htmlspecialchars(strip_tags($info['student_id']));
        $clear_type = htmlspecialchars(strip_tags($info['clear_type']));
        $session = htmlspecialchars(strip_tags($info['session']));
      $q = "SELECT * from " .

            TABLES['student_clear'] . " as s_c
            where s_c.student_id = ? and s_c.clearance_type = ? and s_c.session = ? ";

        $stmt =  $this->db->prepare($q);
        $stmt->execute([$id, $clear_type-1, $session]);
        $count = $stmt->rowCount();

        if ($count > 0) {
            return true;
        }
      


        return false;

}

function   getClearance2($session)  {
       
    $s = htmlspecialchars(strip_tags($session));
    $q = "SELECT sc.student_id, concat(si.firstname, ' ', si.othername, ' ', si.surname) as name, count(sc.clearance_type) as clearNum ,  max(sc.clearance_type) as maxClear, f.name as faculty , d.name as dept , p.programme as prog
    from ". TABLES['student_clear'] ." as sc 
     join ". TABLES['students'] ." as stu on sc.student_id = stu.student_id 
     join ". TABLES['students_info'] ." as si on sc.student_id = si.student_id 
     join ". TABLES['faculty'] ." as f on f.id = stu.faculty_id 
     join ". TABLES['dept'] ." as d on d.id = stu.dept_id 
     join ". TABLES['prog'] ." as p on p.id = stu.prog_id
     
     where sc.session = ? group by sc.student_id
            ";

        $stmt =  $this->db->prepare($q);
        $stmt->execute([ $s]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->students = $row;
 

        return true;
    }
    function   getClearance($session)  {
       
    $s = htmlspecialchars(strip_tags($session));
    extract(TABLES);

    $this->portal->pullAll(['session'=>$s],"SELECT sc.student_id,
    
   
    sc.clearance_type as clear_type,
  
   pt.name as pay_name,
   pa.rrr,
   pa.date_paid,
   pa.pay_type,
   pa.amount,
   pa.status as pay_status,
   count(clearance_type) as maxClear
from $student_clear as sc 
left join $pay_type as pt on pt.clear_id = sc.clearance_type
left  join $pay as pa on pa.pay_type = pt.id and (pa.student_id = sc.student_id )
where sc.session = ? and sc.clearance_type < 3 group by sc.student_id order by maxClear ASC
       ");
      $c = $this->portal->result;

      if($c == null){
          return false;
      }
     foreach($c as $key=>$value){

        $this->app->pull(['appd_id'=>$value['student_id']],"select app.email, concat(firstname, ' ', othername, ' ' , surname) as name, dept_id, faculty_id, programme_id, options_id 
        
          from $app_application as app join $app_course on $app_course.appd_id = app.appd_id 
          join $app_names on $app_names.email = app.email where app.appd_id = ?
        
        ");

          $b = $this->app->result;
          
            $opt = array(
                "programme" => $b['programme_id'],
                "department" => $b['dept_id'],
            
            );
            $this->portal->pull($opt,"select f.name as faculty,d.name as department ,p.programme from $dept as d 
            join $faculty as f on f.id = d.faculty 
            join $prog as p on p.id = ? where d.id = ?");
           $h =  $this->portal->result;

          $clear[] = array_merge($value,$b,$h) ;
           
          
         

     }
     $this->students = @$clear;
     //var_dump($this->students);
return true;
    }
    function getFinalClearanceInfo($c){
      
   
        if($c){
        $q = "SELECT count(*) as TotalClear , max(id) as finalClear 
        from ". TABLES['clear_type'] ." 

        
              ";
        }else{
           
            $q = "SELECT count(*) as TotalClear , max(id) as finalClear 
            from ". TABLES['clear_type'] ." where clear_type = 1
            
                  ";
       
    }
          $stmt =  $this->db->prepare($q);
          $stmt->execute();
          $count = $stmt->rowCount();
  
          if ($count <= 0) {
              return false;
          }
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $this->clear_type = $row;
   
  
          return true;
    }
    function checkFinalClearanceInfo($info,$s){

     $this->getFinalClearanceInfo($s);
   $clear = $this->clear_type['TotalClear'];
        $q = "SELECT count(*) as TotalClear , max(clearance_type) as finalClear 
        from ". TABLES['student_clear'] ." where (student_id = ?  || student_id = ? )and session = ? 
              ";
               
          $stmt =  $this->db->prepare($q);
          $stmt->execute([$info['student_id'],$info['appd_id'],$info['session']]);
          $count = $stmt->rowCount();
  
          if ($count <= 0) {
              return false;
          }
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $v = $row['TotalClear'];

         return (int)$v == (int)$clear;
  
           }


    function getPayInfo($id){
        $q = "SELECT pt.* , ct.url
        from ". TABLES['clear_type'] ." as ct join ". TABLES['pay_type'] ."  as pt
        on pt.clear_id = ct.id
        
        where ct.id = ?  
              ";
               
          $stmt =  $this->db->prepare($q);
          $stmt->execute([$id]);
          $count = $stmt->rowCount();
  
          if ($count <= 0) {
              return [];
          }
        return  $row = $stmt->fetch(PDO::FETCH_ASSOC);
       

    }
}
