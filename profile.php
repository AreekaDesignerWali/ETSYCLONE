<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

$products = mysqli_query($conn, "SELECT * FROM products WHERE seller_id='$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Etsy Clone</title>
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
        .btn {
            background-color: #ff6f61;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
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
        <h1>My Profile</h1>
    </header>
    <div class="container">
        <h2>Welcome, <?php echo $user['username']; ?></h2>
        <p>Email: <?php echo $user['email']; ?></p>
        <button class="btn" onclick="redirectTo('add_product.php')">Add Product</button>
        <h3>Your Products</h3>
        <div class="product-grid">
            <?php while ($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>">
                    <h3><?php echo $product['title']; ?></h3>
                    <p>$<?php echo $product['price']; ?></p>
                    <button class="btn" onclick="redirectTo('edit_product.php?id=<?php echo $product['id']; ?>')">Edit</button>
                    <button class="btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('edit_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id + '&action=delete'
                }).then(() => window.location.reload());
            }
        }
    </script>
</body>
</html>
