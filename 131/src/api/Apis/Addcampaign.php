<?php
session_start();

class Api_Addcampaign extends Api
{  
     
    public function __construct()
    {
        // In here you could initialize some shared logic between this API and rest of the project
    }

    /**
     * Get individual record or records list
     */
    public function get($id = null){        
        if($id==""){//If id is empty
            $primaryKey = 'id';
            $table      = 'user_customers';
            $columns    = array(
                array( 'db' => '`uc`.`id`', 'dt' => 0, 'field' => 'id'),
                array( 'db' => '`uc`.`id`', 'dt' => 1,
                    'formatter' => function($d,$row){
                        $checked = "";
                        $select_ids = isset($_GET['selected_ids'])? $_GET['selected_ids']:array();
                        // if($_GET['allSelected']=="Yes"){
                        //     $checked = "checked";
                        // }else{
                            if(isset($_SESSION['selected_ids'])){
                                if(($key = array_search($d, $_SESSION['selected_ids'])) !== false) {
                                    $checked = "checked";
                                }
                            }
                        // }                        
                        return '<input type="checkbox" '.$checked.' data-id="'.$d.'" class="selectCheckBox" />';
                    },
                    'field' => 'id'),
                array( 'db' => '`uc`.`name`', 'dt' => 2, 'field' => 'name'),
                array( 'db' => '`uc`.`email`', 'dt' => 3, 'field' => 'email'),
                array( 'db' => '`uc`.`phone`', 'dt' => 4, 'field' => 'phone'),
                array( 'db' => '`uc`.`added_on`', 'dt' => 5,
                    'formatter' => function($d,$row){
                        return gmdate("m/d/y", $row['added_on']);
                    }, 
                    'field' => 'added_on'),
            );
            $joinQuery = "FROM `{$table}` AS `uc` LEFT JOIN `customer_msgs` AS `cm` ON `cm`.`customer_id` =`uc`.`id` ";
            $extraCondition = " `uc`.`user_id` = ".$_SESSION['userid']." AND `uc`.`location_id` = ".$_SESSION['location_id']." AND `cm`.`customer_id` IS NULL ";
            $groupBy = NULL;
            $having = NULL;
            $response = Api::getDbData($_REQUEST, $table, $primaryKey, $columns, $joinQuery, $extraCondition, $groupBy, $having);
            return Api::responseOk($response);//Return data
        }//IF id is empty
        else{
            $user_id = $_SESSION['userid'];
            $data = array();
            $pdo_conn = Api::dbConnection();//mysqli connection
            $stmt = $pdo_conn->prepare("SELECT * FROM
             `user_locations` WHERE user_id = ? ");
            $stmt->bind_param("i",$user_id);//Binding paramenter
            $stmt->execute(); //Execute query          
            $result = Api::get_result($stmt); //fetching result  
            if(count($result) > 0)//If result if exits
            {
                while($row = array_shift($result))//
                {  
                    $data = $row;//Row data shift in data array
                }
            }
            return Api::responseOk($data);//Return data

        }//else

    } 

    /**
     * Update record
     */
    public function put($record_id = null){
        // In real world there would be call to model with validation and probably token checking
        $data_arr = Api::$input_data; //to update
        //update single user
    }

    /**
     * Create record
     */
    public function post($id = null){   
        $data_arr = Api::$input_data; //to update
        $operation  = isset($data_arr['operation'])?$data_arr['operation']:"";
        $page_start = isset($data_arr['pageStart'])?$data_arr['pageStart']:"";
        $page_end   = isset($data_arr['pageEnd'])?10:10;
        
        if($operation !=""){
            if(!isset($_SESSION['selected_ids'])){
                $_SESSION['selected_ids'] = [];
            }

            //New client single select
            if($operation=="select"){
                if(isset($_SESSION['selected_ids'])){
                    $id = $data_arr['id'];
                    array_push($_SESSION['selected_ids'],$id);
                }
            }

            //New client singele un_select
            else if($operation == "un_select"){
                if(isset($_SESSION['selected_ids'])){
                    $id = $data_arr['id'];
                    if(($key = array_search($id, $_SESSION['selected_ids'])) !== false) {
                        unset($_SESSION['selected_ids'][$key]);
                        $_SESSION['selected_ids'] = array_values($_SESSION['selected_ids']);
                    }
                }
            }

            //Old client single row select
            else if($operation=="old_select"){
                if(isset($_SESSION['selected_ids'])){
                    $id = $data_arr['id'];
                    array_push($_SESSION['selected_ids'],$id);
                }
            }

            //Old client single row unselect
            else if($operation == "old_un_select"){
                if(isset($_SESSION['selected_ids'])){
                    $id = $data_arr['id'];
                    if(($key = array_search($id, $_SESSION['selected_ids'])) !== false) {
                        unset($_SESSION['selected_ids'][$key]);
                        $_SESSION['selected_ids'] = array_values($_SESSION['selected_ids']);
                    }
                }
            }

            //Select all new client
            else if($operation == "select_all"){

                $data = array();
                $pdo_conn = Api::dbConnection();//mysqli connection
                $stmt = $pdo_conn->prepare("SELECT `uc`.* FROM `user_customers` AS `uc` 
                    LEFT JOIN `customer_msgs` AS `cm` ON `cm`.`customer_id` =`uc`.`id` 
                    WHERE `uc`.`user_id` = ? AND `uc`.`location_id` = ? AND `cm`.`customer_id` IS NULL ORDER BY `uc`.`id` DESC
                    LIMIT ".$page_start.", ".$page_end." 

                    ");
                $stmt->bind_param("ii",$_SESSION['userid'],$_SESSION['location_id']);//Binding paramenter
                $stmt->execute(); //Execute query          
                $result = Api::get_result($stmt); //fetching result  
                if(count($result) > 0){
                    while($row = array_shift($result)){  
                        $data[] = $row['id'];//Row data shift in data array
                    }
                }

                if(count($data)>0){
                    if(isset($_SESSION['selected_ids'])){
                        $_SESSION['selected_ids'] = array_merge($_SESSION['selected_ids'], $data);
                    }else{
                        $_SESSION['selected_ids'] = $data;
                    }
                }
                return Api::responseOk($data);//Return data
            }

            //Old clients select all row
            else if($operation == "select_all_old"){
                $data = array();
                $pdo_conn = Api::dbConnection();//mysqli connection
                $stmt = $pdo_conn->prepare("SELECT `uc`.* FROM `user_customers` AS `uc` 
                            RIGHT JOIN `customer_msgs` AS `cm` ON `cm`.`customer_id`=`uc`.`id` 
                            WHERE `uc`.`user_id` = ? and  `uc`.`location_id` = ?
                            GROUP BY `cm`.`customer_id` 
                            ORDER BY `uc`.`id` DESC 
                            LIMIT ".$page_start.", ".$page_end."
                             ");
                $stmt->bind_param("ii",$_SESSION['userid'],$_SESSION['location_id']);//Binding paramenter
                $stmt->execute(); //Execute query          
                $result = Api::get_result($stmt); //fetching result  
                if(count($result) > 0){
                    while($row = array_shift($result)){  
                        $data[] = $row['id'];//Row data shift in data array
                    }
                }
                if(count($data)>0){
                    if(isset($_SESSION['selected_ids'])){
                        $_SESSION['selected_ids'] = array_merge($_SESSION['selected_ids'], $data);

                    }else{
                        $_SESSION['selected_ids'] = $data;
                    }
                }
                return Api::responseOk($data);//Return data
            }


            else if($operation == "un_select_all"){
                $data = array();
                $pdo_conn = Api::dbConnection();//mysqli connection
                $stmt = $pdo_conn->prepare("SELECT `uc`.`id` FROM `user_customers` AS `uc` 
                LEFT JOIN `customer_msgs` AS `cm` ON `cm`.`customer_id` =`uc`.`id` 
                WHERE `uc`.`user_id` = ? AND and  `uc`.`location_id` = ? AND  `cm`.`customer_id` IS NULL 
                ORDER BY `uc`.`id` DESC 
                LIMIT ".$page_start.", ".$page_end."
                    ");
                $stmt->bind_param("ii",$_SESSION['userid'],$_SESSION['location_id']);//Binding paramenter
                $stmt->execute(); //Execute query          
                $result = Api::get_result($stmt); //fetching result  
                if(count($result) > 0){
                    while($row = array_shift($result)){  
                        $data[] = $row['id'];//Row data shift in data array
                    }
                }
                
                for($i=0; $i<count($data); $i++){
                    $pos = array_search($data[$i], $_SESSION['selected_ids']);
                    unset($_SESSION['selected_ids'][$pos]); // remove item at index 0
                    $_SESSION['selected_ids'] = array_values($_SESSION['selected_ids']); // 'reindex' array
                }
            }
        }
        else{
            
        }
            

    }//Post function


    /**
     * Delete record
     */
    public function delete( $id = null )
    {
       
    }//Delete function
}
