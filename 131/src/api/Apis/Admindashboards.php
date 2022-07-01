<?php
session_start();

class Api_Admindashboards extends Api
{
     
    public function __construct()
    {
        // In here you could initialize some shared logic between this API and rest of the project
    }

    /**  
     * Get individual record or records list
     */
    public function get($id = null){

        if($id ==""){ 
            $user_id = $_SESSION['userid'];
            $data = array();
            $range = 123;//Set default unixtimestamp
            $pdo_conn = Api::dbConnection();//mysqli connection
            //Select date options
            // if($_GET['filter'] == "selectedDate"){
                
            //     $date = $_GET['date'];//Select Date type
            //     if($date == "24hours"){//Select value is 24hours
            //         $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= UNIX_TIMESTAMP( NOW() - INTERVAL 24 HOUR )";
            //     }
            //     if($date == "7days"){//Select option last 7 days
            //         $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY) )";
            //     }
            //     if($date == "30days"){//Select option 30 days
            //         $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY) )";
            //     }
            //     if($date == "1year"){////Select option 1 year
            //         $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 YEAR) )";
            //     }
            //     if($date == "currentYear"){////Select option 1 year
            //         $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= UNIX_TIMESTAMP(CONCAT(YEAR(CURDATE()),'-01-01'))";
            //     }
            //     if($date == "pastYear"){////Select option past year
            //         $current = getdate()[0];
            //         $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= UNIX_TIMESTAMP(NOW() - Interval 1 Year)";
            //     }
            // }//If condition

            if($_GET['filter'] == "selectedDate"){
                $date = $_GET['date'];//Select Date type
                if($date == "24hours"){//Select value is 24hours
                    $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= ($time-(24*60*60))";
                }
                if($date == "7days"){//Select option last 7 days
                    $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= ($time-(24*60*60*7))";
                }
                if($date == "30days"){//Select option 30 days
                    $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= ($time-(24*60*60*30))";
                }
                if($date == "1year"){////Select option 1 year
                    $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND FROM_UNIXTIME(`cm`.`sent_on`) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 YEAR) )";
                }
                if($date == "currentYear"){////Select option 1 year
                    $date = date("Y")."-01-01";
                    $date  = strtotime($date);   
                    $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` >= ".$date." ";
                }
                if($date == "pastYear"){////Select option past year
                    $current = getdate()[0];
                    $start_date = (date("Y")-1)."-01-01";
                    $end_date = (date("Y")-1)."-12-31";
                    
                    $from_date  = strtotime($start_date);                   
                    $to_date    = strtotime($end_date);
                    $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` BETWEEN ".$from_date." AND ".$to_date."  ";
                }
            }//If condition

            //Custom date seleted
            else if($_GET['filter'] == "dateRange"){
                $startDate  = $_GET['startDate'];
                $endDate    = $_GET['endDate'];
                $from_date  = strtotime($startDate);                   
                $to_date    = strtotime($endDate);
                $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL AND `cm`.`sent_on` BETWEEN ".$from_date." AND ".$to_date." ";
            }//custom date

            //By default
            else{ 
                $date_range_where_condition = "WHERE `up`.`expired_on` IS NULL ";
            }

            $stmt = $pdo_conn->prepare("SELECT
                COUNT(CASE WHEN `cm`.`status` != 'Sent' AND `cm`.`status` != 'Pending' AND `cm`.`status` != 'Not Sent' THEN 1 ELSE NULL END )
                AS 'Clicked',
                COUNT(CASE WHEN `cm`.`status` = 'Sent' AND `cm`.`status` != 'Pending' AND `cm`.`status` != 'Not Sent' THEN 1 ELSE NULL END )
                AS 'Notclicked',
                COUNT(CASE WHEN `cm`.`is_recommended` = 'Yes' THEN 1 ELSE NULL END )
                AS 'Recommend',
                COUNT(CASE WHEN `cm`.`is_recommended` = 'No' AND `cm`.`status` = 'Clicked' THEN 1 ELSE NULL END )
                AS 'Notrecommend',
                COUNT(CASE WHEN `cm`.`status` = 'Reviewed' THEN 1 ELSE NULL END )
                AS 'Review'
                FROM ( SELECT * FROM `customer_msgs` GROUP BY `customer_msgs`.`feedback_code`) AS `cm` LEFT JOIN `user_customers`
                AS `uc` ON `uc`.`id`=`cm`.`customer_id` LEFT JOIN `users`
                AS `u` ON `u`.`id`=`uc`.`user_id` LEFT JOIN `user_plan` AS `up` ON `up`.`user_id`=`u`.`id`
                $date_range_where_condition
            ");

            $stmt->execute(); //Execute query          
            $result = Api::get_result($stmt); //fetching result  
            if(count($result) > 0){//If result if exits
                while($row = array_shift($result)){//
                    $data = $row;
                }
            }

            return Api::responseOk($data);//Return data

        }//IF id is empty
    } 

    /**
     * Update record
     */
    public function put($record_id = null)
    {

    }

    /**
     * Create record
     */
    public function post($operation = null)
    { 

    }//Post function


    /**
     * Delete record
     */
    public function delete( $id = null )
    {
       
    }//Delete function
}