<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include '../server/vendor/autoload.php';
$full_url  = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$host_name = $_SERVER['HTTP_HOST'];
  
if(isset($_POST["operation"])){
	  $operation = $_POST["operation"];
	  require("./config.php");
    require( './ssp.class.php' );
}else{
	 exit(); 
}

//User type get
$type = "";
if(isset($_POST['type'])){
    if($_POST['type'] == "contributor"){
        //Set contributor session id
        $id   = $_SESSION['user_id'];
        $type = "contributor";
    }
    if($_POST['type'] == "redeemer"){
        //Set contributor session id
        $id   = $_SESSION['user_id'];
        $type = "redeemer";
    }
    if($_POST['type'] == "admin"){
        //Set contributor session id
        $id   = $_SESSION['user_id'];
        $type = "admin";
    }
}else{
    $type = "admin";
}

//Final get url
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
//Final url

$_SESSION['logged'] = "failed";

//User list details
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

    $response = array();
    $first_name = $_POST["firstName"]; 
    $last_name = $_POST['lastName'];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST['password'];
    $userType = $_POST['userType']; 
    
    $result = mysqli_query($conn,"SELECT * FROM `users` where `email_id` ='$email' ");
    $num_rows = mysqli_num_rows($result);
    if($num_rows >= 1){
        $response['status'] = 'Email_Taken';
    }
    else
    {

      $encypt_password = password_hash($password, PASSWORD_DEFAULT);    
      $stmt = $pdo_conn->prepare("INSERT INTO `users` (`first_name`,`last_name`,`email_id`,`password`,`phone`,`user_type`,`registered_on`) VALUES (?,?,?,?,?,?,UNIX_TIMESTAMP() )");

      $stmt->bind_param('ssssss',$first_name,$last_name,$email,$encypt_password,$phone,$userType);
    
       if($stmt->execute()){   //if query executed successfully
           $response['status'] = 'Success';
       }
       else{
          $response['status'] = 'Error';
      }
    }
   echo json_encode($response);
}// Save User
                      
else if($operation == 'auth'){
 
    $originalpassword = '';
    $response = array();
     
    if(!empty($_REQUEST['data'])){
        $data = $_REQUEST['data'];
        $email = $data['s_email'];
        $password = $data['s_password'];
        $userType = 'admin';
        $stmt = $pdo_conn->prepare("SELECT * from `users` WHERE `email_id` = ? AND `user_type` = ?  ");
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
            }
            if(password_verify($password, $bcrypt_password)){ 
                if($status == 'A' ){         
                    $_SESSION["email"]      = $email;
                    $_SESSION["user_name"]   = $first_name." ".$last_name;
                    $_SESSION['user_id']     = $user_id;
                    $_SESSION['registered_on'] = $registered_on;
                    $_SESSION['user_type']   = $user_type;
                    $_SESSION['logo']       = $logo;
                    unset($_SESSION['is_front_page']);
                    $response['success']    = true;
                    $response['message']    = "Logged in successfully. Redirecting to your account...";
                    $message = 'You have logged in to your account.';
                    $subject = "Admin Login";
                    //Send mail function
                    $header = "Welcome";
                    //mailFuntion($email, $subject, $header, $message);
                    if($email_subscritpion=="Y"){
                        mailFuntion($email, $subject, $header, $message ,$email_sub_code,$final_url);
                    }
                }
                else{  
                    $response['success'] = false;
                    $response['message'] = 'This account is inactive. Contact us at ';
                }
            }
            else{
                $response['error'] = true;
                $response['message'] = 'Authentication failed. Wrong credentials.';
            }
        }
        else{
            $response['error'] = 'Wrong';
            $response['message'] = 'This email id is not registered!';
        }
    }
    echo json_encode($response);
}//Save users


    else if($operation == 'get-contributor'){
        $contributeType = $_POST['contributeType'];
        $primaryKey = 'id'; 
        $table = 'contributions';
        $table2 = 'users';
        $columns = array(
                array( 'db' => '`c`.`id`', 'dt' => 0 , 'field' => 'id' ),
                array( 'db' => '`p`.`transaction_id`', 'dt' => 1, 'field' => 'transaction_id'),
                array( 'db' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)', 'dt' => 2, 'field' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)'  ),
                array( 'db' => '`c`.`contributor_type`', 'dt' => 3 , 'field' => 'contributor_type'),
                array( 'db' => '`c`.`amount`', 'dt' => 4 , 'field' => 'amount',
                    'formatter' => function($d, $row){
                        return "$ ".number_format($row['amount'],2);
                    }
                ),
                array( 'db' => '`c`.`donation_amount`', 'dt' => 5, 'field' => 'donation_amount',
                    'formatter' => function($d, $row){
                        return "$ ".number_format($row['donation_amount'],2);
                    }
                ),
                array( 'db' => '`c`.`datetime`', 'dt' => 6, 
                    'formatter' => function( $d, $row ) {
                        if($row['datetime'] == NULL){
                            $mydate = '';
                        }else{
                            $mydate =  date('d-M-Y h:i A', $row['datetime']);
                        }
                        return $mydate;
                    },
                    'field' => 'datetime'
                ),
                array(
                    'db'        => '`u`.`id`',
                    'dt'        => 7,
                    'field' => 'id',
                    'formatter' => function( $d, $row ) {
                        return '<div class="d-flex">
                        <a href="javascript:void(0)" title="View" onclick="showdetails('.$row['id'].')"><i class="fa fa-eye text-primary text-center ml-4"></i></a> 
                        </div>';
                    }
                )
            );
        $joinQuery = "FROM `{$table}` AS `c` 
                    LEFT JOIN `{$table2}` AS `u` ON `u`.`id`=`c`.`user_id` 
                    LEFT JOIN `payments_history` AS `p` ON `p`.`order_id`=`c`.`payment_order_id` ";
        $extraCondition = " `c`.`contributor_type` = '".$contributeType."' ";
        $groupBy = NULL;
        $having = NULL;
        echo json_encode(
            SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
        );
    }// get-contributor

