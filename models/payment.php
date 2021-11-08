<?php

use Carbon\Carbon;
use GuzzleHttp\Client;


class Payment
{
    public $name;
    public  $id;
    public $status;
    public $all;
    public $pay_info;
    public $db;


    function __construct($db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public function getPayments($type,$app)
    {
             
    
        $q = "SELECT p.* , pt.name as purpose , s.session as session  from " .

            TABLES['pay']."  as p join  ". TABLES['pay_type'] ." as pt on pt.id = p.pay_type  join "
            .TABLES['session']. " as s on s.id = p.session where student_id = ? || student_id = ? " ;
     
        $stmt =  $this->db->prepare($q);




        $stmt->execute([$type,$app]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return [];
        }
       return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
    }

    public function getPaymentsAll($type)
    {
             
    
        $q = "SELECT p.* , prog.short_name as prog_name, ct.id as clear_type , d.name as dept_name, f.name as fac_name, concat(st.firstname,' ', st.othername,' ', st.surname) as name, pt.name as purpose , s.session as session  from " .

            TABLES['pay']."  as p join
            ". TABLES['pay_type'] ." as pt on pt.id = p.pay_type  join "
            .TABLES['clear_type'] ." as ct on ct.id = pt.clear_id join "
                     .TABLES['students']." as std  on std.student_id = p.student_id or  std.appd_id = p.student_id  join "
            .TABLES['students_info']." as st  on st.student_id = std.student_id or st.student_id = std.appd_id join "
   
            .TABLES['dept']." as d on d.id = std.dept_id join "
            .TABLES['prog']." as prog on prog.id = std.prog_id join "
            .TABLES['faculty']." as f on f.id = std.faculty_id join "
            .TABLES['session']. " as s  on s.id = p.session where p.session = ?" ;
     
        $stmt =  $this->db->prepare($q);




        $stmt->execute([$type]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return [];
        }
       return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
    }
    public function getPaymentType($type)
    {
             
       if($type){
        $q = "SELECT *  from " .

            TABLES['pay_type']   ;
       }else{
        $q = "SELECT *  from " .

        TABLES['pay_type']." where type = 1"  ;
       }
        $stmt =  $this->db->prepare($q);




        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return [];
        }
       return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
    }


