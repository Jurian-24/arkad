<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsvImportProfile extends Model
{
    use HasUlids;

    protected $fillable = [
        'institution_id', 'name', 'delimiter', 'date_format',
        'decimal_separator', 'column_mapping',
    ];

    protected function casts(): array
    {
        return ['column_mapping' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class, 'profile_id');
    }
}
