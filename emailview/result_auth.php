<?php include ('head.php');

$name = @$_GET['name'];

 $code = @$_GET['code'];


?>
    
    <table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
                        <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:left;">
                            	<p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                    Dear <?= @$name ?>,                                 
                                </p>
                            	<p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                   You requested for result Authorization code. Below is your four (4) digit code.
                                </p>
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                                      <tr>
                                        <td class="emailcolsplit" align="left" valign="top" width="58%">
                                            <p style=" text-align:center; color:#222222;  width:fit-content; background:#ccc ;  font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                                <b><?= $code  ?></b>                                            </p>
                                        </td>
                                        
                                      </tr>
                                      <tr>
                                        <td class="emailcolsplit" align="left" valign="top" width="58%">
                                            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                               This code will last will for one hour. Thank you.
                                            </p>
                                        </td>
                                        
                                      </tr>
                                    </table>
    
                            </td>
                        </tr>
                   </table>
                   
                   <?php include ('foot.php') ?>