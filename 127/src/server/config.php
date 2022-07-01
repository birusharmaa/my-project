<?php
// define("HOST", "151.106.96.51");  // DB Host name generally localhost
// define("dbUser", "u481967625_pranamthankyou"); // Db user
// define("dbPassword","Pranam@001"); // Db password
// define("dbToUse", "u481967625_pranamthankyou");

//$globalvar = 'http://localhost/projects-sm/master/src/';

//define("BASEPATH", "http://localhost/projects-sm/master/src/");

global $sql_dt_conn;
define("HOST", "localhost");  // DB Host name generally localhost
define("dbUser", "root"); // Db user
define("dbPassword",""); // Db password
define("dbToUse", "127"); 

// unset($_SESSION['is_front_page']);

error_reporting(E_ALL);
define('GIFTBIT_TEST_MODE', false);
define('GIFTBIT_TEST_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJTSEEyNTYifQ==.LzFqeDJuWmpkWmR2c25lSVVleW9kcWhjZWJDNGh0YjlWS1I5MVRRS1VMbWNmV3BIaWIydVZnWlc4ZjhpbStQUjR3MVU5bjY4WWdTaXJEWnZnMnd2N0xlVFVYSUVnTXliY1hNdkVEdG9KVHRKdkFKTHBFTHNxODhBVDRnbHRMcVY=.5TOxuCIT0/YDULdTVJ7xh+jFGRpWOUGQsOWS8ZrK6Ic=');
define('GIFTBIT_PROD_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJTSEEyNTYifQ==.M2pqd2EzUmVaM3NvQklTVnI0TWJrRVBna2tpWXBzQklsNkJjVDhveEZEMHJBN04yZzhUUExOS1BlWlRJeXAxeGRDclFKWW4wblFKRURHYkhVeWdnQ2FaRWxrUjRGSjBJdFkrUHBsdTc2ZVFncWV5M1JjdWtLMUlPdVJpRFk4bFE=.Qso8arGPZUJ7NbsHwlf3WN+FH7WPpJzMXGsvllMszCI=');

$pdo_conn = new mysqli(HOST, dbUser, dbPassword, dbToUse) or die("Could not select database");
$conn = mysqli_connect(HOST,dbUser, dbPassword,dbToUse) or die("Could not select database");
//echo 1;
$sql_dt_conn = array(
    'user' => dbUser,
    'pass' => dbPassword,
    'db'   => dbToUse,
    'host' => HOST
);
function get_result( $Statement ) {
    $RESULT = array();
    $Statement->store_result();
    for ( $i = 0; $i < $Statement->num_rows; $i++ ) {
        $Metadata = $Statement->result_metadata();
        $PARAMS = array();
        while ( $Field = $Metadata->fetch_field() ) {
            $PARAMS[] = &$RESULT[ $i ][ $Field->name ];
        }
        call_user_func_array( array( $Statement, 'bind_result' ), $PARAMS );
        $Statement->fetch();
    }
    return $RESULT;
}


// $final_url   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
// $server_name = $_SERVER['HTTP_HOST'];
// $folder_name = $_SERVER['PHP_SELF'];
// if($server_name == "localhost"){
//     $full_url = $final_url.$server_name.$folder_name;
// }else{
//      $full_url = $final_url.$server_name.$folder_name;
// }
// $url_arr = preg_split("#/#", $full_url); 
// $url_arr_len = count($url_arr);

// if($server_name == "localhost"){
//     if($url_arr_len >= 8 ){
//         $url_arr_len = "../";
//     }else{
//         $url_len = "";
//     }    
// }

//Set Website fee
define("WEBSITE_FEE", 2);  // DB Host name generally localhost


//Local Path
$base_path = "http://localhost/projects-sm/127/src/";

//Server path
//$base_path = "https://pranamthankyou.org/";

?>