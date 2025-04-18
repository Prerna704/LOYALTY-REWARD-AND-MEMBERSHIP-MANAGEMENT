<?php
require_once 'config.php';

if (!isset($_SESSION["loggedin"])) {
    header("location: login.php");
    exit;
}

// Fetch user data
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update basic info
    if (isset($_POST['update_info'])) {
        $full_name = trim($_POST["full_name"]);
        $phone = trim($_POST["phone"]);
        $email = trim($_POST["email"]);
        
        $sql = "UPDATE users SET full_name = :full_name, phone = :phone, email = :email WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":full_name", $full_name, PDO::PARAM_STR);
        $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION["success"] = "Profile updated successfully!";
            header("location: profile.php");
            exit;
        } else {
            $error = "Error updating profile.";
        }
    }
    
    // Update password
    if (isset($_POST['update_password'])) {
        $current_password = trim($_POST["current_password"]);
        $new_password = trim($_POST["new_password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $sql = "UPDATE users SET password = :password WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":password", $new_password_hash, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $success = "Password updated successfully!";
                    } else {
                        $error = "Error updating password.";
                    }
                } else {
                    $error = "Password must be at least 6 characters.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
    
    // Update profile picture
    if (isset($_POST['update_picture']) && !empty($_FILES['profile_pic']['name'])) {
        $target_dir = "profile_pics/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $new_filename = "user_" . $_SESSION["id"] . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check !== false) {
            // Limit file size to 2MB
            if ($_FILES["profile_pic"]["size"] <= 2000000) {
                // Allow certain file formats
                $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
                if (in_array(strtolower($file_extension), $allowed_extensions)) {
                    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                        // Delete old profile picture if it's not the default
                        if ($user['profile_pic'] != 'default.jpg' && file_exists("profile_pics/" . $user['profile_pic'])) {
                            unlink("profile_pics/" . $user['profile_pic']);
                        }
                        
                        $sql = "UPDATE users SET profile_pic = :profile_pic WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":profile_pic", $new_filename, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
                        
                        if ($stmt->execute()) {
                            $_SESSION["profile_pic"] = $new_filename;
                            $success = "Profile picture updated successfully!";
                        } else {
                            $error = "Error updating profile picture in database.";
                        }
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                $error = "Sorry, your file is too large (max 2MB).";
            }
        } else {
            $error = "File is not an image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Loyalty Rewards</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4A6FA5;
            --secondary: #166088;
            --accent: #4FC3F7;
            --success: #4CAF50;
            --danger: #F44336;
            --light: #F8F9FA;
            --dark: #343A40;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .container2 {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #166088;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .profile-section {
            display: flex;
            margin-bottom: 30px;
        }

        .profile-pic-container {
            margin-right: 30px;
            text-align: center;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--accent);
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.25);
            outline: none;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--secondary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: var(--success);
        }

        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: var(--danger);
        }

        .section-title {
            color: var(--secondary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .tab-container {
            margin-top: 30px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            border-bottom: 3px solid var(--secondary);
            color: var(--secondary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1 class="section-title">Edit Profile</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-section">
            <div class="profile-pic-container">
                <img src="profile_pics/<?php echo htmlspecialchars($user['profile_pic'] ?? 'default.jpg'); ?>" class="profile-pic" alt="Profile Picture">
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="display: none;">
                    <label for="profile_pic" class="btn btn-primary" style="margin-bottom: 10px;">
                        <i class="fas fa-camera"></i> Change Photo
                    </label>
                    <button type="submit" name="update_picture" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                </form>
            </div>
            
            <div style="flex-grow: 1;">
                <div class="tab-container">
                    <div class="tab-buttons">
                        <button class="tab-btn active" onclick="openTab(event, 'personal-info')">Personal Info</button>
                        <button class="tab-btn" onclick="openTab(event, 'change-password')">Change Password</button>
                    </div>
                    
                    <div id="personal-info" class="tab-content active">
                        <form method="post">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Member Since</label>
                                <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user['reg_date'])); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label>Membership Level</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['membership_level']); ?>" disabled>
                            </div>
                            
                            <button type="submit" name="update_info" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                    
                    <div id="change-password" class="tab-content">
                        <form method="post">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" name="update_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            // Hide all tab content
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            // Remove active class from all tab buttons
            var tabbuttons = document.getElementsByClassName("tab-btn");
            for (var i = 0; i < tabbuttons.length; i++) {
                tabbuttons[i].classList.remove("active");
            }
            
            // Show the current tab and add active class to the button
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
        
        // Preview profile picture before upload
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-pic').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>