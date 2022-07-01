<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include '../server/vendor/autoload.php';
session_start();

if(isset($_POST["operation"])){
	$operation = $_POST["operation"]; 
	require("./config.php");
    require( './ssp.class.php' ); 
}else{
	exit();
} 


$final_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
//$full_url  = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$host_name = $_SERVER['HTTP_HOST'];
if($host_name=="localhost"){
    $final_url = $final_url."/projects-sm/127/src";
}else{
    $final_url = $final_url;
}

$_SESSION['logged'] = "failed";
 
if($operation == "users_list_details"){  
}           
//To change status of user by id
else if($operation == "change_status"){
    $response = array();
    $id = $_POST['id'];
    $stmt = $pdo_conn->prepare("UPDATE `users` SET status = CASE status WHEN 'A' THEN 'I' ELSE 'A' END WHERE `id` = ?");
    $stmt->bind_param('i',$id);
    if($stmt->execute()){
        $response['status'] = 'Success';
        $response['message'] = 'Status updated successfully.';
    }else{
        $response['status'] = 'Failed';
        $response['message'] = 'Status could not be changed. Please try againafter sometime.';
    }
    echo json_encode($response);
}// Change Status

else if($operation == 'saveUser'){   
    
    $response  = array();
    $first_name= $_POST["firstName"]; 
    $last_name = $_POST['lastName'];
    $email     = isset($_POST["email"])?strtolower($_POST["email"]):"";
    $phone     = $_POST["phone"];
    $password  = $_POST['password'];
    $company   = $_POST['company'];
    $user_type = "redeemer";
    $result = mysqli_query($conn,"SELECT * FROM `users` WHERE `email_id` ='$email' AND `user_type` = '$user_type' ");
    $num_rows = mysqli_num_rows($result);
    
    if($num_rows >= 1){
        $response['status'] = 'Email_Taken';
    }
    else{

        function get_name($n){ 
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
            $random_string = ''; 
            for ($i = 0; $i < $n; $i++) { 
                $index = rand(0, strlen($characters) - 1); 
                $random_string .= $characters[$index]; 
            }  
            return $random_string; 
        }

        function email_code($n){ 
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
            $string = ''; 
            for ($i = 0; $i < $n; $i++) { 
                $index = rand(0, strlen($characters) - 1); 
                $string .= $characters[$index]; 
            }  
            return $string; 
        }
        //getname function closed

        //Unique code check function
        function unique_check($conn,$unique_email){
            $is_unique = false;
            while(!$is_unique){
                $result = mysqli_query($conn,"SELECT email_sub_code FROM users WHERE email_sub_code = '".$unique_email."' ");
                if($result->num_rows > 0){
                    $unique_email = get_name(12);//Generate new randam string
                }        
                else{
                    $is_unique = true; 
                }       
            }
            return $unique_email; //Return unique key
        }

        $unique_email = get_name(12);
        $unique_email = unique_check($conn,$unique_email);
        
        $encypt_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo_conn->prepare("INSERT INTO `users` (`first_name`,`last_name`,`email_id`,`password`,`phone`,`user_type`,`registered_on`,`company`,`email_sub_code`) VALUES (?,?,?,?,?,?,UNIX_TIMESTAMP(),?,?)");
        $stmt->bind_param('ssssssss',$first_name,$last_name,$email,$encypt_password,$phone,$user_type,$company,$unique_email);
        if($stmt->execute()){   //if query executed successfully
          $last_id = $stmt->insert_id;
          $_SESSION['subscriber_id']=$last_id;
            $response['status'] = 'Success';
            $name = $first_name." ".$last_name;
            
            //$message = "Username <b>".$name."</b> and email ".$email."</b><br/> Redeemer account register successfully.";
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
                                            <p style="margin-left: 4px; text-align: center">
                                                Thank you '.$name.' for being part of Pranam-ThankYou family.<br/>Your account is activated, please login.
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
                                            <p style="margin: 0;"><a href="'.$final_url.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
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
                                            <p style="margin: 0;">If these emails get annoying, please click <a href="'.$final_url.'/unsubscribe.php?code='.$unique_email.'" target="_blank" style="color: #c20a0a;">here</a> to opt out.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>';
            //$message = "Username <b>".$name."</b> and email ".$email."</b><br/> contributor account register successfully."; 
            
            //Check if last_id is not empty 
            $subject = "Redeemer Register";
            //Send mail function
            $email_subscritpion = "Y";
            if($email_subscritpion=="Y"){
                "mailFuntion"($email, $body, $subject, $unique_email,$final_url);

                    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `id` = ? AND `user_type` = ?  ");  
                    $stmt->bind_param("is",$last_id,$userType);
                    $stmt->execute();
                    $result = get_result($stmt);  
                    if(count($result)>0){
                        while($row = array_shift( $result)) {  
                            $bcrypt_password    = $row["password"];
                            $email              = $row["email_id"];
                            $first_name         = $row["first_name"];
                            $last_name          = $row["last_name"];
                            $user_type          = $row["user_type"];
                            $user_id            = $row['id'];
                            $registered_on      = $row['registered_on'];
                            $user_id            = $row['id'];
                            $status             = $row['status'];
                            $logo               = $row['logo'];
                            
                            //$date = $row['expiry_date'];
                        }
                            $_SESSION["email"]    = $email;
                            $_SESSION["user_name"] = $first_name." ".$last_name;
                            $_SESSION['user_id']   = $user_id;
                            $_SESSION['registered_on'] = $registered_on;
                            $_SESSION['user_type'] = $user_type;
                            $_SESSION['logo']     = $logo;
                            unset($_SESSION['is_front_page']);
                    }

                $response['status'] = 'Success';
            }

            $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `id` = ? ");
            $stmt->bind_param('i',$last_id);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){ 
                while($row = array_shift( $result)) {  
                    $name            = $row['first_name']." ".$row['last_name'];
                    $email           = $row['email_id'];
                    $first_name      = $row["first_name"];
                    $last_name       = $row["last_name"];
                    $user_type       = $row["user_type"];
                    $user_id         = $row['id'];
                    $status          = $row['status'];
                    $logo            = $row['logo'];
                }
            }

            if($status=='A'){         
                $_SESSION["email"] = $email;
                $_SESSION["user_name"] = $first_name." ".$last_name;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['logo'] = $logo;
            }
        }
        else{
            $response['status'] = 'Error';
        }
    }
    echo json_encode($response);
}// Save User

