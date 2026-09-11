<?php

namespace App\Models;

use Database\Factories\CakePreviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CakePreview extends Model
{
    // NOTE (change NOT in the worksheet instructions): Preview records map
    // each curated cake combination to a generated local image path.

    /** @use HasFactory<CakePreviewFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'selection_key',
        'image_path',
        'alt_text',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hasAvailableImage(): bool
    {
        return is_file(public_path($this->image_path));
    }
}
