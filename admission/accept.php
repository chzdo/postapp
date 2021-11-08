<?php error_reporting(0); ?>
<html>
        <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        </head>

        <body>

                <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">
                    
        <img src="../../public/logo.png" style="width:70px ; height: 70px" class="rounded" alt="...">
        <h5>
            FEDERAL UNIVERSITY OF LAFIA
        </h5>
        <h6>
            SCHOOL OF POSTGRADUATE STUDIES
        </h6>


<?php 

if(isset($_GET['y']) && $_GET['y'] != ''){

$mysqli = new mysqli('localhost','root','','journals_spgs');

$q = "select  ci.*, un.* , un.email as  em , app.user_name as email from application as app
join  course_info as ci on ci.appd_id = app.appd_id 
join  user_info as un on un.appd_id = app.appd_id 
where app.pay_status = 1 and status = 3

and app.appd_id = '".$_GET['y']."'";

$resp = mysqli_query($mysqli,$q) or die(mysqli_error($mysqli));
if($resp){
    if(mysqli_num_rows($resp) > 0){
        $row = mysqli_fetch_assoc($resp);
        $load =  base64_encode(json_encode($row));
        $id = base64_encode($row['appd_id']);
    echo '           <div class="alert alert-success" role="alert">
    <h4 class="alert-heading">Yeah!</h4>
    <p>Student found</p>
    <hr>
    <p class="mb-0">you will soon be redirected!</p>
  </div>';
   
echo "<script>

 document.location.href = 'http://localhost/portal/portal/api/admission/?a=$id&load=$load'
</script>";

//<a href='http://localhost/portal/portal/api/admission/?a=$id&load=$load' > accept </a>";

  
}else{
    ?>
 
                <div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">Ooops!</h4>
  <p>Aww sorry, The page you are looking for does not exist</p>
  <hr>
  <p class="mb-0">Either the student does not exist or has not yet been admitted! you can contact MIS team for more information</p>
</div>

   
<?php }


}
}else{
    ?>
             <div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">Ooops!</h4>
  <p>Aww sorry, Invalid Link</p>
  <hr>
  <p class="mb-0">This link is either incomplete or broken!</p>
</div>


<?php
}
?>


</div>
        </body>
    </html>