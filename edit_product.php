<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM products WHERE id='$id' AND seller_id='{$_SESSION['user_id']}'";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];
    $query = "DELETE FROM products WHERE id='$id' AND seller_id='{$_SESSION['user_id']}'";
    mysqli_query($conn, $query);
    echo "<script>window.location.href='profile.php';</script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {
    $id = $_POST['id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $image = $product['image'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    $query = "UPDATE products SET title='$title', description='$description', price='$price', 
              category='$category', stock='$stock', image='$image' 
              WHERE id='$id' AND seller_id='{$_SESSION['user_id']}'";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Product updated!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Etsy Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            background-color: #ff6f61;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background-color: #e55a50;
        }
        @media (max-width: 600px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <input type="text" name="title" value="<?php echo $product['title']; ?>" required>
            <textarea name="description" required><?php echo $product['description']; ?></textarea>
            <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
            <select name="category" required>
                <option value="Handmade" <?php if ($product['category'] == 'Handmade') echo 'selected'; ?>>Handmade</option>
                <option value="Jewelry" <?php if ($product['category'] == 'Jewelry') echo 'selected'; ?>>Jewelry</option>
                <option value="Home Decor" <?php if ($product['category'] == 'Home Decor') echo 'selected'; ?>>Home Decor</option>
                <option value="Digital Products" <?php if ($product['category'] == 'Digital Products') echo 'selected'; ?>>Digital Products</option>
            </select>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
            <input type="file" name="image" accept="image/*">
            <button type="submit" class="btn">Update Product</button>
        </form>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
