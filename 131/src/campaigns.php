<?php
session_start();
require("server/config.php");
if ($_SESSION['usertype'] != "User") {
  header("location:login.php");
  exit();
}
if (isset($_SESSION['selected_ids'])) {
  unset($_SESSION['selected_ids']);
}
$pageName = "campaigns";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Campaigns</title>
  <link rel="shortcut icon" type="image/jpg" href="img/new-favicon.png" />
  <!-- include css file -->
  <?php require("css.php"); ?>
  <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="css/datatables/bootstrap4/datatables.min.css">
  <link rel="stylesheet" href="css/datatables/bootstrap4/responsive.dataTables.css">
  <link rel="stylesheet" href="css/add-camp.css">
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
  <link rel="stylesheet" href="css/jquery.datetimepicker.css">
  <link href="css/style.css" rel="stylesheet">
</head>

<body id="page-top">

  <div id="loading">
    <div id="loading-image">
      <img class="float" src="img/favicon.png" width="100" alt="Loading..." /><br />
      <img src="img/loading.gif" width="70" />
    </div>
  </div>

  <input type="hidden" id="pageName" value="<?= $pageName; ?>" />

  <div id="wrapper">
    <?php require("sidebar.php"); ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php require("topbar.php"); ?>
        <?php require("header.php"); ?>
        <div class="container-fluid pt-3 px-4">
          <div class="row">
            <div class="col-12">
              <h2 class="font-weight-bold mb-0">Campaigns</h2>
              <h6 class="sub-page-heading mt-0 text-muted mb-4 small">Add new campaigns!</h6>
            </div>
          </div>

          <div class="card shadow mb-4">
            <div class="card-body my-4">
              <div class="table-responsive">
                <table class="table table-bordered" id="customersDataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Campaign Name</th>
                      <th>Created Date</th>
                      <th>Sent On/ Scheduled On</th>
                      <th>Remark</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </div><!-- End of Main Content -->
      <?php require("footer.php"); ?>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content text-center">
        <div class="modal-header">
          <h5 class="modal-title" id="commantitle">Delete Customer?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="delCustomerId" value="">
          Do you really want to delete the customer?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="deleteCustomer">Yes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div class="modal fade" id="confirmPlanModal" tabindex="-1" role="dialog" aria-labelledby="confirmPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content text-center">
        <div class="modal-header">
          <h5 class="modal-title" id="commantitle">Confirmation ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="planUserIdConf" value="">
          <input type="hidden" id="updateplanId" value="">
          Are you sure you want to update the plan of <span class="font-weight-bold" id="planUserNameConf"></span> (<span class="font-weight-bold" id="planUserEmailConf"></span>) to
          <span class="font-weight-bold" id="planPlanConf"></span>(<span class="font-weight-bold" id="planUserSubTypeConf"></span>) ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-bg text-white" id="updatePlanConf">Yes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Archieved Modal -->
  <div class="modal fade" id="archievedModal" tabindex="-1" role="dialog" aria-labelledby="archievedModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content text-center">
        <div class="modal-header">
          <h5 class="modal-title" id="commantitle">Archived ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="archiveId" value="">
          Do you really want to archive this campaign ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-bg text-white" id="archiveYes">Yes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Archieved Modal -->
  <div class="modal fade" id="deleteArchiveModal" tabindex="-1" role="dialog" aria-labelledby="deleteArchiveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content text-center">
        <div class="modal-header">
          <h5 class="modal-title" id="commantitle">Delete Archived ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="deleteArchiveID" value="">
          Are you sure that you want to delete this campaign?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-bg text-white" id="deleteArchive">Yes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Campaign Modal -->
  <div class="modal fade" id="addCampaignModal" tabindex="-1" role="dialog" aria-labelledby="addCamaignModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg rounded">
      <div class="modal-content addCamaignModal">
        <div class="modal-header pb-0">
          <h3 class="modal-title text-center font-weight-bold w-100" id="commantitle">Add new campaign</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body mb-4">
          <!-- Step 1 Section -->
          <section id="select-users" class="">
            <div class="container m-auto">
              <div class="row" id="customers-data">
                <div class="col-12 text-center">
                  <h2>Customers</h2>
                  <label><input type='checkbox' id="showOldClients" onclick='showOldClientsCheckbox(this)'> Show Old Customers</label>
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered" id="campaignDatatable" width="100%" cellspacing="0">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>
                          <input type="checkbox" id="allCheckbox" onclick="allCheckbox(this)" />
                          <input type="checkbox" id="oldAllCheckbox" onclick="oldAllCheckbox()" style="display: none;" />
                        </th>
                        <th>Customer Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-md-12 text-center">
                  <div id="error-select-id"></div>
                  <button class="btn btn-bg mt-2 text-white text-white px-5" data-dismiss="modal" aria-label="Close">Close <i class="fa fa-times" aria-hidden="true"></i></button>
                  <button class="btn btn-bg mt-2 text-white text-white px-5" id="addCompaign"><span class="font-weight-bold mr-2">Next </span> <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                </div>
              </div>
            </div>
          </section>
          <!-- Step 1 Section End -->

          <!-- Step 2 Section -->
          <section id="select-users-step2" class="d-none">
            <div class="container">
              <div class="row customRow mt-2">
                <div class="col-md-12">
                  <label for="campName" class="font-weight-bold ">Campaign Name*</label>
                  <input type="text" id="campName" placeholder="Enter campaign name" class="form-control customField" />
                </div>

                <!-- <div class="col-md-12 mt-4 d-none" id="col4Lebel">
                                    <label for="location" class="font-weight-bold ">Location*</label>
                                    <select class="customField form-control" id="location">
                                       <option value="">--Select--</option> 
                                    </select>
                                </div> -->

                <div class="col-md-12 mt-4">
                  <div class="">
                    <label for="message" class=" font-weight-bold">Message Type</label>
                  </div>
                  <div class="row">
                    <div class="col-md-7">
                      <label class="form-check-label pb-2" for="email">
                        <input class="mb-1" type="radio" id="" name="message" data-type="email" value="Email" checked /> Email
                      </label>
                      &nbsp&nbsp
                      <label class="form-check-label" for="sms">
                        <input class="custom_type sms mb-1" type="radio" id="sms" name="message" data-type="sms" value="Phone" />
                        SMS
                      </label>
                      &nbsp&nbsp
                      <label class="form-check-label" for="both">
                        <input class="custom_type both mb-1" type="radio" id="both" name="message" data-type="both" value="Both" />
                        Both
                      </label>
                    </div>
                  </div>
                </div>

                <div class="col-md-8 col-12">
                  <div class="row">
                    <div class="col-md-12 messageSection" id='smsSection'>
                      <div class="mt-2">
                        <label for="message" class=" font-weight-bold">Sample Messages</label>
                      </div>

                      <div class="col-md-12 col-12 pl-0" id="customSmsSection">
                        <p id="smsMessage" class="small"></p>
                      </div>
                    </div>

                    <div class=" d-none col-md-12 messageSection" id='emailSection'>
                      <h5 class="text-center py-1">Select Message</h5>

                      <div class="col-md-12 col-12" id="customEmailSection">
                        <p id="emailMessage"></p>
                      </div>
                    </div>
                    <div class="col-md-12 col-12">
                      <p id="smsMessage" class="small"></p>
                      <p id="smsMessageWithoutLen" class="small d-none"></p>
                    </div>
                  </div>
                </div>

                <div class="col-md-12 mt-4" id="">
                  <label for="remarks" class="font-weight-bold ">Remarks</label>
                  <textarea class="customField form-control" placeholder="Remarks" rows="2" id="remark"></textarea>
                  <!-- <option value="">--Select--</option> -->

                </div>

                <div class="col-md-12 col-12 my-3 text-center">
                  <button class="btn btn-bg mt-2 text-white text-white px-5" id="step-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button>
                  <button class="btn btn-bg mt-2 text-white font-weight-bold stepTwoNext px-5" id="stepTwoNext">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                </div>
              </div>
            </div>
          </section>
          <!-- Step 2 Section End -->

          <section id="select-users-step3" class="d-none mb-5">
            <div class="container m-auto mb-4">
              <!-- <div class="row customRow messageSection mt-0"> -->
              <div class="data col-12 col-md-8 offset-md-2">
                <h4>When to send</h4>

                <div class="form-check p-0 mb-2">
                  <label class="form-check-label" for="now">
                    <input type="radio" id="now" name="exeCompaign" value="now" checked /> Now
                  </label>
                  <a href="#" data-toggle="tooltip" title="Campaign will be executed within 10 minutes!">
                    <i class="fa fa-question-circle small" aria-hidden="true"></i>
                  </a>
                </div>
                <div class="form-check p-0 mb-2">
                  <label class="form-check-label" for="radio2">
                    <input class="date" type="radio" id="redio2" name="exeCompaign" value="later" />
                    At &nbsp &nbsp
                    <a href="#" data-toggle="tooltip" title="Campaign will be executed within 10 minutes!">
                      <!-- <i class="fa fa-question-circle small" aria-hidden="true"></i> -->
                    </a>&nbsp &nbsp
                    <input id="datetimepicker" type="datetime" class="datepickerIcon " autocomplete="off">
                  </label>
                </div>

                <div class="form-check p-0 mb-2">
                  <label class="form-check-label" for="save">
                    <input type="radio" id="save" name="exeCompaign" value="save">
                    Save as Draft
                  </label>
                </div>

                <div class="form-check-inline">
                  <label class="form-check-label">
                    <input type="checkbox" id="remember" name="execompRemend" value="yes">
                    Remind customers who haven't clicked on feedback link after one week.
                  </label>
                </div>
                <div class="">
                  <p class="" id="remaingMsgLimit"></p>
                </div>
                <button class="btn btn-bg mt-2 text-white px-3" id="step-back-two"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button>
                <button class="btn btn-bg mt-2 px-3 text-white font-weight-bold" id="executeCompaign">Create campaign</button>
              </div>
              <!-- </div>  -->
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Campaign Modal -->
  <div class="modal fade" id="editCampaignModal" tabindex="-1" role="dialog" aria-labelledby="editCampaignModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg rounded">
      <div class="modal-content addCamaignModal">
        <div class="modal-header pb-0">
          <h3 class="modal-title text-center font-weight-bold w-100" id="commantitle">Edit campaign</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i class="fa fa-times" aria-hidden="true"></i>
          </button>
        </div>
        <div class="modal-body mb-4">
          <input type="hidden" id="editCampId" value="" />
          <!-- Step 1 Section -->
          <section id="edit-select-users" class="">
            <div class="container m-auto">
              <div class="row" id="customers-data">
                <div class="col-12 text-center">
                  <h2>Campaigns</h2>
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered" id="editCampaignDatatable" width="100%" cellspacing="0">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>
                          <input type="checkbox" id="editAllCheckbox" onclick="editAllCheckbox()" />
                        </th>
                        <th>Customer Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                  </table>
                </div>
                <div class="col-12 text-center">
                  <div class="campaign-selection-err"></div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-md-12 text-center">
                  <div id="edit-error-select-id"></div>
                  <button class="btn btn-bg mt-2 text-white text-white px-5" data-dismiss="modal" aria-label="Close">Close <i class="fa fa-times" aria-hidden="true"></i></button>
                  <button class="btn btn-bg mt-2 text-white text-white px-5" id="editCampaign"><span class="font-weight-bold mr-2">Next </span> <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                </div>
              </div>
            </div>
          </section>
          <!-- Step 1 Section End -->

          <!-- Step 2 Section -->
          <section id="edit-select-users-step2" class="d-none">
            <div class="container">
              <div class="row customRow mt-2">
                <div class="col-md-12">
                  <label for="editCampName" class="font-weight-bold ">Campaign Name*</label>
                  <input type="text" id="editCampName" placeholder="Enter campaign name" class="form-control customField" />
                </div>

                <div class="col-md-12 mt-4 d-none" id="editCol4Lebel">
                  <label for="editLocation" class="font-weight-bold ">Location*</label>
                  <select class="customField form-control" id="editLocation">
                    <!-- <option value="">--Select--</option> -->
                  </select>
                </div>

                <div class="col-md-12 mt-4">
                  <div class="">
                    <label for="message" class=" font-weight-bold">Message Type</label>
                  </div>
                  <div class="row">
                    <div class="col-md-7">
                      <label class="form-check-label" for="editBoth">
                        <input class="editCustomtype both mb-1" type="radio" id="editBoth" name="editMessage" data-type="both" value="Both" checked /> Both
                      </label>
                      &nbsp
                      <label class="form-check-label" for="editSms">
                        <input class="editCustomtype sms mb-1" type="radio" id="editSms" name="editMessage" data-type="sms" value="Phone">
                        SMS
                      </label>
                      &nbsp
                      <label class="form-check-label" for="editEmail">
                        <input class="mb-1" type="radio" id="" name="editMessage" data-type="email" value="Email" /> Email
                      </label>
                    </div>
                  </div>
                </div>

                <div class="col-md-8 col-12">
                  <div class="row">
                    <div class="col-md-12 messageSection" id='editSmsSection'>
                      <div class="mt-2">
                        <label for="message" class="font-weight-bold">Sample Messages</label>
                      </div>

                      <div class="col-md-12 col-12 pl-0" id="editCustomSmsSection">

                      </div>
                      <div class="col-md-12 col-12 pl-0">
                        <p id="editSmsMessage" class="small"></p>
                        <p id="editSmsMessageWithoutLen" class="small d-none text-warning"></p>
                      </div>
                    </div>

                  </div>
                </div>

                <div class="col-md-12 mt-4" id="">
                  <label for="editRemark" class="font-weight-bold ">Remarks</label>
                  <textarea class="customField form-control" placeholder="Remarks" rows="2" id="editRemark"></textarea>
                </div>

                <div class="col-md-12 col-12 my-3 text-center">
                  <button class="btn btn-bg mt-2 text-white text-white px-5" id="edit-step-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button>
                  <button class="btn btn-bg mt-2 text-white font-weight-bold stepTwoNext px-5" id="editStepTwoNext">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                </div>
              </div>
            </div>
          </section>
          <!-- Step 2 Section End -->

          <section id="edit-select-users-step3" class="d-none mb-5">
            <div class="container m-auto mb-4">
              <!-- <div class="row customRow messageSection mt-0"> -->
              <div class="data col-12 col-md-8 offset-md-2">
                <h4>When to send</h4>

                <div class="form-check p-0 pb-2">
                  <label class="form-check-label" for="editNow">
                    <input type="radio" id="editNow" name="editExeCompaign" value="now" checked />
                    Now
                  </label>
                  <a href="#" data-toggle="tooltip" title="Campaign will be executed within 10 minutes!">
                    <i class="fa fa-question-circle small" aria-hidden="true"></i>
                  </a>
                </div>


                <div class="form-check p-0 pb-2">
                  <label class="form-check-label" for="radio2">
                    <input class="date" type="radio" id="redio2" name="editExeCompaign" value="later" />
                    At &nbsp &nbsp
                    <a href="#" data-toggle="tooltip" title="Campaign will be executed within 10 minutes!">
                      <i class="fa fa-question-circle small" aria-hidden="true"></i>
                    </a>
                    &nbsp &nbsp
                    <input id="editdatetimepicker" type="datetime" class="datepickerIcon " autocomplete="off">
                  </label>

                </div>

                <div class="form-check p-0 mb-2">
                  <label class="form-check-label" for="editSave">
                    <input type="radio" id="editSave" name="editExeCompaign" value="save">
                    Save as Draft
                  </label>
                </div>

                <div class="form-check-inline">
                  <label class="form-check-label">
                    <input type="checkbox" id="editRemeber" name="editCampRemend" value="yes">
                    Remind customers who haven't clicked on feedback link after one week.
                  </label>
                </div>
                <div class="">
                  <p class="" id="editRemaingMsgLimit"></p>
                </div>
                <button class="btn btn-bg mt-2 text-white px-3" id="edit-step-back-two"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button>
                <button class="btn btn-bg mt-2 px-3 text-white font-weight-bold" id="updateCampaign">Update Campaign</button>
              </div>
              <!-- </div>  -->
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>

  <!-- View Campaign -->
  <div class="modal fade" id="viewCampaignModal" tabindex="-1" role="dialog" aria-labelledby="viewCamaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg rounded">
      <div class="modal-content addCamaignModal">
        <div class="modal-header pb-0">
          <h3 class="modal-title text-center font-weight-bold w-100" id="commantitle">View campaign</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i class="fa fa-times" aria-hidden="true"></i>
          </button>
        </div>
        <div class="modal-body mb-4">
          <input type="hidden" id="viewCampId" value="" />
          <!-- Step 1 Section -->
          <div class="container m-auto">

            <div class="row mt-2">
              <div class="col-12">
                <h4>Campaign Information</h4>
              </div>
              <div class="customCol col-md-6 col-12 mt-2">
                <label for="viewCampName" class="font-weight-bold">Campaign Name </label>
                <span class="form-control customField" id="viewCampName"></span>
              </div>

              <div class="customCol col-md-6 col-12 mt-2">
                <label for="viewLocation" class="font-weight-bold">Location</label>
                <input type="text" class="form-control customField bg-white" id="viewLocation" readonly>
              </div>

              <div class="customCol col-md-6 col-12 mt-4">
                <label for="viewAddedOn" id="viewAddedOnText" class="font-weight-bold">Added on</label>
                <span class="form-control customField" id="viewAddedOn"></span>
              </div>

              <div class="customCol d-none col-md-6 col-12 mt-4">
                <label for="viewMode" class="font-weight-bold">Mode</label>
                <span class="form-control customField" id="viewMode"></span>
              </div>

              <div class="customCol col-md-6 col-12 mt-4">
                <label for="viewMode" class="font-weight-bold">Status</label>
                <!-- <input type="text" class="form-control customField" id="viewMode" placeholder="Enter email"> -->
                <span class="form-control customField" id="viewStatus"></span>
              </div>

              <div class="scheduled col-md-6 col-12 mt-4">
                <label for="viewMode" class="font-weight-bold">Scheduled</label>
                <span id="scheduled" class="form-control customField"></span>
              </div>

              <div class="col-md-6 col-12 mt-4">
                <label for="viewRemark" class="font-weight-bold">Remark</label>
                <span id="viewRemark" class="form-control customField"></span>
              </div>

            </div>

            <div class="row mt-5" id="customers-data">
              <h4>Message Information</h4>
              <div class="table-responsive">
                <table class="table table-bordered" id="viewCampaignDatatable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th>Id</th>
                      <th>Customer Name</th>
                      <th>Email Number</th>
                      <th>Mobile</th>
                      <th>Sent Via</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
            <div class="row mt-4">
              <div class="col-md-12 d-flex justify-content-center">
                <button class="btn btn-bg text-white text-white px-5" data-dismiss="modal" aria-label="Close">OK</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- //Link validation check -->
  <div class="modal fade" id="linkValidationModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="linkValidationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold" id="exampleModalLabel">Warning</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <span class="text-center" id="linkValidation"></span>
          <input type="hidden" id="linkSaveType" value="" />
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <button class="btn btn-bg text-white" type="button" id="withOutLinkNext">Continue Anyway</button>
        </div>
      </div>
    </div>
  </div>

  <!-- //Edit Link validation check -->
  <div class="modal fade" id="editLinkValidationModal" tabindex="-1" role="dialog" aria-labelledby="editLinkValidationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Header</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <span class="text-center" id="editLinkInfo"></span>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <button class="btn btn-bg text-white" type="button" id="editWithOutLinkNext">Continue
              Anyway</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" id="chechboxSelectId" value="" />

  <!-- Step 2 -->
  <input type="hidden" id="plan_id" value="" />
  <input type="hidden" id="modeNew" value="" />
  <input type="hidden" id="locationId" value="" />
  <input type="hidden" id="messageType" value="" />
  <input type="hidden" id="customSmsMsgInfo" value="" />
  <input type="hidden" id="campaignName" value="" />
  <input type="hidden" id="remarkValue" value="" />

  <!-- Edit Campaign section -->
  <input type="hidden" value="" id="campId" />
  <input type="hidden" id="editChechboxSelectId" value="" />
  <input type="hidden" id="editPlan_id" value="" />
  <input type="hidden" id="editmodeNew" value="" />
  <input type="hidden" id="editLocationId" value="" />
  <input type="hidden" id="editMessageType" value="" />
  <input type="hidden" id="editCustomSmsMsgInfo" value="" />
  <input type="hidden" id="editCampaignName" value="" />
  <input type="hidden" id="editRemarkValue" value="" />

  <input type="hidden" id="brand_name" value="<Business>" />
  <input type="hidden" id="link_dummy" value="<Link>" />

  <?php require("js.php"); ?>
  <script src="js/page/comman.js"></script>
  <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
  <script type="text/javascript" src="js/jquery.datetimepicker.js"></script>
  <script src="js/jquery.datetimepicker.full.min.js"></script>
  <script type="text/javascript" src="js/page/check-plan.js"></script>
  <script src="js/datatables/boostrap/datatables.min.js"></script>
  <script src="js/datatables/boostrap/dataTables.responsive.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBYcQZaaPQC7XwA7OUvinj0YJ_N1o_dXN4&libraries=places">
  </script>

  <script type="text/javascript">
    $('#succesmodal').click(function() {
      location.reload();
    });

    $('#errormodal').click(function() {
      $('#errorMsgModal').modal('hide');
      location.reload();
    });

    $('#location').change(function() {
      var locations = window.locations;
      $.each(locations, function(i, location) {
        var l_id = $('#location').val();
        if (location.id == l_id) {
          var old_name = $("#brand_name").val();
          var old_link = $("#link_dummy").val();
          $("#brand_name").val(location.name);
          $("#link_dummy").val(location.link);
          var custom_message = $("#custom_sms_message").val();
          //custom_message = custom_message.replace(old_name, location.name);
          //custom_message = custom_message.replace("<Business>", location.name);
          //custom_message = custom_message.replace(old_link, location.link);
          //custom_message = custom_message.replace("<Link>", location.link);
          $("#custom_sms_message").val(custom_message);
          var msgs = $('.msgs');
          $.each(msgs, function(i, m) {
            var custom_message = $(m).text();
            // custom_message = custom_message.replace(old_name, location.name);
            // custom_message = custom_message.replace("<Business>", location.name);
            // custom_message = custom_message.replace(old_link, location.link);
            // custom_message = custom_message.replace("<Link>", location.link);
            //$(m).text(custom_message);
          });
        }
      });
      characherRemaining();
    });

    $('#editLocation').change(function() {
      var locations = window.locations;
      $.each(locations, function(i, location) {
        var l_id = $('#editLocation').val();
        if (location.id == l_id) {
          var old_name = $("#brand_name").val();
          var old_link = $("#link_dummy").val();
          $("#brand_name").val(location.name);
          $("#link_dummy").val(location.link);
          var custom_message = $("#editCustomSmsMessage").val();
          custom_message = custom_message.replace(old_name, location.name);
          custom_message = custom_message.replace(old_link, location.link);
          $("#editCustomSmsMessage").val(custom_message);

          var msgs = $('.edit_msgs');
          $.each(msgs, function(i, m) {
            var custom_message = $(m).text();
            custom_message = custom_message.replace(old_name, location.name);
            custom_message = custom_message.replace("<Business>", location.name);
            custom_message = custom_message.replace(old_link, location.link);
            custom_message = custom_message.replace("<Link>", location.link);
            $(m).text(custom_message);
          });
        }
      });
      editCustomMessage();
    });

    //Jquey date
    jQuery('#datetimepicker').datetimepicker();
    var checkPastTime = function(inputDateTime) {
      if (typeof(inputDateTime) != "undefined" && inputDateTime !== null) {
        var current = new Date();
        //check past year and month
        if (inputDateTime.getFullYear() < current.getFullYear()) {
          $('#datetimepicker').datetimepicker('reset');
          //alert("Sorry! Past date time not allow.");
        } else if ((inputDateTime.getFullYear() == current.getFullYear()) && (inputDateTime.getMonth() <
            current.getMonth())) {
          $('#datetimepicker').datetimepicker('reset');
          //alert("Sorry! Past date time not allow.");
        }
        // check input date equal to todate date
        if (inputDateTime.getDate() == current.getDate()) {
          if (inputDateTime.getHours() < current.getHours()) {
            $('#datetimepicker').datetimepicker('reset');
          }
          this.setOptions({
            minTime: moment() //here pass current time hour
          });
        } else {
          this.setOptions({
            minTime: false
          });
        }
      }
    };

    var currentYear = new Date();
    $('#datetimepicker').datetimepicker({
      format: 'Y-m-d H:i',
      minDate: moment(),
      yearStart: currentYear.getFullYear(), // Start value for current Year selector
      onChangeDateTime: checkPastTime,
      onShow: checkPastTime
    });

    jQuery('#editdatetimepicker').datetimepicker();
    var checkPastTime2 = function(inputDateTime) {
      if (typeof(inputDateTime) != "undefined" && inputDateTime !== null) {
        var current = new Date();
        //check past year and month
        if (inputDateTime.getFullYear() < current.getFullYear()) {
          $('#editdatetimepicker').datetimepicker('reset');
          //alert("Sorry! Past date time not allow.");
        } else if ((inputDateTime.getFullYear() == current.getFullYear()) && (inputDateTime.getMonth() <
            current.getMonth())) {
          $('#editdatetimepicker').datetimepicker('reset');
          //alert("Sorry! Past date time not allow.");
        }
        // check input date equal to todate date
        if (inputDateTime.getDate() == current.getDate()) {
          if (inputDateTime.getHours() < current.getHours()) {
            $('#editdatetimepicker').datetimepicker('reset');
          }
          this.setOptions({
            minTime: moment() //here pass current time hour
          });
        } else {
          this.setOptions({
            minTime: false
          });
        }
      }
    };

    var currentYear = new Date();
    $('#editdatetimepicker').datetimepicker({
      format: 'Y-m-d H:i',
      minDate: 0,
      yearStart: currentYear.getFullYear(), // Start value for current Year selector
      onChangeDateTime: checkPastTime2,
      onShow: checkPastTime2
    });

    $(function() {
      $('input[name="daterange2"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
          cancelLabel: 'Clear'
        }
      });
      $('input[name="daterange2"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format(
          'MM/DD/YYYY'));
      });

      $('input[name="daterange2"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
      });
    });

    //Date picker
    $(function() {
      $('input[name="daterange"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
          cancelLabel: 'Clear'
        }
      });
      $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format(
          'MM/DD/YYYY'));
      });

      $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
      });
    });

    //Making sidebar menu active
    $('.menu-' + $('#pageName').val()).addClass('active');

    //Call the dataTables jQuery plugin
    $(document).ready(function() {
      // Load Locations
      $('[data-toggle="tooltip"]').tooltip();

      $('#loading').show();
      var json_data = {};
      var headers = {
        'X-Authorization': 'authy'
      };
      $.ajax({
        "type": 'GET',
        "url": 'api/v1/location',
        "processData": true,
        "crossDomain": true,
        "xhrFields": {
          "withCredentials": false
        },
        "dataType": 'json',
        "data": json_data,
        "headers": headers,
        "timeout": 60000,
        success: function(res, general_msg) {
          window.locations = res.result.locations;
        },
        error: function(data, general_error) {
          $('#loading').hide();
        }
      }) //Inner Ajax

      //Customer Databales.
      $('#customersDataTable').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
		"searching": false,
        "paging": true,
        "ajax": function(data, callback, settings) {
          $.ajax({
            "type": 'GET',
            "url": 'api/v1/campaign',
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
              callback(response.result);
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }) //Inner Ajax
        },
        "columnDefs": [{
          "targets": [0],
          "visible": false,
          "searchable": false,
        }],
        "order": [
          [0, "desc"]
        ],
        "oLanguage": {
          "sSearch": ""
        },
        "oSearch": {
          "sSearch": ""
        },
        "language": {
          "searchPlaceholder": 'Search',
          "infoFiltered": ""
        },
        initComplete: function() {
          $(this.api().table().container()).find('input').parent().wrap('<form>').parent().attr('autocomplete', 'off');
        },

        "dom": '<"row"<"col-sm-4 pull-left"f><"col-sm-4"> <"col-sm-4 pull-right"<"leftbar">>> <"row" <"col-md-12 jqueryDataTable px-0"t><"col-md-7 px-0"i><"col-md-5"p>>',
      }); //Datatable
      $("div.leftbar").html(
        '<a href="javascript:void(0)" class="float-right"><button class="btn btn-bg border-info font-weight-bold text-white" id="add-camp-btn" onclick="addCompaignFunc()"><i class="fa fa-plus pr-1" aria-hidden="true"></i>Add Campaign</button></a>'
      );
      $('#loading').hide();
      $("#customersDataTable_filter input").autocomplete({
        disabled: true
      });
    }); //Document Ready

    //Is archive Campaing Function
    function archiveCampaign(id) {
      $("#archiveId").val(id);
      $("#archievedModal").modal();
    }

    //isarchive is Yes clicked
    $("#archiveYes").click(function() {
      var id = "";

      id = $("#archiveId").val();
      //Id campaign id is exists
      if (id) {
        $('#loading').show();
        $("#archievedModal").modal("hide");
        var json_data = {
          "operation": "Isarchive"
        };
        var headers = {
          'X-Authorization': 'authy'
        };
        $.ajax({
          type: 'PUT',
          url: 'api/v1/campaign/' + id,
          processData: true,
          crossDomain: true,
          xhrFields: {
            withCredentials: false
          },
          dataType: 'json',
          data: json_data,
          headers: headers,
          timeout: 60000,
          success: function(response, general_msg) {
            $('#customersDataTable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
            $('#successMsgMoadl').text(response.result.message);
            $('#MsgModal').modal('show');
          },
          error: function(data, general_error) {
            $('#loading').hide();
            //$("#updateCustomer").prop("disabled", false);
            $('#errorMsgbody').text(data.responseJSON.info.message);
            $('#errorMsgModal').modal('show');
          }
        }); //Ajax function
      }
    });

    //deleteCampaign function
    function deleteCampaign(id) {
      $("#deleteArchiveID").val(id);
      $("#deleteArchiveModal").modal();
    }

    //deleteArchive clicked
    $("#deleteArchive").click(function() {
      var id = "";
      id = $("#deleteArchiveID").val();
      //Id campaign id is exists
      if (id) {
        $('#loading').show();
        $("#deleteArchiveModal").modal("hide");
        var json_data = {};
        var headers = {
          'X-Authorization': 'authy'
        };
        $.ajax({
          type: 'DELETE',
          url: 'api/v1/campaign/' + id,
          processData: true,
          crossDomain: true,
          xhrFields: {
            withCredentials: false
          },
          dataType: 'json',
          data: json_data,
          headers: headers,
          timeout: 60000,
          success: function(response, general_msg) {
            $('#customersDataTable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
            $('#successMsgMoadl').text(response.result.message);
            $('#MsgModal').modal('show');
          },
          error: function(data, general_error) {
            $('#loading').hide();
            $('#errorMsgbody').text(data.responseJSON.info.message);
            $('#errorMsgModal').modal('show');
          }
        }); //Ajax function
      }
    });

    //Add Campaign Function
    function addCompaignFunc() {
      $('.selectCheckBox').each(function() {
        selected_ids.pop($(this).data("id"));
        $(this).prop("checked", false).closest("tr").removeClass("selected");
        $("#chechboxSelectId").val(selected_ids);
        allSelected = "No";
      });
      $("#addCampaignModal").modal();
    }

    var allSelected = "";
    if ($("#allCheckbox").prop("checked") == true) {
      allSelected = "Yes";
    } else {
      allSelected = "No";
    }

    var oldClientAllSelect = "";
    if ($("#showOldClients").prop("checked") == true) {
      oldClientAllSelect = "Yes";
    } else {
      oldClientAllSelect = "No";
    }

    var selected_ids = []; //Selected Id
    $(document).ready(function() {
      clientsDatatables();
      //$("div.leftbarNew").html('<a href="javascript:void(0)" class="float-right"><i class="fa fa-plus" aria-hidden="true"></i></a>');
      $("#campaignDatatable").on('click', ".selectCheckBox", function() {
        var headers = {
          'X-Authorization': 'authy'
        };
        if ($(this).is(":checked")) {
          selected_ids.push($(this).data("id"));
          $(this).closest("tr").addClass("selected");
          $('#loading').show();
          var data = {
            "operation": "select",
            "id": $(this).data("id")
          }
          $.ajax({
            "type": 'POST',
            "url": 'api/v1/addcampaign/',
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }); //Inner Ajax
        } //If
        else {
          selected_ids.pop($(this).data("id"));
          $(this).closest("tr").removeClass("selected");
          allSelected = "No";
          $('#loading').hide();
          var data = {
            "operation": "un_select",
            "id": $(this).data("id")
          }
          $.ajax({
            "type": 'POST',
            "url": 'api/v1/addcampaign/',
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }); //Inner Ajax
          $("#allCheckbox").prop("checked", false);
        } //else
        $("#chechboxSelectId").val(selected_ids);
      }); //Checkbox click end         
      $("#loading").hide();
      //clientsDatatables();
    }); //Document Ready

    //New client select all check box is ture
    function allCheckbox(e) {

      var table = $('#campaignDatatable').DataTable();
      var info = table.page.info();
      var pageInfo = info.page + 1;
      var pageStart = info.start;
      //pageStart     = pageStart+1;
      var pageEnd = info.end;

      // console.log(pageStart);
      // console.log(pageEnd);

      if ($(e).prop("checked") == true) {
        var headers = {
          'X-Authorization': 'authy'
        };
        $('#loading').show();
        $("#chechboxSelectId").val(selected_ids);
        allSelected = "Yes";
        var data = {
          "operation": "select_all",
          "pageStart": pageStart,
          "pageEnd": pageEnd
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/addcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#campaignDatatable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        }); //Inner Ajax
      } else {
        $('.selectCheckBox').each(function() {
          selected_ids.pop($(this).data("id"));
          $(this).prop("checked", false);
          $(this).prop("checked", false).closest("tr").removeClass("selected");
          $("#chechboxSelectId").val(selected_ids);
          allSelected = "No";
        });

        var headers = {
          'X-Authorization': 'authy'
        };
        $('#loading').show();
        allSelected = "No";
        var data = {
          "operation": "un_select_all",
          "pageStart": pageStart,
          "pageEnd": pageEnd
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/addcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#campaignDatatable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        }); //Inner Ajax
      }
    }

    //Old client select all check box is true
    function oldAllCheckbox() {
      var table = $('#campaignDatatable').DataTable();
      var info = table.page.info();
      var pageInfo = info.page + 1;
      var pageStart = info.start;
      //pageStart     = pageStart+1;
      var pageEnd = info.end;
      if ($("#oldAllCheckbox").prop("checked") == true) {
        var headers = {
          'X-Authorization': 'authy'
        };
        $('#loading').show();
        $("#chechboxSelectId").val(selected_ids);
        var data = {
          "operation": "select_all_old",
          "pageStart": pageStart,
          "pageEnd": pageEnd
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/addcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#campaignDatatable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        }); //Inner Ajax
      } else {
        $('.oldSelectCheckBox').each(function() {
          selected_ids.pop($(this).data("id"));
          $(this).prop("checked", false);
          $(this).prop("checked", false).closest("tr").removeClass("selected");
          $("#chechboxSelectId").val(selected_ids);
        });
        var oldClientAllSelect = "No";
        var json_data = {
          "operation": "destroy_Session",
          "pageStart": pageStart,
          "pageEnd": pageEnd
        };
        var headers = {
          'X-Authorization': 'authy'
        };
        $.ajax({
          type: 'POST',
          url: 'api/v1/showallclients',
          processData: true,
          crossDomain: true,
          xhrFields: {
            withCredentials: false
          },
          dataType: 'json',
          data: json_data,
          headers: headers,
          timeout: 60000,
          success: function(response, general_msg) {
            if (response.status == "OK") {
              clientsDatatables();
            }
          },
          error: function(data, general_error) {}
        });
        $("#oldAllCheckbox").prop("checked", false);
      }
    }

    //Check select id or select old old clients 
    $("#campaignDatatable").on('click', ".oldSelectCheckBox", function() {
      var headers = {
        'X-Authorization': 'authy'
      };
      if ($(this).is(":checked")) {
        selected_ids.push($(this).data("id"));
        $(this).closest("tr").addClass("selected");
        $('#loading').show();
        var data = {
          "operation": "old_select",
          "id": $(this).data("id")
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/addcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        }); //Inner Ajax
      } //If
      else {
        selected_ids.pop($(this).data("id"));
        $(this).closest("tr").removeClass("selected");
        allSelected = "No";
        $('#loading').hide();
        var data = {
          "operation": "old_un_select",
          "id": $(this).data("id")
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/addcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        }); //Inner Ajax
        $("#oldAllCheckbox").prop("checked", false);
      } //else
      $("#chechboxSelectId").val(selected_ids);
    }); //Checkbox click end       

    $("#addCompaign").click(function() {
      $('.input_error').removeClass('input_error');
      $('.validation').remove();
      $('#smsSection').show();
      var flag = true;
      var selected_ids = $("#chechboxSelectId").val(); //Select id
      var checked = false;
      $('.selectCheckBox').each(function() {
        if ($(this).is(":checked")) {
          checked = true;
        }
      });
      $('.oldSelectCheckBox').each(function() {
        if ($(this).is(":checked")) {
          checked = true;
        }
      });

      if (!checked) {
        $("#error-select-id").addClass("input_error");
        $("#error-select-id").parent().append(
          "<div class='validation'><span class='validation text-danger'>*Please select minimum 1 user.</span></div>"
        );
        flag = false;
      }

      if (flag) {
        //$('#loading').show();
        var json_data = {};
        var headers = {
          'X-Authorization': 'authy'
        };
        $.ajax({
          "type": 'GET',
          "url": 'api/v1/location',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": json_data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $("#select-users").addClass("d-none"); //Step 1 section hide
            //Step 2 Section show
            $("#select-users-step2").removeClass("d-none");
            $("#select-users-step2").addClass("d-block");

            //Set plan id in hidden field
            $("#plan_id").val(response.result['locations'][0].plan_id);
            $("#locationId").val(response.result['locations'][0].id);
            if (response.result['locations'][0].plan_id == 3 || response.result[
                'locations'][0].plan_id == 2) {}

            if (response.result['locations'][0].plan_id == 3) {
              //Loction Section Show
              $("#col4Lebel").removeClass("d-none");
              $("#col4Space").removeClass("d-none");
            }

            //Making location option
            for (var i = 0; i < response.result['locations'].length; i++) {
              $('#location').append(
                '<option value=' + response.result['locations'][i].id + '>' +
                response.result['locations'][i].name + '</option>'
              );
            } //location loop
            var custom_sms_message = response.result['locations'][0].custom_sms_message;
            var custom_sms_msg = response.result['locations'][0].custom_message;
            $.each(window.locations, function(i, location) {
              var l_id = $('#location').val();
              // if (location.id == l_id) {
              //     $("#brand_name").val(location.name);
              //     $("#link_dummy").val(location.link);
              //     if (custom_sms_message) {
              //         custom_sms_message = custom_sms_message.replace(
              //             "<Business>", location.name);
              //         custom_sms_message = custom_sms_message.replace(
              //             "<Link>", location.link);
              //     }
              //     if (custom_sms_msg) {
              //         custom_sms_msg = custom_sms_msg.replace("<Business>",
              //             location.name);
              //         custom_sms_msg = custom_sms_msg.replace("<Link>",
              //             location.link);
              //     }
              // }
            });

            //Admin message 
            var check;
            var sms_msgs = 0;
            $("#customSmsSection").html('');
            var check = "";
            for (var k = 0; k < response.result['admin_msg'].length; k++) {
              var custom_message = $("<div/>").html(response.result['admin_msg'][k]
                .msg).html();
              // custom_message = custom_message.replace("<Business>", $("#brand_name")
              //     .val());
              // custom_message = custom_message.replace("<Link>", $("#link_dummy")
              //     .val());
              custom_message.replace("&lt;", "<").replace("&gt;", ">");
              response.result['admin_msg'][k].msg = custom_message;
              if (response.result['admin_msg'][k]) {
                $("#customSmsSection").append(
                  "<p style='font-size:12px;' ><span class='font-weight-bold'>Sample-" +
                  (k + 1) + "</span> " + response.result['admin_msg'][k].msg +
                  "<p/>"
                );
                sms_msgs++;
              }
            }
            //Admin message

            //Append custome message section
            $("#customSmsSection").append(
              "<label for='both' id='customMsgid' class='custom_sms_msg d-block text-left'><input type='radio' class='radiobtn' id='custom_sms_msg' name='customSmsMsg' value='customSmsMsg' checked style='display:none;'> <b>Write Message</b> <button class='btn btn-secondary btn-sm' id='client_name' onclick=\"insertText(\'custom_sms_message\', \'<Customer Name>\');return false;\"> &lt;Customer Name&gt;</button> <button class='btn btn-secondary btn-sm' id='brandName' onclick=\"insertText(\'custom_sms_message\', \'<Business>\');return false;\"> &lt;Business&gt;</button> <button class='btn btn-secondary btn-sm' id='linkName' onclick=\"insertText(\'custom_sms_message\', \'<Link>\');return false;\"> &lt;Link&gt;</button> <textarea class='mt-2 customField' style='width: 100%' class='form-control' name='custom_sms_message' id='custom_sms_message' style='resize:none;' rows='3' placeholder='Write custom message here.''></textarea></label>"
            );

            //Set custom message 
            $("#custom_sms_message").val(response.result.location_message);
            $("#custom_sms_msg").data('msg', custom_sms_message);
            characherRemaining();
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
            $('#errorMsgbody').text(data.responseJSON.info.message);
            $('#errorMsgModal').modal('show');
          },
        }); // Ajax
      } //If flag condition is true
    }); //Id add compaign click

    //click on step two next button
    $("#stepTwoNext").click(function() {
      linkvalidation();
    }); // Step 2 button

    $("#withOutLinkNext").click(function() {
      if ($("#linkSaveType").val() == "Edit") {
        editWithOutLinkValidation();
      } else {
        withLinkValidation();
      }
    }); // Step 2 button

    //Linkvalidation function
    function linkvalidation() {
      var flag = true;
      $(".validation").remove();
      var locationId = "";

      if ($("#plan_id").val() == 3) {
        locationId = $("#location").find(":selected").val();
      } else {
        locationId = $("#locationId").val();
      }

      var campName = $.trim($("#campName").val());
      var remark = $("#remark").val();
      if (campName == "") {
        $('#campName').focus();
        $("#campName").addClass("input_error");
        $("#campName").parent().append(
          "<label class='validation text-danger'>*Please enter campaign name.</label> ");
        flag = false;
      }
      var messageType = $('input:radio[name=message]:checked').val();
      var customSmsMsgInfo = $('#custom_sms_message').val();
      var customSmsMsgId = "customSmsMsg";

      //Check link syntax
      //var linkCheck = $('#link_dummy').val();
      var linkCheck = "<Link>";
      if (customSmsMsgInfo.indexOf(linkCheck) == -1) {
        $("#custom_sms_message").addClass("input_error");
        $("#custom_sms_message").parent().append(
          "<span class='validation text-danger'><br/>*Please enter link.</span>");
        flag = false;
      } else {
        //var companyCheck = $('#brand_name').val();
        var companyCheck = "<Business>";
        if (customSmsMsgInfo.indexOf(companyCheck) == -1) {
          //$("#custom_sms_message").addClass("input_error");
          //$("#custom_sms_message").parent().append("<span class='validation text-danger'>*Please enter brand name.</span>");
          flag = false;
          $('#linkValidation').text("You haven\'t added your Business name in the message.");
          $('#linkValidationModal').modal('show');
        }
      }

      //Check all validation
      $("#locationId").val(locationId);
      $("#messageType").val(messageType);
      $("#customSmsMsgInfo").val(customSmsMsgInfo);
      $("#campaignName").val(campName);
      $("#remarkValue").val(remark);

      if (flag) {
        $("#select-users-step2").removeClass("d-block");
        $("#select-users-step2").addClass("d-none");
        $("#select-users-step3").removeClass("d-none");
      } //IF flag
    }

    //Without linkValidation function
    function withLinkValidation() {
      $("#linkValidationModal").modal("hide");
      var flag = true;
      $(".validation").remove();
      var locationId = "";

      if ($("#plan_id").val() == 3) {
        locationId = $("#location").find(":selected").val();
      } else {
        locationId = $("#locationId").val();
      }

      var campName = $.trim($("#campName").val());
      var remark = $("#remark").val();
      var messageType = $('input:radio[name=message]:checked').val();

      if (campName == "") {
        $('#campName').focus();
        $("#campName").addClass("input_error");
        $("#campName").parent().append(
          "<label class='validation text-danger'>*Please enter campaign name.</label> ");
        flag = false;
      }
      var customSmsMsgInfo = $('#custom_sms_message').val();

      //var linkCheck = $('#link_dummy').val();
      var linkCheck = "<Link>"
      if (customSmsMsgInfo.indexOf(linkCheck) == -1) {
        $("#custom_sms_message").addClass("input_error");
        $("#custom_sms_message").parent().append(
          "<span class='validation text-danger'><br/>*Please enter link.</span>");
        flag = false;
      }

      $("#locationId").val(locationId);
      $("#messageType").val(messageType);
      $("#customSmsMsgInfo").val(customSmsMsgInfo);
      $("#campaignName").val(campName);
      $("#remarkValue").val(remark);

      if (flag) {
        $("#select-users-step2").removeClass("d-block");
        $("#select-users-step2").addClass("d-none");
        $("#select-users-step3").removeClass("d-none");
      } //IF flag
    }

    $("#step-back").click(function() {
      $("#select-users-step2").addClass("d-none");
      $("#select-users-step2").removeClass("d-block");
      $("#select-users").removeClass("d-none");
      $('#location').val('');
      $('#location').text('');
    });

    $("#step-back-two").click(function() {
      $("#select-users-step3").addClass("d-none");
      $("#select-users-step3").removeClass("d-block");
      $("#select-users-step2").removeClass("d-none");
    });

    //Execute campaigns
    $("#executeCompaign").click(function() {

      $(".validation").remove();
      $(".input_error").removeClass('input_error');

      var flag = true;
      var sendType = $('input:radio[name=exeCompaign]:checked').val();
      var dateTime = $("#datetimepicker").val();

      if ($('#remember').is(":checked")) {
        var reminderCheck = "On";
      } else {
        var reminderCheck = "Off";
      }
      //validation Check
      if (sendType == "later") {
        if (dateTime == "") {
          $("#datetimepicker").addClass("input_error");
          $("#datetimepicker").parent().append(
            "<label class='text-danger validation'>*Please choose date and time.</label>");
          flag = false;
        } //If dateTime is emplty
      } //If sendType late

      //Old step infomation
      var customMsg = $.trim($("#customMsgInfo").val());
      var messageType = $("#messageType").val();
      var locationId = $("#locationId").val();
      var plan_id = $("#plan_id").val();
      var selectId = $("#chechboxSelectId").val();
      var campName = $("#campaignName").val();

      var customSmsMsgInfo = $("#customSmsMsgInfo").val();
      var remark = $("#remarkValue").val();
      var selected_ids = $("#chechboxSelectId").val(); //Select id
      var selectedIdArr = "";
      var select_id = JSON.stringify(selectedIdArr);

      if (flag) {
        $('#loading').show();
        var json_data = {
          "customSmsMsgInfo": customSmsMsgInfo,
          "select_id": select_id,
          //"mode"       : mode,

          "messageType": messageType,
          "customMsg": customMsg,
          "plan_id": plan_id,
          "send_type": sendType,
          "date_time": dateTime,
          "campName": campName,
          "remark": remark,
          "reminderCheck": reminderCheck,
          "operation": "addCampaign"
        };
        var headers = {
          'X-Authorization': 'authy'
        };

        $.ajax({
          type: 'POST',
          url: 'api/v1/campaign',
          processData: true,
          crossDomain: true,
          xhrFields: {
            withCredentials: false
          },
          dataType: 'json',
          data: json_data,
          headers: headers,
          timeout: 60000,
          success: function(response, general_msg) {
            debugger;
            $('#loading').hide();
            $("#addCampaignModal").modal("hide");
            $("#executeCompaign").prop("disabled", false);
            if (response.status == "OK") {
              $('#customersDataTable').DataTable().ajax.reload(null, false);
              $('#successMsgMoadl').text(response.result.message);
              $('#MsgModal').modal('show');
              window.cp('event', {
                'action': 'Campaign Created',
                'properties': {
                  'Campaign_Name': campName, // Optional properties
                  'Message_Type': messageType,
                  'Remarks': remark,
                  'Message': customMsg
                }
              });
            }
          },
          error: function(data, general_error) {
            debugger;
            $('#loading').hide();
            if (data.responseJSON.info.status == "remaing_limit") {
              $("#executeCompaign").prop("disabled", false);
              $("#remaingMsgLimit").addClass("input_error");
              $("#remaingMsgLimit").parent().append(
                "<span class='validation text-danger'>*" + data.responseJSON
                .info.message + "</span>");
            } else if (data.responseJSON.info.status == "errorMsg") {
              $("#executeCompaign").prop("disabled", false);
              $("#remaingMsgLimit").addClass("input_error");
              $("#remaingMsgLimit").parent().append(
                "<span class='validation text-danger'>*" + data.responseJSON
                .info.message + "</span>");
            } else {
              $("#addCampaignModal").modal("hide");
              $("#executeCompaign").prop("disabled", false);
              $('#errorMsgbody').text(data.responseJSON.info.message);
              $('#errorMsgModal').modal('show');
            }
          }
        }); //Ajax function
      } //If flag
    }); //Execute campaign button

    $('.toast').on('hidden.bs.toast', function() {
      $("#executeCompaign").prop("disabled", false);
      if ($("#toastStatus").val() == "Success") {
        location.href = "campaigns";
      }
    });

    //Textarea keyup function
    $(document).on('change keyup', "textarea[name='custom_message']", function() {
      var len = $("#custom_message").val().length;
      $("#custom_message").parent().find('.validation').remove();
      if (len >= 160) {
        $("#custom_message").addClass("input_error");
        $("#custom_message").parent().append(
          "<label class='validation text-danger'>*Only 160 characters is allowed.</label> ");
      }
    });

    $(document).on('change keyup', "textarea[name='custom_sms_message']", function() {
      characherRemaining();
    });

    function characherRemaining() {
      var selection = $('#customSmsSection input:checked');
      if (selection.val() == "customSmsMsg") {
        var area = document.getElementById("custom_sms_message");
        area.value = $("#custom_sms_message").val();
        var smsMessage = document.getElementById("smsMessage");
        var text_msg = document.getElementById("custom_sms_message").value;
        var maxLength = 160;
        if (area.value.length <= maxLength) {
          smsMessage.innerHTML = (maxLength - area.value.length) + " characters remaining";
          $('#smsMessage').removeClass("text-danger").addClass('text-success');
        } else {
          var smsMessageWithoutLen = document.getElementById("smsMessageWithoutLen");
          smsMessageWithoutLen.innerHTML = "Recommended: 160 characters.";
          $("#smsMessage").removeClass("d-none");
          $("#smsMessageWithoutLen").addClass("d-none text-warning");
        }

        if (area.value.length <= 0) {
          $("#smsMessage").show();
        } else if (area.value.length > 160) {
          $("#smsMessage").addClass("d-none");
          $("#smsMessageWithoutLen").removeClass("d-none");
        } else if (area.value.length <= 160) {
          $("#smsMessage").removeClass("d-none");
          $("#smsMessageWithoutLen").addClass("d-none");
        } else {
          $("#smsMessage").show();
        }
      } else {
        $("#smsMessage").hide();
      }
    }
    //Brand Name Inserted Function

    function insertText(areaId, text) {
      //$("#brandName").prop("disabled",true);
      var txtarea = document.getElementById(areaId);
      if (!txtarea) {
        return;
      }
      // if (text == '<Link>') {
      //     text = $('#link_dummy').val();
      // }
      // if (text == '<Business>') {
      //     text = $('#brand_name').val();
      // }
      var scrollPos = txtarea.scrollTop;
      var strPos = 0;
      var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
        "ff" : (document.selection ? "ie" : false));
      if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        strPos = range.text.length;
      } else if (br == "ff") {
        strPos = txtarea.selectionStart;
      }

      var front = (txtarea.value).substring(0, strPos);
      var back = (txtarea.value).substring(strPos, txtarea.value.length);
      txtarea.value = front + text + back;
      strPos = strPos + text.length;
      if (br == "ie") {
        txtarea.focus();
        var ieRange = document.selection.createRange();
        ieRange.moveStart('character', -txtarea.value.length);
        ieRange.moveStart('character', strPos);
        ieRange.moveEnd('character', 0);
        ieRange.select();
      } else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
      }
      txtarea.scrollTop = scrollPos;
      characherRemaining();
      if (areaId = "editCustomSmsMessage") {
        editCustomMessage();
      } else {
        characherRemaining();
      }
    }

    function editInsertText(areaId, text) {
      //$("#brandName").prop("disabled",true);
      var txtarea = document.getElementById(areaId);
      if (!txtarea) {
        return;
      }
      // if (text == '<Link>') {
      //     text = $('#link_dummy').val();
      // }
      // if (text == '<Business>') {
      //     text = $('#brand_name').val();
      // }
      var scrollPos = txtarea.scrollTop;
      var strPos = 0;
      var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
        "ff" : (document.selection ? "ie" : false));
      if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        strPos = range.text.length;
      } else if (br == "ff") {
        strPos = txtarea.selectionStart;
      }

      var front = (txtarea.value).substring(0, strPos);
      var back = (txtarea.value).substring(strPos, txtarea.value.length);
      txtarea.value = front + text + back;
      strPos = strPos + text.length;
      if (br == "ie") {
        txtarea.focus();
        var ieRange = document.selection.createRange();
        ieRange.moveStart('character', -txtarea.value.length);
        ieRange.moveStart('character', strPos);
        ieRange.moveEnd('character', 0);
        ieRange.select();
      } else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
      }
      txtarea.scrollTop = scrollPos;
      editCustomMessage();
    }

    //Edit Campaign Modal Value
    function editCampaign(id) {
      //location.href="edit-campaign?id="+id;
      var allSelected = "";
      if ($("#editAllCheckbox").prop("checked") == true) {
        allSelected = "Yes";
      } else {
        allSelected = "No";
      }
      var selected_ids = []; //Selected Id
      $("#editCampId").val(id);
      $("#campId").val(id);
      $("#editCampaignModal").modal();

      var json_data = {};
      var headers = {
        'X-Authorization': 'authy'
      };
      var table = $('#editCampaignDatatable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
		"searching": false,
        "destroy": true,
        "ajax": function(data, callback, settings) {
          data.selected_ids = selected_ids; //Selected Id send on server side
          data.allSelected = allSelected;
          $.ajax({
            "type": 'GET',
            "url": 'api/v1/editcampaign/' + id,
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
              callback(response.result);
              $("#editCampaignName").val(response.result.camp_name);
              $("#editCampName").val(response.result.camp_name);
              $("#editRemark").val(response.result.remark);
              $("#editChechboxSelectId").val(response.result.selected_cust_id);
              if (response.result.camp_status == "Draft") {
                $("input[name=editExeCompaign][value=" + 'save' + "]").prop('checked', true);
              } else {
                $("input[name=editExeCompaign][value=" + 'later' + "]").prop('checked', true);
              }
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }) //Inner Ajax
        },
        "columnDefs": [{
            "targets": [],
            "visible": false,
            "searchable": false,
          },
          {
            "targets": [1],
            "orderable": false
          }
        ],
        "order": [
          [0, "desc"]
        ],
        "oLanguage": {
          "sSearch": ""
        },
        "language": {
          "searchPlaceholder": "    Search",
          "infoFiltered": ""
        },
        "drawCallback": function(settings) {
          editSelectBoxCheckOrNot();
        },
        initComplete: function() {
          $(this.api().table().container()).find('input[type="search"]').attr('autocomplete', 'off');
        },
        "dom": '<"row"<"col-sm-6 pull-left"f><"col-sm-6"> <"col-md-12 p-0 jqueryDataTable"t><"col-md-7 p-0"i><"col-md-5"p>>',
        "rowCallback": function(row, data, index) {
          $('td', row).css('margin-bottom', '4px');
        },
      }); //Datatable

      //Checkbox click
      $("#editCampaignDatatable").unbind().on('click', ".editSelectCheckBox", function() {
        var headers = {
          'X-Authorization': 'authy'
        };
        if ($(this).is(":checked")) {
          selected_ids.push($(this).data("id"));
          $(this).closest("tr").addClass("selected");
          $('#loading').show();
          var data = {
            "operation": "select",
            "id": $(this).data("id")
          }
          $.ajax({
            "type": 'POST',
            "url": 'api/v1/editcampaign/',
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }); //Inner Ajax
        } //If
        else {
          selected_ids.pop($(this).data("id"));
          $(this).closest("tr").removeClass("selected");
          allSelected = "No";
          $('#loading').show();
          var data = {
            "operation": "un_select",
            "id": $(this).data("id")
          }
          $.ajax({
            "type": 'POST',
            "url": 'api/v1/editcampaign/',
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }); //Inner Ajax
          $("#editAllCheckbox").prop("checked", false);
        } //else
        //var unique = selected_ids.filter((v, i, a) => a.indexOf(v) === i);
        $("#editChechboxSelectId").val(selected_ids);
      }); //Checkbox click end 

      $("#loading").hide();
    } //Edit icon 

    //Select All
    function editAllCheckbox() {
      var table = $('#editCampaignDatatable').DataTable();
      var info = table.page.info();
      var pageInfo = info.page + 1;
      var pageStart = info.start;
      //pageStart     = pageStart+1;
      var pageEnd = info.end;

      if ($("#editAllCheckbox").prop("checked") == true) {
        var headers = {
          'X-Authorization': 'authy'
        };
        $("#editChechboxSelectId").val(selected_ids);
        allSelected = "Yes";
        $('#loading').show();
        var data = {
          "operation": "select_all",
          "pageStart": pageStart,
          "pageEnd": pageEnd
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/editcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#editCampaignDatatable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        }); //Inner Ajax
      } else {
        $('.editSelectCheckBox').each(function() {
          selected_ids.pop($(this).data("id"));
          $(this).prop("checked", false).closest("tr").removeClass("selected");
          $("#editChechboxSelectId").val(selected_ids);
          allSelected = "No";
        })

        var headers = {
          'X-Authorization': 'authy'
        };
        $('#loading').show();
        allSelected = "No";
        var data = {
          "operation": "un_select_all",
          "pageStart": pageStart,
          "pageEnd": pageEnd
        }
        $.ajax({
          "type": 'POST',
          "url": 'api/v1/editcampaign/',
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            $('#editCampaignDatatable').DataTable().ajax.reload(null, false);
            $('#loading').hide();
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },
        });
      }
    }

    //Edit campaigin
    $("#editCampaign").click(function() {
      $('.input_error').removeClass('input_error');
      $('.validation').remove();

      $('#editSmsSection').show();
      //$('#editEmailSection').show();
      var flag = true;
      var selected_ids = $("#editChechboxSelectId").val(); //Select id
      var campId = $("#campId").val();
      var select_id_length = selected_ids.length; //Selected Id lengh
      // $('.editSelectCheckBox').each(function(){
      //    if(!flag){
      //        if($(this).prop("checked") == true){
      //             flag = true;                    
      //        }
      //    }
      // });
      // if(!flag){
      //     $(".campaign-selection-err").addClass("validation text-center");
      //     $(".campaign-selection-err").parent().append("<span class='text-danger'>*Please select minimum 1 user.</span>");
      // }

      if (flag) {
        var json_data = {
          "operation": "getCampRecords"
        };
        var headers = {
          'X-Authorization': 'authy'
        };
        $.ajax({
          "type": 'GET',
          "url": 'api/v1/editcampaign/' + campId,
          "processData": true,
          "crossDomain": true,
          "xhrFields": {
            "withCredentials": false
          },
          "dataType": 'json',
          "data": json_data,
          "headers": headers,
          "timeout": 60000,
          success: function(response, general_msg) {
            debugger;
            $("#edit-select-users").addClass("d-none"); //Step 1 section hide
            //Step 2 Section show
            $("#edit-select-users-step2").removeClass("d-none");
            $("#edit-select-users-step2").addClass("d-block");

            //Set plan id in hidden field
            $("#editPlan_id").val(response.result['locations'][0].plan_id);
            $("#editLocationId").val(response.result['locations'][0].id);

            if (response.result['locations'][0].plan_id == 3) {
              //Loction Section Show
              $("#editCol4Lebel").removeClass("d-none");
            }
            //Making location option
            for (var i = 0; i < response.result['locations'].length; i++) {
              $('#editLocation').append(
                '<option value=' + response.result['locations'][i].id + '>' +
                response.result['locations'][i].name + '</option>'
              );
            } //location loop

            //Admin message 
            var check;
            var sms_msgs = 0;
            var email_msgs = 0;

            $("#editCustomSmsSection").html('');
            for (var k = 0; k < response.result['admin_msg'].length; k++) {
              if (response.result['admin_msg'][k]) {
                $("#editCustomSmsSection").append(
                  "<p class style='font-size:12px;'><span class='font-weight-bold'>Sample-" +
                  (k + 1) + " </span>" + response.result['admin_msg'][k].msg +
                  "</p>"
                );
                sms_msgs++;
              }
            } //Admin message

            $("#editCustomSmsSection").append(
              "<label for='both' id='editCustomMsgId'" +
              " class='edit_custom_sms_msg'>" +
              "<input type='radio' class='editSmsRadioBtn' " +
              "id='editCustomSmsMsg' name='editCustomSmsMsg' " +
              "value='customSmsMsg' style='display:none;'> <b>Write Message</b> " +
              "<button class='btn btn-secondary btn-sm' " +
              "id='client_name_edit' onclick=\"insertText(\'editCustomSmsMessage\', \'<Customer Name>\');" +
              "return false;\"> &lt;Customer Name&gt; </button> " +
              "<button class='btn btn-secondary btn-sm' " +
              "id='brandNameEdit' onclick=\"insertText(\'editCustomSmsMessage\', \'<Business>\');" +
              "return false;\"> &lt;Business&gt; </button> " +
              "<button class='btn btn-secondary btn-sm' id='linkNameEdit' " +
              "onclick=\"insertText(\'editCustomSmsMessage\', \'<Link>\');return false;\"> &lt;Link&gt;</button> <textarea class='mt-2 p-2 customField' style='width: 100%' name='editCustomSmsMessage' id='editCustomSmsMessage' style='resize:none;' rows='3' ></textarea></label>"
            );
            //Set custom message 
            $("#editCustomSmsMessage").val(response.result['modeLocation'][0].msg);

            //check if custom message template is empty

            //Textbox show only for plan_id 3
            if (response.result['locations'][0].plan_id >= 1) {
              $("#editCustomMsgId").removeClass("d-none");
              $("#editCustomEmailId").removeClass("d-none");
            }

            //Mode value set
            $("#editMode").val(response.result['modeLocation'][0].mode);

            //Set send type
            if (response.result['sent_via'].length == 1) {
              if (response.result['sent_via'][0].sent_via == "Phone") {
                $("input[name=editMessage][value=" + 'Phone' + "]").prop('checked', true);
                $("input[name=editCustomSmsMsg][value=" + response.result['sent_via'][0].admin_temp + "]").prop('checked', true);
              } else {
                $("input[name=editMessage][value=" + 'Email' + "]").prop('checked', true);
              }
            } else {
              if (response.result['sent_via'].length == 2) {
                $("input[name=editMessage][value=" + 'Both' + "]").prop('checked', true);
              }
            }

            //Location value set
            $("#editLocation").val(response.result['modeLocation'][0].location_id);
            var locations = window.locations;
            $.each(locations, function(i, location) {
              var l_id = $('#editLocation').val();
              if (location.id == l_id) {
                var old_name = $("#brand_name").val();
                var old_link = $("#link_dummy").val();
                $("#brand_name").val(location.name);
                $("#link_dummy").val(location.link);
                var custom_message = $("#editCustomSmsMessage").val();
                // custom_message = custom_message.replace(old_name, location
                //     .name);
                // custom_message = custom_message.replace(old_link, location
                //     .link);

                // custom_message = custom_message.replace("<Business>",
                //     location.name);
                // custom_message = custom_message.replace("<Link>", location
                //     .link);

                $("#editCustomSmsMessage").val(custom_message);

                var msgs = $('.edit_msgs');
                // $.each(msgs, function(i, m) {
                //     var custom_message = $(m).text();
                //     custom_message = custom_message.replace(
                //         old_name, location.name);
                //     custom_message = custom_message.replace(
                //         "<Business>", location.name);
                //     custom_message = custom_message.replace(
                //         old_link, location.link);
                //     custom_message = custom_message.replace(
                //         "<Link>", location.link);
                //     $(m).text(custom_message);
                // });
              }
            });
            editCustomMessage();
            $('#loading').hide();
            $("#edit-message-body").text(response.result['modeLocation'][0].msg);
          },
          error: function(data, general_error) {
            $('#loading').hide();
          },

        }); // Ajax
      } //If flag condition is true
    }); //Id add compaign click 

    $("#edit-step-back").click(function() {
      $("#edit-select-users-step2").addClass("d-none");
      $("#edit-select-users-step2").removeClass("d-block");
      $("#edit-select-users").removeClass("d-none");
      $('#editLocation').val('');
      $('#editLocation').text('');
    });

    $(document).on('change keyup', "textarea[name='editCustomSmsMessage']", function() {
      editCustomMessage();
    });

    function editCustomMessage() {
      var area = document.getElementById("editCustomSmsMessage");
      //$('#preview_msg').val($('#editCustomSmsMessage').val());
      $('#edit-message-body').text($('#editCustomSmsMessage').val());
      var smsMessage = document.getElementById("editSmsMessage");
      var text_msg = document.getElementById("editCustomSmsMessage").value;

      var maxLength = 160;
      if (area.value.length <= maxLength) {
        smsMessage.innerHTML = (maxLength - area.value.length) + " characters remaining";
        $('#editSmsMessage').removeClass("text-danger").addClass('text-success');
      } else {
        var editSmsMessageWithoutLen = document.getElementById("editSmsMessageWithoutLen");
        editSmsMessageWithoutLen.innerHTML = "Recommended: 160 characters.";
        $("#editSmsMessageWithoutLen").removeClass("d-none");
      }

      if (area.value.length <= 0) {
        $("#editSmsMessage").show();
      } else if (area.value.length > 160) {
        $("#editSmsMessage").addClass("d-none");
        $("#editSmsMessageWithoutLen").removeClass("d-none");
      } else if (area.value.length <= 160) {
        $("#editSmsMessage").removeClass("d-none");
        $("#editSmsMessageWithoutLen").addClass("d-none");
      } else {
        $("#editSmsMessage").show();
      }
      //$('#editCustomSmsMessage').prop("readonly",false);
      if ($("#editCustomSmsMessage").val() != "") {
        $("#edit-msg-preview-title").addClass("d-none");
      } else {
        $("#edit-msg-preview-title").removeClass("d-none");
      }
    }

    //click on step two next button
    $("#editStepTwoNext").click(function() {
      if ($("#linkSaveType").val() == "Edit") {

        editWithOutLinkValidation();
      } else {
        editlinkValidation();

      }
    }); // Step 2 button

    // $("#editWithOutLinkNext").click(function(){
    //     editWithOutLinkValidation();
    // });

    //Edit link validatin
    function editlinkValidation() {
      $(".validation").remove();
      var locationId = "";
      var flag = true;

      if ($("#editPlan_id").val() == 3) {
        locationId = $("#editLocation").find(":selected").val();
      } else {
        locationId = $("#editLocationId").val();
      }
      var campName = $.trim($("#editCampName").val());
      var remark = $("#editRemark").val();
      if (campName == "") {
        $('#editCampName').focus();
        $("#editCampName").addClass("input_error");
        $("#editCampName").parent().append(
          "<label class='validation text-danger'>*Please enter campaign name.</label> ");
        flag = false;
      }

      //Message type 
      var messageType = $('input:radio[name=editMessage]:checked').val();
      if (messageType == "Phone") {
        messageType = "Phone";
      } else if (messageType == "Email") {
        messageType = "Email";
      } else {
        messageType = "Both";
      }
      var customSmsMsgInfo = $("#editCustomSmsMessage").val();

      //var linkCheck = $('#link_dummy').val();
      var linkCheck = "<Link>";
      if (customSmsMsgInfo.indexOf(linkCheck) == -1) {
        $("#editCustomSmsMessage").addClass("input_error");
        $("#editCustomSmsMessage").parent().append(
          "<span class='validation text-danger'><br/>*Please enter link.</span>");
        flag = false;
      } else {
        //var companyCheck = $('#brand_name').val();
        var companyCheck = "<Business>";
        if (customSmsMsgInfo.indexOf(companyCheck) == -1) {
          flag = false;
          $('#linkValidation').text("You haven\'t added your Business name in the message.");
          $('#linkValidationModal').modal("show");
          $("#linkSaveType").val("Edit");
          flag = false;
        }
      }

      //Check all validation
      $("#editLocationId").val(locationId);
      $("#editMessageType").val(messageType);
      $("#editCampaignName").val(campName);
      $("#editRemarkValue").val(remark);

      if (flag) {
        $("#edit-select-users-step2").removeClass("d-block");
        $("#edit-select-users-step2").addClass("d-none");
        $("#edit-select-users-step3").removeClass("d-none");
      } //IF flag
    }

    //Edit link validatin
    function editWithOutLinkValidation() {
      $("#linkValidationModal").modal("hide");
      $(".validation").remove();
      var locationId = "";
      var flag = true;

      // if ($("#editPlan_id").val() == 3) {
      //     locationId = $("#editLocation").find(":selected").val();
      // } else {
      //     locationId = $("#editLocationId").val();
      // }
      var campName = $.trim($("#editCampName").val());
      var remark = $("#editRemark").val();

      if (campName == "") {
        $('#editCampName').focus();
        $("#editCampName").addClass("input_error");
        $("#editCampName").parent().append(
          "<label class='validation text-danger'>*Please enter campaign name.</label> ");
        flag = false;
      }
      //Message type 

      var messageType = $('input:radio[name=editMessage]:checked').val();

      var customSmsMsgInfo = $("#editCustomSmsMessage").val();

      //var linkCheck = $('#link_dummy').val();
      var linkCheck = "<Link>";
      if (customSmsMsgInfo.indexOf(linkCheck) == -1) {
        $("#editCustomSmsMessage").addClass("input_error");
        $("#editCustomSmsMessage").parent().append(
          "<span class='validation text-danger'><br/>*Please enter link.</span>");
        flag = false;
      }

      //Check all validation
      $("#editLocationId").val(locationId);
      $("#editMessageType").val(messageType);
      $("#editCustomSmsMsgInfo").val(customSmsMsgInfo);
      $("#editCampaignName").val(campName);
      $("#editRemarkValue").val(remark);
      if (flag) {
        $("#edit-select-users-step2").removeClass("d-block");
        $("#edit-select-users-step2").addClass("d-none");
        $("#edit-select-users-step3").removeClass("d-none");
      } //IF flag
    }

    //Updation campaign final step 
    $("#edit-step-back-two").click(function() {
      $("#edit-select-users-step3").addClass("d-none");
      $("#edit-select-users-step3").removeClass("d-block");
      $("#edit-select-users-step2").removeClass("d-none");
    });

    //Update Campaign
    $("#updateCampaign").click(function() {
      $(".validation").remove();
      $(".input_error").removeClass('input_error');

      var flag = true;
      var sendType = $('input:radio[name=editExeCompaign]:checked').val();
      var dateTime = $("#editdatetimepicker").val();

      if ($('#editRemember').is(":checked")) {
        var reminderCheck = "On";
      } else {
        var reminderCheck = "Off";
      }
      //validation Check
      if (sendType == "later") {
        if (dateTime == "") {
          $("#editdatetimepicker").addClass("input_error");
          $("#editdatetimepicker").parent().append(
            "<label class='text-danger validation'>*Please choose date and time.</label>");
          flag = false;
        } //If dateTime is emplty
      } //If sendType late
      //Old step infomation
      var customSmsMsgInfo = $("#editCustomSmsMessage").val();
      var messageType = $("#editMessageType").val();
      var locationId = $("#editLocationId").val();
      var plan_id = $("#editPlan_id").val();
      var selectId = $("#editChechboxSelectId").val();
      var campName = $("#editCampaignName").val();
      var campId = $("#campId").val();
      var remark = $("#editRemarkValue").val();

      var selected_ids = $("#editChechboxSelectId").val(); //Select id
      var selectedIdArr = "";

      if (selected_ids != "") { //Select Id is not empty
        selectedIdArr = selected_ids.split(','); //selected_ids is convert in array
      }
      var select_id = JSON.stringify(selectedIdArr);

      if (flag) {
        $('#loading').show();
        $("#executeCompaign").prop("disabled", true);

        var json_data = {
          "customSmsMsgInfo": customSmsMsgInfo,
          "select_id": select_id,
          //"mode"       : mode,

          "messageType": messageType,
          "plan_id": plan_id,
          "send_type": sendType,
          "date_time": dateTime,
          "campName": campName,
          "remark": remark,
          "reminderCheck": reminderCheck,
          "campId": campId,
          "operation": "updateCampaign"
        };
        var headers = {
          'X-Authorization': 'authy'
        };
        $.ajax({
          type: 'PUT',
          url: 'api/v1/campaign',
          processData: true,
          crossDomain: true,
          xhrFields: {
            withCredentials: false
          },
          dataType: 'json',
          data: json_data,
          headers: headers,
          timeout: 60000,
          success: function(response, general_msg) {
            $('#loading').hide();
            $("#editCampaignModal").modal("hide");
            $("#executeCompaign").prop("disabled", false);
            if (response.status == "OK") {
              $('#successMsgMoadl').text(response.result.message);
              $('#MsgModal').modal('show');
            }
          },
          error: function(data, general_error) {
            $('#loading').hide();
            $("#executeCompaign").prop("disabled", false);
            if (data.responseJSON.info.status == "remaing_limit") {
              $("#editRemaingMsgLimit").addClass("input_error");
              $("#editRemaingMsgLimit").parent().append(
                "<span class='validation text-danger'>*" + data.responseJSON
                .info.message + "</span>");
            } else if (data.responseJSON.info.status = "selectError") {
              $("#editRemaingMsgLimit").addClass("input_error");
              $("#editRemaingMsgLimit").parent().append(
                "<span class='validation text-danger'>*" + data.responseJSON
                .info.message + "</span>");
            } else {
              $("#editCampaignModal").modal("hide");
              $("#executeCompaign").prop("disabled", false);
              $('#errorMsgbody').text(data.responseJSON.info.message);
              $('#errorMsgModal').modal('show');
            }
          }
        }); //Ajax function
      } //If flag
    }); //Execute campaign button

    //View campaign
    function viewCampaign(id) {
      //location.href="view-campaign?id="+id;        
      var json_data = {};
      var headers = {
        'X-Authorization': 'authy'
      };
      $('#viewCampaignDatatable').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,		
		"searching": false,
        "destroy": true,
        "ajax": function(data, callback, settings) {
          $.ajax({
            "type": 'GET',
            "url": 'api/v1/viewcampaign/' + id,
            "processData": true,
            "crossDomain": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              callback(response.result);

              //All campaign information set
              $("#viewCampName").text(response.result.allData.name);
              $("#viewLocation").val(response.result.allData.location_name);
              $("#viewMode").text(response.result.allData.mode);
              $("#viewRemark").text(response.result.allData.remarks);
              var date = new Date(response.result.allData.added_on * 1000);
              const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
              ];
              var day = date.getDate();
              if (day < 10) {
                day = "0" + day;
              }
              var newDate = monthNames[date.getMonth()] + "/" + day + "/" + date
                .getFullYear();
              $("#viewAddedOn").text(newDate);
              //$("#newDate").text(response.result.allData.added_on);
              $("#viewStatus").text(response.result.allData.status);
              if (response.result.allData.status == "Completed") {
                $("#viewAddedOnText").text("Sent On");
              }

              var d = new Date();
              var yourTimeZone = d.getTimezoneOffset();

              var scheDate = new Date(((1628760600 * 1000) + 1000) +
                yourTimeZone);
              var newday = scheDate.getDate();
              if (newday < 10) {
                newday = "0" + newday;
              }
              var newScheDate = monthNames[scheDate.getMonth()] + "/" + newday +
                "/" + scheDate.getFullYear() + " " + scheDate.getHours() +
                ":00";

              //response.result.allData.scheduled_on
              if (response.result.allData.status == "Scheduled") {

                $("#scheduled").text(newScheDate);
                $(".scheduled").removeClass("d-none");
              } else {
                $(".scheduled").addClass("d-none");
              }
              $('#loading').hide();
              $("#viewCampaignModal").modal();
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }); //Inner Ajax
        },
        "columnDefs": [{
            "targets": [0],
            "visible": false,
            "searchable": false
          },
          {
            "targets": [4],
            "searchable": false
          },
        ],
        "order": [
          [0, "desc"]
        ],
        "oLanguage": {
          "sSearch": ""
        },
        "language": {
          "searchPlaceholder": '   Search',
          "infoFiltered": ""
        },
        "dom": '<"row"<"col-sm-4 pull-left"f><"col-sm-4 text-center"> <"col-sm-4 pull-right"<"leftbar">>> <"row" <"col-md-12 jqueryDataTable px-0"t><"col-md-7 px-0"i><"col-md-5"p>>',
      }); //Datatable
    }

    $('#addCampaignModal').on('hidden.bs.modal', function(e) {
      $("#select-users").removeClass("d-none");
      $("#select-users-step2").addClass("d-none");
      $("#select-users-step2").removeClass("d-block");
      $("#select-users-step3").addClass("d-none");
      $("#select-users-step3").removeClass("d-block");
      $("#campName").val("");
    })

    $('#editCampaignModal').on('hidden.bs.modal', function(e) {
      location.reload();
    });

    //show old and new clients on click checkbox eventes
    function showOldClientsCheckbox(e) {
      if ($(e).is(':checked')) {
        $("#loading").show();
        $("#oldAllCheckbox").show();
        $("#allCheckbox").hide();
        var headers = {
          'X-Authorization': 'authy'
        };
        $('#campaignDatatable').DataTable().destroy();
        var table = $('#campaignDatatable').DataTable({
          "processing": true,
          "searching": false,
          "serverSide": true,
          "responsive": true,
          "retrieve": true,
          "ajax": function(data, callback, settings) {
            data.selected_ids = selected_ids; //Selected Id send on server side
            data.oldClientAllSelect = oldClientAllSelect;
            $.ajax({
              "type": 'GET',
              "url": 'api/v1/showallclients',
              "processData": true,
              "crossDomain": true,
              "serverSide": true,
              "responsive": true,
              "xhrFields": {
                "withCredentials": false
              },
              "dataType": 'json',
              "data": data,
              "headers": headers,
              "timeout": 60000,
              success: function(response, general_msg) {
                $('#loading').hide();
                callback(response.result);
              },
              error: function(data, general_error) {
                $('#loading').hide();
              },
            }) //Inner Ajax
          },
          "columnDefs": [{
              "targets": [0],
              "visible": false,
              "searchable": false,
            },
            {
              "targets": [1],
              "orderable": false
            }
          ],

          "order": [
            [0, "desc"]
          ],
          "oLanguage": {
            "sSearch": ""
          },
          "language": {
            "searchPlaceholder": "    Search",
            "infoFiltered": ""
          },
          "drawCallback": function(settings) {
            selectOldAllBoxCheckOrNot();
          },
          initComplete: function() {
            $(this.api().table().container()).find('input[type="text"]').attr('autocomplete', 'off');
          },
          "dom": '<"row"<"col-sm-6 pull-left"f><"col-sm-6 text-left"> <"col-md-12 p-0 jqueryDataTable"t><"col-md-7 p-0"i><"col-md-5"p>>',
          "rowCallback": function(row, data, index) {
            $('td', row).css('margin-bottom', '4px');
          },
        }); //Datatable
        $("#loading").hide();
      } else {
        $("#oldAllCheckbox").hide();
        $("#allCheckbox").show();
        $("#loading").show();
        $('#campaignDatatable').DataTable().destroy();
        clientsDatatables();
      }
    }

    //Show all old clients datatables
    function clientsDatatables() {
      var json_data = {};
      var headers = {
        'X-Authorization': 'authy'
      };
      var table = $('#campaignDatatable').DataTable({
        "processing": true,
        "searching": false,
        "serverSide": true,
        "responsive": true,
        "retrieve": true,
        "ajax": function(data, callback, settings) {
          data.selected_ids = selected_ids; //Selected Id send on server side
          data.allSelected = allSelected;
          $.ajax({
            "type": 'GET',
            "url": 'api/v1/addcampaign',
            "processData": true,
            "crossDomain": true,
            "serverSide": true,
            "responsive": true,
            "xhrFields": {
              "withCredentials": false
            },
            "dataType": 'json',
            "data": data,
            "headers": headers,
            "timeout": 60000,
            success: function(response, general_msg) {
              $('#loading').hide();
              callback(response.result);
            },
            error: function(data, general_error) {
              $('#loading').hide();
            },
          }) //Inner Ajax
        },
        "columnDefs": [{
            "targets": [0],
            "visible": false,
            "searchable": false,
          },
          {
            "targets": [1],
            "orderable": false
          }
        ],
        "order": [
          [0, "desc"]
        ],
        "oLanguage": {
          "sSearch": ""
        },
        "language": {
          "searchPlaceholder": "    Search",
          "infoFiltered": ""
        },
        "drawCallback": function(settings) {
          selectAllBoxCheckOrNot();
        },
        initComplete: function() {
          $(this.api().table().container()).find('input[type="search"]').parent().wrap('<form>').parent().attr('autocomplete', 'off').css('overflow', 'hidden').css('margin', 'auto');
          $(this.api().table().container()).find('input[type="search"]').attr('autocomplete', 'off');
        },
        "dom": '<"row"<"col-sm-6 pull-left"f><"col-sm-6 text-left"> <"col-md-12 p-0 jqueryDataTable"t><"col-md-7 p-0"i><"col-md-5"p>>',
        "rowCallback": function(row, data, index) {
          $('td', row).css('margin-bottom', '4px');
        },
      }); //Datatable
    }

    function selectAllBoxCheckOrNot() {
      var flag = true;
      var len = 0;
      $('.selectCheckBox').each(function() {
        if ($(this).prop("checked") == false) {
          flag = false;
        }
        if ($(this).prop("checked") == true) {
          len = 2;
        }

      });
      if (flag == true) {
        if (len == 2) {
          $("#allCheckbox").prop("checked", true);
        }
      } else {
        $("#allCheckbox").prop("checked", false);
      }

      var validationFlag = true;
      $('.oldSelectCheckBox').each(function() {
        if ($(this).prop("checked") == false) {
          flag = false;
        }
      });
      if (flag == true) {
        $("#oldAllCheckbox").prop("checked", true);
      } else {
        $("#oldAllCheckbox").prop("checked", false);
      }
    }

    function selectOldAllBoxCheckOrNot() {
      var flag = true;
      $('.oldSelectCheckBox').each(function() {
        if ($(this).prop("checked") == false) {
          flag = false;
        }
      });
      if (flag == true) {
        $("#oldAllCheckbox").prop("checked", true);
      } else {
        $("#oldAllCheckbox").prop("checked", false);
      }
    }

    function editSelectBoxCheckOrNot() {
      var flag = true;
      $('.editSelectCheckBox').each(function() {
        if ($(this).prop("checked") == false) {
          flag = false;
        }
      });
      if (flag == true) {
        $("#editAllCheckbox").prop("checked", true);
      } else {
        $("#editAllCheckbox").prop("checked", false);
      }
    }

    $('#MsgModal').on('hidden.bs.modal', function(e) {
      location.reload();
    })
  </script>
</body>

</html>