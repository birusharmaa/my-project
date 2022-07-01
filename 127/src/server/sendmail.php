<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../server/vendor/autoload.php';

        // Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);
session_start();

if(isset($_POST["operation"])){
    $operation = $_POST["operation"];
    require("config.php");
    require("ssp.class.php"); 
}else{
    exit();
}

$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
else{}

$ishttps = $isSecure ? 'https' : 'http';
$hostName = $_SERVER['HTTP_HOST'];
$finalUrl = $ishttps.'://'.$hostName;
if($hostName=="localhost"){
    $finalUrl = $finalUrl."/projects-sm/127/src/";
}

if($operation == "sendLink"){  
    
    $data = array();
    $email = $_POST['email'];
    $type  = $_POST['type'];

    $token = bin2hex(random_bytes(15));
    
    $y = mysqli_query($conn,"UPDATE `users` SET `reset_link` = '".$token."', `reset_timestamp` = UNIX_TIMESTAMP() WHERE `users`.`email_id` = '".$email."' ");
    
    $query = "SELECT * FROM `users` WHERE `email_id` = '$email' ";
    $result = mysqli_query($conn,$query);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $user = $row['first_name']." ".$row['last_name'];

            try{
                $mail = new PHPMailer(true);
                $smtp = true;
            
                $username = "";
                $password = "";
                
                if($smtp){
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.hostinger.in';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    //$mail->SMTPSecure = 'ssl';
                    $mail->Port       = 587;
                    $username         = "contact@pranamthankyou.org";
                    $password         = "PranamThankYou@5432";
                }       
               
                $mail->SMTPAuth   = $smtp;
                $mail->Username   = $username; // SMTP username
                $mail->Password   = $password; // SMTP password
                // echo $user."=".$username."=".$password;
                // exit;
                $mail->setFrom("contact@pranamthankyou.org", 'Pranam-ThankYou');
                $mail->addAddress($email); // Add a recipient
                $mail->addReplyTo("contact@pranamthankyou.org", 'Pranam-ThankYou');
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset';
                $body = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td bgcolor="#c20a0a" align="center">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>
                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <tr>
                        <td bgcolor="#c20a0a" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                        <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Welcome!</h1>
                                        <img src="https://pranamthankyou.org/images/logo.png" 
                                        width="125" style="display: block; border: 0px;" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#ffffff" align="left" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                        <p style="margin-left: 4px; text-align: center;">
                                            Hi '.$user.', <br/>You have requested to reset your password.
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td bgcolor="#ffffff" align="left">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center" style="border-radius: 3px;" bgcolor="#c20a0a">
                                                                <a href="'.$finalUrl.'/'.$type.'/reset-password.php?token='.$token.'"
                                                                    style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #fce8e6; display: inline-block;">
                                                                    Reset Password
                                                                </a>
                                                                
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 30px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#fce8e6" align="center" style="padding: 30px 30px 30px 30px; border-radius: 4px 4px 4px 4px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                        <h2 style="font-size: 20px; font-weight: 400; color: #111111; margin: 0;">Need more help?</h2>
                                        <p style="margin: 0;"><a href="'.$finalUrl.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 30px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                        <p style="margin: 0; text-align: center;">In need of any kind of support, please feel free to drop an email at contact@pranamthankyou.org<a href="#" target="_blank" style="color: #111111; font-weight: 700;"></a>.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>';
                //$mail->Body    = 'Hi '.$user.', <br/>You have requested to reset your password.<br/><br/><div style="width: 100%;"><a href="'.$finalUrl.'/'.$type.'/reset_password.php?token='.$token.'"><input type="button" style="background-color: #1e73be; color:#fff; cursor: pointer" value="Reset Password" /></a></div>';
                //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                $mail->Body      = $body;
                if($mail->send()){
                    $_SESSION['emailCheck'] = $email;
                    $data['status'] = 'Success';
                    $data['message'] = '<span class="text-success mt-3" style="font-size: .8125rem;">*Reset password link is sent to your email.</span>';
                    //return true;
                }
                else{
                    $data['status'] = 'failed';
                    $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Error in mail sending. Please try after some time.</span>';
                    //return false;
                }
            }
            catch(Exception $e){   
                echo $e->getMessage(); //Boring error messages from anything else!
                $data['status'] = 'failed';
                $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Error in mail sending. Please try after some time.</span>';
                //return false;
            }
        }
    }
    else{
        $data['status'] = 'failed';
        $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Your email id is not registered with us.</span>';
    }
    echo json_encode($data);
}

