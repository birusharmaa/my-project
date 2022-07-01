<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include '../server/vendor/autoload.php';
session_start(); 
if(isset($_POST["operation"])){
	$operation = $_POST["operation"];
	require( "config.php" );
    require( 'ssp.class.php' ); 
}else{
    exit();
}

$is_secure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $is_secure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $is_secure = true;
}
else{}

$ishttps = $is_secure ? 'https' : 'http';
$host_name = $_SERVER['HTTP_HOST'];
$final_url = $ishttps.'://'.$host_name;

if($host_name=="localhost"){
    $final_url = $final_url."/projects-sm/127/src";
}else{
    $final_url = $final_url;
}

$_SESSION['logged'] = "failed";
 
if($operation == 'auth'){ 
 
    $originalpassword = '';
    $response = array(); 
    if(!empty($_REQUEST['data'])){
        $data = $_REQUEST['data'];
        $email = strtolower($data['s_email']);
        $password = $data['s_password'];
        $userType = 'contributor';
        
        $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `email_id` = ? AND `user_type` = ?  ");
        $stmt->bind_param("ss",$email,$userType);
        $stmt->execute();
        $result = get_result($stmt);
        if(count($result)>0){ 
            while($row = array_shift( $result)) {  
                $bcrypt_password = $row["password"];
                $email           = $row["email_id"];
                $first_name      = $row["first_name"];
                $last_name       = $row["last_name"];
                $user_type       = $row["user_type"];
                $registered_on   = $row['registered_on'];
                $user_id         = $row['id'];
                $status          = $row['status'];
                $logo            = $row['logo'];
                $email_sub_code  = $row['email_sub_code'];
                $email_subscritpion = $row['email_subscritpion'];
                //$date = $row['expiry_date'];
            }
            if(password_verify( $password ,$bcrypt_password)){ 
                $stmt = $pdo_conn->prepare("SELECT * FROM `users_plan` WHERE `user_id`=? order by `id` desc LIMIT 1");
                $stmt->bind_param("i",$user_id);
                $stmt->execute();
                $result = get_result($stmt);
                    if(count($result)>0){ 
                        while($row = array_shift( $result)){
                            $date = $row['expiry_date'];
                            $current_date = date("Y-m-d");
                            $current_date = strtotime($current_date);
                            $expiry_date = strtotime($date);
                            if($current_date > $expiry_date){
                                $response['expiry_date']="expire";
                                $_SESSION['subscriber_id']=$user_id;
                            }else{
                                if($status=='A'){         

                                    $_SESSION["email"] = $email;
                                    $_SESSION["user_name"] = $first_name." ".$last_name;
                                    $_SESSION['user_id'] = $user_id;
                                    $_SESSION['registered_on'] = $registered_on;
                                    $_SESSION['user_type'] = $user_type;
                                    $_SESSION['logo'] = $logo;
                                    unset($_SESSION['is_front_page']);
                                    $response['success'] = true;
                                    $response['message'] = "Logged in successfully. Redirecting to your account...";
                                    $header = "Welcome";
                                    $message = 'You have logged in to your account.';
                                    $subject = "Contributor Login";
                                    //Send mail function
                                    if($email_subscritpion=="Y"){
                                        mailFuntion($email, $subject, $header, $message,$email_sub_code,$final_url);
                                    }
                                }
                                else{  
                                    $response['success'] = false;
                                    $response['message'] = 'This account is disabled, please contact to admin.';
                                } 
                            } 
                        }
                    }else{
                        $response['expiry_date']="expire";
                        $_SESSION['subscriber_id']=$user_id;
                    }
                }
                else{
                    $response['error'] = true;
                    $response['message'] = 'Authentication failed. Wrong credentials.';
                }
            }
            else{
                $response['error'] = 'Wrong';
                $response['message'] = 'Email id is not registered. Please register!';
            }
        }
    echo json_encode($response);
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
    $userType  = $_POST['userType'];
    $company   = $_POST['company'];
    $is_refer  = $_POST['isRefer'];
    if($is_refer!="Yes"){
        $is_refer = "";
    }
    $refer_code= isset($_POST['referralRode'])?$_POST['referralRode']:"";
    $user_type = "contributor";

    $result = mysqli_query($conn,"SELECT * FROM `users` WHERE `email_id` = '$email' AND `user_type` = '$user_type' ");
    $num_rows = mysqli_num_rows($result);
    if($num_rows >= 1){
        $response['status'] = 'Email_Taken';
    }
    else{

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
                $result = mysqli_query($conn,"SELECT referral_code FROM users WHERE referral_code = '".$unique."' ");
                if($result->num_rows > 0){
                    $unique = get_name(8);//Generate new randam string
                }        
                else{
                    $is_unique = true; 
                }       
            }
            return $unique; //Return unique key
        }
        // Function uniquecheck Closed

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
        function email_code_unique($conn,$unique_email){
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

        $unique_email = email_code(12);
        $unique_email = email_code_unique($conn,$unique_email);

        $unique = get_name(8);
        $unique = unique_check($conn, $unique);
                
        //Get referral code sponsor details        
        $sponsor_id = "";
        $last_id = "";
        $defalut_card = 0;
        if(!empty($refer_code)){
            $stmt = $pdo_conn->prepare("SELECT `id` FROM `users` WHERE `referral_code` = ? " );
            $stmt->bind_param("s",$refer_code);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){ 
                while($row = array_shift( $result)) {  
                    $sponsor_id = $row['id'];
                    $defalut_card = $defalut_card+1; 
                }
            }
        }

        //Check user exist or not as redeem
        $is_red_user_exist = "";
        if(!empty($is_refer)){
            $user_type = "redeemer";
            $stmt = $pdo_conn->prepare("SELECT `id` FROM `users` WHERE `email_id` = ? AND user_type = ? " );
            $stmt->bind_param("ss",$email, $user_type);
            $stmt->execute();
            $result = get_result($stmt);
            if(count($result)>0){ 
                $is_red_user_exist = "Yes";
                session_destroy();
                session_unset();
                session_start();
            }
        }

        //Encrypt pass value
        $encypt_password = password_hash($password, PASSWORD_DEFAULT);
        //Insert data in user table
        if($is_red_user_exist == "Yes"){
            $defalut_card = $defalut_card+1;
        }
        
        if(isset($_SESSION['get_one_card'])){
            if($_SESSION['get_one_card']=="Yes"){
                $defalut_card = $defalut_card+1;
            }
            unset($_SESSION['get_one_card']);
        }

        $stmt = $pdo_conn->prepare("INSERT INTO `users` (`first_name`,`last_name`,`email_id`,`password`,`phone`,`user_type`,`registered_on`,`company`, `referral_code`,`email_sub_code` ) VALUES (?,?,?,?,?,?,UNIX_TIMESTAMP(),?,?,? )");
        $stmt->bind_param('sssssssss',$first_name,$last_name,$email,$encypt_password,$phone,$userType,$company,$unique,$unique_email);
        if($stmt->execute()){   //if query executed successfully
            //Get last inserted id
            $last_id = $stmt->insert_id;
            $is_redeem = "No";
            $status = "New";
            $amount = 5;
            while(1 <= $defalut_card ){
                
                $unique = get_name(8);
                $unique = unique_check($conn, $unique);
                
                $stmt = $pdo_conn->prepare("INSERT INTO `generated_cards` 
                                            (   `user_email`, `unique_card_no`, 
                                                `is_reedemed`, `status`, 
                                                `generated_on`, `user_id`, 
                                                `card_amount` ) 
                                            VALUES (?, ?, ?, ?, NOW(), ?, ? )");
                $stmt->bind_param('ssssis',$email, $unique, $is_redeem, $status, $last_id, $amount);
                $stmt->execute();
                $defalut_card--;
            }

            
            $_SESSION['subscriber_id']=$last_id;
            $response['status'] = 'Success';
            $name = $first_name." ".$last_name;
            
            $message = "Username <b>".$name."</b> and email ".$email."</b><br/> contributor account register successfully."; 
            
            $subject = "Contributor Register";
            $header  = "Welcome"; 
            //Check if last_id is not empty

            if(!empty($last_id)){
                //If sponsor is not empty   
                if(!empty($sponsor_id)){
                    $is_redeem = "No";
                    $status = "New";
                    $amount = 5;
                    $i = 1;
                    while(1 <= $i){
                        $unique = get_name(8);
                        $unique = unique_check($conn, $unique);
                        $stmt = $pdo_conn->prepare("INSERT INTO `generated_cards` 
                                                    (   `user_email`, `unique_card_no`, 
                                                        `is_reedemed`, `status`, 
                                                        `generated_on`, `user_id`, 
                                                        `card_amount` ) 
                                                    VALUES (?, ?, ?, ?, NOW(), ?, ? )");
                        $stmt->bind_param('ssssis',$email, $unique, $is_redeem, $status, $sponsor_id, $amount);
                        $stmt->execute();
                        $i--;
                    }

                    //Register with sponsar
                    $stmt = $pdo_conn->prepare("INSERT INTO `user_sponsor` (`user_id`,`sponsor_id`) VALUES (?,?)");
                    $stmt->bind_param('ii',$last_id,$sponsor_id);
                    $stmt->execute();
                }
                else{
                    //Register without sponsor
                    $stmt = $pdo_conn->prepare("INSERT INTO `user_sponsor` (`user_id`) VALUES (?)");
                    $stmt->bind_param('i',$last_id);
                    $stmt->execute();  
                }
            }
            //Send mail function
            $email_subscritpion = "Y";
            if($email_subscritpion=="Y"){
                //mailFuntion($email, $subject, $header, $message,$unique_email,$final_url);
                $response['status'] = 'Success';
                //$response['message'] = ""
            }
            //mailFuntion($email, $subject, $header, $body);
        }
        else{
            $response['status'] = 'Error';
        }
    }
    echo json_encode($response);
}// Save User

