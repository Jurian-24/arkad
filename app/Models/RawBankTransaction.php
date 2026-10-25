<?php

namespace App\Models;

use App\Enums\RawRowStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawBankTransaction extends Model
{
    use HasUlids;

    protected $fillable = [
        'import_batch_id', 'account_id', 'row_number',
        'payload', 'dedupe_hash', 'status', 'error_message',
    ];

    // The original CSV row contains personal data, keep it out of serialized output.
    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'row_number' => 'integer',
            'status' => RawRowStatus::class,
        ];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The normalized transaction made from this row, if it has been processed. */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'raw_transaction_id');
    }
}
