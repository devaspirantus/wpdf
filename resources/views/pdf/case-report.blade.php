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
  .page { page-break-after: always; }
  .page:last-child { page-break-after: auto; }
  .header-table { width: 100%; margin-bottom: 14px; border-bottom: 3px solid #0b3d91; padding-bottom: 10px; }
  .header-title { font-size: 19px; font-weight: bold; color: #0b3d91; padding: 4px 0; }
  .header-sub { font-size: 10.5px; color: #444; }
  .meta { width: 100%; border: 1px solid #d7deee; background: #f4f6fb; border-radius: 8px; margin-bottom: 14px; font-size: 11.5px; }
  .meta td { padding: 6px 14px; border-bottom: 1px solid #e7ecf6; }
  .meta td:last-child { padding-bottom: 6px; }
  .meta tr:last-child td { border-bottom: none; }
  .meta td.b { font-weight: bold; color: #0b3d91; width: 30%; }
  .doc-title { text-align: center; font-size: 14px; font-weight: bold; color: #0b3d91; margin-bottom: 10px; }
  .doc-time { text-align: center; font-size: 11px; color: #555; margin-bottom: 8px; }
  .doc-img { display: block; width: 100%; max-height: 78vh; object-fit: contain; border: 1px solid #d7deee; border-radius: 8px; background: #f8f9fc; }
  table.crit { width: 100%; margin-bottom: 14px; font-size: 11.5px; }
  table.crit th { background: #0b3d91; color: #fff; padding: 7px 10px; text-align: right; font-size: 11.5px; }
  table.crit td { padding: 6px 10px; border-bottom: 1px solid #e3e8f2; vertical-align: top; }
  table.crit tr.section td { background: #eef2fa; font-weight: bold; font-size: 12px; }
  td.num, td.score { text-align: center; width: 34px; }
  .score { font-weight: bold; font-size: 13px; color: #0b3d91; }
  .signs { width: 100%; margin-top: 14px; }
  .sign-box { width: 50%; border: 1px solid #c9d3e8; border-radius: 8px; padding: 10px 12px 14px; font-size: 11.5px; }
  .sign-box h4 { margin: 0 0 2px; font-size: 12px; color: #0b3d91; }
  .sign-box .name { color: #555; font-size: 10.5px; margin-bottom: 6px; }
  .sign-box img { width: 92%; border-bottom: 1px solid #999; }
  .footer-note { margin-top: 12px; font-size: 10px; color: #666; text-align: center; }
</style>
</head>
<body>
  {{-- ===== Page 1: worker data + station timestamps ===== --}}
  <div class="page">
    <table class="header-table">
      <tr>
        <td>
          <div class="header-title">{{ $centerName }} — ملف العامل</div>
          <div class="header-sub">ملف اختبار وتقييم للمرشح</div>
        </td>
        <td style="text-align:left; vertical-align:top; white-space:nowrap;">
          <span class="header-sub">التاريخ: {{ now()->format('d/m/Y') }}<br>رقم التذكرة: {{ $case->ticket_number }}</span>
        </td>
      </tr>
    </table>

    <table class="meta">
      <tr><td class="b">اسم العامل</td><td>{{ $case->full_name }}</td></tr>
      <tr><td class="b">رقم الجواز</td><td>{{ $case->passport_number }}</td></tr>
      <tr><td class="b">رقم التذكرة</td><td>{{ $case->ticket_number }}</td></tr>
    </table>

    <table class="meta">
      <tr><td class="b">التوقيت في الاستقبال</td><td>{{ $receptionTime }}</td></tr>
      <tr><td class="b">التوقيت في المعمل النظري</td><td>{{ $theoryTime }}</td></tr>
      <tr><td class="b">التوقيت في المعمل العملي</td><td>{{ $practicalTime }}</td></tr>
      <tr><td class="b">توقيع التقييم</td><td>{{ $reviewTime }}</td></tr>
    </table>
  </div>

  {{-- ===== Pages 2–6: station documents ===== --}}
  @foreach ($images as $image)
    <div class="page">
      <div class="doc-title">{{ $image['label'] }}</div>
      <div class="doc-time">{{ $image['time'] }}</div>
      <img class="doc-img" src="{{ $image['src'] }}" alt="{{ $image['label'] }}">
    </div>
  @endforeach

  {{-- ===== Page 7: evaluation ===== --}}
  <div class="page">
    <table class="header-table">
      <tr>
        <td>
          <div class="header-title">{{ $centerName }} — نموذج التقييم العمليّ</div>
          <div class="header-sub">مرجع لملف العامل: {{ $case->full_name }} — جواز: {{ $case->passport_number }}</div>
        </td>
        <td style="text-align:left; vertical-align:top; white-space:nowrap;">
          <span class="header-sub">التاريخ: {{ $evaluation->signed_at->format('d/m/Y') }}<br>رقم التذكرة: {{ $case->ticket_number }}</span>
        </td>
      </tr>
    </table>

    <table class="meta">
      <tr><td class="b">العامل</td><td>{{ $case->full_name }}</td></tr>
      <tr><td class="b">رقم الجواز</td><td>{{ $case->passport_number }}</td></tr>
      <tr><td class="b">المهمة العمليّة</td><td>{{ $evaluation->task }}</td></tr>
      <tr><td class="b">درجة الصعوبة</td><td>{{ $difficulty }}</td></tr>
    </table>

    <table class="crit">
      <thead>
        <tr>
          <th style="width:34px;">م</th>
          <th>المعيار</th>
          <th style="width:90px;">الدرجة (0–5)</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sections as $sectionTitle => $criteria)
          <tr class="section"><td colspan="3">{{ $sectionTitle }}</td></tr>
          @foreach ($criteria as $key => $criterion)
            <tr>
              <td class="num">{{ $key }}</td>
              <td>{{ $criterion }}</td>
              <td class="score">{{ $evaluation->scores[$key] ?? '—' }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>

    <table class="signs">
      <tr>
        <td class="sign-box">
          <h4>توقيع المقيّم</h4>
          <div class="name">{{ $evaluation->evaluator_name }} — هوية: {{ $evaluation->evaluator_id_number }}</div>
          <img src="{{ $evaluation->evaluator_signature }}">
        </td>
        <td class="sign-box">
          <h4>توقيع العامل</h4>
          <div class="name">{{ $case->full_name }} — جواز: {{ $case->passport_number }}</div>
          <img src="{{ $evaluation->worker_signature }}">
        </td>
      </tr>
    </table>

    <div class="footer-note">
      هذا النموذج يُقفل بعد التوقيعين — أي تعديل بعدهما غير ممكن.
    </div>
  </div>
</body>
</html>
