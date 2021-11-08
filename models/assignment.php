<?php

use Carbon\Carbon;

class Assignment
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
    $session = htmlentities(strip_tags($post['session_id']));
    $question = htmlentities(strip_tags($post['question']));
    $file_type = htmlentities(strip_tags($post['file_type']));
    $dead_line = htmlentities(strip_tags($post['dead_line']));
    $staff_id = htmlentities(strip_tags($post['staff_id']));
    $id = Carbon::now()->timestamp;

 $location = '';

    if ($this->file != null){
        $file = explode('.',$this->file['name']);
        if(!is_dir('assignment/')){
            mkdir('assignment/');
        }
        $location = 'assignment/'.$this->id.implode("",explode("/",$id)).'.'.$file[1];
        move_uploaded_file($this->file['tmp_name'],$location);
    }
    $query = "INSERT INTO ".TABLES['assignment'] ."  
      set course_id = ? , session_id = ? , question = ?, file_type = ? , dead_line = ? , staff_id = ?, id = ? , file = ?";



      $stmt = $this->db->prepare($query);
     $location =  $location  == '' ? '' : '/student/courses/assignment/create/'.$location;
      if($stmt->execute([$course,$session,$question,$file_type,$dead_line,$staff_id,$id, $location ])){
          return true;
      }

      return false;
   }


   function delete($post){

    $id = htmlentities(strip_tags($post['ass_id']));
   
    $query = "Delete from ".TABLES['assignment'] ."  
      where  id = ? ";


      $stmt = $this->db->prepare($query);

      if($stmt->execute([$id])){
          return true;
      }

      return false;
   }
   function submit($post){

    $id = htmlentities(strip_tags($post));
    $query = "select * from   ".TABLES['assignment_submit'] ."  
      where ass_id = ? and student_id = ?   ";
      $stmt = $this->db->prepare($query);

     $stmt->execute([$this->id,$id]);
        if($stmt->rowCount() > 0){
             return false;
        }
    

    $query = "insert into  ".TABLES['assignment_submit'] ."  
      set ass_id = ? , student_id = ? , file = ?  ";
     

      $file = explode('.',$this->file['name']);
      
      $location = 'assignment/'.$this->id.implode("",explode("/",$id)).'.'.$file[1];
      move_uploaded_file($this->file['tmp_name'],$location);
      $stmt = $this->db->prepare($query);

      if($stmt->execute([$this->id,$id,'/student/courses/assignment/submit/'.$location])){
          return true;
      }

      return false;
   }

   function getStudentAssignment($student,$course, $session){

    $student = htmlentities(strip_tags($student));
    $course = htmlentities(strip_tags($course));
    $query = "select *, ass.id as a_id from   ".TABLES['assignment'] ." as ass left
    join ". TABLES['assignment_submit']." as asst on ass.id = asst.ass_id and asst.student_id = ?
      where course_id = ? and session_id = ?  ";
      $stmt = $this->db->prepare($query);

   
      if($stmt->execute([$student,$course,$session])){
         $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $row;
      }

      return false;
   }

   function getCourseAssignment(){


    $query = "select ass.*, asst.file,asst.date_submitted,asst.student_id from   ".TABLES['assignment'] ." as ass left
    join ". TABLES['assignment_submit']." as asst on ass.id = asst.ass_id 
      where ass_id = ?   ";
      $stmt = $this->db->prepare($query);

   
      if($stmt->execute([$this->id])){
          $my =  array();
       while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
           $my[] = $row;
       }
         return $my;
      }

      return false;
   }

     function getCourseAssignmentList($c,$s){


    $query = "select * from   ".TABLES['assignment'] ." 
   
      where course_id = ? and session_id = ?  ";
      $stmt = $this->db->prepare($query);

   
      if($stmt->execute([$c,$s])){
          $my = array();
       while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
           $my[] = $row;
       }
         return $my;
      }

      return false;
   }
   function isExpired(){

  $now = Carbon::now();
 $dead = Carbon::createFromTimeString($this->info['dead_line']);
 if($now->gt($dead)){
     return true;
 }
 return false;
   }

   function setFile($file){
       $this->file = $file;
   }
   function isFile(){
    if($this->info['file_type'] != $this->file['type']){
        return false;
    }
   return true;
    }
    public function check($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['assignment'] . "

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