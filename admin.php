<?php
include 'db_connect.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Console</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* --- your existing CSS unchanged --- */
    :root {
        --primary: #4CAF50;
        --accent: #FF9800;
        --info: #2196F3;
        --danger: #e74c3c;
        --light-bg: #f4f6f8;
        --card-bg: #fff;
        --text-dark: #333;
        --dark-bg: #121212;
        --dark-card: #1e1e1e;
        --dark-text: #f0f0f0;
        --shadow: rgba(0,0,0,0.15);
        --transition: all 0.3s ease;
    }
    * { transition: var(--transition); }
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 0;
        padding: 40px;
        background: linear-gradient(135deg, #e0f7fa, #f4f6f8);
        color: var(--text-dark);
    }
    body.dark-mode { background: linear-gradient(135deg, #121212, #1e1e1e); color: var(--dark-text); }
    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 20px 20px;
        z-index: -1;
    }
    .admin-title { font-size: 2.2rem; font-weight: 700; color: var(--primary); margin-bottom: 25px; text-align:center; letter-spacing:1px; text-shadow:1px 1px 3px rgba(0,0,0,0.7);}
    body.dark-mode .admin-title { color: #66bb6a; }
    /* Stats Boxes */
    .stats-box { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius:12px; font-weight:600; box-shadow:0 4px 10px var(--shadow); cursor:default; color:#fff; margin-right:15px;}
    .stats-box:hover { transform: translateY(-2px); box-shadow: 0 6px 15px var(--shadow);}
    .stats-box.total { background: linear-gradient(135deg, #4caf50, #81c784);}
    .stats-box.pending { background: linear-gradient(135deg, #f39c12, #f1c40f);}
    .stats-box.delivered { background: linear-gradient(135deg, #27ae60, #2ecc71);}
    .stats-box.canceled { background: linear-gradient(135deg, #e74c3c, #ff6b6b);}
    /* Buttons */
    button, .create-btn { padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; font-weight:600; box-shadow:0 2px 6px var(--shadow); transition: var(--transition);}
    .create-btn { text-decoration:none; color:#fff; margin-right:10px; background: linear-gradient(135deg, var(--primary), #66bb6a);}
    .create-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px var(--shadow);}
    .create-btn.info { background: linear-gradient(135deg, var(--info), #64b5f6);}
    .create-btn.danger { background: linear-gradient(135deg, var(--danger), #e74c3c);}
    .create-btn.accent { background: linear-gradient(135deg, var(--accent), #ffb74d);}
    /* Filter Bar */
    .filter-bar { background: var(--card-bg); border-radius:10px; box-shadow:0 2px 8px var(--shadow); padding:15px 20px; margin:25px 0; display:flex; flex-wrap:wrap; gap:10px; align-items:center;}
    body.dark-mode .filter-bar { background: var(--dark-card); }
    .filter-bar input, .filter-bar select { padding:8px 10px; border-radius:6px; border:1px solid #ccc; min-width:180px;}
    .filter-bar button { background: var(--info); color:#fff; padding:8px 14px; border:none; border-radius:6px; cursor:pointer;}
    .filter-bar button:hover { background:#1976D2;}
    .filter-bar .reset-btn { background: var(--danger); color:#fff; text-decoration:none; padding:8px 14px; border-radius:6px;}
    .filter-bar .reset-btn:hover { background:#c0392b;}
    /* Table */
    table { border-collapse: collapse; width:100%; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px var(--shadow);}
    th, td { border:1px solid #ddd; padding:12px; text-align:center;}
    th { background-color: var(--primary); color:white; font-weight:600; position:sticky; top:0;}
    tr:nth-child(even){ background-color:#f9f9f9;}
    tr:hover { background-color:#e0f7fa; transform: scale(1.02);}
    body.dark-mode tr:nth-child(even){ background-color:#2a2a2a;}
    body.dark-mode tr:hover{ background-color:#333;}
    .status-badge { padding:6px 12px; border-radius:12px; font-size:0.9em; color:#fff; font-weight:500;}
    .status-Pending { background:#f39c12; }
    .status-OrderConfirmed { background:#3498db; }
    .status-Shipped { background:#9b59b6; }
    .status-OutforDelivery { background:#e67e22; }
    .status-Delivered { background:#27ae60; }
    .status-Canceled { background:#e74c3c; }
    table button.update { background-color:#2196F3; color:#fff; padding:6px 12px; border-radius:6px; border:none; margin-bottom:5px;}
    table button.update:hover { background-color:#1976D2; }
    table button.cancel { background-color:#e74c3c; color:#fff; padding:6px 12px; border-radius:6px; border:none; margin-bottom:5px;}
    table button.cancel:hover { background-color:#c0392b; }
    /* Modal */
    #statusModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1000;}
    #statusModal .modal-content { background: var(--card-bg); padding:25px 20px; border-radius:12px; width:320px; margin:100px auto; text-align:center; box-shadow:0 6px 15px var(--shadow);}
    #darkToggle { appearance:none; width:40px; height:20px; background:#ccc; border-radius:20px; position:relative; cursor:pointer; outline:none;}
    #darkToggle:checked { background: var(--primary); }
    #darkToggle::before { content:''; position:absolute; top:2px; left:2px; width:16px; height:16px; background:white; border-radius:50%; transition:0.3s;}
    #darkToggle:checked::before { transform: translateX(20px); }
    @media (max-width:768px){ table, th, td{ font-size:13px;} body{ margin:20px;} .filter-bar{ flex-direction:column; align-items:stretch; }}
    .chart-container { display:flex; justify-content:center; align-items:center; margin:20px 0; }
    #orderChart { width:300px; height:150px; }
    </style>
</head>
<body>
<label style="float:right;">
    <input type="checkbox" id="darkToggle"> 🌙 Dark Mode
</label>

<h1 class="admin-title">ORDER MANAGEMENT</h1>

<?php
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$delivered = $conn->query("SELECT COUNT(*) as total FROM orders WHERE order_status='Delivered'")->fetch_assoc()['total'];
$canceled = $conn->query("SELECT COUNT(*) as total FROM orders WHERE order_status='Canceled'")->fetch_assoc()['total'];

// Pending = all orders except delivered & canceled
$pending = $total_orders - $delivered - $canceled;
?>

<div style="margin-bottom:20px;">
    <div class="stats-box total">📦 <strong>Total Orders:</strong> <?php echo $total_orders; ?></div>
    <div class="stats-box pending">⏳ <strong>Pending Orders:</strong> <?php echo $pending; ?></div>
    <div class="stats-box delivered">✔️ <strong>Delivered Orders:</strong> <?php echo $delivered; ?></div>
    <div class="stats-box canceled">❌ <strong>Canceled Orders:</strong> <?php echo $canceled; ?></div>
</div>

<div class="chart-container"><canvas id="orderChart"></canvas></div>

<div style="margin-bottom:20px;">
    <a href="create_customer.php" class="create-btn">+ Create New Customer</a>
    <a href="delivered_products.php" class="create-btn info">Delivered Products</a>
    <a href="canceled_products.php" class="create-btn danger">Canceled Products</a>
    <a href="export_pdf.php" class="create-btn accent">📄 Export PDF</a>
</div>


<div class="filter-bar">
    <form method="GET" class="filter-form" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Search customer or product" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <input type="date" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
        <select name="status">
            <option value="">-- All Status --</option>
            <option value="Pending">Pending</option>
            <option value="Order Confirmed">Order Confirmed</option>
            <option value="Shipped">Shipped</option>
            <option value="Out for Delivery">Out for Delivery</option>
            <option value="Delivered">Delivered</option>
            <option value="Canceled">Canceled</option>
        </select>
        <button type="submit">Filter</button>
        <a href="admin.php" class="reset-btn">Reset</a>
    </form>
</div>

<?php
$where = "WHERE order_status!='Delivered' AND order_status!='Canceled'";
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where .= " AND (client_name LIKE '%$search%' OR product_name LIKE '%$search%')";
}
if (!empty($_GET['date'])) {
    $date = $conn->real_escape_string($_GET['date']);
    $where .= " AND DATE(order_date) = '$date'";
}
if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where .= " AND order_status='$status'";
}

$sql = "SELECT *, DATE_ADD(order_date, INTERVAL 5 DAY) AS estimated_delivery FROM orders $where ORDER BY order_date DESC";
$result = $conn->query($sql);

$status_icons = [
    'Pending' => '⏳',
    'Order Confirmed' => '✅',
    'Shipped' => '🚚',
    'Out for Delivery' => '📦',
    'Delivered' => '✔️',
    'Canceled' => '❌'
];

if ($result->num_rows > 0) {
    echo "<div style='overflow-x:auto;'><table>
    <tr>
        <th>Order ID</th>
        <th>Client Name</th>
        <th>Client Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Product</th>
        <th>Status</th>
        <th>Order Date</th>
        <th>Estimated Delivery</th>
        <th>Actions</th>
    </tr>";

    while ($row = $result->fetch_assoc()) {
        $icon = $status_icons[$row['order_status']];
        $status_class = str_replace(' ','',$row['order_status']);
        $order_date = date('Y-m-d H:i:s', strtotime($row['order_date']));
        $delivery_date = isset($row['estimated_delivery']) && !empty($row['estimated_delivery']) ? date('Y-m-d', strtotime($row['estimated_delivery'])) : '—';

        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['client_name']}</td>
            <td>{$row['client_email']}</td>
            <td>{$row['phone_number']}</td>
            <td>{$row['address']}</td>
            <td>{$row['product_name']}</td>
            <td><span class='status-badge status-{$status_class}'>{$icon} {$row['order_status']}</span></td>
            <td>{$order_date}</td>
            <td>{$delivery_date}</td>
            <td>";
        if($row['order_status'] != 'Delivered' && $row['order_status'] != 'Canceled'){
            echo "<button class='update' onclick=\"openModal({$row['id']}, '{$row['order_status']}')\">🛠️ Update</button>
                  <button class='cancel' onclick=\"cancelOrder({$row['id']})\">❌ Cancel</button>";
        } else {
            echo "-";
        }
        echo "</td></tr>";
    }

    echo "</table></div>";
} else {
    echo "<p>No orders found.</p>";
}
?>

<div id="statusModal">
    <div class="modal-content">
        <h3>Update Order Status</h3>
        <form method="POST" action="update_status.php">
            <input type="hidden" name="order_id" id="modalOrderId">
            <select name="status" id="modalStatus">
                <option value="Pending">⏳ Pending</option>
                <option value="Order Confirmed">✅ Order Confirmed</option>
                <option value="Shipped">🚚 Shipped</option>
                <option value="Out for Delivery">📦 Out for Delivery</option>
                <option value="Delivered">✔️ Delivered</option>
            </select>
            <br><br>
            <button type="submit">✅ Update</button>
            <button type="button" onclick="closeModal()">❌ Cancel</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const darkToggle = document.getElementById('darkToggle');
darkToggle.addEventListener('change', () => { document.body.classList.toggle('dark-mode', darkToggle.checked); });

function openModal(orderId, currentStatus) {
    document.getElementById('modalOrderId').value = orderId;
    document.getElementById('modalStatus').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}
function closeModal() { document.getElementById('statusModal').style.display = 'none'; }
window.onclick = function(event) { if(event.target == document.getElementById('statusModal')) closeModal(); }

async function cancelOrder(orderId){
    const reason = prompt("Please provide a reason for cancellation:");
    if(!reason) return;
    try{
        const res = await fetch('update_status.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`order_id=${orderId}&status=Canceled&reason=${encodeURIComponent(reason)}`
        });
        const data = await res.json();
        if(data.success){
            alert("Order canceled successfully! Reason: " + reason);
            location.reload();
        } else {
            alert("Error canceling order!");
        }
    } catch(err){
        alert("Error canceling order!");
    }
}

// Chart
const ctx = document.getElementById('orderChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Delivered', 'Pending', 'Canceled'],
        datasets: [{
            data: [<?php echo $delivered; ?>, <?php echo $pending; ?>, <?php echo $canceled; ?>],
            backgroundColor: ['#4CAF50', '#FF9800', '#e74c3c'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
</body>
</html>
