<?php
session_start();
require 'db.php';

// Debugging: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to add a product. Redirecting to login...'); window.location.href='login.php';</script>";
    exit();
}

// Ensure Uploads folder exists and is writable
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!is_writable($upload_dir)) {
    chmod($upload_dir, 0777);
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $table_name = mysqli_real_escape_string($conn, $_POST['table_name']);
    $image = $_FILES['image']['name'];
    $target = $upload_dir . basename($image);

    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $query = "INSERT INTO products (seller_id, title, description, price, category, image, stock, table_name) 
                      VALUES ('{$_SESSION['user_id']}', '$title', '$description', '$price', '$category', '$image', '$stock', '$table_name')";
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Product added!'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Error adding product: " . mysqli_error($conn) . "');</script>";
                unlink($target); // Remove the uploaded file if DB insert fails
            }
        } else {
            echo "<script>alert('Error moving uploaded file. Check folder permissions or disk space.');</script>";
        }
    } else {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
        ];
        $error_msg = $error_messages[$_FILES['image']['error']] ?? 'Unknown upload error.';
        echo "<script>alert('Image upload failed: $error_msg');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Etsy Clone</title>
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
        <h2>Add Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Product Title" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="number" name="price" placeholder="Price" step="0.01" required>
            <select name="category" required>
                <option value="Handmade">Handmade</option>
                <option value="Jewelry">Jewelry</option>
                <option value="Home Decor">Home Decor</option>
                <option value="Digital Products">Digital Products</option>
            </select>
            <input type="number" name="stock" placeholder="Stock" required>
            <input type="text" name="table_name" placeholder="Table Name (e.g., Featured)" value="">
            <input type="file" name="image" accept="image/*" required>
            <button type="submit" class="btn">Add Product</button>
        </form>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
