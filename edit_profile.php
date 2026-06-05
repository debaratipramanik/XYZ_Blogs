<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if(!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user($conn, $user_id);
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = sanitize($_POST['bio']);
    $profile_pic = $user['profile_pic'];

    // Handle Image Upload
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $new_filename = 'profile_' . $user_id . '_' . uniqid() . '.' . $filetype;
            $upload_dir = 'assets/uploads/';
            
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $new_filename)) {
                $profile_pic = $new_filename;
                // Don't delete default.png
                if($user['profile_pic'] != 'default.png' && file_exists($upload_dir . $user['profile_pic'])) {
                    unlink($upload_dir . $user['profile_pic']);
                }
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF.";
        }
    }

    if(empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET bio = ?, profile_pic = ? WHERE id = ?");
        
        if($stmt->execute([$bio, $profile_pic, $user_id])) {
            $success = "Profile updated successfully.";
            $user['bio'] = $bio;
            $user['profile_pic'] = $profile_pic;
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Edit Profile</h3>
                <?= display_error($error) ?>
                <?= display_success($success) ?>
                
                <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <img src="assets/uploads/<?= htmlspecialchars($user['profile_pic']) ?>" class="profile-pic mb-2" alt="Current Profile Pic">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username (Cannot be changed)</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email (Cannot be changed)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Change Profile Picture</label>
                        <input type="file" name="profile_pic" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