//Make Payment
// else if($operation == 'makePayment'){
    
//     $cardValidation = false;
//     $amount  = $_POST['amount'];
//     $number  = $_POST['cardNumber'];
//     $month   = $_POST['month'];
//     $year    = $_POST['year'];
//     $cvv     = $_POST['cvv'];

//     $response = array();
//     if($amount == '' ){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please enter contribution amount.';
//         echo json_encode($response);
//         exit();
//     }

//     if($amount != '' && $amount <= 14 ){
//         $response['status'] = 'Failed';
//         $response['message'] = 'You have to contribute minimum $15.';
//         echo json_encode($response);
//         exit();
//     }

//     if($number == '' ){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please enter card number.';
//         echo json_encode($response);
//         exit();
//     }
    
//     if($number == '' ){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please enter card valid number.';
//         echo json_encode($response);
//         exit();
//     }
    
//     if($month == 'Select Month' ){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please choose month.';
//         echo json_encode($response);
//         exit();
//     }
    
//     if($year == 'Select Year' ){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please choose year.';
//         echo json_encode($response);
//         exit();
//     }
    
//     if($cvv == ''){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please enter cvv number.';
//         echo json_encode($response);
//         exit();
//     }
    
//     if(strlen($cvv) < 3){
//         $response['status'] = 'Failed';
//         $response['message'] = 'Please enter correct cvv code.';
//         echo json_encode($response);
//         exit();
//     }

