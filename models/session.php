<?php

use Carbon\Carbon;

class Session{
    public $name; 
    public  $id;
     public $status;
    public $all;
    private $table ='session';
    public $db;
 
    function __construct($db)
    {
        $this->db = $db;
    }


   
    function allActive(){
      

     
      
    $query = "Select 
       * 
       from  ".TABLES['session']." 
        where status = 1" ;

      $stmt = $this->db->prepare($query);
     
     
       $stmt->execute();
      $count = $stmt->rowCount();
$count;
      if ($count > 0){
       
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        // assign values to object properties
           $this->all = $row;
           return true;
    
      }
      return false;
    }

    public function check($id, $type= 0){
   
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from ".
              
              TABLES['session'] ;

          $q .=  $type==0?  "  WHERE id = :id and status = 1" : "  WHERE id = :id " ;

            $stmt =  $this->db->prepare($q);

            $stmt->bindParam('id',$this->id);

   
            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count <= 0){
                return false;
            }
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->info  = $row;
               $this->name = $row['session'];
               return true;
            
       
        
        
    }
    public function checkAdmission($id){
      $this->id = htmlspecialchars(strip_tags($id));
      $q = "SELECT distinct  sess.* from ".
              
      TABLES['admission'] ." as adm , ".TABLES['session']
      ." as sess where adm.session = sess.id and sess.status = 1 and sess.id = :id "

      ;

          $stmt =  $this->db->prepare($q);

          $stmt->bindParam('id',$this->id);


          $stmt->execute();
          $count = $stmt->rowCount();

          if ($count <= 0){
              return false;
          }
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
             $this->name = $row['session'];
             return true;
          
     
      
      
  }
  public function checkCurrentAdmission($id){
    $this->id = htmlspecialchars(strip_tags($id));
    $q = "SELECT distinct  sess.* , adm.status as app_state, adm.dead_line from ".
           
    TABLES['session_admission'] ." as adm , ".TABLES['session']
    ." as sess where adm.session = sess.id  and adm.session = :id "

    ;

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id',$this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0){
            return false;
        }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
           $this->name = $row['session'];
           $this->info = $row;
           return true;
        
   
    
    
}


    function getAdmissionSession(){
     $q = "SELECT distinct  sess.* from ".
              
              TABLES['admission'] ." as adm , ".TABLES['session']
              ." as sess where adm.session = sess.id and sess.status = 1 "

              ;
              $stmt =  $this->db->prepare($q);
              $stmt->execute();
              $count = $stmt->rowCount();
              if ($count <= 0){
                return false;
            }
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->all = $row;
            return true;
    }




