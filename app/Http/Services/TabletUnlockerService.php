<?php

namespace App\Services;

use App\Models\TabletOfWisdom;
use App\Models\User;
use Illuminate\Support\Collection;

class TabletUnlockerService
{
    /**
     * Unlock every tablet whose condition the user now meets.
     *
     * @return Collection<int, TabletOfWisdom> the tablets unlocked just now,
     *         so the caller can show a "new tablet" message
     */
    public function sync(User $user): Collection
    {
        $alreadyUnlocked = $user->tabletProgress()->pluck('tablet_id');

        $newlyUnlocked = TabletOfWisdom::ordered()
            ->whereNotIn('id', $alreadyUnlocked)
            ->get()
            ->filter(fn (TabletOfWisdom $tablet) => $tablet->isUnlockableBy($user));

        foreach ($newlyUnlocked as $tablet) {
            $user->tabletProgress()->create([
                'tablet_id' => $tablet->id,
                'unlocked_at' => now(),
            ]);
        }

        return $newlyUnlocked->values();
    }
}
