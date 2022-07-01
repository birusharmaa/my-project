<?php 
//Send OTP email subject
$subject = "Email Verification - GlowLocal";

//Send OTP email body 
$body = '<body Simpli style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; width: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;" bgcolor="#F0F0F0">
            <span class="preheader-text" Simpli style="color: transparent; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; visibility: hidden; width: 0; display: none; mso-hide: all;">          
            </span>
            <div  style="display:none; font-size:0px; line-height:0px; max-height:0px; max-width:0px; opacity:0; overflow:hidden; visibility:hidden; mso-hide:all;">    
            </div>
            <table border="0" align="center" cellpadding="0" cellspacing="0" width="100%" style="width:100%;max-width:600px;">
                <tr>
                    <td align="center" Simpli bgcolor="#F0F0F0" data-composer>
                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row container-padding" width="640" style="width:640px;max-width:640px;" Simpli>
                            
                            <tr>
                                <td align="center" class="center-text">
                                    <img style="width:90px;border:0px;display: inline!important;padding-top:15px;padding-bottom: 15px;" src="'.$full_url.'/logo.png" width="90" border="0" editable="true" Simpli data-image-edit  Simpli alt="logo">
                                </td>
                            </tr>
                            
                        </table>

                        <table border="0" align="center" cellpadding="0" cellspacing="0" class="row" role="presentation" width="640" style="width:640px;max-width:640px;" simpli="">
                            <tbody>
                                <tr>
                                    <td align="center">
                                       
                                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row container-padding25" width="600" style="width:600px;max-width:600px;">
                                            <tbody>
                                                <tr>
                                                    <td align="center" simpli="" bgcolor="#FFFFFF" style="border-radius:10px 10px 10px 10px;padding-top:40px; border-bottom:solid 6px #DDDDDD;">
                                                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row container-padding" width="520" style="width:520px;max-width:520px;">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="center-text" simpli="" align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:12px;line-height:24px;font-weight:900;font-style:normal;color:#1898c2;text-decoration:none;letter-spacing:2px;">
                                                                        <singleline>
                                                                            <div mc:edit="" simpli="">
                                                                                EMAIL VERIFICATION
                                                                            </div>
                                                                        </singleline>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="center-text" simpli="" align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:40px;line-height:54px;font-weight:700;font-style:normal;color:#333333;text-decoration:none;letter-spacing:0px;">
                                                                        <singleline>
                                                                            <div mc:edit="" simpli="">
                                                                                Verify Your Email Account
                                                                            </div>
                                                                        </singleline>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="15" style="font-size:15px;line-height:15px;" simpli="">&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="center-text" simpli="" align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:16px;line-height:26px;font-weight:300;font-style:normal;color:#333333;text-decoration:none;letter-spacing:0px;">
                                                                        <singleline>
                                                                            <div mc:edit="" simpli="">
                                                                                Please use the following OTP to verify your email.
                                                                            </div>
                                                                        </singleline>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="25" style="font-size:25px;line-height:25px;" simpli="">&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center">
                                                                        <table border="0" cellspacing="0" cellpadding="0" role="presentation" align="center" class="row" width="480" style="width:480px;max-width:480px;">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" simpli="" bgcolor="#FAFAFA" style="border-radius: 10px;border: 2px dotted #DDDDDD;">
                                                                                        <table border="0" cellspacing="0" cellpadding="0" role="presentation" align="center" class="row" width="480" style="width:480px;max-width:480px;">
                                                                                            <tbody>
                                                                                                <tr>
                                                                                                    <td height="20" style="font-size:20px;line-height:20px;">&nbsp;</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td class="center-text" simpli="" align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:22px;line-height:26px;font-weight:700;font-style:normal;color:#333333;text-decoration:none;letter-spacing:0px;">
                                                                                                        <singleline>
                                                                                                            <div mc:edit="" simpli="">
                                                                                                                USE CODE: <span style="color:#1898c2;">'.$random_number.'</span>
                                                                                                            </div>
                                                                                                        </singleline>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td height="20" style="font-size:20px;line-height:20px;">&nbsp;</td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="30" style="font-size:30px;line-height:30px;" simpli="">&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center">
                                                                        <table border="0" cellspacing="0" cellpadding="0" role="presentation" align="center" class="center-float">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" simpli="" bgcolor="#ff7775" style="border-radius: 6px;">
                                                                                        <table border="0" cellpadding="0" cellspacing="0" align="center">
                                                                                            <tr>
                                                                                                <td align="center" width="35"></td>
                                                                                          
                                                                                                <td align="center" width="35"></td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="40" style="font-size:40px;line-height:40px;" simpli="">&nbsp;</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>


                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="width:100%;max-width:100%;" Simpli>
                            <tr>
                                <td align="center">
                                    <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row container-padding" width="520" style="width:520px;max-width:520px;">
                                        <tr>
                                            <td height="50" style="font-size:50px;line-height:50px;" Simpli>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="center-text" Simpli align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:24px;line-height:32px;font-weight:400;font-style:normal;color:#999999;text-decoration:none;letter-spacing:0px;">
                                                <singleline>
                                   
                                        <tr>
                                            <td align="center">
                                                <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row" width="480" style="width:480px;max-width:480px;">
                                                    <tr>
                                                        <td class="center-text" Simpli align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:14px;line-height:24px;font-weight:480;font-style:normal;color:#666666;text-decoration:none;letter-spacing:0px;">
                                                            <multiline>
                                                                <div mc:edit Simpli>
                                                                &copy; '.date("Y").' glowlocal.org | All Rights Reserved.
                                                               
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="30" style="font-size:30px;line-height:30px;" Simpli>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="center-text">
                                                <img style="width:120px;border:0px;display: inline!important;" src="'.$full_url.'/logo.png" width="120" border="0" editable="true" Simpli data-image-edit  Simpli alt="logo">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="50" style="font-size:50px;line-height:50px;" Simpli>&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>';

    ?>