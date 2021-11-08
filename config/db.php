<?php
// used to get mysql database connection

class Db{
 
    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "pgschool_new";
    private $username = "root";
    private $password = "";
 

    private $exhost = "localhost";
    private $exdb_name = "fulafia_web";
    private $exusername = "root";
    private $expassword = "";

    private $apphost = "localhost";
    private $appdb_name = "spgs";
    private $appusername = "root";
    private $apppassword = "";
 

    

    private $db_name_web = "journals_pgportal";
    private $username_web = "journals_spgs";
    private $password_web = "SPGS020##";

    
    private $exhost_web = "localhost";
    private $exdb_name_web = "pgschool_new";
    private $exusername_web = "root";
    private $expassword_web = "";
    public $conn;
 
    // get the database connection
    public function getConnection(){
 
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
   
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
       return $this->conn;
    }
    public function getExtConnection(){
 
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->exhost . ";dbname=" . $this->exdb_name, $this->exusername, $this->expassword);
   
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
       return $this->conn;
    }

    public function getAppConnection(){
 
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->apphost . ";dbname=" . $this->appdb_name, $this->appusername, $this->apppassword);
   
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
       return $this->conn;
    }
}


?>