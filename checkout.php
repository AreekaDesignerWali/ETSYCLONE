<?php
session_start();
require 'db.php';

// Initialize error message
$error_message = '';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to place an order. Redirecting to login...'); window.location.href='login.php';</script>";
    exit();
}

// Validate user_id exists in users table
$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
$validate_query = "SELECT id FROM users WHERE id = '$user_id'";
$validate_result = mysqli_query($conn, $validate_query);
if (!$validate_result || mysqli_num_rows($validate_result) == 0) {
    echo "<script>alert('Invalid user session. Please log in again. Redirecting to login...'); window.location.href='login.php';</script>";
    exit();
}

// Debug: Show user_id
echo "<script>console.log('Current User ID: $user_id');</script>";

// Handle place order action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'place_order') {
    mysqli_begin_transaction($conn);

    try {
        // Calculate total from cart
        $cart_query = "SELECT c.product_id, c.quantity, p.price 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = '$user_id'";
        $cart_result = mysqli_query($conn, $cart_query);
        if (!$cart_result) {
            throw new Exception("Error fetching cart: " . mysqli_error($conn));
        }

        $order_items = [];
        $total = 0;
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $total += $item['price'] * $item['quantity'];
            $order_items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }

        if (empty($order_items)) {
            throw new Exception("Cart is empty. Please add items before placing an order.");
        }

        // Insert order
        $order_query = "INSERT INTO orders (user_id, total, status) VALUES ('$user_id', '$total', 'Pending')";
        if (!mysqli_query($conn, $order_query)) {
            throw new Exception("Error creating order: " . mysqli_error($conn));
        }
        $order_id = mysqli_insert_id($conn);

        // Insert order items
        foreach ($order_items as $item) {
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                           VALUES ('$order_id', '{$item['product_id']}', '{$item['quantity']}', '{$item['price']}')";
            if (!mysqli_query($conn, $item_query)) {
                throw new Exception("Error adding order item: " . mysqli_error($conn));
            }
        }

        // Clear cart
        $clear_cart_query = "DELETE FROM cart WHERE user_id = '$user_id'";
        if (!mysqli_query($conn, $clear_cart_query)) {
            throw new Exception("Error clearing cart: " . mysqli_error($conn));
        }

        mysqli_commit($conn);
        echo "<script>alert('Order placed successfully!'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
        echo "<script>alert('Error: $error_message');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Etsy Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        header {
            background-color: #ff6f61;
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            background-color: #333;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .checkout-form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .checkout-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .card-selection {
            margin: 10px 0;
        }
        .card-selection label {
            margin-right: 10px;
        }
        .btn {
            background-color: #ff6f61;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background-color: #e55a50;
        }
        .error-message {
            background-color: #ffe6e6;
            color: #d32f2f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Checkout</h1>
    </header>
    <nav>
        <div>
            <a href="#" onclick="redirectTo('index.php')">Home</a>
            <a href="#" onclick="redirectTo('products.php')">Products</a>
            <a href="#" onclick="redirectTo('cart.php')">Cart</a>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirectTo('profile.php')">Profile</a>
                <a href="#" onclick="redirectTo('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="redirectTo('login.php')">Login</a>
                <a href="#" onclick="redirectTo('signup.php')">Signup</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <div class="checkout-form">
            <h2>Payment Details</h2>
            <form method="POST">
                <div class="card-selection">
                    <label><input type="radio" name="card" value="card1" required> Card 1 (****1234)</label>
                    <label><input type="radio" name="card" value="card2" required> Card 2 (****5678)</label>
                </div>
                <input type="hidden" name="action" value="place_order">
                <button type="submit" class="btn">Place Order</button>
            </form>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
