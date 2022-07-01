<?php
session_start();
//date_default_timezone_set('');
if(isset($_POST["operation"]))
{
  	$operation = $_POST["operation"];
	  require("../config.php");
    require("../ssp.class.php"); 
}
else
{	//return to request page if operation is not set
  	exit();
}

// get users
if($operation == 'get_users')
{
    $primaryKey = 'id';
    $table      = 'users';
    $columns    = array(
        array( 'db' => '`u`.`id`', 'dt' => 0, 'field' => 'id'),
        array( 'db' => '`u`.`name`', 'dt' => 1, 'field' => 'name'),
        array( 'db' => '`u`.`email`', 'dt' => 2 , 'field' => 'email'),
        array( 'db' => '`u`.`phone`', 'dt' => 3 , 'field' => 'phone'),
        array( 'db' => '`u`.`registered_on`', 'dt' => 4,
            'formatter' => function($d,$row)
            {
             //return date('d-M-Y', strtotime($row['registered_on']));
              return gmdate("Y-m-d", $row['registered_on']);
            }, 
            'field' => 'registered_on'),
        array( 'db' => '`p`.`name` `plan` ', 'dt' => 5 ,
          'formatter' => function($d,$row)
          {
            return $row['plan'].'<a href="javascript:void(0)" title="Edit Plan" onclick="editPlan('.$row['id'].')"><i class="fa fa-edit text-warning ml-2"></i></a>
                    ';

          },
          'field' => 'plan'),
        array( 'db' => '`u`.`status`', 'dt' => 6 , 'field' => 'status', 'formatter' => 
             function($d, $row){
              $id = $row['id'];
              if($d == 'Active')
                  {
                      return '<div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" onclick="changeStatus('.$row['id'].')" id="customSwitches'.$id.'" checked=checked>
                                  <label class="custom-control-label" for="customSwitches'.$id.'">Active</label>
                              </div>';
                  }
              else
              {
                  return '<div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input" onclick="changeStatus('.$row['id'].')" id="customSwitches'.$id.'">
                      <label class="custom-control-label" for="customSwitches'.$id.'">Inactive</label>
                  </div>';
              }
        }),
        array(
            'db'        => '`u`.`id`',
            'dt'        => 7,
            'formatter' => function( $d, $row ) {
            return '<a href="javascript:void(0)" title="Edit" onclick="editUser('.$d.')"><i class="fa fa-edit text-warning mr-3"></i></a>
                    <a href="javascript:void(0)" title="Change Password" onclick="changePass('.$d.')"><i class="fas fa-key mr-3"></i></a>
                    <a href="javascript:void(0)" title="Settings" onclick="settingUser('.$d.')"><i class="fa fa-cog text-info"></i></a>';
            },
          'field' => 'id' )
         );
      $joinQuery = "FROM `{$table}` AS `u` LEFT JOIN `user_plan` AS `up` ON `up`.`user_id`=`u`.`id` LEFT JOIN `plans` AS `p` ON `p`.`id`=`up`.`plan_id`  ";
      $extraCondition = NULL;
      $groupBy = NULL;
      $having = NULL;
      echo json_encode(
          SSP::simple( $_REQUEST, $sql_dt_conn, $table, $primaryKey, $columns, $joinQuery, $extraCondition, $groupBy, $having)
      );
}
// Get Users End userChangeSt

//Change status operation start
else if($operation == "changeStatus"){
    $response = array();
    $id       = $_POST['id'];
    $stmt = $pdo_conn->prepare("UPDATE `users` SET status = CASE status WHEN 'Active' THEN 'Inactive' ELSE 'Active' END WHERE `id` = ?");
    $stmt->bind_param('i',$id);
    if($stmt->execute())
    {
        $response['success'] = true;
        $response['message'] = "User status changed successfully.";
    }
    else
    {
        $response['success'] = false;
        $response['message'] = "User status change unsuccessfully.";
    }
    echo json_encode($response);
}
//Change status operation end

// else if($operation == 'edit-User')
// {
//     $data = array();
//     $id = $_POST['id'];
//     $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE id = ?");
//     $stmt->bind_param("i",$id);  //Binding parameters into statement
//     $stmt->execute();               //Execute query
//     $result = get_result($stmt);  //Fetch results
//     if(count($result) > 0)
//     { //if user exists
//         while($row = array_shift($result))
//         {  
//             $data = $row;   //set whole record data into variable
//         }
//     }
//     echo json_encode($data);
// }// Edit User

