<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTabletProgress extends Model
{
    use HasUlids;

    protected $fillable = [
        'tablet_id',
        'unlocked_at',
        'read_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(TabletOfWisdom::class);
    }
}
