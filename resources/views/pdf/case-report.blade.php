<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 0; }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: 'IBM Plex Sans Arabic', 'Noto Naskh Arabic', 'Noto Sans Arabic', sans-serif;
    font-size: 11.5px;
    color: #191c1e;
  }
  .page {
    position: relative;
    width: 210mm;
    min-height: 297mm;
    padding: 14mm 15mm 17mm;
    overflow: hidden;
    page-break-after: always;
  }
  .page:last-child { page-break-after: auto; }
  .brand-bar {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 8mm;
  }
  .brand-bar td {
    background: #101b30;
    color: #fff;
    height: 18mm;
    padding: 4mm 6mm;
  }
  .brand-bar td:first-child {
    width: 73%;
    border-right: 3mm solid #004328;
  }
  .brand-title { font-size: 16px; font-weight: 700; line-height: 1.35; }
  .brand-subtitle { margin-top: 1mm; font-size: 9.5px; color: #dce3ee; }
  .brand-meta { text-align: left; direction: rtl; font-size: 9.5px; line-height: 1.8; }
  .section-title {
    display: table;
    width: 100%;
    margin: 0 0 4mm;
  }
  .section-title span {
    display: table-cell;
    vertical-align: middle;
  }
  .section-title .mark {
    width: 9mm;
    height: 9mm;
    border-radius: 50%;
    background: #004328;
    color: #fff;
    text-align: center;
    font-weight: 700;
  }
  .section-title .text {
    padding-right: 3mm;
    font-size: 14px;
    font-weight: 700;
    color: #101b30;
  }
  table.card {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 0.4mm solid #c9cec9;
    border-radius: 3mm;
    overflow: hidden;
    margin-bottom: 5mm;
  }
  table.card tr + tr td { border-top: 0.25mm solid #dfe4df; }
  table.card td {
    height: 13mm;
    padding: 3mm 4mm;
    vertical-align: middle;
  }
  table.card td.label {
    width: 34%;
    background: #f2f4f6;
    color: #404942;
    font-weight: 700;
  }
  table.card td.value {
    background: #fff;
    color: #101b30;
    font-size: 12.5px;
  }
  .timeline {
    width: 100%;
    border-collapse: separate;
    border-spacing: 2mm 1.5mm;
  }
  .timeline td {
    width: 25%;
    border: 0.35mm solid #c9cec9;
    border-top: 1.2mm solid #004328;
    border-radius: 2mm;
    background: #f7f9fb;
    padding: 3mm 3mm;
    vertical-align: top;
  }
  .timeline .step {
    display: block;
    color: #004328;
    font-size: 9.5px;
    font-weight: 700;
  }
  .timeline .time {
    display: block;
    margin-top: 2mm;
    color: #191c1e;
    font-size: 10px;
  }
  .document-head {
    border-right: 3mm solid #004328;
    background: #101b30;
    color: #fff;
    padding: 4mm 5mm;
    margin-bottom: 4mm;
  }
  .document-head h1 { margin: 0; font-size: 15px; }
  .document-head p { margin: 1.5mm 0 0; color: #dce3ee; font-size: 9.5px; }
  .image-frame {
    width: 100%;
    height: 235mm;
    border: 0.45mm solid #c9cec9;
    border-radius: 3mm;
    background: #f2f4f6;
    overflow: hidden;
  }
  .doc-img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .evaluation-card {
    width: 100%;
    border-collapse: collapse;
    border: 0.35mm solid #c9cec9;
  }
  .evaluation-card td {
    height: 10mm;
    padding: 2mm 3mm;
  }
  .evaluation-card td.label {
    width: 25%;
    background: #f2f4f6;
    color: #404942;
    font-weight: 700;
  }
  table.criteria {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 3mm;
    border: 0.35mm solid #c9cec9;
  }
  table.criteria th {
    height: 9mm;
    background: #004328;
    color: #fff;
    padding: 1.5mm 2.5mm;
    font-size: 9.5px;
  }
  table.criteria th:first-child { width: 8mm; text-align: center; }
  table.criteria th:last-child { width: 22mm; text-align: center; }
  table.criteria td {
    height: 8.6mm;
    padding: 1.2mm 2.5mm;
    border-top: 0.25mm solid #dfe4df;
    vertical-align: middle;
    font-size: 8.8px;
  }
  table.criteria tr.section td {
    height: 7mm;
    background: #e7ece8;
    color: #101b30;
    font-weight: 700;
    font-size: 9.5px;
  }
  table.criteria td.number,
  table.criteria td.score { text-align: center; }
  table.criteria td.score {
    color: #004328;
    font-size: 11px;
    font-weight: 700;
  }
  .signatures {
    width: 100%;
    border-spacing: 3mm 0;
    margin-top: 3mm;
  }
  .signatures td {
    width: 50%;
    border: 0.35mm solid #c9cec9;
    border-top: 1.2mm solid #004328;
    border-radius: 2mm;
    padding: 2mm 3mm;
  }
  .signatures h4 { margin: 0; color: #101b30; font-size: 10.5px; }
  .signatures p { height: 5mm; margin: 1mm 0 0; color: #404942; font-size: 8.5px; }
  .signatures img {
    display: block;
    width: 88%;
    height: 17mm;
    margin: 1mm auto 0;
    object-fit: contain;
  }
  .locked-note {
    margin-top: 3mm;
    border-radius: 2mm;
    background: #edf7f1;
    color: #004328;
    padding: 2mm 3mm;
    text-align: center;
    font-size: 8.8px;
  }
</style>
</head>
<body>
  <div class="page">
    <table class="brand-bar">
      <tr>
        <td>
          <div class="brand-title">{{ $centerName }}</div>
          <div class="brand-subtitle">منصة اختبارات العمالة — ملف العامل والتقييم العملي</div>
        </td>
        <td>
          <div class="brand-meta">التاريخ: {{ now()->format('d/m/Y') }}<br>رقم التذكرة: {{ $case->ticket_number }}</div>
        </td>
      </tr>
    </table>

    <div class="section-title">
      <span class="mark">1</span>
      <span class="text">بيانات العامل</span>
    </div>
    <table class="card">
      <tr><td class="label">اسم العامل</td><td class="value">{{ $case->full_name }}</td></tr>
      <tr><td class="label">رقم الجواز</td><td class="value">{{ $case->passport_number }}</td></tr>
      <tr><td class="label">رقم التذكرة</td><td class="value">{{ $case->ticket_number }}</td></tr>
    </table>

    <div class="section-title">
      <span class="mark">2</span>
      <span class="text">توقيت مراحل الاختبار</span>
    </div>
    <table class="timeline">
      <tr>
        <td><span class="step">الاستقبال</span><span class="time">{{ $receptionTime }}</span></td>
        <td><span class="step">المختبر النظري</span><span class="time">{{ $theoryTime }}</span></td>
        <td><span class="step">المختبر العملي</span><span class="time">{{ $practicalTime }}</span></td>
        <td><span class="step">المراجعة والتوقيع</span><span class="time">{{ $reviewTime }}</span></td>
      </tr>
    </table>
  </div>

  @foreach ($images as $index => $image)
    <div class="page">
      <table class="brand-bar">
        <tr>
          <td>
            <div class="brand-title">{{ $centerName }}</div>
            <div class="brand-subtitle">منصة اختبارات العمالة — مستندات ملف العامل</div>
          </td>
          <td>
            <div class="brand-meta">رقم التذكرة: {{ $case->ticket_number }}<br>{{ $image['time'] }}</div>
          </td>
        </tr>
      </table>

      <div class="document-head">
        <h1>{{ $index + 1 }}. {{ $image['label'] }}</h1>
        <p>{{ $image['station'] }} — ختم المحطة والتوقيت: {{ $image['time'] }}</p>
      </div>
      <div class="image-frame">
        <img class="doc-img" src="{{ $image['src'] }}" alt="{{ $image['label'] }}">
      </div>
    </div>
  @endforeach

  <div class="page">
    <table class="brand-bar">
      <tr>
        <td>
          <div class="brand-title">{{ $centerName }}</div>
          <div class="brand-subtitle">نموذج التقييم العملي — ملف العامل رقم {{ $case->ticket_number }}</div>
        </td>
        <td>
          <div class="brand-meta">تاريخ التوقيع: {{ $evaluation->signed_at->format('d/m/Y') }}<br>رقم التذكرة: {{ $case->ticket_number }}</div>
        </td>
      </tr>
    </table>

    <table class="evaluation-card">
      <tr>
        <td class="label">اسم العامل</td><td>{{ $case->full_name }}</td>
        <td class="label">رقم الجواز</td><td>{{ $case->passport_number }}</td>
      </tr>
      <tr>
        <td class="label">المهمة العملية</td><td>{{ $evaluation->task }}</td>
        <td class="label">درجة الصعوبة</td><td>{{ $difficulty }}</td>
      </tr>
      <tr>
        <td class="label">المقيّم</td><td>{{ $evaluation->evaluator_name }}</td>
        <td class="label">رقم هوية المقيّم</td><td>{{ $evaluation->evaluator_id_number }}</td>
      </tr>
    </table>

    <table class="criteria">
      <thead>
        <tr>
          <th>م</th>
          <th>المعيار</th>
          <th>الدرجة</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sections as $sectionTitle => $criteria)
          <tr class="section"><td colspan="3">{{ $sectionTitle }}</td></tr>
          @foreach ($criteria as $key => $criterion)
            <tr>
              <td class="number">{{ $key }}</td>
              <td>{{ $criterion }}</td>
              <td class="score">{{ $evaluation->scores[$key] ?? '—' }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>

    <table class="signatures">
      <tr>
        <td>
          <h4>توقيع المقيّم</h4>
          <p>{{ $evaluation->evaluator_name }} — هوية: {{ $evaluation->evaluator_id_number }}</p>
          <img src="{{ $evaluation->evaluator_signature }}" alt="توقيع المقيّم">
        </td>
        <td>
          <h4>توقيع العامل</h4>
          <p>{{ $case->full_name }} — جواز: {{ $case->passport_number }}</p>
          <img src="{{ $evaluation->worker_signature }}" alt="توقيع العامل">
        </td>
      </tr>
    </table>

    <div class="locked-note">تم إقفال النموذج بعد اكتمال التوقيعين، ولا يمكن إجراء تعديل عليه بعد التوقيع.</div>
  </div>
</body>
</html>
