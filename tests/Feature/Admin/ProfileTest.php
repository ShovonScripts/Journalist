<?php

namespace Tests\Feature\Admin;

use App\Models\Award;
use App\Models\CareerHistory;
use App\Models\Education;
use App\Models\JournalistProfile;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    public function test_only_one_journalist_profile_can_exist(): void
    {
        JournalistProfile::factory()->create();

        $this->expectException(RuntimeException::class);

        JournalistProfile::factory()->create();
    }

    public function test_the_profile_can_be_updated(): void
    {
        $profile = JournalistProfile::factory()->create();
        $publications = Publication::factory()->count(2)->create();

        $this->actingAs($this->owner())
            ->put(route('admin.journalist-profile.update', $profile), [
                'name' => 'Emrul Hasan Bappi',
                'title' => 'Investigative Journalist',
                'short_bio' => 'Reports on power and accountability.',
                'long_bio' => 'A longer biography.',
                'beats' => 'Politics, Human rights, Politics',
                'social' => ['x' => 'https://x.com/ehb', 'email' => 'ehb@example.com'],
                'publications' => $publications->pluck('id')->all(),
            ])
            ->assertRedirect(route('admin.journalist-profile.edit', $profile));

        $profile->refresh();

        $this->assertSame('Emrul Hasan Bappi', $profile->name);
        $this->assertSame(['Politics', 'Human rights'], $profile->beats());
        $this->assertSame('https://x.com/ehb', $profile->social()['x']);
        $this->assertSame('ehb@example.com', $profile->social()['email']);
        $this->assertCount(2, $profile->publications);
    }

    public function test_sub_resources_attach_to_the_current_profile(): void
    {
        $owner = $this->owner();
        $profile = JournalistProfile::factory()->create();

        $this->actingAs($owner)->post(route('admin.career-history.store'), [
            'role' => 'Staff Reporter',
            'organization' => 'The Daily Star',
            'start_date' => '2018-01-01',
            'end_date' => null,
            'description' => 'Covered the courts beat.',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('admin.education.store'), [
            'institution' => 'University of Dhaka',
            'program' => 'BA Journalism',
            'start_date' => '2012-01-01',
            'end_date' => '2016-01-01',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('admin.awards.store'), [
            'title' => 'Best Investigative Report',
            'awarding_body' => 'Press Council',
            'year' => 2023,
        ])->assertRedirect();

        $this->assertSame($profile->id, CareerHistory::query()->firstOrFail()->journalist_profile_id);
        $this->assertSame($profile->id, Education::query()->firstOrFail()->journalist_profile_id);
        $this->assertSame($profile->id, Award::query()->firstOrFail()->journalist_profile_id);
    }

    public function test_an_invalid_date_range_is_rejected(): void
    {
        JournalistProfile::factory()->create();

        $this->actingAs($this->owner())
            ->post(route('admin.education.store'), [
                'institution' => 'University of Dhaka',
                'start_date' => '2020-01-01',
                'end_date' => '2019-01-01',
            ])
            ->assertSessionHasErrors('end_date');
    }

    public function test_reorder_persists_positions(): void
    {
        $profile = JournalistProfile::factory()->create();
        $entries = CareerHistory::factory()->count(3)->create(['journalist_profile_id' => $profile->id]);
        $ordered = $entries->pluck('id')->reverse()->values()->all();

        $this->actingAs($this->owner())
            ->postJson(route('admin.career-history.reorder'), ['ids' => $ordered])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame($ordered[0], CareerHistory::query()->orderBy('sort_order')->first()->id);
        $this->assertSame(1, CareerHistory::query()->find($ordered[0])->sort_order);
    }

    public function test_the_passcode_change_requires_the_current_passcode(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->put(route('admin.profile.passcode'), [
                'current_passcode' => 'wrong',
                'passcode' => 'a-new-secret-passphrase',
                'passcode_confirmation' => 'a-new-secret-passphrase',
            ])
            ->assertSessionHasErrors('current_passcode');

        $this->assertTrue(Hash::check('password', $owner->fresh()->password));
    }

    public function test_the_passcode_can_be_changed_and_is_logged(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->put(route('admin.profile.passcode'), [
                'current_passcode' => 'password',
                'passcode' => 'a-new-secret-passphrase',
                'passcode_confirmation' => 'a-new-secret-passphrase',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('a-new-secret-passphrase', $owner->fresh()->password));
        $this->assertDatabaseHas('activity_logs', ['action' => 'profile.passcode_changed', 'user_id' => $owner->id]);
    }

    public function test_the_account_can_be_updated(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->put(route('admin.profile.update'), [
                'name' => 'Emrul Hasan Bappi',
                'email' => 'new@journalist.local',
            ])
            ->assertRedirect();

        $this->assertSame('new@journalist.local', $owner->fresh()->email);
    }
}
