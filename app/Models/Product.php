<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    // NOTE (change NOT in the worksheet instructions): This model represents
    // the four cake bases required by the midterm Product rubric.

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'base_price_cents',
        'image_path',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'base_price_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function cakePreviews(): HasMany
    {
        return $this->hasMany(CakePreview::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
