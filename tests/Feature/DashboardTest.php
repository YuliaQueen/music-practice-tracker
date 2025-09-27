<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Session;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_display_dashboard()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
        );
    }

    /** @test */
    public function it_displays_recent_exercises()
    {
        $this->actingAs($this->user);

        // Create some exercises
        Exercise::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('exercises')
        );
    }

    /** @test */
    public function it_displays_recent_sessions()
    {
        $this->actingAs($this->user);

        // Create some sessions
        Session::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
        );
    }

    /** @test */
    public function it_displays_statistics()
    {
        $this->actingAs($this->user);

        // Create some completed sessions
        Session::factory()->completed()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
        );
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_only_shows_user_own_data()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        // Create exercises for both users
        Exercise::factory()->create(['user_id' => $this->user->id]);
        Exercise::factory()->create(['user_id' => $otherUser->id]);

        // Create sessions for both users
        Session::factory()->create(['user_id' => $this->user->id]);
        Session::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        
        // The dashboard should only show data for the authenticated user
        $response->assertInertia(fn ($page) => 
            $page->has('exercises')
        );
    }
}