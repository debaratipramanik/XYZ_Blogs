<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get all posts with user info and like count
$query = "
    SELECT p.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($query);
$user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Newsfeed</h2>
        
        <?php if(count($posts) > 0): ?>
            <?php foreach($posts as $post): ?>
                <div class="card post-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/uploads/<?= htmlspecialchars($post['profile_pic']) ?>" alt="Profile" class="small-profile-pic">
                            <div>
                                <h6 class="mb-0"><a href="profile.php?id=<?= $post['user_id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['username']) ?></a></h6>
                                <small class="text-muted"><?= date('F j, Y, g:i a', strtotime($post['created_at'])) ?></small>
                            </div>
                        </div>
                        <h4 class="card-title">
                            <a href="post.php?id=<?= $post['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['title']) ?></a>
                        </h4>
                        <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?><?= strlen($post['content']) > 200 ? '...' : '' ?></p>
                        
                        <?php if($post['image']): ?>
                            <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>" class="img-fluid rounded mb-3" alt="Post Image">
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <a href="#" class="like-btn text-decoration-none" data-post-id="<?= $post['id'] ?>">
                                    <i class="<?= $post['user_liked'] ? 'fas' : 'far' ?> fa-heart fs-5"></i>
                                    <span class="like-count"><?= $post['like_count'] ?></span>
                                </a>
                                <a href="post.php?id=<?= $post['id'] ?>" class="text-decoration-none ms-3 text-muted">
                                    <i class="far fa-comment fs-5"></i> Read More
                                </a>
                            </div>
                            <?php if(is_logged_in() && $_SESSION['user_id'] == $post['user_id']): ?>
                                <div>
                                    <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No posts found. Be the first to <a href="create_post.php">create a post</a>!</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
