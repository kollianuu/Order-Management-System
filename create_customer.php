<?php
$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create New Customer Order</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #e0f7fa, #f8f8f8);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-image: radial-gradient(rgba(0,0,0,0.02) 1px, transparent 1px);
    background-size: 20px 20px;
    z-index: -1;
}
.form-container {
    background: #ffffff;
    padding: 30px 35px;
    border-radius: 15px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}
h2 {
    color: #333;
    margin-bottom: 25px;
    font-weight: 700;
    text-align: center;
}
form {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 15px 15px;
    align-items: center;
}
label {
    font-weight: 600;
    color: #555;
    display: flex;
    align-items: center;
    gap: 8px;
}
.input-field {
    position: relative;
    width: 100%;
}
.input-field i {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    color: #888;
}
.input-field input,
.input-field select,
.input-field textarea {
    width: 100%;
    padding: 10px 12px 10px 35px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}
textarea { resize: none; height: 70px; }
input:focus, select:focus, textarea:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76,175,80,0.3);
    outline: none;
}
button {
    grid-column: 1 / -1;
    padding: 12px;
    background: #2196F3;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.3s ease, transform 0.2s ease;
}
button:hover {
    background: #1976D2;
    transform: translateY(-2px);
}
/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}
.modal-content {
    background: #fff;
    padding: 25px 20px;
    border-radius: 12px;
    width: 320px;
    text-align: center;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    animation: popIn 0.4s ease;
}
@keyframes popIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.modal-content h3 {
    margin-bottom: 15px;
    color: #27ae60;
}
.modal-content button {
    padding: 8px 16px;
    background: #4CAF50;
    color: white;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}
.modal-content button:hover {
    background: #388E3C;
    transform: translateY(-2px);
}
@media (max-width: 600px) {
    form { grid-template-columns: 1fr; }
    label { margin-bottom: 5px; }
    button { grid-column: 1; }
}
</style>
</head>
<body>

<div class="form-container">
    <h2>Create New Customer Order</h2>
    <form method="POST" action="save_customer.php" id="orderForm" onsubmit="return confirmSubmission()">

        <label for="client_name"><i class="fa fa-user"></i> Name</label>
        <div class="input-field">
            <input type="text" name="client_name" id="client_name" placeholder="Customer Name" required>
        </div>

        <label for="client_email"><i class="fa fa-envelope"></i> Email</label>
        <div class="input-field">
            <input type="email" name="client_email" id="client_email" placeholder="Customer Email" required>
        </div>

        <label for="phone_number"><i class="fa fa-phone"></i> Phone</label>
        <div class="input-field">
            <input type="text" name="phone_number" id="phone_number" placeholder="Phone Number" required>
        </div>

        <label for="address"><i class="fa fa-map-marker-alt"></i> Address</label>
        <div class="input-field">
            <textarea name="address" id="address" placeholder="Customer Address" required></textarea>
        </div>

        <label for="product_name"><i class="fa fa-box"></i> Product</label>
        <div class="input-field">
            <input type="text" name="product_name" id="product_name" placeholder="Product Name" required>
        </div>

        <label for="quantity"><i class="fa fa-sort-numeric-up"></i> Quantity</label>
        <div class="input-field">
            <input type="number" name="quantity" id="quantity" min="1" value="1" required>
        </div>

        <label for="order_status"><i class="fa fa-tasks"></i> Status</label>
        <div class="input-field">
            <select name="order_status" id="order_status">
                <option value="Pending">⏳ Pending</option>
                <option value="Order Confirmed">✅ Order Confirmed</option>
                <option value="Shipped">🚚 Shipped</option>
                <option value="Out for Delivery">📦 Out for Delivery</option>
                <option value="Delivered">✔️ Delivered</option>
            </select>
        </div>

        <input type="hidden" name="estimated_delivery" id="estimatedDelivery">

        <button type="submit">Create Order</button>
    </form>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>✅ Order Created Successfully!</h3>
        <button onclick="closeModal()">OK</button>
    </div>
</div>

<script>
const estimatedDeliveryInput = document.getElementById('estimatedDelivery');
function calculateEstimatedDelivery() {
    const qty = parseInt(document.getElementById('quantity').value);
    let days = 5 + Math.floor(qty / 10); // extra days for bulk orders
    const estimated = new Date();
    estimated.setDate(estimated.getDate() + days);
    estimatedDeliveryInput.value = estimated.toISOString().split('T')[0];
}
document.getElementById('quantity').addEventListener('input', calculateEstimatedDelivery);
calculateEstimatedDelivery();

// Confirmation before submission
function confirmSubmission() {
    return confirm("Are you sure you want to create this order?");
}

// Success modal display
<?php if($success): ?>
const modal = document.getElementById('successModal');
modal.style.display = 'flex';
function closeModal() {
    modal.style.display = 'none';
    window.location.href = 'create_customer.php';
}
window.onclick = function(event) {
    if(event.target == modal) closeModal();
}
<?php endif; ?>
</script>

</body>
</html>
