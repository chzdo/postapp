<?php include('head.php');

$name = @$_GET['name'];

$id = @$_GET['id'];
$purpose = @$_GET['purpose'];
$amount= @$_GET['amount'];
$rrr = @$_GET['rrr'];
$session = @$_GET['session'];
?>

<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
    <tr>
        <td width="100%" bgcolor="#ffffff" style="text-align:left;">
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                Dear <?= @$name ?>,
            </p>
            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
               This is to notify you of the payment made with our portal. Below is a summary of the details.
            </p>
            <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                    <table border="1" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                           <tr>
                                 <td>
                                   ID
                               </td>
                               <td>
                                   RRR
                               </td>
                               <td>
                                  PURPOSE
                               </td>
                               <td>
                                  AMOUNT
                               </td>
                               <td>
                                   SESSION
                               </td>
                           </tr>
                           <tr>
                                 <td>
                                   <?= $id ?>
                               </td>
                               <td>
                                  <?= $rrr ?>
                               </td>
                               <td>
                               <?= $purpose ?>
                               </td>
                               <td>
                               <?= $amount ?>
                               </td>
                               <td>
                               <?= $session ?>
                               </td>
                           </tr>
                    </table>
                </td>
                </tr>
                <tr>
                    <td class="emailcolsplit" align="left" valign="top" width="58%">
                        <p style="color:#222222; margin-top:10px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                            You can always print your eReciept from the portal.
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