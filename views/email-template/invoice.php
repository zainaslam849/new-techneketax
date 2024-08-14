<?php
$message='<!doctype html>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>'.$env['SITE_NAME'].'</title>
    <style>
        .button {
            background-color: #04AA6D; /* Green */
            border: none;
            color: white !important;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }
        h1,h2,h3,h4,h5,span,a,p,label{
            color: #000 !important;
        }
        .btn{
            padding: 15px;
            background: black;
            color: white !important;
            border-radius: 7px;
            text-decoration: none;
        }
    </style>
</head>
<body style="background-color:#000; margin:0!important; padding:0!important">
<div style="background-color:#000;margin:0!important;padding:0!important">


    <table border="0" cellpadding="0" cellspacing="0" width="100%">

        <tbody>
        <tr>
            <td bgcolor="#fff" align="center" style="padding:0px 10px 0px 10px">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px">
                    <tbody><tr>
                        <td bgcolor="#ffffff" align="center" valign="top" style="padding:0px 0px 0px 0px;margin-top:10px; border-radius:4px 4px 0px 0px;    background-color: #CDCDCD; color:#ffffff;font-family:Lato,Helvetica,Arial,sans-serif;font-size:48px;font-weight:400;letter-spacing:4px;line-height:48px">
                            <img src="'.$env['APP_URL'].'uploads/settings/'.$settings[0]['logo'].'" width="40%" class="CToWUd" data-bit="iit">
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="center" valign="top" style="padding:0px 0px 0px 0px;border-radius:4px 4px 0px 0px;    background-color: #CDCDCD; color:#000;font-family:Lato,Helvetica,Arial,sans-serif;font-size:48px;font-weight:400;letter-spacing:4px;line-height:48px">
                            <h1 style="font-size:32px;font-weight:400;margin:2px">'.$firm_name.' Send You an invoice </h1>
                        </td>
                    </tr>
                    </tbody></table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#fff" align="center" style="padding:0px 0px 0px 0px">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px">
                    <tbody><tr>
                        <td bgcolor="#ffffff" align="center" style="padding:10px;color:#000;     background-color: #CDCDCD;">
                            <h3 style=" padding-right: 21px; padding-left: 21px; ">
                              '.$send_message.'
                            </h3>
                            <a href="'.$env['APP_URL'].'invoice/view/'.$insert.'" class="btn">Invoice</a>
                            <br>
                            <h3 style=" padding-right: 21px; padding-left: 21px; ">
                                Thank you,
                                '.$env['SITE_NAME'].' Team</p>
                            </h3>
                        </td>
                    </tr>
                    </tbody></table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#fff" align="center" >
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px">
                    <tbody><tr>
                        <td bgcolor="#FFECD1" align="center" style="padding:30px 30px 30px 30px;border-radius:4px 4px 4px 4px; background-color: #000; color:#ffffff; font-family:Lato,Helvetica,Arial,sans-serif;font-size:18px;font-weight:400;line-height:25px">
                            <p style="margin:0;padding-top: 3px; padding-bottom: 3px; padding-right: 21px; padding-left: 21px; color:#ffffff !important;">You are receiving this email because '. $firm_name .' send you a invoice<a href="#" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://paywithmoon.com&amp;source=gmail&amp;ust=1666436123784000&amp;usg=AOvVaw1gmBJ5oK0QGzrUsrMq9kkE" style="color:#ffffff !important;">'.$settings[0]['name'].'</a>  If this wasn"t you, you can safely ignore this email.</p>
                        </td>
                    </tr>
                    </tbody></table>
            </td>
        </tr>

        </tbody></table><div class="yj6qo"></div><div class="adL">
    </div></div>
</body>
</html>';