//     function validatecard($number){
//         global $type;
//         $cardtype = array(
//             "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
//             "mastercard" => "/^5[1-5][0-9]{14}$/",
//             "amex"       => "/^3[47][0-9]{13}$/",
//             "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
//         );
          
//         if (preg_match($cardtype['visa'],$number)){
//             $type= "visa";
//             return 'visa';
//         }
        
//         else if (preg_match($cardtype['mastercard'],$number)){
//             $type= "mastercard";
//             return 'mastercard';
//         }
          
//         else if (preg_match($cardtype['amex'],$number)){
//             $type= "amex";
//             return 'amex';
//         }
        
//         else if (preg_match($cardtype['discover'],$number)){
//             $type= "discover";
//             return 'discover';
//         }
        
//         else{
//             return false;
//         } 
//     }// Validate Card Funtion;

//     if (validatecard($number) !== false){
//       //echo "<green> $type detected. credit card number is valid</green>";
//         $cardValidation = true;
//     }
//     else{
//         $response['status'] = 'Failed';
//         $response['message'] = 'Your card number is not valid';
//     }
    
//     if($cardValidation){
//         $contributor_type = 'Regular';
//         $guest_first_name = '';
//         $guest_last_name  = '';
//         $guest_email      = '';
//         $guest_phone      = '';
//         $datetime         = '';
//         //$remind_cards     = 
//         $remind_cards = $amount/5;
//         $remind_cards = (int)$remind_cards;
//         $stmt = $pdo_conn->prepare("INSERT INTO `contributions` (`user_id`,`contributor_type`,`guest_first_name`,`guest_last_name`,`guest_email`,`guest_phone`,`amount`,`datetime`) VALUES (?,?,?,?,?,?,?,UNIX_TIMESTAMP() )");
//         $stmt->bind_param('issssss',$_SESSION['userid'],$contributor_type,$guest_first_name,$guest_last_name,$guest_email,$guest_phone,$amount);
        
