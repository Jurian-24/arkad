<?php

namespace App\Models;

use App\Enums\InstitutionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A bank, broker or savings provider. This is a shared catalogue: users
 * pick an institution when they add an account, they do not create them.
 */
class Institution extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'type',
        'country',
        'default_expected_return_pct',
    ];

    protected function casts(): array
    {
        return [
            'type' => InstitutionType::class,
            'default_expected_return_pct' => 'decimal:3',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function csvImportProfiles(): HasMany
    {
        return $this->hasMany(CsvImportProfile::class);
    }
}