//forgot Password
else if($operation == "forgotPassword"){        
    $response = array();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $type   =  'redeemer';
    
    $encypt_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo_conn->prepare("UPDATE `users` SET `password` = ? WHERE `email_id` = ? AND `user_type` = ? ");
    $stmt->bind_param('sss',$encypt_password,$email,$type);
    //if($stmt->execute()){
        $response['status'] = 'Success';
        //$stmt = $pdo_conn->prepare("UPDATE `users` SET `reset_link` = '' WHERE `email_id` = ? AND `user_type` = ? ");
        //$stmt->bind_param('ss',$email,$type);
        if($stmt->execute()){
            $response['status'] = 'Success';
        }
    //}
    else{
        $response['status'] = 'Failed';
    }
    echo json_encode($response);
}//forgotPassword

else if($operation == 'auth'){
 
    $originalpassword = '';
    $response = array();
 
    if(!empty($_REQUEST['data'])){
        $data = $_REQUEST['data'];
        $email = strtolower($data['s_email']);
        $password = $data['s_password'];
        $userType = 'redeemer';
        $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `email_id` = ? AND `user_type` = ?  ");  
        $stmt->bind_param("ss",$email,$userType);
        $stmt->execute();
        $result = get_result($stmt);  
        if(count($result)>0){
            while($row = array_shift( $result)) {  
                $bcrypt_password    = $row["password"];
                $email              = $row["email_id"];
                $first_name         = $row["first_name"];
                $last_name          = $row["last_name"];
                $user_type          = $row["user_type"];
                $user_id            = $row['id'];
                $registered_on      = $row['registered_on'];
                $user_id            = $row['id'];
                $status             = $row['status'];
                $logo               = $row['logo'];
                $email_sub_code     = $row['email_sub_code'];
                $email_subscritpion = $row['email_subscritpion'];
                //$date = $row['expiry_date'];
            }

            if( password_verify( $password ,$bcrypt_password)){                    
                if($status=='A' ){         
                    $_SESSION["email"]    = $email;
                    $_SESSION["user_name"] = $first_name." ".$last_name;
                    $_SESSION['user_id']   = $user_id;
                    $_SESSION['registered_on'] = $registered_on;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['logo']     = $logo;
                    unset($_SESSION['is_front_page']);
                    
                    $response['success'] = true;
                    $response['message'] = "Logged in successfully. Redirecting to your account...";
                    
                    $body = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td bgcolor="#c20a0a" align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                            <tr>
                                                <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> 
                                                </td>
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
                                                       You have logged in to your account.
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
                                                    <p style="margin: 0;"><a href="'.$final_url.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
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
                    $subject = "Redeemer Login";
                    //Send mail function
                    //mailFuntion($email, );
                    if($email_subscritpion=="Y"){
                        mailFuntion($email, $body, $subject ,$email_sub_code,$final_url);
                    }
                    
                }
                else {  
                    $response['success'] = false;
                    $response['message'] = 'This account is disabled, please contact to admin.';
                }   
            }else{
                $response['error']="wrongpassword";
                $response['message'] = 'Authentication failed. Wrong credentials.'; 
            }
        } 
        else{
            $response['error'] = true;
            $response['message'] = 'Email id is not registered. Please register!';
        }
    }
    echo json_encode($response);
} // Auth Operations

