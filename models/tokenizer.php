<?php

use Carbon\Carbon;

class Tokens
{
    public $name;
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



    public function Verify($token)
    {
        $this->id = htmlspecialchars(strip_tags($token));

        $q = "SELECT * from " .
        
            TABLES['token'] . "

              WHERE token = ? and date(exp_date) >= ?  ";
    
            $stmt =  $this->db->prepare($q);

            $stmt->execute([$this->id, date('y-m-d')]);
    




        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->token_value = $row;
        return true;
    }

    public function isReason($info,$reason)
    {
  
         $people = json_decode($this->token_value['for_student'], true);
   
         if(!($reason == $this->token_value['reason'])){
             return false;
         }
      
         if(!($info['session'] == $this->token_value['session'])){
            return false;
        }

       if(array_search($info['id'],$people) !== false){
           return true;
       }
         return false;
        
    }
    
    public function verifyReason($info)
    {
  
    
         if(!($info['reason'] == $this->token_value['reason'])){
             return false;
         }
         if(!($info['session'] == $this->token_value['session'])){
            return false;
        }
      
      return true;
        
    }
}
