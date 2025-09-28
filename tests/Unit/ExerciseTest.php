<?php

namespace Tests\Unit;

use App\Domains\Planning\Models\Exercise;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_an_exercise()
    {
        $exercise = Exercise::create([
            'user_id' => $this->user->id,
            'title' => 'Test Exercise',
            'description' => 'Test Description',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
            'status' => Exercise::STATUS_PLANNED,
        ]);

        $this->assertDatabaseHas('exercises', [
            'id' => $exercise->id,
            'user_id' => $this->user->id,
            'title' => 'Test Exercise',
            'type' => Exercise::TYPE_TECHNIQUE,
            'planned_duration' => 30,
            'status' => Exercise::STATUS_PLANNED,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $exercise = Exercise::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $exercise->user);
        $this->assertEquals($this->user->id, $exercise->user->id);
    }

    /** @test */
    public function it_can_get_type_label()
    {
        $exercise = Exercise::factory()->create(['type' => Exercise::TYPE_TECHNIQUE]);

        $this->assertEquals('Техника', $exercise->type_label);
    }

    /** @test */
    public function it_can_get_status_label()
    {
        $exercise = Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);

        $this->assertEquals('Запланировано', $exercise->status_label);
    }

    /** @test */
    public function it_can_get_type_icon()
    {
        $exercise = Exercise::factory()->create(['type' => Exercise::TYPE_TECHNIQUE]);

        $this->assertEquals('⚡', $exercise->type_icon);
    }

    /** @test */
    public function it_can_check_if_can_start()
    {
        $exercise = Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);

        $this->assertTrue($exercise->canStart());

        $exercise->update(['status' => Exercise::STATUS_ACTIVE]);
        $this->assertFalse($exercise->canStart());
    }

    /** @test */
    public function it_can_check_if_can_pause()
    {
        $exercise = Exercise::factory()->create(['status' => Exercise::STATUS_ACTIVE]);

        $this->assertTrue($exercise->canPause());

        $exercise->update(['status' => Exercise::STATUS_PLANNED]);
        $this->assertFalse($exercise->canPause());
    }

    /** @test */
    public function it_can_check_if_can_complete()
    {
        $exercise = Exercise::factory()->create(['status' => Exercise::STATUS_ACTIVE]);

        $this->assertTrue($exercise->canComplete());

        $exercise->update(['status' => Exercise::STATUS_PAUSED]);
        $this->assertTrue($exercise->canComplete());

        $exercise->update(['status' => Exercise::STATUS_PLANNED]);
        $this->assertFalse($exercise->canComplete());
    }

    /** @test */
    public function it_can_check_if_can_cancel()
    {
        $exercise = Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);

        $this->assertTrue($exercise->canCancel());

        $exercise->update(['status' => Exercise::STATUS_ACTIVE]);
        $this->assertTrue($exercise->canCancel());

        $exercise->update(['status' => Exercise::STATUS_COMPLETED]);
        $this->assertFalse($exercise->canCancel());
    }

    /** @test */
    public function it_can_scope_for_user()
    {
        $user2 = User::factory()->create();
        
        Exercise::factory()->create(['user_id' => $this->user->id]);
        Exercise::factory()->create(['user_id' => $user2->id]);

        $exercises = Exercise::forUser($this->user->id)->get();

        $this->assertCount(1, $exercises);
        $this->assertEquals($this->user->id, $exercises->first()->user_id);
    }

    /** @test */
    public function it_can_scope_with_status()
    {
        Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);
        Exercise::factory()->create(['status' => Exercise::STATUS_ACTIVE]);

        $plannedExercises = Exercise::withStatus(Exercise::STATUS_PLANNED)->get();
        $activeExercises = Exercise::withStatus(Exercise::STATUS_ACTIVE)->get();

        $this->assertCount(1, $plannedExercises);
        $this->assertCount(1, $activeExercises);
    }

    /** @test */
    public function it_can_scope_of_type()
    {
        Exercise::factory()->create(['type' => Exercise::TYPE_TECHNIQUE]);
        Exercise::factory()->create(['type' => Exercise::TYPE_REPERTOIRE]);

        $techniqueExercises = Exercise::ofType(Exercise::TYPE_TECHNIQUE)->get();
        $repertoireExercises = Exercise::ofType(Exercise::TYPE_REPERTOIRE)->get();

        $this->assertCount(1, $techniqueExercises);
        $this->assertCount(1, $repertoireExercises);
    }

    /** @test */
    public function it_can_scope_planned()
    {
        Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);
        Exercise::factory()->create(['status' => Exercise::STATUS_ACTIVE]);

        $plannedExercises = Exercise::planned()->get();

        $this->assertCount(1, $plannedExercises);
        $this->assertEquals(Exercise::STATUS_PLANNED, $plannedExercises->first()->status);
    }

    /** @test */
    public function it_can_scope_active()
    {
        Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);
        Exercise::factory()->create(['status' => Exercise::STATUS_ACTIVE]);

        $activeExercises = Exercise::active()->get();

        $this->assertCount(1, $activeExercises);
        $this->assertEquals(Exercise::STATUS_ACTIVE, $activeExercises->first()->status);
    }

    /** @test */
    public function it_can_scope_completed()
    {
        Exercise::factory()->create(['status' => Exercise::STATUS_PLANNED]);
        Exercise::factory()->create(['status' => Exercise::STATUS_COMPLETED]);

        $completedExercises = Exercise::completed()->get();

        $this->assertCount(1, $completedExercises);
        $this->assertEquals(Exercise::STATUS_COMPLETED, $completedExercises->first()->status);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $exercise = Exercise::factory()->create([
            'planned_duration' => '30',
            'actual_duration' => '25',
            'metadata' => ['key' => 'value'],
        ]);

        $this->assertIsInt($exercise->planned_duration);
        $this->assertIsInt($exercise->actual_duration);
        $this->assertIsArray($exercise->metadata);
        $this->assertEquals(['key' => 'value'], $exercise->metadata);
    }
}