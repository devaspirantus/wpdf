<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\Reception;
use App\Livewire\ReviewStation;
use App\Models\CaseReturn;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ReturnedCasesUxTest extends TestCase
{
    use RefreshDatabase;

    private User $reviewer;

    private function asRole(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user);

        return $user;
    }

    private function reviewer(): User
    {
        return $this->reviewer ??= User::factory()->create([
            'role' => 'review',
            'name' => 'أحمد المراجع',
        ]);
    }

    private function returnedCase(Station $returnedTo, array $overrides = []): WorkerCase
    {
        $case = WorkerCase::create(array_merge([
            'full_name' => 'سالم محمد',
            'passport_number' => 'P5550001',
            'ticket_number' => 'T-7001',
            'status' => CaseStatus::returned,
        ], $overrides));

        CaseReturn::create([
            'case_id' => $case->id,
            'returned_to_station' => $returnedTo,
            'note' => 'مستند الجواز غير واضح',
            'returned_by' => $this->reviewer()->id,
        ]);

        return $case;
    }

    public function test_theory_dashboard_shows_returned_case_with_badge(): void
    {
        $this->asRole('theory');
        $case = $this->returnedCase(Station::theory);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('سالم محمد', escape: false)
            ->assertSee('مُرجع من رفع البيانات', escape: false)
            ->assertSee('ملاحظة الإرجاع: مستند الجواز غير واضح', escape: false)
            ->assertSee('بواسطة: أحمد المراجع', escape: false);
    }

    public function test_practical_dashboard_shows_returned_case_with_badge(): void
    {
        $this->asRole('practical');
        $this->returnedCase(Station::practical);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('سالم محمد', escape: false)
            ->assertSee('مُرجع من رفع البيانات', escape: false)
            ->assertSee('ملاحظة الإرجاع: مستند الجواز غير واضح', escape: false)
            ->assertSee('بواسطة: أحمد المراجع', escape: false);
    }

    public function test_returned_to_theory_is_hidden_from_practical_dashboard_and_vice_versa(): void
    {
        $this->asRole('practical');
        $this->returnedCase(Station::theory, ['ticket_number' => 'T-7001']);

        $this->get(route('dashboard'))->assertDontSee('T-7001', escape: false);

        $this->returnedCase(Station::practical, [
            'ticket_number' => 'T-7002',
            'full_name' => 'خالد سعد',
            'passport_number' => 'P5550002',
        ]);

        $this->get(route('dashboard'))
            ->assertSee('T-7002', escape: false)
            ->assertDontSee('T-7001', escape: false);

        $this->asRole('theory');

        $this->get(route('dashboard'))
            ->assertSee('T-7001', escape: false)
            ->assertDontSee('T-7002', escape: false);
    }

    public function test_reception_lists_returned_cases(): void
    {
        $this->asRole('reception');
        $case = $this->returnedCase(Station::reception);

        $this->get(route('reception'))
            ->assertOk()
            ->assertSee('ملفات مَرجعة إلى الاستقبال', escape: false)
            ->assertSee('سالم محمد', escape: false)
            ->assertSee('T-7001', escape: false)
            ->assertSee('مُرجع من رفع البيانات', escape: false)
            ->assertSee('ملاحظة الإرجاع: مستند الجواز غير واضح', escape: false)
            ->assertSee('بواسطة: أحمد المراجع', escape: false)
            ->assertSee('فتح الملف', escape: false);
    }

    public function test_reception_does_not_list_cases_returned_to_other_stations(): void
    {
        $this->asRole('reception');
        $this->returnedCase(Station::theory, ['ticket_number' => 'T-7001']);

        $this->get(route('reception'))
            ->assertOk()
            ->assertSee('لا توجد ملفات مَرجعة إلى الاستقبال حاليًا.', escape: false)
            ->assertDontSee('T-7001', escape: false);
    }

    public function test_reception_search_filters_returned_cases(): void
    {
        $this->asRole('reception');

        $this->returnedCase(Station::reception, ['ticket_number' => 'T-7001']);
        $this->returnedCase(Station::reception, [
            'ticket_number' => 'T-7002',
            'full_name' => 'خالد سعد',
            'passport_number' => 'P5550003',
        ]);

        Livewire::test(Reception::class)
            ->set('search', 'T-7002')
            ->assertSee('T-7002', escape: false)
            ->assertDontSee('T-7001', escape: false);
    }

    public function test_selecting_returned_case_shows_return_note_and_resubmit_button(): void
    {
        $this->asRole('reception');
        $case = $this->returnedCase(Station::reception);

        Livewire::test(Reception::class)
            ->call('selectCase', $case->id)
            ->assertSet('isReturned', true)
            ->assertSet('returnNote', 'مستند الجواز غير واضح')
            ->assertSee('تم إرجاع هذا الملف من محطة المراجعة', escape: false)
            ->assertSee('إرجاع إلى رفع البيانات', escape: false);
    }

    public function test_resubmit_sends_case_back_to_review(): void
    {
        $this->asRole('reception');
        $case = $this->returnedCase(Station::reception);
        $store = app(StoreCaseDocument::class);
        $store->handle($case, Station::reception, 'worker_photo', UploadedFile::fake()->image('worker.png'), auth()->id());
        $store->handle($case, Station::reception, 'passport', UploadedFile::fake()->image('passport.png'), auth()->id());
        $store->handle($case, Station::reception, 'ticket', UploadedFile::fake()->image('ticket.png'), auth()->id());

        Livewire::test(Reception::class)
            ->call('selectCase', $case->id)
            ->call('resubmit')
            ->assertSet('advancedTo', true)
            ->assertSet('isReturned', false);

        $this->assertSame(CaseStatus::review, $case->fresh()->status);
    }

    public function test_case_returned_to_theory_has_no_resubmit_button_at_reception(): void
    {
        $this->asRole('reception');
        $case = $this->returnedCase(Station::theory);

        Livewire::test(Reception::class)
            ->call('selectCase', $case->id)
            ->assertSet('isReturned', false)
            ->assertDontSee('إرجاع إلى رفع البيانات', escape: false);
    }

    public function test_review_list_shows_resubmitted_badge_for_case_with_returns(): void
    {
        $this->asRole('reception');
        $case = $this->returnedCase(Station::theory, ['status' => CaseStatus::review]);

        Livewire::test(ReviewStation::class)
            ->assertSee('T-7001', escape: false)
            ->assertSee('عاد بعد التصحيح', escape: false);
    }

    public function test_review_detail_shows_resubmitted_badge_for_case_with_returns(): void
    {
        $this->asRole('reception');
        $case = $this->returnedCase(Station::practical, ['status' => CaseStatus::review]);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('عاد بعد التصحيح', escape: false);
    }

    public function test_review_detail_shows_no_badge_for_case_without_returns(): void
    {
        $this->asRole('reception');
        $case = WorkerCase::create([
            'full_name' => 'فهد سالم',
            'passport_number' => 'P5550009',
            'ticket_number' => 'T-7010',
            'status' => CaseStatus::review,
        ]);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertDontSee('عاد بعد التصحيح', escape: false);
    }
}
