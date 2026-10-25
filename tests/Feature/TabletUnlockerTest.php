<?php

namespace Tests\Feature;

use App\Enums\TabletUnlockCondition;
use App\Models\TabletOfWisdom;
use App\Models\User;
use App\Services\TabletUnlockerService;
use Database\Factories\AccountFactory;
use Database\Factories\ImportBatchFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabletUnlockerTest extends TestCase
{
    use RefreshDatabase;

    private function tablet(?TabletUnlockCondition $condition, ?int $threshold = null, int $order = 1): TabletOfWisdom
    {
        return TabletOfWisdom::create([
            'title' => "Tablet {$order}",
            'body' => 'Wisdom.',
            'sort_order' => $order,
            'unlock_condition' => $condition,
            'unlock_threshold' => $threshold,
        ]);
    }

    public function test_a_tablet_without_a_condition_is_open_to_everyone(): void
    {
        $user = User::factory()->create();
        $this->tablet(null);

        $unlocked = (new TabletUnlockerService())->sync($user);

        $this->assertCount(1, $unlocked);
        $this->assertSame(1, $user->tabletProgress()->count());
    }

    public function test_a_tablet_stays_locked_until_its_condition_is_met(): void
    {
        $user = User::factory()->create();
        $this->tablet(TabletUnlockCondition::FirstImport);

        $this->assertCount(0, (new TabletUnlockerService())->sync($user));

        $account = AccountFactory::new()->create(['user_id' => $user->id]);
        ImportBatchFactory::new()->processed()->create(['account_id' => $account->id]);

        $this->assertCount(1, (new TabletUnlockerService())->sync($user));
    }

    public function test_a_pending_import_does_not_unlock_anything(): void
    {
        $user = User::factory()->create();
        $this->tablet(TabletUnlockCondition::FirstImport);

        $account = AccountFactory::new()->create(['user_id' => $user->id]);
        ImportBatchFactory::new()->create(['account_id' => $account->id]); // still pending

        $this->assertCount(0, (new TabletUnlockerService())->sync($user));
    }

    public function test_syncing_twice_does_not_unlock_a_tablet_twice(): void
    {
        $user = User::factory()->create();
        $this->tablet(null);

        (new TabletUnlockerService())->sync($user);
        $second = (new TabletUnlockerService())->sync($user);

        $this->assertCount(0, $second);
        $this->assertSame(1, $user->tabletProgress()->count());
    }

    public function test_the_threshold_is_respected(): void
    {
        $user = User::factory()->create();
        $this->tablet(TabletUnlockCondition::AccountsAdded, threshold: 2);

        AccountFactory::new()->create(['user_id' => $user->id]);
        $this->assertCount(0, (new TabletUnlockerService())->sync($user));

        AccountFactory::new()->create(['user_id' => $user->id]);
        $this->assertCount(1, (new TabletUnlockerService())->sync($user));
    }

    public function test_progress_of_one_user_does_not_unlock_tablets_for_another(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->tablet(TabletUnlockCondition::AccountsAdded, threshold: 1);

        AccountFactory::new()->create(['user_id' => $user->id]);

        $this->assertCount(1, (new TabletUnlockerService())->sync($user));
        $this->assertCount(0, (new TabletUnlockerService())->sync($other));
    }

    public function test_the_same_tablet_cannot_be_unlocked_twice_for_one_user(): void
    {
        $user = User::factory()->create();
        $tablet = $this->tablet(null);

        $user->tabletProgress()->create(['tablet_id' => $tablet->id, 'unlocked_at' => now()]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $user->tabletProgress()->create(['tablet_id' => $tablet->id, 'unlocked_at' => now()]);
    }

    public function test_marking_a_tablet_as_read_only_records_the_first_time(): void
    {
        $user = User::factory()->create();
        $tablet = $this->tablet(null);
        $progress = $user->tabletProgress()->create(['tablet_id' => $tablet->id, 'unlocked_at' => now()]);

        $this->assertFalse($progress->isRead());

        $this->travelTo(now()->setDate(2026, 10, 1)->setTime(10, 0));
        $progress->markRead();
        $firstRead = $progress->fresh()->read_at;

        $this->travelTo(now()->setDate(2026, 10, 5)->setTime(10, 0));
        $progress->fresh()->markRead();

        $this->assertTrue($progress->fresh()->isRead());
        $this->assertTrue($firstRead->equalTo($progress->fresh()->read_at));
    }
}
