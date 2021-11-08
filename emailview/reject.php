<?php include('head.php');

$name = @$_GET['name'];

$appd_id = @$_GET['appd_id'];
$FACULTY = @$_GET['faculty'];
$DEPT = @$_GET['dept'];
$PROG = @$_GET['prog'];
$session = @$_GET['session'];
?>

<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
    <tr>
        <td width="100%" bgcolor="#ffffff" style="text-align:left;">
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Dear <?= @$name ?>,
            </p>
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                We reject to inform you that your application for a   <?= $PROG ?>  programme in the Fcaulty of  <b> <?= $FACULTY  ?> </b> 
              ,  <?= $DEPT ?> department for <?=  $session ?> session was not successfull.
            </p>
            <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                           We encourage you to keep trying. 
                    </td>
                </tr>
                
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                            Thank you.
                        </p>
                    </td>

                </tr>
            </table>

        </td>
    </tr>
</table>

<?php include('foot.php') ?>