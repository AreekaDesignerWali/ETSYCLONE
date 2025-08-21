<?php
session_start();
require 'db.php';

// Initialize error message
$error_message = '';

// Handle cart addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please log in to add to cart. Redirecting to login...'); window.location.href='login.php';</script>";
        exit();
    }
    $user_id = $_SESSION['user_id'];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    
    // Debug: Show user_id and product_id
    echo "<script>console.log('User ID: $user_id, Product ID: $product_id');</script>";
    
    // Check if product exists
    $product_check = mysqli_query($conn, "SELECT id FROM products WHERE id = '$product_id'");
    if (!$product_check || mysqli_num_rows($product_check) == 0) {
        echo "<script>alert('Product not found.');</script>";
    } else {
        // Check if item already in cart
        $cart_check = mysqli_query($conn, "SELECT quantity FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'");
        if ($cart_row = mysqli_fetch_assoc($cart_check)) {
            $query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND product_id = '$product_id'";
        } else {
            $query = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES ('$user_id', '$product_id', 1, NOW())";
        }
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Product added to cart!'); window.location.href=window.location.href;</script>";
        } else {
            echo "<script>alert('Error adding to cart: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Handle filter form submission
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
$table_name = isset($_GET['table_name']) ? mysqli_real_escape_string($conn, $_GET['table_name']) : '';

$query = "SELECT * FROM products WHERE 1=1";
if ($search) {
    $query .= " AND title LIKE '%$search%'";
}
if ($category) {
    $query .= " AND category='$category'";
}
if ($min_price !== '') {
    $query .= " AND price >= '$min_price'";
}
if ($max_price !== '') {
    $query .= " AND price <= '$max_price'";
}
if ($table_name) {
    $query .= " AND table_name LIKE '%$table_name%'";
}

$products = mysqli_query($conn, $query);
if (!$products) {
    $error_message = "Error retrieving products: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Etsy Clone</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .search-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .search-filter input, .search-filter select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            flex: 1;
            min-width: 150px;
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
        .error-message {
            background-color: #ffe6e6;
            color: #d32f2f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        @media (max-width: 600px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            .search-filter {
                flex-direction: column;
            }
            .search-filter input, .search-filter select {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Products</h1>
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
        <form class="search-filter" method="GET" action="products.php">
            <input type="text" name="search" placeholder="Search products" value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Handmade" <?php if ($category == 'Handmade') echo 'selected'; ?>>Handmade</option>
                <option value="Jewelry" <?php if ($category == 'Jewelry') echo 'selected'; ?>>Jewelry</option>
                <option value="Home Decor" <?php if ($category == 'Home Decor') echo 'selected'; ?>>Home Decor</option>
                <option value="Digital Products" <?php if ($category == 'Digital Products') echo 'selected'; ?>>Digital Products</option>
            </select>
            <input type="number" name="min_price" placeholder="Min Price" step="0.01" value="<?php echo htmlspecialchars($min_price); ?>">
            <input type="number" name="max_price" placeholder="Max Price" step="0.01" value="<?php echo htmlspecialchars($max_price); ?>">
            <input type="text" name="table_name" placeholder="Table Name" value="<?php echo htmlspecialchars($table_name); ?>">
            <button type="submit" class="btn">Filter</button>
        </form>
        <div class="product-grid">
            <?php if ($products && mysqli_num_rows($products) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                        <p>$<?php echo number_format($product['price'], 2); ?></p>
                        <p>Table: <?php echo htmlspecialchars($product['table_name']); ?></p>
                        <button class="btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
        function addToCart(productId) {
            fetch('products.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId + '&action=add'
            }).then(response => response.text())
              .then(() => {
                  window.location.href = window.location.href;
              }).catch(error => {
                  alert('Error adding to cart: ' + error);
              });
        }
    </script>
</body>
</html>
