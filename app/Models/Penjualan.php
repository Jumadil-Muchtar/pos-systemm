<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Penjualan extends Model
{
    public function pelanggan(): BelongsTo{
        return $this->belongsTo(Pelanggan::class);
    }

    public function barangs() : HasMany{
        return $this->hasMany(Barang::class);
    }

    public function saldo() : HasOne{
        return $this->hasOne(SaldoPelanggan::class);
    }

}
