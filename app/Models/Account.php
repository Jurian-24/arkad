<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasUlids;

    protected $fillable = [
        'institution_id', 'name', 'type', 'iban',
        'currency', 'expected_return_pct', 'opened_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
            'expected_return_pct' => 'decimal:3',
            'type' => AccountType::class, // a backed enum: checking, savings, deposit, investment, loan
            'iban' => 'encrypted',        // optional, but note below
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function terms(): HasOne
    {
        return $this->hasOne(AccountTerm::class);
    }

    public function balanceSnapshots(): HasMany
    {
        return $this->hasMany(AccountBalanceSnapshot::class);
    }

    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(AccountBalanceSnapshot::class)->latestOfMany('as_of_date');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class);
    }

    public function recurringPayments(): HasMany
    {
        return $this->hasMany(RecurringPayment::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /** Recurring transfers that move money into this account. */
    public function incomingRecurringTransfers(): HasMany
    {
        return $this->hasMany(RecurringPayment::class, 'destination_account_id');
    }
}
