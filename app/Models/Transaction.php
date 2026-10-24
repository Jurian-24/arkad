<?php

namespace App\Models;

use App\Enums\TransactionKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasUlids;

    protected $fillable = [
        'account_id',
        'raw_transaction_id',
        'category_id',
        'recurring_payment_id',
        'transfer_group_id',
        'booked_at',
        'amount',
        'currency',
        'kind',
        'description',
        'counterparty_name',
        'counterparty_iban',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'booked_at' => 'date',
            // Signed amount: negative = money out, positive = money in.
            'amount' => 'decimal:2',
            'kind' => TransactionKind::class,
            // Optional, see the note about encryption below.
            'counterparty_iban' => 'encrypted',
            'description' => 'encrypted',
        ];
    }

    //  relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function rawTransaction(): BelongsTo
    {
        return $this->belongsTo(RawBankTransaction::class, 'raw_transaction_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recurringPayment(): BelongsTo
    {
        return $this->belongsTo(RecurringPayment::class);
    }

    /**
     * The other side of an internal transfer: the row in the same group
     * that is not this one. Null if the counterpart has not been imported
     * yet, or if this is not a transfer.
     */
    public function transferPartner(): ?Transaction
    {
        if (! $this->transfer_group_id) {
            return null;
        }

        return static::where('transfer_group_id', $this->transfer_group_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    // scopes

    /** Only rows that count towards income and expense totals. */
    public function scopeCountingInTotals(Builder $query): Builder
    {
        return $query->where('kind', '!=', TransactionKind::InternalTransfer);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('kind', TransactionKind::Income);
    }

    public function scopeExpenses(Builder $query): Builder
    {
        return $query->where('kind', TransactionKind::Expense);
    }

    public function scopeInternalTransfers(Builder $query): Builder
    {
        return $query->where('kind', TransactionKind::InternalTransfer);
    }

    /** All transactions booked in the same calendar month as $date. */
    public function scopeInMonth(Builder $query, Carbon $date): Builder
    {
        return $query->whereBetween('booked_at', [
            $date->copy()->startOfMonth()->toDateString(),
            $date->copy()->endOfMonth()->toDateString(),
        ]);
    }


    // helpers
    public function isIncoming(): bool
    {
        return (float) $this->amount > 0;
    }

    public function isOutgoing(): bool
    {
        return (float) $this->amount < 0;
    }
}
