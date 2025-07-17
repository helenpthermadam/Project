<?php
session_start(); 

if (!isset($_SESSION['email'])) {
    header("Location: login_user.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="style3.css"> -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="sidebar close">
    <div class="sidebar-header">
        <!-- <div class="image-txt">
            <div class="image">
                <img src="logo1.png" alt="logo">
            </div>
            <div class="header-txt">Sample Website</div>
        </div> -->
        <!-- <div class="sidebar-toggle" id="toggleSidebar">
            <i class="fas fa-chevron-left"></i>
        </div>
    </div> -->
    <!-- <div class="menu-bar">
        <div class="menu">
            <ul class="menu-links">
                <li class="nav-link">
                    <a href="admin_dashboard.php">
                        <span class="text nav-text">Add book</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="edit_project1.php">
                        <span class="text nav-text">Edit user</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="logout_project1.php">
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div> -->
</nav>
     <div class="container mt-5" id="add-book-form">
        <h4 class="text-center mb-4">Add New Book</h4>
        <form id="bookForm" class="mx-auto">
            <div class="mb-3">
                <label for="title" class="form-label">Book Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
                <span id="titleErr"></span>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" class="form-control" id="author" name="author" required>
                <span id="authorErr"></span>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                <span id="priceErr"></span>
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" required>
                <span id="stockErr"></span>
            </div>
            <button type="button" class="btn btn-primary" id="add" name="add">Add Book</button>
        </form>
    </div>
<script>
    jQuery (document).ready(function($){
            $('#add').on('click',function(e){
                e.preventDefault();
                
                var id =$('#id').val();
                var title = $('#title').val();
                var author = $('#author').val();
                var price = $('#price').val();
                var stock = $('#stock').val();
                isValid = true;

                if (title === '') {
                    $('#titleErr').text("Title is required");
                    isValid = false;
                }

                if (author === '') {
                    $('#authorErr').text("Author is required");
                    isValid = false;
                }

                if (price === '') {
                    $('#priceErr').text("Price is required");
                    isValid = false;
                } 
                else if (isNaN(price) || price <= 0) {
                    $('#priceErr').text("Price must be a positive number");
                    isValid = false;
                }

                if (stock === '') {
                    $('#stockErr').text("Stock is required");
                    isValid = false;
                } else if (isNaN(stock) || stock < 0) {
                    $('#stockErr').text("Stock must be a non-negative number");
                    isValid = false;
                }

                if (isValid) {

                    $.ajax({
                        url: 'login_project1.php',
                        type: 'POST',
                        dataType: 'json', 
                        data: {
                            id:id,
                            title: title,
                            author: author,
                            price: price,
                            stock: stock,
                            action:'add_book',
                            },
                        success: function(response){
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
