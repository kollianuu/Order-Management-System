CREATE DATABASE IF NOT EXISTS order_tracking;
USE order_tracking;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100),
    client_email VARCHAR(100),
    product_name VARCHAR(100),
    order_status VARCHAR(50) DEFAULT 'Pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO orders (client_name, client_email, product_name, order_status)
VALUES ('Manjusha Kolli', 'manjusha@example.com', 'Smartphone', 'Pending');
