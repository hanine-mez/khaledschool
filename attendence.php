<?php
// api/attendance.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT a.*, 
                   COALESCE(s.first_name || ' ' || s.last_name, 'طالب محذوف') AS student_name,
                   c.title AS course_title, 
                   c.level 
            FROM attendance_sheets a
            LEFT JOIN students s ON a.student_id = s.id
            LEFT JOIN courses c ON a.course_id = c.id
            ORDER BY a.attendance_date DESC, a.id DESC
        ");
        $records = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $records]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // معالجة التاريخ لضمان توافقه مع قاعدة البيانات
    $raw_date = $data['attendance_date'] ?? '';
    if (!empty($raw_date)) {
        // إذا كان التاريخ بصيغة مجمعة أو أمريكية، نقوم بتحويله إلى YYYY-MM-DD
        $timestamp = strtotime($raw_date);
        $attendance_date = $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
    } else {
        $attendance_date = date('Y-m-d');
    }

    $course_id    = $data['course_id'] ?? '';
    $student_id   = $data['student_id'] ?? '';
    $teacher_name = trim($data['teacher_name'] ?? '');
    $session_time = trim($data['session_time'] ?? '');
    $status       = $data['status'] ?? 'حاضر';

    if (empty($course_id) || empty($student_id) || empty($teacher_name)) {
        echo json_encode(['success' => false, 'message' => 'الرجاء اختيار المقياس واسم الطالب وأستاذ المادة']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO attendance_sheets (attendance_date, course_id, student_id, teacher_name, session_time, status) 
            VALUES (:attendance_date, :course_id, :student_id, :teacher_name, :session_time, :status)
        ");
        $stmt->execute([
            ':attendance_date' => $attendance_date,
            ':course_id'       => $course_id,
            ':student_id'      => $student_id,
            ':teacher_name'    => $teacher_name,
            ':session_time'    => $session_time,
            ':status'          => $status
        ]);

        echo json_encode(['success' => true, 'message' => 'تم تسجيل الحضور بنجاح']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
    }
    exit;
}