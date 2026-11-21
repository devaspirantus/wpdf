# نموذج البيانات — MVP

> هذه الأسماء الوحيدة المسموحة. لا تخترع أسماء جداول أو أعمدة جديدة.
> المعمارية: MVC — الموديلات في app/Models/، الـControllers في app/Http/Controllers/،
> والـViews في resources/views/. راجع AGENTS.md قسم Architecture.

## خريطة التسمية
| المصطلح | الموديل | الجدول |
|---|---|---|
| ملف العامل | WorkerCase | worker_cases |
| زيارة محطة | StationVisit | station_visits |
| مستند/صورة | Document | documents |
| التقييم | Evaluation | evaluations |
| إرجاع | CaseReturn | case_returns |
| مستخدم | User | users |

## worker_cases
| العمود | النوع | ملاحظة |
|---|---|---|
| id | bigint | PK |
| full_name | string | اسم العامل |
| passport_number | string | |
| ticket_number | string | UNIQUE |
| status | enum | reception / theory / practical / review / completed / returned / cancelled |
| created_at / updated_at | timestamp | |

## documents
| العمود | النوع | ملاحظة |
|---|---|---|
| id | bigint | PK |
| case_id | FK → worker_cases | |
| station | enum | reception / theory / practical |
| type | string | worker_photo / passport / ticket / theory_photo / practical_photo |
| path | string | مسار الصورة في التخزين الخاص |
| created_by | FK → users | مين صوّرها |
| created_at | timestamp | |

## evaluations
| العمود | النوع | ملاحظة |
|---|---|---|
| id | bigint | PK |
| case_id | FK → worker_cases | UNIQUE (تقييم واحد لكل ملف) |
| task | string | المهمة العملية |
| difficulty | enum | easy / medium / hard |
| scores | json | 13 درجة (0-5) |
| evaluator_name | string | |
| evaluator_id_number | string | |
| evaluator_signature | mediumText | بيانات توقيع المقيّم (data-URL صورة) |
| worker_signature | mediumText | بيانات توقيع العامل (data-URL صورة) |
| signed_at | timestamp | null = لم يوقّع بعد |
| created_at | timestamp | |

## station_visits
| العمود | النوع | ملاحظة |
|---|---|---|
| id | bigint | PK |
| case_id | FK → worker_cases | |
| station | enum | reception / theory / practical / review |
| user_id | FK → users | |
| started_at / completed_at | timestamp | لأوقات المحطات في صفحة الـPDF الأولى |

## case_returns
| العمود | النوع | ملاحظة |
|---|---|---|
| id | bigint | PK |
| case_id | FK → worker_cases | |
| returned_to_station | enum | المحطة المرجوع إليها |
| note | text | الملاحظة النصية |
| returned_by | FK → users | موظف رفع البيانات |
| created_at | timestamp | |

## users
| العمود | النوع | ملاحظة |
|---|---|---|
| id, name, email, password | | من Breeze |
| role | enum | reception / theory / practical / review |

## قواعد الحالة
- الملف يتحرك بالترتيب فقط: reception → theory → practical → review → completed
- الإرجاع: من review إلى محطة واحدة، والحالة تصير returned، وتُسجّل في case_returns
- التوقيعان يُرسمان على Canvas في المتصفح (Alpine inline) ويُحفظان data-URL صورة (png/jpeg، 2MB كحد أقصى) عبر Action:SignEvaluation
- التقييم يُقفل التعديل عليه لما يمتلى التوقيعان (يُضبط signed_at)، ولا يمكن التوقيع مرتين
- إذا رُجع الملف للعملي: يُمسح التوقيعان (يعود signed_at = null)

## فصل الطبقات (MVC)
| الطبقة | المكان | المسؤولية |
|---|---|---|
| Model | app/Models/ | البيانات والعلاقات فقط |
| View | resources/views/ | الواجهة العربية (Blade + Livewire) |
| Controller | app/Http/Controllers/ | استقبال الطلب وتحويله للـAction |
| Action/Service | app/Actions/ أو app/Services/ | منطق العمل الحقيقي |