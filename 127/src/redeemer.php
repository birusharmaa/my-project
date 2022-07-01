<?php

session_start();
$_SESSION['is_front_page'] = true;
include('server/config.php');



//User type
$user_type = "";
if(isset($_SESSION['user_id'])){

    $_SESSION['message'] = 'Are you sure you want to change password ?';
    if(!empty($_GET['verify']) == 'true'){
        $_SESSION['message'] ='<span class="text-success mt-3 disPlay" style="font-size: .8125rem;">Your password successfully changed</span>';
    }else{
        $_SESSION['message'] = '';
    } 

    $id = $_SESSION['user_id'];
    $query = "SELECT `user_type` FROM `users` WHERE `id` = '".$id."' ";
    $result = mysqli_query($conn,$query);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $user_type = $row['user_type'];
        }
    }
}


if(!empty($user_type)){
    if($user_type == "redeemer"){
        header("location: redeem/redeemcard.php");
    }
    else{
        echo "<h2 class='text-center mt-5 mx-5'>You are already logged in as <b>".ucfirst($user_type)."</b>. You are order to access redeemer account,<br/> please logout and continue.<h2>";
        echo "<h3 class='text-center'><a href='../$user_type'>Home</a><h3>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Redeem - Pranam-ThankYou</title>

    <link rel='shortcut icon' type='image/png' href="images/favicon.png" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/redeem.css">

</head>

<body>
    <!-- Loader -->
    <div id="loading">
        <div id="loading-image">
            <img class="float" src="images/favicon.png" width="100" />
        </div>
    </div>

    <?php include('header.php'); ?>
    <section class="login-section">
        <div class="container p-0 ">
            <div class="row justify-content-center d-md-flex ">
                <div class="col-xl-4 col-md-6 mx-auto">
                    <div class="card mt-5 mb-5 shadow-sm">
                        <div class="card-body ">
                            <div class="auth-form-light text-left p-4">
                                <div class="brand-logo text-center">
                                    <p class="redeem-login-heading">Sign in or register to redeem your gift card
                                    </p>
                                </div>

                                <form class="pt-3 form-inline-md form-horizontal d-md-inline" action="#" id="login-form"
                                    method="post">
                                    <div class="form-group">
                                        <label><b>Username</b></label>
                                        <input type="email" class="form-control form-control-lg pl-0" id="s_email"
                                            name="s_email" placeholder="Username" required="">
                                    </div>
                                    <div class="form-group">
                                        <label><b>Password</b></label>
                                        <input type="password" class="form-control form-control-lg pl-0" id="s_password"
                                            name="s_password" placeholder="Password" required="">
                                        <br><span id="errorlogin" class="text-danger mt-3"></span>
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-success text-uppercase text-white mr-4" id="signin-btn"
                                            type="submit">SIGN IN<i class="fa fa-arrow-circle-o-right"
                                                aria-hidden="true"></i>
                                        </button>
                                        <a href="redeem/registration.php"
                                            class="auth-link text-white float-right"><input type="button"
                                                class="btn btn-success text-uppercase text-white" name="Register"
                                                value="Register">
                                        </a>
                                    </div>
                                    <div class="text-left mt-4">
                                        <a href="redeem/forgot-password" class="btn-redcolor"><u><b>Forgot
                                                    Password</b></u>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Login body end  -->


    <section class=" color-green-bg ">
        <div class="container pb-5">
            <div class="row">
                <div class="col-12 text-sm-left text-center">
                    <h2 class="text-uppercase color-grey-dk nature-spirit" id="howitsworkHead">How it works
                    </h2>
                </div>

            </div>

            <div class="row " id="work-section-row">
                <div class="col-lg-4 col-12 mb-5 text-center">
                    <div class="how-it-works-div m-auto  color-grey-dk-bg">
                        <p class="works_text color-white">Sign In<br> or <br>Register
                        </p>

                    </div>
                </div>
                <div class="col-lg-4 col-12 mb-5 text-center">
                    <div class="how-it-works-div m-auto color-grey-dk-bg">
                        <p class="works_text color-white">Enter <br> <span id="redeemCode">Code</span>
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-12 mb-5 text-center">
                    <div class="how-it-works-div m-auto color-grey-dk-bg">
                        <p class="works_text color-white">Redeem <br> <span id="redeemGift"> Gift</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- how it work section ends -->

    <section class="thank-you-bg" id="redeem-section">
        <div class="container ">
            <div class="row ">
                <!-- <div class="col-sm-6 mt-4 "></div> -->
                <div class="offset-md-6 col-sm-6 my-auto ">
                    <div class="card color-green-bg " id="pranam_thank_you_text">
                        <div class="card-body color-grey-lt text-align-center">
                            <p class="mb-0">Someone said thank you</p>
                            <p>Now redeem</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('footer.php'); ?>

    <!-- Scripts section -->
    <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>

    <script>
    //on page load
    $(document).ready(function() {


        //Login event
        $('#login-form').submit(function(e) {
            $("#loading").show();
            $('.input_error').removeClass('input_error');
            $('.validation').remove();
            $('.success-msg').addClass('d-none');
            $('#preloader').show();
            e.preventDefault(e);
            $('#signin-btn').attr("disabled", "disabled");
            // $("#loading").removeClass('hide');
            $('#response-message').html('Authenticating. Please wait...');
            var my_data = $(this).serializeArray();
            var user_data = {};
            for (var i = 0; i < my_data.length; i++) {
                user_data[my_data[i].name] = my_data[i].value;
            }
            $.post("server/redeemer.php", {
                    operation: 'auth',
                    data: user_data
                },
                function(response) {
                    $("#loading").hide();
                    $('#signin-btn').removeAttr("disabled");
                    response = jQuery.parseJSON(response);
                    //if(response.expiry_date=="expire"){
                    //window.location.href="membership.php";
                    // }
                    if (response.success) {
                        $("#errorlogin").addClass("input_error");
                        $("#errorlogin").parent().append(
                            "<label class='text-success'>Logged in successfully. Redirecting to your account</label>"
                        );
                        $('#login-form')[0].reset();
                        location.href = 'redeem/redeemcard.php';
                    } else {
                        $('#response-message').removeClass('text-danger').removeClass(
                            'text-success').addClass('text-danger').html(response
                            .message);
                        $('#preloader').fadeOut(1000);
                        $("#errorlogin").addClass("input_error");
                        $("#errorlogin").parent().append("<label class='validation text-danger'>*" +
                            response.message + "</label>");
                        validationFlag = false;
                        $('.disPlay').hide();
                    }
                }
            );
        });

        $('.menu-redeem').addClass('active'); //making the current tab in menu active

        $('#loading').hide(); //hide preloader

    }); //document ready ends
    </script>
</body>

</html>