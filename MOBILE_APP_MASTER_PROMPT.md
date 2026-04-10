# Tofof Mobile App Master Prompt

استخدم هذا البرومت كما هو لبناء تطبيق موبايل كامل لمتجر Tofof مرتبط بالـ API الحالية.

---

## Prompt

أنت مهندس تطبيقات موبايل Senior. المطلوب منك بناء تطبيق موبايل إنتاجي كامل لمتجر Tofof يدعم Android وiOS من نفس الكود باستخدام Flutter.

### 1) الهدف
ابنِ تطبيق متجر موبايل متكامل مرتبط بالباكند الحالي للموقع:
- Domain: https://tofofstore.com
- API Base URL: https://tofofstore.com/api

شرط إلزامي:
- ممنوع استخدام Localhost أو أي Local URL في الكود أو الإعدادات أو الأمثلة.
- استخدم فقط https://tofofstore.com/api في جميع أجزاء المشروع.

### 2) Stack المطلوبة
- Flutter (آخر إصدار مستقر)
- State Management: Riverpod (أو BLoC إذا اخترته مع التزام كامل)
- Networking: Dio
- Routing: go_router
- Secure Token Storage: flutter_secure_storage
- i18n: intl

### 3) شرط مهم جدا: الهوية البصرية والألوان
طبّق نفس هوية الموقع. لا تستخدم ألوان عشوائية.

الألوان الأساسية:
- Primary: #6D0E16
- Text Dark: #34282C
- Accent Gold: #D59E06
- White: #FFFFFF

قواعد تصميم إلزامية:
- الزر الأساسي: خلفية #6D0E16 ونص أبيض.
- العناصر الثانوية: #34282C.
- الخلفيات: #FFFFFF.
- تصميم احترافي نظيف مناسب للتطبيقات التجارية.
- دعم Dark Mode مع الحفاظ على نفس هوية الألوان.

### 4) اللغات والاتجاه
- العربية (RTL) افتراضي.
- الإنجليزية (LTR).
- التطبيق يبدّل الاتجاه تلقائيا حسب اللغة.
- عند جلب السلايدر استخدم locale=en إذا اللغة إنجليزية.

### 5) API المطلوب ربطها

#### Public Endpoints
- GET /store/sliders?locale=en (اختياري)
- GET /store/ui-content
- GET /store/sections
- GET /store/categories
- GET /store/products
- GET /store/discount-codes

#### Auth/Protected Endpoints
- POST /login (body: pin)
- GET /store/notifications (Bearer token)
- GET /user (Bearer token)
- POST /employee/logout (Bearer token)

### 6) شاشات التطبيق المطلوبة
- Splash
- Onboarding
- Home
  - Sliders (hero + promo_primary + promo_secondary)
  - Top Window من /store/ui-content
  - Popup Notification من /store/ui-content
  - Sections
  - منتجات من /store/products
- Products List + Search + Filters + Pagination
- Product Details
- Categories
- Discount Codes
- Notifications (بعد تسجيل الدخول)
- Settings (اللغة + الثيم)
- Login (PIN)

### 7) متطلبات هندسية
- Clean Architecture (Data / Domain / Presentation)
- فصل واضح بين API layer وUI
- Models + JSON parsing منظم
- Error handling شامل (Network, Timeout, 401, 500)
- Loading/Empty/Error states محترفة
- Caching خفيف لشاشة Home
- Logging مناسب في debug mode

### 8) الأمان
- خزّن التوكن فقط في flutter_secure_storage
- أضف Authorization: Bearer TOKEN لكل Endpoint محمي
- عند 401 نفّذ logout تلقائي وارجع لصفحة الدخول

### 9) مخرجاتك المطلوبة (إلزامي)
أريد منك تسليم كل شيء بشكل عملي وليس شرح نظري فقط:
- هيكل المشروع الكامل
- جميع الملفات الأساسية مع كودها
- Theme system كامل بالألوان المحددة
- API service + interceptors
- نماذج Data Models لكل Endpoint أساسي
- ViewModels/Controllers للشاشات
- Widgets قابلة لإعادة الاستخدام
- أوامر تشغيل المشروع
- أوامر بناء Android APK + AAB
- أوامر بناء iOS
- طريقة تغيير Base URL بسهولة

### 10) جودة التنفيذ
- الكود clean وقابل للصيانة
- لا يوجد hardcode غير ضروري
- مهيأ للتوسع لاحقا
- UX سريع وسلس على الأجهزة المتوسطة
- لا يوجد أي مرجع Local أو localhost أو 127.0.0.1 نهائيا

### 11) تنسيق التسليم
ابدأ بالتالي:
1. Project Structure
2. Dependencies
3. Core Config (theme, api, localization)
4. Feature by Feature Implementation
5. Run & Build Instructions
6. Next Improvements

لا تختصر. أريد تنفيذ كامل بجودة Production-ready.

---

## ملاحظة
إذا احتجت افتراضات غير مذكورة، افترض أفضل ممارسة مناسبة لتطبيق متجر احترافي.