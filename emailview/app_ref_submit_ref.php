<?php include('head.php');

$name = @$_GET['name'];
$appd_id = @$_GET['appd_id'];



?>

<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
    <tr>
        <td width="100%" bgcolor="#ffffff" style="text-align:left;">
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Dear <?= @$name ?>,
            </p>
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
               Thank you for filling the form for applicant with ID <?=  $appd_id ?> .
              
            </p>
         

        </td>
    </tr>
</table>

<?php include('foot.php') ?>