//         if($stmt->execute()){   //if query executed successfully
//             $response['status'] = 'Success';
//             $response['message'] = 'Your contribution amount has been successfully.';
//             $response['autoDown'] = 'Yes';
//             $_SESSION['check_contribute'] = "Yes";
            
//             $stmt = $pdo_conn->prepare("SELECT `id`,`remind_cards`,`first_name`,`last_name`,`email_id` from `users` WHERE `id` = ? ");
//             $stmt->bind_param("i",$_SESSION['userid']);
//             $stmt->execute();
//             $result = get_result($stmt);
//             if(count($result)>0){ 
//                 while($row = array_shift( $result)) {  
//                     $name = $row["first_name"]." ".$row["last_name"];
//                     $email = $row["email_id"];
//                     $old_remind_cards = $row['remind_cards'];
//                     $id= $row['id']; 
//                 }
                
//                 $remind_cards = $old_remind_cards+$remind_cards;
//                 $stmt = $pdo_conn->prepare("UPDATE `users` SET remind_cards = ? WHERE `id` = ? ");
//                 $stmt->bind_param('si',$remind_cards,$id);
//                 $stmt->execute();
                
//                 $header = 'Congratulations';
//                 $body   = " Hi <b>'.$email.,'</b>,<br/> thankyou for your contribution. $'.$amount.' ";                 
//                 $subject = "Contributor amount.";
//                 mailFuntion($email, $subject, $header, $body);
//             }
//         }
//         else{
//             $response['status'] = 'Error';
//             $response['message'] = 'Your contribution amount has been successfully.';
//             $response['autoDown'] = 'No';
//         }
//     }
//     echo json_encode($response);
// }
//makePayment

else if($operation == 'get-contributor'){
    $primaryKey = 'id'; 
    $table = 'contributions';
    $table2 = 'users';
    $p_table = "payments_history";
    $columns = array(
          array( 'db' => '`c`.`id`', 'dt' => 0 , 'field' => 'id' ),
          array( 'db' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)', 'dt' => 2, 'field' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)'  ),
          array( 'db' => '`c`.`contributor_type`', 'dt' => 3 , 'field' => 'contributor_type'),
          array( 'db' => '`c`.`amount`', 'dt' => 4 , 'field' => 'amount',
                  'formatter' => function($d, $row ){
                    return "$".number_format($row['amount'],2);
                }
           ),
          array( 'db' => '`c`.`donation_amount`', 'dt' => 5 , 'field' => 'donation_amount',
                  'formatter' => function($d, $row ){
                    return '$ '.number_format($row['donation_amount'],2);
                }
           ),
          array( 'db' => '`p`.`transaction_id`', 'dt' => 1, 'field' => 'transaction_id'),
          array( 'db' => '`c`.`datetime`', 'dt' => 6, 
                'formatter' => function( $d, $row ) {
                  if($row['datetime'] == NULL){
                      $mydate = '';
                  }
                  else{
                      $mydate =  date('d-M-Y h:i A',$row['datetime']);
                  }
                  return $mydate;
              },
                    'field' => 'datetime'
          ),
          array(
              'db'        => '`u`.`id`',
              'dt'        =>7,
              'field' => 'id',
              'formatter' => function( $d, $row ) {
                return '<div class="d-flex">
                  <a href="javascript:void(0)" title="View" onclick="showdetails('.$row['id'].')"><i class="fa fa-eye text-primary text-center ml-4"></i></a> 
                  </div>';
                }
              )
         );
    $joinQuery = "FROM `{$table}` AS `c` LEFT JOIN `{$table2}` AS `u`
                   ON `u`.`id`=`c`.`user_id` 
                   LEFT JOIN `{$p_table}` AS `p` ON `p`.`order_id`=`c`.`payment_order_id` ";
    $extraCondition = " `c`.`user_id` = '".$_SESSION['user_id']."' AND `c`.`contributor_type` = 'Regular' ";
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
}

