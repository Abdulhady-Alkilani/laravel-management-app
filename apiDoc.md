# توثيق واجهة برمجة التطبيقات (API Documentation) للأنظمة المحمولة

هذا المستند يحتوي على توثيق شامل لجميع مسارات (Endpoints) واجهة برمجة التطبيقات (RESTful API) الخاصة بتطبيق الإدارة.

**الرابط الأساسي (Base URL):** `http://your-domain.com/api/v1`

---

## 📌 إعدادات عامة (Global Settings)

### الترويسات المطلوبة (Headers)
لجميع الطلبات التي تتطلب مصادقة (أي ما عدا تسجيل الدخول)، يجب إرفاق الترويسات التالية:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {YOUR_TOKEN_HERE}
```

### شكل الاستجابة الموحد (Standard Response Format)
جميع الاستجابات تأتي بهذا الهيكل الموحد:
```json
{
  "success": true,
  "message": "نص الرسالة هنا",
  "data": { ... بيانات الاستجابة ... },
  "status_code": 200
}
```

---

## 🔐 1. المصادقة (Authentication)

### 1.1 تسجيل الدخول (Login)
يقوم بالتحقق من بيانات المستخدم وإرجاع الـ Token.
- **المسار:** `POST /login`
- **المصادقة:** ❌ (غير مطلوبة)

**جسم الطلب (Body):**
```json
{
  "username": "johndoe",
  "password": "password123"
}
```

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح.",
  "data": {
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "gender": "Male"
    },
    "roles": ["Civil Engineer"],
    "token": "1|xyzmypasswordtoken..."
  },
  "status_code": 200
}
```

### 1.2 تسجيل الخروج (Logout)
يحذف الـ Token الحالي من قاعدة البيانات.
- **المسار:** `POST /logout`
- **المصادقة:** ✅ (Bearer Token)

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح.",
  "data": null,
  "status_code": 200
}
```

### 1.3 المستخدم الحالي (Get User)
إرجاع بيانات المستخدم المرتبط بالـ Token المرفق.
- **المسار:** `GET /user`
- **المصادقة:** ✅ (Bearer Token)

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "roles": ["Civil Engineer"]
  },
  "status_code": 200
}
```

---

## 🛠️ 2. المهارات (Skills Endpoints)
عامة لجميع المستخدمين المسجلين (تتطلب مصادقة).

### 2.1 جلب قائمة المهارات المتاحة
- **المسار:** `GET /skills`
- **الوصف:** يرجع قائمة بجميع المهارات المخزنة في النظام.

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم جلب المهارات بنجاح.",
  "data": [
    {
      "id": 1,
      "name": "AutoCAD",
      "description": "برنامج تصميم هندسي"
    },
    {
      "id": 2,
      "name": "Project Management",
      "description": "إدارة المشاريع"
    }
  ],
  "status_code": 200
}
```

---

## 🏗️ 3. المهندسون (Engineer Endpoints)
جميع المسارات تبدأ بـ `/engineer`. تتطلب مصادقة (Bearer Token) وأن يكون للمستخدم إحدى صلاحيات الهندسة (مثل: Civil Engineer, Architect... إلخ).

### 3.1 جلب السيرة الذاتية
- **المسار:** `GET /engineer/cv`

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم جلب السيرة الذاتية بنجاح.",
  "data": {
    "id": 5,
    "profile_details": "مهندس مدني بخبرة 5 سنوات",
    "experience": "عملت في شركة X",
    "education": "بكالوريوس هندسة مدنية",
    "cv_file_url": "http://domain.com/storage/cvs/file.pdf",
    "ai_score": 85,
    "skills": [
      { "id": 1, "name": "AutoCAD" }
    ]
  },
  "status_code": 200
}
```

### 3.2 تحديث السيرة الذاتية (أو إنشاؤها)
- **المسار:** `PUT /engineer/cv` أو `POST /engineer/cv`
- **الوصف:** يقوم بتحديث السيرة الذاتية، وفي حال عدم وجود سيرة ذاتية للمستخدم سيتم إنشاؤها تلقائياً. يمكن إرسال حقول نصية وملف معاً.

**جسم الطلب (Body):** (صيغة Form-Data إذا كان هناك ملف)
```json
{
  "profile_details": "تحديث جديد للملف الشخصي",
  "experience": "خبرة جديدة تمت إضافتها",
  "education": "ماجستير هندسة",
  "cv_file": "(ملف اختياري: pdf, docx, jpg...)"
}
```

**استجابة ناجحة (200 OK):** 
يعيد كائن السيرة الذاتية المُحدّث.

### 3.3 إضافة مهارات للسيرة الذاتية
- **المسار:** `POST /engineer/skills`
- **الوصف:** يمكن إرسال مصفوفة تحتوي على **نصوص** (لإضافة مهارات جديدة) أو **أرقام IDs** (لربط مهارات موجودة مسبقاً في النظام).

**جسم الطلب (Body):**
```json
{
  "skills": ["Revit", 2, "إدارة فرق العمل"]
}
```
> [!TIP]
> **ملاحظة لمطور الواجهات (Flutter):**
> يمكنك تمرير مصفوفة مختلطة تحتوي على أرقام `Integers` (تعبر عن معرّف المهارة `ID` من القائمة) ونصوص `Strings` (تعبر عن مهارات جديدة يدوية). الـ API تمت برمجته ليتعامل مع كلا النوعين معاً في نفس المصفوفة ولن يعطيك خطأ `Validation` عند إرسال الأرقام.

