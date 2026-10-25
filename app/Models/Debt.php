<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A debt the user is paying off ("repay thy debts" in the book).
 * The monthly payment itself is a recurring payment, so the actual
 * transactions can be matched to it.
 */
class Debt extends Model
{
    use HasUlids;

    protected $fillable = [
        'recurring_payment_id',
        'name',
        'principal',
        'interest_rate_pct',
        'monthly_payment',
        'started_on',
        'ends_on',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'interest_rate_pct' => 'decimal:3',
            'monthly_payment' => 'decimal:2',
            'started_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recurringPayment(): BelongsTo
    {
        return $this->belongsTo(RecurringPayment::class);
    }

    /** A debt without an end date, or with one in the future, is still running. */
    public function isActive(): bool
    {
        return $this->ends_on === null || $this->ends_on->isFuture();
    }
}
