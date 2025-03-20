<?php
require 'vendor/autoload.php';
ini_set('display_errors', 'On'); // Not to show errors on page
require("connection.inc.php");
require("function.inc.php");
session_start();
if (isset($_SESSION["msg_veh"])) {
    unset($_SESSION["msg_veh"]);
}
if (isset($_SESSION['Admin_Login']) && $_SESSION['Admin_Login'] != '' && $_SESSION['Admin_Login'] == 'yes') {
} else { // Then nothing
    header('location: login');
    die();
}
if (isset($_SESSION['veh_msg'])) { // Check if the error / success message exists // Display and clear the error/ sucess message if page is reload
    $msg = $_SESSION['veh_msg'];
    if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
        unset($_SESSION['veh_msg']);
    }
}
if (isset($_GET['ac']) && $_GET['ac'] != '') { // For deleting the Event
    $action = get_safe_value($con, $_GET['ac']);
    if ($action == 'delete') {
        $url_code = get_safe_value($con, $_GET['requestid']); // requestid is nothing but url_code of database
        $delete_sql = "delete from entry_exit where vehicle_url_code='$url_code'";
        mysqli_query($con, $delete_sql);
        header('location: vehicle_entry');
        die();
    }
    if ($action == 'exit') {
        $type = get_safe_value($con, $_GET['vehtype']);
        $url_code = get_safe_value($con, $_GET['requestid']); // requestid = vehicle_url_code
        $exit_sql = "UPDATE entry_exit SET exit_date_time = NOW() WHERE vehicle_url_code = '$url_code'"; //Update exit time for this specific vehicle
        mysqli_query($con, $exit_sql);
        sleep(1); //Wait a short time to ensure exit time is updated
        $sql = "SELECT  entry_exit.*, vehicle_category.amount, vehicle_category.category_name FROM entry_exit INNER JOIN vehicle_category ON entry_exit.vehicle_type = vehicle_category.cat_url_code WHERE entry_exit.vehicle_url_code = '$url_code' AND vehicle_category.cat_status = 1 ORDER BY entry_exit.id DESC LIMIT 1";
        $result = mysqli_query($con, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $entry_time = strtotime($row['entry_date_time']); //Convert entry and exit time to timestamps
            $exit_time = strtotime($row['exit_date_time']);
            if (!$exit_time || $exit_time < $entry_time) { //Debugging: Check if timestamps are correct
                echo "<script>alert('Error: Exit time not recorded correctly.');</script>";
                exit();
            }
            $total_seconds = $exit_time - $entry_time; // Calculate total parked time in hours correctly
            $hours_parked = ceil($total_seconds / 3600);  // Use ceil to charge even for partial hours
            $charge_per_hour = (float) $row['amount']; //Get charge per hour, ensuring it's a valid float
            if ($hours_parked > 1) { //Calculate extra charges only if parked for more than 1 hour
                $extra_hours = $hours_parked - 1;
                $extra_amount = $extra_hours * $charge_per_hour;
                $entry_timestamp = strtotime($row['entry_date_time']); // Convert timestamps
                $exit_timestamp = !empty($row['exit_date_time']) ? strtotime($row['exit_date_time']) : $entry_timestamp + 3600;
                $entry_time = date('d-m-Y h:i A', $entry_timestamp); // Convert to 12-hour format
                $exit_time = date('d-m-Y h:i A', $exit_timestamp);
                $hours_parked = max(1, ceil(($exit_timestamp - $entry_timestamp) / 3600)); // Calculate total parked time (at least 1 hour)
                $amount = $row['amount']; // Calculate extra charge
                $charge_per_hour = (float) $amount;
                $extra_hours = max(0, $hours_parked - 1);
                $extra_charge = $extra_hours * $charge_per_hour;
                $total_charge = $charge_per_hour + $extra_charge;
                echo "<script>
                    var downloadID = '$url_code'; // Ensure PHP value is correctly passed
                     if (confirm('You have stayed for $hours_parked hours. Extra charge: Rs. $extra_charge. Do you want to print the receipt?')) {
                        var printWindow = window.open('download_ticket.php?did=' + downloadID, '_blank');
                        if (printWindow) {
                            printWindow.onload = function () { // Ensure the print function works when the page loads
                                printWindow.focus();
                                printWindow.print();
                            };
                        } else {
                            alert('Popup blocked! Please allow pop-ups to print the receipt.');
                        }
                    }
                    setTimeout(function() {
                        window.location.href = 'vehicle_entry'; // Redirect to main page
                    }, 1000); // Redirect after 2 seconds
                </script>";
                /* mysqli_query($con, "delete from entry_exit where vehicle_url_code='$url_code'"); */
            } else {
                echo "<script>
                    var downloadID = '$url_code'; // Ensure PHP value is correctly passed
                    if (confirm('You have stayed for $hours_parked hours. No extra charge. Do you want to print the receipt?')) {
                        var printWindow = window.open('download_ticket.php?did=' + downloadID, '_blank');
                        if (printWindow) {
                            printWindow.onload = function () { // Ensure the print function works when the page loads
                                printWindow.focus();
                                printWindow.print();
                            };
                        } else {
                            alert('Popup blocked! Please allow pop-ups to print the receipt.');
                        }
                    }
                    setTimeout(function() {
                        window.location.href = 'vehicle_entry'; // Redirect to main page
                    }, 1000); // Redirect after 2 seconds
                </script>";
            }
        } else {
            echo "<script>alert('Error: No record found for the vehicle.');</script>";
        }
    }
}
$records_per_page = 10; // Number of records to display per page // Pagination
$start = 0;
$page = 1;
if (isset($_GET['p'])) {
    $page = $_GET['p'];
    $start = ($page - 1) * $records_per_page;
}
$record = mysqli_num_rows(mysqli_query($con, "SELECT * FROM entry_exit"));
$total_pages = ceil($record / $records_per_page);
$sql_page = "SELECT entry_exit.*,vehicle_category.category_name,vehicle_category.cat_url_code FROM entry_exit,vehicle_category where entry_exit.vehicle_type=vehicle_category.cat_url_code and vehicle_category.cat_status=1 ORDER BY entry_exit.owner_name ASC LIMIT $start, $records_per_page";
$res_page = mysqli_query($con, $sql_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry/Exit | PLM</title>
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
                <li class="active">
                    <a href="vehicle_entry">
                        <span class="icon">
                            <img src="assets/imgs/parking_gate_blue.png" width="50">
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
                            <input type="text" name="s" id="searchInput" placeholder="Search here" value="<?php if (isset($_GET['s'])) {
                                echo $_GET['s'];
                            } ?>"
                                onkeypress="if(event.key === 'Enter'){ this.form.submit(); } updateParkedVehicle(this.value.trim());">
                            <ion-icon name="search-outline"></ion-icon>
                        </label>
                    </div>
                </form>
            </div>
            <!-- ================ Events Details List ================= -->
            <div class="details-d-e">
                <div class="container-d-e">
                    <div class="cardHeader">
                        <h2>Parked Vehicle Records <span id="count"></span></h2>
                        <div class="button-container">
                            <a href="manage_entry" class="btn-c">Add</a>
                        </div>
                    </div>
                    <?php if (isset($msg)) {
                        echo $msg;
                    } ?>
                    <table>
                        <thead>
                            <tr>
                                <td>Owner Name</td>
                                <td>Mobile No.</td>
                                <td>Vehicle Registration No.</td>
                                <td>Type</td>
                                <td>Entry Date/Time</td>
                                <td>Parked Slot</td>
                                <td>Paid</td>
                                <td colspan="3">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- For showing Search data and if no data found showing No results found and if not searched showing normal curd -->
                            <?php
                            $search_query = '';
                            if (isset($_GET['s']) && !empty(trim($_GET['s']))) {
                                $search = get_safe_value($con, $_GET['s']);
                                if (!empty($search)) {
                                    $search_query = "AND (entry_exit.owner_name LIKE '%$search%' OR entry_exit.mobile_no LIKE '%$search%' OR entry_exit.vehicle_no LIKE '%$search%')"; // Perform search query
                                    $record_query = "SELECT COUNT(*) as total FROM entry_exit INNER JOIN vehicle_category ON entry_exit.vehicle_type = vehicle_category.cat_url_code WHERE vehicle_category.cat_status=1 and 1=1 $search_query"; // Get the total number of records considering the search filter
                                    $record_result = mysqli_query($con, $record_query);
                                    if ($record_result) {
                                        $record_row = mysqli_fetch_assoc($record_result);
                                        $record = $record_row['total'];
                                        $total_pages = ceil($record / $records_per_page);
                                        $sql_page = "SELECT entry_exit.*, vehicle_category.category_name FROM entry_exit INNER JOIN vehicle_category ON entry_exit.vehicle_type = vehicle_category.cat_url_code WHERE vehicle_category.cat_status=1 and 1=1 $search_query ORDER BY entry_exit.owner_name ASC LIMIT $start, $records_per_page"; // Fetch the participants data with pagination and optional search filtering
                                        $res_page1 = mysqli_query($con, $sql_page);
                                        if (mysqli_num_rows($res_page1) > 0) {
                                            while ($items = mysqli_fetch_assoc($res_page1)) { // Loop through and display search results
                                                $date = date("d-m-Y h:i A", strtotime($items['entry_date_time'])); // Convert date formats
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($items['owner_name']); ?></td>
                                                    <td><?= htmlspecialchars($items['mobile_no']); ?></td>
                                                    <td><span><?= htmlspecialchars($items['vehicle_no']); ?></span></td>
                                                    <td><span><?= htmlspecialchars($items['category_name']); ?></span></td>
                                                    <td><span><?= $date; ?></span></td>
                                                    <td><span><?= htmlspecialchars($items['parked_spot']); ?></span></td>
                                                    <td><span><?= htmlspecialchars('₹ ' . $items['price']); ?></span></td>
                                                    <td><a href='manage_entry/<?= htmlspecialchars($items['vehicle_url_code']) ?>'
                                                            class='btn-c'>Edit</a></td>
                                                    <td><a href='?ac=exit&requestid=<?= htmlspecialchars($items['vehicle_url_code']) ?>&vehtype=<?= htmlspecialchars($items['category_name']) ?>'
                                                            class='btn-c exit'>Exit</a></td>
                                                    <td><a href='?ac=delete&requestid=<?= htmlspecialchars($items['vehicle_url_code']) ?>'
                                                            class='btn-c delete'>Delete</a></td>
                                                </tr>
                                                <?php
                                            }
                                        } elseif (!mysqli_num_rows($res_page1) > 0) { ?>
                                            <td colspan="8">No record Found</td>
                                        <?php }
                                    }
                                } elseif (!mysqli_num_rows($res_page1) > 0) { ?>
                                    <td colspan="8">No record Found</td>
                                <?php }
                            } else {
                                $query = "SELECT entry_exit.*,vehicle_category.category_name,vehicle_category.cat_url_code FROM entry_exit,vehicle_category where entry_exit.vehicle_type=vehicle_category.cat_url_code and vehicle_category.cat_status=1 ORDER BY entry_exit.owner_name ASC LIMIT $start, $records_per_page"; // Fetch all events // Normal CRUD operation (when no search is performed)
                                $query_run = mysqli_query($con, $query);
                                if (mysqli_num_rows($query_run) > 0) {
                                    while ($row = mysqli_fetch_assoc($res_page)) {
                                        $date = date("d-m-Y h:i A", strtotime($row['entry_date_time'])); // Convert date formats
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['owner_name']); ?></td>
                                            <td><?= htmlspecialchars($row['mobile_no']); ?></td>
                                            <td><span><?= htmlspecialchars($row['vehicle_no']); ?></span></td>
                                            <td><span><?= htmlspecialchars($row['category_name']); ?></span></td>
                                            <td><span><?= $date; ?></span></td>
                                            <td><span><?= htmlspecialchars($row['parked_spot']); ?></span></td>
                                            <td><span><?= htmlspecialchars('₹ ' . $row['price']); ?></span></td>
                                            <td><a href='manage_entry/<?= htmlspecialchars($row['vehicle_url_code']) ?>'
                                                    class='btn-c'>Edit</a></td>
                                            <td><a href='?ac=exit&requestid=<?= htmlspecialchars($row['vehicle_url_code']) ?>&vehtype=<?= htmlspecialchars($row['category_name']) ?>'
                                                    class='btn-c exit'>Exit</a></td>
                                            <td><a onclick="printTicket('<?= htmlspecialchars($row['vehicle_url_code']) ?>')"
                                                    class='btn-c' style="cursor: pointer;">Print</a></td>
                                            <td><a href='?ac=delete&requestid=<?= htmlspecialchars($row['vehicle_url_code']) ?>'
                                                    class='btn-c delete'>Delete</a></td>
                                        </tr>
                                        <?php
                                    }
                                } elseif (!mysqli_num_rows($query_run) > 0) { ?>
                                    <td colspan="8">No record Found</td>
                                <?php }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Hidden Modal -->
                <div id="printModal"
                    style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); width:80%; height:80%; background:white; border: 1px solid #ccc; box-shadow: 0px 0px 10px rgba(0,0,0,0.5); z-index:1000; padding:10px;">
                    <button onclick="closePrintModal()"
                        style="position:absolute; top:5px; right:10px; background:red; color:white; border:none; padding:5px 10px; cursor:pointer;">X</button>
                    <iframe id="printIframe" style="width:100%; height:90%; border:none;"></iframe>
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
                function updateParkedVehicle(searchQuery = '') { // Function to update total event data
                    $.ajax({
                        url: 'count.php',
                        method: 'GET',
                        data: {
                            type: 'parked_vehicle',
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
                let searchText = $("#searchInput").val().trim(); // Run updateParkedVehicle() after page reload with the existing search query
                if (searchText) {
                    updateParkedVehicle(searchText);
                }
                updateParkedVehicle(searchText); // Initial count update & if the initial count is blank then normal count // Run updateParkedVehicle() after page reload with the existing search query
                setInterval(function () { // Keep updating count every 5 seconds with the latest search query
                    let currentSearch = $("#searchInput").val().trim();
                    updateParkedVehicle(currentSearch);
                }, 5000); // 5 seconds
                function printTicket(downloadID) {
                    if (!downloadID) {
                        alert("Invalid Download ID");
                        return;
                    }
                    var iframe = document.createElement('iframe'); // Create a hidden iframe
                    iframe.style.position = 'absolute';
                    iframe.style.width = '0px';
                    iframe.style.height = '0px';
                    iframe.style.border = 'none';
                    iframe.src = 'download_ticket.php?did=' + downloadID + '&ac=print'; // Load the PDF inside the iframe
                    document.body.appendChild(iframe); // Append to body
                    iframe.onload = function () { // Trigger print when the PDF loads
                        iframe.contentWindow.print();
                        /* // Remove iframe after printing
                        setTimeout(function () {
                            document.body.removeChild(iframe);
                        }, 2000); */
                    };
                }
            </script>
            <!-- ====== ionicons ======= -->
            <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
            <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>