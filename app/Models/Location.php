<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    
    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_location')
            ->withPivot('stock');
    }
    /**
     * Relasi transfer barang KELUAR dari lokasi ini
     */
    public function outgoingTransfers()
    {
        return $this->hasMany(Transfer::class, 'from_warehouse_id');
    }

    /**
     * Relasi transfer barang MASUK ke lokasi ini
     */
    public function incomingTransfers()
    {
        return $this->hasMany(Transfer::class, 'to_warehouse_id');
    }

   
    
    
}
