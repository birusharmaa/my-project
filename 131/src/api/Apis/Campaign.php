<?php
session_start();
class Api_Campaign extends Api
{

  public function __construct()
  {
    // In here you could initialize some shared logic between this API and rest of the project
  }


  /** 
   * Get individual record or records list
   */
  public function get($id = null){
      if($id == ""){ //If id is empty
          //Get selected user_plan id
          $up_status = "Subscribed";
          $plan_id = "";
          $pdo_conn = Api::dbConnection(); //mysqli connection
          $st = $pdo_conn->prepare("SELECT `id` FROM `user_plan` WHERE `user_id` = ? AND `status` = ? AND `expired_on` IS NULL ");
          $st->bind_param('is',$_SESSION['userid'], $up_status);
          $st->execute();//execute query
          $res = Api::get_result($st); //fetching result
          if(count($res) > 0){ //if user exists
              while($row = array_shift($res)){  
                  $plan_id = $row['id'];
              }
          }

          $primaryKey = 'id';
          $table      = 'user_campaigns';
          $table2      = "customer_msgs";
          $columns    = array(
              array('db' => '`uc`.`id`', 'dt' => 0, 'field' => 'id'),
              array('db' => '`uc`.`name`', 'dt' => 1, 'field' => 'name'),
              array(
                  'db' => '`uc`.`added_on`', 'dt' => 2,
                  'formatter' => function ($d, $row) {
                      return gmdate("m/d/Y H:i", ($row['added_on'] - ($_SESSION['your_time_zone'])));
                  },
                  'field' => 'added_on'
              ),
              array(
                  'db' => '`uc`.`scheduled_on`', 'dt' => 3,
                  'formatter' => function ($d, $row) {
                    if ($row['scheduled_on'] == '0' || $row['scheduled_on'] == NULL) {
                        return "";
                    } else {
                        //return $row['scheduled_on'];
                        if ($row['status'] == "Completed") {
                            return gmdate("m/d/y H:i", ($row['scheduled_on'] - ($_SESSION['your_time_zone'])));
                        } else if ($row['status'] == "Pending") {
                            return "";
                        } else {
                            return gmdate("m/d/y H:i", ($row['scheduled_on']  - ($_SESSION['your_time_zone'])));
                        }
                    }
                },
                'field' => 'scheduled_on'
              ),
              array('db' => '`uc`.`remarks`', 'dt' => 4, 'field' => 'remarks'),
              array('db' => '`uc`.`status`', 'dt' => 5, 'field' => 'status'),
              array(
                'db' => '`uc`.`id`', 'dt' => 6,
                'formatter' => function ($d, $row) {
                    if ($row['status'] == "Scheduled" || $row['status'] == "Draft") {
                        return '<a href="javascript:void(0)" title="View" onclick="viewCampaign(' . $d . ')"><img src="./img/view-icon.png" class="view-icon-img mr-3" /></a>
                                    <a href="javascript:void(0)" title="Edit" onclick="editCampaign(' . $d . ')"><img src="./img/edit-icon.png" class="edit-icon-img mr-3" /></a>
                                    <a href="javascript:void(0)" title="Delete" onclick="deleteCampaign(' . $d . ')"><img src="./img/delete-icon.png" class="delete-icon-img" /></a>
                                ';
                    } //If condition
                    else {
                        return '<a href="javascript:void(0)" title="View" onclick="viewCampaign(' . $d . ')"><img src="./img/view-icon.png" class="view-icon-img mr-3" /></a>
                                <a href="javascript:void(0)" title="Archive" onclick="archiveCampaign(' . $d . ')"><i class="fa fa-archive text-dark "></i></a>';
                    } //Else part
                }, //Formatter
                'field' => 'id'
              )
          );
          $archived = 'No';
          // $joinQuery = "FROM `{$table}` AS `uc` LEFT JOIN `customer_msgs` AS `cm` ON `cm`.`campaign_id` = `uc`.`id`
          //     LEFT JOIN `user_customers` AS `u` ON `u`.`id` = `cm`.`customer_id`  ";
          // $extraCondition = " `u`.`user_id` = ".$_SESSION['userid']." AND `uc`.`is_archived` = '".$archived."' ";
          // $groupBy = " `uc`.`id` ";
          $joinQuery = "FROM `{$table}` AS `uc` LEFT JOIN `user_locations` AS `ul` ON `uc`.location_id = `ul`.id  ";
          $extraCondition = " `ul`.`user_id` = " . $_SESSION['userid'] . " AND `ul`.`id` = " . $_SESSION['location_id'] . " AND `uc`.`is_archived` = '" . $archived . "' ";
          $groupBy = NULL;
          $having = NULL;
          $response = Api::getDbData($_REQUEST, $table, $primaryKey, $columns, $joinQuery, $extraCondition, $groupBy, $having);

          return Api::responseOk($response); //Return data
      } //IF id is empty
  }

