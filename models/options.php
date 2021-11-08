<?php

use Carbon\Carbon;

class Options
{
    public $appd_id;
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

    function allActive()
    {




        $query = "Select 
       o.*, d.name as d_name
       from  " . TABLES['options'] . " as o join ".  TABLES['dept'] ." as d on o.department = d.id
       ";

        $stmt = $this->db->prepare($query);


        $stmt->execute();
        $count = $stmt->rowCount();
        $count;
        if ($count > 0) {

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // assign values to object properties


            $this->all = $row;


            return true;
        }
        return false;
    }

    public function check($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
        $q = "SELECT * from " .

            TABLES['options'] . "

              WHERE id = :id ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['name'];
        return true;
    }

    function add($name, $dept, $creator)
    {
        try {
            $f_name = htmlspecialchars(strip_tags($name));
            $dept = htmlspecialchars(strip_tags($dept));
            $f_creator = htmlspecialchars(strip_tags($creator));
            $stmt = $this->db->prepare('select * from ' . TABLES['options'] . ' where name = ? ');
            if (!$stmt->execute([$f_name])) {
                return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
            }
            if ($stmt->rowCount() > 0) {
                return array('code' => 0, 'message' => 'Option Already Exist', 'payload' => null);
            }
            $stmt = $this->db->prepare('insert into   ' . TABLES['options'] . ' set  name = ? , department = ? , created_by = ? ');

            if ($stmt->execute([$f_name, $dept, $f_creator])) {

                $this->allActive();
                return array('code' => 1, 'message' => 'Options created', 'payload' => $this->all);
            }
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        } catch (Exception $e) {

            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
    }



    function Update($id, $name, $dept, $updater)
    {
        try {
            $id = htmlspecialchars(strip_tags($id));
            $f_name = htmlspecialchars(strip_tags($name));
            $f_des = htmlspecialchars(strip_tags($dept));
            $f_updater = htmlspecialchars(strip_tags($updater));
            $stmt = $this->db->prepare('select * from ' . TABLES['options'] . ' where name = ?  and not id = ?');
            if (!$stmt->execute([$f_name, $id])) {
                return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
            }
            if ($stmt->rowCount() > 0) {
                return array('code' => 0, 'message' => 'Options Already Exist', 'payload' => null);
            }
            $stmt = $this->db->prepare('update   ' . TABLES['options'] . ' set  name = ? , department = ? , updated_by = ?, updated_on = ? where id = ?');
            $u_on = Carbon::now();
            if ($stmt->execute([$f_name, $f_des, $f_updater, $u_on, $id])) {
                $this->allActive();
                return array('code' => 1, 'message' => 'Options Updated', 'payload' => $this->all);
            }
            return array('code' => 0, 'message' => 'DB Error', 'payload' => null);
        } catch (Exception $e) {

            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
    }

    function remove($id)
    {
        try {
            $id = htmlspecialchars(strip_tags($id));

            $stmt = $this->db->prepare('delete  from ' . TABLES['options'] . ' where  id = ?');
            
            if (!$stmt->execute([$id])) {
                return array('code' => 0, 'message' => 'DB Error 1', 'payload' => null);
            }


            $this->allActive();
            return array('code' => 1, 'message' => 'Options Removed', 'payload' => $this->all);
        } catch (Exception $e) {

            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
    }







    function allDept($dept)
    {

        $dept =  htmlspecialchars(strip_tags($dept));


        $query = "Select 
     * 
     from  " . TABLES['options'] . " 
       where department = ?";

        $stmt = $this->db->prepare($query);


        $stmt->execute([$dept]);
        $count = $stmt->rowCount();
        $count;
        if ($count > 0) {

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // assign values to object properties


            $this->all = $row;


            return true;
        }
        return false;
    }
}
