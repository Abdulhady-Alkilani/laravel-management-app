
# تقرير مشروع نظام إدارة البناء والتشييد (Construction Management System)
**تاريخ التقرير:** أبريل 2026
**الهدف:** توفير فهم شامل لبنية النظام الحالي، وتحديد خارطة الطريق للتطويرات الجديدة (تحسين نظام السير الذاتية، دمج الذكاء الاصطناعي للفلترة عبر API Key، وبناء RESTful API لتطبيق Flutter).

---

## 1. التقنيات المستخدمة (Tech Stack)
*   **الخلفية (Backend):** Laravel 12, PHP 8.2+
*   **لوحة التحكم (Admin Panel):** Filament v3.3 (Multi-Panel Architecture)
*   **المصادقة والتسجيل (Auth & Onboarding):** Laravel Livewire 3 (Custom Auth, Multi-step employee application)
*   **قاعدة البيانات (Database):** MySQL
*   **API للموبايل:** Laravel Sanctum, API Resources, Form Requests
*   **الموبايل (قيد التطوير):** Flutter (State Management: Provider, Networking: Dio/Http)

---

## 2. هيكلية النظام الحالية (Current Architecture)
النظام يعتمد على تعدد اللوحات (Panels) بناءً على دور المستخدم (RBAC). يتم توجيه المستخدمين تلقائياً بعد تسجيل الدخول (Livewire `CustomLogin`) إلى اللوحة المناسبة:
*   `Admin`: تحكم كامل.
*   `Manager`: إدارة مشاريعه، ورشاته، تقاريره، ومهامه.
*   `Workshop Supervisor`: إدارة الورش المعين لها، ربط العمال، وإدارة المهام والتقارير.
*   `Reviewer`: مراجعة السير الذاتية (`Cv`) ومقترحات الخدمات الجديدة (`NewServiceProposal`).
*   `Investor`: عرض المشاريع المستثمر بها (القراءة فقط).
*   `Engineer` & `Worker`: (لهم لوحات ويب بسيطة، وسيتم نقل عملهم الأساسي إلى تطبيق Flutter).
*   `Service Proposer`: لتقديم طلبات واقتراحات الخدمات.

---

## 3. مخطط قاعدة البيانات (DBML - Database Markup Language)
*(يحتوي هذا المخطط على البنية الحالية مع التعديلات الجديدة المطلوبة لملفات الـ CV والـ AI)*

```dbml
Table users {
  id int [primary key, increment]
  first_name varchar
  last_name varchar
  username varchar [unique]
  email varchar [unique]
  password varchar
  gender varchar
  address varchar
  nationality varchar
  phone_number varchar
  profile_details text
  created_at timestamp
  updated_at timestamp
}

Table roles {
  id int [primary key, increment]
  name varchar [unique]
}

Table user_roles {
  id int [primary key, increment]
  user_id int[ref: > users.id]
  role_id int[ref: > roles.id]
}

Table cvs {
  id int [primary key, increment]
  user_id int [ref: - users.id]
  profile_details text
  experience text 
  education text
  cv_file_path varchar // <== [تطوير جديد: مسار ملف PDF أو الصورة ATS]
  ai_score int // <==[تطوير جديد: لحفظ تقييم الذكاء الاصطناعي للفلترة]
  cv_status varchar // (قيد الانتظار، تمت الموافقة، مرفوض)
  rejection_reason text
  created_at timestamp
  updated_at timestamp
}

Table skills {
  id int [primary key, increment]
  name varchar [unique]
}

Table cv_skill {
  id int [primary key, increment]
  cv_id int[ref: > cvs.id]
  skill_id int[ref: > skills.id]
}

Table projects {
  id int [primary key, increment]
  name varchar
  description text
  budget decimal
  start_date date
  end_date_planned date
  end_date_actual date
  status varchar
  manager_user_id int [ref: > users.id]
}

Table project_investor_links {
  id int [primary key, increment]
  project_id int[ref: > projects.id]
  investor_user_id int [ref: > users.id]
  investment_amount decimal
}

Table workshops {
  id int [primary key, increment]
  name varchar
  project_id int [ref: > projects.id]
  supervisor_user_id int[ref: > users.id]
}

Table worker_workshop_links {
  id int [primary key, increment]
  worker_id int [ref: > users.id]
  workshop_id int[ref: > workshops.id]
  assigned_date date
}

Table tasks {
  id int[primary key, increment]
  project_id int [ref: > projects.id]
  workshop_id int[ref: > workshops.id]
  assigned_to_user_id int [ref: > users.id]
  description text
  progress int
  status varchar
  start_date date
  end_date_planned date
  actual_end_date date
  estimated_cost decimal
  actual_cost decimal
}

Table reports {
  id int [primary key, increment]
  employee_id int[ref: > users.id]
  project_id int [ref: > projects.id]
  workshop_id int[ref: > workshops.id]
  service_id int [ref: > services.id]
  report_type varchar
  report_details text
  report_status varchar
}

Table services {
  id int[primary key, increment]
  name varchar
}
```

---

## 4. التطويرات المطلوبة: القسم الأول (Laravel / Filament Web App)

### أ. تحسين نظام السير الذاتية (CV System Upgrade)
يجب تعديل النظام ليقبل ملفات السير الذاتية الفعلية (PDF/Images) لتكون متوافقة مع أنظمة ATS.
1.  **تعديل Migration & Model:** إضافة حقل `cv_file_path` إلى جدول `cvs`.
2.  **تعديل نموذج طلب التوظيف (Livewire):** في `EmployeeApplicationController` و `employee-form-step4.blade.php`، إضافة حقل رفع ملف (`FileUpload`) لتخزين السيرة الذاتية وحفظ المسار.
3.  **تعديل Filament Resources:** إضافة إمكانية عرض وتنزيل الملف المرفق في لوحتي `Admin` و `Reviewer` داخل `CvResource`.

