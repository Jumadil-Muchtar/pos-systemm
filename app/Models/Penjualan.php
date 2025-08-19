<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;


class Penjualan extends Model
{
    use SoftDeletes;
    public function pelanggan(): BelongsTo{
        return $this->belongsTo(Pelanggan::class);
    }

    public function barangs() : HasMany{
        return $this->hasMany(Barang::class);
    }

    public function barang_terjuals() : HasMany {
        return $this->hasMany(BarangTerjual::class);
    }

    public function saldo() : HasOne{
        return $this->hasOne(SaldoPelanggan::class);
    }

}
