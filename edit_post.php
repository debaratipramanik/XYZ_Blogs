<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if(!is_logged_in()) {
    redirect('login.php');
}

$error = '';
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post) {
    redirect('index.php');
}

// Check ownership
if($post['user_id'] != $_SESSION['user_id']) {
    redirect('index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $image_name = $post['image']; // Default to old image

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
                    // Delete old image if it exists
                    if(!empty($post['image']) && file_exists($upload_dir . $post['image'])) {
                        unlink($upload_dir . $post['image']);
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF.";
            }
        }

        if(empty($error)) {
            $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
            
            if($stmt->execute([$title, $content, $image_name, $post_id])) {
                redirect("post.php?id=$post_id");
            } else {
                $error = "Failed to update post.";
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
                <h3 class="card-title mb-4">Edit Post</h3>
                <?= display_error($error) ?>
                
                <form action="edit_post.php?id=<?= $post_id ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>
                    <?php if($post['image']): ?>
                        <div class="mb-3">
                            <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>" class="img-thumbnail" width="150" alt="Current Image">
                        </div>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label">Change Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Post</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
