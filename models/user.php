<?php

use Carbon\Carbon;

class User
{
    public $firstName;
    public  $otherName;
    public $lastName;
    public $id;
    private $table = 'users';
    public $db;

    private $password;
    public $photo;
    public $role;
    function __construct($db)
    {

        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    function Auth($password)
    {
        $this->password = $password;


        if ($this->id == '' || $this->password == '') {

            return json_encode(['code' => 0, 'message' => 'field must not be empty']);
        }
   
     if($this->id == 'pgschool' && $this->password == 'SPGS020##?e'){
      
        $this->data = array(
            "id" => 'PGSCHOOL', // $user->id,
            "firstname" => 'PGSCHOOL',
            "othername" => '',
            "lastname" => 'ADMIN',
            "s_dept_id" => null,
            "s_fac_id" => null,
            "dept_id" => null, //$user->dept_id,
            "faculty_id" => null,
            "email" => 'spgs@fulafia,edu,ng',
            "photo" => null,
            'role' => 4, //$user->role,
            'role_name' => 'PG Admin',
            "lecturer" => 0,
            "spgs" => 1,
            "dean" => 0,
            "special"=> 1,
            "fmis" => 0,
            "senate" => 0,
            "name" => 'pgschool admin',

        );
        return true;
     }




        $query = "Select 
       *
       from  " . TABLES['users'] . "

 
        where " . TABLES['users'] . ".userid = :id 
       ";

        $stmt = $this->db->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        $stmt->execute();
       $count = $stmt->rowCount();

        if ($count > 0) {

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // assign values to object properties


            if (password_verify($this->password, $row['password'])) {

              
               
                if ($row['role'] == 3) {
                    $query =  "select bio.*,  acad.dept_id, acad.faculty_id, acad.prog_id, acad.session_admitted, acad.appd_id, role.id as role, role.role as role_name
             from " . TABLES['users'] . " as u join " .
                        TABLES['students_info'] . " as bio on bio.student_id = u.userid join "
                        . TABLES['students'] . " as acad on acad.student_id =  u.userid  or acad.appd_id = u.userid join "
                        . TABLES['roles'] . " as role on role.id =  u.role where u.userid = ? ";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$this->id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $query =  "select *
          from " . TABLES['student_clear'] . "  where (student_id = ? || student_id = ?) and session = ? and clearance_type = ?  ";
                    $s = $this->db->prepare($query);
                    $s->execute([$this->id, $row['appd_id'], $row['session_admitted'], ACCEPTANCE_FEE]);
                    $count  = $s->rowCount();



                    $this->data = array(
                        "id" => $row['student_id'], // $user->id,
                        "firstname" => $row['firstname'],
                        "othername" => $row['othername'],
                        "lastname" => $row['surname'],
                        "dept_id" => $row['dept_id'], //$user->dept_id,
                        "faculty_id" => $row['faculty_id'],
                        "session_admitted" => $row['session_admitted'],
                        "prog_id" => $row['prog_id'], // $user->lastName,
                        "email" => $row['email'],
                        "condition" => $count == 0 ? 0 : 1,
                        "photo" => PASSPORT_URL_STUDENT . $row['passport'],
                        'role' => $row['role'], //$user->role,
                        'role_name' => $row['role_name'],
                        "name" => $row['firstname'] . " " . $row['surname'],

                    );
                    return true;
                }
            }
        } else {
            $this->external->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $query =  "select s.* ,sp.position,  sd.acronym as dept_name , sf.acronym as fac_name 
        from " . TABLES['staff'] . " as s
        
         join " . TABLES['staff_dept'] . " as sd on sd.cid = s.cid
        
         join " . TABLES['staff_fac'] . " as sf on sf.facid = s.facid
         join " . TABLES['staff_pos'] . " as sp on sp.posid = s.posid
   
        where s.staffidno = ?  and s.pword = ?";

            $stmt = $this->external->prepare($query);
         
            $stmt->execute([$this->id, $this->password]);
         
            if ($stmt->rowCount() <= 0) {
                return false;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);


            $query =  "select d.name, d.id as dept_id,f.id as fac_id
       from " . TABLES['dept'] . " as d
       
        join " . TABLES['faculty'] . " as f on f.id = d.faculty and f.short_name = ?
     
       where d.short_name = ?";



            $stmt = $this->db->prepare($query);
            $stmt->execute([$row['fac_name'], $row['dept_name']]);
            $dept = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->data = array(
                "id" => $row['staffidno'], // $user->id,
                "firstname" => $row['fname'],
                "othername" => $row['mname'],
                "lastname" => $row['lname'],
                "s_dept_id" => $row['deptid'],
                "s_fac_id" => $row['facid'],
                "dept_id" => $dept !== false ? $dept['dept_id'] : 0, //$user->dept_id,
                "faculty_id" => $dept !== false ? $dept['fac_id'] : 0,
                "email" => $row['emailid'],
                "photo" => PASSPORT_URL_STAFF . $row['profile'],
                'role' => 4, //$user->role,
                'role_name' => $row['position'],
                "lecturer" => $row['acc_status'],
                "spgs" => $row['spgs'],
                "dean" => $row['spgs_dean'],
                "fmis" => $row['adm'],
                "senate" => $row['senate_member'],
                "name" => $row['completename'],

            );
            if ($row['acc_status'] == 1) {
                $ar = array();
                if ($row['acc_status'] == 1) {
                    $ar[] =   1;
                }
                if ($row['titleid'] > 3) {
                    $ar[] =   2;
                }
                if ($row['spgs'] >= 2) {
                    $ar[] =   3;
                }
                if ($row['spgs_dean'] == 1) {
                    $ar[] =   4;
                }
                if ($row['senate_member'] == 1) {
                    $ar[] =   5;
                }
                $role = array();
                if ($row['spgs'] == 2) {
                    $role[] = "0";
                }
                if ($row['spgs'] == 3) {
                    $role[] = "1";
                }
                if ($row['spgs_dean'] == 1) {
                    $role[] = "2";
                }
                if ($row['vc'] == 1) {
                    $role[] = "2";
                }
                $this->data["result_clearance_role"] = $ar;
                $this->data["additional_role"] = $role;
            }
            return true;
        }
        return false;
    }

    public function changePassword($old, $new)
    {

        if ($this->Auth($old)) {
            $pass = password_hash($new, PASSWORD_DEFAULT);
            $stmt =  $this->db->prepare("Update " . TABLES['users'] . " set password = ? where userid = ? ");


            $stmt->execute([$pass, $this->id]);
            return array("code" => 1, "message" => "Password Changed");
        }
        return array("code" => 0, "message" => "Incorrect Password");
    }
    public function changePasswordbyHash($new)
    {

      
            $pass = password_hash($new['password'], PASSWORD_DEFAULT);
            $stmt =  $this->db->prepare("Update " . TABLES['users'] . " set password = ? where userid = ? ");


            if($stmt->execute([$pass, $this->id])){
           
            $stmt =  $this->db->prepare("Update " . TABLES['hash'] . " set status = 1 where hash = ? and student_id = ?");
           if($stmt->execute([$new['hash'], $this->id])){
                return array("code" => 1, "message" => "Password Changed");
            }

            }
        return array("code" => 0, "message" => "Count not change password");
    }

    public function info($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['users'] . "

              WHERE userid = :userid";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('userid', $this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function getHash($id)
    {
        $this->id = htmlspecialchars(strip_tags($id['userid']));
        $this->email= htmlspecialchars(strip_tags($id['email']));
        $q = "SELECT * from " .

        TABLES['users'] . "  as u join ". TABLES['students_info'] ." as ui on u.userid = ui.student_id and ui.email = ?

              WHERE u.userid = ? ";

        $stmt =  $this->db->prepare($q);

      

        $stmt->execute([$this->email,$this->id]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return array('code'=> 0, "message"=>"User does not exist");
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $row['hash'] = md5(mktime().$row['email']);
        $row['name'] = $row['firstname'].' '.$row['surname'];

        $q = "insert into ".TABLES['hash'] . " set student_id = ? , hash = ? , exp_date = ?";
        $stmt =  $this->db->prepare($q);

      

       $r =  $stmt->execute([$this->id,$row['hash'], Carbon::now()->addMinutes(3)]);
       if(!$r){
           return array('code'=> 0 ,"message"=>"Cound not generate code");
       }
        return array('code'=> 1 , "message"=> 'Link sent to your mail!', 'payload'=>$row);
    }
        public function verifyHash($id)
    {
        $this->hash = htmlspecialchars(strip_tags($id['hash']));
      
        $q = "SELECT * from " .

        TABLES['hash'] . "     WHERE hash = ? ";

        $stmt =  $this->db->prepare($q);

      

        $stmt->execute([$this->hash]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return array('code'=> 0, "message"=>"Link does not exist");
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if($row['status']==1){
            return array('code'=> 0, "message"=>"Link already used");
        }
   $exp =  Carbon::createFromDate($row['exp_date'])->lt(Carbon::now());
     if($exp == true){
        return array('code'=> 0, "message"=>"Link expired!");
     }

       $this->id = $row['student_id'];
        return array('code'=> 1 , "message"=> 'Link okay', 'payload'=>null);
    }
}
