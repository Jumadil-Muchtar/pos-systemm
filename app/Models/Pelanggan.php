<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    public function pembelians() : HasMany{
        return $this->hasMany(Penjualan::class);
    }
    public function bayarUtangs() : HasMany{
        return $this->hasMany(BayarUtang::class);
    }
    public function riwayatSaldo() : HasMany{
        return $this->hasMany(SaldoPelanggan::class);
    }
}
