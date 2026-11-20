<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
  body {
    font-family: 'Noto Naskh Arabic', 'Noto Sans Arabic', sans-serif;
    font-size: 12px;
    color: #111;
  }
  table { border-collapse: collapse; }
  .header-table { width: 100%; margin-bottom: 14px; border-bottom: 3px solid #0b3d91; padding-bottom: 10px; }
  .header-title { font-size: 19px; font-weight: bold; color: #0b3d91; padding: 4px 0; }
  .header-sub { font-size: 10.5px; color: #444; }
  .meta { width: 100%; border: 1px solid #d7deee; background: #f4f6fb; border-radius: 8px; margin-bottom: 14px; font-size: 11.5px; }
  .meta td { padding: 6px 14px; border-bottom: 1px solid #e7ecf6; }
  .meta td.b { font-weight: bold; color: #0b3d91; width: 30%; }
  .tashkeel-test { border: 1px dashed #0b3d91; border-radius: 8px; background: #fbfcff; padding: 10px 14px; margin-bottom: 14px; font-size: 12.5px; }
  .tashkeel-test h3 { margin: 0 0 6px; font-size: 12px; color: #0b3d91; }
  table.crit { width: 100%; margin-bottom: 16px; font-size: 11.5px; }
  table.crit th { background: #0b3d91; color: #fff; padding: 7px 10px; text-align: right; font-size: 11.5px; }
  table.crit td { padding: 7px 10px; border-bottom: 1px solid #e3e8f2; vertical-align: top; }
  table.crit tr.section td { background: #eef2fa; font-weight: bold; font-size: 12px; }
  td.num, td.score { text-align: center; width: 34px; }
  .score { font-weight: bold; font-size: 13px; color: #0b3d91; }
  .signs { width: 100%; margin-top: 18px; }
  .sign-box { width: 50%; border: 1px solid #c9d3e8; border-radius: 8px; padding: 10px 12px 14px; font-size: 11.5px; }
  .sign-box h4 { margin: 0 0 2px; font-size: 12px; color: #0b3d91; }
  .sign-box .name { color: #555; font-size: 10.5px; margin-bottom: 6px; }
  .sign-box img { width: 92%; border-bottom: 1px solid #999; }
  .footer-note { margin-top: 14px; font-size: 10px; color: #666; text-align: center; }
</style>
</head>
<body>
  <table class="header-table">
    <tr>
      <td>
        <div class="header-title">مركز الاختبار المهنيّ — نموذج التقييم العمليّ</div>
        <div class="header-sub">عينة اختبار توليد الـPDF (Step 0 spike) — ليست ملفاّ رسميّا</div>
      </td>
      <td style="text-align:left; vertical-align:top; white-space:nowrap;">
        <span class="header-sub">التاريخ: {{ $data['date'] }}<br>رقم التذكرة: {{ $data['ticket'] }}</span>
      </td>
    </tr>
  </table>

  <table class="meta">
    <tr><td class="b">المرشَّح</td><td>{{ $data['candidate'] }}</td></tr>
    <tr><td class="b">رقم الجواز</td><td>{{ $data['passport'] }}</td></tr>
    <tr><td class="b">المهنة</td><td>{{ $data['trade'] }}</td></tr>
    <tr><td class="b">المهمة العمليّة</td><td>{{ $data['task'] }}</td></tr>
    <tr><td class="b">درجة الصعوبة</td><td>{{ $data['difficulty'] }}</td></tr>
    <tr><td class="b">المُقيِّم</td><td>{{ $data['assessor'] }} — هوية: {{ $data['assessorId'] }}</td></tr>
  </table>

  <div class="tashkeel-test">
    <h3>اختبار التشكيل والدّقة الخطّيّة:</h3>
    «بِسَمِّ اللَّه الرَّحْمَٰن الرَّحِيم: هٰذٰا نَصُّ تَجْربِيَّةٍ مُتِمَّ بِالتَّشْكِيل،
    لِنَتَأَكَّد أَّن حَرَكَات الفَتْح وَالدَّمّ وَالكَسْر وَالتَّنْوِين وَالسُّكُون
    تُصَوَّر فِي المَخْرَج بِصُورَة صَحِيحَة مَرْتُبَة، وَلَا تَنْفَصِل عَنِ الحُرُوف.»
  </div>

  <table class="crit">
    <thead>
      <tr>
        <th style="width:34px;">م</th>
        <th>المعيار</th>
        <th style="width:90px;">الدرجة (0–5)</th>
      </tr>
    </thead>
    <tbody>
      <tr class="section"><td colspan="3">أولا — فهم المشكلة وسير العملية</td></tr>
      @foreach (['أظهر فهما لأهداف المهمة والغرض منها والعمل على تحقيقها؟', 'استخدم الموارد المتاحة بفعالية لجمع المعلومات الضرورية؟', 'اتّبع جميع التعليمات؟', 'أظهر مبادرة في إدارة الوقت لإكمال المهمة ضمن إطار مقبول؟', 'اتّبع التسلسل المنطقي وأدّى المهام بالترتيب الصحيح؟'] as $i => $c)
        <tr>
          <td class="num">{{ $i + 1 }}</td>
          <td>{{ $c }}</td>
          <td class="score">{{ $scores[$i] }}</td>
        </tr>
      @endforeach
      <tr class="section"><td colspan="3">ثانيا — الأمن والسلامة</td></tr>
      @foreach (['اختار معدات الحماية الشخصية المناسبة وارتداها قبل التنفيذ؟', 'اتّخذ جميع احتياطات السلامة أثناء الأداء؟', 'تعامَل مع المواد/الحاويات الخطرة بشكل آمن واتّبع التخلص السليم؟', 'أظهر وعيا بمحيطه والمخاطر على نفسه وعلى الآخرين؟'] as $i => $c)
        <tr>
          <td class="num">{{ $i + 6 }}</td>
          <td>{{ $c }}</td>
          <td class="score">{{ $scores[$i + 5] }}</td>
        </tr>
      @endforeach
      <tr class="section"><td colspan="3">ثالثا — الأدوات والمعدات</td></tr>
      @foreach (['أظهر الكفاءة في استخدام الأدوات والمعدات المناسبة؟', 'أظهر فهما للمبادئ الرئيسية وأجزائها لتحديد المشكلة الأساسية؟'] as $i => $c)
        <tr>
          <td class="num">{{ $i + 10 }}</td>
          <td>{{ $c }}</td>
          <td class="score">{{ $scores[$i + 9] }}</td>
        </tr>
      @endforeach
      <tr class="section"><td colspan="3">رابعا — الصحة والنظافة</td></tr>
      @foreach (['حافظ على منطقة العمل نظيفة ومنظمة طوال المهمة؟', 'سلّم جميع المعدات بحالة جيدة؟'] as $i => $c)
        <tr>
          <td class="num">{{ $i + 12 }}</td>
          <td>{{ $c }}</td>
          <td class="score">{{ $scores[$i + 11] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table class="signs">
    <tr>
      <td class="sign-box">
        <h4>توقيع المُقيِّم</h4>
        <div class="name">{{ $data['assessor'] }} — هوية: {{ $data['assessorId'] }}</div>
        <img src="data:image/png;base64,{{ $sigAssessorBase64 }}">
      </td>
      <td class="sign-box">
        <h4>توقيع المرشَّح (العامل)</h4>
        <div class="name">{{ $data['candidate'] }} — جواز: {{ $data['passport'] }}</div>
        <img src="data:image/png;base64,{{ $sigCandidateBase64 }}">
      </td>
    </tr>
  </table>

  <div class="footer-note">
    هذا النموذج يُقفل بعد التوقيعين — أي تعديل بعدهما غير ممكن.
  </div>
</body>
</html>
