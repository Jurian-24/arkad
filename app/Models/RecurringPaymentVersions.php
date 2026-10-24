<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The expected amount of a recurring payment from a certain date onwards.
 *
 * Example: Netflix costs 12.99 from 2025-01-01 and 15.49 from 2026-03-01.
 * Comparing a March 2025 transaction against the version that was valid
 * then avoids a false "price increase" warning on old months.
 */
class RecurringPaymentVersion extends Model
{
    use HasUlids;

    protected $fillable = [
        'amount',
        'valid_from',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'valid_from' => 'date',
        ];
    }

    public function recurringPayment(): BelongsTo
    {
        return $this->belongsTo(RecurringPayment::class);
    }
}
