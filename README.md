Order Management System

A simple Order Tracking System built using plain PHP, MySQL, HTML, CSS, and JavaScript. This system allows clients to view the status of their orders and lets the admin update order statuses, which automatically triggers email notifications to the clients.

Features

Client Interface:

View all orders placed.

Track the current status of each order.

Admin Interface:

View all orders.

Update order status (Pending → Shipped → Delivered).

Automatic email notification sent to the client when the status changes to "Shipped".

Email Notification:

Sends email to clients when their order is shipped.

Real-time Status Update:

Order status updated by admin is immediately visible to the client.

Project Structure
order-tracking-system/
│
├── index.php           # Client interface
├── admin.php           # Admin interface
├── db_connect.php      # Database connection
├── send_email.php      # Email notification logic
├── assets/
│   ├── style.css       # CSS styles
│   └── script.js       # JavaScript
├── README.md           # Project documentation
└── database.sql        # SQL file to create database and tables

Setup Instructions
1. Prerequisites

XAMPP / WAMP / LAMP server with PHP 7.x+

MySQL database

Web browser

2. Database Setup

Open phpMyAdmin or any MySQL client.

Create a new database, e.g., order_tracking.

Import the provided database.sql file or create the table manually:

CREATE TABLE orders (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_name VARCHAR(100) NULL,
    client_email VARCHAR(100) NULL,
    phone_number VARCHAR(10) NOT NULL,
    address VARCHAR(100) NOT NULL,
    product_name VARCHAR(100) NULL,
    order_status VARCHAR(50) DEFAULT 'Pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    estimated_delivery DATE NULL,
    delivered_date DATE NULL,
    canceled_date DATE NULL
);

3. Configure Database Connection

Open db_connect.php

Update your database credentials:

$servername = "localhost";
$username = "root";
$password = "";
$database = "order_tracking";

4. Email Configuration

Open send_email.php

Configure SMTP settings or use PHP mail():

$to = $client_email;
$subject = "Order Status Update";
$message = "Hello $client_name, your order for $product_name has been shipped.";
$headers = "From: your-email@example.com";
mail($to, $subject, $message, $headers);

5. Running the Project

Start your local server (XAMPP/WAMP).

Place the project folder inside htdocs (for XAMPP) or the web root.

Open your browser:

Client Interface: http://localhost/order-tracking-system/index.php

<img width="1853" height="963" alt="image" src="https://github.com/user-attachments/assets/930562fa-fca6-4f22-911f-4ddc4249497c" />

Admin Interface: http://localhost/order-tracking-system/admin.php

<img width="1051" height="822" alt="image" src="https://github.com/user-attachments/assets/273ffd8b-fb15-4264-91f3-f7547ca958e8" />

6. Usage

Admin: Update order status → Client receives email automatically.

Client: View order status updates in real-time.

7. Notes

Plain PHP only; no frameworks.

Basic CSS/JS for UI; can be extended.

Make sure your PHP mail function or SMTP settings are configured properly for email notifications.

8. Author

Kolli Anusha
[Github: Kollianuu](https://github.com/kollianuu)