//update user details
else if($operation == "update_users"){

    $response = array();
    $validation = true;
    $id      = $_POST['id'];
    $name    = $_POST["name"]; 
    $email   = $_POST['email'];
    $phone   = $_POST["phone"];
    $website = $_POST["website"];
    $address = $_POST['address'];
    $organization = $_POST['organization'];

    $stmt = $pdo_conn->prepare("SELECT * FROM `users` WHERE id != ?");
    $stmt->bind_param("i",$id);  //Binding parameters into statement
    $stmt->execute();               //Execute query
    $result = get_result($stmt);  //Fetch results
    if(count($result)>0)
    { 
        while($row = array_shift($result))
        {  
            $db_email = $row['email'];
            $db_phone = $row['phone'];

            if($email == $db_email)// Checking old email new email
            {
                $validation = false;
                $response['message'] = 'Email associated with other user.';
            }
            else
            {
                if($phone == $db_phone) // checking old phone and new phone number
                {
                    $validation = false;
                    $response['message'] = 'Phone associated with other user.';
                }
            }// if else part
        }// while loop
    }// if count result
    
    if($validation)// Validation check condition
    {
        $stmt = $pdo_conn->prepare("UPDATE `users` SET `name` = ?, `email` = ?, `phone` = ?,`website` = ?, `address` = ?, `organization` = ? WHERE `id` = ? ");
        $stmt->bind_param('ssssssi',$name,$email,$phone,$website,$address,$organization,$id);    
        if($stmt->execute()){
            $response['success'] = true;
            $response['message'] = 'User details has been updated.';
         }
        else
        {
            $response['success'] = false;
            $response['message'] = 'User details updation unsuccessfully.';
        }
    }// Validation check condition if stmt
    echo json_encode($response);
}
// Update User details operation end
  
//user password change operation
else if($operation == "user_password_change")
{
    $password = $_POST['password'];
    $userid   = $_POST['id'];
    $response = array(); 
   
    $encypt_password = password_hash($password, PASSWORD_DEFAULT);
   
    $stmt = $pdo_conn->prepare("UPDATE `users` SET  `password` = ? WHERE `id` = ? ");
    $stmt->bind_param("si",$encypt_password,$userid);
    if($stmt->execute())
    {
        $response['success'] = true;
        $response['message'] = "Password changed successfully.";
    }
    else
    {
        $response['success'] = false;
        $response['message'] = "Password change unsuccessfully."; 
    }
    echo json_encode($response);
}
// User Password Change end

//Edit user plan
else if($operation == "editPlan")
{
    $data = array();
    $id = $_POST['id'];
    
    $stmt = $pdo_conn->prepare("SELECT `u`.*,`p`.`name` AS `planName`,`p`.`id` AS `planId`,`up`.`subscription_type` FROM
     `users` AS `u` LEFT JOIN `user_plan`
      AS `up` ON `up`.`user_id` = `u`.`id` 
      LEFT JOIN `plans` AS `p` ON `p`.`id` = `up`.`plan_id` WHERE `u`.`id` = ? ");
    $stmt->bind_param("i",$id);
    $stmt->execute();          
    $result = get_result($stmt); 
      if(count($result) > 0)
      {
        while($row = array_shift($result))
        {  
            $data = $row;
        }
    }
    echo json_encode($data);
}
//Edit user plan

//Update user plan
else if($operation == "userPlanChange")
{
    $data    = array();
    $userId  = $_POST['userId'];
    $planId  = $_POST['planId'];
    $planType= $_POST['planType'];
    
    $lastInsetId = "";
    //get last inserted data where user_id = ?
    $stmt = $pdo_conn->prepare("SELECT `id` FROM `user_plan` WHERE `user_id` = ? ORDER BY `id` DESC LIMIT 1 ");
    $stmt->bind_param("i",$userId);
    $stmt->execute();    
    $result = get_result($stmt); 
    
    if(count($result)>0)// count row
    {
        while($row = array_shift($result))
        {  
            $lastInsetId = $row['id']; //get last insert id where user_id = ?
        }
    }

    if($lastInsetId)//if id is exits
    {
        $status = "Upgraded by Admin";

        $stmt = $pdo_conn->prepare("UPDATE `user_plan` SET  `expired_on` = UNIX_TIMESTAMP() ,`subscription_type` = ?, `status` = ? WHERE `id` = ? ");
        $stmt->bind_param("ssi",$planType,$status,$lastInsetId);
        if($stmt->execute())
        {
            //$data['success'] = true;
            $status = "Subscribed";
            $stmt = $pdo_conn->prepare("INSERT INTO `user_plan`(`user_id`,`plan_id`,`subscribed_on`,`status`) VALUES (?,?,UNIX_TIMESTAMP(),?)");
            $stmt->bind_param("iis",$userId,$planId,$status);
            if($stmt->execute())
            {
                $data['success'] = true;
                $data['message'] = "User plan has been changed.";
            }
            else
            {
                $data['success'] = false;
                $data['message'] = "User plan has not been change.";
            }

        }
        else
        {
            $data['success'] = false;
            $data['message'] = "User plan has not been change.";
        }
    }
    
    echo json_encode($data);
}
//Update user plan







?>