else if($operation == 'get-contributor-guest'){
    $contributeType = $_POST['contributeType'];
    $primaryKey = 'id'; 
    $table = 'contributions';
    $columns = array(
                array( 'db' => '`c`.`id`', 'dt' => 0 , 'field' => 'id' ),
                array( 'db' => '`p`.`transaction_id`', 'dt' => 1, 'field' => 'transaction_id'),
                array( 'db' => 'CONCAT( `c`.`guest_first_name`," ", `c`.`guest_last_name`)', 'dt' => 2, 'field' => 'CONCAT( `c`.`guest_first_name`," ", `c`.`guest_last_name`)'  ),
                array( 'db' => '`c`.`contributor_type`', 'dt' => 3, 'field' => 'contributor_type'),
                array( 'db' => '`c`.`amount`', 'dt' => 4, 'field' => 'amount',
                    'formatter' => function($d, $row){
                        return "$ ".number_format($row['amount'],2);
                    }
                ),
                array( 'db' => '`c`.`donation_amount`', 'dt' => 5, 'field' => 'donation_amount',
                    'formatter' => function($d, $row){
                        return "$ ".number_format($row['donation_amount'],2);
                    }
                ),
                array( 'db' => '`c`.`datetime`', 'dt' => 6, 
                    'formatter' => function( $d, $row ) {
                        if($row['datetime'] == NULL){
                            $mydate = '';
                        }else{
                            $mydate =  date('d-M-Y H:i', $row['datetime']);
                        }
                        return $mydate;
                    },
                    'field' => 'datetime'
                )
            );
    $joinQuery = "FROM `{$table}` AS `c` 
                LEFT JOIN `payments_history` AS `p` ON `p`.`order_id`=`c`.`payment_order_id` ";
    $extraCondition = " `c`.`contributor_type` = '".$contributeType."' ";
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
}// get-contributor-guest