### ب. تحليل وفلترة السير الذاتية بالذكاء الاصطناعي (AI CV Filtering via API)
لدور الـ `Admin` والـ `Reviewer`، المطلوب دمج خدمة AI (مثل OpenAI أو DeepSeek) لتقييم وفرز السير الذاتية.
1.  **الإعداد الأمني (API Key):** يجب إنشاء خدمة في Laravel (Service Class) تتصل بـ API الذكاء الاصطناعي باستخدام **API Key**. يجب تخزين هذا المفتاح بشكل آمن في ملف `.env` (مثال: `AI_SERVICE_API_KEY`) واستدعائه عبر الـ `config`.
2.  **الآلية (Filament Action):** إضافة زر `Action` (مثلاً: "تحليل وتصفية بالذكاء الاصطناعي") في جدول السير الذاتية المعلقة.
3.  **العملية:** عند النقر، يقوم الباك إند بإرسال بيانات الـ CVs (المهارات، الخبرات، التفاصيل) عبر HTTP Request (مع تمرير الـ API Key في الـ Header) إلى نموذج الذكاء الاصطناعي، ويطلب منه إعطاء درجة (Score من 0 إلى 100) بناءً على قوة المهارات وسنوات الخبرة.
4.  **النتيجة:** يتم حفظ النتيجة في عمود `ai_score` في جدول `cvs`، ثم يتم إعادة ترتيب الجدول لعرض **أفضل 10 سير ذاتية (Top 10)** حصلت على أعلى تقييم.

---

## 5. التطويرات المطلوبة: القسم الثاني (بناء RESTful API لـ Flutter)

الهدف هو بناء API قوي ومنظم باستخدام `Laravel Sanctum` لخدمة تطبيق الموبايل الخاص بالعمال والمهندسين.

### قواعد التصميم للـ API:
*   استخدام `Route::prefix('api/v1')`.
*   استخدام `FormRequest` لجميع عمليات الـ Validation.
*   استخدام `API Resources` (`JsonResource`) لتنسيق البيانات الراجعة.
*   إرجاع استجابات قياسية (مثال: `success`, `data`, `message`, `status_code`).

### الـ Endpoints المطلوبة:

#### 1. المصادقة (Authentication)
*   `POST /login`: تسجيل الدخول بإرجاع Token (Sanctum) + User Data + Role. *(التسجيل يتم عبر الويب فقط).*
*   `POST /logout`: إتلاف التوكن الحالي.

#### 2. صلاحيات المهندس (Engineer Endpoints)
*   **السيرة الذاتية والمهارات:**
    *   `GET /engineer/cv`: عرض السيرة الذاتية (مع المهارات والملف).
    *   `PUT /engineer/cv`: تعديل بيانات الـ CV.
    *   `POST /engineer/skills`: إضافة مهارات جديدة (باستخدام `firstOrCreate`).
*   **المشاريع:**
    *   `GET /engineer/projects`: جلب المشاريع التي يمتلك المهندس مهاماً فيها.
*   **المهام:**
    *   `GET /engineer/tasks`: عرض المهام (مع الفلترة حسب المشروع).
    *   `PUT /engineer/tasks/{task}`: تعديل نسبة التقدم (`progress`) وحالة المهمة (`status`).
*   **التقارير:**
    *   `GET /engineer/reports`: جلب تقاريره الخاصة.
    *   `POST /engineer/reports`: إنشاء تقرير (تكون حالة `report_status` معلقة افتراضياً).
    *   `PUT /engineer/reports/{report}`: تعديل محتوى التقرير.
    *   `DELETE /engineer/reports/{report}`: حذف التقرير.

#### 3. صلاحيات العامل (Worker Endpoints)
*   **السيرة الذاتية والمهارات:** نفس Endpoints المهندس (`GET /worker/cv`, `PUT /worker/cv`, `POST /worker/skills`).
*   **الورشات:** `GET /worker/workshops`: عرض الورشات المربوط بها العامل.
*   **المهام:** نفس Endpoints المهندس (`GET /worker/tasks`, `PUT /worker/tasks/{task}`).
*   *(العامل ليس لديه صلاحيات للتقارير).*

---

## 6. تمهيد لتطبيق Flutter (Flutter App Blueprint)

(هذا القسم مخصص للـ AI Agent الذي سيكتب كود Flutter)

*   **إدارة الحالة (State Management):** استخدام `Provider`. مطلوب إنشاء: `AuthProvider`, `CvProvider`, `TaskProvider`, `ProjectProvider` (للمهندس), `ReportProvider` (للمهندس), و `WorkshopProvider` (للعامل).
*   **الشبكات (Networking):** استخدام مكتبة `Dio` أو `http`. **هام:** إعداد `Interceptors` لحقن الـ `Bearer Token` (Sanctum Token) تلقائياً في `Headers` كل الطلبات، والتقاط خطأ `401 Unauthorized` لعمل إجبار تسجيل خروج محلي (Force Logout).
*   **التخزين المحلي (Local Storage):** استخدام `flutter_secure_storage` لحفظ الـ Token واسم المستخدم بأمان.
*   **واجهة المستخدم (UI/UX):**
    *   دعم كامل للغة العربية `RTL`.
    *   شاشة Login توجه المستخدم إلى `EngineerDashboard` أو `WorkerDashboard` بناءً على الـ Role العائد من الـ API.
    *   استخدام `BottomNavigationBar` للتنقل السلس بين (المهام، السيرة الذاتية، التقارير/الورش).