### 3.4 جلب المشاريع المرتبطة
عرض المشاريع التي لدى المهندس مهام بداخلها.
- **المسار:** `GET /engineer/projects`

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم جلب المشاريع بنجاح.",
  "data": [
    {
      "id": 1,
      "name": "برج المملكة",
      "status": "مفتوح"
    }
  ],
  "status_code": 200
}
```

### 3.5 جلب المهام
- **المسار:** `GET /engineer/tasks`
- الاستعلام الاختياري (Query Param): `?project_id=1` لفلترة المهام حسب المشروع.

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم جلب المهام بنجاح.",
  "data": [
    {
      "id": 10,
      "description": "مراجعة المخططات",
      "progress": 50,
      "status": "قيد التنفيذ",
      "project": { "id": 1, "name": "برج المملكة" }
    }
  ],
  "status_code": 200
}
```

### 3.6 عرض تفاصيل مهمة محددة
- **المسار:** `GET /engineer/tasks/{taskId}`

### 3.7 تحديث حالة المهمة
- **المسار:** `PUT /engineer/tasks/{taskId}`

**جسم الطلب (Body):**
```json
{
  "progress": 100,
  "status": "مكتملة" 
}
```
*(الخيارات المتاحة للحالة: قيد التنفيذ, مكتملة, معلقة, ملغاة)*

> [!WARNING]
> **قاعدة هامة:** 
> - لا يمكن تعيين `status` كـ "مكتملة" إلا إذا كانت `progress` = 100.
> - لا يمكن تعيين `progress` = 100 إلا إذا كانت `status` = "مكتملة".
> - سيتم إرجاع خطأ `422` في حال مخالفة هذه القاعدة.

### 3.8 إدارة التقارير (Reports CRUD)

**أ. جلب كافة التقارير:** `GET /engineer/reports`
**ب. جلب تفاصيل تقرير محدد:** `GET /engineer/reports/{reportId}`
(ستُرجع تفاصيل التقرير إن كان يخص نفس المهندس)
**ت. إرسال تقرير جديد:** `POST /engineer/reports`
**جسم الطلب:**
```json
{
  "project_id": 1,
  "workshop_id": null,
  "service_id": null,
  "report_type": "تقرير دوري",
  "report_details": "تم إنجاز 30% من الأساسات اليوم."
}
```
**ج. تعديل تقرير:** `PUT /engineer/reports/{reportId}`
**جسم الطلب:** `{"report_details": "تعديل محتوى التقرير"}`
**د. حذف تقرير:** `DELETE /engineer/reports/{reportId}`

---

## 👷 4. العمال (Worker Endpoints)
جميع المسارات تبدأ بـ `/worker`. تتطلب مصادقة (Bearer Token) وصلاحية العامل (Worker).

### 4.1 جلب السيرة الذاتية للعمال
- **المسار:** `GET /worker/cv`
(التفاصيل والاستجابة مطابقة لنقطة المهندس)

### 4.2 تحديث السيرة الذاتية (أو إنشاؤها)
- **المسار:** `PUT /worker/cv` أو `POST /worker/cv`
(التفاصيل مطابقة لنقطة المهندس)

{
  "skills": ["Revit", 2, "إدارة فرق العمل"]
}
```
> [!TIP]
> ينطبق هنا نفس التنبيه المذكور في قسم المهندس: يمكنك تمرير مصفوفة `skills` تحتوي على أرقام `IDs` ونصوص معاً بكل مرونة.

### 4.4 جلب الورشات (Workshops)
الورشات المعيّن فيها هذا العامل.
- **المسار:** `GET /worker/workshops`

**استجابة ناجحة (200 OK):**
```json
{
  "success": true,
  "message": "تم جلب الورشات بنجاح.",
  "data": [
    {
      "id": 3,
      "name": "ورشة الحدادة",
      "project": {
        "id": 1,
        "name": "برج المملكة"
      }
    }
  ],
  "status_code": 200
}
```

### 4.5 جلب المهام
- **المسار:** `GET /worker/tasks`
(الاستجابة مطابقة تماماً لنقطة المهندس)

### 4.6 عرض تفاصيل مهمة محددة
- **المسار:** `GET /worker/tasks/{taskId}`

### 4.7 تحديث حالة المهمة
- **المسار:** `PUT /worker/tasks/{taskId}`
**جسم الطلب (Body):**
```json
{
  "progress": 100,
  "status": "مكتملة"
}
```
> [!WARNING]
> ينطبق هنا نفس شرط نسبة الإنجاز وحالة الإكمال المذكور في نقطة المهندس.

---

## 🚫 5. هيكل الأخطاء (Error Formatting)

في حال فشل الـ Validation الدائم الخاص بـ Laravel، سيرجع خادم الـ API بيانات الخطأ كالتالي (422 Unprocessable Entity):
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "username": [
            "اسم المستخدم مطلوب."
        ]
    }
}
```

وفي حال رفض الصلاحية أو عدم وجود التوكن (401 Unauthorized):
```json
{
    "message": "Unauthenticated."
}
```
