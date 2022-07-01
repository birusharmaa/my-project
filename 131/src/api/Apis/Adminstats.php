<?php
session_start();

class Api_adminstats extends Api
{
    public function __construct()
    {
        // In here you could initialize some shared logic between this API and rest of the project

    }

    /**
     * Get individual record or records list
     */
    public function get($url =''){
        //Count all users
        $user_type = "Admin";
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT  COUNT(*) AS 'id' FROM `users` WHERE `user_type` != ? ");
        $stmt->bind_param("s",$user_type); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
         if(count($result)){
            while($row = array_shift($result)){ 
                $data['num_total_user'] = $row;
            }
        }//end here
      
        $plan_id=1;     
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT COUNT(*) AS 'free_plan' FROM `user_plan` WHERE `plan_id`=? AND `expired_on` is NULL");
        $stmt->bind_param("i",$plan_id); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)){
            while($row = array_shift($result)){ 
                $data['num_free_user'] = $row;
            } 
        }

        $plan_id=2;
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT  COUNT(*) AS 'basic_plan' FROM `user_plan` WHERE `plan_id`=? AND `expired_on` is NULL");
        $stmt->bind_param("i",$plan_id); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)){
            while($row = array_shift($result)){ 
                $data['num_basic_user'] = $row;
            }
        }

        $plan_id=3;     
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT  COUNT(*) AS 'premium_plan' FROM `user_plan` WHERE `plan_id`=? AND `expired_on` IS NULL");
        $stmt->bind_param("i",$plan_id); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)){
            while($row = array_shift($result)){ 
                $data['num_premium_user'] = $row;
            }   
        }

        $date = new DateTime();
        $current_date = $date->getTimestamp();
        $first_day = date('01-m-Y');
        $first_day = strtotime($first_day); 
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT COUNT(*) AS 'new_user'  FROM `users` WHERE `registered_on` BETWEEN ".$first_day." AND ".$current_date." ");
        //$stmt->bind_param("i",$plan_id); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)>0){
            while($row = array_shift($result)){ 
                $data['new_user_this_month'] = $row;
            }
        }
        
        $plan_id=1;       
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT  COUNT(id) AS 'conversion_rate' FROM `user_plan` WHERE `plan_id`=? AND `expired_on` IS NOT NULL");
        $stmt->bind_param("i",$plan_id); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)){
            while($row = array_shift($result)){ 
                $data['free_to_paid'] = $row;
            }   
        }

        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT SUM(amount_paid) AS 'total_amount'  FROM `user_payments`");
        //$stmt->bind_param("i",$plan_id); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)>0){
            while($row = array_shift($result)){ 
                //$msgid=$row['id'];
                $data['nett_sales'] = $row;
            }
        }

        $first_day = date('01-m-Y');
        $first_day = strtotime($first_day);
        $pdo_conn = API::dbConnection();//DB Connection
        $stmt = $pdo_conn->prepare("SELECT SUM(amount_paid) AS 'new_amount'  FROM `user_payments` WHERE `paid_on`>?");
        $stmt->bind_param("i",$first_day); //Bind parameter
        $stmt->execute();  //Execute query
        $result = API::get_result($stmt);  //Fetch results     
        if(count($result)>0){
            while($row = array_shift($result)){ 
                $data['new_sales'] = $row;
            }  
        }

        $response = array('status'=>'success','message'=>'Get data  successfully.');
        return Api::responseOk($data);   
    }

      
    /**
     * Update record
     */
    public function put($id = null)
    {
       
    }
  
    /**
     * Post function
     */ 
    public function post($operation = null)
    {

      
    }  


    /**
     * Delete record
     */   
    public function delete( $id = null )
    {
        // In real world there would be call to model with validation and probably token checking

    }
}

?>


 