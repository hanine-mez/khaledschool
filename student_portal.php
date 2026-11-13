<?php
// student_portal.php
$mode = $_GET['mode'] ?? 'online';
$isOnline = ($mode === 'online');
$title = $isOnline ? 'التعليم الإلكتروني عن بعد (Online)' : 'تعليم الحضوري في المقر';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?> — Khaled School</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 20px;">

  <header class="top" style="max-width: 1000px; margin: 0 auto 30px auto; display: flex; justify-content: space-between; align-items: center; background: var(--card); padding: 15px 25px; border-radius: 10px; border: 1px solid var(--rule);">
    <div class="brand" style="display: flex; align-items: center; gap: 10px;">
      <img src="assets/images/logo.png" alt="Logo" style="height: 40px;">
      <h2 style="color: var(--ink); margin: 0; font-size: 20px;"><?php echo $title; ?></h2>
    </div>
    <a href="index.html" class="btn" style="background: var(--slate); text-decoration: none; padding: 6px 15px; font-size: 14px;">العودة للرئيسية</a>
  </header>

  <main style="max-width: 1000px; margin: 0 auto; display: grid; gap: 20px;">

    <?php if ($isOnline): ?>
      <!-- قسم خاصة بتنبيه الدفع للتعليم عن بعد -->
      <div class="panel" style="background: linear-gradient(135deg, #80043c22, var(--card)); border: 1px solid var(--brass);">
        <h3 style="color: var(--brass);">💳 تفعيل حساب التعليم عن بعد والدفع</h3>
        <p style="margin-top: 8px; color: var(--slate);">لمشاهدة الفيديوهات والدروس المسجلة، يجب أن يكون اشتراكك مفْعلاً من طرف الإدارة بعد تأكيد عملية الدفع.</p>
        <div style="margin-top: 12px; display: flex; gap: 10px;">
          <input type="text" id="student-phone-check" placeholder="أدخل رقم هاتفك للتحقق من التفعيل..." style="padding: 8px; width: 250px; border: 1px solid var(--rule); border-radius: 6px;">
          <button class="btn" onclick="checkStudentAccess()">التحقق من الاشتراك</button>
        </div>
        <p id="access-msg" style="margin-top: 8px; font-weight: bold;"></p>
      </div>
    <?php endif; ?>

    <!-- المقاييس والأساتذة (تظهر للجميع) -->
    <div class="panel">
      <h3>📚 المقاييس والأساتذة المشرفين</h3>
      <div id="student-courses-list" style="margin-top: 15px; display: grid; gap: 10px;"></div>
    </div>

    <?php if ($isOnline): ?>
      <!-- الفيديوهات (تظهر فقط في قسم الأونلاين وبشرط التفعيل) -->
      <div class="panel" id="videos-section" style="opacity: 0.6; pointer-events: none;">
        <h3>🎥 الفيديوهات التعليمية المسجلة (متحكم بها بالاشتراك)</h3>
        <p style="color: var(--danger); font-size: 13px; margin-bottom: 10px;">* يرجى إدخال رقم الهاتف والتحقق من التفعيل أعلاه لفتح الفيديوهات.</p>
        <div id="student-videos-list" style="margin-top: 15px; display: grid; gap: 20px;"></div>
      </div>
    <?php else: ?>
      <!-- قسم خاص بالحضوري (الجداول والتوقيت في المقر) -->
      <div class="panel">
        <h3>📍 معلومات الحضور في المقر</h3>
        <p style="color: var(--slate);">يمكنك الاطلاع على أوقات الحصص والقاعات المخصصة للتعليم الحضوري في مدرسة خالد.</p>
        <div style="background: var(--bg); padding: 15px; border-radius: 8px; border: 1px solid var(--rule); margin-top: 10px;">
          <p><b>الأيام المتاحة:</b> الأحد إلى الخميس</p>
          <p><b>التوقيت الإداري:</b> 08:00 صباحاً إلى 18:00 مساءً</p>
        </div>
      </div>
    <?php endif; ?>

  </main>

  <script>
    const isOnlineMode = <?php echo $isOnline ? 'true' : 'false'; ?>;

    async function loadStudentPortalData() {
      try {
        // 1. جلب المقاييس
        const resCourses = await fetch('api/courses.php');
        const dataCourses = await resCourses.json();
        const coursesDiv = document.getElementById('student-courses-list');
        if (dataCourses.success && dataCourses.data.length > 0) {
          coursesDiv.innerHTML = dataCourses.data.map(c => `
            <div style="background: var(--bg); padding: 12px; border-radius: 8px; border: 1px solid var(--rule); display: flex; justify-content: space-between;">
              <span><b>${c.title}</b> (${c.level})</span>
              <span style="color: var(--brass);">الأستاذ: ${c.teacher_name || 'غير محدد'} | السعر: ${c.rate} دج</span>
            </div>
          `).join('');
        } else {
          coursesDiv.innerHTML = '<p>لا توجد مقاييس متاحة حالياً.</p>';
        }

        // 2. إذا كنا في وضع الأونلاين، نجلب الفيديوهات لكن نبقيها مقفلة حتى يتحقق من اشتراكه
        if (isOnlineMode) {
          const resVideos = await fetch('api/videos.php');
          const dataVideos = await resVideos.json();
          const videosDiv = document.getElementById('student-videos-list');
          if (dataVideos.success && dataVideos.data.length > 0) {
            videosDiv.innerHTML = dataVideos.data.map(v => `
              <div class="video-card" style="background: var(--bg); padding: 15px; border-radius: 8px; border: 1px solid var(--rule);">
                <h4 style="margin-bottom: 10px; color: var(--ink);">${v.title} — المقياس: ${v.course_title}</h4>
                <video controls controlsList="nodownload" oncontextmenu="return false;" style="width:100%; max-height:350px; border-radius:6px;">
                  <source src="${v.video_url}" type="video/mp4">
                  متصفحك لا يدعم تشغيل الفيديو.
                </video>
              </div>
            `).join('');
          } else {
            videosDiv.innerHTML = '<p>لا توجد فيديوهات مرفوعة حالياً.</p>';
          }
        }
      } catch (e) {
        console.error(e);
      }
    }

    // دالة محاكاة التحقق من دفع الطالب وتفعيل حسابه عبر رقم الهاتف
    function checkStudentAccess() {
      const phone = document.getElementById('student-phone-check').value.trim();
      const msg = document.getElementById('access-msg');
      const videoSec = document.getElementById('videos-section');

      if (!phone) {
        msg.style.color = 'var(--danger)';
        msg.textContent = 'الرجاء إدخال رقم الهاتف أولاً.';
        return;
      }

      // هنا كمثال محلي: لو رقم الهاتف مسجل وتم دفع رسومه، نفتح الفيديوهات
      // (يمكننا لاحقاً ربطه بـ API خاص بالاشتراكات)
      if (phone.length >= 9) {
        msg.style.color = 'var(--success)';
        msg.textContent = '✅ تم التحقق من الاشتراك بنجاح! تم فتح الفيديوهات.';
        videoSec.style.opacity = '1';
        videoSec.style.pointerEvents = 'auto';
      } else {
        msg.style.color = 'var(--danger)';
        msg.textContent = '❌ رقم الهاتف غير مسجل في قائمة المدفوعات. يرجى التقرب للإدارة للدفع والتفعيل.';
      }
    }

    document.addEventListener('DOMContentLoaded', loadStudentPortalData);
  </script>
</body>
</html>