<?php 
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include '../server/vendor/autoload.php';
require("../server/config.php");

$final_url   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
$host_name = $_SERVER['HTTP_HOST'];
$final_url = $final_url.$host_name;

if($host_name=="localhost"){
    $final_url = $final_url."/projects-sm/127/src";
}else{
    $final_url = $final_url;
}

$order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
$guest_details = isset($_REQUEST['guest_details']) ? $_REQUEST['guest_details'] : '';
//$order_id = "90259095RE357005A";
$response = array(
    "status" => "error",
    "message" => "There is some issue in your payment. Please try again later."
);

$uri = "https://pranamthankyou.org/paypal-rest/ipn.php";

if(!empty($order_id)){
    
    include_once '../server/config.php';
    include_once 'functions.php';
    // Get Token
    $token = getToken();
    // Add headers
    $headers[] = "Authorization: Bearer ".$token."";
    //$subscription = simple_curl("/v1/billing/subscriptions/".$s_id, 'GET', null , $headers);
    $order = simple_curl("/v2/checkout/orders/".$order_id, 'GET', null , $headers);

    if(!empty($order->id) && ($order->id == $order_id) && ($order->status == "COMPLETED")){
        // Process Database Queries
        //User Id 
        
        $user_id     = isset($_SESSION['user_id'])?$_SESSION['user_id']:"";
        $payment_amt = "" ;//Fetch amount from paypal response
        $payment_amt = $order->purchase_units[0]->amount->value;

        //Create random String
        function getName($n) { 
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
            $randomString = ''; 
            for ($i = 0; $i < $n; $i++) { 
                $index = rand(0, strlen($characters) - 1); 
                $randomString .= $characters[$index]; 
            }  
            return $randomString; 
        }//getname function closed

        //Unique code check function
        function uniqueCheck($conn,$unique){
            $is_unique = false;
            while(!$is_unique){
                $result = mysqli_query($conn,"SELECT unique_card_no FROM generated_cards WHERE unique_card_no = '".$unique."' ");
                if($result->num_rows > 0){
                    $unique = getName(8);//Generate new randam string
                }else{
                    $is_unique = true; 
                }       
            }
            return $unique; //Return unique key
        }// Function uniquecheck Closed
        
        //Save payments details in database
        if($user_id!=""){//Logged in user
            //Amount detail
            $amount       = isset($_POST['payment_details']['amount'])?$_POST['payment_details']['amount']:0; // Donation amount
            $d_amount     = isset($_POST['payment_details']['donateAmount'])?$_POST['payment_details']['donateAmount']:"";
            $no_cards     = isset($_POST['payment_details']['giftCardAmt'])?$_POST['payment_details']['giftCardAmt']:"";
            //Decode json data
            $no_cards     = json_decode($no_cards);
            //Remove stdclass object
            $no_cards     = json_decode(json_encode($no_cards), true);
            
            //Amount calculation between front side and server side 
            if(($amount+$d_amount) <= $payment_amt ){
                $amount = $amount;
            }else{
                $amount = $payment_amt ;
            }

            $type     = "Regular";
            $isRedeem = 'No';
            $status   = 'New';
            
            $stmt = $pdo_conn->prepare("INSERT INTO `contributions` 
                    (`payment_order_id`, `user_id`, `contributor_type`, `amount`,`donation_amount`,
                      `datetime`,`payment_status`) VALUES 
                    ( ?,?, ?, ?, ?, UNIX_TIMESTAMP(),? )");
            $stmt->bind_param('sissss',$order_id, $user_id, $type, $amount, $d_amount, $status);
            if($stmt->execute()){
                $_SESSION['last_insert_id'] = $stmt->insert_id;
                for($i=0; $i<count($no_cards); $i++){
                    for($j=0; $j<$no_cards[$i]['card']; $j++){
                        $unique = getName(8);
                        $unique = uniqueCheck($conn,$unique);
                        $stmt = $pdo_conn->prepare("INSERT INTO `generated_cards` 
                                                ( `payment_order_id`, `user_email`, `unique_card_no`, 
                                                    `is_reedemed`, `status`, 
                                                    `generated_on`, `user_id`, 
                                                    `card_amount` ) 
                                                VALUES (?,?, ?, ?, ?, NOW(), ?, ? )");
                        $stmt->bind_param('sssssii',$order_id, $_SESSION['email'], $unique, $isRedeem, $status, $user_id, $no_cards[$i]['amount'] );
                        if($stmt->execute()){
                            $_SESSION['last_insert_id'] = $stmt->insert_id;
                        }
                    }

                    //Fetching Remind date
                    $name = "";//user name
                    $stmt = $pdo_conn->prepare("SELECT `email_sub_code`, `email_subscritpion`,`email_id`, `remind_cards`, `first_name`, `last_name` from `users` WHERE `id` = ? ");
                    $stmt->bind_param("i",$user_id);
                    $stmt->execute();

                    $result = get_result($stmt);
                    if(count($result)>0){ 
                        while($row = array_shift( $result)){  
                            $old_remind_cards = $row['remind_cards']; //Blance remind cards
                            $name = $row['first_name']." ".$row['last_name'];
                            $email = $row['email_id'];
                            $email_subscritpion = $row['email_subscritpion'];
                            $email_sub_code = $row['email_sub_code'];
                        }
                        $response = array(
                                    "status" => "successGuest",
                                    "message" => "Your payment has been Successful."
                                );
                        if($i == (count($no_cards)-1)){
                            $subject = "Payment Successful";
                            //$message = "Your payment has been successful. $" .$amount;
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
                                                            <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Congratulations!</h1>
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
                                                                Hi <b>'.ucwords($name).',</b><br/>
                                                                thank you for your contribution.
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
                                                            <p style="margin: 0;"><span style="color: #c20a0a;">We&rsquo;re here to help you out</span></p>
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
                                                            <p style="margin: 0;">If these emails get annoying, please click <a href="'.$final_url.'/unsubscribe.php?code='.$email_sub_code.'" target="_blank" style="color: #c20a0a;">here</a> to opt out.</p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>';
                            if($email_subscritpion=="Y"){
                                mailFuntion($email, $subject, $body,$email_sub_code,$final_url);
                            }
                        }
                    }

                    $_SESSION['user_payment_status'] = "Yes";

                    //Save sponsor cards
                    // $amount = $amount/2;
                    // $amount = (int)($amount/5);
                    // $us_id  = "";
                    // $sponsor_id = "";
                    
                    // $stmt = $pdo_conn->prepare("SELECT * from `user_sponsor` WHERE `user_id` = ? ");
                    // $stmt->bind_param("i",$user_id);
                    // $stmt->execute();
                    // $result = get_result($stmt);
                    // if(count($result)>0){//If result found 
                    //     while($row = array_shift( $result)){  
                    //         //user_sponsors table id
                    //         $us_id = $row['id'];
                    //         //users table id
                    //         $sponsor_id = $row['sponsor_id'];
                    //         //Sponsor id
                    //         $sponsor_user_id = $row['user_id'];
                    //     }
                    // }

                    // if(!empty($us_id) && !empty($sponsor_id)){
                    //     //Fetch sponsor user cards
                    //     for($i=1; $i<= $amount; $i++){
                    //         $sponsor_card = 5;
                    //         $stmt = $pdo_conn->prepare("INSERT INTO `sponsor_cards` (`user_id`,`referral_id`,`award_cards`,`datetime`) VALUES (?,?,?,UNIX_TIMESTAMP() )");
                    //         $stmt->bind_param('iii',$sponsor_id,$sponsor_user_id,$sponsor_card);
                    //         $stmt->execute();

                    //         $unique = getName(8);
                    //         $unique = uniqueCheck($conn,$unique);
                    //         $stmt = $pdo_conn->prepare("INSERT INTO `generated_cards` 
                    //                                     ( `user_email`, `unique_card_no`, 
                    //                                         `is_reedemed`, `status`, 
                    //                                         `generated_on`, `user_id` , 
                    //                                         `card_amount` ) 
                    //                                     VALUES (?, ?, ?, ?, NOW(), ?, ? )");
                    //         $stmt->bind_param('ssssii',$email, $unique, $isRedeem, $status, $sponsor_id, $sponsor_card );
                    //         $stmt->execute();
                    //     }
                    // }//Save Sponsor cards end
                }
            }
        }
        //Guest details save in DB
        else{
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

            //Gueast all detail
            $amount  = $guest_details['amount'];//Number of cards
            $f_name  = $guest_details['first_name'];
            $l_name  = $guest_details['last_name'];
            $email   = !empty($guest_details['email'])?$guest_details['email']:"";
            $phone   = $guest_details['phone'];
            $d_amount= isset($guest_details['donate_amount'])?$guest_details['donate_amount']:0; // Donation amount
            $no_cards= isset($guest_details['giftCardAmt'])?$guest_details['giftCardAmt']:"";
            //Decode json data
            $no_cards = json_decode($no_cards);
            //Remove stdclass object
            $no_cards = json_decode(json_encode($no_cards), true);
            $type    = "Guest";
            $pay_status  = "Completed";
            //Get amount for cards

            $website_fee = constant("WEBSITE_FEE");
            if($amount <= $payment_amt ){
                $amount = $amount-$d_amount-$website_fee;
            }
            else{
                $amount = $payment_amt-$website_fee;
                $amount = $amount-$d_amount;
            }
            //Download card manage this session
            $_SESSION['guestAmt']    = $amount;
            if(isset($_SESSION['get_one_card'])){
                if($_SESSION['get_one_card'] == "Yes" ){
                    $_SESSION['guestAmt'] = $amount+5;
                }
                unset($_SESSION['get_one_card']);
            }
            $_SESSION['guestStatus'] = "Yes";

            $timestamp = time();

            $isRedeem = 'No';
            $status   = 'New';
            $stmt = $pdo_conn->prepare("INSERT INTO `contributions` (`payment_order_id`, `contributor_type`,`guest_first_name`,`guest_last_name`,`guest_email`,`guest_phone`,`amount`,`donation_amount`,`datetime`,`payment_status`, `user_unique_code`, `website_fee` ) VALUES (?,?,?,?,?,?,?,?,UNIX_TIMESTAMP(),?, ?, ? )");
            $stmt->bind_param('sssssssssis',$order_id, $type,$f_name,$l_name,$email,$phone,$amount,$d_amount,$pay_status,$timestamp, $website_fee);
            if($stmt->execute()){
                for($i=0; $i<count($no_cards); $i++){
                    for($j=0; $j<$no_cards[$i]['card']; $j++){
                        $unique = getName(8);
                        $unique = uniqueCheck($conn,$unique);
                        $stmt = $pdo_conn->prepare("INSERT INTO `generated_cards`( `user_email`, `unique_card_no`, `is_reedemed`, 
                                                                                    `status`, `generated_on`, 
                                                                                    `user_unique_code`, 
                                                                                    `card_amount`, `payment_order_id` ) 
                                                    VALUES (?, ?, ?, ?, NOW(), ?, ?, ? )");
                        $stmt->bind_param('ssssiis',$email, $unique, $isRedeem, $status, $timestamp, $no_cards[$i]['amount'], $order_id );
                        if($stmt->execute()){
                            $response = array(
                                "status" => "successGuest",
                                "message" => "Your payment has been Successful."
                            );
                            $_SESSION['last_insert_id'] = $stmt->insert_id;
                            $_SESSION['guestStatus'] = "Yes";
                            $_SESSION['paymentStatus'] = "";
                            $_SESSION['guest_email'] = $email;
                            $_SESSION['unique_user_id'] = $timestamp;
                        }                    
                    }
                    if($i == (count($no_cards)-1)){
                        //Mail Message/
                        $message = "Your payment has been Successful.";
                        $name  = $f_name." ".$l_name;//Guest name
                        $subject = "Payment Successful";//Mail Subject
                        
                        //Mail Body
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
                                                        <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Congratulations!</h1>
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
                                                            Hi <b>'.ucwords($name).',</b><br/>
                                                            thank you for your contribution.
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
                                                                                <a href="'.$finalUrl.'/cards-download.php"
                                                                                    style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #fce8e6; display: inline-block;">
                                                                                    Download cards
                                                                                </a>
                                                                                
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
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
                                                        <p style="margin: 0;"><span style="color: #c20a0a;">We&rsquo;re here to help you out</span></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                                <tr>
                                                    <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; margin-bottom:20px; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                                        <p style="margin-bottom:30px; text-align:center;">In need of any kind of support, please feel free to drop an email at contact@pranamthankyou.org<a href="#" target="_blank" style="color: #111111; font-weight: 700;"></a>.</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                </table>';
                        if(!empty($email)){
                            mailFunction($email, $subject, $body);  
                        }
                    }
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
function mailFuntion($email, $subject, $message, $unique_email, $final_url){
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
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        if($mail->send()){
            return true;
        }
        else{
            return false;
        }
    }
    catch(Exception $e){   
        echo $e->getMessage(); //Boring error messages from anything else!
        //exit;
    }
}

function mailFunction($email, $subject, $message){
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
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        if($mail->send()){
            return true;
        }
        else{
            return false;
        }
    }
    catch(Exception $e){   
        echo $e->getMessage(); //Boring error messages from anything else!
        //exit;
    }
}
//Mail function
echo json_encode($response);
?>

