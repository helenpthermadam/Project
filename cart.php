<?php
session_start();
include_once '../includes/Database.php';

if (!isset($_SESSION['id'])) {
    header("Location: login_user.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$conn = Database::getConnection();
$userId = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Your Cart</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="home.php">Book Store</a>
        <div class="ml-auto">
            <a href="cart.php" class="btn btn-outline-primary">
                Cart <span class="badge badge-light" id="cart-count">0</span>
            </a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Your Shopping Cart</h2>
        
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cart-items">
        
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-5">
                <a href="home_project1.php" class="btn btn-outline-secondary">Continue Shopping</a>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <dl class="row mb-0">
                            <dt class="col-6">Subtotal:</dt>
                            <dd class="col-6 text-right" id="subtotal">0/-</dd>
                            
                            <dt class="col-6">Shipping:</dt>
                            <dd class="col-6 text-right">Free</dd>
                            
                            <dt class="col-6">Tax:</dt>
                            <dd class="col-6 text-right">0/-</dd>
                            
                            <dt class="col-6 border-top mt-2 pt-2 font-weight-bold">Total:</dt>
                            <dd class="col-6 border-top mt-2 pt-2 text-right font-weight-bold" id="total">0.00</dd>
                        </dl>
                        <a href="checkout.php" class="btn btn-primary btn-block mt-3">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        
        function loadCartItems() {
            $.ajax({
                url: 'login_project1.php',
                type: 'POST',
                data: {
                    action: 'get_cart_items',
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#cart-items').empty();
                        let total = 0; 
                        let subtotal = 0;

                        response.data.forEach(function(item) {
                            const price = parseFloat(item.price);
                            const quantity = parseInt(item.quantity); 
                            const itemSubtotal = price * quantity; 
                            total += itemSubtotal;
                            subtotal += itemSubtotal;
                            $('#cart-items').append(`
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h5 class="mb-1">${item.title}</h5>
                                            <small class="text-muted">by ${item.author}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${price.toFixed(2)}</td> 
                                <td>
                                    <input type="number" class="form-control quantity-input" data-cart-id="${item.cart_id}" value="${item.quantity}" min="1">
                                </td>
                                <td>${subtotal.toFixed(2)}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary update-quantity" data-cart-id="${item.cart_id}">Update</button>
                                    <button class="btn btn-sm btn-outline-danger remove-item" data-cart-id="${item.cart_id}">Remove</button>
                                </td>
                            </tr>
                            `);
                        });

                        $('#total').text(`${total.toFixed(2)}`); 
                        $('#subtotal').text(`${subtotal.toFixed(2)}`); 
                        $('#cart-count').text(response.data.length);  
                    } else {
                        alert('Error fetching cart items: ' + response.message);
                        }
                },
                error: function() {
                    alert('Error loading cart items. Please try again.');
                }
            });
        }

        
        loadCartItems();

       $(document).on('click', '.update-quantity', function() {
            const cartId = $(this).data('cart-id');
            const quantity = $(this).closest('tr').find('.quantity-input').val();

            console.log('Updating cart ID:', cartId, 'to quantity:', quantity); 

            $.ajax({
                url: 'login_project1.php',
                type: 'POST',
                data: {
                    action: 'update_cart_quantity',
                    cart_id: cartId,
                    quantity: quantity,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response); 
                    if (response.status === 'success') {
                        loadCartItems(); 
                    } else {
                        alert('Error updating quantity: ' + response.message);
                    }
                },
                error: function() {
                    // console.error('AJAX Error:', textStatus, errorThrown); 
                    alert('Error updating quantity. Please try again.');
                }
            });
        });


       
        $(document).on('click', '.remove-item', function() {
            const cartId = $(this).data('cart-id');

            if (confirm('Are you sure you want to remove this item from your cart?')) {
                $.ajax({
                    url: 'login_project1.php',
                    type: 'POST',
                    data: {
                        action: 'remove_from_cart',
                        cart_id: cartId,
                        csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            loadCartItems();
                        } else {
                            alert('Error removing item: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error removing item. Please try again.');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
