<?php 



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class email {

   private $email;
   private $html = [];
   private $mail;
   private $recievers = array();
   private $attach = [];
   
    function __construct()
    {
      //  $this->email = $email;
          $this->mail = new PHPMailer(true);
          $this->mail->SMTPDebug = 0;                      // Enable verbose debug output
          $this->mail->isSMTP();                                            // Send using SMTP
          $this->mail->Host       = EMAIL['HOST'];                    // Set the SMTP server to send through
          $this->mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $this->mail->Username   = EMAIL['USERNAME'];                     // SMTP username
          $this->mail->Password   = EMAIL['PASSWORD'];                              // SMTP password
          $this->mail->SMTPSecure = EMAIL['STMP'];         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
          $this->mail->Port       = EMAIL['PORT']; 
          $this->mail->isHTML(true);  
          
    }

    function addHTMLBody($html){
        $this->html = $html;
    }

    function addReciever($Reciever){
        $this->recievers = $Reciever;
    }
    function setHeading($subject,$from, $name){
        $this->mail->Subject = $subject;
        $this->mail->setFrom($from, $name);
    }

    function addAddress($email){
       // $this->recievers  = $email;
    }

    function send(){
        $result = array();
        $code = 0;
        foreach($this->recievers as $reciever){

      
          try{
            $this->mail->addAddress($reciever['email']);
            $this->mail->Body    = $reciever['html'];
            if(isset($reciever['attach'])){
                $this->mail->addAttachment($reciever['attach']['file'], $reciever['attach']['filename']);
            }
            $this->mail->send();
            $result ['sent'] [] = $reciever['email'];
            $code = 1;
      
        }catch(\Exception $e){
            $result ['failed'][]  = $reciever['email'];
          
          $code = 0;
        }
        $this->mail->clearAddresses();
        $this->mail->clearAttachments();
        
       // var_dump($code);
    
        }
      
        return $result;
    }
}





















?>