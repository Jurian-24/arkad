<?php

namespace App\Models;

use App\Enums\Bucket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The share of income a user wants to put in a bucket (the 70 / 20 / 10
 * rule from the book), valid from a certain date. When a user changes the
 * split, new rows are added so older months keep their old targets.
 */
class AllocationRule extends Model
{
    use HasUlids;

    protected $fillable = [
        'bucket',
        'target_pct',
        'valid_from',
    ];

    protected function casts(): array
    {
        return [
            'bucket' => Bucket::class, // living, debt, savings
            'target_pct' => 'decimal:2',
            'valid_from' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The split that applied to a user on the given date: for each bucket,
     * the newest rule that started on or before that date.
     *
     * @return Collection<string, AllocationRule> keyed by bucket value
     */
    public static function effectiveFor(User $user, Carbon $date): Collection
    {
        return static::where('user_id', $user->id)
            ->where('valid_from', '<=', $date->toDateString())
            ->orderByDesc('valid_from')
            ->get()
            ->unique(fn ($rule) => $rule->bucket->value)
            ->keyBy(fn ($rule) => $rule->bucket->value);
    }
}
