<?php
require 'db.php';

$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';
$status = "Not Found";
$color = "#ef4444"; // Red
$msg = "Invalid Ticket";

if ($id) {
    $sql = "SELECT * FROM attendees WHERE reg_id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status = "VERIFIED";
        $color = "#10b981"; // Green
        $msg = "Allowed Entry";
        $name = $row['full_name'];
        $course = $row['course'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Verification</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #eee; padding: 20px; }
        .card { background: white; padding: 40px; border-radius: 10px; max-width: 300px; margin: 0 auto; }
        .status { font-size: 24px; font-weight: bold; color: <?php echo $color; ?>; border: 2px solid <?php echo $color; ?>; padding: 10px; display: inline-block; margin-bottom: 20px; border-radius: 8px;}
    </style>
</head>
<body>
    <div class="card">
        <div class="status"><?php echo $status; ?></div>
        
        <?php if($status == "VERIFIED"): ?>
            <h2><?php echo $name; ?></h2>
            <p>Course: <?php echo $course; ?></p>
            <p>ID: <?php echo $id; ?></p>
        <?php else: ?>
            <h2>Ticket Not Found</h2>
            <p>Please check the ID manually.</p>
        <?php endif; ?>
    </div>
</body>
</html>
