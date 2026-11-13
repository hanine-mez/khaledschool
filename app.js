// assets/js/app.js
document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('form-login');
  if (loginForm) loginForm.addEventListener('submit', handleLogin);
  
  const studentForm = document.getElementById('form-student');
  if (studentForm) studentForm.addEventListener('submit', handleStudentSave);
  
  const teacherForm = document.getElementById('form-teacher');
  if (teacherForm) teacherForm.addEventListener('submit', handleTeacherSave);
  
  const courseForm = document.getElementById('form-course');
  if (courseForm) courseForm.addEventListener('submit', handleCourseSave);
  
  const paymentForm = document.getElementById('form-payment');
  if (paymentForm) paymentForm.addEventListener('submit', handlePaymentSave);
  
  const videoForm = document.getElementById('form-video');
  if (videoForm) videoForm.addEventListener('submit', handleVideoSave);

  const attForm = document.getElementById('form-attendance');
  if (attForm) attForm.addEventListener('submit', handleAttendanceSave);
});

/* Login & Logout */
async function handleLogin(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  const errEl = document.getElementById('login-err');
  
  try {
    const res = await fetch('api/auth.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    const textResponse = await res.text();
    let data;
    try {
      data = JSON.parse(textResponse);
    } catch (parseErr) {
      if (errEl) errEl.textContent = 'خطأ في استجابة الخادم';
      return;
    }

    if (data.success) {
      const publicHome = document.getElementById('public-home');
      const adminDashboard = document.getElementById('admin-dashboard-view');
      
      if (publicHome) publicHome.classList.add('hidden');
      if (adminDashboard) adminDashboard.classList.remove('hidden');
      
      navigateTo('dashboard');
    } else {
      if (errEl) errEl.textContent = data.message || 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
  } catch (err) {
    if (errEl) errEl.textContent = 'تعذر الاتصال بالخادم';
    console.error(err);
  }
}

async function logout() {
  try {
    await fetch('api/auth.php?action=logout');
  } catch(e) {}
  
  const publicHome = document.getElementById('public-home');
  const adminDashboard = document.getElementById('admin-dashboard-view');
  const loginBox = document.getElementById('admin-login-box');

  if (adminDashboard) adminDashboard.classList.add('hidden');
  if (publicHome) publicHome.classList.remove('hidden');
  if (loginBox) loginBox.classList.add('hidden');
}

function navigateTo(section) {
  ['dashboard', 'students', 'teachers', 'courses', 'finance', 'videos', 'attendance'].forEach(s => {
    const el = document.getElementById(`sec-${s}`);
    const btn = document.getElementById(`btn-${s}`);
    if (el) el.classList.add('hidden');
    if (btn) btn.classList.remove('active');
  });

  const targetSec = document.getElementById(`sec-${section}`);
  const targetBtn = document.getElementById(`btn-${section}`);
  if (targetSec) targetSec.classList.remove('hidden');
  if (targetBtn) targetBtn.classList.add('active');

  if (section === 'dashboard') loadDashboard();
  if (section === 'students') loadStudents();
  if (section === 'teachers') loadTeachers();
  if (section === 'courses') loadCourses();
  if (section === 'finance') loadFinance();
  if (section === 'videos') loadVideos();
  if (section === 'attendance') loadAttendance();
}

/* Dashboard */
async function loadDashboard() {
  try {
    const res = await fetch('api/dashboard.php');
    const data = await res.json();
    if (data.success) {
      if (document.getElementById('stat-students')) document.getElementById('stat-students').textContent = data.stats.total_students;
      if (document.getElementById('stat-teachers')) document.getElementById('stat-teachers').textContent = data.stats.total_teachers;
      if (document.getElementById('stat-courses')) document.getElementById('stat-courses').textContent = data.stats.total_courses;
      if (document.getElementById('stat-revenue')) document.getElementById('stat-revenue').textContent = `${data.stats.total_revenue} دج`;
    }
  } catch (e) { console.error(e); }
}

/* Students */
async function loadStudents(search = '') {
  try {
    const res = await fetch(`api/students.php?search=${encodeURIComponent(search)}`);
    const data = await res.json();
    const tbody = document.getElementById('tbody-students');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (data.success && data.data.length > 0) {
      data.data.forEach(st => {
        tbody.innerHTML += `
          <tr>
            <td>${st.id}</td>
            <td>${st.first_name} ${st.last_name}</td>
            <td>${st.phone || '—'}</td>
            <td>${st.specialty}</td>
            <td><button class="del" onclick="deleteStudent(${st.id})">حذف</button></td>
          </tr>`;
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="5">لا يوجد طلاب.</td></tr>';
    }
  } catch (e) { console.error(e); }
}

async function handleStudentSave(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  await fetch('api/students.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  e.target.reset();
  loadStudents();
  loadDashboard();
}

async function deleteStudent(id) {
  if (!confirm('هل أنت متأكد من الحذف؟')) return;
  await fetch(`api/students.php?id=${id}`, { method: 'DELETE' });
  loadStudents();
  loadDashboard();
}

/* Teachers */
async function loadTeachers() {
  try {
    const res = await fetch('api/teachers.php');
    const data = await res.json();
    const tbody = document.getElementById('tbody-teachers');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (data.success) {
      data.data.forEach(t => {
        tbody.innerHTML += `
          <tr>
            <td>${t.id}</td>
            <td>${t.name}</td>
            <td>${t.phone || '—'}</td>
            <td>${t.percentage}%</td>
            <td><button class="del" onclick="deleteTeacher(${t.id})">حذف</button></td>
          </tr>`;
      });
    }
  } catch (e) { console.error(e); }
}

async function handleTeacherSave(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  await fetch('api/teachers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  e.target.reset();
  loadTeachers();
  loadDashboard();
}

async function deleteTeacher(id) {
  if (!confirm('تأكيد الحذف؟')) return;
  await fetch(`api/teachers.php?id=${id}`, { method: 'DELETE' });
  loadTeachers();
  loadDashboard();
}

/* Courses */
async function loadCourses() {
  try {
    const res = await fetch('api/courses.php');
    const data = await res.json();
    const tbody = document.getElementById('tbody-courses');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (data.success) {
      data.data.forEach(c => {
        tbody.innerHTML += `
          <tr>
            <td>${c.code}</td>
            <td>${c.title}</td>
            <td>${c.level}</td>
            <td>${c.teacher_name || 'غير محدد'}</td>
            <td>${c.rate} دج</td>
            <td><button class="del" onclick="deleteCourse(${c.id})">حذف</button></td>
          </tr>`;
      });
    }
  } catch (e) { console.error(e); }
}

async function handleCourseSave(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  await fetch('api/courses.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  e.target.reset();
  loadCourses();
  loadDashboard();
}

async function deleteCourse(id) {
  if (!confirm('تأكيد الحذف؟')) return;
  await fetch(`api/courses.php?id=${id}`, { method: 'DELETE' });
  loadCourses();
  loadDashboard();
}

/* Finance */
async function loadFinance() {
  try {
    const resStudents = await fetch('api/students.php');
    const dataStudents = await resStudents.json();
    const selectStudent = document.getElementById('select-student');
    if (selectStudent && dataStudents.success) {
      selectStudent.innerHTML = '<option value="">اختر الطالب...</option>';
      dataStudents.data.forEach(s => {
        selectStudent.innerHTML += `<option value="${s.id}">${s.first_name} ${s.last_name}</option>`;
      });
    }

    const resCourses = await fetch('api/courses.php');
    const dataCourses = await resCourses.json();
    const selectCourse = document.getElementById('select-course');
    if (selectCourse && dataCourses.success) {
      selectCourse.innerHTML = '<option value="">اختر المقياس...</option>';
      dataCourses.data.forEach(c => {
        selectCourse.innerHTML += `<option value="${c.id}">${c.title} (${c.level})</option>`;
      });
    }

    const resFinance = await fetch('api/finance.php');
    const dataFinance = await resFinance.json();
    
    if (dataFinance.success) {
      if(document.getElementById('fin-total')) document.getElementById('fin-total').textContent = `${dataFinance.summary.total_revenue} دج`;
      if(document.getElementById('fin-teacher')) document.getElementById('fin-teacher').textContent = `${dataFinance.summary.total_teacher_share} دج`;
      if(document.getElementById('fin-school')) document.getElementById('fin-school').textContent = `${dataFinance.summary.total_school_share} دج`;

      const tbody = document.getElementById('tbody-finance');
      if (tbody) {
        tbody.innerHTML = '';
        dataFinance.data.forEach(p => {
          tbody.innerHTML += `
            <tr>
              <td>${p.payment_date}</td>
              <td>${p.first_name} ${p.last_name}</td>
              <td>${p.course_title} (${p.level})</td>
              <td>${p.amount_paid} دج</td>
              <td>${p.teacher_name || 'غير محدد'} (${p.teacher_percentage}%)</td>
              <td><b>${p.teacher_share} دج</b></td>
              <td><b style="color:var(--success)">${p.school_share} دج</b></td>
            </tr>`;
        });
      }
    }
  } catch (e) { console.error(e); }
}

async function handlePaymentSave(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  const res = await fetch('api/finance.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  if(data.success) {
    e.target.reset();
    loadFinance();
    loadDashboard();
  } else {
    alert(data.message);
  }
}

/* Videos */
async function loadVideos() {
  try {
    const resCourses = await fetch('api/courses.php');
    const dataCourses = await resCourses.json();
    const selectCourse = document.getElementById('video-course');
    if (selectCourse && dataCourses.success) {
      selectCourse.innerHTML = '<option value="">اختر المقياس...</option>';
      dataCourses.data.forEach(c => {
        selectCourse.innerHTML += `<option value="${c.id}">${c.title} (${c.level})</option>`;
      });
    }

    const resVideos = await fetch('api/videos.php');
    const dataVideos = await resVideos.json();
    const container = document.getElementById('video-list');
    if (!container) return;
    container.innerHTML = '';

    if(dataVideos.success && dataVideos.data.length > 0) {
      dataVideos.data.forEach(v => {
        container.innerHTML += `
          <div class="video-card">
            <h4>${v.title} — المقياس: ${v.course_title} (${v.level})</h4>
            <video controls controlsList="nodownload" oncontextmenu="return false;" style="width:100%; max-height:400px; border-radius:8px;">
              <source src="${v.video_url}" type="video/mp4">
              متصفحك لا يدعم تشغيل الفيديو.
            </video>
          </div>`;
      });
    } else {
      container.innerHTML = '<p>لا توجد فيديوهات مسجلة حالياً.</p>';
    }
  } catch (e) { console.error(e); }
}

async function handleVideoSave(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  await fetch('api/videos.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  e.target.reset();
  loadVideos();
}

/* Attendance */
async function loadAttendance() {
  try {
    const dateInput = document.querySelector('#form-attendance input[name="attendance_date"]');
    if (dateInput && !dateInput.value) {
      dateInput.value = new Date().toISOString().split('T')[0];
    }

    const resCourses = await fetch('api/courses.php');
    const dataCourses = await resCourses.json();
    const selectCourse = document.getElementById('att-course');
    if (selectCourse && dataCourses.success) {
      selectCourse.innerHTML = '<option value="">اختر المقياس...</option>';
      dataCourses.data.forEach(c => {
        selectCourse.innerHTML += `<option value="${c.id}">${c.title} (${c.level})</option>`;
      });
    }

    const resStudents = await fetch('api/students.php');
    const dataStudents = await resStudents.json();
    const selectStudent = document.getElementById('att-student');
    if (selectStudent && dataStudents.success) {
      selectStudent.innerHTML = '<option value="">اختر الطالب المسجل...</option>';
      dataStudents.data.forEach(s => {
        selectStudent.innerHTML += `<option value="${s.id}">${s.first_name} ${s.last_name} (${s.specialty})</option>`;
      });
    }

    const resAtt = await fetch('api/attendance.php');
    const dataAtt = await resAtt.json();
    const tbody = document.getElementById('tbody-attendance');
    if (tbody && dataAtt.success) {
      tbody.innerHTML = '';
      if (dataAtt.data.length > 0) {
        dataAtt.data.forEach(a => {
          const statusColor = a.status === 'غائب' ? 'var(--danger)' : 'var(--success)';
          tbody.innerHTML += `
            <tr>
              <td><b>${a.attendance_date}</b></td>
              <td><b>${a.student_name || '—'}</b></td>
              <td>${a.course_title || '—'} (${a.level || ''})</td>
              <td>${a.teacher_name}</td>
              <td>${a.session_time}</td>
              <td><span style="color:${statusColor}; font-weight:bold;">${a.status}</span></td>
            </tr>`;
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="6">لا توجد سجلات حضور.</td></tr>';
      }
    }
  } catch (e) { 
    console.error("Error loading attendance:", e); 
  }
}

async function handleAttendanceSave(e) {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  try {
    const res = await fetch('api/attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      const savedDate = payload.attendance_date;
      e.target.reset();
      const dateInput = document.querySelector('#form-attendance input[name="attendance_date"]');
      if (dateInput && savedDate) dateInput.value = savedDate;
      loadAttendance();
    } else {
      alert(data.message);
    }
  } catch (err) {
    console.error(err);
  }
}