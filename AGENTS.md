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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
