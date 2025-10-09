<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendStatusEmail($toEmail, $toName, $orderId, $status, $productName, $customBody = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kollianuu3530@gmail.com'; // Gmail
        $mail->Password   = 'devhduuoyfoteayk';        // App Password
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
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = "Order #$orderId - $status Update";
        $mail->Body    = "
        <div style='font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; border-radius:8px;'>
            <h2 style='color:#4CAF50;'>Order Update Notification</h2>
            <p>Dear <b>$toName</b>,</p>
            $customBody
            <hr>
            <p style='font-size:12px; color:#888;'>This is an automated email from Order Tracking System.</p>
        </div>";
        $mail->AltBody = strip_tags($customBody);

        $mail->SMTPDebug  = 2; // Show detailed debug output
        $mail->Debugoutput = 'html';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

?>
