<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if(!is_logged_in()) {
    redirect('login.php');
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $image_name = null;

    if(empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        // Handle Image Upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if(in_array(strtolower($filetype), $allowed)) {
                $new_filename = uniqid() . '.' . $filetype;
                $upload_dir = 'assets/uploads/';
                
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_name = $new_filename;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF.";
            }
        }

        if(empty($error)) {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)");
            
            if($stmt->execute([$user_id, $title, $content, $image_name])) {
                redirect('index.php');
            } else {
                $error = "Failed to create post.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title mb-4">Create New Post</h3>
                <?= display_error($error) ?>
                
                <form action="create_post.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Publish Post</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
