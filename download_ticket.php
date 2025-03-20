<?php
require 'vendor/autoload.php';
require("connection.inc.php");
if (!isset($_GET['did'])) {
    die("No Download ID provided.");
}
$url_code = $_GET['did'];
$sql = mysqli_query($con, "SELECT  entry_exit.*, vehicle_category.amount, vehicle_category.category_name 
                FROM entry_exit 
                INNER JOIN vehicle_category 
                ON entry_exit.vehicle_type = vehicle_category.cat_url_code 
                WHERE entry_exit.vehicle_url_code = '$url_code' 
                AND vehicle_category.cat_status = 1
                ORDER BY entry_exit.id DESC LIMIT 1");
$current_exit_time = date('d-m-Y h:i A', time()); // i.e is system time
if ($row = mysqli_fetch_assoc($sql)) {
    $entry_time = strtotime($row['entry_date_time']); //Convert entry and exit time to timestamps
    $exit_time = strtotime($row['exit_date_time']);
    $total_seconds = $exit_time - $entry_time; // Calculate total parked time in hours correctly
    $hours_parked = ceil($total_seconds / 3600);  // Use ceil to charge even for partial hours
    $charge_per_hour = (float) $row['amount']; //Get charge per hour, ensuring it's a valid float
    if ($hours_parked > 1) { //Calculate extra charges only if parked for more than 1 hour
        $extra_hours = $hours_parked - 1;
        $extra_amount = $extra_hours * $charge_per_hour;
        $entry_timestamp = strtotime($row['entry_date_time']); // Convert timestamps
        $exit_timestamp = (!empty($row['exit_date_time']) && $row['exit_date_time'] !== '0000-00-00 00:00:00') ? strtotime($row['exit_date_time']) : $entry_timestamp + 3600;
        $entry_time = date('d-m-Y h:i A', $entry_timestamp); // Convert to 12-hour format
        $exit_time = date('d-m-Y h:i A', $exit_timestamp);
        $hours_parked = max(1, ceil(($exit_timestamp - $entry_timestamp) / 3600)); // Calculate total parked time (at least 1 hour)
        $vehicle_no = $row['vehicle_no'];
        $owner_name = $row['owner_name'];
        $owner_mobileno = $row['mobile_no'];
        $amount = $row['amount'];
        $vehicle_type = $row['category_name'];
        $parked_spot = $row['parked_spot'];
        $charge_per_hour = (float) $amount; // Calculate extra charge
        $extra_hours = max(0, $hours_parked - 1);
        $extra_charge = $extra_hours * $charge_per_hour;
        $total_charge = $charge_per_hour + $extra_charge;
        $pdf = new TCPDF(); // Generate PDF
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 16); // Ticket Title
        $pdf->Cell(190, 10, 'Parking Ticket', 1, 1, 'C');
        $pdf->SetFont('courier', '',12); // Ticket Content
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Owner Name:</b> ' . $owner_name, 1, 0, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Vehicle No:</b> ' . $vehicle_no, 1, 1, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Vehicle Type:</b> ' . $vehicle_type, 1, 0, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Slot No:</b> ' . $parked_spot, 1, 1, false, true, 'L');
        $pdf->writeHTMLCell(95, 10.6, '', '', '<b>Entry Time:</b> ' . $entry_time, 1, 0, false, true, 'L'); // **Entry & Estimated Exit Time Row**
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Required Exit Time:</b> ' . $exit_time, 1, 1, false, true, 'L');
        if(!isset($_GET['ac']) || $_GET['ac'] !== "print"){
            $pdf->writeHTMLCell(190, 8, '', '', '<b>Exit Time:</b> ' . $current_exit_time, 1, 1, false, true, 'C');
        }
        $pdf->SetFont('courier', '', 10);
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Total Hours:</b> ' . $hours_parked, 1, 0, false, true, 'L'); // Parking Charges Section
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Base Charge: Rs.</b> ' . $charge_per_hour, 1, 1, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Extra Hours:</b> ' . $extra_hours, 1, 0, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Extra Charge: Rs.</b> ' . $extra_charge, 1, 1, false, true, 'L');
        $pdf->SetFont('courier', 'B', 14);
        $pdf->Cell(190, 10, "Total Amount: Rs. $total_charge", 1, 1, 'C');
        $pdf->Output("Parking_Ticket_$vehicle_no.pdf", 'I'); // 'I' opens in the browser instead of downloading 'D' // Output the PDF
        if (!isset($_GET['ac']) || $_GET['ac'] !== "print") {
            mysqli_query($con, "DELETE FROM entry_exit WHERE vehicle_url_code='$url_code'");
            mysqli_query($con, "Insert into parking_record (owner_name, mobile_no, vehicle_no, base_price, extra_charge, amt_paid, extra_hours, vehicle_type, entry_date_time, exit_date_time, parked_spot) values('$owner_name','$owner_mobileno','$vehicle_no','$charge_per_hour','$extra_charge','$total_charge','$extra_hours','$vehicle_type','".$row['entry_date_time']."','".$row['exit_date_time']."','$parked_spot')");
        }
    } else {
        $entry_time = strtotime($row['entry_date_time']); //Convert entry and exit time to timestamps
        $exit_time = strtotime($row['exit_date_time']);
        $total_seconds = $exit_time - $entry_time; // Calculate total parked time in hours correctly
        $hours_parked = ceil($total_seconds / 3600);  // Use ceil to charge even for partial hours
        $charge_per_hour = (float) $row['amount']; //Get charge per hour, ensuring it's a valid float
        $extra_hours = $hours_parked - 1;
        $extra_amount = $extra_hours * $charge_per_hour;
        $entry_timestamp = strtotime($row['entry_date_time']); // Convert timestamps
        $exit_timestamp = (!empty($row['exit_date_time']) && $row['exit_date_time'] !== '0000-00-00 00:00:00') ? strtotime($row['exit_date_time']) : $entry_timestamp + 3600;
        $entry_time = date('d-m-Y h:i A', $entry_timestamp); // Convert to 12-hour format
        $exit_time = date('d-m-Y h:i A', $exit_timestamp);
        $hours_parked = max(1, ceil(($exit_timestamp - $entry_timestamp) / 3600)); // Calculate total parked time (at least 1 hour)
        $vehicle_no = $row['vehicle_no'];
        $owner_name = $row['owner_name'];
        $owner_mobileno = $row['mobile_no'];
        $amount = $row['amount'];
        $vehicle_type = $row['category_name'];
        $parked_spot = $row['parked_spot'];
        $charge_per_hour = (float) $amount; // Calculate extra charge
        $extra_hours = max(0, $hours_parked - 1);
        $extra_charge = $extra_hours * $charge_per_hour;
        $total_charge = $charge_per_hour + $extra_charge;
        $pdf = new TCPDF(); // Generate PDF
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 16); // Ticket Title
        $pdf->Cell(190, 10, 'Parking Ticket', 1, 1, 'C');
        $pdf->SetFont('courier', '',12); // Ticket Content
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Owner Name:</b> ' . $owner_name, 1, 0, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Vehicle No:</b> ' . $vehicle_no, 1, 1, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Vehicle Type:</b> ' . $vehicle_type, 1, 0, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Slot No:</b> ' . $parked_spot, 1, 1, false, true, 'L');
        $pdf->writeHTMLCell(95, 10.6, '', '', '<b>Entry Time:</b> ' . $entry_time, 1, 0, false, true, 'L'); // **Entry & Estimated Exit Time Row**
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Required Exit Time:</b> ' . $exit_time, 1, 1, false, true, 'L');
        if(!isset($_GET['ac']) || $_GET['ac'] !== "print"){
            $pdf->writeHTMLCell(190, 8, '', '', '<b>Exit Time:</b> ' . $current_exit_time, 1, 1, false, true, 'C');
        }
        $pdf->SetFont('courier', '', 10);
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Total Hours:</b> ' . $hours_parked, 1, 0, false, true, 'L'); // Parking Charges Section
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Base Charge: Rs.</b> ' . $charge_per_hour, 1, 1, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Extra Hours:</b> ' . $extra_hours, 1, 0, false, true, 'L');
        $pdf->writeHTMLCell(95, 10, '', '', '<b>Extra Charge: Rs.</b> ' . $extra_charge, 1, 1, false, true, 'L');
        $pdf->SetFont('courier', 'B', 14);
        $pdf->Cell(190, 10, "Total Amount: Rs. $total_charge", 1, 1, 'C');
        $pdf->Output("Parking_Ticket_$vehicle_no.pdf", 'I'); // 'I' opens in the browser instead of downloading 'D' // Output the PDF
        if (!isset($_GET['ac']) || $_GET['ac'] !== "print") {
            mysqli_query($con, "DELETE FROM entry_exit WHERE vehicle_url_code='$url_code'");
            mysqli_query($con, "Insert into parking_record (owner_name, mobile_no, vehicle_no, base_price, extra_charge, amt_paid, extra_hours, vehicle_type, entry_date_time, exit_date_time, parked_spot) values('$owner_name','$owner_mobileno','$vehicle_no','$charge_per_hour','$extra_charge','$total_charge','$extra_hours','$vehicle_type','".$row['entry_date_time']."','".$row['exit_date_time']."','$parked_spot')");
        }
    }
}
?>
<script>
    window.onload = function () {
        window.print(); // Auto-trigger print dialog
    };
</script>