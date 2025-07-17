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
    <title>Checkout</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Checkout</h2>
        <form id="checkout-form" method="POST" action="login_project1.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" class="form-control" id="fullName" name="fullName" required>
                <span id="nameErr"></span>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
                <span id="addressErr"></span>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" class="form-control" id="city" name="city" required>
                <span id="cityErr"></span>
            </div>
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" class="form-control" id="state" name="state" required>
                <span id="stateErr"></span>
            </div>
            <div class="form-group">
                <label for="zip">Zip Code</label>
                <input type="text" class="form-control" id="zip" name="zip" required>
                <span id="zipErr"></span>
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" class="form-control" id="country" name="country" required>
                <span id="countryErr"></span>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
                <span id="phoneErr"></span>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <span id="emailErr"></span>
            </div>
            <button type="submit" id="order_submit" class="btn btn-primary">Submit Order</button>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#checkout-form').on('submit', function(e) {
            e.preventDefault(); 
            $('#nameErr, #addressErr, #cityErr, #stateErr, #zipErr, #countryErr, #phoneErr, #emailErr').text('');

            var fullName = $('#fullName').val();
            var address = $('#address').val();
            var city = $('#city').val();
            var state = $('#state').val();
            var zip = $('#zip').val();
            var country = $('#country').val();
            var phone = $('#phone').val();
            var email = $('#email').val();

            var isValid = true;

            var namePattern = /^[a-zA-Z\s-]+$/;
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var zipPattern = /^[1-9][0-9]{5}$/;
            var phonePattern = /^(?:\+91|91)?[789]\d{9}$/;

            if (fullName === '') {
                $('#nameErr').text("Full name is required");
                isValid = false;
            } else if (!namePattern.test(fullName)) {
                $('#nameErr').text("Only letters and spaces are allowed");
                isValid = false;
            }
            if (address === '') {
                $('#addressErr').text("Address is required");
                isValid = false;
            }
            if (city === '') {
                $('#cityErr').text("City is required");
                isValid = false;
            } else if (!namePattern.test(city)) {
                $('#cityErr').text("Only letters and spaces are allowed");
                isValid = false;
            }
            if (state === '') {
                $('#stateErr').text("State is required");
                isValid = false;
            } else if (!namePattern.test(state)) {
                $('#stateErr').text("Only letters and spaces are allowed");
                isValid = false;
            }
            if (zip === '') {
                $('#zipErr').text("Zip code is required");
                isValid = false;
            } else if (!zipPattern.test(zip)) {
                $('#zipErr').text("Invalid Zip Code format");
                isValid = false;
            }
            if (country === '') {
                $('#countryErr').text("Country is required");
                isValid = false;
            } else if (!namePattern.test(country)) {
                $('#countryErr').text("Only letters and spaces are allowed");
                isValid = false;
            }
            if (phone === '') {
                $('#phoneErr').text("Phone number is required");
                isValid = false;
            } else if (!phonePattern.test(phone)) {
                $('#phoneErr').text("Enter a valid Indian phone number.");
                isValid = false;
            }
            if (email === '') {
                $('#emailErr').text("Email is required");
                isValid = false;
            } else if (!emailPattern.test(email)) {
                $('#emailErr').text("Invalid email format");
                isValid = false;
            }

            if (isValid) {
                const formData = $('#checkout-form').serialize() + '&action=process_checkout'; // Append action to form data

                $.ajax({
                    url: 'login_project1.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Order submitted successfully! Order ID: ' + response.order_id);
                            window.location.href = 'confirmation.php?order_id=' + response.order_id;
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error submitting order. Please try again.');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
