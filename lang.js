// assets/js/lang.js
const translations = {
  ar: {
    school_name: "مدرسة خالد التعليمية",
    btn_dashboard: "الرئيسية",
    btn_finance: "تسيير المداخيل والمالية",
    btn_students: "الطلاب",
    btn_teachers: "الأساتذة",
    btn_courses: "المقاييس",
    btn_videos: "الفيديوهات والدروس",
    btn_attendance: "ورقة الحضور",
    btn_logout: "خروج",
    login_title: "تسجيل الدخول — الإدارة",
    lbl_user: "اسم المستخدم",
    lbl_pass: "كلمة المرور",
    btn_login: "دخول",
    dash_title: "لوحة التحكم الإحصائية",
    stat_students: "إجمالي الطلاب المسجلين",
    stat_teachers: "عدد الأساتذة",
    stat_courses: "المقاييس والدروس",
    fin_title: "تسيير المداخيل والمستحقات المالية",
    stu_title: "إدارة الطلاب",
    tea_title: "إدارة الأساتذة والنسب",
    cou_title: "إدارة المقاييس والدروس",
    vid_title: "منصة الفيديوهات المحمية (مشاهدة فقط)",
    att_title: "ورقة الحضور اليومية"
  },
  fr: {
    school_name: "Khaled School",
    btn_dashboard: "Tableau de bord",
    btn_finance: "Finances & Revenus",
    btn_students: "Étudiants",
    btn_teachers: "Professeurs",
    btn_courses: "Cours / Modules",
    btn_videos: "Vidéos & Leçons",
    btn_attendance: "Feuille de présence",
    btn_logout: "Déconnexion",
    login_title: "Connexion — Administration",
    lbl_user: "Nom d'utilisateur",
    lbl_pass: "Mot de passe",
    btn_login: "Se connecter",
    dash_title: "Tableau de bord statistique",
    stat_students: "Total des étudiants",
    stat_teachers: "Nombre de professeurs",
    stat_courses: "Cours et modules",
    fin_title: "Gestion financière et revenus",
    stu_title: "Gestion des étudiants",
    tea_title: "Gestion des professeurs",
    cou_title: "Gestion des cours",
    vid_title: "Plateforme vidéo protégée",
    att_title: "Feuille de présence quotidienne"
  }
};

function changeLanguage(lang) {
  localStorage.setItem('school_lang', lang);
  
  // تغيير اتجاه الصفحة ولغتها
  document.documentElement.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
  document.documentElement.setAttribute('lang', lang);
  document.body.style.direction = lang === 'ar' ? 'rtl' : 'ltr';
  document.body.style.textAlign = lang === 'ar' ? 'right' : 'left';

  // تحديث النصوص في الصفحة
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (translations[lang] && translations[lang][key]) {
      el.textContent = translations[lang][key];
    }
  });
}

// تطبيق اللغة المحفوظة بمجرد فتح الصفحة
document.addEventListener('DOMContentLoaded', () => {
  const savedLang = localStorage.getItem('school_lang') || 'ar';
  const langSelect = document.getElementById('lang-selector');
  if (langSelect) {
    langSelect.value = savedLang;
  }
  changeLanguage(savedLang);
});