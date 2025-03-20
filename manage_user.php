<?php
ini_set('display_errors', 'On'); // Not to show errors on page
require("connection.inc.php");
require("function.inc.php");
$base_path = "/parking/";
session_start();
if (isset($_SESSION['Admin_Login']) && $_SESSION['Admin_Login'] != '' && $_SESSION['Admin_Login'] == 'yes') {
} else { // Then nothing
    header('location: login.php');
    die();
}
$msg = '';
if (isset($_GET['editid']) && $_GET['editid'] != '') { // For finding the id in database
    $editid = get_safe_value($con, $_GET['editid']); // edit id noting but the data of url_code column
    $sql = "select * from user where url_code='$editid'";
    $res = mysqli_query($con, $sql);
    /* $sql_id = "SELECT url_code FROM user WHERE url_code='$editid'"; // checking if id exists
    $result = $con->query($sql_id); */
    if ($res->num_rows == 0) {
        $name = ''; // ID does NOT exist in the database
        $email = '';
        $password = '';
    } else {
        $row = mysqli_fetch_assoc($res);
        $name = $row['full_name'];
        $email = $row['email'];
    }
}
$randomCode = generateRandomAlphaNumeric(250) . generateRandomAlphaNumeric(250); // Change 250 to whatever length you need
$url_code = $randomCode;
if (isset($_POST['submit'])) {
    $name = get_safe_value($con, $_POST['name']);
    $email = get_safe_value($con, $_POST['email']);
    $password = get_safe_value($con, $_POST['password']);
    if (empty($name) || empty($email) || empty($password)) { // Check if any field is empty
        $msg = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> All fields are required.</div>";
    } else {
        $sql = "select * from user where email='$email'";
        $check = mysqli_query($con, $sql);
        $res = mysqli_fetch_assoc($check);
        if (isset($_GET['editid']) && $_GET['editid'] != '') {
            $encryption_key = bin2hex(openssl_random_pseudo_bytes(16)); // For password encrypt
            $hashed_password = encryptPassword($password, $encryption_key);
            $sql = mysqli_query($con, "Update user set full_name='$name', email='$email', password='$hashed_password', url_code='$url_code', encryption_key='$encryption_key' where url_code='$editid'");
            $_SESSION['user_msg'] = "<div id='info-form'><i class='fa-solid fa-user-pen'></i> User edited successfully.</div>";
            header('location: ' . htmlspecialchars($base_path) . 'user');
            die();
        } else {
            if ($res > 0) {
                $_SESSION['user_msg'] = "<div id='info-form'><i class='fa-solid fa-user-pen'></i> User Already exists.</div>";
                header('location: ' . htmlspecialchars($base_path) . 'user');
                die();
            } else {
                $encryption_key = bin2hex(openssl_random_pseudo_bytes(16)); // For password encrypt
                $hashed_password = encryptPassword($password, $encryption_key);
                $sql_insert = mysqli_query($con, "Insert into user (full_name, email, password, encryption_key, url_code) values ('$name', '$email', '$hashed_password', '$encryption_key','$url_code')");
                if ($sql_insert) {
                    $_SESSION['user_msg'] = "<div id='success-form'><i class='fa-solid fa-user-plus'></i> User created successfully.</div>";
                    header('location: ' . htmlspecialchars($base_path) . 'user');
                    die();
                } else {
                    $_SESSION['msg_user'] = "<div id='error-form'><i class='fa-regular fa-circle-exclamation'></i> Something went wrong.</div>";
                }
            }
        }
    }
}
if (isset($_SESSION['msg_user'])) { // Check if the error / success message exists // Display and clear the error/ sucess message if page is reload
    $msg = $_SESSION['msg_user'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['msg_user']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | PSM</title>
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
                    <a href="slot">
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
                <li class="active">
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
                            echo "Edit User";
                        } else {
                            echo "Create User";
                        } ?>
                        </h2>
                        <div class="button-container">
                            <a href="<?php echo htmlspecialchars($base_path); ?>user" class="btn-c delete">Cancel</a>
                        </div>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <form method="POST">
                        <div class="fields">
                            <div class="input-field">
                                <label>Full Name<span> *</span></label>
                                <input type="text" name="name" placeholder="Enter full name" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                    echo $name;
                                } ?>">
                            </div>
                            <div class="input-field">
                                <label>Email<span> *</span></label>
                                <input type="email" name="email" placeholder="Enter user email" value="<?php if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                    echo $email;
                                } ?>">
                            </div>
                            <div class="input-field">
                                <label>Password<span> *</span></label>
                                <input type="password" name="password" placeholder="Enter passworde" id="password"
                                    value="<?php
                                    if (isset($_GET['editid']) && $_GET['editid'] != '') {
                                        $sql = "Select * from user where url_code='{$_GET['editid']}'";
                                        $result = mysqli_query($con, $sql);
                                        $user = mysqli_fetch_assoc($result); // Fetch the user data
                                        $stored_password_encoded = $user['password']; // Retrieve the Base64 encoded password from the database
                                        $encryption_key = $user['encryption_key'];
                                        $stored_password = decryptPassword($stored_password_encoded, $encryption_key); // Decode the stored password
                                        echo $stored_password;
                                    } ?>">
                            </div>
                            <div class="input">
                                <input type="checkbox" id="showPasswordCheckbox" onclick="togglePasswordVisibility()">
                                <label for="showPasswordCheckbox">Show password</label>
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
                function togglePasswordVisibility() { // To show / hide password
                    var passwordField = document.getElementById("password");
                    var checkbox = document.getElementById("showPasswordCheckbox");
                    if (checkbox.checked) {
                        passwordField.type = "text"; // Show password
                    } else {
                        passwordField.type = "password"; // Hide password
                    }
                }
            </script>
            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>