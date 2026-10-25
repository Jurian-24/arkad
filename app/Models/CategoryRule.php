<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MatchField;
use App\Enums\MatchType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryRule extends Model
{
    use HasUlids;

    protected $fillable = [
        'category_id',
        'match_field',
        'match_type',
        'match_value',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'match_field' => MatchField::class, // description, counterparty_name, counterparty_iban
            'match_type' => MatchType::class,   // contains, equals, regex
            'priority' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
