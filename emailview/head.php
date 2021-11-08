<?php 
include ('../vendor/autoload.php');
require_once ('../config/core.php') ;



?>
<html !Doctype>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* PUT ALL CSS IN THE EMAIL <HEAD>

These styles are meant for clients that recognize CSS in the <head>; the email WILL STILL WORK for those that don't. */

#outlook a{padding:0;}
body{width:100% !important; background-color:#41849a;-webkit-text-size-adjust:none; -ms-text-size-adjust:none;margin:0 !important; padding:0 !important;}  
.ReadMsgBody{width:100%;} 
.ExternalClass{width:100%;}
ol li {margin-bottom:15px;}
	
img{height:auto; line-height:100%; outline:none; text-decoration:none;}
#backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}
	
p {margin: 1em 0;}
	
h1, h2, h3, h4, h5, h6 {color:#222222 !important; font-family:Arial, Helvetica, sans-serif; line-height: 100% !important;}
	
table td {border-collapse:collapse;}
	
.yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span { color: black; text-decoration: none !important; border-bottom: none !important; background: none !important;}
	
.im {color:black;}
div[id="tablewrap"] {
		width:100%; 
		max-width:600px!important;
	}
table[class="fulltable"], td[class="fulltd"] {
		max-width:100% !important;
		width:100% !important;
		height:auto !important;
	}
			
@media screen and (max-device-width: 430px), screen and (max-width: 430px) { 
		td[class=emailcolsplit]{
			width:100%!important; 
			float:left!important;
			padding-left:0!important;
			max-width:430px !important;
		}
    td[class=emailcolsplit] img {
    margin-bottom:20px !important;
    }
}


</style>
</head>

<body style="width:100% !important; margin:0 !important; padding:0 !important; -webkit-text-size-adjust:none; -ms-text-size-adjust:none; background-color:#FFFFFF;">
<table cellpadding="0" cellspacing="0" border="0" id="backgroundTable" style="height:auto !important; margin:0; padding:0; width:100% !important; background-color:#FFFFFF; color:#222222;">
	<tr>
		<td>
         <div id="tablewrap" style="width:100% !important; max-width:600px !important; text-align:center !important; margin-top:0 !important; margin-right: auto !important; margin-bottom:0 !important; margin-left: auto !important;">
		      <table id="contenttable" width="600" align="center" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF; text-align:center !important; margin-top:0 !important; margin-right: auto !important; margin-bottom:0 !important; margin-left: auto !important; border:none; width: 100% !important; max-width:600px !important;">
            <tr>
                <td width="100%">
                    <table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:center;"><a href="#">
                                <img src=<?=  URL.'/public/logo.png' ?> alt="Main banner image and link" style="display:inline-block; max-width:100px !important; width:100px !important; height:auto !important;border-bottom-right-radius:8px;border-bottom-left-radius:8px;" border="0"></a>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:center;">
                                 <h3 >
                                           <?=          SCHOOLNAME  ?>
                                 </h3>
                        </td>
                        <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:center;">
                                 <h5 >
                                           <?=         SCHOOL   ?>
                                 </h5>
                        </td>
                        </tr>
                   </table>
                
                 