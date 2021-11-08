<?php

use Carbon\Carbon;

class Material
{
    public $appd_id;
    public  $id;
    public $status;
    public $all;
    private $table = 'session';
    private $filetype = ['application/pdf', 'application/vnd.ms-access', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',  'application/vnd.ms-powerpoint', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    public $db;
    public $file;
    function __construct($db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

   function create($post){

    $course = htmlentities(strip_tags($post['course_id']));
   
    $staff_id = htmlentities(strip_tags($post['staff_id']));
    $title = htmlentities(strip_tags($post['title']));
   
   
    $id = Carbon::now()->timestamp;

 

      $file = explode('.',$this->file['name']);
      
      $location = 'material/'.$this->id.$id.'.'.$file[1];
      move_uploaded_file($this->file['tmp_name'],$location);
      $query = "INSERT INTO ".TABLES['material'] ."  
      set course_id = ? , staff_id = ? , file = ? , title = ?";

      $stmt = $this->db->prepare($query);

      if($stmt->execute([$course,$staff_id,'/student/courses/material/create/'.$location, $title])){
          return true;
      }

      return false;
   }


   function delete($post){

    $id = htmlentities(strip_tags($post['material_id']));
   
    $query = "Delete from ".TABLES['material'] ."  
      where  id = ? ";


      $stmt = $this->db->prepare($query);

      if($stmt->execute([$id])){
          return true;
      }

      return false;
   }
 
   function getMaterial($post){

    $id = htmlentities(strip_tags($post));
    $query = "select * from   ".TABLES['material'] ." where  course_id = ?  order by id DESC  ";
      $stmt = $this->db->prepare($query);

   
      if($stmt->execute([$id])){
         $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $row;
      }

      return false;
   }



   function setFile($file){
       $this->file = $file;
   }
   function isFile(){
     return  in_array($this->file['type'],$this->filetype);
  
    }
    public function check($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['material'] . "

              WHERE id = :id ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->info  = $row;
      return true;
    }

  



   

  







  


  


   
}
