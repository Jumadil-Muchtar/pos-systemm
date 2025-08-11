<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends Model
{
    use SoftDeletes;
    public function pemasok(): BelongsTo{
        return $this->belongsTo(Pemasok::class);
    }

    public function barangs() : HasMany{
        return $this->hasMany(Barang::class);
    }
}
