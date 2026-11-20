# Worker Testing App — MVP

## What this is
A 4-station worker testing flow for employment approval:
Reception → Theory Lab → Practical Lab → Data Review.
The ONLY output is ONE Arabic PDF per worker, delivered to management.
MVP scope only — do not build deferred features.

## Source of truth
- docs/PRD.md        → business requirements & decisions.
- docs/PLAN.md       → task list. Do ONE task per session. Tick [x] when done.
- docs/DATA_MODEL.md → table/column names. NEVER invent new names.

## Stack (locked — do not change)
- Architecture: MVC (Model–View–Controller)
- Laravel 13 · PHP 8.3+
- Livewire 4 · Alpine.js 3 (bundled with Livewire) · Tailwind CSS 4 (RTL)
- Dev environment: Laravel Sail (Docker)
- Auth: Laravel Breeze
- Database: MySQL 8 (via Sail)
- Queue: database driver
- Tests: Pest · Formatter: Pint
- Arabic PDF library: LOCK HERE after the Step-0 spike (Browsershot or mPDF).

## Architecture (MVC)
- **Models** (`app/Models/`): data & relationships only (WorkerCase, Document,
  Evaluation, StationVisit, CaseReturn, User). No business logic here.
- **Views** (`resources/views/`): Blade + Livewire components. UI only — Arabic RTL.
  No queries or logic inside views.
- **Controllers** (`app/Http/Controllers/`): thin. Route the request, delegate to
  Actions/Services, return the response. No business logic in controllers.
- **Business logic** lives in Actions/Services (e.g., CaseStateMachine,
  GeneratePdfAction, ReturnCaseAction). Controllers call them.
- Validation goes in Form Requests, never inside controllers.

## Language
Code, commits, comments: English.
UI text + PDF output: Arabic (RTL).

## Business rules
- Thin controllers. Validation in Form Requests. Logic in Actions/Services.
- Case status changes ONLY through a CaseStateMachine service.
- ticket_number is UNIQUE. Reject duplicates with a clear Arabic message.
- Evaluation form is LOCKED after both signatures. No edits after signing.
- Return (MVP) = send whole file to ONE station with a text note.
  If returned to Practical, BOTH signatures are cleared and must be re-signed.
- No pass/fail logic. No overall score. 13 FIXED criteria, scored 0–5 each.
- Do NOT build a form editor. Criteria are fixed.

## Security & Authorization (never violate)
- Worker/passport images: private storage ONLY. Never public disk.
- Image access goes through an auth-protected, role-gated route.
- No external APIs, no cloud services, no third-party integrations.
- No image deletion in MVP.

## Station Access Control
- Each station route MUST verify the logged-in user's role matches that station.
  On mismatch → abort(403) with Arabic message: "غير مصرح لك بالدخول لهذه المحطة".
- At the END of each station, display a summary showing:
  • Current station name
  • User's role and permissions
  • List of completed documents (count + names)
  • Next station (only if authorized and complete)
- This makes permissions visible and prevents unauthorized access.

## Station Transition Lock
- A user CANNOT move to the next station unless ALL required fields
  and documents in the current station are completed.
- The "Next Station" button is disabled until validation passes.
- Server-side validation MUST also enforce this (never trust the frontend only).
- If a user tries to skip, return them to the current station with an Arabic
  message listing the missing items.

## Out of scope — do NOT build (deferred to Phase 2)
Offline/sync queues · admin panel UI · metrics/CSV · audit viewer ·
PDF versioning UI · form editor · QR/OCR · notifications · payments ·
archiving/deletion · delivery-tracking button.

## Before finishing a task
Run `php artisan test` and `./vendor/bin/pint`. Fix all failures.
Then mark the task [x] in docs/PLAN.md.