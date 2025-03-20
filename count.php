<?php
require('connection.inc.php');

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the 'type' parameter from the query string
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Initialize response variable
$response = '';


switch ($type) {

    /* =========== For index page to show total Slots, Available Slots ============== */
    case 'total_parking_slots':
        $totalSlots = 0;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Base Query
        $query = "SELECT slot_start, slot_end 
                  FROM parking_slot 
                  INNER JOIN vehicle_category 
                  ON parking_slot.slot_type = vehicle_category.cat_url_code 
                  AND vehicle_category.cat_status = 1 ";

        // Apply search filter if a search term is provided
        if (!empty($search)) {
            $query .= "WHERE vehicle_category.category_name LIKE '%$search%'";
        }

        $result = mysqli_query($con, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $start = (int)$row['slot_start'];
                $end   = (int)$row['slot_end'];
                $totalSlots += ($end - $start + 1);
            }
            echo $totalSlots; // Return count
        } else {
            echo "0"; // Default to 0 if query fails
        }

        break;


    case 'total_available_parking_slots':
        // Get all slots from the parking_slot table
        $sql = "SELECT slot_start, slot_end FROM parking_slot INNER JOIN vehicle_category ON parking_slot.slot_type = vehicle_category.cat_url_code 
            AND vehicle_category.cat_status = 1";
        $result = mysqli_query($con, $sql);

        $total_slots = 0;
        $occupied_slots = 0;


        while ($row = mysqli_fetch_assoc($result)) {
            $slot_start = $row['slot_start'];
            $slot_end = $row['slot_end'];

            // Calculate total slots in this range
            $range_total = ($slot_end - $slot_start) + 1;
            $total_slots += $range_total;

            // Count occupied slots for this range
            $occupied_sql = "SELECT COUNT(*) AS occupied_count FROM entry_exit 
                            WHERE parked_spot BETWEEN $slot_start AND $slot_end";
            $occupied_result = mysqli_query($con, $occupied_sql);
            $occupied_row = mysqli_fetch_assoc($occupied_result);
            $occupied_slots += $occupied_row['occupied_count'];
        }

        // Calculate available slots
        $available_slots = $total_slots - $occupied_slots;

        // Fetch the result and retrieve the count
        if ($result) {
            $response = $available_slots;
        }/*  else {
            $response = "Error: " . mysqli_error($con);
        } */
        break;

    case 'total_user':
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $query = "SELECT COUNT(*) AS total_user FROM user";

        // Apply search filter if a search term is provided
        if (!empty($search)) {
            $query .= " WHERE full_name LIKE '%$search%'";
        }

        $result = mysqli_query($con, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $response = $row['total_user'];
        }/*  else {
            $response = "Error: " . mysqli_error($con);
        } */
        break;

    case 'total_category':
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $query = "SELECT COUNT(*) AS total_category FROM vehicle_category";

        // Apply search filter if a search term is provided
        if (!empty($search)) {
            $query .= " WHERE category_name LIKE '%$search%'";
        }

        $result = mysqli_query($con, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $response = $row['total_category'];
        }/*  else {
            $response = "Error: " . mysqli_error($con);
        } */
        break;

    // For index page total parked vehicle & entry/exit page
    case 'parked_vehicle':
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $query = "SELECT COUNT(*) AS total_vehicle FROM entry_exit";

        // Apply search filter if a search term is provided
        if (!empty($search)) {
            $query .= " WHERE entry_exit.owner_name LIKE '%$search%' OR entry_exit.mobile_no LIKE '%$search%' OR entry_exit.vehicle_no LIKE '%$search%'";
        }

        $result = mysqli_query($con, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $response = $row['total_vehicle'];
        }/*  else {
            $response = "Error: " . mysqli_error($con);
        } */
        break;

    case 'total_income':
        $current_time = date("Y-m-d H:i:s");

        // Step 1: Calculate Base Income
        $query = "SELECT 
            (SELECT COALESCE(SUM(price), 0) FROM entry_exit WHERE exit_date_time IS NOT NULL) 
            + 
            (SELECT COALESCE(SUM(base_price), 0) FROM parking_record) 
          AS base_income";

        $result = mysqli_query($con, $query);
        $base_income = 0;

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $base_income = floatval($row['base_income']);
        }

        // Step 2: Calculate Extra Income from parking_record
        $query = "SELECT COALESCE(SUM(extra_charge), 0) AS extra_income FROM parking_record";
        $result = mysqli_query($con, $query);
        $extra_income = 0;

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $extra_income = floatval($row['extra_income']);
        }

        // Step 3: Calculate Extra Charges for Currently Parked Vehicles
        $query = "SELECT entry_date_time, price FROM entry_exit WHERE exit_date_time IS NULL";
        $result = mysqli_query($con, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $entry_time = strtotime($row['entry_date_time']);
                $base_price = floatval($row['price']);
                $current_time_sec = strtotime($current_time);

                // Check if parked for more than an hour
                $one_hour_later = $entry_time + 3600;

                if ($current_time_sec > $one_hour_later) {
                    // Calculate extra hours parked
                    $extra_hours = floor(($current_time_sec - $one_hour_later) / 3600);

                    // Assuming extra charges are the same as the base price per hour
                    $extra_income += $extra_hours * $base_price;
                }
            }
        }

        // Step 4: Calculate Total Income
        $total_income = $base_income + $extra_income;

        $response = number_format($total_income);

        break;



    default:
        $response = 'Access Denied';
        break;
}



// Close connection
mysqli_close($con);

// Output the response
echo $response;
