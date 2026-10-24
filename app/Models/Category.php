<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

class Category extends Model
{
    use HasUlids;

    protected $fillable = [
        'parent_id',
        'name',
        'bucket',
        'is_income',
    ];

    protected function casts(): array
    {
        return [
            'is_income' => 'boolean',
            'bucket' => Bucket::class, // enum: living, debt, savings, other
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CategoryRule::class)->orderBy('priority');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringPayments(): HasMany
    {
        return $this->hasMany(RecurringPayment::class);
    }

    // return categories made by the user with the systemcategories
    public function scopeAvailableTo(Builder $query, User $user): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $user->id));
    }
}
