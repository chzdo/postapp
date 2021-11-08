<?php



class Application{
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
  function checkExist($id,$session){

   $query = http_build_query(array(
      'appd_id'=>$id,
      'session' => $session
    ));

    $response = file_get_contents(API_APPLICANT.$query,false);
    $user = json_decode($response,true);
    if($user->code == 1){
   return $user;
    }
return false;
 }
    function setAdmission($post,$session)
    {
      $error = [];
     $APIsql = '';
     $APIsqlrole  = array();
     foreach($post as $key =>$pos){
      
      if (!($pos['status'] == 1 || $pos['status']  == 0)) {
           $error[] = array('id'=>$pos['appd_id'], 'reason'=>'invalid admission status');
           unset($post[$key]);
           continue;
       
      }
     // $user = ($this->checkExist($pos['appd_id'],$session));
    //  if($user === false){
    //   $error[] = array('id'=>$post['appd_id'], 'reason'=>'invalid admission status');
    //   unset($post[$key]);
     //  continue;
    //}
      if ($this->checkAdmission($pos['appd_id'], $session)){
         $error[] = array('id'=>$pos['appd_id'], 'reason'=>'Admission set already');
         unset($post[$key]);
         continue;
      }

      
      $APIsql .=  "when appd_id = '".$pos['appd_id']."'  then '".($pos['status'] == 1 ? 3 : 4)."'  "; 
                          
      $APIsqlrole []= "'".$pos['appd_id']."'";
    
    
     // ;

     }
     //var_dump($user);

      $q = implode(',', $APIsqlrole);

     return  array('code'=>1 , 'message'=>'success', 'payload'=>array('error'=>$error, 'Api'=>array('setQuery'=>$APIsql, 'whereQuery'=>$q), 'filteredApp'=> $post));
     
    }

    function saveAdmissionList($list,$session,$faculty,$dept){
      $sql = "INSERT INTO ".TABLES['admission']."(appd_id,department,faculty,session,name,adm_status) values (?,?,?,?,?,?)" ;
    //  $qpart = array_fill(0,count($list),"(?,?,?,?,?,?)");
   //   $sql .= implode(',', $qpart);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $stmt =  $this->db->prepare($sql);
    if(!$stmt){
      echo "\nPDO::errorInfo():\n";
      print_r($this->db->errorInfo()); 
    }

      $i=1;
      $this->db->beginTransaction();
     foreach($list as $app){
      
       $d = $this->getID($app['dept'],$dept);
       $f = $this->getID($app['faculty'],$faculty);

      if(!$stmt->execute([$app['appd_id'], $d, $f, $session, $app['name'], $app['status']])){
        $this->db->rollback();
         return false;
      }
     }
     try{
     $this->db->commit();
     return true;
     }catch(PDOException $e){
       $this->db->rollback();
     }
    
   
      
      
    }
 
    function setBulkAdmission($app)
    {
 
      $list [][]= array();
      $arrayhead = $app[5];
      $i = 0;
      foreach ($arrayhead as $head) {
       if ($i>6) break;
        if ($head != HEAD[$i]) {
        return (Object)array('code'=>0, 'message'=>'invalid file');
        }
        $i++;
      }
      $file = array_splice($app,5);
      
$i=0;
      foreach($file as $student){
      
           $list [$i]['appd_id'] = $student['B'];
           $list [$i]['name'] = $student['C'];
           $list [$i]['email'] = $student['D'];
           $list [$i]['faculty'] = $student['E'];
           $list [$i]['dept'] = $student['F'];
           $list [$i]['status'] = $student['G'];
           $i++;
  
      }

      return (Object)array('code'=>1, 'message'=>'Success', 'payload'=>$list);

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

    public function checkAdmission($id,$session){
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from ".
              
              TABLES['admission'] ."

              WHERE appd_id = :id and session = :session ";

            $stmt =  $this->db->prepare($q);

            $stmt->bindParam('id',$this->id);
            $stmt->bindParam('session',$session);

            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count <= 0){
                return false;
            }
               
               return true;
            
       
        
        
    }

    function getAdmissionList($id){
      $session = htmlspecialchars(strip_tags($id));
      $q = "SELECT * from ".
            
            TABLES['admission'] ."

            WHERE session = :id ";

          $stmt =  $this->db->prepare($q);

          $stmt->bindParam('id',$session);
          $stmt->execute();
          $count = $stmt->rowCount();

          if ($count <= 0){
              return false;
          }
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
              $this->all [] = $row;
            }
            
             return true;

    }
    function getID($id, $obj)
    {

          $v = array_filter($obj, function ($var) use ($id) {
            return ($var['name'] == $id);
        });
    
     
        foreach ($v as $key) {
    
            if (isset($key['id'])) {
               return $key['id'];
                break;
            }
        }
    
        return null;
    }
    
}



?>

