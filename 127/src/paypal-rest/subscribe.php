<?php 
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include '../server/vendor/autoload.php';

$is_secure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $is_secure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $is_secure = true;
}
else{}

$is_https = $is_secure ? 'https' : 'http';
$host_name = $_SERVER['HTTP_HOST'];
$final_url = $is_https.'://'.$host_name;

if($host_name=="localhost"){
    $final_url = $final_url."/projects-sm/127/src";
}else{
    $final_url = $final_url;
}

$s_id = isset($_REQUEST['s_id']) ? $_REQUEST['s_id'] : '';
$sub_id = isset($_SESSION['subscriber_id']) ? $_SESSION['subscriber_id'] : '';

$response = array(
        "status" => "error",
        "message" => "There is some issue in your subscription. Please try again later."
);

$uri = "https://pranamthankyou.org/paypal-rest/ipn.php";

if(!empty($s_id) && !empty($sub_id)){
    
    include_once '../server/config.php';
    include_once 'functions.php';
    // Get Token
    $token = getToken();
    // Add headers
    $headers[] = "Authorization: Bearer ".$token."";
    $subscription = simple_curl("/v1/billing/subscriptions/".$s_id, 'GET', null , $headers);
    
    if(!empty($subscription->id) && ($subscription->id == $s_id) && ($subscription->status == "ACTIVE")){
        // Process Database Queries
        $user_id = $sub_id;
        // Create Webhook if not exists
        $exists = FALSE;
        $webhooks = simple_curl("/v1/notifications/webhooks", 'GET', null , $headers);
        foreach($webhooks->webhooks as $webhook){
            if($webhook->url == $uri){
                $exists = TRUE;
            }
        }
        
        if(!$exists){
            $data = array(
                "id"=>"PRANAM2",
                "url"=>$uri,
                "event_types"=>[
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.CANCELLED"
                    ),
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.CREATED"
                    ),
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.SUSPENDED"
                    ),
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.RE-ACTIVATED"
                    ),
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.EXPIRED"
                    ),
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.PAYMENT.FAILED"
                    ),
                    array(
                        "name"=>"BILLING.SUBSCRIPTION.UPDATED"
                    )
                ]
            );
            $webhook = simple_curl("/v1/notifications/webhooks", 'POST', json_encode($data) , $headers);            
        }
        // Check for existing subscription // Cancel it
        
        $stmt = $pdo_conn->prepare("SELECT * FROM `users_plan` WHERE `user_id` = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();        
        $result = get_result($stmt);
        if(count($result)>0){ 
            while($row = array_shift( $result)) { 
                $now = strtotime("now");
                $expiry = strtotime($row['expiry_date']);
                
                if($now <= $expiry){
                    $data = array("reason"=>"Registered for a fresh plan");
                    $cancel = simple_curl("/v1/billing/subscriptions/".$row['subscription_id']."/cancel", 'POST',json_encode($data) , $headers);
                    
                }                
            }
        }
        
        $plan_id = 1;
        $expiry_date = $subscription->billing_info->next_billing_time;
        $expiry_date = date('Y-m-d H:i:s',strtotime($expiry_date));
        $subscription_id = $subscription->id;
       
        $stmt = $pdo_conn->prepare("INSERT INTO users_plan(user_id,plan_id,expiry_date,subscription_id) VALUES (?,?,?,?)");        
        $stmt->bind_param('iiss',$user_id,$plan_id,$expiry_date,$subscription_id);
        if($stmt->execute()){
            $response['status'] = 'success';
            $response['message'] = "Thank you for becoming a part of Pranam-ThankYou family.<br/><br/>Please wait to access your account.";
            unset($_SESSION['subscriber_id']);
            
            $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `id` = ? ");
            $stmt->bind_param('i',$user_id);
            $stmt->execute();
            $result = get_result($stmt);
            $email = "";
            if(count($result)>0){ 
                while($row = array_shift( $result)) {  
                    $name           = $row['first_name']." ".$row['last_name'];
                    $email          = $row['email_id'];
                    $email_sub_code = $row['email_sub_code'];
                    $bcrypt_password = $row["password"];
                    $email           = $row["email_id"];
                    $first_name      = $row["first_name"];
                    $last_name       = $row["last_name"];
                    $user_type       = $row["user_type"];
                    //$userid = $row['id'];
                    $user_id         = $row['id'];
                    $status          = $row['status'];
                    $logo            = $row['logo'];
                    $email_subscritpion = $row['email_subscritpion'];
                }
                $message = "Thank you ".$name." for being part of Pranam-ThankYou family.<br/> Your account is activated, please login."; 
                $subject = "Contributor Register";
                $header  = "Welcome"; 
                //Check if email is not empty
                if(!empty($email)){
                    mailFuntion($email, $subject, $header, $message, $email_sub_code,$final_url);
                }
            }
            
            if($status=='A'){         
                // $_SESSION["email"] = $email;
                // $_SESSION["username"] = $first_name." ".$last_name;
                // $_SESSION['userid'] = $user_id;
                // $_SESSION['registered_on'] = $registered_on;
                // $_SESSION['usertype'] = $user_type;
                // $_SESSION['logo'] = $logo;
                $_SESSION["email"] = $email;
                $_SESSION["user_name"] = $first_name." ".$last_name;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['logo'] = $logo;
                
                $response['success'] = true;
                $response['message'] = "Membership plan activated successfully. Redirecting to your account...";
                $header = "Welcome";
                $message = 'You have logged in to your account.';
                $subject = "Contributor Login";
                //Send mail function

                if($email_subscritpion=="Y"){
                    mailFuntion($email, $subject, $header, $message,$email_sub_code,$final_url);
                }
            }
        }
    }else{
        // Send Invalid Response // Return response
    }
}else{  
    // Send Invalid Response // Return response
    
}
//Main function
function mailFuntion($email, $subject, $header, $message, $email_sub_code, $final_url){

    $mail = new PHPMailer(true);
    try{
        $mail->SMTPDebug = 0;  
        $mail->isSMTP();       
        $mail->Host       = 'smtp.hostinger.in';//Set the SMTP server to send through
        $mail->SMTPAuth   = true;            // Enable SMTP authentication
        $mail->Username   = 'contact@pranamthankyou.org'; // SMTP username
        $mail->Password   = 'PranamThankYou@5432';                // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('contact@pranamthankyou.org', 'Pranam-ThankYou');
        $mail->addAddress($email);     // Add a recipient
        $mail->addReplyTo('contact@pranamthankyou.org', 'Pranam-ThankYou');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);
        // echo $username."==".$email;
        // exit;                                  
        $mail->Subject = $subject;
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
                                    <h1 style="font-size: 48px; font-weight: 400; margin: 2;">'.$header.'!</h1>
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
                                <td bgcolor="#ffffff" align="center" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin-left: 4px; text-center;">
                                        '.$message.'
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
                                                        <td align="center" style="border-radius: 3px;" bgcolor="#FFA73B">
                                                                                                                                
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
                                    <p style="margin: 0;"><a style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#f4f4f4" align="center" style="padding: 30px 30px 30px 30px; border-radius: 4px 4px 4px 4px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">If these emails get annoying , please click <a href="'.$final_url.'/unsubscribe.php?code='.$email_sub_code.'" target="_blank" style="color: #c20a0a;">here </a>to opt out.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>';
        $mail->Body    = $body;
        if($mail->send()){
            return true;
        }
        else{
            return false;
        }
    }
    catch(Exception $e){   
      //echo $e->getMessage(); //Boring error messages from anything else!
        $data['status'] = 'failed';
        $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Message could not be sent.</span>';
        return true; 
    }
}

echo json_encode($response);
?>
