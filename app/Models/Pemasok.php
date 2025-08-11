<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    public function penjualans()  : HasMany{
        return $this->hasMany(Pembelian::class);
    }
}
