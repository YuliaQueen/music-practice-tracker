<?php

namespace Tests\Unit;

use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Planning\Models\Template;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
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
    public function it_can_create_a_session()
    {
        $session = Session::create([
            'user_id' => $this->user->id,
            'practice_template_id' => $this->template->id,
            'title' => 'Test Session',
            'description' => 'Test Description',
            'planned_duration' => 60,
            'status' => Session::STATUS_PLANNED,
        ]);

        $this->assertDatabaseHas('practice_sessions', [
            'id' => $session->id,
            'user_id' => $this->user->id,
            'practice_template_id' => $this->template->id,
            'title' => 'Test Session',
            'planned_duration' => 60,
            'status' => Session::STATUS_PLANNED,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($this->user->id, $session->user->id);
    }

    /** @test */
    public function it_belongs_to_a_template()
    {
        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'practice_template_id' => $this->template->id,
        ]);

        $this->assertInstanceOf(Template::class, $session->template);
        $this->assertEquals($this->template->id, $session->template->id);
    }

    /** @test */
    public function it_has_many_blocks()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        $block1 = SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'sort_order' => 1,
        ]);

        $block2 = SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'sort_order' => 2,
        ]);

        $this->assertCount(2, $session->blocks);
        $this->assertInstanceOf(SessionBlock::class, $session->blocks->first());
    }

    /** @test */
    public function it_can_check_if_is_active()
    {
        $session = Session::factory()->create(['status' => Session::STATUS_ACTIVE]);

        $this->assertTrue($session->isActive());

        $session->update(['status' => Session::STATUS_PLANNED]);
        $this->assertFalse($session->isActive());
    }

    /** @test */
    public function it_can_check_if_is_completed()
    {
        $session = Session::factory()->create(['status' => Session::STATUS_COMPLETED]);

        $this->assertTrue($session->isCompleted());

        $session->update(['status' => Session::STATUS_PLANNED]);
        $this->assertFalse($session->isCompleted());
    }

    /** @test */
    public function it_can_check_if_is_planned()
    {
        $session = Session::factory()->create(['status' => Session::STATUS_PLANNED]);

        $this->assertTrue($session->isPlanned());

        $session->update(['status' => Session::STATUS_ACTIVE]);
        $this->assertFalse($session->isPlanned());
    }

    /** @test */
    public function it_can_check_if_can_be_started()
    {
        $session = Session::factory()->create(['status' => Session::STATUS_PLANNED]);

        $this->assertTrue($session->canBeStarted());

        $session->update(['status' => Session::STATUS_PAUSED]);
        $this->assertTrue($session->canBeStarted());

        $session->update(['status' => Session::STATUS_ACTIVE]);
        $this->assertFalse($session->canBeStarted());
    }

    /** @test */
    public function it_can_check_if_can_be_completed()
    {
        $session = Session::factory()->create(['status' => Session::STATUS_ACTIVE]);

        $this->assertTrue($session->canBeCompleted());

        $session->update(['status' => Session::STATUS_PAUSED]);
        $this->assertTrue($session->canBeCompleted());

        $session->update(['status' => Session::STATUS_PLANNED]);
        $this->assertFalse($session->canBeCompleted());
    }

    /** @test */
    public function it_can_get_total_blocks_duration()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'planned_duration' => 20,
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'planned_duration' => 30,
        ]);

        $this->assertEquals(50, $session->getTotalBlocksDuration());
    }

    /** @test */
    public function it_can_get_total_blocks_actual_duration()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'actual_duration' => 15,
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'actual_duration' => 25,
        ]);

        $this->assertEquals(40, $session->getTotalBlocksActualDuration());
    }

    /** @test */
    public function it_can_get_current_block()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_PLANNED,
        ]);

        $activeBlock = SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_ACTIVE,
        ]);

        $currentBlock = $session->getCurrentBlock();

        $this->assertInstanceOf(SessionBlock::class, $currentBlock);
        $this->assertEquals($activeBlock->id, $currentBlock->id);
    }

    /** @test */
    public function it_can_get_next_block()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'sort_order' => 1,
        ]);

        $nextBlock = SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_PLANNED,
            'sort_order' => 2,
        ]);

        $block = $session->getNextBlock();

        $this->assertInstanceOf(SessionBlock::class, $block);
        $this->assertEquals($nextBlock->id, $block->id);
    }

    /** @test */
    public function it_can_manage_metadata()
    {
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        $session->setMetadata('key1', 'value1');
        $session->setMetadata('key2.nested', 'value2');

        $this->assertEquals('value1', $session->getMetadata('key1'));
        $this->assertEquals('value2', $session->getMetadata('key2.nested'));
        $this->assertEquals('default', $session->getMetadata('nonexistent', 'default'));
    }

    /** @test */
    public function it_can_scope_for_user()
    {
        $user2 = User::factory()->create();

        Session::factory()->create(['user_id' => $this->user->id]);
        Session::factory()->create(['user_id' => $user2->id]);

        $sessions = Session::forUser($this->user->id)->get();

        $this->assertCount(1, $sessions);
        $this->assertEquals($this->user->id, $sessions->first()->user_id);
    }

    /** @test */
    public function it_can_scope_active()
    {
        Session::factory()->create(['status' => Session::STATUS_PLANNED]);
        Session::factory()->create(['status' => Session::STATUS_ACTIVE]);

        $activeSessions = Session::active()->get();

        $this->assertCount(1, $activeSessions);
        $this->assertEquals(Session::STATUS_ACTIVE, $activeSessions->first()->status);
    }

    /** @test */
    public function it_can_scope_completed()
    {
        Session::factory()->create(['status' => Session::STATUS_PLANNED]);
        Session::factory()->create(['status' => Session::STATUS_COMPLETED]);

        $completedSessions = Session::completed()->get();

        $this->assertCount(1, $completedSessions);
        $this->assertEquals(Session::STATUS_COMPLETED, $completedSessions->first()->status);
    }

    /** @test */
    public function it_can_scope_planned()
    {
        Session::factory()->create(['status' => Session::STATUS_PLANNED]);
        Session::factory()->create(['status' => Session::STATUS_ACTIVE]);

        $plannedSessions = Session::planned()->get();

        $this->assertCount(1, $plannedSessions);
        $this->assertEquals(Session::STATUS_PLANNED, $plannedSessions->first()->status);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $session = Session::factory()->create([
            'planned_duration' => '60',
            'actual_duration' => '55',
            'metadata' => ['key' => 'value'],
        ]);

        $this->assertIsInt($session->planned_duration);
        $this->assertIsInt($session->actual_duration);
        $this->assertIsArray($session->metadata);
        $this->assertEquals(['key' => 'value'], $session->metadata);
    }
}
