<?php
// api/finance.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// تسجيل دفعة جديدة
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $student_id = (int)($data['student_id'] ?? 0);
    $course_id = (int)($data['course_id'] ?? 0);
    $amount_paid = (float)($data['amount_paid'] ?? 0);

    if (!$student_id || !$course_id || $amount_paid <= 0) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول مطلوبة والمبلغ يجب أن يكون أكبر من 0']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO payments (student_id, course_id, amount_paid) VALUES (:sid, :cid, :amt)");
    $stmt->execute([':sid' => $student_id, ':cid' => $course_id, ':amt' => $amount_paid]);

    echo json_encode(['success' => true]);
    exit;
}

// جلب السجلات المالية والحساب الأوتوماتيكي
if ($method === 'GET') {
    $sql = "
        SELECT 
            p.id, p.amount_paid, p.payment_date,
            s.first_name, s.last_name,
            c.title AS course_title, c.level,
            t.name AS teacher_name,
            COALESCE(t.percentage, 50.0) AS teacher_percentage
        FROM payments p
        JOIN students s ON p.student_id = s.id
        JOIN courses c ON p.course_id = c.id
        LEFT JOIN teachers t ON c.teacher_id = t.id
        ORDER BY p.id DESC
    ";
    
    $payments = $pdo->query($sql)->fetchAll();

    $totalRevenue = 0;
    $totalTeacherShare = 0;
    $totalSchoolShare = 0;

    foreach ($payments as &$p) {
        $pct = (float)$p['teacher_percentage'];
        $p['teacher_share'] = ($p['amount_paid'] * $pct) / 100;
        $p['school_share'] = $p['amount_paid'] - $p['teacher_share'];

        $totalRevenue += $p['amount_paid'];
        $totalTeacherShare += $p['teacher_share'];
        $totalSchoolShare += $p['school_share'];
    }

    echo json_encode([
        'success' => true,
        'data' => $payments,
        'summary' => [
            'total_revenue' => $totalRevenue,
            'total_teacher_share' => $totalTeacherShare,
            'total_school_share' => $totalSchoolShare
        ]
    ]);
    exit;
}