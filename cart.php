<?php
session_start();
require 'db.php';

// Initialize error message
$error_message = '';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view your cart. Redirecting to login...'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Debug: Show user_id
echo "<script>console.log('Current User ID: $user_id');</script>";

$cart_query = "SELECT c.id, p.title, p.price, c.quantity, p.image 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);
if (!$cart_result) {
    $error_message = "Error fetching cart: " . mysqli_error($conn);
}

$total = 0;
if ($cart_result) {
    while ($item = mysqli_fetch_assoc($cart_result)) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Etsy Clone</title>
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
        .cart-items {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .cart-item img {
            max-width: 50px;
            height: auto;
            margin-right: 10px;
        }
        .cart-item p {
            margin: 0 10px;
        }
        .total {
            text-align: right;
            margin-top: 20px;
            font-weight: bold;
        }
        .btn {
            background-color: #ff6f61;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
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
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Cart</h1>
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
        <div class="cart-items">
            <h2>Your Cart</h2>
            <?php if ($cart_result && mysqli_num_rows($cart_result) > 0): ?>
                <?php
                mysqli_data_seek($cart_result, 0); // Reset pointer
                while ($item = mysqli_fetch_assoc($cart_result)): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <p><?php echo htmlspecialchars($item['title']); ?> - $<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></p>
                    </div>
                <?php endwhile; ?>
                <div class="total">Total: $<?php echo number_format($total, 2); ?></div>
                <a href="checkout.php" onclick="redirectTo('checkout.php')"><button class="btn">Proceed to Checkout</button></a>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
