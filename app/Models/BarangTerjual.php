<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BarangTerjual extends Model
{
    public function barang() : BelongsTo{
        return $this->belongsTo(Barang::class);
    }
}
