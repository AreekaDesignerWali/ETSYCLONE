<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Username or email already exists!');</script>";
    } else {
        $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            // Immediate redirection to login.php after successful signup
            echo "<script>window.location.href='login.php';</script>";
            exit(); // Ensure no further code is executed
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Etsy Clone</title>
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
        .form-container input {
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
        .link {
            text-align: center;
            margin-top: 10px;
        }
        .link a {
            color: #ff6f61;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
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
        <h2>Signup</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Signup</button>
        </form>
        <div class="link">
            <a href="#" onclick="redirectTo('login.php')">Already have an account? Login</a>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
