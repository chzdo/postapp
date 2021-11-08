<?php
include_once '../../../../vendor/autoload.php';

include_once '../../../../config/db.php';
include_once '../../../../config/query.php';
include_once '../../../../models/faculty.php';
include_once '../../../../models/dept.php';
include_once '../../../../models/applicant.php';
include_once '../../../../models/programme.php';
include_once '../../../../models/session.php';
// generate json web token
include_once '../../../../config/core.php';






if(!isset($_GET['session'])){
    throw new Error('no session found');
    return;
}

$db = new Db();




$query = new Query($db->getAppConnection());
$portal = new Query($db->getConnection());


  $app = new Applicant($query);
  $app->portal = $portal;

 $data = $app->getSummarySubmitted($_GET['session']);



?>

<html>

<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> 
</head>

<body>
    <div class=' w-100  p-1'>
        <div class='w-100 d-flex flex-column justify-content-center align-item-center '>

              <div class="d-flex justify-content-center w-100">
             <img src='https://spgs.fulafia.edu.ng/dsb/logo.png' style='width:50px; height:50px  ' />
              </div>
            
            <p class='h4' style=' width: 100%; text-align:center'>
                FEDERAL UNIVERSITY OF LAFIA
            </p>
            <p class='h5' style='width: 100%; text-align:center'>
                POST GRADUATE SCHOOL
            </p>
            <p class='h6' style='width: 100%; text-align:center'>
               <?= $data['session'] ?>  APPLICATION
            </p>

        </div>
        <div class=' w-100'>


<table class='table table-bordered'>
<?php

foreach ($data['value'] as $key => $value) {
  
        ?>

        <thead>
            <tr>
                <th colspan=14 style="font-size:15em; font-weight:900"  > FACULTY OF <?= strtoupper($key)  ?></th>
            </tr>
        </thead>
        <?php

        foreach ($value as $dept => $list) {
        
        ?>
                <thead>

                    <tr>
                        <th  colspan=14 style="font-size:13em; font-weight:900"  > DEPARTMENT OF <?= strtoupper($dept) ?> </th>

                    </tr>
                </thead>
                <?php
                foreach ($list as  $prog=> $plist) {
                  
                ?>
                        <thead>

                            <tr>
                                <th colspan=14 scope='col' style="font-size:11em; font-weight:900" > PROGRAMME : <?= strtoupper($prog) ?> </th>

                            </tr>
                        </thead>
                        <thead>
                            <tr style=" font-weight:900" >
                                <th style=" font-weight:900" scope='col'>#</th>
                                <th style=" font-weight:900" scope='col'>Application ID</th>
                                <th style=" font-weight:900"scope='col'>Exam Fee Status</th>
                               <th style=" font-weight:900" scope='col'>Name</th>
                                <th style=" font-weight:900" style=" font-weight:900" scope='col'>Phone Number</th>
                                <th style=" font-weight:900" scope='col'>Email</th>
                                <th style=" font-weight:900" scope='col'>Address</th>
                                <th style=" font-weight:900" scope='col'>Department</th>
                                <th style=" font-weight:900" scope='col'>Programme</th>
                                <th style=" font-weight:900" scope='col'>Option</th>
                                <th style=" font-weight:900" scope='col'>Olevel Results</th>
                                <th style=" font-weight:900" scope='col'>Degree Result(s)


                                </th>
                             
                                <th scope='col'>Referee (s)</th>
                                <th scope='col'>Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($plist  as   $appd) {
                            ?>
                            <tr>
                                <td scope='row'><?= $i++  ?></td>
                                <td><?= $appd['appd_id']  ?></td>
                                <td><?= ucfirst($appd['exam_status']) ?></td>
                                <td><?= ucfirst($appd['surname']) . ' ' . ucfirst($appd['firstname']) . '  ' . ucfirst($appd['othername']) ?></td>
                                <td><?= ucfirst($appd['phone']) ?></td>
                                <td><?= ucfirst($appd['email']) ?></td>
                                <td><?= ucfirst($appd['address']) ?></td>
                               
                                <td><?= $appd['department'] ?></td>
                                <td><?= $appd['programme']?></td>
                                <td><?= $appd['options']?></td>
                                <td>
                                    <?php
                                    foreach ($appd['olevel'] as $a => $b) { ?>
                                <?=   $a.'-('.ucfirst($b).')'."."."<br><br>" ?>
                                       
                                  <?php  }

                                    ?>
                                </td>
                                <td>
                                    <?php
                                    foreach ($appd['degree'] as $a => $b) { ?>
                                <?=   ucfirst($b['qualification'])." ".ucfirst($b['programme'])." (". $b['cgpa'].")"."<br><br>"; ?>
                                       
                                  <?php  }

                                    ?>
                                </td>
                                
                                <td>
                                    <?php
                                    if (isset($appd['referee'])) {
                                        $j = 0;
                                        foreach ($appd['referee'] as $a => $b) { ?>
                                           <?= (++$j) . ' ' . $b['name'] . "<br>"; ?>
                                     <?php   }
                                    } ?>
                                </td>
                                <td>

                                </td>
                                </tr>
                            <?php } ?>
                        </tbody>
<?php
                    }
                }
            }
    

    ?>
</table>




     </div>
    </div>

</body>

</html>