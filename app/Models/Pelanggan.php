<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use SoftDeletes;
    public function pembelians() : HasMany{
        return $this->hasMany(Penjualan::class);
    }
    public function hutangs() : HasMany{
        return $this->hasMany(Hutang::class);
    }
    public function riwayatSaldo() : HasMany{
        return $this->hasMany(SaldoPelanggan::class);
    }
}