    public function checkAcceptPayInfo($id,$ses, $type)
    {
       $sid = htmlspecialchars(strip_tags($id));
     
        $se = htmlspecialchars(strip_tags($ses));
       $type = htmlspecialchars(strip_tags($type));
       $q = "SELECT pay.* , ct.* , ses.session as sessionName  from " .

            TABLES['pay'] . " as pay join ". TABLES['session'] ." as ses on pay.session = ses.id 
            join ".TABLES['pay_type'] ." as pt on pay.pay_type = pt.id  
            
            join ".TABLES['clear_type'] ." as ct on pt.clear_id = ct.id 
            WHERE (pay.student_id  = ? ) and pay.pay_type = ? and pay.session = ?  ";

        $stmt =  $this->db->prepare($q);




        $stmt->execute([$sid,   $type, $se]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->pay_info = $row;
       $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
        where bursar = 1 
        
        "  ;
        $this->external->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $stmt =    $this->external->prepare($query);
        $stmt->execute();
        $dean = $stmt->fetch(PDO::FETCH_ASSOC);
       
        $this->pay_info['bursar'] = $dean == false? '': $dean['name'];
        $this->pay_info['bursar_sign'] = $dean == false? '': SIGNATURE_URL.$dean['signature'];
        $this->pay_info['RRR'] = $row['rrr'];
        $this->pay_info['orderId'] = $row['order_id'];
        $new_hash_string = MERCHANTID . $this->pay_info['RRR'] . APIKEY;
        $this->pay_info['hash'] = hash('sha512', $new_hash_string);
        unset($this->pay_info['rrr']);
        unset($this->pay_info['order_id']);
        return true;
    }





    public function checkPayInfo($id, $aid, $ses, $type)
    {
       $sid = htmlspecialchars(strip_tags($id));
       $aid = htmlspecialchars(strip_tags($aid));
        $se = htmlspecialchars(strip_tags($ses));
       $type = htmlspecialchars(strip_tags($type));
       $q = "SELECT pay.* , ct.* , ses.session as sessionName, concat( stu.firstname, ' ', stu.othername , ' ', stu.surname) as name  from " .

            TABLES['pay'] . " as pay join ". TABLES['session'] ." as ses on pay.session = ses.id 
            join ". TABLES['students'] ." as s on (s.student_id = pay.student_id || s.appd_id = pay.student_id ) 
            join ". TABLES['students_info'] ." as stu on (s.student_id = stu.student_id || s.appd_id = stu.student_id) 
            join ".TABLES['pay_type'] ." as pt on pay.pay_type = pt.id  
            
            join ".TABLES['clear_type'] ." as ct on pt.clear_id = ct.id 
            WHERE (pay.student_id  = ? || pay.student_id = ?) and pay.pay_type = ? and pay.session = ?  ";

        $stmt =  $this->db->prepare($q);




        $stmt->execute([$sid, $aid,  $type, $se]);
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->pay_info = $row;
       $query = "select completename as name, signature from " . TABLES['staff'] . "  
    
        where bursar = 1 
        
        "  ;
        $this->external->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $stmt =    $this->external->prepare($query);
        $stmt->execute();
        $dean = $stmt->fetch(PDO::FETCH_ASSOC);
       
        $this->pay_info['bursar'] = $dean == false? '': $dean['name'];
        $this->pay_info['bursar_sign'] = $dean == false? '': SIGNATURE_URL.$dean['signature'];
        $this->pay_info['RRR'] = $row['rrr'];
        $this->pay_info['orderId'] = $row['order_id'];
        $new_hash_string = MERCHANTID . $this->pay_info['RRR'] . APIKEY;
        $this->pay_info['hash'] = hash('sha512', $new_hash_string);
        unset($this->pay_info['rrr']);
        unset($this->pay_info['order_id']);
        return true;
    }
    public function check($id)
    {
        $this->id = htmlspecialchars(strip_tags($id));
       $q = "SELECT * from " .

            TABLES['pay_type'] . " as p_type join " . TABLES['clear_type'] . " as c_type on 

              p_type.clear_id = c_type.id

              WHERE p_type.id = :id ";

        $stmt =  $this->db->prepare($q);

        $stmt->bindParam('id', $this->id);


        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count <= 0) {
            return false;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->info = $row;
        return true;
    }


    public function setPaymentInfo($info)
    {
        //   merchantId, serviceTypeId, orderId, hash, payerName, payerEmail, amt, responseurl
        $timestamp =  Carbon::now()->timestamp;
        $this->pay_info['serviceTypeId'] = SERVICETYPEID;
        $this->pay_info['amount']  = $this->info['amount'];

        $this->pay_info['orderId']  = $timestamp;
        $this->pay_info['payerName']  = $info['name'];
        $this->pay_info['payerEmail']  = $info['email'];
        $this->pay_info['payerPhone']  = $info['phone'];

        $hash_string = MERCHANTID . SERVICETYPEID .    $this->pay_info['orderId'] . $this->pay_info['amount'] . APIKEY;
        $hash = hash('sha512', $hash_string);
        $this->pay_info['hash']  = $hash;

     
    }

    public function getRRR()
    {



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => GATEWAYURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($this->pay_info),
            CURLOPT_HTTPHEADER => array(
                "Authorization: remitaConsumerKey=" . MERCHANTID . ",remitaConsumerToken=" . $this->pay_info['hash'],
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $json_response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $jsonData = substr($json_response, 7, -1);
        $response = json_decode($jsonData, true);
  
        if ($response == null) {
            return array('code' => 0, 'message' => 'Could not generate RRR try again', 'payload' => null);
        }
        if ($response['statuscode'] == '025') {
            $rrr = trim($response['RRR']);
            $new_hash_string = MERCHANTID . $rrr . APIKEY;
            $new_hash = hash('sha512', $new_hash_string);
            $this->pay_info['RRR'] = $response['RRR'];
            $this->pay_info['hash'] = $new_hash;
            return array('code' => 1, 'message' => $response['status'], 'payload' => null);
        }
        return array('code' => 0, 'message' => $response['status'], 'payload' => null);
    }


    function save($sid, $pid, $ses, $payer)
    {

        $q = " insert into " . TABLES['pay'] . "(student_id,pay_type,rrr,order_id,amount,session,payer_id,ref) values (?,?,?,?,?,?,?,?)";


        $id = htmlentities(strip_tags($sid));
        $pid = htmlentities(strip_tags($pid));
        $rrr = (int)$this->pay_info['RRR'];
        $oid = $this->pay_info['orderId'];
        $amt = $this->pay_info['amount'];
        $payer_id = $payer;
        $ref = "FUL/MIS/SPGS/".$this->pay_info['orderId'];
        $sess = $ses;

        try {
            $stmt =  $this->db->prepare($q);

            if ($stmt->execute([$id, $pid, $rrr, $oid, $amt, $sess,$payer_id,$ref])) {
                return array('code' => 1, 'message' => 'Saved', 'payload' => null);
            }
        } catch (\Exception $e) {
            return array('code' => 0, 'message' => $e->getMessage(), 'payload' => null);
        }
        return array('code' => 0, 'message' => 'Something went wrong', 'payload' => null);
    }


    function VerifyRRR($rrr, $id, $aid, $order_id)
    {

        $rrr = htmlspecialchars(strip_tags($rrr));
        $id = htmlspecialchars(strip_tags($id));
        $aid = htmlspecialchars(strip_tags($aid));
        $order_id = htmlspecialchars(strip_tags($order_id));
        $q = "SELECT p.* , pt.clear_id as clear_type , pt.name as purpose from " .

            TABLES['pay'] . " as p join ".  TABLES['pay_type'] ." as pt on p.pay_type = pt.id 

    WHERE (student_id  = ? || student_id  = ?) and order_id = ? and rrr = ?  ";

        try {
            $stmt =  $this->db->prepare($q);

            $stmt->execute([$id,$aid, $order_id, $rrr]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->pay_info = $row;
                return true;
            }
            return false;
        } catch (\Exception $e) {
           
            return false;
        }
    }

    function verifyPayment(){

        $mert =  MERCHANTID;
        $api_key =  APIKEY;
        $concatString = $this->pay_info['rrr'] . $api_key . $mert;
        $hash = hash('sha512', $concatString);
        $url  = CHECKSTATUSURL . '/' . $mert  . '/' . $this->pay_info['rrr'] . '/' . $hash . '/' . 'status.reg';
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);
        $response = json_decode($result, true);
        return $response;
    }
    function updatePayment(){

        $q = "UPDATE  " .

        TABLES['pay'] . " set status = 1 , date_paid = ?

WHERE student_id  = ? and order_id = ? and rrr = ? and status = 0 ";

try {
    $stmt =  $this->db->prepare($q);

  $resp =  $stmt->execute([ Carbon::now(), $this->pay_info['student_id'], $this->pay_info['order_id'], $this->pay_info['rrr']]);
  
    if ($resp) {
 
        return true;
    }
    return false;
} catch (\Exception $e) {
    return false;
}

    }


   function  VerifyOrderID($id,$aid,$order_id){
  
    $id = htmlspecialchars(strip_tags($id));
    $aid = htmlspecialchars(strip_tags($aid));
    $order_id = htmlspecialchars(strip_tags($order_id));
    $q = "SELECT pay.* , ct.id as clear_type , ct.url , pt.name as purpose from " .

        TABLES['pay'] . "  as pay join ". TABLES['pay_type'] ." as pt on pay.pay_type = pt.id
        join ". TABLES['clear_type'] ." as ct on pt.clear_id = ct.id
WHERE (student_id  = ? || student_id  = ?) and order_id = ?   ";

    try {
        $stmt =  $this->db->prepare($q);

        $stmt->execute([$id, $aid, $order_id] );
        $count = $stmt->rowCount();
        if ($count > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->pay_info = $row;
            return true;
        }
        return false;
    } catch (\Exception $e) {
        return false;
    }
    }
 function    verifyOrderPayment(){
    $mert = MERCHANTID;
    $api_key = APIKEY;
    $concatString = $this->pay_info['order_id'] . $api_key . $mert;
    $hash = hash('sha512', $concatString);
    $url = CHECKSTATUSURL . '/' . $mert . '/' . $this->pay_info['order_id'] . '/' . $hash . '/' . 'orderstatus.reg';
    //  Initiate curl
    $ch = curl_init();
    // Disable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Will return the response, if false it print the response
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Set the url
    curl_setopt($ch, CURLOPT_URL, $url);
    // Execute
    $result = curl_exec($ch);
    // Closing
    curl_close($ch);
    $response = json_decode($result, true);
    return $response;
    }
}
