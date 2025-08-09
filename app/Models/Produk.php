<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;
    public function kategori() : BelongsTo{
        return $this->belongsTo(KategoriProduk::class);
    }

    public function barangs(): HasMany{
        return $this->hasMany(Barang::class);
    }
}
