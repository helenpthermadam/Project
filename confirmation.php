<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Order Confirmation</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Order Confirmation</h1>
        <p>Thank you for your order!</p>
        <p>Your order ID is: <strong id="order-id"></strong></p>
        <p>Total Amount: <strong id="order-total"></strong></p>
        <p>Order Date: <strong id="order-date"></strong></p>
        <h3>Order Details</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody id="order-items"></tbody>
        </table>
        <p>If you have any questions about your order, please contact our support team.</p>
    </div>
    <script>
    $(document).ready(function() {
        function fetchOrderDetails(orderId) {
            $.ajax({
                url: 'login_project1.php',
                type: 'POST',
                data: {
                    action: 'fetch_order_details',
                    order_id: orderId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                    
                        displayOrderDetails(response.order, response.order_details);
                    } else {
                        alert('Error fetching order details: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error fetching order details. Please try again.');
                }
            });
        }

       
        function displayOrderDetails(order, orderDetails) {
            
            $('#order-id').text(order.id);
            $('#order-total').text(order.total + '/-');
            $('#order-date').text(order.created_at);
            $('#order-items').empty();
           
            orderDetails.forEach(function(detail) {
                $('#order-items').append(`
                    <tr>
                        <td>${detail.title}</td>
                        <td>${detail.quantity}</td>
                        <td>${detail.price}/-</td>
                    </tr>
                `);
            });
        }

        
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('order_id');

        if (orderId) {
            fetchOrderDetails(orderId); 
        } else {
            alert('No order ID provided in the URL.');
        }
    });
    </script>
</body>
</html>
