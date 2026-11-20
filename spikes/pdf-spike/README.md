# Step-0 PDF Spike — Browsershot vs mPDF

Result of the Step-0 spike: choosing the Arabic PDF engine for the worker
testing app. Both candidates generated the same sample (Arabic tashkeel text,
13-criteria table, two embedded signatures) and were compared on rendering,
text layer, size, and integration effort.

## Verdict: `spatie/browsershot` (spike winner, now locked in AGENTS.md)

## What was tested

- Template: `templates/pdf.blade.php` — RTL Blade rendering the full 7-page-style
  evaluation layout (header, worker info, 13 fixed criteria table, tashkeel
  stress sentence, two base64 PNG signatures, footer).
- Data: `data/sample.php` — realistic Arabic sample values.
- Drivers:
  - `spatie/browsershot ^5.5` (Chromium headless inside the Sail container)
  - `mpdf/mpdf ^8.3` (Noto Naskh Arabic, `useOTL = 14`, RTL directionality)
- Verification: `pdftotext` (layout + `-raw` modes) and page/size checks.

## Findings

| Criterion | Browsershot | mPDF |
| --- | --- | --- |
| Arabic tashkeel rendering | Correct, full diacritics, correct connecting forms | Correct, full diacritics |
| RTL reading order | Correct | Correct |
| Table + embedded signature images | Correct | Correct |
| Text layer (copy/paste/search) | Clean, correct reading order on first pass | Requires `pdftotext -raw`; layout mode scrambles diacritic clustering order |
| Output size (sample) | ~125 KB | ~53 KB |
| Pages | 2 | 2 |
| Setup effort | Needs Chrome + `no-sandbox` args inside container | Font registration + `useOTL`/`useKashida` tuning |
| HTML/CSS fidelity | Native (it IS a browser) | Approximates CSS; pagination control is fiddly |

Notes:

- The only text-layer anomaly seen at first (`وَالدَّمِّ` appearing "missing")
  was confirmed to be a layout-mode extraction ordering artifact, not content
  loss — the string is present in `-raw` output.
- Both engines shared a minor visual bidi quirk in the date line
  (`PM 01:30 2026-11-19`); fix is a one-line `dir="ltr"` span in the final
  template, engine-independent.
- mPDF's smaller file size is irrelevant: PRD §13 caps the final PDF at 10 MB,
  and both engines are orders of magnitude under it.
- The app's PDF will be the final, richest UI output; rendering the actual Blade
  template through the same HTML/CSS stack the UI already uses keeps visual
  parity and makes the 7-page layout much easier to maintain.

## How to run

Requires the Sail stack running (Chrome + Noto fonts live in the container):

```bash
./vendor/bin/sail up -d
./vendor/bin/sail php spikes/pdf-spike/bin/generate.php
```

Output lands in `spikes/pdf-spike/output/` (`browsershot.pdf`, plus the shared
`rendered.html` for inspection).

Verify the text layer:

```bash
pdftotext spikes/pdf-spike/output/browsershot.pdf - | head
```

## Follow-ups (carried into T4.1)

- Add `dir="ltr"` span for the datetime value (bidi order).
- Fix applied in spike: footer wording "يُقفل" (was a diacritic typo "يوقَل").
- Final PDF must follow PRD §13: 7 pages, `TicketNo_Date_vN.pdf`, ≤ 10 MB,
  generated only when all 6 documents are complete.
