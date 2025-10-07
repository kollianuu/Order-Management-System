# Order Management System

A web-based **Order Management System** built with PHP, MySQL, HTML, CSS, and JavaScript.  
This system allows administrators to manage customer orders, track delivery status, view delivered and canceled orders, and export order details as PDFs.

---

## Features

- **Admin Dashboard**
  - View total orders, pending, delivered, and canceled orders.
  - Filter orders by date, status, customer, or product.
  - Update order status and cancel orders.
  - Export order list as PDF.
<img width="1871" height="993" alt="image" src="https://github.com/user-attachments/assets/9277f545-a1b9-484e-9e02-4d2374fbe5c3" />

- **Order Tracking**
  - Customers can track order status using Order ID.
  - Visual progress bar showing order stages: Pending → Confirmed → Shipped → Out for Delivery → Delivered.
  - Cancel order option available before delivery.

- **Delivered Products Page**
  - List of all delivered products with customer details.

- **Canceled Products Page**
  - List of all canceled orders with reasons (if needed).

- **PDF Export**
  - Generate a PDF of order details from admin panel.

---

## Technology Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **PDF Generation:** TCPDF / FPDF (if used)
- **Email Notifications:** PHPMailer (if used)

---

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/Order-Management-System.git
