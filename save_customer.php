<?php
include 'db_connect.php';
include 'send_email.php'; // optional, if you want email notifications

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escape user inputs
    $name     = $conn->real_escape_string($_POST['client_name']);
    $email    = $conn->real_escape_string($_POST['client_email']);
    $phone    = $conn->real_escape_string($_POST['phone_number']);
    $address  = $conn->real_escape_string($_POST['address']);
    $product  = $conn->real_escape_string($_POST['product_name']);
    $status   = $conn->real_escape_string($_POST['order_status']);
    $estimated_delivery = $conn->real_escape_string($_POST['estimated_delivery']);

    // Insert into orders table
    $sql = "INSERT INTO orders (client_name, client_email, phone_number, address, product_name, order_status, estimated_delivery, order_date)
            VALUES ('$name', '$email', '$phone', '$address', '$product', '$status', '$estimated_delivery', NOW())";

    if ($conn->query($sql)) {
        // Optional: send email notification
        // sendStatusEmail($email, $name, $conn->insert_id, $status, $product);

        // Redirect to admin page (order tracking)
        header("Location: admin.php?success=1");
        exit;
    } else {
        // Redirect with error message
        header("Location: create_customer.php?error=" . urlencode($conn->error));
        exit;
    }
}
?>
