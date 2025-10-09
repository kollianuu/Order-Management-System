<?php
// Detect AJAX as early as possible and start buffering BEFORE any include to avoid stray output
$__isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
// Track if we've already emitted a JSON response
$__jsonResponded = false;
if ($__isAjax) {
    @ini_set('display_errors', '0');
    @error_reporting(0);
    ob_start();
    // Ensure we always return JSON even on fatal errors
    register_shutdown_function(function() use (&$__jsonResponded){
        // If there's already content in buffer, do nothing (normal flow handled elsewhere)
        $buffer = ob_get_contents();
        if ($__jsonResponded) { return; }
        if ($buffer !== '' && $buffer !== false) { return; }
        // If we reach here, a fatal error likely occurred; include details to aid debugging
        $lastError = error_get_last();
        $message = 'Server error. Please try again.';
        if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Provide concise error for debugging
            $message = 'Fatal: ' . $lastError['message'];
            error_log('update_status.php fatal: ' . $lastError['message'] . ' in ' . $lastError['file'] . ':' . $lastError['line']);
        }
        while (ob_get_level() > 0) { @ob_end_clean(); }
        if (!headers_sent()) { header('Content-Type: application/json'); }
        echo json_encode(['success'=>false,'message'=>$message,'redirect'=>'admin.php']);
    });
}

include 'db_connect.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$statusMessage = '';
$redirectUrl = 'admin.php';
$isAjax = $__isAjax; // use value computed before includes

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Make MySQLi throw exceptions so we can return proper JSON errors
    if (function_exists('mysqli_report')) { mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); }
    try {
        $order_id = $conn->real_escape_string($_POST['order_id']);
        $new_status = $conn->real_escape_string($_POST['status']);
        $reason = isset($_POST['reason']) ? $conn->real_escape_string($_POST['reason']) : '';

        if (!$order_id || !$new_status) { throw new Exception('Order ID or status missing'); }

        // Fetch client info
        $sql = "SELECT client_email, client_name, product_name, order_date, estimated_delivery FROM orders WHERE id='$order_id'";
        $result = $conn->query($sql);
        $order = $result ? $result->fetch_assoc() : null;

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
            $update = "UPDATE orders SET order_status='$new_status', cancel_reason='$reason', canceled_date=NOW() WHERE id='$order_id'";
        } else {
            $update = "UPDATE orders SET order_status='$new_status' WHERE id='$order_id'";
        }

        if ($conn->query($update)) {
            // Prepare email body
            if ($new_status === 'Delivered') {
                $email_body = "
                    <p>Your order for <b>$product_name</b> has been <b>Delivered</b> successfully!</p>
                    <p>We hope you enjoy your purchase. Thank you for shopping with us!</p>
                ";
            } elseif ($new_status === 'Shipped') {
                $estimated_delivery_text = !empty($estimated_delivery) ? $estimated_delivery : $estimated_delivery_date;
                $email_body = "
                    <p>Your order for <b>$product_name</b> has been <b>Shipped</b>.</p>
                    <p>Estimated Delivery Date: <b>$estimated_delivery_text</b></p>
                ";
            } elseif ($new_status === 'Canceled') {
                $email_body = "
                    <p>Your order for <b>$product_name</b> has been <b>Canceled</b>.</p>
                    <p>Reason: <i>$reason</i></p>
                ";
            } else {
                $email_body = "
                    <p>Your order for <b>$product_name</b> has been updated to status: <b>$new_status</b>.</p>
                ";
            }

            // Send email notification with PHPMailer
            $mail = new PHPMailer(true);
            $emailSent = false; $emailError = '';
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'kollianuu3530@gmail.com'; // your Gmail
                $mail->Password   = 'devhduuoyfoteayk';        // your Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
                
                // Fix SSL certificate verification issues (common with XAMPP)
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('kollianuu3530@gmail.com', 'Order Tracking System');
                $mail->addAddress($client_email, $client_name);

                $mail->isHTML(true);
                $mail->Subject = "Order #$order_id - $new_status Update";
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; border-radius:8px;'>
                        <h2 style='color:#4CAF50;'>Order Update Notification</h2>
                        <p>Dear <b>$client_name</b>,</p>
                        $email_body
                        <hr>
                        <p style='font-size:12px; color:#888;'>This is an automated email from Order Tracking System.</p>
                    </div>";
                $mail->AltBody = strip_tags($email_body);

                // Debug output only for non-AJAX (page) requests
                if ($isAjax) {
                    $mail->SMTPDebug  = 0;
                    $mail->Debugoutput = 'error_log';
                } else {
                    $mail->SMTPDebug  = 2;
                    $mail->Debugoutput = 'html';
                }

                $mail->send();
                $emailSent = true;
            } catch (Exception $e) {
                $emailError = "Email could not be sent. PHPMailer Error: {$mail->ErrorInfo}";
                error_log($emailError);
                $emailSent = false;
            }

            // Set status message and redirect URL
            $emailStatus = isset($emailSent) ? ($emailSent ? " 📧 Email sent" : " ⚠️ Email failed") : "";
            
            if ($new_status == 'Delivered') {
                $statusMessage = "✅ Order #$order_id marked as Delivered!$emailStatus";
                $redirectUrl = 'delivered_products.php';
            } elseif ($new_status == 'Canceled') {
                $statusMessage = "⚠️ Order #$order_id has been Canceled! Reason: $reason$emailStatus";
                $redirectUrl = 'admin.php';
            } else {
                $statusMessage = "✅ Order #$order_id updated successfully!$emailStatus";
                $redirectUrl = 'admin.php';
            }
            
            // Add email error details only when there is an actual error
            if (!empty($emailError)) {
                $statusMessage .= "<br><small style='color:red;'>Email Error: " . htmlspecialchars($emailError) . "</small>";
            }
        } else {
            $statusMessage = "❌ Error updating Order #$order_id!";
            $redirectUrl = 'admin.php';
        }
    } else {
        $statusMessage = "❌ Order not found!";
        $redirectUrl = 'admin.php';
    }
    
    // Return JSON response for AJAX requests
    if ($isAjax) {
        // Discard any previous output (e.g., warnings or SMTP debug) to keep JSON valid
        while (ob_get_level() > 0) { @ob_end_clean(); }
        if (!headers_sent()) { header('Content-Type: application/json'); }
        $success = strpos($statusMessage, '❌') === false;
        echo json_encode([
            'success' => $success,
            'message' => strip_tags($statusMessage),
            'redirect' => $redirectUrl
        ]);
        $__jsonResponded = true;
        exit;
    }
    } catch (Exception $e) {
        error_log('update_status.php error: ' . $e->getMessage());
        if ($isAjax) {
            while (ob_get_level() > 0) { @ob_end_clean(); }
            if (!headers_sent()) { header('Content-Type: application/json'); }
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'redirect' => $redirectUrl]);
            $__jsonResponded = true;
            exit;
        } else {
            $statusMessage = '❌ ' . htmlspecialchars($e->getMessage());
        }
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
