<?php
require 'vendor/autoload.php'; // Dompdf
include 'db.php'; // database connection

use Dompdf\Dompdf;

$dompdf = new Dompdf();

/* ================= FETCH DATA ================= */
$result = $conn->query("SELECT * FROM sales");

if(!$result){
    die("Database Error: " . $conn->error);
}

/* ================= BUILD HTML ================= */
$html = "
<h2 style='text-align:center;'>Sales Report</h2>

<table border='1' width='100%' cellpadding='8' cellspacing='0'>
<tr style='background:#f2f2f2;'>
<th>Product</th>
<th>Market</th>
<th>Quantity</th>
<th>Total (GHS)</th>
<th>Date</th>
</tr>
";

/* ================= LOOP DATA ================= */
while($row = $result->fetch_assoc()){
    $html .= "
    <tr>
        <td>{$row['product_name']}</td>
        <td>{$row['market_name']}</td>
        <td>{$row['quantity']}</td>
        <td>{$row['total_price']}</td>
        <td>{$row['sale_date']}</td>
    </tr>
    ";
}

$html .= "</table>";

/* ================= GENERATE PDF ================= */
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ================= DOWNLOAD ================= */
$dompdf->stream("sales_report.pdf", ["Attachment" => true]);
?>