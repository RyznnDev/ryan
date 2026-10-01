<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    public const CATEGORIES = ['Elektronik', 'Fashion', 'Makanan', 'Minuman', 'Lainnya'];

    public const STATUSES = [
        'active'   => 'Aktif',
        'inactive' => 'Nonaktif',
    ];

    protected $fillable = [
        'image', 'sku', 'title', 'category', 'status',
        'description', 'price', 'stock',
    ];

    public function category()  { return $this->belongsTo(Category::class); }

    public function locations() { return $this->belongsToMany(Location::class, 'product_location')->withPivot('stock'); }

    public function adjustLocationStock(int $locationId, int $delta): void
    {
        $current = (int) ($this->locations()->where('locations.id', $locationId)->first()?->pivot->stock ?? 0);

        if ($current + $delta < 0) {
            throw ValidationException::withMessages(['qty' => 'Stok di gudang tidak cukup.']);
        }

        $this->locations()->syncWithoutDetaching([$locationId => ['stock' => $current + $delta]]);
    }


}