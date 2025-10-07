document.getElementById('trackForm').addEventListener('submit', function(e){
    e.preventDefault();
    const orderId = document.getElementById('order_id').value;
    fetch('fetch_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_id=' + orderId
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) {
            document.getElementById('orderDetails').innerHTML = "<p>" + data.error + "</p>";
        } else {
            document.getElementById('orderDetails').innerHTML = `
                <h3>Order #${data.id}</h3>
                <p><strong>Product:</strong> ${data.product_name}</p>
                <p><strong>Status:</strong> ${data.order_status}</p>
            `;
        }
    });
});
