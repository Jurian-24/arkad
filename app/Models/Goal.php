<?php

namespace App\Models;

use App\Enums\GoalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasUlids;

    protected $fillable = [
        'account_id',
        'name',
        'target_amount',
        'currency',
        'target_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'target_date' => 'date',
            'status' => GoalStatus::class, // active, reached, abandoned
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * How far along the goal is, as a percentage of the target amount,
     * based on the latest balance of the linked account. Null when the
     * goal is not linked to an account or there is no snapshot yet.
     */
    public function progressPct(): ?float
    {
        $balance = $this->account?->latestSnapshot?->balance;

        if ($balance === null || (float) $this->target_amount <= 0) {
            return null;
        }

        return round(min(100, (float) $balance / (float) $this->target_amount * 100), 1);
    }
}
