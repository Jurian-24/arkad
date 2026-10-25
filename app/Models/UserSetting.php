<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasUlids;

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        // value is a json column: strings, numbers, booleans and arrays all work.
        return ['value' => 'json'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
