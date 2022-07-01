<?php
//Feedback request email subject
$subject = "How Did ".$company_name." Do?";

//Add end text for emil and phone
$end_text = "";

//Setting page logo or app.glowlocal.org logo img path
if($logo_url!=""){
    $logo = $logo_url;
    $user_logo = "Yes";
    $logo = "https://app.glowlocal.org/uploads/users_logo/".$logo_url;
}else{
    $logo = $full_url.'/logo.png';
    $user_logo = "";
}


//Feedback request email body
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
                                    <img style="width:90px;border:0px;display: inline!important;padding-top:15px;padding-bottom: 15px;" src="'.$logo.'" width="90" border="0" editable="true" Simpli data-image-edit  Simpli alt="logo">                                
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
                                                    <td align="center" simpli="" bgcolor="#FFFFFF" style="border-radius:10px 10px 10px 10px; border-bottom:solid 6px #DDDDDD;padding-top:40px;">
                                                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row container-padding" width="520" style="width:520px;max-width:520px;">
                                                            <tbody>
                                                          <tr>
                                                          <td class="center-text" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; line-height: 24px; font-weight: 900; font-style: normal; color: #1898c2; text-decoration: none; letter-spacing: 2px;" align="center">
                                                          <div>WE\'D LOVE TO HEAR YOUR THOUGHTS</div>
                                                          </td>
                            </tr>     
                                                                <tr>
                                                                    <td class="center-text" simpli="" align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:50px;line-height:34px;font-weight:600;font-style:normal;color:#333333;text-decoration:none;letter-spacing:0px;">
                                                                        <singleline>
																		<br>
                                                                            <div mc:edit="" simpli="">
                                                                                Leave a review!
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
                                                                                <p style="margin-left: 4px;padding-top:20px;padding-bottom:20px;">                                                                                    
                                                                                   '.$message.'<br/>
<div>
                                                                                How was your experience?
                                                                            </div>
                                                                                   <br/>
                                                                                   <a href="'.$share_feedback.'" style="text-decoration: none;" >
                                                                                     <img alt="Share Feedback" src="'.$full_url.'/thumbup.jpg"
                                                                                     width=100" >
                                                                                  </a>
                                                                                  <a href="'.$proive_feedback.'" style="text-decoration: none;">
                                                                                     <img alt="Provide Feedback" src="'.$full_url.'/thumbdown.jpg"
                                                                                     width=100">
                                                                                  </a>
                                                                                </p>                                                     
                                                                            </div>
                                                                        </singleline>
                                                                    </td>
                                                                </tr>
                                                               
                                                            </tbody>
                                                        </table>

                                                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row container-padding" width="520" style="width:520px;max-width:520px;">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="center-text" simpli="" align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:16px;line-height:26px;font-weight:300;font-style:normal;color:#333333;text-decoration:none;letter-spacing:0px;">
                                                                        <singleline>
                                                                            <div mc:edit="" simpli="">
                                                                                <p style="margin-left: 4px;padding-top:20px;padding-bottom:20px;">                                                                                    
                                                                                   '.$end_text.'                                                                              </p>                                                     
                                                                            </div>
                                                                        </singleline>
                                                                    </td>
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
                                                <!-- Social Icons -->
                                                <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="width:100%;max-width:100%;">
                                                    <tr>
                                                        <td align="center">
                                                            <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation">
                                                                <tr>
                                                                    <td  class="rwd-on-mobile" align="center" valign="middle" height="36" style="height: 36px;">
                                                                        <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation">
                                                                    
                                                                        </table>
                                                                    </td>
                                                                    <td  class="rwd-on-mobile" align="center" valign="middle" height="36" style="height: 36px;">
                                                                      <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation">
                                                                    
                            
                                   
                                                                    
                                        <tr>
                                            <td height="40" style="font-size:40px;line-height:40px;" Simpli>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row" width="480" style="width:480px;max-width:480px;">
                                                    <tr>
                                                        <td class="center-text" Simpli align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:16px;line-height:24px;font-weight:480;font-style:normal;color:#666666;text-decoration:none;letter-spacing:0px;">
                                                    <tr>
                                                        <td align="center">
                                                            <!-- column -->
                                                            <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation">
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="30" style="font-size:30px;line-height:30px;" Simpli>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <table border="0" align="center" cellpadding="0" cellspacing="0" role="presentation" class="row" width="480" style="width:480px;max-width:480px;">
                                                    <tr>
                                                        <td class="center-text" Simpli align="center" style="font-family:Catamaran,Arial,Helvetica,sans-serif;font-size:14px;line-height:24px;font-weight:480;font-style:normal;color:#666666;text-decoration:none;letter-spacing:0px;">
                                                            <multiline>
                                                                <div mc:edit Simpli>
                                                                    &copy; '.date("Y").' GlowLocal LLC | All Rights Reserved<br>
                                                                    <br>
                                                                    <br>
                                                                    <a href="https://app.glowlocal.org/unsubscribe.php?code='.$unique_code. '  target="_blank" ">
                                                                        <span>Unsubscribe</span>
                                                                    </a>
                                                        </td>
                                                </tr>
                                            </table>
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
            </table>';
        ?>