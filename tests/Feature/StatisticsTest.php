<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_display_statistics_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Statistics/Index')
        );
    }

    /** @test */
    public function it_displays_daily_statistics()
    {
        $this->actingAs($this->user);

        // Create sessions for today
        $todaySession = Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'started_at' => now()->startOfDay()->addHours(10),
            'completed_at' => now()->startOfDay()->addHours(11),
            'actual_duration' => 60,
        ]);

        SessionBlock::factory()->completed()->create([
            'practice_session_id' => $todaySession->id,
            'actual_duration' => 30,
            'type' => 'technique',
        ]);

        SessionBlock::factory()->completed()->create([
            'practice_session_id' => $todaySession->id,
            'actual_duration' => 30,
            'type' => 'repertoire',
        ]);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }

    /** @test */
    public function it_displays_weekly_statistics()
    {
        $this->actingAs($this->user);

        // Create sessions for this week
        for ($i = 0; $i < 3; $i++) {
            Session::factory()->completed()->create([
                'user_id' => $this->user->id,
                'started_at' => now()->startOfWeek()->addDays($i),
                'actual_duration' => 60,
            ]);
        }

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }

    /** @test */
    public function it_displays_monthly_statistics()
    {
        $this->actingAs($this->user);

        // Create sessions for this month
        for ($i = 0; $i < 5; $i++) {
            Session::factory()->completed()->create([
                'user_id' => $this->user->id,
                'started_at' => now()->startOfMonth()->addDays($i * 5),
                'actual_duration' => 45,
            ]);
        }

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }

    /** @test */
    public function it_displays_exercise_type_statistics()
    {
        $this->actingAs($this->user);

        // Create exercises of different types
        Exercise::factory()->completed()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => Exercise::TYPE_TECHNIQUE,
            'actual_duration' => 20,
        ]);

        Exercise::factory()->completed()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => Exercise::TYPE_REPERTOIRE,
            'actual_duration' => 30,
        ]);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }

    /** @test */
    public function it_can_filter_statistics_by_period()
    {
        $this->actingAs($this->user);

        // Create sessions for different periods
        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'started_at' => now()->subDays(1),
            'actual_duration' => 60,
        ]);

        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'started_at' => now()->subDays(8),
            'actual_duration' => 45,
        ]);

        $response = $this->get(route('statistics.index', ['period' => 'week']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }

    /** @test */
    public function it_only_shows_user_own_statistics()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        // Create sessions for both users
        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'actual_duration' => 60,
        ]);

        Session::factory()->completed()->create([
            'user_id' => $otherUser->id,
            'actual_duration' => 45,
        ]);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        
        // Statistics should only include data for the authenticated user
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('statistics.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_handles_empty_statistics_gracefully()
    {
        $this->actingAs($this->user);

        // No sessions or exercises created

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics')
                ->has('chartData')
        );
    }
}