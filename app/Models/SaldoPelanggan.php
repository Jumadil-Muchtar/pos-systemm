<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoPelanggan extends Model
{
    public function penjualan() : BelongsTo {
        return $this->belongsTo(Penjualan::class);
    }
    public function pelanggan() : BelongsTo {
        return $this->belongsTo(Pelanggan::class);
    }
}
