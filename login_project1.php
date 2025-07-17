<?php
header('Content-Type: application/json');
session_start();

include_once '../includes/Database.php';

$conn = Database::getConnection();
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if ($_POST['action'] == 'insert') {
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);
        $user_type = $_POST['user_type'] ?? '0';
        
        $is_admin = ($user_type === '1') ? 1 : 0;

        if ($password) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $result_1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result_1 && count($result_1) > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email exists']);
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users(username, email, password_hash, is_admin) VALUES (:username, :email, :password_hash, :is_admin)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password_hash', $hashedPassword);
                $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'User  added successfully.',
                        'redirect' => 'login_user.php',
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Error adding user: ' . $stmt->errorInfo()[2]
                    ]);
                }
            }
        }
    } 
    
    
    
    else if ($_POST['action'] == 'login_process') {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result_1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result_1 && count($result_1) > 0) {
            $result = $result_1[0];
            $hashed_password = $result['password_hash'];
            $is_admin = $result['is_admin'];

            if (password_verify($password, $hashed_password)) {
                $_SESSION['email'] = $email;
                $_SESSION['id'] = $result['id']; 
                $_SESSION['username'] = $result['username'];
                if ($is_admin) {
                    echo json_encode(['status' => 'success', 
                    'redirect' => 'admin_dashboard.php',
                    'id' => $result['id'],
                    'username' => $result['username']
                    ]);
                } else {
                    echo json_encode(['status' => 'success', 'redirect' => 'home_project1.php']);
                }
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
            }
        }
    } 
    
    
    
    
    else if ($_POST['action'] == 'add_book') {
        $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars(trim($_POST['author']), ENT_QUOTES, 'UTF-8');
        $price = filter_var(trim($_POST['price']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock = filter_var(trim($_POST['stock']), FILTER_SANITIZE_NUMBER_INT);
        
        $stmt = $conn->prepare("INSERT INTO books (title, author, price, stock) VALUES (:title, :author, :price, :stock)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Book added successfully.',
                'redirect' => 'admin_dashboard.php',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error adding product: ' . $stmt->errorInfo()[2]
            ]);
        }
    } 
    
    
    
    
    
    else if ($_POST['action'] == 'view_book') {
        $stmt = $conn->prepare("SELECT id ,title, author, price FROM books");
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $books = array();
            
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $row['title'] = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                    $row['author'] = htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8');
                    $row['price'] = (float)$row['price'];
                    $books[] = $row;
                }
            }
        
            echo json_encode($books);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error fetching books: ' . $stmt->errorInfo()[2]
            ]);
        }
    }

    else if ($_POST['action'] == 'search_books') {
        $query = $_POST['query'];
    $stmt = $conn->prepare("SELECT id, title, author, price FROM books WHERE title LIKE :query OR author LIKE :query");
    $stmt->execute(['query' => '%' . $query . '%']);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($result);
    exit;

    }




   else if ($_POST['action'] == 'get_book_details') {

        if (isset($_POST['id'])) {
            $bookId = $_POST['id'];
            $stmt = $conn->prepare("SELECT * FROM books WHERE id = :id");
            $stmt->bindParam(':id', $bookId);
            $stmt->execute();
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            // print_r($book);
            if ($book) {
                echo json_encode(['status' => 'success', 'book' => $book]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Book not found.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No book ID provided.']);
        }
    }


    else if ($_POST['action'] == 'add_to_cart') {
        if (!isset($_SESSION['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
            exit;
        }

        $userId = $_SESSION['id']; 
        $bookId = $_POST['book_id'];
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        $stockStmt = $conn->prepare("SELECT stock FROM books WHERE id = :book_id");
        $stockStmt->bindParam(':book_id', $bookId);
        $stockStmt->execute();
        $stock = $stockStmt->fetchColumn();
        if ($stock === false) {
            echo json_encode(['status' => 'error', 'message' => 'Book not found']);
            exit;
        }
        if ($stock < $quantity) {
            echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
            exit;
        }
        
        $checkStmt = $conn->prepare("SELECT id, quantity FROM carts WHERE user_id = :user_id AND book_id = :book_id");
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->bindParam(':book_id', $bookId);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {

            $already_in_cart = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $already_in_cart['quantity'] + $quantity;

            if ($stock < $newQuantity) {
                echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
                exit;
            }
            $updateCartStmt = $conn->prepare("UPDATE carts SET quantity = :quantity WHERE id = :cart_id");
            $updateCartStmt->bindParam(':quantity', $newQuantity);
            $updateCartStmt->bindParam(':cart_id', $already_in_cart['id']);
            // if ($updateCartStmt->execute()) {
            //     echo json_encode(['status' => 'success', 'message' => 'Book quantity updated in cart']);
            // } else {
            //     echo json_encode(['status' => 'error', 'message' => 'Failed to update book quantity in cart']);
            // }/
            $updateCartStmt->execute();
        } else {
        
            $stmt = $conn->prepare("INSERT INTO carts (user_id, book_id, quantity) VALUES (:user_id, :book_id, :quantity)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':book_id', $bookId);
            $stmt->bindParam(':quantity', $quantity); 
            $stmt->execute();
            }
            $newStock = $stock - $quantity;

            $updateStockStmt = $conn->prepare("UPDATE books SET stock = :stock WHERE id = :book_id");
            $updateStockStmt->bindParam(':stock', $newStock);
            $updateStockStmt->bindParam(':book_id', $bookId);
            $updateStockStmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Book added to cart']);
    }

    else if ($_POST['action'] == 'get_cart_items') {
        if (!isset($_SESSION['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User  not logged in']);
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed.']);
            exit;
        }
        $userId = $_SESSION['id'];

        $stmt = $conn->prepare("
            SELECT c.id as cart_id, b.id as book_id, b.title, b.author, b.price, c.quantity 
            FROM carts c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $cartItems]);
    }
   
   else if ($_POST['action'] == 'update_cart_quantity') {
    if (!isset($_SESSION['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User  not logged in']);
        exit;
    }
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed.']);
        exit;
    }
    $userId = $_SESSION['id'];
    $cartId = $_POST['cart_id'];
    $newQuantity = max(1, intval($_POST['quantity'])); 

   
    $stmt = $conn->prepare("SELECT quantity, book_id FROM carts WHERE id = :cart_id AND user_id = :user_id");
    $stmt->bindParam(':cart_id', $cartId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cartItem) {
        echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
        exit;
    }

    $oldQuantity = $cartItem['quantity'];
    $bookId = $cartItem['book_id'];
    $quantityDiff = $newQuantity - $oldQuantity;

   
    $stockStmt = $conn->prepare("SELECT stock FROM books WHERE id = :book_id");
    $stockStmt->bindParam(':book_id', $bookId);
    $stockStmt->execute();
    $stock = $stockStmt->fetchColumn();

    if ($quantityDiff > 0 && $stock < $quantityDiff) {
        echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
        exit;
    }

    
    $updateCartStmt = $conn->prepare("UPDATE carts SET quantity = :quantity WHERE id = :id AND user_id = :user_id");
    $updateCartStmt->bindParam(':quantity', $newQuantity);
    $updateCartStmt->bindParam(':id', $cartId);
    $updateCartStmt->bindParam(':user_id', $userId);

    if (!$updateCartStmt->execute()) {
        
        $errorInfo = $updateCartStmt->errorInfo();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity: ' . $errorInfo[2]]);
        exit;
    }

    
    if ($quantityDiff > 0) {
        $newStock = $stock - $quantityDiff;
        $updateStockStmt = $conn->prepare("UPDATE books SET stock = :stock WHERE id = :book_id");
        $updateStockStmt->bindParam(':stock', $newStock);
        $updateStockStmt->bindParam(':book_id', $bookId);

        if (!$updateStockStmt->execute()) {
            
            $errorInfo = $updateStockStmt->errorInfo();
            echo json_encode(['status' => 'error', 'message' => 'Failed to update stock: ' . $errorInfo[2]]);
            exit;
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Quantity updated']);
}

    else if ($_POST['action'] == 'remove_from_cart') {
        if (!isset($_SESSION['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User  not logged in']);
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed.']);
            exit;
        }
        $userId = $_SESSION['id'];
        $cartId = $_POST['cart_id'];

        $stmt = $conn->prepare("SELECT quantity, book_id FROM carts WHERE id = :cart_id AND user_id = :user_id");
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cartItem) {
            echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
            exit;
        }

        $quantity = $cartItem['quantity'];
        $bookId = $cartItem['book_id'];
        
        $stmt = $conn->prepare("DELETE FROM carts WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $cartId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        // if ($stmt->execute()) {
        //     echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
        // } else {
        //     echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
        // }
        echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
    }

    else if ($_POST['action'] == 'process_checkout') {
        if (!isset($_SESSION['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User  not logged in']);
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed.']);
            exit;
        }
        $userId = $_SESSION['id'];
        $fullName = trim($_POST['fullName'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zip = trim($_POST['zip'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $conn->beginTransaction();

        try {
            $stmt = $conn->prepare("
            SELECT c.book_id, c.quantity, b.price, b.stock
            FROM carts c
            JOIN books b ON c.book_id = b.id
            WHERE c.user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cartItems)) {
                throw new Exception('Your cart is empty.');
            }

            $total = 0;
            foreach ($cartItems as $item) {
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception("Not enough stock for book ID {$item['book_id']}.");
                }
                $total += $item['price'] * $item['quantity'];
            }

            $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total, created_at) VALUES (:user_id, :total, NOW())");
            $orderStmt->bindParam(':user_id', $userId);
            $orderStmt->bindParam(':total', $total);
            $orderStmt->execute();

            $orderId = $conn->lastInsertId();

            $orderDetailStmt = $conn->prepare("
                INSERT INTO order_details (order_id, book_id, quantity, price)
                VALUES (:order_id, :book_id, :quantity, :price)
                ");

            $updateStockStmt = $conn->prepare("
                UPDATE books SET stock = stock - :quantity WHERE id = :book_id
            ");

           foreach ($cartItems as $item) {
   
                error_log("Processing book ID: {$item['book_id']}, Quantity: {$item['quantity']}");
    
                $orderDetailStmt->bindParam(':order_id', $orderId);
                $orderDetailStmt->bindParam(':book_id', $item['book_id']);
                $orderDetailStmt->bindParam(':quantity', $item['quantity']);
                $orderDetailStmt->bindParam(':price', $item['price']);
                $orderDetailStmt->execute();
    
                error_log("Updating stock for book ID {$item['book_id']} by {$item['quantity']}");
                $updateStockStmt->bindParam(':quantity', $item['quantity']);
                $updateStockStmt->bindParam(':book_id', $item['book_id']);
                $updateStockStmt->execute();
            }

            $clearCartStmt = $conn->prepare("DELETE FROM carts WHERE user_id = :user_id");
            $clearCartStmt->bindParam(':user_id', $userId);
            $clearCartStmt->execute();

            $conn->commit();

            echo json_encode(['status' => 'success', 'order_id' => $orderId]);
            exit;

            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
    }



    else if ($_POST['action'] == 'fetch_order_details') {
        if (!isset($_SESSION['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User  not logged in']);
            exit;
        }

    
        $orderId = intval($_POST['order_id']);

        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :order_id");
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            exit;
        }

        $orderDetailsStmt = $conn->prepare("SELECT od.*, b.title FROM order_details od JOIN books b ON od.book_id = b.id WHERE od.order_id = :order_id");
        $orderDetailsStmt->bindParam(':order_id', $orderId);
        $orderDetailsStmt->execute();
        $orderDetails = $orderDetailsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'order' => $order, 'order_details' => $orderDetails]);
        exit;
    }


}


    $conn = null; 

?>
