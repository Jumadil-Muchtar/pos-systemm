<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BayarUtang extends Model
{
    public function pelanggan() : BelongsTo{
        return $this->belongsTo(Pelanggan::class);
    }
    
}
