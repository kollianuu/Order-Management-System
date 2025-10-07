<?php
include 'db_connect.php';
require_once('tcpdf/tcpdf.php'); // Path to TCPDF library

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Order Tracking System');
$pdf->SetTitle('Orders Report');
$pdf->SetHeaderData('', 0, 'Orders Report', '');
$pdf->setHeaderFont(Array('helvetica', '', 12));
$pdf->setFooterFont(Array('helvetica', '', 10));
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// Fetch orders from DB
$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = $conn->query($sql);

// HTML table for PDF
$html = '<h2>Orders Report</h2>
<table border="1" cellpadding="5">
<tr>
    <th>Order ID</th>
    <th>Customer Name</th>
    <th>Customer Email</th>
    <th>Product</th>
    <th>Status</th>
    <th>Order Date</th>
</tr>';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>'.$row['id'].'</td>
        <td>'.$row['client_name'].'</td>
        <td>'.$row['client_email'].'</td>
        <td>'.$row['product_name'].'</td>
        <td>'.$row['order_status'].'</td>
        <td>'.$row['order_date'].'</td>
    </tr>';
}

$html .= '</table>';

// Output HTML content to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Send PDF to browser
$pdf->Output('orders_report.pdf', 'I'); // I = Inline display, D = download
exit;
?>
