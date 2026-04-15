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

## 🏗️ 2. المهندسون (Engineer Endpoints)
جميع المسارات تبدأ بـ `/engineer`. تتطلب مصادقة (Bearer Token).

### 2.1 جلب السيرة الذاتية
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

### 2.2 تحديث السيرة الذاتية
- **المسار:** `PUT /engineer/cv`

**جسم الطلب (Body):**
```json
{
  "profile_details": "تحديث جديد للملف الشخصي",
  "experience": "خبرة جديدة تمت إضافتها",
  "education": "ماجستير هندسة"
}
```

**استجابة ناجحة (200 OK):** 
يعيد كائن السيرة الذاتية المُحدّث.

### 2.3 إضافة مهارات للسيرة الذاتية
- **المسار:** `POST /engineer/skills`

**جسم الطلب (Body):**
```json
{
  "skills": ["Revit", "Project Management"]
}
```

### 2.4 جلب المشاريع المرتبطة
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

### 2.5 جلب المهام
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

### 2.6 عرض تفاصيل مهمة محددة
- **المسار:** `GET /engineer/tasks/{taskId}`

### 2.7 تحديث حالة المهمة
- **المسار:** `PUT /engineer/tasks/{taskId}`

**جسم الطلب (Body):**
```json
{
  "progress": 80,
  "status": "قيد التنفيذ" 
}
```
*(الخيارات المتاحة للحالة: قيد التنفيذ, مكتملة, معلقة, ملغاة)*

### 2.7 إدارة التقارير (Reports CRUD)

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

## 👷 3. العمال (Worker Endpoints)
جميع المسارات تبدأ بـ `/worker`. تتطلب مصادقة (Bearer Token).

### 3.1 جلب السيرة الذاتية للعمال
- **المسار:** `GET /worker/cv`
(التفاصيل والاستجابة مطابقة لنقطة المهندس)

### 3.2 تحديث السيرة الذاتية
- **المسار:** `PUT /worker/cv`
(التفاصيل مطابقة لنقطة المهندس)

### 3.3 إضافة مهارات
- **المسار:** `POST /worker/skills`
(التفاصيل مطابقة لنقطة المهندس)

### 3.4 جلب الورشات (Workshops)
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

### 3.5 جلب المهام
- **المسار:** `GET /worker/tasks`
(الاستجابة مطابقة تماماً لنقطة المهندس)

### 3.6 عرض تفاصيل مهمة محددة
- **المسار:** `GET /worker/tasks/{taskId}`

### 3.7 تحديث حالة المهمة
- **المسار:** `PUT /worker/tasks/{taskId}`
**جسم الطلب (Body):**
```json
{
  "progress": 100,
  "status": "مكتملة"
}
```

---

## 🚫 4. هيكل الأخطاء (Error Formatting)

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
