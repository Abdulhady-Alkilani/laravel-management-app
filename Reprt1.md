# تقرير تنفيذ الخطوة 4(أ): تحسين نظام السير الذاتية (CV System Upgrade)
**تاريخ التنفيذ:** 9 أبريل 2026  
**الحالة:** ✅ مكتمل بنجاح

---

## ملخص التنفيذ
تم تعديل نظام السير الذاتية ليقبل رفع ملفات فعلية (PDF/Images) متوافقة مع أنظمة ATS، مع إمكانية العرض والتنزيل في لوحات Filament (Admin و Reviewer).

---

## الملفات المُعدّلة والمُنشأة

### 1. [NEW] Migration — إضافة حقول جديدة لجدول `cvs`
**الملف:** `database/migrations/2026_04_09_112040_add_cv_file_path_and_ai_score_to_cvs_table.php`

| الحقل | النوع | الوصف |
|---|---|---|
| `cv_file_path` | `varchar (nullable)` | مسار ملف السيرة الذاتية المرفوع (PDF أو صورة) |
| `ai_score` | `integer (nullable)` | تقييم الذكاء الاصطناعي (0-100) — جاهز للخطوة 4ب |

**حالة الـ Migration:** ✅ تم تشغيله بنجاح (23.07ms)

---

### 2. [MODIFY] Model — `app/Models/Cv.php`
- **التغيير:** إضافة `'cv_file_path'` و `'ai_score'` إلى مصفوفة `$fillable`
- **السبب:** السماح بالـ Mass Assignment لهذين الحقلين الجديدين

```diff
 protected $fillable = [
     'user_id',
     'profile_details',
     'experience',
     'education',
+    'cv_file_path', // مسار ملف السيرة الذاتية
+    'ai_score',     // تقييم الذكاء الاصطناعي
     'cv_status',
     'rejection_reason',
 ];
```

---

### 3. [MODIFY] Controller — `app/Http/Controllers/EmployeeApplicationController.php`
**التغييرات:**

#### أ. Validation (دالة `storeStep4`)
```diff
+ 'cv_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
```
- **الأنواع المسموحة:** PDF, JPG, JPEG, PNG
- **الحد الأقصى:** 5MB (5120 KB)
- **رسائل خطأ مخصصة بالعربية** لنوع الملف والحجم

#### ب. تخزين الملف
```php
$cvFilePath = null;
if ($request->hasFile('cv_file')) {
    $cvFilePath = $request->file('cv_file')->store('cvs', 'public');
}
```
- الملفات تُخزن في: `storage/app/public/cvs/`
- يمكن الوصول إليها عبر: `{APP_URL}/storage/cvs/{filename}`

#### ج. حفظ المسار في قاعدة البيانات
```diff
 $cv = Cv::create([
     ...
+    'cv_file_path' => $cvFilePath,
     ...
 ]);
```

---

### 4. [MODIFY] Blade Template — `resources/views/applications/employee-form-step4.blade.php`
**التغييرات:**

#### أ. إضافة `enctype` للفورم
```diff
- <form action="..." method="POST">
+ <form action="..." method="POST" enctype="multipart/form-data">
```

#### ب. إضافة Fieldset جديد لرفع الملف
- حقل `<input type="file">` مع `accept=".pdf,.jpg,.jpeg,.png"`
- أيقونة Bootstrap Icons `bi-file-earmark-arrow-up`
- نص مساعد يوضح الأنواع المسموحة والحجم الأقصى
- ملاحظة تنصح بصيغة PDF للتوافق مع ATS
- عرض رسائل الأخطاء

---

### 5. [MODIFY] Filament Admin Panel — `app/Filament/Resources/CvResource.php`
**التغييرات:**

#### أ. الفورم (Form)
- إضافة `FileUpload::make('cv_file_path')` مع:
  - `disk('public')`, `directory('cvs')`
  - `acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])`
  - `maxSize(5120)` — 5MB
  - `openable()`, `downloadable()`, `previewable()` — للعرض والتنزيل
  - نص مساعد بالعربية

#### ب. الجدول (Table)
- إضافة `IconColumn::make('cv_file_path')` مع:
  - أيقونة تنزيل خضراء ✅ إذا يوجد ملف
  - علامة X رمادية ❌ إذا لا يوجد ملف
  - `tooltip` عند التمرير
  - `url()` + `openUrlInNewTab()` — ينقر للتنزيل مباشرة

---

### 6. [MODIFY] Filament Reviewer Panel — `app/Filament/Reviewer/Resources/Reviewer/CvResource.php`
**التغييرات:**

#### أ. الفورم (Form)
- إضافة `FileUpload::make('cv_file_path')` بوضع **`disabled()`** (عرض وتنزيل فقط، بدون تعديل)
- `openable()`, `downloadable()`, `previewable()`
- نص مساعد: "ملف السيرة الذاتية المرفق (للعرض والتنزيل فقط)"

#### ب. الجدول (Table)
- نفس عمود الأيقونة في لوحة Admin (تنزيل / لا يوجد ملف)

---

## نتائج التحقق

| الاختبار | النتيجة |
|---|---|
| Migration تم تنفيذه بنجاح | ✅ |
| Storage Link موجود | ✅ |
| PHP Syntax — `Cv.php` | ✅ لا أخطاء |
| PHP Syntax — `EmployeeApplicationController.php` | ✅ لا أخطاء |
| PHP Syntax — `CvResource.php` (Admin) | ✅ لا أخطاء |
| PHP Syntax — `CvResource.php` (Reviewer) | ✅ لا أخطاء |

---

## ملاحظات تقنية

1. **مسار التخزين:** الملفات تُخزن في `storage/app/public/cvs/` ويتم الوصول إليها عبر `public/storage/cvs/` (Symbolic Link)
2. **حقل `ai_score`:** تمت إضافته في الـ Migration استعداداً للخطوة 4(ب) — تحليل وفلترة السير الذاتية بالذكاء الاصطناعي
3. **صلاحيات المراجع:** المراجع يستطيع **عرض وتنزيل** الملف فقط، ولا يستطيع تعديله أو استبداله
4. **حقل رفع الملف اختياري (`nullable`):** المتقدم يمكنه إرسال الطلب بدون ملف مرفق

---

## الخطوات التالية المقترحة

- [ ] **الخطوة 4(ب):** تحليل وفلترة السير الذاتية بالذكاء الاصطناعي (AI CV Filtering via API)
- [ ] **القسم 5:** بناء RESTful API لتطبيق Flutter (Sanctum + API Resources)
- [ ] **القسم 6:** تطوير تطبيق Flutter (Provider + Dio + UI)

---

> **ملاحظة:** يُنصح باختبار رفع ملف فعلي عبر نموذج طلب التوظيف (الخطوة 4) والتأكد من ظهوره في لوحتي Admin و Reviewer قبل الانتقال للخطوة التالية.