else if($operation == 'get-download'){
    $primaryKey = 'id'; 
    $table = 'generated_cards';
    $columns = array(
            array( 'db' => '`g`.`id`', 'dt' => 0 , 'field' => 'id' ),
            array( 'db' => '`c`.`card_name`', 'dt' => 1 , 'field' => 'card_name', 
                    'formatter' => function($d, $row ){
                        if($row['card_name'] =="" || $row['card_name']==null){
                            return "Custom Card";
                        }else{
                            return $row['card_name'];
                        }
                    }
                ),
            array( 'db' => '`g`.`card_amount`', 'dt' => 2 , 'field' => 'card_amount',
                    'formatter' => function($d, $row ){
                        return "$".number_format($row['card_amount'],2);
                    }
                ),
           
            array( 'db' => '`g`.`is_reedemed`', 'dt' => 3 , 'field' => 'is_reedemed' ),
            array( 'db' => '`g`.`generated_on`', 'dt' => 4, 'field' => 'generated_on', 
                    'formatter' => function( $d, $row ) {
                        return $row['generated_on'];
                    }
                )
            );
    $joinQuery = "FROM `{$table}` AS `g` LEFT JOIN `cards` AS `c` ON `c`.`id`=`g`.`card_id` ";
    $extraCondition = " `g`.`user_id` = '".$_SESSION['user_id']."' AND `g`.`status` = 'Downloaded' ";
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
}
// get-contributor

else if($operation == 'sendLink'){
    $data = array();
    $email = $_POST['email'];
    
    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `email_id` = ?");
    $stmt->bind_param("s",$email);  //Binding parameters into statement
    $stmt->execute();               //Execute query
    $result = get_result($stmt);  //Fetch results
    
    if(count($result) > 0){ //if user exists
        //$token = bin2hex(random_bytes(12))
    }
    else{
        $data['status'] = 'No Record';
    }
    echo json_encode($data);
}
// forgot Link

else if($operation == "forgotPassword"){        
    $response = array();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $type   =  'contributor';
    $encypt_password = password_hash($password, PASSWORD_DEFAULT);        
    $stmt = $pdo_conn->prepare("UPDATE `users` SET `password` = ? WHERE `email_id` = ? AND `user_type` = ? ");
    $stmt->bind_param('sss',$encypt_password,$email,$type);
    if($stmt->execute()){
        $response['status'] = 'Success';
        $stmt = $pdo_conn->prepare("UPDATE `users` SET `reset_link` = '' WHERE `email_id` = ? AND `user_type` = ? ");
        $stmt->bind_param('ss',$email,$type);
        if($stmt->execute()){
            $response['status'] = 'Success';
        }
    }
    else{
        $response['status'] = 'Failed';
    }
    echo json_encode($response);
}//forgotPassword

else if($operation== "emptyData"){
    $data = array();
    if(isset($_SESSION['guestStatus'])){
        $_SESSION['guestStatus'] = "";
        $_SESSION['guestAmt'] = "";
        
    }
    if(isset($_SESSION['unique_user_id'])){
        unset($_SESSION['unique_user_id']);
    }
    
    $last_id = isset($_SESSION['last_insert_id'])?$_SESSION['last_insert_id']:"";
    $message = $_POST['message'];
    $allow_home = isset($_POST['allow_home'])?$_POST['allow_home']:"";
    if(!empty($last_id) AND !empty($message)){
       $stmt = $pdo_conn->prepare("UPDATE `contributions` SET  `contribution_msg` = ?, `allow_home` = ?  WHERE `id` = ?  ");
        $stmt->bind_param("ssi",$message,$allow_home,$last_id);//binding parameter
        if($stmt->execute()){
            $data['status'] = "Success";
            unset($_SESSION['last_insert_id']);
        }
    }
    unset($_SESSION['last_insert_id']);
    $data['status'] = 'Success';
    echo json_encode($data);
}
//

