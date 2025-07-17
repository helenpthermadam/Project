<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <form class="mx-auto" id="login_form" method="POST">
            <h4 class="text-center">Login</h4>
            <div class="mb-3 mt-5">
                <label for="email" class="form-label">Email: </label>
                <input type="email" class="form-control" id="email" name="email">
                <span id="emailErr"></span>

            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password: </label>
                <input type="password" class="form-control" id="password" name="password">
                <span id="passwordErr"></span>
            </div>
    
            <button type="button" class="btn btn-primary" id="login" name="login">Login</button>
            <p>Dont have an account?<a href="registration.php">Register now</a></p>
        
        </form>
    </div>

    <div id="resultMessage" class="success-message"></div>
    <div id="resultMessage" class="error-message"></div>
    <script>
        jQuery(document).ready(function($){
            $('#login').on('click',function(e){
                var id =$('#id').val();
                var email=$('#email').val();
                var password=$('#password').val();

                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                isValid = true;
                if(email ===''){
                    $(emailErr).text("Email is required");
                    isValid = false;
                }
                else if(!emailPattern .test(email)){
                    $(emailErr).text("Invalid email format");
                    isValid = false;
                }
                 if(password ===''){
                    $(passwordErr).text("Password is required");
                    isValid = false;
                }

                if(isValid){
                    $.ajax({
                        url: 'login_project1.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            email: email,
                            password: password,
                            action:'login_process',
                        },

                        success:function(response){
                            if(response.status == 'success'){
                               
                                sessionStorage.setItem('id', response.id);
                                sessionStorage.setItem('username', response.username);

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