<?php include('head.php');

$student_id = @$_GET['student_id'];
$student_name = @$_GET['student_name'];
$email = @$_GET['email'];
$name = @$_GET['name'];
$id = @$_GET['id'];

?>

<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
    <tr>
        <td width="100%" bgcolor="#ffffff" style="text-align:left;">
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Dear <?= @$name ?>,
            </p>
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                You have been selected by <?= $student_name  ?> with Application ID  <?= $student_id ?> as a referee in  a Post graduate application at the Federal University of Lafia.
              
            </p>
            <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                           To fill the referee form for this applicant, Kindly  <a style="color:#2489B3; font-weight:bold; text-decoration:underline;" href="<?= URL_FOR_REFEREE_CREATE."/".base64_encode($student_id)."/".base64_encode($email) ?>">Click here</a>!
                    </td>
                </tr>
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                         If you do not have knowledge of this request, Please kindly ignore this mail.
                    </td>
                </tr>
             
            </table>

        </td>
    </tr>
</table>

<?php include('foot.php') ?>