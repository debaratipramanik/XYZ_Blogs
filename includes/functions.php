<?php
session_start();

function sanitize($input) {
    // PDO prepared statements handle SQL injection, so we only need XSS protection
    return htmlspecialchars(strip_tags(trim($input)));
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($location) {
    header("Location: " . $location);
    exit();
}

function get_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function display_error($error) {
    if (!empty($error)) {
        return "<div class='alert alert-danger'>$error</div>";
    }
    return '';
}

function display_success($msg) {
    if (!empty($msg)) {
        return "<div class='alert alert-success'>$msg</div>";
    }
    return '';
}
?>
