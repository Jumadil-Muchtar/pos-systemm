<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hutang extends Model
{
    use SoftDeletes;

    public function pelanggan() : BelongsTo{
        return $this->belongsTo(Pelanggan::class);
    }
    public function penjualan() : BelongsTo{
        return $this->belongsTo(Penjualan::class);
    }
}