else if($operation == 'sendLink'){ 
    $data = array();
    $email = $_POST['email'];
    
    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `email_id` = ?");
    $stmt->bind_param("s",$email);  //Binding parameters into statement
    $stmt->execute();               //Execute query
    $result = get_result($stmt);  //Fetch results
    
     if(count($result) > 0)
      { //if user exists
        //$token = bin2hex(random_bytes(12))
      }
     else{
      $data['status'] = 'No Record';
     }
    echo json_encode($data);
   }// forgot Link

   //forgot Password
    else if($operation == "forgotPassword"){
        
        $response = array();
        $email = $_POST['email'];
        $password = $_POST['password'];
        $type   =  'admin';

        $encypt_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo_conn->prepare("UPDATE `users` SET `password` = ? WHERE `email_id` = ? AND `user_type` = ? ");
        $stmt->bind_param('sss',$encypt_password,$email,$type);
        if($stmt->execute())
        {
            $response['status'] = 'Success';
            $stmt = $pdo_conn->prepare("UPDATE `users` SET `reset_link` = '' WHERE `email_id` = ? AND `user_type` = ? ");
            $stmt->bind_param('ss',$email,$type);
            if($stmt->execute())
            {
              $response['status'] = 'Success';
            }
          {
            $response['status'] = 'Success';  
          }
        }
        else
         {
          $response['status'] = 'Failed';
         }
        echo json_encode($response);
    }//forgotPassword
    else if($operation == "giftCardHistory"){
    
       $url = "";
        $token = "";
        $balance = 0;
//         $brand_code = '';
// 
//         if($_POST['brand_code']!=NULL){
//             $brand_code = $_POST['brand_code'];
//         }

        
        $primaryKey = 'id'; 
        $table = 'giftbit_coupons';
         // $table2 = 'cards';
        $columns = array(
            array( 'db' => '`u`.`first_name`', 'dt' => 0,
               'formatter' => function( $d, $row ) {
                      return $row['first_name'].' '.$row['last_name'];
                  },
                    'field' => 'first_name'
             ),
              array( 'db' => '`u`.`email_id`', 'dt' => 1,
               'formatter' => function( $d, $row ) {
                      return $row['email_id'];
                  },
                    'field' => 'email_id'
             ),
            array( 'db' => '`gc`.`price`', 'dt' => 2,
               'formatter' => function( $d, $row ) {
                      return "$ ".number_format($row['price'],2);
                  },
                    'field' => 'price'
             ),
            
            array( 'db' => '`gc`.`brand_name`', 'dt' => 3,
               'formatter' => function( $d, $row ) {
                      return '<div class="text-center"><img src="'.$row['brand_image'].'" width="100px"> <br />'.$row['brand_name'].'</div>';
                  },
                    'field' => 'brand_name'
             ),
            array( 'db' => '`gc`.`status`', 'dt' => 4 ,
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
            
            array( 'db' => '`gc`.`generated_on`', 'dt' => 5 ,
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
             ),array( 'db' => '`u`.`last_name`', 'dt' => 6,
               'formatter' => function( $d, $row ) {
                      return $row['last_name'].' '.$row['last_name'];
                  },
                    'field' => 'last_name'
             ),
            array( 'db' => '`gc`.`brand_image`', 'dt' =>7, 'field' => 'brand_image'),
         );
    $joinQuery = "FROM `{$table}` AS `gc` LEFT JOIN users as `u` ON `gc`.`user_id` = `u`.`id` ";
    $extraCondition = NULL;
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
}

else if($operation == 'viewProfile'){        
    $data = array();
    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();            
    $result = get_result($stmt); 
    if(count($result) > 0){ 
        while($row = array_shift($result)){  
            $data = $row;
        }
    }
    echo json_encode($data);
}// View Users

// Edit profile
else if($operation == 'editProfile'){
    $data = array();
    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i",$_SESSION['user_id']);
    $stmt->execute();            
    $result = get_result($stmt); 
    if(count($result) > 0){ 
        while($row = array_shift($result)){  
          $data = $row;
        }
    }
    echo json_encode($data); 
}// edit  Users profile

    // Update profile
else if($operation == "updateProfile"){
    
    $response = array();
    $fname    = $_POST['fname'];
    $lname    = $_POST['lname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $status   = $_POST['status'];
    $id       = $_SESSION['user_id'];
    $_SESSION['user_name'] = $fname." ".$lname;

    $stmt = $pdo_conn->prepare("UPDATE `users` SET `first_name` = ? , `last_name` = ?,`email_id` = ? , `phone` = ? ,`status`=? WHERE `id`=? ");
    $stmt->bind_param('sssssi',$fname,$lname,$email,$phone,$status,$id);
    if($stmt->execute()){
        $response['status'] = 'Success';
        $response['message'] = 'Profile updated successfully.';
    }else{
        $response['status'] = 'Failed';
        $response['message'] = 'Profile updation failled.';
    }
    echo json_encode($response);
  }// Change Status

    else if($operation == 'manageUser'){
        $primaryKey = 'id'; 
        $table = 'users';
        $columns = array(
            array( 'db' => '`u`.`id`', 'dt' => 0 , 'field' => 'id' ),
            array( 'db' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)', 'dt' => 1, 'field' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)'  ),
            array( 'db' => '`u`.`email_id`', 'dt' => 2 , 'field' => 'email_id'),
            array( 'db' => '`u`.`phone`', 'dt' => 3 , 'field' => 'phone'),
            array( 'db' => '`u`.`user_type`', 'dt' => 4 , 'field' => 'user_type'),
            array( 'db' => '`u`.`registered_on`', 'dt' => 5 ,
                'formatter' => function( $d, $row ) {
                    if($row['registered_on'] == NULL){
                        $regisDate = '';
                    }else{
                        $regisDate =  date( 'd-M-Y d:s A',$row['registered_on']);
                    }
                    return $regisDate;
                },
                'field' => 'registered_on'
             ),
            array( 'db' => '`u`.`status`', 'dt' => 6 , 'field' => 'status', 
                'formatter' => function( $d, $row ) {
                    $id = $row['id'];
                    if($d == 'A'){
                        return '<label class="switch">
                                    <input type="checkbox" onclick="changeStatus('.$row['id'].')" id="customSwitches'.$row['id'].'" checked>
                                    <span class="slider round"></span>
                                </label> Active';
                    }else{
                        return '<label class="switch">
                                    <input type="checkbox" onclick="changeStatus('.$row['id'].')" id="customSwitches'.$row['id'].'">
                                    <span class="slider round"></span>
                                </label> Inactive';
                    }
                } 
            ),
            array(
                'db'        => '`u`.`id`',
                'dt'        =>7,
                'field' => 'id',
                'formatter' => function( $d, $row ) {
                    return '<div class="d-flex">
                            <a href="javascript:void(0)" title="Edit" onclick="editUser('.$row['id'].')"><i class="fa fa-edit text-warning ml-3 mr-3"></i></a>
                            <a href="javascript:void(0)" title="Delete"  onclick="delUser('.$row['id'].')"><i class="fa fa-trash" aria-hidden="true" text-danger mr-3"></i></a>
                        </div>';
                    }
                )
        );
        $joinQuery = "FROM `{$table}` AS `u` ";
        $extraCondition = " `u`.`user_type` != 'admin' ";
        $groupBy = NULL;
        $having = NULL;
        echo json_encode(
            SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
        );
    }//manageUser Operation

  else if($operation == "userChangeStatus"){
    $response = array();
    $id = $_POST['id'];
    $stmt = $pdo_conn->prepare("UPDATE `users` SET status = CASE status WHEN 'A' THEN 'I' ELSE 'A' END WHERE `id` = ?");
    $stmt->bind_param('i',$id);
    if($stmt->execute())
    {
        $response['status'] = 'Success';
    }
    else
     {
        $response['status'] = 'Failed';
     }
    echo json_encode($response);
   }// Change user Status

  else if($operation == 'saveCard'){
    $response = array();
 
    $cardName = $_POST["cardnumber"]; 
    $status = $_POST['status'];
    
    $img_name = '';
    if($cardName == ''){   
      $response['status'] = 'Empty';
      $response['message'] = 'Please enter card name.';
      echo json_encode($response);
      exit();
    }

    if(empty($_FILES['file_att'])){   
      $response['status'] = 'Empty';
      $response['message'] = 'Please choose gift card image.';
      echo json_encode($response);
      exit();
    }
    else 
     {
      $location  = "../uploads/card_img/";
      if($_FILES['file_att']['name'] != '')
      {
       $img_name = time().$_FILES['file_att']['name'];  
      }
      $finalLocation = $location.$img_name;
       if( move_uploaded_file($_FILES['file_att']['tmp_name'], $finalLocation))
        {
           //echo "File uploaded Sucess";
        }
    }

    $stmt = $pdo_conn->prepare("INSERT INTO `cards` (`card_name`,`img_path`,`created_on`,`status`) VALUES (?,?,NOW(),?)");
    $stmt->bind_param('sss',$cardName,$img_name,$status);

     if($stmt->execute()){
         $response['status'] = 'Success';
         $response['message'] ='Card added successfully.';
     }
     else{
         $response['status'] = 'Error';
    }
      echo json_encode($response);
  }// Save User

  else if($operation == 'manageCards')
    {
      $primaryKey = 'id'; 
      $table = 'cards';
      $columns = array(
            array( 'db' => '`c`.`id`', 'dt' => 0 , 'field' => 'id' ),
            array( 'db' => '`c`.`card_name`', 'dt' => 1 , 'field' => 'card_name'),
            //array( 'db' => '`c`.`img_path`', 'dt' => 2 , 'field' => 'img_path'),
            array( 'db' => '`c`.`img_path`', 'dt' => 2 , 'field' => 'img_path',
            'formatter' => function($d, $row){
                    if($row['img_path'] == '')
                    {
                     return '<a href="../images/girf-card.jpg" target="_blank"><img src="../images/girf-card.jpg" width="50" ></a>';
                    }
                    else
                    {
                     return '<a href="../uploads/card_img/'.$row["img_path"].'" target="_blank"><img src="../uploads/card_img/'.$row['img_path'].'" width="50" ></a>';
                    }
                   }
              ),
            array( 'db' => '`c`.`created_on`', 'dt' => 3 ,
               'formatter' => function( $d, $row ) {
                  if($row['created_on'] == NULL)
                     {
                        $created_on_date = '';
                     }
                     else
                     {
                        $created_on_date =  date('d-M-Y h:i A',strtotime($row['created_on']));
                      }
                      return $created_on_date;
                  },
                    'field' => 'created_on'
             ),
            array( 'db' => '`c`.`status`', 'dt' => 4 , 'field' => 'status'),
          array(
            'db'        => '`c`.`id`',
            'dt'        =>5, 
            'field' => 'id',
            'formatter' => function( $d, $row ) {
                return '<div class="d-flex">
                 <a href="javascript:void(0)" title="Edit" onclick="editCard('.$row['id'].')"><i class="fa fa-edit text-warning ml-3 mr-3"></i></a>
                 <a href="javascript:void(0)" title="Delete"  onclick="deleteCard('.$row['id'].')"><i class="fa fa-trash" aria-hidden="true" text-danger mr-3"></i></a>       
                  </div>';
                 }
               )
         );
      //<a href="javascript:void(0)" title="View" onclick="showCard('.$row['id'].')"><i class="fa fa-eye text-primary text-center ml-4"></i></a>
                
    $joinQuery = "FROM `{$table}` AS `c` ";
    $extraCondition = NULL;
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
  }//manage card operation

//redeemCardHistoryAdmin operation
else if($operation == 'redeemCardHistoryAdmin'){
    $primaryKey = 'id'; 
    $table      = 'generated_cards';
    $table2     = 'cards';
    $table3     = 'users';
    $columns = array(
        array( 'db' => '`g`.`id`', 'dt' => 0 , 'field' => 'id' ),
        array( 'db' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)', 'dt' => 1, 'field' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)'  ),
        array( 'db' => '`u`.`email_id`', 'dt' => 2, 'field' => 'email_id'),
        array( 'db' => '`u`.`phone`', 'dt' => 3, 'field' => 'phone'),
        array( 'db' => '`c`.`card_name`', 'dt' => 4, 
            'formatter'=>function($d, $row){
                if($row['card_name']!=""){
                    return $row['card_name'];
                }else{
                    return 'Custom Card';
                }
            },
            'field' => 'card_name'
        ),
        array( 'db' => '`g`.`unique_card_no`', 'dt' => 5, 'field' => 'unique_card_no'),
        array( 'db' => '`g`.`card_amount`', 'dt' => 6,
            'formatter' => function( $d, $row ) {
                return '$'.$row['card_amount'];
            },
            'field' => 'card_amount'
        ),
        array( 'db' => '`g`.`generated_on`', 'dt' => 7,
            'formatter' => function( $d, $row ) {
                if($row['generated_on'] == NULL){
                    $regisDate = '';
                }
                else{
                    $regisDate =  date( 'd-M-Y h:i A',strtotime($row['generated_on']));
                }
                return $regisDate;
            },
            'field' => 'generated_on'
        ),
        array( 'db' => '`g`.`status`','dt' => 8, 'field' => 'status' )
    );
    $joinQuery = "FROM `{$table}` AS `g` LEFT JOIN `{$table2}` AS `c` ON `c`.`id` = `g`.`card_id` LEFT JOIN `{$table3}` AS `u` ON `g`.`redeemer_user_id` = `u`.`id` ";
    $extraCondition = " `g`.`is_reedemed` = 'Yes' "; 
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
}
//redeemCardHistory Operation
 
 
  else if($operation == 'withdrawHistory')
    {
      $primaryKey = 'id'; 
      $table = 'withdrawals';
      $table2 = 'users';
      $columns = array(
            array( 'db' => '`w`.`id`', 'dt' => 0 , 'field' => 'id' ),
            array( 'db' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)', 'dt' => 1, 'field' => 'CONCAT( `u`.`first_name`," ", `u`.`last_name`)'  ),
            array( 'db' => '`w`.`amount`', 'dt' => 2 ,
             'formatter' => function( $d, $row ) {
                  return "$ ".number_format($row['amount'],2);
                  },
              'field' => 'amount'),
            array( 'db' => '`w`.`payment_notes`', 'dt' => 3 , 'field' => 'payment_notes' ),
            array( 'db' => '`w`.`requested_on`', 'dt' => 4,
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
            array( 'db' => '`w`.`status`',     'dt' => 5, 'field' => 'status',
             'formatter' => function( $d, $row ) {
                $id = $row['id'];
                $selectHtml = '';
                if($d=='Pending'){
                  $selectHtml .= '<select class="form-control actives form-control-sm selectInTable " onchange="changestatuspro(this,'.$row['id'].',\''.$row['user_id'].'\',\''.$row['amount'].'\')">
                  <option class="" value="Pending" '. (($d=='Pending')?'selected':'').'>Pending</option>
                  <option class="" value="Completed" '. (($d=='Completed')?'selected':'').'>Completed</option>
                  <option class="" value="Rejected" '. (($d=='Rejected')?'selected':'').'>Rejected</option>
                  </select>';
                }else{
                  $selectHtml = '<p class="mb-0 text-center">'.$d.'</p>';
                }
                   return $selectHtml;
               }
             ),
             array( 'db' => '`w`.`admin_notes`', 'dt' => 6 , 'field' => 'admin_notes' ),
             array( 'db' => '`w`.`user_id`', 'dt' => 7, 'field' => 'user_id' )
         );
    $joinQuery = "FROM `{$table}` AS `w` LEFT JOIN `{$table2}` AS `u` ON `u`.`id` = `w`.`user_id` ";
    $extraCondition = NULL ;
    $groupBy = NULL;
    $having = NULL;
    echo json_encode(
        SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition,$groupBy,$having)
    );
  }//withdrawHistory Operation

  else if($operation == "update_status")
  {
    $response    = array();
    
    $withdrawID  = $_POST['withdrawID'];
    $statusName  = $_POST['statusName'];
    $userId      = $_POST['userId'];
    $amount      = $_POST['amount'];
    $admin_notes = $_POST['admin_notes'];

    $stmt = $pdo_conn->prepare("UPDATE `withdrawals` SET `status` = ? , `completed_on` = CURRENT_TIMESTAMP(), `admin_notes` = ? WHERE `id` = ? ");
    $stmt->bind_param('ssi',$statusName,$admin_notes,$withdrawID);
    if($stmt->execute())
    {
      if($statusName != 'Completed')
      {
        $stmt = $pdo_conn->prepare("SELECT balance from `users` WHERE `id` = ? ");
        $stmt->bind_param("i",$userId);
        $stmt->execute();
        $result = get_result($stmt);
        if(count($result)>0)
        {
          while($row = array_shift( $result))
          {
            $balance = $row["balance"];
          } 
        }//balance if
        $new_balance = $balance+$amount;
        $stmt = $pdo_conn->prepare("UPDATE `users` SET `balance` = $new_balance WHERE `id` = ? ");
        $stmt->bind_param('i',$userId);
        if($stmt->execute())
        {  
          $response['status'] = 'Success';
          $response['message'] = 'Withdraw requested status changed successfully.';
        }
      }//if completes
      else
      {
        $response['status'] = 'Success';
        $response['message'] = 'Withdraw requested status changed successfully.';
      }
      
    }
    else
    {
      $response['status']  = 'Failed';
      $response['message'] = 'Status could not be changed. Please try againafter sometime.';
    }
    echo json_encode($response);
  }// Change Status Operation

  else if($operation == 'edit-Card')
  {
    $data = array();
    $id = $_POST['id'];

    $stmt = $pdo_conn->prepare("SELECT * FROM `cards` WHERE id = ?");
    $stmt->bind_param("i",$id);  //Binding parameters into statement
    $stmt->execute();               //Execute query
    $result = get_result($stmt);  //Fetch results
      if(count($result) > 0){ //if user exists
      while($row = array_shift($result))
       {  
            $data = $row;   //set whole record data into variable
       }
     }
    echo json_encode($data);
   }// Edit Card

   else if($operation == "updateCard"){

    $response = array();
    
    $id = $_POST['id'];
    $cardName = $_POST["cardName"]; 
    $status = $_POST['status'];
    $oldImageName = $_POST['oldImageName'];
    $img_name = '';
    $location  = "../uploads/card_img/";
    if(!empty($_FILES['file_att']))
    {
      {
        $img_name = time().$_FILES['file_att']['name'];
      }
      $finalLocation = $location.time().$_FILES['file_att']['name'];
       if( move_uploaded_file($_FILES['file_att']['tmp_name'], $finalLocation))
        {
          $fullPath = $location.$oldImageName;
           unlink($fullPath);
        }
      }

    if( $img_name == '' )
    {
       $stmt = $pdo_conn->prepare("UPDATE `cards` SET `card_name` = ?, `status` = ?, `created_on` = now() WHERE `id` = ? ");
       $stmt->bind_param('ssi',$cardName,$status,$id);
    }
    else{
      $stmt = $pdo_conn->prepare("UPDATE `cards` SET `card_name` = ?, `status` = ?,`img_path` = ?,  `created_on` = now() WHERE `id` = ?  ");
       $stmt->bind_param('sssi',$cardName,$status,$img_name,$id);
    }
    if($stmt->execute()){
         $response['status'] = 'Success';
         $response['message'] = 'Card updated successfully.';
     }
    else
     {
      $response['status'] = 'Failed';
      $response['message'] = 'Card updation successfully.';
      }
    echo json_encode($response);
   }// Update Card
   else if($operation == "delete-Card")
 {
   $response = array();
    $id = $_POST['id'];
    $oldImage = '';
    $stmt = $pdo_conn->prepare("SELECT * FROM `cards` WHERE id = ? " );
    $stmt->bind_param("i",$id); 
    $stmt->execute();            
    $result = get_result($stmt); 
      if(count($result) > 0){ 
      while($row = array_shift($result))
       {  
          $oldImage = $row['img_path'];
       }
     }

    $stmt = $pdo_conn->prepare("DELETE FROM `cards` WHERE `id` = ? ");
    $stmt->bind_param('i',$id);
    if($stmt->execute()){
      $response['status'] = 'Success';
      $location  = "../uploads/card_img/";
        if($oldImage != '')
        {
         $fullPath = $location.$oldImage;
         unlink($fullPath);
        }
    }
    else
    {
      $response['status'] = 'Failed';
    }
    echo json_encode($response);
} //Auth