else if($operation== "paymentModalClose"){
    $data = array();
    if(isset($_SESSION['modal'])){
        $_SESSION['modal'] = "";
    } 

    if(isset($_SESSION['user_payment_status'])){
        $_SESSION['user_payment_status'] = "";
        unset($_SESSION['user_payment_status']);
    }
    
    if(isset($_SESSION['guestStatus'])){
        $_SESSION['guestStatus'] = "";
        $_SESSION['guestAmt'] = "";
        if(isset($_SESSION['unique_user_id'])){
            unset($_SESSION['unique_user_id']);
        }
    }
    
    $data['status'] = 'Success';
    echo json_encode($data);
}

//emptySessionData operation
else if($operation== "emptySessionData"){
    $data = array();
    
    if(isset($_SESSION['modal'])){
      $_SESSION['modal'] = "";
    } 

    if(isset($_SESSION['user_payment_status'])){
      $_SESSION['user_payment_status'] = "";
      unset($_SESSION['user_payment_status']);
    }
    
    if(isset($_SESSION['guestStatus'])){
        $_SESSION['guestStatus'] = "";
        $_SESSION['guestAmt'] = "";
        if(isset($_SESSION['unique_user_id'])){
            unset($_SESSION['unique_user_id']);
        }
    }
    
    $last_id = isset($_SESSION['last_insert_id'])?$_SESSION['last_insert_id']:"";
    $message = !empty($_POST['message'])?$_POST['message']:"";

    if(!empty($last_id) AND !empty($message)){
        $stmt = $pdo_conn->prepare("UPDATE `contributions` SET  `contribution_msg` = ? WHERE `id` = ?  ");
        $stmt->bind_param("si",$message,$last_id);//binding parameter
        if($stmt->execute()){
            $data['status'] = "Success";
            unset($_SESSION['last_insert_id']);
        }
    }
    $data['status'] = 'Success';
    echo json_encode($data);
}
//emptySessionData

// else if($operation == 'donateAmount'){
//      $cardValidation = false;
//      $response = array();
     
//       $amount       = $_POST['amount'];
//       $first_name   = $_POST['firstName'];
//       $last_name    = $_POST['lastName'];
//       $email        = $_POST['email'];
//       $phone        = $_POST['phone'];
//       $number       = $_POST['cardNumber'];
//       $month        = $_POST['month'];
//       $year         = $_POST['year'];
//       $cvv          = $_POST['cvv'];

//       if($amount == '' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter donate amount.';
//           echo json_encode($response);
//           exit();
//         }
//         if($first_name == '' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter first name.';
//           echo json_encode($response);
//           exit();
//         }
//         if($last_name == '' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter last name.';
//           echo json_encode($response);
//           exit();
//         }
//         if($email == '' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter email.';
//           echo json_encode($response);
//           exit();
//         }

//         function checkemail($str) {
//          return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
//         }

//         if($email != '')
//         {
//            if(!checkemail($email)){
//               $response['status'] = 'Failed';
//               $response['message'] = 'Please enter valid email.';
//               echo json_encode($response);
//               exit();
//            }
//         }
//         if($phone == '' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter phone number.';
//           echo json_encode($response);
//           exit();
//         }
 
//         if($number == '' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter card number.';
//           echo json_encode($response);
//           exit();
//         }
 
//         if($month == 'Select Month' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please choose month.';
//           echo json_encode($response);
//           exit();
//         }
//         if($year == 'Select Year' )
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please choose year.';
//           echo json_encode($response);
//           exit();
//         }
//         if($cvv == '')
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter cvv number.';
//           echo json_encode($response);
//           exit();
//         }
//         if(strlen($cvv) < 3)
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Please enter correct cvv code.';
//           echo json_encode($response);
//           exit();
//         }

