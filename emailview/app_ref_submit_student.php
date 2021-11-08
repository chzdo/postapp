<?php include('head.php');

$name = @$_GET['name'];
$r_name = @$_GET['r_name'];
$email = @$_GET['email'];


?>

<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
    <tr>
        <td width="100%" bgcolor="#ffffff" style="text-align:left;">
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Dear <?= @$name ?>,
            </p>
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Your Referee with the name <?= $r_name  ?> has submitted.  
              
            </p>
         

        </td>
    </tr>
</table>

<?php include('foot.php') ?>