  /**
   * Update record
   */
  public function put($record_id = null)
  {
    // In real world there would be call to model with validation and probably token checking

    $data_arr = Api::$input_data; //to update
    $id = $record_id; //user id
    $operation = isset($data_arr['operation']) ? $data_arr['operation'] : ""; //Geting operation

    if ($operation == "Isarchive") {
      $is_archived = "Yes";

      $pdo_conn = Api::dbConnection(); //mysqli connection
      $stmt = $pdo_conn->prepare("UPDATE `user_campaigns` AS `uc` LEFT JOIN `user_locations` AS `ul` ON `ul`.`id`= `uc`.`location_id` SET `uc`.`is_archived` = ? WHERE `uc`.`id` = ? AND `ul`.`user_id` = ? ");
      $stmt->bind_param('sii', $is_archived, $id, $_SESSION['userid']);
      if ($stmt->execute()) {
        $response = array('status' => 'success', 'message' => 'Campaign archived successfully.');
        return Api::responseOk($response);
      } else {
        $info = array('ErrorName' => 'Empty', 'message' => 'Campaign archive unsuccessful.');
        return Api::responseError(401, $info);
      }
    } else if ($operation == "updateCampaign") {

      $select_id_arr = $_SESSION['selected_ids'];
      $select_id_arr = array_unique($select_id_arr);
      if (count($select_id_arr) <= 0) {
        $info = array('status' => 'selectError', 'message' => "*Please select minimum 1 user.");
        return Api::responseError(401, $info);
      }

      $pdo_conn = Api::dbConnection(); //mysqli connection
      //Get selected user_plan id
      $up_status = "Subscribed";
      $plan_id = 0;
      $st = $pdo_conn->prepare("SELECT `id` FROM `user_plan` WHERE `user_id` = ? AND `status` = ? AND `expired_on` IS NULL ");
      $st->bind_param('is', $_SESSION['userid'], $up_status);
      $st->execute(); //execute query
      $res = Api::get_result($st); //fetching result
      if (count($res) > 0) { //if user exists
        while ($row = array_shift($res)) {
          $plan_id  = $row['id'];
          $old_plan = $row['id'];
        }
      }

      //Set default value
      $remaing_limit = 0;
      $total_request = 0;
      $request_limit = 0;
      $request_limit_sms = 0;
      //Find total sent campaign requests
      $camp_status = "Draft";
      $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                `total_request` 
                FROM `customer_msgs` AS `cm` 
                LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                WHERE `uc`.`user_id` = ? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? AND `cm`.`sent_via` = 'Email' ");
      $stmt->bind_param('isi', $_SESSION['userid'], $camp_status, $plan_id);
      $stmt->execute(); //execute query
      $result = Api::get_result($stmt); //fetching result
      if (count($result) > 0) { //if user exists
        while ($row = array_shift($result)) {
          $total_request = $row['total_request'];
        }
      }

      //Find total plan request limits
      $stmt = $pdo_conn->prepare("SELECT `p`.`request_limit`, `p`.`request_limit_sms`
                FROM `plans` AS `p` 
                LEFT JOIN `user_plan` AS `up` 
                ON `up`.`plan_id`=`p`.`id` WHERE `up`.`user_id` = ? AND `up`.`status`='Subscribed' ");
      $stmt->bind_param('i', $_SESSION['userid']);
      $stmt->execute(); //execute query
      $result = Api::get_result($stmt); //fetching result
      if (count($result) > 0) { //if user exists
        while ($row = array_shift($result)) {
          $request_limit     = $row['request_limit'];
          $request_limit_sms = $row['request_limit_sms'];
        }
      }

      //Campaign sending type
      $send_type   = isset($data_arr['send_type']) ? $data_arr['send_type'] : '';
      $messageType = isset($data_arr['messageType']) ? $data_arr['messageType'] : '';

      //Checking remaing limits if sent_type is not Draft
      if ($send_type != "save" and $messageType == "Email") {
        $remaing_limit = $request_limit - $total_request;
        if ($remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($remaing_limit != 0) {
          if (count($select_id_arr) > $remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback request limit.");
            return Api::responseError(401, $info);
          }
        }
      }


      $select_id   = !empty($data_arr['select_id']) ? $data_arr['select_id'] : '';
      $location_id = !empty($data_arr['location_id']) ? $data_arr['location_id'] : '';
      //$customMsg   = !empty($data_arr['customMsg']) ? $data_arr['customMsg'] : '';
      $date_time   = !empty($data_arr['date_time']) ? $data_arr['date_time'] : '';
      $camp_name   = !empty($data_arr['campName']) ? $data_arr['campName'] : '';
      $smsMsgInfo  = isset($data_arr['customSmsMsgInfo']) ? $data_arr['customSmsMsgInfo'] : '';
      $emailMsgInfo = isset($data_arr['customEmailMsgInfo']) ? $data_arr['customEmailMsgInfo'] : '';
      $camp_id     = isset($data_arr['campId']) ? $data_arr['campId'] : "";
      $emailMsgId  = isset($data_arr['customEmailMsgId']) ? $data_arr['customEmailMsgId'] : '';
      $smsMsgId    = isset($data_arr['customSmsMsgId']) ? $data_arr['customSmsMsgId'] : 'customSmsMsg';
      $remark      = !empty($data_arr['remark']) ? $data_arr['remark'] : '';

      if (empty($camp_id)) { //camp id is not empty
        $info = array('status' => 'Error', 'message' => 'Campaign id not found, please try again.');
        return Api::responseError(401, $info);
      }
      //Selected id decode in json
      $select_id_arr = $_SESSION['selected_ids'];
      if (count($select_id_arr) <= 0) { //If select check
        $info = array('status' => 'Error', 'message' => 'Customer id not found, please try again.');
        return Api::responseError(401, $info);
      }

      //Checking remaing limits if sent_type is not Draft for SMS
      //Checking remaing limits if sent_type is not Draft for SMS
      if ($send_type != "save" and $messageType == "Phone") {

        $camp_status = "Draft";
        $sent_via = "Phone";
        $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                    `total_request` 
                    FROM `customer_msgs` AS `cm` 
                    LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                    LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                    WHERE `uc`.`user_id` = ? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? AND `cm`.`sent_via` = 'Phone' ");
        $stmt->bind_param('isi', $_SESSION['userid'], $camp_status, $plan_id);
        $stmt->execute(); //execute query
        $result = Api::get_result($stmt); //fetching result
        if (count($result) > 0) { //if user exists
          while ($row = array_shift($result)) {
            $total_sms_request = $row['total_request'];
          }
        }
        $remaing_limit = $request_limit_sms - $total_sms_request;
        if ($remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($remaing_limit != 0) {
          if (count($select_id_arr) > $remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback request limit.");
            return Api::responseError(401, $info);
          }
        }
      }


      //Total request check for Both
      if ($send_type != "save" and $messageType == "Both") {

        $camp_status = "Draft";
        $sent_via = "Phone";
        $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                    `total_request` 
                    FROM `customer_msgs` AS `cm` 
                    LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                    LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                    WHERE `uc`.`user_id` = ? AND `sent_via`=? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? ");
        $stmt->bind_param('issi', $_SESSION['userid'], $sent_via, $camp_status, $old_plan);
        $stmt->execute(); //execute query
        $result = Api::get_result($stmt); //fetching result
        if (count($result) > 0) { //if user exists
          while ($row = array_shift($result)) {
            $total_sms_request = $row['total_request'];
          }
        }

        $sent_via = "Email";
        $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                    `total_request` 
                    FROM `customer_msgs` AS `cm` 
                    LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                    LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                    WHERE `uc`.`user_id` = ? AND `sent_via`=? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? ");
        $stmt->bind_param('issi', $_SESSION['userid'], $sent_via, $camp_status, $old_plan);
        $stmt->execute(); //execute query
        $result = Api::get_result($stmt); //fetching result
        if (count($result) > 0) { //if user exists
          while ($row = array_shift($result)) {
            $total_email_request = $row['total_request'];
          }
        }

        $sms_remaing_limit = $request_limit_sms - $total_sms_request;

        if ($sms_remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback sms request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($sms_remaing_limit != 0) {
          if (count($select_id_arr) > $sms_remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback sms request limit.");
            return Api::responseError(401, $info);
          }
        }

        $remaing_limit = $request_limit - $total_email_request;
        if ($remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback email request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($remaing_limit != 0) {
          if (count($select_id_arr) > $remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback email request limit.");
            return Api::responseError(401, $info);
          }
        }
      }

      if ($send_type == "later") {
        date_default_timezone_set('UTC');
        //$date_time = gmdate("m-d-Y H:i", ( $date_time - ($_SESSION['your_time_zone'])));
        $scheduled_on = strtotime($date_time) + ($_SESSION['your_time_zone']);
        $status = "Scheduled";
        $send_on = 'NULL';
      } else {
        $scheduled_on = 'NULL';
        $send_on = 'NULL';
      }

      if ($send_type == "now") {
        $status = "Pending";
        $send_on = time();
        date_default_timezone_set('UTC');
        $scheduled_on = time();
      }

      if ($send_type == "save") {
        $status = "Draft";
        $send_on = 'NULL';
      }

      $is_archived   = "No";
      $request_type  = "Campaign";
      $reminder      = $data_arr['reminderCheck'];
      $feedback_code = "00";

      $stmt = $pdo_conn->prepare("UPDATE `user_campaigns` AS `uc` 
                                        LEFT JOIN `user_locations` 
                                        AS `ul` ON `ul`.`id`=`uc`.`location_id` 
                                        SET `uc`.`location_id` = ?, `uc`.`scheduled_on` = ?, 
                                        `uc`.`status` = ?, `uc`.`is_archived` = ?, 
                                        `uc`.`name` = ?, `uc`.`remarks` = ? WHERE `uc`.`id` = ? AND `ul`.`user_id` = ? ");

      $stmt->bind_param('iissssii', $_SESSION['location_id'], $scheduled_on, $status, $is_archived, $camp_name, $remark, $camp_id, $_SESSION['userid']);
      if ($stmt->execute()) {
        if (count($select_id_arr) > 0) {
          $status = "Pending";
          $campaign_id =  $camp_id;

          //Campaign create for Both message type
          if ($messageType == "Both") { //If message type Both

            $type = "Email";
            $type_phone = "Phone";
            $query = "";
            $query = "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id`) VALUES ";
            $check = 0;

            //Query value for Email and Phone
            for ($i = 0; $i < count($select_id_arr); $i++) {
              $unique = Api::getName(5);
              $unique = Api::uniqueCheck($pdo_conn, $unique);

              $empty_check_phone = "";
              $empty_check_email = "";
              $client_name       = "";

              //Empty check customer email and phone
              $check_stmt = $pdo_conn->prepare("SELECT `name`, `phone`, `email` FROM `user_customers` WHERE `id` = ? ");
              $check_stmt->bind_param('i', $select_id_arr[$i]);
              $check_stmt->execute(); //execute query
              $check_res = Api::get_result($check_stmt); //fetching result
              if (count($check_res) > 0) { //if user exists
                while ($row = array_shift($check_res)) {
                  $empty_check_phone = $row['phone'];
                  $empty_check_email = $row['email'];
                  $client_name       = $row['name'];
                }
              }

             //Make query without comma
            if (!empty($empty_check_phone) && $check == 0) {
              $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type_phone . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id ,'" . $_SESSION['location_id'] . "' )";
            }

            //Make query with comma
            if (!empty($empty_check_phone) && $check == 1) {
              $query .= ", (" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type_phone . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id ,'" . $_SESSION['location_id'] . "' )";
            }

            //Make query without comma
            if (!empty($empty_check_email) && $check == 0 && empty($empty_check_phone)) {
              $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' )";
            }

            //Make query with comma
            if (!empty($empty_check_email) && ($check == 1 || !empty($empty_check_phone))) {
              $query .= ", (" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' )";
            }
            //Phone and Email empty check
            if (empty($empty_check_phone) && empty($empty_check_email)) {
              // $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
              // $stmt->bind_param('i', $campaign_id);
              // $stmt->execute();
              $info = array('status' => 'errorMsg', 'message' => 'We can\'t create campaign because "' . $client_name . '" does\'t have email or phone.');
              return Api::responseError(401, $info);
            } else {
              $check = 1;
            }
            }

            //Query empty check
            if (!empty($query)) {
              $stmt = $pdo_conn->prepare("DELETE FROM `customer_msgs` WHERE `campaign_id` = ? ");
              $stmt->bind_param('i', $camp_id);
              if ($stmt->execute()) {
                $stmt = $pdo_conn->prepare($query);
                if ($stmt->execute()) { //if query execute(Insert customer_msgs table data )    
                  $response = array('status' => 'success', 'message' => 'Campaign created successfully.');
                  return Api::responseOk($response);
                } else {
                  $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
                  $stmt->bind_param('i', $campaign_id);
                  $stmt->execute();

                  $info = array('status' => 'success', 'message' => 'Campaign execute usuccessfully.');
                  return Api::responseError(401, $info);
                }
              }
            } else {
              $info = array('status' => 'success', 'message' => 'Campaign execute usuccessfully.');
              return Api::responseError(401, $info);
            }
          }

          //Campaign create for Email message type
          if ($messageType == "Email") { //If message type Email

            $query = "";
            $query = "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES ";

            //Query value
            for ($i = 0; $i < count($select_id_arr); $i++) {
              $unique = Api::getName(5);
              $unique = Api::uniqueCheck($pdo_conn, $unique);

              $empty_check_email = "";
              $client_name       = "";
              //Empty check customer email and phone
              $check_stmt = $pdo_conn->prepare("SELECT `name`, `email` FROM `user_customers` WHERE `id` = ? ");
              $check_stmt->bind_param('i', $select_id_arr[$i]);
              $check_stmt->execute(); //execute query
              $check_res = Api::get_result($check_stmt); //fetching result
              if (count($check_res) > 0) { //if user exists
                while ($row = array_shift($check_res)) {
                  $empty_check_email = $row['email'];
                  $client_name       = $row['name'];
                }
              }

              //Comma add or not 
              if ($i == (count($select_id_arr) - 1)) {
                $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "', '" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id," . $_SESSION['location_id'] . " ) ";
              } else {
                $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "', '" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id," . $_SESSION['location_id'] . " ), ";
              }

              //Email empty check
              if (empty($empty_check_email)) {

                $info = array('status' => 'success', 'message' => 'We can\'t create campaign because "' . $client_name . '" does\'t have email.');
                return Api::responseError(401, $info);
              }
            }

            if ($query != "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES") {
              $stmt = $pdo_conn->prepare("DELETE FROM `customer_msgs` WHERE `campaign_id` = ? ");
              $stmt->bind_param('i', $camp_id);
              if ($stmt->execute()) {
                $stmt = $pdo_conn->prepare($query);
                $stmt->execute();
                $response = array('status' => 'success', 'message' => 'Campaign created successfully.');
                return Api::responseOk($response);
              }
            } else {

              $info = array('status' => 'success', 'message' => 'Campaign execution failed.');
              return Api::responseError(401, $info);
            }
          }

          //Campaign create for Phone message type
          if ($messageType == "Phone") { //If message type Phone
            $query = "";
            $query = "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES ";

            //Query value
            for ($i = 0; $i < count($select_id_arr); $i++) {
              $unique = Api::getName(5);
              $unique = Api::uniqueCheck($pdo_conn, $unique);

              $empty_check_phone = "";
              $client_name       = "";
              //Empty check customer email and phone
              $check_stmt = $pdo_conn->prepare("SELECT `name`, `phone` FROM `user_customers` WHERE `id` = ? ");
              $check_stmt->bind_param('i', $select_id_arr[$i]);
              $check_stmt->execute(); //execute query
              $check_res = Api::get_result($check_stmt); //fetching result
              if (count($check_res) > 0) { //if user exists
                while ($row = array_shift($check_res)) {
                  $empty_check_phone = $row['phone'];
                  $client_name       = $row['name'];
                }
              }

              //Comma add or not
              if ($i == (count($select_id_arr) - 1)) {
                $query .= "(" . $select_id_arr[$i] . ",'" . $emailMsgId . "','" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id," . $_SESSION['location_id'] . " ) ";
              } else {
                $query .= "(" . $select_id_arr[$i] . ",'" . $emailMsgId . "','" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id," . $_SESSION['location_id'] . " ), ";
              }

              //Phone number empty check
              if (empty($empty_check_phone)) {

                $info = array('status' => 'success', 'message' => 'We can\'t create campaign because "' . $client_name . '" does\'t have phone number.');
                return Api::responseError(401, $info);
              }
            }

            if ($query != "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES") {
              $stmt = $pdo_conn->prepare("DELETE FROM `customer_msgs` WHERE `campaign_id` = ? ");
              $stmt->bind_param('i', $camp_id);
              if ($stmt->execute()) {
                $stmt = $pdo_conn->prepare($query);
                $stmt->execute();
                $response = array('status' => 'success', 'message' => 'Campaign created successfully.');
                return Api::responseOk($response);
              }
            } else {
              $info = array('status' => 'success', 'message' => 'Campaign execution failed.');
              return Api::responseError(401, $info);
            }
          }
        }
      } else {
        $info = array('ErrorName' => 'Empty', 'message' => 'Somethings wrong, Please try after some time');
        return Api::responseError(401, $info);
      }
    } else {
    }
  }

