<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Tracking</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; padding:0; background:linear-gradient(135deg,#e0f7fa,#ffffff); display:flex; justify-content:center; align-items:center; min-height:100vh; }
        .tracking-container { background:#fff; padding:30px; border-radius:12px; width:100%; max-width:500px; box-shadow:0 8px 20px rgba(0,0,0,0.15);}
        h2 { text-align:center; color:#333; margin-bottom:20px; }
        input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:16px; margin-bottom:15px; box-sizing:border-box; }
        button { width:100%; padding:12px; background:#007bff; color:white; font-weight:bold; border:none; border-radius:6px; cursor:pointer;}
        button:hover { background:#0056b3; }
        .order-details { margin-top:20px; background:#f8f9fa; padding:15px; border-radius:8px; font-size:14px; }
        .progress-bar { display:flex; justify-content:space-between; margin:15px 0; }
        .progress-step { flex:1; text-align:center; position:relative; }
        .progress-step::before { content:''; position:absolute; top:15px; left:50%; width:100%; height:6px; background:#e9ecef; z-index:-1; }
        .progress-step:first-child::before { left:50%; width:50%; }
        .progress-step:last-child::before { width:50%; }
        .progress-icon { display:inline-block; background:#e9ecef; color:#fff; width:30px; height:30px; border-radius:50%; line-height:30px; margin-bottom:5px; }
        .progress-step.completed .progress-icon { background:#28a745; }
        .progress-step.canceled .progress-icon { background:#dc3545; }
        .actions { display:flex; justify-content:space-between; margin-top:15px; }
        .actions button { width:48%; padding:10px; font-size:15px; border-radius:6px; cursor:pointer; }
        .cancel { background:#dc3545; color:white; }
        .error { color:red; text-align:center; }
        @media (max-width:500px){ .progress-step::before{height:4px;} }
    </style>
</head>
<body>
<div class="tracking-container">
    <h2>Order Tracking</h2>
    <form id="trackForm">
        <input type="text" id="order_id" placeholder="Enter Order ID" required>
        <button type="submit">Track Order</button>
    </form>
    <div id="orderDetails"></div>
</div>

<script>
document.getElementById('trackForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const orderId = document.getElementById('order_id').value;

    try {
        const response = await fetch('fetch_order.php?order_id=' + orderId);
        const data = await response.json();

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

        document.getElementById('orderDetails').innerHTML = `
            <div class="order-details">
                <p><strong>Customer:</strong> ${order.client_name} (${order.client_email})</p>
                <p><strong>Product:</strong> ${order.product_name}</p>
                <p><strong>Order Date:</strong> ${order.order_date}</p>
                <p><strong>Estimated Delivery:</strong> ${order.estimated_delivery}</p>
                <p id="status"><strong>Status:</strong> ${order.order_status}</p>
                ${cancelReasonHtml}
                ${progressHtml}
                ${!['Delivered','Canceled'].includes(order.order_status) ? `<div class="actions">
                    <button class="cancel" onclick="cancelOrder(${order.id})">Cancel</button>
                </div>` : ''}
            </div>
        `;
    } catch(err){
        document.getElementById('orderDetails').innerHTML = '<p class="error">⚠️ Error fetching order details.</p>';
    }
});

async function cancelOrder(orderId){
    const reason = prompt("Please provide a reason for cancellation:");
    if(!reason) return;

    try {
        const res = await fetch('update_status.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`order_id=${orderId}&status=Canceled&reason=${encodeURIComponent(reason)}`
        });
        const data = await res.json();

        if(data.success){
            alert('Order canceled successfully! Reason: ' + reason);
            // Update status
            document.getElementById('status').innerHTML = '<strong>Status:</strong> Canceled';
            // Add cancel reason
            const orderDetails = document.querySelector('.order-details');
            const reasonPara = document.createElement('p');
            reasonPara.innerHTML = `<strong>Cancellation Reason:</strong> ${reason}`;
            orderDetails.appendChild(reasonPara);
            // Update progress bar
            document.querySelectorAll('.progress-step').forEach(step=>{
                step.classList.remove('completed');
                step.classList.add('canceled');
                step.querySelector('.progress-icon').textContent = '❌';
            });
            // Remove cancel button
            document.querySelector('.actions .cancel')?.remove();
        } else {
            alert('Error canceling order! ' + (data.error || ''));
        }
    } catch(err){
        alert('Error canceling order! ' + err);
    }
}
</script>
</body>
</html>
