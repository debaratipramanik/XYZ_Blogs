<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "
    SELECT p.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
";

$stmt = $conn->prepare($query);
$user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
$stmt->execute([$user_id, $post_id]);
$post = $stmt->fetch();

if(!$post) {
    redirect('index.php');
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card post-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="assets/uploads/<?= htmlspecialchars($post['profile_pic']) ?>" alt="Profile" class="small-profile-pic">
                    <div>
                        <h6 class="mb-0"><a href="profile.php?id=<?= $post['user_id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['username']) ?></a></h6>
                        <small class="text-muted"><?= date('F j, Y, g:i a', strtotime($post['created_at'])) ?></small>
                    </div>
                </div>
                <h2 class="card-title mb-4"><?= htmlspecialchars($post['title']) ?></h2>
                
                <?php if($post['image']): ?>
                    <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>" class="img-fluid rounded mb-4 w-100" style="max-height: 500px; object-fit: contain; background: #eee;" alt="Post Image">
                <?php endif; ?>

                <div class="card-text fs-5" style="line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
                
                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="#" class="like-btn text-decoration-none" data-post-id="<?= $post['id'] ?>">
                            <i class="<?= $post['user_liked'] ? 'fas' : 'far' ?> fa-heart fs-4"></i>
                            <span class="like-count fs-5"><?= $post['like_count'] ?></span>
                        </a>
                    </div>
                    <?php if(is_logged_in() && $_SESSION['user_id'] == $post['user_id']): ?>
                        <div>
                            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-outline-secondary">Edit</a>
                            <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
