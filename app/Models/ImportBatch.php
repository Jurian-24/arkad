<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasUlids;

    protected $fillable = [
        'account_id', 'profile_id', 'filename', 'file_hash',
        'status', 'rows_total', 'rows_imported', 'rows_skipped', 'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'imported_at' => 'datetime',
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

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CsvImportProfile::class, 'profile_id');
    }

    public function rawBankTransactions(): HasMany
    {
        return $this->hasMany(RawBankTransaction::class);
    }
}
