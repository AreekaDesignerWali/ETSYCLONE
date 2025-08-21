<?php
session_start();
require 'db.php';

$featured_products = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
$trending_products = mysqli_query($conn, "SELECT * FROM products ORDER BY RAND() LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etsy Clone - Homepage</title>
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
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #333;
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .product-card h3 {
            font-size: 18px;
            margin: 10px 0;
        }
        .product-card p {
            color: #555;
        }
        .btn {
            background-color: #ff6f61;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #e55a50;
        }
        @media (max-width: 600px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Etsy Clone</h1>
    </header>
    <nav>
        <div>
            <a href="index.php">Home</a>
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
        <h2>Featured Products</h2>
        <div class="product-grid">
            <?php while ($product = mysqli_fetch_assoc($featured_products)): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>">
                    <h3><?php echo $product['title']; ?></h3>
                    <p>$<?php echo $product['price']; ?></p>
                    <button class="btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                </div>
            <?php endwhile; ?>
        </div>
        <h2>Trending Products</h2>
        <div class="product-grid">
            <?php while ($product = mysqli_fetch_assoc($trending_products)): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>">
                    <h3><?php echo $product['title']; ?></h3>
                    <p>$<?php echo $product['price']; ?></p>
                    <button class="btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
        function addToCart(productId) {
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId + '&action=add'
            }).then(response => response.text())
              .then(() => alert('Product added to cart!'));
        }
    </script>
</body>
</html>
