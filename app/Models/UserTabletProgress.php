<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTabletProgress extends Model
{
    use HasUlids;

    // Laravel would pluralize this to user_tablet_progresses.
    protected $table = 'user_tablet_progress';

    protected $fillable = [
        'tablet_id',
        'unlocked_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(TabletOfWisdom::class, 'tablet_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /** Record that the user has read the tablet (only the first time). */
    public function markRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}