//       function validatecard($number)
//        {
//           global $type;
//           $cardtype = array(
//               "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
//               "mastercard" => "/^5[1-5][0-9]{14}$/",
//               "amex"       => "/^3[47][0-9]{13}$/",
//               "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
//           );
//           if (preg_match($cardtype['visa'],$number))
//           {
//             $type= "visa";
//             return 'visa';
//           }
//           else if (preg_match($cardtype['mastercard'],$number))
//           {
//             $type= "mastercard";
//             return 'mastercard';
//           }
//           else if (preg_match($cardtype['amex'],$number))
//           {
//             $type= "amex";
//             return 'amex';
//           }
//           else if (preg_match($cardtype['discover'],$number))
//           {
//              $type= "discover";
//              return 'discover';
//           }
//           else
//           {
//             return false;
//           } 
//        }// Validate Card Funtion;

//        if (validatecard($number) !== false)
//         {
//          //echo "<green> $type detected. credit card number is valid</green>";
//           $cardValidation = true;
//         }
//         else
//         {
//           $response['status'] = 'Failed';
//           $response['message'] = 'Your card number is not valid';
//         }
//         if($cardValidation)
//         {
//           $contributor_type   = 'Guest';
//           $userid = '';
//           $stmt = $pdo_conn->prepare("INSERT INTO `contributions` (`user_id`,`contributor_type`,`guest_first_name`,`guest_last_name`,`guest_email`,`guest_phone`,`amount`,`datetime`) VALUES (?,?,?,?,?,?,?,now() )");

//         $stmt->bind_param('issssss',$userid,$contributor_type,$first_name,$last_name,$email,$phone,$amount);
        
//            if($stmt->execute()){   //if query executed successfully
//               $response['status'] = 'Success';
//               $response['message'] = 'Thank you for your contribution. Now you can download Thank You cards.';
//               $response['autoDown'] = 'Yes';
//               $_SESSION['check_contribute'] = "Yes";
//               $response['amount'] = $amount;
//               $name = $first_name." ".$last_name;

//               mailFuntion($email,$name,$amount);
//            }
//            else{
//               $response['status'] = 'Error';
//               $response['message'] = 'Your contribution amount has been unsuccessfully.';
//               $response['autoDown'] = 'No';
//           }
//         }

//         echo json_encode($response);
// }//Donate Money Operations

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
    $usertype = 'admin';  
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
}
// Change Status

else if($operation == "makeOption"){
    $result = array();
    if(isset($_SESSION['unique_user_id'])){
        $sql = "SELECT count(`card_amount`) as count , card_amount
                                            FROM `generated_cards` 
                                            WHERE `status`='New' AND `user_email` = '".$_SESSION['guest_email']."'
                                            AND `user_unique_code` = '".$_SESSION['unique_user_id']."' 
                                            GROUP BY card_amount ORDER BY card_amount ASC";
    }
    else if(isset($_SESSION['user_id'])){
        $sql = "SELECT count(`card_amount`) as count, card_amount
                                                FROM `generated_cards` 
                                                WHERE `status`='New' AND `user_id` = ".$_SESSION['user_id']." AND `card_amount`  IS NOT NULL
                                                GROUP BY card_amount ORDER BY `card_amount` ASC";
    }
    if(isset($_SESSION['unique_user_id']) || isset($_SESSION['user_id']) ){
        $res = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($res) > 0){
            $result['status'] = "Success";
            while($data = mysqli_fetch_assoc($res)){
                $result['data'][] = $data;
            }
        }else{
            $result['status'] = "Failed";
        }
    }                                 
    echo json_encode($result);                            
}

