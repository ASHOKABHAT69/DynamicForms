<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // TRIM inputs to remove accidental spaces
    $u = $conn->real_escape_string(trim($_POST['username']));
    $p = $conn->real_escape_string(trim($_POST['password']));
    
    $sql = "SELECT * FROM admins WHERE username = '$u' AND password = '$p'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['admin_username'] = $row['username'];
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $message = "❌ Invalid Username or Password";
        
        // DEBUGGING: Uncomment the line below if you still can't login. It will show you what you typed.
        // echo "Debug: You typed User: [$u] / Pass: [$p]";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #1f2937; display: flex; height: 100vh; justify-content: center; align-items: center; font-family: sans-serif; margin: 0;}
        form { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 350px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        input { width: 100%; padding: 12px; margin: 10px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; }
        button:hover { background: #4338ca; }
        .error { color: red; font-weight: bold; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Admin Panel</h2>
        
        <?php if($message): ?>
            <span class="error"><?php echo $message; ?></span>
        <?php endif; ?>
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
