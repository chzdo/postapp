<?php



class Query {
    private $query;
    public $db;
 
    function __construct($db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    function insert($input,$table){
  
    $query = "insert into $table set ";
    $set = '';
    foreach($input as $key=>$value){
       if (array_key_last($input) == $key){
            $set .= $key.'= ? ';
        }else{
        $set .= $key.'= ? ,';
        }
    }

  $query .= $set;
          $new = $this->filter($input);
    $new = array_values($new);
    
   $stmt = $this->db->prepare($query);

    if ($stmt->execute($new)){
        return true;
    }
    return false;
    }


function filter($input){
    $filter = [];
    foreach($input as $key=>$value){
        $filter[] = strip_tags(htmlentities($value));
    }
    return $filter;
}



function pull($inputs,$query){
    $this->result = NULL;
        $stmt = $this->db->prepare($query);
        $new = $this->filter($inputs);
        $new = array_values($new);
        $stmt->execute($new);
        if($stmt->rowCount() <= 0){
        return false;

}
  $this->result = $stmt->fetch(PDO::FETCH_ASSOC);

  return true;
    

}
function pullAll($inputs,$query){
    $this->result = NULL;
    $stmt = $this->db->prepare($query);
    $new = $this->filter($inputs);
    $new = array_values($new);
    $stmt->execute($new);
    if($stmt->rowCount() <= 0){
    return false;

}
$this->result = $stmt->fetchAll(PDO::FETCH_ASSOC);

return true;


}
function update($inputs,$condition, $conj, $table ){
    $query = "update  $table set ";
    $set = '';
    foreach($inputs as $key=>$value){
       if (array_key_last($inputs) == $key){
            $set .= $key.'= ? ';
        }else{
        $set .= $key.'= ? ,';
        }
    }

  $query .= $set;
  if(count($condition) > 0){
      $con = ' where ';
      $i = 0;
    foreach($condition as $key=>$value){
        if (array_key_last($condition) == $key){
             $con .= $key.'= ? ';
         }else{
         $con .= $key.'= ? '.$conj[$i].' ';
         $i++;
         }
     }
   $query .= $con;
  }
          $new = $this->filter($inputs);
          $new2 = $this->filter($condition);
    $new = array_values($new);
    $new2 = array_values($new2);
   $newA = array_merge($new,$new2);
   $stmt = $this->db->prepare($query);

    if ($stmt->execute($newA)){
        return true;
    }
    return false;

}

function delete($condition, $conj, $table ){
    $query = "delete from  $table  ";
   
  if(count($condition) > 0){
      $con = ' where ';
      $i = 0;
    foreach($condition as $key=>$value){
        if (array_key_last($condition) == $key){
             $con .= $key.'= ? ';
         }else{
         $con .= $key.'= ? '.$conj[$i]." ";
         $i++;
         }
     }
   $query .= $con;
  }
      
          $new2 = $this->filter($condition);

    $new2 = array_values($new2);

   $stmt = $this->db->prepare($query);

    if ($stmt->execute($new2)){
        return true;
    }
    return false;

}
  
}
