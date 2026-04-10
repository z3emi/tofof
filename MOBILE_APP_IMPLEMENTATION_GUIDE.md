# Tofof Mobile App Implementation Guide (Android + iOS)

## 1) الهدف من هذا الملف
هذا الملف مرجع عملي لبناء تطبيق موبايل واحد يدعم:
- Android (APK + AAB)
- iOS (IPA/TestFlight/App Store)

التطبيق سيكون مرتبط بموقع Tofof الحالي وبواجهات API الموجودة فعليا داخل المشروع.

---

## 2) أفضل خيار تقني
الاقتراح الأفضل لهذا المشروع هو Flutter لأنه يسمح ببناء تطبيق Android وiOS من نفس الكود.

البديل: React Native.

في هذا الملف سيتم الشرح على Flutter.

---

## 3) معلومات الربط مع الـ Backend

### عنوان API الأساسي
- Production (الموقع الفعلي):
  - https://tofofstore.com/api

مهم:
- لا تستخدم localhost أو أي Local URL نهائيا.
- اجعل جميع استدعاءات التطبيق على الدومين الإنتاجي فقط.

ملاحظة مهمة للمشروع الحالي:
- اجعل Base URL الافتراضي (والوحيد) في التطبيق:
  - https://tofofstore.com/api

---

## 4) مسارات API المتاحة للتطبيق

### A) مسارات المتجر العامة (بدون تسجيل دخول)
- GET /store/sliders
  - Query اختياري: locale=en
- GET /store/ui-content
- GET /store/sections
- GET /store/categories
  - Query اختياري: primary_category_id
- GET /store/products
  - Query اختياري:
    - q
    - category_id
    - section_id
    - on_sale=true|false
    - sort=latest|price_asc|price_desc|top_rated
    - per_page (1..60)
- GET /store/discount-codes

### B) مسارات تتطلب توكن Sanctum
- GET /store/notifications
- GET /user
- POST /employee/logout

### C) تسجيل الدخول للموظف
- POST /login
  - Body:
    - pin

يرجع:
- token
- token_type
- employee object

مهم:
- بعد تسجيل الدخول خزّن التوكن محليا بشكل آمن.
- أرسل التوكن في جميع الطلبات المحمية:
  - Authorization: Bearer YOUR_TOKEN

---

## 5) ربط شاشات التطبيق مع API

### Home Screen
- Sliders من /store/sliders
  - hero
  - promo_primary
  - promo_secondary
- Top bar content من /store/ui-content
  - top_window
- Popup content من /store/ui-content
  - popup_notification
- الأقسام من /store/sections
- منتجات مقترحة من /store/products

### Products Screen
- /store/products مع الفلاتر والبحث والpagination

### Categories Screen
- /store/categories

### Discount Codes Screen
- /store/discount-codes

### Notifications Screen (بعد تسجيل الدخول)
- /store/notifications

---

## 6) الهوية البصرية (الألوان)
اعتمد نفس ألوان الموقع في التطبيق:

- Primary: #6D0E16
- Text Dark: #34282C
- Accent Gold: #D59E06
- White: #FFFFFF

قواعد مهمة:
- الزر الأساسي: خلفية #6D0E16 ونص أبيض.
- الروابط والعناصر الثانوية: #34282C.
- الخلفيات الفاتحة: #FFFFFF.
- لا تستخدم ألوان عشوائية خارج النظام اللوني.

---

## 7) دعم اللغات RTL/LTR
- اللغة الأساسية العربية (RTL).
- الإنجليزية (LTR).
- اربط لغة التطبيق مع باراميتر locale في /store/sliders.

نقطة تنفيذ:
- إذا اللغة en أرسل locale=en.
- بخلاف ذلك استخدم العربية افتراضيا.

---

## 8) هيكل مقترح لمشروع Flutter

lib/
- core/
  - constants/
  - theme/
  - network/
  - storage/
