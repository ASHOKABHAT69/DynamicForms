<?php
require 'db.php';

// Fetch Settings, Courses, AND Custom Fields
$settings = $conn->query("SELECT * FROM form_settings WHERE id=1")->fetch_assoc();
$courses_res = $conn->query("SELECT * FROM courses_list ORDER BY course_name ASC");
$fields_res = $conn->query("SELECT * FROM form_fields");

// Prepare Data for JS
$courses = [];
while($r = $courses_res->fetch_assoc()) $courses[] = $r['course_name'];

$custom_fields = [];
while($r = $fields_res->fetch_assoc()) $custom_fields[] = $r;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['page_title']; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { background-color: white; width: 100%; max-width: 600px; margin: 0 auto; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #4f46e5; padding: 30px; text-align: center; color: white; }
        .member-block { padding: 20px; border-bottom: 5px solid #f3f4f6; position: relative; }
        .member-title { font-weight: bold; color: #4f46e5; margin-bottom: 15px; display: block; font-size: 18px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 15px; background-color: #10b981; color: white; border: none; font-size: 18px; cursor: pointer; font-weight: bold; }
        .btn-add { width: 100%; padding: 12px; background-color: #4f46e5; color: white; border: none; font-size: 16px; cursor: pointer; border-bottom: 1px solid #333;}
        .btn-remove { position: absolute; top: 20px; right: 20px; background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .member-block:first-child .btn-remove { display: none; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2><?php echo $settings['page_title']; ?></h2>
            <p><?php echo $settings['sub_title']; ?></p>
        </div>

        <form action="process.php" method="POST">
            <div id="members-container">
                <!-- Javascript will duplicate this block -->
                <div class="member-block">
                    <span class="member-title">Member 1</span>
                    <button type="button" class="btn-remove" onclick="removeMember(this)">Remove</button>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname[]" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email[]" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone[]" required>
                    </div>
                    <div class="form-group">
                        <label>Select Course</label>
                        <select name="course[]" required>
                            <option value="" disabled selected>Choose...</option>
                            <?php foreach($courses as $c) echo "<option value='$c'>$c</option>"; ?>
                        </select>
                    </div>

                    <!-- DYNAMIC CUSTOM FIELDS RENDERED HERE -->
                    <?php foreach($custom_fields as $field): ?>
                        <div class="form-group">
                            <label><?php echo $field['label']; ?></label>
                            <input type="<?php echo $field['field_type']; ?>" 
                                   name="<?php echo $field['field_name']; ?>[]" required>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <?php if(isset($settings['max_members']) && $settings['max_members'] > 1): ?>
                <button type="button" class="btn-add" id="addBtn" onclick="addMember()">+ Add Another Member</button>
            <?php endif; ?>

            <button type="submit" class="btn-submit">Submit Registration</button>
        </form>
    </div>

    <script>
        const maxMembers = <?php echo isset($settings['max_members']) ? $settings['max_members'] : 1; ?>;
        
        // Prepare options for JS
        const courseOptions = `<?php foreach($courses as $c) echo "<option value='$c'>$c</option>"; ?>`;
        
        // Prepare custom fields for JS
        let customFieldsHtml = '';
        <?php foreach($custom_fields as $field): ?>
            customFieldsHtml += `
            <div class="form-group">
                <label><?php echo $field['label']; ?></label>
                <input type="<?php echo $field['field_type']; ?>" name="<?php echo $field['field_name']; ?>[]" required>
            </div>`;
        <?php endforeach; ?>

        let count = 1;

        function addMember() {
            if(count >= maxMembers) {
                alert("Maximum team size reached!"); return;
            }
            count++;
            
            const html = `
            <div class="member-block">
                <span class="member-title">Member ${count}</span>
                <button type="button" class="btn-remove" onclick="removeMember(this)">Remove</button>
                <div class="form-group"><label>Full Name</label><input type="text" name="fullname[]" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email[]" required></div>
                <div class="form-group"><label>Phone Number</label><input type="tel" name="phone[]" required></div>
                <div class="form-group"><label>Select Course</label>
                    <select name="course[]" required><option value="" disabled selected>Choose...</option>${courseOptions}</select>
                </div>
                ${customFieldsHtml} <!-- Injects your custom fields -->
            </div>`;
            
            document.getElementById('members-container').insertAdjacentHTML('beforeend', html);
        }

        function removeMember(btn) {
            btn.closest('.member-block').remove();
            count--;
            document.querySelectorAll('.member-title').forEach((el, index) => el.innerText = 'Member ' + (index + 1));
        }
    </script>
</body>
</html>