function getCurrentAdmissionSession(){
  $q = "SELECT distinct  sess.* , adm.dead_line , adm.status as app_status from ".
           
           TABLES['session_admission'] ." as adm , ".TABLES['session']
           ." as sess where adm.session = sess.id and sess.status = 1 "

           ;
           $stmt =  $this->db->prepare($q);
           $stmt->execute();
           $count = $stmt->rowCount();
           if ($count <= 0){
             $this->all = [];
             return false;
         }
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
         $this->all = $row;
         return true;
 }

  function isCurrent($session){
  $q = "SELECT distinct  sess.* from ".
           
    TABLES['session_current'] ." as adm , ".TABLES['session']
    ." as sess where adm.session_id = sess.id and sess.status = 1 and adm.session_id = $session "

    ;
    
    $stmt =  $this->db->prepare($q);
    $stmt->execute();
    $count = $stmt->rowCount();
    if ($count <= 0){
      return false;
  }
  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $this->all = $row;
  return true;
  }
  function getCurrentSession(){
    $q = "SELECT distinct  sess.* from ".
            
     TABLES['session_current'] ." as adm , ".TABLES['session']
     ." as sess where adm.session_id = sess.id and sess.status = 1  "
 
     ;
     
     $stmt =  $this->db->prepare($q);
     $stmt->execute();
     $count = $stmt->rowCount();
     if ($count <= 0){
       return false;
   }
   $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $this->all = $row;
   return true;
   }
  function isAdminActive($session,$semester){
    $q = "SELECT distinct  sess.* , adm.semester as semester , adm.status as level from ".
           
    TABLES['session_current'] ." as adm , ".TABLES['session']
    ." as sess where adm.session_id = sess.id and sess.status = 1 and  adm.status > 1 adm.session_id = $session"

    ;
    $stmt =  $this->db->prepare($q);
    $stmt->execute();
    $count = $stmt->rowCount();
  
  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $this->all = $row;
    if(count($row) > 0){
     return false;
    }

  return true;
 
  }
  function isClosed($session){
    $q = "SELECT distinct  sess.*  from ".
           
    TABLES['session_current'] ." as adm , ".TABLES['session']
    ." as sess where adm.session_id = sess.id and sess.status = 1 and  adm.status > 0 and adm.session_id = $session "

    ;
    $stmt =  $this->db->prepare($q);
    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count <= 0){
      return false;
  }


  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $this->all = $row;


  return true;
  }

  function close($id,$status){
    $id = htmlspecialchars(strip_tags($id));
    $status = htmlspecialchars(strip_tags($status));

    $q = "update  ".
           
    TABLES['session_current'] ." 
    set status = ? where session_id = ? " 
    ;
    $stmt =  $this->db->prepare($q);
   $res = $stmt->execute([$status, $id]);
  
    if ($res){
      $this->all();
      $msg = $status == 0? 'Session Opened' : 'Session Closed';
      return array('code'=> 1, 'message'=> $msg, 'payload'=> $this->all);
    
  }

  return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
  }



  function add($name, $creator)
  {
      try {
          $f_name = htmlspecialchars(strip_tags($name));
         
          $f_creator = htmlspecialchars(strip_tags($creator));
          $stmt = $this->db->prepare('select * from ' . TABLES['session'] . ' where session = ? ');
          if (!$stmt->execute([$f_name])) {
              return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
          }
          if ($stmt->rowCount() > 0) {
              return array('code' => 0, 'message' => 'Session Already Exist', 'payload' => null);
          }
          $stmt = $this->db->prepare('insert into   ' . TABLES['session'] . ' set  session = ? , created_by = ? ');

          if ($stmt->execute([$f_name, $f_creator])) {

              $this->all();
              return array('code' => 1, 'message' => 'Session created', 'payload' => $this->all);
          }
          return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
      } catch (Exception $e) {

          return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
      }
  }



  function Update($id, $name, $updater)
  {
      try {
          $id = htmlspecialchars(strip_tags($id));
          $f_name = htmlspecialchars(strip_tags($name));
      
          $f_updater = htmlspecialchars(strip_tags($updater));
          $stmt = $this->db->prepare('select * from ' . TABLES['session'] . ' where session = ?  and not id = ?');
          if (!$stmt->execute([$f_name, $id])) {
              return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
          }
          if ($stmt->rowCount() > 0) {
              return array('code' => 0, 'message' => 'Session Already Exist', 'payload' => null);
          }
          $stmt = $this->db->prepare('update   ' . TABLES['session'] . ' set  session = ? , updated_by = ?, updated_on = ? where id = ?');
          $u_on = Carbon::now();
          if ($stmt->execute([$f_name, $f_updater, $u_on, $id])) {
              $this->all();
              return array('code' => 1, 'message' => 'Session Updated', 'payload' => $this->all);
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

          $stmt = $this->db->prepare('delete  from ' . TABLES['session'] . ' where  id = ?');
          
          if (!$stmt->execute([$id])) {
              return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
          }


          $this->all();
          return array('code' => 1, 'message' => 'Session Removed', 'payload' => $this->all);
      } catch (Exception $e) {

          return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
      }
  }

  function setStatus($id,$status,$user){
    try{
         $id = htmlspecialchars(strip_tags($id));
         $status = htmlspecialchars(strip_tags($status));
         $user = htmlspecialchars(strip_tags($user));
         $stmt = $this->db->prepare('update  '.TABLES['session'].' set  status = ? , updated_on = ? , updated_by = ? where id = ?');
   
         if($stmt->execute([$status,  Carbon::now(), $user , $id ])){
           $this->all();
           return array('code'=> 1, 'message'=> 'Session Status Changed', 'payload'=> $this->all);
         }
         return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
       }
     catch(Exception $e){
   
       return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
     }
   }
  

   function setCurrentSession($id){
    try{
         $id = htmlspecialchars(strip_tags($id));
         $stmt = $this->db->prepare('select * from ' . TABLES['session_current']);
         if (!$stmt->execute()) {
             return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
         }
         if ($stmt->rowCount() > 0) {
          $stmt = $this->db->prepare('update  '.TABLES['session_current'].' set  session_id  = 1 , semester = ?,  date = ? , status = 0');
   
         }else{
          $stmt = $this->db->prepare('insert into   '.TABLES['session_current'].' set  session_id = 1 , semester = ?, date = ?  ,  status = 0');
   
         }
         
         if($stmt->execute([$id,   Carbon::now() ])){
           $this->all();
           return array('code'=> 1, 'message'=> 'Current Session Set', 'payload'=> $this->all);
         }
         return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
       }
     catch(Exception $e){
   
       return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
     }
   }
  

   function setAdmissionSession($id){
    try{
         $id = htmlspecialchars(strip_tags($id));
         $stmt = $this->db->prepare('select * from ' . TABLES['session_admission']);
         if (!$stmt->execute()) {

             return array('code' => 0, 'message' => 'DB Error1', 'payload' => null);

         }
         if ($stmt->rowCount() > 0) {

          $stmt = $this->db->prepare('update  '.TABLES['session_admission'].' set  session = ? , status = 1');
   
         }else{

          $stmt = $this->db->prepare('insert into   '.TABLES['session_admission'].' set  session = ? , state = 1 ');
   
         }
         
         if($stmt->execute([$id ])){
           $this->all();
           return array('code'=> 1, 'message'=> 'Admission Session Set', 'payload'=> $this->all);
         }
         return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
       }
     catch(Exception $e){
   
       return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
     }
   }

   function updateAdmissionSession($info){
    try{
         $session = htmlspecialchars(strip_tags($info['session']));
         $status = htmlspecialchars(strip_tags($info['status']));
         $deadline = htmlspecialchars(strip_tags($info['dead_line']));
         $deadline = ($deadline == "")? null : $deadline;
       
          $stmt = $this->db->prepare('update  '.TABLES['session_admission'].' set  session = ? , dead_line = ? , status = ? where session = ?');
   
     
         
         if($stmt->execute([$session, $deadline ,  $status, $session])){
          
           return array('code'=> 1, 'message'=> 'Admission Session Set', 'payload'=> null);
         }
         return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
       }
     catch(Exception $e){
   
       return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
     }
   }

  function all(){
           
     
          
           
 $query = "Select 
       se.* , sa.session as session_adm, sc.session_id as session_cur, sc.semester ,sc.status as cur_status
       from  ".TABLES['session']." as se left join ". TABLES['session_current']." as sc on se.id = sc.session_id 
       left join ".TABLES['session_admission'] ." as sa on se.id = sa.session
         " ;
  
      $stmt = $this->db->prepare($query);
     
     
       $stmt->execute();
      $count = $stmt->rowCount();
  $count;
      if ($count > 0){
       
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
        // assign values to object properties
          
         
                   $this->all = $row;
              
          
           return true;
    
      }
      return false;
    }

}

?>