else if($operation == 'edit-User'){
    $data = array();
    $id = $_POST['id'];

    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE id = ?");
    $stmt->bind_param("i",$id);  //Binding parameters into statement
    $stmt->execute();               //Execute query
    $result = get_result($stmt);  //Fetch results
      if(count($result) > 0){ //if user exists
      while($row = array_shift($result))
       {  
            $data = $row;   //set whole record data into variable
       }
     }
    echo json_encode($data);
   }// Edit Card

   else if($operation == "updateUser"){

    $response = array();
    
    $id              = $_POST['id'];
    $first_name      = $_POST["fistName"]; 
    $last_name       = $_POST['lastName'];
    $email           = $_POST['email'];
    $phone           = $_POST['phone'];
    $status          = $_POST['status'];
    
    $stmt = $pdo_conn->prepare("UPDATE `users` SET `first_name` = ?,`last_name` = ?,`email_id` = ?, `phone` = ?, `status` = ? WHERE `id` = ? ");
    $stmt->bind_param('sssssi',$first_name,$last_name,$email,$phone,$status,$id);
    
    if($stmt->execute()){
         $response['status'] = 'Success';
         $response['message'] = 'User details updated successfully.';
     } 
    else
     {
      $response['status'] = 'Failed';
      $response['message'] = 'User details updation unsuccessfully.';
      }
    echo json_encode($response);
}// Edit


