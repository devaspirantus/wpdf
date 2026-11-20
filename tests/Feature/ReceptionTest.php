<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\Reception;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ReceptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private function actingAsReceptionUser(): User
    {
        $user = User::factory()->create(['role' => 'reception']);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/reception')->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_the_reception_screen(): void
    {
        $this->actingAsReceptionUser();

        $this->get('/reception')
            ->assertOk()
            ->assertSee('محطة الاستقبال')
            ->assertSee('اسم العامل')
            ->assertSee('رقم الجواز')
            ->assertSee('رقم التذكرة');
    }

    public function test_a_worker_case_is_created_on_submit(): void
    {
        $this->actingAsReceptionUser();

        Livewire::test(Reception::class)
            ->set('fullName', 'محمود علي')
            ->set('passportNumber', 'P1234567')
            ->set('ticketNumber', 'T-1001')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('worker_cases', [
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-1001',
            'status' => CaseStatus::reception->value,
        ]);

        $this->assertDatabaseCount('worker_cases', 1);
    }

    public function test_fields_are_reset_after_a_successful_creation(): void
    {
        $this->actingAsReceptionUser();

        $component = Livewire::test(Reception::class)
            ->set('fullName', 'محمود علي')
            ->set('passportNumber', 'P1234567')
            ->set('ticketNumber', 'T-1002')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertSame('', $component->instance()->fullName);
        $this->assertSame('', $component->instance()->passportNumber);
        $this->assertSame('', $component->instance()->ticketNumber);
        $this->assertTrue($component->instance()->created);
    }

    public function test_empty_fields_are_rejected(): void
    {
        $this->actingAsReceptionUser();

        Livewire::test(Reception::class)
            ->set('fullName', '')
            ->set('passportNumber', '')
            ->set('ticketNumber', '')
            ->call('create')
            ->assertHasErrors(['fullName', 'passportNumber', 'ticketNumber']);

        $this->assertDatabaseCount('worker_cases', 0);
    }

    public function test_duplicate_ticket_number_is_rejected(): void
    {
        $this->actingAsReceptionUser();

        WorkerCase::create([
            'full_name' => 'أحمد',
            'passport_number' => 'P1111111',
            'ticket_number' => 'T-DUP-1',
            'status' => CaseStatus::reception,
        ]);

        Livewire::test(Reception::class)
            ->set('fullName', 'محمد')
            ->set('passportNumber', 'P2222222')
            ->set('ticketNumber', 'T-DUP-1')
            ->call('create')
            ->assertHasErrors('ticketNumber');

        $this->assertDatabaseCount('worker_cases', 1);
    }

    public function test_the_advance_button_is_disabled_until_all_requirements_are_met(): void
    {
        $this->actingAsReceptionUser();

        $component = Livewire::test(Reception::class)
            ->set('fullName', 'محمود علي')
            ->set('passportNumber', 'P1234567')
            ->set('ticketNumber', 'T-1001')
            ->call('create');

        $component->assertSeeHtml('wire:click="advance" disabled');

        $case = WorkerCase::first();
        $store = app(StoreCaseDocument::class);

        foreach (['worker_photo', 'passport', 'ticket'] as $type) {
            $store->handle($this->case(), Station::reception, $type, UploadedFile::fake()->image($type.'.png'), $this->user->id);
            $component->dispatch('case-document-stored', caseId: $case->id);
        }

        $component->assertDontSeeHtml('wire:click="advance" disabled');
    }

    public function test_advance_moves_a_complete_case_to_theory(): void
    {
        $this->actingAsReceptionUser();

        $component = Livewire::test(Reception::class)
            ->set('fullName', 'محمود علي')
            ->set('passportNumber', 'P1234567')
            ->set('ticketNumber', 'T-1001')
            ->call('create');

        $this->storeAllReceptionDocuments(WorkerCase::first());

        $component->call('advance');

        $this->assertDatabaseHas('worker_cases', [
            'id' => WorkerCase::first()->id,
            'status' => CaseStatus::theory->value,
        ]);
        $this->assertTrue($component->instance()->advancedTo);
    }

    public function test_advance_is_blocked_server_side_and_shows_arabic_error(): void
    {
        $this->actingAsReceptionUser();

        $component = Livewire::test(Reception::class)
            ->set('fullName', 'محمود علي')
            ->set('passportNumber', 'P1234567')
            ->set('ticketNumber', 'T-1001')
            ->call('create');

        $this->storeReceptionDocument(WorkerCase::first(), 'worker_photo');

        $component->call('advance')
            ->assertSee('المحطة الاستقبال غير مكتملة: صورة جواز السفر، صورة التذكرة');

        $this->assertSame(
            ['صورة جواز السفر', 'صورة التذكرة'],
            $component->instance()->missingRequirements,
        );
        $this->assertDatabaseHas('worker_cases', ['status' => CaseStatus::reception->value]);
    }

    public function test_document_stored_event_refreshes_requirements(): void
    {
        $this->actingAsReceptionUser();

        $component = Livewire::test(Reception::class)
            ->set('fullName', 'محمود علي')
            ->set('passportNumber', 'P1234567')
            ->set('ticketNumber', 'T-1001')
            ->call('create');

        $this->assertTrue($component->instance()->hasMissing);

        $case = WorkerCase::first();

        app(StoreCaseDocument::class)->handle(
            $case,
            Station::reception,
            'worker_photo',
            UploadedFile::fake()->image('worker.png'),
            $this->user->id,
        );

        $component->dispatch('case-document-stored', caseId: $case->id);

        $met = collect($component->instance()->requirements)
            ->firstWhere('label', 'صورة العامل');

        $this->assertTrue($met['met']);
    }

    private function storeAllReceptionDocuments(WorkerCase $case): void
    {
        foreach (['worker_photo', 'passport', 'ticket'] as $type) {
            $this->storeReceptionDocument($case, $type);
        }
    }

    private function storeReceptionDocument(WorkerCase $case, string $type): void
    {
        app(StoreCaseDocument::class)->handle(
            $case,
            Station::reception,
            $type,
            UploadedFile::fake()->image($type.'.png'),
            $this->user->id,
        );
    }

    private function case(): WorkerCase
    {
        return WorkerCase::first();
    }
}
