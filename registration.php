<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bcrypt.js/5.0.1/bcrypt.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <form class="mx-auto" id="register_form" method="POST">
            <h4 class="text-center">Register</h4>
            <div class="mb-3 mt-5">
                <label for="username" class="form-label">Username:</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username">
                <span id="nameErr"></span>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address">
                <span id="emailErr"></span>
            </div>
            <div class="mb-3">
                <label for="password"class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                <span id="passwordErr"></span>
            </div>
            <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirm password:</label>
                <input type="password" class="form-control" id="cpass" name="cpass" placeholder="Enter your confirm password">
                <span id="cpassErr"></span>
            </div>
            <div class="mb-3">
                <label for="user_type">User type:</label>
                <select class="form-select" name="user_type" id="user_type">
                
                <option value="0">User </option>
                <option value="1">Admin</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" id="register" name="register">Submit</button>
            
        </form>
    </div>
    <div id="resultMessage" class="success-message"></div>
    <div id="resultMessage" class="error-message"></div>
    <script>
        jQuery (document).ready(function($){
            $('#register').on('click',function(e){
                e.preventDefault();
                alert("test");
                var id =$('#id').val();
                var username = $('#username').val();
                var email = $('#email').val();
                var password = $('#password').val();
                var cpass = $('#cpass').val();
                // var user_type = 0;
                var user_type = $('#user_type').val();
                isValid = true;

                var namePattern = /^[a-zA-Z\s-]+$/;
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                

                if(username === ''){
                    $('#nameErr').text("Name is required");
                     isValid = false;
                }
                else if(!namePattern.test(username)){
                    $('#nameErr').text("Only letters and spaces are allowed");
                    isValid = false;
                } 


                if(email === ''){
                    $('#emailErr').text("Email required");
                }
                else if(!emailPattern.test(email)){
                    $('#emailErr').text("Invalid email format");
                    isValid = false;
                }

                 if(password === ''){
                    $('#passwordErr').text("Password is required");
                    isValid = false;
                }
                if(cpass === ''){
                    $('#cpassErr').text("Confirm password is required");
                    isValid = false;
                    }
                if (password !== cpass) {
                    $('#cpassErr').text("password and confirm password must be same");
                    isValid = false;
                }

                if(isValid){
                    // var salt = bcrypt.genSaltSync(10);
                    // var hashedPassword = bcrypt.hashSync(password, salt);
                    
                        $.ajax({
                            
                            url: 'login_project1.php',
                            type: 'POST',
                            dataType: 'json', 
                            data: {
                                id: id,
                                username: username,
                                email: email,
                                password: password,
                                user_type: user_type,
                                action:'insert',
                                 },
                                 
                            success: function(response){
                                console.log("Login response:", response);
                                if(response.status === 'success'){
                                    alert("test");
                                    window.location.href = response.redirect; 
                                } 
                                else {
                                    $('#resultMessage').text(response.message);
                                }
                            },
                            error: function(){
                                
                                $('#resultMessage').text("There was an error processing your request.");
                    
                            }
                        });
                    }
            });
         });
    
    </script>



</body>
</html>