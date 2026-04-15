# دليل اختبار API الشامل باستخدام Postman

الهدف من هذا الدليل هو توفير مرجع عملي وسريع لاختبار كافة المسارات (Endpoints) للواجهة البرمجية (API) عبر برنامج Postman دون نسيان أي مسار، ليكون المطور والتطبيق المحمول على دراية تامة بطريقة الربط.

---

## 🛠️ أولاً: إعدادات الإرسال الأساسية (يجب قراءتها)

1. **الرابط الأساسي (Base URL):** أنشئ متغيراً في بيئة Postman باسم `base_url` بقيمة السيرفر مع المسار، مثلاً: `http://localhost:8000/api/v1`
2. **الترويسات الثابتة (Global Headers):** لجميع الطلبات القادمة باستثناء Login، تأكد من إضافة:
   - `Accept: application/json`
3. **المصادقة (Authorization):**
   - اختر نوع المصادقة **Bearer Token**.
   - القيمة ستكون المتغير `{{token}}` (والذي ستحصل عليه عبر طلب نسجيل الدخول كمهندس أو كعامل).

---

## 🔐 ثانياً: مصادقة المستخدمين (Authentication)

### 1️⃣ تسجيل الدخول (Login)
- **النقطة:** تحصل منها على الـ Token الخاص بالجلسة والذي يعتمد عليه باقي الـ API.
- **Method:** `POST`
- **URL:** `{{base_url}}/login`
- **Body (raw - JSON):**
```json
{
  "username": "tester1",
  "password": "password"
}
```
*(هام: بمجرد نجاح الطلب، انسخ قيمة Token من الاستجابة وضعها في متغير `{{token}}` ببيئة الـ Postman).*

### 2️⃣ جلب بيانات المستخدم الحالي (Get Current User)
- **Method:** `GET`
- **URL:** `{{base_url}}/user`
*(توضح لك البيانات تفصيلياً مع مصفوفة "Roles" لمعرفة هل المستخدم مهندس أم عامل).*

### 3️⃣ تسجيل الخروج (Logout)
- **Method:** `POST`
- **URL:** `{{base_url}}/logout`
*(تقوم بإبطال صلاحية الـ Token المُرسل فوراً وإنهاء الجلسة).*

---

## 🏗️ ثالثاً: مسارات المـهـنـدس (Engineer Endpoints)
**(تأكد أن الـ Token الحالي يعود لمستخدم يملك دور Engineer، لتفادي الخطأ 403/404)**

### 4️⃣ عرض السيرة الذاتية (Get CV)
- **Method:** `GET`
- **URL:** `{{base_url}}/engineer/cv`

### 5️⃣ تعديل السيرة الذاتية (Update CV)
- **Method:** `PUT`
- **URL:** `{{base_url}}/engineer/cv`
- **Body:**
```json
{
  "profile_details": "وصف عن المهندس المحدث",
  "experience": "خبرات المهندس...",
  "education": "معلومات الجامعة"
}
```

### 6️⃣ إضافة مهارات للسيرة الذاتية (Add Skills)
- **Method:** `POST`
- **URL:** `{{base_url}}/engineer/skills`
- **Body:**
```json
{
  "skills": ["Revit", "AutoCAD", "Primavera P6"]
}
```

### 7️⃣ جلب المشاريع المسندة للمهندس (Get Projects)
- **Method:** `GET`
- **URL:** `{{base_url}}/engineer/projects`

### 8️⃣ جلب كافة المهام الهندسية (Get All Tasks)
- **Method:** `GET`
- **URL:** `{{base_url}}/engineer/tasks`
*(ملاحظة: يمكن إضافة فلتر استعلام للمشروع كالآتي: `{{base_url}}/engineer/tasks?project_id=1`)*

### 9️⃣ عرض تفاصيل مهمة هندسية محددة (Get Specific Task)
- **Method:** `GET`
- **URL:** `{{base_url}}/engineer/tasks/1` *(استبدل 1 برقم المهمة)*

