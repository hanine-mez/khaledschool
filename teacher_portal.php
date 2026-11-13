<?php
// teacher_portal.php
session_start();
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>بوابة الأستاذ — Khaled School</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="top">
    <div class="brand">
      <img src="assets/images/logo.png" alt="Logo" class="logo">
      <h1>بوابة الأستاذ — Khaled School</h1>
    </div>
    <div>
      <select id="lang-selector" onchange="changeLanguage(this.value)" style="padding:5px;">
        <option value="ar">العربية</option>
        <option value="fr">Français</option>
      </select>
    </div>
  </header>

  <main style="margin-top: 30px;">
    <h2>مرحباً بك أستاذنا الكريم</h2>
    <div class="panel">
      <h3>المقاييس والنسب الخاصة بك</h3>
      <p>يمكنك من خلال هذه الواجهة متابعة المقاييس المسندة إليك والنسب المالية الخاصة بحصصك.</p>
    </div>
  </main>

  <script src="assets/js/lang.js"></script>
</body>
</html>