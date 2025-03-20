<?php
ini_set('display_errors', 'On'); // Not to show errors on page
require("connection.inc.php");
require("function.inc.php");
$base_path = "/parking/";
session_start();
if (isset($_SESSION['Admin_Login']) && $_SESSION['Admin_Login'] != '' && $_SESSION['Admin_Login'] == 'yes') {
} else { // Then nothing
    header('location: ' . htmlspecialchars($base_path) . 'login.php');
    die();
}
$msg = '';
if (isset($_GET['editid']) && $_GET['editid'] != '') { // For finding the id in database
    $editid = get_safe_value($con, $_GET['editid']); // edit id noting but the data of url_code column
    $sql = "select * from vehicle_category where cat_url_code='$editid'";
    $res = mysqli_query($con, $sql);
    /* $sql_id = "SELECT url_code FROM user WHERE url_code='$editid'"; // checking if id exists
    $result = $con->query($sql_id); */
    if ($res->num_rows == 0) {
        $category = ''; // ID does NOT exist in the database
        $price_per_hour = '';
    } else {
        $row = mysqli_fetch_assoc($res);
        $vehicle_category = $row['category_name'];
        $price_per_hour = $row['amount'];
    }
}
$randomCode = generateRandomAlphaNumeric(250) . generateRandomAlphaNumeric(250); // Change 250 to whatever length you need
$url_code = $randomCode;
if (isset($_POST['submit'])) {
    $vehicle_category = get_safe_value($con, $_POST['vehicle_category']);
    $price_per_hour = get_safe_value($con, $_POST['price_per_hour']);
    if (empty($vehicle_category)) { // Check if any field is empty
        $msg = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Category Name is required.</div>";
    } else {
        $sql = "select * from vehicle_category where category_name='$vehicle_category'";
        $check = mysqli_query($con, $sql);
        $res = mysqli_fetch_assoc($check);
        if (isset($_GET['editid']) && $_GET['editid'] != '') {
            if (empty($vehicle_category)) {
                $_SESSION['msg_cat'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Category Name is required.</div>";
            } else {
                $sql = mysqli_query($con, "Update vehicle_category set category_name='$vehicle_category', amount='$price_per_hour' where cat_url_code='$editid'");
                $_SESSION['cat_msg'] = "<div id='info-form'><img src='assets/imgs/Add_vehicle_category.png' width='30'><span style='position: relative; top: -5px;'> Category edited successfully.</span></div>";
                header('location: ' . htmlspecialchars($base_path) . 'vehicle_category');
                die();
            }
        } else {
            if ($res > 0) { // Checking if the category's slot already exists
                $_SESSION['cat_msg'] = "<div id='info-form'><img src='assets/imgs/Add_vehicle_category.png' width='30'><span style='position: relative; top: -5px;'> Category Already exists.</span></div>";
                header('location: ' . htmlspecialchars($base_path) . 'vehicle_category');
                die();
            } else {
                $category_status = 1;
                $sql_insert = mysqli_query($con, "Insert into vehicle_category (category_name, amount, cat_status, cat_url_code) values ('$vehicle_category', '$price_per_hour', '$category_status', '$url_code')");
                if ($sql_insert) {
                    $_SESSION['cat_msg'] = "<div id='success-form'><img src='assets/imgs/Add_vehicle_category.png' width='30'><span style='position: relative; top: -5px;'> Category created successfully.</span></div>";
                    header('location: ' . htmlspecialchars($base_path) . 'vehicle_category');
                    die();
                } else {
                    $_SESSION['msg_cat'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Something went wrong.</div>";
                }
            }
        }
    }
}
if (isset($_SESSION['msg_cat'])) { // Check if the error / success message exists // Display and clear the error/ sucess message if page is reload
    $msg = $_SESSION['msg_cat'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['msg_cat']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category | PSM</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>assets/css/style.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($base_path); ?>assets/imgs/favicon.png">
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
                    <a href="<?php echo htmlspecialchars($base_path); ?>index">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>slot">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/free_parking_white.png"
                                width="30">
                        </span>
                        <span class="title">Parking Slot</span>
                    </a>
                </li>
                <li class="active">
                    <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_category">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/Add_vehicle_category_blue.png"
                                width="30">
                        </span>
                        <span class="title">Vehicle Category</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_entry">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/parking_gate.png"
                                width="50">
                        </span>
                        <span class="title">Vehicle Entry/Exit</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>reports">
                        <span class="icon">
                            <i class="fa-regular fa-file-pdf"></i>
                        </span>
                        <span class="title">Reports</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>user">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">Users</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>logout">
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
            </div>
            <!-- ================ Events Details List ================= -->
            <div class="details-d-e">
                <div class="container-d-e">
                    <div class="cardHeader">
                        <h2><?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                            echo "Edit Category";
                        } else {
                            echo "Create Category";
                        } ?>
                        </h2>
                        <div class="button-container">
                            <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_category"
                                class="btn-c delete">Cancel</a>
                        </div>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <form method="POST">
                        <div class="fields">
                            <div class="input-field">
                                <label>Category Name<span> *</span></label>
                                <input type="text" name="vehicle_category" placeholder="Enter vehicle category" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                    echo $vehicle_category;
                                } ?>">
                            </div>
                            <div class="input-field">
                                <label>Price per hour<span> *</span></label>
                                <input type="int" name="price_per_hour" placeholder="Enter parking price per hour"
                                    value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                        echo $price_per_hour;
                                    } ?>">
                            </div>
                        </div>
                        <input class="submitBtn" type="submit" name="submit" value="Submit">
                    </form>
                </div>
            </div>
            <footer>
                <p>©<?php $year = date("Y");
                echo $year; ?> Parking Lot Management | All Rights Reserved</p>
                <p><strong>Developed by Jevin Kalathiya</strong></p>
            </footer>
            <!-- =========== Scripts =========  -->
            <script src="<?php echo htmlspecialchars($base_path); ?>assets/js/main.js"></script>
            <script>
                /*================================ To remove error msg ==================================*/
                document.addEventListener('DOMContentLoaded', function () { // Wait for the document to fully load
                    var sessionMsg = document.getElementById('error-form'); // Find the session message element
                    if (sessionMsg) { // If the session message element exists, set a timeout to remove it
                        setTimeout(function () {
                            sessionMsg.style.display = 'none';
                        }, 10000); // 10000 milliseconds = 10 seconds
                    }
                });
            </script>
            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>