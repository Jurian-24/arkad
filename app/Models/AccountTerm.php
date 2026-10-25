<?php

namespace App\Models;

use App\Enums\Compounding;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The contract details of a deposit-style account: how much was deposited,
 * the agreed interest rate and when it matures.
 */
class AccountTerm extends Model
{
    use HasUlids;

    protected $fillable = [
        'principal',
        'interest_rate_pct',
        'start_date',
        'maturity_date',
        'compounding',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'interest_rate_pct' => 'decimal:3',
            'start_date' => 'date',
            'maturity_date' => 'date',
            'compounding' => Compounding::class, // none, monthly, yearly
            'auto_renew' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** Whether the term has ended. Open-ended accounts never mature. */
    public function hasMatured(): bool
    {
        return $this->maturity_date !== null && $this->maturity_date->isPast();
    }

    /** Days until maturity, or null for an open-ended account. */
    public function daysUntilMaturity(): ?int
    {
        if ($this->maturity_date === null) {
            return null;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->maturity_date, false));
    }

    /**
     * Estimated value at maturity, based on the principal, the rate and the
     * compounding. This is an estimate: banks differ in how they count days
     * and tax is not included, so check the contract for the exact figure.
     */
    public function projectedMaturityValue(): ?float
    {
        if ($this->maturity_date === null) {
            return null;
        }

        $principal = (float) $this->principal;
        $rate = (float) $this->interest_rate_pct / 100;
        $years = $this->start_date->diffInDays($this->maturity_date) / 365;

        $value = match ($this->compounding) {
            Compounding::None => $principal * (1 + $rate * $years),
            Compounding::Monthly => $principal * (1 + $rate / 12) ** (12 * $years),
            Compounding::Yearly => $principal * (1 + $rate) ** $years,
        };

        return round($value, 2);
    }
}
