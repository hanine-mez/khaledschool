<?php
// api/dashboard.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

// التأكد من صلاحيات الأدمن
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. حساب إجمالي الطلاب المسجلين
    $stmtStudents = $pdo->query("SELECT COUNT(*) AS total FROM students");
    $totalStudents = $stmtStudents->fetch()['total'] ?? 0;

    // 2. حساب عدد الأساتذة
    $stmtTeachers = $pdo->query("SELECT COUNT(*) AS total FROM teachers");
    $totalTeachers = $stmtTeachers->fetch()['total'] ?? 0;

    // 3. حساب عدد المقاييس والدروس
    $stmtCourses = $pdo->query("SELECT COUNT(*) AS total FROM courses");
    $totalCourses = $stmtCourses->fetch()['total'] ?? 0;

    // 4. حساب المداخيل الإجمالية من جدول المدفوعات (payments)
    $stmtRevenue = $pdo->query("SELECT SUM(amount_paid) AS total FROM payments");
    $totalRevenue = $stmtRevenue->fetch()['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_courses' => $totalCourses,
            'total_revenue' => $totalRevenue
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'stats' => [
            'total_students' => 0,
            'total_teachers' => 0,
            'total_courses' => 0,
            'total_revenue' => 0
        ],
        'message' => $e->getMessage()
    ]);
}