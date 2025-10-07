<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to send order status email
function sendStatusEmail($toEmail, $toName, $orderId, $status, $productName) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kollianusha3530@gmail.com'; // your Gmail
        $mail->Password = 'nnmaeqzppuoufclr'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Sender info
        $mail->setFrom('kollianusha3530@gmail.com', 'Order Tracking System');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Order #$orderId Status Updated";
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; border-radius:8px;'>
            <h2 style='color:#4CAF50;'>Order Update Notification</h2>
            <p>Dear <b>$toName</b>,</p>
            <p>Your order for <b>$productName</b> has been updated to the status: 
            <span style='color:#2196F3; font-weight:bold;'>$status</span>.</p>
            <p>Order ID: <b>$orderId</b></p>
            <p>Thank you for shopping with us!</p>
            <hr>
            <p style='font-size:12px; color:#888;'>This is an automated email from Order Tracking System.</p>
        </div>";

        // Send mail
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
