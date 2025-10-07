<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivered Products - Admin</title>
    <style>
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

    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; padding: 0; min-height: 100vh; background: linear-gradient(135deg, #e0f7fa, #f4f6f8); color: var(--text-dark); transition: background-color 0.4s ease, color 0.4s ease; }
    body.dark-mode { background: linear-gradient(135deg, #121212, #1e1e1e); color: var(--dark-text); }

    .admin-title { font-size: 2.2rem; font-weight: 700; color: var(--primary); margin-bottom: 25px; text-align: center; letter-spacing: 1px; text-shadow: 1px 1px 3px rgba(0,0,0,0.7); }

    /* Buttons */
    a.back-btn, .export-btn { display: inline-block; padding: 10px 18px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 4px 10px var(--shadow); margin-right:10px; }
    a.back-btn { background: linear-gradient(135deg, #4caf50,#81c784); color: #fff; }
    a.back-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px var(--shadow); }
    .export-btn { background: linear-gradient(135deg, #ff9800,#ffc107); color:#fff; }
    .export-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px var(--shadow); }

    /* Filter Bar */
    .filter-bar { background: var(--card-bg); border-radius: 10px; box-shadow: 0 4px 10px var(--shadow); padding: 15px 20px; margin: 20px 0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: flex-start; }
    .filter-bar input, .filter-bar select { padding:8px 10px; border-radius:6px; border:1px solid #ccc; min-width:180px; }
    .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color: var(--primary); box-shadow:0 0 5px var(--shadow);}
    .filter-bar button { background: var(--info); color:#fff; padding:8px 14px; border:none; border-radius:6px; cursor:pointer; }
    .filter-bar button:hover { background:#1976D2; }
    .filter-bar .reset-btn { background: var(--danger); color:#fff; text-decoration:none; padding:8px 14px; border-radius:6px; }
    .filter-bar .reset-btn:hover { background:#c0392b; }

    /* Table */
    table { border-collapse: collapse; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px var(--shadow); }
    th, td { padding:12px; text-align:center; border:1px solid #ddd; }
    th { background: var(--info); color:#fff; position: sticky; top:0; font-weight:600; }
    tr:nth-child(even) { background:#f9f9f9; }
    tr:hover { background:#e0f7fa; transform:scale(1.01); }
    .status-badge { padding:5px 12px; border-radius:12px; font-size:0.9em; font-weight:500; color:#fff; }
    .status-Pending { background:#f39c12; }
    .status-OrderConfirmed { background:#3498db; }
    .status-Shipped { background:#9b59b6; }
    .status-OutforDelivery { background:#e67e22; }
    .status-Delivered { background:#27ae60; }

    /* Update button */
    button.update-btn { background: #2196F3; color: #fff; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
    button.update-btn:hover { background: #1976D2; }

    /* Modal */
    #statusModal { display:none; position: fixed; top:50%; left:50%; transform: translate(-50%, -50%); background:#fff; padding:25px 20px; border-radius:12px; width:350px; box-shadow:0 10px 25px rgba(0,0,0,0.3); z-index:1000; }
    #modalOverlay { display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; }

    @media(max-width:768px){ table, th, td{ font-size:13px; } .filter-bar{ flex-direction:column; align-items:stretch; } }
    </style>
</head>
<body>

<label style="float:right;">
    <input type="checkbox" id="darkToggle"> 🌙 Dark Mode
</label>

<h1 class="admin-title">Delivered Products</h1>

<div style="margin-bottom:15px;">
    <a href="admin.php" class="back-btn">← Back to Orders</a>
    <a href="export_pdf.php?status=Delivered" class="export-btn">📄 Export PDF</a>
</div>

<div class="filter-bar">
    <form method="GET" class="filter-form" style="display:flex; flex-wrap:wrap; gap:10px;">
        <input type="text" name="search" placeholder="🔍 Search customer or product" value="<?php echo isset($_GET['search'])?htmlspecialchars($_GET['search']):''; ?>">
        <input type="date" name="date" value="<?php echo isset($_GET['date'])?htmlspecialchars($_GET['date']):''; ?>">
        <select name="status">
            <option value="">-- All Status --</option>
            <option value="Delivered" selected>✔️ Delivered</option>
        </select>
        <button type="submit">Filter</button>
        <a href="delivered_products.php" class="reset-btn">Reset</a>
    </form>
</div>

<?php
$where = "WHERE order_status='Delivered'";
if(!empty($_GET['search'])){ $search=$conn->real_escape_string($_GET['search']); $where.=" AND (client_name LIKE '%$search%' OR product_name LIKE '%$search%')"; }
if(!empty($_GET['date'])){ $date=$conn->real_escape_string($_GET['date']); $where.=" AND DATE(order_date)='$date'"; }

$sql="SELECT * FROM orders $where ORDER BY delivered_date DESC";
$result=$conn->query($sql);

$status_icons=['Pending'=>'⏳','Order Confirmed'=>'✅','Shipped'=>'🚚','Out for Delivery'=>'📦','Delivered'=>'✔️'];

if($result->num_rows>0){
    echo "<div style='overflow-x:auto;'>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Client Name</th>
                    <th>Client Email</th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Delivered Date</th>
                    <th>Update</th>
                </tr>";
    while($row=$result->fetch_assoc()){
        $icon=$status_icons[$row['order_status']];
        $status_class=str_replace(' ','',$row['order_status']);
        $delivered_date = $row['delivered_date'] ? date('Y-m-d', strtotime($row['delivered_date'])) : '-';
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['client_name']}</td>
                <td>{$row['client_email']}</td>
                <td>{$row['product_name']}</td>
                <td><span class='status-badge status-{$status_class}'>{$icon} {$row['order_status']}</span></td>
                <td>{$row['order_date']}</td>
                <td>{$delivered_date}</td>
                <td><button class='update-btn' onclick=\"openModal({$row['id']},'{$row['order_status']}')\">🛠️ Update</button></td>
              </tr>";
    }
    echo "</table></div>";
} else { echo "<p>No delivered products found.</p>"; }
?>

<!-- Modal & Overlay -->
<div id="modalOverlay"></div>
<div id="statusModal">
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
        <button type="submit" class="update-btn">✅ Update</button>
        <button type="button" onclick="closeModal()" class="update-btn" style="background:#e74c3c;">❌ Cancel</button>
    </form>
</div>

<script>
const darkToggle=document.getElementById('darkToggle');
darkToggle.addEventListener('change',()=>{document.body.classList.toggle('dark-mode',darkToggle.checked);});

const modal=document.getElementById('statusModal');
const overlay=document.getElementById('modalOverlay');

function openModal(orderId,currentStatus){
    document.getElementById('modalOrderId').value=orderId;
    document.getElementById('modalStatus').value=currentStatus;
    modal.style.display='block';
    overlay.style.display='block';
}
function closeModal(){
    modal.style.display='none';
    overlay.style.display='none';
}

overlay.addEventListener('click', closeModal);
</script>
</body>
</html>
