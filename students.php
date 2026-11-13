<?php
// api/students.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch or Search Students
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $stmt = $pdo->prepare("
            SELECT * FROM students 
            WHERE first_name LIKE :s OR last_name LIKE :s OR phone LIKE :s OR specialty LIKE :s
            ORDER BY last_name ASC, first_name ASC
        ");
        $stmt->execute([':s' => "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY last_name ASC, first_name ASC");
    }
    $students = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $students]);
    exit;
}

// POST: Add New Student
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $specialty = trim($data['specialty'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($specialty)) {
        echo json_encode(['success' => false, 'message' => 'First name, last name, and specialty are required.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, phone, specialty) VALUES (:fn, :ln, :ph, :sp)");
    $stmt->execute([':fn' => $firstName, ':ln' => $lastName, ':ph' => $phone, ':sp' => $specialty]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// PUT: Edit Student
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $specialty = trim($data['specialty'] ?? '');

    if (!$id || empty($firstName) || empty($lastName) || empty($specialty)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE students SET first_name = :fn, last_name = :ln, phone = :ph, specialty = :sp WHERE id = :id");
    $stmt->execute([':fn' => $firstName, ':ln' => $lastName, ':ph' => $phone, ':sp' => $specialty, ':id' => $id]);

    echo json_encode(['success' => true]);
    exit;
}

// DELETE: Remove Student
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}