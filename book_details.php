<?php
session_start(); 
include_once '../includes/Database.php';
$conn = Database::getConnection();

$cartCount = 0;

if (isset($_SESSION['id'])) {
    $cartCount = Database::getCartCount($_SESSION['id']);
}

if (isset($_GET['id'])) {
    $bookId = intval($_GET['id']); 
} else {
   echo "Invalid book ID.";
   exit;
}


$stmt = $conn->prepare("SELECT id, title, author, price FROM books WHERE id = :id");
$stmt->bindParam(':id', $bookId, PDO::PARAM_INT);
$stmt->execute();
$localBook = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$localBook) {
    echo "Book not found.";
    exit;
}


$apiKey = 'AIzaSyD6xW5Vr0IALqsOE6iyJJmaEB6HyX4tYK8'; 
$googleApiUrl = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($localBook['title']) . "&key=" . $apiKey;

$googleBookData = file_get_contents($googleApiUrl);
$googleBookData = json_decode($googleBookData, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Details</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="cart.php">Book Store</a>
            <div class="ml-auto">
                <a href="cart.php" class="cart-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div id="bookDetails" class="card">
            <div class="card-body">
                <h2 class="card-title" id="bookTitle"><?= htmlspecialchars($localBook['title']) ?></h2>
                <h5 class="card-subtitle mb-3" id="bookAuthor">By <?= htmlspecialchars($localBook['author']) ?></h5>
                <p class="card-text text-success h4" id="bookPrice"><?= number_format($localBook['price'], 2) ?>/-</p>
                <button id="addToCartBtn" class="btn btn-primary" data-book-id="<?= $bookId ?>">
                    Add to Cart
                </button>
            </div>
        </div>

        <div id="additionalDetails" class="mt-4">
            <?php if (!empty($googleBookData['items'])): ?>
                <?php $googleBook = $googleBookData['items'][0]['volumeInfo']; ?>
                <h4>Additional Details from Google Books API:</h4>
                <p><strong>Description:</strong> <?= htmlspecialchars($googleBook['description'] ?? 'No description available.') ?></p>
                <p><strong>Published Date:</strong> <?= htmlspecialchars($googleBook['publishedDate'] ?? 'N/A') ?></p>
                <p><strong>Page Count:</strong> <?= htmlspecialchars($googleBook['pageCount'] ?? 'N/A') ?></p>
                <p><strong>Categories:</strong> <?= htmlspecialchars(implode(', ', $googleBook['categories'] ?? [])) ?></p>
                <?php if (!empty($googleBook['imageLinks']['thumbnail'])): ?>
                    <img src="<?= htmlspecialchars($googleBook['imageLinks']['thumbnail']) ?>" alt="Book Cover" class="img-fluid">
                <?php endif; ?>
            <?php else: ?>
                <p>No additional details found from Google Books API.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#addToCartBtn').on('click', function() {
            const bookId = $(this).data('book-id');
            const quantity = 1;
            $.ajax({
                url: 'login_project1.php',
                type: 'POST',
                data: {
                    action: 'add_to_cart',
                    book_id: bookId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Book added to cart successfully!');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error adding to cart. Please try again.');
                }
            });
        });
    });
    </script>

</body>
</html>
