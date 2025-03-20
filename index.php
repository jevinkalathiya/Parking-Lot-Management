<?php
ini_set('display_errors', 'on'); // Not to show errors on page
require("./connection.inc.php");
require("./function.inc.php");
$base_path = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/";
session_start();
if (isset($_SESSION['Admin_Login']) && $_SESSION['Admin_Login'] != '' && $_SESSION['Admin_Login'] == 'yes') {
} else { // Then nothing
    header('location: login');
    die();
}
$current_time = date("Y-m-d H:i:s"); // Data For dount chart
$query = "SELECT 
        (SELECT COALESCE(SUM(price), 0) FROM entry_exit) 
        + 
        (SELECT COALESCE(SUM(base_price), 0) FROM parking_record) 
        AS base_income";
$result = mysqli_query($con, $query);
$base_income = 0;
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $base_income = $row['base_income'];
}
$query = "SELECT COALESCE(SUM(extra_charge), 0) AS extra_income FROM parking_record"; // Calculate extra charges from parking_record
$result = mysqli_query($con, $query);
$extra_income = 0;
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $extra_income = $row['extra_income'];
}
$query = "SELECT entry_date_time, price FROM entry_exit"; // Calculate extra charges for currently parked vehicles in entry_exit
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $entry_time = strtotime($row['entry_date_time']);
    $base_price = $row['price'];
    $one_hour_later = $entry_time + 3600; // 1 hour later
    if (strtotime($current_time) > $one_hour_later) { // If the vehicle has been parked for more than 1 hour, calculate extra charge
        $extra_income += floor((strtotime($current_time) - $one_hour_later) / 3600) * $base_price;
    }
}
$total_income = $base_income + $extra_income; // Calculate total income (Base Income + Extra Income)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PLM</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="./assets/imgs/favicon.png">
    <!-- ======= Google Chart ======= -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!-- ======= Ajax ====== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container-income {
            margin: auto;
            width: 95%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            flex-wrap: wrap;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .chart {
            width: 100%;
            height: 100%;
            flex-grow: 1;
        }

        .chart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
        }

        .chart-container:first-child {
            flex: 2;
            min-width: 500px;
        }

        .chart-container:last-child {
            flex: 1;
            min-width: 300px;
        }
        @media (max-width: 991px) {
            .chart {
                min-width: 350px;
                max-width: 500px;
            }
        }

        @media (max-width: 768px) {
            .container-income {
                flex-direction: column;
                align-items: center;
            }

            .chart-container {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .chart-container{
                text-align: center;
            }
            .chart-container:first-child {
                flex: 2;
                min-width: 395px;
                overflow-x: hidden;
            }

            .chart {
                width: 100%;
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li><br>
                    <span class="company-title">Parking Slot Management</span>
                </li>
                <li class="active">
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
            </div>
            <!-- ======================= Cards ================== -->
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers" id="totalSlots"></div> <!-- Getting Data from count.php using ajax -->
                        <div class="cardName">Total Slots</div>
                    </div>
                    <div class="iconBx">
                        <img src="assets/imgs/parking_slot.png" data-hover="assets/imgs/parking_slot_white.png"
                            alt="total-parking-slot" width="70">
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers" id="availableSlots"></div> <!-- Getting Data from count.php using ajax -->
                        <div class="cardName">Total Available Slots</div>
                    </div>
                    <div class="iconBx">
                        <img src="assets/imgs/free_parking.png" data-hover="assets/imgs/free_parking_white.png"
                            alt="available-parking-slot" width="70">
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers" id="totalVehicleParked"></div>
                        <!-- Getting Data from count.php using ajax -->
                        <div class="cardName">Total Vehicle Parked</div>
                    </div>
                    <div class="iconBx">
                        <img src="assets/imgs/total_vehicle_gray.png" data-hover="assets/imgs/total_vehicle_white.png"
                            alt="total-parked-car" width="70">
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers" id="totalIncome"></div> <!-- Getting Data from count.php using ajax -->
                        <div class="cardName">Total Income</div>
                    </div>
                    <div class="iconBx">
                        <img src="assets/imgs/money_gray.png" data-hover="assets/imgs/money_white.png" alt="total-money"
                            width="70">
                    </div>
                </div>
            </div>
            <div class="container-income">
                <div class="chart-container">
                    <h2>Parking Data for Vehicles Over the Last Week</h2>
                    <div id="chart_div" class="chart"></div>
                </div>
                <div class="chart-container">
                    <h2>Total Income Distribution</h2>
                    <div id="donut_chart" class="chart"></div>
                </div>
            </div>
        </div>
        <!-- =========== Scripts =========  -->
        <script>
            document.addEventListener("mouseover", function(event) {
                let card = event.target.closest(".card");
                if (!card) return;
                let img = card.querySelector(".iconBx img");
                if (!img) return;
                if (!img.dataset.original) { // Store the original source only once
                    img.dataset.original = img.src;
                }
                let hoverSrc = img.getAttribute("data-hover");
                if (!hoverSrc) return;
                img.src = hoverSrc;
                card.addEventListener("mouseleave", () => {
                    img.src = img.dataset.original;
                }, {
                    once: true
                });
            });
        </script>
        <script src="assets/js/main.js"></script>
        <script>
            function updateTotalsSlots() { // Function to update total slots data in card
                $.ajax({
                    url: 'count.php',
                    method: 'GET',
                    data: {
                        type: 'total_parking_slots'
                    },
                    success: function(data) {
                        $('#totalSlots').text(data);
                    },
                    error: function() {
                        $('#totalSlots').text('0');
                    }
                });
            }
            setInterval(updateTotalsSlots, 10000); // Update count every 0.1 seconds (100 ms)
            updateTotalsSlots(); // Initial load
            function updateAvailableSlots() { // Function to update total event data in card
                $.ajax({
                    url: 'count.php',
                    method: 'GET',
                    data: {
                        type: 'total_available_parking_slots'
                    },
                    success: function(data) {
                        $('#availableSlots').text(data);
                    },
                    error: function() {
                        $('#availableSlots').text('0');
                    }
                });
            }
            setInterval(updateAvailableSlots, 10000);
            updateAvailableSlots();

            function updateVehicleParked() { // Function to update total vehicle parked data in card
                $.ajax({
                    url: 'count.php',
                    method: 'GET',
                    data: {
                        type: 'parked_vehicle'
                    },
                    success: function(data) {
                        $('#totalVehicleParked').text(data);
                    },
                    error: function() {
                        $('#totalVehicleParked').text('0');
                    }
                });
            }
            setInterval(updateVehicleParked, 10000);
            updateVehicleParked();

            function updateTotalIncome() {
                $.ajax({
                    url: 'count.php',
                    method: 'GET',
                    data: {
                        type: 'total_income'
                    },
                    success: function(data) {
                        $('#totalIncome').text(data);
                    },
                    error: function() {
                        $('#totalIncome').text('0');
                    }
                });
            }
            setInterval(updateTotalIncome, 10000);
            updateTotalIncome();
        </script>
        <!-- Vehicle Parking Data (Last 6 Days) -->
        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['bar']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Date', <?php
                                require('connection.inc.php');
                                $vehicleTypes = [];
                                $sql = "SELECT DISTINCT category_name FROM vehicle_category
                            UNION 
                            SELECT DISTINCT vehicle_type FROM parking_record";
                                $result = mysqli_query($con, $sql);
                                while ($row = $result->fetch_assoc()) {
                                    $vehicleTypes[] = "'" . $row['category_name'] . "'";
                                }
                                echo implode(",", $vehicleTypes);
                                ?>],
                    <?php
                    $sql = "SELECT DATE(entry_date_time) AS parked_date, category_name, COUNT(*) AS total_count
                        FROM (
                            SELECT DATE(entry_exit.entry_date_time) AS entry_date_time, vehicle_category.category_name
                            FROM entry_exit
                            JOIN vehicle_category ON entry_exit.vehicle_type = vehicle_category.cat_url_code
                            UNION ALL
                            SELECT DATE(parking_record.entry_date_time) AS entry_date_time, parking_record.vehicle_type
                            FROM parking_record
                        ) AS combined_data
                        WHERE DATE(entry_date_time) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                        GROUP BY parked_date, category_name
                        ORDER BY parked_date, total_count DESC";
                    $data = [];
                    $result = mysqli_query($con, $sql);
                    while ($row = $result->fetch_assoc()) {
                        $date = $row['parked_date'];
                        $vehicle = $row['category_name'];
                        $count = $row['total_count'];
                        $data[$date][$vehicle] = $count;
                    }
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-$i days"));
                        echo "['$date', ";
                        foreach ($vehicleTypes as $vehicle) {
                            $vehicleName = trim($vehicle, "'");
                            echo isset($data[$date][$vehicleName]) ? $data[$date][$vehicleName] . "," : "0,";
                        }
                        echo "],";
                    }
                    ?>
                ]);
                var options = {
                    bars: 'vertical',
                    height: 500,
                    Width: 1000,
                    colors: ['#4285F4', '#EA4335', '#FBBC05', '#34A853', '#9C27B0', '#F57C00', '#7ed321'],
                    vAxis: {
                        format: '0',
                        gridlines: {
                            count: -1
                        }, // Adjusts gridline count dynamically
                        viewWindow: {
                            min: 0
                        } // Ensures no negative values if applicable
                    },
                    hAxis: {
                        title: 'Date',
                        textStyle: {
                            fontSize: 12
                        },
                        slantedText: true,
                        slantedTextAngle: 45
                    },
                    vAxis: {
                        title: 'Count',
                        minValue: 0
                    },
                    legend: {
                        position: 'top',
                        alignment: 'center'
                    },
                    backgroundColor: 'transparent'
                };
                var chart = new google.charts.Bar(document.getElementById('chart_div'));
                chart.draw(data, google.charts.Bar.convertOptions(options));
            }
            window.addEventListener('resize', drawChart);
        </script>
        <!-- Total Income Chart (i.e Base income + Extra income) -->
        <script type="text/javascript">
            google.charts.load("current", {
                packages: ["corechart"]
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Income Type', 'Amount'],
                    ['Base Income', <?php echo $base_income; ?>],
                    ['Extra Income', <?php echo $extra_income; ?>]
                ]);
                var options = {
                    pieHole: 0.4,
                    colors: ['#2667ff', '#7ed321'],
                    chartArea: {
                        width: '90%',
                        height: '80%'
                    },
                    legend: {
                        position: 'bottom'
                    }
                };
                var chart = new google.visualization.PieChart(document.getElementById('donut_chart'));
                chart.draw(data, options);
            }
            window.addEventListener('resize', drawChart); // Responsive Redraw on Resize
        </script>
        <!-- ====== ionicons ======= -->
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>