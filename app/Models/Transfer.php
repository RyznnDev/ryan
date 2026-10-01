<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = ['product_id', 'from_warehouse_id', 'to_warehouse_id', 'date', 'qty', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke Gudang/Lokasi Asal
     */
    public function fromWarehouse()
    {
        // Sesuaikan 'Warehouse::class' atau 'Location::class' sesuai nama model gudang kamu
        return $this->belongsTo(Location::class, 'from_warehouse_id'); 
    }

    /**
     * Relasi ke Gudang/Lokasi Tujuan
     */
    public function toWarehouse()
    {
        // Sesuaikan 'Warehouse::class' atau 'Location::class' sesuai nama model gudang kamu
        return $this->belongsTo(Location::class, 'to_warehouse_id');
    }

    

    

}
