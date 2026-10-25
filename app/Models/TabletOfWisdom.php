<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TabletOfWisdom extends Model
{
    use HasUlids;

    protected $fillable = [
        'title',
        'body',
        'sort_order',
        'unlock_condition',
    ];

    protected $table = 'tablets_of_wisdom';

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserTabletProgress::class);
    }
}
