<?php
//namespace models;
use \Carbon\Carbon;

include 'cron.php';
class N extends Cron{

    function __construct($fetch,$db){
        parent::__construct($db);
       $this->fetch = $fetch;
    }

    function notify($type,$info){
        $this->info = $info;
        switch($type){
            case EMAIL_CODE_LOGIN:  return  $this->login(); break;
            case EMAIL_CODE_RESET:  return  $this->reset(); break;
            case EMAIL_CODE_ACCEPT:  return  $this->accept(); break;
            case EMAIL_CODE_PAY:  return  $this->pay(); break;
            case EMAIL_CODE_MATRIC:  return  $this->matric(); break;


            case EMAIL_CODE_APP_CREATE:  return  $this->app_create(); break;
            case EMAIL_CODE_APP_PASSWORD:  return  $this->app_password(); break;
            case EMAIL_CODE_APP_CREATE_COURSE:  return  $this->app_create_course(); break;
            case EMAIL_CODE_APP_REFEREE_CREATE:  return  $this->app_referee_create(); break;
            case EMAIL_CODE_APP_SUBMIT:  return  $this->app_submit(); break;
            case EMAIL_CODE_APP_REFEREE_SUBMIT_STUDENT:  return  $this->app_ref_submit_student(); break;
            case EMAIL_CODE_APP_REFEREE_SUBMIT_REF:  return  $this->app_ref_submit_ref(); break;
         
        }
       

    }


   private function login(){
       $v =  Carbon::now();
        $response = $this->fetch->request('GET', URL.'/api/emailview/login.php', [
            'query' => ['name' => htmlspecialchars(strip_tags($this->info['name'])), "ip"=>$_SERVER['REMOTE_ADDR'], "time"=> "$v"  ]
        ]);
        $data= $response->getBody()->getContents();

       
        if($this->info['cron']){
          return $this->insert(array(array("email"=>$this->info['email'],"html"=>$data)), $this->info['type']);
      
        }
        $this->addReciever(array(array('email'=>$this->info['email'],"html"=>$data)));
        $this->setHeading('Login Alert',NOREPLY,PG);
        return $this->send();
    }
         
    private     function sendNotify(){
            if($this->info['cron']){
                return $this->insert(array(array("email"=>$this->info['email'],"html"=>$this->data)), $this->info['type']);
            
              }
              $this->addReciever(array(array('email'=>$this->info['email'],"html"=>$this->data)));
              $this->setHeading($this->heading,NOREPLY,PG);
              return $this->send();
        }

        private  function reset(){
         
         $response = $this->fetch->request('GET', URL.'/api/emailview/reset.php', [
             'query' => ['name' => htmlspecialchars(strip_tags($this->info['name'])), "code"=>$this->info['code']  ]
         ]);
         $this->data= $response->getBody()->getContents();
          $this->heading = "Password Reset";
          $this->sendNotify();
        
        
     }
     private function accept(){
         
        $response = $this->fetch->request('GET', URL.'/api/emailview/stu_accept.php', [
            'query' => ['name' => htmlspecialchars(strip_tags($this->info['name'])), "id"=>$this->info['id']  ]
        ]);
        $this->data= $response->getBody()->getContents();
      
         $this->heading = "Acceptance Notice";
         $this->sendNotify();
       
       
    }

    private  function pay(){
         
        $response = $this->fetch->request('GET', URL.'/api/emailview/payment.php', [
            'query' => $this->info
        ]);
        $this->data= $response->getBody()->getContents();
      
         $this->heading = "Payment Notice";
         $this->sendNotify();
       
       
    }

    private function matric(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/matric_gen.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Matric Number generation";
         $this->sendNotify();
        
    }

    private function app_create(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_create.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Account Verification";
         $this->sendNotify();
        
    }
    private function app_password(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_password.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Password Recovery OTP";
         $this->sendNotify();
        
    }
    private function app_create_course(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_create_course.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Application ID Created";
         $this->sendNotify();
        
    }
    private function app_referee_create(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_referee_create.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Referee Notice";
         $this->sendNotify();
        
    }
    private function app_submit(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_submit.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Application Submitted";
         $this->sendNotify();
        
    }
    private function app_ref_submit_student(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_ref_submit_student.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Referee Form Submission Notice";
         $this->sendNotify();
        
    }
    private function app_ref_submit_ref(){
        $response = $this->fetch->request('GET', URL.'/api/emailview/app_ref_submit_ref.php', [
            'query' => $this->info   ]);
        
         $this->data= $response->getBody()->getContents();
         $this->heading = "Referee Form Submission Notice";
         $this->sendNotify();
        
    }
    
}