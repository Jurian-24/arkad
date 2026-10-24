<?php

namespace App\Models;

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

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'raw_transaction_id');
    }
}
