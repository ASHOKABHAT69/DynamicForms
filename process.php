<?php
require 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Standard Fields
    $names = $_POST['fullname'];
    $emails = $_POST['email'];
    $phones = $_POST['phone'];
    $courses = $_POST['course'];
    
    // Fetch Custom Field Definitions to know what to look for
    $field_defs = [];
    $res = $conn->query("SELECT * FROM form_fields");
    while($r = $res->fetch_assoc()) {
        $field_defs[] = $r;
    }

    $success_list = [];

    // Loop through each person
    for ($i = 0; $i < count($names); $i++) {
        
        $n = $conn->real_escape_string($names[$i]);
        $e = $conn->real_escape_string($emails[$i]);
        $p = $conn->real_escape_string($phones[$i]);
        $c = $conn->real_escape_string($courses[$i]);
        
        // --- HANDLE CUSTOM DATA ---
        $custom_entry = [];
        foreach($field_defs as $def) {
            $key = $def['field_name']; // e.g., field_123456
            // Get the value for THIS person (index $i)
            if(isset($_POST[$key][$i])) {
                $val = $_POST[$key][$i];
                // Save nicely: "Age: 25"
                $custom_entry[ $def['label'] ] = $val;
            }
        }
        // Convert array to text for storage
        $custom_json = $conn->real_escape_string(json_encode($custom_entry));
        // --------------------------

        $regID = "REG-" . rand(10000, 99999);
        
        // Save to DB (Includes custom_data)
        $sql = "INSERT INTO attendees (reg_id, full_name, email, phone, course, custom_data) 
                VALUES ('$regID', '$n', '$e', '$p', '$c', '$custom_json')";
        
        if ($conn->query($sql) === TRUE) {
            // QR Gen Logic
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['PHP_SELF']);
            $path = str_replace('\\', '/', $path);
            $path = rtrim($path, '/');
            $currentDomain = "$protocol://$host$path";
            $qrData = $currentDomain . "/verify.php?id=" . $regID;

            $success_list[] = ['name' => $n, 'id' => $regID, 'qr' => $qrData];
        }
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; text-align: center; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; margin-bottom: 20px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; align-items: center; text-align: left; }
        .qr-area { margin-right: 20px; }
        .info-area h3 { margin: 0 0 5px 0; color: #4f46e5; }
        .info-area p { margin: 0; color: #555; }
        h1 { color: #10b981; }
        .btn { background: #333; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Success!</h1>
        <p>You have successfully registered <?php echo count($success_list); ?> people.</p>

        <?php foreach($success_list as $index => $person): ?>
            <div class="card">
                <div class="qr-area" id="qrcode-<?php echo $index; ?>"></div>
                <div class="info-area">
                    <h3><?php echo htmlspecialchars($person['name']); ?></h3>
                    <p>Ticket ID: <strong><?php echo $person['id']; ?></strong></p>
                    <p style="font-size:12px; color:gray">Scan at entrance</p>
                </div>
            </div>
            <script>
                new QRCode(document.getElementById("qrcode-<?php echo $index; ?>"), {
                    text: "<?php echo $person['qr']; ?>",
                    width: 100, height: 100
                });
            </script>
        <?php endforeach; ?>

        <a href="index.php" class="btn">Return to Form</a>
    </div>
</body>
</html>
