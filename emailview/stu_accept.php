<?php include('head.php');

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
                We are pleased to inform you that you have successfully been cleared by the post graduate school.
              
            </p>
            <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                           You can click  <a style="color:#2489B3; font-weight:bold; text-decoration:underline;" href="<?= URL2 ?>">Here </a> to proceed with your registration.
                    </td>
                </tr>
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                           Please also note that your username and password is your registration number <?= $id ?>
                    </td>
                </tr>
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                            Accept our Congratulations.
                        </p>
                    </td>

                </tr>
            </table>

        </td>
    </tr>
</table>

<?php include('foot.php') ?>