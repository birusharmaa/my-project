<?php
    session_start();
    require_once('../server/config.php');
    if(!isset($_SESSION['user_id'])){      //if not logged in
        header('Location: signin.php');  //Skip to login
        exit();
    }else{
        $_SESSION['paymentStatus'] = "Yes";
    } 

    //User type
    $user_type = "";
    if(isset($_SESSION['user_id'])){
        $id = $_SESSION['user_id'];
        $query = "SELECT `user_type` FROM `users` WHERE `id` = '".$id."' ";
        $result = mysqli_query($conn,$query);
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $user_type = $row['user_type'];
            }
        }
    }

    //Usertype check
    if(!empty($user_type)){
        if($user_type != 'contributor' ){
            header('Location: signin.php');
            exit();
        }
    }  
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contribution History</title>

    <link rel='shortcut icon' type='image/png' href="../images/favicon.png" />
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/datatables/latest/datatables.min.css">
    <link rel="stylesheet" href="../css/datatables/latest/responsive.dataTables.css">
    <link rel="stylesheet" href="../css/styles.css?<?=time()?>">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <!-- <link rel="stylesheet" href="../css/register.css"> -->
    <style type="text/css">
    h5#thankHeader {
        padding-left: 1%;
    }

    input#btnbb {
        margin-top: 86px;
        margin-bottom: 10px;
        /* margin: 0 auto; */
        float: right;
        /* margin-left: 17px; */
        padding: 20px;
        border-radius: 25px;
        margin-right: 30px;
    }

    #btnbb:hover {
        background-image: none;
        background-color: bisque;
    }

    .images {
        width: 100%;
        height: auto;
        cursor: pointer;
    }

    input#quantity {
        background-color: #ffffff;
        width: 50%;
    }

    .div_img {
        margin-bottom: 40px;
        border: 1px solid grey;
        padding: 5px;
    }

    .quantityCls {
        display: flex;
        margin-top: 10px;
        margin-block: 10px;
        margin-left: 12%;
    }

    label.label {
        margin-top: 12px;
    }

    .row,
    .columns_wrap {
        margin-left: 0px;
        margin-right: 0px;
    }

    .text-danger {
        color: #f7051c !important;
    }

    p.text-danger {
        margin-bottom: 0px;
    }

    table thead tr th {
        font-weight: bold;
    }

    .thank-you-name {
        float: left;
        margin-bottom: 0px;
        padding-left: 10px;
    }
    </style>
</head>

