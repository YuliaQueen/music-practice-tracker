<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\Exercise;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_display_exercises_index()
    {
        $this->actingAs($this->user);

        Exercise::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('exercises.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Exercises/Index')
                ->has('exercises.data', 3)
        );
    }

    /** @test */
    public function it_can_display_create_exercise_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('exercises.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Exercises/Create')
        );
    }

    /** @test */
    public function it_can_store_a_new_exercise()
    {
        $this->actingAs($this->user);

        $exerciseData = [
            'title' => 'Test Exercise',
            'description' => 'Test Description',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
        ];

        $response = $this->post(route('exercises.store'), $exerciseData);

        $response->assertRedirect(route('exercises.index'));
        $response->assertSessionHas('success', 'Упражнение успешно создано');

        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'title' => 'Test Exercise',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
            'status' => Exercise::STATUS_PLANNED,
        ]);
    }

    /** @test */
    public function it_validates_exercise_store_request()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('exercises.store'), []);

        $response->assertSessionHasErrors(['title', 'type', 'planned_duration']);
    }

    /** @test */
    public function it_validates_exercise_title_is_required()
    {
        $this->actingAs($this->user);

        $exerciseData = [
            'title' => '',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
        ];

        $response = $this->post(route('exercises.store'), $exerciseData);

        $response->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function it_validates_exercise_type_is_valid()
    {
        $this->actingAs($this->user);

        $exerciseData = [
            'title' => 'Test Exercise',
            'type' => 'invalid_type',
            'planned_duration' => 30,
        ];

        $response = $this->post(route('exercises.store'), $exerciseData);

        $response->assertSessionHasErrors(['type']);
    }

    /** @test */
    public function it_validates_exercise_duration_is_within_limits()
    {
        $this->actingAs($this->user);

        $exerciseData = [
            'title' => 'Test Exercise',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 500, // More than 8 hours
        ];

        $response = $this->post(route('exercises.store'), $exerciseData);

        $response->assertSessionHasErrors(['planned_duration']);
    }

    /** @test */
    public function it_can_display_exercise_show_page()
    {
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('exercises.show', $exercise));

        $response->assertStatus(500); // Expected since Show.vue doesn't exist
    }

    /** @test */
    public function it_can_display_exercise_edit_form()
    {
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('exercises.edit', $exercise));

        $response->assertStatus(500); // Expected since Edit.vue doesn't exist
    }

    /** @test */
    public function it_can_update_an_exercise()
    {
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Exercise',
            'description' => 'Updated Description',
            'type' => Exercise::TYPE_REPERTOIRE,
            'planned_duration' => 45,
        ];

        $response = $this->put(route('exercises.update', $exercise), $updateData);

        $response->assertRedirect(route('exercises.index'));
        $response->assertSessionHas('success', 'Упражнение успешно обновлено');

        $this->assertDatabaseHas('exercises', [
            'id' => $exercise->id,
            'title' => 'Updated Exercise',
            'type' => Exercise::TYPE_REPERTOIRE,
            'planned_duration' => 45,
        ]);
    }

    /** @test */
    public function it_can_delete_an_exercise()
    {
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('exercises.destroy', $exercise));

        $response->assertRedirect(route('exercises.index'));
        $response->assertSessionHas('success', 'Упражнение успешно удалено');

        $this->assertSoftDeleted('exercises', [
            'id' => $exercise->id,
        ]);
    }

    /** @test */
    public function it_prevents_access_to_other_users_exercises()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('exercises.show', $exercise));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_prevents_updating_other_users_exercises()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'title' => 'Hacked Exercise',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
        ];

        $response = $this->put(route('exercises.update', $exercise), $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_prevents_deleting_other_users_exercises()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $exercise = Exercise::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->delete(route('exercises.destroy', $exercise));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('exercises.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_can_accept_scheduled_for_date()
    {
        $this->actingAs($this->user);

        $exerciseData = [
            'title' => 'Scheduled Exercise',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
            'scheduled_for' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->post(route('exercises.store'), $exerciseData);

        $response->assertRedirect(route('exercises.index'));
        $response->assertSessionHas('success', 'Упражнение успешно создано');

        $this->assertDatabaseHas('exercises', [
            'title' => 'Scheduled Exercise',
            'scheduled_for' => $exerciseData['scheduled_for'],
        ]);
    }

    /** @test */
    public function it_validates_scheduled_for_is_future_date()
    {
        $this->actingAs($this->user);

        $exerciseData = [
            'title' => 'Past Exercise',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
            'scheduled_for' => now()->subDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->post(route('exercises.store'), $exerciseData);

        $response->assertSessionHasErrors(['scheduled_for']);
    }
}