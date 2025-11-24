<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";
$debug_info = "";

// Handle Add Admin (Kept from previous version)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_admin_user'])) {
    $new_u = $conn->real_escape_string(trim($_POST['new_admin_user']));
    $new_p = $conn->real_escape_string(trim($_POST['new_admin_pass']));
    if(!empty($new_u) && !empty($new_p)) {
        $check = $conn->query("SELECT id FROM admins WHERE username='$new_u'");
        if ($check->num_rows > 0) {
            $msg = "<span style='color:red'>❌ Username exists!</span>";
        } else {
            $conn->query("INSERT INTO admins (username, password) VALUES ('$new_u', '$new_p')");
            $msg = "<span style='color:green'>✅ Admin Added!</span>";
        }
    }
}

$result = $conn->query("SELECT * FROM attendees ORDER BY reg_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f3f4f6; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .table-container { overflow-x: auto; background: white; padding: 10px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background: #4f46e5; color: white; }
        .extra-data { font-size: 13px; color: #555; background: #f9f9f9; padding: 5px; border-radius: 4px; display: inline-block; margin-top: 2px;}
        .logout { background: #ef4444; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; }
        .link-btn { text-decoration: none; color: #4f46e5; font-weight: bold; margin-right: 15px; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h3 style="margin:0; display:inline-block; margin-right:20px;">Attendee List</h3>
            <a href="admin_settings.php" class="link-btn">⚙️ Edit Form & Fields</a>
        </div>
        <a href="admin_login.php?logout=true" class="logout">Logout</a>
    </div>

    <div class="table-container">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Other Details</th></tr></thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        // Parse the Custom Data JSON
                        $extras_html = "";
                        if(isset($row['custom_data']) && !empty($row['custom_data'])) {
                            $json = json_decode($row['custom_data'], true);
                            if($json) {
                                foreach($json as $label => $val) {
                                    $extras_html .= "<div class='extra-data'><strong>$label:</strong> $val</div> ";
                                }
                            }
                        }

                        echo "<tr>
                            <td><strong>{$row['reg_id']}</strong></td>
                            <td>{$row['full_name']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['course']}</td>
                            <td>$extras_html</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No attendees found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- Admin Tool at bottom -->
    <div style="margin-top:20px; padding:20px; background:white; border-radius:8px;">
        <h3>Add Admin</h3>
        <?php if($msg) echo "<p>$msg</p>"; ?>
        <form method="POST">
            <input type="text" name="new_admin_user" placeholder="Username" required style="padding:5px;">
            <input type="text" name="new_admin_pass" placeholder="Password" required style="padding:5px;">
            <button type="submit" style="padding:5px 10px; background:#10b981; color:white; border:none; cursor:pointer;">Add</button>
        </form>
    </div>

</body>
</html>
