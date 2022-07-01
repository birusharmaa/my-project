<?php
session_start();
//date_default_timezone_set('');
if(isset($_POST["operation"])){
	$operation = $_POST["operation"];
	require("config.php");
}else{	//return to request page if operation is not set
	exit();
}
  // login check
  if($operation == "auth")
  {
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $response = array(); 

    if(strlen($password) < 6)
    {
      $response['status']  = 'Failed';
      $response['message'] = 'Please enter minimum 6 digits password.';
      echo json_encode($response);
      exit();
    }//Check password length

    $stmt = $pdo_conn->prepare("SELECT * from `users` WHERE `email` = ? ");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = get_result($stmt);        
    if(count($result)>0)//Email exits or not
    { 
      while($row=array_shift($result))
      {  
        $name           = $row["name"];
        $email          = $row["email"];
        $phone          = $row["phone"];
        $bcrypt_password= $row["password"];
        $address        = $row["address"];
        $organization   = $row["organization"];
        $website        = $row['website'];
        $registered_on  = $row['registered_on'];
        $user_type      = $row['user_type'];
        $status         = $row['status'];
        $user_id        = $row['id'];
      }//While
      if(password_verify($password,$bcrypt_password))// password verify 
      { 
        if($status=='Active')//if user is active
        {         

          if($_POST["checks"] == 1)
          {
            setcookie ("email",$_POST["email"],time()+(86400 * 30), "/");
            setcookie ("password",$_POST["password"],time()+(86400 * 30), "/");
            setcookie ("user",$user_id,time()+(86400 * 30), "/");
            setcookie ("user_type",$user_type,time()+(86400 * 30), "/");
            setcookie ("name",$name,time()+(86400 * 30), "/");
          }
          else
          {
            setcookie("username","");
            setcookie("password","");
          }

          $_SESSION["email"]        = $email;
          $_SESSION["username"]     = $name;
          $_SESSION['userid']       = $user_id;
          $_SESSION['registered_on']= $registered_on;
          $_SESSION['usertype']     = $user_type;
          $response['status']      = "Success";
          $response['message']      = "Logged in successfully. Redirecting to your account...";
        }//If user is active
        else 
        {  
          $response['status'] = "Failed";
          $response['message'] = 'Your account is inactive. Please contact support.';
        }//Else condition user is Inactive
      }//If condition for authentication 
      else
      {
        $response['status'] = "Failed";
        $response['message'] = 'Wrong credentials. Please check again.';
      }//else condition Authentication failed 
    }//If condition email exits or not
    else
    {
      $response['status'] = 'blank';
      $response['message'] = 'Your email doesn'."'".'t exists.';
    }// else email does't not exist
    echo json_encode($response);

  }//step_3 all validation check and save in user table

  //Reset Password operation start
  else if($operation == "resetpassword")
  {
    $message = array();
    $email   = $_POST['email'];
    if($email != '')
    {
        $message['success'] = true;
        $message['message'] = "Reset link sent on your email id! Please check and reset password";
    }
    echo json_encode($message);
  }
  //Reset Password operation close

  

?>
