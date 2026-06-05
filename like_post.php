<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if(!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if like exists
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    
    if($stmt->rowCount() > 0) {
        // Unlike
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        $action = 'unliked';
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $post_id]);
        $action = 'liked';
    }

    // Get new count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $count_result = $stmt->fetch();

    echo json_encode([
        'status' => 'success',
        'action' => $action,
        'likes' => $count_result['count']
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
?>
