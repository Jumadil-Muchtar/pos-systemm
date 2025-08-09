<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    public function produk() : BelongsTo{
        return $this->belongsTo(Produk::class);
    }
    public function pembelian() : BelongsTo {
        return $this->belongsTo(Pembelian::class);
    }
    public function penjualan() : BelongsTo {
        return $this->belongsTo(Penjualan::class);
    }
}