else if($operation == "delete-User"){
   $response = array();
    $id = $_POST['id'];

    $stmt = $pdo_conn->prepare("DELETE FROM `users` WHERE `id` = ? ");
    $stmt->bind_param('i',$id);
    if($stmt->execute()){
      $response['status'] = 'Success';
      $response['message'] = 'User deleted successfully.';
    }
    else
    {
      $response['status'] = 'Failed';
      $response['message'] = 'User deletion unsuccessfully.';
    } 
    echo json_encode($response);
} //Delete User

else if($operation == "updateLogo"){
    $response = array();
    $oldImage = "";
    $location = "../uploads/users_logo/";
    
    $stmt = $pdo_conn->prepare("SELECT `logo` FROM `users` WHERE `id` = ? ");
    $stmt->bind_param("i",$id);//Binding paramenter
    $stmt->execute(); //Execute query          
    $result = get_result($stmt); //fetching result  
    if(count($result) > 0)//If result if exits
    {
        while($row = array_shift($result))//
        {  
            $oldImage = $row['logo'];//Row data shift in data array
        }
    }
    
    if( $_FILES['file_att']['name'] != '' ){
        $img_name = time().$_FILES['file_att']['name'];
        $img_name = preg_replace('/\s+/', '_', $img_name);
        $finalLocation = $location.$img_name;
        if(move_uploaded_file($_FILES['file_att']['tmp_name'],$finalLocation)){
            $fullPath = $location.$oldImage;
            if($oldImage!=""){
                unlink($fullPath);//Delete file
            }
            $stmt = $pdo_conn->prepare("UPDATE `users` SET  `logo` = ? WHERE `id` = ?  ");
            $stmt->bind_param("si",$img_name,$id);//binding parameter
            if($stmt->execute()){
                $response['status']  = "Success";
                $response['img_name']= $img_name;
                //Change image set on session logo
                if($type=="admin"){
                    $_SESSION['logo']               = $img_name;  
                }
                if($type=="contributor"){
                    $_SESSION['logo'] = $img_name;  
                }
                if($type=="redeemer"){
                    $_SESSION['logo']     = $img_name;  
                }
            }
        }
    }
    echo json_encode($response);
}

