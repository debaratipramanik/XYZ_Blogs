<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$query_string = isset($_GET['q']) ? sanitize($_GET['q']) : '';

$users = [];
$posts = [];

if(!empty($query_string)) {
    // Search Users (Postgres uses ILIKE for case-insensitive search, but LIKE works if exact. Let's use ILIKE for better UX)
    $search_term = "%$query_string%";
    $stmt = $conn->prepare("SELECT id, username, profile_pic, bio FROM users WHERE username ILIKE ?");
    $stmt->execute([$search_term]);
    $users = $stmt->fetchAll();

    // Search Posts
    $post_query = "
        SELECT p.*, u.username, u.profile_pic,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.title ILIKE ? OR p.content ILIKE ?
        ORDER BY p.created_at DESC
    ";
    $stmt = $conn->prepare($post_query);
    $user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
    $stmt->execute([$user_id, $search_term, $search_term]);
    $posts = $stmt->fetchAll();
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Search Results for "<?= htmlspecialchars($query_string) ?>"</h2>
        
        <?php if(empty($query_string)): ?>
            <div class="alert alert-warning">Please enter a search term.</div>
        <?php else: ?>
            
            <!-- Users Results -->
            <h4 class="mb-3">Users</h4>
            <?php if(count($users) > 0): ?>
                <div class="row mb-4">
                    <?php foreach($users as $user): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex align-items-center">
                                    <img src="assets/uploads/<?= htmlspecialchars($user['profile_pic']) ?>" class="profile-pic me-3" style="width: 60px; height: 60px; border-width: 2px;" alt="Profile">
                                    <div>
                                        <h5 class="mb-0"><a href="profile.php?id=<?= $user['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($user['username']) ?></a></h5>
                                        <small class="text-muted"><?= htmlspecialchars(substr($user['bio'] ?? '', 0, 50)) ?>...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-4">No users found.</p>
            <?php endif; ?>

            <hr>

            <!-- Posts Results -->
            <h4 class="mb-3 mt-4">Posts</h4>
            <?php if(count($posts) > 0): ?>
                <?php foreach($posts as $post): ?>
                    <div class="card post-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/uploads/<?= htmlspecialchars($post['profile_pic']) ?>" alt="Profile" class="small-profile-pic">
                                <div>
                                    <h6 class="mb-0"><a href="profile.php?id=<?= $post['user_id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['username']) ?></a></h6>
                                    <small class="text-muted"><?= date('F j, Y', strtotime($post['created_at'])) ?></small>
                                </div>
                            </div>
                            <h5 class="card-title">
                                <a href="post.php?id=<?= $post['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['title']) ?></a>
                            </h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
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
                <p class="text-muted">No posts found.</p>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
