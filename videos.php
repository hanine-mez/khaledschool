<?php
// api/videos.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $course_id = (int)($_GET['course_id'] ?? 0);
    if ($course_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM video_lessons WHERE course_id = :cid ORDER BY id DESC");
        $stmt->execute([':cid' => $course_id]);
    } else {
        $stmt = $pdo->query("SELECT v.*, c.title AS course_title, c.level FROM video_lessons v JOIN courses c ON v.course_id = c.id ORDER BY v.id DESC");
    }
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $course_id = (int)($data['course_id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $video_url = trim($data['video_url'] ?? '');

    if (!$course_id || empty($title) || empty($video_url)) {
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع البيانات']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO video_lessons (course_id, title, video_url) VALUES (:cid, :tt, :url)");
    $stmt->execute([':cid' => $course_id, ':tt' => $title, ':url' => $video_url]);
    echo json_encode(['success' => true]);
    exit;
}