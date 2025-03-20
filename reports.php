<?php
ini_set('display_errors', 'On'); // Not to show errors on page
require("connection.inc.php");
require("function.inc.php");
require('vendor/autoload.php');
use Dompdf\Dompdf;
use Dompdf\Options;
use FontLib\Table\Type\head;
session_start();
if (isset($_SESSION['Admin_Login']) && $_SESSION['Admin_Login'] != '' && $_SESSION['Admin_Login'] == 'yes') {
} else { // Then nothing
    header('location: login');
    die();
}
$msg = '';
if (isset($_POST['submit'])) { // Allow the page to load normally if no form is submitted
    $alldata = isset($_POST['alldata']) ? 1 : 0; // 1 if checked, 0 if not
    if ($alldata) {
        printReport($con, 1);
    } else {
        if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
            $start_date = get_safe_value($con, $_POST['start_date']);
            $end_date = get_safe_value($con, $_POST['end_date']);
            if ($start_date > $end_date) {
                $_SESSION['msg_reports'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> End Date cannot be before Start Date.</div>";
            } else {
                $_SESSION['msg_reports'] = "<div id='success-form'><i class='fa-regular fa-file-pdf' style='font-size: 20px;' color='#fff'></i> Generating report from $start_date to $end_date</div>";
                printReport($con, '', $start_date, $end_date);
            }
        } else {
            $_SESSION['msg_reports'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Both Start Date and End Date are required.</div>";
        }
    }
}
if (isset($_SESSION['msg_reports'])) {
    $msg = $_SESSION['msg_reports'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['msg_reports']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | PLM</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="./assets/imgs/favicon.png">
    <!-- ======= Ajax ====== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li><br>
                    <span class="company-title">Parking Slot Management</span>
                </li>
                <li>
                    <a href="index">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="slot">
                        <span class="icon">
                            <img src="assets/imgs/free_parking_white.png" width="30">
                        </span>
                        <span class="title">Parking Slot</span>
                    </a>
                </li>
                <li>
                    <a href="vehicle_category">
                        <span class="icon">
                            <img src="assets/imgs/Add_vehicle_category.png" width="30">
                        </span>
                        <span class="title">Vehicle Category</span>
                    </a>
                </li>
                <li>
                    <a href="vehicle_entry">
                        <span class="icon">
                            <img src="assets/imgs/parking_gate.png" width="50">
                        </span>
                        <span class="title">Vehicle Entry/Exit</span>
                    </a>
                </li>
                <li class="active">
                    <a href="reports">
                        <span class="icon">
                            <i class="fa-regular fa-file-pdf"></i>
                        </span>
                        <span class="title">Reports</span>
                    </a>
                </li>
                <li>
                    <a href="user">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">Users</span>
                    </a>
                </li>
                <li>
                    <a href="logout">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Log Out</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <form method="get" id="searchForm">
                    <div class="search">
                        <label>
                            <input type="text" name="s" placeholder="Search here" value="<?php if (isset($_GET['s'])) {
                                                                                                echo $_GET['s'];
                                                                                            } ?>" onkeypress="if(event.key === 'Enter'){ this.form.submit(); }">
                            <ion-icon name="search-outline"></ion-icon>
                        </label>
                    </div>
                </form>
            </div>
            <!-- ================ Participants Details List ================= -->
            <div class="details-d-r">
                <div class="container-d-r">
                    <div class="cardHeader">
                        <h2>Reports <!-- <span id="count"></span> --></h2>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <form method="POSt">
                        <div class="fields">
                            <div class="input-field">
                                <label>Start Date<span> *</span></label>
                                <input type="date" name="start_date">
                            </div>
                            <div class="input-field">
                                <label>End Date<span> *</span></label>
                                <input type="date" name="end_date">
                            </div>
                            <div class="input">
                                <input type="checkbox" name="alldata" id="alldata">
                                <label for="alldata">All Parking Data</label>
                            </div>
                        </div>
                        <input type="submit" name="submit" class="submitBtn" value="Generate PDF">
                    </form>
                    <div class="footer">
                        <p>©<?php $year = date("Y");
                            echo $year; ?> Parking Lot Management | All Rights Reserved</p>
                        <p><strong>Developed by Jevin Kalathiya</strong></p>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sessionMsg = document.getElementById('error-form'); // Find the session message element
            var sessionMsg_success = document.getElementById('success-form');
            var sessionMsg_info = document.getElementById('info-form');
            if (sessionMsg) { // If the session message element exists, set a timeout to remove it
                setTimeout(function() {
                    sessionMsg.style.display = 'none';
                }, 5000); // 5000 milliseconds = 5 seconds
            }
            if (sessionMsg_success) {
                setTimeout(function() {
                    sessionMsg_success.style.display = 'none';
                }, 5000); // 5000 milliseconds = 5 seconds
            }
            if (sessionMsg_info) {
                setTimeout(function() {
                    sessionMsg_info.style.display = 'none';
                }, 5000); // 5000 milliseconds = 5 seconds
            }
        });
    </script>
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>