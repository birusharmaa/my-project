<?php
// Compulsory Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$bodyReceived = file_get_contents('php://input');
$event = json_decode($bodyReceived);
$myfile = file_put_contents('logs.txt', $bodyReceived.PHP_EOL , FILE_APPEND | LOCK_EX);
$subscription_id = 0;
$event_type = '';
if(!empty(@$event->event_type)){
    $event_type = $event->event_type;
}

if(@$event->resource_type == "subscription"){
    $subscription_id = $event->resource->id;
}

if(($event_type == "BILLING.SUBSCRIPTION.CREATED") || ($event_type == "BILLING.SUBSCRIPTION.RE-ACTIVATED") || ($event_type == "BILLING.SUBSCRIPTION.UPDATED")){ 
    // Subscription Created
    // Include config
    include_once '../server/config.php';
    include_once 'functions.php';
    
    // Get Token
    $token = getToken();
    // Add headers
    $headers[] = "Authorization: Bearer ".$token."";
    $subscription = simple_curl("/v1/billing/subscriptions/".$subscription_id, 'GET', null , $headers);
    $user_id = '';
    if(!empty($subscription->id) && ($subscription->id == $subscription_id) && ($subscription->status == "ACTIVE")){
            $stmt = $pdo_conn->prepare("SELECT * FROM `users_plan` WHERE `subscription_id` = ? ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("s",$subscription_id);
            $stmt->execute();        
            $result = get_result($stmt);
            if(count($result)>0){ 
                while($row = array_shift( $result)) { 
                    $user_id = $row['user_id'];
                }
            }
    }
    
    $plan_id = 1;
    $expiry_date = $subscription->billing_info->next_billing_time;
    $expiry_date = date('Y-m-d H:i:s',strtotime($expiry_date));
    $subscription_id = $subscription->id;

    //Save payment details in payment_history table
    //$create_time    = $subscription->start_time;
    $create_time    = date('Y-m-d h:i:s');
    //$update_time    = $subscription->update_time;
    $update_time    = date('Y-m-d h:i:s');
    $amount = 25;
    $type = "Subscription";
    $payment_stmt = $pdo_conn->prepare("INSERT INTO `payments_history`( `type`, `amount`, `transaction_id`, `create_time`, `update_time` ) VALUES (?, ?, ?, ?, ?) ");        
    $payment_stmt->bind_param('sisss', $type, $amount, $subscription_id, $create_time, $update_time );
    $payment_stmt->execute();
    
    //Save subscription id and exipry date in user_plan table
    $plan_stmt = $pdo_conn->prepare("INSERT INTO users_plan(user_id,plan_id,expiry_date,subscription_id) VALUES (?,?,?,?)");        
    $plan_stmt->bind_param('iiss',$user_id,$plan_id,$expiry_date,$subscription_id);
    if($plan_stmt->execute()){
        echo $event_type." for user: ".$user_id;
    }
}

else if(($event_type == "BILLING.SUBSCRIPTION.CANCELLED") || ($event_type == "BILLING.SUBSCRIPTION.SUSPENDED") || ($event_type == "BILLING.SUBSCRIPTION.EXPIRED") || ($event_type == "BILLING.SUBSCRIPTION.PAYMENT.FAILED")){ 
    // Subscription Cancelled
    // Subscription Created
    // Include config
    include_once '../server/config.php';
    include_once 'functions.php';
    
    // Get Token
    $token = getToken();
    // Add headers
    $headers[] = "Authorization: Bearer ".$token."";
    $subscription = simple_curl("/v1/billing/subscriptions/".$subscription_id, 'GET', null , $headers);
    $user_id = '';
    if(!empty($subscription->id) && ($subscription->id == $subscription_id)){
            $stmt = $pdo_conn->prepare("SELECT * FROM `users_plan` WHERE `subscription_id` = ? ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("s",$subscription_id);
            $stmt->execute();        
            $result = get_result($stmt);
            if(count($result)>0){ 
                while($row = array_shift( $result)) { 
                    $user_id = $row['user_id'];
                }
            }
    }
    
    $plan_id = 1;
    $expiry_date = "1970-12-12";
    $expiry_date = date('Y-m-d H:i:s',strtotime($expiry_date));
    $subscription_id = $subscription->id;
    
    $stmt = $pdo_conn->prepare("INSERT INTO users_plan(user_id,plan_id,expiry_date,subscription_id) VALUES (?,?,?,?)");        
    $stmt->bind_param('iiss',$user_id,$plan_id,$expiry_date,$subscription_id);
    if($stmt->execute()){
        echo $event_type." for user: ".$user_id;
    }
}

else if( ($event_type == "PAYMENT.CAPTURE.COMPLETED") || ($event_type == "PAYMENT.CAPTURE.PENDING" ) ){
    include_once '../server/config.php';
    include_once 'functions.php';
    
    //Get transaction id, amount and order id
    $transaction_id = $event->resource->id;
    $amount         = $event->resource->amount->value;
    $order_id       = $event->resource->supplementary_data->related_ids->order_id;
    
    //Payment type
    $type = "Order";

    //Created and updated time
    $create_time    = $event->resource->create_time;
    $create_time    = date('Y-m-d h:i:s', strtotime($create_time));
    $update_time    = $event->resource->update_time;
    $update_time    = date('Y-m-d h:i:s', strtotime($update_time));
    
    //Save detatils in payment history table
    $stmt = $pdo_conn->prepare("INSERT INTO `payments_history` (`type`, `amount`, `transaction_id`, `order_id`, `create_time`, `update_time` ) VALUES (?, ?, ?, ?, ?, ?) ");        
    $stmt->bind_param('ssssss',$type, $amount, $transaction_id, $order_id, $create_time, $update_time);
    if($stmt->execute()){
        echo "1";
    }
}

else if( ($event_type == "PAYMENT.CAPTURE.REFUNDED") || ($event_type == "PAYMENT.CAPTURE.DENIED") ){
    include_once '../server/config.php';
    include_once 'functions.php';

    //Get transaction id
    $transaction_id = $event->resource->id;
    $order_id       = $event->resource->supplementary_data->related_ids->order_id;
    //Updated time
    $update_time    = $event->resource->update_time;
    $update_time    = date('Y-m-d h:i:s', strtotime($update_time));
    $status         = "Refunded";

    $stmt = $pdo_conn->prepare("UPDATE `payments_history` SET `status` = ? WHERE `order_id` = ? ");
    $stmt->bind_param('ss',$status, $order_id);
    if($stmt->execute()){
        $stmt = $pdo_conn->prepare("DELETE FROM `generated_cards` WHERE `payment_order_id` = ? ");
        $stmt->bind_param('s',$order_id);
        $stmt->execute();
    }
}

echo 1;

?>
