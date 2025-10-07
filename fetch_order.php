<?php
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_GET['order_id'])) {
    echo json_encode(['success'=>false,'message'=>'Order ID missing']);
    exit;
}

$order_id = $conn->real_escape_string($_GET['order_id']);
$sql = "SELECT *, DATE_ADD(order_date, INTERVAL 5 DAY) AS estimated_delivery FROM orders WHERE id='$order_id'";
$result = $conn->query($sql);

if($result && $result->num_rows>0){
    $order = $result->fetch_assoc();
    $order['order_date'] = date('Y-m-d H:i:s', strtotime($order['order_date']));
    $order['estimated_delivery'] = date('Y-m-d', strtotime($order['estimated_delivery']));
    echo json_encode(['success'=>true,'order'=>$order]);
} else {
    echo json_encode(['success'=>false,'message'=>'Order not found']);
}
?>