  /**
   * Create record
   */
  public function post($operation = null)
  {
    $operation = isset($_POST['operation']) ? $_POST['operation'] : '';

    if ($operation == "addCampaign") {

      $select_id_arr = $_SESSION['selected_ids'];
      $select_id_arr = array_unique($select_id_arr);
      $pdo_conn = Api::dbConnection(); //mysqli connection


      //Get selected user_plan id
      $up_status = "Subscribed";
      $plan_id = 0;
      $st = $pdo_conn->prepare("SELECT `id` FROM `user_plan` WHERE `user_id` = ? AND `status` = ? AND `expired_on` IS NULL ");
      $st->bind_param('is', $_SESSION['userid'], $up_status);
      $st->execute(); //execute query
      $res = Api::get_result($st); //fetching result
      if (count($res) > 0) { //if user exists
        while ($row = array_shift($res)) {
          $plan_id = $row['id'];
        }
      }

      //Set default value
      $remaing_limit = 0;
      $total_request = 0;
      $request_limit = 0;
      $request_limit_sms = 0;

      //Find total sent campaign requests
      $camp_status = "Draft";
      $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                `total_request` 
                FROM `customer_msgs` AS `cm` 
                LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                WHERE `uc`.`user_id` = ? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? AND `cm`.`sent_via` = 'Email' ");
      $stmt->bind_param('isi', $_SESSION['userid'], $camp_status, $plan_id);
      $stmt->execute(); //execute query
      $result = Api::get_result($stmt); //fetching result
      if (count($result) > 0) { //if user exists
        while ($row = array_shift($result)) {
          $total_request = $row['total_request'];
        }
      }

      //Find total plan request limits
      $stmt = $pdo_conn->prepare("SELECT `p`.`request_limit`, `p`.`request_limit_sms`
                FROM `plans` AS `p` 
                LEFT JOIN `user_plan` AS `up` 
                ON `up`.`plan_id`=`p`.`id` WHERE `up`.`user_id` = ? AND `up`.`status`='Subscribed' ");
      $stmt->bind_param('i', $_SESSION['userid']);
      $stmt->execute(); //execute query
      $result = Api::get_result($stmt); //fetching result
      if (count($result) > 0) { //if user exists
        while ($row = array_shift($result)) {
          $request_limit     = $row['request_limit'];
          $request_limit_sms = $row['request_limit_sms'];
        }
      }

      //Campaign sending type
      $send_type   = isset($_POST['send_type']) ? $_POST['send_type'] : '';
      $messageType = isset($_POST['messageType']) ? $_POST['messageType'] : '';

      //Checking remaing limits if sent_type is not Draft
      if ($send_type != "save" and $messageType == "Email") {
        $remaing_limit = $request_limit - $total_request;
        if ($remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($remaing_limit != 0) {
          if (count($select_id_arr) > $remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback request limit.");
            return Api::responseError(401, $info);
          }
        }
      }


      $select_id   = isset($_POST['select_id']) ? $_POST['select_id'] : '';
      //$mode        = isset($_POST['mode']) ? $_POST['mode'] : '';
      // $location_id = isset($_POST['location_id']) ? $_POST['location_id'] : '';
      $messageType = isset($_POST['messageType']) ? $_POST['messageType'] : '';
      $customMsg   = isset($_POST['customMsg']) ? $_POST['customMsg'] : '';
      $date_time   = isset($_POST['date_time']) ? $_POST['date_time'] : '';
      $camp_name   = isset($_POST['campName']) ? $_POST['campName'] : '';
      $smsMsgInfo  = isset($_POST['customSmsMsgInfo']) ? $_POST['customSmsMsgInfo'] : '';
      $emailMsgInfo = isset($_POST['customEmailMsgInfo']) ? $_POST['customEmailMsgInfo'] : '';
      $emailMsgId  = isset($_POST['customEmailMsgId']) ? $_POST['customEmailMsgId'] : '';
      $smsMsgId    = isset($_POST['customSmsMsgId']) ? $_POST['customSmsMsgId'] : '';
      $remark_value = isset($_POST['remark']) ? $_POST['remark'] : '';

      //Checking remaing limits if sent_type is not Draft for SMS
      if ($send_type != "save" and $messageType == "Phone") {

        $camp_status = "Draft";
        $sent_via = "Phone";
        $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                    `total_request` 
                    FROM `customer_msgs` AS `cm` 
                    LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                    LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                    WHERE `uc`.`user_id` = ? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? AND `cm`.`sent_via` = 'Phone' ");
        $stmt->bind_param('isi', $_SESSION['userid'], $camp_status, $plan_id);
        $stmt->execute(); //execute query
        $result = Api::get_result($stmt); //fetching result
        if (count($result) > 0) { //if user exists
          while ($row = array_shift($result)) {
            $total_sms_request = $row['total_request'];
          }
        }

        $remaing_limit = $request_limit_sms - $total_sms_request;
        if ($remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0

        if ($remaing_limit != 0) {
          if (count($select_id_arr) > $remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback request limit.");
            return Api::responseError(401, $info);
          }
        }
      }

      //Total request check for Both
      if ($send_type != "save" and $messageType == "Both") {

        $camp_status = "Draft";
        $sent_via = "Phone";
        $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                    `total_request` 
                    FROM `customer_msgs` AS `cm` 
                    LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                    LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                    WHERE `uc`.`user_id` = ? AND `sent_via`=? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? ");
        $stmt->bind_param('issi', $_SESSION['userid'], $sent_via, $camp_status, $old_plan);
        $stmt->execute(); //execute query
        $result = Api::get_result($stmt); //fetching result
        if (count($result) > 0) { //if user exists
          while ($row = array_shift($result)) {
            $total_sms_request = $row['total_request'];
          }
        }

        $camp_status = "Draft";
        $sent_via = "Email";
        $stmt = $pdo_conn->prepare("SELECT COUNT(DISTINCT `cm`.`feedback_code`) AS 
                    `total_request` 
                    FROM `customer_msgs` AS `cm` 
                    LEFT JOIN `user_customers` AS `uc` ON `uc`.`id`=`cm`.`customer_id` 
                    LEFT JOIN `user_campaigns` AS `u_camp` ON `u_camp`.`id`=`cm`.`campaign_id` 
                    WHERE `uc`.`user_id` = ? AND `sent_via`=? AND `u_camp`.`status` != ? AND `cm`.`plan_id` = ? ");
        $stmt->bind_param('issi', $_SESSION['userid'], $sent_via, $camp_status, $plan_id);
        $stmt->execute(); //execute query
        $result = Api::get_result($stmt); //fetching result
        if (count($result) > 0) { //if user exists
          while ($row = array_shift($result)) {
            $total_email_request = $row['total_request'];
          }
        }

        $sms_remaing_limit = $request_limit_sms - $total_sms_request;

        if ($sms_remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback sms request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($sms_remaing_limit != 0) {
          if (count($select_id_arr) > $sms_remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback sms request limit.");
            return Api::responseError(401, $info);
          }
        }

        $remaing_limit = $request_limit - $total_email_request;
        if ($remaing_limit <= 0) {
          $info = array('status' => 'remaing_limit', 'message' => "Sorry! Your maximum feedback email request limit for this month has been reached.");
          return Api::responseError(401, $info);
        }
        // AND $total_request !=0
        if ($remaing_limit != 0) {
          if (count($select_id_arr) > $remaing_limit) {
            $info = array('status' => 'remaing_limit', 'message' => "Sorry! Only " . $remaing_limit . " requests is left as per your monthly feedback email request limit.");
            return Api::responseError(401, $info);
          }
        }
      }

      if ($send_type == "later") {
        date_default_timezone_set('UTC');
        $scheduled_on = strtotime($date_time) + ($_SESSION['your_time_zone']);
        $status = "Scheduled";
        $send_on = 'NULL';
      } else {
        $scheduled_on = 'NULL';
        $send_on = 'NULL';
      }

      if ($send_type == "now") {
        $status = "Pending";
        $send_on = time();
        date_default_timezone_set('UTC');
        $scheduled_on = time();
      }

      if ($send_type == "save") {
        $status = "Draft";
        $send_on = 'NULL';
      }

      $is_archived   = "No";
      $request_type  = "Campaign";
      $reminder      = $_POST['reminderCheck'];
      $feedback_code = "00";


      $stmt = $pdo_conn->prepare("INSERT INTO `user_campaigns` 
                (`location_id`,`added_on`,`scheduled_on`,`status`,`is_archived`,`name`,`remarks`)
                 VALUES (?,UNIX_TIMESTAMP(),?,?,?,?,?)");
      $stmt->bind_param('iissss', $_SESSION['location_id'], $scheduled_on, $status, $is_archived, $camp_name, $remark_value);
      if ($stmt->execute()) { // Insert data in users table
        $status = "Pending";
        $campaign_id =  $stmt->insert_id;

        //Campaign create for Both message type
        if ($messageType == "Both") { //If message type Both

          $type = "Email";
          $type_phone = "Phone";
          $query = "";
          $query = "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id`) VALUES";
          $check = 0;

          //Query value for Email and Phone
          for ($i = 0; $i < count($select_id_arr); $i++) {
            $unique = Api::getName(5);
            $unique = Api::uniqueCheck($pdo_conn, $unique);
            $empty_check_phone = "";
            $empty_check_email = "";
            $client_name       = "";

            //Empty check customer email and phone
            $check_stmt = $pdo_conn->prepare("SELECT `name`, `phone`, `email` FROM `user_customers` WHERE `id` = ? ");
            $check_stmt->bind_param('i', $select_id_arr[$i]);
            $check_stmt->execute(); //execute query
            $check_res = Api::get_result($check_stmt); //fetching result
            if (count($check_res) > 0) { //if user exists
              while ($row = array_shift($check_res)) {
                $empty_check_phone = $row['phone'];
                $empty_check_email = $row['email'];
                $client_name       = $row['name'];
              }
            }

            //Make query without comma
            if (!empty($empty_check_phone) && $check == 0) {
              $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type_phone . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id ,'" . $_SESSION['location_id'] . "' )";
            }

            //Make query with comma
            if (!empty($empty_check_phone) && $check == 1) {
              $query .= ", (" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type_phone . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id ,'" . $_SESSION['location_id'] . "' )";
            }

            //Make query without comma
            if (!empty($empty_check_email) && $check == 0 && empty($empty_check_phone)) {
              $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' )";
            }

            //Make query with comma
            if (!empty($empty_check_email) && ($check == 1 || !empty($empty_check_phone))) {
              $query .= ", (" . $select_id_arr[$i] . ",'" . $smsMsgId . "','" . $type . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' )";
            }
            //Phone and Email empty check
            if (empty($empty_check_phone) && empty($empty_check_email)) {
              $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
              $stmt->bind_param('i', $campaign_id);
              $stmt->execute();
              $info = array('status' => 'errorMsg', 'message' => 'We can\'t create campaign because "' . $client_name . '" does\'t have email or phone.');
              return Api::responseError(401, $info);
            } else {
              $check = 1;
            }
          }

          // echo $query;
          // exit;
          //Query empty check
          if (!empty($query)) {

            $stmt = $pdo_conn->prepare($query);
            if ($stmt->execute()) { //if query execute(Insert customer_msgs table data )    
              $response = array('status' => 'success', 'message' => 'Campaign created successfully.');
              return Api::responseOk($response);
            } else {
              $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
              $stmt->bind_param('i', $campaign_id);
              $stmt->execute();

              $info = array('status' => 'errorMsg', 'message' => 'Campaign execute usuccessfully.');
              return Api::responseError(401, $info);
            }
          } else {
            echo "fff";
            $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
            $stmt->bind_param('i', $campaign_id);
            $stmt->execute();

            $info = array('status' => 'errorMsg', 'message' => 'Campaign execute usuccessfully.');
            return Api::responseError(401, $info);
          }
        }

        //Campaign create for Email message type
        if ($messageType == "Email") { //If message type Email

          $query = "";
          $query = "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES ";

          //Query value
          for ($i = 0; $i < count($select_id_arr); $i++) {
            $unique = Api::getName(5);
            $unique = Api::uniqueCheck($pdo_conn, $unique);

            $empty_check_email = "";
            $client_name       = "";
            //Empty check customer email and phone
            $check_stmt = $pdo_conn->prepare("SELECT `name`, `email` FROM `user_customers` WHERE `id` = ? ");
            $check_stmt->bind_param('i', $select_id_arr[$i]);
            $check_stmt->execute(); //execute query
            $check_res = Api::get_result($check_stmt); //fetching result
            if (count($check_res) > 0) { //if user exists
              while ($row = array_shift($check_res)) {
                $empty_check_email = $row['email'];
                $client_name       = $row['name'];
              }
            }

            //Comma add or not 
            if ($i == (count($select_id_arr) - 1)) {
              $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "', '" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' ) ";
            } else {
              $query .= "(" . $select_id_arr[$i] . ",'" . $smsMsgId . "', '" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' ), ";
            }

            //Email empty check
            if (empty($empty_check_email)) {
              $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
              $stmt->bind_param('i', $campaign_id);
              $stmt->execute();
              $info = array('status' => 'errorMsg', 'message' => 'We can\'t create campaign because "' . $client_name . '" does\'t have email.');
              return Api::responseError(401, $info);
            }
          }

          if ($query != "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES") {
            $stmt = $pdo_conn->prepare($query);
            $stmt->execute();
            $response = array('status' => 'success', 'message' => 'Campaign created successfully.');
            return Api::responseOk($response);
          } else {
            $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
            $stmt->bind_param('i', $campaign_id);
            $stmt->execute();
            $info = array('status' => 'errorMsg', 'message' => 'Campaign execution failed.');
            return Api::responseError(401, $info);
          }
        }

        //Campaign create for Phone message type
        if ($messageType == "Phone") { //If message type Phone

          $query = "";
          $query = "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES ";

          //Query value
          for ($i = 0; $i < count($select_id_arr); $i++) {

            $unique = Api::getName(5);
            $unique = Api::uniqueCheck($pdo_conn, $unique);

            $empty_check_phone = "";
            $client_name       = "";
            //Empty check customer email and phone
            $check_stmt = $pdo_conn->prepare("SELECT `name`, `phone` FROM `user_customers` WHERE `id` = ? ");
            $check_stmt->bind_param('i', $select_id_arr[$i]);
            $check_stmt->execute(); //execute query
            $check_res = Api::get_result($check_stmt); //fetching result
            if (count($check_res) > 0) { //if user exists
              while ($row = array_shift($check_res)) {
                $empty_check_phone = $row['phone'];
                $client_name       = $row['name'];
              }
            }

            //Comma add or not
            if ($i == (count($select_id_arr) - 1)) {
              $query .= "(" . $select_id_arr[$i] . ",'" . $emailMsgId . "','" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' ) ";
            } else {
              $query .= "(" . $select_id_arr[$i] . ",'" . $emailMsgId . "','" . $messageType . "','" . $request_type . "'," . $campaign_id . ",'" . $reminder . "','" . mysqli_real_escape_string($pdo_conn, $smsMsgInfo) . "','" . $status . "','" . $unique . "', $plan_id,'" . $_SESSION['location_id'] . "' ), ";
            }

            //Phone number empty check
            if (empty($empty_check_phone)) {

              $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
              $stmt->bind_param('i', $campaign_id);
              $stmt->execute();
              $info = array('status' => 'errorMsg', 'message' => 'We can\'t create campaign because "' . $client_name . '" does\'t have phone number.');
              return Api::responseError(401, $info);
            }
          }

          if ($query != "INSERT INTO `customer_msgs` (`customer_id`, `admin_msg_id`, `sent_via`, `request_type`, `campaign_id`, `reminder`, `msg`, `status`,`feedback_code`, `plan_id`,`location_id` ) VALUES") {
            $stmt = $pdo_conn->prepare($query);
            $stmt->execute();

            $response = array('status' => 'errorMsg', 'message' => 'Campaign created successfully.');
            return Api::responseOk($response);
          } else {
            $stmt = $pdo_conn->prepare("DELETE FROM `user_campaigns` WHERE `id` = ? ");
            $stmt->bind_param('i', $campaign_id);
            $stmt->execute();
            $info = array('status' => 'errorMsg', 'message' => 'Campaign execution failed.');
            return Api::responseError(401, $info);
          }
        }
      } //First If condition
      else {
        $info = array('status' => 'success', 'message' => 'Campaign execute usuccessfully.');
        return Api::responseError(401, $info);
      } //Else part               
    } //If operation

    if ($operation == "resetSelectedId") {
      if (isset($_SESSION['selected_ids'])) {
        unset($_SESSION['selected_ids']);
        $_SESSION['selected_ids'] = "";
      }
    }
  } //Post function


  /**
   * Delete record
   */
  public function delete($id = null)
  {
    // In real world there would be call to model with validation and probably token checking
    if (empty($id)) { //Cutomer id empty check
      $info = array('ErrorName' => 'Empty', 'message' => 'Campaign id is not found.');
      return Api::responseError(401, $info);
    }

    $pdo_conn = API::dbConnection();
    $stmt = $pdo_conn->prepare("DELETE `uc`.* FROM `user_campaigns` AS `uc` 
                                    LEFT JOIN `user_locations` AS `ul` 
                                    ON `uc`.`location_id` = `ul`.`id` 
                                    WHERE `uc`.`id` = ? AND `ul`.`user_id` = ? ");
    $stmt->bind_param('ii', $id, $_SESSION['userid']);
    if ($stmt->execute()) {
      $response = array('status' => 'success', 'message' => 'Campaign deleted successfully.');
      return Api::responseOk($response);
    } else {
      $info = array('ErrorName' => 'Empty', 'message' => 'Somethings wrong, Please try after some time');
      return Api::responseError(401, $info);
    }
  } //Delete function   
}
