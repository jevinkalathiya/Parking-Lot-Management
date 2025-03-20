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
    $sql = "select * from entry_exit where vehicle_url_code='$editid'";
    $res = mysqli_query($con, $sql);
    if ($res->num_rows == 0) {
        $vehicle_owner = ''; // ID does NOT exist in the database
        $owner_no = '';
        $vehicle_no = '';
        $vehicle_type = '';
        $entry_date_time = '';
        $parked_slot_id = '';
    } else {
        $row = mysqli_fetch_assoc($res);
        $vehicle_owner = $row['owner_name'];
        $owner_no = $row['mobile_no'];
        $vehicle_no = $row['vehicle_no'];
        $vehicle_type = $row['vehicle_type'];
        $parked_slot_id = $row['parked_spot'];
        $entry_date_time = date("d-m-Y h:i A", strtotime($row['entry_date_time']));
    }
}
$randomCode = generateRandomAlphaNumeric(250) . generateRandomAlphaNumeric(250); // Change 250 to whatever length you need
$url_code = $randomCode;
if (isset($_POST['submit'])) {
    $vehicle_owner = get_safe_value($con, $_POST['vehicle_owner']);
    $owner_mobileno = get_safe_value($con, $_POST['owner_mobileno']);
    $vehicle_no = get_safe_value($con, $_POST['vehicle_no']);
    $vehicle_type = get_safe_value($con, $_POST['vehicle_type']);
    if (empty($vehicle_owner) || empty($owner_mobileno) || empty($vehicle_no) || empty($vehicle_type)) { // Check if any field is empty
        $_SESSION['msg_veh'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> All fields are required.</div>";
    } else {
        $sql_vehicle_no = "select * from entry_exit where vehicle_no='$vehicle_no'"; // Checking if vehicle no is already parked
        $check_vehicle_no = mysqli_query($con, $sql_vehicle_no);
        $res_vehicle_no = mysqli_fetch_assoc($check_vehicle_no);
        if (isset($_GET['editid']) && $_GET['editid'] != '') {
            if (empty($vehicle_owner) || empty($owner_mobileno) || empty($vehicle_no) || empty($vehicle_type)) {
                $_SESSION['msg_veh'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> All fields are required.</div>";
            } else {
                /* Checking is there any free parking slot available to park vehicle of that vehicle type */
                $sql = "SELECT slot_start, slot_end FROM parking_slot WHERE slot_type = '$vehicle_type'"; // Get slot range for the vehicle type
                $result = mysqli_query($con, $sql);
                if ($row = mysqli_fetch_assoc($result)) {
                    $slot_start = $row['slot_start'];
                    $slot_end = $row['slot_end'];
                    for ($i = $slot_start; $i <= $slot_end; $i++) { // Find an empty slot within the range
                        $check_slot_sql = "SELECT COUNT(*) AS count FROM entry_exit WHERE parked_spot = $i"; // Check if the slot is already occupied
                        $check_slot_result = mysqli_query($con, $check_slot_sql);
                        $check_slot_row = mysqli_fetch_assoc($check_slot_result);
                        if ($check_slot_row['count'] == 0) { // If slot is not occupied
                            $price_sql = mysqli_query($con, "select amount from vehicle_category where cat_url_code='$vehicle_type'");
                            $price_row = mysqli_fetch_assoc($price_sql);
                            $sql_insert = mysqli_query($con, "Update entry_exit set owner_name='$vehicle_owner', mobile_no='$owner_mobileno', vehicle_no='$vehicle_no', price='" . $price_row['amount'] . "', vehicle_type='$vehicle_type', parked_spot='$i' where vehicle_url_code='$editid'"); // Insert the entry with assigned slot
                            if ($sql_insert) {
                                $_SESSION['veh_msg'] = "<div id='success-form' style='height: 50px'><img src='assets/imgs/parking_gate.png' width='40' style='position: relative; top: -5px;'><span style='position: relative; top: -15px;'> Vehicle record edited successfully.</span></div>";
                                $_SESSION['veh_url_code'] = $url_code;
                                header('location: ' . htmlspecialchars($base_path) . 'vehicle_entry');
                                die();
                            } else {
                                $_SESSION['msg_veh'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Something went wrong.</div>";
                            }
                        } //break; // Stop loop once a slot is assigned
                    }
                    $_SESSION['msg_veh'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> No available slots for this vehicle type.</div>"; // If no slots are available
                }
            }
        } else {
            if ($res_vehicle_no > 0) { // Checking if the vehicle is already parked or not
                $_SESSION['msg_veh'] = "<div id='error-form' style='height: 50px'><i class='fa-regular fa-circle-exclamation'></i> Duplicate Vehicle Registration Number.</div>";
            } else {
                /* Checking is there any free parking slot available to park vehicle of that vehicle type */
                $sql = "SELECT slot_start, slot_end FROM parking_slot WHERE slot_type = '$vehicle_type'"; // Get slot range for the vehicle type
                $result = mysqli_query($con, $sql);
                if ($row = mysqli_fetch_assoc($result)) {
                    $slot_start = $row['slot_start'];
                    $slot_end = $row['slot_end'];
                    for ($i = $slot_start; $i <= $slot_end; $i++) { // Find an empty slot within the range
                        $check_slot_sql = "SELECT COUNT(*) AS count FROM entry_exit WHERE parked_spot = $i"; // Check if the slot is already occupied
                        $check_slot_result = mysqli_query($con, $check_slot_sql);
                        $check_slot_row = mysqli_fetch_assoc($check_slot_result);
                        if ($check_slot_row['count'] == 0) { // If slot is not occupied
                            $price_sql = mysqli_query($con, "select amount from vehicle_category where cat_url_code='$vehicle_type'");
                            $price_row = mysqli_fetch_assoc($price_sql);
                            $sql_insert = mysqli_query($con, "Insert into entry_exit (owner_name, mobile_no, vehicle_no, price, vehicle_type, entry_date_time, parked_spot, vehicle_url_code) values ('$vehicle_owner', '$owner_mobileno', '$vehicle_no', '" . $price_row['amount'] . "', '$vehicle_type', NOW(), '$i', '$url_code')"); // Insert the entry with assigned slot
                            if ($sql_insert) {
                                $_SESSION['veh_msg'] = "<div id='success-form' style='height: 50px'><img src='assets/imgs/parking_gate.png' width='40' style='position: relative; top: -5px;'><span style='position: relative; top: -15px;'> Vehicle record added successfully.</span></div>";
                                $_SESSION['veh_url_code'] = $url_code;
                                header('location: ' . htmlspecialchars($base_path) . 'vehicle_entry');
                                die();
                            } else {
                                $_SESSION['msg_veh'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Something went wrong.</div>";
                            }
                        } //break; // Stop loop once a slot is assigned
                    }
                    $_SESSION['msg_veh'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> No available slots for this vehicle type.</div>"; // If no slots are available
                }
            }
        }
    }
}
if (isset($_SESSION['msg_veh'])) { // Check if the error / success message exists // Display and clear the error/ sucess message if page is reload
    $msg = $_SESSION['msg_veh'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['msg_veh']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry | PSM</title>
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
                <li>
                    <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_category">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/Add_vehicle_category.png"
                                width="30">
                        </span>
                        <span class="title">Vehicle Category</span>
                    </a>
                </li>
                <li class="active">
                    <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_entry">
                        <span class="icon">
                            <img src="<?php echo htmlspecialchars($base_path); ?>assets/imgs/parking_gate_blue.png"
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
                            echo "Edit Vehicle Record";
                        } else {
                            echo "New Vehicle Record";
                        } ?>
                        </h2>
                        <div class="button-container">
                            <a href="<?php echo htmlspecialchars($base_path); ?>vehicle_entry"
                                class="btn-c delete">Cancel</a>
                        </div>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <form method="POST">
                        <div class="fields">
                            <div class="input-field">
                                <label>Owner Name<span> *</span></label>
                                <input type="text" name="vehicle_owner" placeholder="Enter vehicle owner" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                    echo $vehicle_owner;
                                } ?>">
                            </div>
                            <div class="input-field">
                                <label>Mobile No.<span> *</span></label>
                                <input type="tel" name="owner_mobileno" placeholder="Enter vehicle owner mobile no"
                                    pattern="[6-9][0-9]{9}" maxlength="10" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                        echo $owner_no;
                                    } ?>">
                            </div>
                            <div class="input-field">
                                <label>Vehicle Registration No.<span> *</span></label>
                                <input type="text" name="vehicle_no" id="vehicleNumber" maxlength="13"
                                    placeholder="MH-02-EF-1234" oninput="formatVehicleNumber(this)" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                        echo $vehicle_no;
                                    } ?>">
                            </div>
                            <div class="input-field">
                                <label>Vehicle Type<span> *</span></label>
                                <select name="vehicle_type">
                                    <option disabled selected>Select Type</option>
                                    <?php
                                    $type_dropdown = mysqli_query($con, "select category_name, amount, cat_url_code from vehicle_category where cat_status=1 order by category_name asc");
                                    while ($row = mysqli_fetch_assoc($type_dropdown)) {
                                        if ($row['cat_url_code'] == $vehicle_type && !$selected) {
                                            echo "<option selected value=" . $row['cat_url_code'] . ">" . $row['category_name'] . ' - ₹ ' . $row['amount'] . '/hr' . "</option>"; // Display the selected category as the selected option once
                                            $selected = true; // Mark the selected category as displayed
                                        } else {
                                            echo "<option value=" . $row['cat_url_code'] . ">" . $row['category_name'] . ' - ₹ ' . $row['amount'] . '/hr' . "</option>"; // Display other categories normally
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if (isset($_GET['editid']) && $_GET['editid'] != '') { ?>
                                <div class="input-field">
                                    <label>Parked Slot ID<span> *</span></label>
                                    <input type="text" name="parked_slot" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                        echo $parked_slot_id;
                                    } ?>" readonly disabled>
                                </div>
                                <div class="input-field">
                                    <label>Entry Date-Time<span> *</span></label>
                                    <input type="text" name="entry_datetime" id="entry_datetime" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                        echo $entry_date_time;
                                    } ?>" readonly disabled>
                                </div>
                            <?php } ?>
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
                function formatVehicleNumber(input) { // Vehicle number formatting
                    let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Remove invalid characters
                    let formattedValue = '';
                    if (value.length > 0) formattedValue += value.substring(0, 2).replace(/[^A-Z]/g, ''); // First two letters // Enforce correct character types at each position
                    if (value.length > 2) formattedValue += '-' + value.substring(2, 4).replace(/[^0-9]/g, ''); // Two numbers
                    if (value.length > 4) formattedValue += '-' + value.substring(4, 6).replace(/[^A-Z]/g, ''); // Two letters
                    if (value.length > 6) formattedValue += '-' + value.substring(6, 10).replace(/[^0-9]/g, ''); // Four numbers
                    input.value = formattedValue;
                }
                /*================================ To remove error msg ==================================*/
                document.addEventListener('DOMContentLoaded', function () { // Wait for the document to fully load
                    var sessionMsg = document.getElementById('error-form'); // Find the session message element
                    if (sessionMsg) { // If the session message element exists, set a timeout to remove it
                        setTimeout(function () {
                            sessionMsg.style.display = 'none';
                        }, 5000); // 5000 milliseconds = 5 seconds
                    }
                });
            </script>
            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>