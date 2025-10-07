<?php
include 'db_connect.php';
include 'send_email.php';

$statusMessage = '';
$redirectUrl = 'admin.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    $reason = isset($_POST['reason']) ? $conn->real_escape_string($_POST['reason']) : '';

    // Fetch client info
    $sql = "SELECT client_email, client_name, product_name, order_date, estimated_delivery FROM orders WHERE id='$order_id'";
    $result = $conn->query($sql);
    $order = $result->fetch_assoc();

    if ($order) {
        $client_email = $order['client_email'];
        $client_name = $order['client_name'];
        $product_name = $order['product_name'];
        $estimated_delivery = $order['estimated_delivery'];

        // Prepare update query based on status
        if ($new_status === 'Shipped' && empty($estimated_delivery)) {
            $estimated_delivery_date = date('Y-m-d', strtotime($order['order_date'] . ' +5 days'));
            $update = "UPDATE orders SET order_status='$new_status', estimated_delivery='$estimated_delivery_date' WHERE id='$order_id'";
        } elseif ($new_status === 'Delivered') {
            $update = "UPDATE orders SET order_status='$new_status', delivered_date=NOW() WHERE id='$order_id'";
        } elseif ($new_status === 'Canceled') {
            $update = "UPDATE orders SET order_status='$new_status', cancel_reason='$reason' WHERE id='$order_id'";
        } else {
            $update = "UPDATE orders SET order_status='$new_status' WHERE id='$order_id'";
        }

        if ($conn->query($update)) {
            // Send email notification
            sendStatusEmail($client_email, $client_name, $order_id, $new_status, $product_name);

            // Set status message and redirect URL
            if ($new_status == 'Delivered') {
                $statusMessage = "✅ Order #$order_id marked as Delivered!";
                $redirectUrl = 'delivered_products.php';
            } elseif ($new_status == 'Canceled') {
                $statusMessage = "⚠️ Order #$order_id has been Canceled! Reason: $reason";
                $redirectUrl = 'admin.php';
            } else {
                $statusMessage = "✅ Order #$order_id updated successfully!";
                $redirectUrl = 'admin.php';
            }
        } else {
            $statusMessage = "❌ Error updating Order #$order_id!";
            $redirectUrl = 'admin.php';
        }
    } else {
        $statusMessage = "❌ Order not found!";
        $redirectUrl = 'admin.php';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Update Status</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: rgba(0,0,0,0.2); margin:0; }
        #messageModal {
            display: block;
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.5);
            z-index:1000;
        }
        #messageModal .modal-content {
            background: #fff;
            padding: 25px 20px;
            border-radius: 12px;
            width: 350px;
            max-width: 90%;
            margin: 150px auto;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        #messageModal h3 { margin-bottom: 20px; font-size: 16px; word-wrap: break-word; }
        #messageModal button {
            background: #4CAF50;
            color:#fff;
            padding:10px 20px;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-weight:600;
            font-size: 14px;
        }
        #messageModal button:hover { background:#45a049; }
        .error-btn { background:#e74c3c; }
        .error-btn:hover { background:#c0392b; }
    </style>
</head>
<body>
    <div id="messageModal">
        <div class="modal-content">
            <h3><?php echo $statusMessage; ?></h3>
            <button onclick="redirect()"><?php echo strpos($statusMessage,'❌')!==false ? 'Close' : 'OK'; ?></button>
        </div>
    </div>

    <script>
        function redirect(){
            window.location.href = "<?php echo $redirectUrl; ?>";
        }
    </script>
</body>
</html>
