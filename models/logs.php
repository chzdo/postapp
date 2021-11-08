<?php 

class Logs{

private $db;
private $person;
    function __construct($db,$person=null){

    $this->db = $db;
    $this->person = $person;
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
   
    function eventLog($id,$event){

        try{
            $id = htmlspecialchars(strip_tags($id));
            $event = htmlspecialchars(strip_tags($event));
            $stmt = $this->db->prepare('insert into '.TABLES['event_logs'].' set  user_id = ? , event = ? , address = ?');
      
            if($stmt->execute([$id, $event, $_SERVER['SERVER_ADDR']])){
              return true;
            }
            return false;
          }
        catch(Exception $e){
      
          return false;
        }
    }
    function resultLog($changes,$course,$session){

      try{
      if($this->person == null) return false;
          $event = htmlspecialchars(strip_tags($changes));
          $stmt = $this->db->prepare('insert into '.TABLES['result_logs'].' set  user_id = ? , event = ? , address = ?, course_id =? , session = ?');
    
          if($stmt->execute([$this->person, $event, $_SERVER['SERVER_ADDR'], $course, $session])){
            return true;
          }
          return false;
        }
      catch(Exception $e){
    
        return false;
      }
  }
    function signinLogs($id){
       
        try{
            $id = htmlspecialchars(strip_tags($id));
           
            $stmt = $this->db->prepare('insert into '.TABLES['sign_logs'].' set  user_id = ? , address = ? ');                       
            if($stmt->execute([$id, $_SERVER['SERVER_ADDR']])){
              return true;
            }
            return false;
          }
        catch(Exception $e){
      
          return false;
        }
    

    }
    function getResultLogs($session,$course){
   
       
            $session = htmlspecialchars(strip_tags($session));
           
            $course = htmlspecialchars(strip_tags($course));
  
            $stmt = $this->db->prepare('select * from '.TABLES['result_logs'].' where   course_id =? and  session = ? order by id DESC');
      
            if($stmt->execute([$course, $session])){
               return  $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
          }
      
    


function getApprovalResultLogs($session,$course){
   
       
  $session = htmlspecialchars(strip_tags($session));
 
  $course = htmlspecialchars(strip_tags($course));

  $stmt = $this->db->prepare('select  arl.*  from  '.TABLES['approve_result'].' as ar 
  join '.TABLES['approve_result_log']. ' as arl on arl.approve_result_id = ar.id
  
  where   ar.course_id =? and  ar.session = ?  order by id  DESC');

  if($stmt->execute([$course, $session])){
     return  $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  return [];
}


}























?>