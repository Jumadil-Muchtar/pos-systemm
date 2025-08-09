<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pemasok;

class Pemasok extends Model
{
    public function penjualans()  : HasMany{
        return $this->hasMany(Pembelian::class);
    }
}