### 🔟 تحديث تقدم وحالة مهمة هندسية (Update Task)
- **Method:** `PUT`
- **URL:** `{{base_url}}/engineer/tasks/1` *(استبدل 1 برقم المهمة الحقيقي المعين لك)*
- **Body:**
```json
{
  "progress": 60,
  "status": "قيد التنفيذ"
}
```

### 🔟 إدارة التقارير (Reports CRUD)

**أ. جلب جميع تقارير المهندس (Get All Reports):**
- **Method:** `GET`
- **URL:** `{{base_url}}/engineer/reports`

**ب. عرض تفاصيل تقرير محدد (Get Specific Report):**
- **Method:** `GET`
- **URL:** `{{base_url}}/engineer/reports/10` *(استبدل 10 برقم التقرير)*

**ت. إرسال تقرير جديد (Create Report):**
- **Method:** `POST`
- **URL:** `{{base_url}}/engineer/reports`
- **Body:**
```json
{
  "project_id": 1,
  "report_type": "تقرير دوري",
  "report_details": "محتويات التقرير هنا..."
}
```

**ث. تعديل تقرير موجود (Update Report):**
- **Method:** `PUT`
- **URL:** `{{base_url}}/engineer/reports/10` *(التقرير رقم 10)*
- **Body:**
```json
{
  "report_type": "تقرير طارئ",
  "report_details": "تم كشف تسريب مياه ويجب المعالجة فوراً."
}
```

**ج. حذف تقرير (Delete Report):**
- **Method:** `DELETE`
- **URL:** `{{base_url}}/engineer/reports/10` *(يحذف التقرير بشكل نهائي)*

---

## 👷 رابعاً: مسارات العـامـل (Worker Endpoints)
**(تأكد أن الـ Token الحالي يعود لمستخدم يملك دور Worker لنجاح هذه الروابط)**

### 1️⃣1️⃣ استعراض السيرة الذاتية للعامل (Get CV)
- **Method:** `GET`
- **URL:** `{{base_url}}/worker/cv`

### 1️⃣2️⃣ تعديل السيرة الذاتية للعامل (Update CV)
- **Method:** `PUT`
- **URL:** `{{base_url}}/worker/cv`
- **Body:**
```json
{
  "profile_details": "عامل تمديدات صحية",
  "experience": "خبرة 10 سنوات",
  "education": "معهد مهني 2012"
}
```

### 1️⃣3️⃣ إضافة مهارات العامل (Add Skills)
- **Method:** `POST`
- **URL:** `{{base_url}}/worker/skills`
- **Body:**
```json
{
  "skills": ["سباكة", "لحام"]
}
```

### 1️⃣4️⃣ استعراض الورشات التابع لها (Get Workshops)
- **Method:** `GET`
- **URL:** `{{base_url}}/worker/workshops`
*(ستعود الورشات ببياناتها مع اسم المشروع واسم المشرف).*

### 1️⃣5️⃣ جلب المهام العمالية (Get Tasks)
- **Method:** `GET`
- **URL:** `{{base_url}}/worker/tasks`

### 1️⃣6️⃣ عرض تفاصيل مهمة عمالية محددة (Get Specific Task)
- **Method:** `GET`
- **URL:** `{{base_url}}/worker/tasks/5` *(استبدل 5 برقم المهمة)*

### 1️⃣7️⃣ تحديث تقدم وحالة مهام العامل (Update Task)
- **Method:** `PUT`
- **URL:** `{{base_url}}/worker/tasks/5` *(استبدل 5 برقم مهمة تابعة للعامل)*
- **Body:**
```json
{
  "progress": 100,
  "status": "مكتملة"
}
```
*(الحالات المسموحة للمهام: قيد التنفيذ, مكتملة, معلقة, ملغاة).*
