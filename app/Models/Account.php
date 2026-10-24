<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $fillable = [
        'user_id', 'institution_id', 'name', 'type', 'iban',
        'currency', 'expected_return_pct', 'opened_at', 'closed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function accountTerm()
    {
        return $this->hasOne(AccountTerm::class);
    }

    public function accountBalanceSnapshots()
    {
        return $this->hasMany(AccountBalanceSnapshot::class);
    }

    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(AccountBalanceSnapshot::class)->latestOfMany('as_of_date');
    }

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
            'expected_return_pct' => 'decimal:2',
            'type' => AccountType::class, // a backed enum: checking, savings, deposit, investment, loan
            'iban' => 'encrypted',        // optional, but note below
        ];
    }
}