else if($operation == 'redeemCard'){

    $comman_msg  = ""; 
    $flag  = true;
    $allow_home  = $_POST['allow_home'];

    $redeem_card= isset($_POST['redeemCard'])?$_POST['redeemCard']:"";
    //Decode json data
    $redeem_card = json_decode($redeem_card);
    //Remove stdclass object
    $redeem_card_arr = json_decode(json_encode($redeem_card), true);
    $len_arr = count($redeem_card_arr);
    
    //Check redeem card validation
    for($i = 0; $i<$len_arr; $i++ ){
       $custom_msg = $redeem_card_arr[$i]["message"];
       $card_number = $redeem_card_arr[$i]["card_number"];
       
        if(!empty($custom_msg)){
            $comman_msg = "Redeemer has left you note,";
            $custom_msg = "&ldquo; ".$custom_msg." &rdquo;";
        }

        $response = array();
        if($card_number == '' ){
            $response['status'] = 'Failed';
            $response['message'] = 'Please enter card number.';
            echo json_encode($response);
            exit();
        }
        else {   

            $is_reedemed = "Yes";
            $stmt = $pdo_conn->prepare("SELECT * from `generated_cards` WHERE `unique_card_no` = ? AND `is_reedemed` = ?  ");
            $stmt->bind_param("ss",$card_number,$is_reedemed);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                $response['status'] = 'Failed';
                $response['message'] = '<b>'.$card_number.'</b> Card code already redeemed! Please check and try again.';
                echo json_encode($response);
                exit();
            }//Update generated Card


            $is_reedemed = "No";
            $stmt = $pdo_conn->prepare("SELECT * from `generated_cards` WHERE `unique_card_no` = ? AND `is_reedemed` = ?  ");
            $stmt->bind_param("ss",$card_number,$is_reedemed);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                while($row = array_shift( $result)){
                    $gererated_card_id = $row["id"];
                    $user_email = $row['user_email'];
                    $redeem_card = $row['is_reedemed'];
                }
            }//Update generated Card
            else{
                $flag = false;
            }// Card count

            //Check redeem and giver not same email id
            $is_reedemed    = "No";
            $exist_user_id  = "";
            $exist_email_id = "";
            $stmt = $pdo_conn->prepare("SELECT * from `generated_cards` WHERE `unique_card_no` = ? AND `is_reedemed` = ?  ");
            $stmt->bind_param("ss", $card_number, $is_reedemed);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                while($row = array_shift( $result)){
                    $exist_user_id  = $row['user_id'];
                    $exist_email_id = $row['user_email'];
                }
                if($exist_email_id != "" && ($exist_user_id !="" && $exist_user_id != NULL)){
                    if($exist_email_id == $_SESSION['email']){
                        $response['status'] = 'Failed';
                        $response['message'] = 'You can\'t redeem <b>'.$card_number.'</b> gift card. Because this gift card generated by same email id.';
                        echo json_encode($response);
                        exit();
                    }
                }
            } 
        } 
    }

    //If validation is true
    if($flag){
        for($j = 0; $j<$len_arr; $j++ ){
           
            $custom_msg = $redeem_card_arr[$j]["message"];
            $card_number = $redeem_card_arr[$j]["card_number"];
           
            $is_reedemed = "No";
            $card_amount = 0;
            $stmt = $pdo_conn->prepare("SELECT * from `generated_cards` WHERE `unique_card_no` = ? AND `is_reedemed` = ?  ");
            $stmt->bind_param("ss",$card_number,$is_reedemed);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                while($row = array_shift( $result)){
                    $gererated_card_id = $row["id"];
                    $user_email   = $row['user_email'];
                    $card_amount  = $row['card_amount'];
                }
            }//Update generated Card

            //Checking generated cards by guest or user
            $is_guest = "Yes";
            $type_user = "contributor";

            $stmt = $pdo_conn->prepare("SELECT `id` from `users` WHERE `email_id` = ? AND `user_type` = ?  ");
            $stmt->bind_param("ss",$user_email,$type_user);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                $is_guest = "No";
                while($row = array_shift( $result)){
                    //$user_email = $row['user_email'];
                }
            }

            $stmt = $pdo_conn->prepare("SELECT `email_subscritpion`, `email_sub_code`, `balance` from `users` WHERE `id` = ? ");
            $stmt->bind_param("i",$_SESSION['user_id']);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                while($row = array_shift( $result)){
                    $balance = $row["balance"];
                    $email_sub_code  = $row['email_sub_code'];
                    $email_subscritpion = $row['email_subscritpion'];
                } 
            }//balance if
                 
                $new_balance = $balance+$card_amount;
                $is_reedemed = "Yes";
                $status      = "Redeemed";
                $stmt = $pdo_conn->prepare("UPDATE `generated_cards` SET `redeemer_msg`= ?,`allow_home` = ?, `is_reedemed` = ? , `redeemer_user_id` = ? ,`status` = ? ,`redeemed_on` = now() WHERE `id` = ? ");
                $stmt->bind_param('sssisi',$custom_msg,$allow_home,$is_reedemed,$_SESSION['user_id'] ,$status,$gererated_card_id);
                if($stmt->execute()){
                   
                    $stmt = $pdo_conn->prepare("UPDATE `users` SET `balance` = ".$new_balance." WHERE `id` = ? ");
                    $stmt->bind_param('i',$_SESSION['user_id']);
                    if($stmt->execute()){  
                        $response['new_balance'] = number_format($new_balance,2);                      
                        $subject = "Card Redeemed";
                        //$message = $card_number." card number redeemed successful by ".$_SESSION['email']."."."<br/>".
                        // $custom_msg;
                        $message = "Congratulations, your Thank You Card was just redeemed.<br>".$custom_msg; 
                        //Send message
                        $message = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td bgcolor="#c20a0a" align="center">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                                <tr>
                                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> 
                                                    </td>
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
                                                    <td bgcolor="#ffffff" align="left" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                                        <p style="margin-left: 4px; text-align: center;">
                                                        Your Thank You Card was just redeemed.<br>
                                                        </p>
                                                        <p style="margin-left: 4px; text-align: center; font-size:20px;">
                                                            '.$comman_msg.'
                                                        </p>
                                                        <p style="margin-left: 4px; text-align: center; font-size:25px;">
                                                            '.$custom_msg.'
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
                                                        <p style="margin: 0;"><a href="'.$final_url.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                                <tr>
                                                    <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                                        <p style="margin-bottom:20px;text-align: center;">In need of any kind of support, please feel free to drop an email at contact@pranamthankyou.org<a href="#" target="_blank" style="color: #111111; font-weight: 700;"></a>.</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>';
                        $message_regular = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td bgcolor="#c20a0a" align="center">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                                <tr>
                                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> 
                                                    </td>
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
                                                    <td bgcolor="#ffffff" align="left" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                                        <p style="margin-left: 4px; text-align: center;">
                                                        Your Thank You Card was just redeemed.<br>
                                                        </p>
                                                        <p style="margin-left: 4px; text-align: center; font-size:20px;">
                                                            '.$comman_msg.'
                                                        </p>
                                                        <p style="margin-left: 4px; text-align: center; font-size:25px;">
                                                            '.$custom_msg.'
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
                                                        <p style="margin: 0;"><a href="'.$final_url.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
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

                        if($is_guest=="Yes"){
                            mailFuntion($user_email, $message, $subject);
                        }
                        else{
                            if($email_subscritpion=="Y"){
                                mailFuntion($user_email, $message_regular, $subject);
                            }    
                        }
                        $response['status'] = 'Success';
                        $response['message'] = 'Thank You for encashing your thank you card. Do you want to Thank somebody? <br/> <a href="javasrcipt:void(0)" onclick="grabonecard()" >Click now</a> to grab one card for FREE (worth $5).';
                    }             
                }     
            }
    }
    else{
        $response['status'] = 'Failed';
        $response['message'] = '<b>'.$card_number.'</b> Card code invalid! Please check and try again.';
        echo json_encode($response);
        exit();
    }
    echo json_encode($response); 
    exit;
    
}// Redeem Card

