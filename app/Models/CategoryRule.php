<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
