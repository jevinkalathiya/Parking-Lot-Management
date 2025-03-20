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
    $sql = "select * from parking_slot where slot_url_code='$editid'";
    $res = mysqli_query($con, $sql);
    /* $sql_id = "SELECT url_code FROM user WHERE url_code='$editid'"; // checking if id exists
    $result = $con->query($sql_id); */
    if ($res->num_rows == 0) {
        $category = ''; // ID does NOT exist in the database
        $slot_start = '';
        $slot_end = '';
    } else {
        $row = mysqli_fetch_assoc($res);
        $category = $row['slot_type'];
        $slot_start = $row['slot_start'];
        $slot_end = $row['slot_end'];
    }
}
$randomCode = generateRandomAlphaNumeric(250) . generateRandomAlphaNumeric(250); // Change 250 to whatever length you need
$url_code = $randomCode;
if (isset($_POST['submit'])) {
    $category = get_safe_value($con, $_POST['category']);
    $slot_start = get_safe_value($con, $_POST['slot_start']);
    $slot_end = get_safe_value($con, $_POST['slot_end']);
    if (!isset($category) || $category === "" || !isset($slot_start) || $slot_start === "" || !isset($slot_end) || $slot_end === "") { // Check if any field is empty
        $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> All fields are required.</div>";
    } else {
        $sql = "select * from parking_slot where slot_type='$category'";
        $check = mysqli_query($con, $sql);
        $res = mysqli_fetch_assoc($check);
        if (isset($_GET['editid']) && $_GET['editid'] != '') {
            if (!isset($category) || $category === "" || !isset($slot_start) || $slot_start === "" || !isset($slot_end) || $slot_end === "") {
                $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> All fields are required.</div>";
            } else {
                if ($slot_start <= 0) {
                    $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Starting slot cannot be lesser than or equal to 0.</div>";
                } else {
                    if ($slot_start < $slot_end) {
                        $sql_overlap_slot = "SELECT * FROM parking_slot WHERE (slot_start <= $slot_end AND slot_end >= $slot_start ) AND slot_url_code != '$editid'"; // Query to check if any slot number is already assigned
                        $slot_overlap_result = mysqli_query($con, $sql_overlap_slot);
                        if (mysqli_num_rows($slot_overlap_result) > 0) {
                            $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> One or more slot numbers are already assigned.</div>";
                        } else {
                            $sql = mysqli_query($con, "Update parking_slot set slot_type='$category', slot_start='$slot_start', slot_end='$slot_end' where slot_url_code='$editid'");
                            $_SESSION['msg'] = "<div id='info-form'><img src='assets/imgs/free_parking_white.png' width='30'><span style='position: relative; top: -5px;'> Parking Slot edited successfully.</span></div>";
                            unset($_SESSION['msg_slot']);
                            header('location: ' . htmlspecialchars($base_path) . 'slot');
                            die();
                        }
                    } else {
                        $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Ending slot cannot be less than Starting slot.</div>";
                    }
                }
            }
        } else {
            if ($res > 0) { // Checking if the category's slot already exists
                $_SESSION['msg'] = "<div id='info-form'><img src='assets/imgs/free_parking_white.png' width='30'><span style='position: relative; top: -5px;'> Category Slots Already exists.</div>";
                unset($_SESSION['msg_slot']);
                header('location: ' . htmlspecialchars($base_path) . 'slot');
                die();
            } else {
                if ($slot_start <= 0) {
                    $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Startin slot cannot be lesser than or equal to 0.</div>";
                } else {
                    if ($slot_start < $slot_end) {
                        $sql_overlap_slot = "SELECT * FROM parking_slot WHERE ((slot_start <= $slot_end AND slot_end >= $slot_start))"; // Query to check if any slot number is already assigned
                        $slot_overlap_result = mysqli_query($con, $sql_overlap_slot);
                        if (mysqli_num_rows($slot_overlap_result) > 0) {
                            $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> One or more slot numbers are already assigned.</div>";
                        } else {
                            $sql_insert = mysqli_query($con, "Insert into parking_slot (slot_type, slot_start, slot_end, slot_url_code) values ('$category', '$slot_start', '$slot_end', '$url_code')"); // Insert new slot since no conflicts exist
                            if ($sql_insert) {
                                $_SESSION['msg'] = "<div id='success-form'><img src='assets/imgs/free_parking_white.png' width='30'><span style='position: relative; top: -5px;'> Parking Slot created successfully.</span></div>";
                                unset($_SESSION['msg_slot']);
                                header('location: ' . htmlspecialchars($base_path) . 'slot');
                                die();
                            } else {
                                $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Something went wrong.</div>";
                            }
                        }
                    } else {
                        $_SESSION['msg_slot'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Ending slot cannot be less than Starting slot.</div>";
                    }
                }
            }
        }
    }
}
if (isset($_SESSION['msg_slot'])) { // Check if the error / success message exists // Display and clear the error/ sucess message if page is reload
    $msg = $_SESSION['msg_slot'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['msg_slot']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slots | PLM</title>
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
                <li class="active">
                    <a href="<?php echo htmlspecialchars($base_path); ?>slot">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/free_parking_blue.png"
                                width="30">
                        </span>
                        <span class="title">Parking Slot</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_category">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/Add_vehicle_category.png"
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
                            echo "Edit Parking Slot";
                        } else {
                            echo "Extend Parking Slot";
                        } ?>
                        </h2>
                        <div class="button-container">
                            <a href="<?php echo htmlspecialchars($base_path); ?>slot" class="btn-c delete">Cancel</a>
                        </div>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <form method="POST">
                        <div class="fields">
                            <div class="input-field">
                                <label>Category<span> *</span></label>
                                <select name="category">
                                    <option disabled selected>Select Category</option>
                                    <?php
                                    $category_dropdown = mysqli_query($con, "select cat_url_code,category_name from vehicle_category where cat_status=1 order by category_name asc");
                                    $selected = false;
                                    $sql_cat = "select * from vehicle_category where category_name='$category'"; // Query To check whether the categories parking slot is assigned or not
                                    $check_cat = mysqli_query($con, $sql_cat);
                                    $res_cat = mysqli_fetch_assoc($check_cat);
                                    while ($row = mysqli_fetch_assoc($category_dropdown)) {
                                        if ($row['cat_url_code'] == $category && !$selected) {
                                            echo "<option selected value=" . $row['cat_url_code'] . ">" . $row['category_name'] . "</option>"; // Display the selected category as the selected option once
                                            $selected = true; // Mark the selected category as displayed
                                        } else {
                                            echo "<option value=" . $row['cat_url_code'] . ">" . $row['category_name'] . "</option>"; // Display other categories normally
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-field">
                                <label>Start Slot<span> *</span></label>
                                <input type="number" name="slot_start" placeholder="Enter starting slot no" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                    echo $slot_start;
                                } ?>">
                            </div>
                            <div class="input-field">
                                <label>End Slot<span> *</span></label>
                                <input type="number" name="slot_end" placeholder="Enter ending slot no" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                    echo $slot_end;
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
                        }, 5000); // 10000 milliseconds = 10 seconds
                    }
                });
            </script>
            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>