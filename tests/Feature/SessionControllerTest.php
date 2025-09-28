<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Planning\Models\Template;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->template = Template::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_can_display_sessions_index()
    {
        $this->actingAs($this->user);

        Session::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('sessions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Sessions/Index')
                ->has('sessions.data', 3)
        );
    }

    /** @test */
    public function it_can_display_create_session_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('sessions.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Sessions/Create')
                ->has('templates')
                ->has('previousExercises')
        );
    }

    /** @test */
    public function it_can_store_a_new_session()
    {
        $this->actingAs($this->user);

        $sessionData = [
            'title' => 'Test Session',
            'description' => 'Test Description',
            'template_id' => $this->template->id,
            'blocks' => [
                [
                    'title' => 'Block 1',
                    'description' => 'Block 1 Description',
                    'duration' => 20,
                    'type' => 'technique',
                ],
                [
                    'title' => 'Block 2',
                    'description' => 'Block 2 Description',
                    'duration' => 30,
                    'type' => 'repertoire',
                ],
            ],
        ];

        $response = $this->post(route('sessions.store'), $sessionData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Сессия создана успешно!');

        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $this->user->id,
            'practice_template_id' => $this->template->id,
            'title' => 'Test Session',
            'planned_duration' => 50, // 20 + 30
            'status' => Session::STATUS_PLANNED,
        ]);

        $this->assertDatabaseHas('practice_session_blocks', [
            'title' => 'Block 1',
            'planned_duration' => 20,
            'type' => 'technique',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('practice_session_blocks', [
            'title' => 'Block 2',
            'planned_duration' => 30,
            'type' => 'repertoire',
            'sort_order' => 2,
        ]);
    }

    /** @test */
    public function it_validates_session_store_request()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('sessions.store'), []);

        $response->assertSessionHasErrors(['title', 'blocks']);
    }

    /** @test */
    public function it_validates_session_blocks_are_required()
    {
        $this->actingAs($this->user);

        $sessionData = [
            'title' => 'Test Session',
            'blocks' => [],
        ];

        $response = $this->post(route('sessions.store'), $sessionData);

        $response->assertSessionHasErrors(['blocks']);
    }

    /** @test */
    public function it_validates_session_blocks_have_required_fields()
    {
        $this->actingAs($this->user);

        $sessionData = [
            'title' => 'Test Session',
            'blocks' => [
                [
                    'title' => '',
                    'duration' => 20,
                    'type' => 'technique',
                ],
            ],
        ];

        $response = $this->post(route('sessions.store'), $sessionData);

        $response->assertSessionHasErrors(['blocks.0.title']);
    }

    /** @test */
    public function it_can_display_session_show_page()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create(['user_id' => $this->user->id]);
        SessionBlock::factory()->count(2)->create(['practice_session_id' => $session->id]);

        $response = $this->get(route('sessions.show', $session));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Sessions/Show')
                ->has('session')
        );
    }

    /** @test */
    public function it_can_start_a_session()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_PLANNED,
        ]);

        $response = $this->post(route('sessions.start', $session));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Сессия запущена!');

        $this->assertDatabaseHas('practice_sessions', [
            'id' => $session->id,
            'status' => Session::STATUS_ACTIVE,
        ]);

        $this->assertNotNull($session->fresh()->started_at);
    }

    /** @test */
    public function it_can_pause_a_session()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_ACTIVE,
        ]);

        $response = $this->post(route('sessions.pause', $session));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Сессия приостановлена');

        $this->assertDatabaseHas('practice_sessions', [
            'id' => $session->id,
            'status' => Session::STATUS_PAUSED,
        ]);
    }

    /** @test */
    public function it_can_complete_a_session()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_ACTIVE,
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'actual_duration' => 30,
        ]);

        $response = $this->post(route('sessions.complete', $session));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Сессия завершена!');

        $this->assertDatabaseHas('practice_sessions', [
            'id' => $session->id,
            'status' => Session::STATUS_COMPLETED,
            'actual_duration' => 30,
        ]);

        $this->assertNotNull($session->fresh()->completed_at);
    }

    /** @test */
    public function it_can_update_a_session_block()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create(['user_id' => $this->user->id]);
        $block = SessionBlock::factory()->create(['practice_session_id' => $session->id]);

        $updateData = [
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 25,
        ];

        $response = $this->patch(route('sessions.blocks.update', [$session, $block]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Блок обновлен');

        $this->assertDatabaseHas('practice_session_blocks', [
            'id' => $block->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 25,
        ]);
    }

    /** @test */
    public function it_can_delete_a_session()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create(['user_id' => $this->user->id]);
        SessionBlock::factory()->create(['practice_session_id' => $session->id]);

        $response = $this->delete(route('sessions.destroy', $session));

        $response->assertRedirect(route('sessions.index'));
        $response->assertSessionHas('success', 'Сессия успешно удалена');

        $this->assertSoftDeleted('practice_sessions', [
            'id' => $session->id,
        ]);

        $this->assertSoftDeleted('practice_session_blocks', [
            'practice_session_id' => $session->id,
        ]);
    }

    /** @test */
    public function it_prevents_access_to_other_users_sessions()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $session = Session::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('sessions.show', $session));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_prevents_starting_other_users_sessions()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Session::STATUS_PLANNED,
        ]);

        $response = $this->post(route('sessions.start', $session));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_prevents_deleting_other_users_sessions()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $session = Session::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->delete(route('sessions.destroy', $session));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('sessions.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_cannot_start_session_in_wrong_status()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_COMPLETED,
        ]);

        $response = $this->post(route('sessions.start', $session));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Сессия не может быть запущена в текущем статусе');
    }

    /** @test */
    public function it_cannot_pause_session_in_wrong_status()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_PLANNED,
        ]);

        $response = $this->post(route('sessions.pause', $session));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Можно приостановить только активную сессию');
    }

    /** @test */
    public function it_cannot_complete_session_in_wrong_status()
    {
        $this->actingAs($this->user);

        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_PLANNED,
        ]);

        $response = $this->post(route('sessions.complete', $session));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Сессия не может быть завершена в текущем статусе');
    }
}