else if($operation == 'withdrawAmount'){
    $totalAmount  = str_replace(",","",$_POST['totalAmount']);
    $amount  = $_POST['amount'];
    $notes  = $_POST['notes'];
      
    $response = array();
    if($amount == ''){
        $response['status'] = 'Failed';
        $response['message'] = 'Plese enter withdraw amount.';
        echo json_encode($response);
        exit();
    }
    //echo $amount."==".$totalAmount;
    //exit();

    if($amount > $totalAmount){
        $response['status'] = 'Failed';
        $response['message'] = 'Your amount is greater than available balance.';
        echo json_encode($response);
        exit();
    }
    if($amount > 50 ){
        $response['status'] = 'Failed';
        $response['message'] = "You can"."'"."t withdraw greater than $15";
        echo json_encode($response);
        exit();
    }

    if($amount < 5 ){
        $response['status'] = 'Failed';
        $response['message'] = "You can"."'"."t withdraw less than $5";
        echo json_encode($response);
        exit();
    }

    if($notes == ""){
        $response['status'] = 'Failed';
        $response['message'] = "Please enter your bank/wallet info in notes so we can process the withdrwal correctly.";
        echo json_encode($response);
        exit();
    }

    $status = 'Pending';
    $stmt = $pdo_conn->prepare("INSERT INTO `withdrawals` (`user_id`,`amount`,`requested_on`,`status`,`payment_notes`) VALUES (?,?,CURRENT_TIMESTAMP(),?,? )");
    $stmt->bind_param('isss',$_SESSION['user_id'],$amount,$status,$notes);
    if($stmt->execute()){   //if query executed successfully
        
        $availableAmount = $totalAmount - $amount;
     
        $stmt = $pdo_conn->prepare("UPDATE `users` SET `balance` = $availableAmount WHERE `id` = ? ");
        $stmt->bind_param('i',$_SESSION['user_id']);
        if($stmt->execute()){   //if query executed successfully
            $response['status'] = 'Success';
            $response['message'] = 'You withdraw request has been submitted.';
            $availableAmount = $totalAmount - $amount;
            $response['availableAmount'] = number_format($availableAmount,2);        
    
        }
        else{
            $response['status'] = 'Error';
            $response['message'] = 'Your withdraw amount unsuccessfully.';
        }
    } 
    echo json_encode($response);
}// Withdraw amount operations

else if($operation == 'withdrawHistory') {
    $primaryKey = 'id'; 
    $table = 'withdrawals';
    $table2 = 'users';
    $columns = array(
        array( 'db' => '`w`.`id`', 'dt' => 0 , 'field' => 'id' ),
        array( 'db' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)', 'dt' => 1, 'field' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)'  ),
        array( 'db' => '`w`.`amount`', 'dt' => 2 ,
         'formatter' => function( $d, $row ) {
              return "$".number_format($row['amount'],2);
              },
          'field' => 'amount'),
        // array( 'db' => '`w`.`payment_notes`', 'dt' => 3 , 'field' => 'payment_notes' ),
        array( 'db' => '`w`.`requested_on`', 'dt' => 3,
           'formatter' => function( $d, $row ) {
              if($row['requested_on'] == NULL)
                 {
                    $requestDate = '';
                 }
                 else 
                 {
                    $requestDate =  date( 'd-M-Y h:i A',strtotime($row['requested_on']));
                  }
                  return $requestDate;
              },
                'field' => 'requested_on'
         ),
        array( 'db' => '`w`.`status`',     'dt' => 4, 'field' => 'status',
         'formatter' => function( $d, $row ) {
            $id = $row['id'];
            //$selectHtml = '';
            // if($d=='Pending'){
            //   $selectHtml .= '<select class="form-control actives form-control-sm selectInTable " onchange="changestatuspro(this,'.$row['id'].',\''.$row['user_id'].'\',\''.$row['amount'].'\')">
            //   <option class="" value="Pending" '. (($d=='Pending')?'selected':'').'>Pending</option>
            //   <option class="" value="Completed" '. (($d=='Completed')?'selected':'').'>Completed</option>
            //   <option class="" value="Rejected" '. (($d=='Rejected')?'selected':'').'>Rejected</option>
            //   </select>';
            // }else{
            //   $selectHtml = '<p class="mb-0 text-center">'.$d.'</p>';
            // }
               return $d;
           }
         ),
          // array( 'db' => '`w`.`admin_notes`', 'dt' => 6 , 'field' => 'admin_notes' ),
         array( 'db' => '`w`.`user_id`', 'dt' => 5, 'field' => 'user_id' )
        );
    $joinQuery = "FROM `{$table}` AS `w` LEFT JOIN `{$table2}` AS `u` ON `u`.`id` = `w`.`user_id` WHERE `w`.`user_id` = ". $_SESSION['user_id'];
    
    $extraCondition = NULL ;
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
}//withdrawHistory Operation

