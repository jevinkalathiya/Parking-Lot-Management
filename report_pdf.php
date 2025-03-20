<?php
require('connection.inc.php');
require('vendor/autoload.php');
use Dompdf\Dompdf;
use Dompdf\Options;
use FontLib\Table\Type\head;
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!empty($_GET['alldata']) && $_GET['alldata'] == 1) {
        $sql = "SELECT * FROM parking_record ORDER BY id ASC";
        $query = mysqli_query($con, $sql);
    } else {
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            if (!strtotime($start_date) || !strtotime($end_date)) { // Validate dates
                die("Error: Invalid date format.");
            }
            if ($start_date > $end_date) {
                die("Error: Start Date cannot be after End Date.");
            }
            $sql = "SELECT * FROM parking_record WHERE DATE(entry_date_time) BETWEEN '$start_date' AND '$end_date' ORDER BY id ASC";
            $query = mysqli_query($con, $sql);
        } else {
            die("Error: Start Date and End Date are required.");
        }
    }
} else {
    die("Invalid Request");
}
extract($_POST);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);
$header = '
            <h2 align="center" class="header">
                Parking Slot Management
            </h2>';
$html = '
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Nunito:wght@200..1000&display=swap");
                body {
                    font-family: "Nunito", sans-serif;
                    font-size: 10px;
                    margin-bottom: 80px;
                }
                @page {
                    size: A4 landscape;
                    margin: 20px 20px 50px 20px;
                }
                .header {
                    background-color: #2667ff;
                    color: #fff;
                    font-size: 20px;
                    text-align: center;
                    padding: 8px;
                    border-radius: 10px;
                    margin-bottom: 10px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    table-layout: fixed;
                    page-break-inside: auto;
                }
                thead { 
                    display: table-header-group; /* Repeat headers on each new page */
                }
                tfoot { 
                    display: table-footer-group; /* Footer stays on each page */
                }
                th, td {
                    border: 1px solid #000;
                    padding: 6px;
                    text-align: center;
                    word-wrap: break-word;
                    font-size: 15px;
                }
                tr {
                    page-break-inside: avoid; /* Ensures rows do not break across pages */
                }
                th {
                    background-color: #87BFFF;
                }
                tr.total-income {
                    font-size: 16px;
                    font-weight: bold;
                }
                .page-break {
                    page-break-before: always;
                }
                footer {
                    position: fixed;
                    bottom: -20px;
                    left: 0;
                    right: 0;
                    border-radius: 10px;
                    background-color: #2667ff;
                    text-align: center;
                    font-size: 15px;
                    padding: 8px;
                    color: #fff;
                }
            </style>';
date_default_timezone_set('Asia/Kolkata');
$html .= '
            <footer>
                © ' . date("Y") . ' Parking Lot Management | All Rights Reserved<br>
                Generated on ' . date("d-m-Y h:i A") . '
            </footer>';
function getTableHeader() // **Function to add table header for new pages**
{
    return '
                <thead>
                    <tr>
                        <th style="width: 10%;">Sr No.</th>
                        <th style="width: 15%;">Name</th>
                        <th style="width: 12%;">Mobile No.</th>
                        <th style="width: 15%;">Vehicle No.</th>
                        <th style="width: 10%;">Type</th>
                        <th style="width: 6%;">Base Price</th>
                        <th style="width: 12%;">Entry Time</th>
                        <th style="width: 12%;">Exit Time</th>
                        <th style="width: 6%;">Total Hrs.</th>
                        <th style="width: 6%;">Extra Hrs.</th>
                        <th style="width: 10%;">Extra Charge</th>
                        <th style="width: 18%;">Amt Paid</th>
                    </tr>
                </thead>';
}
$html .= '<table>' . getTableHeader() . '<tbody>';
$total_income = 0;
$page_total_income = 0;
$row_count = 0;
$max_rows_per_page = 6;
if (mysqli_num_rows($query) > 0) {
    foreach ($query as $row) {
        $total_hours = $row['extra_hours'] + 1;
        $amt_paid = (float) $row['amt_paid'];
        $total_income += $amt_paid;
        $page_total_income += $amt_paid;
        $row_count++;
        $html .= '
                    <tr>
                        <td>' . $row_count . '</td>
                        <td>' . htmlspecialchars($row['owner_name']) . '</td>
                        <td>' . htmlspecialchars($row['mobile_no']) . '</td>
                        <td>' . htmlspecialchars($row['vehicle_no']) . '</td>
                        <td>' . htmlspecialchars($row['vehicle_type']) . '</td>
                        <td>' . htmlspecialchars('₹ ' . $row['base_price']) . '</td>
                        <td>' . htmlspecialchars(date("d-m-Y h:i A", strtotime($row['entry_date_time']))) . '</td>
                        <td>' . htmlspecialchars(date("d-m-Y h:i A", strtotime($row['exit_date_time']))) . '</td>
                        <td>' . $total_hours . '</td>
                        <td>' . htmlspecialchars($row['extra_hours']) . '</td>
                        <td>' . htmlspecialchars('₹ ' . $row['extra_charge']) . '</td>
                        <td>' . htmlspecialchars('₹ ' . number_format($amt_paid, 2)) . '</td>
                    </tr>';
        if ($row_count % $max_rows_per_page == 0) { // **If max rows reached, insert total and start new page**
            $html .= '
                        <tr class="total-income"  style="background-color: #FFFF00;">
                            <td colspan="11" style="text-align: right;">Total Income:</td>
                            <td><strong>₹ ' . number_format($page_total_income, 2) . '</strong></td>
                        </tr>
                    </tbody>
                    </table>
                    <div class="page-break"></div> <!-- Force new page -->
                    <table>' . getTableHeader() . '<tbody>';
            $page_total_income = 0; // Reset page income
        }
    }
}
$html .= ' // **Final total income for the last page**
            <tr class="total-income"  style="background-color: #FFFF00;">
                <td colspan="11" style="text-align: right;">Total Income:</td>
                <td><strong>₹ ' . number_format($page_total_income, 2) . '</strong></td>
            </tr>';
$html .= ' // **Overall total income**
            <tr class="total-income" style="background-color: #2667ff; color: #fff;">
                <td colspan="11" style="text-align: right;">Overall Total Income:</td>
                <td><strong>₹ ' . number_format($total_income, 2) . '</strong></td>
            </tr>';
$html .= '</tbody></table>';
$dompdf->loadHtml($header . $html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();
$dompdf->stream("Parking Report.pdf");
?>