//Password Change Operation
else if($operation == "changePass"){
    $response = array();
    $old_password = !empty($_POST['oldpassword'])?$_POST['oldpassword']:"";
    $new_password = !empty($_POST['newpassword'])?$_POST['newpassword']:"";

    $stmt = $pdo_conn->prepare("SELECT `password` FROM `users` WHERE `id` = ? ");
    $stmt->bind_param("i",$id);//Binding paramenter
    $stmt->execute(); //Execute query          
    $result = get_result($stmt); //fetching result  
    if(count($result) > 0){//If result if exits
        while($row = array_shift($result)){//
            $db_old_password = $row['password'];//Row data shift in data array
        }
    }
    
    if (password_verify($old_password, $db_old_password)) {
        $encypt_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo_conn->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ? ");
        $stmt->bind_param('si',$encypt_password,$id);
        if($stmt->execute()){
             $response['status'] = 'Success';
             $response['message'] = 'Password changed successfully.';
        }else{
            $response['status'] = 'Failed';
            $response['message'] = 'Password change unsuccessfully.';
        }
    }else{
        $response['status'] = 'Failed';
        $response['message'] = 'Old password is not correct.';
    }
    echo json_encode($response);
}

//Email subscription
else if($operation == "emailsubscritpion"){
    
    $response   = array();
    $email_code = !empty($_POST['emailCode'])?$_POST['emailCode']:"";
    $user_id    = "";
    
    if($email_code!=""){
        
        $stmt = $pdo_conn->prepare("SELECT `id`,`email_subscritpion` FROM `users` WHERE `email_sub_code` = ? ");
        $stmt->bind_param("s",$email_code);//Binding paramenter
        $stmt->execute(); //Execute query          
        $result = get_result($stmt); //fetching result  
        if(count($result) > 0){
            while($row = array_shift($result)){ 
                $user_id = $row['id'];
                $email_sub_status = $row['email_subscritpion'];
            }
        }
        if($user_id!=""){
            $email_subscritpion = "N";
            $stmt = $pdo_conn->prepare("UPDATE `users` SET `email_subscritpion` = ? WHERE `id` = ? ");
            $stmt->bind_param('si',$email_subscritpion,$user_id );
            if($email_sub_status=="Y"){
                if($stmt->execute()){
                    $response['status'] = 'Y';
                }
            }else{
                $response['status'] = 'Y';
            }
        }
    }
    echo json_encode($response);
}

//Email subscription
else if($operation == "emailsubscritpioncheck"){
    
    $response   = array();
    $email_code = !empty($_POST['emailCode'])?$_POST['emailCode']:"";
    $user_id    = "";
    
    if($email_code!=""){
        $email_sub_status = "";
        $stmt = $pdo_conn->prepare("SELECT `email_subscritpion` FROM `users` WHERE `email_sub_code` = ? ");
        $stmt->bind_param("s",$email_code);//Binding paramenter
        $stmt->execute(); //Execute query          
        $result = get_result($stmt); //fetching result  
        if(count($result) > 0){
            while($row = array_shift($result)){ 
                $email_sub_status = $row['email_subscritpion'];
            }
        }
        $response['status'] = $email_sub_status;
    }
    echo json_encode($response);
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
          if($mail->send())
          {
              return true;
          }
          else{
              return false;
          }
      }
      catch(Exception $e)
      {   
          //echo $e->getMessage(); //Boring error messages from anything else!
          $data['status'] = 'failed';
          $data['message'] = '<span class="text-danger mt-3" style="font-size: .8125rem;">*Message could not be sent.</span>';
          return true; 
      }
}
//Mail function
?>