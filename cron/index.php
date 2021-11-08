<?php
echo base64_decode($_GET['a']); return;
include_once '../vendor/autoload.php';
include('../config/core.php');
include('../config/db.php');
include('../models/cron.php');
include('../models/email.php');
$database = new Db();
$email = new email();
$db = $database->getConnection();
$cron = new Cron($db);

 $query = "Select 
       * 
       from  ".TABLES['email']." 
        where status = 0" ;

      $stmt = $db->prepare($query);
      
      if($stmt->execute()){
          
          if($stmt->rowCount() > 0){
              
               while( $row = $stmt->fetch(PDO::FETCH_ASSOC)){
                   
                  $cron->invitation(array(array("email"=>$row['student_id'],"html"=>$row['body'])),$row['id'],$email);
               }
               
             
              
          }
          
          
          
          
      }
     




















?>