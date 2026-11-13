<?php
// api/courses.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT c.*, t.name AS teacher_name 
        FROM courses c 
        LEFT JOIN teachers t ON c.teacher_id = t.id 
        ORDER BY c.level ASC, c.title ASC
    ");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $code = trim($data['code'] ?? '');
    $title = trim($data['title'] ?? '');
    $level = trim($data['level'] ?? '');
    $teacher_id = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null;
    $rate = (float)($data['rate'] ?? 500.0);

    if (empty($code) || empty($title) || empty($level)) {
        echo json_encode(['success' => false, 'message' => 'Code, Title, and Level are required.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO courses (code, title, level, teacher_id, rate) VALUES (:cd, :tt, :lvl, :tid, :rt)");
    $stmt->execute([':cd' => $code, ':tt' => $title, ':lvl' => $level, ':tid' => $teacher_id, ':rt' => $rate]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}