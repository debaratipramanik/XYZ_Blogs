<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$profile_user = $stmt->fetch();

if(!$profile_user) {
    redirect('index.php');
}

// Fetch posts by this user
$query = "
    SELECT p.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($query);
$current_user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
$stmt->execute([$current_user_id, $profile_id]);
$posts = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        
        <div class="profile-header shadow">
            <img src="assets/uploads/<?= htmlspecialchars($profile_user['profile_pic']) ?>" alt="Profile Picture" class="profile-pic mb-3">
            <h2><?= htmlspecialchars($profile_user['username']) ?></h2>
            <p class="mb-2"><i class="far fa-envelope"></i> <?= htmlspecialchars($profile_user['email']) ?></p>
            <p class="fst-italic"><?= nl2br(htmlspecialchars($profile_user['bio'] ?? 'No bio available.')) ?></p>
            
            <?php if(is_logged_in() && $_SESSION['user_id'] == $profile_user['id']): ?>
                <a href="edit_profile.php" class="btn btn-light mt-3">Edit Profile</a>
            <?php endif; ?>
        </div>

        <h3 class="mb-4 text-center">Posts by <?= htmlspecialchars($profile_user['username']) ?></h3>
        
        <div class="row">
            <?php if(count($posts) > 0): ?>
                <?php foreach($posts as $post): ?>
                    <div class="col-md-6">
                        <div class="card post-card h-100">
                            <?php if($post['image']): ?>
                                <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>" class="card-img-top" alt="Post Image" style="height: 200px;">
                            <?php else: ?>
                                <div class="bg-light w-100 d-flex align-items-center justify-content-center text-muted" style="height: 200px;">
                                    <i class="far fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><a href="post.php?id=<?= $post['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['title']) ?></a></h5>
                                <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 100))) ?><?= strlen($post['content']) > 100 ? '...' : '' ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted"><?= date('M j, Y', strtotime($post['created_at'])) ?></small>
                                    <a href="#" class="like-btn text-decoration-none" data-post-id="<?= $post['id'] ?>">
                                        <i class="<?= $post['user_liked'] ? 'fas' : 'far' ?> fa-heart fs-5"></i>
                                        <span class="like-count"><?= $post['like_count'] ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-5">
                    <p>No posts to display.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
