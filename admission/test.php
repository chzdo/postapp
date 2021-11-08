<?php

include_once '../vendor/autoload.php';
include_once '../config/core.php';
include_once '../config/db.php';
include_once '../config/query.php';
include_once '../models/clearance.php';
include_once '../models/student.php';
include_once '../models/session.php';

include_once '../models/applicant.php';

$db = new Db();


$data = $db->getConnection();

$query = new Query($db->getAppConnection());
$portal = new Query($db->getConnection());


  $app = new Applicant($query);
  $app->portal = $portal;

 echo json_encode($app->getSummarySubmitted(3));

return;
$q = new Query($data);

$q->insert(array("name"=>"stanley","age"=>12,"date"=>date('now')), "s");

$q->update(array("name"=>"staley2"),array("name"=>"stanley"),[],'s');
$q->pullAll(array("name"=>"Stan","age"=>9),"select * from s where name = ? and age = ?");

