<?php

use Carbon\Carbon;

class Faculty{
    public $appd_id; 
    public  $id;
     public $status;
    public $all ;
    private $table ='session';
    public $db;
 
    function __construct($db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    function allActive(){
      

     
      
    $query = "Select 
       * 
       from  ".TABLES['faculty']." 
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

    public function check($id){
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from ".
              
              TABLES['faculty'] ."

              WHERE id = :id ";

            $stmt =  $this->db->prepare($q);

            $stmt->bindParam('id',$this->id);


            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count <= 0){
                return false;
            }
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
               $this->name = $row['name'];
               return true;
            
       
        
        
    }

    function add($name,$des,$creator){
 try{
      $f_name = htmlspecialchars(strip_tags($name));
      $f_des = htmlspecialchars(strip_tags($des));
      $f_creator = htmlspecialchars(strip_tags($creator));
      $stmt = $this->db->prepare('select * from '.TABLES['faculty'].' where name = ? ');
      if(!$stmt->execute([$f_name])){
        return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
      }
      if($stmt->rowCount() > 0){
        return array('code'=> 0, 'message'=> 'Faculty Already Exist', 'payload'=> null);
      }
      $stmt = $this->db->prepare('insert into   '.TABLES['faculty'].' set  name = ? , description = ? , created_by = ? ');

      if($stmt->execute([$f_name, $f_des, $f_creator])){
     
       $this->all();
        return array('code'=> 1, 'message'=> 'Faculty created', 'payload'=> $this->all);
      }
      return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
    }
  catch(Exception $e){

    return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
  }
}


function setStatus($id,$status,$user){
  try{
       $id = htmlspecialchars(strip_tags($id));
       $status = htmlspecialchars(strip_tags($status));
       $user = htmlspecialchars(strip_tags($user));
       $stmt = $this->db->prepare('update  '.TABLES['faculty'].' set  status = ? , updated_on = ? , updated_by = ? where id = ?');
 
       if($stmt->execute([$status,  Carbon::now(), $user , $id ])){
         $this->all();
         return array('code'=> 1, 'message'=> 'Faculty Status Changed', 'payload'=> $this->all);
       }
       return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
     }
   catch(Exception $e){
 
     return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
   }
 }


function Update($id, $name,$des,$updater){
  try{
    $id = htmlspecialchars(strip_tags($id));
       $f_name = htmlspecialchars(strip_tags($name));
       $f_des = htmlspecialchars(strip_tags($des));
       $f_updater = htmlspecialchars(strip_tags($updater));
       $stmt = $this->db->prepare('select * from '.TABLES['faculty'].' where name = ?  and not id = ?');
       if(!$stmt->execute([$f_name, $id])){
         return array('code'=> 0, 'message'=> 'DB Error 1', 'payload'=> null);
       }
       if($stmt->rowCount() > 0){
         return array('code'=> 0, 'message'=> 'Faculty Already Exist', 'payload'=> null);
       }
       $stmt = $this->db->prepare('update   '.TABLES['faculty'].' set  name = ? , description = ? , updated_by = ?, updated_on = ? where id = ?');
        $u_on = Carbon::now();
       if($stmt->execute([$f_name, $f_des, $f_updater, $u_on, $id])){
         $this->all();
         return array('code'=> 1, 'message'=> 'Faculty Updated', 'payload'=> $this->all);
       }
       return array('code'=> 0, 'message'=> 'DB Error', 'payload'=> null);
     }
   catch(Exception $e){
 
     return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
   }
 }

 function remove($id){
  try{
    $id = htmlspecialchars(strip_tags($id));
 
       $stmt = $this->db->prepare('delete  from '.TABLES['faculty'].' where  id = ?');
       if(!$stmt->execute([$id])){
         return array('code'=> 0, 'message'=> 'DB Error 1', 'payload'=> null);
       }
    
      
         $this->all();
         return array('code'=> 1, 'message'=> 'Faculty Removed', 'payload'=> $this->all);
  
     }
   catch(Exception $e){
 
     return array('code'=> 0, 'message'=> $e->getMessage(), 'payload'=> null);
   }
 }







function all(){
      

     
      
  $query = "Select 
     * 
     from  ".TABLES['faculty']." 
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

