<?php include ('head.php');

$name = @$_GET['email'];

 $id = @$_GET['code'];


?>
    
    <table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="25" width="100%">
                        <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:left;">
                            	<p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                    Dear Applicant,                                 
                                </p>
                            	<p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                  You attempted to recover your password. Please use the OTP below to change your password. 
                                </p>
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%" class="emailwrapto100pc">
                                      <tr>
                                        <td class="emailcolsplit" align="left" valign="top" width="58%">
                                            <p style=" text-align:center; color:#222222;  width:fit-content; background:#ccc ;  font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                                <b><?= $id ?></b>                                            </p>
                                        </td>
                                        
                                      </tr>
                                      <tr>
                                        <td class="emailcolsplit" align="left" valign="top" width="58%">
                                            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                             This code will be valid for the next two (2) hours.
                                            </p>
                                        </td>
                                        
                                      </tr>
                                      <tr>
                                        <td class="emailcolsplit" align="left" valign="top" width="58%">
                                            <p style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0; font-weight:normal;">
                                              If this is not you please kindly ignore this mail. 
                                            </p>
                                        </td>
                                        
                                      </tr>
                                    </table>
    
                            </td>
                        </tr>
                   </table>
                   
                   <?php include ('foot.php') ?>