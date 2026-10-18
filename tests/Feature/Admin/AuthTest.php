<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('name="passcode"', false);
    }

    public function test_owner_can_sign_in_with_passcode(): void
    {
        $owner = User::factory()->owner()->create(['password' => 'secret-passcode']);

        $this->post(route('admin.login'), ['passcode' => 'secret-passcode'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_wrong_passcode_is_rejected_with_a_generic_message(): void
    {
        User::factory()->owner()->create(['password' => 'right-passcode']);

        $this->from(route('admin.login'))
            ->post(route('admin.login'), ['passcode' => 'wrong-passcode'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('passcode');

        $this->assertGuest();
    }

    public function test_a_non_owner_passcode_cannot_sign_in(): void
    {
        User::factory()->editor()->create(['password' => 'editor-passcode']);

        $this->post(route('admin.login'), ['passcode' => 'editor-passcode'])
            ->assertSessionHasErrors('passcode');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        User::factory()->owner()->create(['password' => 'right-passcode']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login'), ['passcode' => 'wrong-passcode']);
        }

        $this->post(route('admin.login'), ['passcode' => 'right-passcode'])
            ->assertSessionHasErrors('passcode');

        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_the_login_screen(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_a_non_owner_authenticated_user_is_refused(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_owner_can_reach_the_dashboard(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Overview');
    }

    public function test_logout_ends_the_session(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}
