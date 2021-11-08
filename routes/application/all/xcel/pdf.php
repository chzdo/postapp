<?php
include_once '../../../../vendor/autoload.php';

include_once '../../../../config/db.php';
include_once '../../../../models/faculty.php';
include_once '../../../../models/dept.php';
include_once '../../../../models/programme.php';
include_once '../../../../models/session.php';
// generate json web token
include_once '../../../../config/core.php';




$db = new Db();

$conn = $db->getConnection();

$f = new Faculty($conn);
$d = new Dept($conn);
$p = new Programme($conn);
$s = new Session($conn);

$f->allActive();
$d->allActive();
$p->allActive();
$s->check(24);

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

try {
    $response = $client->request('GET', 'application/all/complete/', [
        'query' => ['session' => htmlspecialchars(strip_tags($_GET['session']))]
    ]);
} catch (ClientException $e) {
    // var_dump($e->getRequest());
}
//var_dump($response);
$response =   json_decode($response->getBody(), true);
//var_dump($response);
$payload = $response['payload'];

function getName($obj, $id)
{



    $v = array_filter($obj, function ($var) use ($id) {
        return ($var['id'] == $id);
    });

    foreach ($v as $key) {

        if (isset($key['name'])) {
            return $key['name'];
            break;
        } else if (isset($key['programme'])) {
            return $key['programme'];
            break;
        }
    }

    return null;
}





?>

<html>

<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> 
</head>

<body>
    <div class=' w-100  p-1'>
        <div class='w-100 d-flex flex-column justify-content-center align-item-center '>

              <div class="d-flex justify-content-center w-100">
              <img src='logo.jpg' style='width:50px; height:50px  ' />
              </div>
            
            <p class='h4' style=' width: 100%; text-align:center'>
                FEDERAL UNIVERSITY OF LAFIA
            </p>
            <p class='h5' style='width: 100%; text-align:center'>
                POST GRADUATE SCHOOL
            </p>
            <p class='h6' style='width: 100%; text-align:center'>
               <?= $s->name ?>  APPLICATION
            </p>

        </div>
        <div class=' w-100'>


<table class='table table-bordered'>
    <?php

    foreach ($payload as $f_id => $d_id) {
        $temp = $f->all;
        try {
            $fac = getName($temp, $f_id);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        if ($fac != null) {
            $fac =  strtoupper($fac); ?>

            <thead>
                <tr>
                    <th colspan='9' scope='col'> FACULTY OF <?= $fac  ?></th>
                </tr>
            </thead>
            <?php

            foreach ($payload[$f_id] as $d_id => $d_id) {
                $temp = $d->all;
                $dt = getName($temp, $d_id);
                if ($dt != null) {
                    $dt =  strtoupper($dt);
            ?>
                    <thead>

                        <tr>
                            <th colspan='9' scope='col'> DEPARTMENT OF <?= $dt ?> </th>

                        </tr>
                    </thead>
                    <?php
                    foreach ($payload[$f_id][$d_id] as  $p_id => $appd) {
                        $temp = $p->all;
                        $pt = getName($temp, $p_id);
                        if ($pt != null) {
                            $pt =  strtoupper($pt);
                    ?>
                            <thead>

                                <tr>
                                    <th colspan='9' scope='col'> PROGRAMME : <?= $pt ?> </th>

                                </tr>
                            </thead>
                            <thead>
                                <tr>
                                    <th scope='col'>#</th>
                                    <th scope='col'>Application ID</th>
                                    <th scope='col'>Name</th>

                                    <th scope='col'>Department</th>
                                    <th scope='col'>Programme</th>
                                    <th scope='col'>Degree Result(s)


                                    </th>
                                    <th scope='col'>Olevel Result(s)</th>
                                    <th scope='col'>Referee (s)</th>
                                    <th scope='col'>Remark</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($payload[$f_id][$d_id][$p_id]  as   $appd) {
                                ?>
                                <tr>
                                    <td scope='row'><?= $i++  ?></td>
                                    <td><?= $appd['appd_id']  ?></td>
                                    <td><?= ucfirst($appd['surname']) . ' ' . ucfirst($appd['firstname']) . '  ' . ucfirst($appd['othername']) ?></td>

                                    <td><?= $dt  ?></td>
                                    <td><?= $pt ?></td>

                                    <td>
                                        <?php
                                        foreach ($appd['degree'] as $a => $b) { 
                                        echo  ucfirst($b['qualification'])." ".ucfirst($b['programme'])." (". $b['cgpa'].")";
                                           echo "<br><br>";
                                           
                                        }

                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        foreach ($appd['olevel'] as $a => $b) {
                                        
                                            
                                                  
                                            echo $a."-(";
                                                        foreach ($b as $sub => $grade) {

                                                            
                                                            echo ucfirst($sub) . ' ' . $grade . "<br>";
                                                        } 
                                                 


                                                  echo ")"."<br><br>";
                                              }
                                        ?>



                                    </td>
                                    <td>
                                        <?php
                                        if (isset($appd['referee'])) {
                                            $i = 0;
                                            foreach ($appd['referee'] as $a => $b) {
                                                echo (++$i) . ' ' . $b . "<br>";
                                            }
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
            }
        }
    }

    ?>
</table>




     </div>
    </div>

</body>

</html>