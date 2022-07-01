<?php
session_start();
//date_default_timezone_set('');

if(isset($_POST["operation"])){
	$operation = $_POST["operation"];
	require("config.php");
  require("ssp.class.php"); 
}else{	//return to request page if operation is not set
	exit();
} 
  // Data insert in sign_up_request
  if($operation == 'step_1'){ 
    
    $response = array();

    $name    = $_POST["name"]; 
    $email   = $_POST["email"];
    $phone   = $_POST["phone"];
    $firm    = $_POST["firm"];
    $website = $_POST["website"];
    $address = $_POST["address"];
    if($name == '')
    {
      $response['status']  = 'Failed';
      $response['name']    = "Name";
      $response['message'] = 'Please enter full name.';
    }
    if($email == '')
    {
      $response['status']  = 'Failed';
      $response['email']   = "Email";
      $response['message'] = 'Please enter email id.';
    }
    if($phone == '')
    {
      $response['status']  = 'Failed';
      $response['phone']   = "Phone";
      $response['message'] = 'Please enter phone number.';
    }

    if($name == '' || $email == '' || $phone == '')
    {
      echo json_encode($response);
      exit();
    }

    //Email exits check in users
    $stmt = $pdo_conn->prepare("SELECT * from `users` WHERE `email` = ? ");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = get_result($stmt);        
    if(count($result)>0)
    { 
      while($row = array_shift( $result))
      { 
        $old_email = $row['email'];
        $old_pass  = $row['password'];
        $user_id   = $row['id'];
      }// While
      if($old_pass == "")
      {
        $response['status']  = "checkUser";
        $response['message'] = "You are on signup process! Please ";
        $_SESSION['user_id'] = $user_id;
        $_SESSION['otp_verify'] = "Yes";
        echo json_encode($response);
        exit();
      }
      else
      {
        $response['status']  = "checkEmail";
        $response['message'] = "This email already exists! Please ";
        $_SESSION['user_id'] = $user_id;
        echo json_encode($response);
        exit();
      }
    }//If Count
    
    //Email exits check in users


    $random_number = mt_rand(100000, 999999);// Random number creation
    //Phone OTP sent process here

    //

    $stmt = $pdo_conn->prepare("INSERT INTO `sign_up_requests` (`name`,`email`,`phone`,`address`,`organization`,`website`,`requested_on`,`otp`) VALUES (?,?,?,?,?,?,UNIX_TIMESTAMP(),?)");
    $stmt->bind_param('sssssss',$name,$email,$phone,$address,$firm,$website,$random_number);
     if($stmt->execute()){   //if query executed successfully
      $last_id = $stmt->insert_id; // Inserted last id
      $_SESSION['sign_up_request_id'] = $last_id;
      $response['status'] = 'Success';
     }
     else
     {
      $response['status'] = 'Failed';
     }
     echo json_encode($response);
  }// Step first Operation
  
  // Mobile number verify
  else if($operation == "step_2")
  {
    $verifyValue = $_POST['verifyValue'];
    $response    = array();

    $stmt = $pdo_conn->prepare("SELECT * from `sign_up_requests` WHERE `id` = ? ");
    $stmt->bind_param("i",$_SESSION['sign_up_request_id']);
    $stmt->execute();
    $result = get_result($stmt);        
    if(count($result)>0)
    { 
      while($row = array_shift( $result))
      {  
        $old_otp     = $row['otp'];
        $name        = $row['name'];
        $email       = $row['email'];
        $phone       = $row['phone'];
        $address     = $row['address'];
        $organization= $row['organization'];
        $website     = $row['website'];
        $requested_on= $row['requested_on'];
      }
    }

    if($old_otp == $verifyValue) // New verify value and old OTP matching
    {
      $current_time = strtotime("now");
      $time_diff =  $current_time-$requested_on;

      if($time_diff < 900)
      {
        $response['status']     = 'Success';
        $_SESSION['otp_verify'] = "Yes";
        $email_verified = "N";
        $phone_verified = "Y";
        $user_type      = "User";
        $status         = "Active";

        $stmt = $pdo_conn->prepare("INSERT INTO `users` (`name`,`email`,`phone`,`address`,`organization`,`website`,`registered_on`,`email_verified`,`phone_verified`,`user_type`,`status`) VALUES (?,?,?,?,?,?,UNIX_TIMESTAMP(),?,?,?,?)");
        $stmt->bind_param('ssssssssss',$name,$email,$phone,$address,$organization,$website,$email_verified,$phone_verified,$user_type,$status);
        if($stmt->execute())// Insert data in users table
          { 
           $_SESSION['user_id'] = $stmt->insert_id;
            if($_SESSION['user_id'])
            {
              $stmt = $pdo_conn->prepare("DELETE FROM `sign_up_requests` WHERE id = ? ");
              $stmt->bind_param('i',$_SESSION['sign_up_request_id']);
              if($stmt->execute())
              {
                $response['status'] = "Success";
              }
            } //$_SESSION['user_id'] 
          }//If stmt-execute
         else
          { 
            $response['status'] = 'Failed';
          }
       }//Checking 15 minutes
       else
       {
        $response['status']  = 'Failed';
        $response['expiry']  = "Expiry";  
        $response['message'] = 'Your OTP is expired. We have send you a new OTP.';
       }

    } //verify If close section 
    else
    {
      $response['status']  = 'Failed';
      $response['message'] = 'OTP is wrong. Please check and try again.';
    }
    echo json_encode($response);

  }//step_2 OTP varifications

  //Resend OTP
  else if( $operation == "resendOTP")
  {
    $response = array();
    $stmt = $pdo_conn->prepare("SELECT `otp`,`phone` from `sign_up_requests` WHERE `id` = ? ");
    $stmt->bind_param("i",$_SESSION['sign_up_request_id']);
    $stmt->execute();
    $result = get_result($stmt);        
    if(count($result)>0)
    { 
      while($row = array_shift( $result))
      {  
        $otp   = $row['otp'];
        $phone = $row['phone'];
      }
    }
    //resend otp here 
    //$otp
    //$phone

    //If opt sent successfully
    if($otp)
    {
      $response['status'] = "Success";
      $response['message'] = "Your otp is sent again.";  
    }
    
    echo json_encode($response);

  }
  //Resend OTP

  // Password validation check and save sign details
  else if($operation == "step_3")
  {
    $password = $_POST['password'];
    $cPassword = $_POST['cPassword'];
    $response    = array(); 

    if($password == "" || $cPassword == "" )
    {
      $response['status']  = 'Failed';
      $response['message'] = 'Please enter password.';
      echo json_encode($response);
      exit();
    }  

    if($password != $cPassword)
    {
      $response['status']  = 'Failed';
      $response['message'] = 'Password and confirm password are not matching';
      echo json_encode($response);
      exit();
    }
    if( strlen($password) < 6 )
    {
      $response['status']  = 'Failed';
      $response['message'] = 'Please enter minimum 6 digits password.';
      echo json_encode($response);
      exit();
    }
    
    $encypt_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo_conn->prepare("UPDATE `users` SET  `password` = ? WHERE `id` = ? ");
    $stmt->bind_param("si",$encypt_password,$_SESSION['user_id']);
    if($stmt->execute())
    {
      $response['status']  = 'Success';
      $_SESSION['otp_verify'] = "";
      unset($_SESSION['sign_up_request_id']);
    }
    else
    {
      $response['status']  = 'Failed';
      $response['message'] = 'Something wrongs.';
    }
    echo json_encode($response);

  }//step_3 all validation check and save in user table

  

?>