<body>

    <?php include('../header.php'); ?>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper">
            <?php require('../sidebar.php'); ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card mb-md-5 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-md-12 table-responsive">
                                        <div class="text-left mb-4">
                                            <b class="page-title">Contributions History</b>
                                        </div>
                                        <table id="contributorHistory" class="table table-bordered display"
                                            class="display responsive nowrap" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Transaction Id</th>
                                                    <th>Name</th>
                                                    <th>Contributor Type</th>
                                                    <th>Contributed Amount</th>
                                                    <th>Donated Amount</th>

                                                    <th>Date Time</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container end -->
    <?php include('../footer.php'); ?>


    <!-- Confirm Modal -->
    <div class="modal" id="pdfMessageModal" tabindex="-1" role="dialog" aria-labelledby="pdfMessageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content text-center" style="background-color: #ecf0f4;">
                <div class="modal-body pt-1">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="close text-right" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-8">
                            <h5 class="font-weight-bold text-left"><b>Message</b></h5>
                        </div>
                        <div class="col-4 modal-logo-column">
                            <img src="../images/new-logo.svg" class="text-right mb-4 mr-3 popup-logo" width="100">
                        </div>
                    </div>
                    <?php
                        if(isset($_SESSION['last_insert_id'])){
                        ?>
                    <div class="row">
                        <span class="main" id="commanbody">Meanwhile your Thank You Cards are downloding, please feel
                            free
                            to leave a message for us.</span>
                        <textarea class="form-control bg-white m-2" id="leaveMsg"
                            placeholder="Enter message"></textarea>
                    </div>

                    <div class="row mt-3">
                        <div class="form-check d-inline">

                            <input type="checkbox" class="form-check-input" value="" id="allow_home">
                            <label class="form-check-label ml-2">Allow my message to appear on
                                Pranam-ThankYou Home Page

                            </label>
                        </div>
                    </div>
                    <?php
                        }else{
                        ?>
                    <span class="main" id="commanbody">Your pdf cards are downloading please wait.</span>
                    <?php
                        }
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn color-green-bg" data-dismiss="modal">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Pdf message modal end -->

    <!-- Message Modal -->
    <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content text-center" style="background-color: #ecf0f4;">

                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="close text-right" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    </div>
                    <div class="row ">
                        <div class="col-md-8">
                            <h5 class="font-weight-bold text-left"><b>Success</b></h5>
                            <p class="main text-left">Your payment has been successful.</p>
                        </div>
                        <div class="col-md-4 modal-logo-column">
                            <img src="../images/new-logo.svg" class="text-right mb-4 mr-3" width="100">

                        </div>
                    </div>

                </div>
                <div class="modal-footer pl-0">
                    <a href="javascript:void(0)" onclick="shareAndEarn()">
                        <p class="float-left mr-5 text-danger">Share and Earn FREE Thank You Cards!</p>
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="checkModalValueFun()">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Message modal end -->

    <!-- Share Modal Response -->
    <div class="modal" id="successMessageModal" tabindex="-1" role="dialog" aria-labelledby="successMessageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content text-center" style="background-color: #ecf0f4;">
                <div class="modal-header">
                    <h5 class="modal-title">Success</h5>
                    <button type="button" style="color: black;" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col">
                            <button type="button" class="close text-right" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    </div>
                    <div class="row ">
                        <div class="col-md-8">
                            <h5 class="font-weight-bold text-left"><b>Success</b></h5>
                            <p class="main text-left shareRefResponse">Your payment has been successful.</p>
                        </div>
                        <div class="col-md-4 modal-logo-column">
                            <img src="../images/new-logo.svg" class="text-right mb-4 mr-3" width="100">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Success message modal end -->
    <?php include('image-download.php'); ?>

    <!-- Scripts section -->
    <script type="text/javascript" src="../js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../js/custom.js"></script>
    <script src="../js/datatables/latest/datatables.min.js"></script>
    <script src="../js/datatables/latest/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="../js/member.js"></script>

    <script>
    //on page load
    $(document).ready(function() {

        $('#loading').hide(); //hide preloader

    }); //document ready ends

    $(document).ready(function() {
        $('#contributorHistory').dataTable().fnDestroy();
        $('#contributorHistory').DataTable({
            //  "fixedHeader": true,
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "../server/contributor.php",
                "type": "POST",
                "data": {
                    "operation": 'get-contributor',
                }
            },
            "columnDefs": [{
                "targets": [0, 2, 6],
                "visible": false,
                "searchable": false
            }],
            "order": [
                [0, "desc"]
            ],
        });
        $('#loading').hide();
    });

    $('#pdfMessageModal').on('hidden.bs.modal', function(e) {
        var message = $("#leaveMsg").val();
        var checkLawfirm = $('#allow_home').is(':checked');
        if (checkLawfirm) {
            var allow_home = "Yes";
        } else {
            var allow_home = "No";
        }
        json_data = {
            "operation": "emptyData",
            "message": message,
            "allow_home": allow_home
        }

        $.ajax({
            type: "POST",
            url: "../server/contributor.php",
            data: json_data,
            success: function(data) {
                location.reload();
            },
            error: function() {}
        });
    });

    // function myProfile() {
    //     $.ajax({
    //         type: "POST",
    //         url: "../server/contributor.php",
    //         data: {
    //             "operation": "viewProfile",
    //             "type": "contributor"
    //         },
    //         success: function(data) {
    //             if (data != "") {
    //                 var parsedData = JSON.parse(data);
    //                 $('#firstNameProfleModal').text(parsedData.first_name);
    //                 $('#fname').val(parsedData.first_name);
    //                 $('#lastNameProfleModal').text(parsedData.last_name);
    //                 $('#emailProfleModal').text(parsedData.email_id);
    //                 $('#phoneProfleModal').text(parsedData.phone);
    //                 $('#usertypeProfleModal').text(parsedData.user_type);
    //                 $('#refCodeModal').text(parsedData.referral_code);
    //                 $('#refUrlModal').html('<a href="https://pranamthankyou.org/?ref=' + parsedData
    //                     .referral_code + '">https://pranamthankyou.org/?ref=' + parsedData
    //                     .referral_code + '</a>');
    //                 var theDate = new Date((parsedData.registered_on) * 1000);
    //                 var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct",
    //                     "Nov", "Dec"
    //                 ];
    //                 var finaldate = theDate.getDate() + '-' + months[theDate.getMonth()] + '-' + theDate
    //                     .getFullYear();
    //                 $('#regisDateProfleModal').text(finaldate);
    //                 var status = parsedData.status;
    //                 if (status == 'A') {
    //                     $('#statusProfleModal').text('Active');
    //                 } else {
    //                     $('#statusProfleModal').text('Inactive');
    //                 }
    //                 // $('#profileModal').modal();
    //             }
    //         }
    //     });
    // }
    </script>

</body>

</html>