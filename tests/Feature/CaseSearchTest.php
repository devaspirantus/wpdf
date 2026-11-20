<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Livewire\CaseSearch;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CaseSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private function actingAsTheoryUser(): User
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/search')->assertRedirect('/login');
    }

    public function test_the_search_screen_displays(): void
    {
        $this->actingAsTheoryUser();

        $this->get('/search')
            ->assertOk()
            ->assertSee('البحث عن ملف العامل')
            ->assertSee('رقم التذكرة');
    }

    public function test_an_existing_ticket_shows_the_case_summary(): void
    {
        $this->actingAsTheoryUser();

        WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-1001',
            'status' => CaseStatus::theory,
        ]);

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', 'T-1001')
            ->call('search')
            ->assertHasNoErrors()
            ->assertSee('محمود علي')
            ->assertSee('P1234567')
            ->assertSee('T-1001')
            ->assertSee('المختبر النظري');
    }

    public function test_search_trims_the_ticket_number(): void
    {
        $this->actingAsTheoryUser();

        WorkerCase::create([
            'full_name' => 'خالد سمير',
            'passport_number' => 'P7654321',
            'ticket_number' => 'T-2002',
            'status' => CaseStatus::theory,
        ]);

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', '  T-2002  ')
            ->call('search')
            ->assertHasNoErrors()
            ->assertSee('T-2002');
    }

    public function test_a_nonexistent_ticket_shows_an_error(): void
    {
        $this->actingAsTheoryUser();

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', 'T-9999')
            ->call('search')
            ->assertSee('لم يتم العثور على ملف برقم التذكرة هذا');
    }

    public function test_an_empty_ticket_is_rejected(): void
    {
        $this->actingAsTheoryUser();

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', '')
            ->call('search')
            ->assertHasErrors(['ticketNumber' => 'required']);
    }
}
