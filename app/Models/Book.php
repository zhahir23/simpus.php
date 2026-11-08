<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'published_year',
        'synopsis',
        'reference_only',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'reference_only' => 'boolean',
        ];
    }
}
