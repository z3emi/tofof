# Tofof Mobile App Implementation Guide (Android + iOS)

## 1) الهدف من هذا الملف
هذا الملف مرجع عملي لبناء تطبيق موبايل واحد يدعم:
- Android (APK + AAB)
- iOS (IPA/TestFlight/App Store)

التطبيق سيكون مرتبط بموقع Tofof الحالي وبواجهات API الموجودة فعليا داخل المشروع.

نطاق التنفيذ:
- التطبيق مخصص للعميل فقط (Customer App).
- لا يوجد أي جزء خاص بالموظفين داخل هذا التطبيق.
- التنفيذ المطلوب جاهز Production من البداية.

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

التطبيق سيكون متجراً كاملاً يحتوي على جميع المسارات (بدون الحاجة لتوثيق، ومسارات تحتاج توثيق العميل فقط).

### A) مسارات التصفح (العامة)
- `GET /store/sliders?locale=en`
- `GET /store/ui-content`
- `GET /store/sections`
- `GET /store/categories`
- `GET /store/products` (يدعم الفلاتر: category_id, section_id, sort, q)
- `GET /store/discount-codes`

### B) مسارات حساب العميل والمصادقة (Auth API)
- `POST /auth/register` (تسجيل حساب جديد)
- `POST /auth/login` (إرجاع Bearer Token)
- `POST /auth/logout` 
- `GET /auth/me` 

### C) مسارات العمليات والحساب (تحتاج Bearer Token للعميل)
- **السلة:** `GET /cart` ، `POST /cart` ، `PATCH /cart/{key}` ، `DELETE /cart/{key}` ، `POST /cart/discount`
- **الدفع وإتمام الطلب:** `GET /checkout` ، `POST /checkout`
- **الطلبات:** `GET /profile/orders` ، `GET /profile/orders/{id}`
- **الملف الشخصي والمحفظة:** `GET /profile` ، `PATCH /profile` ، `GET /profile/wallet` ، `GET /profile/addresses`
- **المفضلة:** `GET /wishlist` ، `POST /wishlist/{productId}/toggle`

ممنوع في هذا التطبيق:
- مسارات الموظفين والإدارة (`/employee/login`, `/employee/logout`)
- أي Endpoint مخصص فقط للـ dashboard.

---

## 5) ربط شاشات التطبيق مع API

### Home Screen
- Sliders من /store/sliders
- Top bar و Popup من /store/ui-content
- الأقسام من /store/sections
- منتجات عرض من /store/products

### Products & Categories Screens
- /store/products لعرض المنتجات مع بناء واجهة تفاعلية للفلاتر الجانبية (Filters Sidebar/BottomSheet).
- /store/categories لاستعراض الأقسام الكلية.

### Cart & Checkout
- واجهة السلة مربوطة بـ `/cart` (تحديث السعر ديناميكياً مع أي تعديل للكمية).
- شاشة Checkout لعنوان الشحن والدفع.

### Profile & Account 
- شاشة حساب شخصي متكاملة (تصميم احترافي).
- محفظة العميل من `/profile/wallet` مع عرض المعاملات.
- تتبع الطلبات `/profile/orders`.

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
- shared/
  - widgets/
  - models/

---

## 9) إعدادات الشبكة والأمان
- فعّل timeout للطلبات.
- اعمل retry منطقي للطلبات العامة.
- لا تستخدم أي PIN أو Token موظف في التطبيق.
- لا تضع أي Secrets داخل الكود.

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
5. اختبار شامل
6. تجهيز نسخ release Android/iOS

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
  - تحقق من endpoint المستخدم (قد يكون endpoint خارج نطاق تطبيق العميل)
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

هذا الدليل خاص بتطبيق العميل فقط ولا يتضمن أي تكامل مع تسجيل دخول الموظفين.

هذا الملف يكفي كبداية تنفيذ كاملة لبناء التطبيق على Android وiOS من مشروع Tofof الحالي.