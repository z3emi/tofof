# Tofof Mobile App Master Prompt

استخدم هذا البرومت كما هو لبناء تطبيق موبايل كامل ومتكامل لمتجر Tofof مرتبط بالـ API الحالية.

---

## Prompt

أنت مهندس تطبيقات موبايل Senior وخبير في تصميم واجهات المستخدم (UI/UX). المطلوب منك بناء تطبيق موبايل إنتاجي كامل لمتجر Tofof يدعم Android وiOS من نفس الكود باستخدام Flutter. 

نطاق المشروع:
- هذا التطبيق مخصص للعميل (Customer App) ليكون **متجراً متكاملاً** وليس مجرد تطبيق لعرض المنتجات.
- واجهات التطبيق يجب أن تكون **في غاية الجمال وبمستوى تطبيقات المتاجر العالمية المتميزة** (Premium Design)، مع حركات انتقالية (Animations) سلسة، وتصميم يجذب العميل ويحفزه على الشراء.
- المطلوب تنفيذ جاهز Production من البداية وليس MVP تجريبي.

### 1) الهدف
ابنِ تطبيق متجر موبايل متداخل ومتصل تماماً مع الباكند، ويشمل جميع العمليات (التصفح، الفلاتر، السلة، الدفع، الحساب الشخصي، المحفظة، المفضلة).
- Domain: https://tofofstore.com
- API Base URL: https://tofofstore.com/api

شرط إلزامي:
- ممنوع استخدام Localhost أو أي Local URL في الكود. جميع المسارات يجب أن تتصل بـ https://tofofstore.com/api.

### 2) Stack المطلوبة
- Flutter (آخر إصدار مستقر)
- State Management: Riverpod (أو BLoC)
- Networking: Dio (مع Interceptors لإضافة الـ Token الخاص بالعميل)
- Routing: go_router
- i18n: intl
- التخزين المحلي: flutter_secure_storage (لحفظ الـ Bearer Token) و shared_preferences.

### 3) الهوية البصرية والألوان والتصميم (UI/UX)
التطبيق يجب أن يعكس فخامة متجر Tofof:
- Primary: #6D0E16 (الأحمر العنابي الساحر)
- Text Dark: #34282C
- Accent Gold: #D59E06 (الذهبي الفاخر)
- White: #FFFFFF

قواعد تصميم إلزامية:
- تصميم البطاقات (Cards) والمنتجات يجب أن يكون حديثاً مع تظليل خفيف (Soft Drop Shadows) وزوايا دائرية (Rounded Corners).
- الزر الأساسي (Primary Button): خلفية #6D0E16 ونص أبيض.
- عند الدخول على (تفاصيل المنتج): يجب أن تكون الشاشة مبهرة تعرض الصور المميزة للمنتج بوضوح مع خيارات الإضافة للسلة والمفضلة أسفل الشاشة (Bottom Sticky Bar).
-دعم الـ Dark Mode ضروري مع الحفاظ على تناسق الهوية.

### 4) اللغات والاتجاه
- العربية (RTL) أساسي وافتراضي.
- الإنجليزية (LTR) كخيار بديل.

### 5) API المطلوب ربطها بالكامل للعميل
التطبيق يجب أن يكون متجراً كاملاً شاملاً كل من:

#### أ. مسارات التصفح الأساسية (بدون توثيق)
- `GET /store/sliders?locale=en`
- `GET /store/ui-content`
- `GET /store/sections`
- `GET /store/categories`
- `GET /store/products` (مع الفلاتر: category_id, section_id, sort, q)
- `GET /store/discount-codes`

#### ب. مسارات حساب العميل والمصادقة (Auth Routes)
- `POST /auth/register` (تسجيل حساب جديد)
- `POST /auth/login` (تسجيل الدخول وإرجاع Bearer Token)
- `POST /auth/logout` (تسجيل الخروج)
- `GET /auth/me` (جلب بيانات المستخدم الحالي)

#### جـ. مسارات العمليات (تحتاج Bearer Token)
**السلة والدفع:**
- `GET /cart` ، `POST /cart` ، `PATCH /cart/{key}` ، `DELETE /cart/{key}`
- `POST /cart/discount` ، `DELETE /cart/discount`
- `GET /checkout` ، `POST /checkout` (لإتمام الطلب)

**الحساب الشخصي والمحفظة والطلبات:**
- `GET /profile` ، `PATCH /profile`
- `GET /profile/orders` (متابعة طلباتي)، `GET /profile/orders/{id}`
- `GET /profile/wallet` (عرض رصيد المحفظة العمليات)
- `GET /profile/addresses` (كذلك POST, PATCH, DELETE للعناوين)
- `GET /profile/notifications` (إشعارات العميل)

**المفضلة:**
- `GET /wishlist` ، `POST /wishlist/{productId}/toggle`

*(ممنوع قطعاً استخدام مسارات تخص الـ employee)*

### 6) شاشات التطبيق الأساسية
- **Splash & Onboarding**
- **Auth Flow:** Login, Register
- **Main Navigation (Bottom Bar):** Home, Categories, Cart, Account
- **Home:** Sliders, Sections, New Arrivals
- **Categories:** عند الدخول للقسم تظهر منتجاته الخاصة فقط مع الفلاتر.
- **Product Details:** صور، الوصف، السعر، إضافة للسلة والمفضلة.
- **Cart & Checkout:** تفاصيل السلة، الخصومات، عناوين الشحن وإتمام الطلب بسلاسة.
- **Account (Profile):** قائمة فخمة تحتوي على (طلباتي، العناوين، المحفظة المالية الخاصة بالعميل، الإعدادات، تسجيل الخروج).
- **Orders Tracking:** تتبع حالات الطلب بتصميم Timeline واضح.

### 7) متطلبات هندسية
- **Clean Architecture** وتقسيم المشروع إلى (Data, Domain, Presentation).
- **Authentication Flow:** معالجة الـ 401 Unauthorized وإعادة توجيه المستخدم لشاشة تسجيل الدخول بطريقة ذكية.
- State Management محترفة لإدارة السلة والـ Wishlist ديناميكياً لتحديث الـ UI فوراً.

### 8) مخرجاتك المطلوبة
أريد منك تسليم الآتي كـ Production Code:
- هيكل المشروع كامل مع إعدادات الشبكة Interceptors لإرفاق توكن العميل.
- State Management للسلة والمستخدم.
- نماذج الواجهات (Screen Widgets) للمنتجات والسلة والبروفايل.
- طريقة تشغيل التطبيق.

لا تختصر. أريد تطبيق متجر حقيقي متكامل مكمل بكل تفاصيل حساب العميل والمحفظة والطلبات.