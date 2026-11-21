<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LogoutRegressionTest extends TestCase
{
    public function test_logout_post_logs_out(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('logout'));
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_logout_form_present_on_dashboard(): void
    {
        $user = User::factory()->create();
        $content = $this->actingAs($user)->get(route('dashboard'))->getContent();
        $this->assertStringContainsString('action="'.route('logout').'"', $content);
    }

    public function test_livewire_scripts_and_alpine_bootstrap_rendered(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard'));
        $content = $response->getContent();
        $this->assertStringContainsString('csrf-token', $content);
        $this->assertStringContainsString('/build/assets/', $content);
        $this->assertStringContainsString('livewire', strtolower($content));
    }
}
