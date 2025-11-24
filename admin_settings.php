<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// A. Update General Settings
if (isset($_POST['update_titles'])) {
    $t = $conn->real_escape_string($_POST['page_title']);
    $s = $conn->real_escape_string($_POST['sub_title']);
    $m = intval($_POST['max_members']);
    if ($m < 1) $m = 1;
    $conn->query("UPDATE form_settings SET page_title='$t', sub_title='$s', max_members=$m WHERE id=1");
    $msg = "✅ Settings Updated!";
}

// B. Add Course Option
if (isset($_POST['add_course'])) {
    $c = $conn->real_escape_string($_POST['new_course_name']);
    if(!empty($c)) {
        $conn->query("INSERT INTO courses_list (course_name) VALUES ('$c')");
        $msg = "✅ Course Added!";
    }
}

// C. Delete Items (Course or Field)
if (isset($_GET['delete_course'])) {
    $id = intval($_GET['delete_course']);
    $conn->query("DELETE FROM courses_list WHERE id=$id");
    header("Location: admin_settings.php"); exit();
}
if (isset($_GET['delete_field'])) {
    $id = intval($_GET['delete_field']);
    $conn->query("DELETE FROM form_fields WHERE id=$id");
    header("Location: admin_settings.php"); exit();
}

// D. NEW: Add Custom Field
if (isset($_POST['add_field'])) {
    $lbl = $conn->real_escape_string($_POST['field_label']);
    $type = $_POST['field_type']; // 'text' or 'number'
    
    // Generate a unique internal name (e.g., field_1745382)
    $fname = "field_" . rand(100000, 999999);
    
    if(!empty($lbl)) {
        $conn->query("INSERT INTO form_fields (label, field_type, field_name) VALUES ('$lbl', '$type', '$fname')");
        $msg = "✅ New Field Added!";
    }
}

// Fetch Data
$settings = $conn->query("SELECT * FROM form_settings WHERE id=1")->fetch_assoc();
$courses = $conn->query("SELECT * FROM courses_list");
$fields = $conn->query("SELECT * FROM form_fields");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form Builder Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #4f46e5; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
        button { padding: 10px 15px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-red { background: #ef4444; text-decoration: none; font-size: 12px; padding: 5px 10px; color: white; border-radius: 3px; float: right; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        .item-row { padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .msg { color: green; font-weight: bold; margin-bottom: 10px; }
        .badge { background: #eee; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav">
        <a href="admin_dashboard.php">← Back to Dashboard</a>
        <span style="color:gray">Form Settings</span>
    </div>

    <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>

    <!-- 1. GENERAL SETTINGS -->
    <div class="card">
        <h2>📝 General Settings</h2>
        <form method="POST">
            <label>Main Title:</label>
            <input type="text" name="page_title" value="<?php echo $settings['page_title']; ?>">
            <label>Subtitle:</label>
            <input type="text" name="sub_title" value="<?php echo $settings['sub_title']; ?>">
            <label>Max Team Size:</label>
            <input type="number" name="max_members" value="<?php echo $settings['max_members'] ?? 1; ?>" min="1" max="20">
            <button type="submit" name="update_titles">Save Settings</button>
        </form>
    </div>

    <!-- 2. CUSTOM FORM FIELDS (NEW) -->
    <div class="card" style="border-left: 5px solid #10b981;">
        <h2>🛠️ Custom Form Fields</h2>
        <p style="font-size:14px; color:gray">Add extra inputs to your form (e.g. Age, College, ID Number).</p>
        
        <form method="POST" style="background: #f0fdf4; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
            <label>New Input Label:</label>
            <input type="text" name="field_label" placeholder="e.g. Age" required>
            
            <label>Input Type:</label>
            <select name="field_type">
                <option value="text">Text (Varchar)</option>
                <option value="number">Number (Integer)</option>
                <option value="date">Date</option>
            </select>
            
            <button type="submit" name="add_field" style="background:#10b981;">+ Add Field</button>
        </form>

        <h3>Active Fields:</h3>
        <?php while($f = $fields->fetch_assoc()): ?>
            <div class="item-row">
                <div>
                    <strong><?php echo $f['label']; ?></strong>
                    <span class="badge"><?php echo strtoupper($f['field_type']); ?></span>
                </div>
                <a href="?delete_field=<?php echo $f['id']; ?>" class="btn-red" onclick="return confirm('Delete this field?');">Delete</a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- 3. MANAGE COURSES -->
    <div class="card">
        <h2>📚 Course Options</h2>
        <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
            <div style="display:flex; gap:10px;">
                <input type="text" name="new_course_name" placeholder="e.g. Graphic Design" required style="margin-bottom:0;">
                <button type="submit" name="add_course">Add</button>
            </div>
        </form>
        <?php while($row = $courses->fetch_assoc()): ?>
            <div class="item-row">
                <?php echo $row['course_name']; ?>
                <a href="?delete_course=<?php echo $row['id']; ?>" class="btn-red" onclick="return confirm('Delete?');">Delete</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
