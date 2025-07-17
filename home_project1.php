<?php
session_start(); 
include_once '../includes/Database.php';

$conn = Database::getConnection();

$cartCount = 0;

if (isset($_SESSION['id'])) {
    
    $cartCount = Database::getCartCount($_SESSION['id']);
 } 
//  else {
    
//     $cartCount = 0; 
//     header("Location: login_user.php"); 
//     // exit;
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Bookstore</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <!-- <a class="navbar-brand" href="#">Book Store</a> -->
        <div class="ml-auto">
            <a href="#" class="btn btn-outline-primary" id="cart-button">
                Cart <span class="badge badge-light"><?php echo htmlspecialchars($cartCount, ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        </div>
    </nav>
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Available Books</h2>

        <!-- <div class="mb-4">
            <input type="text" id="search-input" class="form-control" placeholder="Search for books...">
            <button id="search-button" class="btn btn-primary mt-2">Search</button>
        </div> -->

        <div class="row" id="books-container">
            
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var isLoggedIn = <?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>;
         const apiKey = 'AIzaSyD6xW5Vr0IALqsOE6iyJJmaEB6HyX4tYK8';
        $(document).ready(function() {

            loadBooks();
            
            $('#search-button').on('click', function() {
                var query = $('#search-input').val();
                if (query) {
                    searchBooks(query);
                } else {
                    alert('Please enter a search term.');
                }
            });

            
            function loadBooks() {
                $.ajax({
                    url: 'login_project1.php', 
                    type: 'POST',
                    data: { 
                        action: 'view_book' 
                    },
                    dataType: 'json',
                    success: function(books) {
                        $('#books-container').empty(); 
                        if (books.length > 0) {
                            books.forEach(function(book) {
                                $('#books-container').append(`
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">${book.title}</h5>
                                                <h6 class="card-subtitle mb-2 text-muted">${book.author}</h6>
                                                <p class="card-text text-success">${book.price.toFixed(2)}/-</p>
                                            </div>
                                            <button class="view_details" data-id="${book.id}">View details</button>
                                            
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            $('#books-container').html('<div class="col-12 text-center">No books available</div>');
                        }
                    },
                    error: function() {
                        $('#books-container').html('<div class="col-12 text-center text-danger">Error loading books</div>');
                    }
                });
            }
            
            loadBooks();
        });

        $(document).on('click', '#cart-button', function(e) {
            e.preventDefault(); 
            if (!isLoggedIn) {
                
                window.location.href = 'login_user.php';
            } else {
                
                window.location.href = 'cart.php'; 
            }
        });
        $(document).on('click', '.view_details', function() {
            var bookId = $(this).data('id');
            console.log("Book ID:", bookId);
            if (!isLoggedIn) {
                
                window.location.href = 'login_user.php';
            } else {
                
                window.location.href = 'book_details.php?id=' + bookId; 
            }
        });
    </script>
</body>
</html>
