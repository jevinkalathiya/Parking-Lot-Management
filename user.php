<?php
ini_set('display_errors', 'On'); // Not to show errors on page
require("connection.inc.php");
require("function.inc.php");
session_start();
if (isset($_SESSION['Admin_Login']) && $_SESSION['Admin_Login'] != '' && $_SESSION['Admin_Login'] == 'yes') {
} else { // Then nothing
    header('location: login');
    die();
}
if (isset($_SESSION['user_msg'])) { // Check if the error / success message exists // Display and clear the error/ sucess message if page is reload
    $msg = $_SESSION['user_msg'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['user_msg']);
    }
}
if (isset($_GET['ac']) && $_GET['ac'] != '') { // For deleting the Event
    $action = get_safe_value($con, $_GET['ac']);
    if ($action == 'delete') {
        $url_code = get_safe_value($con, $_GET['requestid']); // requestid is nothing but url_code od database
        $delete_sql = "delete from user where url_code='$url_code'";
        mysqli_query($con, $delete_sql);
        header('location: user');
        die();
    }
}
$records_per_page = 10; // Number of records to display per page // Pagination
$start = 0;
$page = 1;
if (isset($_GET['p'])) {
    $page = $_GET['p'];
    $start = ($page - 1) * $records_per_page;
}
$record = mysqli_num_rows(mysqli_query($con, "SELECT * FROM user"));
$total_pages = ceil($record / $records_per_page);
$sql_page = "SELECT * FROM user LIMIT $start, $records_per_page";
$res_page = mysqli_query($con, $sql_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | PSM</title>
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
                <li>
                    <a href="reports">
                        <span class="icon">
                            <i class="fa-regular fa-file-pdf"></i>
                        </span>
                        <span class="title">Reports</span>
                    </a>
                </li>
                <li class="active">
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
                            <input type="text" name="s" id="searchInput" placeholder="Search here" value="<?php if (isset($_GET['s'])) {
                                echo $_GET['s'];
                            } ?>"
                                onkeypress="if(event.key === 'Enter'){ this.form.submit(); } updateUser(this.value.trim());">
                            <ion-icon name="search-outline"></ion-icon>
                        </label>
                    </div>
                </form>
            </div>
            <!-- ================ Events Details List ================= -->
            <div class="details-d-e">
                <div class="container-d-e">
                    <div class="cardHeader">
                        <h2>Users <span id="count"></span></h2>
                        <div class="button-container">
                            <a href="manage_user" class="btn-c">Add</a>
                        </div>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <table>
                        <thead>
                            <tr>
                                <td>Name</td>
                                <td>Email</td>
                                <td>Password</td>
                                <td colspan="2">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- For showing Search data and if no data found showing No results found and if not searched showing normal curd -->
                            <?php
                            $sql = "select * from user LIMIT $start, $records_per_page"; // fetching the event data to show in table (CURD)
                            $res = mysqli_query($con, $sql);
                            $search_query = '';
                            if (isset($_GET['s']) && !empty(trim($_GET['s']))) {
                                $search = get_safe_value($con, $_GET['s']);
                                if (!empty($search)) {
                                    $search_query = "WHERE full_name LIKE '%$search%'"; // Perform search query
                                    $record_query = "SELECT COUNT(*) as total FROM user $search_query"; // Get the total number of records considering the search filter
                                    $record_result = mysqli_query($con, $record_query);
                                    if ($record_result) {
                                        $record_row = mysqli_fetch_assoc($record_result);
                                        $record = $record_row['total'];
                                        $total_pages = ceil($record / $records_per_page);
                                        $sql_page = "SELECT * FROM user $search_query LIMIT $start, $records_per_page"; // Fetch the event data with pagination and optional search filtering
                                        $res_page1 = mysqli_query($con, $sql_page);
                                        if (mysqli_num_rows($res_page1) > 0) {
                                            while ($items = mysqli_fetch_assoc($res_page1)) { // Loop through and display search results
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($items['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($items['email']); ?></td>
                                                    <td>
                                                        <!-- Hidden password field -->
                                                        <span id="passwordField_<?php echo $items['id']; ?>" style="display: none;">
                                                            <?php echo htmlspecialchars(decryptPassword($items['password'], $items['encryption_key'])); ?>
                                                        </span>
                                                        <!-- Masked password -->
                                                        <span id="hiddenPassword_<?php echo $items['id']; ?>">
                                                            ******
                                                        </span>
                                                        <!-- Eye icon for toggling visibility -->
                                                        <span id="showpass_<?php echo $items['id']; ?>"
                                                            onclick="togglePasswordVisibility(<?php echo $items['id']; ?>)">
                                                            <i id="icon_<?php echo $items['id']; ?>" class="fa-regular fa-eye-slash"></i>
                                                        </span>
                                                    </td>
                                                    <td><a href='manage_user/<?php echo $items['url_code']; ?>' class='btn-c'>Edit</a></td>
                                                    <td><a href='?ac=delete&requestid=<?php echo $items['url_code']; ?>'
                                                            class='btn-c delete'>Delete</a></td>
                                                </tr>
                                                <?php
                                            }
                                        } elseif (!mysqli_num_rows($res_page1) > 0) { ?>
                                            <td colspan="7">No record Found</td>
                                        <?php }
                                    }
                                } else {
                                    echo "<div id='no-record'><h4>No results found for the search term.</h4></div>"; // No search results found
                                }
                            } else {
                                $query = "select * from user limit $start, $records_per_page"; // Fetch all events // Normal CRUD operation (when no search is performed)
                                $query_run = mysqli_query($con, $query);
                                if (mysqli_num_rows($query_run) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_run)) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <!-- Hidden password field -->
                                                <span id="passwordField_<?php echo $row['id']; ?>" style="display: none;">
                                                    <?php echo htmlspecialchars(decryptPassword($row['password'], $row['encryption_key'])); ?>
                                                </span>
                                                <!-- Masked password -->
                                                <span id="hiddenPassword_<?php echo $row['id']; ?>">
                                                    ******
                                                </span>
                                                <!-- Eye icon for toggling visibility -->
                                                <span id="showpass_<?php echo $row['id']; ?>"
                                                    onclick="togglePasswordVisibility(<?php echo $row['id']; ?>)">
                                                    <i id="icon_<?php echo $row['id']; ?>" class="fa-regular fa-eye-slash"></i>
                                                </span>
                                            </td>
                                            <td><a href='manage_user/<?php echo $row['url_code']; ?>' class='btn-c'>Edit</a>
                                            </td>
                                            <td><a href='?ac=delete&requestid=<?php echo $row['url_code']; ?>'
                                                    class='btn-c delete'>Delete</a></td>
                                        </tr>
                                        <?php
                                    }
                                } elseif (!mysqli_num_rows($query_run) > 0) { ?>
                                    <td colspan="7">No record Found</td>
                                <?php }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php
                echo '<nav aria-label="Page navigation">';
                echo '<ul class="pagination">';
                if ($page > 1) { // Previous button
                    echo '<li class="page-item"><a class="page-link" href="?p=' . ($page - 1) . '">Previous</a></li>';
                }
                $max_links = 3; // Maximum number of page links to display // Page number links
                $start_page = max(1, $page - floor($max_links / 2));
                $end_page = min($total_pages, $page + floor($max_links / 2));
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?p=1">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item"><span class="page-link">...</span></li>';
                    }
                }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $page) {
                        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="?p=' . $i . '">' . $i . '</a></li>';
                    }
                }
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?p=' . $total_pages . '">' . $total_pages . '</a></li>';
                }
                if ($page < $total_pages) { // Next button
                    echo '<li class="page-item"><a class="page-link" href="?p=' . ($page + 1) . '">Next</a></li>';
                }
                echo '</ul>';
                echo '</nav>';
                ?>
            </div>
            <footer>
                <p>©<?php $year = date("Y");
                echo $year; ?> Parking Lot Management | All Rights Reserved</p>
                <p><strong>Developed by Jevin Kalathiya</strong></p>
            </footer>
            <!-- =========== Scripts =========  -->
            <script src="assets/js/main.js"></script>
            <script>
                /*================================ To remove error / success msg ==================================*/
                document.addEventListener('DOMContentLoaded', function () { // Wait for the document to fully load
                    var sessionMsg = document.getElementById('error-form'); // Find the session message element
                    var sessionMsg_success = document.getElementById('success-form');
                    var sessionMsg_info = document.getElementById('info-form');
                    if (sessionMsg) { // If the session message element exists, set a timeout to remove it
                        setTimeout(function () {
                            sessionMsg.style.display = 'none';
                        }, 5000); // 5000 milliseconds = 5 seconds
                    }
                    if (sessionMsg_success) {
                        setTimeout(function () {
                            sessionMsg_success.style.display = 'none';
                        }, 5000); // 5000 milliseconds = 5 seconds
                    }
                    if (sessionMsg_info) {
                        setTimeout(function () {
                            sessionMsg_info.style.display = 'none';
                        }, 5000); // 5000 milliseconds = 5 seconds
                    }
                });
                function togglePasswordVisibility(index) { // To show / hide password
                    var passwordField = document.getElementById('passwordField_' + index); // Get the elements for the specific row using the index
                    var hiddenPassword = document.getElementById('hiddenPassword_' + index);
                    var icon = document.getElementById('icon_' + index);
                    if (passwordField.style.display === 'none') { // Toggle visibility
                        passwordField.style.display = 'inline';
                        hiddenPassword.style.display = 'none';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        passwordField.style.display = 'none';
                        hiddenPassword.style.display = 'inline';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                }
                function updateUser(searchQuery = '') { // Function to update total event data
                    $.ajax({
                        url: 'count.php',
                        method: 'GET',
                        data: {
                            type: 'total_user',
                            search: searchQuery // Pass search parameter
                        },
                        success: function (data) {
                            $('#count').text("(" + data + ")");
                        },
                        error: function () {
                            $('#count').text("(0)");
                        }
                    });
                }
                let searchText = $("#searchInput").val().trim(); // Run updateUser() after page reload with the existing search query
                if (searchText) {
                    updateUser(searchText);
                }
                updateUser(searchText); // Initial count update & if the initial count is blank then normal count // Run updateUser() after page reload with the existing search query
                setInterval(function () { // Keep updating count every 5 seconds with the latest search query
                    let currentSearch = $("#searchInput").val().trim();
                    updateUser(currentSearch);
                }, 5000); // 5 seconds
            </script>
            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>