else if($operation == "guestOption"){
    $result = array();
    $sql = "SELECT count(`card_amount`) as count , card_amount
            FROM `generated_cards` 
            WHERE `status`='New' AND `user_email` = '".$_SESSION['guest_email']."'
            AND user_unique_code IS NOT NULL 
            GROUP BY card_amount ORDER BY card_amount ASC";
    $res = mysqli_query($conn, $sql);
    if(mysqli_num_rows($res) > 0){
        $result['status'] = "Success";
        while($data = mysqli_fetch_assoc($res)){
            $result['data'][] = $data;
        }
    }else{
        $result['status'] = "Failed";
    } 
    echo json_encode($result);                            
}

  //Leave Message
  // else if($operation == "leaveMsg"){
  //     $response = array();
  //     $last_id = isset($_SESSION['last_insert_id'])?$_SESSION['last_insert_id']:"";
  //     $message = $_POST['message'];
  //     if($last_id){
  //          $stmt = $pdo_conn->prepare("UPDATE `contributions` SET  `contribution_msg` = ? WHERE `id` = ?  ");
  //           $stmt->bind_param("si",$img_name,$last_id);//binding parameter
  //           if($stmt->execute())
  //           {
  //               $response['status'] = "Success";
  //           }
  //     }
  //   echo json_encode($response);

  // }//Leave Message

else if($operation == "shareRefferalLink"){
    $email  = $_POST['email'];
    $phone  = $_POST['phone'];
    $referral_code = "";
    $data = array();
    $stmt = $pdo_conn->prepare("SELECT `first_name`, `last_name`, `referral_code` from `users` WHERE `id` = ? ");
    $stmt->bind_param("i",$_SESSION['user_id']);
    $stmt->execute();
    $result = get_result($stmt);
    if(count($result)>0){ 
        while($row = array_shift( $result)) {  
            $referral_code = $row["referral_code"];
            $full_name     = $row['first_name']." ".$row['last_name']; 
        }
    }

    $st = $pdo_conn->prepare("SELECT `id` from `users` WHERE `email_id` = ? ");
    $st->bind_param("s",$email);
    $st ->execute();
    $res = get_result($st);
    if(count($res)>0){ 
        $data['status'] = 'emailExist';
        $data['message'] = "The person is already a member.";
        echo json_encode($data);
        exit();
    }
    
    if($referral_code!=""){
        $subject = "Referral code";
        //http://pranamthankyou.org
        //$message = "Hi, ".$full_name." just downloaded easy-to-print THANK YOU cards from ".$final_url."/contributor/register.php?ref=".$referral_code." <br/>Download yours NOW! Share Smiles Securely!";
        $name_message = "Hi, ".$full_name." just downloaded easy-to-print THANK YOU cards from <br/><a href='https://pranamthankyou.org'><b><u>Pranam-ThankYou</u></b><br/></a><br/>Download yours NOW! Share Smiles Securely!'";  
    }
    $header = "Welcome";
    
    if(!empty($email) && !empty($referral_code)){
        if(shareRefferalLinkMail( $email, $subject, $header, $name_message, $final_url,$referral_code )){
            $data['status'] = 'Success';
            $data['message'] = "Refferal link is sent.<br/><br/>You can also share referral code from profile details.";      
        }else{
            $data['status'] = 'Failed';
            $data['status'] = "Refferal link sending failed.<br/><br/>You can also find and share referral code from profile details.";
        }  
    }
    echo json_encode($data);
    exit();
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
                                    <p style="margin: 0;">If these emails get annoying, please click <a href="'.$final_url.'/unsubscribe.php?code='.$email_sub_code.'" target="_blank" style="color: #c20a0a;">here</a> to opt out.</p>
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

function shareRefferalLinkMail($email, $subject, $header, $name_message, $final_url,$referral_code){
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
        $mail->setFrom('contact@pranamthankyou.org', 'Pranam-Thank-You');
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
                                <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 0px 30px;">
                                    <p class="mb-0" style="font-size:18px; margin-bottom:0px !important;">'.$name_message.'</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="center" style="padding: 0px 30px 60px 30px;">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="center" style="border-radius: 3px;" bgcolor="#c20a0a">
                                                <a href="'.$final_url.'/member/registration.php?ref='.$referral_code.'"
                                                    style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #fce8e6; display: inline-block;">
                                                    Get Your Cards
                                                </a>
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
                                <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; margin-bottom:20px; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                    <p style="margin-bottom:30px; text-align:center;">In need of any kind of support, please feel free to drop an email at contact@pranamthankyou.org<a href="#" target="_blank" style="color: #111111; font-weight: 700;"></a>.</p>
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
//Mail function

?>