<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Tracking</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin:0; padding:0;
            background:linear-gradient(135deg,#e0f7fa,#ffffff);
            display:flex; justify-content:center; align-items:flex-start;
            min-height:100vh; padding-top:50px; transition: all 0.3s;
        }
        body.dark { background:#121212; color:#eee; }

        .tracking-container {
            position:relative;
            background:#fff; padding:30px; border-radius:12px;
            width:100%; max-width:500px;
            box-shadow:0 8px 20px rgba(0,0,0,0.15);
            transition: all 0.3s;
        }
        body.dark .tracking-container { background:#1e1e1e; }

        /* Dark mode toggle top-right */
        #darkModeToggle {
            position:absolute;
            top:15px;
            right:15px;
            font-weight:bold;
            display:flex;
            align-items:center;
            gap:5px;
            z-index:20; /* ensure above sticky header */
            white-space:nowrap; /* prevent label clipping */
        }
        #darkModeToggle input[type="checkbox"] { transform: scale(1.2); cursor:pointer; }

        h2 {
            text-align:center; color:#333; margin-bottom:20px;
            position:sticky; top:0; background:#fff; padding:10px 0; z-index:10;
        }
        body.dark h2 { background:#1e1e1e; color:#eee; }

        input, select {
            width:100%; padding:10px; border:1px solid #ccc;
            border-radius:6px; font-size:16px; margin-bottom:15px; box-sizing:border-box;
        }
        body.dark input, body.dark select { background:#2a2a2a; border:1px solid #555; color:#eee; }

        button {
            width:100%; padding:12px; font-weight:bold; border:none;
            border-radius:6px; cursor:pointer; font-size:16px;
        }
        button:hover { opacity:0.9; }

        .order-details {
            margin-top:20px; background:#f8f9fa; padding:15px; border-radius:8px;
            font-size:14px; transition: all 0.3s;
        }
        body.dark .order-details { background:#2a2a2a; }

        .progress-bar { display:flex; justify-content:space-between; margin:15px 0; }
        .progress-step { flex:1; text-align:center; position:relative; }
        .progress-step::before {
            content:''; position:absolute; top:15px; left:50%;
            width:100%; height:6px; background:#e9ecef; z-index:-1;
        }
        .progress-step:first-child::before { left:50%; width:50%; }
        .progress-step:last-child::before { width:50%; }
        .progress-icon {
            display:inline-block; background:#e9ecef; color:#fff;
            width:30px; height:30px; border-radius:50%; line-height:30px;
            margin-bottom:5px;
        }
        .progress-step.completed .progress-icon { background:#28a745; }
        .progress-step.canceled .progress-icon { background:#dc3545; }

        .actions { display:flex; justify-content:center; margin-top:15px; }
        .actions button.cancel {
            background:#dc3545; color:white; width:100%; padding:12px;
            font-size:16px; border-radius:6px; cursor:pointer;
        }

        .error { color:red; text-align:center; }

        .badge { padding:4px 8px; border-radius:4px; font-weight:bold; margin-left:5px; }
        .badge.pending { background:#ffc107; color:#000; }
        .badge.order\ confirmed { background:#17a2b8; color:#fff; }
        .badge.shipped { background:#007bff; color:#fff; }
        .badge.out\ for\ delivery { background:#fd7e14; color:#fff; }
        .badge.delivered { background:#28a745; color:#fff; }
        .badge.canceled { background:#dc3545; color:#fff; }

        #loading { text-align:center; display:none; font-size:20px; margin:10px 0; }

        @media (max-width:500px){ .progress-step::before{height:4px;} }
    </style>
</head>
<body>

<div class="tracking-container">
    <!-- Dark Mode toggle -->
    <div id="darkModeToggle">
        <input type="checkbox" id="darkSwitch" onchange="toggleDarkMode()">
        <label for="darkSwitch">🌙 Dark Mode</label>
    </div>

    <h2>Order Tracking</h2>

    <!-- Search history dropdown -->
    <select id="orderHistory" onchange="document.getElementById('order_id').value = this.value;">
        <option value="">Recent Orders</option>
    </select>

    <form id="trackForm">
        <input type="text" id="order_id" placeholder="Enter Order ID" required>
        <button type="submit">Track Order</button>
    </form>

    <div id="loading">🔄 Loading...</div>
    <div id="orderDetails"></div>
</div>

<script>
let orderHistory = [];

function toggleDarkMode(){
    document.body.classList.toggle('dark');
}

document.getElementById('trackForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const orderId = document.getElementById('order_id').value;

    if(!orderHistory.includes(orderId)){
        orderHistory.push(orderId);
        const option = document.createElement('option');
        option.value = orderId;
        option.text = orderId;
        document.getElementById('orderHistory').appendChild(option);
    }

    document.getElementById('loading').style.display = 'block';
    document.getElementById('orderDetails').innerHTML = '';

    try {
        const response = await fetch('fetch_order.php?order_id=' + orderId);
        const data = await response.json();
        document.getElementById('loading').style.display = 'none';

        if(!data.success){
            document.getElementById('orderDetails').innerHTML = '<p class="error">⚠️ Order not found.</p>';
            return;
        }

        const order = data.order;
        const statusStages = ["Pending","Order Confirmed","Shipped","Out for Delivery","Delivered"];
        const currentStage = statusStages.indexOf(order.order_status)+1;

        let progressHtml = '<div class="progress-bar">';
        statusStages.forEach((status,index)=>{
            let stepClass = (index<currentStage)?'completed':'';
            if(order.order_status==='Canceled') stepClass='canceled';
            let icon = status==='Pending'?'⏳':status==='Order Confirmed'?'✅':status==='Shipped'?'🚚':status==='Out for Delivery'?'📦':status==='Delivered'?'✔️':'❌';
            progressHtml += `<div class="progress-step ${stepClass}">
                                <div class="progress-icon">${icon}</div>
                                <div>${status}</div>
                             </div>`;
        });
        progressHtml += '</div>';

        const cancelReasonHtml = order.order_status==='Canceled' && order.cancel_reason ? `<p><strong>Cancellation Reason:</strong> ${order.cancel_reason}</p>` : '';

        let badgeClass = order.order_status.toLowerCase().replace(/ /g, '\\ ');

        document.getElementById('orderDetails').innerHTML = `
            <div class="order-details">
                <p><strong>Customer:</strong> ${order.client_name} (${order.client_email})</p>
                <p><strong>Product:</strong> ${order.product_name}</p>
                <p><strong>Order Date:</strong> ${order.order_date}</p>
                <p><strong>Estimated Delivery:</strong> ${order.estimated_delivery}</p>
                <p id="status"><strong>Status:</strong> <span class="badge ${badgeClass}">${order.order_status}</span></p>
                ${cancelReasonHtml}
                ${progressHtml}
                ${!['Delivered','Canceled'].includes(order.order_status) ? `<div class="actions">
                    <button class="cancel" onclick="cancelOrder(${order.id})">Cancel Order</button>
                </div>` : ''}
            </div>
        `;
    } catch(err){
        document.getElementById('loading').style.display = 'none';
        document.getElementById('orderDetails').innerHTML = '<p class="error">⚠️ Error fetching order details.</p>';
    }
});

async function cancelOrder(orderId){
    const reason = prompt("Please provide a reason for cancellation:");
    if(!reason) return;

    try {
        const res = await fetch('update_status.php',{
            method:'POST',
            headers:{
                'Content-Type':'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body:`order_id=${orderId}&status=Canceled&reason=${encodeURIComponent(reason)}`
        });
        const data = await res.json();

        if(data.success){
            alert('Order canceled successfully! Reason: ' + reason);
            document.getElementById('status').innerHTML = '<strong>Status:</strong> <span class="badge canceled">Canceled</span>';
            const orderDetails = document.querySelector('.order-details');
            const reasonPara = document.createElement('p');
            reasonPara.innerHTML = `<strong>Cancellation Reason:</strong> ${reason}`;
            orderDetails.appendChild(reasonPara);
            document.querySelectorAll('.progress-step').forEach(step=>{
                step.classList.remove('completed');
                step.classList.add('canceled');
                step.querySelector('.progress-icon').textContent = '❌';
            });
            document.querySelector('.actions .cancel')?.remove();
        } else {
            alert('Error canceling order: ' + (data.message || 'Unknown error'));
        }
    } catch(err){
        alert('Error canceling order: ' + err.message);
    }
}
</script>
</body>
</html>
