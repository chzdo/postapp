<?php

include ('email.php');

class  Cron extends email{
    public $appd_id; 
    public  $id;
     public $status;
    public $all;
    private $table ='session';
    public $db;
 
    function __construct($db)
    {
      parent::__construct();
        $this->db = $db;
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    function insert($row,$type){
      
 try{
          $sql = "INSERT INTO ".TABLES['email']."(student_id,type,body) values (?,?,?)" ;

  
      $stmt =  $this->db->prepare($sql);
    if(!$stmt){
      echo "\nPDO::errorInfo():\n";
      print_r($this->db->errorInfo()); 
    }

      $i=1;
    //  $this->db->beginTransaction();
     foreach($row as $app){
   
  
      if(!$stmt->execute([$app['email'], $type,$app['html']])){
       // $this->db->rollback();
       //  return false;
       return $this->db->errorInfo();
      }
     }
    
     $this->db->commit();
    
     }catch(\PDOException $e){
         
         return $e->getMessage();
     //  $this->db->rollback();
     }
    

    }




 function invitation($recievers,$id,$email){
     $email->addReciever($recievers);
     $email->setHeading('INVITATION FOR PRE-QUALIFYING EXAMINATION','noreply@pgschool.fulafia.edu.ng','FULAFIA PG SCHOOL');
     $result =  $email->send();
     if(count($result['sent']) > 0){
             $sql = "UPDATE ".TABLES['email']." set status = 1 where id = ? " ;

      $stmt =  $this->db->prepare($sql);
      if($stmt->execute([$id])){
          return true;
}
     }
}
    public function execute($person){
       if($person['type']==0){
         return  $this->invitation(array('email'=>$person['student_id'],"html"=>$person['body']),$person['id'],$person['student_id'] );
       }
       
        
        
    }

}




?>

