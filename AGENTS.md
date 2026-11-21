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
- Arabic PDF library: `spatie/browsershot` (locked in Step-0 spike — Arabic tashkeel + RTL verified, clean text layer).

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

## Camera fallback (capture/upload)
- The CameraCapture component offers TWO ways to provide the image:
  live camera capture, OR "رفع من الجهاز" (upload from device) as a
  fallback when the camera is unavailable/denied.
- Both paths converge on the SAME server method `saveImage(string $imageData)`
  and the SAME `StoreCaseDocument` action. Do NOT modify `StoreCaseDocument`
  or the `cases/{id}/{station}/{type}.{ext}` storage pattern.
- Server-side validation in `saveImage()` (order matters):
  1. strict data-URL regex: only `data:image/jpeg` or `data:image/png` allowed
  2. strict `base64_decode($payload, true)` — reject on failure
  3. decoded binary size <= 5 MB
  4. `getimagesizefromstring()` content sniff — decode is not enough
- Camera logic is 100% inline Alpine in the Blade. Do NOT modify
  `resources/js/app.js`.

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

## Local Dev Setup (permissions)
- The container's `sail` user (uid 1337, gid 1000) writes to bind-mounted
  files as a different uid than the host owner. If commands fail with
  "Permission denied" on `storage/logs` (or other owned-by-container files):
  1. Ensure group-write: `chmod -R g+w storage config bootstrap/cache` on host.
  2. Or one-shot from host: `docker compose exec -u root -T laravel.test \
     sh -c "chown -R 1000:1000 /var/www/html/storage /var/www/html/bootstrap/cache"`.
  3. Re-run the failing command.
- Livewire config is published at `config/livewire.php`; `payload.max_size`
  is in BYTES (currently 5MB for image uploads).

## Out of scope — do NOT build (deferred to Phase 2)
Offline/sync queues · admin panel UI · metrics/CSV · audit viewer ·
PDF versioning UI · form editor · QR/OCR · notifications · payments ·
archiving/deletion · delivery-tracking button.

## Before finishing a task
Run `php artisan test` and `./vendor/bin/pint`. Fix all failures.
Then mark the task [x] in docs/PLAN.md.

## Definition of Done (for EVERY task)
A task is NOT complete until ALL of these are true:
1. `php artisan test` passes.
2. `./vendor/bin/pint` is clean.
3. The task is marked [x] in docs/PLAN.md.
4. The work is COMMITTED as `feat(Tx.y): <description>`.
   - Commit BEFORE reporting. One task = one commit.
   - NEVER batch multiple tasks into one commit.
   - If you finish a task, commit it immediately — do not wait.

## Livewire 4 gotchas
- Event listeners use `#[On('event-name')]`. `#[Listen]` does NOT exist in
  Livewire 4 and causes "handler does not exist" errors.