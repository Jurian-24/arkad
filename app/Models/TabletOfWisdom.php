<?php

namespace App\Models;

use App\Enums\TabletUnlockCondition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TabletOfWisdom extends Model
{
    use HasUlids;

    protected $table = 'tablets_of_wisdom';

    protected $fillable = [
        'title',
        'body',
        'sort_order',
        'unlock_condition',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'unlock_condition' => TabletUnlockCondition::class,
            'unlock_threshold' => 'integer',
        ];
    }

    public function userProgress(): HasMany
    {
        // The foreign key is tablet_id, not the default tablet_of_wisdom_id.
        return $this->hasMany(UserTabletProgress::class, 'tablet_id');
    }

    /** Tablets in the order they should be shown. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /** Whether the user meets the condition. A tablet without one is open to everyone. */
    public function isUnlockableBy(User $user): bool
    {
        return $this->unlock_condition === null
            || $this->unlock_condition->isMetBy($user, $this->unlock_threshold);
    }
}
