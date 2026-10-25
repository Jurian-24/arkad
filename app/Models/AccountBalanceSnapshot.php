<?php

namespace App\Models;

use App\Enums\SnapshotSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The balance of an account on a certain date. For bank accounts it can be
 * derived from transactions, for investment platforms the user enters it
 * manually.
 */
class AccountBalanceSnapshot extends Model
{
    use HasUlids;

    protected $fillable = [
        'as_of_date',
        'balance',
        'contributions',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'as_of_date' => 'date',
            'balance' => 'decimal:2',
            // Net money the user added in this period (negative = withdrawn).
            'contributions' => 'decimal:2',
            'source' => SnapshotSource::class, // manual, derived, import
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** The snapshot of the same account right before this one. */
    public function previous(): ?AccountBalanceSnapshot
    {
        return static::where('account_id', $this->account_id)
            ->where('as_of_date', '<', $this->as_of_date)
            ->orderByDesc('as_of_date')
            ->first();
    }

    /**
     * Growth since the previous snapshot, without the money the user put in:
     * growth = balance - previous balance - contributions.
     *
     * Returns null for the first snapshot, because there is nothing to
     * compare against. A deposit of 1,000 then does not look like growth.
     */
    public function growth(): ?float
    {
        $previous = $this->previous();

        if (! $previous) {
            return null;
        }

        return round(
            (float) $this->balance - (float) $previous->balance - (float) $this->contributions,
            2,
        );
    }

    public function scopeOnOrBefore(Builder $query, $date): Builder
    {
        return $query->where('as_of_date', '<=', $date);
    }
}