- features/
  - home/
  - products/
  - categories/
  - discounts/
  - notifications/
  - auth/
- shared/
  - widgets/
  - models/

---

## 9) إعدادات الشبكة والأمان
- فعّل timeout للطلبات.
- اعمل retry منطقي للطلبات العامة.
- خزّن التوكن في secure storage.
- لا تخزن التوكن في shared preferences كنص عادي.
- في كل request محمي، أضف Bearer token.

---

## 10) خطوات بناء التطبيق (Flutter)

### 10.1 تجهيز البيئة
- ثبّت Flutter SDK.
- ثبّت Android Studio مع Android SDK.
- على macOS ثبّت Xcode (مطلوب لبناء iOS).
- نفذ:
  - flutter doctor

### 10.2 إنشاء المشروع
- flutter create tofof_mobile
- cd tofof_mobile

### 10.3 الحزم الأساسية المقترحة
- dio (API requests)
- flutter_riverpod أو bloc (state management)
- go_router (navigation)
- freezed + json_serializable (models)
- flutter_secure_storage (token)
- intl (localization)

### 10.4 أوامر تشغيل
- flutter pub get
- flutter run

---

## 11) بناء Android

### Debug APK
- flutter build apk --debug

### Release APK
- flutter build apk --release

### Play Store AAB
- flutter build appbundle --release

ملفات الناتج:
- build/app/outputs/flutter-apk/
- build/app/outputs/bundle/release/

مهم للنشر:
- جهز keystore للتوقيع.
- اضبط versionCode وversionName.

---

## 12) بناء iOS

مطلوب Mac + Xcode.

### إعداد أولي
- افتح ios/Runner.xcworkspace داخل Xcode.
- اضبط:
  - Bundle Identifier
  - Team Signing
  - Deployment Target

### بناء iOS
- flutter build ios --release

### رفع TestFlight / App Store
- استخدم Xcode Archive ثم Upload to App Store Connect.

مهم:
- اختبر على جهاز حقيقي.
- تأكد من أيونات التطبيق وLaunch screen وسياسات الخصوصية.

---

## 13) خطة تنفيذ سريعة (Roadmap)
1. إعداد Flutter Project + Theme + Localization
2. بناء طبقة API عامة
3. بناء Home (sliders + ui-content)
4. بناء المنتجات والفلاتر
5. بناء تسجيل الدخول + التوكن
6. بناء الإشعارات المحمية
7. اختبار شامل
8. تجهيز نسخ release Android/iOS

---

## 14) أمثلة استجابة مختصرة من API

### /store/ui-content
يرجع:
- top_window:
  - enabled
  - content_html
  - content_text
  - animation
  - background_color
  - text_color
- popup_notification:
  - enabled
  - content_html
  - content_text

### /store/products
يرجع:
- data: قائمة المنتجات
- meta:
  - current_page
  - last_page
  - per_page
  - total

---

## 15) ملاحظات تشغيل مهمة
- إذا API لا تعمل من الهاتف:
  - تحقق من Base URL
  - تحقق من firewall
  - تحقق من أن Apache يعمل
- إذا ظهرت 401:
  - تحقق من إرسال Bearer token
  - تحقق من صلاحية التوكن
- إذا الصور لا تظهر:
  - تحقق من روابط storage
  - تحقق من أن image_url صالح من API

---

## 16) Checklist قبل النشر
- جميع الشاشات تعمل بدون كراش
- اللغة العربية RTL صحيحة
- الألوان مطابقة للهوية
- API errors handled
- no hardcoded localhost in release
- Android signed build جاهز
- iOS archive جاهز

---

## 17) ملفات مرجعية داخل الباكند
- routes/api.php
- app/Http/Controllers/Api/StoreController.php
- app/Http/Controllers/Api/PinLoginController.php
- app/Services/Auth/PinAuthenticationService.php

هذا الملف يكفي كبداية تنفيذ كاملة لبناء التطبيق على Android وiOS من مشروع Tofof الحالي.