<?php include('head.php');

$name = @$_GET['name'];

$id = @$_GET['appd_id'];
$purpose = @$_GET['dept'];
$amount= @$_GET['faculty'];
$rrr = @$_GET['prog'];
$session = @$_GET['otp'];
$session = @$_GET['session'];
?>

<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
    <tr>
        <td width="100%" bgcolor="#ffffff" style="text-align:left;">
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Dear <?= @$name ?>,
            </p>
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
               You have successfully created a registration ID for your application. below is the detail of the Course.
            </p>
            <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                    <table border="1" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                           <tr>
                                 <td>
                                 Application  ID
                               </td>
                               <td>
                                <?= $id ?>
                               </td>
                               </tr>
                               <tr>
                                 <td>
                                Programme
                               </td>
                               <td>
                                <?= $prog ?>
                               </td>
                               </tr>

                               <tr>
                                 <td>
                               Department
                               </td>
                               <td>
                                <?= $dept ?>
                               </td>
                               </tr>

                               <tr>
                                 <td>
                               Faculty
                               </td>
                               <td>
                                <?= $faculty ?>
                               </td>
                               </tr>

                               <tr>
                                 <td>
                                Options
                               </td>
                               <td>
                                <?= $opt ?>
                               </td>
                               </tr>

                               <tr>
                                 <td>
                                Session
                               </td>
                               <td>
                                <?= $session ?>
                               </td>
                               </tr>
                               <td>
                                   
                            
                    </table>
                </td>
                </tr>
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; margin-top:10px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                           Please keep the application ID Safe. 
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