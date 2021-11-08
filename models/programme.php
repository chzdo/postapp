<?php



class Programme{
    public $appd_id; 
    public  $id;
     public $status;
    public $all;
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
       from  ".TABLES['prog']." 
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

    public function verify($id){
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from ".
              
              TABLES['prog'] ."

              WHERE id = :id ";

            $stmt =  $this->db->prepare($q);

            $stmt->bindParam('id',$this->id);


            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count <= 0){
                return false;
            }
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
               $this->name = $row['short_name'];
               $this->long_name = $row['programme'];
               return true;
            
       
        
        
    }


    
    public function verifywithDepartment($id,$d){
      $this->id = htmlspecialchars(strip_tags($id));
      $q = "SELECT * from ".
            
            TABLES['prog'] ." as p join ". TABLES['dept_prog'] ." as dp on dp.prog_id = p.id and dp.dept_id = $d

            WHERE p.id = :id ";

          $stmt =  $this->db->prepare($q);

          $stmt->bindParam('id',$this->id);


          $stmt->execute();
          $count = $stmt->rowCount();

          if ($count <= 0){
              return false;
          }
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
             $this->name = $row['short_name'];
             $this->long_name = $row['programme'];
             return true;
          
     
      
      
  }
}




?>