else if($operation == "sendOTP"){

    $data = array();
    $email = $_POST['email'];

     //Create random String
    function get_name($n){ 
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $random_string = ''; 
        for ($i = 0; $i < $n; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $random_string .= $characters[$index]; 
        }  
        return $random_string; 
    }
    //getname function closed

    //Unique code check function
    function unique_check($conn,$unique){
        $is_unique = false;
        while(!$is_unique){
            $result = mysqli_query($conn,"SELECT `guest_token` FROM `generated_cards` WHERE guest_token = '".$unique."' ");
            if($result->num_rows > 0){
                $unique = get_name(15);//Generate new randam string
            }        
            else{
                $is_unique = true; 
            }       
        }
        return $unique; //Return unique key
    }

    $unique = get_name(15);
    $unique = unique_check($conn, $unique);

    $y = mysqli_query($conn,"UPDATE `generated_cards` SET `guest_token` = '$unique' WHERE `user_email` = '".$email."' AND `user_unique_code` IS NOT NULL ");
    
    $query = "SELECT `guest_token` FROM `generated_cards` WHERE `user_email` = '".$email."' AND `user_unique_code` IS NOT NULL ";
    $result = mysqli_query($conn,$query);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
        }
        try{
            $mail = new PHPMailer(true);
            $smtp = true;
        
            $username = "";
            $password = "";
            
            if($smtp){
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.in';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                //$mail->SMTPSecure = 'ssl';
                $mail->Port       = 587;
                $username         = "contact@pranamthankyou.org";
                $password         = "PranamThankYou@5432";
            }       
           
            $mail->SMTPAuth   = $smtp;
            $mail->Username   = $username; // SMTP username
            $mail->Password   = $password; // SMTP password
            // echo $user."=".$username."=".$password;
            // exit;
            $mail->setFrom("contact@pranamthankyou.org", 'Pranam-ThankYou');
            $mail->addAddress($email); // Add a recipient
            $mail->addReplyTo("contact@pranamthankyou.org", 'Pranam-ThankYou');
            $mail->isHTML(true);
            $mail->Subject = 'Email verification code';
            $body = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td bgcolor="#c20a0a" align="center">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                
                <tr>
                    <td bgcolor="#c20a0a" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                    <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Welcome!</h1>
                                    <img src="https://pranamthankyou.org/images/logo.png" 
                                    width="125" style="display: block; border: 0px;" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin-left: 4px; text-align: center;">
                                        Hi '.$email.', <br/><br/>Please verify below code to download cards.
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td bgcolor="#ffffff" align="left">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                                                <table border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="center" style="border-radius: 3px;" bgcolor="#c20a0a">
                                                            <p
                                                                style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #fce8e6; display: inline-block;">
                                                                '.$unique.'
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 30px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#fce8e6" align="center" style="padding: 30px 30px 30px 30px; border-radius: 4px 4px 4px 4px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <h2 style="font-size: 20px; font-weight: 400; color: #111111; margin: 0;">Need more help?</h2>
                                    <p style="margin: 0;"><a href="'.$finalUrl.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 30px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                    <p style="margin: 0; text-align: center;">In need of any kind of support, please feel free to drop an email at contact@pranamthankyou.org<a href="#" target="_blank" style="color: #111111; font-weight: 700;"></a>.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>';
            //$mail->Body    = 'Hi '.$user.', <br/>You have requested to reset your password.<br/><br/><div style="width: 100%;"><a href="'.$finalUrl.'/'.$type.'/reset_password.php?token='.$token.'"><input type="button" style="background-color: #1e73be; color:#fff; cursor: pointer" value="Reset Password" /></a></div>';
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->Body      = $body;
            if($mail->send()){
                $data['status'] = 'Success';
                $data['message'] = '<span class="text-success mt-3" style="font-size: .8125rem;">We sent a verification code.</span>';
                //return true;
            }
            else{
                $data['status'] = 'failed';
                $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Error in mail sending.</span>';
                //return false;
            }
        }
        catch(Exception $e){   
            echo $e->getMessage(); //Boring error messages from anything else!
            $data['status'] = 'failed';
            $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Error in mail sending. Please try after some time.</span>';
            //return false;
        }
    }
    else{
        $data['status'] = 'failed';
        $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Please enter valid email.</span>';
    }
    echo json_encode($data);
}

else if($operation =="checkToken"){
    $response = array();
    $otp = $_POST['otp'];
    $stmt = $pdo_conn->prepare("SELECT * from `generated_cards` WHERE `guest_token` = ? AND `user_unique_code` IS NOT NULL LIMIT 1");
    $stmt->bind_param("s",$otp);
    $stmt->execute();
    $result = get_result($stmt);
    if(count($result)>0){
        while($row = array_shift( $result)) {  
            $_SESSION['guestStatus'] = "Yes";
            $_SESSION['guest_email'] = $row['user_email'];
            $response['status'] = 'Success';
            $response['message'] = $row['user_email'];
            $stmt = $pdo_conn->prepare("UPDATE `generated_cards` SET `guest_token` = '' WHERE `guest_token` = ? ");
            $stmt->bind_param("s",$otp);
            $stmt->execute();
        }
    }else{
        $response['status'] = 'Failed';
        $response['message'] = '<label class="text-danger" style="font-size: .8125rem;">*Please enter valid code and try again.</label>';
    }
    echo json_encode($response);
    exit();
}

?>