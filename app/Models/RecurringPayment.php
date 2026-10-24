<?php

namespace App\Models;

use App\Enums\Frequency;
use App\Enums\RecurringKind;
use App\Enums\RecurringSource;
use App\Enums\RecurringStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class RecurringPayment extends Model
{
    use HasUlids;

    protected $fillable = [
        'account_id',
        'destination_account_id',
        'category_id',
        'name',
        'kind',
        'amount',
        'currency',
        'amount_is_variable',
        'frequency',
        'interval_count',
        'anchor_date',
        'ends_on',
        'status',
        'source',
        'match_pattern',
    ];

    protected function casts(): array
    {
        return [
            'kind' => RecurringKind::class,
            'frequency' => Frequency::class,
            'status' => RecurringStatus::class,
            'source' => RecurringSource::class,
            'amount' => 'decimal:2',
            'amount_is_variable' => 'boolean',
            'interval_count' => 'integer',
            'anchor_date' => 'date',
            'ends_on' => 'date',
            'match_pattern' => 'array',
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

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RecurringPaymentVersion::class)->orderByDesc('valid_from');
    }

    public function debt(): HasOne
    {
        return $this->hasOne(Debt::class);
    }

    // scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', RecurringStatus::Active);
    }

    public function scopeSuggested(Builder $query): Builder
    {
        return $query->where('status', RecurringStatus::Suggested);
    }

    /**
     * The amount converted to one month, so that yearly and quarterly payments
     * count as an even cost in the monthly budget.
     *
     * Example: 100 every 2 weeks = 100 * (52 / 2) / 12 = 216.67 per month.
     */
    protected function monthlyEquivalent(): Attribute
    {
        return Attribute::get(function (): float {
            // How many payments happen per year, taking the interval into account.
            $periodsPerYear = $this->frequency->periodsPerYear() / max(1, $this->interval_count);

            return round((float) $this->amount * $periodsPerYear / 12, 2);
        });
    }

    /**
     * The next due date on or after $from (defaults to today), calculated from
     * anchor_date. Returns null when the payment has ended.
     */
    public function nextDueDate(?Carbon $from = null): ?Carbon
    {
        $from = ($from ?? today())->copy()->startOfDay();

        // Step forward one interval at a time, always starting from the anchor date.
        // Each date is calculated from the anchor, not from the previous date, so
        // a payment on the 31st returns to the 31st after a short month instead of
        // drifting to the 28th (Jan 31 -> Feb 28 -> Mar 31, not Mar 28).
        $step = 0;
        $date = $this->anchor_date->copy();

        // The limit prevents an endless loop if the data is invalid.
        while ($date->lt($from) && $step < 5000) {
            $step++;
            $date = $this->frequency->advance(
                $this->anchor_date->copy(),
                $this->interval_count * $step,
            );
        }

        // The payment has an end date and the next occurrence falls after it.
        if ($this->ends_on && $date->gt($this->ends_on)) {
            return null;
        }

        return $date;
    }
}
