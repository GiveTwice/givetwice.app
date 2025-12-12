<?php

namespace Tests\Feature;

use App\Actions\FetchGiftDetailsAction;
use App\Events\GiftCreated;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GiftCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_gift_emits_gift_created_event(): void
    {
        Event::fake([GiftCreated::class]);

        $user = User::factory()->create();

        $gift = Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/123',
        ]);

        Event::assertDispatched(GiftCreated::class, function ($event) use ($gift) {
            return $event->gift->id === $gift->id;
        });
    }

    public function test_gift_created_event_dispatches_fetch_gift_details_action(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $gift = Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/456',
        ]);

        Queue::assertPushed(FetchGiftDetailsAction::class, function ($job) use ($gift) {
            return $job->gift->id === $gift->id;
        });
    }

    public function test_fetch_gift_details_action_is_dispatched_to_fetch_queue(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/789',
        ]);

        Queue::assertPushedOn('fetch', FetchGiftDetailsAction::class);
    }

    public function test_gift_is_created_with_pending_fetch_status_by_default(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $gift = Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/abc',
        ]);

        $this->assertEquals('pending', $gift->fetch_status);
        $this->assertTrue($gift->isPending());
    }
}