else if($operation == 'redeemCardHistory')  {
      $primaryKey = 'id'; 
      $table  = 'generated_cards'; 
      $table2 = 'cards';
      $columns = array(
            array( 'db' => '`g`.`id`', 'dt' => 0 , 'field' => 'id' ),
            array( 'db' => '`c`.`card_name`', 'dt' => 1 , 'field' => 'card_name', 
                'formatter' => function( $d, $row ) {
                    if($row['card_name']!=""){
                        return $row['card_name'];
                    }else{
                        return 'Custom Card';
                    }
                }
            ),
            array( 'db' => '`g`.`unique_card_no`', 'dt' => 2 , 'field' => 'unique_card_no'),
            array( 'db' => '`g`.`card_amount`', 'dt' => 3 ,
               'formatter' => function( $d, $row ) {
                     return "$".number_format($row['card_amount'],2);
                  },
                    'field' => 'card_amount'
             ),
            array( 'db' => '`g`.`generated_on`', 'dt' => 4 ,
               'formatter' => function( $d, $row ) {
                    if($row['generated_on'] == NULL){
                        $regisDate = '';
                    }else{
                        $regisDate =  date( 'd-M-Y h:i A',strtotime($row['generated_on']));
                    }
                    return $regisDate;
                },
                'field' => 'generated_on'
            ),

            array( 'db' => '`g`.`redeemer_msg`', 'dt' => 5 ,
                'formatter' => function( $d, $row ) {
                    if($row['redeemer_msg'] != NULL && $row['redeemer_msg'] != ""){
                        $thank_msg =$row['redeemer_msg'];
                    }else{
                        $thank_msg =  '<div class="d-flex">
                        <a href="javascript:void(0)" class="btn btn-success" title="View" onclick="saythank('.$row['id'].')">Say thank</a> 
                        </div>';
                    }
                    return $thank_msg;
                },
                'field' => 'redeemer_msg'
            ),
            array( 'db' => '`g`.`status`','dt' => 6, 'field' => 'status' )
        );
    $joinQuery = "FROM `{$table}` AS `g` LEFT JOIN `{$table2}` AS `c` ON `c`.`id` = `g`.`card_id` ";
    $extraCondition = " `g`.`redeemer_user_id` = '".$_SESSION['user_id']."' ";
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );

}//redeemCardHistory Operation

    else if($operation == "giftCardHistory"){
    
        // Update Gift Cards
        header('Content-Type: application/json'); // Specify the type of data

        $url = "";
        $token = "";
        $balance = 0;
//         $brand_code = '';
// 
//         if($_POST['brand_code']!=NULL){
//             $brand_code = $_POST['brand_code'];
//         }

        

        //Check balance of user
        $stmt = $pdo_conn->prepare("SELECT * from `giftbit_coupons` WHERE `user_id` = ? AND `status` = 'SENT_AND_REDEEMABLE'");
        $stmt->bind_param("i",$_SESSION['user_id']);
        $stmt->execute();
        $result = get_result($stmt);
        $not_redeemed_coupons =  array();
        if(count($result) > 0){
            while($row = array_shift( $result)){
                $not_redeemed_coupons[] = $row;
            } 
        }
        
        foreach($not_redeemed_coupons as $key=>$not_redeemed_coupon){
            $campaign_id = $not_redeemed_coupon['campaign_id'];
            $id = $not_redeemed_coupon['id'];
            if(GIFTBIT_TEST_MODE){
                $url = "https://api-testbed.giftbit.com/papi/v1/gifts?campaign_id=".$campaign_id;
                $token = GIFTBIT_TEST_TOKEN;
            }else{
                $url = "https://api.giftbit.com/papi/v1/gifts?campaign_id=".$campaign_id;
                $token = GIFTBIT_PROD_TOKEN;
            }
            // begin script
            $ch = curl_init(); 

            // extra headers
            $headers[] = "Accept: */*";
            $headers[] = "Connection: Keep-Alive";
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer ".$token;
             // basic curl options for all requests
            curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
            curl_setopt($ch, CURLOPT_HEADER,  0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  

            // set first URL
            curl_setopt($ch, CURLOPT_URL, $url);
            
            // execute session to get cookies and required form inputs
            $result = curl_exec($ch);
            curl_close($ch);
            $parsed_data = json_decode($result,true);
            
            
            if(!empty($parsed_data['gifts'][0]['status']) && !empty($parsed_data['gifts'][0]['delivery_status'])){
            
                $status = $parsed_data['gifts'][0]['status'];
                $delivery_status = $parsed_data['gifts'][0]['delivery_status'];
                
                $stmt = $pdo_conn->prepare("UPDATE `giftbit_coupons` SET status = ? , delivery_status = ? WHERE `id` = ?");
                $stmt->bind_param("ssi",$status, $delivery_status, $id);
                $stmt->execute();
                
            }
        }


        $primaryKey = 'id'; 
        $table = 'giftbit_coupons';
         // $table2 = 'cards';
        $columns = array(
            array( 'db' => '`gc`.`price`', 'dt' => 0,
               'formatter' => function( $d, $row ) {
                      return "$ ".number_format($row['price'],2);
                  },
                    'field' => 'price'
             ),
            
            array( 'db' => '`gc`.`brand_name`', 'dt' => 1,
               'formatter' => function( $d, $row ) {
                      return '<div class="text-center"><img src="'.$row['brand_image'].'" width="100px"> <br />'.$row['brand_name'].'</div>';
                  },
                    'field' => 'brand_name'
             ),
            array( 'db' => '`gc`.`status`', 'dt' => 2 ,
               'formatter' => function( $d, $row ) {
                  if($row['status'] == "SENT_AND_REDEEMABLE")
                     {
                        $status = 'Not claimed';
                                                
                     }
                     else if($row['status'] == "REDEEMED")
                     {
                        $status =  'Claimed';
                      }
                      return $status;
                  },
                    'field' => 'status'
             ),
            
            

             array( 'db' => '`gc`.`generated_on`', 'dt' => 3 ,
             'formatter' => function( $d, $row ) {
                if($row['generated_on'] == NULL)
                   {
                      $genratedDate = '(..)';
                   }
                   else
                   {
                      $genratedDate =  date( 'd-M-Y h:i A',($row['generated_on']));
                    }
                    return $genratedDate;
                },
                  'field' => 'generated_on'
           ),
            array('db' => '`gc`.`gift_link`','dt' =>4,
                    'formatter' => function( $d, $row ){
                      $action =  '<a class="btn btn-success btn-sm" target="_blank" href="'.$row['gift_link'].'">View Gift Card</a> ';
                      return $action;
                  },
                    'field' => 'gift_link'
             ),
            array( 'db' => '`gc`.`brand_image`', 'dt' => 5, 'field' => 'brand_image'),
         );
        
    $joinQuery = "FROM `{$table}` AS `gc` ";
    $extraCondition = "`user_id` = '".$_SESSION['user_id']."' ";
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
    }

else if($operation == "updateLogo"){
    $response = array();
    $location = "../uploads/users_logo/";
    if($_FILES['file_att']['name'] != ''){
        $img_name = time().$_FILES['file_att']['name'];  
    }
    $finalLocation = $location.time().$_FILES['file_att']['name'];
    if(move_uploaded_file($_FILES['file_att']['tmp_name'], $finalLocation)){
        $stmt = $pdo_conn->prepare("UPDATE `users` SET  `logo` = ? WHERE `id` = ?  ");
        $stmt->bind_param("si",$img_name,$_SESSION['user_id']);//binding parameter
        if($stmt->execute()){
            $response['status'] = "Success";
            $response['img_name'] = $img_name;

            $_SESSION['logo'] = $img_name;//Change image set on session logo
        }
    }
    echo json_encode($response);
}

else if($operation == "updateProfile"){
    $response = array();

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $usertype = 'redeemer';  
    $status = $_POST['status'];

    $stmt = $pdo_conn->prepare("UPDATE `users` SET `first_name` = ? , `last_name` =?,`email_id` = ? , `phone` = ? , `user_type` = ?  ,`status`=? WHERE `id`=? ");
    $stmt->bind_param('ssssssi',$fname,$lname,$email,$phone,$usertype,$status,$_SESSION['user_id']);
    if($stmt->execute()){
        $response['status'] = 'Success';
        $response['message'] = 'Profile updated successfully.';
    }else{
        $response['status'] = 'Failed';
        $response['message'] = 'Profile updation failled.';
     }
    echo json_encode($response);
}// Change Status

else if($operation == 'get_brands'){
    header('Content-Type: application/json'); // Specify the type of data

    $url = "";
    $token = "";
    $amount = 50;
    $authorization = '';
    $balance = 0;

    //https://api-testbed.giftbit.com/papi/v1/regions
    
    // GET REGION ID
    
    if(GIFTBIT_TEST_MODE){
        $url = "https://api-testbed.giftbit.com/papi/v1/regions";
        $token = GIFTBIT_TEST_TOKEN;
    }else{
        $url = "https://api.giftbit.com/papi/v1/regions";
        $token = GIFTBIT_PROD_TOKEN;
    }
    
    // begin script
    $ch = curl_init(); 

    // extra headers
    $headers[] = "Accept: */*";
    $headers[] = "Connection: Keep-Alive";
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer ".$token;
    $headers[] = "currencyisocode: USD";
    // $headers[] = "min_price_in_cents: 500";
    $headers[] = "max_price_in_cents: ".$amount*100;
    $headers[] = "limit: 200";
    $headers[] = "region: USA";


    // basic curl options for all requests
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
    curl_setopt($ch, CURLOPT_HEADER,  0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  

    // set first URL
    curl_setopt($ch, CURLOPT_URL, $url);
   
    // execute session to get cookies and required form inputs
    $result = curl_exec($ch);
    curl_close($ch);
    $parsed_data = json_decode($result,TRUE);
    $region_id = 'USA';
    foreach($parsed_data['regions'] as $key=>$regions){
        if($regions['name'] == 'USA'){
            $region_id = $regions['id'];
        }
    }
    
    // GET BRANDS
    $amount_in_cents = $amount*100;
    if(GIFTBIT_TEST_MODE){
        $url = "https://api-testbed.giftbit.com/papi/v1/brands?region=".$region_id."&currencyisocode=USD&max_price_in_cents=".$amount_in_cents."&limit=200";
        $token = GIFTBIT_TEST_TOKEN;
    }else{
        $url = "https://api.giftbit.com/papi/v1/brands?region=".$region_id."&currencyisocode=USD&max_price_in_cents=".$amount_in_cents."&limit=200";
        $token = GIFTBIT_PROD_TOKEN;
    }

    //Check balance of user
    $stmt = $pdo_conn->prepare("SELECT balance from `users` WHERE `id` = ? ");
    $stmt->bind_param("i",$_SESSION['user_id']);
    $stmt->execute();
    $result = get_result($stmt);
    if(count($result)>0){
        while($row = array_shift( $result)){
            $balance = $row["balance"];
        } 
    }

//     if($amount == 0){
//         echo 1;
//         exit();
//     }else if($amount>$balance){
//         echo 2;
//         exit();
//     }


    // begin script
    $ch = curl_init(); 

    // extra headers
    $headers[] = "Accept: */*";
    $headers[] = "Connection: Keep-Alive";
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer ".$token;
//     $headers[] = "currencyisocode: USD";
//     // $headers[] = "min_price_in_cents: 500";
//     $headers[] = "max_price_in_cents: ".$amount*100;
//     $headers[] = "limit: 200";
//     $headers[] = "region: ".$region_id;


    // basic curl options for all requests
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
    curl_setopt($ch, CURLOPT_HEADER,  0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  

    // set first URL
    curl_setopt($ch, CURLOPT_URL, $url);
   
    // execute session to get cookies and required form inputs
    $result = curl_exec($ch);
    curl_close($ch);
    echo json_encode($result);

  }
  else if($operation == 'get_card'){
    header('Content-Type: application/json'); // Specify the type of data

    $url = "";
    $token = "";
    $amount = 0;
    $balance = 0;
    $brand_code = '';

    if($_POST['amount']!=NULL){
        $amount = $_POST['amount'];
    }
    if($_POST['brand_code']!=NULL){
        $brand_code = $_POST['brand_code'];
    }

    if(GIFTBIT_TEST_MODE){
        $url = "https://api-testbed.giftbit.com/papi/v1/embedded";
        $token = GIFTBIT_TEST_TOKEN;
    }else{
        $url = "https://api.giftbit.com/papi/v1/embedded";
        $token = GIFTBIT_PROD_TOKEN;
    }

    //Check balance of user
    $stmt = $pdo_conn->prepare("SELECT balance from `users` WHERE `id` = ? ");
    $stmt->bind_param("i",$_SESSION['user_id']);
    $stmt->execute();
    $result = get_result($stmt);
    if(count($result)>0){
        while($row = array_shift( $result)){
            $balance = $row["balance"];
        } 
    }

    if($amount == 0){
        echo 1;
        exit();
    }else if($amount>$balance){
        echo 2;
        exit();
    }


    // begin script
    $ch = curl_init(); 

    
    // extra headers
    $headers[] = "Accept: */*";
    $headers[] = "Connection: Keep-Alive";
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer ".$token;
    $body = array(
            "brand_code"=>$brand_code,    
            "price_in_cents"=>$amount*100,
            "id"=>$_SESSION['user_id'].time()        
    );

    // basic curl options for all requests
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
    curl_setopt($ch, CURLOPT_HEADER,  0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($body));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  

    // set first URL
    curl_setopt($ch, CURLOPT_URL, $url);
   
    // execute session to get cookies and required form inputs
    $result = curl_exec($ch);
    curl_close($ch);
    $parsed_data = json_decode($result,true);
    // print_r($parsed_data['campaign']['id']);
    
    $stmt = $pdo_conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param("ii",$amount,$_SESSION['user_id']);
    $stmt->execute();
    
    
    if(isset($parsed_data['status']) && ($parsed_data['status'] == 200)){
        $campaign_id = $parsed_data['campaign']['id'];
        $user_id = $_SESSION['user_id'];
        $price =  ((float)$parsed_data['campaign']['fees']['cost_entries'][0]['amount_in_cents'])/100;
        $fees =  ((float)$parsed_data['campaign']['fees']['cost_entries'][1]['fee_per_gift_in_cents'])/100;
        $brand_code = $parsed_data['campaign']['brand_code'];
        //$brand_image = NULL;
        $gift_link = $parsed_data['gift_link'];
        //$claimed_on  = NULL;
        $delivery_status = "UNSENT";
        $status = "SENT_AND_REDEEMABLE";
        $brand_name = isset($_POST['brand_name']) ? $_POST['brand_name'] : '';
        $brand_image = isset($_POST['brand_image']) ? $_POST['brand_image'] : '';
        

        $stmt = $pdo_conn->prepare("INSERT INTO `giftbit_coupons`(
            `campaign_id`,
            `user_id`,
            `price`,
            `fees`,
            `brand_code`,
            `brand_name`,
            `brand_image`,
            `gift_link`,
            `generated_on`,
            `claimed_on`,
            `delivery_status`,
            `status`
            ) VALUES(?,?,?,?,?,?,?,?,UNIX_TIMESTAMP(),NULL,?,?) ");
        $stmt->bind_param("siiissssss",$campaign_id,$user_id,$price,$fees,$brand_code,$brand_name,$brand_image,$gift_link,$delivery_status,$status);
        $stmt->execute();

    }
    echo json_encode($result);

  }

  else if($operation == 'get_brand_detail'){
    header('Content-Type: application/json'); // Specify the type of data

    $url = "";
    $token = "";
    $balance = 0;
    $brand_code = '';

    if($_POST['brand_code']!=NULL){
        $brand_code = $_POST['brand_code'];
    }

    if(GIFTBIT_TEST_MODE){
        $url = "https://api-testbed.giftbit.com/papi/v1/brands/".$brand_code;
        $token = GIFTBIT_TEST_TOKEN;
    }else{
        $url = "https://api.giftbit.com/papi/v1/brands/".$brand_code;
        $token = GIFTBIT_PROD_TOKEN;
    }

//     //Check balance of user
//     $stmt = $pdo_conn->prepare("SELECT balance from `users` WHERE `id` = ? ");
//     $stmt->bind_param("i",$_SESSION['user_id']);
//     $stmt->execute();
//     $result = get_result($stmt);
//     if(count($result)>0){
//         while($row = array_shift( $result)){
//             $balance = $row["balance"];
//         } 
//     }

//     if($amount == 0){
//         echo 1;
//         exit();
//     }else if($amount>$balance){
//         echo 2;
//         exit();
//     }


    // begin script
    $ch = curl_init(); 

    // extra headers
    $headers[] = "Accept: */*";
    $headers[] = "Connection: Keep-Alive";
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer ".$token;
    // $headers[] = "brand_code: ".$brand_code;
    // $headers[] = "price_in_cents: ".$amount*100;
    // $headers[] = "id: 3234232332";



    // basic curl options for all requests
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
    curl_setopt($ch, CURLOPT_HEADER,  0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  

    // set first URL
    curl_setopt($ch, CURLOPT_URL, $url);
   
    // execute session to get cookies and required form inputs
    $result = curl_exec($ch);
    curl_close($ch);
    echo json_encode($result);
}

//Operation grabonecard
else if($operation == "grabonecard"){
    $data = array();
    // session_destroy();
    // session_unset();
    // session_start();
    $_SESSION['get_one_card'] = "Yes";
    if(isset($_SESSION['get_one_card'])){
        $data['status'] = "Success";
    }else{
        $data['status'] = "Failed";
    }
    echo json_encode($data);
}

// Update Say thank you message
else if($operation == "update_message"){

    $custom_msg = $_POST['message'];
    $id      = $_POST['id'];
    $user_type = $_POST['type'];

    
    if(!empty($custom_msg)){
        $comman_msg = "Redeemer has left you note,";
        $custom_msg = "&ldquo; ".$custom_msg." &rdquo;";
    }

    $response = array();
    if($custom_msg != '' ){ 
        
        $is_reedemed = "No";
        $stmt = $pdo_conn->prepare("SELECT * from `generated_cards` WHERE `id` = ?   ");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = get_result($stmt);
        if(count($result)>0){
            while($row = array_shift( $result)){
                $gererated_card_id = $row["id"];
                $user_email = $row['user_email'];
            }
        }
            
            //Checking generated cards by guest or user
            $is_guest = "Yes";
            $type_user = "contributor";
            $stmt = $pdo_conn->prepare("SELECT `id` from `users` WHERE `email_id` = ? AND `user_type` = ?  ");
            $stmt->bind_param("ss",$user_email,$type_user);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                $is_guest = "No";
                while($row = array_shift( $result)){
                    //$user_email = $row['user_email'];
                }
            }


            $stmt = $pdo_conn->prepare("SELECT `email_subscritpion`, `email_sub_code`, `balance` from `users` WHERE `id` = ? ");
            $stmt->bind_param("i",$_SESSION['user_id']);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){
                
                while($row = array_shift( $result)){
                    $balance = $row["balance"];
                    $email_sub_code  = $row['email_sub_code'];
                    $email_subscritpion = $row['email_subscritpion'];
                } 
            }//balance if

            $new_balance = $balance+5;
            $is_reedemed = "Yes";
            $status      = "Redeemed";
            $stmt = $pdo_conn->prepare("UPDATE `generated_cards` SET `redeemer_msg`= ? WHERE `id` = ? ");
            $stmt->bind_param('si',$custom_msg,$id);
            if($stmt->execute()){
                
                    $subject = "Card Redeemed";
                      //$message = $card_number." card number redeemed successful by ".$_SESSION['email']."."."<br/>".
                       // $custom_msg;
                    $message = "Congratulations, your Thank You Card was just redeemed.<br>".$custom_msg; 
                      //Send message
                    $message = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td bgcolor="#c20a0a" align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                            <tr>
                                                <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> 
                                                </td>
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
                                                <td bgcolor="#ffffff" align="left" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                                    <p style="margin-left: 4px; text-align: center;">
                                                       Your Thank You Card was just redeemed.<br>
                                                    </p>
                                                    <p style="margin-left: 4px; text-align: center; font-size:20px;">
                                                        '.$comman_msg.'
                                                    </p>
                                                    <p style="margin-left: 4px; text-align: center; font-size:25px;">
                                                        '.$custom_msg.'
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
                                                    <p style="margin: 0;"><a href="'.$final_url.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                            <tr>
                                                <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                                    <p style="margin-bottom:20px;text-align: center;">In need of any kind of support, please feel free to drop an email at contact@pranamthankyou.org<a href="#" target="_blank" style="color: #111111; font-weight: 700;"></a>.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';
                    $message_regular = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td bgcolor="#c20a0a" align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                                            <tr>
                                                <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> 
                                                </td>
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
                                                <td bgcolor="#ffffff" align="left" style="padding-left:20px;padding-right:20px;padding-top:20px; color: #666666; font-family: Lato, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                                    <p style="margin-left: 4px; text-align: center;">
                                                       Your Thank You Card was just redeemed.<br>
                                                    </p>
                                                    <p style="margin-left: 4px; text-align: center; font-size:20px;">
                                                        '.$comman_msg.'
                                                    </p>
                                                    <p style="margin-left: 4px; text-align: center; font-size:25px;">
                                                        '.$custom_msg.'
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
                                                    <p style="margin: 0;"><a href="'.$final_url.'" target="_blank" style="color: #c20a0a;">We&rsquo;re here to help you out</a></p>
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
                    if($is_guest=="Yes"){
                        mailFuntion($user_email, $message, $subject);    
                    }
                    else{
                        if($email_subscritpion=="Y"){
                            //mailFuntion($user_email, $message_regular, $subject ,$email_sub_code,$final_url);
                            mailFuntion($user_email, $message_regular, $subject);
                        }    
                    }
                    $response['status'] = 'Success';
                    $response['message'] = 'Thank You for sending message. Do you want to Thank somebody? <br/> <a href="javasrcipt:void(0)" onclick="grabonecard()" >Click now</a> to grab one card for FREE (worth $5).';
                            
            
        }//Update generated Card
        else{
            $response['status'] = 'Failed';
            $response['message'] = 'Card code invalid! Please check and try again.';
        }// Card count 
    }//else
    echo json_encode($response);     

}
//Main function
function mailFuntion($email,$message,$subject){
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
        $data['email'] = 'Sent';
        return true;
      }
      else{
        $data['email'] = 'Not Sent';
        return false;
      }
  }
  catch(Exception $e)
  {   
    //echo $e->getMessage(); //Boring error messages from anything else!
      $data['status'] = 'failed';
      $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Message could not be sent.</span>';
  }
}
//Mail function


 
?>