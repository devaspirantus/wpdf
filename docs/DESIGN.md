# نظام التصميم (Design System)

تُبنى واجهة المنصة بأكملها وفق **نظام تصميم الحكومة السعودية (DGA / كود المنصات)**.
الواجهة عربية RTL بالكامل. لا تُستخدم أي ألوان من لوحة Tailwind الافتراضية في أي
مكوّن — الألوان كلها من الرموز (tokens) المعرّفة في `@theme`.

## الخطوط

- **Noto Sans Arabic** — weights 400 / 500 / 600 / 700
- مدمجة محليًا (self-hosted) في `public/fonts/*.woff2` — لا CDN خارجي.
- مُعرّفة عبر `@font-face` ثم مربوطة بـ `--font-sans` في `resources/css/app.css`.

## رموز الألوان (Color Tokens)

المعرّفة في `@theme` داخل `resources/css/app.css`. تُستخدم كأصناف Tailwind مولّدة
مثل `bg-primary`, `text-navy`, `border-line`.

| Token | القيمة | الاستخدام |
|-------|--------|-----------|
| `primary` | `#26634B` | اللون الأساسي — أزرار، ترويسات بطاقات، روابط. |
| `primary-dark` | `#144D3F` | hover/active للأزرار الأساسية، عناوين. |
| `primary-tint` | `#27AA8C` | تظليل أفتح للتحويم البسيط. |
| `primary-pastel` | `#ACDDC7` | خلفيات ناعمة، أيقونات، شارات. |
| `secondary` | `#005A96` | لون ثانوي — أزرار بديلة، روابط مساعدة. |
| `secondary-dark` | `#0053BA` | hover للزر الثانوي. |
| `secondary-tint` | `#0054AE` | تظليل بديل. |
| `secondary-pastel` | `#339EEC` | خلفيات/شارات ثانوية. |
| `navy` | `#160F3E` | خلفية الشريط العلوي (header)، نصوص على `warning`. |
| `navy-tint` | `#444061` | تظليل كحلي. |
| `navy-pastel` | `#CDCCD5` | تظليل كحلي فاتح. |
| `danger` | `#AF0818` | أخطاء، حذف، رفض. |
| `success` | `#006604` | نجاح، مكتمل، اعتماد. |
| `warning` | `#FFC107` | تنبيهات، قيد المعالجة. النص على خلفيته `text-navy`. |
| `info` | `#339EEC` | معلومات عامة. |
| `ink` | `#212529` | لون النص الأساسي. |
| `muted` | `#495057` | نصوص ثانوية، وصف، footers. |
| `line` | `#dee2e6` | خطوط فواصل، حدود جداول. |
| `surface` | `#f4f6f5` | خلفية الصفحة العامة. |

## استثناءات (غير DGA)

- **`bg-white`** على وسم `<img>` (صور التوقيعات/الملصقات) مقصود.
- **`text-white`** على الأزرار الملوّنة مقصود.
- الشريط البرتقالي في `review-station.blade.php` (الخط 265) إشارة مقصودة للرفض.
- ملفات `resources/views/pdf/*` تستخدم خط `Noto Naskh Arabic` ولون `#0b3d91`
  الخاص بالـ PDF — **لا تُعدَّل**.

## فئات المكوّنات (Component Classes)

معرّفة في `@layer components` داخل `resources/css/app.css`:

| الصنف | الوصف |
|-------|-------|
| `.dga-card` | بطاقة بإطار أخضر فاتح وزاوية مستديرة وظل خفيف. |
| `.dga-card-header` | ترويسة البطاقة — خلفية خفيفة ونص `primary-dark` عريض. |
| `.dga-card-body` | جسم البطاقة بحشو ثابت. |
| `.dga-btn` | أساس الزر — حواف دائرية (pill)، حدّ مزدوج. |
| `.dga-btn-primary` | زر أساسي مطلي `primary`. |
| `.dga-btn-outline` | زر بإطار `primary` ونص `primary`. |
| `.dga-btn-secondary` | زر بإطار `secondary` ونص `secondary`. |
| `.dga-btn-danger` | زر خطر مطلي `danger`. |
| `.dga-badge` | شارة دائرية صغيرة للنصوص القصيرة. |
| `.dga-table` | جدول بترويسة `primary` وحواف يمينية للنص. |
| `.dga-nav-link` | رابط تنقّل عادي. |
| `.dga-nav-link-active` | رابط تنقّل نشط (خط سفلي `primary`). |

## الأنماط (Patterns)

### التخطيط العام (`layouts/app.blade.php`)
- شريط علوي كحلي (`bg-navy`) بعرض ثابت مع شعار + روابط التنقّل.
- منطقة محتوى بحشو جانبي ثابت على `bg-surface`.
- تذييل سفلي بسيط.

### التخطيط الضيف (`layouts/guest.blade.php`)
- شعار مركزي + عنوان "منصة اختبارات العمالة".
- المحتوى داخل `.dga-card` بعرض `max-w-md`.

### الصفحة الرئيسية (`welcome.blade.php`)
- شريط علوي كحلي مع زر دخول.
- قسم تعريفي مركز بالشعار والعنوان.
- بطاقات (3) تشرح المحطات.
- تذييل.

### المحطّات (Livewire views)
- كل محطة داخل `.dga-card` بـ `.dga-card-header` و `.dga-card-body`.
- الجداول عبر `.dga-table`.
- الأزرار عبر `.dga-btn-*`.
- الحالات عبر `.dga-badge` بألوان الحالة (`success`/`warning`/`danger`/`info`).

### النماذج
- الحقول عبر `@tailwindcss/forms` + قواعد `@layer base` (حدود + focus ring أخضر).
- التحقق من الأخطاء بعرضها في `text-danger` أسفل الحقل.

## القواعد الإلزامية

1. لا تُضاف أي أيقونة من لوحة Tailwind الافتراضية (`gray-*`, `blue-*`, `green-*`،
   إلخ) للواجهة. استخدم رموز DGA فقط.
2. العربية RTL — كل التخطيطات `dir="rtl"`.
3. لا يُعدَّل منطق الأعمال أو نماذج البيانات أو توليد PDF.
4. الخطوط محلية فقط.
