
<tbody>
<tr>
<td scope='row'> </td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
</tbody>




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
                        <th scope='col'>Degree Result(s)</th>
                        <th scope='col'>Olevel Result(s)</th>
                        <th scope='col'>Referee (s)</th>
                        <th scope='col'>Remark</th>
                    </tr>
                </thead>

                <thead>
                    <tr>
                        <th scope='col'>#</th>
                        <th scope='col'>Application ID</th>
                        <th scope='col'>Name</th>

                        <th scope='col'>Department</th>
                        <th scope='col'>Programme</th>
                        <th scope='col'>Degree Result(s)</th>
                        <th scope='col'>Olevel Result(s)</th>
                        <th scope='col'>Referee (s)</th>
                        <th scope='col'>Remark</th>
                    </tr>
                </thead>

                <?php
            }
        }
    }
}?>
function PDFHTML($info, $f , $d, $p, $s){
 $html = "
 <html>

 <head>
     <link rel='stylesheet' href='http://localhost/portal/portal/api/routes/application/all/pdf/bootstrap.css'  media='all'/>
 </head>
 
 <body style='font-family:Lucida Sans, Lucida Sans Regular, Lucida Grande, Lucida Sans Unicode, Geneva, Verdana, sans-serif'>
 
     <div class=' w-100  p-1'>
         <div class='  ' style='width:100%' >
      <center>
             <img src='http://localhost/portal/portal/public/logo.png'  style='width:50px; height:50px; margin-left:'auto' />
        </center>
             </div>
         <div class='  ' >
   <center>
             <p class='h4'>
                 FEDERAL UNIVERSITY OF LAFIA
             </p>
             <p class='h5'>
                 POST GRADUATE SCHOOL
             </p>
             <p class='h6'>
                 $s APPLICATION
             </p>
             </center>
             </div>
             <div class=' w-100'>
                 <table class='table table-bordered'>";

                 foreach($info as $f_id => $d_id){  
                     $temp = $f->all;
                     $fac = getName($temp,$f_id);
                     if ($fac != null){
                        $fac =  strtoupper($fac);
              $html .= "         <thead>
                         <tr>
                             <th colspan='10' scope='col'> FACULTY OF $fac </th>
                         </tr>
                     </thead>";
                     foreach($info[$f_id] as $d_id => $p_id){ 
                        $temp = $d->all;
                        $dt = getName($temp,$d_id);
                        if ($dt != null){
                           $dt =  strtoupper($dt);
                  $html .= "   <thead>
                         <tr>
                             <th colspan='10' scope='col'> DEPARTMENT OF $dt </th>
 
                         </tr>
                     </thead>";

                     foreach($info[$f_id][$d_id] as  $p_id => $appd){ 
                        $temp = $p->all;
                        $pt = getName($temp,$p_id);
                        if ($pt != null){
                           $dt =  strtoupper($pt);

                 $html .=  "  <thead>
                         <tr>
                             <th colspan='10' scope='col'>PROGRAMME :  $pt</th>
 
                         </tr>
                     </thead>
                     <thead>
                         <tr>
                             <th scope='col'>#</th>
                             <th scope='col'>Application ID</th>
                             <th scope='col'>Name</th>
                             
                             <th scope='col'>Department</th>
                             <th scope='col'>Programme</th>
                             <th scope='col'>Degree Result(s)</th>
                             <th scope='col'>Olevel Result(s)</th>
                             <th scope='col'>Referee (s)</th>
                             <th scope='col'>Remark</th>
                         </tr>
                     </thead>
                     <tbody>";
                     $i = 1;
                     foreach($info[$f_id][$d_id][$p_id]  as   $appd){
                        $html .=  " <tr>
                        <td scope='row'>".$i++  ."</td>
                        <td>". $appd['appd_id']  ."</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        </tr>";
                     }
                     /**
                       $html .=  " <tr>
                             <th scope='row'>".$i++  ."</th>
                             <td>". $appd['appd_id']  ."</td>
                             <td> ". $appd['surname'] .' ' . $appd['firstname'] . '  '. $appd['othername'] ."</td>
                            
                             <td> $dt  </td>
                             <td> $pt</td>
                             <td>MSC</td>
                             <td>
                                 <table class='table'>
                                     <tr>
                                         <th scope='row'>MSC</th>
                                     </tr>
                                     <tr>
                                         <th scope='row'>Computer Science</th>
                                     </tr>
                                     <tr>
                                         <th scope='row'>3.69</th>
                                     </tr>
                                 </table>
                             </td>
                             <td>
                                 <table class='table'>
                                     <tr >
                                         <th colspan='2' scope='row'>2344566hj</th>
                                     </tr>
                                     <tr>
                                         <th scope='row'>Englis</th>
                                         <th scope='row'>A1</th>
                                     </tr>
                                     
                                 </table>
                             </td>
                             <td>
                                 <table class='table'>
                                     <tr >
                                         <th colspan='2' scope='row'>Sam</th>
                                     </tr>
                                     <tr>
                                         <th scope='row'>Tunde</th>
                                        
                                     </tr>
                                     
                                 </table>
                             </td>
                             <td>
                               
                             </td>
                         </tr>";
                     } **/
                       
                  $html .=  "   </tbody>";
                        }}
                 }
                }
            }
                }
               $html .= "  </table>
             </div>
       
 
 
     </div>
 
     </body>
 
 </html>
 ";

return $html;

}





function getName($obj,$id){
   
   
    
   $v = array_filter($obj, function($var) use ($id) { return ($var['id'] == $id);});
    foreach($v as $key){
       
       if (isset($key['name'])){
           return $key['name'];
           break;
       }else if (isset($key['programme'])){
           return $key['programme'];
       break;
       } 
    }
   
   return null;
   }

   <table class='table'>
                                 <?php 

                             //    foreach($appd['olevel'] as $key=>$value) {?>

                                     <tr >
                                         <th colspan='2' scope='row'><?=''?></th>
                                     </tr>
                                     <tr>
                                     <?php 

                                     //foreach($value as $sub=>$grade) {?>
                                         <th scope='row'><?= ''// $sub ?></th>
                                         <th scope='row'><?= ''// $grade ?></th>
                                     <?php// } ?>
                                     </tr>
                                     <?php //} ?>
                                 </table>
                             </td>
                             <td>
                                 <table class='table'>
                                 <?php 
                                  foreach($appd['referee'] as $id=>$name) {?>
                                     <tr >
                                         <th colspan='2' scope='row'><?= $name ?></th>
                                     </tr>
                                  <?php } ?>
                                     
                                 </table>
                             </td>
                             <td>
                               
                             </td>
                         </tr>
                                               <?php } ?>